/**
 * ============================================================
 * REMEMBERMIND DESIGN SYSTEM - CHART THEME ENGINE
 * ============================================================
 * Fuente única de verdad para la estética de todos los gráficos.
 * Compatible con modo claro / oscuro dinámico de RememberMind.
 * ============================================================
 */

// Resolve CSS color functions before passing them to Chart.js' RGB/HEX parser.
let rmColorContext;
const rmResolvedColors = new Map();

function rmResolveCssColor(value) {
    if (typeof value !== 'string') return value;
    const variable = /^var\((--[a-z0-9-]+)\)$/i.exec(value.trim());
    if (variable) return rmGetCss(variable[1]);
    if (!value || /^#|^rgba?\(/i.test(value)) return value;
    if (typeof document === 'undefined' || !globalThis.CSS?.supports('color', value)) return value;
    if (rmResolvedColors.has(value)) return rmResolvedColors.get(value);
    if (!rmColorContext) {
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 1;
        rmColorContext = canvas.getContext('2d', { willReadFrequently: true });
    }
    if (!rmColorContext) return value;
    rmColorContext.clearRect(0, 0, 1, 1);
    rmColorContext.fillStyle = value;
    rmColorContext.fillRect(0, 0, 1, 1);
    const [r, g, b, a] = rmColorContext.getImageData(0, 0, 1, 1).data;
    const resolved = `rgba(${r}, ${g}, ${b}, ${a / 255})`;
    rmResolvedColors.set(value, resolved);
    return resolved;
}

export function rmGetCss(prop) {
    if (typeof window === 'undefined' || typeof document === 'undefined') return '';
    return rmResolveCssColor(getComputedStyle(document.documentElement).getPropertyValue(prop).trim());
}

export function rmChartNumber(prop, fallback) {
    const value = parseFloat(rmGetCss(prop));
    return Number.isFinite(value) ? value : fallback;
}

const RM_CHART_TONES = new Set(['care', 'clinical', 'alert', 'neutral', 'cognitive', 'rehab', 'reference']);
const RM_CHART_DOMAINS = new Set(['residents', 'beds', 'alerts', 'medication', 'cognitive', 'rehab', 'staff', 'activities']);

export function rmChartColor(tone, variant = 'primary') {
    if (variant === 'soft' && RM_CHART_TONES.has(tone)) {
        return rmGetCss(`--rm-chart-${tone}-100`);
    }
    if (RM_CHART_DOMAINS.has(tone)) {
        const slot = variant === 'secondary' ? 'secondary' : 'primary';
        return rmGetCss(`--rm-chart-token-${tone}-${slot}`);
    }
    if (RM_CHART_TONES.has(tone)) {
        return rmGetCss(`--rm-chart-${tone}-500`);
    }
    throw new Error(`Unknown RememberMind chart tone: ${tone}`);
}

export function rmIsDark() {
    if (typeof document === 'undefined') return false;
    return document.documentElement.classList.contains('dark')
        || document.documentElement.dataset.theme === 'dark';
}

export function rmPrefersReducedMotion() {
    return typeof window !== 'undefined'
        && typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function rmHexToRgba(hex, alpha = 1) {
    if (!hex) return 'transparent';
    if (typeof hex !== 'string') return hex;
    hex = rmResolveCssColor(hex);
    if (hex.startsWith('rgba') || hex.startsWith('rgb')) {
        const matches = hex.match(/[\d.]+/g);
        if (matches && matches.length >= 3) {
            return `rgba(${matches[0]}, ${matches[1]}, ${matches[2]}, ${alpha})`;
        }
        return hex;
    }
    if (!/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i.test(hex)) return 'transparent';
    let cleanHex = hex.replace('#', '');
    if (cleanHex.length === 3) cleanHex = cleanHex.split('').map(c => c + c).join('');
    const r = parseInt(cleanHex.substring(0, 2), 16) || 0;
    const g = parseInt(cleanHex.substring(2, 4), 16) || 0;
    const b = parseInt(cleanHex.substring(4, 6), 16) || 0;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// --- Paleta Categórica Institucional (10 colores canónicos de colors.css) ---
export function rmChartPalette() {
    return Array.from({ length: 10 }, (_, i) => {
        const val = rmGetCss(`--rm-chart-${i + 1}`);
        if (val) return val;
        return rmGetCss('--rm-primary') || 'transparent';
    });
}

export function rmChartPaletteAlpha(alpha = 0.85) {
    return rmChartPalette().map(c => rmHexToRgba(c, alpha));
}

// --- Colores Semánticos Clínicos ---
export function rmChartSemanticColors() {
    return {
        danger:      rmGetCss('--rm-chart-danger'),
        dangerSoft:  rmGetCss('--rm-chart-danger-soft'),
        warningHigh: rmGetCss('--rm-chart-warning-high'),
        warning:     rmGetCss('--rm-chart-warning'),
        warningSoft: rmGetCss('--rm-chart-warning-soft'),
        success:     rmGetCss('--rm-chart-success'),
        successSoft: rmGetCss('--rm-chart-success-soft'),
        info:        rmGetCss('--rm-chart-info'),
        infoSoft:    rmGetCss('--rm-chart-info-soft'),
        neutral:     rmGetCss('--rm-chart-neutral'),
    };
}

// --- Mapeo ESTABLE: Origen Clínico -> Token Permanente ---
export const CLINICAL_ORIGINS_MAP = {
    SIGNOS:           { name: 'Signos Vitales',   token: '--rm-chart-2' },
    MEDICACION:       { name: 'Medicación',       token: '--rm-chart-1' },
    INCIDENTE:        { name: 'Incidentes',       token: '--rm-chart-danger' },
    SOLICITUD_MEDICA: { name: 'Solicitud Médica', token: '--rm-chart-7' },
    PLAN:             { name: 'Plan Cuidados',    token: '--rm-chart-3' },
    SEGUIMIENTO:      { name: 'Seguimiento',      token: '--rm-chart-4' },
    VALORACION:       { name: 'Valoración',       token: '--rm-chart-8' },
    FICHA:            { name: 'Ficha Clínica',    token: '--rm-chart-5' },
    MANUAL:           { name: 'Manual',           token: '--rm-chart-6' },
    SISTEMA:          { name: 'Sistema',          token: '--rm-chart-9' },
    USUARIO:          { name: 'Usuario',          token: '--rm-chart-4' },
};

export function rmGetOriginColor(origenKey) {
    const meta = CLINICAL_ORIGINS_MAP[origenKey];
    if (meta) {
        return rmGetCss(meta.token) || rmGetCss('--rm-info');
    }
    return rmGetCss('--rm-info');
}

function rmResolveDatasetGlowColor(dataset, datasetIndex) {
    const candidates = [dataset?.borderColor, dataset?.backgroundColor]
        .flatMap(value => Array.isArray(value) ? value : [value]);
    const explicit = candidates.find(value => typeof value === 'string' && value !== 'transparent');

    if (explicit) return explicit;

    const paletteIndex = (datasetIndex % 10) + 1;
    return rmGetCss(`--rm-chart-${paletteIndex}`) || rmGetCss('--rm-chart-2') || 'transparent';
}

const rmSoftChartGlowPlugin = {
    id: 'rmSoftChartGlow',

    beforeDatasetDraw(chart, args, pluginOptions) {
        if (pluginOptions?.enabled === false || !chart?.ctx) return;

        const dataset = chart.data?.datasets?.[args.index];
        const color = rmResolveDatasetGlowColor(dataset, args.index);
        const isSparkline = chart.canvas?.closest?.('.rm-sparkline, .is-sparkline');

        chart.ctx.save();
        chart.ctx.shadowColor = rmHexToRgba(color, rmChartNumber('--rm-chart-state-glow', .10));
        chart.ctx.shadowBlur = isSparkline ? 2 : Math.min(3, rmChartNumber('--rm-line-glow-blur', 10));
        chart.ctx.shadowOffsetX = 0;
        chart.ctx.shadowOffsetY = 0;
        args.meta.$rmSoftGlowActive = true;
    },

    afterDatasetDraw(chart, args) {
        if (!args.meta?.$rmSoftGlowActive || !chart?.ctx) return;

        chart.ctx.restore();
        args.meta.$rmSoftGlowActive = false;
    },
};

const rmReducedMotionChartPlugin = {
    id: 'rmReducedMotion',
    beforeInit(chart) {
        if (rmPrefersReducedMotion()) chart.options.animation = false;
    },
};

function rmChartEntryDuration(chart) {
    if (chart.canvas?.closest?.('.rm-sparkline, .is-sparkline')) {
        return rmChartNumber('--rm-chart-sparkline-enter-duration', 420);
    }
    const token = {
        line: '--rm-chart-line-enter-duration',
        bar: '--rm-chart-bar-enter-duration',
        doughnut: '--rm-chart-donut-enter-duration',
        pie: '--rm-chart-donut-enter-duration',
        polarArea: '--rm-chart-donut-enter-duration',
        radar: '--rm-chart-radar-enter-duration',
    }[chart.config.type] || '--rm-chart-motion-enter-duration';
    return rmChartNumber(token, 720);
}

const rmSemanticMotionPlugin = {
    id: 'rmSemanticMotion',
    beforeInit(chart) {
        if (rmPrefersReducedMotion()) {
            chart.options.animation = false;
            return;
        }
        const original = chart.config.options?.animation;
        if (original === false) return;
        const prior = original && typeof original === 'object' ? original : {};
        const enterDuration = rmChartEntryDuration(chart);
        const updateDuration = rmChartNumber('--rm-chart-motion-update-duration', 420);
        const stagger = rmChartNumber('--rm-chart-stagger-fast', 35);
        const easing = rmGetCss('--rm-chart-js-easing') || 'easeOutCubic';
        chart.options.animation = {
            ...prior,
            duration: () => chart.$rmChartDrawn ? updateDuration : enterDuration,
            easing,
            delay: (context) => chart.$rmChartDrawn || context.type !== 'data'
                ? 0 : Math.min(context.dataIndex || 0, 8) * stagger,
        };
        chart.options.transitions.active.animation.duration = rmChartNumber('--rm-chart-motion-hover-duration', 160);
    },
    afterRender(chart) {
        chart.$rmChartDrawn = true;
    },
};

/**
 * Instala el lenguaje visual institucional sobre cualquier instancia Chart.js,
 * incluidas las gráficas heredadas que todavía no consumen los presets RMCharts.
 */
export function rmInstallGlobalChartTheme(Chart) {
    if (!Chart || Chart.__rmClinicalThemeInstalled) return;

    Chart.register(rmSoftChartGlowPlugin, rmReducedMotionChartPlugin, rmSemanticMotionPlugin);

    const applyDefaults = () => {
        const axisText = rmGetCss('--rm-chart-axis-text');
        const grid = rmGetCss('--rm-chart-grid');
        const tooltipBg = rmGetCss('--rm-chart-tooltip-bg');
        const tooltipText = rmGetCss('--rm-chart-tooltip-text');
        const tooltipBorder = rmGetCss('--rm-chart-tooltip-border') || grid;

        Chart.defaults.color = axisText;
        Chart.defaults.borderColor = grid;
        Chart.defaults.font.family = rmGetCss('--rm-chart-font-family') || "'Nunito Sans', sans-serif";
        Chart.defaults.font.size = rmChartNumber('--rm-chart-label-size', 12);
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        const reduceMotion = rmPrefersReducedMotion();
        Chart.defaults.animation.duration = reduceMotion ? 0 : rmChartNumber('--rm-chart-motion-enter-duration', 720);
        Chart.defaults.animation.easing = rmGetCss('--rm-chart-js-easing') || 'easeOutCubic';
        Chart.defaults.interaction.mode = 'index';
        Chart.defaults.interaction.intersect = false;

        Chart.defaults.elements.line.borderWidth = rmChartNumber('--rm-line-stroke-width', 3);
        Chart.defaults.elements.line.tension = .38;
        Chart.defaults.elements.point.radius = rmChartNumber('--rm-line-dot-size', 5) / 2;
        Chart.defaults.elements.point.hoverRadius = rmChartNumber('--rm-line-dot-hover-size', 7) / 2;
        Chart.defaults.elements.point.borderWidth = 2;
        Chart.defaults.elements.bar.borderRadius = rmChartNumber('--rm-bar-radius', 10);
        Chart.defaults.elements.bar.borderSkipped = false;
        Chart.defaults.elements.arc.borderWidth = 2;
        Chart.defaults.elements.arc.borderRadius = 6;
        Chart.defaults.elements.arc.spacing = 3;

        Chart.defaults.plugins.legend.labels.color = axisText;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
        Chart.defaults.plugins.legend.labels.padding = 14;
        Chart.defaults.plugins.legend.labels.font = {
            family: rmGetCss('--rm-chart-font-family'),
            size: rmChartNumber('--rm-chart-legend-size', 12),
            weight: '600',
        };

        Object.assign(Chart.defaults.plugins.tooltip, {
            backgroundColor: tooltipBg,
            titleColor: tooltipText,
            bodyColor: tooltipText,
            borderColor: tooltipBorder,
            borderWidth: 1,
            cornerRadius: rmChartNumber('--rm-tooltip-radius', 14),
            padding: 11,
            boxPadding: 5,
            usePointStyle: true,
            titleFont: { family: rmGetCss('--rm-chart-font-family'), size: rmChartNumber('--rm-chart-label-size', 12), weight: '700' },
            bodyFont: { family: rmGetCss('--rm-chart-font-family'), size: rmChartNumber('--rm-chart-label-size', 12), weight: '500' },
            animation: { duration: reduceMotion ? 0 : rmChartNumber('--rm-chart-motion-tooltip-duration', 160), easing: rmGetCss('--rm-chart-js-easing') || 'easeOutCubic' },
        });

        if (Chart.defaults.plugins.datalabels) {
            Chart.defaults.plugins.datalabels.display = false;
        }
    };

    applyDefaults();
    Chart.__rmClinicalThemeInstalled = true;

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(() => {
            const previous = {
                axis: Chart.defaults.color,
                grid: Chart.defaults.borderColor,
                tooltipBg: Chart.defaults.plugins.tooltip.backgroundColor,
                tooltipText: Chart.defaults.plugins.tooltip.bodyColor,
                tooltipBorder: Chart.defaults.plugins.tooltip.borderColor,
            };
            applyDefaults();
            const current = {
                axis: Chart.defaults.color,
                grid: Chart.defaults.borderColor,
                tooltipBg: Chart.defaults.plugins.tooltip.backgroundColor,
                tooltipText: Chart.defaults.plugins.tooltip.bodyColor,
                tooltipBorder: Chart.defaults.plugins.tooltip.borderColor,
            };
            Object.values(Chart.instances || {}).forEach((instance) => {
                if (!instance?.options) return;
                const replace = (object, key, oldValue, newValue) => {
                    if (object?.[key] === oldValue) object[key] = newValue;
                };
                const tooltip = instance.options.plugins?.tooltip;
                replace(tooltip, 'backgroundColor', previous.tooltipBg, current.tooltipBg);
                replace(tooltip, 'titleColor', previous.tooltipText, current.tooltipText);
                replace(tooltip, 'bodyColor', previous.tooltipText, current.tooltipText);
                replace(tooltip, 'borderColor', previous.tooltipBorder, current.tooltipBorder);
                replace(instance.options.plugins?.legend?.labels, 'color', previous.axis, current.axis);
                Object.values(instance.options.scales || {}).forEach((scale) => {
                    replace(scale.ticks, 'color', previous.axis, current.axis);
                    replace(scale.grid, 'color', previous.grid, current.grid);
                });
                instance.update('none');
            });
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
    }
}

export function rmGetOriginLabel(origenKey) {
    return CLINICAL_ORIGINS_MAP[origenKey]?.name || origenKey;
}

// --- Gradientes Elegantes para Barras y Áreas ---
export function rmCreateGradient(ctx, hex, height = 240) {
    if (!ctx) return hex;
    try {
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        const alpha = rmChartNumber('--rm-line-area-opacity', .12);
        gradient.addColorStop(0,   rmHexToRgba(hex, alpha));
        gradient.addColorStop(0.5, rmHexToRgba(hex, alpha / 2));
        gradient.addColorStop(1,   rmHexToRgba(hex, 0));
        return gradient;
    } catch (e) {
        return hex;
    }
}

export function rmCreateBarGradient(ctx, hex, height = 240) {
    if (!ctx) return hex;
    try {
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0,   rmHexToRgba(hex, rmChartNumber('--rm-bar-fill-hover-opacity', .96)));
        gradient.addColorStop(0.6, rmHexToRgba(hex, rmChartNumber('--rm-bar-fill-opacity', .84)));
        gradient.addColorStop(1,   rmHexToRgba(hex, rmChartNumber('--rm-bar-fill-soft-opacity', .72)));
        return gradient;
    } catch (e) {
        return hex;
    }
}

// --- Opciones Base Compartidas ---
export function rmBaseChartOptions(overrides = {}) {
    const axisTextColor   = rmGetCss('--rm-chart-axis-text');
    const axisTitleColor  = rmGetCss('--rm-chart-axis-title');
    const gridColor       = rmGetCss('--rm-chart-grid');
    const tooltipBg       = rmGetCss('--rm-chart-tooltip-bg');
    const tooltipText     = rmGetCss('--rm-chart-tooltip-text');
    const tooltipBorder   = rmGetCss('--rm-chart-tooltip-border');

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: rmPrefersReducedMotion() ? 0 : rmChartNumber('--rm-chart-motion-enter-duration', 720),
            easing: rmGetCss('--rm-chart-js-easing') || 'easeOutCubic',
        },
        transitions: {
            active: {
                animation: {
                    duration: rmPrefersReducedMotion() ? 0 : rmChartNumber('--rm-chart-motion-hover-duration', 160),
                    easing: 'easeOutCubic'
                }
            }
        },
        layout: {
            padding: { top: 8, right: 18, bottom: 6, left: 6 },
        },
        plugins: {
            legend: {
                display: false,
                labels: {
                    font: { family: "'Nunito Sans', Inter, system-ui, sans-serif", size: 11, weight: '600' },
                    color: axisTextColor,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 12,
                },
            },
            tooltip: {
                backgroundColor: tooltipBg,
                titleColor: tooltipText,
                bodyColor: tooltipText,
                borderColor: tooltipBorder,
                borderWidth: 1,
                cornerRadius: rmChartNumber('--rm-tooltip-radius', 14),
                padding: { top: 10, right: 12, bottom: 10, left: 12 },
                titleFont: { family: rmGetCss('--rm-chart-font-family'), size: rmChartNumber('--rm-chart-label-size', 12), weight: '700' },
                bodyFont: { family: rmGetCss('--rm-chart-font-family'), size: rmChartNumber('--rm-chart-label-size', 12), weight: '500' },
                boxPadding: 4,
                displayColors: true,
            },
            datalabels: {
                display: false,
            },
        },
        scales: {
            x: {
                grid: { color: gridColor, drawBorder: false },
                ticks: {
                    color: axisTextColor,
                    font: { family: "'Nunito Sans', sans-serif", size: 10, weight: '500' },
                },
            },
            y: {
                grid: { color: gridColor, drawBorder: false },
                ticks: {
                    color: axisTextColor,
                    font: { family: "'Nunito Sans', sans-serif", size: 10, weight: '500' },
                },
            },
        },
        ...overrides,
    };
}

export function rmDoughnutDefaults() {
    const base = rmBaseChartOptions();
    delete base.scales;
    return {
        ...base,
        cutout: rmGetCss('--rm-donut-cutout') || '76%',
        animation: {
            duration: rmPrefersReducedMotion() ? 0 : rmChartNumber('--rm-chart-donut-enter-duration', 750),
            easing: rmGetCss('--rm-chart-js-easing') || 'easeOutCubic',
            animateRotate: true,
            animateScale: false,
        },
        plugins: {
            ...base.plugins,
            legend: {
                display: false,
            },
            datalabels: {
                display: false,
            },
        },
    };
}
