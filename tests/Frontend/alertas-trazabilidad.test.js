import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { rmTrazaAlerta } from '../../resources/frontend/scripts/modules/alertas-trazabilidad.js';
import { distribuirTraza, iconoTraza, presentacionesTraza } from '../../resources/frontend/scripts/modules/alertas-distribucion.js';

const centroVista = mapa => { const [x,y,w,h] = mapa.vista.split(' ').map(Number); return {x:x+w/2,y:y+h/2}; };

test('selección, relaciones y zoom solo recorren los nodos recibidos', () => {
    const datos = { nodos: [{id:'alerta', label:'Alerta'}, {id:'residente', label:'Residente'}], aristas:[{desde:'alerta',hasta:'residente'}] };
    const mapa = rmTrazaAlerta(datos);
    mapa.init();
    mapa.seleccionar('residente');
    assert.equal(mapa.nodo.label, 'Residente');
    assert.equal(mapa.conectado(datos.aristas[0]), true);
    mapa.seleccionar('otra-alerta');
    assert.equal(mapa.seleccionado, 'residente');
    mapa.ampliar(100); assert.equal(mapa.zoom, 4);
    mapa.ampliar(-100); assert.equal(mapa.zoom, .6);
    assert.ok(!mapa.vista.includes('NaN'));
    assert.deepEqual(datos.nodos.map(n=>n.id), ['alerta','residente']);
    const nuevo = rmTrazaAlerta({nodos:[{id:'alerta',label:'Otro residente'}],aristas:[]});
    nuevo.init(); assert.equal(nuevo.nodo.label,'Otro residente');
    assert.equal(nuevo.seleccionado,'alerta');
});

test('capas e inspector recorren datos profesionales y el enfoque no modifica relaciones', () => {
    const datos = {
        ancho: 400, alto: 700, inicio: 'valoracion',
        posiciones: { alerta: {x:200,y:60}, valoracion: {x:200,y:400} },
        nodos: [{id:'alerta',label:'Alerta',grupo:'caso'}, {id:'residente',label:'Residente',grupo:'caso'}, {id:'valoracion',label:'Nutrición',grupo:'profesional',detalles:[{etiqueta:'Requerimiento hídrico',valor:'1.500 mL por día'}]}, {id:'evento',label:'Seguimiento',grupo:'eventos'}],
        aristas: [{desde:'residente',hasta:'valoracion',label:'contexto del residente',tipo:'contexto'}, {desde:'alerta',hasta:'evento',label:'registró evento'}],
    };
    const original = JSON.stringify(datos);
    const mapa = rmTrazaAlerta(datos); mapa.init();
    assert.equal(mapa.nodo.label, 'Nutrición');
    assert.deepEqual(mapa.nodo.detalles, datos.nodos[2].detalles);
    assert.deepEqual(mapa.relacionesSeleccionadas, ['Residente · contexto del residente · Nutrición']);
    mapa.centrarSeleccion(); assert.equal(mapa.zoom, 1.6); assert.deepEqual(centroVista(mapa), datos.posiciones.valoracion);
    assert.ok(!mapa.vista.includes('NaN'));
    mapa.cambiarCapa('caso');
    assert.equal(mapa.nodoVisible(datos.nodos[2]), false);
    assert.equal(mapa.aristaVisible(datos.aristas[0]), false);
    mapa.seleccionar('valoracion'); assert.notEqual(mapa.seleccionado, 'valoracion');
    mapa.cambiarCapa('profesional'); mapa.seleccionar('valoracion');
    assert.equal(mapa.aristaVisible(datos.aristas[0]), true);
    assert.equal(mapa.nodoVisible(datos.nodos[3]), false);
    mapa.cambiarCapa('no-autorizada'); assert.equal(mapa.capa, 'profesional');
    mapa.centrar(); assert.equal(mapa.zoom, 1); assert.equal(mapa.panY, 0);
    let inspector = false, desplazado = false;
    mapa.$refs = { inspector: { focus() { inspector = true; }, scrollIntoView() { desplazado = true; } } };
    mapa.consultarSeleccion(); assert.equal(inspector, true); assert.equal(desplazado, true);
    mapa.ampliar(.2); mapa.enfocarNodo('valoracion'); assert.deepEqual(centroVista(mapa), datos.posiciones.valoracion);
    assert.equal(JSON.stringify(datos), original);
});

