# Chức Năng Quản Lý Đơn Hàng - Tạo Order Cho User

## Tổng Quan
Đã thêm chức năng cho phép admin tạo đơn hàng mới cho user thông qua việc tìm kiếm user theo ID hoặc email.

## Các Tính Năng Mới

### 1. Tìm Kiếm User
- **Tìm theo ID**: Nhập ID của user để tìm kiếm chính xác
- **Tìm theo Email**: Nhập email (có thể tìm kiếm một phần) để tìm user
- **Hiển thị thông tin**: Sau khi tìm thấy, hiển thị thông tin user bao gồm:
  - ID
  - Tên
  - Email  
  - Trạng thái (active/inactive)
  - Số dư ví

### 2. Tạo Đơn Hàng Mới
- **Auto-fill thông tin**: Tự động điền tên và email từ thông tin user
- **Form đầy đủ**: Bao gồm tất cả thông tin cần thiết cho đơn hàng:
  - Thông tin người nhận (họ, tên, email, số điện thoại)
  - Địa chỉ giao hàng (địa chỉ 1, địa chỉ 2, quốc gia, mã bưu điện)
  - Thông tin đơn hàng (số lượng, tổng phụ, tổng tiền)
  - Phương thức vận chuyển
  - Phương thức thanh toán
  - Trạng thái đơn hàng

## Cách Sử Dụng

### Bước 1: Truy Cập Trang Tạo Đơn Hàng
1. Đăng nhập với quyền admin
2. Vào menu **Đơn Hàng** 
3. Nhấn nút **"Tạo Đơn Hàng Mới"**

### Bước 2: Tìm Kiếm User
1. Trong ô **"Tìm Kiếm User"**, nhập:
   - ID của user (ví dụ: 1, 2, 3...)
   - Hoặc email của user (ví dụ: user@example.com)
2. Nhấn nút **"Tìm Kiếm"** hoặc nhấn Enter
3. Nếu tìm thấy, thông tin user sẽ hiển thị

### Bước 3: Điền Thông Tin Đơn Hàng
1. Sau khi chọn user, form tạo đơn hàng sẽ xuất hiện
2. Một số thông tin sẽ được tự động điền (tên, email)
3. Điền đầy đủ các thông tin còn lại:
   - **Số điện thoại**: Bắt buộc
   - **Địa chỉ**: Bắt buộc
   - **Quốc gia**: Bắt buộc
   - **Phương thức vận chuyển**: Chọn từ danh sách có sẵn
   - **Số lượng**: Số lượng sản phẩm
   - **Tổng phụ**: Tổng tiền trước phí vận chuyển
   - **Tổng tiền**: Tổng tiền cuối cùng

### Bước 4: Hoàn Tất
1. Kiểm tra lại thông tin
2. Nhấn **"Tạo Đơn Hàng"**
3. Hệ thống sẽ tạo đơn hàng và chuyển về danh sách đơn hàng

## Routes API

### Tìm Kiếm User
```
POST /admin/order/search-user
```
**Parameters:**
- `search`: ID hoặc email của user

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "status": "active",
        "wallet_balance": 100.00
    }
}
```

## Files Đã Thay Đổi

### 1. Controller
- **File**: `app/Http/Controllers/OrderController.php`
- **Methods mới**:
  - `create()`: Hiển thị form tạo đơn hàng
  - `searchUser()`: API tìm kiếm user
- **Methods cập nhật**:
  - `store()`: Xử lý tạo đơn hàng cho admin

### 2. Views
- **File mới**: `resources/views/backend/order/create.blade.php`
- **File cập nhật**: `resources/views/backend/order/index.blade.php` (thêm nút tạo mới)

### 3. Routes
- **File**: `routes/web.php`
- **Route mới**: `POST /admin/order/search-user`

### 4. Model
- **File**: `app/Models/Order.php`
- **Cập nhật**: Loại bỏ `delivery_charge` khỏi fillable (không tồn tại trong DB)

## Lưu Ý Kỹ Thuật

1. **Validation**: Đã thêm validation đầy đủ cho tất cả trường
2. **Security**: Sử dụng CSRF token cho tất cả form
3. **UX**: Auto-fill thông tin user, tính toán tự động tổng tiền
4. **Responsive**: Giao diện tương thích với mobile
5. **Error Handling**: Xử lý lỗi khi không tìm thấy user

## Test Chức Năng

### Test Cases
1. **Tìm user theo ID hợp lệ**: Phải hiển thị thông tin user
2. **Tìm user theo email hợp lệ**: Phải hiển thị thông tin user  
3. **Tìm user không tồn tại**: Phải hiển thị thông báo lỗi
4. **Tạo đơn hàng với thông tin đầy đủ**: Phải tạo thành công
5. **Tạo đơn hàng thiếu thông tin**: Phải hiển thị lỗi validation

### Cách Test
```bash
# Truy cập trang tạo đơn hàng
http://your-domain/admin/order/create

# Test API tìm kiếm user
curl -X POST http://your-domain/admin/order/search-user \
  -H "Content-Type: application/json" \
  -d '{"search": "1", "_token": "your-csrf-token"}'
```

## Troubleshooting

### Lỗi Thường Gặp
1. **"User not found"**: Kiểm tra ID hoặc email có đúng không
2. **"Validation errors"**: Kiểm tra các trường bắt buộc
3. **"CSRF token mismatch"**: Refresh trang và thử lại
4. **"Route not found"**: Kiểm tra routes đã được cache chưa

### Giải Pháp
```bash
# Clear cache routes
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan route:cache

# Clear config cache  
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan config:cache
```