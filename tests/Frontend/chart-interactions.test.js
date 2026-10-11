import { test } from 'node:test';
import assert from 'node:assert/strict';
import puppeteer from 'puppeteer';
import { existsSync } from 'node:fs';
import { rmInstallChartInteractions } from '../../resources/frontend/styles/design-system/charts/chart-interactions.js';

test('leyenda y aro muestran datos reales con mouse, teclado y después de actualizar el DOM', async () => {
    const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.setContent(`<style>.rm-admin-dashboard__donut{width:140px;height:140px;position:relative}.rm-chart-inspect-ring{position:absolute;inset:0;pointer-events:none} .rm-chart-inspect-tooltip{position:fixed}span::before{background:green}</style>
          <div class="rm-admin-dashboard__donut-layout"><div class="rm-admin-dashboard__donut"></div>
          <div class="rm-admin-dashboard__donut-legend"><a href="/alertas"><span>Alta</span><strong>3</strong></a><div><span>Normal</span><strong>7</strong></div><div><span>Vacío</span><strong>0</strong></div></div></div>
          <div><div class="rm-admin-dashboard__bar-group" title="Lunes: 4 ocupadas">Lunes</div></div>`);
        await page.evaluate(rmInstallChartInteractions);
        await page.hover('a');
        assert.equal(await page.$eval('[role=tooltip]', el => el.textContent), 'Alta: 3 · 30%');
        assert.equal(await page.$eval('.rm-chart-inspect-ring circle', el => el.getAttribute('stroke-dasharray')), '30 70');
        assert.equal(await page.$eval('a', el => el.getAttribute('href')), '/alertas');
        await page.focus('.rm-admin-dashboard__donut-legend > div');
        assert.equal(await page.$eval('[role=tooltip]', el => el.textContent), 'Normal: 7 · 70%');
        await page.keyboard.press('Escape');
        assert.equal(await page.$eval('[role=tooltip]', el => el.hidden), true);
        await page.mouse.move(138, 78);
        assert.equal(await page.$eval('[role=tooltip]', el => el.textContent), 'Alta: 3 · 30%');
        await page.focus('.rm-admin-dashboard__bar-group');
        assert.equal(await page.$eval('[role=tooltip]', el => el.textContent), 'Lunes: 4 ocupadas');
        await page.evaluate(() => { document.querySelector('.rm-admin-dashboard__donut-legend').innerHTML = '<div><span>Actualizado</span><strong>9</strong></div>'; });
        await page.waitForSelector('.rm-admin-dashboard__donut-legend > [tabindex="0"]');
        await page.focus('.rm-admin-dashboard__donut-legend > div');
        assert.equal(await page.$eval('[role=tooltip]', el => el.textContent), 'Actualizado: 9 · 100%');
        assert.deepEqual(errors, []);
    } finally { await browser.close(); }
});
