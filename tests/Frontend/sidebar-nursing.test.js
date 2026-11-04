import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { createServer } from 'node:http';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = file => readFileSync(new URL(file, root), 'utf8');
const blade = read('resources/views/components/layout/barra-lateral-sistema.blade.php');
const shell = read('resources/views/components/layout/sidebar-shell-state.blade.php');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = manifest['resources/frontend/styles/app.css'].file;
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
const sectionKeys = ['Mi turno', 'Mis residentes', 'Medicación', 'Alertas', 'Incidentes', 'Cuidados', 'Pase de turno'];
const labels = ['Signos vitales', 'Dolor', 'Cognición', 'Conducta', 'Sueño', 'Ingesta', 'Hidratación', 'Eliminación', 'Movilidad', 'Heridas'];
const sidebarData = blade.match(/x-data="([\s\S]*?)"\s+x-on:livewire:navigating/)[1]
    .replace('@js($sidebarStorageKey)', "'nursing-sidebar-test'")
    .replace('@js($activeNursingSection)', "'Cuidados'")
    .replace("@js(array_map(static fn ($section) => $section['title'], $sections))", JSON.stringify(sectionKeys).replaceAll('"', "'"))
    .replace(/\{\{ \$initialOpen[^}]+\}\}/, '5');
const click = blade.match(/@click="if \(sidebarCollapsed[^\"]+"/)[0].replaceAll('{{ $index }}', '5');
const escape = blade.match(/@keydown.escape.window="([^\"]+)"/)[0];
const init = blade.match(/x-init="([^\"]+)"/)[0];
const link = (label, index, className) => `<a href="#item-${index}" class="${className}"><span class="${className === 'rm-sidebar__item' ? 'rm-sidebar__label rm-nav-group' : ''}">${label}</span></a>`;
const fixture = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"><script src="/livewire.js" defer></script></head>
<body class="rm-shell" x-data="{${shell}}" x-init="initSidebar()" :class="{'is-sidebar-collapsed':sidebarCollapsed}" @resize.window.debounce.150ms="syncSidebarViewport()">
<button id="sidebar-mobile-trigger" @click="sidebarOpen=true">Abrir menú</button>
<aside class="rm-sidebar" data-rm-nursing :class="{'is-mobile-open':sidebarOpen}" :inert="sidebarRange==='drawer'&&!sidebarOpen" x-data="${sidebarData}" ${init} ${escape}>
<header class="rm-sidebar__header"><span class="rm-sidebar__brand-copy">RememberMind</span><button class="rm-sidebar__header-toggle" @click="toggleSidebarCollapse()">Contraer</button><button class="rm-sidebar__mobile-close" x-ref="mobileClose" @click="sidebarOpen=false">Cerrar</button></header>
<nav class="rm-sidebar__nav" x-ref="navigation">
${sectionKeys.slice(0, 5).map((label, i) => `<div class="rm-sidebar__entry">${link(label, i, 'rm-sidebar__item')}</div>`).join('')}
<div class="rm-sidebar__entry"><button id="rm-sidebar-trigger-5" class="rm-sidebar__item" ${click}><span class="rm-sidebar__label rm-nav-group">Cuidados</span></button>
<div class="rm-sidebar__submenu" x-show="openSection===5&&(!sidebarCollapsed||innerWidth<1024)" x-collapse.duration.200ms style="display:none"><div class="rm-sidebar__submenu-content is-visible">${labels.map((label, i) => link(label, i, 'rm-sidebar__subitem')).join('')}</div></div></div>
<div class="rm-sidebar__entry">${link('Pase de turno', 6, 'rm-sidebar__item')}</div></nav>
<div id="rm-sidebar-flyout-5" class="rm-sidebar__flyout" x-show="sidebarCollapsed&&flyoutIndex===5&&innerWidth>=1024" :style="{top:flyoutTop+'px'}" x-transition x-cloak>${labels.map((label, i) => link(label, i, 'rm-sidebar__flyout-link')).join('')}</div>
<footer class="rm-sidebar__footer"><div class="rm-sidebar__account-trigger">Cuenta de prueba</div></footer></aside><main data-rm-main>Contenido de prueba</main></body></html>`;
const nursingFixture = fixture
    .replace(/<nav class="rm-sidebar__nav"[\s\S]*?<\/nav>/, `<nav class="rm-sidebar__nav" x-ref="navigation">${sectionKeys.map((label, i) => `<div class="rm-sidebar__entry">${link(label, i, 'rm-sidebar__item')}</div>`).join('')}</nav>`)
    .replace(/<div id="rm-sidebar-flyout-5"[\s\S]*?<\/div>/, '');

test('enfermería: siete accesos directos visibles; otros roles conservan foco del submenú compacto', async () => {
    assert.ok(executablePath, 'Se necesita Chrome para comprobar el sidebar.');
    const server = createServer((req, res) => {
        if (req.url.startsWith('/build/')) {
            res.setHeader('Content-Type', req.url.endsWith('.css') ? 'text/css' : 'font/woff2');
            res.end(read(`public${req.url}`));
        } else if (req.url === '/livewire.js') {
            res.setHeader('Content-Type', 'application/javascript');
            res.end(read('vendor/livewire/livewire/dist/livewire.js'));
        } else {
            res.setHeader('Content-Type', 'text/html');
            res.end(req.url === '/shared' ? fixture : nursingFixture);
        }
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    let browser;
    try {
        browser = await puppeteer.launch({ headless: true, executablePath });
        const page = await browser.newPage();
        page.setDefaultTimeout(6000);
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 800 });
            await page.goto(`http://127.0.0.1:${server.address().port}`);
            await page.waitForFunction(() => document.body.hasAttribute('data-rm-sidebar-ready'));
            if (width < 1024) await page.click('#sidebar-mobile-trigger');
            await page.waitForFunction(() => document.querySelector('aside').getBoundingClientRect().left === 0);
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `${width}: sin desbordamiento horizontal`);
            const visible = await page.evaluate(() => {
                const nav = document.querySelector('nav').getBoundingClientRect();
                const items = [...document.querySelectorAll('nav > .rm-sidebar__entry > a')].map(el => el.getBoundingClientRect());
                return items.length === 7 && items.every(r => r.top >= nav.top && r.bottom <= nav.bottom);
            });
            assert.equal(visible, true, `${width}: siete accesos principales visibles sin desplegar formularios`);
            if (width === 1024) {
                await page.goto(`http://127.0.0.1:${server.address().port}/shared`);
                await page.waitForFunction(() => document.body.hasAttribute('data-rm-sidebar-ready'));
                await page.focus('#rm-sidebar-trigger-5');
                await page.keyboard.press('Enter');
                await page.waitForFunction(() => document.activeElement.matches('#rm-sidebar-flyout-5 a:first-child'));
                await page.keyboard.press('Tab');
                assert.equal(await page.evaluate(() => document.activeElement.textContent.trim()), 'Dolor');
                await page.keyboard.press('Escape');
                await page.waitForFunction(() => document.activeElement.id === 'rm-sidebar-trigger-5');
                await page.waitForFunction(() => getComputedStyle(document.querySelector('.rm-sidebar__flyout')).display === 'none');
            }
        }
        assert.deepEqual(errors, []);
    } finally {
        await browser?.close();
        await new Promise(resolve => server.close(resolve));
    }
});
