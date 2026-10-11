import { test } from 'node:test';
import assert from 'node:assert/strict';

const listeners = new Map();
globalThis.window = {
    addEventListener: (name, callback) => listeners.set(name, callback),
    removeEventListener: (name, callback) => { if (listeners.get(name) === callback) listeners.delete(name); },
};
await import('../../resources/frontend/scripts/modules/clinical-form-feedback.js');

test('corrección envía enteros precargados sin redondear fracciones ni vacíos', () => {
    const state = window.rmClinicalFormFeedback();
    const sent = [];
    state.$wire = { porcentajeAlimentacion: '90.00', cantidadHidratacionMl: '200.00', $set: (...args) => sent.push(args) };
    state.prepareFeedback = type => { state.feedbackAttempt = type; };
    state.prepareDailyFeedback();
    assert.deepEqual(sent, [['porcentajeAlimentacion', 90, false], ['cantidadHidratacionMl', 200, false]]);
    assert.equal(state.feedbackAttempt, 'seguimiento');
    sent.length = 0;
    state.$wire.porcentajeAlimentacion = '';
    state.$wire.cantidadHidratacionMl = '200.50';
    state.prepareDailyFeedback();
    assert.deepEqual(sent, []);
});

test('curación y cierre solo confirman su propio resultado del servidor', () => {
    const state = window.rmClinicalFormFeedback();
    state.$nextTick = callback => callback();
    state.feedbackAttempt = 'curacion';
    assert.equal(state.confirmWorkspaceFeedback({ icon: 'success', title: 'Herida cerrada' }), false);
    assert.equal(state.confirmWorkspaceFeedback({ icon: 'error', title: 'Curación registrada' }), false);
    assert.equal(state.confirmWorkspaceFeedback([{ icon: 'success', title: 'Curación registrada' }]), true);
    assert.equal(state.clinicalFeedbackOpen, true);
    state.clinicalFeedbackOpen = false;
    state.feedbackAttempt = 'cierre-herida';
    assert.equal(state.confirmWorkspaceFeedback({ icon: 'success', title: 'Herida cerrada' }), true);
    assert.equal(state.clinicalFeedbackOpen, true);
});

test('el formulario oculto no interrumpe navegación y el foco vuelve al disparador disponible', () => {
    const capture = window.rmClinicalCapture();
    capture.$el = { querySelectorAll: () => [], getClientRects: () => [], closest: () => ({ classList: { contains: () => true } }) };
    capture.$nextTick = callback => callback();
    capture.init();
    capture.initialCapture = 'otra captura';
    const event = { prevented: false, preventDefault() { this.prevented = true; } };
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, false);
    capture.destroy();
    const feedback = window.rmClinicalFormFeedback();
    let restored = '';
    feedback.$el = { closest: () => ({ querySelector: () => ({ focus: () => { restored = 'selector'; } }) }) };
    feedback.$nextTick = callback => callback();
    feedback.clinicalReturnTarget = { isConnected: true, focus: () => { restored = 'herida'; } };
    feedback.closeClinicalFeedback();
    assert.equal(restored, 'herida');
    feedback.clinicalReturnTarget.isConnected = false;
    feedback.closeClinicalFeedback();
    assert.equal(restored, 'selector');
});

test('el resultado espera un intento y el evento confirmado del mismo formulario', () => {
    const state = window.rmClinicalFormFeedback();
    let restored = false;
    state.$nextTick = callback => callback();
    const context = { querySelector: selector => ({ textContent: selector.includes('resident') ? 'Residente sintético' : 'Profesional sintético' }) };
    const root = { querySelector: selector => selector.includes('context') ? context : { focus: () => { restored = true; } } };
    state.$el = { closest: () => root };
    state.confirmFeedback('dolor');
    assert.equal(state.clinicalFeedbackOpen, false);
    state.prepareFeedback('dolor');
    assert.equal(state.clinicalFeedbackOpen, false);
    state.confirmFeedback('otro');
    assert.equal(state.clinicalFeedbackOpen, false);
    state.confirmFeedback('dolor');
    assert.equal(state.clinicalFeedbackOpen, true);
    assert.equal(state.feedbackResident, 'Residente sintético');
    state.closeClinicalFeedback();
    assert.equal(state.clinicalFeedbackOpen, false);
    assert.equal(restored, true);
});

