@extends('layouts.main.admin')

@section('title', 'Dashboard Akademik')
@section('page_title', 'Akademik')
@section('page_subtitle', 'Dashboard Kesiswaan')

@section('content')

<?php
// ==========================================================================
// PUSAT KONTROL DATA DUMMY (DATASET SWITCHER)
// Ubah nilai $activeDataset di bawah ini untuk mengganti skenario data.
// Opsi yang tersedia: 'ganjil' (Fokus PPDB/Siswa Baru) atau 'genap' (Fokus Ujian/Kelulusan)
// ==========================================================================
$activeDataset = 'ganjil';

$twColors = [
    'cyan' => '#06B6D4',
    'emerald' => '#10B981',
    'blue' => '#3B82F6',
    'amber' => '#F59E0B',
    'yellow' => '#EAB308',
    'indigo' => '#6366F1',
    'orange' => '#F97316',
    'rose' => '#F43F5E',
    'red' => '#EF4444',
    'sky' => '#0EA5E9',
    'purple' => '#8B5CF6',
    'teal' => '#14B8A6',
];

$datasets = [];

// --------------------------------------------------------------------------
// SKENARIO A: GANJIL (Awal Tahun Ajaran, Lonjakan Siswa Baru / PPDB)
// --------------------------------------------------------------------------
$datasets['ganjil'] = [
    'academic_year' => (object) ['name' => '2026/2027', 'status' => 'active'],
    'semester' => (object) ['type' => 'odd', 'code' => '2026-1', 'label' => 'Ganjil'],
    'stats' => (object) [
        'total_active_students' => 318,
        'total_class_groups' => 5,
        'mutations_this_month' => 14, // Tinggi karena PPDB
        'active_extracurriculars' => 8,
        'growth_students' => 12.5, // Lonjakan positif
    ],
    'concentrations' => collect([
        (object) ['id' => 1, 'name' => 'Rekayasa Perangkat Lunak', 'alias' => 'RPL', 'color' => 'blue', 'quota' => 108, 'student_count' => 96],
        (object) ['id' => 2, 'name' => 'Teknik Komputer Jaringan', 'alias' => 'TKJ', 'color' => 'emerald', 'quota' => 108, 'student_count' => 101],
        (object) ['id' => 3, 'name' => 'Desain Komunikasi Visual', 'alias' => 'DKV', 'color' => 'purple', 'quota' => 72, 'student_count' => 58],
        (object) ['id' => 4, 'name' => 'Akuntansi', 'alias' => 'AK', 'color' => 'amber', 'quota' => 72, 'student_count' => 63],
    ]),
    'class_groups' => collect([
        (object) ['id' => 1, 'name' => 'X RPL 1', 'capacity' => 36, 'filled' => 36, 'homeroom_teacher' => 'Siti Aminah, S.Kom'],
        (object) ['id' => 2, 'name' => 'X RPL 2', 'capacity' => 36, 'filled' => 34, 'homeroom_teacher' => 'Budi Santoso, S.Kom'],
        (object) ['id' => 3, 'name' => 'XI TKJ 1', 'capacity' => 36, 'filled' => 28, 'homeroom_teacher' => 'Rina Wulandari, S.T'],
        (object) ['id' => 4, 'name' => 'XII DKV 1', 'capacity' => 36, 'filled' => 29, 'homeroom_teacher' => 'Andi Prakoso, S.Sn'],
        (object) ['id' => 5, 'name' => 'XII AK 1', 'capacity' => 36, 'filled' => 31, 'homeroom_teacher' => 'Dewi Lestari, S.Pd'],
    ]),
    'mutations' => collect([
        (object) ['student_name' => 'Sistem', 'description' => 'membuat 5 rombel baru untuk siswa kelas X', 'context' => 'Penerimaan Siswa Baru', 'time_ago' => '1 jam lalu', 'icon_config' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'icon' => 'plus-circle']],
        (object) ['student_name' => 'Muhammad Rizky', 'description' => 'pindah masuk dari SMPN 4 Palembang', 'context' => 'Mutasi Masuk', 'time_ago' => '2 jam lalu', 'icon_config' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'icon' => 'log-in']],
        (object) ['student_name' => 'Nadia Ramadhani', 'description' => 'mendaftar ekstrakurikuler Robotik', 'context' => 'Kegiatan Siswa', 'time_ago' => '4 jam lalu', 'icon_config' => ['bg' => 'bg-info/10', 'text' => 'text-info', 'icon' => 'cpu']],
        (object) ['student_name' => 'Sistem', 'description' => 'mengirim jadwal pelajaran ke wali kelas', 'context' => 'Otomatisasi', 'time_ago' => 'Kemarin', 'icon_config' => ['bg' => 'bg-warning/10', 'text' => 'text-warning-dark', 'icon' => 'mail']],
    ]),
    'students' => collect([
        (object) ['name' => 'Muhammad Rizky', 'nis' => '2627001', 'class_group_name' => 'X RPL 2', 'concentration_alias' => 'RPL', 'concentration_color' => '#3B82F6', 'status' => 'active'],
        (object) ['name' => 'Nadia Ramadhani', 'nis' => '2627002', 'class_group_name' => 'X TKJ 1', 'concentration_alias' => 'TKJ', 'concentration_color' => '#10B981', 'status' => 'active'],
        (object) ['name' => 'Rangga Saputra', 'nis' => '2627003', 'class_group_name' => 'X DKV 1', 'concentration_alias' => 'DKV', 'concentration_color' => '#8B5CF6', 'status' => 'active'],
        (object) ['name' => 'Citra Lestari', 'nis' => '2627004', 'class_group_name' => 'X AK 1', 'concentration_alias' => 'AK', 'concentration_color' => '#F59E0B', 'status' => 'active'],
    ]),
    'mutation_trend' => [
        'labels' => ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'],
        'masuk' => [3, 5, 2, 4, 45, 62], // Lonjakan di Jul/Agu (PPDB)
        'keluar' => [1, 2, 1, 3, 4, 2],
    ]
];

