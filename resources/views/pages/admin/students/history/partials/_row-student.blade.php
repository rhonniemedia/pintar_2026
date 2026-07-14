@php
$genderLabel = $r->gender === 'L' ? 'Laki-laki' : 'Perempuan';

$initials = strtoupper(substr($r->name, 0, 2));
$colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#8B5CF6,#A78BFA)'];
$color = $colors[isset($loop) ? ($loop->index % 4) : 0];

$nisn = $r->vault->nisn_encrypted ?? '-';

// Rombel terakhir siswa ini (sudah diurutkan terbaru di controller).
$lastGroup = $r->classGroupStudents->first();
$rombel = optional($lastGroup?->classGroup)->name ?? '-';
$jurusan = optional($lastGroup?->classGroup?->concentration)->name ?? '-';

// Mutasi terbaru (kalau ada) untuk alasan & tanggal keluar.
$latestMutation = $r->mutations->first();

$mutationReasonMap = [
'transfer_out' => 'Pindah',
'dropped_out' => 'Keluar',
'deceased' => 'Meninggal',
];

if ($r->status === 'graduated') {
$exitLabel = 'Lulus';
$exitDate = $lastGroup?->exit_date;
$exitBadgeClass = 'bg-success/10 text-success';
} else {
$exitLabel = $latestMutation
? ($mutationReasonMap[$latestMutation->status] ?? ucfirst(str_replace('_', ' ', $latestMutation->status)))
: ($r->status === 'transferred_out' ? 'Pindah' : 'Keluar');

$exitDate = $latestMutation->mutation_date ?? $lastGroup?->exit_date;

$exitBadgeClass = match ($r->status) {
'transferred_out' => 'bg-blue-500/10 text-blue-600',
'dropped_out' => 'bg-error/10 text-error',
default => 'bg-secondary/10 text-secondary',
};
}
@endphp

<tr id="row-student-history-{{ $r->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-4 py-4">
        <div class="flex items-center gap-3">
            <div @style(["background: {$color}"])
                class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ $initials }}
            </div>
            <div>
                <div class="font-semibold text-foreground text-sm uppercase">{{ $r->name }}</div>
                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                    <span class="inline-block size-1.5 rounded-full {{ $r->gender === 'L' ? 'bg-blue-500' : 'bg-pink-500' }}"></span>
                    {{ $genderLabel }}
                </div>
            </div>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold font-mono">{{ $r->nis ?? '-' }}</span>
            <span class="px-2 py-1 rounded-md bg-warning/10 text-warning-dark text-xs font-bold font-mono">{{ $nisn }}</span>
        </div>
    </td>

    <td class="px-4 py-4">
        <div class="text-sm font-semibold text-foreground">{{ $rombel }}</div>
        <div class="text-xs text-secondary">{{ $jurusan }}</div>
    </td>

    <td class="px-4 py-4">
        <span class="inline-block px-2 py-1 rounded-md text-xs font-bold {{ $exitBadgeClass }}">{{ $exitLabel }}</span>
        <div class="text-xs text-secondary mt-1">
            {{ $exitDate ? \Illuminate\Support\Carbon::parse($exitDate)->translatedFormat('d F Y') : '-' }}
        </div>
    </td>
</tr>