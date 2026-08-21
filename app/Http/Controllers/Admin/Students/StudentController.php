<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\Religion;
use App\Exports\StudentsExport;
use App\Filters\StudentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Students\UpdateStudentPhotoRequest;
use App\Http\Requests\Admin\Students\UpdateStudentRequest;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Services\StudentStatsService;
use App\Traits\HasBlindIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use HasBlindIndex;

    public function __construct(
        private readonly StudentStatsService $statsService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'filter_status',
            'filter_grade',
            'filter_gender',
            'filter_religion',
            'filter_special_needs',
            'filter_concentration',
            'filter_age',
            'filter_age_date',
            'filter_orphan_status',
            'filter_food_allergy',
        ]);

        $semesterId = CoreSemester::where('status', 'active')->value('id');
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        // 1. Kueri untuk tabel (Terpengaruh oleh filter dan pencarian)
        $baseQuery = $this->buildBaseQuery($filters, $semesterId);

        // 2. Kueri untuk Statcard (FIXED - Tidak terpengaruh filter pengguna)
        // Mengirimkan array kosong [] agar layanan statistik menghitung total data secara utuh
        $unfilteredQuery = $this->buildBaseQuery([], $semesterId);
        $stats = $this->statsService->getStats($unfilteredQuery, $semesterId);

        $students = (clone $baseQuery)
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && !$request->header('HX-History-Restore-Request')) {
            return $this->renderPartials($students, $stats);
        }

        return view('pages.admin.students.data.index', array_merge(
            [
                'students'             => $students,
                'search'               => $filters['search'] ?? null,
                'filterStatus'         => $filters['filter_status'] ?? null,
                'filterGrade'          => $filters['filter_grade'] ?? null,
                'filterGender'         => $filters['filter_gender'] ?? null,
                'filterReligion'       => $filters['filter_religion'] ?? null,
                'filterSpecialNeeds'   => $filters['filter_special_needs'] ?? null,
                'filterConcentration'  => $filters['filter_concentration'] ?? null,
                'filterAge'            => $filters['filter_age'] ?? null,
                'filterAgeDate'        => $filters['filter_age_date'] ?? null,
                'filterOrphanStatus'   => $filters['filter_orphan_status'] ?? null,
                'filterFoodAllergy'    => $filters['filter_food_allergy'] ?? null,
                'concentrationOptions' => $concentrationOptions,
            ],
            $stats,
            ['religionOptions' => Religion::cases()]
        ));
    }

    /**
     * Menampilkan Modal Tambah Siswa Baru
     */
    public function create()
    {
        // Ambil data konsentrasi untuk dropdown
        $concentrations = CoreConcentration::orderBy('name')->get();

        return view('pages.admin.students.data.partials._create-modal', compact('concentrations'));
    }

    /**
     * Memproses Penyimpanan Data Siswa Baru
     */
    public function store(Request $request)
    {
        // 1. Buat Validator secara manual
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'nisn'              => 'required|numeric|digits:10',
            'nik'               => 'nullable|numeric|digits:16',
            'gender'            => 'required|in:L,P',
            'religion'          => 'nullable|string',
            'concentration_id'  => 'required|exists:core_concentrations,id',
            'entry_date'        => 'required|date',
            'entry_grade_level' => 'required|in:10,11,12',
            'registration_type' => 'required|in:new,transfer',
        ], [
            'nisn.digits' => 'NISN harus berjumlah tepat 10 digit angka.',
            'nik.digits'  => 'NIK harus berjumlah tepat 16 digit angka.',
        ]);

        $nisnHash = null;
        $nikHash = null;

        // 2. Tambahkan hook pengecekan hash kustom setelah validasi dasar selesai
        $validator->after(function ($validator) use ($request, &$nisnHash, &$nikHash) {
            // Pengecekan NISN
            if ($request->filled('nisn') && !$validator->errors()->has('nisn')) {
                $nisnHash = $this->blindIndexHash($request->nisn);
                if (DB::table('acd_students_vault')->where('nisn_hash', $nisnHash)->exists()) {
                    $validator->errors()->add('nisn', 'NISN ini sudah terdaftar pada siswa lain.');
                }
            }

            // Pengecekan NIK
            if ($request->filled('nik') && !$validator->errors()->has('nik')) {
                $nikHash = $this->blindIndexHash($request->nik);
                if (DB::table('acd_students_vault')->where('nik_hash', $nikHash)->exists()) {
                    $validator->errors()->add('nik', 'NIK ini sudah terdaftar pada siswa lain.');
                }
            }
        });

        // 3. TANGKAP ERROR: Jika gagal, JANGAN redirect. 
        // Kembalikan view modal secara langsung agar HTMX bisa menampilkan pesan errornya.
        if ($validator->fails()) {
            $request->flash(); // Simpan input lama agar fungsi old() di view tetap berjalan

            // Ambil ulang data konsentrasi yang dibutuhkan dropdown modal
            $concentrations = CoreConcentration::orderBy('name')->get();

            return view('pages.admin.students.data.partials._create-modal', compact('concentrations'))
                ->withErrors($validator);
        }

        // 4. Lanjutkan proses simpan jika lolos validasi
        $validated = $validator->validated();

        DB::transaction(function () use ($validated, $nisnHash, $nikHash) {
            $student = Student::create([
                'name'              => $validated['name'],
                'gender'            => $validated['gender'],
                'concentration_id'  => $validated['concentration_id'],
                'entry_date'        => $validated['entry_date'],
                'entry_grade_level' => $validated['entry_grade_level'],
                'registration_type' => $validated['registration_type'],
            ]);

            $religionVal = !empty($validated['religion']) ? Religion::tryFrom($validated['religion'])?->value : null;

            $student->vault()->create([
                'nisn_encrypted'     => $validated['nisn'],
                'nisn_hash'          => $nisnHash,

                'nik_encrypted'      => $validated['nik'] ?? null,
                'nik_hash'           => $nikHash,

                'religion_encrypted' => $religionVal,
                'religion_hash'      => $religionVal ? $this->blindIndexHash($religionVal) : null,
            ]);
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data peserta didik baru berhasil ditambahkan.'
            ]
        ]));
    }

    /**
     * Download data siswa ke Excel, mengikuti filter & pencarian yang
     * sedang aktif (bukan semua data). Filter diambil dari query string,
     * jadi tombol Download di view harus menyertakan query string yang
     * sama persis dengan yang dipakai tabel (lihat index.blade.php).
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'search',
            'filter_status',
            'filter_grade',
            'filter_gender',
            'filter_religion',
            'filter_special_needs',
            'filter_concentration',
            'filter_age',
            'filter_age_date',
            'filter_orphan_status',
            'filter_food_allergy',
        ]);

        $semesterId = CoreSemester::where('status', 'active')->value('id');

        // Pakai query builder yang SAMA dengan yang dipakai tabel (buildBaseQuery),
        // tapi tanpa paginate -> ambil semua baris yang lolos filter.
        $students = $this->buildBaseQuery($filters, $semesterId)
            ->orderBy('name', 'asc')
            ->get();

        $fileName = 'data-siswa-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new StudentsExport($students), $fileName);
    }

    public function floating(Request $request)
    {
        $filters = $request->only([
            'search',
            'filter_gender',
            'filter_religion',
            'filter_special_needs',
            'filter_concentration',
            'filter_age',
            'filter_age_date',
            'filter_orphan_status',
            'filter_food_allergy',
        ]);

        $semesterId = CoreSemester::where('status', 'active')->value('id');
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        // Query khusus mencari siswa yang TIDAK terdaftar di rombel manapun pada semester aktif
        $query = Student::with(['vault', 'concentration'])
            ->where('status', '!=', 'graduated')
            ->whereDoesntHave('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

        $studentFilter = new StudentFilter([
            'search'        => $filters['search'] ?? null,
            'status'        => null,
            'grade'         => null,
            'gender'        => $filters['filter_gender'] ?? null,
            'religion'      => $filters['filter_religion'] ?? null,
            'special_needs' => $filters['filter_special_needs'] ?? null,

            // 1. KOSONGKAN filter concentration di sini agar tidak memicu pencarian lewat Rombel 
            'concentration' => null,

            'age'           => $filters['filter_age'] ?? null,
            'age_reference_date' => $filters['filter_age_date'] ?? null,
            'orphan_status' => $filters['filter_orphan_status'] ?? null,
            'food_allergy'  => $filters['filter_food_allergy'] ?? null,
        ], $semesterId);

        $baseQuery = $studentFilter->apply($query);

        // 2. TERAPKAN FILTER JURUSAN SECARA MANUAL DI SINI
        if (!empty($filters['filter_concentration'])) {
            // Gunakan relasi 'concentration' bawaan dari model Student
            $baseQuery->whereHas('concentration', function ($q) use ($filters) {
                $q->where('id', $filters['filter_concentration']);
            });
        }

        // 1. Ambil semua siswa tanpa rombel di semester ini
        $rawFloatingQuery = Student::where('status', '!=', 'graduated')
            ->whereDoesntHave('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

        // 2. Lewatkan ke StudentFilter dengan array kosong 
        // Ini memastikan filter bawaan sistem (seperti status = aktif) tetap berjalan, 
        // namun mengabaikan filter pencarian/gender dari user.
        $emptyFilter = new StudentFilter([], $semesterId);
        $unfilteredQuery = $emptyFilter->apply($rawFloatingQuery);

        $floatingStats = [
            'totalFloating'  => (clone $unfilteredQuery)->count(),
            'maleFloating'   => (clone $unfilteredQuery)->where('gender', 'L')->count(),
            'femaleFloating' => (clone $unfilteredQuery)->where('gender', 'P')->count(),
        ];

        $students = (clone $baseQuery)
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && !$request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.data.partials._table', compact('students'))->render();
        }

        return view('pages.admin.students.floating.index', array_merge(
            [
                'students'             => $students,
                'search'               => $filters['search'] ?? null,
                'filterGender'         => $filters['filter_gender'] ?? null,
                'filterReligion'       => $filters['filter_religion'] ?? null,
                'filterSpecialNeeds'   => $filters['filter_special_needs'] ?? null,
                'filterConcentration'  => $filters['filter_concentration'] ?? null,
                'filterAge'            => $filters['filter_age'] ?? null,
                'filterAgeDate'        => $filters['filter_age_date'] ?? null,
                'filterOrphanStatus'   => $filters['filter_orphan_status'] ?? null,
                'filterFoodAllergy'    => $filters['filter_food_allergy'] ?? null,
                'concentrationOptions' => $concentrationOptions,
            ],
            $floatingStats, // <-- Masukkan variabel statcard di sini
            ['religionOptions' => Religion::cases()]
        ));
    }

    /**
     * Download data siswa mengambang ke Excel, mengikuti filter & pencarian yang sedang aktif.
     */
    public function exportFloating(Request $request)
    {
        $filters = $request->only([
            'search',
            'filter_gender',
            'filter_religion',
            'filter_special_needs',
            'filter_concentration',
            'filter_age',
            'filter_age_date',
            'filter_orphan_status',
            'filter_food_allergy',
        ]);

        $semesterId = CoreSemester::where('status', 'active')->value('id');

        $query = Student::with(['vault', 'concentration', 'guardians.vault'])
            ->where('status', '!=', 'graduated')
            ->whereDoesntHave('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

        $studentFilter = new StudentFilter([
            'search'        => $filters['search'] ?? null,
            'status'        => null,
            'grade'         => null,
            'gender'        => $filters['filter_gender'] ?? null,
            'religion'      => $filters['filter_religion'] ?? null,
            'special_needs' => $filters['filter_special_needs'] ?? null,
            'concentration' => null,
            'age'           => $filters['filter_age'] ?? null,
            'age_reference_date' => $filters['filter_age_date'] ?? null,
            'orphan_status' => $filters['filter_orphan_status'] ?? null,
            'food_allergy'  => $filters['filter_food_allergy'] ?? null,
        ], $semesterId);

        $baseQuery = $studentFilter->apply($query);

        if (!empty($filters['filter_concentration'])) {
            $baseQuery->whereHas('concentration', function ($q) use ($filters) {
                $q->where('id', $filters['filter_concentration']);
            });
        }

        $students = $baseQuery->orderBy('name', 'asc')->get();

        $fileName = 'data-siswa-mengambang-' . now()->format('Y-m-d_His') . '.xlsx';

        // Menggunakan StudentsExport yang sama karena kolom Kelas dan Rombel otomatis akan menjadi '-'
        return Excel::download(new StudentsExport($students), $fileName);
    }

    public function show(string $id)
    {
        $student = Student::with(['vault', 'concentration', 'activeClassGroup'])->findOrFail($id);
        return view('pages.admin.students.data.partials._show-modal', compact('student'));
    }

    public function showGuardian(string $id)
    {
        $student = Student::with(['guardians.vault'])->findOrFail($id);
        return view('pages.admin.students.data.partials._show-guardian-modal', compact('student'));
    }

    public function edit(Request $request, string $id)
    {
        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);
        $currentStep = $request->query('step', 1);

        return view('pages.admin.students.data.partials._edit-modal', compact('student', 'currentStep'));
    }

    public function update(UpdateStudentRequest $request, string $id)
    {
        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);
        $step = (int) $request->query('step', 1);

        // Validasi lolos, bersihkan data null agar tidak menimpa database dengan nilai kosong
        $validated = $request->validated();

        // Fungsi bantuan untuk membersihkan array dari nilai null
        $filterNulls = fn($array) => array_filter($array, fn($val) => !is_null($val));

        // --- STEP 1: IDENTITAS ---
        if ($step === 1) {
            $studentData = $filterNulls([
                'name'               => $validated['name'] ?? null,
                'gender'             => $validated['gender'] ?? null,
                'child_order'        => $validated['child_order'] ?? null,
                'number_of_siblings' => $validated['number_of_siblings'] ?? null,
            ]);

            if (!empty($studentData)) {
                $student->update($studentData);
            }

            $vault = $student->vault ?? $student->vault()->create();

            if (!is_null($validated['pob'] ?? null)) $vault->pob_encrypted = $validated['pob'];
            if (!is_null($validated['dob'] ?? null)) $vault->dob_encrypted = $validated['dob'];

            // Penambahan proses simpan NISN terenkripsi
            if (!is_null($validated['nisn'] ?? null)) {
                $vault->nisn_encrypted = $validated['nisn'];
                $vault->nisn_hash      = $this->blindIndexHash($validated['nisn']);
            }

            if (!is_null($validated['nik'] ?? null)) {
                $vault->nik_encrypted = $validated['nik'];
                $vault->nik_hash      = $this->blindIndexHash($validated['nik']);
            }

            if (!is_null($validated['religion'] ?? null)) {
                $vault->religion_encrypted = Religion::tryFrom($validated['religion'])?->value;
                $vault->religion_hash = $this->blindIndexHash($validated['religion']);
            }

            $vault->save();

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 2], 303);
        }

        // --- STEP 2: ALAMAT & KONTAK ---
        if ($step === 2) {
            $studentData = $filterNulls([
                'residence_type'     => $validated['residence_type'] ?? null,
                'transportation'     => $validated['transportation'] ?? null,
                'distance_to_school' => $validated['distance_to_school'] ?? null,
            ]);

            if (!empty($studentData)) {
                $student->update($studentData);
            }

            $vault = $student->vault ?? $student->vault()->create();

            $vaultFields = [
                'phone_number' => 'phone_number_encrypted',
                'email'        => 'email_encrypted',
                'address'      => 'address_encrypted',
                'rt'           => 'rt_encrypted',
                'rw'           => 'rw_encrypted',
                'village'      => 'village_encrypted',
                'district'     => 'district_encrypted',
                'regency'      => 'regency_encrypted',
                'province'     => 'province_encrypted',
                'postal_code'  => 'postal_code_encrypted'
            ];

            foreach ($vaultFields as $requestKey => $dbColumn) {
                if (!is_null($validated[$requestKey] ?? null)) {
                    $vault->{$dbColumn} = $validated[$requestKey];
                }
            }

            $vault->save();

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 3], 303);
        }

        // --- STEP 3: ORANGTUA/WALI ---
        if ($step === 3) {
            foreach ($validated['guardians'] as $relation => $guardianData) {
                // Abaikan dan jangan ubah apapun jika user mengosongkan nama orangtua/wali
                if (empty($guardianData['name'])) {
                    continue;
                }

                // PERBAIKAN: Pastikan key relasi bersih dan seragam (huruf kecil semua)
                // Jika form Anda mengirim dari input tersembunyi, gunakan $guardianData['relationship']
                $relationKey = strtolower(trim($guardianData['relationship'] ?? $relation));

                $guardianUpdateData = $filterNulls([
                    'name'          => $guardianData['name'] ?? null,
                    'living_status' => $guardianData['living_status'] ?? null,
                    'birth_year'    => $guardianData['birth_year'] ?? null,
                    'occupation'    => $guardianData['occupation'] ?? null,
                    'education'     => $guardianData['education'] ?? null,
                    'income_range'  => $guardianData['income_range'] ?? null,
                ]);

                // Gunakan relationKey yang sudah distandarisasi
                $guardian = $student->guardians()->updateOrCreate(
                    ['relationship' => $relationKey],
                    $guardianUpdateData
                );

                $guardianVault = $guardian->vault ?? $guardian->vault()->create();

                if (!is_null($guardianData['nik'] ?? null)) $guardianVault->nik_encrypted = $guardianData['nik'];
                if (!is_null($guardianData['phone_number'] ?? null)) $guardianVault->phone_number_encrypted = $guardianData['phone_number'];
                if (!is_null($guardianData['address'] ?? null)) $guardianVault->address_encrypted = $guardianData['address'];

                $guardianVault->save();
            }

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 4], 303);
        }

        // --- STEP 4: AKADEMIK ---
        if ($step === 4) {
            $studentData = $filterNulls([
                'previous_school'               => $validated['previous_school'] ?? null,
                'previous_school_npsn'          => $validated['previous_school_npsn'] ?? null,
                'previous_school_status'        => $validated['previous_school_status'] ?? null,
                'previous_school_city'          => $validated['previous_school_city'] ?? null,
                'previous_school_province'      => $validated['previous_school_province'] ?? null,
                'graduation_certificate_number' => $validated['graduation_certificate_number'] ?? null,
                'graduation_year'               => $validated['graduation_year'] ?? null,
            ]);

            if (!empty($studentData)) {
                $student->update($studentData);
            }

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 5], 303);
        }

        // --- STEP 5: KESEHATAN & SELESAI ---
        if ($step === 5) {
            // 1. Ambil data utama (yang difilter null-nya agar tidak menimpa data yang tidak dikirim)
            $studentData = $filterNulls([
                'height'                 => $validated['height'] ?? null,
                'weight'                 => $validated['weight'] ?? null,
                'blood_type'             => $validated['blood_type'] ?? null,
                'has_food_allergy'       => $validated['has_food_allergy'] ?? null,
                'is_special_condition'   => $validated['is_special_condition'] ?? null,
                'medical_history'        => $validated['medical_history'] ?? null,
                'interest_art'           => $validated['interest_art'] ?? null,
                'interest_sport'         => $validated['interest_sport'] ?? null,
                'interest_organization'  => $validated['interest_organization'] ?? null,
                'extracurricular_choice' => $validated['extracurricular_choice'] ?? null,
                'post_graduation_plan'   => $validated['post_graduation_plan'] ?? null,
                'willing_to_language_train' => $validated['willing_to_language_train'] ?? null,
                'ready_for_bkk_selection'   => $validated['ready_for_bkk_selection'] ?? null,
            ]);

            // 2. LOGIKA PEMBERSIHAN DATA KONDISIONAL (Memaksa NULL ke database jika opsi disembunyikan)

            // Alergi Makanan
            $studentData['food_allergy'] = ($validated['has_food_allergy'] ?? 'no') === 'yes'
                ? ($validated['food_allergy'] ?? null)
                : null;

            // Kondisi Khusus / Disabilitas (Ini yang mencegah error Enum "none")
            $studentData['special_condition_type'] = ($validated['is_special_condition'] ?? 'no') === 'yes'
                ? ($validated['special_condition_type'] ?? null)
                : null;
            $studentData['condition_description'] = ($validated['is_special_condition'] ?? 'no') === 'yes'
                ? ($validated['condition_description'] ?? null)
                : null;

            // Rencana Karir & BKK
            $isBekerja = ($validated['post_graduation_plan'] ?? '') === 'bekerja';
            $studentData['work_interest'] = $isBekerja ? ($validated['work_interest'] ?? null) : null;
            $studentData['foreign_language_skills'] = $isBekerja ? ($validated['foreign_language_skills'] ?? null) : null;

            $isLuarNegeri = $isBekerja && in_array($validated['work_interest'] ?? '', ['luar-negeri', 'bersedia-keduanya']);
            $studentData['target_country'] = $isLuarNegeri ? ($validated['target_country'] ?? null) : null;
            $studentData['target_program'] = $isLuarNegeri ? ($validated['target_program'] ?? null) : null;


            // 3. Eksekusi Update
            if (!empty($studentData)) {
                $student->update($studentData);
            }

            return response()->noContent()->header('HX-Trigger', json_encode([
                'close-modal' => true,
                'showAlert' => [
                    'icon' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data siswa berhasil diperbarui.'
                ],
                'refreshStudentData' => true
            ]));
        }

        return redirect()->route('admin.students.data.index', $request->query(), 303);
    }

    /**
     * Menampilkan Modal Edit Foto
     */
    public function editPhoto(string $id)
    {
        $student = Student::findOrFail($id);
        return view('pages.admin.students.data.partials._edit-photo-modal', compact('student'));
    }

    /**
     * Memproses Unggahan & Penyimpanan Foto
     */
    public function updatePhoto(UpdateStudentPhotoRequest $request, string $id)
    {
        $student = Student::findOrFail($id);

        if ($request->hasFile('photo')) {
            // 1. Hapus foto lama jika ada
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }

            // 2. Simpan foto baru ke dalam folder 'students/photos' di disk public
            $path = $request->file('photo')->store('students/photos', 'public');

            // 3. Update kolom database
            $student->update([
                'photo' => $path
            ]);
        }

        // 4. Berikan respon sukses untuk HTMX (Tutup modal & Refresh data tabel)
        return response()->noContent()->header('HX-Trigger', json_encode([
            'close-modal' => true,
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Pas foto siswa berhasil diperbarui.'
            ],
            'refreshStudentData' => true
        ]));
    }

    /**
     * Menampilkan Modal Informasi Generate NIS
     */
    public function generateNisModal()
    {
        $activeSemester = CoreSemester::where('status', 'active')->first();
        $semesterId = $activeSemester ? $activeSemester->id : null;

        // Cari siswa yang belum memiliki NIS dan sudah masuk rombel aktif
        $eligibleStudents = Student::with(['concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            ->whereNull('nis')
            ->whereHas('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.admin.students.data.partials._generate-nis-modal', compact('eligibleStudents'));
    }

    /**
     * Memproses Generate NIS
     */
    public function generateNis(Request $request)
    {
        // 1. Ambil Tahun Ajaran Aktif dari Database
        $activeAcademicYear = \App\Models\CoreAcademicYear::where('status', 'active')->first();

        if (!$activeAcademicYear) {
            return response()->noContent()->header('HX-Trigger', json_encode([
                'close-modal' => true,
                'showAlert' => [
                    'icon' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Tidak ada Tahun Ajaran yang aktif. Silakan atur terlebih dahulu di menu Data Master.'
                ]
            ]));
        }

        // 2. Cek kesesuaian Tahun Ajaran Aktif dengan waktu/tanggal berjalan saat ini
        $now = \Carbon\Carbon::now();
        $startDate = \Carbon\Carbon::parse($activeAcademicYear->start_date);
        $endDate = \Carbon\Carbon::parse($activeAcademicYear->end_date);

        if (!$now->between($startDate, $endDate)) {
            return response()->noContent()->header('HX-Trigger', json_encode([
                'close-modal' => true,
                'showAlert' => [
                    'icon' => 'warning',
                    'title' => 'Perhatian!',
                    'text' => "Tahun ajaran aktif saat ini ({$activeAcademicYear->name}) tidak sesuai dengan waktu berjalan. Pastikan Anda menggunakan tahun ajaran yang tepat di menu Data Master sebelum men-generate NIS."
                ]
            ]));
        }

        // 3. Ambil 2 digit tahun aktif (misal '2026/2027' -> ambil '26')
        $startYear = explode('/', $activeAcademicYear->name)[0];
        $yearCode = substr($startYear, 2, 2);

        // 4. Proses pencarian siswa tanpa NIS di rombel aktif
        $activeSemester = \App\Models\CoreSemester::where('status', 'active')->first();
        $semesterId = $activeSemester ? $activeSemester->id : null;

        // Ambil data siswa berserta relasinya (TANPA orderBy di database)
        $eligibleStudents = \App\Models\Student::with(['concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            ->whereNull('nis')
            ->whereHas('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            })
            ->get();

        if ($eligibleStudents->isEmpty()) {
            return response()->noContent()->header('HX-Trigger', json_encode([
                'close-modal' => true,
                'showAlert' => [
                    'icon' => 'info',
                    'title' => 'Tidak Ada Data',
                    'text' => 'Semua siswa di rombel aktif sudah memiliki NIS.'
                ]
            ]));
        }

        // 5. PENGURUTAN BERTINGKAT (Konsentrasi -> Rombel -> Abjad Nama)
        $sortedStudents = $eligibleStudents->sort(function ($a, $b) {
            // Urutkan berdasarkan Nama Konsentrasi
            $concA = $a->concentration->name ?? '';
            $concB = $b->concentration->name ?? '';
            if ($concA !== $concB) {
                return strcmp($concA, $concB);
            }

            // Jika Konsentrasi sama, urutkan berdasarkan Nama Rombel
            $rombelA = $a->activeClassGroup->first()->name ?? '';
            $rombelB = $b->activeClassGroup->first()->name ?? '';
            if ($rombelA !== $rombelB) {
                return strcmp($rombelA, $rombelB);
            }

            // Jika Rombel sama, urutkan berdasarkan Nama Siswa
            return strcmp($a->name, $b->name);
        })->values(); // values() digunakan untuk me-reset index array setelah disortir

        // 6. Cari urutan NIS terbesar di tahun ajaran aktif ini
        $lastStudent = \App\Models\Student::whereNotNull('nis')
            ->whereRaw("SUBSTRING(nis, 3, 2) = ?", [$yearCode])
            ->orderByRaw("CAST(SUBSTRING(nis, 5, 3) AS UNSIGNED) DESC")
            ->first();

        $lastSequence = 0;
        if ($lastStudent && strlen($lastStudent->nis) == 7) {
            $lastSequence = (int) substr($lastStudent->nis, 4, 3);
        }

        $generatedCount = 0;

        // 7. Eksekusi penyimpanan NIS menggunakan data yang sudah diurutkan rapi
        foreach ($sortedStudents as $student) {
            $concentrationCode = str_pad($student->concentration->code ?? $student->concentration_id, 2, '0', STR_PAD_LEFT);

            $lastSequence++;
            $sequenceCode = str_pad($lastSequence, 3, '0', STR_PAD_LEFT);

            $newNis = $concentrationCode . $yearCode . $sequenceCode;

            $student->update(['nis' => $newNis]);
            $generatedCount++;
        }

        return response()->noContent()->header('HX-Trigger', json_encode([
            'close-modal' => true,
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => "$generatedCount NIS siswa berhasil di-generate secara berurutan."
            ],
            'refreshStudentData' => true
        ]));
    }

    public function destroy(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Dihapus!',
                'text' => 'Data siswa berhasil dihapus.'
            ],
            'refreshStudentData' => true
        ]));
    }

    private function buildBaseQuery(array $filters, ?string $semesterId)
    {
        $query = Student::with(['vault', 'concentration', 'guardians.vault', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            ->whereHas('activeClassGroup', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });

        $studentFilter = new StudentFilter([
            'search'        => $filters['search'] ?? null,
            'status'        => $filters['filter_status'] ?? null,
            'grade'         => $filters['filter_grade'] ?? null,
            'gender'        => $filters['filter_gender'] ?? null,
            'religion'      => $filters['filter_religion'] ?? null,
            'special_needs' => $filters['filter_special_needs'] ?? null,
            'concentration' => $filters['filter_concentration'] ?? null,
            'age'           => $filters['filter_age'] ?? null,
            'age_reference_date' => $filters['filter_age_date'] ?? null,
            'orphan_status' => $filters['filter_orphan_status'] ?? null,
            'food_allergy'  => $filters['filter_food_allergy'] ?? null,
        ], $semesterId);

        return $studentFilter->apply($query);
    }

    private function renderPartials($students, array $stats): string
    {
        $stats['isOob'] = true;
        $tableHtml = view('pages.admin.students.data.partials._table', compact('students'))->render();
        $statsHtml = view('pages.admin.students.data.partials._stats-cards', $stats)->render();

        return $tableHtml . $statsHtml;
    }
}
