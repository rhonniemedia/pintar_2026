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

// Opsi alasan untuk Dikeluarkan (Dismissed) - Diperbaiki: Berisi daftar pelanggaran
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

// Opsi alasan untuk Mengundurkan Diri (Resigned) - Diperbaiki: Berisi alasan personal/ekonomi
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

<div id="modal-container"
    x-data="{ open: false }"
    x-init="setTimeout(() => open = true, 50)"
    @close-modal.window="open = false; setTimeout(() => $el.outerHTML = '<div id=\'modal-container\'></div>', 300)">

    <x-ui.modal show="open" maxWidth="2xl">
        <div class="bg-white rounded-2xl overflow-hidden shadow-xl flex flex-col max-h-[90vh]">

            {{-- Header Modal --}}
            <div class="px-6 py-5 border-b border-border flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-error/10 flex items-center justify-center text-error">
                        <i data-lucide="log-out" class="size-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-foreground text-lg">Proses Mutasi Keluar</h3>
                        <p class="text-xs text-secondary">Keluarkan atau pindahkan peserta didik dari sekolah</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close-modal')" class="text-secondary hover:text-error transition-colors cursor-pointer">
                    <i data-lucide="x" class="size-5"></i>
                </button>
            </div>

            {{-- Form HTMX --}}
            <form id="form-mutasi-keluar"
                hx-post="{{ route('admin.students.transfer.out.store') }}"
                hx-target="#modal-container"
                hx-swap="outerHTML"
                class="flex flex-col overflow-hidden"
                x-data="{ mutationType: '', saving: false }"
                @htmx:before-request="saving = true"
                @htmx:after-request="saving = false">
                @csrf

                <div class="p-6 overflow-y-auto space-y-5 [scrollbar-gutter:stable]">

                    {{-- Alert Peringatan --}}
                    <div class="flex gap-3 items-start bg-warning/10 border border-warning/30 rounded-xl px-4 py-3.5">
                        <i data-lucide="alert-circle" class="text-warning-dark size-5 mt-0.5 flex-shrink-0"></i>
                        <p class="text-sm font-medium text-warning-dark leading-relaxed">
                            Pastikan data sudah benar. Memproses mutasi akan mengubah status aktif siswa dan mengeluarkannya dari rombongan belajar saat ini.
                        </p>
                    </div>

                    {{-- Pilih Siswa --}}
                    <div>
                        <label class="block text-xs font-bold text-secondary uppercase tracking-wider mb-1.5">Peserta Didik Aktif <span class="text-error">*</span></label>
                        <x-ui.searchable-select
                            name="student_id"
                            :options="$studentOptions"
                            placeholder="— Cari dan Pilih Peserta Didik —" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Jenis Mutasi --}}
                        <div>
                            <label class="block text-xs font-bold text-secondary uppercase tracking-wider mb-1.5">Jenis Status <span class="text-error">*</span></label>
                            <div @change="mutationType = $event.target.value">
                                <x-ui.select
                                    name="status"
                                    :options="$statusOptions"
                                    placeholder="— Pilih Jenis —" />
                            </div>
                        </div>

                        {{-- Tanggal Mutasi --}}
                        <div>
                            <label class="block text-xs font-bold text-secondary uppercase tracking-wider mb-1.5">Tanggal Mutasi <span class="text-error">*</span></label>
                            <input type="date" name="mutation_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm focus:border-primary focus:outline-none" required>
                        </div>
                    </div>

                    {{-- Form Dinamis: Pindah Sekolah (Transfer Out) --}}
                    <div x-show="mutationType === 'transfer_out'" x-cloak x-transition class="space-y-4 bg-blue-50/50 border border-blue-100 rounded-xl p-4 mt-2">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="school" class="size-4 text-blue-500"></i>
                            <span class="text-sm font-bold text-blue-800">Detail Pindah Sekolah</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-1">Nomor Surat <span class="text-error">*</span></label>
                            <input type="text" name="reference_number_pindah" placeholder="Masukkan nomor surat pindah..." class="w-full bg-white border border-blue-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" :required="mutationType === 'transfer_out'" :disabled="mutationType !== 'transfer_out'">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-1">Sekolah Tujuan <span class="text-error">*</span></label>
                            <input type="text" name="destination_school" placeholder="Masukkan nama sekolah tujuan..." class="w-full bg-white border border-blue-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" :required="mutationType === 'transfer_out'" :disabled="mutationType !== 'transfer_out'">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-blue-700 uppercase tracking-wider mb-1">Alasan Pindah</label>
                            <input type="text" name="notes_pindah" placeholder="Contoh: Mengikuti orang tua pindah tugas..." class="w-full bg-white border border-blue-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" :disabled="mutationType !== 'transfer_out'">
                        </div>
                    </div>

                    {{-- Form Dinamis: Dikeluarkan (Dismissed) --}}
                    <div x-show="mutationType === 'dismissed'" x-cloak x-transition class="space-y-4 bg-red-50/20 border border-red-100 rounded-xl p-4 mt-2">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="user-x" class="size-4 text-red-600"></i>
                            <span class="text-sm font-bold text-red-800">Detail Dikeluarkan</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-red-700 uppercase tracking-wider mb-1">Nomor Surat <span class="text-error">*</span></label>
                            <input type="text" name="reference_number_dikeluarkan" placeholder="Masukkan nomor surat keputusan..." class="w-full bg-white border border-red-200 rounded-lg px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none" :required="mutationType === 'dismissed'" :disabled="mutationType !== 'dismissed'">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-red-700 uppercase tracking-wider mb-1">Alasan atau Catatan <span class="text-error">*</span></label>
                            <x-ui.select
                                name="notes_dikeluarkan"
                                :options="$dismissedReasons"
                                placeholder="— Pilih Alasan —" />
                        </div>
                    </div>

                    {{-- Form Dinamis: Mengundurkan Diri (Resigned) --}}
                    <div x-show="mutationType === 'resigned'" x-cloak x-transition class="space-y-4 bg-orange-50/20 border border-orange-100 rounded-xl p-4 mt-2">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="user-minus" class="size-4 text-orange-600"></i>
                            <span class="text-sm font-bold text-orange-800">Detail Mengundurkan Diri</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-orange-700 uppercase tracking-wider mb-1">Nomor Surat <span class="text-error">*</span></label>
                            <input type="text" name="reference_number_mundur" placeholder="Masukkan nomor surat pengunduran diri..." class="w-full bg-white border border-orange-200 rounded-lg px-3 py-2.5 text-sm focus:border-orange-500 focus:outline-none" :required="mutationType === 'resigned'" :disabled="mutationType !== 'resigned'">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-orange-700 uppercase tracking-wider mb-1">Alasan atau Catatan <span class="text-error">*</span></label>
                            <x-ui.select
                                name="notes_mundur"
                                :options="$resignedReasons"
                                placeholder="— Pilih Alasan —" />
                        </div>
                    </div>

                </div>

                {{-- Footer Modal --}}
                <div class="p-5 border-t border-border bg-slate-50/50 flex items-center justify-end gap-3 shrink-0 rounded-b-2xl">
                    <button type="button" @click="$dispatch('close-modal')" class="px-5 py-2.5 text-sm font-semibold text-secondary bg-white border border-border rounded-xl hover:bg-muted transition-colors cursor-pointer">Batal</button>
                    <button type="submit" :disabled="saving || mutationType === ''" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-focus rounded-xl transition-all disabled:opacity-50 shadow-sm cursor-pointer shadow-error/20">
                        <i data-lucide="save" x-show="!saving" class="size-4"></i>
                        <i data-lucide="loader-2" x-show="saving" x-cloak class="size-4 animate-spin"></i>
                        <span x-text="saving ? 'Memproses...' : 'Proses Mutasi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>