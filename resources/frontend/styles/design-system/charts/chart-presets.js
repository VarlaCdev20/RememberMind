/**
 * ============================================================
 * REMEMBERMIND DESIGN SYSTEM - CHART PRESETS
 * ============================================================
 * Presets estandarizados para Chart.js respetando tokens institucionales.
 * Estilo visual: Translúcido (Glassmorphic) con barras y anillos gruesos.
 * ============================================================
 */

import {
    rmBaseChartOptions,
    rmDoughnutDefaults,
    rmHexToRgba,
    rmGetCss,
    rmIsDark,
    rmCreateGradient,
    rmCreateBarGradient,
} from './chart-theme.js';

// --- Preset: Doughnut Clínico Translúcido Grueso ---
export function rmDoughnutChartConfig(labels, data, colors, customOptions = {}) {
    const defaults = rmDoughnutDefaults();
    
    // Convertir colores a translúcidos con bordes nítidos para efecto vidrio/luz
    const translucentBg = colors.map(c => rmHexToRgba(c, 0.80));
    const borderColors = colors.map(c => rmHexToRgba(c, 0.98));

    return {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: translucentBg,
                borderColor: borderColors,
                borderWidth: 2,
                hoverOffset: 6,
                hoverBorderColor: '#FFFFFF',
                hoverBorderWidth: 2.5,
            }]
        },
        options: {
            ...defaults,
            cutout: '58%', // Anillo grueso con cuerpo y presencia
            plugins: {
                ...defaults.plugins,
                tooltip: {
                    ...defaults.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                            return ` ${context.label}: ${val} (${pct}%)`;
                        }
                    }
                }
            },
            ...customOptions,
        }
    };
}

// --- Preset: Barras Horizontales Clínicas Gruesas y Notorias ---
export function rmBarHorizontalChartConfig(labels, data, colors, customOptions = {}) {
    const isDark = rmIsDark();
    const axisTextColor  = rmGetCss('--rm-chart-axis-text')  || (isDark ? '#94A3B8' : '#64748B');
    const axisTitleColor = rmGetCss('--rm-chart-axis-title') || (isDark ? '#F8FAFC' : '#1E293B');
    const gridColor      = rmGetCss('--rm-chart-grid')       || (isDark ? 'rgba(255,255,255,0.06)' : 'rgba(224,212,198,0.35)');

    const maxVal = Math.max(...(data.length ? data : [0]), 1);
    const suggestedMax = maxVal + Math.ceil(maxVal * 0.28) + 1;

    // Colores intensos con acabado traslúcido elegante
    const translucentBg = colors.map(c => rmHexToRgba(c, 0.88));
    const borderColors  = colors.map(c => rmHexToRgba(c, 0.98));

    const base = rmBaseChartOptions();

    const mergedScales = {
        x: {
            beginAtZero: true,
            grace: '18%',
            suggestedMax: suggestedMax,
            grid: {
                color: gridColor,
                drawBorder: false,
            },
            ticks: {
                color: axisTextColor,
                font: { family: 'Inter, system-ui, sans-serif', size: 10.5, weight: '600' },
                precision: 0,
                stepSize: 1,
            },
            ...(customOptions.scales?.x || {}),
        },
        y: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                color: isDark ? '#FFFFFF' : '#1E293B',
                font: { family: 'Inter, system-ui, sans-serif', size: 11.5, weight: '700' },
                padding: 8,
            },
            ...(customOptions.scales?.y || {}),
        }
    };

    const mergedPlugins = {
        ...base.plugins,
        legend: { display: false },
        datalabels: {
            display: function(context) {
                return (context.dataset.data[context.dataIndex] || 0) > 0;
            },
            anchor: 'end',
            align: 'right',
            offset: 8,
            color: isDark ? '#FFFFFF' : '#0F172A',
            font: { family: 'Outfit, Inter, system-ui, sans-serif', size: 11.5, weight: '800' },
            formatter: function(value) {
                return value;
            },
            clip: false,
            ...(customOptions.plugins?.datalabels || {}),
        },
        tooltip: {
            ...base.plugins.tooltip,
            padding: 10,
            cornerRadius: 8,
            titleFont: { family: 'Inter, system-ui, sans-serif', size: 12, weight: '700' },
            bodyFont: { family: 'Inter, system-ui, sans-serif', size: 11.5, weight: '500' },
            callbacks: {
                label: function(context) {
                    const raw = context.raw || 0;
                    return ` Total: ${raw} eventos`;
                }
            },
            ...(customOptions.plugins?.tooltip || {}),
        },
    };

    const { scales: _s, plugins: _p, layout: _l, ...otherCustom } = customOptions;

    return {
        type: 'bar',
        data: {
            labels: labels.length ? labels : ['Sin datos'],
            datasets: [{
                label: customOptions._datasetLabel || 'Total',
                data: data.length ? data : [0],
                backgroundColor: translucentBg,
                borderColor: borderColors,
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 18,
                maxBarThickness: 20,
            }]
        },
        options: {
            ...base,
            indexAxis: 'y',
            layout: {
                padding: { top: 6, right: 38, bottom: 6, left: 6 },
                ...(_l || {}),
            },
            scales: mergedScales,
            plugins: mergedPlugins,
            ...otherCustom,
        }
    };
}

