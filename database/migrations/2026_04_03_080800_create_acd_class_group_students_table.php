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

            $table->enum('exit_reason', ['moved_class'])->nullable();

            $table->timestamps();

            // Cukup pastikan siswa tidak diinput 2x di rombel yang SAMA.
            $table->unique(['student_id', 'class_group_id'], 'unique_student_class_group');

            // Index untuk query "siapa saja yang masih aktif di rombel ini"
            $table->index(['class_group_id', 'exit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_class_group_students');
    }
};
