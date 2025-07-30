<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_admin_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Quyền hạn chức năng
            $table->boolean('can_manage_products')->default(false);
            $table->boolean('can_manage_orders')->default(true);
            $table->boolean('can_view_reports')->default(true);
            $table->boolean('can_manage_users')->default(true);
            $table->boolean('can_create_users')->default(true);
            
            // Giới hạn
            $table->integer('max_users_allowed')->default(1000);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            
            // Cấu hình khác
            $table->boolean('auto_approve_users')->default(true);
            $table->boolean('notification_new_user')->default(true);
            $table->boolean('notification_new_order')->default(true);
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id', 'unique_user_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_admin_settings');
    }
};
