<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\Religion;
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
use Illuminate\Support\Facades\Storage;

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
        ]);

        $semesterId = CoreSemester::where('status', 'active')->value('id');
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');
        $baseQuery = $this->buildBaseQuery($filters, $semesterId);

        $stats = $this->statsService->getStats(clone $baseQuery, $semesterId);

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
                'concentrationOptions' => $concentrationOptions,
            ],
            $stats,
            ['religionOptions' => Religion::cases()]
        ));
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
        $query = Student::with(['vault', 'concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
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
