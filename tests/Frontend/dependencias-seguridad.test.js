import test from 'node:test';
import assert from 'node:assert/strict';
import { verificarAuditoria } from '../../scripts/verificar-auditoria-npm.mjs';

const fixture = () => ({
    informe: { auditReportVersion: 2, metadata: { vulnerabilities: { total: 1 } }, vulnerabilities: { braces: {
        name: 'braces', severity: 'high', nodes: ['node_modules/braces'],
        via: [{ dependency: 'braces', severity: 'high', url: 'https://github.com/advisories/GHSA-vfj7-8cjw-p6xm' }],
    } } },
    lock: { packages: { 'node_modules/braces': { dev: true, version: '3.0.3' }, 'node_modules/tailwindcss': { version: '3.4.19' } } },
});
const vigente = new Date('2026-10-11T00:00:00Z');

function fixtureLinux() {
    const { informe, lock } = fixture();
    informe.vulnerabilities.tailwindcss = { name: 'tailwindcss', severity: 'high', nodes: ['node_modules/tailwindcss'], via: ['braces'] };
    informe.vulnerabilities['@tailwindcss/typography'] = { name: '@tailwindcss/typography', severity: 'high', nodes: ['node_modules/@tailwindcss/typography'], via: ['tailwindcss'] };
    informe.metadata.vulnerabilities.total = 3;
    lock.packages['node_modules/tailwindcss'].dev = true;
    lock.packages['node_modules/@tailwindcss/typography'] = { dev: true, version: '0.5.19' };
    return { informe, lock };
}

test('la propagación Linux a typography conserva el mismo advisory de build', () => {
    const { informe, lock } = fixtureLinux();
    assert.equal(verificarAuditoria(informe, lock, vigente).pendientes, 1);
    lock.packages['node_modules/@tailwindcss/typography'].dev = false;
    assert.throws(() => verificarAuditoria(informe, lock, vigente), /alcanza ejecución/);
});

test('typography no permite otra causa ni otra versión por la excepción de braces', () => {
    const { informe, lock } = fixtureLinux();
    informe.vulnerabilities['@tailwindcss/typography'].via = ['braces'];
    assert.throws(() => verificarAuditoria(informe, lock, vigente), /propagación/);
    informe.vulnerabilities['@tailwindcss/typography'].via = ['tailwindcss'];
    lock.packages['node_modules/@tailwindcss/typography'].version = 'otra';
    assert.throws(() => verificarAuditoria(informe, lock, vigente), /propagación/);
});

test('la deuda conocida se informa con su advisory y nunca como cero', () => {
    const { informe, lock } = fixture();
    assert.equal(verificarAuditoria(informe, lock, vigente).pendientes, 1);
});
test('una dependencia afectada de ejecución bloquea el control', () => {
    const { informe, lock } = fixture();
    lock.packages['node_modules/braces'].dev = false;
    assert.throws(() => verificarAuditoria(informe, lock, vigente), /alcanza ejecución/);
});
test('un advisory nuevo o la expiración bloquean el control', () => {
    const { informe, lock } = fixture();
    assert.throws(() => verificarAuditoria(informe, lock, new Date('2026-11-11T00:00:00Z')), /Venció/);
    informe.vulnerabilities.braces.via.push({ url: 'https://github.com/advisories/nuevo', dependency: 'braces', severity: 'critical' });
    assert.throws(() => verificarAuditoria(informe, lock, vigente), /inesperado/);
});
test('una auditoría fallida o un cambio de cadena no se aceptan', () => {
    const { informe, lock } = fixture();
    assert.throws(() => verificarAuditoria({ error: 'network' }, lock, vigente), /válido/);
    assert.throws(() => verificarAuditoria({ ...informe, vulnerabilities: {} }, lock, vigente), /incompleto/);
    lock.packages['node_modules/braces'].version = 'otra';
    assert.throws(() => verificarAuditoria(informe, lock, vigente), /cambió/);
});
