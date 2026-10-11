    Alpine.data('signosVitalesCharts', () => ({
        init() {
            this.$nextTick(() => {
                this.initTendencia();
                this.initDistPA();
            });

            $wire.$watch('chartTendencia7d', () => {
                this.initTendencia();
            });
            $wire.$watch('chartDistPA', () => {
                this.initDistPA();
            });

            window.RMCharts?.onThemeChange(() => {
                this.initTendencia();
                this.initDistPA();
            }, 'clinica-signos-vitales-panel');
        },
        initTendencia() {
            const data = rmDatosa9b5f3c2c94b;
            const canvas = document.getElementById('chartTendenciaSV');
            if (!canvas || typeof Chart === 'undefined') return;

            const axisTextColor = window.RMCharts.getCss('--rm-chart-axis-text');
            const gridColor = window.RMCharts.getCss('--rm-chart-grid');
            const blue = window.RMCharts.color('clinical');
            const blueSoft = window.RMCharts.color('care');
            const mint = window.RMCharts.color('reference');
            const pointSurface = window.RMCharts.getCss('--rm-chart-surface-bg');
            const translucent = (color, alpha) => window.RMCharts?.hexToRgba(color, alpha) || color;

            const config = {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'PA Sistólica (mmHg)',
                            data: data.pa,
                            borderColor: blue,
                            backgroundColor: translucent(blue, window.RMCharts.number('--rm-line-area-opacity', .12)),
                            borderWidth: window.RMCharts.number('--rm-line-stroke-width', 3),
                            pointRadius: window.RMCharts.number('--rm-line-dot-size', 5) / 2,
                            pointHoverRadius: 6,
                            pointBackgroundColor: pointSurface,
                            pointBorderColor: blue,
                            tension: 0.38,
                            fill: true,
                            yAxisID: 'yPA',
                        },
                        {
                            label: 'FC (bpm)',
                            data: data.fc,
                            borderColor: mint,
                            backgroundColor: translucent(mint, window.RMCharts.number('--rm-line-area-secondary-opacity', .09)),
                            borderWidth: window.RMCharts.number('--rm-line-stroke-width', 3),
                            pointRadius: window.RMCharts.number('--rm-line-dot-size', 5) / 2,
                            pointBackgroundColor: pointSurface,
                            pointBorderColor: mint,
                            tension: 0.38,
                            borderDash: [4, 3],
                            yAxisID: 'yPA',
                        },
                        {
                            label: 'SpO₂ (%)',
                            data: data.sat,
                            borderColor: blueSoft,
                            backgroundColor: translucent(blueSoft, window.RMCharts.number('--rm-line-area-secondary-opacity', .09)),
                            borderWidth: window.RMCharts.number('--rm-line-stroke-width', 3),
                            pointRadius: window.RMCharts.number('--rm-line-dot-size', 5) / 2,
                            pointBackgroundColor: pointSurface,
                            pointBorderColor: blueSoft,
                            tension: 0.38,
                            fill: true,
                            yAxisID: 'ySat',
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: { family: window.RMCharts.getCss('--rm-chart-font-family'), size: window.RMCharts.number('--rm-chart-legend-size', 12), weight: '600' },
                                color: axisTextColor,
                                padding: 10,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            cornerRadius: window.RMCharts.number('--rm-tooltip-radius', 14),
                            padding: 10,
                            backgroundColor: window.RMCharts.getCss('--rm-chart-tooltip-bg'),
                            titleColor: window.RMCharts.getCss('--rm-chart-tooltip-text'),
                            bodyColor: window.RMCharts.getCss('--rm-chart-tooltip-text'),
                            borderColor: window.RMCharts.getCss('--rm-chart-tooltip-border'),
                            borderWidth: 1,
                        },
                        datalabels: { display: false },
                    },
                    scales: {
                        yPA: {
                            type: 'linear',
                            position: 'left',
                            min: 50,
                            max: 200,
                            grid: { color: gridColor, drawBorder: false },
                            ticks: { font: { size: 10 }, color: blue },
                        },
                        ySat: {
                            type: 'linear',
                            position: 'right',
                            min: 80,
                            max: 100,
                            grid: { display: false },
                            ticks: {
                                font: { size: 10 },
                                color: blueSoft,
                                callback: (v) => v + '%'
                            },
                        },
                        x: {
                            grid: { color: gridColor, drawBorder: false },
                            ticks: { font: { size: 10 }, color: axisTextColor }
                        },
                    }
                }
            };

            window.RMCharts.init('sv_tendencia', canvas, config, () => this.initTendencia());
        },
        initDistPA() {
            const data = rmDatos2e40af881bbb;
            const canvas = document.getElementById('chartDistPA');
            if (!canvas || typeof Chart === 'undefined') return;

            const colors = [window.RMCharts.color('clinical'), window.RMCharts.color('care'), window.RMCharts.color('neutral'), window.RMCharts.color('alert'), window.RMCharts.color('alert')];

            const config = window.RMCharts && window.RMCharts.presets
                ? window.RMCharts.presets.doughnut(
                    data.labels,
                    data.values,
                    colors,
                    {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { family: window.RMCharts.getCss('--rm-chart-font-family'), size: window.RMCharts.number('--rm-chart-legend-size', 12), weight: '600' }, padding: 8 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ` ${ctx.label}: ${ctx.parsed} pacientes`
                                }
                            }
                        }
                    }
                )
                : {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{ data: data.values, backgroundColor: colors }]
                    }
                };

            window.RMCharts.init('sv_dist_pa', canvas, config, () => this.initDistPA());
        },
    }));
