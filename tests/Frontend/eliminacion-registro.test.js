import { test } from 'node:test';
import assert from 'node:assert/strict';
globalThis.window={innerWidth:1440,innerHeight:900,addEventListener(){},removeEventListener(){}};
await import('../../resources/frontend/scripts/modules/signos-vitales-registro.js');
await import('../../resources/frontend/scripts/modules/dolor-registro.js');
const {createEliminationCapture,normalizeEliminationHistory}=await import('../../resources/frontend/scripts/modules/eliminacion-registro.js');
const initial={type:'',volumen_ml:'',presencia_sangre:null,presencia_moco:null,molestia_eliminacion:null,descripcion_molestia:'',observacion:''};
const row=(codigo,tipo,grupo='Hoy')=>({codigo,tipo,fecha:'09/10/2026',hora:'10:00',grupo,campos:[{nombre:'Volumen medido',valor:'0.00 mL'}],legacy:false,observacion:null});
test('eliminación inicia sin tipo ni booleans inventados; vacío no es cero',()=>{
 const state=createEliminationCapture([],initial);assert.equal(state.values.type,'');assert.equal(state.values.presencia_sangre,null);assert.equal(state.values.volumen_ml,'');assert.equal(state.isDirty(),false);
 state.setValue('volumen_ml','0');assert.equal(state.values.volumen_ml,'0');assert.equal(state.isDirty(),true);
});
test('sincroniza captura estructurada diferida y preserva triestado',()=>{
 const state=createEliminationCapture([],initial);const calls=[];state.$wire={$set(...args){calls.push(args);}};
 for(const value of [null,false,true]){state.setValue('presencia_sangre',value);assert.equal(state.values.presencia_sangre,value);assert.deepEqual(calls.at(-1),['elimDatos.presencia_sangre',value,false]);}
 state.setValue('cod_personal','OTRO');assert.equal(state.values.cod_personal,undefined);
});
test('molestia no/null elimina descripción stale, sí la conserva',()=>{
 const state=createEliminationCapture([],{...initial,molestia_eliminacion:true,descripcion_molestia:'Referida'});
 state.setValue('molestia_eliminacion',true);assert.equal(state.values.descripcion_molestia,'Referida');
 state.setValue('molestia_eliminacion',false);assert.equal(state.values.descripcion_molestia,'');
 state.setValue('descripcion_molestia','Nuevo');state.setValue('molestia_eliminacion',null);assert.equal(state.values.descripcion_molestia,'');
});
test('último siempre es del mismo tipo; no compara urinaria con intestinal',()=>{
 const state=createEliminationCapture([row('I1','INTESTINAL'),row('U1','URINARIA')],{...initial,type:'URINARIA'});
 assert.equal(state.previous().codigo,'U1');state.values.type='INTESTINAL';assert.equal(state.previous().codigo,'I1');state.values.type='';assert.equal(state.previous(),null);
});
test('la captura sigue dirty tras remount por tipo y el último no depende de la lista truncada',()=>{
 const state=createEliminationCapture([row('I1','INTESTINAL')],{...initial,type:'URINARIA'},false,{URINARIA:row('U_OLD','URINARIA')},{elimTipo:'',elimDatos:{...initial,type:undefined}});
 assert.equal(state.isDirty(),true);assert.equal(state.previous().codigo,'U_OLD');
});
test('filtros y grupos del historial no cambian la captura',()=>{
 const state=createEliminationCapture([row('I1','INTESTINAL'),row('U1','URINARIA','Ayer')],{...initial,type:'URINARIA',volumen_ml:'350'});
 assert.equal(state.groups().length,2);state.historyFilter='INTESTINAL';assert.equal(state.filteredHistory().length,1);assert.equal(state.values.volumen_ml,'350');assert.equal(state.groups()[0].label,'Hoy');
});
test('historia legacy conserva texto, sin atribuir unidad ni proyectar gráfica',()=>{
 const legacy={...row('OLD','URINARIA'),legacy:true,campos:[{nombre:'Cantidad anterior',valor:'350'},{nombre:'Características anteriores',valor:'Amarillo claro'}]};
 const state=createEliminationCapture([legacy],initial);assert.equal(state.history[0].campos[0].valor,'350');assert.equal(state.history[0].legacy,true);assert.equal(state.rows,undefined);
});
test('historias vacías y largas: seis recientes, eventos sin puntos ficticios',()=>{
 assert.deepEqual(createEliminationCapture([],initial).recent(),[]);
 const state=createEliminationCapture(Array.from({length:20},(_,i)=>row('U'+i,'URINARIA')),initial);assert.equal(state.recent().length,6);assert.equal(state.filteredHistory().length,20);
});
test('datos malformados no producen literales prohibidos visibles',()=>{
 const history=normalizeEliminationHistory([null,{},row('OK','URINARIA'),{...row('BAD','URINARIA'),campos:[{nombre:'Volumen',valor:'NaN'}],observacion:'[object Object]'}]);
 assert.equal(history.length,2);assert.equal(history[1].campos.length,0);assert.equal(history[1].observacion,'');
});
test('cerrar popup devuelve foco y conserva campos; resultado no activa dirty',()=>{
 const state=createEliminationCapture([],{...initial,volumen_ml:'350'});let focused=false;
 state.$refs={trendDialog:{hidePopover(){}}};state.$nextTick=fn=>fn();state.trendReturnFocus={focus(){focused=true;}};state.trendOpen=true;state.closeTrend();
 assert.equal(state.trendOpen,false);assert.equal(state.values.volumen_ml,'350');assert.equal(focused,true);
 assert.equal(createEliminationCapture([],initial,true).isDirty(),false);
});
