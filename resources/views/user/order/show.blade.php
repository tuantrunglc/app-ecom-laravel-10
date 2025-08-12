@extends('user.layouts.master')

@section('title','Order Detail')

@section('main-content')
<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Order Details</h1>
    <p class="text-muted mb-0">
      <a href="{{route('user.order.index')}}" class="text-walmart-blue">My Orders</a> 
      <i class="fas fa-chevron-right mx-2 text-muted"></i> 
      Order #{{$order->order_number}}
    </p>
  </div>
  <div class="d-flex">
    <a href="{{route('order.pdf',$order->id)}}" class="walmart-btn walmart-btn-secondary mr-2">
      <i class="fas fa-download mr-2"></i>
      Download PDF
    </a>
    <a href="{{route('user.order.index')}}" class="walmart-btn walmart-btn-primary">
      <i class="fas fa-arrow-left mr-2"></i>
      Back to Orders
    </a>
  </div>
</div>

@if($order)
<!-- Order Status Card -->
<div class="walmart-card mb-4">
  <div class="card-body">

    <table class="table table-striped table-hover">
      <thead>
        <tr>
            <th>S.N.</th>
            <th>Order No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Quantity</th>
            <th>Charge</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
            <td>{{$order->id}}</td>
            <td>{{$order->order_number}}</td>
            <td>{{$order->first_name}} {{$order->last_name}}</td>
            <td>{{$order->email}}</td>
            <td>{{$order->quantity}}</td>
            <td>${{$order->shipping ? $order->shipping->price : '0.00'}}</td>
            <td>${{number_format($order->total_amount,2)}}</td>
            <td>
                @if($order->status=='new')
                  <span class="badge badge-primary">{{$order->status}}</span>
                @elseif($order->status=='process')
                  <span class="badge badge-warning">{{$order->status}}</span>
                @elseif($order->status=='delivered')
                  <span class="badge badge-success">{{$order->status}}</span>
                @else
                  <span class="badge badge-danger">{{$order->status}}</span>
                @endif
            </td>
            <td>
                <form method="POST" action="{{route('order.destroy',[$order->id])}}">
                  @csrf
                  @method('delete')
                      <button class="btn btn-danger btn-sm dltBtn" data-id={{$order->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </form>
            </td>

        </tr>
      </tbody>
    </table>

    <section class="confirmation_part section_padding">
      <div class="order_boxes">
        <div class="row">
          <div class="col-lg-6 col-lx-4">
            <div class="order-info">
              <h4 class="text-center pb-4">ORDER INFORMATION</h4>
              <table class="table">
                    <tr class="">
                        <td>Order Number</td>
                        <td> : {{$order->order_number}}</td>
                    </tr>
                    <tr>
                        <td>Order Date</td>
                        <td> : {{$order->created_at->format('D d M, Y')}} at {{$order->created_at->format('g : i a')}} </td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td> : {{$order->quantity}}</td>
                    </tr>
                    <tr>
                        <td>Order Status</td>
                        <td> : {{$order->status}}</td>
                    </tr>
                    <tr>
                      @php
                          $shipping_charge=DB::table('shippings')->where('id',$order->shipping_id)->pluck('price');
                      @endphp
                        <td>Shipping Charge</td>
                        <td> :${{$order->shipping ? $order->shipping->price : '0.00'}}</td>
                    </tr>
                    <tr>
                        <td>Total Amount</td>
                        <td> : $ {{number_format($order->total_amount,2)}}</td>
                    </tr>
                    <tr>
                      <td>Payment Method</td>
                      <td> : @if($order->payment_method=='wallet') Wallet Payment @elseif($order->payment_method=='cod') Cash on Delivery @else Paypal @endif</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td> : {{$order->payment_status}}</td>
                    </tr>
              </table>
            </div>
          </div>
          <div>
            <h4 class="mb-1">Order #{{$order->order_number}}</h4>
            <p class="text-muted mb-2">Placed on {{$order->created_at->format('F d, Y')}} at {{$order->created_at->format('g:i A')}}</p>
            <div>
              @if($order->status=='new')
                <span class="status-badge new">New Order</span>
              @elseif($order->status=='process')
                <span class="status-badge process">Processing</span>
              @elseif($order->status=='delivered')
                <span class="status-badge delivered">Delivered</span>
              @else
                <span class="status-badge cancelled">{{ucfirst($order->status)}}</span>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-md-right">
        <div class="order-total">
          <h2 class="text-walmart-blue mb-0">${{number_format($order->total_amount,2)}}</h2>
          <p class="text-muted mb-0">Total Amount</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Order Information -->
<div class="row">
  <div class="col-lg-6 mb-4">
    <div class="walmart-card h-100">
      <div class="card-header">
        <h4 class="card-title">
          <i class="fas fa-info-circle mr-2"></i>
          Order Information
        </h4>
      </div>
      <div class="card-body">
        <div class="order-info-list">
          <div class="info-item">
            <span class="info-label">Order Number:</span>
            <span class="info-value font-weight-bold">{{$order->order_number}}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Order Date:</span>
            <span class="info-value">{{$order->created_at->format('D, M d, Y')}} at {{$order->created_at->format('g:i A')}}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Quantity:</span>
            <span class="info-value">{{$order->quantity}} item(s)</span>
          </div>
          <div class="info-item">
            <span class="info-label">Order Status:</span>
            <span class="info-value">
              @if($order->status=='new')
                <span class="status-badge new">New</span>
              @elseif($order->status=='process')
                <span class="status-badge process">Processing</span>
              @elseif($order->status=='delivered')
                <span class="status-badge delivered">Delivered</span>
              @else
                <span class="status-badge cancelled">{{ucfirst($order->status)}}</span>
              @endif
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Shipping Charge:</span>
            <span class="info-value">${{number_format($order->shipping ? $order->shipping->price : 0, 2)}}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Payment Method:</span>
            <span class="info-value">
              @if($order->payment_method=='cod') 
                <i class="fas fa-money-bill-wave mr-1"></i>Cash on Delivery 
              @else 
                <i class="fab fa-paypal mr-1"></i>PayPal 
              @endif
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Payment Status:</span>
            <span class="info-value">
              @if($order->payment_status == 'paid')
                <span class="badge badge-success">Paid</span>
              @else
                <span class="badge badge-warning">{{ucfirst($order->payment_status)}}</span>
              @endif
            </span>
          </div>
          <div class="info-item border-top pt-3 mt-3">
            <span class="info-label">Total Amount:</span>
            <span class="info-value font-weight-bold text-lg text-walmart-blue">${{number_format($order->total_amount,2)}}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="walmart-card h-100">
      <div class="card-header">
        <h4 class="card-title">
          <i class="fas fa-shipping-fast mr-2"></i>
          Shipping Information
        </h4>
      </div>
      <div class="card-body">
        <div class="shipping-info-list">
          <div class="info-item">
            <span class="info-label">Full Name:</span>
            <span class="info-value font-weight-bold">{{$order->first_name}} {{$order->last_name}}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Email:</span>
            <span class="info-value">
              <a href="mailto:{{$order->email}}" class="text-walmart-blue">{{$order->email}}</a>
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Phone:</span>
            <span class="info-value">
              <a href="tel:{{$order->phone}}" class="text-walmart-blue">{{$order->phone}}</a>
            </span>
          </div>
          <div class="info-item">
            <span class="info-label">Address:</span>
            <span class="info-value">{{$order->address1}}@if($order->address2), {{$order->address2}}@endif</span>
          </div>
          <div class="info-item">
            <span class="info-label">Country:</span>
            <span class="info-value">{{$order->country}}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Post Code:</span>
            <span class="info-value">{{$order->post_code}}</span>
          </div>
        </div>
        
        <!-- Shipping Address Card -->
        <div class="shipping-address-card mt-4 p-3 bg-light rounded">
          <h6 class="font-weight-bold mb-2">
            <i class="fas fa-map-marker-alt mr-2"></i>
            Delivery Address
          </h6>
          <address class="mb-0">
            <strong>{{$order->first_name}} {{$order->last_name}}</strong><br>
            {{$order->address1}}<br>
            @if($order->address2){{$order->address2}}<br>@endif
            {{$order->country}} {{$order->post_code}}<br>
            <i class="fas fa-phone mr-1"></i>{{$order->phone}}<br>
            <i class="fas fa-envelope mr-1"></i>{{$order->email}}
          </address>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Order Actions -->
<div class="walmart-card">
  <div class="card-header">
    <h4 class="card-title">Order Actions</h4>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-2">
      <a href="{{route('order.pdf',$order->id)}}" class="walmart-btn walmart-btn-primary">
        <i class="fas fa-download mr-2"></i>
        Download Invoice
      </a>
      
      @if($order->status != 'delivered' && $order->status != 'cancel')
      <button class="walmart-btn walmart-btn-warning" onclick="trackOrder()">
        <i class="fas fa-truck mr-2"></i>
        Track Order
      </button>
      @endif
      
      <form method="POST" action="{{route('user.order.delete',[$order->id])}}" class="d-inline">
        @csrf
        @method('delete')
        <button type="button" class="walmart-btn walmart-btn-danger dltBtn" data-id="{{$order->id}}">
          <i class="fas fa-trash-alt mr-2"></i>
          Delete Order
        </button>
      </form>
    </div>
  </div>
</div>

@endif
@endsection

@push('styles')
<style>
.order-status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--gray-100);
}

