@extends('backend.layouts.master')

@section('title','Edit Order')

@section('main-content')
<div class="card shadow mb-4">
  <div class="row">
    <div class="col-md-12">
      @include('backend.layouts.notification')
    </div>
  </div>
  <h5 class="card-header">Edit Order</h5>
  <div class="card-body">
    <form action="{{ route('sub-admin.orders.update', $order->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-control">
          <option value="pending" {{ in_array($order->status, ['processing','shipped','delivered','cancelled','returned']) ? 'disabled' : '' }} {{ $order->status=='pending'?'selected':'' }}>Pending</option>
          <option value="processing" {{ in_array($order->status, ['shipped','delivered','cancelled','returned']) ? 'disabled' : '' }} {{ $order->status=='processing'?'selected':'' }}>Processing</option>
          <option value="shipped" {{ in_array($order->status, ['delivered','cancelled','returned']) ? 'disabled' : '' }} {{ $order->status=='shipped'?'selected':'' }}>Shipped</option>
          <option value="delivered" {{ in_array($order->status, ['cancelled','returned']) ? 'disabled' : '' }} {{ $order->status=='delivered'?'selected':'' }}>Delivered</option>
          <option value="cancelled" {{ $order->status=='delivered' ? 'disabled' : '' }} {{ in_array($order->status, ['cancel','cancelled']) ? 'selected' : '' }}>Cancelled</option>
          <option value="returned" {{ $order->status=='delivered' ? 'disabled' : '' }} {{ $order->status=='returned'?'selected':'' }}>Returned</option>
        </select>
      </div>

      <div class="form-group">
        <label for="tracking_number">Tracking Number</label>
        <input type="text" id="tracking_number" name="tracking_number" class="form-control" value="{{ old('tracking_number', $order->tracking_number) }}" placeholder="e.g., 1Z999AA10123456784">
      </div>

      <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional info for this order">{{ old('notes', $order->notes) }}</textarea>
      </div>

      <div class="form-group">
        <label for="cancel_reason">Cancel Reason (required if status = cancelled)</label>
        <textarea id="cancel_reason" name="cancel_reason" class="form-control" rows="2" placeholder="Why is this order cancelled?">{{ old('cancel_reason', $order->cancel_reason) }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('sub-admin.orders') }}" class="btn btn-secondary">Back</a>
    </form>
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