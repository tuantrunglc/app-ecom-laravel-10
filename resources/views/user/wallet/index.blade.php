@extends('user.layouts.master')

@section('title','My Wallet')

@section('main-content')
<div class="card">
    <h5 class="card-header">My Wallet</h5>
    <div class="card-body">
        @include('user.layouts.notification')
        
        <!-- Balance Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">Current Balance</h4>
                        <h2 class="mb-0">{{ $user->formatted_balance }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end align-items-center h-100">
                    <a href="{{ route('wallet.deposit.form') }}" class="btn btn-success btn-lg mr-2">
                        <i class="fas fa-plus"></i> Deposit
                    </a>
                    <a href="{{ route('wallet.withdraw.form') }}" class="btn btn-warning btn-lg">
                        <i class="fas fa-minus"></i> Withdraw
                    </a>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="row">
            <div class="col-md-8">
                <h5>Transaction History</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($transaction->type == 'deposit')
                                        <span class="badge badge-success">Deposit</span>
                                    @else
                                        <span class="badge badge-danger">Withdraw</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->formatted_amount }}</td>
                                <td>
                                    @if($transaction->status == 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($transaction->status == 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $transaction->description }}</small>
                                    @if($transaction->admin_note)
                                        <br><small class="text-muted">Admin: {{ $transaction->admin_note }}</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No transactions yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for transactions -->
                <div class="d-flex justify-content-center">
                    {{ $transactions->links() }}
                </div>
            </div>

            <!-- Withdrawal Requests -->
            <div class="col-md-4">
                <h5>Withdrawal Requests</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->created_at->format('d/m/Y') }}</td>
                                <td>{{ $withdrawal->formatted_amount }}</td>
                                <td>
                                    @if($withdrawal->status == 'pending')
                                        <span class="badge badge-warning badge-sm">Pending</span>
                                    @elseif($withdrawal->status == 'completed')
                                        <span class="badge badge-success badge-sm">Completed</span>
                                    @else
                                        <span class="badge badge-danger badge-sm">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No requests yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for withdrawals -->
                <div class="d-flex justify-content-center">
                    {{ $withdrawals->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-body h2 {
        font-weight: bold;
    }
    .badge-sm {
        font-size: 0.75em;
    }
</style>
@endpush