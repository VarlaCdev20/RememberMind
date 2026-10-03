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
                        <button class="rm-topbar__action rm-topbar__theme-toggle">Tema</button>
                        <div><button class="rm-btn-icon">Alertas</button></div>
                        <div><button class="rm-topbar__action">Ajustes</button></div>
                        <div><button class="rm-topbar__profile">Perfil</button></div>
                      </div>
                    </header>
                    <main data-rm-main><div class="${variant === 'superadmin' ? 'rm-dashboard-composition--superadmin' : ''}">Contenido</div></main>
                    </div></body></html>`);
                    const result = await page.evaluate(() => {
                        const background = selector => getComputedStyle(document.querySelector(selector)).backgroundColor;
                        const buttons = [...document.querySelectorAll('.rm-topbar__actions button')].map(button => {
                            const rect = button.getBoundingClientRect();
                            return { width: rect.width, height: rect.height, left: rect.left, right: rect.right };
                        });
                        return { main: background('main'), sidebar: background('.rm-sidebar'), topbar: background('.rm-topbar'), buttons };
                    });
                    const context = `${variant || 'sistema'} ${theme} ${width}px`;
                    assert.equal(result.main, result.sidebar, context);
                    assert.equal(result.main, result.topbar, context);
                    for (const button of result.buttons) {
                        assert.ok(button.width >= 44 && button.height >= 44, `${context}: target visible`);
                        assert.ok(button.left >= 0 && button.right <= width + 1, `${context}: botón dentro de pantalla`);
                    }
                }
            }
        }
    } finally { await browser.close(); }
});
