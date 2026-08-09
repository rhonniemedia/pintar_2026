<div id="modal-container"
    x-data="{ 
        open: false,
        isSubmitting: false,
        closeModal() {
            this.open = false;
            setTimeout(() => {
                document.getElementById('modal-container').outerHTML = '<div id=\'modal-container\'></div>';
            }, 300);
        }
    }"
    x-init="setTimeout(() => open = true, 50)"
    @close-modal.window="closeModal()">

    <x-ui.modal show="open" maxWidth="md">
        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-sm border border-amber-200">
                    <i data-lucide="arrow-right-left" class="size-5 sm:size-6"></i>
                </div>

                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Cetak Laporan Mutasi</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Filter periode dan unduh laporan PDF</p>
                </div>
            </div>

            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0"
                :disabled="isSubmitting">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        <form action="{{ route('admin.students.reports.mutation') }}" method="GET" target="_blank" @submit="setTimeout(() => closeModal(), 200)">
            {{-- Body Modal --}}
            <div class="p-4 sm:p-6 overflow-y-auto max-h-[60vh] bg-slate-50/30 flex-1 custom-scrollbar space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-foreground">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="w-full rounded-xl border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5 text-foreground">Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" class="w-full rounded-xl border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5 text-foreground">Tanggal Validasi (Surat)</label>
                    <input type="date" name="tgl_validasi" class="w-full rounded-xl border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" value="{{ now()->format('Y-m-d') }}" required>
                </div>

            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-3 shrink-0">
                <button type="button" @click="closeModal()"
                    class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer"
                    :disabled="isSubmitting">
                    Batal
                </button>

                <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-70 disabled:cursor-not-allowed text-white rounded-xl font-semibold text-sm transition-all shadow-sm shadow-amber-600/30 cursor-pointer">
                    <i data-lucide="printer" class="size-4"></i>
                    <span>Cetak PDF</span>
                </button>
            </div>
        </form>
    </x-ui.modal>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>