.order-total h2 {
  font-size: 2.5rem;
  font-weight: var(--font-bold);
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--border-light);
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: var(--font-medium);
  color: var(--gray-600);
  min-width: 140px;
  flex-shrink: 0;
}

.info-value {
  color: var(--gray-800);
  text-align: right;
  flex: 1;
}

.shipping-address-card {
  background: var(--gray-50);
  border: 1px solid var(--border-light);
}

.shipping-address-card address {
  font-style: normal;
  line-height: 1.6;
}

.h-100 {
  height: 100% !important;
}

.gap-2 {
  gap: 0.5rem;
}

.flex-wrap {
  flex-wrap: wrap !important;
}

.mx-2 {
  margin-left: 0.5rem !important;
  margin-right: 0.5rem !important;
}

.fa-2x {
  font-size: 2em;
}

.text-md-right {
  text-align: right !important;
}

.bg-light {
  background-color: var(--gray-100) !important;
}

.rounded {
  border-radius: 0.375rem !important;
}

.mt-4 {
  margin-top: 1.5rem !important;
}

.pt-3 {
  padding-top: 1rem !important;
}

.mt-3 {
  margin-top: 1rem !important;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .order-total {
    text-align: center !important;
    margin-top: 1rem;
  }
  
  .order-total h2 {
    font-size: 2rem;
  }
  
  .d-flex.justify-content-between {
    flex-direction: column;
    gap: 1rem;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
  
  .info-label {
    min-width: auto;
  }
  
  .info-value {
    text-align: left;
  }
  
  .order-status-icon {
    width: 50px;
    height: 50px;
  }
  
  .gap-2 {
    gap: 0.75rem;
  }
  
  .walmart-btn {
    width: 100%;
    justify-content: center;
  }
}

/* Print styles */
@media print {
  .walmart-btn,
  .card-header,
  .order-actions {
    display: none !important;
  }
  
  .walmart-card {
    box-shadow: none !important;
    border: 1px solid #ddd !important;
  }
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
$(document).ready(function(){
  // CSRF Token setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  
  // Delete confirmation
  $('.dltBtn').click(function(e){
    e.preventDefault();
    var form = $(this).closest('form');
    var dataID = $(this).data('id');
    
    swal({
      title: "Delete Order?",
      text: "Are you sure you want to delete this order? This action cannot be undone!",
      icon: "warning",
      buttons: {
        cancel: {
          text: "Cancel",
          value: null,
          visible: true,
          className: "btn-secondary",
          closeModal: true,
        },
        confirm: {
          text: "Yes, Delete",
          value: true,
          visible: true,
          className: "btn-danger",
          closeModal: true
        }
      },
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        form.submit();
      }
    });
  });
  
  // Initialize tooltips
  $('[data-toggle="tooltip"]').tooltip();
});

// Track order function
function trackOrder() {
  swal({
    title: "Order Tracking",
    text: "Order tracking feature will be available soon!",
    icon: "info",
    button: "OK"
  });
}

// Print order function
function printOrder() {
  window.print();
}
</script>
@endpush
