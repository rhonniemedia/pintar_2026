@php
$relationIcon = fn(?\App\Enums\Student\FamilyRelation $relation) => match ($relation) {
\App\Enums\Student\FamilyRelation::AYAH => 'user',
\App\Enums\Student\FamilyRelation::IBU => 'users',
\App\Enums\Student\FamilyRelation::WALI => 'shield-check',
default => 'shield-check',
};
$firstGuardianId = optional($student->guardians->first())->id;
@endphp

<div id="modal-container"
    x-data="{ 
        open: false,
        activeTab: '{{ $firstGuardianId }}',
        closeModal() {
            this.open = false;
            setTimeout(() => document.getElementById('modal-container').outerHTML = '<div id=\'modal-container\'></div>', 300);
        }
    }"
    x-init="setTimeout(() => open = true, 50)">

    <x-ui.modal show="open" maxWidth="3xl">
        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="users" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Data Orang Tua / Wali</h3>
                    <div class="flex items-center flex-wrap gap-1.5 mt-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-cyan-50 border border-cyan-100 text-cyan-600 font-semibold text-[10px] sm:text-[11px] uppercase">
                            {{ $student->name }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-secondary font-semibold text-[10px] sm:text-[11px]">
                            {{ $student->guardians->count() }} data tercatat
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        @if($student->guardians->isEmpty())
        {{-- Empty State --}}
        <div class="flex-1 flex flex-col items-center justify-center gap-3 p-10 sm:p-14 text-center">
            <div class="size-14 rounded-full bg-slate-100 flex items-center justify-center">
                <i data-lucide="user-x" class="size-6 text-secondary"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-foreground">Belum ada data orang tua/wali</p>
                <p class="text-xs text-secondary mt-1 max-w-xs">Data ayah, ibu, atau wali untuk siswa ini belum diinput ke sistem.</p>
            </div>
        </div>
        @else
        {{-- Tab Navigation --}}
        <div class="sticky top-0 z-20 bg-white border-b border-border shadow-sm">
            <div class="flex flex-wrap items-center gap-2 px-4 sm:px-6 py-3">
                @foreach($student->guardians as $guardian)
                <button @click="activeTab = '{{ $guardian->id }}'"
                    class="flex-auto flex justify-center items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer whitespace-nowrap"
                    :class="activeTab === '{{ $guardian->id }}' 
                    ? 'bg-primary text-white shadow-sm shadow-primary/30 ring-2 ring-primary/20' 
                    : 'bg-slate-100 text-secondary hover:bg-slate-200'">
                    <i data-lucide="{{ $relationIcon($guardian->relationship) }}" class="size-3.5 sm:size-4"></i>
                    <span>{{ $guardian->relationship?->label() ?? 'Wali' }}{{ $guardian->name ? ' · ' . \Illuminate\Support\Str::limit($guardian->name, 18) : '' }}</span>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Tab Contents --}}
        {{-- 'grid' membuat tiap panel orang tua/wali bertumpuk di 1 sel yang sama, sehingga
             saat Alpine menjalankan transisi fade, panel lama & baru saling menimpa alih-alih
             mendorong tinggi kontainer (mencegah modal terlihat "melebar" sesaat). --}}
        <div class="grid p-4 sm:p-6 overflow-y-auto max-h-[55vh] sm:max-h-[60vh] bg-slate-50/30 flex-1 [scrollbar-gutter:stable]">

            @foreach($student->guardians as $guardian)
            @php
            // ASUMSI: value backing LivingStatus untuk kondisi "hidup" adalah 'alive'
            // (sesuai perilaku sebelum enum ini ada). Sesuaikan jika case-nya beda.
            $isAlive = $guardian->living_status?->value === 'alive';
            @endphp
            <div x-show="activeTab === '{{ $guardian->id }}'" x-transition.opacity @if(!$loop->first) x-cloak @endif class="col-start-1 row-start-1">

                {{-- Identitas Orang Tua/Wali --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $guardian->name ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Hubungan</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $guardian->relationship?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Status</label>
                        <p class="inline-flex items-center gap-1.5 text-sm font-medium leading-snug {{ $isAlive ? 'text-foreground' : 'text-secondary' }}">
                            <span class="size-1.5 rounded-full {{ $isAlive ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $isAlive ? 'Hidup' : 'Meninggal Dunia' }}
                        </p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Tahun Lahir</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $guardian->birth_year ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Pekerjaan</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $guardian->occupation?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Pendidikan Terakhir</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $guardian->education?->label() ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3 sm:col-span-2">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Rentang Penghasilan</label>
                        <p class="text-sm font-medium text-foreground leading-snug">{{ $guardian->income_range?->label() ?? '-' }}</p>
                    </div>
                </div>

                {{-- Kontak & Alamat --}}
                <div class="flex items-center gap-2 pt-5 mt-5 border-t border-border/70">
                    <i data-lucide="phone" class="size-4 text-secondary"></i>
                    <h4 class="font-bold text-foreground text-sm">Kontak & Alamat</h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nomor Induk Kependudukan (NIK)</label>
                        <p class="text-sm font-medium text-foreground leading-snug font-mono">{{ $guardian->vault->nik_encrypted ?? '-' }}</p>
                    </div>
                    <div class="bg-white border border-border rounded-xl px-3.5 py-3">
                        <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1">Nomor Telepon / WhatsApp</label>
                        <p class="text-sm font-medium text-foreground leading-snug font-mono">{{ $guardian->vault->phone_number_encrypted ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5 px-1">Alamat</label>
                    <div class="flex items-start gap-2.5 bg-white border border-border rounded-xl p-3.5">
                        <i data-lucide="map-pin" class="size-4 text-secondary shrink-0 mt-0.5"></i>
                        <p class="text-sm font-medium text-foreground leading-relaxed">
                            {{ $guardian->vault->address_encrypted ?? 'Alamat sama dengan siswa / belum diisi' }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
        @endif

        {{-- Footer Modal --}}
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end shrink-0">
            <button type="button" @click="closeModal()"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </x-ui.modal>

    {{-- Re-initialize Lucide Icons di dalam DOM modal yang baru di-load HTMX --}}
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>