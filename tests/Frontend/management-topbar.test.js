import test from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

test('la barra sin búsqueda usa una fila móvil y conserva controles legibles sin bisel', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [320, 390, 768, 1024, 1280, 1440]) {
            await page.setViewport({ width, height: 900 });
            for (const theme of ['light', 'dark']) for (const search of [false, true]) {
                await page.setContent(`<html data-theme="${theme}" class="${theme === 'dark' ? 'dark' : ''}"><head><style>${css}</style></head><body class="rm-shell"><div class="rm-app-frame"><header class="rm-topbar" data-rm-topbar="system"><div class="rm-topbar__identity"><button class="rm-topbar__action rm-topbar__hamburger" aria-label="Abrir menú">Menú</button></div>${search ? '<div class="rm-topbar-search"><button class="rm-topbar-search__toggle" aria-label="Buscar">Buscar</button></div>' : ''}<div class="rm-topbar__actions"><button class="rm-topbar__action">Fecha</button><button class="rm-topbar__action">Tema</button><div><button class="rm-btn-icon">Avisos</button></div><div><button class="rm-topbar__action">Ajustes</button></div><div><button class="rm-topbar__profile">Perfil</button></div></div></header><main data-rm-main>Contenido sintético</main></div></body></html>`);
                const result = await page.evaluate(() => {
                    const bar = document.querySelector('.rm-topbar').getBoundingClientRect();
                    return { height: bar.height, overflow: document.documentElement.scrollWidth > innerWidth,
                        controls: [...document.querySelectorAll('.rm-topbar button')].map(e => {
                            const rect = e.getBoundingClientRect();
                            const style = getComputedStyle(e);
                            return { width: rect.width, height: rect.height, left: rect.left, right: rect.right, top: rect.top, bottom: rect.bottom, image: style.backgroundImage, shadow: style.boxShadow };
                        }).filter(e => e.width > 0) };
                });
                const context = `${width}px ${theme} búsqueda=${search}`;
                assert.equal(result.overflow, false, context);
                if (width < 768) assert.equal(result.height, search ? 108 : 68, context);
                for (const control of result.controls) {
                    assert.ok(control.width >= 44 && control.height >= 44, `${context}: target`);
                    assert.ok(control.left >= 0 && control.right <= width, `${context}: dentro del viewport`);
                    assert.ok(control.top >= 0 && control.bottom <= result.height, `${context}: dentro de la barra`);
                    assert.equal(control.image, 'none', `${context}: sin degradado`);
                    assert.doesNotMatch(control.shadow, /inset/, `${context}: sin bisel`);
                }
            }
        }
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
        const duration = await page.$eval('.rm-topbar__action', e => getComputedStyle(e).transitionDuration);
        assert.ok(duration.split(',').every(value => parseFloat(value) <= 0.00001), 'movimiento reducido conserva feedback inmediato');
    } finally { await browser.close(); }
});

test('subtítulos y periodos del panel tienen contraste legible en ambos temas', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const theme of ['light', 'dark']) {
            await page.setContent(`<html data-theme="${theme}" class="${theme === 'dark' ? 'dark' : ''}"><head><style>${css}</style></head><body class="rm-shell"><main data-rm-main><section class="rm-dashboard-composition"><article class="rm-dashboard-data-panel rm-dashboard-data-panel--chart"><header class="rm-dashboard-data-panel__heading"><div class="rm-dashboard-data-panel__heading-copy"><h2>Cobertura de prueba</h2><p>Personas únicas por área</p></div><span class="rm-dashboard-data-panel__period">Hoy</span></header></article></section></main></body></html>`);
            const ratios = await page.evaluate(() => {
                const parse = value => {
                    const srgb = value.match(/^color\(srgb ([^)]+)\)/);
                    const rgb = value.match(/^rgba?\(([^)]+)\)/);
                    if (!srgb && !rgb) throw new Error(`Formato de color no soportado: ${value}`);
                    const parts = (srgb || rgb)[1].split(/[,\s/]+/).map(Number);
                    return [...parts.slice(0, 3).map(n => srgb ? n : n / 255), parts[3] ?? 1];
                };
                const mix = (fg, bg) => [...[0, 1, 2].map(i => fg[i] * fg[3] + bg[i] * (1 - fg[3])), 1];
                const luminance = color => color.slice(0, 3).reduce((sum, value, i) => sum + (value <= .04045 ? value / 12.92 : ((value + .055) / 1.055) ** 2.4) * [.2126, .7152, .0722][i], 0);
                return [...document.querySelectorAll('.rm-dashboard-data-panel__heading-copy p,.rm-dashboard-data-panel__period')].map(element => {
                    const ancestors = [];
                    for (let parent = element; parent; parent = parent.parentElement) ancestors.push(parent);
                    const bg = ancestors.reverse().reduce((color, parent) => mix(parse(getComputedStyle(parent).backgroundColor), color), [1, 1, 1, 1]);
                    const fg = mix(parse(getComputedStyle(element).color), bg);
                    return (Math.max(luminance(fg), luminance(bg)) + .05) / (Math.min(luminance(fg), luminance(bg)) + .05);
                });
            });
            assert.ok(ratios.every(ratio => ratio >= 4.5), `${theme}: contraste ${ratios.join(', ')}`);
        }
    } finally { await browser.close(); }
});
