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

$exitLabel = $mutationReasonMap[$r->status] ?? ucfirst(str_replace('_', ' ', $r->status));
$exitDate = $r->mutation_date;

$exitBadgeClass = match ($r->status) {
'transfer_in' => 'bg-teal-500/10 text-teal-700',
'transfer_out' => 'bg-blue-500/10 text-blue-600',
'dropped_out' => 'bg-error/10 text-error',
'deceased' => 'bg-gray-500/10 text-gray-700',
default => 'bg-secondary/10 text-secondary',
};
@endphp

<tr id="row-student-history-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-4 py-4">
        <div class="flex items-center gap-3">
            <div @style(["background: {$color}"])
                class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ $initials }}
            </div>
            <div>
                <div class="font-semibold text-foreground text-sm uppercase">{{ $student->name ?? '-' }}</div>
                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                    <span class="inline-block size-1.5 rounded-full {{ optional($student)->gender === 'L' ? 'bg-blue-500' : 'bg-pink-500' }}"></span>
                    {{ $genderLabel }}
                </div>
            </div>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 rounded-md bg-emerald-500/10 text-emerald-700 text-xs font-bold font-mono" title="NIS">{{ $student->nis ?? '-' }}</span>
            <span class="px-2 py-1 rounded-md bg-amber-500/10 text-amber-700 text-xs font-bold font-mono" title="NISN">{{ $nisn }}</span>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="text-sm font-semibold text-foreground">{{ $rombel }}</div>
        <div class="text-xs text-secondary">{{ $jurusan }}</div>
    </td>

    <td class="px-4 py-4">
        <span class="inline-block px-2 py-1 rounded-md text-xs font-bold {{ $exitBadgeClass }}">
            {{ $exitLabel }}
        </span>

        <div class="mt-1 flex items-center gap-1 text-[11px] text-secondary">
            <i data-lucide="calendar" class="w-3 h-3"></i>
            <span>
                {{ $exitDate ? \Illuminate\Support\Carbon::parse($exitDate)->translatedFormat('d M Y') : '-' }}
            </span>
        </div>
    </td>
</tr>