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

test('Alertas de Gerencia: filtros, detalle y cinco anchos', { timeout: 240000 }, async t => {
    const root = fileURLToPath(new URL('../../', import.meta.url));
    const output = path.join(root, 'output', 'gerencia-alertas-diseno-20261107');
    await mkdir(output, { recursive: true });
    const db = path.join(tmpdir(), `remembermind-gerencia-${randomBytes(8).toString('hex')}_testing.sqlite`);
    await writeFile(db, '', { flag: 'wx', mode: 0o600 });
    const port = await new Promise(resolve => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => resolve(p)); }); });
    const base = `http://127.0.0.1:${port}`;
    const token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', AVISOS_PLANIFICACION_HABILITADOS: 'false', APP_URL: base, RM_GERENCIA_QA_DB: db, RM_GERENCIA_QA_TOKEN: token };
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
    const response = await page.goto(base + '/admin/alertas-clinicas', { waitUntil: 'networkidle0' });
    assert.equal(response.status(), 200);

    const click = async selector => { await page.$eval(selector,e=>e.scrollIntoView({block:'center'})); await page.click(selector); await page.waitForNetworkIdle({idleTime:300}); };

    for (const width of [1440,1280,1024,768,390]) {
        await page.setViewport({width,height:1000});
        await page.goto(base+'/admin/alertas-clinicas',{waitUntil:'networkidle0'});
        await page.waitForSelector('.rm-manager-alerts__list tbody tr',{visible:true});
        assert.equal(await page.$$eval('[data-alert-summary]',e=>e.length),3);
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'Listado sin desbordamiento');
        assert.ok(await page.$$eval('.rm-manager-alerts__list tbody tr',e=>e.every(r=>r.scrollHeight<=r.clientHeight+2)),'Filas sin superposición');
        await page.screenshot({path:path.join(output,'alertas-'+width+'.png'),fullPage:true});
        await click('[data-alert-summary="urgentes"]');
        await page.waitForFunction(()=>document.querySelector('#gerencia-alertas-prioridad').value==='PRIORITARIAS');
        await click('button[wire\\:click="limpiarFiltros"]');
        await page.waitForFunction(()=>document.querySelector('#gerencia-alertas-estado').value==='' && document.querySelector('#gerencia-alertas-prioridad').value==='');
        await click('.rm-manager-alerts__advanced summary');
        await page.waitForSelector('#gerencia-alertas-desde',{visible:true});
        assert.ok(await page.$eval('#gerencia-alertas-desde',e=>e.getBoundingClientRect().height>=44),'Filtros táctiles');
        await click('.rm-manager-alerts__advanced summary');
        await page.type('#gerencia-alertas-buscar','Sin coincidencia sintética');
        await page.waitForFunction(()=>document.querySelector('.rm-manager-alerts__list').innerText.includes('No hay alertas en esta consulta'));
        await click('button[wire\\:click="limpiarFiltros"]');
        await page.waitForSelector('.rm-manager-alerts__list tbody tr',{visible:true});
        await click('.rm-manager-alerts__list button[wire\\:click^="verDetalle("]');
        await page.waitForSelector('[role="dialog"]',{visible:true});
        assert.ok((await page.$eval('[role="dialog"]',e=>e.innerText)).includes('Situación registrada'),'Detalle conectado al registro');
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'Detalle sin desbordamiento');
        await page.screenshot({path:path.join(output,'detalle-'+width+'.png'),fullPage:true});
        await click('[role="dialog"] button[aria-label="Cerrar panel"]');
        await page.waitForFunction(()=>![...document.querySelectorAll('[role="dialog"]')].some(e=>e.getClientRects().length));
    }
    assert.deepEqual(errors,[]);
});
