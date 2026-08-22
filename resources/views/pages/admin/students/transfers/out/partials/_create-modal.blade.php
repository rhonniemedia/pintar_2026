@php
// Siapkan data opsi untuk searchable select Peserta Didik
$studentOptions = [];
foreach($students as $st) {
$rombel = $st->activeClassGroup->first()?->name ?? 'Tanpa Rombel';
$nis = $st->nis ?? '-';
$studentOptions[] = [
'value' => $st->id,
'label' => $st->name . " (NIS: {$nis}) — {$rombel}"
];
}

// Mengambil Jenis Status langsung dari Enum kecuali transfer_in dan graduated
$statusOptions = [];
foreach (\App\Enums\Student\MutationStatus::cases() as $case) {
if (!in_array($case->value, ['transfer_in', 'graduated'])) {
$statusOptions[] = [
'value' => $case->value,
'label' => $case->label()
];
}
}

// Opsi alasan untuk Dikeluarkan (Dismissed)
$dismissedReasons = [
['value' => 'Pelanggaran berat tata tertib sekolah', 'label' => 'Pelanggaran berat tata tertib sekolah'],
['value' => 'Pelanggaran disiplin berulang setelah pembinaan', 'label' => 'Pelanggaran disiplin berulang setelah pembinaan'],
['value' => 'Pelanggaran terhadap ketentuan sekolah', 'label' => 'Pelanggaran terhadap ketentuan sekolah'],
['value' => 'Tindakan kekerasan/perundungan yang berat', 'label' => 'Tindakan kekerasan/perundungan yang berat'],
['value' => 'Penyalahgunaan atau peredaran zat terlarang', 'label' => 'Penyalahgunaan atau peredaran zat terlarang'],
['value' => 'Tindakan yang membahayakan warga sekolah', 'label' => 'Tindakan yang membahayakan warga sekolah'],
['value' => 'Tindakan yang merugikan atau mencemarkan nama baik sekolah', 'label' => 'Tindakan yang merugikan atau mencemarkan nama baik sekolah'],
['value' => 'Pemalsuan atau manipulasi dokumen/data', 'label' => 'Pemalsuan atau manipulasi dokumen/data'],
['value' => 'Pelanggaran berat lainnya', 'label' => 'Pelanggaran berat lainnya'],
];

// Opsi alasan untuk Mengundurkan Diri (Resigned)
$resignedReasons = [
['value' => 'Permintaan orang tua/wali', 'label' => 'Permintaan orang tua/wali'],
['value' => 'Kendala ekonomi', 'label' => 'Kendala ekonomi'],
['value' => 'Pindah domisili', 'label' => 'Pindah domisili'],
['value' => 'Pertimbangan kesehatan', 'label' => 'Pertimbangan kesehatan'],
['value' => 'Memilih untuk bekerja', 'label' => 'Memilih untuk bekerja'],
['value' => 'Alasan pribadi', 'label' => 'Alasan pribadi'],
['value' => 'Alasan lainnya', 'label' => 'Alasan lainnya'],
];
@endphp

