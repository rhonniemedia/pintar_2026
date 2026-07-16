<div x-show="filterModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="filterModalOpen = false">

    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">Filter Riwayat Siswa</h3>
                <p class="text-xs text-secondary mt-0.5">Persempit daftar berdasarkan kriteria berikut</p>
            </div>
            <button @click="filterModalOpen = false" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        <div id="student-history-filter-form" class="flex-1 overflow-y-auto p-6 space-y-4">

            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Status Keluar</label>
                <select name="filter_exit_status" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <option value="">Semua Status</option>
                    @foreach ($exitStatusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filterExitStatus===$value)>{{ $label }}</option>
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

            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Semester Keluar</label>
                <select name="filter_exit_semester" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <option value="">Semua Semester</option>
                    @foreach ($exitSemesterOptions as $semester)
                    <option value="{{ $semester }}" @selected($filterExitSemester==$semester)>{{ $semester }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-between shrink-0">
            <button type="button"
                @click="
                    document.querySelectorAll('#student-history-filter-form select').forEach(el => el.value = '');
                    document.getElementById('btn-apply-history-filter').click();
                "
                class="px-4 py-2 rounded-xl border border-border bg-white text-secondary hover:bg-muted transition-colors cursor-pointer">
                Reset Filter
            </button>

            <button type="button"
                id="btn-apply-history-filter"
                hx-get="{{ route('admin.students.history.index') }}"
                hx-include="#student-history-filter-form, [name='search']"
                hx-target="#students-history-container" hx-select="#students-history-container" hx-swap="outerHTML" hx-push-url="true"
                @click="filterModalOpen = false"
                class="px-5 py-2.5 bg-primary text-white hover:bg-primary-dark shadow-md text-sm font-bold rounded-xl transition-all cursor-pointer">
                Terapkan Filter
            </button>
        </div>

    </div>
</div>