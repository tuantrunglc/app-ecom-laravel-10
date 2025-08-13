@extends('backend.layouts.master')

@section('title','Order Detail')

@section('main-content')
<div class="card shadow mb-4">
  <div class="row">
    <div class="col-md-12">
      @include('backend.layouts.notification')
    </div>
  </div>
  <h5 class="card-header d-flex justify-content-between align-items-center">
    <span>Order Details</span>
    <div>
      <a href="{{ route('order.pdf', $order->id) }}" class="btn btn-sm btn-primary shadow-sm mr-2">
        <i class="fas fa-download fa-sm text-white-50"></i> Generate PDF
      </a>
      <a href="{{ route('sub-admin.orders.edit', $order->id) }}" class="btn btn-sm btn-warning shadow-sm">
        <i class="fas fa-edit fa-sm text-white-50"></i> Edit Order
      </a>
    </div>
  </h5>
  <div class="card-body">
    @if($order)
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
            @php($status = strtolower($order->status))
            @if(in_array($status, ['new','pending']))
              <span class="badge badge-primary">{{ ucfirst($order->status) }}</span>
            @elseif(in_array($status, ['process','processing']))
              <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
            @elseif(in_array($status, ['shipped']))
              <span class="badge badge-info">{{ ucfirst($order->status) }}</span>
            @elseif(in_array($status, ['delivered']))
              <span class="badge badge-success">{{ ucfirst($order->status) }}</span>
            @elseif(in_array($status, ['cancel','cancelled']))
              <span class="badge badge-danger">{{ ucfirst($order->status) }}</span>
            @elseif(in_array($status, ['returned']))
              <span class="badge badge-secondary">{{ ucfirst($order->status) }}</span>
            @else
              <span class="badge badge-light text-dark">{{ ucfirst($order->status) }}</span>
            @endif
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
                  <td> : {{ ucfirst($order->status) }}</td>
                </tr>
                <tr>
                  <td>Shipping Charge</td>
                  <td> : $ {{$order->shipping ? $order->shipping->price : '0.00'}}</td>
                </tr>
                @if(!empty($order->coupon))
                <tr>
                  <td>Coupon</td>
                  <td> : $ {{number_format($order->coupon,2)}}</td>
                </tr>
                @endif
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
                @if(!empty($order->tracking_number))
                <tr>
                  <td>Tracking Number</td>
                  <td> : {{$order->tracking_number}}</td>
                </tr>
                @endif
                @if(!empty($order->notes))
                <tr>
                  <td>Notes</td>
                  <td> : {{$order->notes}}</td>
                </tr>
                @endif
                @if(!empty($order->cancel_reason))
                <tr>
                  <td>Cancel Reason</td>
                  <td> : {{$order->cancel_reason}}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>

          <div class="col-lg-6 col-lx-4">
            <div class="shipping-info">
              <h4 class="text-center pb-4">SHIPPING INFORMATION</h4>
              <table class="table">
                <tr class="">
                  <td>Full Name</td>
                  <td> : {{$order->first_name}} {{$order->last_name}}</td>
                </tr>
                <tr>
                  <td>Email</td>
                  <td> : {{$order->email}}</td>
                </tr>
                <tr>
                  <td>Phone No.</td>
                  <td> : {{$order->phone}}</td>
                </tr>
                <tr>
                  <td>Address</td>
                  <td> : {{$order->address1}}@if($order->address2), {{$order->address2}}@endif</td>
                </tr>
                <tr>
                  <td>Country</td>
                  <td> : {{$order->country}}</td>
                </tr>
                <tr>
                  <td>Post Code</td>
                  <td> : {{$order->post_code}}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
  .order-info,.shipping-info{
    background:#ECECEC;
    padding:20px;
  }
  .order-info h4,.shipping-info h4{
    text-decoration: underline;
  }
</style>
@endpush