@extends('layouts.main.admin')

@section('title', 'Data Alumni')
@section('page_title', 'Data Alumni & Kelulusan')
@section('page_subtitle', 'Kelola daftar peserta didik yang telah menyelesaikan pendidikan')

@section('content')
<div class="p-8">

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

            <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                {{-- Dropdown Filter Tahun --}}
                <select
                    name="filter_year"
                    hx-get="{{ route('admin.students.graduates.index') }}"
                    hx-include="[name='search']"
                    hx-target="#graduates-container"
                    hx-swap="outerHTML"
                    hx-push-url="true"
                    class="h-11 w-full md:w-40 bg-white border border-border rounded-xl px-4 text-sm focus:outline-none focus:border-primary transition-all">
                    <option value="">Semua Tahun</option>
                    @foreach($yearOptions as $year)
                    <option value="{{ $year }}" {{ $filterYear == $year ? 'selected' : '' }}>Angkatan {{ $year }}</option>
                    @endforeach
                </select>

                {{-- Search Box --}}
                <div class="relative w-full md:w-auto">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nama / NIS..."
                        hx-get="{{ route('admin.students.graduates.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-include="[name='filter_year']"
                        hx-target="#graduates-container"
                        hx-swap="outerHTML"
                        hx-push-url="true"
                        class="h-11 w-full md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>
            </div>
        </div>

        @include('pages.admin.students.graduates.partials._table', ['graduates' => $graduates])

    </div>
</div>
@endsection