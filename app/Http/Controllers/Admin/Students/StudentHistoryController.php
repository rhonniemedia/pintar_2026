<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\CoreConcentration;
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
        $filterExitYear       = $request->input('filter_exit_year');

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
            ->when($filterExitYear, function ($q) use ($filterExitYear) {
                $q->whereYear('mutation_date', $filterExitYear);
            });

        // 2. Terapkan Stats dari Base Query
        $stats = $this->getStats(clone $baseQuery);

        // 3. Dropdown Options
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');
        $exitYearOptions       = $this->getExitYearOptions();

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
                'filterExitYear',
                'concentrationOptions',
                'exitYearOptions'
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

    private function getExitYearOptions()
    {
        return StudentMutation::selectRaw('YEAR(mutation_date) as y')
            ->distinct()
            ->pluck('y')
            ->filter()
            ->sortDesc()
            ->values();
    }

    private function renderPartials($students, $stats): string
    {
        $stats['isOob'] = true;
        $tableHtml = view('pages.admin.students.history.partials._table', compact('students'))->render();
        $statsHtml = view('pages.admin.students.history.partials._stats-cards', $stats)->render();
        return $tableHtml . $statsHtml;
    }
}
