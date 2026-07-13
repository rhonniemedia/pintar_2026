@extends('layouts.main.admin')

@section('title', 'Detail Rombongan Belajar')
@section('page_title', 'Detail Rombongan Belajar')

@section('content')
<div class="p-8">
    {{-- 1. HEADER & TOMBOL KEMBALI --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            @php
            $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII'];
            $gradeLabel = $grades[$classGroup->grade_level] ?? $classGroup->grade_level;
            $displayName = $classGroup->name ?: "{$gradeLabel} {$classGroup->concentration->name} {$classGroup->group_number}";

            // Kenaikan: tingkat 10 & 11, semester genap. Kelulusan: tingkat 12, semester genap.
            $activeSemester = $classGroup->semester;
            $isEvenSemester = $activeSemester?->isEven() ?? false;

            // PERBAIKAN: Mengecek ketersediaan semester berikutnya
            $hasNextSemester = $activeSemester?->next !== null;

            // PERBAIKAN: Tombol hanya tampil jika semester genap DAN semester berikutnya sudah ada
            $canPromote = in_array($classGroup->grade_level, ['10', '11'], true) && $isEvenSemester && $hasNextSemester;
            $canGraduate = $classGroup->grade_level === '12' && $isEvenSemester && $hasNextSemester;
            @endphp
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">{{ $displayName }}</h1>
            <p class="text-sm text-secondary">{{ $classGroup->concentration->name ?? '-' }}</p>
        </div>

        <div class="flex items-center gap-2">
            @if ($canPromote)
            <div class="flex items-center gap-2">
                <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                    <button type="button" @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-full bg-secondary text-white font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer focus:outline-none">
                        <i data-lucide="copy-check" class="size-4 pointer-events-none"></i>
                        <span>Kenaikan</span>
                        <i data-lucide="chevron-down" class="size-4 ml-1 transition-transform pointer-events-none" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 z-20 mt-2 w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left">

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Aksi</p>

                        <!-- Proses Kenaikan -->
                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.promotion.form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="copy-check" class="size-4 text-secondary pointer-events-none"></i> Proses Kenaikan
                        </button>

                        <!-- Pembatalan Kenaikan -->
                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.promotion.cancel-form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors cursor-pointer text-left">
                            <i data-lucide="undo-2" class="size-4 pointer-events-none"></i> Pembatalan
                        </button>
                    </div>
                </div>
            </div>
            @elseif ($canGraduate)
            <div class="flex items-center gap-2">
                <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                    <button type="button" @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-full bg-primary text-white font-semibold text-sm hover:opacity-90 transition-opacity cursor-pointer focus:outline-none">
                        <i data-lucide="graduation-cap" class="size-4 pointer-events-none"></i>
                        <span>Kelulusan</span>
                        <i data-lucide="chevron-down" class="size-4 ml-1 transition-transform pointer-events-none" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 z-20 mt-2 w-56 rounded-xl border border-border bg-white shadow-lg py-3 flex flex-col text-left">

                        <p class="px-4 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-secondary">Aksi</p>

                        <!-- Proses Kelulusan -->
                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.graduation.form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-foreground hover:bg-muted transition-colors cursor-pointer text-left">
                            <i data-lucide="graduation-cap" class="size-4 text-secondary pointer-events-none"></i> Proses Kelulusan
                        </button>

                        <!-- Pembatalan Kelulusan -->
                        <button type="button" @click="open = false"
                            hx-get="{{ route('admin.students.group.graduation.cancel-form', $classGroup->id) }}" hx-target="#modal-container" hx-swap="innerHTML"
                            class="flex items-center gap-2 mx-2 px-3 py-2 rounded-lg text-sm text-error hover:bg-error/10 transition-colors cursor-pointer text-left">
                            <i data-lucide="undo-2" class="size-4 pointer-events-none"></i> Pembatalan
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <button type="button" onclick="history.back()"
                class="flex items-center gap-2 px-4 py-2.5 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all bg-white cursor-pointer shadow-sm">
                <i data-lucide="arrow-left" class="size-4 pointer-events-none"></i>
                <span>Kembali ke Daftar</span>
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

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 cursor-default">
            <div class="flex items-center gap-2">
                <div class="size-10 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="user-check" class="size-5 text-slate-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Wali Kelas</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-lg truncate">{{ $classGroup->homeroomTeacher->name ?? 'Belum ditentukan' }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-green-200 cursor-default">
            <div class="flex items-center gap-2">
                <div class="size-10 bg-success/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="size-5 text-success"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Total Anggota</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ $classGroup->total_students_count }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-blue-200 cursor-default">
            <div class="flex items-center gap-2">
                <div class="size-10 bg-blue-500/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="user" class="size-5 text-blue-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Siswa Laki-laki</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl text-blue-600">{{ $classGroup->male_students_count }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-pink-200 cursor-default">
            <div class="flex items-center gap-2">
                <div class="size-10 bg-pink-500/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="user" class="size-5 text-pink-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Siswa Perempuan</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl text-pink-600">{{ $classGroup->female_students_count }}</p>
            </div>
        </div>
    </div>

    {{-- 3. AREA TABEL PESERTA DIDIK (Menggunakan desain asli) --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-foreground">Daftar Siswa di Kelas Ini</h2>
                <p class="text-sm text-secondary mt-1">Gunakan pencarian untuk merampingkan data.</p>
            </div>

            <div class="flex items-center gap-2" id="group-students-filter-form"
                x-data="{ gender: '{{ $filterGender }}', search: '{{ $search }}' }">
                <select name="filter_gender" x-ref="genderSelect" x-model="gender"
                    hx-get="{{ route('admin.students.group.show', $classGroup->id) }}"
                    hx-trigger="change"
                    hx-target="#students-container"
                    hx-select="#students-container"
                    hx-include="[name='search']"
                    hx-push-url="true"
                    class="h-11 shrink-0 min-w-[9.5rem] bg-white border border-border rounded-xl pl-3 pr-8 text-sm focus:outline-none focus:border-primary">
                    <option value="">Semua Gender</option>
                    <option value="L" @selected($filterGender==='L' )>Laki-Laki</option>
                    <option value="P" @selected($filterGender==='P' )>Perempuan</option>
                </select>

                <div class="relative shrink-0">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                    <input
                        type="text"
                        name="search"
                        x-ref="searchInput"
                        x-model="search"
                        value="{{ $search }}"
                        placeholder="Cari nama atau NIS..."
                        hx-get="{{ route('admin.students.group.show', $classGroup->id) }}"
                        hx-trigger="keyup changed delay:400ms, search, refreshClassData from:body"
                        hx-target="#students-container"
                        hx-select="#students-container"
                        hx-include="[name='filter_gender']"
                        hx-push-url="true"
                        class="h-11 w-56 md:w-64 bg-white border border-border rounded-xl pl-10 pr-4 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                <button type="button" x-show="gender || search" x-cloak
                    @click="
                        gender = '';
                        search = '';
                        $refs.genderSelect.value = '';
                        $refs.searchInput.value = '';
                        htmx.trigger($refs.searchInput, 'search');
                    "
                    title="Reset filter & pencarian"
                    class="flex items-center justify-center size-9 rounded-xl border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors shrink-0">
                    <i data-lucide="x" class="size-4 pointer-events-none"></i>
                </button>
            </div>
        </div>

        {{-- MEMANGGIL KOMPONEN TABEL DARI DATA SISWA UTAMA --}}
        @include('pages.admin.students.groups.partials._students-table', ['students' => $students])

    </div>
</div>
@endsection