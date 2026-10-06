import { test } from 'node:test';
import assert from 'node:assert/strict';
import { rmAdmisiones } from '../../resources/frontend/scripts/modules/admisiones-interactivas.js';

test('búsqueda conserva filtros y reinicia la página sin solicitudes duplicadas', () => {
    const originalWindow = globalThis.window;
    const originalFormData = globalThis.FormData;
    const navegaciones = [];
    globalThis.window = { location: { origin: 'http://localhost', href: 'http://localhost/admisiones?page=3' }, Livewire: { navigate: url => navegaciones.push(url) } };
    globalThis.FormData = class { *[Symbol.iterator]() { yield* [['tab', 'admitidos'], ['vista', 'tarjetas'], ['search', '  prueba  '], ['por_pagina', '20'], ['orden', 'antiguas']]; } };
    try {
        const panel = rmAdmisiones();
        panel.$refs = { filtros: { action: 'http://localhost/admisiones', reportValidity: () => true } };
        panel.filtrar(); panel.filtrar();
        assert.equal(navegaciones.length, 1);
        const url = new URL(navegaciones[0]);
        assert.equal(url.searchParams.get('search'), 'prueba');
        assert.equal(url.searchParams.get('vista'), 'tarjetas');
        assert.equal(url.searchParams.get('por_pagina'), '20');
        assert.equal(url.searchParams.has('page'), false);
        assert.equal(panel.loading, true);
        panel.loading = false; panel.$refs.filtros.reportValidity = () => false;
        panel.filtrar(); assert.equal(navegaciones.length, 1);
    } finally { globalThis.window = originalWindow; globalThis.FormData = originalFormData; }
});

test('resumen consulta datos disponibles y devuelve el foco al cerrar', () => {
    const panel = rmAdmisiones(); const focos = [];
    panel.$nextTick = callback => callback();
    panel.$refs = { cerrarResumen: { focus: () => focos.push('cerrar') } };
    const trigger = { focus: () => focos.push('registro') };
    panel.abrirResumen({ nombre: 'PERSONA DE PRUEBA', codigo: 'ADM_PRUEBA' }, trigger);
    assert.equal(panel.resumen.codigo, 'ADM_PRUEBA');
    panel.cerrarResumen();
    assert.equal(panel.resumen, null); assert.deepEqual(focos, ['cerrar', 'registro']);
});
