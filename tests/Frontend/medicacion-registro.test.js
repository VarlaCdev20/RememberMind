import test from 'node:test';
import assert from 'node:assert/strict';
import { medicacionRegistro, validMedicationSeries } from '../../resources/frontend/scripts/modules/medicacion-registro.js';

test('rechaza series ausentes o no numéricas sin convertirlas en resultados cero', () => {
    assert.deepEqual(validMedicationSeries(null), []);
    assert.deepEqual(validMedicationSeries([{fecha:'2026-10-14',administradas:null,omitidas:1},{fecha:'2026-10-14',administradas:'3',omitidas:0}]), []);
    assert.deepEqual(validMedicationSeries([{fecha:'2026-10-14',administradas:2,omitidas:1}]), [{fecha:'2026-10-14',administradas:2,omitidas:1}]);
});

test('fallo de red conserva captura y permite consultar antes de reintentar', async () => {
    const state = medicacionRegistro();
    state.$wire = {resultado:'OMITIDA',motivo_omision:'Rechazo observado',guardar:async()=>{throw new Error('Network');}};
    state.$nextTick = fn => fn(); state.$el = {querySelectorAll:()=>[]}; state.dirty = true;
    await state.submit();
    assert.equal(state.busy, false); assert.equal(state.dirty, true);
    assert.equal(state.$wire.motivo_omision, 'Rechazo observado');
    assert.match(state.transportError, /captura se conserva/);
});

test('doble envío pendiente no inicia una segunda operación', async () => {
    const state = medicacionRegistro(); let complete, calls = 0;
    state.$wire = {guardar:()=>{calls++;return new Promise(resolve=>complete=resolve);}};
    state.$nextTick = fn=>fn(); state.$el={querySelectorAll:()=>[]};
    const pending = state.submit(); await state.submit(); assert.equal(calls,1); complete(); await pending;
    assert.equal(state.busy,false);
});

test('validación enfoca el campo visible antes que una alerta de transporte oculta', async () => {
    const state = medicacionRegistro(); let focused = false;
    const invalid = {getClientRects:()=>[{}],matches:()=>true,focus:()=>{focused=true;}};
    const hidden = {getClientRects:()=>[]};
    state.$wire={guardar:async()=>{}}; state.$nextTick=fn=>fn();
    state.$el={querySelectorAll:selector=>selector.includes('aria-invalid')?[invalid]:[hidden]};
    await state.submit(); assert.equal(focused,true);
});

test('confirmación del servidor hace visible el resultado guardado',()=>{
    const state=medicacionRegistro();let focused=false,scrolled=false;
    state.$el={querySelector:()=>({setAttribute:()=>{},scrollIntoView:()=>{scrolled=true;},focus:()=>{focused=true;}})};
    state.focusSaved();assert.equal(scrolled,true);assert.equal(focused,true);
});
