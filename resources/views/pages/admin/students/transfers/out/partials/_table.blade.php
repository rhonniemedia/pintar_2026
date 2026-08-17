<div id="mutasi-keluar-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.students.transfer.out.index') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}"
    hx-target="#mutasi-keluar-container"
    hx-swap="outerHTML">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table-fixed">
            <colgroup>
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:10%">
            </colgroup>
            <thead>
                <tr class="border-b border-border bg-gray-50/30">
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case">Nama | Nomor Induk Kependudukan</div>
                    </th>

                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Tujuan Mutasi
                        <div class="text-[11px] font-normal normal-case">Sekolah Tujuan / Alasan</div>
                    </th>

                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Ditinggalkan
                        <div class="text-[11px] font-normal normal-case">Rombel & Tanggal Keluar</div>
                    </th>

                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Detail | Batal</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $r)
                @php
                $student = $r->student;
                $initials = strtoupper(substr($student->name ?? 'U', 0, 2));
                $colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
                $color = $colors[isset($loop) ? ($loop->index % 4) : 0];

                // Tambahkan baris ini untuk memanggil NISN
                $nisn = optional(optional($student)->vault)->nisn_encrypted ?? '-';

                @endphp
                <tr class="border-b border-border hover:bg-muted/50 transition-colors">

                    {{-- Kolom Peserta Didik --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3 group">
                            {{-- Memanggil komponen Avatar --}}
                            <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />

                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                    {{ $student->name ?? '-' }}
                                </div>

                                {{-- Menampilkan NIK dengan format teks standar --}}
                                <div class="text-xs text-secondary mt-0.5">
                                    {{ optional(optional($student)->vault)->nik_encrypted ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom Tujuan Mutasi --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground truncate pr-2 font-medium" title="{{ $r->destination_school ?? $r->mutation_reason_label }}">
                            {{ $r->destination_school ?? ($r->mutation_reason_label ?? 'Tidak diketahui') }}
                        </div>
                        @if ($r->is_transfer_out)
                        <div class="text-xs text-secondary truncate" title="{{ $r->notes }}">
                            {{ $r->notes ?? 'Tidak ada catatan' }}
                        </div>
                        @endif
                    </td>

                    {{-- Kolom Rombel Terakhir --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground font-medium truncate">
                            {{ optional($r->classGroup)->name ?? '-' }}
                        </div>
                        <div class="mt-1 flex items-center gap-1 text-[11px] text-secondary">
                            <i data-lucide="calendar-off" class="w-3 h-3"></i>
                            <span>
                                {{ \Carbon\Carbon::parse($r->mutation_date)->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </td>

                    {{-- Kolom Aksi --}}
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
                                        hx-get="{{ route('admin.students.detail.personal', $student->id) }}"
                                        hx-target="#modal-container"
                                        hx-swap="outerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="user" class="size-4 text-secondary pointer-events-none"></i> Data Peserta Didik
                                    </button>
                                    <button type="button"
                                        @click="open = false"
                                        hx-get="{{ route('admin.students.detail.guardian', $student->id) }}"
                                        hx-target="#modal-container"
                                        hx-swap="outerHTML"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="users" class="size-4 text-secondary pointer-events-none"></i> Data Orang Tua
                                    </button>

                                    <div class="my-2 border-t border-border"></div>

                                    {{-- Cek apakah ada surat keterangan terkait mutasi --}}
                                    @if ($r->latest_letter)
                                    <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">
                                        Cetak
                                    </p>

                                    <!-- Cetak Surat Keterangan -->
                                    <a href="{{ route('admin.students.letters.download', $r->latest_letter->id) }}"
                                        target="_blank"
                                        class="flex items-center gap-2 mx-2 px-3 py-2 mt-1 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                                        <i data-lucide="file-text" class="size-4 pointer-events-none"></i>
                                        Surat Keterangan
                                    </a>
                                    @endif

                                    <div class="my-2 border-t border-border"></div>

                                    <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Manajemen Data</p>
                                    <!-- Hapus Data -->
                                    <button type="button"
                                        disabled
                                        hx-delete="{{ route('admin.students.data.destroy', $student->id) }}"
                                        hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML"
                                        hx-confirm="Yakin ingin menghapus data {{ $student->name }}? Tindakan ini tidak dapat dibatalkan."
                                        class="flex items-center gap-2 mx-2 px-3 py-2 mt-1 rounded-lg text-sm text-error opacity-50 cursor-not-allowed text-left rounded-t-none">
                                        <i data-lucide="trash-2" class="size-4 pointer-events-none"></i> Hapus Data
                                    </button>
                                </div>
                            </div>

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="archive-restore" class="size-10 text-border"></i>
                            <p class="font-medium text-sm">Belum ada data siswa mutasi keluar pada semester ini</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination --}}
    <x-ui.pagination :paginator="$data" hxTarget="#mutasi-keluar-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>