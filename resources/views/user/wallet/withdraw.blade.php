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
                        <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
                        <input id="bank_name" type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                               name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g: Vietcombank, BIDV, Techcombank..." required>
                        @error('bank_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bank_account">Account Number <span class="text-danger">*</span></label>
                        <input id="bank_account" type="text" class="form-control @error('bank_account') is-invalid @enderror" 
                               name="bank_account" value="{{ old('bank_account') }}" placeholder="Enter account number" required>
                        @error('bank_account')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="account_name">Account Holder Name <span class="text-danger">*</span></label>
                        <input id="account_name" type="text" class="form-control @error('account_name') is-invalid @enderror" 
                               name="account_name" value="{{ old('account_name') }}" placeholder="Account holder name (as per ID card)" required>
                        @error('account_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Name must match the bank account</small>
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
        
        // Check bank information
        if (!$('#bank_name').val().trim()) {
            e.preventDefault();
            alert('Please enter bank name');
            $('#bank_name').focus();
            return false;
        }
        
        if (!$('#bank_account').val().trim()) {
            e.preventDefault();
            alert('Please enter account number');
            $('#bank_account').focus();
            return false;
        }
        
        if (!$('#account_name').val().trim()) {
            e.preventDefault();
            alert('Please enter account holder name');
            $('#account_name').focus();
            return false;
        }
        
        let confirmMsg = 'Are you sure you want to withdraw $' + amount.toFixed(2) + ' to account:\n';
        confirmMsg += 'Bank: ' + $('#bank_name').val() + '\n';
        confirmMsg += 'Account: ' + $('#bank_account').val() + '\n';
        confirmMsg += 'Holder: ' + $('#account_name').val() + '\n\n';
        confirmMsg += 'Note: Cannot cancel after submitting request!';
        
        return confirm(confirmMsg);
    });
});
</script>
@endpush