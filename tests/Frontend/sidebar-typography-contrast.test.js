import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const manifest = JSON.parse(readFileSync(new URL('public/build/manifest.json', root), 'utf8'));
const css = readFileSync(new URL(`public/build/${manifest['resources/frontend/styles/app.css'].file}`, root), 'utf8');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

function luminance(rgb) {
    const [red, green, blue] = rgb.match(/\d+(?:\.\d+)?/g).slice(0, 3).map(Number).map(value => {
        const normalized = value / 255;
        return normalized <= .04045 ? normalized / 12.92 : ((normalized + .055) / 1.055) ** 2.4;
    });
    return red * .2126 + green * .7152 + blue * .0722;
}

function contrast(first, second) {
    const values = [luminance(first), luminance(second)].sort((a, b) => b - a);
    return (values[0] + .05) / (values[1] + .05);
}

test('la jerarquía del sidebar usa tinta cálida legible y conserva el acento activo', { skip: !executablePath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const role of ['superadmin', 'nursing']) {
            for (const theme of ['light', 'dark']) {
                await page.setContent(`<html class="${theme === 'dark' ? 'dark' : ''}" data-theme="${theme}"><head><style>${css}</style></head><body class="rm-shell" data-role="${role}"><aside class="rm-sidebar"><header class="rm-sidebar__header"><span class="rm-sidebar__brand-copy"><strong>RememberMind</strong></span></header><nav class="rm-sidebar__nav"><p class="rm-sidebar__group">institución</p><a class="rm-sidebar__item"><i class="rm-sidebar__icon"></i><span>Módulo</span></a><a class="rm-sidebar__subitem">Opción</a><a class="rm-sidebar__item is-active"><i class="rm-sidebar__icon"></i><span>Actual</span></a></nav></aside></body></html>`);
                const styles = await page.evaluate(() => {
                    const value = (selector, property) => getComputedStyle(document.querySelector(selector))[property];
                    return {
                        background: value('.rm-sidebar', 'backgroundColor'),
                        brand: value('.rm-sidebar__brand-copy strong', 'color'),
                        section: value('.rm-sidebar__group', 'color'),
                        module: value('.rm-sidebar__item:not(.is-active)', 'color'),
                        subitem: value('.rm-sidebar__subitem', 'color'),
                        activeBackground: value('.rm-sidebar__item.is-active', 'backgroundColor'),
                        activeText: value('.rm-sidebar__item.is-active', 'color'),
                        activeIcon: value('.rm-sidebar__item.is-active .rm-sidebar__icon', 'color'),
                    };
                });
                for (const key of ['brand', 'section', 'module', 'subitem']) {
                    assert.ok(contrast(styles[key], styles.background) >= 4.5, `${role} ${theme} ${key} legible`);
                }
                if (theme === 'light') {
                    assert.ok(contrast(styles.activeText, styles.activeBackground) >= 4.5, `${role} selección legible`);
                    const [red, green, blue] = styles.module.match(/\d+/g).slice(0, 3).map(Number);
                    assert.ok(red > green && green > blue, 'tinta cálida del contrato aprobado');
                }
                assert.notEqual(styles.activeIcon, styles.activeText, 'el icono mantiene el acento del rol');
            }
        }
    } finally { await browser.close(); }
});
