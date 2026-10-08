import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

globalThis.window = { innerWidth: 1440, innerHeight: 900 };
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');

const limits = {
    sis: { min: 1, max: 400, decimales: 0 }, dia: { min: 1, max: 400, decimales: 0 },
    fc: { min: 1, max: 300, decimales: 0 }, fr: { min: 1, max: 100, decimales: 0 },
    temp: { min: 25, max: 45, decimales: 1 }, sat: { min: 1, max: 100, decimales: 2 },
    glucosa: { min: 1, max: 999999.99, decimales: 2 },
};
const crear = (history = [], initial = {}, bands = {}, tones = {}) => window.rmSignosRegistro(history, {
    sis: '', dia: '', fc: '', fr: '', temp: '', sat: '', glucosa: '', ...initial,
}, limits, bands, tones);
const popover = () => ({ open: false, matches() { return this.open; }, showPopover() { this.open = true; }, hidePopover() { this.open = false; } });

test('cerrar la gráfica conserva captura crítica, evaluación e historial y devuelve el foco', () => {
    const estado = crear([{ fecha: '07/10 08:00', fc: 70 }], { fc: '', fecha_hora: '2026-10-07T10:17' });
    estado.values.fc = '135';
    estado.syncEvaluation({ valores: { ...estado.values }, evaluacion: { resultados: [{ variable: 'frecuencia_cardiaca', severidad: 'CRITICO' }] } });
    const historia = estado.history;
    let focoRestaurado = false;
    estado.$nextTick = callback => callback();
    const trigger = { focus() { focoRestaurado = true; } };
    estado.$refs = { trendDialog: popover() };
    estado.activateMeasurement('fc');
    assert.equal(estado.trendOpen, false, 'Actualizar el signo por escritura no reabre una gráfica cerrada');
    estado.openTrend('fc', trigger);
    assert.equal(estado.$refs.trendDialog.open, true);
    estado.closeTrend();
    assert.equal(estado.$refs.trendDialog.open, false);
    assert.equal(estado.trendOpen, false);
    assert.equal(estado.values.fc, '135');
    assert.equal(estado.toneOf('fc'), 'danger');
    assert.equal(estado.isDirty(), true);
    assert.equal(estado.history, historia);
    assert.equal(focoRestaurado, true);
});

test('la gráfica representa únicamente el signo elegido y rechaza una selección desconocida', () => {
    const estado = crear();
    estado.$nextTick = callback => callback();
    estado.$refs = { trendDialog: popover() };
    estado.openTrend('desconocido');
    assert.equal(estado.trendOpen, false);
    estado.openTrend('sat');
    assert.equal(estado.active, 'sat');
    assert.equal(estado.trendOpen, true);
    estado.openTrend('dia');
    assert.equal(estado.active, 'pa', 'PA conserva ambas series');
});

test('mover y ajustar tamaño mantiene la ventana visible y conserva mediciones', () => {
    const estado = crear([], { fc: '135' });
    estado.fitTrend();
    const original = { ...estado.trendBounds };
    estado.moveTrend(-100, 30);
    assert.equal(estado.trendBounds.x, original.x - 100);
    assert.equal(estado.trendBounds.y, original.y + 30);
    estado.resizeTrend(64, 48);
    assert.equal(estado.trendBounds.width, original.width + 64);
    assert.equal(estado.trendBounds.height, original.height + 48);
    estado.moveTrend(-99999, -99999);
    assert.equal(estado.trendBounds.x, 8);
    assert.equal(estado.trendBounds.y, 8);
    estado.resizeTrend(99999, 99999);
    assert.equal(estado.trendBounds.width, window.innerWidth - 16);
    assert.equal(estado.trendBounds.height, window.innerHeight - 16);
    estado.resizeTrend(-99999, -99999);
    assert.equal(estado.trendBounds.width, 360);
    assert.equal(estado.trendBounds.height, 240);
    assert.equal(estado.values.fc, '135');
    estado.resetTrend();
    assert.deepEqual(estado.trendBounds, original);
});

