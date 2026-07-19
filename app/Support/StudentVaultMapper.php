<?php

namespace App\Support;

use App\Models\StudentVault;

class StudentVaultMapper
{
    /**
     * Mapping: key input tervalidasi => kolom terenkripsi di StudentVault.
     * Tambah field baru cukup tambah satu baris di sini.
     */
    private const FIELD_MAP = [
        'pob'          => 'pob_encrypted',
        'dob'          => 'dob_encrypted',
        'nik'          => 'nik_encrypted',
        'phone_number' => 'phone_number_encrypted',
        'email'        => 'email_encrypted',
        'address'      => 'address_encrypted',
        'rt'           => 'rt_encrypted',
        'rw'           => 'rw_encrypted',
        'village'      => 'village_encrypted',
        'district'     => 'district_encrypted',
        'regency'      => 'regency_encrypted',
        'province'     => 'province_encrypted',
        'postal_code'  => 'postal_code_encrypted',
    ];

    /**
     * Isi kolom *_encrypted di vault dari data tervalidasi.
     * Tidak menangani religion_hash — itu butuh HasBlindIndex, ditangani di controller/service pemanggil.
     */
    public static function fill(StudentVault $vault, array $validated): void
    {
        foreach (self::FIELD_MAP as $input => $column) {
            $vault->{$column} = $validated[$input] ?? null;
        }
    }
}
