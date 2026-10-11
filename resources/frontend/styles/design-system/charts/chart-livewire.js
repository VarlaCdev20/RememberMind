/**
 * ============================================================
 * REMEMBERMIND DESIGN SYSTEM - CHART INSTANCE MANAGER & LIVEWIRE
 * ============================================================
 * Gestiona un Map() único para instancias Chart.js.
 * Reutiliza instancias compatibles para animar cambios de datos,
 * y adapta los gráficos a Livewire y al tema sin recarga.
 * ============================================================
 */

import { rmChartNumber, rmIsDark, rmPrefersReducedMotion } from './chart-theme.js';

// Map() global único de instancias de gráficos
const chartInstances = new Map();

// Map() de renderers para refresco automático por tema o resize
const chartRenderers = new Map();

/**
 * Obtiene el Map de instancias.
 */
export function rmGetInstances() {
    return chartInstances;
}

/**
 * Inicializa un gráfico registrado con key única.
 * Reutiliza una instancia compatible; destruye y recrea cuando cambia
 * el canvas o el tipo de gráfico.
 */
export function rmInitChart(key, canvasIdOrEl, config, rendererFn = null) {
    const canvas = typeof canvasIdOrEl === 'string'
        ? document.getElementById(canvasIdOrEl)
        : canvasIdOrEl;

    if (!canvas || typeof Chart === 'undefined') {
        return null;
    }

    const registered = chartInstances.get(key);
    let existing = null;
    try {
        existing = Chart.getChart(canvas);
    } catch (e) {
        console.warn(`[RM Charts] Error reading canvas chart for ${key}:`, e);
    }

    // Preserve the Chart.js instance so values interpolate from the previous data.
    if (existing && registered === existing && existing.config.type === config.type) {
        const visibility = existing.data.datasets.map((_, index) => existing.isDatasetVisible(index));
        existing.data.labels = config.data.labels;
        config.data.datasets.forEach((dataset, index) => {
            if (existing.data.datasets[index]) {
                Object.assign(existing.data.datasets[index], dataset);
            } else {
                existing.data.datasets.push(dataset);
            }
        });
        existing.data.datasets.length = config.data.datasets.length;
        existing.options = {
            ...config.options,
            animation: rmPrefersReducedMotion() ? false : {
                ...(config.options?.animation && typeof config.options.animation === 'object' ? config.options.animation : {}),
                duration: rmChartNumber('--rm-chart-motion-update-duration', 420),
                delay: 0,
            },
        };
        visibility.forEach((visible, index) => {
            if (index < existing.data.datasets.length) existing.setDatasetVisibility(index, visible);
        });
        existing.update();
        if (typeof rendererFn === 'function') chartRenderers.set(key, rendererFn);
        return existing;
    }

    // A changed canvas or chart type needs a new instance.
    rmDestroyChart(key);
    if (existing) {
        for (const [otherKey, chart] of chartInstances.entries()) {
            if (chart === existing) {
                chartInstances.delete(otherKey);
                chartRenderers.delete(otherKey);
            }
        }
        existing.destroy();
    }

    // Crear una instancia cuando no es posible conservar la anterior.
    try {
        const instance = new Chart(canvas, config);
        chartInstances.set(key, instance);

        if (typeof rendererFn === 'function') {
            chartRenderers.set(key, rendererFn);
        }

        return instance;
    } catch (e) {
        console.error(`[RM Charts] Error creating chart "${key}":`, e);
        return null;
    }
}

/**
 * Destruye un gráfico específico por su key.
 */
export function rmDestroyChart(key) {
    if (chartInstances.has(key)) {
        try {
            const chart = chartInstances.get(key);
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        } catch (e) {
            console.warn(`[RM Charts] Error destroying chart ${key}:`, e);
        }
        chartInstances.delete(key);
    }
    chartRenderers.delete(key);
}

/**
 * Destruye todos los gráficos registrados.
 */
export function rmDestroyAllCharts() {
    for (const [key, chart] of chartInstances.entries()) {
        try {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        } catch (e) {
            console.warn(`[RM Charts] Error destroying chart ${key}:`, e);
        }
    }
    chartInstances.clear();
    chartRenderers.clear();
}

