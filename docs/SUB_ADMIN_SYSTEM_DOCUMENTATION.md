# Hệ Thống Sub Admin - Tài Liệu Phân Tích và Hướng Dẫn Triển Khai

## 1. PHÂN TÍCH HỆ THỐNG HIỆN TẠI

### 1.1 Cấu Trúc User và Role Hiện Tại

**Bảng Users (từ migration):**
```sql
- id (Primary Key)
- name (string)
- email (string, unique, nullable)
- email_verified_at (timestamp, nullable)
- password (string, nullable)
- photo (string, nullable)
- role (enum: 'admin', 'user') - Chỉ có 2 role
- provider (string, nullable) - OAuth provider
- provider_id (string, nullable) - OAuth provider ID
- status (enum: 'active', 'inactive') - default: 'active'
- wallet_balance (decimal) - Đã có sẵn từ update trước
- remember_token (nullable)
- timestamps
```

**Controllers hiện tại:**
- `UsersController.php`: Quản lý CRUD users (chỉ admin access)
- `AdminController.php`: Dashboard admin và profile management
- `FrontendController.php`: Đăng ký user từ frontend
- `Auth/RegisterController.php`: Đăng ký chuẩn Laravel (bị disable)

**Middleware hiện tại:**
- `Admin.php`: Chỉ kiểm tra role == 'admin', redirect về route theo role
- `User.php`: Kiểm tra role == 'user'

**Routes hiện tại:**
- `/admin/*`: Middleware ['auth', 'admin'] - Chỉ admin truy cập
- `/user/*`: Middleware ['user'] - Chỉ user truy cập
- `Auth::routes(['register' => false])` - Đăng ký bị tắt
- Custom register: `/user/register` → `FrontendController@registerSubmit`

**Views hiện tại:**
- `backend/users/index.blade.php`: Danh sách users (admin)
- `backend/users/create.blade.php`: Tạo user mới (admin)
- `backend/users/edit.blade.php`: Sửa user (admin)
- `frontend/pages/register.blade.php`: Form đăng ký user

**Validation hiện tại:**
- Tạo user (admin): role chỉ có 'admin' hoặc 'user'
- Đăng ký frontend: không có trường role, mặc định là 'user'

### 1.2 Vấn đề cần giải quyết

**Hạn chế của hệ thống hiện tại:**
1. **Phân quyền đơn giản**: Chỉ có 2 role admin/user
2. **Không có hierarchy**: Admin không thể tạo sub-admin
3. **Không có referral system**: User đăng ký không có mã giới thiệu
4. **Không có relationship**: Không có mối quan hệ parent-child giữa users
5. **Quản lý tập trung**: Admin phải quản lý tất cả users
6. **Không có phân quyền chi tiết**: Sub admin không thể có quyền hạn riêng

## 2. THIẾT KẾ HỆ THỐNG SUB ADMIN MỚI

### 2.1 Cấu Trúc Role và Quyền Hạn

**ADMIN (Super Admin)**
```
Quyền hạn:
├── Quản lý toàn bộ hệ thống
├── Tạo/sửa/xóa Sub Admin
├── Phân quyền cho Sub Admin
├── Xem tất cả users và Sub Admin
├── Quản lý mã Sub Admin
├── Xem báo cáo tổng thể
├── Cấu hình hệ thống
└── Truy cập tất cả chức năng

Truy cập:
├── /admin/* (tất cả routes admin hiện tại)
├── /admin/sub-admins/* (quản lý sub admin)
└── /admin/users/* (xem tất cả users)
```

**SUB_ADMIN**
```
Quyền hạn:
├── Quản lý users thuộc quyền (users có parent_sub_admin_id = sub_admin.id)
├── Xem thống kê users của mình
├── Quản lý đơn hàng của users thuộc quyền (tùy cấu hình)
│   ├── Xem danh sách đơn hàng
│   ├── Xem chi tiết đơn hàng
│   ├── Cập nhật trạng thái đơn hàng
│   ├── In hóa đơn/phiếu giao hàng
│   ├── Xử lý hoàn trả/hủy đơn
│   └── Theo dõi vận chuyển
├── Quản lý sản phẩm (tùy cấu hình)
├── Xem báo cáo users và đơn hàng của mình
├── Tạo mã giới thiệu cho users
├── Nhận hoa hồng từ đơn hàng (nếu được cấu hình)
└── Không thể tạo Sub Admin khác

Truy cập:
├── /sub-admin/* (dashboard riêng)
├── /sub-admin/users/* (chỉ users thuộc quyền)
├── /sub-admin/orders/* (chỉ orders của users thuộc quyền)
│   ├── /sub-admin/orders (danh sách đơn hàng)
│   ├── /sub-admin/orders/{id} (chi tiết đơn hàng)
│   ├── /sub-admin/orders/{id}/edit (sửa trạng thái)
│   ├── /sub-admin/orders/{id}/invoice (in hóa đơn)
│   └── /sub-admin/orders/{id}/tracking (theo dõi vận chuyển)
├── /sub-admin/reports/* (báo cáo users và orders thuộc quyền)
└── /sub-admin/profile/* (quản lý profile)

Hạn chế:
├── Không thể xem users của Sub Admin khác
├── Không thể xem orders của users không thuộc quyền
├── Không thể truy cập /admin/*
├── Không thể tạo/sửa/xóa Sub Admin
├── Chỉ có thể cập nhật trạng thái đơn hàng theo quy định
└── Quyền hạn phụ thuộc vào sub_admin_settings
```

**USER**
```
Quyền hạn:
├── Người dùng cuối
├── Mua sắm, đặt hàng
├── Quản lý profile cá nhân
├── Xem lịch sử đơn hàng
└── Sử dụng wallet

Thuộc tính:
├── Có thể thuộc về một Sub Admin (parent_sub_admin_id)
├── Đăng ký bằng mã giới thiệu (referral_code)
└── Truy cập /user/* như hiện tại
```

### 2.2 Cấu Trúc Database Mới

**Bảng users (cập nhật):**
```sql
-- Thêm role sub_admin
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'sub_admin', 'user') DEFAULT 'user';

-- Thêm các trường mới
ALTER TABLE users 
ADD COLUMN sub_admin_code VARCHAR(20) UNIQUE NULL AFTER role COMMENT 'Mã Sub Admin để users đăng ký',
ADD COLUMN parent_sub_admin_id BIGINT UNSIGNED NULL AFTER sub_admin_code COMMENT 'ID của Sub Admin quản lý user này',
ADD COLUMN referral_code VARCHAR(20) NULL AFTER parent_sub_admin_id COMMENT 'Mã giới thiệu user sử dụng khi đăng ký',
ADD COLUMN created_by BIGINT UNSIGNED NULL AFTER referral_code COMMENT 'ID của admin/sub_admin tạo user này';

-- Thêm indexes
ALTER TABLE users 
ADD INDEX idx_parent_sub_admin (parent_sub_admin_id),
ADD INDEX idx_sub_admin_code (sub_admin_code),
ADD INDEX idx_role (role),
ADD INDEX idx_created_by (created_by);

-- Thêm foreign keys
ALTER TABLE users 
ADD FOREIGN KEY fk_parent_sub_admin (parent_sub_admin_id) REFERENCES users(id) ON DELETE SET NULL,
ADD FOREIGN KEY fk_created_by (created_by) REFERENCES users(id) ON DELETE SET NULL;
```

**Bảng sub_admin_settings (mới):**
```sql
CREATE TABLE sub_admin_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'ID của Sub Admin',
    
    -- Quyền hạn chức năng
    can_manage_products BOOLEAN DEFAULT FALSE COMMENT 'Có thể quản lý sản phẩm',
    can_manage_orders BOOLEAN DEFAULT TRUE COMMENT 'Có thể quản lý đơn hàng',
    can_view_reports BOOLEAN DEFAULT TRUE COMMENT 'Có thể xem báo cáo',
    can_manage_users BOOLEAN DEFAULT TRUE COMMENT 'Có thể quản lý users',
    can_create_users BOOLEAN DEFAULT TRUE COMMENT 'Có thể tạo users mới',
    
    -- Giới hạn
    max_users_allowed INT DEFAULT 1000 COMMENT 'Số users tối đa được quản lý',
    commission_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Tỷ lệ hoa hồng (%)',
    
    -- Cấu hình khác
    auto_approve_users BOOLEAN DEFAULT TRUE COMMENT 'Tự động duyệt users mới',
    notification_new_user BOOLEAN DEFAULT TRUE COMMENT 'Nhận thông báo user mới',
    notification_new_order BOOLEAN DEFAULT TRUE COMMENT 'Nhận thông báo đơn hàng mới',
    
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_settings (user_id)
);
```

