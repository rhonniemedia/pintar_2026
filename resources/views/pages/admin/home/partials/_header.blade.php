<!-- ── Header ── -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-foreground text-2xl md:text-3xl font-bold mb-1">Dashboard Akademik</h1>
        <p class="text-secondary text-sm">
            Selamat datang kembali, <span class="font-semibold text-foreground">{{ auth()->user()->name ?? 'Admin' }}</span>
            — Tahun Ajaran {{ $data->academic_year->name }}, Semester {{$data->semester->label }}
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
                    <a href="#" class="group flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-foreground rounded-xl hover:bg-muted transition-colors">
                        <i data-lucide="file-spreadsheet" class="size-4 text-secondary group-hover:text-primary transition-colors"></i>
                        <span>Rekap Siswa</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>