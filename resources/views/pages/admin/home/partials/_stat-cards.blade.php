<!-- ── Stat Cards ── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <!-- Card 1: Total Peserta Didik Aktif -->
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
            <p class="text-sm text-secondary mt-0.5">Total Peserta Didik Aktif</p>
            <p class="text-xs text-secondary mt-1.5 flex items-center gap-1">
                <i data-lucide="calendar" class="size-3"></i>Tahun Ajaran {{ $data->academic_year->name }}
            </p>
        </div>
    </div>

    <!-- Card 2: Kelas XII -->
    <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="size-11 bg-info/10 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="graduation-cap" class="size-5 text-info"></i>
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-blue-50 text-blue-700">
                Kelas XII
            </span>
        </div>
        <div>
            <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->grade_12->total) }}</p>
            <p class="text-sm text-secondary mt-0.5">Peserta Didik Kelas XII</p>
            <div class="flex items-center gap-2 sm:gap-3 mt-1.5">
                <span class="inline-flex items-center gap-1 text-[11px] text-secondary">
                    <svg class="size-3.5 text-blue-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="10" cy="14" r="5"></circle>
                        <line x1="13.5" y1="10.5" x2="20" y2="4"></line>
                        <polyline points="15 4 20 4 20 9"></polyline>
                    </svg>
                    <span class="font-medium text-blue-600 hidden sm:inline">Laki-laki:</span>
                    {{ $data->stats->grade_12->male }}
                </span>
                <span class="text-border text-[10px]">|</span>
                <span class="inline-flex items-center gap-1 text-[11px] text-secondary">
                    <svg class="size-3.5 text-pink-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="10" r="5"></circle>
                        <line x1="12" y1="15" x2="12" y2="22"></line>
                        <line x1="9" y1="19" x2="15" y2="19"></line>
                    </svg>
                    <span class="font-medium text-pink-600 hidden sm:inline">Perempuan:</span>
                    {{ $data->stats->grade_12->female }}
                </span>
            </div>
        </div>
    </div>

    <!-- Card 3: Kelas XI -->
    <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="size-11 bg-warning/10 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="book-open" class="size-5 text-warning-dark"></i>
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-warning/10 text-warning-dark">
                Kelas XI
            </span>
        </div>
        <div>
            <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->grade_11->total) }}</p>
            <p class="text-sm text-secondary mt-0.5">Peserta Didik Kelas XI</p>
            <div class="flex items-center gap-2 sm:gap-3 mt-1.5">
                <span class="inline-flex items-center gap-1 text-[11px] text-secondary">
                    <svg class="size-3.5 text-blue-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="10" cy="14" r="5"></circle>
                        <line x1="13.5" y1="10.5" x2="20" y2="4"></line>
                        <polyline points="15 4 20 4 20 9"></polyline>
                    </svg>
                    <span class="font-medium text-blue-600 hidden sm:inline">Laki-laki:</span>
                    {{ $data->stats->grade_11->male }}
                </span>
                <span class="text-border text-[10px]">|</span>
                <span class="inline-flex items-center gap-1 text-[11px] text-secondary">
                    <svg class="size-3.5 text-pink-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="10" r="5"></circle>
                        <line x1="12" y1="15" x2="12" y2="22"></line>
                        <line x1="9" y1="19" x2="15" y2="19"></line>
                    </svg>
                    <span class="font-medium text-pink-600 hidden sm:inline">Perempuan:</span>
                    {{ $data->stats->grade_11->female }}
                </span>
            </div>
        </div>
    </div>

    <!-- Card 4: Kelas X -->
    <div class="flex flex-col rounded-2xl border border-border p-5 gap-3 bg-white hover:shadow-md transition-all duration-300 cursor-pointer">
        <div class="flex items-center justify-between">
            <div class="size-11 bg-success/10 rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="user-plus" class="size-5 text-success"></i>
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full bg-success/10 text-success-dark">
                Kelas X
            </span>
        </div>
        <div>
            <p class="font-bold text-3xl text-foreground">{{ number_format($data->stats->grade_10->total) }}</p>
            <p class="text-sm text-secondary mt-0.5">Peserta Didik Kelas X</p>
            <div class="flex items-center gap-2 sm:gap-3 mt-1.5">
                <span class="inline-flex items-center gap-1 text-[11px] text-secondary">
                    <svg class="size-3.5 text-blue-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="10" cy="14" r="5"></circle>
                        <line x1="13.5" y1="10.5" x2="20" y2="4"></line>
                        <polyline points="15 4 20 4 20 9"></polyline>
                    </svg>
                    <span class="font-medium text-blue-600 hidden sm:inline">Laki-laki:</span>
                    {{ $data->stats->grade_10->male }}
                </span>
                <span class="text-border text-[10px]">|</span>
                <span class="inline-flex items-center gap-1 text-[11px] text-secondary">
                    <svg class="size-3.5 text-pink-500 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="10" r="5"></circle>
                        <line x1="12" y1="15" x2="12" y2="22"></line>
                        <line x1="9" y1="19" x2="15" y2="19"></line>
                    </svg>
                    <span class="font-medium text-pink-600 hidden sm:inline">Perempuan:</span>
                    {{ $data->stats->grade_10->female }}
                </span>
            </div>
        </div>
    </div>

</div>