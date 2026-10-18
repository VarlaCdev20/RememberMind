// QA del Blade real exportado desde PHPUnit; sin sesión ni conexión a la instalación operativa.
import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import puppeteer from 'puppeteer';

const root = process.cwd();
const fixtures = process.env.RM_INFORME_COGNITIVO_QA_DIR || join(process.env.TEMP, 'remembermind-informe-cognitivo-qa');
const evidence = join(fixtures, 'evidence');
mkdirSync(evidence, { recursive: true });
const manifest = JSON.parse(readFileSync(join(root, 'public/build/manifest.json'), 'utf8'));
const css = manifest['resources/frontend/styles/app.css'].file;
const states = ['DIFICULTAD_EVIDENCIADA', 'SIN_DIFICULTAD_EVIDENCIADA', 'HALLAZGOS_MIXTOS', 'SIN_EVALUACION', 'INSUFICIENTE', 'PRELIMINAR', 'BLOQUEADA', 'INTEGRIDAD', 'ENFERMERIA', 'TECNICA', 'ERROR'];
const server = createServer((req, res) => {
    const state = req.url.slice(1);
    if (states.includes(state)) {
        const html = readFileSync(join(fixtures, `${state}.html`), 'utf8');
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        res.end(`<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"></head><body class="rm-shell"><main class="p-4" aria-label="Análisis sintético de QA">${html}</main></body></html>`);
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
    for (const theme of ['light', 'dark']) for (const width of [1440, 1280, 1024, 768, 390]) for (const state of states) {
        await page.setViewport({ width, height: 1000 });
        await page.goto(`http://127.0.0.1:${server.address().port}/${state}`);
        await page.evaluate(theme => {
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.dataset.theme = theme;
            document.querySelectorAll('[wire\\:loading]').forEach(e => e.hidden = true);
        }, theme);
        await page.evaluate(() => document.fonts.ready);
        await page.evaluate(async () => {
            await new Promise(requestAnimationFrame);
            await Promise.all(document.getAnimations().filter(a => a.effect?.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {})));
        });
        const initial = await page.evaluate(() => ({
            overflow: document.documentElement.scrollWidth > innerWidth,
            text: document.body.innerText,
            areas: document.querySelectorAll('.rm-expert-area-grid button').length,
            controls: [...document.querySelectorAll('a,button,summary')].filter(e => e.getBoundingClientRect().height).map(e => ({ height: e.getBoundingClientRect().height, width: e.getBoundingClientRect().width, text: e.textContent.trim().slice(0, 50) })),
        }));
        assert.equal(initial.overflow, false, `${state}/${width}/${theme}: desbordamiento`);
        assert.equal(initial.areas, state === 'ERROR' ? 0 : 5);
        assert.ok(!/\b(null|NaN|undefined|Infinity)\b|\[object Object\]/.test(initial.text));
        assert.ok(initial.controls.every(c => c.height >= 44 && c.width >= 44), JSON.stringify(initial.controls));
        const ordered = await page.evaluate(() => ['encontrado-titulo', 'areas-cognitivas-titulo', 'significado-titulo', 'por-que-titulo', 'historial-cognitivo'].filter(id => document.getElementById(id)).map(id => document.getElementById(id).getBoundingClientRect().top));
        assert.deepEqual(ordered, [...ordered].sort((a, b) => a - b));
        const summary = await page.$('details summary');
        if (summary) {
            await summary.focus(); await page.keyboard.press('Enter');
            await page.waitForFunction(() => document.querySelector('details').open);
            await page.keyboard.press('Enter');
            await page.waitForFunction(() => !document.querySelector('details').open);
        }
        // Comprueba también el reflow de tablas, códigos largos y fundamentos desplegados.
        await page.$$eval('details', elements => elements.forEach(e => e.open = true));
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `${state}/${width}/${theme}: detalle desbordado`);
        const contrast = await page.evaluate(() => {
            const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
            const ctx = canvas.getContext('2d');
            const rgb = color => { ctx.clearRect(0, 0, 1, 1); ctx.fillStyle = color; ctx.fillRect(0, 0, 1, 1); return [...ctx.getImageData(0, 0, 1, 1).data]; };
            const lum = c => c.slice(0, 3).map(v => { v /= 255; return v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4; }).reduce((s, v, i) => s + v * [.2126, .7152, .0722][i], 0);
            return [...document.querySelectorAll('.rm-expert-results h1,.rm-expert-results h2,.rm-expert-results p,.rm-expert-results span,.rm-expert-results dt,.rm-expert-results dd,.rm-expert-results a,.rm-expert-results summary,.rm-expert-results li')].filter(e => e.getBoundingClientRect().height && e.checkVisibility()).map(e => {
                let bg, node = e;
                while (node) { const c = rgb(getComputedStyle(node).backgroundColor); if (c[3] === 255) { bg = c; break; } node = node.parentElement; }
                if (!bg) return { ratio: null, text: e.textContent.trim().slice(0, 50) };
                const a = lum(rgb(getComputedStyle(e).color)), b = lum(bg);
                return { ratio: (Math.max(a, b) + .05) / (Math.min(a, b) + .05), fg: getComputedStyle(e).color, bg, text: e.textContent.trim().slice(0, 50) };
            });
        });
        if (!contrast.every(c => c.ratio !== null && c.ratio >= 4.5)) await page.screenshot({ path: join(evidence, 'contraste-revisar.png'), fullPage: true });
        assert.ok(contrast.every(c => c.ratio !== null && c.ratio >= 4.5), JSON.stringify({ state, width, theme, contrast: contrast.filter(c => c.ratio === null || c.ratio < 4.5) }));
        if (width === 1440 || width === 390 || state === 'DIFICULTAD_EVIDENCIADA') await page.screenshot({ path: join(evidence, `${state}-${width}-${theme}.png`), fullPage: true });
        results.push({ state, width, theme, minimumContrast: Math.min(...contrast.map(c => c.ratio)), result: 'PASS' });
    }
    assert.deepEqual(errors, []);
    writeFileSync(join(evidence, 'resultados.json'), JSON.stringify(results, null, 2));
    console.log(JSON.stringify({ combinations: results.length, minimumContrast: Math.min(...results.map(r => r.minimumContrast)), evidence, result: 'PASS' }));
} finally {
    await browser?.close();
    await new Promise(resolve => server.close(resolve));
}
