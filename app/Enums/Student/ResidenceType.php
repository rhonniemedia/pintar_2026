<?php

namespace App\Enums\Student;

enum ResidenceType: string
{
    case ORANG_TUA = 'orang-tua';
    case WALI = 'wali';
    case KOS = 'kos';
    case ASRAMA = 'asrama';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::ORANG_TUA => 'Bersama Orang Tua',
            self::WALI => 'Bersama Wali',
            self::KOS => 'Kos/Kontrak',
            self::ASRAMA => 'Asrama',
            self::LAINNYA => 'Lainnya',
        };
    }
}
