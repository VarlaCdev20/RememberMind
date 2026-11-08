import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, writeFileSync, rmSync } from 'node:fs';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { test } from 'node:test';
import puppeteer from 'puppeteer';

test('Áreas: carga, Galería, descarte, persistencia y formulario adaptable', {
    skip: process.env.RM_AREAS_FOTOGRAFIA_E2E !== '1', timeout: 240000,
}, async () => {
    const root = resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/i, '$1'));
    const id = randomBytes(8).toString('hex');
    const db = join(tmpdir(), 'remembermind-gerencia-' + id + '_testing.sqlite');
    assert.equal(resolve(dirname(db)), resolve(tmpdir()));
    writeFileSync(db, '', { flag: 'wx' });
    const port = await new Promise(r => {
        const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => r(p)); });
    });
    const base = 'http://127.0.0.1:' + port, token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_GERENCIA_QA_DB: db,
        RM_GERENCIA_QA_TOKEN: token, RM_GERENCIA_QA_ARCHIVOS: '1', RM_GERENCIA_QA_AMPLIACION: '1' };
    const router = join(root, 'tests/Support/ServidorGerenciaSintetico.php');
    const out = join(tmpdir(), 'remembermind-area-fotografia-' + id); mkdirSync(out);
    let server, browser, page;
    try {
        const init = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 45000 });
        assert.equal(init.status, 0, init.stderr); assert.match(init.stdout, /PREPARADO/);
        const imagen = join(out, 'area-prueba.png');
        const dibujar = spawnSync('php', ['-r', '$i=imagecreatetruecolor(600,360);imagefill($i,0,0,imagecolorallocate($i,157,203,183));imagefilledrectangle($i,40,50,560,310,imagecolorallocate($i,208,200,193));imagestring($i,5,130,170,"AREA SINTETICA DE PRUEBA",imagecolorallocate($i,37,70,58));imagepng($i,$argv[1]);imagedestroy($i);', imagen], { encoding: 'utf8' });
        assert.equal(dibujar.status, 0, dibujar.stderr);
        server = spawn('php', ['-S', '127.0.0.1:' + port, '-t', 'public', router], { cwd: root, env, stdio: 'ignore', windowsHide: true });
        const chrome = [process.env.PUPPETEER_EXECUTABLE_PATH, puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => typeof p === 'string' && existsSync(p));
        browser = await puppeteer.launch({ executablePath: chrome, headless: true });
        page = await browser.newPage();
        const errores = [], nativos = [], fallos = [];
        page.on('pageerror', e => errores.push(e.message));
        page.on('dialog', async d => { nativos.push(d.message()); await d.dismiss(); });
        page.on('response', r => { if (r.status() >= 400) fallos.push([r.status(), new URL(r.url()).pathname]); });
        await page.setRequestInterception(true);
        page.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-GERENCIA-QA': token } }) : r.abort());
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
        const click = async (label, scope = 'body') => {
            await page.waitForFunction((l, s) => [...document.querySelectorAll(s + ' button')].some(b => b.innerText.trim() === l && b.getClientRects().length), {}, label, scope);
            const h = await page.evaluateHandle((l, s) => [...document.querySelectorAll(s + ' button')].find(b => b.innerText.trim() === l && b.getClientRects().length), label, scope);
            await h.asElement().click(); await h.dispose();
        };
        const ir = async () => {
            const r = await page.goto(base + '/admin/areas-institucionales', { waitUntil: 'networkidle0' }); assert.equal(r.status(), 200);
            await page.evaluate(() => document.fonts.ready);
        };
        const abrir = async () => {
            await click('Nueva área'); await page.waitForSelector('#area-registro-dialogo [role=dialog]', { visible: true });
            await page.waitForFunction(() => document.getElementById('area-registro-dialogo').contains(document.activeElement));
        };
        const cerrado = () => page.waitForSelector('#area-registro-dialogo', { hidden: true });
        const comprobarImagen = selector => page.waitForFunction(s => {
            const i = document.querySelector(s); return i && i.complete && i.naturalWidth > 0;
        }, {}, selector);

        for (const [width, theme] of [[1440, 'light'], [1280, 'light'], [1024, 'light'], [768, 'light'], [390, 'light'], [390, 'dark']]) {
            await page.evaluateOnNewDocument(t => { if (location.protocol === 'http:') localStorage.setItem('remembermind-theme', t); }, theme);
            await page.setViewport({ width, height: width === 390 ? 844 : 940 }); await ir(); await abrir();
            const estado = await page.$eval('#area-registro-dialogo [role=dialog]', e => {
                const r = e.getBoundingClientRect(); return { fit: r.left >= 0 && r.right <= innerWidth && r.top >= 0 && r.bottom <= innerHeight,
                    overflow: e.scrollWidth > e.clientWidth + 1, input: e.querySelector('input').offsetHeight,
                    font: getComputedStyle(e.querySelector('input')).fontFamily,
                    literal: /\b(?:null|NaN|undefined|Infinity)\b|\[object Object\]/.test(e.innerText) };
            });
            assert.equal(estado.fit, true); assert.equal(estado.overflow, false); assert.ok(estado.input >= 44);
            assert.match(estado.font, /Nunito/); assert.equal(estado.literal, false);
            await page.keyboard.press('Tab'); assert.equal(await page.evaluate(() => document.getElementById('area-registro-dialogo').contains(document.activeElement)), true);
            await page.type('#area-registro-nombre', 'ÁREA QA ' + width + ' ' + theme);
            if (width === 1440) {
                await click('Elegir de Galería', '#area-registro-dialogo');
                await page.waitForSelector('#area-galeria-selector', { visible: true });
                assert.ok(await page.$eval('#area-galeria-selector', e => e.innerText.includes('Todavía no hay fotografías')));
                await page.$eval('#area-registro-archivo', e => e.scrollIntoView({ block: 'center' }));
                const input = await page.$('#area-registro-archivo'); await input.uploadFile(imagen);
                await comprobarImagen('.rm-area-photo__preview img');
                await page.type('#area-registro-texto-alternativo', 'Espacio institucional sintético');
            } else {
                await click('Elegir de Galería', '#area-registro-dialogo');
                await page.waitForSelector('.rm-area-photo__option', { visible: true });
                await comprobarImagen('.rm-area-photo__option img');
                await page.click('.rm-area-photo__option');
                await comprobarImagen('.rm-area-photo__preview img');
            }
            await page.screenshot({ path: join(out, 'formulario-' + width + '-' + theme + '.png'), fullPage: true });
            await click('Cancelar', '#area-registro-dialogo'); await page.waitForSelector('.rm-confirmation', { visible: true });
            await page.click('.swal2-cancel'); await page.waitForSelector('.rm-confirmation', { hidden: true });
            await comprobarImagen('.rm-area-photo__preview img');
            await click('Registrar área', '#area-registro-dialogo'); await cerrado();
            await comprobarImagen('[data-area-card] img');
            console.log(width + ' ' + theme + ': formulario, foto, descarte y registro correctos');
        }
        await ir(); await comprobarImagen('[data-area-card] img');
        const editar = await page.$('button[aria-label="Editar área ÁREA QA 1440 LIGHT"]');
        await editar.click(); await page.waitForSelector('#area-registro-dialogo [role=dialog]', { visible: true });
        await comprobarImagen('.rm-area-photo__preview img');
        await click('Quitar imagen', '#area-registro-dialogo'); await click('Guardar cambios', '#area-registro-dialogo'); await cerrado();
        assert.equal(await page.$$eval('[data-area-card] img', e => e.length), 5, 'Quitar afecta solo al área editada');
        await abrir(); await click('Elegir de Galería', '#area-registro-dialogo');
        await comprobarImagen('.rm-area-photo__option img');
        assert.equal(await page.$$eval('.rm-area-photo__option', e => e.length), 1, 'El archivo sigue en Galería y no se duplica al reutilizarlo');
        assert.deepEqual(errores, []); assert.deepEqual(nativos, []); assert.deepEqual(fallos, []);
        console.log('Capturas sintéticas: ' + out);
    } catch (error) {
        if (page && !page.isClosed()) {
            await page.screenshot({ path: join(out, 'fallo.png'), fullPage: true });
            console.log((await page.$eval('body', e => e.innerText)).slice(-2300));
        }
        console.log('Capturas de diagnóstico: ' + out); throw error;
    } finally {
        await browser?.close(); server?.kill();
        for (const suffix of ['', '-shm', '-wal', '.views', '.storage', '.log']) {
            assert.equal(resolve(dirname(db + suffix)), resolve(tmpdir()));
            rmSync(db + suffix, { recursive: true, force: true });
        }
    }
});
