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
     *
     * Menambahkan kolom penghubung audit dari acd_class_group_students
     * ke acd_student_mutations. Dipisah jadi migrasi tersendiri karena
     * acd_student_mutations baru dibuat setelah acd_class_group_students
     * (urutan timestamp migrasi).
     *
     * Kolom ini diisi saat exit_date ditutup akibat kejadian mutasi
     * (tamat, pindah, keluar, meninggal, menikah, dsb) - TIDAK diisi
     * kalau exit_reason = 'moved_class' (pindah kelas internal, siswa
     * tetap aktif, tidak ada kejadian mutasi terkait).
     */
    public function up(): void
    {
        Schema::table('acd_class_group_students', function (Blueprint $table) {
            $table->foreignUuid('mutation_id')->nullable()
                ->after('exit_reason')
                ->constrained('acd_student_mutations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('acd_class_group_students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mutation_id');
        });
    }
};