// --------------------------------------------------------------------------
// SKENARIO B: GENAP (Menjelang Kelulusan, Ujian Sekolah, Mutasi Keluar)
// --------------------------------------------------------------------------
$datasets['genap'] = [
    'academic_year' => (object) ['name' => '2026/2027', 'status' => 'active'],
    'semester' => (object) ['type' => 'even', 'code' => '2026-2', 'label' => 'Genap'],
    'stats' => (object) [
        'total_active_students' => 310,
        'total_class_groups' => 5,
        'mutations_this_month' => 2,
        'active_extracurriculars' => 14,
        'growth_students' => -1.2, // Tren sedikit menurun
    ],
    'concentrations' => collect([
        (object) ['id' => 1, 'name' => 'Rekayasa Perangkat Lunak', 'alias' => 'RPL', 'color' => 'blue', 'quota' => 108, 'student_count' => 94],
        (object) ['id' => 2, 'name' => 'Teknik Komputer Jaringan', 'alias' => 'TKJ', 'color' => 'emerald', 'quota' => 108, 'student_count' => 98],
        (object) ['id' => 3, 'name' => 'Desain Komunikasi Visual', 'alias' => 'DKV', 'color' => 'purple', 'quota' => 72, 'student_count' => 57],
        (object) ['id' => 4, 'name' => 'Akuntansi', 'alias' => 'AK', 'color' => 'amber', 'quota' => 72, 'student_count' => 61],
    ]),
    'class_groups' => collect([
        (object) ['id' => 1, 'name' => 'X RPL 1', 'capacity' => 36, 'filled' => 35, 'homeroom_teacher' => 'Siti Aminah, S.Kom'],
        (object) ['id' => 2, 'name' => 'X RPL 2', 'capacity' => 36, 'filled' => 34, 'homeroom_teacher' => 'Budi Santoso, S.Kom'],
        (object) ['id' => 3, 'name' => 'XI TKJ 1', 'capacity' => 36, 'filled' => 27, 'homeroom_teacher' => 'Rina Wulandari, S.T'],
        (object) ['id' => 4, 'name' => 'XII DKV 1', 'capacity' => 36, 'filled' => 36, 'homeroom_teacher' => 'Andi Prakoso, S.Sn'],
        (object) ['id' => 5, 'name' => 'XII AK 1', 'capacity' => 36, 'filled' => 36, 'homeroom_teacher' => 'Dewi Lestari, S.Pd'],
    ]),
    'mutations' => collect([
        (object) ['student_name' => 'Ayu Kartika', 'description' => 'melakukan pendaftaran SNBP (Perguruan Tinggi)', 'context' => 'Kelulusan Kelas XII', 'time_ago' => '30 menit lalu', 'icon_config' => ['bg' => 'bg-info/10', 'text' => 'text-info', 'icon' => 'graduation-cap']],
        (object) ['student_name' => 'Bagas Nugroho', 'description' => 'mengajukan surat pindah keluar (ikut ortu)', 'context' => 'Mutasi Keluar', 'time_ago' => '3 jam lalu', 'icon_config' => ['bg' => 'bg-error/10', 'text' => 'text-error', 'icon' => 'log-out']],
        (object) ['student_name' => 'Sistem', 'description' => 'mencetak kartu ujian akhir sekolah kelas XII', 'context' => 'Ujian Sekolah', 'time_ago' => '5 jam lalu', 'icon_config' => ['bg' => 'bg-primary/10', 'text' => 'text-primary', 'icon' => 'printer']],
        (object) ['student_name' => 'Tim Pramuka', 'description' => 'juara 1 Lomba Tingkat Cabang', 'context' => 'Prestasi Ekskul', 'time_ago' => '2 hari lalu', 'icon_config' => ['bg' => 'bg-success/10', 'text' => 'text-success', 'icon' => 'trophy']],
    ]),
    'students' => collect([
        (object) ['name' => 'Ayu Kartika', 'nis' => '2425045', 'class_group_name' => 'XII AK 1', 'concentration_alias' => 'AK', 'concentration_color' => '#F59E0B', 'status' => 'graduated'],
        (object) ['name' => 'Dimas Anggara', 'nis' => '2425088', 'class_group_name' => 'XII DKV 1', 'concentration_alias' => 'DKV', 'concentration_color' => '#8B5CF6', 'status' => 'graduated'],
        (object) ['name' => 'Bagas Nugroho', 'nis' => '2425087', 'class_group_name' => '-', 'concentration_alias' => 'TKJ', 'concentration_color' => '#10B981', 'status' => 'transferred_out'],
        (object) ['name' => 'Sinta Dewi', 'nis' => '2526012', 'class_group_name' => 'XI RPL 1', 'concentration_alias' => 'RPL', 'concentration_color' => '#3B82F6', 'status' => 'active'],
    ]),
    'mutation_trend' => [
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        'masuk' => [1, 0, 2, 0, 1, 0],
        'keluar' => [2, 3, 1, 4, 12, 25], // Lonjakan keluar di Mei/Jun (Lulus)
    ]
];

