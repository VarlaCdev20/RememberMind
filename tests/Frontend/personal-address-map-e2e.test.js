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

test('Mapa de dirección: selección, confirmación y teclado en cinco anchos', { timeout: process.env.RM_GERENCIA_QA_PERSONAL ? 600000 : 300000, skip: !chrome }, async t => {
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
    const env = { ...process.env, RM_GERENCIA_QA_PERSONAL:'1', RM_GERENCIA_QA_AMPLIACION:'1', RM_GERENCIA_QA_MAPA:'1', RM_GERENCIA_QA_ARCHIVOS:'1', APP_ENV:'testing', APP_URL:base, RM_GERENCIA_QA_DB:db, RM_GERENCIA_QA_TOKEN:token };
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
    const out=join(root,'output/personal-mapa-20261105'); mkdirSync(out,{recursive:true});
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
        await page.setViewport({width,height:960});
        await go('/admin/personal-institucional');
        await page.click('button[wire\\:click="abrirModalNuevo"]');
        await page.waitForSelector('.rm-staff-registration #trabajador-nombres',{visible:true});
        await page.evaluate(async()=>{
            const root=document.querySelector('.rm-staff-registration');const wire=Livewire.find(root.getAttribute('wire:id'));
            await wire.set('ciudad_id','LPZ');await wire.set('municipio_id','MUN_LPZ');
            document.getElementById('personal-abrir-mapa').scrollIntoView({block:'center'});
        });
        await page.click('#personal-abrir-mapa');
        await page.waitForSelector('#personal-mapa-direccion.leaflet-container',{visible:true});
        await page.waitForFunction(()=>!!document.querySelector('.leaflet-tile-loaded'));
        assert.match(await page.$eval('.rm-address-map__heading',e=>e.innerText),/LA PAZ/);
        const zonaCatalogo = await page.$eval('#direccion-zona_id-source', select => {
            const option = [...select.options].find(o => o.value && o.value !== 'OTRA');
            return option ? { value: option.value, nombre: option.textContent } : null;
        });
        assert.ok(zonaCatalogo, 'La prueba dispone de una zona del catálogo');
        await page.evaluate(async zona => {
            const root = document.querySelector('.rm-staff-registration');
            await Livewire.find(root.getAttribute('wire:id')).set('zona_id', zona.value);
        }, zonaCatalogo);
        await page.waitForFunction(nombre => document.querySelector('.rm-address-map__heading').innerText.includes(nombre.trim()), { timeout: 10000 }, zonaCatalogo.nombre).catch(async error => {
            console.error(await page.$eval('.rm-address-map', el => ({ texto: el.innerText, zonaActual: Alpine.$data(el).zonaActual, contexto: Alpine.$data(el).contexto, abierto: Alpine.$data(el).abierto })));
            throw error;
        });
        await page.evaluate(async () => {
            const root = document.querySelector('.rm-staff-registration');
            const wire = Livewire.find(root.getAttribute('wire:id'));
            await wire.set('zona_id', 'OTRA');
            await wire.set('otra_zona', 'BARRIO DE PRUEBA');
        });
        await page.$$eval('.rm-address-map__heading button', botones => botones.find(b => b.textContent.includes('UBICAR ZONA')).click());
        await page.waitForFunction(() => document.querySelector('.rm-address-map__heading').innerText.includes('BARRIO DE PRUEBA'));
        await page.$eval('#personal-mapa-direccion',e=>e.scrollIntoView({block:'center'}));
        await page.click('#personal-mapa-direccion');
        await page.waitForFunction(()=>!document.getElementById('personal-consultar-punto').disabled);
        await page.click('#personal-consultar-punto');
        await page.waitForFunction(()=>document.getElementById('personal-direccion-mapa').value.includes('CALLE SINTÉTICA'));
        assert.equal(await page.$eval('#trabajador-direccion',e=>e.innerText.includes('CALLE SINTÉTICA')),false,'Consultar no aplica la dirección');
        const estado=await page.$eval('.rm-address-map',e=>({overflow:e.scrollWidth>e.clientWidth+1,attribution:e.querySelector('.leaflet-control-attribution').innerText,focus:document.activeElement.id}));
        assert.equal(estado.overflow,false);assert.match(estado.attribution,/OpenStreetMap/);assert.equal(estado.focus,'personal-direccion-mapa');
        await page.$eval('#personal-direccion-mapa',e=>{e.value='CALLE SINTÉTICA 120, BARRIO DE PRUEBA, LA PAZ, BOLIVIA';e.dispatchEvent(new Event('input',{bubbles:true}));});
        await page.screenshot({path:join(out,'seleccion-'+width+'.png'),fullPage:true});
        await page.$eval('#personal-usar-punto',e=>e.scrollIntoView({block:'center'}));
        await page.click('#personal-usar-punto');
        await page.waitForFunction(()=>document.getElementById('trabajador-direccion').innerText.includes('CALLE SINTÉTICA 120'));
        assert.equal(await page.$eval('#direccion-encabezado',e=>e===document.activeElement),true);
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false);
        await page.screenshot({path:join(out,'aplicada-'+width+'.png'),fullPage:true});
        // Reabrir, situar con teclado y marcar el centro sin necesidad de ratón.
        await page.click('#direccion-encabezado');await page.click('#personal-abrir-mapa');
        await page.waitForFunction(()=>document.getElementById('personal-mapa-direccion').getClientRects().length>0);
        await page.focus('#personal-mapa-direccion');await page.keyboard.press('ArrowRight');
        await page.click('.rm-address-map__actions button');
        assert.equal(await page.$eval('#personal-consultar-punto',e=>e.disabled),false);
    }
    assert.deepEqual(errors,[]);assert.deepEqual(solicitudesFallidas,[]);
});
