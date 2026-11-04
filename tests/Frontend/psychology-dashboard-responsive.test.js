import { test } from 'node:test';
import assert from 'node:assert/strict';
import { existsSync, readFileSync } from 'node:fs';
import puppeteer from 'puppeteer';

const root = new URL('../../', import.meta.url);
const read = path => readFileSync(new URL(path, root), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = read(`public/build/${manifest['resources/frontend/styles/app.css'].file}`);
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe']
    .find(path => path && existsSync(path));

const html = (width, theme) => `<!doctype html><html data-theme="${theme}" class="${theme === 'dark' ? 'dark' : ''}"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>${css}</style><style>body{margin:0}.test-shell{box-sizing:border-box;width:100%;padding:16px 20px 16px ${width >= 1024 ? 260 : width >= 768 ? 100 : 20}px}</style></head><body class="rm-shell"><main class="rm-depth-canvas test-shell"><div class="rm-dashboard-composition rm-psychologist-dashboard">
<header class="rm-dashboard-header"><div class="rm-dashboard-header__content"><h1 class="rm-dashboard-header__title">Buen día, profesional de Psicología</h1><p>Consulta tus registros, próximas atenciones y alertas a tu cargo.</p></div><div class="rm-dashboard-header__visual"><img alt="" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="></div></header>
<section><div class="rm-dashboard-overview rm-psychology-dashboard__metrics">${['Aplicaciones registradas', 'Atenciones de hoy', 'Alertas a tu cargo'].map((label, i) => `<div class="rm-metric-card"><strong class="rm-metric-card__value">${i === 0 ? 96 : 0}</strong><span class="rm-metric-card__label">${label}</span><p class="rm-metric-card__description">${i === 0 ? 'Completas o vigentes · 30 días' : 'Registradas a tu nombre'}</p></div>`).join('')}</div></section>
<section><div class="rm-psychology-dashboard__charts"><article class="rm-chart-card rm-psychology-chart"><h2>Seguimiento mensual</h2><div class="rm-psychology-chart__plot"><canvas></canvas></div><details class="rm-psychology-chart__data"><summary>Ver cifras por mes</summary><table><tbody><tr><th>Enero</th><td>3</td><td>4</td></tr></tbody></table></details></article><article class="rm-chart-card rm-psychology-chart"><h2>Instrumentos utilizados</h2><div class="rm-psychology-chart__composition"><div class="rm-psychology-chart__plot rm-psychology-chart__plot--donut"><canvas></canvas></div><div class="rm-psychology-chart__breakdown"><ul><li><span class="rm-psychology-chart__swatch"></span><span>Instrumento de evaluación con nombre extenso</span><strong>12</strong></li></ul></div></div></article></div></section>
<section><div class="rm-psychology-dashboard__followup"><div class="rm-psychology-dashboard__pending"><section class="rm-dashboard-data-panel"><h2>Próximas atenciones</h2><p>Sin atenciones futuras registradas.</p></section></div><section class="rm-dashboard-data-panel rm-psychology-recent"><h2>Aplicaciones recientes</h2><ul class="rm-psychology-recent__list">${Array.from({ length: 5 }, (_, i) => `<li><div><strong>Residente de ejemplo</strong><span>Instrumento registrado ${i + 1}</span></div><time>17/10/2026</time></li>`).join('')}</ul></section><section class="rm-psychology-areas"><h2>Áreas de valoración</h2><div class="rm-psychology-areas__links rm-psychology-areas__links--four">${['Cognitiva', 'Afectiva y emocional', 'Funcionamiento', 'Entorno y red social'].map(label => `<a href="#"><span>${label}</span></a>`).join('')}</div></section></div></section>
</div></main></body></html>`;

test('dashboard de Psicología se adapta al espacio disponible', async t => {
    const browser = await puppeteer.launch({ headless: true, executablePath, args: ['--no-sandbox'] });
    t.after(() => browser.close());

    for (const theme of ['light', 'dark']) {
        for (const width of [1912, 1440, 1280, 1024, 942, 768, 390]) {
            const page = await browser.newPage();
            await page.setViewport({ width, height: 900 });
            await page.setContent(html(width, theme));
            await page.evaluate(() => {
                const instrumentList = document.querySelector('.rm-psychology-chart__breakdown ul');
                for (let index = 0; index < 4; index++) {
                    const row = instrumentList.firstElementChild.cloneNode(true);
                    row.querySelectorAll('span')[1].textContent = `Instrumento de evaluación ${index + 2} con nombre extenso`;
                    instrumentList.append(row);
                }
                document.querySelectorAll('.rm-psychology-chart')[1].insertAdjacentHTML('beforeend', '<details class="rm-psychology-chart__data"><summary>Ver cantidades por instrumento</summary></details>');
            });
            const layout = await page.evaluate(() => {
                const boxes = selector => [...document.querySelectorAll(selector)].map(el => {
                    const { x, y, width, height } = el.getBoundingClientRect();
                    return { x, y, width, height };
                });
                return {
                    overflow: document.documentElement.scrollWidth - innerWidth,
                    sections: boxes('.rm-psychologist-dashboard > *'),
                    charts: boxes('.rm-psychology-chart'),
                    clippedCharts: [...document.querySelectorAll('.rm-psychology-chart')].some(card => card.lastElementChild.getBoundingClientRect().bottom > card.getBoundingClientRect().bottom - 2),
                    monthlyPlot: boxes('.rm-psychology-chart__plot')[0],
                    monthlySummary: boxes('.rm-psychology-chart__data summary')[0],
                    metrics: boxes('.rm-metric-card'),
                    areas: boxes('.rm-psychology-areas__links a'),
                    pending: boxes('.rm-psychology-dashboard__pending')[0],
                    recent: boxes('.rm-psychology-recent')[0],
                    areaPanel: boxes('.rm-psychology-areas')[0],
                    imageVisible: getComputedStyle(document.querySelector('.rm-dashboard-header__visual')).display !== 'none',
                };
            });
            assert.ok(layout.overflow <= 1, `${theme} ${width}px: desbordamiento de ${layout.overflow}px`);
            assert.equal(layout.clippedCharts, false, `${theme} ${width}px: ninguna tarjeta debe recortar instrumentos ni el acceso al desglose`);
            assert.ok(layout.sections.every((box, index) => index === 0 || box.y >= layout.sections[index - 1].y + layout.sections[index - 1].height), `${theme} ${width}px: las secciones deben ocupar filas completas como en Enfermería`);
            assert.ok(layout.sections.every(box => Math.abs(box.x - layout.sections[0].x) <= 1 && Math.abs(box.width - layout.sections[0].width) <= 1), `${theme} ${width}px: cabecera y secciones deben compartir ancho`);
            assert.ok(layout.monthlySummary.y - (layout.monthlyPlot.y + layout.monthlyPlot.height) <= 20, `${theme} ${width}px: no debe quedar un hueco bajo la gráfica mensual`);
            assert.ok(layout.monthlyPlot.height <= 160, `${theme} ${width}px: la tendencia debe mantenerse compacta`);
            if (width >= 1024 || width === 942) {
                assert.ok(Math.abs(layout.charts[0].y - layout.charts[1].y) <= 1, `${theme} ${width}px: las gráficas deben compartir fila`);
                assert.ok(Math.abs(layout.charts[0].width - layout.charts[1].width) <= 1, `${theme} ${width}px: las gráficas deben repartirse el ancho`);
            }
            if (width >= 1280 || width === 942) {
                assert.ok(layout.areaPanel.y >= layout.pending.y + layout.pending.height, `${theme} ${width}px: las áreas deben quedar bajo pendientes`);
                assert.ok(layout.areaPanel.x < layout.recent.x && layout.areaPanel.y < layout.recent.y + layout.recent.height, `${theme} ${width}px: las áreas deben ocupar el hueco junto a recientes`);
            }
            if (width >= 1024 || width === 942) {
                assert.ok(layout.metrics.every(box => Math.abs(box.y - layout.metrics[0].y) <= 1), `${theme} ${width}px: los tres indicadores deben compartir una fila`);
                assert.ok(layout.metrics.every(box => Math.abs(box.width - layout.metrics[0].width) <= 1), `${theme} ${width}px: los indicadores deben tener el mismo ancho`);
            }
            if (width <= 768) {
                assert.ok(layout.charts[1].y > layout.charts[0].y, `${theme} ${width}px: las gráficas deben apilarse`);
            }
            if (width <= 1024 && width !== 942) {
                assert.equal(layout.imageVisible, false, `${theme} ${width}px: la imagen del hero debe ceder espacio al texto`);
            }
            if (width === 942) assert.equal(layout.imageVisible, true, `${theme}: la cabecera conserva su imagen cuando cabe`);
            if (width === 390) {
                assert.ok(layout.metrics[2].y > layout.metrics[1].y, `${theme}: indicadores en una columna`);
                assert.ok(layout.areas[1].y > layout.areas[0].y, `${theme}: accesos en una columna`);
                assert.ok(layout.areas.every(box => box.height >= 44), `${theme}: accesos táctiles suficientes`);
            }
            await page.close();
        }
    }
});
