<div id="modal-container"
    x-data="{ 
        open: false,
        closeModal() {
            this.open = false;
            setTimeout(() => document.getElementById('modal-container').outerHTML = '<div id=\'modal-container\'></div>', 300);
        }
    }"
    x-init="setTimeout(() => open = true, 50)"
    @close-modal.window="closeModal()">

    <x-ui.modal show="open" maxWidth="2xl">

        <div id="create-modal-content" class="flex flex-col flex-1 min-h-0"
            x-data="{ isLoading: false }">

            {{-- Modal Header --}}
            <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="size-11 sm:size-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="user-plus" class="size-5 sm:size-6"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Tambah Peserta Didik Baru</h3>
                        <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Masukkan data utama siswa. Data lainnya dapat dilengkapi nanti.</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()"
                    class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                    <i data-lucide="x" class="size-4 pointer-events-none"></i>
                </button>
            </div>

            @php
            $inputClass = 'w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors placeholder:text-muted-foreground';
            $labelClass = 'block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5';
            $errorClass = 'border-error ring-1 ring-error/30';
            @endphp

            <div class="block p-4 sm:p-6 overflow-y-auto max-h-[60vh] bg-slate-50/30 flex-1 [scrollbar-gutter:stable]">

                {{-- Form Tambah Data --}}
                <form id="create-student-form"
                    @htmx:before-request="isLoading = true"
                    @htmx:after-request="isLoading = false"
                    hx-post="{{ route('admin.students.data.store') }}" {{-- Sesuaikan dengan route store Anda --}}
                    hx-target="#create-modal-content"
                    hx-select="#create-modal-content"
                    hx-swap="outerHTML"
                    hx-on::after-request="if(event.detail.successful) { window.dispatchEvent(new CustomEvent('close-modal')); htmx.ajax('GET', '{{ route('admin.students.data.index') }}', '#students-container'); }">

                    @csrf

                    {{-- SEKSI 1: IDENTITAS PRIBADI --}}
                    <h4 class="text-sm font-bold text-foreground flex items-center gap-2 mb-3">
                        <i data-lucide="contact" class="size-4 text-primary"></i> Identitas Pribadi
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 p-4 bg-white border border-border rounded-2xl">

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Nama Lengkap <span class="text-error">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap siswa" required class="{{ $inputClass }} @error('name') {{ $errorClass }} @enderror">
                            @error('name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">NISN <span class="text-error">*</span></label>
                            <input type="text" name="nisn" inputmode="numeric" maxlength="10" value="{{ old('nisn') }}" placeholder="10 Digit NISN" required class="{{ $inputClass }} font-mono @error('nisn') {{ $errorClass }} @enderror">
                            @error('nisn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">NIK</label>
                            <input type="text" name="nik" inputmode="numeric" maxlength="16" value="{{ old('nik') }}" placeholder="16 Digit NIK (Opsional)" class="{{ $inputClass }} font-mono @error('nik') {{ $errorClass }} @enderror">
                            @error('nik') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Jenis Kelamin <span class="text-error">*</span></label>
                            <select name="gender" required class="{{ $inputClass }} @error('gender') {{ $errorClass }} @enderror">
                                <option value="">— Pilih —</option>
                                @foreach(\App\Enums\Student\Gender::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('gender')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('gender') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Agama</label>
                            <select name="religion" class="{{ $inputClass }} @error('religion') {{ $errorClass }} @enderror">
                                <option value="">— Pilih —</option>
                                @foreach(\App\Enums\Student\Religion::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('religion')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('religion') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- SEKSI 2: DATA AKADEMIK AWAL --}}
                    <h4 class="text-sm font-bold text-foreground flex items-center gap-2 mb-3">
                        <i data-lucide="graduation-cap" class="size-4 text-primary"></i> Data Akademik Awal
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-white border border-border rounded-2xl">

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Program Keahlian / Konsentrasi <span class="text-error">*</span></label>
                            <select name="concentration_id" required class="{{ $inputClass }} @error('concentration_id') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Program Keahlian —</option>
                                {{-- Looping data konsentrasi dari controller (Harus dikirim dari backend) --}}
                                @foreach($concentrations ?? [] as $concentration)
                                <option value="{{ $concentration->id }}" @selected(old('concentration_id')==$concentration->id)>{{ $concentration->name }}</option>
                                @endforeach
                            </select>
                            @error('concentration_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Tanggal Diterima <span class="text-error">*</span></label>
                            <input type="date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}" required class="{{ $inputClass }} @error('entry_date') {{ $errorClass }} @enderror">
                            @error('entry_date') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Diterima di Kelas <span class="text-error">*</span></label>
                            <select name="entry_grade_level" required class="{{ $inputClass }} @error('entry_grade_level') {{ $errorClass }} @enderror">
                                <option value="10" @selected(old('entry_grade_level')=='10' )>Kelas 10</option>
                                <option value="11" @selected(old('entry_grade_level')=='11' )>Kelas 11</option>
                                <option value="12" @selected(old('entry_grade_level')=='12' )>Kelas 12</option>
                            </select>
                            @error('entry_grade_level') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Jenis Pendaftaran <span class="text-error">*</span></label>
                            <div class="flex items-center gap-6 mt-2">
                                <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                                    <input type="radio" name="registration_type" value="new" @checked(old('registration_type', 'new' )==='new' ) class="accent-primary size-4"> Siswa Baru
                                </label>
                                <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                                    <input type="radio" name="registration_type" value="transfer" @checked(old('registration_type')==='transfer' ) class="accent-primary size-4"> Siswa Pindahan
                                </label>
                            </div>
                            @error('registration_type') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </form>
            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-3 shrink-0">
                <button type="button" @click="closeModal()"
                    class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                    Batal
                </button>

                <button type="submit" form="create-student-form" :disabled="isLoading"
                    class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow-sm shadow-indigo-600/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                    {{-- Tampilan Normal --}}
                    <div x-show="!isLoading" class="flex items-center gap-1.5">
                        <i data-lucide="save" class="size-4"></i>
                        <span>Simpan Data</span>
                    </div>

                    {{-- Tampilan Loading --}}
                    <div x-show="isLoading" x-cloak class="flex items-center gap-1.5">
                        <i data-lucide="loader-2" stroke-width="3" class="size-4 animate-spin"></i>
                        <span>Menyimpan...</span>
                    </div>
                </button>
            </div>

            {{-- Re-init Icons --}}
            <script>
                if (typeof lucide !== 'undefined') lucide.createIcons();
            </script>

        </div>
    </x-ui.modal>
</div>