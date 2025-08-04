# Tài Liệu Luồng Checkout - E-commerce Laravel 10

## Tổng Quan
Hệ thống checkout hỗ trợ 3 loại đặt hàng:
1. **Regular Checkout**: Đặt hàng từ giỏ hàng thông thường
2. **Buy Now**: Mua ngay không qua giỏ hàng
3. **Admin Order**: Admin tạo đơn hàng cho user

## 1. Luồng Regular Checkout (Đặt hàng từ giỏ hàng)

### 1.1 Thêm sản phẩm vào giỏ hàng
**File**: `app/Http/Controllers/CartController.php`

#### Phương thức `addToCart()` (Thêm 1 sản phẩm)
```php
// Route: /add-to-cart/{slug}
// Method: GET
// Middleware: user (phải đăng nhập)
```

**Logic**:
1. Kiểm tra sản phẩm tồn tại
2. Kiểm tra sản phẩm đã có trong giỏ hàng chưa
3. Nếu có: Tăng số lượng + cập nhật amount
4. Nếu chưa: Tạo mới Cart record
5. Kiểm tra stock đủ không
6. Lưu vào database

#### Phương thức `singleAddToCart()` (Thêm với số lượng tùy chỉnh)
```php
// Route: /add-to-cart
// Method: POST
// Middleware: user
```

**Input**:
- `slug`: Slug của sản phẩm
- `quant[1]`: Số lượng muốn thêm

**Logic tương tự addToCart() nhưng với số lượng tùy chỉnh**

### 1.2 Xem giỏ hàng
**File**: `resources/views/frontend/pages/cart.blade.php`
**Route**: `/cart`

**Hiển thị**:
- Danh sách sản phẩm trong giỏ
- Số lượng, giá, tổng tiền
- Nút cập nhật, xóa
- Nút "Checkout"

### 1.3 Cập nhật giỏ hàng
**Method**: `cartUpdate()` trong `CartController`
**Route**: `POST /cart-update`

**Logic**:
1. Lặp qua từng sản phẩm trong request
2. Kiểm tra stock
3. Cập nhật quantity và amount
4. Lưu vào database

### 1.4 Trang Checkout
**Method**: `checkout()` trong `CartController`
**Route**: `GET /checkout`
**File**: `resources/views/frontend/pages/checkout.blade.php`

**Logic**:
```php
public function checkout(Request $request){
    // Kiểm tra xem có phải Buy Now không
    $isBuyNow = $request->has('buy_now') || session()->has('buy_now');
    
    if($isBuyNow && session()->has('buy_now')){
        // Nếu là Buy Now, pass data buy_now sang view
        $buyNowItem = session('buy_now');
        return view('frontend.pages.checkout', compact('buyNowItem', 'isBuyNow'));
    }
    
    // Nếu không phải Buy Now, checkout bình thường với cart
    return view('frontend.pages.checkout', ['isBuyNow' => false]);
}
```

**Form checkout bao gồm**:
- Thông tin giao hàng (họ tên, email, phone, địa chỉ, quốc gia)
- Chọn phương thức vận chuyển
- Chọn phương thức thanh toán (COD/PayPal)
- Tóm tắt đơn hàng

### 1.5 Xử lý đặt hàng
**Method**: `store()` trong `OrderController`
**Route**: `POST /cart/order`

**Validation**:
```php
$this->validate($request,[
    'first_name'=>'string|required',
    'last_name'=>'string|required',
    'address1'=>'string|required',
    'address2'=>'string|nullable',
    'country'=>'string|required',
    'phone'=>'string|required',
    'post_code'=>'string|nullable',
    'email'=>'string|required|email',
    'shipping'=>'required|exists:shippings,id',
    'payment_method'=>'required|in:cod,paypal',
]);
```

**Logic xử lý**:
1. Kiểm tra giỏ hàng không rỗng
2. Tạo order number: `ORD-{RANDOM_STRING}`
3. Tính toán:
   - `sub_total`: Tổng giá sản phẩm (từ Helper::totalCartPrice())
   - `quantity`: Tổng số lượng (từ Helper::cartCount())
   - `total_amount`: sub_total + shipping_cost - coupon
4. Set payment_status:
   - PayPal: 'paid'
   - COD: 'Unpaid'
5. Lưu Order vào database
6. Gửi notification cho admin
7. Xử lý theo payment method:
   - PayPal: Redirect đến payment gateway
   - COD: Cập nhật Cart records với order_id, clear session
8. Redirect về home với thông báo thành công

## 2. Luồng Buy Now (Mua ngay)

### 2.1 Buy Now Action
**Method**: `buyNow()` trong `CartController`
**Route**: `POST /buy-now`

**Input**:
- `slug`: Slug sản phẩm
- `quant[1]`: Số lượng

