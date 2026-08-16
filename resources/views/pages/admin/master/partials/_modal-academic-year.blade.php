@php
$isEdit = isset($academicYear);
$statusOptions = [
['value' => 'inactive', 'label' => 'Tidak Aktif'],
['value' => 'active', 'label' => 'Aktif'],
];
@endphp
<div x-data="{ open: false }" x-init="setTimeout(() => open = true, 10)">
    <x-ui.modal show="open" maxWidth="lg">

        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="{{ $isEdit ? 'calendar-range' : 'calendar-plus' }}" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">{{ $isEdit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">{{ $isEdit ? 'Perbarui detail durasi operasional sekolah.' : 'Masukkan detail durasi operasional sekolah.' }}</p>
                </div>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form HTMX --}}
        <form hx-post="{{ $isEdit ? route('admin.master-data.academic-year.update', $academicYear->id) : route('admin.master-data.academic-year.store') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            x-data="{ saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-slate-50/30">

                {{-- Input Nama --}}
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Nama Tahun Ajaran</label>
                    <input type="text" name="name" required maxlength="50"
                        value="{{ old('name', $isEdit ? $academicYear->name : '') }}" placeholder="Contoh: 2026/2027"
                        class="w-full bg-white border @error('name') border-error @else border-border @enderror rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    @error('name') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Input Tanggal Mulai --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" required
                            value="{{ old('start_date', $isEdit ? \Illuminate\Support\Carbon::parse($academicYear->start_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('start_date') border-error @else border-border @enderror rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('start_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input Tanggal Selesai --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" required
                            value="{{ old('end_date', $isEdit ? \Illuminate\Support\Carbon::parse($academicYear->end_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('end_date') border-error @else border-border @enderror rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('end_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Input Status --}}
                <div class="pt-1">
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Status Tahun Ajaran</label>
                    <x-ui.select
                        name="status"
                        :options="$statusOptions"
                        value="{{ old('status', $isEdit ? $academicYear->status : 'inactive') }}"
                        placeholder="Pilih Status" />
                    @error('status') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            {{-- Footer Form --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end shrink-0 gap-2">

                <!-- Tombol Batal -->
                <button type="button"
                    :disabled="saving"
                    @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all duration-200 cursor-pointer disabled:opacity-50">
                    <span>Batal</span>
                </button>

                <!-- Tombol Simpan -->
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