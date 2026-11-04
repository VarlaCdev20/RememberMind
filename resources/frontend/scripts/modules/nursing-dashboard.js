// Interacción de presentación; los datos y estados proceden del dashboard del servidor.
export function montarDashboardEnfermeria() {
    if (!document.querySelector('.rm-nursing-dashboard')) return;
    (() => {
        if (window.remembermindIncidentTrend) {
            window.remembermindIncidentTrend.render();
            return;
        }

        const controller = { chart: null, lastPayload: null, lastTheme: null, scheduled: false };
        const token = (name) => window.RMCharts?.getCss(name) || getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        const resolveColor = (value) => {
            const match = /^var\((--[a-z0-9-]+)\)$/.exec(value || '');
            return match ? token(match[1]) : token('--rm-chart-clinical-500');
        };

        controller.render = () => {
            const root = document.querySelector('[data-incident-trend]');
            const canvas = root?.querySelector('canvas');
            if (!canvas || !root.dataset.trend) {
                window.RMCharts?.destroy('nursing-dashboard-incident-trend');
                controller.chart = null;
                controller.lastPayload = null;
                return;
            }
            if (typeof Chart === 'undefined') return;

            const raw = root.dataset.trend;
            const theme = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            if (controller.chart?.canvas === canvas && controller.lastPayload === raw && controller.lastTheme === theme) return;

            let summary;
            try {
                summary = JSON.parse(raw);
            } catch (error) {
                console.warn('No se pudo leer la tendencia de incidentes del turno.', error);
                window.RMCharts?.destroy('nursing-dashboard-incident-trend');
                controller.chart = null;
                controller.lastPayload = null;
                return;
            }
            const datasets = summary.datasets.map((series) => {
                const color = resolveColor(series.color);
                return {
                    label: series.label,
                    data: series.data,
                    borderColor: color,
                    backgroundColor: window.RMCharts?.hexToRgba(color, window.RMCharts.number('--rm-line-area-opacity', .12)) || color,
                    fill: false,
                    tension: 0,
                    borderDash: summary.datasets.length > 1 && series !== summary.datasets[0] ? [6, 3] : [],
                    pointStyle: ['circle', 'rectRot', 'triangle', 'rect'][summary.datasets.indexOf(series) % 4],
                    borderWidth: window.RMCharts?.number('--rm-line-stroke-width', 3) || 3,
                    pointRadius: 4,
                    pointBackgroundColor: token('--rm-chart-surface-bg'),
                    pointBorderWidth: 2,
                    pointHoverRadius: 5.5,
                    pointHitRadius: 14,
                };
            });
            const chartData = { labels: summary.labels, datasets };
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                animation: matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: window.RMCharts.number('--rm-motion-chart-enter', 240), easing: window.RMCharts.getCss('--rm-chart-js-easing') },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: token('--rm-chart-tooltip-bg'),
                        titleColor: token('--rm-chart-tooltip-text'),
                        bodyColor: token('--rm-chart-tooltip-text'),
                        borderColor: token('--rm-chart-tooltip-border'),
                        borderWidth: 1,
                        cornerRadius: window.RMCharts?.number('--rm-tooltip-radius', 14) || 14,
                        callbacks: { title: (items) => summary.labels_completas[items[0]?.dataIndex] || '' },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: token('--rm-text-secondary'), maxRotation: 0, autoSkip: true, maxTicksLimit: 5, font: { family: token('--rm-chart-font-family'), size: window.RMCharts.number('--rm-font-size-meta', 12) } },
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: Math.max(1, ...summary.datasets.flatMap((series) => series.data)),
                        title: { display: true, text: 'Incidentes', color: token('--rm-text-secondary'), font: { size: window.RMCharts.number('--rm-font-size-meta', 12) } },
                        grid: { color: token('--rm-chart-grid') },
                        border: { display: false },
                        ticks: {
                            color: token('--rm-text-secondary'),
                            precision: 0,
                            maxTicksLimit: 4,
                            callback: (value) => Number.isInteger(value) ? value : '',
                            font: { family: token('--rm-chart-font-family'), size: window.RMCharts.number('--rm-font-size-meta', 12) },
                        },
                    },
                },
            };

            controller.chart = window.RMCharts.init('nursing-dashboard-incident-trend', canvas,
                { type: 'line', data: chartData, options });
            controller.lastPayload = raw;
            controller.lastTheme = theme;
        };
        controller.schedule = () => {
            if (controller.scheduled) return;
            controller.scheduled = true;
            requestAnimationFrame(() => { controller.scheduled = false; controller.render(); });
        };
        window.remembermindIncidentTrend = controller;

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', controller.schedule, { once: true });
        else controller.schedule();
        window.addEventListener('load', controller.schedule, { once: true });
        window.addEventListener('remembermind:theme-changed', controller.schedule);
        document.addEventListener('livewire:navigated', controller.schedule);
        const bindLivewire = () => Livewire.hook('morphed', () => controller.schedule());
        if (window.Livewire) bindLivewire();
        else document.addEventListener('livewire:init', bindLivewire, { once: true });
    })();

    (() => {
        if (window.remembermindNursingAnalytics) {
            window.remembermindNursingAnalytics.schedule();
            return;
        }

        const controller = { scheduled: false, themeBound: false, attempts: 0, state: {} };
        const token = (name) => window.RMCharts?.getCss(name) || getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
        const specs = {
            followup: { selector: '[data-nursing-followup]', attribute: 'nursingFollowup', key: 'nursing-dashboard-followup' },
            activity: { selector: '[data-nursing-activity]', attribute: 'nursingActivity', key: 'nursing-dashboard-activity' },
        };

        controller.renderOne = (type) => {
            const spec = specs[type];
            const root = document.querySelector(spec.selector);
            const canvas = root?.querySelector('canvas');
            const raw = root?.dataset[spec.attribute] || '';
            if (!canvas || !raw) {
                window.RMCharts.destroy(spec.key);
                delete controller.state[type];
                return;
            }

            const theme = window.RMCharts.isDark() ? 'dark' : 'light';
            const previous = controller.state[type];
            if (previous?.canvas === canvas && previous.raw === raw && previous.theme === theme) return;

            let data;
            try {
                data = JSON.parse(raw);
            } catch (error) {
                console.warn('No se pudieron leer los datos de actividad del turno.', error);
                window.RMCharts.destroy(spec.key);
                delete controller.state[type];
                return;
            }

            let config;
            if (type === 'followup') {
                const colors = [token('--rm-chart-sage'), token('--rm-chart-blue'), token('--rm-chart-coral')];
                config = window.RMCharts.presets.doughnut(data.labels, data.values, colors);
                config.type = 'pie';
                config.data.datasets[0].backgroundColor = colors;
                config.data.datasets[0].borderColor = token('--rm-chart-surface-bg');
                config.data.datasets[0].borderWidth = 3;
                config.options.cutout = 0;
                config.data.datasets[0].borderRadius = 0;
                config.options.animation = reducedMotion() ? false : {
                    duration: window.RMCharts.number('--rm-motion-chart-enter', 240), easing: 'easeOutQuart', animateRotate: true, animateScale: false,
                };
            } else if (type === 'activity') {
                const colors = [token('--rm-chart-sage'), token('--rm-chart-warning'), token('--rm-chart-coral')];
                config = window.RMCharts.presets.barHorizontal(data.labels, data.values, colors);
                Object.assign(config.data.datasets[0], { backgroundColor: colors, borderWidth: 0, borderRadius: 4, barThickness: 18, maxBarThickness: 18 });
                config.options.scales.x.suggestedMax = Math.max(1, ...data.values);
                config.options.scales.x.ticks.maxTicksLimit = 5;
                for (const axis of ['x', 'y']) {
                    config.options.scales[axis].ticks.color = token('--rm-text-secondary');
                    config.options.scales[axis].ticks.font.size = window.RMCharts.number('--rm-font-size-label', 13);
                }
                config.options.plugins.datalabels.display = true;
                config.options.plugins.tooltip.callbacks.label = (item) => ` ${item.raw} acciones`;
                config.options.animation = reducedMotion() ? false : { duration: window.RMCharts.number('--rm-motion-chart-enter', 240), easing: 'easeOutQuart' };
            }

            if (window.RMCharts.init(spec.key, canvas, config)) {
                controller.state[type] = { canvas, raw, theme };
            }
        };
        controller.render = () => {
            if (typeof Chart === 'undefined' || !window.RMCharts?.presets) {
                if (controller.attempts++ < 20) setTimeout(controller.schedule, 100);
                return;
            }
            controller.attempts = 0;
            if (!controller.themeBound) {
                window.RMCharts.onThemeChange(controller.schedule, 'nursing-dashboard-analytics');
                controller.themeBound = true;
            }
            Object.keys(specs).forEach(controller.renderOne);
        };
        controller.schedule = () => {
            if (controller.scheduled) return;
            controller.scheduled = true;
            requestAnimationFrame(() => { controller.scheduled = false; controller.render(); });
        };
        window.remembermindNursingAnalytics = controller;

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', controller.schedule, { once: true });
        else controller.schedule();
        window.addEventListener('load', controller.schedule, { once: true });
        document.addEventListener('livewire:navigated', controller.schedule);
        const bindLivewire = () => Livewire.hook('morphed', controller.schedule);
        if (window.Livewire) bindLivewire();
        else document.addEventListener('livewire:init', bindLivewire, { once: true });
    })();

    (() => {
        if (window.remembermindNursingLightMotion) return;
        window.remembermindNursingLightMotion = true;
        const finePointer = matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)');
        let frame = 0;
        let active = null;
        let pointerX = 0;
        let pointerY = 0;

        document.addEventListener('pointermove', (event) => {
            if (!finePointer.matches || document.documentElement.classList.contains('dark') || document.documentElement.dataset.theme === 'dark') return;
            const surface = event.target.closest?.('.rm-nursing-dashboard .rm-nursing-welcome, .rm-nursing-dashboard .rm-nursing-module');
            if (!surface) return;
            active = surface;
            pointerX = event.clientX;
            pointerY = event.clientY;
            if (frame) return;
            frame = requestAnimationFrame(() => {
                frame = 0;
                if (!active?.isConnected) return;
                const bounds = active.getBoundingClientRect();
                active.style.setProperty('--rm-nursing-glint-x', `${pointerX - bounds.left}px`);
                active.style.setProperty('--rm-nursing-glint-y', `${pointerY - bounds.top}px`);
            });
        }, { passive: true });
    })();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', montarDashboardEnfermeria, { once: true });
} else {
    montarDashboardEnfermeria();
}
document.addEventListener('livewire:navigated', montarDashboardEnfermeria);
