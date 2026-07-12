@extends('layouts.admin')

@section('title', 'Data Pengguna')
@section('page_title', 'Data Pengguna')
@section('page_subtitle', 'Manajemen Akses & Akun Sistem')

@section('content')

<div class="flex-1 overflow-y-auto p-5 md:p-8 bg-white">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-foreground text-2xl md:text-3xl font-bold mb-1">Manajemen Pengguna</h1>
            <p class="text-secondary text-sm">Kelola akun, email, dan hak akses staf SPMB.</p>
        </div>
        <div class="grid grid-cols-2 gap-2 w-full md:w-auto md:flex md:items-center md:gap-3">
            <button
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin'); window.location.reload();"
                class="flex items-center justify-center gap-2 px-4 py-3 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all duration-300 cursor-pointer bg-white">
                <i id="refresh-icon" data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Segarkan</span>
            </button>
        </div>
    </div>

    {{-- ── Statistik Cards ── --}}
    @include('pages.admin.pengguna.partials._stats-cards')

    {{-- ═══════════════════════════════════════════
         TABEL PENGGUNA
         #users-container = target HTMX (wrapper kosong, tanpa x-data)
         div.user-app-root = tempat x-data Alpine
    ════════════════════════════════════════════ --}}
    <div id="users-container">

        <div class="flex flex-col rounded-2xl border border-border p-6 bg-white gap-6"
            x-data="userApp()">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-lg text-foreground">Daftar Akun Terdaftar</h3>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative">
                        <form action="{{ url()->current() }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">

                            <div class="relative w-full sm:w-auto">
                                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..."
                                    hx-get="{{ url()->current() }}"
                                    hx-include="closest form"
                                    hx-trigger="keyup changed delay:500ms, search"
                                    hx-target="#users-container"
                                    hx-select="#users-container"
                                    hx-swap="innerHTML"
                                    hx-push-url="true"
                                    class="pl-9 pr-9 py-2 rounded-xl border border-border bg-white text-sm focus:ring-1 focus:ring-primary outline-none w-full sm:w-[220px] transition-all" />

                                @if(request('search'))
                                <button type="button"
                                    hx-get="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                    hx-target="#users-container" hx-select="#users-container"
                                    hx-swap="innerHTML" hx-push-url="true"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors focus:outline-none cursor-pointer">
                                    <i data-lucide="x" class="size-4"></i>
                                </button>
                                @endif
                            </div>

                            <div class="relative w-full sm:w-auto">
                                <i data-lucide="filter" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                                <select name="filter_role"
                                    hx-get="{{ url()->current() }}"
                                    hx-include="closest form"
                                    hx-target="#users-container"
                                    hx-select="#users-container"
                                    hx-swap="innerHTML"
                                    hx-push-url="true"
                                    class="w-full sm:w-auto py-2 pl-9 pr-8 rounded-xl border border-border bg-white text-sm focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer font-medium text-gray-700">
                                    <option value="">Semua Role</option>
                                    <option value="superadmin" {{ request('filter_role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                                    <option value="admin" {{ request('filter_role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="verifikator" {{ request('filter_role') === 'verifikator' ? 'selected' : '' }}>Verifikator</option>
                                    <option value="observator" {{ request('filter_role') === 'observator' ? 'selected' : '' }}>Observator</option>
                                    <option value="user" {{ request('filter_role') === 'user' ? 'selected' : '' }}>User (Peserta)</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] border-collapse">
                    <thead class="border-b border-border">
                        <tr>
                            <th class="px-4 py-4 text-left text-sm font-bold text-foreground w-[30%]">
                                Pengguna
                                <p class="font-normal text-xs text-secondary">Nama & Alamat Email</p>
                            </th>
                            <th class="px-4 py-4 text-left text-sm font-bold text-foreground w-[30%]">
                                Hak Akses (Role)
                                <p class="font-normal text-xs text-secondary">Tingkat Otorisasi Sistem</p>
                            </th>
                            <th class="px-4 py-4 text-left text-sm font-bold text-foreground w-[30%]">
                                Bergabung Pada
                                <p class="font-normal text-xs text-secondary">Waktu Pembuatan Akun</p>
                            </th>
                            <th class="px-4 py-4 text-left text-sm font-bold text-foreground w-[10%]">
                                Aksi
                                <p class="font-normal text-xs text-secondary">Kelola Akun</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-white">
                        @forelse($users as $u)
                        @include('pages.admin.pengguna.partials._row-user', ['u' => $u])
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-secondary">
                                    <i data-lucide="users-2" class="size-10 text-border"></i>
                                    <p class="font-medium">Tidak ada data pengguna ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include ('pages.admin.pengguna.partials._pagination', [
            'peserta' => $users,
            'target' => '#users-container'
            ])

            @include ('pages.admin.pengguna.partials._edit-modal')

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function userApp() {
        return {
            activeUser: null,
            editModalOpen: false,
            loading: false,
            errors: [],

            // 1. TAMBAHKAN STATE RESET PASSWORD
            form: {
                name: '',
                email: '',
                role: '',
                reset_password: false
            },

            openEdit(u) {
                this.activeUser = u;
                this.form = {
                    name: u.name,
                    email: u.email,
                    role: u.role,
                    reset_password: false
                };
                this.errors = [];
                this.editModalOpen = true;

                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            closeEditModal() {
                this.editModalOpen = false;
                this.errors = [];
            },

            /* ── LOGIKA HAPUS PENGGUNA (Menggunakan Custom Alert) ── */
            confirmDelete(user) {
                // Memanggil fungsi dari sweet-alert.blade.php
                window.ShowConfirm({
                    title: 'Nonaktifkan Akun?',
                    message: `Apakah Anda yakin ingin menonaktifkan akun ${user.name}? Tindakan ini akan memindahkan data ke arsip.`,
                    confirmText: 'Ya, Nonaktifkan!'
                }, () => {
                    // Callback jika tombol Yes diklik
                    this.executeDelete(user.id);
                });
            },

            async executeDelete(id) {
                try {
                    const res = await fetch(`/admin/user-data/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Accept': 'application/json'
                        }
                    });

                    if (res.ok) {
                        const htmlResponse = await res.text();

                        const row = document.getElementById('user-row-' + id);
                        if (row) row.remove();

                        const statsWrapper = document.createElement('div');
                        statsWrapper.innerHTML = htmlResponse;
                        const newStats = statsWrapper.querySelector('#stats-container');
                        const oldStats = document.getElementById('stats-container');
                        if (oldStats && newStats) {
                            oldStats.outerHTML = newStats.outerHTML;
                        }

                        window.ShowAlert({
                            type: 'success',
                            title: 'Berhasil Dinonaktifkan!',
                            message: 'Akun pengguna telah berhasil dipindahkan ke arsip sistem.',
                            confirmText: 'OK'
                        });

                    } else {
                        const data = await res.json();
                        window.ShowAlert({
                            type: 'error',
                            title: 'Gagal Menghapus!',
                            message: data.message || 'Terjadi kesalahan pada server.'
                        });
                    }
                } catch (e) {
                    console.error(e);
                    window.ShowAlert({
                        type: 'error',
                        title: 'Koneksi Gagal!',
                        message: 'Periksa jaringan internet Anda.'
                    });
                }
            },

            /* ── LOGIKA RESET PASSWORD (Menggunakan Custom Alert) ── */
            confirmResetPassword() {
                window.ShowConfirm({
                    title: 'Reset Password?',
                    message: `Anda akan me-reset password akun ${this.activeUser.name}. Password baru adalah: Password123*. Lanjutkan reset password?`,
                    confirmText: 'Ya, Reset Password!'
                }, () => {
                    // Callback jika tombol Yes diklik
                    this.executeResetPassword();
                });
            },

            async executeResetPassword() {
                try {
                    const res = await fetch(`/admin/user-data/${this.activeUser.id}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    const data = await res.json();

                    if (res.ok && data.status === 'success') {
                        this.closeEditModal();

                        setTimeout(() => {
                            window.ShowAlert({
                                type: 'success',
                                title: 'Password Direset!',
                                message: data.message,
                                confirmText: 'Selesai'
                            });
                        }, 300);
                    } else {
                        window.ShowAlert({
                            type: 'error',
                            title: 'Gagal Mereset!',
                            message: data.message || 'Terjadi kesalahan pada server.'
                        });
                    }
                } catch (e) {
                    console.error(e);
                    window.ShowAlert({
                        type: 'error',
                        title: 'Koneksi Gagal!',
                        message: 'Periksa jaringan internet Anda.'
                    });
                }
            },

            /* ── LOGIKA EDIT PENGGUNA ── */
            async submitEdit() {
                this.loading = true;
                this.errors = [];

                try {
                    const payload = {
                        ...this.form,
                        _token: document.querySelector('meta[name="csrf-token"]')?.content,
                    };

                    const res = await fetch(`/admin/user-data/${this.activeUser.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': payload._token
                        },
                        body: JSON.stringify(payload),
                    });

                    const contentType = res.headers.get("content-type");

                    if (res.ok) {
                        if (contentType && contentType.includes("application/json")) {
                            const data = await res.json();
                            if (data.status === 'info') {
                                this.closeEditModal();
                                setTimeout(() => {
                                    window.ShowAlert({
                                        type: 'info',
                                        title: 'Tidak Ada Perubahan',
                                        message: data.message || 'Data akun sama dengan sebelumnya.',
                                        confirmText: 'Mengerti'
                                    });
                                }, 300);
                            }
                        } else {
                            const htmlResponse = await res.text();

                            const tableWrapper = document.createElement('div');
                            tableWrapper.innerHTML = `<table><tbody>${htmlResponse}</tbody></table>`;
                            const newRow = tableWrapper.querySelector('tr[id^="user-row-"]');

                            const statsWrapper = document.createElement('div');
                            statsWrapper.innerHTML = htmlResponse;
                            const newStats = statsWrapper.querySelector('#stats-container');

                            const targetRow = document.getElementById('user-row-' + this.activeUser.id);
                            if (targetRow && newRow) {
                                targetRow.outerHTML = newRow.outerHTML;
                            }

                            const oldStats = document.getElementById('stats-container');
                            if (oldStats && newStats) {
                                oldStats.outerHTML = newStats.outerHTML;
                            }

                            this.closeEditModal();

                            // 2. SESUAIKAN PESAN SUKSES
                            setTimeout(() => {
                                const pesanSukses = this.form.reset_password ?
                                    'Profil berhasil diperbarui dan Password telah dikembalikan ke default.' :
                                    'Informasi pengguna berhasil diperbarui.';

                                window.ShowAlert({
                                    type: 'success',
                                    title: 'Data Diperbarui!',
                                    message: pesanSukses,
                                    confirmText: 'OK'
                                });
                            }, 300);

                            this.$nextTick(() => {
                                if (window.lucide) lucide.createIcons();
                            });
                        }
                    } else {
                        const data = await res.json();
                        if (res.status === 422 && data.errors) {
                            this.errors = Object.values(data.errors).map(err => err[0]);
                        } else {
                            window.ShowAlert({
                                type: 'error',
                                title: 'Gagal Menyimpan!',
                                message: data.message || 'Terjadi kesalahan pada server.'
                            });
                        }
                    }
                } catch (e) {
                    console.error(e);
                    window.ShowAlert({
                        type: 'error',
                        title: 'Gagal Koneksi!',
                        message: 'Periksa jaringan Anda.'
                    });
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
<script>
    // Setelah HTMX selesai swap & DOM stabil, reinit Alpine + Lucide
    document.addEventListener('htmx:afterSettle', function(e) {
        if (window.lucide) {
            lucide.createIcons();
        }
        const container = document.getElementById('users-container');
        if (container && window.Alpine) {
            Alpine.initTree(container);
        }
    });
</script>
@endpush