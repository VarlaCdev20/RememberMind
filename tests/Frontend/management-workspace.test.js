import test from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

test('Gerencia renovada y Enfermería conservan indicadores y controles legibles según su contenedor', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [320, 390, 768, 1024, 1280, 1440]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: 900 });
            const available = width - (width >= 1024 ? 224 : 0) - 40;
            for (const manager of [false, true]) {
                const metrics = (manager ? ['Personal activo', 'Ocupación', 'Alertas activas'] : ['Residentes', 'Medicación pendiente', 'Cuidados pendientes', 'Alertas del turno']).map(label => `<section class="rm-card rm-metric-card"><div class="rm-metric-card__top"><span class="rm-metric-card__icon">I</span></div><div class="rm-metric-card__body"><strong class="rm-metric-card__value">29</strong><h2 class="rm-metric-card__label">${label}</h2><p class="rm-metric-card__description">Personas únicas de la jornada actual</p></div></section>`).join('');
                const links = ['Personal', 'Áreas', 'Planificación', 'Cobertura', 'Reportes gerenciales'].map(label => `<a class="rm-btn-secondary" href="/prueba"><span class="rm-workspace-access-card__icon">I</span><span class="rm-workspace-access-card__copy"><strong>${label}</strong><small>Descripción operativa de prueba</small></span></a>`).join('');
                await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head><body class="rm-shell" data-role="${manager ? 'manager' : 'nursing'}"><main data-rm-main style="width:${available}px;margin:0;padding:0"><section class="${manager ? 'rm-management-workspace' : 'rm-dashboard-composition rm-nursing-dashboard'}"><header class="rm-dashboard-header" style="grid-column:1/-1"><div class="rm-dashboard-header__content"><p class="rm-dashboard-header__eyebrow">Centro geriátrico</p><div class="rm-dashboard-header__heading"><h1 class="rm-dashboard-header__title">Buenas tardes, Alicia Elena Prueba Ejemplo</h1></div><p class="rm-dashboard-header__subtitle">Organiza el equipo y consulta la planificación.</p></div><div class="rm-dashboard-header__visual"></div></header><section class="grid gap-4 ${manager ? 'rm-management-overview' : 'rm-nursing-dashboard__overview'}">${metrics}</section>${manager ? `<nav class="rm-management-tabs">${links}</nav><div class="rm-management-panels"><article class="rm-dashboard-data-panel">Cobertura</article><article class="rm-dashboard-data-panel">Profesiones</article><article class="rm-dashboard-data-panel">Actividades</article></div>` : ''}</section></main></body></html>`);
                const result = await page.evaluate(() => {
                    const title = getComputedStyle(document.querySelector('h1'));
                    const header = getComputedStyle(document.querySelector('.rm-dashboard-header'));
                    const columns = selector => getComputedStyle(document.querySelector(selector)).gridTemplateColumns.split(' ').length;
                    return { header: { background: header.backgroundColor, columns: header.gridTemplateColumns, align: getComputedStyle(document.querySelector('.rm-dashboard-header__content')).textAlign, titleSize: title.fontSize, titleFamily: title.fontFamily, titleFill: title.webkitTextFillColor, titleImage: title.backgroundImage },
                        metrics: columns('.rm-management-overview,.rm-nursing-dashboard__overview'),
                        panels: document.querySelector('.rm-management-panels') ? columns('.rm-management-panels') : null,
                        overflow: document.querySelector('main').scrollWidth > document.querySelector('main').clientWidth,
                        collisions: [...document.querySelectorAll('.rm-metric-card__label')].some(e => { const label = e.getBoundingClientRect(); const value = e.parentElement.querySelector('strong').getBoundingClientRect(); const card = e.closest('.rm-metric-card').getBoundingClientRect(); const solapan = label.left < value.right - 1 && label.right > value.left + 1 && label.top < value.bottom - 1 && label.bottom > value.top + 1; return solapan || label.right > card.right + 1; }),
                        accesses: [...document.querySelectorAll('.rm-management-tabs a')].map(e => ({ width: e.getBoundingClientRect().width, height: e.getBoundingClientRect().height })) };
                });
                const context = `${width}px ${theme} ${manager ? 'Gerencia' : 'Enfermería'}`;
                assert.equal(result.overflow, false, context);
                assert.equal(result.collisions, false, `${context}: cifras y etiquetas separadas`);
                if (manager) {
                    assert.equal(result.metrics, available < 640 ? 1 : 3, `${context}: columnas según contenedor`);
                    assert.equal(result.panels, available < 640 ? 1 : 2, `${context}: paneles según contenedor`);
                    assert.equal(result.accesses.length, 5);
                    for (const access of result.accesses) assert.ok(access.width >= 44 && access.height >= 44, `${context}: accesos legibles`);
                }
            }
        }
    } finally { await browser.close(); }
});
