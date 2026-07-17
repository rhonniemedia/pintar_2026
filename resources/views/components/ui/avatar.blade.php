@props([
'name' => 'User',
'gender' => null, // Diharapkan 'L' atau 'P'
'index' => null // Menerima nilai $loop->index untuk urutan warna
])

@php
// 1. Ekstrak 2 huruf pertama untuk inisial
$initials = strtoupper(substr(trim($name ?? 'U'), 0, 2));

// 2. Daftar variasi warna gradien persis seperti desain asli Anda
$colors = [
'linear-gradient(135deg,#FF1443,#FF6B6B)', // Merah/Pink
'linear-gradient(135deg,#3B82F6,#93C5FD)', // Biru
'linear-gradient(135deg,#F59E0B,#FCD34D)', // Kuning/Orange
'linear-gradient(135deg,#8B5CF6,#A78BFA)' // Ungu
];

// 3. Menentukan urutan warna
// Jika berada di dalam tabel (memiliki $loop->index), gunakan urutan index.
// Jika tidak (misal di halaman profil), gunakan hash dari nama agar warnanya selalu sama untuk siswa tersebut.
$colorIndex = $index !== null ? ($index % 4) : (crc32($name) % 4);
$color = $colors[$colorIndex];

// 4. Konfigurasi Lencana Gender
$isMale = $gender === 'L';
$genderTitle = $isMale ? 'Laki-laki' : ($gender === 'P' ? 'Perempuan' : 'Tidak diketahui');
$badgeBg = $isMale ? 'bg-blue-500' : 'bg-pink-500';
$badgeIcon = $isMale ? 'mars' : 'venus';
@endphp

<div class="relative shrink-0">
    {{-- Lingkaran Avatar --}}
    <div @style(["background: {$color}"])
        class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-sm">
        {{ $initials }}
    </div>

    {{-- Lencana Gender (hanya tampil jika memiliki gender L atau P) --}}
    @if(in_array($gender, ['L', 'P']))
    <span title="{{ $genderTitle }}"
        class="absolute -bottom-0.5 -right-0.5 flex items-center justify-center size-4 rounded-full border-2 border-white {{ $badgeBg }}">
        <i data-lucide="{{ $badgeIcon }}" class="size-2.5 text-white pointer-events-none"></i>
    </span>
    @endif
</div>