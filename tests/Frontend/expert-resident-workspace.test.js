import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { residentWorkspace, residentGraph } from '../../resources/frontend/scripts/modules/expert-resident-workspace.js';
import { knowledgeGraph, distribuirRedCompacta } from '../../resources/frontend/scripts/modules/orion-explorador.js';

test('las clases del lienzo forman expresiones Alpine válidas con y sin recorrido', () => {
    const vista = readFileSync(new URL('../../resources/views/components/ui/expert-graph-canvas.blade.php', import.meta.url), 'utf8');
    const expresiones = [...vista.matchAll(/:class="([^"]+)"/g)].map(match => match[1]);
    assert.ok(expresiones.length > 0);
    for (const recorrido of [false, true]) {
        for (const expresion of expresiones) {
            const resuelta = expresion.replace(/\{\{\s*\$pathAware[^}]+\}\}/g, recorrido ? 'enRecorrido(arista)' : 'false');
            assert.doesNotThrow(() => new Function(`return (${resuelta});`));
        }
    }
});

test('fuentes numerosas: pagina sin perder registros y sigue conexiones fuera de página', () => {
    const fuente = { nodos: [{id:'grupo', nombre:'Aplicaciones', tipo:'Conjunto de registros', columna:2},
        ...Array.from({length:15}, (_,i) => ({id:`s${i}`, nombre:`Fuente ${i}`, tipo:'Registro', columna:0}))],
        aristas:Array.from({length:15}, (_,i) => ({origen:`s${i}`, destino:'grupo', relacion:'Pertenece'})) };
    const antes = structuredClone(fuente);
    const c = residentGraph(fuente);
    c.cambiarPaginaFuente(1);
    assert.equal(c.paginasFuentes,3); assert.equal(c.cantidadVisible,7);
    assert.equal(c.esVisible('s14'),false);
    c.seguirConexion('s14'); assert.equal(c.paginaFuentes,3); assert.equal(c.actual.id,'s14');
    assert.equal(c.esVisible('s14'),true); assert.equal(c.nodos.length,16);
    assert.deepEqual(fuente,antes);
});

test('selección por residente no mezcla áreas con requisitos ni modifica la fuente', () => {
    const elementos = [{ id: 'COG-MEM', grupo: 'areas' }, { id: 'C01', grupo: 'requisitos' }];
    const anterior = structuredClone(elementos);
    const c = residentWorkspace(elementos);
    c.seleccionar('C01'); assert.equal(c.seleccionado, 'COG-MEM');
    c.cambiarGrupo('requisitos'); assert.equal(c.seleccionado, 'C01');
    c.cambiarGrupo('otro'); assert.equal(c.grupo, 'requisitos');
    assert.deepEqual(elementos, anterior);
});

test('retorno y selección entre páginas recuperan la fuente y redistribuyen el mapa', () => {
    const fuente = { nodos: [{ id: 'grupo', tipo: 'Conjunto de registros', columna: 2 },
        ...Array.from({ length: 15 }, (_, i) => ({ id: `s${i}`, tipo: 'Registro', columna: 0 }))],
        aristas: [{ origen: 's0', destino: 's14', relacion: 'Referencia' }] };
    const c = residentGraph(fuente);
    c.seleccionar('s0');
    c.seguirConexion('s14');
    assert.equal(c.paginaFuentes, 3);
    c.volverPaso('s0');
    assert.equal(c.paginaFuentes, 1);
    assert.equal(c.esVisible(c.actual.id), true);
    assert.ok(c.actual.y < c.alto);
    c.seleccionar('s14');
    assert.equal(c.paginaFuentes, 3);
    assert.equal(c.esVisible(c.actual.id), true);
    assert.ok(c.actual.y < c.alto);
    assert.equal(c.cantidadVisible, 4);
});

