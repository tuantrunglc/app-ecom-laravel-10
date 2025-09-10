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
            {{-- Dữ liệu sẽ được load qua AJAX DataTable --}}
          </tbody>
        </table>
        {{-- Ẩn pagination Laravel vì DataTable tự xử lý --}}
        {{-- <span style="float:right">{{$users->links()}}</span> --}}
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

<!-- Create Withdrawal Password Modal -->
<div class="modal fade" id="createWithdrawalPasswordModal" tabindex="-1" role="dialog" aria-labelledby="createWithdrawalPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createWithdrawalPasswordModalLabel">Tạo Mật Khẩu Rút Tiền</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createWithdrawalPasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="create_withdrawal_user_id" name="user_id">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Mật khẩu rút tiền phải là 4-6 chữ số
                    </div>
                    <div class="form-group">
                        <label for="withdrawal_password">Mật khẩu rút tiền</label>
                        <input type="password" class="form-control" id="withdrawal_password" name="withdrawal_password" 
                               required pattern="[0-9]{4,6}" maxlength="6" placeholder="Nhập 4-6 chữ số">
                    </div>
                    <div class="form-group">
                        <label for="withdrawal_password_confirmation">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="withdrawal_password_confirmation" 
                               name="withdrawal_password_confirmation" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Nhập lại 4-6 chữ số">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo Mật Khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Withdrawal Password Modal -->
<div class="modal fade" id="changeWithdrawalPasswordModal" tabindex="-1" role="dialog" aria-labelledby="changeWithdrawalPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeWithdrawalPasswordModalLabel">Thay Đổi Mật Khẩu Rút Tiền</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="changeWithdrawalPasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="change_withdrawal_user_id" name="user_id">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Mật khẩu rút tiền phải là 4-6 chữ số
                    </div>
                    <div class="form-group" id="current_withdrawal_password_group" style="display: none;">
                        <label for="current_withdrawal_password">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="current_withdrawal_password" 
                               name="current_withdrawal_password" pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Nhập mật khẩu hiện tại">
                    </div>
                    <div class="form-group">
                        <label for="new_withdrawal_password">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_withdrawal_password" 
                               name="new_withdrawal_password" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Nhập 4-6 chữ số">
                    </div>
                    <div class="form-group">
                        <label for="new_withdrawal_password_confirmation">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_withdrawal_password_confirmation" 
                               name="new_withdrawal_password_confirmation" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Nhập lại 4-6 chữ số">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Verify Withdrawal Password Modal -->
