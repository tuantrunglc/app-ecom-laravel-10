<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\SubAdminSettings;
use App\Models\SubAdminUserStats;
use App\Models\Order;  
use App\Models\Shipping;
use App\Models\WalletTransaction;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseNotificationService;
use App\Notifications\StatusNotification;
use Notification;

class SubAdminController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->middleware(['auth', 'sub_admin']);
        $this->firebaseService = $firebaseService;
    }

    // Dashboard Sub Admin
    public function index()
    {
        $subAdmin = auth()->user();
        
        // Cập nhật stats trước khi hiển thị
        $this->updateSubAdminStats($subAdmin->id);
        
        $stats = $this->getSubAdminStats($subAdmin->id);
        
        return view('backend.sub-admin.dashboard', compact('subAdmin', 'stats'));
    }

    // Quản lý users thuộc quyền
    public function users()
    {
        $subAdmin = auth()->user();
        $users = $subAdmin->managedUsers()->paginate(10);
        
        return view('backend.sub-admin.users.index', compact('users', 'subAdmin'));
    }

    // Tạo user mới (nếu có quyền)
    public function createUser()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_create_users) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo user mới');
        }
        
        return view('backend.sub-admin.users.create');
    }

    // Lưu user mới
    public function storeUser(Request $request)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_create_users) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo user mới');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'active',
            'parent_sub_admin_id' => $subAdmin->id,
            'created_by' => $subAdmin->id,
        ]);

        $this->updateSubAdminStats($subAdmin->id);

        return redirect()->route('sub-admin.users')->with('success', 'Tạo user thành công');
    }

    // Xem chi tiết user
    public function showUser($id)
    {
        $subAdmin = auth()->user();
        $user = $subAdmin->managedUsers()->findOrFail($id);
        
        return view('backend.sub-admin.users.show', compact('user'));
    }

    // Sửa user
    public function editUser($id)
    {
        $subAdmin = auth()->user();
        $user = $subAdmin->managedUsers()->findOrFail($id);
        
        return view('backend.sub-admin.users.edit', compact('user'));
    }

    // Cập nhật user
    public function updateUser(Request $request, $id)
    {
        $subAdmin = auth()->user();
        $user = $subAdmin->managedUsers()->findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($request->only(['name', 'email', 'status']));

        return redirect()->route('sub-admin.users')->with('success', 'Cập nhật user thành công');
    }

    // Quản lý đơn hàng
    public function orders()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền quản lý đơn hàng');
        }
        
        $orders = $this->getManagedOrders($subAdmin->id)->paginate(15);
        
        return view('backend.sub-admin.orders.index', compact('orders', 'subAdmin'));
    }

    // Xem chi tiết đơn hàng
    public function showOrder($orderId)
    {
        $subAdmin = auth()->user();
        $order = $this->authorizeOrderAccess($orderId);
        
        return view('backend.sub-admin.orders.show', compact('order'));
    }

    // Sửa trạng thái đơn hàng
    public function editOrder($orderId)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa đơn hàng');
        }
        
        $order = $this->authorizeOrderAccess($orderId);
        
        return view('backend.sub-admin.orders.edit', compact('order'));
    }

    // Cập nhật đơn hàng
    public function updateOrder(Request $request, $orderId)
    {
        $subAdmin = auth()->user();
        $order = $this->authorizeOrderAccess($orderId);

        $this->validate($request, [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'cancel_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        // Kiểm tra logic chuyển trạng thái
        if (!$this->canUpdateOrderStatus($order->status, $request->status)) {
            return redirect()->back()->with('error', 'Không thể chuyển từ trạng thái ' . $order->status . ' sang ' . $request->status);
        }

        $oldStatus = $order->status;
        
        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number,
            'notes' => $request->notes,
            'cancel_reason' => $request->cancel_reason,
        ]);

        // Process commission if order status changed to delivered
        if($request->status == 'delivered' && $oldStatus != 'delivered') {
            $this->processCommission($order);
        }

        return redirect()->route('sub-admin.orders')->with('success', 'Cập nhật đơn hàng thành công');
    }

    // Tạo đơn hàng mới cho user thuộc quyền quản lý
    public function createOrder()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo đơn hàng');
        }
        
        // Lấy danh sách users thuộc quyền quản lý
        $managedUsers = $subAdmin->managedUsers()->where('status', 'active')->get();
        
        // Lấy danh sách phương thức vận chuyển
        $shippings = Shipping::where('status', 'active')->get();
        
        return view('backend.sub-admin.orders.create', compact('managedUsers', 'subAdmin', 'shippings'));
    }

    // Tìm kiếm user thuộc quyền quản lý
    public function searchManagedUser(Request $request)
    {
        $subAdmin = auth()->user();
        $search = $request->search;
        
        // Chỉ tìm trong users thuộc quyền quản lý
        $user = $subAdmin->managedUsers()
                         ->where(function($query) use ($search) {
                             $query->where('id', $search)
                                   ->orWhere('email', 'like', '%' . $search . '%');
                         })
                         ->where('status', 'active')
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
        
        return response()->json([
            'success' => false,
            'message' => 'User không tìm thấy hoặc không thuộc quyền quản lý của bạn'
        ]);
    }

    // Tìm kiếm sản phẩm
    public function searchProduct(Request $request)
    {
        $search = $request->search;
        
        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập từ khóa tìm kiếm'
            ]);
        }
        
        // Tìm kiếm sản phẩm theo tên, có trạng thái active và còn hàng
        $products = Product::where('status', 'active')
                          ->where('stock', '>', 0)
                          ->where(function($query) use ($search) {
                              $query->where('title', 'like', '%' . $search . '%')
                                    ->orWhere('slug', 'like', '%' . $search . '%');
                          })
                          ->select('id', 'title', 'price', 'discount', 'stock', 'photo')
                          ->limit(10)
                          ->get();
        
        if ($products->count() > 0) {
            $productsData = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'discount' => $product->discount ?? 0,
                    'stock' => $product->stock,
                    'photo' => $product->getFirstPhoto() ? asset($product->getFirstPhoto()) : null
                ];
            });
            
            return response()->json([
                'success' => true,
                'products' => $productsData
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm nào'
        ]);
    }

    // Lưu đơn hàng mới
    public function storeOrder(Request $request)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo đơn hàng');
        }

        $this->validate($request,[
            'user_id'=>'required|exists:users,id',
            'first_name'=>'nullable|string|max:255',
            'last_name'=>'nullable|string|max:255',
            'address1'=>'nullable|string|max:500',
            'address2'=>'nullable|string|max:500',
            'country'=>'nullable|string|max:255',
            'phone'=>'nullable|string|max:20',
            'post_code'=>'nullable|string|max:20',
            'email'=>'nullable|email|max:255',
            'shipping'=>'required|exists:shippings,id',
            'payment_method'=>'required|in:wallet',
            'status'=>'required|in:new,process,delivered,cancel',
            'quantity'=>'required|integer|min:1',
            'sub_total'=>'required|numeric|min:0|max:999999.99',
            'total_amount'=>'required|numeric|min:0|max:999999.99',
            'products_data'=>'required|string'
        ], [
            'user_id.required' => 'Vui lòng chọn user',
            'user_id.exists' => 'User không tồn tại',
            'shipping.required' => 'Vui lòng chọn phương thức vận chuyển',
            'shipping.exists' => 'Phương thức vận chuyển không tồn tại',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ',
            'quantity.required' => 'Tổng số lượng là bắt buộc',
            'quantity.integer' => 'Tổng số lượng phải là số nguyên',
            'quantity.min' => 'Tổng số lượng phải ít nhất là 1',
            'sub_total.required' => 'Vui lòng nhập tổng phụ',
            'sub_total.numeric' => 'Tổng phụ phải là số',
            'sub_total.min' => 'Tổng phụ không được nhỏ hơn 0',
            'sub_total.max' => 'Tổng phụ không được vượt quá 999,999.99',
            'total_amount.required' => 'Vui lòng nhập tổng cộng',
            'total_amount.numeric' => 'Tổng cộng phải là số',
            'total_amount.min' => 'Tổng cộng không được nhỏ hơn 0',
            'total_amount.max' => 'Tổng cộng không được vượt quá 999,999.99',
            'products_data.required' => 'Vui lòng chọn ít nhất một sản phẩm'
        ]);

        // Kiểm tra user có thuộc quyền quản lý không
        $user = $subAdmin->managedUsers()->where('id', $request->user_id)->first();
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'User không thuộc quyền quản lý của bạn');
        }

        // Kiểm tra user có active không
        if ($user->status !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Không thể tạo đơn hàng cho user không hoạt động');
        }

        // Parse products data
        $productsData = json_decode($request->products_data, true);
        if (!$productsData || !is_array($productsData) || empty($productsData)) {
            return redirect()->back()->withInput()->with('error', 'Dữ liệu sản phẩm không hợp lệ');
        }

        // Validate products and calculate totals
        $calculatedSubTotal = 0;
        $totalQuantity = 0;
        $validatedProducts = [];

        foreach ($productsData as $productData) {
            $product = Product::where('id', $productData['id'])
                             ->where('status', 'active')
                             ->first();
            
            if (!$product) {
                return redirect()->back()->withInput()->with('error', 'Sản phẩm ID ' . $productData['id'] . ' không tồn tại hoặc không hoạt động');
            }

            if ($product->stock < $productData['quantity']) {
                return redirect()->back()->withInput()->with('error', 'Sản phẩm "' . $product->title . '" không đủ hàng trong kho');
            }

            // Calculate final price with discount
            $finalPrice = $product->price;
            if ($product->discount > 0) {
                $finalPrice = $product->price - ($product->price * $product->discount / 100);
            }

            $validatedProducts[] = [
                'product' => $product,
                'quantity' => $productData['quantity'],
                'price' => $finalPrice,
                'amount' => $finalPrice * $productData['quantity']
            ];

            $calculatedSubTotal += $finalPrice * $productData['quantity'];
            $totalQuantity += $productData['quantity'];
        }

        // Verify calculated totals
        if (abs($calculatedSubTotal - $request->sub_total) > 0.01) {
            return redirect()->back()->withInput()->with('error', 'Tổng phụ không chính xác. Tính toán: ' . number_format($calculatedSubTotal, 2) . ' VND');
        }

        // Kiểm tra logic của total_amount
        $shipping = Shipping::find($request->shipping);
        $expectedTotal = $calculatedSubTotal + ($shipping ? $shipping->price : 0);
        
        if (abs($request->total_amount - $expectedTotal) > 0.01) {
            return redirect()->back()->withInput()->with('error', 'Tổng tiền không chính xác. Vui lòng kiểm tra lại.');
        }

        // Check wallet balance if payment method is wallet
        if ($request->payment_method == 'wallet') {
            $totalAmount = $request->total_amount;
            $currentBalance = $user->wallet_balance ?? 0;
            
            if ($currentBalance < $totalAmount) {
                $shortfall = $totalAmount - $currentBalance;
                
                // Send Firebase notification to user about insufficient wallet balance
                $this->firebaseService->sendInsufficientWalletNotification(
                    $user, 
                    $currentBalance, 
                    $totalAmount, 
                    $shortfall
                );
                
                // Also send traditional notification as backup
                $details = [
                    'title' => 'Order Failed - Insufficient Wallet Balance - Your balance: $' . number_format($currentBalance, 2) . ', Required: $' . number_format($totalAmount, 2) . '. Please add $' . number_format($shortfall, 2) . ' to your wallet.',
                    'actionURL' => route('wallet.index'),
                    'fas' => 'fas fa-exclamation-triangle'
                ];
                
                Notification::send($user, new StatusNotification($details));
                
                // Flash message for sub-admin
                request()->session()->flash('error','Cannot create order for user "' . $user->name . '". Insufficient wallet balance! User balance: $' . number_format($currentBalance, 2) . ', Required: $' . number_format($totalAmount, 2) . '. The user has been notified to add $' . number_format($shortfall, 2) . ' to their wallet.');
                
                return redirect()->back()->withInput();
            }
        }

        // Start transaction
        DB::beginTransaction();
        
        try {
            // Chuẩn bị data để tạo order
            $data = $request->only([
                'user_id', 'first_name', 'last_name', 'email', 'phone', 'country',
                'address1', 'address2', 'post_code', 'payment_method', 'status',
                'sub_total', 'total_amount'
            ]);
            
            // Add calculated quantity
            $data['quantity'] = $totalQuantity;
            
            // Mapping shipping field
            $data['shipping_id'] = $request->shipping;
            $data['order_number'] = 'ORD-'.strtoupper(uniqid());
            $data['payment_status'] = 'unpaid'; // Mặc định là chưa thanh toán
            
            $order = Order::create($data);
            
            if (!$order) {
                throw new \Exception('Không thể tạo đơn hàng');
            }

            // Trigger real-time notification event
            $orderUser = User::find($request->user_id);
            if ($orderUser) {
                event(new \App\Events\OrderCreated($order, $orderUser));
            }

            // Create cart items for each product
            foreach ($validatedProducts as $productInfo) {
                Cart::create([
                    'user_id' => $request->user_id,
                    'product_id' => $productInfo['product']->id,
                    'order_id' => $order->id,
                    'quantity' => $productInfo['quantity'],
                    'price' => $productInfo['price'],
                    'amount' => $productInfo['amount'],
                    'status' => 'new'
                ]);

                // Update product stock
                $productInfo['product']->decrement('stock', $productInfo['quantity']);
            }

            DB::commit();
            request()->session()->flash('success','Đơn hàng đã được tạo thành công với ' . count($validatedProducts) . ' sản phẩm');
            
        } catch (\Exception $e) {
            DB::rollback();
            request()->session()->flash('error','Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage());
        }
        
        return redirect()->route('sub-admin.orders');
    }

    // Báo cáo
    public function reports()
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_view_reports) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem báo cáo');
        }
        
        $stats = $this->getDetailedStats($subAdmin->id);
        
        return view('backend.sub-admin.reports', compact('stats'));
    }

    // Helper methods
    private function getManagedOrders($subAdminId)
    {
        return Order::whereHas('user', function($query) use ($subAdminId) {
            $query->where('parent_sub_admin_id', $subAdminId);
        })->with(['user'])->orderBy('created_at', 'desc');
    }

    private function authorizeOrderAccess($orderId)
    {
        $subAdmin = auth()->user();
        
        $order = Order::whereHas('user', function($query) use ($subAdmin) {
            $query->where('parent_sub_admin_id', $subAdmin->id);
        })->findOrFail($orderId);
        
        return $order;
    }

    private function canUpdateOrderStatus($currentStatus, $newStatus)
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => ['returned'],
            'cancelled' => [],
            'returned' => []
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    private function getSubAdminStats($subAdminId)
    {
        $subAdmin = User::findOrFail($subAdminId);
        
        $totalUsers = $subAdmin->getManagedUsersCount();
        $activeUsers = $subAdmin->getActiveUsersCount();
        $inactiveUsers = $totalUsers - $activeUsers;
        
        $totalOrders = $this->getManagedOrders($subAdminId)->count();
        
        $totalRevenue = $this->getManagedOrders($subAdminId)
            ->where('status', 'delivered')
            ->sum('total_amount');

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
        ];
    }

    private function getDetailedStats($subAdminId)
    {
        $basicStats = $this->getSubAdminStats($subAdminId);
        
        // Thêm thống kê chi tiết khác nếu cần
        $basicStats['orders_this_month'] = $this->getManagedOrders($subAdminId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $basicStats['revenue_this_month'] = $this->getManagedOrders($subAdminId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'delivered')
            ->sum('total_amount');

        return $basicStats;
    }

    private function updateSubAdminStats($subAdminId)
    {
        $stats = $this->getSubAdminStats($subAdminId);
        
        SubAdminUserStats::updateOrCreate(
            ['sub_admin_id' => $subAdminId],
            [
                'total_users' => $stats['total_users'],
                'active_users' => $stats['active_users'],
                'inactive_users' => $stats['inactive_users'],
                'total_orders' => $stats['total_orders'],
                'total_revenue' => $stats['total_revenue'],
                'last_updated' => now(),
            ]
        );
    }

    /**
     * Process commission when order is delivered
     */
    private function processCommission($order)
    {
        $user = $order->user;
        $totalCommission = 0;
        $commissionDetails = [];

        // Calculate commission for each product in the order
        foreach($order->cart as $cart) {
            $product = $cart->product;
            if($product && $product->commission > 0) {
                // Calculate commission amount: (product price * quantity * commission percentage / 100)
                $commissionAmount = ($cart->price * $cart->quantity * $product->commission) / 100;
                $totalCommission += $commissionAmount;
                
                $commissionDetails[] = [
                    'product' => $product->title,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'commission_rate' => $product->commission,
                    'commission_amount' => $commissionAmount
                ];
            }
        }

        // Add commission to user's wallet if there's any commission
        if($totalCommission > 0) {
            $balanceBefore = $user->wallet_balance ?? 0;
            $balanceAfter = $balanceBefore + $totalCommission;
            
            // Update user's wallet balance
            $user->wallet_balance = $balanceAfter;
            $user->save();

            // Create wallet transaction record
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'commission',
                'amount' => $totalCommission,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Product commission from order #' . $order->order_number . '. Details: ' . json_encode($commissionDetails),
                'status' => 'completed'
            ]);

            // Update sub admin stats if user has parent sub admin
            if($user->parent_sub_admin_id) {
                $this->updateSubAdminCommissionStats($user->parent_sub_admin_id, $totalCommission);
            }
        }
    }

    /**
     * Update sub admin commission statistics
     */
    private function updateSubAdminCommissionStats($subAdminId, $commissionAmount)
    {
        $subAdminStats = SubAdminUserStats::firstOrCreate(
            ['sub_admin_id' => $subAdminId],
            [
                'total_users' => 0,
                'active_users' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'commission_earned' => 0,
                'last_updated' => now()
            ]
        );

        $subAdminStats->commission_earned += $commissionAmount;
        $subAdminStats->last_updated = now();
        $subAdminStats->save();
    }

    /**
     * Change user password (SubAdmin version)
     */
    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check permission - SubAdmin can only manage their own users
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền thay đổi mật khẩu user này'], 403);
        }

        $this->validate($request, [
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = Hash::make($request->new_password);
        $status = $user->save();

        if ($status) {
            return response()->json(['success' => 'Đã thay đổi mật khẩu thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi thay đổi mật khẩu'], 500);
        }
    }

    /**
     * Toggle user account status (SubAdmin version)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check permission - SubAdmin can only manage their own users
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền thay đổi trạng thái user này'], 403);
        }

        // Toggle status
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->status = $newStatus;
        $status = $user->save();

        if ($status) {
            $message = $newStatus === 'active' ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản';
            return response()->json([
                'success' => $message,
                'new_status' => $newStatus,
                'status_badge' => $newStatus === 'active' 
                    ? '<span class="badge badge-success">active</span>' 
                    : '<span class="badge badge-warning">inactive</span>'
            ]);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi thay đổi trạng thái'], 500);
        }
    }

    /**
     * Show user details for editing (SubAdmin version)
     */
    public function showDetails($id)
    {
        $user = User::findOrFail($id);
        
        // Check permission - SubAdmin can only manage their own users
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền xem thông tin user này'], 403);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'photo' => $user->photo,
                'wallet_balance' => $user->wallet_balance,
                'birth_date' => $user->birth_date,
                'age' => $user->age,
                'gender' => $user->gender,
                'address' => $user->address,
                'bank_name' => $user->bank_name,
                'bank_account_number' => $user->bank_account_number,
                'bank_account_name' => $user->bank_account_name,
                'created_at' => $user->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Update user information via AJAX (SubAdmin version)
     */
    public function updateInfo(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check permission - SubAdmin can only manage their own users
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền chỉnh sửa user này'], 403);
        }

        $this->validate($request, [
            'name' => 'string|required|max:30',
            'email' => 'string|required|email|unique:users,email,' . $id,
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer|min:1|max:120',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
        ]);

        $data = $request->only([
            'name', 'email', 'birth_date', 'age', 'gender', 
            'address', 'bank_name', 'bank_account_number', 'bank_account_name'
        ]);

        $status = $user->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Đã cập nhật thông tin thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi cập nhật thông tin'], 500);
        }
    }
}
