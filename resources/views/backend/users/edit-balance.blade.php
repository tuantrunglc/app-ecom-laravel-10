@extends('backend.layouts.master')

@section('title', 'Chỉnh sửa số dư ví - ' . $user->name)

@section('main-content')
<div class="card">
    <h5 class="card-header">
        <i class="fas fa-wallet"></i> Chỉnh sửa số dư ví - {{ $user->name }}
    </h5>
    
    <div class="card-body">
        <!-- Thông tin user -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-user"></i> Thông tin người dùng</h6>
                        <p><strong>Tên:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Vai trò:</strong> 
                            @if($user->role == 'admin')
                                <span class="badge badge-danger">Admin</span>
                            @elseif($user->role == 'sub_admin')
                                <span class="badge badge-warning">Sub Admin</span>
                            @else
                                <span class="badge badge-primary">User</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title"><i class="fas fa-coins"></i> Số dư hiện tại</h6>
                        <h3 class="mb-0">${{ number_format($user->wallet_balance, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form chỉnh sửa -->
        <form action="{{ route('admin.wallet.update-balance', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="action"><i class="fas fa-exchange-alt"></i> Hành động <span class="text-danger">*</span></label>
                        <select class="form-control @error('action') is-invalid @enderror" id="action" name="action" required>
                            <option value="">-- Chọn hành động --</option>
                            <option value="add" {{ old('action') == 'add' ? 'selected' : '' }}>
                                <i class="fas fa-plus"></i> Thêm tiền vào ví
                            </option>
                            <option value="subtract" {{ old('action') == 'subtract' ? 'selected' : '' }}>
                                <i class="fas fa-minus"></i> Trừ tiền khỏi ví
                            </option>
                        </select>
                        @error('action')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="amount"><i class="fas fa-dollar-sign"></i> Số tiền <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('amount') is-invalid @enderror" 
                               id="amount" 
                               name="amount" 
                               value="{{ old('amount') }}" 
                               step="0.01" 
                               min="0.01" 
                               max="999999"
                               placeholder="Nhập số tiền..."
                               required>
                        @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Tối thiểu: $0.01 - Tối đa: $999,999</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="reason"><i class="fas fa-comment-alt"></i> Lý do <span class="text-danger">*</span></label>
                <textarea class="form-control @error('reason') is-invalid @enderror" 
                          id="reason" 
                          name="reason" 
                          rows="3" 
                          placeholder="Nhập lý do chỉnh sửa số dư ví..."
                          maxlength="500"
                          required>{{ old('reason') }}</textarea>
                @error('reason')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <small class="form-text text-muted">Tối đa 500 ký tự. Còn lại: <span id="remaining">500</span> ký tự</small>
            </div>

            <!-- Preview section -->
            <div id="preview-section" class="alert alert-warning" style="display: none;">
                <h6><i class="fas fa-eye"></i> Xem trước giao dịch:</h6>
                <div id="preview-content"></div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success" id="submit-btn" disabled>
                    <i class="fas fa-save"></i> Cập nhật số dư ví
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<style>
    #preview-section {
        border-left: 4px solid #ffc107;
    }
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .bg-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
$(document).ready(function() {
    // Character counter for reason field
    $('#reason').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;
        $('#remaining').text(remaining);
        
        if (remaining < 50) {
            $('#remaining').addClass('text-danger').removeClass('text-muted');
        } else {
            $('#remaining').addClass('text-muted').removeClass('text-danger');
        }
    });

    // Preview functionality
    function updatePreview() {
        const action = $('#action').val();
        const amount = $('#amount').val();
        const reason = $('#reason').val();
        const currentBalance = {{ $user->wallet_balance }};

        if (action && amount && reason) {
            let newBalance;
            let actionText;
            let colorClass;
            let icon;

            if (action === 'add') {
                newBalance = currentBalance + parseFloat(amount);
                actionText = 'Thêm tiền vào ví';
                colorClass = 'text-success';
                icon = 'fas fa-plus-circle';
            } else {
                newBalance = currentBalance - parseFloat(amount);
                actionText = 'Trừ tiền khỏi ví';
                colorClass = 'text-danger';
                icon = 'fas fa-minus-circle';
            }

            const previewHtml = `
                <div class="row">
                    <div class="col-md-4">
                        <strong><i class="${icon}"></i> Hành động:</strong><br>
                        <span class="${colorClass}">${actionText}</span>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-dollar-sign"></i> Số tiền:</strong><br>
                        <span class="${colorClass}">$${parseFloat(amount).toFixed(2)}</span>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-wallet"></i> Số dư sau:</strong><br>
                        <span class="font-weight-bold ${newBalance >= 0 ? 'text-success' : 'text-danger'}">
                            $${newBalance.toFixed(2)}
                        </span>
                    </div>
                </div>
                <div class="mt-2">
                    <strong><i class="fas fa-comment"></i> Lý do:</strong><br>
                    <em>${reason}</em>
                </div>
            `;

            $('#preview-content').html(previewHtml);
            $('#preview-section').show();

            // Enable submit button if new balance is not negative (for subtract action)
            if (action === 'subtract' && newBalance < 0) {
                $('#submit-btn').prop('disabled', true);
                $('#preview-section').removeClass('alert-warning').addClass('alert-danger');
                $('#preview-content').prepend('<div class="alert alert-danger mb-2"><i class="fas fa-exclamation-triangle"></i> <strong>Lỗi:</strong> Số dư không đủ để thực hiện giao dịch!</div>');
            } else {
                $('#submit-btn').prop('disabled', false);
                $('#preview-section').removeClass('alert-danger').addClass('alert-warning');
            }
        } else {
            $('#preview-section').hide();
            $('#submit-btn').prop('disabled', true);
        }
    }

    // Update preview when form fields change
    $('#action, #amount, #reason').on('input change', function() {
        updatePreview();
    });

    // Form validation
    $('form').on('submit', function(e) {
        const action = $('#action').val();
        const amount = parseFloat($('#amount').val());
        const currentBalance = {{ $user->wallet_balance }};

        if (action === 'subtract' && amount > currentBalance) {
            e.preventDefault();
            swal("Lỗi!", "Số dư không đủ để thực hiện giao dịch!", "error");
            return false;
        }

        // Confirmation dialog
        e.preventDefault();
        const actionText = action === 'add' ? 'thêm' : 'trừ';
        const form = this;
        
        swal({
            title: 'Xác nhận thay đổi?',
            text: `Bạn có chắc chắn muốn ${actionText} $${amount.toFixed(2)} vào ví của {{ $user->name }}?`,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy',
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $(form).off('submit').submit();
            }
        });
    });
});
</script>
@endpush
