<?php

namespace App\Enums\Student;

/**
 * Status keaktifan siswa saat ini (kolom cache di acd_students.status).
 *
 * Sumber kebenaran perubahan status ini selalu berasal dari
 * acd_student_mutations - lihat MutationStatus::resultingStudentStatus().
 * Jangan mengubah kolom ini secara manual di luar proses pencatatan mutasi,
 * supaya acd_students.status tidak pernah menyimpang dari riwayat mutasi.
 */
enum StudentStatus: string
{
    case ACTIVE = 'active';
    case GRADUATED = 'graduated';
    case DROPPED_OUT = 'dropped_out';
    case DISCONTINUED = 'discontinued';
    case RESIGNED = 'resigned';
    case TRANSFERRED_OUT = 'transferred_out';
    case DECEASED = 'deceased';
    case MARRIED = 'married';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::GRADUATED => 'Tamat / Lulus',
            self::DROPPED_OUT => 'Dikeluarkan (Dropout)',
            self::DISCONTINUED => 'Putus Sekolah',
            self::RESIGNED => 'Mengundurkan Diri',
            self::TRANSFERRED_OUT => 'Pindah Keluar',
            self::DECEASED => 'Meninggal',
            self::MARRIED => 'Menikah',
        };
    }

    /**
     * Status yang termasuk kategori "masih terdaftar sebagai siswa aktif".
     * Saat ini hanya ACTIVE, tapi disediakan sebagai satu titik definisi
     * kalau ke depan ada status "aktif bersyarat" lain (mis. cuti/leave).
     */
    public static function activeStatuses(): array
    {
        return [self::ACTIVE];
    }

    public function isActive(): bool
    {
        return in_array($this, self::activeStatuses(), true);
    }
}
