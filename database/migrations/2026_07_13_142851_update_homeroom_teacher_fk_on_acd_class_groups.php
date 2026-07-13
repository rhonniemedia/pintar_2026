<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acd_class_groups', function (Blueprint $table) {
            // 1. Hapus relasi foreign key yang lama (yang mengarah ke users)
            // Format default nama foreign key Laravel: namaTabel_namaKolom_foreign
            $table->dropForeign(['homeroom_teacher_id']);

            // 2. Buat relasi foreign key yang baru mengarah ke staff_data
            $table->foreign('homeroom_teacher_id')
                ->references('id')
                ->on('staff_data'); // Opsional: tambahkan ->nullOnDelete() atau ->cascadeOnDelete() jika perlu
        });
    }

    public function down(): void
    {
        // Fungsi rollback jika Anda melakukan php artisan migrate:rollback
        Schema::table('acd_class_groups', function (Blueprint $table) {
            // Hapus relasi ke staff_data
            $table->dropForeign(['homeroom_teacher_id']);

            // Kembalikan relasi ke users
            $table->foreign('homeroom_teacher_id')
                ->references('id')
                ->on('users');
        });
    }
};
