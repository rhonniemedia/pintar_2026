<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\MutationStatus;
use App\Enums\Student\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreSemester;
use App\Models\StudentMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Menangani proses kenaikan kelas (grade 10 & 11, semester genap)
 * dan kelulusan (grade 12, semester genap).
 */
class ClassGroupPromotionController extends Controller
{
    private const GRADE_UP = [
        '10' => '11',
        '11' => '12',
    ];

    /* =====================================================================
     * KENAIKAN KELAS
     * ===================================================================== */

    public function promotionForm(ClassGroup $classGroup)
    {
        $this->assertPromotionAllowed($classGroup);

        $nextSemester = $this->nextSemesterOf($classGroup);

        if (!$nextSemester) {
            return view('pages.admin.students.groups.partials._promotion-modal', [
                'classGroup' => $classGroup,
                'candidates' => collect(),
                'targetGroupsNaik' => collect(),
                'targetGroupsTinggal' => collect(),
                'nextSemesterMissing' => true,
            ]);
        }

        $nextGrade = self::GRADE_UP[$classGroup->grade_level];

        $candidates = $this->activeCandidates($classGroup)
            ->whereDoesntHave('student.classGroupStudents', function ($q) use ($nextSemester) {
                $q->whereHas('classGroup', fn($qq) => $qq->where('semester_id', $nextSemester->id));
            })
            ->get();

        $targetGroupsNaik = ClassGroup::where('semester_id', $nextSemester->id)
            ->where('concentration_id', $classGroup->concentration_id)
            ->where('grade_level', $nextGrade)
            ->orderBy('group_number')
            ->get();

        $targetGroupsTinggal = ClassGroup::where('semester_id', $nextSemester->id)
            ->where('concentration_id', $classGroup->concentration_id)
            ->where('grade_level', $classGroup->grade_level)
            ->orderBy('group_number')
            ->get();

        return view('pages.admin.students.groups.partials._promotion-modal', [
            'classGroup' => $classGroup,
            'candidates' => $candidates,
            'targetGroupsNaik' => $targetGroupsNaik,
            'targetGroupsTinggal' => $targetGroupsTinggal,
            'nextSemesterMissing' => false,
        ]);
    }

