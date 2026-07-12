<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'acd_students';

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function concentration(): BelongsTo
    {
        return $this->belongsTo(CoreConcentration::class);
    }

    public function vault(): HasOne
    {
        return $this->hasOne(StudentVault::class);
    }

    /**
     * Semua baris riwayat rombel siswa ini (pivot model langsung, bukan lewat belongsToMany).
     * Dipakai di ClassGroupPromotionController::promotionForm() untuk mengecek apakah siswa
     * sudah punya baris di rombel semester berikutnya (whereDoesntHave('student.classGroupStudents', ...)).
     */
    public function classGroupStudents(): HasMany
    {
        return $this->hasMany(ClassGroupStudent::class, 'student_id');
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(StudentViolation::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(StudentMutation::class);
    }

    public function classGroups(): BelongsToMany
    {
        return $this->belongsToMany(ClassGroup::class, 'acd_class_group_students')
            ->withPivot('entry_date', 'exit_date', 'status')
            ->withTimestamps();
    }

    /**
     * Rombel yang menjadi catatan keanggotaan sah siswa (status pivot = active/graduated).
     * Diakses via ->activeClassGroup->first() karena tetap berbentuk BelongsToMany.
     *
     * PERBAIKAN: sengaja mencakup status 'graduated' juga, bukan cuma 'active'. Alasannya:
     * saat siswa lulus (lihat ClassGroupPromotionController::graduate(), decision 'lulus'),
     * status pivot rombelnya diubah jadi 'graduated', BUKAN dihapus. Kalau relasi ini cuma
     * mengizinkan status 'active', siswa yang baru saja lulus jadi tidak match di whereHas(),
     * sehingga hilang dari listing/statistik siswa kelas 12 semester berjalan meski secara
     * data dia memang tercatat lulus dari rombel itu.
     */
    public function activeClassGroup(): BelongsToMany
    {
        return $this->classGroups()->wherePivotIn('status', ['active', 'graduated']);
    }

    public function extracurriculars(): BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class, 'acd_extracurricular_students')
            ->withPivot('score', 'description')
            ->withTimestamps();
    }
}
