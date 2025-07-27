@extends('backend.layouts.master')

@section('title', 'Đặt Kết Quả Cho User')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Đặt Kết Quả Cho User</h3>
                <p class="text-subtitle text-muted">Thiết lập kết quả trúng thưởng cho user cụ thể</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Đặt Kết Quả</li>
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
                    <h4 class="card-title">🎯 Thiết Lập Kết Quả</h4>
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

                    <form action="{{ route('admin.lucky-wheel.set-result.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="user_id" class="form-label">
                                        Chọn User <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" 
                                            name="user_id" 
                                            required>
                                        <option value="">-- Chọn User --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">User sẽ nhận được phần thưởng này trong lần quay tiếp theo</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="prize_id" class="form-label">
                                        Chọn Phần Thưởng <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('prize_id') is-invalid @enderror" 
                                            id="prize_id" 
                                            name="prize_id" 
                                            required>
                                        <option value="">-- Chọn Phần Thưởng --</option>
                                        @foreach($prizes as $prize)
                                        <option value="{{ $prize->id }}" 
                                                data-image="{{ $prize->image ? asset('storage/' . $prize->image) : '' }}"
                                                data-description="{{ $prize->description }}"
                                                data-probability="{{ $prize->probability }}"
                                                data-remaining="{{ $prize->remaining_quantity }}"
                                                {{ old('prize_id') == $prize->id ? 'selected' : '' }}>
                                            {{ $prize->name }} ({{ $prize->probability }}% - Còn {{ $prize->remaining_quantity }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('prize_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Chỉ hiển thị các phần thưởng đang hoạt động</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="expires_at" class="form-label">
                                        Thời Hạn (Tùy chọn)
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control @error('expires_at') is-invalid @enderror" 
                                           id="expires_at" 
                                           name="expires_at" 
                                           value="{{ old('expires_at') }}"
                                           min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Để trống nếu không có thời hạn. Sau thời hạn, kết quả sẽ tự động hủy.</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Lưu Ý Quan Trọng:</h6>
                            <ul class="mb-0">
                                <li>User chỉ có thể có 1 kết quả được đặt tại một thời điểm</li>
                                <li>Kết quả sẽ được áp dụng trong lần quay tiếp theo của user</li>
                                <li>Sau khi user quay trúng, kết quả sẽ tự động bị xóa</li>
                                <li>Admin có thể hủy kết quả đã đặt bất cứ lúc nào</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.lucky-wheel.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Quay Lại
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-magic"></i> Đặt Kết Quả
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
            <!-- Preview Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">👁️ Xem Trước</h4>
                </div>
                <div class="card-body text-center">
                    <div id="userPreview" class="mb-3" style="display: none;">
                        <div class="bg-primary text-white rounded p-2 mb-2">
                            <i class="bi bi-person"></i>
                            <span id="selectedUserName">Chưa chọn user</span>
                        </div>
                    </div>
                    
                    <div id="prizePreview" style="display: none;">
                        <div id="prizeImagePreview" class="mb-3" style="display: none;">
                            <img id="prizeImage" src="" alt="Prize" class="img-fluid rounded" style="max-height: 100px;">
                        </div>
                        <div id="prizeIconPreview" class="mb-3">
                            <i class="bi bi-gift" style="font-size: 3rem; color: #ccc;"></i>
                        </div>
                        <h5 id="prizeName">Tên phần thưởng</h5>
                        <p id="prizeDescription" class="text-muted small">Mô tả phần thưởng</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge bg-info" id="prizeProbability">0%</span>
                            <span class="badge bg-success" id="prizeRemaining">0 còn</span>
                        </div>
                    </div>
                    
                    <div id="noSelection" class="text-muted">
                        <i class="bi bi-arrow-up" style="font-size: 2rem;"></i>
                        <p>Chọn user và phần thưởng để xem trước</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">📊 Thống Kê Nhanh</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $users->count() }}</h4>
                            <small class="text-muted">Tổng Users</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $prizes->count() }}</h4>
                            <small class="text-muted">Phần Thưởng</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sets -->
            <div class="card mt-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">🕒 Gần Đây</h4>
                        <a href="{{ route('admin.lucky-wheel.admin-sets') }}" class="btn btn-sm btn-outline-primary">
                            Xem Tất Cả
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $recentSets = \App\Models\LuckyWheelAdminSet::with(['user', 'prize'])
                            ->orderBy('created_at', 'desc')
                            ->limit(3)
                            ->get();
                    @endphp
                    
                    @if($recentSets->count() > 0)
                    @foreach($recentSets as $set)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <small class="text-muted">{{ $set->user->name }}</small><br>
                            <strong class="small">{{ $set->prize->name }}</strong>
                        </div>
                        <span class="badge {{ $set->is_used ? 'bg-success' : 'bg-warning' }}">
                            {{ $set->is_used ? 'Đã dùng' : 'Chờ' }}
                        </span>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted text-center">Chưa có kết quả nào được đặt</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // User selection preview
    $('#user_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const userName = selectedOption.text();
        
        if ($(this).val()) {
            $('#selectedUserName').text(userName);
            $('#userPreview').show();
            updatePreviewVisibility();
        } else {
            $('#userPreview').hide();
            updatePreviewVisibility();
        }
    });

    // Prize selection preview
    $('#prize_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        
        if ($(this).val()) {
            const prizeName = selectedOption.text().split(' (')[0]; // Remove probability part
            const prizeImage = selectedOption.data('image');
            const prizeDescription = selectedOption.data('description');
            const prizeProbability = selectedOption.data('probability');
            const prizeRemaining = selectedOption.data('remaining');
            
            $('#prizeName').text(prizeName);
            $('#prizeDescription').text(prizeDescription || 'Không có mô tả');
            $('#prizeProbability').text(prizeProbability + '%');
            $('#prizeRemaining').text(prizeRemaining + ' còn');
            
            if (prizeImage) {
                $('#prizeImage').attr('src', prizeImage);
                $('#prizeImagePreview').show();
                $('#prizeIconPreview').hide();
            } else {
                $('#prizeImagePreview').hide();
                $('#prizeIconPreview').show();
            }
            
            $('#prizePreview').show();
            updatePreviewVisibility();
        } else {
            $('#prizePreview').hide();
            updatePreviewVisibility();
        }
    });

    function updatePreviewVisibility() {
        if ($('#user_id').val() && $('#prize_id').val()) {
            $('#noSelection').hide();
        } else {
            $('#noSelection').show();
        }
    }

    // Form validation
    $('form').on('submit', function(e) {
        const userId = $('#user_id').val();
        const prizeId = $('#prize_id').val();
        
        if (!userId || !prizeId) {
            alert('Vui lòng chọn đầy đủ user và phần thưởng!');
            e.preventDefault();
            return false;
        }

        // Confirm before submit
        const userName = $('#user_id option:selected').text();
        const prizeName = $('#prize_id option:selected').text().split(' (')[0];
        
        if (!confirm(`Bạn có chắc muốn đặt kết quả "${prizeName}" cho user "${userName}"?`)) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize Select2 for better UX
    if (typeof $.fn.select2 !== 'undefined') {
        $('#user_id').select2({
            placeholder: '-- Chọn User --',
            allowClear: true,
            width: '100%'
        });
        
        $('#prize_id').select2({
            placeholder: '-- Chọn Phần Thưởng --',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endpush