test('entrar a otro campo cambia la misma gráfica sin robar foco ni perder las mediciones', () => {
    const estado = crear([], { fc: '75', temp: '36.5' });
    estado.$nextTick = callback => callback();
    estado.$refs = { trendDialog: popover() };
    let focusCalls = 0;
    const input = { focus() { focusCalls++; } };
    estado.focusMeasurement('fc', input);
    assert.equal(estado.trendOpen, true);
    assert.equal(estado.active, 'fc');
    const bounds = { ...estado.trendBounds };
    estado.focusMeasurement('temp', input);
    assert.equal(estado.active, 'temp');
    assert.deepEqual(estado.trendBounds, bounds, 'Cambiar de signo conserva la posición y el tamaño');
    assert.equal(estado.values.fc, '75');
    assert.equal(estado.values.temp, '36.5');
    assert.equal(focusCalls, 0, 'Abrir o cambiar la gráfica no mueve el foco del campo');
});

test('cerrar la gráfica devuelve el foco al campo sin reabrirla por el evento focus', () => {
    const estado = crear();
    estado.$nextTick = callback => callback();
    estado.$refs = { trendDialog: popover() };
    const input = { focus() { estado.focusMeasurement('fc', input); } };
    estado.focusMeasurement('fc', input);
    estado.closeTrend();
    assert.equal(estado.trendOpen, false);
    assert.equal(estado.$refs.trendDialog.open, false);
    estado.focusMeasurement('temp', input);
    assert.equal(estado.trendOpen, true);
    assert.equal(estado.active, 'temp');
});

test('la ventana se adapta a móvil y a cambios de viewport sin salirse', () => {
    const estado = crear();
    estado.fitTrend();
    const previous = { width: window.innerWidth, height: window.innerHeight };
    try {
        window.innerWidth = 390; window.innerHeight = 640;
        estado.fitTrend();
        assert.equal(estado.trendBounds.width, 374);
        assert.equal(estado.trendBounds.height, 280);
        assert.equal(estado.trendBounds.x, 8);
        assert.equal(estado.trendBounds.y, 352);
    } finally { window.innerWidth = previous.width; window.innerHeight = previous.height; }
});

test('ampliar el viewport recoloca la gráfica al lado sin reiniciar su tamaño ni captura', () => {
    const estado = crear([], { fc: '135' });
    estado.fitTrend();
    estado.moveTrend(-100, 20);
    const previous = { width: window.innerWidth, height: window.innerHeight };
    const height = estado.trendBounds.height;
    try {
        window.innerWidth = 1920;
        estado.fitTrend();
        assert.equal(estado.trendBounds.x, 1460);
        assert.equal(estado.trendBounds.width, 440);
        assert.equal(estado.trendBounds.height, height);
        assert.equal(estado.values.fc, '135');
        estado.moveTrend(-24, 0);
        assert.equal(estado.trendBounds.x, 1436);
    } finally { window.innerWidth = previous.width; window.innerHeight = previous.height; }
});

test('arrastrar los costados cambia el ancho y mantiene fijo el costado opuesto', () => {
    const estado = crear();
    estado.fitTrend({ x: 400, y: 100, width: 560, height: 600 });
    estado.resizeTrendEdge('w', 100, 80);
    assert.deepEqual(estado.trendBounds, { x: 500, y: 100, width: 460, height: 600 });
    assert.equal(estado.trendBounds.x + estado.trendBounds.width, 960);
    estado.resizeTrendEdge('w', 99999, 0);
    assert.equal(estado.trendBounds.width, 360);
    assert.equal(estado.trendBounds.x + estado.trendBounds.width, 960);
    estado.resizeTrendEdge('e', 99999, 0);
    assert.equal(estado.trendBounds.x, 600);
    assert.equal(estado.trendBounds.x + estado.trendBounds.width, window.innerWidth - 8);
    estado.resizeTrendEdge('w', -99999, 0);
    assert.equal(estado.trendBounds.x, 8);
});

