@extends('backend.layouts.master')

@section('title','Quản Lý Users')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Danh Sách Users Thuộc Quyền</h6>
    </div>
    <div class="card-body">
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
            {{-- Dữ liệu sẽ được load qua AJAX DataTable --}}
          </tbody>
        </table>
        {{-- Ẩn pagination Laravel vì DataTable tự xử lý --}}
        {{-- <span style="float:right">{{$users->links()}}</span> --}}
        @else
          <h6 class="text-center">Chưa có user nào thuộc quyền! 
            @if($subAdmin->subAdminSettings->can_create_users)
            <a href="{{route('sub-admin.users.create')}}" class="text-primary">Tạo user mới</a>
            @endif
          </h6>
        @endif
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
                                <label for="edit_phone_number">Số điện thoại</label>
                                <input type="text" class="form-control" id="edit_phone_number" name="phone_number" maxlength="15" pattern="[0-9+\-\s()]{10,15}">
                                <small class="form-text text-muted">Sub admin có thể chỉnh sửa phone number</small>
                            </div>
                        </div>
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
                                <input type="text" class="form-control" id="edit_wallet_balance" name="wallet_balance" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small class="form-text text-muted">Số dư ví chỉ có thể thay đổi thông qua hệ thống giao dịch</small>
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
      $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#user-dataTable').DataTable( {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('sub-admin.users.index') }}",
                "type": "GET",
                "data": function(d) {
                    d.ajax = 1; // Để backend biết đây là AJAX request
                }
            },
            "columns": [
                { "data": "id", "name": "id" },
                { "data": "name", "name": "name" },
                { "data": "email", "name": "email" },
                { "data": "wallet_balance", "name": "wallet_balance", "orderable": false, "searchable": false },
                { "data": "status", "name": "status" },
                { "data": "created_at", "name": "created_at" },
                { "data": "action", "name": "action", "orderable": false, "searchable": false }
            ],
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[3, 6] // Wallet Balance và Action không sort được
                }
            ],
            "order": [[ 0, "desc" ]], // Sắp xếp theo ID giảm dần
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "language": {
                "processing": "Đang xử lý...",
                "search": "Tìm kiếm trong tất cả bản ghi:",
                "lengthMenu": "Hiển thị _MENU_ dòng",
                "info": "Hiển thị _START_ đến _END_ trong _TOTAL_ dòng",
                "infoEmpty": "Hiển thị 0 đến 0 trong 0 dòng",
                "infoFiltered": "(lọc từ _MAX_ tổng số dòng)",
                "zeroRecords": "Không tìm thấy dữ liệu",
                "emptyTable": "Không có dữ liệu trong bảng",
                "paginate": {
                    "first": "Đầu tiên",
                    "previous": "Trước",
                    "next": "Tiếp theo",
                    "last": "Cuối cùng"
                },
                "aria": {
                    "sortAscending": ": Sắp xếp tăng dần",
                    "sortDescending": ": Sắp xếp giảm dần"
                }
            }
        } );

        // Function to reload DataTable
        function reloadDataTable() {
            $('#user-dataTable').DataTable().ajax.reload(null, false);
        }

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
                url: '/sub-admin/users/' + userId + '/change-password',
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
            var statusCell = btn.closest('tr').find('td:nth-child(5)'); // Status column
            
            swal({
                title: "Xác nhận",
                text: currentStatus === 'active' ? "Bạn có muốn khóa tài khoản này?" : "Bạn có muốn mở khóa tài khoản này?",
                icon: "warning",
                buttons: true,
            })
            .then((willToggle) => {
                if (willToggle) {
                    $.ajax({
                        url: '/sub-admin/users/' + userId + '/toggle-status',
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
                            
                            // Reload table to update status badge
                            reloadDataTable();
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
                url: '/sub-admin/users/' + userId + '/details',
                type: 'GET',
                success: function(response){
                    var user = response.user;
                    $('#edit_user_id').val(user.id);
                    $('#edit_name').val(user.name);
                    $('#edit_email').val(user.email);
                    $('#edit_phone_number').val(user.phone_number);
                    $('#edit_birth_date').val(user.birth_date);
                    $('#edit_age').val(user.age);
                    $('#edit_gender').val(user.gender);
                    $('#edit_address').val(user.address);
                    $('#edit_bank_name').val(user.bank_name);
                    $('#edit_bank_account_number').val(user.bank_account_number);
                    $('#edit_bank_account_name').val(user.bank_account_name);
                    let balanceNum = Number(user.wallet_balance);
                    if (isNaN(balanceNum)) balanceNum = 0;
                    $('#edit_wallet_balance').val(balanceNum.toFixed(2));   

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
                url: '/sub-admin/users/' + userId + '/update-info',
                type: 'POST',
                data: formData,
                success: function(response){
                    $('#editUserModal').modal('hide');
                    swal("Success!", response.success, "success");
                    
                    // Reload table to update user info
                    reloadDataTable();
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
      });
  </script>
@endpush