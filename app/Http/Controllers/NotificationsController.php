<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications()->take(50)->get(),
            'recent' => $user->notifications()->latest()->take(50)->get(),
        ]);
    }

    public function markRead(Request $request)
    {
        $data = $request->validate([
            'ids' => 'array',
            'ids.*' => 'string',
            'all' => 'boolean',
        ]);

        $user = $request->user();

        if (!empty($data['all'])) {
            $user->unreadNotifications->markAsRead();
        } elseif (!empty($data['ids'])) {
            $user->notifications()->whereIn('id', $data['ids'])->get()->each->markAsRead();
        }

        return response()->json(['ok' => true]);
    }
}