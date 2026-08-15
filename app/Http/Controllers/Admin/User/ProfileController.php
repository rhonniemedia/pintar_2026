<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $userData = [
            'name'      => $user->name ?? $user->staff->name ?? 'Pengguna',
            'email'     => $user->staff->vault->email ?? $user->email ?? null,
            'telephone' => $user->staff->vault->phone_number ?? null,
            'nip'       => $user->staff->vault->nip ?? null,
            'role'      => $user->roles->first()->name ?? 'Administrator',
            // Perbaikan: Ambil foto dari relasi staff
            'photo'     => $user->staff?->photo ? asset('storage/' . $user->staff->photo) : null,
        ];

        return view('pages.admin.profile.index', compact('userData'));
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();
        $staff = $user->staff;

        // Perbaikan: Simpan foto ke relasi staff
        if ($staff) {
            if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }

            $path = $request->file('photo')->store('profile-photos', 'public');
            $staff->update(['photo' => $path]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Foto profil berhasil diperbarui.',
            'photo_url' => asset('storage/' . ($path ?? '')),
        ]);
    }

    public function editData(Request $request)
    {
        $user = $request->user();
        return view('pages.admin.profile.partials._modal-edit-data', compact('user'));
    }

    public function updateData(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'telephone' => 'required|numeric|digits_between:10,15',
        ], [
            'telephone.required'       => 'Nomor telepon wajib diisi.',
            'telephone.numeric'        => 'Nomor telepon hanya boleh berisi angka.',
            'telephone.digits_between' => 'Nomor telepon harus terdiri dari 10 hingga 15 digit.',
        ]);

        if ($validator->fails()) {
            /** @var \Illuminate\View\View $view */
            $view = view('pages.admin.profile.partials._modal-edit-data', compact('user'));

            return $view->withErrors($validator);
        }

        // PASTIKAN menyimpan data ke relasi vault milik staff
        if ($user->staff && $user->staff->vault) {
            $user->staff->vault->update([
                // Pastikan nama propertinya sesuai dengan model Anda (phone_number atau telephone)
                'phone_number' => $request->telephone,
            ]);
        }

        return response('')->header('HX-Trigger', json_encode([
            'profileUpdated' => [
                'telephone' => $request->telephone,
                'message'   => 'Data profil berhasil diperbarui.'
            ]
        ]));
    }

    public function editPassword(Request $request)
    {
        $user = $request->user();
        return view('pages.admin.profile.partials._modal-edit-password', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password'          => ['required', 'current_password'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required'         => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi tidak cocok dengan data kami.',
            'new_password.required'             => 'Kata sandi baru wajib diisi.',
            'new_password.min'                  => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi sandi tidak cocok.',
        ]);

        if ($validator->fails()) {
            /** @var \Illuminate\View\View $view */
            $view = view('pages.admin.profile.partials._modal-edit-password', compact('user'));

            return $view->withErrors($validator);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response('')->header('HX-Trigger', json_encode([
            'passwordUpdated' => [
                'message' => 'Kata sandi Anda berhasil diperbarui.'
            ]
        ]));
    }
}