    public function promote(Request $request, ClassGroup $classGroup)
    {
        $this->assertPromotionAllowed($classGroup);

        $data = $request->validate([
            'decision' => 'required|in:naik,tinggal',
            'target_class_group_id' => 'required|uuid|exists:acd_class_groups,id',
            'student_id' => 'required|array|min:1',
            'student_id.*' => 'uuid|exists:acd_students,id',
            'entry_date' => 'required|date',
        ]);

        $nextSemester = $this->nextSemesterOf($classGroup);

        if (!$nextSemester) {
            return response()->json([
                'message' => 'Semester berikutnya belum tersedia. Buat semester berikutnya terlebih dahulu sebelum memproses kenaikan kelas.'
            ], 422);
        }

        $targetClassGroup = ClassGroup::where('id', $data['target_class_group_id'])
            ->where('semester_id', $nextSemester->id)
            ->firstOrFail();

        DB::transaction(function () use ($data, $classGroup, $targetClassGroup) {
            foreach ($data['student_id'] as $studentId) {
                // PERBAIKAN: Kolom status pivot dihapus, patokan aktif adalah exit_date NULL
                $currentRow = ClassGroupStudent::where('class_group_id', $classGroup->id)
                    ->where('student_id', $studentId)
                    ->whereNull('exit_date')
                    ->first();

                if (!$currentRow) {
                    continue;
                }

                $currentRow->update(['exit_date' => $data['entry_date']]);

                ClassGroupStudent::firstOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_group_id' => $targetClassGroup->id,
                    ],
                    [
                        'entry_date' => $data['entry_date'],
                        // PERBAIKAN: Hapus baris 'status' => 'active'
                    ]
                );
            }
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Proses kenaikan kelas berhasil disimpan.'
            ],
            'refreshClassData' => true
        ]));
    }

    public function promotionCancelForm(ClassGroup $classGroup)
    {
        $nextSemester = $this->nextSemesterOf($classGroup);

        $candidates = $nextSemester
            ? ClassGroupStudent::with('student')
            ->where('class_group_id', $classGroup->id)
            ->whereNotNull('exit_date')
            ->whereHas('student', function ($q) {
                $q->where('status', StudentStatus::ACTIVE->value);
            })
            ->whereHas('student.classGroupStudents', function ($q) use ($nextSemester) {
                $q->whereHas('classGroup', fn($qq) => $qq->where('semester_id', $nextSemester->id));
            })
            ->get()
            : collect();

        return view('pages.admin.students.groups.partials._promotion-cancel-modal', [
            'classGroup' => $classGroup,
            'candidates' => $candidates,
        ]);
    }

    public function cancelPromotion(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'student_id' => 'required|array|min:1',
            'student_id.*' => 'uuid|exists:acd_students,id',
        ]);

        $nextSemester = $this->nextSemesterOf($classGroup);

        if (!$nextSemester) {
            return response()->json([
                'message' => 'Tidak ada data kenaikan kelas yang bisa dibatalkan.'
            ], 422);
        }

        DB::transaction(function () use ($data, $classGroup, $nextSemester) {
            foreach ($data['student_id'] as $studentId) {
                ClassGroupStudent::whereHas('classGroup', fn($q) => $q->where('semester_id', $nextSemester->id))
                    ->where('student_id', $studentId)
                    ->delete();

                ClassGroupStudent::where('class_group_id', $classGroup->id)
                    ->where('student_id', $studentId)
                    ->update(['exit_date' => null]);
            }
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Dibatalkan!',
                'text' => 'Pembatalan kenaikan kelas berhasil disimpan.'
            ],
            'refreshClassData' => true
        ]));
    }

    /* =====================================================================
     * KELULUSAN
     * ===================================================================== */

    public function graduationForm(ClassGroup $classGroup)
    {
        $this->assertGraduationAllowed($classGroup);

        $candidates = $this->activeCandidates($classGroup)->get();

        $nextSemester = $this->nextSemesterOf($classGroup);
        $targetGroupsTidakLulus = $nextSemester
            ? ClassGroup::where('semester_id', $nextSemester->id)
            ->where('concentration_id', $classGroup->concentration_id)
            ->where('grade_level', '12')
            ->orderBy('group_number')
            ->get()
            : collect();

        return view('pages.admin.students.groups.partials._graduation-modal', [
            'classGroup' => $classGroup,
            'candidates' => $candidates,
            'targetGroupsTidakLulus' => $targetGroupsTidakLulus,
            'nextSemesterMissing' => is_null($nextSemester),
        ]);
    }

    public function graduate(Request $request, ClassGroup $classGroup)
    {
        $this->assertGraduationAllowed($classGroup);

        $data = $request->validate([
            'decision' => 'required|in:lulus,tidak-lulus',
            'exit_date' => 'required|date',
            'student_id' => 'required|array|min:1',
            'student_id.*' => 'uuid|exists:acd_students,id',
            'target_class_group_id' => 'required_if:decision,tidak-lulus|nullable|uuid|exists:acd_class_groups,id',
        ]);

        $nextSemester = $this->nextSemesterOf($classGroup);

        $targetClassGroup = null;
        if ($data['decision'] === 'tidak-lulus') {
            if (!$nextSemester) {
                return response()->json([
                    'message' => 'Semester berikutnya belum tersedia. Buat semester berikutnya terlebih dahulu untuk memproses siswa tidak lulus.'
                ], 422);
            }

            $targetClassGroup = ClassGroup::where('id', $data['target_class_group_id'])
                ->where('semester_id', $nextSemester->id)
                ->firstOrFail();
        }

        DB::transaction(function () use ($data, $classGroup, $targetClassGroup) {
            foreach ($data['student_id'] as $studentId) {
                $currentRow = ClassGroupStudent::with('student')
                    ->where('class_group_id', $classGroup->id)
                    ->where('student_id', $studentId)
                    // PERBAIKAN: Hapus where status = active
                    ->whereNull('exit_date')
                    ->first();

                if (!$currentRow) {
                    continue;
                }

                if ($data['decision'] === 'lulus') {
                    // PERBAIKAN: Buat record Mutasi untuk kelulusan
                    $mutation = StudentMutation::create([
                        'student_id'    => $studentId,
                        'semester_id'   => $classGroup->semester_id,
                        'class_group_id' => $classGroup->id,
                        'status'        => MutationStatus::GRADUATED->value,
                        'mutation_date' => $data['exit_date'],
                    ]);

                    $currentRow->update([
                        // PERBAIKAN: Status dihapus, gunakan mutation_id
                        'exit_date'   => $data['exit_date'],
                        'mutation_id' => $mutation->id,
                    ]);

                    $currentRow->student->update(['status' => StudentStatus::GRADUATED->value]);
                } else {
                    $currentRow->update(['exit_date' => $data['exit_date']]);

                    ClassGroupStudent::firstOrCreate(
                        [
                            'student_id' => $studentId,
                            'class_group_id' => $targetClassGroup->id,
                        ],
                        [
                            'entry_date' => $data['exit_date'],
                        ]
                    );
                }
            }
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Proses kelulusan berhasil disimpan.'
            ],
            'refreshClassData' => true
        ]));
    }

    public function graduationCancelForm(ClassGroup $classGroup)
    {
        $candidates = ClassGroupStudent::with('student')
            ->where('class_group_id', $classGroup->id)
            ->whereNotNull('exit_date')
            ->whereHas('student', function ($q) {
                $q->whereIn('status', [StudentStatus::ACTIVE->value, StudentStatus::GRADUATED->value]);
            })
            ->get();

        return view('pages.admin.students.groups.partials._graduation-cancel-modal', [
            'classGroup' => $classGroup,
            'candidates' => $candidates,
        ]);
    }

    public function cancelGraduation(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'student_id' => 'required|array|min:1',
            'student_id.*' => 'uuid|exists:acd_students,id',
        ]);

        $nextSemester = $this->nextSemesterOf($classGroup);

        DB::transaction(function () use ($data, $classGroup, $nextSemester) {
            foreach ($data['student_id'] as $studentId) {
                $currentRow = ClassGroupStudent::with('student')
                    ->where('class_group_id', $classGroup->id)
                    ->where('student_id', $studentId)
                    ->first();

                if (!$currentRow) {
                    continue;
                }

                // PERBAIKAN: Cek apakah kelulusan terkait dengan mutation_id
                if ($currentRow->student->status->value === StudentStatus::GRADUATED->value || $currentRow->mutation_id) {

                    // Hapus data mutasi kelulusannya jika ada
                    if ($currentRow->mutation_id) {
                        StudentMutation::where('id', $currentRow->mutation_id)->delete();
                    }

                    $currentRow->update([
                        'exit_date' => null,
                        'mutation_id' => null, // Reset mutation_id
                    ]);

                    $currentRow->student->update(['status' => StudentStatus::ACTIVE->value]);
                } elseif ($nextSemester) {
                    ClassGroupStudent::whereHas('classGroup', fn($q) => $q->where('semester_id', $nextSemester->id))
                        ->where('student_id', $studentId)
                        ->delete();

                    $currentRow->update(['exit_date' => null]);
                }
            }
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'showAlert' => [
                'icon' => 'success',
                'title' => 'Dibatalkan!',
                'text' => 'Pembatalan kelulusan berhasil disimpan.'
            ],
            'refreshClassData' => true
        ]));
    }

    /* =====================================================================
     * HELPER
     * ===================================================================== */

    private function assertPromotionAllowed(ClassGroup $classGroup): void
    {
        $semester = $classGroup->semester;

        abort_unless(
            in_array($classGroup->grade_level, ['10', '11'], true) && $semester?->isEven(),
            403,
            'Kenaikan kelas hanya berlaku untuk tingkat 10 & 11 pada semester genap.'
        );
    }

    private function assertGraduationAllowed(ClassGroup $classGroup): void
    {
        $semester = $classGroup->semester;

        abort_unless(
            $classGroup->grade_level === '12' && $semester?->isEven(),
            403,
            'Kelulusan hanya berlaku untuk tingkat 12 pada semester genap.'
        );
    }

    private function nextSemesterOf(ClassGroup $classGroup): ?CoreSemester
    {
        return $classGroup->semester?->next;
    }

    /**
     * Query dasar siswa aktif (belum diproses) di rombel ini.
     */
    private function activeCandidates(ClassGroup $classGroup)
    {
        return ClassGroupStudent::with(['student', 'student.vault'])
            ->where('class_group_id', $classGroup->id)
            // PERBAIKAN: Gunakan exit_reason dan mutation_id, BUKAN exit_date
            ->whereNull('exit_reason')
            ->whereNull('mutation_id')
            ->whereHas('student', function ($query) {
                $query->where('status', StudentStatus::ACTIVE->value);
            });
    }
}
