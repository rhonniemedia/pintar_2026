@extends('layouts.main.admin')

@section('title', 'Data Peserta Didik')
@section('page_title', 'Peserta Didik')
@section('page_subtitle', 'Kelola basis data akademik siswa')

@section('content')
<div class="px-4 py-6 md:p-8"
    x-data="{ 
        filterModalOpen: false,
        syncModalOpen: false,
        isFilterActive: {{ ($filterGrade || $filterGender || $filterReligion || $filterSpecialNeeds || $filterConcentration || $filterAge || $filterOrphanStatus || $filterFoodAllergy) ? 'true' : 'false' }},
        checkFilterStatus() {
            const grade = document.querySelector('[name=filter_grade]')?.value || '';
            const gender = document.querySelector('[name=filter_gender]')?.value || '';
            const religion = document.querySelector('[name=filter_religion]')?.value || '';
            const special = document.querySelector('[name=filter_special_needs]')?.value || '';
            const concentration = document.querySelector('[name=filter_concentration]')?.value || '';
            const age = document.querySelector('[name=filter_age]')?.value || '';
            const orphanStatus = document.querySelector('[name=filter_orphan_status]')?.value || '';
            const foodAllergy = document.querySelector('[name=filter_food_allergy]')?.value || '';
            
            this.isFilterActive = (grade !== '' || gender !== '' || religion !== '' || special !== '' || concentration !== '' || age !== '' || orphanStatus !== '' || foodAllergy !== '');
        }
    }"
    @htmx:after-request.document="checkFilterStatus()">

    {{-- 1. PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Data Peserta Didik</h1>
            <p class="text-sm text-secondary leading-relaxed">Kelola basis data akademik siswa secara menyeluruh.</p>
        </div>

        {{-- Grup Tombol Aksi (Grid 2 Kolom di Mobile, Flex di Desktop) --}}
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-3 mt-2 md:mt-0">

            {{-- Tombol Tarik Data SPMB --}}
            <button type="button"
                @click="syncModalOpen = true"
                hx-get="{{ route('admin.integration.spmb.sync.info') }}"
                hx-target="#spmb-info-container"
                hx-trigger="click"
                title="Tarik data SPMB"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-full font-semibold text-xs sm:text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-amber-600/30 whitespace-nowrap">
                <i data-lucide="calendar-sync" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Tarik Data</span>
            </button>

            <a id="btn-download-excel"
                href="{{ route('admin.students.data.export') }}"
                data-export-url="{{ route('admin.students.data.export') }}"
                title="Download data Excel (mengikuti filter yang aktif)"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-xs sm:text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30 whitespace-nowrap">
                <i data-lucide="file-box" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Download</span>
            </a>

            <button type="button"
                hx-get="{{ route('admin.students.data.generate-nis-modal') }}"
                hx-target="#modal-container"
                hx-trigger="click"
                title="Generate NIS"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-full font-semibold text-xs sm:text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-purple-600/30 whitespace-nowrap">
                <i data-lucide="hat-glasses" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Generate NIS</span>
            </button>

            @if(auth()->user()->roles->contains('name', 'superadmin'))
            {{-- Tombol Tambah (Khusus Superadmin) --}}
            <button type="button"
                hx-get="#" {{-- Ganti dengan route penambahan siswa Anda --}}
                hx-target="#modal-container"
                title="Tambah Peserta Didik"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full font-semibold text-xs sm:text-sm transition-all cursor-pointer shadow-sm shadow-indigo-600/30 whitespace-nowrap">
                <i data-lucide="file-plus-corner" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Tambah</span>
            </button>
            @else
            {{-- Tombol Segarkan (Untuk Non-Superadmin) --}}
            <a href="{{ route('admin.students.data.index') }}"
                title="Segarkan halaman"
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin');"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-2 sm:px-4 py-2.5 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-xs sm:text-sm transition-all bg-white cursor-pointer whitespace-nowrap">
                <i id="refresh-icon" data-lucide="refresh-cw" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Segarkan</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    @include('pages.admin.students.data.partials._stats-cards', [
    'totalStats' => $totalStats,
    'activeStats' => $activeStats,
    'graduatedStats' => $graduatedStats,
    'inactiveStats' => $inactiveStats,
    'grade12Stats' => $grade12Stats ?? 0,
    'grade11Stats' => $grade11Stats ?? 0,
    'grade10Stats' => $grade10Stats ?? 0,
    ])

    {{-- Tabel Data --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Siswa</h2>
                <p class="text-sm text-secondary mt-1">Gunakan fitur pencarian dan filter untuk merampingkan data.</p>
            </div>

            {{-- Bagian Kanan: Grup Dropdown, Pencarian, & Filter (Mobile Optimized) --}}
            <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 w-full lg:w-auto" x-data="{ searchQuery: '{{ $search ?? '' }}' }">

                {{-- 1. INPUT PENCARIAN (Full width & di atas pada mobile, di tengah pada desktop) --}}
                <div class="relative w-full sm:w-56 md:w-64 flex items-center order-1 sm:order-2">
                    <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari peserta..."
                        autocomplete="off"
                        hx-get="{{ route('admin.students.data.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#students-container"
                        hx-select="#students-container"
                        hx-swap="outerHTML"
                        hx-include="#student-filter-form"
                        hx-push-url="true"
                        class="h-11 w-full bg-white border rounded-xl pl-10 pr-10 text-sm focus:outline-none focus:border-primary transition-all"
                        :class="searchQuery.length > 0 ? 'border-primary/50 text-foreground font-medium' : 'border-border text-foreground'">

                    <button
                        type="button"
                        x-show="searchQuery.length > 0"
                        x-cloak
                        @click="searchQuery = ''; $nextTick(() => $refs.searchInput.dispatchEvent(new Event('search')))"
                        class="absolute right-3 flex items-center justify-center size-5 rounded-full bg-slate-100 hover:bg-error/10 text-secondary hover:text-error transition-all cursor-pointer focus:outline-none">
                        <i data-lucide="x" class="size-3"></i>
                    </button>
                </div>

                {{-- 2. GRUP DROPDOWN & FILTER (Sejajar pada mobile) --}}
                <div class="flex items-center gap-2 w-full sm:w-auto order-2 sm:order-1 sm:order-none">

                    {{-- Dropdown Siswa Aktif / Mengambang --}}
                    <div x-data="{ dropdownOpen: false }" class="relative flex-1 sm:flex-none shrink-0">
                        <button
                            @click="dropdownOpen = !dropdownOpen"
                            @click.away="dropdownOpen = false"
                            type="button"
                            class="relative flex w-full sm:w-auto h-11 items-center justify-between sm:justify-start gap-2 rounded-xl border border-border bg-white px-3 hover:bg-muted transition-colors focus:outline-none cursor-pointer">
                            <div class="flex items-center gap-2">
                                <i data-lucide="users" class="size-4 text-secondary shrink-0"></i>
                                <span class="text-sm font-medium text-foreground">Siswa Aktif</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="size-4 text-secondary transition-transform duration-200 shrink-0"
                                :class="{ 'rotate-180': dropdownOpen }">
                            </i>
                        </button>

                        {{-- Isi Dropdown --}}
                        <div
                            x-show="dropdownOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute left-0 sm:left-auto right-0 sm:right-auto top-full mt-2 w-full sm:w-48 rounded-xl border border-border bg-white shadow-lg z-50 p-1.5 flex flex-col gap-1">

                            <a href="{{ route('admin.students.data.index') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium rounded-lg bg-primary/10 text-primary">
                                <i data-lucide="user-check" class="size-4"></i>
                                Siswa Aktif
                            </a>
                            <div class="h-px bg-border mx-1"></div>
                            <a href="{{ route('admin.students.floating.index') }}" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg text-secondary hover:bg-muted hover:text-foreground transition-colors">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="user-x" class="size-4"></i>
                                    Mengambang
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- Tombol Filter --}}
                    <button
                        type="button"
                        @click="filterModalOpen = true"
                        title="Filter"
                        class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-white hover:bg-muted transition-colors cursor-pointer focus:outline-none order-3 sm:order-none">
                        <i data-lucide="filter" class="size-4 text-secondary"></i>
                        <span
                            x-show="isFilterActive"
                            x-cloak
                            class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary border-2 border-white"></span>
                        </span>
                    </button>

                </div>
            </div>
        </div>

        @include('pages.admin.students.data.partials._table', compact('students'))

    </div>

    {{-- Default filter modal --}}
    @include('pages.admin.students.data.partials._filter-modal', [
    'filterGrade' => $filterGrade,
    'filterGender' => $filterGender,
    'filterReligion' => $filterReligion,
    'filterSpecialNeeds' => $filterSpecialNeeds,
    'filterConcentration' => $filterConcentration,
    'filterAge' => $filterAge,
    'filterAgeDate' => $filterAgeDate,
    'filterOrphanStatus' => $filterOrphanStatus,
    'filterFoodAllergy' => $filterFoodAllergy,
    'concentrationOptions' => $concentrationOptions,
    'religionOptions' => $religionOptions,
    'showFoodAllergyFilter' => true,
    ])

    @include('pages.admin.students.data.partials._sync-spmb-modal')

    <div id="modal-container"></div>

</div>

<script>
    document.getElementById('btn-download-excel')?.addEventListener('click', function(event) {
        event.preventDefault();
        var baseUrl = this.dataset.exportUrl;
        window.location.href = baseUrl + window.location.search;
    });
</script>
@endsection