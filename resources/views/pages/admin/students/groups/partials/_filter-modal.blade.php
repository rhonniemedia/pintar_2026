<x-ui.modal show="filterModalOpen" maxWidth="lg">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
        <div>
            <h3 class="font-bold text-foreground text-lg">Filter Rombongan Belajar</h3>
            <p class="text-xs text-secondary mt-0.5">Persempit daftar berdasarkan kriteria berikut</p>
        </div>
        <button @click="filterModalOpen = false" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
            <i data-lucide="x" class="size-4 text-secondary"></i>
        </button>
    </div>

    {{-- Form filter --}}
    <div id="class-group-filter-form" class="flex-1 overflow-y-auto p-6 space-y-4">

        <div>
            <label class="block text-sm font-bold text-foreground mb-2">Kelas</label>
            <select name="filter_grade" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                <option value="">Semua Kelas</option>
                @foreach (['10' => 'X', '11' => 'XI', '12' => 'XII'] as $value => $label)
                <option value="{{ $value }}" @selected($filterGrade===$value)>Kelas {{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-bold text-foreground mb-2">Jurusan</label>
            <select name="filter_concentration" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                <option value="">Semua Jurusan</option>
                @foreach ($concentrationOptions as $id => $name)
                <option value="{{ $id }}" @selected($filterConcentration==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-between shrink-0">
        <button type="button"
            @click="
                    document.querySelectorAll('#class-group-filter-form select').forEach(el => el.value = '');
                    document.getElementById('btn-apply-filter').click();
                "
            class="px-4 py-2 rounded-xl border border-border bg-white text-secondary hover:bg-muted transition-colors cursor-pointer">
            Reset Filter
        </button>

        <button type="button"
            id="btn-apply-filter"
            hx-get="{{ route('admin.students.group.index') }}"
            hx-include="#class-group-filter-form, [name='search']"
            hx-target="#class-groups-container" hx-select="#class-groups-container" hx-swap="outerHTML" hx-push-url="true"
            @click="filterModalOpen = false"
            class="px-5 py-2.5 bg-primary text-white hover:bg-primary-dark shadow-md text-sm font-bold rounded-xl transition-all cursor-pointer">
            Terapkan Filter
        </button>
    </div>

</x-ui.modal>