<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory, HasUuids;

    // Menunjuk ke tabel roles di database pusat
    protected $table = 'user_roles';

    // Properti $incrementing dan $keyType dihapus karena trait HasUuids 
    // sudah otomatis mengatur primary key menjadi string non-increment.

    protected $guarded = ['id'];

    // ========================================================================
    // RELASI
    // ========================================================================

    /**
     * Relasi balik ke tabel Users melalui pivot user_app_roles
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_app_roles', 'role_id', 'user_id')
            ->withPivot('id', 'app_id')
            ->withTimestamps();
    }
}
