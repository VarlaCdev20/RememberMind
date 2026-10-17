import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

test('relieve compartido, acciones estables y movimiento reducido en cinco anchos', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [1440, 1280, 1024, 768, 390]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: 900 });
            for (const motion of ['no-preference', 'reduce']) {
                await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: motion }]);
                await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}"><head><style>${css}</style><style>main{padding:24px}.qa-grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr))}.qa-grid>*{min-width:0}#info{padding:20px}#clinical{padding:20px}</style></head><body class="rm-shell"><main data-rm-main><div class="rm-dashboard-composition rm-nursing-dashboard"><div class="qa-grid" style="grid-column:1/-1"><div id="shadow-reference" style="box-shadow:var(--rm-shadow-card)"></div><section id="info" class="rm-card rm-nursing-module">Información del turno: esta tarjeta no es un botón.</section><a id="action" href="#details" class="rm-card rm-card-interactive">Ver residentes asignados</a><section id="clinical" class="rm-clinical-form__section"><label for="note">Observación de prueba</label><input id="note" class="rm-input"></section><button id="button" class="rm-btn rm-btn-secondary">Ver historial</button><button id="disabled" class="rm-btn rm-btn-primary" disabled>Guardando</button></div></div></main></body></html>`);
                await page.hover('#info');
                const info = await page.$eval('#info', el => ({ shadow: getComputedStyle(el).boxShadow, transform: getComputedStyle(el).transform, cursor: getComputedStyle(el).cursor }));
                assert.notEqual(info.shadow, 'none');
                assert.equal(info.shadow, await page.$eval('#shadow-reference', el => getComputedStyle(el).boxShadow), 'el dashboard consume el relieve compartido');
                assert.equal(info.transform, 'none', 'la tarjeta informativa no simula un enlace');
                assert.notEqual(info.cursor, 'pointer');
                await page.focus('#note');
                assert.equal(await page.$eval('#note', el => getComputedStyle(el).outlineStyle), 'solid');
                await page.hover('#action');
                await page.waitForFunction(expected => {
                    const t = getComputedStyle(document.querySelector('#action')).transform;
                    return expected === 'reduce' ? t === 'none' : t !== 'none' && new DOMMatrix(t).m42 < -1.9;
                }, {}, motion);
                const card = await page.$eval('#action', el => ({ shadow: getComputedStyle(el).boxShadow, transition: getComputedStyle(el).transitionDuration }));
                assert.notEqual(card.shadow, 'none');
                if (motion === 'reduce') assert.ok(card.transition.split(',').every(value => parseFloat(value) <= .001), card.transition);
                await page.hover('#button');
                await page.mouse.down();
                await page.waitForFunction(expected => {
                    const t = getComputedStyle(document.querySelector('#button')).transform;
                    return expected === 'reduce' ? t === 'none' : t !== 'none' && new DOMMatrix(t).m42 > .9 && new DOMMatrix(t).a < .99;
                }, {}, motion);
                await page.mouse.up();
                assert.equal(await page.$eval('#disabled', el => getComputedStyle(el).transform), 'none');
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
            }
        }
    } finally { await browser.close(); }
});
