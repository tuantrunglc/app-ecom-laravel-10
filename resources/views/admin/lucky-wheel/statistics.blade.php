@extends('backend.layouts.master')

@section('title', 'Thống Kê Vòng Quay')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Thống Kê Chi Tiết</h3>
                <p class="text-subtitle text-muted">Báo cáo và phân tích dữ liệu vòng quay may mắn</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Thống Kê</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Filter Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">🔍 Bộ Lọc</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.lucky-wheel.statistics') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_from" class="form-label">Từ Ngày</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_from" 
                                           name="date_from" 
                                           value="{{ $dateFrom }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_to" class="form-label">Đến Ngày</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_to" 
                                           name="date_to" 
                                           value="{{ $dateTo }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Lọc
                                        </button>
                                        <a href="{{ route('admin.lucky-wheel.statistics') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-clockwise"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon purple">
                                <i class="iconly-boldShow"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Tổng Lượt Quay</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($totalStats['total_spins']) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon blue">
                                <i class="iconly-boldProfile"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Lượt Trúng Thưởng</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($totalStats['total_winners']) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body px-3 py-4-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stats-icon green">
                                <i class="iconly-boldAdd-User"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted font-semibold">Admin Đặt Kết Quả</h6>
                            <h6 class="font-extrabold mb-0">{{ number_format($totalStats['total_admin_sets']) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Daily Statistics Chart -->
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4>Biểu Đồ Theo Ngày</h4>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Win Rate Pie Chart -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4>Tỷ Lệ Trúng Thưởng</h4>
                </div>
                <div class="card-body">
                    <canvas id="winRateChart" height="200"></canvas>
                    <div class="text-center mt-3">
                        <h4 class="text-primary">
                            {{ $totalStats['total_spins'] > 0 ? number_format(($totalStats['total_winners'] / $totalStats['total_spins']) * 100, 1) : 0 }}%
                        </h4>
                        <small class="text-muted">Tỷ lệ trúng thưởng tổng</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prize Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Thống Kê Theo Phần Thưởng</h4>
                </div>
                <div class="card-body">
                    @if($prizeStats->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Phần Thưởng</th>
                                    <th>Tỷ Lệ Thiết Lập</th>
                                    <th>Lượt Quay</th>
                                    <th>Tỷ Lệ Thực Tế</th>
                                    <th>Hiệu Suất</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prizeStats as $prize)
                                @php
                                    $actualRate = $totalStats['total_spins'] > 0 ? ($prize->spins_count / $totalStats['total_spins']) * 100 : 0;
                                    $efficiency = $prize->probability > 0 ? ($actualRate / $prize->probability) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($prize->image)
                                            <img src="{{ asset('storage/' . $prize->image) }}" 
                                                 alt="{{ $prize->name }}" 
                                                 class="rounded me-2" 
                                                 style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                            <i class="bi bi-gift text-primary me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $prize->name }}</strong>
                                                <br><small class="text-muted">{{ Str::limit($prize->description, 30) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $prize->probability }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ number_format($prize->spins_count) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ number_format($actualRate, 1) }}%</span>
                                    </td>
                                    <td>
                                        @if($efficiency > 120)
                                        <span class="badge bg-danger">{{ number_format($efficiency, 0) }}%</span>
                                        @elseif($efficiency > 80)
                                        <span class="badge bg-success">{{ number_format($efficiency, 0) }}%</span>
                                        @else
                                        <span class="badge bg-warning">{{ number_format($efficiency, 0) }}%</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($prize->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-danger">Tạm dừng</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-graph-up" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="text-muted mt-3">Chưa có dữ liệu thống kê</h5>
                        <p class="text-muted">Dữ liệu sẽ xuất hiện sau khi có người tham gia vòng quay</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Export & Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Thao Tác</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <button onclick="exportData('csv')" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Xuất CSV
                        </button>
                        <button onclick="exportData('pdf')" class="btn btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
                        </button>
                        <button onclick="printReport()" class="btn btn-info">
                            <i class="bi bi-printer"></i> In Báo Cáo
                        </button>
                        <a href="{{ route('admin.lucky-wheel.spins') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> Xem Chi Tiết Lượt Quay
                        </a>
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
const dailyCtx = document.getElementById('dailyChart').getContext('2d');

new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('vi-VN');
        }),
        datasets: [{
            label: 'Lượt Quay',
            data: dailyData.map(item => item.total_spins),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }, {
            label: 'Lượt Trúng',
            data: dailyData.map(item => item.total_winners),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            fill: true
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

// Win Rate Pie Chart
const totalSpins = {{ $totalStats['total_spins'] }};
const totalWinners = {{ $totalStats['total_winners'] }};
const winRateCtx = document.getElementById('winRateChart').getContext('2d');

new Chart(winRateCtx, {
    type: 'doughnut',
    data: {
        labels: ['Trúng Thưởng', 'Không Trúng'],
        datasets: [{
            data: [totalWinners, totalSpins - totalWinners],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Export functions
function exportData(format) {
    const params = new URLSearchParams({
        date_from: '{{ $dateFrom }}',
        date_to: '{{ $dateTo }}',
        format: format
    });
    
    // This would need to be implemented in the controller
    alert(`Xuất dữ liệu ${format.toUpperCase()} - Chức năng sẽ được triển khai sau`);
}

function printReport() {
    window.print();
}

// Auto refresh every 5 minutes
setInterval(function() {
    if (confirm('Dữ liệu có thể đã thay đổi. Bạn có muốn tải lại trang?')) {
        location.reload();
    }
}, 300000); // 5 minutes
</script>

<style>
@media print {
    .page-heading, .breadcrumb, .btn, .card-header {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
}
</style>
@endpush