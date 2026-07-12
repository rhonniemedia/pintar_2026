<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Data;
use Illuminate\Http\Request;

class ClassGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $filterGrade = (string) $request->query('filter_grade', '');
        $filterConcentration = (string) $request->query('filter_concentration', '');

        $filterGender = (string) $request->query('filter_gender', '');
        $filterReligion = (string) $request->query('filter_religion', '');
        $filterSpecialNeeds = (string) $request->query('filter_special_needs', '');
        $religionOptions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

        $activeSemester = CoreSemester::where('status', 'active')->first();
        $semesterId = $activeSemester ? $activeSemester->id : null;

        // Nama tabel pivot diambil dinamis dari model ClassGroupStudent (bukan
        // di-hardcode), supaya tidak tergantung penamaan tabel yang sebenarnya.
        $cgsTable = (new ClassGroupStudent())->getTable();

        // PERBAIKAN: kondisi ini SENGAJA TIDAK memfilter exit_date, konsisten dengan
        // docblock ClassGroup::activeStudents() — exit_date diisi sebagai JADWAL pindah
        // semester berikutnya, bukan berarti siswa sudah keluar dari rombel ini sekarang.
        // Kondisi "anggota rombel ini" yang benar:
        // - pivot.status = 'active' DAN acd_students.status = 'active' -> ini persis
        //   logika activeStudents() di model.
        // - ATAU pivot.status = 'graduated' -> siswa lulus DARI rombel ini, tetap
        //   dihitung sebagai riwayat kelulusan rombel ini (tanpa syarat acd_students.status,
        //   karena begitu siswa lulus, acd_students.status juga biasanya berubah dari
        //   'active').
        $memberCondition = fn($q) => $q->where(
            fn($q2) => $q2->where(
                fn($q3) => $q3->where("{$cgsTable}.status", 'active')->where('acd_students.status', 'active')
            )->orWhere("{$cgsTable}.status", 'graduated')
        );

        $query = ClassGroup::query()
            ->with(['concentration', 'homeroomTeacher'])
            ->select('acd_class_groups.*')
            ->selectSub(
                fn($q) => $q->selectRaw('count(*)')->from($cgsTable)
                    ->join('acd_students', 'acd_students.id', '=', "{$cgsTable}.student_id")
                    ->whereColumn("{$cgsTable}.class_group_id", 'acd_class_groups.id')
                    ->tap($memberCondition),
                'total_students_count'
            )
            ->selectSub(
                fn($q) => $q->selectRaw('count(*)')->from($cgsTable)
                    ->join('acd_students', 'acd_students.id', '=', "{$cgsTable}.student_id")
                    ->whereColumn("{$cgsTable}.class_group_id", 'acd_class_groups.id')
                    ->where('acd_students.gender', 'L')
                    ->tap($memberCondition),
                'male_students_count'
            )
            ->selectSub(
                fn($q) => $q->selectRaw('count(*)')->from($cgsTable)
                    ->join('acd_students', 'acd_students.id', '=', "{$cgsTable}.student_id")
                    ->whereColumn("{$cgsTable}.class_group_id", 'acd_class_groups.id')
                    ->where('acd_students.gender', 'P')
                    ->tap($memberCondition),
                'female_students_count'
            )
            ->where('semester_id', $semesterId);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('concentration', fn($qc) => $qc->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('homeroomTeacher', fn($qt) => $qt->where('name', 'like', "%{$search}%"));
            });
        }

        if ($filterGrade !== '') {
            $query->where('grade_level', $filterGrade);
        }

        if ($filterConcentration !== '') {
            $query->where('concentration_id', $filterConcentration);
        }

        $classGroups = $query
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $totalStats = ClassGroup::where('semester_id', $semesterId)->count();

        $grade12Stats = ClassGroup::where('grade_level', '12')
            ->where('semester_id', $semesterId)
            ->count();

        $grade11Stats = ClassGroup::where('grade_level', '11')
            ->where('semester_id', $semesterId)
            ->count();

        $grade10Stats = ClassGroup::where('grade_level', '10')
            ->where('semester_id', $semesterId)
            ->count();

        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        return view('pages.admin.students.groups.index', compact(
            'classGroups',
            'search',
            'filterGrade',
            'filterConcentration',
            'concentrationOptions',
            'totalStats',
            'grade12Stats',
            'grade11Stats',
            'grade10Stats',
            'filterGender',
            'filterReligion',
            'filterSpecialNeeds',
            'religionOptions'
        ));
    }

    public function destroy(Request $request, string $id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $classGroup->delete();

        return $this->index($request);
    }

    public function show(Request $request, string $id)
    {
        // 1. Ambil metrik ringkasan rombel (Stats Card)
        // PERBAIKAN: sama seperti index() — kondisi mengikuti persis docblock
        // ClassGroup::activeStudents(): TIDAK memfilter exit_date (itu cuma jadwal
        // pindah semester berikutnya, bukan status keluar sekarang). Anggota rombel ini
        // = (pivot.status='active' DAN acd_students.status='active') ATAU
        // pivot.status='graduated' (riwayat lulus dari rombel ini).
        $cgsTable = (new ClassGroupStudent())->getTable();

        $memberCondition = fn($q) => $q->where(
            fn($q2) => $q2->where(
                fn($q3) => $q3->where("{$cgsTable}.status", 'active')->where('acd_students.status', 'active')
            )->orWhere("{$cgsTable}.status", 'graduated')
        );

        $classGroup = ClassGroup::with(['concentration', 'homeroomTeacher', 'semester'])
            ->select('acd_class_groups.*')
            ->selectSub(
                fn($q) => $q->selectRaw('count(*)')->from($cgsTable)
                    ->join('acd_students', 'acd_students.id', '=', "{$cgsTable}.student_id")
                    ->whereColumn("{$cgsTable}.class_group_id", 'acd_class_groups.id')
                    ->tap($memberCondition),
                'total_students_count'
            )
            ->selectSub(
                fn($q) => $q->selectRaw('count(*)')->from($cgsTable)
                    ->join('acd_students', 'acd_students.id', '=', "{$cgsTable}.student_id")
                    ->whereColumn("{$cgsTable}.class_group_id", 'acd_class_groups.id')
                    ->where('acd_students.gender', 'L')
                    ->tap($memberCondition),
                'male_students_count'
            )
            ->selectSub(
                fn($q) => $q->selectRaw('count(*)')->from($cgsTable)
                    ->join('acd_students', 'acd_students.id', '=', "{$cgsTable}.student_id")
                    ->whereColumn("{$cgsTable}.class_group_id", 'acd_class_groups.id')
                    ->where('acd_students.gender', 'P')
                    ->tap($memberCondition),
                'female_students_count'
            )
            ->findOrFail($id);

        $search = trim((string) $request->query('search', ''));
        $filterGender = (string) $request->query('filter_gender', '');

        // 2. Query anggota siswa di rombel ini
        // PERBAIKAN: filter roster ini disamakan persis dengan kondisi stats card di
        // atas — (pivot.status='active' DAN acd_students.status='active') ATAU
        // pivot.status='graduated' — supaya tabel dan angka "Total Anggota" dkk selalu
        // sinkron. Tidak memfilter exit_date, konsisten dengan docblock
        // ClassGroup::activeStudents().
        $students = $classGroup->students()
            ->where(function ($q) use ($cgsTable) {
                $q->where(
                    fn($q2) => $q2->where("{$cgsTable}.status", 'active')
                        ->where('acd_students.status', 'active')
                )->orWhere("{$cgsTable}.status", 'graduated');
            })
            ->with(['vault', 'concentration', 'activeClassGroup'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('acd_students.name', 'like', "%{$search}%")
                        ->orWhere('acd_students.nis', 'like', "%{$search}%");
                });
            })
            ->when($filterGender !== '', function ($q) use ($filterGender) {
                $q->where('acd_students.gender', $filterGender);
            })
            ->orderBy('acd_students.name', 'asc')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && !$request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.groups.partials._students-table', compact('students'));
        }

        return view('pages.admin.students.groups.show', compact('classGroup', 'students', 'search', 'filterGender'));
    }

    private function getFormOptions()
    {
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        // Sesuaikan Model yang digunakan untuk Wali Kelas di sistem Anda (misal: User role 'teacher', atau Staff)
        // $teacherOptions = User::where('role', 'teacher')->orderBy('name')->pluck('name', 'id');
        $teacherOptions = Data::where('status', 'active')
            ->whereHas('personnelType', function ($query) {
                $query->where('alias', 'guru');
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        return compact('concentrationOptions', 'teacherOptions');
    }

    /**
     * Helper validasi data Rombel
     */
    private function validateData(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'nullable|string|max:255',
            'grade_level' => 'required|string|in:10,11,12',
            'concentration_id' => 'required|exists:core_concentrations,id',
            'homeroom_teacher_id' => 'nullable', // sesuaikan: 'nullable|exists:users,id'
        ]);
    }

    public function create()
    {
        $options = $this->getFormOptions();
        return view('pages.admin.students.groups.partials._form-modal', $options);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $activeSemester = CoreSemester::where('status', 'active')->first();
        $data['semester_id'] = $activeSemester ? $activeSemester->id : null;

        ClassGroup::create($data);

        // Kembalikan response kosong dengan header trigger HTMX
        // Ini akan memberitahu halaman index untuk refresh tabel dan menutup modal
        return response()->noContent()->header('HX-Trigger', 'classGroupSaved');
    }

    public function edit($id)
    {
        $classGroup = ClassGroup::findOrFail($id);
        $options = $this->getFormOptions();

        return view('pages.admin.students.groups.partials._form-modal', array_merge(['classGroup' => $classGroup], $options));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateData($request, $id);

        $classGroup = ClassGroup::findOrFail($id);
        $classGroup->update($data);

        // Trigger event yang sama saat update sukses
        return response()->noContent()->header('HX-Trigger', 'classGroupSaved');
    }
}
