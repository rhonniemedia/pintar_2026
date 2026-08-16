<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update ENUM di tabel acd_students
        DB::statement("ALTER TABLE acd_students MODIFY COLUMN status ENUM('active', 'graduated', 'dropped_out', 'dismissed', 'discontinued', 'resigned', 'transferred_out', 'deceased', 'married') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Rollback (Hapus dismissed)
        DB::statement("ALTER TABLE acd_students MODIFY COLUMN status ENUM('active', 'graduated', 'dropped_out', 'discontinued', 'resigned', 'transferred_out', 'deceased', 'married') NOT NULL DEFAULT 'active'");
    }
};
