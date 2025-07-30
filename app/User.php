<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'photo', 'status', 'provider', 'provider_id', 
        'wallet_balance', 'sub_admin_code', 'parent_sub_admin_id', 'referral_code', 'created_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
    ];

    public function orders(){
        return $this->hasMany('App\Models\Order');
    }

    // Wallet relationships
    public function walletTransactions()
    {
        return $this->hasMany('App\Models\WalletTransaction')->orderBy('created_at', 'desc');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany('App\Models\WithdrawalRequest')->orderBy('created_at', 'desc');
    }

    // Sub Admin Relationships
    public function subAdminSettings()
    {
        return $this->hasOne('App\Models\SubAdminSettings', 'user_id');
    }

    public function managedUsers()
    {
        return $this->hasMany('App\User', 'parent_sub_admin_id');
    }

    public function parentSubAdmin()
    {
        return $this->belongsTo('App\User', 'parent_sub_admin_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function subAdminStats()
    {
        return $this->hasOne('App\Models\SubAdminUserStats', 'sub_admin_id');
    }

    // Helper methods
    public function getFormattedBalanceAttribute()
    {
        return '$' . number_format($this->wallet_balance, 2);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin()
    {
        return $this->role === 'sub_admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function canManageUser($userId)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($this->isSubAdmin()) {
            return $this->managedUsers()->where('id', $userId)->exists();
        }
        
        return false;
    }

    public function generateSubAdminCode()
    {
        do {
            $code = 'SA' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('sub_admin_code', $code)->exists());
        
        return $code;
    }

    public function getManagedUsersCount()
    {
        return $this->managedUsers()->count();
    }

    public function getActiveUsersCount()
    {
        return $this->managedUsers()->where('status', 'active')->count();
    }
}
