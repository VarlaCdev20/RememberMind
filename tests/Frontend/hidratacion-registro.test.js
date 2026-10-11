import { test } from 'node:test';
import assert from 'node:assert/strict';
globalThis.window = { innerWidth: 1440, innerHeight: 900, addEventListener() {}, removeEventListener() {} };
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');
const { createHydrationCapture, hydrationNumber, normalizeHydrationHistory, toleranceLabel } = await import('../../resources/frontend/scripts/modules/hidratacion-registro.js');
const initial = { quantity: '', liquid: '', tolerance: '', observation: '' };
const saved = (volumen, fecha_hora = '2026-10-09T09:00:00') => ({ codigo: fecha_hora, volumen, fecha_hora, tipo: 'Agua', tolerancia: 'ADECUADA' });
const make = (history = [], values = {}) => createHydrationCapture(history, { ...initial, ...values }, '2026-10-09T10:00:00');

test('hidratación comienza vacía; validación entera mantiene rango canónico y cero no es ausencia válida', () => {
    const state = make(); assert.equal(state.current(), null); assert.equal(state.quantityInvalid(), false); assert.equal(state.isDirty(), false);
    for (const value of ['0', '-1', '10001', '250.5', 'NaN', 'Infinity', '1e3']) {
        state.setValue('quantity', value); assert.equal(state.current(), null); assert.equal(state.quantityInvalid(), true);
    }
    state.setValue('quantity','10000'); assert.equal(state.current(),10000);
    state.setValue('quantity','1'); assert.equal(state.current(),1);
});
test('todos los atajos y el input comparten valor, sincronización diferida y preview', () => {
    const state = make(); const calls=[]; state.$wire = { $set(...args) { calls.push(args); } };
    for (const value of ['100','150','200','250','350','500','728']) {
        state.setValue('quantity',value); assert.equal(state.current(),Number(value)); assert.equal(state.rows().at(-1).value,Number(value));
        assert.deepEqual(calls.at(-1),['hidratacionCantidad',value,false]);
    }
    state.setValue('quantity',''); assert.equal(state.current(),null); assert.equal(state.isDirty(),false);
});
test('opciones son solo las tolerancias propias; campos opcionales no se infieren', () => {
    assert.equal(toleranceLabel('BUENA'),'Sin registrar'); assert.equal(toleranceLabel(null),'Sin registrar');
    assert.equal(toleranceLabel('__proto__'),'Sin registrar'); assert.equal(toleranceLabel('constructor'),'Sin registrar');
    for (const value of ['ADECUADA','PARCIAL','RECHAZO','NAUSEAS']) assert.notEqual(toleranceLabel(value),'Sin registrar');
    const state = make(); state.setValue('tolerance','PARCIAL'); assert.equal(state.isDirty(),true);
    state.setValue('liquid','Infusión'); state.setValue('observation','Aporte observado');
    assert.equal(state.values.quantity,''); assert.equal(state.current(),null);
});
test('normaliza datos persistidos decimales sin convertir null ni fechas inválidas en puntos', () => {
    for (const value of [null, undefined, '', 'NaN','Infinity',{}, [],'42 ml']) assert.equal(hydrationNumber(value),null);
    const rows=normalizeHydrationHistory([saved(null),saved('250.25'),saved(500,'2026-02-30T10:00:00'),saved('undefined')]);
    assert.equal(rows.length,1); assert.equal(rows[0].value,250.25);
    const sanitized=normalizeHydrationHistory([{...saved(250),tipo:'[object Object]',tolerancia:'NaN'}]);
    assert.equal(sanitized[0].liquid,'Sin registrar'); assert.equal(sanitized[0].tolerance,'Sin registrar');
});
test('0 históricos vacío, 1 punto, 1 más preview son dos puntos con unión discontinua', () => {
    const empty=make(); assert.equal(empty.rows().length,0); assert.equal(empty.historyLine(),''); assert.equal(empty.previewLine(),'');
    const state=make([saved(250)]); assert.equal(state.markers().length,1); assert.equal(state.previewLine(),'');
    state.setValue('quantity','350'); assert.equal(state.markers().length,2); assert.equal(state.previewLine().split(' ').length,2);
    assert.ok(state.markers().every(row=>Number.isFinite(row.x)&&Number.isFinite(row.y)));
});
test('un cero histórico válido de Ingesta se conserva; no habilita cero en una nueva captura', () => {
    const state=make([saved('0.00')]);
    assert.equal(state.history.length,1); assert.equal(state.previous().value,0);
    assert.equal(state.markers()[0].y,170);
    state.setValue('quantity','350'); assert.equal(state.comparison(),'+350 mL');
    state.setValue('quantity','0'); assert.equal(state.current(),null); assert.equal(state.quantityInvalid(),true);
});
test('historia preserva tolerancia literal de otros consumidores y desempata por código como el backend', () => {
    const rows=normalizeHydrationHistory([{...saved(250),codigo:'HID_Z',tolerancia:'BUENA'}, {...saved(150),codigo:'HID_A'}]);
    assert.deepEqual(rows.map(row=>row.code),['HID_A','HID_Z']);
    assert.equal(rows.at(-1).tolerance,'BUENA');
    assert.equal(toleranceLabel('BUENA'),'Sin registrar');
    assert.equal(make(rows.map(row=>({codigo:row.code,volumen:row.value,fecha_hora:'2026-10-09T09:00:00',tolerancia:row.tolerance}))).previous().code,'HID_Z');
});
test('diferencia es descriptiva: positiva, negativa, igual y no disponible', () => {
    const state=make([saved(250)]); assert.equal(state.comparison(),'Sin comparación disponible');
    state.setValue('quantity','350'); assert.equal(state.comparison(),'+100 mL');
    state.setValue('quantity','150'); assert.equal(state.comparison(),'-100 mL');
    state.setValue('quantity','250'); assert.equal(state.comparison(),'Sin cambio');
    assert.equal(make([],{quantity:'250'}).comparison(),'Sin comparación disponible');
});
test('historial largo limita filas y gráfica, escala dinámica soporta más de 500 mL', () => {
    const rows=Array.from({length:15},(_,i)=>saved((i+1)*100,`2026-10-09T${String(i).padStart(2,'0')}:00:00`));
    const state=make(rows); assert.equal(state.history.length,10); assert.equal(state.markers().length,6);
    assert.equal(state.previous().value,1500); assert.ok(state.axisMaximum()>1500);
    state.setValue('quantity','10000'); assert.equal(state.markers().length,7); assert.ok(state.axisMaximum()>10000);
});
test('tooltip es seguro, actualizado y distingue preview; resultado no añade punto sin guardar', () => {
    const state=make([saved(250)],{quantity:'350',liquid:'Agua',tolerance:'PARCIAL'});
    state.selectedPoint=state.markers().at(-1); assert.match(state.selectedMarker().label,/350 mL.*09\/10\/2026 10:00.*Parcial.*Sin guardar/);
    state.setValue('quantity','500'); assert.match(state.selectedMarker().label,/500 mL/);
    for (const row of state.markers()) assert.doesNotMatch(row.label,/null|NaN|undefined|Infinity|\[object Object\]/);
    const result=createHydrationCapture([saved(350)],{quantity:'500'},'2026-10-09T10:00:00',true);
    assert.equal(result.rows().length,1); assert.equal(result.isDirty(),false);
});
test('cerrar popup devuelve foco, mantiene captura y permite continuar', () => {
    const state=make([saved(250)],{quantity:'350',liquid:'Infusión',tolerance:'PARCIAL',observation:'Observado'});
    const before=structuredClone(state.values);let focused=false;
    state.$refs={trendDialog:{hidePopover(){}}};state.$nextTick=fn=>fn();
    state.trendReturnFocus={focus(){focused=true;}};state.trendOpen=true;state.closeTrend();
    assert.equal(focused,true);assert.equal(state.trendOpen,false);assert.deepEqual(state.values,before);
});
