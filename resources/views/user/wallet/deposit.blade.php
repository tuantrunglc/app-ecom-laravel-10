@extends('user.layouts.master')

@section('title','Deposit Money')

@section('main-content')
<div class="card">
    <h5 class="card-header">Deposit Request</h5>
    <div class="card-body">
        @include('user.layouts.notification')
        
        <div class="row">
            <div class="col-md-8">
                <form method="post" action="{{ route('wallet.deposit') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="amount">Deposit Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="amount" type="number" class="form-control @error('amount') is-invalid @enderror" 
                                   name="amount" value="{{ old('amount') }}" placeholder="Enter amount" 
                                   min="10" max="50000" step="1" required>
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
                            Minimum: $10.00 - Maximum: $50,000.00
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="note">Note (Optional)</label>
                        <textarea id="note" class="form-control @error('note') is-invalid @enderror" 
                                  name="note" rows="3" placeholder="Additional note (if any)">{{ old('note') }}</textarea>
                        @error('note')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Maximum 500 characters</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane"></i> Submit Deposit Request
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
                            <i class="fas fa-info-circle text-info"></i> Deposit Instructions
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Enter deposit amount</li>
                            <li><i class="fas fa-check text-success"></i> Submit request</li>
                            <li><i class="fas fa-check text-success"></i> Customer service will contact you</li>
                            <li><i class="fas fa-check text-success"></i> Follow transfer instructions</li>
                            <li><i class="fas fa-check text-success"></i> Money will be added after confirmation</li>
                        </ul>
                        
                        <hr>
                        
                        <h6 class="card-title">
                            <i class="fas fa-clock text-warning"></i> Processing Time
                        </h6>
                        <p class="card-text">
                            <small class="text-muted">
                                Customer service will contact you within 30 minutes - 2 hours during business hours.
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
    // Format amount when typing
    $('#amount').on('input', function() {
        let value = $(this).val();
        if (value) {
            // Display formatted amount
            let formatted = '$' + parseFloat(value).toFixed(2);
            $(this).attr('title', formatted);
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let amount = parseFloat($('#amount').val());
        
        if (amount < 10) {
            e.preventDefault();
            alert('Minimum amount is $10.00');
            return false;
        }
        
        if (amount > 50000) {
            e.preventDefault();
            alert('Maximum amount is $50,000.00');
            return false;
        }
        
        return confirm('Are you sure you want to submit a deposit request for $' + amount.toFixed(2) + '?');
    });
});
</script>
@endpush