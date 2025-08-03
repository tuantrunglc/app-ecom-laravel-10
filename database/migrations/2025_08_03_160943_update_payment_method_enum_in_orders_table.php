<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the enum to include wallet
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod', 'paypal', 'wallet') DEFAULT 'cod'");
        
        // Update existing records to use wallet payment method
        DB::statement("UPDATE orders SET payment_method = 'wallet' WHERE payment_method IN ('cod', 'paypal')");
        
        // Finally, modify the enum to only include wallet
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('wallet') DEFAULT 'wallet'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod', 'paypal') DEFAULT 'cod'");
        
        // Update wallet records back to cod
        DB::statement("UPDATE orders SET payment_method = 'cod' WHERE payment_method = 'wallet'");
    }
};
