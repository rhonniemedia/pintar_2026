<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acd_student_violations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('acd_students')->cascadeOnDelete();

            // Wajib ada untuk rekap laporan per semester, tidak boleh null
            $table->foreignUuid('semester_id')->constrained('core_semesters')->cascadeOnDelete();

            // Opsional (nullable) untuk mengetahui di kelas mana pelanggaran terjadi, 
            // bisa null jika siswa belum dapat kelas (misal saat masa orientasi).
            $table->foreignUuid('class_group_id')->nullable()->constrained('acd_class_groups');

            $table->string('violation_type');
            $table->text('description')->nullable();
            $table->date('violation_date'); // Tetap dipertahankan untuk tahu hari/tanggal pasti kejadian

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_student_violations');
    }
};
