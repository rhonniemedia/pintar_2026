<div id="mutasi-masuk-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.students.transfer.in.index') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}"
    hx-target="#mutasi-masuk-container"
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
                        <div class="text-[11px] font-normal normal-case">Nama | NISN</div>
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

                // Tambahkan baris ini untuk memanggil NISN
                $nisn = optional(optional($student)->vault)->nisn_encrypted ?? '-';

                // Mengambil 2 huruf pertama untuk avatar
                $initials = strtoupper(substr($student->name ?? 'U', 0, 2));

                // Variasi warna gradien seperti referensi
                $colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
                $color = $colors[isset($loop) ? ($loop->index % 4) : 0];
                @endphp
                <tr id="row-mutasi-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">

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

                    {{-- Kolom Sekolah Asal --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground font-medium truncate pr-2" title="{{ $r->origin_school }}">
                            {{ $r->origin_school ?? '-' }}
                        </div>
                        <div class="text-xs text-secondary truncate">
                            {{ $r->origin_school_city ?? '-' }}
                        </div>
                    </td>

                    {{-- Kolom Diterima --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground font-medium truncate">
                            {{ optional($r->classGroup)->name ?? '-' }}
                        </div>
                        <div class="mt-1 flex items-center gap-1 text-[11px] text-secondary">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            <span>
                                {{ \Carbon\Carbon::parse($r->mutation_date)->translatedFormat('d M Y') }}
                            </span>
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

    {{-- Memanggil komponen pagination --}}
    <x-ui.pagination :paginator="$data" hxTarget="#mutasi-masuk-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>