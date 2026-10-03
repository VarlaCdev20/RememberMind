import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, existsSync } from 'node:fs';
import { createServer } from 'node:http';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = file => readFileSync(new URL(file, root), 'utf8');
const shell = read('resources/views/components/layout/sidebar-shell-state.blade.php');
const blade = read('resources/views/components/layout/barra-lateral-sistema.blade.php');
const sidebarData = blade.match(/x-data="([\s\S]*?)"\s+x-on:livewire:navigating/)[1]
    .replace('@js($sidebarStorageKey)', "'sidebar-test-context'")
    .replace("@js(array_map(static fn ($section) => $section['title'], $sections))", "['Sistema', 'Institución']")
    .replace(/\{\{ \$initialOpen[^}]+\}\}/, '0');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = manifest['resources/frontend/styles/app.css'].file;
const browserPath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));
assert.ok(browserPath, 'Se necesita Chrome para verificar la interacción real del sidebar.');
const fixture = path => `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/build/${css}"><style>${read('resources/frontend/styles/modules/pages-welcome.css')}</style><script>document.addEventListener('alpine:init',()=>Alpine.data('shell',()=>({${shell}})));</script><script src="/livewire.js" data-csrf="test" data-update-uri="/update" defer></script></head>
<body class="rm-shell" x-data="shell" x-init="initSidebar()" :class="{'is-sidebar-collapsed':sidebarCollapsed}" @resize.window.debounce.150ms="syncSidebarViewport()">
<button id="sidebar-mobile-trigger" @click="sidebarOpen=true">Abrir</button><div class="rm-sidebar-overlay" x-show="sidebarOpen" @click="sidebarOpen=false" style="display:none"></div>
<aside class="rm-sidebar" wire:transition.navigate="rm-sidebar" :class="{'is-mobile-open':sidebarOpen}" x-data="${sidebarData}" @livewire:navigating.window="saveSidebarView();closeSidebarForNavigation()" x-init="restoreSidebarView();$watch('openSection',()=>saveSidebarView())" @keydown.escape.window="sidebarOpen=false">
<header class="rm-sidebar__header"><span class="rm-sidebar__brand-copy"><strong class="rm-brand">RememberMind</strong></span><button id="collapse" class="rm-sidebar__header-toggle" @click="toggleSidebarCollapse()">‹</button><button class="rm-sidebar__mobile-close" x-ref="mobileClose" @click="sidebarOpen=false">Cerrar</button></header>
<nav class="rm-sidebar__nav" x-ref="navigation" @scroll.debounce.100ms="saveSidebarView()">
<p class="rm-sidebar__group rm-nav-section">SISTEMA</p><div class="rm-sidebar__entry"><button id="group" class="rm-sidebar__item" :aria-expanded="openSection===0" @click="openSection=openSection===0?null:0">Sistema</button>
<div class="rm-sidebar__submenu" x-show="openSection===0" x-collapse.duration.200ms><div class="rm-sidebar__submenu-content" :class="{'is-visible':openSection===0}"><a href="/second" wire:navigate @click="sidebarOpen=false" class="rm-sidebar__subitem is-active" aria-current="page"><span class="rm-sidebar__subindicator"></span>Usuarios</a></div></div></div>
${Array.from({length:30},(_,i)=>`<a class="rm-sidebar__item" href="/second" wire:navigate @click="sidebarOpen=false">Sección de ejemplo ${i}</a>`).join('')}
</nav><footer class="rm-sidebar__footer"><div class="rm-sidebar__account-trigger">Cuenta de ejemplo</div></footer></aside><main data-rm-main style="min-height:1800px;padding:24px"><h1>${path}</h1><div style="position:fixed;left:-10000px;top:0;width:320px;pointer-events:none"><div id="theme-card" class="rm-card">Card de referencia</div><input id="theme-input" class="rm-input" aria-label="Dato de referencia" value="Ejemplo"><table class="rm-table"><thead id="theme-table-head"><tr><th>Columna</th></tr></thead><tbody><tr><td>Ejemplo</td></tr></tbody></table><div id="theme-modal" class="rm-modal-panel">Modal de referencia</div><div id="theme-topbar" style="background:var(--rm-topbar-bg)">Encabezado de referencia</div><div class="welcome-page"><section id="theme-public-hero" class="welcome-hero">Inicio</section><section id="theme-public-services" class="welcome-services-detail">Servicios</section><footer id="theme-public-footer" class="welcome-footer">Pie público</footer></div></div></main></body></html>`;
const server = createServer((req,res) => {
    try {
        if (req.url.startsWith('/build/')) {
            const file = req.url.slice(1);
            res.setHeader('Content-Type', file.endsWith('.css') ? 'text/css' : 'font/woff2');
            res.end(read(`public/${file}`));
        } else if (req.url === '/livewire.js') {
            res.setHeader('Content-Type', 'application/javascript'); res.end(read('vendor/livewire/livewire/dist/livewire.js'));
        } else { res.setHeader('Content-Type','text/html');res.end(fixture(req.url)); }
    } catch (error) { res.statusCode=500;res.end(error.message); }
});
const settle = page => page.waitForFunction(()=>window.Alpine && document.querySelector('.rm-sidebar')?._x_dataStack);
const state = page => page.evaluate(()=>({ width:document.querySelector('.rm-sidebar').getBoundingClientRect().width,collapsed:Alpine.$data(document.body).sidebarCollapsed,range:Alpine.$data(document.body).sidebarRange,locked:document.documentElement.classList.contains('rm-sidebar-scroll-locked'),preference:localStorage.getItem('remembermind-sidebar-collapsed'),scroll:document.querySelector('nav').scrollTop,open:Alpine.$data(document.querySelector('aside')).openSection }));

