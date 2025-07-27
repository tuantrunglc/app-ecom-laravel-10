<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LuckyWheelPrize;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelSetting;
use App\Models\LuckyWheelAdminSet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LuckyWheelController extends Controller
{
    /**
     * Hiển thị trang vòng quay may mắn
     */
    public function index()
    {
        // Kiểm tra vòng quay có được bật không
        if (!LuckyWheelSetting::getValue('wheel_enabled', true)) {
            return view('frontend.lucky-wheel.disabled');
        }

        // Lấy danh sách phần thưởng
        $prizes = LuckyWheelPrize::active()->get();
        
        // Lấy cài đặt
        $settings = [
            'max_spins_per_day' => LuckyWheelSetting::getValue('max_spins_per_day', 3),
            'require_login' => LuckyWheelSetting::getValue('require_login', true),
            'animation_duration' => LuckyWheelSetting::getValue('animation_duration', 3000)
        ];

        $userSpinsToday = 0;
        $canSpin = true;

        if (Auth::check()) {
            $userSpinsToday = LuckyWheelSpin::getUserSpinsToday(Auth::id());
            $canSpin = LuckyWheelSpin::canUserSpin(Auth::id());
        } elseif ($settings['require_login']) {
            $canSpin = false;
        }

        return view('frontend.lucky-wheel.index', compact(
            'prizes', 
            'settings', 
            'userSpinsToday', 
            'canSpin'
        ));
    }

    /**
     * Thực hiện quay vòng quay
     */
    public function spin(Request $request)
    {
        try {
            // Kiểm tra vòng quay có được bật không
            if (!LuckyWheelSetting::getValue('wheel_enabled', true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vòng quay hiện đang tạm dừng hoạt động.'
                ]);
            }

            // Kiểm tra đăng nhập
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để tham gia vòng quay.'
                ]);
            }

            $userId = Auth::id();

            // Kiểm tra số lần quay trong ngày
            if (!LuckyWheelSpin::canUserSpin($userId)) {
                $maxSpins = LuckyWheelSetting::getValue('max_spins_per_day', 3);
                return response()->json([
                    'success' => false,
                    'message' => "Bạn đã hết lượt quay hôm nay. Tối đa {$maxSpins} lần/ngày."
                ]);
            }

            DB::beginTransaction();

            // Kiểm tra admin có đặt kết quả không
            $adminSet = LuckyWheelAdminSet::getAvailableSetForUser($userId);
            
            if ($adminSet && $adminSet->canBeUsed()) {
                // Sử dụng kết quả admin đặt
                $prize = $adminSet->prize;
                $isWinner = true;
                $adminSetFlag = true;
                
                // Đánh dấu admin set đã sử dụng
                $adminSet->markAsUsed();
            } else {
                // Random theo tỷ lệ
                $result = $this->randomPrize();
                $prize = $result['prize'];
                $isWinner = $result['is_winner'];
                $adminSetFlag = false;
            }

            // Lưu kết quả quay
            $spin = LuckyWheelSpin::create([
                'user_id' => $userId,
                'prize_id' => $prize ? $prize->id : null,
                'spin_date' => Carbon::today(),
                'is_winner' => $isWinner,
                'admin_set' => $adminSetFlag
            ]);

            // Giảm số lượng phần thưởng nếu trúng
            if ($isWinner && $prize) {
                $prize->decreaseQuantity();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isWinner ? 'Chúc mừng! Bạn đã trúng thưởng!' : 'Chúc bạn may mắn lần sau!',
                'prize' => $prize ? [
                    'id' => $prize->id,
                    'name' => $prize->name,
                    'description' => $prize->description,
                    'image' => $prize->image
                ] : null,
                'is_winner' => $isWinner,
                'admin_set' => $adminSetFlag
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
            ]);
        }
    }

    /**
     * Random phần thưởng theo tỷ lệ
     */
    private function randomPrize()
    {
        $prizes = LuckyWheelPrize::active()->available()->get();
        
        if ($prizes->isEmpty()) {
            // Không có phần thưởng nào
            $noPrize = LuckyWheelPrize::where('name', 'like', '%may mắn lần sau%')->first();
            return [
                'prize' => $noPrize,
                'is_winner' => false
            ];
        }

        // Tính tổng tỷ lệ
        $totalProbability = $prizes->sum('probability');
        
        // Random số từ 0 đến tổng tỷ lệ
        $random = mt_rand(0, $totalProbability * 100) / 100;
        
        $currentProbability = 0;
        foreach ($prizes as $prize) {
            $currentProbability += $prize->probability;
            if ($random <= $currentProbability) {
                return [
                    'prize' => $prize,
                    'is_winner' => !str_contains(strtolower($prize->name), 'may mắn lần sau')
                ];
            }
        }

        // Fallback - trả về phần thưởng cuối cùng
        $lastPrize = $prizes->last();
        return [
            'prize' => $lastPrize,
            'is_winner' => !str_contains(strtolower($lastPrize->name), 'may mắn lần sau')
        ];
    }

    /**
     * Lịch sử quay của user
     */
    public function history(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $spins = LuckyWheelSpin::where('user_id', Auth::id())
            ->with('prize')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('frontend.lucky-wheel.history', compact('spins'));
    }

    /**
     * API: Lấy danh sách phần thưởng
     */
    public function getPrizes()
    {
        $prizes = LuckyWheelPrize::active()->get();
        
        return response()->json([
            'success' => true,
            'prizes' => $prizes
        ]);
    }

    /**
     * API: Lấy thông tin user
     */
    public function getUserInfo()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ]);
        }

        $userId = Auth::id();
        $userSpinsToday = LuckyWheelSpin::getUserSpinsToday($userId);
        $maxSpins = LuckyWheelSetting::getValue('max_spins_per_day', 3);
        $canSpin = LuckyWheelSpin::canUserSpin($userId);

        return response()->json([
            'success' => true,
            'user_spins_today' => $userSpinsToday,
            'max_spins_per_day' => $maxSpins,
            'remaining_spins' => $maxSpins - $userSpinsToday,
            'can_spin' => $canSpin
        ]);
    }
}
