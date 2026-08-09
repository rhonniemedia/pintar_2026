<?php

namespace App\Models;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    // Menentukan kolom yang boleh diisi secara massal
    protected $fillable = [
        'username',
        'password',
        'staff_id',
        'status'
    ];

    // Menyembunyikan data sensitif saat model diubah menjadi array/JSON
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ========================================================================
    // RELASI
    // ========================================================================

    /**
     * Relasi ke Profil Staf (Tabel staff_data)
     */
    public function staff()
    {
        return $this->belongsTo(Data::class, 'staff_id');
    }

    /**
     * Relasi Pivot untuk mengecek Hak Akses (Role) di masing-masing Aplikasi
     */
    public function roles()
    {
        return $this->belongsToMany(UserRole::class, 'user_app_roles', 'user_id', 'role_id')
            ->withPivot('id', 'app_id')
            ->withTimestamps();
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Helper agar middleware dan controller lebih clean saat mengecek akses aplikasi
     */
    public function hasAccessToApp(string $appId): bool
    {
        return $this->roles()
            ->wherePivot('app_id', $appId)
            ->exists();
    }
}
