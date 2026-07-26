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
                <!-- Table Controls -->
                <div class="px-4 sm:px-6 py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shrink-0">
                    <!-- Informasi Total -->
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

                    <!-- Pencarian -->
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <div class="relative w-full md:w-72">
                            <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-secondary pointer-events-none"></i>
                            <input type="text" x-model="search" @input="$nextTick(() => syncHeaderCheckbox())" placeholder="Cari nama atau NIS..."
                                class="w-full bg-slate-50 hover:bg-white border border-border rounded-xl pl-10 pr-10 py-2.5 text-sm focus:bg-white focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                            <button type="button" x-show="search" x-cloak
                                @click="search = ''; $nextTick(() => syncHeaderCheckbox())"
                                class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center justify-center size-6 rounded-md text-secondary hover:bg-error/10 hover:text-error transition-colors cursor-pointer">
                                <i data-lucide="x" class="size-3.5 pointer-events-none"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="flex-1 flex flex-col min-h-0 px-4 sm:px-6 pb-4 sm:pb-6">
                    <div class="flex-1 overflow-auto border-y border-border/50">
                        <table class="w-full text-left border-collapse relative min-w-[550px]">
                            <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm shadow-sm">
                                <tr>
                                    <!-- Padding pl-4 agar nama tidak mepet kiri -->
                                    <th class="py-3 pl-4 pr-2 text-sm font-bold text-secondary uppercase tracking-wider w-1/2">Peserta Didik</th>
                                    <th class="px-2 py-3 text-sm font-bold text-secondary uppercase tracking-wider">NIS / NISN</th>
                                    <!-- Padding pr-4 agar checkbox tidak mepet kanan -->
                                    <th class="py-3 pl-2 pr-4 text-right w-16">
                                        <label class="relative inline-flex items-center justify-center cursor-pointer align-middle" title="Pilih Semua">
                                            <input type="checkbox" x-ref="headerCb" @change="checkAll"
                                                class="peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-primary checked:border-primary indeterminate:bg-primary indeterminate:border-primary hover:border-primary/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30">
                                            <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                                            <i data-lucide="minus" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-indeterminate:opacity-100 peer-indeterminate:scale-100 transition-all pointer-events-none"></i>
                                        </label>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/50">
                                @forelse($candidates as $c)
                                <tr class="hover:bg-slate-50/80 transition-colors group"
                                    x-show="search === '' || '{{ strtolower($c->student->name) }}'.includes(search.toLowerCase()) || '{{ $c->student->nis }}'.includes(search)">

                                    <td class="py-3 pl-4 pr-2">
                                        <div class="flex items-center gap-3">
                                            @php
                                            $initials = strtoupper(substr($c->student->name, 0, 2));
                                            $colors = ['bg-blue-100 text-blue-700', 'bg-emerald-100 text-emerald-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700'];
                                            $avatarColor = $colors[$loop->index % 4];
                                            @endphp
                                            <div class="size-9 rounded-full {{ $avatarColor }} flex items-center justify-center text-xs font-bold shrink-0">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-foreground uppercase group-hover:text-primary transition-colors">
                                                    {{ $c->student->name }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-2 py-3 align-middle">
                                        <div class="flex flex-wrap gap-1.5 items-center">
                                            <span class="px-2 py-0.5 rounded bg-cyan-100 text-cyan-600 font-bold text-xs">{{ $c->student->nis }}</span>
                                            <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-600 font-bold text-xs">{{ $c->student->vault->nisn_encrypted ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <td class="py-3 pl-2 pr-4 text-right">
                                        <label class="relative inline-flex items-center justify-center cursor-pointer align-middle">
                                            <input type="checkbox" name="student_id[]" value="{{ $c->student->id }}" @change="syncHeaderCheckbox"
                                                class="student-cb peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-primary checked:border-primary hover:border-primary/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30">
                                            <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                                        </label>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-16 text-center">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <div class="size-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-1">
                                                <i data-lucide="inbox" class="size-6"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-foreground">Tidak ada siswa yang siap diproses</p>
                                                <p class="text-xs text-secondary mt-1">Data siswa mungkin sudah diproses atau masih kosong.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer Form --}}
            <div class="px-4 sm:px-6 py-4 border-t border-border bg-gray-50/50 flex flex-col lg:flex-row items-stretch lg:items-center justify-between shrink-0 gap-5">

                <!-- Input Aksi Kenaikan -->
                <div class="grid grid-cols-2 sm:flex sm:flex-row items-center gap-3 w-full lg:w-auto">
                    <select name="decision" x-model="decision" class="col-span-1 bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">
                        <option value="naik">Naik Kelas</option>
                        <option value="tinggal">Tinggal Kelas</option>
                    </select>

                    <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="col-span-1 bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all">

                    <select name="target_class_group_id" x-show="decision === 'naik'" x-cloak class="col-span-2 sm:col-span-1 bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all" :required="decision === 'naik'" :disabled="decision !== 'naik'">
                        <option value="">-- Rombel Tujuan (Naik) --</option>
                        @foreach($targetGroupsNaik as $tg)
                        <option value="{{ $tg->id }}">{{ $tg->name }}</option>
                        @endforeach
                    </select>

                    <select name="target_class_group_id" x-show="decision === 'tinggal'" x-cloak class="col-span-2 sm:col-span-1 bg-white border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-primary transition-all" :required="decision === 'tinggal'" :disabled="decision !== 'tinggal'">
                        <option value="">-- Rombel Tujuan (Tinggal) --</option>
                        @foreach($targetGroupsTinggal as $tg)
                        <option value="{{ $tg->id }}">{{ $tg->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="grid grid-cols-2 sm:flex sm:flex-row items-center gap-2 w-full lg:w-auto">
                    <button type="button"
                        :disabled="saving"
                        @click="open = false; setTimeout(() => document.getElementById('modal-container').innerHTML = '', 150)"
                        class="col-span-1 justify-center inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 hover:shadow-sm transition-all duration-200 cursor-pointer disabled:opacity-50">
                        <i data-lucide="x" class="size-4"></i>
                        <span>Batal</span>
                    </button>

                    <button type="submit"
                        :disabled="saving || {{ $nextSemesterMissing ? 'true' : 'false' }}"
                        class="col-span-1 justify-center inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-md hover:bg-primary-dark hover:shadow-lg transition-all duration-200 cursor-pointer disabled:opacity-70">
                        <i data-lucide="save" class="size-4" x-show="!saving"></i>
                        <svg x-show="saving" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Memproses...' : 'Simpan Data'"></span>
                    </button>
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