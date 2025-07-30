@extends('backend.layouts.master')

@section('title','Tạo User Mới')

@section('main-content')

<div class="card">
    <h5 class="card-header">Tạo User Mới</h5>
    <div class="card-body">
      <form method="post" action="{{route('sub-admin.users.store')}}">
        {{csrf_field()}}
        
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Tên <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="name" placeholder="Nhập tên user"  value="{{old('name')}}" class="form-control">
          @error('name')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputEmail" class="col-form-label">Email <span class="text-danger">*</span></label>
          <input id="inputEmail" type="email" name="email" placeholder="Nhập email"  value="{{old('email')}}" class="form-control">
          @error('email')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="inputPassword" class="col-form-label">Mật khẩu <span class="text-danger">*</span></label>
          <input id="inputPassword" type="password" name="password" placeholder="Nhập mật khẩu"  value="{{old('password')}}" class="form-control">
          @error('password')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="status" class="col-form-label">Trạng thái <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="">-- Chọn trạng thái --</option>
              <option value="active" {{(old('status')=='active')? 'selected' : ''}}>Hoạt động</option>
              <option value="inactive" {{(old('status')=='inactive')? 'selected' : ''}}>Không hoạt động</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="alert alert-info">
          <h6><i class="fas fa-info-circle"></i> Thông tin</h6>
          <p class="mb-2">User sẽ được tạo với các thông tin sau:</p>
          <ul class="mb-0">
            <li>Thuộc quyền quản lý của bạn</li>
            <li>Mã giới thiệu: <strong>{{auth()->user()->sub_admin_code}}</strong></li>
            <li>Giới hạn hiện tại: {{auth()->user()->getManagedUsersCount()}}/{{auth()->user()->subAdminSettings->max_users_allowed}} users</li>
          </ul>
        </div>

        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">Đặt lại</button>
           <button class="btn btn-success" type="submit">Tạo User</button>
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