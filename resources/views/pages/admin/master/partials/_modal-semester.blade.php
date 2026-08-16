@php
$isEdit = isset($semester);

$ayOptions = [];
foreach($academicYears as $ay) {
$ayOptions[] = [
'value' => $ay->id,
'label' => $ay->name . ' (' . \Carbon\Carbon::parse($ay->start_date)->format('Y') . ')'
];
}

$typeOptions = [
['value' => 'odd', 'label' => 'Ganjil (Odd)'],
['value' => 'even', 'label' => 'Genap (Even)'],
];

$statusOptions = [
['value' => 'inactive', 'label' => 'Tidak Aktif'],
['value' => 'active', 'label' => 'Aktif'],
];

$sourceSemOptions = [];
foreach($semesters ?? [] as $s) {
$typeLabel = $s->type === 'odd' ? 'Ganjil' : 'Genap';
$ayName = $s->academicYear->name ?? '-';
$sourceSemOptions[] = [
'value' => $s->id,
'label' => $s->code . ' · Semester ' . $typeLabel . ' · ' . $ayName
];
}
@endphp
<div x-data="{ open: false }" x-init="setTimeout(() => open = true, 10)">
    <x-ui.modal show="open" maxWidth="lg">

        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="{{ $isEdit ? 'calendar' : 'calendar-days' }}" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">{{ $isEdit ? 'Edit Semester' : 'Tambah Semester' }}</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">{{ $isEdit ? 'Perbarui detail periode semester.' : 'Tentukan periode semester untuk tahun ajaran aktif.' }}</p>
                </div>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form HTMX --}}
        <form hx-post="{{ $isEdit ? route('admin.master-data.semester.update', $semester->id) : route('admin.master-data.semester.store') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            x-data="{ saving: false, duplikat: {{ old('duplikat_rombel') ? 'true' : 'false' }} }"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-slate-50/30">
                {{-- Pilihan Tahun Ajaran Induk --}}
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tahun Ajaran Induk</label>
                    <x-ui.select
                        name="academic_year_id"
                        :options="$ayOptions"
                        value="{{ old('academic_year_id', $isEdit ? $semester->academic_year_id : '') }}"
                        placeholder="-- Pilih Tahun Ajaran --" />
                    @error('academic_year_id') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Tipe Semester --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tipe Semester</label>
                        <x-ui.select
                            name="type"
                            :options="$typeOptions"
                            value="{{ old('type', $isEdit ? $semester->type : '') }}"
                            placeholder="-- Pilih Tipe --" />
                        @error('type') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Kode Semester --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Kode Semester</label>
                        <input type="text" name="code" value="{{ old('code', $isEdit ? $semester->code : '') }}" placeholder="Contoh: 2026-1"
                            class="w-full bg-white border @error('code') border-error @else border-border @enderror rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('code') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Input Tanggal Mulai --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $isEdit ? \Illuminate\Support\Carbon::parse($semester->start_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('start_date') border-error @else border-border @enderror rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('start_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input Tanggal Selesai --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $isEdit ? \Illuminate\Support\Carbon::parse($semester->end_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('end_date') border-error @else border-border @enderror rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('end_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Input Status Semester --}}
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Status Semester</label>
                    <x-ui.select
                        name="status"
                        :options="$statusOptions"
                        value="{{ old('status', $isEdit ? $semester->status : 'inactive') }}"
                        placeholder="Pilih Status" />
                    @error('status') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                @unless($isEdit)
                {{-- Duplikasi Rombel --}}
                <div class="pt-3 border-t border-border">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="hidden" name="duplikat_rombel" value="0">
                        <input type="checkbox" name="duplikat_rombel" value="1" x-model="duplikat"
                            {{ old('duplikat_rombel') ? 'checked' : '' }}
                            class="size-4 rounded border-border text-primary focus:ring-primary/30 cursor-pointer">
                        <span class="text-sm font-bold text-foreground">Duplikasi Rombel dari Semester Sebelumnya</span>
                    </label>
                    <p class="text-xs text-secondary mt-1 ml-6.5">Rombel dari semester sumber akan disalin ke semester baru ini beserta kenaikan kelasnya (X &rarr; XI &rarr; XII).</p>

                    <div x-show="duplikat" x-cloak class="mt-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Semester Sumber</label>
                        <x-ui.select
                            name="sumber_semester_id"
                            :options="$sourceSemOptions"
                            value="{{ old('sumber_semester_id') }}"
                            placeholder="-- Pilih Semester Sumber --" />
                        @error('sumber_semester_id') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endunless
            </div>

            {{-- Footer Form --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end shrink-0 gap-2">
                <button type="button"
                    :disabled="saving"
                    @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all duration-200 cursor-pointer disabled:opacity-50">
                    <span>Batal</span>
                </button>

                <button type="submit"
                    :disabled="saving"
                    class="inline-flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold shadow-md hover:bg-primary-dark transition-all duration-200 cursor-pointer disabled:opacity-70">
                    <div x-show="!saving" class="flex items-center gap-1.5">
                        <i data-lucide="save" class="size-4"></i>
                        <span>{{ $isEdit ? 'Perbarui Data' : 'Simpan Data' }}</span>
                    </div>
                    <div x-show="saving" x-cloak class="flex items-center gap-1.5">
                        <i data-lucide="loader-2" class="size-4 animate-spin"></i>
                        <span>Menyimpan...</span>
                    </div>
                </button>
            </div>
        </form>
    </x-ui.modal>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>