// --- Preset: Área Suave Translúcida (Evolución) ---
export function rmAreaChartConfig(labels, datasets, customOptions = {}) {
    const base = rmBaseChartOptions();
    return {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets.map(ds => ({
                fill: true,
                tension: 0.38,
                pointRadius: 4,
                pointHoverRadius: 6.5,
                pointBorderWidth: 2,
                pointBackgroundColor: rmIsDark() ? '#1E293B' : '#FFFFFF',
                borderWidth: 2.5,
                ...ds,
            }))
        },
        options: {
            ...base,
            ...customOptions,
        }
    };
}

// --- Preset: Línea Continua ---
export function rmLineChartConfig(labels, datasets, customOptions = {}) {
    const base = rmBaseChartOptions();
    return {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets.map(ds => ({
                tension: 0.28,
                pointRadius: 4,
                pointHoverRadius: 6.5,
                borderWidth: 2.5,
                ...ds,
            }))
        },
        options: {
            ...base,
            ...customOptions,
        }
    };
}

// --- Preset: Barras Verticales Gruesas y Translúcidas ---
export function rmBarChartConfig(labels, datasets, customOptions = {}) {
    const base = rmBaseChartOptions();
    return {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets.map(ds => {
                const bg = Array.isArray(ds.backgroundColor)
                    ? ds.backgroundColor.map(c => rmHexToRgba(c, 0.78))
                    : (ds.backgroundColor ? rmHexToRgba(ds.backgroundColor, 0.78) : undefined);
                const border = Array.isArray(ds.borderColor)
                    ? ds.borderColor.map(c => rmHexToRgba(c, 0.95))
                    : (ds.borderColor ? rmHexToRgba(ds.borderColor, 0.95) : undefined);
                return {
                    borderRadius: 8,
                    borderSkipped: 'bottom',
                    barPercentage: 0.85,
                    categoryPercentage: 0.90,
                    borderWidth: 1.5,
                    backgroundColor: bg || ds.backgroundColor,
                    borderColor: border || ds.borderColor,
                    ...ds,
                };
            })
        },
        options: {
            ...base,
            ...customOptions,
        }
    };
}

// --- Preset: Barras Apiladas Translúcidas ---
export function rmStackedBarChartConfig(labels, datasets, customOptions = {}) {
    const base = rmBaseChartOptions();
    return {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets.map(ds => ({
                borderRadius: 6,
                barPercentage: 0.85,
                categoryPercentage: 0.90,
                borderWidth: 1.5,
                ...ds,
            }))
        },
        options: {
            ...base,
            scales: {
                x: { ...base.scales.x, stacked: true },
                y: { ...base.scales.y, stacked: true },
            },
            ...customOptions,
        }
    };
}

