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
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));

test('reporte de Enfermería: filtros reales, vista previa, PDF y cinco tamaños', { timeout: 120000, skip: !executablePath }, async t => {
    const db = join(tmpdir(), `remembermind-reporte-${randomBytes(8).toString('hex')}_testing.sqlite`);
    writeFileSync(db, '', { flag: 'wx' });
    t.after(() => { for (const f of [db, `${db}-wal`, `${db}-shm`]) rmSync(f, { force: true }); });
    const port = await new Promise((resolve, reject) => {
        const socket = createServer(); socket.on('error', reject);
        socket.listen(0, '127.0.0.1', () => { const p = socket.address().port; socket.close(() => resolve(p)); });
    });
    const base = `http://127.0.0.1:${port}`, token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_REPORTE_DB: db, RM_REPORTE_TOKEN: token };
    const router = join(root, 'tests/Support/ServidorReporteEnfermeriaSintetico.php');
    const prep = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 30000 });
    assert.equal(prep.status, 0, prep.stderr + prep.stdout); assert.match(prep.stdout, /PREPARADO/);
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', join(root, 'public'), router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    t.after(() => server.kill());
    let ready = false, lastResponse = '';
    for (let i = 0; i < 40; i++) {
        try { const r = await fetch(`${base}/admin/enfermeria/reportes`, { headers: { 'X-RM-REPORTE-QA': token } }); if (r.ok) { ready = true; break; } lastResponse = `${r.status}: ${(await r.text()).replace(/<[^>]*>/g, '').slice(0, 1600)}`; } catch (e) { if (server.exitCode !== null) throw e; }
        await new Promise(r => setTimeout(r, 100));
    }
    assert.ok(ready, `No inició el reporte sintético: ${lastResponse}`);
    const browser = await puppeteer.launch({ headless: true, executablePath }); t.after(() => browser.close());
    const page = await browser.newPage(), errors = []; page.on('pageerror', e => errors.push(e.message));
    await page.setRequestInterception(true);
    page.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-REPORTE-QA': token } }) : r.abort());
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);

    const revisarCabecera = async (vista, width) => {
        const orden = await page.$eval('[data-rm-main] header[data-rm-ui="page-heading"]', header => {
            const rect = el => el?.getBoundingClientRect().toJSON();
            return {
                cabecera: rect(header),
                titulo: rect(header.querySelector('.rm-collection-header__identity')),
                contexto: rect(header.querySelector('.rm-collection-header__meta')),
                acciones: rect(header.querySelector('.rm-collection-header__actions')),
                alineacion: getComputedStyle(header.querySelector('.rm-collection-header__copy')).textAlign,
            };
        });
        assert.equal(orden.alineacion, 'start', vista + ' ' + width);
        if (orden.contexto) assert.ok(orden.contexto.top >= orden.titulo.bottom - 1, 'Estado y fecha debajo del título en ' + vista + ' ' + width);
        for (const bloque of [orden.titulo, orden.contexto, orden.acciones].filter(Boolean)) {
            assert.ok(bloque.right <= orden.cabecera.right + 1 && bloque.left >= orden.cabecera.left - 1, 'Cabecera dentro de la pantalla en ' + vista + ' ' + width);
        }
        if (orden.acciones?.width && width > 600) assert.ok(orden.acciones.left >= orden.titulo.right - 1, 'Acciones al costado en ' + vista);
    };
    const output = process.env.RM_REPORTE_QA_EVIDENCE || join(root, 'output/reporte-enfermeria-20261027'); mkdirSync(output, { recursive: true });
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({ width, height: 1000 });
        const response = await page.goto(`${base}/admin/enfermeria/reportes`, { waitUntil: 'networkidle0' }); assert.equal(response.status(), 200);
        await page.waitForFunction(() => window.Livewire && window.Alpine);
        await revisarCabecera('reportes', width);
        assert.ok(await page.$('a[href$="/admin/enfermeria/reportes"]'), 'Acceso al reporte desde navegación de Enfermería');
        for (const theme of ['light', 'dark']) {
            await page.evaluate(theme => window.RememberMindTheme.apply(theme), theme);
            await new Promise(r => setTimeout(r, 300));
            const data = await page.evaluate(() => ({ width: document.documentElement.scrollWidth, viewport: innerWidth,
                text: document.querySelector('main').innerText, labeled: [...document.querySelectorAll('main input,main select')].every(el => document.querySelector(`label[for="${el.id}"]`)) }));
            assert.ok(data.width <= data.viewport + 1, `Desbordamiento ${width}/${theme}: ${data.width}`);
            assert.ok(data.labeled); assert.doesNotMatch(data.text, /\b(?:undefined|NaN|Infinity)\b|\[object Object\]/);
            const controls = await page.$$eval('.rm-nursing-report select,.rm-nursing-report input', els => els.map(el => ({ height: el.getBoundingClientRect().height, line: parseFloat(getComputedStyle(el).lineHeight) })));
            assert.ok(controls.every(c => c.height >= 44 && c.line <= c.height), 'Campos completos y objetivos táctiles');
            const contrast = await page.evaluate(() => {
                const canvas = document.createElement('canvas'), ctx = canvas.getContext('2d');
                const luminance = color => {
                    ctx.clearRect(0, 0, 1, 1); ctx.fillStyle = color; ctx.fillRect(0, 0, 1, 1);
                    return [...ctx.getImageData(0, 0, 1, 1).data].slice(0, 3).map(v => v / 255)
                        .map(v => v <= 0.04045 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4)
                        .reduce((n, v, i) => n + v * [0.2126, 0.7152, 0.0722][i], 0);
                };
                return [...document.querySelectorAll('.rm-nursing-report .rm-label,.rm-nursing-report__count,.rm-nursing-report .rm-card > p')].map(el => {
                    const fg = luminance(getComputedStyle(el).color), bg = luminance(getComputedStyle(el.closest('.rm-card')).backgroundColor);
                    return (Math.max(fg, bg) + 0.05) / (Math.min(fg, bg) + 0.05);
                });
            });
            const surface = await page.$eval('.rm-nursing-report__filters', el => {
                const s = getComputedStyle(el); return { background: s.backgroundColor, color: s.color };
            });
            assert.ok(contrast.every(v => v >= 4.5), `Contraste texto/superficie ${theme}: ${contrast}; ${JSON.stringify(surface)}`);
            await page.screenshot({ path: join(output, `reporte-${width}-${theme}.png`), fullPage: true });
        }
    }
    await page.click('[aria-controls="enfermeria-tipos-grafica"]');
    await page.click('#enfermeria-tipos-grafica button[value="torta"]');
    await page.waitForFunction(() => window.RMCharts.get(document.querySelector('.rm-nursing-report canvas')?.id)?.config.type === 'pie');
    await page.$eval('#reporte-desde', el => el.focus());
    assert.equal(await page.evaluate(() => document.activeElement.id), 'reporte-desde');
    await page.evaluate(() => {
        for (const id of ['reporte-desde', 'reporte-hasta']) {
            const el = document.getElementById(id); el.value = '2026-10-20';
            el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    await page.click('form[aria-label="Filtros del reporte"] button[type="submit"]');
    await page.waitForFunction(() => document.querySelector('a[href*="vista-previa"]').href.includes('hasta=2026-10-20'));
    assert.match(await page.$eval('main', el => el.innerText), /No hay registros para los filtros aplicados/);
    await page.screenshot({ path: join(output, 'sin-registros-390.png'), fullPage: true });
    await page.evaluate(() => {
        const el = document.getElementById('reporte-hasta'); el.value = '2026-10-27';
        el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true }));
    });
    await page.select('#reporte-residente', 'RES_QA_RPT'); await page.select('#reporte-tipo', 'CONTROLES');
    await page.click('form[aria-label="Filtros del reporte"] button[type="submit"]');
    await page.waitForFunction(() => document.querySelector('a[href*="vista-previa"]').href.includes('tipo=CONTROLES'));
    const registros = '.rm-nursing-report__records';
    assert.equal(await page.$$eval(registros + ' tbody tr', rows => rows.length), 20);
    await page.click(registros + ' [aria-label="Página siguiente"]');
    await page.waitForFunction(selector => document.querySelectorAll(selector + ' tbody tr').length === 3, {}, registros);
    const preview = await page.$eval('a[href*="vista-previa"]', el => el.href);
    const trigger = '[aria-controls="exportar-enfermeria-reporte"]';
    const dialog = '#exportar-enfermeria-reporte';
    await page.focus(trigger);
    await page.keyboard.press('Enter');
    await page.waitForSelector(dialog, { visible: true });
    assert.equal(new URL(page.url()).pathname, '/admin/enfermeria/reportes');
    const [pdfUrl, csvUrl] = await page.$eval(dialog + ' form', el => ['pdfUrl','csvUrl'].map(key => {
        const url = new URL(el.dataset[key]);
        url.search = new URLSearchParams(new FormData(el));
        return url.href;
    }));
    await page.click(dialog + ' [value="csv"]');
    await page.click(dialog + ' button[type="submit"]');
    await page.waitForFunction(selector => document.querySelector(selector).innerText.includes('La descarga se inició'), {}, dialog);
    await page.keyboard.press('Escape');
    await page.waitForSelector(dialog, { hidden: true });
    assert.equal(await page.$eval(trigger, el => el === document.activeElement), true);
    const csv = await fetch(csvUrl, { headers: { 'X-RM-REPORTE-QA': token } });
    assert.equal(csv.status, 200); const csvText = await csv.text();
    assert.equal(csvText.split('\n').filter(row => row.includes('Control de signos vitales')).length, 23);
    const pdf = await fetch(pdfUrl, { headers: { 'X-RM-REPORTE-QA': token } });
    assert.equal(pdf.status, 200); const bytes = Buffer.from(await pdf.arrayBuffer()); assert.equal(bytes.subarray(0, 5).toString(), '%PDF-');
    writeFileSync(join(output, 'resumen-sintetico.pdf'), bytes);
    await page.goto(preview, { waitUntil: 'networkidle0' });
    assert.equal(await page.$$eval('.scroll tbody tr', rows => rows.length), 23);
    assert.match(await page.$eval('body', el => el.innerText), /Alicia Sintética/);
    await page.screenshot({ path: join(output, 'vista-previa-390.png'), fullPage: true });
    await page.evaluate(() => { window.print = () => { window.reportPrinted = true; }; });
    await page.click('[data-report-print]');
    assert.equal(await page.evaluate(() => window.reportPrinted), true, 'Botón conectado a imprimir');
    await page.emulateMediaType('print');
    assert.equal(await page.$eval('.report-tools', el => getComputedStyle(el).display), 'none');
    await page.emulateMediaType('screen');
    await page.goto(`${base}/admin/enfermeria/cuidados`, { waitUntil: 'networkidle0' });
    const cuidados = '.rm-care-center';
    await page.waitForSelector(cuidados + ' .rm-care-center__overview');
    assert.equal(await page.$$eval(cuidados + ' .rm-report-export-button', nodes => nodes.length), 1);
    assert.equal(await page.$eval('#central-periodo', el => el.checkVisibility()), false);
    await page.click('[aria-controls="central-periodo"]');
    await page.waitForSelector('#central-desde', { visible: true });
    await page.click('[aria-controls="central-periodo"]');
    await page.select('#central-tipo', 'hidratacion');
    const hidratacion = '[aria-controls="exportar-enfermeria-hidratacion"]';
    await page.waitForSelector(hidratacion, { visible: true });
    await page.click(hidratacion);
    await page.waitForSelector('#exportar-enfermeria-hidratacion', { visible: true });
    assert.equal(await page.$eval('#exportar-enfermeria-hidratacion input[name="tipo"]', el => el.value), 'HIDRATACION');
    await page.keyboard.press('Escape');
    await page.waitForSelector('#exportar-enfermeria-hidratacion', { hidden: true });
    await page.click(cuidados + ' [aria-label="Limpiar filtros"]');
    await page.waitForFunction(() => document.getElementById('central-tipo').value === '');
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    await page.evaluate(() => window.RememberMindTheme.apply('light'));
    await page.screenshot({ path: join(output, 'cuidados-limpio-390.png') });
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({ width, height: 1000 });
        const response = await page.goto(base + '/admin/enfermeria/medicacion', { waitUntil: 'networkidle0' });
        assert.equal(response.status(), 200);
        await page.waitForSelector('.rm-medication-workspace table', { visible: true });
        await revisarCabecera('medicacion', width);
        await page.evaluate(() => window.RememberMindTheme.apply('light'));
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), 'Medicación en ' + width);
        assert.equal(await page.$eval('[aria-controls="exportar-enfermeria-medicacion"]', el => el.getBoundingClientRect().width), 44);
        await page.screenshot({ path: join(output, 'medicacion-' + width + '.png') });
    }
    await page.click('[aria-controls="exportar-enfermeria-medicacion"]');
    await page.waitForSelector('#exportar-enfermeria-medicacion', { visible: true });
    await page.screenshot({ path: join(output, 'medicacion-exportar-390.png') });
    await page.keyboard.press('Escape');
    await page.waitForSelector('#exportar-enfermeria-medicacion', { hidden: true });
    await page.click('.rm-medication-workspace__tab:last-child');
    await page.waitForSelector('#historial-med-buscar', { visible: true });
    assert.equal(await page.$eval('#historial-med-filtros-extra', el => el.checkVisibility()), false);
    await page.click('[aria-controls="historial-med-filtros-extra"]');
    await page.waitForSelector('#historial-med-via', { visible: true });
    await page.select('#historial-med-via', 'ORAL');
    await page.waitForFunction(() => document.querySelector('.rm-medication-workspace__content').innerText.includes('Vía: Oral'));

    await page.goto(base + '/admin/enfermeria/pacientes', { waitUntil: 'networkidle0' });
    assert.match(await page.$eval('.rm-resident-directory__header .rm-collection-header__context', el => el.textContent), /Solo lectura/);
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({ width, height: 1000 });
        await revisarCabecera('pacientes', width);
        await page.screenshot({ path: join(output, 'cabecera-residentes-' + width + '.png') });
    }
    for (const vista of ['dashboard', 'pacientes', 'alertas', 'incidentes', 'pase-turno', 'agenda', 'registros', 'tareas', 'seguimiento']) {
        const response = await page.goto(base + '/admin/enfermeria/' + vista, { waitUntil: 'networkidle0' });
        assert.equal(response.status(), 200, vista);
        if (vista !== 'dashboard') await revisarCabecera(vista, 390);
        if (vista === 'alertas') {
            assert.equal(await page.$('[aria-label="Alertas por vínculo profesional"], #alertas-especialidad'), null);
            await page.screenshot({ path: join(output, 'alertas-por-rol-390.png') });
        }
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1), vista);
    }
    await page.evaluateOnNewDocument(() => { window.print = () => { window.reportPrintRequests = (window.reportPrintRequests || 0) + 1; }; });
    await page.goto(`${preview}&imprimir=1`, { waitUntil: 'networkidle0' });
    await page.waitForFunction(() => window.reportPrintRequests === 1);
    const generalPdf = await fetch(pdfUrl.replace('tipo=CONTROLES', 'tipo=TODOS'), { headers: { 'X-RM-REPORTE-QA': token } });
    assert.equal(generalPdf.status, 200);
    writeFileSync(join(output, 'reporte-general-secciones.pdf'), Buffer.from(await generalPdf.arrayBuffer()));
    assert.deepEqual(errors, []);
});
