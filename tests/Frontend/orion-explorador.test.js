import test from 'node:test';
import assert from 'node:assert/strict';
import { distribuirGrafo, exploradorGrafo, graficaCobertura, cognitiveTimeline, knowledgeGraph } from '../../resources/frontend/scripts/modules/orion-explorador.js';

test('grafo de conocimiento arrastra posiciones sin modificar relaciones ni datos originales', () => {
    const datos = { nodos: [{ id: 'a', columna: 0 }, { id: 'b', columna: 1 }], aristas: [{ origen: 'a', destino: 'b', relacion: 'DECLARA' }] };
    const original = structuredClone(datos);
    const g = knowledgeGraph(datos);
    g.$refs = { area: { querySelector: () => ({ setPointerCapture() {} }) } };
    g.iniciarNodo({ button: 0, clientX: 10, clientY: 20, pointerId: 1 }, 'a');
    const x = g.punto('a').x;
    g.mover({ clientX: 40, clientY: 20 });
    assert.equal(g.punto('a').x, x + 30);
    assert.ok(Number.isFinite(g.etiqueta(datos.aristas[0]).x));
    g.seleccionar('a');
    assert.equal(g.numeroConexiones, 1);
    assert.equal(g.esVecino('b'), true);
    assert.equal(g.esVecino('ajeno'), false);
    g.terminar();
    assert.equal(g.arrastreNodo, null);
    assert.deepEqual(datos, original);
});

test('distribución estable sin añadir nodos ni inferir conexiones', () => {
    const datos = { nodos: [{ id: 'a', columna: 0 }, { id: 'b', columna: 3 }], aristas: [{ origen: 'a', destino: 'b', relacion: 'soporte' }] };
    const copia = structuredClone(datos);
    const g = exploradorGrafo(datos);
    assert.equal(g.nodos.length, 2);
    assert.deepEqual(datos, copia);
    assert.deepEqual(distribuirGrafo(datos), distribuirGrafo(datos));
    g.seleccionar('b');
    assert.equal(g.actual.id, 'b');
    assert.equal(g.vinculada(datos.aristas[0]), true);
    assert.match(g.ruta(datos.aristas[0]), /^M /);
    assert.equal(g.ruta({ origen: 'desconocido', destino: 'b' }), '');
    for (let i = 0; i < 30; i++) g.zoom(.5);
    assert.equal(g.escala, 2.5);
    g.centrar();
    assert.ok(g.escala >= .5 && g.escala <= 1);
    assert.ok(Number.isFinite(g.dx) && Number.isFinite(g.dy));
    g.restablecer();
    assert.equal(g.escala, 1);
    const vertical = distribuirGrafo(datos, true);
    assert.equal(vertical.nodos[0].x, vertical.nodos[1].x);
    assert.ok(vertical.nodos[0].y < vertical.nodos[1].y);
    Object.assign(g, vertical, { viewportAncho: 320, viewportAlto: 280 });
    g.seleccionar('b');
    assert.ok(g.punto('b').y + g.dy < 280);
    let desconectado = false;
    g.observador = { disconnect: () => { desconectado = true; } };
    g.destroy();
    assert.equal(desconectado, true);
});

test('la evolución conserva intervalos, ceros reales y ciclo RMCharts sin rellenar ausencias', () => {
    const original = globalThis.window;
    let config, repaint, destroyed;
    globalThis.window = { RMCharts: {
        color: () => 'token-cognitive',
        presets: { line: (labels, datasets) => ({ data: { labels, datasets }, options: { scales: { y: {} }, plugins: { tooltip: {} } } }) },
        init: (key, canvas, definition, callback) => { config = definition; repaint = callback; assert.equal(key, 'timeline'); },
        destroy: key => { destroyed = key; },
    } };
    try {
        const puntos = [{ fecha: '2026-10-01T09:00:00-04:00', valor: 0 }, { fecha: '2026-10-07T09:00:00-04:00', valor: -1.25 }];
        const chart = cognitiveTimeline(puntos, 'timeline', 'Puntuación z');
        chart.$refs = { canvas: {} };
        chart.dibujar();
        assert.equal(config.options.scales.x.type, 'linear');
        assert.deepEqual(config.data.datasets[0].data.map(p => p.y), [0, -1.25]);
        assert.equal(config.data.datasets[0].data[1].x - config.data.datasets[0].data[0].x, 6 * 86400000);
        assert.equal(config.data.datasets[0].spanGaps, false);
        repaint();
        chart.destroy();
        assert.equal(destroyed, 'timeline');
        const single = cognitiveTimeline([puntos[0]], 'timeline', 'Puntuación z');
        single.$refs = { canvas: {} };
        single.dibujar();
        assert.equal(config.data.datasets[0].showLine, false);
    } finally { globalThis.window = original; }
});

test('el ciclo del chart usa RMCharts, repinta con tema y elimina su instancia', () => {
    const original = globalThis.window;
    let destruida = false, renderer, colorRecibido;
    globalThis.window = { RMCharts: {
        color: () => 'token-cognitive',
        presets: { barHorizontal: (labels, data, colors) => { colorRecibido = colors; return { data: { datasets: [{ data }] }, options: { scales: { x: {} } } }; } },
        init: (key, canvas, config, callback) => { renderer = callback; assert.equal(key, 'aislada'); assert.equal(config.options.scales.x.ticks.precision, 0); },
        destroy: key => { destruida = key === 'aislada'; },
    } };
    try {
        const c = graficaCobertura({ etiquetas: ['Contexto'], valores: [2] }, 'aislada');
        c.$refs = { canvas: {} };
        c.dibujar();
        assert.deepEqual(colorRecibido, ['token-cognitive']);
        renderer();
        c.destroy();
        assert.equal(destruida, true);
    } finally { globalThis.window = original; }
});
