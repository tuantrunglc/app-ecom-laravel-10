@extends('backend.layouts.master')

@section('title','Users của Sub Admin')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Users của Sub Admin: {{ $subAdmin->name }}</h6>
      <a href="{{ route('admin.sub-admins.show', $subAdmin->id) }}" class="btn btn-secondary btn-sm float-right">
          <i class="fas fa-arrow-left"></i> Quay lại
      </a>
    </div>
    <div class="card-body">
      <div class="row mb-3">
          <div class="col-md-6">
              <div class="card bg-primary text-white">
                  <div class="card-body">
                      <h5>{{ $users->total() }}</h5>
                      <p class="mb-0">Tổng Users</p>
                  </div>
              </div>
          </div>
          <div class="col-md-6">
              <div class="card bg-success text-white">
                  <div class="card-body">
                      <h5>{{ $subAdmin->getActiveUsersCount() }}</h5>
                      <p class="mb-0">Users Hoạt động</p>
                  </div>
              </div>
          </div>
      </div>

      <div class="table-responsive">
        @if(count($users)>0)
        <table class="table table-bordered" id="user-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên</th>
              <th>Email</th>
              <th>Số dư Ví</th>
              <th>Trạng thái</th>
              <th>Ngày đăng ký</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $user)   
                <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td>${{number_format($user->wallet_balance ?? 0, 2)}}</td>
                    <td>
                        @if($user->status=='active')
                            <span class="badge badge-success">{{$user->status}}</span>
                        @else
                            <span class="badge badge-warning">{{$user->status}}</span>
                        @endif
                    </td>
                    <td>{{$user->created_at->format('d M, Y')}}</td>
                    <td>
                        <a href="{{route('users.show', $user->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Xem chi tiết" data-placement="bottom"><i class="fas fa-eye"></i></a>
                        <a href="{{route('users.edit', $user->id)}}" class="btn btn-warning btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Chỉnh sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$users->links()}}</span>
        @else
          <h6 class="text-center">Sub Admin này chưa có user nào!</h6>
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
      
      $('#user-dataTable').DataTable( {
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[6]
                }
            ]
        } );
  </script>
@endpush