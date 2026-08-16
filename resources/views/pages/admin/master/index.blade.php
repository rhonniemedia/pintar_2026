@extends('layouts.main.admin')

@section('title', 'Data Master')
@section('page_title', 'Data Master')
@section('page_subtitle', 'Kelola data referensi utama sistem')

@section('content')
<div class="p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Data Master</h1>
            <p class="text-sm text-secondary">Kelola data tahun ajaran, semester, dan jurusan.</p>
        </div>

        <div class="flex items-center gap-2">
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
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-focus text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-primary/30">
                <i data-lucide="plus" class="size-4"></i>
                <span>Tambah Data</span>
            </button>

            <a href="{{ route('admin.master-data.academic') }}"
                onclick="document.getElementById('refresh-icon').classList.add('animate-spin');"
                class="flex items-center gap-2 px-4 py-2.5 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all bg-white cursor-pointer">
                <i id="refresh-icon" data-lucide="refresh-cw" class="size-4"></i>
                <span>Segarkan</span>
            </a>
        </div>
    </div>

    {{-- NAVIGASI TAB MENU --}}
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 border-b border-border">
        <a href="{{ route('admin.master-data.academic', ['tab' => 'academic-year']) }}"
            class="px-5 py-3 border-b-2 text-sm font-bold transition-all {{ $activeTab === 'academic-year' ? 'border-primary text-primary' : 'border-transparent text-secondary hover:text-foreground' }}">
            Tahun Ajaran
        </a>
        <a href="{{ route('admin.master-data.academic', ['tab' => 'semester']) }}"
            class="px-5 py-3 border-b-2 text-sm font-bold transition-all {{ $activeTab === 'semester' ? 'border-primary text-primary' : 'border-transparent text-secondary hover:text-foreground' }}">
            Semester
        </a>
        <a href="{{ route('admin.master-data.academic', ['tab' => 'concentration']) }}"
            class="px-5 py-3 border-b-2 text-sm font-bold transition-all {{ $activeTab === 'concentration' ? 'border-primary text-primary' : 'border-transparent text-secondary hover:text-foreground' }}">
            Jurusan
        </a>
    </div>

    {{-- KOTAK KONTEN UTAMA[cite: 9] --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel & Search[cite: 9] --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">
                    Daftar {{ $activeTab === 'academic-year' ? 'Tahun Ajaran' : ($activeTab === 'semester' ? 'Semester' : 'Jurusan') }}
                </h2>
                <p class="text-sm text-secondary mt-1">Gunakan fitur pencarian untuk merampingkan data.</p>
            </div>

            <div class="flex items-center gap-2" x-data="{ searchQuery: '{{ $search ?? '' }}' }">
                {{-- Input Pencarian dengan Tombol X Interaktif --}}
                <div class="relative w-56 md:w-64 flex items-center">
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