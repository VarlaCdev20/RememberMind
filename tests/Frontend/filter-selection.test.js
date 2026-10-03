import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const css = readFileSync(new URL('resources/frontend/styles/design-system/components/filters.css', root), 'utf8');
const nursingCss = readFileSync(new URL('resources/frontend/styles/design-system/patterns/nursing-module.css', root), 'utf8');
const script = readFileSync(new URL('resources/frontend/scripts/modules/filter-selection.js', root), 'utf8');
const browserPath = [
    process.env.PUPPETEER_EXECUTABLE_PATH,
    await puppeteer.executablePath(),
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
].find(path => path && existsSync(path));

test('los filtros elegidos destacan y vuelven al estado neutral al limpiarlos', { skip: !browserPath }, async () => {
    const browser = await puppeteer.launch({ headless: true, executablePath: browserPath });
    try {
        const page = await browser.newPage();
        await page.setContent(`
            <style>:root {
                --rm-surface-main: #f6f1ea; --rm-input-bg: #ddd1c6; --rm-border-soft: #c5b8ac;
                --rm-action-primary: #a9c1aa; --rm-action-primary-active: #88a68c;
                --rm-action-primary-ink: #536b5a; --rm-focus: #5a93bd;
                --rm-clinical: #4f7390; --rm-violet: #9276a2; --rm-cyan: #3d8b93;
                --rm-status-high: #d47a4d; --rm-text-primary: #302c2a;
            }</style>
            <div class="rm-nursing-shell"><main><section class="rm-filter-bar">
                <div class="rm-filter-bar__controls">
                    <input id="search" placeholder="Buscar residente">
                    <select id="state"><option value="">Todos</option><option value="PENDIENTE">Pendiente</option></select>
                    <input id="date" type="date">
                </div>
                <div class="rm-filter-bar__active"><div>
                    <span class="inline-flex">Búsqueda: Ana</span>
                    <span class="inline-flex">Estado: PENDIENTE</span>
                    <span class="inline-flex">Fecha: 01/10/2026</span>
                </div></div>
            </section></main></div>`);
        await page.addStyleTag({ content: css + nursingCss });
        await page.addScriptTag({ content: script, type: 'module' });
        const settleStyles = async () => {
            // Allow the filter's scheduled DOM update and style calculation first.
            await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
            await page.waitForFunction(() => document.getAnimations().every(animation =>
                animation.playState === 'finished' || animation.playState === 'idle'));
        };
        const inspect = () => page.evaluate(() => Object.fromEntries(['search', 'state', 'date'].map(id => {
            const element = document.getElementById(id);
            const style = getComputedStyle(element);
            return [id, {
                active: element.getAttribute('data-rm-filter-active'),
                tone: element.getAttribute('data-rm-filter-tone'),
                background: style.backgroundColor,
                border: style.borderTopColor,
                weight: style.fontWeight,
            }];
        })));
        await page.waitForFunction(() => document.getElementById('date').getAttribute('data-rm-filter-active') === 'false');
        await settleStyles();
        const neutral = await inspect();

        await page.evaluate(() => {
            for (const [id, value] of [['search', 'Ana'], ['state', 'PENDIENTE'], ['date', '2026-10-01']]) {
                const element = document.getElementById(id);
                element.value = value;
                element.dispatchEvent(new Event(id === 'search' ? 'input' : 'change', { bubbles: true }));
            }
        });
        await settleStyles();
        const selected = await inspect();
        for (const id of ['search', 'state', 'date']) {
            assert.equal(selected[id].active, 'true');
            assert.notEqual(selected[id].background, neutral[id].background, `${id} debe cambiar su fondo`);
            assert.notEqual(selected[id].border, neutral[id].border, `${id} debe cambiar su borde`);
            assert.equal(selected[id].weight, '700');
        }
        assert.equal(new Set(['search', 'state', 'date'].map(id => selected[id].background)).size, 3);
        assert.deepEqual(['search', 'state', 'date'].map(id => selected[id].tone), ['blue', 'violet', 'teal']);
        const chips = await page.$$eval('.rm-filter-bar__active .inline-flex', elements => elements.map(element => ({
            tone: element.getAttribute('data-rm-filter-tone'),
            background: getComputedStyle(element).backgroundColor,
        })));
        assert.deepEqual(chips.map(chip => chip.tone), ['blue', 'violet', 'teal']);
        assert.equal(new Set(chips.map(chip => chip.background)).size, 3);

        await page.focus('#state');
        await settleStyles();
        const focused = await page.$eval('#state', element => ({
            background: getComputedStyle(element).backgroundColor,
            shadow: getComputedStyle(element).boxShadow,
        }));
        assert.equal(focused.background, selected.state.background);
        assert.notEqual(focused.shadow, 'none');

        await page.setViewport({ width: 390, height: 700 });
        assert.equal((await inspect()).state.background, selected.state.background);

        await page.$eval('#search', element => element.setAttribute('aria-invalid', 'true'));
        await settleStyles();
        assert.notEqual((await inspect()).search.background, selected.search.background);
        await page.$eval('#search', element => element.removeAttribute('aria-invalid'));

        await page.evaluate(() => {
            const hooks = {};
            window.Livewire = { hook: (name, callback) => { hooks[name] = callback; } };
            document.dispatchEvent(new Event('livewire:init'));
            const date = document.getElementById('date');
            date.value = '';
            hooks['morph.updated']({ el: date });
        });
        await page.waitForFunction(() => document.getElementById('date').getAttribute('data-rm-filter-active') === 'false');

        await page.evaluate(() => {
            for (const id of ['search', 'state', 'date']) {
                const element = document.getElementById(id);
                element.value = '';
                element.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        await settleStyles();
        const cleared = await inspect();
        for (const id of ['search', 'state', 'date']) {
            assert.equal(cleared[id].active, 'false');
            assert.equal(cleared[id].tone, null);
            assert.equal(cleared[id].background, neutral[id].background);
        }
    } finally {
        await browser.close();
    }
});
