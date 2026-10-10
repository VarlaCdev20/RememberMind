import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const effects = readFileSync(new URL('resources/frontend/scripts/components/efectos-ambientales.js', root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

test('la luz sigue al mouse sin bloquear controles y desaparece con movimiento reducido', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        // La fixture comprueba primero el estado animado, independientemente de Windows.
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'no-preference' }]);
        await page.setViewport({ width: 1280, height: 800 });
        await page.setContent(`<html><head><style>${css}</style></head><body class="rm-shell" data-role="superadmin"><button id="control" style="position:fixed;left:300px;top:240px;z-index:1">Acción</button><div class="rm-cursor-glow" aria-hidden="true"></div></body></html>`);
        await page.evaluate(source => {
            const start = new Function(source.replace('export function', 'function') + '\nreturn iniciarEfectosAmbientales;')();
            start({ init() {} });
        }, effects);
        await page.mouse.move(320, 260);
        await new Promise(resolve => setTimeout(resolve, 200));
        const visible = await page.evaluate(() => {
            const glow = document.querySelector('.rm-cursor-glow');
            const style = getComputedStyle(glow);
            return { left: style.left, top: style.top, opacity: style.opacity, pointerEvents: style.pointerEvents, target: document.elementFromPoint(320, 260)?.id };
        });
        assert.equal(visible.left, '320px');
        assert.equal(visible.top, '260px');
        assert.equal(visible.opacity, '1');
        assert.equal(visible.pointerEvents, 'none');
        assert.equal(visible.target, 'control');

        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
        assert.equal(await page.$eval('.rm-cursor-glow', glow => getComputedStyle(glow).display), 'none');
    } finally { await browser.close(); }
});
