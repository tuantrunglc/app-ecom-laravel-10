@extends('user.layouts.master')

@section('title','My Wallet')

@section('main-content')
<!-- Notifications -->
@include('user.layouts.notification')

@if(!$hasBankInfo)
<!-- Bank Info Warning -->
<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <div class="d-flex align-items-center">
    <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
    <div>
      <h5 class="alert-heading mb-1">Link Bank Information</h5>
      <p class="mb-2">You need to link your bank information to be able to withdraw money from your wallet.</p>
      <a href="{{ route('user-profile') }}" class="btn btn-warning btn-sm">
        <i class="fas fa-user-edit mr-1"></i> Update Now
      </a>
    </div>
  </div>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 mb-1 text-gray-800 font-weight-bold">My Wallet</h1>
    <p class="text-muted mb-0">Manage your account balance and transactions</p>
  </div>
  <div class="d-none d-md-block">
    <a href="{{route('home')}}" target="_blank" class="walmart-btn walmart-btn-secondary">
      <i class="fas fa-shopping-cart mr-2"></i>
      Continue Shopping
    </a>
  </div>
</div>

@php
    $total_deposits = $transactions->where('type', 'deposit')->where('status', 'completed')->sum('amount');
    $total_withdrawals = $transactions->where('type', 'withdraw')->where('status', 'completed')->sum('amount');
    $pending_transactions = $transactions->where('status', 'pending')->count();
@endphp

<!-- Wallet Stats -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card primary">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{ $user->formatted_balance ?? '$0.00' }}</h3>
          <p>Current Balance</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-wallet"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card success">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>${{number_format($total_deposits, 2)}}</h3>
          <p>Total Deposits</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-arrow-down"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card warning">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>${{number_format($total_withdrawals, 2)}}</h3>
          <p>Total Withdrawals</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-arrow-up"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="stats-card info">
      <div class="stats-card-content">
        <div class="stats-card-info">
          <h3>{{$pending_transactions}}</h3>
          <p>Pending</p>
        </div>
        <div class="stats-card-icon">
          <i class="fas fa-clock"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
  <div class="col-12">
    <div class="walmart-card">
      <div class="card-header">
        <h4 class="card-title">Quick Actions</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="action-card deposit-card">
              <div class="action-content">
                <div class="action-icon">
                  <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-info">
                  <h5>Add Money</h5>
                  <p class="text-muted">Deposit funds to your wallet</p>
                </div>
                <div class="action-button">
                  <a href="{{ route('wallet.deposit.form') }}" class="walmart-btn walmart-btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Deposit
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="action-card withdraw-card">
              <div class="action-content">
                <div class="action-icon">
                  <i class="fas fa-minus-circle"></i>
                </div>
                <div class="action-info">
                  <h5>Withdraw Money</h5>
                  <p class="text-muted">Request withdrawal from your wallet</p>
                </div>
                <div class="action-button">
                  @if($hasBankInfo)
                    <a href="{{ route('wallet.withdraw.form') }}" class="walmart-btn walmart-btn-warning">
                      <i class="fas fa-minus mr-2"></i>
                      Withdraw
                    </a>
                  @else
                    <button class="walmart-btn walmart-btn-secondary" disabled title="Need to link bank information">
                      <i class="fas fa-lock mr-2"></i>
                      Withdraw
                    </button>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Transaction History -->
