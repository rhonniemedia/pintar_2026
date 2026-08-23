<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\MutationStatus;
use App\Enums\Student\Religion;
use App\Enums\Student\StudentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Students\StoreMutationInRequest;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Services\AcademicPeriod;
use App\Traits\HasBlindIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentMutationInController extends Controller
{
    use HasBlindIndex;

    public function __construct(
        private readonly AcademicPeriod $academicPeriod,
    ) {}

    /**
     * Daftar riwayat mutasi masuk (siswa pindahan) pada semester yang
     * sedang dilihat admin.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        // Nama variabel $semesterAktif dipertahankan (dipakai di view), tapi
        // isinya sekarang semester yang SEDANG DILIHAT admin (topbar), bukan
        // selalu semester aktif Data Master.
        $semesterAktif = $this->academicPeriod->current();

        $data = StudentMutation::with(['student.vault', 'classGroup.concentration'])
            ->where('status', MutationStatus::TRANSFER_IN->value)
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('mutation_date')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && ! $request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.transfers.in.partials._table', compact('data', 'search'));
        }

        return view('pages.admin.students.transfers.in.index', [
            'title'         => 'Mutasi Peserta Didik - Pindahan Masuk',
            'data'          => $data,
            'search'        => $search,
            'semesterAktif' => $semesterAktif,
        ]);
    }

    /**
     * Menampilkan Form Create All-in-One
     */
    public function create()
    {
        return view('pages.admin.students.transfers.in.partials._modal-create', [
            'classGroups' => $this->activeClassGroups(),
            'currentStep' => 1,
        ]);
    }

    /**
     * Memvalidasi step saat ini lalu melompat ke step berikutnya
     * tanpa menyimpan ke database.
     */
    public function validateStep(StoreMutationInRequest $request)
    {
        $request->flash();
        $nextStep = (int) $request->input('current_step', 1) + 1;

        return view('pages.admin.students.transfers.in.partials._modal-create', [
            'classGroups' => $this->activeClassGroups(),
            'currentStep' => $nextStep,
        ]);
    }

    /**
     * Memproses dan menyimpan seluruh data dari Step 1-5 sekaligus.
     */
    public function store(StoreMutationInRequest $request)
    {
        $validated = $request->validated();
        $filterNulls = fn($array) => array_filter($array, fn($val) => !is_null($val));

        try {
            DB::transaction(function () use ($validated, $filterNulls) {
                // SENGAJA tetap pakai semester aktif Data Master (bukan
                // academicPeriod): NIS yang di-generate harus mengikuti
                // semester intake berjalan sesungguhnya, dan class group
                // yang dipilih di form (lihat activeClassGroups() di bawah)
                // juga diambil dari semester aktif ini - keduanya harus
                // konsisten satu sama lain.
                $semesterAktif = CoreSemester::where('status', 'active')->firstOrFail();
                $classGroup = ClassGroup::with('concentration')->findOrFail($validated['class_group_id']);

                $nis = $this->generateNis($classGroup, $semesterAktif);

                // 1. Simpan Data Utama Siswa
                $studentData = $filterNulls([
                    'name'               => $validated['name'],
                    'gender'             => $validated['gender'],
                    'nis'                => $nis,
                    'child_order'        => $validated['child_order'] ?? null,
                    'number_of_siblings' => $validated['number_of_siblings'] ?? null,
                    'entry_date'         => $validated['entry_date'],
                    'registration_type'  => 'transfer',
                    'entry_grade_level'  => $classGroup->grade_level,
                    'concentration_id'   => $classGroup->concentration_id,
                    'status'             => StudentStatus::ACTIVE->value,
                    'status_date'        => $validated['entry_date'],

                    // Alamat & Kontak
                    'residence_type'     => $validated['residence_type'] ?? null,
                    'transportation'     => $validated['transportation'] ?? null,
                    'distance_to_school' => $validated['distance_to_school'] ?? null,

                    // Sekolah Kelulusan Sebelumnya
                    'previous_school'               => $validated['previous_school'] ?? null,
                    'previous_school_npsn'          => $validated['previous_school_npsn'] ?? null,
                    'previous_school_status'        => $validated['previous_school_status'] ?? null,
                    'previous_school_city'          => $validated['previous_school_city'] ?? null,
                    'previous_school_province'      => $validated['previous_school_province'] ?? null,
                    'graduation_certificate_number' => $validated['graduation_certificate_number'] ?? null,
                    'graduation_year'               => $validated['graduation_year'] ?? null,
                ]);
                $student = Student::create($studentData);

                // 2. Simpan Brankas Identitas Siswa (Vault)
                $vaultData = [
                    'nisn_encrypted'         => $validated['nisn'],
                    'nisn_hash'              => $this->blindIndexHash($validated['nisn']),
                    'nik_encrypted'          => $validated['nik'] ?? null,
                    'nik_hash'               => !empty($validated['nik']) ? $this->blindIndexHash($validated['nik']) : null,
                    'pob_encrypted'          => $validated['pob'] ?? null,
                    'dob_encrypted'          => $validated['dob'] ?? null,
                    'dob_hash'               => !empty($validated['dob']) ? $this->blindIndexHash($validated['dob']) : null,
                    'religion_encrypted'     => Religion::tryFrom($validated['religion'])?->value,
                    'religion_hash'          => $this->blindIndexHash($validated['religion']),

                    // Alamat & Kontak Sensitif
                    'phone_number_encrypted' => $validated['phone_number'] ?? null,
                    'email_encrypted'        => $validated['email'] ?? null,
                    'address_encrypted'      => $validated['address'] ?? null,
                    'rt_encrypted'           => $validated['rt'] ?? null,
                    'rw_encrypted'           => $validated['rw'] ?? null,
                    'village_encrypted'      => $validated['village'] ?? null,
                    'district_encrypted'     => $validated['district'] ?? null,
                    'regency_encrypted'      => $validated['regency'] ?? null,
                    'province_encrypted'     => $validated['province'] ?? null,
                    'postal_code_encrypted'  => $validated['postal_code'] ?? null,
                ];
                $student->vault()->create($filterNulls($vaultData));

                // 3. Simpan Penempatan Rombel
                ClassGroupStudent::create([
                    'student_id'     => $student->id,
                    'class_group_id' => $classGroup->id,
                    'entry_date'     => $validated['entry_date'],
                ]);

                // 4. Simpan Riwayat Mutasi (Sekolah Asal Pindahan)
                StudentMutation::create($filterNulls([
                    'student_id'             => $student->id,
                    'semester_id'            => $semesterAktif->id,
                    'class_group_id'         => $classGroup->id,
                    'status'                 => MutationStatus::TRANSFER_IN->value,
                    'mutation_date'          => $validated['entry_date'],
                    'origin_school'          => $validated['origin_school'] ?? null,
                    'origin_school_npsn'     => $validated['origin_school_npsn'] ?? null,
                    'origin_school_city'     => $validated['origin_school_city'] ?? null,
                    'origin_school_province' => $validated['origin_school_province'] ?? null,
                    'notes'                  => $validated['notes'] ?? null,
                ]));

                // 5. Simpan Data Orang Tua / Wali
                if (!empty($validated['guardian_name']) && !empty($validated['guardian_relationship'])) {
                    $relationKey = strtolower(trim($validated['guardian_relationship']));

                    $guardian = $student->guardians()->create($filterNulls([
                        'relationship' => $relationKey,
                        'name'         => $validated['guardian_name'],
                        'birth_year'   => $validated['guardian_birth_year'] ?? null,
                        'occupation'   => $validated['guardian_occupation'] ?? null,
                        'education'    => $validated['guardian_education'] ?? null,
                        'income_range' => $validated['guardian_income_range'] ?? null,
                    ]));

                    $guardianVaultData = $filterNulls([
                        'nik_encrypted'          => $validated['guardian_nik'] ?? null,
                        'nik_hash'               => !empty($validated['guardian_nik']) ? $this->blindIndexHash($validated['guardian_nik']) : null,
                        'phone_number_encrypted' => $validated['guardian_phone'] ?? null,
                        'address_encrypted'      => $validated['guardian_address'] ?? null,
                    ]);

                    if (!empty($guardianVaultData)) {
                        $guardian->vault()->create($guardianVaultData);
                    }
                }
            });

            return response()->noContent()->header('HX-Trigger', json_encode([
                'close-modal' => true,
                'refreshTable' => true,
                'showAlert' => [
                    'icon' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data siswa mutasi berhasil ditambahkan.'
                ]
            ]));
        } catch (\Exception $e) {
            Log::error('Gagal membuat data mutasi masuk: ' . $e->getMessage());

            return response(
                '<div class="p-4 text-sm text-error font-bold">Terjadi kesalahan sistem: ' . e($e->getMessage()) . '</div>',
                500
            );
        }
    }

    private function generateNis(ClassGroup $classGroup, CoreSemester $semesterAktif): string
    {
        $tahun = Carbon::parse($semesterAktif->start_date)->format('y');
        $kodeJurusan = $classGroup->concentration->code;

        $lastNis = Student::where('nis', 'LIKE', '__' . $tahun . '___')
            ->whereRaw('LENGTH(nis) = 7')
            ->orderByRaw('CAST(RIGHT(nis, 3) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->first();

        $nomorUrut = $lastNis ? ((int) substr($lastNis->nis, -3)) + 1 : 1;

        return $kodeJurusan . $tahun . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }

    private function activeClassGroups()
    {
        // SENGAJA tetap pakai semester aktif Data Master, harus konsisten
        // dengan semester yang dipakai saat store() menyimpan data (lihat
        // catatan di store()) - supaya rombel yang dipilih di form selalu
        // cocok dengan semester_id yang benar-benar disimpan.
        $semesterAktif = CoreSemester::where('status', 'active')->first();

        return ClassGroup::with('concentration')
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }
}
