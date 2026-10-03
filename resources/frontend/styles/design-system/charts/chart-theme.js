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
        chart.ctx.shadowColor = rmHexToRgba(color, rmIsDark() ? .11 : .08);
        chart.ctx.shadowBlur = isSparkline ? 2 : 3;
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

/**
 * Instala el lenguaje visual institucional sobre cualquier instancia Chart.js,
 * incluidas las gráficas heredadas que todavía no consumen los presets RMCharts.
 */
export function rmInstallGlobalChartTheme(Chart) {
    if (!Chart || Chart.__rmClinicalThemeInstalled) return;

    Chart.register(rmSoftChartGlowPlugin);

    const applyDefaults = () => {
        const axisText = rmGetCss('--rm-chart-axis-text');
        const grid = rmGetCss('--rm-chart-grid');
        const tooltipBg = rmGetCss('--rm-chart-tooltip-bg');
        const tooltipText = rmGetCss('--rm-chart-tooltip-text');
        const tooltipBorder = rmGetCss('--rm-chart-tooltip-border') || grid;

        Chart.defaults.color = axisText;
        Chart.defaults.borderColor = grid;
        Chart.defaults.font.family = "'Nunito Sans', Inter, system-ui, sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        const reduceMotion = rmPrefersReducedMotion();
        Chart.defaults.animation.duration = reduceMotion ? 0 : 560;
        Chart.defaults.animation.easing = 'easeOutQuart';
        Chart.defaults.interaction.mode = 'index';
        Chart.defaults.interaction.intersect = false;

        Chart.defaults.elements.line.borderWidth = 3;
        Chart.defaults.elements.line.tension = .38;
        Chart.defaults.elements.point.radius = 3;
        Chart.defaults.elements.point.hoverRadius = 5.5;
        Chart.defaults.elements.point.borderWidth = 2;
        Chart.defaults.elements.bar.borderRadius = 10;
        Chart.defaults.elements.bar.borderSkipped = false;
        Chart.defaults.elements.arc.borderWidth = 2;
        Chart.defaults.elements.arc.borderRadius = 6;
        Chart.defaults.elements.arc.spacing = 3;

        Chart.defaults.plugins.legend.labels.color = axisText;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
        Chart.defaults.plugins.legend.labels.padding = 14;
        Chart.defaults.plugins.legend.labels.font = {
            family: "'Nunito Sans', Inter, system-ui, sans-serif",
            size: 11,
            weight: '600',
        };

        Object.assign(Chart.defaults.plugins.tooltip, {
            backgroundColor: tooltipBg,
            titleColor: tooltipText,
            bodyColor: tooltipText,
            borderColor: tooltipBorder,
            borderWidth: 1,
            cornerRadius: 14,
            padding: 11,
            boxPadding: 5,
            usePointStyle: true,
            titleFont: { family: "'Nunito Sans', sans-serif", size: 12, weight: '700' },
            bodyFont: { family: "'Nunito Sans', sans-serif", size: 11, weight: '500' },
        });

        if (Chart.defaults.plugins.datalabels) {
            Chart.defaults.plugins.datalabels.display = false;
        }
    };

    applyDefaults();
    Chart.__rmClinicalThemeInstalled = true;

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(() => {
            applyDefaults();
            Object.values(Chart.instances || {}).forEach((instance) => instance?.update?.('none'));
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
        gradient.addColorStop(0,   rmHexToRgba(hex, 0.38));
        gradient.addColorStop(0.5, rmHexToRgba(hex, 0.16));
        gradient.addColorStop(1,   rmHexToRgba(hex, 0.02));
        return gradient;
    } catch (e) {
        return hex;
    }
}

export function rmCreateBarGradient(ctx, hex, height = 240) {
    if (!ctx) return hex;
    try {
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0,   rmHexToRgba(hex, 0.95));
        gradient.addColorStop(0.6, rmHexToRgba(hex, 0.80));
        gradient.addColorStop(1,   rmHexToRgba(hex, 0.65));
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
            duration: rmPrefersReducedMotion() ? 0 : 560,
            easing: 'easeOutQuart',
        },
        transitions: {
            active: {
                animation: {
                    duration: 220,
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
                cornerRadius: 14,
                padding: { top: 10, right: 12, bottom: 10, left: 12 },
                titleFont: { family: "'Nunito Sans', sans-serif", size: 12, weight: '700' },
                bodyFont: { family: "'Nunito Sans', sans-serif", size: 11, weight: '500' },
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
        cutout: '58%',
        animation: {
            duration: rmPrefersReducedMotion() ? 0 : 650,
            easing: 'easeOutQuart',
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
