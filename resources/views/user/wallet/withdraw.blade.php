@extends('user.layouts.master')

@section('title','Withdraw Money')

@section('main-content')
<div class="card">
    <h5 class="card-header">Withdrawal Request</h5>
    <div class="card-body">
        @include('user.layouts.notification')
        
        <div class="row">
            <div class="col-md-8">
                <!-- Display current balance -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-wallet"></i> Current Balance: <strong>{{ $user->formatted_balance }}</strong></h6>
                </div>

                @if(!$hasBankInfo)
                <!-- Bank Info Required Alert -->
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Bank Information Required</h5>
                    <p class="mb-3">You need to link your bank information before you can withdraw money. This helps ensure the safety and security of your transactions.</p>
                    <a href="{{ route('user-profile') }}" class="btn btn-primary">
                        <i class="fas fa-user-edit"></i> Update Bank Information
                    </a>
                </div>
                @else
                <!-- Bank Info Display -->
                <div class="alert alert-success">
                    <h6><i class="fas fa-university"></i> Linked Bank Information:</h6>
                    <ul class="mb-0">
                        <li><strong>Bank:</strong> {{ $user->bank_name }}</li>
                        <li><strong>Account Number:</strong> {{ $user->bank_account_number }}</li>
                        <li><strong>Account Holder:</strong> {{ $user->bank_account_name }}</li>
                    </ul>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        To change bank information, please update in your 
                        <a href="{{ route('user-profile') }}">Profile page</a>
                    </small>
                </div>
                @endif
                
                @if($hasBankInfo)
                <form method="post" action="{{ route('wallet.withdraw') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="amount">Withdrawal Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="amount" type="number" class="form-control @error('amount') is-invalid @enderror" 
                                   name="amount" value="{{ old('amount') }}" placeholder="Enter amount" 
                                   min="50" max="{{ $user->wallet_balance }}" step="1" required>
                            <div class="input-group-append">
                                <span class="input-group-text">USD</span>
                            </div>
                        </div>
                        @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            Minimum: $50.00 - Maximum: {{ $user->formatted_balance }}
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="withdrawal_password">Withdrawal Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="withdrawal_password" type="password" class="form-control @error('withdrawal_password') is-invalid @enderror" 
                                   name="withdrawal_password" placeholder="Enter 4-6 digit withdrawal password" 
                                   pattern="[0-9]{4,6}" maxlength="6" required>
                            @if(!$user->hasWithdrawalPassword())
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" id="createWithdrawalPasswordBtn" data-toggle="tooltip" title="Create withdrawal password">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                        @error('withdrawal_password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-shield-alt"></i> 
                            @if($user->hasWithdrawalPassword())
                                Enter your 4-6 digit withdrawal password for security verification
                            @else
                                <span class="text-warning">You don't have a withdrawal password yet. Click the + button to create one. <strong>Note: You can only create it once!</strong></span>
                            @endif
                        </small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-paper-plane"></i> Submit Withdrawal Request
                        </button>
                        <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
                @endif
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle text-info"></i> Withdrawal Instructions
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Enter complete account information</li>
                            <li><i class="fas fa-check text-success"></i> Double-check information before submitting</li>
                            <li><i class="fas fa-check text-success"></i> Customer service will process in 1-3 days</li>
                            <li><i class="fas fa-check text-success"></i> Money will be transferred to your account</li>
                        </ul>
                        
                        <hr>
                        
                        <h6 class="card-title">
                            <i class="fas fa-exclamation-triangle text-warning"></i> Important Notes
                        </h6>
                        <ul class="list-unstyled">
                            <li><small class="text-muted">• Account holder name must match ID card</small></li>
                            <li><small class="text-muted">• Double-check account number</small></li>
                            <li><small class="text-muted">• Cannot cancel after submitting request</small></li>
                            <li><small class="text-muted">• Withdrawal fee: 0 VND</small></li>
                        </ul>
                        
                        <hr>
                        
                        <h6 class="card-title">
                            <i class="fas fa-clock text-warning"></i> Processing Time
                        </h6>
                        <p class="card-text">
                            <small class="text-muted">
                                1-3 business days (Monday - Friday)
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Withdrawal Password Modal -->
<div class="modal fade" id="createWithdrawalPasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Withdrawal Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createWithdrawalPasswordForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> You can only create withdrawal password ONCE! Make sure to remember it as you cannot change it later.
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Create a 4-6 digit PIN for withdrawal security
                    </div>
                    <div class="form-group">
                        <label for="modal_withdrawal_password">Withdrawal Password</label>
                        <input type="password" class="form-control" id="modal_withdrawal_password" 
                               name="withdrawal_password" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Enter 4-6 digits">
                    </div>
                    <div class="form-group">
                        <label for="modal_withdrawal_password_confirmation">Confirm Password</label>
                        <input type="password" class="form-control" id="modal_withdrawal_password_confirmation" 
                               name="withdrawal_password_confirmation" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Confirm 4-6 digits">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let maxAmount = {{ $user->wallet_balance }};
    
    // Format amount when typing
    $('#amount').on('input', function() {
        let value = $(this).val();
        if (value) {
            let formatted = '$' + parseFloat(value).toFixed(2);
            $(this).attr('title', formatted);
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let amount = parseFloat($('#amount').val());
        
        if (amount < 50) {
            e.preventDefault();
            alert('Minimum amount is $50.00');
            return false;
        }
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Amount cannot exceed current balance');
            return false;
        }
        
        @if($hasBankInfo)
        let confirmMsg = 'Are you sure you want to withdraw $' + amount.toFixed(2) + ' to account:\n';
        confirmMsg += 'Bank: {{ $user->bank_name }}\n';
        confirmMsg += 'Account Number: {{ $user->bank_account_number }}\n';
        confirmMsg += 'Account Holder: {{ $user->bank_account_name }}\n\n';
        confirmMsg += 'Note: Cannot cancel after submitting request!';
        
        return confirm(confirmMsg);
        @else
        e.preventDefault();
        alert('Please link your bank information before withdrawing money!');
        return false;
        @endif
    });

    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Create Withdrawal Password Button
    $('#createWithdrawalPasswordBtn').on('click', function() {
        $('#createWithdrawalPasswordModal').modal('show');
    });

    // Create Withdrawal Password Form Submit
    $('#createWithdrawalPasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("user.create-withdrawal-password") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#createWithdrawalPasswordModal').modal('hide');
                    alert('Success: ' + response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = '';
                
                if (errors) {
                    $.each(errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });
                } else {
                    errorMessage = 'An error occurred!';
                }
                
                alert('Error: ' + errorMessage);
            }
        });
    });

    // Clear form when modal is hidden
    $('#createWithdrawalPasswordModal').on('hidden.bs.modal', function() {
        $('#createWithdrawalPasswordForm')[0].reset();
    });
});
</script>
@endpush