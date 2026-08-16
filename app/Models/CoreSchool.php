<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreSchool extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'core_schools';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'establishment_date' => 'date',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? new static();
    }

    // ========================================================================
    // RELASI PENANDATANGAN SURAT
    // ========================================================================

    public function headmaster(): BelongsTo
    {
        return $this->belongsTo(Data::class, 'headmaster_staff_id');
    }

    public function studentAffairsDeputy(): BelongsTo
    {
        return $this->belongsTo(Data::class, 'student_affairs_deputy_staff_id');
    }

    public function administrationCoordinator(): BelongsTo
    {
        return $this->belongsTo(Data::class, 'administration_coordinator_staff_id');
    }
}
