import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

test('los shells por rol conservan fondo y controles completos en claro, oscuro y móvil', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [320, 390, 768, 1024, 1440]) {
            await page.setViewport({ width, height: 900 });
            for (const variant of ['', 'rm-shell--administracion', 'rm-nursing-shell', 'superadmin']) {
                for (const theme of ['light', 'dark']) {
                    await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head>
                    <body class="rm-shell ${variant}"><div class="rm-app-frame">
                    <aside class="rm-sidebar"><header class="rm-sidebar__header"></header></aside>
                    <header class="rm-topbar" data-rm-topbar="system">
                      <div class="rm-topbar__identity"><button class="rm-topbar__action rm-topbar__hamburger">Menú</button></div>
                      <div class="rm-topbar__search-slot"><div class="rm-topbar-search"><button class="rm-topbar-search__toggle">Buscar</button><form class="rm-topbar-search__form"><input aria-label="Buscar"></form></div></div>
                      <div class="rm-topbar__actions">
                        <div><button class="rm-topbar__action">Calendario</button></div>
                        <button class="rm-topbar__action rm-topbar__theme-toggle" aria-label="Cambiar tema"><i class="ph-bold ph-moon rm-topbar__theme-icon--moon"></i><i class="ph-bold ph-sun rm-topbar__theme-icon--sun"></i></button>
                        <div><button class="rm-btn-icon">Alertas</button></div>
                        <div><button class="rm-topbar__action">Ajustes</button></div>
                        <div><button class="rm-topbar__profile">Perfil</button></div>
                      </div>
                    </header>
                    <section data-role-preview-banner>Modo previsualización</section>
                    <main data-rm-main><div class="${variant === 'superadmin' ? 'rm-dashboard-composition--superadmin' : ''}">Contenido</div></main>
                    </div></body></html>`);
                    const result = await page.evaluate(() => {
                        const background = selector => getComputedStyle(document.querySelector(selector)).backgroundColor;
                        const buttons = [...document.querySelectorAll('.rm-topbar__actions button')].map(button => {
                            const rect = button.getBoundingClientRect();
                            return { width: rect.width, height: rect.height, left: rect.left, right: rect.right };
                        });
                        const topbar = document.querySelector('.rm-topbar').getBoundingClientRect();
                        const banner = document.querySelector('[data-role-preview-banner]').getBoundingClientRect();
                        const moon = getComputedStyle(document.querySelector('.rm-topbar__theme-icon--moon')).display;
                        const sun = getComputedStyle(document.querySelector('.rm-topbar__theme-icon--sun')).display;
                        return { main: background('main'), sidebar: background('.rm-sidebar'), topbar: background('.rm-topbar'), buttons, moon, sun, topbarBounds: { left: topbar.left, right: topbar.right }, bannerBounds: { left: banner.left, right: banner.right } };
                    });
                    const context = `${variant || 'sistema'} ${theme} ${width}px`;
                    assert.equal(result.main, result.sidebar, context);
                    assert.equal(result.main, result.topbar, context);
                    assert.equal(result.moon !== 'none', theme === 'light', `${context}: icono luna`);
                    assert.equal(result.sun !== 'none', theme === 'dark', `${context}: icono sol`);
                    assert.ok(Math.abs(result.bannerBounds.left - result.topbarBounds.left) <= 1, `${context}: aviso alineado con topbar`);
                    assert.ok(Math.abs(result.bannerBounds.right - result.topbarBounds.right) <= 1, `${context}: ancho del aviso alineado con topbar`);
                    for (const button of result.buttons) {
                        assert.ok(button.width >= 44 && button.height >= 44, `${context}: target visible`);
                        assert.ok(button.left >= 0 && button.right <= width + 1, `${context}: botón dentro de pantalla`);
                    }
                }
            }
        }
    } finally { await browser.close(); }
});

test('Enfermería conserva la misma jerarquía de encabezado y superficie de card que otros roles', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const theme of ['light', 'dark']) {
            const styles = [];
            for (const shellClass of ['rm-shell', 'rm-shell rm-nursing-shell']) {
                await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head><body class="${shellClass}"><main><section class="rm-dashboard-composition rm-nursing-dashboard"><header class="rm-dashboard-header rm-nursing-dashboard__welcome"><div class="rm-dashboard-header__content"><h1 class="rm-dashboard-header__title">Bienvenido</h1></div></header><section class="rm-card rm-nursing-dashboard__kpi"><h2>Tareas del turno</h2></section></section></main></body></html>`);
                styles.push(await page.evaluate(() => {
                    const heading = getComputedStyle(document.querySelector('.rm-dashboard-header__title'));
                    const card = getComputedStyle(document.querySelector('.rm-card'));
                    const grid = document.querySelector('.rm-nursing-dashboard').getBoundingClientRect();
                    const header = document.querySelector('.rm-dashboard-header').getBoundingClientRect();
                    return { headingFamily: heading.fontFamily, headingSize: heading.fontSize, headingColor: heading.color, cardBg: card.backgroundColor, cardRadius: card.borderRadius, cardShadow: card.boxShadow, headerWidth: header.width, gridWidth: grid.width };
                }));
            }
            assert.deepEqual(styles[1], styles[0], theme);
            assert.ok(styles[1].headerWidth >= styles[1].gridWidth - 1, `${theme}: encabezado ocupa toda la cuadrícula`);
        }
    } finally { await browser.close(); }
});
