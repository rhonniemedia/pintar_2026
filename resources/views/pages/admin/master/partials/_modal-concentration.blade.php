@php
$isEdit = isset($concentration);
@endphp
<div x-data="{ open: false }" x-init="setTimeout(() => open = true, 10)">
    <x-ui.modal show="open" maxWidth="lg">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">{{ $isEdit ? 'Edit Jurusan' : 'Tambah Jurusan' }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ $isEdit ? 'Perbarui detail program keahlian.' : 'Tambahkan program keahlian atau konsentrasi baru.' }}</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form HTMX --}}
        <form hx-{{ $isEdit ? 'put' : 'post' }}="{{ $isEdit ? route('admin.master-data.concentration.update', $concentration->id) : route('admin.master-data.concentration.store') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            x-data="{ saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Input Nama Jurusan --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Nama Jurusan (Lengkap)</label>
                    <input type="text" name="name" value="{{ old('name', $isEdit ? $concentration->name : '') }}" placeholder="Contoh: Rekayasa Perangkat Lunak"
                        class="w-full bg-white border @error('name') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    @error('name') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Input Alias --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Singkatan (Alias)</label>
                        <input type="text" name="alias" value="{{ old('alias', $isEdit ? $concentration->alias : '') }}" placeholder="Contoh: RPL"
                            class="w-full bg-white border @error('alias') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('alias') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Input Kode --}}
                    <div>
                        <label class="block text-sm font-bold text-foreground mb-2">Kode Jurusan</label>
                        <input type="text" name="code" value="{{ old('code', $isEdit ? $concentration->code : '') }}" placeholder="Contoh: JUR-001"
                            class="w-full bg-white border @error('code') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        @error('code') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Input Ikon --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Ikon (Lucide)</label>
                    <input type="text" name="icon" value="{{ old('icon', $isEdit ? $concentration->icon : '') }}" placeholder="Contoh: monitor, cpu, database"
                        class="w-full bg-white border @error('icon') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <p class="text-[11px] text-secondary mt-1">Gunakan nama ikon dari <a href="https://lucide.dev/icons/" target="_blank" class="text-primary hover:underline">Lucide Icons</a>.</p>
                    @error('icon') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Input Deskripsi --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Deskripsi Singkat</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan secara singkat fokus jurusan ini..."
                        class="w-full bg-white border @error('description') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all resize-none">{{ old('description', $isEdit ? $concentration->description : '') }}</textarea>
                    @error('description') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Input Status --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Status Jurusan</label>
                    <select name="status" class="w-full bg-white border @error('status') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="active" {{ old('status', $isEdit ? $concentration->status : 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="archived" {{ old('status', $isEdit ? $concentration->status : 'active') === 'archived' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>
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