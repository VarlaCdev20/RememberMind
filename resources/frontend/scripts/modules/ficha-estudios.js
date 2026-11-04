function waitForChartEstudios(callback, maxAttempts = 30) {
    if (typeof window.Chart !== 'undefined') {
        callback(window.Chart);
        return;
    }
    let attempts = 0;
    const interval = setInterval(() => {
        attempts++;
        if (typeof window.Chart !== 'undefined') {
            clearInterval(interval);
            callback(window.Chart);
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.warn('RememberMind: Chart.js could not be loaded in time for Estudios.');
        }
    }, 100);
}

export function moduloResultadosEstudios(config) {
    return {
        labels: config.labels || [],
        data: config.data || [],
        fechas: config.fechas || [],
        unidad: config.unidad || '',
        nombre: config.nombre || '',
        rangoMin: config.rangoMin || 0,
        rangoMax: config.rangoMax || 0,
        parametro: config.parametro || 'glucosa',
        chartInstance: null,

        initCharts() {
            this.safeRenderChart();

            window.addEventListener('render-graficos-estudios', () => {
                setTimeout(() => {
                    this.safeRenderChart();
                }, 60);
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'estudios' || tab === 'historial' || tab === 'resultados') {
                    setTimeout(() => {
                        this.safeRenderChart();
                    }, 60);
                }
            });

            window.addEventListener('resize', () => {
                const ctx = document.getElementById('chartEvolucionEstudiosCanvas');
                if (ctx && window.Chart) {
                    window.Chart.getChart(ctx)?.resize();
                }
            });

            window.addEventListener('remembermind:theme-changed', () => {
                this.safeRenderChart();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const ctx = document.getElementById('chartEvolucionEstudiosCanvas');
                        if (ctx && ctx.offsetParent !== null) {
                            this.safeRenderChart();
                        }
                    });
                });
            }
        },

        refreshCharts() {
            setTimeout(() => {
                this.safeRenderChart();
            }, 60);
        },

        safeRenderChart() {
            waitForChartEstudios((Chart) => {
                // Sincronizar datos si el payload fue actualizado por Livewire
                const payloadEl = document.getElementById('estudiosChartDataPayload');
                if (payloadEl && payloadEl.dataset.chart) {
                    try {
                        const payload = JSON.parse(payloadEl.dataset.chart);
                        if (payload.labels) this.labels = payload.labels;
                        if (payload.data) this.data = payload.data;
                        if (payload.fechas) this.fechas = payload.fechas;
                        if (payload.unidad) this.unidad = payload.unidad;
                        if (payload.nombre) this.nombre = payload.nombre;
                        if (payload.rango_min !== undefined) this.rangoMin = Number(payload.rango_min);
                        if (payload.rango_max !== undefined) this.rangoMax = Number(payload.rango_max);
                    } catch {
                        console.warn('RememberMind: el payload de la gráfica de estudios no pudo actualizarse.');
                        return;
                    }
                }

                this.renderChart(Chart);
            });
        },

        renderChart(Chart) {
            Chart = Chart || window.Chart;
            if (!Chart) return;

            const ctx = document.getElementById('chartEvolucionEstudiosCanvas');
            if (!ctx || ctx.offsetParent === null) return;

            const api = window.RMCharts;
            if (!api) return;
            const config = api.presets.area(this.labels, [{
                label: this.nombre,
                data: this.data,
                borderColor: api.color('clinical'),
                backgroundColor: api.hexToRgba(api.color('clinical'), api.number('--rm-line-area-opacity', .12)),
            }]);
            config.options.scales.x.grid.display = false;
            config.options.scales.y.beginAtZero = false;
            config.options.plugins.tooltip.callbacks = {
                label: (context) => {
                    const fecha = this.fechas[context.dataIndex] || '';
                    return ` ${this.nombre}: ${context.parsed.y} ${this.unidad} (${fecha})`;
                }
            };
            this.chartInstance = api.init('evolucion-estudios', ctx, config);
        }
    };
}
window.moduloResultadosEstudios = moduloResultadosEstudios;
