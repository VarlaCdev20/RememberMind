import { test } from 'node:test';
import assert from 'node:assert/strict';
import { crearRegistroPersonal } from '../../resources/frontend/scripts/modules/personal-registration.js';

test('captura conserva letras internacionales y no altera contraseñas', () => {
    const form = crearRegistroPersonal();
    const nombre = {dataset:{captura:'letras'},value:'maría9 muñoz',selectionStart:11,setSelectionRange(a){this.cursor=a;}};
    form.normalizar({target:nombre}); assert.equal(nombre.value,'MARÍA MUÑOZ'); assert.equal(nombre.cursor,10);
    const clave = {dataset:{},value:'Temporal#Abc123'}; form.normalizar({target:clave}); assert.equal(clave.value,'Temporal#Abc123');
});

test('vista previa local fuerza MIME permitido y libera el archivo al cerrar', async () => {
    const form = crearRegistroPersonal();
    form.retenerArchivo({target:{dataset:{documentoPersonal:'CI'},files:[new Blob(['%PDF-1.4'])]}});
    form.abrirVistaDocumento({dataset:{documentoCodigo:'CI',documentoMime:'text/html',documentoNombre:'Identidad'}});
    assert.equal(form.vistaDocumento,null);
    form.abrirVistaDocumento({dataset:{documentoCodigo:'CI',documentoMime:'application/pdf',documentoNombre:'Identidad'}});
    const url=form.vistaDocumento.url;
    assert.match(url,/^blob:/); assert.equal((await fetch(url)).headers.get('content-type'),'application/pdf');
    form.cerrarVistaDocumento(); assert.equal(form.vistaDocumento,null); await assert.rejects(fetch(url));
    form.actualizarCatalogo([]); assert.deepEqual(form.archivosLocales,{});
});
