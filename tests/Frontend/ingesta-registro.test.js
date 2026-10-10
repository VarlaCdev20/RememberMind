import { test } from 'node:test';
import assert from 'node:assert/strict';
globalThis.window = { innerWidth: 1440, innerHeight: 900, addEventListener() {}, removeEventListener() {} };
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');
const { createIntakeCapture, intakeNumber, normalizeIntakeHistory } = await import('../../resources/frontend/scripts/modules/ingesta-registro.js');
const initial = { meal: '', percentage: '', tolerance: '', swallowing: null, liquids: false, quantity: '', observation: '' };
const saved = (porcentaje, date = '2026-10-09T08:00:00') => ({ porcentaje, fecha_hora: date, comida: 'Desayuno', codigo: date });
const make = (history = [], values = {}, threshold = 50) => createIntakeCapture(history, { ...initial, ...values }, '2026-10-09T10:00:00', threshold);

test('porcentaje vacío no equivale a cero; extremos y dos decimales se conservan', () => {
    for (const invalid of [null, undefined, '', ' ', '-1', '100.01', '72.555', 'abc', NaN, Infinity, {}, true, '1e2']) assert.equal(intakeNumber(invalid), null);
    for (const value of ['0', 0, '72.50', '100.00']) assert.equal(intakeNumber(value), Number(value));
    assert.equal(intakeNumber('999999.99', 999999.99), 999999.99);
});
test('captura comparte una fuente exacta para slider, shortcuts y campo', () => {
    const state = make(); const updates = []; state.$wire = { $set: (...args) => updates.push(args) };
    assert.equal(state.isDirty(), false); assert.equal(state.current(), null);
    state.setValue('percentage', '72.50'); assert.equal(state.current(), 72.5); assert.equal(state.values.percentage, '72.50');
    state.setValue('percentage', '0'); assert.equal(state.current(), 0); assert.equal(state.isDirty(), true);
    state.setValue('percentage', '72.555'); assert.equal(state.percentageInvalid(), true); assert.equal(state.current(), null);
    state.setValue('percentage', ''); assert.equal(state.percentageInvalid(), false); assert.equal(state.isDirty(), false);
    assert.deepEqual(updates[0], ['ingestaPorcentaje', '72.50', false]);
});
test('deglución comienza sin respuesta y false es una respuesta explícita', () => {
    const state = make(); assert.equal(state.values.swallowing, null);
    state.setValue('swallowing', false); assert.equal(state.isDirty(), true);
    state.setValue('swallowing', true); assert.equal(state.values.swallowing, true);
});
test('desactivar líquidos borra cantidad incluso cero, sin borrar el resto', () => {
    const state = make([], { meal: 'CENA' }); state.setValue('liquids', true); state.setValue('quantity', '0');
    state.setValue('liquids', false); assert.equal(state.values.quantity, ''); assert.equal(state.values.meal, 'CENA');
    assert.equal(state.isDirty(), false);
});
test('umbral viene del backend y preview no guarda ni genera alertas', () => {
    const state = make([], { percentage: '65' }, 70); assert.equal(state.lowPreview(), true);
    state.setValue('percentage', '70'); assert.equal(state.lowPreview(), false);
    state.setValue('percentage', ''); assert.equal(state.lowPreview(), false);
    assert.equal(make([], { percentage: '0' }, null).lowPreview(), false);
});
test('historial conserva null como ausencia; cero válido y fechas reales', () => {
    const rows = normalizeIntakeHistory([saved(null), saved('0', '2026-10-09T09:00:00'), saved('NaN'), saved(80, '2026-02-30T10:00:00')]);
    assert.equal(rows.length, 3); assert.equal(rows[0].value, null); assert.equal(rows.at(-1).value, 0);
    assert.equal(make([saved(null)]).comparison(), 'Sin comparación disponible');
});
test('0 históricos no inventa tendencia; 1 histórico un punto y actual da dos', () => {
    const empty = make(); assert.equal(empty.rows().length, 0); assert.equal(empty.historyLine(), ''); assert.equal(empty.previewLine(), '');
    const state = make([saved(20)]); assert.equal(state.markers().length, 1); assert.equal(state.previewLine(), '');
    state.setValue('percentage', '72.50'); assert.equal(state.markers().length, 2); assert.equal(state.previewLine().split(' ').length, 2);
    assert.match(state.markers().at(-1).label, /09\/10\/2026 10:00 · Sin guardar/);
    assert.equal(state.comparison(), '+52.5 puntos porcentuales');
    assert.ok(state.markers().every(row => Number.isFinite(row.x) && Number.isFinite(row.y)));
});
test('null nunca crea punto; historial largo limita a diez y mantiene timestamps', () => {
    const history = Array.from({ length: 15 }, (_, i) => saved(i, `2026-10-09T${String(i).padStart(2, '0')}:00:00`));
    const state = make(history); assert.equal(state.history.length, 10); assert.equal(state.previous().value, 14);
    assert.equal(make([saved(null)]).markers().length, 0);
    state.setValue('percentage', '100'); assert.equal(state.markers().length, 11);
    state.selectedPoint = state.markers().at(-1); assert.match(state.selectedMarker().label, /Vista previa/);
});
test('popup cierra, devuelve foco y preserva todos los campos; resultado no añade preview', () => {
    const state = make([saved(10)], { meal: 'CENA', percentage: '80' }); const before = structuredClone(state.values);
    let focus = false; state.$refs = { trendDialog: { hidePopover() {} } }; state.$nextTick = fn => fn();
    state.trendReturnFocus = { focus() { focus = true; } }; state.trendOpen = true; state.closeTrend();
    assert.equal(focus, true); assert.equal(state.trendOpen, false); assert.deepEqual(state.values, before);
    const result = createIntakeCapture([saved(80)], { percentage: '90' }, '2026-10-09T10:00:00', 50, true);
    assert.equal(result.rows().length, 1); assert.equal(result.isDirty(), false);
});
test('limpieza devuelve foco a captura y al destruir elimina su listener', () => {
    const state = make(); const listeners = new Map(); let focused = false;
    const originalAdd = window.addEventListener, originalRemove = window.removeEventListener;
    window.addEventListener = (name, fn) => listeners.set(name, fn);
    window.removeEventListener = (name, fn) => { if (listeners.get(name) === fn) listeners.delete(name); };
    state.$nextTick = fn => fn(); state.$el = { closest: () => null, querySelector: () => ({ focus() { focused = true; } }) };
    try {
        state.init(); listeners.get('registro-campos-limpiados')(); assert.equal(focused, true);
        state.destroy(); assert.equal(listeners.size, 0);
    } finally { window.addEventListener = originalAdd; window.removeEventListener = originalRemove; }
});
