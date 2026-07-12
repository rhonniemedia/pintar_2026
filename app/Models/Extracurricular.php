<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extracurricular extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'acd_extracurriculars';

    protected $guarded = ['id'];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'acd_extracurricular_students')
            ->withPivot('score', 'description')
            ->withTimestamps();
    }
}
