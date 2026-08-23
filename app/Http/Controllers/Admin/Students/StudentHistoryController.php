<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\CoreConcentration;
use App\Models\CoreSemester; // Pastikan model ini di-import
use App\Models\StudentMutation;
use Illuminate\Http\Request;

class StudentHistoryController extends Controller
{
    /**
     * Opsi status berdasarkan enum di tabel acd_student_mutations.
     */
    private const MUTATION_STATUS_OPTIONS = [
        'transfer_in'     => 'Masuk (Pindahan)',
        'transfer_out'    => 'Keluar (Pindah)',
        'dropped_out'     => 'Putus Sekolah',
        'deceased'        => 'Meninggal Dunia',
    ];

    public function index(Request $request)
    {
        $search               = $request->input('search');
        $filterExitStatus     = $request->input('filter_exit_status');
        $filterConcentration  = $request->input('filter_concentration');
        $filterExitSemester   = $request->input('filter_exit_semester');
        $filterYear           = $request->input('filter_year');

        // 1. Base Query langsung ke tabel Mutasi
        $baseQuery = StudentMutation::with(['student.vault', 'classGroup.concentration'])
            ->whereIn('status', array_keys(self::MUTATION_STATUS_OPTIONS))
            ->when($search, function ($query) use ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($filterExitStatus, function ($q) use ($filterExitStatus) {
                $q->where('status', $filterExitStatus);
            })
            ->when($filterConcentration, function ($q) use ($filterConcentration) {
                // Filter jurusan berdasarkan rombel saat mutasi terjadi
                $q->whereHas('classGroup', function ($q2) use ($filterConcentration) {
                    $q2->where('concentration_id', $filterConcentration);
                });
            })
            ->when($filterExitSemester, function ($q) use ($filterExitSemester) {
                // Ambil data semester dari database berdasarkan kode (contoh: '2025-1')
                $semester = CoreSemester::where('code', $filterExitSemester)->first();

                if ($semester) {
                    // Filter berdasarkan rentang tanggal resmi semester tersebut
                    $q->whereBetween('mutation_date', [$semester->start_date, $semester->end_date]);
                }
            })
            ->when($filterYear, function ($q) use ($filterYear) {
                // Filter berdasarkan tahun terjadinya mutasi (mutation_date)
                $q->whereYear('mutation_date', $filterYear);
            });

        // 2. Terapkan Stats dari Base Query
        $stats = $this->getStats(clone $baseQuery);

        // 3. Dropdown Options
        $concentrationOptions  = CoreConcentration::orderBy('name')->pluck('name', 'id');
        $exitSemesterOptions   = $this->getExitSemesterOptions();
        $yearOptions           = $this->getYearOptions();

        // 4. Data Tabel
        $students = (clone $baseQuery)
            ->orderByDesc('mutation_date')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && !$request->header('HX-History-Restore-Request')) {
            return $this->renderPartials($students, $stats);
        }

        return view('pages.admin.students.history.index', array_merge(
            compact(
                'students',
                'search',
                'filterExitStatus',
                'filterConcentration',
                'filterExitSemester',
                'filterYear',
                'concentrationOptions',
                'exitSemesterOptions',
                'yearOptions'
            ),
            $stats,
            ['exitStatusOptions' => self::MUTATION_STATUS_OPTIONS]
        ));
    }

    private function getStats($query): array
    {
        $transferInStats  = (clone $query)->where('status', 'transfer_in')->count();
        $transferOutStats = (clone $query)->where('status', 'transfer_out')->count();
        $droppedOutStats  = (clone $query)->where('status', 'dropped_out')->count();
        $deceasedStats    = (clone $query)->where('status', 'deceased')->count();

        $totalHistoryStats = $transferInStats + $transferOutStats + $droppedOutStats + $deceasedStats;

        return compact('totalHistoryStats', 'transferInStats', 'transferOutStats', 'droppedOutStats', 'deceasedStats');
    }

    private function getExitSemesterOptions()
    {
        // Mengambil kode semester dari tabel core_semesters, diurutkan dari yang terbaru berdasarkan tanggal mulai
        return CoreSemester::orderBy('start_date', 'desc')
            ->pluck('code')
            ->values();
    }

    private function getYearOptions()
    {
        // Mengambil daftar tahun unik dari tanggal mutasi, diurutkan dari yang terbaru
        return StudentMutation::whereNotNull('mutation_date')
            ->selectRaw('YEAR(mutation_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }

    private function renderPartials($students, $stats): string
    {
        $stats['isOob'] = true;
        $tableHtml = view('pages.admin.students.history.partials._table', compact('students'))->render();
        $statsHtml = view('pages.admin.students.history.partials._stats-cards', $stats)->render();
        return $tableHtml . $statsHtml;
    }
}
