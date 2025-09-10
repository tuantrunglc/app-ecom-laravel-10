<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Conversation extends Model
{
    protected $fillable = ['id', 'type', 'created_by'];
    
    public $incrementing = false;
    protected $keyType = 'string';

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOtherParticipant($currentUserId)
    {
        $participant = $this->participants()->where('user_id', '!=', $currentUserId)->first();
        
        // Nếu user bị xóa khỏi database, trả về null
        if (!$participant) {
            return null;
        }
        
        return $participant;
    }

    /**
     * Get conversation between two users
     */
    public static function findBetweenUsers($userId1, $userId2)
    {
        return self::whereHas('participants', function($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('participants', function($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->where('type', 'direct')
            ->first();
    }

    /**
     * Create conversation between two users
     */
    public static function createBetweenUsers($userId1, $userId2, $createdBy)
    {
        $conversationId = 'conv_' . \Illuminate\Support\Str::random(10);
        
        $conversation = self::create([
            'id' => $conversationId,
            'type' => 'direct',
            'created_by' => $createdBy
        ]);

        $conversation->participants()->attach([$userId1, $userId2]);

        return $conversation;
    }
}