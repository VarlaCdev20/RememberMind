import { test } from 'node:test';
import assert from 'node:assert/strict';

async function cargarSignos(conAlpine, caso) {
    const registros = [];
    const eventos = new Map();
    globalThis.window = conAlpine ? { Alpine: { data: (nombre, factory) => registros.push([nombre, factory]) } } : {};
    globalThis.document = { getElementById: () => null, addEventListener: (nombre, callback) => eventos.set(nombre, callback) };
    const modulo = await import('../../resources/frontend/scripts/modules/ficha-signos.js?' + caso);
    return { modulo, registros, eventos };
}

test('la ficha registra Signos cuando Alpine ya está disponible y evita el registro doble', async () => {
    const { registros, eventos } = await cargarSignos(true, 'carga-tardia');
    assert.equal(registros.length, 1);
    eventos.get('alpine:init')();
    assert.equal(registros.length, 1);
    assert.equal(registros[0][0], 'rmSignosVitalesModule');
});

test('la ficha espera Alpine sin perder su registro al cargar primero el bundle', async () => {
    const { registros, eventos } = await cargarSignos(false, 'carga-inicial');
    assert.equal(registros.length, 0);
    window.Alpine = { data: (nombre, factory) => registros.push([nombre, factory]) };
    eventos.get('alpine:init')();
    assert.equal(registros.length, 1);
});

test('cambiar métrica conserva el contrato Livewire y el estado de cada ficha', async () => {
    const { modulo } = await cargarSignos(true, 'interaccion');
    const a = modulo.crearSignosFicha({ metricaActiva: 'PA', periodoActivo: '7d', labels: ['15/10'] });
    const b = modulo.crearSignosFicha({ metricaActiva: 'FC', periodoActivo: '30d' });
    const llamadas = [];
    a.$wire = { setMetricaSignos: valor => llamadas.push(['metrica', valor]), setPeriodoSignos: valor => llamadas.push(['periodo', valor]) };
    a.reinitCharts = () => {};
    a.seleccionarMetrica('SPO2');
    a.cambiarPeriodo('30d');
    assert.equal(a.metricaActiva, 'SPO2');
    assert.equal(b.metricaActiva, 'FC');
    assert.equal(b.periodoActivo, '30d');
    assert.deepEqual(llamadas, [['metrica','SPO2'], ['periodo','30d']]);
    for (const metodo of ['renderMainChart','updateMainChart','getScalesForMetrica','getDatasetsForMetrica','renderSparklines']) assert.equal(typeof a[metodo], 'function');
});

test('preferencias antiguas del navegador no pueden cambiar la interpretación clínica', async () => {
    const { modulo } = await cargarSignos(true, 'servidor-autoritativo');
    const interpretaciones = [{ SPO2: { label: 'En objetivo médico', message: 'Objetivo autorizado en la fecha de toma.' } }];
    globalThis.localStorage = { getItem: () => { throw new Error('No debe consultar rangos del navegador'); } };
    window.addEventListener = () => {};
    const estado = modulo.crearSignosFicha({ interpretaciones });
    estado.reinitCharts = () => {};
    estado.init();
    estado.abrirModalRangos();
    assert.equal(estado.modalRangos, true);
    assert.deepEqual(estado.interpretaciones, interpretaciones);
    assert.equal(estado.guardarRangos, undefined);
    assert.equal(estado.rangos, undefined);
});

test('el tooltip conserva el resultado del servidor y una sola lectura sigue siendo un punto', async () => {
    const { modulo } = await cargarSignos(true, 'grafica-canonica');
    let grafica;
    window.RMCharts = { getCss: () => '#777777', color: () => '#777777', init: (_id, _canvas, config) => (grafica = config) };
    document.documentElement = { classList: { contains: () => false } };
    document.getElementById = () => ({ offsetParent: {} });
    const estado = modulo.crearSignosFicha({ metricaActiva: 'SPO2', labels: ['17/10 10:00'], spo2: [89],
        interpretaciones: [{ SPO2: { label: 'En objetivo médico', message: 'Objetivo 88–92 %.' } }] });
    estado.renderMainChart();
    assert.deepEqual(grafica.data.datasets[0].data, [89]);
    assert.deepEqual(grafica.data.labels, ['17/10 10:00']);
    assert.deepEqual(grafica.options.plugins.tooltip.callbacks.afterBody([{ dataIndex: 0 }]), [' Estado: En objetivo médico', 'Objetivo 88–92 %.']);
    assert.equal(grafica.plugins.some(p => p.id === 'normalRangeZone'), false);
});

test('abrir un evento reutiliza el registro interpretado y no fabrica autoría ni parámetros', async () => {
    const { modulo } = await cargarSignos(true, 'drawer-canonico');
    const registro = { id: 'COD_SIG_PRUEBA', estado: 'Crítico', responsable: 'Autora sintética', criterio: 'Resultado del servidor' };
    const estado = modulo.crearSignosFicha({ registros: [registro] });
    estado.abrirDrawerDesdeEvento({ registro: { id: registro.id }, estado: 'Normal' });
    assert.equal(estado.registroActivo, registro);
    assert.equal(estado.drawerAbierto, true);
    estado.cerrarDrawer();
    estado.abrirDrawerDesdeEvento({ registro: { id: 'INEXISTENTE' } });
    assert.equal(estado.drawerAbierto, false);
});
