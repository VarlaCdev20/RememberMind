import { test } from 'node:test';
import assert from 'node:assert/strict';
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { readFile, mkdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { randomBytes } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer';

test('guía DCL real: nueve faltantes, teclado y cinco anchos sin consultas SQL', { timeout: 90000 }, async () => {
    const root = fileURLToPath(new URL('../../', import.meta.url));
    const isolated = path.join(tmpdir(), `remembermind-guia-${randomBytes(8).toString('hex')}`);
    await mkdir(path.join(isolated, 'views'), { recursive: true, mode: 0o700 });
    const { stdout: html } = await promisify(execFile)('php', ['tests/Support/RenderPlanCapturaDcl.php'], {
        cwd: root, windowsHide: true, maxBuffer: 1024 * 1024,
        env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:', DB_URL: '',
            APP_CONFIG_CACHE: path.join(isolated, 'config.php'), APP_ROUTES_CACHE: path.join(isolated, 'routes.php'),
            VIEW_COMPILED_PATH: path.join(isolated, 'views'), CACHE_STORE: 'array', SESSION_DRIVER: 'array',
            QUEUE_CONNECTION: 'sync', MAIL_MAILER: 'array' },
    });
    const manifest = JSON.parse(await readFile(path.join(root, 'public/build/manifest.json'), 'utf8'));
    const files = [...new Set(Object.values(manifest).flatMap(entry => [...(entry.css || []), ...(entry.file.endsWith('.css') ? [entry.file] : [])]))];
    assert.ok(files.length, 'Build CSS real disponible');
    const css = (await Promise.all(files.map(file => readFile(path.join(root, 'public/build', file), 'utf8')))).join('\n');
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(),
        'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(file => file && existsSync(file));
    assert.ok(executablePath, 'Navegador de prueba disponible');
    const browser = await puppeteer.launch({ headless: true, executablePath });
    const results = [];
    try {
        const page = await browser.newPage();
        await page.setRequestInterception(true);
        page.on('request', request => request.url().startsWith('data:') ? request.continue() : request.abort());
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
        await mkdir(path.join(root, 'output/dcl-guia-20261031'), { recursive: true });
        for (const width of [1440, 1280, 1024, 768, 390]) {
            await page.setViewport({ width, height: 900 });
            await page.setContent(`<html lang="es"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${css}</style></head><body class="rm-shell"><main class="rm-expert-results" style="padding:16px"><div class="rm-orion-petersen-card">${html}</div></main></body></html>`);
            const main = '[data-dcl-capture-plan] > summary';
            await page.focus(main);
            await page.keyboard.press('Enter');
            assert.equal(await page.$eval('[data-dcl-capture-plan]', el => el.open), true);
            for (const summary of await page.$$('[data-dcl-capture-plan] section summary')) {
                await summary.focus();
                await page.keyboard.press('Enter');
            }
            const state = await page.evaluate(() => ({
                overflow: document.documentElement.scrollWidth - innerWidth,
                sections: document.querySelectorAll('[data-dcl-capture-plan] section').length,
                summaries: [...document.querySelectorAll('summary')].map(el => el.getBoundingClientRect().height),
                links: [...document.querySelectorAll('a')].every(el => el.protocol === 'https:' && el.rel.includes('noopener')),
                codeVisible: /COG-MEM|\bC0[1-5]\b/.test(document.body.innerText),
            }));
            assert.ok(state.overflow <= 1, `${width}: overflow ${state.overflow}`);
            assert.equal(state.sections, 5);
            assert.ok(state.summaries.every(height => height >= 44), `${width}: controles táctiles`);
            assert.equal(state.links, true);
            assert.equal(state.codeVisible, false);
            results.push({ width, ...state });
            await page.screenshot({ path: path.join(root, `output/dcl-guia-20261031/guia-${width}.png`), fullPage: true });
            await page.focus(main);
            await page.keyboard.press('Enter');
            assert.equal(await page.$eval('[data-dcl-capture-plan]', el => el.open), false);
        }
        console.log(JSON.stringify(results));
    } finally {
        await browser.close();
    }
});
