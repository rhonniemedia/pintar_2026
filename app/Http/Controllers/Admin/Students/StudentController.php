<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Traits\HasBlindIndex;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use HasBlindIndex;

    private const RELIGION_OPTIONS = [
        'Islam',
        'Kristen',
        'Katolik',
        'Hindu',
        'Buddha',
        'Konghucu',
        'Lainnya',
    ];

    public function index(Request $request)
    {
        $search              = $request->input('search');
        $filterStatus        = $request->input('filter_status');
        $filterGrade         = $request->input('filter_grade');
        $filterGender        = $request->input('filter_gender');
        $filterReligion      = $request->input('filter_religion');
        $filterSpecialNeeds  = $request->input('filter_special_needs');
        $filterConcentration = $request->input('filter_concentration');

        $semesterAktif = CoreSemester::where('status', 'active')->first();
        $semesterId    = $semesterAktif ? $semesterAktif->id : null;

        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        // 1. BUAT BASE QUERY
        $baseQuery = Student::with(['vault', 'concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            // Syarat Mutlak: Harus terdaftar di rombel semester ini
            ->whereHas('activeClassGroup', function ($q2) use ($semesterId) {
                $q2->where('semester_id', $semesterId);
            })
            // Terapkan Filter Status / Fallback
            ->when($filterStatus, function ($q) use ($filterStatus) {
                $q->where('status', $filterStatus);
            }, function ($q) {
                $q->whereIn('status', ['active', 'graduated']);
            })
            // Terapkan sisa filter lainnya
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($filterGrade, function ($q) use ($filterGrade, $semesterId) {
                $q->whereHas('activeClassGroup', function ($q2) use ($filterGrade, $semesterId) {
                    $q2->where('grade_level', $filterGrade)->where('semester_id', $semesterId);
                });
            })
            ->when($filterGender, fn($q) => $q->where('gender', $filterGender))
            ->when($filterSpecialNeeds, fn($q) => $q->where('is_special_condition', $filterSpecialNeeds))
            ->when($filterConcentration, function ($q) use ($filterConcentration, $semesterId) {
                $q->whereHas('activeClassGroup', function ($q2) use ($filterConcentration, $semesterId) {
                    $q2->where('semester_id', $semesterId)->where('concentration_id', $filterConcentration);
                });
            })
            ->when($filterReligion, function ($q) use ($filterReligion) {
                $hash = $this->blindIndexHash($filterReligion);
                $q->whereHas('vault', fn($q2) => $q2->where('religion_hash', $hash));
            });

        // 2. LEMPAR BASE QUERY KE PENGHITUNG STATS (Agar angka ikut terfilter dinamis)
        $stats = $this->getStats(clone $baseQuery, $semesterId);

        // 3. PAGINASI UNTUK TABEL
        $students = (clone $baseQuery)
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && !$request->header('HX-History-Restore-Request')) {
            return $this->renderPartials($students, $stats);
        }

        return view('pages.admin.students.data.index', array_merge(
            compact(
                'students',
                'search',
                'filterStatus',
                'filterGrade',
                'filterGender',
                'filterReligion',
                'filterSpecialNeeds',
                'filterConcentration',
                'concentrationOptions'
            ),
            $stats,
            ['religionOptions' => self::RELIGION_OPTIONS]
        ));
    }

    public function destroy(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        // Kembalikan ke index agar baseQuery dan state HTMX berjalan normal
        return $this->index($request);
    }

    private function getStats($baseQuery, $semesterId): array
    {
        // 1. STATISTIK SEMESTER AKTIF
        $totalStats = (clone $baseQuery)->count();

        // PERBAIKAN DISINI: 
        // Agar angka di kartu "Total Siswa Aktif" sama dengan total di tabel,
        // kita harus memasukkan status 'graduated' ke dalam hitungan aktif 
        // HANYA untuk siswa yang masih terikat di rombel semester berjalan ini.
        $activeStats = (clone $baseQuery)->whereIn('status', ['active', 'graduated'])->count();

        $grade12Stats = (clone $baseQuery)->whereHas('activeClassGroup', function ($query) use ($semesterId) {
            $query->where('grade_level', '12')->where('semester_id', $semesterId);
        })->count();

        $grade11Stats = (clone $baseQuery)->whereHas('activeClassGroup', function ($query) use ($semesterId) {
            $query->where('grade_level', '11')->where('semester_id', $semesterId);
        })->count();

        $grade10Stats = (clone $baseQuery)->whereHas('activeClassGroup', function ($query) use ($semesterId) {
            $query->where('grade_level', '10')->where('semester_id', $semesterId);
        })->count();

        // 2. STATISTIK HISTORIS (AKUMULATIF)
        // Dihitung dari global (tabel acd_students langsung) karena alumni dan
        // siswa pindah sudah tidak memiliki relasi ke semester aktif saat ini.
        $graduatedStats = Student::where('status', 'graduated')->count();
        $inactiveStats  = Student::whereIn('status', ['dropped_out', 'transferred_out'])->count();

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

    private function renderPartials($students, $stats): string
    {
        $stats['isOob'] = true;

        $tableHtml = view('pages.admin.students.data.partials._table', compact('students'))->render();
        $statsHtml = view('pages.admin.students.data.partials._stats-cards', $stats)->render();

        return $tableHtml . $statsHtml;
    }
}