test('bordes superior e inferior y esquinas ajustan alto y ancho sin mover el borde opuesto', () => {
    const estado = crear();
    estado.fitTrend({ x: 400, y: 100, width: 560, height: 600 });
    estado.resizeTrendEdge('n', 200, 80);
    assert.deepEqual(estado.trendBounds, { x: 400, y: 180, width: 560, height: 520 });
    estado.resizeTrendEdge('s', 200, -100);
    assert.deepEqual(estado.trendBounds, { x: 400, y: 180, width: 560, height: 420 });
    estado.resizeTrendEdge('nw', 40, 30);
    assert.deepEqual(estado.trendBounds, { x: 440, y: 210, width: 520, height: 390 });
    estado.resizeTrendEdge('se', 50, 40);
    assert.deepEqual(estado.trendBounds, { x: 440, y: 210, width: 570, height: 430 });
});

test('arrastre y redimensionado capturan el puntero correcto y terminan sin listeners pendientes', () => {
    const estado = crear();
    estado.trendOpen = true;
    estado.fitTrend();
    const target = { focus() {}, setPointerCapture() {}, hasPointerCapture() { return true; }, releasePointerCapture() {} };
    const event = { button: 0, pointerId: 2, clientX: 100, clientY: 100, currentTarget: target, preventDefault() {}, stopPropagation() {} };
    const original = { ...estado.trendBounds };
    estado.startTrendPointer(event, 'move');
    estado.updateTrendPointer({ ...event, pointerId: 3, clientX: 50 });
    assert.equal(estado.trendBounds.x, original.x);
    estado.updateTrendPointer({ ...event, clientX: 50 });
    assert.equal(estado.trendBounds.x, original.x - 50);
    estado.endTrendPointer(event);
    assert.equal(estado.trendPointer, null);
    estado.startTrendPointer(event, 'resize');
    estado.updateTrendPointer({ ...event, clientX: 130, clientY: 120 });
    assert.equal(estado.trendBounds.width, original.width + 30);
    assert.equal(estado.trendBounds.height, original.height + 20);
    estado.endTrendPointer(event);
    assert.equal(estado.trendPointer, null);
});

test('limpiar reinicia captura errores y vista previa conservando fecha e historial', () => {
    const estado = crear([{ fecha: '07/10 08:00', fc: 70 }], { obs: '', fecha_hora: '2026-10-07T10:17' });
    estado.values.fc = '72';
    estado.values.obs = 'Nota temporal';
    estado.values.temp = '6';
    estado.validate('temp');
    estado.syncEvaluation({ valores: { ...estado.values }, evaluacion: { resultados: [{ variable: 'frecuencia_cardiaca', severidad: 'NORMAL' }] } });
    assert.equal(estado.isDirty(), true);
    const historia = estado.history;
    const blancos = { ...estado.initialValues };
    estado.resetCapture({ valores: blancos });
    assert.deepEqual(estado.values, blancos);
    assert.equal(estado.values.fecha_hora, '2026-10-07T10:17');
    assert.equal(estado.isDirty(), false);
    assert.equal(estado.captureCleared, true);
    assert.deepEqual(estado.touched, {});
    assert.deepEqual(estado.errors, {});
    assert.equal(estado.validPreview('fc'), false);
    assert.equal(estado.evaluationCurrent('fc'), false);
    assert.equal(estado.history, historia);
    assert.equal(estado.chartRows('fc').length, 1);
    estado.activateMeasurement('fc');
    assert.equal(estado.captureCleared, false);
});

