# Wallet Balance Validation Features

## Tính Năng Đã Thêm

### 1. **Frontend Validation (JavaScript)**
**File**: `resources/views/frontend/pages/checkout.blade.php`

#### **Real-time Balance Check**
- Kiểm tra số dư ví ngay khi trang load
- Kiểm tra lại khi user thay đổi shipping method
- Hiển thị thông báo lỗi bằng **tiếng Anh**

#### **Error Display**
```html
<div id="wallet-error" class="text-danger mt-2" style="display: none;"></div>
```

**Nội dung thông báo lỗi:**
```
⚠️ Insufficient wallet balance!
Your balance: $0.00
Required: $150.00
Please add $150.00 to your wallet.
```

#### **Button State Management**
- **Đủ tiền**: Button "proceed to checkout" màu xanh, có thể click
- **Không đủ tiền**: Button màu xám, disabled, không thể click

#### **Form Submission Prevention**
- Ngăn không cho submit form nếu không đủ số dư
- Auto scroll đến thông báo lỗi khi user cố gắng submit

### 2. **Backend Validation (PHP)**
**File**: `app/Http/Controllers/OrderController.php`

#### **Server-side Check**
```php
if($currentBalance < $totalAmount){
    request()->session()->flash('error','Insufficient wallet balance. Your balance: $' . number_format($currentBalance, 2) . ', Required: $' . number_format($totalAmount, 2) . '. Please add funds to your wallet before placing the order.');
    return back();
}
```

#### **Thông báo lỗi Backend (tiếng Anh)**
```
Insufficient wallet balance. Your balance: $0.00, Required: $150.00. Please add funds to your wallet before placing the order.
```

### 3. **CSS Styling**
**Thông báo lỗi có styling đẹp:**
- Background màu đỏ nhạt
- Border màu đỏ
- Icon warning
- Padding và border-radius

**Button disabled styling:**
- Màu xám
- Cursor not-allowed
- Không thể click

## Luồng Hoạt Động

### **Khi User Vào Trang Checkout:**

1. **JavaScript load** → Lấy `walletBalance` từ server
2. **Tính total amount** = subtotal + shipping - coupon
3. **So sánh** walletBalance vs totalAmount
4. **Nếu không đủ**:
   - Hiển thị error message màu đỏ
   - Disable submit button
   - Button chuyển màu xám
5. **Nếu đủ**:
   - Ẩn error message
   - Enable submit button
   - Button màu bình thường

### **Khi User Thay Đổi Shipping:**

1. **Tính lại total amount** với shipping cost mới
2. **Update giá** trong "#order_total_price"
3. **Kiểm tra lại wallet balance**
4. **Update UI** tương ứng

### **Khi User Click Submit:**

1. **JavaScript check** một lần nữa
2. **Nếu không đủ**:
   - Prevent form submission
   - Scroll đến error message
   - Return false
3. **Nếu đủ**:
   - Cho phép submit form
   - Server sẽ check lại và xử lý

### **Server-side Processing:**

1. **Validate form data**
2. **Tính total amount** chính xác
3. **Kiểm tra wallet balance** của user
4. **Nếu không đủ**:
   - Flash error message
   - Return back to checkout
5. **Nếu đủ**:
   - Trừ tiền từ wallet
   - Tạo order
   - Redirect success

## User Experience

### **Visual Feedback**
- ✅ **Đủ tiền**: Button xanh, có thể checkout
- ❌ **Không đủ tiền**: Button xám, thông báo lỗi rõ ràng

### **Error Messages (Tiếng Anh)**
- **Frontend**: "Insufficient wallet balance! Your balance: $X.XX, Required: $Y.YY"
- **Backend**: "Insufficient wallet balance. Your balance: $X.XX, Required: $Y.YY. Please add funds to your wallet before placing the order."

### **Responsive Design**
- Error message hiển thị đẹp trên mobile và desktop
- Button state thay đổi mượt mà
- Auto scroll đến error khi cần thiết

## Security Features

### **Double Validation**
- Frontend validation để UX tốt
- Backend validation để security
- Không thể bypass bằng cách disable JavaScript

### **Real-time Updates**
- Kiểm tra lại mỗi khi có thay đổi
- Đảm bảo thông tin luôn chính xác
- Prevent race conditions

## Testing Scenarios

### ✅ **Cần Test**

1. **User có $0 trong ví**:
   - Vào checkout → Thấy error message
   - Button bị disable
   - Không thể submit form

2. **User có $50, order cần $150**:
   - Thấy "Please add $100.00 to your wallet"
   - Button disabled

3. **User có $200, order cần $150**:
   - Không có error message
   - Button enabled
   - Có thể checkout thành công

4. **User thay đổi shipping**:
   - Total amount update
   - Error message update tương ứng
   - Button state update

5. **User cố gắng bypass JavaScript**:
   - Server vẫn block và hiển thị error
   - Redirect back to checkout

## Code Locations

### **Frontend Files**
- `resources/views/frontend/pages/checkout.blade.php` (lines 460-462, 602-617, 639-698)

### **Backend Files**  
- `app/Http/Controllers/OrderController.php` (lines 205-221)

### **Features Added**
- Real-time wallet balance validation
- English error messages
- Button state management
- Form submission prevention
- Auto-scroll to errors
- Responsive error styling

---

**Kết quả**: User sẽ biết rõ ràng khi không đủ tiền và không thể checkout cho đến khi có đủ số dư trong ví.