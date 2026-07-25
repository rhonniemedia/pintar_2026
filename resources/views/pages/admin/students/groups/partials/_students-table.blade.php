<div id="students-container">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table-fixed">
            <colgroup>
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:10%">
            </colgroup>
            <thead>
                <tr class="border-b border-border">
                    <th class="px-4 py-3 text-sm font-bold text-secondary">Peserta Didik <br><span class="text-[11px] font-normal">Nama | NIK</span></th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">Kelahiran <br><span class="text-[11px] font-normal">Tempat | Tanggal</span></th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">Nomor Induk Siswa <br><span class="text-[11px] font-normal">NIS | NISN</span></th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">Aksi <br><span class="text-[11px] font-normal">Edit | Move</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                @php
                // Mengakses data dari model StudentVault (relasi 'vault') — cast 'encrypted'
                // otomatis mendekripsi nilainya saat properti diambil.
                $nik = $r->vault->nik_encrypted ?? '-';
                $tempatLahir = $r->vault->pob_encrypted ?? '-';
                $tanggalLahir = $r->vault->dob_encrypted ?? '-';
                $nisn = $r->vault->nisn_encrypted ?? '-';

                $initials = strtoupper(substr($r->name, 0, 2));
                $colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
                $color = $colors[$loop->index % 4];
                @endphp

                <tr id="row-student-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
                    <td class="px-4 py-4">
                        <a href="{{ route('admin.students.edit.personal', $r->id) }}" title="Detail" class="flex items-center gap-3 group">

                            {{-- Memanggil komponen Avatar --}}
                            <x-ui.avatar :name="$r->name" :gender="$r->gender" :index="$loop->index" />

                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary group-hover:none transition-colors truncate">{{ $r->name }}</div>

                                {{-- Font NIK dikembalikan ke standar bawaan --}}
                                <div class="text-xs text-secondary truncate">{{ $nik }}</div>
                            </div>
                        </a>
                    </td>

                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground">{{ $tempatLahir }}</div>
                        <div class="text-xs text-secondary">Tanggal: {{ $tanggalLahir }}</div>
                    </td>

                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-20 text-center px-2 py-1 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold">{{ $r->nis ?? '-' }}</span>
                            <span class="px-2 py-1 rounded-md bg-warning/10 text-warning-dark text-xs font-bold">{{ $nisn }}</span>
                        </div>
                    </td>

                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">

                            {{-- DROPDOWN AKSI DENGAN POSISI FIXED --}}
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

                                        // Hitung posisi tepat setelah elemen dirender
                                        this.$nextTick(() => {
                                            const btn = this.$refs.button.getBoundingClientRect();
                                            const menu = this.$refs.menu.getBoundingClientRect();

                                            const spaceBelow = window.innerHeight - btn.bottom;
                                            const spaceAbove = btn.top;

                                            // Jika di bawah sempit tapi di atas luas, drop-up
                                            const dropUp = spaceBelow < menu.height && spaceAbove > menu.height;

                                            // Sejajarkan sisi kanan menu dengan sisi kanan tombol
                                            this.menuX = btn.right - menu.width;

                                            // Tempatkan di atas atau di bawah tombol
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
                                    class="inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all focus:outline-none cursor-pointer">

                                    <span class="text-sm font-medium">Aksi</span>

                                    <i
                                        data-lucide="chevron-down"
                                        class="size-4 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }">
                                    </i>
                                </button>

                                {{--
                                    Perubahan Utama: 
                                    Menggunakan "fixed z-[9999]" dan ":style" dinamis 
                                    sehingga menu keluar dari batas tabel yang overflow 
                                --}}
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
                                    class="fixed z-[9999] w-56 rounded-xl border border-border bg-white shadow-xl py-3 flex flex-col text-left origin-top-right">

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

                                    <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Manajemen Data</p>
                                    <button type="button"
                                        @click="open = false"
                                        hx-get="{{ route('admin.students.edit.personal', $r->id) }}"
                                        hx-target="#modal-container"
                                        hx-swap="outerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="file-pen-line" class="size-4 text-secondary pointer-events-none"></i> Edit Data
                                    </button>

                                    <!-- Edit Photo -->
                                    <button type="button"
                                        @click="open = false"
                                        hx-get="{{ route('admin.students.edit.photo', $r->id) }}"
                                        hx-target="#modal-container"
                                        hx-swap="outerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="camera" class="size-4 text-secondary pointer-events-none"></i> Edit Foto
                                    </button>

                                    <!-- Pindah Kelas -->
                                    <button type="button"
                                        @click="open = false"
                                        hx-get="{{ route('admin.students.group.student.move-form', ['classGroup' => $classGroup->id, 'student' => $r->id]) }}"
                                        hx-target="#modal-container"
                                        hx-swap="innerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="arrow-right-left" class="size-4 text-secondary pointer-events-none"></i> Pindah Kelas
                                    </button>

                                    <!-- Hapus Data -->
                                    <button type="button"
                                        disabled
                                        hx-delete="{{ route('admin.students.data.destroy', $r->id) }}"
                                        hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML"
                                        hx-confirm="Yakin ingin menghapus data {{ $r->name }}? Tindakan ini tidak dapat dibatalkan."
                                        class="flex items-center gap-2 mx-2 px-3 py-2 mt-1 rounded-lg text-sm text-error opacity-50 cursor-not-allowed text-left border-t border-border pt-2 rounded-t-none">
                                        <i data-lucide="trash-2" class="size-4 pointer-events-none"></i> Hapus Data
                                    </button>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-sm text-secondary">Belum ada data siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination yang baru dibuat --}}
    <x-ui.pagination :paginator="$students" hxTarget="#students-container" />
</div>