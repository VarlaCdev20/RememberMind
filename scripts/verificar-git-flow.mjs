import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';

const tarea = /^feature\/[a-z0-9]+(?:-[a-z0-9]+)*$/;
const entrega = /^(release|hotfix)\/[a-z0-9]+(?:[.-][a-z0-9]+)*$/;

export function validarRamas(base, origen) {
    if (base === 'main' && entrega.test(origen)) return;
    if (base === 'develop' && (tarea.test(origen) || entrega.test(origen) || origen === 'main')) return;
    throw new Error(`Integración no permitida: ${origen} -> ${base}. Usa feature -> develop y release/hotfix -> main.`);
}

export function validarMensajes(mensajes) {
    const patron = /^(feat|fix|refactor|perf|test|docs|build|ci|chore|revert)(\([a-z0-9-]+\))?!?: \S.+$/u;
    for (const mensaje of mensajes) {
        if (mensaje.length > 100 || !patron.test(mensaje)) {
            throw new Error('Los commits nuevos deben usar tipo(alcance): descripción concreta, máximo 100 caracteres.');
        }
    }
}

function ejecutar() {
    const evento = JSON.parse(readFileSync(process.env.GITHUB_EVENT_PATH, 'utf8'));
    const pr = evento.pull_request;
    if (!pr) throw new Error('La comprobación requiere un evento pull_request.');
    validarRamas(pr.base.ref, pr.head.ref);
    // Las entregas iniciales conservan el historial anterior; no se reescriben sus mensajes.
    if (tarea.test(pr.head.ref) || pr.head.ref.startsWith('hotfix/')) {
        if (![pr.base.sha, pr.head.sha].every(sha => /^[a-f0-9]{40}$/i.test(sha))) {
            throw new Error('SHA de comparación inválido.');
        }
        const mensajes = execFileSync('git', ['log', '--no-merges', '--format=%s', `${pr.base.sha}..${pr.head.sha}`], { encoding: 'utf8' })
            .trim().split('\n').filter(Boolean);
        validarMensajes(mensajes);
    }
    console.log('Git Flow: ramas y mensajes de commits nuevos aprobados.');
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) ejecutar();
