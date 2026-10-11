import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const modalCss = read('resources/frontend/styles/design-system/components/modals.css');
const depthCss = read('resources/frontend/styles/design-system/patterns/depth-canvas.css');

test('las plantillas principales no transforman el contenedor de los modales', () => {
    for (const layout of ['enfermeria', 'sistema']) {
        const source = read(`resources/views/layouts/${layout}.blade.php`);
        const animation = source.split('@keyframes rm-fade-in-up')[1]?.split('.animate-fade-in-up')[0];
        assert.ok(animation, `Falta la animación en ${layout}`);
        assert.doesNotMatch(animation, /transform\s*:/);
    }
});

const chrome = [
    process.env.PUPPETEER_EXECUTABLE_PATH,
    await puppeteer.executablePath(),
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
].find(path => path && existsSync(path));

test('el modal y su fondo cubren la ventana y mantienen visible el pie', { skip: !chrome }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath: chrome });
    try {
        for (const [width, height] of [[1440, 900], [390, 700]]) {
            const page = await browser.newPage();
            await page.setViewport({ width, height });
            await page.setContent(`
                <style>
                    :root { --rm-z-modal-backdrop: 990; --rm-z-modal: 1000; }
                    body { margin: 0; }
                    .topbar { position: sticky; top: 0; z-index: 30; height: 90px; background: white; }
                    .rm-depth-canvas { height: 470px; }
                    .animate-fade-in-up { animation: rm-fade-in-up 250ms forwards; }
                    @keyframes rm-fade-in-up { from { opacity: 0; } to { opacity: 1; } }
                    .jetstream-modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; overflow-y: auto; padding: 16px; background: rgb(0 0 0 / 40%); }
                    .jetstream-modal__panel { width: min(840px, 100%); background: white; }
                    .tall-content { height: 1200px; }
                    .modal-footer { height: 64px; }
                </style>
                <div class="rm-shell"><header class="topbar">Barra</header>
                    <main data-rm-main class="rm-depth-canvas">
                        <div class="animate-fade-in-up"><div class="jetstream-modal">
                            <div class="jetstream-modal__panel"><div><div class="tall-content">Formulario</div></div><footer class="modal-footer">Guardar</footer></div>
                        </div></div>
                    </main>
                </div>`);
            await page.addStyleTag({ content: depthCss + modalCss });
            await new Promise(resolve => setTimeout(resolve, 300));
            const result = await page.evaluate(() => {
                const overlay = document.querySelector('.jetstream-modal').getBoundingClientRect();
                const panel = document.querySelector('.jetstream-modal__panel').getBoundingClientRect();
                const footer = document.querySelector('.modal-footer').getBoundingClientRect();
                const content = document.querySelector('.jetstream-modal__panel > :first-child');
                return {
                    overlayHeight: overlay.height,
                    topLayer: document.elementFromPoint(2, 2)?.className,
                    bottomLayer: document.elementFromPoint(2, innerHeight - 2)?.className,
                    panelTop: panel.top,
                    panelBottom: panel.bottom,
                    footerBottom: footer.bottom,
                    scrollable: content.scrollHeight > content.clientHeight,
                };
            });
            assert.equal(result.overlayHeight, height);
            assert.equal(result.topLayer, 'jetstream-modal');
            assert.ok(result.bottomLayer === 'jetstream-modal' || result.bottomLayer === 'modal-footer');
            assert.ok(result.panelTop >= 0 && result.panelBottom <= height);
            assert.ok(result.footerBottom <= height);
            assert.equal(result.scrollable, true);
            await page.close();
        }

        const customPage = await browser.newPage();
        await customPage.setViewport({ width: 1440, height: 900 });
        await customPage.setContent(`
            <style>
                :root { --rm-z-modal-backdrop: 990; }
                body { margin: 0; }
                .topbar { position: sticky; top: 0; z-index: 30; height: 90px; background: white; }
                .rm-depth-canvas { height: 470px; }
                .fixed.inset-0.z-50 { position: fixed; inset: 0; z-index: 50; background: rgb(0 0 0 / 40%); }
            </style>
            <div class="rm-shell"><header class="topbar">Barra</header>
                <main data-rm-main class="rm-depth-canvas"><div><div class="fixed inset-0 z-50">Otro modal</div></div></main>
            </div>`);
        await customPage.addStyleTag({ content: depthCss });
        assert.equal(await customPage.evaluate(() => document.elementFromPoint(2, 2)?.className), 'fixed inset-0 z-50');
        assert.equal(await customPage.evaluate(() => document.elementFromPoint(2, 898)?.className), 'fixed inset-0 z-50');
        await customPage.close();
    } finally {
        await browser.close();
    }
});
