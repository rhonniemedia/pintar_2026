<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="uppercase tracking-wider border-b border-border text-secondary font-semibold text-xs">
            <tr>
                <th scope="col" class="px-4 py-3 w-[30%]">Peserta</th>
                <th scope="col" class="px-4 py-3 w-[30%]">Asal Sekolah & Kontak</th>
                <th scope="col" class="px-4 py-3 w-[25%]">Pilihan Jurusan</th>
                <th scope="col" class="px-4 py-3 w-[15%]">Status & Waktu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($applicants as $applicant)
            @php
            $fullName = $applicant->personalData->full_name ?? 'Tanpa Nama';
            $init = strtoupper(substr($fullName, 0, 2));
            $colors = [
            'linear-gradient(135deg,#FF1443,#FF6B6B)',
            'linear-gradient(135deg,#3B82F6,#93C5FD)',
            'linear-gradient(135deg,#F59E0B,#FCD34D)',
            'linear-gradient(135deg,#8B5CF6,#A78BFA)'
            ];
            $color = $colors[$loop->index % 4];
            $phone = $applicant->personalData?->phone_number ?? null;
            $schoolCity = $applicant->personalData->previous_school_city ?? null;

            // Logika Status yang diperbarui (Tanpa border, warna lebih soft untuk background)
            $status = $applicant->verification_status;
            $badgeClass = match($status) {
            'verified' => 'bg-success/10 text-success-dark',
            'rejected' => 'bg-error/10 text-error-dark',
            default => 'bg-warning/10 text-warning-dark',
            };
            // Tambahan ikon untuk mempercantik status
            $statusIcon = match($status) {
            'verified' => 'check-circle-2',
            'rejected' => 'x-circle',
            default => 'clock',
            };
            $statusText = match($status) {
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => 'Menunggu',
            };
            @endphp
            <tr class="border-b border-border hover:bg-muted/50 transition-colors">

                {{-- Nama & Avatar --}}
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        @if($applicant->personalData && $applicant->personalData->photo)
                        <img src="{{ asset('storage/' . $applicant->personalData->photo) }}"
                            alt="Foto"
                            class="h-10 w-10 rounded-full object-cover shrink-0">
                        @else
                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0"
                            style="background: {{ $color }}">
                            {{ $init }}
                        </div>
                        @endif
                        <div>
                            <div class="font-semibold text-foreground text-sm uppercase">{{ $fullName }}</div>
                            <div class="text-xs text-secondary font-mono">{{ $applicant->registration_number ?? '-' }}</div>
                        </div>
                    </div>
                </td>

                {{-- Asal Sekolah & Kontak --}}
                <td class="px-4 py-4">
                    <div class="text-sm font-medium text-foreground uppercase leading-snug">
                        {{ $applicant->personalData->previous_school ?? '-' }}
                    </div>
                    @if($schoolCity)
                    <div class="mt-0.5 inline-flex items-center gap-1 text-xs text-secondary">
                        <i data-lucide="map-pin" class="size-3"></i>
                        <span>{{ $schoolCity }}</span>
                    </div>
                    @elseif($phone)
                    <a href="https://wa.me/62{{ ltrim($phone, '0') }}" target="_blank"
                        class="mt-1 inline-flex items-center gap-x-1.5 text-xs text-secondary hover:text-green-600 hover:underline transition-colors">
                        <i data-lucide="phone" class="size-3"></i>
                        <span>{{ $phone }}</span>
                    </a>
                    @endif
                </td>

                {{-- Pilihan Jurusan --}}
                <td class="px-4 py-4">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if($applicant->choice1Concentration)
                        <span class="inline-flex items-center justify-center gap-1 w-[62px] py-1 rounded-md text-xs font-bold border bg-red-100 text-red-700 border-red-200">
                            <span class="font-normal opacity-75">1.</span>
                            <span class="truncate">{{ $applicant->choice1Concentration->alias ?? $applicant->choice1Concentration->name }}</span>
                        </span>
                        @endif
                        @if(isset($applicant->choice2Concentration) && $applicant->choice2Concentration)
                        <span class="inline-flex items-center justify-center gap-1 w-[62px] py-1 rounded-md text-xs font-bold border bg-yellow-100 text-yellow-800 border-yellow-300">
                            <span class="font-normal opacity-75">2.</span>
                            <span class="truncate">{{ $applicant->choice2Concentration->alias ?? $applicant->choice2Concentration->name }}</span>
                        </span>
                        @endif
                        @if(isset($applicant->choice3Concentration) && $applicant->choice3Concentration)
                        <span class="inline-flex items-center justify-center gap-1 w-[62px] py-1 rounded-md text-xs font-bold border bg-gray-700 text-white border-gray-800">
                            <span class="font-normal opacity-75">3.</span>
                            <span class="truncate">{{ $applicant->choice3Concentration->alias ?? $applicant->choice3Concentration->name }}</span>
                        </span>
                        @endif
                        @if(!$applicant->choice1Concentration)
                        <span class="text-secondary text-xs">-</span>
                        @endif
                    </div>
                </td>

                {{-- Kolom Gabungan: Status (Atas) dan Waktu Daftar (Bawah) --}}
                <td class="px-4 py-4">
                    <div class="flex flex-col items-start gap-1.5">
                        {{-- Status Badge Baru --}}
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badgeClass }}">
                            <i data-lucide="{{ $statusIcon }}" class="size-3"></i>
                            {{ $statusText }}
                        </span>

                        {{-- Teks Waktu --}}
                        <span class="text-[10px] text-secondary font-medium flex items-center gap-1">
                            <i data-lucide="calendar" class="size-3"></i>
                            {{ $applicant->submitted_at ? \Carbon\Carbon::parse($applicant->submitted_at)->translatedFormat('d M Y, H:i') : '-' }}
                        </span>
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-10 text-center">
                    <div class="flex flex-col items-center justify-center gap-3">
                        <div class="size-12 rounded-full bg-slate-50 flex items-center justify-center">
                            <i data-lucide="file-search" class="size-6 text-secondary/50"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Tidak ada pendaftar ditemukan</p>
                            <p class="text-xs text-secondary mt-0.5">Coba ubah kata kunci atau filter pencarian.</p>
                        </div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination HTMX --}}
