<div id="chat-message-center">
    <a class="nav-link dropdown-toggle" href="#" id="chatMessagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-comments fa-fw"></i>
        <span id="chat-mc-count" class="badge badge-danger badge-counter">0</span>
    </a>
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="chatMessagesDropdown">
        <h6 class="dropdown-header">
            Message Center
            <button id="mark-all-read" class="btn btn-sm btn-link text-primary float-right" style="font-size: 12px; padding: 0;">
                Đánh dấu tất cả đã đọc
            </button>
        </h6>
        <div id="chat-mc-items">
            <div class="dropdown-item text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
        </div>
        <a class="dropdown-item text-center small text-gray-500" href="{{ url('/chat') }}">Đi tới Chat</a>
    </div>
</div>

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script>
(function(){
    var itemsEl = document.getElementById('chat-mc-items');
    var countEl = document.getElementById('chat-mc-count');
    var markAllBtn = document.getElementById('mark-all-read');
    var maxItems = 10;
    var pollInterval = 60000; // 60 seconds (reduced frequency since we have Firebase realtime)
    var pollTimer;
    
    // Firebase setup
    var firebaseInitialized = false;
    var database = null;
    var currentUser = {
        id: {{ (int) auth()->id() }},
        role: @json(optional(auth()->user())->role ?? 'admin')
    };

    // Get CSRF token
    function getCSRFToken() {
        var token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    // Get API token (adjust based on your auth method)
    function getAPIToken() {
        // If using session auth, return empty (Laravel will handle via session)
        // If using Sanctum/JWT, get from localStorage or meta tag
        return '';
    }

    // Initialize Firebase
    function initFirebase() {
        try {
            // Read Firebase config from meta tags
            function meta(name) { 
                var el = document.querySelector('meta[name="'+name+'"]'); 
                return el ? el.getAttribute('content') : ''; 
            }
            
            var firebaseConfig = {
                apiKey: meta('firebase-api-key'),
                authDomain: meta('firebase-auth-domain'),
                databaseURL: meta('firebase-database-url'),
                projectId: meta('firebase-project-id'),
                storageBucket: meta('firebase-storage-bucket'),
                messagingSenderId: meta('firebase-messaging-sender-id'),
                appId: meta('firebase-app-id')
            };
            
            if (!firebase.apps.length && firebaseConfig.apiKey) {
                firebase.initializeApp(firebaseConfig);
                database = firebase.database();
                firebaseInitialized = true;
                console.log('Firebase initialized for Message Center');
                setupFirebaseListeners();
            } else if (firebase.apps.length) {
                database = firebase.database();
                firebaseInitialized = true;
                setupFirebaseListeners();
            }
        } catch (error) {
            console.error('Firebase initialization failed:', error);
            firebaseInitialized = false;
        }
    }

    // Setup Firebase listeners for realtime notifications
    function setupFirebaseListeners() {
        if (!firebaseInitialized || !database) return;
        
        // Get assigned conversations (similar to old logic)
        getAssignedConversations().then(function(conversationIds) {
            conversationIds.forEach(function(conversationId) {
                bindToConversation(conversationId);
            });
        });
    }

    // Get conversations this user should monitor
    function getAssignedConversations() {
        return new Promise(function(resolve) {
            if (currentUser.role === 'admin') {
                // Admin can see all conversations - get from userConversations
                database.ref('userConversations/' + currentUser.id)
                    .once('value')
                    .then(function(snapshot) {
                        var conversations = snapshot.val() || {};
                        resolve(Object.keys(conversations));
                    })
                    .catch(function(error) {
                        console.warn('Failed to get admin conversations:', error);
                        resolve([]);
                    });
            } else if (currentUser.role === 'sub_admin') {
                // Sub admin - get assigned conversations
                database.ref('userConversations/' + currentUser.id)
                    .once('value')
                    .then(function(snapshot) {
                        var conversations = snapshot.val() || {};
                        resolve(Object.keys(conversations));
                    })
                    .catch(function(error) {
                        console.warn('Failed to get sub_admin conversations:', error);
                        resolve([]);
                    });
            } else {
                resolve([]);
            }
        });
    }

    // Bind to a specific conversation for realtime updates
    function bindToConversation(conversationId) {
        if (!database) return;
        
        try {
            database.ref('messages/' + conversationId)
                .limitToLast(1)
                .on('child_added', function(snapshot) {
                    var message = snapshot.val() || {};
                    
                    // Ignore messages from current user
                    if (parseInt(message.senderId) === currentUser.id) return;
                    
                    // Add realtime notification to Message Center
                    addRealtimeNotification({
                        conversationId: conversationId,
                        senderId: message.senderId,
                        senderName: message.senderName || 'Unknown User',
                        preview: message.type === 'image' ? '[Hình ảnh]' : (message.content || ''),
                        type: message.type || 'text',
                        timestamp: message.timestamp || Date.now(),
                        isRealtime: true
                    });
                    
                    playSound();
                });
        } catch (error) {
            console.error('Error binding to conversation:', conversationId, error);
        }
    }

    function timeAgo(timestamp) {
        try {
            var date = new Date(timestamp);
            var now = new Date();
            var diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'vừa xong';
            if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
            if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
            return Math.floor(diff / 86400) + ' ngày trước';
        } catch(e) {
            return 'vừa xong';
        }
    }

    function setCount(n) {
        if (!countEl) return;
        countEl.textContent = n > 99 ? '99+' : n.toString();
        countEl.style.display = n > 0 ? 'inline' : 'none';
    }

    function playSound() {
        var audio = document.getElementById('mc-audio');
        if (audio) {
            audio.play().catch(function(){});
        }
    }

    // Store realtime notifications (not persisted in Laravel)
    var realtimeNotifications = [];

    // Add realtime notification (from Firebase)
    function addRealtimeNotification(notificationData) {
        // Check if already exists (prevent duplicates)
        var exists = realtimeNotifications.find(function(n) {
            return n.conversationId === notificationData.conversationId && 
                   Math.abs(n.timestamp - notificationData.timestamp) < 5000; // 5 second tolerance
        });
        
        if (exists) return;
        
        // Add to realtime notifications
        realtimeNotifications.unshift({
            id: 'realtime_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
            conversationId: notificationData.conversationId,
            senderId: notificationData.senderId,
            senderName: notificationData.senderName,
            preview: notificationData.preview,
            type: notificationData.type,
            timestamp: notificationData.timestamp,
            isRealtime: true,
            is_unread: true,
            created_at: new Date(notificationData.timestamp).toISOString(),
            data: {
                conversation_id: notificationData.conversationId,
                sender_id: notificationData.senderId,
                sender_name: notificationData.senderName,
                preview: notificationData.preview,
                type: notificationData.type,
                timestamp: notificationData.timestamp,
                link: '/chat?conversationId=' + notificationData.conversationId
            }
        });
        
        // Keep only recent realtime notifications (last 20)
        if (realtimeNotifications.length > 20) {
            realtimeNotifications = realtimeNotifications.slice(0, 20);
        }
        
        // Re-render with combined data
        renderCombinedNotifications();
    }

    // Render combined notifications (Laravel + Firebase realtime)
    function renderCombinedNotifications(laravelData) {
        if (!itemsEl) return;
        
        var laravelUnread = (laravelData && laravelData.unread) || [];
        var laravelRecent = (laravelData && laravelData.recent) || [];
        
        // Combine all notifications
        var allNotifications = [];
        
        // Add realtime notifications (always unread)
        realtimeNotifications.forEach(function(n) {
            allNotifications.push(n);
        });
        
        // Add Laravel unread notifications
        laravelUnread.forEach(function(n) { 
            n.is_unread = true; 
            n.isRealtime = false;
            allNotifications.push(n); 
        });
        
        // Add Laravel recent notifications (if not already in unread)
        laravelRecent.forEach(function(n) { 
            if (!laravelUnread.find(function(u) { return u.id === n.id; })) {
                n.is_unread = false; 
                n.isRealtime = false;
                allNotifications.push(n); 
            }
        });
        
        // Remove duplicates and sort by timestamp
        var uniqueNotifications = [];
        var seen = new Set();
        
        allNotifications.forEach(function(n) {
            var key = n.isRealtime ? 
                ('rt_' + n.conversationId + '_' + Math.floor(n.timestamp / 10000)) : 
                ('db_' + n.id);
            
            if (!seen.has(key)) {
                seen.add(key);
                uniqueNotifications.push(n);
            }
        });
        
        // Sort by timestamp desc
        uniqueNotifications.sort(function(a, b) {
            var aTime = a.timestamp || new Date(a.created_at).getTime();
            var bTime = b.timestamp || new Date(b.created_at).getTime();
            return bTime - aTime;
        });
        
        // Update count (realtime + Laravel unread)
        var unreadCount = realtimeNotifications.length + laravelUnread.length;
        setCount(unreadCount);
        
        // Clear items
        itemsEl.innerHTML = '';
        
        if (uniqueNotifications.length === 0) {
            itemsEl.innerHTML = '<div class="dropdown-item text-center text-muted">Không có thông báo mới</div>';
            return;
        }
        
        // Render items (limit to maxItems)
        uniqueNotifications.slice(0, maxItems).forEach(function(notification) {
            renderNotificationItem(notification);
        });
    }

    // Render individual notification item
    function renderNotificationItem(notification) {
        var item = document.createElement('a');
        item.className = 'dropdown-item d-flex align-items-center message-item' + 
                       (notification.is_unread ? ' bg-light' : '');
        item.href = notification.data ? notification.data.link : ('/chat?conversationId=' + notification.conversationId);
        
        if (notification.id && !notification.isRealtime) {
            item.setAttribute('data-notification-id', notification.id);
        }
        
        var avatarWrap = document.createElement('div');
        avatarWrap.className = 'dropdown-list-image mr-3';
        var img = document.createElement('img');
        img.className = 'rounded-circle';
        img.src = '{{ asset('backend/img/avatar.png') }}';
        img.alt = (notification.data ? notification.data.sender_name : notification.senderName) || 'user';
        img.style.width = '40px';
        img.style.height = '40px';
        avatarWrap.appendChild(img);
        
        var textWrap = document.createElement('div');
        textWrap.className = 'font-weight-bold flex-grow-1';
        
        var title = document.createElement('div');
        title.className = 'text-truncate';
        var senderName = notification.data ? notification.data.sender_name : notification.senderName;
        var preview = notification.data ? notification.data.preview : notification.preview;
        title.textContent = (senderName || 'Tin nhắn mới') + (preview ? ': ' + preview : '');
        
        var meta = document.createElement('div');
        meta.className = 'small text-gray-500';
        var timestamp = notification.timestamp || notification.created_at;
        meta.textContent = timeAgo(timestamp) + (notification.isRealtime ? ' (realtime)' : '');
        
        textWrap.appendChild(title);
        textWrap.appendChild(meta);
        
        // Unread indicator
        if (notification.is_unread) {
            var indicator = document.createElement('div');
            indicator.className = 'ml-2';
            indicator.innerHTML = '<i class="fas fa-circle text-primary" style="font-size: 8px;"></i>';
            textWrap.appendChild(indicator);
        }
        
        item.appendChild(avatarWrap);
        item.appendChild(textWrap);
        
        // Click handler
        item.addEventListener('click', function(e) {
            if (notification.is_unread && !notification.isRealtime) {
                markAsRead([notification.id]);
            } else if (notification.isRealtime) {
                // Remove from realtime notifications when clicked
                realtimeNotifications = realtimeNotifications.filter(function(n) {
                    return n.id !== notification.id;
                });
                renderCombinedNotifications();
            }
        });
        
        itemsEl.appendChild(item);
    }

    // Legacy function for Laravel-only notifications
    function renderNotifications(data) {
        renderCombinedNotifications(data);
    }

    function fetchNotifications() {
        var headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        };
        
        var token = getAPIToken();
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }
        
        fetch('/api/notifications', {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(function(data) {
            renderNotifications(data);
        })
        .catch(function(error) {
            console.error('Error fetching notifications:', error);
            if (itemsEl) {
                itemsEl.innerHTML = '<div class="dropdown-item text-center text-danger">Lỗi tải thông báo</div>';
            }
        });
    }

    function markAsRead(ids, isAll) {
        var headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        };
        
        var token = getAPIToken();
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }
        
        var payload = isAll ? { all: true } : { ids: ids };
        
        fetch('/api/notifications/read', {
            method: 'POST',
            headers: headers,
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(function(data) {
            // Refresh notifications after marking as read
            fetchNotifications();
        })
        .catch(function(error) {
            console.error('Error marking notifications as read:', error);
        });
    }

    function startPolling() {
        fetchNotifications(); // Initial load
        pollTimer = setInterval(fetchNotifications, pollInterval);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    // Mark all as read handler
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Clear realtime notifications
            realtimeNotifications = [];
            
            // Mark Laravel notifications as read
            markAsRead([], true);
        });
    }

    // Start polling when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Firebase first
        initFirebase();
        
        // Start Laravel polling (reduced frequency since we have Firebase realtime)
        startPolling();
    });

    // Stop polling when page unloads
    window.addEventListener('beforeunload', function() {
        stopPolling();
    });

    // Pause polling when tab is not visible (optional optimization)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
        }
    });

})();
</script>
<audio id="mc-audio" src="/sounds/notify.mp3" preload="auto"></audio>
@endpush