test('las flechas desplazan el foco sin persistir ni cambiar relaciones', () => {
    const mapa = rmTrazaAlerta({nodos:[{id:'alerta'},{id:'estado'}],aristas:[]});
    let foco = null;
    mapa.$root = {querySelectorAll:()=>['alerta','estado'].map(id=>({dataset:{trazaNodo:id},focus(){foco=id;}}))};
    let prevenido = false;
    mapa.mover({key:'ArrowRight',preventDefault(){prevenido=true;}},'alerta');
    assert.equal(foco,'estado'); assert.equal(prevenido,true);
    assert.equal(mapa.seleccionado,'alerta');
});

test('la capa del hecho conserva el plan y la ejecución referenciados sin mostrar campos de otras capas', () => {
    const datos = {inicio:'origen-operativo',nodos:[{id:'alerta',grupo:'caso'}, {id:'residente',grupo:'caso'}, {id:'origen-operativo',grupo:'origen'}, {id:'plan-origen',grupo:'origen'}, {id:'profesional',grupo:'profesional'}],aristas:[{desde:'alerta',hasta:'origen-operativo'},{desde:'plan-origen',hasta:'origen-operativo'}]};
    const mapa = rmTrazaAlerta(datos); mapa.init(); mapa.cambiarCapa('origen');
    assert.equal(mapa.seleccionado,'origen-operativo');
    assert.equal(mapa.nodoVisible(datos.nodos[3]),true);
    assert.equal(mapa.nodoVisible(datos.nodos[4]),false);
    assert.equal(mapa.aristaVisible(datos.aristas[1]),true);
    assert.deepEqual(datos.aristas,[{desde:'alerta',hasta:'origen-operativo'},{desde:'plan-origen',hasta:'origen-operativo'}]);
});

test('las nueve áreas tienen composiciones distintas que conservan exactamente los elementos autorizados', () => {
    const datos = {nodos:[{id:'alerta'}, ...['residente','estado','responsable','especialidad-0'].map(id=>({id,grupo:'caso'})),
        ...['origen-operativo','plan-origen','intervencion-origen'].map(id=>({id,grupo:'origen'})),
        ...['contexto-profesional','autor-profesional','atencion-profesional'].map(id=>({id,grupo:'profesional'})),
        ...[0,1,2].map(seccion=>({id:'campo-'+seccion,grupo:'profesional',seccion})), {id:'evento-1',grupo:'eventos'}], aristas:[]};
    const original = JSON.stringify(datos), composiciones = new Set();
    for (const foco of Object.keys(presentacionesTraza)) {
        const posiciones = distribuirTraza({...datos,foco});
        assert.deepEqual(Object.keys(posiciones).sort(), datos.nodos.map(n=>n.id).sort());
        assert.ok(Object.values(posiciones).every(p=>Number.isFinite(p.x)&&Number.isFinite(p.y)));
        assert.equal(new Set(Object.values(posiciones).map(p=>p.x+','+p.y)).size, datos.nodos.length);
        composiciones.add(JSON.stringify(posiciones));
    }
    assert.equal(composiciones.size, 9);
    assert.equal(JSON.stringify(datos), original);
    assert.equal(iconoTraza({id:'campo-1',seccion:1},{foco:'nutricion'}),'ph-drop');
    assert.equal(iconoTraza({id:'origen-operativo'},{origenModulo:'MEDICACION'}),'ph-pill');
});

