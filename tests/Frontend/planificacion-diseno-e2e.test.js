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

test('Planificación aprobada: tarjetas Nunito, selección y cinco anchos', { timeout: process.env.RM_GERENCIA_QA_PERSONAL ? 600000 : 300000, skip: !chrome }, async t => {
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
    const env = { ...process.env, RM_GERENCIA_QA_PERSONAL:'1', RM_GERENCIA_QA_AMPLIACION:'1', RM_GERENCIA_QA_MAPA:'0', RM_GERENCIA_QA_PLANIFICACION:'1', RM_GERENCIA_QA_ARCHIVOS:'0', APP_ENV:'testing', APP_URL:base, RM_GERENCIA_QA_DB:db, RM_GERENCIA_QA_TOKEN:token };
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
    page.on('request',r=>{
        if(r.url().startsWith('https://tile.openstreetmap.org/'))return r.respond({status:200,contentType:'image/svg+xml',body:'<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"><rect width="256" height="256" fill="#dce5da"/><path d="M0 128H256M128 0V256" stroke="#f7f4ed" stroke-width="24"/><text x="14" y="28" font-size="13" fill="#345043">MAPA SINTÉTICO DE PRUEBA</text></svg>'});
        if(r.url().startsWith(base+'/'))return r.continue({headers:{...r.headers(),'X-RM-GERENCIA-QA':token}});
        r.abort();
    });
    await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
    const out=join(root,'output/planificacion-diseno-20261105'); mkdirSync(out,{recursive:true});
    t.after(async()=>{
        writeFileSync(join(out,'diagnostico.json'),JSON.stringify({errors,solicitudesFallidas},null,2));
        if(!page.isClosed())await page.screenshot({path:join(out,'ultimo-estado.png'),fullPage:true});
    });
    const go=async path=>{
        const r=await page.goto(base+path,{waitUntil:'networkidle0'}); assert.equal(r.status(),200,`${path}: ${r.status() === 200 ? '' : await page.$eval('body', e => e.innerText)}`);
        await page.waitForFunction(()=>window.Alpine && window.Livewire);
        await page.evaluate(()=>document.fonts.ready);
    };
    await go('/dashboard');
    assert.match(page.url(),/dashboard$/);
    page.on('dialog',async d=>d.accept());
    for(const width of [1440,1280,1024,768,390]) {
        await page.setViewport({width,height:960});await go('/admin/turnos-asignaciones');
        await page.waitForSelector('.rm-planning-event');
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false,'Sin desbordamiento '+width);
        if(width===1440)assert.ok(await page.$eval('.rm-planning__toolbar',e=>e.getBoundingClientRect().height<140),'Barra temporal compacta');
        assert.equal(await page.$eval('.rm-planning',e=>getComputedStyle(e).fontFamily.startsWith('Nunito')),true);
        assert.equal(await page.$eval('.rm-planning-event',e=>getComputedStyle(e).fontFamily.startsWith('Nunito')),true);
        assert.equal(await page.evaluate(()=>document.fonts.check('800 15px Nunito')),true);
        await page.evaluate(()=>{const b=[...document.querySelectorAll('[data-asignacion]')].find(e=>e.getClientRects().length);b.scrollIntoView({block:'center'});b.focus();});
        await page.screenshot({path:join(out,'agenda-'+width+'.png'),fullPage:true});
        await page.keyboard.press('Enter');await page.waitForSelector('.rm-planning__person');
        await page.waitForFunction(()=>document.querySelector('.rm-planning__detail')===document.activeElement);
        assert.ok(await page.$eval('.rm-planning__detail',e=>e.innerText.includes('no confirma asistencia')));
        await page.screenshot({path:join(out,'detalle-'+width+'.png'),fullPage:true});
        const medidas=await page.evaluate(()=>{
            const b=[...document.querySelectorAll('[data-asignacion]')].find(e=>e.getClientRects().length);
            return {separacion:parseFloat(getComputedStyle(b.parentElement).gap),presionados:[...document.querySelectorAll('[data-asignacion][aria-pressed="true"]')].filter(e=>e.getClientRects().length).length};
        });assert.ok(medidas.separacion>=12);assert.equal(medidas.presionados,1);
        await page.evaluate(()=>{document.querySelector('main')?.scrollTo(0,0);document.querySelector('[data-rm-main]')?.scrollTo(0,0);window.scrollTo(0,0);});
        await page.screenshot({path:join(out,'semana-'+width+'.png'),fullPage:true});
        if(width===1440) {
            await page.evaluate(()=>[...document.querySelectorAll('.rm-planning__detail button')].find(e=>e.innerText.includes('Gestionar horarios')).click());
            await page.waitForSelector('#planificacion-nueva [role="dialog"]',{visible:true});
            await page.waitForFunction(()=>[...document.querySelectorAll('#planificacion-nueva button')].some(e=>e.getAttribute('wire:click')?.startsWith('editarAsignacion(')));
            await page.screenshot({path:join(out,'horarios-existentes.png'),fullPage:true});
            await page.evaluate(()=>{const r=document.querySelector('.rm-planning');return Livewire.find(r.getAttribute('wire:id')).call('cerrarModal');});
            await page.waitForFunction(()=>!document.getElementById('planificacion-nueva'));
        }
        await page.evaluate(()=>document.querySelector('.rm-planning__detail button[aria-label="Cerrar detalle"]').click());
        await page.waitForFunction(()=>!document.querySelector('.rm-planning__person'));
        assert.equal(await page.evaluate(()=>!!document.activeElement?.dataset.asignacion),true,'Foco vuelve a la tarjeta');
        await page.evaluate(()=>{const r=document.querySelector('.rm-planning');return Livewire.find(r.getAttribute('wire:id')).call('cambiarVista','mes');});
        await page.waitForSelector('.rm-planning__month-day');
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false);
        await page.screenshot({path:join(out,'mes-'+width+'.png'),fullPage:true});
        await page.evaluate(()=>{const r=document.querySelector('.rm-planning');return Livewire.find(r.getAttribute('wire:id')).call('cambiarVista','semana');});
        await page.waitForSelector('.rm-planning-event');
        await page.type('#planificacion-buscar','no-existe-trabajador-prueba');
        await page.waitForFunction(()=>document.querySelectorAll('.rm-planning-event').length===0);
        await page.evaluate(()=>{const r=document.querySelector('.rm-planning');return Livewire.find(r.getAttribute('wire:id')).call('limpiarFiltros');});
        await page.waitForSelector('.rm-planning-event');
        await page.evaluate(()=>{const r=document.querySelector('.rm-planning');return Livewire.find(r.getAttribute('wire:id')).call('abrirNuevaAsignacion');});
        await page.waitForSelector('#planificacion-nueva [role="dialog"]',{visible:true});
        assert.ok(await page.$eval('#planificar-persona',e=>getComputedStyle(e).fontFamily.startsWith('Nunito')));
        await page.screenshot({path:join(out,'nueva-'+width+'.png'),fullPage:true});
        await page.evaluate(()=>{const r=document.querySelector('.rm-planning');return Livewire.find(r.getAttribute('wire:id')).call('cerrarModal');});
        await page.waitForFunction(()=>!document.getElementById('planificacion-nueva'));
    }
    assert.deepEqual(errors,[]);assert.deepEqual(solicitudesFallidas,[]);
});
