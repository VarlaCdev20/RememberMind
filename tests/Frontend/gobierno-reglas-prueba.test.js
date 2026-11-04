import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { createServer } from 'node:net';
import { randomBytes } from 'node:crypto';
import { writeFile, mkdir, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer';

test('gobierno de reglas: cumplimiento, disponibilidad y responsive en la aplicación', { skip: process.env.RM_REGLAS_E2E !== '1', timeout: 180000 }, async t => {
    const root = fileURLToPath(new URL('../../', import.meta.url));
    const db = path.join(tmpdir(), `remembermind-experto-${randomBytes(8).toString('hex')}_testing.sqlite`);
    const storage = `${db}.storage`;
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    for (const dir of ['framework/views', 'framework/sessions', 'framework/cache/data', 'logs']) {
        await mkdir(path.join(storage, dir), { recursive: true, mode: 0o700 });
    }
    const port = await new Promise(resolve => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => resolve(p)); }); });
    const base = `http://127.0.0.1:${port}`;
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_EXPERTO_DB: db,
        RM_EXPERTO_TOKEN: randomBytes(24).toString('hex'), LARAVEL_STORAGE_PATH: storage,
        VIEW_COMPILED_PATH: path.join(storage, 'framework/views'), MAIL_MAILER: 'array' };
    const router = 'tests/Support/ServidorExpertoSintetico.php';
    let server, browser;
    t.after(async () => {
        if (browser) await browser.close();
        if (server && server.exitCode === null) await new Promise(resolve => { server.once('close', resolve); server.kill(); });
        await rm(db, { force: true });
        await rm(storage, { recursive: true, force: true });
    });
    const run = args => new Promise((resolve, reject) => {
        const c = spawn('php', args, { cwd: root, env, windowsHide: true });
        let out = ''; c.stdout.on('data', b => out += b); c.stderr.on('data', b => out += b);
        c.on('error', reject); c.on('close', code => code ? reject(new Error(out)) : resolve(out));
    });
    assert.match(await run([router, '--preparar']), /PREPARADO/);
    server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(),
        'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));
    assert.ok(executablePath);
    browser = await puppeteer.launch({ headless: true, executablePath });
    const page = await browser.newPage();
    await page.evaluateOnNewDocument(() => localStorage.setItem('remembermind-theme', 'light'));
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    await page.setRequestInterception(true);
    page.on('request', request => request.url().startsWith(base) || request.url().startsWith('data:') ? request.continue() : request.abort());
    await page.setExtraHTTPHeaders({ 'X-RM-Experto': env.RM_EXPERTO_TOKEN, 'X-RM-Actor': 'MED' });
    await page.setViewport({ width: 1440, height: 1000 });
    const response = await page.goto(`${base}/admin/sistema-experto/reglas`, { waitUntil: 'networkidle0' });
    assert.equal(response.status(), 200);
    const version = await page.$eval('#gob-version', el => [...el.options].find(o => o.textContent.includes('Perfil cognitivo inicial'))?.value);
    assert.ok(version);
    await page.select('#gob-version', version); await page.waitForNetworkIdle({ idleTime: 250 });
    const button = await page.evaluateHandle(() => [...document.querySelectorAll('button')].find(el => el.textContent.includes('Probar condiciones')));
    assert.ok(button.asElement()); await button.asElement().click();
    await page.waitForSelector('[id^="gob-prueba-"]');
    for (const select of await page.$$('[id^="gob-prueba-"]')) {
        const value = await select.evaluate(el => [...el.options].find(o => o.value)?.value);
        await select.select(value);
    }
    const evaluate = await page.evaluateHandle(() => [...document.querySelectorAll('button')].find(el => el.textContent.trim() === 'Evaluar condiciones'));
    await evaluate.asElement().click();
    await page.waitForFunction(() => document.body.innerText.includes('La prueba lógica no acredita mediciones'));
    assert.ok(await page.evaluate(() => document.body.innerText.includes('Se cumplen todas las condiciones con estas entradas.')));
    const out = path.join(root, 'output/experto-reglas-20261031-qa'); await mkdir(out, { recursive: true });
    const matrix = [];
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({ width, height: 1000 });
        if (width < 768) {
            await page.keyboard.press('Escape');
            await page.waitForFunction(() => !document.querySelector('.rm-sidebar')?.classList.contains('is-mobile-open'));
        }
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
        assert.ok(overflow <= 1, `${width}: overflow ${overflow}`);
        await page.focus('[id^="gob-prueba-"]');
        await page.$eval('[id^="gob-prueba-"]', el => el.scrollIntoView({ block: 'center' }));
        assert.ok(await page.$eval(':focus', el => el.getBoundingClientRect().height >= 44));
        assert.ok(await page.$eval(':focus', el => { const rect = el.getBoundingClientRect(); return rect.left >= 0 && rect.right <= innerWidth + 1; }));
        await page.screenshot({ path: path.join(out, `reglas-${width}.png`), fullPage: true });
        matrix.push({ width, overflow });
    }
    assert.deepEqual(errors, []);
    console.log(JSON.stringify({ matrix, errors }));
});
