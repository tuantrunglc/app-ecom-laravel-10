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
        'wallet_balance', 'sub_admin_code', 'parent_sub_admin_id', 'referral_code', 'created_by',
        'birth_date', 'age', 'gender', 'address', 'bank_name', 'bank_account_number', 'bank_account_name',
        'withdrawal_password', 'withdrawal_password_created_at', 'withdrawal_password_updated_at',
        'vip_level_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'withdrawal_password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'withdrawal_password_created_at' => 'datetime',
        'withdrawal_password_updated_at' => 'datetime',
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

    // VIP Relationships
    public function vipLevel()
    {
        return $this->belongsTo(\App\Models\VipLevel::class, 'vip_level_id');
    }

    public function dailyPurchases()
    {
        return $this->hasMany(\App\Models\UserDailyPurchase::class);
    }

    public function todayPurchases()
    {
        return $this->hasOne(\App\Models\UserDailyPurchase::class)
            ->where('purchase_date', today());
    }

    // VIP Helpers
    public function getDailyPurchaseLimitAttribute()
    {
        return $this->vipLevel ? $this->vipLevel->daily_purchase_limit : 5;
    }

    public function getTodayPurchasesCountAttribute()
    {
        $todayRecord = $this->todayPurchases;
        return $todayRecord ? $todayRecord->products_bought : 0;
    }

    public function canBuyMoreProductsToday($quantity = 1)
    {
        return ($this->today_purchases_count + $quantity) <= $this->daily_purchase_limit;
    }

    public function getRemainingPurchasesTodayAttribute()
    {
        return max(0, $this->daily_purchase_limit - $this->today_purchases_count);
    }

    public function getVipLevelNameAttribute()
    {
        return $this->vipLevel ? $this->vipLevel->name : 'FREE';
    }

    public function getVipColorAttribute()
    {
        return $this->vipLevel ? $this->vipLevel->color : '#6c757d';
    }

    public function addPurchase($quantity = 1)
    {
        return \App\Models\UserDailyPurchase::incrementTodayPurchases($this->id, $quantity);
    }

    public function resetTodayPurchases()
    {
        $todayRecord = $this->todayPurchases;
        if ($todayRecord) {
            $todayRecord->update(['products_bought' => 0]);
        }
    }

    // Chat System Methods
    public function conversations()
    {
        return $this->belongsToMany('App\Models\Conversation', 'conversation_participants', 'user_id', 'conversation_id')
                    ->withTimestamps();
    }

    public function canChatWith(User $user)
    {
        // Admin có thể chat với tất cả Users và Sub Admins (không giới hạn)
        if ($this->role === 'admin') {
            return true;
        }
        
        // Sub Admin có thể chat với Admin và users thuộc quyền quản lý
        if ($this->role === 'sub_admin') {
            return $user->role === 'admin' || 
                   ($user->role === 'user' && $user->parent_sub_admin_id === $this->id);
        }
        
        // User chỉ có thể chat với Admin và Sub Admin quản lý mình (parent_sub_admin_id)
        if ($this->role === 'user') {
            // User luôn chat được với Admin
            if ($user->role === 'admin') {
                return true;
            }
            
            // User chỉ chat với Sub Admin quản lý mình (chỉ có 1 Sub Admin)
            if ($user->role === 'sub_admin' && $this->parent_sub_admin_id === $user->id) {
                return true;
            }
        }
        
        return false;
    }

    public function getAvailableChatUsers()
    {
        $query = User::where('id', '!=', $this->id)->where('status', 'active');
        
        if ($this->role === 'admin') {
            // Admin có thể chat với tất cả Users và Sub Admins (không giới hạn)
            return $query->get();
        }
        
        if ($this->role === 'sub_admin') {
            // Sub Admin chat với Admin và users thuộc quyền quản lý
            return $query->where(function($q) {
                $q->where('role', 'admin')
                  ->orWhere(function($subQ) {
                      $subQ->where('role', 'user')
                           ->where('parent_sub_admin_id', $this->id);
                  });
            })->get();
        }
        
        if ($this->role === 'user') {
            // User chỉ chat với Admin và Sub Admin quản lý mình (chỉ có 1 Sub Admin)
            return $query->where(function($q) {
                $q->where('role', 'admin')
                  ->orWhere(function($subQ) {
                      $subQ->where('role', 'sub_admin')
                           ->where('id', $this->parent_sub_admin_id);
                  });
            })->get();
        }
        
        return collect();
    }

    // Withdrawal Password Methods
    /**
     * Kiểm tra user đã có mật khẩu rút tiền chưa
     */
    public function hasWithdrawalPassword()
    {
        return !empty($this->withdrawal_password);
    }

    /**
     * Kiểm tra mật khẩu rút tiền
     */
    public function checkWithdrawalPassword($password)
    {
        return \Hash::check($password, $this->withdrawal_password);
    }

    /**
     * Đặt mật khẩu rút tiền mới
     */
    public function setWithdrawalPassword($password)
    {
        $this->withdrawal_password = \Hash::make($password);
        $this->withdrawal_password_updated_at = now();
        
        if (!$this->withdrawal_password_created_at) {
            $this->withdrawal_password_created_at = now();
        }
        
        $this->save();
    }

    /**
     * Validate PIN format (4-6 digits)
     */
    public static function validateWithdrawalPin($pin)
    {
        return preg_match('/^\d{4,6}$/', $pin);
    }
}
