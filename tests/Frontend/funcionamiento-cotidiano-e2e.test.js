import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { createServer } from 'node:net';
import { randomBytes } from 'node:crypto';
import { mkdir, writeFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer';

test('funcionamiento cotidiano: captura y rectificación responsive', { skip: process.env.RM_FUNCIONAMIENTO_E2E !== '1', timeout: 240000 }, async t => {
    const root = path.resolve(fileURLToPath(new URL('../../', import.meta.url)));
    const db = path.join(tmpdir(), `remembermind-explorador-${randomBytes(8).toString('hex')}_testing.sqlite`);
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    const port = await new Promise(resolve => {
        const socket = createServer().listen(0, '127.0.0.1', () => {
            const value = socket.address().port;
            socket.close(() => resolve(value));
        });
    });
    const base = `http://127.0.0.1:${port}`;
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_EXPLORADOR_DB: db, RM_EXPLORADOR_TOKEN: randomBytes(24).toString('hex') };
    const router = 'tests/Support/ServidorExploradorSintetico.php';
    const args = [];
    const run = arguments_ => new Promise((resolve, reject) => {
        const child = spawn('php', [...args, router, ...arguments_], { cwd: root, env, windowsHide: true });
        let output = '';
        child.stdout.on('data', value => output += value);
        child.stderr.on('data', value => output += value);
        child.on('error', reject);
        child.on('close', code => code ? reject(new Error(output)) : resolve(output));
    });
    assert.match(await run(['--preparar']), /PREPARADO/);
    const server = spawn('php', [...args, '-S', `127.0.0.1:${port}`, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    t.after(() => server.kill());
    const evidence = path.join(root, 'output', 'funcionamiento-cotidiano-20261028');
    await mkdir(evidence, { recursive: true });
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(value => value && existsSync(value));
    const browser = await puppeteer.launch({ headless: true, executablePath, args: ['--no-sandbox'] });
    t.after(() => browser.close());
    const page = await browser.newPage();
    await page.setExtraHTTPHeaders({ 'X-RM-Explorador': env.RM_EXPLORADOR_TOKEN });
    await page.setRequestInterception(true);
    page.on('request', request => request.url().startsWith(base) || request.url().startsWith('data:') ? request.continue() : request.abort());
    await page.evaluateOnNewDocument(() => localStorage.setItem('remembermind-theme', 'light'));
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const settled = () => page.waitForNetworkIdle({ idleTime: 250, timeout: 20000 });
    const clickText = async (selector, text) => {
        const handle = await page.evaluateHandle((selector_, text_) => [...document.querySelectorAll(selector_)].find(element => element.getClientRects().length && element.textContent.includes(text_) && !element.disabled), selector, text);
        assert.ok(handle.asElement(), `Control visible: ${text}`);
        await handle.asElement().click();
        await settled();
    };
    const resize = async width => {
        await page.setViewport({ width, height: 1000 });
        await page.waitForFunction(value => document.body._x_dataStack?.[0]?.sidebarRange === (value >= 1280 ? 'desktop' : value >= 768 ? 'laptop' : 'drawer'), {}, width);
        await page.waitForFunction(value => {
            const rect = document.querySelector('.rm-sidebar').getBoundingClientRect();
            return value >= 768 ? Math.abs(rect.left) < 1 : rect.right <= 1;
        }, {}, width);
    };
    await page.setViewport({ width: 1440, height: 1000 });
    const response = await page.goto(`${base}/admin/residentes/RES_E2E_GUIA/funcionamiento-cotidiano`, { waitUntil: 'networkidle0' });
    assert.equal(response.status(), 200);
    await clickText('button', 'Registrar observación');
    await page.focus('#cotidiano-actividad');
    await page.keyboard.press('ArrowDown');
    await page.waitForFunction(() => document.querySelector('#cotidiano-actividad').getAttribute('aria-expanded') === 'true');
    await page.keyboard.press('Escape');
    await page.waitForFunction(() => document.querySelector('#cotidiano-actividad').getAttribute('aria-expanded') === 'false');

    const values = {
        actividad: 'Rutinas cotidianas', fuente: 'Información del equipo de cuidados',
        oportunidad: 'Sin oportunidad de realizarla', desempeno: 'No se pudo evaluar',
        tipo_ayuda: 'No aplica', causa_ayuda: 'Organización de la residencia', nivel_previo: 'No se conoce',
    };
    for (const [field, value] of Object.entries(values)) {
        await page.click(`#cotidiano-${field}`);
        await clickText('[role=option]', value);
    }
    await clickText('button', 'Cerrar captura');
    assert.match(await page.$eval('body', el => el.innerText), /Captura sin guardar/);
    await clickText('button', 'Volver a la captura');
    await page.type('#cotidiano-notas', 'Observación sintética: actividad no disponible en el contexto de esta sesión.');
    const matrix = [];
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await resize(width);
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
        assert.ok(overflow <= 1, `Captura ${width}: ${overflow}`);
        matrix.push({ width, overflow });
        assert.ok(!await page.evaluate(() => /\b(null|undefined|NaN|Infinity)\b|\[object Object\]/.test(document.body.innerText)), 'Sin literales técnicos visibles');
        await page.$eval('#cotidiano-actividad', el => el.scrollIntoView({ block: 'start' }));
        await page.screenshot({ path: path.join(evidence, `captura-${width}.png`), fullPage: true });
    }
    await clickText('button', 'Revisar observación');
    await page.waitForSelector('.rm-captura__revision');
    await page.waitForFunction(() => { const r = document.querySelector('.rm-captura__revision').getBoundingClientRect(); return r.top >= 95 && r.top < innerHeight - 100; });
    assert.match(await page.$eval('.rm-captura__revision', el => el.innerText), /No se pudo evaluar/);
    assert.equal(await page.$$eval('.rm-captura__revision input', items => items.length), 0);
    assert.equal(JSON.parse(await run(['--estado'])).funcionamiento.length, 0);
    await page.screenshot({ path: path.join(evidence, 'revision-390.png'), fullPage: true });
    await clickText('button', 'Volver a editar');
    assert.match(await page.$eval('#cotidiano-desempeno', el => el.innerText), /No se pudo evaluar/);
    await clickText('button', 'Revisar observación');
    await clickText('button', 'Confirmar y registrar');
    await page.waitForFunction(() => document.body.innerText.includes('Observación de funcionamiento cotidiano registrada.'));
    await clickText('button', 'Rectificar observación');
    assert.equal(await page.$eval('#cotidiano-notas', el => el.value), '');
    await page.type('#cotidiano-notas', 'Rectificación sintética: se precisa la procedencia del relato, conservando el original.');
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await clickText('button', 'Revisar observación');
    await clickText('button', 'Confirmar y registrar');
    await page.waitForFunction(() => document.body.innerText.includes('Rectificación registrada.'));
    assert.match(await page.$eval('body', el => el.innerText), /Original rectificado/);
    const state = JSON.parse(await run(['--estado']));
    assert.equal(state.funcionamiento.length, 2);
    assert.equal(state.funcionamiento[1].cod_control_anterior, state.funcionamiento[0].cod_control_iadl);
    assert.equal(state.funcionamiento[0].desempeno, 'NO_EVALUABLE');
    assert.equal(state.evaluaciones, 0);
    assert.deepEqual(errors, []);
    await page.evaluate(() => scrollTo(0, 0));
    await page.screenshot({ path: path.join(evidence, 'historial-390.png'), fullPage: true });
    await writeFile(path.join(evidence, 'verificacion.json'), JSON.stringify({ matrix, errors, state, synthetic: true, backend: 'SQLite desechable; flujos Livewire reales' }, null, 2));
});
