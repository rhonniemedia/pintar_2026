<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    /**
     * Middleware ini berjalan SETELAH AuthorizeAppAccess (yang sudah meloloskan
     * role superadmin & admin untuk akses /admin/*). Di sini mempersempit lagi
     * khusus untuk route yang hanya boleh diakses superadmin, misalnya /admin/users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $appIdSekarang = config('app.core_id');

        $roleAplikasi = $user->roles()->wherePivot('app_id', $appIdSekarang)->first();

        if ($roleAplikasi && $roleAplikasi->name === 'superadmin') {
            return $next($request);
        }

        return response()->view('pages.auth.forbidden', [], 403);
    }
}