test('SpO2 sin objetivo muestra estado informativo y respeta evaluación médica cuando existe', () => {
    const estado = crear([], { sat: '98', fecha_hora: '2026-10-07T10:17' });
    assert.equal(estado.awaitingMedicalGoal('sat'), false);
    const evaluar = severidad => estado.syncEvaluation({ valores: { ...estado.values }, evaluacion: { resultados: [{ variable: 'saturacion_oxigeno', severidad }] } });
    evaluar(null);
    assert.equal(estado.awaitingMedicalGoal('sat'), true);
    assert.equal(estado.toneOf('sat'), 'neutral');
    assert.equal(estado.toneLabel('sat'), 'Sin objetivo médico');
    evaluar('OBJETIVO_PERSONALIZADO');
    assert.equal(estado.awaitingMedicalGoal('sat'), false);
    assert.equal(estado.toneOf('sat'), 'target');
    evaluar('CRITICO');
    assert.equal(estado.toneOf('sat'), 'danger');
    assert.equal(estado.awaitingMedicalGoal('sat'), false);
    estado.values.sat = '101';
    assert.equal(estado.awaitingMedicalGoal('sat'), false);
});

test('el contexto real de Alpine conserva valores numéricos e historia de PA', () => {
    const livewire = readFileSync(new URL('../../vendor/livewire/livewire/dist/livewire.js', import.meta.url), 'utf8');
    const start = livewire.indexOf('  function mergeProxies(objects)');
    const end = livewire.indexOf('  function collapseProxies()', start);
    assert.ok(start >= 0 && end > start, 'La prueba necesita el contexto de Alpine instalado');
    const mergeProxies = runInNewContext(`${livewire.slice(start, end)}; mergeProxies;`);
    const estado = crear([
        { fecha: '07/10 10:00', sis: 125, dia: 82, temp: 36.8 },
        { fecha: '07/10 09:00', sis: 120, dia: 80, temp: 36.7 },
    ]);
    const contexto = mergeProxies([{}, estado]);

    assert.equal(contexto.labelOf(estado.history[0], 'temp'), '36.8');
    assert.equal(contexto.records('fc').length, 0);
    assert.equal(contexto.chartRows('pa').length, 2);
    assert.match(contexto.chartPoints('pa'), /^\d/);
    assert.match(contexto.chartPoints('pa', 'dia'), /^\d/);
});

test('un panel cambia al signo activo y utiliza historial real en orden cronológico', () => {
    const estado = crear([
        { fecha: '01/10 10:24', fc: 75, sat: 89 },
        { fecha: '01/10 08:10', fc: 72, sat: 92 },
        { fecha: '30/09 18:20', fc: 68, sat: 94 },
    ]);
    assert.equal(estado.active, 'pa');
    estado.activateMeasurement('fc');
    assert.equal(estado.active, 'fc');
    assert.deepEqual(estado.chartRows('fc').map(row => row.value), [68, 72, 75]);
    assert.equal(estado.chartMarkers('fc').length, 3);
    estado.activateMeasurement('sat');
    assert.equal(estado.active, 'sat');
    assert.deepEqual(estado.chartRows('sat').map(row => row.value), [94, 92, 89]);
});

test('la vista previa válida se distingue del historial y un dato inválido se excluye', () => {
    const estado = crear([{ fecha: '01/10 08:10', sat: 92 }], { sat: '89' });
    estado.activateMeasurement('sat');
    assert.equal(estado.change('sat'), '-3 puntos porcentuales');
    assert.deepEqual(estado.chartRows('sat').map(row => row.preview), [false, true]);
    estado.values.sat = '135';
    estado.validate('sat');
    assert.equal(estado.errors.sat, 'La saturación de oxígeno no puede superar 100 %.');
    assert.equal(estado.validPreview('sat'), false);
    assert.deepEqual(estado.chartRows('sat').map(row => row.preview), [false]);
});

