<div x-data="{ 
    modalOpen: true,
    checkAll(e) {
        const checked = e.target.checked;
        document.querySelectorAll('.student-cb').forEach(el => {
            el.checked = checked;
        });
        e.target.indeterminate = false;
    },
    syncHeaderCheckbox() {
        const checkboxes = Array.from(document.querySelectorAll('.student-cb'));
        const checkedCount = checkboxes.filter(el => el.checked).length;
        const header = this.$refs.headerCb;
        if (!header) return;
        header.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        header.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }
}">
    @php
    $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII'];
    $gradeLabel = $grades[$classGroup->grade_level] ?? $classGroup->grade_level;
    $alias = $classGroup->name ?: "{$gradeLabel} {$classGroup->concentration->name} {$classGroup->group_number}";
    $jurusan = $classGroup->concentration->name ?? '-';
    @endphp

    <x-ui.modal show="modalOpen" maxWidth="3xl">

        {{-- Header --}}
        <div class="flex items-start sm:items-center justify-between px-4 sm:px-6 py-4 sm:py-5 border-b border-border shrink-0 bg-gray-50/50 gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <div class="size-9 rounded-xl bg-emerald-600/10 flex items-center justify-center shrink-0">
                    <i data-lucide="user-plus" class="size-4 text-emerald-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-foreground text-lg leading-tight">Tambah Peserta Didik</h3>
                    <p class="text-xs text-secondary mt-1 sm:mt-0.5">
                        Ke Rombel: <span class="font-bold text-foreground">{{ $alias }}</span> <br class="block sm:hidden" />
                        <span class="hidden sm:inline">&bull;</span>
                        Jurusan: <span class="font-semibold text-foreground">{{ $jurusan }}</span>
                    </p>
                </div>
            </div>
            <button type="button" @click="modalOpen = false" class="size-8 rounded-lg border border-border flex items-center justify-center hover:bg-muted transition-colors cursor-pointer shrink-0 mt-1 sm:mt-0">
                <i data-lucide="x" class="size-4 text-secondary"></i>
            </button>
        </div>

        {{-- Form Body --}}
        <form id="add-student-form"
            hx-post="{{ route('admin.students.group.add-student.store', $classGroup->id) }}"
            hx-target="#students-container"
            hx-swap="outerHTML"
            @htmx:after-request="if($event.detail.successful) modalOpen = false"
            class="flex flex-col max-h-[75vh]">

            @csrf

            <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                @if($floatingStudents->isEmpty())
                <div class="text-center py-10">
                    <i data-lucide="inbox" class="size-12 text-border mx-auto mb-3"></i>
                    <p class="text-sm font-medium text-foreground">Tidak ada siswa mengambang</p>
                    <p class="text-xs text-secondary mt-1">Semua siswa di jurusan ini sudah memiliki rombongan belajar.</p>
                </div>
                @else
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between px-1 mb-3 sm:mb-4 gap-2">
                        <span class="text-sm font-bold text-foreground">{{ $floatingStudents->count() }} Siswa Tersedia</span>

                        {{-- Checkbox "Pilih Semua" (Custom UI) --}}
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

                    {{-- Daftar Siswa --}}
                    @foreach($floatingStudents as $student)
                    <label class="flex items-start sm:items-center gap-3 p-3 sm:p-4 rounded-xl border border-border hover:bg-muted/50 transition-colors cursor-pointer group shadow-sm">

                        {{-- Checkbox Individual (Custom UI) --}}
                        <div class="relative inline-flex items-center justify-center cursor-pointer align-middle shrink-0 mt-1 sm:mt-0">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @change="syncHeaderCheckbox"
                                class="student-cb peer appearance-none size-5 rounded-md border-2 border-border bg-white checked:bg-primary checked:border-primary hover:border-primary/60 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <i data-lucide="check" class="absolute inset-0 m-auto size-3.5 text-white opacity-0 scale-50 peer-checked:opacity-100 peer-checked:scale-100 transition-all pointer-events-none"></i>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-2.5 sm:gap-4 overflow-hidden">

                            {{-- Kolom 1: Profil Siswa --}}
                            <div class="flex items-center gap-3 sm:gap-4">
                                <x-ui.avatar :name="$student->name" :gender="$student->gender" :index="$loop->index" class="size-9 text-xs shrink-0" />
                                <div>
                                    <div class="text-sm font-bold text-foreground group-hover:text-primary transition-colors leading-tight">{{ $student->name }}</div>
                                    <div class="text-xs text-secondary mt-0.5">{{ $student->vault->nisn_encrypted ?? 'NISN Kosong' }}</div>
                                </div>
                            </div>

                            {{-- Kolom 2: Jurusan --}}
                            <div class="pl-[3.25rem] sm:pl-0 shrink-0 text-left sm:w-[45%]">
                                <div class="text-sm font-bold text-foreground">{{ $student->concentration->alias ?? 'KODE' }}</div>
                                <div class="text-xs text-secondary mt-0.5 truncate">{{ $student->concentration->name ?? 'Tanpa Jurusan' }}</div>
                            </div>

                        </div>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-4 sm:px-6 py-4 border-t border-border bg-gray-50/50 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end shrink-0 gap-2 sm:gap-3">
                <button type="button" @click="modalOpen = false" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-sm font-semibold text-secondary hover:bg-muted transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    @if($floatingStudents->isEmpty()) disabled @endif
                    class="w-full sm:w-auto flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="save" class="size-4"></i>
                    Simpan ke Rombel
                </button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Script pembaruan Lucide Icons --}}
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>