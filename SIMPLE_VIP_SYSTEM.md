# Hệ Thống VIP Đơn Giản

## 1. Tổng Quan

### Mục đích
- Admin có thể thay đổi cấp độ VIP của user
- Mỗi cấp VIP có giới hạn mua sản phẩm khác nhau trong 1 ngày
- User mua sản phẩm theo giới hạn VIP của mình

### Các Cấp Độ VIP
| Cấp Độ | Giới Hạn Mua/Ngày | Giá Tham Khảo |
|--------|-------------------|---------------|
| **FREE** | 5 sản phẩm/ngày | $0 |
| **VIP BẠC** | 30 sản phẩm/ngày | $3,888.00 |
| **VIP BẠCH KIM** | 50 sản phẩm/ngày | $5,888.00 |
| **VIP KIM CƯƠNG** | 70 sản phẩm/ngày | $7,888.00 |
| **VIP LEGEND** | 100 sản phẩm/ngày | $10,888.00 |

---

## 2. Database

### 2.1 Bảng `vip_levels` (Các Cấp Độ VIP)
```sql
CREATE TABLE vip_levels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,                     -- Tên VIP (FREE, VIP BẠC, VIP BẠCH KIM...)
    level INT NOT NULL UNIQUE,                     -- Cấp độ (0=FREE, 1=BẠC, 2=BẠCH KIM...)
    daily_purchase_limit INT NOT NULL,             -- Giới hạn mua sản phẩm/ngày
    price DECIMAL(10,2) DEFAULT 0,                 -- Giá tham khảo
    color VARCHAR(7) DEFAULT '#6c757d',            -- Màu đại diện
    is_active BOOLEAN DEFAULT TRUE,                -- Trạng thái hoạt động
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2.2 Cập Nhật Bảng `users`
```sql
ALTER TABLE users ADD COLUMN vip_level_id BIGINT UNSIGNED DEFAULT 1 AFTER role;

ALTER TABLE users ADD FOREIGN KEY (vip_level_id) REFERENCES vip_levels(id) ON DELETE SET NULL;
```

### 2.3 Bảng `user_daily_purchases` (Theo dõi mua hàng hàng ngày)
```sql
CREATE TABLE user_daily_purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    purchase_date DATE NOT NULL,                   -- Ngày mua (YYYY-MM-DD)
    products_bought INT DEFAULT 0,                 -- Số sản phẩm đã mua trong ngày
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, purchase_date),
    INDEX idx_purchase_date (purchase_date)
);
```

---

## 3. Models

### 3.1 Model `VipLevel`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipLevel extends Model
{
    protected $fillable = [
        'name', 'level', 'daily_purchase_limit', 'price', 'color', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getIsFreePlanAttribute()
    {
        return $this->level == 0;
    }
}
```

