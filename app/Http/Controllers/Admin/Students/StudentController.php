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

    /**
     * Opsi agama untuk dropdown filter. Sesuaikan urutan/nilai jika
     * berbeda dari yang dipakai saat input data siswa, karena filter
     * ini mencocokkan blind-index hash secara persis (bukan LIKE).
     */
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

        // 1. Tambahkan penangkap input filter jurusan
        $filterConcentration = $request->input('filter_concentration');

        $stats = $this->getGlobalStats();

        // Ambil Semester Aktif untuk acuan tabel
        $semesterAktif = CoreSemester::where('status', 'active')->first();
        $semesterId    = $semesterAktif ? $semesterAktif->id : null;

        // 2. Ambil daftar jurusan untuk opsi dropdown
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        $students = Student::with(['vault', 'concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            // PERBAIKAN: sebelumnya where('status','active') saja, sehingga siswa kelas 12
            // yang baru diluluskan (status jadi 'graduated') hilang dari listing meski masih
            // tercatat di rombel semester berjalan. activeClassGroup() sudah mencakup pivot
            // status 'graduated' juga (lihat Student::activeClassGroup()).
            ->whereIn('status', ['active', 'graduated'])
            ->whereHas('activeClassGroup', function ($q2) use ($semesterId) {
                $q2->where('semester_id', $semesterId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($filterGrade, function ($q) use ($filterGrade, $semesterId) {
                $q->whereHas('activeClassGroup', function ($q2) use ($filterGrade, $semesterId) {
                    $q2->where('grade_level', $filterGrade)
                        ->where('semester_id', $semesterId);
                });
            })
            ->when($filterGender, fn($q) => $q->where('gender', $filterGender))
            ->when($filterSpecialNeeds, fn($q) => $q->where('is_special_condition', $filterSpecialNeeds))

            // 3. Tambahkan kondisi query untuk filter jurusan
            // PERBAIKAN: acuan jurusan yang benar adalah concentration_id milik ROMBEL
            // (acd_class_groups) tempat siswa aktif terdaftar, bukan kolom concentration_id
            // di acd_students — kolom itu bisa saja belum sinkron dengan rombel siswa saat ini,
            // sehingga hasilnya beda dari jumlah anggota rombel di ClassGroupController.
            ->when($filterConcentration, function ($q) use ($filterConcentration, $semesterId) {
                $q->whereHas('activeClassGroup', function ($q2) use ($filterConcentration, $semesterId) {
                    $q2->where('semester_id', $semesterId)
                        ->where('concentration_id', $filterConcentration);
                });
            })

            ->when($filterReligion, function ($q) use ($filterReligion) {
                $hash = $this->blindIndexHash($filterReligion);
                $q->whereHas('vault', fn($q2) => $q2->where('religion_hash', $hash));
            })
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
                'filterConcentration',  // 4. Kirim variabel ini ke view
                'concentrationOptions'  // 4. Kirim opsi jurusan ke view
            ),
            $stats,
            ['religionOptions' => self::RELIGION_OPTIONS]
        ));
    }

    public function destroy(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        $stats = $this->getGlobalStats();

        $semesterAktif = CoreSemester::where('status', 'active')->first();
        $semesterId    = $semesterAktif ? $semesterAktif->id : null;

        // PERBAIKAN: samakan dengan index() - jangan filter status 'active' saja,
        // supaya siswa kelas 12 yang sudah lulus tidak ikut hilang dari tabel hasil HTMX
        $students = Student::with(['vault', 'concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            ->whereIn('status', ['active', 'graduated'])
            ->whereHas('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return $this->renderPartials($students, $stats);
    }

    private function getGlobalStats(): array
    {
        // 1. Ambil Semester Aktif terlebih dahulu
        $semesterAktif = CoreSemester::where('status', 'active')->first();
        $semesterId    = $semesterAktif ? $semesterAktif->id : null;

        // 2. PERBAIKAN: Hapus when() agar jika $semesterId null, jumlah total & aktif akan 0 (tidak menjumlahkan semua semester)
        $totalStats = Student::whereHas('activeClassGroup', function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        })->count();

        $activeStats = Student::where('status', 'active')
            ->whereHas('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })->count();

        // Lulus dan keluar/pindah tidak terikat semester aktif (akumulatif historis)
        $graduatedStats = Student::where('status', 'graduated')->count();
        $inactiveStats  = Student::whereIn('status', ['dropped_out', 'transferred_out'])->count();

        // 3. Hitung siswa dengan memfilter grade_level DAN semester_id
        // PERBAIKAN: ikutkan status 'graduated' juga (bukan cuma 'active') supaya rombel
        // kelas 12 yang siswanya sudah diluluskan tidak jadi 0 di kartu statistik.
        $grade12Stats = Student::whereIn('status', ['active', 'graduated'])
            ->whereHas('activeClassGroup', function ($query) use ($semesterId) {
                $query->where('grade_level', '12')
                    ->where('semester_id', $semesterId);
            })->count();

        $grade11Stats = Student::whereIn('status', ['active', 'graduated'])
            ->whereHas('activeClassGroup', function ($query) use ($semesterId) {
                $query->where('grade_level', '11')
                    ->where('semester_id', $semesterId);
            })->count();

        $grade10Stats = Student::whereIn('status', ['active', 'graduated'])
            ->whereHas('activeClassGroup', function ($query) use ($semesterId) {
                $query->where('grade_level', '10')
                    ->where('semester_id', $semesterId);
            })->count();

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
        $stats['isOob'] = true; // Untuk HTMX out-of-band swap pada stats cards

        $tableHtml = view('pages.admin.students.data.partials._table', compact('students'))->render();
        $statsHtml = view('pages.admin.students.data.partials._stats-cards', $stats)->render();

        return $tableHtml . $statsHtml;
    }
}
