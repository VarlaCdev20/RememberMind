import test from 'node:test';
import assert from 'node:assert/strict';
import { cognitiveProgress, cognitiveResponse, cognitiveComparison, observacionCognitiva } from '../../resources/frontend/scripts/modules/observacion-cognitiva.js';

test('progreso cuenta false y true pero no ausencia; no es puntaje clínico', () => {
    assert.equal(cognitiveProgress(), 0);
    assert.equal(cognitiveProgress({confusion: false, sigue_instrucciones: true, memoria_reciente: 'CONSERVADA', memoria_remota: null, atencion: ''}), 3);
});
test('etiquetas distinguen no observado y negativa sin reinterpretar historia literal', () => {
    assert.equal(cognitiveResponse('confusion', null), 'No observado');
    assert.equal(cognitiveResponse('confusion', false), 'No');
    assert.equal(cognitiveResponse('confusion', true), 'Sí');
    assert.equal(cognitiveResponse('memoria_reciente', 'CONSERVADA', {memoria_reciente: {CONSERVADA: 'Conservada'}}), 'Conservada');
    assert.equal(cognitiveResponse('memoria_reciente', 'LITERAL_HISTORICO'), 'LITERAL_HISTORICO');
});
test('comparación literal no diagnostica ni compara ausentes con false', () => {
    assert.equal(cognitiveComparison(null, false), 'Sin datos comparables');
    assert.equal(cognitiveComparison(false, false), 'Misma respuesta registrada');
    assert.equal(cognitiveComparison('CONSERVADA', 'ALTERADA'), 'Respuestas diferentes; revisar el contexto');
});

test('fallo de transporte conserva captura y libera el bloqueo sin éxito falso', async () => {
    globalThis.window = {rmClinicalFormFeedback: () => ({}), rmSignosRegistro: () => ({})};
    try {
        const control = observacionCognitiva();
        control.$wire = {datos: {confusion: false, observacion: 'Pendiente'}, guardar: async () => {throw new Error('Network');}};
        await control.submit();
        assert.equal(control.busy,false);
        assert.equal(control.$wire.datos.confusion,false);
        assert.equal(control.$wire.datos.observacion,'Pendiente');
        assert.match(control.transportError,/captura se conserva/);
    } finally { delete globalThis.window; }
});
