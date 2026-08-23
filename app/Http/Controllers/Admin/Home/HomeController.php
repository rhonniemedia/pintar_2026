<?php

namespace App\Http\Controllers\Admin\Home;

use App\Enums\Student\MutationStatus;
use App\Filters\StudentFilter;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Services\AcademicPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private readonly AcademicPeriod $academicPeriod,
    ) {}

    public function index(Request $request)
    {
        // 1. Ambil Semester ID mengikuti pilihan user di topbar atau default aktif
        $semesterId = $this->academicPeriod->currentId();

        // Ambil objek semester berdasarkan ID yang sedang dilihat untuk kebutuhan label di view
        $currentSemester = CoreSemester::with('academicYear')->find($semesterId);

        // 2. Fungsi Helper yang DISINKRONKAN DENGAN STUDENT CONTROLLER
        $countStudents = function ($grade = null, $gender = null) use ($semesterId) {
            // Kueri dasar sama persis dengan buildBaseQuery di StudentController
            $query = Student::whereHas('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

            // Gunakan StudentFilter agar filter bawaan (status = active) dieksekusi persis
            // seperti di halaman Master Data Siswa.
            $filter = new StudentFilter([
                'grade'  => $grade,
                'gender' => $gender,
            ], $semesterId);

            return $filter->apply($query)->count();
        };

        // --- STATISTIK KELAS ---
        $grade10Total  = $countStudents('10');
        $grade10Male   = $countStudents('10', 'L');
        $grade10Female = $countStudents('10', 'P');

        $grade11Total  = $countStudents('11');
        $grade11Male   = $countStudents('11', 'L');
        $grade11Female = $countStudents('11', 'P');

        $grade12Total  = $countStudents('12');
        $grade12Male   = $countStudents('12', 'L');
        $grade12Female = $countStudents('12', 'P');

        // Total Keseluruhan Siswa Aktif (Lebih akurat mengambil dari kueri utama daripada dijumlah manual)
        $totalActive = $countStudents();
        $totalMale   = $countStudents(null, 'L');
        $totalFemale = $countStudents(null, 'P');

        // --- KAPASITAS ROMBEL ---
        $classGroupsData = ClassGroup::with('homeroomTeacher')
            ->where('semester_id', $semesterId)
            ->withCount('activeStudents as filled')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->take(10)
            ->get()
            ->map(function ($group) {
                $capacity = $group->capacity ?? 36;
                $filled = $group->filled ?? 0;
                $percent = $capacity > 0 ? round(($filled / $capacity) * 100) : 0;

                $group->capacity = $capacity;
                $group->filled = $filled;
                $group->percent = $percent;
                $group->ratio_badge = $percent >= 100 ? 'bg-error/10 text-error-dark'
                    : ($percent >= 90 ? 'bg-warning/10 text-warning-dark' : 'bg-success/10 text-success-dark');
                $group->bar_color = $percent >= 100 ? '#EF4444' : ($percent >= 90 ? '#F59E0B' : '#10B981');

                return $group;
            });

        // --- SISWA TERBARU (DARI TABEL MUTASI) ---
        $latestStudentsData = StudentMutation::with(['student.activeClassGroup.concentration', 'student.vault'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->filter(fn($mutation) => $mutation->student !== null)
            ->map(function ($mutation) {
                $student = $mutation->student;
                $activeClass = $student->activeClassGroup->first();

                // Ambil nilai status (pastikan mencakup status masuk Anda, misal: 'transfer_in' atau 'new')
                $statusVal = is_object($mutation->status) ? $mutation->status->value : $mutation->status;
                $isMasuk = in_array(strtolower($statusVal), ['transfer_in', 'new', 'masuk', 'active']);

                return (object) [
                    'name'                => $student->name,
                    'nisn'                => $student->vault?->nisn_encrypted ?? '-',
                    'birth_place'         => $student->vault?->pob_encrypted ?? '-',
                    'birth_date'          => $student->vault?->dob_encrypted ? Carbon::parse($student->vault->dob_encrypted)->translatedFormat('d M Y') : '-',
                    'class_group_name'    => $activeClass ? $activeClass->name : '-',
                    'concentration_alias' => $activeClass?->concentration ? $activeClass->concentration->name : '-',
                    'concentration_color' => '#3B82F6',
                    'icon_class'          => $isMasuk ? 'bg-success/10 text-success' : 'bg-error/10 text-error',
                    'icon_name'           => $isMasuk ? 'chevrons-down' : 'chevrons-up',
                    'status_label'        => $isMasuk ? 'Masuk' : 'Keluar',
                ];
            })->values();

        // --- AKTIVITAS TERBARU (MUTASI) ---
        $latestMutationsData = StudentMutation::with('student')
            ->orderBy('created_at', 'desc')
            ->take(7)
            ->get()
            ->map(function ($mut) {
                $status  = MutationStatus::tryFrom(is_object($mut->status) ? $mut->status->value : (string) $mut->status);
                $isMasuk = $status === MutationStatus::TRANSFER_IN;

                $mut->student_name = $mut->student?->name ?? 'Sistem';
                $mut->description  = $mut->description ?? 'Melakukan perubahan data';
                $mut->context      = $status ? 'Mutasi ' . $status->label() : 'Mutasi';
                $mut->time_ago     = $mut->created_at->diffForHumans();
                $mut->icon_config  = match (true) {
                    $status === MutationStatus::GRADUATED => ['bg' => 'bg-info/10', 'text' => 'text-info', 'icon' => 'graduation-cap'],
                    $isMasuk                              => ['bg' => 'bg-success/10', 'text' => 'text-success', 'icon' => 'log-in'],
                    default                               => ['bg' => 'bg-error/10', 'text' => 'text-error', 'icon' => 'log-out'],
                };

                return $mut;
            });

        // --- TREN MUTASI 6 BULAN TERAKHIR ---
        // Daftar status diambil langsung dari enum agar selalu sinkron dengan migrasi.
        $masukStatuses  = [MutationStatus::TRANSFER_IN->value];
        $keluarStatuses = collect(MutationStatus::cases())
            ->filter(fn(MutationStatus $s) => $s !== MutationStatus::TRANSFER_IN)
            ->map(fn(MutationStatus $s) => $s->value)
            ->all();

        $months      = collect(range(5, 0))->map(fn($i) => now()->subMonthsNoOverflow($i));
        $trendLabels = $months->map(fn($m) => $m->translatedFormat('M'))->all();
        $trendMasuk  = [];
        $trendKeluar = [];

        foreach ($months as $m) {
            // PENTING: kelompokkan berdasarkan mutation_date (tanggal kejadian),
            // BUKAN created_at (tanggal di-input), agar mutasi yang di-input
            // terlambat tetap masuk ke bulan kejadiannya.
            $perBulan = fn() => StudentMutation::query()
                ->whereMonth('mutation_date', $m->month)
                ->whereYear('mutation_date', $m->year);

            $trendMasuk[]  = $perBulan()->whereIn('status', $masukStatuses)->count();
            $trendKeluar[] = $perBulan()->whereIn('status', $keluarStatuses)->count();
        }

        // 3. Susun Object Data
        $data = (object) [
            'academic_year' => (object) [
                'name'   => $currentSemester?->academicYear?->name ?? '-',
                'status' => $currentSemester?->status ?? '-'
            ],
            'semester' => (object) [
                'label' => $currentSemester?->isEven() ? 'Genap' : 'Ganjil'
            ],
            'stats' => (object) [
                'total_active_students' => $totalActive,
                'growth_students'       => 0,
                'grade_10' => (object) ['total' => $grade10Total, 'male' => $grade10Male, 'female' => $grade10Female],
                'grade_11' => (object) ['total' => $grade11Total, 'male' => $grade11Male, 'female' => $grade11Female],
                'grade_12' => (object) ['total' => $grade12Total, 'male' => $grade12Male, 'female' => $grade12Female],
            ],
            'class_groups'   => $classGroupsData,
            'mutations'      => $latestMutationsData,
            'students'       => $latestStudentsData,
            'mutation_trend' => [
                'labels' => $trendLabels,
                'masuk'  => $trendMasuk,
                'keluar' => $trendKeluar,
            ]
        ];

        // 4. Siapkan Data untuk Chart Donut
        $genderChartData = [
            ['label' => 'Laki-laki', 'count' => $totalMale, 'color' => '#3B82F6'],
            ['label' => 'Perempuan', 'count' => $totalFemale, 'color' => '#EC4899']
        ];

        $mutationTrendData = $data->mutation_trend;

        return view('pages.admin.home.index', compact('data', 'genderChartData', 'mutationTrendData'));
    }
}
