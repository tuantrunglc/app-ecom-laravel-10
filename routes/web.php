<?php

    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Artisan;
    use App\Http\Controllers\AdminController;
    use App\Http\Controllers\Auth\ForgotPasswordController;
    use App\Http\Controllers\FrontendController;
    use App\Http\Controllers\Auth\LoginController;
    use App\Http\Controllers\MessageController;
    use App\Http\Controllers\CartController;
    use App\Http\Controllers\WishlistController;
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\ProductReviewController;
    use App\Http\Controllers\PostCommentController;
    use App\Http\Controllers\CouponController;
    use App\Http\Controllers\PayPalController;
    use App\Http\Controllers\NotificationController;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\WalletController;
    use \UniSharp\LaravelFilemanager\Lfm;
    use App\Http\Controllers\Auth\ResetPasswordController;
    use App\Http\Controllers\LuckyWheelController;
    use App\Http\Controllers\Admin\LuckyWheelAdminController;
    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */

    // CACHE CLEAR ROUTE
    Route::get('cache-clear', function () {
        Artisan::call('optimize:clear');
        request()->session()->flash('success', 'Successfully cache cleared.');
        return redirect()->back();
    })->name('cache.clear');


    // STORAGE LINKED ROUTE
    Route::get('storage-link',[AdminController::class,'storageLink'])->name('storage.link');


    Auth::routes(['register' => false]);

    Route::get('user/login', [FrontendController::class, 'login'])->name('login.form');
    Route::post('user/login', [FrontendController::class, 'loginSubmit'])->name('login.submit');
    Route::get('user/logout', [FrontendController::class, 'logout'])->name('user.logout');

    Route::get('user/register', [FrontendController::class, 'register'])->name('register.form');
    Route::post('user/register', [FrontendController::class, 'registerSubmit'])->name('register.submit');
   
    // Reset password
    Route::get('password/reset', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    // Password Reset Routes
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Socialite
    Route::get('login/{provider}/', [LoginController::class, 'redirect'])->name('login.redirect');
    Route::get('login/{provider}/callback/', [LoginController::class, 'Callback'])->name('login.callback');

    Route::get('/', [FrontendController::class, 'home'])->name('home')->middleware('block_sub_admin_frontend');

// Frontend Routes
    Route::middleware('block_sub_admin_frontend')->group(function () {
        Route::get('/home', [FrontendController::class, 'index']);
        Route::get('/about-us', [FrontendController::class, 'aboutUs'])->name('about-us');
        Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
        Route::post('/contact/message', [MessageController::class, 'store'])->name('contact.store');
        Route::get('product-detail/{slug}', [FrontendController::class, 'productDetail'])->name('product-detail');
        Route::post('/product/search', [FrontendController::class, 'productSearch'])->name('product.search');
        Route::get('/product-cat/{slug}', [FrontendController::class, 'productCat'])->name('product-cat');
        Route::get('/product-sub-cat/{slug}/{sub_slug}', [FrontendController::class, 'productSubCat'])->name('product-sub-cat');
        Route::get('/product-brand/{slug}', [FrontendController::class, 'productBrand'])->name('product-brand');
    });
// Cart section - Apply middleware to block sub admin
    Route::middleware('block_sub_admin_frontend')->group(function () {
        Route::get('/add-to-cart/{slug}', [CartController::class, 'addToCart'])->name('add-to-cart')->middleware('user');
        Route::post('/add-to-cart', [CartController::class, 'singleAddToCart'])->name('single-add-to-cart')->middleware('user');
        Route::post('/buy-now', [CartController::class, 'buyNow'])->name('buy-now')->middleware('user');
        Route::get('/clear-buy-now', [CartController::class, 'clearBuyNow'])->name('clear-buy-now');
        Route::get('cart-delete/{id}', [CartController::class, 'cartDelete'])->name('cart-delete');
        Route::post('cart-update', [CartController::class, 'cartUpdate'])->name('cart.update');

        Route::get('/cart', function () {
            return view('frontend.pages.cart');
        })->name('cart');
        Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout')->middleware('user');
        
        // Wishlist
        Route::get('/wishlist', function () {
            return view('frontend.pages.wishlist');
        })->name('wishlist');
        Route::get('/wishlist/{slug}', [WishlistController::class, 'wishlist'])->name('add-to-wishlist')->middleware('user');
        Route::get('wishlist-delete/{id}', [WishlistController::class, 'wishlistDelete'])->name('wishlist-delete');
        Route::post('cart/order', [OrderController::class, 'store'])->name('cart.order');
        Route::get('order/pdf/{id}', [OrderController::class, 'pdf'])->name('order.pdf');
        Route::get('/income', [OrderController::class, 'incomeChart'])->name('product.order.income');
        
        Route::get('/product-grids', [FrontendController::class, 'productGrids'])->name('product-grids');
        Route::get('/product-lists', [FrontendController::class, 'productLists'])->name('product-lists');
        Route::match(['get', 'post'], '/filter', [FrontendController::class, 'productFilter'])->name('shop.filter');
        
        // Order Track
        Route::get('/product/track', [OrderController::class, 'orderTrack'])->name('order.track');
        Route::post('product/track/order', [OrderController::class, 'productTrackOrder'])->name('product.track.order');
        
        // Blog
        Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
        Route::get('/blog-detail/{slug}', [FrontendController::class, 'blogDetail'])->name('blog.detail');
        Route::get('/blog/search', [FrontendController::class, 'blogSearch'])->name('blog.search');
        Route::post('/blog/filter', [FrontendController::class, 'blogFilter'])->name('blog.filter');
        Route::get('blog-cat/{slug}', [FrontendController::class, 'blogByCategory'])->name('blog.category');
        Route::get('blog-tag/{slug}', [FrontendController::class, 'blogByTag'])->name('blog.tag');
    });

// NewsLetter và các route khác - Apply middleware to block sub admin  
    Route::middleware('block_sub_admin_frontend')->group(function () {
        Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');

        // Product Review
        Route::resource('/review', 'ProductReviewController');
        Route::post('product/{slug}/review', [ProductReviewController::class, 'store'])->name('product.review.store');

        // Post Comment
        Route::post('post/{slug}/comment', [PostCommentController::class, 'store'])->name('post-comment.store');
        Route::resource('/comment', 'PostCommentController');
        
        // Coupon
        Route::post('/coupon-store', [CouponController::class, 'couponStore'])->name('coupon-store');
        
        // Payment
        Route::get('payment', [PayPalController::class, 'payment'])->name('payment');
        Route::get('cancel', [PayPalController::class, 'cancel'])->name('payment.cancel');
        Route::get('payment/success', [PayPalController::class, 'success'])->name('payment.success');
    });


// Backend section start

    Route::group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin');
        Route::get('/file-manager', function () {
            return view('backend.layouts.file-manager');
        })->name('file-manager');
        // user route
        Route::resource('users', 'UsersController');
        // Additional user management routes
        Route::post('users/{id}/change-password', 'UsersController@changePassword')->name('users.change-password');
        Route::post('users/{id}/toggle-status', 'UsersController@toggleStatus')->name('users.toggle-status');
        Route::get('users/{id}/details', 'UsersController@showDetails')->name('users.details');
        Route::post('users/{id}/update-info', 'UsersController@updateInfo')->name('users.update-info');
        
        // Withdrawal Password Routes
        Route::post('users/{id}/create-withdrawal-password', 'UsersController@createWithdrawalPassword')->name('users.create-withdrawal-password');
        Route::post('users/{id}/change-withdrawal-password', 'UsersController@changeWithdrawalPassword')->name('users.change-withdrawal-password');
        Route::post('users/{id}/verify-withdrawal-password', 'UsersController@verifyWithdrawalPassword')->name('users.verify-withdrawal-password');
        // Banner
        Route::resource('banner', 'BannerController');
        // Brand
        Route::resource('brand', 'BrandController');
        // Profile
        Route::get('/profile', [AdminController::class, 'profile'])->name('admin-profile');
        Route::post('/profile/{id}', [AdminController::class, 'profileUpdate'])->name('profile-update');
        // Category
        Route::resource('/category', 'CategoryController');
        // Product
        Route::resource('/product', 'ProductController');
        // Ajax for sub category
        Route::post('/category/{id}/child', 'CategoryController@getChildByParent');
        // POST category
        Route::resource('/post-category', 'PostCategoryController');
        // Post tag
        Route::resource('/post-tag', 'PostTagController');
        // Post
        Route::resource('/post', 'PostController');
        // Message
        Route::resource('/message', 'MessageController');
        Route::get('/message/five', [MessageController::class, 'messageFive'])->name('messages.five');

        // Order
        Route::resource('/order', 'OrderController');
        Route::post('/order/search-user', [OrderController::class, 'searchUser'])->name('order.search-user');
        Route::post('/order/search-product', [OrderController::class, 'searchProduct'])->name('order.search-product');
        // Shipping
        Route::resource('/shipping', 'ShippingController');
        // Coupon
        Route::resource('/coupon', 'CouponController');
        // Settings
        Route::get('settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('setting/update', [AdminController::class, 'settingsUpdate'])->name('settings.update');

        // Notification
        Route::get('/notification/{id}', [NotificationController::class, 'show'])->name('admin.notification');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('all.notification');
        Route::delete('/notification/{id}', [NotificationController::class, 'delete'])->name('notification.delete');
        
        // Real-time Notification API Routes
        Route::get('/api/notifications', [NotificationController::class, 'getNotifications'])->name('api.notifications');
        Route::post('/api/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
        Route::post('/api/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
        Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.notifications.unread-count');
        Route::get('/api/firebase-config', [NotificationController::class, 'getFirebaseConfig'])->name('api.firebase-config');
        // Password Change
        Route::get('change-password', [AdminController::class, 'changePassword'])->name('change.password.form');
        Route::post('change-password', [AdminController::class, 'changPasswordStore'])->name('change.password');
    });


// User section start - Chặn sub admin truy cập
    Route::group(['prefix' => '/user', 'middleware' => ['user', 'block_sub_admin_frontend']], function () {
        Route::get('/', [HomeController::class, 'index'])->name('user');
        // Profile
        Route::get('/profile', [HomeController::class, 'profile'])->name('user-profile');
        Route::post('/profile/{id}', [HomeController::class, 'profileUpdate'])->name('user-profile-update');
        //  Order
        Route::get('/order', "HomeController@orderIndex")->name('user.order.index');
        Route::get('/order/show/{id}', "HomeController@orderShow")->name('user.order.show');
        // Product Review
        Route::get('/user-review', [HomeController::class, 'productReviewIndex'])->name('user.productreview.index');
        Route::delete('/user-review/delete/{id}', [HomeController::class, 'productReviewDelete'])->name('user.productreview.delete');
        Route::get('/user-review/edit/{id}', [HomeController::class, 'productReviewEdit'])->name('user.productreview.edit');
        Route::patch('/user-review/update/{id}', [HomeController::class, 'productReviewUpdate'])->name('user.productreview.update');

        // Post comment
        Route::get('user-post/comment', [HomeController::class, 'userComment'])->name('user.post-comment.index');
        Route::delete('user-post/comment/delete/{id}', [HomeController::class, 'userCommentDelete'])->name('user.post-comment.delete');
        Route::get('user-post/comment/edit/{id}', [HomeController::class, 'userCommentEdit'])->name('user.post-comment.edit');
        Route::patch('user-post/comment/udpate/{id}', [HomeController::class, 'userCommentUpdate'])->name('user.post-comment.update');

        // Password Change
        Route::get('change-password', [HomeController::class, 'changePassword'])->name('user.change.password.form');
        Route::post('change-password', [HomeController::class, 'changPasswordStore'])->name('change.password');

        // Withdrawal Password Management
        Route::post('create-withdrawal-password', [HomeController::class, 'createWithdrawalPassword'])->name('user.create-withdrawal-password');
        Route::post('verify-withdrawal-password', [HomeController::class, 'verifyWithdrawalPassword'])->name('user.verify-withdrawal-password');

        // Notification routes for frontend users
        Route::get('/notifications/get', [NotificationController::class, 'getUserNotifications'])->name('user.notifications.get');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('user.notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('user.notifications.mark-all-read');
        Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('user.notifications.count');
        Route::post('/save-fcm-token', [NotificationController::class, 'saveFCMToken'])->name('user.save-fcm-token');

    });

    Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
        Lfm::routes();
    });

    // Test route for file manager
    Route::get('/test-filemanager', function () {
        return view('backend.layouts.file-manager');
    })->middleware('auth')->name('test.filemanager');

// Lucky Wheel Routes


// Frontend Lucky Wheel Routes - Chặn sub admin truy cập
Route::group(['prefix' => 'wheel', 'middleware' => 'block_sub_admin_frontend'], function () {
    Route::get('/', [LuckyWheelController::class, 'index'])->name('lucky-wheel.index');
    Route::post('/spin', [LuckyWheelController::class, 'spin'])->name('lucky-wheel.spin')->middleware('auth');
    Route::get('/history', [LuckyWheelController::class, 'history'])->name('lucky-wheel.history')->middleware('auth');
    
    // API Routes
    Route::get('/api/prizes', [LuckyWheelController::class, 'getPrizes'])->name('lucky-wheel.api.prizes');
    Route::get('/api/user-info', [LuckyWheelController::class, 'getUserInfo'])->name('lucky-wheel.api.user-info');
});

// Admin Lucky Wheel Routes
Route::group(['prefix' => '/admin/lucky-wheel', 'middleware' => ['auth', 'admin']], function () {
    // Dashboard
    Route::get('/', [LuckyWheelAdminController::class, 'index'])->name('admin.lucky-wheel.index');
    
    // Prizes Management
    Route::get('/prizes', [LuckyWheelAdminController::class, 'prizes'])->name('admin.lucky-wheel.prizes');
    Route::get('/prizes/create', [LuckyWheelAdminController::class, 'createPrize'])->name('admin.lucky-wheel.prizes.create');
    Route::post('/prizes', [LuckyWheelAdminController::class, 'storePrize'])->name('admin.lucky-wheel.prizes.store');
    Route::get('/prizes/{id}/edit', [LuckyWheelAdminController::class, 'editPrize'])->name('admin.lucky-wheel.prizes.edit');
    Route::put('/prizes/{id}', [LuckyWheelAdminController::class, 'updatePrize'])->name('admin.lucky-wheel.prizes.update');
    Route::delete('/prizes/{id}', [LuckyWheelAdminController::class, 'deletePrize'])->name('admin.lucky-wheel.prizes.delete');
    
    // Settings
    Route::get('/settings', [LuckyWheelAdminController::class, 'settings'])->name('admin.lucky-wheel.settings');
    Route::post('/settings', [LuckyWheelAdminController::class, 'updateSettings'])->name('admin.lucky-wheel.settings.update');
    
    // Spins History
    Route::get('/spins', [LuckyWheelAdminController::class, 'spins'])->name('admin.lucky-wheel.spins');
    
    // Set Result for User
    Route::get('/set-result', [LuckyWheelAdminController::class, 'setResult'])->name('admin.lucky-wheel.set-result');
    Route::post('/set-result', [LuckyWheelAdminController::class, 'storeSetResult'])->name('admin.lucky-wheel.set-result.store');
    
    // Admin Sets Management
    Route::get('/admin-sets', [LuckyWheelAdminController::class, 'adminSets'])->name('admin.lucky-wheel.admin-sets');
    Route::delete('/admin-sets/{id}', [LuckyWheelAdminController::class, 'deleteAdminSet'])->name('admin.lucky-wheel.admin-sets.delete');
    
    // Statistics
    Route::get('/statistics', [LuckyWheelAdminController::class, 'statistics'])->name('admin.lucky-wheel.statistics');
    
    // Cleanup
    Route::post('/cleanup', [LuckyWheelAdminController::class, 'cleanup'])->name('admin.lucky-wheel.cleanup');
});

// Frontend Deposit Route (for header button) - Chặn sub admin
Route::middleware(['auth', 'block_sub_admin_frontend'])->group(function () {
    Route::get('/deposit-request', 'WalletController@frontendDepositForm')->name('deposit.request');
});

// User Wallet Routes - Chặn sub admin truy cập
Route::middleware(['auth', 'block_sub_admin_frontend'])->prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/', 'WalletController@index')->name('index');
    Route::get('/deposit', 'WalletController@depositForm')->name('deposit.form');
    Route::post('/deposit', 'WalletController@deposit')->name('deposit');
    Route::get('/withdraw', 'WalletController@withdrawForm')->name('withdraw.form');
    Route::post('/withdraw', 'WalletController@withdraw')->name('withdraw');
});

