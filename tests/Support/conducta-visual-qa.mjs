// Isolated, synthetic render from RegistroConductaTest. No application session or DB access.
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { createServer } from 'node:http';
import assert from 'node:assert/strict';
import puppeteer from 'puppeteer';

const root = process.cwd();
const fixtureDir = process.env.RM_CONDUCTA_QA_DIR || join(process.env.TEMP, 'remembermind-conducta-qa');
const evidenceDir = resolve(process.env.RM_CONDUCTA_EVIDENCE_DIR || join(fixtureDir, 'evidence'));
mkdirSync(evidenceDir, { recursive: true });
const read = path => readFileSync(join(root, path), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = manifest['resources/frontend/styles/app.css'].file;
const buildFixture = name => {
    let html = readFileSync(join(fixtureDir, `conducta-${name}.html`), 'utf8');
    const rawSnapshot=html.match(/wire:snapshot="([^"]+)"/);
    const snapshot = rawSnapshot ? JSON.parse(rawSnapshot[1].replaceAll('&quot;', '"').replaceAll('&amp;', '&')) : JSON.parse(readFileSync(join(fixtureDir,'conducta-'+name+'.state.json'),'utf8'));
    const unwrap = value => Array.isArray(value) && value.length === 2 && value[1]?.s ? unwrap(value[0]) : value && typeof value === 'object' ? Object.fromEntries(Object.entries(value).map(([key, item]) => [key, unwrap(item)])) : value;
    html = html.replace(/\s+wire:(?:snapshot|id|name|effects|key)="[^"]*"/g, '').replace(/window\.Livewire\.find\('[^']+'\)\.entangle/g, '$wire.entangle');
    return `<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"><script async src="https://unpkg.com/@phosphor-icons/web"></script></head><body class="rm-shell rm-nursing-shell" data-role="nursing"><main class="rm-resident-directory" aria-label="Entorno sintético de QA">${html}</main><script>window.qaInitial=${JSON.stringify(unwrap(snapshot.data))};window.livewireScriptConfig={csrf:'synthetic',uri:'/qa-only'};</script><script type="module" src="/qa.mjs"></script></body></html>`;
};
const moduleSource = `
import '/modules/clinical-form-feedback.js';
import '/modules/free-text-select.js';
import '/modules/signos-vitales-registro.js';
import '/modules/conducta-registro.js';
import '/components/clinical-consultations.js';
const {Livewire, Alpine} = await import('/livewire.esm.js');
const state = Alpine.reactive(window.qaInitial);
const get = name => name.split('.').reduce((v,key)=>v?.[key],state);
const set = (name,value) => {const keys=name.split('.');const last=keys.pop();keys.reduce((v,key)=>v[key],state)[last]=value;};
Object.assign(state,{$set:set,entangle(name){return Alpine.interceptor((initial,getter,setter)=>{Alpine.entangle({get:()=>get(name),set:v=>set(name,v)},{get:getter,set:setter});return get(name);})(get(name));},async guardar(){window.qaSubmits=(window.qaSubmits||0)+1;},async limpiarCaptura(){for(const key of Object.keys(state.datos))state.datos[key]=null;document.querySelector('.rm-conducta').dispatchEvent(new CustomEvent('conducta-captura-reiniciada',{bubbles:true}));},async cerrarModal(){state.mostrarFormulario=false;}});
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
        else if (['/one', '/empty', '/dense', '/favicon.ico'].includes(req.url)) { res.setHeader('Content-Type', 'text/html'); res.end(buildFixture(req.url === '/empty' ? 'empty' : req.url === '/dense' ? 'dense' : 'one')); }
        else { res.statusCode = 404; res.end(); }
    } catch (error) { res.statusCode = 500; res.end('Synthetic fixture error'); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
let browser;
const results = [];
try {
    browser = await puppeteer.launch({ headless: true, executablePath });
    const page = await browser.newPage();
    page.setDefaultTimeout(10000); page.setDefaultNavigationTimeout(30000);
    const errors = [];
    page.on('dialog',async dialog=>{assert.equal(dialog.type(),'beforeunload');await dialog.accept();});
    page.on('pageerror', error => { errors.push(error.message); console.error(error.message); });
    for (const width of [1440,1280,1024,768,390]) {
        await page.setViewport({width,height:900});
        await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
        await page.goto(`http://127.0.0.1:${server.address().port}/one`,{waitUntil:'domcontentloaded'});
        await page.waitForFunction(()=>window.qaReady && document.querySelector('.rm-modal-shell').getBoundingClientRect().width>0);
        await page.waitForFunction(()=>getComputedStyle(document.querySelector('.ph-brain'),'::before').content!=='none'); await page.evaluate(()=>document.fonts.ready);
        const geometry = await page.evaluate(()=>{
            const panel=document.querySelector('.rm-modal-panel').getBoundingClientRect();
            const body=document.querySelector('.rm-modal-body');
            return {fonts:{title:getComputedStyle(document.querySelector('.rm-modal-panel__title')).fontFamily,chip:getComputedStyle(document.querySelector('.rm-conducta__chips button')).fontFamily},width:innerWidth,horizontal:document.documentElement.scrollWidth>innerWidth || body.scrollWidth>body.clientWidth+1,panel:{x:panel.x,y:panel.y,width:panel.width,height:panel.height},columns:getComputedStyle(document.querySelector('.rm-conducta__workspace')).gridTemplateColumns.split(' ').length,motion:matchMedia('(prefers-reduced-motion: reduce)').matches};
        });
        assert.equal(geometry.width,width);
        assert.equal(geometry.horizontal,false,`${width}: no horizontal overflow`);
        assert.equal(geometry.columns,width<=768?1:2);
        assert.ok(geometry.panel.x>=0 && geometry.panel.y>=0 && geometry.panel.height<=900);
        assert.equal(geometry.motion,true);
        assert.equal(await page.evaluate(()=>/\b(?:NaN|undefined|Infinity|null)\b|\[object Object\]/.test(document.body.innerText)),false);
        await page.screenshot({path:join(evidenceDir,`conducta-${width}.png`),fullPage:false});
        await page.click('.rm-conducta__evolution-trigger');
        await page.waitForFunction(()=>document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        await checkConsultationLayout(page, '#conducta-grafica-popup');
        const popup=await page.evaluate(()=>{
            const el=document.querySelector('#conducta-grafica-popup');const r=el.getBoundingClientRect();
            return {x:r.x,y:r.y,width:r.width,height:r.height,overflow:el.scrollWidth>el.clientWidth,focused:document.activeElement.hasAttribute('data-trend-move')};
        });
        assert.ok(popup.x>=0 && popup.y>=0 && popup.x+popup.width<=width && popup.y+popup.height<=900);
        assert.equal(popup.overflow,false); assert.equal(popup.focused,true);
        await page.keyboard.press('ArrowLeft');
        await page.keyboard.press('ArrowDown');
        await page.keyboard.press('Home');
        await page.click('#conducta-grafica-popup [aria-label="Ampliar gráfica"]');
        assert.equal(await page.evaluate(()=>{const r=document.querySelector('#conducta-grafica-popup').getBoundingClientRect();return r.x>=0 && r.y>=0 && r.right<=innerWidth && r.bottom<=innerHeight;}),true,'move/resize remains inside viewport');
        await page.click('#conducta-grafica-popup [aria-label="Restablecer tamaño y posición"]');
        await page.hover('.rm-conducta__line-dot');
        await page.waitForFunction(()=>document.querySelector('[role=tooltip]').getBoundingClientRect().height>0);
        await page.screenshot({path:join(evidenceDir,`conducta-popup-${width}.png`),fullPage:false});
        assert.equal(await page.evaluate(()=>{const popup=document.querySelector('#conducta-grafica-popup');const point=popup.querySelector('.rm-conducta__line-plot').getBoundingClientRect();const body=popup.querySelector('.rm-signos__graph-popup-body').getBoundingClientRect();return point.top>=body.top && point.bottom<=body.bottom;}),true,'plot remains visible inside popup');
        assert.equal(await page.evaluate(()=>getComputedStyle(document.querySelector('.rm-conducta__line-path')).display),'none','one record has no invented line');
        await page.click('.rm-conducta__chart-dates summary');
        await page.focus('.rm-conducta__chart-point');
        await page.waitForFunction(()=>document.querySelector('[role=tooltip]').getBoundingClientRect().height>0);
        await page.keyboard.press('Escape');
        await page.waitForFunction(()=>!document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        await page.waitForFunction(()=>document.activeElement.classList.contains('rm-conducta__evolution-trigger'));
        assert.equal(await page.evaluate(()=>qaWire.mostrarFormulario),true);
        await page.click('input[name="conducta-agitacion"][value="1"] + span');
        await page.waitForFunction(()=>window.qaWire.datos.agitacion===true);
        assert.equal(await page.evaluate(()=>document.querySelector('.rm-conducta__indicator').dataset.observed),'true');
        await page.click('.rm-modal-panel__close');
        await page.waitForFunction(()=>document.querySelector('.rm-conducta__discard').getBoundingClientRect().height>0);
        assert.equal(await page.evaluate(()=>window.qaWire.mostrarFormulario),true);
        await page.evaluate(()=>[...document.querySelectorAll('button')].find(el=>el.textContent.trim()==='Seguir editando').click());
        await page.waitForFunction(()=>document.querySelector('.rm-conducta__workspace').getBoundingClientRect().height>0); await page.click('input[name="conducta-estado_animo"][value="__custom"] + span');
        await page.waitForFunction(()=>document.activeElement.id==='conducta-estado_animo-custom');
        await page.type('#conducta-estado_animo-custom','Descripción sintética');
        await page.waitForFunction(()=>window.qaWire.datos.estado_animo==='Descripción sintética');
        await page.evaluate(()=>document.querySelector('.rm-conducta__evolution-trigger').click());
        await page.waitForFunction(()=>document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        await checkConsultationLayout(page, '#conducta-grafica-popup');
        await page.keyboard.press('Escape');
        await page.waitForFunction(()=>!document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        assert.equal(await page.evaluate(()=>qaWire.datos.estado_animo),'Descripción sintética');
        assert.equal(await page.evaluate(()=>qaWire.datos.agitacion),true);
        assert.equal(await page.evaluate(()=>qaWire.mostrarFormulario),true);
        if(width===1440){
            await page.screenshot({path:join(evidenceDir,'conducta-capture.png'),fullPage:false});
            await page.evaluate(()=>{
                document.querySelectorAll('.rm-conducta__indicator input[value="1"] + span').forEach(el=>el.click());
                document.querySelector('input[name="conducta-estado_animo"][value="TRISTE"] + span').click();
                document.querySelector('.rm-conducta__chips[data-tone="context"] button').click();
            });
            await page.waitForFunction(()=>document.querySelector('.rm-conducta__chips button').dataset.added==='true');
            const choices=await page.evaluate(()=>{
                const canvas=document.createElement('canvas');canvas.width=canvas.height=1;
                const ctx=canvas.getContext('2d',{willReadFrequently:true});
                const luminance=color=>{ctx.clearRect(0,0,1,1);ctx.fillStyle=color;ctx.fillRect(0,0,1,1);const rgb=[...ctx.getImageData(0,0,1,1).data].slice(0,3).map(v=>{const c=v/255;return c<=.04045?c/12.92:((c+.055)/1.055)**2.4;});return rgb[0]*.2126+rgb[1]*.7152+rgb[2]*.0722;};
                return [...document.querySelectorAll('.rm-conducta__indicator input:checked + span,.rm-conducta__mood-options input:checked + span,.rm-conducta__chips button[data-added="true"],.rm-conducta__actions .rm-btn-secondary,.rm-conducta__evolution-trigger')].map(el=>{const s=getComputedStyle(el);const a=luminance(s.color),b=luminance(s.backgroundColor);return {text:el.textContent.trim(),background:s.backgroundColor,contrast:(Math.max(a,b)+.05)/(Math.min(a,b)+.05)};});
            });
            assert.ok(new Set(choices.map(choice=>choice.background)).size>=4,'distinct selected capture groups');
            assert.ok(choices.every(choice=>choice.contrast>=4.5),'selected text contrast');
            await page.screenshot({path:join(evidenceDir,'conducta-selected-colors.png'),fullPage:false});
            writeFileSync(join(evidenceDir,'selected-colors.json'),JSON.stringify(choices,null,2));
        }
        await page.evaluate(()=>{const body=document.querySelector('.rm-modal-body');body.scrollTop=body.scrollHeight;});
        await page.screenshot({path:join(evidenceDir,'conducta-bottom-'+width+'.png'),fullPage:false});
        await page.evaluate(()=>[...document.querySelectorAll('button')].find(el=>el.textContent.trim()==='Ver completo').click());
        await page.waitForFunction(()=>document.querySelector('.rm-conducta__history-overlay').getBoundingClientRect().height>0);
        await checkConsultationLayout(page, '.rm-conducta__history-overlay');
        if(width===1280) {
            await page.setViewport({width:390,height:900});
            await page.waitForFunction(selector=>document.querySelector(selector).getAttribute('aria-modal')==='true',{},'.rm-conducta__history-overlay');
            await page.waitForFunction(selector=>document.querySelector(selector).contains(document.activeElement),{},'.rm-conducta__history-overlay');
            await page.keyboard.press('Tab');
            assert.ok(await page.evaluate(selector=>document.querySelector(selector).contains(document.activeElement),'.rm-conducta__history-overlay'),'Foco dentro de la consulta al pasar a móvil');
            await page.setViewport({width:1280,height:900});
            await page.waitForFunction(selector=>document.querySelector(selector).getAttribute('aria-modal')==='false',{},'.rm-conducta__history-overlay');
            await checkConsultationLayout(page,'.rm-conducta__history-overlay');
        }
        await page.screenshot({path:join(evidenceDir,`conducta-history-${width}.png`),fullPage:false});
        await page.keyboard.press('Tab');
        assert.equal(await page.evaluate(compact=>Boolean(document.activeElement.closest(compact ? '.rm-conducta__history-overlay' : '.rm-modal-shell')),width<1280),true);
        await page.keyboard.press('Escape');
        await page.waitForFunction(()=>document.querySelector('.rm-conducta__history-overlay').getBoundingClientRect().height===0);
        await page.waitForFunction(()=>document.activeElement.textContent.trim()==='Ver completo');
        await page.evaluate(()=>[...document.querySelectorAll('button')].find(el=>el.textContent.trim()==='Limpiar campos'&&el.getClientRects().length).click());
        await page.waitForFunction(()=>document.querySelector('.rm-conducta__discard').getBoundingClientRect().height>0);
        assert.equal(await page.evaluate(()=>qaWire.datos.agitacion),true);
        await page.evaluate(()=>[...document.querySelectorAll('button')].find(el=>el.textContent.trim()==='Limpiar campos'&&el.getClientRects().length).click());
        await page.waitForFunction(()=>qaWire.datos.agitacion===null&&qaWire.datos.estado_animo===null);
        results.push({...geometry,status:'PASS'});
    }
    await page.setViewport({width:1440,height:900});
    await page.goto(`http://127.0.0.1:${server.address().port}/empty`,{waitUntil:'domcontentloaded'});
    await page.waitForFunction(()=>window.qaReady);
    await page.click('.rm-conducta__evolution-trigger');
    await page.waitForFunction(()=>document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        await checkConsultationLayout(page, '#conducta-grafica-popup');
    assert.equal(await page.$('.rm-conducta__chart-point'),null);
    await page.screenshot({path:join(evidenceDir,'conducta-empty.png'),fullPage:false});
    await page.goto(`http://127.0.0.1:${server.address().port}/one`,{waitUntil:'domcontentloaded'});
    await page.waitForFunction(()=>window.qaReady);
    await page.click('.rm-conducta__evolution-trigger');
    await page.waitForFunction(()=>document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        await checkConsultationLayout(page, '#conducta-grafica-popup');
    assert.equal((await page.$$('.rm-conducta__chart-point')).length,1);
    await page.click('.rm-conducta__chart-dates summary');
    await page.focus('.rm-conducta__chart-point');
    await page.waitForFunction(()=>document.querySelector('[role=tooltip]').getBoundingClientRect().height>0);
    assert.equal(await page.evaluate(()=>document.querySelector('[role=tooltip]').innerText.includes('Agitación')),true);
    for(const width of [1440,390]){
        await page.setViewport({width,height:900});
        await page.goto('http://127.0.0.1:'+server.address().port+'/dense',{waitUntil:'domcontentloaded'});
        await page.waitForFunction(()=>window.qaReady);
        await page.click('.rm-conducta__evolution-trigger');
        await page.waitForFunction(()=>document.querySelector('#conducta-grafica-popup').matches(':popover-open'));
        await checkConsultationLayout(page, '#conducta-grafica-popup');
        assert.equal(await page.evaluate(()=>{const body=document.querySelector('.rm-modal-body');return body.scrollWidth>body.clientWidth;}),false);
        assert.equal((await page.$$('.rm-conducta__chart-point')).length,15);
        assert.equal((await page.$$('.rm-conducta__line-dot')).length,15);
        assert.equal(await page.evaluate(()=>/NaN|Infinity|undefined/.test(document.querySelector('.rm-conducta__line-path').getAttribute('points'))),false);
        await page.evaluate(()=>document.querySelector('#conducta-grafica-popup .rm-signos__graph-popup-body').scrollTop=0);
        await page.screenshot({path:join(evidenceDir,'conducta-dense-'+width+'.png'),fullPage:false});
        await page.click('.rm-conducta__chart-dates summary');
        assert.equal(await page.evaluate(()=>[...document.querySelectorAll('.rm-conducta__chart-point')].every(el=>{const r=el.getBoundingClientRect();return r.width>=44&&r.height>=44;})),true);
    }
    assert.deepEqual(errors,[]);
    writeFileSync(join(evidenceDir,'result.json'),JSON.stringify({scope:'Rendered production Blade/CSS + real Alpine; synthetic transport. Backend independently tested with real DB.',viewports:results,empty:'PASS',one:'PASS',tooltip:'PASS',errors},null,2));
    console.log(JSON.stringify({status:'PASS',evidence:evidenceDir,viewports:results.map(item=>item.width)}));
} finally {
    await browser?.close();
    await new Promise(resolve=>server.close(resolve));
}
