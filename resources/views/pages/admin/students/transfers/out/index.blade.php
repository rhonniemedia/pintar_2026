@extends('layouts.main.admin')

@section('title', 'Mutasi Keluar')
@section('page_title', 'Mutasi Peserta Didik')
@section('page_subtitle', 'Data siswa yang pindah atau keluar dari sekolah')

@section('content')
<div class="px-5 py-8 md:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-foreground mb-1">Mutasi Keluar</h1>
            <p class="text-xs sm:text-sm text-secondary">
                Kelola data peserta didik yang pindah ke luar sekolah.
                @if($semesterAktif)
                Semester aktif: <span class="font-semibold text-foreground">{{ $semesterAktif->code }}</span>
                @else
                <span class="text-error font-semibold">Belum ada semester aktif.</span>
                @endif
            </p>
        </div>

        {{-- Tombol aksi dibuat membentang penuh (w-full) di layar mobile --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <button type="button"
                hx-get="{{ route('admin.students.transfer.out.create') }}"
                hx-target="#mutasi-keluar-container" hx-swap="afterend"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2.5 w-full sm:w-auto bg-primary hover:bg-primary-focus text-white rounded-full font-semibold text-xs sm:text-sm transition-colors cursor-pointer shadow-sm shadow-error/30 whitespace-nowrap">
                {{-- Menggunakan ikon user-minus agar senada dengan tab --}}
                <i data-lucide="user-minus" class="size-3.5 sm:size-4 shrink-0"></i>
                <span>Mutasi Keluar</span>
            </button>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="flex items-center gap-5 sm:gap-6 border-b border-border mb-6 overflow-x-auto scrollbar-hide">
        <a href="{{ route('admin.students.transfer.in.index') }}"
            class="flex items-center gap-2 pb-3 text-sm font-bold border-b-2 whitespace-nowrap transition-colors border-transparent text-secondary hover:text-foreground hover:border-border">
            <i data-lucide="user-plus" class="size-4"></i>
            <span>Mutasi Masuk</span>
        </a>
        <a href="{{ route('admin.students.transfer.out.index') }}"
            class="flex items-center gap-2 pb-3 text-sm font-bold border-b-2 whitespace-nowrap transition-colors border-primary text-primary">
            <i data-lucide="user-minus" class="size-4"></i>
            <span>Mutasi Keluar</span>
        </a>
    </div>

    {{-- KOTAK KONTEN UTAMA --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-foreground">Daftar Peserta Didik Keluar</h2>
                <p class="text-xs sm:text-sm text-secondary mt-0.5 sm:mt-1">Menampilkan riwayat siswa pindah/keluar pada semester aktif.</p>
            </div>

            {{-- Form pencarian menyesuaikan lebar layar --}}
            <div class="flex items-center gap-2 w-full md:w-auto" x-data="{ searchQuery: '{{ $search ?? '' }}' }">
                {{-- Input Pencarian ditarik agar mengisi sisa ruang (flex-1) di mobile --}}
                <div class="relative flex-1 sm:flex-none sm:w-56 md:w-64 flex items-center">
                    <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari nama / NIS..."
                        autocomplete="off"
                        hx-get="{{ route('admin.students.transfer.out.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#mutasi-keluar-container"
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

        @include('pages.admin.students.transfers.out.partials._table', ['data' => $data, 'search' => $search])
    </div>

    {{-- MODAL ADD / EDIT KOSONG --}}
    <div id="form-modal-content"> </div>

    {{-- MODAL DETAIL (Data Peserta Didik / Data Orang Tua) --}}
    <div id="modal-container"></div>

</div>
@endsection