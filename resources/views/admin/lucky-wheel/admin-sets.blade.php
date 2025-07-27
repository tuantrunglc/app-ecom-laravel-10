@extends('backend.layouts.master')

@section('title', 'Quản Lý Kết Quả Đã Đặt')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Kết Quả Đã Đặt</h3>
                <p class="text-subtitle text-muted">Quản lý các kết quả admin đã thiết lập cho users</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kết Quả Đã Đặt</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <!-- Filter & Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">🔍 Bộ Lọc & Thao Tác</h4>
                        <a href="{{ route('admin.lucky-wheel.set-result') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Đặt Kết Quả Mới
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.lucky-wheel.admin-sets') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status" class="form-label">Trạng Thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">-- Tất cả --</option>
                                        <option value="unused" {{ request('status') === 'unused' ? 'selected' : '' }}>Chưa sử dụng</option>
                                        <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Đã sử dụng</option>
                                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Lọc
                                        </button>
                                        <a href="{{ route('admin.lucky-wheel.admin-sets') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-clockwise"></i> Reset
                                        </a>
                                        <form action="{{ route('admin.lucky-wheel.cleanup') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" 
                                                    onclick="return confirm('Bạn có chắc muốn dọn dẹp các kết quả hết hạn?')">
                                                <i class="bi bi-trash"></i> Dọn Dẹp Hết Hạn
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Sets List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Danh Sách Kết Quả</h4>
                        <span class="badge bg-info">{{ $adminSets->total() }} kết quả</span>
                    </div>
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

                    @if($adminSets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Phần Thưởng</th>
                                    <th>Admin Đặt</th>
                                    <th>Thời Hạn</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Tạo</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adminSets as $index => $adminSet)
                                <tr>
                                    <td>{{ $adminSets->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-content bg-primary text-white">
                                                    {{ substr($adminSet->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $adminSet->user->name }}</strong>
                                                <br><small class="text-muted">{{ $adminSet->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($adminSet->prize->image)
                                            <img src="{{ asset('storage/' . $adminSet->prize->image) }}" 
                                                 alt="{{ $adminSet->prize->name }}" 
                                                 class="rounded me-2" 
                                                 style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                            <i class="bi bi-gift text-primary me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $adminSet->prize->name }}</strong>
                                                @if($adminSet->prize->description)
                                                <br><small class="text-muted">{{ Str::limit($adminSet->prize->description, 30) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <div class="avatar-content bg-success text-white">
                                                    {{ substr($adminSet->admin->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $adminSet->admin->name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($adminSet->expires_at)
                                        <span class="text-muted">
                                            {{ $adminSet->expires_at->format('d/m/Y H:i') }}
                                        </span>
                                        @if($adminSet->expires_at->isPast())
                                        <br><small class="text-danger">Đã hết hạn</small>
                                        @else
                                        <br><small class="text-success">{{ $adminSet->expires_at->diffForHumans() }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">Không giới hạn</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($adminSet->is_used)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Đã sử dụng
                                        </span>
                                        @elseif($adminSet->expires_at && $adminSet->expires_at->isPast())
                                        <span class="badge bg-danger">
                                            <i class="bi bi-clock"></i> Hết hạn
                                        </span>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-hourglass-split"></i> Chờ sử dụng
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $adminSet->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        <br><small class="text-muted">{{ $adminSet->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if(!$adminSet->is_used)
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="deleteAdminSet({{ $adminSet->id }}, '{{ $adminSet->user->name }}', '{{ $adminSet->prize->name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $adminSets->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-magic" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="text-muted mt-3">Không có kết quả nào</h5>
                        <p class="text-muted">Chưa có kết quả nào được đặt hoặc không phù hợp với bộ lọc</p>
                        <a href="{{ route('admin.lucky-wheel.set-result') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Đặt Kết Quả Đầu Tiên
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    @if($adminSets->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">📊 Thống Kê Nhanh</h4>
                </div>
                <div class="card-body">
                    @php
                        $totalSets = \App\Models\LuckyWheelAdminSet::count();
                        $usedSets = \App\Models\LuckyWheelAdminSet::where('is_used', true)->count();
                        $expiredSets = \App\Models\LuckyWheelAdminSet::where('expires_at', '<', now())->where('is_used', false)->count();
                        $activeSets = \App\Models\LuckyWheelAdminSet::unused()->notExpired()->count();
                    @endphp
                    
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary">{{ $totalSets }}</h4>
                            <small class="text-muted">Tổng Kết Quả</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">{{ $usedSets }}</h4>
                            <small class="text-muted">Đã Sử Dụng</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning">{{ $activeSets }}</h4>
                            <small class="text-muted">Đang Chờ</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-danger">{{ $expiredSets }}</h4>
                            <small class="text-muted">Hết Hạn</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác Nhận Xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa kết quả đã đặt?</p>
                <div class="alert alert-info">
                    <strong>User:</strong> <span id="deleteUserName"></span><br>
                    <strong>Phần thưởng:</strong> <span id="deletePrizeName"></span>
                </div>
                <p class="text-danger"><small>Lưu ý: Không thể khôi phục sau khi xóa!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteAdminSet(id, userName, prizeName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deletePrizeName').textContent = prizeName;
    document.getElementById('deleteForm').action = `/admin/lucky-wheel/admin-sets/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

$(document).ready(function() {
    // Auto-refresh every 30 seconds for active sets
    if (window.location.search.includes('status=unused') || !window.location.search.includes('status=')) {
        setInterval(function() {
            // Only refresh if user is not interacting
            if (document.hidden === false) {
                const lastActivity = Date.now() - (window.lastActivity || 0);
                if (lastActivity > 30000) { // 30 seconds of inactivity
                    location.reload();
                }
            }
        }, 30000);
        
        // Track user activity
        document.addEventListener('mousemove', function() {
            window.lastActivity = Date.now();
        });
        
        document.addEventListener('keypress', function() {
            window.lastActivity = Date.now();
        });
    }
});
</script>
@endpush