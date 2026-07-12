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
                            <div class="relative shrink-0">
                                <div @style(["background: {$color}"])
                                    class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                    {{ $initials }}
                                </div>
                                <span title="{{ $r->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}"
                                    class="absolute -bottom-0.5 -right-0.5 flex items-center justify-center size-4 rounded-full border-2 border-white {{ $r->gender === 'L' ? 'bg-blue-500' : 'bg-pink-500' }}">
                                    <i data-lucide="{{ $r->gender === 'L' ? 'mars' : 'venus' }}" class="size-2.5 text-white pointer-events-none"></i>
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary group-hover:none transition-colors truncate">{{ $r->name }}</div>
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
                            <span class="px-2 py-1 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold font-mono">{{ $r->nis ?? '-' }}</span>
                            <span class="px-2 py-1 rounded-md bg-warning/10 text-warning-dark text-xs font-bold font-mono">{{ $nisn }}</span>
                        </div>
                    </td>

                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.students.edit.personal', $r->id) }}" title="Edit"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                                <i data-lucide="file-pen-line" class="size-4 pointer-events-none"></i>
                            </a>
                            <button type="button" title="Pindah Kelas"
                                hx-get="{{-- route('admin.students.move', $r->id) --}}"
                                hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-warning/10 hover:text-warning-dark hover:border-warning/30 transition-colors cursor-pointer">
                                <i data-lucide="repeat-2" class="size-4 pointer-events-none"></i>
                            </button>
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

    @include('pages.admin.students.data.partials._pagination', ['students' => $students])
</div>