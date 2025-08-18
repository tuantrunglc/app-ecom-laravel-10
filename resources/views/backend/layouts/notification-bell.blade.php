{{-- Real-time Notification Bell Component --}}
<a class="nav-link dropdown-toggle" href="#" id="notification-bell" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-bell fa-fw"></i>
    <!-- Counter - Show both old and new notifications -->
    <span id="notification-badge" class="badge badge-danger badge-counter">
        @php
            $oldCount = count(Auth::user()->unreadNotifications);
            $displayCount = $oldCount > 5 ? '5+' : $oldCount;
        @endphp
        <span class="count" data-count="{{ $oldCount }}">{{ $displayCount }}</span>
    </span>
</a>
    
{{-- Notification Dropdown --}}
<div id="notification-dropdown" class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="notification-bell">
        {{-- Header --}}
        <h6 class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications Center</span>
            <div class="notification-controls">
                <button id="sound-toggle" class="btn btn-sm btn-link sound-toggle p-1" title="Toggle notification sounds">
                    <i class="fas fa-volume-up"></i>
                </button>
                <button id="mark-all-read" class="btn btn-sm btn-link p-1" title="Mark all as read">
                    <i class="fas fa-check-double"></i>
                </button>
            </div>
        </h6>
        
        {{-- Existing Laravel Notifications --}}
        @foreach(Auth::user()->unreadNotifications as $notification)
            @php
                $data = $notification->data ?? [];
                $title = $data['title'] ?? ($data['preview'] ?? ($data['message'] ?? 'Notification'));
            @endphp
            <a class="dropdown-item d-flex align-items-center" target="_blank" href="{{route('admin.notification',$notification->id)}}">
                <div class="mr-3">
                    <div class="icon-circle bg-primary">
                        <i class="fas {{$data['fas'] ?? 'fa-bell'}} text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">{{$notification->created_at->format('F d, Y h:i A')}}</div>
                    <span class="@if($notification->unread()) font-weight-bold @else small text-gray-500 @endif">{{$title}}</span>
                </div>
            </a>
            @if($loop->index+1==5)
                @php 
                    break;
                @endphp
            @endif
        @endforeach
        
        {{-- Separator for real-time notifications --}}
        <div id="realtime-separator" class="dropdown-divider" style="display: none;"></div>
        <h6 id="realtime-header" class="dropdown-header" style="display: none;">Real-time Notifications</h6>
        
        {{-- Real-time Notifications List --}}
        <div id="notification-list" class="notification-list" style="display: none;">
            <div class="notification-loading">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading real-time notifications...</div>
            </div>
        </div>
        
        {{-- Sound Control --}}
        <div class="dropdown-item-text d-flex align-items-center justify-content-between px-3 py-2 bg-light">
            <small class="text-muted">Volume:</small>
            <div class="d-flex align-items-center">
                <i class="fas fa-volume-down text-muted me-2"></i>
                <input type="range" id="volume-control" class="form-range" style="width: 80px;" min="0" max="1" step="0.1" value="0.5">
                <i class="fas fa-volume-up text-muted ms-2"></i>
            </div>
        </div>
        
        {{-- Footer --}}
        <a class="dropdown-item text-center small text-gray-500" href="{{ route('all.notification') }}">
            View All Notifications
        </a>
</div>