test('el estado clínico anterior desaparece al editar y vuelve solo con la evaluación nueva', () => {
    const estado = crear([], { fc: '135' }, {}, { fc: 'danger' });
    assert.equal(estado.toneOf('fc'), 'danger');
    estado.values.fc = '72';
    assert.equal(estado.toneOf('fc'), 'neutral');
    assert.equal(estado.toneLabel('fc'), 'Evaluando lectura');
    estado.syncEvaluation({
        valores: { fc: '72' },
        evaluacion: { resultados: [{ variable: 'frecuencia_cardiaca', severidad: 'NORMAL' }] },
    });
    assert.equal(estado.toneOf('fc'), 'success');
    estado.values.fc = '135';
    assert.equal(estado.toneOf('fc'), 'neutral');
});

test('presión incompleta y decimales fuera de escala muestran error local', () => {
    const estado = crear([], { sis: '120' });
    estado.validate('sis');
    assert.equal(estado.errors.dia, 'Completa también la presión diastólica.');
    assert.equal(estado.validPreview('pa'), false);
    estado.values.dia = '80';
    estado.validate('dia');
    assert.equal(estado.validPreview('pa'), true);
    estado.values.temp = '37.23';
    estado.validate('temp');
    assert.equal(estado.errors.temp, 'Revisa el valor ingresado. Introduce un número válido.');
});

test('historial parcial descarta null, vacíos y valores no finitos sin dibujar NaN', () => {
    const estado = crear([
        { fecha: '04/10 08:00', fc: null },
        { fecha: '03/10 08:00', fc: '', sat: 93 },
        { fecha: '02/10 08:00', fc: 'NaN' },
        { fecha: '01/10 08:00', fc: 'Infinity' },
        { fecha: '30/09 08:00', fc: 72 },
    ], { fc: '96' });
    assert.deepEqual(estado.records('fc').map(row => row.fc), [72]);
    assert.equal(estado.previous('fc').fc, 72);
    assert.equal(estado.change('fc'), '+24 lpm');
    assert.deepEqual(estado.chartRows('fc').map(row => row.value), [72, 96]);
    const puntos = estado.chartPoints('fc').split(' ').map(point => point.split(',').map(Number));
    assert.equal(puntos.length, 2);
    assert.ok(puntos.every(([x, y]) => Number.isFinite(x) && Number.isFinite(y)
        && x >= estado.plot.left && x <= estado.plot.right && y >= estado.plot.top && y <= estado.plot.bottom));
});

test('presión arterial dibuja sistólica y diastólica con dos lecturas', () => {
    const estado = crear([{ fecha: '04/10 08:00', sis: 120, dia: 80 }], { sis: '182', dia: '122' });
    assert.equal(estado.chartRows('pa').length, 2);
    assert.equal(estado.chartPoints('pa').split(' ').length, 2);
    assert.equal(estado.chartPoints('pa', 'dia').split(' ').length, 2);
    assert.notEqual(estado.chartPoints('pa'), estado.chartPoints('pa', 'dia'));
    assert.equal(estado.chartMarkers('pa', 'dia')[1].markerLabel, 'Diastólica 122 mmHg');
});

test('la banda de objetivo médico usa límites estructurados y no interpreta texto libre', () => {
    const estado = crear([{ fecha: '04/10 08:00', sat: 89 }], { sat: '90' }, {
        sat: { min: 88, max: 92 }, fc: { min: null, max: 90 },
    });
    assert.equal(estado.chartBands('sat').length, 1);
    assert.equal(estado.chartBands('sat')[0].label, 'Objetivo médico 88–92 %');
    assert.ok(estado.chartBands('sat')[0].height > 0);
    assert.deepEqual(estado.chartBands('fc'), []);
});

test('límites técnicos vienen del servidor y una temperatura de 6 se trata como error de captura', () => {
    const estado = crear([], { temp: '6' });
    estado.validate('temp');
    assert.match(estado.errors.temp, /Comprueba que no falte un dígito/);
    assert.equal(estado.validPreview('temp'), false);
    estado.values.temp = '39.4';
    estado.validate('temp');
    assert.equal(estado.errors.temp, '');
    assert.equal(estado.validPreview('temp'), true);
});

