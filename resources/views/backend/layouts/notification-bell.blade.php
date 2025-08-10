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
            <a class="dropdown-item d-flex align-items-center" target="_blank" href="{{route('admin.notification',$notification->id)}}">
                <div class="mr-3">
                    <div class="icon-circle bg-primary">
                        <i class="fas {{$notification->data['fas'] ?? 'fa-bell'}} text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500">{{$notification->created_at->format('F d, Y h:i A')}}</div>
                    <span class="@if($notification->unread()) font-weight-bold @else small text-gray-500 @endif">{{$notification->data['title']}}</span>
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
});
</script>