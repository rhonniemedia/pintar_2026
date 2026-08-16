<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\MutationStatus;
use App\Enums\Student\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Models\StudentMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentMutationOutController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $semesterAktif = CoreSemester::where('status', 'active')->first();

        $data = StudentMutation::with(['student.vault', 'classGroup.concentration'])
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
        $semesterAktif = CoreSemester::where('status', 'active')->first();

        $students = Student::with('activeClassGroup')
            ->where('status', StudentStatus::ACTIVE->value)
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

        $student = Student::with('activeClassGroup')->findOrFail($validated['student_id']);
        $semesterAktif = CoreSemester::where('status', 'active')->first();
        $activeGroup = $student->activeClassGroup->first();

        // 2. Pemetaan Status menggunakan Enum tersentralisasi & Ekstraksi Data Form
        $statusEnum          = MutationStatus::from($validated['status']);
        $finalMutationStatus = $statusEnum->value;
        $finalStudentStatus  = $statusEnum->resultingStudentStatus()->value;

        $destinationSchool = match ($statusEnum) {
            MutationStatus::TRANSFER_OUT => $validated['destination_school'] ?? null,
            default => null,
        };

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

        // 3. Simpan dalam Satu Transaksi
        DB::transaction(function () use (
            $student,
            $finalStudentStatus,
            $finalMutationStatus,
            $semesterAktif,
            $validated,
            $referenceNumber,
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
                'reference_number'   => $referenceNumber,
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
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'close-modal'  => true,
            'refreshTable' => true,
            'showAlert'    => [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => 'Peserta didik berhasil dimutasi dan dikeluarkan dari rombongan belajar.'
            ]
        ]));
    }
}
