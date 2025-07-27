@extends('backend.layouts.master')

@section('title', 'Cài Đặt Vòng Quay')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Cài Đặt Vòng Quay</h3>
                <p class="text-subtitle text-muted">Cấu hình các thông số cho vòng quay may mắn</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cài Đặt</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Cấu Hình Hệ Thống</h4>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('admin.lucky-wheel.settings.update') }}" method="POST">
                        @csrf
                        
                        <!-- General Settings -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="bi bi-gear"></i> Cài Đặt Chung
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="max_spins_per_day" class="form-label">
                                        Số Lần Quay Tối Đa/Ngày <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('max_spins_per_day') is-invalid @enderror" 
                                           id="max_spins_per_day" 
                                           name="max_spins_per_day" 
                                           value="{{ old('max_spins_per_day', $settings['max_spins_per_day']->value ?? 3) }}" 
                                           min="1" 
                                           max="10"
                                           required>
                                    @error('max_spins_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Giới hạn số lần quay mỗi user trong 1 ngày</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="animation_duration" class="form-label">
                                        Thời Gian Hiệu Ứng (ms) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('animation_duration') is-invalid @enderror" 
                                           id="animation_duration" 
                                           name="animation_duration" 
                                           value="{{ old('animation_duration', $settings['animation_duration']->value ?? 3000) }}" 
                                           min="1000" 
                                           max="10000"
                                           step="100"
                                           required>
                                    @error('animation_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Thời gian xoay vòng quay (1000-10000ms)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="min_prize_probability" class="form-label">
                                        Tỷ Lệ Trúng Tối Thiểu (%) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('min_prize_probability') is-invalid @enderror" 
                                           id="min_prize_probability" 
                                           name="min_prize_probability" 
                                           value="{{ old('min_prize_probability', $settings['min_prize_probability']->value ?? 10) }}" 
                                           min="0" 
                                           max="100"
                                           step="0.1"
                                           required>
                                    @error('min_prize_probability')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tỷ lệ trúng thưởng tối thiểu của hệ thống</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Feature Settings -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="bi bi-toggles"></i> Tính Năng
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="wheel_enabled" 
                                           name="wheel_enabled" 
                                           value="1" 
                                           {{ old('wheel_enabled', ($settings['wheel_enabled']->value ?? 'true') === 'true') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="wheel_enabled">
                                        <strong>Bật Vòng Quay</strong>
                                    </label>
                                    <div class="form-text">Bật/tắt toàn bộ chức năng vòng quay may mắn</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="require_login" 
                                           name="require_login" 
                                           value="1" 
                                           {{ old('require_login', ($settings['require_login']->value ?? 'true') === 'true') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_login">
                                        <strong>Yêu Cầu Đăng Nhập</strong>
                                    </label>
                                    <div class="form-text">User phải đăng nhập mới được tham gia</div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.lucky-wheel.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Quay Lại
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check"></i> Lưu Cài Đặt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview & Info -->
        <div class="col-12 col-lg-4">
            <!-- Current Status -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">🔍 Trạng Thái Hiện Tại</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Vòng Quay:</span>
                        <span class="badge {{ ($settings['wheel_enabled']->value ?? 'true') === 'true' ? 'bg-success' : 'bg-danger' }}">
                            {{ ($settings['wheel_enabled']->value ?? 'true') === 'true' ? 'Đang Hoạt Động' : 'Tạm Dừng' }}
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Yêu Cầu Đăng Nhập:</span>
                        <span class="badge {{ ($settings['require_login']->value ?? 'true') === 'true' ? 'bg-info' : 'bg-secondary' }}">
                            {{ ($settings['require_login']->value ?? 'true') === 'true' ? 'Có' : 'Không' }}
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Giới Hạn/Ngày:</span>
                        <span class="badge bg-primary">{{ $settings['max_spins_per_day']->value ?? 3 }} lần</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Thời Gian Xoay:</span>
                        <span class="badge bg-warning">{{ $settings['animation_duration']->value ?? 3000 }}ms</span>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">💡 Gợi Ý Cài Đặt</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-lightbulb"></i> Số Lần Quay/Ngày</h6>
                        <small>
                            • 1-2 lần: Tạo sự khan hiếm<br>
                            • 3-5 lần: Cân bằng tốt<br>
                            • 6+ lần: Tăng tương tác
                        </small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-clock"></i> Thời Gian Hiệu Ứng</h6>
                        <small>
                            • 2-3 giây: Tạo hồi hộp<br>
                            • 4-5 giây: Tăng kịch tính<br>
                            • 6+ giây: Có thể gây chán
                        </small>
                    </div>
                    
                    <div class="alert alert-success">
                        <h6><i class="bi bi-shield-check"></i> Bảo Mật</h6>
                        <small>
                            Yêu cầu đăng nhập giúp:<br>
                            • Theo dõi lượt quay<br>
                            • Chống spam<br>
                            • Tăng tương tác user
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">⚡ Thao Tác Nhanh</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('lucky-wheel.index') }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-eye"></i> Xem Vòng Quay
                        </a>
                        <a href="{{ route('admin.lucky-wheel.prizes') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-gift"></i> Quản Lý Phần Thưởng
                        </a>
                        <a href="{{ route('admin.lucky-wheel.statistics') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-graph-up"></i> Xem Thống Kê
                        </a>
                        <form action="{{ route('admin.lucky-wheel.cleanup') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm w-100" 
                                    onclick="return confirm('Bạn có chắc muốn dọn dẹp dữ liệu cũ?')">
                                <i class="bi bi-trash"></i> Dọn Dẹp Dữ Liệu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Live preview for animation duration
    $('#animation_duration').on('input', function() {
        const value = $(this).val();
        const seconds = (value / 1000).toFixed(1);
        $(this).next('.text-muted').text(`Thời gian xoay vòng quay (${seconds}s)`);
    });

    // Validate settings
    $('form').on('submit', function(e) {
        const maxSpins = parseInt($('#max_spins_per_day').val());
        const animationDuration = parseInt($('#animation_duration').val());
        const minProbability = parseFloat($('#min_prize_probability').val());

        if (maxSpins < 1 || maxSpins > 10) {
            alert('Số lần quay/ngày phải từ 1 đến 10!');
            e.preventDefault();
            return false;
        }

        if (animationDuration < 1000 || animationDuration > 10000) {
            alert('Thời gian hiệu ứng phải từ 1000ms đến 10000ms!');
            e.preventDefault();
            return false;
        }

        if (minProbability < 0 || minProbability > 100) {
            alert('Tỷ lệ trúng tối thiểu phải từ 0% đến 100%!');
            e.preventDefault();
            return false;
        }
    });

    // Toggle switch effects
    $('#wheel_enabled').change(function() {
        if ($(this).is(':checked')) {
            $('.card').removeClass('opacity-50');
        } else {
            if (!confirm('Bạn có chắc muốn tắt vòng quay? User sẽ không thể tham gia!')) {
                $(this).prop('checked', true);
            }
        }
    });
});
</script>
@endpush