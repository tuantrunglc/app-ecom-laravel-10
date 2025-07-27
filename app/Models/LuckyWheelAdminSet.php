<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LuckyWheelAdminSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prize_id',
        'admin_id',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime'
    ];

    // Relationship với user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship với prize
    public function prize()
    {
        return $this->belongsTo(LuckyWheelPrize::class, 'prize_id');
    }

    // Relationship với admin
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scope để lấy các set chưa sử dụng
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    // Scope để lấy các set chưa hết hạn
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    // Scope để lấy set cho user cụ thể
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Lấy kết quả được đặt cho user (chưa sử dụng và chưa hết hạn)
    public static function getAvailableSetForUser($userId)
    {
        return self::forUser($userId)
            ->unused()
            ->notExpired()
            ->with('prize')
            ->first();
    }

    // Đánh dấu đã sử dụng
    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    // Kiểm tra có hết hạn không
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Kiểm tra có thể sử dụng không
    public function canBeUsed()
    {
        return !$this->is_used && !$this->isExpired();
    }

    // Cleanup các set đã hết hạn
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', Carbon::now())
            ->where('is_used', false)
            ->delete();
    }
}
