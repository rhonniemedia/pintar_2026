@php
// Persiapan data opsi jenis surat untuk x-ui.select
$letterTypeOptionsList = [
['value' => '', 'label' => 'Semua Jenis Surat']
];
if (isset($letterTypes)) {
foreach ($letterTypes as $type) {
$letterTypeOptionsList[] = [
'value' => $type->value,
'label' => $type->label()
];
}
}
@endphp

@extends('layouts.main.admin')

@section('title', 'Persuratan Peserta Didik')
@section('page_title', 'Persuratan Peserta Didik')
@section('page_subtitle', 'Riwayat surat keterangan yang pernah diterbitkan')

@section('content')
<div class="p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Persuratan Peserta Didik</h1>
            <p class="text-sm text-secondary">Kelola dan terbitkan surat keterangan peserta didik.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button"
                hx-get="{{ route('admin.students.letters.create') }}"
                hx-target="#modal-form-container"
                hx-swap="innerHTML"
                class="flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-primary/30">
                <i data-lucide="file-plus-2" class="size-4"></i>
                <span>Buat Surat</span>
            </button>
        </div>
    </div>

    {{-- KOTAK KONTEN UTAMA --}}
    <div class="bg-white rounded-2xl border border-border p-5">

        {{-- Header Tabel & Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Riwayat Surat</h2>
                <p class="text-sm text-secondary mt-1">Menampilkan seluruh surat yang pernah diterbitkan.</p>
            </div>

            <form id="filter-form" class="flex flex-col sm:flex-row items-center gap-2" x-data="{ searchQuery: '{{ $search ?? '' }}' }" @submit.prevent>

                {{-- Filter Jenis Surat (Menggunakan x-ui.select dengan HTMX Wrapper & Ikon) --}}
                <div class="relative w-full sm:w-52"
                    hx-get="{{ route('admin.students.letters.index') }}"
                    hx-include="#filter-form"
                    hx-trigger="change"
                    hx-target="#letter-list-container"
                    hx-select="#letter-list-container"
                    hx-swap="outerHTML"
                    hx-push-url="true">

                    {{-- Ikon di sebelah kiri --}}
                    <i data-lucide="file-text" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-secondary z-10 pointer-events-none"></i>

                    <div class="[&>div>button]:pl-10">
                        <x-ui.select
                            name="letter_type"
                            :options="$letterTypeOptionsList"
                            value="{{ $letterType ?? '' }}"
                            placeholder="Semua Jenis Surat" />
                    </div>
                </div>

                {{-- Search Input --}}
                <div class="relative w-full sm:w-auto flex items-center">
                    <i data-lucide="search" class="absolute left-3 size-4 transition-colors"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari nama / NIS..."
                        autocomplete="off"
                        hx-get="{{ route('admin.students.letters.index') }}"
                        hx-include="#filter-form"
                        hx-trigger="keyup changed delay:400ms, search"
                        hx-target="#letter-list-container"
                        hx-select="#letter-list-container"
                        hx-swap="outerHTML"
                        hx-push-url="true"
                        class="h-11 w-full sm:w-56 md:w-64 bg-white border rounded-xl pl-10 pr-10 text-sm focus:outline-none focus:border-primary transition-all"
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
            </form>
        </div>

        @include('pages.admin.students.letters.partials._table', ['data' => $data, 'search' => $search, 'letterType' => $letterType])

    </div>

    {{-- MODAL BUAT SURAT --}}
    <div id="modal-form-container"></div>

</div>
@endsection