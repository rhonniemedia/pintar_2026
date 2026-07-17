<div id="modal-container" x-data="{ open: false }" x-init="setTimeout(() => open = true, 10)">
    <x-ui.modal show="open" maxWidth="md">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">Pindah Kelas</h3>
                <p class="text-xs text-secondary mt-0.5">Pindahkan siswa ke rombel lain pada tingkat yang sama.</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => $el.closest('#modal-container').innerHTML = '', 150)" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form --}}
        <form hx-post="{{ route('admin.students.group.student.move', ['classGroup' => $currentClass->id, 'student' => $student->id]) }}"
            x-data="{ saving: false }"
            @htmx:before-request="saving = true"
            @htmx:after-request="
                saving = false;
                
                if ($event.detail.successful) {
                    // 1. Jika sukses, langsung tutup modal
                    open = false;
                    
                    // 2. Kosongkan container modal setelah animasi selesai
                    setTimeout(() => {
                        const container = document.getElementById('modal-container');
                        if (container) container.innerHTML = '';
                    }, 150);
                } else {
                    // Jika gagal, tangkap pesan error dari server dan tampilkan SweetAlert
                    let errorMsg = 'Gagal memproses perpindahan kelas.';
                    try {
                        const response = JSON.parse($event.detail.xhr.responseText);
                        if (response.errors) errorMsg = response.errors[Object.keys(response.errors)[0]][0];
                        else if (response.message) errorMsg = response.message;
                    } catch(e) {}
                    
                    window.ShowAlert({type: 'error', title: 'Validasi Gagal', message: errorMsg});
                }
            "
            class="flex flex-col">
            @csrf

            <div class="p-6 space-y-4">
                {{-- Info Siswa --}}
                <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl flex items-center gap-3">
                    <div class="size-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                        <i data-lucide="user" class="size-5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-foreground">{{ $student->name }}</p>
                        <p class="text-xs text-secondary">Dari Kelas: <span class="font-semibold text-blue-700">{{ $currentClass->name }}</span></p>
                    </div>
                </div>

                {{-- Dropdown Target Kelas --}}
                <div>
                    <label class="block text-sm font-bold text-foreground mb-2">Pilih Kelas Tujuan</label>
                    <select name="target_class_group_id" required
                        class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="">-- Pilih Rombel Tujuan --</option>
                        @forelse($availableClasses as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->name }} (Jurusan: {{ $class->concentration->name ?? '-' }})
                        </option>
                        @empty
                        <option value="" disabled>Tidak ada kelas lain di tingkat ini.</option>
                        @endforelse
                    </select>
                    <p class="text-xs text-secondary mt-1.5 flex items-center gap-1">
                        <i data-lucide="info" class="size-3"></i> Hanya menampilkan kelas tingkat {{ $currentClass->grade_level }}.
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end gap-2 shrink-0">
                <button type="button" :disabled="saving" @click="open = false; setTimeout(() => $el.closest('#modal-container').innerHTML = '', 150)"
                    class="px-4 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted transition-colors disabled:opacity-50">
                    Batal
                </button>
                <button type="submit" :disabled="saving"
                    class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-bold shadow-md rounded-xl hover:bg-primary-dark transition-all disabled:opacity-70">
                    <i data-lucide="arrow-right-left" class="size-4" x-show="!saving"></i>
                    <i data-lucide="loader-2" class="size-4 animate-spin" x-show="saving" x-cloak></i>
                    <span x-text="saving ? 'Memproses...' : 'Pindahkan'"></span>
                </button>
            </div>
        </form>
    </x-ui.modal>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>