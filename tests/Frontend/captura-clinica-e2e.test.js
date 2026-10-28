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

test('captura propia: revisión, entrevista y cinco tamaños', { skip: process.env.RM_CAPTURA_E2E !== '1', timeout: 240000 }, async t => {
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
    const evidence = path.join(root, 'output', 'captura-clinica-20261028');
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
    const clickText = async (selector, text, exact = false) => {
        const handle = await page.evaluateHandle((selector_, text_, exact_) => [...document.querySelectorAll(selector_)].find(element => element.getClientRects().length && (exact_ ? element.textContent.trim() === text_ : element.textContent.includes(text_)) && !element.disabled), selector, text, exact);
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
    const response = await page.goto(`${base}/admin/psicologia/evaluacion/cognitiva`, { waitUntil: 'networkidle0' });
    assert.equal(response.status(), 200);
    await clickText('.rm-instrumento__acciones button', 'Registrar aplicación');
    await page.click('.rm-modal-shell.is-open button[id^="captura-residente-"]');
    await clickText('.rm-choice__popover [role="option"]', 'Residente sintético');
    await page.waitForSelector('.rm-modal-shell.is-open button[id^="captura-valor-"]');
    await page.click('.rm-modal-shell.is-open button[id^="captura-valor-"]');
    await clickText('.rm-choice__popover [role="option"]', 'No');
    await page.waitForFunction(() => document.querySelector('.rm-captura__pie-ayuda')?.innerText.includes('1 de 1'));
    await page.focus('.rm-captura__modal .rm-captura__guia summary');
    await page.keyboard.press('Enter');
    assert.match(await page.$eval('.rm-captura__modal .rm-captura__guia', element => element.innerText), /Escolaridad sintética de referencia/);
    assert.match(await page.$eval('.rm-captura__modal .rm-captura__guia', element => element.innerText), /no indica los años cursados/);
    const matrix = [];
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await resize(width);
        const dimensions = await page.$eval('.rm-captura__modal .rm-modal-panel', element => {
            const rect = element.getBoundingClientRect();
            const body = element.querySelector('.rm-modal-body');
            return { left: rect.left, right: rect.right, overflow: body.scrollWidth - body.clientWidth };
        });
        assert.ok(dimensions.left >= -1 && dimensions.right <= width + 1 && dimensions.overflow <= 1, JSON.stringify({ width, ...dimensions }));
        matrix.push({ surface: 'captura', width, ...dimensions });
        await page.screenshot({ path: path.join(evidence, `captura-${width}.png`), fullPage: true });
        await page.$eval('.rm-captura__modal .rm-captura__guia', element => element.scrollIntoView({ block: 'start' }));
        await page.screenshot({ path: path.join(evidence, `guia-captura-${width}.png`), fullPage: true });
    }
    assert.ok(await page.$eval('textarea[id^=captura-observaciones-]', element => element.getBoundingClientRect().height >= 80), 'Observaciones con espacio multilínea');
    await clickText('.rm-captura__acciones button', 'Revisar captura');
    await page.waitForSelector('.rm-captura__revision');
    assert.match(await page.$eval('.rm-captura__revision', element => element.innerText), /1 de 1/);
    assert.match(await page.$eval('.rm-captura__respuestas-revision', element => element.innerText), /No/);
    assert.equal(await page.$$eval('.rm-captura__revision input', elements => elements.length), 0);
    await page.screenshot({ path: path.join(evidence, 'revision-390.png'), fullPage: true });
    for (let index = 0; index < 8; index++) {
        await page.keyboard.press('Tab');
        assert.ok(await page.evaluate(() => document.activeElement.closest('[role="dialog"]')), 'Foco dentro del diálogo de revisión');
    }
    const contrast = await page.$eval('.rm-captura__respuestas-revision dt', element => {
        const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, 1, 1);
        const ancestry = []; for (let current = element; current; current = current.parentElement) ancestry.unshift(current);
        for (const current of ancestry) { ctx.fillStyle = getComputedStyle(current).backgroundColor; ctx.fillRect(0, 0, 1, 1); }
        const background = [...ctx.getImageData(0, 0, 1, 1).data];
        ctx.fillStyle = getComputedStyle(element).color; ctx.fillRect(0, 0, 1, 1);
        const foreground = [...ctx.getImageData(0, 0, 1, 1).data];
        const luminance = rgb => rgb.slice(0, 3).map(channel => { const value = channel / 255; return value <= .04045 ? value / 12.92 : ((value + .055) / 1.055) ** 2.4; }).reduce((sum, value, index) => sum + value * [.2126, .7152, .0722][index], 0);
        const values = [luminance(background), luminance(foreground)];
        return (Math.max(...values) + .05) / (Math.min(...values) + .05);
    });
    assert.ok(contrast >= 4.5, `Contraste de etiqueta de revisión: ${contrast}`);
    await clickText('.rm-captura__acciones button', 'Volver a editar');
    assert.match(await page.$eval('.rm-modal-shell.is-open button[id^="captura-valor-"]', element => element.innerText), /No/);
    await clickText('.rm-captura__acciones button', 'Revisar captura');
    await clickText('.rm-captura__acciones button', 'Confirmar y registrar');
    await page.waitForFunction(() => !document.querySelector('.rm-captura__modal'));
    const state = JSON.parse(await run(['--estado']));
    assert.equal(state.aplicaciones, 1);
    assert.equal(state.respuestas, 1);
    assert.ok(state.valor_logico === false || state.valor_logico === 0);

    const session = await page.goto(`${base}/admin/psicologia/residente/RES_E2E_GUIA/ficha?tab=seguimiento`, { waitUntil: 'networkidle0' });
    assert.equal(session.status(), 200);
    await clickText('button', 'Registrar nueva sesión');
    await page.waitForSelector('#sesion-motivo');
    await page.click('#sesion-tipo');
    await clickText('[role=option]', 'Entrevista con informante familiar');
    await page.waitForFunction(() => document.querySelector('#sesion-tipo').innerText.includes('Entrevista con informante familiar'));
    await page.type('#sesion-motivo', 'Entrevista de seguimiento sintética');
    await page.type('#sesion-escolaridad', '0');
    await page.click('#sesion-puede_leer');
    await clickText('.rm-choice__popover [role=option]', 'No', true);
    await page.waitForFunction(() => document.querySelector('#sesion-puede_leer').innerText.trim() === 'No');
    await page.type('#sesion-notas', 'Relato del informante sintético, diferenciado de la observación directa.');
    await page.focus('#sesion-guia-contexto summary');
    await page.keyboard.press('Enter');
    assert.match(await page.$eval('#sesion-guia-contexto', element => element.innerText), /Escolaridad sintética de referencia/);
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await resize(width);
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
        assert.ok(overflow <= 1, `Entrevista ${width}: ${overflow}`);
        matrix.push({ surface: 'entrevista', width, overflow });
        await page.$eval('#sesion-motivo', element => element.scrollIntoView({ block: 'center' }));
        await page.screenshot({ path: path.join(evidence, `entrevista-${width}.png`), fullPage: false });
        await page.$eval('#sesion-escolaridad', element => element.scrollIntoView({ block: 'start' }));
        await page.screenshot({ path: path.join(evidence, `contexto-sesion-${width}.png`), fullPage: false });
        await page.$eval('#sesion-guia-contexto', element => element.scrollIntoView({ block: 'start' }));
        await page.screenshot({ path: path.join(evidence, `guia-entrevista-${width}.png`), fullPage: false });
    }
    await page.$eval('#sesion-guia-contexto', element => element.open = true);
    assert.match(await page.$eval('#sesion-guia-contexto', element => element.innerText), /Cambio y evolución/);
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await clickText('button', 'Guardar sesión');
    await page.waitForFunction(() => document.body.innerText.includes('Sesión de atención psicológica registrada'));
    assert.match(await page.$eval('body', element => element.innerText), /Relato del informante sintético/);
    const guardado = JSON.parse(await run(['--estado']));
    assert.deepEqual(guardado.contexto_sesion, { escolaridad_anios: 0, puede_leer: false, puede_escribir: null });
    assert.deepEqual(errors, []);
    await writeFile(path.join(evidence, 'verificacion.json'), JSON.stringify({ matrix, errors, state, contrast, synthetic: true, backend: 'SQLite desechable; flujos Livewire reales' }, null, 2));
});
