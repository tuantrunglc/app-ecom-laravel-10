<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAdminSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'can_manage_products', 'can_manage_orders', 'can_view_reports',
        'can_manage_users', 'can_create_users', 'max_users_allowed', 'commission_rate',
        'auto_approve_users', 'notification_new_user', 'notification_new_order',
        'notification_new_deposit', 'notification_new_withdrawal'
    ];

    protected $casts = [
        'can_manage_products' => 'boolean',
        'can_manage_orders' => 'boolean',
        'can_view_reports' => 'boolean',
        'can_manage_users' => 'boolean',
        'can_create_users' => 'boolean',
        'auto_approve_users' => 'boolean',
        'notification_new_user' => 'boolean',
        'notification_new_order' => 'boolean',
        'notification_new_deposit' => 'boolean',
        'notification_new_withdrawal' => 'boolean',
        'commission_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
