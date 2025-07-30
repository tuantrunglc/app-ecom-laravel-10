# Hệ Thống Ví Điện Tử - Laravel 10

## Tổng Quan
Hệ thống ví điện tử đơn giản cho phép user nạp tiền và rút tiền thông qua admin duyệt thủ công.

## Tính Năng Đã Triển Khai

### 1. Database
- ✅ Thêm cột `wallet_balance` vào bảng `users`
- ✅ Bảng `wallet_transactions` (lịch sử giao dịch)
- ✅ Bảng `withdrawal_requests` (yêu cầu rút tiền)

### 2. Models
- ✅ Cập nhật User model với wallet relationships
- ✅ WalletTransaction model
- ✅ WithdrawalRequest model

### 3. Controllers
- ✅ WalletController (user): index, depositForm, deposit, withdrawForm, withdraw
- ✅ Admin\WalletController: deposits, withdrawals, approve, reject

### 4. Views
- ✅ user/wallet/index.blade.php (trang chính ví)
- ✅ user/wallet/deposit.blade.php (form nạp tiền)
- ✅ user/wallet/withdraw.blade.php (form rút tiền)
- ✅ admin/wallet/deposits.blade.php (admin quản lý nạp tiền)
- ✅ admin/wallet/withdrawals.blade.php (admin quản lý rút tiền)

### 5. Routes
- ✅ User routes với middleware auth
- ✅ Admin routes với middleware auth, admin

### 6. Navigation
- ✅ Thêm menu "Ví Của Tôi" vào user sidebar
- ✅ Thêm menu "Quản Lý Ví" vào admin sidebar

## Cách Sử Dụng

### Cho User:
1. **Truy cập ví**: Vào menu "Ví Của Tôi" trong sidebar
2. **Nạp tiền**: 
   - Click "Nạp Tiền" → Nhập số tiền (10,000 - 50,000,000 VNĐ)
   - CSKH sẽ liên hệ để hướng dẫn chuyển khoản
   - Admin duyệt → Tiền được cộng vào ví
3. **Rút tiền**:
   - Click "Rút Tiền" → Nhập thông tin ngân hàng
   - Admin xử lý trong 1-3 ngày làm việc

### Cho Admin:
1. **Quản lý nạp tiền**: Admin → Quản Lý Ví → Yêu Cầu Nạp Tiền
2. **Quản lý rút tiền**: Admin → Quản Lý Ví → Yêu Cầu Rút Tiền
3. **Duyệt/Từ chối**: Click nút tương ứng và nhập ghi chú

## URLs Chính

### User:
- `/wallet` - Trang ví chính
- `/wallet/deposit` - Form nạp tiền
- `/wallet/withdraw` - Form rút tiền

### Admin:
- `/admin/wallet/deposits` - Quản lý nạp tiền
- `/admin/wallet/withdrawals` - Quản lý rút tiền

## Database Schema

### Bảng `users` (thêm cột):
```sql
wallet_balance DECIMAL(15,2) DEFAULT 0.00
```

### Bảng `wallet_transactions`:
```sql
id, user_id, type (deposit/withdraw), amount, balance_before, balance_after,
description, status (pending/completed/rejected), admin_note, timestamps
```

### Bảng `withdrawal_requests`:
```sql
id, user_id, amount, bank_name, bank_account, account_name,
status (pending/completed/rejected), admin_note, timestamps
```

## Validation Rules

### Nạp tiền:
- Số tiền: 10,000 - 50,000,000 VNĐ
- Ghi chú: Tối đa 500 ký tự (tùy chọn)

### Rút tiền:
- Số tiền: Tối thiểu 50,000 VNĐ, không vượt quá số dư
- Thông tin ngân hàng: Bắt buộc và hợp lệ

## Security Features
- ✅ CSRF Protection
- ✅ Input Validation
- ✅ Database Transactions
- ✅ Authentication & Authorization
- ✅ XSS Protection

## Workflow

### Nạp Tiền:
1. User tạo yêu cầu → Status: pending
2. CSKH liên hệ user
3. User chuyển khoản
4. Admin duyệt → Cộng tiền vào ví → Status: completed

### Rút Tiền:
1. User tạo yêu cầu → Status: pending
2. Admin kiểm tra và chuyển khoản
3. Admin duyệt → Trừ tiền từ ví → Status: completed

## Files Đã Tạo/Sửa

### Migrations:
- `2025_07_28_034528_add_wallet_balance_to_users_table.php`
- `2025_07_28_034741_create_wallet_transactions_table.php`
- `2025_07_28_034831_create_withdrawal_requests_table.php`

### Models:
- `app/User.php` (updated)
- `app/Models/WalletTransaction.php`
- `app/Models/WithdrawalRequest.php`

### Controllers:
- `app/Http/Controllers/WalletController.php`
- `app/Http/Controllers/Admin/WalletController.php`

### Views:
- `resources/views/user/wallet/index.blade.php`
- `resources/views/user/wallet/deposit.blade.php`
- `resources/views/user/wallet/withdraw.blade.php`
- `resources/views/admin/wallet/deposits.blade.php`
- `resources/views/admin/wallet/withdrawals.blade.php`

### Routes:
- `routes/web.php` (updated)

### Navigation:
- `resources/views/user/layouts/sidebar.blade.php` (updated)
- `resources/views/backend/layouts/sidebar.blade.php` (updated)

## Testing
Hệ thống đã được test cơ bản:
- ✅ Migrations chạy thành công
- ✅ Routes được tạo đúng
- ✅ Database connection hoạt động
- ✅ User model có wallet_balance = 0.00

## Lưu Ý
- Hệ thống không tích hợp payment gateway
- Tất cả giao dịch được xử lý thủ công
- Admin cần chuyển khoản thực tế khi duyệt rút tiền
- Hệ thống sử dụng Bootstrap UI có sẵn của project

## Hỗ Trợ
Nếu gặp lỗi, hãy kiểm tra:
1. Database connection
2. Middleware 'admin' đã được đăng ký
3. User đã login và có quyền admin
4. Cache đã được clear

---
**Tạo bởi**: AI Assistant  
**Ngày**: 28/07/2025  
**Version**: 1.0