<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\ClassGroupStudent;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentGraduationController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->input('search');
        $filterYear = $request->input('filter_year');

        // Nama tabel fisik acd_class_group_students, diambil dari model
        // agar tidak hardcode dan tetap konsisten jika nama tabel berubah.
        $classGroupStudentsTable = (new ClassGroupStudent())->getTable();

        // Ambil data siswa dengan status 'graduated' beserta data vault-nya.
        // "Tahun kelulusan" bukan kolom tersendiri, melainkan diambil dari
        // exit_date TERAKHIR (baris paling akhir) di riwayat rombel siswa
        // pada tabel acd_class_group_students — baris terakhir itu otomatis
        // adalah saat siswa tersebut lulus.
        $query = Student::with('vault')
            ->where('status', 'graduated')
            ->withMax('classGroupStudents as latest_exit_date', 'exit_date');

        // Pencarian berdasarkan nama atau NIS
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tahun kelulusan (tahun dari exit_date terakhir)
        if ($filterYear) {
            $query->whereHas('classGroupStudents', function ($q) use ($filterYear, $classGroupStudentsTable) {
                $q->whereNotNull('exit_date')
                    ->whereYear('exit_date', $filterYear)
                    // Pastikan baris ini benar-benar baris TERAKHIR milik siswa
                    // tersebut (exit_date-nya sama dengan MAX(exit_date) miliknya).
                    ->where('exit_date', '=', function ($sub) use ($classGroupStudentsTable) {
                        $sub->selectRaw('MAX(exit_date)')
                            ->from("{$classGroupStudentsTable} as latest_cgs")
                            ->whereColumn('latest_cgs.student_id', "{$classGroupStudentsTable}.student_id");
                    });
            });
        }

        $graduates = $query
            ->orderByDesc('latest_exit_date')
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Ambil daftar tahun kelulusan unik untuk dropdown filter, berdasarkan
        // exit_date terakhir tiap siswa yang berstatus 'graduated'
        $yearOptions = $this->getYearOptions();

        // Render partial untuk request HTMX
        if ($request->header('HX-Request') && !$request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.graduates.partials._table', compact('graduates'));
        }

        return view('pages.admin.students.graduates.index', compact('graduates', 'search', 'filterYear', 'yearOptions'));
    }

    public function show($id)
    {
        $student = Student::with('vault', 'classGroupStudents.classGroup.concentration')
            ->where('status', 'graduated')
            ->findOrFail($id);

        return view('pages.admin.students.graduates.show', compact('student'));
    }

    /**
     * Daftar tahun kelulusan unik, diambil dari exit_date TERAKHIR
     * (baris paling akhir riwayat rombel) tiap siswa berstatus 'graduated'.
     */
    private function getYearOptions()
    {
        $table = (new ClassGroupStudent())->getTable();

        return ClassGroupStudent::query()
            ->selectRaw('YEAR(exit_date) as year')
            ->whereNotNull('exit_date')
            ->whereHas('student', function ($q) {
                $q->where('status', 'graduated');
            })
            // Hanya ambil baris yang merupakan exit_date TERAKHIR milik siswanya
            ->where('exit_date', '=', function ($sub) use ($table) {
                $sub->selectRaw('MAX(exit_date)')
                    ->from("{$table} as latest_cgs")
                    ->whereColumn('latest_cgs.student_id', "{$table}.student_id");
            })
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
