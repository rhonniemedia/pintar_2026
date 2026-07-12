<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student (pivot)
     * Prefix tabel: acd_ (academic)
     * Penempatan siswa ke rombel
     */
    public function up(): void
    {
        Schema::create('acd_class_group_students', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi cukup ke Siswa dan Rombel (karena rombel sudah terikat semester)
            $table->foreignUuid('student_id')->constrained('acd_students')->cascadeOnDelete();
            $table->foreignUuid('class_group_id')->constrained('acd_class_groups')->cascadeOnDelete();

            $table->date('entry_date');
            $table->date('exit_date')->nullable();

            $table->enum('status', ['active', 'moved_class', 'dropped', 'graduated'])->default('active');

            $table->timestamps();

            // MODIFIKASI: Cukup pastikan siswa tidak diinput 2x di rombel yang SAMA.
            $table->unique(['student_id', 'class_group_id'], 'unique_student_class_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_class_group_students');
    }
};
