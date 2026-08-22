@extends('layouts.main.admin')

@section('title', 'Data Master')
@section('page_title', 'Data Master')
@section('page_subtitle', 'Kelola data referensi utama sistem')

@section('content')
<div class="px-5 py-8 md:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-foreground mb-1">Data Master</h1>
            <p class="text-xs sm:text-sm text-secondary">Kelola data tahun ajaran, semester, dan jurusan.</p>
        </div>

        {{-- Grup Tombol Aksi (Grid 2 Kolom di Mobile, Flex di Desktop) --}}
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center w-full md:w-auto">
            @php
            $modalRoute = match($activeTab) {
            'academic-year' => route('admin.master-data.academic-year.create'),
            'semester' => route('admin.master-data.semester.create'),
            'concentration' => route('admin.master-data.concentration.create'),
            default => '#',
            };
            @endphp

            <button type="button"
                hx-get="{{ $modalRoute }}"
                hx-target="#modal-form-container"
                hx-swap="innerHTML"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2.5 w-full sm:w-auto bg-primary hover:bg-primary-focus text-white rounded-full font-semibold text-xs sm:text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-primary/30 whitespace-nowrap">
                <i data-lucide="plus" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Tambah Data</span>
            </button>

            <a href="{{ route('admin.master-data.academic') }}"
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin');"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2.5 w-full sm:w-auto ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-xs sm:text-sm transition-all bg-white cursor-pointer whitespace-nowrap">
                <i id="refresh-icon" data-lucide="refresh-cw" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Segarkan</span>
            </a>
        </div>
    </div>

    {{-- NAVIGASI TAB MENU --}}
    <div class="flex items-center gap-5 sm:gap-6 border-b border-border mb-6 overflow-x-auto scrollbar-hide">
        <a href="{{ route('admin.master-data.academic', ['tab' => 'academic-year']) }}"
            class="flex items-center gap-2 pb-3 text-sm font-bold border-b-2 whitespace-nowrap transition-colors {{ $activeTab === 'academic-year' ? 'border-primary text-primary' : 'border-transparent text-secondary hover:text-foreground hover:border-border' }}">
            <i data-lucide="calendar-days" class="size-4"></i>
            <span>Tahun Ajaran</span>
        </a>
        <a href="{{ route('admin.master-data.academic', ['tab' => 'semester']) }}"
            class="flex items-center gap-2 pb-3 text-sm font-bold border-b-2 whitespace-nowrap transition-colors {{ $activeTab === 'semester' ? 'border-primary text-primary' : 'border-transparent text-secondary hover:text-foreground hover:border-border' }}">
            <i data-lucide="layers" class="size-4"></i>
            <span>Semester</span>
        </a>
        <a href="{{ route('admin.master-data.academic', ['tab' => 'concentration']) }}"
            class="flex items-center gap-2 pb-3 text-sm font-bold border-b-2 whitespace-nowrap transition-colors {{ $activeTab === 'concentration' ? 'border-primary text-primary' : 'border-transparent text-secondary hover:text-foreground hover:border-border' }}">
            <i data-lucide="graduation-cap" class="size-4"></i>
            <span>Jurusan</span>
        </a>
    </div>

    {{-- KOTAK KONTEN UTAMA --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-foreground">
                    Daftar {{ $activeTab === 'academic-year' ? 'Tahun Ajaran' : ($activeTab === 'semester' ? 'Semester' : 'Jurusan') }}
                </h2>
                <p class="text-xs sm:text-sm text-secondary mt-0.5 sm:mt-1">Gunakan fitur pencarian untuk merampingkan data.</p>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto" x-data="{ searchQuery: '{{ $search ?? '' }}' }">
                {{-- Input Pencarian dengan Tombol X Interaktif --}}
                <div class="relative w-full sm:w-56 md:w-64 flex items-center">
                    <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari data..."
                        autocomplete="off"
                        hx-get="{{ route('admin.master-data.academic', ['tab' => $activeTab]) }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#master-data-container"
                        hx-select="#master-data-container"
                        hx-swap="outerHTML"
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
            </div>
        </div>

        {{-- WADAH TABEL DINAMIS --}}
        @include($viewPartial, ['data' => $data])

        {{-- Tempat HTMX melempar form modal --}}
        <div id="modal-form-container" @closeModal.window="document.getElementById('modal-form-container').innerHTML = ''"></div>

    </div>
</div>
@endsection