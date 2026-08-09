<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use App\Models\Student;
use App\Models\StudentMutation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class StudentReportController extends Controller
{
    // =========================================================================
    // 1. REKAPITULASI JURUSAN
    // =========================================================================

    public function concentrationModal()
    {
        return view('pages.admin.home.partials._concentration-modal');
    }

    public function concentrationReport(Request $request)
    {
        Carbon::setLocale('id');

        $dataSekolah = (object) [
            'sekolah' => 'SMK NEGERI 1 REJANG LEBONG',
            'kepalaSekolah' => (object) ['nama' => 'Dr. H. Fulan, M.Pd', 'nip' => '19700101 199512 1 001'],
            'kesiswaan' => (object) ['nama' => 'Budi Santoso, S.Pd', 'nip' => '19800202 200501 1 002'],
            'kaTu' => (object) ['nama' => 'Siti Aminah, S.E', 'nip' => '19750303 200003 2 003'],
        ];

        $semester = CoreSemester::with('academicYear')->where('status', 'active')->firstOrFail();

        $concentrations = CoreConcentration::with(['classGroups' => function ($q) use ($semester) {
            $q->where('semester_id', $semester->id)->with('activeStudents');
        }])
            ->orderBy('name', 'asc')
            ->get();

        $reportData = [];
        $grandTotal = [
            '10' => ['L' => 0, 'P' => 0, 'JML' => 0],
            '11' => ['L' => 0, 'P' => 0, 'JML' => 0],
            '12' => ['L' => 0, 'P' => 0, 'JML' => 0],
            'total' => 0
        ];

        foreach ($concentrations as $concentration) {
            $jurusanRow = [
                'name' => $concentration->name,
                'grades' => [
                    '10' => ['L' => 0, 'P' => 0, 'JML' => 0],
                    '11' => ['L' => 0, 'P' => 0, 'JML' => 0],
                    '12' => ['L' => 0, 'P' => 0, 'JML' => 0],
                ],
                'total_jurusan' => 0
            ];

            foreach ($concentration->classGroups as $cg) {
                // Perbaikan deteksi tingkat kelas
                $gradeVal = trim((string) $cg->grade_level);
                $mappedGrade = '10';

                if ($gradeVal === '12' || str_contains(strtoupper($gradeVal), 'XII')) {
                    $mappedGrade = '12';
                } elseif ($gradeVal === '11' || str_contains(strtoupper($gradeVal), 'XI')) {
                    $mappedGrade = '11';
                } elseif ($gradeVal === '10' || str_contains(strtoupper($gradeVal), 'X')) {
                    $mappedGrade = '10';
                }

                // Perbaikan perhitungan L/P agar kebal terhadap Enum Casting
                $maleCount = 0;
                $femaleCount = 0;

                foreach ($cg->activeStudents as $student) {
                    $genderVal = is_object($student->gender) ? $student->gender->value : $student->gender;
                    if (strtoupper(trim((string) $genderVal)) === 'L') {
                        $maleCount++;
                    } elseif (strtoupper(trim((string) $genderVal)) === 'P') {
                        $femaleCount++;
                    }
                }

                $totalSiswa = $maleCount + $femaleCount;

                $jurusanRow['grades'][$mappedGrade]['L'] += $maleCount;
                $jurusanRow['grades'][$mappedGrade]['P'] += $femaleCount;
                $jurusanRow['grades'][$mappedGrade]['JML'] += $totalSiswa;
                $jurusanRow['total_jurusan'] += $totalSiswa;

                $grandTotal[$mappedGrade]['L'] += $maleCount;
                $grandTotal[$mappedGrade]['P'] += $femaleCount;
                $grandTotal[$mappedGrade]['JML'] += $totalSiswa;
                $grandTotal['total'] += $totalSiswa;
            }

            $reportData[] = $jurusanRow;
        }

        $bulanAwal = $request->tgl_mulai ? Carbon::parse($request->tgl_mulai) : Carbon::now()->startOfMonth();
        $bulanAkhir = $request->tgl_selesai ? Carbon::parse($request->tgl_selesai) : Carbon::now()->endOfMonth();
        $namaBulanAwal = strtoupper($bulanAwal->translatedFormat('F'));
        $namaBulanAkhir = strtoupper($bulanAkhir->translatedFormat('F'));
        $tahun = $bulanAwal->translatedFormat('Y');

        $bulan = ($bulanAwal->year === $bulanAkhir->year)
            ? (($namaBulanAwal !== $namaBulanAkhir) ? "$namaBulanAwal - $namaBulanAkhir $tahun" : "$namaBulanAwal $tahun")
            : "$namaBulanAwal " . $bulanAwal->year . " - $namaBulanAkhir " . $bulanAkhir->year;

        $tglValidasi = $request->tgl_validasi ? Carbon::parse($request->tgl_validasi)->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y');

        $html = view('pages.admin.reports.rekapitulasi-jurusan', [
            'title'       => 'Rekapitulasi Kompetensi Keahlian',
            'tahunAjaran' => $semester->academicYear,
            'reportData'  => $reportData,
            'grandTotal'  => $grandTotal,
            'tglValidasi' => $tglValidasi,
            'bulan'       => $bulan,
            'sekolah'     => $dataSekolah
        ])->render();

        $pdf = App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("enable_remote", true);
        $pdf->getDomPDF()->set_option("chroot", public_path());

        $pdf->loadHTML($html)->setPaper([0, 0, 612, 936], 'landscape');

        return $pdf->stream('Rekapitulasi Data Peserta Didik.pdf');
    }

    // =========================================================================
    // 2. KEADAAN PESERTA DIDIK
    // =========================================================================

    public function studentCountModal()
    {
        return view('pages.admin.home.partials._student-count-modal');
    }

    public function studentCountReport(Request $request)
    {
        Carbon::setLocale('id');

        $dataSekolah = (object) [
            'sekolah' => 'SMK NEGERI 1 REJANG LEBONG',
            'kepalaSekolah' => (object) ['nama' => 'Dr. H. Fulan, M.Pd', 'nip' => '19700101 199512 1 001'],
            'kesiswaan' => (object) ['nama' => 'Budi Santoso, S.Pd', 'nip' => '19800202 200501 1 002'],
            'kaTu' => (object) ['nama' => 'Siti Aminah, S.E', 'nip' => '19750303 200003 2 003'],
        ];

        $tanggalMulai = $request->tanggal_mulai ? Carbon::parse($request->tanggal_mulai)->startOfDay() : Carbon::now()->startOfYear();
        $tanggalAkhir = $request->tanggal_akhir ? Carbon::parse($request->tanggal_akhir)->endOfDay() : Carbon::now()->endOfDay();

        $semester = CoreSemester::where('status', 'active')->firstOrFail();
        $concentrations = CoreConcentration::orderBy('name', 'asc')->get();

        $laporan = [];

        foreach ($concentrations as $concentration) {
            $classGroups = ClassGroup::where('concentration_id', $concentration->id)
                ->where('semester_id', $semester->id)
                ->orderBy('grade_level')
                ->get();

            $rombelData = [];

            foreach ($classGroups as $rombel) {
                // ================================================================
                // Snapshot AKHIR: siswa yang aktif di rombel ini SEKARANG (real-time)
                // ================================================================
                $akhirIds = collect();
                $akhirL = 0;
                $akhirP = 0;

                foreach ($rombel->activeStudents as $student) {
                    $genderVal = is_object($student->gender) ? $student->gender->value : $student->gender;
                    $akhirIds->push($student->id);
                    if (strtoupper(trim((string) $genderVal)) === 'L') {
                        $akhirL++;
                    } elseif (strtoupper(trim((string) $genderVal)) === 'P') {
                        $akhirP++;
                    }
                }

                // ================================================================
                // Mutasi SELAMA periode laporan, diambil sebagai ID siswa (bukan
                // sekadar count) agar rekonstruksi snapshot "Awal" di bawah tetap
                // akurat walau ada siswa yang bermutasi lebih dari sekali dalam
                // periode yang sama. Cukup pakai mutation_date sebagai acuan —
                // tidak perlu entry_date/exit_date di pivot rombel.
                // ================================================================
                $masukIdsPeriode = $rombel->mutations()
                    ->where('status', 'transfer_in')
                    ->whereBetween('mutation_date', [$tanggalMulai, $tanggalAkhir])
                    ->pluck('student_id');

                // FIX: siswa lulus (graduated) tidak dihitung sebagai "keluar" —
                // konsisten dengan activeStudents() yang tetap menghitung mereka
                // sebagai anggota rombel (akhir) selagi semester masih aktif.
                // Kalau graduated ikut masuk "keluar" di sini, siswa yang sama
                // akan tampil kontradiktif: dihitung "keluar" tapi juga masih
                // dihitung "aktif" di kolom Akhir.
                $keluarIdsPeriode = $rombel->mutations()
                    ->where('status', '!=', 'transfer_in')
                    ->where('status', '!=', \App\Enums\Student\MutationStatus::GRADUATED->value)
                    ->whereBetween('mutation_date', [$tanggalMulai, $tanggalAkhir])
                    ->pluck('student_id');

                $masukL = $rombel->mutations()->where('status', 'transfer_in')
                    ->whereBetween('mutation_date', [$tanggalMulai, $tanggalAkhir])
                    ->whereHas('student', fn($q) => $q->where('gender', 'L'))->count();
                $masukP = $rombel->mutations()->where('status', 'transfer_in')
                    ->whereBetween('mutation_date', [$tanggalMulai, $tanggalAkhir])
                    ->whereHas('student', fn($q) => $q->where('gender', 'P'))->count();

                $keluarL = $rombel->mutations()->where('status', '!=', 'transfer_in')
                    ->where('status', '!=', \App\Enums\Student\MutationStatus::GRADUATED->value)
                    ->whereBetween('mutation_date', [$tanggalMulai, $tanggalAkhir])
                    ->whereHas('student', fn($q) => $q->where('gender', 'L'))->count();
                $keluarP = $rombel->mutations()->where('status', '!=', 'transfer_in')
                    ->where('status', '!=', \App\Enums\Student\MutationStatus::GRADUATED->value)
                    ->whereBetween('mutation_date', [$tanggalMulai, $tanggalAkhir])
                    ->whereHas('student', fn($q) => $q->where('gender', 'P'))->count();

                // ================================================================
                // Rekonstruksi snapshot AWAL (sebelum periode dimulai):
                // = siswa aktif SEKARANG, DIKURANGI yang baru masuk selama periode
                //   (mereka belum ada di rombel saat cutoff),
                //   DITAMBAH yang sempat keluar selama periode
                //   (mereka masih ada di rombel saat cutoff, walau sekarang sudah tidak).
                // Pakai operasi diff/merge atas ID (bukan aritmatika count) supaya
                // tidak salah hitung kalau ada siswa yang mutasi lebih dari sekali.
                // ================================================================
                $awalIds = $akhirIds
                    ->diff($masukIdsPeriode)
                    ->merge($keluarIdsPeriode)
                    ->unique();

                $genderCounts = [];
                if ($awalIds->isNotEmpty()) {
                    $genderCounts = Student::whereIn('id', $awalIds)
                        ->select('gender', DB::raw('count(*) as total'))
                        ->groupBy('gender')
                        ->pluck('total', 'gender')
                        ->toArray();
                }

                $awalL = $genderCounts['L'] ?? 0;
                $awalP = $genderCounts['P'] ?? 0;

                $rombelData[] = [
                    'rombel' => $rombel,
                    'awal'   => ['L' => $awalL, 'P' => $awalP, 'J' => $awalL + $awalP],
                    'masuk'  => ['L' => $masukL, 'P' => $masukP, 'J' => $masukL + $masukP],
                    'keluar' => ['L' => $keluarL, 'P' => $keluarP, 'J' => $keluarL + $keluarP],
                    'akhir'  => ['L' => $akhirL, 'P' => $akhirP, 'J' => $akhirL + $akhirP],
                ];
            }

            if (count($rombelData) > 0) {
                $laporan[] = [
                    'jurusan' => $concentration,
                    'rombels' => $rombelData,
                ];
            }
        }

        $tglValidasi = $request->tgl_validasi ? Carbon::parse($request->tgl_validasi)->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y');

        $html = view('pages.admin.reports.rekapitulasi-siswa', [
            'title'        => 'Rekapitulasi Keadaan Peserta Didik',
            'laporan'      => $laporan,
            'tahunAjaran'  => $semester->academicYear,
            'tanggalMulai' => $tanggalMulai->translatedFormat('d F Y'),
            'tglValidasi'  => $tglValidasi,
            'sekolah'      => $dataSekolah,
            'bulan'        => strtoupper($tanggalMulai->translatedFormat('F') . ' - ' . $tanggalAkhir->translatedFormat('F Y')),
        ])->render();

        $pdf = App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("enable_remote", true);
        $pdf->getDomPDF()->set_option("chroot", public_path());

        $pdf->loadHTML($html)->setPaper([0, 0, 612, 936], 'portrait');

        return $pdf->stream('Rekapitulasi Keadaan Peserta Didik.pdf');
    }

    // =========================================================================
    // 3. MUTASI PESERTA DIDIK
    // =========================================================================

    public function mutationModal()
    {
        return view('pages.admin.home.partials._mutation-modal');
    }

    public function mutationReport(Request $request)
    {
        Carbon::setLocale('id');

        $dataSekolah = (object) [
            'sekolah' => 'SMK NEGERI 1 REJANG LEBONG',
            'kepalaSekolah' => (object) ['nama' => 'Dr. H. Fulan, M.Pd', 'nip' => '19700101 199512 1 001'],
            'kesiswaan' => (object) ['nama' => 'Budi Santoso, S.Pd', 'nip' => '19800202 200501 1 002'],
            'kaTu' => (object) ['nama' => 'Siti Aminah, S.E', 'nip' => '19750303 200003 2 003'],
        ];

        $semester = CoreSemester::where('status', 'active')->firstOrFail();

        $awalBulan = $request->tgl_mulai ? Carbon::parse($request->tgl_mulai)->startOfDay() : Carbon::now()->startOfMonth();
        $akhirBulan = $request->tgl_selesai ? Carbon::parse($request->tgl_selesai)->endOfDay() : Carbon::now()->endOfMonth();

        $mutasiMasuk = StudentMutation::with(['student.vault', 'student.guardians.vault', 'classGroup'])
            ->where('semester_id', $semester->id)
            ->where('status', 'transfer_in')
            ->whereBetween('mutation_date', [$awalBulan, $akhirBulan])
            ->orderBy('mutation_date', 'asc')
            ->get();

        $mutasiKeluar = StudentMutation::with(['student.vault', 'student.guardians.vault', 'classGroup'])
            ->where('semester_id', $semester->id)
            ->where('status', '!=', 'transfer_in')
            // FIX: siswa lulus (graduated) tidak dianggap "mutasi keluar" —
            // kelulusan adalah proses normal, bukan perpindahan/keluar sekolah,
            // jadi tidak seharusnya muncul di laporan mutasi.
            ->where('status', '!=', \App\Enums\Student\MutationStatus::GRADUATED->value)
            ->whereBetween('mutation_date', [$awalBulan, $akhirBulan])
            ->orderBy('mutation_date', 'asc')
            ->get();

        $tglValidasi = $request->tgl_validasi ? Carbon::parse($request->tgl_validasi)->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y');
        $bulan = strtoupper($awalBulan->translatedFormat('F') . ($awalBulan->format('m') != $akhirBulan->format('m') ? ' - ' . $akhirBulan->translatedFormat('F') : '') . ' ' . $awalBulan->year);

        $html = view('pages.admin.reports.rekapitulasi-mutasi', [
            'title'        => 'Rekapitulasi Mutasi Peserta Didik',
            'sekolah'      => $dataSekolah,
            'mutasiMasuk'  => $mutasiMasuk,
            'mutasiKeluar' => $mutasiKeluar,
            'tgl_validasi' => $tglValidasi,
            'bulan'        => $bulan,
        ])->render();

        $pdf = App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("enable_remote", true);
        $pdf->getDomPDF()->set_option("chroot", public_path());

        $pdf->loadHTML($html)->setPaper([0, 0, 612, 936], 'landscape');

        return $pdf->stream('Rekapitulasi Mutasi Peserta Didik.pdf');
    }
}
