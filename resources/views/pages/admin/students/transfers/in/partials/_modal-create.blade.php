{{-- File: resources/views/pages/admin/students/transfers/in/partials/_modal-create.blade.php --}}
<div x-data="{ 
        open: true,
        step: {{ $currentStep ?? 1 }},
        saving: false,
        closeModal() {
            this.open = false;
            setTimeout(() => {
                const container = document.getElementById('modal-form-container');
                if (container) container.innerHTML = '';
            }, 200);
        },
        // Fungsi untuk melompat ke tab yang memiliki error validasi dari Laravel
        checkErrors() {
            const firstError = document.querySelector('.border-error');
            if (firstError) {
                const stepContainer = firstError.closest('[data-step]');
                if (stepContainer) {
                    this.step = parseInt(stepContainer.getAttribute('data-step'));
                }
            }
        }
    }"
    x-init="setTimeout(() => checkErrors(), 100)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="closeModal()"
    @close-modal.window="closeModal()">

    <div class="bg-white sm:rounded-2xl w-full sm:max-w-3xl h-full sm:h-auto sm:max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="user-plus" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Tambah Siswa Pindahan</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Masukkan data peserta didik mutasi masuk.</p>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Stepper Navigation --}}
        @php
        $steps = [
        1 => ['label' => 'Identitas', 'icon' => 'user'],
        2 => ['label' => 'Kontak & Alamat', 'icon' => 'map-pin'],
        3 => ['label' => 'Orang Tua', 'icon' => 'users'],
        4 => ['label' => 'Sekolah Asal', 'icon' => 'school'],
        5 => ['label' => 'Penerimaan', 'icon' => 'calendar-check'],
        ];

        $inputClass = 'w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors placeholder:text-muted-foreground';
        $labelClass = 'block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5';
        $errorClass = 'border-error ring-1 ring-error/30';
        @endphp

        <div class="sticky top-0 z-20 bg-white border-b border-border shadow-sm shrink-0">
            <div class="flex items-center px-4 sm:px-10 pt-4 pb-3 sm:pb-7">
                @foreach($steps as $number => $stepItem)
                <div class="relative shrink-0">
                    <button type="button"
                        @click="step = {{ $number }}"
                        class="cursor-pointer relative z-10 flex items-center justify-center size-8 sm:size-9 rounded-full text-xs sm:text-sm font-bold transition-all duration-300"
                        :class="{
                            'bg-emerald-500 text-white hover:bg-emerald-600': step > {{ $number }},
                            'bg-primary text-white shadow-md shadow-primary/30 ring-4 ring-primary/15 scale-110': step === {{ $number }},
                            'bg-white text-secondary border-2 border-border hover:border-primary/50': step < {{ $number }}
                        }">
                        <span x-show="step <= {{ $number }}">{{ $number }}</span>
                        <i data-lucide="check" class="size-4" x-show="step > {{ $number }}" x-cloak></i>
                    </button>

                    <span class="absolute top-full mt-2 text-[10px] sm:text-[11px] font-semibold leading-tight transition-colors duration-300 text-center w-24 left-1/2 -translate-x-1/2 hidden sm:block"
                        :class="{
                            'text-emerald-600': step > {{ $number }},
                            'text-primary': step === {{ $number }},
                            'text-secondary': step < {{ $number }}
                        }">
                        {{ $stepItem['label'] }}
                    </span>
                </div>

                @if(!$loop->last)
                <div class="flex-1 h-[3px] rounded-full transition-colors duration-300"
                    :class="step > {{ $number }} ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Form HTMX --}}
        <form id="create-transfer-form"
            hx-post="{{ route('admin.students.transfer.in.store') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 min-h-0">
            @csrf

            {{-- INDIKATOR STEP SAAT INI UNTUK SERVER --}}
            <input type="hidden" name="current_step" :value="step">

            <div class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 [scrollbar-gutter:stable]">

                {{-- ========================================== --}}
                {{-- STEP 1: IDENTITAS PERSONAL                 --}}
                {{-- ========================================== --}}
                <div x-show="step === 1" data-step="1" x-transition.opacity.duration.300ms class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Nama Lengkap Siswa <span class="text-error">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap siswa" class="{{ $inputClass }} @error('name') {{ $errorClass }} @enderror">
                            @error('name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Nomor Induk Kependudukan (NIK) <span class="text-error">*</span></label>
                            <input type="text" name="nik" inputmode="numeric" maxlength="16" value="{{ old('nik') }}" placeholder="16 digit NIK" class="{{ $inputClass }} font-mono @error('nik') {{ $errorClass }} @enderror">
                            @error('nik') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">NISN <span class="text-error">*</span></label>
                            <input type="text" name="nisn" inputmode="numeric" maxlength="10" value="{{ old('nisn') }}" placeholder="Masukkan NISN" class="{{ $inputClass }} font-mono @error('nisn') {{ $errorClass }} @enderror">
                            @error('nisn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Tempat Lahir <span class="text-error">*</span></label>
                            <input type="text" name="pob" value="{{ old('pob') }}" placeholder="Kota kelahiran" class="{{ $inputClass }} @error('pob') {{ $errorClass }} @enderror">
                            @error('pob') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Date Input --}}
                        <div>
                            <label class="{{ $labelClass }}">Tanggal Lahir <span class="text-error">*</span></label>
                            <input type="date" name="dob" value="{{ old('dob') }}" class="{{ $inputClass }} @error('dob') {{ $errorClass }} @enderror">
                            @error('dob') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Jenis Kelamin <span class="text-error">*</span></label>
                            <select name="gender" class="{{ $inputClass }} @error('gender') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jenis Kelamin —</option>
                                @foreach(\App\Enums\Student\Gender::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('gender')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('gender') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Agama <span class="text-error">*</span></label>
                            <select name="religion" class="{{ $inputClass }} @error('religion') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Agama —</option>
                                @foreach(\App\Enums\Student\Religion::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('religion')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('religion') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Anak Ke (Opsional)</label>
                            <input type="number" min="1" name="child_order" value="{{ old('child_order') }}" placeholder="Contoh: 1" class="{{ $inputClass }} @error('child_order') {{ $errorClass }} @enderror">
                            @error('child_order') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Jumlah Saudara (Opsional)</label>
                            <input type="number" min="0" name="number_of_siblings" value="{{ old('number_of_siblings') }}" placeholder="Contoh: 2" class="{{ $inputClass }} @error('number_of_siblings') {{ $errorClass }} @enderror">
                            @error('number_of_siblings') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- STEP 2: ALAMAT & KONTAK                    --}}
                {{-- ========================================== --}}
                <div x-show="step === 2" data-step="2" x-transition.opacity.duration.300ms x-cloak class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Nomor Telepon / WhatsApp (Opsional)</label>
                            <input type="text" name="phone_number" inputmode="tel" value="{{ old('phone_number') }}" placeholder="Contoh: 081234567890" class="{{ $inputClass }} font-mono @error('phone_number') {{ $errorClass }} @enderror">
                            @error('phone_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Email (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Contoh: nama@email.com" class="{{ $inputClass }} @error('email') {{ $errorClass }} @enderror">
                            @error('email') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Jenis Tempat Tinggal <span class="text-error">*</span></label>
                            <select name="residence_type" class="{{ $inputClass }} @error('residence_type') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jenis Tempat Tinggal —</option>
                                @foreach(\App\Enums\Student\ResidenceType::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('residence_type')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('residence_type') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Moda Transportasi <span class="text-error">*</span></label>
                            <select name="transportation" class="{{ $inputClass }} @error('transportation') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Moda Transportasi —</option>
                                @foreach(\App\Enums\Student\Transportation::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('transportation')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('transportation') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Jarak ke Sekolah (Opsional)</label>
                            <select name="distance_to_school" class="{{ $inputClass }} @error('distance_to_school') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jarak ke Sekolah —</option>
                                @foreach(\App\Enums\Student\DistanceToSchool::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('distance_to_school')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('distance_to_school') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="{{ $labelClass }} px-1">Alamat Lengkap Siswa <span class="text-error">*</span></label>
                        <textarea name="address" rows="2" placeholder="Masukkan alamat jalan dan nomor rumah" class="{{ $inputClass }} resize-none @error('address') {{ $errorClass }} @enderror">{{ old('address') }}</textarea>
                        @error('address') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- BARIS 1: RT (1/4), RW (1/4), Desa (1/2) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                        <div class="col-span-1">
                            <label class="{{ $labelClass }}">RT (Opsional)</label>
                            <input type="text" name="rt" maxlength="5" value="{{ old('rt') }}" placeholder="Contoh: 001" class="{{ $inputClass }} @error('rt') {{ $errorClass }} @enderror">
                            @error('rt') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-1">
                            <label class="{{ $labelClass }}">RW (Opsional)</label>
                            <input type="text" name="rw" maxlength="5" value="{{ old('rw') }}" placeholder="Contoh: 002" class="{{ $inputClass }} @error('rw') {{ $errorClass }} @enderror">
                            @error('rw') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="{{ $labelClass }}">Kel/Desa <span class="text-error">*</span></label>
                            <input type="text" name="village" value="{{ old('village') }}" placeholder="Masukkan kelurahan/desa" class="{{ $inputClass }} @error('village') {{ $errorClass }} @enderror">
                            @error('village') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BARIS 2: Kecamatan (1/2), Kabupaten (1/2) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="{{ $labelClass }}">Kecamatan <span class="text-error">*</span></label>
                            <input type="text" name="district" value="{{ old('district') }}" placeholder="Masukkan kecamatan" class="{{ $inputClass }} @error('district') {{ $errorClass }} @enderror">
                            @error('district') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Kabupaten/Kota <span class="text-error">*</span></label>
                            <input type="text" name="regency" value="{{ old('regency') }}" placeholder="Masukkan kabupaten/kota" class="{{ $inputClass }} @error('regency') {{ $errorClass }} @enderror">
                            @error('regency') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BARIS 3: Provinsi (1/2), Kode Pos (1/2) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="{{ $labelClass }}">Provinsi <span class="text-error">*</span></label>
                            <input type="text" name="province" value="{{ old('province') }}" placeholder="Masukkan provinsi" class="{{ $inputClass }} @error('province') {{ $errorClass }} @enderror">
                            @error('province') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Kode Pos (Opsional)</label>
                            <input type="text" name="postal_code" inputmode="numeric" maxlength="10" value="{{ old('postal_code') }}" placeholder="Contoh: 30111" class="{{ $inputClass }} @error('postal_code') {{ $errorClass }} @enderror">
                            @error('postal_code') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- STEP 3: ORANG TUA / WALI                   --}}
                {{-- ========================================== --}}
                <div x-show="step === 3" data-step="3" x-transition.opacity.duration.300ms x-cloak class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Nama Orang Tua / Wali <span class="text-error">*</span></label>
                            <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Masukkan nama orang tua atau wali" class="{{ $inputClass }} @error('guardian_name') {{ $errorClass }} @enderror">
                            @error('guardian_name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Hubungan Keluarga <span class="text-error">*</span></label>
                            <select name="guardian_relationship" class="{{ $inputClass }} @error('guardian_relationship') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Hubungan —</option>
                                @foreach(\App\Enums\Student\FamilyRelation::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('guardian_relationship')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('guardian_relationship') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">NIK Wali (Opsional)</label>
                            <input type="text" name="guardian_nik" inputmode="numeric" maxlength="16" value="{{ old('guardian_nik') }}" placeholder="16 digit NIK" class="{{ $inputClass }} font-mono @error('guardian_nik') {{ $errorClass }} @enderror">
                            @error('guardian_nik') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Tahun Lahir (Opsional)</label>
                            <input type="number" name="guardian_birth_year" min="1900" max="{{ date('Y') }}" value="{{ old('guardian_birth_year') }}" placeholder="Contoh: 1980" class="{{ $inputClass }} @error('guardian_birth_year') {{ $errorClass }} @enderror">
                            @error('guardian_birth_year') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Pendidikan Terakhir (Opsional)</label>
                            <select name="guardian_education" class="{{ $inputClass }} @error('guardian_education') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Pendidikan —</option>
                                @foreach(\App\Enums\Student\Education::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('guardian_education')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('guardian_education') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Pekerjaan (Opsional)</label>
                            <select name="guardian_occupation" class="{{ $inputClass }} @error('guardian_occupation') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Pekerjaan —</option>
                                @foreach(\App\Enums\Student\Profession::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('guardian_occupation')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('guardian_occupation') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Penghasilan Bulanan (Opsional)</label>
                            <select name="guardian_income_range" class="{{ $inputClass }} @error('guardian_income_range') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Rentang Penghasilan —</option>
                                @foreach(\App\Enums\Student\Income::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('guardian_income_range')===$option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('guardian_income_range') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Nomor Telepon / WhatsApp (Opsional)</label>
                            <input type="text" name="guardian_phone" inputmode="tel" value="{{ old('guardian_phone') }}" placeholder="Contoh: 081234567890" class="{{ $inputClass }} font-mono @error('guardian_phone') {{ $errorClass }} @enderror">
                            @error('guardian_phone') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Alamat Tempat Tinggal (Opsional)</label>
                            <textarea name="guardian_address" rows="2" placeholder="Alamat lengkap wali/orang tua" class="{{ $inputClass }} resize-none @error('guardian_address') {{ $errorClass }} @enderror">{{ old('guardian_address') }}</textarea>
                            @error('guardian_address') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- STEP 4: SEKOLAH ASAL                       --}}
                {{-- ========================================== --}}
                <div x-show="step === 4" data-step="4" x-transition.opacity.duration.300ms x-cloak class="space-y-6">

                    {{-- SEKSI 1: DATA SEKOLAH ASAL PINDAHAN --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Nama Sekolah Asal Pindahan <span class="text-error">*</span></label>
                            <input type="text" name="origin_school" value="{{ old('origin_school') }}" placeholder="Contoh: SMK Negeri 2 Bengkulu" class="{{ $inputClass }} @error('origin_school') {{ $errorClass }} @enderror">
                            @error('origin_school') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">NPSN Sekolah Asal (Opsional)</label>
                            <input type="text" name="origin_school_npsn" value="{{ old('origin_school_npsn') }}" placeholder="Masukkan NPSN sekolah asal" class="{{ $inputClass }} font-mono @error('origin_school_npsn') {{ $errorClass }} @enderror">
                            @error('origin_school_npsn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Kabupaten/Kota Sekolah Asal (Opsional)</label>
                            <input type="text" name="origin_school_city" value="{{ old('origin_school_city') }}" placeholder="Contoh: Rejang Lebong" class="{{ $inputClass }} @error('origin_school_city') {{ $errorClass }} @enderror">
                            @error('origin_school_city') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Provinsi Sekolah Asal (Opsional)</label>
                            <input type="text" name="origin_school_province" value="{{ old('origin_school_province') }}" placeholder="Contoh: Bengkulu" class="{{ $inputClass }} @error('origin_school_province') {{ $errorClass }} @enderror">
                            @error('origin_school_province') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- SEKSI 2: DATA KELULUSAN SEBELUMNYA --}}
                    <div class="border-t border-border pt-5">
                        <h4 class="text-xs font-bold text-foreground mb-3 flex items-center gap-2">
                            <i data-lucide="award" class="size-4 text-primary"></i> Data Pendidikan Sebelumnya (SMP / Sederajat)
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="{{ $labelClass }}">Nama Sekolah Kelulusan (Opsional)</label>
                                <input type="text" name="previous_school" value="{{ old('previous_school') }}" placeholder="Contoh: SMP Negeri 1 Rejang Lebong" class="{{ $inputClass }} @error('previous_school') {{ $errorClass }} @enderror">
                                @error('previous_school') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">NPSN Sekolah Kelulusan (Opsional)</label>
                                <input type="text" name="previous_school_npsn" value="{{ old('previous_school_npsn') }}" placeholder="Masukkan NPSN" class="{{ $inputClass }} font-mono @error('previous_school_npsn') {{ $errorClass }} @enderror">
                                @error('previous_school_npsn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Status Sekolah (Opsional)</label>
                                <select name="previous_school_status" class="{{ $inputClass }} @error('previous_school_status') {{ $errorClass }} @enderror">
                                    <option value="">— Pilih Status —</option>
                                    <option value="negeri" @selected(old('previous_school_status')==='negeri' )>Negeri</option>
                                    <option value="swasta" @selected(old('previous_school_status')==='swasta' )>Swasta</option>
                                </select>
                                @error('previous_school_status') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Kabupaten/Kota Kelulusan (Opsional)</label>
                                <input type="text" name="previous_school_city" value="{{ old('previous_school_city') }}" placeholder="Contoh: Rejang Lebong" class="{{ $inputClass }} @error('previous_school_city') {{ $errorClass }} @enderror">
                                @error('previous_school_city') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Provinsi Kelulusan (Opsional)</label>
                                <input type="text" name="previous_school_province" value="{{ old('previous_school_province') }}" placeholder="Contoh: Bengkulu" class="{{ $inputClass }} @error('previous_school_province') {{ $errorClass }} @enderror">
                                @error('previous_school_province') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Nomor Ijazah (Opsional)</label>
                                <input type="text" name="graduation_certificate_number" value="{{ old('graduation_certificate_number') }}" placeholder="Nomor seri ijazah" class="{{ $inputClass }} @error('graduation_certificate_number') {{ $errorClass }} @enderror">
                                @error('graduation_certificate_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="{{ $labelClass }}">Tahun Lulus (Opsional)</label>
                                <input type="text" name="graduation_year" inputmode="numeric" maxlength="4" value="{{ old('graduation_year') }}" placeholder="Contoh: 2024" class="{{ $inputClass }} @error('graduation_year') {{ $errorClass }} @enderror">
                                @error('graduation_year') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- STEP 5: INFORMASI PENERIMAAN               --}}
                {{-- ========================================== --}}
                <div x-show="step === 5" data-step="5" x-transition.opacity.duration.300ms x-cloak class="space-y-4">

                    <div class="bg-cyan-50 border border-cyan-100 text-cyan-700 text-xs rounded-xl px-3.5 py-2.5 flex items-start gap-2 mb-4">
                        <i data-lucide="info" class="size-4 shrink-0 mt-0.5"></i>
                        <span>Pastikan Rombel Tujuan sudah benar. Tingkat kelas dan jurusan siswa akan otomatis mengikuti rombongan belajar yang dipilih.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Rombongan Belajar Tujuan <span class="text-error">*</span></label>
                            <select name="class_group_id" class="{{ $inputClass }} @error('class_group_id') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Rombel —</option>
                                @forelse ($classGroups as $cg)
                                <option value="{{ $cg->id }}" @selected(old('class_group_id')==$cg->id)>
                                    {{ $cg->name ?? ('Kelas ' . $cg->grade_level . ' - ' . $cg->group_number) }}
                                    &mdash; {{ optional($cg->concentration)->name }}
                                </option>
                                @empty
                                <option value="" disabled>Tidak ada rombel aktif pada tahun ajaran ini</option>
                                @endforelse
                            </select>
                            @error('class_group_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Date Input --}}
                        <div>
                            <label class="{{ $labelClass }}">Tanggal Mutasi Masuk / Diterima <span class="text-error">*</span></label>
                            <input
                                type="date"
                                name="entry_date"
                                value="{{ old('entry_date') }}"
                                class="{{ $inputClass }} @error('entry_date') {{ $errorClass }} @enderror">
                            @error('entry_date')
                            <span class="text-error text-[10px] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" rows="3" placeholder="Tambahkan catatan khusus mutasi jika ada..." class="{{ $inputClass }} resize-none @error('notes') {{ $errorClass }} @enderror">{{ old('notes') }}</textarea>
                            @error('notes') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-between gap-2 shrink-0">
                <div>
                    {{-- Tombol Batal (Hanya muncul di step 1) --}}
                    <button type="button" x-show="step === 1" @click="closeModal()"
                        class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                        Batal
                    </button>

                    {{-- Tombol Kembali (Muncul di step > 1) --}}
                    <button type="button" x-show="step > 1" x-cloak @click="step--"
                        class="flex items-center gap-1.5 px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                        <i data-lucide="arrow-left" class="size-4"></i>
                        <span>Kembali</span>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline text-xs text-secondary font-medium">Langkah <span x-text="step"></span> dari 5</span>

                    {{-- Tombol Lanjut dengan Validasi Server --}}
                    <button type="button" x-show="step < 5"
                        hx-post="{{ route('admin.students.transfer.in.validate-step') }}"
                        hx-target="#modal-form-container"
                        hx-swap="innerHTML"
                        class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer">
                        <div class="flex items-center gap-1.5">
                            <span>Lanjut</span>
                            <i data-lucide="arrow-right" class="size-4"></i>
                        </div>
                    </button>

                    {{-- Tombol Submit / Simpan (Hanya di Step Terakhir) --}}
                    <button type="submit" x-show="step === 5" x-cloak :disabled="saving"
                        class="flex items-center justify-center min-w-[160px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                        <div x-show="!saving" class="flex items-center gap-1.5">
                            <i data-lucide="save" class="size-4"></i>
                            <span>Simpan Data</span>
                        </div>

                        <div x-show="saving" x-cloak class="flex items-center gap-1.5">
                            <i data-lucide="loader-2" stroke-width="3" class="size-4 animate-spin"></i>
                            <span>Menyimpan...</span>
                        </div>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>