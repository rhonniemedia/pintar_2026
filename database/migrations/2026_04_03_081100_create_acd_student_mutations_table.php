<?php

use App\Enums\Student\MutationStatus;
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

            $table->enum('status', array_column(MutationStatus::cases(), 'value'));

            // Detail sekolah asal (relevan untuk TRANSFER_IN)
            $table->string('origin_school')->nullable();
            $table->string('origin_school_npsn', 20)->nullable();

            // Detail sekolah tujuan (relevan untuk TRANSFER_OUT)
            $table->string('destination_school')->nullable();
            $table->string('destination_school_npsn', 20)->nullable();

            $table->text('notes')->nullable();

            // Tanggal pasti kapan mutasi terjadi (secara administratif)
            $table->date('mutation_date');

            $table->timestamps();

            // Index untuk laporan rekap per jenis status & periode
            $table->index(['status', 'semester_id']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_student_mutations');
    }
};
