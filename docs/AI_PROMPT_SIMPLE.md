# AI Prompt - Hệ Thống Ví Đơn Giản

## Prompt Chính

```
Tôi cần bạn tạo hệ thống ví điện tử đơn giản cho Laravel 10 với yêu cầu sau:

### Chức năng:
1. **Nạp tiền**: User tạo yêu cầu → CSKH liên hệ → Admin duyệt thủ công
2. **Rút tiền**: User tạo yêu cầu với thông tin ngân hàng → Admin duyệt và chuyển khoản thủ công
3. **Xem số dư và lịch sử giao dịch**

### Database:
- Thêm cột `wallet_balance` vào bảng `users`
- Bảng `wallet_transactions` (lưu lịch sử nạp/rút)
- Bảng `withdrawal_requests` (yêu cầu rút tiền)

### Models cần tạo:
- Cập nhật User model
- WalletTransaction model
- WithdrawalRequest model

### Controllers:
- WalletController (user): index, depositForm, deposit, withdrawForm, withdraw
- Admin\WalletController: deposits, withdrawals, approve, reject

### Views:
- user/wallet/index.blade.php (trang chính)
- user/wallet/deposit.blade.php (form nạp tiền)
- user/wallet/withdraw.blade.php (form rút tiền)
- admin/wallet/deposits.blade.php (admin quản lý nạp tiền)
- admin/wallet/withdrawals.blade.php (admin quản lý rút tiền)

### Yêu cầu kỹ thuật:
- Laravel 10, User model ở `app/User.php`
- Bootstrap UI
- Validation đầy đủ
- Database transaction cho tính toàn vẹn
- Comments tiếng Việt

Hãy tạo từng file một cách chi tiết.
```

## Prompt Tạo Migration

```
Tạo 3 file migration cho hệ thống ví:

1. **add_wallet_balance_to_users_table**: Thêm cột wallet_balance DECIMAL(15,2) DEFAULT 0.00
2. **create_wallet_transactions_table**: 
   - id, user_id, type (enum: deposit/withdraw), amount, balance_before, balance_after
   - description, status (enum: pending/completed/rejected), admin_note, timestamps
3. **create_withdrawal_requests_table**:
   - id, user_id, amount, bank_name, bank_account, account_name
   - status (enum: pending/completed/rejected), admin_note, timestamps

Bao gồm foreign keys và indexes.
```

## Prompt Tạo Models

```
Tạo models cho hệ thống ví:

1. **Cập nhật User model** (app/User.php):
   - Thêm wallet_balance vào fillable và casts
   - Relationships: walletTransactions(), withdrawalRequests()
   - Accessor: getFormattedBalanceAttribute()

2. **WalletTransaction model**:
   - Fillable, casts, relationship với User
   - Accessors: getFormattedAmountAttribute(), getStatusTextAttribute()

3. **WithdrawalRequest model**:
   - Fillable, casts, relationship với User
   - Accessor: getFormattedAmountAttribute()

Sử dụng namespace App\Models cho models mới.
```

## Prompt Tạo Controllers

```
Tạo 2 controllers:

1. **WalletController** cho user:
   - index(): Hiển thị số dư, lịch sử giao dịch, yêu cầu rút tiền
   - depositForm(): Form yêu cầu nạp tiền
   - deposit(): Xử lý yêu cầu nạp tiền (tạo record pending)
   - withdrawForm(): Form yêu cầu rút tiền
   - withdraw(): Xử lý yêu cầu rút tiền (kiểm tra số dư, tạo withdrawal_request)

2. **Admin\WalletController** cho admin:
   - deposits(): Danh sách yêu cầu nạp tiền
   - approveDeposit(): Duyệt nạp tiền (cộng tiền vào ví)
   - withdrawals(): Danh sách yêu cầu rút tiền
   - approveWithdrawal(): Duyệt rút tiền (trừ tiền từ ví)
   - reject(): Từ chối yêu cầu

Bao gồm validation, DB transaction, error handling.
```

## Prompt Tạo Views

