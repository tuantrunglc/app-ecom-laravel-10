@extends('user.layouts.master')

@section('main-content')
<!-- Notifications -->
@include('user.layouts.notification')

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">My Orders</h1>
    <p class="text-muted mb-0">Track and manage all your orders</p>
  </div>
  <div class="d-none d-md-block">
    <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
      <i class="fas fa-plus mr-2"></i>
      Place New Order
    </a>
  </div>
</div>

<!-- Mobile Place Order Button -->
<div class="d-block d-md-none mb-3 text-center">
  <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
    <i class="fas fa-plus mr-2"></i>
    Place New Order
  </a>
</div>

@php
    $total_orders = count($orders);
    $new_orders = $orders->where('status', 'new')->count();
    $processing_orders = $orders->where('status', 'process')->count();
    $delivered_orders = $orders->where('status', 'delivered')->count();
    $has_delivered_orders = $delivered_orders > 0;
@endphp

<!-- Order Stats -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card primary">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$total_orders}}</h3>
          <p>Total Orders</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-shopping-bag"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card warning">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$new_orders}}</h3>
          <p>New Orders</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-clock"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card info">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$processing_orders}}</h3>
          <p>Processing</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-cog"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card success">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$delivered_orders}}</h3>
          <p>Delivered</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Orders Table -->
<div class="walmart-card">
  <div class="card-header">
    <h4 class="card-title">Order History</h4>
    <div class="d-flex align-items-center">
      <div class="mr-3">
        <select class="walmart-select" id="statusFilter" style="width: auto; min-width: 120px;">
          <option value="">All Status</option>
          <option value="new">New</option>
          <option value="process">Processing</option>
          <option value="delivered">Delivered</option>
          <option value="cancel">Cancelled</option>
        </select>
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    @if(count($orders)>0)
      <div class="table-responsive">
        <table class="walmart-table" id="order-dataTable">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Shipping</th>
              <th>Total</th>
              @if($has_delivered_orders)
              <th>Commission</th>
              @endif
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)
            <tr>
              <td data-label="Order #">
                <div class="mobile-content">
                  <div class="font-weight-bold text-walmart-blue">{{$order->order_number}}</div>
                  <small class="text-muted">#{{$order->id}}</small>
                </div>
              </td>
              <td data-label="Date">
                <div class="mobile-content">
                  <div>{{date('M d, Y', strtotime($order->created_at))}}</div>
                  <small class="text-muted">{{date('h:i A', strtotime($order->created_at))}}</small>
                </div>
              </td>
              <td data-label="Customer">
                <div class="mobile-content">
                  <div class="font-weight-medium">{{$order->first_name}} {{$order->last_name}}</div>
                  <small class="text-muted">{{$order->email}}</small>
                </div>
              </td>
              <td data-label="Items">
                <div class="mobile-content">
                  <span class="font-weight-bold">{{$order->quantity}}</span> item(s)
                </div>
              </td>
              <td data-label="Shipping">
                <div class="mobile-content">
                  ${{number_format($order->shipping->price ?? 0, 2)}}
                </div>
              </td>
              <td data-label="Total">
                <div class="mobile-content">
                  <div class="font-weight-bold text-lg">${{number_format($order->total_amount,2)}}</div>
                </div>
              </td>
              @if($has_delivered_orders)
              <td data-label="Commission">
                <div class="mobile-content">
                  @if($order->status == 'delivered')
                    @if(isset($commissions[$order->order_number]))
                      <div class="font-weight-bold text-success">${{number_format($commissions[$order->order_number], 2)}}</div>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </div>
              </td>
              @endif
              <td data-label="Status">
                <div class="mobile-content">
                  @if($order->status=='new')
                    <span class="status-badge new">New</span>
                  @elseif($order->status=='process')
                    <span class="status-badge process">Processing</span>
                  @elseif($order->status=='delivered')
                    <span class="status-badge delivered">Delivered</span>
                  @else
                    <span class="status-badge cancelled">{{ucfirst($order->status)}}</span>
                  @endif
                </div>
              </td>
              <td data-label="Actions">
                <a href="{{route('user.order.show',$order->id)}}" 
                   class="walmart-btn walmart-btn-warning walmart-btn-icon" 
                   data-toggle="tooltip" title="View Details">
                  <i class="fas fa-eye"></i>
                </a>
                @if($order->status=='new')
                <button class="walmart-btn walmart-btn-primary walmart-btn-icon ml-1 js-advance-order" 
                        data-id="{{$order->id}}" data-toggle="tooltip" title="Advance to Processing">
                  <i class="fas fa-arrow-right"></i>
                </button>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="p-3 border-top">
        {{$orders->links()}}
      </div>
    @else
      <div class="text-center py-5">
        <div class="mb-3">
          <i class="fas fa-shopping-bag fa-4x text-muted"></i>
        </div>
        <h5 class="text-muted mb-3">No Orders Found</h5>
        <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your orders here!</p>
        <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
          <i class="fas fa-shopping-cart mr-2"></i>
          Start Shopping
        </a>
      </div>
    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