test('la advertencia al salir depende de cambios visibles y se limpia al desmontar', () => {
    const state = window.rmClinicalCapture();
    const fields = [{ id: 'ubicacion', type: 'text', value: '', getAttribute: () => 'dolorUbicacion' }, { id: 'eva-5', type: 'radio', checked: false, value: '5', getAttribute: () => 'dolorEva' }];
    let open = true;
    state.$el = { querySelectorAll: () => fields, closest: () => ({ classList: { contains: () => open } }) };
    state.$nextTick = callback => callback();
    state.init();
    const event = { prevented: false, preventDefault() { this.prevented = true; } };
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, false);
    fields[1].checked = true;
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, true);
    event.prevented = false;
    open = false;
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, false);
    state.destroy();
    assert.equal(listeners.has('beforeunload'), false);
});

test('seguir editando conserva la referencia inicial aunque se remonte la captura', () => {
    const state = window.rmClinicalCapture({ dolorUbicacion: '' });
    state.$el = {
        querySelectorAll: () => [{ id: 'ubicacion', type: 'text', value: 'Rodilla', getAttribute: () => 'dolorUbicacion' }],
        closest: () => ({ classList: { contains: () => true } }),
    };
    state.$nextTick = callback => callback();
    state.init();
    const event = { prevented: false, preventDefault() { this.prevented = true; } };
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, true);
    state.destroy();
});

test('hidratación confirma únicamente el éxito de su guardado y cierra la captura', () => {
    const state = window.rmClinicalFormFeedback();
    state.$nextTick = callback => callback();
    state.feedbackAttempt = 'hidratacion';
    state.clinicalFormOpen = true;
    state.clinicalFormDirty = true;
    state.confirmWorkspaceFeedback([{ icon: 'error', title: 'Cuidado registrado' }]);
    assert.equal(state.clinicalFeedbackOpen, false);
    state.confirmWorkspaceFeedback([{ icon: 'success', title: 'Dispositivo registrado' }]);
    assert.equal(state.clinicalFeedbackOpen, false);
    state.confirmWorkspaceFeedback([{ icon: 'success', title: 'Cuidado registrado' }]);
    assert.equal(state.clinicalFeedbackOpen, true);
    assert.equal(state.clinicalFormOpen, false);
    assert.equal(state.clinicalFormDirty, false);
});

test('cancelar una captura con cambios pide descarte; confirmar lo reinicia sin guardar', () => {
    const state = window.rmClinicalFormFeedback();
    state.$el = { closest: () => ({ querySelector: () => null }) };
    state.$nextTick = callback => callback();
    state.clinicalFormOpen = true;
    state.clinicalFormDirty = true;
    state.closeClinicalForm();
    assert.equal(state.clinicalFormOpen, true);
    assert.equal(state.clinicalDiscardOpen, true);
    const updates = [];
    state.$wire = { $set: (...args) => updates.push(args) };
    state.discardClinicalForm({ subtipo: '', cantidadMl: null });
    assert.deepEqual(updates, [['subtipo', '', false], ['cantidadMl', null, false]]);
    assert.equal(state.clinicalFormOpen, false);
    assert.equal(state.clinicalFormDirty, false);
});

test('el éxito de cuidados corresponde únicamente al formulario que inició el guardado', () => {
    const state = window.rmClinicalFormFeedback();
    state.$nextTick = callback => callback();
    state.feedbackAttempt = 'dolor';
    state.confirmCareFeedback();
    assert.equal(state.clinicalFeedbackOpen, false);
    state.feedbackAttempt = 'movilidad';
    state.confirmCareFeedback();
    assert.equal(state.clinicalFeedbackOpen, true);
    assert.equal(state.feedbackAttempt, null);
});

