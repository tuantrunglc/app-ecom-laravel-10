<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LuckyWheelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description'
    ];

    // Lấy giá trị setting với cache
    public static function getValue($key, $default = null)
    {
        return Cache::remember("lucky_wheel_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Cập nhật giá trị setting
    public static function setValue($key, $value, $description = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description
            ]
        );

        // Xóa cache
        Cache::forget("lucky_wheel_setting_{$key}");

        return $setting;
    }

    // Lấy tất cả settings dạng key-value
    public static function getAllSettings()
    {
        return Cache::remember('lucky_wheel_all_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    // Xóa cache khi update
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("lucky_wheel_setting_{$setting->key}");
            Cache::forget('lucky_wheel_all_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget("lucky_wheel_setting_{$setting->key}");
            Cache::forget('lucky_wheel_all_settings');
        });
    }

    // Các setting mặc định
    public static function getDefaultSettings()
    {
        return [
            'max_spins_per_day' => [
                'value' => '3',
                'description' => 'Số lần quay tối đa mỗi ngày'
            ],
            'wheel_enabled' => [
                'value' => 'true',
                'description' => 'Bật/tắt chức năng vòng quay'
            ],
            'require_login' => [
                'value' => 'true',
                'description' => 'Yêu cầu đăng nhập để quay'
            ],
            'animation_duration' => [
                'value' => '3000',
                'description' => 'Thời gian hiệu ứng xoay (ms)'
            ],
            'min_prize_probability' => [
                'value' => '10',
                'description' => 'Tỷ lệ trúng thưởng tối thiểu (%)'
            ]
        ];
    }
}
