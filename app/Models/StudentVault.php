<?php

namespace App\Models;

use App\Enums\Student\Religion;
use App\Models\Student;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentVault extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'acd_students_vault';

    protected $guarded = ['id'];

    protected $casts = [
        'nisn_encrypted' => 'encrypted',
        'nik_encrypted' => 'encrypted',
        'pob_encrypted' => 'encrypted',
        'dob_encrypted' => 'encrypted',
        'religion_encrypted' => 'encrypted',
        'email_encrypted' => 'encrypted',
        'phone_number_encrypted' => 'encrypted',
        'address_encrypted' => 'encrypted',
        'rt_encrypted' => 'encrypted',
        'rw_encrypted' => 'encrypted',
        'village_encrypted' => 'encrypted',
        'district_encrypted' => 'encrypted',
        'regency_encrypted' => 'encrypted',
        'province_encrypted' => 'encrypted',
        'postal_code_encrypted' => 'encrypted',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Virtual Attribute untuk Agama (Religion)
     * 
     * Memungkinkan Anda mengakses $vault->religion sebagai Enum, 
     * sementara sistem tetap menyimpan dan mengenkripsinya ke dalam $vault->religion_encrypted
     */
    protected function religion(): Attribute
    {
        return Attribute::make(
            // Saat Get: Ambil nilai string yang sudah didekripsi oleh Laravel, ubah ke Enum
            get: fn() => $this->religion_encrypted ? Religion::tryFrom($this->religion_encrypted) : null,
        );
    }
}
