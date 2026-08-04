<!-- ── Chart + Distribusi Gender ── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    {{-- Chart Tren Mutasi --}}
    <div class="flex flex-col h-full rounded-2xl border border-border p-6 gap-4 bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
            <div>
                <h3 class="font-bold text-lg text-foreground">Tren Mutasi Siswa</h3>
                <p class="text-sm text-secondary">6 bulan terakhir — masuk vs keluar</p>
            </div>

            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-secondary">
                    <span class="size-2.5 bg-[#10B981] inline-block"></span>
                    Masuk
                </span>
                <span class="flex items-center gap-1.5 text-secondary">
                    <span class="size-2.5 bg-[#EF4444] inline-block"></span>
                    Keluar
                </span>
            </div>
        </div>

        <div class="w-full relative flex-1 min-h-[250px] mt-2">
            <canvas id="mutationTrendChart"></canvas>
        </div>
    </div>

    {{-- Distribusi Gender --}}
    <div class="flex flex-col h-full rounded-2xl border border-border p-6 gap-4 bg-white">
        <div class="shrink-0">
            <h3 class="font-bold text-lg text-foreground">Distribusi Siswa</h3>
            <p class="text-sm text-secondary">Perbandingan Laki-laki & Perempuan</p>
        </div>

        <div class="flex-1 flex justify-center items-center w-full relative min-h-[200px] mt-2">
            <div class="relative size-[200px]">
                <canvas id="donutChart"></canvas>
            </div>
        </div>

        <div class="flex items-center justify-center gap-6 text-sm font-semibold mt-2">
            @foreach ($genderChartData as $gender)
            <div class="flex items-center gap-2 text-secondary">
                @php $dotStyle = ['background-color: ' . $gender['color']]; @endphp
                <span class="size-3 inline-block shrink-0" @style($dotStyle)></span>
                <span>{{ $gender['label'] }}</span>
                <span class="text-foreground ml-0.5">{{ number_format($gender['count']) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>