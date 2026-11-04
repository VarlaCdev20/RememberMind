import { test } from 'node:test';
import assert from 'node:assert/strict';
import { crearAcceso } from '../../resources/frontend/scripts/modules/auth-access.js';

const estado = datos => Object.assign(crearAcceso(datos), { $nextTick: fn => fn(), $refs: {
    loginCorreo: { focus() {} }, loginPassword: { focus() {} }, recoverCorreo: { focus() {} }, confirmacion: { focus() {} },
} });
const evento = () => ({ prevenido: false, preventDefault() { this.prevenido = true; } });
const envio = () => Object.assign(evento(), { currentTarget: { action: 'http://localhost/forgot-password' } });
const simularFormulario = t => {
    const Formulario = globalThis.FormData;
    t.mock.method(globalThis, 'FormData', function () { const body = new Formulario(); body.set('_token', 'csrf-sintetico'); return body; });
};

test('validación explica qué campo falta y normaliza correo sin modificar contraseña', () => {
    const acceso = estado();
    const sinDatos = evento(); acceso.submitLogin(sinDatos);
    assert.equal(sinDatos.prevenido, true);
    assert.match(acceso.correoError, /Escribe el correo/);
    assert.match(acceso.passwordError, /Escribe tu contraseña/);
    acceso.correo = ' PERSONA@EXAMPLE.TEST '; acceso.password = ' ClaveSinCambios! ';
    const valido = evento(); acceso.submitLogin(valido);
    assert.equal(valido.prevenido, false);
    assert.equal(acceso.correo, 'persona@example.test');
    assert.equal(acceso.password, ' ClaveSinCambios! ');
    assert.equal(acceso.isSubmitting, true);
    const duplicado = evento(); acceso.submitLogin(duplicado);
    assert.equal(duplicado.prevenido, true);
});

test('recuperación conserva el correo, rechaza formato y evita doble envío', async t => {
    simularFormulario(t);
    let completar;
    const solicitudes = [];
    t.mock.method(globalThis, 'fetch', (url, opciones) => { solicitudes.push({ url, opciones }); return new Promise(resolve => { completar = resolve; }); });
    const acceso = estado(); acceso.correo = 'persona@example.test';
    acceso.abrirRecuperacion();
    assert.equal(acceso.panel, 'recover'); assert.equal(acceso.recoverCorreo, acceso.correo);
    acceso.recoverCorreo = 'correo-invalido'; const invalido = evento(); acceso.submitRecovery(invalido);
    assert.equal(invalido.prevenido, true); assert.match(acceso.recoverCorreoError, /formato/);
    acceso.password = 'SeConserva123!';
    acceso.recoverCorreo = ' OTRA@EXAMPLE.TEST '; const valido = envio(); const pendiente = acceso.submitRecovery(valido);
    assert.equal(valido.prevenido, true); assert.equal(acceso.recoverCorreo, 'otra@example.test');
    assert.equal(acceso.recoverSubmitting, true); assert.equal(acceso.recoverySent, false);
    const duplicado = evento(); acceso.submitRecovery(duplicado); assert.equal(duplicado.prevenido, true);
    acceso.volverLogin(); assert.equal(acceso.panel, 'recover');
    assert.equal(solicitudes.length, 1);
    assert.equal(solicitudes[0].opciones.headers.Accept, 'application/json');
    assert.deepEqual([...solicitudes[0].opciones.body.keys()], ['_token', 'correo']);
    completar(new Response(JSON.stringify({ message: 'Solicitud recibida' }), { headers: { 'Content-Type': 'application/json' } }));
    await pendiente;
    assert.equal(acceso.recoverySent, true); assert.equal(acceso.recoverSubmitting, false);
    acceso.volverLogin();
    assert.equal(acceso.panel, 'login'); assert.equal(acceso.correo, 'otra@example.test');
    assert.equal(acceso.password, 'SeConserva123!');
});

test('confirmación de cambio gana al marcador de recuperación incluso sin storage', () => {
    const acceso = estado({ completado: true, recuperacion: true, status: 'Contraseña actualizada' });
    acceso.init(); assert.equal(acceso.panel, 'login'); assert.equal(acceso.recoverySent, false);
    const recuperacion = estado({ recuperacion: true, status: 'Si el correo corresponde…' });
    recuperacion.init(); assert.equal(recuperacion.panel, 'recover'); assert.equal(recuperacion.recoverySent, true);
});

test('error conserva explicación del servidor sin generar éxito', () => {
    const acceso = estado({ recuperacion: true, errores: { correo: ['El correo no está disponible.'] } });
    acceso.init(); assert.equal(acceso.panel, 'recover'); assert.equal(acceso.recoverySent, false);
    assert.equal(acceso.recoveryError, 'El correo no está disponible.');
    assert.equal(acceso.errorServidor, '');
    assert.equal(acceso.validarCorreo('a'.repeat(121), true), 'El correo no puede superar los 120 caracteres.');
});

test('fallos HTTP, sesión vencida y red se muestran sin falso éxito ni detalles internos', async t => {
    simularFormulario(t);
    const respuestas = [
        new Response(JSON.stringify({ errors: { correo: ['El correo del sistema no está disponible.'] } }), { status: 503, headers: { 'Content-Type': 'application/json' } }),
        new Response('', { status: 419 }),
        new Response('', { status: 429 }),
        new Response(JSON.stringify({ message: 'SQL y stack privados' }), { status: 500, headers: { 'Content-Type': 'application/json' } }),
        new Response('<html>login</html>', { headers: { 'Content-Type': 'text/html' } }),
        new Response(JSON.stringify({ errors: { correo: ['Revisa el formato del correo.'] } }), { status: 422, headers: { 'Content-Type': 'application/json' } }),
        new Error('Fallo privado de red'),
    ];
    t.mock.method(globalThis, 'fetch', async () => { const r = respuestas.shift(); if (r instanceof Error) throw r; return r; });
    const acceso = estado(); acceso.correo = 'persona@example.test'; acceso.abrirRecuperacion();
    for (const esperado of [/no está disponible/, /venció/, /varias solicitudes/, /No pudimos/, /No pudimos/, /formato/, /conexión/]) {
        await acceso.submitRecovery(envio());
        assert.match(acceso.recoveryError, esperado);
        assert.doesNotMatch(acceso.recoveryError, /SQL|stack|privado|<html>/);
        assert.equal(acceso.recoverySent, false); assert.equal(acceso.recoverSubmitting, false);
    }
});
