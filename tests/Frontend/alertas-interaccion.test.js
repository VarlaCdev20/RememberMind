import { test } from 'node:test';
import assert from 'node:assert/strict';
import { rmAlertasInteraccion, registrarInteraccionAlertas } from '../../resources/frontend/scripts/modules/alertas-interaccion.js';

test('una escritura en curso protege el diálogo y libera los controles al finalizar sin borrar captura', () => {
    const ui = rmAlertasInteraccion();
    ui.captura = 'Texto pendiente de confirmación';
    ui.estado({ inicio: true, escritura: true });
    let bloqueado = false;
    ui.proteger({ type: 'keydown', preventDefault() { bloqueado = true; }, stopImmediatePropagation() {} });
    assert.equal(bloqueado, true);
    ui.estado({ inicio: false, escritura: true });
    bloqueado = false;
    ui.proteger({ type: 'keydown', preventDefault() { bloqueado = true; }, stopImmediatePropagation() {} });
    assert.equal(bloqueado, false);
    assert.equal(ui.captura, 'Texto pendiente de confirmación');
    ui.estado({ inicio: true, escritura: false });
    ui.proteger({ type: 'keydown', preventDefault() { assert.fail('Una consulta no bloquea Escape'); } });
});

test('el interceptor solo trata Alertas, conserva autorización y nunca inserta HTML ni reintenta', () => {
    let interceptor;
    globalThis.window = { Livewire: { interceptRequest(fn) { interceptor = fn; } } };
    globalThis.CustomEvent ??= class { constructor(type, options) { this.type = type; this.detail = options.detail; } };
    registrarInteraccionAlertas();
    const eventos = [];
    const el = { matches: () => true, dispatchEvent: evento => eventos.push(evento) };
    const hooks = {};
    const params = {
        request: { messages: new Set([{ component: { el }, actions: new Set([{ name: 'guardarAlerta' }]) }]) },
        onSend: fn => hooks.send = fn, onError: fn => hooks.error = fn,
        onFailure: fn => hooks.failure = fn, onFinish: fn => hooks.finish = fn,
    };
    interceptor(params);
    hooks.send();
    assert.deepEqual(eventos[0].detail, { inicio: true, escritura: true });
    hooks.error({ response: { status: 403 }, preventDefault() { assert.fail('403 pertenece al guardián compartido'); } });
    assert.equal(eventos.length, 1);
    let prevenido = false;
    hooks.error({ response: { status: 500 }, body: '<script>privado</script>', preventDefault() { prevenido = true; } });
    assert.equal(prevenido, true);
    assert.match(eventos.at(-1).detail.message, /No se pudo confirmar/);
    assert.doesNotMatch(eventos.at(-1).detail.message, /script|privado/);
    hooks.finish();
    assert.deepEqual(eventos.at(-1).detail, { inicio: false, escritura: true });
    hooks.failure();
    assert.equal(eventos.at(-1).type, 'rm-alertas-error');
    assert.equal(eventos.filter(e => e.type === 'rm-alertas-request' && e.detail.inicio).length, 1);
    el.matches = () => false;
    interceptor({ ...params, onSend() { assert.fail('Otro módulo no se intercepta'); } });
    delete globalThis.window;
});
