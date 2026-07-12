<div class="flex flex-col gap-1">
    @forelse($activities as $activity)
    @php $icon = $activity->icon_config; @endphp
    <div class="flex gap-3 group">
        <div class="relative flex flex-col items-center">
            <div class="size-8 rounded-full {{ $icon['bg'] }} flex items-center justify-center shrink-0 z-10 ring-1 ring-border group-hover:ring-primary/30 transition-colors">
                <i data-lucide="{{ $icon['icon'] }}" class="size-3.5 {{ $icon['text'] }}"></i>
            </div>
            @if(!$loop->last)
            <div class="w-px flex-1 bg-border mt-1"></div>
            @endif
        </div>

        <div class="flex flex-col pb-4 pt-0.5 min-w-0">
            <p class="text-sm text-foreground leading-snug">
                <span class="font-bold">{{ $activity->user->name ?? 'Sistem' }}</span>
                <span class="text-secondary"> {{ $activity->description }}</span>
            </p>
            @if($activity->context)
            <p class="text-xs text-secondary/80 mt-0.5 truncate">{{ $activity->context }}</p>
            @endif
            <div class="flex items-center gap-1.5 mt-1">
                <i data-lucide="clock" class="size-3 text-secondary/50 shrink-0"></i>
                <span class="text-xs text-secondary/60 font-medium">{{ $activity->time_ago }}</span>
            </div>
        </div>
    </div>
    @empty
    <div class="py-8 flex flex-col items-center justify-center text-center gap-3">
        <div class="size-12 rounded-full bg-slate-50 flex items-center justify-center">
            <i data-lucide="inbox" class="size-5 text-secondary/50"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-foreground">Belum ada aktivitas</p>
            <p class="text-xs text-secondary mt-0.5">Aktivitas admin dan sistem akan muncul di sini.</p>
        </div>
    </div>
    @endforelse
</div>
<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>