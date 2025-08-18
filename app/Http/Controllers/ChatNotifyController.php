<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\User;
use App\Notifications\ChatNewMessageNotification;
use App\Models\Conversation;

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

        // Find recipients from conversation participants (exclude sender)
        $conversation = Conversation::with('participants')->find($data['conversation_id']);
        if (!$conversation) {
            return response()->json(['ok' => false, 'message' => 'Conversation not found'], 404);
        }

        $recipients = $conversation->participants->filter(function ($user) use ($data) {
            return (int) $user->id !== (int) $data['sender_id'];
        });

        if ($recipients->isEmpty()) {
            return response()->json(['ok' => true, 'message' => 'No recipients']);
        }

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