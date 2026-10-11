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
    rmChartNumber,
    rmChartColor,
    rmPrefersReducedMotion,
    rmCreateGradient,
    rmCreateBarGradient,
} from './chart-theme.js';

// --- Preset: Doughnut Clínico Translúcido Grueso ---
export function rmDoughnutChartConfig(labels, data, colors, customOptions = {}) {
    const defaults = rmDoughnutDefaults();
    
    // Convertir colores a translúcidos con bordes nítidos para efecto vidrio/luz
    const translucentBg = colors.map(c => rmHexToRgba(c, rmChartNumber('--rm-donut-ring-opacity', .52)));
    const segmentBorder = colors.map(c => rmHexToRgba(c, rmChartNumber('--rm-chart-mark-border-opacity', .88)));

    return {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: translucentBg,
                borderColor: segmentBorder,
                borderWidth: 2,
                borderRadius: 5,
                spacing: rmChartNumber('--rm-donut-gap', 2),
                hoverOffset: 4,
                hoverBorderColor: segmentBorder,
                hoverBorderWidth: 2.5,
            }]
        },
        options: {
            ...defaults,
            cutout: rmGetCss('--rm-donut-cutout'),
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
    const axisTextColor  = rmGetCss('--rm-chart-axis-text');
    const axisTitleColor = rmGetCss('--rm-chart-axis-title');
    const gridColor      = rmGetCss('--rm-chart-grid');

    const maxVal = Math.max(...(data.length ? data : [0]), 1);
    const suggestedMax = maxVal + Math.ceil(maxVal * 0.28) + 1;

    // Colores intensos con acabado traslúcido elegante
    const translucentBg = colors.map(c => rmHexToRgba(c, rmChartNumber('--rm-hbar-fill-opacity', .52)));
    const borderColors  = colors.map(c => rmHexToRgba(c, rmChartNumber('--rm-chart-mark-border-opacity', .88)));

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
                font: { family: "'Nunito Sans', system-ui, sans-serif", size: 11, weight: '500' },
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
                color: axisTitleColor,
                font: { family: "'Nunito Sans', system-ui, sans-serif", size: 11.5, weight: '600' },
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
            color: axisTitleColor,
            font: { family: "'Nunito Sans', system-ui, sans-serif", size: 11.5, weight: '700' },
            formatter: function(value) {
                return value;
            },
            clip: false,
            ...(customOptions.plugins?.datalabels || {}),
        },
        tooltip: {
            ...base.plugins.tooltip,
            padding: 10,
            cornerRadius: 14,
            titleFont: { family: "'Nunito Sans', system-ui, sans-serif", size: 12, weight: '700' },
            bodyFont: { family: "'Nunito Sans', system-ui, sans-serif", size: 11.5, weight: '500' },
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
                borderWidth: 2,
                borderRadius: rmChartNumber('--rm-hbar-radius', 999),
                borderSkipped: false,
                barThickness: rmChartNumber('--rm-hbar-height', 14),
                maxBarThickness: rmChartNumber('--rm-hbar-height', 14),
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
                pointRadius: rmChartNumber('--rm-line-dot-size', 5) / 2,
                pointHoverRadius: 5.5,
                pointBorderWidth: 2,
                pointBackgroundColor: rmGetCss('--rm-chart-surface-bg'),
                borderWidth: rmChartNumber('--rm-line-stroke-width', 3),
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
                tension: 0.38,
                pointRadius: rmChartNumber('--rm-line-dot-size', 5) / 2,
                pointHoverRadius: 5.5,
                borderWidth: rmChartNumber('--rm-line-stroke-width', 3),
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
                const fillSource = ds.backgroundColor || ds.borderColor;
                const edgeSource = ds.borderColor || ds.backgroundColor;
                const bg = Array.isArray(fillSource)
                    ? fillSource.map(c => rmHexToRgba(c, rmChartNumber('--rm-bar-fill-opacity', .52)))
                    : (fillSource ? rmHexToRgba(fillSource, rmChartNumber('--rm-bar-fill-opacity', .52)) : undefined);
                const border = Array.isArray(edgeSource)
                    ? edgeSource.map(c => rmHexToRgba(c, rmChartNumber('--rm-chart-mark-border-opacity', .88)))
                    : (edgeSource ? rmHexToRgba(edgeSource, rmChartNumber('--rm-chart-mark-border-opacity', .88)) : undefined);
                return {
                    ...ds,
                    borderRadius: rmChartNumber('--rm-bar-radius', 10),
                    borderSkipped: false,
                    barPercentage: 0.86,
                    categoryPercentage: 0.78,
                    maxBarThickness: rmChartNumber('--rm-bar-width', 18),
                    borderWidth: 2,
                    backgroundColor: bg || ds.backgroundColor,
                    borderColor: border || ds.borderColor,
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
            datasets: datasets.map(ds => {
                const fillSource = ds.backgroundColor || ds.borderColor;
                const edgeSource = ds.borderColor || ds.backgroundColor;
                const toColor = (source, opacity) => Array.isArray(source)
                    ? source.map(color => rmHexToRgba(color, opacity))
                    : rmHexToRgba(source, opacity);
                return {
                    ...ds,
                    borderRadius: rmChartNumber('--rm-bar-radius', 10),
                    barPercentage: 0.86,
                    categoryPercentage: 0.78,
                    borderWidth: 2,
                    backgroundColor: fillSource ? toColor(fillSource, rmChartNumber('--rm-bar-fill-opacity', .52)) : ds.backgroundColor,
                    borderColor: edgeSource ? toColor(edgeSource, rmChartNumber('--rm-chart-mark-border-opacity', .88)) : ds.borderColor,
                };
            })
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
    const translucentBg = colors.map(c => rmHexToRgba(c, rmChartNumber('--rm-donut-ring-opacity', .52)));
    const borderColors = colors.map(c => rmHexToRgba(c, rmChartNumber('--rm-chart-mark-border-opacity', .88)));

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
    if (datasets.length > 2) {
        throw new Error('RememberMind radar charts support at most two series');
    }
    const axisTextColor = rmGetCss('--rm-chart-axis-text');
    const gridColor     = rmGetCss('--rm-chart-grid-soft');
    const base = rmBaseChartOptions();
    delete base.scales;

    return {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets.map(ds => ({
                fill: true,
                pointRadius: rmChartNumber('--rm-radar-node-size', 4) / 2,
                pointHoverRadius: 5,
                borderWidth: rmChartNumber('--rm-radar-stroke-width', 2),
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
                        font: { family: "'Nunito Sans', system-ui, sans-serif", size: 11, weight: '500' }
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
    const fillColor = rmHexToRgba(color || rmChartColor('care'), rmChartNumber('--rm-radial-ring-opacity', .88));
    const trackColor = rmHexToRgba(rmChartColor('care', 'soft'), rmChartNumber('--rm-radial-track-opacity', .16));

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
            cutout: rmGetCss('--rm-radial-cutout'),
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
    const mainColor = color || rmChartColor('care');
    const pointSurface = rmGetCss('--rm-chart-surface-bg');
    const sparklineWidth = rmChartNumber('--rm-sparkline-stroke-width', 2.5);
    const sparklineFill = rmChartNumber('--rm-sparkline-fill-opacity', .10);

    // Gradiente vertical translúcido para el área
    const fillGradient = (context) => {
        const chart = context.chart;
        const { ctx, chartArea } = chart;
        if (!chartArea) {
            return rmHexToRgba(mainColor, sparklineFill);
        }
        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        gradient.addColorStop(0, rmHexToRgba(mainColor, sparklineFill));
        gradient.addColorStop(0.65, rmHexToRgba(mainColor, sparklineFill / 2));
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
                    if (!chartArea) return rmHexToRgba(dsColor, sparklineFill);
                    const grad = cCtx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    grad.addColorStop(0, rmHexToRgba(dsColor, sparklineFill));
                    grad.addColorStop(0.7, rmHexToRgba(dsColor, sparklineFill / 2));
                    grad.addColorStop(1, rmHexToRgba(dsColor, 0.01));
                    return grad;
                }),
                borderWidth: sparklineWidth,
                tension: 0.40,
                fill: true,
                pointRadius: customOptions._showPoints ? 2.5 : 0,
                pointHoverRadius: 5.5,
                pointBackgroundColor: pointSurface,
                pointBorderColor: dsColor,
                pointBorderWidth: 1.5,
                pointHoverBackgroundColor: dsColor,
                pointHoverBorderColor: pointSurface,
                pointHoverBorderWidth: 2,
                ...ds,
            };
        })
        : [{
            label: customOptions._datasetLabel || 'Tendencia',
            data: Array.isArray(data) ? data : [],
            borderColor: mainColor,
            backgroundColor: fillGradient,
            borderWidth: sparklineWidth,
            tension: 0.40,
            fill: true,
            pointRadius: customOptions._showPoints !== false ? 2.5 : 0,
            pointHoverRadius: 5.5,
            pointBackgroundColor: pointSurface,
            pointBorderColor: mainColor,
            pointBorderWidth: 1.5,
            pointHoverBackgroundColor: mainColor,
            pointHoverBorderColor: pointSurface,
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
                duration: rmPrefersReducedMotion() ? 0 : 560,
                easing: 'easeOutQuart',
            },
            animations: {
                y: {
                    duration: rmPrefersReducedMotion() ? 0 : 520,
                    easing: 'easeOutQuart',
                },
                x: {
                    duration: rmPrefersReducedMotion() ? 0 : 560,
                    easing: 'easeOutQuart',
                }
            },
            transitions: {
                active: {
                    animation: {
                        duration: rmPrefersReducedMotion() ? 0 : 250,
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
                    backgroundColor: rmGetCss('--rm-chart-tooltip-bg'),
                    titleColor: rmGetCss('--rm-chart-tooltip-text'),
                    bodyColor: rmGetCss('--rm-chart-tooltip-text'),
                    borderColor: rmGetCss('--rm-chart-tooltip-border'),
                    borderWidth: 1,
                    cornerRadius: 14,
                    padding: { top: 5, right: 8, bottom: 5, left: 8 },
                    titleFont: { family: "'Nunito Sans', system-ui, sans-serif", size: 10, weight: '700' },
                    bodyFont: { family: "'Nunito Sans', system-ui, sans-serif", size: 10.5, weight: '500' },
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

/**
 * Entrada semántica para nuevas gráficas. `tone` describe el dato, no la página.
 * Los gráficos de gravedad clínica deben recibir colores explícitos del dominio.
 */
export function rmSemanticChartConfig(type, tone, labels, data, customOptions = {}) {
    const primary = rmChartColor(tone);
    const secondary = ['residents', 'beds', 'alerts', 'medication', 'staff', 'activities', 'cognitive', 'rehab'].includes(tone)
        ? rmChartColor(tone, 'secondary')
        : rmChartColor('reference');
    if (type === 'donut' && labels.length > 2 && !customOptions.colors) {
        throw new Error('Multi-category donuts require explicit semantic colors in customOptions.colors');
    }
    const series = (Array.isArray(data) && data.length && typeof data[0] === 'object' && 'data' in data[0])
        ? data.map((item, index) => {
            const color = index === 0 ? primary : secondary;
            return {
                ...item,
                borderColor: item.borderColor || color,
                backgroundColor: item.backgroundColor || (type === 'line' || type === 'area'
                    ? rmHexToRgba(color, rmChartNumber('--rm-line-area-opacity', .12))
                    : color),
            };
        })
        : [{ label: tone, data, borderColor: primary,
            backgroundColor: type === 'line' || type === 'area'
                ? rmHexToRgba(primary, rmChartNumber('--rm-line-area-opacity', .12)) : primary }];

    switch (type) {
        case 'line': return rmLineChartConfig(labels, series, customOptions);
        case 'area': return rmAreaChartConfig(labels, series, customOptions);
        case 'bar': return rmBarChartConfig(labels, series, customOptions);
        case 'stackedBar': return rmStackedBarChartConfig(labels, series, customOptions);
        case 'radar': return rmRadarChartConfig(labels, series, customOptions);
        case 'sparkline': return rmSparklineChartConfig(labels, data, primary, customOptions);
        case 'barHorizontal': return rmBarHorizontalChartConfig(labels, data, labels.map((_, index) => index ? secondary : primary), customOptions);
        case 'donut': return rmDoughnutChartConfig(labels, data, customOptions.colors || labels.map((_, index) => index ? secondary : primary), customOptions);
        default: throw new Error(`Unknown RememberMind chart type: ${type}`);
    }
}
