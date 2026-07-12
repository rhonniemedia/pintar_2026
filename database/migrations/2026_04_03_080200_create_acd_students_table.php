<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student
     * Prefix tabel: acd_ (academic) - biar jelas semua tabel di folder
     * ini milik domain akademik, sama seperti pola staff_ di app-kepegawaian.
     *
     * Profil utama siswa (dulu: data_peserta_didiks, dilengkapi
     * dari referensi personal_data). NIK, NISN, tempat/tanggal
     * lahir, agama, email, telepon, dan seluruh komponen alamat
     * SENGAJA TIDAK ada di sini - semua data pribadi itu dipindah
     * ke acd_students_vault pada migration berikutnya. Tabel ini hanya
     * berisi data non-sensitif yang dipakai untuk listing/filtering
     * sehari-hari, laporan PPDB, dan data kesehatan/minat siswa.
     *
     * Catatan: height, weight, medical_history, condition_description
     * tetap plain sesuai referensi (bukan keputusan saya) - pertimbangkan
     * enkripsi untuk medical_history & condition_description kalau
     * kebijakan privasi sekolah kamu mengharuskan (keduanya termasuk
     * "data pribadi spesifik" - kesehatan/disabilitas - menurut UU PDP).
     */
    public function up(): void
    {
        Schema::create('acd_students', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // --- Identitas inti ---
            $table->string('name');
            $table->string('nick_name')->nullable();
            $table->string('nis')->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->integer('child_order')->nullable();
            $table->integer('number_of_siblings')->nullable();
            $table->string('blood_type', 3)->nullable();

            // --- Sejarah Penerimaan (Statis) ---
            $table->date('entry_date');
            $table->enum('registration_type', ['new', 'transfer'])->default('new');
            $table->enum('entry_grade_level', ['10', '11', '12', '13'])->default('10'); // Mutlak, sejarah awal masuk
            $table->foreignUuid('concentration_id')->constrained('core_concentrations');

            // Status Siswa (Aktif/Lulus/Keluar/Pindah)
            $table->enum('status', ['active', 'graduated', 'dropped_out', 'transferred_out'])->default('active');

            // --- Domisili & mobilitas (non-sensitif) ---
            $table->string('residence_type')->nullable();     // j_tinggal
            $table->string('transportation')->nullable();
            $table->string('distance_to_school')->nullable();

            // --- Riwayat sekolah asal ---
            $table->string('previous_school')->nullable();
            $table->string('previous_school_npsn')->nullable();
            $table->string('previous_school_status')->nullable();
            $table->string('previous_school_city')->nullable();
            $table->string('previous_school_province')->nullable();
            $table->string('graduation_certificate_number')->nullable();
            $table->string('graduation_year', 4)->nullable();

            // --- Kondisi khusus & status profil ---
            $table->enum('is_special_condition', ['yes', 'no'])->default('no');
            $table->string('special_condition_type')->nullable()->default('none');
            $table->text('condition_description')->nullable();

            // --- Kesehatan & minat ---
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->string('medical_history', 100)->nullable();
            $table->string('interest_art', 100)->nullable();
            $table->string('interest_sport', 100)->nullable();
            $table->string('interest_organization', 100)->nullable();
            $table->string('extracurricular_choice', 100)->nullable();
            $table->string('fl2sn_category', 100)->nullable();
            $table->string('o2sn_category', 100)->nullable();

            $table->string('photo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_students');
    }
};
