// HTML/estado de pruebas Livewire sintéticas. Verifica presentación; PHPUnit prueba servidor.
import {readFileSync,writeFileSync,mkdirSync,existsSync} from 'node:fs';
import {join,resolve} from 'node:path';
import {createServer} from 'node:http';
import assert from 'node:assert/strict';
import puppeteer from 'puppeteer';
const root=process.cwd(), fixture=process.env.RM_MEDICATION_QA_DIR;
const theme=process.env.RM_MEDICATION_QA_THEME==='dark'?'dark':'light';
const widths=theme==='dark'?[1440,390]:[1440,1280,1024,768,390];
const states=theme==='dark'?['selected','admin','omitted','photo','windows']:['initial','selected','prescription','photo','admin','omitted','invalid','discard','saved','windows'];
if(!fixture) throw new Error('Indica RM_MEDICATION_QA_DIR con las vistas sintéticas exportadas.');
const build=process.env.RM_MEDICATION_BUILD_DIR || join(root,'public/build');
const evidence=resolve(process.env.RM_MEDICATION_EVIDENCE_DIR || join(fixture,theme==='dark'?'evidence-dark':'evidence'));
mkdirSync(evidence,{recursive:true});
const manifest=JSON.parse(readFileSync(join(build,'manifest.json'),'utf8'));
const css=manifest['resources/frontend/styles/app.css'].file, js=manifest['resources/frontend/scripts/app.js'].file;
const styles=[...new Set([css,...(manifest['resources/frontend/scripts/app.js'].css||[])])];
const unwrap=value=>Array.isArray(value)&&value.length===2&&value[1]?.s ? unwrap(value[0]) : Array.isArray(value) ? value.map(unwrap) : value&&typeof value==='object'?Object.fromEntries(Object.entries(value).map(([k,v])=>[k,unwrap(v)])):value;
const html=name=>{
    let body=readFileSync(join(fixture,`medicacion-${name}.html`),'utf8');
    const snapshot=JSON.parse(readFileSync(join(fixture,`medicacion-${name}.state.json`),'utf8'));
    body=body.replace(/\s+wire:(?:snapshot|id|name|effects|key)="[^"]*"/g,'').replace(/window\.Livewire\.find\('[^']+'\)\.entangle/g,'$wire.entangle');
    body=body.replace(/src="https?:\/\/[^\"]+\/storage\/imagenes\/medicamentos\/([A-Za-z0-9_-]+\.png)"/g,'src="/storage/imagenes/medicamentos/$1"');
    return `<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">${styles.map(file=>`<link rel="stylesheet" href="/build/${file}">`).join('')}<script defer src="https://unpkg.com/@phosphor-icons/web"></script></head><body class="rm-shell rm-nursing-shell"><main aria-label="QA sintético">${body}</main><script>window.qaInitial=${JSON.stringify(unwrap(snapshot.data))};window.livewireScriptConfig={csrf:'synthetic',uri:'/qa-only'};</script><script type="module" src="/qa.mjs"></script></body></html>`;
};
const moduleSource=`
await import('/build/${js}');
const {Livewire,Alpine}=await import('/livewire.esm.js');
const state=Alpine.reactive(window.qaInitial);
const get=name=>name.split('.').reduce((v,k)=>v?.[k],state);
const set=(name,value)=>{const keys=name.split('.');const last=keys.pop();keys.reduce((v,k)=>v[k],state)[last]=value;};
Object.assign(state,{$set:set,entangle(name){return Alpine.interceptor((initial,getter,setter)=>{Alpine.entangle({get:()=>get(name),set:v=>set(name,v)},{get:getter,set:setter});return get(name);})(get(name));},async guardar(){window.qaSubmits=(window.qaSubmits||0)+1;},async solicitarSalida(action){state.confirmacion=action;},async cancelarConfirmacion(){state.confirmacion='';},async cerrarPrescripcion(){state.mostrarPrescripcion=false;},async cerrarImagen(){state.mostrarImagen=false;}});
Alpine.magic('wire',()=>state);
document.querySelectorAll('*').forEach(el=>{if(el.hasAttribute('wire:loading'))el.style.display='none';});
document.querySelectorAll('input,select,textarea').forEach(field=>{const attr=[...field.attributes].find(a=>a.name.startsWith('wire:model'));if(!attr)return;const name=attr.value;if(field.type==='radio')field.checked=field.value===get(name);else field.value=get(name)??'';field.addEventListener(field.type==='radio'||field.tagName==='SELECT'?'change':'input',()=>set(name,field.value));Alpine.effect(()=>{if(field.type==='radio')field.checked=field.value===get(name);else field.value=get(name)??'';});});
Livewire.start();window.qaWire=state;window.qaReady=true;
`;
const server=createServer((req,res)=>{
    try {
        const pathname=new URL(req.url,'http://localhost').pathname;
        if(pathname==='/qa.mjs'){res.setHeader('Content-Type','application/javascript');res.end(moduleSource);}
        else if(pathname.startsWith('/storage/imagenes/medicamentos/')&&pathname.endsWith('.png')){res.setHeader('Content-Type','image/png');res.end(readFileSync(join(fixture,'medication-test.png')));}
        else if(pathname==='/livewire.esm.js'){res.setHeader('Content-Type','application/javascript');res.end(readFileSync(join(root,'vendor/livewire/livewire/dist/livewire.esm.js')));}
        else if(pathname.startsWith('/build/')&&!pathname.includes('..')){res.setHeader('Content-Type',pathname.endsWith('.css')?'text/css':pathname.endsWith('.js')?'application/javascript':'font/woff2');res.end(readFileSync(join(build,pathname.slice(7))));}
        else if(/^\/(initial|selected|prescription|photo|admin|omitted|saved|windows|discard|invalid)$/.test(pathname)){res.setHeader('Content-Type','text/html');res.end(html(pathname.slice(1)));}
        else {res.statusCode=404;res.end();}
    }catch(error){res.statusCode=500;res.end('Synthetic fixture error');}
});
await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));
const executablePath=[process.env.PUPPETEER_EXECUTABLE_PATH,await puppeteer.executablePath(),'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(p=>p&&existsSync(p));
const browser=await puppeteer.launch({headless:true,executablePath});const page=await browser.newPage();page.setDefaultTimeout(12000);
const errors=[],results=[],contrasts=[];page.on('pageerror',e=>{errors.push(e.message);process.stderr.write(e.message+'\n');});
await page.evaluateOnNewDocument(value=>localStorage.setItem('remembermind-theme',value),theme);
try {
    for(const width of widths) {
        await page.setViewport({width,height:950});await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
        for(const name of states) {
            await page.goto(`http://127.0.0.1:${server.address().port}/${name}`,{waitUntil:'networkidle0'});
            await page.waitForFunction(()=>window.qaReady&&document.querySelector('.rm-medication > .rm-modal-shell > .rm-modal-panel').getBoundingClientRect().width>0);
            const bounds=await page.evaluate(()=>{const panel=document.querySelector('.rm-medication > .rm-modal-shell > .rm-modal-panel'),r=panel.getBoundingClientRect();return {left:r.left,right:r.right,width:r.width,scroll:panel.scrollWidth,client:panel.clientWidth,body:document.body.scrollWidth};});
            assert.ok(bounds.left>=-1&&bounds.right<=width+1,`${name}/${width}: modal fuera del viewport`);
            assert.ok(bounds.scroll<=bounds.client+2,`${name}/${width}: scroll horizontal de modal`);
            assert.ok(bounds.body<=width+2,`${name}/${width}: scroll horizontal global`);
            const visibleText=await page.$eval('body',el=>el.innerText);
            assert.ok(!/\b(?:null|NaN|undefined|Infinity)\b|\[object Object\]/.test(visibleText),`${name}/${width}: valor técnico inválido visible`);
            const bodyHeight=await page.$eval('.rm-medication > .rm-modal-shell > .rm-modal-panel .rm-modal-body',el=>el.getBoundingClientRect().height);
            assert.ok(bodyHeight>=250,`${name}/${width}: cuerpo del formulario sin espacio suficiente`);
            assert.deepEqual(await page.$$eval('#formAdministracion > .rm-medication__section',els=>els.map(el=>el.getAttribute('aria-labelledby'))),['med-dose-title','med-order-title','med-result-title','med-safety-title','med-confirm-title'],'Lectura y tabulación en orden lógico');
            const consultations=await page.$$eval('.rm-medication__toolbar .rm-btn',els=>els.map(el=>{const r=el.getBoundingClientRect();return {width:r.width,height:r.height,top:r.top,bottom:r.bottom};}));
            for(const button of consultations) assert.ok(button.width>=44&&button.height>=44&&button.top>=0&&button.bottom<=950,'Consulta visible y accesible');
            if(name==='selected') {
                await page.$eval('.rm-medication__medication',el=>el.scrollIntoView({block:'start'}));
                assert.ok(await page.$eval('.rm-medication-media__illustration svg',el=>el.getBoundingClientRect().height>100));
                await page.screenshot({path:join(evidence,`medicacion-imagen-${width}.png`),fullPage:false});
            }
            if(name==='photo') {
                await page.waitForSelector('.rm-medication__image-dialog.is-open');
                assert.equal(await page.$eval('#medication-enlarged-photo-foil',el=>Boolean(el)),true);
            }
            if(name==='initial') assert.equal(await page.$$eval('input[name="resultado"]:checked',els=>els.length),0);
            if(name==='admin') assert.equal(await page.$eval('#med-dosis-real',el=>el.value),'50');
            if(name==='admin') {
                const samples=await page.evaluate(()=>{
                    const canvas=document.createElement('canvas');canvas.width=canvas.height=1;const ctx=canvas.getContext('2d',{willReadFrequently:true});
                    const rgba=color=>{ctx.clearRect(0,0,1,1);ctx.fillStyle=color;ctx.fillRect(0,0,1,1);return [...ctx.getImageData(0,0,1,1).data].map((n,i)=>i===3?n/255:n);};
                    const blend=(fg,bg)=>fg.slice(0,3).map((c,i)=>c*fg[3]+bg[i]*(1-fg[3]));
                    const lum=rgb=>rgb.map(c=>{const n=c/255;return n<=.04045?n/12.92:((n+.055)/1.055)**2.4;}).reduce((sum,n,i)=>sum+n*[.2126,.7152,.0722][i],0);
                    return ['label[for="med-dosis-real"]','.rm-medication__toolbar .rm-btn-secondary','button[type="submit"][form="formAdministracion"]'].map(selector=>{
                        const el=document.querySelector(selector),parents=[];for(let node=el;node;node=node.parentElement)parents.unshift(node);
                        let background=[255,255,255];for(const parent of parents)background=blend(rgba(getComputedStyle(parent).backgroundColor),background);
                        // Incluye el highlight superior configurado en el botón compartido.
                        if(el.classList.contains('rm-btn-primary')){const page=rgba(getComputedStyle(el).getPropertyValue('--rm-palette-page'));page[3]=parseFloat(getComputedStyle(el).getPropertyValue('--rm-button-primary-highlight'))/100;background=blend(page,background);}
                        const foreground=blend(rgba(getComputedStyle(el).color),background),values=[lum(foreground),lum(background)].sort((a,b)=>b-a);
                        return {selector,foreground,background,ratio:(values[0]+.05)/(values[1]+.05)};
                    });
                });
                for(const sample of samples)assert.ok(sample.ratio>=4.5,`${width}: contraste ${JSON.stringify(sample)}`);
                contrasts.push({width,samples});
            }
            if(name==='omitted') {assert.equal(await page.$('#med-dosis-real'),null);assert.equal(await page.$eval('#med-motivo',el=>el.value),'Rechazo observado');}
            if(name==='windows') {
                await page.waitForFunction(()=>document.querySelectorAll('.rm-signos__graph-popup:popover-open').length===2);
                await page.waitForFunction(()=>window.RMCharts.has('medication-results-chart'));
                assert.equal(await page.evaluate(()=>window.RMCharts.get('medication-results-chart').data.datasets[0].data[0]),1);
                const windows=await page.$$eval('.rm-signos__graph-popup:popover-open',els=>els.map(el=>{const r=el.getBoundingClientRect();return {left:r.left,right:r.right,top:r.top,bottom:r.bottom};}));
                for(const b of windows) assert.ok(b.left>=0&&b.right<=width+1&&b.top>=0&&b.bottom<=951,'Ventana fuera del viewport');
                const popupOverflows=await page.$$eval('.rm-signos__graph-popup:popover-open .rm-signos__graph-popup-body',els=>els.map(el=>el.scrollWidth>el.clientWidth+2));
                assert.ok(popupOverflows.every(value=>!value),'Filtros y gráfica caben dentro de sus ventanas');
                if(width<640) {
                    await page.focus('#medicacion-evolucion [aria-label="Cerrar evolucion"]');
                    for(let tab=0;tab<8;tab++){await page.keyboard.press('Tab');assert.ok(await page.evaluate(()=>document.querySelector('#medicacion-evolucion').contains(document.activeElement)),'El foco móvil salió de la ventana activa');}
                }
                if(width>=1024){await page.focus('#medicacion-historial [data-trend-move]');await page.keyboard.press('ArrowRight');await page.click('#medicacion-historial [aria-label="Reducir historial"]');}
                await page.screenshot({path:join(evidence,`medicacion-both-${width}.png`),fullPage:false});
                await page.click('#medicacion-evolucion [aria-label="Cerrar evolucion"]');
                try { await page.waitForFunction(()=>document.querySelectorAll('.rm-signos__graph-popup:popover-open').length===1); }
                catch(error) { process.stderr.write(JSON.stringify(await page.evaluate(()=>{const button=document.querySelector('#medicacion-evolucion [aria-label="Cerrar evolucion"]'),r=button.getBoundingClientRect();return {edge:[...document.querySelectorAll('.rm-signos__graph-edge--e')].map(el=>({w:getComputedStyle(el).width,min:getComputedStyle(el).minWidth,pos:getComputedStyle(el).position,rect:el.getBoundingClientRect().toJSON()})),wire:[qaWire.mostrarHistorial,qaWire.mostrarEvolucion],hit:document.elementFromPoint(r.x+r.width/2,r.y+r.height/2)?.outerHTML,windows:[...document.querySelectorAll('.rm-signos__graph-popup')].map(el=>({id:el.id,open:el.matches(':popover-open')}))};}))+'\n');throw error; }
                assert.equal(await page.evaluate(()=>qaWire.cod_horario_prescripcion),'HPR_TEST_01');
                if(width>=1280) {
                    await page.waitForFunction(()=>document.querySelector('.rm-medication > .rm-modal-shell').hasAttribute('data-clinical-consultation-side'));
                    await page.evaluate(()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))));
                    const historyLayout=await page.evaluate(()=>{const p=document.querySelector('#medicacion-historial').getBoundingClientRect(),f=document.querySelector('.rm-medication > .rm-modal-shell > .rm-modal-panel').getBoundingClientRect();return {left:p.left,right:p.right,formLeft:f.left,formRight:f.right};});
                    assert.ok(historyLayout.formRight+8<=historyLayout.left||historyLayout.right+8<=historyLayout.formLeft,'Historial y captura visibles lado a lado');
                }
                await page.screenshot({path:join(evidence,`medicacion-history-${width}.png`),fullPage:false});
                await page.evaluate(()=>{qaWire.mostrarHistorial=false;qaWire.mostrarEvolucion=true;});
                await page.waitForFunction(()=>document.querySelector('#medicacion-evolucion').matches(':popover-open')&&!document.querySelector('#medicacion-historial').matches(':popover-open'));
                const assertSideBySide = async () => {
                    await page.evaluate(()=>new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))));
                    const layout=await page.evaluate(()=>{
                        const panel=document.querySelector('.rm-medication > .rm-modal-shell > .rm-modal-panel'),popup=document.querySelector('#medicacion-evolucion');
                        const form=panel.getBoundingClientRect(),evolution=popup.getBoundingClientRect();
                        return {formRight:form.right,formWidth:form.width,popupLeft:evolution.left,popupRight:evolution.right,viewport:innerWidth,scroll:panel.scrollWidth,client:panel.clientWidth};
                    });
                    assert.ok(layout.formWidth>=700,`Formulario legible: ${JSON.stringify(layout)}`);
                    assert.ok(layout.formRight+8<=layout.popupLeft,`Captura y evolución sin solaparse: ${JSON.stringify(layout)}`);
                    assert.ok(layout.popupRight<=layout.viewport+1&&layout.scroll<=layout.client+2,'Sin overflow al compartir el espacio');
                };
                if(width>=1280) {
                    await assertSideBySide();
                    await page.click('#medicacion-evolucion [aria-label="Ampliar evolucion"]');
                    await assertSideBySide();
                    await page.click('#medicacion-evolucion [aria-label="Restablecer evolucion"]');
                    await assertSideBySide();
                }
                await page.screenshot({path:join(evidence,`medicacion-evolution-${width}.png`),fullPage:false});
                await page.click('#medicacion-evolucion [aria-label="Cerrar evolucion"]');
                await page.waitForFunction(()=>!document.querySelector('#medicacion-evolucion').matches(':popover-open'));
                await page.waitForFunction(()=>!document.querySelector('.rm-medication > .rm-modal-shell').hasAttribute('data-clinical-consultation-side'));
                const restored=await page.$eval('.rm-medication > .rm-modal-shell > .rm-modal-panel',el=>{const r=el.getBoundingClientRect();return {left:r.left,right:r.right};});
                assert.ok(Math.abs(restored.left-(width-restored.right))<=2,'El formulario vuelve al centro al cerrar evolución');
                assert.equal(await page.evaluate(()=>qaWire.cod_horario_prescripcion),'HPR_TEST_01','La consulta conserva la dosis seleccionada');
            }
            if(['admin','omitted','invalid'].includes(name)) await page.evaluate(()=>document.querySelector('#med-dosis-real, #med-motivo')?.scrollIntoView({block:'center'}));
            await page.screenshot({path:join(evidence,`medicacion-${name}-${width}.png`),fullPage:false});
            results.push({width,theme,state:name,viewport:true,noHorizontalScroll:true});
        }
    }
    assert.deepEqual(errors,[]);writeFileSync(join(evidence,'resultados.json'),JSON.stringify({results,contrasts,errors},null,2));
    process.stdout.write(JSON.stringify({theme,viewports:widths.length,states:states.length,captures:results.length,errors:errors.length,evidence})+'\n');
} finally {await browser.close();await new Promise(resolve=>server.close(resolve));}
