<div id="students-container">

    {{-- ============ 1. DESKTOP TABLE ============ --}}
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full min-w-[880px] text-left border-collapse">
            <colgroup>
                <col style="width:32%">
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:8%">
            </colgroup>
            <thead>
                <tr class="border-b border-border">
                    <th class="px-5 py-3 text-sm font-bold text-secondary">Peserta Didik <br><span class="text-[11px] font-normal">Nama | NIK</span></th>
                    <th class="px-5 py-3 text-sm font-bold text-secondary">Kelahiran <br><span class="text-[11px] font-normal">Tempat | Tanggal</span></th>
                    <th class="px-5 py-3 text-sm font-bold text-secondary">Nomor Induk Siswa <br><span class="text-[11px] font-normal">NIS | NISN</span></th>
                    <th class="px-5 py-3 text-sm font-bold text-secondary">Aksi <br><span class="text-[11px] font-normal">Edit | Move</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                @php
                $nik = $r->vault->nik_encrypted ?? '-';
                $tempatLahir = $r->vault->pob_encrypted ?? '-';
                $rawDob = $r->vault->dob_encrypted ?? null;
                $tanggalLahir = ($rawDob && $rawDob !== '-') ? $rawDob : null;
                $nisn = $r->vault->nisn_encrypted ?? '-';

                $initials = strtoupper(substr($r->name, 0, 2));
                $colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
                $color = $colors[$loop->index % 4];
                @endphp

                <tr id="row-student-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="$r->name" :gender="$r->gender" :index="$loop->index" />
                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm truncate">
                                    {{ $r->name }}
                                </div>
                                <div class="text-xs text-secondary truncate">
                                    {{ $nik }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 text-sm">
                        <div class="font-semibold text-foreground truncate">{{ $tempatLahir ?? '-' }}</div>
                        <div class="text-xs text-secondary mt-0.5 flex items-center gap-1.5 overflow-hidden">
                            <i data-lucide="calendar" class="size-3.5 shrink-0"></i>
                            @php
                            $tanggalLahirFormatted = '-';
                            if (!empty($tanggalLahir) && $tanggalLahir !== '-') {
                            try {
                            $tanggalLahirFormatted = \Carbon\Carbon::parse($tanggalLahir)->translatedFormat('d F Y');
                            } catch (\Throwable $e) {
                            $tanggalLahirFormatted = '-';
                            }
                            }
                            @endphp
                            <span class="truncate">{{ $tanggalLahirFormatted }}</span>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-24 text-center px-3 py-1.5 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold whitespace-nowrap">{{ $r->nis ?? '-' }}</span>
                            <span class="px-3 py-1.5 rounded-md bg-warning/10 text-warning-dark text-xs font-bold whitespace-nowrap">{{ $nisn }}</span>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div
                                x-data="{
                                    open: false,
                                    menuX: 0,
                                    menuY: 0,
                                    toggle() {
                                        if (this.open) { this.open = false; return; }
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
                                    class="inline-flex items-center gap-2 h-9 px-3.5 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all focus:outline-none cursor-pointer whitespace-nowrap">
                                    <span class="text-sm font-medium">Aksi</span>
                                    <i data-lucide="chevron-down" class="size-4 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </button>

                                <div x-ref="menu" x-show="open" x-cloak
                                    x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                    :style="`top: ${menuY}px; left: ${menuX}px;`"
                                    class="fixed z-[9999] w-56 rounded-xl border border-border bg-white shadow-xl py-3 flex flex-col text-left origin-top-right">

                                    <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Detail</p>
                                    <button type="button" @click="open = false" hx-get="{{ route('admin.students.detail.personal', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="user" class="size-4 text-secondary pointer-events-none"></i> Data Peserta Didik
                                    </button>
                                    <button type="button" @click="open = false" hx-get="{{ route('admin.students.detail.guardian', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="users" class="size-4 text-secondary pointer-events-none"></i> Data Orang Tua
                                    </button>

                                    <div class="my-2 border-t border-border"></div>

                                    <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Manajemen Data</p>
                                    <button type="button" @click="open = false" hx-get="{{ route('admin.students.edit.personal', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="file-pen-line" class="size-4 text-secondary pointer-events-none"></i> Edit Data
                                    </button>
                                    <button type="button" @click="open = false" hx-get="{{ route('admin.students.edit.photo', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="camera" class="size-4 text-secondary pointer-events-none"></i> Edit Foto
                                    </button>
                                    <button type="button" @click="open = false" hx-get="{{ route('admin.students.group.student.move-form', ['classGroup' => $classGroup->id, 'student' => $r->id]) }}" hx-target="#modal-container" hx-swap="innerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="arrow-right-left" class="size-4 text-secondary pointer-events-none"></i> Pindah Kelas
                                    </button>

                                    {{-- TAMBAHAN MENU CETAK --}}
                                    <div class="my-2 border-t border-border"></div>
                                    <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Cetak Dokumen</p>

                                    {{-- Tombol Cetak Surat Pernyataan --}}
                                    <button type="button" @click="open = false"
                                        hx-get="{{ route('admin.students.data.print.statement-modal', $r->id) }}"
                                        hx-target="#modal-container"
                                        hx-swap="innerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left w-[calc(100%-1rem)]">
                                        <i data-lucide="file-signature" class="size-4 text-secondary pointer-events-none shrink-0"></i> Surat Pernyataan
                                    </button>

                                    {{-- Tombol Cetak Biodata (Siapkan rutenya nanti) --}}
                                    <button type="button" @click="open = false"
                                        hx-get="{{ route('admin.students.data.print.biodata-modal', $r->id) }}"
                                        hx-target="#modal-container"
                                        hx-swap="innerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left w-[calc(100%-1rem)]">
                                        <i data-lucide="printer" class="size-4 text-secondary pointer-events-none shrink-0"></i> Biodata Peserta Didik
                                    </button>
                                    {{-- AKHIR TAMBAHAN --}}

                                    <button type="button" disabled hx-delete="{{ route('admin.students.data.destroy', $r->id) }}" hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML" hx-confirm="Yakin ingin menghapus data {{ $r->name }}? Tindakan ini tidak dapat dibatalkan." class="flex items-center gap-2 mx-2 px-3 py-2 mt-1 rounded-lg text-sm text-error opacity-50 cursor-not-allowed text-left border-t border-border pt-2 rounded-t-none">
                                        <i data-lucide="trash-2" class="size-4 pointer-events-none"></i> Hapus Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-sm text-secondary">Belum ada data siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS ============ --}}
    <div class="lg:hidden divide-y divide-border border-y border-border bg-white -mx-5 mb-5 mt-2">
        @forelse ($students as $r)
        @php
        $nik = $r->vault->nik_encrypted ?? '-';
        $tempatLahir = $r->vault->pob_encrypted ?? '-';
        $rawDob = $r->vault->dob_encrypted ?? null;
        $tanggalLahir = ($rawDob && $rawDob !== '-') ? $rawDob : null;
        $nisn = $r->vault->nisn_encrypted ?? '-';

        $tanggalLahirFormatted = '-';
        if (!empty($tanggalLahir) && $tanggalLahir !== '-') {
        try {
        $tanggalLahirFormatted = \Carbon\Carbon::parse($tanggalLahir)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
        $tanggalLahirFormatted = '-';
        }
        }
        @endphp

        <div id="card-student-{{ $r->id }}" class="px-5 py-4 border-border hover:bg-muted/40 active:bg-muted/60 transition-colors">

            <div class="flex items-start gap-3">
                <x-ui.avatar :name="$r->name" :gender="$r->gender" :index="$loop->index" />

                <div class="min-w-0 flex-1">
                    {{-- Bagian Atas: Nama & NIK --}}
                    <div class="min-w-0">
                        <p class="font-semibold text-foreground text-sm truncate uppercase">
                            {{ $r->name }}
                        </p>
                        <p class="text-xs text-secondary mt-0.5 truncate" title="NIK">
                            {{ $nik }}
                        </p>
                    </div>

                    {{-- Bagian Tengah: Detail --}}
                    <div class="mt-3 border-t border-b border-border divide-y divide-border text-xs">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="hash" class="size-3 text-slate-400"></i>
                                NIS / NISN
                            </p>
                            <div class="flex items-center gap-1.5 justify-end flex-wrap">
                                @if(!empty($r->nis))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-700 whitespace-nowrap">{{ $r->nis }}</span>
                                @endif
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warning/10 text-warning-dark whitespace-nowrap">{{ $nisn }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="calendar" class="size-3 text-slate-400"></i>
                                Kelahiran
                            </p>
                            <div class="text-right truncate">
                                <span class="font-medium text-foreground">{{ $tempatLahir }}</span>
                                <span class="text-secondary/70">, {{ $tanggalLahirFormatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Bawah: Dropdown Aksi --}}
            <div class="mt-3 flex justify-end">
                <div x-data="{
                        open: false,
                        menuX: 0,
                        menuY: 0,
                        toggle() {
                            if (this.open) { this.open = false; return; }
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
                        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        :style="`top: ${menuY}px; left: ${menuX}px;`"
                        class="fixed z-[9999] w-56 rounded-xl border border-border bg-white shadow-xl py-3 flex flex-col text-left origin-top-right">

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Detail</p>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.detail.personal', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="user" class="size-4 text-secondary pointer-events-none"></i> Data Peserta Didik
                        </button>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.detail.guardian', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="users" class="size-4 text-secondary pointer-events-none"></i> Data Orang Tua
                        </button>

                        <div class="my-2 border-t border-border"></div>

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Manajemen Data</p>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.edit.personal', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="file-pen-line" class="size-4 text-secondary pointer-events-none"></i> Edit Data
                        </button>
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.edit.photo', $r->id) }}" hx-target="#modal-container" hx-swap="outerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="camera" class="size-4 text-secondary pointer-events-none"></i> Edit Foto
                        </button>

                        <!-- Pindah Kelas -->
                        <button type="button" @click="open = false" hx-get="{{ route('admin.students.group.student.move-form', ['classGroup' => $classGroup->id, 'student' => $r->id]) }}" hx-target="#modal-container" hx-swap="innerHTML" class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="arrow-right-left" class="size-4 text-secondary pointer-events-none"></i> Pindah Kelas
                        </button>

                        {{-- TAMBAHAN MENU CETAK (MOBILE) --}}
                        <div class="my-2 border-t border-border"></div>
                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Cetak Dokumen</p>

                        {{-- Tombol Cetak Surat Pernyataan --}}
                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.data.print.statement-modal', $r->id) }}"
                            hx-target="#modal-container"
                            hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left w-[calc(100%-1rem)]">
                            <i data-lucide="file-signature" class="size-4 text-secondary pointer-events-none shrink-0"></i> Surat Pernyataan
                        </button>

                        {{-- Tombol Cetak Biodata (Siapkan rutenya nanti) --}}
                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.data.print.biodata-modal', $r->id) }}"
                            hx-target="#modal-container"
                            hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left w-[calc(100%-1rem)]">
                            <i data-lucide="printer" class="size-4 text-secondary pointer-events-none shrink-0"></i> Biodata Peserta Didik
                        </button>
                        {{-- AKHIR TAMBAHAN --}}

                        <button type="button" disabled hx-delete="{{ route('admin.students.data.destroy', $r->id) }}" hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML" hx-confirm="Yakin ingin menghapus data {{ $r->name }}? Tindakan ini tidak dapat dibatalkan." class="flex items-center gap-2 mx-2 px-3 py-2 mt-1 rounded-lg text-sm text-error opacity-50 cursor-not-allowed text-left border-t border-border pt-2 rounded-t-none">
                            <i data-lucide="trash-2" class="size-4 pointer-events-none"></i> Hapus Data
                        </button>
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="px-4 py-16 text-center text-secondary">
            <div class="flex flex-col items-center gap-3">
                <i data-lucide="users" class="size-10 text-border"></i>
                <p class="font-medium text-sm">Belum ada data siswa di rombel ini.</p>
            </div>
        </div>
        @endforelse
    </div>

    <x-ui.pagination :paginator="$students" hxTarget="#students-container" />

    <script>
        (function() {
            // Sorot & scroll otomatis ke siswa yang dituju dari hasil pencarian topbar (?highlight=ID)
            const params = new URLSearchParams(window.location.search);
            const highlightId = params.get('highlight');
            if (!highlightId) return;

            const row = document.getElementById('row-student-' + highlightId);
            const card = document.getElementById('card-student-' + highlightId);
            const target = row || card;
            if (!target) return;

            target.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            const highlightClasses = row ?
                ['bg-primary/10'] :
                ['ring-2', 'ring-primary', 'rounded-xl', 'bg-primary/5'];

            target.classList.add(...highlightClasses, 'transition-colors', 'duration-700');

            setTimeout(() => {
                target.classList.remove(...highlightClasses);
            }, 2500);

            // Bersihkan param 'highlight' dari URL supaya tidak menyala lagi saat refresh/paging
            params.delete('highlight');
            const query = params.toString();
            window.history.replaceState({}, '', window.location.pathname + (query ? '?' + query : ''));
        })();

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>