// --------------------------------------------------------------------------
// PEMROSESAN DATA FINAL (Pre-kalkulasi UI)
// --------------------------------------------------------------------------
$data = (object) $datasets[$activeDataset];

$data->concentrations = $data->concentrations->map(function ($c) use ($twColors) {
    $raw = $c->color ?? '';
    if (str_starts_with($raw, '#')) $c->dot_color = $raw;
    elseif (isset($twColors[$raw])) $c->dot_color = $twColors[$raw];
    else $c->dot_color = '#6B7280';
    return $c;
});

$data->class_groups = $data->class_groups->map(function ($group) {
    $percent = $group->capacity > 0 ? round(($group->filled / $group->capacity) * 100) : 0;
    $group->percent = $percent;
    $group->ratio_badge = $percent >= 100 ? 'bg-error/10 text-error-dark'
        : ($percent >= 90 ? 'bg-warning/10 text-warning-dark' : 'bg-success/10 text-success-dark');
    $group->bar_color = $percent >= 100 ? '#EF4444' : ($percent >= 90 ? '#F59E0B' : '#10B981');
    return $group;
});

$data->students = $data->students->map(function ($s) {
    $s->status_classes = match ($s->status) {
        'active' => 'bg-success/10 text-success-dark',
        'graduated' => 'bg-info/10 text-info-dark',
        'transferred_out' => 'bg-warning/10 text-warning-dark',
        'dropped_out' => 'bg-error/10 text-error-dark',
        default => 'bg-secondary/10 text-secondary-dark',
    };
    $s->status_label = match ($s->status) {
        'active' => 'Aktif',
        'graduated' => 'Lulus',
        'transferred_out' => 'Pindah Keluar',
        'dropped_out' => 'Dropout',
        default => ucfirst($s->status),
    };
    return $s;
});

