<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LuckyWheelSpin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prize_id',
        'spin_date',
        'is_winner',
        'admin_set'
    ];

    protected $casts = [
        'spin_date' => 'date',
        'is_winner' => 'boolean',
        'admin_set' => 'boolean'
    ];

    // Relationship với user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với prize
    public function prize()
    {
        return $this->belongsTo(LuckyWheelPrize::class, 'prize_id');
    }

    // Scope để lấy lượt quay hôm nay
    public function scopeToday($query)
    {
        return $query->whereDate('spin_date', Carbon::today());
    }

    // Scope để lấy lượt quay của user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope để lấy lượt quay trúng thưởng
    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    // Scope để lấy lượt quay admin đặt
    public function scopeAdminSet($query)
    {
        return $query->where('admin_set', true);
    }

    // Đếm số lần quay của user trong ngày
    public static function getUserSpinsToday($userId)
    {
        return self::forUser($userId)->today()->count();
    }

    // Kiểm tra user có thể quay không
    public static function canUserSpin($userId)
    {
        $maxSpins = LuckyWheelSetting::getValue('max_spins_per_day', 3);
        $todaySpins = self::getUserSpinsToday($userId);
        
        return $todaySpins < $maxSpins;
    }
}
