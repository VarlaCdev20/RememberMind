import { test } from 'node:test';
import assert from 'node:assert/strict';
const listeners = new Map();
globalThis.window = { addEventListener: (key, handler) => listeners.set(key, handler), removeEventListener: key => listeners.delete(key), confirm: () => false };
globalThis.document = { addEventListener: (key, handler) => listeners.set(key, handler), removeEventListener: key => listeners.delete(key) };
await import('../../resources/frontend/scripts/modules/wound-evidence.js');
await import('../../resources/frontend/scripts/modules/clinical-form-feedback.js');
function state() { const item = window.rmWoundWorkspace(); item.$refs = {}; item.$nextTick = callback => callback(); item.$wire = { fotografias: [], revisionFotos: 0, $set(field, value) { this[field] = value; } }; return item; }

test('materiales conservan texto libre y selección no persiste ni pierde otros campos', () => {
    const item = state(); item.$wire.materialesLesion = 'Material libre'; item.$wire.respuestaLesion = 'Respuesta conservada';
    item.toggleMaterial('Gasa estéril'); assert.equal(item.$wire.materialesLesion, 'Material libre; Gasa estéril');
    item.toggleMaterial('Gasa estéril'); assert.equal(item.$wire.materialesLesion, 'Material libre');
    item.chooseText('olorLesion', 'Ausente'); assert.equal(item.$wire.olorLesion, 'Ausente');
    item.chooseText('codResidente', 'AJENO'); assert.equal(item.$wire.codResidente, undefined);
    assert.equal(item.$wire.respuestaLesion, 'Respuesta conservada'); assert.equal(item.clinicalFormDirty, true);
});

test('listo para registrar indica captura completa, no éxito ni juicio clínico', () => {
    const item = state(); Object.assign(item.$wire, {lesionId:'HER_QA', aspectoLesion:'Granulación', accionLesion:'Cambio de apósito', largoLesion:null, anchoLesion:null, profundidadLesion:null, dolorLesion:null});
    assert.equal(item.curationReady(), true);
    for (const invalid of [-1, 11, 2.5, NaN]) { item.$wire.dolorLesion=invalid; assert.equal(item.curationReady(), false); }
    item.$wire.dolorLesion=0; assert.equal(item.curationReady(), true);
    item.$wire.largoLesion=Infinity; assert.equal(item.curationReady(), false);
    item.$wire.largoLesion=2; item.uploading=true; assert.equal(item.curationReady(), false);
    item.uploading=false; item.uploadError='Falló la carga'; assert.equal(item.curationReady(), false);
    item.uploadError=''; item.$wire.aspectoLesion=''; assert.equal(item.curationReady(), false);
    assert.equal(item.curationToast, false);
});

test('gráfica requiere dos medidas históricas válidas y conserva gaps', () => {
    for (const rows of [[], [{area:2}], [{area:null},{area:2}], [{area:Infinity},{area:NaN}]]) assert.equal(window.rmWoundEvolution(rows).enough,false);
    const chart=window.rmWoundEvolution([{area:4},{area:null},{area:2}]);
    assert.equal(chart.enough,true); assert.equal(chart.visibleRows[1].area,null);
    const item=state(); assert.equal(item.hasArea(null,2),false); assert.equal(item.hasArea('',2),false); assert.equal(item.hasArea(0,2),true);
});

test('carga fallida conserva captura y no crea preview de éxito', () => {
    const item = state(); item.pendingFiles = [new Blob(['sintético'])];
    item.$wire.uploadMultiple = (name, files, success, failure, progress) => { progress({detail:{progress:64}}); failure(); };
    item.sendFiles();
    assert.equal(item.progress,64); assert.equal(item.uploading,false); assert.equal(item.uploadError,'Error al cargar la fotografía.');
    assert.equal(item.pendingFiles.length,1); assert.equal(item.previews.length,0); assert.equal(item.clinicalFormDirty,true);
});

test('callback de transporte sin validación servidor no simula éxito', () => {
    const item = state(); item.pendingFiles = [new Blob(['inválido'])];
    item.$wire.uploadMultiple = (name, files, success) => success(); item.sendFiles();
    assert.match(item.uploadError,/No se pudieron añadir/); assert.equal(item.previews.length,0);
});

test('reemplazo inválido conserva fotografía anterior', () => {
    const item = state(); const old = new Blob(['anterior']); const oldUrl = URL.createObjectURL(old);
    item.previews = [{index:0,url:oldUrl,name:'anterior.png'}]; item.$wire.fotografias = [{}];
    item.$wire.uploadMultiple = (name, files, success) => success();
    item.replacePhoto(item.previews[0],{target:{files:[new Blob(['incorrecto'])],value:'selección'}});
    assert.equal(item.previews[0].url,oldUrl); assert.match(item.uploadError,/No se pudieron añadir/); item.clearPreviews();
});

test('galería navega con límites y devuelve foco al cerrar', () => {
    const item = state(); let focused = false;
    item.viewGallery([{codigo:'A'},{codigo:'B'}],0,{currentTarget:{focus:()=>{focused=true;}}});
    item.step(-1); assert.equal(item.current,1); item.step(1); assert.equal(item.current,0);
    item.closeGallery(); assert.equal(item.viewerOpen,false); assert.equal(focused,true);
});

