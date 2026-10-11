import assert from 'node:assert/strict';
import test from 'node:test';
import { validarRamas, validarMensajes } from './verificar-git-flow.mjs';

test('permite trabajo, entrega, hotfix y sincronización', () => {
    for (const [base, origen] of [['develop', 'feature/identidad-documentos'], ['main', 'release/base-tecnica-2026-10-11'], ['main', 'hotfix/acceso-enfermeria'], ['develop', 'hotfix/acceso-enfermeria'], ['develop', 'main']]) {
        assert.doesNotThrow(() => validarRamas(base, origen));
    }
});

test('rechaza saltarse develop o integrar ramas sin contrato', () => {
    for (const [base, origen] of [['main', 'feature/usuarios'], ['main', 'develop'], ['develop', 'CAMBIOS'], ['main', 'release/'], ['develop', 'feature/../main'], ['otra', 'feature/usuarios']]) {
        assert.throws(() => validarRamas(base, origen));
    }
});

test('exige mensajes concretos y convencionales para trabajo nuevo', () => {
    assert.doesNotThrow(() => validarMensajes(['fix(identidad): rechazar cuentas inactivas', 'ci: verificar ramas y commits', 'refactor!: retirar contrato obsoleto']));
    for (const mensaje of ['cambios', 'fix: ', 'feat: a', `docs: ${'x'.repeat(100)}`]) assert.throws(() => validarMensajes([mensaje]));
});
