<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student (vault / data sensitif terenkripsi)
     * Prefix tabel: acd_ (academic)
     *
     * Menyimpan identitas & kontak orang tua/wali (dulu: sebagian
     * dari data_ortus, dilengkapi dari referensi parent_data).
     * occupation/education/income_range TIDAK ada di sini - lihat
     * catatan revisi di acd_guardians_table.php.
     *
     * - nik: sama seperti staff/siswa, diberi hash untuk verifikasi/dedup.
     * - phone_number: diberi hash karena realistis dicari exact-match
     *   (misal saat import data / cek duplikasi kontak).
     * - address: teks bebas, tidak diberi hash - dipakai untuk kasus
     *   alamat ortu berbeda dari alamat siswa di acd_students_vault.
     */
    public function up(): void
    {
        Schema::create('acd_guardians_vault', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guardian_id')->unique()->constrained('acd_guardians')->cascadeOnDelete();

            $table->text('nik_encrypted')->nullable();
            $table->string('nik_hash', 64)->nullable();

            $table->text('phone_number_encrypted')->nullable();
            $table->string('phone_number_hash', 64)->nullable();

            $table->text('address_encrypted')->nullable();

            $table->timestamps();

            $table->index('nik_hash');
            $table->index('phone_number_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_guardians_vault');
    }
};
