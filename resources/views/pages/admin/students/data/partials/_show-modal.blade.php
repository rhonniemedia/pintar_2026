<div id="modal-container"
    x-data="{ 
        open: false,
        activeTab: 'identitas',
        closeModal() {
            this.open = false;
            setTimeout(() => document.getElementById('modal-container').outerHTML = '<div id=\'modal-container\'></div>', 300);
        }
    }"
    x-init="setTimeout(() => open = true, 50)">

    <x-ui.modal show="open" maxWidth="3xl">
        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                @php
                $initials = strtoupper(substr($student->name, 0, 2));
                $colors = ['bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700'];
                $avatarColor = $colors[crc32($student->id) % 4];
                @endphp
                <div class="size-11 sm:size-12 rounded-full {{ $avatarColor }} flex items-center justify-center font-bold text-base sm:text-lg shrink-0 shadow-sm">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg uppercase leading-tight truncate">{{ $student->name }}</h3>
                    <div class="flex items-center flex-wrap gap-1.5 mt-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-cyan-50 border border-cyan-100 text-cyan-600 font-semibold text-[10px] sm:text-[11px]">
                            NIS. {{ $student->nis ?? '-' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-orange-50 border border-orange-100 text-orange-600 font-semibold text-[10px] sm:text-[11px]">
                            NISN. {{ $student->vault->nisn_encrypted ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Tab Navigation --}}
        <div class="sticky top-0 z-20 bg-white border-b border-border shadow-sm">
            {{-- Menggunakan flex-wrap agar otomatis turun/menyesuaikan di layar kecil --}}
            <div class="flex flex-wrap items-center gap-2 px-4 sm:px-6 py-3">

                {{-- Tambahkan flex-auto dan justify-center pada setiap button --}}
                <button @click="activeTab = 'identitas'"
                    class="flex-auto flex justify-center items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'identitas' 
                ? 'bg-primary text-white shadow-sm shadow-primary/30 ring-2 ring-primary/20' 
                : 'bg-slate-100 text-secondary hover:bg-slate-200'">
                    <i data-lucide="user" class="size-3.5 sm:size-4"></i>
                    <span>Identitas Diri</span>
                </button>

                <button @click="activeTab = 'akademik'"
                    class="flex-auto flex justify-center items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'akademik' 
                ? 'bg-primary text-white shadow-sm shadow-primary/30 ring-2 ring-primary/20' 
                : 'bg-slate-100 text-secondary hover:bg-slate-200'">
                    <i data-lucide="graduation-cap" class="size-3.5 sm:size-4"></i>
                    <span>Akademik & Riwayat</span>
                </button>

                <button @click="activeTab = 'kontak'"
                    class="flex-auto flex justify-center items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'kontak' 
                ? 'bg-primary text-white shadow-sm shadow-primary/30 ring-2 ring-primary/20' 
                : 'bg-slate-100 text-secondary hover:bg-slate-200'">
                    <i data-lucide="map-pin" class="size-3.5 sm:size-4"></i>
                    <span>Kontak & Alamat</span>
                </button>

                <button @click="activeTab = 'kesehatan'"
                    class="flex-auto flex justify-center items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="activeTab === 'kesehatan' 
                ? 'bg-primary text-white shadow-sm shadow-primary/30 ring-2 ring-primary/20' 
                : 'bg-slate-100 text-secondary hover:bg-slate-200'">
                    <i data-lucide="activity" class="size-3.5 sm:size-4"></i>
                    <span>Kesehatan & Minat</span>
                </button>

            </div>
        </div>

        {{-- Tab Contents --}}
        {{-- 'grid' membuat ke-4 panel bertumpuk di 1 sel yang sama, sehingga saat Alpine
             menjalankan transisi fade (panel lama & baru sempat tampil bersamaan), keduanya
             saling menimpa alih-alih mendorong tinggi kontainer — inilah yang sebelumnya
             membuat modal terlihat "melebar" sesaat. scrollbar-gutter mencegah lompatan lebar
             tambahan saat scrollbar vertikal muncul/hilang antar tab. --}}
        <div class="grid p-4 sm:p-6 overflow-y-auto max-h-[55vh] sm:max-h-[60vh] bg-slate-50/30 flex-1 [scrollbar-gutter:stable]">

            {{-- TAB 1: IDENTITAS DIRI --}}
            <div x-show="activeTab === 'identitas'" x-transition.opacity class="col-start-1 row-start-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->name }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nama Panggilan</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->nick_name ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Jenis Kelamin</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->gender?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Tempat, Tanggal Lahir</label>
                        <p class="text-sm font-medium text-foreground leading-snug">
                            {{ $student->vault->pob_encrypted ?? '-' }},
                            {{ $student->vault->dob_encrypted ? \Carbon\Carbon::parse($student->vault->dob_encrypted)->translatedFormat('d F Y') : '-' }}
                        </p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Agama</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->vault->religion?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nomor Induk Kependudukan (NIK)</label>
                        <p class="text-sm font-medium text-foreground leading-snug font-mono">{{ $student->vault->nik_encrypted ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Anak Ke / Jumlah Saudara</label>
                        <p class="text-sm font-medium text-foreground leading-snug">
                            {{ $student->child_order ?? '-' }} dari {{ $student->number_of_siblings ?? '-' }} bersaudara
                        </p>
                    </div>
                </div>
            </div>

            {{-- TAB 2: AKADEMIK & RIWAYAT --}}
            <div x-show="activeTab === 'akademik'" x-transition.opacity x-cloak class="col-start-1 row-start-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Rombongan Belajar Saat Ini</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ optional($student->activeClassGroup->first())->name ?? 'Belum memiliki Rombel' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Konsentrasi Keahlian (Jurusan)</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->concentration->name ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Tanggal Masuk</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->entry_date ? \Carbon\Carbon::parse($student->entry_date)->translatedFormat('d F Y') : '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Jenis Pendaftaran & Tingkat Masuk</label>
                        <p class="text-sm font-medium text-foreground leading-snug">
                            {{ $student->registration_type?->label() ?? '-' }} (Tingkat {{ $student->entry_grade_level }})
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-5 mt-5 border-t border-border/70">
                    <i data-lucide="school" class="size-4 text-secondary"></i>
                    <h4 class="font-bold text-foreground text-sm">Riwayat Sekolah Asal</h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Sekolah Asal</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->previous_school ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">NPSN Sekolah Asal</label>
                        <p class="text-sm font-medium text-foreground leading-snug font-mono">{{ $student->previous_school_npsn ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Kota / Provinsi Asal</label>
                        <p class="text-sm font-medium text-foreground leading-snug">
                            {{ $student->previous_school_city ?? '-' }} / {{ $student->previous_school_province ?? '-' }}
                        </p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nomor / Tahun Ijazah</label>
                        <p class="text-sm font-medium text-foreground leading-snug">
                            {{ $student->graduation_certificate_number ?? '-' }} ({{ $student->graduation_year ?? '-' }})
                        </p>
                    </div>
                </div>
            </div>

            {{-- TAB 3: KONTAK & ALAMAT --}}
            <div x-show="activeTab === 'kontak'" x-transition.opacity x-cloak class="col-start-1 row-start-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nomor Telepon / WhatsApp</label>
                        <p class="text-sm font-medium text-foreground leading-snug font-mono">{{ $student->vault->phone_number_encrypted ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Email</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->vault->email_encrypted ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Jenis Tempat Tinggal</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->residence_type?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Moda Transportasi</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->transportation?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Jarak ke Sekolah</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->distance_to_school?->label() ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5 px-1">Alamat Lengkap</label>
                    <div class="flex items-start gap-2.5 bg-white border border-border rounded-xl p-3.5">
                        <i data-lucide="map-pin" class="size-4 text-secondary shrink-0 mt-0.5"></i>
                        <p class="text-sm font-medium text-foreground leading-relaxed">
                            {{ $student->vault->address_encrypted ?? '-' }}<br>
                            RT. {{ $student->vault->rt_encrypted ?? '-' }} / RW. {{ $student->vault->rw_encrypted ?? '-' }},
                            Kel/Desa {{ $student->vault->village_encrypted ?? '-' }},<br>
                            Kec. {{ $student->vault->district_encrypted ?? '-' }},
                            {{ $student->vault->regency_encrypted ?? '-' }},<br>
                            Prov. {{ $student->vault->province_encrypted ?? '-' }}
                            {{ $student->vault->postal_code_encrypted ? ' - ' . $student->vault->postal_code_encrypted : '' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- TAB 4: KESEHATAN & MINAT --}}
            <div x-show="activeTab === 'kesehatan'" x-transition.opacity x-cloak class="col-start-1 row-start-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Tinggi / Berat Badan</label>
                        <p class="text-sm font-medium text-foreground leading-snug">
                            {{ $student->height ? $student->height . ' cm' : '-' }} /
                            {{ $student->weight ? $student->weight . ' kg' : '-' }}
                        </p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Golongan Darah</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->blood_type ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5 px-1">Kondisi Khusus / Disabilitas</label>
                    @if($student->is_special_condition === 'yes')
                    <div class="bg-warning/10 border border-warning/30 p-3.5 rounded-xl flex gap-2.5">
                        <i data-lucide="alert-triangle" class="size-4 text-warning-dark shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-warning-dark">{{ $student->special_condition_type?->label() ?? 'Ada Kondisi Khusus' }}</p>
                            <p class="text-sm text-foreground mt-1">{{ $student->condition_description ?? 'Tidak ada keterangan tambahan.' }}</p>
                        </div>
                    </div>
                    @else
                    <div class="bg-white border border-border rounded-xl p-3.5 flex items-center gap-2.5">
                        <i data-lucide="check-circle-2" class="size-4 text-emerald-600 shrink-0"></i>
                        <p class="text-sm font-medium text-foreground">Tidak ada kondisi khusus (Normal)</p>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3 sm:col-span-2">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Riwayat Penyakit</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->medical_history ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-5 mt-5 border-t border-border/70">
                    <i data-lucide="star" class="size-4 text-secondary"></i>
                    <h4 class="font-bold text-foreground text-sm">Minat & Bakat</h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Minat Seni</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->interest_art ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Minat Olahraga</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->interest_sport ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Minat Organisasi</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->interest_organization ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Pilihan Ekstrakurikuler</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $student->extracurricular_choice ?? '-' }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer Modal --}}
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end shrink-0">
            <button type="button" @click="closeModal()"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </x-ui.modal>

    {{-- Re-initialize Lucide Icons di dalam DOM modal yang baru di-load HTMX --}}
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>