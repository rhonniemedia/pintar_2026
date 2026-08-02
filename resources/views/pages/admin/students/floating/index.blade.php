@extends('layouts.main.admin')

@section('title', 'Siswa Mengambang')
@section('page_title', 'Siswa Mengambang')
@section('page_subtitle', 'Kelola data siswa yang belum memiliki rombongan belajar')

@section('content')
<div class="p-8"
    x-data="{ 
        filterModalOpen: false,
        isFilterActive: {{ ($filterGender || $filterConcentration) ? 'true' : 'false' }},
        checkFilterStatus() {
            const gender = document.querySelector('[name=filter_gender]')?.value || '';
            const concentration = document.querySelector('[name=filter_concentration]')?.value || '';
            
            this.isFilterActive = (gender !== '' || concentration !== '');
        }
    }"
    @htmx:after-request.document="checkFilterStatus()">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Siswa Mengambang</h1>
            <p class="text-sm text-secondary">Daftar peserta didik yang belum dimasukkan ke dalam rombongan belajar.</p>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
            <button type="button"
                title="Download data Excel"
                class="flex items-center justify-center gap-2 px-3 py-2.5 sm:px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30 whitespace-nowrap">
                <i data-lucide="file-box" class="size-4 shrink-0"></i>
                <span>Download</span>
            </button>

            <a href="{{ route('admin.students.floating.index') }}"
                title="Segarkan halaman"
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin');"
                class="flex items-center justify-center gap-2 px-3 py-2.5 sm:px-4 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all bg-white cursor-pointer whitespace-nowrap">
                <i id="refresh-icon" data-lucide="refresh-cw" class="size-4 shrink-0"></i>
                <span>Segarkan</span>
            </a>
        </div>
    </div>

    @include('pages.admin.students.data.partials._stats-cards', [
    'isFloating' => true, // Menandakan bahwa ini adalah halaman mengambang
    'totalFloating' => $totalFloating ?? 0,
    'maleFloating' => $maleFloating ?? 0,
    'femaleFloating' => $femaleFloating ?? 0,
    ])

    {{-- Tabel Data --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Siswa</h2>
                <p class="text-sm text-secondary mt-1">Gunakan fitur pencarian dan filter untuk merampingkan data.</p>
            </div>

            {{-- Bagian Kanan: Grup Dropdown, Pencarian, & Filter --}}
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">

                {{-- TOMBOL DROPDOWN: State Mengambang --}}
                <div x-data="{ dropdownOpen: false }" class="relative shrink-0">
                    <button
                        @click="dropdownOpen = !dropdownOpen"
                        @click.away="dropdownOpen = false"
                        type="button"
                        class="relative flex h-11 items-center gap-2 rounded-xl border border-border bg-white px-3 hover:bg-muted transition-colors focus:outline-none cursor-pointer">
                        <i data-lucide="user-x" class="size-4 text-secondary"></i>
                        <span class="text-sm font-medium text-foreground hidden sm:block">Siswa Mengambang</span>
                        <i data-lucide="chevron-down" class="size-4 text-secondary"></i>
                    </button>

                    <div
                        x-show="dropdownOpen"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute right-0 sm:left-0 sm:right-auto top-full mt-2 w-48 rounded-xl border border-border bg-white shadow-lg z-50 p-1.5 flex flex-col gap-1">

                        {{-- Link ke Siswa Aktif --}}
                        <a href="{{ route('admin.students.data.index') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium rounded-lg text-secondary hover:bg-muted hover:text-foreground transition-colors">
                            <i data-lucide="user-check" class="size-4"></i>
                            Siswa Aktif
                        </a>

                        <div class="h-px bg-border mx-1"></div>

                        {{-- State Saat Ini (Aktif) --}}
                        <a href="{{ route('admin.students.floating.index') }}" class="flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg bg-primary/10 text-primary">
                            <div class="flex items-center gap-2">
                                <i data-lucide="user-x" class="size-4"></i>
                                Mengambang
                            </div>
                        </a>
                    </div>
                </div>

                {{-- INPUT PENCARIAN --}}
                <div class="relative flex-1 md:flex-none">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari peserta..."
                        hx-get="{{ route('admin.students.floating.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#students-container"
                        hx-select="#students-container"
                        hx-swap="outerHTML"
                        hx-include="#student-filter-form"
                        hx-push-url="true"
                        class="h-11 w-full sm:w-56 md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                {{-- TOMBOL FILTER --}}
                <button
                    type="button"
                    @click="filterModalOpen = true"
                    title="Filter"
                    class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-white hover:bg-muted transition-colors cursor-pointer focus:outline-none">
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

        @include('pages.admin.students.data.partials._table', compact('students'))

    </div>

    @include('pages.admin.students.data.partials._filter-modal', [
    'filterRoute' => route('admin.students.floating.index'),

    // Atur false untuk menyembunyikan opsi filter
    'showGradeFilter' => false,
    'showReligionFilter' => false,
    'showAgeFilter' => false,
    'showSpecialNeedsFilter' => false,

    'filterGender' => $filterGender ?? null,
    'filterConcentration' => $filterConcentration ?? null,
    'concentrationOptions' => $concentrationOptions ?? [],
    ])

    <div id="modal-container"></div>

</div>
@endsection