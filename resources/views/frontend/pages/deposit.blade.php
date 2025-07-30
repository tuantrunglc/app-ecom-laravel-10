@extends('frontend.layouts.master')

@section('title','Request Deposit - E-SHOP')

@section('main-content')
<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="bread-inner">
                    <ul class="bread-list">
                        <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                        <li class="active"><a href="javascript:void(0);">Request Deposit</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Breadcrumbs -->

<!-- Deposit Request Section -->
<section class="shop-services section home">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-10 col-12 mx-auto">
                <div class="section-title">
                    <h2>Request Deposit</h2>
                    <p>Add money to your wallet quickly and securely</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 col-md-12 col-12 mx-auto">
                <!-- Deposit Form Card -->
                <div class="deposit-card">
                    @include('frontend.layouts.notification')
                    
                    <!-- Current Balance Display -->
                    @auth
                    <div class="balance-display">
                        <div class="balance-info">
                            <i class="fa fa-wallet"></i>
                            <div class="balance-text">
                                <h4>Current Balance</h4>
                                <h2>{{ Auth::user()->formatted_balance }}</h2>
                            </div>
                        </div>
                    </div>
                    @endauth
                    
                    <!-- Deposit Form -->
                    <div class="deposit-form-section">
                        <form method="post" action="{{ route('wallet.deposit') }}" class="deposit-form">
                            @csrf
                            <input type="hidden" name="from_frontend" value="1">
                            
                            <div class="form-group">
                                <label for="amount">Deposit Amount <span class="required">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-money"></i></span>
                                    </div>
                                    <input id="amount" type="number" class="form-control @error('amount') is-invalid @enderror" 
                                           name="amount" value="{{ old('amount') }}" placeholder="Enter amount" 
                                           min="10" max="50000" step="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">USD</span>
                                    </div>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Minimum: 10 USD - Maximum: 50,000,000 USD
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="note">Additional Note (Optional)</label>
                                <textarea id="note" class="form-control @error('note') is-invalid @enderror" 
                                          name="note" rows="4" placeholder="Any additional information...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum 500 characters</small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-paper-plane"></i> Submit Request
                                </button>
                                <a href="{{ route('home') }}" class="btn btn-secondary btn-lg">
                                    <i class="fa fa-arrow-left"></i> Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- How it Works Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="how-it-works">
                    <h3>How It Works</h3>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="work-step">
                                <div class="step-icon">
                                    <i class="fa fa-edit"></i>
                                    <span class="step-number">1</span>
                                </div>
                                <h4>Submit Request</h4>
                                <p>Fill out the deposit form with your desired amount</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="work-step">
                                <div class="step-icon">
                                    <i class="fa fa-phone"></i>
                                    <span class="step-number">2</span>
                                </div>
                                <h4>We Contact You</h4>
                                <p>Our customer service will contact you within 30 minutes</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="work-step">
                                <div class="step-icon">
                                    <i class="fa fa-credit-card"></i>
                                    <span class="step-number">3</span>
                                </div>
                                <h4>Make Payment</h4>
                                <p>Follow the transfer instructions provided by our team</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="work-step">
                                <div class="step-icon">
                                    <i class="fa fa-check-circle"></i>
                                    <span class="step-number">4</span>
                                </div>
                                <h4>Money Added</h4>
                                <p>Your wallet balance will be updated after confirmation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Deposit Request Section -->
@endsection

@push('styles')
<style>
    /* Deposit Page Styles */
    .deposit-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 40px;
        margin-bottom: 30px;
    }

    .balance-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        color: white;
    }

    .balance-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .balance-info i {
        font-size: 40px;
        opacity: 0.8;
    }

    .balance-text h4 {
        margin: 0;
        font-size: 16px;
        opacity: 0.9;
        font-weight: 400;
    }

    .balance-text h2 {
        margin: 5px 0 0 0;
        font-size: 32px;
        font-weight: 700;
    }

    .deposit-form-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 30px;
    }

    .deposit-form .form-group {
        margin-bottom: 25px;
    }

    .deposit-form label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .deposit-form .required {
        color: #dc3545;
    }

    .deposit-form .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .deposit-form .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .deposit-form .input-group-text {
        background: #667eea;
        color: white;
        border: 2px solid #667eea;
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }

    .form-actions .btn {
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .form-actions .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .form-actions .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    /* How it Works Section */
    .how-it-works {
        background: #fff;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .how-it-works h3 {
        text-align: center;
        margin-bottom: 40px;
        color: #333;
        font-weight: 700;
    }

    .work-step {
        text-align: center;
        padding: 20px;
    }

    .step-icon {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .step-icon i {
        font-size: 40px;
        color: #667eea;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 50%;
        border: 3px solid #e9ecef;
    }

    .step-number {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #28a745;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .work-step h4 {
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .work-step p {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .deposit-card {
            padding: 20px;
        }
        
        .deposit-form-section {
            padding: 20px;
        }
        
        .balance-info {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .form-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Format amount when typing
    $('#amount').on('input', function() {
        let value = $(this).val();
        if (value) {
            let formatted = '$' + parseFloat(value).toFixed(2);
            $(this).attr('title', formatted);
        }
    });
    
    // Form validation
    $('.deposit-form').on('submit', function(e) {
        let amount = parseFloat($('#amount').val());
        
        if (amount < 10) {
            e.preventDefault();
            alert('Minimum amount is $10.00');
            $('#amount').focus();
            return false;
        }
        
        if (amount > 50000) {
            e.preventDefault();
            alert('Maximum amount is $50,000.00');
            $('#amount').focus();
            return false;
        }
        
        // Confirmation dialog
        let confirmMsg = 'Are you sure you want to submit a deposit request for $' + 
                        amount.toFixed(2) + '?\n\n' +
                        'Our customer service team will contact you shortly with payment instructions.';
        
        return confirm(confirmMsg);
    });
    
    // Auto-focus on amount field
    $('#amount').focus();
});
</script>
@endpush