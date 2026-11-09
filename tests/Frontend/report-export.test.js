import { test } from 'node:test';
import assert from 'node:assert/strict';
import { tipoDeGrafica, configurarGraficaReporte } from '../../resources/frontend/scripts/modules/report-charts.js';

test('la elección conserva radar, dona y torta solo cuando los datos los permiten', () => {
 const grafica={tipos:['barras','dona','torta','radar'],recomendado:'dona'};
 for(const tipo of ['dona','torta','radar']) assert.equal(tipoDeGrafica(grafica,tipo),tipo);
 assert.equal(tipoDeGrafica(grafica),'dona');
 assert.equal(tipoDeGrafica({tipos:['barras'],recomendado:'barras'},'torta'),'barras');
});
test('el radar usa conteos con escala común desde cero y conserva la alternativa textual', () => {
 const api={color:x=>x,hexToRgba:x=>x,presets:{radar:(labels,datasets,options)=>({data:{labels,datasets},options:{...options,scales:{r:{ticks:{}}}}})}};
 const grafica={serie:[{etiqueta:'Validados',valor:40},{etiqueta:'Observados',valor:20},{etiqueta:'Cargados',valor:30}],unidad:'archivos',maximo:40};
 const config=configurarGraficaReporte(api,grafica,'radar');
 assert.deepEqual(config.data.datasets[0].data,[40,20,30]);
 assert.deepEqual(config.data.labels,['1','2','3']);
 assert.equal(config.options.scales.r.min,0);
 assert.equal(config.options.scales.r.max,40);
 assert.equal(config.options.scales.r.ticks.stepSize,10);
 assert.equal(config.options.plugins.tooltip.callbacks.title([{dataIndex:1}]),'Observados');
});
test('la dona recibe categorías sin sumar de nuevo el total', () => {
 let datos;
 const api={color:x=>x,presets:{doughnut:(labels,values)=>{datos={labels,values}; return {};}}};
 configurarGraficaReporte(api,{serie:[{etiqueta:'Activos',valor:6},{etiqueta:'Inactivos',valor:4}]},'dona');
 assert.deepEqual(datos,{labels:['Activos','Inactivos'],values:[6,4]});
});
