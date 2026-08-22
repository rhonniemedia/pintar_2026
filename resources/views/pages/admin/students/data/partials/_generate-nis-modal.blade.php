<div id="modal-container"
    x-data="{ 
        open: false,
        isSubmitting: false,
        closeModal() {
            this.open = false;
            // Delay disesuaikan dengan durasi transisi keluar komponen x-ui.modal
            setTimeout(() => {
                document.getElementById('modal-container').outerHTML = '<div id=\'modal-container\'></div>';
            }, 300);
        }
    }"
    x-init="setTimeout(() => open = true, 50)"
    @close-modal.window="closeModal()">

    <x-ui.modal show="open" maxWidth="2xl">
        {{-- Header Modal --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                {{-- Ikon Header --}}
                <div class="size-11 sm:size-12 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 shadow-sm border border-purple-200">
                    <i data-lucide="hat-glasses" class="size-5 sm:size-6"></i>
                </div>

                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Generate NIS Siswa</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Mencetak NIS berurutan untuk siswa di rombel aktif</p>
                </div>
            </div>

            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0"
                :disabled="isSubmitting">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 custom-scrollbar">

            @if($eligibleStudents->isEmpty())
            <div class="text-center p-8 text-sm font-medium text-secondary bg-white rounded-xl border border-dashed border-border flex flex-col items-center gap-3">
                <div class="p-3 bg-white rounded-full shadow-sm border border-border/50">
                    <i data-lucide="check-circle-2" class="size-6 text-emerald-500"></i>
                </div>
                <p>Belum ada data siswa yang perlu di-generate NIS-nya.<br>Semua siswa di rombel aktif saat ini sudah memiliki NIS.</p>
            </div>
            @else
            {{-- Kelompokkan siswa berdasarkan jurusan --}}
            @php
            $groupedStudents = $eligibleStudents->groupBy(function($student) {
            return $student->concentration ? $student->concentration->name : 'Tanpa Jurusan';
            });
            @endphp

            <div class="mb-4">
                <p class="text-sm text-secondary">Sistem akan men-generate NIS secara otomatis dengan format: <strong>Kode Jurusan + Tahun Aktif + Nomor Urut</strong>.</p>
            </div>

            {{-- Kontainer Utama --}}
            <div class="bg-white border border-border rounded-xl overflow-hidden shadow-sm">

                {{-- Header Tabel/List --}}
                <div class="bg-slate-50/80 px-4 py-4 border-b border-border flex justify-between items-center">
                    <span class="font-bold text-sm text-foreground uppercase tracking-wider">Daftar Antrean Generate</span>
                    <span class="bg-purple-100 text-purple-700 text-xs font-bold px-3 py-1 rounded-md">Total: {{ $eligibleStudents->count() }} Siswa</span>
                </div>

                {{-- List --}}
                <div class="divide-y divide-border">
                    @foreach($groupedStudents as $jurusan => $students)
                    @php
                    $lakiLaki = $students->where('gender', 'L')->count();
                    $perempuan = $students->where('gender', 'P')->count();
                    $total = $students->count();
                    @endphp

                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[15px] font-semibold text-foreground leading-snug">{{ $jurusan }}</h4>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs text-secondary">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="mars" class="size-3.5 text-blue-500"></i> {{ $lakiLaki }} Laki-laki
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="venus" class="size-3.5 text-pink-500"></i> {{ $perempuan }} Perempuan
                                </span>
                            </div>
                        </div>

                        {{-- Bagian Kanan (Jumlah Siswa per Jurusan) --}}
                        <div class="flex flex-col items-center justify-center shrink-0 pl-3 w-[70px]">
                            <div>
                                <span class="text-xl font-black text-foreground leading-none">{{ $total }}</span>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest mt-1.5 text-secondary">Siswa</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Footer Modal --}}
        <div class="mt-auto px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-3 shrink-0">
            <button type="button" @click="closeModal()"
                class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer"
                :disabled="isSubmitting">
                Batal
            </button>

            @if($eligibleStudents->isNotEmpty())
            <form hx-post="{{ route('admin.students.data.generate-nis') }}"
                hx-swap="none"
                @submit="isSubmitting = true">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 disabled:opacity-70 disabled:cursor-not-allowed text-white rounded-xl font-semibold text-sm transition-all shadow-sm shadow-purple-600/30 cursor-pointer"
                    :disabled="isSubmitting">

                    {{-- Icon loading saat disubmit --}}
                    <i data-lucide="loader-2" class="size-4 animate-spin" x-show="isSubmitting" x-cloak></i>
                    <i data-lucide="sparkles" class="size-4" x-show="!isSubmitting"></i>

                    <span x-text="isSubmitting ? 'Memproses...' : 'Generate NIS'"></span>
                </button>
            </form>
            @endif
        </div>
    </x-ui.modal>

    {{-- Re-initialize Lucide Icons --}}
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>