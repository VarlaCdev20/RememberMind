
    Alpine.data('signosVitalesCharts', () => ({
        charts: {},
        init() {
            this.$nextTick(() => {
                this.initTendencia();
                this.initDistPA();
            });

            $wire.$watch('chartTendencia7d', () => {
                if (this.charts.tendencia) { this.charts.tendencia.destroy(); }
                this.initTendencia();
            });
            $wire.$watch('chartDistPA', () => {
                if (this.charts.distPA) { this.charts.distPA.destroy(); }
                this.initDistPA();
            });
        },
        initTendencia() {
            const data = rmDatosa9b5f3c2c94b;
            const ctx = document.getElementById('chartTendenciaSV');
            if (!ctx) return;
            this.charts.tendencia = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'PA Sistólica (mmHg)',
                            data: data.pa,
                            borderColor: '#9B8AC7',
                            backgroundColor: 'rgba(155,138,199,0.12)',
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#9B8AC7',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'yPA',
                        },
                        {
                            label: 'FC (bpm)',
                            data: data.fc,
                            borderColor: '#D9795F',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#D9795F',
                            tension: 0.4,
                            borderDash: [5, 3],
                            yAxisID: 'yPA',
                        },
                        {
                            label: 'SpO₂ (%)',
                            data: data.sat,
                            borderColor: '#5B7C9D',
                            backgroundColor: 'rgba(91,124,157,0.06)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#5B7C9D',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'ySat',
                        },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 10 } },
                        tooltip: { mode: 'index', intersect: false },
                        datalabels: { display: false },
                    },
                    scales: {
                        yPA: {
                            type: 'linear', position: 'left',
                            min: 50, max: 200,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 10 }, color: '#9B8AC7' },
                        },
                        ySat: {
                            type: 'linear', position: 'right',
                            min: 80, max: 100,
                            grid: { display: false },
                            ticks: { font: { size: 10 }, color: '#5B7C9D',
                                     callback: (v) => v + '%' },
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    }
                }
            });
        },
        initDistPA() {
            const data = rmDatos2e40af881bbb;
            const ctx = document.getElementById('chartDistPA');
            if (!ctx) return;
            const colors = ['#5B7C9D','#3F7D5A','#E9A05F','#D9795F','#C9654E'];
            this.charts.distPA = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#F4EEE7',
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } },
                        datalabels: {
                            display: true,
                            color: '#fff',
                            font: { size: 10, weight: 'bold' },
                            formatter: (val, ctx) => val > 0 ? val : '',
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.label}: ${ctx.parsed} pacientes`
                            }
                        }
                    },
                    cutout: '58%',
                }
            });
        },
    }));
    