$chartData = $data->concentrations->map(function ($c) {
    return ['label' => $c->alias ?? $c->name, 'count' => $c->student_count, 'color' => $c->dot_color];
})->values();

$mutationTrendData = $data->mutation_trend;
?>

<div class="flex-1 overflow-y-auto p-5 md:p-8 bg-white" x-data="akademikDashboardApp()">

    <!-- ── Header ── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-foreground text-2xl md:text-3xl font-bold mb-1">Dashboard Akademik</h1>
            <p class="text-secondary text-sm">
                Selamat datang kembali, <span class="font-semibold text-foreground">{{ auth()->user()->name ?? 'Admin' }}</span>
                — Tahun Ajaran {{ $data->academic_year->name }}, Semester {{ $data->semester->label }}
                <span class="inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $data->stats->growth_students >= 0 ? 'bg-success/10 text-success-dark' : 'bg-error/10 text-error-dark' }} align-middle">
                    <i data-lucide="{{ $data->stats->growth_students >= 0 ? 'trending-up' : 'trending-down' }}" class="size-2.5"></i>
                    {{ $data->semester->label }} (Aktif)
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 px-4 py-3 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-sm transition-all duration-300 cursor-pointer bg-white">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                <span>Ekspor</span>
            </button>
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="flex items-center gap-2 px-4 py-3 bg-primary text-white rounded-full font-bold text-sm hover:bg-primary-hover transition-all duration-300 cursor-pointer">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Laporan</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-transition class="absolute right-0 mt-2 w-60 bg-white border border-border rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] z-50 p-2" style="display: none;">
                    <div class="px-3 pt-2 pb-1">
                        <span class="text-xs font-bold text-secondary uppercase tracking-wider">Cetak Data</span>
                    </div>
                    <div class="h-px bg-border my-2"></div>
                    <div class="flex flex-col gap-1">
                        <a href="#" @click.prevent="open = false; $dispatch('open-modal-rekap-siswa')" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-xl hover:bg-muted transition-colors">
                            <i data-lucide="file-spreadsheet" class="size-4 text-secondary group-hover:text-primary transition-colors"></i>
                            <span>Rekap Siswa</span>
                        </a>
                        <a href="#" target="_blank" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-xl hover:bg-muted transition-colors">
                            <i data-lucide="layout-grid" class="size-4 text-secondary group-hover:text-primary transition-colors"></i>
                            <span>Rekap Rombel</span>
                        </a>
                        <a href="#" target="_blank" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-xl hover:bg-muted transition-colors">
                            <i data-lucide="shuffle" class="size-4 text-secondary group-hover:text-primary transition-colors"></i>
                            <span>Rekap Mutasi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Stat Cards ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="size-11 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="size-5 text-primary"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full {{ $data->stats->growth_students >= 0 ? 'bg-success/10 text-success-dark' : 'bg-error/10 text-error-dark' }}">
                    <i data-lucide="{{ $data->stats->growth_students >= 0 ? 'trending-up' : 'trending-down' }}" class="size-3"></i>{{ abs($data->stats->growth_students) }}%
                </span>
            </div>
            <div>
                <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->total_active_students) }}</p>
                <p class="text-sm text-secondary mt-0.5">Total Siswa Aktif</p>
                <p class="text-xs text-secondary mt-1.5 flex items-center gap-1">
                    <i data-lucide="calendar" class="size-3"></i>Tahun Ajaran {{ $data->academic_year->name }}
                </p>
            </div>
        </div>

        <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="size-11 bg-info/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="layout-grid" class="size-5 text-info"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-blue-50 text-blue-700">
                    <i data-lucide="school" class="size-3"></i>Rombel
                </span>
            </div>
            <div>
                <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->total_class_groups) }}</p>
                <p class="text-sm text-secondary mt-0.5">Rombongan Belajar</p>
                <p class="text-xs text-secondary mt-1.5 flex items-center gap-1">
                    <i data-lucide="check" class="size-3"></i>Tersebar di 3 tingkat
                </p>
            </div>
        </div>

        <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="size-11 bg-warning/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="shuffle" class="size-5 text-warning-dark"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-warning/10 text-warning-dark">
                    {{ $data->stats->mutations_this_month }}
                </span>
            </div>
            <div>
                <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->mutations_this_month) }}</p>
                <p class="text-sm text-secondary mt-0.5">Mutasi Bulan Ini</p>
                <p class="text-xs text-secondary mt-1.5 flex items-center gap-1">
                    <i data-lucide="calendar-check" class="size-3"></i>Masuk & keluar gabungan
                </p>
            </div>
        </div>

        <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
            <div class="flex items-center justify-between">
                <div class="size-11 bg-success/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="trophy" class="size-5 text-success"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-success/10 text-success-dark">
                    Aktif
                </span>
            </div>
            <div>
                <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->active_extracurriculars) }}</p>
                <p class="text-sm text-secondary mt-0.5">Ekstrakurikuler</p>
                <p class="text-xs text-secondary mt-1.5 flex items-center gap-1">
                    <i data-lucide="user-check" class="size-3"></i>Semua punya pembina
                </p>
            </div>
        </div>
    </div>

    <!-- ── Chart + Distribusi Jurusan ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <div class="lg:col-span-2 flex flex-col h-full rounded-2xl border border-border p-6 gap-4 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                <div>
                    <h3 class="font-bold text-lg text-foreground">Tren Mutasi Siswa</h3>
                    <p class="text-sm text-secondary">6 bulan terakhir — masuk vs keluar</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="flex items-center gap-1.5 text-secondary"><span class="size-2.5 rounded-full bg-[#FF1443] inline-block"></span>Masuk</span>
                    <span class="flex items-center gap-1.5 text-secondary"><span class="size-2.5 rounded-full bg-[#080C1A]/30 inline-block"></span>Keluar</span>
                </div>
            </div>
            <div class="w-full relative flex-1 min-h-[250px] mt-2">
                <canvas id="mutationTrendChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-1 flex flex-col rounded-2xl border border-border p-6 gap-4 bg-white">
            <div>
                <h3 class="font-bold text-lg text-foreground">Distribusi Siswa</h3>
                <p class="text-sm text-secondary">Siswa aktif per jurusan</p>
            </div>
            <div class="flex justify-center">
                <div style="width:160px;height:160px;position:relative">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
            <div class="flex flex-col gap-1.5 mt-1">
                @foreach($data->concentrations as $concentration)
                <div class="flex items-center justify-between text-sm py-0.5">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $concentration->dot_color }}"></div>
                        <span class="{{ $concentration->student_count > 0 ? 'text-foreground font-medium' : 'text-secondary' }}">
                            {{ $concentration->alias ?? $concentration->name }}
                        </span>
                    </div>
                    <span class="{{ $concentration->student_count > 0 ? 'font-bold text-foreground' : 'font-medium text-secondary' }}">
                        {{ number_format($concentration->student_count) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ── Kapasitas Rombel + Aktivitas ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="flex flex-col rounded-2xl border border-border p-6 gap-5 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg text-foreground">Kapasitas Rombel</h3>
                    <p class="text-sm text-secondary">Jumlah siswa vs daya tampung kelas</p>
                </div>
                <a href="#" class="size-9 rounded-xl border border-border flex items-center justify-center text-secondary hover:border-primary hover:text-primary transition-colors cursor-pointer">
                    <i data-lucide="arrow-right" class="size-4"></i>
                </a>
            </div>
            <div class="flex flex-col divide-y divide-border">
                @forelse($data->class_groups as $group)
                <div class="py-3 first:pt-0 last:pb-0">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-foreground">{{ $group->name }}</span>
                        <span class="text-xs text-secondary">
                            {{ number_format($group->filled) }} / {{ number_format($group->capacity) }} siswa
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $group->ratio_badge }} ml-1">
                                {{ $group->percent }}%
                            </span>
                        </span>
                    </div>
                    <div class="h-1.5 rounded-full bg-border overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" style="width:{{ min($group->percent, 100) }}%;background:{{ $group->bar_color }}"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-secondary text-center py-4">Belum ada rombel dibuat</p>
                @endforelse
            </div>
        </div>

        <div class="flex flex-col rounded-2xl border border-border p-6 gap-4 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg text-foreground">Aktivitas Terbaru</h3>
                    <p class="text-sm text-secondary">Mutasi & perubahan data kesiswaan</p>
                </div>
                <span class="text-[10px] font-bold text-secondary/60 uppercase tracking-wider flex items-center gap-1">
                    <i data-lucide="refresh-cw" class="size-3"></i>Auto-refresh
                </span>
            </div>
            <div id="activity-feed-akademik" class="flex flex-col gap-3">
                @foreach($data->mutations as $mut)
                <div class="flex items-start gap-3 py-2 border-b border-border/50 last:border-0">
                    <div class="size-8 rounded-full {{ $mut->icon_config['bg'] }} {{ $mut->icon_config['text'] }} flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="{{ $mut->icon_config['icon'] }}" class="size-4"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-foreground">
                            <span class="font-bold">{{ $mut->student_name }}</span> {{ $mut->description }}
                        </p>
                        @if($mut->context)
                        <p class="text-[10px] text-secondary mt-0.5">{{ $mut->context }}</p>
                        @endif
                        <span class="text-[10px] text-secondary/70">{{ $mut->time_ago }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ── Tabel Siswa ── -->
    <div class="flex flex-col rounded-2xl border border-border p-6 bg-white gap-6">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-lg text-foreground">Siswa Terbaru</h3>
                <p class="text-sm text-secondary">Perubahan data siswa terakhir</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form action="#" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto" onsubmit="event.preventDefault();">
                    <div class="relative w-full sm:w-auto">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                        <input type="text" name="search" placeholder="Cari siswa / NIS..." class="pl-9 pr-9 py-2 h-10 rounded-xl border border-border bg-white text-sm focus:ring-1 focus:ring-primary outline-none w-full sm:w-[220px] transition-all" />
                    </div>
                    <a href="#" class="flex items-center justify-center gap-1.5 px-5 h-10 bg-primary text-white rounded-xl sm:rounded-full font-bold text-xs hover:bg-primary-hover transition-all cursor-pointer w-full sm:w-auto">
                        <i data-lucide="users" class="size-3.5"></i>Kelola Siswa
                    </a>
                </form>
            </div>
        </div>

        <div id="student-table-wrapper" class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border text-secondary text-xs uppercase tracking-wider">
                        <th class="py-3 px-4 font-bold">Nama Siswa</th>
                        <th class="py-3 px-4 font-bold">NIS</th>
                        <th class="py-3 px-4 font-bold">Rombel</th>
                        <th class="py-3 px-4 font-bold">Jurusan</th>
                        <th class="py-3 px-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($data->students as $s)
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-foreground">{{ $s->name }}</td>
                        <td class="py-3 px-4 text-secondary">{{ $s->nis }}</td>
                        <td class="py-3 px-4 text-secondary">{{ $s->class_group_name }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold text-white" style="background-color: {{ $s->concentration_color }}">
                                {{ $s->concentration_alias }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $s->status_classes }}">
                                {{ $s->status_label }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function akademikDashboardApp() {
        return {};
    }

    const concentrationData = @json($chartData);
    const mutationTrendData = @json($mutationTrendData);

    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) lucide.createIcons();

        const donutCtx = document.getElementById('donutChart');
        if (donutCtx && concentrationData.length) {
            const donutData = concentrationData.map(d => d.count > 0 ? d.count : 0.0001);
            new Chart(donutCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: concentrationData.map(d => d.label),
                    datasets: [{
                        data: donutData,
                        backgroundColor: concentrationData.map(d => d.color),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '72%'
                }
            });
        }

        const trendCtx = document.getElementById('mutationTrendChart');
        if (trendCtx) {
            new Chart(trendCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: mutationTrendData.labels,
                    datasets: [{
                            label: 'Masuk',
                            data: mutationTrendData.masuk,
                            backgroundColor: 'rgba(255,20,67,0.75)',
                            borderRadius: 8,
                            barThickness: 14
                        },
                        {
                            label: 'Keluar',
                            data: mutationTrendData.keluar,
                            backgroundColor: 'rgba(8,12,26,0.2)',
                            borderRadius: 8,
                            barThickness: 14
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection