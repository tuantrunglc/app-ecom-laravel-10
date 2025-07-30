@extends('backend.layouts.master')

@section('title','Báo Cáo Sub Admin')

@section('main-content')
<div class="page-heading">
    <h3>Báo Cáo & Thống Kê</h3>
</div>

<div class="page-content">
    <div class="row mb-3">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon purple">
                                <i class="iconly-boldProfile"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Tổng Users</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['total_users'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon blue">
                                <i class="iconly-boldProfile"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Users Hoạt động</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['active_users'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon green">
                                <i class="iconly-boldBag-2"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Tổng Đơn hàng</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['total_orders'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon red">
                                <i class="iconly-boldBookmark"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Tổng Doanh thu</h6>
                            <h6 class="font-extrabold mb-0">${{ number_format($stats['total_revenue'], 2) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 col-md-6 col-lg-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon orange">
                                <i class="iconly-boldCalendar"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Đơn hàng tháng này</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['orders_this_month'] ?? 0 }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-lg-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon teal">
                                <i class="iconly-boldWallet"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Doanh thu tháng này</h6>
                            <h6 class="font-extrabold mb-0">${{ number_format($stats['revenue_this_month'] ?? 0, 2) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Thông tin Chi tiết</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td><strong>Mã Sub Admin:</strong></td>
                                        <td><code>{{ auth()->user()->sub_admin_code }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Giới hạn Users:</strong></td>
                                        <td>{{ $stats['total_users'] }}/{{ auth()->user()->subAdminSettings->max_users_allowed }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tỷ lệ hoa hồng:</strong></td>
                                        <td>{{ auth()->user()->subAdminSettings->commission_rate }}%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Users không hoạt động:</strong></td>
                                        <td>{{ $stats['inactive_users'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Quyền hạn của bạn:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-{{ auth()->user()->subAdminSettings->can_manage_users ? 'check text-success' : 'times text-danger' }}"></i>
                                    Quản lý Users
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ auth()->user()->subAdminSettings->can_create_users ? 'check text-success' : 'times text-danger' }}"></i>
                                    Tạo Users mới
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ auth()->user()->subAdminSettings->can_manage_orders ? 'check text-success' : 'times text-danger' }}"></i>
                                    Quản lý Đơn hàng
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ auth()->user()->subAdminSettings->can_view_reports ? 'check text-success' : 'times text-danger' }}"></i>
                                    Xem Báo cáo  
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ auth()->user()->subAdminSettings->can_manage_products ? 'check text-success' : 'times text-danger' }}"></i>
                                    Quản lý Sản phẩm
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stats-icon.purple {
        background-color: #667eea;
        color: white;
    }
    .stats-icon.blue {
        background-color: #3b82f6;
        color: white;
    }
    .stats-icon.green {
        background-color: #10b981;
        color: white;
    }
    .stats-icon.red {
        background-color: #ef4444;
        color: white;
    }
    .stats-icon.orange {
        background-color: #f59e0b;
        color: white;
    }
    .stats-icon.teal {
        background-color: #14b8a6;
        color: white;
    }
</style>
@endpush