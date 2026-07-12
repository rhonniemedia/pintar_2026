<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoreSemester extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'core_semesters';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    /**
     * Tahun ajaran induk semester ini.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(CoreAcademicYear::class, 'academic_year_id');
    }

    /**
     * Semester operasional berikutnya dalam rantai (lintas tahun ajaran).
     * Contoh: semester genap kelas X -> semester ganjil tahun ajaran berikutnya.
     */
    public function next(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_id');
    }

    /**
     * Semester-semester yang menjadikan semester ini sebagai "next".
     */
    public function previous(): HasMany
    {
        return $this->hasMany(self::class, 'next_id');
    }

    public function isEven(): bool
    {
        return $this->type === 'even';
    }

    public function isOdd(): bool
    {
        return $this->type === 'odd';
    }
}
