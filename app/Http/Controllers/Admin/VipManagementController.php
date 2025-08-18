<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VipLevel;
use App\User;

class VipManagementController extends Controller
{
    // List and manage VIP levels
    public function vipLevels()
    {
        $vipLevels = VipLevel::withCount('users')->ordered()->get();
        return view('backend.vip.levels', compact('vipLevels'));
    }

    public function updateVipLevel(Request $request, VipLevel $vipLevel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'daily_purchase_limit' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'color' => 'required|string|max:7',
            'is_active' => 'nullable|boolean',
        ]);

        $vipLevel->update([
            'name' => $validated['name'],
            'daily_purchase_limit' => $validated['daily_purchase_limit'],
            'price' => $validated['price'],
            'color' => $validated['color'],
            'is_active' => (bool)($validated['is_active'] ?? $vipLevel->is_active),
        ]);

        return redirect()->back()->with('success', 'VIP Level updated successfully');
    }

    // Manage users' VIP assignment
    public function userVipManagement()
    {
        $users = User::with(['vipLevel'])->orderBy('id', 'desc')->paginate(20);
        $vipLevels = VipLevel::active()->ordered()->get();
        return view('backend.vip.user-management', compact('users', 'vipLevels'));
    }

    public function changeUserVip(Request $request, User $user)
    {
        $validated = $request->validate([
            'vip_level_id' => 'required|exists:vip_levels,id',
        ]);

        $user->update(['vip_level_id' => $validated['vip_level_id']]);
        return redirect()->back()->with('success', "User {$user->name} VIP level updated successfully");
    }

    public function resetUserTodayPurchases(User $user)
    {
        $user->resetTodayPurchases();
        return redirect()->back()->with('success', "Today purchases reset for {$user->name}");
    }
}