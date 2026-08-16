@php
$targetOptionsNaik = [];
foreach($targetGroupsNaik as $tg) {
$targetOptionsNaik[] = ['value' => $tg->id, 'label' => $tg->name];
}

$targetOptionsTinggal = [];
foreach($targetGroupsTinggal as $tg) {
$targetOptionsTinggal[] = ['value' => $tg->id, 'label' => $tg->name];
}
@endphp

<div id="modal-container"
    x-init="setTimeout(() => open = true, 10)"
    x-data="{
        open: false,
        search: '',
        decision: 'naik',
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
                <h3 class="font-bold text-foreground text-lg">Proses Kenaikan Kelas</h3>
                <p class="text-xs text-secondary mt-0.5">Tentukan status kenaikan atau tinggal kelas peserta didik.</p>
            </div>
            <button type="button" @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        @if($nextSemesterMissing)
        <div class="bg-error/10 text-error px-4 sm:px-6 py-3 text-sm font-medium border-b border-error/20 flex gap-2 items-start sm:items-center shrink-0">
            <i data-lucide="alert-triangle" class="size-4 shrink-0 mt-0.5 sm:mt-0"></i>
            <span>Semester berikutnya belum diatur. Harap buat semester baru di Data Master sebelum memproses kenaikan.</span>
        </div>
        @endif

        {{-- Form --}}
        <form hx-post="{{ route('admin.students.group.promote', $classGroup->id) }}"
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
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div class="flex items-center justify-center size-10 rounded-full bg-primary/10 text-primary shrink-0">
                            <i data-lucide="users" class="size-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-secondary">Total Kandidat</p>
                            <p class="text-sm font-bold text-foreground">
                                {{ $candidates->count() }} <span class="font-normal text-secondary">Siswa</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <div class="relative w-full md:w-72 flex items-center">
                            <i data-lucide="search" class="absolute left-3.5 size-4 transition-colors pointer-events-none"
                                :class="search.length > 0 ? 'text-primary' : 'text-secondary'"></i>

                            <input type="text" x-ref="searchInput" x-model="search" @input="$nextTick(() => syncHeaderCheckbox())" placeholder="Cari nama atau NIS..."
                                class="w-full bg-slate-50 hover:bg-white border border-border rounded-xl pl-10 pr-10 py-2.5 text-sm focus:bg-white focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all"
                                :class="search.length > 0 ? 'border-primary/50 font-medium' : 'border-border'">

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
                    @if($candidates->isEmpty())
                    <div class="text-center py-10">
                        <i data-lucide="inbox" class="size-12 text-border mx-auto mb-3"></i>
                        <p class="text-sm font-medium text-foreground">Tidak ada siswa yang siap diproses</p>
                        <p class="text-xs text-secondary mt-1">Data siswa mungkin sudah diproses atau masih kosong.</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between px-1 mb-3 sm:mb-4 gap-2">
                            <span class="text-sm font-bold text-foreground">{{ $candidates->count() }} Siswa Ditemukan</span>

                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-secondary">Pilih Semua</span>
                                <label class="relative inline-flex items-center justify-center cursor-pointer align-middle" title="Pilih Semua">
                                    <input type="checkbox" x-ref="headerCb" @change="checkAll"
                                        class="peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-primary checked:border-primary indeterminate:bg-primary indeterminate:border-primary hover:border-primary/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                                    <i data-lucide="minus" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-indeterminate:opacity-100 peer-indeterminate:scale-100 transition-all pointer-events-none"></i>
                                </label>
                            </div>
                        </div>

                        @foreach($candidates as $c)
                        <label class="student-row flex items-start sm:items-center gap-3 p-3 sm:p-4 rounded-xl border border-border hover:bg-muted/50 transition-colors cursor-pointer group shadow-sm"
                            x-show="search === '' || '{{ strtolower($c->student->name) }}'.includes(search.toLowerCase()) || '{{ $c->student->nis }}'.includes(search)">

                            <div class="relative inline-flex items-center justify-center cursor-pointer align-middle shrink-0 mt-1 sm:mt-0">
                                <input type="checkbox" name="student_id[]" value="{{ $c->student->id }}" @change="syncHeaderCheckbox"
                                    class="student-cb peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-primary checked:border-primary hover:border-primary/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30">
                                <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-2.5 sm:gap-4 overflow-hidden">
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <x-ui.avatar :name="$c->student->name" :gender="$c->student->gender ?? null" :index="$loop->index" class="size-9 text-xs shrink-0" />
                                    <div class="flex flex-col gap-1">
                                        <div class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors leading-tight uppercase">{{ $c->student->name }}</div>
                                        <div class="text-xs text-secondary mt-0.5">
                                            NIS: {{ $c->student->nis ?? '-' }} <span class="mx-1">&bull;</span> NISN: {{ $c->student->vault->nisn_encrypted ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="pl-[3.25rem] sm:pl-0 shrink-0 text-left sm:w-[45%]">
                                    <div class="text-sm font-semibold text-foreground truncate">{{ $c->student->vault->pob_encrypted ?? '-' }}</div>
                                    <div class="text-xs text-secondary mt-0.5 flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="size-3.5"></i>
                                        <span>{{ $c->student->vault->dob_encrypted ? \Carbon\Carbon::parse($c->student->vault->dob_encrypted)->translatedFormat('d F Y') : '-' }}</span>
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
            <div class="px-4 sm:px-6 py-4 border-t border-border bg-gray-50 flex flex-col gap-4">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-5">

                    <!-- Input Pengaturan Kenaikan -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full md:w-3/4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-secondary">Status Keputusan</label>

                            <div @change="decision = $event.target.value">
                                <x-ui.select
                                    name="decision"
                                    :options="[
                                        ['value' => 'naik', 'label' => 'Naik Kelas'],
                                        ['value' => 'tinggal', 'label' => 'Tinggal Kelas']
                                    ]"
                                    value="naik"
                                    placeholder="-- Pilih Keputusan --" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-secondary">Tanggal Proses</label>
                            <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-secondary">Rombel Tujuan</label>

                            <div x-show="decision === 'naik'">
                                <x-ui.select
                                    name="target_class_group_id"
                                    :options="$targetOptionsNaik"
                                    placeholder="-- Pilih Rombel --" />
                            </div>

                            <div x-show="decision === 'tinggal'" x-cloak>
                                <x-ui.select
                                    name="target_class_group_id"
                                    :options="$targetOptionsTinggal"
                                    placeholder="-- Pilih Rombel --" />
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center gap-2 w-full md:w-auto mt-2 md:mt-0 justify-end">
                        <button type="button"
                            :disabled="saving"
                            @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                            class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all duration-200 cursor-pointer disabled:opacity-50">
                            Batal
                        </button>

                        <button type="submit"
                            :disabled="saving || {{ $nextSemesterMissing ? 'true' : 'false' }}"
                            class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-md hover:bg-primary-dark hover:shadow-lg transition-all duration-200 cursor-pointer disabled:opacity-70">
                            <i data-lucide="save" class="size-4" x-show="!saving"></i>
                            <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="saving ? 'Memproses...' : 'Simpan'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-ui.modal>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>