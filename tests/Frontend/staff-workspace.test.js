// Offline QA of real Blade renders exported by PersonalInstitucionalResponsiveTest.
// This server has no access to the running app, its session or its database.
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync, mkdirSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { createServer } from 'node:http';
import puppeteer from 'puppeteer';

const root = process.cwd();
const fixtureDir = process.env.RM_STAFF_QA_DIR;
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
const ready = executablePath && fixtureDir && existsSync(join(fixtureDir, 'staff-resumen.html'));

test('Personal institucional conserva todas sus secciones sin scroll horizontal en cinco anchos y ambos temas', { skip: !ready }, async () => {
    const manifest = JSON.parse(readFileSync(join(root, 'public/build/manifest.json'), 'utf8'));
    const css = manifest['resources/frontend/styles/app.css'].file;
    const evidenceDir = process.env.RM_STAFF_EVIDENCE_DIR;
    if (evidenceDir) mkdirSync(evidenceDir, { recursive: true });
    const moduleSource = `
        import * as theme from '/charts/chart-theme.js';
        import * as presets from '/charts/chart-presets.js';
        const { Livewire, Alpine } = await import('/livewire.esm.js');
        theme.rmInstallGlobalChartTheme(Chart);
        window.RMCharts = { getCss:theme.rmGetCss, number:theme.rmChartNumber, color:theme.rmChartColor, palette:theme.rmChartPalette,
          presets:{ doughnut:presets.rmDoughnutChartConfig, bar:presets.rmBarChartConfig, barHorizontal:presets.rmBarHorizontalChartConfig },
          init(key,canvas,config){return new Chart(canvas,config);} };
        Alpine.magic('wire',()=>({$set(){},on(){},dispatch(){},entangle(){return false;}}));
        Livewire.start();
        await document.fonts.ready;
        window.qaReady=true;
    `;
    const server = createServer((req, res) => {
        try {
            const path = new URL(req.url, 'http://fixture.local').pathname;
            if (path === '/qa.mjs') { res.setHeader('Content-Type', 'application/javascript'); res.end(moduleSource); }
            else if (path === '/livewire.esm.js') { res.setHeader('Content-Type', 'application/javascript'); res.end(readFileSync(join(root, 'vendor/livewire/livewire/dist/livewire.esm.js'))); }
            else if (path === '/chart.js') { res.setHeader('Content-Type', 'application/javascript'); res.end(readFileSync(join(root, 'node_modules/chart.js/dist/chart.umd.js'))); }
            else if (path.startsWith('/charts/') && /^\/charts\/[a-z-]+\.js$/.test(path)) { res.setHeader('Content-Type', 'application/javascript'); res.end(readFileSync(join(root, 'resources/frontend/styles/design-system', path.slice(1)))); }
            else if (path.startsWith('/build/') && !path.includes('..')) { res.setHeader('Content-Type', path.endsWith('.css') ? 'text/css' : 'font/woff2'); res.end(readFileSync(join(root, 'public', path))); }
            else if (path.startsWith('/fixture/')) {
                const name = path.slice('/fixture/'.length);
                assert.match(name, /^[a-z-]+$/);
                let html = readFileSync(join(fixtureDir, `staff-${name}.html`), 'utf8')
                    .replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '')
                    .replace(/\s+wire:(?:snapshot|id|effects|key|name)="[^"]*"/g, '')
                    .replace(/<img\b[^>]*>/gi, '');
                if (['formulario-horario', 'asignaciones'].includes(name)) html = `<div class="rm-staff-workspace"><div class="rm-staff-overlay fixed inset-0 flex items-center justify-center"><section class="rm-staff-dialog flex flex-col w-full max-w-5xl"><header class="rm-staff-dialog__header">Horario sintético</header><div class="rm-staff-dialog__body flex flex-1"><div class="rm-staff-dialog__content flex-1 overflow-y-auto">${html}</div></div></section></div></div>`;
                res.setHeader('Content-Type', 'text/html');
                res.end(`<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"><script src="/chart.js"></script></head><body class="rm-shell" data-role="manager"><main data-rm-main style="width:calc(100% - var(--qa-sidebar,0px));margin-left:var(--qa-sidebar,0px);height:100dvh;padding:16px;overflow:auto">${html}</main><script>window.livewireScriptConfig={csrf:'synthetic',uri:'/qa-only'};</script><script type="module" src="/qa.mjs"></script></body></html>`);
            } else { res.statusCode = 404; res.end(); }
        } catch (error) { res.statusCode = 500; res.end(String(error)); }
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const browser = await puppeteer.launch({ headless: true, executablePath });
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.setRequestInterception(true);
    page.on('request', request => request.url().startsWith(`http://127.0.0.1:${server.address().port}/`) ? request.continue() : request.abort());
    let checked = 0;
    try {
        for (const width of [1440, 1280, 1024, 768, 390]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: 900 });
            await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
            for (const state of ['resumen', 'lista', 'tarjetas', 'paginado', 'salud', 'admin', 'disponibilidad', 'reportes', 'vacio', 'horarios', 'formulario-horario', 'asignaciones']) {
                await page.goto(`http://127.0.0.1:${server.address().port}/fixture/${state}`);
                await page.waitForFunction(() => window.qaReady);
                await page.evaluate(({ theme, width }) => {
                    document.documentElement.dataset.theme = theme;
                    document.documentElement.classList.toggle('dark', theme === 'dark');
                    document.body.style.setProperty('--qa-sidebar', width >= 1024 ? '224px' : '0px');
                }, { theme, width });
                await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
                const result = await page.evaluate(() => {
                    const visible = e => e.checkVisibility() && e.getBoundingClientRect().width > 0;
                    const boxes = [...document.querySelectorAll('main, .rm-staff-tabs, .rm-staff-table-wrap, .rm-filter-bar, .rm-staff-dialog, .rm-staff-dialog__content')].filter(visible);
                    return {
                        overflow: boxes.filter(e => e.scrollWidth > e.clientWidth + 2).map(e => ({ classes: e.className, client: e.clientWidth, scroll: e.scrollWidth })),
                        controls: [...document.querySelectorAll('main button, main input:not([type="checkbox"], [type="radio"]), main select')].filter(visible).filter(e => e.getBoundingClientRect().height < 43.9).map(e => e.outerHTML.slice(0,160)),
                        tabs: document.querySelectorAll('.rm-staff-tabs button').length,
                        metricColumns: document.querySelector('.rm-staff-metrics') ? getComputedStyle(document.querySelector('.rm-staff-metrics')).gridTemplateColumns.split(' ').length : null,
                        cells: [...document.querySelectorAll('.rm-staff-table tbody td:not([colspan])')].filter(e => !e.dataset.label).length,
                        firstCells: [...document.querySelectorAll('.rm-staff-table tbody td:first-child:not([colspan])')].map(e => e.getBoundingClientRect().width),
                        clippedRows: [...document.querySelectorAll('.rm-staff-table tbody tr')].filter(e => e.scrollHeight > e.clientHeight + 2).map(e => ({height:e.clientHeight,content:e.scrollHeight})),
                        forbidden: /\b(null|NaN|undefined|Infinity)\b|\[object Object\]/.test(document.querySelector('main').innerText),
                        dialogs: [...document.querySelectorAll('.rm-staff-dialog')].map(e => { const b=e.getBoundingClientRect();return {left:b.left,right:b.right,top:b.top,bottom:b.bottom}; }),
                        charts: [...document.querySelectorAll('.rm-chart-card canvas')].filter(visible).map(e => ({ rendered:!!Chart.getChart(e), width:e.getBoundingClientRect().width, height:e.getBoundingClientRect().height })),
                    };
                });
                const context = `${state} ${width}px ${theme}`;
                assert.deepEqual(result.overflow, [], `${context}: overflow ${JSON.stringify(result.overflow)}`);
                assert.deepEqual(result.controls, [], `${context}: controles 44px ${JSON.stringify(result.controls)}`);
                assert.equal(result.cells, 0, `${context}: campos identificados`);
                assert.deepEqual(result.clippedRows, [], `${context}: filas sin superposición`);
                if (width === 390 && result.metricColumns !== null) assert.equal(result.metricColumns, 2, `${context}: resumen en dos columnas`);
                if (width === 390) for (const cellWidth of result.firstCells) assert.ok(cellWidth >= 250, `${context}: identidad legible (${cellWidth}px)`);
                assert.equal(result.forbidden, false, context);
                if (!['formulario-horario', 'asignaciones'].includes(state)) assert.equal(result.tabs, 5, context);
                for (const dialog of result.dialogs) assert.ok(dialog.left >= -1 && dialog.right <= width + 1 && dialog.top >= -1 && dialog.bottom <= 901, `${context}: diálogo visible ${JSON.stringify(dialog)}`);
                for (const chart of result.charts) assert.ok(chart.rendered && chart.width > 0 && chart.height > 0, `${context}: gráfica real visible`);
                if (state === 'resumen') {
                    assert.equal(await page.$eval('#staff-summary-filters', e => e.checkVisibility()), false, `${context}: filtros secundarios plegados al entrar`);
                    assert.equal(await page.$$eval('.rm-staff-overview dd', e => e.length), 4, `${context}: cuatro indicadores conservados`);
                    assert.equal(await page.$eval('.rm-staff-overview', e => getComputedStyle(e).display), 'flex', `${context}: contexto en texto sin rejilla de tarjetas`);
                    const nestedShadows = await page.$$eval('.rm-staff-overview, .rm-staff-list__row, .rm-staff-summary-toolbar', elements => elements.filter(e => getComputedStyle(e).boxShadow !== 'none').map(e => e.className));
                    assert.deepEqual(nestedShadows, [], `${context}: sin sombras independientes dentro del resumen y directorio`);
                    const firstPersonTop = await page.$eval('.rm-person-entity-card', e => e.getBoundingClientRect().top);
                    assert.ok(firstPersonTop < 700, `${context}: el directorio aparece en la primera pantalla (${firstPersonTop}px)`);
                    await page.focus('button[aria-controls="staff-summary-filters"]');
                    await page.keyboard.press('Space');
                    await page.waitForFunction(() => document.querySelector('#staff-summary-filters').checkVisibility());
                    assert.equal(await page.$eval('button[aria-controls="staff-summary-filters"]', e => e.getAttribute('aria-expanded')), 'true', `${context}: estado accesible del desplegable`);
                    const expanded = await page.$eval('#staff-summary-filters', e => ({ controls:[...e.querySelectorAll('select')].length, overflow:e.scrollWidth > e.clientWidth + 2 }));
                    assert.equal(expanded.controls, 3, `${context}: filtros completos`);
                    assert.equal(expanded.overflow, false, `${context}: filtros desplegados sin desbordamiento`);
                    await page.keyboard.press('Space');
                    await page.waitForFunction(() => !document.querySelector('#staff-summary-filters').checkVisibility());
                }
                if (state === 'salud') {
                    await page.click('button[aria-controls="staff-directory-filters"]');
                    await page.waitForFunction(() => document.querySelector('#staff-directory-filters').checkVisibility());
                    assert.equal(await page.$eval('#staff-directory-filters', e => e.checkVisibility()), true, `${context}: filtros desplegables`);
                }
                if (state === 'lista') {
                    await page.focus('.rm-staff-list button');
                    assert.equal(await page.evaluate(() => getComputedStyle(document.activeElement).outlineStyle), 'solid', `${context}: foco visible`);
                }
                if (state === 'horarios') {
                    assert.equal(await page.evaluate(() => !!document.activeElement.closest('.rm-staff-dialog')), true, `${context}: foco dentro del diálogo`);
                    for (let index = 0; index < 8; index++) {
                        await page.keyboard.press('Tab');
                        assert.equal(await page.evaluate(() => !!document.activeElement.closest('.rm-staff-dialog')), true, `${context}: teclado contenido en la ficha`);
                    }
                }
                if (state === 'paginado') assert.equal(await page.$$eval('.rm-staff-list__row', elements => elements.length), 12, `${context}: página completa sin truncamiento oculto`);
                if (evidenceDir && theme === 'light' && [390, 1440].includes(width) && ['resumen', 'lista', 'tarjetas', 'paginado', 'salud', 'reportes', 'horarios', 'formulario-horario', 'asignaciones'].includes(state)) {
                    if (state === 'salud') await page.$eval('.rm-staff-table', e => e.scrollIntoView({block:'start'}));
                    else if (state === 'reportes') await page.$eval('.rm-chart-card', e => e.scrollIntoView({block:'start'}));
                    await page.screenshot({ path: join(resolve(evidenceDir), `staff-${state}-${width}.png`), fullPage: true });
                }
                checked++;
            }
        }
        assert.deepEqual(errors, [], 'Sin errores JavaScript en las fixtures');
        assert.equal(checked, 120);
        await page.goto(`http://127.0.0.1:${server.address().port}/fixture/filtros`);
        await page.waitForFunction(() => window.qaReady);
        assert.equal(await page.$eval('#staff-summary-filters', e => e.checkVisibility()), true, 'Filtros activos visibles al entrar');
        // La fixture no hidrata wire:model; el estado se prueba en PHP y aquí sus chips visibles.
        const activeFilters = await page.$eval('.rm-filter-bar__active', e => e.innerText);
        assert.match(activeFilters, /Rubro: Salud/, 'El tipo aplicado sigue visible');
        assert.match(activeFilters, /Estado: Activo/, 'El estado aplicado sigue visible');
        // Los anchos entre móvil y tablet cruzan el breakpoint del header compartido.
        // Una base flex horizontal no debe convertirse en altura al apilarse.
        for (const width of [640, 672, 700, 735, 760, 767, 768, 832]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: 900 });
            await page.goto(`http://127.0.0.1:${server.address().port}/fixture/resumen`);
            await page.waitForFunction(() => window.qaReady);
            await page.evaluate(theme => {
                document.documentElement.dataset.theme = theme;
                document.documentElement.classList.toggle('dark', theme === 'dark');
            }, theme);
            const header = await page.$eval('.rm-collection-header', e => {
                const box = e.getBoundingClientRect();
                return { height: box.height, direction: getComputedStyle(e).flexDirection };
            });
            assert.ok(header.height <= 300, `${width}px ${theme}: cabecera compacta (${header.height}px, ${header.direction})`);
            if (evidenceDir && width === 735 && theme === 'light') await page.screenshot({ path: join(resolve(evidenceDir), 'staff-header-735.png'), fullPage: true });
        }
        assert.deepEqual(errors, [], 'Sin errores JavaScript también en anchos intermedios');
        await page.setViewport({ width: 390, height: 600 });
        await page.goto(`http://127.0.0.1:${server.address().port}/fixture/formulario-horario`);
        await page.waitForFunction(() => window.qaReady);
        await page.focus('button[wire\\:click="solicitarGuardar"]');
        const footer = await page.evaluate(() => { const b = document.activeElement.getBoundingClientRect(); return {top:b.top,bottom:b.bottom}; });
        assert.ok(footer.top >= 0 && footer.bottom <= 601, 'La acción de guardar sigue alcanzable con teclado en pantalla baja, sin ejecutarla');
    } finally { await browser.close(); await new Promise(resolve => server.close(resolve)); }
});