test('salida y navegación protegen campos y fotografías sin guardar', () => {
    const item = state(); item.clinicalFormOpen=true; item.clinicalFormDirty=true; item.init();
    const event = {prevented:false,preventDefault(){this.prevented=true;}};
    listeners.get('beforeunload')(event); assert.equal(event.prevented,true);
    event.prevented=false; listeners.get('livewire:navigate')(event); assert.equal(event.prevented,true);
    item.clinicalFormDirty=false; event.prevented=false; listeners.get('beforeunload')(event); assert.equal(event.prevented,false);
    item.destroy(); assert.equal(listeners.size,0);
});

test('área derivada conserva cero y no muestra valores no finitos', () => {
    const item = state();
    assert.equal(item.areaPreview(0, 2), '0.00');
    assert.equal(item.areaPreview(2.5, 1.5), '3.75');
    assert.equal(item.areaPreview(null, 2), 'Sin medidas');
    for (const value of [Infinity, NaN, 1e300, -1]) assert.equal(item.areaPreview(value, 2), 'Revisa las medidas');
});

test('formulario emergente abre cada acción, enfoca su campo y no reinicia una captura abierta', () => {
    for (const [operation, selector] of [['nueva-herida', '#nueva-nuevaHeridaTipo'], ['curacion', '#curacion-herida'], ['cierre-herida', '#herida-resultado']]) {
        const item = state(); let focused;
        item.$el = {querySelector: () => null};
        item.$root = {querySelector: target => ({focus: () => { focused = target; }})};
        const trigger = {focus() {}};
        item.openForm({currentTarget: trigger}, operation);
        assert.equal(item.clinicalFormOpen, true);
        assert.equal(item.nueva, operation === 'nueva-herida');
        assert.equal(focused, selector);
        assert.equal(item.clinicalReturnTarget, trigger);
        item.clinicalFormDirty = true;
        item.openForm({currentTarget: {}}, 'curacion');
        assert.equal(item.clinicalFormDirty, true);
        assert.equal(item.clinicalOperation, operation);
    }
});

test('cerrar modal usa protección compartida y devuelve foco; carga en curso impide cerrar', () => {
    const item = Object.assign(window.rmClinicalFormFeedback(), state()); let focused = false;
    item.$el = {closest: () => ({querySelector: () => null})};
    item.clinicalReturnTarget = {isConnected: true, focus: () => { focused = true; }};
    item.clinicalFormOpen = true; item.clinicalFormDirty = true;
    item.closeWoundForm();
    assert.equal(item.clinicalFormOpen, true);
    assert.equal(item.clinicalDiscardOpen, true);
    assert.equal(focused, false);
    item.clinicalFormDirty = false; item.uploading = true;
    item.closeWoundForm(); assert.equal(item.clinicalFormOpen, true);
    item.uploading = false; item.clinicalDiscardOpen = false;
    item.closeWoundForm(); assert.equal(item.clinicalFormOpen, false); assert.equal(focused, true);
});

test('popup del selector abre captura directamente y conserva resultado hasta cerrar', () => {
    const item = Object.assign(window.rmClinicalFormFeedback(), state()); let closed = 0;
    item.$root = {querySelector: selector => selector === '[data-wound-popup]'
        ? {dataset: {woundPopup: 'true', woundOperation: 'curacion'}} : {focus() {}}};
    item.$el = {closest: () => ({querySelector: () => null})};
    item.$wire.$dispatch = event => { assert.equal(event, 'heridas-popup-cerrado'); closed++; };
    item.init(); assert.equal(item.clinicalFormOpen, true); assert.equal(item.clinicalOperation, 'curacion');
    assert.equal(item.$wire.mostrarFormularioHerida, true);
    item.saved(); assert.equal(item.clinicalFormOpen, true); assert.equal(closed, 0);
    item.closeWoundForm(); assert.equal(closed, 1); assert.equal(item.clinicalFormOpen, false);
    assert.equal(item.$wire.mostrarFormularioHerida, false);
    item.destroy();
});

test('pasar a nueva herida requiere confirmar descarte y esperar autorización del servidor', async () => {
    const item = state(); let prepared = 0;
    item.$wire.prepararNuevaHerida = async () => { prepared++; };
    item.clinicalFormOpen = true; item.clinicalFormDirty = true;
    await item.startNewWound({currentTarget: {}}); assert.equal(prepared, 0);
    item.clinicalFormDirty = false;
    item.$root = {querySelector: () => ({focus() {}})};
    await item.startNewWound({currentTarget: {}});
    assert.equal(prepared, 1); assert.equal(item.nueva, true); assert.equal(item.clinicalFormOpen, true);
});

test('identificación confirmada permanece abierta y primera curación espera respuesta autorizada', async () => {
    const item = state(); item.nueva = true; item.isPopup = false; item.clinicalFormDirty = true;
    item.saved(); assert.equal(item.clinicalFormOpen, true); assert.equal(item.$wire.mostrarFormularioHerida, true);
    assert.equal(item.clinicalFormDirty, false);
    item.$wire.prepararPrimeraCuracion = async () => { throw new Error('Denegado'); };
    await assert.rejects(item.startFirstCuration(), /Denegado/); assert.equal(item.nueva, true);
    item.$wire.prepararPrimeraCuracion = async () => {}; item.$root = {querySelector: () => ({focus() {}})};
    await item.startFirstCuration(); assert.equal(item.nueva, false); assert.equal(item.clinicalOperation, 'curacion');
});