.fa-4x {
  font-size: 4em;
}

.p-0 {
  padding: 0 !important;
}

.p-3 {
  padding: 1rem !important;
}

.border-top {
  border-top: 1px solid var(--border-light) !important;
}

.font-weight-medium {
  font-weight: var(--font-medium);
}

.text-lg {
  font-size: var(--text-lg);
}

.align-items-center {
  align-items: center !important;
}

.mr-2 {
  margin-right: 0.5rem !important;
}

.mr-3 {
  margin-right: 1rem !important;
}

.mb-3 {
  margin-bottom: 1rem !important;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}

.py-5 {
  padding-top: 3rem !important;
  padding-bottom: 3rem !important;
}

/* Status filter styling */
#statusFilter {
  padding: 0.5rem 0.75rem;
  font-size: var(--text-sm);
}

/* Commission styling */
.text-success {
  color: #28a745 !important;
}

/* Mobile touch improvements */
.walmart-btn {
  min-height: 44px; /* iOS touch target minimum */
  touch-action: manipulation;
}

.stats-card {
  transition: transform 0.2s ease;
  touch-action: manipulation;
}

.stats-card:active {
  transform: scale(0.98);
}

/* Filter dropdown mobile */
.walmart-select {
  min-height: 44px;
  font-size: 16px; /* Prevent zoom on iOS */
}