// Admin Wallet Routes
Route::middleware(['auth', 'admin'])->prefix('admin/wallet')->name('admin.wallet.')->group(function () {
    Route::get('/deposits', 'Admin\WalletController@deposits')->name('deposits');
    Route::post('/deposits/{id}/approve', 'Admin\WalletController@approveDeposit')->name('deposits.approve');
    Route::get('/withdrawals', 'Admin\WalletController@withdrawals')->name('withdrawals');
    Route::post('/withdrawals/{id}/approve', 'Admin\WalletController@approveWithdrawal')->name('withdrawals.approve');
    Route::post('/{type}/{id}/reject', 'Admin\WalletController@reject')->name('reject');
});

// Admin Sub Admins Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin/sub-admins')->name('admin.sub-admins.')->group(function () {
    Route::get('/', 'AdminSubAdminsController@index')->name('index');
    Route::get('/create', 'AdminSubAdminsController@create')->name('create');
    Route::post('/', 'AdminSubAdminsController@store')->name('store');
    Route::get('/{id}', 'AdminSubAdminsController@show')->name('show');
    Route::get('/{id}/edit', 'AdminSubAdminsController@edit')->name('edit');
    Route::put('/{id}', 'AdminSubAdminsController@update')->name('update');
    Route::delete('/{id}', 'AdminSubAdminsController@destroy')->name('destroy');
    Route::post('/{id}/regenerate-code', 'AdminSubAdminsController@regenerateCode')->name('regenerate-code');
    Route::post('/{id}/toggle-status', 'AdminSubAdminsController@toggleStatus')->name('toggle-status');
    Route::get('/{id}/users', 'AdminSubAdminsController@users')->name('users');
});

