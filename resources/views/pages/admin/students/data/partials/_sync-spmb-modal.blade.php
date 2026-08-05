{{-- resources/views/pages/admin/students/data/partials/_sync-spmb-modal.blade.php --}}

<x-ui.modal show="syncModalOpen" maxWidth="lg">
    {{-- Modal Header --}}
    <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            {{-- Icon Header --}}
            <div class="size-11 sm:size-12 rounded-full bg-amber-100 flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="calendar-sync" class="size-5 sm:size-6 text-amber-600"></i>
            </div>

            {{-- Title & Subtitle --}}
            <div class="min-w-0">
                <h3 class="font-bold text-foreground text-base sm:text-lg uppercase leading-tight truncate">Tarik Data dari SPMB</h3>
                <p class="text-xs sm:text-sm text-secondary mt-1">Tarik data peserta didik baru yang lolos seleksi.</p>
            </div>
        </div>

        {{-- Close Button --}}
        <button type="button" @click="syncModalOpen = false"
            class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
            <i data-lucide="x" class="size-4 pointer-events-none"></i>
        </button>
    </div>

    {{-- Modal Body --}}
    <div class="p-4 sm:p-6 overflow-y-auto max-h-[55vh] sm:max-h-[60vh] bg-slate-50/30 flex-1 [scrollbar-gutter:stable]">

        {{-- Info Box --}}
        <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl flex gap-3 mb-4">
            <i data-lucide="info" class="size-5 text-blue-600 shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-blue-800">Informasi Sinkronisasi</p>
                <p class="text-sm text-blue-700 mt-1 leading-relaxed">
                    Data di bawah ini ditarik secara *real-time* dari aplikasi SPMB. Proses sinkronisasi akan mengimpor peserta dan mengabaikan data yang sudah ada (mencegah duplikasi).
                </p>
            </div>
        </div>

        {{-- Container Target HTMX (Tanpa border/bg di wrapper utama) --}}
        <div id="spmb-info-container">
            {{-- Loading State (Border putus-putus diletakkan di sini agar hilang saat data load) --}}
            <div class="min-h-[120px] flex items-center justify-center border border-dashed border-border rounded-xl bg-white p-6">
                <div class="flex flex-col items-center justify-center text-secondary">
                    <i data-lucide="loader-2" class="size-6 animate-spin mb-2 text-primary"></i>
                    <span class="text-sm font-medium">Memeriksa ketersediaan data di SPMB...</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Footer Modal --}}
    <div x-data="{ isSyncingBtn: false, isDoneBtn: false }"
        @all-sync-finished.window="isSyncingBtn = false; isDoneBtn = true"
        class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex flex-col sm:flex-row items-center justify-end gap-2 sm:gap-3 shrink-0">

        <button type="button" x-show="!isDoneBtn" @click="syncModalOpen = false"
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted transition-all cursor-pointer">
            Batal
        </button>

        <button type="button"
            @click="
                if (isDoneBtn) {
                    // HANYA TUTUP MODAL, TANPA RELOAD HALAMAN
                    syncModalOpen = false;
                } else {
                    isSyncingBtn = true;
                    $dispatch('start-real-sync'); 
                }
            "
            :disabled="isSyncingBtn"
            :class="{
                'bg-amber-600 hover:bg-amber-700': !isDoneBtn && !isSyncingBtn,
                'bg-amber-600/70 cursor-not-allowed': isSyncingBtn,
                'bg-emerald-600 hover:bg-emerald-700': isDoneBtn
            }"
            class="w-full sm:w-auto flex justify-center items-center px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm transition-all cursor-pointer">

            <span x-show="!isSyncingBtn && !isDoneBtn" class="flex items-center gap-2">
                <i data-lucide="download-cloud" class="size-4"></i>
                <span>Mulai Sinkronisasi</span>
            </span>

            <span x-show="isSyncingBtn" x-cloak class="flex items-center gap-2">
                <i data-lucide="loader-2" class="size-4 animate-spin"></i>
                <span>Menyinkronkan...</span>
            </span>

            <span x-show="isDoneBtn" x-cloak class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="size-4"></i>
                <span>Tutup</span>
            </span>

        </button>
    </div>
</x-ui.modal>