test('sidebar adaptativo: preferencia desktop, laptop temporal, drawer y persistencia durante navegación', async () => {
    await new Promise(resolve=>server.listen(0,'127.0.0.1',resolve));
    const origin=`http://127.0.0.1:${server.address().port}`;
    const browser=await puppeteer.launch({headless:true,executablePath:browserPath});
    try {
        const page=await browser.newPage();page.setDefaultTimeout(6000);const errors=[];page.on('pageerror',error=>errors.push(error.message));
        await page.setViewport({width:1440,height:900});await page.goto(origin+'/first');await settle(page);
        await page.waitForFunction(()=>document.body.hasAttribute('data-rm-sidebar-ready'));
        assert.equal((await state(page)).width,224);
        const navigationType=await page.evaluate(()=>['.rm-nav-section','#group','.rm-sidebar__subitem'].map(selector=>{const style=getComputedStyle(document.querySelector(selector));return {family:style.fontFamily,size:style.fontSize,weight:style.fontWeight,tracking:style.letterSpacing,case:style.textTransform};}));
        assert.match(navigationType[0].family,/Nunito Sans/);assert.equal(navigationType[0].size,'11px');assert.equal(navigationType[0].weight,'600');assert.equal(navigationType[0].tracking,'1.1px');assert.equal(navigationType[0].case,'lowercase');
        assert.match(navigationType[1].family,/Plus Jakarta Sans/);assert.equal(navigationType[1].size,'14px');assert.equal(navigationType[1].weight,'600');assert.equal(navigationType[1].case,'uppercase');
        assert.match(navigationType[2].family,/Inter/);assert.equal(navigationType[2].size,'13.5px');assert.equal(navigationType[2].weight,'600');
        await page.click('#collapse');await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().width===80);
        assert.equal((await state(page)).preference,'true');
        await page.reload();await settle(page);await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().width===80);
        await page.setViewport({width:1100,height:900});await page.waitForFunction(()=>Alpine.$data(document.body).sidebarRange==='laptop');
        await page.click('#collapse');await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().width===224);
        assert.equal((await state(page)).preference,'true','La expansión laptop no cambia la preferencia desktop');
        await page.evaluate(()=>Alpine.$data(document.body).closeSidebarForNavigation());
        await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().width===80);
        await page.setViewport({width:900,height:900});await page.waitForFunction(()=>Alpine.$data(document.body).sidebarRange==='drawer');await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().right<=0);
        await page.click('#sidebar-mobile-trigger');await page.waitForFunction(()=>document.documentElement.classList.contains('rm-sidebar-scroll-locked'));
        assert.equal((await state(page)).width,300);
        await page.keyboard.press('Escape');await page.waitForFunction(()=>!document.documentElement.classList.contains('rm-sidebar-scroll-locked'));await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().right<=0);
        await page.click('#sidebar-mobile-trigger');await page.waitForFunction(()=>document.documentElement.classList.contains('rm-sidebar-scroll-locked'));
        await page.waitForFunction(()=>getComputedStyle(document.querySelector('.rm-sidebar-overlay')).display!=='none');await page.mouse.click(700,300);await page.waitForFunction(()=>!document.documentElement.classList.contains('rm-sidebar-scroll-locked'));
        await page.setViewport({width:390,height:844,isMobile:true,hasTouch:true});await settle(page);await page.click('#sidebar-mobile-trigger');await page.waitForFunction(()=>document.querySelector('.rm-sidebar').classList.contains('is-mobile-open'));
        assert.equal((await state(page)).width,320);await page.waitForFunction(()=>document.querySelector('aside').getBoundingClientRect().left===0);
        await page.click('#group');await page.waitForFunction(()=>Alpine.$data(document.querySelector('aside')).openSection===null);
        await page.waitForFunction(()=>getComputedStyle(document.querySelector('.rm-sidebar__submenu')).display==='none');await page.evaluate(()=>document.querySelector('nav').scrollTop=240);
        await page.waitForFunction(()=>JSON.parse(sessionStorage.getItem('sidebar-test-context'))?.scroll===240);
        await page.evaluate(()=>{ window.sidebarPageTransitions=0; if(document.startViewTransition){const start=document.startViewTransition.bind(document);document.startViewTransition=(options)=>{window.sidebarPageTransitions++;return start(options);};} });
        await page.click('nav > a:nth-of-type(6)');await page.waitForFunction(()=>location.pathname==='/second');await settle(page);
        await page.waitForFunction(()=>document.body.hasAttribute('data-rm-sidebar-ready'));
        assert.equal(await page.evaluate(()=>window.sidebarPageTransitions),1,'Solo el sidebar participa en una transición de navegación');
        const snapshots = await page.evaluate(()=>['old','new'].map(kind=>{const style=getComputedStyle(document.documentElement,'::view-transition-'+kind+'(rm-sidebar)');return {opacity:style.opacity,animation:style.animationName};}));
        assert.deepEqual(snapshots,[{opacity:'0',animation:'none'},{opacity:'1',animation:'none'}],'La página anterior no se superpone con el nuevo sidebar');
        const palette = await page.evaluate(()=>({bg:getComputedStyle(document.querySelector('aside')).backgroundColor,link:getComputedStyle(document.querySelector('nav > a')).color,button:getComputedStyle(document.querySelector('#group')).appearance}));
        assert.deepEqual(palette,{bg:'rgb(216, 210, 204)',link:'rgb(75, 68, 63)',button:'none'},'La navegación comparte el fondo tierra-ceniza de la topbar, sin colores nativos');
        assert.equal((await state(page)).locked,false);assert.equal((await state(page)).open,0,'La ruta nueva abre automáticamente su grupo activo');
        await page.evaluate(()=>Alpine.$data(document.body).sidebarOpen=true);
        await page.waitForFunction(()=>document.querySelector('nav').scrollTop===240);
        const touch = await page.evaluate(()=>{const el=document.querySelector('nav > a');return {height:el.getBoundingClientRect().height,transform:getComputedStyle(el).transform};});
        assert.ok(touch.height>=44);assert.equal(touch.transform,'none');
        for (const selector of ['class','attribute']) {
            await page.evaluate(kind=>{document.documentElement.classList.toggle('dark',kind==='class');if(kind==='attribute')document.documentElement.dataset.theme='dark';else delete document.documentElement.dataset.theme;},selector);
            await page.waitForFunction(()=>getComputedStyle(document.querySelector('nav > a')).color==='rgb(237, 245, 247)' && getComputedStyle(document.querySelector('.is-active')).color==='rgb(237, 245, 247)');
            const darkPalette=await page.evaluate(()=>({bg:getComputedStyle(document.querySelector('aside')).backgroundColor,text:getComputedStyle(document.querySelector('nav > a')).color,selected:getComputedStyle(document.querySelector('.is-active')).color}));
            await page.evaluate(()=>Promise.all(document.querySelector('#theme-input').getAnimations().map(animation=>animation.finished)));
            const globalSurfaces=await page.evaluate(()=>{const canvas=document.createElement('canvas');canvas.width=canvas.height=1;const context=canvas.getContext('2d');return ['body','#theme-card','#theme-input','#theme-table-head','#theme-modal','#theme-topbar'].map(selector=>{context.fillStyle='#07141C';context.fillRect(0,0,1,1);context.fillStyle=getComputedStyle(document.querySelector(selector)).backgroundColor;context.fillRect(0,0,1,1);return [...context.getImageData(0,0,1,1).data].slice(0,3);});});
            assert.ok(['16,33,43','25,46,57'].includes(globalSurfaces[2].join(',')),'El input normal o bajo hover conserva una superficie petróleo');
            assert.deepEqual(globalSurfaces.filter((_,index)=>index!==2),[[10,24,33],[16,33,43],[20,40,50],[32,43,54],[10,24,33]],'El fondo del shell comparte el petróleo del sidebar y la topbar');
            const contrasts=await page.evaluate(()=>{
                const canvas=document.createElement('canvas');canvas.width=canvas.height=1;const context=canvas.getContext('2d');
                const luminance=rgb=>rgb.map(value=>{value/=255;return value<=.04045?value/12.92:((value+.055)/1.055)**2.4;}).reduce((sum,value,index)=>sum+value*[.2126,.7152,.0722][index],0);
                return ['body','#theme-card','#theme-input','#theme-table-head','#theme-modal','#theme-topbar'].map(selector=>{
                    const style=getComputedStyle(document.querySelector(selector));context.fillStyle='#07141C';context.fillRect(0,0,1,1);context.fillStyle=style.backgroundColor;context.fillRect(0,0,1,1);const bg=luminance([...context.getImageData(0,0,1,1).data].slice(0,3));context.fillStyle=style.color;context.fillRect(0,0,1,1);const fg=luminance([...context.getImageData(0,0,1,1).data].slice(0,3));return {selector,ratio:(Math.max(bg,fg)+.05)/(Math.min(bg,fg)+.05)};
                });
            });
            for(const contrast of contrasts)assert.ok(contrast.ratio>=4.5,`${contrast.selector}: contraste mínimo 4.5:1 en modo oscuro`);
            const publicSurfaces=await page.evaluate(()=>['#theme-public-hero','#theme-public-services','#theme-public-footer'].map(selector=>getComputedStyle(document.querySelector(selector)).backgroundColor));
            assert.deepEqual(publicSurfaces,['rgb(10, 24, 33)','rgb(16, 33, 43)','rgb(7, 20, 28)'],'La landing también comparte el tema petróleo');
            assert.deepEqual(darkPalette,{bg:'rgb(10, 24, 33)',text:'rgb(237, 245, 247)',selected:'rgb(237, 245, 247)'},'El sidebar usa petróleo con ambos selectores del tema oscuro');
        }
        await page.evaluate(()=>{document.documentElement.classList.remove('dark');document.documentElement.dataset.theme='light';});
        assert.equal((await page.evaluate(()=>getComputedStyle(document.querySelector('aside')).backgroundColor)),palette.bg,'Volver a claro recupera el capuchino');
        await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
        const duration=await page.evaluate(()=>getComputedStyle(document.querySelector('aside')).transitionDuration);
        assert.match(duration,/1e-05s|0\.00001s/);
        assert.deepEqual(errors,[]);
    } finally { await browser.close();await new Promise(resolve=>server.close(resolve)); }
});
