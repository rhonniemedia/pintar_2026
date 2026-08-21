{{-- File pembungkus tabel riwayat peserta didik --}}
<div id="students-history-container">

    {{-- ============ 1. DESKTOP TABLE ============ --}}
    <div class="hidden lg:block overflow-x-auto pb-4 custom-scrollbar">
        <table class="w-full text-left min-w-[800px]">
            <thead>
                <tr class="border-b border-border bg-slate-50/50">
                    <th class="w-[32%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Nama | Jenis Kelamin</div>
                    </th>

                    <th class="w-[26%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Nomor Induk Siswa
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">NIS | NISN</div>
                    </th>

                    <th class="w-[26%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Rombongan Belajar
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Rombel | Jurusan</div>
                    </th>

                    <th class="w-[16%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Keluar
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Alasan | Tanggal</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                {{-- Memanggil row khusus desktop --}}
                @include('pages.admin.students.history.partials._row-student', ['r' => $r])
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center justify-center gap-3 text-secondary">
                            <div class="size-16 rounded-full bg-slate-100 flex items-center justify-center">
                                <i data-lucide="inbox" class="size-8 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-foreground text-base">Riwayat Kosong</p>
                                <p class="text-sm mt-0.5">Tidak ada riwayat peserta didik yang ditemukan.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS ============ --}}
    <div class="lg:hidden divide-y divide-border bg-white -mx-4 sm:-mx-5">
        @forelse ($students as $r)
        @php
        $student = $r->student;
        $nik = optional(optional($student)->vault)->nik_encrypted ?? '-';
        $nisn = optional(optional($student)->vault)->nisn_encrypted ?? '-';

        $rombel = optional($r->classGroup)->name ?? '-';
        $jurusan = optional(optional($r->classGroup)->concentration)->name ?? '-';

        $mutationReasonMap = [
        'transfer_in' => 'Pindahan Masuk',
        'transfer_out' => 'Pindah Sekolah',
        'dropped_out' => 'Putus Sekolah',
        'deceased' => 'Meninggal Dunia',
        ];

        $exitLabel = $mutationReasonMap[$r->status->value ?? ''] ?? ucfirst(str_replace('_', ' ', $r->status->value ?? '-'));
        $exitDate = $r->mutation_date;
        $exitDateFormatted = $exitDate ? \Illuminate\Support\Carbon::parse($exitDate)->translatedFormat('d M Y') : '-';

        $exitBadgeClass = match ($r->status?->value) {
        'transfer_in' => 'bg-teal-500/10 text-teal-700',
        'transfer_out' => 'bg-blue-500/10 text-blue-600',
        'dropped_out' => 'bg-error/10 text-error',
        'deceased' => 'bg-gray-500/10 text-gray-700',
        default => 'bg-secondary/10 text-secondary',
        };
        @endphp

        <div id="card-student-history-{{ $r->id }}" class="p-4 border-border hover:bg-slate-50/80 transition-colors">

            <div class="flex items-start gap-3">
                <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />

                <div class="min-w-0 flex-1">
                    {{-- Bagian Atas: Nama, NIK, & Label Status --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-foreground text-sm uppercase truncate">
                                {{ $student->name ?? '-' }}
                            </p>
                            <p class="text-xs text-secondary mt-0.5 truncate" title="NIK">
                                {{ $nik }}
                            </p>
                        </div>

                        {{-- Label Status di Sudut Kanan --}}
                        <span class="shrink-0 inline-flex px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider {{ $exitBadgeClass }}">
                            {{ $exitLabel }}
                        </span>
                    </div>

                    {{-- Bagian Bawah: Detail NIS, Rombel & Tanggal Keluar --}}
                    <div class="mt-3 border-t border-border divide-y divide-border text-xs">

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="hash" class="size-3 text-slate-400"></i>
                                NIS / NISN
                            </p>
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                @if(!empty($student->nis))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-700 whitespace-nowrap">{{ $student->nis }}</span>
                                @endif
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-warning/10 text-warning-dark whitespace-nowrap">{{ $nisn }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="book-open" class="size-3 text-slate-400"></i>
                                Rombel
                            </p>
                            <div class="text-right truncate">
                                <p class="text-foreground font-medium truncate">{{ $rombel }}</p>
                                <p class="text-secondary/80 truncate text-[10px]">{{ $jurusan }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="calendar-off" class="size-3 text-slate-400"></i>
                                Tgl Keluar
                            </p>
                            <p class="text-foreground font-medium text-right truncate">
                                {{ $exitDateFormatted }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="px-4 py-16 text-center">
            <div class="flex flex-col items-center justify-center gap-3 text-secondary">
                <div class="size-16 rounded-full bg-slate-100 flex items-center justify-center">
                    <i data-lucide="inbox" class="size-8 text-slate-400"></i>
                </div>
                <div>
                    <p class="font-bold text-foreground text-base">Riwayat Kosong</p>
                    <p class="text-sm mt-0.5">Tidak ada riwayat peserta didik yang ditemukan.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Area Pagination --}}
    <div class="mt-4">
        <x-ui.pagination :paginator="$students" hxTarget="#students-history-container" />
    </div>

    {{-- Re-inisialisasi ikon Lucide untuk HTMX --}}
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>