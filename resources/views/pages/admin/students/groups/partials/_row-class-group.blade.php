@php
$grades = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
$gradeLabel = $grades[$g->grade_level] ?? $g->grade_level;
$concentrationName = $g->concentration->name ?? '-';
$displayName = $g->name ?: trim("{$gradeLabel} {$concentrationName} {$g->group_number}");

$colors = ['linear-gradient(135deg,#10B981,#6EE7B7)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
$color = $colors[isset($loop) ? ($loop->index % 4) : 0];

$homeroomName = $g->homeroomTeacher->name ?? null;
$totalStudents = $g->total_students_count ?? 0;
$maleStudents = $g->male_students_count ?? 0;
$femaleStudents = $g->female_students_count ?? 0;
@endphp

<tr id="row-class-group-{{ $g->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-4 py-4">
        <a href="{{ route('admin.students.group.show', $g->id) }}" class="flex items-center gap-3 group transition-all">
            <div @style(["background: {$color}"])
                class="h-10 w-10 rounded-full flex items-center justify-center text-white shrink-0">
                <i data-lucide="book-open" class="size-4 pointer-events-none"></i>
            </div>
            <div>
                {{-- Teks merespon hover dari tag <a> utama menggunakan 'group-hover:' --}}
                <div class="font-semibold text-foreground text-sm uppercase group-hover:text-primary group-hover:none transition-colors">
                    {{ $displayName }}
                </div>
                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                    <span class="inline-block size-1.5 rounded-full bg-emerald-500"></span>
                    {{ $concentrationName }}
                </div>
            </div>
        </a>
    </td>

    <td class="px-4 py-4">
        <div class="text-sm font-semibold text-foreground">
            {{ $homeroomName ?? 'Belum ada wali kelas' }}
        </div>
        <div class="text-xs text-secondary mt-0.5">
            {{ number_format($totalStudents, 0, ',', '.') }} Orang
            &middot; <span class="text-blue-600">Laki-laki {{ $maleStudents }}</span>
            &middot; <span class="text-pink-600">Perempuan {{ $femaleStudents }}</span>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="flex items-center gap-2">
            <button type="button" title="Edit"
                @click="formModalOpen = true"
                hx-get="{{ route('admin.students.group.edit', $g->id) }}"
                hx-target="#form-modal-content"
                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                <i data-lucide="file-pen-line" class="size-4 pointer-events-none"></i>
            </button>

            <button type="button" title="Hapus"
                hx-delete="{{ route('admin.students.group.destroy', $g->id) }}"
                hx-target="#class-groups-container" hx-select="#class-groups-container" hx-swap="outerHTML"
                hx-confirm="Yakin ingin menghapus group {{ $displayName }}? Tindakan ini tidak dapat dibatalkan."
                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-error hover:bg-error/10 transition-all cursor-pointer">
                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
            </button>
        </div>
    </td>
</tr>