// Isolated, synthetic render from RegistroConductaTest. No application session or DB access.
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { createServer } from 'node:http';
import assert from 'node:assert/strict';
import puppeteer from 'puppeteer';

const root = process.cwd();
const fixtureDir = process.env.RM_COGNITION_QA_DIR || join(process.env.TEMP, 'remembermind-cognicion-qa');
const evidenceDir = resolve(process.env.RM_COGNITION_EVIDENCE_DIR || join(fixtureDir, 'evidence'));
mkdirSync(evidenceDir, { recursive: true });
const read = path => readFileSync(join(root, path), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = manifest['resources/frontend/styles/app.css'].file;
const buildFixture = name => {
    let html = readFileSync(join(fixtureDir, `cognicion-${name}.html`), 'utf8');
    const rawSnapshot=html.match(/wire:snapshot="([^"]+)"/);
    const snapshot = rawSnapshot ? JSON.parse(rawSnapshot[1].replaceAll('&quot;', '"').replaceAll('&amp;', '&')) : JSON.parse(readFileSync(join(fixtureDir,'cognicion-'+name+'.state.json'),'utf8'));
    const unwrap = value => Array.isArray(value) && value.length === 2 && value[1]?.s ? unwrap(value[0]) : value && typeof value === 'object' ? Object.fromEntries(Object.entries(value).map(([key, item]) => [key, unwrap(item)])) : value;
    html = html.replace(/\s+wire:(?:snapshot|id|name|effects|key)="[^"]*"/g, '').replace(/window\.Livewire\.find\('[^']+'\)\.entangle/g, '$wire.entangle');
    return `<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"><script async src="https://unpkg.com/@phosphor-icons/web"></script></head><body class="rm-shell rm-nursing-shell" data-role="nursing"><main class="rm-resident-directory" aria-label="Entorno sintético de QA">${html}</main><script>window.qaInitial=${JSON.stringify(unwrap(snapshot.data))};window.livewireScriptConfig={csrf:'synthetic',uri:'/qa-only'};</script><script type="module" src="/qa.mjs"></script></body></html>`;
};
const moduleSource = `
import '/modules/clinical-form-feedback.js';
import '/modules/free-text-select.js';
import '/modules/signos-vitales-registro.js';
import '/modules/conducta-registro.js';
import '/modules/observacion-cognitiva.js';
import '/components/clinical-consultations.js';
const {Livewire, Alpine} = await import('/livewire.esm.js');
const state = Alpine.reactive(window.qaInitial);
document.querySelectorAll('*').forEach(el=>{if(el.hasAttribute('wire:loading'))el.style.display='none';});
const get = name => name.split('.').reduce((v,key)=>v?.[key],state);
const set = (name,value) => {const keys=name.split('.');const last=keys.pop();keys.reduce((v,key)=>v[key],state)[last]=value;};
Object.assign(state,{$set:set,entangle(name){return Alpine.interceptor((initial,getter,setter)=>{Alpine.entangle({get:()=>get(name),set:v=>set(name,v)},{get:getter,set:setter});return get(name);})(get(name));},async guardar(){window.qaSubmits=(window.qaSubmits||0)+1;},async limpiarCaptura(){for(const key of Object.keys(state.datos))state.datos[key]=null;document.querySelector('.rm-cognicion').dispatchEvent(new CustomEvent('cognicion-captura-reiniciada',{bubbles:true}));},async cerrarModal(){state.mostrarFormulario=false;}});
Alpine.magic('wire',()=>state);
[...document.querySelectorAll('input,select,textarea')].filter(field=>field.hasAttribute('wire:model')||field.hasAttribute('wire:model.live')).forEach(field=>{const name=field.getAttribute('wire:model')||field.getAttribute('wire:model.live');field.value=get(name)??'';field.addEventListener(field.tagName==='SELECT'?'change':'input',()=>set(name,field.value));Alpine.effect(()=>field.value=get(name)??'');});
Livewire.start();window.qaWire=state;window.qaReady=true;
`;
const checkConsultationLayout = async (page, selector, width = page.viewport().width) => {
    await page.evaluate(()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))));
    if(width>=1280)await page.waitForFunction(selector=>{const p=document.querySelector(selector).getBoundingClientRect(),f=document.querySelector(selector).closest('.rm-modal-shell').querySelector(':scope > .rm-modal-panel').getBoundingClientRect();return f.right+8<=p.left||p.right+8<=f.left;},{},selector);
    const layout=await page.evaluate(selector=>{const popup=document.querySelector(selector),shell=popup.closest('.rm-modal-shell'),form=shell.querySelector(':scope > .rm-modal-panel'),body=shell.querySelector('.rm-modal-body');const p=popup.getBoundingClientRect(),f=form.getBoundingClientRect();return {left:p.left,right:p.right,top:p.top,bottom:p.bottom,formLeft:f.left,formRight:f.right,overflow:popup.scrollWidth>popup.clientWidth+2||body.scrollWidth>body.clientWidth+2};},selector);
    assert.ok(layout.left>=0&&layout.right<=width+1&&layout.top>=0&&layout.bottom<=901,'Consulta dentro del viewport');
    assert.equal(layout.overflow,false,'Captura y consulta sin overflow horizontal');
    if(width>=1280)assert.ok(layout.formRight+8<=layout.left||layout.right+8<=layout.formLeft,'Consulta y captura visibles lado a lado');
};
const server = createServer((req, res) => {
    try {
        if (req.url === '/qa.mjs') { res.setHeader('Content-Type', 'application/javascript'); res.end(moduleSource); }
        else if (req.url === '/livewire.esm.js') { res.setHeader('Content-Type', 'application/javascript'); res.end(read('vendor/livewire/livewire/dist/livewire.esm.js')); }
        else if ((req.url.startsWith('/modules/') || req.url.startsWith('/components/')) && !req.url.includes('..')) { res.setHeader('Content-Type', 'application/javascript'); res.end(read('resources/frontend/scripts'+req.url)); }
        else if (req.url.startsWith('/build/') && !req.url.includes('..')) { res.setHeader('Content-Type', req.url.endsWith('.css') ? 'text/css' : 'font/woff2'); res.end(read('public'+req.url)); }
        else if (['/one', '/empty', '/error', '/favicon.ico'].includes(req.url)) { res.setHeader('Content-Type', 'text/html'); res.end(buildFixture(req.url === '/empty' ? 'empty' : req.url === '/error' ? 'error' : 'one')); }
        else { res.statusCode = 404; res.end(); }
    } catch (error) { res.statusCode = 500; res.end('Synthetic fixture error'); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
let browser;
const results = [];
try {
    browser = await puppeteer.launch({headless: true, executablePath});
    const page = await browser.newPage();
    page.setDefaultTimeout(10000);
    const errors = [];
    page.on('pageerror', e => {errors.push(e.message); console.error(e.message);});
    page.on('dialog', async dialog => { await dialog.accept(); });
    for (const width of [1440, 1280, 1024, 768, 390]) {
        await page.setViewport({width, height: 900});
        await page.emulateMediaFeatures([{name: 'prefers-reduced-motion', value: 'reduce'}]);
        await page.goto(`http://127.0.0.1:${server.address().port}/one`, {waitUntil: 'domcontentloaded'});
        await page.waitForFunction(() => window.qaReady && document.querySelector('.rm-modal-panel').getBoundingClientRect().width > 0);
        await page.waitForFunction(() => getComputedStyle(document.querySelector('.ph-brain'),'::before').content !== 'none' && getComputedStyle(document.querySelector('.ph-brain')).fontFamily.includes('Phosphor'), {timeout:30000});
        await page.evaluate(() => document.fonts.ready);
        const layout = await page.evaluate(() => {
            const r = document.querySelector('.rm-modal-panel').getBoundingClientRect();
            const b = document.querySelector('.rm-modal-body');
            return {width: innerWidth, horizontal: document.documentElement.scrollWidth > innerWidth || b.scrollWidth > b.clientWidth + 1, panel: {x:r.x,y:r.y,width:r.width,height:r.height}, reduced: matchMedia('(prefers-reduced-motion: reduce)').matches};
        });
        assert.equal(layout.width, width); assert.equal(layout.horizontal, false);
        assert.ok(layout.panel.x >= 0 && layout.panel.y >= 0 && layout.panel.height <= 900);
        assert.equal(layout.reduced, true);
        const captureSummary = await page.evaluate(() => {
            const summary = document.querySelector('.rm-cognicion__summary');
            const title = summary.querySelector('h3').getBoundingClientRect();
            return {titleWidth: title.width, height: summary.getBoundingClientRect().height};
        });
        assert.ok(captureSummary.titleWidth >= 160, 'El título de captura debe poder leerse sin apilar letras');
        assert.ok(captureSummary.height < 400, 'El resumen no debe ocupar todo el espacio de captura');
        if (width === 390) {
            const mobile = await page.evaluate(() => ({
                firstFieldBottom: document.querySelector('#cognicion-orientacion_persona').getBoundingClientRect().bottom,
                footerTop: document.querySelector('.rm-modal-footer').getBoundingClientRect().top,
                navigationHeights: [...document.querySelectorAll('.rm-cognicion__nav-short')].map(el => el.getBoundingClientRect().height),
            }));
            assert.ok(mobile.firstFieldBottom <= mobile.footerTop, 'La primera respuesta debe ser visible antes del footer en móvil');
            assert.ok(mobile.navigationHeights.every(height => height <= 24), 'Los nombres de las secciones deben leerse completos');
        }
        assert.equal(await page.evaluate(() => /\b(?:NaN|undefined|Infinity|null)\b|\[object Object\]/.test(document.body.innerText)), false);
        await page.screenshot({path:join(evidenceDir,`cognicion-${width}.png`),fullPage:false});
        await page.evaluate(() => document.querySelector('[aria-controls="cognicion-grafica"]').click());
        await page.waitForFunction(() => document.querySelector('#cognicion-grafica').matches(':popover-open'));
        await checkConsultationLayout(page, '#cognicion-grafica');
        const popup = await page.evaluate(() => {
            const p = document.querySelector('#cognicion-grafica'); const r = p.getBoundingClientRect();
            return {inside:r.x >= 0 && r.y >= 0 && r.right <= innerWidth && r.bottom <= innerHeight, horizontal:p.scrollWidth > p.clientWidth + 1, focused:document.activeElement.hasAttribute('data-trend-move')};
        });
        assert.equal(popup.inside,true); assert.equal(popup.horizontal,false); assert.equal(popup.focused,true);
        await page.keyboard.press('ArrowLeft'); await page.keyboard.press('Home');
        await page.click('#cognicion-grafica [aria-label="Ampliar gráfica cognitiva"]');
        await page.click('#cognicion-grafica [aria-label="Restablecer gráfica cognitiva"]');
        await page.click('.rm-cognicion__point');
        await page.screenshot({path:join(evidenceDir,`cognicion-popup-${width}.png`),fullPage:false});
        await page.keyboard.press('Escape');
        await page.waitForFunction(() => !document.querySelector('#cognicion-grafica').matches(':popover-open'));
        await page.waitForFunction(() => document.activeElement.getAttribute('aria-controls') === 'cognicion-grafica');
        assert.equal(await page.evaluate(() => qaWire.datos.observacion),'Captura pendiente');
        await page.evaluate(() => document.querySelector('[aria-controls="cognicion-historial"]').click());
        await page.waitForFunction(() => document.querySelector('#cognicion-historial').getBoundingClientRect().height > 0);
        await checkConsultationLayout(page, '#cognicion-historial');
        if(width===1280) {
            await page.setViewport({width:390,height:900});
            await page.waitForFunction(selector=>document.querySelector(selector).getAttribute('aria-modal')==='true',{},'#cognicion-historial');
            await page.waitForFunction(selector=>document.querySelector(selector).contains(document.activeElement),{},'#cognicion-historial');
            await page.keyboard.press('Tab');
            assert.ok(await page.evaluate(selector=>document.querySelector(selector).contains(document.activeElement),'#cognicion-historial'),'Foco dentro de la consulta al pasar a móvil');
            await page.setViewport({width:1280,height:900});
            await page.waitForFunction(selector=>document.querySelector(selector).getAttribute('aria-modal')==='false',{},'#cognicion-historial');
            await checkConsultationLayout(page,'#cognicion-historial');
        }
        await page.screenshot({path:join(evidenceDir,`cognicion-history-${width}.png`),fullPage:false});
        await page.keyboard.press('Escape');
        await page.waitForFunction(() => document.querySelector('#cognicion-historial').getBoundingClientRect().height === 0);
        await page.select('#cognicion-orientacion_persona','ORIENTADO');
        await page.waitForFunction(() => qaWire.datos.orientacion_persona === 'ORIENTADO');
        await page.evaluate(() => document.querySelector('input[name="cognicion-sigue_instrucciones"][value="0"] + span').click());
        await page.waitForFunction(() => qaWire.datos.sigue_instrucciones === false);
        await page.evaluate(() => { document.querySelector('#cognicion-seccion-A').open=false; });
        await page.click('.rm-cognicion__section-nav [aria-controls="cognicion-seccion-A"]');
        await page.waitForFunction(() => document.querySelector('#cognicion-seccion-A').open && document.activeElement === document.querySelector('#cognicion-seccion-A > summary'));
        assert.equal(await page.evaluate(() => qaWire.datos.orientacion_persona),'ORIENTADO');
        assert.equal(await page.evaluate(() => qaWire.datos.sigue_instrucciones),false);
        await page.click('.rm-cognicion__section-nav [aria-controls="cognicion-seccion-D"]');
        await page.waitForFunction(() => document.activeElement.hasAttribute('data-section-focus'));
        assert.equal(await page.evaluate(() => qaWire.datos.observacion),'Captura pendiente');
        await page.click('.rm-cognicion__section-nav [aria-controls="cognicion-seccion-A"]');
        const selectedContrast = await page.evaluate(() => {
            const node=document.querySelector('input[name="cognicion-sigue_instrucciones"]:checked + span');
            const style=getComputedStyle(node); const canvas=document.createElement('canvas'); const ctx=canvas.getContext('2d');
            const luminance=color=>{ctx.fillStyle=color;ctx.fillRect(0,0,1,1);const rgb=[...ctx.getImageData(0,0,1,1).data].slice(0,3).map(v=>{const c=v/255;return c<=.04045?c/12.92:((c+.055)/1.055)**2.4;});return rgb[0]*.2126+rgb[1]*.7152+rgb[2]*.0722;};
            const a=luminance(style.color),b=luminance(style.backgroundColor); return (Math.max(a,b)+.05)/(Math.min(a,b)+.05);
        });
        assert.ok(selectedContrast>=4.5);
        await page.screenshot({path:join(evidenceDir,`cognicion-selected-${width}.png`),fullPage:false});
        await page.evaluate(() => document.querySelector('.rm-modal-panel__close').click());
        await page.waitForFunction(() => window.Alpine.$data(document.querySelector('.rm-cognicion')).clinicalDiscardOpen);
        assert.equal(await page.evaluate(() => qaWire.mostrarFormulario),true);
        await page.evaluate(() => [...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Seguir editando').click());
        await page.waitForFunction(() => !window.Alpine.$data(document.querySelector('.rm-cognicion')).clinicalDiscardOpen);
        await page.evaluate(() => [...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Limpiar campos').click());
        await page.waitForFunction(() => window.Alpine.$data(document.querySelector('.rm-cognicion')).clinicalDiscardOpen);
        assert.equal(await page.evaluate(() => qaWire.datos.observacion),'Captura pendiente');
        await page.evaluate(() => [...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Mantener datos').click());
        const overflow = await page.evaluate(() => {const b=document.querySelector('.rm-modal-body');return b.scrollWidth>b.clientWidth+1;});
        assert.equal(overflow,false);
        results.push({width,layout,popup,capturePreserved:true});
    }
    for (const name of ['empty','error']) {
        await page.setViewport({width:1440,height:900});
        await page.goto(`http://127.0.0.1:${server.address().port}/${name}`,{waitUntil:'domcontentloaded'});
        await page.waitForFunction(() => window.qaReady && document.querySelector('.rm-modal-panel').getBoundingClientRect().width>0);
        if(name==='empty') {
            await page.screenshot({path:join(evidenceDir,'cognicion-empty-form.png'),fullPage:false});
            await page.evaluate(() => document.querySelector('[aria-controls="cognicion-grafica"]').click());
            await page.waitForFunction(() => document.querySelector('#cognicion-grafica').matches(':popover-open'));
        await checkConsultationLayout(page, '#cognicion-grafica');
            assert.equal(await page.$$('.rm-cognicion__point').then(p=>p.length),0);
        }
        await page.screenshot({path:join(evidenceDir,`cognicion-${name}.png`),fullPage:false});
    }
    assert.deepEqual(errors,[]);
    writeFileSync(join(evidenceDir,'result.json'),JSON.stringify({scope:'Synthetic Blade + Alpine runtime; backend verified separately by Feature tests',results,errors},null,2));
    console.log(JSON.stringify({viewports:results.length,evidenceDir,errors}));
} finally { await browser?.close(); await new Promise(resolve=>server.close(resolve)); }
