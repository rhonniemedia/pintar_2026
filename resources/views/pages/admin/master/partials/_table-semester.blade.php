<div id="master-data-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.master-data.academic') }}?tab=semester{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}"
    hx-target="#master-data-container"
    hx-swap="outerHTML">

    {{-- ============ 1. DESKTOP TABLE ============ --}}
    <div class="hidden lg:block overflow-x-auto pb-2 custom-scrollbar">
        <table class="w-full text-left min-w-[800px]">
            <thead>
                <tr class="border-b border-border bg-gray-50/30">
                    <th class="w-[32%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Kode Semester</th>
                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Tipe & Tahun Ajaran</th>
                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Status</th>
                    <th class="w-[8%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $r)
                <tr id="row-sem-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
                    <td class="px-4 py-4">
                        <div class="font-semibold text-foreground text-sm font-mono bg-muted inline-block px-2 py-1 rounded">{{ $r->code }}</div>
                    </td>

                    <td class="px-4 py-4">
                        <div class="font-semibold text-foreground text-sm capitalize">
                            Semester {{ $r->type === 'odd' ? 'Ganjil' : 'Genap' }}
                        </div>
                        <div class="text-xs text-secondary mt-0.5">TA: {{ $r->academicYear->name ?? '-' }}</div>
                    </td>

                    <td class="px-4 py-4">
                        @if($r->status === 'active')
                        <span class="px-2 py-1 rounded-md bg-emerald-500/10 text-emerald-700 text-xs font-bold border border-emerald-500/20">Aktif</span>
                        @else
                        <span class="px-2 py-1 rounded-md bg-slate-500/10 text-slate-700 text-xs font-bold border border-slate-500/20">Tidak Aktif</span>
                        @endif
                    </td>

                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2" x-data="{}">
                            <button type="button" title="Edit"
                                hx-get="{{ route('admin.master-data.semester.edit', $r->id) }}"
                                hx-target="#modal-form-container"
                                hx-swap="innerHTML"
                                class="inline-flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-primary/10 hover:text-primary hover:border-primary/30 transition-all cursor-pointer">
                                <i data-lucide="pencil" class="size-4 pointer-events-none"></i>
                            </button>

                            <button type="button" title="Hapus"
                                @click="
                                    ShowConfirm({
                                        title: 'Hapus Semester?',
                                        message: 'Data semester \'{{ addslashes($r->code) }}\' akan dihapus permanen dan tidak dapat dikembalikan.',
                                        confirmText: 'Ya, Hapus',
                                        cancelText: 'Batal',
                                    }, () => {
                                        htmx.ajax('DELETE', '{{ route('admin.master-data.semester.destroy', $r->id) }}', { target: '#master-data-container', swap: 'none' });
                                    })
                                "
                                class="inline-flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-all cursor-pointer">
                                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium">Tidak ada data semester</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS ============ --}}
    <div class="lg:hidden divide-y divide-border bg-white -mx-4 sm:-mx-5">
        @forelse ($data as $r)
        @php
        $semesterTypeLabel = $r->type === 'odd' ? 'Semester Ganjil' : 'Semester Genap';
        $academicYearLabel = $r->academicYear->name ?? '-';
        @endphp

        <div id="card-sem-{{ $r->id }}" class="p-4 border-border hover:bg-muted/40 active:bg-muted/60 transition-colors">

            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-semibold text-foreground text-sm uppercase truncate">
                        {{ $semesterTypeLabel }}
                    </p>
                    <p class="text-[10px] font-mono font-semibold text-secondary mt-0.5 truncate bg-muted inline-block px-1.5 py-0.5 rounded">
                        {{ $r->code }}
                    </p>
                </div>

                {{-- Badge Status --}}
                @if($r->status === 'active')
                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-700">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>AKTIF
                </span>
                @else
                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold rounded-md bg-gray-100 text-gray-500">
                    <span class="size-1.5 rounded-full bg-gray-400"></span>TIDAK AKTIF
                </span>
                @endif
            </div>

            {{-- Detail Tahun Ajaran --}}
            <div class="mt-3 border-t border-border pt-2.5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-secondary flex items-center gap-1.5 shrink-0 text-xs">
                        <i data-lucide="calendar" class="size-3 text-slate-400"></i>
                        Tahun Ajaran
                    </p>
                    <p class="text-foreground text-xs font-medium text-right truncate">
                        {{ $academicYearLabel }}
                    </p>
                </div>
            </div>

            {{-- Aksi --}}
            <div class="mt-4 flex justify-end gap-2" x-data="{}">
                <button type="button" title="Edit"
                    hx-get="{{ route('admin.master-data.semester.edit', $r->id) }}"
                    hx-target="#modal-form-container"
                    hx-swap="innerHTML"
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-primary/10 hover:text-primary transition-all cursor-pointer">
                    <i data-lucide="pencil" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Edit</span>
                </button>
                <button type="button" title="Hapus"
                    @click="
                        ShowConfirm({
                            title: 'Hapus Semester?',
                            message: 'Data semester \'{{ addslashes($r->code) }}\' akan dihapus permanen dan tidak dapat dikembalikan.',
                            confirmText: 'Ya, Hapus',
                            cancelText: 'Batal',
                        }, () => {
                            htmx.ajax('DELETE', '{{ route('admin.master-data.semester.destroy', $r->id) }}', { target: '#master-data-container', swap: 'none' });
                        })
                    "
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
                <p class="font-medium text-sm">Tidak ada data semester</p>
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $data->links() }}
    </div>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>