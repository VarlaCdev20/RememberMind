import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, writeFileSync, readFileSync, rmSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { createServer } from 'node:net';
import puppeteer from 'puppeteer';

const root = resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/i, '$1'));
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));

test('recuperación real: solicitar, corregir, cambiar y volver al login en cinco anchos', { timeout: 120000, skip: !executablePath }, async t => {
    const db = join(tmpdir(), `remembermind-recuperacion-${randomBytes(8).toString('hex')}_testing.sqlite`);
    writeFileSync(db, '', { flag: 'wx' });
    t.after(() => { for (const file of [db, `${db}-wal`, `${db}-shm`, `${db}.enlace.json`]) rmSync(file, { force: true }); });
    const port = await new Promise((resolve, reject) => {
        const socket = createServer(); socket.on('error', reject);
        socket.listen(0, '127.0.0.1', () => { const port = socket.address().port; socket.close(() => resolve(port)); });
    });
    const base = `http://127.0.0.1:${port}`;
    const tokenEnsayo = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_RECUPERACION_DB: db, RM_RECUPERACION_TOKEN: tokenEnsayo };
    const router = join(root, 'tests/Support/ServidorRecuperacionSintetico.php');
    const preparado = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 30000 });
    assert.equal(preparado.status, 0, preparado.stderr);
    assert.match(preparado.stdout, /PREPARADO/);
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', join(root, 'public'), router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    t.after(() => server.kill());
    let ready = false;
    for (let i = 0; i < 40; i++) {
        try { const r = await fetch(`${base}/forgot-password`, { headers: { 'X-RM-RECUPERACION-QA': tokenEnsayo } }); if (r.ok) { ready = true; break; } } catch (e) { if (server.exitCode !== null) throw e; }
        await new Promise(r => setTimeout(r, 100));
    }
    assert.ok(ready, 'No inició el servidor de pruebas');
    const browser = await puppeteer.launch({ headless: true, executablePath });
    t.after(() => browser.close());
    const page = await browser.newPage();
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    const jsErrors = []; page.on('pageerror', error => jsErrors.push(error.message));
    await page.setRequestInterception(true);
    page.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-RECUPERACION-QA': tokenEnsayo } }) : r.abort());
    const output = join(root, 'output/recuperacion-contrasena-20261026'); mkdirSync(output, { recursive: true });
    const go = async path => {
        const r = await page.goto(path.startsWith(base) ? path : base + path, { waitUntil: 'networkidle0' });
        assert.equal(r.status(), 200);
        await page.waitForFunction(() => window.Alpine && window.Livewire);
        await page.waitForFunction(() => [...document.querySelectorAll('main img')].every(img => img.complete && img.naturalWidth > 0));
        await page.evaluate(() => document.fonts.ready);
    };
    const submit = async selector => Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click(selector)]);
    await page.setViewport({ width: 390, height: 900 });
    await go('/login');
    await page.type('#correo', 'recuperacion@example.test');
    await page.type('#password', 'Incorrecta123!');
    await submit('form[action$="/login"] button[type="submit"]');
    await page.waitForFunction(() => [...document.querySelectorAll('[role="alert"]')].filter(el => el.getClientRects().length).length === 1);
    assert.match(await page.$eval('main', el => el.innerText), /Revisa tus datos de acceso/);
    await page.screenshot({ path: join(output, 'acceso-error-390.png'), fullPage: true });
    const abrirRecuperacion = async () => {
        const actual = page.url();
        await page.evaluate(() => { window.__rmMismaPagina = true; });
        await page.focus('button[aria-controls="panel-recuperacion"]');
        await page.keyboard.press('Enter');
        await page.waitForSelector('#recover_correo', { visible: true });
        await page.waitForFunction(() => document.activeElement?.id === 'recover_correo');
        assert.equal(page.url(), actual);
        assert.equal(await page.evaluate(() => window.__rmMismaPagina), true);
        assert.equal(await page.$('[role="tablist"]'), null, 'No debe mostrarse una barra de pestañas');
    };
    const comprobarLayout = async (width, theme, panel) => {
        const estado = await page.evaluate(() => {
            const form = document.querySelector('form[action$="/forgot-password"]') ?? document.querySelector('[data-reset-password-form]');
            const activos = [...document.querySelectorAll('main button, main a.rm-btn')].filter(el => el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden');
            return {
                overflow: document.documentElement.scrollWidth > innerWidth + 1,
                pequeños: activos.filter(el => { const b = el.getBoundingClientRect(); return b.height < 43; }).map(el => el.textContent.trim()),
                texto: document.querySelector('main').innerText,
                avisosFlotantes: document.querySelectorAll('main [class*="fixed"]').length,
                labels: [...form.querySelectorAll('input:not([type="hidden"])')].every(el => el.labels.length > 0),
                tipografia: {
                    titulo: getComputedStyle([...document.querySelectorAll('main h1, main h2')].find(el => el.getClientRects().length)).fontFamily,
                    campo: getComputedStyle([...document.querySelectorAll('main input[type="email"]')].find(el => el.getClientRects().length)).fontFamily,
                    fuenteLocal: [...document.fonts].some(font => font.family.includes('Plus Jakarta Sans') && font.status === 'loaded'),
                },
                botonesIntegrados: [...document.querySelectorAll('.rm-auth-control__toggle')].filter(el => el.getClientRects().length).every(el => {
                    const boton = el.getBoundingClientRect(), campo = el.parentElement.querySelector('input').getBoundingClientRect();
                    return boton.left >= campo.left && boton.right <= campo.right && boton.top >= campo.top && boton.bottom <= campo.bottom;
                }),
                contraste: [...document.querySelectorAll('main h1, main h2, main .rm-label, main .rm-auth-note, main button.rm-btn, main .rm-auth-control__toggle')]
                    .filter(el => el.getClientRects().length).map(el => {
                        const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
                        const ctx = canvas.getContext('2d');
                        const capas = []; for (let padre = el; padre; padre = padre.parentElement) capas.unshift(padre);
                        ctx.fillStyle = 'white'; ctx.fillRect(0, 0, 1, 1);
                        for (const capa of capas) { ctx.fillStyle = getComputedStyle(capa).backgroundColor; ctx.fillRect(0, 0, 1, 1); }
                        const bg = [...ctx.getImageData(0, 0, 1, 1).data];
                        ctx.fillStyle = getComputedStyle(el).color; ctx.fillRect(0, 0, 1, 1);
                        const fg = [...ctx.getImageData(0, 0, 1, 1).data];
                        const luz = rgb => rgb.slice(0, 3).map(n => { const c = n / 255; return c <= .04045 ? c / 12.92 : ((c + .055) / 1.055) ** 2.4; }).reduce((sum, v, i) => sum + v * [.2126, .7152, .0722][i], 0);
                        const a = luz(bg), b = luz(fg);
                        return { texto: el.textContent.trim(), bg, fg, color: getComputedStyle(el).color, fondo: getComputedStyle(el).backgroundColor, ratio: (Math.max(a, b) + .05) / (Math.min(a, b) + .05), minimo: /^H[12]$/.test(el.tagName) || !el.textContent.trim() ? 3 : 4.5 };
                    }),
            };
        });
        assert.equal(estado.overflow, false, `${panel} ${theme} ${width}: overflow`);
        assert.deepEqual(estado.pequeños, [], `${panel} ${theme} ${width}: controles pequeños`);
        assert.equal(estado.avisosFlotantes, 0);
        assert.equal(estado.labels, true);
        assert.match(estado.tipografia.titulo, /^"?Plus Jakarta Sans/);
        assert.match(estado.tipografia.campo, /^"?Inter/);
        assert.equal(estado.tipografia.fuenteLocal, true, 'La tipografía debe cargar desde los assets locales');
        assert.equal(estado.botonesIntegrados, true, 'Mostrar contraseña debe quedar dentro del campo');
        assert.deepEqual(estado.contraste.filter(c => c.ratio < c.minimo), [], `${panel} ${theme} ${width}: contraste`);
        assert.doesNotMatch(estado.texto, /\b(?:undefined|NaN|Infinity|null)\b|\[object Object\]/);
        await page.screenshot({ path: join(output, `${panel}-${theme}-${width}.png`), fullPage: true });
    };
    for (const theme of ['light', 'dark']) {
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 900 });
            await go('/login');
            await page.evaluate(theme => window.RememberMindTheme.apply(theme), theme);
            await comprobarLayout(width, theme, 'acceso');
            assert.deepEqual(await page.$eval('#acceso-titulo', el => ({ alineacion: getComputedStyle(el).textAlign, caja: getComputedStyle(el).textTransform })), { alineacion: 'center', caja: 'uppercase' });
            const jerarquia = await page.$eval('#panel-login > .rm-auth-heading', el => ({
                titulo: parseFloat(getComputedStyle(el.querySelector('h1')).fontSize),
                descripcion: parseFloat(getComputedStyle(el.querySelector('p')).fontSize),
                alineacion: getComputedStyle(el.querySelector('p')).textAlign,
            }));
            assert.equal(jerarquia.alineacion, 'center'); assert.ok(jerarquia.titulo > jerarquia.descripcion);
            if (width >= 768) {
                const editorial = await page.evaluate(() => ({
                    titulo: getComputedStyle(document.querySelector('.rm-auth-brand__message h2')).fontFamily,
                    tituloPartes: document.querySelector('.rm-auth-brand__message h2').querySelectorAll('em, span').length,
                    tituloCursiva: getComputedStyle(document.querySelector('.rm-auth-brand__message h2')).fontStyle,
                    tituloAlineacion: getComputedStyle(document.querySelector('.rm-auth-brand__message h2')).textAlign,
                    cursiva: getComputedStyle(document.querySelector('.rm-auth-brand__message p')).fontStyle,
                    mayusculas: getComputedStyle(document.querySelector('.rm-auth-brand__identity span')).textTransform,
                }));
                assert.match(editorial.titulo, /Plus Jakarta Sans/); assert.equal(editorial.tituloPartes, 0);
                assert.equal(editorial.tituloCursiva, 'italic'); assert.equal(editorial.tituloAlineacion, 'center');
                assert.equal(editorial.cursiva, 'italic'); assert.equal(editorial.mayusculas, 'uppercase');
            }
            await abrirRecuperacion();
            await comprobarLayout(width, theme, 'recuperacion');
            await page.click('button.rm-auth-back');
            await page.waitForFunction(() => document.activeElement?.id === 'correo');
        }
    }
    await page.setViewport({ width: 390, height: 1000 });
    await go('/login');
    await page.type('#correo', 'recuperacion@example.test');
    await page.type('#password', 'SeConserva123!');
    await abrirRecuperacion();
    const respuestaRecuperacion = page.waitForResponse(r => r.url().endsWith('/forgot-password') && r.request().method() === 'POST');
    await page.click('form[action$="/forgot-password"] button[type="submit"]');
    assert.equal((await respuestaRecuperacion).status(), 200);
    await page.waitForFunction(() => [...document.querySelectorAll('p')].some(el => el.textContent.trim() === 'Solicitud recibida' && el.getClientRects().length));
    assert.equal(new URL(page.url()).pathname, '/login');
    assert.equal(await page.evaluate(() => window.__rmMismaPagina), true, 'Enviar recuperación no debe recargar la página');
    assert.equal(await page.$eval('#password', el => el.value), 'SeConserva123!');
    await page.click('.rm-auth-help summary');
    await page.waitForFunction(() => document.querySelector('.rm-auth-help').open === true);
    await page.screenshot({ path: join(output, 'solicitud-desde-login-390.png'), fullPage: true });
    await page.click('button.rm-auth-back');
    await page.waitForSelector('#password', { visible: true });
    assert.equal(await page.$eval('#password', el => el.value), 'SeConserva123!');

    // La fotografía y el formulario permanecen fijos con movimiento normal o reducido.
    await page.setViewport({ width: 1440, height: 900 });
    await go('/login');
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'no-preference' }]);
    const posicion = async selector => page.$eval(selector, el => { const r = el.getBoundingClientRect(); return [r.x, r.y, r.width, r.height]; });
    const campoAntes = await posicion('#correo');
    const fotoAntes = await posicion('[data-auth-photo]');
    await page.mouse.move(1100, 620);
    await new Promise(r => setTimeout(r, 250));
    assert.equal(await page.$eval('[data-auth-photo]', el => getComputedStyle(el).transform), 'none');
    assert.deepEqual(await posicion('[data-auth-photo]'), fotoAntes);
    assert.deepEqual(await posicion('#correo'), campoAntes);
    await abrirRecuperacion();
    await page.screenshot({ path: join(output, 'recuperacion-foto-fija-1440.png'), fullPage: true });
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    await page.waitForFunction(() => matchMedia('(prefers-reduced-motion: reduce)').matches && getComputedStyle(document.querySelector('[data-auth-photo]')).transform === 'none');
    assert.equal(await page.$eval('[data-auth-photo]', el => getComputedStyle(el).transform), 'none');
    await page.mouse.move(500, 500);
    assert.equal(await page.$eval('.rm-auth-brand__message', el => getComputedStyle(el).transform), 'none');
    for (const theme of ['light', 'dark']) {
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            await go('/forgot-password');
            await page.evaluate(theme => window.RememberMindTheme.apply(theme), theme);
            await comprobarLayout(width, theme, 'solicitud-directa');
        }
    }
    await page.type('#recuperar-correo', 'recuperacion@example.test');
    await submit('form button[type="submit"]');
    await page.waitForSelector('[data-recovery-status]');
    assert.match(await page.$eval('[data-recovery-status]', el => el.innerText), /Spam/);
    const enlace = JSON.parse(readFileSync(`${db}.enlace.json`, 'utf8')).url;
    for (const theme of ['light', 'dark']) {
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 1000 });
            await go(enlace);
            await page.evaluate(theme => window.RememberMindTheme.apply(theme), theme);
            await comprobarLayout(width, theme, 'nueva-contrasena');
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, `${theme} ${width}: overflow`);
            assert.equal(await page.$$eval('input:not([type="hidden"])', els => els.filter(e => e.labels.length).length), 3);
            await page.click('button[aria-controls="reset-password"]');
            assert.equal(await page.$eval('#reset-password', el => el.type), 'text');
            await page.click('button[aria-controls="reset-password"]');
            assert.equal(await page.$eval('#reset-password', el => el.type), 'password');
            await page.screenshot({ path: join(output, `${theme}-${width}.png`), fullPage: true });
        }
    }
    await page.type('#reset-password', 'NuevaSegura456!');
    await page.type('#reset-confirmation', 'Distinta789!');
    await submit('form button[type="submit"]');
    assert.match(await page.$eval('#reset-password-error', el => el.innerText), /confirmación|coincid/i);
    assert.equal(await page.$eval('#reset-password', el => el.value), '');
    await page.type('#reset-password', 'NuevaSegura456!');
    await page.type('#reset-confirmation', 'NuevaSegura456!');
    // Una marca antigua de recuperación no debe esconder la confirmación en otro panel.
    await page.evaluate(() => sessionStorage.setItem('rm-password-recovery-pending', '1'));
    await submit('form button[type="submit"]');
    assert.equal(new URL(page.url()).pathname, '/login');
    await page.waitForSelector('[data-password-reset-confirmation]', { visible: true });
    assert.match(await page.$eval('[data-password-reset-confirmation]', el => el.innerText), /Ya puedes iniciar sesión/);
    await page.screenshot({ path: join(output, 'confirmacion-login-390.png'), fullPage: true });
    await go(enlace);
    await page.waitForSelector('[data-reset-link-invalid]');
    assert.equal(await page.$('[data-reset-password-form]'), null);
    await page.screenshot({ path: join(output, 'enlace-utilizado-390.png'), fullPage: true });
    await page.click('a[href$="/forgot-password"]');
    await page.waitForSelector('#recuperar-correo');
    assert.deepEqual(jsErrors, []);
});
