@php
// Persiapan data opsi gender untuk komponen x-ui.select
$genderOptionsList = [
['value' => '', 'label' => 'Semua Gender'],
['value' => 'L', 'label' => 'Laki-Laki'],
['value' => 'P', 'label' => 'Perempuan'],
];
@endphp

@extends('layouts.main.admin')

@section('title', 'Detail Rombongan Belajar')
@section('page_title', 'Detail Rombongan Belajar')

@section('content')
<div class="px-5 py-8 md:p-8">
    {{-- 1. HEADER & TOMBOL AKSI --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">

        {{-- Bagian Kiri: Judul dan Tombol Kembali (Rata Kiri-Kanan khusus di Mobile) --}}
        <div class="flex items-start justify-between w-full md:w-auto md:flex-1 gap-4">
            <div class="min-w-0 pr-2">
                @php
                $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII'];
                $gradeLabel = $grades[$classGroup->grade_level] ?? $classGroup->grade_level;
                $displayName = $classGroup->name ?: "{$gradeLabel} {$classGroup->concentration->name} {$classGroup->group_number}";

                // Kenaikan: tingkat 10 & 11, semester genap. Kelulusan: tingkat 12, semester genap.
                $activeSemester = $classGroup->semester;
                $isEvenSemester = $activeSemester?->isEven() ?? false;
                $hasNextSemester = $activeSemester?->next !== null;

                $canPromote = in_array($classGroup->grade_level, ['10', '11'], true) && $isEvenSemester && $hasNextSemester;
                $canGraduate = $classGroup->grade_level === '12' && $isEvenSemester && $hasNextSemester;
                @endphp
                <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1 truncate">{{ $displayName }}</h1>
                <p class="text-sm text-secondary truncate">{{ $classGroup->concentration->name ?? '-' }}</p>
            </div>

            {{-- TOMBOL KEMBALI (Tampil di sebelah kanan judul pada perangkat Mobile) --}}
            <button type="button" onclick="history.back()" title="Kembali"
                class="md:hidden flex items-center justify-center size-9 shrink-0 ring-1 ring-border hover:ring-emerald-500 rounded-full text-foreground hover:text-emerald-500 transition-all bg-white cursor-pointer shadow-sm">
                <i data-lucide="arrow-left" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Bagian Kanan: Grup Tombol Aksi (Tersusun ke bawah di Mobile, ke samping di Desktop) --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full md:w-auto shrink-0 mt-2 md:mt-0">

            {{-- TOMBOL TAMBAH --}}
            <button type="button"
                hx-get="{{ route('admin.students.group.add-student.form', $classGroup->id) }}"
                hx-target="#modal-container"
                hx-swap="innerHTML"
                class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-600/30 cursor-pointer w-full sm:w-auto">
                <i data-lucide="plus" class="size-4 shrink-0"></i>
                <span>Tambah</span>
            </button>

            @if ($canPromote)
            <div class="w-full sm:w-auto">
                <div x-data="{ open: false }" @click.outside="open = false" class="relative block sm:inline-block w-full sm:w-auto text-left">
                    <button type="button" @click="open = !open"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-secondary text-white font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer focus:outline-none w-full sm:w-auto">
                        <i data-lucide="copy-check" class="size-4 pointer-events-none shrink-0"></i>
                        <span>Kenaikan</span>
                        <i data-lucide="chevron-down" class="size-4 ml-1 transition-transform pointer-events-none shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 right-0 sm:left-auto sm:right-0 z-20 mt-2 w-auto sm:w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left">

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Aksi</p>

                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.promotion.form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="copy-check" class="size-4 text-secondary pointer-events-none"></i> Proses Kenaikan
                        </button>

                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.promotion.cancel-form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors cursor-pointer text-left">
                            <i data-lucide="undo-2" class="size-4 pointer-events-none"></i> Pembatalan
                        </button>
                    </div>
                </div>
            </div>
            @elseif ($canGraduate)
            <div class="w-full sm:w-auto">
                <div x-data="{ open: false }" @click.outside="open = false" class="relative block sm:inline-block w-full sm:w-auto text-left">
                    <button type="button" @click="open = !open"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-primary text-white font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer focus:outline-none w-full sm:w-auto">
                        <i data-lucide="graduation-cap" class="size-4 pointer-events-none shrink-0"></i>
                        <span>Kelulusan</span>
                        <i data-lucide="chevron-down" class="size-4 ml-1 transition-transform pointer-events-none shrink-0" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 right-0 sm:left-auto sm:right-0 z-20 mt-2 w-auto sm:w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left">

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Aksi</p>

                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.graduation.form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="graduation-cap" class="size-4 text-secondary pointer-events-none"></i> Proses Kelulusan
                        </button>

                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.graduation.cancel-form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors cursor-pointer text-left">
                            <i data-lucide="undo-2" class="size-4 pointer-events-none"></i> Pembatalan
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- TOMBOL KEMBALI (Tampil berdampingan di Desktop) --}}
            <button type="button" onclick="history.back()" title="Kembali"
                class="hidden md:flex items-center justify-center size-10 shrink-0 ring-1 ring-border hover:ring-emerald-500 rounded-full text-foreground hover:text-emerald-500 transition-all bg-white cursor-pointer shadow-sm">
                <i data-lucide="arrow-left" class="size-4 pointer-events-none"></i>
            </button>
        </div>
    </div>

    {{-- Target kosong untuk modal kenaikan/kelulusan yang di-load lewat HTMX --}}
    <div id="modal-container"></div>

    {{-- 2. RINGKASAN STATS CARD (Wali Kelas & Anggota) --}}
    <div id="stats-cards-container"
        hx-get="{{ route('admin.students.group.show', $classGroup->id) }}"
        hx-trigger="refreshClassData from:body"
        hx-select="#stats-cards-container"
        hx-swap="outerHTML"
        class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <x-ui.stat-card
            theme="slate"
            icon="user-check"
            title="Wali Kelas"
            :value="$classGroup->homeroomTeacher->name_with_title ?? 'Belum ditentukan'"
            valueClass="text-lg truncate" />

        <x-ui.stat-card
            theme="success"
            icon="users"
            title="Total Anggota"
            :value="$classGroup->total_students_count" />

        <x-ui.stat-card
            theme="blue"
            icon="user"
            title="Siswa Laki-laki"
            :value="$classGroup->male_students_count" />

        <x-ui.stat-card
            theme="pink"
            icon="user"
            title="Siswa Perempuan"
            :value="$classGroup->female_students_count" />

    </div>

    {{-- 3. AREA TABEL PESERTA DIDIK --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Siswa di Kelas Ini</h2>
                <p class="text-sm text-secondary mt-1">Gunakan pencarian untuk merampingkan data.</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto" id="group-students-filter-form"
                x-data="{ searchQuery: '{{ $search ?? '' }}' }">

                {{-- Dropdown Filter Gender menggunakan x-ui.select dengan Ikon --}}
                <div class="relative w-full sm:w-44"
                    hx-get="{{ route('admin.students.group.show', $classGroup->id) }}"
                    {{-- PERBAIKAN 1: Menargetkan trigger secara eksplisit ke input name='filter_gender' --}}
                    hx-trigger="change from:[name='filter_gender'], input from:[name='filter_gender']"
                    hx-target="#students-container"
                    hx-select="#students-container"
                    {{-- PERBAIKAN 2: Menambahkan hx-swap="outerHTML" agar tabel tidak bersarang --}}
                    hx-swap="outerHTML"
                    hx-include="[name='search'], [name='filter_gender']"
                    hx-push-url="true">

                    <i data-lucide="users" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-secondary z-10 pointer-events-none"></i>

                    <div class="[&>div>button]:pl-10">
                        <x-ui.select
                            name="filter_gender"
                            :options="$genderOptionsList"
                            value="{{ $filterGender ?? '' }}"
                            placeholder="Semua Gender" />
                    </div>
                </div>

                {{-- Search Box dengan Tombol X Interaktif --}}
                <div class="relative w-full sm:w-56 md:w-64 flex items-center">
                    <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                        :class="searchQuery.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Cari nama atau NIS..."
                        autocomplete="off"
                        hx-get="{{ route('admin.students.group.show', $classGroup->id) }}"
                        hx-trigger="keyup changed delay:400ms, search, refreshClassData from:body"
                        hx-target="#students-container"
                        hx-select="#students-container"
                        {{-- PERBAIKAN 2: Menambahkan hx-swap="outerHTML" --}}
                        hx-swap="outerHTML"
                        hx-include="[name='search'], [name='filter_gender']"
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

        {{-- MEMANGGIL KOMPONEN TABEL DARI DATA SISWA UTAMA --}}
        @include('pages.admin.students.groups.partials._students-table', ['students' => $students])

    </div>
</div>
@endsection