@php
$grades = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
$gradeLabel = $grades[$g->grade_level] ?? $g->grade_level;
$concentrationName = $g->concentration->name ?? '-';
$displayName = $g->name ?: trim("{$gradeLabel} {$concentrationName} {$g->group_number}");

$colors = ['linear-gradient(135deg,#10B981,#6EE7B7)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
$color = $colors[isset($loop) ? ($loop->index % 4) : 0];

$homeroomName = $g->homeroomTeacher->name_with_title ?? null;
$totalStudents = $g->total_students_count ?? 0;
$maleStudents = $g->male_students_count ?? 0;
$femaleStudents = $g->female_students_count ?? 0;
@endphp

<tr id="row-class-group-{{ $g->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-5 py-4 min-w-[260px]">
        <a href="{{ route('admin.students.group.show', $g->id) }}" class="flex items-center gap-3 group transition-all">
            <div @style(["background: {$color}"])
                class="h-10 w-10 rounded-full flex items-center justify-center text-white shrink-0">
                <i data-lucide="book-open" class="size-4 pointer-events-none"></i>
            </div>
            <div>
                {{-- Teks merespon hover dari tag <a> utama menggunakan 'group-hover:' --}}
                <div class="font-semibold text-foreground text-sm uppercase group-hover:text-primary group-hover:none transition-colors whitespace-nowrap">
                    {{ $displayName }}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5 whitespace-nowrap">
                    <span class="inline-block size-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    {{ $concentrationName }}
                </div>
            </div>
        </a>
    </td>

    <td class="px-5 py-4 min-w-[320px]">
        {{-- Nama Wali Kelas --}}
        <div class="text-sm font-semibold text-foreground mb-2 whitespace-nowrap">
            @if($homeroomName)
            {{ $homeroomName }}
            @else
            <span class="text-secondary/70 italic font-medium">Belum ada wali kelas</span>
            @endif
        </div>

        {{-- Statistik Siswa --}}
        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">

            <!-- Total -->
            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200"
                title="Total Siswa">
                <i data-lucide="users" class="size-3 text-slate-500"></i>
                <span class="font-semibold">Total</span>
                <span class="font-bold">{{ $totalStudents }}</span>
            </div>

            <!-- Laki-laki -->
            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200"
                title="Laki-laki">
                <svg class="size-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="10" cy="14" r="5"></circle>
                    <line x1="13.5" y1="10.5" x2="20" y2="4"></line>
                    <polyline points="15 4 20 4 20 9"></polyline>
                </svg>
                <span class="font-semibold">L</span>
                <span class="font-bold">{{ $maleStudents }}</span>
            </div>

            <!-- Perempuan -->
            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 border border-pink-200"
                title="Perempuan">
                <svg class="size-3 text-pink-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="10" r="5"></circle>
                    <line x1="12" y1="15" x2="12" y2="22"></line>
                    <line x1="9" y1="19" x2="15" y2="19"></line>
                </svg>
                <span class="font-semibold">P</span>
                <span class="font-bold">{{ $femaleStudents }}</span>
            </div>

        </div>
    </td>

    <td class="px-5 py-4 min-w-[110px]">
        <div class="flex items-center gap-2" x-data="{}">
            <button type="button" title="Edit"
                @click="formModalOpen = true"
                hx-get="{{ route('admin.students.group.edit', $g->id) }}"
                hx-target="#form-modal-content"
                class="flex items-center justify-center size-9 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer shrink-0">
                <i data-lucide="file-pen-line" class="size-4 pointer-events-none"></i>
            </button>

            {{-- Tombol Hapus: Ditampilkan hanya jika tidak ada siswa --}}
            @if($totalStudents == 0)
            <button type="button" title="Hapus"
                @click="
                        ShowConfirm({
                            title: 'Hapus Rombel?',
                            message: 'Yakin ingin menghapus rombel \'{{ addslashes($displayName) }}\'? Tindakan ini tidak dapat dibatalkan.',
                            confirmText: 'Ya, Hapus',
                            cancelText: 'Batal',
                        }, () => {
                            htmx.ajax('DELETE', '{{ route('admin.students.group.destroy', $g->id) }}', { 
                                target: '#class-groups-container', 
                                swap: 'none' 
                            });
                        })
                    "
                class="flex items-center justify-center size-9 rounded-lg border border-border bg-white text-error hover:bg-error/10 transition-all cursor-pointer shrink-0">
                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
            </button>
            @endif
        </div>
    </td>
</tr>