test('la gráfica no supera cinco puntos aunque el historial tenga más lecturas', () => {
    const historial = [6, 5, 4, 3, 2, 1].map((fc, index) => ({ fecha: `01/10 0${index}:00`, fc }));
    const estado = crear(historial, { fc: '7' });
    assert.equal(estado.records('fc').length, 5);
    assert.equal(estado.chartRows('fc').length, 5);
    assert.equal(estado.chartRows('fc').at(-1).preview, true);
});

test('una sola medición histórica muestra el valor y la vista previa lleva el estado evaluado por el servidor', () => {
    const estado = crear([{ fecha: '01/10 13:34', glucosa: 105 }], {}, {}, { glucosa: 'danger' });
    assert.match(estado.emptyHistoryMessage('glucosa'), /Última medición: 01\/10 13:34 · 105 mg\/dL/);
    assert.equal(estado.chartPoints('glucosa'), '');
    estado.values.glucosa = '48';
    assert.equal(estado.chartRows('glucosa').length, 2);
    assert.equal(estado.toneOf('glucosa'), 'neutral');
    estado.syncEvaluation({
        valores: { glucosa: '48' },
        evaluacion: { resultados: [{ variable: 'glucemia', severidad: 'CRITICO' }] },
    });
    assert.match(estado.chartMarkers('glucosa')[1].markerLabel, /Crítico/);
    assert.equal(estado.toneOf('glucosa'), 'danger');
    estado.values.glucosa = 'Infinity';
    assert.equal(estado.hasCardError('glucosa'), true);
    assert.equal(estado.validPreview('glucosa'), false);
    assert.equal(estado.chartRows('glucosa').length, 1);
});

test('el historial malformado no llega a etiquetas, comparaciones, tooltip ni gráfica', () => {
    const estado = crear([
        { fecha: 'null', glucosa: null, fc: 'NaN' },
        { fecha: 'undefined', glucosa: 'Infinity', fc: [] },
        { fecha: null, glucosa: '105', fc: false },
    ], { glucosa: '120' });
    assert.deepEqual(estado.records('glucosa').map(row => row.glucosa), [105]);
    assert.equal(estado.records('glucosa')[0].fecha, 'Registro previo');
    assert.equal(estado.change('glucosa'), '+15 mg/dL');
    assert.equal(estado.chartRows('glucosa').length, 2);
    assert.equal(estado.chartMarkers('glucosa').some(row => /null|NaN|undefined|Infinity/.test(row.markerLabel + row.date)), false);
    assert.equal(estado.records('fc').length, 0);
    assert.equal(estado.chartPoints('fc'), '');
    assert.equal(estado.chartRange('fc').min, 0);
    assert.match(estado.emptyHistoryMessage('fc'), /No hay mediciones anteriores/);
});

test('otra entrada inválida mantiene el tono crítico y el historial respeta la hora clínica', () => {
    const estado = crear([
        { fecha: '06/10/2026 09:00', fecha_hora: '2026-10-06T09:00:00', fc: 72 },
        { fecha: '06/10/2026 08:00', fecha_hora: '2026-10-06T08:00:00', fc: 70 },
    ], { fc: '135', temp: '6', fecha_hora: '2026-10-06T08:30' });
    estado.syncEvaluation({ evaluacion: { resultados: [{ variable: 'frecuencia_cardiaca', severidad: 'CRITICO' }] }, valores: { ...estado.values } });
    assert.equal(estado.toneOf('fc'), 'danger');
    assert.equal(estado.toneOf('temp'), 'neutral');
    assert.equal(estado.previous('fc').fc, 70);
    assert.equal(estado.change('fc'), '+65 lpm');
    estado.values.fecha_hora = '2026-10-06T09:30';
    assert.equal(estado.toneOf('fc'), 'neutral');
    assert.equal(estado.previous('fc').fc, 72);
});

