import { test } from 'node:test';
import assert from 'node:assert/strict';
import { rmResidentes } from '../../resources/frontend/scripts/modules/residentes-interactivos.js';

test('detalle de cama restaura el foco sin cambiar ocupaciones', () => {
    const panel = rmResidentes();
    const foco = [];
    panel.$nextTick = callback => callback();
    panel.$refs = { cerrarCama: { focus: () => foco.push('cerrar') } };
    panel.abrirCama({ codigo: 'CAM_PRUEBA', estado: 'Ocupada' }, { focus: () => foco.push('cama') });
    assert.equal(panel.cama.codigo, 'CAM_PRUEBA');
    panel.cerrarCama();
    assert.equal(panel.cama, null);
    assert.deepEqual(foco, ['cerrar', 'cama']);
});

test('actualización de alojamiento vuelve a consultar la misma vista y filtros', () => {
    const previous = globalThis.window;
    const visitas = [];
    globalThis.window = { location: { href: 'http://localhost/residentes?vista=camas&piso=2&page=3' }, Livewire: { navigate: url => visitas.push(url) } };
    try {
        const panel = rmResidentes();
        panel.cama = { codigo: 'CAM_PRUEBA' };
        panel.actualizar();
        assert.equal(panel.cama, null);
        assert.equal(panel.loading, true);
        assert.deepEqual(visitas, [window.location.href]);
    } finally { globalThis.window = previous; }
});
