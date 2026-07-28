<?php

namespace App\Http\Controllers\Admin\Students;

use App\Enums\Student\MutationStatus;
use App\Enums\Student\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ClassGroupAttendanceController extends Controller
{
    /**
     * Definisi kondisi "anggota rombel" (diambil dari ClassGroupController
     * agar mandiri dan tidak perlu mengubah Trait secara global).
     */
    private function memberOfClassGroupCondition(string $cgsTable): \Closure
    {
        return function ($q) use ($cgsTable) {
            $q->whereIn('acd_students.status', [
                StudentStatus::ACTIVE->value,
                StudentStatus::GRADUATED->value
            ])
                ->whereNull("{$cgsTable}.exit_reason")
                ->where(function ($qPivot) use ($cgsTable) {
                    $qPivot->whereNull("{$cgsTable}.mutation_id")
                        ->orWhereExists(function ($sub) use ($cgsTable) {
                            $sub->selectRaw('1')
                                ->from('acd_student_mutations')
                                ->whereColumn('acd_student_mutations.id', "{$cgsTable}.mutation_id")
                                ->where('acd_student_mutations.status', MutationStatus::GRADUATED->value);
                        });
                });
        };
    }

    /**
     * 1. Menampilkan Modal Cetak (Dipanggil dari tombol Daftar Hadir di index)
     */
    public function showModal()
    {
        $concentrationOptions = CoreConcentration::orderBy('name')->pluck('name', 'id');

        // Buat file view ini nanti yang berisi form dengan dropdown tingkat, konsentrasi, dan kelas
        return view('pages.admin.students.groups.partials._modal-print-attendance', compact('concentrationOptions'));
    }

    /**
     * 2. Endpoint HTMX: Mengambil daftar kelas sesuai Tingkat & Konsentrasi yang dipilih
     */
    public function getFilteredClasses(Request $request)
    {
        $grade = $request->query('filter_grade');
        $concentrationId = $request->query('filter_concentration');

        $activeSemester = CoreSemester::where('status', 'active')->first();

        $classes = ClassGroup::where('semester_id', $activeSemester?->id)
            ->when($grade, fn($q) => $q->where('grade_level', $grade))
            ->when($concentrationId, fn($q) => $q->where('concentration_id', $concentrationId))
            ->orderBy('name', 'asc')
            ->get();

        // [!] UBAH TEKS DI BARIS INI: dari "-- Pilih Kelas --" menjadi "-- Semua Kelas --"
        $html = '<option value="">-- Semua Kelas --</option>';

        foreach ($classes as $class) {
            $html .= '<option value="' . $class->id . '">' . $class->name . '</option>';
        }

        return response($html);
    }

    /**
     * 3. Memproses dan Render PDF
     */
    public function printPdf(Request $request)
    {
        // 1. Validasi: Tingkat wajib, sisanya opsional
        $request->validate([
            'filter_grade' => 'required|in:10,11,12',
            'filter_concentration' => 'nullable|exists:core_concentrations,id',
            'class_group_id' => 'nullable|exists:acd_class_groups,id'
        ]);

        $activeSemester = CoreSemester::where('status', 'active')->first();

        // 2. Susun Query Pencarian Rombel
        $query = ClassGroup::with(['concentration', 'homeroomTeacher', 'semester'])
            ->where('semester_id', $activeSemester?->id)
            ->where('grade_level', $request->filter_grade);

        // Jika user memilih Konsentrasi tertentu
        if ($request->filled('filter_concentration')) {
            $query->where('concentration_id', $request->filter_concentration);
        }

        // Jika user memilih 1 Kelas spesifik
        if ($request->filled('class_group_id')) {
            $query->where('id', $request->class_group_id);
        }

        // Ambil data rombel (bisa 1, bisa juga banyak/semua kelas)
        $classGroups = $query->orderBy('name', 'asc')->get();

        if ($classGroups->isEmpty()) {
            return response('Tidak ada data rombongan belajar yang ditemukan untuk kriteria ini.', 404);
        }

        $cgsTable = (new ClassGroupStudent())->getTable();
        $memberCondition = $this->memberOfClassGroupCondition($cgsTable);

        // 3. Siapkan Array untuk menampung data multi-halaman
        $pagesData = [];

        foreach ($classGroups as $classGroup) {

            // 1. TAMBAHKAN with(['vault']) AGAR NISN TERBACA
            $students = $classGroup->students()
                ->with(['vault'])
                ->where($memberCondition)
                ->orderBy('acd_students.name', 'asc')
                ->get();

            $lakiLaki = $students->where('gender', 'L')->count();
            $perempuan = $students->where('gender', 'P')->count();

            $result = (object) [
                'lakiLaki' => $lakiLaki,
                'perempuan' => $perempuan,
                'total' => $lakiLaki + $perempuan,
            ];

            $dataRombel = (object) [
                'nama_rombel' => $classGroup->name,
                'dataJurusan' => (object) ['jurusan' => $classGroup->concentration->name ?? '-'],
                'dataGuru' => (object) [
                    'nama' => $classGroup->homeroomTeacher ? $classGroup->homeroomTeacher->name_capital_with_title : 'Belum Diatur',
                    'nip' => $classGroup->homeroomTeacher->nip ?? '~'
                ]
            ];

            // 2. AMBIL DARI RELASI CORE SEMESTERS
            $tahunAjaran = (object) [
                // Sesuaikan 'name' dengan nama kolom asli di tabel core_semesters Anda.
                // Jika nama kolomnya berbeda (misal: 'academic_year' atau 'tahun_ajaran'), 
                // silakan ubah kata 'name' di bawah ini.
                'tahun_ajaran' => $classGroup->semester->academicYear->name ?? '-'
            ];

            // 3. PANGGIL NISN MELALUI RELASI VAULT
            $rombel = $students->map(function ($student) {
                return (object) [
                    'dataPesertaDidik' => (object) [
                        'nis' => $student->nis,
                        'nama' => $student->name,
                        'nisn' => $student->vault?->nisn_encrypted ?? '',
                        'jk' => $student->gender,
                    ]
                ];
            });

            $pagesData[] = compact('dataRombel', 'tahunAjaran', 'rombel', 'result');
        }

        // 4. Render ke DomPDF
        $pdf = Pdf::loadView('pages.admin.students.groups.pdf.attendance', compact('pagesData'));

        // Menggunakan array untuk ukuran kustom F4 (Lebar: 612pt, Tinggi: 936pt)
        $pdf->setPaper(array(0, 0, 612, 936), 'portrait');

        // Penamaan file dinamis
        $filename = $request->filled('class_group_id')
            ? 'Daftar_Hadir_' . str_replace(' ', '_', $classGroups->first()->name) . '.pdf'
            : 'Daftar_Hadir_Tingkat_' . $request->filter_grade . '.pdf';

        return $pdf->stream($filename);
    }
}
