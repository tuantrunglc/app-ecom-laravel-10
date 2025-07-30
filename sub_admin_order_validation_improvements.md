# Sub Admin Order Validation - Cải tiến

## Các vấn đề đã phát hiện và sửa:

### 1. **Validation Rules không đầy đủ**

#### Trước (có vấn đề):
```php
'first_name'=>'string|required',  // Thiếu giới hạn độ dài
'address1'=>'string|required',    // Không giới hạn length
'phone'=>'string|required',       // Không giới hạn format
'sub_total'=>'required|numeric|min:0'  // Không có max limit
```

#### Sau (đã cải tiến):
```php
'first_name'=>'required|string|max:255',
'address1'=>'required|string|max:500', 
'phone'=>'required|string|max:20',
'sub_total'=>'required|numeric|min:0|max:999999.99'
```

### 2. **Thiếu Custom Error Messages**

#### Đã thêm:
- Messages tiếng Việt cho tất cả validation rules
- Thông báo lỗi cụ thể và dễ hiểu
- Hướng dẫn người dùng sửa lỗi

### 3. **Field Mapping không chính xác**

#### Trước:
```php
$data = $request->all();  // Lấy tất cả, có thể có field không cần thiết
```

#### Sau:
```php
$data = $request->only([...]);  // Chỉ lấy fields cần thiết
$data['shipping_id'] = $request->shipping;  // Mapping đúng field name
$data['payment_status'] = 'unpaid';  // Set default value
```

### 4. **Thiếu Business Logic Validation**

#### Đã thêm:
- **User Active Check**: Kiểm tra user có active không
- **Total Amount Logic**: Kiểm tra tổng tiền = subtotal + shipping
- **Authority Check**: Đảm bảo user thuộc quyền quản lý

## Validation Rules chi tiết:

### **Required Fields với Length Limits:**
```php
'user_id' => 'required|exists:users,id'
'first_name' => 'required|string|max:255'  
'last_name' => 'required|string|max:255'
'address1' => 'required|string|max:500'
'country' => 'required|string|max:255'
'phone' => 'required|string|max:20'
'email' => 'required|email|max:255'
'shipping' => 'required|exists:shippings,id'
'payment_method' => 'required|in:cod,paypal'
'status' => 'required|in:new,process,delivered,cancel'
'sub_total' => 'required|numeric|min:0|max:999999.99'
'quantity' => 'required|integer|min:1|max:1000'
'total_amount' => 'required|numeric|min:0|max:999999.99'
```

### **Optional Fields:**
```php
'address2' => 'nullable|string|max:500'
'post_code' => 'nullable|string|max:20'
```

## Business Logic Validation:

### 1. **Authority Check**
```php
$user = $subAdmin->managedUsers()->where('id', $request->user_id)->first();
if (!$user) {
    return redirect()->back()->withInput()->with('error', 'User không thuộc quyền quản lý');
}
```

### 2. **User Status Check**  
```php
if ($user->status !== 'active') {
    return redirect()->back()->withInput()->with('error', 'Không thể tạo đơn hàng cho user không hoạt động');
}
```

### 3. **Total Amount Logic Check**
```php
$shipping = Shipping::find($request->shipping);
$expectedTotal = $request->sub_total + ($shipping ? $shipping->price : 0);

if (abs($request->total_amount - $expectedTotal) > 0.01) {
    return redirect()->back()->withInput()->with('error', 'Tổng tiền không chính xác');
}
```

## Error Handling Improvements:

### 1. **withInput() Support**
- Giữ lại dữ liệu đã nhập khi có lỗi
- User không cần nhập lại toàn bộ form

### 2. **Specific Error Messages**
- Messages tiếng Việt
- Hướng dẫn cụ thể cho từng lỗi
- Thân thiện với người dùng

### 3. **Field Highlighting**
- Form fields bị lỗi sẽ highlight màu đỏ
- Bootstrap validation classes

## Data Integrity:

### 1. **Safe Field Selection**
```php
$data = $request->only([
    'user_id', 'first_name', 'last_name', 'email', 'phone', 'country',
    'address1', 'address2', 'post_code', 'payment_method', 'status',
    'sub_total', 'quantity', 'total_amount'
]);
```

### 2. **Proper Field Mapping**
```php
$data['shipping_id'] = $request->shipping;  // Map từ 'shipping' sang 'shipping_id'
$data['order_number'] = 'ORD-'.strtoupper(uniqid());  // Auto generate unique
$data['payment_status'] = 'unpaid';  // Default value
```

## Test Cases cho Validation:

### Test 1: Missing Required Fields
- Bỏ trống first_name → "Vui lòng nhập họ"
- Bỏ trống email → "Vui lòng nhập email"  

### Test 2: Invalid Format
- Email sai format → "Email không đúng định dạng"
- Quantity âm → "Số lượng phải ít nhất là 1"

### Test 3: Length Limits
- Họ > 255 ký tự → "Họ không được vượt quá 255 ký tự"
- Địa chỉ > 500 ký tự → "Địa chỉ không được vượt quá 500 ký tự"

### Test 4: Business Logic
- User không thuộc quyền → "User không thuộc quyền quản lý của bạn"
- User inactive → "Không thể tạo đơn hàng cho user không hoạt động"
- Total amount sai → "Tổng tiền không chính xác"

## Kết quả:
✅ Validation chặt chẽ và đầy đủ
✅ Error messages thân thiện người dùng
✅ Business logic validation
✅ Data integrity protection
✅ User experience tốt hơn với withInput()