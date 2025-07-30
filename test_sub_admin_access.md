# Test Sub Admin Access Blocking

## Mô tả
Đã implement hệ thống chặn Sub Admin truy cập vào các view frontend của user.

## Các thay đổi đã thực hiện:

### 1. Tạo Middleware mới
- **File**: `app/Http/Middleware/BlockSubAdminFromFrontend.php`
- **Chức năng**: Chặn user có role 'sub_admin' truy cập vào các trang frontend
- **Redirect**: Chuyển hướng về `sub-admin.dashboard` nếu sub_admin cố truy cập

### 2. Đăng ký Middleware
- **File**: `app/Http/Kernel.php`
- **Key**: `block_sub_admin_frontend`
- **Class**: `\App\Http\Middleware\BlockSubAdminFromFrontend::class`

### 3. Apply Middleware vào Routes
**Các route đã được bảo vệ:**
- Trang chủ: `/`
- Tất cả frontend routes: `/home`, `/about-us`, `/contact`, etc.
- Product routes: `/product-detail`, `/product-cat`, `/product-brand`
- Cart & Wishlist: `/cart`, `/checkout`, `/wishlist`
- Blog routes: `/blog`, `/blog-detail`
- User dashboard: `/user/*`
- Lucky Wheel frontend: `/wheel/*`
- Wallet user routes: `/wallet/*`

### 4. Cập nhật User Middleware  
- **File**: `app/Http/Middleware/User.php`
- **Cải tiến**: Thêm kiểm tra role sub_admin và redirect

## Cách test:

### Test 1: User bình thường
1. Login với user có role 'user'
2. Truy cập `/` - Phải hiển thị bình thường
3. Truy cập `/user` - Phải hiển thị dashboard user

### Test 2: Admin
1. Login với user có role 'admin'  
2. Truy cập `/` - Phải hiển thị bình thường
3. Truy cập `/admin` - Phải hiển thị admin dashboard

### Test 3: Sub Admin (Chặn)
1. Login với user có role 'sub_admin'
2. Truy cập `/` - Phải redirect về `/sub-admin` với thông báo lỗi
3. Truy cập `/user` - Phải redirect về `/sub-admin` với thông báo lỗi
4. Truy cập `/sub-admin` - Phải hiển thị bình thường

## Thông báo lỗi
```
"Sub Admin không được phép truy cập vào các trang user"
```

## Notes
- Sub Admin vẫn có thể truy cập các route thuộc prefix `/sub-admin`
- Admin vẫn có thể truy cập tất cả routes
- User bình thường không bị ảnh hưởng