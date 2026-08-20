@php
// Persiapan data opsi tahun angkatan untuk x-ui.select
$yearOptionsList = [
['value' => '', 'label' => 'Semua Tahun']
];
if (isset($yearOptions)) {
foreach ($yearOptions as $year) {
$yearOptionsList[] = [
'value' => $year,
'label' => 'Angkatan ' . $year
];
}
}
@endphp

@extends('layouts.main.admin')

@section('title', 'Data Alumni')
@section('page_title', 'Data Alumni & Kelulusan')
@section('page_subtitle', 'Kelola daftar peserta didik yang telah menyelesaikan pendidikan')

@section('content')
<div class="px-5 py-8 md:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Data Alumni</h1>
            <p class="text-sm text-secondary">Menampilkan riwayat kelulusan dan nomor ijazah peserta didik.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full font-semibold text-sm transition-all shadow-sm">
                <i data-lucide="printer" class="size-4"></i>
                <span>Cetak Rekap Alumni</span>
            </button>
        </div>
    </div>

    {{-- KOTAK KONTEN UTAMA --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel, Filter & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Lulusan</h2>
                <p class="text-sm text-secondary mt-1">Total <span class="font-bold text-foreground">{{ $graduates->total() }}</span> alumni terdaftar.</p>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto" x-data="{ searchQuery: '{{ $search ?? '' }}' }">
                {{-- Dropdown Filter Tahun dengan Ikon Kalender --}}
                <div class="relative w-full md:w-44"
                    hx-get="{{ route('admin.students.graduates.index') }}"
                    hx-include="[name='search']"
                    hx-target="#graduates-container"
                    hx-swap="outerHTML"
                    hx-push-url="true">

                    {{-- Ikon di sebelah kiri --}}
                    <i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-secondary z-10 pointer-events-none"></i>

                    <div class="[&>div>button]:pl-10">
                        <x-ui.select
                            name="filter_year"
                            :options="$yearOptionsList"
                            value="{{ $filterYear ?? '' }}"
                            placeholder="Semua Tahun" />
                    </div>
                </div>

                {{-- Search Box dengan Tombol X Interaktif --}}
                <div class="relative w-full md:w-auto flex items-center">
                    <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari nama / NIS..."
                        autocomplete="off"
                        hx-get="{{ route('admin.students.graduates.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-include="[name='filter_year']"
                        hx-target="#graduates-container"
                        hx-swap="outerHTML"
                        hx-push-url="true"
                        class="h-11 w-full md:w-64 bg-white border rounded-xl pl-10 pr-10 text-sm focus:outline-none focus:border-primary transition-all"
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

        @include('pages.admin.students.graduates.partials._table', ['graduates' => $graduates])

    </div>
</div>
@endsection