// --- Preset: Torta (Pie) Translúcida ---
export function rmPieChartConfig(labels, data, colors, customOptions = {}) {
    const defaults = rmDoughnutDefaults();
    delete defaults.cutout;
    const translucentBg = colors.map(c => rmHexToRgba(c, 0.80));
    const borderColors = colors.map(c => rmHexToRgba(c, 0.98));

    return {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: translucentBg,
                borderColor: borderColors,
                borderWidth: 2,
            }]
        },
        options: {
            ...defaults,
            ...customOptions,
        }
    };
}

// --- Preset: Radar Geriátrico ---
export function rmRadarChartConfig(labels, datasets, customOptions = {}) {
    const isDark = rmIsDark();
    const axisTextColor = rmGetCss('--rm-chart-axis-text') || (isDark ? '#94A3B8' : '#64748B');
    const gridColor     = rmGetCss('--rm-chart-grid')      || (isDark ? 'rgba(255,255,255,0.08)' : 'rgba(224,212,198,0.40)');
    const base = rmBaseChartOptions();
    delete base.scales;

    return {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets.map(ds => ({
                fill: true,
                pointRadius: 4,
                borderWidth: 2,
                ...ds,
            }))
        },
        options: {
            ...base,
            scales: {
                r: {
                    grid: { color: gridColor },
                    angleLines: { color: gridColor },
                    pointLabels: {
                        color: axisTextColor,
                        font: { family: 'Inter, system-ui, sans-serif', size: 10, weight: '600' }
                    },
                    ticks: {
                        display: false,
                        stepSize: 1,
                    }
                }
            },
            ...customOptions,
        }
    };
}

// --- Preset: Scatter / Dispersión ---
export function rmScatterChartConfig(datasets, customOptions = {}) {
    const base = rmBaseChartOptions();
    return {
        type: 'scatter',
        data: { datasets },
        options: {
            ...base,
            ...customOptions,
        }
    };
}