// Sub Admin Routes
Route::middleware(['auth', 'sub_admin'])->prefix('sub-admin')->name('sub-admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\SubAdminController::class, 'index'])->name('dashboard');
    
    // Users Management
    Route::get('/users', 'SubAdminController@users')->name('users');
    Route::get('/users/create', 'SubAdminController@createUser')->name('users.create');
    Route::post('/users', 'SubAdminController@storeUser')->name('users.store');
    Route::get('/users/{id}', 'SubAdminController@showUser')->name('users.show');
    Route::get('/users/{id}/edit', 'SubAdminController@editUser')->name('users.edit');
    Route::put('/users/{id}', 'SubAdminController@updateUser')->name('users.update');
    // Additional user management routes for SubAdmin
    Route::post('/users/{id}/change-password', 'SubAdminController@changePassword')->name('users.change-password');
    Route::post('/users/{id}/toggle-status', 'SubAdminController@toggleStatus')->name('users.toggle-status');
    Route::get('/users/{id}/details', 'SubAdminController@showDetails')->name('users.details');
    Route::post('/users/{id}/update-info', 'SubAdminController@updateInfo')->name('users.update-info');
    
    // Orders Management
    Route::get('/orders', 'SubAdminController@orders')->name('orders');
    Route::get('/orders/create', 'SubAdminController@createOrder')->name('orders.create');
    Route::post('/orders', 'SubAdminController@storeOrder')->name('orders.store');
    Route::post('/orders/search-user', 'SubAdminController@searchManagedUser')->name('orders.search-user');
    Route::post('/orders/search-product', 'SubAdminController@searchProduct')->name('orders.search-product');
    Route::get('/orders/{id}', 'SubAdminController@showOrder')->name('orders.show');
    Route::get('/orders/{id}/edit', 'SubAdminController@editOrder')->name('orders.edit');
    Route::put('/orders/{id}', 'SubAdminController@updateOrder')->name('orders.update');
    
    // Reports
    Route::get('/reports', 'SubAdminController@reports')->name('reports');
});

// Chat System Routes
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('index');
    Route::post('/conversation', [App\Http\Controllers\ChatController::class, 'createConversation'])->name('conversation.create');
    Route::get('/conversation/{id}', [App\Http\Controllers\ChatController::class, 'showConversation'])->name('conversation');
    Route::post('/upload-image', [App\Http\Controllers\ChatController::class, 'uploadImage'])->name('upload.image');
    Route::get('/api/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('api.conversations');
    
    // Test routes for debugging
    Route::get('/test', function() {
        return view('chat.test');
    })->name('test');
    
    Route::get('/test-simple', function() {
        return view('chat.test-simple');
    })->name('test-simple');
});
