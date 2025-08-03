# Thay Đổi Hệ Thống Thanh Toán - Chuyển Sang Wallet Payment

## Tóm Tắt
Đã thay đổi hệ thống thanh toán từ COD/PayPal sang thanh toán qua ví (wallet_balance) của user.

## Các File Đã Thay Đổi

### 1. **Database Migration**
**File**: `database/migrations/2025_08_03_160943_update_payment_method_enum_in_orders_table.php`
- Cập nhật enum `payment_method` trong bảng `orders` từ `['cod','paypal']` thành `['wallet']`
- Chuyển đổi tất cả orders cũ sang `wallet` payment method
- **Đã chạy**: ✅

### 2. **OrderController.php**
**File**: `app/Http/Controllers/OrderController.php`

**Thay đổi validation (dòng 92):**
```php
// Từ: 'payment_method'=>'required|in:cod,paypal',
// Thành: 'payment_method'=>'required|in:wallet',
```

**Thêm logic kiểm tra và trừ tiền ví (dòng 205-221):**
```php
// Check wallet balance and process payment
if($request->payment_method == 'wallet'){
    $totalAmount = $order_data['total_amount'];
    
    if($payingUser->wallet_balance < $totalAmount){
        request()->session()->flash('error','Insufficient wallet balance...');
        return back();
    }
    
    // Deduct money from wallet
    $payingUser->wallet_balance -= $totalAmount;
    $payingUser->save();
    
    $order_data['payment_status'] = 'paid';
}
```

**Loại bỏ logic PayPal redirect:**
- Xóa điều kiện `if(request('payment_method')=='paypal')`
- Tất cả orders đều xử lý như wallet payment

### 3. **SubAdminController.php**
**File**: `app/Http/Controllers/SubAdminController.php`

**Thay đổi validation (dòng 257):**
```php
// Từ: 'payment_method'=>'required|in:cod,paypal',
// Thành: 'payment_method'=>'required|in:wallet',
```

### 4. **Frontend Checkout View**
**File**: `resources/views/frontend/pages/checkout.blade.php`

**Thay đổi payment options (dòng 460-461):**
```php
// Từ:
<input name="payment_method" type="radio" value="cod"> <label> Cash On Delivery</label>
<input name="payment_method" type="radio" value="paypal"> <label> PayPal</label>

// Thành:
<input name="payment_method" type="radio" value="wallet" checked required> 
<label> Wallet Payment (Balance: ${{number_format(auth()->user()->wallet_balance ?? 0, 2)}})</label>
```

### 5. **Backend Order Views**

#### **Admin Order Show**
**File**: `resources/views/backend/order/show.blade.php`
```php
// Thay đổi hiển thị payment method
@if($order->payment_method=='wallet') Wallet Payment 
@elseif($order->payment_method=='cod') Cash on Delivery 
@else Paypal @endif
```

#### **User Order Show**
**File**: `resources/views/user/order/show.blade.php`
- Tương tự như admin order show

#### **Admin Order Create**
**File**: `resources/views/backend/order/create.blade.php`
```php
// Chỉ còn lại option wallet
<option value="wallet" selected>Thanh toán qua ví</option>
```

#### **Sub-Admin Order Create**
**File**: `resources/views/backend/sub-admin/orders/create.blade.php`
```php
// Chỉ còn lại option wallet
<option value="wallet" selected>Thanh toán qua ví</option>
```

## Tính Năng Mới

### 1. **Kiểm Tra Số Dư Ví**
- Trước khi tạo order, hệ thống kiểm tra user có đủ số dư không
- Hiển thị thông báo lỗi nếu không đủ số dư
- Hiển thị số dư hiện tại trong checkout form

### 2. **Tự Động Trừ Tiền**
- Khi order được tạo thành công, tự động trừ tiền từ `wallet_balance`
- Áp dụng cho cả 3 loại order: Regular, Buy Now, Admin Order

### 3. **Payment Status**
- Tất cả wallet payments đều có `payment_status = 'paid'`
- Không cần xử lý payment gateway

## Logic Xử Lý

### **Regular Checkout & Buy Now**
1. User điền form checkout
2. Hệ thống kiểm tra `wallet_balance >= total_amount`
3. Nếu đủ: Trừ tiền + tạo order + set `payment_status = 'paid'`
4. Nếu không đủ: Hiển thị lỗi + redirect back

### **Admin Order**
1. Admin/Sub-admin tạo order cho user
2. Hệ thống kiểm tra `wallet_balance` của user được chọn
3. Xử lý tương tự như regular checkout

## Thông Báo User

### **Success Messages**
- "Your product successfully placed in order. Payment deducted from wallet."
- "Đơn hàng đã được tạo thành công cho user ID: X"

### **Error Messages**
- "Insufficient wallet balance. Your balance: $X.XX, Required: $Y.YY"

## Backward Compatibility

### **Existing Orders**
- Tất cả orders cũ đã được chuyển đổi sang `payment_method = 'wallet'`
- Views vẫn hiển thị đúng cho orders cũ (COD/PayPal) nếu có

### **Database**
- Enum đã được cập nhật an toàn
- Không mất dữ liệu orders cũ

## Testing Checklist

### ✅ **Cần Test**
1. **Frontend Checkout**
   - [ ] Regular checkout với đủ số dư
   - [ ] Regular checkout với không đủ số dư
   - [ ] Buy Now với đủ số dư
   - [ ] Buy Now với không đủ số dư

2. **Admin Panel**
   - [ ] Admin tạo order cho user có đủ số dư
   - [ ] Admin tạo order cho user không đủ số dư
   - [ ] Sub-admin tạo order

3. **Order Display**
   - [ ] Hiển thị "Wallet Payment" trong order details
   - [ ] Hiển thị số dư ví trong checkout form

4. **Database**
   - [ ] Orders mới có `payment_method = 'wallet'`
   - [ ] Orders mới có `payment_status = 'paid'`
   - [ ] Wallet balance được trừ đúng số tiền

## Rollback Plan

Nếu cần rollback:
1. Chạy migration rollback: `php artisan migrate:rollback --step=1`
2. Revert các thay đổi trong controllers và views
3. Cập nhật lại validation rules

## Notes

- **Wallet Balance**: Cần đảm bảo users có đủ số dư trước khi test
- **Shipping Cost**: Vẫn được tính vào total_amount
- **Coupon**: Vẫn hoạt động bình thường, được trừ từ total_amount
- **Stock Management**: Không thay đổi, vẫn trừ stock khi order status = 'delivered'