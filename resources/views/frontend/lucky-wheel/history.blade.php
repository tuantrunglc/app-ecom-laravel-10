@extends('frontend.layouts.master')

@section('title', 'Lịch Sử Vòng Quay')

@section('main-content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-history"></i> Lịch Sử Vòng Quay</h4>
                        <a href="{{ route('lucky-wheel.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($spins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Ngày Quay</th>
                                    <th>Phần Thưởng</th>
                                    <th>Kết Quả</th>
                                    <th>Loại</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($spins as $index => $spin)
                                <tr>
                                    <td>{{ $spins->firstItem() + $index }}</td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $spin->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($spin->prize)
                                        <div class="d-flex align-items-center">
                                            @if($spin->prize->image)
                                            <img src="{{ asset('storage/' . $spin->prize->image) }}" 
                                                 alt="{{ $spin->prize->name }}" 
                                                 class="me-2 rounded" 
                                                 style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                            <i class="fas fa-gift text-primary me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $spin->prize->name }}</strong>
                                                @if($spin->prize->description)
                                                <br><small class="text-muted">{{ $spin->prize->description }}</small>
                                                @endif
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-muted">Không có phần thưởng</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($spin->is_winner)
                                        <span class="badge badge-success">
                                            <i class="fas fa-trophy"></i> Trúng thưởng
                                        </span>
                                        @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-times"></i> Chúc may mắn lần sau
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($spin->admin_set)
                                        <span class="badge badge-info">
                                            <i class="fas fa-star"></i> Đặc biệt
                                        </span>
                                        @else
                                        <span class="badge badge-light">
                                            <i class="fas fa-random"></i> Ngẫu nhiên
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $spins->links() }}
                    </div>
                    
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Bạn chưa có lịch sử quay nào</h5>
                        <p class="text-muted">Hãy tham gia vòng quay để có cơ hội nhận những phần thưởng hấp dẫn!</p>
                        <a href="{{ route('lucky-wheel.index') }}" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Quay ngay
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}

.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}
</style>
@endpush