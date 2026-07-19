<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\Religion;
use App\Filters\StudentFilter;
// Religion dipakai di update() untuk Religion::tryFrom(), bukan lagi untuk
// membangun daftar opsi <select> — blade agama sekarang loop Religion::cases() sendiri.
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Students\UpdateStudentRequest;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Services\StudentStatsService;
use App\Support\StudentVaultMapper;
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

        // Lempar base query ke penghitung stats agar angka ikut terfilter dinamis
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
        // Load student beserta guardian pertama (jika ada)
        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);
        $currentStep = $request->query('step', 1);

        return view('pages.admin.students.data.partials._edit-modal', compact('student', 'currentStep'));
    }

    public function update(Request $request, string $id)
    {
        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);
        $step = (int) $request->query('step', 1);

        // --- STEP 1: IDENTITAS ---
        if ($step === 1) {
            $validated = $request->validate([
                'name'               => 'required|string|max:255',
                'gender'             => 'required|in:L,P',
                'nick_name'          => 'nullable|string|max:255',
                'pob'                => 'nullable|string|max:255',
                'dob'                => 'nullable|date',
                'religion'           => 'nullable|string',
                'nik'                => 'nullable|numeric|digits_between:15,17',
                'child_order'        => 'nullable|integer|min:1',
                'number_of_siblings' => 'nullable|integer|min:0',
            ]);

            $student->update([
                'name'               => $validated['name'],
                'nick_name'          => $validated['nick_name'],
                'gender'             => $validated['gender'],
                'child_order'        => $validated['child_order'],
                'number_of_siblings' => $validated['number_of_siblings'],
            ]);

            $vault = $student->vault;
            $vault->pob_encrypted = $validated['pob'];
            $vault->dob_encrypted = $validated['dob'];
            $vault->nik_encrypted = $validated['nik'];
            $vault->nik_hash      = $validated['nik'] ? $this->blindIndexHash($validated['nik']) : null; // kalau ada kolom nik_hash untuk pencarian

            $vault->religion_encrypted = $validated['religion'] ? Religion::tryFrom($validated['religion'])?->value : null;
            $vault->religion_hash = $validated['religion'] ? $this->blindIndexHash($validated['religion']) : null;

            $vault->save();

            // Lanjut ke step 2
            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 2], 303);
        }

        // --- STEP 2: ALAMAT & KONTAK ---
        if ($step === 2) {
            $validated = $request->validate([
                'phone_number'       => 'nullable|string',
                'email'              => 'nullable|email',
                'residence_type'     => 'nullable|string',
                'transportation'     => 'nullable|string',
                'distance_to_school' => 'nullable|string',
                'address'            => 'required|string',
                // Tambahkan rule rt, rw, desa, dll.
            ]);

            $student->update([
                'residence_type'     => $validated['residence_type'],
                'transportation'     => $validated['transportation'],
                'distance_to_school' => $validated['distance_to_school'],
            ]);

            // Update vault kontak...
            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 3], 303);
        }

        // --- STEP 3: ORANGTUA/WALI (BARU) ---
        if ($step === 3) {
            $validated = $request->validate([
                'guardian_name'          => 'required|string|max:255',
                'guardian_relationship'  => 'required|in:father,mother,guardian',
                'guardian_living_status' => 'required|in:alive,deceased',
                'guardian_birth_year'    => 'nullable|numeric|digits:4',
                'guardian_occupation'    => 'nullable|string|max:255',
                'guardian_education'     => 'nullable|string|max:255',
                'guardian_income_range'  => 'nullable|string|max:255',
                'guardian_nik'           => 'nullable|numeric',
                'guardian_phone_number'  => 'nullable|string',
                'guardian_address'       => 'nullable|string',
            ]);

            // Gunakan updateOrCreate untuk mengelola data Guardian pertama
            $guardian = $student->guardians()->updateOrCreate(
                ['relationship' => $validated['guardian_relationship']], // Asumsi 1 relasi utama
                [
                    'name'          => $validated['guardian_name'],
                    'living_status' => $validated['guardian_living_status'],
                    'birth_year'    => $validated['guardian_birth_year'],
                    'occupation'    => $validated['guardian_occupation'],
                    'education'     => $validated['guardian_education'],
                    'income_range'  => $validated['guardian_income_range'],
                ]
            );

            // Update Guardian Vault
            $guardianVault = $guardian->vault ?? $guardian->vault()->create();
            // Implementasikan enkripsi/hash sesuai pattern sistem Anda
            $guardianVault->nik_encrypted = $validated['guardian_nik'];
            $guardianVault->phone_number_encrypted = $validated['guardian_phone_number'];
            $guardianVault->address_encrypted = $validated['guardian_address'];
            $guardianVault->save();

            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 4], 303);
        }

        // --- STEP 4: AKADEMIK ---
        if ($step === 4) {
            $validated = $request->validate([
                'previous_school'               => 'nullable|string|max:255',
                'previous_school_npsn'          => 'nullable|numeric',
                'previous_school_city'          => 'nullable|string|max:255',
                'previous_school_province'      => 'nullable|string|max:255',
                'graduation_certificate_number' => 'nullable|string|max:255',
                'graduation_year'               => 'nullable|numeric|digits:4',
            ]);

            $student->update($validated);
            return redirect()->route('admin.students.edit.personal', ['id' => $id, 'step' => 5], 303);
        }

        // --- STEP 5: KESEHATAN & SELESAI ---
        if ($step === 5) {
            $validated = $request->validate([
                'height'                 => 'nullable|numeric|min:0',
                'weight'                 => 'nullable|numeric|min:0',
                'blood_type'             => 'nullable|string',
                'is_special_condition'   => 'required|in:yes,no',
                'special_condition_type' => 'nullable|string|required_if:is_special_condition,yes',
                // ... validasi riwayat dll
            ]);

            $student->update($validated);

            // Step terakhir sukses, kembalikan ke index (HTMX akan mengganti tabel utama)
            return redirect()
                ->route('admin.students.data.index', $request->query())
                ->with('success', 'Data siswa berhasil diperbarui.');
        }
    }

    public function destroy(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()
            ->route('admin.students.index', $request->query())
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Base query yang dipakai bersama oleh tabel (paginate) dan kartu statistik.
     */
    private function buildBaseQuery(array $filters, ?string $semesterId)
    {
        $query = Student::with(['vault', 'concentration', 'activeClassGroup' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId);
        }])
            // Syarat Mutlak: Harus terdaftar di rombel semester ini
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