/* Mobile responsive improvements */
@media (max-width: 768px) {
  /* Hide desktop button and show mobile version */
  .d-none.d-md-block {
    display: none !important;
  }
  
  /* Mobile header adjustments */
  .d-flex.align-items-center.justify-content-between.mb-4 {
    flex-direction: column;
    align-items: flex-start;
    text-align: center;
  }
  
  .d-flex.align-items-center.justify-content-between.mb-4 > div:first-child {
    width: 100%;
    text-align: center;
    margin-bottom: 1rem;
  }
  
  /* Stats cards mobile */
  .stats-card {
    margin-bottom: 1rem;
  }
  
  .stats-card h3 {
    font-size: 1.5rem;
  }
  
  .stats-card-content {
    padding: 1rem;
  }
  
  /* Card header mobile */
  .card-header {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 1rem;
    padding: 1rem;
  }
  
  .card-header .d-flex {
    width: 100%;
    justify-content: flex-start;
  }
  
  .card-header h4 {
    font-size: 1.25rem;
    margin-bottom: 0;
  }
  
  /* Table responsive */
  .table-responsive {
    border: none;
    box-shadow: none;
  }
  
  .walmart-table {
    border: 0;
    width: 100%;
  }
  
  .walmart-table thead {
    display: none;
  }
  
  .walmart-table tbody tr {
    display: block;
    margin-bottom: 1.5rem;
    background: #fff;
    border: 1px solid #e3e6f0;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  }
  
  .walmart-table tbody tr td {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border: none;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
    position: relative;
  }
  
  .walmart-table tbody tr td:last-child {
    border-bottom: none;
    padding-top: 1rem;
    justify-content: center;
    gap: 0.5rem;
  }
  
  .walmart-table tbody tr td:before {
    content: attr(data-label);
    font-weight: bold;
    color: #5a5c69;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    min-width: 100px;
    text-align: left;
    margin-right: 1rem;
  }
  
  .walmart-table tbody tr td .mobile-content {
    flex-grow: 1;
    text-align: right;
  }
  
  .walmart-table tbody tr td[data-label="Order #"]:before {
    content: "Order Number";
  }
  
  .walmart-table tbody tr td[data-label="Date"]:before {
    content: "Order Date";
  }
  
  .walmart-table tbody tr td[data-label="Customer"]:before {
    content: "Customer Info";
  }
  
  .walmart-table tbody tr td[data-label="Items"]:before {
    content: "Items";
  }
  
  .walmart-table tbody tr td[data-label="Shipping"]:before {
    content: "Shipping Cost";
  }
  
  .walmart-table tbody tr td[data-label="Total"]:before {
    content: "Total Amount";
  }
  
  .walmart-table tbody tr td[data-label="Commission"]:before {
    content: "Commission";
  }
  
  .walmart-table tbody tr td[data-label="Status"]:before {
    content: "Order Status";
  }
  
  .walmart-table tbody tr td[data-label="Actions"]:before {
    content: "Actions";
  }
  
  /* Special handling for complex content */
  .walmart-table tbody tr td[data-label="Order #"],
  .walmart-table tbody tr td[data-label="Date"],
  .walmart-table tbody tr td[data-label="Customer"] {
    padding-bottom: 0.75rem;
  }
  
  .walmart-table tbody tr td[data-label="Actions"] {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }
  
  .walmart-table tbody tr td[data-label="Actions"] > * {
    margin: 0.25rem;
  }
  
  /* Status badges mobile */
  .status-badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
  }
  
  /* Button adjustments for mobile */
  .walmart-btn-icon {
    padding: 0.5rem;
    min-width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  
  /* Empty state mobile */
  .text-center.py-5 {
    padding: 2rem 1rem !important;
  }
  
  .text-center.py-5 .fa-4x {
    font-size: 3em;
  }
}

/* Small mobile devices */
@media (max-width: 576px) {
  .container-fluid {
    padding-left: 15px;
    padding-right: 15px;
  }
  
  .stats-card-content {
    padding: 0.75rem;
  }
  
  .stats-card h3 {
    font-size: 1.25rem;
  }
  
  .stats-card p {
    font-size: 0.875rem;
  }
  
  .walmart-table tbody tr {
    padding: 0.75rem;
    margin-bottom: 1rem;
  }
  
  .walmart-table tbody tr td {
    padding: 0.375rem 0;
    font-size: 0.875rem;
  }
  
  .walmart-table tbody tr td:before {
    min-width: 80px;
    font-size: 0.875rem;
  }
  
  .card-header {
    padding: 0.75rem;
  }
  
  .card-header h4 {
    font-size: 1.125rem;
  }
  
  /* Better mobile buttons */
  .walmart-btn-icon {
    min-width: 44px;
    min-height: 44px;
    padding: 0.6rem;
    font-size: 1rem;
  }
  
  .walmart-btn-icon.ml-1 {
    margin-left: 0.5rem !important;
  }
  
  /* Order header mobile */
  .h3 {
    font-size: 1.5rem;
  }
  
  /* Improve status filter */
  #statusFilter {
    width: 100%;
    max-width: 200px;
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
  
  // Status filter functionality
  $('#statusFilter').change(function(){
    var status = $(this).val().toLowerCase();
    var rows = $('#order-dataTable tbody tr');
    
    if(status === '') {
      rows.show();
    } else {
      rows.each(function(){
        var rowStatus = $(this).find('.status-badge').text().toLowerCase();
        if(rowStatus.includes(status)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  });
  
  // Advance order button
  $(document).on('click', '.js-advance-order', function(){
    var orderId = $(this).data('id');
    var $btn = $(this);

    swal({
      title: "Payment Order",
      text: "Are you sure you want to pay for this order?",
      icon: "warning",
      buttons: true,
      dangerMode: false,
    }).then(function(willDo){
      if(!willDo) return;

      $btn.prop('disabled', true);
      $.post('/user/order/' + orderId + '/advance', {})
        .done(function(resp){
          swal("Success", resp.message || "Order updated", "success");
          // Reload to update status and commission view
          setTimeout(function(){ location.reload(); }, 800);
        })
        .fail(function(xhr){
          var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Request failed';
          swal("Error", msg, "error");
        })
        .always(function(){
          $btn.prop('disabled', false);
        });
    });
  });

  // Initialize tooltips (disable on touch devices)
  if (!('ontouchstart' in window)) {
    $('[data-toggle="tooltip"]').tooltip();
  }
  
  // Stats cards click functionality with better mobile support
  $('.stats-card').on('click touchend', function(e){
    e.preventDefault();
    var cardType = '';
    if($(this).hasClass('warning')) {
      cardType = 'new';
    } else if($(this).hasClass('info')) {
      cardType = 'process';
    } else if($(this).hasClass('success')) {
      cardType = 'delivered';
    }
    
    if(cardType) {
      $('#statusFilter').val(cardType).trigger('change');
      // Scroll to table on mobile
      if(window.innerWidth <= 768) {
        $('html, body').animate({
          scrollTop: $('.walmart-card').offset().top - 100
        }, 500);
      }
    }
  });
  
  // Add cursor pointer to clickable stats cards
  $('.stats-card.warning, .stats-card.info, .stats-card.success').css('cursor', 'pointer');
  
  // Improve mobile table scrolling
  if(window.innerWidth <= 768) {
    $('.table-responsive').css('overflow-x', 'visible');
  }
  
  // Handle orientation change
  $(window).on('orientationchange resize', function() {
    setTimeout(function() {
      if(window.innerWidth <= 768) {
        $('.table-responsive').css('overflow-x', 'visible');
      } else {
        $('.table-responsive').css('overflow-x', 'auto');
      }
    }, 100);
  });
});
</script>
@endpush
