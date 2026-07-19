<?php

namespace App\Enums\Student;

enum Transportation: string
{
    case JALAN_KAKI = 'jalan-kaki';
    case SEPEDA = 'sepeda';
    case KENDARAAN_PRIBADI = 'kendaraan-pribadi';
    case KENDARAAN_UMUM = 'kendaraan-umum';
    case ANTAR_JEMPUT = 'antar-jemput';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::JALAN_KAKI => 'Jalan Kaki',
            self::SEPEDA => 'Sepeda',
            self::KENDARAAN_PRIBADI => 'Kendaraan Pribadi',
            self::KENDARAAN_UMUM => 'Kendaraan Umum',
            self::ANTAR_JEMPUT => 'Antar Jemput',
            self::LAINNYA => 'Lainnya',
        };
    }
}
