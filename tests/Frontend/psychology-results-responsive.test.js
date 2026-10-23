import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = read(`public/build/${manifest['resources/frontend/styles/app.css'].file}`);
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe']
    .find(path => path && existsSync(path));

const pageHtml = (width, theme) => `<!doctype html><html data-theme="${theme}" class="${theme === 'dark' ? 'dark' : ''}"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${css}</style><style>body{margin:0}.test-shell{margin-left:${width >= 1024 ? 90 : 0}px;padding:16px;min-width:0}</style></head><body class="rm-shell"><main class="test-shell"><div class="rm-expert-results rm-expert-results--psychology"><header class="rm-expert-heading rm-expert-page-heading"><div><h1>Evaluación cognitiva y funcional</h1><p class="rm-expert-subtitle">Resultados y evidencias disponibles</p></div><div class="rm-expert-heading-actions"><button class="rm-btn rm-btn-primary">Ver registros y evidencias</button><button class="rm-btn rm-btn-secondary">Historial</button><button class="rm-btn rm-btn-secondary">Revisión profesional</button></div></header><div class="rm-orion-workspace"><div class="rm-psychology-overview"><section class="rm-psychology-resident"><div class="rm-psychology-resident__identity"><span class="rm-expert-avatar">P</span><div><h2>Residente de ejemplo con nombre y apellidos extensos</h2><p>86 años</p></div></div><div class="rm-psychology-resident__date"><strong>Consulta actual</strong><span>Información considerada hasta hoy</span></div><span class="rm-psychology-resident__state">Sin evaluación histórica completa</span></section><section class="rm-psychology-pending"><div class="rm-psychology-pending__main"><span class="rm-psychology-pending__status">No concluyente</span><h2>Aún no es posible clasificar el perfil cognitivo</h2><p>Hay observaciones registradas, pero falta información para interpretar las áreas.</p></div><div class="rm-psychology-pending__needs"><h3>Qué falta para orientar el resultado</h3><ul><li>Mediciones interpretables de cinco áreas cognitivas</li><li>Autonomía en actividades instrumentales</li></ul><button class="rm-btn rm-btn-primary">Ver qué se necesita</button></div></section><div class="rm-psychology-dashboard"><div class="rm-psychology-dashboard__main"><section class="rm-psychology-panel"><header><div><h2>Estado de los cinco dominios</h2><p>Qué se pudo valorar en cada área.</p></div><button class="rm-psychology-help" aria-label="Ayuda">?</button></header><ul class="rm-psychology-status-list">${['Memoria','Atención','Funciones ejecutivas','Lenguaje','Función visuoespacial'].map(name => `<li><span>${name}</span><strong>Sin medición interpretable</strong></li>`).join('')}</ul></section><section class="rm-psychology-panel"><header><div><h2>Controles registrados</h2></div></header><div class="rm-psychology-month-chart"><div><strong>3</strong><span class="rm-psychology-month-chart__bar" style="--rm-bar-height:75%"></span><span>Oct 2026</span></div></div></section></div><div class="rm-psychology-dashboard__aside"><section class="rm-psychology-panel"><header><div><h2>Fuentes disponibles</h2></div></header><dl class="rm-psychology-count-list"><div><dt>Observaciones por componente</dt><dd>12</dd></div></dl></section><section class="rm-psychology-panel"><header><div><h2>Qué se comprobó</h2></div></header><ul class="rm-psychology-status-list"><li><span>Autonomía instrumental</span><strong>Pendiente</strong></li></ul></section></div></div></div></div></div></main></body></html>`;

test('resultado de Psicología conserva contenido y controles sin desbordar en seis anchos', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const theme of ['light', 'dark']) {
            for (const width of [1440, 1280, 1024, 913, 768, 390]) {
                await page.setViewport({ width, height: 900 });
                const html = pageHtml(width, theme)
                    .replace('class="rm-psychology-overview"', 'class="rm-psychology-overview is-compact-profile"')
                    .replace('class="rm-psychology-dashboard"', 'class="rm-psychology-dashboard rm-psychology-dashboard--compact"')
                    .replace('<div class="rm-psychology-dashboard__aside">', '<div class="rm-psychology-dashboard__aside"><section class="rm-psychology-panel rm-psychology-process is-incomplete"><header><h2>Proceso de evaluación</h2></header><ol><li>Registro original</li><li>Valoración</li><li>Criterios</li><li>Perfil</li></ol><button class="rm-btn rm-btn-secondary">Ver qué falta</button></section>');
                await page.setContent(html);
                const result = await page.evaluate(() => {
                    const main = document.querySelector('main');
                    const dashboard = document.querySelector('.rm-psychology-dashboard');
                    const controls = [...document.querySelectorAll('button')].map(button => ({
                        height: button.getBoundingClientRect().height,
                        right: button.getBoundingClientRect().right,
                    }));
                    return {
                        overflow: document.documentElement.scrollWidth > innerWidth + 1 || main.scrollWidth > main.clientWidth + 1,
                        columns: getComputedStyle(dashboard).gridTemplateColumns.split(' ').length,
                        controls,
                    };
                });
                assert.equal(result.overflow, false, `${theme} ${width}px: contenido sin scroll horizontal`);
                assert.ok(result.controls.every(control => control.height >= 44 && control.right <= width + 1), `${theme} ${width}px: controles visibles y de al menos 44px`);
                if (width === 390) assert.equal(result.columns, 1, `${theme}: una columna en móvil`);
                if (width === 1440) assert.equal(result.columns, 3, `${theme}: tres columnas en escritorio`);
            }
        }
    } finally {
        await browser.close();
    }
});

test('la composición compartida conserva tres columnas en escritorio y una en móvil para Enfermería', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [1440, 390]) {
            await page.setViewport({ width, height: 900 });
            await page.setContent(pageHtml(width, 'light')
                .replace('class="rm-expert-results rm-expert-results--psychology"', 'class="rm-expert-results rm-expert-results--nursing"')
                .replace('class="rm-psychology-overview"', 'class="rm-psychology-overview is-compact-profile"')
                .replace('class="rm-psychology-dashboard"', 'class="rm-psychology-dashboard rm-psychology-dashboard--compact"'));
            const result = await page.evaluate(() => ({
                overflow: document.documentElement.scrollWidth > innerWidth + 1,
                columns: getComputedStyle(document.querySelector('.rm-psychology-dashboard')).gridTemplateColumns.split(' ').length,
            }));
            assert.equal(result.overflow, false, `Enfermería ${width}px: contenido sin scroll horizontal`);
            assert.equal(result.columns, width === 1440 ? 3 : 1, `Enfermería ${width}px: distribución esperada`);
        }
    } finally {
        await browser.close();
    }
});

test('el perfil multidominio de Enfermería distribuye gráfica y criterios sin huecos', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        await page.setViewport({ width: 1440, height: 900 });
        await page.setContent(pageHtml(1440, 'light')
            .replace('class="rm-expert-results rm-expert-results--psychology"', 'class="rm-expert-results rm-expert-results--nursing"')
            .replace('class="rm-psychology-overview"', 'class="rm-psychology-overview is-multidomain"')
            .replace('class="rm-psychology-dashboard"', 'class="rm-psychology-dashboard rm-psychology-dashboard--multidomain"'));
        const columns = await page.$eval('.rm-psychology-dashboard', element => getComputedStyle(element).gridTemplateColumns.split(' ').length);
        assert.equal(columns, 2);
    } finally {
        await browser.close();
    }
});
