import { test } from 'node:test';
import assert from 'node:assert/strict';
import { storedEvidence } from '../../resources/frontend/scripts/modules/expert-stored-evidence.js';

test('datos guardados: búsqueda sin acentos y paginación sin modificar los originales', () => {
    const rows = Array.from({ length: 19 }, (_, i) => Object.freeze({ id: String(i), tipo: i % 2 ? 'Valoración original' : 'Respuesta instrumental', dato: i === 0 ? 'Memória' : 'Valor', valor: i === 0 ? '0' : String(i), fecha: '19/11/2026' }));
    const original = JSON.stringify(rows);
    const ui = storedEvidence(Object.freeze(rows));
    assert.equal(ui.paginas, 3);
    assert.equal(ui.visibles.length, 8);
    ui.cambiarPagina(99);
    assert.equal(ui.pagina, 3);
    assert.equal(ui.rango, '17–19 de 19 datos');
    ui.busqueda = 'memoria'; ui.filtrar();
    assert.equal(ui.pagina, 1);
    assert.equal(ui.visibles[0].valor, '0');
    ui.busqueda = ''; ui.tipo = 'Valoración original'; ui.filtrar();
    assert.equal(ui.filtradas.length, 9);
    ui.cambiarPagina(-99);
    assert.equal(ui.pagina, 1);
    ui.busqueda = 'ausente'; ui.filtrar();
    assert.equal(ui.rango, '0 datos');
    assert.equal(ui.paginas, 1);
    assert.deepEqual(ui.visibles, []);
    assert.equal(JSON.stringify(rows), original);
});