### 3.2 Model `UserDailyPurchase`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserDailyPurchase extends Model
{
    protected $fillable = [
        'user_id', 'purchase_date', 'products_bought'
    ];

    protected $casts = [
        'purchase_date' => 'date'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static methods
    public static function getTodayPurchases($userId)
    {
        return self::where('user_id', $userId)
                   ->where('purchase_date', today())
                   ->first();
    }

    public static function incrementTodayPurchases($userId, $quantity = 1)
    {
        $record = self::firstOrCreate([
            'user_id' => $userId,
            'purchase_date' => today()
        ], [
            'products_bought' => 0
        ]);

        $record->increment('products_bought', $quantity);
        return $record;
    }
}
```

### 3.3 Cập Nhật Model `User`
```php
// Thêm vào User model

protected $fillable = [
    // ... existing fields
    'vip_level_id'
];

// Relationships
public function vipLevel()
{
    return $this->belongsTo(VipLevel::class);
}

public function dailyPurchases()
{
    return $this->hasMany(UserDailyPurchase::class);
}

public function todayPurchases()
{
    return $this->hasOne(UserDailyPurchase::class)
                ->where('purchase_date', today());
}

// Methods
public function getDailyPurchaseLimitAttribute()
{
    return $this->vipLevel ? $this->vipLevel->daily_purchase_limit : 5;
}

public function getTodayPurchasesCountAttribute()
{
    $todayRecord = $this->todayPurchases;
    return $todayRecord ? $todayRecord->products_bought : 0;
}

public function canBuyMoreProductsToday($quantity = 1)
{
    return ($this->today_purchases_count + $quantity) <= $this->daily_purchase_limit;
}

public function getRemainingPurchasesTodayAttribute()
{
    return max(0, $this->daily_purchase_limit - $this->today_purchases_count);
}

public function getVipLevelNameAttribute()
{
    return $this->vipLevel ? $this->vipLevel->name : 'FREE';
}

public function getVipColorAttribute()
{
    return $this->vipLevel ? $this->vipLevel->color : '#6c757d';
}

// Add purchase when user buys products
public function addPurchase($quantity = 1)
{
    return UserDailyPurchase::incrementTodayPurchases($this->id, $quantity);
}

// Reset today purchases (if needed)
public function resetTodayPurchases()
{
    $todayRecord = $this->todayPurchases;
    if ($todayRecord) {
        $todayRecord->update(['products_bought' => 0]);
    }
}
```

---

## 4. Controllers

### 4.1 Admin VIP Management Controller
```php
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\VipLevel;
use App\Models\User;
use Illuminate\Http\Request;

class VipManagementController extends Controller
{
    // Quản lý VIP Levels
    public function vipLevels()
    {
        $vipLevels = VipLevel::ordered()->get();
        return view('backend.vip.levels', compact('vipLevels'));
    }

    public function updateVipLevel(Request $request, VipLevel $vipLevel)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'daily_purchase_limit' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'color' => 'required|string|max:7',
        ]);

        $vipLevel->update($request->only(['name', 'daily_purchase_limit', 'price', 'color']));
        
        return redirect()->back()->with('success', 'VIP Level updated successfully');
    }

    // Quản lý VIP của Users
    public function userVipManagement()
    {
        $users = User::with('vipLevel')->paginate(20);
        $vipLevels = VipLevel::active()->ordered()->get();
        
        return view('backend.vip.user-management', compact('users', 'vipLevels'));
    }

    public function changeUserVip(Request $request, User $user)
    {
        $request->validate([
            'vip_level_id' => 'required|exists:vip_levels,id'
        ]);

        $user->update([
            'vip_level_id' => $request->vip_level_id
        ]);

        return redirect()->back()->with('success', "User {$user->name} VIP level updated successfully");
    }

    public function resetUserTodayPurchases(User $user)
    {
        $user->resetTodayPurchases();
        return redirect()->back()->with('success', "Today purchases reset for {$user->name}");
    }
}
```

### 4.2 Order/Cart Controller (với kiểm tra limit mua hàng)
```php
<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function addToCart(Request $request)
    {
        $user = Auth::user();
        $quantity = $request->input('quantity', 1);
        
        // Kiểm tra giới hạn mua hàng trong ngày
        if (!$user->canBuyMoreProductsToday($quantity)) {
            return redirect()->back()->with('error', 
                "Daily purchase limit exceeded! You can buy {$user->daily_purchase_limit} products per day. " .
                "Today: {$user->today_purchases_count}/{$user->daily_purchase_limit}. VIP: {$user->vip_level_name}"
            );
        }

        // Validate request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Thêm vào cart logic
        // ... existing cart logic

        return redirect()->back()->with('success', 
            "Product added to cart! Remaining purchases today: {$user->remaining_purchases_today}"
        );
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cartItems = $request->input('cart_items', []);
        $totalQuantity = array_sum(array_column($cartItems, 'quantity'));
        
        // Kiểm tra giới hạn mua hàng trước khi checkout
        if (!$user->canBuyMoreProductsToday($totalQuantity)) {
            return redirect()->back()->with('error', 
                "Cannot complete purchase. Daily limit: {$user->daily_purchase_limit}, " .
                "Today purchased: {$user->today_purchases_count}, Trying to buy: {$totalQuantity}"
            );
        }

        // Process order
        // ... existing order processing logic

        // Ghi nhận số lượng đã mua trong ngày
        $user->addPurchase($totalQuantity);

        return redirect()->route('order.success')->with('success', 
            "Order completed successfully! Remaining purchases today: {$user->remaining_purchases_today}"
        );
    }
}
```

---

## 5. Views

### 5.1 Admin - VIP Levels Management
```php
<!-- resources/views/backend/vip/levels.blade.php -->
@extends('backend.layouts.master')

@section('title', 'VIP Levels Management')

