<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\ClassGroupStudent;
use App\Models\CoreConcentration;
use App\Models\Student;
use App\Models\StudentMutation;
use Illuminate\Http\Request;

class StudentHistoryController extends Controller
{
    /**
     * Status Student yang dianggap "riwayat" (sudah tidak aktif).
     * Key = nilai kolom Student.status, Value = label tampilan.
     */
    private const EXIT_STATUS_OPTIONS = [
        'graduated'        => 'Lulus',
        'dropped_out'      => 'Keluar',
        'transferred_out'  => 'Pindah',
    ];

    public function index(Request $request)
    {
        $search               = $request->input('search');
        $filterExitStatus     = $request->input('filter_exit_status');
        $filterConcentration  = $request->input('filter_concentration');
        $filterExitYear       = $request->input('filter_exit_year');

        $stats = $this->getStats();

        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');
        $exitYearOptions       = $this->getExitYearOptions();

        $students = Student::with([
            'vault',
            // Rombel terakhir siswa (bukan dibatasi semester aktif seperti di StudentController,
            // karena siswa riwayat bisa saja keluar/lulus di semester manapun di masa lalu).
            'classGroupStudents' => fn($q) => $q->orderByDesc('entry_date')->with('classGroup.concentration'),
            // Baris mutasi terbaru untuk keperluan alasan & tanggal keluar.
            'mutations' => fn($q) => $q->orderByDesc('mutation_date'),
        ])
            ->whereIn('status', array_keys(self::EXIT_STATUS_OPTIONS))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($filterExitStatus, fn($q) => $q->where('status', $filterExitStatus))
            // PERBAIKAN (mengikuti pola StudentController): acuan jurusan yang benar adalah
            // concentration_id milik ROMBEL siswa (riwayat rombel), bukan kolom concentration_id
            // di acd_students yang bisa saja tidak sinkron dengan rombel terakhirnya.
            ->when($filterConcentration, function ($q) use ($filterConcentration) {
                $q->whereHas('classGroupStudents.classGroup', function ($q2) use ($filterConcentration) {
                    $q2->where('concentration_id', $filterConcentration);
                });
            })
            // Tahun keluar bisa bersumber dari 2 tempat berbeda tergantung jenis keluarnya,
            // jadi dicek keduanya sekaligus (OR).
            ->when($filterExitYear, function ($q) use ($filterExitYear) {
                $q->where(function ($q2) use ($filterExitYear) {
                    $q2->whereHas('classGroupStudents', function ($q3) use ($filterExitYear) {
                        $q3->where('status', 'graduated')->whereYear('exit_date', $filterExitYear);
                    })->orWhereHas('mutations', function ($q3) use ($filterExitYear) {
                        $q3->whereYear('mutation_date', $filterExitYear);
                    });
                });
            })
            ->orderBy('name', 'asc')
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
            ['exitStatusOptions' => self::EXIT_STATUS_OPTIONS]
        ));
    }

    private function getStats(): array
    {
        $graduatedStats      = Student::where('status', 'graduated')->count();
        $droppedOutStats     = Student::where('status', 'dropped_out')->count();
        $transferredOutStats = Student::where('status', 'transferred_out')->count();
        $totalHistoryStats   = $graduatedStats + $droppedOutStats + $transferredOutStats;

        return compact('totalHistoryStats', 'graduatedStats', 'droppedOutStats', 'transferredOutStats');
    }

    /**
     * Opsi tahun untuk dropdown filter, diambil dari data riil (bukan angka statis)
     * supaya selalu sinkron: gabungan tahun exit_date rombel kelulusan + tahun mutation_date.
     */
    private function getExitYearOptions()
    {
        $graduationYears = ClassGroupStudent::where('status', 'graduated')
            ->whereNotNull('exit_date')
            ->selectRaw('YEAR(exit_date) as y')
            ->distinct()
            ->pluck('y');

        $mutationYears = StudentMutation::selectRaw('YEAR(mutation_date) as y')
            ->distinct()
            ->pluck('y');

        return $graduationYears->merge($mutationYears)
            ->filter()
            ->unique()
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
