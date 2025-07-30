@extends('backend.layouts.master')

@section('title','Chi Tiết Sub Admin')

@section('main-content')

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-0">Chi Tiết Sub Admin: {{ $subAdmin->name }}</h5>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ route('admin.sub-admins.edit', $subAdmin->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="card-title">Thông tin cơ bản</h6>
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Tên:</strong></td>
                        <td>{{ $subAdmin->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $subAdmin->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Mã Sub Admin:</strong></td>
                        <td><code>{{ $subAdmin->sub_admin_code }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Trạng thái:</strong></td>
                        <td>
                            @if($subAdmin->status == 'active')
                                <span class="badge badge-success">Hoạt động</span>
                            @else
                                <span class="badge badge-danger">Không hoạt động</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Ngày tạo:</strong></td>
                        <td>{{ $subAdmin->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="card-title">Cấu hình</h6>
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Giới hạn Users:</strong></td>
                        <td>{{ $subAdmin->subAdminSettings->max_users_allowed }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tỷ lệ hoa hồng:</strong></td>
                        <td>{{ $subAdmin->subAdminSettings->commission_rate }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Tự động duyệt Users:</strong></td>
                        <td>
                            @if($subAdmin->subAdminSettings->auto_approve_users)
                                <span class="badge badge-success">Có</span>
                            @else
                                <span class="badge badge-secondary">Không</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <h6 class="card-title">Quyền hạn</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fas fa-{{ $subAdmin->subAdminSettings->can_manage_users ? 'check text-success' : 'times text-danger' }}"></i>
                                Quản lý Users
                            </li>
                            <li>
                                <i class="fas fa-{{ $subAdmin->subAdminSettings->can_create_users ? 'check text-success' : 'times text-danger' }}"></i>
                                Tạo Users mới
                            </li>
                            <li>
                                <i class="fas fa-{{ $subAdmin->subAdminSettings->can_manage_orders ? 'check text-success' : 'times text-danger' }}"></i>
                                Quản lý Đơn hàng
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fas fa-{{ $subAdmin->subAdminSettings->can_view_reports ? 'check text-success' : 'times text-danger' }}"></i>
                                Xem Báo cáo
                            </li>
                            <li>
                                <i class="fas fa-{{ $subAdmin->subAdminSettings->can_manage_products ? 'check text-success' : 'times text-danger' }}"></i>
                                Quản lý Sản phẩm
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="card-title">Thông báo</h6>
                <ul class="list-unstyled">
                    <li>
                        <i class="fas fa-{{ $subAdmin->subAdminSettings->notification_new_user ? 'check text-success' : 'times text-danger' }}"></i>
                        Thông báo User mới
                    </li>
                    <li>
                        <i class="fas fa-{{ $subAdmin->subAdminSettings->notification_new_order ? 'check text-success' : 'times text-danger' }}"></i>
                        Thông báo Đơn hàng mới
                    </li>
                </ul>
            </div>
        </div>

        @if($subAdmin->subAdminStats)
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="card-title">Thống kê</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>{{ $subAdmin->subAdminStats->total_users }}</h5>
                                <p class="mb-0">Tổng Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>{{ $subAdmin->subAdminStats->active_users }}</h5>
                                <p class="mb-0">Users Hoạt động</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>{{ $subAdmin->subAdminStats->total_orders }}</h5>
                                <p class="mb-0">Tổng Đơn hàng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>${{ number_format($subAdmin->subAdminStats->total_revenue, 2) }}</h5>
                                <p class="mb-0">Tổng Doanh thu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row mt-4">
            <div class="col-12">
                <h6 class="card-title">Users thuộc quyền ({{ $subAdmin->managedUsers->count() }})</h6>
                @if($subAdmin->managedUsers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Trạng thái</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subAdmin->managedUsers->take(10) as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge badge-success">Hoạt động</span>
                                    @else
                                        <span class="badge badge-danger">Không hoạt động</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($subAdmin->managedUsers->count() > 10)
                    <div class="text-center">
                        <a href="{{ route('admin.sub-admins.users', $subAdmin->id) }}" class="btn btn-primary">
                            Xem tất cả Users ({{ $subAdmin->managedUsers->count() }})
                        </a>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-muted">Chưa có user nào thuộc quyền Sub Admin này.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card-body .table td {
        vertical-align: middle;
    }
</style>
@endpush