**Bảng sub_admin_user_stats (mới - để tracking):**
```sql
CREATE TABLE sub_admin_user_stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sub_admin_id BIGINT UNSIGNED NOT NULL,
    total_users INT DEFAULT 0,
    active_users INT DEFAULT 0,
    inactive_users INT DEFAULT 0,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,
    commission_earned DECIMAL(15,2) DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sub_admin_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sub_admin_stats (sub_admin_id)
);
```

### 2.3 Quy Trình Hoạt Động Chi Tiết

**2.3.1 Tạo Sub Admin (Admin thực hiện)**
```
1. Admin truy cập /admin/sub-admins/create
2. Nhập thông tin Sub Admin:
   - Tên, email, password
   - Tự động generate sub_admin_code (unique)
   - Cấu hình quyền hạn trong sub_admin_settings
3. Hệ thống tạo:
   - User với role = 'sub_admin'
   - Record trong sub_admin_settings
   - Record trong sub_admin_user_stats
4. Gửi email thông báo cho Sub Admin
5. Sub Admin có thể login và truy cập /sub-admin/*
```

**2.3.2 Đăng Ký User Với Mã Sub Admin**
```
1. User truy cập /user/register
2. Form có thêm trường "Mã giới thiệu Sub Admin" (optional)
3. User nhập thông tin + mã Sub Admin
4. Hệ thống validate:
   - Mã Sub Admin có tồn tại không
   - Sub Admin có đang active không
   - Sub Admin có vượt quá max_users_allowed không
5. Tạo user với:
   - role = 'user'
   - parent_sub_admin_id = sub_admin.id
   - referral_code = mã đã nhập
6. Cập nhật sub_admin_user_stats
7. Gửi thông báo cho Sub Admin (nếu bật notification)
8. User được redirect về trang chủ
```

**2.3.3 Sub Admin Quản Lý Users**
```
1. Sub Admin login và truy cập /sub-admin/users
2. Chỉ hiển thị users có parent_sub_admin_id = sub_admin.id
3. Sub Admin có thể:
   - Xem danh sách users
   - Xem chi tiết user
   - Sửa thông tin user (tùy quyền hạn)
   - Kích hoạt/vô hiệu hóa user
   - Xem lịch sử đơn hàng của user
4. Mọi thao tác đều bị giới hạn trong phạm vi users thuộc quyền
```

**2.3.4 Sub Admin Quản Lý Đơn Hàng**
```
1. Sub Admin truy cập /sub-admin/orders
2. Chỉ hiển thị orders của users có parent_sub_admin_id = sub_admin.id
3. Sub Admin có thể:
   - Xem danh sách đơn hàng với filter/search
   - Xem chi tiết đơn hàng đầy đủ
   - Cập nhật trạng thái đơn hàng:
     * pending → processing → shipped → delivered
     * pending → cancelled (với lý do)
     * delivered → returned (xử lý hoàn trả)
   - In hóa đơn và phiếu giao hàng
   - Thêm ghi chú nội bộ cho đơn hàng
   - Cập nhật thông tin vận chuyển
   - Xử lý hoàn tiền (nếu có quyền)
4. Nhận thông báo real-time khi có đơn hàng mới
5. Dashboard hiển thị thống kê đơn hàng theo ngày/tuần/tháng
```

## 3. TRIỂN KHAI CODE CHI TIẾT

### 3.1 Migration Files

**File: `database/migrations/xxxx_update_users_table_for_sub_admin.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableForSubAdmin extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Cập nhật role enum
            $table->enum('role', ['admin', 'sub_admin', 'user'])->default('user')->change();
            
            // Thêm các trường mới
            $table->string('sub_admin_code', 20)->unique()->nullable()->after('role');
            $table->unsignedBigInteger('parent_sub_admin_id')->nullable()->after('sub_admin_code');
            $table->string('referral_code', 20)->nullable()->after('parent_sub_admin_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('referral_code');
            
            // Thêm indexes
            $table->index('parent_sub_admin_id', 'idx_parent_sub_admin');
            $table->index('sub_admin_code', 'idx_sub_admin_code');
            $table->index('role', 'idx_role');
            $table->index('created_by', 'idx_created_by');
            
            // Thêm foreign keys
            $table->foreign('parent_sub_admin_id', 'fk_parent_sub_admin')
                  ->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by', 'fk_created_by')
                  ->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Xóa foreign keys
            $table->dropForeign('fk_parent_sub_admin');
            $table->dropForeign('fk_created_by');
            
            // Xóa indexes
            $table->dropIndex('idx_parent_sub_admin');
            $table->dropIndex('idx_sub_admin_code');
            $table->dropIndex('idx_role');
            $table->dropIndex('idx_created_by');
            
            // Xóa columns
            $table->dropColumn(['sub_admin_code', 'parent_sub_admin_id', 'referral_code', 'created_by']);
            
            // Khôi phục role enum cũ
            $table->enum('role', ['admin', 'user'])->default('user')->change();
        });
    }
}
```

