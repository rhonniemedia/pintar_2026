<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repo: app-akademik
     * Domain: student (referensi ekstrakurikuler per semester)
     * Prefix tabel: acd_ (academic)
     */
    public function up(): void
    {
        Schema::create('acd_extracurriculars', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // TAMBAHAN: Ikat ekskul ke semester untuk melacak pembina dan periode
            $table->foreignUuid('semester_id')->constrained('core_semesters')->cascadeOnDelete();

            $table->string('name'); // contoh: 'Pramuka', 'Basket'

            // Pembina bisa berubah tiap semester tanpa merusak data masa lalu
            $table->foreignUuid('supervisor_id')->nullable()->constrained('users');

            $table->timestamps();

            // Mencegah duplikasi pembuatan ekskul yang sama di satu semester
            $table->unique(['semester_id', 'name'], 'unique_extracurricular_per_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acd_extracurriculars');
    }
};