test('la escala deja margen alrededor de puntos y objetivos y muestra valores finitos', () => {
    const estado = crear([{ fecha: '07/10 08:00', temp: 36.6 }, { fecha: '07/10 07:00', temp: 36.5 }], { temp: '36.7' }, { temp: { min: 36, max: 37 } });
    const rango = estado.chartRange('temp');
    assert.ok(rango.min < 36 && rango.max > 37);
    assert.ok(estado.chartTicks('temp').length >= 3 && estado.chartTicks('temp').length <= 7);
    assert.ok(estado.chartTicks('temp').every(tick => Number.isFinite(tick.y) && !/NaN|Infinity/.test(tick.label)));
    assert.ok(estado.chartMarkers('temp').every(row => row.y > estado.plot.top && row.y < estado.plot.bottom));
    assert.equal(estado.chartSummary('fc'), 'Sin mediciones para representar');
});

test('el eje respeta intervalos temporales reales y ordena registros con timestamp válido', () => {
    const estado = crear([
        { fecha: '07/10 08:00', fecha_hora: '2026-10-07T08:00:00', fc: 70 },
        { fecha: '07/10 11:00', fecha_hora: '2026-10-07T11:00:00', fc: 74 },
        { fecha: '07/10 08:30', fecha_hora: '2026-10-07T08:30:00', fc: 72 },
    ], { fc: '76', fecha_hora: '2026-10-07T12:00' });
    assert.deepEqual(estado.chartRows('fc').map(row => row.value), [70, 72, 74, 76]);
    const puntos = estado.chartMarkers('fc');
    assert.equal(puntos[1].x - puntos[0].x, (estado.plot.right - estado.plot.left) / 8);
    assert.match(estado.chartSummary('fc'), /Intervalos de tiempo reales/);
    assert.equal(estado.chartHistoryPoints('fc').split(' ').length, 3);
    assert.equal(estado.chartPreviewPoints('fc').split(' ').length, 2);
    assert.deepEqual(estado.chartTimeLabels('fc').map(row => row.time), ['08:00', '12:00']);
});

test('una lectura aislada y fechas iguales o inválidas no generan una tendencia ficticia', () => {
    const solo = crear([], { fc: '72', fecha_hora: '2026-10-07T12:00' });
    assert.match(solo.chartSummary('fc'), /Una lectura/);
    assert.equal(solo.chartHistoryPoints('fc'), '');
    assert.equal(solo.chartPreviewPoints('fc'), '');
    assert.equal(solo.chartMarkers('fc').length, 1);
    const mismaHora = crear([{ fecha: '07/10 12:00', fecha_hora: '2026-10-07T12:00:00', fc: 70 }], { fc: '72', fecha_hora: '2026-10-07T12:00' });
    assert.equal(mismaHora.chartRows('fc').length, 2);
    assert.equal(mismaHora.chartMarkers('fc')[0].x, mismaHora.chartMarkers('fc')[1].x);
    assert.equal(mismaHora.chartTimeLabels('fc').length, 1);
    const invalida = crear([{ fecha: 'Registro previo', fecha_hora: '2026-02-30T08:00:00', fc: 70 }], { fc: '72', fecha_hora: '2026-10-07T12:00' });
    assert.equal(invalida.temporalChart('fc'), false);
    assert.match(invalida.chartSummary('fc'), /tiempo no disponible/);
    assert.equal(invalida.chartTimeLabels('fc')[0].time, 'Fecha no disponible');
});

test('comparación de PA distingue cambios opuestos y SpO2 usa puntos porcentuales', () => {
    const estado = crear([{ fecha: '07/10 08:00', sis: 120, dia: 80, sat: 95 }], { sis: '125', dia: '78', sat: '98' });
    assert.deepEqual(estado.comparisonRows('pa').map(row => [row.label, row.direction, row.delta]), [
        ['Sistólica', 'Subió', 5], ['Diastólica', 'Bajó', -2],
    ]);
    assert.equal(estado.comparisonRows('sat')[0].difference, '+3 puntos porcentuales');
    estado.values.dia = '80';
    assert.equal(estado.comparisonRows('pa')[1].direction, 'Sin cambio');
    estado.values.sis = '';
    assert.deepEqual(estado.comparisonRows('pa'), []);
});

