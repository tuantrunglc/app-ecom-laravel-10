@extends('backend.layouts.master')

@section('title','Sub Admin Dashboard')

@section('main-content')
<div class="page-heading">
    <h3>Sub Admin Dashboard</h3>
</div>

<div class="page-content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Chào mừng, {{ $subAdmin->name }}!</h4>
                    <p class="mb-0">Mã Sub Admin của bạn: <strong>{{ $subAdmin->sub_admin_code }}</strong></p>
                </div>
            </div>
        </div>
    </div>

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
                            <h6 class="text-muted font-semibold">Doanh thu</h6>
                            <h6 class="font-extrabold mb-0">${{ number_format($stats['total_revenue'], 2) }}</h6>
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
                    <h4>Quyền hạn của bạn</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-{{ $subAdmin->subAdminSettings->can_manage_users ? 'check text-success' : 'times text-danger' }}"></i>
                                    Quản lý Users
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ $subAdmin->subAdminSettings->can_create_users ? 'check text-success' : 'times text-danger' }}"></i>
                                    Tạo Users mới
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ $subAdmin->subAdminSettings->can_manage_orders ? 'check text-success' : 'times text-danger' }}"></i>
                                    Quản lý Đơn hàng
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-{{ $subAdmin->subAdminSettings->can_view_reports ? 'check text-success' : 'times text-danger' }}"></i>
                                    Xem Báo cáo
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-{{ $subAdmin->subAdminSettings->can_manage_products ? 'check text-success' : 'times text-danger' }}"></i>
                                    Quản lý Sản phẩm
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <p><strong>Giới hạn Users:</strong> {{ $stats['total_users'] }}/{{ $subAdmin->subAdminSettings->max_users_allowed }}</p>
                        <p><strong>Tỷ lệ hoa hồng:</strong> {{ $subAdmin->subAdminSettings->commission_rate }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Thao tác nhanh</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('sub-admin.users') }}" class="btn btn-primary btn-block">
                                <i class="iconly-boldProfile"></i> Quản lý Users
                            </a>
                        </div>
                        @if($subAdmin->subAdminSettings->can_create_users)
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('sub-admin.users.create') }}" class="btn btn-success btn-block">
                                <i class="iconly-boldAdd-User"></i> Tạo User mới
                            </a>
                        </div>
                        @endif
                        @if($subAdmin->subAdminSettings->can_manage_orders)
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('sub-admin.orders') }}" class="btn btn-info btn-block">
                                <i class="iconly-boldBag-2"></i> Quản lý Đơn hàng
                            </a>
                        </div>
                        @endif
                        @if($subAdmin->subAdminSettings->can_view_reports)
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('sub-admin.reports') }}" class="btn btn-warning btn-block">
                                <i class="iconly-boldChart"></i> Xem Báo cáo
                            </a>
                        </div>
                        @endif
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
</style>
@endpush