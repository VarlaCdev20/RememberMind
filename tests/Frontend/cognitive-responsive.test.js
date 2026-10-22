import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = read(`public/build/${manifest['resources/frontend/styles/app.css'].file}`);
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
const areas = ['Aprendizaje y memoria', 'Atención', 'Funciones ejecutivas', 'Lenguaje', 'Habilidades visuoespaciales y visuoconstructivas'];
const button = text => `<button type="button" class="rm-btn rm-btn-secondary">${text}</button>`;
const context = `<div class="rm-cognitive-identity-header"><div class="rm-cognitive-person"><span class="rm-cognitive-avatar">PS</span><div><p class="rm-cognitive-person-label">Residente en consulta</p><h2>Persona Sintética Apellidos de Prueba con Nombre Extenso</h2><p>81 años · Habitación de referencia · Cama de referencia</p></div></div><span class="rm-orion-state">Solo lectura · Enfermería</span></div><dl class="rm-cognitive-meta"><div><dt>Fecha de evaluación</dt><dd>20/10/2026 09:00</dd></div><div><dt>Versión utilizada</dt><dd>VERSION_DOCUMENTAL_EXTENSA_CON_IDENTIDAD_CONSERVADA_2026</dd></div><div><dt>Estado</dt><dd>Información insuficiente</dd></div></dl>`;
const table = `<table class="rm-orion-table"><caption>Comparación de estados conservados</caption><thead><tr><th scope="col">Área</th><th scope="col">Inicial</th><th scope="col">Reciente</th></tr></thead><tbody>${areas.map(area => `<tr><th scope="row">${area}</th><td data-label="Inicial · 18/10/2026 · Versión documental extensa">Información insuficiente dentro del alcance evaluado</td><td data-label="Reciente · 20/10/2026 · Versión documental extensa">Sin dificultad evidenciada dentro del alcance evaluado</td></tr>`).join('')}</tbody></table>`;
const filters = `<div class="rm-cognitive-filters">${['Inicial', 'Reciente', 'Área', 'Instrumento'].map((name, index) => `<div><label for="filter-${index}">${name}</label><select class="rm-select" id="filter-${index}"><option>Una versión de instrumento con nombre muy largo y referencia documental</option></select></div>`).join('')}</div>`;
const shell = (theme, content) => `<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${css}</style></head><body class="rm-shell rm-nursing-shell"><div class="rm-app-frame"><main data-rm-main style="padding:16px"><div class="rm-expert-results"><div class="rm-cognitive-reading">${content}</div></div></main></div></body></html>`;

