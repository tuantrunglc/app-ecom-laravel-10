@extends('backend.layouts.master')

@section('title','Chat with ' . $otherParticipant->name)

@section('main-content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('chat.index') }}" class="btn btn-sm btn-secondary me-3">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <img src="{{ $otherParticipant->photo ? asset($otherParticipant->photo) : asset('backend/img/avatar.png') }}" 
                 class="rounded-circle me-2" width="40" height="40" alt="Avatar">
            <div>
                <h6 class="mb-0">{{ $otherParticipant->name }}</h6>
                <small class="text-muted">
                    <span class="badge badge-{{ $otherParticipant->role == 'admin' ? 'danger' : ($otherParticipant->role == 'sub_admin' ? 'warning' : 'info') }}">
                        {{ ucfirst(str_replace('_', ' ', $otherParticipant->role)) }}
                    </span>
                    <span id="user-status" class="ms-2"></span>
                </small>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <!-- Messages Container -->
        <div id="messages-container" class="p-3" style="height: 500px; overflow-y: auto;">
            <div id="messages-list">
                <!-- Messages will be loaded here -->
            </div>
            <div id="typing-indicator" class="text-muted small" style="display: none;">
                <i class="fas fa-circle-notch fa-spin"></i> <span id="typing-user"></span> is typing...
            </div>
        </div>
        
        <!-- Message Input -->
        <div class="border-top p-3">
            <form id="message-form" class="d-flex align-items-end">
                <div class="flex-grow-1 me-2">
                    <textarea id="message-input" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Type your message..."
                              style="resize: none;"></textarea>
                </div>
                <div class="d-flex flex-column">
                    <label for="image-input" class="btn btn-outline-secondary btn-sm mb-1" title="Upload Image">
                        <i class="fas fa-image"></i>
                    </label>
                    <input type="file" id="image-input" accept="image/*" style="display: none;">
                    <button type="submit" class="btn btn-primary btn-sm" title="Send Message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="preview-image" src="" class="img-fluid" alt="Preview">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="send-image-btn">Send Image</button>
            </div>
        </div>
    </div>
</div>

<!-- Firebase Configuration and Chat Logic -->
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

    console.log('Firebase Config:', firebaseConfig);

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
        console.log('Initializing Firebase without authentication...');
        initializeChat();
    } catch (error) {
        console.error('Firebase initialization failed:', error);
        alert('Failed to connect to chat system. Please refresh the page.');
    }

    function initializeChat() {
        console.log('Initializing chat for conversation:', conversationId);
        
        // Listen for messages
        const messagesRef = ref(database, `messages/${conversationId}`);
        const messagesQuery = query(messagesRef, orderByChild('timestamp'));
        
        onValue(messagesQuery, (snapshot) => {
            console.log('Messages snapshot received');
            const messages = [];
            snapshot.forEach((childSnapshot) => {
                messages.push({
                    id: childSnapshot.key,
                    ...childSnapshot.val()
                });
            });
            console.log('Messages loaded:', messages.length);
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
            lastSeen: serverTimestamp()
        }).then(() => {
            console.log('User presence set successfully');
        }).catch((error) => {
            console.error('Error setting user presence:', error);
        });

        // Set offline when user disconnects
        onDisconnect(currentUserPresenceRef).set({
            status: 'offline',
            lastSeen: serverTimestamp()
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
        messageContent.className = `p-2 rounded ${isCurrentUser ? 'bg-primary text-white' : 'bg-light'}`;
        messageContent.style.maxWidth = '70%';
        
        let content = '';
        
        if (message.type === 'image') {
            content = `
                <img src="${message.imageUrl}" class="img-fluid rounded mb-2" style="max-width: 200px; cursor: pointer;" 
                     onclick="showImageModal('${message.imageUrl}')" alt="Image">
                ${message.content ? `<div>${escapeHtml(message.content)}</div>` : ''}
            `;
        } else {
            content = `<div>${escapeHtml(message.content)}</div>`;
        }
        
        const timestamp = message.timestamp ? new Date(message.timestamp).toLocaleString() : 'Sending...';
        
        content += `
            <div class="small ${isCurrentUser ? 'text-light' : 'text-muted'} mt-1">
                <strong>${escapeHtml(message.senderName)}</strong> • ${timestamp}
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
        console.log('Sending message:', { content, type, imageUrl });
        
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
                console.log('Message sent successfully');
            })
            .catch((error) => {
                console.error('Error sending message:', error);
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
                console.log('Conversation updated successfully');
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

    // Global function for image modal
    window.showImageModal = function(imageUrl) {
        document.getElementById('preview-image').src = imageUrl;
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    };

    // Test Firebase connection
    window.testFirebaseConnection = function() {
        console.log('Testing Firebase connection...');
        const testRef = ref(database, 'test');
        set(testRef, {
            message: 'Test connection',
            timestamp: serverTimestamp(),
            userId: currentUserId
        })
        .then(() => {
            console.log('Firebase connection test successful!');
            alert('Firebase connection is working!');
        })
        .catch((error) => {
            console.error('Firebase connection test failed:', error);
            alert('Firebase connection failed: ' + error.message);
        });
    };
</script>
@endsection

@push('styles')
<style>
    #messages-container {
        background-color: #f8f9fa;
    }
    
    .bg-primary {
        background-color: #007bff !important;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .badge {
        font-size: 0.7em;
    }
    
    .rounded {
        border-radius: 0.5rem !important;
    }
    
    #message-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endpush