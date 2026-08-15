<div
    x-data="{
        open: false,
        gradeError: false,
        close() { this.open = false },
        submitForm(e) {
            if (!$refs.grade.value) {
                e.preventDefault();
                this.gradeError = true;
            } else {
                this.gradeError = false;
                setTimeout(() => this.close(), 100);
            }
        }
    }"
    x-init="
        setTimeout(() => open = true, 10);
        $watch('open', (value) => { if (!value) setTimeout(() => $el.remove(), 210) });
    "
    @keydown.escape.window="close()">

    <x-ui.modal show="open" maxWidth="md">

        {{-- Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="printer" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Cetak Daftar Hadir</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Pilih rombel untuk mencetak dokumen PDF</p>
                </div>
            </div>

            <button @click="close()" type="button" class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form Body --}}
        <form id="print-attendance-form" action="{{ route('admin.students.attendance.print') }}" method="GET" target="_blank"
            @submit="submitForm" novalidate
            class="flex flex-col flex-1 overflow-hidden">

            <div class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 space-y-4">

                {{-- Filter Tingkat --}}
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">
                        Tingkat <span class="text-error font-bold text-[12px]">*</span>
                    </label>
                    <select x-ref="grade" name="filter_grade"
                        @change="gradeError = false"
                        class="w-full bg-white border rounded-xl px-3 py-2.5 text-sm focus:outline-none transition-all"
                        :class="gradeError ? 'border-error focus:border-error focus:ring-1 focus:ring-error' : 'border-border focus:border-primary focus:ring-1 focus:ring-primary'"
                        hx-get="{{ route('admin.students.attendance.classes') }}"
                        hx-target="#class_group_id"
                        hx-indicator="#loading-indicator"
                        hx-include="#print-attendance-form">
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10">Kelas 10</option>
                        <option value="11">Kelas 11</option>
                        <option value="12">Kelas 12</option>
                    </select>
                    <p x-show="gradeError" x-cloak class="text-[10px] font-medium text-error mt-1.5">
                        Tingkat harus dipilih
                    </p>
                </div>

                {{-- Filter Konsentrasi --}}
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Konsentrasi Keahlian</label>
                    <select name="filter_concentration"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all focus:ring-1 focus:ring-primary"
                        hx-get="{{ route('admin.students.attendance.classes') }}"
                        hx-target="#class_group_id"
                        hx-indicator="#loading-indicator"
                        hx-include="#print-attendance-form">
                        <option value="">-- Semua Konsentrasi --</option>
                        @foreach($concentrationOptions as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilihan Kelas --}}
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">
                        Kelas (Rombel)
                    </label>
                    <select id="class_group_id" name="class_group_id"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all focus:ring-1 focus:ring-primary">
                        <option value="">-- Semua Kelas --</option>
                        <!-- Opsi kelas dimuat dinamis oleh HTMX -->
                    </select>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-2 shrink-0">

                <button type="button" @click="close()"
                    class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                    Batal
                </button>

                <button type="submit"
                    class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer">
                    <i data-lucide="printer" class="size-4 mr-2"></i>
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