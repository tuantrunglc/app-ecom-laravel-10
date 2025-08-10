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
            $table->string('withdrawal_password')->nullable()->after('password');
            $table->timestamp('withdrawal_password_created_at')->nullable()->after('withdrawal_password');
            $table->timestamp('withdrawal_password_updated_at')->nullable()->after('withdrawal_password_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['withdrawal_password', 'withdrawal_password_created_at', 'withdrawal_password_updated_at']);
        });
    }
};
