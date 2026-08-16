<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE acd_student_mutations MODIFY COLUMN status ENUM('transfer_in', 'transfer_out', 'dropped_out', 'dismissed', 'resigned', 'deceased', 'married', 'graduated') NOT NULL");
    }

    public function down(): void
    {
        // Kembalikan ke enum sebelumnya jika di-rollback (tanpa dismissed)
        DB::statement("ALTER TABLE acd_student_mutations MODIFY COLUMN status ENUM('transfer_in', 'transfer_out', 'dropped_out', 'resigned', 'deceased', 'married', 'graduated') NOT NULL");
    }
};
