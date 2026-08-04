<x-ui.modal show="filterModalOpen" maxWidth="lg">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
        <div class="flex items-center gap-3">
            <div class="size-9 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                <i data-lucide="sliders-horizontal" class="size-4 text-primary"></i>
            </div>
            <div>
                <h3 class="font-bold text-foreground text-lg">Filter Data Siswa</h3>
                <p class="text-xs text-secondary mt-0.5">Persempit daftar berdasarkan kriteria berikut</p>
            </div>
        </div>
        <button @click="filterModalOpen = false" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
            <i data-lucide="x" class="size-4 text-secondary"></i>
        </button>
    </div>

    {{-- Form filter --}}
    <div id="student-filter-form" class="flex-1 overflow-y-auto p-6 space-y-6">

        {{-- Grup: Akademik --}}
        <div class="space-y-3">
            <p class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-secondary">
                <i data-lucide="graduation-cap" class="size-3.5"></i>
                Akademik
            </p>
            <div class="grid grid-cols-2 gap-3">
                @if($showGradeFilter ?? true)
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Kelas</label>
                    <select name="filter_grade" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach (['10', '11', '12'] as $grade)
                        <option value="{{ $grade }}" @selected(isset($filterGrade) && $filterGrade===$grade)>Kelas {{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="{{ isset($showGradeFilter) && !$showGradeFilter ? 'col-span-2' : '' }}">
                    <label class="block text-sm font-bold text-foreground mb-2">Jurusan</label>
                    <select name="filter_concentration" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Jurusan</option>
                        @foreach ($concentrationOptions as $id => $name)
                        <option value="{{ $id }}" @selected(isset($filterConcentration) && $filterConcentration==$id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Grup: Demografi --}}
        <div class="space-y-3 pt-1 border-t border-border/70">
            <p class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-secondary pt-3">
                <i data-lucide="users" class="size-3.5"></i>
                Demografi
            </p>
            <div class="grid grid-cols-2 gap-3">
                <div class="{{ isset($showReligionFilter) && !$showReligionFilter ? 'col-span-2' : '' }}">
                    <label class="block text-sm font-bold text-foreground mb-2">Jenis Kelamin</label>
                    <select name="filter_gender" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Gender</option>
                        <option value="L" @selected(isset($filterGender) && $filterGender==='L' )>Laki-laki</option>
                        <option value="P" @selected(isset($filterGender) && $filterGender==='P' )>Perempuan</option>
                    </select>
                </div>

                @if($showReligionFilter ?? true)
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Agama</label>
                    <select name="filter_religion" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Agama</option>
                        @foreach ($religionOptions as $religion)
                        <option value="{{ $religion->value }}" @selected(isset($filterReligion) && $filterReligion===$religion->value)>{{ $religion->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            {{-- Usia --}}
            @if($showAgeFilter ?? true)
            <div class="rounded-xl border border-border bg-gray-50/60 p-4 mt-3">
                <label class="flex items-center gap-1.5 text-sm font-bold text-foreground mb-3">
                    <i data-lucide="calendar-clock" class="size-4 text-secondary"></i>
                    Usia pada Tanggal Acuan
                </label>
                <div class="flex items-stretch gap-2">
                    <div class="relative w-24 shrink-0">
                        <input
                            type="number"
                            name="filter_age"
                            min="0"
                            value="{{ $filterAge ?? '' }}"
                            placeholder="0"
                            class="h-11 w-full bg-white border border-border rounded-xl pl-3 pr-9 text-sm text-center font-semibold focus:outline-none focus:border-primary transition-all">
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-secondary">thn</span>
                    </div>

                    <i data-lucide="arrow-right" class="size-4 text-secondary shrink-0 self-center"></i>

                    <div class="relative flex-1">
                        <i data-lucide="calendar" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                        <input
                            type="date"
                            name="filter_age_date"
                            value="{{ $filterAgeDate ?? now()->toDateString() }}"
                            class="h-11 w-full bg-white border border-border rounded-xl pl-9 pr-3 text-sm focus:outline-none focus:border-primary transition-all">
                    </div>

                    <button
                        type="button"
                        title="Gunakan hari ini"
                        onclick="const el = document.querySelector('[name=filter_age_date]'); el.valueAsDate = new Date();"
                        class="h-11 shrink-0 px-3 rounded-xl border border-border bg-white text-xs font-semibold text-secondary hover:bg-muted hover:text-foreground transition-colors cursor-pointer">
                        Hari ini
                    </button>
                </div>
                <p class="text-xs text-secondary mt-2">Kosongkan usia jika tidak ingin memfilter berdasarkan umur.</p>
            </div>
            @endif
        </div>

        {{-- Grup: Lainnya --}}
        @if(($showSpecialNeedsFilter ?? true) || ($showOrphanStatusFilter ?? true) || ($showFoodAllergyFilter ?? true))
        <div class="space-y-3 pt-1 border-t border-border/70">
            <p class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-secondary pt-3">
                <i data-lucide="heart-pulse" class="size-3.5"></i>
                Lainnya
            </p>
            <div class="grid grid-cols-2 gap-3">
                @if($showSpecialNeedsFilter ?? true)
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Kebutuhan Khusus</label>
                    <select name="filter_special_needs" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Kondisi</option>
                        <option value="yes" @selected(isset($filterSpecialNeeds) && $filterSpecialNeeds==='yes' )>Berkebutuhan Khusus</option>
                        <option value="no" @selected(isset($filterSpecialNeeds) && $filterSpecialNeeds==='no' )>Reguler</option>
                    </select>
                </div>
                @endif

                @if($showFoodAllergyFilter ?? false)
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Alergi Makanan</label>
                    <select name="filter_food_allergy" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Siswa</option>
                        <option value="yes" @selected(isset($filterFoodAllergy) && $filterFoodAllergy==='yes' )>Punya Alergi</option>
                        <option value="no" @selected(isset($filterFoodAllergy) && $filterFoodAllergy==='no' )>Tidak Ada Alergi</option>
                    </select>
                </div>
                @endif

                @if($showOrphanStatusFilter ?? true)
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-foreground mb-2">Status Yatim/Piatu</label>
                    <select name="filter_orphan_status" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Semua Siswa</option>
                        <option value="yatim" @selected(isset($filterOrphanStatus) && $filterOrphanStatus==='yatim' )>Yatim (Ayah Meninggal)</option>
                        <option value="piatu" @selected(isset($filterOrphanStatus) && $filterOrphanStatus==='piatu' )>Piatu (Ibu Meninggal)</option>
                        <option value="yatim_piatu" @selected(isset($filterOrphanStatus) && $filterOrphanStatus==='yatim_piatu' )>Yatim Piatu (Ayah &amp; Ibu Meninggal)</option>
                    </select>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-between shrink-0">
        <button type="button"
            @click="
                document.querySelectorAll('#student-filter-form select').forEach(el => el.value = '');
                document.querySelectorAll('#student-filter-form input[type=number]').forEach(el => el.value = '');
                document.querySelectorAll('#student-filter-form input[type=date]').forEach(el => el.value = '');
                document.getElementById('btn-apply-filter').click();
            "
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-border bg-white text-secondary hover:bg-muted transition-colors cursor-pointer">
            <i data-lucide="rotate-ccw" class="size-3.5"></i>
            Reset Filter
        </button>

        <button type="button"
            id="btn-apply-filter"
            {{-- Menggunakan variabel $filterRoute yang dikirim dari halaman pemanggil --}}
            hx-get="{{ $filterRoute ?? route('admin.students.data.index') }}"
            hx-include="#student-filter-form, [name='search']"
            hx-target="#students-container" hx-select="#students-container" hx-swap="outerHTML" hx-push-url="true"
            @click="filterModalOpen = false"
            class="flex items-center gap-1.5 px-5 py-2.5 bg-primary text-white hover:bg-primary-dark shadow-md text-sm font-bold rounded-xl transition-all cursor-pointer">
            <i data-lucide="check" class="size-4"></i>
            Terapkan Filter
        </button>
    </div>

</x-ui.modal>