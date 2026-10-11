import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const advisoryPendiente = 'https://github.com/advisories/GHSA-vfj7-8cjw-p6xm';
const cadenaBuild = new Set(['braces', 'chokidar', 'fast-glob', 'micromatch', 'tailwindcss', '@tailwindcss/typography']);

// Control temporal y visible de una deuda de build; no certifica su corrección.
export function verificarAuditoria(informe, lock, fecha = new Date()) {
    if (informe.error || informe.auditReportVersion !== 2 || ! informe.metadata || ! informe.vulnerabilities) {
        throw new Error('La auditoría no produjo un informe válido.');
    }
    const afectados = Object.values(informe.vulnerabilities);
    if (informe.metadata.vulnerabilities?.total !== afectados.length) {
        throw new Error('La auditoría contiene un inventario incompleto.');
    }
    if (afectados.length === 0) return { pendientes: 0 };
    if (fecha >= new Date('2026-11-11T00:00:00Z')) {
        throw new Error('Venció la revisión temporal de braces. Revisar parche upstream o migración de Tailwind.');
    }
    for (const paquete of afectados) {
        if (paquete.name === '@tailwindcss/typography'
            && (lock.packages?.['node_modules/@tailwindcss/typography']?.version !== '0.5.19'
                || paquete.via?.length !== 1 || paquete.via[0] !== 'tailwindcss')) {
            throw new Error('La propagación de typography cambió y exige nueva revisión.');
        }
        if (! cadenaBuild.has(paquete.name) || paquete.severity !== 'high' || ! paquete.nodes?.length) {
            throw new Error(`Alerta nueva o no contemplada: ${paquete.name}.`);
        }
        for (const nodo of paquete.nodes) {
            if (lock.packages?.[nodo]?.dev !== true) {
                throw new Error(`La dependencia afectada alcanza ejecución: ${nodo}.`);
            }
        }
        for (const causa of paquete.via) {
            const conocida = typeof causa === 'string'
                ? cadenaBuild.has(causa) && informe.vulnerabilities[causa]
                : causa.url === advisoryPendiente && causa.dependency === 'braces' && causa.severity === 'high';
            if (! conocida) throw new Error(`Advisory inesperado en ${paquete.name}.`);
        }
    }
    const braces = informe.vulnerabilities.braces;
    if (! braces?.via.some(causa => typeof causa !== 'string' && causa.url === advisoryPendiente)
        || lock.packages?.['node_modules/braces']?.version !== '3.0.3'
        || lock.packages?.['node_modules/tailwindcss']?.version !== '3.4.19') {
        throw new Error('La cadena de build cambió y exige nueva revisión.');
    }

    return { pendientes: 1, advisory: advisoryPendiente, paquetes: afectados.length };
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
    try {
        const leer = path => JSON.parse(readFileSync(path, 'utf8').replace(/^\uFEFF/, ''));
        const resultado = verificarAuditoria(leer(process.argv[2]), leer('package-lock.json'));
        if (resultado.pendientes) {
            console.warn(`PENDIENTE: ${resultado.advisory}; ${resultado.paquetes} paquetes de build afectados. Revisión vence 2026-11-11.`);
        } else {
            console.log('Auditoría completa sin alertas.');
        }
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
}
