@extends('backend.layouts.master')

@section('title','Quản Lý Nạp Tiền')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Danh Sách Yêu Cầu Nạp Tiền</h6>
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
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Ghi Chú</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                    <tr>
                        <td>{{ $deposit->id }}</td>
                        <td>
                            <strong>{{ $deposit->user->name }}</strong><br>
                            <small class="text-muted">{{ $deposit->user->email }}</small>
                        </td>
                        <td>
                            <strong class="text-success">{{ $deposit->formatted_amount }}</strong>
                        </td>
                        <td>
                            @if($deposit->status == 'pending')
                                <span class="badge badge-warning">{{ $deposit->status_text }}</span>
                            @elseif($deposit->status == 'completed')
                                <span class="badge badge-success">{{ $deposit->status_text }}</span>
                            @else
                                <span class="badge badge-danger">{{ $deposit->status_text }}</span>
                            @endif
                        </td>
                        <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <small>{{ $deposit->description }}</small>
                            @if($deposit->admin_note)
                                <br><small class="text-info">Admin: {{ $deposit->admin_note }}</small>
                            @endif
                        </td>
                        <td>
                            @if($deposit->status == 'pending')
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="approveDeposit({{ $deposit->id }}, '{{ $deposit->user->name }}', '{{ $deposit->formatted_amount }}')">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="rejectRequest('deposit', {{ $deposit->id }}, '{{ $deposit->user->name }}', '{{ $deposit->formatted_amount }}')">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                            @else
                                <span class="text-muted">Đã xử lý</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Không có yêu cầu nạp tiền nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $deposits->links() }}
        </div>
    </div>
</div>

<!-- Modal Duyệt Nạp Tiền -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duyệt Nạp Tiền</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn duyệt yêu cầu nạp tiền?</p>
                    <div id="approveInfo"></div>
                    
                    <div class="form-group">
                        <label for="admin_note_approve">Ghi Chú Admin (Tùy chọn)</label>
                        <textarea class="form-control" id="admin_note_approve" name="admin_note" rows="3" 
                                  placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác Nhận Duyệt</button>
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
                                  placeholder="Nhập lý do từ chối..." required></textarea>
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
function approveDeposit(id, userName, amount) {
    $('#approveForm').attr('action', '{{ route("admin.wallet.deposits.approve", ":id") }}'.replace(':id', id));
    $('#approveInfo').html(`
        <div class="alert alert-info">
            <strong>User:</strong> ${userName}<br>
            <strong>Số tiền:</strong> ${amount}
        </div>
    `);
    $('#approveModal').modal('show');
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