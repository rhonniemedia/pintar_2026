@extends('layouts.main.admin')

@section('title', 'Mutasi Keluar')
@section('page_title', 'Mutasi Peserta Didik')
@section('page_subtitle', 'Data siswa yang pindah atau keluar dari sekolah')

@section('content')
<div class="p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Mutasi Keluar</h1>
            <p class="text-sm text-secondary">
                Kelola data peserta didik yang pindah ke luar sekolah.
                @if($semesterAktif)
                Semester aktif: <span class="font-semibold text-foreground">{{ $semesterAktif->code }}</span>
                @else
                <span class="text-error font-semibold">Belum ada semester aktif.</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Tombol dimatikan sementara karena fokusnya hanya menampilkan data dulu --}}
            <button type="button" disabled
                class="flex items-center gap-2 px-5 py-2.5 bg-primary/50 text-white rounded-full font-semibold text-sm cursor-not-allowed">
                <i data-lucide="plus" class="size-4"></i>
                <span>Proses Mutasi Keluar</span>
            </button>
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="flex items-center gap-6 border-b border-border mb-6">
        <a href="{{ route('admin.students.transfer.in.index') }}"
            class="pb-3 text-sm font-bold border-b-2 transition-colors border-transparent text-secondary hover:text-foreground hover:border-border">
            Mutasi Masuk
        </a>
        <a href="{{ route('admin.students.transfer.out.index') }}"
            class="pb-3 text-sm font-bold border-b-2 transition-colors border-primary text-primary">
            Mutasi Keluar
        </a>
    </div>

    {{-- KOTAK KONTEN UTAMA --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Peserta Didik Keluar</h2>
                <p class="text-sm text-secondary mt-1">Menampilkan riwayat siswa pindah/keluar pada semester aktif.</p>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIS..."
                        hx-get="{{ route('admin.students.transfer.out.index') }}"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#mutasi-keluar-container"
                        hx-swap="outerHTML"
                        hx-push-url="true"
                        class="h-11 w-56 md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>
            </div>
        </div>

        @include('pages.admin.students.transfers.out.partials._table', ['data' => $data, 'search' => $search])
    </div>
</div>
@endsection