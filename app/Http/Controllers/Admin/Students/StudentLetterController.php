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
