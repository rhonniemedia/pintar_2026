{{-- File: resources/views/pages/admin/students/transfers/in/partials/_modal-create.blade.php --}}
<div x-data="{ open: true }" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)">

    <div class="bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">Tambah Siswa Pindahan</h3>
                <p class="text-xs text-secondary mt-0.5">Masukkan data lengkap peserta didik pindahan dari sekolah lain.</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-form-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form HTMX --}}
        <form hx-post="{{ route('admin.students.transfer.in.store') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            x-data="{ saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="flex-1 overflow-y-auto p-6 space-y-6">

                {{-- ============ IDENTITAS PERSONAL ============ --}}
                <div>
                    <h4 class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                        <i data-lucide="user" class="size-4 text-primary"></i> Identitas Personal
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-foreground mb-2">Nama Siswa</label>
                            <input type="text" name="name" required maxlength="255" value="{{ old('name') }}"
                                class="w-full bg-white border @error('name') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('name') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">NIK (opsional)</label>
                            <input type="text" name="nik" maxlength="16" value="{{ old('nik') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);"
                                class="w-full bg-white border @error('nik') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('nik') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Jenis Kelamin</label>
                            <select name="gender" required
                                class="w-full bg-white border @error('gender') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>-- Pilih --</option>
                                <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Agama</label>
                            <select name="religion" required
                                class="w-full bg-white border @error('religion') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                                <option value="" disabled {{ old('religion') ? '' : 'selected' }}>-- Pilih Agama --</option>
                                @foreach ($religionOptions as $opt)
                                <option value="{{ $opt }}" {{ old('religion') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('religion') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Tempat Lahir</label>
                            <input type="text" name="pob" maxlength="255" value="{{ old('pob') }}"
                                class="w-full bg-white border @error('pob') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('pob') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Tanggal Lahir</label>
                            <input type="date" name="dob" value="{{ old('dob') }}"
                                class="w-full bg-white border @error('dob') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('dob') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">NISN</label>
                            <input type="text" name="nisn" required maxlength="10" value="{{ old('nisn') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                class="w-full bg-white border @error('nisn') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('nisn') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Anak Ke (opsional)</label>
                            <input type="number" min="1" name="child_order" value="{{ old('child_order') }}"
                                class="w-full bg-white border @error('child_order') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('child_order') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Jumlah Saudara (opsional)</label>
                            <input type="number" min="0" name="number_of_siblings" value="{{ old('number_of_siblings') }}"
                                class="w-full bg-white border @error('number_of_siblings') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('number_of_siblings') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-dashed border-border"></div>

                {{-- ============ ORANG TUA / WALI ============ --}}
                <div>
                    <h4 class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                        <i data-lucide="users" class="size-4 text-primary"></i> Orang Tua / Wali
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Nama Orang Tua/Wali</label>
                            <input type="text" name="guardian_name" required maxlength="255" value="{{ old('guardian_name') }}"
                                class="w-full bg-white border @error('guardian_name') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_name') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Hubungan Keluarga</label>
                            <select name="guardian_relationship" required
                                class="w-full bg-white border @error('guardian_relationship') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                                <option value="" disabled {{ old('guardian_relationship') ? '' : 'selected' }}>-- Pilih Hubungan --</option>
                                @foreach ($guardianRelationships as $val => $label)
                                <option value="{{ $val }}" {{ old('guardian_relationship') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('guardian_relationship') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">NIK Wali (opsional)</label>
                            <input type="text" name="guardian_nik" maxlength="16" value="{{ old('guardian_nik') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);"
                                class="w-full bg-white border @error('guardian_nik') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_nik') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Tahun Lahir (opsional)</label>
                            <input type="number" name="guardian_birth_year" min="1900" max="{{ date('Y') }}" value="{{ old('guardian_birth_year') }}"
                                class="w-full bg-white border @error('guardian_birth_year') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_birth_year') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Pekerjaan (opsional)</label>
                            <input type="text" name="guardian_occupation" maxlength="255" value="{{ old('guardian_occupation') }}"
                                placeholder="Contoh: Wiraswasta"
                                class="w-full bg-white border @error('guardian_occupation') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_occupation') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Pendidikan Terakhir (opsional)</label>
                            <input type="text" name="guardian_education" maxlength="255" value="{{ old('guardian_education') }}"
                                placeholder="Contoh: SMA / Sederajat"
                                class="w-full bg-white border @error('guardian_education') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_education') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Penghasilan Bulanan (opsional)</label>
                            <input type="text" name="guardian_income_range" maxlength="255" value="{{ old('guardian_income_range') }}"
                                placeholder="Contoh: Rp2.000.000 - Rp2.999.999"
                                class="w-full bg-white border @error('guardian_income_range') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_income_range') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Nomor Telepon (opsional)</label>
                            <input type="text" name="guardian_phone" maxlength="20" value="{{ old('guardian_phone') }}"
                                class="w-full bg-white border @error('guardian_phone') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('guardian_phone') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-foreground mb-2">Alamat Tempat Tinggal (opsional)</label>
                            <textarea name="guardian_address" rows="2"
                                class="w-full bg-white border @error('guardian_address') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">{{ old('guardian_address') }}</textarea>
                            @error('guardian_address') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-dashed border-border"></div>

                {{-- ============ SEKOLAH ASAL ============ --}}
                <div>
                    <h4 class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                        <i data-lucide="school" class="size-4 text-primary"></i> Sekolah Asal
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-foreground mb-2">Nama Sekolah Asal</label>
                            <input type="text" name="previous_school" required maxlength="255" value="{{ old('previous_school') }}"
                                class="w-full bg-white border @error('previous_school') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('previous_school') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Kab/Kota Sekolah Asal (opsional)</label>
                            <input type="text" name="previous_school_city" maxlength="255" value="{{ old('previous_school_city') }}"
                                class="w-full bg-white border @error('previous_school_city') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('previous_school_city') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Provinsi Sekolah Asal (opsional)</label>
                            <input type="text" name="previous_school_province" maxlength="255" value="{{ old('previous_school_province') }}"
                                class="w-full bg-white border @error('previous_school_province') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('previous_school_province') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">NPSN Sekolah Asal (opsional)</label>
                            <input type="text" name="previous_school_npsn" maxlength="20" value="{{ old('previous_school_npsn') }}"
                                class="w-full bg-white border @error('previous_school_npsn') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('previous_school_npsn') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Nomor Ijazah (opsional)</label>
                            <input type="text" name="graduation_certificate_number" maxlength="50" value="{{ old('graduation_certificate_number') }}"
                                class="w-full bg-white border @error('graduation_certificate_number') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('graduation_certificate_number') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Tahun Lulus (opsional)</label>
                            <input type="text" name="graduation_year" maxlength="4" value="{{ old('graduation_year') }}"
                                class="w-full bg-white border @error('graduation_year') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('graduation_year') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-dashed border-border"></div>

                {{-- ============ INFORMASI PENERIMAAN ============ --}}
                <div>
                    <h4 class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                        <i data-lucide="calendar-check" class="size-4 text-primary"></i> Informasi Penerimaan
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-foreground mb-2">Rombel Tujuan</label>
                            <select name="class_group_id" required
                                class="w-full bg-white border @error('class_group_id') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                                <option value="" disabled {{ old('class_group_id') ? '' : 'selected' }}>-- Pilih Rombel --</option>
                                @forelse ($classGroups as $cg)
                                <option value="{{ $cg->id }}" {{ old('class_group_id') === $cg->id ? 'selected' : '' }}>
                                    {{ $cg->name ?? ('Kelas ' . $cg->grade_level . ' - ' . $cg->group_number) }}
                                    &mdash; {{ optional($cg->concentration)->name }} ({{ optional($cg->concentration)->alias }})
                                </option>
                                @empty
                                <option value="" disabled>Tidak ada rombel pada tahun ajaran ini</option>
                                @endforelse
                            </select>
                            @error('class_group_id') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                            <p class="text-xs text-secondary mt-1">Tingkat kelas & jurusan siswa mengikuti rombel yang dipilih di sini.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-foreground mb-2">Tanggal Masuk</label>
                            <input type="date" name="entry_date" required value="{{ old('entry_date') }}"
                                class="w-full bg-white border @error('entry_date') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                            @error('entry_date') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-foreground mb-2">Catatan Tambahan (opsional)</label>
                            <textarea name="notes" rows="2"
                                class="w-full bg-white border @error('notes') border-error @else border-border @enderror rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">{{ old('notes') }}</textarea>
                            @error('notes') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Form --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end shrink-0 gap-2">

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
                    <i data-lucide="save" class="size-4" x-show="!saving"></i>
                    <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan Data'"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>