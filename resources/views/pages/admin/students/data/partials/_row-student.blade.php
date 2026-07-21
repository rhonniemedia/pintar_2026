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
@endphp

<tr id="row-student-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-4 py-4">
        <div class="flex items-center gap-3">
            <x-ui.avatar :name="$r->name" :gender="$r->gender" :index="$loop->index" />
            <div>
                <div class="font-semibold text-foreground text-sm uppercase">{{ $r->name }}</div>
                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                    {{ $r->vault->nik_encrypted ?? '-' }}
                </div>
            </div>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="flex items-center gap-2">
            <span class="inline-block w-20 text-center px-2 py-1 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold">{{ $r->nis ?? '-' }}</span>
            <span class="px-2 py-1 rounded-md bg-warning/10 text-warning-dark text-xs font-bold">{{ $nisn }}</span>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="text-sm font-semibold text-foreground">{{ $rombel }}</div>
        <div class="text-xs text-secondary">{{ $jurusan }}</div>
    </td>

    <td class="px-4 py-4">
        <div
            x-data="{
                open: false,
                dropUp: false,

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

                        this.dropUp = spaceBelow < menu.height && spaceAbove > menu.height;
                    });
                }
            }"
            @click.outside="open = false"
            class="relative inline-block text-left">

            <button
                x-ref="button"
                type="button"
                @click="toggle()"
                title="Aksi"
                class="inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all focus:outline-none cursor-pointer">

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

                :class="dropUp
            ? 'bottom-full mb-1 origin-bottom-right'
            : 'top-full mt-1 origin-top-right'"

                class="absolute right-0 z-20 w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left">

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

                <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Edit & Delete</p>
                <button type="button"
                    @click="open = false"
                    hx-get="{{ route('admin.students.edit.personal', $r->id) }}"
                    hx-target="#modal-container"
                    hx-swap="outerHTML"
                    class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                    <i data-lucide="file-pen-line" class="size-4 text-secondary pointer-events-none"></i> Edit Data
                </button>

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