test('consulta cognitiva mantiene identidad, acciones y áreas legibles sin overflow', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [390, 650, 768, 1024, 1280, 1440]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: 900 });
            await page.setContent(shell(theme, `<header class="rm-expert-heading"><div><h1>Evaluación cognitiva y cuidados</h1><p>Consulta del residente con información conservada.</p></div><a class="rm-expert-back" href="#profile">Volver a Mis residentes</a></header><section class="rm-card rm-cognitive-identity">${context}</section><div class="rm-cognitive-context">Persona Sintética · Solo lectura</div><section class="rm-card rm-orion-result"><h2>¿Qué encontró el sistema?</h2><div class="rm-orion-toolbar">${button('¿Qué significa este resultado?')}${button('¿Por qué se obtuvo este resultado?')}</div></section><section class="rm-card rm-orion-panel" id="profile"><div class="rm-orion-domain-grid">${areas.map(area => `<button class="rm-orion-domain" type="button"><strong>${area}</strong><span>Información insuficiente dentro del alcance evaluado</span></button>`).join('')}</div></section><div class="rm-orion-area-layout"><section class="rm-card rm-orion-panel">Interpretación y registros</section><aside class="rm-card rm-orion-panel">Cuidados del residente</aside></div>`));
            const result = await page.evaluate(() => {
                const main = document.querySelector('main');
                const bounds = [...document.querySelectorAll('.rm-cognitive-reading button, .rm-cognitive-person, .rm-cognitive-meta')].map(element => { const r = element.getBoundingClientRect(); return { element: element.className, left: r.left, right: r.right, height: r.height, scroll: element.scrollWidth, width: element.clientWidth }; });
                return { overflow: main.scrollWidth > main.clientWidth + 1, bounds, identityHeight: document.querySelector('.rm-cognitive-identity').getBoundingClientRect().height, contentHeight: document.querySelector('.rm-cognitive-identity-header').getBoundingClientRect().height + document.querySelector('.rm-cognitive-identity > .rm-cognitive-meta').getBoundingClientRect().height, identity: ['display','flexWrap','gridTemplateColumns','width'].map(p => getComputedStyle(document.querySelector('.rm-cognitive-identity'))[p]), context: getComputedStyle(document.querySelector('.rm-cognitive-context')).position, columns: getComputedStyle(document.querySelector('.rm-orion-domain-grid')).gridTemplateColumns.split(' ').length };
            });
            assert.equal(result.overflow, false, `${theme} ${width}px: página sin desbordamiento`);
            for (const rect of result.bounds) { assert.ok(rect.left >= 0 && rect.right <= width + 1, `${theme} ${width}px: ${JSON.stringify(rect)} ${JSON.stringify(result.identity)}`); assert.ok(rect.scroll <= rect.width + 1, `${width}px: texto dentro de su control ${JSON.stringify(rect)}`); }
            assert.ok(result.identityHeight - result.contentHeight < 70, `${width}px: sin altura vacía entre identidad y evaluación`);
            if (width === 390) { assert.equal(result.context, 'sticky'); assert.equal(result.columns, 1); }
            await page.focus('.rm-orion-toolbar button');
            assert.equal(await page.$eval(':focus', element => getComputedStyle(element).outlineStyle), 'solid');
            assert.ok(await page.$eval(':focus', element => element.getBoundingClientRect().height >= 44));
        }
    } finally { await browser.close(); }
});

test('identidad compacta aparece solo después de abandonar la tarjeta y desconecta su observador', () => {
    const source = read('app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/enfermeria.blade.php');
    const definition = source.match(/class="rm-cognitive-reading" x-data="([\s\S]*?)">/)[1];
    let notify, observed, disconnected = false;
    class Observer {
        constructor(callback) { notify = callback; }
        observe(element) { observed = element; }
        disconnect() { disconnected = true; }
    }
    const state = new Function('IntersectionObserver', `return (${definition})`)(Observer);
    state.$refs = { residenteContexto: {} };
    state.$nextTick = callback => callback();
    state.init();
    assert.equal(observed, state.$refs.residenteContexto);
    assert.equal(state.contextoFuera, false);
    notify([{ isIntersecting: false, boundingClientRect: { top: 300 } }]);
    assert.equal(state.contextoFuera, false);
    notify([{ isIntersecting: false, boundingClientRect: { top: -200 } }]);
    assert.equal(state.contextoFuera, true);
    notify([{ isIntersecting: true, boundingClientRect: { top: 0 } }]);
    assert.equal(state.contextoFuera, false);
    state.destroy();
    assert.equal(disconnected, true);
    assert.match(source, /x-show="contextoFuera" x-cloak/);
});