**File: `database/migrations/xxxx_create_sub_admin_settings_table.php`**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubAdminSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('sub_admin_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Quyền hạn chức năng
            $table->boolean('can_manage_products')->default(false);
            $table->boolean('can_manage_orders')->default(true);
            $table->boolean('can_view_reports')->default(true);
            $table->boolean('can_manage_users')->default(true);
            $table->boolean('can_create_users')->default(true);
            
            // Giới hạn
            $table->integer('max_users_allowed')->default(1000);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            
            // Cấu hình khác
            $table->boolean('auto_approve_users')->default(true);
            $table->boolean('notification_new_user')->default(true);
            $table->boolean('notification_new_order')->default(true);
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id', 'unique_user_settings');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_admin_settings');
    }
}
```

### 3.2 Model Updates

**File: `app/User.php` (cập nhật)**
```php
<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'photo', 'status', 'provider', 'provider_id', 
        'wallet_balance', 'sub_admin_code', 'parent_sub_admin_id', 'referral_code', 'created_by'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function walletTransactions()
    {
        return $this->hasMany('App\Models\WalletTransaction')->orderBy('created_at', 'desc');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany('App\Models\WithdrawalRequest')->orderBy('created_at', 'desc');
    }

    // Sub Admin Relationships
    public function subAdminSettings()
    {
        return $this->hasOne('App\Models\SubAdminSettings', 'user_id');
    }

    public function managedUsers()
    {
        return $this->hasMany('App\User', 'parent_sub_admin_id');
    }

    public function parentSubAdmin()
    {
        return $this->belongsTo('App\User', 'parent_sub_admin_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function subAdminStats()
    {
        return $this->hasOne('App\Models\SubAdminUserStats', 'sub_admin_id');
    }

    // Helper Methods
    public function getFormattedBalanceAttribute()
    {
        return '$' . number_format($this->wallet_balance, 2);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin()
    {
        return $this->role === 'sub_admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function canManageUser($userId)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($this->isSubAdmin()) {
            return $this->managedUsers()->where('id', $userId)->exists();
        }
        
        return false;
    }

    public function generateSubAdminCode()
    {
        do {
            $code = 'SA' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('sub_admin_code', $code)->exists());
        
        return $code;
    }

    public function getManagedUsersCount()
    {
        return $this->managedUsers()->count();
    }

    public function getActiveUsersCount()
    {
        return $this->managedUsers()->where('status', 'active')->count();
    }
}
```

**File: `app/Models/SubAdminSettings.php` (mới)**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubAdminSettings extends Model
{
    protected $fillable = [
        'user_id', 'can_manage_products', 'can_manage_orders', 'can_view_reports',
        'can_manage_users', 'can_create_users', 'max_users_allowed', 'commission_rate',
        'auto_approve_users', 'notification_new_user', 'notification_new_order'
    ];

    protected $casts = [
        'can_manage_products' => 'boolean',
        'can_manage_orders' => 'boolean',
        'can_view_reports' => 'boolean',
        'can_manage_users' => 'boolean',
        'can_create_users' => 'boolean',
        'auto_approve_users' => 'boolean',
        'notification_new_user' => 'boolean',
        'notification_new_order' => 'boolean',
        'commission_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
```

### 3.3 Middleware Updates

**File: `app/Http/Middleware/SubAdmin.php` (mới)**
```php
<?php
namespace App\Http\Middleware;

use Closure;

class SubAdmin
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login.form');
        }

        $user = auth()->user();
        
        if ($user->role === 'sub_admin') {
            return $next($request);
        }
        
        // Nếu là admin, cho phép truy cập sub-admin routes
        if ($user->role === 'admin') {
            return $next($request);
        }

        request()->session()->flash('error', 'Bạn không có quyền truy cập trang này');
        return redirect()->route($user->role);
    }
}
```

**File: `app/Http/Middleware/Admin.php` (cập nhật)**
```php
<?php
namespace App\Http\Middleware;

use Closure;

class Admin
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login.form');
        }

        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        request()->session()->flash('error', 'Bạn không có quyền truy cập trang này');
        
        // Redirect theo role
        if ($user->role === 'sub_admin') {
            return redirect()->route('sub-admin.dashboard');
        } else {
            return redirect()->route('user');
        }
    }
}
```

### 3.4 Controller Updates

**File: `app/Http/Controllers/SubAdminController.php` (mới)**
```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\SubAdminSettings;
use App\Models\SubAdminUserStats;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SubAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'sub_admin']);
    }

    // Dashboard Sub Admin
    public function index()
    {
        $subAdmin = auth()->user();
        $stats = $this->getSubAdminStats($subAdmin->id);
        
        return view('backend.sub-admin.dashboard', compact('subAdmin', 'stats'));
    }

    // Quản lý users thuộc quyền
    public function users()
    {
        $subAdmin = auth()->user();
        $users = $subAdmin->managedUsers()->paginate(10);
        
        return view('backend.sub-admin.users.index', compact('users', 'subAdmin'));
    }

    // Tạo user mới (nếu có quyền)
    public function createUser()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_create_users) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo user mới');
        }
        
        return view('backend.sub-admin.users.create');
    }

    // Lưu user mới
    public function storeUser(Request $request)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_create_users) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo user mới');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'active',
            'parent_sub_admin_id' => $subAdmin->id,
            'created_by' => $subAdmin->id,
        ]);

        $this->updateSubAdminStats($subAdmin->id);

        return redirect()->route('sub-admin.users')->with('success', 'Tạo user thành công');
    }

    // Xem chi tiết user
    public function showUser($id)
    {
        $subAdmin = auth()->user();
        $user = $subAdmin->managedUsers()->findOrFail($id);
        
        return view('backend.sub-admin.users.show', compact('user'));
    }

    // Sửa user
    public function editUser($id)
    {
        $subAdmin = auth()->user();
        $user = $subAdmin->managedUsers()->findOrFail($id);
        
        return view('backend.sub-admin.users.edit', compact('user'));
    }

    // Cập nhật user
    public function updateUser(Request $request, $id)
    {
        $subAdmin = auth()->user();
        $user = $subAdmin->managedUsers()->findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($request->only(['name', 'email', 'status']));

        return redirect()->route('sub-admin.users')->with('success', 'Cập nhật user thành công');
    }

    // Quản lý đơn hàng
    public function orders()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền quản lý đơn hàng');
        }
        
        $orders = $this->getManagedOrders($subAdmin->id)->paginate(15);
        
        return view('backend.sub-admin.orders.index', compact('orders', 'subAdmin'));
    }

    // Xem chi tiết đơn hàng
    public function showOrder($orderId)
    {
        $subAdmin = auth()->user();
        $order = $this->authorizeOrderAccess($orderId);
        
        return view('backend.sub-admin.orders.show', compact('order'));
    }

    // Sửa trạng thái đơn hàng
    public function editOrder($orderId)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa đơn hàng');
        }
        
        $order = $this->authorizeOrderAccess($orderId);
        
        return view('backend.sub-admin.orders.edit', compact('order'));
    }

    // Cập nhật đơn hàng
    public function updateOrder(Request $request, $orderId)
    {
        $subAdmin = auth()->user();
        $order = $this->authorizeOrderAccess($orderId);

        $this->validate($request, [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'cancel_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        // Kiểm tra logic chuyển trạng thái
        if (!$this->canUpdateOrderStatus($order->status, $request->status)) {
            return redirect()->back()->with('error', 'Không thể chuyển từ trạng thái ' . $order->status . ' sang ' . $request->status);
        }

        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number,
            'notes' => $request->notes,
            'cancel_reason' => $request->cancel_reason,
            'updated_by' => $subAdmin->id,
        ]);

        // Log activity
        activity()
            ->performedOn($order)
            ->causedBy($subAdmin)
            ->withProperties([
                'old_status' => $order->getOriginal('status'),
                'new_status' => $request->status,
                'notes' => $request->notes
            ])
            ->log('Order status updated by Sub Admin');

        // Gửi thông báo cho user (nếu cần)
        if (in_array($request->status, ['shipped', 'delivered', 'cancelled'])) {
            // Notification::send($order->user, new OrderStatusUpdated($order));
        }

        return redirect()->route('sub-admin.orders')->with('success', 'Cập nhật đơn hàng thành công');
    }

    // In hóa đơn
    public function printInvoice($orderId)
    {
        $order = $this->authorizeOrderAccess($orderId);
        
        return view('backend.sub-admin.orders.invoice', compact('order'));
    }

    // Báo cáo
    public function reports()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_view_reports) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem báo cáo');
        }
        
        $stats = $this->getDetailedStats($subAdmin->id);
        
        return view('backend.sub-admin.reports', compact('stats'));
    }

    // Helper methods
    private function getManagedOrders($subAdminId)
    {
        return Order::whereHas('user', function($query) use ($subAdminId) {
            $query->where('parent_sub_admin_id', $subAdminId);
        })->with(['user', 'orderItems.product'])->orderBy('created_at', 'desc');
    }

    private function authorizeOrderAccess($orderId)
    {
        $subAdmin = auth()->user();
        
        $order = Order::whereHas('user', function($query) use ($subAdmin) {
            $query->where('parent_sub_admin_id', $subAdmin->id);
        })->findOrFail($orderId);
        
        return $order;
    }

    private function canUpdateOrderStatus($currentStatus, $newStatus)
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'returned'],
            'delivered' => ['returned'],
            'cancelled' => [], // Không thể chuyển từ cancelled
            'returned' => [], // Không thể chuyển từ returned
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    private function getSubAdminStats($subAdminId)
    {
        $subAdmin = User::find($subAdminId);
        
        return [
            'total_users' => $subAdmin->getManagedUsersCount(),
            'active_users' => $subAdmin->getActiveUsersCount(),
            'inactive_users' => $subAdmin->managedUsers()->where('status', 'inactive')->count(),
            'total_orders' => $this->getTotalOrders($subAdminId),
            'pending_orders' => $this->getOrdersByStatus($subAdminId, 'pending'),
            'processing_orders' => $this->getOrdersByStatus($subAdminId, 'processing'),
            'shipped_orders' => $this->getOrdersByStatus($subAdminId, 'shipped'),
            'delivered_orders' => $this->getOrdersByStatus($subAdminId, 'delivered'),
            'total_revenue' => $this->getTotalRevenue($subAdminId),
            'monthly_revenue' => $this->getMonthlyRevenue($subAdminId),
            'commission_earned' => $this->getCommissionEarned($subAdminId),
        ];
    }

    private function getTotalOrders($subAdminId)
    {
        return Order::whereHas('user', function($query) use ($subAdminId) {
            $query->where('parent_sub_admin_id', $subAdminId);
        })->count();
    }

    private function getOrdersByStatus($subAdminId, $status)
    {
        return Order::whereHas('user', function($query) use ($subAdminId) {
            $query->where('parent_sub_admin_id', $subAdminId);
        })->where('status', $status)->count();
    }

    private function getTotalRevenue($subAdminId)
    {
        return Order::whereHas('user', function($query) use ($subAdminId) {
            $query->where('parent_sub_admin_id', $subAdminId);
        })->where('status', 'delivered')->sum('total_amount');
    }

    private function getMonthlyRevenue($subAdminId)
    {
        return Order::whereHas('user', function($query) use ($subAdminId) {
            $query->where('parent_sub_admin_id', $subAdminId);
        })->where('status', 'delivered')
          ->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->sum('total_amount');
    }

    private function getCommissionEarned($subAdminId)
    {
        $subAdmin = User::find($subAdminId);
        $commissionRate = $subAdmin->subAdminSettings->commission_rate;
        $totalRevenue = $this->getTotalRevenue($subAdminId);
        
        return $totalRevenue * ($commissionRate / 100);
    }

    private function updateSubAdminStats($subAdminId)
    {
        $stats = $this->getSubAdminStats($subAdminId);
        
        SubAdminUserStats::updateOrCreate(
            ['sub_admin_id' => $subAdminId],
            $stats
        );
    }
}
```

## 4. TÍNH NĂNG CHI TIẾT VÀ LUỒNG HOẠT ĐỘNG

### 4.1 Đăng Ký User Với Mã Sub Admin

**Frontend Form Updates:**
```html
<!-- Thêm vào form register -->
<div class="col-12">
    <div class="form-group">
        <label>Mã Giới Thiệu Sub Admin (Tùy chọn)</label>
        <input type="text" name="sub_admin_code" placeholder="Nhập mã giới thiệu nếu có" value="{{old('sub_admin_code')}}">
        @error('sub_admin_code')
            <span class="text-danger">{{$message}}</span>
        @enderror
        <small class="form-text text-muted">Nếu bạn có mã giới thiệu từ Sub Admin, hãy nhập vào đây</small>
    </div>
</div>
```

**Validation Logic:**
```php
// Trong FrontendController@registerSubmit
$this->validate($request, [
    'name' => 'string|required|min:2',
    'email' => 'string|required|unique:users,email',
    'password' => 'required|min:6|confirmed',
    'sub_admin_code' => 'nullable|exists:users,sub_admin_code'
]);

// Logic xử lý
$parentSubAdmin = null;
if ($request->sub_admin_code) {
    $parentSubAdmin = User::where('sub_admin_code', $request->sub_admin_code)
                         ->where('role', 'sub_admin')
                         ->where('status', 'active')
                         ->first();
    
    if (!$parentSubAdmin) {
        return back()->with('error', 'Mã Sub Admin không hợp lệ');
    }
    
    // Kiểm tra giới hạn users
    $currentUsersCount = $parentSubAdmin->getManagedUsersCount();
    $maxAllowed = $parentSubAdmin->subAdminSettings->max_users_allowed;
    
    if ($currentUsersCount >= $maxAllowed) {
        return back()->with('error', 'Sub Admin này đã đạt giới hạn số lượng users');
    }
}
```

### 4.2 Dashboard Sub Admin - Giao Diện và Chức Năng

**4.2.1 Dashboard Chính**
```
┌─────────────────────────────────────────────────────────────┐
│                    SUB ADMIN DASHBOARD                      │
├─────────────────────────────────────────────────────────────┤
│  Thống Kê Tổng Quan:                                       │
│  ┌─────────────┬─────────────┬─────────────┬─────────────┐  │
│  │ Tổng Users  │ Users Hoạt  │ Users Ngưng │ Đơn Hàng    │  │
│  │     150     │     142     │      8      │     89      │  │
│  └─────────────┴─────────────┴─────────────┴─────────────┘  │
│                                                             │
│  Mã Sub Admin của bạn: SA12345678                          │
│  Giới hạn users: 150/1000                                  │
│  Tỷ lệ hoa hồng: 5.00%                                     │
├─────────────────────────────────────────────────────────────┤
│  Hoạt Động Gần Đây:                                        │
│  • User mới đăng ký: john@example.com (2 giờ trước)        │
│  • Đơn hàng mới: #12345 - $150.00 (3 giờ trước)           │
│  • User kích hoạt: mary@example.com (1 ngày trước)         │
└─────────────────────────────────────────────────────────────┘
```

**4.2.2 Quản Lý Users**
```
Danh Sách Users Thuộc Quyền:
┌─────┬─────────────────┬─────────────────┬─────────────┬─────────────┬─────────────┐
│ ID  │ Tên             │ Email           │ Trạng Thái  │ Ngày Tham   │ Hành Động  │
│     │                 │                 │             │ Gia         │             │
├─────┼─────────────────┼─────────────────┼─────────────┼─────────────┼─────────────┤
│ 101 │ Nguyễn Văn A    │ a@example.com   │ Hoạt động   │ 2024-01-15  │ [Sửa][Xem] │
│ 102 │ Trần Thị B      │ b@example.com   │ Ngưng       │ 2024-01-14  │ [Sửa][Xem] │
│ 103 │ Lê Văn C        │ c@example.com   │ Hoạt động   │ 2024-01-13  │ [Sửa][Xem] │
└─────┴─────────────────┴─────────────────┴─────────────┴─────────────┴─────────────┘

Chức năng:
- Tìm kiếm users theo tên/email
- Lọc theo trạng thái (active/inactive)
- Xuất danh sách Excel
- Tạo user mới (nếu có quyền)
```

**4.2.3 Quản Lý Đơn Hàng**
```
Danh Sách Đơn Hàng Thuộc Quyền:
┌─────────┬─────────────────┬─────────────┬─────────────┬─────────────┬─────────────┬─────────────┐
│ Mã ĐH   │ Khách Hàng      │ Tổng Tiền   │ Trạng Thái  │ Ngày Đặt    │ Vận Chuyển  │ Hành Động  │
├─────────┼─────────────────┼─────────────┼─────────────┼─────────────┼─────────────┼─────────────┤
│ #12345  │ Nguyễn Văn A    │ $250.00     │ Processing  │ 2024-01-15  │ -           │ [Sửa][Xem] │
│ #12346  │ Trần Thị B      │ $180.00     │ Shipped     │ 2024-01-14  │ VN123456    │ [Sửa][Xem] │
│ #12347  │ Lê Văn C        │ $320.00     │ Delivered   │ 2024-01-13  │ VN789012    │ [Xem][In]  │
│ #12348  │ Phạm Thị D      │ $95.00      │ Pending     │ 2024-01-12  │ -           │ [Sửa][Xem] │
└─────────┴─────────────────┴─────────────┴─────────────┴─────────────┴─────────────┴─────────────┘

Bộ Lọc và Tìm Kiếm:
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ [Tìm kiếm: ________________] [Trạng thái: All ▼] [Từ ngày: ______] [Đến ngày: ______] [Lọc] │
│                                                                                             │
│ Trạng thái: [All] [Pending] [Processing] [Shipped] [Delivered] [Cancelled] [Returned]      │
│ Tổng: 156 đơn hàng | Doanh thu: $45,230.00 | Hoa hồng: $2,261.50                          │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

Chi Tiết Đơn Hàng:
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ ĐƠN HÀNG #12345                                                                             │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ Khách hàng: Nguyễn Văn A (a@example.com)                                                   │
│ Ngày đặt: 15/01/2024 10:30                                                                 │
│ Trạng thái: Processing                                                                      │
│ Tổng tiền: $250.00                                                                         │
│                                                                                             │
│ SẢN PHẨM:                                                                                   │
│ • iPhone 15 Pro - Số lượng: 1 - Giá: $200.00                                              │
│ • Case iPhone - Số lượng: 2 - Giá: $25.00                                                 │
│                                                                                             │
│ ĐỊA CHỈ GIAO HÀNG:                                                                          │
│ 123 Nguyễn Văn Linh, Quận 7, TP.HCM                                                        │
│ SĐT: 0901234567                                                                            │
│                                                                                             │
│ THAO TÁC:                                                                                   │
│ [Cập nhật trạng thái ▼] [Thêm mã vận chuyển] [Thêm ghi chú] [In hóa đơn]                  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

Cập Nhật Trạng Thái:
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ CHUYỂN TRẠNG THÁI ĐƠN HÀNG #12345                                                          │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ Trạng thái hiện tại: Processing                                                            │
│ Chuyển sang: [Shipped ▼] (Có thể chọn: Shipped, Cancelled)                                │
│                                                                                             │
│ Mã vận chuyển: [________________]                                                           │
│ Ghi chú: [_________________________________________________]                               │
│                                                                                             │
│ Lý do hủy (nếu chọn Cancelled):                                                            │
│ [_________________________________________________________________]                         │
│                                                                                             │
│ [Cập nhật] [Hủy]                                                                           │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

**4.2.4 Báo Cáo Chi Tiết**
```
Báo Cáo Tổng Quan:
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│  THỐNG KÊ THÁNG NÀY:                                                                        │
│  ┌─────────────┬─────────────┬─────────────┬─────────────┬─────────────┬─────────────┐      │
│  │ Tổng ĐH     │ Pending     │ Processing  │ Shipped     │ Delivered   │ Cancelled   │      │
│  │     156     │     12      │     8       │     15      │     118     │     3       │      │
│  └─────────────┴─────────────┴─────────────┴─────────────┴─────────────┴─────────────┘      │
│                                                                                             │
│  DOANH THU:                                                                                 │
│  • Tổng doanh thu: $45,230.00                                                              │
│  • Doanh thu tháng này: $12,500.00                                                         │
│  • Hoa hồng kiếm được: $2,261.50 (5%)                                                     │
│  • Trung bình/đơn: $290.19                                                                 │
│                                                                                             │
│  TOP USERS THEO DOANH THU:                                                                  │
│  1. john@example.com - $2,500.00 (8 đơn) - Hoa hồng: $125.00                             │
│  2. mary@example.com - $1,800.00 (6 đơn) - Hoa hồng: $90.00                              │
│  3. peter@example.com - $1,200.00 (4 đơn) - Hoa hồng: $60.00                             │
│                                                                                             │
│  BIỂU ĐỒ DOANH THU 7 NGÀY GẦN NHẤT:                                                        │
│  $2000 ┤                                                                                   │
│  $1500 ┤     ██                                                                            │
│  $1000 ┤  ██ ██    ██                                                                      │
│  $500  ┤  ██ ██ ██ ██ ██                                                                   │
│  $0    └──┴──┴──┴──┴──┴──┴──                                                               │
│         T2 T3 T4 T5 T6 T7 CN                                                               │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

Báo Cáo Chi Tiết Đơn Hàng:
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│  PHÂN TÍCH TRẠNG THÁI ĐƠN HÀNG:                                                            │
│                                                                                             │
│  Pending (12 đơn):                                                                         │
│  • Cần xử lý trong 24h: 8 đơn                                                              │
│  • Quá hạn xử lý: 4 đơn ⚠️                                                                 │
│                                                                                             │
│  Processing (8 đơn):                                                                       │
│  • Đang chuẩn bị hàng: 5 đơn                                                               │
│  • Chờ vận chuyển: 3 đơn                                                                   │
│                                                                                             │
│  Shipped (15 đơn):                                                                         │
│  • Đang giao: 15 đơn                                                                       │
│  • Thời gian giao trung bình: 2.3 ngày                                                     │
│                                                                                             │
│  Delivered (118 đơn):                                                                      │
│  • Giao thành công: 118 đơn                                                                │
│  • Tỷ lệ hài lòng: 96.2%                                                                   │
│                                                                                             │
│  Cancelled (3 đơn):                                                                        │
│  • Khách hủy: 2 đơn                                                                        │
│  • Hết hàng: 1 đơn                                                                         │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 4.3 Dashboard Admin - Quản Lý Sub Admin

**4.3.1 Danh Sách Sub Admin**
```
┌─────┬─────────────────┬─────────────────┬─────────────┬─────────────┬─────────────┐
│ ID  │ Tên Sub Admin   │ Email           │ Mã Sub      │ Số Users    │ Hành Động  │
│     │                 │                 │ Admin       │ Quản Lý     │             │
├─────┼─────────────────┼─────────────────┼─────────────┼─────────────┼─────────────┤
│ 50  │ Nguyễn Admin A  │ admin1@ex.com   │ SA12345678  │ 150/1000    │ [Sửa][Xóa] │
│ 51  │ Trần Admin B    │ admin2@ex.com   │ SA87654321  │ 89/500      │ [Sửa][Xóa] │
│ 52  │ Lê Admin C      │ admin3@ex.com   │ SA11111111  │ 25/200      │ [Sửa][Xóa] │
└─────┴─────────────────┴─────────────────┴─────────────┴─────────────┴─────────────┘
```

**4.3.2 Tạo Sub Admin Mới**
```
Form Tạo Sub Admin:
┌─────────────────────────────────────────────────────────────┐
│  Thông Tin Cơ Bản:                                         │
│  • Tên: [________________]                                 │
│  • Email: [________________]                               │
│  • Password: [________________]                            │
│  • Mã Sub Admin: SA12345678 (tự động tạo)                 │
│                                                             │
│  Cấu Hình Quyền Hạn:                                       │
│  ☑ Quản lý users                                           │
│  ☑ Tạo users mới                                           │
│  ☑ Quản lý đơn hàng                                        │
│  ☐ Quản lý sản phẩm                                        │
│  ☑ Xem báo cáo                                             │
│                                                             │
│  Giới Hạn:                                                 │
│  • Số users tối đa: [1000]                                │
│  • Tỷ lệ hoa hồng (%): [5.00]                             │
│                                                             │
│  Thông Báo:                                                │
│  ☑ Nhận thông báo user mới                                 │
│  ☑ Nhận thông báo đơn hàng mới                             │
│  ☑ Tự động duyệt users mới                                 │
└─────────────────────────────────────────────────────────────┘
```

### 4.4 Phân Quyền Chi Tiết và Bảo Mật

**4.4.1 Matrix Phân Quyền Chi Tiết**
```
┌─────────────────────────────┬─────────┬─────────────┬─────────┐
│ Chức Năng                   │ Admin   │ Sub Admin   │ User    │
├─────────────────────────────┼─────────┼─────────────┼─────────┤
│ Tạo Sub Admin               │    ✓    │      ✗      │    ✗    │
│ Quản lý Sub Admin           │    ✓    │      ✗      │    ✗    │
│ Xem tất cả users            │    ✓    │      ✗      │    ✗    │
│ Quản lý users riêng         │    ✓    │      ✓      │    ✗    │
│ Tạo users mới               │    ✓    │   Tùy cấu   │    ✗    │
│ Quản lý sản phẩm            │    ✓    │   Tùy cấu   │    ✗    │
│ Quản lý đơn hàng            │    ✓    │   Tùy cấu   │    ✗    │
│ ├── Xem đơn hàng            │    ✓    │      ✓      │    ✗    │
│ ├── Cập nhật trạng thái     │    ✓    │      ✓      │    ✗    │
│ ├── In hóa đơn              │    ✓    │      ✓      │    ✗    │
│ ├── Thêm mã vận chuyển      │    ✓    │      ✓      │    ✗    │
│ ├── Hủy đơn hàng            │    ✓    │      ✓      │    ✗    │
│ ├── Xử lý hoàn trả          │    ✓    │   Tùy cấu   │    ✗    │
│ └── Hoàn tiền               │    ✓    │   Tùy cấu   │    ✗    │
│ Xem báo cáo                 │    ✓    │   Tùy cấu   │    ✗    │
│ ├── Báo cáo doanh thu       │    ✓    │      ✓      │    ✗    │
│ ├── Báo cáo đơn hàng        │    ✓    │      ✓      │    ✗    │
│ └── Báo cáo hoa hồng        │    ✓    │      ✓      │    ✗    │
│ Cấu hình hệ thống           │    ✓    │      ✗      │    ✗    │
│ Mua sắm                     │    ✗    │      ✗      │    ✓    │
│ Xem đơn hàng cá nhân        │    ✗    │      ✗      │    ✓    │
└─────────────────────────────┴─────────┴─────────────┴─────────┘
```

**4.4.2 Middleware Security Flow**
```
Request → Auth Check → Role Check → Permission Check → Action

Ví dụ: Sub Admin truy cập /sub-admin/users/edit/123
1. Auth Check: User đã login?
2. Role Check: User có role = 'sub_admin'?
3. Permission Check: User 123 có thuộc quyền quản lý không?
4. Action: Cho phép sửa user hoặc từ chối
```

**4.4.3 Data Isolation (Cách ly dữ liệu)**
```php
// Sub Admin chỉ thấy users thuộc quyền
$users = auth()->user()->managedUsers(); // WHERE parent_sub_admin_id = current_user_id

// Sub Admin chỉ thấy orders của users thuộc quyền
$orders = Order::whereHas('user', function($query) {
    $query->where('parent_sub_admin_id', auth()->id());
});

// Sub Admin chỉ thấy báo cáo của users thuộc quyền
$revenue = Order::join('users', 'orders.user_id', '=', 'users.id')
               ->where('users.parent_sub_admin_id', auth()->id())
               ->sum('orders.total_amount');
```

## 5. BẢO MẬT VÀ VALIDATION CHI TIẾT

### 5.1 Validation Rules Toàn Diện

**5.1.1 Đăng Ký User**
```php
$rules = [
    'name' => 'required|string|min:2|max:255',
    'email' => 'required|email|unique:users,email|max:255',
    'password' => 'required|string|min:6|confirmed',
    'sub_admin_code' => [
        'nullable',
        'string',
        'size:10', // Mã Sub Admin có độ dài cố định
        'regex:/^SA[A-Z0-9]{8}$/', // Format: SA + 8 ký tự
        'exists:users,sub_admin_code',
        function ($attribute, $value, $fail) {
            if ($value) {
                $subAdmin = User::where('sub_admin_code', $value)
                              ->where('role', 'sub_admin')
                              ->where('status', 'active')
                              ->first();
                
                if (!$subAdmin) {
                    $fail('Mã Sub Admin không hợp lệ hoặc đã bị vô hiệu hóa.');
                }
                
                // Kiểm tra giới hạn users
                $currentCount = $subAdmin->getManagedUsersCount();
                $maxAllowed = $subAdmin->subAdminSettings->max_users_allowed;
                
                if ($currentCount >= $maxAllowed) {
                    $fail('Sub Admin này đã đạt giới hạn số lượng users (' . $maxAllowed . ').');
                }
            }
        }
    ]
];
```

**5.1.2 Tạo Sub Admin**
```php
$rules = [
    'name' => 'required|string|min:2|max:255',
    'email' => 'required|email|unique:users,email|max:255',
    'password' => 'required|string|min:8|confirmed',
    'max_users_allowed' => 'required|integer|min:1|max:10000',
    'commission_rate' => 'required|numeric|min:0|max:100',
    'can_manage_products' => 'boolean',
    'can_manage_orders' => 'boolean',
    'can_view_reports' => 'boolean',
    'can_manage_users' => 'boolean',
    'can_create_users' => 'boolean',
];
```

**5.1.3 Cập Nhật User (Sub Admin)**
```php
$rules = [
    'name' => 'required|string|min:2|max:255',
    'email' => 'required|email|max:255|unique:users,email,' . $userId,
    'status' => 'required|in:active,inactive',
    'password' => 'nullable|string|min:6|confirmed', // Optional khi update
];

// Custom validation: Chỉ cho phép Sub Admin sửa users thuộc quyền
$this->validate($request, $rules);

$user = auth()->user()->managedUsers()->findOrFail($userId);
if (!$user) {
    abort(403, 'Bạn không có quyền sửa user này');
}
```

### 5.2 Security Measures Chi Tiết

**5.2.1 Mã Sub Admin Security**
```php
class User extends Authenticatable 
{
    public function generateSubAdminCode()
    {
        do {
            // Format: SA + 8 ký tự random (A-Z, 0-9)
            $code = 'SA' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (self::where('sub_admin_code', $code)->exists());
        
        return $code;
    }
    
    public function regenerateSubAdminCode()
    {
        $this->sub_admin_code = $this->generateSubAdminCode();
        $this->save();
        
        // Log activity
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['old_code' => $this->getOriginal('sub_admin_code')])
            ->log('Sub Admin code regenerated');
            
        return $this->sub_admin_code;
    }
}
```

**5.2.2 Middleware Security Layers**
```php
// app/Http/Middleware/SubAdminPermission.php
class SubAdminPermission
{
    public function handle($request, Closure $next, $permission = null)
    {
        $user = auth()->user();
        
        if (!$user || !$user->isSubAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $settings = $user->subAdminSettings;
        
        // Kiểm tra quyền hạn cụ thể
        switch ($permission) {
            case 'manage_products':
                if (!$settings->can_manage_products) {
                    abort(403, 'Bạn không có quyền quản lý sản phẩm');
                }
                break;
                
            case 'manage_orders':
                if (!$settings->can_manage_orders) {
                    abort(403, 'Bạn không có quyền quản lý đơn hàng');
                }
                break;
                
            case 'create_users':
                if (!$settings->can_create_users) {
                    abort(403, 'Bạn không có quyền tạo users mới');
                }
                break;
                
            case 'view_reports':
                if (!$settings->can_view_reports) {
                    abort(403, 'Bạn không có quyền xem báo cáo');
                }
                break;
        }
        
        return $next($request);
    }
}
```

**5.2.3 Data Access Control**
```php
// app/Http/Controllers/SubAdminController.php
class SubAdminController extends Controller
{
    protected function authorizeUserAccess($userId)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->isSubAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $user = $subAdmin->managedUsers()->find($userId);
        
        if (!$user) {
            abort(404, 'User không tồn tại hoặc bạn không có quyền truy cập');
        }
        
        return $user;
    }
    
    public function editUser($userId)
    {
        $user = $this->authorizeUserAccess($userId);
        return view('backend.sub-admin.users.edit', compact('user'));
    }
}
```

**5.2.4 Rate Limiting**
```php
// routes/web.php
Route::group([
    'prefix' => '/sub-admin', 
    'middleware' => ['auth', 'sub_admin', 'throttle:60,1']
], function () {
    // Sub Admin routes với rate limit 60 requests/minute
});

// Đặc biệt cho tạo users
Route::post('/sub-admin/users', [SubAdminController::class, 'storeUser'])
     ->middleware(['auth', 'sub_admin', 'throttle:10,1']); // 10 users/minute max
```

**5.2.5 Audit Logging**
```php
// app/Traits/LogsActivity.php
trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            if ($model instanceof User && $model->role === 'user' && $model->parent_sub_admin_id) {
                activity()
                    ->performedOn($model)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'user_email' => $model->email,
                        'sub_admin_id' => $model->parent_sub_admin_id,
                        'referral_code' => $model->referral_code
                    ])
                    ->log('User created by Sub Admin');
            }
        });
        
        static::updated(function ($model) {
            if ($model instanceof User && $model->isDirty(['status', 'role'])) {
                activity()
                    ->performedOn($model)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'old' => $model->getOriginal(),
                        'new' => $model->getAttributes()
                    ])
                    ->log('User status/role changed');
            }
        });
    }
}
```

### 5.3 Input Sanitization và XSS Protection

**5.3.1 Request Sanitization**
```php
// app/Http/Requests/CreateUserRequest.php
class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->isSubAdmin() && 
               auth()->user()->subAdminSettings->can_create_users;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
    
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags(trim($this->name)),
            'email' => strtolower(trim($this->email)),
        ]);
    }
}
```

**5.3.2 Output Escaping trong Views**
```blade
{{-- Luôn sử dụng {{ }} thay vì {!! !!} --}}
<td>{{ $user->name }}</td>
<td>{{ $user->email }}</td>

{{-- Nếu cần HTML, sử dụng htmlspecialchars --}}
<td>{!! htmlspecialchars($user->description, ENT_QUOTES, 'UTF-8') !!}</td>
```

## 6. TESTING STRATEGY CHI TIẾT

### 6.1 Unit Tests

**6.1.1 User Model Tests**
```php
// tests/Unit/UserModelTest.php
class UserModelTest extends TestCase
{
    public function test_user_can_have_sub_admin_parent()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $user = User::factory()->create(['parent_sub_admin_id' => $subAdmin->id]);
        
        $this->assertEquals($subAdmin->id, $user->parentSubAdmin->id);
        $this->assertTrue($subAdmin->managedUsers->contains($user));
    }
    
    public function test_sub_admin_code_generation()
    {
        $user = new User();
        $code = $user->generateSubAdminCode();
        
        $this->assertMatchesRegularExpression('/^SA[A-Z0-9]{8}$/', $code);
        $this->assertEquals(10, strlen($code));
    }
    
    public function test_user_permission_checks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $user = User::factory()->create(['role' => 'user']);
        
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($subAdmin->isSubAdmin());
        $this->assertTrue($user->isUser());
    }
}
```

**6.1.2 Middleware Tests**
```php
// tests/Unit/SubAdminMiddlewareTest.php
class SubAdminMiddlewareTest extends TestCase
{
    public function test_sub_admin_middleware_allows_sub_admin()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $this->actingAs($subAdmin);
        
        $request = Request::create('/sub-admin/dashboard');
        $middleware = new SubAdmin();
        
        $response = $middleware->handle($request, function () {
            return response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }
    
    public function test_sub_admin_middleware_blocks_regular_user()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        
        $request = Request::create('/sub-admin/dashboard');
        $middleware = new SubAdmin();
        
        $response = $middleware->handle($request, function () {
            return response('OK');
        });
        
        $this->assertEquals(302, $response->getStatusCode());
    }
}
```

### 6.2 Feature Tests

**6.2.1 Registration with Sub Admin Code**
```php
// tests/Feature/RegistrationTest.php
class RegistrationTest extends TestCase
{
    public function test_user_can_register_with_valid_sub_admin_code()
    {
        $subAdmin = User::factory()->create([
            'role' => 'sub_admin',
            'sub_admin_code' => 'SA12345678',
            'status' => 'active'
        ]);
        
        SubAdminSettings::factory()->create([
            'user_id' => $subAdmin->id,
            'max_users_allowed' => 100
        ]);
        
        $response = $this->post('/user/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sub_admin_code' => 'SA12345678'
        ]);
        
        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'parent_sub_admin_id' => $subAdmin->id,
            'referral_code' => 'SA12345678'
        ]);
    }
    
    public function test_registration_fails_with_invalid_sub_admin_code()
    {
        $response = $this->post('/user/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sub_admin_code' => 'INVALID123'
        ]);
        
        $response->assertSessionHasErrors('sub_admin_code');
    }
}
```

**6.2.2 Sub Admin Dashboard Tests**
```php
// tests/Feature/SubAdminDashboardTest.php
class SubAdminDashboardTest extends TestCase
{
    public function test_sub_admin_can_view_dashboard()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        SubAdminSettings::factory()->create(['user_id' => $subAdmin->id]);
        
        $response = $this->actingAs($subAdmin)->get('/sub-admin');
        
        $response->assertStatus(200);
        $response->assertViewIs('backend.sub-admin.dashboard');
    }
    
    public function test_sub_admin_can_only_see_managed_users()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $otherSubAdmin = User::factory()->create(['role' => 'sub_admin']);
        
        $managedUser = User::factory()->create(['parent_sub_admin_id' => $subAdmin->id]);
        $otherUser = User::factory()->create(['parent_sub_admin_id' => $otherSubAdmin->id]);
        
        $response = $this->actingAs($subAdmin)->get('/sub-admin/users');
        
        $response->assertStatus(200);
        $response->assertSee($managedUser->email);
        $response->assertDontSee($otherUser->email);
    }
}
```

### 6.3 Integration Tests

**6.3.1 Complete User Journey Test**
```php
// tests/Feature/CompleteUserJourneyTest.php
class CompleteUserJourneyTest extends TestCase
{
    public function test_complete_sub_admin_user_management_flow()
    {
        // 1. Admin tạo Sub Admin
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->post('/admin/sub-admins', [
            'name' => 'Sub Admin Test',
            'email' => 'subadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'max_users_allowed' => 100,
            'commission_rate' => 5.00,
            'can_manage_users' => true,
            'can_create_users' => true,
        ]);
        
        $subAdmin = User::where('email', 'subadmin@test.com')->first();
        $this->assertNotNull($subAdmin);
        $this->assertEquals('sub_admin', $subAdmin->role);
        $this->assertNotNull($subAdmin->sub_admin_code);
        
        // 2. User đăng ký với mã Sub Admin
        $response = $this->post('/user/register', [
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sub_admin_code' => $subAdmin->sub_admin_code
        ]);
        
        $user = User::where('email', 'user@test.com')->first();
        $this->assertEquals($subAdmin->id, $user->parent_sub_admin_id);
        
        // 3. Sub Admin quản lý user
        $response = $this->actingAs($subAdmin)->get('/sub-admin/users');
        $response->assertSee($user->email);
        
        // 4. Sub Admin sửa user
        $response = $this->actingAs($subAdmin)->put("/sub-admin/users/{$user->id}", [
            'name' => 'Updated User Name',
            'email' => $user->email,
            'status' => 'active'
        ]);
        
        $user->refresh();
        $this->assertEquals('Updated User Name', $user->name);
    }
}
```

### 6.4 Performance Tests

```php
// tests/Feature/PerformanceTest.php
class PerformanceTest extends TestCase
{
    public function test_sub_admin_dashboard_performance_with_many_users()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        SubAdminSettings::factory()->create(['user_id' => $subAdmin->id]);
        
        // Tạo 1000 users thuộc Sub Admin
        User::factory()->count(1000)->create(['parent_sub_admin_id' => $subAdmin->id]);
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($subAdmin)->get('/sub-admin/users');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime, 'Dashboard should load in under 2 seconds');
    }
}
```

## 7. DEPLOYMENT CHECKLIST CHI TIẾT

### 7.1 Pre-Deployment

**7.1.1 Code Review Checklist**
```
☐ Tất cả migrations đã được test
☐ Middleware đã được test với các role khác nhau
☐ Validation rules đã được kiểm tra
☐ Security measures đã được implement
☐ Error handling đã được thêm vào
☐ Logging đã được cấu hình
☐ Performance đã được test với data lớn
```

**7.1.2 Database Backup**
```bash
# Backup database trước khi migrate
mysqldump -u username -p database_name > backup_before_sub_admin_$(date +%Y%m%d_%H%M%S).sql
```

### 7.2 Deployment Steps

**7.2.1 Migration và Seeding**
```bash
# 1. Chạy migrations
php artisan migrate --force

# 2. Tạo Sub Admin Settings cho các Sub Admin hiện có (nếu có)
php artisan db:seed --class=SubAdminSettingsSeeder

# 3. Tạo sample data (development only)
php artisan db:seed --class=SubAdminSampleDataSeeder
```

**7.2.2 Cache và Config**
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**7.2.3 Permissions và Storage**
```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
```

### 7.3 Post-Deployment Verification

**7.3.1 Functional Tests**
```bash
# Chạy test suite
php artisan test --filter=SubAdmin

# Test specific features
curl -X POST http://your-domain.com/user/register \
  -d "name=Test&email=test@test.com&password=123456&password_confirmation=123456&sub_admin_code=SA12345678"
```

**7.3.2 Performance Monitoring**
```bash
# Monitor database queries
tail -f storage/logs/laravel.log | grep "SELECT"

# Check response times
curl -w "@curl-format.txt" -o /dev/null -s "http://your-domain.com/sub-admin/users"
```

## 8. MAINTENANCE & MONITORING

### 8.1 Monitoring Dashboard

**8.1.1 Key Metrics**
```php
// app/Console/Commands/SubAdminMetrics.php
class SubAdminMetrics extends Command
{
    public function handle()
    {
        $metrics = [
            'total_sub_admins' => User::where('role', 'sub_admin')->count(),
            'active_sub_admins' => User::where('role', 'sub_admin')->where('status', 'active')->count(),
            'total_managed_users' => User::whereNotNull('parent_sub_admin_id')->count(),
            'avg_users_per_sub_admin' => User::whereNotNull('parent_sub_admin_id')->count() / max(1, User::where('role', 'sub_admin')->count()),
            'sub_admins_at_limit' => $this->getSubAdminsAtLimit(),
        ];
        
        Log::info('Sub Admin Metrics', $metrics);
        return $metrics;
    }
    
    private function getSubAdminsAtLimit()
    {
        return User::where('role', 'sub_admin')
            ->whereHas('subAdminSettings', function($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM users WHERE parent_sub_admin_id = users.id) >= max_users_allowed');
            })->count();
    }
}
```

### 8.2 Automated Maintenance Tasks

**8.2.1 Cleanup Tasks**
```php
// app/Console/Commands/CleanupSubAdminData.php
class CleanupSubAdminData extends Command
{
    public function handle()
    {
        // Xóa Sub Admin codes không sử dụng trong 30 ngày
        $inactiveSubAdmins = User::where('role', 'sub_admin')
            ->where('status', 'inactive')
            ->where('updated_at', '<', now()->subDays(30))
            ->get();
            
        foreach ($inactiveSubAdmins as $subAdmin) {
            // Chuyển users về không có parent
            $subAdmin->managedUsers()->update(['parent_sub_admin_id' => null]);
            
            // Xóa settings
            $subAdmin->subAdminSettings()->delete();
            
            // Xóa stats
            $subAdmin->subAdminStats()->delete();
            
            $this->info("Cleaned up inactive Sub Admin: {$subAdmin->email}");
        }
    }
}
```

## 9. FUTURE ENHANCEMENTS VÀ ROADMAP

### 9.1 Phase 2 Features

**9.1.1 Multi-Level Sub Admin**
```
Sub Admin Level 1
├── Sub Admin Level 2
│   ├── User A
│   └── User B
└── User C

Cấu trúc:
- parent_sub_admin_id: ID của Sub Admin cấp trên
- sub_admin_level: 1, 2, 3... (tối đa 3 cấp)
- inheritance_permissions: Kế thừa quyền từ cấp trên
```

**9.1.2 Commission System**
```php
// Tự động tính hoa hồng khi order completed
class OrderCompletedListener
{
    public function handle(OrderCompleted $event)
    {
        $order = $event->order;
        $user = $order->user;
        
        if ($user->parentSubAdmin) {
            $commission = $order->total * ($user->parentSubAdmin->subAdminSettings->commission_rate / 100);
            
            WalletTransaction::create([
                'user_id' => $user->parentSubAdmin->id,
                'type' => 'commission',
                'amount' => $commission,
                'description' => "Commission from order #{$order->id}",
                'reference_id' => $order->id,
            ]);
        }
    }
}
```

### 9.3 Routes Configuration cho Sub Admin Orders

**9.3.1 Sub Admin Routes**
```php
// routes/web.php - Sub Admin Order Management Routes
Route::group([
    'prefix' => 'sub-admin',
    'middleware' => ['auth', 'sub_admin'],
    'as' => 'sub-admin.'
], function () {
    
    // Dashboard
    Route::get('/', [SubAdminController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', SubAdminController::class, [
        'only' => ['index', 'show', 'create', 'store', 'edit', 'update']
    ])->names([
        'index' => 'users',
        'show' => 'users.show',
        'create' => 'users.create',
        'store' => 'users.store',
        'edit' => 'users.edit',
        'update' => 'users.update',
    ]);
    
    // Order Management
    Route::group(['middleware' => 'sub_admin_permission:manage_orders'], function () {
        Route::get('/orders', [SubAdminController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [SubAdminController::class, 'showOrder'])->name('orders.show');
        Route::get('/orders/{id}/edit', [SubAdminController::class, 'editOrder'])->name('orders.edit');
        Route::put('/orders/{id}', [SubAdminController::class, 'updateOrder'])->name('orders.update');
        Route::get('/orders/{id}/invoice', [SubAdminController::class, 'printInvoice'])->name('orders.invoice');
        Route::post('/orders/{id}/tracking', [SubAdminController::class, 'updateTracking'])->name('orders.tracking');
    });
    
    // Reports
    Route::group(['middleware' => 'sub_admin_permission:view_reports'], function () {
        Route::get('/reports', [SubAdminController::class, 'reports'])->name('reports');
        Route::get('/reports/orders', [SubAdminController::class, 'orderReports'])->name('reports.orders');
        Route::get('/reports/commission', [SubAdminController::class, 'commissionReports'])->name('reports.commission');
        Route::get('/reports/export', [SubAdminController::class, 'exportReports'])->name('reports.export');
    });
    
    // Profile Management
    Route::get('/profile', [SubAdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [SubAdminController::class, 'updateProfile'])->name('profile.update');
});
```

**9.3.2 API Routes cho Mobile/AJAX**
```php
// routes/api.php - Sub Admin API
Route::group([
    'prefix' => 'v1/sub-admin',
    'middleware' => ['auth:sanctum', 'sub_admin']
], function () {
    
    // Dashboard Stats
    Route::get('/stats', [ApiSubAdminController::class, 'getStats']);
    Route::get('/recent-activities', [ApiSubAdminController::class, 'getRecentActivities']);
    
    // Orders API
    Route::group(['middleware' => 'sub_admin_permission:manage_orders'], function () {
        Route::get('/orders', [ApiSubAdminController::class, 'getOrders']);
        Route::get('/orders/{id}', [ApiSubAdminController::class, 'getOrder']);
        Route::patch('/orders/{id}/status', [ApiSubAdminController::class, 'updateOrderStatus']);
        Route::post('/orders/{id}/notes', [ApiSubAdminController::class, 'addOrderNote']);
    });
    
    // Users API
    Route::get('/users', [ApiSubAdminController::class, 'getUsers']);
    Route::get('/users/{id}/orders', [ApiSubAdminController::class, 'getUserOrders']);
    
    // Real-time notifications
    Route::get('/notifications', [ApiSubAdminController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [ApiSubAdminController::class, 'markNotificationRead']);
});
```

### 9.2 API Integration

**9.2.1 RESTful API cho Mobile App**
```php
// routes/api.php
Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'sub-admin', 'middleware' => 'sub_admin'], function () {
        Route::get('/dashboard', [ApiSubAdminController::class, 'dashboard']);
        Route::get('/users', [ApiSubAdminController::class, 'users']);
        Route::post('/users', [ApiSubAdminController::class, 'createUser']);
        Route::get('/stats', [ApiSubAdminController::class, 'stats']);
    });
});
```

### 9.3 Advanced Analytics

**9.3.1 Real-time Dashboard**
```javascript
// resources/js/sub-admin-dashboard.js
class SubAdminDashboard {
    constructor() {
        this.initWebSocket();
        this.initCharts();
    }
    
    initWebSocket() {
        Echo.private(`sub-admin.${userId}`)
            .listen('NewUserRegistered', (e) => {
                this.updateUserCount(e.user);
                this.showNotification('New user registered: ' + e.user.email);
            })
            .listen('NewOrderPlaced', (e) => {
                this.updateRevenueChart(e.order);
            });
    }
}
```

---

## KẾT LUẬN

Hệ thống Sub Admin này được thiết kế để:

1. **Mở rộng khả năng quản lý**: Cho phép phân quyền quản lý users cho nhiều Sub Admin
2. **Bảo mật cao**: Với nhiều lớp bảo mật và kiểm tra quyền hạn
3. **Linh hoạt**: Cấu hình quyền hạn chi tiết cho từng Sub Admin
4. **Scalable**: Có thể mở rộng để hỗ trợ hàng nghìn Sub Admin và users
5. **Audit-ready**: Đầy đủ logging và tracking cho compliance

**Lợi ích chính:**
- Giảm tải cho Admin chính
- Tăng hiệu quả quản lý users
- Tạo cơ hội kinh doanh cho Sub Admin (commission)
- Cải thiện trải nghiệm người dùng với referral system

**Tài liệu này sẽ được cập nhật liên tục khi triển khai và phát triển thêm các tính năng mới.**