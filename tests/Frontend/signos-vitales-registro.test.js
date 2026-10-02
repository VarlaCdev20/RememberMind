import { test } from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = {};
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');

const crear = (history = [], initial = {}) => window.rmSignosRegistro(history, {
    sis: '', dia: '', fc: '', fr: '', temp: '', sat: '', glucosa: '', ...initial,
});

test('un panel cambia al signo activo y utiliza historial real en orden cronológico', () => {
    const estado = crear([
        { fecha: '01/10 10:24', fc: 75, sat: 89 },
        { fecha: '01/10 08:10', fc: 72, sat: 92 },
        { fecha: '30/09 18:20', fc: 68, sat: 94 },
    ]);
    assert.equal(estado.active, null);
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
    assert.equal(estado.errors.sat, 'La saturación no puede superar el 100 %.');
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
    assert.equal(estado.errors.temp, 'Ingresa un valor numérico válido.');
});

test('la gráfica no supera cinco puntos aunque el historial tenga más lecturas', () => {
    const historial = [6, 5, 4, 3, 2, 1].map((fc, index) => ({ fecha: `01/10 0${index}:00`, fc }));
    const estado = crear(historial, { fc: '7' });
    assert.equal(estado.records('fc').length, 5);
    assert.equal(estado.chartRows('fc').length, 5);
    assert.equal(estado.chartRows('fc').at(-1).preview, true);
});