{{-- Add meta tags for JavaScript --}}
<meta name="user-role" content="{{ Auth::user()->role }}">
<meta name="user-id" content="{{ Auth::id() }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Simple notification bell styling --}}
<style>
.sound-toggle:hover i {
    color: #007bff !important;
}
.badge-counter {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

{{-- Include Firebase SDK --}}
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>

{{-- Include notification sounds --}}
<script src="{{ asset('js/notification-sounds.js') }}"></script>

{{-- Simple sound controls --}}
<script>
$(document).ready(function() {
    // Xử lý nút bật/tắt âm thanh
    $('#sound-toggle').on('click', function(e) {
        e.preventDefault();
        const isEnabled = window.notificationSounds.toggle();
        const icon = $(this).find('i');
        
        if (isEnabled) {
            icon.removeClass('fa-volume-mute').addClass('fa-volume-up');
            window.notificationSounds.playSuccess(); // Test sound
        } else {
            icon.removeClass('fa-volume-up').addClass('fa-volume-mute');
        }
    });
    
    // Xử lý điều khiển âm lượng
    $('#volume-control').on('input', function() {
        const volume = parseFloat($(this).val());
        window.notificationSounds.setVolume(volume);
    });
    
    // Cập nhật UI ban đầu
    const soundToggleIcon = $('#sound-toggle i');
    if (window.notificationSounds.isEnabled()) {
        soundToggleIcon.removeClass('fa-volume-mute').addClass('fa-volume-up');
    } else {
        soundToggleIcon.removeClass('fa-volume-up').addClass('fa-volume-mute');
    }

    // ================= Firebase Realtime for Header =================
    try {
        const firebaseConfig = {
            apiKey: "{{ config('firebase.api_key') }}",
            authDomain: "{{ config('firebase.auth_domain') }}",
            databaseURL: "{{ config('firebase.database_url') }}",
            projectId: "{{ config('firebase.project_id') }}",
            storageBucket: "{{ config('firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
            appId: "{{ config('firebase.app_id') }}"
        };

        // Init Firebase v8 if needed
        if (!window.firebase || !firebase.apps || !firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }

        const db = firebase.database();
        const currentUserId = document.querySelector('meta[name="user-id"]').getAttribute('content');
        const convApiUrl = "{{ route('chat.api.conversations') }}";

        const $badge = $('#notification-badge .count');
        const initialDbCount = parseInt($badge.data('count')) || 0;
        const convStates = {}; // { convId: { unread: number, lastMessage: {...} } }

        // UI helpers
        function updateBadge() {
            const realtimeCount = Object.values(convStates).reduce((sum, s) => sum + (s.unread || 0), 0);
            const total = initialDbCount + realtimeCount;
            const display = total > 5 ? '5+' : total;
            $badge.text(display).attr('data-count', total);
        }

        function ensureRealtimeSectionVisible() {
            $('#realtime-separator, #realtime-header, #notification-list').show();
        }

        function renderRealtimePanel() {
            ensureRealtimeSectionVisible();
            const $list = $('#notification-list');
            $list.empty();

            const items = Object.entries(convStates)
                .filter(([, s]) => (s.unread || 0) > 0)
                .map(([id, s]) => ({ id, ...s }))
                .sort((a, b) => (b.lastTs || 0) - (a.lastTs || 0))
                .slice(0, 5);

            if (!items.length) {
                $list.append('<div class="dropdown-item-text small text-gray-500">Không có tin nhắn chưa đọc</div>');
                return;
            }

            items.forEach(item => {
                const ts = item.lastTs ? new Date(item.lastTs).toLocaleString() : '';
                const preview = item.last && item.last.type === 'image' ? '[Hình ảnh]' : (item.last && item.last.content ? $('<div>').text(item.last.content).html() : 'Tin nhắn mới');
                const url = '/chat/conversation/' + item.id;
                const html = `
                    <a class="dropdown-item d-flex align-items-center" href="${url}">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-comments text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">${ts}</div>
                            <span class="font-weight-bold">${preview}</span>
                        </div>
                    </a>`;
                $list.append(html);
            });
        }

        function attachConversationListener(convId) {
            if (convStates[convId] && convStates[convId]._listening) return;
            convStates[convId] = convStates[convId] || {};
            convStates[convId]._listening = true;

            const ref = db.ref('conversations/' + convId);
            ref.on('value', (snap) => {
                const data = snap.val() || {};
                const key = 'user_' + currentUserId;
                const prevUnread = convStates[convId].unread || 0;
                const unread = data.unreadCount && data.unreadCount[key] ? parseInt(data.unreadCount[key]) : 0;
                convStates[convId].unread = unread;
                convStates[convId].last = data.lastMessage || null;
                convStates[convId].lastTs = (data.lastMessage && data.lastMessage.timestamp) || (data.updatedAt) || Date.now();

                // Sound when new unread appears
                if (unread > prevUnread) {
                    window.triggerNotificationSound && window.triggerNotificationSound('notification');
                }

                updateBadge();
                renderRealtimePanel();
            }, (err) => console.warn('Realtime conv error', convId, err));
        }

        // Bootstrap: fetch user's conversations from backend, then attach listeners
        fetch(convApiUrl, { credentials: 'same-origin' })
            .then(r => r.json())
            .then(list => {
                (list || []).forEach(c => attachConversationListener(c.id));
                updateBadge();
                renderRealtimePanel();
            })
            .catch(err => console.warn('Load conversations failed', err));
    } catch (e) {
        console.warn('Firebase realtime header init failed:', e);
    }
    // ================= End Firebase Realtime =================
});
</script>