// --- Preset: Gauge / Semicírculo ---
export function rmGaugeConfig(value, max = 100, color = null, customOptions = {}) {
    const isDark = rmIsDark();
    const fillColor = color ? rmHexToRgba(color, 0.85) : (rmGetCss('--rm-chart-1') || '#344D7A');
    const trackColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(224,212,198,0.30)';

    return {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [value, Math.max(0, max - value)],
                backgroundColor: [fillColor, trackColor],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            circumference: 180,
            rotation: -90,
            cutout: '68%', // Más grueso que el 80% previo
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
                datalabels: { display: false },
            },
            ...customOptions,
        }
    };
}
// --- Preset: Micrográfico / Sparkline Clínico con Movimiento Suave Translúcido ---
export function rmSparklineChartConfig(labels, data, color = null, customOptions = {}) {
    const isDark = rmIsDark();
    const mainColor = color || rmGetCss('--rm-chart-1') || (isDark ? '#60A5FA' : '#344D7A');

    // Gradiente vertical translúcido para el área
    const fillGradient = (context) => {
        const chart = context.chart;
        const { ctx, chartArea } = chart;
        if (!chartArea) {
            return rmHexToRgba(mainColor, isDark ? 0.28 : 0.20);
        }
        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        gradient.addColorStop(0, rmHexToRgba(mainColor, isDark ? 0.38 : 0.26));
        gradient.addColorStop(0.65, rmHexToRgba(mainColor, isDark ? 0.14 : 0.08));
        gradient.addColorStop(1, rmHexToRgba(mainColor, 0.01));
        return gradient;
    };

    const isMultiDataset = Array.isArray(data) && typeof data[0] === 'object' && data[0] !== null && 'data' in data[0];

    const datasets = isMultiDataset
        ? data.map((ds, idx) => {
            const dsColor = ds.borderColor || ds.color || mainColor;
            return {
                label: ds.label || `Serie ${idx + 1}`,
                data: ds.data,
                borderColor: dsColor,
                backgroundColor: ds.backgroundColor || ((ctx) => {
                    const chart = ctx.chart;
                    const { ctx: cCtx, chartArea } = chart;
                    if (!chartArea) return rmHexToRgba(dsColor, 0.20);
                    const grad = cCtx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    grad.addColorStop(0, rmHexToRgba(dsColor, isDark ? 0.36 : 0.24));
                    grad.addColorStop(0.7, rmHexToRgba(dsColor, isDark ? 0.12 : 0.06));
                    grad.addColorStop(1, rmHexToRgba(dsColor, 0.01));
                    return grad;
                }),
                borderWidth: 2,
                tension: 0.40,
                fill: true,
                pointRadius: customOptions._showPoints ? 2.5 : 0,
                pointHoverRadius: 5.5,
                pointBackgroundColor: isDark ? '#0F172A' : '#FFFFFF',
                pointBorderColor: dsColor,
                pointBorderWidth: 1.5,
                pointHoverBackgroundColor: dsColor,
                pointHoverBorderColor: '#FFFFFF',
                pointHoverBorderWidth: 2,
                ...ds,
            };
        })
        : [{
            label: customOptions._datasetLabel || 'Tendencia',
            data: Array.isArray(data) ? data : [],
            borderColor: mainColor,
            backgroundColor: fillGradient,
            borderWidth: 2,
            tension: 0.40,
            fill: true,
            pointRadius: customOptions._showPoints !== false ? 2.5 : 0,
            pointHoverRadius: 5.5,
            pointBackgroundColor: isDark ? '#0F172A' : '#FFFFFF',
            pointBorderColor: mainColor,
            pointBorderWidth: 1.5,
            pointHoverBackgroundColor: mainColor,
            pointHoverBorderColor: '#FFFFFF',
            pointHoverBorderWidth: 2,
            ...(customOptions.dataset || {}),
        }];

    return {
        type: 'line',
        data: {
            labels: labels && labels.length ? labels : (Array.isArray(data) ? data.map((_, i) => `${i + 1}`) : []),
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            // Animación sutil progresiva (600–900ms, easeOutQuart)
            animation: {
                duration: 800,
                easing: 'easeOutQuart',
            },
            animations: {
                y: {
                    duration: 750,
                    easing: 'easeOutQuart',
                },
                x: {
                    duration: 800,
                    easing: 'easeOutQuart',
                }
            },
            transitions: {
                active: {
                    animation: {
                        duration: 250,
                        easing: 'easeOutCubic'
                    }
                }
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            layout: {
                padding: {
                    top: 4,
                    right: 6,
                    bottom: 4,
                    left: 6,
                },
                ...(customOptions.layout || {}),
            },
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: isDark ? 'rgba(15, 23, 42, 0.92)' : 'rgba(30, 41, 59, 0.90)',
                    titleColor: '#FFFFFF',
                    bodyColor: '#F8FAFC',
                    borderColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.08)',
                    borderWidth: 1,
                    cornerRadius: 6,
                    padding: { top: 5, right: 8, bottom: 5, left: 8 },
                    titleFont: { family: 'Inter, system-ui, sans-serif', size: 10, weight: '600' },
                    bodyFont: { family: 'Inter, system-ui, sans-serif', size: 10.5, weight: '500' },
                    displayColors: false,
                    boxPadding: 3,
                    callbacks: {
                        title: function(items) {
                            return items[0]?.label || '';
                        },
                        label: function(context) {
                            const val = context.parsed?.y !== undefined ? context.parsed.y : context.raw;
                            return ` ${context.dataset.label || 'Valor'}: ${val}`;
                        }
                    },
                    ...(customOptions.plugins?.tooltip || {}),
                },
                ...(customOptions.plugins || {}),
            },
            scales: {
                x: {
                    display: false,
                    grid: { display: false, drawBorder: false },
                    ...(customOptions.scales?.x || {}),
                },
                y: {
                    display: false,
                    grid: { display: false, drawBorder: false },
                    beginAtZero: false,
                    grace: '8%',
                    ...(customOptions.scales?.y || {}),
                },
            },
            ...customOptions,
        }
    };
}

export const rmMiniLineChartConfig = rmSparklineChartConfig;
