@extends('backend.layouts.master')

@section('title','Chat System')

@section('main-content')
<div class="card">
    <h5 class="card-header">Chat System</h5>
    <div class="card-body">
        <div class="row">
            <!-- Sidebar - Available Users -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">Available Users</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($availableUsers as $availableUser)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $availableUser->photo ? asset($availableUser->photo) : asset('backend/img/avatar.png') }}" 
                                                 class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                                            <div>
                                                <h6 class="mb-1">{{ $availableUser->name }}</h6>
                                                <small class="text-muted">
                                                    <span class="badge badge-{{ $availableUser->role == 'admin' ? 'danger' : ($availableUser->role == 'sub_admin' ? 'warning' : 'info') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $availableUser->role)) }}
                                                    </span>
                                                    @if($availableUser->role == 'user' && $availableUser->parentSubAdmin)
                                                        <br><small>Managed by: {{ $availableUser->parentSubAdmin->name }}</small>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div>
                                            <form action="{{ route('chat.conversation.create') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="participant_id" value="{{ $availableUser->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-comment"></i> Chat
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted">
                                    No users available for chat
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0">Recent Conversations</h6>
                    </div>
                    <div class="card-body">
                        @if($conversations->count() > 0)
                            <div class="list-group">
                                @foreach($conversations as $conversation)
                                    @php
                                        $otherParticipant = $conversation->getOtherParticipant($user->id);
                                    @endphp
                                    <a href="{{ route('chat.conversation', $conversation->id) }}" 
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $otherParticipant->photo ? asset($otherParticipant->photo) : asset('backend/img/avatar.png') }}" 
                                                     class="rounded-circle me-2" width="40" height="40" alt="Avatar">
                                                <div>
                                                    <h6 class="mb-1">{{ $otherParticipant->name }}</h6>
                                                    <small class="text-muted">
                                                        <span class="badge badge-{{ $otherParticipant->role == 'admin' ? 'danger' : ($otherParticipant->role == 'sub_admin' ? 'warning' : 'info') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $otherParticipant->role)) }}
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $conversation->updated_at->diffForHumans() }}</small>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <h5>No conversations yet</h5>
                                <p>Start a conversation by clicking the "Chat" button next to a user.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Firebase Configuration -->
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

    // Initialize Firebase
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js';
    import { getAuth, signInWithCustomToken } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-auth.js';
    import { getDatabase, ref, onValue } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js';

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const database = getDatabase(app);

    // Sign in anonymously (simpler approach)
    import { signInAnonymously } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-auth.js';
    
    signInAnonymously(auth)
        .then((userCredential) => {
            console.log('Firebase authenticated successfully');
            
            // Set user presence
            const userId = {{ $user->id }};
            const userPresenceRef = ref(database, `userPresence/${userId}`);
            
            // Update presence status
            import { set, serverTimestamp, onDisconnect } from 'https://www.gstatic.com/firebasejs/9.0.0/firebase-database.js';
            
            set(userPresenceRef, {
                status: 'online',
                lastSeen: serverTimestamp()
            });

            // Set offline when user disconnects
            onDisconnect(userPresenceRef).set({
                status: 'offline',
                lastSeen: serverTimestamp()
            });
        })
        .catch((error) => {
            console.error('Firebase authentication failed:', error);
        });
</script>
@endsection

@push('styles')
<style>
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .badge {
        font-size: 0.7em;
    }
    
    .card-body {
        max-height: 600px;
        overflow-y: auto;
    }
</style>
@endpush