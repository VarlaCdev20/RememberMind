// Revisa el Blade real exportado por PHPUnit con datos sintéticos. Sin sesión o BDD operativa.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import puppeteer from 'puppeteer';

const root = process.cwd();
const fixtures = process.env.RM_RESUMEN_COGNITIVO_QA_DIR || join(process.env.TEMP, 'remembermind-resumen-cognitivo-qa');
const evidence = join(fixtures, 'evidence');
mkdirSync(evidence, { recursive: true });
const manifest = JSON.parse(readFileSync(join(root, 'public/build/manifest.json'), 'utf8'));
const css = manifest['resources/frontend/styles/app.css'].file;
const states = ['vacio', 'preliminar', 'insuficiente', 'documentada', 'contradiccion', 'sustentado', 'restringido'];
const server = createServer((req, res) => {
    const state = req.url.slice(1);
    if (states.includes(state)) {
        const html = readFileSync(join(fixtures, `${state}.html`), 'utf8');
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        res.end(`<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"></head><body class="rm-shell"><main class="p-4" aria-label="Ficha sintética de QA">${html}</main></body></html>`);
    } else if (req.url.startsWith('/build/') && !req.url.includes('..')) {
        const file = resolve(root, 'public' + req.url);
        if (!existsSync(file)) { res.writeHead(404).end(); return; }
        res.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : 'font/woff2');
        res.end(readFileSync(file));
    } else { res.writeHead(404).end(); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
let browser;
const results = [];
try {
    browser = await puppeteer.launch({ headless: true, executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    for (const theme of ['light', 'dark']) {
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            for (const state of states) {
                await page.goto(`http://127.0.0.1:${server.address().port}/${state}`);
                await page.evaluate(theme => {
                    document.documentElement.classList.toggle('dark', theme === 'dark');
                    document.documentElement.dataset.theme = theme;
                }, theme);
                await page.evaluate(() => document.fonts.ready);
                const initial = await page.evaluate(() => ({
                    overflow: document.documentElement.scrollWidth > innerWidth,
                    text: document.body.innerText,
                    controls: [...document.querySelectorAll('a,summary')].map(e => ({ height: e.getBoundingClientRect().height, width: e.getBoundingClientRect().width })),
                }));
                assert.equal(initial.overflow, false, `${state}/${width}/${theme}: desbordamiento`);
                assert.ok(!/\b(null|NaN|undefined|Infinity)\b|\[object Object\]/.test(initial.text));
                assert.ok(!/COD_|EV-CM|COG-MEM|VER_TEST/.test(initial.text));
                assert.ok(initial.controls.every(c => c.height >= 44 && c.width >= 44));
                const original = await page.$('a[href^="#control-cognitivo-"]');
                if (original) {
                    const target = await original.evaluate(e => e.hash.slice(1));
                    await original.focus();
                    await page.keyboard.press('Enter');
                    await page.waitForFunction(id => document.getElementById(id).open, { timeout: 2000 }, target);
                    assert.equal(await page.evaluate(() => document.activeElement.tagName), 'SUMMARY');
                    await page.$eval(`#${target} summary`, e => e.focus());
                    await page.keyboard.press('Enter');
                    await page.waitForFunction(id => !document.getElementById(id).open, { timeout: 2000 }, target);
                    await page.keyboard.press('Enter');
                    await page.waitForFunction(id => document.getElementById(id).open, { timeout: 2000 }, target);
                }
                const contrast = await page.evaluate(() => {
                    const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
                    const ctx = canvas.getContext('2d');
                    const rgb = color => { ctx.clearRect(0, 0, 1, 1); ctx.fillStyle = color; ctx.fillRect(0, 0, 1, 1); return [...ctx.getImageData(0, 0, 1, 1).data]; };
                    const luminance = c => c.slice(0, 3).map(v => { v /= 255; return v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4; }).reduce((s, v, i) => s + v * [.2126, .7152, .0722][i], 0);
                    return [...document.querySelectorAll('section h2,section h3,section p,section dt,section dd,section a,section summary')].filter(e => e.getBoundingClientRect().height).map(e => {
                        let bg, node = e;
                        while (node) { const c = rgb(getComputedStyle(node).backgroundColor); if (c[3] === 255) { bg = c; break; } node = node.parentElement; }
                        if (!bg) return { ratio: null, text: e.textContent.trim().slice(0, 50) };
                        const fg = rgb(getComputedStyle(e).color); const a = luminance(fg), b = luminance(bg);
                        return { ratio: (Math.max(a, b) + .05) / (Math.min(a, b) + .05), text: e.textContent.trim().slice(0, 50) };
                    });
                });
                assert.ok(contrast.every(c => c.ratio !== null && c.ratio >= 4.5), JSON.stringify({ state, width, theme, contrast: contrast.filter(c => c.ratio === null || c.ratio < 4.5) }));
                assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
                if (width === 1440 || width === 390 || state === 'preliminar') await page.screenshot({ path: join(evidence, `${state}-${width}-${theme}.png`), fullPage: true });
                results.push({ state, width, theme, controls: initial.controls.length, minimumContrast: Math.min(...contrast.map(c => c.ratio)), result: 'PASS' });
            }
        }
    }
    assert.deepEqual(errors, []);
    writeFileSync(join(evidence, 'resultados.json'), JSON.stringify(results, null, 2));
    console.log(JSON.stringify({ combinations: results.length, minimumContrast: Math.min(...results.map(r => r.minimumContrast)), evidence, result: 'PASS' }));
} finally {
    await browser?.close();
    await new Promise(resolve => server.close(resolve));
}
