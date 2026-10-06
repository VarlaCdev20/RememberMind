import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { diasDelMes, rmCalendario, rmSelector } from '../../resources/frontend/scripts/modules/controles-institucionales.js';

test('calendario alinea lunes, respeta febrero y años bisiestos', () => {
    assert.equal(diasDelMes(2026, 5)[0], '2026-06-01');
    assert.equal(diasDelMes(2024, 1).filter(Boolean).length, 29);
    assert.equal(diasDelMes(1900, 1).filter(Boolean).length, 28);
    assert.equal(diasDelMes(2000, 1).at(-1), '2000-02-29');
});

test('calendario conserva ISO, límites inclusivos y cruces de año', () => {
    const calendar = rmCalendario({ min: '2026-10-01', max: '2026-10-06', value: '2026-10-03' });
    assert.equal(calendar.allowed('2026-10-01'), true);
    assert.equal(calendar.allowed('2026-10-06'), true);
    assert.equal(calendar.allowed('2026-10-07'), false);
    assert.equal(calendar.allowed(null), false);
    calendar.year = 2026; calendar.month = 0; calendar.changeMonth(-1);
    assert.equal(calendar.year, 2025); assert.equal(calendar.month, 11);
    const events = [];
    calendar.$refs = { native: { dispatchEvent: e => events.push(e.type) }, trigger: { focus() {} } };
    calendar.$nextTick = callback => callback();
    globalThis.FocusEvent ??= class extends Event {};
    calendar.choose('2026-10-07');
    assert.equal(calendar.value, '2026-10-03');
    calendar.choose('2026-10-05');
    assert.equal(calendar.value, '2026-10-05');
    assert.equal(calendar.$refs.native.value, '2026-10-05');
    assert.deepEqual(events, ['input', 'change', 'blur']);
});

test('selector busca sin acentos y mantiene selección múltiple', () => {
    const selector = rmSelector(['A']);
    selector.options = [{ value: 'A', label: 'Aprobación' }, { value: 'B', label: 'Admisión' }];
    selector.$refs = { native: { multiple: true, options: [{ value: 'A' }, { value: 'B' }], dispatchEvent() {} } };
    selector.$nextTick = callback => callback();
    globalThis.FocusEvent ??= class extends Event {};
    selector.search = 'admision';
    assert.deepEqual(selector.filtered.map(o => o.value), ['B']);
    selector.choose(selector.options[1]);
    assert.deepEqual(selector.value, ['A', 'B']);
    selector.choose(selector.options[0]);
    assert.deepEqual(selector.value, ['B']);
    assert.equal(selector.label, 'Admisión');
});

function temaFixture(saved = 'dark') {
    const listeners = new Map();
    const root = () => ({ dataset: {}, style: {}, classList: { dark: false, toggle(name, active) { this[name] = active; } } });
    const document = { documentElement: root(), readyState: 'complete', querySelectorAll: () => [], addEventListener(name, fn) { listeners.set(name, fn); } };
    const storage = new Map(saved ? [['remembermind-theme', saved]] : []);
    const window = { matchMedia: () => ({ matches: false, addEventListener() {} }), dispatchEvent() {}, addEventListener(name, fn) { listeners.set(name, fn); } };
    const context = vm.createContext({ document, window, localStorage: { getItem: k => storage.get(k), setItem: (k,v) => storage.set(k,v) }, CustomEvent: class { constructor(type, options) { this.type = type; this.detail = options.detail; } } });
    vm.runInContext(readFileSync(new URL('../../resources/frontend/scripts/utilities/modo-oscuro.js', import.meta.url), 'utf8'), context);
    return { document, window, root, storage, listeners };
}

test('tema se conserva después de reemplazar el documento al navegar', () => {
    const f = temaFixture('dark');
    assert.equal(f.document.documentElement.classList.dark, true);
    f.document.documentElement = f.root();
    f.listeners.get('livewire:navigated')();
    assert.equal(f.document.documentElement.dataset.theme, 'dark');
    assert.equal(f.document.documentElement.style.colorScheme, 'dark');
    f.window.RememberMindTheme.toggle();
    assert.equal(f.storage.get('remembermind-theme'), 'light');
    f.document.documentElement = f.root();
    f.listeners.get('livewire:navigated')();
    assert.equal(f.document.documentElement.dataset.theme, 'light');
});

test('tema sincroniza otras ventanas e ignora preferencias inválidas', () => {
    const f = temaFixture('invalid');
    assert.equal(f.document.documentElement.dataset.theme, 'light');
    f.storage.set('remembermind-theme', 'dark');
    f.listeners.get('storage')({ key: 'remembermind-theme' });
    assert.equal(f.document.documentElement.dataset.theme, 'dark');
});
