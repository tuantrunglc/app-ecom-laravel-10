<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\SubAdminSettings;
use App\Models\SubAdminUserStats;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSubAdminsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // Danh sách Sub Admins
    public function index()
    {
        $subAdmins = User::where('role', 'sub_admin')
            ->with(['subAdminSettings', 'subAdminStats'])
            ->paginate(10);
        
        // Đảm bảo tất cả sub-admin đều có settings
        foreach ($subAdmins as $subAdmin) {
            if (!$subAdmin->subAdminSettings) {
                $subAdmin->getOrCreateSubAdminSettings();
            }
        }
        
        return view('backend.admin.sub-admins.index', compact('subAdmins'));
    }

    // Tạo Sub Admin mới
    public function create()
    {
        return view('backend.admin.sub-admins.create');
    }

    // Lưu Sub Admin mới
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'can_manage_products' => 'boolean',
            'can_manage_orders' => 'boolean',
            'can_view_reports' => 'boolean',
            'can_manage_users' => 'boolean',
            'can_create_users' => 'boolean',
            'max_users_allowed' => 'required|integer|min:1',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Tạo Sub Admin
            $subAdmin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'sub_admin',
                'status' => 'active',
                'sub_admin_code' => $this->generateSubAdminCode(),
                'created_by' => auth()->user()->id,
            ]);

            // Tạo Settings
            SubAdminSettings::create([
                'user_id' => $subAdmin->id,
                'can_manage_products' => $request->boolean('can_manage_products'),
                'can_manage_orders' => $request->boolean('can_manage_orders'),
                'can_view_reports' => $request->boolean('can_view_reports'),
                'can_manage_users' => $request->boolean('can_manage_users'),
                'can_create_users' => $request->boolean('can_create_users'),
                'max_users_allowed' => $request->max_users_allowed,
                'commission_rate' => $request->commission_rate ?? 0,
                'auto_approve_users' => $request->boolean('auto_approve_users', true),
                'notification_new_user' => $request->boolean('notification_new_user', true),
                'notification_new_order' => $request->boolean('notification_new_order', true),
                'notification_new_deposit' => $request->boolean('notification_new_deposit', true),
                'notification_new_withdrawal' => $request->boolean('notification_new_withdrawal', true),
            ]);

            // Tạo Stats record
            SubAdminUserStats::create([
                'sub_admin_id' => $subAdmin->id,
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'commission_earned' => 0,
                'last_updated' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.sub-admins.index')->with('success', 'Tạo Sub Admin thành công. Mã Sub Admin: ' . $subAdmin->sub_admin_code);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Xem chi tiết Sub Admin
    public function show($id)
    {
        $subAdmin = User::where('role', 'sub_admin')
            ->with(['subAdminSettings', 'subAdminStats', 'managedUsers'])
            ->findOrFail($id);
        
        return view('backend.admin.sub-admins.show', compact('subAdmin'));
    }

    // Sửa Sub Admin
    public function edit($id)
    {
        $subAdmin = User::where('role', 'sub_admin')
            ->with('subAdminSettings')
            ->findOrFail($id);
        
        return view('backend.admin.sub-admins.edit', compact('subAdmin'));
    }

    // Cập nhật Sub Admin
    public function update(Request $request, $id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'status' => 'required|in:active,inactive',
            'can_manage_products' => 'boolean',
            'can_manage_orders' => 'boolean',
            'can_view_reports' => 'boolean',
            'can_manage_users' => 'boolean',
            'can_create_users' => 'boolean',
            'max_users_allowed' => 'required|integer|min:1',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật thông tin Sub Admin
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $subAdmin->update($updateData);

            // Cập nhật Settings
            $subAdmin->subAdminSettings->update([
                'can_manage_products' => $request->boolean('can_manage_products'),
                'can_manage_orders' => $request->boolean('can_manage_orders'),
                'can_view_reports' => $request->boolean('can_view_reports'),
                'can_manage_users' => $request->boolean('can_manage_users'),
                'can_create_users' => $request->boolean('can_create_users'),
                'max_users_allowed' => $request->max_users_allowed,
                'commission_rate' => $request->commission_rate ?? 0,
                'auto_approve_users' => $request->boolean('auto_approve_users'),
                'notification_new_user' => $request->boolean('notification_new_user'),
                'notification_new_order' => $request->boolean('notification_new_order'),
                'notification_new_deposit' => $request->boolean('notification_new_deposit'),
                'notification_new_withdrawal' => $request->boolean('notification_new_withdrawal'),
            ]);

            DB::commit();

            return redirect()->route('admin.sub-admins.index')->with('success', 'Cập nhật Sub Admin thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Xóa Sub Admin
    public function destroy($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Cập nhật users thuộc quyền Sub Admin (set parent_sub_admin_id = null)
            $subAdmin->managedUsers()->update(['parent_sub_admin_id' => null]);

            // Xóa Sub Admin (cascade sẽ xóa settings và stats)
            $subAdmin->delete();

            DB::commit();

            return redirect()->route('admin.sub-admins.index')->with('success', 'Xóa Sub Admin thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Tái tạo mã Sub Admin
    public function regenerateCode($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);
        $subAdmin->update(['sub_admin_code' => $this->generateSubAdminCode()]);

        return redirect()->back()->with('success', 'Tái tạo mã Sub Admin thành công: ' . $subAdmin->sub_admin_code);
    }

    // Toggle trạng thái Sub Admin
    public function toggleStatus($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);
        $newStatus = $subAdmin->status === 'active' ? 'inactive' : 'active';
        $subAdmin->update(['status' => $newStatus]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công');
    }

    // Xem users của Sub Admin
    public function users($id)
    {
        $subAdmin = User::where('role', 'sub_admin')->findOrFail($id);
        $users = $subAdmin->managedUsers()->paginate(15);

        return view('backend.admin.sub-admins.users', compact('subAdmin', 'users'));
    }

    // Helper methods
    private function generateSubAdminCode()
    {
        do {
            $code = 'SA' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (User::where('sub_admin_code', $code)->exists());
        
        return $code;
    }
}
