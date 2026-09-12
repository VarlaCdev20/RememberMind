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

            const config = {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'PA Sistólica (mmHg)',
                            data: data.pa,
                            borderColor: '#7565A8',
                            backgroundColor: 'rgba(117,101,168,0.16)',
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: isDark ? '#1E293B' : '#FFFFFF',
                            pointBorderColor: '#7565A8',
                            tension: 0.38,
                            fill: true,
                            yAxisID: 'yPA',
                        },
                        {
                            label: 'FC (bpm)',
                            data: data.fc,
                            borderColor: '#D9745B',
                            backgroundColor: 'rgba(217,116,91,0.12)',
                            borderWidth: 2,
                            pointRadius: 3.5,
                            pointBackgroundColor: isDark ? '#1E293B' : '#FFFFFF',
                            pointBorderColor: '#D9745B',
                            tension: 0.38,
                            borderDash: [4, 3],
                            yAxisID: 'yPA',
                        },
                        {
                            label: 'SpO₂ (%)',
                            data: data.sat,
                            borderColor: '#4E8CA6',
                            backgroundColor: 'rgba(78,140,166,0.14)',
                            borderWidth: 2.5,
                            pointRadius: 3.5,
                            pointBackgroundColor: isDark ? '#1E293B' : '#FFFFFF',
                            pointBorderColor: '#4E8CA6',
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
                            backgroundColor: isDark ? '#0F172A' : '#1E293B',
                            titleColor: '#F8FAFC',
                            bodyColor: '#F8FAFC',
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
                            ticks: { font: { size: 10 }, color: '#7565A8' },
                        },
                        ySat: {
                            type: 'linear',
                            position: 'right',
                            min: 80,
                            max: 100,
                            grid: { display: false },
                            ticks: {
                                font: { size: 10 },
                                color: '#4E8CA6',
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