const datos = () => ({ nodos: [
    { id: 's', nombre: 'Soporte original', tipo: 'Soporte', columna: 0 },
    { id: 'v', nombre: 'Valoración original', tipo: 'Valoración', columna: 1 },
    { id: 'a', nombre: 'Memoria', tipo: 'Área', columna: 2 },
    { id: 'b', nombre: 'Otra área', tipo: 'Área', columna: 2 },
], aristas: [{ origen: 's', destino: 'v', relacion: 'Sustenta' }, { origen: 'v', destino: 'a', relacion: 'Valora' }] });

test('el recorrido selecciona registros reales y conserva las direcciones de relación', () => {
    const c = residentGraph(datos());
    c.explorarEtapa('Soporte'); assert.equal(c.actual.id, 's');
    assert.equal(c.conexionesActuales[0].nombre, 'Valoración original');
    c.explorarEtapa('Elemento'); assert.equal(c.actual.id, 'a');
    c.explorarEtapa('Inexistente'); assert.equal(c.actual.id, 'a');
    c.seleccionar('ajeno'); assert.equal(c.actual.id, 'a');
    assert.equal(c.conexionesActuales[0].origen, 'v');
});

test('enfocar un grafo conserva toda la fuente y recupera los nodos al salir', () => {
    const fuente = datos(), anterior = structuredClone(fuente), c = knowledgeGraph(fuente);
    c.seleccionar('s'); c.enfocarConexiones();
    assert.equal(c.cantidadVisible, 2);
    assert.equal(c.aristaVisible(fuente.aristas[1]), false);
    c.limpiarSeleccion(); assert.equal(c.cantidadVisible, 4);
    assert.deepEqual(fuente, anterior);
});

test('las redes grandes se distribuyen sin superponer nodos y caben al ajustar', () => {
    const fuente = { nodos: Array.from({ length: 90 }, (_, i) => ({ id: String(i), columna: i % 3 })), aristas: [] };
    const layout = distribuirRedCompacta(fuente, 900);
    assert.equal(new Set(layout.nodos.map(n => `${n.x}:${n.y}`)).size, 90);
    const c = knowledgeGraph(fuente); c.viewportAncho = 900; c.viewportAlto = 400; c.distribuir('compacta');
    assert.ok(c.ancho * c.escala <= 900 + .1);
    assert.ok(c.alto * c.escala <= 400 + .1);
    c.seleccionar('89'); assert.ok(c.escala >= .65);
    assert.equal(c.nodos.length, 90);
});

test('inicializa una sola vez, observa el lienzo y desconecta al salir', () => {
    const previousWindow = globalThis.window, previousObserver = globalThis.ResizeObserver;
    let observed = 0, disconnected = 0;
    globalThis.window = { matchMedia: () => ({ matches: false }) };
    globalThis.ResizeObserver = class {
        observe() { observed++; }
        disconnect() { disconnected++; }
    };
    try {
        const c = residentGraph(datos());
        c.$refs = { area: {} }; c.$nextTick = callback => callback();
        c.init();
        assert.equal(observed, 1);
        assert.equal(c.actual.id, 'a');
        assert.equal(c.vista, 'mapa');
        c.destroy(); assert.equal(disconnected, 1);
    } finally {
        globalThis.window = previousWindow; globalThis.ResizeObserver = previousObserver;
    }
});

test('seguir el camino solo permite relaciones reales y regresar conserva la dirección original', () => {
    const fuente = datos(), anterior = structuredClone(fuente), c = residentGraph(fuente);
    c.seleccionar('a'); c.seguirConexion('v'); c.seguirConexion('s');
    assert.deepEqual(c.pasosRecorrido.map(n => n.id), ['a', 'v', 's']);
    assert.ok(c.enRecorrido(fuente.aristas[0]));
    assert.ok(c.enRecorrido(fuente.aristas[1]));
    c.seguirConexion('b'); assert.equal(c.actual.id, 's');
    c.volverPaso('v'); assert.deepEqual(c.recorrido, ['a', 'v']);
    c.seguirConexion('a'); assert.deepEqual(c.recorrido, ['a']);
    c.volverPaso('ajeno'); assert.deepEqual(c.recorrido, ['a']);
    assert.deepEqual(fuente, anterior);
});
