# Hệ Thống Ví Điện Tử - Phiên Bản Đơn Giản

## Tổng Quan
Hệ thống ví cho phép user nạp/rút tiền. CSKH sẽ xử lý thủ công các yêu cầu.

## Database

### 1. Thêm cột wallet vào bảng users
```sql
ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(15,2) DEFAULT 0.00 AFTER status;
```

### 2. Bảng lịch sử giao dịch
```sql
CREATE TABLE wallet_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('deposit', 'withdraw') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_before DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
    admin_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_transactions (user_id, created_at)
);
```

### 3. Bảng yêu cầu rút tiền
```sql
CREATE TABLE withdrawal_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    bank_name VARCHAR(255) NOT NULL,
    bank_account VARCHAR(100) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
    admin_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Models

### User Model (app/User.php)
```php
<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'photo', 'status', 
        'provider', 'provider_id', 'wallet_balance'
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
    ];

    // Relationships
    public function walletTransactions()
    {
        return $this->hasMany('App\Models\WalletTransaction')->orderBy('created_at', 'desc');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany('App\Models\WithdrawalRequest')->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->wallet_balance, 0, ',', '.') . ' VNĐ';
    }
}
```

### WalletTransaction Model
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_before', 'balance_after',
        'description', 'status', 'admin_note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', '.') . ' VNĐ';
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'completed' => 'Hoàn thành',
            'rejected' => 'Từ chối'
        ];
        return $statuses[$this->status] ?? $this->status;
    }
}
```

### WithdrawalRequest Model
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'bank_name', 'bank_account', 
        'account_name', 'status', 'admin_note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', '.') . ' VNĐ';
    }
}
```

## Controller

### WalletController
```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Trang ví chính
    public function index()
    {
        $user = Auth::user();
        $transactions = $user->walletTransactions()->paginate(10);
        $withdrawals = $user->withdrawalRequests()->paginate(5);

        return view('user.wallet.index', compact('user', 'transactions', 'withdrawals'));
    }

    // Form yêu cầu nạp tiền
    public function depositForm()
    {
        return view('user.wallet.deposit');
    }

    // Xử lý yêu cầu nạp tiền
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:50000000',
            'note' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();

        // Tạo yêu cầu nạp tiền
        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'balance_before' => $user->wallet_balance,
            'balance_after' => $user->wallet_balance, // Chưa thay đổi
            'description' => 'Yêu cầu nạp tiền: ' . ($request->note ?? ''),
            'status' => 'pending'
        ]);

        return redirect()->route('wallet.index')
            ->with('success', 'Yêu cầu nạp tiền đã được gửi. CSKH sẽ liên hệ với bạn sớm.');
    }

    // Form yêu cầu rút tiền
    public function withdrawForm()
    {
        return view('user.wallet.withdraw');
    }

    // Xử lý yêu cầu rút tiền
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:100',
            'account_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Số dư không đủ để thực hiện giao dịch');
        }

        // Tạo yêu cầu rút tiền
        WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'account_name' => $request->account_name,
            'status' => 'pending'
        ]);

        return redirect()->route('wallet.index')
            ->with('success', 'Yêu cầu rút tiền đã được gửi. CSKH sẽ xử lý trong 1-3 ngày làm việc.');
    }
}
```

### Admin Controller
```php
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    // Danh sách yêu cầu nạp tiền
    public function deposits()
    {
        $deposits = WalletTransaction::where('type', 'deposit')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallet.deposits', compact('deposits'));
    }

    // Duyệt nạp tiền
    public function approveDeposit(Request $request, $id)
    {
        $transaction = WalletTransaction::findOrFail($id);
        $user = $transaction->user;

        DB::transaction(function () use ($transaction, $user, $request) {
            // Cập nhật số dư user
            $newBalance = $user->wallet_balance + $transaction->amount;
            $user->update(['wallet_balance' => $newBalance]);

            // Cập nhật transaction
            $transaction->update([
                'status' => 'completed',
                'balance_after' => $newBalance,
                'admin_note' => $request->admin_note
            ]);
        });

        return back()->with('success', 'Đã duyệt nạp tiền thành công');
    }

    // Danh sách yêu cầu rút tiền
    public function withdrawals()
    {
        $withdrawals = WithdrawalRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallet.withdrawals', compact('withdrawals'));
    }

    // Duyệt rút tiền
    public function approveWithdrawal(Request $request, $id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);
        $user = $withdrawal->user;

        DB::transaction(function () use ($withdrawal, $user, $request) {
            // Trừ tiền từ ví user
            $newBalance = $user->wallet_balance - $withdrawal->amount;
            $user->update(['wallet_balance' => $newBalance]);

            // Tạo transaction rút tiền
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'withdraw',
                'amount' => $withdrawal->amount,
                'balance_before' => $user->wallet_balance + $withdrawal->amount,
                'balance_after' => $newBalance,
                'description' => 'Rút tiền về ' . $withdrawal->bank_name,
                'status' => 'completed',
                'admin_note' => $request->admin_note
            ]);

            // Cập nhật trạng thái withdrawal
            $withdrawal->update([
                'status' => 'completed',
                'admin_note' => $request->admin_note
            ]);
        });

        return back()->with('success', 'Đã duyệt rút tiền thành công');
    }

    // Từ chối yêu cầu
    public function reject(Request $request, $type, $id)
    {
        if ($type === 'deposit') {
            $item = WalletTransaction::findOrFail($id);
        } else {
            $item = WithdrawalRequest::findOrFail($id);
        }

        $item->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu');
    }
}
```

## Routes

### routes/web.php
```php
// User Wallet Routes
Route::middleware(['auth'])->prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/', 'WalletController@index')->name('index');
    Route::get('/deposit', 'WalletController@depositForm')->name('deposit.form');
    Route::post('/deposit', 'WalletController@deposit')->name('deposit');
    Route::get('/withdraw', 'WalletController@withdrawForm')->name('withdraw.form');
    Route::post('/withdraw', 'WalletController@withdraw')->name('withdraw');
});