test('ratón y tacto desplazan el lienzo y organizan posiciones sin cambiar datos clínicos', () => {
    const datos={foco:'turno',nodos:[{id:'alerta'},{id:'residente'}],aristas:[{desde:'alerta',hasta:'residente'}]};
    const original=JSON.stringify(datos), mapa=rmTrazaAlerta(datos); mapa.init();
    let capturas=0;
    const superficie={getBoundingClientRect:()=>({width:500,height:400}),setPointerCapture(){capturas++;}};
    const fondo={button:0,pointerId:1,clientX:100,clientY:100,currentTarget:superficie,target:{closest:()=>null}};
    mapa.iniciarArrastre(fondo); mapa.arrastrar({...fondo,clientX:150,clientY:140});
    assert.notEqual(mapa.panX,0); assert.notEqual(mapa.panY,0);
    mapa.terminarArrastre(fondo); assert.equal(mapa.arrastre,null);
    const elemento={...fondo,pointerId:2,target:{closest:()=>({dataset:{trazaNodo:'residente'}})}};
    mapa.iniciarArrastre(elemento); assert.equal(mapa.arrastre,null);
    mapa.organizar=true; const antes={...mapa.posicion('residente')};
    mapa.iniciarArrastre(elemento); mapa.arrastrar({...elemento,clientX:170}); mapa.terminarArrastre(elemento);
    assert.notEqual(mapa.posicion('residente').x,antes.x); assert.equal(capturas,2);
    mapa.restablecer(); assert.deepEqual(mapa.coordenadas,distribuirTraza(datos));
    assert.equal(JSON.stringify(datos),original);
});

test('Alt y flechas reorganizan sin cambiar selección y el ajuste encuadra solo la capa consultada', () => {
    const mapa=rmTrazaAlerta({nodos:[{id:'alerta'},{id:'residente'},{id:'valoracion',grupo:'profesional'}],aristas:[]}); mapa.init();
    mapa.organizar=true; const antes={...mapa.posicion('residente')};
    mapa.mover({key:'ArrowRight',altKey:true,preventDefault(){}},'residente');
    assert.equal(mapa.posicion('residente').x,antes.x+20); assert.equal(mapa.seleccionado,'alerta');
    mapa.cambiarCapa('caso'); mapa.centrar();
    const [x,y,w,h]=mapa.vista.split(' ').map(Number);
    for (const nodo of mapa.datos.nodos.filter(n=>mapa.nodoVisible(n))) {
        const p=mapa.posicion(nodo.id); assert.ok(p.x>=x && p.x<=x+w && p.y>=y && p.y<=y+h);
    }
});

test('ResizeObserver adapta el encuadre y se desconecta al cambiar de caso', () => {
    const anterior=globalThis.ResizeObserver; let callback,desconectado=false;
    globalThis.ResizeObserver=class {constructor(cb){callback=cb;} observe(){} disconnect(){desconectado=true;}};
    try {
        const mapa=rmTrazaAlerta({nodos:[{id:'alerta'},{id:'residente'}],aristas:[]});
        mapa.$refs={lienzo:{}}; mapa.init(); callback([{contentRect:{width:800,height:400}}]);
        assert.equal(mapa.marco.ancho/mapa.marco.alto,2);
        mapa.destroy(); assert.equal(desconectado,true);
    } finally {globalThis.ResizeObserver=anterior;}
});

test('un rechazo de pantalla completa explica la recuperación sin fingir éxito', async () => {
    const anterior=globalThis.document; globalThis.document={fullscreenElement:null};
    try {
        const mapa=rmTrazaAlerta({nodos:[{id:'alerta'}],aristas:[]}); mapa.init();
        mapa.$root={requestFullscreen:async()=>{throw new Error('Bloqueada');}};
        await mapa.alternarPantallaCompleta(); assert.match(mapa.aviso,/No se pudo ampliar/); assert.equal(mapa.pantallaCompleta,false);
    } finally {globalThis.document=anterior;}
});

