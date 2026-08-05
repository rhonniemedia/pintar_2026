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

        <form id="dummy-sync-form" class="space-y-4">
            {{-- Pilihan Gelombang / Tahun Ajaran --}}
            <div class="bg-white border border-border rounded-xl px-4 py-4">
                <label for="academic_year" class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Tahun Ajaran / Gelombang</label>
                <select id="academic_year" name="academic_year" class="w-full h-11 rounded-lg border border-border bg-white px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors">
                    <option value="">-- Pilih Periode SPMB --</option>
                    <option value="2024-g1">Tahun Ajaran 2024/2025 - Gelombang 1</option>
                    <option value="2024-g2">Tahun Ajaran 2024/2025 - Gelombang 2</option>
                </select>
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl flex gap-3">
                <i data-lucide="info" class="size-5 text-blue-600 shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-blue-800">Informasi Sinkronisasi</p>
                    <p class="text-sm text-blue-700 mt-1 leading-relaxed">
                        Proses ini akan menarik biodata dasar, jalur masuk, dan data orang tua. Data yang sudah sinkron sebelumnya tidak akan mengalami duplikasi.
                    </p>
                </div>
            </div>
        </form>

    </div>

    {{-- Footer Modal --}}
    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex flex-col sm:flex-row items-center justify-end gap-2 sm:gap-3 shrink-0">
        <button type="button" @click="syncModalOpen = false"
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
            Batal
        </button>
        <button type="button"
            class="w-full sm:w-auto flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 shadow-sm transition-all cursor-pointer">
            <i data-lucide="download-cloud" class="size-4"></i>
            <span>Mulai Sinkronisasi</span>
        </button>
    </div>
</x-ui.modal>