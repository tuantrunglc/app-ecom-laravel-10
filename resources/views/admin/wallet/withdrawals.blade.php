@extends('backend.layouts.master')

@section('title','Quản Lý Rút Tiền')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh Sách Yêu Cầu Rút Tiền</h6>
    </div>
    <div class="card-body">
        @include('backend.layouts.notification')
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Số Tiền</th>
                        <th>Thông Tin Ngân Hàng</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td>{{ $withdrawal->id }}</td>
                        <td>
                            <strong>{{ $withdrawal->user->name }}</strong><br>
                            <small class="text-muted">{{ $withdrawal->user->email }}</small><br>
                            <small class="text-info">Số dư: {{ $withdrawal->user->formatted_balance }}</small>
                        </td>
                        <td>
                            <strong class="text-warning">{{ $withdrawal->formatted_amount }}</strong>
                        </td>
                        <td>
                            <strong>{{ $withdrawal->bank_name }}</strong><br>
                            <span class="text-monospace">{{ $withdrawal->bank_account }}</span><br>
                            <small class="text-muted">{{ $withdrawal->account_name }}</small>
                        </td>
                        <td>
                            @if($withdrawal->status == 'pending')
                                <span class="badge badge-warning">{{ $withdrawal->status_text }}</span>
                            @elseif($withdrawal->status == 'completed')
                                <span class="badge badge-success">{{ $withdrawal->status_text }}</span>
                            @else
                                <span class="badge badge-danger">{{ $withdrawal->status_text }}</span>
                            @endif
                            
                            @if($withdrawal->admin_note)
                                <br><small class="text-info">{{ $withdrawal->admin_note }}</small>
                            @endif
                        </td>
                        <td>{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($withdrawal->status == 'pending')
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="approveWithdrawal({{ $withdrawal->id }}, '{{ $withdrawal->user->name }}', '{{ $withdrawal->formatted_amount }}', '{{ $withdrawal->bank_name }}', '{{ $withdrawal->bank_account }}', '{{ $withdrawal->account_name }}')">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="rejectRequest('withdrawal', {{ $withdrawal->id }}, '{{ $withdrawal->user->name }}', '{{ $withdrawal->formatted_amount }}')">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                            @else
                                <span class="text-muted">Đã xử lý</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Không có yêu cầu rút tiền nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>

<!-- Modal Duyệt Rút Tiền -->
<div class="modal fade" id="approveWithdrawalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duyệt Rút Tiền</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="approveWithdrawalForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Lưu ý:</strong> Sau khi duyệt, tiền sẽ được trừ khỏi ví user. Hãy chắc chắn đã chuyển khoản thành công!
                    </div>
                    
                    <div id="approveWithdrawalInfo"></div>
                    
                    <div class="form-group">
                        <label for="admin_note_approve_withdrawal">Ghi Chú Admin (Tùy chọn)</label>
                        <textarea class="form-control" id="admin_note_approve_withdrawal" name="admin_note" rows="3" 
                                  placeholder="VD: Đã chuyển khoản thành công lúc 14:30 ngày 28/07/2025"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác Nhận Đã Chuyển Khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ Chối -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ Chối Yêu Cầu</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn từ chối yêu cầu này?</p>
                    <div id="rejectInfo"></div>
                    
                    <div class="form-group">
                        <label for="admin_note_reject">Lý Do Từ Chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="admin_note_reject" name="admin_note" rows="3" 
                                  placeholder="VD: Thông tin tài khoản không chính xác, Số dư không đủ..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác Nhận Từ Chối</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveWithdrawal(id, userName, amount, bankName, bankAccount, accountName) {
    $('#approveWithdrawalForm').attr('action', '{{ route("admin.wallet.withdrawals.approve", ":id") }}'.replace(':id', id));
    $('#approveWithdrawalInfo').html(`
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Thông Tin User</h6>
                        <p><strong>Tên:</strong> ${userName}</p>
                        <p><strong>Số tiền:</strong> <span class="text-warning">${amount}</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Thông Tin Chuyển Khoản</h6>
                        <p><strong>Ngân hàng:</strong> ${bankName}</p>
                        <p><strong>Số TK:</strong> <span class="text-monospace">${bankAccount}</span></p>
                        <p><strong>Chủ TK:</strong> ${accountName}</p>
                    </div>
                </div>
            </div>
        </div>
    `);
    $('#approveWithdrawalModal').modal('show');
}

function rejectRequest(type, id, userName, amount) {
    $('#rejectForm').attr('action', '{{ route("admin.wallet.reject", [":type", ":id"]) }}'.replace(':type', type).replace(':id', id));
    $('#rejectInfo').html(`
        <div class="alert alert-warning">
            <strong>User:</strong> ${userName}<br>
            <strong>Số tiền:</strong> ${amount}
        </div>
    `);
    $('#rejectModal').modal('show');
}

$(document).ready(function() {
    // Reset form khi đóng modal
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});
</script>
@endpush