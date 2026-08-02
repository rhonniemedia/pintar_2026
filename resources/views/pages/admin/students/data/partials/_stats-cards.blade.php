<div id="stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>

    @if(isset($isFloating) && $isFloating)
    {{-- TAMPILAN UNTUK SISWA MENGAMBANG (3 CARD) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-ui.stat-card
            theme="orange"
            icon="users"
            title="Total Siswa Mengambang"
            :value="$totalFloating ?? 0" />

        <x-ui.stat-card
            theme="blue"
            icon="user"
            title="Laki-laki"
            :value="$maleFloating ?? 0" />

        <x-ui.stat-card
            theme="purple"
            icon="user"
            title="Perempuan"
            :value="$femaleFloating ?? 0" />
    </div>
    @else
    {{-- TAMPILAN UNTUK SISWA AKTIF (4 CARD ASLI) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card
            theme="success"
            icon="check-circle"
            title="Total Siswa Aktif"
            :value="$activeStats ?? 0" />

        <x-ui.stat-card
            theme="purple"
            icon="users"
            title="Siswa Kelas XII"
            :value="$grade12Stats ?? 0" />

        <x-ui.stat-card
            theme="blue"
            icon="users"
            title="Siswa Kelas XI"
            :value="$grade11Stats ?? 0" />

        <x-ui.stat-card
            theme="orange"
            icon="users"
            title="Siswa Kelas X"
            :value="$grade10Stats ?? 0" />
    </div>
    @endif

</div>