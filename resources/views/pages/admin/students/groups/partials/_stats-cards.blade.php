<div id="stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Card 1: Total Rombel --}}
        <x-ui.stat-card
            theme="success"
            icon="layout-grid"
            title="Total Rombel"
            :value="$totalStats ?? 0" />

        {{-- Card 2: Rombel Kelas XII --}}
        <x-ui.stat-card
            theme="purple"
            icon="book-open"
            title="Rombel Kelas XII"
            :value="$grade12Stats ?? 0" />

        {{-- Card 3: Rombel Kelas XI --}}
        <x-ui.stat-card
            theme="blue"
            icon="book-open"
            title="Rombel Kelas XI"
            :value="$grade11Stats ?? 0" />

        {{-- Card 4: Rombel Kelas X --}}
        <x-ui.stat-card
            theme="orange"
            icon="book-open"
            title="Rombel Kelas X"
            :value="$grade10Stats ?? 0" />

    </div>
</div>