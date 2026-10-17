import { test } from 'node:test';
import assert from 'node:assert/strict';
globalThis.window ??= {};
const { freeTextSelect } = await import('../../resources/frontend/scripts/modules/free-text-select.js');

function field(value = '') {
    const item = freeTextSelect(value, ['Abrasión', 'Herida quirúrgica']);
    item.$watch = (key, callback) => { item.watcher = callback; };
    item.$refs = { custom: { focus() { item.focused = true; } } };
    item.$nextTick = callback => callback();
    item.init();
    return item;
}

test('opciones y Otro conservan un único texto libre, sin persistir el centinela', () => {
    const item = field();
    assert.equal(item.choice, '');
    assert.equal(item.value, '');
    item.choose('Abrasión');
    assert.equal(item.value, 'Abrasión');
    item.choose('__custom');
    item.watcher(item.value);
    assert.equal(item.value, '');
    assert.equal(item.choice, '__custom');
    assert.equal(item.focused, true);
    item.write('Descripción libre');
    item.watcher(item.value);
    assert.equal(item.value, 'Descripción libre');
    item.choose('Herida quirúrgica');
    item.choose('__custom');
    assert.equal(item.value, 'Descripción libre');
});

test('recuperación y reinicio del backend sincronizan selección y escritura', () => {
    const item = field('Texto previo conservado');
    assert.equal(item.choice, '__custom');
    assert.equal(item.customText, 'Texto previo conservado');
    item.value = 'Abrasión'; item.watcher(item.value);
    assert.equal(item.choice, 'Abrasión');
    item.value = ''; item.watcher(item.value);
    assert.equal(item.choice, '');
    assert.equal(item.customText, '');
    item.choose('VALOR_AJENO');
    assert.equal(item.value, '');
});

test('escribir una opción conocida en Otro mantiene el campo libre y admite borrarlo', () => {
    const item = field(); item.choose('__custom');
    for (const text of ['Abrasión', '']) {
        item.write(text); item.watcher(item.value);
        assert.equal(item.choice, '__custom');
        assert.equal(item.value, text);
    }
});

test('buscar ejemplos ignora acentos y no modifica la captura', () => {
    const item = field('Herida quirúrgica');
    item.search = 'ABRASION';
    assert.deepEqual(item.visibleOptions, ['Abrasión']);
    assert.equal(item.value, 'Herida quirúrgica');
    item.search = 'quirurgica herida';
    assert.deepEqual(item.visibleOptions, ['Herida quirúrgica']);
    item.search = 'sin coincidencia';
    assert.deepEqual(item.visibleOptions, []);
    item.search = '  ';
    assert.equal(item.visibleOptions.length, 2);
});

test('elegir ilustración conserva el valor canónico, avisa cambios y devuelve foco', () => {
    const item = field();
    let event, focused = false;
    item.$dispatch = name => { event = name; };
    item.$refs.native = { focus: () => { focused = true; } };
    const panel = { open: true };
    const trigger = { closest: () => panel };
    item.search = 'abra';
    item.chooseIllustration('Abrasión', trigger);
    assert.equal(item.value, 'Abrasión');
    assert.equal(item.choice, 'Abrasión');
    assert.equal(panel.open, false);
    assert.equal(item.search, '');
    assert.equal(event, 'change');
    assert.equal(focused, true);
    item.chooseIllustration('FUERA_DE_LISTA', trigger);
    assert.equal(item.value, 'Abrasión');
    item.chooseIllustration('__custom', trigger);
    assert.equal(item.value, '');
    assert.equal(item.choice, '__custom');
    assert.equal(item.focused, true);
});
