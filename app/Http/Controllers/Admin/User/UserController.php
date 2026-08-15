<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\CoreApp;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Mengambil id aplikasi "Pintar" saat ini dari tabel core_apps (berdasarkan `code`).
     * Kode app diambil dari config('app.code'), default 'pintar'. Di-cache 1 hari
     * supaya tidak query berulang. UUID di fallback hanya jaring pengaman terakhir
     * (nilai id 'pintar' di tabel core_apps saat ini), bukan sumber kebenaran utama.
     */
    protected function currentAppId(): string
    {
        $appCode = config('app.code', 'pintar');

        return Cache::remember("core_app_id:{$appCode}", now()->addDay(), function () use ($appCode) {
            return CoreApp::where('code', $appCode)->value('id')
                ?? '0fdb4b2f-4a54-4a00-83c5-f5c4dd9b509b';
        });
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role');

        // Ambil ID aplikasi saat ini (SIMKA)
        $appId = config('app.core_id');

        // Karena tabel user_roles tidak memiliki app_id (role bersifat global),
        // kita ambil semua role untuk ditampilkan di dropdown.
        $roles = UserRole::all();

        // Kueri Dasar: Hanya user yang terikat dengan aplikasi SIMKA
        $query = User::query()
            ->whereHas('roles', function ($q) use ($appId) {
                // Memfilter user yang memiliki akses ke aplikasi SIMKA
                // Menggunakan tabel pivot yang benar: user_app_roles
                $q->where('user_app_roles.app_id', $appId);
            })
            ->with([
                'staff.vault',
                // Eager loading HANYA untuk role aplikasi SIMKA
                'roles' => function ($q) use ($appId) {
                    // Menggunakan tabel pivot yang benar: user_app_roles
                    $q->where('user_app_roles.app_id', $appId);
                }
            ]);

        // Filter Pencarian Teks
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhereHas('staff', function ($staffQuery) use ($search) {
                        $staffQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter Berdasarkan Role dari Dropdown
        if ($roleFilter) {
            $query->whereHas('roles', function ($roleQuery) use ($roleFilter, $appId) {
                $roleQuery->where('name', $roleFilter)
                    ->where('user_app_roles.app_id', $appId); // Validasi ganda dengan nama tabel yang benar
            });
        }

        $data = $query->paginate(10)->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('pages.admin.users.partials._table', compact('data', 'search', 'roles'));
        }

        return view('pages.admin.users.index', compact('data', 'search', 'roles'));
    }

    public function editRole(User $user)
    {
        $appId = $this->currentAppId();
        $roles = UserRole::orderBy('name')->get();

        $selectedRoleId = $user->roles()
            ->wherePivot('app_id', $appId)
            ->value('user_roles.id');

        return view('pages.admin.users.partials._modal-edit-role', compact('user', 'roles', 'selectedRoleId'));
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:user_roles,id',
        ]);

        $appId  = $this->currentAppId();
        $roleId = $validated['role_id'];

        DB::transaction(function () use ($user, $appId, $roleId) {
            // Hapus dulu assignment role user ini khusus untuk app saat ini
            DB::table('user_app_roles')
                ->where('user_id', $user->id)
                ->where('app_id', $appId)
                ->delete();

            // Simpan 1 role yang dipilih
            DB::table('user_app_roles')->insert([
                'id'         => (string) Str::uuid(),
                'user_id'    => $user->id,
                'app_id'     => $appId,
                'role_id'    => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->noContent()->header('HX-Trigger', json_encode([
            'close-modal'  => true,
            'refreshTable' => true,
            'showAlert'    => [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => 'Hak akses pengguna berhasil diperbarui.',
            ],
        ]));
    }

    public function editPassword(User $user)
    {
        $phone = optional($user->staff?->vault)->phone_number;

        return view('pages.admin.users.partials._modal-edit-password', compact('user', 'phone'));
    }

    public function updatePassword(User $user)
    {
        $phone = optional($user->staff?->vault)->phone_number;

        if (! $phone) {
            return response()->noContent()->header('HX-Trigger', json_encode([
                'showAlert' => [
                    'icon'  => 'error',
                    'title' => 'Gagal!',
                    'text'  => 'Nomor telepon pengguna belum terdata, kata sandi tidak bisa direset ke default.',
                ],
            ]));
        }

        $defaultPassword = 'MySch' . $phone . '*';

        $user->update([
            // Otomatis di-hash oleh cast 'password' => 'hashed' pada model User
            'password' => $defaultPassword,
        ]);

        return response()->noContent()->header('HX-Trigger', json_encode([
            'close-modal' => true,
            'showAlert'   => [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => 'Kata sandi pengguna berhasil direset ke default.',
            ],
        ]));
    }
}
