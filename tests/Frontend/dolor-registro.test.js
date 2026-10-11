import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
globalThis.window = { innerWidth: 1440, innerHeight: 900, addEventListener() {}, removeEventListener() {} };
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');
const { createPainCapture, normalizePainHistory, evaNumber } = await import('../../resources/frontend/scripts/modules/dolor-registro.js');
const make = (history = [], initial = {}) => createPainCapture(history,
    { eva: '', location: '', duration: '', unit: '', trigger: '', intervention: '', frequency: '', relief: '', response: '', ...initial }, '2026-10-09T10:00:00');
const saved = (intensidad, fecha_hora = '2026-10-09T08:00:00', ubicacion = 'Rodilla derecha') => ({ intensidad, fecha_hora, ubicacion });

const config = readFileSync('config/enfermeria.php', 'utf8');
const intensityBands = [...config.matchAll(/\['min' => (\d+), 'max' => (\d+), 'state' => '([^']+)', 'label' => '([^']+)'\]/g)]
    .map(([, min, max, state, label]) => ({ min: Number(min), max: Number(max), state, label }));

test('EVA recupera los rangos aprobados del servidor sin modificar la historia', () => {
    const state = createPainCapture([saved(6)], { eva: '' }, '2026-10-09T10:00:00', { intensityBands });
    const history = structuredClone(state.history);
    assert.equal(intensityBands.length, 4);
    for (const [value, band] of [['0', 'none'], ['1', 'low'], ['3', 'low'], ['4', 'medium'], ['6', 'medium'], ['7', 'high'], ['10', 'high']]) {
        state.values.eva = value;
        assert.equal(state.intensityPresentation().state, band);
        assert.ok(state.intensityPresentation().label.length > 0);
    }
    state.values.eva = ''; assert.equal(state.intensityPresentation().state, 'empty');
    assert.deepEqual(state.history, history);
    assert.equal(make([], { eva: '7' }).intensityPresentation().state, 'selected');
});

test('desplegables sincronizan captura, dirty y Livewire; personalizado conserva valor exacto', () => {
    const state = make([], { duration: '12.5', unit: 'horas', frequency: 'Al caminar' }); const updates = [];
    state.$wire = { $set(...args) { updates.push(args); } };
    state.chooseCaptureOption('duration', 'dolorDuracionValor', '__custom');
    assert.equal(state.values.duration, '12.5'); assert.equal(state.isDirty(), false);
    state.chooseCaptureOption('duration', 'dolorDuracionValor', '30');
    state.chooseCaptureOption('unit', 'dolorDuracionUnidad', 'minutos');
    state.chooseCaptureOption('frequency', 'dolorFrecuencia', 'Intermitente');
    assert.equal(state.isDirty(), true);
    assert.deepEqual(updates, [['dolorDuracionValor', '30', false], ['dolorDuracionUnidad', 'minutos', false], ['dolorFrecuencia', 'Intermitente', false]]);
    state.chooseCaptureOption('frequency', 'dolorFrecuencia', '__custom');
    assert.equal(state.values.frequency, 'Intermitente');
    state.chooseCaptureOption('eva', 'dolorEva', '10'); assert.equal(state.current(), null);
    state.chooseCaptureOption('duration', 'dolorFrecuencia', '60'); assert.equal(state.values.duration, '30');
    state.chooseCaptureOption('duration', 'dolorDuracionValor', ''); assert.equal(state.values.duration, '');
});

