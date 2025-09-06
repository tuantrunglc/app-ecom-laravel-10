@extends('backend.layouts.master')

@section('title','Sub Admin Chat - ' . $otherParticipant->name)

@section('main-content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
        <div class="d-flex align-items-center">
            <a href="{{ route('chat.index') }}" class="btn btn-sm btn-dark me-3">
                <i class="fas fa-arrow-left"></i> Back to Chat
            </a>
            <img src="{{ $otherParticipant->photo ? asset($otherParticipant->photo) : asset('backend/img/avatar.png') }}" 
                 class="rounded-circle me-2" width="40" height="40" alt="Avatar">
            <div>
                <h6 class="mb-0 text-dark">{{ $otherParticipant->name }}</h6>
                <small>
                    <span class="badge badge-{{ $otherParticipant->role == 'admin' ? 'danger' : 'info' }}">
                        {{ ucfirst(str_replace('_', ' ', $otherParticipant->role)) }}
                    </span>
                    <span id="user-status" class="ms-2"></span>
                </small>
            </div>
        </div>
        <div class="badge badge-dark">
            <i class="fas fa-user-tie"></i> Sub Admin View
        </div>
    </div>
    
    <div class="card-body p-0">
        <!-- Sub Admin Chat Header Info -->
        <div class="bg-light p-2 border-bottom">
            <div class="row text-center">
                <div class="col-md-3">
                    <small class="text-muted">Chatting as</small><br>
                    <strong class="text-warning">Sub Administrator</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Participant</small><br>
                    <strong>{{ $otherParticipant->name }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Relationship</small><br>
                    @if($otherParticipant->role == 'admin')
                        <span class="badge badge-danger">Your Supervisor</span>
                    @else
                        <span class="badge badge-info">Your User</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Conversation ID</small><br>
                    <code class="small">{{ $conversation->id }}</code>
                </div>
            </div>
        </div>

        <!-- Messages Container -->
        <div id="messages-container" class="p-3" style="height: 500px; overflow-y: auto; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
            <div id="messages-list">
                <!-- Messages will be loaded here -->
            </div>
            <div id="typing-indicator" class="text-muted small" style="display: none;">
                <i class="fas fa-circle-notch fa-spin"></i> <span id="typing-user"></span> is typing...
            </div>
        </div>
        
        <!-- Sub Admin Message Input -->
        <div class="border-top p-3 bg-light">
            <form id="message-form" class="d-flex align-items-end">
                <div class="flex-grow-1 me-2">
                    <textarea id="message-input" 
                              class="form-control border-warning" 
                              rows="2" 
                              placeholder="Type your message as Sub Administrator..."
                              style="resize: none;"></textarea>
                </div>
                <div class="d-flex flex-column">
                    <label for="image-input" class="btn btn-outline-warning btn-sm mb-1" title="Upload Image">
                        <i class="fas fa-image"></i>
                    </label>
                    <input type="file" id="image-input" accept="image/*" style="display: none;">
                    <button type="submit" class="btn btn-warning btn-sm" title="Send Message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
            <div class="mt-2">
                <small class="text-muted">
                    <i class="fas fa-user-tie text-warning"></i> 
                    @if($otherParticipant->role == 'admin')
                        You are chatting with your supervisor
                    @else
                        You are chatting with a user under your management
                    @endif
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="preview-image" src="" class="img-fluid" alt="Preview">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="send-image-btn">Send Image</button>
            </div>
        </div>
    </div>
</div>

<!-- Firebase Configuration and Chat Logic -->
<style>
/* Chat Message Line Break Support */
.chat-message-text {
    white-space: pre-line !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    word-break: break-word !important;
    line-height: 1.4;
    margin: 0;
}

/* Đảm bảo textarea giữ nguyên line breaks */
#message-input {
    white-space: pre-wrap;
    resize: vertical;
}
</style>

<script type="module">
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "{{ config('firebase.api_key') }}",
        authDomain: "{{ config('firebase.auth_domain') }}",
        databaseURL: "{{ config('firebase.database_url') }}",
        projectId: "{{ config('firebase.project_id') }}",
        storageBucket: "{{ config('firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
        appId: "{{ config('firebase.app_id') }}"
    };

    console.log('Sub Admin Chat - Firebase Config:', firebaseConfig);

    // Initialize Firebase
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js';
    import { getAuth, signInAnonymously } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-auth.js';
    import { getDatabase, ref, push, set, onValue, serverTimestamp, query, orderByChild, onDisconnect } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js';

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const database = getDatabase(app);

    // Chat variables
    const conversationId = "{{ $conversation->id }}";
    const currentUserId = {{ $user->id }};
    const otherUserId = {{ $otherParticipant->id }};
    const currentUserName = "{{ $user->name }}";
    const currentUserRole = "{{ $user->role }}";
    const currentUserPhoto = "{{ $user->photo ? asset($user->photo) : asset('backend/img/avatar.png') }}";
    const otherUserPhoto = "{{ $otherParticipant->photo ? asset($otherParticipant->photo) : asset('backend/img/avatar.png') }}";

    let selectedImageFile = null;

    // DOM elements
    const messagesContainer = document.getElementById('messages-container');
    const messagesList = document.getElementById('messages-list');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const imageInput = document.getElementById('image-input');
    const userStatus = document.getElementById('user-status');
    const typingIndicator = document.getElementById('typing-indicator');
    const typingUser = document.getElementById('typing-user');

    // Initialize chat without authentication (for testing)
    try {
        console.log('Initializing Sub Admin Firebase Chat...');
        initializeChat();
    } catch (error) {
        console.error('Firebase initialization failed:', error);
        alert('Failed to connect to chat system. Please refresh the page.');
    }

    function initializeChat() {
        console.log('Initializing sub admin chat for conversation:', conversationId);
        
        // Listen for messages
        const messagesRef = ref(database, `messages/${conversationId}`);
        const messagesQuery = query(messagesRef, orderByChild('timestamp'));
        
        onValue(messagesQuery, (snapshot) => {
            console.log('Sub Admin - Messages snapshot received');
            const messages = [];
            snapshot.forEach((childSnapshot) => {
                messages.push({
                    id: childSnapshot.key,
                    ...childSnapshot.val()
                });
            });
            console.log('Sub Admin - Messages loaded:', messages.length);
            displayMessages(messages);
            markMessagesAsRead();
        }, (error) => {
            console.error('Error listening to messages:', error);
        });

        // Listen for user presence
        const otherUserPresenceRef = ref(database, `userPresence/${otherUserId}`);
        onValue(otherUserPresenceRef, (snapshot) => {
            const presence = snapshot.val();
            if (presence) {
                if (presence.status === 'online') {
                    userStatus.innerHTML = '<span class="badge badge-success">Online</span>';
                } else {
                    const lastSeen = new Date(presence.lastSeen);
                    userStatus.innerHTML = `<span class="badge badge-secondary">Last seen: ${lastSeen.toLocaleString()}</span>`;
                }
            }
        });

        // Set current user presence
        const currentUserPresenceRef = ref(database, `userPresence/${currentUserId}`);
        set(currentUserPresenceRef, {
            status: 'online',
            lastSeen: serverTimestamp(),
            role: 'sub_admin'
        }).then(() => {
            console.log('Sub Admin presence set successfully');
        }).catch((error) => {
            console.error('Error setting sub admin presence:', error);
        });

        // Set offline when user disconnects
        onDisconnect(currentUserPresenceRef).set({
            status: 'offline',
            lastSeen: serverTimestamp(),
            role: 'sub_admin'
        });
    }

    function displayMessages(messages) {
        messagesList.innerHTML = '';
        messages.forEach(message => {
            const messageElement = createMessageElement(message);
            messagesList.appendChild(messageElement);
        });
        scrollToBottom();
    }

    function createMessageElement(message) {
        const messageDiv = document.createElement('div');
        const isCurrentUser = message.senderId == currentUserId;
        
        messageDiv.className = `mb-3 d-flex ${isCurrentUser ? 'justify-content-end' : 'justify-content-start'}`;
        
        const messageContent = document.createElement('div');
        messageContent.className = `p-3 rounded ${isCurrentUser ? 'bg-warning text-dark' : 'bg-white border'}`;
        messageContent.style.maxWidth = '70%';
        messageContent.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        
        let content = '';
        
        if (message.type === 'image') {
            content = `
                <img src="${message.imageUrl}" class="img-fluid rounded mb-2" style="max-width: 200px; cursor: pointer;" 
                     onclick="showImageModal('${message.imageUrl}')" alt="Image">
                ${message.content ? `<div class="chat-message-text">${escapeHtml(message.content)}</div>` : ''}
            `;
        } else {
            content = `<div class="chat-message-text">${escapeHtml(message.content)}</div>`;
        }
        
        const timestamp = message.timestamp ? new Date(message.timestamp).toLocaleString() : 'Sending...';
        const roleColor = message.senderRole === 'admin' ? 'text-danger' : (message.senderRole === 'sub_admin' ? 'text-warning' : 'text-info');
        
        content += `
            <div class="small ${isCurrentUser ? 'text-dark' : 'text-muted'} mt-2">
                <strong class="${isCurrentUser ? 'text-dark' : roleColor}">
                    ${message.senderRole === 'admin' ? '👑 ' : (message.senderRole === 'sub_admin' ? '🛡️ ' : '')}${escapeHtml(message.senderName)}
                </strong> • ${timestamp}
                ${message.readBy && message.readBy[`user_${otherUserId}`] ? '<i class="fas fa-check-double ms-1"></i>' : '<i class="fas fa-check ms-1"></i>'}
            </div>
        `;
        
        messageContent.innerHTML = content;
        messageDiv.appendChild(messageContent);
        
        return messageDiv;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function sendMessage(content, type = 'text', imageUrl = null, imageName = null) {
        console.log('Sub Admin sending message:', { content, type, imageUrl });
        
        const messageData = {
            id: `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            conversationId: conversationId,
            senderId: currentUserId,
            senderName: currentUserName,
            senderRole: currentUserRole,
            content: content,
            type: type,
            timestamp: serverTimestamp(),
            readBy: {
                [`user_${currentUserId}`]: serverTimestamp(),
                [`user_${otherUserId}`]: null
            }
        };

        if (type === 'image') {
            messageData.imageUrl = imageUrl;
            messageData.imageName = imageName;
        }

        // Add message to Firebase
        const messagesRef = ref(database, `messages/${conversationId}`);
        push(messagesRef, messageData)
            .then(() => {
                console.log('Sub Admin message sent successfully');
                // Notify backend about new message (Method B)
                notifyNewMessage(messageData);
            })
            .catch((error) => {
                console.error('Error sending sub admin message:', error);
                alert('Failed to send message. Please try again.');
            });

        // Update conversation last message
        const conversationRef = ref(database, `conversations/${conversationId}`);
        const conversationData = {
            id: conversationId,
            type: 'direct',
            participants: {
                [`user_${currentUserId}`]: {
                    id: currentUserId,
                    role: currentUserRole,
                    name: currentUserName,
                    avatar: currentUserPhoto,
                    parentSubAdminId: {{ $user->parent_sub_admin_id ?? 'null' }}
                },
                [`user_${otherUserId}`]: {
                    id: otherUserId,
                    role: "{{ $otherParticipant->role }}",
                    name: "{{ $otherParticipant->name }}",
                    avatar: otherUserPhoto,
                    parentSubAdminId: {{ $otherParticipant->parent_sub_admin_id ?? 'null' }}
                }
            },
            lastMessage: {
                id: messageData.id,
                senderId: currentUserId,
                content: content,
                type: type,
                timestamp: serverTimestamp()
            },
            unreadCount: {
                [`user_${currentUserId}`]: 0,
                [`user_${otherUserId}`]: 1
            },
            updatedAt: serverTimestamp()
        };

        set(conversationRef, conversationData)
            .then(() => {
                console.log('Sub Admin conversation updated successfully');
            })
            .catch((error) => {
                console.error('Error updating conversation:', error);
            });
    }

    function markMessagesAsRead() {
        // Mark messages as read by current user
        const messagesRef = ref(database, `messages/${conversationId}`);
        // Implementation can be added here if needed
    }

    // Event listeners
    messageForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const content = messageInput.value.trim();
        if (content) {
            sendMessage(content);
            messageInput.value = '';
        }
    });

    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });

    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            selectedImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('preview-image').src = e.target.result;
                const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                modal.show();
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('send-image-btn').addEventListener('click', () => {
        if (selectedImageFile) {
            uploadImage(selectedImageFile);
            const modal = bootstrap.Modal.getInstance(document.getElementById('imagePreviewModal'));
            modal.hide();
        }
    });

    function uploadImage(file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('conversation_id', conversationId);

        fetch('{{ route("chat.upload.image") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sendMessage('', 'image', data.imageUrl, data.imageName);
            } else {
                console.error('Upload failed:', data);
                alert('Failed to upload image');
            }
        })
        .catch(error => {
            console.error('Error uploading image:', error);
            alert('Failed to upload image');
        });
    }

    // Notify backend about new message (Method B)
    function notifyNewMessage(messageData) {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const payload = {
            conversation_id: conversationId,
            sender_id: messageData.senderId,
            sender_name: messageData.senderName,
            preview: messageData.type === 'image' ? '' : (messageData.content || ''),
            type: messageData.type,
            timestamp: Date.now()
        };

        fetch('/chat/notify-new-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Notification sent successfully:', data);
        })
        .catch(error => {
            console.error('Error sending notification:', error);
            // Don't show alert to user, just log the error
        });
    }

    // Global function for image modal
    window.showImageModal = function(imageUrl) {
        document.getElementById('preview-image').src = imageUrl;
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    };
</script>
@endsection

@push('styles')
<style>
    #messages-container {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
    }
    
    .border-warning {
        border-color: #ffc107 !important;
    }
    
    .border-warning:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .badge {
        font-size: 0.7em;
    }
    
    .rounded {
        border-radius: 0.5rem !important;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    code {
        font-size: 0.8em;
        color: #e83e8c;
    }
</style>
@endpush