@extends('backend.layouts.master')

@section('title','Chỉnh sửa Sub Admin')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh sửa Sub Admin: {{ $subAdmin->name }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('admin.sub-admins.update', $subAdmin->id)}}">
        {{csrf_field()}}
        @method('PUT')

        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tên <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="name" placeholder="Nhập tên Sub Admin"  value="{{old('name', $subAdmin->name)}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputEmail" class="col-form-label">Email <span class="text-danger">*</span></label>
          <input id="inputEmail" type="email" name="email" placeholder="Nhập email"  value="{{old('email', $subAdmin->email)}}" class="form-control">
          @error('email')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputPassword" class="col-form-label">Mật khẩu mới (để trống nếu không đổi)</label>
          <input id="inputPassword" type="password" name="password" placeholder="Nhập mật khẩu mới" class="form-control">
          @error('password')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="status" class="col-form-label">Trạng thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active" {{(old('status', $subAdmin->status)=='active')? 'selected' : ''}}>Hoạt động</option>
              <option value="inactive" {{(old('status', $subAdmin->status)=='inactive')? 'selected' : ''}}>Không hoạt động</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="max_users_allowed" class="col-form-label">Giới hạn Users <span class="text-danger">*</span></label>
              <input id="max_users_allowed" type="number" name="max_users_allowed" placeholder="Nhập số lượng users tối đa" value="{{old('max_users_allowed', $subAdmin->subAdminSettings->max_users_allowed)}}" class="form-control">
              @error('max_users_allowed')
              <span class="text-danger">{{$message}}</span>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="commission_rate" class="col-form-label">Tỷ lệ hoa hồng (%)</label>
              <input id="commission_rate" type="number" step="0.01" name="commission_rate" placeholder="0.00" value="{{old('commission_rate', $subAdmin->subAdminSettings->commission_rate)}}" class="form-control">
              @error('commission_rate')
              <span class="text-danger">{{$message}}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0">Cấu hình Quyền hạn</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="can_manage_users" value="1" id="can_manage_users" {{old('can_manage_users', $subAdmin->subAdminSettings->can_manage_users) ? 'checked' : ''}}>
                    <label class="form-check-label" for="can_manage_users">
                      Có thể quản lý Users
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="can_create_users" value="1" id="can_create_users" {{old('can_create_users', $subAdmin->subAdminSettings->can_create_users) ? 'checked' : ''}}>
                    <label class="form-check-label" for="can_create_users">
                      Có thể tạo Users mới
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="can_manage_orders" value="1" id="can_manage_orders" {{old('can_manage_orders', $subAdmin->subAdminSettings->can_manage_orders) ? 'checked' : ''}}>
                    <label class="form-check-label" for="can_manage_orders">
                      Có thể quản lý Đơn hàng
                    </label>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="can_view_reports" value="1" id="can_view_reports" {{old('can_view_reports', $subAdmin->subAdminSettings->can_view_reports) ? 'checked' : ''}}>
                    <label class="form-check-label" for="can_view_reports">
                      Có thể xem Báo cáo
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="can_manage_products" value="1" id="can_manage_products" {{old('can_manage_products', $subAdmin->subAdminSettings->can_manage_products) ? 'checked' : ''}}>
                    <label class="form-check-label" for="can_manage_products">
                      Có thể quản lý Sản phẩm
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header bg-info text-white">
            <h6 class="mb-0">Cấu hình Thông báo</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="auto_approve_users" value="1" id="auto_approve_users" {{old('auto_approve_users', $subAdmin->subAdminSettings->auto_approve_users) ? 'checked' : ''}}>
                    <label class="form-check-label" for="auto_approve_users">
                      Tự động duyệt Users mới
                    </label>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="notification_new_user" value="1" id="notification_new_user" {{old('notification_new_user', $subAdmin->subAdminSettings->notification_new_user) ? 'checked' : ''}}>
                    <label class="form-check-label" for="notification_new_user">
                      Nhận thông báo User mới
                    </label>
                  </div>
                </div>

                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="notification_new_order" value="1" id="notification_new_order" {{old('notification_new_order', $subAdmin->subAdminSettings->notification_new_order) ? 'checked' : ''}}>
                    <label class="form-check-label" for="notification_new_order">
                      Nhận thông báo Đơn hàng mới
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="alert alert-warning mt-3">
          <h6><i class="fas fa-info-circle"></i> Thông tin hiện tại</h6>
          <ul class="mb-0">
            <li>Mã Sub Admin: <strong>{{ $subAdmin->sub_admin_code }}</strong></li>
            <li>Tổng Users hiện tại: <strong>{{ $subAdmin->getManagedUsersCount() }}</strong></li>
            <li>Users hoạt động: <strong>{{ $subAdmin->getActiveUsersCount() }}</strong></li>
            <li>Tạo lúc: <strong>{{ $subAdmin->created_at->format('d/m/Y H:i') }}</strong></li>
          </ul>
        </div>

        <div class="form-group mb-3 mt-3">
          <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-secondary">Hủy</a>
          <button class="btn btn-success" type="submit">Cập nhật Sub Admin</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
@endpush