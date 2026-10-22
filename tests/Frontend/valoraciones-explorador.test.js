import { test } from 'node:test';
import assert from 'node:assert/strict';
import { rmAvatarGuia, rmGuiaVisual, posesGuia, mensajeErrorValoracion } from '../../resources/frontend/scripts/modules/valoraciones-explorador.js';
import { rmCalendario, rmSelector } from '../../resources/frontend/scripts/modules/controles-institucionales.js';

test('avatar admite ocho estados, pausa voluntaria y rechazo de poses desconocidas', () => {
    const avatar = rmAvatarGuia('saludo', 'Ayuda contextual');
    assert.equal(posesGuia.length, 8);
    for (const pose of posesGuia) { avatar.recibir(pose, pose); assert.equal(avatar.pose, pose); }
    avatar.recibir('diagnostico', 'no autorizado'); assert.equal(avatar.pose, 'descanso');
    avatar.pausar(); avatar.recibir('exito', 'mensaje'); assert.equal(avatar.pose, 'descanso');
    avatar.pausar(); assert.equal(avatar.pose, 'saludo'); assert.equal(avatar.texto, 'Ayuda contextual');
});

test('storyboard limita avance, permite retroceder y reinicia al cambiar de sección', () => {
    const guia = rmGuiaVisual(); guia.mover(-1, 4); assert.equal(guia.paso, 0);
    guia.mover(10, 4); assert.equal(guia.paso, 3); guia.mover(-1, 4); assert.equal(guia.paso, 2);
    guia.seleccionar('materiales'); assert.equal(guia.paso, 0); assert.equal(guia.tab, 'materiales');
});

test('fallos de transporte conservan incertidumbre del guardado', () => {
    assert.match(mensajeErrorValoracion(419), /sesión caducó/);
    assert.match(mensajeErrorValoracion(409), /catálogo o la fuente cambió/);
    assert.match(mensajeErrorValoracion(0), /no se pudo confirmar/i);
    assert.match(mensajeErrorValoracion(500), /consulta el historial/);
});

test('controles institucionales no cambian datos mientras están bloqueados', () => {
    const selector = rmSelector('A');
    selector.$refs = { native: { disabled: true } };
    selector.choose({ value: 'B' }); assert.equal(selector.value, 'A');
    const calendario = rmCalendario({ value: '2026-10-22' });
    calendario.$refs = { native: { readOnly: true } };
    calendario.choose('2026-10-21'); assert.equal(calendario.value, '2026-10-22');
    calendario.show(); assert.equal(calendario.open, false);
});
