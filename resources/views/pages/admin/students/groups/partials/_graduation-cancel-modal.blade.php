{{-- resources/views/pages/admin/students/groups/partials/_graduation-cancel-modal.blade.php --}}
<div id="modal-container"
    x-init="setTimeout(() => open = true, 10)"
    x-data="{
        open: false,
        search: '',
        saving: false,
        checkAll(e) {
            const checked = e.target.checked;
            document.querySelectorAll('.student-cb').forEach(el => {
                if(el.closest('.student-row').style.display !== 'none') {
                    el.checked = checked;
                }
            });
            e.target.indeterminate = false;
        },
        syncHeaderCheckbox() {
            const visible = Array.from(document.querySelectorAll('.student-cb'))
                .filter(el => el.closest('.student-row').style.display !== 'none');
            const checkedCount = visible.filter(el => el.checked).length;
            const header = this.$refs.headerCb;
            if (!header) return;
            header.checked = visible.length > 0 && checkedCount === visible.length;
            header.indeterminate = checkedCount > 0 && checkedCount < visible.length;
        }
    }">

    <x-ui.modal show="open" maxWidth="4xl">
        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between px-4 sm:px-6 py-4 sm:py-5 border-b border-border shrink-0 bg-gray-50/50 gap-4">
            <div>
                <h3 class="font-bold text-foreground text-lg">Pembatalan Kelulusan</h3>
                <p class="text-xs text-secondary mt-0.5">{{ $classGroup->name ?: $classGroup->grade_level . ' ' . optional($classGroup->concentration)->name }}</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form --}}
        <form hx-post="{{ route('admin.students.group.graduation.cancel', $classGroup->id) }}"
            @htmx:before-request="saving = true"
            @htmx:after-request="
                saving = false;
                
                if ($event.detail.successful) {
                    open = false;
                    setTimeout(() => {
                        const container = document.getElementById('modal-container');
                        if (container) container.innerHTML = '';
                    }, 150);
                } else {
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
                <!-- Kontrol Atas -->
                <div class="px-4 sm:px-6 py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shrink-0 border-b border-border">
                    <!-- Informasi Total -->
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div class="flex items-center justify-center size-10 rounded-full bg-error/10 text-error shrink-0">
                            <i data-lucide="users" class="size-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-secondary">Total Kandidat</p>
                            <p class="text-sm font-bold text-foreground">
                                {{ $candidates->count() }} <span class="font-normal text-secondary">Siswa</span>
                            </p>
                        </div>
                    </div>

                    <!-- Pencarian dengan Tombol X Interaktif -->
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <div class="relative w-full md:w-72 flex items-center">
                            <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                                :class="search.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                            <input type="text" x-ref="searchInput" x-model="search" @input="$nextTick(() => syncHeaderCheckbox())" placeholder="Cari nama atau NIS..."
                                class="w-full bg-slate-50 hover:bg-white border border-border rounded-xl pl-10 pr-10 py-2.5 text-sm focus:bg-white focus:outline-none focus:border-error focus:ring-4 focus:ring-error/10 transition-all"
                                :class="search.length > 0 ? 'border-error/50 font-medium' : 'border-border'">

                            <button type="button" x-show="search" x-cloak
                                @click="search = ''; $nextTick(() => syncHeaderCheckbox())"
                                class="absolute right-3 flex items-center justify-center size-5 rounded-full bg-slate-100 hover:bg-error/10 text-secondary hover:text-error transition-all cursor-pointer focus:outline-none">
                                <i data-lucide="x" class="size-3"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Daftar Siswa -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                    @if ($candidates->isEmpty())
                    <!-- State Data Kosong -->
                    <div class="flex-1 flex items-center justify-center py-10">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="size-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-1">
                                <i data-lucide="inbox" class="size-6"></i>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-foreground">Data Kosong</p>
                                <p class="text-xs text-secondary mt-1">Belum ada siswa yang diproses kelulusannya di rombel ini.</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between px-1 mb-3 sm:mb-4 gap-2">
                            <span class="text-sm font-bold text-foreground">{{ $candidates->count() }} Siswa Ditemukan</span>

                            {{-- Checkbox "Pilih Semua" --}}
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-secondary">Pilih Semua</span>
                                <label class="relative inline-flex items-center justify-center cursor-pointer align-middle" title="Pilih Semua">
                                    <input type="checkbox" x-ref="headerCb" @change="checkAll"
                                        class="peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-error checked:border-error indeterminate:bg-error indeterminate:border-error hover:border-error/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-error/30">
                                    <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                                    <i data-lucide="minus" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-indeterminate:opacity-100 peer-indeterminate:scale-100 transition-all pointer-events-none"></i>
                                </label>
                            </div>
                        </div>

                        @foreach ($candidates as $row)
                        <label class="student-row flex items-start sm:items-center gap-3 p-3 sm:p-4 rounded-xl border border-border hover:bg-muted/50 transition-colors cursor-pointer group shadow-sm"
                            x-show="search === '' || '{{ strtolower($row->student->name) }}'.includes(search.toLowerCase()) || '{{ $row->student->nis ?? '' }}'.includes(search)">

                            {{-- Checkbox Individual --}}
                            <div class="relative inline-flex items-center justify-center cursor-pointer align-middle shrink-0 mt-1 sm:mt-0">
                                <input type="checkbox" name="student_id[]" value="{{ $row->student->id }}" @change="syncHeaderCheckbox"
                                    class="student-cb peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-error checked:border-error hover:border-error/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-error/30">
                                <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-2.5 sm:gap-4 overflow-hidden">

                                {{-- Kolom 1: Profil Siswa & NIS/NISN --}}
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <x-ui.avatar :name="$row->student->name" :gender="$row->student->gender ?? null" :index="$loop->index" class="size-9 text-xs shrink-0" />

                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-semibold text-foreground group-hover:text-error transition-colors leading-tight uppercase">{{ $row->student->name }}</div>
                                        <div class="text-xs text-secondary mt-0.5">
                                            NIS: {{ $row->student->nis ?? '-' }} <span class="mx-1">&bull;</span> NISN: {{ $row->student->vault->nisn_encrypted ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom 2: Tempat & Tanggal Lahir --}}
                                <div class="pl-[3.25rem] sm:pl-0 shrink-0 text-left sm:w-[45%]">
                                    <div class="text-sm font-semibold text-foreground truncate">{{ $row->student->vault->pob_encrypted ?? '-' }}</div>
                                    <div class="text-xs text-secondary mt-0.5 flex items-center gap-1.5 overflow-hidden">
                                        <i data-lucide="calendar" class="size-3.5 shrink-0"></i>
                                        <span class="truncate">{{ $row->student->vault->dob_encrypted ? \Carbon\Carbon::parse($row->student->vault->dob_encrypted)->translatedFormat('d F Y') : '-' }}</span>
                                    </div>
                                </div>

                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer Form --}}
            <div class="px-4 sm:px-6 py-4 border-t border-border bg-gray-50 flex items-center justify-end shrink-0">
                <!-- Wrapper Tombol -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button"
                        :disabled="saving"
                        @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                        class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all duration-200 cursor-pointer disabled:opacity-50">
                        Batal
                    </button>

                    @if ($candidates->isNotEmpty())
                    <button type="submit"
                        :disabled="saving"
                        class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-error text-white text-sm font-bold shadow-md hover:opacity-90 hover:shadow-lg transition-all duration-200 cursor-pointer disabled:opacity-70">
                        <i data-lucide="alert-circle" class="size-4" x-show="!saving"></i>
                        <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Membatalkan...' : 'Batalkan Kelulusan'"></span>
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