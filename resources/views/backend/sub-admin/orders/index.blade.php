@extends('backend.layouts.master')

@section('title','Quản Lý Đơn Hàng')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh Sách Đơn Hàng</h6>
      <div class="float-right">
        @if($subAdmin->subAdminSettings->can_manage_orders)
          <a class="btn btn-primary" href="{{route('sub-admin.orders.create')}}" role="button">
            <i class="fas fa-plus"></i> Tạo Đơn Hàng Mới
          </a>
        @endif
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($orders)>0)
        <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>STT</th>
              <th>Mã đơn hàng</th>
              <th>Khách hàng</th>
              <th>Số tiền</th>
              <th>Trạng thái</th>
              <th>Ngày đặt</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @foreach($orders as $order)   
                <tr>
                    <td>{{$order->id}}</td>
                    <td>{{$order->order_number}}</td>
                    <td>{{$order->user->name}}</td>
                    <td>${{number_format($order->total_amount, 2)}}</td>
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
                    <td>{{$order->created_at->format('d M, Y')}}</td>
                    <td>
                        <a href="{{route('sub-admin.orders.show', $order->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Xem chi tiết" data-placement="bottom"><i class="fas fa-eye"></i></a>
                        <a href="{{route('sub-admin.orders.edit', $order->id)}}" class="btn btn-warning btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Chỉnh sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$orders->links()}}</span>
        @else
          <h6 class="text-center">Chưa có đơn hàng nào!</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <style>
      div.dataTables_wrapper div.dataTables_paginate{
          display: none;
      }
  </style>
@endpush

@push('scripts')

  <!-- Page level plugins -->
  <script src="{{asset('backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="{{asset('backend/js/demo/datatables-demo.js')}}"></script>
  <script>
      
      $('#order-dataTable').DataTable( {
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[6]
                }
            ]
        } );
  </script>
@endpush