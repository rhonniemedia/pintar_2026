<?php

namespace App\Enums\Student;

enum RegistrationType: string
{
    case NEW = 'new';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Siswa Baru',
            self::TRANSFER => 'Pindahan',
        };
    }
}
