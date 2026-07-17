@php
$isEdit = isset($classGroup);
$actionUrl = $isEdit ? route('admin.students.group.update', $classGroup->id) : route('admin.students.group.store');
$title = $isEdit ? 'Edit Data Rombel' : 'Tambah Data Rombel';
$subtitle = $isEdit ? 'Perbarui informasi rombongan belajar' : 'Tambahkan rombongan belajar baru ke sistem';
@endphp

{{--
    Wrapper tunggal x-data. `close()` hanya bertugas mengubah state `open`.
    Pembersihan DOM ditangani otomatis lewat $watch di bawah, sehingga
    berlaku untuk SEMUA jalur penutupan modal: tombol X, tombol Batal,
    selesai submit (htmx), MAUPUN klik backdrop / Escape yang sudah jadi
    fitur bawaan <x-ui.modal>. Tidak perlu lagi utak-atik DOM manual
    (closest/parentElement/innerHTML) di beberapa tempat.
--}}
<div
    x-data="{
        open: false,
        close() { this.open = false },
    }"
    x-init="
        setTimeout(() => open = true, 10);
        $watch('open', (value) => { if (!value) setTimeout(() => $el.remove(), 210) });
    "
    @keydown.escape.window="close()">
    <x-ui.modal show="open" maxWidth="lg">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">{{ $title }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ $subtitle }}</p>
            </div>
            <button @click="close()" type="button" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form Body --}}
        <form hx-{{ $isEdit ? 'put' : 'post' }}="{{ $actionUrl }}"
            x-data="{ saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="
                saving = false;
                close();

                if (!$event.detail.successful) {
                    let errorMsg = 'Pastikan semua kolom wajib telah diisi dengan benar.';

                    try {
                        const response = JSON.parse($event.detail.xhr.responseText);
                        if (response.errors) {
                            const firstKey = Object.keys(response.errors)[0];
                            errorMsg = response.errors[firstKey][0];
                        } else if (response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        console.error('Gagal parsing pesan error', e);
                    }

                    window.ShowAlert({
                        type: 'error',
                        title: $event.detail.xhr.status === 422 ? 'Validasi Gagal' : 'Terjadi Kesalahan',
                        message: $event.detail.xhr.status === 422 ? errorMsg : 'Gagal memproses permintaan.'
                    });
                }
            "
            class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Rombongan Belajar</label>
                    <input type="text" name="name" value="{{ $classGroup->name ?? '' }}" placeholder="Contoh: X DPIB 1"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Kelas</label>
                    <select name="grade_level" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="10" @selected(($classGroup->grade_level ?? '') == '10')>Kelas X</option>
                        <option value="11" @selected(($classGroup->grade_level ?? '') == '11')>Kelas XI</option>
                        <option value="12" @selected(($classGroup->grade_level ?? '') == '12')>Kelas XII</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Nomor Kelompok</label>
                    <input type="number" name="group_number" value="{{ $classGroup->group_number ?? '1' }}" min="1" required placeholder="Contoh: 1"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <p class="text-xs text-secondary mt-1.5">Angka urut pembeda kelas (misal: isi 1 untuk X DPIB 1, isi 2 untuk X DPIB 2).</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Jurusan</label>
                    <select name="concentration_id" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Pilih Jurusan</option>
                        @foreach($concentrationOptions as $id => $name)
                        <option value="{{ $id }}" @selected(($classGroup->concentration_id ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Wali Kelas</label>
                    <select name="homeroom_teacher_id" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Pilih Wali Kelas (Opsional)</option>
                        @foreach($teacherOptions as $id => $name)
                        <option value="{{ $id }}" @selected(($classGroup->homeroom_teacher_id ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end gap-3 shrink-0">

                <button type="button" @click="close()" :disabled="saving"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-white text-secondary hover:bg-muted font-medium text-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="x" class="size-4"></i> Batal
                </button>

                <button type="submit" :disabled="saving"
                    class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 shadow-md text-white font-bold text-sm rounded-xl transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                    <i data-lucide="save" class="size-4" x-show="!saving"></i>

                    <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
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