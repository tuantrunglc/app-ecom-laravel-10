<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LuckyWheelPrize;
use App\Models\LuckyWheelSetting;

class LuckyWheelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các phần thưởng mẫu
        $prizes = [
            [
                'name' => 'Voucher 100.000đ',
                'description' => 'Voucher giảm giá 100.000đ cho đơn hàng từ 500.000đ',
                'image' => 'voucher-100k.png',
                'probability' => 15.00,
                'quantity' => 100,
                'remaining_quantity' => 100,
                'is_active' => true
            ],
            [
                'name' => 'Voucher 50.000đ',
                'description' => 'Voucher giảm giá 50.000đ cho đơn hàng từ 200.000đ',
                'image' => 'voucher-50k.png',
                'probability' => 25.00,
                'quantity' => 200,
                'remaining_quantity' => 200,
                'is_active' => true
            ],
            [
                'name' => 'Voucher 20.000đ',
                'description' => 'Voucher giảm giá 20.000đ cho đơn hàng từ 100.000đ',
                'image' => 'voucher-20k.png',
                'probability' => 30.00,
                'quantity' => 300,
                'remaining_quantity' => 300,
                'is_active' => true
            ],
            [
                'name' => 'Freeship',
                'description' => 'Miễn phí vận chuyển cho đơn hàng bất kỳ',
                'image' => 'freeship.png',
                'probability' => 20.00,
                'quantity' => 500,
                'remaining_quantity' => 500,
                'is_active' => true
            ],
            [
                'name' => 'Chúc bạn may mắn lần sau',
                'description' => 'Không trúng thưởng, hãy thử lại lần sau nhé!',
                'image' => 'no-prize.png',
                'probability' => 10.00,
                'quantity' => 9999,
                'remaining_quantity' => 9999,
                'is_active' => true
            ]
        ];

        foreach ($prizes as $prize) {
            LuckyWheelPrize::create($prize);
        }

        // Tạo các setting mặc định
        $settings = LuckyWheelSetting::getDefaultSettings();
        
        foreach ($settings as $key => $setting) {
            LuckyWheelSetting::create([
                'key' => $key,
                'value' => $setting['value'],
                'description' => $setting['description']
            ]);
        }

        $this->command->info('Lucky Wheel data seeded successfully!');
    }
}
