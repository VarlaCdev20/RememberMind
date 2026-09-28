import { afterEach, test } from 'node:test';
import assert from 'node:assert/strict';
import documentosAdulto from '../../resources/frontend/scripts/modules/documentos-adulto.js';

afterEach(() => { delete globalThis.Swal; });

function formulario({ nombre = 'Documento', tipo = 'MEDICO', archivo } = {}) {
    let envios = 0;
    const campos = {
        '[name=nombre]': { value: nombre },
        '[name=tipo_documento]': { value: tipo },
        '[name=archivo]': { files: archivo ? [archivo] : [] },
    };
    return {
        querySelector: selector => campos[selector],
        submit() { envios++; },
        get envios() { return envios; },
    };
}

test('subida válida envía los campos reales y evita un segundo envío', async () => {
    globalThis.Swal = { fire: async () => ({ isConfirmed: true }) };
    const estado = documentosAdulto('2026-09-10');
    estado.abrirRegistro();
    const form = formulario({ archivo: { name: 'documento.pdf', size: 100 } });
    const event = { target: form, preventDefault() {} };
    await estado.validarDocumento(event);
    await estado.validarDocumento(event);
    assert.equal(form.envios, 1);
    assert.deepEqual(estado.errors, {});
});

test('editar conserva código y fecha y permite guardar sin reemplazar archivo', async () => {
    globalThis.Swal = { fire: async () => ({ isConfirmed: true }) };
    const estado = documentosAdulto('2026-09-10');
    estado.abrirEdicion({ cod_doc_am: 'DOC_001', nombre: 'Informe', tipo_documento: 'MEDICO', fecha_subida: '2026-09-09T00:00:00.000000Z' });
    assert.equal(estado.docData.id, 'DOC_001');
    assert.equal(estado.docData.fecha_doc, '2026-09-09');
    const form = formulario();
    await estado.validarDocumento({ target: form, preventDefault() {} });
    assert.equal(form.envios, 1);
});

test('campos vacíos y archivo inválido impiden enviar', async () => {
    globalThis.Swal = { fire: async () => ({ isConfirmed: true }) };
    const estado = documentosAdulto('2026-09-10');
    const form = formulario({ nombre: '', tipo: '', archivo: { name: 'script.exe', size: 100 } });
    await estado.validarDocumento({ target: form, preventDefault() {} });
    assert.equal(form.envios, 0);
    assert.deepEqual(Object.keys(estado.errors), ['nom_doc', 'tipo_doc', 'archivo']);
});

test('cancelar confirmación no envía ni bloquea el formulario', async () => {
    globalThis.Swal = { fire: async () => ({ isConfirmed: false }) };
    const estado = documentosAdulto('2026-09-10');
    estado.abrirEdicion({ cod_doc_am: 'DOC_001' });
    const form = formulario();
    await estado.validarDocumento({ target: form, preventDefault() {} });
    assert.equal(form.envios, 0);
    assert.equal(estado.cargando, false);
});
