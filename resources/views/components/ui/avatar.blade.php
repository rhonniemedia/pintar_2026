@props([
'name' => 'User',
'gender' => null,
'index' => null
])

@php
// 1. Ekstrak 2 huruf pertama untuk inisial
$initials = strtoupper(substr(trim($name ?? 'U'), 0, 2));

// 2. Daftar variasi warna gradien (Memadukan palet Anda dengan tambahan Merah Terang #D62828)
$colors = [
'linear-gradient(135deg, #6F1D1B, #D62828)', // Merah Gelap ke Merah Terang (Vibrant & Pop)
'linear-gradient(135deg, #432818, #99582A)', // Coklat Tua ke Coklat Karat (Earthy)
'linear-gradient(135deg, #99582A, #BB9457)', // Coklat Karat ke Emas/Coklat Muda
'linear-gradient(135deg, #BB9457, #FFE6A7)', // Emas ke Krem (Sangat terang)
'linear-gradient(135deg, #D62828, #BB9457)' // Merah Terang ke Emas (Elegan & Menonjol)
];

// 3. Menentukan urutan warna
$colorIndex = $index !== null ? ($index % 5) : (crc32($name) % 5);
$color = $colors[$colorIndex];

// 4. Penyesuaian Kontras Teks
// Karena gradien ke-4 (index 3) didominasi warna terang, kita gunakan teks gelap.
$textColorClass = $colorIndex === 3 ? 'text-gray-900' : 'text-white';

// 5. Konfigurasi Lencana Gender
$isMale = $gender === 'L';
$genderTitle = $isMale ? 'Laki-laki' : ($gender === 'P' ? 'Perempuan' : 'Tidak diketahui');
$badgeBg = $isMale ? 'bg-blue-500' : 'bg-pink-500';
$badgeIcon = $isMale ? 'mars' : 'venus';
@endphp

<div class="relative shrink-0">
    {{-- Lingkaran Avatar --}}
    <div @style(["background: {$color}"])
        class="h-10 w-10 rounded-full flex items-center justify-center {{ $textColorClass }} text-sm font-bold shadow-sm">
        {{ $initials }}
    </div>

    {{-- Lencana Gender --}}
    @if(in_array($gender, ['L', 'P']))
    <span title="{{ $genderTitle }}"
        class="absolute -bottom-0.5 -right-0.5 flex items-center justify-center size-4 rounded-full border-2 border-white {{ $badgeBg }}">
        <i data-lucide="{{ $badgeIcon }}" class="size-2.5 text-white pointer-events-none"></i>
    </span>
    @endif
</div>