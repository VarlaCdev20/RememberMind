import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

test('fondos sólidos conservan relieve, foco y degradado exclusivo de las letras', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [390, 768, 1024, 1280, 1440]) for (const theme of ['light', 'dark']) {
            await page.setViewport({ width, height: 900 });
            await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head><body class="rm-shell" data-role="nursing"><div class="rm-app-frame"><main data-rm-main class="rm-depth-canvas"><section class="rm-dashboard-composition rm-nursing-dashboard"><header class="rm-dashboard-header"><h1 class="rm-dashboard-header__title">Título de prueba</h1></header><div class="rm-card">Contenido de prueba</div><button class="rm-btn rm-btn-primary">Acción</button><button class="rm-btn rm-btn-info">Información</button><button class="rm-btn rm-btn-danger">Peligro</button><div class="rm-resident-directory"><header class="rm-collection-header rm-resident-directory__header">Cabecera de prueba</header><div class="rm-filter-bar rm-collection-toolbar rm-resident-directory__filters">Filtros de prueba</div><div class="rm-panel">Panel</div><div class="rm-modal-panel">Modal</div><div class="bg-white">Superficie heredada</div><div class="bg-gray-50">Superficie auxiliar</div></div><label for="paleta-input">Campo de prueba</label><input id="paleta-input" class="rm-input"><section class="rm-resident-directory__results" aria-label="Residentes de prueba"><header class="rm-resident-directory__results-header">1 residente de prueba</header><div class="rm-resident-directory__cards"><article class="rm-person-entity-card rm-resident-directory__item rm-resident-directory__item--card rm-resident-compact-card">Residente de prueba</article></div></section></section></main></div></body></html>`);
            await page.focus('#paleta-input');
            const result = await page.evaluate(() => ({
                canvas: getComputedStyle(document.querySelector('main')).backgroundColor,
                pattern: getComputedStyle(document.querySelector('main')).backgroundImage,
                patternBlend: getComputedStyle(document.querySelector('main')).backgroundBlendMode,
                patternSize: getComputedStyle(document.querySelector('main')).backgroundSize,
                canvasShadow: getComputedStyle(document.querySelector('main')).boxShadow,
                warmSurfaces: [...document.querySelectorAll('.rm-collection-header,.rm-filter-bar,.rm-card,.rm-panel,.rm-modal-panel,.bg-white,.bg-gray-50')].map(e => getComputedStyle(e).backgroundColor),
                inputBackground: getComputedStyle(document.querySelector('#paleta-input')).backgroundColor,
                residents: (() => { const c = getComputedStyle(document.querySelector('.rm-resident-directory__results')); const card = getComputedStyle(document.querySelector('.rm-resident-compact-card')); return { background: c.backgroundColor, shadow: c.boxShadow, border: c.borderTopWidth, cardBackground: card.backgroundColor, cardShadow: card.boxShadow }; })(),
                surfaces: [...document.querySelectorAll('.rm-card,.rm-btn,.rm-dashboard-header,.rm-nursing-dashboard')].map(e => getComputedStyle(e).backgroundImage),
                title: getComputedStyle(document.querySelector('h1')).backgroundImage,
                shadow: getComputedStyle(document.querySelector('.rm-card')).boxShadow,
                focused: document.activeElement.id,
                outline: getComputedStyle(document.activeElement).outlineStyle,
            }));
            assert.equal(result.canvas, theme === 'light' ? 'rgb(216, 210, 205)' : 'rgb(10, 24, 33)');
            assert.match(result.pattern, /^radial-gradient\(circle/, 'puntos menudos');
            assert.match(result.pattern, /0\.6px/, 'radio menor que un píxel');
            assert.doesNotMatch(result.pattern, /url\(|repeating-linear-gradient/, 'trama de puntos sin corrugado');
            assert.equal(result.patternSize, '16px 16px');
            assert.equal(result.patternBlend, 'normal');
            if (theme === 'light') {
                assert.ok(result.warmSurfaces.every(color => ['rgb(236, 233, 230)', 'rgb(240, 237, 234)', 'color(srgb 0.92549 0.913725 0.901961 / 0.84)'].includes(color)), 'superficies cálidas con jerarquía de elevación');
                assert.ok(new Set(result.warmSurfaces).size >= 2, 'panel elevado distinguible de la tarjeta');
                assert.equal(result.inputBackground, 'rgb(231, 226, 222)', 'campos con superficie cálida');
            } else {
                assert.ok(result.warmSurfaces.every(color => color !== 'rgb(255, 255, 255)'), 'sin blanco en tema oscuro');
            }
            assert.notEqual(result.canvasShadow, 'none', 'lienzo con profundidad interior');
            assert.equal(result.residents.background, 'rgba(0, 0, 0, 0)', 'sin panel exterior');
            assert.equal(result.residents.shadow, 'none');
            assert.equal(result.residents.border, '0px');
            assert.notEqual(result.residents.cardBackground, 'rgba(0, 0, 0, 0)', 'conservar la tarjeta individual');
            assert.notEqual(result.residents.cardShadow, 'none');
            assert.ok(result.surfaces.every(image => image === 'none'), `${theme} ${width}px: fondos uniformes`);
            assert.match(result.title, /linear-gradient/, `${theme} ${width}px: conservar letras`);
            assert.notEqual(result.shadow, 'none', `${theme} ${width}px: relieve visible`);
            assert.equal(result.focused, 'paleta-input');
            assert.equal(result.outline, 'solid');
        }
    } finally { await browser.close(); }
});

test('el acento del rol cambia la navegación y los controles sin teñir superficies ni alertas', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        const appearances = [];
        for (const role of ['superadmin', 'nursing', 'doctor', 'psychology']) {
            await page.setContent(`<html data-theme="light"><head><style>${css}</style></head><body class="rm-shell ${role === 'nursing' ? 'rm-nursing-shell' : ''}" data-role="${role}"><div class="rm-app-frame"><aside class="rm-sidebar"><nav class="rm-sidebar__nav"><a class="rm-sidebar__item is-active"><span class="rm-sidebar__indicator"></span><i class="rm-sidebar__icon"></i>Inicio</a></nav></aside><header class="rm-topbar"></header><main data-rm-main><section class="rm-dashboard-composition"><header class="rm-dashboard-header"><div class="rm-dashboard-header__content"><span class="rm-dashboard-header__role"><span></span>Rol</span></div></header><div class="rm-card rm-metric-card">Métrica</div><span class="rm-filter-chip rm-filter-chip--danger">Crítico</span><button class="rm-btn rm-btn-primary">Acción</button><div class="rm-tabs-line"><button class="rm-tab-line-item is-active">Resumen</button></div></section></main></div></body></html>`);
            appearances.push(await page.evaluate(() => {
                const color = (selector, property) => getComputedStyle(document.querySelector(selector))[property];
                return {
                    role: getComputedStyle(document.body).getPropertyValue('--rm-role-primary').trim(),
                    sidebar: color('.rm-sidebar', 'backgroundColor'),
                    active: color('.rm-sidebar__item', 'backgroundColor'),
                    activeIcon: color('.rm-sidebar__icon', 'color'),
                    header: color('.rm-dashboard-header', 'backgroundColor'),
                    badge: color('.rm-dashboard-header__role', 'backgroundColor'),
                    card: color('.rm-metric-card', 'backgroundColor'),
                    button: color('.rm-btn-primary', 'backgroundColor'),
                    tab: color('.rm-tab-line-item', 'borderBottomColor'),
                    alert: color('.rm-filter-chip--danger', 'backgroundColor'),
                };
            }));
        }
        assert.equal(new Set(appearances.map(item => item.role)).size, appearances.length);
        for (const key of ['sidebar', 'header', 'card', 'alert']) {
            assert.equal(new Set(appearances.map(item => item[key])).size, 1, `${key} mantiene su semántica global`);
        }
        assert.equal(new Set(appearances.map(item => item.button)).size, 1, 'acción primaria compartida entre roles');
        for (const key of ['active', 'activeIcon', 'badge', 'tab']) {
            assert.equal(new Set(appearances.map(item => item[key])).size, appearances.length, `${key} refleja el rol`);
        }
    } finally { await browser.close(); }
});

test('los shells por rol conservan fondo y controles completos en claro, oscuro y móvil', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [320, 390, 768, 1024, 1280, 1440]) {
            await page.setViewport({ width, height: 900 });
            for (const variant of ['', 'rm-shell--administracion', 'rm-nursing-shell', 'superadmin']) {
                for (const theme of ['light', 'dark']) {
                    await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head>
                    <body class="rm-shell ${variant}"><div class="rm-app-frame">
                    <aside class="rm-sidebar"><header class="rm-sidebar__header"></header></aside>
                    <header class="rm-topbar" data-rm-topbar="system">
                      <div class="rm-topbar__identity"><button class="rm-topbar__action rm-topbar__hamburger">Menú</button></div>
                      <div class="rm-topbar__search-slot"><div class="rm-topbar-search"><button class="rm-topbar-search__toggle">Buscar</button><form class="rm-topbar-search__form"><input aria-label="Buscar"></form></div></div>
                      <div class="rm-topbar__actions">
                        <div><button class="rm-topbar__action">Calendario</button></div>
                        <button class="rm-topbar__action rm-topbar__theme-toggle" aria-label="Cambiar tema"><i class="ph-bold ph-moon rm-topbar__theme-icon--moon"></i><i class="ph-bold ph-sun rm-topbar__theme-icon--sun"></i></button>
                        <div><button class="rm-btn-icon">Alertas</button></div>
                        <div><button class="rm-topbar__action">Ajustes</button></div>
                        <div><button class="rm-topbar__profile">Perfil</button></div>
                      </div>
                    </header>
                    <section data-role-preview-banner>Modo previsualización</section>
                    <main data-rm-main><div class="${variant === 'superadmin' ? 'rm-dashboard-composition--superadmin' : ''}">Contenido</div></main>
                    </div></body></html>`);
                    const result = await page.evaluate(() => {
                        const background = selector => getComputedStyle(document.querySelector(selector)).backgroundColor;
                        const buttons = [...document.querySelectorAll('.rm-topbar__actions button')].map(button => {
                            const rect = button.getBoundingClientRect();
                            return { width: rect.width, height: rect.height, left: rect.left, right: rect.right };
                        });
                        const topbar = document.querySelector('.rm-topbar').getBoundingClientRect();
                        const banner = document.querySelector('[data-role-preview-banner]').getBoundingClientRect();
                        const moon = getComputedStyle(document.querySelector('.rm-topbar__theme-icon--moon')).display;
                        const sun = getComputedStyle(document.querySelector('.rm-topbar__theme-icon--sun')).display;
                        const image = selector => getComputedStyle(document.querySelector(selector)).backgroundImage;
                        return { pattern: image('main'), sidebarImage: image('.rm-sidebar'), topbarImage: image('.rm-topbar'), main: background('main'), sidebar: background('.rm-sidebar'), topbar: background('.rm-topbar'), buttons, moon, sun, topbarBounds: { left: topbar.left, right: topbar.right }, bannerBounds: { left: banner.left, right: banner.right } };
                    });
                    const context = `${variant || 'sistema'} ${theme} ${width}px`;
                    if (theme === 'light') {
                        assert.equal(result.main, 'rgb(216, 210, 205)', `${context}: lienzo aprobado`);
                        assert.equal(result.sidebar, 'rgb(216, 210, 205)', `${context}: shell aprobado`);
                        assert.equal(result.topbar, result.main, `${context}: navbar y fondo del mismo color claro`);
                        assert.equal(result.topbar, result.sidebar, `${context}: cabecera del mismo color que el sidebar`);
                    } else {
                        assert.equal(result.main, result.sidebar, `${context}: base oscura compartida`);
                    }
                    assert.equal(result.topbar, result.sidebar, `${context}: base compartida en ambos temas`);
                    assert.match(result.pattern, /^radial-gradient\(circle/, `${context}: puntos menudos`);
                    assert.doesNotMatch(result.pattern, /repeating-linear-gradient|url\(/, `${context}: sin franjas ni imágenes`);
                    assert.equal(result.sidebarImage, 'none', `${context}: sidebar sólido`);
                    assert.equal(result.topbarImage, 'none', `${context}: navbar sólido`);
                    assert.equal(result.moon !== 'none', theme === 'light', `${context}: icono luna`);
                    assert.equal(result.sun !== 'none', theme === 'dark', `${context}: icono sol`);
                    assert.ok(Math.abs(result.bannerBounds.left - result.topbarBounds.left) <= 1, `${context}: aviso alineado con topbar`);
                    assert.ok(Math.abs(result.bannerBounds.right - result.topbarBounds.right) <= 1, `${context}: ancho del aviso alineado con topbar`);
                    for (const button of result.buttons) {
                        assert.ok(button.width >= 44 && button.height >= 44, `${context}: target visible`);
                        assert.ok(button.left >= 0 && button.right <= width + 1, `${context}: botón dentro de pantalla`);
                    }
                }
            }
        }
    } finally { await browser.close(); }
});

