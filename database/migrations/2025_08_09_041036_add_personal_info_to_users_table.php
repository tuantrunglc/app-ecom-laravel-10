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
            $table->date('birth_date')->nullable()->after('photo');
            $table->integer('age')->nullable()->after('birth_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('age');
            $table->text('address')->nullable()->after('gender');
            $table->string('bank_name')->nullable()->after('address');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'age', 
                'gender',
                'address',
                'bank_name',
                'bank_account_number',
                'bank_account_name'
            ]);
        });
    }
};