test('ventanas de lectura adaptan filtros, tablas y pie en cinco anchos y altura reducida', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [390, 768, 1024, 1280, 1440]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: width === 390 ? 600 : 900 });
            await page.setContent(shell(theme, `<div class="rm-modal-shell rm-modal-shell--reading fixed inset-0 flex items-center justify-center p-4"><div class="rm-modal-shell__overlay"></div><div role="dialog" aria-modal="true" aria-labelledby="modal-title" class="rm-modal-panel relative w-full flex flex-col" style="max-width:900px"><header class="rm-modal-header shrink-0 flex justify-between gap-4 px-5 py-4"><div class="rm-modal-header__heading"><h2 class="rm-modal-panel__title" id="modal-title">Evolución cognitiva y comparación de evaluaciones conservadas</h2></div><button type="button" class="rm-modal-panel__close" aria-label="Cerrar">×</button></header><div class="rm-modal-body flex-1 min-h-0 overflow-y-auto px-5 py-4"><div class="rm-cognitive-evolution">${filters}<div class="rm-orion-tabs">${button('Evolución en el tiempo')}${button('Comparar áreas')}${button('Ver datos')}</div><div class="rm-chart-body rm-cognitive-chart"><canvas></canvas></div><div class="rm-orion-table-wrap">${table}</div></div></div><footer class="rm-modal-footer shrink-0 flex flex-col sm:flex-row items-center justify-end gap-3 px-5 py-3">${button('Ver datos')}${button('Cerrar')}</footer></div></div>`));
            const result = await page.evaluate(() => {
                const panel = document.querySelector('.rm-modal-panel');
                const body = document.querySelector('.rm-modal-body');
                const rect = panel.getBoundingClientRect();
                const footer = document.querySelector('.rm-modal-footer').getBoundingClientRect();
                return { top: rect.top, bottom: rect.bottom, height: innerHeight, overflow: body.scrollWidth > body.clientWidth + 1, scrollable: body.scrollHeight > body.clientHeight, footer: footer.bottom, close: document.querySelector('.rm-modal-panel__close').getBoundingClientRect().width, tableRow: getComputedStyle(document.querySelector('tbody tr')).display, filterColumns: getComputedStyle(document.querySelector('.rm-cognitive-filters')).gridTemplateColumns.split(' ').length };
            });
            assert.equal(result.overflow, false, `${theme} ${width}px: contenido sin scroll horizontal`);
            assert.ok(result.top >= 0 && result.bottom <= result.height + 1);
            assert.ok(result.footer <= result.height + 1);
            assert.ok(result.close >= 44);
            if (width === 390) { assert.equal(result.tableRow, 'block'); assert.equal(result.filterColumns, 1); assert.equal(result.scrollable, true); }
        }
    } finally { await browser.close(); }
});

test('panel de evidencias conserva cierre y datos largos al cambiar su ancho', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [390, 768, 1024, 1280, 1440]) {
            await page.setViewport({ width, height: 600 });
            await page.setContent(shell('light', `<div class="rm-drawer-shell rm-drawer-shell--reading fixed inset-0"><div class="rm-drawer-backdrop"></div><div class="fixed inset-y-0 right-0 flex"><aside role="dialog" aria-label="Registros y evidencias" class="rm-drawer rm-drawer-lg"><header class="rm-drawer-header"><div class="rm-drawer-header-row"><div><h2 class="rm-drawer-title">Registros y evidencias de la evaluación cognitiva</h2><p class="rm-drawer-subtitle">Persona Sintética con Nombre Extenso</p></div><button class="rm-btn-icon rm-drawer__close" aria-label="Cerrar">×</button></div></header><div class="rm-drawer-body"><div class="rm-orion-tabs">${button('Interpretación')}${button('Registros')}${button('Cuidados')}</div><dl class="rm-cognitive-meta"><div><dt>Instrumento</dt><dd>INSTRUMENTO_CON_NOMBRE_MUY_EXTENSO_DE_PRUEBA</dd></div><div><dt>Referencia</dt><dd>Referencia documental conservada para esta medición</dd></div></dl><section class="rm-orion-care-group"><h3>Cuidados programados</h3><article class="rm-orion-care-row"><div><strong>Intervención con descripción extensa para comprobar la adaptación del panel</strong><p>${'Descripción documental sintética. '.repeat(30)}</p>${button('Ver detalle del cuidado')}</div></article></section></div></aside></div></div>`));
            const result = await page.evaluate(() => {
                const drawer = document.querySelector('.rm-drawer').getBoundingClientRect();
                const body = document.querySelector('.rm-drawer-body');
                const close = document.querySelector('.rm-drawer__close').getBoundingClientRect();
                return { left: drawer.left, right: drawer.right, bottom: drawer.bottom, close: { width: close.width, right: close.right }, overflow: body.scrollWidth > body.clientWidth + 1, scrollable: body.scrollHeight > body.clientHeight };
            });
            assert.ok(result.left >= 0 && result.right <= width + 1 && result.bottom <= 601);
            assert.ok(result.close.width >= 44 && result.close.right <= width + 1);
            assert.equal(result.overflow, false, `${width}px: el panel mantiene todo el contenido dentro de su ancho`);
            assert.equal(result.scrollable, true);
        }
    } finally { await browser.close(); }
});
