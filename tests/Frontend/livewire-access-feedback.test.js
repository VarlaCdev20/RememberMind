import {test} from 'node:test';
import assert from 'node:assert/strict';

async function entorno(caso, disponible = true) {
    const listeners = new Map(), registros = [], dialogos = [], destinos = [], alertas = [];
    const captura = {observacion: 'Dato sintético pendiente'};
    const aviso = {hidden: true, querySelector: () => ({href: 'http://localhost/dashboard'})};
    const livewire = {interceptRequest: callback => registros.push(callback)};
    globalThis.window = {Livewire: disponible ? livewire : undefined,
        SwalAmandita: {fire: opciones => {dialogos.push(opciones); return Promise.resolve({isConfirmed: false});}},
        location: {assign: url => destinos.push(url)}, alert: texto => alertas.push(texto)};
    globalThis.document = {addEventListener: (nombre, callback) => listeners.set(nombre, callback), querySelector: () => aviso};
    await import('../../resources/frontend/scripts/modules/livewire-access-feedback.js?' + caso);
    const enviar = (status, metodo = 'refrescarTurno') => {
        let error, prevenido = false;
        registros[0]({request: {messages: new Set([{actions: new Set([{name: metodo}])}])}, onError: callback => error = callback});
        error({response: {status}, body: '<script>contenido del servidor que no debe insertarse</script>', preventDefault: () => prevenido = true});
        return prevenido;
    };
    return {listeners, registros, dialogos, destinos, alertas, aviso, livewire, captura, enviar};
}

test('un 403 conserva captura y pantalla sin insertar la página HTML del servidor ni reintentar', async () => {
    const e = await entorno('conserva-captura');
    assert.equal(e.enviar(403, 'guardar'), true);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(e.aviso.hidden, false);
    assert.equal(e.captura.observacion, 'Dato sintético pendiente');
    assert.equal(e.dialogos.length, 1);
    assert.match(e.dialogos[0].text, /resultado no está confirmado/);
    assert.deepEqual(e.destinos, []);
});

test('los rechazos de sondeos no apilan diálogos y otra acción rechazada sí da feedback', async () => {
    const e = await entorno('sondeos');
    e.enviar(403); await new Promise(resolve => setImmediate(resolve));
    e.enviar(403, 'verificarAlertas');
    assert.equal(e.dialogos.length, 1);
    assert.equal(e.aviso.hidden, false);
    e.enviar(403, 'guardar');
    assert.equal(e.dialogos.length, 2);
});

test('volver al inicio requiere la elección del usuario y usa el enlace generado por el servidor', async () => {
    const e = await entorno('inicio-explicito');
    window.SwalAmandita.fire = () => Promise.resolve({isConfirmed: true});
    e.enviar(403);
    await new Promise(resolve => setImmediate(resolve));
    assert.deepEqual(e.destinos, ['http://localhost/dashboard']);
});

test('si falta SweetAlert el aviso persistente y el mensaje nativo siguen mostrando el rechazo', async () => {
    const e = await entorno('sin-cdn');
    delete window.SwalAmandita;
    assert.equal(e.enviar(403), true);
    assert.equal(e.aviso.hidden, false);
    assert.equal(e.alertas.length, 1);
    assert.deepEqual(e.destinos, []);
});

test('no oculta fallos de otro tipo ni rechazos cuando falta el componente de recuperación', async () => {
    const e = await entorno('fallos-originales');
    assert.equal(e.enviar(500), false);
    assert.equal(e.enviar(419), false);
    document.querySelector = () => null;
    assert.equal(e.enviar(403), false);
    assert.equal(e.aviso.hidden, true);
});

test('el registro funciona antes y después de Livewire sin duplicar interceptores', async () => {
    const e = await entorno('carga-temprana', false);
    assert.equal(e.registros.length, 0);
    window.Livewire = e.livewire;
    e.listeners.get('livewire:init')();
    e.listeners.get('livewire:init')();
    assert.equal(e.registros.length, 1);
});
