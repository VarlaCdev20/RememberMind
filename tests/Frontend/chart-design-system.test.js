import { afterEach, test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { rmChartColor, rmChartNumber, rmHexToRgba, rmInstallGlobalChartTheme, rmCreateGradient, rmCreateBarGradient } from '../../resources/frontend/styles/design-system/charts/chart-theme.js';
import { rmPieChartConfig, rmSemanticChartConfig, rmSparklineChartConfig } from '../../resources/frontend/styles/design-system/charts/chart-presets.js';
import { rmDestroyAllCharts, rmInitChart } from '../../resources/frontend/styles/design-system/charts/chart-livewire.js';

const css = readFileSync(new URL('../../resources/frontend/styles/design-system/tokens/chart-colors.css', import.meta.url), 'utf8');
const values = {
    '--rm-chart-cognitive-strong': '#876FAD',
    '--rm-chart-care-500': '#7FA883',
    '--rm-chart-clinical-500': '#7FAFD8',
    '--rm-chart-reference-500': '#B6ACA2',
    '--rm-chart-token-residents-primary': '#7FA883',
    '--rm-chart-token-residents-secondary': '#7FAFD8',
    '--rm-chart-token-alerts-primary': '#E28B79',
    '--rm-chart-token-alerts-secondary': '#D6C2A8',
    '--rm-line-stroke-width': '3px',
    '--rm-line-area-opacity': '.12',
    '--rm-bar-width': '18px',
    '--rm-bar-radius': '10px',
    '--rm-bar-fill-opacity': '.52',
    '--rm-bar-fill-hover-opacity': '.68',
    '--rm-donut-ring-opacity': '.52',
    '--rm-chart-mark-border-opacity': '.88',
    '--rm-donut-cutout': '76%',
    '--rm-chart-bar-enter-duration': '600ms',
    '--rm-chart-motion-update-duration': '420ms',
    '--rm-chart-stagger-fast': '35ms',
    '--rm-danger': '#B96D61',
};

globalThis.window = { matchMedia: () => ({ matches: false }) };
globalThis.document = { documentElement: { classList: { contains: () => false }, dataset: {} } };
globalThis.getComputedStyle = () => ({ getPropertyValue: name => values[name] ?? '' });

afterEach(() => { values['--rm-chart-care-500'] = '#7FA883'; });

test('la línea cognitiva dispone de una variante contrastada del token compartido', () => {
    assert.equal(rmChartColor('cognitive', 'strong'), '#876FAD');
});

test('rellenos sólidos conservan series y no dependen de degradados ni tamaño del canvas', () => {
    const ctx = { createLinearGradient() { throw new Error('No debe crear un degradado'); } };
    assert.equal(rmCreateGradient(ctx, '#7FA58D', 100), 'rgba(127, 165, 141, 0.12)');
    assert.equal(rmCreateBarGradient(ctx, '#7FA58D', 500), 'rgba(127, 165, 141, 0.52)');
    const datos = [2, null, 4];
    const sparkline = rmSparklineChartConfig(['Lun', 'Mar', 'Mié'], datos, '#7FA58D');
    assert.equal(typeof sparkline.data.datasets[0].backgroundColor, 'string');
    assert.deepEqual(sparkline.data.datasets[0].data, datos);
    const varias = rmSparklineChartConfig(['Lun'], [{ label: 'Serie real', data: [3], color: '#3F6B7D' }]);
    assert.equal(typeof varias.data.datasets[0].backgroundColor, 'string');
    assert.deepEqual(varias.data.datasets[0].data, [3]);
});

test('tokens semánticos y de estructura están en el módulo CSS global', () => {
    for (const name of ['care', 'clinical', 'alert', 'neutral', 'cognitive', 'rehab', 'reference']) {
        assert.match(css, new RegExp(`--rm-chart-${name}-500:`));
    }
    for (const name of ['surface-bg', 'surface-border', 'radius', 'blur', 'grid']) {
        assert.match(css, new RegExp(`--rm-chart-${name}:`));
    }
    assert.match(css, /:is\(\.dark, \[data-theme="dark"\]\)/);
});

test('paleta semántica refleja tokens actuales y distingue las series', () => {
    assert.equal(rmChartColor('residents'), '#7FA883');
    assert.equal(rmChartColor('residents', 'secondary'), '#7FAFD8');
    values['--rm-chart-care-500'] = '#819B84';
    assert.equal(rmChartColor('care'), '#819B84');
    assert.equal(rmChartNumber('--rm-bar-width', 0), 18);
    assert.equal(rmHexToRgba({ gradient: true }, .5).gradient, true);
    assert.equal(rmHexToRgba('var(--rm-danger)', .5), 'rgba(185, 109, 97, 0.5)');
});

test('factory produce línea, barras y donut coherentes sin inventar categorías', () => {
    const line = rmSemanticChartConfig('line', 'residents', ['Lun', 'Mar'], [
        { label: 'Actual', data: [3, 4] }, { label: 'Anterior', data: [2, 3] },
    ]);
    assert.equal(line.type, 'line');
    assert.equal(line.data.datasets[0].borderColor, '#7FA883');
    assert.equal(line.data.datasets[1].borderColor, '#7FAFD8');
    assert.equal(line.data.datasets[0].borderWidth, 3);

    const bars = rmSemanticChartConfig('bar', 'clinical', ['A'], [5]);
    assert.equal(bars.type, 'bar');
    assert.equal(bars.data.datasets[0].borderRadius, 10);
    assert.equal(bars.data.datasets[0].maxBarThickness, 18);
    assert.equal(bars.data.datasets[0].backgroundColor, 'rgba(127, 175, 216, 0.52)');
    assert.equal(bars.data.datasets[0].borderColor, 'rgba(127, 175, 216, 0.88)');
    assert.equal(bars.data.datasets[0].borderWidth, 2);

    assert.throws(() => rmSemanticChartConfig('donut', 'alerts', ['A', 'B', 'C'], [1, 2, 3]),
        /explicit semantic colors/);
    const donut = rmSemanticChartConfig('donut', 'alerts', ['Abiertas', 'Cerradas'], [1, 2]);
    assert.equal(donut.data.datasets[0].data[0], 1);
    assert.equal(donut.options.cutout, '76%');
    assert.equal(donut.data.datasets[0].backgroundColor[0], 'rgba(226, 139, 121, 0.52)');
    assert.equal(donut.data.datasets[0].borderColor[0], 'rgba(226, 139, 121, 0.88)');
    const pie = rmPieChartConfig(['A', 'B'], [1, 2], ['#7FA883', '#7FAFD8']);
    assert.equal(pie.data.datasets[0].backgroundColor[0], 'rgba(127, 168, 131, 0.52)');
    assert.equal(pie.data.datasets[0].borderColor[0], 'rgba(127, 168, 131, 0.88)');
    assert.throws(() => rmSemanticChartConfig('radar', 'rehab', ['A'], [
        { data: [1] }, { data: [2] }, { data: [3] },
    ]), /at most two series/);
});

test('Livewire actualiza valores sin desmontar la gráfica ni perder series ocultas', () => {
    const charts = new Map();
    class FakeChart {
        constructor(canvas, config) {
            this.canvas = canvas;
            this.config = { type: config.type };
            this.data = { labels: config.data.labels, datasets: config.data.datasets };
            this.options = config.options;
            this.visible = [true, false];
            this.updateCount = 0;
            this.destroyed = false;
            charts.set(canvas, this);
        }
        static getChart(canvas) { return charts.get(canvas); }
        isDatasetVisible(index) { return this.visible[index] ?? true; }
        setDatasetVisibility(index, visible) { this.visible[index] = visible; }
        update() { this.updateCount += 1; }
        destroy() { this.destroyed = true; charts.delete(this.canvas); }
    }
    globalThis.Chart = FakeChart;
    const canvas = {};
    const first = rmInitChart('motion-test', canvas, {
        type: 'line', data: { labels: ['Lun'], datasets: [{ label: 'Actual', data: [2] }, { label: 'Anterior', data: [1] }] }, options: {},
    });
    const updated = rmInitChart('motion-test', canvas, {
        type: 'line', data: { labels: ['Mar'], datasets: [{ label: 'Actual', data: [4] }, { label: 'Anterior', data: [3] }] }, options: {},
    });
    assert.equal(updated, first);
    assert.equal(first.destroyed, false);
    assert.equal(first.updateCount, 1);
    assert.deepEqual(first.data.datasets[0].data, [4]);
    assert.equal(first.isDatasetVisible(1), false);
    assert.equal(first.options.animation.duration, 420);
    rmDestroyAllCharts();
    delete globalThis.Chart;
});

test('animación inicial y actualización usan duraciones comunes y respetan movimiento reducido', () => {
    const plugins = [];
    const Chart = {
        defaults: {
            font: {}, animation: {}, interaction: {},
            elements: { line: {}, point: {}, bar: {}, arc: {} },
            plugins: { legend: { labels: {} }, tooltip: {} },
        },
        register: (...items) => plugins.push(...items),
        instances: {},
    };
    rmInstallGlobalChartTheme(Chart);
    const motion = plugins.find(plugin => plugin.id === 'rmSemanticMotion');
    const chart = {
        canvas: { closest: () => null },
        config: { type: 'bar', options: {} },
        options: { animation: {}, transitions: { active: { animation: {} } } },
    };
    motion.beforeInit(chart);
    assert.equal(chart.options.animation.duration(), 600);
    assert.equal(chart.options.animation.delay({ type: 'data', dataIndex: 2 }), 70);
    motion.afterRender(chart);
    assert.equal(chart.options.animation.duration(), 420);
    assert.equal(chart.options.animation.delay({ type: 'data', dataIndex: 2 }), 0);
    window.matchMedia = () => ({ matches: true });
    const reduced = { config: { type: 'line', options: {} }, options: { animation: {} } };
    motion.beforeInit(reduced);
    assert.equal(reduced.options.animation, false);
    window.matchMedia = () => ({ matches: false });
});
