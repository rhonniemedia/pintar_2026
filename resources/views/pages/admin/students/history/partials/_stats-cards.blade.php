<div id="history-stats-container" {!! isset($isOob) && $isOob ? 'hx-swap-oob="true"' : '' !!}>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <x-ui.stat-card
            theme="secondary"
            icon="history"
            title="Total Riwayat"
            :value="$totalHistoryStats ?? 0" />

        <x-ui.stat-card
            theme="teal"
            icon="log-in"
            title="Masuk (Pindahan)"
            :value="$transferInStats ?? 0" />

        <x-ui.stat-card
            theme="blue"
            icon="log-out"
            title="Keluar (Pindah)"
            :value="$transferOutStats ?? 0" />

        {{-- Menghitung total gabungan secara langsung di dalam properti value --}}
        <x-ui.stat-card
            theme="error"
            icon="user-x"
            title="DO / Meninggal"
            :value="($droppedOutStats ?? 0) + ($deceasedStats ?? 0)" />

    </div>
</div>