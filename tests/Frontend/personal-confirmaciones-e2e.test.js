import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';
import { existsSync, writeFileSync, rmSync } from 'node:fs';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import puppeteer from 'puppeteer';
import { test } from 'node:test';

test('Personal: confirmaciones accesibles, cancelación sin cambios y ejecución única', { skip: process.env.RM_PERSONAL_CONFIRMACIONES_E2E !== '1', timeout: 240000 }, async () => {
const id=randomBytes(8).toString('hex'), db=join(tmpdir(),'remembermind-gerencia-'+id+'_testing.sqlite');
assert.equal(resolve(dirname(db)),resolve(tmpdir()));
writeFileSync(db,'',{flag:'wx'});
const port=await new Promise(r=>{const s=createServer().listen(0,'127.0.0.1',()=>{const p=s.address().port;s.close(()=>r(p));});});
const base='http://127.0.0.1:'+port, token=randomBytes(24).toString('hex');
const env={...process.env,APP_ENV:'testing',APP_URL:base,RM_GERENCIA_QA_DB:db,RM_GERENCIA_QA_TOKEN:token,RM_GERENCIA_QA_ARCHIVOS:'1',RM_GERENCIA_QA_AMPLIACION:'1'};
const router='tests/Support/ServidorGerenciaSintetico.php';
let server,browser;
try {
    const init=spawnSync('php',[router,'--preparar'],{env,encoding:'utf8',timeout:45000});
    assert.equal(init.status,0,init.stderr); assert.match(init.stdout,/PREPARADO/);
    const profile = spawnSync('php',['-r', 'exit((new PDO("sqlite:".getenv("RM_GERENCIA_QA_DB")))->prepare("UPDATE personal SET telefono = ?, direccion = ?, genero = ?, fecha_nacimiento = ?, fecha_ingreso = ?, observacion = ?")->execute(["+591 77712345", "AVENIDA DE PRUEBA 123, ZONA CENTRAL", "F", "1990-03-12", "2026-10-01", "Datos ficticios para verificar la ficha."]) ? 0 : 1);'],{env,encoding:'utf8'});
    assert.equal(profile.status,0,profile.stderr);
    server=spawn('php',['-S','127.0.0.1:'+port,'-t','public',router],{env,stdio:'ignore',windowsHide:true});
    const chrome=[process.env.PUPPETEER_EXECUTABLE_PATH,puppeteer.executablePath(),'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p=>typeof p==='string'&&existsSync(p));
    assert.ok(chrome);
    browser=await puppeteer.launch({executablePath:chrome,headless:true});
    const page=await browser.newPage(), errors=[];
    page.on('pageerror',e=>errors.push(e.message));
    await page.evaluateOnNewDocument(()=>{if(location.protocol==='http:')localStorage.setItem('remembermind-theme','light');});
    await page.setRequestInterception(true);
    page.on('request',r=>r.url().startsWith(base+'/') ? r.continue({headers:{...r.headers(),'X-RM-GERENCIA-QA':token}}) : r.abort());
    await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);

    const nativos=[], llamadas=[];
    page.on('dialog',async d=>{nativos.push(d.type());await d.dismiss();});
    page.on('request',r=>{
        if(r.method()!=='POST'||!r.url().endsWith('/update'))return;
        const data=JSON.parse(r.postData()||'{}');
        for(const component of data.components||[]) for(const call of component.calls||[]) llamadas.push(call.method);
    });
    const contar=nombre=>llamadas.filter(n=>n===nombre).length;
    const clickText=async(label,root='body')=>{
        await page.waitForFunction((label,root)=>[...document.querySelectorAll(root+' button')].some(b=>b.innerText.trim()===label&&b.getClientRects().length),{},label,root);
        await page.evaluate((label,root)=>[...document.querySelectorAll(root+' button')].find(b=>b.innerText.trim()===label&&b.getClientRects().length).click(),label,root);
    };
    const menu=async()=>{
        await page.evaluate(()=>{const record=[...document.querySelectorAll('button')].find(b=>b.textContent.trim()==='Ver ficha'&&b.getClientRects().length);record.closest('tr,.rm-staff-directory__card').querySelector('button[aria-label^="Acciones de"]').click();});
    };
    const leer=()=>{
        const code='$pdo=new PDO("sqlite:".getenv("RM_GERENCIA_QA_DB"));$u=$pdo->query("SELECT cod_usuario,estado FROM usuarios WHERE correo=\'equipo1@example.test\'")->fetch(PDO::FETCH_ASSOC);$p=$pdo->prepare("SELECT * FROM personal WHERE cod_usuario=?");$p->execute([$u["cod_usuario"]]);$personal=$p->fetch(PDO::FETCH_ASSOC);$a=$pdo->prepare("SELECT * FROM asignaciones_personal WHERE cod_personal=? ORDER BY cod_asignacion_personal");$a->execute([$personal["cod_personal"]]);echo json_encode(["acceso"=>$u["estado"],"personal"=>$personal,"asignaciones"=>$a->fetchAll(PDO::FETCH_ASSOC),"movimientos"=>$pdo->query("SELECT COUNT(*) FROM movimientos_laborales_personal")->fetchColumn()]);';
        const result=spawnSync('php',['-r',code],{env,encoding:'utf8'});
        assert.equal(result.status,0,result.stderr);return JSON.parse(result.stdout);
    };
    const original=leer();
    for(const [width,theme] of [[1440,'light'],[1280,'light'],[1024,'light'],[768,'light'],[390,'light'],[390,'dark']]){
        await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:width===1440?'no-preference':'reduce'}]);
        await page.evaluateOnNewDocument(t=>{if(location.protocol==='http:')localStorage.setItem('remembermind-theme',t);},theme);
        await page.setViewport({width,height:width===390?844:940});
        await page.goto(base+'/admin/personal-institucional',{waitUntil:'networkidle0'});
        if(width===390)await clickText('Tarjetas');
        await menu();await clickText('Suspender acceso');
        await page.waitForSelector('.rm-confirmation',{visible:true});
        await page.waitForFunction(()=>document.activeElement?.classList.contains('swal2-cancel'));
        const state=await page.$eval('.rm-confirmation',e=>{
            const r=e.getBoundingClientRect();
            return {fit:r.left>=0&&r.right<=innerWidth&&r.top>=0&&r.bottom<=innerHeight,
                overflow:e.scrollWidth>e.clientWidth+1,font:getComputedStyle(e).fontFamily,
                cancel:e.querySelector('.swal2-cancel').offsetHeight,confirm:e.querySelector('.swal2-confirm').offsetHeight,
                cancelFocused:document.activeElement===e.querySelector('.swal2-cancel')};
        });
        assert.ok(state.fit&&!state.overflow&&state.cancel>=44&&state.confirm>=44&&state.cancelFocused,JSON.stringify(state));
        assert.match(state.font,/Nunito/);
        assert.match(await page.$eval('.rm-confirmation',e=>e.textContent),/equipo1@example.test/);
        const path=join(tmpdir(),'remembermind-confirmaciones-'+id+'-'+width+'-'+theme+'.png');
        await page.screenshot({path});console.log(path);
        await page.click('.swal2-cancel');
        await page.waitForSelector('.rm-confirmation',{hidden:true});
        assert.equal(contar('toggleEstado'),0);assert.deepEqual(leer(),original);
        await menu();await clickText('Enviar enlace de acceso');
        await page.waitForSelector('.rm-confirmation',{visible:true});
        await page.keyboard.press('Escape');await page.waitForSelector('.rm-confirmation',{hidden:true});
        assert.equal(contar('enviarRecuperacion'),0);
        console.log(width+' px '+theme+': cancelar y Escape conservan el registro');
    }
    // Confirmar una sola vez modifica el acceso y conserva personal/asignaciones.
    await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'no-preference'}]);
    await page.setViewport({width:1440,height:940});
    await page.goto(base+'/admin/personal-institucional',{waitUntil:'networkidle0'});
    await menu();await clickText('Suspender acceso');
    await page.waitForSelector('.rm-confirmation',{visible:true});
    await page.evaluate(()=>[...document.querySelectorAll('.rm-staff-directory__actions button')].find(b=>b.textContent.trim()==='Suspender acceso').click());
    assert.equal(await page.$$eval('.rm-confirmation',els=>els.length),1);
    await page.click('.rm-confirmation .swal2-confirm');
    await page.waitForFunction(()=>[...document.querySelectorAll('button')].some(b=>b.textContent.trim()==='Activar acceso'));
    assert.equal(contar('toggleEstado'),1);
    const suspendido=leer();
    assert.equal(suspendido.acceso,'SUSPENDIDO');assert.deepEqual(suspendido.personal,original.personal);assert.deepEqual(suspendido.asignaciones,original.asignaciones);
    await page.evaluate(()=>window.Swal.close());
    await menu();await clickText('Activar acceso');await page.waitForSelector('.rm-confirmation',{visible:true});
    await page.click('.rm-confirmation .swal2-confirm');
    await page.waitForFunction(()=>![...document.querySelectorAll('button')].some(b=>b.textContent.trim()==='Activar acceso'));
    assert.equal(contar('toggleEstado'),2);assert.deepEqual(leer(),original);
    await page.evaluate(()=>window.Swal.close());
    // El formulario laboral espera confirmación; cancelar mantiene la captura.
    await menu();await clickText('Cambiar situación laboral');
    await page.waitForSelector('#laboral-motivo',{visible:true});
    await page.click('button[form="gerencia-laboral-form"]');
    await page.waitForSelector('.rm-confirmation',{visible:true});
    await page.waitForFunction(()=>document.querySelector('.rm-confirmation .swal2-cancel')===document.activeElement);
    assert.equal(await page.$eval('.rm-confirmation',e=>e.querySelector('.swal2-cancel')===document.activeElement),true);
    await page.keyboard.press('Tab');
    assert.equal(await page.$eval('.rm-confirmation',e=>e.contains(document.activeElement)),true);
    await page.keyboard.press('Escape');await page.waitForSelector('.rm-confirmation',{hidden:true});
    assert.ok(await page.$('#laboral-motivo'));
    assert.equal(contar('guardarEstadoLaboral'),0);
    await page.click('button[form="gerencia-laboral-form"]');
    await page.waitForSelector('.rm-confirmation',{visible:true});await page.click('.swal2-confirm');
    await page.waitForSelector('#laboral-motivo[aria-invalid="true"]');
    assert.equal(contar('guardarEstadoLaboral'),1);assert.deepEqual(leer(),original);
    await clickText('Cancelar','#gerencia-estado-laboral');
    await page.waitForSelector('.rm-confirmation',{visible:true});await page.click('.swal2-cancel');
    await page.waitForSelector('.rm-confirmation',{hidden:true});
    assert.ok(await page.$('#laboral-motivo'));
    await clickText('Cancelar','#gerencia-estado-laboral');
    await page.waitForSelector('.rm-confirmation',{visible:true});await page.click('.swal2-confirm');
    await page.waitForSelector('#gerencia-estado-laboral [role="dialog"]',{hidden:true});
    // El descarte del editor usa el mismo componente y conserva datos al cancelar.
    await menu();await clickText('Editar registro');await page.waitForSelector('#trabajador-nombres',{visible:true});
    await page.type('#trabajador-nombres',' CAMBIO');
    await clickText('Cancelar','#gerencia-personal-ficha');
    await page.waitForSelector('.rm-confirmation',{visible:true});await page.keyboard.press('Escape');
    await page.waitForSelector('.rm-confirmation',{hidden:true});assert.ok(await page.$('#trabajador-nombres'));
    await clickText('Cancelar','#gerencia-personal-ficha');
    await page.waitForSelector('.rm-confirmation',{visible:true});await page.click('.swal2-confirm');
    await page.waitForSelector('#gerencia-personal-ficha [role="dialog"]',{hidden:true});
    assert.deepEqual(leer(),original);
    // Los nombres se muestran como texto, sin interpretar HTML.
    await page.evaluate(()=>{void window.rmConfirmar({title:'Confirmación de prueba',subject:'<img src=x onerror="window.inyectado=true">',text:'Contenido sintético.',confirmText:'Continuar'});});
    await page.waitForSelector('.rm-confirmation',{visible:true});
    assert.equal(await page.$('.rm-confirmation__subject img'),null);
    assert.match(await page.$eval('.rm-confirmation__subject',e=>e.textContent),/^<img src=x/);
    assert.equal(await page.evaluate(()=>window.inyectado),undefined);
    await page.click('.swal2-cancel');await page.waitForSelector('.rm-confirmation',{hidden:true});
    assert.deepEqual(nativos,[]);
    console.log('Confirmaciones: una ejecución, cancelación sin cambios, validación y texto seguro correctos');
    assert.deepEqual(errors,[]);
}finally{
    await browser?.close();server?.kill();
    for(const suffix of ['', '-shm','-wal','.views','.storage','.log']) rmSync(db+suffix,{recursive:true,force:true});
}
});
