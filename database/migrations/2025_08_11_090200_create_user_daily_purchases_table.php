<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_daily_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('purchase_date');
            $table->integer('products_bought')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'purchase_date'], 'unique_user_date');
            $table->index('purchase_date', 'idx_purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_purchases');
    }
};