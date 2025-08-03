<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\SubAdminSettings;
use App\Models\SubAdminUserStats;
use App\Models\Order;  
use App\Models\Shipping;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SubAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'sub_admin']);
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

        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number,
            'notes' => $request->notes,
            'cancel_reason' => $request->cancel_reason,
        ]);

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

    // Lưu đơn hàng mới
    public function storeOrder(Request $request)
    {
        $subAdmin = auth()->user();
        
        if (!$subAdmin->subAdminSettings->can_manage_orders) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo đơn hàng');
        }

        $this->validate($request,[
            'user_id'=>'required|exists:users,id',
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'address1'=>'required|string|max:500',
            'address2'=>'nullable|string|max:500',
            'country'=>'required|string|max:255',
            'phone'=>'required|string|max:20',
            'post_code'=>'nullable|string|max:20',
            'email'=>'required|email|max:255',
            'shipping'=>'required|exists:shippings,id',
            'payment_method'=>'required|in:wallet',
            'status'=>'required|in:new,process,delivered,cancel',
            'sub_total'=>'required|numeric|min:0|max:999999.99',
            'quantity'=>'required|integer|min:1|max:1000',
            'total_amount'=>'required|numeric|min:0|max:999999.99'
        ], [
            'user_id.required' => 'Vui lòng chọn user',
            'user_id.exists' => 'User không tồn tại',
            'first_name.required' => 'Vui lòng nhập họ',
            'first_name.max' => 'Họ không được vượt quá 255 ký tự',
            'last_name.required' => 'Vui lòng nhập tên',
            'last_name.max' => 'Tên không được vượt quá 255 ký tự',
            'address1.required' => 'Vui lòng nhập địa chỉ chính',
            'address1.max' => 'Địa chỉ không được vượt quá 500 ký tự',
            'address2.max' => 'Địa chỉ phụ không được vượt quá 500 ký tự',
            'country.required' => 'Vui lòng nhập quốc gia',
            'country.max' => 'Quốc gia không được vượt quá 255 ký tự',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'post_code.max' => 'Mã bưu chính không được vượt quá 20 ký tự',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'shipping.required' => 'Vui lòng chọn phương thức vận chuyển',
            'shipping.exists' => 'Phương thức vận chuyển không tồn tại',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ',
            'sub_total.required' => 'Vui lòng nhập tổng phụ',
            'sub_total.numeric' => 'Tổng phụ phải là số',
            'sub_total.min' => 'Tổng phụ không được nhỏ hơn 0',
            'sub_total.max' => 'Tổng phụ không được vượt quá 999,999.99',
            'quantity.required' => 'Vui lòng nhập số lượng',
            'quantity.integer' => 'Số lượng phải là số nguyên',
            'quantity.min' => 'Số lượng phải ít nhất là 1',
            'quantity.max' => 'Số lượng không được vượt quá 1000',
            'total_amount.required' => 'Vui lòng nhập tổng cộng',
            'total_amount.numeric' => 'Tổng cộng phải là số',
            'total_amount.min' => 'Tổng cộng không được nhỏ hơn 0',
            'total_amount.max' => 'Tổng cộng không được vượt quá 999,999.99'
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

        // Kiểm tra logic của total_amount
        $shipping = Shipping::find($request->shipping);
        $expectedTotal = $request->sub_total + ($shipping ? $shipping->price : 0);
        
        if (abs($request->total_amount - $expectedTotal) > 0.01) {
            return redirect()->back()->withInput()->with('error', 'Tổng tiền không chính xác. Vui lòng kiểm tra lại.');
        }

        // Chuẩn bị data để tạo order
        $data = $request->only([
            'user_id', 'first_name', 'last_name', 'email', 'phone', 'country',
            'address1', 'address2', 'post_code', 'payment_method', 'status',
            'sub_total', 'quantity', 'total_amount'
        ]);
        
        // Mapping shipping field
        $data['shipping_id'] = $request->shipping;
        $data['order_number'] = 'ORD-'.strtoupper(uniqid());
        $data['payment_status'] = 'unpaid'; // Mặc định là chưa thanh toán
        
        $status = Order::create($data);
        
        if($status){
            request()->session()->flash('success','Đơn hàng đã được tạo thành công');
        }
        else{
            request()->session()->flash('error','Có lỗi xảy ra khi tạo đơn hàng, vui lòng thử lại!');
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
}