test('los dashboards por rol comparten tamaño de cabecera y superficie de cards', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [390, 768, 1440]) {
            await page.setViewport({ width, height: 900 });
            for (const theme of ['light', 'dark']) {
                const styles = [];
                for (const variant of [
                { shell: '', dashboard: 'rm-dashboard-composition--superadmin', cards: 'rm-superadmin-metrics', card: '', panel: 'rm-dashboard-data-panel' },
                { shell: 'rm-shell--administracion', dashboard: 'rm-admin-dashboard', cards: 'rm-admin-dashboard__state-strip', card: '', panel: 'rm-admin-dashboard__attention-body' },
                { shell: 'rm-nursing-shell', dashboard: 'rm-nursing-dashboard', cards: '', card: 'rm-nursing-dashboard__kpi', panel: 'rm-card rm-nursing-module' },
                { shell: '', dashboard: '', cards: '', card: '', panel: 'rm-dashboard-data-panel' },
                { shell: '', dashboard: 'rm-dashboard-composition--management', cards: 'rm-management-metrics rm-dashboard-overview', card: '', panel: 'rm-dashboard-data-panel' },
                ]) {
                    await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head><body class="rm-shell ${variant.shell}"><main><section class="rm-dashboard-composition ${variant.dashboard}"><header class="rm-dashboard-header rm-nursing-dashboard__welcome"><div class="rm-dashboard-header__content"><h1 class="rm-dashboard-header__title">Bienvenido</h1></div></header><div class="${variant.cards}"><article class="rm-card rm-metric-card ${variant.card}"><h2>Tareas del turno</h2></article></div><article class="${variant.panel}" data-panel>Información</article></section></main></body></html>`);
                    styles.push(await page.evaluate(() => {
                        const heading = getComputedStyle(document.querySelector('.rm-dashboard-header__title'));
                        const card = getComputedStyle(document.querySelector('.rm-metric-card'));
                        const panel = getComputedStyle(document.querySelector('[data-panel]'));
                        const welcome = getComputedStyle(document.querySelector('.rm-dashboard-header'));
                        const header = document.querySelector('.rm-dashboard-header').getBoundingClientRect();
                        return { headingFamily: heading.fontFamily, headingSize: heading.fontSize, headingWeight: heading.fontWeight, headingColor: heading.color, headerBg: welcome.backgroundColor, headerMinHeight: welcome.minHeight, cardBg: card.backgroundColor, cardMinHeight: card.minHeight, cardRadius: card.borderRadius, cardShadow: card.boxShadow, panelBg: panel.backgroundColor, panelRadius: panel.borderRadius, headerWidth: header.width };
                    }));
                }
                for (const [index, style] of styles.entries()) {
                    const { headerWidth, ...sharedStyle } = style;
                    const { headerWidth: baseWidth, ...baseStyle } = styles[0];
                    assert.deepEqual(sharedStyle, baseStyle, `${theme} ${width}px: variante ${index}`);
                    assert.equal(style.headingWeight, '800', `${theme} ${width}px: saludo destacado`);
                    assert.ok(style.headerWidth >= Math.min(width - 16, 500) - 1, `${theme} ${width}px: encabezado ocupa el espacio disponible`);
                }
            }
        }
    } finally { await browser.close(); }
});
