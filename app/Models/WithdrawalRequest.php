<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'amount', 'bank_name', 'bank_account', 
        'account_name', 'status', 'admin_note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationship với User
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    // Accessor để format số tiền
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    // Accessor để hiển thị trạng thái bằng tiếng Việt
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'completed' => 'Hoàn thành',
            'rejected' => 'Từ chối'
        ];
        return $statuses[$this->status] ?? $this->status;
    }
}
