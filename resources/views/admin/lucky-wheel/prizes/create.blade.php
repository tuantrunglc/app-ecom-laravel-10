@extends('backend.layouts.master')

@section('title', 'Thêm Phần Thưởng')

@section('main-content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Thêm Phần Thưởng</h3>
                <p class="text-subtitle text-muted">Tạo phần thưởng mới cho vòng quay</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.index') }}">Vòng Quay</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lucky-wheel.prizes') }}">Phần Thưởng</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Thêm Mới</li>
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
                    <form action="{{ route('admin.lucky-wheel.prizes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Tên Phần Thưởng <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
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
                                              placeholder="Nhập mô tả phần thưởng">{{ old('description') }}</textarea>
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
                                           value="{{ old('probability') }}" 
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
                                    <label for="quantity" class="form-label">Số Lượng <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="{{ old('quantity') }}" 
                                           min="0"
                                           placeholder="0"
                                           required>
                                    @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Số lượng phần thưởng có sẵn</small>
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
                                    <small class="text-muted">Chấp nhận: JPG, PNG, GIF. Tối đa 2MB</small>
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
                                           {{ old('is_active', true) ? 'checked' : '' }}>
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
                                        <i class="bi bi-check"></i> Lưu Phần Thưởng
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
                    <div id="imagePreview" class="mb-3" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 150px;">
                    </div>
                    <div id="defaultIcon" class="mb-3">
                        <i class="bi bi-gift" style="font-size: 4rem; color: #ccc;"></i>
                    </div>
                    <h5 id="previewName" class="text-muted">Tên phần thưởng</h5>
                    <p id="previewDescription" class="text-muted small">Mô tả phần thưởng</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-info" id="previewProbability">0%</span>
                        <span class="badge bg-secondary" id="previewQuantity">0</span>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">💡 Gợi Ý</h4>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Tỷ lệ trúng nên phù hợp với giá trị phần thưởng
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Hình ảnh nên có kích thước vuông (1:1)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Mô tả ngắn gọn, dễ hiểu
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success"></i>
                            Kiểm tra tổng tỷ lệ không vượt quá 100%
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
        } else {
            $('#imagePreview').hide();
            $('#defaultIcon').show();
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
    });
});
</script>
@endpush