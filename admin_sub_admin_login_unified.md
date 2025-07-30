# Admin & Sub Admin Unified Login System

## Mô tả
Đã cập nhật hệ thống login để Admin và Sub Admin cùng đăng nhập qua route `/login`.

## Các thay đổi đã thực hiện:

### 1. Cập nhật LoginController
**File**: `app/Http/Controllers/Auth/LoginController.php`

#### Thay đổi credentials method:
- **Trước**: Chỉ cho phép `role = 'admin'`
- **Sau**: Cho phép tất cả role nhưng sẽ filter trong `authenticated()` method

#### Thêm redirectTo() method:
- **Admin**: Redirect đến `/admin` (route: `admin`)
- **Sub Admin**: Redirect đến `/sub-admin` (route: `sub-admin.dashboard`)
- **User thường**: Logout và hiển thị lỗi

#### Thêm authenticated() method:
- Kiểm tra role trước khi cho đăng nhập thành công
- Chỉ cho phép `admin` và `sub_admin`
- User thường sẽ bị logout ngay lập tức

### 2. Cập nhật Middleware
**Files**: `app/Http/Middleware/Admin.php` và `app/Http/Middleware/SubAdmin.php`
- Thay đổi redirect từ `route('login.form')` sang `route('login')`
- Đồng nhất hệ thống login

### 3. Cập nhật Login View
**File**: `resources/views/auth/login.blade.php`
- Thay đổi title thành "Admin & Sub Admin Login"
- Thêm thông báo "Đăng nhập dành cho Admin và Sub Admin"

## Cách hoạt động:

### Route `/login` (GET)
- Hiển thị form login với giao diện admin
- Dành cho Admin và Sub Admin

### Route `/login` (POST)
- **Admin**: Đăng nhập thành công → Redirect `/admin`
- **Sub Admin**: Đăng nhập thành công → Redirect `/sub-admin`
- **User thường**: Đăng nhập → Logout ngay lập tức → Hiển thị lỗi

### Route `/user/login` (Vẫn giữ nguyên)
- Dành cho user thông thường
- Sử dụng `FrontendController::loginSubmit()`

## Test Cases:

### Test 1: Admin Login qua /login
```
Email: admin@example.com (role: admin)
Password: xxxxxx
Expected: Redirect đến /admin với message "Đăng nhập thành công"
```

### Test 2: Sub Admin Login qua /login
```
Email: subadmin@example.com (role: sub_admin)  
Password: xxxxxx
Expected: Redirect đến /sub-admin với message "Đăng nhập thành công"
```

### Test 3: User thường cố login qua /login
```
Email: user@example.com (role: user)
Password: xxxxxx
Expected: Logout ngay lập tức, hiển thị lỗi "Trang này chỉ dành cho Admin và Sub Admin"
```

### Test 4: User thường login qua /user/login (vẫn bình thường)
```
Email: user@example.com (role: user)
Password: xxxxxx
Expected: Redirect đến / (home) với message "Successfully login"
```

## Lưu ý:
- Admin và Sub Admin giờ có thể chọn đăng nhập qua `/login` hoặc `/user/login`
- User thường CHỈ có thể đăng nhập qua `/user/login`
- Hệ thống vẫn tương thích ngược với cách login cũ
- Middleware đã được đồng bộ để redirect về `/login` thống nhất