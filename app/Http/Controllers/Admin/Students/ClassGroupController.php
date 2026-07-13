<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Data;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        // Keamanan lapis kedua di sisi server: Cek apakah rombel masih punya siswa
        if ($classGroup->students()->exists()) {
            return response()->noContent()->header('HX-Trigger', json_encode([
                'showAlert' => [
                    'icon' => 'error',
                    'title' => 'Tidak Bisa Dihapus!',
                    'text' => 'Rombel ini masih memiliki siswa di dalamnya.'
                ]
            ]));
        }

        try {
            $classGroup->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->noContent()->header('HX-Trigger', json_encode([
                'showAlert' => [
                    'icon' => 'error',
                    'title' => 'Tidak Bisa Dihapus!',
                    'text' => 'Rombel ini masih terhubung dengan data riwayat akademik.'
                ]
            ]));
        }

        // Trigger SweetAlert sukses dan refresh tabel (lewat classGroupSaved)
        return response()->noContent()->header('HX-Trigger', json_encode([
            'classGroupSaved' => true,
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data rombongan belajar berhasil dihapus.'
            ]
        ]));
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

            // PERBAIKAN: Hanya kembalikan tabel jika request benar-benar dari pencarian tabel
            if ($request->header('HX-Target') === 'students-container') {
                return view('pages.admin.students.groups.partials._students-table', compact('students', 'classGroup'));
            }

            // Jika request datang dari stats-cards-container, 
            // biarkan proses berlanjut ke bawah agar mengembalikan full view.
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
        $activeSemester = CoreSemester::where('status', 'active')->first();
        $semesterId = $activeSemester ? $activeSemester->id : null;

        return $request->validate([
            'name' => 'nullable|string|max:255',
            'grade_level' => 'required|string|in:10,11,12',
            'concentration_id' => 'required|exists:core_concentrations,id',
            'group_number' => 'required|integer|min:1',

            // Memastikan satu guru hanya memegang satu rombel di semester aktif
            'homeroom_teacher_id' => [
                'nullable',
                'exists:staff_data,id',
                Rule::unique('acd_class_groups', 'homeroom_teacher_id')
                    ->where('semester_id', $semesterId)
                    ->ignore($id) // Sangat penting agar tidak error saat proses Update rombel itu sendiri
            ],
        ], [
            // Pesan error kustom agar pengguna paham penyebab validasi gagal
            'homeroom_teacher_id.unique' => 'Guru ini sudah terdaftar sebagai wali kelas di rombel lain pada semester saat ini.'
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

        // Mengirim multiple trigger dalam bentuk JSON
        return response()->noContent()->header('HX-Trigger', json_encode([
            'classGroupSaved' => true,
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data rombongan belajar berhasil ditambahkan.'
            ]
        ]));
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

        // Mengirim multiple trigger dalam bentuk JSON
        return response()->noContent()->header('HX-Trigger', json_encode([
            'classGroupSaved' => true,
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Diperbarui!',
                'text' => 'Data rombongan belajar berhasil diperbarui.'
            ]
        ]));
    }

    /**
     * Menampilkan Modal Form Pindah Kelas
     */
    public function moveClassForm(string $classGroupId, string $studentId)
    {
        $currentClass = ClassGroup::findOrFail($classGroupId);
        // Sesuaikan nama model Siswa dengan yang ada di aplikasi Anda
        $student = Student::findOrFail($studentId);

        // Ambil daftar kelas dengan TINGKAT & SEMESTER yang sama, KECUALI kelas saat ini
        $availableClasses = ClassGroup::with('concentration')
            ->where('semester_id', $currentClass->semester_id)
            ->where('grade_level', $currentClass->grade_level)
            ->where('id', '!=', $currentClass->id)
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.admin.students.groups.partials._modal-move-class', compact('currentClass', 'student', 'availableClasses'));
    }

    /**
     * Memproses Perpindahan Kelas
     */
    public function moveClass(Request $request, string $classGroupId, string $studentId)
    {
        $request->validate([
            'target_class_group_id' => 'required|exists:acd_class_groups,id'
        ], [
            'target_class_group_id.required' => 'Pilih kelas tujuan terlebih dahulu.'
        ]);

        $currentClass = ClassGroup::findOrFail($classGroupId);
        $targetClass = ClassGroup::findOrFail($request->target_class_group_id);

        // Validasi Keamanan: Tolak jika mencoba pindah lintas tingkat secara paksa
        if ($currentClass->grade_level !== $targetClass->grade_level) {
            return response()->json(['message' => 'Pelanggaran sistem: Tidak diizinkan pindah lintas tingkat.'], 422);
        }

        // Proses pindah: Update ID rombel pada tabel pivot yang statusnya masih aktif
        ClassGroupStudent::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->where('status', 'active')
            ->update(['class_group_id' => $targetClass->id]);

        // Kirim trigger SweetAlert DAN trigger refresh data secara bersamaan
        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil Pindah!',
                'text' => 'Siswa berhasil dipindahkan ke ' . $targetClass->name
            ],
            'refreshClassData' => true // Trigger kustom untuk refresh partial
        ]));
    }
}
