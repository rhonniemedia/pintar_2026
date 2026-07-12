{{-- File: resources/views/pages/admin/master/partials/_modal-academic-year.blade.php --}}
@php
$isEdit = isset($academicYear);
@endphp
<div x-data="{ open: true }" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)">

    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">{{ $isEdit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ $isEdit ? 'Perbarui detail durasi operasional sekolah.' : 'Masukkan detail durasi operasional sekolah.' }}</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4 text-secondary"></i>
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

            <div class="flex-1 overflow-y-auto p-6 space-y-4">

                {{-- Input Nama --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Nama Tahun Ajaran</label>
                    <input type="text" name="name" required maxlength="50"
                        value="{{ old('name', $isEdit ? $academicYear->name : '') }}" placeholder="Contoh: 2026/2027"
                        class="w-full bg-white border @error('name') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    @error('name') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Input Tanggal Mulai --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" required
                            value="{{ old('start_date', $isEdit ? \Illuminate\Support\Carbon::parse($academicYear->start_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('start_date') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('start_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input Tanggal Selesai --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" required
                            value="{{ old('end_date', $isEdit ? \Illuminate\Support\Carbon::parse($academicYear->end_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('end_date') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('end_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Input Status --}}
                <div class="mt-4">
                    <label class="block text-sm font-bold text-foreground mb-2">Status Tahun Ajaran</label>
                    <select name="status"
                        class="w-full bg-white border @error('status') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="inactive" {{ old('status', $isEdit ? $academicYear->status : 'inactive') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="active" {{ old('status', $isEdit ? $academicYear->status : 'inactive') === 'active' ? 'selected' : '' }}>Aktif</option>
                    </select>
                    @error('status') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            {{-- Footer Form --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end shrink-0 gap-2">

                <!-- Tombol Batal -->
                <button type="button"
                    :disabled="saving"
                    @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
                    border border-border bg-white
                    text-secondary text-sm font-semibold
                    hover:bg-muted hover:border-gray-300
                    hover:shadow-sm hover:-translate-y-0.5
                    active:translate-y-0 active:shadow-none
                    transition-all duration-200
                    cursor-pointer
                    disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">

                    <i data-lucide="x" class="size-4"></i>
                    <span>Batal</span>

                </button>

                <!-- Tombol Simpan dengan Loading Spinner -->
                <button type="submit"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                    bg-primary text-white text-sm font-bold
                    shadow-md
                    hover:bg-primary-dark hover:shadow-lg hover:-translate-y-0.5
                    active:translate-y-0 active:shadow-md
                    transition-all duration-200
                    cursor-pointer
                    disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-md">

                    <!-- Icon Save (normal) menggunakan x-show -->
                    <i data-lucide="save" class="size-4" x-show="!saving"></i>

                    <!-- Spinner (loading) menggunakan x-show dan x-cloak -->
                    <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    <!-- Teks dinamis -->
                    <span x-text="saving ? 'Menyimpan...' : '{{ $isEdit ? 'Perbarui Data' : 'Simpan Data' }}'"></span>

                </button>
            </div>
        </form>

    </div>

    {{-- Trigger Lucide Icons (karena dirender oleh HTMX) --}}
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>