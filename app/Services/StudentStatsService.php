<?php

namespace App\Services;

use App\Enums\Student\StudentStatus;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentStatsService
{
    private const ACTIVE_STATUSES = [StudentStatus::ACTIVE->value, StudentStatus::GRADUATED->value];

    private const INACTIVE_STATUSES = [StudentStatus::DROPPED_OUT->value, StudentStatus::TRANSFERRED_OUT->value];

    /**
     * @param Builder $baseQuery Query dasar (sudah difilter) dari StudentController::index().
     *                           Method ini akan meng-clone-nya untuk tiap hitungan agar
     *                           baseQuery asli tidak ikut ter-mutasi.
     * @param string|null $semesterId ID (UUID) semester aktif.
     */
    public function getStats(Builder $baseQuery, ?string $semesterId): array
    {
        // 1. STATISTIK SEMESTER AKTIF
        $totalStats = (clone $baseQuery)->count();

        // Agar angka di kartu "Total Siswa Aktif" sama dengan total di tabel,
        // status 'graduated' ikut dihitung sebagai aktif HANYA untuk siswa yang
        // masih terikat di rombel semester berjalan ini.
        $activeStats = (clone $baseQuery)->whereIn('status', self::ACTIVE_STATUSES)->count();

        $grade12Stats = $this->countByGrade($baseQuery, '12', $semesterId);
        $grade11Stats = $this->countByGrade($baseQuery, '11', $semesterId);
        $grade10Stats = $this->countByGrade($baseQuery, '10', $semesterId);

        // 2. STATISTIK HISTORIS (AKUMULATIF)
        // Dihitung dari global (tabel acd_students langsung) karena alumni dan
        // siswa pindah sudah tidak memiliki relasi ke semester aktif saat ini.
        $graduatedStats = Student::where('status', StudentStatus::GRADUATED->value)->count();
        $inactiveStats  = Student::whereIn('status', self::INACTIVE_STATUSES)->count();

        return compact(
            'totalStats',
            'activeStats',
            'graduatedStats',
            'inactiveStats',
            'grade12Stats',
            'grade11Stats',
            'grade10Stats'
        );
    }

    private function countByGrade(Builder $baseQuery, string $gradeLevel, ?string $semesterId): int
    {
        return (clone $baseQuery)->whereHas('activeClassGroup', function (Builder $query) use ($gradeLevel, $semesterId) {
            $query->where('grade_level', $gradeLevel)->where('semester_id', $semesterId);
        })->count();
    }
}
