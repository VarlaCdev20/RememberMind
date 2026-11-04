function waitForChartEventos(callback, maxAttempts = 30) {
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
            console.warn('RememberMind: Chart.js could not be loaded in time for Eventos.');
        }
    }, 100);
}

export function moduloEventosClinicos(config) {
    return {
        mesesLabels: config.mesesLabels || [],
        mesesData: config.mesesData || [],
        tipoLabels: config.tipoLabels || [],
        tipoData: config.tipoData || [],
        tipoPercentages: config.tipoPercentages || {},
        tipoTotal: config.tipoTotal || 0,
        chartMesesInstance: null,
        chartTipoInstance: null,

        initModule() {
            this.safeRenderCharts();

            window.addEventListener('render-graficos-eventos', () => {
                setTimeout(() => {
                    this.safeRenderCharts();
                }, 60);
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'eventos' || tab === 'alertas') {
                    setTimeout(() => {
                        this.safeRenderCharts();
                    }, 60);
                }
            });

            window.addEventListener('resize', () => {
                const cMeses = document.getElementById('chartEventosPorMesCanvas');
                const cTipo = document.getElementById('chartEventosPorTipoCanvas');
                if (cMeses && window.Chart) window.Chart.getChart(cMeses)?.resize();
                if (cTipo && window.Chart) window.Chart.getChart(cTipo)?.resize();
            });

            window.addEventListener('remembermind:theme-changed', () => {
                this.safeRenderCharts();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const cMeses = document.getElementById('chartEventosPorMesCanvas');
                        const cTipo = document.getElementById('chartEventosPorTipoCanvas');
                        if ((cMeses && cMeses.offsetParent !== null) || (cTipo && cTipo.offsetParent !== null)) {
                            this.safeRenderCharts();
                        }
                    });
                });
            }
        },

        safeRenderCharts() {
            waitForChartEventos((Chart) => {
                const payloadEl = document.getElementById('eventosChartDataPayload');
                if (payloadEl) {
                    try {
                        if (payloadEl.dataset.meses) {
                            const pm = JSON.parse(payloadEl.dataset.meses);
                            if (pm.labels) this.mesesLabels = pm.labels;
                            if (pm.data) this.mesesData = pm.data;
                        }
                        if (payloadEl.dataset.tipo) {
                            const pt = JSON.parse(payloadEl.dataset.tipo);
                            if (pt.labels) this.tipoLabels = pt.labels;
                            if (pt.data) this.tipoData = pt.data;
                            if (pt.percentages) this.tipoPercentages = pt.percentages;
                            if (pt.total !== undefined) this.tipoTotal = pt.total;
                        }
                    } catch {
                        console.warn('RememberMind: el payload de la gráfica de eventos no pudo actualizarse.');
                        return;
                    }
                }

                this.renderCharts(Chart);
            });
        },

        renderCharts(Chart) {
            Chart = Chart || window.Chart;
            if (!Chart) return;

            // 1. Gráfico Eventos por Mes (Bar Chart)
            const ctxMeses = document.getElementById('chartEventosPorMesCanvas');
            if (ctxMeses && ctxMeses.offsetParent !== null) {
                const api = window.RMCharts;
                if (!api) return;
                const config = api.presets.semantic('bar', 'clinical', this.mesesLabels, this.mesesData);
                config.data.datasets[0].label = 'Eventos';
                config.options.scales.x.grid.display = false;
                config.options.scales.y.beginAtZero = true;
                config.options.scales.y.ticks.precision = 0;
                this.chartMesesInstance = api.init('eventos-por-mes', ctxMeses, config);
            }

            // 2. Gráfico Eventos por Tipo (Donut Chart)
            const ctxTipo = document.getElementById('chartEventosPorTipoCanvas');
            if (ctxTipo && ctxTipo.offsetParent !== null) {
                const api = window.RMCharts;
                if (!api) return;
                const eventPalette = ['clinical', 'neutral', 'care', 'cognitive', 'reference'].map(tone => api.color(tone));
                const config = api.presets.doughnut(this.tipoLabels, this.tipoData,
                    this.tipoLabels.map((_, index) => eventPalette[index % eventPalette.length]));
                config.options.plugins.tooltip.callbacks.label = (ctx) => {
                    const label = ctx.label || '';
                    const val = ctx.raw || 0;
                    const pct = this.tipoPercentages[label] || Math.round((val / (this.tipoTotal || 1)) * 100);
                    return ` ${label}: ${val} (${pct}%)`;
                };
                this.chartTipoInstance = api.init('eventos-por-tipo', ctxTipo, config);
            }
        }
    };
}
window.moduloEventosClinicos = moduloEventosClinicos;
