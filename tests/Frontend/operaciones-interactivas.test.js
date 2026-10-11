import { test } from 'node:test';
import assert from 'node:assert/strict';
import { rmOperaciones } from '../../resources/frontend/scripts/modules/operaciones-interactivas.js';

test('gráficos móviles empiezan plegados y la actualización conserva filtros y página', () => {
    const anterior = globalThis.window;
    const navegaciones = [];
    globalThis.window = { matchMedia: () => ({ matches: true }), location: { href: 'http://localhost/visitas?vista=agenda&tab=dentro&page=2' }, Livewire: { navigate: url => navegaciones.push(url) } };
    try {
        const panel = rmOperaciones();
        panel.init();
        assert.equal(panel.graficos, false);
        panel.graficos = true;
        panel.actualizar();
        assert.equal(panel.graficos, true);
        assert.equal(panel.loading, true);
        assert.deepEqual(navegaciones, [window.location.href]);
    } finally { globalThis.window = anterior; }
});

test('gráficos de escritorio empiezan visibles', () => {
    const anterior = globalThis.window;
    globalThis.window = { matchMedia: () => ({ matches: false }) };
    try { const panel = rmOperaciones(); panel.init(); assert.equal(panel.graficos, true); }
    finally { globalThis.window = anterior; }
});

test('la preferencia de gráficos se conserva por módulo y no acepta valores inválidos', () => {
    const anterior = globalThis.window;
    const preferencias = new Map([['rm.operaciones.graficos.alertas', 'false'], ['rm.operaciones.graficos.visitas', 'invalida']]);
    globalThis.window = { matchMedia: () => ({ matches: false }), localStorage: { getItem: key => preferencias.get(key), setItem: (key,value) => preferencias.set(key,value) } };
    try {
        const alertas = rmOperaciones('alertas'); alertas.init(); assert.equal(alertas.graficos, false);
        alertas.alternarGraficos(); assert.equal(preferencias.get('rm.operaciones.graficos.alertas'), 'true');
        const visitas = rmOperaciones('visitas'); visitas.init(); assert.equal(visitas.graficos, true);
        const otraCarga = rmOperaciones('alertas'); otraCarga.init(); assert.equal(otraCarga.graficos, true);
    } finally { globalThis.window = anterior; }
});

test('ficha local carga sin cambiar URL y restaura el foco al cerrar', async () => {
    const anterior = globalThis.window;
    const fetchAnterior = globalThis.fetch;
    const solicitudes = [];
    const focos = [];
    const ubicacion = { origin:'http://localhost', pathname:'/incidentes', href:'http://localhost/incidentes?vista=lista&page=2' };
    globalThis.window = { location: ubicacion };
    globalThis.fetch = async (url,options) => { solicitudes.push([url,options.headers]); return { ok:true,redirected:false,headers:{get:()=> '1'},text:async()=> '<section role="dialog">Ficha sintética</section>' }; };
    try {
        const panel=rmOperaciones('incidentes'); panel.$nextTick=fn=>fn();
        await panel.abrirFicha(ubicacion.href+'&detalle=INC_PRUEBA','INC_PRUEBA',{currentTarget:{focus:opciones=>focos.push(opciones.preventScroll)}});
        assert.equal(panel.cargandoFicha,false); assert.ok(panel.fichaHtml.includes('Ficha sintética'));
        assert.equal(ubicacion.href,'http://localhost/incidentes?vista=lista&page=2');
        assert.equal(solicitudes[0][1]['X-RM-Ficha'],'1');
        panel.cerrarFicha(); assert.equal(panel.fichaHtml,''); assert.deepEqual(focos,[true]);
    } finally { globalThis.window=anterior; globalThis.fetch=fetchAnterior; }
});

test('ficha denegada y sesión redirigida no incorporan HTML ni abandonan el módulo', async()=>{
    const anterior=globalThis.window, fetchAnterior=globalThis.fetch;
    globalThis.window={location:{origin:'http://localhost',pathname:'/alertas'}};
    try {
        for(const respuesta of [{ok:false,redirected:false},{ok:true,redirected:true},{ok:true,redirected:false}]) {
            globalThis.fetch=async()=>({...respuesta,headers:{get:()=>null},text:async()=>'<pre>Internals</pre>'});
            const panel=rmOperaciones('alertas');
            await panel.abrirFicha('/alertas?detalle=ALE_PRUEBA','ALE_PRUEBA',{currentTarget:{}});
            assert.equal(panel.fichaHtml,''); assert.ok(panel.errorFicha.includes('No se pudo abrir')); assert.equal(panel.cargandoFicha,false);
        }
        const panel=rmOperaciones('alertas');
        await panel.abrirFicha('/residentes?residente=RES_PRUEBA','RES_PRUEBA',{currentTarget:{}});
        assert.ok(panel.errorFicha.includes('esta ventana'));
    } finally {globalThis.window=anterior;globalThis.fetch=fetchAnterior;}
});

test('cerrar una petición pendiente impide insertar una ficha tardía',async()=>{
    const anterior=globalThis.window,fetchAnterior=globalThis.fetch;
    globalThis.window={location:{origin:'http://localhost',pathname:'/alertas'}};
    let terminar;
    globalThis.fetch=()=>new Promise(resolve=>{terminar=resolve});
    try {
        const panel=rmOperaciones('alertas');panel.$nextTick=fn=>fn();
        const pendiente=panel.abrirFicha('/alertas?detalle=ALE_PRUEBA','ALE_PRUEBA',{currentTarget:{focus(){}}});
        panel.cerrarFicha();
        terminar({ok:true,redirected:false,headers:{get:()=> '1'},text:async()=> '<div>Ficha tardía</div>'});
        await pendiente;assert.equal(panel.fichaHtml,'');assert.equal(panel.cargandoFicha,false);
    }finally{globalThis.window=anterior;globalThis.fetch=fetchAnterior;}
});

test('el cierre libera la trampa de foco antes de desmontar el modal',()=>{
    const anterior = globalThis.window;
    globalThis.window = { location: { href: 'http://localhost/jornadas' } };
    try {
    const panel=rmOperaciones('jornadas'); const pendientes=[];
    panel.$nextTick=fn=>pendientes.push(fn);panel.fichaAbierta=true;panel.fichaHtml='<div>Ficha</div>';
    panel.cerrarFicha();
    assert.equal(panel.fichaAbierta,false);assert.equal(panel.fichaHtml,'<div>Ficha</div>');
    pendientes[0]();assert.equal(panel.fichaHtml,'');
    } finally {globalThis.window=anterior;}
});

test('cerrar una ficha enlazada retira solo detalle sin reabrirlo al actualizar',()=>{
    const anterior = globalThis.window;
    const cambios = [];
    globalThis.window = { location: { href: 'http://localhost/actividades?mes=2026-10&dia=2026-10-06&detalle=ACT_OP' }, history: { state: { prueba: true }, replaceState: (...args) => cambios.push(args) } };
    try {
        const panel = rmOperaciones('actividades'); panel.$nextTick=fn=>fn();
        panel.cerrarFicha();
        assert.equal(cambios.length,1);
        assert.equal(cambios[0][2],'http://localhost/actividades?mes=2026-10&dia=2026-10-06');
        assert.deepEqual(cambios[0][0],{prueba:true});
    } finally {globalThis.window=anterior;}
});
