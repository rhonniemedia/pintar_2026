<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\MutationStatus;
use App\Enums\Student\StudentStatus;
use App\Filters\StudentFilter;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Data;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClassGroupController extends Controller
{
    /**
     * Definisi tunggal "anggota rombel" (dipakai di index, show stats, dan
     * show roster) - satu-satunya tempat logika ini ditulis, supaya kalau
     * kebijakan berubah, cukup ubah di sini.
     *
     * Anggota rombel = exit_date masih NULL (belum keluar sama sekali),
     * ATAU sudah keluar tapi sebabnya GRADUATED (tamat) - siswa yang lulus
     * tetap dihitung/tampil sebagai riwayat anggota rombel terakhirnya,
     * supaya rombel kelas 12 yang sudah lulus semua tidak tampil kosong.
     *
     * Sebab keluar lain (pindah, keluar, meninggal, menikah, DO, resign,
     * atau pindah kelas internal) TIDAK lagi dihitung sebagai anggota
     * rombel ini.
     */
    private function memberOfClassGroupCondition(string $cgsTable): \Closure
    {
        return function ($q) use ($cgsTable) {
            // 1. Status siswa utama (di acd_students) harus ACTIVE atau GRADUATED
            $q->whereIn('acd_students.status', [
                StudentStatus::ACTIVE->value,
                StudentStatus::GRADUATED->value
            ])
                // 2. PERBAIKAN: Gunakan exit_reason, bukan exit_date
                ->whereNull("{$cgsTable}.exit_reason")
                // 3. PERBAIKAN: Tidak ada mutasi keluar, kecuali mutasi Lulus
                ->where(function ($qPivot) use ($cgsTable) {
                    $qPivot->whereNull("{$cgsTable}.mutation_id")
                        ->orWhereExists(function ($sub) use ($cgsTable) {
                            $sub->selectRaw('1')
                                ->from('acd_student_mutations')
                                ->whereColumn('acd_student_mutations.id', "{$cgsTable}.mutation_id")
                                ->where('acd_student_mutations.status', MutationStatus::GRADUATED->value);
                        });
                });
        };
    }

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

        // Kondisi "anggota rombel" - lihat memberOfClassGroupCondition() untuk
        // penjelasan lengkap. Sudah tidak bergantung pada kolom `status`
        // yang dihapus dari acd_class_group_students.
        $memberCondition = $this->memberOfClassGroupCondition($cgsTable);

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

    public function addStudentForm(ClassGroup $classGroup)
    {
        $semesterId = $classGroup->semester_id;

        // 1. Kueri dasar: Siswa dengan jurusan yang sama, BUKAN lulusan, & belum punya rombel di semester ini
        $rawQuery = Student::with(['vault', 'concentration'])
            ->where('concentration_id', $classGroup->concentration_id)
            ->where('status', '!=', 'graduated') // <-- Tambahan kondisi pengecualian status graduated
            ->whereDoesntHave('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

        // 2. Lewatkan ke StudentFilter agar membuang data mutasi/drop out (hanya ambil yang Aktif)
        $emptyFilter = new StudentFilter([], $semesterId);

        // 3. Eksekusi kueri yang sudah difilter
        $floatingStudents = $emptyFilter->apply($rawQuery)
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.admin.students.groups.partials._add-student-modal', compact('classGroup', 'floatingStudents'));
    }

    /**
     * Memproses penambahan siswa mengambang (floating) ke sebuah rombel.
     * Dipanggil dari form di _add-student-modal.blade.php
     */
    public function storeStudent(Request $request, ClassGroup $classGroup)
    {
        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:acd_students,id',
        ], [
            'student_ids.required' => 'Pilih minimal satu siswa terlebih dahulu.',
            'student_ids.*.exists' => 'Salah satu siswa yang dipilih tidak valid atau sudah tidak tersedia.',
        ]);

        // Gagal validasi -> kembalikan status non-2xx (422) supaya HTMX TIDAK
        // menutup modal atau menukar #students-container (lihat @htmx:after-request
        // di _add-student-modal.blade.php yang hanya modalOpen=false saat successful).
        if ($validator->fails()) {
            return response()->noContent(422)->header('HX-Trigger', json_encode([
                'showAlert' => [
                    'icon' => 'error',
                    'title' => 'Gagal!',
                    'text' => $validator->errors()->first(),
                ]
            ]));
        }

        $data = $validator->validated();

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($data['student_ids'] as $studentId) {
            // Hindari duplikasi: hanya insert jika siswa belum punya baris aktif
            // (exit_date belum terisi) di rombel ini pada semester yang sama.
            $exists = ClassGroupStudent::where('class_group_id', $classGroup->id)
                ->where('student_id', $studentId)
                ->whereNull('exit_date')
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            ClassGroupStudent::create([
                'class_group_id' => $classGroup->id,
                'student_id' => $studentId,
                'entry_date' => now(),
            ]);
            $addedCount++;
        }

        // Susun pesan alert sesuai hasil: gagal total (info), sukses sebagian,
        // atau sukses penuh - supaya user tahu persis apa yang terjadi.
        if ($addedCount === 0) {
            $alert = [
                'icon' => 'info',
                'title' => 'Tidak Ada Perubahan',
                'text' => 'Siswa yang dipilih sudah terdaftar di rombel ini sebelumnya.',
            ];
        } elseif ($skippedCount > 0) {
            $alert = [
                'icon' => 'success',
                'title' => 'Berhasil Sebagian',
                'text' => "{$addedCount} siswa ditambahkan, {$skippedCount} siswa dilewati karena sudah terdaftar.",
            ];
        } else {
            $alert = [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => "{$addedCount} siswa berhasil ditambahkan ke rombel.",
            ];
        }

        // Susun ulang query anggota rombel (sama seperti method show()) supaya
        // partial tabel yang dikembalikan konsisten dengan halaman utama.
        $cgsTable = (new ClassGroupStudent())->getTable();
        $memberCondition = $this->memberOfClassGroupCondition($cgsTable);

        $students = $classGroup->students()
            ->where($memberCondition)
            ->with(['vault', 'concentration', 'activeClassGroup'])
            ->orderBy('acd_students.name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return response()
            ->view('pages.admin.students.groups.partials._students-table', compact('students', 'classGroup'))
            ->header('HX-Trigger', json_encode([
                'showAlert' => $alert,
                // Trigger ini yang bikin #stats-cards-container di show.blade.php
                // ikut refresh (lihat hx-trigger="refreshClassData from:body").
                // Sebelumnya trigger ini tidak dikirim, makanya stat card statis.
                'refreshClassData' => true,
            ]));
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
        } catch (QueryException $e) {
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
        // Kondisi "anggota rombel" - lihat memberOfClassGroupCondition().
        $cgsTable = (new ClassGroupStudent())->getTable();

        $memberCondition = $this->memberOfClassGroupCondition($cgsTable);

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
        // Cukup panggil $memberCondition agar 100% sinkron dengan Stats Card!
        $students = $classGroup->students()
            ->where($memberCondition)
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

        // Menggunakan model Data kembali
        $teacherOptions = Data::where('status', 'active')
            ->whereHas('personnelType', function ($query) {
                // Perlebar filter: Cari jika alias='guru' ATAU namanya mengandung kata 'guru'
                $query->where('alias', 'guru')
                    ->orWhere('alias', 'GURU')
                    ->orWhere('name', 'like', '%Guru%');
            })
            ->get()
            // Memanfaatkan accessor name_with_title dari model Data agar gelar tampil di form
            ->pluck('name_with_title', 'id');

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

        // Proses pindah: Update ID rombel pada baris pivot yang masih aktif
        // (exit_date belum terisi = siswa memang masih terdaftar di rombel asal).
        // Pindah kelas internal TIDAK menyentuh acd_student_mutations, karena
        // siswa tetap berstatus AKTIF - hanya berpindah rombel.
        ClassGroupStudent::where('student_id', $studentId)
            ->where('class_group_id', $classGroupId)
            ->whereNull('exit_date')
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
