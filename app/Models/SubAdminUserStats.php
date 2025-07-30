<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAdminUserStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_admin_id', 'total_users', 'active_users', 'inactive_users',
        'total_orders', 'total_revenue', 'commission_earned', 'last_updated'
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'commission_earned' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function subAdmin()
    {
        return $this->belongsTo('App\User', 'sub_admin_id');
    }
}
