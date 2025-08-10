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
        Schema::table('sub_admin_settings', function (Blueprint $table) {
            $table->boolean('notification_new_deposit')->default(true)->comment('Nhận thông báo yêu cầu nạp tiền mới');
            $table->boolean('notification_new_withdrawal')->default(true)->comment('Nhận thông báo yêu cầu rút tiền mới');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_admin_settings', function (Blueprint $table) {
            $table->dropColumn(['notification_new_deposit', 'notification_new_withdrawal']);
        });
    }
};
