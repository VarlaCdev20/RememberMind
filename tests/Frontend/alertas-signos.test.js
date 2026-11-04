import { test } from 'node:test';
import assert from 'node:assert/strict';
import { datosSignosAlerta, rmSignosAlerta } from '../../resources/frontend/scripts/modules/alertas-signos.js';

test('presión conserva sistólica y diastólica, no convierte ausencias en cero ni admite valores inválidos', () => {
    const datos = datosSignosAlerta([
        { fecha: '2026-10-20T08:00:00-04:00', presion_sistolica: 220, presion_diastolica: null },
        { fecha: '2026-10-20T09:00:00-04:00', presion_sistolica: '120abc', presion_diastolica: 0 },
        { fecha: 'fecha inválida', presion_sistolica: 120, presion_diastolica: 80 },
    ], 'presion');
    assert.equal(datos.unidad, 'mmHg');
    assert.deepEqual(datos.series.map(s => s.label), ['Sistólica', 'Diastólica']);
    assert.deepEqual(datos.series[0].data.map(p => p.y), [220]);
    assert.deepEqual(datos.series[1].data.map(p => p.y), [0]);
    assert.notEqual(datos.series[0].pointStyle, datos.series[1].pointStyle);
});

test('una medición de temperatura se presenta sin límites fijos, interpolación ni datos artificiales', () => {
    let configuracion;
    globalThis.window = { RMCharts: {
        color: tono => tono, init: (clave, canvas, config) => { configuracion = config; return true; },
        presets: { line: (labels, datasets) => ({ data: { labels, datasets }, options: { scales: { x: { ticks: {} }, y: {} }, plugins: { tooltip: {}, legend: {} } } }) },
    } };
    const ui = rmSignosAlerta([{ fecha: '2026-10-20T08:00:00-04:00', temperatura: 41.5 }], 'prueba', 'America/La_Paz');
    ui.$refs = { canvas: {} };
    ui.render();
    assert.equal(ui.variable, 'temperatura');
    assert.equal(ui.error, false);
    assert.equal(configuracion.data.datasets[0].data[0].y, 41.5);
    assert.equal(configuracion.data.datasets[0].showLine, false);
    assert.equal(configuracion.options.scales.y.min, undefined);
    assert.equal(configuracion.options.scales.y.max, undefined);
    ui.variable = 'presion';
    window.RMCharts.destroy = () => {};
    ui.render();
    assert.equal(ui.sinMediciones, true);
    delete globalThis.window;
});
