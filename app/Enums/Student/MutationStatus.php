<?php

namespace App\Enums\Student;

enum MutationStatus: string
{
    case PINDAHAN = 'pindahan';
    case PINDAH_KELUAR = 'pindah';
    case KELUAR_DROPOUT = 'keluar';
    case MENGUNDURKAN_DIRI = 'mengundurkan-diri';
    case MENINGGAL = 'meninggal';
    case MENIKAH = 'menikah';
    case PUTUS_SEKOLAH = 'putus-sekolah';

    public function label(): string
    {
        return match ($this) {
            self::PINDAHAN => 'Pindahan',
            self::PINDAH_KELUAR => 'Pindah',
            self::KELUAR_DROPOUT => 'Dikeluarkan (Dropout)',
            self::MENGUNDURKAN_DIRI => 'Mengundurkan Diri',
            self::MENINGGAL => 'Meninggal',
            self::MENIKAH => 'Menikah',
            self::PUTUS_SEKOLAH => 'Putus Sekolah',
        };
    }
}
