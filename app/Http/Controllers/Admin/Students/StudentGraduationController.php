<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentGraduationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterYear = $request->input('filter_year');

        // Ambil data siswa dengan status 'graduated' beserta data vault-nya
        $query = Student::with('vault')
            ->where('status', 'graduated');

        // Pencarian berdasarkan nama atau NIS
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tahun kelulusan
        if ($filterYear) {
            $query->where('graduation_year', $filterYear);
        }

        $graduates = $query->orderBy('graduation_year', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Ambil daftar tahun kelulusan yang unik untuk dropdown filter
        $yearOptions = Student::where('status', 'graduated')
            ->whereNotNull('graduation_year')
            ->select('graduation_year')
            ->distinct()
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');

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
}
