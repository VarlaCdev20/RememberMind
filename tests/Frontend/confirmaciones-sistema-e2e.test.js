import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, readdirSync, writeFileSync, rmSync } from 'node:fs';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { test } from 'node:test';
import puppeteer from 'puppeteer';

const root = resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/i, '$1'));
test('Las vistas y scripts del sistema usan avisos compartidos, sin diálogos nativos', () => {
    const revisar = dir => {
        for (const entrada of readdirSync(dir, { withFileTypes: true })) {
            const p = join(dir, entrada.name);
            if (entrada.isDirectory()) revisar(p);
            else if (/\.(?:blade\.php|js|ts)$/.test(p) && !p.endsWith('.min.js')) {
                const texto = readFileSync(p, 'utf8');
                assert.doesNotMatch(texto, /wire:confirm|\b(?:window\.)?(?:confirm|alert|prompt)\s*\(/, p);
            }
        }
    };
    revisar(join(root, 'app')); revisar(join(root, 'resources'));
});

test('Confirmaciones globales: botones, formularios, avisos, motivos y navegación', {
    skip: process.env.RM_CONFIRMACIONES_SISTEMA_E2E !== '1', timeout: 240000,
}, async () => {
    const id = randomBytes(8).toString('hex'), db = join(tmpdir(), 'remembermind-gerencia-' + id + '_testing.sqlite');
    assert.equal(resolve(dirname(db)), resolve(tmpdir())); writeFileSync(db, '', { flag: 'wx' });
    const port = await new Promise(r => { const s = createServer().listen(0, '127.0.0.1', () => { const p = s.address().port; s.close(() => r(p)); }); });
    const base = 'http://127.0.0.1:' + port, token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_GERENCIA_QA_DB: db, RM_GERENCIA_QA_TOKEN: token,
        RM_GERENCIA_QA_ARCHIVOS: '1', RM_GERENCIA_QA_AMPLIACION: '1' };
    const router = join(root, 'tests/Support/ServidorGerenciaSintetico.php');
    const out = join(tmpdir(), 'remembermind-confirmaciones-sistema-' + id); mkdirSync(out);
    let server, browser, page;
    try {
        const init = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 45000 });
        assert.equal(init.status, 0, init.stderr);
        server = spawn('php', ['-S', '127.0.0.1:' + port, '-t', 'public', router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
        const chrome = [process.env.PUPPETEER_EXECUTABLE_PATH, puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => typeof p === 'string' && existsSync(p));
        browser = await puppeteer.launch({ executablePath: chrome, headless: true }); page = await browser.newPage();
        const errores = [], nativos = [], llamadas = [];
        page.on('pageerror', e => errores.push(e.message)); page.on('dialog', async d => { nativos.push(d.message()); await d.dismiss(); });
        await page.setRequestInterception(true);
        page.on('request', r => {
            if (r.method() === 'POST' && r.url().endsWith('/update')) {
                for (const c of JSON.parse(r.postData() || '{}').components || []) for (const a of c.calls || []) llamadas.push(a.method);
            }
            return r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-GERENCIA-QA': token } }) : r.abort();
        });
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
        const ir = async path => { assert.equal((await page.goto(base + path, { waitUntil: 'networkidle0' })).status(), 200); await page.evaluate(() => document.fonts.ready); };
        const click = async (label, scope = 'body') => {
            await page.waitForFunction((l, s) => [...document.querySelectorAll(s + ' button')].some(b => b.innerText.trim() === l && b.getClientRects().length), {}, label, scope);
            const el = await page.evaluateHandle((l, s) => [...document.querySelectorAll(s + ' button')].find(b => b.innerText.trim() === l && b.getClientRects().length), label, scope);
            await el.asElement().click(); await el.dispose();
        };
        const popup = async () => {
            await page.waitForSelector('.rm-confirmation', { visible: true });
            await page.waitForFunction(() => {
                const r = document.querySelector('.rm-confirmation')?.getBoundingClientRect();
                return r && Math.abs((r.left + r.right) / 2 - innerWidth / 2) < 2 && Math.abs((r.top + r.bottom) / 2 - innerHeight / 2) < 2;
            });
        };
        const cerrado = () => page.waitForSelector('.rm-confirmation', { hidden: true });
        const contar = () => llamadas.filter(a => a === 'toggleEstado').length;
        await ir('/admin/areas-institucionales'); await click('Nueva área');
        await page.waitForSelector('#area-registro-nombre', { visible: true }); await page.type('#area-registro-nombre', 'ÁREA DE CONFIRMACIONES');
        await click('Registrar área', '#area-registro-dialogo'); await page.waitForSelector('#area-registro-dialogo', { hidden: true });
        const selector = '[data-area-card] button[data-rm-confirm-subject="ÁREA DE CONFIRMACIONES"]';
        await page.evaluate(() => { window.confirmarQaOriginal = window.rmConfirmar; window.rmConfirmar = undefined; });
        await page.click(selector); await page.waitForSelector('#rm-confirmation-unavailable', { visible: true });
        assert.equal(contar(), 0, 'Un fallo de carga no permite enviar una acción sin confirmar');
        await page.evaluate(() => { window.rmConfirmar = window.confirmarQaOriginal; delete window.confirmarQaOriginal; });
        for (const [width, theme] of [[1440, 'light'], [1280, 'light'], [1024, 'light'], [768, 'light'], [390, 'light'], [390, 'dark']]) {
            await page.evaluateOnNewDocument(t => { if (location.protocol === 'http:') localStorage.setItem('remembermind-theme', t); }, theme);
            await page.setViewport({ width, height: width === 390 ? 844 : 940 }); await ir('/admin/areas-institucionales');
            const antes = contar(); await page.click(selector); await popup();
            const estado = await page.$eval('.rm-confirmation', e => {
                const r = e.getBoundingClientRect(); return { fit: r.left >= 0 && r.right <= innerWidth && r.top >= 0 && r.bottom <= innerHeight,
                    centered: Math.abs((r.left + r.right) / 2 - innerWidth / 2) < 2 && Math.abs((r.top + r.bottom) / 2 - innerHeight / 2) < 2,
                    font: getComputedStyle(e).fontFamily, button: e.querySelector('.swal2-confirm').offsetHeight };
            });
            assert.equal(estado.fit, true); assert.equal(estado.centered, true); assert.match(estado.font, /Nunito/); assert.ok(estado.button >= 44);
            await page.screenshot({ path: join(out, 'area-' + width + '-' + theme + '.png') });
            await page.click('.swal2-cancel'); await cerrado(); assert.equal(contar(), antes);
            await page.click(selector); await popup(); await page.keyboard.press('Escape'); await cerrado(); assert.equal(contar(), antes);
        }
        await page.click(selector); await popup(); await page.click('.swal2-confirm'); await cerrado();
        await page.waitForFunction(s => document.querySelector(s)?.title === 'Activar Área', {}, selector);
        assert.equal(contar(), 1, 'La acción de estado se ejecuta una sola vez');

        // La validación HTML se conserva y un envío confirmado se reanuda una sola vez.
        await page.evaluate(() => {
            const form = document.createElement('form'); form.id = 'qa-confirm-form';
            form.innerHTML = '<label for="qa-nombre">Dato de prueba</label><input id="qa-nombre" required><button type="submit" data-rm-confirm="Confirmar envío de prueba">Enviar prueba</button>';
            form.addEventListener('submit', e => { e.preventDefault(); window.enviosQa = (window.enviosQa || 0) + 1; });
            document.querySelector('main').prepend(form);
        });
        await click('Enviar prueba'); assert.equal(await page.$('.rm-confirmation'), null);
        await page.type('#qa-nombre', 'Dato sintético'); await page.keyboard.press('Enter'); await popup();
        assert.equal(await page.evaluate(() => window.enviosQa || 0), 0); await page.click('.swal2-cancel'); await cerrado();
        await click('Enviar prueba'); await popup(); await page.click('.swal2-confirm'); await cerrado();
        assert.equal(await page.evaluate(() => window.enviosQa), 1);
        await page.evaluate(() => document.getElementById('qa-confirm-form').remove());

        // Los avisos y la captura de motivos comparten la ventana y respetan Cancelar.
        await page.evaluate(() => { void window.rmAvisar({ title: 'Aviso de prueba', text: 'Contenido sintético.' }); }); await popup();
        assert.equal(await page.$eval('.swal2-cancel', e => e.offsetParent === null), true); await page.click('.swal2-confirm'); await cerrado();
        await page.evaluate(() => { void window.rmAvisar({ title: 'Operación completada', text: 'Contenido sintético.', icon: 'success' }); }); await popup();
        assert.ok(await page.$('.rm-confirmation--success')); await page.click('.swal2-confirm'); await cerrado();
        await page.evaluate(() => { window.motivoQa = undefined; void window.rmPedirTexto({ title: 'Motivo', inputLabel: 'Motivo de prueba', text: 'Datos sintéticos.', inputValidator: v => v.trim().length < 10 ? 'Escribe al menos 10 caracteres.' : undefined }).then(v => window.motivoQa = v); });
        await popup(); await page.click('.swal2-confirm'); await page.waitForSelector('.swal2-validation-message', { visible: true });
        await page.type('.swal2-textarea', 'Motivo suficiente'); await page.click('.swal2-confirm'); await cerrado();
        assert.equal(await page.evaluate(() => window.motivoQa), 'Motivo suficiente');

        // Un aviso también debe quedar por encima de paneles en la capa nativa.
        await page.evaluate(() => {
            const popover = document.createElement('div'); popover.id = 'qa-popover'; popover.setAttribute('popover', 'manual');
            popover.innerHTML = '<button id="qa-popover-button" data-rm-confirm="Confirmar dentro de ventana">Acción en ventana</button>';
            document.body.append(popover); popover.showPopover();
        });
        await page.click('#qa-popover-button'); await popup();
        assert.equal(await page.$eval('.rm-confirmation', e => { const r = e.getBoundingClientRect(); return Boolean(document.elementFromPoint((r.left + r.right) / 2, (r.top + r.bottom) / 2)?.closest('.rm-confirmation')); }), true);
        await page.click('.swal2-cancel'); await cerrado(); await page.evaluate(() => document.getElementById('qa-popover').remove());

        await ir('/admin/galeria'); await click('Subir fotografías');
        await page.waitForSelector('input[wire\\:model="titulo"]', { visible: true }); await page.type('input[wire\\:model="titulo"]', 'Sin guardar');
        await click('Cancelar'); await popup(); await page.click('.swal2-cancel'); await cerrado();
        assert.equal(await page.$eval('input[wire\\:model="titulo"]', e => e.value), 'Sin guardar');

        await ir('/admin/areas-institucionales');
        await page.evaluate(base => {
            document.addEventListener('livewire:navigate', event => window.rmConfirmarSalida(event, 'Captura de prueba sin guardar.'));
            window.Livewire.navigate(base + '/dashboard');
        }, base);
        await popup(); await page.click('.swal2-cancel'); await cerrado(); assert.match(page.url(), /areas-institucionales$/);
        await page.evaluate(base => window.Livewire.navigate(base + '/dashboard'), base); await popup();
        await page.click('.swal2-confirm'); await page.waitForFunction(() => location.pathname === '/dashboard');
        assert.deepEqual(nativos, []); assert.deepEqual(errores, []);
        console.log('Capturas sintéticas: ' + out);
    } catch (error) {
        if (page && !page.isClosed()) { await page.screenshot({ path: join(out, 'fallo.png') }); console.log((await page.$eval('body', e => e.innerText)).slice(-1600)); }
        console.log('Diagnóstico: ' + out); throw error;
    } finally {
        await browser?.close(); server?.kill();
        for (const suffix of ['', '-shm', '-wal', '.views', '.storage', '.log']) { assert.equal(resolve(dirname(db + suffix)), resolve(tmpdir())); rmSync(db + suffix, { recursive: true, force: true }); }
    }
});
