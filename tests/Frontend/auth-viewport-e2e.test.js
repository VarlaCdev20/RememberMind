import { test } from 'node:test';
import assert from 'node:assert/strict';
import puppeteer from 'puppeteer';
import { mkdir, writeFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';

test('acceso al 100%: controles visibles sin desbordamiento', { skip: process.env.RM_AUTH_VIEWPORT_E2E !== '1', timeout: 90000 }, async () => {
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(p => typeof p === 'string' && existsSync(p));
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        const output = 'output/documentacion-personal-20261105';
        await mkdir(output, { recursive: true });
        const resultados = [];
        for (const [width, height] of [[1440, 900], [1366, 768], [1280, 720], [1024, 768], [768, 1024], [390, 844], [390, 667]]) {
            await page.setViewport({ width, height, deviceScaleFactor: 1 });
            await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle0' });
            const medidas = await page.evaluate(() => {
                const submit = document.querySelector('#panel-login button[type="submit"]').getBoundingClientRect();
                return { overflow: document.documentElement.scrollWidth - innerWidth, submitBottom: submit.bottom, height: innerHeight };
            });
            assert.ok(medidas.overflow <= 1, `${width}: sin desplazamiento horizontal`);
            assert.ok(medidas.submitBottom <= height, `${width}x${height}: acceso visible ${JSON.stringify(medidas)}`);
            await page.screenshot({ path: `${output}/acceso-${width}-${height}.png`, fullPage: true });
            resultados.push({ width, height, ...medidas });
        }
        await writeFile(`${output}/acceso-escala.json`, JSON.stringify(resultados, null, 2));
    } finally {
        await browser.close();
    }
});
