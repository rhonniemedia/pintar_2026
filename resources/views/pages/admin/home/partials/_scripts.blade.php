@push('scripts')
{{--
    Data dari server ditaruh di <script type="application/json"> terpisah
    (bukan langsung di-inline di tengah kode JS). Ini sengaja dilakukan agar
    directive Blade (@json) tidak bercampur dengan kode JS di file yang sama —
    percampuran itu yang membuat editor (VSCode) salah membaca `@json(...)`
    sebagai sintaks decorator JS/TS, sehingga parser "nyasar" dan baris-baris
    setelahnya (termasuk `Chart`) ikut terdeteksi sebagai error palsu.
--}}
<script type="application/json" id="gender-chart-data">
    @json($genderChartData)
</script>
<script type="application/json" id="mutation-trend-data">
    @json($mutationTrendData)
</script>

<script>
    /**
     * Entry point Alpine.js untuk halaman dashboard akademik.
     * Pertahankan jika dipakai di `x-data` halaman induk — hapus jika tidak.
     */
    function akademikDashboardApp() {
        return {};
    }

    (function() {
        'use strict';

        const CHART_COLORS = {
            masuk: '#10B981',
            keluar: '#EF4444',
        };

        function readJsonData(elementId) {
            const el = document.getElementById(elementId);
            return el ? JSON.parse(el.textContent) : null;
        }

        /** Diagram donat distribusi gender. */
        function renderDonutChart(genderData) {
            const canvas = document.getElementById('donutChart');

            if (!canvas || !window.Chart || !genderData || genderData.length === 0) {
                return;
            }

            // Nilai 0 diganti sangat kecil agar slice tetap ter-render tipis.
            const values = genderData.map((d) => (d.count > 0 ? d.count : 0.0001));

            new window.Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: genderData.map((d) => d.label),
                    datasets: [{
                        data: values,
                        backgroundColor: genderData.map((d) => d.color),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4,
                    }],
                },
                options: {
                    responsive: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                },
            });
        }

        /** Diagram batang tren mutasi (masuk vs keluar). */
        function renderMutationTrendChart(mutationTrendData) {
            const canvas = document.getElementById('mutationTrendChart');

            if (!canvas || !window.Chart || !mutationTrendData) {
                return;
            }

            new window.Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: mutationTrendData.labels,
                    datasets: [{
                            label: 'Masuk',
                            data: mutationTrendData.masuk,
                            backgroundColor: CHART_COLORS.masuk,
                            barThickness: 28,
                        },
                        {
                            label: 'Keluar',
                            data: mutationTrendData.keluar,
                            backgroundColor: CHART_COLORS.keluar,
                            barThickness: 28,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.04)',
                            },
                        },
                    },
                },
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }

            renderDonutChart(readJsonData('gender-chart-data'));
            renderMutationTrendChart(readJsonData('mutation-trend-data'));
        });
    })();
</script>
@endpush