test('EVA vacía no se convierte en cero; solo enteros 0–10 generan preview', () => {
    for (const value of [null, undefined, '', ' ', '2.5', '-1', '11', Infinity, NaN, {}, true, '6abc']) assert.equal(evaNumber(value), null);
    for (const value of [0, '0', 6, '10']) assert.equal(evaNumber(value), Number(value));
    const state = make(); assert.equal(state.current(), null); assert.deepEqual(state.rows(), []);
    state.values.eva = '0'; assert.equal(state.rows()[0].value, 0); assert.equal(state.rows()[0].preview, true);
});
test('mapa añade, combina, elimina y limpia zonas sin borrar texto manual', () => {
    const state = make([], { location: 'Molestia al caminar' }); const updates = [];
    state.$wire = { $set(...args) { updates.push(args); } };
    state.toggleZone('Rodilla derecha'); state.toggleZone('Zona lumbar');
    assert.equal(state.values.location, 'Molestia al caminar, Rodilla derecha, Zona lumbar');
    state.toggleZone('Rodilla derecha'); assert.equal(state.values.location, 'Molestia al caminar, Zona lumbar');
    state.clearZones(); assert.equal(state.values.location, 'Molestia al caminar');
    assert.equal(updates.at(-1)[0], 'dolorUbicacion'); assert.equal(updates.at(-1)[2], false);
});
test('edición manual manda; no se infiere una selección ni se duplica ubicación', () => {
    const state = make(); state.toggleZone('Rodilla derecha');
    state.values.location = 'Zona lumbar'; state.syncManualLocation(); assert.deepEqual(state.zones, []);
    state.toggleZone('Zona lumbar'); assert.equal(state.values.location, 'Zona lumbar');
    state.values.location = ''; state.syncManualLocation(); assert.deepEqual(state.zones, []);
});
test('mapa rehúsa superar max120 sin truncar la captura; cuenta caracteres Unicode', () => {
    const state = make([], { location: 'á'.repeat(120) }); state.toggleZone('Rodilla derecha');
    assert.equal(state.values.location.length, 120); assert.equal(state.zones.length, 0);
    assert.match(state.locationError, /120/);
    state.values.location = 'á'.repeat(103); state.toggleZone('Rodilla derecha');
    assert.equal(state.locationLength(), 120); assert.equal(state.zones.length, 1);
});
test('historial plano rechaza ausencias, objetos, no finitos y fechas inválidas', () => {
    const bad = [null, {}, saved(null), saved(''), saved('NaN'), saved(Infinity), saved('4abc'), saved(11), saved(2, '2026-02-30T08:00:00'), saved(2, 'undefined'), saved(2, null)];
    assert.deepEqual(normalizePainHistory(bad), []);
    const rows = normalizePainHistory([saved('0'), saved('6', '2026-10-08T09:00:00', {})]);
    assert.equal(rows.length, 2); assert.equal(rows[0].location, ''); assert.equal(rows[1].value, 0);
    for (const value of ['null', 'NaN', 'undefined', null]) assert.equal(normalizePainHistory([saved(4, '2026-10-09T08:00:00', value)])[0].location, '');
});
test('0/1/1+preview/2 históricos usan puntos reales y conexión preview discontinua', () => {
    const empty = make(); assert.deepEqual(empty.markers(), []); assert.equal(empty.previewLine(), '');
    const state = make([saved(4)]); assert.equal(state.markers().length, 1); assert.equal(state.markers()[0].x, 196);
    state.values.eva = '6'; assert.equal(state.markers().length, 2); assert.equal(state.previewLine().split(' ').length, 2);
    assert.equal(state.history.length, 1);
    const two = make([saved(4), saved(3, '2026-10-09T09:00:00')]); assert.equal(two.historyLine().split(' ').length, 2);
    assert.equal(two.previewLine(), ''); assert.ok(two.markers().every(row => Number.isFinite(row.x) && Number.isFinite(row.y)));
});
test('eje temporal conserva intervalos y escala fija 0–10, incluso fechas iguales', () => {
    const state = make([saved(0, '2026-10-09T06:00:00'), saved(6, '2026-10-09T07:00:00'), saved(10, '2026-10-09T10:00:00')]);
    assert.deepEqual(state.markers().map(row => row.x), [44, 119, 344]);
    assert.deepEqual(state.markers().map(row => row.y), [170, 86, 30]);
    const same = make([saved(3), saved(4)]); assert.deepEqual(same.markers().map(row => row.x), [196, 196]);
});
test('delta expresa únicamente cambio numérico positivo, negativo o igual', () => {
    const state = make([saved(4)]); assert.equal(state.comparison(), 'Sin comparación disponible');
    for (const [value, message] of [['6', '+2 puntos'], ['3', '−1 punto'], ['4', 'Sin cambio']]) { state.values.eva = value; assert.equal(state.comparison(), message); }
    assert.equal(make([], { eva: '6' }).comparison(), 'Sin comparación disponible');
});
test('la selección del popup se cierra primero, devuelve foco y conserva toda la captura', () => {
    const state = make([saved(4)], { eva: '6', location: 'Rodilla derecha', duration: '30', unit: 'minutos', trigger: 'Caminar', intervention: 'Reposo' });
    let focus = 0; const dialog = { open: false, matches() { return this.open; }, showPopover() { this.open = true; }, hidePopover() { this.open = false; }, querySelector() { return { focus() {} }; } };
    state.$refs = { trendDialog: dialog }; state.$nextTick = callback => callback();
    const capture = { ...state.values }; state.openTrend({ focus() { focus++; } }); assert.equal(dialog.open, true);
    state.closeTrend(); assert.equal(dialog.open, false); assert.equal(focus, 1); assert.deepEqual(state.values, capture); assert.equal(state.history.length, 1);
    state.openTrend(); assert.equal(dialog.open, true);
});
test('mover, ocho bordes, resize y reset reutilizan límites del viewport de Signos', () => {
    const state = make([], { eva: '6' }); state.fitTrend(); const original = { ...state.trendBounds };
    assert.equal(original.width, 560); state.moveTrend(-100, 20); assert.equal(state.trendBounds.x, original.x - 100);
    for (const edge of ['w', 'e', 'n', 's', 'nw', 'ne', 'sw', 'se']) { state.resizeTrendEdge(edge, 10, 10); assert.ok(state.trendBounds.x >= 8 && state.trendBounds.y >= 8); }
    state.resizeTrend(9999, 9999); assert.equal(state.trendBounds.width, 1424); assert.equal(state.trendBounds.height, 884);
    state.resetTrend(); assert.deepEqual(state.trendBounds, original); assert.equal(state.values.eva, '6');
    window.innerWidth = 390; window.innerHeight = 844; state.fitTrend();
    assert.ok(state.trendBounds.width <= 374); assert.ok(state.trendBounds.x + state.trendBounds.width <= 382);
    window.innerWidth = 1440; window.innerHeight = 900;
});
test('dirty cubre todos los campos editables; cerrar un popup no altera el estado', () => {
    for (const key of ['eva', 'location', 'duration', 'unit', 'trigger', 'intervention', 'frequency', 'relief', 'response']) { const state = make(); assert.equal(state.isDirty(), false); state.values[key] = '1'; assert.equal(state.isDirty(), true); }
});

