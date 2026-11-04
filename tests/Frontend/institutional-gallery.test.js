// QA offline: HTML de Livewire, imágenes sintéticas y CSS reales; sin sesión de la aplicación.
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';
import { createServer } from 'node:http';
import puppeteer from 'puppeteer';

const root=process.cwd();
const fixtureDir=process.env.RM_GALLERY_QA_DIR;
const executablePath=[process.env.PUPPETEER_EXECUTABLE_PATH,await puppeteer.executablePath(),'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p=>p&&existsSync(p));
const ready=executablePath&&fixtureDir&&existsSync(join(fixtureDir,'gallery-poblada.html'));

test('Galería: siete estados en cinco anchos y ambos temas, teclado y límites antes de subir', {skip:!ready},async()=>{
    const css=JSON.parse(readFileSync(join(root,'public/build/manifest.json'),'utf8'))['resources/frontend/styles/app.css'].file;
    const evidence=process.env.RM_GALLERY_EVIDENCE_DIR;
    if(evidence)mkdirSync(evidence,{recursive:true});
    const server=createServer((req,res)=>{
        try{
            const path=new URL(req.url,'http://fixture.local').pathname;
            if(path==='/livewire.esm.js'){res.setHeader('Content-Type','application/javascript');res.end(readFileSync(join(root,'vendor/livewire/livewire/dist/livewire.esm.js')));}
            else if(path==='/qa.mjs'){res.setHeader('Content-Type','application/javascript');res.end(`const {Livewire,Alpine}=await import('/livewire.esm.js');Alpine.magic('wire',()=>({$set(){},on(){},dispatch(){},entangle(){return false;}}));Livewire.start();await document.fonts.ready;window.qaReady=true;`);}
            else if(path.startsWith('/build/')&&!path.includes('..')){res.setHeader('Content-Type',path.endsWith('.css')?'text/css':'font/woff2');res.end(readFileSync(join(root,'public',path)));}
            else if(path.startsWith('/fixture/')){
                const name=path.slice('/fixture/'.length);assert.match(name,/^[a-z]+$/);
                const html=readFileSync(join(fixtureDir,`gallery-${name}.html`),'utf8').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi,'').replace(/\s+wire:(?:snapshot|id|effects|key|name)="[^"]*"/g,'');
                res.setHeader('Content-Type','text/html');res.end(`<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"><style>[wire\\:loading]{display:none}</style></head><body class="rm-shell" data-role="manager"><main data-rm-main style="margin:auto;width:100%;max-width:1216px;padding:16px">${html}</main><script>window.livewireScriptConfig={csrf:'synthetic',uri:'/qa-only'};</script><script type="module" src="/qa.mjs"></script></body></html>`);
            }else{res.statusCode=404;res.end();}
        }catch(error){res.statusCode=500;res.end(String(error));}
    });
    await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));
    const base=`http://127.0.0.1:${server.address().port}`;
    const browser=await puppeteer.launch({headless:true,executablePath});
    const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await page.setRequestInterception(true);page.on('request',r=>r.url().startsWith(base+'/')||r.url().startsWith('data:')?r.continue():r.abort());
    let checked=0;
    try{
        for(const width of [1440,1280,1024,768,390])for(const theme of ['light','dark'])for(const state of ['vacio','formulario','previsualizacion','poblada','edicion','errores','archivada']){
            await page.setViewport({width,height:900});await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
            await page.goto(base+'/fixture/'+state);await page.waitForFunction(()=>window.qaReady);
            await page.evaluate(theme=>{document.documentElement.dataset.theme=theme;document.documentElement.classList.toggle('dark',theme==='dark');},theme);
            const result=await page.evaluate(()=>{
                const visible=e=>e.checkVisibility()&&e.getBoundingClientRect().width>0;
                return{
                    overflow:[...document.querySelectorAll('main,.rm-gallery,.rm-gallery-editor,.rm-gallery-filters,.rm-gallery-photo')].filter(visible).filter(e=>e.scrollWidth>e.clientWidth+2).map(e=>e.className),
                    small:[...document.querySelectorAll('button,input:not([type="checkbox"]),select,textarea')].filter(visible).filter(e=>e.getBoundingClientRect().height<43.9).map(e=>e.outerHTML.slice(0,100)),
                    labels:[...document.querySelectorAll('input,select,textarea')].filter(e=>(!e.id||!document.querySelector('label[for="'+e.id+'"]'))&&!e.closest('label')).length,
                    images:[...document.images].filter(visible).map(e=>e.complete&&e.naturalWidth>0),
                    filterHeight:document.querySelector('.rm-gallery-filters').getBoundingClientRect().height,
                    photoTop:document.querySelector('.rm-gallery-photo')?.getBoundingClientRect().top,
                    forbidden:/\b(null|NaN|undefined|Infinity)\b|\[object Object\]/.test(document.querySelector('main').innerText),
                };
            });
            const context=`${state} ${width}px ${theme}`;
            assert.deepEqual(result.overflow,[],context+' reflow');assert.deepEqual(result.small,[],context+' touch');assert.equal(result.labels,0,context+' labels');assert.equal(result.forbidden,false,context);
            assert.ok(result.images.every(Boolean),context+' fotografías visibles');
            assert.ok(result.filterHeight<240,context+' filtros compactos: '+result.filterHeight);
            if(state==='poblada')assert.ok(result.photoTop<700,context+' la fotografía aparece en la primera pantalla');
            await page.focus('button');assert.equal(await page.evaluate(()=>getComputedStyle(document.activeElement).outlineStyle),'solid',context+' foco');
            if(evidence&&theme==='light'&&[390,1440].includes(width))await page.screenshot({path:join(evidence,`gallery-${state}-${width}.png`),fullPage:true});
            checked++;
        }
        await page.goto(base+'/fixture/formulario');await page.waitForFunction(()=>window.qaReady);
        await page.$eval('#gallery-files',input=>{const data=new DataTransfer();data.items.add(new File([new Uint8Array(2097153)],'demasiado.png',{type:'image/png'}));input.files=data.files;input.dispatchEvent(new Event('change',{bubbles:true}));});
        await page.waitForFunction(()=>document.querySelector('form button[type="submit"]').disabled);
        assert.match(await page.$eval('[x-text="errorCarga"]',e=>e.innerText),/2 MB/,'Límite individual antes de subir');
        assert.equal(await page.$eval('#gallery-files',e=>e.files.length),0,'Selección excesiva rechazada');
        await page.goto(base+'/fixture/formulario');await page.waitForFunction(()=>window.qaReady);
        await page.type('#gallery-title','Cambios sin guardar');let dialogs=0;
        page.once('dialog',async d=>{dialogs++;await d.dismiss();});await page.click('button[wire\\:click="cerrar"]');
        assert.equal(dialogs,1,'Cancelar confirma cambios locales aún no enviados');
        assert.equal(checked,70);assert.deepEqual(errors,[],'Sin errores Alpine/Livewire en fixtures');
    }finally{await browser.close();await new Promise(resolve=>server.close(resolve));}
});
