<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ChatNewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $conversationId,
        public int $senderId,
        public string $senderName,
        public string $preview,
        public string $type,
        public int $timestamp
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'sender_id'       => $this->senderId,
            'sender_name'     => $this->senderName,
            'preview'         => $this->type === 'image' ? '[Hình ảnh]' : $this->preview,
            'timestamp'       => $this->timestamp,
            'type'            => $this->type,
            'link'            => url('/chat/conversation/' . $this->conversationId),
        ];
    }
}