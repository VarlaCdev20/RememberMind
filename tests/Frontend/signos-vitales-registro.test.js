import { test } from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = {};
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

test('un panel cambia al signo activo y utiliza historial real en orden cronológico', () => {
    const estado = crear([
        { fecha: '01/10 10:24', fc: 75, sat: 89 },
        { fecha: '01/10 08:10', fc: 72, sat: 92 },
        { fecha: '30/09 18:20', fc: 68, sat: 94 },
    ]);
    assert.equal(estado.active, 'pa');
    estado.focus('fc');
    assert.equal(estado.active, 'fc');
    assert.deepEqual(estado.chartRows('fc').map(row => row.value), [68, 72, 75]);
    assert.equal(estado.chartMarkers('fc').length, 3);
    estado.focus('sat');
    assert.equal(estado.active, 'sat');
    assert.deepEqual(estado.chartRows('sat').map(row => row.value), [94, 92, 89]);
});

test('la vista previa válida se distingue del historial y un dato inválido se excluye', () => {
    const estado = crear([{ fecha: '01/10 08:10', sat: 92 }], { sat: '89' });
    estado.focus('sat');
    assert.equal(estado.change('sat'), '-3 %');
    assert.deepEqual(estado.chartRows('sat').map(row => row.preview), [false, true]);
    estado.values.sat = '135';
    estado.validate('sat');
    assert.equal(estado.errors.sat, 'La saturación de oxígeno no puede superar 100 %.');
    assert.equal(estado.validPreview('sat'), false);
    assert.deepEqual(estado.chartRows('sat').map(row => row.preview), [false]);
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
    assert.match(estado.chartPoints('fc'), /^18,\d+ 282,\d+$/);
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
    assert.match(estado.chartMarkers('glucosa')[1].markerLabel, /Crítico/);
    assert.equal(estado.toneOf('glucosa'), 'danger');
    estado.values.glucosa = 'Infinity';
    assert.equal(estado.hasCardError('glucosa'), true);
    assert.equal(estado.validPreview('glucosa'), false);
    assert.equal(estado.chartRows('glucosa').length, 1);
});
