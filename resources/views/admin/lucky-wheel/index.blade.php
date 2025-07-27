@extends('backend.layouts.master')

@section('title', 'Quản Lý Vòng Quay May Mắn')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Vòng Quay May Mắn</h3>
                <p class="text-subtitle text-muted">Dashboard quản lý vòng quay may mắn</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon purple">
                                <i class="iconly-boldShow"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Tổng Phần Thưởng</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['total_prizes'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon blue">
                                <i class="iconly-boldProfile"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Phần Thưởng Hoạt Động</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['active_prizes'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon green">
                                <i class="iconly-boldAdd-User"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Lượt Quay Hôm Nay</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['total_spins_today'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon red">
                                <i class="iconly-boldBookmark"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Trúng Thưởng Hôm Nay</h6>
                            <h6 class="font-extrabold mb-0">{{ $stats['total_winners_today'] }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Statistics Chart -->
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4>Thống Kê 7 Ngày Gần Nhất</h4>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Prizes -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4>Top Phần Thưởng</h4>
                </div>
                <div class="card-body">
                    @if($topPrizes->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topPrizes as $prize)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div class="d-flex align-items-center">
                                @if($prize->image)
                                <img src="{{ asset('storage/' . $prize->image) }}" alt="{{ $prize->name }}" 
                                     class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                <div class="bg-primary rounded d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-gift text-white"></i>
                                </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $prize->name }}</h6>
                                    <small class="text-muted">{{ $prize->probability }}% tỷ lệ</small>
                                </div>
                            </div>
                            <span class="badge bg-primary">{{ $prize->spins_count }} lượt</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center">Chưa có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Thao Tác Nhanh</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.lucky-wheel.prizes') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-gift"></i> Quản Lý Phần Thưởng
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.lucky-wheel.settings') }}" class="btn btn-info btn-block">
                                <i class="fas fa-cog"></i> Cài Đặt
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.lucky-wheel.set-result') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-magic"></i> Đặt Kết Quả
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.lucky-wheel.statistics') }}" class="btn btn-success btn-block">
                                <i class="fas fa-chart-bar"></i> Thống Kê Chi Tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Thống Kê Tổng Quan</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-primary">{{ $stats['total_spins'] }}</h3>
                                <p class="text-muted">Tổng Lượt Quay</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-success">{{ $stats['total_winners'] }}</h3>
                                <p class="text-muted">Tổng Lượt Trúng</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-info">
                                {{ $stats['total_spins'] > 0 ? number_format(($stats['total_winners'] / $stats['total_spins']) * 100, 1) : 0 }}%
                            </h3>
                            <p class="text-muted">Tỷ Lệ Trúng Thưởng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Statistics Chart
const dailyData = @json($dailyStats);
const ctx = document.getElementById('dailyChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: dailyData.map(item => item.date),
        datasets: [{
            label: 'Lượt Quay',
            data: dailyData.map(item => item.spins),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Lượt Trúng',
            data: dailyData.map(item => item.winners),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Thống kê lượt quay và trúng thưởng theo ngày'
            }
        }
    }
});
</script>
@endpush