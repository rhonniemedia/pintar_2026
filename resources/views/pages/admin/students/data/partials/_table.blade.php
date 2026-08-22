{{-- File: resources/views/pages/admin/students/data/partials/_table.blade.php --}}
<div id="students-container"
    hx-get="{{ request()->fullUrl() }}"
    hx-trigger="refreshStudentData from:body"
    hx-swap="outerHTML">

    {{-- ============ 1. DESKTOP TABLE (lg ke atas) ============ --}}
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border">
                    <th class="w-[32%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case">Nama | Jenis Kelamin</div>
                    </th>

                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Nomor Induk Siswa
                        <div class="text-[11px] font-normal normal-case">NIS | NISN</div>
                    </th>

                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Rombongan Belajar
                        <div class="text-[11px] font-normal normal-case">Rombel | Jurusan</div>
                    </th>

                    <th class="w-[8%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Detil | Edit</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                @include('pages.admin.students.data.partials._row-student', ['r' => $r])
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium">Tidak ada data siswa ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS (di bawah lg) ============ --}}
    {{-- Margin negatif (-mx-5) menembus padding kotak putih pembungkus di index --}}
    <div class="lg:hidden divide-y divide-border border-y border-border bg-white -mx-5 mb-5 mt-2">
        @forelse ($students as $r)
        @php
        $nisn = $r->vault->nisn_encrypted ?? '-';
        $rombel = optional($r->activeClassGroup->first())->name ?? '-';
        $jurusan = $r->concentration->name ?? '-';
        $alias = $r->concentration->alias ?? '-';
        @endphp

        <div id="card-student-{{ $r->id }}" class="px-5 py-4 border-border hover:bg-muted/40 active:bg-muted/60 transition-colors">

            <div class="flex items-start gap-3">
                <x-ui.avatar :name="$r->name" :gender="$r->gender" :index="$loop->index" />

                <div class="min-w-0 flex-1">
                    {{-- Bagian Atas: Nama, NIK, dan Badge Rombel --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-foreground text-sm uppercase truncate">
                                {{ $r->name }}
                            </p>
                            <p class="text-xs text-secondary mt-0.5 truncate" title="NIK">
                                {{ $r->vault->nik_encrypted ?? '-' }}
                            </p>
                        </div>

                        {{-- Logika Badge Rombel vs Mengambang disamakan dengan Desktop --}}
                        @if($rombel !== '-')
                        <span class="shrink-0 inline-flex px-2 py-1 rounded-md text-[10px] font-bold bg-primary/10 text-primary">
                            {{ $rombel }}
                        </span>
                        @else
                        <span class="shrink-0 inline-flex px-2 py-1 rounded-md text-[10px] font-bold bg-secondary/10 text-secondary">
                            {{ $alias }}
                        </span>
                        @endif
                    </div>

                    {{-- Bagian Tengah: Detail Akademik (NIS, NISN & Jurusan) --}}
                    <div class="mt-3 border-t border-b border-border divide-y divide-border text-xs">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="hash" class="size-3 text-slate-400"></i>
                                NIS / NISN
                            </p>
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                @if(!empty($r->nis))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-700 whitespace-nowrap">{{ $r->nis }}</span>
                                @endif
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warning/10 text-warning-dark whitespace-nowrap">{{ $nisn }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="book-open" class="size-3 text-slate-400"></i>
                                Jurusan
                            </p>
                            <p class="text-foreground text-right truncate">{{ $jurusan }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Bawah: Dropdown Aksi Alpine.js (Di-copy utuh agar kalkulasi Y akurat) --}}
            <div class="mt-3 flex justify-end">
                <div x-data="{
                        open: false,
                        menuX: 0,
                        menuY: 0,
                        toggle() {
                            if (this.open) {
                                this.open = false;
                                return;
                            }
                            this.open = true;
                            this.$nextTick(() => {
                                const btn = this.$refs.button.getBoundingClientRect();
                                const menu = this.$refs.menu.getBoundingClientRect();
                                const spaceBelow = window.innerHeight - btn.bottom;
                                const spaceAbove = btn.top;
                                
                                const dropUp = spaceBelow < menu.height && spaceAbove > menu.height;
                                
                                this.menuX = btn.right - menu.width;
                                this.menuY = dropUp ? (btn.top - menu.height - 4) : (btn.bottom + 4);
                            });
                        }
                    }"
                    @click.outside="open = false"
                    @scroll.window="open = false"
                    @resize.window="open = false"
                    class="relative inline-block text-left">

                    <button x-ref="button" type="button" @click="toggle()" title="Aksi"
                        class="inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all focus:outline-none cursor-pointer whitespace-nowrap">
                        <span class="text-xs font-medium">Aksi</span>
                        <i data-lucide="chevron-down" class="size-3 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-ref="menu" x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        :style="`top: ${menuY}px; left: ${menuX}px;`"
                        class="fixed z-[9999] w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left origin-top-right">

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Detail</p>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.detail.personal', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="user" class="size-4 text-secondary pointer-events-none"></i> Data Peserta Didik
                        </button>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.detail.guardian', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="users" class="size-4 text-secondary pointer-events-none"></i> Data Orang Tua
                        </button>

                        <div class="my-2 border-t border-border"></div>

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">
                            {{ $rombel !== '-' ? 'Edit & Delete' : 'Hapus' }}
                        </p>

                        @if($rombel !== '-')
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.edit.personal', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="file-pen-line" class="size-4 text-secondary pointer-events-none"></i> Edit Data
                        </button>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.edit.photo', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="camera" class="size-4 text-secondary pointer-events-none"></i> Edit Foto
                        </button>
                        @endif

                        <button type="button" disabled hx-delete="{{ route('admin.students.data.destroy', $r->id) }}" hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML" hx-confirm="Yakin ingin menghapus data {{ $r->name }}? Tindakan ini tidak dapat dibatalkan." class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-error opacity-50 cursor-not-allowed text-left">
                            <i data-lucide="trash-2" class="size-4 pointer-events-none"></i> Hapus Data
                        </button>
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="px-4 py-16 text-center text-secondary">
            <div class="flex flex-col items-center gap-3">
                <i data-lucide="inbox" class="size-10 text-border"></i>
                <p class="font-medium text-sm">Tidak ada data siswa ditemukan</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Memanggil komponen pagination --}}
    <x-ui.pagination :paginator="$students" hxTarget="#students-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>