test('áreas independientes conservan la escala y no rellenan lecturas aisladas', () => {
    const estado = crear([{ fecha: '07/10 08:00', sis: 120, dia: 80 }, { fecha: '07/10 07:00', sis: 118, dia: 78 }], { sis: '125', dia: '76' });
    for (const field of ['value', 'dia']) {
        const polygon = estado.chartAreaPoints('pa', field).split(' ').map(point => point.split(',').map(Number));
        assert.equal(polygon.length, 4);
        assert.equal(polygon[0][1], estado.plot.bottom);
        assert.equal(polygon.at(-1)[1], estado.plot.bottom);
        assert.equal(polygon[1][1], estado.chartMarkers('pa', field)[0].y);
        assert.ok(polygon.every(([x,y]) => Number.isFinite(x) && Number.isFinite(y)));
        assert.equal(estado.chartAreaPoints('pa', field, true).split(' ').length, 4);
    }
    const aislada = crear([], { fc: '72' });
    assert.equal(aislada.chartAreaPoints('fc', 'value', true), '');
    const simultanea = crear([{ fecha_hora: '2026-10-07T12:00:00', fc: 70 }], { fc: '72', fecha_hora: '2026-10-07T12:00' });
    assert.equal(simultanea.chartAreaPoints('fc', 'value', true), '');
});

test('etiquetas de selección muestran las dos presiones sin desbordar la gráfica', () => {
    const estado = crear([{ sis: 120, dia: 80 }], { sis: '125', dia: '78' });
    assert.deepEqual(estado.chartTooltip('pa'), []);
    estado.selectedPoint = estado.chartMarkers('pa', 'dia').at(-1);
    const tips = estado.chartTooltip('pa');
    assert.deepEqual(tips.map(tip => tip.label), ['Sistólica 125 mmHg', 'Diastólica 78 mmHg']);
    assert.ok(tips.every(tip => tip.x >= estado.plot.left && tip.x + 142 <= estado.plot.right && tip.y >= estado.plot.top && tip.y + 24 <= estado.plot.bottom));
    assert.ok(tips[1].y >= tips[0].y + 26);
    estado.activateMeasurement('fc');
    assert.deepEqual(estado.chartTooltip('fc'), []);
});

test('atención iniciada conserva el rojo histórico y una nueva hora actualiza la banda del objetivo', () => {
    const estado = crear([{ fecha: '06/10/2026 08:00', fecha_hora: '2026-10-06T08:00:00', fc: 135,
        resultados: [{ variable: 'frecuencia_cardiaca', severidad: 'CRITICO' }], alerta_estado: 'EN_ATENCION' },
        { fecha: '06/10/2026 08:30', fecha_hora: '2026-10-06T08:30:50', fc: 72 }],
        { fecha_hora: '2026-10-06T08:30' }, { fc: { min: 60, max: 80 } });
    assert.equal(estado.records('fc').length, 1);
    assert.equal(estado.chartMarkers('fc')[0].tone, 'danger');
    assert.match(estado.chartMarkers('fc')[0].markerLabel, /Crítico.*EN ATENCION/);
    estado.syncEvaluation({ evaluacion: { resultados: [] }, valores: { ...estado.values }, bandas: { fc: { min: 100, max: 110 } } });
    assert.equal(estado.chartBands('fc')[0].min, 100);
    estado.syncEvaluation({ evaluacion: { resultados: [] }, valores: { ...estado.values }, bandas: {} });
    assert.equal(estado.chartBands('fc').length, 0);
});
