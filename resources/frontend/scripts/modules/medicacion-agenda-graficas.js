/** Agenda filtrada, de solo lectura. RMCharts conserva instancias al actualizar Livewire. */
export function medicacionAgendaGraficas() {
    return {
        init() {
            this.observer = new MutationObserver(() => this.$nextTick(() => this.render()));
            this.observer.observe(this.$el, { attributes: true, attributeFilter: ['data-agenda'], childList: true });
            this.unsubscribeTheme = window.RMCharts.onThemeChange(() => this.render(), 'medication-agenda', this.$el);
            this.$nextTick(() => this.render());
        },
        render() {
            const api = window.RMCharts;
            const data = JSON.parse(this.$el.dataset.agenda);
            if (!data.total) {
                api.destroy('medication-agenda-states');
                api.destroy('medication-agenda-hours');
                return;
            }
            const states = data.estados.filter(item => item.cantidad > 0);
            const colors = api.semanticColors();
            const donut = api.presets.doughnut(states.map(item => item.label), states.map(item => item.cantidad), states.map(item => colors[item.tono]));
            donut.options.cutout = '76%';
            donut.options.layout = { padding: 0 };
            donut.data.datasets[0].backgroundColor = states.map(item => api.hexToRgba(colors[item.tono], .82));
            donut.data.datasets[0].borderColor = states.map(item => colors[item.tono]);
            api.init('medication-agenda-states', this.$el.querySelector('#medication-agenda-states'), donut);

            const base = api.baseOptions();
            const bars = api.presets.bar(data.horarios.map(item => item.label), [{
                label: 'Dosis programadas', data: data.horarios.map(item => item.cantidad),
                backgroundColor: colors.info, borderColor: colors.info,
            }], {
                scales: {
                    x: { ...base.scales.x, grid: { display: false }, ticks: { ...base.scales.x.ticks, font: { ...base.scales.x.ticks.font, size: 12 }, maxRotation: 0, maxTicksLimit: 8 } },
                    y: { ...base.scales.y, beginAtZero: true, ticks: { ...base.scales.y.ticks, font: { ...base.scales.y.ticks.font, size: 12 }, precision: 0 },
                        title: { display: true, text: 'Dosis', color: api.getCss('--rm-chart-axis-title') } },
                },
            });
            bars.data.datasets[0].maxBarThickness = 28;
            bars.data.datasets[0].backgroundColor = api.hexToRgba(colors.info, .82);
            bars.data.datasets[0].borderColor = colors.info;
            api.init('medication-agenda-hours', this.$el.querySelector('#medication-agenda-hours'), bars);
        },
        destroy() {
            this.observer?.disconnect();
            this.unsubscribeTheme?.();
            window.RMCharts.destroy('medication-agenda-states');
            window.RMCharts.destroy('medication-agenda-hours');
        },
    };
}

if (typeof window !== 'undefined') window.rmMedicacionAgendaGraficas = medicacionAgendaGraficas;