<div class="row">
  <div class="col-lg-8 mb-4">
    <div class="walmart-card">
      <div class="card-header">
        <h4 class="card-title">Transaction History</h4>
        <div class="d-flex align-items-center">
          <select class="walmart-select" id="transactionFilter" style="width: auto; min-width: 120px;">
            <option value="">All Types</option>
            <option value="deposit">Deposits</option>
            <option value="withdraw">Withdrawals</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        @if(count($transactions) > 0)
          <div class="table-responsive">
            <table class="walmart-table">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                @foreach($transactions as $transaction)
                <tr data-type="{{$transaction->type}}">
                  <td data-label="Date & Time">
                    <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                  </td>
                  <td data-label="Type">
                    @if($transaction->type == 'deposit')
                      <span class="transaction-type deposit">
                        <i class="fas fa-arrow-down mr-1"></i>
                        Deposit
                      </span>
                    @else
                      <span class="transaction-type withdraw">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Withdraw
                      </span>
                    @endif
                  </td>
                  <td data-label="Amount">
                    <span class="amount {{$transaction->type == 'deposit' ? 'positive' : 'negative'}}">
                      {{$transaction->type == 'deposit' ? '+' : '-'}}{{ $transaction->formatted_amount ?? '$'.number_format($transaction->amount, 2) }}
                    </span>
                  </td>
                  <td data-label="Status">
                    @if($transaction->status == 'pending')
                      <span class="status-badge process">Pending</span>
                    @elseif($transaction->status == 'completed')
                      <span class="status-badge delivered">Completed</span>
                    @else
                      <span class="status-badge cancelled">Rejected</span>
                    @endif
                  </td>
                  <td data-label="Description">
                    <div class="transaction-description">
                      <small>{{ $transaction->description ?? 'No description' }}</small>
                      @if($transaction->admin_note ?? false)
                        <br><small class="text-muted"><strong>Admin:</strong> {{ $transaction->admin_note }}</small>
                      @endif
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="p-3 border-top">
            {{ $transactions->links() }}
          </div>
        @else
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="fas fa-receipt fa-4x text-muted"></i>
            </div>
            <h5 class="text-muted mb-3">No Transactions Yet</h5>
            <p class="text-muted mb-4">Your transaction history will appear here once you make deposits or withdrawals.</p>
            <a href="{{ route('wallet.deposit.form') }}" class="walmart-btn walmart-btn-primary">
              <i class="fas fa-plus mr-2"></i>
              Make First Deposit
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Withdrawal Requests -->
  <div class="col-lg-4 mb-4">
    <div class="walmart-card">
      <div class="card-header">
        <h4 class="card-title">Recent Withdrawals</h4>
      </div>
      <div class="card-body p-0">
        @if(count($withdrawals ?? []) > 0)
          <div class="withdrawal-list">
            @foreach($withdrawals as $withdrawal)
            <div class="withdrawal-item">
              <div class="withdrawal-content">
                <div class="withdrawal-info">
                  <div class="withdrawal-amount">{{ $withdrawal->formatted_amount ?? '$'.number_format($withdrawal->amount, 2) }}</div>
                  <div class="withdrawal-date">{{ $withdrawal->created_at->format('M d, Y') }}</div>
                </div>
                <div class="withdrawal-status">
                  @if($withdrawal->status == 'pending')
                    <span class="status-badge process">Pending</span>
                  @elseif($withdrawal->status == 'completed')
                    <span class="status-badge delivered">Completed</span>
                  @else
                    <span class="status-badge cancelled">Rejected</span>
                  @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
          
          <!-- Pagination -->
          <div class="p-3 border-top">
            {{ $withdrawals->links() }}
          </div>
        @else
          <div class="text-center py-4">
            <i class="fas fa-money-bill-wave fa-2x text-muted mb-3"></i>
            <p class="text-muted mb-0">No withdrawal requests yet</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