**Logic**:
1. Validate input
2. Kiểm tra stock
3. Tính giá sau discount
4. Tạo buyNowItem array và lưu vào session:
```php
$buyNowItem = [
    'product_id' => $product->id,
    'product' => $product,
    'slug' => $product->slug,
    'title' => $product->title,
    'photo' => $product->photo,
    'price' => $product->price,
    'discount_price' => $after_discount_price,
    'discount' => $product->discount,
    'quantity' => $request->quant[1],
    'amount' => $after_discount_price * $request->quant[1],
    'stock' => $product->stock
];
session(['buy_now' => $buyNowItem]);
```
5. Redirect đến checkout với flag buy_now=1

### 2.2 Checkout cho Buy Now
**Trang checkout sẽ hiển thị khác**:
- Hiển thị thông tin sản phẩm Buy Now thay vì cart
- Tính toán dựa trên buyNowItem trong session
- Form vẫn giống nhau

### 2.3 Xử lý Order cho Buy Now
**Trong OrderController::store()**:
```php
$isBuyNow = $request->has('buy_now_mode') && $request->buy_now_mode == 1;

if ($isBuyNow) {
    $order_data['user_id'] = $request->user()->id;
    // For Buy Now orders, use session data
    $buyNowItem = session('buy_now');
    $order_data['sub_total'] = $buyNowItem['amount'];
    $order_data['quantity'] = $buyNowItem['quantity'];
    
    // Tính total_amount với shipping và coupon
    $shipping_cost = 0;
    if($request->shipping){
        $shipping = Shipping::where('id', $request->shipping)->pluck('price');
        $shipping_cost = $shipping[0] ?? 0;
    }
    
    if(session('coupon')){
        $order_data['coupon'] = session('coupon')['value'];
        $order_data['total_amount'] = $buyNowItem['amount'] + $shipping_cost - session('coupon')['value'];
    } else {
        $order_data['total_amount'] = $buyNowItem['amount'] + $shipping_cost;
    }
}
```

**Sau khi tạo order thành công**:
1. Clear session buy_now và coupon
2. Tạo Cart record liên kết với order_id (để tracking)
3. Redirect về home

## 3. Luồng Admin Order (Admin tạo đơn cho user)

### 3.1 Trang tạo đơn hàng
**File**: `resources/views/backend/sub-admin/orders/create.blade.php`
**Route**: Admin panel

**Tính năng**:
1. **Tìm kiếm user**: Nhập ID hoặc email
2. **Hiển thị thông tin user**: ID, tên, email, trạng thái, số dư ví
3. **Form đặt hàng**: Thông tin giao hàng, chi tiết đơn hàng

### 3.2 Search User API
**Method**: `searchUser()` trong `OrderController`
**Route**: `POST /sub-admin/orders/search-user`

**Logic**:
```php
$user = User::where('id', $search)
           ->orWhere('email', 'like', '%' . $search . '%')
           ->first();

if ($user) {
    return response()->json([
        'success' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'wallet_balance' => $user->wallet_balance ?? 0
        ]
    ]);
}
```

### 3.3 Xử lý Admin Order
**Trong OrderController::store()**:
```php
$isAdminOrder = $request->has('user_id') && $request->user_id != auth()->id();

if ($isAdminOrder) {
    $order_data['user_id'] = $request->user_id;
    // For admin orders, use provided values
    $order_data['sub_total'] = $request->sub_total;
    $order_data['quantity'] = $request->quantity;
    $order_data['total_amount'] = $request->total_amount;
}
```

**Validation cho admin order**:
```php
$this->validate($request,[
    'user_id'=>'required|exists:users,id',
    'first_name'=>'string|required',
    'last_name'=>'string|required',
    'address1'=>'string|required',
    'address2'=>'string|nullable',
    'country'=>'string|required',
    'phone'=>'string|required',
    'post_code'=>'string|nullable',
    'email'=>'string|required|email',
    'shipping'=>'required|exists:shippings,id',
    'payment_method'=>'required|in:cod,paypal',
    'status'=>'required|in:new,process,delivered,cancel',
    'sub_total'=>'required|numeric|min:0',
    'quantity'=>'required|integer|min:1',
    'total_amount'=>'required|numeric|min:0'
]);
```

## 4. Models và Database

### 4.1 Cart Model
**Table**: `carts`
**Fields**:
- `id`: Primary key
- `user_id`: ID user sở hữu
- `product_id`: ID sản phẩm
- `order_id`: ID đơn hàng (null khi chưa checkout)
- `quantity`: Số lượng
- `price`: Giá sản phẩm (sau discount)
- `amount`: Tổng tiền (price * quantity)

