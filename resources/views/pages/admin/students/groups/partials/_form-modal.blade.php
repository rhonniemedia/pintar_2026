@php
$isEdit = isset($classGroup);
$actionUrl = $isEdit ? route('admin.students.group.update', $classGroup->id) : route('admin.students.group.store');
$title = $isEdit ? 'Edit Data Rombel' : 'Tambah Data Rombel';
$subtitle = $isEdit ? 'Perbarui informasi rombongan belajar' : 'Tambahkan rombongan belajar baru ke sistem';
@endphp

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
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="{{ $isEdit ? 'edit-3' : 'component' }}" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">{{ $title }}</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">{{ $subtitle }}</p>
                </div>
            </div>

            <button @click="close()" type="button" class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
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
                    } catch (e) { console.error('Gagal parsing pesan error', e); }

                    window.ShowAlert({
                        type: 'error',
                        title: $event.detail.xhr.status === 422 ? 'Validasi Gagal' : 'Terjadi Kesalahan',
                        message: $event.detail.xhr.status === 422 ? errorMsg : 'Gagal memproses permintaan.'
                    });
                }
            "
            class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 space-y-4">
                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Rombongan Belajar</label>
                    <input type="text" name="name" value="{{ $classGroup->name ?? '' }}" placeholder="Contoh: X DPIB 1"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Kelas</label>
                    <select name="grade_level" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="10" @selected(($classGroup->grade_level ?? '') == '10')>Kelas X</option>
                        <option value="11" @selected(($classGroup->grade_level ?? '') == '11')>Kelas XI</option>
                        <option value="12" @selected(($classGroup->grade_level ?? '') == '12')>Kelas XII</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Nomor Kelompok</label>
                    <input type="number" name="group_number" value="{{ $classGroup->group_number ?? '1' }}" min="1" required placeholder="Contoh: 1"
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                    <p class="text-[11px] text-secondary mt-1.5">Angka urut pembeda kelas (misal: isi 1 untuk X DPIB 1).</p>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Jurusan</label>
                    <select name="concentration_id" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Pilih Jurusan</option>
                        @foreach($concentrationOptions as $id => $name)
                        <option value="{{ $id }}" @selected(($classGroup->concentration_id ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-2">Wali Kelas (Opsional)</label>
                    <select name="homeroom_teacher_id" class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">Pilih Wali Kelas</option>
                        @foreach($teacherOptions as $id => $name)
                        <option value="{{ $id }}" @selected(($classGroup->homeroom_teacher_id ?? '') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-2 shrink-0">

                <button type="button" @click="close()" :disabled="saving"
                    class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    Batal
                </button>

                <button type="submit" :disabled="saving"
                    class="flex items-center justify-center min-w-[140px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

                    <div x-show="!saving" class="flex items-center gap-1.5">
                        <i data-lucide="save" class="size-4"></i>
                        <span>Simpan</span>
                    </div>

                    <div x-show="saving" x-cloak class="flex items-center gap-1.5">
                        <i data-lucide="loader-2" class="size-4 animate-spin"></i>
                        <span>Menyimpan...</span>
                    </div>
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