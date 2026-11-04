import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, writeFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { createServer } from 'node:net';
import puppeteer from 'puppeteer';

const root = resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/i, '$1'));
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));

test('Recordarme: casilla real, navegador nuevo solo con cookie persistente y revocación', { timeout: 60000, skip: !executablePath }, async t => {
    const db = join(tmpdir(), `remembermind-recuperacion-${randomBytes(8).toString('hex')}_testing.sqlite`);
    writeFileSync(db, '', { flag: 'wx' });
    t.after(() => { for (const file of [db, `${db}-wal`, `${db}-shm`, `${db}.enlace.json`]) rmSync(file, { force: true }); });
    const port = await new Promise((resolve, reject) => {
        const socket = createServer(); socket.on('error', reject);
        socket.listen(0, '127.0.0.1', () => { const port = socket.address().port; socket.close(() => resolve(port)); });
    });
    const base = `http://127.0.0.1:${port}`;
    const token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV: 'testing', APP_URL: base, RM_RECUPERACION_DB: db, RM_RECUPERACION_TOKEN: token, RM_RECORDAR_ENSAYO: '1' };
    const router = join(root, 'tests/Support/ServidorRecuperacionSintetico.php');
    const preparado = spawnSync('php', [router, '--preparar'], { cwd: root, env, encoding: 'utf8', timeout: 30000 });
    assert.equal(preparado.status, 0, preparado.stderr);
    assert.match(preparado.stdout, /PREPARADO/);
    const server = spawn('php', ['-S', `127.0.0.1:${port}`, '-t', join(root, 'public'), router], { cwd: root, env, windowsHide: true, stdio: 'ignore' });
    t.after(() => server.kill());
    let listo = false;
    for (let i = 0; i < 40; i++) {
        try { const r = await fetch(`${base}/login`, { headers: { 'X-RM-RECUPERACION-QA': token } }); if (r.ok) { listo = true; break; } } catch (error) { if (server.exitCode !== null) throw error; }
        await new Promise(r => setTimeout(r, 100));
    }
    assert.ok(listo);
    const navegadores = [];
    t.after(async () => { for (const browser of navegadores) if (browser.connected) await browser.close(); });
    const abrir = async () => {
        const browser = await puppeteer.launch({ headless: true, executablePath }); navegadores.push(browser);
        const page = await browser.newPage();
        await page.setRequestInterception(true);
        page.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-RECUPERACION-QA': token } }) : r.abort());
        return { browser, page };
    };
    const primera = await abrir();
    await primera.page.goto(`${base}/login`, { waitUntil: 'networkidle0' });
    await primera.page.waitForSelector('#remember_me', { visible: true });
    await primera.page.type('#correo', 'recuperacion@example.test');
    await primera.page.type('#password', 'AnteriorSegura123!');
    await primera.page.click('#remember_me');
    const datos = await primera.page.$eval('form[action$="/login"]', el => Object.fromEntries(new FormData(el)));
    assert.equal(datos.remember, '1');
    // El endpoint usa Fortify real; detener la redirección mantiene la prueba en su router aislado.
    await primera.page.$eval('form[action$="/login"]', async form => {
        await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', redirect: 'manual' });
    });
    const cookies = await primera.browser.defaultBrowserContext().cookies();
    const recuerdo = cookies.find(cookie => cookie.name.startsWith('remember_web_'));
    assert.ok(recuerdo, 'Debe emitirse la cookie al marcar Recordarme');
    assert.equal(recuerdo.httpOnly, true); assert.equal(recuerdo.sameSite, 'Lax');
    assert.ok(Math.abs(recuerdo.expires - (Date.now() / 1000 + 30 * 86400)) < 10);
    await primera.browser.close();

    const segunda = await abrir();
    // Solo se conserva la cookie persistente; no hay cookie de sesión ni contraseña.
    await segunda.browser.defaultBrowserContext().setCookie({ name: recuerdo.name, value: recuerdo.value,
        domain: recuerdo.domain, path: recuerdo.path, expires: recuerdo.expires,
        httpOnly: recuerdo.httpOnly, secure: recuerdo.secure, sameSite: recuerdo.sameSite });
    const restaurada = await segunda.page.goto(`${base}/_ensayo-recordarme`);
    const estado = await restaurada.json();
    assert.equal(estado.autenticado, true); assert.equal(estado.recordado, true);
    const logout = await segunda.page.evaluate(async ({ base, csrf }) => {
        const r = await fetch(`${base}/logout`, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
        return r.status;
    }, { base, csrf: estado.csrf });
    assert.equal(logout, 204);
    assert.equal((await segunda.browser.defaultBrowserContext().cookies()).some(c => c.name === recuerdo.name), false);
    await segunda.browser.close();

    const tercera = await abrir();
    await tercera.browser.defaultBrowserContext().setCookie({ name: recuerdo.name, value: recuerdo.value,
        domain: recuerdo.domain, path: recuerdo.path, expires: recuerdo.expires, httpOnly: true, secure: recuerdo.secure, sameSite: recuerdo.sameSite });
    const revocada = await tercera.page.goto(`${base}/_ensayo-recordarme`);
    assert.equal((await revocada.json()).autenticado, false, 'Reutilizar una cookie revocada debe fallar');
});
