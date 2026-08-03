@php
$genderLabel = $r->gender === 'L' ? 'Laki-laki' : 'Perempuan';

$initials = strtoupper(substr($r->name, 0, 2));
$colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
$color = $colors[isset($loop) ? ($loop->index % 4) : 0];

// NISN diakses langsung karena cast 'encrypted' pada model StudentVault
// otomatis mendekripsi nilainya saat properti diambil.
$nisn = $r->vault->nisn_encrypted ?? '-';

$rombel = optional($r->activeClassGroup->first())->name ?? '-';
$jurusan = $r->concentration->name ?? '-';
$alias = $r->concentration->alias ?? '-';
@endphp

<tr id="row-student-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-5 py-4 min-w-[240px]">
        <div class="flex items-center gap-3">
            <x-ui.avatar :name="$r->name" :gender="$r->gender" :index="$loop->index" />
            <div>
                <div class="font-semibold text-foreground text-sm uppercase whitespace-nowrap">{{ $r->name }}</div>
                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                    {{ $r->vault->nik_encrypted ?? '-' }}
                </div>
            </div>
        </div>
    </td>

    {{-- Kolom NIS dan NISN --}}
    <td class="px-5 py-4 min-w-[190px]">
        <div class="flex items-center gap-2">
            {{-- Badge NIS hanya tampil jika data NIS tidak kosong --}}
            @if(!empty($r->nis))
            <span class="inline-block w-24 text-center px-3 py-1.5 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold whitespace-nowrap">{{ $r->nis }}</span>
            @endif

            <span class="px-3 py-1.5 rounded-md bg-warning/10 text-warning-dark text-xs font-bold whitespace-nowrap">{{ $nisn }}</span>
        </div>
    </td>

    {{-- Kolom Rombel dan Jurusan --}}
    <td class="px-5 py-4 min-w-[160px]">
        @if($rombel !== '-')
        {{-- Tampilan untuk Siswa Aktif (Punya Rombel) --}}
        <div class="text-sm font-semibold text-foreground whitespace-nowrap">{{ $rombel }}</div>
        <div class="text-xs text-secondary whitespace-nowrap">{{ $jurusan }}</div>
        @else
        {{-- Tampilan untuk Siswa Mengambang (Hanya Jurusan) --}}
        <div class="text-sm font-semibold text-foreground whitespace-nowrap">{{ $alias }}</div>
        <div class="text-xs text-secondary whitespace-nowrap">{{ $jurusan }}</div>
        @endif
    </td>

    <td class="px-5 py-4 min-w-[120px]">
        <div
            x-data="{
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
                    
                    // Sejajarkan kanan menu dengan kanan tombol
                    this.menuX = btn.right - menu.width;
                    // Tentukan posisi Y (atas atau bawah tombol)
                    this.menuY = dropUp ? (btn.top - menu.height - 4) : (btn.bottom + 4);
                });
            }
        }"
            @click.outside="open = false"
            @scroll.window="open = false"
            @resize.window="open = false"
            class="relative inline-block text-left">

            <button
                x-ref="button"
                type="button"
                @click="toggle()"
                title="Aksi"
                class="inline-flex items-center gap-2 h-9 px-3.5 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all focus:outline-none cursor-pointer whitespace-nowrap">

                <span class="text-sm font-medium">Aksi</span>

                <i
                    data-lucide="chevron-down"
                    class="size-4 transition-transform duration-200"
                    :class="{ 'rotate-180': open }">
                </i>
            </button>

            <div
                x-ref="menu"
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                :style="`top: ${menuY}px; left: ${menuX}px;`"
                class="fixed z-[9999] w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left origin-top-right">

                <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Detail</p>
                <button type="button"
                    @click="open = false"
                    hx-get="{{ route('admin.students.detail.personal', $r->id) }}"
                    hx-target="#modal-container"
                    hx-swap="outerHTML"
                    class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                    <i data-lucide="user" class="size-4 text-secondary pointer-events-none"></i> Data Peserta Didik
                </button>
                <button type="button"
                    @click="open = false"
                    hx-get="{{ route('admin.students.detail.guardian', $r->id) }}"
                    hx-target="#modal-container"
                    hx-swap="outerHTML"
                    class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                    <i data-lucide="users" class="size-4 text-secondary pointer-events-none"></i> Data Orang Tua
                </button>

                <div class="my-2 border-t border-border"></div>

                {{-- Ubah label secara dinamis --}}
                <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">
                    {{ $rombel !== '-' ? 'Edit & Delete' : 'Hapus' }}
                </p>

                {{-- Tampilkan tombol Edit hanya jika siswa memiliki rombel (Bukan siswa mengambang) --}}
                @if($rombel !== '-')
                <button type="button"
                    @click="open = false"
                    hx-get="{{ route('admin.students.edit.personal', $r->id) }}"
                    hx-target="#modal-container"
                    hx-swap="outerHTML"
                    class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                    <i data-lucide="file-pen-line" class="size-4 text-secondary pointer-events-none"></i> Edit Data
                </button>

                <button type="button"
                    @click="open = false"
                    hx-get="{{ route('admin.students.edit.photo', $r->id) }}"
                    hx-target="#modal-container"
                    hx-swap="outerHTML"
                    class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                    <i data-lucide="camera" class="size-4 text-secondary pointer-events-none"></i> Edit Foto
                </button>
                @endif

                <button type="button"
                    disabled
                    hx-delete="{{ route('admin.students.data.destroy', $r->id) }}"
                    hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML"
                    hx-confirm="Yakin ingin menghapus data {{ $r->name }}? Tindakan ini tidak dapat dibatalkan."
                    class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-error opacity-50 cursor-not-allowed">
                    <i data-lucide="trash-2" class="size-4 pointer-events-none"></i> Hapus Data
                </button>
            </div>
        </div>
    </td>
</tr>