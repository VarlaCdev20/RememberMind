// Construcción y actualización de la gráfica principal.
export const graficaSignos = {
        renderMainChart() {
            const canvas = document.getElementById('signosVitalesMainCanvas');
            if (!canvas) return;

            if (canvas.offsetParent === null) return;

            const self = this;
            const isDark = document.documentElement.classList.contains('dark');
            const chartToken = (name) => window.RMCharts?.getCss(name);
            const chartGrid = chartToken('--rm-chart-grid-strong');
            const datasets = this.getDatasetsForMetrica(this.metricaActiva, isDark);
            const scalesConfig = this.getScalesForMetrica(this.metricaActiva, isDark);

            // Plugin: Crosshair vertical al hacer hover
            const verticalCrosshairPlugin = {
                id: 'verticalCrosshair',
                afterDraw: (chart) => {
                    if (chart.tooltip?._active && chart.tooltip._active.length) {
                        const activePoint = chart.tooltip._active[0];
                        const { ctx, chartArea } = chart;
                        const x = activePoint.element.x;
                        const topY = chartArea.top;
                        const bottomY = chartArea.bottom;

                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(x, topY);
                        ctx.lineTo(x, bottomY);
                        ctx.setLineDash([4, 4]);
                        ctx.lineWidth = 1.2;
                        ctx.strokeStyle = chartGrid;
                        ctx.stroke();
                        ctx.restore();
                    }
                }
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },

                    // =========================================================
                    // DATALABELS: NÚMEROS VISIBLES DIRECTAMENTE SOBRE CADA PUNTO
                    // =========================================================
                    datalabels: {
                        display: (context) => {
                            const val = context.dataset.data[context.dataIndex];
                            return val !== null && val !== undefined && val !== '';
                        },
                        align: (context) => {
                            if (self.metricaActiva === 'PA') {
                                return context.datasetIndex === 0 ? 'top' : 'bottom';
                            }
                            return 'top';
                        },
                        anchor: (context) => {
                            if (self.metricaActiva === 'PA') {
                                return context.datasetIndex === 0 ? 'end' : 'start';
                            }
                            return 'end';
                        },
                        offset: 5,
                        backgroundColor: chartToken('--rm-chart-tooltip-bg'),
                        borderColor: (context) => {
                            return context.dataset.borderColor || chartToken('--rm-chart-2');
                        },
                        borderWidth: 1.2,
                        borderRadius: 5,
                        padding: { top: 2, bottom: 2, left: 5, right: 5 },
                        color: (context) => {
                            return chartToken('--rm-chart-tooltip-text');
                        },
                        font: {
                            size: 10,
                            weight: 'bold',
                            family: 'Inter, system-ui, sans-serif'
                        },
                        formatter: (value) => {
                            if (value === null || value === undefined) return '';
                            if (self.metricaActiva === 'TEMP') {
                                return Number(value).toFixed(1) + '°';
                            }
                            if (self.metricaActiva === 'SPO2') {
                                return value + '%';
                            }
                            return value;
                        }
                    },

                    tooltip: {
                        enabled: true,
                        backgroundColor: chartToken('--rm-chart-tooltip-bg'),
                        titleColor: chartToken('--rm-chart-tooltip-text'),
                        bodyColor: chartToken('--rm-chart-tooltip-text'),
                        borderColor: chartToken('--rm-chart-tooltip-border'),
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            title: (items) => {
                                const idx = items[0]?.dataIndex ?? 0;
                                return self.labelsLargas[idx] || self.labels[idx] || '';
                            },
                            label: (context) => {
                                const label = context.dataset.label || '';
                                const val = context.parsed.y;
                                if (val === null || val === undefined) return null;
                                const unit = self.metricasInfo[self.metricaActiva]?.unidad || '';
                                return ` ${label}: ${val} ${unit}`;
                            },
                            afterBody: (items) => {
                                const lectura = self.interpretaciones[items[0]?.dataIndex]?.[self.metricaActiva];
                                return lectura ? [` Estado: ${lectura.label}`, lectura.message] : '';
                            }
                        }
                    }
                },
                scales: scalesConfig
            };

            let plotLabels = this.labels.length ? [...this.labels] : ['Sin datos'];

            this.mainChart = window.RMCharts.init('signos-vitales-principal', canvas, {
                type: 'line',
                data: {
                    labels: plotLabels,
                    datasets: datasets
                },
                options: options,
                plugins: [verticalCrosshairPlugin]
            });
        },

        updateMainChart() {
            const canvas = document.getElementById('signosVitalesMainCanvas');
            if (!canvas) return;

            const chart = Chart.getChart(canvas);
            if (!chart) {
                this.renderMainChart();
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const datasets = this.getDatasetsForMetrica(this.metricaActiva, isDark);
            const scalesConfig = this.getScalesForMetrica(this.metricaActiva, isDark);

            let plotLabels = this.labels.length ? [...this.labels] : ['Sin datos'];

            chart.data.labels = plotLabels;
            chart.data.datasets = datasets;
            chart.options.scales = scalesConfig;
            chart.update();
        },

        getScalesForMetrica(metrica, isDark) {
            const gridColor = window.RMCharts?.getCss('--rm-chart-grid') || 'rgba(80, 71, 65, 0.12)';
            const tickColor = window.RMCharts?.getCss('--rm-chart-axis-text') || '#6B5F57';

            let yLimits = { suggestedMin: 50, suggestedMax: 160 };
            if (metrica === 'FC') yLimits = { suggestedMin: 50, suggestedMax: 110 };
            else if (metrica === 'SPO2') yLimits = { suggestedMin: 85, suggestedMax: 100 };
            else if (metrica === 'TEMP') yLimits = { suggestedMin: 35.0, suggestedMax: 39.0 };
            else if (metrica === 'FR') yLimits = { suggestedMin: 10, suggestedMax: 26 };
            else if (metrica === 'DOLOR') yLimits = { suggestedMin: 0, suggestedMax: 10 };
            else if (metrica === 'GLUCOSA') yLimits = { suggestedMin: 60, suggestedMax: 180 };
            else if (metrica === 'PESO') yLimits = { suggestedMin: 50, suggestedMax: 90 };

            return {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: tickColor,
                        font: { size: 11, family: 'Inter, sans-serif' },
                        maxRotation: 0,
                    }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        color: tickColor,
                        font: { size: 11, family: 'Inter, sans-serif' },
                        callback: (val) => {
                            if (metrica === 'TEMP') return val.toFixed(1) + '°';
                            if (metrica === 'SPO2') return val + '%';
                            return val;
                        }
                    },
                    ...yLimits
                }
            };
        },


};
