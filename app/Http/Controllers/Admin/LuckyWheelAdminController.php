<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LuckyWheelPrize;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelSetting;
use App\Models\LuckyWheelAdminSet;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LuckyWheelAdminController extends Controller
{
    /**
     * Dashboard vòng quay may mắn
     */
    public function index()
    {
        $stats = [
            'total_prizes' => LuckyWheelPrize::count(),
            'active_prizes' => LuckyWheelPrize::active()->count(),
            'total_spins_today' => LuckyWheelSpin::whereDate('created_at', Carbon::today())->count(),
            'total_winners_today' => LuckyWheelSpin::whereDate('created_at', Carbon::today())->winners()->count(),
            'total_spins' => LuckyWheelSpin::count(),
            'total_winners' => LuckyWheelSpin::winners()->count(),
        ];

        // Thống kê theo ngày (7 ngày gần nhất)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyStats[] = [
                'date' => $date->format('d/m'),
                'spins' => LuckyWheelSpin::whereDate('created_at', $date)->count(),
                'winners' => LuckyWheelSpin::whereDate('created_at', $date)->winners()->count(),
            ];
        }

        // Top phần thưởng được quay nhiều nhất
        $topPrizes = LuckyWheelPrize::withCount('spins')
            ->orderBy('spins_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.lucky-wheel.index', compact('stats', 'dailyStats', 'topPrizes'));
    }

    /**
     * Quản lý phần thưởng
     */
    public function prizes()
    {
        $prizes = LuckyWheelPrize::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.lucky-wheel.prizes.index', compact('prizes'));
    }

    /**
     * Form tạo phần thưởng mới
     */
    public function createPrize()
    {
        return view('admin.lucky-wheel.prizes.create');
    }

    /**
     * Lưu phần thưởng mới
     */
    public function storePrize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'probability' => 'required|numeric|min:0|max:100',
            'quantity' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['remaining_quantity'] = $data['quantity'];
        $data['is_active'] = $request->has('is_active');

        // Upload hình ảnh
        if ($request->hasFile('image')) {
            $userId = auth()->id() ?? 1; // Use authenticated user ID or default to 1
            
            // Create directory if it doesn't exist
            $uploadPath = public_path("photos/{$userId}/LuckyWheel");
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $file = $request->file('image');
            // Generate unique filename with random prefix
            $randomPrefix = substr(md5(uniqid()), 0, 5);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $randomPrefix . '-' . $originalName . '.' . $extension;
            
            // Move file to the structured directory
            $file->move($uploadPath, $fileName);
            $data['image'] = "/photos/{$userId}/LuckyWheel/{$fileName}";
        }

        LuckyWheelPrize::create($data);

        return redirect()->route('admin.lucky-wheel.prizes')
            ->with('success', 'Tạo phần thưởng thành công!');
    }

    /**
     * Form chỉnh sửa phần thưởng
     */
    public function editPrize($id)
    {
        $prize = LuckyWheelPrize::findOrFail($id);
        return view('admin.lucky-wheel.prizes.edit', compact('prize'));
    }

    /**
     * Cập nhật phần thưởng
     */
    public function updatePrize(Request $request, $id)
    {
        $prize = LuckyWheelPrize::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'probability' => 'required|numeric|min:0|max:100',
            'quantity' => 'required|integer|min:0',
            'remaining_quantity' => 'required|integer|min:0|max:' . $request->quantity,
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        // Upload hình ảnh mới
        if ($request->hasFile('image')) {
            // Xóa hình cũ
            if ($prize->image) {
                $oldImagePath = public_path($prize->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $userId = auth()->id() ?? 1; // Use authenticated user ID or default to 1
            
            // Create directory if it doesn't exist
            $uploadPath = public_path("photos/{$userId}/LuckyWheel");
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $file = $request->file('image');
            // Generate unique filename with random prefix
            $randomPrefix = substr(md5(uniqid()), 0, 5);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $randomPrefix . '-' . $originalName . '.' . $extension;
            
            // Move file to the structured directory
            $file->move($uploadPath, $fileName);
            $data['image'] = "/photos/{$userId}/LuckyWheel/{$fileName}";
        }

        $prize->update($data);

        return redirect()->route('admin.lucky-wheel.prizes')
            ->with('success', 'Cập nhật phần thưởng thành công!');
    }

    /**
     * Xóa phần thưởng
     */
    public function deletePrize($id)
    {
        $prize = LuckyWheelPrize::findOrFail($id);

        // Kiểm tra có lượt quay nào sử dụng phần thưởng này không
        if ($prize->spins()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Không thể xóa phần thưởng đã có người quay trúng!');
        }

        // Xóa hình ảnh
        if ($prize->image) {
            $imagePath = public_path($prize->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $prize->delete();

        return redirect()->route('admin.lucky-wheel.prizes')
            ->with('success', 'Xóa phần thưởng thành công!');
    }

    /**
     * Quản lý cài đặt
     */
    public function settings()
    {
        $settings = LuckyWheelSetting::all()->keyBy('key');
        $defaultSettings = LuckyWheelSetting::getDefaultSettings();
        
        return view('admin.lucky-wheel.settings', compact('settings', 'defaultSettings'));
    }

    /**
     * Cập nhật cài đặt
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'max_spins_per_day' => 'required|integer|min:1|max:10',
            'wheel_enabled' => 'boolean',
            'require_login' => 'boolean',
            'animation_duration' => 'required|integer|min:1000|max:10000',
            'min_prize_probability' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $settings = [
            'max_spins_per_day' => $request->max_spins_per_day,
            'wheel_enabled' => $request->has('wheel_enabled') ? 'true' : 'false',
            'require_login' => $request->has('require_login') ? 'true' : 'false',
            'animation_duration' => $request->animation_duration,
            'min_prize_probability' => $request->min_prize_probability
        ];

        foreach ($settings as $key => $value) {
            LuckyWheelSetting::setValue($key, $value);
        }

        return redirect()->back()
            ->with('success', 'Cập nhật cài đặt thành công!');
    }

    /**
     * Lịch sử quay
     */
    public function spins(Request $request)
    {
        $query = LuckyWheelSpin::with(['user', 'prize']);

        // Filter theo ngày
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter theo user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter theo trúng thưởng
        if ($request->is_winner !== null) {
            $query->where('is_winner', $request->is_winner);
        }

        // Filter theo admin set
        if ($request->admin_set !== null) {
            $query->where('admin_set', $request->admin_set);
        }

        $spins = $query->orderBy('created_at', 'desc')->paginate(20);

        // Lấy danh sách user để filter
        $users = User::select('id', 'name', 'email')->get();

        return view('admin.lucky-wheel.spins', compact('spins', 'users'));
    }

    /**
     * Đặt kết quả cho user
     */
    public function setResult()
    {
        $users = User::select('id', 'name', 'email')->get();
        $prizes = LuckyWheelPrize::active()->get();
        
        return view('admin.lucky-wheel.set-result', compact('users', 'prizes'));
    }

    /**
     * Lưu kết quả đặt cho user
     */
    public function storeSetResult(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'prize_id' => 'required|exists:lucky_wheel_prizes,id',
            'expires_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Kiểm tra user đã có kết quả được đặt chưa
        $existingSet = LuckyWheelAdminSet::getAvailableSetForUser($request->user_id);
        if ($existingSet) {
            return redirect()->back()
                ->with('error', 'User này đã có kết quả được đặt trước đó!');
        }

        LuckyWheelAdminSet::create([
            'user_id' => $request->user_id,
            'prize_id' => $request->prize_id,
            'admin_id' => Auth::id(),
            'expires_at' => $request->expires_at
        ]);

        $user = User::find($request->user_id);
        $prize = LuckyWheelPrize::find($request->prize_id);

        return redirect()->back()
            ->with('success', "Đã đặt kết quả '{$prize->name}' cho user {$user->name}!");
    }

    /**
     * Danh sách kết quả đã đặt
     */
    public function adminSets(Request $request)
    {
        $query = LuckyWheelAdminSet::with(['user', 'prize', 'admin']);

        // Filter theo trạng thái
        if ($request->status === 'used') {
            $query->where('is_used', true);
        } elseif ($request->status === 'unused') {
            $query->unused()->notExpired();
        } elseif ($request->status === 'expired') {
            $query->where('expires_at', '<', Carbon::now())->where('is_used', false);
        }

        $adminSets = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.lucky-wheel.admin-sets', compact('adminSets'));
    }

    /**
     * Xóa kết quả đã đặt
     */
    public function deleteAdminSet($id)
    {
        $adminSet = LuckyWheelAdminSet::findOrFail($id);

        if ($adminSet->is_used) {
            return redirect()->back()
                ->with('error', 'Không thể xóa kết quả đã được sử dụng!');
        }

        $adminSet->delete();

        return redirect()->back()
            ->with('success', 'Xóa kết quả đã đặt thành công!');
    }

    /**
     * Thống kê chi tiết
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        // Thống kê tổng quan
        $totalStats = [
            'total_spins' => LuckyWheelSpin::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_winners' => LuckyWheelSpin::whereBetween('created_at', [$dateFrom, $dateTo])->winners()->count(),
            'total_admin_sets' => LuckyWheelSpin::whereBetween('created_at', [$dateFrom, $dateTo])->adminSet()->count(),
        ];

        // Thống kê theo phần thưởng
        $prizeStats = LuckyWheelPrize::withCount([
            'spins' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }
        ])->get();

        // Thống kê theo ngày
        $dailyStats = LuckyWheelSpin::selectRaw('DATE(created_at) as date, COUNT(*) as total_spins, SUM(is_winner) as total_winners')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.lucky-wheel.statistics', compact(
            'totalStats', 
            'prizeStats', 
            'dailyStats', 
            'dateFrom', 
            'dateTo'
        ));
    }

    /**
     * Cleanup dữ liệu cũ
     */
    public function cleanup()
    {
        // Xóa các admin set đã hết hạn
        $expiredSets = LuckyWheelAdminSet::cleanupExpired();

        return redirect()->back()
            ->with('success', "Đã dọn dẹp {$expiredSets} kết quả hết hạn!");
    }
}
