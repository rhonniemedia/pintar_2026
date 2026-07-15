<div id="history-stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] hover:-translate-y-1 hover:shadow-lg transition-all cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-secondary opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-secondary/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="history" class="size-5 text-secondary"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Total Riwayat</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($totalHistoryStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] hover:-translate-y-1 hover:shadow-lg transition-all cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-teal-500 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-teal-500/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="log-in" class="size-5 text-teal-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Masuk (Pindahan)</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($transferInStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] hover:-translate-y-1 hover:shadow-lg transition-all cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-blue-500 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-blue-500/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="log-out" class="size-5 text-blue-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Keluar (Pindah)</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($transferOutStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] hover:-translate-y-1 hover:shadow-lg transition-all cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-error opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-error/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="user-x" class="size-5 text-error"></i>
                </div>
                <p class="font-medium text-xs text-secondary">DO / Meninggal</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format(($droppedOutStats ?? 0) + ($deceasedStats ?? 0), 0, ',', '.') }}</p>
            </div>
        </div>

    </div>
</div>