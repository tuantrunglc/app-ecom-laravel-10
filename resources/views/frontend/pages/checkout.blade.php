@extends('frontend.layouts.master')

@section('title','Checkout page')

@push('styles')
<style>
    /* Checkout page styling */
    .checkout .order-details {
        background: #fff;
        padding: 30px;
        border: 1px solid #eee;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .checkout .single-widget h2 {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #F7941D;
    }
    
    .checkout-title {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .checkout-title h2 {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    
    .checkout-title p {
        color: #666;
        font-size: 16px;
    }
</style>
@endpush

@section('main-content')

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0)">Checkout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->
            
    <!-- Start Checkout -->
    <section class="shop checkout section">
        <div class="container">
            <div class="checkout-title">
                <h2>Complete Your Order</h2>
                <p>Review your order details and proceed with payment</p>
            </div>
            <form class="form" method="POST" action="{{route('cart.order')}}">
                @csrf
                <div class="row justify-content-center"> 
                    <div class="col-lg-6 col-md-8 col-12">
                        <div class="order-details">
                            <!-- Order Widget -->
                            <div class="single-widget">
                                @if(isset($isBuyNow) && $isBuyNow && isset($buyNowItem))
                                    <!-- Buy Now Order Summary -->
                                    <h2>ORDER SUMMARY</h2>
                                    <div class="content">
                                        <!-- Buy Now Product Details -->
                                        <div class="buy-now-product mb-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
                                            <div class="row align-items-center">
                                                <div class="col-3">
                                                    @php 
                                                        $photo = explode(',', $buyNowItem['photo']);
                                                    @endphp
                                                    <img src="{{$photo[0]}}" alt="{{$buyNowItem['title']}}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                                </div>
                                                <div class="col-9">
                                                    <h6 class="mb-1">{{$buyNowItem['title']}}</h6>
                                                    <small class="text-muted">Quantity: {{$buyNowItem['quantity']}}</small><br>
                                                    <small class="text-muted">Price: ${{number_format($buyNowItem['discount_price'], 2)}}</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <ul>
                                            <li class="order_subtotal" data-price="{{$buyNowItem['amount']}}">Subtotal<span>${{number_format($buyNowItem['amount'],2)}}</span></li>
                                            <li class="shipping">
                                                Shipping Cost
                                                @if(count(Helper::shipping())>0)
                                                    <select name="shipping" class="nice-select">
                                                        <option value="">Select your address</option>
                                                        @foreach(Helper::shipping() as $shipping)
                                                        <option value="{{$shipping->id}}" class="shippingOption" data-price="{{$shipping->price}}">{{$shipping->type}}: ${{$shipping->price}}</option>
                                                        @endforeach
                                                    </select>
                                                @else 
                                                    <span>Free</span>
                                                @endif
                                            </li>
                                            
                                            @if(session('coupon'))
                                            <li class="coupon_price" data-price="{{session('coupon')['value']}}">You Save<span>${{number_format(session('coupon')['value'],2)}}</span></li>
                                            @endif
                                            @php
                                                $total_amount = $buyNowItem['amount'];
                                                if(session('coupon')){
                                                    $total_amount = $total_amount - session('coupon')['value'];
                                                }
                                            @endphp
                                            <li class="last" id="order_total_price">Total<span>${{number_format($total_amount,2)}}</span></li>
                                        </ul>
                                        <!-- Hidden inputs for Buy Now -->
                                        <input type="hidden" name="buy_now_mode" value="1">
                                        <input type="hidden" name="buy_now_product_id" value="{{$buyNowItem['product_id']}}">
                                        <input type="hidden" name="buy_now_quantity" value="{{$buyNowItem['quantity']}}">
                                        <input type="hidden" name="buy_now_amount" value="{{$buyNowItem['amount']}}">
                                    </div>
                                @else
                                    <!-- Regular Cart Summary -->
                                    <h2>CART TOTALS</h2>
                                    <div class="content">
                                        <ul>
                                            <li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Cart Subtotal<span>${{number_format(Helper::totalCartPrice(),2)}}</span></li>
                                            <li class="shipping">
                                                Shipping Cost
                                                @if(count(Helper::shipping())>0 && Helper::cartCount()>0)
                                                    <select name="shipping" class="nice-select">
                                                        <option value="">Select your address</option>
                                                        @foreach(Helper::shipping() as $shipping)
                                                        <option value="{{$shipping->id}}" class="shippingOption" data-price="{{$shipping->price}}">{{$shipping->type}}: ${{$shipping->price}}</option>
                                                        @endforeach
                                                    </select>
                                                @else 
                                                    <span>Free</span>
                                                @endif
                                            </li>
                                            
                                            @if(session('coupon'))
                                            <li class="coupon_price" data-price="{{session('coupon')['value']}}">You Save<span>${{number_format(session('coupon')['value'],2)}}</span></li>
                                            @endif
                                            @php
                                                $total_amount=Helper::totalCartPrice();
                                                if(session('coupon')){
                                                    $total_amount=$total_amount-session('coupon')['value'];
                                                }
                                            @endphp
                                            @if(session('coupon'))
                                                <li class="last"  id="order_total_price">Total<span>${{number_format($total_amount,2)}}</span></li>
                                            @else
                                                <li class="last"  id="order_total_price">Total<span>${{number_format($total_amount,2)}}</span></li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <!--/ End Order Widget -->
                            <!-- Order Widget -->
                            <div class="single-widget">
                                <h2>Payments</h2>
                                <div class="content">
                                    <div class="checkbox">
                                        <form-group>
                                            <input name="payment_method" type="radio" value="wallet" checked required> 
                                            <label> Wallet Payment (Balance: ${{number_format(auth()->user()->wallet_balance ?? 0, 2)}})</label>
                                            <div id="wallet-error" class="text-danger mt-2" style="display: none;"></div>
                                        </form-group>
                                        
                                    </div>
                                </div>
                            </div>
                            <!--/ End Order Widget -->
                            <!-- Button Widget -->
                            <div class="single-widget get-button">
                                <div class="content">
                                    <div class="button">
                                        <button type="submit" class="btn">proceed to checkout</button>
                                    </div>
                                </div>
                            </div>
                            <!--/ End Button Widget -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!--/ End Checkout -->
    
    <!-- Start Shop Services Area  -->
    <section class="shop-services section home">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-rocket"></i>
                        <h4>Free shiping</h4>
                        <p>Orders over $100</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-reload"></i>
                        <h4>Free Return</h4>
                        <p>Within 30 days returns</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-lock"></i>
                        <h4>Sucure Payment</h4>
                        <p>100% secure payment</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-tag"></i>
                        <h4>Best Peice</h4>
                        <p>Guaranteed price</p>
                    </div>
                    <!-- End Single Service -->
                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Services -->
    
    <!-- Start Shop Newsletter  -->
    <section class="shop-newsletter section">
        <div class="container">
            <div class="inner-top">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2 col-12">
                        <!-- Start Newsletter Inner -->
                        <div class="inner">
                            <h4>Newsletter</h4>
                            <p> Subscribe to our newsletter and get <span>10%</span> off your first purchase</p>
                            <form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">
                                <input name="EMAIL" placeholder="Your email address" required="" type="email">
                                <button class="btn">Subscribe</button>
                            </form>
                        </div>
                        <!-- End Newsletter Inner -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Newsletter -->
@endsection
@push('styles')
	<style>
		li.shipping{
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}
		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}
		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}
		.form-select {
			height: 30px;
			width: 100%;
		}
		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}
		.list li{
			margin-bottom:0 !important;
		}
		.list li:hover{
			background:#F7941D !important;
			color:white !important;
		}
		.form-select .nice-select::after {
			top: 14px;
		}
		#wallet-error {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			border-radius: 8px;
			padding: 15px;
			margin-top: 15px;
			color: #721c24;
			font-size: 14px;
			line-height: 1.5;
		}
		#wallet-error i {
			color: #721c24;
			margin-right: 8px;
			font-size: 16px;
		}
		#wallet-error strong {
			display: block;
			margin-bottom: 8px;
			font-size: 15px;
		}
		#wallet-error small {
			display: block;
			margin-top: 5px;
			margin-bottom: 10px;
		}
		#wallet-error .btn {
			font-size: 12px;
			padding: 6px 12px;
		}
		.btn-secondary {
			background-color: #6c757d !important;
			border-color: #6c757d !important;
			cursor: not-allowed !important;
			opacity: 0.7;
		}
		.btn-secondary:hover {
			background-color: #6c757d !important;
			border-color: #6c757d !important;
		}
	</style>
