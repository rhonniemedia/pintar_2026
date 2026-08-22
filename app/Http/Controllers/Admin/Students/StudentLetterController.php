<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\LetterType;
use App\Http\Controllers\Controller;
use App\Models\CoreSchool;
use App\Models\Student;
use App\Models\StudentLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentLetterController extends Controller
{
    public function index(Request $request): View
    {
        $search     = $request->input('search');
        $letterType = $request->input('letter_type');

        $data = StudentLetter::with(['student.vault', 'classGroup.concentration', 'semester', 'author'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($letterType, fn($q) => $q->where('letter_type', $letterType))
            ->orderByDesc('letter_date')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->header('HX-Request') && ! $request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.letters.partials._table', compact('data', 'search', 'letterType'));
        }

        return view('pages.admin.students.letters.index', [
            'title'       => 'Persuratan Peserta Didik',
            'data'        => $data,
            'search'      => $search,
            'letterType'  => $letterType,
            'letterTypes' => LetterType::cases(),
        ]);
    }

    /**
     * Modal langkah 1: pilih jenis surat yang mau diterbitkan.
     * $createRoutes memetakan LetterType->value ke nama route form langkah-2-nya.
     * Jenis surat yang BELUM ada di sini otomatis tampil non-aktif ("segera hadir") di modal.
     */
    public function create(): View
    {
        return view('pages.admin.students.letters.partials._modal-create', [
            'letterTypes'  => LetterType::cases(),
            'createRoutes' => [
                LetterType::ACTIVE->value       => 'admin.students.letters.create-active',
                LetterType::GOOD_CONDUCT->value => 'admin.students.letters.create-good-conduct',
                LetterType::POOR_FAMILY->value  => 'admin.students.letters.create-poor-family',
            ],
        ]);
    }

    /** Modal langkah 2 khusus jenis "Keterangan Aktif". */
    public function createActive(): View
    {
        return $this->renderStudentForm('pages.admin.students.letters.partials._modal-create-active');
    }

    /** Modal langkah 2 khusus jenis "Keterangan Berkelakuan Baik". */
    public function createGoodConduct(): View
    {
        return $this->renderStudentForm('pages.admin.students.letters.partials._modal-create-good-conduct');
    }

    /** Modal langkah 2 khusus jenis "Keterangan Tidak Mampu". */
    public function createPoorFamily(): View
    {
        return $this->renderStudentForm('pages.admin.students.letters.partials._modal-create-poor-family');
    }

    public function storeActive(Request $request): Response
    {
        return $this->generateSimpleLetter(
            $request,
            LetterType::ACTIVE,
            'pages.admin.students.letters.pdf.keterangan-aktif',
            'Surat keterangan aktif berhasil diterbitkan.',
        );
    }

    public function storeGoodConduct(Request $request): Response
    {
        return $this->generateSimpleLetter(
            $request,
            LetterType::GOOD_CONDUCT,
            'pages.admin.students.letters.pdf.kelakuan-baik',
            'Surat keterangan berkelakuan baik berhasil diterbitkan.',
        );
    }

    public function storePoorFamily(Request $request): Response
    {
        return $this->generateSimpleLetter(
            $request,
            LetterType::POOR_FAMILY,
            'pages.admin.students.letters.pdf.keterangan-miskin',
            'Surat keterangan tidak mampu berhasil diterbitkan.',
        );
    }

    public function download(StudentLetter $letter)
    {
        $disk = $this->disk();

        abort_unless($disk->exists($letter->file_path), 404, 'File surat tidak ditemukan.');

        return $disk->response(
            $letter->file_path,
            $letter->letter_type->label() . ' - ' . ($letter->student->name ?? 'surat') . '.pdf'
        );
    }

    public function destroy(StudentLetter $letter): Response
    {
        // 0. TAMBAHAN: Cegah hapus untuk jenis surat mutasi keluar
        $type = $letter->letter_type instanceof \UnitEnum
            ? $letter->letter_type->value
            : $letter->letter_type;

        $protectedTypes = [
            LetterType::TRANSFER->value,
            LetterType::DISMISSED->value,
            LetterType::RESIGNED->value
        ];

        if (in_array($type, $protectedTypes)) {
            return $this->htmxTrigger([
                'showAlert' => [
                    'icon'  => 'error',
                    'title' => 'Gagal Dihapus!',
                    'text'  => 'Surat mutasi keluar terkait dengan catatan historis siswa tidak boleh dihapus melalui menu ini.',
                ],
            ]);
        }

        // 1. Hapus file fisik PDF terlebih dahulu agar tidak menjadi sampah (orphan file).
        if ($letter->file_path && $this->disk()->exists($letter->file_path)) {
            $this->disk()->delete($letter->file_path);
        }

        // 2. Hapus permanen dari database (aman untuk model dengan/without SoftDeletes).
        $letter->forceDelete();

        // 3. Kembalikan response trigger HTMX.
        return $this->htmxTrigger([
            'refreshTable' => true,
            'showAlert'    => [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => 'Data riwayat surat beserta file PDF berhasil dihapus.',
            ],
        ]);
    }

    /**
     * Modal cetak Surat Pernyataan Peserta Didik Baru.
     * Dipicu dari dropdown "Aksi" > "Cetak Dokumen" > "Surat Pernyataan" di
     * tabel data siswa. Beda dengan surat-surat di atas: dokumen ini TIDAK
     * dicatat sebagai StudentLetter (tidak ada histori/download ulang),
     * langsung dibuka di tab baru sesuai form modalnya (target="_blank").
     */
    public function printStatementModal(string $id): View
    {
        $student = Student::with('guardians')->findOrFail($id);

        return view('pages.admin.students.groups.partials._modal-print-statement', compact('student'));
    }

    /** Render & tampilkan (stream) PDF Surat Pernyataan Peserta Didik Baru. */
    public function printStatementPdf(Request $request, string $id): Response
    {
        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);

        $validated = $request->validate([
            'print_date'  => 'required|date',
            // nullable: kalau siswa belum punya data wali sama sekali, biarkan tetap
            // bisa cetak - template sudah menangani $waliPembuat null -> tampil "-".
            'guardian_id' => 'nullable|exists:acd_guardians,id',
        ]);

        $vault = $student->vault;

        $school = CoreSchool::first();

        if (! $school) {
            return $this->htmxTrigger([
                'showAlert' => [
                    'icon'  => 'error',
                    'title' => 'Gagal!',
                    'text'  => 'Data sekolah belum diisi. Lengkapi dulu di Data Master > Data Sekolah.',
                ],
            ]);
        }

        // 1. Racik data pribadi siswa sesuai field yang dipakai statement-letter.blade.php
        $personalData = (object) [
            'full_name'       => $student->name,
            'gender'          => $student->gender?->value, // enum Gender -> 'L' / 'P'
            'religion'        => $vault?->religion?->label(),
            'phone_number'    => $vault?->phone_number_encrypted,
            'previous_school' => $student->previous_school,
        ];

        $ttl = trim(
            ($vault?->pob_encrypted ?? '-') . ', ' .
                ($vault?->dob_encrypted ? Carbon::parse($vault->dob_encrypted)->translatedFormat('d F Y') : '-')
        );

        // 2. Susun alamat lengkap dari pecahan kolom di StudentVault
        $alamatLengkapSiswa = collect([
            $vault?->address_encrypted,
            $vault?->rt_encrypted ? "RT {$vault->rt_encrypted}" : null,
            $vault?->rw_encrypted ? "RW {$vault->rw_encrypted}" : null,
            $vault?->village_encrypted,
            $vault?->district_encrypted,
            $vault?->regency_encrypted,
            $vault?->province_encrypted,
            $vault?->postal_code_encrypted,
        ])->filter()->implode(', ') ?: '-';

        // 3. Wali yang tanda tangan = pilihan eksplisit user dari dropdown modal.
        // ->guardians->find() otomatis terscope ke siswa ini (relasi sudah di-eager-load).
        $guardian = $validated['guardian_id']
            ? $student->guardians->find($validated['guardian_id'])
            : null;

        $waliPembuat = $guardian ? (object) [
            'name'               => $guardian->name,
            'occupation'         => $guardian->occupation?->label(),
            'address'            => $guardian->vault?->address_encrypted,
            'phone_number'       => $guardian->vault?->phone_number_encrypted,
            'relationship_label' => $guardian->relationship?->label(),
        ] : null;

        $printDate = Carbon::parse($validated['print_date']);

        $pdf = Pdf::loadView('pages.admin.students.groups.pdf.statement-letter', [
            'personalData'       => $personalData,
            'ttl'                => $ttl,
            'alamatLengkapSiswa' => $alamatLengkapSiswa,
            'waliPembuat'        => $waliPembuat,
            'namaSekolah'        => $school->name,
            'tanggalCetak'       => $printDate->translatedFormat('d F Y'),
        ])->setPaper('a4', 'portrait');

        $fileName = 'surat-pernyataan-' . Str::slug($student->name) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Modal cetak Biodata Peserta Didik.
     */
    public function printBiodataModal(string $id): View
    {
        $student = Student::with('guardians')->findOrFail($id);

        return view('pages.admin.students.groups.partials._modal-print-biodata', compact('student'));
    }

    /** Render & tampilkan (stream) PDF Biodata Peserta Didik. */
    public function printBiodataPdf(Request $request, string $id): Response
    {
        $validated = $request->validate([
            'print_date'  => 'required|date',
            'guardian_id' => 'nullable|exists:acd_guardians,id',
        ]);

        $student = Student::with(['vault', 'guardians.vault', 'activeClassGroup.semester.academicYear'])
            ->findOrFail($id);

        $vault = $student->vault;

        // 1. Racik $personalData sesuai field yang dipakai student-personal-data.blade.php.
        // NB: kolom "email", "photo" diambil dari Student/StudentVault - sesuaikan kalau
        // ternyata namanya berbeda (mis. email disimpan di tabel lain).
        $personalData = (object) [
            'full_name'                     => $student->name,
            'nick_name'                     => $student->nick_name,
            'nik'                           => $vault?->nik_encrypted,
            'nisn'                          => $vault?->nisn_encrypted,
            'gender'                        => $student->gender?->value, // 'L' / 'P'
            'religion'                      => $vault?->religion?->label(),
            'child_order'                   => $student->child_order,
            'number_of_siblings'            => $student->number_of_siblings,
            'phone_number'                  => $vault?->phone_number_encrypted,
            'email'                         => $vault?->email_encrypted,
            'address'                       => $vault?->address_encrypted,
            'rt'                            => $vault?->rt_encrypted,
            'rw'                            => $vault?->rw_encrypted,
            'village'                       => $vault?->village_encrypted,
            'district'                      => $vault?->district_encrypted,
            'regency'                       => $vault?->regency_encrypted,
            'province'                      => $vault?->province_encrypted,
            'postal_code'                   => $vault?->postal_code_encrypted,
            'residence_type'                => $student->residence_type?->value,
            'transportation'                => $student->transportation?->value,
            'distance_to_school'             => $student->distance_to_school?->value,
            'previous_school'               => $student->previous_school,
            'previous_school_status'        => $student->previous_school_status,
            'previous_school_npsn'          => $student->previous_school_npsn,
            'previous_school_city'          => $student->previous_school_city,
            'previous_school_province'      => $student->previous_school_province,
            'graduation_year'               => $student->graduation_year,
            'graduation_certificate_number' => $student->graduation_certificate_number,
            'height'                        => $student->height,
            'weight'                        => $student->weight,
            'blood_type'                    => $student->blood_type,
            'medical_history'               => $student->medical_history,
            'is_special_condition'          => $student->is_special_condition,
            'special_condition_type'        => $student->special_condition_type?->value,
            'extracurricular_choice'        => $student->extracurricular_choice,
            'interest_organization'         => $student->interest_organization,
            'fl2sn_category'                => $student->fl2sn_category,
            'o2sn_category'                 => $student->o2sn_category,
            'photo'                         => $student->photo,
        ];

        $ttl = trim(
            ($vault?->pob_encrypted ?? '-') . ', ' .
                ($vault?->dob_encrypted ? Carbon::parse($vault->dob_encrypted)->translatedFormat('d F Y') : '-')
        );

        // 2. Tahun ajaran diambil dari rombel aktif siswa (kalau ada).
        $academicYear = $student->activeClassGroup->first()?->semester?->academicYear;
        $tahunAjaran  = $academicYear->name ?? '-'; // TODO: sesuaikan nama kolom/attribute academicYear kalau bukan "name"

        // 3. Data Ayah / Ibu / Wali - dipisah per jenis relationship.
        $father   = $this->mapGuardianForPdf($student->guardians->firstWhere('relationship', \App\Enums\Student\FamilyRelation::AYAH));
        $mother   = $this->mapGuardianForPdf($student->guardians->firstWhere('relationship', \App\Enums\Student\FamilyRelation::IBU));
        $guardian = $this->mapGuardianForPdf($student->guardians->firstWhere('relationship', \App\Enums\Student\FamilyRelation::WALI));

        // 4. Nilai rapor & TKA (data pendaftaran PPDB) - BELUM ada sumber datanya di
        // model yang saya lihat, jadi sementara dikosongkan. Template sudah menangani
        // $registration null dengan pesan "Data pendaftaran belum diisi". Kalau ada
        // model pendaftaran (mis. dari modul SPMB), sambungkan di sini.
        $registration = null;

        // 5. Penanda tangan: pakai pilihan eksplisit dari dropdown modal kalau ada.
        // Kalau user tidak memilih (mis. akses langsung tanpa lewat modal), fallback
        // ke urutan lama: Ayah > Ibu > Wali yang masih hidup.
        $selectedGuardian = $validated['guardian_id']
            ? $student->guardians->find($validated['guardian_id'])
            : null;

        $penandaTangan = $selectedGuardian
            ? $this->mapGuardianForPdf($selectedGuardian)
            : collect([$father, $mother, $guardian])->first(fn($p) => $p && $p->isAlive());

        $printDate = Carbon::parse($validated['print_date']);

        $pdf = Pdf::loadView('pages.admin.students.groups.pdf.student-personal-data', [
            'personalData'  => $personalData,
            'ttl'           => $ttl,
            'tahunAjaran'   => $tahunAjaran,
            'father'        => $father,
            'mother'        => $mother,
            'guardian'      => $guardian,
            'registration'  => $registration,
            'penandaTangan' => $penandaTangan,
            'tanggalCetak'  => $printDate->translatedFormat('d F Y'),
        ]);

        $fileName = 'biodata-' . Str::slug($student->name) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Petakan model Guardian ke object presentasi yang dipakai
     * student-personal-data.blade.php (name, nik, birth_year, education,
     * occupation, income_range, phone_number, address, isAlive()).
     *
     * TODO: "birth_year" belum ketemu kolomnya di Guardian - sesuaikan kalau
     * ternyata namanya berbeda atau tidak ada.
     * TODO: logic isAlive() menebak nilai enum LivingStatus ("deceased" =
     * meninggal) - sesuaikan persis dengan case yang ada di LivingStatus.php.
     */
    private function mapGuardianForPdf(?\App\Models\Guardian $guardian): ?object
    {
        if (! $guardian) {
            return null;
        }

        $isAlive = ! $guardian->living_status
            || strtolower((string) $guardian->living_status->value) !== 'deceased';

        // Pakai anonymous class (bukan stdClass) karena template PDF memanggil
        // ->isAlive() sebagai METHOD, bukan properti.
        return new class($guardian, $isAlive) {
            public string $name;
            public ?string $nik;
            public $birth_year;
            public ?string $education;
            public ?string $occupation;
            public ?string $income_range;
            public ?string $phone_number;
            public ?string $address;
            private bool $alive;

            public function __construct(\App\Models\Guardian $guardian, bool $alive)
            {
                $this->name         = $guardian->name;
                $this->nik          = $guardian->vault?->nik_encrypted;
                $this->birth_year   = $guardian->birth_year ?? null;
                $this->education    = $guardian->education?->label();
                $this->occupation   = $guardian->occupation?->label();
                $this->income_range = $guardian->income_range?->label();
                $this->phone_number = $guardian->vault?->phone_number_encrypted;
                $this->address      = $guardian->vault?->address_encrypted;
                $this->alive        = $alive;
            }

            public function isAlive(): bool
            {
                return $this->alive;
            }
        };
    }

    /**
     * Logic bersama untuk jenis surat yang cuma butuh 3 input (siswa, nomor surat,
     * tanggal surat) dan strukturnya seragam: Keterangan Aktif, Kelakuan Baik,
     * Tidak Mampu. Jenis surat dengan field tambahan (mis. Pindah Sekolah butuh
     * data wali & sekolah tujuan) TIDAK pakai helper ini, dibuat method store terpisah.
     */
    protected function generateSimpleLetter(Request $request, LetterType $type, string $pdfView, string $successMessage): Response
    {
        $validated = $request->validate([
            'student_id'    => 'required|exists:acd_students,id',
            'letter_number' => 'required|string|max:255',
            'letter_date'   => 'required|date',
        ]);

        $school = CoreSchool::with(['headmaster.vault', 'headmaster.currentGrade.grade'])->first();

        if (! $school) {
            return $this->htmxTrigger([
                'showAlert' => [
                    'icon'  => 'error',
                    'title' => 'Gagal!',
                    'text'  => 'Data sekolah belum diisi. Lengkapi dulu di Data Master > Data Sekolah.',
                ],
            ]);
        }

        $student = Student::with(['vault', 'activeClassGroup.concentration', 'activeClassGroup.semester.academicYear'])
            ->findOrFail($validated['student_id']);

        $classGroup = $student->activeClassGroup->first();
        $letterDate = Carbon::parse($validated['letter_date']);

        $path = $this->storePdf($pdfView, $school, $student, $classGroup, $validated['letter_number'], $letterDate, $type);

        StudentLetter::create([
            'student_id'     => $student->id,
            'class_group_id' => $classGroup?->id,
            'semester_id'    => $classGroup?->semester_id,
            'letter_type'    => $type->value,
            'letter_number'  => $validated['letter_number'],
            'letter_date'    => $letterDate,
            'file_path'      => $path,
            'created_by'     => Auth::id(),
        ]);

        return $this->htmxTrigger([
            'close-modal'  => true,
            'refreshTable' => true,
            'showAlert'    => [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => $successMessage,
            ],
        ]);
    }

    /** Render form langkah 2: daftar siswa sama untuk semua jenis surat. */
    private function renderStudentForm(string $view): View
    {
        return view($view, [
            'students' => Student::with('activeClassGroup')->orderBy('name')->get(),
        ]);
    }

    /** Render PDF surat, simpan ke disk private, kembalikan path-nya. */
    private function storePdf(
        string $pdfView,
        CoreSchool $school,
        Student $student,
        ?object $classGroup, // ganti ke tipe model ClassGroup Anda jika ada, mis. ?ClassGroup
        string $letterNumber,
        Carbon $letterDate,
        LetterType $type,
    ): string {

        // Ukuran kertas F4 (215mm x 330mm) dalam satuan points
        $f4PaperSize = [0, 0, 609.448, 935.433];

        $pdf = Pdf::loadView($pdfView, [
            'school'       => $school,
            'student'      => $student,
            'classGroup'   => $classGroup,
            'letterNumber' => $letterNumber,
            'letterDate'   => $letterDate->translatedFormat('d F Y'),
        ])->setPaper($f4PaperSize, 'portrait');

        $fileName = sprintf('%s-%s-%s.pdf', $type->value, now()->format('YmdHis'), Str::random(8));
        $path     = "surat/{$fileName}";

        $this->disk()->put($path, $pdf->output());

        return $path;
    }

    /**
     * Disk 'local' = private (storage/app/private), TIDAK bisa diakses lewat URL publik.
     * Return type konkret FilesystemAdapter membuat Intelephense mengenali
     * method put/exists/response/delete — sekaligus single point of access disk.
     */
    private function disk(): FilesystemAdapter
    {
        return Storage::disk('local');
    }

    /** Response 204 + header HX-Trigger untuk handler HTMX di frontend. */
    private function htmxTrigger(array $payload): Response
    {
        return response()->noContent()->header('HX-Trigger', json_encode($payload));
    }
}
