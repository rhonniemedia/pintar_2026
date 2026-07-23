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

    <x-ui.modal show="open" maxWidth="3xl">

        {{-- WADAH BARU: HTMX hanya akan mengganti elemen div ini beserta isinya, modal luarnya tidak tersentuh --}}
        <div id="edit-modal-content" class="flex flex-col flex-1 min-h-0"
            x-data="{ 
                isSpecial: '{{ old('is_special_condition', $student->is_special_condition ?? 'no') }}',
                isLoading: false
            }">

            {{-- Modal Header --}}
            <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="size-11 sm:size-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="pencil" class="size-5 sm:size-6"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Edit Data Peserta Didik</h3>
                        <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate uppercase">{{ $student->name }}</p>
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
            2 => ['label' => 'Alamat', 'icon' => 'map-pin'],
            3 => ['label' => 'Orangtua', 'icon' => 'users'],
            4 => ['label' => 'Akademik', 'icon' => 'graduation-cap'],
            5 => ['label' => 'Kesehatan', 'icon' => 'activity'],
            ];
            $activeStep = (int) ($currentStep ?? 1);
            @endphp
            <div class="sticky top-0 z-20 bg-white border-b border-border shadow-sm">
                <div class="flex items-center px-6 sm:px-10 pt-5 pb-4 sm:pb-9">
                    @foreach($steps as $number => $step)
                    <div class="relative shrink-0">
                        <button type="button"
                            @if($number < $activeStep)
                            hx-get="{{ route('admin.students.edit.personal', ['id' => $student->id, 'step' => $number]) }}"
                            hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML"
                            class="cursor-pointer relative z-10 flex items-center justify-center size-8 sm:size-9 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 bg-emerald-500 text-white hover:bg-emerald-600"
                            @else
                            disabled
                            class="disabled:cursor-not-allowed relative z-10 flex items-center justify-center size-8 sm:size-9 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 {{ $activeStep === $number ? 'bg-primary text-white shadow-md shadow-primary/30 ring-4 ring-primary/15 scale-110' : 'bg-white text-secondary border-2 border-border' }}"
                            @endif>
                            @if($activeStep > $number)
                            <i data-lucide="check" class="size-4"></i>
                            @else
                            <span>{{ $number }}</span>
                            @endif
                        </button>

                        <span class="absolute top-full mt-2 whitespace-nowrap text-[11px] font-semibold leading-tight transition-colors duration-300 hidden sm:block {{ $loop->first ? 'left-0' : ($loop->last ? 'right-0' : 'left-1/2 -translate-x-1/2') }} {{ $activeStep > $number ? 'text-emerald-600' : ($activeStep === $number ? 'text-primary' : 'text-secondary') }}">
                            {{ $step['label'] }}
                        </span>
                    </div>
                    @if(!$loop->last)
                    <div class="flex-1 h-[3px] rounded-full transition-colors duration-300 {{ $activeStep > ($number) ? 'bg-emerald-500' : ($activeStep === ($number + 1) ? 'bg-primary' : 'bg-slate-200') }}"></div>
                    @endif
                    @endforeach
                </div>
            </div>

            @php
            $inputClass = 'w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors placeholder:text-muted-foreground';
            $labelClass = 'block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5';
            $errorClass = 'border-error ring-1 ring-error/30';
            @endphp

            <div id="edit-modal-scroll-area" class="block p-4 sm:p-6 overflow-y-auto max-h-[55vh] sm:max-h-[60vh] bg-slate-50/30 flex-1 [scrollbar-gutter:stable]">

                @if($activeStep === 1)
                {{-- STEP 1: IDENTITAS DIRI --}}
                <form id="edit-student-form"
                    @htmx:before-request="isLoading = true"
                    @htmx:after-request="isLoading = false"
                    hx-put="{{ route('admin.students.edit.personal.update', ['id' => $student->id, 'step' => 1]) }}"
                    hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                        {{-- Baris 1: Nama Lengkap (Full Width) --}}
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Nama Lengkap <span class="text-error">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $student->name) }}" placeholder="Masukkan nama lengkap" class="{{ $inputClass }} @error('name') {{ $errorClass }} @enderror">
                            @error('name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Baris 2: NIK & NISN --}}
                        <div>
                            <label class="{{ $labelClass }}">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" name="nik" inputmode="numeric" maxlength="16" value="{{ old('nik', $student->vault->nik_encrypted ?? '') }}" placeholder="Masukkan 16 digit NIK" class="{{ $inputClass }} font-mono @error('nik') {{ $errorClass }} @enderror">
                            @error('nik') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">NISN <span class="text-error">*</span></label>
                            <input type="text" name="nisn" inputmode="numeric" maxlength="10" value="{{ old('nisn', $student->vault->nisn_encrypted ?? '') }}" placeholder="Masukkan NISN" class="{{ $inputClass }} font-mono @error('nisn') {{ $errorClass }} @enderror">
                            @error('nisn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Baris 3: Tempat & Tanggal Lahir --}}
                        <div>
                            <label class="{{ $labelClass }}">Tempat Lahir</label>
                            <input type="text" name="pob" value="{{ old('pob', $student->vault->pob_encrypted ?? '') }}" placeholder="Masukkan tempat lahir" class="{{ $inputClass }} @error('pob') {{ $errorClass }} @enderror">
                            @error('pob') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Tanggal Lahir</label>
                            <input type="date" name="dob"
                                value="{{ old('dob', $student->vault->dob_encrypted ? \Carbon\Carbon::parse($student->vault->dob_encrypted)->format('Y-m-d') : '') }}"
                                class="{{ $inputClass }} @error('dob') {{ $errorClass }} @enderror">
                            @error('dob') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Baris 4: Jenis Kelamin & Agama --}}
                        <div>
                            <label class="{{ $labelClass }}">Jenis Kelamin <span class="text-error">*</span></label>
                            <select name="gender" class="{{ $inputClass }} @error('gender') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jenis Kelamin —</option>
                                @foreach(\App\Enums\Student\Gender::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('gender', $student->gender?->value) === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('gender') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Agama</label>
                            <select name="religion" class="{{ $inputClass }} @error('religion') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Agama —</option>
                                @foreach(\App\Enums\Student\Religion::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('religion', $student->vault->religion?->value) === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('religion') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Baris 5: Anak Ke & Jumlah Saudara --}}
                        <div>
                            <label class="{{ $labelClass }}">Anak Ke</label>
                            <input type="number" name="child_order" min="1" value="{{ old('child_order', $student->child_order) }}" placeholder="Contoh: 1" class="{{ $inputClass }} @error('child_order') {{ $errorClass }} @enderror">
                            @error('child_order') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Jumlah Saudara</label>
                            <input type="number" name="number_of_siblings" min="0" value="{{ old('number_of_siblings', $student->number_of_siblings) }}" placeholder="Contoh: 2" class="{{ $inputClass }} @error('number_of_siblings') {{ $errorClass }} @enderror">
                            @error('number_of_siblings') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </form>
                @endif

                @if($activeStep === 2)
                {{-- STEP 2: ALAMAT & KONTAK --}}
                <form id="edit-student-form"
                    @htmx:before-request="isLoading = true"
                    @htmx:after-request="isLoading = false"
                    hx-put="{{ route('admin.students.edit.personal.update', ['id' => $student->id, 'step' => 2]) }}"
                    hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone_number" inputmode="tel" value="{{ old('phone_number', $student->vault->phone_number_encrypted ?? '') }}" placeholder="Contoh: 081234567890" class="{{ $inputClass }} font-mono @error('phone_number') {{ $errorClass }} @enderror">
                            @error('phone_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Email</label>
                            <input type="email" name="email" value="{{ old('email', $student->vault->email_encrypted ?? '') }}" placeholder="Contoh: nama@email.com" class="{{ $inputClass }} @error('email') {{ $errorClass }} @enderror">
                            @error('email') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Jenis Tempat Tinggal</label>
                            <select name="residence_type" class="{{ $inputClass }} @error('residence_type') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jenis Tempat Tinggal —</option>
                                @foreach(\App\Enums\Student\ResidenceType::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('residence_type', $student->residence_type?->value) === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('residence_type') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Moda Transportasi</label>
                            <select name="transportation" class="{{ $inputClass }} @error('transportation') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Moda Transportasi —</option>
                                @foreach(\App\Enums\Student\Transportation::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('transportation', $student->transportation?->value) === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('transportation') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Jarak ke Sekolah</label>
                            <select name="distance_to_school" class="{{ $inputClass }} @error('distance_to_school') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jarak ke Sekolah —</option>
                                @foreach(\App\Enums\Student\DistanceToSchool::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('distance_to_school', $student->distance_to_school?->value) === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            @error('distance_to_school') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="{{ $labelClass }} px-1">Alamat Lengkap Siswa</label>
                        <textarea name="address" rows="2" placeholder="Masukkan alamat jalan dan nomor rumah" class="{{ $inputClass }} resize-none @error('address') {{ $errorClass }} @enderror">{{ old('address', $student->vault->address_encrypted ?? '') }}</textarea>
                        @error('address') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- BARIS 1: RT (1/4), RW (1/4), Desa (1/2) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                        <div class="col-span-1">
                            <label class="{{ $labelClass }}">RT</label>
                            <input type="text" name="rt" maxlength="5" value="{{ old('rt', $student->vault->rt_encrypted ?? '') }}" placeholder="Contoh: 001" class="{{ $inputClass }} @error('rt') {{ $errorClass }} @enderror">
                            @error('rt') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-1">
                            <label class="{{ $labelClass }}">RW</label>
                            <input type="text" name="rw" maxlength="5" value="{{ old('rw', $student->vault->rw_encrypted ?? '') }}" placeholder="Contoh: 002" class="{{ $inputClass }} @error('rw') {{ $errorClass }} @enderror">
                            @error('rw') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="{{ $labelClass }}">Kel/Desa</label>
                            <input type="text" name="village" value="{{ old('village', $student->vault->village_encrypted ?? '') }}" placeholder="Masukkan kelurahan/desa" class="{{ $inputClass }} @error('village') {{ $errorClass }} @enderror">
                            @error('village') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BARIS 2: Kecamatan (1/2), Kabupaten (1/2) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="{{ $labelClass }}">Kecamatan</label>
                            <input type="text" name="district" value="{{ old('district', $student->vault->district_encrypted ?? '') }}" placeholder="Masukkan kecamatan" class="{{ $inputClass }} @error('district') {{ $errorClass }} @enderror">
                            @error('district') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Kabupaten/Kota</label>
                            <input type="text" name="regency" value="{{ old('regency', $student->vault->regency_encrypted ?? '') }}" placeholder="Masukkan kabupaten/kota" class="{{ $inputClass }} @error('regency') {{ $errorClass }} @enderror">
                            @error('regency') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BARIS 3: Provinsi (1/2), Kode Pos (1/2) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="{{ $labelClass }}">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province', $student->vault->province_encrypted ?? '') }}" placeholder="Masukkan provinsi" class="{{ $inputClass }} @error('province') {{ $errorClass }} @enderror">
                            @error('province') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Kode Pos</label>
                            <input type="text" name="postal_code" inputmode="numeric" maxlength="10" value="{{ old('postal_code', $student->vault->postal_code_encrypted ?? '') }}" placeholder="Contoh: 30111" class="{{ $inputClass }} @error('postal_code') {{ $errorClass }} @enderror">
                            @error('postal_code') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </form>
                @endif

                @if($activeStep === 3)
                {{-- STEP 3: ORANGTUA / WALI --}}
                @php
                $hasWali = $student->guardians->contains(fn($g) => $g->relationship === \App\Enums\Student\FamilyRelation::WALI || $g->relationship === 'guardian');
                @endphp
                <form id="edit-student-form"
                    x-data="{ showWali: {{ $hasWali ? 'true' : 'false' }} }"
                    @htmx:before-request="isLoading = true"
                    @htmx:after-request="isLoading = false"
                    hx-put="{{ route('admin.students.edit.personal.update', ['id' => $student->id, 'step' => 3]) }}"
                    hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML">
                    @csrf @method('PUT')

                    <div class="space-y-6">
                        @foreach(\App\Enums\Student\FamilyRelation::cases() as $relation)
                        @php
                        $relValue = $relation->value;
                        $relLabel = $relation->label();
                        $isRequired = in_array($relValue, ['father', 'mother']);
                        $guardian = $student->guardians->first(fn($g) => $g->relationship === $relation || $g->relationship === $relValue);
                        @endphp

                        <div class="{{ $loop->first ? '' : 'pt-6 border-t border-border/80' }}"
                            @if($relValue==='guardian' ) x-show="showWali" x-transition.opacity x-cloak @endif>

                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="user" class="size-4 text-secondary"></i>
                                    <h4 class="font-bold text-foreground text-sm uppercase tracking-wide">Data {{ $relLabel }}</h4>
                                </div>
                                @if($relValue === 'guardian')
                                <button type="button" @click="showWali = false" class="text-xs font-semibold text-error hover:bg-error/10 px-2.5 py-1.5 rounded-lg flex items-center gap-1 cursor-pointer transition-colors">
                                    <i data-lucide="trash-2" class="size-3.5"></i> Batal / Hapus
                                </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                @if($relValue === 'guardian')
                                {{-- ============================== --}}
                                {{-- LAYOUT DATA WALI        --}}
                                {{-- ============================== --}}

                                {{-- Baris 1: Nama Lengkap --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">Nama Lengkap</label>
                                    <input type="text" name="guardians[{{ $relValue }}][name]" value="{{ old('guardians.'.$relValue.'.name', $guardian?->name ?? '') }}" placeholder="Masukkan nama lengkap {{ $relLabel }}" class="{{ $inputClass }} @error('guardians.'.$relValue.'.name') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror

                                    {{-- Hidden input status hidup --}}
                                    <input type="hidden" name="guardians[{{ $relValue }}][living_status]" value="alive">
                                </div>

                                {{-- Baris 2: Tahun Lahir & NIK --}}
                                <div>
                                    <label class="{{ $labelClass }}">Tahun Lahir</label>
                                    <input type="text" name="guardians[{{ $relValue }}][birth_year]" inputmode="numeric" maxlength="4" value="{{ old('guardians.'.$relValue.'.birth_year', $guardian?->birth_year ?? '') }}" placeholder="Contoh: 1985" class="{{ $inputClass }} @error('guardians.'.$relValue.'.birth_year') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.birth_year') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Nomor Induk Kependudukan (NIK)</label>
                                    <input type="text" name="guardians[{{ $relValue }}][nik]" inputmode="numeric" maxlength="16" value="{{ old('guardians.'.$relValue.'.nik', $guardian?->vault?->nik_encrypted ?? '') }}" placeholder="Masukkan 16 digit NIK" class="{{ $inputClass }} font-mono @error('guardians.'.$relValue.'.nik') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.nik') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 3: Pendidikan Terakhir & No Telepon/WA --}}
                                <div>
                                    <label class="{{ $labelClass }}">Pendidikan Terakhir</label>
                                    <select name="guardians[{{ $relValue }}][education]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.education') {{ $errorClass }} @enderror">
                                        <option value="">— Pilih Pendidikan Terakhir —</option>
                                        @foreach(\App\Enums\Student\Education::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.education', $guardian?->education?->value) === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.education') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">No Telepon / WhatsApp</label>
                                    <input type="text" name="guardians[{{ $relValue }}][phone_number]" inputmode="tel" value="{{ old('guardians.'.$relValue.'.phone_number', $guardian?->vault?->phone_number_encrypted ?? '') }}" placeholder="Contoh: 081234567890" class="{{ $inputClass }} font-mono @error('guardians.'.$relValue.'.phone_number') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.phone_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 4: Pekerjaan & Rentang Penghasilan --}}
                                <div>
                                    <label class="{{ $labelClass }}">Pekerjaan</label>
                                    <select name="guardians[{{ $relValue }}][occupation]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.occupation') {{ $errorClass }} @enderror">
                                        <option value="">— Pilih Pekerjaan —</option>
                                        @foreach(\App\Enums\Student\Profession::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.occupation', $guardian?->occupation?->value) === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.occupation') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Rentang Penghasilan</label>
                                    <select name="guardians[{{ $relValue }}][income_range]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.income_range') {{ $errorClass }} @enderror">
                                        <option value="">— Pilih Rentang Penghasilan —</option>
                                        @foreach(\App\Enums\Student\Income::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.income_range', $guardian?->income_range?->value) === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.income_range') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 5: Alamat Lengkap --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">Alamat Lengkap</label>
                                    <textarea name="guardians[{{ $relValue }}][address]" rows="2" placeholder="Masukkan alamat jalan dan nomor rumah {{ $relLabel }}" class="{{ $inputClass }} resize-none @error('guardians.'.$relValue.'.address') {{ $errorClass }} @enderror">{{ old('guardians.'.$relValue.'.address', $guardian?->vault?->address_encrypted ?? '') }}</textarea>
                                    @error('guardians.'.$relValue.'.address') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                @else
                                {{-- ============================== --}}
                                {{-- LAYOUT DATA AYAH / IBU     --}}
                                {{-- ============================== --}}

                                {{-- Baris 1: Nama Orangtua --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">Nama Lengkap @if($isRequired)<span class="text-error">*</span>@endif</label>
                                    <input type="text" name="guardians[{{ $relValue }}][name]" value="{{ old('guardians.'.$relValue.'.name', $guardian?->name ?? '') }}" placeholder="Masukkan nama lengkap {{ $relLabel }}" class="{{ $inputClass }} @error('guardians.'.$relValue.'.name') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 2: Status & NIK --}}
                                <div>
                                    <label class="{{ $labelClass }}">Status Kehidupan @if($isRequired)<span class="text-error">*</span>@endif</label>
                                    <select name="guardians[{{ $relValue }}][living_status]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.living_status') {{ $errorClass }} @enderror">
                                        @foreach(\App\Enums\Student\LivingStatus::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.living_status', $guardian?->living_status?->value ?? 'alive') === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.living_status') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Nomor Induk Kependudukan (NIK)</label>
                                    <input type="text" name="guardians[{{ $relValue }}][nik]" inputmode="numeric" maxlength="16" value="{{ old('guardians.'.$relValue.'.nik', $guardian?->vault?->nik_encrypted ?? '') }}" placeholder="Masukkan 16 digit NIK" class="{{ $inputClass }} font-mono @error('guardians.'.$relValue.'.nik') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.nik') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 3: Tahun Lahir & Telepon --}}
                                <div>
                                    <label class="{{ $labelClass }}">Tahun Lahir</label>
                                    <input type="text" name="guardians[{{ $relValue }}][birth_year]" inputmode="numeric" maxlength="4" value="{{ old('guardians.'.$relValue.'.birth_year', $guardian?->birth_year ?? '') }}" placeholder="Contoh: 1985" class="{{ $inputClass }} @error('guardians.'.$relValue.'.birth_year') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.birth_year') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">No Telepon / WhatsApp</label>
                                    <input type="text" name="guardians[{{ $relValue }}][phone_number]" inputmode="tel" value="{{ old('guardians.'.$relValue.'.phone_number', $guardian?->vault?->phone_number_encrypted ?? '') }}" placeholder="Contoh: 081234567890" class="{{ $inputClass }} font-mono @error('guardians.'.$relValue.'.phone_number') {{ $errorClass }} @enderror">
                                    @error('guardians.'.$relValue.'.phone_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 4: Pendidikan & Pekerjaan --}}
                                <div>
                                    <label class="{{ $labelClass }}">Pendidikan Terakhir</label>
                                    <select name="guardians[{{ $relValue }}][education]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.education') {{ $errorClass }} @enderror">
                                        <option value="">— Pilih Pendidikan Terakhir —</option>
                                        @foreach(\App\Enums\Student\Education::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.education', $guardian?->education?->value) === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.education') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Pekerjaan</label>
                                    <select name="guardians[{{ $relValue }}][occupation]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.occupation') {{ $errorClass }} @enderror">
                                        <option value="">— Pilih Pekerjaan —</option>
                                        @foreach(\App\Enums\Student\Profession::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.occupation', $guardian?->occupation?->value) === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.occupation') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 5: Penghasilan (Full Width) --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">Rentang Penghasilan</label>
                                    <select name="guardians[{{ $relValue }}][income_range]" class="{{ $inputClass }} @error('guardians.'.$relValue.'.income_range') {{ $errorClass }} @enderror">
                                        <option value="">— Pilih Rentang Penghasilan —</option>
                                        @foreach(\App\Enums\Student\Income::cases() as $option)
                                        <option value="{{ $option->value }}" @selected(old('guardians.'.$relValue.'.income_range', $guardian?->income_range?->value) === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('guardians.'.$relValue.'.income_range') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Baris 6: Alamat Lengkap --}}
                                <div class="sm:col-span-2">
                                    <label class="{{ $labelClass }}">Alamat Lengkap</label>
                                    <textarea name="guardians[{{ $relValue }}][address]" rows="2" placeholder="Masukkan alamat jalan dan nomor rumah {{ $relLabel }}" class="{{ $inputClass }} resize-none @error('guardians.'.$relValue.'.address') {{ $errorClass }} @enderror">{{ old('guardians.'.$relValue.'.address', $guardian?->vault?->address_encrypted ?? '') }}</textarea>
                                    @error('guardians.'.$relValue.'.address') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                            </div>
                        </div>
                        @endforeach

                        {{-- Tombol Tambah Wali (Muncul jika form wali disembunyikan) --}}
                        <div class="flex justify-center pt-2" x-show="!showWali" x-cloak>
                            <button type="button" @click="showWali = true" class="px-5 py-2.5 border border-dashed border-primary text-primary rounded-xl text-sm font-semibold hover:bg-primary/5 transition-colors flex items-center gap-2 cursor-pointer w-full justify-center">
                                <i data-lucide="plus" class="size-4"></i> Tambah Data Wali (Opsional)
                            </button>
                        </div>
                    </div>
                </form>
                @endif

                @if($activeStep === 4)
                {{-- STEP 4: AKADEMIK & RIWAYAT --}}
                <form id="edit-student-form"
                    @htmx:before-request="isLoading = true"
                    @htmx:after-request="isLoading = false"
                    hx-put="{{ route('admin.students.edit.personal.update', ['id' => $student->id, 'step' => 4]) }}"
                    hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML">
                    @csrf @method('PUT')

                    <div class="bg-cyan-50 border border-cyan-100 text-cyan-700 text-xs rounded-xl px-3.5 py-2.5 flex items-start gap-2 mb-4">
                        <i data-lucide="info" class="size-4 shrink-0 mt-0.5"></i>
                        <span>Rombongan belajar &amp; jurusan tidak diubah di sini — gunakan menu <strong>Pindah Kelas</strong> atau <strong>Kenaikan Kelas</strong>.</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Sekolah Asal</label>
                            <input type="text" name="previous_school" value="{{ old('previous_school', $student->previous_school ?? '') }}" placeholder="Masukkan nama sekolah asal" class="{{ $inputClass }} @error('previous_school') {{ $errorClass }} @enderror">
                            @error('previous_school') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">NPSN Sekolah Asal</label>
                            <input type="text" name="previous_school_npsn" value="{{ old('previous_school_npsn', $student->previous_school_npsn ?? '') }}" placeholder="Masukkan NPSN sekolah asal" class="{{ $inputClass }} font-mono @error('previous_school_npsn') {{ $errorClass }} @enderror">
                            @error('previous_school_npsn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Kota Asal</label>
                            <input type="text" name="previous_school_city" value="{{ old('previous_school_city', $student->previous_school_city ?? '') }}" placeholder="Masukkan kota asal sekolah" class="{{ $inputClass }} @error('previous_school_city') {{ $errorClass }} @enderror">
                            @error('previous_school_city') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Provinsi Asal</label>
                            <input type="text" name="previous_school_province" value="{{ old('previous_school_province', $student->previous_school_province ?? '') }}" placeholder="Masukkan provinsi asal sekolah" class="{{ $inputClass }} @error('previous_school_province') {{ $errorClass }} @enderror">
                            @error('previous_school_province') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Nomor Ijazah</label>
                            <input type="text" name="graduation_certificate_number" value="{{ old('graduation_certificate_number', $student->graduation_certificate_number ?? '') }}" placeholder="Masukkan nomor ijazah" class="{{ $inputClass }} @error('graduation_certificate_number') {{ $errorClass }} @enderror">
                            @error('graduation_certificate_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Tahun Ijazah</label>
                            <input type="text" name="graduation_year" inputmode="numeric" maxlength="4" value="{{ old('graduation_year', $student->graduation_year ?? '') }}" placeholder="Contoh: 2024" class="{{ $inputClass }} @error('graduation_year') {{ $errorClass }} @enderror">
                            @error('graduation_year') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </form>
                @endif

                @if($activeStep === 5)
                {{-- STEP 5: KESEHATAN & MINAT --}}
                <form id="edit-student-form"
                    @htmx:before-request="isLoading = true"
                    @htmx:after-request="isLoading = false"
                    hx-put="{{ route('admin.students.edit.personal.update', ['id' => $student->id, 'step' => 5]) }}"
                    hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML"
                    hx-on::after-request="if (!event.detail.xhr.responseURL.includes('step=')) window.dispatchEvent(new CustomEvent('close-modal'))">
                    @csrf @method('PUT')

                    {{-- BARIS 1: Tinggi, Berat, Golongan Darah --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">Tinggi Badan (cm)</label>
                            <input type="number" step="0.1" min="0" name="height" value="{{ old('height', $student->height) }}" placeholder="Contoh: 160" class="{{ $inputClass }} @error('height') {{ $errorClass }} @enderror">
                            @error('height') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Berat Badan (kg)</label>
                            <input type="number" step="0.1" min="0" name="weight" value="{{ old('weight', $student->weight) }}" placeholder="Contoh: 50" class="{{ $inputClass }} @error('weight') {{ $errorClass }} @enderror">
                            @error('weight') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Golongan Darah</label>
                            <select name="blood_type" class="{{ $inputClass }} @error('blood_type') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Golongan Darah —</option>
                                @foreach(['A', 'B', 'AB', 'O'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type', $student->blood_type) === $bt)>{{ $bt }}</option>
                                @endforeach
                            </select>
                            @error('blood_type') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BARIS 2: Alergi Makanan --}}
                    <div x-data="{ hasFoodAllergy: '{{ old('has_food_allergy', $student->food_allergy ? 'yes' : 'no') }}' }" class="mt-4 bg-white border border-border rounded-xl p-3.5">
                        <label class="{{ $labelClass }} mb-2">Alergi Makanan</label>
                        <div class="flex items-center gap-4 mb-1">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-foreground cursor-pointer">
                                <input type="radio" name="has_food_allergy" value="no" x-model="hasFoodAllergy" class="accent-primary"> Tidak
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-foreground cursor-pointer">
                                <input type="radio" name="has_food_allergy" value="yes" x-model="hasFoodAllergy" class="accent-primary"> Ya
                            </label>
                        </div>

                        <div x-show="hasFoodAllergy === 'yes'" x-transition.opacity x-cloak class="mt-3">
                            <label class="{{ $labelClass }}">Pilih Jenis Alergi Makanan</label>
                            <select name="food_allergy" class="{{ $inputClass }} @error('food_allergy') {{ $errorClass }} @enderror">
                                <option value="">— Pilih Jenis Alergi Makanan —</option>
                                <option value="Susu sapi" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Susu sapi')>Susu sapi (Umumnya pada bayi/anak)</option>
                                <option value="Telur" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Telur')>Telur (Terutama putih telur)</option>
                                <option value="Kacang tanah" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Kacang tanah')>Kacang tanah (Peanut)</option>
                                <option value="Kacang pohon" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Kacang pohon')>Kacang pohon (Almond, kenari, mete, dll)</option>
                                <option value="Ikan" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Ikan')>Ikan (Tuna, salmon, kakap, dll)</option>
                                <option value="Makanan laut" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Makanan laut')>Makanan laut (Udang, kepiting, cumi, dll)</option>
                                <option value="Kedelai" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Kedelai')>Kedelai (Tahu, tempe, susu kedelai)</option>
                                <option value="Gandum" @selected(old('food_allergy', $student->food_allergy ?? '') === 'Gandum')>Gandum</option>
                            </select>
                            @error('food_allergy') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- BARIS 3: Kondisi Khusus / Disabilitas --}}
                    <div class="mt-4 bg-white border border-border rounded-xl p-3.5">
                        <label class="{{ $labelClass }} mb-2">Kondisi Khusus / Disabilitas</label>
                        <div class="flex items-center gap-4 mb-1">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-foreground cursor-pointer">
                                <input type="radio" name="is_special_condition" value="no" x-model="isSpecial" class="accent-primary"> Tidak Ada
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-foreground cursor-pointer">
                                <input type="radio" name="is_special_condition" value="yes" x-model="isSpecial" class="accent-primary"> Ada
                            </label>
                        </div>
                        @error('is_special_condition') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror

                        <div x-show="isSpecial === 'yes'" x-transition.opacity x-cloak class="mt-3">
                            <div>
                                <label class="{{ $labelClass }}">Jenis Kondisi</label>
                                <select name="special_condition_type" class="{{ $inputClass }} @error('special_condition_type') {{ $errorClass }} @enderror">
                                    <option value="">— Pilih Jenis Kondisi —</option>
                                    @foreach(\App\Enums\Student\SpecialCondition::cases() as $option)
                                    <option value="{{ $option->value }}" @selected(old('special_condition_type', $student->special_condition_type?->value) === $option->value)>{{ $option->label() }}</option>
                                    @endforeach
                                </select>
                                @error('special_condition_type') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2 mt-3">
                                <label class="{{ $labelClass }}">Keterangan</label>
                                <textarea name="condition_description" rows="2" placeholder="Jelaskan kondisi khusus siswa" class="{{ $inputClass }} resize-none @error('condition_description') {{ $errorClass }} @enderror">{{ old('condition_description', $student->condition_description ?? '') }}</textarea>
                                @error('condition_description') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- BARIS 4: Riwayat Penyakit --}}
                    <div class="mt-3">
                        <label class="{{ $labelClass }} px-1">Riwayat Penyakit Umum</label>
                        <textarea name="medical_history" rows="2" placeholder="Tuliskan riwayat penyakit (jika ada)" class="{{ $inputClass }} resize-none @error('medical_history') {{ $errorClass }} @enderror">{{ old('medical_history', $student->medical_history ?? '') }}</textarea>
                        @error('medical_history') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Minat & Bakat (Tetap) --}}
                    <div class="flex items-center gap-2 pt-5 mt-5 border-t border-border/70">
                        <i data-lucide="star" class="size-4 text-secondary"></i>
                        <h4 class="font-bold text-foreground text-sm">Minat & Bakat</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="{{ $labelClass }}">Minat Seni</label>
                            <input type="text" name="interest_art" value="{{ old('interest_art', $student->interest_art ?? '') }}" placeholder="Contoh: Melukis, Musik" class="{{ $inputClass }} @error('interest_art') {{ $errorClass }} @enderror">
                            @error('interest_art') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Minat Olahraga</label>
                            <input type="text" name="interest_sport" value="{{ old('interest_sport', $student->interest_sport ?? '') }}" placeholder="Contoh: Sepak Bola, Renang" class="{{ $inputClass }} @error('interest_sport') {{ $errorClass }} @enderror">
                            @error('interest_sport') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Minat Organisasi</label>
                            <input type="text" name="interest_organization" value="{{ old('interest_organization', $student->interest_organization ?? '') }}" placeholder="Contoh: OSIS, Pramuka" class="{{ $inputClass }} @error('interest_organization') {{ $errorClass }} @enderror">
                            @error('interest_organization') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Pilihan Ekstrakurikuler</label>
                            <input type="text" name="extracurricular_choice" value="{{ old('extracurricular_choice', $student->extracurricular_choice ?? '') }}" placeholder="Contoh: Pramuka, PMR" class="{{ $inputClass }} @error('extracurricular_choice') {{ $errorClass }} @enderror">
                            @error('extracurricular_choice') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </form>
                @endif

            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-between gap-2 shrink-0">
                <div>
                    @if($activeStep === 1)
                    <button type="button" @click="closeModal()"
                        class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                        Batal
                    </button>
                    @else
                    <button type="button"
                        hx-get="{{ route('admin.students.edit.personal', ['id' => $student->id, 'step' => $activeStep - 1]) }}"
                        hx-target="#edit-modal-content" hx-select="#edit-modal-content" hx-swap="outerHTML"
                        class="flex items-center gap-1.5 px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                        <i data-lucide="arrow-left" class="size-4"></i>
                        <span>Kembali</span>
                    </button>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline text-xs text-secondary font-medium">Langkah {{ $activeStep }} dari 5</span>

                    @if($activeStep < 5)
                        <button type="submit" form="edit-student-form" :disabled="isLoading"
                        class="flex items-center justify-center min-w-[160px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                        {{-- Tampilan Normal --}}
                        <div x-show="!isLoading" class="flex items-center gap-1.5">
                            <span>Simpan & Lanjut</span>
                            <i data-lucide="arrow-right" class="size-4"></i>
                        </div>

                        {{-- Tampilan Loading --}}
                        <div x-show="isLoading" x-cloak class="flex items-center gap-1.5">
                            <i data-lucide="loader-2" class="size-4 animate-spin"></i>
                            <span>Menyimpan...</span>
                        </div>
                        </button>
                        @else
                        <button type="submit" form="edit-student-form" :disabled="isLoading"
                            class="flex items-center justify-center min-w-[210px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                            {{-- Tampilan Normal --}}
                            <div x-show="!isLoading" class="flex items-center gap-1.5">
                                <span>Simpan Perubahan Final</span>
                            </div>

                            {{-- Tampilan Loading --}}
                            <div x-show="isLoading" x-cloak class="flex items-center gap-1.5">
                                <i data-lucide="loader-2" class="size-4 animate-spin"></i>
                                <span>Menyimpan...</span>
                            </div>
                        </button>
                        @endif
                </div>
            </div>

            {{-- Script ditempatkan di sini agar dieksekusi ulang saat HTMX mengganti konten --}}
            <script>
                if (typeof lucide !== 'undefined') lucide.createIcons();
            </script>

        </div>
    </x-ui.modal>
</div>