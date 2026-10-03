(() => {
    const signosLabels = @json($svLabels);
    const sistolica = @json($svSistolica);
    const diastolica = @json($svDiastolica);
    const pulso = @json($svPulso);
    const medLabels = @json($medLabels);
    const medCounts = @json($medCounts);
    const barthelLabels = @json($barthelLabels);
    const barthelValues = @json($barthelValues);
    const evalLabels = @json($evalLabels);
    const evalPuntajes = @json($evalPuntajes);
    const evalMaximos = @json($evalMaximos);
    const suffix = @json($idAdulto);
    let observer;

    const render = () => {
        const api = window.RMCharts;
        if (!api || !window.Chart) return;
        const visible = document.getElementById('reportes-charts-section-' + suffix);
        if (!visible || visible.offsetParent === null) return;

        const signsCanvas = document.getElementById('chartSignos' + suffix);
        if (signsCanvas && signosLabels.length) {
            const config = api.presets.area(signosLabels, [
                { label: 'Sistólica', data: sistolica, borderColor: api.color('clinical'),
                    backgroundColor: api.hexToRgba(api.color('clinical'), api.number('--rm-line-area-opacity', .12)) },
                { label: 'Diastólica', data: diastolica, borderColor: api.color('reference'), fill: false },
                { label: 'Pulso (lpm)', data: pulso, borderColor: api.color('care'), borderDash: [4, 3], fill: false },
            ]);
            config.options.scales.y.min = 40;
            config.options.plugins.legend = {
                display: true, position: 'bottom', labels: api.baseOptions().plugins.legend.labels,
            };
            api.init('expediente-signos-' + suffix, signsCanvas, config);
        }

        const medicationCanvas = document.getElementById('chartMed' + suffix);
        if (medicationCanvas && medLabels.length) {
            const medicationTones = {
                ACTIVO: 'care', PAUSADO: 'neutral', 'EN REVISION': 'cognitive',
                SUSPENDIDO: 'reference', FINALIZADO: 'reference',
            };
            const colors = medLabels.map(label =>
                api.color(medicationTones[String(label).toUpperCase()] || 'neutral'));
            const config = api.presets.doughnut(medLabels, medCounts, colors);
            config.options.plugins.datalabels = {
                display: true,
                color: api.getCss('--rm-chart-tooltip-text'),
                font: {
                    family: api.getCss('--rm-chart-font-family'),
                    size: api.number('--rm-chart-label-size', 12),
                    weight: '700',
                },
                formatter: value => value > 0 ? value : '',
            };
            api.init('expediente-medicacion-' + suffix, medicationCanvas, config);
        }

        const barthelCanvas = document.getElementById('chartBarthel' + suffix);
        if (barthelCanvas && barthelLabels.length) {
            const config = api.presets.semantic('area', 'rehab', barthelLabels, barthelValues);
            config.data.datasets[0].label = 'Índice Barthel';
            config.options.scales.x.ticks.maxRotation = 35;
            config.options.scales.y.min = 0;
            config.options.scales.y.max = 100;
            config.options.scales.y.ticks.stepSize = 20;
            config.options.plugins.datalabels = {
                display: true,
                color: api.getCss('--rm-chart-label'),
                font: {
                    family: api.getCss('--rm-chart-font-family'),
                    size: api.number('--rm-chart-label-size', 12),
                    weight: '700',
                },
                anchor: 'end',
                align: 'top',
            };
            api.init('expediente-barthel-' + suffix, barthelCanvas, config);
        }

        const evaluationCanvas = document.getElementById('chartEval' + suffix);
        if (evaluationCanvas && evalLabels.length) {
            const config = api.presets.bar(evalLabels, [
                { label: 'Puntaje obtenido', data: evalPuntajes, backgroundColor: api.color('cognitive') },
                { label: 'Puntaje máximo', data: evalMaximos, backgroundColor: api.color('reference') },
            ]);
            config.options.scales.x.grid.display = false;
            config.options.scales.x.ticks.maxRotation = 30;
            config.options.scales.y.min = 0;
            config.options.plugins.legend = {
                display: true, position: 'bottom', labels: api.baseOptions().plugins.legend.labels,
            };
            config.options.plugins.datalabels = {
                display: true,
                color: api.getCss('--rm-chart-label'),
                font: {
                    family: api.getCss('--rm-chart-font-family'),
                    size: api.number('--rm-chart-label-size', 12),
                    weight: '700',
                },
                anchor: 'end',
                align: 'top',
                formatter: value => value > 0 ? value : '',
            };
            api.init('expediente-evaluaciones-' + suffix, evaluationCanvas, config);
        }
    };

    const attach = () => {
        const section = document.getElementById('reportes-charts-section-' + suffix);
        if (!section) return;
        if (section.offsetParent !== null) render();
        observer?.disconnect();
        observer = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting) render();
        }, { threshold: .05 });
        observer.observe(section);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attach, { once: true });
    } else {
        attach();
    }
    window.RMCharts?.onThemeChange(render, 'adulto-carpetas-reportes');
})();
