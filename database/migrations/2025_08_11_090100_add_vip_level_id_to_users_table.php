<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Put after role if exists; schema builder can't position reliably across DBs, but it's fine
            $table->unsignedBigInteger('vip_level_id')->nullable()->after('role');
            $table->foreign('vip_level_id')->references('id')->on('vip_levels')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['vip_level_id']);
            $table->dropColumn('vip_level_id');
        });
    }
};