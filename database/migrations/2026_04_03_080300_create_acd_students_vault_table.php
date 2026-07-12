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
     * Pola sama seperti staff_data_vault di app-kepegawaian:
     * kolom *_encrypted (cast 'encrypted' di model) + *_hash
     * (blind index HMAC-SHA256) untuk pencarian exact-match,
     * khusus untuk field yang memang butuh dicari persis.
     *
     * - nisn: identitas wajib & unik per siswa -> encrypted + hash unik.
     * - nik: tidak semua siswa (terutama anak baru) sudah punya NIK
     *   terpisah dari KK -> nullable, hash tidak unik (index biasa).
     * - dob, religion, email, district: masing-masing diberi hash
     *   karena realistis dicari exact-match (verifikasi usia, filter
     *   agama untuk kegiatan keagamaan, dedup email, rekap siswa per
     *   kecamatan untuk keperluan dinas).
     * - pob, phone_number, address/rt/rw/village/regency/province/
     *   postal_code: teks bebas, tidak diberi hash (search terstruktur
     *   cukup lewat district_hash; hash tambahan di sini tidak
     *   menambah manfaat, hanya menambah luas permukaan data sensitif).
     */
    public function up(): void
    {
        Schema::create('acd_students_vault', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->unique()->constrained('acd_students')->cascadeOnDelete();

            // --- Identitas ---
            $table->text('nisn_encrypted');
            $table->string('nisn_hash', 64)->unique();

            $table->text('nik_encrypted')->nullable();
            $table->string('nik_hash', 64)->nullable();

            $table->text('pob_encrypted')->nullable();       // place of birth

            $table->text('dob_encrypted')->nullable();       // date of birth
            $table->string('dob_hash', 64)->nullable();

            $table->text('religion_encrypted')->nullable();
            $table->string('religion_hash', 64)->nullable();

            // --- Kontak ---
            $table->text('email_encrypted')->nullable();
            $table->string('email_hash', 64)->nullable();

            $table->text('phone_number_encrypted')->nullable();
            $table->string('phone_number_hash', 64)->nullable();

            // --- Alamat terstruktur ---
            $table->text('address_encrypted')->nullable();
            $table->text('rt_encrypted')->nullable();
            $table->text('rw_encrypted')->nullable();
            $table->text('village_encrypted')->nullable();

            $table->text('district_encrypted')->nullable();
            $table->string('district_hash', 64)->nullable();

            $table->text('regency_encrypted')->nullable();
            $table->text('province_encrypted')->nullable();
            $table->text('postal_code_encrypted')->nullable();

            $table->timestamps();

            $table->index('nik_hash');
            $table->index('dob_hash');
            $table->index('religion_hash');
            $table->index('email_hash');
            $table->index('phone_number_hash');
            $table->index('district_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_students_vault');
    }
};
