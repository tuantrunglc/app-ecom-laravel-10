@extends('backend.layouts.master')

@section('title', 'Quản Lý Phần Thưởng')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Quản Lý Phần Thưởng</h3>
                <p class="text-subtitle text-muted">Danh sách các phần thưởng trong vòng quay</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Phần Thưởng</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Danh Sách Phần Thưởng</h4>
                        <a href="{{ route('admin.lucky-wheel.prizes.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Thêm Phần Thưởng
                        </a>
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

                    @if($prizes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th>Hình Ảnh</th>
                                    <th>Tên Phần Thưởng</th>
                                    <th>Mô Tả</th>
                                    <th>Tỷ Lệ (%)</th>
                                    <th>Số Lượng</th>
                                    <th>Còn Lại</th>
                                    <th>Trạng Thái</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prizes as $prize)
                                <tr>
                                    <td>
                                        @if($prize->image)
                                        <img src="{{ asset('storage/' . $prize->image) }}" 
                                             alt="{{ $prize->name }}" 
                                             class="rounded" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-gift text-white"></i>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $prize->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ Str::limit($prize->description, 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $prize->probability }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ number_format($prize->quantity) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $prize->remaining_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($prize->remaining_quantity) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($prize->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                        @else
                                        <span class="badge bg-danger">Tạm dừng</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.lucky-wheel.prizes.edit', $prize->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deletePrize({{ $prize->id }}, '{{ $prize->name }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $prizes->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-gift" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="text-muted mt-3">Chưa có phần thưởng nào</h5>
                        <p class="text-muted">Hãy thêm phần thưởng đầu tiên cho vòng quay của bạn!</p>
                        <a href="{{ route('admin.lucky-wheel.prizes.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Thêm Phần Thưởng
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
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
                <p>Bạn có chắc chắn muốn xóa phần thưởng <strong id="prizeName"></strong>?</p>
                <p class="text-danger"><small>Lưu ý: Không thể xóa phần thưởng đã có người quay trúng!</small></p>
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
function deletePrize(id, name) {
    document.getElementById('prizeName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/lucky-wheel/prizes/${id}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Initialize DataTable if needed
@if($prizes->count() > 0)
$(document).ready(function() {
    $('#table1').DataTable({
        "pageLength": 10,
        "ordering": true,
        "searching": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
        }
    });
});
@endif
</script>
@endpush