<div id="stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all hover:-translate-y-1 hover:shadow-lg hover:border-blue-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-blue-400 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="size-5 text-blue-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Total Pengguna</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all hover:-translate-y-1 hover:shadow-lg hover:border-purple-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-purple-500 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-purple-50 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="shield" class="size-5 text-purple-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Admin & Superadmin</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($adminStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-emerald-500 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="file-check" class="size-5 text-emerald-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Tim Verifikator</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($verifikatorStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden flex flex-col rounded-2xl border border-border p-5 gap-4 bg-white min-h-[130px] transition-all hover:-translate-y-1 hover:shadow-lg hover:border-amber-200 cursor-default">
            <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-amber-500 opacity-[0.07]"></div>
            <div class="flex items-center gap-2">
                <div class="size-10 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="clipboard-list" class="size-5 text-amber-600"></i>
                </div>
                <p class="font-medium text-xs text-secondary">Tim Observator</p>
            </div>
            <div class="border-t border-dashed border-border pt-3">
                <p class="font-bold text-3xl">{{ number_format($observatorStats ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

    </div>
</div>