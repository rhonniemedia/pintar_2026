<div
    x-data="{
        open: false,
        gradeError: false, // State untuk melacak error validasi tingkat
        close() { this.open = false },
        submitForm(e) {
            // Cek apakah tingkat sudah dipilih
            if (!$refs.grade.value) {
                e.preventDefault(); // Hentikan proses submit
                this.gradeError = true; // Tampilkan pesan error
            } else {
                this.gradeError = false;
                // Jika valid, biarkan submit berjalan dan tutup modal sesaat kemudian
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
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">Cetak Daftar Hadir</h3>
                <p class="text-xs text-secondary mt-0.5">Pilih rombel untuk mencetak dokumen PDF</p>
            </div>
            <button @click="close()" type="button" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form Body --}}
        {{-- Tambahkan novalidate agar tooltip bawaan browser tidak muncul menimpa error kustom kita --}}
        <form id="print-attendance-form" action="{{ route('admin.students.attendance.print') }}" method="GET" target="_blank"
            @submit="submitForm" novalidate
            class="flex flex-col flex-1 overflow-hidden">

            <div class="flex-1 overflow-y-auto p-6 space-y-4">

                {{-- Filter Tingkat (WAJIB) --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">
                        Tingkat <span class="text-red-500">*</span>
                    </label>
                    <select x-ref="grade" name="filter_grade"
                        @change="gradeError = false" {{-- Sembunyikan error saat user mulai memilih --}}
                        class="w-full bg-white border rounded-xl px-3 py-2.5 text-sm focus:outline-none transition-all"
                        :class="gradeError ? 'border-red-500 focus:border-red-500 focus:ring-1 focus:ring-red-500' : 'border-border focus:border-primary focus:ring-1 focus:ring-primary'"
                        hx-get="{{ route('admin.students.attendance.classes') }}"
                        hx-target="#class_group_id"
                        hx-indicator="#loading-indicator"
                        hx-include="#print-attendance-form">
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10">Kelas 10</option>
                        <option value="11">Kelas 11</option>
                        <option value="12">Kelas 12</option>
                    </select>
                    {{-- Pesan Error Kustom --}}
                    <p x-show="gradeError" x-cloak class="text-xs text-red-500 mt-1">
                        Tingkat harus dipilih
                    </p>
                </div>

                {{-- Filter Konsentrasi (TIDAK WAJIB) --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Konsentrasi Keahlian</label>
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

                {{-- Pilihan Kelas (TIDAK WAJIB) --}}
                <div>
                    <label class="flex items-center gap-2 text-sm font-bold text-foreground mb-2">
                        Kelas (Rombel)
                    </label>
                    <select id="class_group_id" name="class_group_id"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all focus:ring-1 focus:ring-primary">
                        <option value="">-- Semua Kelas --</option>
                        <!-- Opsi kelas akan dimuat secara dinamis oleh HTMX ke sini -->
                    </select>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end gap-3 shrink-0">

                <button type="button" @click="close()"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-white text-secondary hover:bg-muted font-medium text-sm transition-colors cursor-pointer">
                    <i data-lucide="x" class="size-4"></i> Batal
                </button>

                {{-- Hapus aksi @click dari sini, pindahkan logika ke @submit form --}}
                <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 shadow-md text-white font-bold text-sm rounded-xl transition-all cursor-pointer">
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