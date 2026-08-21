<!-- ── Header ── -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-foreground text-2xl md:text-3xl font-bold mb-1">Dashboard Akademik</h1>
        <p class="text-secondary text-sm leading-relaxed">
            Selamat datang kembali, <span class="font-semibold text-foreground">{{ auth()->user()->name ?? 'Admin' }}</span>
            — Tahun Ajaran {{ $data->academic_year->name }}, Semester {{$data->semester->label }}

            {{-- Margin top ditambahkan pada badge khusus di mobile agar sejajar saat turun baris --}}
            <span class="inline-flex items-center gap-1 mt-1.5 sm:mt-0 sm:ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $data->stats->growth_students >= 0 ? 'bg-success/10 text-success-dark' : 'bg-error/10 text-error-dark' }} align-middle">
                <i data-lucide="{{ $data->stats->growth_students >= 0 ? 'trending-up' : 'trending-down' }}" class="size-2.5 shrink-0"></i>
                {{ $data->semester->label }} (Aktif)
            </span>
        </p>
    </div>

    {{-- Grup Tombol diubah menggunakan Grid di layar mobile --}}
    <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-3 w-full md:w-auto mt-2 md:mt-0">

        <!-- Tombol Ekspor -->
        <button class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2.5 sm:py-3 ring-1 ring-border hover:ring-primary rounded-full text-foreground font-semibold text-xs sm:text-sm transition-all duration-300 cursor-pointer bg-white w-full sm:w-auto">
            <i data-lucide="download-cloud" class="w-4 h-4 shrink-0"></i>
            <span class="whitespace-nowrap">Ekspor</span>
        </button>

        <!-- Tombol & Dropdown Cetak Laporan -->
        <div class="relative w-full sm:w-auto text-left" x-data="{ open: false }" @click.outside="open = false">
            <button type="button"
                @click="open = !open"
                class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2.5 sm:py-3 bg-primary text-white rounded-full font-bold text-xs sm:text-sm hover:bg-primary-hover transition-all duration-300 cursor-pointer w-full sm:w-auto">
                <i data-lucide="printer" class="w-4 h-4 shrink-0"></i>

                {{-- Teks disingkat di mobile --}}
                <span class="whitespace-nowrap hidden sm:inline">Cetak Laporan</span>
                <span class="whitespace-nowrap sm:hidden">Cetak</span>

                <i data-lucide="chevron-down"
                    class="w-4 h-4 transition-transform duration-300 shrink-0"
                    :class="open ? 'rotate-180' : ''"></i>
            </button>

            <!-- Dropdown Content -->
            <div x-show="open"
                x-cloak
                x-transition
                class="absolute right-0 mt-2 w-[220px] sm:w-60 bg-white border border-border rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] z-50 p-2 origin-top-right">

                <div class="px-3 pt-2 pb-1">
                    <span class="text-xs font-bold text-secondary uppercase tracking-wider">
                        Cetak Laporan
                    </span>
                </div>

                <div class="h-px bg-border my-2"></div>

                <div class="flex flex-col gap-1">
                    <!-- Tombol Laporan Rekapitulasi Jurusan -->
                    <a href="javascript:void(0)"
                        hx-get="{{ route('admin.students.reports.concentration.modal') }}"
                        hx-target="#modal-container"
                        @click="open = false"
                        class="group flex items-center gap-3 px-3 py-2.5 text-sm text-foreground rounded-xl hover:bg-muted transition-colors">
                        <i data-lucide="book-open"
                            class="size-4 text-secondary group-hover:text-primary transition-colors shrink-0"></i>
                        <span>Data Jurusan</span>
                    </a>

                    <!-- Tombol Laporan Keadaan Peserta Didik -->
                    <a href="javascript:void(0)"
                        hx-get="{{ route('admin.students.reports.student-count.modal') }}"
                        hx-target="#modal-container"
                        @click="open = false"
                        class="group flex items-center gap-3 px-3 py-2.5 text-sm text-foreground rounded-xl hover:bg-muted transition-colors">
                        <i data-lucide="users"
                            class="size-4 text-secondary group-hover:text-primary transition-colors shrink-0"></i>
                        <span>Data Peserta Didik</span>
                    </a>

                    <!-- Tombol Laporan Mutasi -->
                    <a href="javascript:void(0)"
                        hx-get="{{ route('admin.students.reports.mutation.modal') }}"
                        hx-target="#modal-container"
                        @click="open = false"
                        class="group flex items-center gap-3 px-3 py-2.5 text-sm text-foreground rounded-xl hover:bg-muted transition-colors">
                        <i data-lucide="arrow-right-left"
                            class="size-4 text-secondary group-hover:text-primary transition-colors shrink-0"></i>
                        <span>Data Mutasi</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>