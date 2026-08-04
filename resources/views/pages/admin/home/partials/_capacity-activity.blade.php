<!-- ── Kapasitas Rombel + Aktivitas ── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    {{-- Kapasitas Rombel --}}
    <div class="flex flex-col rounded-2xl border border-border p-6 gap-5 bg-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-lg text-foreground">Kapasitas Rombel</h3>
                <p class="text-sm text-secondary">Jumlah siswa vs daya tampung kelas</p>
            </div>

            <a href="#"
                class="size-9 rounded-xl border border-border flex items-center justify-center text-secondary hover:border-primary hover:text-primary transition-colors cursor-pointer">
                <i data-lucide="arrow-right" class="size-4"></i>
            </a>
        </div>

        <div class="flex flex-col divide-y divide-border">
            @forelse ($data->class_groups as $group)
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

                @php $barStyle = 'style="width:' . min($group->percent, 100) . '%;background:' . e($group->bar_color) . '"'; @endphp

                <div class="h-1.5 rounded-full bg-border overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500" {!! $barStyle !!}></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-secondary text-center py-4">Belum ada rombel dibuat</p>
            @endforelse
        </div>
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="flex flex-col rounded-2xl border border-border p-6 gap-4 bg-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-lg text-foreground">Aktivitas Terbaru</h3>
                <p class="text-sm text-secondary">Mutasi & perubahan data kesiswaan</p>
            </div>

            <span class="text-[10px] font-bold text-secondary/60 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="refresh-cw" class="size-3"></i>
                Auto-refresh
            </span>
        </div>

        <div id="activity-feed-akademik" class="flex flex-col gap-3">
            @foreach ($data->mutations as $mut)
            <div class="flex items-start gap-3 py-2 border-b border-border/50 last:border-0">
                <div class="size-8 rounded-full {{ $mut->icon_config['bg'] }} {{ $mut->icon_config['text'] }} flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="{{ $mut->icon_config['icon'] }}" class="size-4"></i>
                </div>

                <div class="flex-1">
                    <p class="text-xs text-foreground">
                        <span class="font-bold">{{ $mut->student_name }}</span> {{ $mut->description }}
                    </p>

                    @if ($mut->context)
                    <p class="text-[10px] text-secondary mt-0.5">{{ $mut->context }}</p>
                    @endif

                    <span class="text-[10px] text-secondary/70">{{ $mut->time_ago }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>