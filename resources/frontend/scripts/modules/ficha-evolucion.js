/** Puntos guardados de PA: dos series, fechas reales y ninguna interpolación ni regla clínica en JS. */
export function evolucionResidenteChart(puntos, chartKey, rango) {
    return {
        error: false,
        unsubscribe: null,
        init() {
            this.$nextTick(() => this.render());
            this.unsubscribe = window.RMCharts?.onThemeChange(() => this.render(), chartKey, this.$el);
        },
        render() {
            if (!puntos.length) return;
            const api = window.RMCharts;
            const canvas = this.$refs.canvas;
            if (!api || !canvas) { this.error = true; return; }
            const fecha = new Intl.DateTimeFormat('es', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: puntos[0].zona });
            const datasets = [
                { label: 'Sistólica · mmHg', campo: 'sistolica', tono: 'clinical', pointStyle: 'circle' },
                { label: 'Diastólica · mmHg', campo: 'diastolica', tono: 'reference', pointStyle: 'triangle' },
            ].map(({ campo, tono, ...serie }) => ({ ...serie,
                data: puntos.filter(p => p[campo] !== null).map(p => ({ x: Date.parse(p.fecha), y: p[campo], codigo: p.codigo, periodo: p.periodo, clasificacion: p.clasificacion })),
                borderColor: api.color(tono), backgroundColor: api.color(tono),
                showLine: false, spanGaps: false, fill: false,
            }));
            const config = api.presets.line([], datasets);
            config.options.parsing = false;
            config.options.scales.x = { ...config.options.scales.x, type: 'linear',
                min: Date.parse(rango.desde), max: Date.parse(rango.hasta),
                title: { display: true, text: 'Fecha de la medición' },
                ticks: { ...config.options.scales.x?.ticks, maxTicksLimit: 4, callback: value => fecha.format(new Date(value)) },
            };
            config.options.scales.y = { ...config.options.scales.y, beginAtZero: false,
                title: { display: true, text: 'mmHg' },
            };
            config.options.plugins.datalabels = { display: false };
            config.options.plugins.tooltip.callbacks = {
                title: items => items.length ? fecha.format(new Date(items[0].raw.x)) : '',
                label: context => `${context.dataset.label}: ${context.raw.y}`,
                afterLabel: context => [`Período ${context.raw.periodo} · ${context.raw.codigo}`, context.raw.clasificacion],
            };
            config.options.plugins.legend.display = true;
            config.options.plugins.legend.labels.usePointStyle = true;
            delete config.options.plugins.legend.labels.pointStyle;
            this.error = !api.init(chartKey, canvas, config);
        },
        destroy() {
            this.unsubscribe?.();
            window.RMCharts?.destroy(chartKey);
        },
    };
}

window.evolucionResidenteChart = evolucionResidenteChart;

/** Abre el original enlazado y conserva la continuidad de lectura por teclado. */
export function evolucionResidente() {
    return {
        abrirOriginal(event) {
            const link = event.target.closest('a[data-original-evolucion]');
            if (!link) return;
            const original = document.getElementById(link.hash.slice(1));
            if (!original) return;
            event.preventDefault();
            this.$refs.cronologia.open = true;
            original.open = true;
            this.$nextTick(() => {
                const summary = original.querySelector('summary');
                summary?.scrollIntoView({ block: 'center' });
                summary?.focus({ preventScroll: true });
            });
        },
    };
}

window.evolucionResidente = evolucionResidente;