@endpush
@push('scripts')
	<script src="{{asset('frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
	<script>
		$(document).ready(function() { 
			// Initialize nice-select for shipping dropdown
			$('select.nice-select').niceSelect();
		});
	</script>
	
	<!-- Shipping and Wallet Balance Script -->
	<script>
		$(document).ready(function(){
			// Wallet balance from server
			const walletBalance = {{auth()->user()->wallet_balance ?? 0}};
			
			// Function to check wallet balance
			function checkWalletBalance() {
				try {
					// Get shipping cost
					let cost = parseFloat( $('.shipping select[name=shipping] option:selected').data('price') ) || 0;
					
					// Get subtotal - try to find the element with data-price
					let subtotalElement = $('.order_subtotal[data-price]');
					let subtotal = 0;
					if (subtotalElement.length > 0) {
						subtotal = parseFloat(subtotalElement.data('price')) || 0;
					} else {
						// Fallback: try to parse from the span text
						let subtotalText = $('.order_subtotal span').text().replace(/[$,]/g, '');
						subtotal = parseFloat(subtotalText) || 0;
					}
					
					// Additional fallback: try to get from total price if subtotal is 0
					if (subtotal === 0) {
						let totalText = $('#order_total_price span').text().replace(/[$,]/g, '');
						subtotal = parseFloat(totalText) || 0;
					}
					
					// Get coupon discount
					let coupon = parseFloat( $('.coupon_price').data('price') ) || 0; 
					let totalAmount = subtotal + cost - coupon;
				
				// Debug logging
				console.log('Wallet Balance Check:', {
					walletBalance: walletBalance,
					subtotal: subtotal,
					cost: cost,
					coupon: coupon,
					totalAmount: totalAmount,
					subtotalElement: subtotalElement.length
				});
				
				const errorDiv = $('#wallet-error');
				const submitBtn = $('button[type="submit"]');
				
				if (totalAmount > 0 && walletBalance < totalAmount) {
					errorDiv.html(`
						<i class="fa fa-exclamation-triangle"></i> 
						<strong>Insufficient wallet balance!</strong><br>
						Your balance: $${walletBalance.toFixed(2)}<br>
						Required: $${totalAmount.toFixed(2)}<br>
						<small>You need to add $${(totalAmount - walletBalance).toFixed(2)} to your wallet.</small><br>
						<a href="{{ route('wallet.index') }}" class="btn btn-sm btn-primary mt-2" target="_blank">
							<i class="fa fa-wallet"></i> Add Funds to Wallet
						</a>
					`).show();
					submitBtn.prop('disabled', true).addClass('btn-secondary').removeClass('btn');
					return false;
				} else {
					errorDiv.hide();
					submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn');
					return true;
				}
			} catch (error) {
				console.error('Error in checkWalletBalance:', error);
				// If there's an error, allow the form to proceed (server will validate)
				$('#wallet-error').hide();
				$('button[type="submit"]').prop('disabled', false).removeClass('btn-secondary').addClass('btn');
				return true;
			}
		}
			
			// Combined shipping change handler
			$('.shipping select[name=shipping]').on('change', function(){
				let cost = parseFloat( $(this).find('option:selected').data('price') ) || 0;
				
				// Get subtotal using same logic as checkWalletBalance
				let subtotalElement = $('.order_subtotal[data-price]');
				let subtotal = 0;
				if (subtotalElement.length > 0) {
					subtotal = parseFloat(subtotalElement.data('price')) || 0;
				} else {
					let subtotalText = $('.order_subtotal span').text().replace(/[$,]/g, '');
					subtotal = parseFloat(subtotalText) || 0;
				}
				
				let coupon = parseFloat( $('.coupon_price').data('price') ) || 0; 
				
				// Update total price display
				$('#order_total_price span').text('$'+(subtotal + cost-coupon).toFixed(2));
				
				// Check wallet balance after price update
				setTimeout(checkWalletBalance, 100);
			});
			
			// Check on page load and when DOM is ready
			checkWalletBalance(); // Immediate check
			setTimeout(checkWalletBalance, 500);
			setTimeout(checkWalletBalance, 1500);
			setTimeout(checkWalletBalance, 3000); // Final check after everything loads
			
			// Check when nice-select is initialized
			$(document).on('niceselect:updated', function() {
				setTimeout(checkWalletBalance, 100);
			});
			
			// Also check when coupon is applied/removed
			$(document).on('DOMSubtreeModified', '#order_total_price', function() {
				setTimeout(checkWalletBalance, 100);
			});
			
			// Check when any input changes
			$('input, select').on('change', function() {
				setTimeout(checkWalletBalance, 100);
			});
			
			// Force check when window loads
			$(window).on('load', function() {
				setTimeout(checkWalletBalance, 500);
			});
			
			// Prevent form submission if insufficient balance
			$('form').on('submit', function(e) {
				if (!checkWalletBalance()) {
					e.preventDefault();
					$('html, body').animate({
						scrollTop: $('#wallet-error').offset().top - 100
					}, 500);
					return false;
				}
			});

		});
	</script>

@endpush