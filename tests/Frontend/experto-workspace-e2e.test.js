import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { createServer } from 'node:net';
import { randomBytes } from 'node:crypto';
import { writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

test('sistema experto real: roles, grafo, teclado y cinco anchos', { skip: process.env.RM_EXPERTO_E2E !== '1', timeout: 240000 }, async t => {
    const root = path.resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'));
    const db = path.join(tmpdir(), `remembermind-experto-${randomBytes(8).toString('hex')}_testing.sqlite`);
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    const port = await new Promise(resolve => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => resolve(p)); }); });
    const base = `http://127.0.0.1:${port}`;
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_EXPERTO_DB: db, RM_EXPERTO_TOKEN: randomBytes(24).toString('hex') };
    const router = 'tests/Support/ServidorExpertoSintetico.php';
    const phpArgs = ['-d', 'extension=pdo_sqlite', '-d', 'extension=sqlite3'];
    const run = args => new Promise((resolve, reject) => {
        const c = spawn('php', [...phpArgs, ...args], { cwd: root, env, windowsHide: true });
        let out = ''; c.stdout.on('data', b => out += b); c.stderr.on('data', b => out += b);
        c.on('error', reject); c.on('close', code => code ? reject(new Error(out)) : resolve(out));
    });
    assert.match(await run([router, '--preparar']), /PREPARADO/);
    const server = spawn('php', [...phpArgs, '-S', `127.0.0.1:${port}`, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => p && existsSync(p));
    const browser = await puppeteer.launch({ headless: true, executablePath, args: ['--no-sandbox'] });
    const page = await browser.newPage();
    await page.setRequestInterception(true);
    page.on('request', r => r.url().startsWith(base) || r.url().startsWith('data:') ? r.continue() : r.abort());
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    const phase = process.env.RM_EXPERTO_FASE || 'despues';
    const evidence = name => path.join(root, 'output', `experto-${phase}-${name}`);
    t.after(async () => { await browser.close(); server.kill(); });
    await page.evaluateOnNewDocument(() => localStorage.setItem('remembermind-theme', 'light'));
    const go = async (actor, url) => {
        await page.setExtraHTTPHeaders({ 'X-RM-Experto': env.RM_EXPERTO_TOKEN, 'X-RM-Actor': actor });
        const r = await page.goto(base + url, { waitUntil: 'networkidle0' });
        assert.equal(r.status(), 200, url);
        await page.waitForFunction(() => window.Livewire);
    };
    const click = async text => {
        const h = await page.evaluateHandle(text => [...document.querySelectorAll('button')].find(e => e.textContent.trim() === text && e.getClientRects().length && !e.disabled), text);
        assert.ok(h.asElement(), `Botón ${text}`); await h.asElement().click(); await page.waitForNetworkIdle({ idleTime: 200 });
    };
    const matrix = [];
    for (const [role, actor, url] of [['superadmin', 'SA', '/admin/sistema-experto'], ['medicina', 'MED', '/admin/medico/residente/RES_QA_EXPERTO/evaluacion-cognitiva/resultados-experto'], ['enfermeria', 'ENF', '/admin/enfermeria/pacientes/RES_QA_EXPERTO/resultados-experto']]) {
        if (phase === 'antes' && existsSync(evidence(`${role}-1440.png`))) continue;
        await page.setViewport({ width: 1440, height: 1000 }); await go(actor, url);
        await page.screenshot({ path: evidence(`${role}-1440.png`), fullPage: true });
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
            const sidebar = await page.$eval('.rm-sidebar', el => ({ width: el.getBoundingClientRect().width, cssWidth: getComputedStyle(el).width, position: getComputedStyle(el).position, classes: el.className, shell: document.body.className })).catch(() => null);
            matrix.push({ role, width, overflow, sidebar });
            if (phase !== 'antes') assert.ok(overflow <= 1, `${role} ${width}: overflow ${overflow}`);
            if (width !== 1440) await page.screenshot({ path: evidence(`${role}-${width}.png`), fullPage: true });
        }
        if (phase !== 'antes') {
            assert.doesNotMatch(await page.$eval('body', el => el.innerText), /O\.R\.I\.O\.N\.|\bORION\b|\bNaN\b|\bundefined\b|\[object Object\]/);
            await page.setViewport({ width: 640, height: 500 });
            assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), `${role} zoom/reflow`);
        }
    }
    if (phase !== 'antes') {
        await page.setViewport({ width: 1440, height: 1000 }); await go('SA', '/admin/sistema-experto');
        await click('Conocimiento'); await page.select('#lab-modo', 'red'); await page.waitForNetworkIdle({ idleTime: 200 });
        await page.waitForSelector('[data-expert-node]');
        await page.click('[data-expert-node]'); await page.waitForNetworkIdle({ idleTime: 300 });
        assert.ok(await page.$eval('.rm-knowledge-inspector', el => el.innerText.includes('Nodo seleccionado')));
        await page.$eval('[data-expert-node]', el => el.focus()); await page.keyboard.press('Enter');
        await page.waitForNetworkIdle({ idleTime: 300 });
        assert.ok(await page.$eval('.rm-knowledge-inspector', el => el.innerText.includes('Versión')));
        await page.waitForFunction(() => document.querySelector('.rm-knowledge-selection')?.getClientRects().length && document.querySelector('[data-expert-node][aria-pressed="true"]'));
        await page.screenshot({ path: evidence('grafo-1440.png'), fullPage: true });
        await page.setViewport({ width: 390, height: 1000 });
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
        await page.screenshot({ path: evidence('grafo-390.png'), fullPage: true });
        await page.type('#expert-network-search', 'concepto-inexistente'); await page.waitForNetworkIdle({ idleTime: 500 });
        await page.waitForFunction(() => document.body.innerText.includes('Sin nodos que coincidan'));
        await click('Limpiar filtros');
        await page.select('#lab-modo', 'catalogo'); await page.waitForNetworkIdle({ idleTime: 300 });
        await page.click('[data-expert-detail]'); await page.waitForSelector('[role="dialog"]', { visible: true });
        await page.waitForFunction(() => document.activeElement?.closest('[role="dialog"]'));
        await page.keyboard.press('Escape'); await page.waitForNetworkIdle({ idleTime: 300 });
        await page.waitForFunction(() => !document.querySelector('.rm-drawer-shell.is-open'));
        await page.screenshot({ path: evidence('catalogo-390.png'), fullPage: true });
        await page.setViewport({ width: 1440, height: 1000 });
        await page.evaluate(() => { document.documentElement.style.zoom = '2'; });
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'Reflow a escala CSS 200%');
        await page.screenshot({ path: evidence('catalogo-200.png'), fullPage: true });
        await page.evaluate(() => { document.documentElement.style.zoom = ''; });
        await go('ENF', '/admin/enfermeria/pacientes/RES_QA_EXPERTO/resultados-experto');
        const denied = await page.goto(base + '/admin/enfermeria/pacientes/RES_QA_AJENO/resultados-experto', { waitUntil: 'networkidle0' });
        assert.equal(denied.status(), 404);
        assert.ok(!await page.$eval('body', el => el.innerText.includes('Residente no asignado')));
    }
    await writeFile(evidence('qa.json'), JSON.stringify({ phase, matrix, errors }, null, 2));
    assert.deepEqual(errors, []);
});
