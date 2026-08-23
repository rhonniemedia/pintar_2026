<div class="flex items-center justify-between w-full h-[90px] shrink-0 border-b border-border bg-white px-5 md:px-8 sticky top-0 z-50">

    <button
        @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden size-11 flex items-center justify-center rounded-xl ring-1 ring-border hover:ring-primary transition-all duration-300 cursor-pointer"
        :aria-label="sidebarOpen ? 'Tutup sidebar' : 'Buka sidebar'">

        <i data-lucide="menu" x-show="!sidebarOpen" class="size-6 text-foreground"></i>
        <i data-lucide="x" x-show="sidebarOpen" x-cloak class="size-6 text-foreground"></i>
    </button>

    {{-- Brand --}}
    <div class="min-w-0 flex-1 ml-3 lg:ml-0">
        <h2 class="text-lg md:text-2xl font-bold text-foreground leading-tight">
            PINTAR
        </h2>

        <p class="hidden md:block text-xs text-secondary leading-tight truncate">
            Platform Informasi Kesiswaan Terintegrasi
        </p>
    </div>
    <div class="flex items-center gap-3">
        {{-- Pemilih Semester Global (berlaku di semua halaman admin) --}}
        <div
            class="flex items-center md:pl-3 border-border relative"
            x-data="{ openPeriod: false }"
            @click.outside="openPeriod = false"
            @keydown.escape.window="openPeriod = false">

            <button
                @click="openPeriod = !openPeriod"
                class="relative flex items-center justify-center md:justify-start gap-2 size-11 md:w-auto md:h-11 md:px-3 rounded-xl ring-1 ring-border hover:ring-primary transition-all duration-300 cursor-pointer"
                aria-label="Pilih semester">

                {{-- Ikon diperbesar sedikit di mobile (size-5) agar sama dengan tombol search, kembali ke size-4 di desktop --}}
                <i data-lucide="calendar-clock" class="size-5 md:size-4 text-secondary shrink-0"></i>

                {{-- Teks disembunyikan di mobile, dimunculkan di layar md ke atas --}}
                <span class="hidden md:block text-sm font-semibold text-foreground truncate max-w-[160px]">
                    {{ $currentAcademicPeriod?->label ?? 'Belum ada semester' }}
                </span>

                {{-- Bagian chevron disembunyikan di mobile --}}
                <i data-lucide="chevron-down"
                    class="hidden md:block size-4 text-secondary shrink-0 transition-transform duration-300"
                    :class="openPeriod ? 'rotate-180' : ''"></i>

                @if($isAcademicPeriodOverridden ?? false)
                <span
                    class="absolute -top-1 -right-1 size-2.5 rounded-full bg-amber-500 ring-2 ring-white"
                    title="Sedang melihat semester lain dari semester aktif"></span>
                @endif
            </button>

            <div
                x-show="openPeriod"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 md:right-0 top-full mt-2 w-[260px] md:w-72 bg-white rounded-2xl shadow-2xl border border-border z-[100]"
                style="display: none">
                <div class="p-2">
                    <p class="px-2 py-1.5 text-xs font-bold text-secondary uppercase tracking-wide">
                        Pilih Semester
                    </p>

                    @if($isAcademicPeriodOverridden ?? false)
                    <div class="mx-1 mb-2 px-2.5 py-2 rounded-lg bg-amber-500/10 text-amber-700 text-xs flex items-start gap-2">
                        <i data-lucide="alert-triangle" class="size-3.5 mt-0.5 shrink-0"></i>
                        <span>Anda sedang melihat semester lain, bukan semester aktif Data Master.</span>
                    </div>
                    @endif

                    <div class="max-h-[60vh] overflow-y-auto">
                        @forelse(($academicPeriodOptions ?? []) as $option)
                        <form method="POST" action="{{ route('admin.academic-period.update') }}" class="w-full m-0 p-0" hx-boost="false">
                            @csrf
                            <input type="hidden" name="semester_id" value="{{ $option->id }}">
                            <button
                                type="submit"
                                class="w-full flex items-center justify-between gap-2 px-2 py-2 rounded-md text-sm transition-colors text-left cursor-pointer
                                    {{ $currentAcademicPeriod && $currentAcademicPeriod->id === $option->id ? 'bg-primary/10 text-primary font-bold' : 'text-secondary hover:bg-muted hover:text-primary' }}">
                                <span class="truncate">{{ $option->label }}</span>
                                @if($option->status === 'active')
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 shrink-0">
                                    Aktif
                                </span>
                                @endif
                            </button>
                        </form>
                        @empty
                        <p class="px-2 py-2 text-sm text-secondary">Belum ada data semester.</p>
                        @endforelse
                    </div>

                    @if($isAcademicPeriodOverridden ?? false)
                    <hr class="my-1 border-border" />
                    <form method="POST" action="{{ route('admin.academic-period.reset') }}" class="w-full m-0 p-0" hx-boost="false">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2 px-2 py-2 rounded-md text-sm text-secondary hover:bg-muted hover:text-primary transition-colors text-left cursor-pointer">
                            <i data-lucide="rotate-ccw" class="size-4 shrink-0"></i>
                            <span class="truncate">Kembali ke Semester Aktif</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pencarian Global Siswa (nama / NIS / NIK / NISN) --}}
        <div
            x-data="{
                isOpen: false,
                query: '',
                loading: false,
                results: [],
                debounceTimer: null,
                open() {
                    this.isOpen = true;
                    this.$nextTick(() => this.$refs.searchInput.focus());
                },
                close() {
                    this.isOpen = false;
                    this.query = '';
                    this.results = [];
                    this.loading = false;
                    clearTimeout(this.debounceTimer);
                },
                onInput() {
                    clearTimeout(this.debounceTimer);
                    const term = this.query.trim();
                    if (term.length < 2) {
                        this.results = [];
                        this.loading = false;
                        return;
                    }
                    this.loading = true;
                    this.debounceTimer = setTimeout(() => {
                        fetch(`{{ route('admin.students.search') }}?q=${encodeURIComponent(term)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => { this.results = data.results ?? []; })
                            .catch(() => { this.results = []; })
                            .finally(() => { this.loading = false; });
                    }, 350);
                },
                initials(name) {
                    if (!name) return '?';
                    const parts = name.trim().split(' ');
                    return (parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '');
                },
                badgeLabel(target) {
                    return {
                        rombel: 'Rombel',
                        data: 'Mengambang',
                        mutasi: 'Mutasi',
                        riwayat: 'Riwayat',
                        lulus: 'Alumni',
                    }[target] ?? 'Mengambang';
                },
                badgeClass(target) {
                    return {
                        rombel: 'bg-primary/10 text-primary',
                        data: 'bg-muted text-secondary',
                        mutasi: 'bg-amber-500/10 text-amber-700',
                        riwayat: 'bg-slate-500/10 text-slate-700',
                        lulus: 'bg-emerald-500/10 text-emerald-700',
                    }[target] ?? 'bg-muted text-secondary';
                }
            }"
            @keydown.escape.window="close()">

            <button
                @click="open()"
                class="size-11 flex items-center justify-center rounded-xl ring-1 ring-border hover:ring-primary transition-all duration-300 cursor-pointer"
                aria-label="Cari siswa">
                <i data-lucide="search" class="size-5 text-secondary"></i>
            </button>

            <template x-teleport="body">
                <div
                    x-show="isOpen"
                    x-cloak
                    class="fixed inset-0 z-[200] flex items-start justify-center pt-20 md:pt-28 px-4"
                    style="display: none">

                    <div class="absolute inset-0 bg-black/40" @click="close()"></div>

                    <div
                        class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-border overflow-hidden"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="flex items-center gap-3 px-4 py-3 border-b border-border">
                            <i data-lucide="search" class="size-5 text-secondary shrink-0"></i>
                            <input
                                type="text"
                                x-ref="searchInput"
                                x-model="query"
                                @input="onInput()"
                                placeholder="Cari nama, NIS, NIK, atau NISN siswa..."
                                class="flex-1 border-0 focus:ring-0 text-sm text-foreground placeholder:text-secondary outline-none" />
                            <button
                                @click="close()"
                                class="size-8 flex items-center justify-center rounded-lg hover:bg-muted transition-colors cursor-pointer"
                                aria-label="Tutup pencarian">
                                <i data-lucide="x" class="size-4 text-secondary"></i>
                            </button>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="loading">
                                <div class="px-4 py-6 text-center text-sm text-secondary">Mencari...</div>
                            </template>

                            <template x-if="!loading && query.trim().length > 0 && query.trim().length < 2">
                                <div class="px-4 py-6 text-center text-sm text-secondary">Ketik minimal 2 karakter.</div>
                            </template>

                            <template x-if="!loading && query.trim().length >= 2 && results.length === 0">
                                <div class="px-4 py-6 text-center text-sm text-secondary">
                                    Tidak ada siswa yang cocok dengan &quot;<span x-text="query"></span>&quot;.
                                </div>
                            </template>

                            <template x-if="!loading && query.trim().length === 0">
                                <div class="px-4 py-6 text-center text-sm text-secondary">Mulai ketik untuk mencari siswa.</div>
                            </template>

                            <template x-for="student in results" :key="student.id">
                                <a
                                    :href="student.url"
                                    class="flex items-center gap-3 px-4 py-3 hover:bg-muted transition-colors border-b border-border last:border-0">
                                    <div class="size-9 rounded-full bg-primary flex items-center justify-center shrink-0 overflow-hidden">
                                        <template x-if="student.photo_url">
                                            <img :src="student.photo_url" alt="Avatar" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!student.photo_url">
                                            <span class="text-white font-black text-xs" x-text="initials(student.name)"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-sm text-foreground truncate" x-text="student.name"></p>
                                        <p class="text-xs text-secondary truncate">
                                            <span x-text="student.nis ?? '-'"></span>
                                            <template x-if="student.concentration">
                                                <span> &middot; <span x-text="student.concentration"></span></span>
                                            </template>
                                            <template x-if="student.rombel">
                                                <span> &middot; <span x-text="student.rombel"></span></span>
                                            </template>
                                        </p>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0"
                                        :class="badgeClass(student.target)"
                                        x-text="badgeLabel(student.target)">
                                    </span>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div
            class="hidden md:flex items-center gap-3 pl-3 border-l border-border relative"
            x-data="{ openProfile: false }"
            @click.outside="openProfile = false">

            {{-- Logic inisial: Ambil nama asli dan cek foto dari relasi staff --}}
            @php
            $user = auth()->user();
            $rawName = $user->staff?->name ?? 'Admin';
            $nameParts = explode(' ', trim($rawName));
            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

            // Variabel untuk nama lengkap bergelar
            $fullNameWithTitle = $user->staff?->name_with_title ?? $rawName;

            // Variabel url foto profil
            $photoUrl = $user->staff?->photo ? asset('storage/' . $user->staff->photo) : null;
            @endphp

            {{-- Avatar Header (Inisial atau Foto) --}}
            <div
                class="size-11 rounded-full bg-primary flex items-center justify-center ring-2 ring-border cursor-pointer shrink-0 overflow-hidden"
                @click="openProfile = !openProfile">
                @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="Avatar" class="w-full h-full object-cover">
                @else
                <span class="text-white font-black text-sm">{{ $initials }}</span>
                @endif
            </div>

            <div
                x-show="openProfile"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-border z-[100]"
                style="display: none">
                <div class="p-2">

                    {{-- Info User Dropdown --}}
                    <div class="flex items-center gap-3 px-2 py-2 mb-1">
                        <div class="size-9 rounded-full bg-primary flex items-center justify-center shrink-0 overflow-hidden">
                            @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                            <span class="text-white font-black text-xs">{{ $initials }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-foreground truncate" title="{{ $fullNameWithTitle }}">
                                {{ $fullNameWithTitle }}
                            </p>
                            <p class="text-xs text-secondary truncate">
                                {{ $user->staff?->vault?->email ?? $user->username ?? 'Administrator' }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-1 border-border" />

                    {{-- Tautan telah diperbarui ke rute profil --}}
                    <a href="{{ route('admin.profile.index') }}"
                        class="flex items-center gap-2 px-2 py-2 rounded-md text-sm text-secondary hover:bg-muted hover:text-primary transition-colors">
                        <i data-lucide="user" class="size-4"></i> Profil Saya
                    </a>

                    <a href="#"
                        class="flex items-center gap-2 px-2 py-2 rounded-md text-sm text-secondary hover:bg-muted hover:text-primary transition-colors">
                        <i data-lucide="settings" class="size-4"></i> Pengaturan Akun
                    </a>

                    <hr class="my-1 border-border" />

                    {{-- Form Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="w-full m-0 p-0">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2 px-2 py-2 rounded-md text-sm text-error hover:bg-error/10 transition-colors text-left cursor-pointer">
                            <i data-lucide="log-out" class="size-4"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>