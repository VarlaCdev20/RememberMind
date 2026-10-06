import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const css = ['spacing', 'sizing', 'radius', 'motion', 'typography', 'shadows'].map(name => read(`resources/frontend/styles/design-system/tokens/${name}.css`)).join('\n')
    + read('resources/frontend/styles/design-system/patterns/residentes-interactivos.css')
    + read('resources/frontend/styles/design-system/patterns/habitaciones-interactivas.css');
const chrome = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe', 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe'].find(path => path && existsSync(path));

test('una cama ocupa su habitación sin espacio vacío y el más funciona con foco y pantalla táctil', { skip: !chrome }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath: chrome });
    try {
        for (const width of [1200, 820, 390]) {
            const page = await browser.newPage();
            await page.setViewport({ width, height: 844, hasTouch: width === 390 });
            await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
            await page.setContent(`<style>*{box-sizing:border-box}body{margin:0}.rm-btn-icon{width:44px;height:44px}</style><div class="rm-residents rm-building is-inventory"><section class="rm-building-rooms"><article class="rm-building-room"><button class="rm-building-room__toggle">Habitación sintética</button><div class="rm-building-room__inside"><div class="rm-building-beds"><div class="rm-building-bed-wrap"><a class="rm-building-bed is-disponible" href="#ficha"><span class="rm-building-bed__picture"></span><span class="rm-building-bed__content"><strong>CAM-PRUEBA</strong><span class="rm-building-status">Disponible</span><small>Articulada</small></span></a><button class="rm-building-bed__assign rm-btn-icon" aria-label="Elegir residente sintético">+</button></div></div></div></article></section></div>`);
            await page.addStyleTag({ content: css });
            const layout = await page.evaluate(() => {
                const inside = document.querySelector('.rm-building-room__inside').getBoundingClientRect();
                const bed = document.querySelector('.rm-building-bed').getBoundingClientRect();
                return { overflow: document.documentElement.scrollWidth > innerWidth, ratio: bed.width / inside.width, height: bed.height, initial: getComputedStyle(document.querySelector('.rm-building-bed__assign')).opacity };
            });
            assert.equal(layout.overflow, false);
            assert.ok(layout.ratio > 0.9, `${width}: una cama debe usar el ancho de la habitación`);
            assert.ok(layout.height < 150, `${width}: cama compacta`);
            assert.equal(layout.initial, width === 390 ? '1' : '0');
            await page.keyboard.press('Tab');
            await page.keyboard.press('Tab');
            const focused = await page.$eval('.rm-building-bed__assign', el => ({ visible: getComputedStyle(el).opacity, focused: document.activeElement === el, width: el.getBoundingClientRect().width }));
            // Primero se enfoca el desplegable y después la ficha: :focus-within revela el +.
            assert.equal(focused.visible, '1');
            await page.keyboard.press('Tab');
            assert.equal(await page.$eval('.rm-building-bed__assign', el => document.activeElement === el), true);
            assert.ok(focused.width >= 44);
            await page.close();
        }
    } finally { await browser.close(); }
});
