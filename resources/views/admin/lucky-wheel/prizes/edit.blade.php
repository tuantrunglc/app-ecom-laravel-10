@extends('backend.layouts.master')

@section('title', 'Chỉnh Sửa Phần Thưởng')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Chỉnh Sửa Phần Thưởng</h3>
                <p class="text-subtitle text-muted">Cập nhật thông tin phần thưởng</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.prizes') }}">Phần Thưởng</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Chỉnh Sửa</li>
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
                    <h4 class="card-title">Thông Tin Phần Thưởng</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('admin.lucky-wheel.prizes.update', $prize->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Tên Phần Thưởng <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $prize->name) }}" 
                                           placeholder="Nhập tên phần thưởng"
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Mô Tả</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="Nhập mô tả phần thưởng">{{ old('description', $prize->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="probability" class="form-label">Tỷ Lệ Trúng (%) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('probability') is-invalid @enderror" 
                                           id="probability" 
                                           name="probability" 
                                           value="{{ old('probability', $prize->probability) }}" 
                                           min="0" 
                                           max="100" 
                                           step="0.01"
                                           placeholder="0.00"
                                           required>
                                    @error('probability')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Nhập tỷ lệ từ 0 đến 100</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="quantity" class="form-label">Tổng Số Lượng <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="{{ old('quantity', $prize->quantity) }}" 
                                           min="0"
                                           placeholder="0"
                                           required>
                                    @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tổng số lượng phần thưởng</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="remaining_quantity" class="form-label">Số Lượng Còn Lại <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('remaining_quantity') is-invalid @enderror" 
                                           id="remaining_quantity" 
                                           name="remaining_quantity" 
                                           value="{{ old('remaining_quantity', $prize->remaining_quantity) }}" 
                                           min="0"
                                           placeholder="0"
                                           required>
                                    @error('remaining_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Số lượng hiện có thể trúng</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Đã Sử Dụng</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $prize->quantity - $prize->remaining_quantity }}" 
                                           readonly>
                                    <small class="text-muted">Số lượng đã được quay trúng</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="image" class="form-label">Hình Ảnh</label>
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*">
                                    @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Chấp nhận: JPG, PNG, GIF. Tối đa 2MB. Để trống nếu không muốn thay đổi.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $prize->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Kích hoạt phần thưởng
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.lucky-wheel.prizes') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Quay Lại
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check"></i> Cập Nhật
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Xem Trước</h4>
                </div>
                <div class="card-body text-center">
                    <div id="imagePreview" class="mb-3" {{ $prize->image ? '' : 'style=display:none;' }}>
                        <img id="previewImg" 
                             src="{{ $prize->image ? asset('storage/' . $prize->image) : '' }}" 
                             alt="Preview" 
                             class="img-fluid rounded" 
                             style="max-height: 150px;">
                    </div>
                    <div id="defaultIcon" class="mb-3" {{ $prize->image ? 'style=display:none;' : '' }}>
                        <i class="bi bi-gift" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                    <h5 id="previewName">{{ $prize->name }}</h5>
                    <p id="previewDescription" class="text-muted small">{{ $prize->description ?: 'Mô tả phần thưởng' }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-info" id="previewProbability">{{ $prize->probability }}%</span>
                        <span class="badge bg-secondary" id="previewQuantity">{{ $prize->quantity }}</span>
                        <span class="badge bg-success" id="previewRemaining">{{ $prize->remaining_quantity }} còn</span>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">📊 Thống Kê</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $prize->spins()->count() }}</h4>
                            <small class="text-muted">Lượt Quay</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $prize->spins()->winners()->count() }}</h4>
                            <small class="text-muted">Lượt Trúng</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            Tỷ lệ trúng thực tế: 
                            <strong>
                                @if($prize->spins()->count() > 0)
                                    {{ number_format(($prize->spins()->winners()->count() / $prize->spins()->count()) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">💡 Lưu Ý</h4>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            Số lượng còn lại không được lớn hơn tổng số lượng
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-info-circle text-info"></i>
                            Thay đổi tỷ lệ sẽ ảnh hưởng đến các lần quay tiếp theo
                        </li>
                        <li>
                            <i class="bi bi-shield-check text-success"></i>
                            Không thể xóa phần thưởng đã có người trúng
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Image preview
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
                $('#defaultIcon').hide();
            }
            reader.readAsDataURL(file);
        }
    });

    // Live preview
    $('#name').on('input', function() {
        const value = $(this).val() || 'Tên phần thưởng';
        $('#previewName').text(value);
    });

    $('#description').on('input', function() {
        const value = $(this).val() || 'Mô tả phần thưởng';
        $('#previewDescription').text(value);
    });

    $('#probability').on('input', function() {
        const value = $(this).val() || '0';
        $('#previewProbability').text(value + '%');
    });

    $('#quantity').on('input', function() {
        const value = $(this).val() || '0';
        $('#previewQuantity').text(value);
        
        // Auto update remaining quantity if it's greater than total
        const remaining = parseInt($('#remaining_quantity').val()) || 0;
        if (remaining > parseInt(value)) {
            $('#remaining_quantity').val(value);
            $('#previewRemaining').text(value + ' còn');
        }
    });

    $('#remaining_quantity').on('input', function() {
        const value = $(this).val() || '0';
        $('#previewRemaining').text(value + ' còn');
    });

    // Validate remaining quantity
    $('#remaining_quantity').on('blur', function() {
        const total = parseInt($('#quantity').val()) || 0;
        const remaining = parseInt($(this).val()) || 0;
        
        if (remaining > total) {
            alert('Số lượng còn lại không được lớn hơn tổng số lượng!');
            $(this).val(total);
            $('#previewRemaining').text(total + ' còn');
        }
    });
});
</script>
@endpush