@if($applicants->hasPages())
<div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-4 px-4 mt-4">
    <span class="text-sm text-secondary text-center">
        Menampilkan <span class="font-semibold text-foreground">{{ $applicants->firstItem() ?? 0 }}</span>
        sampai <span class="font-semibold text-foreground">{{ $applicants->lastItem() ?? 0 }}</span>
        dari <span class="font-semibold text-foreground">{{ number_format($applicants->total() ?? 0, 0, ',', '.') }}</span> pendaftar
    </span>
    <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">

        {{-- Tombol Previous --}}
        @if ($applicants->onFirstPage())
        <button class="p-2 rounded-lg border border-border bg-white hover:bg-muted cursor-not-allowed opacity-50 transition-colors" disabled>
            <i data-lucide="chevron-left" class="size-4"></i>
        </button>
        @else
        <button type="button"
            hx-get="{{ route('admin.dashboard.applicants') }}"
            hx-target="#applicant-table-wrapper"
            hx-vals="js:{page: {{ $applicants->currentPage() - 1 }}, search: document.querySelector('#applicant-search')?.value ?? '', status: document.querySelector('#applicant-status')?.value ?? '', concentration: document.querySelector('#applicant-concentration')?.value ?? ''}"
            class="p-2 rounded-lg border border-border bg-white hover:bg-muted cursor-pointer transition-colors">
            <i data-lucide="chevron-left" class="size-4"></i>
        </button>
        @endif

        {{-- Nomor Halaman Dinamis (1, 2, ..., n) --}}
        @php
        $curr = $applicants->currentPage();
        $last = $applicants->lastPage();
        $pages = collect([1, $curr - 1, $curr, $curr + 1, $last])
        ->filter(fn($p) => $p >= 1 && $p <= $last)
            ->unique()->sort()->values();
            @endphp

            @foreach ($pages as $i => $page)
            @if ($i > 0 && $page - $pages[$i - 1] > 1)
            <span class="px-1 text-secondary text-sm">…</span>
            @endif

            @if ($page === $curr)
            <button class="w-9 h-9 rounded-lg bg-primary text-white text-sm font-bold" disabled>{{ $page }}</button>
            @else
            <button type="button"
                hx-get="{{ route('admin.dashboard.applicants') }}"
                hx-target="#applicant-table-wrapper"
                hx-vals="js:{page: {{ $page }}, search: document.querySelector('#applicant-search')?.value ?? '', status: document.querySelector('#applicant-status')?.value ?? '', concentration: document.querySelector('#applicant-concentration')?.value ?? ''}"
                class="w-9 h-9 rounded-lg border border-border bg-white hover:bg-muted text-sm cursor-pointer transition-colors">
                {{ $page }}
            </button>
            @endif
            @endforeach

            {{-- Tombol Next --}}
            @if ($applicants->hasMorePages())
            <button type="button"
                hx-get="{{ route('admin.dashboard.applicants') }}"
                hx-target="#applicant-table-wrapper"
                hx-vals="js:{page: {{ $applicants->currentPage() + 1 }}, search: document.querySelector('#applicant-search')?.value ?? '', status: document.querySelector('#applicant-status')?.value ?? '', concentration: document.querySelector('#applicant-concentration')?.value ?? ''}"
                class="p-2 rounded-lg border border-border bg-white hover:bg-muted cursor-pointer transition-colors">
                <i data-lucide="chevron-right" class="size-4"></i>
            </button>
            @else
            <button class="p-2 rounded-lg border border-border bg-white hover:bg-muted cursor-not-allowed opacity-50 transition-colors" disabled>
                <i data-lucide="chevron-right" class="size-4"></i>
            </button>
            @endif
    </div>
</div>
@endif

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>