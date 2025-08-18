<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipLevel extends Model
{
    protected $fillable = [
        'name', 'level', 'daily_purchase_limit', 'price', 'color', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function users()
    {
        // App\User is the User model in this project
        return $this->hasMany(\App\User::class, 'vip_level_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format((float)$this->price, 2);
    }

    public function getIsFreePlanAttribute()
    {
        return (int)$this->level === 0;
    }
}