<?php

namespace App\Enums\Student;

enum MutationStatus: string
{
    case TRANSFER_IN = 'transfer_in';
        // Urutan disesuaikan mulai dari sini:
    case TRANSFER_OUT = 'transfer_out'; // 1. Pindah Sekolah
    case DISMISSED = 'dismissed';       // 2. Dikeluarkan
    case RESIGNED = 'resigned';         // 3. Mengundurkan Diri
    case DROPPED_OUT = 'dropped_out';   // 4. Putus Sekolah
    case DECEASED = 'deceased';         // 5. Meninggal
    case MARRIED = 'married';           // 6. Menikah

    case GRADUATED = 'graduated';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER_IN => 'Pindah Masuk',
            self::TRANSFER_OUT => 'Pindah Sekolah',
            self::DISMISSED => 'Dikeluarkan',
            self::RESIGNED => 'Mengundurkan Diri',
            self::DROPPED_OUT => 'Putus Sekolah',
            self::DECEASED => 'Meninggal',
            self::MARRIED => 'Menikah',
            self::GRADUATED => 'Lulus',
        };
    }

    /**
     * Daftar status yang termasuk kategori "berhenti/keluar"
     * dan tidak termasuk mutasi pindah sekolah.
     */
    public static function dropoutReasons(): array
    {
        return [
            self::DISMISSED,
            self::RESIGNED,
            self::DROPPED_OUT,
            self::DECEASED,
            self::MARRIED,
        ];
    }

    /**
     * Status siswa (acd_students.status) setelah kejadian mutasi
     * tercatat.
     */
    public function resultingStudentStatus(): StudentStatus
    {
        return match ($this) {
            self::TRANSFER_IN => StudentStatus::ACTIVE,
            self::TRANSFER_OUT => StudentStatus::TRANSFERRED_OUT,
            self::DISMISSED => StudentStatus::DISMISSED,
            self::RESIGNED => StudentStatus::RESIGNED,
            self::DROPPED_OUT => StudentStatus::DROPPED_OUT,
            self::DECEASED => StudentStatus::DECEASED,
            self::MARRIED => StudentStatus::MARRIED,
            self::GRADUATED => StudentStatus::GRADUATED,
        };
    }

    /**
     * Apakah kejadian ini menutup keanggotaan siswa di rombel
     */
    public function closesClassGroupMembership(): bool
    {
        return match ($this) {
            self::TRANSFER_IN => false,
            default => true,
        };
    }
}
