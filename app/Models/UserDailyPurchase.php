<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDailyPurchase extends Model
{
    protected $fillable = [
        'user_id', 'purchase_date', 'products_bought'
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    // Static helpers
    public static function getTodayPurchases($userId)
    {
        return self::where('user_id', $userId)
            ->where('purchase_date', today())
            ->first();
    }

    public static function incrementTodayPurchases($userId, $quantity = 1)
    {
        $record = self::firstOrCreate(
            [
                'user_id' => $userId,
                'purchase_date' => today(),
            ],
            [
                'products_bought' => 0,
            ],
        );

        $record->increment('products_bought', $quantity);
        return $record;
    }
}