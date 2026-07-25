<?php

namespace App\Enums\Student;

enum MutationStatus: string
{
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case DROPPED_OUT = 'dropped_out';
    case DISCONTINUED = 'discontinued';
    case RESIGNED = 'resigned';
    case DECEASED = 'deceased';
    case MARRIED = 'married';
    case GRADUATED = 'graduated';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER_IN => 'Pindah Masuk',
            self::TRANSFER_OUT => 'Pindah Sekolah',
            self::DROPPED_OUT => 'Dikeluarkan (Dropout)',
            self::DISCONTINUED => 'Putus Sekolah',
            self::RESIGNED => 'Mengundurkan Diri',
            self::DECEASED => 'Meninggal',
            self::MARRIED => 'Menikah',
            self::GRADUATED => 'Lulus',
        };
    }

    /**
     * Daftar status yang termasuk kategori "berhenti/keluar" (non transfer),
     * dipakai untuk mengisi dropdown "Rincian Alasan Keluar" di form mutasi
     * dan validasi detail_reason di controller.
     */
    public static function dropoutReasons(): array
    {
        return [
            self::DROPPED_OUT,
            self::DISCONTINUED,
            self::RESIGNED,
            self::MARRIED,
            self::DECEASED,
        ];
    }

    /**
     * Status siswa (acd_students.status) yang sesuai setelah kejadian ini
     * tercatat. Dipakai untuk update kolom cache status secara otomatis
     * dan konsisten, tanpa perlu mapping manual berulang di service/controller.
     */
    public function resultingStudentStatus(): StudentStatus
    {
        return match ($this) {
            self::TRANSFER_IN => StudentStatus::ACTIVE,
            self::TRANSFER_OUT => StudentStatus::TRANSFERRED_OUT,
            self::DROPPED_OUT => StudentStatus::DROPPED_OUT,
            self::DISCONTINUED => StudentStatus::DISCONTINUED,
            self::RESIGNED => StudentStatus::RESIGNED,
            self::DECEASED => StudentStatus::DECEASED,
            self::MARRIED => StudentStatus::MARRIED,
            self::GRADUATED => StudentStatus::GRADUATED,
        };
    }

    /**
     * Apakah kejadian ini menutup keanggotaan siswa di rombel
     * (mengisi acd_class_group_students.exit_date)?
     *
     * PENTING: MARRIED sengaja dibuat true di sini sebagai default aman.
     * Kalau kebijakan sekolah kamu mengizinkan siswa menikah tetap
     * lanjut sekolah, ubah nilai ini menjadi false dan tangani exit_date
     * secara terpisah di service layer sesuai kebijakan tersebut.
     */
    public function closesClassGroupMembership(): bool
    {
        return match ($this) {
            self::TRANSFER_IN => false,
            default => true,
        };
    }
}
