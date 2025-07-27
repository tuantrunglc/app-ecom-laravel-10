@extends('backend.layouts.master')

@section('title', 'Lịch Sử Quay')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Lịch Sử Quay</h3>
                <p class="text-subtitle text-muted">Danh sách tất cả lượt quay của users</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Lịch Sử</li>
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
                    <form method="GET" action="{{ route('admin.lucky-wheel.spins') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from" class="form-label">Từ Ngày</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_from" 
                                           name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_to" class="form-label">Đến Ngày</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="date_to" 
                                           name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user_id" class="form-label">User</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">-- Tất cả --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="is_winner" class="form-label">Kết Quả</label>
                                    <select class="form-select" id="is_winner" name="is_winner">
                                        <option value="">-- Tất cả --</option>
                                        <option value="1" {{ request('is_winner') === '1' ? 'selected' : '' }}>Trúng thưởng</option>
                                        <option value="0" {{ request('is_winner') === '0' ? 'selected' : '' }}>Không trúng</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="admin_set" class="form-label">Loại</label>
                                    <select class="form-select" id="admin_set" name="admin_set">
                                        <option value="">-- Tất cả --</option>
                                        <option value="1" {{ request('admin_set') === '1' ? 'selected' : '' }}>Admin đặt</option>
                                        <option value="0" {{ request('admin_set') === '0' ? 'selected' : '' }}>Ngẫu nhiên</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Lọc
                                        </button>
                                        <a href="{{ route('admin.lucky-wheel.spins') }}" class="btn btn-secondary">
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

    <!-- Spins List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Danh Sách Lượt Quay</h4>
                        <span class="badge bg-info">{{ $spins->total() }} kết quả</span>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($spins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Phần Thưởng</th>
                                    <th>Kết Quả</th>
                                    <th>Loại</th>
                                    <th>Thời Gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($spins as $index => $spin)
                                <tr>
                                    <td>{{ $spins->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-content bg-primary text-white">
                                                    {{ substr($spin->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $spin->user->name }}</strong>
                                                <br><small class="text-muted">{{ $spin->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($spin->prize)
                                        <div class="d-flex align-items-center">
                                            @if($spin->prize->image)
                                            <img src="{{ asset('storage/' . $spin->prize->image) }}" 
                                                 alt="{{ $spin->prize->name }}" 
                                                 class="rounded me-2" 
                                                 style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                            <i class="bi bi-gift text-primary me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $spin->prize->name }}</strong>
                                                @if($spin->prize->description)
                                                <br><small class="text-muted">{{ Str::limit($spin->prize->description, 30) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-muted">Không có phần thưởng</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($spin->is_winner)
                                        <span class="badge bg-success">
                                            <i class="bi bi-trophy"></i> Trúng thưởng
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Không trúng
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($spin->admin_set)
                                        <span class="badge bg-warning">
                                            <i class="bi bi-star"></i> Admin đặt
                                        </span>
                                        @else
                                        <span class="badge bg-info">
                                            <i class="bi bi-shuffle"></i> Ngẫu nhiên
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $spin->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        <br><small class="text-muted">{{ $spin->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $spins->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-list-ul" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="text-muted mt-3">Không có dữ liệu</h5>
                        <p class="text-muted">Chưa có lượt quay nào phù hợp với bộ lọc</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for better UX
    if (typeof $.fn.select2 !== 'undefined') {
        $('#user_id').select2({
            placeholder: '-- Tất cả --',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Auto-submit form when filters change
    $('.form-select, .form-control').on('change', function() {
        // Optional: Auto-submit after a delay
        // setTimeout(() => $(this).closest('form').submit(), 500);
    });
});
</script>
@endpush