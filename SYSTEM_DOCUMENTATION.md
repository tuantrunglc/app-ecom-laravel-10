# 📚 Tài liệu Hệ thống Laravel E-Commerce

## 📋 Mục lục
1. [Tổng quan hệ thống](#tổng-quan-hệ-thống)
2. [Cấu trúc dự án](#cấu-trúc-dự-án)
3. [Cơ sở dữ liệu](#cơ-sở-dữ-liệu)
4. [Tính năng chính](#tính-năng-chính)
5. [API và Routes](#api-và-routes)
6. [Models và Relationships](#models-và-relationships)
7. [Controllers](#controllers)
8. [Views và Frontend](#views-và-frontend)
9. [Authentication & Authorization](#authentication--authorization)
10. [Payment Integration](#payment-integration)
11. [File Storage](#file-storage)
12. [Configuration](#configuration)
13. [Deployment](#deployment)
14. [Maintenance](#maintenance)

---

## 🏗️ Tổng quan hệ thống

### Thông tin cơ bản
- **Framework:** Laravel 10.x
- **PHP Version:** 8.1+
- **Database:** MySQL 5.7+
- **Frontend:** Vue.js + Bootstrap
- **Payment:** PayPal Integration
- **Email:** SMTP/Newsletter (MailChimp)

### Kiến trúc hệ thống
```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Laravel API    │    │   Database      │
│  (Vue.js/Blade) │◄──►│   (Controllers)  │◄──►│   (MySQL)       │
└─────────────────┘    └──────────────────┘    └─────────────────┘
        │                       │                       │
        ▼                       ▼                       ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Static Assets │    │   File Storage   │    │   External APIs │
│  (CSS/JS/Images)│    │  (Images/Files)  │    │   (PayPal/Mail) │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

---

## 📁 Cấu trúc dự án

### Cấu trúc thư mục chính
```
├── app/                          # Ứng dụng Laravel
│   ├── Http/
│   │   ├── Controllers/         # Controllers
│   │   ├── Middleware/          # Middleware
│   │   └── Helpers.php          # Helper functions
│   ├── Models/                  # Eloquent Models
│   ├── Providers/               # Service Providers
│   ├── Events/                  # Events
│   └── Notifications/           # Notifications
├── bootstrap/                    # Bootstrap files
├── config/                      # Configuration files
├── database/
│   ├── migrations/              # Database migrations
│   ├── seeders/                 # Database seeders
│   └── e-shop.sql              # Database dump
├── public/                      # Public assets
│   ├── backend/                # Admin assets
│   ├── frontend/               # Frontend assets
│   ├── css/                    # Compiled CSS
│   ├── js/                     # Compiled JS
│   └── images/                 # Images
├── resources/
│   ├── js/                     # Vue.js source
│   ├── sass/                   # SCSS source
│   ├── views/                  # Blade templates
│   └── lang/                   # Language files
├── routes/
│   ├── web.php                 # Web routes
│   ├── api.php                 # API routes
│   └── channels.php            # Broadcast channels
├── storage/                     # Storage files
│   ├── app/public/             # Public storage
│   ├── framework/              # Framework cache
│   └── logs/                   # Log files
└── vendor/                     # Composer dependencies
```

### Các file cấu hình quan trọng
- **`.env`** - Environment configuration
- **`composer.json`** - PHP dependencies
- **`package.json`** - Node.js dependencies
- **`webpack.mix.js`** - Asset compilation
- **`docker-compose.yml`** - Docker configuration

---

## 🗄️ Cơ sở dữ liệu

### Sơ đồ ERD
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Users    │    │   Orders    │    │  Products   │
│─────────────│    │─────────────│    │─────────────│
│ id (PK)     │◄──►│ id (PK)     │    │ id (PK)     │
│ name        │    │ user_id (FK)│    │ title       │
│ email       │    │ order_number│    │ slug        │
│ role        │    │ total_amount│    │ price       │
└─────────────┘    │ status      │    │ category_id │
                   └─────────────┘    │ brand_id    │
                                     └─────────────┘
                                           │
                   ┌─────────────┐         │
                   │ Categories  │◄────────┘
                   │─────────────│
                   │ id (PK)     │
                   │ title       │
                   │ slug        │
                   │ is_parent   │
                   └─────────────┘
```

### Bảng dữ liệu chính

#### 1. **users** - Người dùng
```sql
- id: Primary Key
- name: Tên người dùng
- email: Email (unique)
- email_verified_at: Thời gian verify email
- password: Mật khẩu (hashed)
- photo: Ảnh đại diện
- role: Vai trò (admin/user)
- status: Trạng thái (active/inactive)
- phone: Số điện thoại
- address: Địa chỉ
- created_at, updated_at: Timestamps
```

#### 2. **products** - Sản phẩm
```sql
- id: Primary Key
- title: Tên sản phẩm
- slug: URL slug
- summary: Mô tả ngắn
- description: Mô tả chi tiết
- photo: Ảnh chính
- stock: Số lượng tồn kho
- size: Kích thước
- condition: Tình trạng (default/new/hot)
- status: Trạng thái (active/inactive)
- price: Giá gốc
- discount: Giảm giá (%)
- is_featured: Sản phẩm nổi bật
- cat_id: Category ID (FK)
- child_cat_id: Subcategory ID (FK)
- brand_id: Brand ID (FK)
- created_at, updated_at: Timestamps
```

#### 3. **categories** - Danh mục
```sql
- id: Primary Key
- title: Tên danh mục
- slug: URL slug
- summary: Mô tả
- photo: Ảnh danh mục
- is_parent: Danh mục cha/con
- parent_id: Parent category ID
- added_by: Người tạo
- status: Trạng thái
- created_at, updated_at: Timestamps
```

#### 4. **orders** - Đơn hàng
```sql
- id: Primary Key
- order_number: Mã đơn hàng
- user_id: User ID (FK)
- sub_total: Tổng tiền hàng
- shipping_id: Shipping ID (FK)
- coupon: Mã giảm giá
- total_amount: Tổng tiền
- quantity: Số lượng
- payment_method: Phương thức thanh toán
- payment_status: Trạng thái thanh toán
- status: Trạng thái đơn hàng
- first_name, last_name: Tên người nhận
- email, phone: Liên hệ
- country, post_code, address1, address2: Địa chỉ
- created_at, updated_at: Timestamps
```

#### 5. **carts** - Giỏ hàng
```sql
- id: Primary Key
- product_id: Product ID (FK)
- order_id: Order ID (FK)
- user_id: User ID (FK)
- price: Giá
- status: Trạng thái
- quantity: Số lượng
- amount: Thành tiền
- created_at, updated_at: Timestamps
```

### Các bảng khác
- **brands** - Thương hiệu
- **banners** - Banner quảng cáo
- **coupons** - Mã giảm giá
- **shippings** - Phương thức vận chuyển
- **wishlists** - Danh sách yêu thích
- **product_reviews** - Đánh giá sản phẩm
- **posts** - Blog posts
- **post_categories** - Danh mục blog
- **post_comments** - Bình luận blog
- **messages** - Tin nhắn liên hệ
- **notifications** - Thông báo
- **settings** - Cài đặt hệ thống

---

## ⚡ Tính năng chính

### 🛍️ Frontend (Khách hàng)
1. **Trang chủ**
   - Banner slideshow
   - Sản phẩm nổi bật
   - Danh mục sản phẩm
   - Blog posts

2. **Sản phẩm**
   - Danh sách sản phẩm với filter
   - Chi tiết sản phẩm
   - Đánh giá và bình luận
   - Sản phẩm liên quan

3. **Giỏ hàng & Thanh toán**
   - Thêm/xóa sản phẩm
   - Cập nhật số lượng
   - Áp dụng coupon
   - Checkout với PayPal
   - Theo dõi đơn hàng

4. **Tài khoản**
   - Đăng ký/đăng nhập
   - Quản lý profile
   - Lịch sử đơn hàng
   - Wishlist

5. **Blog**
   - Danh sách bài viết
   - Chi tiết bài viết
   - Bình luận

### 🔧 Backend (Admin)
1. **Dashboard**
   - Thống kê tổng quan
   - Biểu đồ doanh thu
   - Đơn hàng mới
   - Users mới

2. **Quản lý sản phẩm**
   - CRUD sản phẩm
   - Quản lý danh mục
   - Quản lý thương hiệu
   - Quản lý kho

3. **Quản lý đơn hàng**
   - Danh sách đơn hàng
   - Chi tiết đơn hàng
   - Cập nhật trạng thái
   - In hóa đơn

4. **Quản lý người dùng**
   - Danh sách users
   - Phân quyền
   - Khóa/mở tài khoản

5. **Marketing**
   - Quản lý banner
   - Mã giảm giá
   - Newsletter
   - SEO settings

6. **Cài đặt**
   - Cài đặt tổng quan
   - Phương thức thanh toán
   - Shipping methods
   - Email templates

---

## 🛣️ API và Routes

### Web Routes (`routes/web.php`)

#### Frontend Routes
```php
// Trang chủ
Route::get('/', 'FrontendController@home')->name('home');

// Sản phẩm
Route::get('/product-detail/{slug}', 'FrontendController@productDetail')->name('product-detail');
Route::get('/product-cat/{slug}', 'FrontendController@productCat')->name('product-cat');
Route::get('/product-brand/{slug}', 'FrontendController@productBrand')->name('product-brand');

// Giỏ hàng
Route::post('/cart/store', 'CartController@addToCart')->name('cart.store');
Route::post('/cart/update', 'CartController@updateCart')->name('cart.update');
Route::post('/cart/delete', 'CartController@deleteCart')->name('cart.delete');

// Checkout
Route::get('/checkout', 'CartController@checkout')->name('checkout');
Route::post('/cart/order', 'OrderController@store')->name('cart.order');

// User Authentication
Route::group(['prefix' => '/user', 'middleware' => ['user']], function(){
    Route::get('/', 'HomeController@index')->name('user');
    Route::get('/order', 'HomeController@orderIndex')->name('user.order.index');
    Route::get('/order/show/{id}', 'HomeController@orderShow')->name('user.order.show');
});
```

#### Admin Routes
```php
Route::group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function(){
    // Dashboard
    Route::get('/', 'AdminController@index')->name('admin');
    
    // Banner
    Route::resource('/banner', 'BannerController');
    
    // Category
    Route::resource('/category', 'CategoryController');
    
    // Product
    Route::resource('/product', 'ProductController');
    
    // Brand
    Route::resource('/brand', 'BrandController');
    
    // Shipping
    Route::resource('/shipping', 'ShippingController');
    
    // Order
    Route::resource('/order', 'OrderController');
    
    // Users
    Route::resource('/users', 'UsersController');
    
    // Coupon
    Route::resource('/coupon', 'CouponController');
});
```

### API Routes (`routes/api.php`)
```php
// Product API
Route::get('/products', 'Api\ProductController@index');
Route::get('/products/{id}', 'Api\ProductController@show');

// Category API
Route::get('/categories', 'Api\CategoryController@index');

// Cart API
Route::post('/cart/add', 'Api\CartController@add');
Route::get('/cart/{user_id}', 'Api\CartController@show');

// Order API
Route::post('/orders', 'Api\OrderController@store');
Route::get('/orders/{user_id}', 'Api\OrderController@index');
```

---

## 🏛️ Models và Relationships

### User Model
```php
class User extends Authenticatable
{
    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'added_by');
    }
}
```

### Product Model
```php
class Product extends Model
{
    // Relationships
    public function cat_info()
    {
        return $this->hasOne(Category::class, 'id', 'cat_id');
    }

    public function sub_cat_info()
    {
        return $this->hasOne(Category::class, 'id', 'child_cat_id');
    }

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function rel_prods()
    {
        return $this->hasMany(Product::class, 'cat_id', 'cat_id')->where('status', 'active')->orderBy('id', 'DESC')->limit(8);
    }

    public function getReview()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id')->with('user_info')->where('status', 'active')->orderBy('id', 'DESC');
    }

    // Accessors
    public function getDiscountAttribute($value)
    {
        return $this->price - ($this->price * $value / 100);
    }
}
```

### Order Model
```php
class Order extends Model
{
    // Relationships
    public function cart_info()
    {
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'id', 'order_id');
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Methods
    public static function getAllOrder($id)
    {
        return Order::with('cart_info')->find($id);
    }

    public static function countActiveOrder()
    {
        $data = Order::count();
        return $data;
    }
}
```

### Category Model
```php
class Category extends Model
{
    // Relationships
    public function parent_info()
    {
        return $this->hasOne(Category::class, 'id', 'parent_id');
    }

    public function child_cat()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->where('status', 'active');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'cat_id', 'id')->where('status', 'active');
    }

    public function sub_products()
    {
        return $this->hasMany(Product::class, 'child_cat_id', 'id')->where('status', 'active');
    }

    // Static Methods
    public static function getAllCategory()
    {
        return Category::orderBy('id', 'DESC')->with('parent_info')->where('status', 'active')->get();
    }

    public static function getProductByCat($slug)
    {
        return Category::with('products')->where('slug', $slug)->first();
    }
}
```

---

## 🎮 Controllers

### FrontendController
```php
class FrontendController extends Controller
{
    public function home()
    {
        $featured = Product::where('status', 'active')->where('is_featured', 1)->orderBy('price', 'DESC')->limit(2)->get();
        $posts = Post::where('status', 'active')->orderBy('id', 'DESC')->limit(3)->get();
        $banners = Banner::where('status', 'active')->limit(3)->orderBy('id', 'DESC')->get();
        $products = Product::where('status', 'active')->orderBy('id', 'DESC')->limit(8)->get();
        $category = Category::where('status', 'active')->where('is_parent', 1)->orderBy('title', 'ASC')->get();
        
        return view('frontend.index')
            ->with('featured', $featured)
            ->with('posts', $posts)
            ->with('banners', $banners)
            ->with('product_lists', $products)
            ->with('category_lists', $category);
    }

    public function productDetail($slug)
    {
        $product_detail = Product::getProductBySlug($slug);
        return view('frontend.pages.product_detail')->with('product_detail', $product_detail);
    }

    public function productCat(Request $request, $slug)
    {
        $products = Category::getProductByCat($slug);
        $sort = '';
        
        if($request->sort != null) {
            $sort = $request->sort;
            if($sort == 'title') {
                $products = Category::getProductByCat($slug)->products()->orderBy('title', 'ASC')->paginate(9);
            }
            if($sort == 'price') {
                $products = Category::getProductByCat($slug)->products()->orderBy('price', 'ASC')->paginate(9);
            }
        }
        
        return view('frontend.pages.product-cat')
            ->with('products', $products)
            ->with('sort', $sort);
    }
}
```

### ProductController (Admin)
```php
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::getAllProduct();
        return view('backend.product.index')->with('products', $products);
    }

    public function create()
    {
        $brand = Brand::get();
        $category = Category::where('is_parent', 1)->get();
        return view('backend.product.create')
            ->with('categories', $category)
            ->with('brands', $brand);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'string|required',
            'summary' => 'string|required',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'size' => 'nullable',
            'stock' => "required|numeric",
            'cat_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'child_cat_id' => 'nullable|exists:categories,id',
            'is_featured' => 'sometimes|in:1',
            'status' => 'required|in:active,inactive',
            'condition' => 'required|in:default,new,hot',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric'
        ]);

        $data = $request->all();
        $slug = Str::slug($request->title);
        $count = Product::where('slug', $slug)->count();
        if($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $data['is_featured'] = $request->input('is_featured', 0);

        $status = Product::create($data);
        if($status) {
            request()->session()->flash('success', 'Product Successfully added');
        } else {
            request()->session()->flash('error', 'Please try again!!');
        }
        return redirect()->route('product.index');
    }
}
```

### CartController
```php
class CartController extends Controller
{
    protected $product = null;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function addToCart(Request $request)
    {
        $product = Product::find($request->product_id);
        if(empty($product)) {
            request()->session()->flash('error', 'Invalid Products');
            return back();
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)
                           ->where('order_id', null)
                           ->where('product_id', $product->id)
                           ->first();

        if($already_cart) {
            $already_cart->quantity = $already_cart->quantity + 1;
            $already_cart->amount = $product->price + $already_cart->amount;
            
            if($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) {
                return back()->with('error', 'Stock not sufficient!.');
            }
            
            $already_cart->save();
        } else {
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = 1;
            $cart->amount = $cart->price * $cart->quantity;
            
            if($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) {
                return back()->with('error', 'Stock not sufficient!.');
            }
            
            $cart->save();
        }
        
        request()->session()->flash('success', 'Product has been added to cart');
        return back();
    }

    public function singleAddToCart(Request $request)
    {
        $request->validate([
            'slug' => 'required',
            'quant' => 'required|array',
            'quant.*' => 'required|numeric|min:1',
        ]);

        $product = Product::where('slug', $request->slug)->first();
        if($product->stock < $request->quant[1] || $product->stock <= 0) {
            return back()->with('error', 'Out of stock, You can add other products.');
        }
        
        if(($product->price - ($product->price * $product->discount) / 100) != $request->quant[0]) {
            return back()->with('error', 'Currency does not match.');
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)
                           ->where('order_id', null)
                           ->where('product_id', $product->id)
                           ->first();

        if($already_cart) {
            $already_cart->quantity = $already_cart->quantity + $request->quant[1];
            $already_cart->amount = ($product->price * $request->quant[1]) + $already_cart->amount;

            if($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) {
                return back()->with('error', 'Stock not sufficient!.');
            }

            $already_cart->save();
        } else {
            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = $request->quant[1];
            $cart->amount = ($product->price - ($product->price * $product->discount) / 100) * $request->quant[1];
            $cart->save();
        }
        
        request()->session()->flash('success', 'Product has been added to cart.');
        return back();
    }

    public function cartDelete(Request $request)
    {
        $cart = Cart::find($request->id);
        if($cart) {
            $cart->delete();
            request()->session()->flash('success', 'Cart successfully removed');
            return back();
        }
        request()->session()->flash('error', 'Error please try again');
        return back();
    }

    public function cartUpdate(Request $request)
    {
        if($request->quant) {
            $error = array();
            $success = '';
            foreach($request->quant as $k => $quant) {
                $id = $request->qty_id[$k];
                $cart = Cart::find($id);
                if($quant > 0 && $cart) {
                    if($cart->product->stock < $quant) {
                        request()->session()->flash('error', 'Out of stock');
                        return back();
                    }
                    $cart->quantity = $cart->product->stock > $quant ? $quant : $cart->product->stock;
                    
                    if($cart->product->stock <= 0) continue;
                    $after_price = ($cart->product->price - ($cart->product->price * $cart->product->discount) / 100);
                    $cart->amount = $after_price * $quant;
                    $cart->save();
                    $success = 'Cart successfully updated!';
                } else {
                    $error[] = 'Cart Invalid!';
                }
            }
            return back()->with($error)->with('success', $success);
        } else {
            return back()->with('Cart Invalid!');
        }
    }

    public function checkout()
    {
        $carts = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->get();
        return view('frontend.pages.checkout', compact('carts'));
    }
}
```

---

## 🎨 Views và Frontend

### Blade Template Structure
```
resources/views/
├── frontend/
│   ├── layouts/
│   │   ├── master.blade.php      # Master layout
│   │   ├── header.blade.php      # Header component
│   │   └── footer.blade.php      # Footer component
│   ├── pages/
│   │   ├── index.blade.php       # Homepage
│   │   ├── about.blade.php       # About page
│   │   ├── contact.blade.php     # Contact page
│   │   ├── product-detail.blade.php # Product detail
│   │   ├── product-grids.blade.php  # Product grid
│   │   ├── product-lists.blade.php  # Product list
│   │   ├── cart.blade.php        # Shopping cart
│   │   ├── checkout.blade.php    # Checkout page
│   │   └── order-track.blade.php # Order tracking
│   └── user/
│       ├── dashboard.blade.php   # User dashboard
│       ├── order.blade.php       # Order history
│       └── profile.blade.php     # User profile
└── backend/
    ├── layouts/
    │   ├── master.blade.php      # Admin master layout
    │   ├── sidebar.blade.php     # Admin sidebar
    │   └── header.blade.php      # Admin header
    ├── index.blade.php           # Admin dashboard
    ├── product/
    │   ├── index.blade.php       # Product list
    │   ├── create.blade.php      # Create product
    │   └── edit.blade.php        # Edit product
    ├── category/
    ├── brand/
    ├── order/
    └── users/
```

### Vue.js Components
```javascript
// resources/js/app.js
require('./bootstrap');

window.Vue = require('vue');

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

const app = new Vue({
    el: '#app',
});
```

### SCSS Structure
```scss
// resources/sass/app.scss
@import "variables";
@import "bootstrap/bootstrap";

// Custom styles
@import "frontend/layout";
@import "frontend/components";
@import "backend/admin";
```

---

## 🔐 Authentication & Authorization

### Authentication
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

### Middleware
```php
// app/Http/Middleware/Admin.php
class Admin
{
    public function handle($request, Closure $next)
    {
        if(auth()->user()->role == 'admin') {
            return $next($request);
        } else {
            request()->session()->flash('error', 'You do not have any permission to access this page');
            return redirect()->route('login');
        }
    }
}

// app/Http/Middleware/User.php
class User
{
    public function handle($request, Closure $next)
    {
        if(auth()->user()->role == 'user') {
            return $next($request);
        } else {
            request()->session()->flash('error', 'You do not have any permission to access this page');
            return redirect()->route('login');
        }
    }
}
```

### Role-based Access
```php
// User roles
const ROLE_ADMIN = 'admin';
const ROLE_USER = 'user';

// In Controller
if(auth()->user()->role !== 'admin') {
    abort(403, 'Unauthorized action.');
}

// In Blade
@if(auth()->user()->role == 'admin')
    <a href="{{ route('admin') }}">Admin Panel</a>
@endif
```

---

## 💳 Payment Integration

### PayPal Configuration
```php
// config/paypal.php
return [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
    ],
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
    ],
    'payment_action' => 'Sale',
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'notify_url' => env('PAYPAL_NOTIFY_URL'),
    'locale' => env('PAYPAL_LOCALE', 'en_US'),
    'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true),
];
```

### PayPal Integration
```php
// In OrderController
public function store(Request $request)
{
    // Validate and create order
    $order = new Order();
    $order->fill($request->all());
    $order->save();

    // PayPal payment
    if($request->payment_method == 'paypal') {
        return $this->paypalPayment($order);
    }
    
    return redirect()->route('home')->with('success', 'Order placed successfully!');
}

private function paypalPayment($order)
{
    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $paypalToken = $provider->getAccessToken();

    $response = $provider->createOrder([
        "intent" => "CAPTURE",
        "application_context" => [
            "return_url" => route('success.payment'),
            "cancel_url" => route('cancel.payment'),
        ],
        "purchase_units" => [
            0 => [
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $order->total_amount
                ]
            ]
        ]
    ]);

    if (isset($response['id']) && $response['id'] != null) {
        foreach ($response['links'] as $links) {
            if ($links['rel'] == 'approve') {
                return redirect()->away($links['href']);
            }
        }
        return redirect()->route('cancel.payment')
                        ->with('error', 'Something went wrong.');
    } else {
        return redirect()->route('cancel.payment')
                        ->with('error', $response['message'] ?? 'Something went wrong.');
    }
}
```

---

## 📁 File Storage

### Storage Configuration
```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'throw' => false,
    ],

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

### File Upload Helper
```php
// app/Http/Helpers.php
class Helper
{
    public static function uploadFile($file, $directory = 'uploads')
    {
        if ($file) {
            $filename = time() . '-' . $file->getClientOriginalName();
            $file->move(public_path($directory), $filename);
            return $directory . '/' . $filename;
        }
        return null;
    }

    public static function deleteFile($filepath)
    {
        if ($filepath && file_exists(public_path($filepath))) {
            unlink(public_path($filepath));
            return true;
        }
        return false;
    }
}
```

### Image Management
```php
// In Product Controller
public function store(Request $request)
{
    $data = $request->all();
    
    // Handle image upload
    if ($request->hasFile('photo')) {
        $data['photo'] = Helper::uploadFile($request->file('photo'), 'uploads/products');
    }
    
    Product::create($data);
}
```

---

## ⚙️ Configuration

### Environment Variables (.env)
```bash
# Application
APP_NAME="Laravel E-Shop"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ecommerce
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=your_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_client_secret

# Newsletter
MAILCHIMP_APIKEY=your_mailchimp_api_key
MAILCHIMP_LIST_ID=your_list_id
```

### Service Configuration

#### Mail Service
```php
// config/mail.php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'name' => env('MAIL_FROM_NAME', 'Example'),
],

'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
    ],
],
```

#### Newsletter Service
```php
// config/newsletter.php
return [
    'apikey' => env('MAILCHIMP_APIKEY'),
    'lists' => [
        'subscribers' => [
            'id' => env('MAILCHIMP_LIST_ID'),
        ],
    ],
];
```

---

## 🚀 Deployment

### Development Setup
```bash
# Clone repository
git clone https://github.com/Prajwal100/Complete-Ecommerce-in-laravel-10.git

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Assets
npm run dev
# or for production
npm run production

# Storage link
php artisan storage:link

# Serve application
php artisan serve
```

### Docker Setup
```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
      
  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: laravel_ecommerce
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_password
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

### Production Deployment

#### Server Requirements
- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js & npm
- Web server (Apache/Nginx)

#### Deployment Steps
```bash
# 1. Upload files
# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Build assets
npm ci --only=production
npm run production

# 4. Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 5. Configure environment
cp .env.example .env
# Edit .env with production settings

# 6. Run migrations
php artisan migrate --force

# 7. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Create storage link
php artisan storage:link
```

---

## 🔧 Maintenance

### Backup
```bash
# Database backup
mysqldump -u username -p database_name > backup.sql

# File backup
tar -czf laravel_backup.tar.gz /path/to/laravel

# Automated backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup_$DATE.sql
tar -czf backup_$DATE.tar.gz /var/www/html backup_$DATE.sql
```

### Monitoring
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check storage usage
du -sh storage/

# Check database size
SELECT table_schema "Database", 
       Round(Sum(data_length + index_length) / 1024 / 1024, 1) "DB Size in MB" 
FROM information_schema.tables 
GROUP BY table_schema;
```

### Performance Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Security Updates
```bash
# Update dependencies
composer update
npm update

# Check for vulnerabilities
composer audit
npm audit

# Update Laravel
composer require laravel/framework:^10.0
```

### Troubleshooting

#### Common Issues
1. **Storage permission errors**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

2. **Asset compilation errors**
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run production
```

3. **Database connection issues**
```bash
# Check MySQL service
systemctl status mysql

# Test connection
mysql -u username -p -h hostname database_name
```

4. **Memory limit errors**
```php
// In .htaccess or php.ini
memory_limit = 512M
max_execution_time = 300
```

---

## 📞 Support & Documentation

### Log Files Location
- **Laravel Logs:** `storage/logs/laravel.log`
- **Web Server Logs:** `/var/log/apache2/` or `/var/log/nginx/`
- **MySQL Logs:** `/var/log/mysql/`

### Useful Commands
```bash
# Generate app key
php artisan key:generate

# Create symbolic link for storage
php artisan storage:link

# Clear all caches
php artisan optimize:clear

# Run queue jobs
php artisan queue:work

# Run scheduled tasks
php artisan schedule:run

# Database refresh
php artisan migrate:refresh --seed
```

### External Resources
- **Laravel Documentation:** https://laravel.com/docs
- **Vue.js Documentation:** https://vuejs.org/guide/
- **Bootstrap Documentation:** https://getbootstrap.com/docs/
- **PayPal Developer:** https://developer.paypal.com/
- **MailChimp API:** https://mailchimp.com/developer/

---

## 📋 Checklist

### Pre-deployment
- [ ] Environment variables configured
- [ ] Database credentials updated
- [ ] PayPal credentials configured (if needed)
- [ ] Mail settings configured
- [ ] Assets compiled for production
- [ ] Database migrated and seeded
- [ ] Storage permissions set correctly
- [ ] SSL certificate installed (production)

### Post-deployment
- [ ] Website loads correctly
- [ ] Admin panel accessible
- [ ] User registration/login works
- [ ] Product pages display properly
- [ ] Shopping cart functionality works
- [ ] Checkout process completes
- [ ] Email notifications sent
- [ ] File uploads work correctly
- [ ] Database backups scheduled

---

**🎯 Kết luận:**
Hệ thống Laravel E-Commerce này là một ứng dụng đầy đủ tính năng với quản lý sản phẩm, đơn hàng, thanh toán PayPal, và hệ thống quản trị mạnh mẽ. Tài liệu này cung cấp tất cả thông tin cần thiết để hiểu, triển khai và bảo trì hệ thống.
