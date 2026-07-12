<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student (pivot nilai ekskul)
     * Prefix tabel: acd_ (academic)
     */
    public function up(): void
    {
        Schema::create('acd_extracurricular_students', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke ekstrakurikuler (yang sudah mewakili ekskul di semester tertentu)
            $table->foreignUuid('extracurricular_id')->constrained('acd_extracurriculars')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('acd_students')->cascadeOnDelete();

            // Nilai rapor ekskul pada semester tersebut
            $table->string('score')->nullable(); // contoh: 'A', 'B', 'Sangat Baik'
            $table->text('description')->nullable();

            $table->timestamps();

            // Mencegah siswa didaftarkan dua kali di ekskul yang sama pada semester yang sama
            $table->unique(['extracurricular_id', 'student_id'], 'unique_acd_extracurricular_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_extracurricular_students');
    }
};
