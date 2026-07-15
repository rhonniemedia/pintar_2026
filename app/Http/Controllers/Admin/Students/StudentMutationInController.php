<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreSemester;
use App\Models\Guardian;
use App\Models\GuardianVault;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Models\StudentVault;
use App\Traits\HasBlindIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentMutationInController extends Controller
{
    use HasBlindIndex;

    /**
     * Opsi agama untuk form + filter. Disamakan persis dengan
     * StudentController::RELIGION_OPTIONS karena pencarian/filter
     * agama mencocokkan blind-index hash secara persis (bukan LIKE) -
     * kalau daftar ini berbeda antar tempat, hash yang dihasilkan
     * juga akan berbeda dan filter tidak akan menemukan hasil yang cocok.
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

    private const GUARDIAN_RELATIONSHIPS = [
        'father' => 'Ayah',
        'mother' => 'Ibu',
        'guardian' => 'Wali',
    ];

    /**
     * Daftar riwayat mutasi masuk (siswa pindahan) pada semester aktif.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $semesterAktif = CoreSemester::where('status', 'active')->first();

        $data = StudentMutation::with(['student.vault', 'classGroup.concentration'])
            ->where('status', 'transfer_in')
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
            'title' => 'Mutasi Peserta Didik - Pindahan Masuk',
            'data' => $data,
            'search' => $search,
            'semesterAktif' => $semesterAktif,
        ]);
    }

    /**
     * Form modal tambah data siswa pindahan (HTMX).
     */
    public function create()
    {
        return view('pages.admin.students.transfers.in.partials._modal-create', $this->modalData());
    }

    /**
     * Simpan siswa pindahan: profil siswa, data vault (sensitif),
     * data wali, riwayat mutasi, dan penempatan ke rombel - dalam
     * satu transaksi.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // --- Identitas Personal ---
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16',
            'gender' => 'required|in:L,P',
            'religion' => 'required|in:' . implode(',', self::RELIGION_OPTIONS),
            'pob' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'nisn' => 'required|string|max:10',
            'child_order' => 'nullable|integer|min:1',
            'number_of_siblings' => 'nullable|integer|min:0',

            // --- Orang Tua / Wali ---
            'guardian_name' => 'required|string|max:255',
            'guardian_relationship' => 'required|in:father,mother,guardian',
            'guardian_nik' => 'nullable|string|max:16',
            'guardian_birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'guardian_occupation' => 'nullable|string|max:255',
            'guardian_education' => 'nullable|string|max:255',
            'guardian_income_range' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_address' => 'nullable|string',

            // --- Sekolah Asal ---
            'previous_school' => 'required|string|max:255',
            'previous_school_city' => 'nullable|string|max:255',
            'previous_school_province' => 'nullable|string|max:255',
            'previous_school_npsn' => 'nullable|string|max:20',
            'graduation_certificate_number' => 'nullable|string|max:50',
            'graduation_year' => 'nullable|string|max:4',

            // --- Informasi Penerimaan ---
            'entry_date' => 'required|date',
            'class_group_id' => 'required|uuid|exists:acd_class_groups,id',
            'notes' => 'nullable|string',
        ]);

        // NISN wajib unik, tapi kolomnya terenkripsi sehingga tidak bisa
        // divalidasi via rule `unique:` biasa - dicek manual lewat hash.
        $validator->after(function ($v) use ($request) {
            if ($request->filled('nisn')) {
                $hash = $this->blindIndexHash($request->nisn);
                if (StudentVault::where('nisn_hash', $hash)->exists()) {
                    $v->errors()->add('nisn', 'NISN sudah terdaftar pada siswa lain.');
                }
            }
        });

        if ($validator->fails()) {
            $request->flash();

            return response()
                ->view('pages.admin.students.transfers.in.partials._modal-create', array_merge(
                    $this->modalData(),
                    ['errors' => $validator->errors()]
                ))
                ->setStatusCode(422);
        }

        DB::beginTransaction();

        try {
            $semesterAktif = CoreSemester::where('status', 'active')->firstOrFail();
            $classGroup = ClassGroup::with('concentration')->findOrFail($request->class_group_id);

            // === AUTO GENERATE NIS ===
            // Format: [kode_jurusan 2 digit][tahun 2 digit][urut 3 digit].
            // Urut dihitung GLOBAL per tahun (lintas jurusan), bukan per
            // jurusan - mengikuti logic yang sama dengan aplikasi lama.
            //
            // CATATAN: "tahun" diambil dari start_date semester aktif
            // (bukan dari nama tahun ajaran di core_academic_years, karena
            // field itu belum dikonfirmasi). Kalau core_academic_years
            // punya kolom nama/tahun yang lebih tepat, ganti baris ini.
            $tahun = Carbon::parse($semesterAktif->start_date)->format('y');
            $kodeJurusan = $classGroup->concentration->code;

            $lastNis = Student::where('nis', 'LIKE', '__' . $tahun . '___')
                ->whereRaw('LENGTH(nis) = 7')
                ->orderByRaw('CAST(RIGHT(nis, 3) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $nomorUrut = $lastNis ? ((int) substr($lastNis->nis, -3)) + 1 : 1;
            $nis = $kodeJurusan . $tahun . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
            // === END AUTO GENERATE NIS ===

            // 1. Profil siswa (non-sensitif)
            $student = Student::create([
                'name' => $request->name,
                'gender' => $request->gender,
                'nis' => $nis,
                'child_order' => $request->child_order,
                'number_of_siblings' => $request->number_of_siblings,
                'entry_date' => $request->entry_date,
                'registration_type' => 'transfer',
                'entry_grade_level' => $classGroup->grade_level,
                'concentration_id' => $classGroup->concentration_id,
                'status' => 'active',
                'previous_school' => $request->previous_school,
                'previous_school_npsn' => $request->previous_school_npsn,
                'previous_school_city' => $request->previous_school_city,
                'previous_school_province' => $request->previous_school_province,
                'graduation_certificate_number' => $request->graduation_certificate_number,
                'graduation_year' => $request->graduation_year,
            ]);

            // 2. Data sensitif (vault) - kontak/alamat siswa sengaja
            // dikosongkan, dilengkapi nanti lewat menu edit.
            StudentVault::create([
                'student_id' => $student->id,
                'nisn_encrypted' => $request->nisn,
                'nisn_hash' => $this->blindIndexHash($request->nisn),
                'nik_encrypted' => $request->nik,
                'nik_hash' => $request->filled('nik') ? $this->blindIndexHash($request->nik) : null,
                'pob_encrypted' => $request->pob,
                'dob_encrypted' => $request->dob,
                'dob_hash' => $request->filled('dob') ? $this->blindIndexHash($request->dob) : null,
                'religion_encrypted' => $request->religion,
                'religion_hash' => $this->blindIndexHash($request->religion),
            ]);

            // 3. Data wali
            $guardian = Guardian::create([
                'student_id' => $student->id,
                'name' => $request->guardian_name,
                'relationship' => $request->guardian_relationship,
                'birth_year' => $request->guardian_birth_year,
                'occupation' => $request->guardian_occupation,
                'education' => $request->guardian_education,
                'income_range' => $request->guardian_income_range,
            ]);

            if ($request->filled('guardian_nik') || $request->filled('guardian_phone') || $request->filled('guardian_address')) {
                GuardianVault::create([
                    'guardian_id' => $guardian->id,
                    'nik_encrypted' => $request->guardian_nik,
                    'nik_hash' => $request->filled('guardian_nik') ? $this->blindIndexHash($request->guardian_nik) : null,
                    'phone_number_encrypted' => $request->guardian_phone,
                    'phone_number_hash' => $request->filled('guardian_phone') ? $this->blindIndexHash($request->guardian_phone) : null,
                    'address_encrypted' => $request->guardian_address,
                ]);
            }

            // 4. Riwayat mutasi
            StudentMutation::create([
                'student_id' => $student->id,
                'semester_id' => $semesterAktif->id,
                'class_group_id' => $classGroup->id,
                'status' => 'transfer_in',
                'origin_destination' => $request->previous_school,
                'notes' => $request->notes,
                'mutation_date' => $request->entry_date,
            ]);

            // 5. Penempatan ke rombel aktif
            ClassGroupStudent::create([
                'student_id' => $student->id,
                'class_group_id' => $classGroup->id,
                'entry_date' => $request->entry_date,
                'status' => 'active',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan mutasi masuk: ' . $e->getMessage());

            return response(
                '<div class="p-4 text-sm text-error">Terjadi kesalahan saat menyimpan data: ' . e($e->getMessage()) . '</div>',
                500
            );
        }

        // Sukses: kosongkan body, modal ditutup & tabel disegarkan lewat
        // event yang memang sudah disiapkan di index.blade.php
        // (@closeModal.window) dan _table.blade.php (hx-trigger="refreshTable from:body").
        return response('', 200)
            ->header('HX-Trigger', json_encode(['closeModal' => true, 'refreshTable' => true]));
    }

    private function modalData(): array
    {
        $semesterAktif = CoreSemester::where('status', 'active')->first();

        $classGroups = ClassGroup::with('concentration')
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return [
            'classGroups' => $classGroups,
            'religionOptions' => self::RELIGION_OPTIONS,
            'guardianRelationships' => self::GUARDIAN_RELATIONSHIPS,
        ];
    }
}
