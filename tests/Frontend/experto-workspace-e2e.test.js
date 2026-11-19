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
        const h = await page.evaluateHandle(text => [...document.querySelectorAll('button')].find(e => e.innerText.trim() === text && e.getClientRects().length && !e.disabled), text);
        assert.ok(h.asElement(), `Botón ${text}`); await h.asElement().click(); await page.waitForNetworkIdle({ idleTime: 200 });
    };
    const matrix = [];
    if (phase === 'conservacion-institucional') {
        await go('MED', '/admin/medico/residente/RES_QA_EXPERTO/evaluacion-cognitiva/resultados-experto');
        await click('Evaluar y guardar resultado');
        await page.waitForFunction(() => document.querySelector('main')?.innerText.includes('Se recuperó y comprobó el resultado original'));
        const savedUrl = page.url();
        assert.match(savedUrl, /resultadoGuardado=/);
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), `resultado guardado ${width}: reflow`);
            await page.waitForFunction(() => innerWidth >= 768 || !document.querySelector(".rm-sidebar") || document.querySelector(".rm-sidebar").getBoundingClientRect().right <= 1);
            assert.match(await page.$eval('h1', el => el.textContent), /Aún no se puede establecer/);
            matrix.push({ role: 'medicina', width, overflow: 0 });
            await page.screenshot({ path: evidence(`resultado-${width}.png`), fullPage: true });
        }
        await click('Revisar valoración');
        await page.select('#decision-revision', 'CONFIRMADA');
        await page.type('#justificacion-revision', 'Abstención técnicamente fundada en esta prueba sintética.');
        await click('Guardar decisión profesional');
        await page.waitForFunction(() => document.querySelector('main')?.innerText.includes('Revisión de Medicina registrada.'));
        const query = new URL(page.url()).search;
        await go('PSI', '/admin/psicologia/residente/RES_QA_EXPERTO/resultados-experto' + query);
        assert.match(await page.$eval('main', el => el.innerText), /Valoración confirmada/);
        await click('Revisar valoración');
        await page.select('#decision-revision', 'RECHAZADA');
        await page.type('#justificacion-revision', 'Discrepancia de Psicología independiente, solo caso sintético.');
        await click('Guardar decisión profesional');
        await page.waitForFunction(() => document.querySelector('main')?.innerText.includes('Revisión de Psicología registrada.'));
        await page.reload({ waitUntil: 'networkidle0' });
        assert.match(await page.$eval('main', el => el.innerText), /Valoración confirmada/);
        assert.match(await page.$eval('main', el => el.innerText), /Valoración rechazada/);
        await page.screenshot({ path: evidence('dos-revisiones-390.png'), fullPage: true });
        assert.deepEqual(errors, []);
        await writeFile(evidence('matriz.json'), JSON.stringify({ matrix, errores: errors, guardado: true, recuperacionTrasRecarga: true, revisionesIndependientes: true, replayCompleto: false }, null, 2));
        return;
    }
    if (phase === 'continuacion-captura') {
        await go('MED', '/admin/sistema-experto/residentes/RES_QA_EXPERTO/petersen');
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), `captura ${width}: reflow`);
            const enlace = await page.$eval('a[href$="/evaluacion-cognitiva/resultados-experto"]', el => ({ texto: el.textContent, alto: el.getBoundingClientRect().height }));
            assert.match(enlace.texto, /Continuar al análisis/);
            assert.ok(enlace.alto >= 44);
            matrix.push({ role: 'medicina', width, overflow: 0 });
            await page.screenshot({ path: evidence(`continuacion-${width}.png`), fullPage: true });
        }
        await click('Registrar Evaluación diferencial');
        assert.equal(await page.$('a[href$="/evaluacion-cognitiva/resultados-experto"]'), null);
        await page.$eval('#pet-fecha', el => { el.value = new Date(Date.now() - 86400000).toISOString().slice(0, 16); el.dispatchEvent(new Event('input', { bubbles: true })); });
        await page.select('#pet-estado', 'N');
        await page.type('#pet-justificacion', 'Registro sintético de revisión diferencial incompleta para verificar la continuidad del formulario.');
        await click('Guardar valoración original');
        await page.waitForSelector('#pet-confirmar-guardado [role="dialog"]', { visible: true });
        await click('Confirmar registro');
        await page.waitForFunction(() => [...document.querySelectorAll('[role="status"]')].some(el => el.innerText.includes('Valoración original registrada: Evaluación diferencial.')));
        await page.$eval('a[href$="/evaluacion-cognitiva/resultados-experto"]', el => el.scrollIntoView({ block: 'center' }));
        await page.screenshot({ path: evidence('captura-guardada-390.png'), fullPage: true });
        const enlace = await page.$('a[href$="/evaluacion-cognitiva/resultados-experto"]');
        await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), enlace.click()]);
        assert.match(page.url(), /\/admin\/medico\/residente\/RES_QA_EXPERTO\/evaluacion-cognitiva\/resultados-experto$/);
        assert.match(await page.$eval('main', el => el.innerText), /Elena sintética/);
        await page.screenshot({ path: evidence('consulta-390.png'), fullPage: true });
        await click('Resumen y análisis');
        await click('Analizar evidencia');
        await page.waitForFunction(() => document.querySelector('main')?.innerText.includes('Resultado calculado para esta consulta:'));
        await page.screenshot({ path: evidence('analisis-390.png'), fullPage: true });
        assert.deepEqual(errors, []);
        await writeFile(evidence('matriz.json'), JSON.stringify({ matrix, errores: errors, capturaGuardada: true, mismaPersona: true, analisisEjecutado: true }, null, 2));
        return;
    }
    for (const [role, actor, url] of [['superadmin', 'SA', '/admin/sistema-experto'], ['medicina', 'MED', '/admin/medico/residente/RES_QA_EXPERTO/evaluacion-cognitiva/resultados-experto'], ['enfermeria', 'ENF', '/admin/enfermeria/pacientes/RES_QA_EXPERTO/resultados-experto']]) {
        if (phase === 'antes' && existsSync(evidence(`${role}-1440.png`))) continue;
        await page.setViewport({ width: 1440, height: 1000 }); await go(actor, url);
        await page.screenshot({ path: evidence(`${role}-1440.png`), fullPage: true });
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            await new Promise(resolve => setTimeout(resolve, 400));
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - innerWidth);
            const sidebar = await page.$eval('.rm-sidebar', el => ({ width: el.getBoundingClientRect().width, cssWidth: getComputedStyle(el).width, position: getComputedStyle(el).position, classes: el.className, shell: document.body.className })).catch(() => null);
            matrix.push({ role, width, overflow, sidebar });
            if (phase !== 'antes') assert.ok(overflow <= 1, `${role} ${width}: overflow ${overflow}`);
            if (width !== 1440) await page.screenshot({ path: evidence(`${role}-${width}.png`), fullPage: true });
        }
        if (phase !== 'antes') {
            assert.doesNotMatch(await page.$eval('body', el => el.innerText), /O\.R\.I\.O\.N\.?|\bORION\b|\bNaN\b|\bundefined\b|\[object Object\]/i);
            await page.setViewport({ width: 640, height: 500 });
            assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), `${role} zoom/reflow`);
        }
    }
    if (phase !== 'antes') {
        await page.setViewport({ width: 1440, height: 1000 }); await go('SA', '/admin/sistema-experto');
        await click('Conocimiento');
        const version = await page.$eval('#expert-version-scope', el => [...el.options].find(o => o.value)?.value);
        assert.ok(version, 'Conocimiento registrado en la base desechable');
        await page.select('#expert-version-scope', version); await page.waitForNetworkIdle({ idleTime: 300 });
        await page.select('#lab-modo', 'red'); await page.waitForNetworkIdle({ idleTime: 200 });
        await page.waitForSelector('[data-expert-node]');
        await page.click('[data-expert-node]'); await page.waitForNetworkIdle({ idleTime: 300 });
        assert.ok(await page.$eval('.rm-knowledge-inspector', el => el.innerText.includes('Nodo seleccionado')));
        await page.$eval('[data-expert-node]', el => el.focus()); await page.keyboard.press('Enter');
        await page.waitForNetworkIdle({ idleTime: 300 });
        assert.ok(await page.$eval('.rm-knowledge-inspector', el => el.innerText.includes('Versión')));
        await page.waitForFunction(() => document.querySelector('.rm-knowledge-selection')?.getClientRects().length && document.querySelector('[data-expert-node][aria-pressed="true"]'));
        await click('Enfocar conexiones');
        assert.ok(await page.evaluate(() => [...document.querySelectorAll('[data-expert-node]')].filter(n => n.getClientRects().length).length < document.querySelectorAll('[data-expert-node]').length));
        await click('Ver toda la página');
        await click('Organizar por niveles');
        await click('Distribuir en cuadrícula');
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

        await go('MED', '/admin/medico/residente/RES_QA_EXPERTO/evaluacion-cognitiva/resultados-experto');
        assert.doesNotMatch(await page.$eval('body', el => el.innerText), /Alteración simulada|Caso sintético|Ejecutar perfil/);
        const simulada = await page.goto(base + '/admin/medico/residente/RES_QA_EXPERTO/evaluacion-cognitiva/resultados-experto?simulacion=1', { waitUntil: 'networkidle0' });
        assert.equal(simulada.status(), 404, 'No se permite reabrir una simulación por URL');
    }
    await writeFile(evidence('qa.json'), JSON.stringify({ phase, matrix, errors }, null, 2));
    assert.deepEqual(errors, []);
});
