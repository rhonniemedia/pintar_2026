<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acd_student_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('student_id')->constrained('acd_students')->cascadeOnDelete();

            // Snapshot referensi rombel & semester saat surat diterbitkan (untuk pencarian/filter riwayat).
            // Nullable karena beberapa jenis surat (mis. panggilan) mungkin tidak selalu terikat rombel spesifik.
            $table->foreignUuid('class_group_id')->nullable()->constrained('acd_class_groups')->nullOnDelete();
            $table->foreignUuid('semester_id')->nullable()->constrained('core_semesters')->nullOnDelete();

            // Jenis surat: active, transfer, good_conduct, poor_family, summons (lihat App\Enums\Student\LetterType)
            $table->string('letter_type');

            // Nomor surat resmi, mis. 421.5/045/O/SMKN1RL/2026. Nullable dulu supaya fleksibel
            // kalau nomor diisi manual belakangan / pola penomoran belum final.
            $table->string('letter_number')->nullable();

            $table->date('letter_date');

            // Data tambahan spesifik per jenis surat (tujuan sekolah pindah, data wali,
            // keperluan & waktu panggilan, dst). Isi lengkap tetap "terkunci" di file PDF-nya;
            // kolom ini cuma untuk kebutuhan tampilan cepat di riwayat/listing.
            $table->json('meta')->nullable();

            // Path file PDF hasil generate, disimpan di disk PRIVATE (bukan public) karena
            // memuat data pribadi siswa. Lihat catatan keamanan di StudentLetterController.
            $table->string('file_path');

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes(); // jaga jejak audit, tidak hard-delete riwayat surat

            $table->index(['student_id', 'letter_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acd_student_letters');
    }
};
