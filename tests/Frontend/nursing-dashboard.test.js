import { test } from 'node:test';
import assert from 'node:assert/strict';

async function entorno(nombre, visible = true, ready = 'complete') {
    const eventos = new Map(), hooks = [], frames = [], creadas = [], destruidas = [], temas = [];
    const registrar = (name, callback) => eventos.set(name, [...(eventos.get(name) ?? []), callback]);
    const chart = name => ({ dataset: { [name]: JSON.stringify(name === 'trend'
        ? { labels: ['17/10'], labels_completas: ['17/10/2026'], datasets: [{ label: 'Caída', color: 'var(--rm-chart-coral)', data: [1] }] }
        : { labels: ['Uno', 'Dos', 'Tres'], values: [1, 2, 3] }) }, querySelector: () => ({ name }) });
    const roots = { '[data-incident-trend]': chart('trend'), '[data-nursing-followup]': chart('nursingFollowup'), '[data-nursing-activity]': chart('nursingActivity') };
    // El canvas debe conservar identidad entre renderizados como en el DOM real.
    for (const root of Object.values(roots)) { const canvas = root.querySelector(); root.querySelector = () => canvas; }
    const config = (labels, values) => ({ data: { labels, datasets: [{ data: values }] }, options: {
        plugins: { tooltip: { callbacks: {} }, datalabels: {} },
        scales: { x: { ticks: { font: {} } }, y: { ticks: { font: {} } } },
    } });
    let mounted = visible, dark = false;
    globalThis.window = { addEventListener: registrar, Livewire: { hook: (name, callback) => hooks.push(callback) },
        RMCharts: { getCss: () => '#7FA58D', number: (name, fallback) => fallback,
            isDark: () => dark, hexToRgba: color => color,
            init: (key, canvas, config) => { creadas.push({ key, canvas, config }); return { canvas }; },
            destroy: key => destruidas.push(key), onThemeChange: callback => temas.push(callback),
            presets: { doughnut: config, barHorizontal: config } } };
    globalThis.Livewire = window.Livewire;
    globalThis.Chart = function () {};
    globalThis.document = { readyState: ready, addEventListener: registrar,
        querySelector: selector => selector === '.rm-nursing-dashboard' ? (mounted ? {} : null) : mounted ? roots[selector] : null,
        documentElement: { classList: { contains: () => dark }, getAttribute: () => dark ? 'dark' : 'light', dataset: {} } };
    globalThis.getComputedStyle = () => ({ getPropertyValue: () => '#7FA58D' });
    globalThis.matchMedia = () => ({ matches: false });
    globalThis.requestAnimationFrame = callback => { frames.push(callback); return frames.length; };
    const flush = () => { while (frames.length) frames.shift()(); };
    const modulo = await import('../../resources/frontend/scripts/modules/nursing-dashboard.js?' + nombre);
    return { modulo, eventos, hooks, roots, creadas, destruidas, temas, flush,
        mount: value => { mounted = value; }, theme: value => { dark = value; } };
}

test('las gráficas no se montan en una pantalla ajena al dashboard', async () => {
    const e = await entorno('otra-pantalla', false);
    e.modulo.montarDashboardEnfermeria(); e.flush();
    assert.equal(e.creadas.length, 0);
    assert.equal(e.hooks.length, 0);
    assert.equal(window.remembermindNursingLightMotion, undefined);
});

test('el primer montaje espera a que el DOM esté disponible', async () => {
    const e = await entorno('dom-pendiente', true, 'loading');
    e.flush();
    assert.equal(e.creadas.length, 0);
    document.readyState = 'interactive';
    for (const callback of e.eventos.get('DOMContentLoaded')) callback();
    e.flush();
    assert.equal(e.creadas.length, 3);
    assert.equal(e.creadas[1].config.type, 'pie');
    assert.deepEqual(e.creadas[2].config.data.datasets[0].data, [1, 2, 3]);
});

test('navegar y volver a montar conserva una instancia por gráfica y listeners únicos', async () => {
    const e = await entorno('ciclo');
    e.modulo.montarDashboardEnfermeria(); e.flush();
    assert.equal(e.creadas.length, 3);
    const listeners = [...e.eventos].map(([key, callbacks]) => [key, callbacks.length]);
    e.modulo.montarDashboardEnfermeria();
    for (const callback of e.eventos.get('livewire:navigated')) callback();
    for (const callback of e.hooks) callback();
    e.flush();
    assert.equal(e.creadas.length, 3);
    assert.deepEqual([...e.eventos].map(([key, callbacks]) => [key, callbacks.length]), listeners);
    assert.equal(e.hooks.length, 2);
    assert.equal(e.eventos.get('pointermove').length, 1);
});

test('un cambio de datos o tema actualiza las gráficas y salir libera sus referencias', async () => {
    const e = await entorno('actualizacion');
    e.modulo.montarDashboardEnfermeria(); e.flush();
    e.roots['[data-nursing-activity]'].dataset.nursingActivity = JSON.stringify({ labels: ['Pendientes'], values: [9] });
    for (const callback of e.hooks) callback(); e.flush();
    assert.equal(e.creadas.length, 4);
    e.theme(true);
    for (const callback of e.temas) callback();
    for (const callback of e.eventos.get('remembermind:theme-changed')) callback();
    e.flush();
    assert.equal(e.creadas.length, 7);
    e.mount(false); for (const callback of e.hooks) callback(); e.flush();
    assert.deepEqual(new Set(e.destruidas), new Set(['nursing-dashboard-incident-trend','nursing-dashboard-followup','nursing-dashboard-activity']));
    assert.equal(window.remembermindIncidentTrend.chart, null);
    assert.deepEqual(window.remembermindNursingAnalytics.state, {});
});

test('un payload ilegible retira la gráfica anterior y registra el fallo', async () => {
    const e = await entorno('payload-invalido');
    e.modulo.montarDashboardEnfermeria(); e.flush();
    const avisos = [], anterior = console.warn;
    console.warn = (...args) => avisos.push(args);
    try {
        e.roots['[data-incident-trend]'].dataset.trend = '{';
        e.roots['[data-nursing-activity]'].dataset.nursingActivity = '{';
        for (const callback of e.hooks) callback(); e.flush();
        assert.equal(e.creadas.length, 3);
        assert.equal(avisos.length, 2);
        assert.ok(e.destruidas.includes('nursing-dashboard-incident-trend'));
        assert.ok(e.destruidas.includes('nursing-dashboard-activity'));
        assert.equal(window.remembermindIncidentTrend.chart, null);
        assert.equal(window.remembermindNursingAnalytics.state.activity, undefined);
    } finally { console.warn = anterior; }
});
