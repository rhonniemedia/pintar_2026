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
     * Data orang tua/wali (dulu: data_ortus, dilengkapi dari
     * referensi parent_data)
     *
     * Plaintext di sini, dan alasannya:
     * - name: dipakai untuk tampilan/pencarian sehari-hari.
     * - relationship, living_status: metadata struktural, dipakai
     *   untuk filter query ("tampilkan semua data ayah yang masih hidup").
     * - birth_year: hanya TAHUN lahir (bukan tanggal lengkap), risiko re-
     *   identifikasi rendah tapi cukup untuk statistik usia orang tua.
     * - occupation, education, income_range: REVISI dari desain
     *   sebelumnya (dulu occupation ikut dienkripsi). Diikutkan
     *   plain di sini sesuai referensi parent_data, karena field ini
     *   biasa dipakai untuk rekap agregat (pengajuan bantuan
     *   pemerintah seperti KIP/PIP) yang butuh query langsung tanpa
     *   decrypt massal. Kalau kebijakan privasi sekolah kamu lebih
     *   ketat dari ini, field-field ini bisa dipindah balik ke
     *   acd_guardians_vault kapan saja.
     *
     * nik, phone_number, address dipindah ke acd_guardians_vault
     * pada migration berikutnya.
     */
    public function up(): void
    {
        Schema::create('acd_guardians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('acd_students')->cascadeOnDelete();

            $table->string('name');
            $table->enum('relationship', ['father', 'mother', 'guardian']);
            $table->enum('living_status', ['alive', 'deceased'])->default('alive');
            $table->string('birth_year', 4)->nullable();

            $table->string('occupation')->nullable();
            $table->string('education')->nullable();     // SD, SMP, SMA, S1, dst
            $table->string('income_range')->nullable();   // rentang penghasilan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_guardians');
    }
};
