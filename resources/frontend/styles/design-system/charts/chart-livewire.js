/**
 * ============================================================
 * REMEMBERMIND DESIGN SYSTEM - CHART INSTANCE MANAGER & LIVEWIRE
 * ============================================================
 * Gestiona un Map() único para instancias Chart.js.
 * Garantiza destrucción previa estricta, reactividad con Livewire
 * y adaptación fluida a cambios claro <-> oscuro sin recarga.
 * ============================================================
 */

import { rmIsDark } from './chart-theme.js';

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
 * Destruye siempre cualquier instancia previa con la misma key
 * o asociada al canvas antes de crear la nueva.
 */
export function rmInitChart(key, canvasIdOrEl, config, rendererFn = null) {
    const canvas = typeof canvasIdOrEl === 'string'
        ? document.getElementById(canvasIdOrEl)
        : canvasIdOrEl;

    if (!canvas || typeof Chart === 'undefined') {
        return null;
    }

    // 1. Destruir si ya existe en nuestro Map
    rmDestroyChart(key);

    // 2. Seguridad extra: si Chart.js ya tiene un chart en este canvas, destruirlo
    try {
        const existing = Chart.getChart(canvas);
        if (existing) {
            existing.destroy();
        }
    } catch (e) {
        console.warn(`[RM Charts] Error destroying previous canvas chart for ${key}:`, e);
    }

    // 3. Crear nueva instancia
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
        } catch (e) {}
    }
    chartInstances.clear();
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
    for (const [key, rendererFn] of chartRenderers.entries()) {
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

export function rmOnThemeChange(callback) {
    themeListeners.add(callback);
    initThemeWatcher();
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
            rmRefreshAllCharts();
        }, 50);
    };

    // 1. Evento canónico de RememberMind
    window.addEventListener('remembermind:theme-changed', notifyListeners);

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