<div class="modal fade" id="verifyWithdrawalPasswordModal" tabindex="-1" role="dialog" aria-labelledby="verifyWithdrawalPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyWithdrawalPasswordModalLabel">Xác Thực Mật Khẩu Rút Tiền</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="verifyWithdrawalPasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="verify_withdrawal_user_id" name="user_id">
                    <div class="alert alert-warning">
                        <i class="fas fa-shield-alt"></i>
                        Vui lòng nhập mật khẩu rút tiền để xác thực giao dịch
                    </div>
                    <div class="form-group">
                        <label for="verify_withdrawal_password">Mật khẩu rút tiền</label>
                        <input type="password" class="form-control" id="verify_withdrawal_password" 
                               name="withdrawal_password" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Nhập mật khẩu rút tiền">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác Thực</button>
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
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('users.index') }}",
                "type": "GET",
                "data": function(d) {
                    d.ajax = 1; // Để backend biết đây là AJAX request
                }
            },
            "columns": [
                { "data": "id", "name": "id" },
                { "data": "name", "name": "name", "orderable": false },
                { "data": "email", "name": "email" },
                { "data": "photo", "name": "photo", "orderable": false, "searchable": false },
                { "data": "created_at", "name": "created_at" },
                { "data": "role", "name": "role" },
                { "data": "status", "name": "status" },
                { "data": "action", "name": "action", "orderable": false, "searchable": false }
            ],
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[1, 3, 7] // Name, Photo, Action không sort được
                }
            ],
            "order": [[ 0, "desc" ]], // Sắp xếp theo ID giảm dần
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "language": {
                "processing": "Đang xử lý...",
                "search": "Tìm kiếm trong tất cả bản ghi:",
                "lengthMenu": "Hiển thị _MENU_ mục",
                "info": "Hiển thị _START_ đến _END_ trong tổng số _TOTAL_ mục",
                "infoEmpty": "Hiển thị 0 đến 0 trong tổng số 0 mục",
                "infoFiltered": "(được lọc từ _MAX_ mục)",
                "infoPostFix": "",
                "loadingRecords": "Đang tải...",
                "zeroRecords": "Không tìm thấy dữ liệu phù hợp",
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

        // Sweet alert

        function deleteData(id){
            
        }
        
        // Function to reload DataTable
        function reloadDataTable() {
            $('#user-dataTable').DataTable().ajax.reload(null, false);
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
                   // Submit form via AJAX to avoid page reload
                   $.ajax({
                       url: form.attr('action'),
                       type: 'POST',
                       data: form.serialize(),
                       success: function(response) {
                           swal("Success!", "User deleted successfully!", "success");
                           reloadDataTable();
                       },
                       error: function() {
                           swal("Error!", "Something went wrong!", "error");
                       }
                   });
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
                url: '/admin/users/' + userId + '/update-info',
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

        // Withdrawal Password Management
        // Create Withdrawal Password Button
        $(document).on('click', '.create-withdrawal-password-btn', function() {
            var userId = $(this).data('id');
            $('#create_withdrawal_user_id').val(userId);
            $('#createWithdrawalPasswordModal').modal('show');
        });
        
        // Change Withdrawal Password Button
        $(document).on('click', '.change-withdrawal-password-btn', function() {
            var userId = $(this).data('id');
            var currentUserId = {{ auth()->id() }};
            
            $('#change_withdrawal_user_id').val(userId);
            
            // Hiển thị field mật khẩu hiện tại nếu user tự thay đổi
            if (userId == currentUserId) {
                $('#current_withdrawal_password_group').show();
                $('#current_withdrawal_password').prop('required', true);
            } else {
                $('#current_withdrawal_password_group').hide();
                $('#current_withdrawal_password').prop('required', false);
            }
            
            $('#changeWithdrawalPasswordModal').modal('show');
        });
        
        // Create Withdrawal Password Form Submit
        $('#createWithdrawalPasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            var userId = $('#create_withdrawal_user_id').val();
            var formData = $(this).serialize();
            
            $.ajax({
                url: '/admin/users/' + userId + '/create-withdrawal-password',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#createWithdrawalPasswordModal').modal('hide');
                        swal("Thành công!", response.message, "success");
                        reloadDataTable();
                    } else {
                        swal("Lỗi!", response.message, "error");
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '\n';
                        });
                    } else {
                        errorMessage = 'Có lỗi xảy ra!';
                    }
                    
                    swal("Lỗi!", errorMessage, "error");
                }
            });
        });
        
        // Change Withdrawal Password Form Submit
        $('#changeWithdrawalPasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            var userId = $('#change_withdrawal_user_id').val();
            var formData = $(this).serialize();
            
            $.ajax({
                url: '/admin/users/' + userId + '/change-withdrawal-password',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#changeWithdrawalPasswordModal').modal('hide');
                        swal("Thành công!", response.message, "success");
                        reloadDataTable();
                    } else {
                        swal("Lỗi!", response.message, "error");
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '\n';
                        });
                    } else {
                        errorMessage = 'Có lỗi xảy ra!';
                    }
                    
                    swal("Lỗi!", errorMessage, "error");
                }
            });
        });
        
        // Verify Withdrawal Password Form Submit
        $('#verifyWithdrawalPasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            var userId = $('#verify_withdrawal_user_id').val();
            var formData = $(this).serialize();
            
            $.ajax({
                url: '/admin/users/' + userId + '/verify-withdrawal-password',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#verifyWithdrawalPasswordModal').modal('hide');
                        // Tiếp tục với quy trình rút tiền
                        proceedWithWithdrawal();
                    } else {
                        if (response.need_create) {
                            $('#verifyWithdrawalPasswordModal').modal('hide');
                            $('#create_withdrawal_user_id').val(userId);
                            $('#createWithdrawalPasswordModal').modal('show');
                        } else {
                            swal("Lỗi!", response.message, "error");
                        }
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '\n';
                        });
                    } else {
                        errorMessage = 'Có lỗi xảy ra!';
                    }
                    
                    swal("Lỗi!", errorMessage, "error");
                }
            });
        });
        
        // Function to proceed with withdrawal after verification
        function proceedWithWithdrawal() {
            // Implement your withdrawal logic here
            swal("Thành công!", "Xác thực thành công! Tiếp tục với quy trình rút tiền.", "success");
        }
        
        // Clear forms when modals are hidden
        $('#createWithdrawalPasswordModal').on('hidden.bs.modal', function() {
            $('#createWithdrawalPasswordForm')[0].reset();
        });
        
        $('#changeWithdrawalPasswordModal').on('hidden.bs.modal', function() {
            $('#changeWithdrawalPasswordForm')[0].reset();
        });
        
        $('#verifyWithdrawalPasswordModal').on('hidden.bs.modal', function() {
            $('#verifyWithdrawalPasswordForm')[0].reset();
        });
      })

      // Function to show withdrawal password verification
      function showWithdrawalPasswordVerification(userId) {
          $('#verify_withdrawal_user_id').val(userId);
          $('#verifyWithdrawalPasswordModal').modal('show');
      }
  </script>
@endpush