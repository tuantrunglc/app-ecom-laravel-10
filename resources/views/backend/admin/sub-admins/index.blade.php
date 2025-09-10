@extends('backend.layouts.master')

@section('title','Danh Sách Sub Admin')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh Sách Sub Admin</h6>
      <a href="{{route('admin.sub-admins.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm Sub Admin"><i class="fas fa-plus"></i> Thêm Sub Admin</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($subAdmins)>0)
        <table class="table table-bordered" id="user-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên</th>
              <th>Email</th>
              <th>Mã Sub Admin</th>
              <th>Số Users</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @foreach($subAdmins as $subAdmin)   
                <tr>
                    <td>{{$subAdmin->id}}</td>
                    <td>{{$subAdmin->name}}</td>
                    <td>{{$subAdmin->email}}</td>
                    <td>
                        <code>{{$subAdmin->sub_admin_code}}</code>
                        <form method="post" action="{{route('admin.sub-admins.regenerate-code', $subAdmin->id)}}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-info btn-xs" data-toggle="tooltip" title="Tái tạo mã">
                                <i class="fas fa-sync"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        @if($subAdmin->subAdminStats)
                            <span class="badge badge-info">{{$subAdmin->subAdminStats->total_users}}</span> / 
                            <span class="badge badge-secondary">{{$subAdmin->subAdminSettings ? $subAdmin->subAdminSettings->max_users_allowed : 0}}</span>
                        @else
                            <span class="badge badge-secondary">0 / {{$subAdmin->subAdminSettings ? $subAdmin->subAdminSettings->max_users_allowed : 0}}</span>
                        @endif
                    </td>
                    <td>
                        @if($subAdmin->status=='active')
                            <span class="badge badge-success">{{$subAdmin->status}}</span>
                        @else
                            <span class="badge badge-warning">{{$subAdmin->status}}</span>
                        @endif
                        <form method="post" action="{{route('admin.sub-admins.toggle-status', $subAdmin->id)}}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{$subAdmin->status=='active' ? 'warning' : 'success'}} btn-xs" data-toggle="tooltip" title="{{$subAdmin->status=='active' ? 'Vô hiệu hóa' : 'Kích hoạt'}}">
                                <i class="fas fa-{{$subAdmin->status=='active' ? 'ban' : 'check'}}"></i>
                            </button>
                        </form>
                    </td>
                    <td>{{$subAdmin->created_at->format('d M, Y')}}</td>
                    <td>
                        <a href="{{route('admin.sub-admins.show', $subAdmin->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Xem chi tiết" data-placement="bottom"><i class="fas fa-eye"></i></a>
                        <a href="{{route('admin.sub-admins.edit', $subAdmin->id)}}" class="btn btn-warning btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Chỉnh sửa" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <a href="{{route('admin.sub-admins.users', $subAdmin->id)}}" class="btn btn-info btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="Xem Users" data-placement="bottom"><i class="fas fa-users"></i></a>
                        <form method="POST" action="{{route('admin.sub-admins.destroy', $subAdmin->id)}}">
                          @csrf 
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn" data-id={{$subAdmin->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Xóa"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$subAdmins->links()}}</span>
        @else
          <h6 class="text-center">Chưa có Sub Admin nào! <a href="{{route('admin.sub-admins.create')}}" class="text-primary">Tạo Sub Admin mới</a> </h6>
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
      .zoom {
        transition: transform .2s; /* Animation */
      }

      .zoom:hover {
        transform: scale(3.2);
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
                    "targets":[7]
                }
            ]
        } );

        // Sweet alert

        function deleteData(id){
            
        }
  </script>
  <script>
      $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
          $('.dltBtn').click(function(e){
            var form=$(this).closest('form');
              var dataID=$(this).data('id');
              // alert(dataID);
              e.preventDefault();
              swal({
                    title: "Bạn có chắc chắn?",
                    text: "Khi xóa Sub Admin, tất cả users thuộc quyền sẽ trở thành users tự do!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                       form.submit();
                    } else {
                        swal("Dữ liệu của bạn được bảo toàn!");
                    }
                });
          })
      })
  </script>
@endpush