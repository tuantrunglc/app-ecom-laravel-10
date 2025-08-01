@extends('backend.layouts.master')

@section('title', 'Sub Admin Chat System')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-comments"></i> Sub Admin Chat System</h4>
                    <div class="badge badge-warning">Sub Admin Panel</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Available Users Panel -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-users"></i> Your Managed Users</h6>
                                    <small>Users under your management + Admin</small>
                                </div>
                                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                    @if($availableUsers->count() > 0)
                                        @foreach($availableUsers as $availableUser)
                                            <div class="list-group-item list-group-item-action d-flex align-items-center p-3 border-bottom">
                                                <img src="{{ $availableUser->photo ? asset($availableUser->photo) : asset('backend/img/avatar.png') }}" 
                                                     class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $availableUser->name }}</h6>
                                                    <small class="text-muted">
                                                        <span class="badge badge-{{ $availableUser->role == 'admin' ? 'danger' : ($availableUser->role == 'sub_admin' ? 'warning' : 'info') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $availableUser->role)) }}
                                                        </span>
                                                        @if($availableUser->role == 'admin')
                                                            <br><span class="text-xs text-success">Your supervisor</span>
                                                        @elseif($availableUser->role == 'user')
                                                            <br><span class="text-xs text-primary">Under your management</span>
                                                        @endif
                                                    </small>
                                                </div>
                                                <div>
                                                    <form action="{{ route('chat.conversation.create') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="participant_id" value="{{ $availableUser->id }}">
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-comment"></i> Chat
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-3 text-center text-muted">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <p>No users available for chat</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Active Conversations Panel -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-comments"></i> Active Conversations</h6>
                                    <small>Your ongoing chats</small>
                                </div>
                                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                    @if($conversations->count() > 0)
                                        @foreach($conversations as $conversation)
                                            @php
                                                $otherParticipant = $conversation->getOtherParticipant($user->id);
                                            @endphp
                                            <div class="list-group-item list-group-item-action d-flex align-items-center p-3 border-bottom">
                                                <img src="{{ $otherParticipant->photo ? asset($otherParticipant->photo) : asset('backend/img/avatar.png') }}" 
                                                     class="rounded-circle me-3" width="50" height="50" alt="Avatar">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">{{ $otherParticipant->name }}</h6>
                                                            <small class="text-muted">
                                                                <span class="badge badge-{{ $otherParticipant->role == 'admin' ? 'danger' : ($otherParticipant->role == 'sub_admin' ? 'warning' : 'info') }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $otherParticipant->role)) }}
                                                                </span>
                                                                @if($otherParticipant->role == 'admin')
                                                                    <span class="text-success">• Supervisor</span>
                                                                @elseif($otherParticipant->role == 'user')
                                                                    <span class="text-primary">• Your user</span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                        <small class="text-muted">{{ $conversation->updated_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-1 text-muted small">Last activity: {{ $conversation->updated_at->format('M d, Y H:i') }}</p>
                                                </div>
                                                <div>
                                                    <a href="{{ route('chat.conversation', $conversation->id) }}" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-eye"></i> Open
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-4 text-center text-muted">
                                            <i class="fas fa-comment-slash fa-3x mb-3"></i>
                                            <h5>No Active Conversations</h5>
                                            <p>Start a conversation by clicking "Chat" next to any user.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sub Admin Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $availableUsers->where('role', 'user')->count() }}</h4>
                                            <small>Managed Users</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-comments fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $conversations->count() }}</h4>
                                            <small>Active Chats</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-shield fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">1</h4>
                                            <small>Admin Contact</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Management Info -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Sub Admin Chat Permissions</h6>
                                <ul class="mb-0">
                                    <li>You can chat with <strong>Admin</strong> (your supervisor)</li>
                                    <li>You can chat with <strong>Users</strong> under your management</li>
                                    <li>You cannot chat with other Sub Admins or their users</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-size: 0.7em;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    .text-xs {
        font-size: 0.65rem;
    }
    
    .alert-info {
        border-left: 4px solid #17a2b8;
    }
</style>
@endpush