### 4.2 Order Model
**Table**: `orders`
**Fields**:
- `id`: Primary key
- `order_number`: Mã đơn hàng (ORD-XXXXXXXXXX)
- `user_id`: ID user đặt hàng
- `sub_total`: Tổng tiền sản phẩm
- `shipping_id`: ID phương thức vận chuyển
- `coupon`: Giá trị coupon (nếu có)
- `total_amount`: Tổng tiền cuối cùng
- `quantity`: Tổng số lượng sản phẩm
- `payment_method`: cod/paypal
- `payment_status`: paid/Unpaid
- `status`: new/process/delivered/cancel
- `first_name`, `last_name`, `email`, `phone`: Thông tin người nhận
- `country`, `address1`, `address2`, `post_code`: Địa chỉ giao hàng

### 4.3 Shipping Model
**Table**: `shippings`
**Fields**:
- `id`: Primary key
- `type`: Loại vận chuyển
- `price`: Giá vận chuyển
- `status`: active/inactive

## 5. Helper Functions

### 5.1 Cart Helper Functions
**File**: `app/Http/Helpers.php`

```php
// Đếm số lượng sản phẩm trong giỏ
Helper::cartCount($user_id = '')

// Lấy tất cả sản phẩm trong giỏ
Helper::getAllProductFromCart($user_id = '')

// Tính tổng tiền giỏ hàng
Helper::totalCartPrice($user_id = '')

// Lấy danh sách shipping
Helper::shipping()
```

### 5.2 Logic tính toán
```php
// Tổng tiền = sub_total + shipping_cost - coupon_value
$total_amount = $sub_total + $shipping_cost - $coupon_value;

// Giá sau discount
$discount_price = $price - ($price * $discount / 100);
```

## 6. Frontend JavaScript

### 6.1 Checkout Page Scripts
**File**: `resources/views/frontend/pages/checkout.blade.php`

**Tính năng**:
1. **Auto calculate shipping**: Khi chọn phương thức vận chuyển, tự động cập nhật tổng tiền
2. **Form validation**: Validate các field bắt buộc
3. **Payment method selection**: Chọn COD hoặc PayPal

### 6.2 Admin Order Scripts
**File**: `resources/views/backend/sub-admin/orders/create.blade.php`

**Tính năng**:
1. **User search**: AJAX search user by ID/email
2. **Auto fill form**: Tự động điền thông tin user vào form
3. **Calculate total**: Tự động tính tổng tiền khi thay đổi sub_total hoặc shipping

## 7. Security & Validation

### 7.1 Middleware
- `user`: Chỉ user đã đăng nhập mới được checkout
- `block_sub_admin_frontend`: Sub admin không được sử dụng frontend cart

### 7.2 Validation Rules
- Tất cả field bắt buộc đều được validate
- Email format validation
- Numeric validation cho price, quantity
- Exists validation cho shipping_id, user_id

### 7.3 Stock Management
- Kiểm tra stock trước khi add to cart
- Kiểm tra stock trước khi checkout
- Trừ stock khi order status = 'delivered'

## 8. Payment Integration

### 8.1 Cash on Delivery (COD)
- `payment_status`: 'Unpaid'
- Không cần xử lý payment gateway
- Thanh toán khi nhận hàng

### 8.2 PayPal Integration
- `payment_status`: 'paid'
- Redirect đến PayPal gateway
- Xử lý callback từ PayPal

## 9. Notifications

### 9.1 Admin Notification
Khi có đơn hàng mới:
```php
$users = User::where('role','admin')->first();
$details = [
    'title' => 'New order created',
    'actionURL' => route('order.show', $order->id),
    'fas' => 'fa-file-alt'
];
Notification::send($users, new StatusNotification($details));
```

### 9.2 User Feedback
- Flash messages cho success/error
- Redirect với thông báo phù hợp

## 10. Session Management

### 10.1 Cart Session
- Không sử dụng session cho cart (lưu database)
- Chỉ sử dụng session cho coupon

### 10.2 Buy Now Session
```php
// Lưu thông tin Buy Now
session(['buy_now' => $buyNowItem]);

// Clear sau khi checkout
session()->forget('buy_now');
```

### 10.3 Coupon Session
```php
// Lưu coupon
session(['coupon' => ['value' => $discount_amount]]);

// Clear sau checkout
session()->forget('coupon');
```

## 11. Error Handling

### 11.1 Common Errors
- Cart empty
- Product out of stock
- Invalid product
- User not found (admin order)
- Payment gateway errors

### 11.2 Error Messages
- Tiếng Việt cho user-facing messages
- English cho system/debug messages
- Flash messages với appropriate styling

## 12. Testing Scenarios

### 12.1 Regular Checkout
1. Add products to cart
2. Update quantities
3. Go to checkout
4. Fill shipping info
5. Select payment method
6. Complete order

### 12.2 Buy Now
1. Click "Buy Now" on product
2. Go directly to checkout
3. Complete order
4. Verify cart not affected

### 12.3 Admin Order
1. Search for user
2. Fill order details
3. Create order for user
4. Verify order created correctly

---

**Lưu ý**: Tài liệu này mô tả luồng checkout hiện tại. Để thay đổi hoặc mở rộng tính năng, cần cập nhật các file controller, view và database schema tương ứng.