test('el drawer protege cambios y exige intento confirmado antes de mostrar el resultado', () => {
    const state = window.rmClinicalFormFeedback();
    state.$el = { closest: () => ({ querySelector: () => null }) };
    state.$nextTick = callback => callback();
    let closed = 0;
    state.$wire = { cerrarModales: () => { closed++; } };
    state.clinicalFormDirty = true;
    state.closeClinicalDrawer();
    assert.equal(closed, 0);
    assert.equal(state.clinicalDiscardOpen, true);
    state.discardClinicalDrawer();
    assert.equal(closed, 1);
    state.confirmDailyFeedback();
    assert.equal(state.clinicalFeedbackOpen, false);
    state.feedbackAttempt = 'seguimiento';
    state.confirmDailyFeedback();
    assert.equal(state.clinicalFeedbackOpen, true);
    assert.equal(state.clinicalFormDirty, false);
});

test('limpiar restaura captura local y edición inicial sin tocar residente ni campos protegidos', () => {
    const mutable = { id: 'observacion', type: 'text', value: 'Original', getAttribute: () => 'observacion' };
    const context = { id: 'residente', type: 'text', value: 'RES_QA', readOnly: true, getAttribute: () => 'residente' };
    const state = window.rmClinicalCapture();
    const sent = [];
    const dirty = [];
    state.$wire = { observacion: 'Original', residente: 'RES_QA', $set: (...args) => sent.push(args) };
    state.$dispatch = (_, value) => dirty.push(value.dirty);
    state.$el = { querySelectorAll: () => [mutable, context], closest: () => null };
    state.$nextTick = callback => callback();
    state.init();
    mutable.value = 'Cambio sin guardar';
    state.notifyDirty();
    assert.equal(dirty.at(-1), true);
    state.restoreCapture();
    assert.deepEqual(sent, [['observacion', 'Original', false]]);
    assert.equal(context.value, 'RES_QA');
    assert.equal(mutable.value, 'Original');
    assert.equal(dirty.at(-1), false);
    const event = { prevented: false, preventDefault() { this.prevented = true; } };
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, false);
    mutable.value = 'Nuevo cambio';
    listeners.get('beforeunload')(event);
    assert.equal(event.prevented, true);
    state.destroy();
});

test('limpiar restablece select y checkbox desde valores iniciales tras validación fallida', () => {
    const fields = [
        { id: 'tipo', type: 'select-one', value: 'OTRO', getAttribute: () => 'tipo' },
        { id: 'incidente', type: 'checkbox', checked: true, value: 'on', getAttribute: () => 'incidente' },
    ];
    const state = window.rmClinicalCapture({ tipo: '', incidente: false });
    state.$wire = { $set: () => {} };
    state.$dispatch = () => {};
    state.$el = { querySelectorAll: () => fields, closest: () => null };
    state.$nextTick = callback => callback();
    state.init();
    state.restoreCapture();
    assert.equal(fields[0].value, '');
    assert.equal(fields[1].checked, false);
    assert.equal(state.snapshot(), state.initialCapture);
    state.destroy();
});

test('la limpieza incluye campos condicionales y conserva su baseline al remontarlos', () => {
    const fields = [];
    const state = window.rmClinicalCapture();
    const updates = [];
    state.$wire = { cantidad: null, $set: (...args) => updates.push(args) };
    state.$dispatch = () => {};
    state.$el = { querySelectorAll: () => fields, closest: () => null };
    state.$nextTick = callback => callback();
    state.init();
    fields.push({ id: 'cantidad', type: 'number', value: '', getAttribute: () => 'cantidad' });
    state.rememberCaptureFields();
    fields[0] = { ...fields[0], value: '250' };
    state.$wire.cantidad = 250;
    state.rememberCaptureFields();
    state.restoreCapture();
    assert.deepEqual(updates, [['cantidad', null, false]]);
    assert.equal(fields[0].value, '');
    state.destroy();
});
