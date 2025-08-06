<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LuckyWheelSpin;
use App\Models\LuckyWheelAdminSet;
use Illuminate\Support\Facades\Cache;

class LuckyWheelPrize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'probability',
        'quantity',
        'remaining_quantity',
        'is_active'
    ];

    protected $casts = [
        'probability' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationship với spins
    public function spins()
    {
        return $this->hasMany(LuckyWheelSpin::class, 'prize_id');
    }

    // Relationship với admin sets
    public function adminSets()
    {
        return $this->hasMany(LuckyWheelAdminSet::class, 'prize_id');
    }

    // Scope để lấy các phần thưởng đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope để lấy các phần thưởng còn số lượng
    public function scopeAvailable($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    // Kiểm tra phần thưởng có còn không
    public function isAvailable()
    {
        return $this->is_active && $this->remaining_quantity > 0;
    }

    // Giảm số lượng phần thưởng
    public function decreaseQuantity()
    {
        if ($this->remaining_quantity > 0) {
            $this->decrement('remaining_quantity');
            return true;
        }
        return false;
    }
}
