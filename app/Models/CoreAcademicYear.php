<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoreAcademicYear extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'core_academic_years';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function next(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_id');
    }

    public function previous(): HasMany
    {
        return $this->hasMany(self::class, 'next_id');
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(CoreSemester::class, 'academic_year_id');
    }
}
