<?php

namespace App\Enums\Student;

enum SpecialCondition: string
{
    case TIDAK_ADA = 'tidak-ada';
    case DISABILITAS_PENUH = 'disabilitas-penuh';
    case DISABILITAS_SEBAGIAN = 'disabilitas-sebagian';
    case GANGGUAN_PENGLIHATAN = 'gangguan-penglihatan-sebagian';
    case GANGGUAN_PENDENGARAN = 'gangguan-pendengaran-sebagian';
    case GANGGUAN_BICARA = 'gangguan-bicara';
    case FISIK_RINGAN = 'gangguan-fisik-ringan';
    case FISIK_BERAT = 'gangguan-fisik-sedang-berat';
    case KOGNITIF_RINGAN = 'gangguan-kognitif-ringan';
    case KOGNITIF_BERAT = 'gangguan-kognitif-sedang-berat';
    case PERILAKU_EMOSI = 'gangguan-perilaku-emosi';
    case MEDIS_KRONIS = 'kondisi-medis-kronis';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::TIDAK_ADA => 'Tidak Ada',
            self::DISABILITAS_PENUH => 'Disabilitas Penuh',
            self::DISABILITAS_SEBAGIAN => 'Disabilitas Sebagian',
            self::GANGGUAN_PENGLIHATAN => 'Gangguan Penglihatan Sebagian',
            self::GANGGUAN_PENDENGARAN => 'Gangguan Pendengaran Sebagian',
            self::GANGGUAN_BICARA => 'Gangguan Bicara',
            self::FISIK_RINGAN => 'Gangguan Fisik Ringan',
            self::FISIK_BERAT => 'Gangguan Fisik Sedang/Berat',
            self::KOGNITIF_RINGAN => 'Gangguan Kognitif Ringan',
            self::KOGNITIF_BERAT => 'Gangguan Kognitif Sedang/Berat',
            self::PERILAKU_EMOSI => 'Gangguan Perilaku/Emosi',
            self::MEDIS_KRONIS => 'Kondisi Medis Kronis',
            self::LAINNYA => 'Lainnya',
        };
    }
}
