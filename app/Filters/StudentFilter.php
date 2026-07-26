<?php

namespace App\Filters;

use App\Enums\Student\StudentStatus;
use App\Traits\HasBlindIndex;
use Carbon\Carbon;
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
            })
            ->when($this->filters['age'] ?? null, function (Builder $q, string $age) {
                $referenceDate = $this->filters['age_reference_date'] ?? now()->toDateString();
                $hashes = $this->generateDobHashesForAge((int) $age, $referenceDate);
                $q->whereHas('vault', fn(Builder $q2) => $q2->whereIn('dob_hash', $hashes));
            });
    }

    /**
     * Menghasilkan daftar blind-index hash dari seluruh kemungkinan tanggal lahir
     * yang membuat seseorang berusia $age tepat pada $referenceDate.
     *
     * dob_hash bersifat deterministik per-tanggal (bukan range-queryable), sehingga
     * pencarian rentang usia dilakukan dengan menghitung rentang tanggal lahir
     * (maksimal ±366 hari), menghash tiap tanggal, lalu mencocokkan dengan whereIn.
     */
    private function generateDobHashesForAge(int $age, string $referenceDate): array
    {
        $reference = Carbon::parse($referenceDate);

        // Tanggal lahir "termuda" yang tetap genap berusia $age di tanggal acuan
        $end = $reference->copy()->subYears($age);
        // Tanggal lahir "tertua" yang belum genap berusia ($age + 1) di tanggal acuan
        $start = $reference->copy()->subYears($age + 1)->addDay();

        $hashes = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $hashes[] = $this->blindIndexHash($cursor->toDateString());
            $cursor->addDay();
        }

        return $hashes;
    }
}
