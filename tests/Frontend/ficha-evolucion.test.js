import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.window = {};
const { evolucionResidenteChart, evolucionResidente } = await import('../../resources/frontend/scripts/modules/ficha-evolucion.js');

test('la gráfica conserva ceros, omite ausencias y muestra dos series de puntos sin interpolación', () => {
    let captured;
    let destroyed;
    window.RMCharts = {
        color: tone => tone,
        presets: { line: (labels, datasets) => ({ data: { labels, datasets }, options: {
            scales: { x: { ticks: {} }, y: {} }, plugins: { tooltip: {}, legend: { labels: {} } },
        } }) },
        init: (key, canvas, config) => { captured = config; return {}; },
        destroy: key => { destroyed = key; },
    };
    const controller = evolucionResidenteChart([
        { codigo: 'A', fecha: '2026-10-01T08:00:00-04:00', zona: 'America/La_Paz', periodo: 'anterior', sistolica: 0, diastolica: null, clasificacion: 'Sin clasificación automática' },
        { codigo: 'B', fecha: '2026-10-17T08:00:00-04:00', zona: 'America/La_Paz', periodo: 'actual', sistolica: 120, diastolica: 75, clasificacion: 'NORMAL' },
    ], 'qa-evolucion', { desde:'2026-09-17T12:00:00-04:00', hasta:'2026-10-17T12:00:00-04:00' });
    controller.$refs = { canvas: {} };
    controller.render();
    assert.equal(captured.data.datasets.length, 2);
    assert.deepEqual(captured.data.datasets[0].data.map(p => p.y), [0,120]);
    assert.deepEqual(captured.data.datasets[1].data.map(p => p.y), [75]);
    assert.equal(captured.data.datasets[0].showLine, false);
    assert.equal(captured.data.datasets[1].spanGaps, false);
    assert.equal(captured.data.datasets[0].data[0].x, Date.parse('2026-10-01T08:00:00-04:00'));
    assert.equal(captured.options.scales.x.min, Date.parse('2026-09-17T12:00:00-04:00'));
    assert.equal(captured.options.scales.x.max, Date.parse('2026-10-17T12:00:00-04:00'));
    assert.equal(captured.options.plugins.legend.display, true);
    assert.match(captured.options.plugins.tooltip.callbacks.afterLabel({raw:captured.data.datasets[0].data[0]})[0], /anterior.*A/);
    controller.destroy();
    assert.equal(destroyed, 'qa-evolucion');
});

test('sin puntos no genera un histórico artificial y la falta de API conserva alternativa textual', () => {
    window.RMCharts = undefined;
    const empty = evolucionResidenteChart([], 'empty');
    empty.render();
    assert.equal(empty.error, false);
    const populated = evolucionResidenteChart([{fecha:'2026-10-01T08:00:00-04:00'}], 'missing-api');
    populated.$refs = { canvas:{} };
    populated.render();
    assert.equal(populated.error, true);
});

test('el enlace al original abre la cronología y devuelve el foco al registro consultado', () => {
    let focused = false;
    let prevented = false;
    const original = { open:false, querySelector:()=>({ scrollIntoView(){}, focus(){focused=true;} }) };
    globalThis.document = { getElementById:id=>id==='evolucion-original-1'?original:null };
    const controller = evolucionResidente();
    controller.$refs = { cronologia:{open:false} };
    controller.$nextTick = callback=>callback();
    controller.abrirOriginal({target:{closest:()=>({hash:'#evolucion-original-1'})},preventDefault(){prevented=true;}});
    assert.equal(controller.$refs.cronologia.open, true);
    assert.equal(original.open, true);
    assert.equal(focused, true);
    assert.equal(prevented, true);
});
