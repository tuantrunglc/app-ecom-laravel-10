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
});
</script>
@endpush