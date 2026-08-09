<?php

namespace App\Models;

use App\Enums\Student\MutationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMutation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'acd_student_mutations';

    protected $guarded = ['id'];

    protected $casts = [
        'mutation_date' => 'date',
        'status'        => MutationStatus::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(CoreAcademicYear::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }
}
