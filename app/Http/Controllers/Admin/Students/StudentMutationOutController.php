<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\LetterType;
use App\Enums\Student\MutationStatus;
use App\Enums\Student\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\CoreSchool;
use App\Models\Student;
use App\Models\StudentLetter;
use App\Models\StudentMutation;
use App\Services\AcademicPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentMutationOutController extends Controller
{
    public function __construct(
        private readonly AcademicPeriod $academicPeriod,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        // Nama variabel $semesterAktif dipertahankan (dipakai di view), tapi
        // isinya sekarang semester yang SEDANG DILIHAT admin (topbar), bukan
        // selalu semester aktif Data Master.
        $semesterAktif = $this->academicPeriod->current();

        // TAMBAHAN: Load relasi 'student.studentLetters'
        $data = StudentMutation::with([
            'student.vault',
            'classGroup.concentration',
            'student.studentLetters' // Tambahkan ini agar data surat ikut termuat
        ])
            ->whereNotIn('status', [
                MutationStatus::GRADUATED->value,
                MutationStatus::TRANSFER_IN->value
            ])
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($mutation) {
                $mutation->is_transfer_out = $mutation->status === MutationStatus::TRANSFER_OUT;

                if (! $mutation->is_transfer_out) {
                    $mutation->mutation_reason_label = $mutation->status->label();
                }

                // PERBAIKAN: Gunakan filter() agar aman dari Enum Casting di Model
                $mutation->latest_letter = $mutation->student?->studentLetters
                    ->filter(function ($letter) {
                        // Cek apakah letter_type berupa objek Enum atau String biasa
                        $type = $letter->letter_type instanceof \UnitEnum
                            ? $letter->letter_type->value
                            : $letter->letter_type;

                        return in_array($type, [
                            LetterType::TRANSFER->value,
                            LetterType::DISMISSED->value,
                            LetterType::RESIGNED->value
                        ]);
                    })
                    ->sortByDesc('created_at')
                    ->first();

                return $mutation;
            });

        if ($request->header('HX-Request') && ! $request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.transfers.out.partials._table', compact('data', 'search'));
        }

        return view('pages.admin.students.transfers.out.index', [
            'title'         => 'Mutasi Peserta Didik - Pindahan Keluar',
            'data'          => $data,
            'search'        => $search,
            'semesterAktif' => $semesterAktif,
        ]);
    }

    public function create()
    {
        // Pakai semester yang SEDANG DILIHAT admin di topbar: nilai ini
        // harus konsisten dengan semester yang benar-benar dipakai saat
        // store() menyimpan record mutasi (lihat catatan di store()),
        // supaya modal tidak menampilkan konteks semester yang berbeda
        // dari yang nanti disimpan.
        $semesterAktif = $this->academicPeriod->current();

        $students = Student::with(['activeClassGroup' => function ($q) use ($semesterAktif) {
            $q->when($semesterAktif, fn($q2) => $q2->where('semester_id', $semesterAktif->id));
        }])
            ->where('status', StudentStatus::ACTIVE->value)
            // Hanya siswa yang rombel aktifnya ada di semester yang sedang
            // dibuka admin di topbar - kalau tidak difilter, siswa dengan
            // rombel aktif di semester lain akan tetap muncul di daftar
            // padahal tidak relevan dengan konteks semester yang sedang
            // dilihat (dan tidak akan lolos ulang saat store()).
            ->when($semesterAktif, function ($q) use ($semesterAktif) {
                $q->whereHas('activeClassGroup', function ($q2) use ($semesterAktif) {
                    $q2->where('semester_id', $semesterAktif->id);
                });
            })
            ->orderBy('name')
            ->get();

        return view('pages.admin.students.transfers.out.partials._create-modal', compact('students', 'semesterAktif'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input berdasarkan Form Modal yang baru
        $validated = $request->validate([
            'student_id'                   => 'required|exists:acd_students,id',
            'status'                       => 'required|in:transfer_out,dismissed,resigned,dropped_out,deceased,married',
            'mutation_date'                => 'required|date',
            // Pindah Sekolah
            'reference_number_pindah'      => 'nullable|required_if:status,transfer_out|string',
            'destination_school'           => 'nullable|required_if:status,transfer_out|string',
            'notes_pindah'                 => 'nullable|string',
            // Dikeluarkan
            'reference_number_dikeluarkan' => 'nullable|required_if:status,dismissed|string',
            'notes_dikeluarkan'            => 'nullable|required_if:status,dismissed|string',
            // Mengundurkan Diri
            'reference_number_mundur'      => 'nullable|required_if:status,resigned|string',
            'notes_mundur'                 => 'nullable|required_if:status,resigned|string',
        ]);

        $student = Student::with('vault')->findOrFail($validated['student_id']);
        // Pakai semester yang SEDANG DILIHAT admin di topbar, sama seperti
        // yang dipakai create() untuk menampilkan modal - supaya record
        // mutasi keluar tidak "nyelip" ke semester aktif Data Master saat
        // admin sedang membuka semester lain di topbar.
        $semesterAktif = $this->academicPeriod->current();

        // Ambil rombel aktif siswa YANG BERADA DI SEMESTER TOPBAR saja
        // (bukan sekadar rombel aktif siswa apa pun). Ini query ulang
        // (bukan pakai relasi hasil eager-load create()) supaya tetap
        // konsisten walau request store() dikirim terpisah/manual, dan
        // supaya siswa yang rombel aktifnya ada di semester lain tidak
        // ikut lolos meski student_id-nya valid.
        $activeGroup = $student->activeClassGroup()
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->first();

        if (! $activeGroup) {
            return response(
                '<div class="p-4 text-sm text-error font-bold">Peserta didik ini tidak memiliki penempatan rombel pada semester yang sedang dibuka di topbar. Pastikan semester yang dipilih sudah sesuai.</div>',
                422
            );
        }

        // 2. Pemetaan Status menggunakan Enum tersentralisasi & Ekstraksi Data Form
        $statusEnum          = MutationStatus::from($validated['status']);
        $finalMutationStatus = $statusEnum->value;
        $finalStudentStatus  = $statusEnum->resultingStudentStatus()->value;

        $destinationSchool = match ($statusEnum) {
            MutationStatus::TRANSFER_OUT => $validated['destination_school'] ?? null,
            default => null,
        };

        // PENTING: reference_number TIDAK disimpan ke acd_student_mutations (kolom itu
        // tidak ada di tabelnya). Untuk TRANSFER_OUT, nilainya dipakai langsung sebagai
        // nomor Surat Keterangan Pindah di StudentLetter (lihat generateTransferLetter()).
        // Untuk DISMISSED/RESIGNED disiapkan untuk jenis surat lain yang belum dibuat.
        $referenceNumber = match ($statusEnum) {
            MutationStatus::TRANSFER_OUT => $validated['reference_number_pindah'] ?? null,
            MutationStatus::DISMISSED    => $validated['reference_number_dikeluarkan'] ?? null,
            MutationStatus::RESIGNED     => $validated['reference_number_mundur'] ?? null,
            default => null,
        };

        $notes = match ($statusEnum) {
            MutationStatus::TRANSFER_OUT => $validated['notes_pindah'] ?? null,
            MutationStatus::DISMISSED    => $validated['notes_dikeluarkan'] ?? null,
            MutationStatus::RESIGNED     => $validated['notes_mundur'] ?? null,
            default => null,
        };

        // 3. Simpan dalam Satu Transaksi (return $mutation supaya bisa dipakai generate surat sesudahnya)
        $mutation = DB::transaction(function () use (
            $student,
            $finalStudentStatus,
            $finalMutationStatus,
            $semesterAktif,
            $validated,
            $destinationSchool,
            $notes,
            $activeGroup
        ) {
            // 3a. Buat rekaman mutasi terlebih dahulu
            $mutation = StudentMutation::create([
                'student_id'         => $student->id,
                'class_group_id'     => $activeGroup?->id,
                'semester_id'        => $semesterAktif?->id,
                'mutation_date'      => $validated['mutation_date'],
                'status'             => $finalMutationStatus,
                'destination_school' => $destinationSchool,
                'notes'              => $notes,
            ]);

            // 3b. Update status utama siswa
            $student->update(['status' => $finalStudentStatus]);

            // 3c. Update pivot table rombel
            if ($activeGroup) {
                $student->classGroups()->updateExistingPivot($activeGroup->id, [
                    'exit_date'   => $validated['mutation_date'],
                    'mutation_id' => $mutation->id,
                ]);
            }

            return $mutation;
        });

        // 4. Kalau statusnya "Pindah Sekolah", "Dikeluarkan", atau "Mengundurkan Diri", terbitkan Surat otomatis.
        $letterWarning = null;

        if (in_array($statusEnum, [MutationStatus::TRANSFER_OUT, MutationStatus::DISMISSED, MutationStatus::RESIGNED]) && $referenceNumber) {
            // Panggil fungsi yang sudah diubah namanya menjadi generateMutationLetter
            $success = $this->generateMutationLetter($student, $activeGroup, $mutation, $referenceNumber, $validated['mutation_date'], $statusEnum);

            if (! $success) {
                $letterWarning = ' Namun surat keterangan GAGAL diterbitkan karena data sekolah belum lengkap (lengkapi di Data Master > Data Sekolah, lalu terbitkan manual lewat menu Persuratan).';
            }
        }

        return response()->noContent()->header('HX-Trigger', json_encode([
            'close-modal'  => true,
            'refreshTable' => true,
            'showAlert'    => [
                'icon'  => $letterWarning ? 'warning' : 'success',
                'title' => $letterWarning ? 'Berhasil, dengan catatan' : 'Berhasil!',
                'text'  => 'Peserta didik berhasil dimutasi dan dikeluarkan dari rombongan belajar.' . ($letterWarning ?? ''),
            ]
        ]));
    }

    /**
     * Generate Surat Keterangan untuk mutasi (Transfer Out, Dismissed, Resigned), 
     * simpan PDF-nya ke disk private, dan catat riwayatnya ke acd_student_letters.
     */
    private function generateMutationLetter(
        Student $student,
        ?ClassGroup $classGroup,
        StudentMutation $mutation,
        string $letterNumber,
        string $mutationDate,
        MutationStatus $statusEnum
    ): bool {
        $school = CoreSchool::with(['headmaster.vault', 'headmaster.currentGrade.grade'])->first();

        if (! $school) {
            return false;
        }

        $guardian = $student->guardians()
            ->with('vault')
            ->get()
            ->sortBy(fn($g) => match ($g->relationship?->value) {
                'guardian' => 0,
                'father'   => 1,
                'mother'   => 2,
                default    => 3,
            })
            ->first();

        $letterDate = Carbon::parse($mutationDate);

        // Menyesuaikan View dan Letter Type berdasarkan Status
        $viewTemplate = match ($statusEnum) {
            MutationStatus::TRANSFER_OUT => 'pages.admin.students.letters.pdf.pindah-sekolah',
            MutationStatus::DISMISSED    => 'pages.admin.students.letters.pdf.dikeluarkan',
            MutationStatus::RESIGNED     => 'pages.admin.students.letters.pdf.mengundurkan-diri',
            default                      => 'pages.admin.students.letters.pdf.pindah-sekolah',
        };

        $letterType = match ($statusEnum) {
            MutationStatus::TRANSFER_OUT => LetterType::TRANSFER->value,
            MutationStatus::DISMISSED    => LetterType::DISMISSED->value, // Pastikan ada di Enum LetterType
            MutationStatus::RESIGNED     => LetterType::RESIGNED->value, // Pastikan ada di Enum LetterType
            default                      => 'unknown',
        };

        // Ukuran kertas F4 (215mm x 330mm) dalam satuan points
        $f4PaperSize = [0, 0, 609.448, 935.433];

        $pdf = Pdf::loadView($viewTemplate, [
            'school'       => $school,
            'student'      => $student,
            'classGroup'   => $classGroup,
            'mutation'     => $mutation,
            'guardian'     => $guardian,
            'letterNumber' => $letterNumber,
            'letterDate'   => $letterDate->translatedFormat('d F Y'),
        ])->setPaper($f4PaperSize, 'portrait');

        $fileName = sprintf('%s-%s-%s.pdf', $letterType, now()->format('YmdHis'), Str::random(8));
        $path     = "surat/{$fileName}";

        $this->disk()->put($path, $pdf->output());

        StudentLetter::create([
            'student_id'     => $student->id,
            'class_group_id' => $classGroup?->id,
            // Pakai semester_id dari record mutasi (sudah mengikuti
            // semester yang sedang dilihat admin di topbar - lihat
            // store()), bukan semester_id bawaan rombel siswa. Keduanya
            // bisa berbeda kalau rombel aktif siswa tidak sama dengan
            // semester yang sedang dibuka admin, dan surat harus konsisten
            // dengan record mutasi yang menyertainya.
            'semester_id'    => $mutation->semester_id,
            'letter_type'    => $letterType,
            'letter_number'  => $letterNumber,
            'letter_date'    => $letterDate,
            'file_path'      => $path,
            'created_by'     => Auth::id(),
        ]);

        return true;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk('local');
    }
}