@section('main-content')
<div class="card">
    <div class="card-header">
        <h5>VIP Levels Configuration</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Name</th>
                        <th>Product Limit</th>
                        <th>Price</th>
                        <th>Color</th>
                        <th>Users Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vipLevels as $level)
                    <tr>
                        <td>
                            <span class="badge badge-info">{{ $level->level }}</span>
                        </td>
                        <td>
                            <strong style="color: {{ $level->color }}">{{ $level->name }}</strong>
                        </td>
                        <td>{{ $level->daily_purchase_limit }} products/day</td>
                        <td>${{ number_format($level->price, 2) }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $level->color }}; color: white;">
                                {{ $level->color }}
                            </span>
                        </td>
                        <td>{{ $level->users->count() }} users</td>
                        <td>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editLevel{{ $level->id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editLevel{{ $level->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.vip.update-level', $level) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5>Edit {{ $level->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $level->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Daily Purchase Limit</label>
                                            <input type="number" name="daily_purchase_limit" class="form-control" value="{{ $level->daily_purchase_limit }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Price</label>
                                            <input type="number" step="0.01" name="price" class="form-control" value="{{ $level->price }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Color</label>
                                            <input type="color" name="color" class="form-control" value="{{ $level->color }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

### 5.2 Admin - User VIP Management
```php
<!-- resources/views/backend/vip/user-management.blade.php -->
@extends('backend.layouts.master')

@section('title', 'User VIP Management')

@section('main-content')
<div class="card">
    <div class="card-header">
        <h5>User VIP Management</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Current VIP</th>
                        <th>Today Purchases</th>
                        <th>Daily Limit</th>
                        <th>Remaining Today</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong><br>
                            <small class="text-muted">{{ $user->email }}</small>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $user->vip_color }}; color: white;">
                                {{ $user->vip_level_name }}
                            </span>
                        </td>
                        <td>{{ $user->today_purchases_count }}</td>
                        <td>{{ $user->daily_purchase_limit }}</td>
                        <td>
                            @if($user->remaining_purchases_today > 0)
                                <span class="text-success">{{ $user->remaining_purchases_today }}</span>
                            @else
                                <span class="text-danger">0 (Limit reached)</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#changeVip{{ $user->id }}">
                                    <i class="fas fa-crown"></i> Change VIP
                                </button>
                                <form method="POST" action="{{ route('admin.vip.reset-today-purchases', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Reset today purchases for this user?')">
                                        <i class="fas fa-redo"></i> Reset Today
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Change VIP Modal -->
                    <div class="modal fade" id="changeVip{{ $user->id }}">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.vip.change-user-vip', $user) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5>Change VIP for {{ $user->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Select VIP Level</label>
                                            <select name="vip_level_id" class="form-control" required>
                                                @foreach($vipLevels as $level)
                                                    <option value="{{ $level->id }}" 
                                                        {{ $user->vip_level_id == $level->id ? 'selected' : '' }}>
                                                        {{ $level->name }} ({{ $level->daily_purchase_limit }} products/day)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="alert alert-info">
                                            <small>
                                                Current: {{ $user->today_purchases_count }}/{{ $user->daily_purchase_limit }} products purchased today
                                            </small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Change VIP</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{ $users->links() }}
    </div>
</div>
@endsection
```

### 5.3 User Dashboard - VIP Status
```php
<!-- resources/views/user/dashboard.blade.php (thêm vào dashboard) -->
<div class="col-lg-4 col-md-6 col-12">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        VIP Status
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <span class="badge" style="background-color: {{ Auth::user()->vip_color }}; color: white; font-size: 14px;">
                            {{ Auth::user()->vip_level_name }}
                        </span>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-crown fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-4 col-md-6 col-12">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Daily Purchase Status
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ Auth::user()->today_purchases_count }} / {{ Auth::user()->daily_purchase_limit }}
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ (Auth::user()->today_purchases_count / Auth::user()->daily_purchase_limit) * 100 }}%">
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-upload fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 6. Routes

```php
// routes/web.php

// Admin VIP Management Routes
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    // VIP Levels
    Route::get('vip/levels', 'Backend\VipManagementController@vipLevels')->name('admin.vip.levels');
    Route::put('vip/levels/{vipLevel}', 'Backend\VipManagementController@updateVipLevel')->name('admin.vip.update-level');
    
    // User VIP Management
    Route::get('vip/users', 'Backend\VipManagementController@userVipManagement')->name('admin.vip.users');
    Route::post('vip/users/{user}/change-vip', 'Backend\VipManagementController@changeUserVip')->name('admin.vip.change-user-vip');
    Route::post('vip/users/{user}/reset-today', 'Backend\VipManagementController@resetUserTodayPurchases')->name('admin.vip.reset-today-purchases');
});
```

---

## 7. Migration Files

### 7.1 Create VIP Levels Table
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVipLevelsTable extends Migration
{
    public function up()
    {
        Schema::create('vip_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->integer('level')->unique();
            $table->integer('daily_purchase_limit');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('color', 7)->default('#6c757d');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('level');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vip_levels');
    }
}
```

### 7.2 Add VIP Fields to Users Table
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVipFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('vip_level_id')->default(1)->after('role')->constrained('vip_levels');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vip_level_id']);
            $table->dropColumn('vip_level_id');
        });
    }
}
```

### 7.3 Create User Daily Purchases Table
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDailyPurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('user_daily_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('purchase_date');
            $table->integer('products_bought')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'purchase_date']);
            $table->index('purchase_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_daily_purchases');
    }
}
```

