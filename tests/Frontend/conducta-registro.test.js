import { test } from 'node:test';
import assert from 'node:assert/strict';

globalThis.window ??= {};
globalThis.requestAnimationFrame = callback => callback();
await import('../../resources/frontend/scripts/modules/clinical-form-feedback.js');
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');
const { conductaRegistro, conductaChart } = await import('../../resources/frontend/scripts/modules/conducta-registro.js');

const component = () => {
    const item = conductaRegistro();
    item.$wire = { datos: { descripcion: null, intervencion: null, respuesta: null },
        $set(path, value) { this.datos[path.split('.')[1]] = value; },
        async cerrarModal() { item.closed = true; }, async limpiarCaptura() { item.cleared = true; },
    };
    item.$refs = { historyTrigger: { focus() { item.returnedFocus = true; } } };
    item.$nextTick = callback => callback();
    return item;
};

test('ayudas añaden texto editable sin duplicarlo ni escribir otros campos', () => {
    const item = component();
    item.addText('descripcion', 'Contexto: ruido.');
    item.addText('descripcion', 'Contexto: ruido.');
    item.addText('descripcion', 'Contexto: descanso.');
    item.addText('cod_personal', 'FORJADO');
    assert.equal(item.$wire.datos.descripcion, 'Contexto: ruido.\nContexto: descanso.');
    assert.equal(item.$wire.datos.cod_personal, undefined);
    assert.equal(item.clinicalFormDirty, true);
});

test('salir y limpiar captura modificada requieren confirmación; no justificativo', async () => {
    const item = component();
    item.clinicalFormDirty = true;
    item.close();
    assert.equal(item.clinicalDiscardOpen, true);
    assert.equal(item.closed, undefined);
    item.clinicalDiscardOpen = false;
    item.clear();
    assert.equal(item.discardIntent, 'clear');
    assert.equal(item.cleared, undefined);
    await item.confirmDiscard();
    assert.equal(item.cleared, true);
    assert.equal(item.clinicalFormDirty, false);
    item.close();
    assert.equal(item.closed, true);
});

test('historial se cierra primero y devuelve foco; captura se conserva', () => {
    const item = component();
    item.historyOpen = true; item.clinicalFormDirty = true;
    item.close();
    assert.equal(item.historyOpen, false);
    assert.equal(item.returnedFocus, true);
    assert.equal(item.clinicalDiscardOpen, false);
    assert.equal(item.clinicalFormDirty, true);
});

test('petición pendiente bloquea doble envío y salidas; fallo mantiene captura', async () => {
    const item = component();
    let reject, calls = 0;
    item.$wire.guardar = () => { calls++; return new Promise((resolve, failure) => { reject = failure; }); };
    item.clinicalFormDirty = true;
    const saving = item.submit();
    await item.submit(); item.close(); item.clear();
    assert.equal(calls, 1);
    assert.equal(item.closed, undefined);
    assert.equal(item.cleared, undefined);
    reject(new Error('Transport failure'));
    await assert.rejects(saving);
    assert.equal(item.busy, false);
    assert.equal(item.clinicalFormDirty, true);
});

test('evolución usa popup independiente acotado al viewport; Escape conserva captura', () => {
    window.innerWidth = 1440; window.innerHeight = 900;
    const item = component(); let opened = false;
    item.$refs.trendDialog = { matches: () => opened, showPopover() { opened = true; }, hidePopover() { opened = false; }, querySelector: () => ({ focus() {} }), closest: () => null };
    const trigger = { focus() { item.graphFocusReturned = true; } };
    item.$wire.datos.descripcion = 'Captura que permanece'; item.clinicalFormDirty = true;
    item.openTrend(trigger);
    assert.equal(opened, true);
    assert.equal(item.trendBounds.width, 520);
    item.moveTrend(-10000, 10000);
    assert.equal(item.trendBounds.x, 8);
    assert.ok(item.trendBounds.y + item.trendBounds.height <= 892);
    item.resizeTrend(10000, 10000);
    assert.ok(item.trendBounds.width <= 792);
    item.close();
    assert.equal(opened, false);
    assert.equal(item.graphFocusReturned, true);
    assert.equal(item.clinicalFormDirty, true);
    assert.equal(item.clinicalDiscardOpen, false);
    assert.equal(item.$wire.datos.descripcion, 'Captura que permanece');
    window.innerWidth = 390; item.openTrend(trigger);
    assert.equal(item.trendBounds.width, 374);
    assert.ok(item.trendBounds.x + item.trendBounds.width <= 382);
});

test('línea factual: cero/uno no inventan tendencia; fechas mantienen sus intervalos', () => {
    assert.equal(conductaChart([]).line, '');
    const one = conductaChart([{ fecha: '2026-10-01', registros: 1 }]);
    assert.equal(one.points.length, 1); assert.equal(one.points[0].x, 50);
    assert.equal(one.line, ''); assert.equal(one.area, '');
    const chart = conductaChart([{ fecha: '2026-10-11', registros: 3 }, { fecha: '2026-10-01', registros: 1 }, { fecha: '2026-10-02', registros: 2 }]);
    assert.deepEqual(chart.points.map(row => row.x), [5, 14, 95]);
    assert.deepEqual(chart.points.map(row => row.value), [1, 2, 3]);
    assert.equal(chart.points.length, 3);
    assert.ok(chart.line); assert.ok(chart.area);
    chart.active = 1; assert.equal(chart.selected.date, '2026-10-02');
});

test('datos no representables no producen puntos ni etiquetas inválidas', () => {
    const chart = conductaChart([null, { fecha: '2026-02-30', registros: 2 }, { fecha: '2026-10-01', registros: null }, { fecha: '2026-10-02', registros: '2abc' }, { fecha: '2026-10-03', registros: Infinity }, { fecha: '2026-10-04', registros: '2', observadas: ['Ansiedad', null] }]);
    assert.equal(chart.points.length, 1);
    assert.equal(chart.points[0].value, 2);
    assert.equal(chart.points[0].observed, 'Ansiedad');
    assert.equal(/NaN|Infinity|undefined/.test(chart.line + chart.area), false);
});
