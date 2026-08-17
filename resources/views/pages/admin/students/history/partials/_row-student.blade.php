@php
$student = $r->student;
$genderLabel = optional($student)->gender === 'L' ? 'Laki-laki' : 'Perempuan';

$initials = strtoupper(substr($student->name ?? 'U', 0, 2));
$colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
$color = $colors[isset($loop) ? ($loop->index % 4) : 0];

$nisn = optional(optional($student)->vault)->nisn_encrypted ?? '-';

$rombel = optional($r->classGroup)->name ?? '-';
$jurusan = optional(optional($r->classGroup)->concentration)->name ?? '-';

// Menentukan label dan warna berdasarkan status Mutasi
$mutationReasonMap = [
'transfer_in' => 'Pindahan Masuk',
'transfer_out' => 'Pindah Sekolah',
'dropped_out' => 'Putus Sekolah',
'deceased' => 'Meninggal Dunia',
];

$exitLabel = $mutationReasonMap[$r->status->value ?? ''] ?? ucfirst(str_replace('_', ' ', $r->status->value ?? '-'));
$exitDate = $r->mutation_date;

$exitBadgeClass = match ($r->status?->value) {
'transfer_in' => 'bg-teal-500/10 text-teal-700',
'transfer_out' => 'bg-blue-500/10 text-blue-600',
'dropped_out' => 'bg-error/10 text-error',
'deceased' => 'bg-gray-500/10 text-gray-700',
default => 'bg-secondary/10 text-secondary',
};
@endphp

<tr id="row-student-history-{{ $r->id }}" class="border-b border-border hover:bg-slate-50/80 transition-colors group">

    {{-- Peserta Didik --}}
    <td class="px-5 py-4 min-w-[240px]">
        <div class="flex items-center gap-3">
            <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />

            <div class="min-w-0">
                <div class="font-semibold text-foreground text-sm uppercase group-hover:text-primary transition-colors truncate">
                    {{ $student->name ?? '-' }}
                </div>
                <div class="text-xs text-secondary mt-0.5 truncate">
                    {{ optional(optional($student)->vault)->nik_encrypted ?? '-' }}
                </div>
            </div>
        </div>
    </td>

    {{-- Nomor Induk --}}
    <td class="px-5 py-4 min-w-[190px]">
        <div class="flex items-center gap-2">
            @if(!empty($student->nis))
            <span class="inline-block w-20 text-center px-2.5 py-1 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold whitespace-nowrap">{{ $student->nis }}</span>
            @endif
            <span class="px-2.5 py-1 rounded-md bg-warning/10 text-warning-dark text-xs font-bold whitespace-nowrap">{{ $nisn }}</span>
        </div>
    </td>

    {{-- Rombel & Jurusan --}}
    <td class="px-5 py-4 min-w-[160px]">
        <div class="text-sm font-semibold text-foreground whitespace-nowrap truncate">{{ $rombel }}</div>
        <div class="text-xs text-secondary whitespace-nowrap truncate">{{ $jurusan }}</div>
    </td>

    {{-- Keluar (Status & Tanggal) --}}
    <td class="px-5 py-4 min-w-[160px]">
        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider mb-1.5 whitespace-nowrap {{ $exitBadgeClass }}">
            {{ $exitLabel }}
        </span>

        <div class="flex items-center gap-1.5 text-xs text-secondary">
            <i data-lucide="calendar" class="size-3.5 shrink-0"></i>
            <span class="whitespace-nowrap">
                {{ $exitDate ? \Illuminate\Support\Carbon::parse($exitDate)->translatedFormat('d M Y') : '-' }}
            </span>
        </div>
    </td>
</tr>