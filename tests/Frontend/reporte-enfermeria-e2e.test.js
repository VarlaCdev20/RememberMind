import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, writeFileSync, rmSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { createServer } from 'node:net';
import puppeteer from 'puppeteer';

const root = resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/i, '$1'));
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));

test('reporte de Enfermería: filtros reales, vista previa, PDF y cinco tamaños', { timeout: 120000, skip: !executablePath }, async t => {
    const db = join(tmpdir(), `remembermind-reporte-${randomBytes(8).toString('hex')}_testing.sqlite`);
    writeFileSync(db, '', { flag: 'wx' });
    t.after(() => { for (const f of [db, `${db}-wal`, `${db}-shm`]) rmSync(f, { force: true }); });
    const port = await new Promise((resolve, reject) => {
        const socket = createServer(); socket.on('error', reject);
        socket.listen(0, '127.0.0.1', () => { const p = socket.address().port; socket.close(() => resolve(p)); });
    });
    const base = `http://127.0.0.1:${port}`, token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_REPORTE_DB: db, RM_REPORTE_TOKEN: token };
    const router = join(root, 'tests/Support/ServidorReporteEnfermeriaSintetico.php');
    const prep = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 30000 });
    assert.equal(prep.status, 0, prep.stderr + prep.stdout); assert.match(prep.stdout, /PREPARADO/);
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', join(root, 'public'), router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    t.after(() => server.kill());
    let ready = false, lastResponse = '';
    for (let i = 0; i < 40; i++) {
        try { const r = await fetch(`${base}/admin/enfermeria/reportes`, { headers: { 'X-RM-REPORTE-QA': token } }); if (r.ok) { ready = true; break; } lastResponse = `${r.status}: ${(await r.text()).replace(/<[^>]*>/g, '').slice(0, 1600)}`; } catch (e) { if (server.exitCode !== null) throw e; }
        await new Promise(r => setTimeout(r, 100));
    }
    assert.ok(ready, `No inició el reporte sintético: ${lastResponse}`);
    const browser = await puppeteer.launch({ headless: true, executablePath }); t.after(() => browser.close());
    const page = await browser.newPage(), errors = []; page.on('pageerror', e => errors.push(e.message));
    await page.setRequestInterception(true);
    page.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-REPORTE-QA': token } }) : r.abort());
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    const output = join(root, 'output/reporte-enfermeria-20261027'); mkdirSync(output, { recursive: true });
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({ width, height: 1000 });
        const response = await page.goto(`${base}/admin/enfermeria/reportes`, { waitUntil: 'networkidle0' }); assert.equal(response.status(), 200);
        await page.waitForFunction(() => window.Livewire && window.Alpine);
        assert.ok(await page.$('a[href$="/admin/enfermeria/reportes"]'), 'Acceso al reporte desde navegación de Enfermería');
        for (const theme of ['light', 'dark']) {
            await page.evaluate(theme => window.RememberMindTheme.apply(theme), theme);
            await new Promise(r => setTimeout(r, 300));
            const data = await page.evaluate(() => ({ width: document.documentElement.scrollWidth, viewport: innerWidth,
                text: document.querySelector('main').innerText, labeled: [...document.querySelectorAll('main input,main select')].every(el => document.querySelector(`label[for="${el.id}"]`)) }));
            assert.ok(data.width <= data.viewport + 1, `Desbordamiento ${width}/${theme}: ${data.width}`);
            assert.ok(data.labeled); assert.doesNotMatch(data.text, /\b(?:undefined|NaN|Infinity)\b|\[object Object\]/);
            const controls = await page.$$eval('.rm-nursing-report select,.rm-nursing-report input', els => els.map(el => ({ height: el.getBoundingClientRect().height, line: parseFloat(getComputedStyle(el).lineHeight) })));
            assert.ok(controls.every(c => c.height >= 44 && c.line <= c.height), 'Campos completos y objetivos táctiles');
            await page.screenshot({ path: join(output, `reporte-${width}-${theme}.png`), fullPage: true });
        }
    }
    await page.$eval('#reporte-desde', el => el.focus());
    assert.equal(await page.evaluate(() => document.activeElement.id), 'reporte-desde');
    await page.evaluate(() => {
        for (const id of ['reporte-desde', 'reporte-hasta']) {
            const el = document.getElementById(id); el.value = '2026-10-20';
            el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    await page.click('form[aria-label="Filtros del reporte"] button[type="submit"]');
    await page.waitForFunction(() => document.querySelector('a[href*="vista-previa"]').href.includes('hasta=2026-10-20'));
    assert.match(await page.$eval('main', el => el.innerText), /No hay registros para los filtros aplicados/);
    await page.screenshot({ path: join(output, 'sin-registros-390.png'), fullPage: true });
    await page.evaluate(() => {
        const el = document.getElementById('reporte-hasta'); el.value = '2026-10-27';
        el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true }));
    });
    await page.select('#reporte-residente', 'RES_QA_RPT'); await page.select('#reporte-tipo', 'CONTROLES');
    await page.click('form[aria-label="Filtros del reporte"] button[type="submit"]');
    await page.waitForFunction(() => document.querySelector('a[href*="vista-previa"]').href.includes('tipo=CONTROLES'));
    const preview = await page.$eval('a[href*="vista-previa"]', el => el.href);
    const pdfUrl = await page.$eval('a[href*="/reportes/pdf"]', el => el.href);
    const pdf = await fetch(pdfUrl, { headers: { 'X-RM-REPORTE-QA': token } });
    assert.equal(pdf.status, 200); const bytes = Buffer.from(await pdf.arrayBuffer()); assert.equal(bytes.subarray(0, 5).toString(), '%PDF-');
    writeFileSync(join(output, 'resumen-sintetico.pdf'), bytes);
    await page.goto(preview, { waitUntil: 'networkidle0' });
    assert.equal(await page.$$eval('.scroll tbody tr', rows => rows.length), 23);
    assert.match(await page.$eval('body', el => el.innerText), /Alicia Sintética/);
    await page.screenshot({ path: join(output, 'vista-previa-390.png'), fullPage: true });
    assert.deepEqual(errors, []);
});
