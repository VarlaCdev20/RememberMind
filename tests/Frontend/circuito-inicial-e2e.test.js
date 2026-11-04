import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { createServer } from 'node:net';
import { randomBytes } from 'node:crypto';
import { writeFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import puppeteer from 'puppeteer';

test('captura inicial: tres ediciones, cinco anchos, ausencia, puntuación y confirmación', { skip: process.env.RM_CIRCUITO_E2E !== '1', timeout: 240000 }, async t => {
    const root = path.resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'));
    const db = path.join(tmpdir(), `remembermind-experto-${randomBytes(8).toString('hex')}_testing.sqlite`);
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    const port = await new Promise(resolve => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => resolve(p)); }); });
    const base = `http://127.0.0.1:${port}`;
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_EXPERTO_DB: db, RM_EXPERTO_TOKEN: randomBytes(24).toString('hex'), RM_EXPERTO_CIRCUITO: '1' };
    const router = 'tests/Support/ServidorExpertoSintetico.php';
    const run = args => new Promise((resolve, reject) => {
        const c = spawn('php', args, { cwd: root, env, windowsHide: true });
        let out = ''; c.stdout.on('data', b => out += b); c.stderr.on('data', b => out += b);
        c.on('error', reject); c.on('close', code => code ? reject(new Error(out)) : resolve(out));
    });
    assert.match(await run([router, '--preparar']), /PREPARADO/);
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => p && existsSync(p));
    const browser = await puppeteer.launch({ headless: true, executablePath, args: ['--no-sandbox'] });
    t.after(async () => { await browser.close(); server.kill(); });
    const page = await browser.newPage();
    await page.setExtraHTTPHeaders({ 'X-RM-Experto': env.RM_EXPERTO_TOKEN, 'X-RM-Actor': 'MED' });
    await page.setRequestInterception(true);
    page.on('request', r => r.url().startsWith(base) || r.url().startsWith('data:') ? r.continue() : r.abort());
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    await page.evaluateOnNewDocument(() => localStorage.setItem('remembermind-theme', 'light'));
    const matrix = [];
    for (const [codigo, titulo] of [['MOCA81-ES', 'MoCA Full 8.1'], ['RUDASPE20-ES', 'RUDAS-PE 2020'], ['FAQ-B7S08-ES', 'FAQ/Pfeffer B7S']]) {
        const response = await page.goto(base + '/__qa/circuito', { waitUntil: 'networkidle0' });
        assert.equal(response.status(), 200);
        await page.waitForFunction(() => window.Livewire);
        await page.click('button.rm-btn-primary');
        await page.waitForSelector('.rm-captura__modal [role="dialog"], [role="dialog"].rm-captura__modal', { visible: true });
        await page.evaluate(async titulo => {
            const el = document.querySelector('.rm-captura-instrumental');
            const c = Livewire.find(el.getAttribute('wire:id'));
            const option = [...el.querySelectorAll('option')].find(o => o.textContent.includes(titulo));
            if (!option) throw new Error('Falta instrumento de catálogo: ' + titulo);
            await c.set('cod_instrumento', option.value);
        }, titulo);
        await page.waitForFunction(titulo => document.body.innerText.includes(titulo) && document.body.innerText.includes('Puntuación incompleta'), {}, titulo);
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 900 });
            const state = await page.evaluate(() => {
                const dialog = document.querySelector('.rm-captura__modal [role="dialog"], [role="dialog"].rm-captura__modal');
                const fields = [...dialog.querySelectorAll('input:not([type="hidden"]),textarea,select')].filter(e => e.getClientRects().length);
                return { overflow: document.documentElement.scrollWidth - innerWidth, dialogOverflow: dialog.scrollWidth - dialog.clientWidth,
                    unlabeled: fields.filter(e => !e.labels?.length && !e.getAttribute('aria-label') && !e.getAttribute('aria-labelledby')).map(e => e.id) };
            });
            matrix.push({ codigo, width, ...state });
            assert.ok(state.overflow <= 1 && state.dialogOverflow <= 1, JSON.stringify(state));
            assert.deepEqual(state.unlabeled, []);
            if ([1440, 390].includes(width)) await page.screenshot({ path: path.join(root, 'output', `circuito-inicial-${codigo}-${width}.png`), fullPage: true });
        }
        await page.evaluate(async () => {
            const el = document.querySelector('.rm-captura-instrumental'); const c = Livewire.find(el.getAttribute('wire:id'));
            const valores = {};
            for (const [pk, meta] of Object.entries(c.vinculoMetodo.inicial.campos_por_pk)) valores[pk] = meta.tipo === 'LOGICO' ? '1' : 0;
            await c.set('valores', valores);
        });
        await page.waitForFunction(() => /(?:0|1) de 30 puntos/.test(document.body.innerText));
        await page.evaluate(async () => { const el = document.querySelector('.rm-captura-instrumental'); await Livewire.find(el.getAttribute('wire:id')).call('revisar'); });
        await page.waitForFunction(() => document.querySelector('.rm-captura-instrumental') && Livewire.find(document.querySelector('.rm-captura-instrumental').getAttribute('wire:id')).confirmando);
        assert.doesNotMatch(await page.$eval('body', el => el.innerText), /\bNaN\b|\bundefined\b|\[object Object\]/);
    }
    await writeFile(path.join(root, 'output/circuito-inicial-20261104-responsive.json'), JSON.stringify({ matrix, errors }, null, 2));
    assert.deepEqual(errors, []);
});
