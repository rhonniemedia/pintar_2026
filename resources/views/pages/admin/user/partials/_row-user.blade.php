@php
$init = strtoupper(substr($u->name, 0, 2));
$colors = ['linear-gradient(135deg,#FF1443,#FF6B6B)', 'linear-gradient(135deg,#3B82F6,#93C5FD)', 'linear-gradient(135deg,#F59E0B,#FCD34D)', 'linear-gradient(135deg,#10B981,#34D399)'];
$color = $colors[isset($loop) ? ($loop->index % 4) : 0];

$roleBadges = [
'superadmin' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => 'shield-alert'],
'admin' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'icon' => 'shield'],
'verifikator' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'file-check'],
'observator' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-300', 'icon' => 'clipboard-list'],
'user' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'border' => 'border-gray-200', 'icon' => 'user'],
];

$badge = $roleBadges[$u->role] ?? $roleBadges['user'];

$alpineData = [
'id' => $u->id,
'name' => $u->name,
'email' => $u->email,
'role' => $u->role,
];
@endphp

<tr id="user-row-{{ $u->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">
    <td class="px-4 py-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0" :style="'background: {{ $color }}'">{{ $init }}</div>
            <div>
                <div class="font-semibold text-foreground text-sm">{{ $u->name }}</div>
                <div class="text-xs text-secondary">{{ $u->email }}</div>
            </div>
        </div>
    </td>

    <td class="px-4 py-4">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
            <i data-lucide="{{ $badge['icon'] }}" class="size-3"></i>
            {{ ucwords($u->role) }}
        </span>
    </td>

    <td class="px-4 py-4">
        <div class="text-sm font-medium text-foreground">
            {{ $u->created_at ? $u->created_at->translatedFormat('d M Y') : '-' }}
        </div>
        @if($u->created_at)
        <div class="flex items-center gap-1.5 mt-1 text-xs text-secondary">
            <i data-lucide="clock" class="size-3"></i>
            <span>{{ $u->created_at->diffForHumans() }}</span>
        </div>
        @else
        <div class="text-xs text-secondary mt-1">-</div>
        @endif
    </td>

    <td class="px-4 py-4 text-left">
        <div class="flex items-center gap-2">
            {{-- MENGGUNAKAN TANDA KUTIP TUNGGAL AGAR JSON TIDAK BENTROK --}}
            <button @click='openEdit(@json($alpineData))' title="Edit Pengguna" class="inline-flex items-center justify-center p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors cursor-pointer border border-blue-200">
                <i data-lucide="edit" class="size-4"></i>
            </button>
            <button @click='confirmDelete(@json($alpineData))' title="Hapus Pengguna" class="inline-flex items-center justify-center p-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 transition-colors cursor-pointer border border-red-200">
                <i data-lucide="trash-2" class="size-4"></i>
            </button>
        </div>
    </td>
</tr>