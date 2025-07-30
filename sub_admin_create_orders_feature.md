# Sub Admin - Tạo Đơn Hàng Cho User Quản Lý

## Mô tả
Đã thêm chức năng cho Sub Admin có thể tạo đơn hàng mới cho các users thuộc quyền quản lý của mình.

## Các thay đổi đã thực hiện:

### 1. Cập nhật SubAdminController
**File**: `app/Http/Controllers/SubAdminController.php`

#### Thêm các methods mới:
- `createOrder()`: Hiển thị form tạo đơn hàng
- `searchManagedUser()`: Tìm kiếm user thuộc quyền quản lý  
- `storeOrder()`: Lưu đơn hàng mới

#### Tính năng chính:
- **Bảo mật**: Chỉ sub admin có quyền `can_manage_orders` mới được tạo đơn hàng
- **Giới hạn quyền**: Chỉ có thể tạo đơn hàng cho users thuộc quyền quản lý
- **Validation**: Kiểm tra đầy đủ thông tin đơn hàng
- **Auto-fill**: Tự động điền thông tin user khi tìm kiếm

### 2. Thêm Routes mới
**File**: `routes/web.php`

```php
Route::get('/orders/create', 'SubAdminController@createOrder')->name('orders.create');
Route::post('/orders', 'SubAdminController@storeOrder')->name('orders.store');
Route::post('/orders/search-user', 'SubAdminController@searchManagedUser')->name('orders.search-user');
```

### 3. Tạo View mới
**File**: `resources/views/backend/sub-admin/orders/create.blade.php`

#### Tính năng giao diện:
- **User Search**: Tìm kiếm user bằng ID hoặc email
- **User Info Display**: Hiển thị thông tin user sau khi tìm thấy
- **Order Form**: Form chi tiết đơn hàng với đầy đủ thông tin
- **Auto Calculate**: Tự động tính tổng tiền khi nhập subtotal và chọn shipping
- **Validation**: Kiểm tra dữ liệu đầu vào

### 4. Cập nhật Orders Index
**File**: `resources/views/backend/sub-admin/orders/index.blade.php`
- Thêm nút "Tạo Đơn Hàng Mới" cho sub admin có quyền

## Cách hoạt động:

### Tạo đơn hàng:
1. Sub admin truy cập `/sub-admin/orders`
2. Click nút "Tạo Đơn Hàng Mới" (nếu có quyền)
3. Tìm kiếm user bằng ID hoặc email
4. Hệ thống kiểm tra user có thuộc quyền quản lý không
5. Hiển thị thông tin user và form đơn hàng
6. Điền thông tin đơn hàng và submit
7. Hệ thống tạo đơn hàng và redirect về danh sách

### Bảo mật:
- ✅ Chỉ sub admin có quyền `can_manage_orders` mới thấy nút tạo đơn hàng
- ✅ Chỉ có thể tìm kiếm users thuộc quyền quản lý 
- ✅ Validation kiểm tra user có thuộc quyền quản lý trước khi tạo đơn hàng
- ✅ Tự động tạo order number duy nhất

## Test Cases:

### Test 1: Sub Admin có quyền
```
1. Login với sub admin có can_manage_orders = true
2. Truy cập /sub-admin/orders
3. Sẽ thấy nút "Tạo Đơn Hàng Mới"
4. Click vào sẽ hiển thị form tạo đơn hàng
```

### Test 2: Sub Admin không có quyền
```
1. Login với sub admin có can_manage_orders = false  
2. Truy cập /sub-admin/orders
3. Không thấy nút "Tạo Đơn Hàng Mới"
4. Truy cập trực tiếp /sub-admin/orders/create sẽ bị từ chối
```

### Test 3: Tìm kiếm user hợp lệ
```
1. Tìm kiếm user thuộc quyền quản lý
2. Hiển thị thông tin user
3. Form đơn hàng được mở
4. Email và tên được tự động điền
```

### Test 4: Tìm kiếm user không hợp lệ
```
1. Tìm kiếm user không thuộc quyền quản lý
2. Hiển thị thông báo lỗi: "User không tìm thấy hoặc không thuộc quyền quản lý của bạn"
3. Form không được mở
```

### Test 5: Tạo đơn hàng thành công
```
1. Chọn user hợp lệ
2. Điền đầy đủ thông tin đơn hàng
3. Submit form
4. Đơn hàng được tạo với order_number tự động
5. Redirect về /sub-admin/orders với thông báo thành công
```

## Lưu ý:
- Sub admin CHỈ có thể tạo đơn hàng cho users thuộc quyền quản lý
- Cần có quyền `can_manage_orders = true` trong sub_admin_settings
- Đơn hàng sẽ có order_number tự động: `ORD-[UNIQUE_ID]`
- Tương tự chức năng admin nhưng có giới hạn quyền hạn
- Form tự động tính tổng tiền dựa trên subtotal + shipping cost