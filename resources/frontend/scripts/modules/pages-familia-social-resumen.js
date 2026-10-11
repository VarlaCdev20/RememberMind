(() => {
    const payload = rmDatosff6fbf562949;

    const renderCharts = () => {
        const api = window.RMCharts;
        if (!api || !window.Chart) return;

        const renderDonut = (id, key, values, tones) => {
            const canvas = document.getElementById(id);
            if (!canvas || !values?.data?.some(value => Number(value) > 0)) return;
            const colors = values.labels.map((_, index) => api.color(tones[index % tones.length]));
            const config = api.presets.doughnut(values.labels, values.data, colors);
            config.options.plugins.legend = {
                display: true,
                position: 'bottom',
                labels: api.baseOptions().plugins.legend.labels,
            };
            api.init(key, canvas, config);
        };

        renderDonut('familiaRedChart', 'familia-red', payload.red, ['care', 'neutral']);
        renderDonut('familiaFichaChart', 'familia-ficha', payload.ficha, ['care', 'clinical', 'neutral']);

        const visitsCanvas = document.getElementById('familiaVisitasChart');
        if (visitsCanvas && payload.visitas?.data?.some(value => Number(value) > 0)) {
            const config = api.presets.semantic('bar', 'care', payload.visitas.labels, payload.visitas.data);
            config.data.datasets[0].label = 'Visitas';
            config.options.scales.y.beginAtZero = true;
            config.options.scales.y.ticks.precision = 0;
            config.options.scales.x.grid.display = false;
            api.init('familia-visitas', visitsCanvas, config);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderCharts, { once: true });
    } else {
        renderCharts();
    }
    window.RMCharts?.onThemeChange(renderCharts, 'familia-social-resumen');
})();
