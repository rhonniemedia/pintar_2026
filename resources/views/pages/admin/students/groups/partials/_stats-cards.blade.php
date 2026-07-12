<div id="stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Card 1: Total Rombel --}}
        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-green-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-success opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-success/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="layout-grid" class="size-5 text-success"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Total Rombel</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($totalStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Card 2: Rombel Kelas XII --}}
        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-purple-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-purple-400 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="book-open" class="size-5 text-primary"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Rombel Kelas XII</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($grade12Stats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Card 3: Rombel Kelas XI --}}
        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-blue-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-blue-400 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-blue-500/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="book-open" class="size-5 text-blue-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Rombel Kelas XI</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($grade11Stats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Card 4: Rombel Kelas X --}}
        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-black/5 hover:border-orange-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-orange-400 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-orange-500/10 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="book-open" class="size-5 text-orange-500"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Rombel Kelas X</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($grade10Stats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

    </div>
</div>