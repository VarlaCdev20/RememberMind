import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { createServer } from 'node:net';
import { randomBytes } from 'node:crypto';
import { mkdtemp, writeFile, mkdir } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

// Opt-in: inicia exclusivamente un servidor y SQLite sintéticos; nunca usa la instalación clínica.
test('explorador real: cuatro áreas, guía, controles institucionales y guardado', { skip: process.env.RM_EXPLORADOR_E2E !== '1', timeout: 480000 }, async t => {
    const root = path.resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'));
    const nonce = randomBytes(8).toString('hex');
    const db = path.join(tmpdir(), `remembermind-explorador-${nonce}_testing.sqlite`);
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    const port = await new Promise(resolve => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => resolve(p)); }); });
    const base = `http://127.0.0.1:${port}`;
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_EXPLORADOR_DB: db, RM_EXPLORADOR_TOKEN: randomBytes(24).toString('hex') };
    const php = process.env.PHP_BINARY || 'php';
    const phpArgs = ['-d', 'extension=pdo_sqlite', '-d', 'extension=sqlite3'];
    const router = 'tests/Support/ServidorExploradorSintetico.php';
    const run = args => new Promise((resolve, reject) => {
        const child = spawn(php, [...phpArgs, ...args], { cwd: root, env, windowsHide: true });
        let out = ''; child.stdout.on('data', b => out += b); child.stderr.on('data', b => out += b);
        child.on('error', reject); child.on('close', code => code ? reject(new Error(out)) : resolve(out));
    });
    assert.match(await run([router, '--preparar']), /PREPARADO/);
    const server = spawn(php, [...phpArgs, '-S', `127.0.0.1:${port}`, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    const evidence = process.env.RM_EXPLORADOR_EVIDENCIA || await mkdtemp(path.join(tmpdir(), 'remembermind-explorador-evidencia-'));
    await mkdir(evidence, { recursive: true });
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => p && existsSync(p));
    const browser = await puppeteer.launch({ headless: true, executablePath, args: ['--no-sandbox'] });
    const page = await browser.newPage();
    await page.setExtraHTTPHeaders({ 'X-RM-Explorador': env.RM_EXPLORADOR_TOKEN });
    let falloTransporte = 0;
    await page.setRequestInterception(true);
    page.on('request', request => {
        if (falloTransporte && request.method() === 'POST' && request.url().includes('/livewire')) {
            const status = falloTransporte; falloTransporte = 0;
            return request.respond({ status, body: 'Fallo sintético de transporte', contentType: 'text/plain' });
        }
        return request.url().startsWith(base) || request.url().startsWith('data:') ? request.continue() : request.abort();
    });
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    t.after(async () => { console.log('Errores de navegador:', errors); if (!page.isClosed()) await writeFile(path.join(evidence, 'ultimo-estado.html'), await page.content()); await browser.close(); server.kill(); });
    await page.evaluateOnNewDocument(() => { localStorage.setItem('remembermind-theme', 'light'); });
    const goto = async area => {
        const response = await page.goto(`${base}/admin/psicologia/evaluacion/${area}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
        assert.equal(response.status(), 200, area);
        await page.waitForFunction(() => window.Livewire && window.rmGuiaVisual && window.rmAvatarGuia);
    };
    await goto('cognitiva');
    console.log('Geometría inicial:', await page.evaluate(() => {
        const el = document.querySelector('.rm-sidebar'); const style = getComputedStyle(el);
        return { sidebar: style.width, token: style.getPropertyValue('--rm-sidebar-width'), position: style.position, collapsed: document.body.classList.contains('is-sidebar-collapsed') };
    }));
    const clickText = async (selector, text) => {
        const handle = await page.evaluateHandle((selector, text) => [...document.querySelectorAll(selector)].find(el => el.textContent.includes(text) && el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden'), selector, text);
        const el = handle.asElement(); assert.ok(el, `No se encontró ${text}`); await el.click();
    };
    const settled = () => page.waitForNetworkIdle({ idleTime: 300, timeout: 15000 });
    const matrix = [];
    for (const width of [1440, 1280, 1024, 768, 390, 320]) {
        await page.setViewport({ width, height: 1000 });
        await page.waitForFunction(width => document.body._x_dataStack?.[0]?.sidebarRange === (width >= 1280 ? 'desktop' : width >= 1024 ? 'laptop' : 'drawer'), {}, width);
        await page.waitForFunction(width => { const r = document.querySelector('.rm-sidebar').getBoundingClientRect(); return width >= 1024 ? Math.abs(r.left) < 1 && Math.abs(r.width - (width >= 1280 ? 224 : 80)) < 1 : r.right <= 1; }, {}, width);
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
        assert.ok(overflow <= 1, `${width}: desbordamiento ${overflow}`);
        matrix.push({ width, overflow });
        await page.screenshot({ path: path.join(evidence, `cognitiva-${width}.png`), fullPage: true });
    }
    await page.setViewport({ width: 1440, height: 1000 });
    for (const area of ['afectiva', 'funcionamiento', 'entorno']) {
        await goto(area);
        await page.screenshot({ path: path.join(evidence, `${area}-1440.png`), fullPage: true });
        if (area !== 'funcionamiento') assert.equal(await page.$$eval('.rm-instrumento__acciones .rm-btn-primary', els => els.length), 0);
    }
    await goto('cognitiva');
    await clickText('.rm-explorador__bibliotecas button', 'Biblioteca de investigación'); await settled();
    assert.equal(await page.$eval('.rm-explorador__grid', el => el.querySelectorAll('.rm-instrumento__acciones .rm-btn-primary').length), 0);
    await clickText('.rm-explorador__dominios button', 'Memoria'); await settled();
    await page.click('.rm-instrumento button[aria-label^="Explicación"]'); await settled();
    await page.waitForSelector('#guia-instrumento.is-open');
    await page.click('#guia-tab-pasos');
    await clickText('#guia-panel-pasos button', 'Siguiente');
    assert.match(await page.$eval('.rm-guia__navegacion', el => el.textContent), /Paso 2 de 4/);
    await page.screenshot({ path: path.join(evidence, 'guia-desktop.png'), fullPage: true });
    await page.focus('#guia-tab-pasos'); await page.keyboard.press('ArrowRight');
    assert.equal(await page.$eval('#guia-tab-materiales', el => el.getAttribute('aria-selected')), 'true');
    await page.setViewport({ width: 390, height: 844 });
    assert.ok(await page.$eval('#guia-instrumento .rm-modal-panel', el => Math.abs(el.getBoundingClientRect().height - innerHeight) <= 2));
    await page.screenshot({ path: path.join(evidence, 'guia-390.png'), fullPage: true });
    await page.keyboard.press('Escape'); await settled();
    await page.waitForFunction(() => !document.querySelector('#guia-instrumento').classList.contains('is-open'));
    await page.setViewport({ width: 1280, height: 950 });
    await clickText('.rm-explorador__bibliotecas button', 'Catálogo institucional'); await settled();
    await clickText('.rm-instrumento__acciones button', 'Registrar aplicación'); await settled();
    await page.waitForSelector('.rm-modal-shell.is-open [id^="captura-residente-"]:not(select)');
    await page.click('.rm-modal-shell.is-open button[id^="captura-residente-"]');
    await page.waitForSelector('.rm-choice__popover:not([style*="display: none"]) [role="option"]', { visible: true });
    await clickText('.rm-choice__popover [role="option"]', 'Residente sintético'); await settled();
    await page.waitForSelector('.rm-modal-shell.is-open button[id^="captura-valor-"]');
    falloTransporte = 503;
    await clickText('.rm-modal-shell.is-open button', 'Revisar'); await settled();
    await page.waitForFunction(() => document.querySelector('.rm-captura-instrumental').innerText.includes('No se pudo confirmar'));
    await page.screenshot({ path: path.join(evidence, 'error-conexion.png'), fullPage: true });
    await clickText('.rm-modal-shell.is-open button', 'Revisar'); await settled();
    await page.waitForFunction(() => document.querySelector('.rm-captura-instrumental').innerText.includes('Registra al menos una respuesta original'));
    await page.screenshot({ path: path.join(evidence, 'error-validacion.png'), fullPage: true });
    // Cero/No debe permanecer como dato y guardar en el backend real, después de revisar.
    await page.click('.rm-modal-shell.is-open button[id^="captura-valor-"]');
    await clickText('.rm-choice__popover [role="option"]', 'No'); await settled();
    await page.click('.rm-modal-shell.is-open button[id^="captura-fecha-"]');
    await page.waitForSelector('.rm-date-picker:not([style*="display: none"])', { visible: true });
    await page.keyboard.press('Escape');
    await page.screenshot({ path: path.join(evidence, 'captura-1280.png'), fullPage: true });
    await clickText('.rm-modal-shell.is-open button', 'Revisar'); await settled();
    await clickText('.rm-modal-shell.is-open button', 'Confirmar'); await settled();
    await page.waitForFunction(() => !document.querySelector('.rm-modal-shell.is-open[id^="captura-instrumental-"]'));
    assert.match(await page.$eval('.rm-table-container tbody', el => el.textContent), /Residente sintético/);
    const persistencia = JSON.parse(await run([router, '--estado']));
    assert.deepEqual(persistencia, { aplicaciones: 1, respuestas: 1, valor_logico: false, evaluaciones: 0, instrumentos: 3 });
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    assert.equal(await page.$eval('.rm-instrumento', el => getComputedStyle(el).animationName), 'none');
    assert.deepEqual(errors, []);
    await writeFile(path.join(evidence, 'matriz.json'), JSON.stringify({ matrix, errors, persistencia, synthetic: true, backend: 'SQLite aislado; captura Livewire real', evidence }, null, 2));
    console.log(`Evidencia sintética: ${evidence}`);
});
