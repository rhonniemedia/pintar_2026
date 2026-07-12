@extends('layouts.main.admin')

@section('title', 'Data Peserta Didik')
@section('page_title', 'Peserta Didik')
@section('page_subtitle', 'Kelola basis data akademik siswa')

@section('content')
<div class="p-8"
    x-data="{ 
        filterModalOpen: false,
        isFilterActive: {{ ($filterGrade || $filterGender || $filterReligion || $filterSpecialNeeds || $filterConcentration) ? 'true' : 'false' }},
        checkFilterStatus() {
            const grade = document.querySelector('[name=filter_grade]')?.value || '';
            const gender = document.querySelector('[name=filter_gender]')?.value || '';
            const religion = document.querySelector('[name=filter_religion]')?.value || '';
            const special = document.querySelector('[name=filter_special_needs]')?.value || '';
            const concentration = document.querySelector('[name=filter_concentration]')?.value || '';
            
            this.isFilterActive = (grade !== '' || gender !== '' || religion !== '' || special !== '' || concentration !== '');
        }
    }"
    @htmx:after-request.document="checkFilterStatus()">

    {{-- 1. PAGE HEADER BARU (Sesuai gambar referensi) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Data Peserta Didik</h1>
            <p class="text-sm text-secondary">Kelola basis data akademik siswa secara menyeluruh.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30">
                <i data-lucide="download" class="size-4"></i>
                <span>Download</span>
            </button>

            <button type="button" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30">
                <i data-lucide="printer" class="size-4"></i>
                <span>Cetak Laporan</span>
            </button>

            <a href="{{ route('admin.students.data.index') }}"
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin');"
                class="flex items-center gap-2 px-4 py-2.5 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all bg-white cursor-pointer">
                <i id="refresh-icon" data-lucide="refresh-cw" class="size-4"></i>
                <span>Segarkan</span>
            </a>
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

        {{-- Header Tabel (Judul disesuaikan agar tidak duplikat dengan Page Header) --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Siswa</h2>
                <p class="text-sm text-secondary mt-1">Gunakan fitur pencarian dan filter untuk merampingkan data.</p>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari peserta..."
                        hx-get="{{ route('admin.students.data.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#students-container"
                        hx-select="#students-container"
                        hx-swap="outerHTML"
                        hx-include="#student-filter-form"
                        hx-push-url="true"
                        class="h-11 w-56 md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                <button
                    type="button"
                    @click="filterModalOpen = true"
                    title="Filter"
                    class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-border bg-white hover:bg-muted transition-colors cursor-pointer focus:outline-none">
                    <i data-lucide="filter" class="size-4 text-secondary"></i>

                    {{-- Container untuk Titik Merah --}}
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

        @include('pages.admin.students.data.partials._table', compact('students'))

    </div>

    @include('pages.admin.students.data.partials._filter-modal', [
    'filterGrade' => $filterGrade,
    'filterGender' => $filterGender,
    'filterReligion' => $filterReligion,
    'filterSpecialNeeds' => $filterSpecialNeeds,
    'filterConcentration' => $filterConcentration,
    'concentrationOptions' => $concentrationOptions,
    'religionOptions' => $religionOptions,
    ])

</div>
@endsection