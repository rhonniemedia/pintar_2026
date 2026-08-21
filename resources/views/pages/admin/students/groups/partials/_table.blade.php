<div id="class-groups-container">

    {{-- ============ 1. DESKTOP TABLE ============ --}}
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border">
                    <th class="w-[46%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Rombel
                        <div class="text-[11px] font-normal normal-case">Nama Rombel | Jurusan</div>
                    </th>

                    <th class="w-[46%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Wali Kelas
                        <div class="text-[11px] font-normal normal-case">Wali Kelas | Jumlah Siswa</div>
                    </th>

                    <th class="w-[8%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Edit | Hapus</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classGroups as $g)
                @include('pages.admin.students.groups.partials._row-class-group', ['g' => $g])
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium">Tidak ada data rombel ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS ============ --}}
    <div class="lg:hidden divide-y divide-border bg-white -mx-4 sm:-mx-5">
        @forelse ($classGroups as $g)
        @php
        $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII'];
        $gradeLabel = $grades[$g->grade_level] ?? $g->grade_level;
        $concentrationName = $g->concentration->name ?? '-';
        $displayName = $g->name ?: trim("{$gradeLabel} {$concentrationName} {$g->group_number}");
        $homeroomName = $g->homeroomTeacher->name_with_title ?? null;
        $totalStudents = $g->total_students_count ?? 0;
        $maleStudents = $g->male_students_count ?? 0;
        $femaleStudents = $g->female_students_count ?? 0;
        @endphp

        <div id="card-class-group-{{ $g->id }}" class="p-4 border-border hover:bg-muted/40 active:bg-muted/60 transition-colors">
            <div class="flex items-start gap-3">
                {{-- Avatar Rombel --}}
                <div class="relative size-10 shrink-0 rounded-full overflow-hidden">
                    <x-ui.avatar name=" " :index="$loop->index ?? 0" class="size-10 absolute inset-0" />
                    <div class="absolute inset-0 flex items-center justify-center text-white">
                        <i data-lucide="notebook-pen" class="size-5 stroke-[2] pointer-events-none"></i>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    {{-- Bagian Atas: Nama Rombel & Jurusan --}}
                    <div class="min-w-0">
                        <a href="{{ route('admin.students.group.show', $g->id) }}" class="font-semibold text-foreground text-sm uppercase truncate block hover:text-primary transition-colors">
                            {{ $displayName }}
                        </a>
                        <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5 truncate">
                            <span class="inline-block size-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            {{ $concentrationName }}
                        </div>
                    </div>

                    {{-- Bagian Tengah: Wali Kelas & Statistik --}}
                    <div class="mt-3 border-t border-b border-border divide-y divide-border text-xs">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="user-check" class="size-3 text-slate-400"></i>
                                Wali Kelas
                            </p>
                            <p class="text-foreground text-right truncate">
                                @if($homeroomName)
                                <span class="font-medium">{{ $homeroomName }}</span>
                                @else
                                <span class="text-secondary/70 italic">Belum ada</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="users" class="size-3 text-slate-400"></i>
                                Siswa
                            </p>
                            <div class="flex flex-wrap items-center justify-end gap-1.5 text-[10px]">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-semibold">T: {{ $totalStudents }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-semibold">L: {{ $maleStudents }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-pink-50 text-pink-700 border border-pink-200 font-semibold">P: {{ $femaleStudents }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Bawah: Tombol Aksi --}}
            <div class="mt-3 flex justify-end gap-2" x-data="{}">
                <button type="button" title="Edit"
                    @click="formModalOpen = true"
                    hx-get="{{ route('admin.students.group.edit', $g->id) }}"
                    hx-target="#form-modal-content"
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                    <i data-lucide="file-pen-line" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Edit</span>
                </button>

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
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-error/20 bg-error/5 text-error hover:bg-error/10 transition-all cursor-pointer">
                    <i data-lucide="trash-2" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Hapus</span>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="px-4 py-16 text-center text-secondary">
            <div class="flex flex-col items-center gap-3">
                <i data-lucide="inbox" class="size-10 text-border"></i>
                <p class="font-medium text-sm">Tidak ada data rombel ditemukan</p>
            </div>
        </div>
        @endforelse
    </div>

    <x-ui.pagination :paginator="$classGroups" hxTarget="#class-groups-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>