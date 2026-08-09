<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoreConcentration extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'core_concentrations';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    /**
     * Relasi ke ClassGroup (Rombongan Belajar)
     * Mengambil semua rombel yang terkait dengan konsentrasi ini.
     */
    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class, 'concentration_id');
    }
}
