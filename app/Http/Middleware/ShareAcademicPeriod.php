<?php

namespace App\Http\Middleware;

use App\Services\AcademicPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * Membagikan konteks "semester yang sedang dilihat" ke SEMUA view admin,
 * supaya dropdown pemilih semester di topbar bisa tampil di halaman
 * manapun tanpa tiap controller harus mengirim variabel ini satu-satu.
 *
 * Didaftarkan sebagai middleware di grup route `admin.` (lihat web.php),
 * jadi otomatis berjalan di setiap request admin sebelum view dirender.
 */
class ShareAcademicPeriod
{
    public function handle(Request $request, Closure $next)
    {
        $academicPeriod = app(AcademicPeriod::class);

        View::share('currentAcademicPeriod', $academicPeriod->current());
        View::share('academicPeriodOptions', $academicPeriod->options());
        View::share('isAcademicPeriodOverridden', $academicPeriod->isOverridden());

        return $next($request);
    }
}
