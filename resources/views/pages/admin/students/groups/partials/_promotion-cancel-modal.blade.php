{{-- resources/views/pages/admin/students/groups/partials/_promotion-cancel-modal.blade.php --}}
<div id="modal-container"
    x-init="setTimeout(() => open = true, 10)"
    x-data="{
        open: false,
        search: '',
        saving: false,
        checkAll(e) {
            const checked = e.target.checked;
            document.querySelectorAll('.student-cb').forEach(el => {
                if(el.closest('tr').style.display !== 'none') {
                    el.checked = checked;
                }
            });
            e.target.indeterminate = false;
        },
        syncHeaderCheckbox() {
            const visible = Array.from(document.querySelectorAll('.student-cb'))
                .filter(el => el.closest('tr').style.display !== 'none');
            const checkedCount = visible.filter(el => el.checked).length;
            const header = this.$refs.headerCb;
            if (!header) return;
            header.checked = visible.length > 0 && checkedCount === visible.length;
            header.indeterminate = checkedCount > 0 && checkedCount < visible.length;
        }
    }">

    <x-ui.modal show="open" maxWidth="4xl">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-border shrink-0 bg-gray-50/50">
            <div>
                <h3 class="font-bold text-foreground text-lg">Pembatalan Kenaikan Kelas</h3>
                <p class="text-xs text-secondary mt-0.5">{{ $classGroup->name ?: $classGroup->grade_level . ' ' . optional($classGroup->concentration)->name }}</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form --}}
        <form hx-post="{{ route('admin.students.group.promotion.cancel', $classGroup->id) }}"
            @htmx:before-request="saving = true"
            @htmx:after-request="
                saving = false;
                
                if ($event.detail.successful) {
                    // Tutup modal secara otomatis setelah sukses
                    open = false;
                    setTimeout(() => {
                        const container = document.getElementById('modal-container');
                        if (container) container.innerHTML = '';
                    }, 150);
                } else {
                    // Tangkap dan tampilkan pesan error jika validasi/server gagal
                    let errorMsg = 'Gagal memproses data.';
                    try {
                        const response = JSON.parse($event.detail.xhr.responseText);
                        if (response.errors) errorMsg = response.errors[Object.keys(response.errors)[0]][0];
                        else if (response.message) errorMsg = response.message;
                    } catch(e) {}
                    
                    window.ShowAlert({type: 'error', title: 'Gagal', message: errorMsg});
                }
            "
            class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="flex-1 flex flex-col overflow-hidden">
                @if ($candidates->isEmpty())
                <!-- State Data Kosong (Dipercantik) -->
                <div class="flex-1 flex items-center justify-center p-6">
                    <div class="flex flex-col items-center justify-center gap-3">
                        <div class="size-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-1">
                            <i data-lucide="inbox" class="size-6"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-bold text-foreground">Data Kosong</p>
                            <p class="text-xs text-secondary mt-1">Belum ada siswa yang diproses kenaikan kelasnya di rombel ini.</p>
                        </div>
                    </div>
                </div>
                @else
                <!-- Table Controls -->
                <div class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0">
                    <!-- Informasi Total (Menggunakan Aksen Merah/Error) -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center size-10 rounded-full bg-error/10 text-error">
                            <i data-lucide="users" class="size-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-secondary">Total Kandidat</p>
                            <p class="text-sm font-bold text-foreground">
                                {{ $candidates->count() }} <span class="font-normal text-secondary">Siswa</span>
                            </p>
                        </div>
                    </div>

                    <!-- Pencarian -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-72">
                            <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                            <input type="text" x-model="search" @input="$nextTick(() => syncHeaderCheckbox())" placeholder="Cari nama atau NIS..."
                                class="w-full bg-slate-50 hover:bg-white border border-border rounded-xl pl-10 pr-10 py-2.5 text-sm focus:bg-white focus:outline-none focus:border-error focus:ring-4 focus:ring-error/10 transition-all">
                            <button type="button" x-show="search" x-cloak
                                @click="search = ''; $nextTick(() => syncHeaderCheckbox())"
                                class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center justify-center size-6 rounded-md text-secondary hover:bg-error/10 hover:text-error transition-colors cursor-pointer">
                                <i data-lucide="x" class="size-3.5 pointer-events-none"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Container (Tanpa Card) -->
                <div class="px-6 pb-6 overflow-y-auto flex-1">
                    <table class="w-full text-left border-collapse relative">
                        <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm">
                            <tr class="border-b border-border">
                                <th class="pb-3 pt-2 text-sm font-bold text-secondary uppercase tracking-wider w-1/2">Peserta Didik</th>
                                <th class="pb-3 pt-2 text-sm font-bold text-secondary uppercase tracking-wider">NIS / NISN</th>
                                <th class="pb-3 pt-2 text-right w-16">
                                    <label class="relative inline-flex items-center justify-center cursor-pointer align-middle" title="Pilih Semua">
                                        <input type="checkbox" x-ref="headerCb" @change="checkAll"
                                            class="peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-error checked:border-error indeterminate:bg-error indeterminate:border-error hover:border-error/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-error/30">
                                        <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                                        <i data-lucide="minus" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-indeterminate:opacity-100 peer-indeterminate:scale-100 transition-all pointer-events-none"></i>
                                    </label>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            @foreach ($candidates as $row)
                            <tr class="hover:bg-slate-50/80 transition-colors group"
                                x-show="search === '' || '{{ strtolower($row->student->name) }}'.includes(search.toLowerCase()) || '{{ $row->student->nis ?? '' }}'.includes(search)">

                                <!-- Kolom Nama & Avatar -->
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        @php
                                        $initials = strtoupper(substr($row->student->name, 0, 2));
                                        $colors = ['bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700'];
                                        $avatarColor = $colors[$loop->index % 4];
                                        @endphp
                                        <div class="size-9 rounded-full {{ $avatarColor }} flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-foreground uppercase group-hover:text-error transition-colors">
                                                {{ $row->student->name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom NIS/NISN (Ukuran dikembalikan ke awal) -->
                                <td class="py-3 text-sm flex gap-2 items-center h-full mt-2">
                                    <span class="px-2 py-0.5 rounded bg-cyan-100 text-cyan-600 font-bold text-xs">{{ $row->student->nis ?? '-' }}</span>
                                    <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-600 font-bold text-xs">{{ $row->student->vault->nisn_encrypted ?? '-' }}</span>
                                </td>

                                <!-- Kolom Checkbox -->
                                <td class="py-3 text-right">
                                    <label class="relative inline-flex items-center justify-center cursor-pointer align-middle">
                                        <input type="checkbox" name="student_id[]" value="{{ $row->student->id }}" @change="syncHeaderCheckbox"
                                            class="student-cb peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-error checked:border-error hover:border-error/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-error/30">
                                        <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Footer Form --}}
            <div class="px-6 py-4 border-t border-border bg-gray-50/50 flex items-center justify-end shrink-0 gap-4">
                <div class="flex items-center gap-2">
                    <button type="button"
                        :disabled="saving"
                        @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 hover:shadow-sm transition-all duration-200 cursor-pointer disabled:opacity-50">
                        <i data-lucide="x" class="size-4"></i>
                        <span>Batal</span>
                    </button>

                    @if ($candidates->isNotEmpty())
                    <button type="submit"
                        :disabled="saving"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-error text-white text-sm font-bold shadow-md hover:opacity-90 hover:shadow-lg transition-all duration-200 cursor-pointer disabled:opacity-70">
                        <i data-lucide="alert-circle" class="size-4" x-show="!saving"></i>
                        <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Membatalkan...' : 'Batalkan Kenaikan'"></span>
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </x-ui.modal>

    {{-- Trigger Lucide Icons --}}
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>