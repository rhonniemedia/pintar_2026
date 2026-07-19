<?php

namespace App\Enums\Student;

enum StudentStatus: string
{
    case ACTIVE = 'active';
    case GRADUATED = 'graduated';
    case DROPPED_OUT = 'dropped_out';
    case TRANSFERRED_OUT = 'transferred_out';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::GRADUATED => 'Lulus',
            self::DROPPED_OUT => 'Dikeluarkan / Putus Sekolah',
            self::TRANSFERRED_OUT => 'Pindah Keluar',
        };
    }
}
