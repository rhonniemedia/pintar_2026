@php
$isEdit = isset($semester);
@endphp
<div x-data="{ open: false }" x-init="setTimeout(() => open = true, 10)">
    <x-ui.modal show="open" maxWidth="lg">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">{{ $isEdit ? 'Edit Semester' : 'Tambah Semester' }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ $isEdit ? 'Perbarui detail periode semester.' : 'Tentukan periode semester untuk tahun ajaran aktif.' }}</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4 text-secondary"></i>
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

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Pilihan Tahun Ajaran Induk --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Tahun Ajaran Induk</label>
                    <select name="academic_year_id" class="w-full bg-white border @error('academic_year_id') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" @selected(old('academic_year_id', $isEdit ? $semester->academic_year_id : '') == $ay->id)>
                            {{ $ay->name }} ({{ \Carbon\Carbon::parse($ay->start_date)->format('Y') }})
                        </option>
                        @endforeach
                    </select>
                    @error('academic_year_id') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Tipe Semester --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Tipe Semester</label>
                        <select name="type" class="w-full bg-white border @error('type') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="odd" @selected(old('type', $isEdit ? $semester->type : '') == 'odd')>Ganjil (Odd)</option>
                            <option value="even" @selected(old('type', $isEdit ? $semester->type : '') == 'even')>Genap (Even)</option>
                        </select>
                        @error('type') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Kode Semester --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Kode Semester</label>
                        <input type="text" name="code" value="{{ old('code', $isEdit ? $semester->code : '') }}" placeholder="Contoh: 2026-1"
                            class="w-full bg-white border @error('code') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('code') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Input Tanggal Mulai --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $isEdit ? \Illuminate\Support\Carbon::parse($semester->start_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('start_date') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('start_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input Tanggal Selesai --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $isEdit ? \Illuminate\Support\Carbon::parse($semester->end_date)->format('Y-m-d') : '') }}"
                            class="w-full bg-white border @error('end_date') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('end_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Input Status Semester --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Status Semester</label>
                    <select name="status" class="w-full bg-white border @error('status') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="inactive" {{ old('status', $isEdit ? $semester->status : 'inactive') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="active" {{ old('status', $isEdit ? $semester->status : 'inactive') === 'active' ? 'selected' : '' }}>Aktif</option>
                    </select>
                    @error('status') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                @unless($isEdit)
                {{-- Duplikasi Rombel --}}
                <div class="pt-2 border-t border-border">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="hidden" name="duplikat_rombel" value="0">
                        <input type="checkbox" name="duplikat_rombel" value="1" x-model="duplikat"
                            {{ old('duplikat_rombel') ? 'checked' : '' }}
                            class="size-4 rounded border-border text-primary focus:ring-primary/30 cursor-pointer">
                        <span class="text-sm font-bold text-foreground">Duplikasi Rombel dari Semester Sebelumnya</span>
                    </label>
                    <p class="text-xs text-secondary mt-1 ml-6">Rombel dari semester sumber akan disalin ke semester baru ini beserta kenaikan kelasnya (X &rarr; XI &rarr; XII).</p>

                    <div x-show="duplikat" x-cloak class="mt-3">
                        <label class="block text-sm font-bold text-foreground mb-2">Semester Sumber</label>
                        <select name="sumber_semester_id" class="w-full bg-white border @error('sumber_semester_id') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            <option value="">-- Pilih Semester Sumber --</option>
                            @foreach($semesters ?? [] as $s)
                            <option value="{{ $s->id }}" @selected(old('sumber_semester_id')==$s->id)>
                                {{ $s->code }} &middot; Semester {{ $s->type === 'odd' ? 'Ganjil' : 'Genap' }} &middot; {{ $s->academicYear->name ?? '-' }}
                            </option>
                            @endforeach
                        </select>
                        @error('sumber_semester_id') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endunless
            </div>

            {{-- Footer Form --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end shrink-0 gap-2">
                <button type="button"
                    :disabled="saving"
                    @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 hover:shadow-sm transition-all duration-200 cursor-pointer disabled:opacity-50">
                    <i data-lucide="x" class="size-4"></i>
                    <span>Batal</span>
                </button>

                <button type="submit"
                    :disabled="saving"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-md hover:bg-primary-dark transition-all duration-200 cursor-pointer disabled:opacity-70">
                    <i data-lucide="save" class="size-4" x-show="!saving"></i>
                    <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? 'Menyimpan...' : '{{ $isEdit ? 'Perbarui Data' : 'Simpan Data' }}'"></span>
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