@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh Sách Người Dùng</h6>
      <a href="{{route('users.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm Người Dùng"><i class="fas fa-plus"></i> Thêm Người Dùng</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="user-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tên</th>
              <th>Email</th>
              <th>Hình Ảnh</th>
              <th>Ngày Tham Gia</th>
              <th>Vai Trò</th>
              <th>Trạng Thái</th>
              <th>Hành Động</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
                <th>S.N.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Photo</th>
                <th>Join Date</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
          </tfoot>
          <tbody>
            @foreach($users as $user)   
                <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td>
                        @if($user->photo)
                            <img src="{{$user->photo}}" class="img-fluid rounded-circle" style="max-width:50px" alt="{{$user->photo}}">
                        @else
                            <img src="{{asset('backend/img/avatar.png')}}" class="img-fluid rounded-circle" style="max-width:50px" alt="avatar.png">
                        @endif
                    </td>
                    <td>{{(($user->created_at)? $user->created_at->diffForHumans() : '')}}</td>
                    <td>{{$user->role}}</td>
                    <td>
                        @if($user->status=='active')
                            <span class="badge badge-success">{{$user->status}}</span>
                        @else
                            <span class="badge badge-warning">{{$user->status}}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-wrap">
                            <!-- Edit User Info -->
                            <button class="btn btn-info btn-sm mr-1 mb-1 edit-user-btn" data-id="{{$user->id}}" data-toggle="tooltip" title="Chỉnh sửa thông tin" data-placement="bottom">
                                <i class="fas fa-user-edit"></i>
                            </button>
                            
                            <!-- Change Password -->
                            <button class="btn btn-warning btn-sm mr-1 mb-1 change-password-btn" data-id="{{$user->id}}" data-toggle="tooltip" title="Đổi mật khẩu" data-placement="bottom">
                                <i class="fas fa-key"></i>
                            </button>
                            
                            <!-- Toggle Status -->
                            <button class="btn btn-sm mr-1 mb-1 toggle-status-btn {{$user->status == 'active' ? 'btn-secondary' : 'btn-success'}}" 
                                    data-id="{{$user->id}}" 
                                    data-status="{{$user->status}}"
                                    data-toggle="tooltip" 
                                    title="{{$user->status == 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản'}}" 
                                    data-placement="bottom">
                                <i class="fas {{$user->status == 'active' ? 'fa-lock' : 'fa-unlock'}}"></i>
                            </button>
                            
                            <!-- Original Edit -->
                            <a href="{{route('users.edit',$user->id)}}" class="btn btn-primary btn-sm mr-1 mb-1" data-toggle="tooltip" title="Chỉnh sửa" data-placement="bottom">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <!-- Delete -->
                            <form method="POST" action="{{route('users.destroy',[$user->id])}}" style="display: inline;">
                                @csrf 
                                @method('delete')
                                <button class="btn btn-danger btn-sm mb-1 dltBtn" data-id={{$user->id}} data-toggle="tooltip" data-placement="bottom" title="Xóa">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                    {{-- Delete Modal --}}
                    {{-- <div class="modal fade" id="delModal{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="#delModal{{$user->id}}Label" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="#delModal{{$user->id}}Label">Delete user</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <form method="post" action="{{ route('users.destroy',$user->id) }}">
                                @csrf 
                                @method('delete')
                                <button type="submit" class="btn btn-danger" style="margin:auto; text-align:center">Parmanent delete user</button>
                              </form>
                            </div>
                          </div>
                        </div>
                    </div> --}}
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$users->links()}}</span>
      </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Đổi Mật Khẩu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="change_password_user_id" name="user_id">
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Đổi Mật Khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Info Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Chỉnh Sửa Thông Tin User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Tên</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_birth_date">Ngày sinh</label>
                                <input type="date" class="form-control" id="edit_birth_date" name="birth_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_age">Tuổi</label>
                                <input type="number" class="form-control" id="edit_age" name="age" min="1" max="120">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_gender">Giới tính</label>
                                <select class="form-control" id="edit_gender" name="gender">
                                    <option value="">Chọn giới tính</option>
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_wallet_balance">Số dư ví</label>
                                <input type="text" class="form-control" id="edit_wallet_balance" name="wallet_balance" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Địa chỉ</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2" maxlength="255"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_bank_name">Tên ngân hàng</label>
                                <input type="text" class="form-control" id="edit_bank_name" name="bank_name" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_bank_account_number">Số tài khoản</label>
                                <input type="text" class="form-control" id="edit_bank_account_number" name="bank_account_number" maxlength="50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_bank_account_name">Tên chủ tài khoản</label>
                                <input type="text" class="form-control" id="edit_bank_account_name" name="bank_account_name" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập Nhật</button>
                </div>
            </form>
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
      .btn-sm {
          padding: 0.25rem 0.5rem;
          font-size: 0.875rem;
          line-height: 1.5;
          border-radius: 0.2rem;
      }
      .d-flex .btn {
          white-space: nowrap;
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
                    "targets":[7] // Chỉ cột Action không sort được
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

        // Delete functionality
        $(document).on('click', '.dltBtn', function(e){
            var form=$(this).closest('form');
            var dataID=$(this).data('id');
            e.preventDefault();
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this data!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                   form.submit();
                } else {
                    swal("Your data is safe!");
                }
            });
        });

        // Change Password functionality
        $(document).on('click', '.change-password-btn', function(){
            var userId = $(this).data('id');
            $('#change_password_user_id').val(userId);
            $('#changePasswordModal').modal('show');
        });

        $('#changePasswordForm').submit(function(e){
            e.preventDefault();
            var userId = $('#change_password_user_id').val();
            var formData = $(this).serialize();
            
            $.ajax({
                url: '/admin/users/' + userId + '/change-password',
                type: 'POST',
                data: formData,
                success: function(response){
                    $('#changePasswordModal').modal('hide');
                    swal("Success!", response.success, "success");
                    $('#changePasswordForm')[0].reset();
                },
                error: function(xhr){
                    var errors = xhr.responseJSON;
                    if(errors.error) {
                        swal("Error!", errors.error, "error");
                    } else if(errors.errors) {
                        var errorMsg = '';
                        $.each(errors.errors, function(key, value) {
                            errorMsg += value[0] + '\n';
                        });
                        swal("Validation Error!", errorMsg, "error");
                    }
                }
            });
        });

        // Toggle Status functionality
        $(document).on('click', '.toggle-status-btn', function(){
            var userId = $(this).data('id');
            var currentStatus = $(this).data('status');
            var btn = $(this);
            var statusCell = btn.closest('tr').find('td:nth-child(7)'); // Status column
            
            swal({
                title: "Xác nhận",
                text: currentStatus === 'active' ? "Bạn có muốn khóa tài khoản này?" : "Bạn có muốn mở khóa tài khoản này?",
                icon: "warning",
                buttons: true,
            })
            .then((willToggle) => {
                if (willToggle) {
                    $.ajax({
                        url: '/admin/users/' + userId + '/toggle-status',
                        type: 'POST',
                        success: function(response){
                            swal("Success!", response.success, "success");
                            
                            // Update button
                            btn.data('status', response.new_status);
                            if(response.new_status === 'active') {
                                btn.removeClass('btn-success').addClass('btn-secondary');
                                btn.find('i').removeClass('fa-unlock').addClass('fa-lock');
                                btn.attr('title', 'Khóa tài khoản');
                            } else {
                                btn.removeClass('btn-secondary').addClass('btn-success');
                                btn.find('i').removeClass('fa-lock').addClass('fa-unlock');
                                btn.attr('title', 'Mở khóa tài khoản');
                            }
                            
                            // Update status badge
                            statusCell.html(response.status_badge);
                        },
                        error: function(xhr){
                            var errors = xhr.responseJSON;
                            swal("Error!", errors.error || "Có lỗi xảy ra", "error");
                        }
                    });
                }
            });
        });

        // Edit User Info functionality
        $(document).on('click', '.edit-user-btn', function(){
            var userId = $(this).data('id');
            
            // Load user data
            $.ajax({
                url: '/admin/users/' + userId + '/details',
                type: 'GET',
                success: function(response){
                    var user = response.user;
                    $('#edit_user_id').val(user.id);
                    $('#edit_name').val(user.name);
                    $('#edit_email').val(user.email);
                    $('#edit_birth_date').val(user.birth_date);
                    $('#edit_age').val(user.age);
                    $('#edit_gender').val(user.gender);
                    $('#edit_address').val(user.address);
                    $('#edit_bank_name').val(user.bank_name);
                    $('#edit_bank_account_number').val(user.bank_account_number);
                    $('#edit_bank_account_name').val(user.bank_account_name);
                    $('#edit_wallet_balance').val('$' + parseFloat(user.wallet_balance || 0).toFixed(2));
                    
                    $('#editUserModal').modal('show');
                },
                error: function(xhr){
                    var errors = xhr.responseJSON;
                    swal("Error!", errors.error || "Không thể tải thông tin user", "error");
                }
            });
        });

        $('#editUserForm').submit(function(e){
            e.preventDefault();
            var userId = $('#edit_user_id').val();
            var formData = $(this).serialize();
            
            $.ajax({
                url: '/admin/users/' + userId + '/update-info',
                type: 'POST',
                data: formData,
                success: function(response){
                    $('#editUserModal').modal('hide');
                    swal("Success!", response.success, "success");
                    
                    // Update table row
                    var row = $('.edit-user-btn[data-id="' + userId + '"]').closest('tr');
                    row.find('td:nth-child(2)').text($('#edit_name').val()); // Name column
                    row.find('td:nth-child(3)').text($('#edit_email').val()); // Email column
                },
                error: function(xhr){
                    var errors = xhr.responseJSON;
                    if(errors.error) {
                        swal("Error!", errors.error, "error");
                    } else if(errors.errors) {
                        var errorMsg = '';
                        $.each(errors.errors, function(key, value) {
                            errorMsg += value[0] + '\n';
                        });
                        swal("Validation Error!", errorMsg, "error");
                    }
                }
            });
        });
      })
  </script>
@endpush