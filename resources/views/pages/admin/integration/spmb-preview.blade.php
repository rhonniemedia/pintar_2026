@extends('layouts.main.admin')

@section('content')
<div class="p-8 flex flex-col gap-6"
    x-data="{ 
        filterModalOpen: false,
        isFilterActive: {{ (!empty($filterGender) || !empty($filterConcentration)) ? 'true' : 'false' }},
        checkFilterStatus() {
            const gender = document.querySelector('[name=filter_gender]')?.value || '';
            const concentration = document.querySelector('[name=filter_concentration]')?.value || '';
            this.isFilterActive = (gender !== '' || concentration !== '');
        }
    }"
    @htmx:after-request.document="checkFilterStatus()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">Pratinjau Data SPMB</h1>
            <p class="text-sm text-secondary">Verifikasi data ({{ $paginatedData->total() }} siswa) sebelum disimpan ke sistem utama.</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.students.data.index') }}" class="flex items-center gap-2 h-10 px-4 rounded-xl border border-border bg-white text-secondary hover:bg-muted font-medium transition-colors text-sm">
                <i data-lucide="arrow-left" class="size-4"></i> Kembali
            </a>
            <form action="{{ route('admin.integration.spmb.sync.store') }}" method="POST">
                @csrf
                <button type="submit"
                    onclick="this.innerHTML='<i data-lucide=\'loader-2\' class=\'size-4 animate-spin\'></i> Menyimpan...'; this.classList.add('opacity-80', 'cursor-not-allowed');"
                    class="flex items-center gap-2 h-10 px-5 rounded-xl bg-primary text-white hover:bg-primary/90 font-bold transition-colors text-sm shadow-sm cursor-pointer">
                    <i data-lucide="save" class="size-4"></i> Simpan Data
                </button>
            </form>
        </div>
    </div>

    <!-- Papan Tabel, Filter & Search -->
    <div class="bg-white rounded-2xl border border-border p-5 shadow-sm">

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <h2 class="text-lg font-bold text-foreground">Daftar Hasil Sinkronisasi</h2>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, nisn, reg..."
                        hx-get="{{ route('admin.integration.spmb.sync.preview') }}"
                        hx-trigger="keyup[key=='Enter'], search"

                        hx-target="#spmb-table-container"
                        hx-swap="outerHTML"
                        hx-include="#spmb-filter-form"
                        hx-push-url="true"
                        class="h-11 w-full sm:w-56 md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                <button type="button" @click="filterModalOpen = true" title="Filter"
                    class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-white hover:bg-muted transition-colors cursor-pointer">
                    <i data-lucide="filter" class="size-4 text-secondary"></i>
                    <span x-show="isFilterActive" x-cloak class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-primary border-2 border-white"></span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Render Tabel (Akan diganti HTMX saat search/filter) -->
        @include('pages.admin.integration.partials._table-spmb')

    </div>

    <!-- Modal Filter -->
    <x-ui.modal show="filterModalOpen" maxWidth="sm">
        <div class="flex items-center justify-between px-6 py-5 border-b border-border bg-gray-50/50">
            <h3 class="font-bold text-foreground text-lg">Filter Sinkronisasi</h3>
            <button @click="filterModalOpen = false" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        <div id="spmb-filter-form" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Jenis Kelamin</label>
                <select name="filter_gender" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary">
                    <option value="">Semua Gender</option>
                    <option value="L" @selected($filterGender==='L' )>Laki-laki</option>
                    <option value="P" @selected($filterGender==='P' )>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-foreground mb-2">Jurusan</label>
                <select name="filter_concentration" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary">
                    <option value="">Semua Jurusan</option>
                    @foreach($concentrationOptions as $jurusan)
                    <option value="{{ $jurusan }}" @selected($filterConcentration===$jurusan)>{{ $jurusan }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex justify-end gap-3">
            <button type="button" @click="document.querySelectorAll('#spmb-filter-form select').forEach(el => el.value = ''); document.getElementById('btn-apply-filter').click();"
                class="px-4 py-2 rounded-xl text-sm border border-border bg-white text-secondary hover:bg-muted transition-colors font-medium">Reset</button>

            <button type="button" id="btn-apply-filter"
                hx-get="{{ route('admin.integration.spmb.sync.preview') }}"
                hx-include="#spmb-filter-form, [name='search']"
                hx-target="#spmb-table-container" hx-swap="outerHTML" hx-push-url="true"
                @click="filterModalOpen = false"
                class="px-5 py-2.5 bg-primary text-white hover:bg-primary-dark shadow-md text-sm font-bold rounded-xl transition-all cursor-pointer">
                Terapkan Filter
            </button>
        </div>
    </x-ui.modal>
</div>
@endsection