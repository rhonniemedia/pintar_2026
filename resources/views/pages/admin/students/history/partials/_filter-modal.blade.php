{{-- File: views/.../_student-history-filter-modal.blade.php --}}
@php
// Persiapan opsi data untuk komponen x-ui.select
$exitStatusOptionsList = [];
if(isset($exitStatusOptions)) {
foreach ($exitStatusOptions as $value => $label) {
$exitStatusOptionsList[] = ['value' => $value, 'label' => $label];
}
}

$concentrationOptionsList = [];
if(isset($concentrationOptions)) {
foreach ($concentrationOptions as $id => $name) {
$concentrationOptionsList[] = ['value' => $id, 'label' => $name];
}
}

$exitSemesterOptionsList = [];
if(isset($exitSemesterOptions)) {
foreach ($exitSemesterOptions as $semester) {
$exitSemesterOptionsList[] = ['value' => $semester, 'label' => $semester];
}
}
@endphp

<div x-show="filterModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="filterModalOpen = false">

    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

        {{-- Header Modal (Disesuaikan gayanya agar seragam) --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="filter" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Filter Riwayat Siswa</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Persempit daftar berdasarkan kriteria berikut</p>
                </div>
            </div>
            <button type="button" @click="filterModalOpen = false"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form filter --}}
        <div id="student-history-filter-form" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-slate-50/30">

            <div>
                <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Status Keluar</label>
                <x-ui.select
                    name="filter_exit_status"
                    :options="$exitStatusOptionsList"
                    value="{{ $filterExitStatus ?? '' }}"
                    placeholder="Semua Status" />
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Jurusan</label>
                <x-ui.select
                    name="filter_concentration"
                    :options="$concentrationOptionsList"
                    value="{{ $filterConcentration ?? '' }}"
                    placeholder="Semua Jurusan" />
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Semester Keluar</label>
                <x-ui.select
                    name="filter_exit_semester"
                    :options="$exitSemesterOptionsList"
                    value="{{ $filterExitSemester ?? '' }}"
                    placeholder="Semua Semester" />
            </div>

        </div>

        {{-- Footer --}}
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-between shrink-0">
            <button type="button"
                @click="
                    $dispatch('reset-filters');
                    document.querySelectorAll('#student-history-filter-form input[type=hidden]').forEach(el => el.value = '');
                    setTimeout(() => { document.getElementById('btn-apply-history-filter').click(); }, 50);
                "
                class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                Reset Filter
            </button>

            <button type="button"
                id="btn-apply-history-filter"
                hx-get="{{ route('admin.students.history.index') }}"
                hx-include="#student-history-filter-form, [name='search']"
                hx-target="#students-history-container" hx-select="#students-history-container" hx-swap="outerHTML" hx-push-url="true"
                @click="filterModalOpen = false"
                class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer">
                Terapkan Filter
            </button>
        </div>

    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>