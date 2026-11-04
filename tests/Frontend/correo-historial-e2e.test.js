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
const router = join(root, 'tests/Support/ServidorCorreoSintetico.php');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));

test('historial real: filtros, modal, teclado y reflow en cinco anchos y dos temas', { timeout: 90000, skip: !executablePath }, async t => {
    const db = join(tmpdir(), `remembermind-correo-${randomBytes(8).toString('hex')}_testing.sqlite`);
    writeFileSync(db, '');
    const token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', RM_CORREO_QA_DB: db, RM_CORREO_QA_TOKEN: token };
    t.after(() => { for (const path of [db, `${db}-wal`, `${db}-shm`]) rmSync(path, { force: true }); });
    const prepared = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 30000 });
    assert.equal(prepared.status, 0, prepared.stderr || prepared.stdout);
    assert.match(prepared.stdout, /PREPARADO/);
    const port = await new Promise((resolve, reject) => {
        const socket = createServer(); socket.on('error', reject);
        socket.listen(0, '127.0.0.1', () => { const value = socket.address().port; socket.close(() => resolve(value)); });
    });
    env.APP_URL = `http://127.0.0.1:${port}`;
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', join(root, 'public'), router], { cwd: root, env, windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
    let errors = ''; server.stderr.on('data', d => { errors += d.toString(); });
    t.after(() => server.kill());
    const url = `http://127.0.0.1:${port}/admin/usuarios/correos/historial`;
    let ready = false;
    for (let i = 0; i < 40; i++) {
        try { const response = await fetch(url, { headers: { 'X-RM-CORREO-QA': token } }); if (response.ok) { ready = true; break; } } catch (e) { if (server.exitCode !== null) throw new Error(errors, { cause: e }); }
        await new Promise(r => setTimeout(r, 150));
    }
    assert.ok(ready, errors);
    const browser = await puppeteer.launch({ headless: true, executablePath });
    t.after(() => browser.close());
    const page = await browser.newPage();
    await page.setRequestInterception(true);
    page.on('request', req => {
        if (req.url().startsWith(`http://127.0.0.1:${port}/`)) req.continue({ headers: { ...req.headers(), 'X-RM-CORREO-QA': token } });
        else req.abort(); // Ningún dato ni asset sale del servidor sintético.
    });
    const jsErrors = []; page.on('pageerror', error => jsErrors.push(error.message));
    const output = join(root, 'output/correo-historial-20261025'); mkdirSync(output, { recursive: true });
    for (const theme of ['light', 'dark']) {
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 900 });
            await page.goto(url, { waitUntil: 'networkidle0' });
            await page.evaluate(theme => {
                localStorage.setItem('remembermind-theme', theme);
                document.documentElement.dataset.theme = theme;
                document.documentElement.classList.toggle('dark', theme === 'dark');
            }, theme);
            try {
                await page.waitForFunction(() => window.Livewire && document.querySelector('[data-correo-historial]'), { timeout: 10000 });
            } catch (e) {
                throw new Error(JSON.stringify({ errors: jsErrors, scripts: await page.$$eval('script[src]', els => els.map(e => e.src)), title: await page.title() }), { cause: e });
            }
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, `${theme} ${width}: overflow`);
            const visibleText = await page.$eval('[data-correo-historial]', el => el.innerText);
            assert.doesNotMatch(visibleText, /\b(?:undefined|NaN|Infinity|null)\b|\[object Object\]|COD_USU_/);
            const labelCount = await page.$$eval('form[aria-label="Filtrar correos"] input, form[aria-label="Filtrar correos"] select', els => els.filter(el => el.labels.length).length);
            assert.equal(labelCount, 5);
            assert.deepEqual(await page.$$eval('#correo-tipo option', els => els.map(e => e.value)), ['', 'ficha', 'paquete']);
            await page.screenshot({ path: join(output, `${theme}-${width}-inicio.png`), fullPage: true });
            const target = width >= 1024 ? 'table button' : 'article button';
            const open = await page.$(`[data-correo-historial] ${target}`);
            await open.focus(); await page.keyboard.press('Enter');
            await page.waitForFunction(() => document.querySelector('#correo-detalle')?.classList.contains('is-open'));
            await page.waitForFunction(() => document.querySelector('#correo-detalle')?.innerText.includes('Recorrido del envío'));
            const modal = await page.$eval('#correo-detalle [role="dialog"]', el => {
                const b = el.getBoundingClientRect();
                return { left: b.left, right: b.right, viewport: innerWidth, label: el.getAttribute('aria-labelledby'), described: el.getAttribute('aria-describedby') };
            });
            assert.ok(modal.left >= 0 && modal.right <= modal.viewport + 1, `${theme} ${width}: modal fuera de viewport`);
            assert.ok(modal.label && modal.described);
            await page.waitForFunction(() => document.querySelector('#correo-detalle [role="dialog"]').contains(document.activeElement));
            await page.keyboard.press('Tab');
            assert.equal(await page.evaluate(() => document.querySelector('#correo-detalle').contains(document.activeElement)), true);
            await page.screenshot({ path: join(output, `${theme}-${width}-detalle.png`), fullPage: true });
            await page.keyboard.press('Escape');
            await page.waitForFunction(() => !document.querySelector('#correo-detalle')?.classList.contains('is-open'));
            await page.waitForFunction(() => document.activeElement?.textContent.includes('Ver detalle'));
            await page.screenshot({ path: join(output, `${theme}-${width}.png`), fullPage: true });
        }
    }
    await page.select('#correo-estado', 'ACEPTADO');
    await page.click('form[aria-label="Filtrar correos"] button[type="submit"]');
    await page.waitForFunction(() => document.querySelector('[data-correo-historial] h2')?.textContent.includes('(1)'));
    await page.type('#correo-busqueda', 'No existe');
    await page.click('form[aria-label="Filtrar correos"] button[type="submit"]');
    await page.waitForFunction(() => document.querySelector('[data-correo-historial]').innerText.includes('No hay solicitudes para mostrar'));
    assert.deepEqual(jsErrors, []);
});
