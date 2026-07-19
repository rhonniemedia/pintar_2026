<?php

namespace App\Enums\Student;

enum DistanceToSchool: string
{
    case KURANG_1_KM = 'kurang-1km';
    case ANTARA_1_3_KM = '1-3km';
    case ANTARA_3_5_KM = '3-5km';
    case ANTARA_5_10_KM = '5-10km';
    case LEBIH_10_KM = 'lebih-10km';

    public function label(): string
    {
        return match ($this) {
            self::KURANG_1_KM => 'Kurang dari 1 km',
            self::ANTARA_1_3_KM => '1 - 3 km',
            self::ANTARA_3_5_KM => '3 - 5 km',
            self::ANTARA_5_10_KM => '5 - 10 km',
            self::LEBIH_10_KM => 'Lebih dari 10 km',
        };
    }
}
