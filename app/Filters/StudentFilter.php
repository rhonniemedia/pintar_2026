<?php

namespace App\Filters;

use App\Enums\Student\StudentStatus;
use App\Traits\HasBlindIndex;
use Illuminate\Database\Eloquent\Builder;

class StudentFilter
{
    use HasBlindIndex;

    /**
     * Status yang dianggap "masih aktif terikat" di rombel semester berjalan.
     * Dipakai sebagai fallback filter saat filter_status tidak diisi.
     */
    private const ACTIVE_STATUSES = [StudentStatus::ACTIVE->value, StudentStatus::GRADUATED->value];

    /**
     * @param array $filters Diharapkan berisi key: search, status, grade, gender,
     *                       religion, special_needs, concentration (semua opsional/nullable).
     * @param string|null $semesterId ID (UUID) semester aktif, dipakai untuk filter grade &
     *                                concentration yang bergantung pada activeClassGroup di
     *                                semester berjalan.
     */
    public function __construct(
        private readonly array $filters,
        private readonly ?string $semesterId,
    ) {}

    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->filters['status'] ?? null, function (Builder $q, string $status) {
                $q->where('status', $status);
            }, function (Builder $q) {
                $q->whereIn('status', self::ACTIVE_STATUSES);
            })
            ->when($this->filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['grade'] ?? null, function (Builder $q, string $grade) {
                $q->whereHas('activeClassGroup', function (Builder $q2) use ($grade) {
                    $q2->where('grade_level', $grade)->where('semester_id', $this->semesterId);
                });
            })
            ->when($this->filters['gender'] ?? null, function (Builder $q, string $gender) {
                $q->where('gender', $gender);
            })
            ->when($this->filters['special_needs'] ?? null, function (Builder $q, string $specialNeeds) {
                $q->where('is_special_condition', $specialNeeds);
            })
            ->when($this->filters['concentration'] ?? null, function (Builder $q, string $concentrationId) {
                $q->whereHas('activeClassGroup', function (Builder $q2) use ($concentrationId) {
                    $q2->where('semester_id', $this->semesterId)
                        ->where('concentration_id', $concentrationId);
                });
            })
            ->when($this->filters['religion'] ?? null, function (Builder $q, string $religion) {
                $hash = $this->blindIndexHash($religion);
                $q->whereHas('vault', fn(Builder $q2) => $q2->where('religion_hash', $hash));
            });
    }
}
