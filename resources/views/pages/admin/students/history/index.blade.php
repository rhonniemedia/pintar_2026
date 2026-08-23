@extends('layouts.main.admin')

@section('title', 'Riwayat Peserta Didik')
@section('page_title', 'Riwayat Peserta Didik')
@section('page_subtitle', 'Rekam jejak siswa yang sudah lulus, keluar, atau pindah')

@section('content')
{{-- Penyesuaian padding utama responsif --}}
<div class="px-5 py-8 md:p-8"
    x-data="{
        filterModalOpen: false,
        isFilterActive: {{ ($filterExitStatus || $filterConcentration || $filterExitSemester || $filterYear) ? 'true' : 'false' }},
        checkFilterStatus() {
            const exitStatus = document.querySelector('[name=filter_exit_status]')?.value || '';
            const concentration = document.querySelector('[name=filter_concentration]')?.value || '';
            const exitSemester = document.querySelector('[name=filter_exit_semester]')?.value || '';
            const year = document.querySelector('[name=filter_year]')?.value || '';

            this.isFilterActive = (exitStatus !== '' || concentration !== '' || exitSemester !== '' || year !== '');
        }
    }"
    @htmx:after-request.document="checkFilterStatus()">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-foreground mb-1">Riwayat Peserta Didik</h1>
            <p class="text-xs sm:text-sm text-secondary">Data siswa yang sudah tidak aktif: lulus, keluar, atau pindah sekolah.</p>
        </div>

        {{-- Tombol aksi dibuat grid agar berdampingan rapi (50:50) di layar mobile --}}
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-3 w-full md:w-auto">
            <button type="button" class="flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30 whitespace-nowrap">
                <i data-lucide="download" class="size-4 shrink-0"></i>
                <span>Download</span>
            </button>

            <a href="{{ route('admin.students.history.index') }}"
                onclick="document.getElementById('refresh-icon-riwayat').classList.add('animate-spin');"
                class="flex items-center justify-center gap-2 px-3 sm:px-4 py-2.5 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all bg-white cursor-pointer whitespace-nowrap">
                <i id="refresh-icon-riwayat" data-lucide="refresh-cw" class="size-4 shrink-0"></i>
                <span>Segarkan</span>
            </a>
        </div>
    </div>

    @include('pages.admin.students.history.partials._stats-cards', [
    'totalHistoryStats' => $totalHistoryStats,
    'transferInStats' => $transferInStats,
    'transferOutStats' => $transferOutStats,
    'droppedOutStats' => $droppedOutStats,
    'deceasedStats' => $deceasedStats,
    ])

    {{-- Penyesuaian padding card pada layar mobile --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-foreground">Daftar Riwayat</h2>
                <p class="text-xs sm:text-sm text-secondary mt-0.5 sm:mt-1">Gunakan fitur pencarian dan filter untuk merampingkan data.</p>
            </div>

            {{-- Pastikan container form memenuhi lebar pada layar kecil --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full md:w-auto" x-data="{ searchQuery: '{{ $search ?? '' }}' }">

                {{-- 1. Filter Tahun (Custom Select bergaya x-ui.select) --}}
                <div class="relative w-full sm:w-44 shrink-0"
                    x-data="{
                        openYear: false,
                        yearValue: '{{ $filterYear ?? '' }}',
                        yearLabel: '{{ $filterYear ? $filterYear : 'Semua Tahun' }}',
                        selectYear(val, label) {
                            this.yearValue = val;
                            this.yearLabel = label;
                            this.openYear = false;
                            // Memicu HTMX setelah value berubah
                            this.$nextTick(() => {
                                this.$refs.yearInput.dispatchEvent(new Event('change', { bubbles: true }));
                            });
                        }
                    }"
                    @click.outside="openYear = false"
                    @keydown.escape.window="openYear = false">

                    {{-- Input tersembunyi sebagai penggerak HTMX --}}
                    <input
                        type="hidden"
                        name="filter_year"
                        x-ref="yearInput"
                        x-model="yearValue"
                        hx-get="{{ route('admin.students.history.index') }}"
                        hx-trigger="change"
                        hx-target="#students-history-container"
                        hx-select="#students-history-container"
                        hx-swap="outerHTML"
                        hx-include="#student-history-filter-form, [name='search']"
                        hx-push-url="true">

                    {{-- Tombol Dropdown (Tampilan Select) --}}
                    <button
                        type="button"
                        @click="openYear = !openYear"
                        class="flex items-center justify-between w-full h-11 bg-white border rounded-xl px-3.5 text-sm focus:outline-none transition-all cursor-pointer ring-offset-1 focus:ring-2 focus:ring-primary/20"
                        :class="yearValue !== '' ? 'border-primary/50 text-primary font-medium' : 'border-border text-foreground hover:border-gray-300'">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <i data-lucide="calendar-days" class="size-4 shrink-0" :class="yearValue !== '' ? 'text-primary' : 'text-secondary'"></i>
                            <span x-text="yearLabel" class="truncate"></span>
                        </div>

                        {{-- Chevron bolak-balik --}}
                        <i data-lucide="chevron-down"
                            class="size-4 shrink-0 transition-transform duration-300 ml-2"
                            :class="[
                               yearValue !== '' ? 'text-primary' : 'text-secondary',
                               openYear ? 'rotate-180' : ''
                           ]"></i>
                    </button>

                    {{-- Isi Dropdown (Options) --}}
                    <div
                        x-show="openYear"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 top-full mt-2 w-full bg-white rounded-xl shadow-lg border border-border py-1.5 z-[100] max-h-60 overflow-y-auto"
                        style="display: none;">

                        <button
                            type="button"
                            @click="selectYear('', 'Semua Tahun')"
                            class="flex items-center w-full px-3.5 py-2.5 text-sm transition-colors text-left"
                            :class="yearValue === '' ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground hover:bg-muted'">
                            Semua Tahun
                        </button>

                        @foreach ($yearOptions as $year)
                        <button
                            type="button"
                            @click="selectYear('{{ $year }}', '{{ $year }}')"
                            class="flex items-center w-full px-3.5 py-2.5 text-sm transition-colors text-left"
                            :class="yearValue === '{{ $year }}' ? 'bg-primary/10 text-primary font-semibold' : 'text-foreground hover:bg-muted'">
                            {{ $year }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Search Box (Sekarang berada di tengah/kanan) --}}
                <div class="relative flex-1 w-full sm:w-56 md:w-64 flex items-center">
                    <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari peserta..."
                        autocomplete="off"
                        hx-get="{{ route('admin.students.history.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#students-history-container"
                        hx-select="#students-history-container"
                        hx-swap="outerHTML"
                        hx-include="#student-history-filter-form, [name='filter_year']"
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

                {{-- 3. Filter Modal Button --}}
                <button
                    type="button"
                    @click="filterModalOpen = true"
                    title="Filter Lengkap"
                    class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-border bg-white hover:bg-muted transition-colors cursor-pointer focus:outline-none shrink-0">
                    <i data-lucide="filter" class="size-4 text-secondary"></i>

                    <span
                        x-show="isFilterActive"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200 transform"
                        x-transition:enter-start="opacity-0 scale-50"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150 transform"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-50"
                        class="absolute -top-1 -right-1 flex h-3 w-3">

                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-primary border-2 border-white"></span>
                    </span>
                </button>
            </div>
        </div>

        @include('pages.admin.students.history.partials._table', compact('students'))

    </div>

    @include('pages.admin.students.history.partials._filter-modal', [
    'filterExitStatus' => $filterExitStatus,
    'filterConcentration' => $filterConcentration,
    'filterExitSemester' => $filterExitSemester,
    'concentrationOptions' => $concentrationOptions,
    'exitSemesterOptions' => $exitSemesterOptions,
    'exitStatusOptions' => $exitStatusOptions,
    ])

</div>
@endsection