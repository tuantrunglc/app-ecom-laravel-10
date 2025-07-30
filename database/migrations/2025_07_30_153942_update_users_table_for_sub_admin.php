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
        Schema::table('users', function (Blueprint $table) {
            // Cập nhật role enum
            $table->enum('role', ['admin', 'sub_admin', 'user'])->default('user')->change();
            
            // Thêm các trường mới
            $table->string('sub_admin_code', 20)->unique()->nullable()->after('role');
            $table->unsignedBigInteger('parent_sub_admin_id')->nullable()->after('sub_admin_code');
            $table->string('referral_code', 20)->nullable()->after('parent_sub_admin_id');
            $table->unsignedBigInteger('created_by')->nullable()->after('referral_code');
            
            // Thêm indexes
            $table->index('parent_sub_admin_id', 'idx_parent_sub_admin');
            $table->index('sub_admin_code', 'idx_sub_admin_code');
            $table->index('role', 'idx_role');
            $table->index('created_by', 'idx_created_by');
            
            // Thêm foreign keys
            $table->foreign('parent_sub_admin_id', 'fk_parent_sub_admin')
                  ->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by', 'fk_created_by')
                  ->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Xóa foreign keys
            $table->dropForeign('fk_parent_sub_admin');
            $table->dropForeign('fk_created_by');
            
            // Xóa indexes
            $table->dropIndex('idx_parent_sub_admin');
            $table->dropIndex('idx_sub_admin_code');
            $table->dropIndex('idx_role');
            $table->dropIndex('idx_created_by');
            
            // Xóa columns
            $table->dropColumn(['sub_admin_code', 'parent_sub_admin_id', 'referral_code', 'created_by']);
            
            // Khôi phục role enum cũ
            $table->enum('role', ['admin', 'user'])->default('user')->change();
        });
    }
};
