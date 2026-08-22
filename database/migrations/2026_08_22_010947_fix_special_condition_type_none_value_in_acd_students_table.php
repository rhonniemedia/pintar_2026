<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrasi data lama: 'none' -> 'tidak-ada' (nilai valid di enum SpecialCondition)
        DB::table('acd_students')
            ->where('special_condition_type', 'none')
            ->update(['special_condition_type' => 'tidak-ada']);

        // 2. Ubah juga data NULL menjadi 'tidak-ada', karena is_special_condition = 'no'
        //    seharusnya selalu berpasangan dengan special_condition_type = 'tidak-ada',
        //    bukan NULL, supaya konsisten dan cast enum tidak pernah ketemu NULL tak terduga.
        DB::table('acd_students')
            ->whereNull('special_condition_type')
            ->update(['special_condition_type' => 'tidak-ada']);

        // 3. Ubah default kolom dari 'none' menjadi 'tidak-ada'
        DB::statement("ALTER TABLE acd_students MODIFY special_condition_type VARCHAR(255) NULL DEFAULT 'tidak-ada'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE acd_students MODIFY special_condition_type VARCHAR(255) NULL DEFAULT 'none'");

        DB::table('acd_students')
            ->where('special_condition_type', 'tidak-ada')
            ->update(['special_condition_type' => 'none']);
    }
};
