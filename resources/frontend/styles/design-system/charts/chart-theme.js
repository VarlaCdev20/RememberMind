/**
 * ============================================================
 * REMEMBERMIND DESIGN SYSTEM - CHART THEME ENGINE
 * ============================================================
 * Fuente única de verdad para la estética de todos los gráficos.
 * Compatible con modo claro / oscuro dinámico de RememberMind.
 * ============================================================
 */

export function rmGetCss(prop) {
    if (typeof window === 'undefined' || typeof document === 'undefined') return '';
    return getComputedStyle(document.documentElement).getPropertyValue(prop).trim();
}

export function rmIsDark() {
    if (typeof document === 'undefined') return false;
    return document.documentElement.classList.contains('dark')
        || document.documentElement.dataset.theme === 'dark';
}

export function rmHexToRgba(hex, alpha = 1) {
    if (!hex) return `rgba(100, 116, 139, ${alpha})`;
    if (hex.startsWith('rgba') || hex.startsWith('rgb')) {
        const matches = hex.match(/[\d.]+/g);
        if (matches && matches.length >= 3) {
            return `rgba(${matches[0]}, ${matches[1]}, ${matches[2]}, ${alpha})`;
        }
        return hex;
    }
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
        const fallbacks = [
            '#344D7A', '#D9745B', '#5F9271', '#C9913E', '#7565A8',
            '#4E8CA6', '#A85C73', '#7B879A', '#9A744E', '#568A80'
        ];
        return fallbacks[i] || '#344D7A';
    });
}

export function rmChartPaletteAlpha(alpha = 0.85) {
    return rmChartPalette().map(c => rmHexToRgba(c, alpha));
}

// --- Colores Semánticos Clínicos ---
export function rmChartSemanticColors() {
    const isDark = rmIsDark();
    return {
        danger:      rmGetCss('--rm-chart-danger')      || (isDark ? '#F87171' : '#E5534B'),
        dangerSoft:  rmGetCss('--rm-chart-danger-soft')  || (isDark ? 'rgba(248,113,113,0.18)' : '#FDE8E7'),
        warningHigh: rmGetCss('--rm-chart-warning-high') || (isDark ? '#FB923C' : '#E67A22'),
        warning:     rmGetCss('--rm-chart-warning')      || (isDark ? '#FBBF24' : '#D9822B'),
        warningSoft: rmGetCss('--rm-chart-warning-soft')  || (isDark ? 'rgba(251,191,36,0.18)' : '#FEF3E2'),
        success:     rmGetCss('--rm-chart-success')      || (isDark ? '#34D399' : '#2D8A6E'),
        successSoft: rmGetCss('--rm-chart-success-soft')  || (isDark ? 'rgba(52,211,153,0.18)' : '#E6F5EE'),
        info:        rmGetCss('--rm-chart-info')         || (isDark ? '#60A5FA' : '#2563EB'),
        infoSoft:    rmGetCss('--rm-chart-info-soft')    || (isDark ? 'rgba(96,165,250,0.18)' : '#EFF6FF'),
        neutral:     rmGetCss('--rm-chart-neutral')      || (isDark ? '#94A3B8' : '#64748B'),
    };
}

// --- Mapeo ESTABLE: Origen Clínico -> Token Permanente ---
export const CLINICAL_ORIGINS_MAP = {
    SIGNOS:           { name: 'Signos Vitales',   token: '--rm-chart-6', fallback: '#F43F5E' }, // Vivid Rose
    MEDICACION:       { name: 'Medicación',       token: '--rm-chart-2', fallback: '#8B5CF6' }, // Vivid Purple
    INCIDENTE:        { name: 'Incidentes',       token: '--rm-chart-7', fallback: '#F97316' }, // Vivid Orange
    SOLICITUD_MEDICA: { name: 'Solicitud Médica', token: '--rm-chart-5', fallback: '#06B6D4' }, // Vivid Cyan
    PLAN:             { name: 'Plan Cuidados',    token: '--rm-chart-1', fallback: '#0EA5E9' }, // Sky Blue
    SEGUIMIENTO:      { name: 'Seguimiento',      token: '--rm-chart-3', fallback: '#14B8A6' }, // Teal
    VALORACION:       { name: 'Valoración',       token: '--rm-chart-4', fallback: '#10B981' }, // Emerald
    FICHA:            { name: 'Ficha Clínica',    token: '--rm-chart-10',fallback: '#F59E0B' }, // Amber
    MANUAL:           { name: 'Manual',           token: '--rm-chart-8', fallback: '#6366F1' }, // Indigo
    SISTEMA:          { name: 'Sistema',          token: '--rm-chart-9', fallback: '#64748B' }, // Slate
    USUARIO:          { name: 'Usuario',          token: '--rm-chart-5', fallback: '#8B5CF6' }, // Purple
};

export function rmGetOriginColor(origenKey) {
    const meta = CLINICAL_ORIGINS_MAP[origenKey];
    if (meta) {
        return meta.fallback || rmGetCss(meta.token) || '#344D7A';
    }
    return '#344D7A';
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
    const isDark = rmIsDark();
    const axisTextColor   = rmGetCss('--rm-chart-axis-text')    || (isDark ? '#94A3B8' : '#64748B');
    const axisTitleColor  = rmGetCss('--rm-chart-axis-title')   || (isDark ? '#F8FAFC' : '#1E293B');
    const gridColor       = rmGetCss('--rm-chart-grid')         || (isDark ? 'rgba(255,255,255,0.06)' : 'rgba(224,212,198,0.35)');
    const tooltipBg       = rmGetCss('--rm-chart-tooltip-bg')   || (isDark ? '#0F172A' : '#1E293B');
    const tooltipText     = rmGetCss('--rm-chart-tooltip-text') || '#F8FAFC';
    const tooltipBorder   = rmGetCss('--rm-chart-tooltip-border') || (isDark ? 'rgba(255,255,255,0.12)' : 'rgba(226,232,240,0.20)');

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 900,
            easing: 'easeOutQuart',
        },
        transitions: {
            active: {
                animation: {
                    duration: 350,
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
                    font: { family: 'Inter, system-ui, sans-serif', size: 11, weight: '500' },
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
                cornerRadius: 8,
                padding: { top: 8, right: 12, bottom: 8, left: 12 },
                titleFont: { family: 'Inter, system-ui, sans-serif', size: 11, weight: '600' },
                bodyFont: { family: 'Inter, system-ui, sans-serif', size: 11, weight: '400' },
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
                    font: { family: 'Inter, system-ui, sans-serif', size: 10, weight: '500' },
                },
            },
            y: {
                grid: { color: gridColor, drawBorder: false },
                ticks: {
                    color: axisTextColor,
                    font: { family: 'Inter, system-ui, sans-serif', size: 10, weight: '500' },
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
            duration: 950,
            easing: 'easeOutQuart',
            animateRotate: true,
            animateScale: true,
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
