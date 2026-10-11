import {test} from 'node:test';
import assert from 'node:assert/strict';
globalThis.window={innerWidth:1440,innerHeight:900,addEventListener(){},removeEventListener(){}};
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');
await import('../../resources/frontend/scripts/modules/dolor-registro.js');
const {createMobilityCapture,normalizeMobilityHistory}=await import('../../resources/frontend/scripts/modules/movilidad-registro.js');
const initial={marcha:'',actividad_realizada:'',distancia_metros:'',dolor_movilidad:null,mareo:null,disnea:null,debilidad:null,observacion:''};
const row=(code,category,group='Hoy')=>({codigo:code,categoria:category,grupo:group,fecha:'10/10/2026',hora:'10:00',actividad:'Caminar en pasillo',campos:[{nombre:'Distancia',valor:'10.00 m'}]});

test('Otro conserva detalles al editar y los limpia al cambiar cada rama',()=>{
    const s=createMobilityCapture([],{...initial,motivo_registro:'',dispositivo:'',motivo_otro:'',dispositivo_otro:''});
    const writes=[];s.$wire={$set(...args){writes.push(args);}};
    s.setValue('motivo_registro','OTRO');s.setValue('motivo_otro','Revisión');
    s.setValue('dispositivo','OTRO');s.setValue('dispositivo_otro','Apoyo');
    assert.equal(s.isDirty(),true);
    s.setValue('motivo_registro','CONTROL_DIARIO');
    assert.equal(s.values.motivo_otro,'');assert.equal(s.values.dispositivo_otro,'Apoyo');
    s.setValue('dispositivo','BASTON');assert.equal(s.values.dispositivo_otro,'');
    assert.deepEqual(writes.at(-1),['movDatos.dispositivo_otro','',false]);
});
test('movilidad conserva distancia cero real y limpia al abandonar deambulación',()=>{const s=createMobilityCapture([],initial);s.setValue('actividad_realizada','CAMINAR_PASILLO');s.setValue('distancia_metros','0');assert.equal(s.values.distancia_metros,'0');assert.equal(s.canWalk(),true);s.setValue('actividad_realizada','SEDESTACION');assert.equal(s.values.distancia_metros,'');assert.equal(s.canWalk(),false);});
test('triestado preserva identidad y sincroniza únicamente campos de captura',()=>{const s=createMobilityCapture([],initial);const writes=[];s.$wire={$set(...a){writes.push(a);}};for(const field of ['dolor_movilidad','mareo','disnea','debilidad'])for(const value of [null,false,true]){s.setValue(field,value);assert.equal(s.values[field],value);assert.deepEqual(writes.at(-1),['movDatos.'+field,value,false]);}s.setValue('cod_personal','OTRO');assert.equal(s.values.cod_personal,undefined);s.setValue('marcha','ASISTIDA');assert.deepEqual(writes.at(-1),['movMarcha','ASISTIDA',false]);});
test('dirty conserva baseline tras remount y resultado guardado no es dirty',()=>{const s=createMobilityCapture([],{...initial,marcha:'ASISTIDA'},false,{movMarcha:'',movDatos:{actividad_realizada:''}});assert.equal(s.isDirty(),true);assert.equal(createMobilityCapture([],{...initial,marcha:'ASISTIDA'},true).isDirty(),false);});
test('historial filtra actividades, agrupa fechas y no transforma captura en historia',()=>{const s=createMobilityCapture([row('A','DEAMBULACION'),row('B','TRANSFERENCIAS','Ayer'),row('C','OTROS')],initial);assert.equal(s.groups().length,2);s.historyFilter='TRANSFERENCIAS';assert.deepEqual(s.filteredHistory().map(r=>r.codigo),['B']);assert.equal(s.values.marcha,'');});
test('historial malformado no expone null NaN ni objetos',()=>{const h=normalizeMobilityHistory([null,{},row('A','OTROS'),{...row('B','OTROS'),observacion:'[object Object]',campos:[{nombre:'Distancia',valor:'NaN'}]}]);assert.equal(h.length,2);assert.equal(h[1].observacion,'');assert.deepEqual(h[1].campos,[]);});
test('flechas seleccionan opción y mantienen foco dentro del grupo',()=>{const s=createMobilityCapture([],initial);let focused=false;const buttons=[{},{focus(){focused=true;}}];buttons[0].parentElement={querySelectorAll(){return buttons;}};s.chooseNext({currentTarget:buttons[0]},'marcha',[{value:'INDEPENDIENTE'},{value:'ASISTIDA'}],1);assert.equal(s.values.marcha,'ASISTIDA');assert.equal(focused,true);});
test('cerrar historial devuelve foco y conserva captura',()=>{const s=createMobilityCapture([],{...initial,marcha:'ASISTIDA'});let focused=false;s.$refs={trendDialog:{hidePopover(){}}};s.$nextTick=f=>f();s.trendReturnFocus={focus(){focused=true;}};s.trendOpen=true;s.closeTrend();assert.equal(s.trendOpen,false);assert.equal(s.values.marcha,'ASISTIDA');assert.equal(focused,true);});
