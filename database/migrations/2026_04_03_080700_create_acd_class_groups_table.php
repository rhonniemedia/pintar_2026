<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student (cross-domain: rombel)
     * Prefix tabel: acd_ (academic)
     */
    public function up(): void
    {
        Schema::create('acd_class_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Cukup ikat ke Semester untuk melacak timeline kesiswaan
            $table->foreignUuid('semester_id')->constrained('core_semesters')->cascadeOnDelete();
            $table->foreignUuid('concentration_id')->constrained('core_concentrations');

            // Relasi ke wali kelas (opsional, bisa diisi nanti saat ploting)
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('users');

            $table->string('grade_level'); // contoh: '10', '11', '12'
            $table->string('name')->nullable();        // contoh: 'X RPL 1'
            $table->unsignedInteger('group_number');   // contoh: 1, 2
            $table->timestamps();

            // Unique key untuk validasi data kesiswaan per semester
            $table->unique([
                'semester_id',
                'concentration_id',
                'grade_level',
                'group_number'
            ], 'unique_class_group_per_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_class_groups');
    }
};
