import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const css = ['spacing', 'sizing', 'radius', 'motion', 'typography', 'shadows', 'colors', 'chart-colors'].map(name => read(`resources/frontend/styles/design-system/tokens/${name}.css`)).join('\n')
    + read('resources/frontend/styles/design-system/patterns/collection-workspace.css')
    + read('resources/frontend/styles/design-system/patterns/residentes-interactivos.css')
    + read('resources/frontend/styles/design-system/patterns/operaciones-interactivas.css')
    + read('resources/frontend/styles/design-system/patterns/calendario-operativo.css');
const chrome = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(path => path && existsSync(path));

test('columnas proporcionales, anillo y tablero caben en móvil y respetan movimiento reducido', { skip: !chrome }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath: chrome });
    try {
        for (const width of [1200, 760, 390]) {
            const page = await browser.newPage();
            await page.setViewport({ width, height: 844 });
            await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
            const columns = [4, 2, 1, 3, 5, 2, 1, 4].map(value => `<a href="#grupo" style="--rm-data-size:${value / 5 * 100}%;--rm-data-color:var(--rm-primary)"><span class="rm-data-column"><strong>${value}</strong><span></span></span><small>Grupo sintético</small></a>`).join('');
            await page.setContent(`<style>*{box-sizing:border-box}body{margin:0}</style><div class="rm-residents rm-operations"><section class="rm-process-analytics"><article class="rm-residents-chart"><div class="rm-data-graphic is-columnas"><div class="rm-data-columns" style="--rm-data-count:8">${columns}</div></div></article><article class="rm-residents-chart"><div class="rm-data-graphic is-anillo"><div class="rm-data-ring"><svg viewBox="0 0 160 160"><circle class="rm-data-ring__segment" cx="80" cy="80" r="58"/></svg><div><strong>4</strong><span>registros sintéticos</span></div></div><ul class="rm-data-legend"><li><a href="#grupo"><span class="rm-data-dot"></span><span>Etiqueta sintética larga para comprobar su distribución</span><strong>3</strong><small>75%</small></a></li></ul></div></article></section><div class="rm-process-board"><section><header><span>Área sintética</span></header><div class="rm-operations-cards"><article class="rm-operation-card">Registro sintético</article></div></section></div></div>`);
            await page.addStyleTag({ content: css });
            const layout = await page.evaluate(() => {
                const bars = [...document.querySelectorAll('.rm-data-column > span')].map(e => e.getBoundingClientRect().height);
                return { overflow: document.documentElement.scrollWidth > innerWidth, ratio: bars[0] / bars[1], animation: getComputedStyle(document.querySelector('.rm-data-ring__segment')).animationName, target: document.querySelector('.rm-data-legend a').getBoundingClientRect().height, grid: document.querySelector('.rm-process-board').getBoundingClientRect().width };
            });
            assert.equal(layout.overflow, false, `${width}: no debe desbordar la página`);
            assert.ok(Math.abs(layout.ratio - 2) < .05, `${width}: las barras deben conservar proporción 4:2`);
            assert.equal(layout.animation, 'none');
            assert.ok(layout.target >= 44);
            assert.ok(layout.grid <= width);
            await page.close();
        }
    } finally { await browser.close(); }
});

test('calendario mensual conserva siete columnas y desplaza dentro de su región en móvil', { skip: !chrome }, async () => {
    const browser=await puppeteer.launch({headless:true,executablePath:chrome});
    try {
        for(const width of [1200,760,390]) {
            const page=await browser.newPage();await page.setViewport({width,height:844});
            const dias=Array.from({length:31},(_,i)=>`<button class="rm-operation-calendar__day"><span>${i+1}</span><span class="rm-operation-calendar__count">${i===1?50:0}</span></button>`).join('');
            await page.setContent(`<style>*{box-sizing:border-box}body{margin:0}</style><div class="rm-residents"><section class="rm-collection-results"><section class="rm-operation-calendar"><header class="rm-operation-calendar__header"><div><h3>Mes sintético</h3></div><nav class="rm-operation-calendar__navigation"><a href="#mes">Anterior</a><a href="#mes">Hoy</a><a href="#mes">Siguiente</a></nav></header><div class="rm-operation-calendar__scroll"><div class="rm-operation-calendar__grid">${dias}</div></div></section></section></div>`);
            await page.addStyleTag({content:css});
            const datos=await page.evaluate(()=>{const region=document.querySelector('.rm-operation-calendar__scroll');return {overflow:document.documentElement.scrollWidth>innerWidth,columns:getComputedStyle(document.querySelector('.rm-operation-calendar__grid')).gridTemplateColumns.split(' ').length,day:document.querySelector('.rm-operation-calendar__day').getBoundingClientRect().height,region:getComputedStyle(region).overflowX,regionWidth:region.clientWidth,scrollWidth:region.scrollWidth};});
            assert.equal(datos.overflow,false);assert.equal(datos.columns,7);assert.ok(datos.day>=44);assert.equal(datos.region,'auto');assert.ok(datos.regionWidth<=width);
            if(width===390)assert.ok(datos.scrollWidth>datos.regionWidth,'el calendario móvil desplaza dentro de su región');
            await page.close();
        }
    } finally {await browser.close();}
});
