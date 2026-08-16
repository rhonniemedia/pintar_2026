<div x-data="{ open: false }" x-init="setTimeout(() => open = true, 10)">
    <x-ui.modal show="open" maxWidth="2xl">

        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="school" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Edit Data Sekolah</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Perbarui identitas dan profil sekolah.</p>
                </div>
            </div>

            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form HTMX --}}
        <form id="edit-school-form"
            hx-post="{{ route('admin.master-data.school.update') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            hx-encoding="multipart/form-data"
            x-data="{ saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 min-h-0">
            @csrf
            @method('PUT')

            @php
            $inputClass = 'w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors';
            $labelClass = 'block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5';
            $errorClass = 'border-error ring-1 ring-error/30';
            @endphp

            <div class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 space-y-5">

                {{-- Logo --}}
                <div>
                    <label class="{{ $labelClass }}">Logo Sekolah</label>
                    <input type="file" name="logo" accept="image/png, image/jpeg, image/jpg"
                        class="{{ $inputClass }} @error('logo') {{ $errorClass }} @enderror">
                    @error('logo') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <hr class="border-border">

                {{-- Data Umum --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Nama Sekolah <span class="text-error">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $school->name) }}"
                            class="{{ $inputClass }} @error('name') {{ $errorClass }} @enderror" required>
                        @error('name') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Status Sekolah <span class="text-error">*</span></label>
                        <select name="status" class="{{ $inputClass }} @error('status') {{ $errorClass }} @enderror" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="negeri" @selected(old('status', $school->status) === 'negeri')>Negeri</option>
                            <option value="swasta" @selected(old('status', $school->status) === 'swasta')>Swasta</option>
                        </select>
                        @error('status') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">NPSN <span class="text-error">*</span></label>
                        <input type="text" name="npsn" value="{{ old('npsn', $school->npsn) }}" maxlength="8" placeholder="8 digit angka"
                            class="{{ $inputClass }} @error('npsn') {{ $errorClass }} @enderror" required>
                        @error('npsn') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">NSS</label>
                        <input type="text" name="nss" value="{{ old('nss', $school->nss) }}"
                            class="{{ $inputClass }} @error('nss') {{ $errorClass }} @enderror">
                        @error('nss') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">No. SK Pendirian</label>
                        <input type="text" name="establishment_decree_number" value="{{ old('establishment_decree_number', $school->establishment_decree_number) }}"
                            class="{{ $inputClass }} @error('establishment_decree_number') {{ $errorClass }} @enderror">
                        @error('establishment_decree_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Tanggal Pendirian</label>
                        <input type="date" name="establishment_date" value="{{ old('establishment_date', optional($school->establishment_date)->format('Y-m-d')) }}"
                            class="{{ $inputClass }} @error('establishment_date') {{ $errorClass }} @enderror">
                        @error('establishment_date') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Status Naungan Dinas</label>
                        <input type="text" name="supervising_office_status" value="{{ old('supervising_office_status', $school->supervising_office_status) }}"
                            class="{{ $inputClass }} @error('supervising_office_status') {{ $errorClass }} @enderror">
                        @error('supervising_office_status') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Instansi Induk</label>
                        <input type="text" name="parent_institution" value="{{ old('parent_institution', $school->parent_institution) }}"
                            class="{{ $inputClass }} @error('parent_institution') {{ $errorClass }} @enderror">
                        @error('parent_institution') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="border-border">

                {{-- Alamat --}}
                <div class="space-y-4">
                    <div>
                        <label class="{{ $labelClass }}">Alamat Sekolah <span class="text-error">*</span></label>
                        <textarea name="address" rows="2"
                            class="{{ $inputClass }} @error('address') {{ $errorClass }} @enderror" required>{{ old('address', $school->address) }}</textarea>
                        @error('address') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- RT 1/4, RW 1/4, Kelurahan 1/2 --}}
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">RT</label>
                            <input type="text" name="rt" value="{{ old('rt', $school->rt) }}"
                                class="{{ $inputClass }} @error('rt') {{ $errorClass }} @enderror">
                            @error('rt') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">RW</label>
                            <input type="text" name="rw" value="{{ old('rw', $school->rw) }}"
                                class="{{ $inputClass }} @error('rw') {{ $errorClass }} @enderror">
                            @error('rw') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="{{ $labelClass }}">Kelurahan/Desa <span class="text-error">*</span></label>
                            <input type="text" name="village" value="{{ old('village', $school->village) }}"
                                class="{{ $inputClass }} @error('village') {{ $errorClass }} @enderror" required>
                            @error('village') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Kecamatan, Kabupaten/Kota --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Kecamatan <span class="text-error">*</span></label>
                            <input type="text" name="district" value="{{ old('district', $school->district) }}"
                                class="{{ $inputClass }} @error('district') {{ $errorClass }} @enderror" required>
                            @error('district') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Kabupaten/Kota <span class="text-error">*</span></label>
                            <input type="text" name="regency" value="{{ old('regency', $school->regency) }}"
                                class="{{ $inputClass }} @error('regency') {{ $errorClass }} @enderror" required>
                            @error('regency') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Provinsi, Kode Pos --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Provinsi <span class="text-error">*</span></label>
                            <input type="text" name="province" value="{{ old('province', $school->province) }}"
                                class="{{ $inputClass }} @error('province') {{ $errorClass }} @enderror" required>
                            @error('province') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Kode Pos</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $school->postal_code) }}"
                                class="{{ $inputClass }} @error('postal_code') {{ $errorClass }} @enderror">
                            @error('postal_code') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-border">

                {{-- Kontak --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $labelClass }}">Telepon <span class="text-error">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                            class="{{ $inputClass }} @error('phone') {{ $errorClass }} @enderror" required>
                        @error('phone') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Email <span class="text-error">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $school->email) }}"
                            class="{{ $inputClass }} @error('email') {{ $errorClass }} @enderror" required>
                        @error('email') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Website</label>
                        <input type="text" name="website" value="{{ old('website', $school->website) }}" placeholder="https://..."
                            class="{{ $inputClass }} @error('website') {{ $errorClass }} @enderror">
                        @error('website') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="border-border">

                {{-- Pejabat Sekolah --}}
                <div>
                    <p class="text-[11px] font-semibold text-foreground mb-3">Pejabat Sekolah <span class="text-secondary font-normal">(dipakai otomatis untuk tanda tangan surat)</span></p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Kepala Sekolah</label>
                            <x-ui.searchable-select
                                name="headmaster_staff_id"
                                :options="$staffOptions"
                                :value="old('headmaster_staff_id', $school->headmaster_staff_id)"
                                placeholder="-- Cari dan Pilih Staf --" />
                            @error('headmaster_staff_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Wakil Bid. Kesiswaan</label>
                            <x-ui.searchable-select
                                name="student_affairs_deputy_staff_id"
                                :options="$staffOptions"
                                :value="old('student_affairs_deputy_staff_id', $school->student_affairs_deputy_staff_id)"
                                placeholder="-- Cari dan Pilih Staf --" />
                            @error('student_affairs_deputy_staff_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="{{ $labelClass }}">Koordinator Tata Usaha</label>
                            <x-ui.searchable-select
                                name="administration_coordinator_staff_id"
                                :options="$staffOptions"
                                :value="old('administration_coordinator_staff_id', $school->administration_coordinator_staff_id)"
                                placeholder="-- Cari dan Pilih Staf --" />
                            @error('administration_coordinator_staff_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-2 shrink-0">
                <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                    class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-slate-50 hover:border-gray-300 transition-all cursor-pointer">
                    Batal
                </button>

                <button type="submit" :disabled="saving"
                    class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">
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
        </form>
    </x-ui.modal>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>