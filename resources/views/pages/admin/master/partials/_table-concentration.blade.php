<div id="master-data-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.master-data.academic') }}?tab=concentration{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}"
    hx-target="#master-data-container"
    hx-swap="outerHTML">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border bg-gray-50/30">
                    <th class="w-[32%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Nama Jurusan</th>
                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Kode / Alias</th>
                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Status</th>
                    <th class="w-[8%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $r)
                <tr id="row-jurusan-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            @if($r->icon)
                            <div class="size-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $r->icon }}" class="size-4 text-secondary"></i>
                            </div>
                            @endif
                            <div>
                                <div class="font-semibold text-foreground text-sm">{{ $r->name }}</div>
                                <div class="text-xs text-secondary mt-0.5 truncate max-w-xs">{{ $r->description }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-4">
                        <div class="flex flex-col items-start gap-1">
                            <span class="px-2 py-1 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold font-mono">{{ $r->code }}</span>
                            <span class="text-xs font-medium text-secondary">{{ $r->alias }}</span>
                        </div>
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
                            {{-- Tombol Edit --}}
                            <button type="button" title="Edit"
                                hx-get="{{ route('admin.master-data.concentration.edit', $r->id) }}"
                                hx-target="#modal-form-container"
                                hx-swap="innerHTML"
                                class="inline-flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-primary/10 hover:text-primary hover:border-primary/30 transition-all cursor-pointer">
                                <i data-lucide="pencil" class="size-4 pointer-events-none"></i>
                            </button>

                            {{-- Tombol Hapus --}}
                            <button type="button" title="Hapus"
                                @click="
                                    ShowConfirm({
                                        title: 'Hapus Jurusan?',
                                        message: 'Data jurusan \'{{ addslashes($r->name) }}\' akan dihapus permanen dan tidak dapat dikembalikan.',
                                        confirmText: 'Ya, Hapus',
                                        cancelText: 'Batal',
                                    }, () => {
                                        htmx.ajax('DELETE', '{{ route('admin.master-data.concentration.destroy', $r->id) }}', { target: '#master-data-container', swap: 'none' });
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
                            <p class="font-medium">Tidak ada data jurusan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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