/**
 * Obtiene la instancia de un gráfico.
 */
export function rmGetChart(key) {
    return chartInstances.get(key) || null;
}

/**
 * Verifica si existe un gráfico por su key.
 */
export function rmHasChart(key) {
    return chartInstances.has(key);
}

/**
 * Actualiza los datos de un gráfico sin reconstruirlo si es posible.
 */
export function rmUpdateChartData(keyOrChart, newData, newLabels = null) {
    const chart = typeof keyOrChart === 'string'
        ? chartInstances.get(keyOrChart)
        : keyOrChart;

    if (!chart || !chart.data || !chart.data.datasets || !chart.data.datasets[0]) {
        return false;
    }

    try {
        if (newLabels && Array.isArray(newLabels)) {
            chart.data.labels = newLabels;
        }
        chart.data.datasets[0].data = newData;
        chart.update();
        return true;
    } catch (e) {
        console.warn('[RM Charts] Error updating chart data:', e);
        return false;
    }
}

/**
 * Re-renderiza todos los gráficos que tengan registrado un renderer.
 * Útil tras cambios de modo claro <-> oscuro.
 */
export function rmRefreshAllCharts() {
    for (const [key, rendererFn] of [...chartRenderers.entries()]) {
        try {
            rendererFn();
        } catch (e) {
            console.warn(`[RM Charts] Error re-rendering chart ${key}:`, e);
        }
    }
}

/**
 * Conecta reactividad de Livewire con un callback.
 */
export function rmWatchLivewireData($wire, prop, callback) {
    if (!$wire || typeof $wire.$watch !== 'function') {
        return;
    }
    $wire.$watch(prop, (newValue) => {
        if (newValue) {
            callback(newValue);
        }
    });
}

/**
 * Observador de cambio de tema:
 * Reutiliza el mecanismo REAL de RememberMind:
 * 1. Escucha 'remembermind:theme-changed' en window
 * 2. Observa MutationObserver en document.documentElement (class / data-theme)
 */
let themeObserverInitialized = false;
const themeListeners = new Set();
const keyedThemeListeners = new Map();

export function rmOnThemeChange(callback, key = null, owner = null) {
    if (key) keyedThemeListeners.set(key, { callback, owner });
    else themeListeners.add(callback);
    initThemeWatcher();
    return () => {
        themeListeners.delete(callback);
        if (key && keyedThemeListeners.get(key)?.callback === callback) keyedThemeListeners.delete(key);
    };
}

function initThemeWatcher() {
    if (themeObserverInitialized || typeof window === 'undefined') return;
    themeObserverInitialized = true;

    let debounceTimer = null;
    const notifyListeners = () => {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const isDark = rmIsDark();
            themeListeners.forEach(fn => {
                try { fn(isDark); } catch (e) { console.warn('[RM Charts] Theme listener error:', e); }
            });
            keyedThemeListeners.forEach((entry, key) => {
                if (entry.owner && !entry.owner.isConnected) {
                    keyedThemeListeners.delete(key);
                    return;
                }
                try { entry.callback(isDark); } catch (e) { console.warn('[RM Charts] Theme listener error:', e); }
            });
            rmRefreshAllCharts();
        }, 50);
    };

    // 1. Evento canónico de RememberMind
    window.addEventListener('remembermind:theme-changed', notifyListeners);

    // Livewire navigate leaves detached canvases in Chart.js unless disposed here.
    document.addEventListener('livewire:navigated', () => {
        for (const [key, chart] of chartInstances.entries()) {
            if (!chart.canvas?.isConnected) rmDestroyChart(key);
        }
        for (const [key, entry] of keyedThemeListeners.entries()) {
            if (entry.owner && !entry.owner.isConnected) keyedThemeListeners.delete(key);
        }
    });

    // 2. MutationObserver sobre <html> para captar .dark o data-theme
    if (typeof MutationObserver !== 'undefined' && document.documentElement) {
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.type === 'attributes' && (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme')) {
                    notifyListeners();
                    break;
                }
            }
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class', 'data-theme']
        });
    }
}