```
Tạo views Bootstrap cho hệ thống ví:

1. **user/wallet/index.blade.php**:
   - Hiển thị số dư hiện tại
   - Nút "Nạp Tiền" và "Rút Tiền"
   - Bảng lịch sử giao dịch với pagination
   - Bảng yêu cầu rút tiền với trạng thái

2. **user/wallet/deposit.blade.php**:
   - Form nhập số tiền (min: 10,000, max: 50,000,000)
   - Textarea ghi chú (optional)
   - Validation frontend

3. **user/wallet/withdraw.blade.php**:
   - Hiển thị số dư hiện tại
   - Form: số tiền, tên ngân hàng, số tài khoản, tên chủ TK
   - Validation (min: 50,000, không vượt quá số dư)

4. **admin/wallet/deposits.blade.php**:
   - Bảng danh sách yêu cầu nạp tiền
   - Nút Duyệt/Từ chối với modal confirm

5. **admin/wallet/withdrawals.blade.php**:
   - Bảng danh sách yêu cầu rút tiền
   - Hiển thị thông tin ngân hàng
   - Nút Duyệt/Từ chối

Sử dụng @extends('layouts.app'), responsive design.
```

## Prompt Tạo Routes

```
Tạo routes cho hệ thống ví:

**User routes** (middleware: auth):
- GET /wallet → WalletController@index
- GET /wallet/deposit → WalletController@depositForm
- POST /wallet/deposit → WalletController@deposit
- GET /wallet/withdraw → WalletController@withdrawForm
- POST /wallet/withdraw → WalletController@withdraw

**Admin routes** (middleware: auth, admin):
- GET /admin/wallet/deposits → Admin\WalletController@deposits
- POST /admin/wallet/deposits/{id}/approve → Admin\WalletController@approveDeposit
- GET /admin/wallet/withdrawals → Admin\WalletController@withdrawals
- POST /admin/wallet/withdrawals/{id}/approve → Admin\WalletController@approveWithdrawal
- POST /admin/wallet/{type}/{id}/reject → Admin\WalletController@reject

Sử dụng route groups và named routes.
```

## Prompt Debug & Fix

```
Tôi gặp lỗi với hệ thống ví: [mô tả lỗi]

Hãy giúp tôi:
1. Xác định nguyên nhân lỗi
2. Cung cấp solution cụ thể
3. Code fix chi tiết
4. Cách test để đảm bảo hoạt động đúng

Context: Laravel 10, User model ở app/User.php, hệ thống ví đơn giản không tích hợp payment gateway.
```

## Prompt Thêm Tính Năng

```
Thêm tính năng cho hệ thống ví:

**[Tên tính năng]**: [Mô tả]

Yêu cầu:
- Cập nhật database schema nếu cần
- Modify existing models/controllers
- Tạo views mới
- Update routes
- Validation và security
- Maintain existing functionality

Giữ nguyên cấu trúc đơn giản, không dùng payment gateway.
```

## Prompt Tối Ưu

```
Tối ưu hệ thống ví hiện tại:

1. **Database**: Thêm indexes, optimize queries
2. **Performance**: Caching, pagination
3. **Security**: Input validation, CSRF protection
4. **UX**: Loading states, confirmations
5. **Code**: Refactor, clean code

Đưa ra suggestions cụ thể với code examples.
```

## Prompt Testing

```
Tạo test cases cho hệ thống ví:

1. **Unit Tests**:
   - Test User wallet methods
   - Test model relationships
   - Test calculations

2. **Feature Tests**:
   - Test deposit flow
   - Test withdrawal flow
   - Test admin approval process
   - Test validations

3. **Integration Tests**:
   - Test complete user journey
   - Test admin workflow

Sử dụng Laravel testing best practices.
```

---

## Cách Sử Dụng Prompts

1. **Bắt đầu với Prompt Chính** để tạo toàn bộ hệ thống
2. **Sử dụng Prompt Cụ Thể** cho từng component
3. **Dùng Prompt Debug** khi gặp lỗi
4. **Prompt Thêm Tính Năng** để mở rộng
5. **Prompt Tối Ưu** để cải thiện performance

## Tips

- Luôn mention Laravel version và cấu trúc project
- Specify rõ requirements (không dùng payment gateway)
- Ask for explanations nếu code phức tạp
- Request validation và error handling
- Yêu cầu comments tiếng Việt