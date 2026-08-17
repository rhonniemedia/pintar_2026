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

// Pemetaan Rute untuk Dropdown (Berasal dari modal create sebelumnya)
$createRoutes = [
\App\Enums\Student\LetterType::ACTIVE->value => 'admin.students.letters.create-active',
\App\Enums\Student\LetterType::GOOD_CONDUCT->value => 'admin.students.letters.create-good-conduct',
\App\Enums\Student\LetterType::POOR_FAMILY->value => 'admin.students.letters.create-poor-family',
];
@endphp

@extends('layouts.main.admin')

@section('title', 'Persuratan Peserta Didik')
@section('page_title', 'Persuratan Peserta Didik')
@section('page_subtitle', 'Riwayat surat keterangan yang pernah diterbitkan')

@section('content')
{{-- Penyesuaian padding utama responsif --}}
<div class="p-4 sm:p-6 md:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5 sm:mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-foreground mb-1">Persuratan Peserta Didik</h1>
            <p class="text-xs sm:text-sm text-secondary">Kelola dan terbitkan surat keterangan peserta didik.</p>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">

            {{-- DROPDOWN BUAT SURAT --}}
            {{-- Tambahkan w-full di layar kecil, md:w-auto di layar besar --}}
            <div class="relative inline-block text-left w-full md:w-auto" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">

                {{-- Tombol dibuat justify-center dan w-full pada layar mobile --}}
                <button type="button"
                    @click="open = !open"
                    class="flex items-center justify-center w-full md:w-auto gap-2 px-5 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-full font-semibold text-sm transition-all duration-300 cursor-pointer shadow-sm shadow-primary/30">
                    <i data-lucide="file-plus-2" class="size-4"></i>
                    <span>Buat Surat</span>
                    <i data-lucide="chevron-down" class="size-4 ml-1 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>

                {{-- Lebar dropdown diubah menjadi full di HP dan w-64 di desktop --}}
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 left-0 md:left-auto mt-2 w-full md:w-64 rounded-2xl border border-border bg-white shadow-xl p-2 flex flex-col text-left origin-top md:origin-top-right z-50">

                    <p class="px-3 pt-2 pb-2 text-[10px] font-bold uppercase tracking-wider text-secondary border-b border-border mb-1.5">
                        Pilih Jenis Surat
                    </p>

                    <div class="max-h-[60vh] overflow-y-auto flex flex-col gap-1">
                        @foreach ($letterTypes as $type)
                        @php $isAvailable = array_key_exists($type->value, $createRoutes); @endphp

                        {{-- Hanya tampilkan surat yang sudah tersedia (isAvailable) --}}
                        @if ($isAvailable)
                        <button type="button"
                            @click="open = false"
                            hx-get="{{ route($createRoutes[$type->value]) }}"
                            hx-target="#modal-form-container"
                            hx-swap="innerHTML"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-secondary hover:bg-slate-100 hover:text-primary transition-all cursor-pointer text-left">
                            <i data-lucide="file-text" class="size-4 text-secondary pointer-events-none"></i>
                            <span>{{ $type->label() }}</span>
                        </button>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- KOTAK KONTEN UTAMA --}}
    {{-- Penyesuaian padding card pada layar mobile --}}
    <div class="bg-white rounded-xl sm:rounded-2xl border border-border p-4 sm:p-5">

        {{-- Header Tabel & Search --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 sm:mb-5">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-foreground">Riwayat Surat</h2>
                <p class="text-xs sm:text-sm text-secondary mt-0.5 sm:mt-1">Menampilkan seluruh surat yang pernah diterbitkan.</p>
            </div>

            {{-- Form filter akan stretch (memenuhi layar) di mobile --}}
            <form id="filter-form" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto" x-data="{ searchQuery: '{{ $search ?? '' }}' }" @submit.prevent>

                {{-- Filter Jenis Surat --}}
                <div class="relative w-full sm:w-48 lg:w-52"
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