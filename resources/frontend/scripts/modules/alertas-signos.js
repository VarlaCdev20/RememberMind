const variables = {
    presion: { unidad: 'mmHg', campos: [['presion_sistolica', 'Sistólica', 'circle'], ['presion_diastolica', 'Diastólica', 'triangle']] },
    pulso: { unidad: 'lpm', campos: [['frecuencia_cardiaca', 'Frecuencia cardíaca', 'circle']] },
    respiracion: { unidad: 'rpm', campos: [['frecuencia_respiratoria', 'Frecuencia respiratoria', 'circle']] },
    saturacion: { unidad: '%', campos: [['saturacion_oxigeno', 'Saturación de oxígeno', 'circle']] },
    temperatura: { unidad: '°C', campos: [['temperatura', 'Temperatura', 'circle']] },
    glucemia: { unidad: 'mg/dL', campos: [['glucemia', 'Glucemia', 'circle']] },
};

export function datosSignosAlerta(puntos, variable) {
    const definicion = variables[variable] || variables.presion;
    return { unidad: definicion.unidad, series: definicion.campos.map(([campo, label, pointStyle]) => ({
        label, pointStyle,
        data: puntos.filter(p => Number.isFinite(Date.parse(p.fecha)) && typeof p[campo] === 'number' && Number.isFinite(p[campo]))
            .map(p => ({ x: Date.parse(p.fecha), y: p[campo] })),
    })) };
}

export function rmSignosAlerta(puntos, clave, zona) {
    return {
        variable: Object.keys(variables).find(v => datosSignosAlerta(puntos, v).series.some(s => s.data.length)) || 'presion',
        sinMediciones: false, error: false, unsubscribe: null,
        init() {
            this.$watch('variable', () => this.render());
            this.unsubscribe = window.RMCharts?.onThemeChange(() => this.render(), clave, this.$el);
            this.$nextTick(() => this.render());
        },
        render() {
            const api = window.RMCharts;
            const datos = datosSignosAlerta(puntos, this.variable);
            this.sinMediciones = !datos.series.some(s => s.data.length);
            this.error = false;
            if (this.sinMediciones) { api?.destroy(clave); return; }
            if (!api || !this.$refs.canvas) { this.error = true; return; }
            const fecha = new Intl.DateTimeFormat('es-BO', { dateStyle: 'short', timeStyle: 'short', timeZone: zona });
            const series = datos.series.map((serie, index) => ({ ...serie,
                label: `${serie.label} · ${datos.unidad}`, showLine: false, spanGaps: false, fill: false,
                borderColor: api.color(index ? 'reference' : 'clinical'),
                backgroundColor: api.color(index ? 'reference' : 'clinical'),
            }));
            const config = api.presets.line([], series);
            config.options.parsing = false;
            config.options.scales.x = { ...config.options.scales.x, type: 'linear',
                title: { display: true, text: 'Fecha de la medición' },
                ticks: { ...config.options.scales.x?.ticks, maxTicksLimit: 3, callback: v => fecha.format(new Date(v)) },
            };
            config.options.scales.y = { ...config.options.scales.y, beginAtZero: false,
                title: { display: true, text: datos.unidad },
            };
            config.options.plugins.datalabels = { display: false };
            config.options.plugins.legend = { ...config.options.plugins.legend, display: true,
                labels: { ...config.options.plugins.legend?.labels, usePointStyle: true },
            };
            delete config.options.plugins.legend.labels.pointStyle;
            config.options.plugins.tooltip.callbacks = {
                title: items => items.length ? fecha.format(new Date(items[0].raw.x)) : '',
                label: item => `${item.dataset.label}: ${item.raw.y}`,
            };
            this.error = !api.init(clave, this.$refs.canvas, config);
        },
        destroy() { this.unsubscribe?.(); window.RMCharts?.destroy(clave); },
    };
}

if (typeof window !== 'undefined') window.rmSignosAlerta = rmSignosAlerta;
