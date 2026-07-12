<?php

namespace App\Traits;

trait HasBlindIndex
{
    /**
     * Hash sebuah nilai untuk pencarian blind-index (HMAC-SHA256),
     * mengikuti pola *_hash di acd_students_vault (nisn_hash, nik_hash,
     * religion_hash, dst).
     *
     * PENTING: sesuaikan implementasi ini (key & normalisasi) dengan
     * hashing yang dipakai saat data vault pertama kali disimpan
     * (mis. saat proses PPDB/registrasi). Kalau berbeda, pencarian/filter
     * berdasarkan field vault tidak akan menemukan hasil yang cocok.
     */
    protected function blindIndexHash(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        return hash_hmac('sha256', $normalized, config('app.key'));
    }
}