/* Action Cards */
.action-card {
  background: var(--white);
  border: 1px solid var(--border-light);
  border-radius: 8px;
  padding: 1.5rem;
  transition: all 0.2s ease;
  height: 100%;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.action-icon {
  font-size: 2.5rem;
  color: var(--walmart-blue);
  flex-shrink: 0;
}

.deposit-card .action-icon {
  color: var(--success);
}

.withdraw-card .action-icon {
  color: var(--warning);
}

.action-info {
  flex: 1;
}

.action-info h5 {
  margin-bottom: 0.5rem;
  font-weight: var(--font-semibold);
  color: var(--gray-800);
}

.action-info p {
  margin-bottom: 0;
  font-size: var(--text-sm);
}

.action-button {
  flex-shrink: 0;
}

/* Transaction Types */
.transaction-type {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: var(--text-xs);
  font-weight: var(--font-medium);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.transaction-type.deposit {
  background: rgba(40, 167, 69, 0.1);
  color: var(--success);
}

.transaction-type.withdraw {
  background: rgba(255, 193, 7, 0.1);
  color: #856404;
}

/* Amount Styling */
.amount {
  font-weight: var(--font-bold);
  font-size: var(--text-base);
}

.amount.positive {
  color: var(--success);
}

.amount.negative {
  color: var(--danger);
}

/* Transaction Description */
.transaction-description {
  max-width: 200px;
}

.transaction-description small {
  line-height: 1.4;
}

/* Withdrawal List */
.withdrawal-list {
  padding: 0;
}

.withdrawal-item {
  border-bottom: 1px solid var(--border-light);
  transition: background-color 0.2s ease;
}

.withdrawal-item:hover {
  background-color: var(--gray-50);
}

.withdrawal-item:last-child {
  border-bottom: none;
}

.withdrawal-content {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.withdrawal-amount {
  font-weight: var(--font-bold);
  color: var(--gray-800);
  margin-bottom: 0.25rem;
}

.withdrawal-date {
  font-size: var(--text-sm);
  color: var(--gray-600);
}

/* Utility Classes */
.fa-2x {
  font-size: 2em;
}

.fa-4x {
  font-size: 4em;
}

.mb-3 {
  margin-bottom: 1rem !important;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}

.mr-1 {
  margin-right: 0.25rem !important;
}

.mr-2 {
  margin-right: 0.5rem !important;
}

.p-0 {
  padding: 0 !important;
}

.p-3 {
  padding: 1rem !important;
}

.py-4 {
  padding-top: 1.5rem !important;
  padding-bottom: 1.5rem !important;
}

.py-5 {
  padding-top: 3rem !important;
  padding-bottom: 3rem !important;
}

.border-top {
  border-top: 1px solid var(--border-light) !important;
}

.text-center {
  text-align: center !important;
}

/* Transaction filter styling */
#transactionFilter {
  padding: 0.5rem 0.75rem;
  font-size: var(--text-sm);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .action-content {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .action-icon {
    font-size: 2rem;
  }
  
  .withdrawal-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .withdrawal-status {
    align-self: flex-end;
  }
  
  .transaction-description {
    max-width: none;
  }
  
  .stats-card h3 {
    font-size: 1.5rem;
  }
  
  .amount {
    font-size: var(--text-sm);
  }
}

@media (max-width: 575.98px) {
  .action-card {
    padding: 1rem;
  }
  
  .withdrawal-content {
    padding: 0.75rem 1rem;
  }
}

/* Print styles */
@media print {
  .action-card,
  .walmart-btn {
    display: none !important;
  }
  
  .walmart-card {
    box-shadow: none !important;
    border: 1px solid #ddd !important;
  }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function(){
  // Transaction filter functionality
  $('#transactionFilter').change(function(){
    var type = $(this).val().toLowerCase();
    var rows = $('table tbody tr');
    
    if(type === '') {
      rows.show();
    } else {
      rows.each(function(){
        var rowType = $(this).data('type');
        if(rowType === type) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  });
  
  // Initialize tooltips
  $('[data-toggle="tooltip"]').tooltip();
  
  // Stats cards click functionality
  $('.stats-card').click(function(){
    var cardType = '';
    if($(this).hasClass('success')) {
      cardType = 'deposit';
    } else if($(this).hasClass('warning')) {
      cardType = 'withdraw';
    }
    
    if(cardType) {
      $('#transactionFilter').val(cardType).trigger('change');
    }
  });
  
  // Add cursor pointer to clickable stats cards
  $('.stats-card.success, .stats-card.warning').css('cursor', 'pointer');
});
</script>
@endpush