test('consultar el historial abandona pantalla completa antes de abrir el modal autorizado', async () => {
    const anterior=globalThis.document, orden=[];
    const mapa=rmTrazaAlerta({codigo:'alerta-aislada',nodos:[{id:'alerta'}],aristas:[]}); mapa.init(); mapa.$root={};
    globalThis.document={fullscreenElement:mapa.$root,exitFullscreen:async()=>{orden.push('salir');}};
    mapa.$wire={verDetalle:async codigo=>{assert.equal(codigo,'alerta-aislada'); orden.push('consultar');}};
    try {
        await mapa.consultarDetalle(); assert.deepEqual(orden,['salir','consultar']); assert.equal(mapa.abriendoDetalle,false);
        globalThis.document.exitFullscreen=async()=>{throw new Error('No disponible');}; orden.length=0;
        await mapa.consultarDetalle(); assert.deepEqual(orden,[]); assert.match(mapa.aviso,/No se pudo abrir/);
    } finally {globalThis.document=anterior;}
});

test('los accesos del área consultan el inspector recibido sin modificar campos ni aceptar elementos ajenos', () => {
    const datos = {modoInicial:'area',nodos:[{id:'alerta',grupo:'caso'},{id:'campo',grupo:'profesional'}],aristas:[]};
    const original = JSON.stringify(datos), mapa = rmTrazaAlerta(datos); mapa.init();
    assert.equal(mapa.modo, 'area');
    let consultas = 0;
    mapa.$refs = {inspector:{focus(){consultas++;},scrollIntoView(){}}};
    mapa.cambiarCapa('caso'); mapa.consultarElemento('campo');
    assert.equal(mapa.nodo.id, 'campo'); assert.equal(mapa.capa, 'todas'); assert.equal(consultas, 1);
    mapa.consultarElemento('ajeno'); assert.equal(consultas, 1); assert.equal(mapa.nodo.id, 'campo');
    mapa.cambiarModo('mapa'); assert.equal(mapa.modo, 'mapa');
    mapa.cambiarModo('edicion'); assert.equal(mapa.modo, 'mapa');
    assert.equal(JSON.stringify(datos), original);
});

test('el siguiente paso abre el formulario existente de especialidad y evita doble apertura', async () => {
    const anterior=globalThis.document, mapa=rmTrazaAlerta({codigo:'caso-aislado',nodos:[{id:'alerta'}],aristas:[]});
    globalThis.document={fullscreenElement:null}; mapa.init();
    let aperturas=0, terminar;
    mapa.$wire={abrirRegistroEspecialidad:codigo=>{assert.equal(codigo,'caso-aislado'); aperturas++; return new Promise(resolve=>{terminar=resolve;});}};
    try {
        const apertura=mapa.abrirContinuacion(true);
        assert.equal(mapa.abriendoDetalle,true);
        await mapa.abrirContinuacion(true); assert.equal(aperturas,1);
        terminar(); await apertura; assert.equal(mapa.abriendoDetalle,false);
        mapa.$wire.abrirRegistroEspecialidad=async()=>{throw new Error('Sin permiso');};
        await mapa.abrirContinuacion(true); assert.match(mapa.aviso,/No se pudo abrir/); assert.equal(mapa.abriendoDetalle,false);
    } finally {globalThis.document=anterior;}
});

test('las expresiones de la vista son válidas y el movimiento reducido elimina la transición de lectura', () => {
    const vista=readFileSync(new URL('../../resources/views/components/ui/alerta-trazabilidad.blade.php',import.meta.url),'utf8');
    for (const atributo of vista.matchAll(/\s(?:x-[\w.:-]+|@[\w.:-]+|:[\w.:-]+)="([^"]*)"/g)) {
        const expresion=atributo[1].replace(/@js\([^)]*\)/g,'null');
        assert.doesNotThrow(()=>new Function('return ('+expresion+')'),atributo[0]);
    }
    assert.doesNotMatch(vista,/>[^<]*\bgrafo\b/i);
    const css=readFileSync(new URL('../../resources/frontend/styles/design-system/components/alerts.css',import.meta.url),'utf8');
    assert.match(css,/@media \(prefers-reduced-motion: reduce\)\s*\{\s*\.rm-alert-map__inspector-content\s*\{\s*animation: none/);
});
