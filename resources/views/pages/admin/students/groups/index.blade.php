@extends('layouts.main.admin')

@section('title', 'Rombongan Belajar')
@section('page_title', 'Rombongan Belajar')
@section('page_subtitle', 'Kelola data rombel dan penempatan siswa')

@section('content')
<div class="p-8"
    x-data="{ 
        filterModalOpen: false,
        formModalOpen: false,
        isFilterActive: {{ ($filterGrade || $filterConcentration) ? 'true' : 'false' }},
        checkFilterStatus() {
            const grade = document.querySelector('[name=filter_grade]')?.value || '';
            const concentration = document.querySelector('[name=filter_concentration]')?.value || '';

            this.isFilterActive = (grade !== '' || concentration !== '');
        },
        resetFormModal() {
            document.getElementById('form-modal-content').innerHTML = `
                <div class=\'flex items-center justify-center h-40\'>
                    <i data-lucide=\'loader-2\' class=\'size-8 text-primary animate-spin\'></i>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }"
    @class-group-saved.window="formModalOpen = false"
    @htmx:after-request.document="checkFilterStatus()">

    {{-- 1. PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Rombongan Belajar</h1>
            <p class="text-sm text-secondary">Kelola data rombel dan penempatan siswa secara menyeluruh.</p>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
            <button type="button"
                @click="formModalOpen = true"
                hx-get="{{ route('admin.students.group.create') }}"
                hx-target="#form-modal-content"
                class="flex items-center justify-center gap-2 px-3 py-2.5 sm:px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30 whitespace-nowrap">
                <i data-lucide="plus" class="size-4 shrink-0"></i>
                <span>Tambah</span>
            </button>

            <button type="button"
                @click="formModalOpen = true"
                hx-get="{{ route('admin.students.attendance.modal') }}"
                hx-target="#form-modal-content"
                class="flex items-center justify-center gap-2 px-3 py-2.5 sm:px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-emerald-600/30 whitespace-nowrap">
                <i data-lucide="printer" class="size-4 shrink-0"></i>
                <span>Daftar Hadir</span>
            </button>

            <a href="{{ route('admin.students.group.index') }}"
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin');"
                class="col-span-2 sm:col-span-1 flex items-center justify-center gap-2 px-3 py-2.5 sm:px-4 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all bg-white cursor-pointer whitespace-nowrap">
                <i id="refresh-icon" data-lucide="refresh-cw" class="size-4 shrink-0"></i>
                <span>Segarkan</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    @include('pages.admin.students.groups.partials._stats-cards', [
    'totalStats' => $totalStats,
    'grade12Stats' => $grade12Stats,
    'grade11Stats' => $grade11Stats,
    'grade10Stats' => $grade10Stats,
    ])

    {{-- Tabel Data --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Rombel</h2>
                <p class="text-sm text-secondary mt-1">Gunakan fitur pencarian dan filter untuk merampingkan data.</p>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari rombel..."
                        hx-get="{{ route('admin.students.group.index') }}"
                        hx-trigger="keyup changed delay:400ms, search, classGroupSaved from:body"
                        hx-target="#class-groups-container"
                        hx-select="#class-groups-container"
                        hx-swap="outerHTML"
                        hx-include="#class-group-filter-form"
                        hx-push-url="true"
                        class="h-11 w-full sm:w-56 md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                <button
                    type="button"
                    @click="filterModalOpen = true"
                    title="Filter"
                    class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-white hover:bg-muted transition-colors cursor-pointer focus:outline-none">
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

        @include('pages.admin.students.groups.partials._table', compact('classGroups'))

    </div>

    @include('pages.admin.students.groups.partials._filter-modal', [
    'filterGrade' => $filterGrade,
    'filterConcentration' => $filterConcentration,
    'concentrationOptions' => $concentrationOptions,
    ])

    {{-- MODAL ADD / EDIT KOSONG --}}
    <div id="form-modal-content"> </div>

</div>
@endsection