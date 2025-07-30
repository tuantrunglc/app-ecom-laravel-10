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
     * Display the lucky wheel page
     */
    public function index()
    {
        // Check if wheel is enabled
        if (!LuckyWheelSetting::getValue('wheel_enabled', true)) {
            return view('frontend.lucky-wheel.disabled');
        }

        // Get prizes list
        $prizes = LuckyWheelPrize::active()->get();
        
        // Get settings
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
     * Spin the wheel
     */
    public function spin(Request $request)
    {
        try {
            // Check if wheel is enabled
            if (!LuckyWheelSetting::getValue('wheel_enabled', true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The wheel is currently disabled.'
                ]);
            }

            // Check if user is logged in
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need to login to spin the wheel.'
                ]);
            }

            $userId = Auth::id();

            // Check daily spin limit
            if (!LuckyWheelSpin::canUserSpin($userId)) {
                $maxSpins = LuckyWheelSetting::getValue('max_spins_per_day', 3);
                return response()->json([
                    'success' => false,
                    'message' => "You've reached your daily spin limit. Maximum {$maxSpins} spins per day."
                ]);
            }

            DB::beginTransaction();

            // Check if admin has set a result
            $adminSet = LuckyWheelAdminSet::getAvailableSetForUser($userId);
            
            if ($adminSet && $adminSet->canBeUsed()) {
                // Use admin set result
                $prize = $adminSet->prize;
                $isWinner = true;
                $adminSetFlag = true;
                
                // Mark admin set as used
                $adminSet->markAsUsed();
            } else {
                // Random prize by probability  
                $result = $this->randomPrize();
                $prize = $result['prize'];
                $isWinner = $result['is_winner'];
                $adminSetFlag = false;
            }

            // Save spin result
            $spin = LuckyWheelSpin::create([
                'user_id' => $userId,
                'prize_id' => $prize ? $prize->id : null,
                'spin_date' => Carbon::today(),
                'is_winner' => $isWinner,
                'admin_set' => $adminSetFlag
            ]);

            // Decrease prize quantity if won
            if ($isWinner && $prize) {
                $prize->decreaseQuantity();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isWinner ? 'Congratulations! You won a prize!' : 'Better luck next time!',
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
                'message' => 'An error occurred, please try again later.'
            ]);
        }
    }

    /**
     * Random prize by probability
     */
    private function randomPrize()
    {
        $prizes = LuckyWheelPrize::active()->available()->get();
        
        if ($prizes->isEmpty()) {
            // No prizes available
            $noPrize = LuckyWheelPrize::where('name', 'like', '%better luck%')->first();
            return [
                'prize' => $noPrize,
                'is_winner' => false
            ];
        }

        // Calculate total probability
        $totalProbability = $prizes->sum('probability');
        
        // Random number from 0 to total probability
        $random = mt_rand(0, $totalProbability * 100) / 100;
        
        $currentProbability = 0;
        foreach ($prizes as $prize) {
            $currentProbability += $prize->probability;
            if ($random <= $currentProbability) {
                return [
                    'prize' => $prize,
                    'is_winner' => !str_contains(strtolower($prize->name), 'better luck')
                ];
            }
        }

        // Fallback - return last prize
        $lastPrize = $prizes->last();
        return [
            'prize' => $lastPrize,
            'is_winner' => !str_contains(strtolower($lastPrize->name), 'better luck')
        ];
    }

    /**
     * User's spin history
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
     * API: Get prizes list
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
     * API: Get user information
     */
    public function getUserInfo()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Not logged in'
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
