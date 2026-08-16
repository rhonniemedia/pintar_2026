@extends('layouts.main.admin')

@section('title', 'Data Sekolah')
@section('page_title', 'Data Sekolah')
@section('page_subtitle', 'Kelola identitas dan profil sekolah')

@section('content')
<div class="p-8 space-y-5"
    x-data="schoolApp(@js($schoolData))"
    @school-updated.window="
        Object.assign(school, $event.detail.school);
        window.ShowAlert({type: 'success', title: 'Berhasil!', message: $event.detail.message});
     ">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Data Sekolah</h1>
            <p class="text-sm text-secondary">Kelola identitas dan profil sekolah.</p>
        </div>

        <button type="button"
            hx-get="{{ route('admin.master-data.school.edit') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-primary/30 shrink-0">
            <i data-lucide="pencil" class="size-4"></i>
            <span>Edit Data Sekolah</span>
        </button>
    </div>

    {{-- ── CARD: Identitas Sekolah ── --}}
    <div class="bg-white rounded-2xl border border-border px-6 py-5">
        <div class="flex items-center gap-5">
            <div class="relative shrink-0">
                <template x-if="school.logo">
                    <img :src="school.logo" alt="Logo Sekolah" class="w-16 h-16 rounded-xl object-cover shadow-sm border border-border">
                </template>
                <template x-if="!school.logo">
                    <div class="w-16 h-16 rounded-xl bg-primary/10 flex items-center justify-center shadow-sm border border-primary/20">
                        <i data-lucide="school" class="size-7 text-primary"></i>
                    </div>
                </template>
            </div>

            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-bold text-foreground leading-snug" x-text="school.name || 'Belum diisi'"></h2>
                <span class="mt-1.5 inline-flex items-center gap-1.5 text-[10px] font-bold text-primary bg-primary/10 border border-primary/20 px-2.5 py-1 rounded-full tracking-wider uppercase">
                    <i data-lucide="landmark" class="size-3"></i>
                    <span x-text="school.status ? (school.status === 'negeri' ? 'Sekolah Negeri' : 'Sekolah Swasta') : '-'"></span>
                </span>
            </div>
        </div>
    </div>

    {{-- ── CARD: Data Umum ── --}}
    <div class="bg-white rounded-2xl border border-border">
        <div class="px-6 py-5 border-b border-border">
            <h5 class="text-[10px] font-bold text-secondary uppercase tracking-widest">Data Umum</h5>
            <p class="text-xs text-secondary mt-0.5">Identitas resmi sekolah</p>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <template x-for="field in generalFields" :key="field.key">
                <div>
                    <label class="block text-[10px] font-bold text-secondary uppercase tracking-widest mb-1.5" x-text="field.label"></label>
                    <div class="w-full rounded-xl bg-slate-50 border border-border text-foreground text-sm px-4 py-3 cursor-default select-none flex items-center min-h-[46px]">
                        <span x-text="field.value" class="font-medium"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── CARD: Alamat ── --}}
    <div class="bg-white rounded-2xl border border-border">
        <div class="px-6 py-5 border-b border-border">
            <h5 class="text-[10px] font-bold text-secondary uppercase tracking-widest">Alamat & Kontak</h5>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <template x-for="field in contactFields" :key="field.key">
                <div>
                    <label class="block text-[10px] font-bold text-secondary uppercase tracking-widest mb-1.5" x-text="field.label"></label>
                    <div class="w-full rounded-xl bg-slate-50 border border-border text-foreground text-sm px-4 py-3 cursor-default select-none flex items-center min-h-[46px]">
                        <span x-text="field.value" class="font-medium"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── CARD: Pejabat Sekolah ── --}}
    <div class="bg-white rounded-2xl border border-border">
        <div class="px-6 py-5 border-b border-border">
            <h5 class="text-[10px] font-bold text-secondary uppercase tracking-widest">Pejabat Sekolah</h5>
            <p class="text-xs text-secondary mt-0.5">Digunakan otomatis untuk tanda tangan surat</p>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <template x-for="field in staffFields" :key="field.key">
                <div>
                    <label class="block text-[10px] font-bold text-secondary uppercase tracking-widest mb-1.5" x-text="field.label"></label>
                    <div class="w-full rounded-xl bg-slate-50 border border-border text-foreground text-sm px-4 py-3 cursor-default select-none flex items-center min-h-[46px]">
                        <span x-text="field.value" class="font-medium"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- TEMPAT RENDER MODAL HTMX --}}
    <div id="modal-form-container"></div>

</div>
@endsection

@push('scripts')
<script>
    function schoolApp(initialData) {
        return {
            school: initialData,

            get generalFields() {
                return [{
                        key: 'npsn',
                        label: 'NPSN',
                        value: this.school.npsn || '-'
                    },
                    {
                        key: 'nss',
                        label: 'NSS',
                        value: this.school.nss || '-'
                    },
                    {
                        key: 'establishment_decree_number',
                        label: 'No. SK Pendirian',
                        value: this.school.establishment_decree_number || '-'
                    },
                    {
                        key: 'establishment_date',
                        label: 'Tanggal Pendirian',
                        value: this.school.establishment_date || '-'
                    },
                    {
                        key: 'supervising_office_status',
                        label: 'Status Naungan Dinas',
                        value: this.school.supervising_office_status || '-'
                    },
                    {
                        key: 'parent_institution',
                        label: 'Instansi Induk',
                        value: this.school.parent_institution || '-'
                    },
                ];
            },

            get contactFields() {
                const alamat = [this.school.address, this.school.rt ? `RT ${this.school.rt}` : null, this.school.rw ? `RW ${this.school.rw}` : null]
                    .filter(Boolean).join(' ');

                return [{
                        key: 'address',
                        label: 'Alamat',
                        value: alamat || '-'
                    },
                    {
                        key: 'village',
                        label: 'Kelurahan/Desa',
                        value: this.school.village || '-'
                    },
                    {
                        key: 'district',
                        label: 'Kecamatan',
                        value: this.school.district || '-'
                    },
                    {
                        key: 'regency',
                        label: 'Kabupaten/Kota',
                        value: this.school.regency || '-'
                    },
                    {
                        key: 'province',
                        label: 'Provinsi',
                        value: this.school.province || '-'
                    },
                    {
                        key: 'postal_code',
                        label: 'Kode Pos',
                        value: this.school.postal_code || '-'
                    },
                    {
                        key: 'phone',
                        label: 'Telepon',
                        value: this.school.phone || '-'
                    },
                    {
                        key: 'email',
                        label: 'Email',
                        value: this.school.email || '-'
                    },
                    {
                        key: 'website',
                        label: 'Website',
                        value: this.school.website || '-'
                    },
                ];
            },

            get staffFields() {
                return [{
                        key: 'headmaster',
                        label: 'Kepala Sekolah',
                        value: this.school.headmaster_name || '-'
                    },
                    {
                        key: 'student_affairs_deputy',
                        label: 'Wakil Bid. Kesiswaan',
                        value: this.school.student_affairs_deputy_name || '-'
                    },
                    {
                        key: 'administration_coordinator',
                        label: 'Koordinator Tata Usaha',
                        value: this.school.administration_coordinator_name || '-'
                    },
                ];
            },

            init() {
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            }
        }
    }
</script>
@endpush