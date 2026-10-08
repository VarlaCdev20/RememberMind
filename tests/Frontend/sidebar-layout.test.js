import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = file => readFileSync(new URL(file, root), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = read(`public/build/${manifest['resources/frontend/styles/app.css'].file}`);
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
assert.ok(executablePath, 'Se necesita Chrome para verificar la geometría real del sidebar.');

const badge = value => `<span class="rm-sidebar__badge" aria-hidden="true"><span class="rm-sidebar__badge-value">${value}</span><span class="rm-sidebar__badge-compact">${value > 99 ? '99+' : value}</span></span>`;
const item = (label, count, group = true) => `<div class="rm-sidebar__entry"><${group ? 'button' : 'a'} class="rm-sidebar__item" aria-label="${label}, ${count}" ${group ? 'aria-expanded="false"' : 'href="/seguimiento"'}><span class="rm-sidebar__indicator"></span><i class="rm-sidebar__icon" aria-hidden="true">◉</i><span class="rm-sidebar__label rm-nav-group">${label}</span>${badge(count)}${group ? '<i class="rm-sidebar__chevron" aria-hidden="true">⌄</i>' : ''}</${group ? 'button' : 'a'}></div>`;

test('sidebar: etiquetas y badges no colisionan en escritorio, laptop y drawer con ambos temas', async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
        for (const admin of [false, true]) {
            for (const theme of ['light', 'dark']) {
                for (const width of [1440, 1100, 900, 390, 320]) {
                    for (const collapsed of width >= 1024 ? [false, true] : [false]) {
                        await page.setViewport({ width, height: 900 });
                        await page.setContent(`<html data-theme="${theme}"><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>${css}</style></head><body class="rm-shell ${admin ? 'rm-shell--administracion' : ''} ${collapsed ? 'is-sidebar-collapsed' : ''}"><aside class="rm-sidebar is-mobile-open"><nav class="rm-sidebar__nav">${item('Seguimiento', 3)}${item('Gestión administrativa e institucional', 12345)}${item('Expediente clínico multidisciplinario', 99, false)}<div class="rm-sidebar__submenu"><a class="rm-sidebar__subitem"><span class="rm-sidebar__subindicator"></span><span>Habitaciones, camas y ocupación institucional</span>${badge(12345)}</a></div></nav></aside></body></html>`);
                        const rows = await page.evaluate(() => {
                            const box = el => { const r = el.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, width: r.width }; };
                            return [...document.querySelectorAll('.rm-sidebar__item')].map(el => {
                                const label = el.querySelector('.rm-sidebar__label');
                                const badge = el.querySelector('.rm-sidebar__badge');
                                const icon = el.querySelector('.rm-sidebar__icon');
                                const caret = el.querySelector('.rm-sidebar__chevron');
                                return { row: box(el), label: box(label), labelOverflow: label.scrollWidth > label.clientWidth + 1, badge: box(badge), badgeOverflow: badge.scrollWidth > badge.clientWidth + 1, badgeText: badge.innerText, icon: box(icon), caret: caret && box(caret), font: parseFloat(getComputedStyle(badge).fontSize), navigationOverflow: el.closest('nav').scrollWidth > el.closest('nav').clientWidth + 1 };
                            });
                        });
                        for (const row of rows) {
                            const context = `${admin ? 'administración' : 'compartido'} ${theme} ${width}px ${collapsed ? 'contraído' : 'expandido'}`;
                            assert.equal(row.navigationOverflow, false, `${context}: sin desbordamiento horizontal`);
                            assert.equal(row.badgeOverflow, false, `${context}: contador legible`);
                            assert.ok(row.badge.left >= row.row.left && row.badge.right <= row.row.right, `${context}: badge dentro del acceso`);
                            if (collapsed) {
                                assert.ok(Math.abs((row.icon.left + row.icon.right) / 2 - (row.row.left + row.row.right) / 2) <= 1, `${context}: icono centrado`);
                                assert.ok(row.icon.bottom <= row.badge.top, `${context}: icono y contador separados`);
                                assert.ok(row.font >= 11, `${context}: contador conserva un tamaño legible`);
                                assert.ok(['3', '99', '99+'].includes(row.badgeText), `${context}: contador compacto`);
                            } else {
                                assert.equal(row.labelOverflow, false, `${context}: etiqueta completa`);
                                assert.ok(row.icon.right <= row.label.left && row.label.right <= row.badge.left, `${context}: columnas separadas`);
                                if (row.caret) assert.ok(row.badge.right <= row.caret.left, `${context}: badge y caret separados`);
                            }
                        }
                    }
                }
            }
        }
    } finally { await browser.close(); }
});
