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

test('Áreas de Gerencia: tarjetas compartidas, filtro y equipo en cinco anchos', { timeout: process.env.RM_GERENCIA_QA_PERSONAL ? 600000 : 300000, skip: !chrome }, async t => {
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
    const env = { ...process.env, RM_GERENCIA_QA_PERSONAL:'1', RM_GERENCIA_QA_AMPLIACION:'1', RM_GERENCIA_QA_MAPA:'0', RM_GERENCIA_QA_ARCHIVOS:'1', APP_ENV:'testing', APP_URL:base, RM_GERENCIA_QA_DB:db, RM_GERENCIA_QA_TOKEN:token };
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
    const out=join(root,'output/gerencia-areas-compartidas-20261105'); mkdirSync(out,{recursive:true});
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
        await go('/admin/areas-institucionales');
        await page.waitForSelector('[data-area-card]');
        assert.match(await page.$eval('main', e => e.innerText), /Áreas y equipo/i);
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false);
        assert.doesNotMatch(await page.$eval('main',e=>e.innerText),/Sin Responsable|Seeder|undefined|\[object Object\]/);
        const total=await page.$$eval('[data-area-card]',e=>e.length);assert.ok(total>0);
        await page.type('#areas-buscar','sin-coincidencias-de-prueba');
        await page.waitForFunction(()=>document.querySelectorAll('[data-area-card]').length===0);
        await page.evaluate(async()=>{const root=document.querySelector('[wire\\:id]');const comp=[...document.querySelectorAll('[wire\\:id]')].find(e=>e.querySelector('#areas-buscar'));await Livewire.find(comp.getAttribute('wire:id')).call('limpiarFiltros');});
        await page.waitForSelector('[data-area-card]');
        assert.ok(await page.$eval('#areas-buscar',e=>parseFloat(getComputedStyle(e).paddingLeft)>=36),'Icono separado del texto');
        await page.screenshot({path:join(out,'tarjetas-'+width+'.png'),fullPage:true});
        await page.$eval('[data-area-card] button',e=>e.scrollIntoView({block:'center'}));
        await page.click('[data-area-card] button');
        await page.waitForSelector('#gerencia-area-equipo [role="dialog"]',{visible:true});
        await page.waitForSelector('#gerencia-area-fecha',{visible:true});
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1),false);
        const ficha = await page.$eval('#gerencia-area-equipo [role="dialog"]', dialogo => {
            const rect = dialogo.getBoundingClientRect();
            const lista = dialogo.querySelector('.rm-area-detail__people');
            return {
                cabe: rect.left >= 0 && rect.right <= innerWidth && rect.top >= 0 && rect.bottom <= innerHeight,
                desborda: dialogo.scrollWidth > dialogo.clientWidth + 1,
                personas: lista?.children.length,
                texto: lista?.innerText,
            };
        });
        assert.equal(ficha.cabe, true);
        assert.equal(ficha.desborda, false);
        assert.equal(ficha.personas, 3);
        assert.match(ficha.texto, /Profesional de prueba 1/);
        assert.match(ficha.texto, /Función asignada/);
        await page.screenshot({path:join(out,'equipo-'+width+'.png'),fullPage:true});
        if (width === 390) {
            const fechaOriginal = await page.$eval('#gerencia-area-fecha', input => {
                const anterior = input.value;
                const siguiente = new Date(anterior + 'T00:00:00Z');
                siguiente.setUTCDate(siguiente.getUTCDate() + 1);
                input.value = siguiente.toISOString().slice(0, 10);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                return anterior;
            });
            await page.waitForFunction(() => document.querySelector('#gerencia-area-equipo').innerText.includes('Sin asignaciones para esta fecha'));
            await page.click('#gerencia-area-equipo .rm-area-detail__dates button:first-child');
            await page.waitForFunction(fecha => document.querySelector('#gerencia-area-fecha').value === fecha
                && document.querySelectorAll('#gerencia-area-equipo .rm-area-detail__people li').length === 3, {}, fechaOriginal);
        }
        await page.evaluate(async()=>{const comp=[...document.querySelectorAll('[wire\\:id]')].find(e=>e.querySelector('#areas-buscar'));await Livewire.find(comp.getAttribute('wire:id')).call('cerrarFicha');});
        await page.waitForFunction(()=>!document.getElementById('gerencia-area-equipo'));
    }
    assert.deepEqual(errors,[]);assert.deepEqual(solicitudesFallidas,[]);
});
