<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student
     * Prefix tabel: acd_ (academic)
     * Riwayat mutasi siswa
     */
    public function up(): void
    {
        Schema::create('acd_student_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('acd_students')->cascadeOnDelete();

            // PERUBAHAN 1: Mengarah ke semester spesifik agar pelaporan akurat
            $table->foreignUuid('semester_id')->constrained('core_semesters')->cascadeOnDelete();

            // Relasi ke rombel jika mutasi terjadi saat siswa sudah punya kelas
            $table->foreignUuid('class_group_id')->nullable()->constrained('acd_class_groups');

            $table->enum('status', ['transfer_in', 'transfer_out', 'deceased', 'dropped_out']);
            $table->string('origin_destination')->nullable();
            $table->text('notes')->nullable();

            // Tanggal pasti kapan mutasi terjadi (secara administratif)
            $table->date('mutation_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_student_mutations');
    }
};
