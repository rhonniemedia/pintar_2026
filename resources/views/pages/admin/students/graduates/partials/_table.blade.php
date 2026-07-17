<div id="graduates-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.students.graduates.index') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
    hx-target="#graduates-container"
    hx-swap="outerHTML">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table-fixed">
            <colgroup>
                <col style="width:35%">
                <col style="width:25%">
                <col style="width:30%">
                <col style="width:10%">
            </colgroup>
            <thead>
                <tr class="border-b border-border bg-gray-50/30">
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case">Nama | NIS & NISN</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Identitas Diri
                        <div class="text-[11px] font-normal normal-case">Tempat & Tanggal Lahir</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Data Kelulusan
                        <div class="text-[11px] font-normal normal-case">Tahun Kelulusan | Nomor Ijazah</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary text-center">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Detail</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($graduates as $student)
                @php
                $nisn = optional($student->vault)->nisn_encrypted ?? '-';
                $pob = optional($student->vault)->pob_encrypted ?? '-';
                $dob = optional($student->vault)->dob_encrypted;

                $initials = strtoupper(substr($student->name ?? 'U', 0, 2));

                $colors = ['linear-gradient(135deg,#10B981,#6EE7B7)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
                $color = $colors[isset($loop) ? ($loop->index % 4) : 0];
                @endphp
                <tr class="border-b border-border hover:bg-muted/50 transition-colors">

                    {{-- Kolom Peserta Didik --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3 group">
                            {{-- Memanggil komponen Avatar --}}
                            <x-ui.avatar :name="$student->name" :gender="$student->gender" :index="$loop->index" />

                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                    {{ $student->name ?? '-' }}
                                </div>

                                {{-- Menampilkan NIK (Menggantikan NIS & NISN) --}}
                                <div class="text-xs text-secondary mt-0.5 truncate" title="NIK">
                                    {{ optional($student->vault)->nik_encrypted ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom Identitas (TTL dari vault) --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="text-foreground truncate">{{ $pob }}</div>
                        <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                            <i data-lucide="calendar" class="size-3.5 text-slate-400"></i>
                            {{ $dob ? \Carbon\Carbon::parse($dob)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </td>

                    {{-- Kolom Kelulusan --}}
                    <td class="px-4 py-4 text-sm">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="inline-block px-2 py-1 rounded-md text-[10px] font-bold bg-success/10 text-success">
                                Lulusan Tahun {{ $student->graduation_year ?? '-' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-secondary">
                            <i data-lucide="file-badge" class="size-3.5 text-slate-400"></i>
                            <span class="truncate font-mono" title="No. Ijazah">
                                {{ $student->graduation_certificate_number ?? 'Ijazah belum diinput' }}
                            </span>
                        </div>
                    </td>

                    {{-- Kolom Aksi --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="#" title="Detail Profil Alumni"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                                <i data-lucide="user-search" class="size-4 pointer-events-none"></i>
                            </a>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="graduation-cap" class="size-10 text-border"></i>
                            <p class="font-medium text-sm">Belum ada data alumni yang terdaftar.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination --}}
    <x-ui.pagination :paginator="$graduates" hxTarget="#graduates-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>