// Admin Wallet Routes
Route::middleware(['auth', 'admin'])->prefix('admin/wallet')->name('admin.wallet.')->group(function () {
    Route::get('/deposits', 'Admin\WalletController@deposits')->name('deposits');
    Route::post('/deposits/{id}/approve', 'Admin\WalletController@approveDeposit')->name('deposits.approve');
    Route::get('/withdrawals', 'Admin\WalletController@withdrawals')->name('withdrawals');
    Route::post('/withdrawals/{id}/approve', 'Admin\WalletController@approveWithdrawal')->name('withdrawals.approve');
    Route::post('/{type}/{id}/reject', 'Admin\WalletController@reject')->name('reject');
});
```

## Views Cơ Bản

### user/wallet/index.blade.php
```html
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Ví Của Tôi</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h3>Số dư: {{ $user->formatted_balance }}</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('wallet.deposit.form') }}" class="btn btn-success">Nạp Tiền</a>
                            <a href="{{ route('wallet.withdraw.form') }}" class="btn btn-warning">Rút Tiền</a>
                        </div>
                    </div>

                    <!-- Lịch sử giao dịch -->
                    <h5>Lịch Sử Giao Dịch</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Loại</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $transaction->type == 'deposit' ? 'Nạp tiền' : 'Rút tiền' }}</td>
                                    <td>{{ $transaction->formatted_amount }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ $transaction->status_text }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### user/wallet/deposit.blade.php
```html
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Yêu Cầu Nạp Tiền</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('wallet.deposit') }}">
                        @csrf
                        <div class="form-group">
                            <label>Số tiền (VNĐ)</label>
                            <input type="number" name="amount" class="form-control" min="10000" max="50000000" required>
                            <small class="text-muted">Tối thiểu: 10,000 VNĐ - Tối đa: 50,000,000 VNĐ</small>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú (tùy chọn)</label>
                            <textarea name="note" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Gửi Yêu Cầu</button>
                        <a href="{{ route('wallet.index') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### user/wallet/withdraw.blade.php
```html
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Yêu Cầu Rút Tiền</div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Số dư hiện tại:</strong> {{ Auth::user()->formatted_balance }}
                    </div>
                    
                    <form method="POST" action="{{ route('wallet.withdraw') }}">
                        @csrf
                        <div class="form-group">
                            <label>Số tiền rút (VNĐ)</label>
                            <input type="number" name="amount" class="form-control" min="50000" required>
                            <small class="text-muted">Tối thiểu: 50,000 VNĐ</small>
                        </div>
                        <div class="form-group">
                            <label>Tên ngân hàng</label>
                            <input type="text" name="bank_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Số tài khoản</label>
                            <input type="text" name="bank_account" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tên chủ tài khoản</label>
                            <input type="text" name="account_name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Gửi Yêu Cầu</button>
                        <a href="{{ route('wallet.index') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## Migration Files

### Migration 1: Add wallet_balance to users
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletBalanceToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('wallet_balance', 15, 2)->default(0.00)->after('status');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
}
```

### Migration 2: Create wallet_transactions table
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdraw']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
}
```

### Migration 3: Create withdrawal_requests table
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('bank_name');
            $table->string('bank_account', 100);
            $table->string('account_name');
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
}
```

## Cách Triển Khai

1. **Tạo migrations:**
```bash
php artisan make:migration add_wallet_balance_to_users_table --table=users
php artisan make:migration create_wallet_transactions_table --create=wallet_transactions
php artisan make:migration create_withdrawal_requests_table --create=withdrawal_requests
```

2. **Chạy migrations:**
```bash
php artisan migrate
```

3. **Tạo models:**
```bash
php artisan make:model Models/WalletTransaction
php artisan make:model Models/WithdrawalRequest
```

4. **Tạo controllers:**
```bash
php artisan make:controller WalletController
php artisan make:controller Admin/WalletController
```

5. **Tạo views** theo template trên

6. **Thêm routes** vào `routes/web.php`

## Workflow

1. **User nạp tiền:** Tạo yêu cầu → CSKH liên hệ → Admin duyệt → Tiền vào ví
2. **User rút tiền:** Tạo yêu cầu → Admin kiểm tra → Duyệt → Chuyển khoản → Trừ tiền ví

Hệ thống này đơn giản, dễ quản lý và phù hợp với quy trình CSKH thủ công.