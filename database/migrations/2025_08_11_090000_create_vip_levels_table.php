<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vip_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->integer('level')->unique();
            $table->integer('daily_purchase_limit');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('color', 7)->default('#6c757d');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default levels based on the spec
        DB::table('vip_levels')->insert([
            ['name' => 'FREE', 'level' => 0, 'daily_purchase_limit' => 5,  'price' => 0,       'color' => '#6c757d', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VIP BẠC', 'level' => 1, 'daily_purchase_limit' => 30, 'price' => 3888,   'color' => '#b0bec5', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VIP BẠCH KIM', 'level' => 2, 'daily_purchase_limit' => 50, 'price' => 5888,   'color' => '#c0c0c0', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VIP KIM CƯƠNG', 'level' => 3, 'daily_purchase_limit' => 70, 'price' => 7888,   'color' => '#00bcd4', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VIP LEGEND', 'level' => 4, 'daily_purchase_limit' => 100, 'price' => 10888, 'color' => '#ff9800', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_levels');
    }
};