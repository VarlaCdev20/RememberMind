import { test } from 'node:test';
import assert from 'node:assert/strict';
import { rmHabitaciones } from '../../resources/frontend/scripts/modules/habitaciones-interactivas.js';

test('vista previa permanece dentro de la ventana y puede descartarse', () => {
    const anterior = globalThis.window;
    globalThis.window = { innerWidth: 390, innerHeight: 844 };
    try {
        const mapa = rmHabitaciones();
        mapa.mostrarVistaPrevia({ codigo: 'CAM_TEST', ocupante: 'Persona sintética' }, { getBoundingClientRect: () => ({ left: 340, bottom: 830 }) });
        assert.equal(mapa.posicion.left, '74px');
        assert.equal(mapa.posicion.top, '694px');
        assert.equal(mapa.vistaPrevia.codigo, 'CAM_TEST');
        mapa.ocultarVistaPrevia();
        assert.equal(mapa.vistaPrevia, null);
    } finally { globalThis.window = anterior; }
});

test('la vista previa permite mover el puntero al contenido sin desaparecer', async () => {
    const mapa = rmHabitaciones();
    mapa.vistaPrevia = { codigo: 'CAM_TEST' };
    mapa.diferirCierrePrevia();
    mapa.conservarVistaPrevia();
    await new Promise(resolve => setTimeout(resolve, 210));
    assert.equal(mapa.vistaPrevia.codigo, 'CAM_TEST');
    mapa.diferirCierrePrevia();
    await new Promise(resolve => setTimeout(resolve, 210));
    assert.equal(mapa.vistaPrevia, null);
    assert.equal(mapa.temporizadorPrevia, null);
});
