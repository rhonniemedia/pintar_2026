<div id="stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>
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
</div>