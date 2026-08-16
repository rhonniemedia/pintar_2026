<?php

namespace App\Models;

use App\Enums\Student\LetterType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLetter extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'acd_student_letters';

    protected $guarded = ['id'];

    protected $casts = [
        'letter_type' => LetterType::class,
        'letter_date' => 'date',
        'meta'        => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(CoreSemester::class, 'semester_id');
    }

    /**
     * User (staf) yang menerbitkan surat ini.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
