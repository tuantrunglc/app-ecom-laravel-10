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
        'name', 'email', 'password','role','photo','status','provider','provider_id','wallet_balance',
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

    // Helper methods
    public function getFormattedBalanceAttribute()
    {
        return '$' . number_format($this->wallet_balance, 2);
    }
}
