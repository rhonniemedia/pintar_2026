<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\CoreSemester;
use App\Models\StudentMutation;
use Illuminate\Http\Request;

class StudentMutationOutController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $semesterAktif = CoreSemester::where('status', 'active')->first();

        $data = StudentMutation::with(['student.vault', 'classGroup.concentration'])
            ->where('status', 'transfer_out')
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('mutation_date')
            ->paginate(10)
            ->withQueryString();

        // Jika request dari HTMX (pencarian/paginasi), kembalikan tabel saja
        if ($request->header('HX-Request') && ! $request->header('HX-History-Restore-Request')) {
            return view('pages.admin.students.transfers.out.partials._table', compact('data', 'search'));
        }

        return view('pages.admin.students.transfers.out.index', [
            'title' => 'Mutasi Peserta Didik - Pindahan Keluar',
            'data' => $data,
            'search' => $search,
            'semesterAktif' => $semesterAktif,
        ]);
    }
}
