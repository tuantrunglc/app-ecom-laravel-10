<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Set all existing users to FREE vip level (level = 0) if available
        $freeId = DB::table('vip_levels')->where('level', 0)->value('id');
        if ($freeId) {
            DB::table('users')->whereNull('vip_level_id')->update(['vip_level_id' => $freeId]);
        }
    }

    public function down(): void
    {
        // No-op: keep data
    }
};