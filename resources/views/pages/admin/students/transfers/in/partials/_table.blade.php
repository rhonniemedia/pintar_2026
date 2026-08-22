<div id="mutasi-masuk-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.students.transfer.in.index') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}"
    hx-target="#mutasi-masuk-container"
    hx-swap="outerHTML">

    {{-- ============ 1. DESKTOP TABLE ============ --}}
    <div class="hidden lg:block overflow-x-auto">
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
                        <div class="text-[11px] font-normal normal-case">Nama | NIK</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Sekolah Asal
                        <div class="text-[11px] font-normal normal-case">Nama Sekolah | Daerah</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Diterima
                        <div class="text-[11px] font-normal normal-case">Rombel | Tanggal</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Detail | Hapus</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $r)
                @php
                $student = $r->student;
                $nik = optional(optional($student)->vault)->nik_encrypted ?? '-';
                $nisn = optional(optional($student)->vault)->nisn_encrypted ?? '-';
                $initials = strtoupper(substr($student->name ?? 'U', 0, 2));

                $mutationDateFormatted = \Carbon\Carbon::parse($r->mutation_date)->translatedFormat('d M Y');
                $className = optional($r->classGroup)->name ?? '-';
                $originSchool = $r->origin_school ?? '-';
                $originCity = $r->origin_school_city ?? '-';

                $colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
                $color = $colors[isset($loop) ? ($loop->index % 4) : 0];
                @endphp
                <tr id="row-mutasi-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">

                    {{-- Kolom Peserta Didik --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3 group">
                            <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />

                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                    {{ $student->name ?? '-' }}
                                </div>
                                <div class="text-xs text-secondary mt-0.5 truncate">
                                    {{ $nik }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom Sekolah Asal --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground font-medium truncate pr-2" title="{{ $originSchool }}">
                            {{ $originSchool }}
                        </div>
                        <div class="text-xs text-secondary truncate">
                            {{ $originCity }}
                        </div>
                    </td>

                    {{-- Kolom Diterima --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground font-medium truncate">
                            {{ $className }}
                        </div>
                        <div class="mt-1 flex items-center gap-1.5 text-[11px] text-secondary">
                            <i data-lucide="calendar" class="size-3.5"></i>
                            <span>{{ $mutationDateFormatted }}</span>
                        </div>
                    </td>

                    {{-- Kolom Aksi --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2" x-data="{}">
                            <button type="button" title="Detail / Edit"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                                <i data-lucide="file-pen-line" class="size-4 pointer-events-none"></i>
                            </button>
                            <button type="button" title="Hapus"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-error hover:bg-error/10 hover:border-error/30 transition-all cursor-pointer">
                                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
                            </button>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium text-sm">Belum ada data siswa pindahan pada semester ini</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS ============ --}}
    <div class="lg:hidden divide-y divide-border border-y border-border bg-white -mx-5 mb-5 mt-2">
        @forelse ($data as $r)
        @php
        $student = $r->student;
        $nik = optional(optional($student)->vault)->nik_encrypted ?? '-';
        $mutationDateFormatted = \Carbon\Carbon::parse($r->mutation_date)->translatedFormat('d M Y');
        $className = optional($r->classGroup)->name ?? '-';
        $originSchool = $r->origin_school ?? '-';
        $originCity = $r->origin_school_city ?? '-';
        @endphp

        <div id="card-mutasi-{{ $r->id }}" class="px-5 py-4 border-border hover:bg-muted/40 active:bg-muted/60 transition-colors">

            <div class="flex items-start gap-3">
                <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />

                <div class="min-w-0 flex-1">
                    {{-- Bagian Atas: Nama & NIK --}}
                    <div class="min-w-0">
                        <p class="font-semibold text-foreground text-sm uppercase truncate">
                            {{ $student->name ?? '-' }}
                        </p>
                        <p class="text-xs text-secondary mt-0.5 truncate" title="NIK">
                            {{ $nik }}
                        </p>
                    </div>

                    {{-- Bagian Tengah: Info Asal Sekolah & Diterima --}}
                    <div class="mt-3 border-t border-b border-border divide-y divide-border text-xs">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="school" class="size-3 text-slate-400"></i>
                                Asal
                            </p>
                            <div class="text-right truncate">
                                <p class="text-foreground font-medium truncate">{{ $originSchool }}</p>
                                <p class="text-secondary/80 truncate text-[10px]">{{ $originCity }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="calendar-check" class="size-3 text-slate-400"></i>
                                Masuk
                            </p>
                            <div class="text-right truncate flex flex-col items-end">
                                <span class="px-1.5 py-0.5 mb-1 rounded bg-primary/10 text-primary text-[10px] font-bold">{{ $className }}</span>
                                <span class="text-foreground truncate">{{ $mutationDateFormatted }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Bawah: Tombol Aksi --}}
            <div class="mt-3 flex justify-end gap-2" x-data="{}">
                <button type="button" title="Detail / Edit"
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                    <i data-lucide="file-pen-line" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Edit</span>
                </button>
                <button type="button" title="Hapus"
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-error/20 bg-error/5 text-error hover:bg-error/10 transition-all cursor-pointer">
                    <i data-lucide="trash-2" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Hapus</span>
                </button>
            </div>

        </div>
        @empty
        <div class="px-4 py-16 text-center text-secondary">
            <div class="flex flex-col items-center gap-3">
                <i data-lucide="inbox" class="size-10 text-border"></i>
                <p class="font-medium text-sm">Belum ada data siswa pindahan pada semester ini</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Memanggil komponen pagination --}}
    <x-ui.pagination :paginator="$data" hxTarget="#mutasi-masuk-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>