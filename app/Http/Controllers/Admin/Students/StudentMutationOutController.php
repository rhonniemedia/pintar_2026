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
            ->orderByDesc('mutation_date')
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
        // 1. Validasi Input (Ubah nama form ke destination_school)
        $validated = $request->validate([
            'student_id'         => 'required|exists:acd_students,id',
            'status'             => 'required|in:transfer_out,dropped_out',
            'mutation_date'      => 'required|date',
            'destination_school' => 'nullable|required_if:status,transfer_out',
            'notes_pindah'       => 'nullable|string',
            'detail_reason'      => 'nullable|required_if:status,dropped_out|in:dropped_out,resigned,married,deceased',
            'notes_meninggal'    => 'nullable|string',
        ]);

        $student = Student::with('activeClassGroup')->findOrFail($validated['student_id']);
        $semesterAktif = CoreSemester::where('status', 'active')->first();
        $activeGroup = $student->activeClassGroup->first();

        // 2. Pemetaan Status menggunakan Enum yang Tersentralisasi
        if ($validated['status'] === 'transfer_out') {
            $finalMutationStatus = MutationStatus::TRANSFER_OUT->value;
            $finalStudentStatus  = StudentStatus::TRANSFERRED_OUT->value;
            $destinationSchool   = $validated['destination_school'] ?? null;
            $notes               = $request->input('notes_pindah');
        } else {
            $reason = $validated['detail_reason'];

            $finalMutationStatus = match ($reason) {
                'resigned' => MutationStatus::RESIGNED->value,
                'married'  => MutationStatus::MARRIED->value,
                'deceased' => MutationStatus::DECEASED->value,
                default    => MutationStatus::DROPPED_OUT->value,
            };

            $finalStudentStatus = match ($reason) {
                'resigned' => StudentStatus::RESIGNED->value,
                'married'  => StudentStatus::MARRIED->value,
                'deceased' => StudentStatus::DECEASED->value,
                default    => StudentStatus::DROPPED_OUT->value,
            };

            $destinationSchool = null;
            $notes = $reason === 'deceased' ? $request->input('notes_meninggal') : null;
        }

        // 3. Simpan dalam Satu Transaksi
        DB::transaction(function () use (
            $student,
            $finalStudentStatus,
            $finalMutationStatus,
            $semesterAktif,
            $validated,
            $destinationSchool,
            $notes,
            $activeGroup
        ) {
            // 3a. Buat rekaman mutasi terlebih dahulu untuk mendapatkan ID-nya
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

            // 3c. Update pivot table: Hanya set exit_date dan mutation_id 
            // (Kolom 'status' pivot sudah tidak ada)
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
