{{-- ========================================== --}}
{{-- MODAL CETAK REKAPITULASI (Taruh di area bawah Blade) --}}
{{-- ========================================== --}}
<div x-data="{ 
        showModal: false, 
        opsiCetak: 'semua',
        tanggal: '{{ date('Y-m-d') }}'
    }"
    @open-modal-rekap.window="showModal = true"
    x-show="showModal"
    style="display: none;"
    class="relative z-[99]"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">

    {{-- Background Overlay --}}
    <div x-show="showModal"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 bg-black/40 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

            {{-- Modal Panel --}}
            <div x-show="showModal"
                @click.outside="showModal = false"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-border">

                {{-- Form Cetak --}}
                <form action="{{ route('admin.laporan.rekapitulasi') }}" method="GET" target="_blank" @submit="showModal = false">
                    <div class="bg-white px-6 pb-6 pt-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-bold leading-6 text-foreground" id="modal-title">Cetak Rekapitulasi</h3>
                            <button type="button" @click="showModal = false" class="text-secondary hover:text-foreground transition-colors cursor-pointer">
                                <i data-lucide="x" class="size-5"></i>
                            </button>
                        </div>

                        <div class="space-y-5">
                            {{-- Pilihan Opsi --}}
                            <div>
                                <label class="block text-sm font-semibold text-foreground mb-3">Pilih Rentang Data</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex flex-col items-start gap-2 p-3 border border-border rounded-xl cursor-pointer transition-colors" :class="opsiCetak === 'semua' ? 'bg-primary/5 border-primary/30 ring-1 ring-primary/30' : 'hover:bg-muted'">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" x-model="opsiCetak" name="opsi" value="semua" class="w-4 h-4 text-primary bg-white border-border focus:ring-primary focus:ring-2">
                                            <span class="text-sm font-semibold text-foreground">Semua Data</span>
                                        </div>
                                        <span class="text-xs text-secondary pl-6">Hingga hari ini</span>
                                    </label>

                                    <label class="flex flex-col items-start gap-2 p-3 border border-border rounded-xl cursor-pointer transition-colors" :class="opsiCetak === 'harian' ? 'bg-primary/5 border-primary/30 ring-1 ring-primary/30' : 'hover:bg-muted'">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" x-model="opsiCetak" name="opsi" value="harian" class="w-4 h-4 text-primary bg-white border-border focus:ring-primary focus:ring-2">
                                            <span class="text-sm font-semibold text-foreground">Harian</span>
                                        </div>
                                        <span class="text-xs text-secondary pl-6">Pilih tanggal spesifik</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Input Tanggal (Muncul jika Harian) --}}
                            <div x-show="opsiCetak === 'harian'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                style="display: none;">
                                <label class="block text-sm font-semibold text-foreground mb-1.5">Tanggal Pendaftaran</label>
                                <input type="date" name="tanggal" x-model="tanggal" class="w-full px-3 py-2 border border-border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-foreground bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Footer Action --}}
                    <div class="bg-muted/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-border">
                        <button type="submit" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary-hover transition-colors cursor-pointer">
                            <i data-lucide="printer" class="size-4"></i>
                            Cetak Laporan
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 inline-flex w-full sm:w-auto justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-foreground shadow-sm ring-1 ring-inset ring-border hover:bg-muted transition-colors cursor-pointer sm:mt-0">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>