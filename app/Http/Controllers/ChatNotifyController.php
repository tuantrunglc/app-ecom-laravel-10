<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\User;
use App\Notifications\ChatNewMessageNotification;

class ChatNotifyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'required|string',
            'sender_id'       => 'required|integer',
            'sender_name'     => 'required|string',
            'preview'         => 'nullable|string',
            'type'            => 'required|string|in:text,image',
            'timestamp'       => 'required|integer',
        ]);

        // TODO: filter recipients by conversation assignment
        $recipients = User::query()
            ->whereIn('role', ['admin','sub_admin'])
            ->get();

        Notification::send($recipients, new ChatNewMessageNotification(
            $data['conversation_id'],
            $data['sender_id'],
            $data['sender_name'],
            $data['preview'] ?? '',
            $data['type'],
            $data['timestamp']
        ));

        return response()->json(['ok' => true]);
    }
}