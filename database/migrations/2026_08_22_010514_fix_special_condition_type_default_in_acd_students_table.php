<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bersihkan dulu data lama yang bernilai 'none'
        DB::table('acd_students')
            ->where('special_condition_type', 'none')
            ->update(['special_condition_type' => null]);

        // 2. Ubah default kolom dari 'none' menjadi NULL
        DB::statement("ALTER TABLE acd_students MODIFY special_condition_type VARCHAR(255) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE acd_students MODIFY special_condition_type VARCHAR(255) NULL DEFAULT 'none'");
    }
};
