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
        <label for="status">Status :</label>
        <select name="status" id="status" class="form-control">
          <option value="new" {{($order->status=='delivered' || $order->status=="process" || $order->status=="cancel") ? 'disabled' : ''}}  {{(($order->status=='new')? 'selected' : '')}}>New</option>
          <option value="process" {{($order->status=='delivered'|| $order->status=="cancel") ? 'disabled' : ''}}  {{(($order->status=='process')? 'selected' : '')}}>Process</option>
          <option value="delivered" {{($order->status=="cancel") ? 'disabled' : ''}}  {{(($order->status=='delivered')? 'selected' : '')}}>Delivered</option>
          <option value="cancel" {{($order->status=='delivered') ? 'disabled' : ''}}  {{(($order->status=='cancel')? 'selected' : '')}}>Cancel</option>
        </select>
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