---

## 8. Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VipLevel;

class VipLevelSeeder extends Seeder
{
    public function run()
    {
        $levels = [
            [
                'name' => 'FREE',
                'level' => 0,
                'daily_purchase_limit' => 5,
                'price' => 0.00,
                'color' => '#6c757d',
            ],
            [
                'name' => 'VIP BẠC',
                'level' => 1,
                'daily_purchase_limit' => 30,
                'price' => 3888.00,
                'color' => '#c0c0c0',
            ],
            [
                'name' => 'VIP BẠCH KIM',
                'level' => 2,
                'daily_purchase_limit' => 50,
                'price' => 5888.00,
                'color' => '#e5e4e2',
            ],
            [
                'name' => 'VIP KIM CƯƠNG',
                'level' => 3,
                'daily_purchase_limit' => 70,
                'price' => 7888.00,
                'color' => '#b9f2ff',
            ],
            [
                'name' => 'VIP LEGEND',
                'level' => 4,
                'daily_purchase_limit' => 100,
                'price' => 10888.00,
                'color' => '#ffd700',
            ]
        ];

        foreach ($levels as $level) {
            VipLevel::create($level);
        }
    }
}
```

---

## 9. Middleware (Tùy chọn)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDailyPurchaseLimit
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $quantity = $request->input('quantity', 1);
        
        if (!$user->canBuyMoreProductsToday($quantity)) {
            return redirect()->back()->with('error', 
                "Daily purchase limit reached! You can buy {$user->daily_purchase_limit} products per day with {$user->vip_level_name} plan. " .
                "Today: {$user->today_purchases_count}/{$user->daily_purchase_limit}"
            );
        }

        return $next($request);
    }
}
```

---

## 10. Implementation Steps

### Bước 1: Database
```bash
php artisan make:migration create_vip_levels_table
php artisan make:migration add_vip_fields_to_users_table
php artisan make:migration create_user_daily_purchases_table
php artisan migrate
```

### Bước 2: Models
```bash
php artisan make:model VipLevel
php artisan make:model UserDailyPurchase
# Cập nhật User model
```

### Bước 3: Controllers & Views
```bash
php artisan make:controller Backend/VipManagementController
# Tạo views theo template trên
```

### Bước 4: Seeder
```bash
php artisan make:seeder VipLevelSeeder
php artisan db:seed --class=VipLevelSeeder
```

### Bước 5: Routes
```php
# Thêm routes vào web.php
```

---

## 11. Tính Năng Chính

✅ **Admin có thể:**
- Chỉnh sửa tên, giới hạn mua hàng/ngày, giá, màu sắc của từng VIP level
- Thay đổi VIP level của bất kỳ user nào
- Reset số lượng sản phẩm đã mua trong ngày của user
- Xem thống kê users theo VIP level

✅ **User:**
- Xem VIP level hiện tại và giới hạn mua hàng/ngày
- Mua sản phẩm theo giới hạn VIP
- Thấy thông báo khi đạt giới hạn mua hàng trong ngày

✅ **Hệ thống:**
- Tự động kiểm tra giới hạn khi mua hàng
- Hiển thị progress bar mua hàng trong ngày
- Màu sắc phân biệt VIP levels
- Reset tự động mỗi ngày (giới hạn mua hàng được làm mới)

Hệ thống này đơn giản, dễ quản lý và đáp ứng đủ yêu cầu của bạn!