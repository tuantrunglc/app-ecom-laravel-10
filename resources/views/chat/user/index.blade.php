@extends('user.layouts.master')

@section('title', 'User Chat System')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-comments"></i> Chat System</h4>
                    <div class="badge badge-info">User Panel</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Available Contacts Panel -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-address-book"></i> Your Support Contacts</h6>
                                    <small>Admin & your assigned Sub Admin</small>
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
                                                        <span class="badge badge-{{ $availableUser->role == 'admin' ? 'danger' : 'warning' }}">
                                                            {{ ucfirst(str_replace('_', ' ', $availableUser->role)) }}
                                                        </span>
                                                        @if($availableUser->role == 'admin')
                                                            <br><span class="text-xs text-danger">System Administrator</span>
                                                        @else
                                                            <br><span class="text-xs text-warning">Your assigned manager</span>
                                                        @endif
                                                    </small>
                                                </div>
                                                <div>
                                                    <form action="{{ route('chat.conversation.create') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="participant_id" value="{{ $availableUser->id }}">
                                                        <button type="submit" class="btn btn-sm btn-info">
                                                            <i class="fas fa-comment"></i> Chat
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-3 text-center text-muted">
                                            <i class="fas fa-user-times fa-2x mb-2"></i>
                                            <p>No support contacts available</p>
                                            <small>Please contact system administrator</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Active Conversations Panel -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-comments"></i> Your Conversations</h6>
                                    <small>Chat history with support team</small>
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
                                                                <span class="badge badge-{{ $otherParticipant->role == 'admin' ? 'danger' : 'warning' }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $otherParticipant->role)) }}
                                                                </span>
                                                                @if($otherParticipant->role == 'admin')
                                                                    <span class="text-danger">• System Admin</span>
                                                                @else
                                                                    <span class="text-warning">• Your Manager</span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                        <small class="text-muted">{{ $conversation->updated_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-1 text-muted small">Last activity: {{ $conversation->updated_at->format('M d, Y H:i') }}</p>
                                                </div>
                                                <div>
                                                    <a href="{{ route('chat.conversation', $conversation->id) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i> Open
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-4 text-center text-muted">
                                            <i class="fas fa-comment-slash fa-3x mb-3"></i>
                                            <h5>No Conversations Yet</h5>
                                            <p>Start a conversation with your support team by clicking "Chat" above.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-comments fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $conversations->count() }}</h4>
                                            <small>Total Conversations</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-headset fa-2x me-3"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $availableUsers->count() }}</h4>
                                            <small>Support Contacts</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Info & Guidelines -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle text-info"></i> Your Account Info</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> {{ $user->name }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Role:</strong> <span class="badge badge-info">User</span></p>
                                    @if($user->parentSubAdmin)
                                        <p><strong>Assigned Manager:</strong> {{ $user->parentSubAdmin->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6><i class="fas fa-lightbulb"></i> Chat Guidelines</h6>
                                <ul class="mb-0 small">
                                    <li>You can chat with <strong>System Admin</strong> for general support</li>
                                    <li>You can chat with your <strong>assigned Sub Admin</strong> for specific help</li>
                                    <li>Be respectful and clear in your messages</li>
                                    <li>Response times may vary based on availability</li>
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
    
    .alert-success {
        border-left: 4px solid #28a745;
    }
    
    .card.border-info {
        border-color: #17a2b8 !important;
    }
</style>
@endpush