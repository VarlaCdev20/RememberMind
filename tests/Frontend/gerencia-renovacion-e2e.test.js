import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, writeFileSync, readFileSync, rmSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve, dirname } from 'node:path';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { createServer } from 'node:net';
import puppeteer from 'puppeteer';

const root = resolve(new URL('../../', import.meta.url).pathname.replace(/^\/([A-Z]:)/i, '$1'));
const chrome = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p => p && existsSync(p));

test('Gerencia: vistas reales, cinco anchos, filtros y modales en SQL desechable', { timeout: 300000, skip: !chrome }, async t => {
    const db = join(tmpdir(), `remembermind-gerencia-${randomBytes(8).toString('hex')}_testing.sqlite`);
    writeFileSync(db, '', { flag: 'wx' });
    t.after(() => {
        assert.equal(resolve(dirname(db)), resolve(tmpdir()));
        const mensajes=existsSync(db+'.log')
            ? readFileSync(db+'.log','utf8').split('\n').filter(l=>l.includes('.ERROR:')).map(l=>l.slice(0,l.indexOf(' {')>0?l.indexOf(' {'):400))
            : [];
        mkdirSync(join(root,'output/auditoria-integral-20261025/gerencia-renovada'),{recursive:true});
        writeFileSync(join(root,'output/auditoria-integral-20261025/gerencia-renovada/servidor-errores.json'),JSON.stringify(mensajes,null,2));
        for (const p of [db, db+'-wal', db+'-shm', db+'.views', db+'.storage',db+'.log']) rmSync(p, { recursive: true, force: true });
    });
    const port = await new Promise((resolve, reject) => {
        const s = createServer(); s.on('error', reject); s.listen(0, '127.0.0.1', () => { const port = s.address().port; s.close(() => resolve(port)); });
    });
    const base = `http://127.0.0.1:${port}`;
    const token = randomBytes(24).toString('hex');
    const env = { ...process.env, APP_ENV:'testing', APP_URL:base, RM_GERENCIA_QA_DB:db, RM_GERENCIA_QA_TOKEN:token };
    const router = join(root,'tests/Support/ServidorGerenciaSintetico.php');
    const preparado = spawnSync('php',[router,'--preparar'],{ cwd:root, env, encoding:'utf8', timeout:45000, windowsHide:true });
    assert.equal(preparado.status,0,preparado.stderr+preparado.stdout);
    assert.match(preparado.stdout,/PREPARADO/);
    const server = spawn('php',['-S',`127.0.0.1:${port}`,'-t',join(root,'public'),router],{cwd:root,env,windowsHide:true,stdio:'ignore'});
    t.after(() => server.kill());
    let ready = false;
    for(let n=0;n<40;n++) {
        try { const r=await fetch(base+'/dashboard',{headers:{'X-RM-GERENCIA-QA':token}}); if(r.ok){ready=true;break;} } catch(e) {if(server.exitCode!==null)throw e;}
        await new Promise(r=>setTimeout(r,100));
    }
    assert.ok(ready,'El servidor aislado no inició.');
    const browser=await puppeteer.launch({headless:true,executablePath:chrome,protocolTimeout:process.env.RM_GERENCIA_QA_CORRECCION ? 30000 : 180000}); t.after(()=>browser.close());
    const page=await browser.newPage();
    await page.evaluateOnNewDocument(()=>{if(location.protocol==='http:') localStorage.setItem('remembermind-theme','light');});
    const errors=[]; page.on('pageerror',e=>errors.push(e.message ?? String(e)));
    const solicitudesFallidas=[];
    page.on('response', r=>{if(r.status()>=400)solicitudesFallidas.push({ruta:new URL(r.url()).pathname,estado:r.status()});});
    await page.setRequestInterception(true);
    page.on('request',r=>r.url().startsWith(base+'/') ? r.continue({headers:{...r.headers(),'X-RM-GERENCIA-QA':token}}) : r.abort());
    await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
    const out=join(root,'output/auditoria-integral-20261025/gerencia-renovada',process.env.RM_GERENCIA_QA_CORRECCION ? 'correccion-concurrente' : (process.env.RM_GERENCIA_QA_ACCESIBILIDAD ? 'aceptacion-accesibilidad' : (process.env.RM_GERENCIA_QA_RAPIDO ? 'depuracion-rapida' : ''))); mkdirSync(out,{recursive:true});
    t.after(async()=>{
        writeFileSync(join(out,'diagnostico.json'),JSON.stringify({errors,solicitudesFallidas},null,2));
        if(!page.isClosed())await page.screenshot({path:join(out,'ultimo-estado.png'),fullPage:true});
    });
    const go=async path=>{
        const r=await page.goto(base+path,{waitUntil:'networkidle0'}); assert.equal(r.status(),200,path);
        await page.waitForFunction(()=>window.Alpine && window.Livewire);
        await page.evaluate(()=>document.fonts.ready);
    };
    const accesibilidad=[];
    const verificarDialogo=async (id,ancho)=>{
        await page.waitForSelector(`#${id} [role="dialog"]`,{visible:true});
        await page.waitForFunction(id=>document.querySelector(`#${id} [role="dialog"]`)?.contains(document.activeElement),{},id);
        const estado=await page.$eval(`#${id} [role="dialog"]`,dialogo=>{
            const nombre=document.getElementById(dialogo.getAttribute('aria-labelledby'))?.textContent.trim();
            const descripcion=document.getElementById(dialogo.getAttribute('aria-describedby'))?.textContent.trim();
            const controles=[...dialogo.querySelectorAll('input:not([type="hidden"]),select,textarea,button')].filter(e=>e.getClientRects().length && !e.disabled);
            const sinNombre=controles.filter(e=>!e.labels?.length && !e.getAttribute('aria-label') && !e.getAttribute('aria-labelledby') && !e.textContent.trim()).map(e=>e.id||e.tagName);
            return {nombre,descripcion,sinNombre,controles:controles.length,overflow:document.documentElement.scrollWidth>innerWidth+1};
        });
        assert.ok(estado.nombre && estado.descripcion,`${id}: nombre y descripción accesibles`);
        assert.deepEqual(estado.sinNombre,[],`${id}: controles sin nombre`);
        assert.equal(estado.overflow,false,`${id}: reflow`);
        for(let n=0;n<estado.controles+2;n++){
            await page.keyboard.press('Tab');
            assert.equal(await page.$eval(`#${id} [role="dialog"]`,e=>e.contains(document.activeElement)),true,`${id}: Tab debe permanecer dentro`);
        }
        await page.keyboard.down('Shift'); await page.keyboard.press('Tab'); await page.keyboard.up('Shift');
        assert.equal(await page.$eval(`#${id} [role="dialog"]`,e=>e.contains(document.activeElement)),true,`${id}: Shift+Tab debe permanecer dentro`);
        accesibilidad.push({modal:id,ancho,...estado,focoConfinado:true});
    };
    const rutas=[['inicio','/dashboard'],['personal','/admin/personal-institucional'],['areas','/admin/areas-institucionales'],
        ['cobertura','/admin/cobertura-equipo'],['residentes','/admin/residentes'],['alertas','/admin/alertas-clinicas'],
        ['reportes','/admin/reportes/institucional'],['galeria','/admin/galeria'],['planificacion','/admin/turnos-asignaciones'],['contactos','/admin/familia-social/red-apoyo']];
    if(process.env.RM_GERENCIA_QA_AMPLIACION) {
        await go('/dashboard');
        assert.match(page.url(),/cambiar-contrasena-inicial/);
        await page.type('#password-temporal','TemporalGerencia#123');
        await page.type('#password-personal','PersonalGerencia#123');
        await page.type('#password-personal-confirmacion','PersonalGerencia#123');
        await Promise.all([page.waitForNavigation({waitUntil:'networkidle0'}),page.click('form[action$="/cambiar-contrasena-inicial"] button[type="submit"]')]);
        assert.match(page.url(),/dashboard$/);
        rutas.push(['documentacion','/admin/personal-documentacion']);
    }
    const evidencia=[];
    if (process.env.RM_GERENCIA_QA_CORRECCION) {
        const etapa = nombre => writeFileSync(join(out, 'etapa.json'), JSON.stringify({ etapa: nombre }));
        etapa('preparar dos pestañas');
        const archivo = join(dirname(db), `remembermind-correccion-${randomBytes(8).toString('hex')}.pdf`);
        writeFileSync(archivo, '%PDF-1.4\n1 0 obj <</Type/Catalog>> endobj\n%%EOF');
        t.after(() => rmSync(archivo, { force: true }));
        const otra = await browser.newPage();
        await otra.setRequestInterception(true);
        otra.on('request', r => r.url().startsWith(base + '/') ? r.continue({ headers: { ...r.headers(), 'X-RM-GERENCIA-QA': token } }) : r.abort());
        otra.on('pageerror', e => errors.push(e.message ?? String(e)));
        await otra.setViewport({ width: 390, height: 960 });
        await page.setViewport({ width: 390, height: 960 });
        for (const pestaña of [page, otra]) {
            await pestaña.bringToFront();
            etapa(pestaña === page ? 'abrir primer formulario' : 'abrir segundo formulario');
            const respuesta = await pestaña.goto(base + '/admin/usuarios/QA_DOC_TITULAR?tab=documentacion', { waitUntil: 'networkidle0' });
            assert.equal(respuesta.status(), 200);
            await pestaña.waitForSelector('button[wire\\:click="abrirSubida(\'CI\')"]', { visible: true });
            await pestaña.click('button[wire\\:click="abrirSubida(\'CI\')"]');
            await pestaña.waitForSelector('input[wire\\:model="archivoSubida"]', { visible: true });
            await (await pestaña.$('input[wire\\:model="archivoSubida"]')).uploadFile(archivo);
            await pestaña.waitForFunction(() => {
                const el = document.querySelector('input[wire\\:model="archivoSubida"]')?.closest('[wire\\:id]');
                return el && window.Livewire.find(el.getAttribute('wire:id')).get('archivoSubida');
            });
        }
        etapa('guardar primer formulario');
        await page.bringToFront();
        await page.click('form[wire\\:submit\\.prevent="subirArchivo"] button[type="submit"]');
        await page.waitForFunction(() => !document.querySelector('input[wire\\:model="archivoSubida"]'));
        etapa('guardar formulario obsoleto');
        await otra.bringToFront();
        await otra.click('form[wire\\:submit\\.prevent="subirArchivo"] button[type="submit"]');
        await otra.waitForFunction(() => document.body.innerText.includes('El documento cambió mientras estaba abierto este formulario'));
        assert.ok(await otra.$('input[wire\\:model="archivoSubida"]'), 'El conflicto conserva el formulario.');
        for (const ancho of [1440, 1280, 1024, 768, 390]) {
            await otra.setViewport({ width: ancho, height: 960 });
            assert.equal(await otra.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false);
            await otra.screenshot({ path: join(out, `conflicto-documental-${ancho}.png`), fullPage: true });
        }
        writeFileSync(join(out, 'resultado.json'), JSON.stringify({ primeraCarga: 'guardada', formularioObsoleto: 'rechazado',
            entradaConservada: true, anchos: [1440, 1280, 1024, 768, 390], erroresJavascript: errors }, null, 2));
        assert.deepEqual(errors, []);
        etapa('completado');
        return;
    }
    for(const width of (process.env.RM_GERENCIA_QA_RAPIDO ? [390] : [1440,1280,1024,768,390])) {
        await page.setViewport({width,height:960,hasTouch:width===390});
        for(const [nombre,ruta] of (process.env.RM_GERENCIA_QA_RAPIDO ? [rutas[1]] : rutas)) {
            await go(ruta);
            const estado=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+1,
                texto:document.querySelector('main')?.innerText||'',
                contenedor:!!document.querySelector('.rm-management-workspace,.rm-gallery')}));
            assert.ok(estado.contenedor,`${nombre}: composición renovada`);
            assert.equal(await page.$eval('html', e=>e.getAttribute('data-theme')),'light');
            assert.equal(estado.overflow,false,`${nombre} ${width}: desbordamiento de página`);
            assert.doesNotMatch(estado.texto,/\b(?:undefined|NaN|Infinity|null)\b|\[object Object\]|COD_(?:RES|PER|USU)_/);
            evidencia.push({vista:nombre,ancho:width,tema:'claro',overflow:false});
            if([1440,390].includes(width)) await page.screenshot({path:join(out,`${nombre}-${width}.png`),fullPage:true});
        }
    }
    await page.setViewport({width:390,height:960,hasTouch:true});
    await go('/admin/personal-institucional');
    await page.click('button[wire\\:click="cambiarVistaListadoResumen(\'tarjetas\')"]');
    await page.waitForSelector('[data-entity="personal"]');
    await page.screenshot({path:join(out,'personal-tarjetas-390.png'),fullPage:true});
    await page.click('button[wire\\:click="cambiarVistaListadoResumen(\'tabla\')"]');
    await page.waitForSelector('.rm-management-workspace table');
    await page.click('button[wire\\:click="abrirModalNuevo"]');
    try {
        await page.waitForSelector('#trabajador-nombres',{visible:true,timeout:10000});
    } catch(error) {
        await page.screenshot({path:join(out,'error-formulario.png'),fullPage:true});
        writeFileSync(join(out,'error-formulario.txt'),await page.$eval('main',e=>e.innerText));
        writeFileSync(join(out,'estructura-formulario.json'),JSON.stringify(await page.$$eval('[wire\\:id]',els=>els.map(e=>({id:e.getAttribute('wire:id'),snapshot:!!e.getAttribute('wire:snapshot'),tag:e.tagName,clase:e.className}))),null,2));
        throw new Error(`${error.message}; errores JS: ${JSON.stringify(errors)}`);
    }
    await page.click('button[wire\\:click="avanzarPaso"]');
    await page.waitForFunction(()=>document.querySelector('[role="dialog"]')?.innerText.includes('Revisa los datos indicados'));
    await page.type('#trabajador-nombres','Nombre sin guardar');
    let dialogos=0, aceptarDialogos=false;
    page.on('dialog',async d=>{dialogos++;aceptarDialogos ? await d.accept() : await d.dismiss();});
    await page.keyboard.press('Escape');
    await page.waitForFunction(()=>document.querySelector('#trabajador-nombres')?.value==='Nombre sin guardar');
    assert.equal(dialogos,1,'Escape debe pedir confirmación si hay datos nuevos.');
    await page.screenshot({path:join(out,'personal-validacion-390.png'),fullPage:true});
    await go('/admin/areas-institucionales');
    await page.click('button[wire\\:click^="verArea("]');
    await page.waitForSelector('#gerencia-area-fecha',{visible:true});
    await page.waitForFunction(()=>document.querySelector('[role="dialog"]')?.innerText.includes('Profesional de prueba'));
    await page.keyboard.press('Escape');
    await page.waitForFunction(()=>![...document.querySelectorAll('[role="dialog"]')].some(e=>e.getClientRects().length));
    await go('/admin/alertas-clinicas');
    await page.click('button[wire\\:click^="verDetalle("]');
    await page.waitForFunction(()=>[...document.querySelectorAll('[role="dialog"]')].some(e=>e.getClientRects().length && e.innerText.includes('Situación registrada')));
    await page.screenshot({path:join(out,'alertas-detalle-390.png'),fullPage:true});
    await go('/admin/residentes?vista=tabla&buscar=SinCoincidencia');
    assert.match(await page.$eval('main',e=>e.innerText),/No hay residentes en esta consulta/);
    await go('/admin/reportes/institucional');
    await page.click('a[href*="/institucional/preparar"]');
    await page.waitForSelector('input[name="formato"][value="csv"]');
    await page.click('input[name="formato"][value="csv"]');
    assert.match(await page.$eval('.rm-management-export form',e=>e.action),/institucional\/csv/);
    assert.match(await page.$eval('main',e=>e.innerText),/Descargar CSV/);
    await page.screenshot({path:join(out,'exportacion-390.png'),fullPage:true});
    await go('/admin/turnos-asignaciones');
    await page.click('button[wire\\:click="cambiarVista(\'mes\')"]');
    await page.waitForSelector('.rm-management-calendar.is-month button');
    await page.click('.rm-management-calendar.is-month button');
    await page.waitForSelector('.rm-management-workspace table');
    await page.click('button[wire\\:click="abrirNuevaAsignacion"]');
    await page.waitForSelector('#planificar-persona',{visible:true});
    await page.click('[role="dialog"] button[wire\\:click^="seleccionarUsuario("]');
    await page.waitForSelector('.rm-staff-schedule',{visible:true});
    await page.screenshot({path:join(out,'planificacion-asignar-390.png'),fullPage:true});
    await go('/admin/familia-social/red-apoyo');
    if(process.env.RM_GERENCIA_QA_ACCESIBILIDAD){
        aceptarDialogos=true;
        for(const width of [1440,1280,1024,768,390]){
            await page.setViewport({width,height:960,hasTouch:width===390});
            await page.focus('button[wire\\:click="nuevoContacto"]');
            await page.click('button[wire\\:click="nuevoContacto"]');
            await verificarDialogo('familia-contacto-formulario',width);
            await page.keyboard.press('Escape');
            await page.waitForSelector('#familia-contacto-formulario',{hidden:true});
            assert.equal(await page.evaluate(()=>document.activeElement?.getAttribute('wire:click')),'nuevoContacto','Cerrar debe devolver foco al control de origen');
        }
        await page.click('button[wire\\:click="nuevoContacto"]');
        await page.waitForSelector('#contacto-nombres',{visible:true});
        // El formulario conserva validación nativa; novalidate permite comprobar también la respuesta real del backend.
        await page.$eval('form[wire\\:submit="guardarContacto"]',form=>form.noValidate=true);
        await page.click('form[wire\\:submit="guardarContacto"] button[type="submit"]');
        await page.waitForSelector('#contacto-nombres[aria-invalid="true"]');
        const error=await page.$eval('#contacto-nombres',e=>({asociado:e.getAttribute('aria-describedby'),texto:document.getElementById(e.getAttribute('aria-describedby'))?.textContent.trim()}));
        assert.equal(error.asociado,'contacto-nombres-error'); assert.ok(error.texto,'Error conectado al campo original');
        await page.screenshot({path:join(out,'contacto-error-asociado-390.png'),fullPage:true});
        await page.keyboard.press('Escape');await page.waitForSelector('#familia-contacto-formulario',{hidden:true});
        writeFileSync(join(out,'teclado-y-formularios.json'),JSON.stringify({accesibilidad,error},null,2));
    }
    await page.click('button[wire\\:click="cambiarVista(\'personas\')"]');
    await page.waitForFunction(()=>document.querySelector('main')?.innerText.includes('Contacto de prueba'));
    await page.click('button[wire\\:click="nuevoContacto"]');
    await page.waitForSelector('#contacto-nombres',{visible:true});
    await page.type('#contacto-nombres','Nueva persona de prueba');
    await page.type('#contacto-apellido_paterno','Registro sintético');
    await page.click('form[wire\\:submit="guardarContacto"] button[type="submit"]');
    await page.waitForFunction(()=>document.querySelector('main')?.innerText.includes('Contacto guardado.'));
    await page.click('button[wire\\:click^="abrirCuenta("]');
    await page.waitForSelector('#familia-correo-acceso',{visible:true});
    await page.type('#familia-correo-acceso','familiar-qa@example.test');
    aceptarDialogos=true;
    await page.click('form[wire\\:submit="crearCuenta"] button[type="submit"]');
    await page.waitForFunction(()=>document.querySelector('main')?.innerText.includes('Cuenta Familiar creada.'));
    await page.screenshot({path:join(out,'contactos-cuenta-390.png'),fullPage:true});
    if(process.env.RM_GERENCIA_QA_AMPLIACION) {
        aceptarDialogos=true;
        await go('/admin/personal-documentacion');
        await page.click('button[wire\\:click="nuevaSolicitud"]');
        await page.waitForSelector('#solicitud-nombre',{visible:true});
        await page.type('#solicitud-nombre','Respaldo documental de prueba');
        await page.type('#solicitud-motivo','Seguimiento del expediente de prueba');
        await page.click('input[type="checkbox"][wire\\:model="personas"]');
        await page.$eval('#solicitud-limite',e=>{const f=new Date();f.setDate(f.getDate()+2);e.value=f.toISOString().slice(0,10);e.dispatchEvent(new Event('input',{bubbles:true}));});
        await page.click('form[wire\\:submit="emitirSolicitud"] button[type="submit"]');
        await page.waitForFunction(()=>document.querySelector('main')?.innerText.includes('Solicitud emitida y destinatarios conservados.'));
        await page.click('button[wire\\:click^="verDetalle("]');
        await page.waitForSelector('#entrega-archivo',{visible:true});
        const archivo=join(dirname(db),`remembermind-respaldo-${randomBytes(8).toString('hex')}.pdf`);
        writeFileSync(archivo,'%PDF-1.4\n1 0 obj <</Type/Catalog>> endobj\n%%EOF'); t.after(()=>rmSync(archivo,{force:true}));
        await (await page.$('#entrega-archivo')).uploadFile(archivo);
        await page.waitForFunction(()=>{
            const el=document.querySelector('#entrega-archivo')?.closest('[wire\\:id]');
            return el && window.Livewire.find(el.getAttribute('wire:id')).get('archivo');
        });
        await page.waitForFunction(()=>!document.querySelector('form[wire\\:submit="guardarEntrega"] button[type="submit"]')?.disabled);
        await page.click('form[wire\\:submit="guardarEntrega"] button[type="submit"]');
        await page.waitForFunction(()=>document.querySelector('main')?.innerText.includes('Entrega guardada. Falta la revisión del documento.'));
        await page.waitForSelector('button[wire\\:click$="\'VALIDAR\')"]',{visible:true});
        await page.click('button[wire\\:click$="\'VALIDAR\')"]');
        await page.waitForFunction(()=>document.querySelector('main')?.innerText.includes('Revisión documental guardada'));
        await page.screenshot({path:join(out,'documentacion-entrega-validada-390.png'),fullPage:true});
        const enlacePrivado=await page.$eval('a[href*="/personal-documentacion/archivos/"]',e=>e.href);
        const respuestaArchivo=await fetch(enlacePrivado,{headers:{'X-RM-GERENCIA-QA':token}});
        assert.equal(respuestaArchivo.status,200);
        assert.match(respuestaArchivo.headers.get('content-type'),/application\/pdf/);
        assert.match(await respuestaArchivo.text(),/^%PDF/);
        if (process.env.RM_GERENCIA_QA_PRORROGA) {
            for (const nombre of ['Respaldo de alta inicial', 'Respaldo de alta complementario']) {
                await go('/admin/personal-documentacion');
                await page.click('button[wire\\:click="nuevaSolicitud"]');
                await page.waitForSelector('#solicitud-nombre', { visible: true });
                await page.type('#solicitud-nombre', nombre);
                await page.type('#solicitud-motivo', 'Completar el expediente de alta de prueba');
                await page.click('input[type="checkbox"][wire\\:model="personas"]');
                await page.select('#solicitud-plazo', 'ALTA');
                await page.waitForFunction(() => !document.querySelector('#solicitud-limite'));
                await page.click('form[wire\\:submit="emitirSolicitud"] button[type="submit"]');
                await page.waitForFunction(() => !document.querySelector('#solicitud-nombre'));
            }
            const abrirPeticion = async nombre => {
                await page.waitForFunction(n => [...document.querySelectorAll('tr')].some(e => e.innerText.includes(n)), {}, nombre);
                await page.evaluate(n => [...document.querySelectorAll('tr')].find(e => e.innerText.includes(n)).querySelector('button').click(), nombre);
                await page.waitForSelector('#entrega-archivo', { visible: true });
            };
            await abrirPeticion('Respaldo de alta inicial');
            await page.evaluate(() => document.querySelector('#prorroga-motivo').closest('details').open = true);
            await page.type('#prorroga-motivo', 'Concesión única del expediente de prueba');
            await page.click('button[wire\\:click="prorrogar"]');
            await page.waitForFunction(() => document.body.innerText.includes('Plazo del expediente prorrogado.') && !document.querySelector('#prorroga-motivo'));
            await page.click('button[wire\\:click="cerrarDetalle"]');
            await page.waitForFunction(() => !document.querySelector('#entrega-archivo'));
            await abrirPeticion('Respaldo de alta complementario');
            await page.waitForFunction(() => document.body.innerText.includes('La concesión se registró en otra solicitud de esta misma alta'));
            assert.equal(await page.$('button[wire\\:click="prorrogar"]'), null);
            assert.match(await page.$eval('main', e => e.innerText), /Concesión única del expediente de prueba/);
            await page.screenshot({ path: join(out, 'documentacion-prorroga-expediente-390.png'), fullPage: true });
        }
    }
    for(const [nombre,ruta] of [rutas[0],rutas[1],rutas[6]]) {
        await go(ruta);
        await page.evaluate(()=>{document.documentElement.classList.add('dark');document.documentElement.setAttribute('data-theme','dark');});
        await page.screenshot({path:join(out,`${nombre}-oscuro-390.png`),fullPage:true});
        evidencia.push({vista:nombre,ancho:390,tema:'oscuro',overflow:await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1)});
    }
    writeFileSync(join(out,'matriz.json'),JSON.stringify({evidencia,erroresJavascript:errors},null,2));
    assert.deepEqual(errors,[],'Errores JS observados en el recorrido.');
});
