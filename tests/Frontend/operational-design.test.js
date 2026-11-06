import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import { mkdir, writeFile } from 'node:fs/promises';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = read(`public/build/${manifest['resources/frontend/styles/app.css'].file}`);
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
const roles = ['superadmin', 'manager', 'admin', 'doctor', 'nursing', 'psychology', 'nutrition', 'physio', 'pedagogy', 'family'];
const fixture = (role, theme) => `<!doctype html><html data-theme="${theme}"><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${css}</style></head><body class="rm-shell" data-role="${role}"><main data-rm-main style="padding:16px;min-height:100vh"><header class="rm-collection-header"><div class="rm-collection-header__identity"><div class="rm-collection-header__copy"><h1>Directorio institucional</h1><p>Consulta y seguimiento del equipo</p></div></div><div class="rm-collection-header__actions"><button class="rm-btn rm-btn-primary">Registrar</button></div></header><section class="rm-filter-bar" style="margin:12px 0;display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,180px),1fr))"><div><label class="rm-label" for="buscar">Buscar persona</label><input id="buscar" class="rm-input" placeholder="Nombre o correo"></div><div><label class="rm-label" for="estado">Estado registrado</label><select class="rm-select" id="estado"><option>Todos los estados</option><option>Activo</option></select></div></section><div class="rm-table-surface"><table class="rm-table" style="table-layout:fixed;width:100%"><thead><tr><th>Persona</th><th>Estado</th><th>Acciones</th></tr></thead><tbody><tr><td>Persona de prueba</td><td><span class="rm-badge rm-badge-success">Activo</span></td><td><button class="rm-btn rm-btn-secondary">Ver ficha</button></td></tr><tr><td>Otra persona</td><td><span class="rm-badge rm-badge-danger">Alerta</span></td><td><button class="rm-btn rm-btn-secondary">Consultar</button></td></tr></tbody></table><footer class="rm-pagination"><p class="rm-pagination__range">1–2 de 12 personas</p><nav class="rm-pagination__pages"><button class="rm-pagination__page" aria-label="Anterior" disabled>‹</button><span class="rm-pagination__page is-current" aria-current="page">1</span><button class="rm-pagination__page">2</button><button class="rm-pagination__page" aria-label="Siguiente">›</button></nav></footer></div></main></body></html>`;

test('diseño operativo compartido: diez roles, dos temas y cinco anchos', { skip: !executablePath, timeout: 180000 }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    const output = new URL('../../output/diseno-compartido-20261106/', import.meta.url);
    await mkdir(output, { recursive: true });
    try {
        const page = await browser.newPage();
        const evidence = [];
        for (const role of roles) for (const theme of ['light', 'dark']) {
            await page.setContent(fixture(role, theme));
            for (const width of [1440, 1280, 1024, 768, 390]) {
                await page.setViewport({ width, height: 900 });
                const state = await page.evaluate(() => {
                    const style = selector => getComputedStyle(document.querySelector(selector));
                    return {
                        overflow: document.documentElement.scrollWidth - innerWidth,
                        header: style('h1').textTransform,
                        filters: style('.rm-filter-bar').backgroundColor,
                        page: style('[data-rm-main]').backgroundColor,
                        table: style('th').textTransform,
                        current: style('.is-current').backgroundColor,
                        success: style('.rm-badge-success').backgroundColor,
                        danger: style('.rm-badge-danger').backgroundColor,
                    };
                });
                assert.ok(state.overflow <= 1, JSON.stringify({ role, theme, width, ...state }));
                assert.equal(state.header, 'uppercase');
                assert.equal(state.table, 'uppercase');
                assert.notEqual(state.filters, state.page);
                assert.notEqual(state.success, state.danger, 'Estados semánticos diferenciados');
                await page.focus('#buscar');
                assert.equal(await page.$eval('#buscar', el => el === document.activeElement), true);
                if (['manager', 'nursing'].includes(role) && [1440, 390].includes(width)) {
                    await page.screenshot({ path: new URL(`${role}-${theme}-${width}.png`, output).pathname.replace(/^\/([A-Z]:)/i, '$1'), fullPage: true });
                }
                evidence.push({ role, theme, width, ...state });
            }
        }
        await writeFile(new URL('matrix.json', output), JSON.stringify(evidence, null, 2));
    } finally { await browser.close(); }
});
