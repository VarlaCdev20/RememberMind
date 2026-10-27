import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
const codigo = readFileSync(new URL('../../resources/frontend/scripts/modules/correo-envios.js', import.meta.url), 'utf8').replace(/^export /gm, '');
const swal = new URL('../../node_modules/sweetalert2/dist/sweetalert2.all.min.js', import.meta.url);

test('modal móvil pasa de pendiente a aceptado, evita doble envío y deja de consultar al cerrar', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        await page.setRequestInterception(true);
        page.on('request', request => request.respond({ status: 200, contentType: 'text/html', body: '<!doctype html><html><body></body></html>' }));
        await page.goto('http://remembermind-correo.test');
        await page.setViewport({ width: 390, height: 844 });
        await page.setContent('<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="sintetico"></head><body><button id="solicitar">Solicitar envío</button></body></html>');
        await page.addScriptTag({ path: swal.pathname.replace(/^\/([A-Z]:)/i, '$1') });
        await page.addScriptTag({ content: codigo });
        await page.evaluate(() => {
            window.postCount = 0; window.getCount = 0;
            window.fetch = async (url, options) => {
                if (options.method === 'POST') {
                    window.postCount++;
                    return { ok: true, json: async () => ({ success: true, estado: 'PENDIENTE', titulo: 'Pendiente', message: 'La solicitud está en cola.', terminado: false, estado_url: '/estado' }) };
                }
                window.getCount++;
                return { ok: true, json: async () => ({ success: true, estado: 'ACEPTADO', titulo: 'Aceptado por el servidor', message: 'El servidor aceptó el correo. Esto no confirma su recepción ni su lectura.', terminado: true }) };
            };
            window.RMCorreo.enviar('/solicitar');
            window.RMCorreo.enviar('/solicitar');
        });
        await page.waitForFunction(() => document.querySelector('.swal2-title')?.textContent === 'Aceptado por el servidor');
        const result = await page.evaluate(() => ({ post: window.postCount, get: window.getCount,
            overflow: document.documentElement.scrollWidth > innerWidth + 1,
            text: document.querySelector('.swal2-html-container').textContent }));
        assert.equal(result.post, 1);
        assert.equal(result.get, 1);
        assert.equal(result.overflow, false);
        assert.match(result.text, /no confirma su recepción/);
        await page.click('.swal2-confirm');
        await page.waitForFunction(() => !document.querySelector('.swal2-container'));
        await new Promise(resolve => setTimeout(resolve, 2100));
        assert.equal(await page.evaluate(() => window.getCount), 1);
    } finally {
        await browser.close();
    }
});
