<?php

namespace App\Enums\Student;

enum FamilyRelation: string
{
    case AYAH = 'father';
    case IBU = 'mother';
    case WALI = 'guardian';

    public function label(): string
    {
        return match ($this) {
            self::AYAH => 'Ayah',
            self::IBU => 'Ibu',
            self::WALI => 'Wali',
        };
    }
}
