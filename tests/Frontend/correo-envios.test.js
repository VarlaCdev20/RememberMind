import { test } from 'node:test';
import assert from 'node:assert/strict';
import { solicitarCorreo, consultarEstadoCorreo } from '../../resources/frontend/scripts/modules/correo-envios.js';

test('la solicitud conserva pendiente y envía CSRF sin inventar entrega', async () => {
    const result = await solicitarCorreo('/solicitar', async (url, options) => {
        assert.equal(options.method, 'POST');
        assert.equal(options.headers['X-CSRF-TOKEN'], 'token-sintetico');
        return { ok: true, json: async () => ({ success: true, estado: 'PENDIENTE', terminado: false }) };
    }, 'token-sintetico');
    assert.equal(result.estado, 'PENDIENTE');
    assert.equal(result.terminado, false);
});

test('error de red y respuesta de error rechazan la solicitud sin mostrar éxito', async () => {
    await assert.rejects(solicitarCorreo('/solicitar', async () => { throw new Error('Red caída'); }));
    await assert.rejects(solicitarCorreo('/solicitar', async () => ({ ok: false, json: async () => ({ success: false, message: 'Sin permiso' }) })), /Sin permiso/);
});

test('lectura sin caché conserva estado de reintento y rechaza sesión vencida', async () => {
    const result = await consultarEstadoCorreo('/estado', async (url, options) => {
        assert.equal(options.cache, 'no-store');
        assert.equal(options.credentials, 'same-origin');
        return { ok: true, json: async () => ({ estado: 'REINTENTO', terminado: false }) };
    });
    assert.equal(result.estado, 'REINTENTO');
    await assert.rejects(consultarEstadoCorreo('/estado', async () => ({ ok: false })), /consultar el estado/);
});
