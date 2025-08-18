@extends('user.layouts.master')

@section('main-content')
<!-- Notifications -->
@include('user.layouts.notification')

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Welcome back, {{Auth()->user()->name}}!</h1>
    <p class="text-muted mb-0">Here's what's happening with your account today.</p>
  </div>
  <div class="d-none d-md-block">
    <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
      <i class="fas fa-shopping-cart mr-2"></i>
      Continue Shopping
    </a>
  </div>
</div>

@php
    $user_orders = DB::table('orders')->where('user_id', auth()->user()->id);
    $total_orders = $user_orders->count();
    $pending_orders = $user_orders->where('status', 'new')->count();
    $processing_orders = $user_orders->where('status', 'process')->count();
    $delivered_orders = $user_orders->where('status', 'delivered')->count();
    $total_spent = $user_orders->sum('total_amount');
    
    $recent_orders = DB::table('orders')
        ->where('user_id', auth()->user()->id)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
@endphp

<!-- Stats Cards Row -->
<div class="row mb-4">
  <!-- Total Orders -->
  <div class="col-xl-3 col-md-6 mb-4">
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

  <!-- Pending Orders -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="stats-card warning">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$pending_orders}}</h3>
          <p>Pending Orders</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-clock"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Processing Orders -->
  <div class="col-xl-3 col-md-6 mb-4">
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

  <!-- Total Spent -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="stats-card success">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>${{number_format($total_spent, 2)}}</h3>
          <p>Total Spent</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-dollar-sign"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions Row -->
<div class="row mb-4">
  <div class="col-12">
    <div class="walmart-card">
      <div class="card-header">
        <h4 class="card-title">Quick Actions</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 col-6 mb-3">
            <a href="{{route('user.order.index')}}" class="walmart-btn walmart-btn-primary w-100">
              <i class="fas fa-list mr-2"></i>
              View Orders
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="{{route('wallet.index')}}" class="walmart-btn walmart-btn-secondary w-100">
              <i class="fas fa-wallet mr-2"></i>
              My Wallet
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="{{route('user.productreview.index')}}" class="walmart-btn walmart-btn-secondary w-100">
              <i class="fas fa-star mr-2"></i>
              Reviews
            </a>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <a href="{{route('chat.index')}}" class="walmart-btn walmart-btn-secondary w-100">
              <i class="fas fa-comment-dots mr-2"></i>
              Support
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Orders -->
<div class="row">
  <div class="col-12">
    <div class="walmart-card">
      <div class="card-header">
        <h4 class="card-title">Recent Orders</h4>
        <a href="{{route('user.order.index')}}" class="view-all">View All Orders</a>
      </div>
      <div class="card-body">
        @if(count($recent_orders) > 0)
          <div class="table-responsive">
            <table class="walmart-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Date</th>
                  <th>Items</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recent_orders as $order)
                <tr>
                  <td data-label="Order #">
                    <strong>{{$order->order_number}}</strong>
                  </td>
                  <td data-label="Date">
                    {{date('M d, Y', strtotime($order->created_at))}}
                  </td>
                  <td data-label="Items">
                    {{$order->quantity}} item(s)
                  </td>
                  <td data-label="Total">
                    <strong>${{number_format($order->total_amount, 2)}}</strong>
                  </td>
                  <td data-label="Status">
                    @if($order->status == 'new')
                      <span class="status-badge new">New</span>
                    @elseif($order->status == 'process')
                      <span class="status-badge process">Processing</span>
                    @elseif($order->status == 'delivered')
                      <span class="status-badge delivered">Delivered</span>
                    @else
                      <span class="status-badge cancelled">{{ucfirst($order->status)}}</span>
                    @endif
                  </td>
                  <td data-label="Actions">
                    <a href="{{route('user.order.show', $order->id)}}" 
                       class="walmart-btn walmart-btn-warning walmart-btn-icon" 
                       data-toggle="tooltip" title="View Order">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="fas fa-shopping-bag fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted mb-3">No Orders Yet</h5>
            <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your orders here!</p>
            <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-primary">
              <i class="fas fa-shopping-cart mr-2"></i>
              Start Shopping
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
.w-100 {
  width: 100% !important;
}

.py-5 {
  padding-top: 3rem !important;
  padding-bottom: 3rem !important;
}

.fa-3x {
  font-size: 3em;
}

.d-flex {
  display: flex !important;
}

.d-inline {
  display: inline !important;
}

@media (max-width: 575.98px) {
  .stats-card h3 {
    font-size: 1.5rem;
  }
  
  .quick-actions .walmart-btn {
    font-size: 0.875rem;
    padding: 0.5rem;
  }
}
</style>
@endpush

@push('scripts')
<script type="text/javascript">
  // Scoped variable name to avoid collisions with other pages
  const incomeApiUrl = "{{route('product.order.income')}}";

  // Only run if the canvas exists on this page
  var ctx = document.getElementById("myAreaChart");
  if (ctx && window.Chart && window.axios) {
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#858796';

    function number_format(number, decimals, dec_point, thousands_sep) {
      number = (number + '').replace(',', '').replace(' ', '');
      var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
          var k = Math.pow(10, prec);
          return '' + Math.round(n * k) / k;
        };
      s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
      if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
      }
      if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
      }
      return s.join(dec);
    }

    axios.get(incomeApiUrl)
      .then(function (response) {
        const data_keys = Object.keys(response.data);
        const data_values = Object.values(response.data);

        new Chart(ctx, {
          type: 'line',
          data: {
            labels: data_keys,
            datasets: [{
              label: "Earnings",
              lineTension: 0.3,
              backgroundColor: "rgba(78, 115, 223, 0.05)",
              borderColor: "rgba(78, 115, 223, 1)",
              pointRadius: 3,
              pointBackgroundColor: "rgba(78, 115, 223, 1)",
              pointBorderColor: "rgba(78, 115, 223, 1)",
              pointHoverRadius: 3,
              pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
              pointHoverBorderColor: "rgba(78, 115, 223, 1)",
              pointHitRadius: 10,
              pointBorderWidth: 2,
              data: data_values,
            }],
          },
          options: {
            maintainAspectRatio: false,
            layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
            scales: {
              xAxes: [{
                time: { unit: 'date' },
                gridLines: { display: false, drawBorder: false },
                ticks: { maxTicksLimit: 7 }
              }],
              yAxes: [{
                ticks: {
                  maxTicksLimit: 5,
                  padding: 10,
                  callback: function(value) { return '$' + number_format(value); }
                },
                gridLines: {
                  color: "rgb(234, 236, 244)",
                  zeroLineColor: "rgb(234, 236, 244)",
                  drawBorder: false,
                  borderDash: [2],
                  zeroLineBorderDash: [2]
                }
              }],
            },
            legend: { display: false },
            tooltips: {
              backgroundColor: "rgb(255,255,255)",
              bodyFontColor: "#858796",
              titleMarginBottom: 10,
              titleFontColor: '#6e707e',
              titleFontSize: 14,
              borderColor: '#dddfeb',
              borderWidth: 1,
              xPadding: 15,
              yPadding: 15,
              displayColors: false,
              intersect: false,
              mode: 'index',
              caretPadding: 10,
              callbacks: {
                label: function(tooltipItem, chart) {
                  var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                  return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
                }
              }
            }
          }
        });
      })
      .catch(function (error) {
        console.log(error)
      });
  }
</script>



  </script>
@endpush