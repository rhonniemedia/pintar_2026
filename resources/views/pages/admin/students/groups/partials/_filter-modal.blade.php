<x-ui.modal show="filterModalOpen" maxWidth="md">

    {{-- Header --}}
    <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="filter" class="size-5 sm:size-6"></i>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Filter Rombongan Belajar</h3>
                <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Persempit daftar berdasarkan kriteria berikut</p>
            </div>
        </div>

        {{-- Tombol Silang (X) --}}
        <button type="button" @click="filterModalOpen = false"
            class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
            <i data-lucide="x" class="size-4 pointer-events-none"></i>
        </button>
    </div>

    {{-- Form filter --}}
    <div id="class-group-filter-form" class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 space-y-4">

        <div>
            <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Kelas</label>
            <select name="filter_grade" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                <option value="">Semua Kelas</option>
                @foreach (['10' => 'X', '11' => 'XI', '12' => 'XII'] as $value => $label)
                <option value="{{ $value }}" @selected($filterGrade===$value)>Kelas {{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Jurusan</label>
            <select name="filter_concentration" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                <option value="">Semua Jurusan</option>
                @foreach ($concentrationOptions as $id => $name)
                <option value="{{ $id }}" @selected($filterConcentration==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-between shrink-0">
        <button type="button"
            @click="
                document.querySelectorAll('#class-group-filter-form select').forEach(el => el.value = '');
                document.getElementById('btn-apply-filter').click();
            "
            class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
            Reset
        </button>

        <button type="button"
            id="btn-apply-filter"
            hx-get="{{ route('admin.students.group.index') }}"
            hx-include="#class-group-filter-form, [name='search']"
            hx-target="#class-groups-container" hx-select="#class-groups-container" hx-swap="outerHTML" hx-push-url="true"
            @click="filterModalOpen = false"
            class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer">
            Terapkan Filter
        </button>
    </div>

</x-ui.modal>