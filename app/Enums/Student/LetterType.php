<?php

namespace App\Enums\Student;

enum LetterType: string
{
    case ACTIVE       = 'active';
    case TRANSFER     = 'transfer';
    case GOOD_CONDUCT = 'good_conduct';
    case POOR_FAMILY  = 'poor_family';
    case SUMMONS      = 'summons';
    case DISMISSED    = 'dismissed';
    case RESIGNED     = 'resigned';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE       => 'Keterangan Aktif',
            self::TRANSFER     => 'Keterangan Pindah Sekolah',
            self::GOOD_CONDUCT => 'Keterangan Berkelakuan Baik',
            self::POOR_FAMILY  => 'Keterangan Tidak Mampu',
            self::SUMMONS      => 'Panggilan',
            self::DISMISSED    => 'Keputusan Pemberhentian',
            self::RESIGNED     => 'Keterangan Mengundurkan Diri',
        };
    }

    /**
     * Nomor klasifikasi surat sesuai kode arsip yang dipakai di kop surat
     * (mis. 421.5/.../O/SMKN1RL/{tahun}). Dipisah dari label supaya mudah
     * disesuaikan kalau ada perubahan pola penomoran nanti.
     */
    public function classificationCode(): string
    {
        return match ($this) {
            self::ACTIVE       => '421.5',
            self::TRANSFER     => '421.5',
            self::GOOD_CONDUCT => '421.5',
            self::POOR_FAMILY  => '421.5',
            self::SUMMONS      => '421.5',
            self::DISMISSED    => '421.5',
            self::RESIGNED     => '421.5',
        };
    }
}
