<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardianVault extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'acd_guardians_vault';

    protected $guarded = ['id'];

    protected $casts = [
        'nik_encrypted' => 'encrypted',
        'phone_number_encrypted' => 'encrypted',
        'address_encrypted' => 'encrypted',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }
}
