<div
    x-data="{
        open: false,
        close() { this.open = false }
    }"
    x-init="
        setTimeout(() => open = true, 10);
        $watch('open', (value) => { if (!value) setTimeout(() => $el.remove(), 210) });
    "
    @keydown.escape.window="close()">

    <x-ui.modal show="open" maxWidth="md">

        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-10 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="printer" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Cetak Biodata Peserta Didik</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">{{ $student->name }}</p>
                </div>
            </div>

            <button @click="close()" type="button" class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form Body --}}
        <form action="{{ route('admin.students.data.print.biodata', $student->id) }}" method="GET" target="_blank"
            @submit="setTimeout(() => close(), 100)"
            class="flex flex-col flex-1 overflow-hidden">

            <div class="block p-4 sm:p-6 overflow-visible bg-white flex-1 space-y-4">

                {{-- Info Dokumen --}}
                <div class="bg-blue-50/50 border border-blue-100 text-blue-700 text-xs sm:text-sm rounded-xl px-4 py-3 flex items-start gap-3">
                    <i data-lucide="info" class="size-4 sm:size-5 shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">Dokumen ini berisi rekap lengkap biodata peserta didik: identitas, alamat, riwayat sekolah asal, kesehatan/minat, serta data orang tua/wali.</span>
                </div>

                {{-- Pilih Penanda Tangan (Wali) --}}
                <div>
                    <label class="block text-[11px] sm:text-xs font-bold text-secondary uppercase tracking-wider mb-2">
                        Penanda Tangan (Orang Tua/Wali) <span class="text-error">*</span>
                    </label>

                    @if($student->guardians->isEmpty())
                    <div class="text-sm text-secondary italic bg-slate-50 border border-border rounded-xl px-3.5 py-2.5">
                        Belum ada data orang tua/wali untuk siswa ini.
                    </div>
                    @else
                    <select name="guardian_id" required
                        class="w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors">
                        <option value="">Pilih Penanda Tangan</option>
                        @foreach($student->guardians as $guardian)
                        <option value="{{ $guardian->id }}">{{ $guardian->relationship->label() }} - {{ $guardian->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>

                {{-- Input Tanggal Cetak --}}
                <div>
                    <label class="block text-[11px] sm:text-xs font-bold text-secondary uppercase tracking-wider mb-2">
                        Tanggal Penulisan Dokumen <span class="text-error">*</span>
                    </label>
                    <input type="date" name="print_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors" required>
                </div>

            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 shrink-0 sm:rounded-b-2xl">

                <button type="button" @click="close()"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer text-center">
                    Batal
                </button>

                <button type="submit"
                    class="w-full sm:w-auto flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer">
                    <i data-lucide="printer" class="size-4 mr-2 pointer-events-none"></i>
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