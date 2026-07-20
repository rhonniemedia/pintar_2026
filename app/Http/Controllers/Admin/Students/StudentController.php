<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\Religion;
use App\Filters\StudentFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Students\UpdateStudentRequest;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Services\StudentStatsService;
use App\Traits\HasBlindIndex;
use Illuminate\Http\Request;

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

        // Jika kode mencapai baris ini, berarti validasi sudah lolos di UpdateStudentRequest
        $validated = $request->validated();

        // --- STEP 1: IDENTITAS ---
        if ($step === 1) {
            $student->update([
                'name'               => $validated['name'],
                'nick_name'          => $validated['nick_name'] ?? null,
                'gender'             => $validated['gender'],
                'child_order'        => $validated['child_order'] ?? null,
                'number_of_siblings' => $validated['number_of_siblings'] ?? null,
            ]);

            $vault = $student->vault ?? $student->vault()->create();
            $vault->pob_encrypted = $validated['pob'] ?? null;
            $vault->dob_encrypted = $validated['dob'] ?? null;
            $vault->nik_encrypted = $validated['nik'] ?? null;
            $vault->nik_hash      = !empty($validated['nik']) ? $this->blindIndexHash($validated['nik']) : null;
            $vault->religion_encrypted = !empty($validated['religion']) ? Religion::tryFrom($validated['religion'])?->value : null;
            $vault->religion_hash = !empty($validated['religion']) ? $this->blindIndexHash($validated['religion']) : null;
            $vault->save();

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 2], 303);
        }

        // --- STEP 2: ALAMAT & KONTAK ---
        if ($step === 2) {
            $student->update([
                'residence_type'     => $validated['residence_type'] ?? null,
                'transportation'     => $validated['transportation'] ?? null,
                'distance_to_school' => $validated['distance_to_school'] ?? null,
            ]);

            $vault = $student->vault ?? $student->vault()->create();
            $vault->phone_number_encrypted = $validated['phone_number'] ?? null;
            $vault->email_encrypted        = $validated['email'] ?? null;
            $vault->address_encrypted      = $validated['address'] ?? null;
            $vault->rt_encrypted           = $validated['rt'] ?? null;
            $vault->rw_encrypted           = $validated['rw'] ?? null;
            $vault->village_encrypted      = $validated['village'] ?? null;
            $vault->district_encrypted     = $validated['district'] ?? null;
            $vault->regency_encrypted      = $validated['regency'] ?? null;
            $vault->province_encrypted     = $validated['province'] ?? null;
            $vault->postal_code_encrypted  = $validated['postal_code'] ?? null;
            $vault->save();

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 3], 303);
        }

        // --- STEP 3: ORANGTUA/WALI ---
        if ($step === 3) {
            $guardian = $student->guardians()->updateOrCreate(
                ['relationship' => $validated['guardian_relationship']],
                [
                    'name'          => $validated['guardian_name'],
                    'living_status' => $validated['guardian_living_status'],
                    'birth_year'    => $validated['guardian_birth_year'] ?? null,
                    'occupation'    => $validated['guardian_occupation'] ?? null,
                    'education'     => $validated['guardian_education'] ?? null,
                    'income_range'  => $validated['guardian_income_range'] ?? null,
                ]
            );

            $guardianVault = $guardian->vault ?? $guardian->vault()->create();
            $guardianVault->nik_encrypted = $validated['guardian_nik'] ?? null;
            $guardianVault->phone_number_encrypted = $validated['guardian_phone_number'] ?? null;
            $guardianVault->address_encrypted = $validated['guardian_address'] ?? null;
            $guardianVault->save();

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 4], 303);
        }

        // --- STEP 4: AKADEMIK ---
        if ($step === 4) {
            $student->update([
                'previous_school'               => $validated['previous_school'] ?? null,
                'previous_school_npsn'          => $validated['previous_school_npsn'] ?? null,
                'previous_school_city'          => $validated['previous_school_city'] ?? null,
                'previous_school_province'      => $validated['previous_school_province'] ?? null,
                'graduation_certificate_number' => $validated['graduation_certificate_number'] ?? null,
                'graduation_year'               => $validated['graduation_year'] ?? null,
            ]);

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 5], 303);
        }

        // --- STEP 5: KESEHATAN & SELESAI ---
        if ($step === 5) {
            $student->update([
                'height'                 => $validated['height'] ?? null,
                'weight'                 => $validated['weight'] ?? null,
                'blood_type'             => $validated['blood_type'] ?? null,
                'is_special_condition'   => $validated['is_special_condition'] ?? 'no',
                'special_condition_type' => $validated['special_condition_type'] ?? null,
                'condition_description'  => $validated['condition_description'] ?? null,
                'medical_history'        => $validated['medical_history'] ?? null,
                'interest_art'           => $validated['interest_art'] ?? null,
                'interest_sport'         => $validated['interest_sport'] ?? null,
                'interest_organization'  => $validated['interest_organization'] ?? null,
                'extracurricular_choice' => $validated['extracurricular_choice'] ?? null,
            ]);

            // Menggunakan pola HX-Trigger dari ClassGroupPromotionController
            return response()->noContent()->header('HX-Trigger', json_encode([
                'close-modal' => true, // Mengirim event untuk menutup modal Alpine
                'showAlert' => [
                    'icon' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data siswa berhasil diperbarui.'
                ],
                'refreshStudentData' => true // Mengirim event untuk me-refresh tabel
            ]));
        }

        return redirect()->route('admin.students.data.index', $request->query(), 303);
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
