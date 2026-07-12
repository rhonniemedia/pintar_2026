@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
<div class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-4" x-data="profileApp(@js($userData))">

    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="text-xl font-semibold text-foreground">Profil Saya</h1>
        <p class="text-sm text-secondary mt-0.5">Kelola informasi akun dan keamanan Anda</p>
    </div>

    {{-- ── CARD 1: Profile Header ── --}}
    <div class="bg-white rounded-2xl border border-border px-6 py-5 mb-5">
        <div class="flex items-center gap-5">
            {{-- Avatar --}}
            <div class="relative shrink-0">
                <template x-if="user.photo">
                    <img :src="user.photo" alt="Avatar" class="w-16 h-16 rounded-full object-cover shadow-md border-2 border-white">
                </template>
                <template x-if="!user.photo">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center shadow-md">
                        <span class="text-xl font-bold text-white tracking-wide" x-text="initials"></span>
                    </div>
                </template>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-semibold text-foreground leading-snug" x-text="user.name"></h2>
                <span class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100 px-2.5 py-0.5 rounded-full tracking-wide uppercase">
                    <i data-lucide="shield-check" class="size-3"></i>
                    <span x-text="user.role"></span>
                </span>
            </div>

            {{-- Status --}}
            <div class="shrink-0 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.7)]"></span>
                <span class="text-sm font-medium text-secondary">Aktif</span>
            </div>
        </div>
    </div>

    {{-- ── CARD 2: Upload Foto ── --}}
    <div class="bg-white rounded-2xl border border-border px-6 py-5 mb-5">
        <h5 class="text-xs font-semibold text-secondary uppercase tracking-widest mb-1">Upload Foto</h5>
        <p class="text-sm text-secondary mb-4">Minimal 800×900 px · JPG atau PNG</p>

        {{-- Input hidden --}}
        <input type="file" x-ref="photoInput" @change="uploadPhoto" class="hidden" accept="image/png, image/jpeg, image/jpg">

        <div @click="$refs.photoInput.click()" class="border-2 border-dashed border-gray-200 rounded-xl py-8 flex flex-col items-center gap-3 hover:border-blue-300 hover:bg-blue-50/30 transition-colors duration-200 cursor-pointer relative overflow-hidden">

            <div x-show="photoLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex flex-col items-center justify-center">
                <i data-lucide="loader-2" class="size-6 text-blue-500 animate-spin mb-2"></i>
                <span class="text-sm font-semibold text-blue-700">Mengunggah...</span>
            </div>

            <div class="w-11 h-11 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center">
                <i data-lucide="cloud-upload" class="size-5 text-blue-500"></i>
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-foreground">Klik untuk upload atau drag &amp; drop</p>
                <p class="text-xs text-secondary mt-1 font-mono">Pilih foto langsung dari perangkat</p>
            </div>
        </div>
    </div>

    {{-- ── CARD 3: Informasi Pribadi ── --}}
    <div class="bg-white rounded-2xl border border-border">

        {{-- Card Header --}}
        <div class="flex items-start justify-between px-6 py-5">
            <div>
                <h5 class="text-xs font-semibold text-secondary uppercase tracking-widest">Informasi Pribadi</h5>
                <p class="text-sm text-secondary mt-0.5">Data profil pengguna</p>
            </div>

            {{-- Dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                    class="w-9 h-9 rounded-xl border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-secondary hover:text-foreground transition cursor-pointer">
                    <i data-lucide="more-vertical" class="size-4"></i>
                </button>

                <div x-show="open" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-52 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden z-30">
                    <button @click="openEditData(); open = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-foreground hover:bg-gray-50 transition cursor-pointer">
                        <i data-lucide="pencil" class="size-4 text-blue-500"></i>
                        Edit Data Pengguna
                    </button>
                    <button @click="openEditPassword(); open = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-foreground hover:bg-gray-50 transition cursor-pointer">
                        <i data-lucide="key-round" class="size-4 text-violet-500"></i>
                        Edit Password
                    </button>
                </div>
            </div>
        </div>

        {{-- Fields Grid --}}
        <div class="px-6 pb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <template x-for="field in fields" :key="field.key">
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-widest mb-1.5" x-text="field.label"></label>
                    <input type="text" :value="field.value" readonly
                        class="w-full rounded-lg bg-gray-50 border border-gray-200 text-foreground text-sm px-3.5 py-2.5 cursor-default select-none focus:outline-none" />
                </div>
            </template>
        </div>

    </div>


    {{-- ══════════ MODAL EDIT DATA ══════════ --}}
    <div x-show="editDataOpen" x-cloak
        class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4 backdrop-blur-sm"
        @keydown.escape.window="editDataOpen = false">

        <div x-show="editDataOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="editDataOpen = false"
            class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden flex flex-col max-h-[90vh]">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center">
                        <i data-lucide="pencil" class="size-4 text-blue-600"></i>
                    </div>
                    <h5 class="font-semibold text-foreground text-sm">Edit Data Pengguna</h5>
                </div>
                <button @click="editDataOpen = false"
                    class="w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center text-secondary hover:text-gray-600 transition cursor-pointer">
                    <i data-lucide="x" class="size-4"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4 overflow-y-auto">
                <div x-show="errors.general" class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600 mb-2 font-medium flex gap-2">
                    <i data-lucide="alert-circle" class="size-4 shrink-0"></i>
                    <span x-text="errors.general"></span>
                </div>

                {{-- NAMA LENGKAP --}}
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-widest mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="form.name" placeholder="Masukkan nama lengkap"
                        class="w-full rounded-lg border text-sm px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"
                        :class="errors.name ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'" />
                    <p x-show="errors.name" x-text="errors.name" class="text-xs text-red-500 mt-1"></p>
                </div>

                {{-- EMAIL --}}
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-widest mb-1.5">
                        Email
                    </label>
                    <input type="email" x-model="form.email" readonly
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 text-secondary text-sm px-3.5 py-2.5 focus:outline-none cursor-not-allowed select-none" />
                    <p class="text-[11px] text-secondary mt-1">Email digunakan sebagai identitas login utama dan tidak dapat diubah.</p>
                </div>

                {{-- TELEPON --}}
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-widest mb-1.5">Telepon</label>
                    <input type="text" x-model="form.telephone" placeholder="Nomor telepon (10–15 digit)"
                        class="w-full rounded-lg border text-sm px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"
                        :class="errors.telephone ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'" />
                    <p x-show="errors.telephone" x-text="errors.telephone" class="text-xs text-red-500 mt-1"></p>
                    <p class="text-xs text-secondary mt-1">Format: angka saja, 10–15 digit</p>
                </div>

                {{-- NIP --}}
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-widest mb-1.5">NIP / NIK</label>
                    <input type="text" x-model="form.nip" placeholder="Nomor Induk (Opsional)"
                        class="w-full rounded-lg border text-sm px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"
                        :class="errors.nip ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'" />
                    <p x-show="errors.nip" x-text="errors.nip" class="text-xs text-red-500 mt-1"></p>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 px-6 py-4 border-t border-gray-100 shrink-0">
                <button @click="editDataOpen = false"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition cursor-pointer">
                    Batal
                </button>
                <button @click="submitEditData()" :disabled="loading"
                    class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 transition disabled:opacity-60 flex items-center gap-2 cursor-pointer">
                    <i data-lucide="loader-2" class="size-4 animate-spin" x-show="loading" x-cloak></i>
                    <i data-lucide="save" class="size-4" x-show="!loading"></i>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </div>
    </div>


    {{-- ══════════ MODAL EDIT PASSWORD ══════════ --}}
    <div x-show="editPasswordOpen" x-cloak
        class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4 backdrop-blur-sm"
        @keydown.escape.window="editPasswordOpen = false">

        <div x-show="editPasswordOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="editPasswordOpen = false"
            class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-violet-50 border border-violet-100 flex items-center justify-center">
                        <i data-lucide="key-round" class="size-4 text-violet-600"></i>
                    </div>
                    <h5 class="font-semibold text-foreground text-sm">Edit Password</h5>
                </div>
                <button @click="editPasswordOpen = false"
                    class="w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center text-secondary hover:text-gray-600 transition cursor-pointer">
                    <i data-lucide="x" class="size-4"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div x-show="pwErrors.general" class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600 mb-2 font-medium flex gap-2">
                    <i data-lucide="alert-circle" class="size-4 shrink-0"></i>
                    <span x-text="pwErrors.general"></span>
                </div>

                <template x-for="pwField in passwordFields" :key="pwField.key">
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-widest mb-1.5">
                            <span x-text="pwField.label"></span>
                            <span class="text-red-500"> *</span>
                        </label>
                        <div class="relative">
                            <input
                                :type="pwField.show ? 'text' : 'password'"
                                x-model="pwForm[pwField.key]"
                                :placeholder="pwField.placeholder"
                                class="w-full rounded-lg border text-sm px-3.5 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition"
                                :class="pwErrors[pwField.key] ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'" />
                            <button type="button" @click="pwField.show = !pwField.show"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary hover:text-gray-600 transition cursor-pointer">
                                <i data-lucide="eye-off" class="size-4" x-show="pwField.show" x-cloak></i>
                                <i data-lucide="eye" class="size-4" x-show="!pwField.show"></i>
                            </button>
                        </div>
                        <p x-show="pwErrors[pwField.key]" x-text="pwErrors[pwField.key]" class="text-xs text-red-500 mt-1"></p>
                        <p x-show="pwField.hint" x-text="pwField.hint" class="text-xs text-secondary mt-1"></p>
                    </div>
                </template>

                <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
                    <p class="text-xs font-semibold text-blue-700 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="shield" class="size-3.5"></i>Tips Keamanan
                    </p>
                    <ul class="text-xs text-blue-600/80 space-y-1 list-disc list-inside">
                        <li>Kombinasi huruf besar, kecil, angka &amp; simbol</li>
                        <li>Minimal 8 karakter</li>
                        <li>Jangan gunakan informasi pribadi</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 px-6 py-4 border-t border-gray-100">
                <button @click="editPasswordOpen = false"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition cursor-pointer">
                    Batal
                </button>
                <button @click="submitEditPassword()" :disabled="loading"
                    class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-violet-600 hover:bg-violet-700 active:scale-95 transition disabled:opacity-60 flex items-center gap-2 cursor-pointer">
                    <i data-lucide="loader-2" class="size-4 animate-spin" x-show="loading" x-cloak></i>
                    <i data-lucide="save" class="size-4" x-show="!loading"></i>
                    <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Menerima data dari Controller
    function profileApp(initialData) {
        return {
            user: initialData,

            editDataOpen: false,
            editPasswordOpen: false,
            loading: false,
            photoLoading: false,

            form: {
                name: '',
                email: '',
                telephone: '',
                nip: ''
            },
            errors: {},

            pwForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            pwErrors: {},

            passwordFields: [{
                    key: 'current_password',
                    label: 'Password Lama',
                    placeholder: 'Masukkan password lama',
                    show: false,
                    hint: ''
                },
                {
                    key: 'new_password',
                    label: 'Password Baru',
                    placeholder: 'Masukkan password baru',
                    show: false,
                    hint: 'Minimal 8 karakter'
                },
                {
                    key: 'new_password_confirmation',
                    label: 'Konfirmasi Password Baru',
                    placeholder: 'Masukkan ulang password baru',
                    show: false,
                    hint: ''
                },
            ],

            get initials() {
                if (!this.user.name) return 'U';
                return this.user.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
            },

            get fields() {
                return [{
                        key: 'name',
                        label: 'Nama Lengkap',
                        value: this.user.name || '-'
                    },
                    {
                        key: 'email',
                        label: 'Email',
                        value: this.user.email || '-'
                    },
                    {
                        key: 'telephone',
                        label: 'Telepon',
                        value: this.user.telephone || '-'
                    },
                    {
                        key: 'nip',
                        label: 'NIP',
                        value: this.user.nip || '-'
                    },
                    {
                        key: 'status',
                        label: 'Status',
                        value: this.user.status || '-'
                    },
                    {
                        key: 'role',
                        label: 'Role',
                        value: this.user.role || '-'
                    },
                ];
            },

            openEditData() {
                this.form = {
                    name: this.user.name || '',
                    email: this.user.email || '',
                    telephone: this.user.telephone || '',
                    nip: this.user.nip || ''
                };
                this.errors = {};
                this.editDataOpen = true;
                this.$nextTick(() => lucide.createIcons());
            },

            openEditPassword() {
                // CEK JIKA USER LOGIN VIA GOOGLE
                if (this.user.is_google_user) {
                    window.ShowAlert({
                        type: 'error',
                        title: 'Akses Ditolak!',
                        message: 'Akun Anda terhubung dengan Google. Password tidak dapat diubah secara manual.',
                        confirmText: 'Mengerti'
                    });
                    return; // Hentikan fungsi di sini, modal tidak akan terbuka
                }

                // Jika bukan user Google, jalankan perintah buka modal seperti biasa
                this.pwForm = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: ''
                };
                this.passwordFields.forEach(f => f.show = false);
                this.pwErrors = {};
                this.editPasswordOpen = true;
                this.$nextTick(() => lucide.createIcons());
            },

            validateData() {
                this.errors = {};
                if (!this.form.name) this.errors.name = 'Nama lengkap wajib diisi.';
                if (!this.form.email) {
                    this.errors.email = 'Email wajib diisi.';
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                    this.errors.email = 'Format email tidak valid.';
                }
                if (this.form.telephone && !/^\d{10,15}$/.test(this.form.telephone)) {
                    this.errors.telephone = 'Nomor telepon harus 10–15 digit angka.';
                }
                return Object.keys(this.errors).length === 0;
            },

            validatePassword() {
                this.pwErrors = {};
                if (!this.pwForm.current_password) this.pwErrors.current_password = 'Password lama wajib diisi.';
                if (!this.pwForm.new_password) {
                    this.pwErrors.new_password = 'Password baru wajib diisi.';
                } else if (this.pwForm.new_password.length < 8) {
                    this.pwErrors.new_password = 'Minimal 8 karakter.';
                }
                if (this.pwForm.new_password !== this.pwForm.new_password_confirmation) {
                    this.pwErrors.new_password_confirmation = 'Konfirmasi password tidak cocok.';
                }
                return Object.keys(this.pwErrors).length === 0;
            },

            async submitEditData() {
                if (!this.validateData()) return;
                this.loading = true;

                try {
                    const res = await fetch(`/admin/profile/data`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.form),
                    });

                    const data = await res.json();

                    if (res.ok && data.status === 'success') {
                        // Update UI tanpa perlu reload halaman
                        this.user.name = this.form.name;
                        this.user.email = this.form.email;
                        this.user.telephone = this.form.telephone;
                        this.user.nip = this.form.nip;

                        this.editDataOpen = false;

                        setTimeout(() => {
                            window.ShowAlert({
                                type: 'success',
                                title: 'Berhasil!',
                                message: data.message
                            });
                        }, 300);
                    } else if (res.status === 422) {
                        for (const key in data.errors) {
                            this.errors[key] = data.errors[key][0];
                        }
                    } else {
                        this.errors.general = data.message || 'Terjadi kesalahan pada server.';
                    }
                } catch (e) {
                    this.errors.general = 'Gagal terhubung ke server.';
                } finally {
                    this.loading = false;
                }
            },

            async submitEditPassword() {
                if (!this.validatePassword()) return;
                this.loading = true;

                try {
                    const res = await fetch(`/admin/profile/password`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.pwForm),
                    });

                    const data = await res.json();

                    if (res.ok && data.status === 'success') {
                        this.editPasswordOpen = false;
                        setTimeout(() => {
                            window.ShowAlert({
                                type: 'success',
                                title: 'Password Diperbarui!',
                                message: data.message
                            });
                        }, 300);
                    } else if (res.status === 422) {
                        for (const key in data.errors) {
                            this.pwErrors[key] = data.errors[key][0];
                        }
                    } else {
                        this.pwErrors.general = data.message || 'Terjadi kesalahan pada server.';
                    }
                } catch (e) {
                    this.pwErrors.general = 'Gagal terhubung ke server.';
                } finally {
                    this.loading = false;
                }
            },

            async uploadPhoto(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    window.ShowAlert({
                        type: 'error',
                        title: 'File Terlalu Besar!',
                        message: 'Ukuran foto maksimal adalah 2MB.'
                    });
                    event.target.value = '';
                    return;
                }

                this.photoLoading = true;
                const formData = new FormData();
                formData.append('photo', file);

                try {
                    const res = await fetch(`/admin/profile/photo`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.status === 'success') {
                        // Perbarui foto di UI seketika
                        this.user.photo = data.photo_url;
                        window.ShowAlert({
                            type: 'success',
                            title: 'Foto Diperbarui!',
                            message: data.message
                        });
                    } else {
                        window.ShowAlert({
                            type: 'error',
                            title: 'Gagal Mengunggah',
                            message: data.errors?.photo?.[0] || data.message || 'Terjadi kesalahan pada sistem.'
                        });
                    }
                } catch (e) {
                    window.ShowAlert({
                        type: 'error',
                        title: 'Gagal Koneksi',
                        message: 'Periksa jaringan internet Anda.'
                    });
                } finally {
                    this.photoLoading = false;
                    event.target.value = '';
                }
            },

            init() {
                this.$nextTick(() => lucide.createIcons());
            },
        }
    }
</script>
@endpush