test('durante guardado bloquea cierres; popup y beforeunload conservan su comportamiento', () => {
    const listeners = new Map(), removals = [];
    const add = (name, handler) => listeners.set(name, handler);
    const remove = name => removals.push(name);
    window.addEventListener = add; window.removeEventListener = remove;
    let saving = true;
    const shell = { querySelector: () => saving, classList: { contains: () => true }, addEventListener: add, removeEventListener: remove };
    const state = make([], { eva: '6' }); state.$el = { closest: () => shell }; state.init();
    const event = (type, key) => ({ type, key, target: { closest: () => true }, prevented: false, stopped: false,
        preventDefault() { this.prevented = true; }, stopImmediatePropagation() { this.stopped = true; } });
    let close = event('click'); listeners.get('click')(close); assert.equal(close.stopped, true);
    let escape = event('keydown', 'Escape'); listeners.get('keydown')(escape); assert.equal(escape.prevented, true);
    state.trendOpen = true; escape = event('keydown', 'Escape'); listeners.get('keydown')(escape); assert.equal(escape.prevented, false);
    saving = false; close = event('click'); listeners.get('click')(close); assert.equal(close.stopped, false);
    let unload = event('beforeunload'); listeners.get('beforeunload')(unload); assert.equal(unload.prevented, false);
    state.values.location = 'Rodilla derecha'; listeners.get('beforeunload')(unload); assert.equal(unload.prevented, true);
    state.destroy(); assert.deepEqual(removals.sort(), ['beforeunload', 'click', 'keydown']);
    window.addEventListener = () => {}; window.removeEventListener = () => {};
});
test('markup conserva Escape propio, teclado de mapa y reduced motion', () => {
    const map = readFileSync('resources/views/components/ui/pain-body-map.blade.php', 'utf8');
    assert.match(map, /role="button" tabindex="0"/); assert.match(map, /@keydown.enter.prevent/); assert.match(map, /@keydown.space.prevent/);
    const popup = readFileSync('resources/views/components/ui/clinical-trend-window.blade.php', 'utf8');
    assert.match(popup, /@keydown.escape.prevent.stop="closeTrend\(\)"/);
    assert.match(readFileSync('resources/frontend/styles/design-system/patterns/dolor.css', 'utf8'), /prefers-reduced-motion: reduce/);
});

test('episodio filtra la comparación y gráfico; el contexto no altera captura ni dirty', () => {
    const episode = [ { ...saved(8, '2026-10-09T06:00:00'), codigo: 'VD_A' }, { ...saved(5), codigo: 'VD_B', origen: 'VD_A', respuesta: 'Menor intensidad' } ];
    const state = createPainCapture([saved(10)], { eva: '3', frequency: '', relief: '', response: '' }, '2026-10-09T10:00:00', { origin: 'VD_A', episode });
    assert.equal(state.previous().value, 5); assert.equal(state.comparison(), '−2 puntos');
    assert.deepEqual(state.rows().map(row => row.value), [8, 5, 3]);
    assert.equal(state.episode[1].response, 'Menor intensidad');
    assert.equal(state.isDirty(), false); state.values.response = 'Respuesta actual'; assert.equal(state.isDirty(), true);
});
test('tooltip de preview sigue EVA y desaparece si se limpia; histórico permanece intacto', () => {
    const state = make([saved(4)], { eva: '6' });
    state.selectedPoint = state.markers().find(point => point.preview);
    state.values.eva = '8';
    assert.equal(state.selectedMarker().value, 8);
    assert.match(state.selectedMarker().label, /EVA 8/);
    state.values.eva = '';
    assert.equal(state.selectedMarker(), null);
    state.selectedPoint = state.markers()[0];
    state.values.eva = '3';
    assert.equal(state.selectedMarker().value, 4);
});

test('gráfica mantiene máximo siete puntos y resultado no agrega preview duplicada', () => {
    const history = Array.from({ length: 7 }, (_, i) => ({ ...saved(i, '2026-10-09T0' + (i + 1) + ':00:00'), codigo: 'VD_' + i }));
    const state = make(history, { eva: '3' }); assert.equal(state.rows().length, 7);
    assert.equal(state.rows().at(-1).preview, true); assert.equal(state.history.length, 7);
    const result = createPainCapture(history, { eva: '3' }, '2026-10-09T10:00:00', { persisted: true });
    assert.equal(result.rows().length, 7); assert.equal(result.rows().some(row => row.preview), false);
});
