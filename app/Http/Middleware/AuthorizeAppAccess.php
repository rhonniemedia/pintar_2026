<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeAppAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Jika belum login, lewati agar ditangani oleh middleware 'auth' bawaan
        if (!$user) {
            return $next($request);
        }

        $appIdSekarang = config('app.core_id');

        // Cek role user di tabel pivot khusus untuk aplikasi ini
        $roleAplikasi = $user->roles()->wherePivot('app_id', $appIdSekarang)->first();

        // Izinkan akses HANYA jika role adalah superadmin atau admin
        if ($roleAplikasi && in_array($roleAplikasi->name, ['superadmin', 'admin'])) {
            return $next($request);
        }

        // Jika selain itu, tampilkan halaman akses ditolak
        return response()->view('pages.auth.forbidden', [], 403);
    }
}
