import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { createServer } from 'node:net';
import { randomBytes } from 'node:crypto';
import { writeFile, mkdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import puppeteer from 'puppeteer';

test('tabla de personal: filas compactas, acciones y adaptación responsive', { skip: process.env.RM_PERSONAL_TABLA_E2E !== '1', timeout: 240000 }, async t => {
    const root = fileURLToPath(new URL('../../', import.meta.url));
    const output = path.join(root, 'output', 'personal-tabla-diseno-20261105');
    await mkdir(output, { recursive: true });
    const db = path.join(tmpdir(), `remembermind-gerencia-${randomBytes(8).toString('hex')}_testing.sqlite`);
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    const port = await new Promise(resolve => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => resolve(p)); }); });
    const base = `http://127.0.0.1:${port}`;
    const token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_GERENCIA_QA_DB: db, RM_GERENCIA_QA_TOKEN: token, RM_GERENCIA_QA_AMPLIACION: '1', RM_GERENCIA_QA_ARCHIVOS: '1' };
    const router = 'tests/Support/ServidorGerenciaSintetico.php';
    const prepared = await new Promise((resolve, reject) => {
        const c = spawn('php', [router, '--preparar'], { cwd: root, env, windowsHide: true });
        let out = ''; c.stdout.on('data', b => out += b); c.stderr.on('data', b => out += b);
        c.on('error', reject); c.on('close', code => code ? reject(new Error(out)) : resolve(out));
    });
    assert.match(prepared, /PREPARADO/);
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    t.after(() => server.kill());
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => typeof p === 'string' && existsSync(p));
    const browser = await puppeteer.launch({ headless: true, executablePath, args: ['--no-sandbox'] });
    t.after(() => browser.close());
    const page = await browser.newPage();
    await page.setRequestInterception(true);
    page.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-GERENCIA-QA': token } }) : r.url().startsWith('data:') || r.url().startsWith('blob:') ? r.continue() : r.abort());
    await page.evaluateOnNewDocument(() => localStorage.setItem('remembermind-theme', 'light'));
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    t.after(async () => { await writeFile(path.join(output, 'browser-errors.json'), JSON.stringify(errors)); });
    await page.goto(base + '/admin/personal-institucional', { waitUntil: 'networkidle0' });
    await page.waitForSelector('.rm-staff-directory__table');
    const matrix = [];
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({ width, height: width <= 390 ? 844 : 800 });
        await page.evaluate(() => window.scrollTo(0, 0));
        const medidas = await page.evaluate(() => ({
            overflow: document.documentElement.scrollWidth - innerWidth,
            rows: document.querySelectorAll('.rm-staff-directory__table tbody tr').length,
            radius: getComputedStyle(document.querySelector('.rm-staff-directory__table tbody td')).borderTopLeftRadius,
            firstRowHeight: document.querySelector('.rm-staff-directory__table tbody tr').getBoundingClientRect().height,
            small: [...document.querySelectorAll('.rm-staff-directory__actions button')].filter(el => el.getClientRects().length && el.getBoundingClientRect().height < 44).length,
            smallDetails: [...document.querySelectorAll('.rm-staff-directory__actions button')].filter(el => el.getClientRects().length && el.getBoundingClientRect().height < 44).map(el => ({ label: el.innerText, height: el.getBoundingClientRect().height, style: getComputedStyle(el).minHeight })),
            filtrosSeparados: !document.querySelector('.rm-staff-directory__panel').contains(document.querySelector('.rm-staff-directory__filters')),
            fondoPanel: getComputedStyle(document.querySelector('.rm-staff-directory__panel')).backgroundColor,
            sombraPanel: getComputedStyle(document.querySelector('.rm-staff-directory__panel')).boxShadow,
            sinMarcoTabla: ['borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth'].every(prop => getComputedStyle(document.querySelector('.rm-staff-directory__table'))[prop] === '0px'),
            encabezados: [...document.querySelectorAll('.rm-staff-directory__table thead th')].map(el => el.textContent.trim()),
            sinDivisores: [...document.querySelectorAll('.rm-staff-directory__table tbody td')].every(el => getComputedStyle(el).borderBottomWidth === '0px' && getComputedStyle(el).borderRightWidth === '0px'),
            columnasAlineadas: [...document.querySelectorAll('.rm-staff-directory__table tbody tr')].every(row => [...row.cells].every((cell, index) => Math.abs(cell.getBoundingClientRect().left - document.querySelectorAll('.rm-staff-directory__table thead th')[index].getBoundingClientRect().left) < 1)),
        }));
        assert.ok(medidas.overflow <= 1, JSON.stringify({ width, ...medidas }));
        assert.ok(medidas.rows > 0);
        await page.screenshot({ path: path.join(output, 'tabla-' + width + '.png'), fullPage: true });
        assert.equal(medidas.small, 0, JSON.stringify(medidas.smallDetails));
        assert.equal(medidas.filtrosSeparados, true);
        assert.equal(medidas.fondoPanel, 'rgba(0, 0, 0, 0)');
        assert.equal(medidas.sombraPanel, 'none');
        assert.equal(medidas.sinMarcoTabla, true);
        assert.equal(medidas.sinDivisores, true);
        assert.deepEqual(medidas.encabezados, ['Trabajador', 'Función', 'Estado laboral', 'Acceso', 'Ficha', 'Acciones']);
        if (width >= 1280) assert.equal(medidas.columnasAlineadas, true);
        if (width >= 1280) assert.ok(medidas.firstRowHeight <= 56, `Fila compacta: ${medidas.firstRowHeight}`);
        await page.screenshot({ path: path.join(output, 'tabla-' + width + '.png'), fullPage: true });
        const trigger = await page.$('.rm-staff-directory__actions button[aria-expanded]');
        await trigger.evaluate(el => el.scrollIntoView({ block: 'center' }));
        await trigger.click();
        await page.waitForSelector('.rm-staff-directory__menu', { visible: true });
        const menu = await page.$eval('.rm-staff-directory__menu', el => ({ left: el.getBoundingClientRect().left, right: el.getBoundingClientRect().right, viewport: innerWidth }));
        assert.ok(menu.left >= 0 && menu.right <= menu.viewport + 1, JSON.stringify({ width, ...menu }));
        await page.keyboard.press('Escape');
        matrix.push({ width, ...medidas });
    }
    assert.deepEqual(errors, []);
    await writeFile(path.join(output, 'qa.json'), JSON.stringify(matrix, null, 2));
});
