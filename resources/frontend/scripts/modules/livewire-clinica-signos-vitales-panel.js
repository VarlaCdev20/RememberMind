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
            });
        },
        initTendencia() {
            const data = rmDatosa9b5f3c2c94b;
            const canvas = document.getElementById('chartTendenciaSV');
            if (!canvas || typeof Chart === 'undefined') return;

            const isDark = window.RMCharts?.isDark() || false;
            const axisTextColor = window.RMCharts ? window.RMCharts.getCss('--rm-chart-axis-text') : '#64748B';
            const gridColor = window.RMCharts ? window.RMCharts.getCss('--rm-chart-grid') : 'rgba(224,212,198,0.35)';
            const blue = window.RMCharts?.getCss('--rm-chart-2') || '#527DAA';
            const blueSoft = window.RMCharts?.getCss('--rm-chart-4') || '#6F92BC';
            const mint = window.RMCharts?.getCss('--rm-chart-1') || '#4F895E';
            const pointSurface = window.RMCharts?.getCss('--rm-surface-raised') || '#F0E7DE';
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
                            backgroundColor: translucent(blue, 0.16),
                            borderWidth: 2.5,
                            pointRadius: 4,
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
                            backgroundColor: translucent(mint, 0.12),
                            borderWidth: 2,
                            pointRadius: 3.5,
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
                            backgroundColor: translucent(blueSoft, 0.14),
                            borderWidth: 2.5,
                            pointRadius: 3.5,
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
                    animation: { duration: 400 },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: { family: 'Inter', size: 10, weight: 'bold' },
                                color: axisTextColor,
                                padding: 10,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            cornerRadius: 8,
                            padding: 10,
                            backgroundColor: window.RMCharts?.getCss('--rm-chart-tooltip-bg') || '#F0E7DE',
                            titleColor: window.RMCharts?.getCss('--rm-chart-tooltip-text') || '#342E2A',
                            bodyColor: window.RMCharts?.getCss('--rm-chart-tooltip-text') || '#342E2A',
                            borderColor: window.RMCharts?.getCss('--rm-chart-tooltip-border') || '#C9BAAC',
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

            if (window.RMCharts) {
                window.RMCharts.init('sv_tendencia', canvas, config, () => this.initTendencia());
            } else {
                new Chart(canvas, config);
            }
        },
        initDistPA() {
            const data = rmDatos2e40af881bbb;
            const canvas = document.getElementById('chartDistPA');
            if (!canvas || typeof Chart === 'undefined') return;

            const palette = window.RMCharts ? window.RMCharts.palette() : ['#4E8CA6', '#5F9271', '#C9913E', '#D9745B', '#A85C73'];
            const colors = [palette[5], palette[2], palette[3], palette[1], palette[6]];

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
                                labels: { boxWidth: 10, font: { family: 'Inter', size: 10, weight: '600' }, padding: 8 }
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

            if (window.RMCharts) {
                window.RMCharts.init('sv_dist_pa', canvas, config, () => this.initDistPA());
            } else {
                new Chart(canvas, config);
            }
        },
    }));
