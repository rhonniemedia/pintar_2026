<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Services\AcademicPeriod;
use Illuminate\Http\Request;

/**
 * Menyimpan pilihan "semester yang sedang dilihat" ke session, dipanggil
 * dari dropdown semester di topbar. Tidak menyentuh status semester aktif
 * di Data Master sama sekali -- murni preferensi tampilan per sesi login.
 */
class AcademicPeriodController extends Controller
{
    public function update(Request $request, AcademicPeriod $academicPeriod)
    {
        $request->validate([
            'semester_id' => 'required|exists:core_semesters,id',
        ]);

        $academicPeriod->setSelected($request->input('semester_id'));

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'));
        }

        return back();
    }

    /**
     * Reset ke default (ikut semester aktif Data Master lagi).
     */
    public function reset(Request $request, AcademicPeriod $academicPeriod)
    {
        $academicPeriod->clearSelected();

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'));
        }

        return back();
    }
}