{{-- KONTANER UTAMA: Menggantikan <x-ui.modal> agar bisa h-full di mobile --}}
<div id="modal-container"
    x-data="{ 
        open: false,
        closeModal() {
            this.open = false;
            setTimeout(() => {
                const container = document.getElementById('modal-container');
                if (container) container.innerHTML = '';
            }, 200);
        }
    }"
    x-init="setTimeout(() => open = true, 50)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="closeModal()"
    @close-modal.window="closeModal()">

    {{-- KOTAK MODAL: h-full di HP, max-h-[90vh] di PC --}}
    <div class="bg-white sm:rounded-2xl w-full sm:max-w-2xl h-full sm:h-auto sm:max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">

        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-error/10 text-error flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="log-out" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Proses Mutasi Keluar</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Keluarkan atau pindahkan peserta didik dari sekolah.</p>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form HTMX: flex-1 dan min-h-0 agar mengisi sisa ruang --}}
        <form id="form-mutasi-keluar"
            hx-post="{{ route('admin.students.transfer.out.store') }}"
            hx-target="#modal-container"
            hx-swap="outerHTML"
            class="flex flex-col flex-1 min-h-0"
            x-data="{ mutationType: '', saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="saving = false">
            @csrf

            {{-- Body Modal: flex-1 agar mendesak footer ke bawah --}}
            <div class="block p-4 sm:p-6 overflow-y-auto flex-1 space-y-4 sm:space-y-5 [scrollbar-gutter:stable]">

                {{-- Alert Peringatan --}}
                <div class="flex gap-2.5 sm:gap-3 items-start bg-warning/10 border border-warning/30 rounded-xl px-3.5 sm:px-4 py-3 sm:py-3.5">
                    <i data-lucide="alert-circle" class="text-warning-dark size-4 sm:size-5 mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs sm:text-sm font-medium text-warning-dark leading-relaxed">
                        Pastikan data sudah benar. Memproses mutasi akan mengubah status aktif siswa dan mengeluarkannya dari rombongan belajar saat ini.
                    </p>
                </div>

                {{-- Pilih Siswa --}}
                <div>
                    <label class="block text-[10px] sm:text-xs font-bold text-secondary uppercase tracking-wider mb-1.5">Peserta Didik Aktif <span class="text-error">*</span></label>
                    <x-ui.searchable-select
                        name="student_id"
                        :options="$studentOptions"
                        placeholder="— Cari dan Pilih Peserta Didik —" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Jenis Mutasi --}}
                    <div>
                        <label class="block text-[10px] sm:text-xs font-bold text-secondary uppercase tracking-wider mb-1.5">Jenis Status <span class="text-error">*</span></label>
                        <div @change="mutationType = $event.target.value">
                            <x-ui.select
                                name="status"
                                :options="$statusOptions"
                                placeholder="— Pilih Jenis —" />
                        </div>
                    </div>

                    {{-- Tanggal Mutasi --}}
                    <div>
                        <label class="block text-[10px] sm:text-xs font-bold text-secondary uppercase tracking-wider mb-1.5">Tanggal Mutasi <span class="text-error">*</span></label>
                        <input type="date" name="mutation_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm focus:border-primary focus:outline-none" required>
                    </div>
                </div>

                {{-- Form Dinamis: Pindah Sekolah (Transfer Out) --}}
                <div x-show="mutationType === 'transfer_out'" x-cloak x-transition class="space-y-3 sm:space-y-4 bg-blue-50/50 border border-blue-100 rounded-xl p-3.5 sm:p-4 mt-2">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="school" class="size-4 text-blue-500"></i>
                        <span class="text-xs sm:text-sm font-bold text-blue-800">Detail Pindah Sekolah</span>
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-1">Nomor Surat <span class="text-error">*</span></label>
                        <input type="text" name="reference_number_pindah" placeholder="Masukkan nomor surat pindah..." class="w-full bg-white border border-blue-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" :required="mutationType === 'transfer_out'" :disabled="mutationType !== 'transfer_out'">
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-1">Sekolah Tujuan <span class="text-error">*</span></label>
                        <input type="text" name="destination_school" placeholder="Masukkan nama sekolah tujuan..." class="w-full bg-white border border-blue-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" :required="mutationType === 'transfer_out'" :disabled="mutationType !== 'transfer_out'">
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-1">Alasan Pindah</label>
                        <input type="text" name="notes_pindah" placeholder="Contoh: Mengikuti orang tua pindah tugas..." class="w-full bg-white border border-blue-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" :disabled="mutationType !== 'transfer_out'">
                    </div>
                </div>

                {{-- Form Dinamis: Dikeluarkan (Dismissed) --}}
                <div x-show="mutationType === 'dismissed'" x-cloak x-transition class="space-y-3 sm:space-y-4 bg-red-50/20 border border-red-100 rounded-xl p-3.5 sm:p-4 mt-2">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="user-x" class="size-4 text-red-600"></i>
                        <span class="text-xs sm:text-sm font-bold text-red-800">Detail Dikeluarkan</span>
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-red-700 uppercase tracking-wider mb-1">Nomor Surat <span class="text-error">*</span></label>
                        <input type="text" name="reference_number_dikeluarkan" placeholder="Masukkan nomor surat keputusan..." class="w-full bg-white border border-red-200 rounded-lg px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none" :required="mutationType === 'dismissed'" :disabled="mutationType !== 'dismissed'">
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-red-700 uppercase tracking-wider mb-1">Alasan atau Catatan <span class="text-error">*</span></label>
                        <x-ui.select
                            name="notes_dikeluarkan"
                            :options="$dismissedReasons"
                            placeholder="— Pilih Alasan —" />
                    </div>
                </div>

                {{-- Form Dinamis: Mengundurkan Diri (Resigned) --}}
                <div x-show="mutationType === 'resigned'" x-cloak x-transition class="space-y-3 sm:space-y-4 bg-orange-50/20 border border-orange-100 rounded-xl p-3.5 sm:p-4 mt-2">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="user-minus" class="size-4 text-orange-600"></i>
                        <span class="text-xs sm:text-sm font-bold text-orange-800">Detail Mengundurkan Diri</span>
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-orange-700 uppercase tracking-wider mb-1">Nomor Surat <span class="text-error">*</span></label>
                        <input type="text" name="reference_number_mundur" placeholder="Masukkan nomor surat pengunduran diri..." class="w-full bg-white border border-orange-200 rounded-lg px-3 py-2.5 text-sm focus:border-orange-500 focus:outline-none" :required="mutationType === 'resigned'" :disabled="mutationType !== 'resigned'">
                    </div>
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-bold text-orange-700 uppercase tracking-wider mb-1">Alasan atau Catatan <span class="text-error">*</span></label>
                        <x-ui.select
                            name="notes_mundur"
                            :options="$resignedReasons"
                            placeholder="— Pilih Alasan —" />
                    </div>
                </div>

            </div>

            {{-- Footer Modal: shrink-0 agar tidak terdorong dan selalu stay di bawah --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 shrink-0">
                <button type="button" @click="closeModal()" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-secondary bg-white border border-border rounded-xl hover:bg-muted transition-colors cursor-pointer text-center">Batal</button>
                <button type="submit" :disabled="saving || mutationType === ''" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-focus rounded-xl transition-all disabled:opacity-50 shadow-sm cursor-pointer shadow-error/20">
                    <i data-lucide="save" x-show="!saving" class="size-4"></i>
                    <i data-lucide="loader-2" x-show="saving" x-cloak class="size-4 animate-spin"></i>
                    <span x-text="saving ? 'Memproses...' : 'Proses Mutasi'"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>