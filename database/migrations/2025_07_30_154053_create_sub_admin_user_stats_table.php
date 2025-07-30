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
        Schema::create('sub_admin_user_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_admin_id');
            $table->integer('total_users')->default(0);
            $table->integer('active_users')->default(0);
            $table->integer('inactive_users')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0.00);
            $table->decimal('commission_earned', 15, 2)->default(0.00);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            $table->foreign('sub_admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('sub_admin_id', 'unique_sub_admin_stats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_admin_user_stats');
    }
};
