import { test } from 'node:test';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer';

const root = fileURLToPath(new URL('../../', import.meta.url));
const read = path => readFileSync(new URL(path, new URL('../../', import.meta.url)), 'utf8');
const manifest = JSON.parse(read('public/build/manifest.json'));
const css = read(`public/build/${manifest['resources/frontend/styles/app.css'].file}`);
const livewire = read('vendor/livewire/livewire/dist/livewire.js');
const executablePath = [process.env.PUPPETEER_EXECUTABLE_PATH, await puppeteer.executablePath(), 'C:/Program Files/Google/Chrome/Application/chrome.exe'].find(path => path && existsSync(path));

const php = String.raw`require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo view("apoyodecision::livewire.medico.resultados-experto.grafico-areas", [
    "graficoId" => "radial-prueba",
    "graficoTitulo" => "Estado de las cinco áreas cognitivas",
    "graficoSubtitulo" => "Estados originales disponibles",
    "modoGrafico" => "radial",
    "areasGrafico" => [
        ["nombre" => "Memoria", "estado" => "Información insuficiente", "grupo" => "insuficiente"],
        ["nombre" => "Atención", "estado" => "Rendimiento esperado sustentado", "grupo" => "esperado"],
        ["nombre" => "Funciones ejecutivas", "estado" => "No interpretable", "grupo" => "no_interpretable"],
        ["nombre" => "Lenguaje", "estado" => "Información contradictoria", "grupo" => "contradictorio"],
        ["nombre" => "Función visuoespacial", "estado" => "Alteración sustentada", "grupo" => "alterado"],
    ],
])->render();`;

test('mapa radial permite explorar estados sin asignar una escala clínica', { skip: !executablePath }, async () => {
    const markup = execFileSync('php', ['-r', php], { cwd: root, encoding: 'utf8' });
    const browser = await puppeteer.launch({ headless: true, executablePath });
    try {
        const page = await browser.newPage();
        for (const width of [390, 768, 1280]) {
            await page.setViewport({ width, height: 900 });
            await page.setContent(`<html><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>[x-cloak]{display:none!important}${css}</style></head><body><main style="max-width:1100px;margin:auto;padding:16px">${markup}</main></body></html>`);
            await page.addScriptTag({ content: livewire });
            await page.evaluate(() => window.Alpine.start());
            await page.waitForFunction(() => document.querySelector('.rm-cognitive-status-chart__choice')?.getAttribute('aria-pressed') === 'true');

            const choices = await page.$$('.rm-cognitive-status-chart__choice');
            assert.equal(choices.length, 5);
            await choices[3].click();
            await page.waitForFunction(() => document.querySelectorAll('.rm-cognitive-status-chart__choice')[3]?.getAttribute('aria-pressed') === 'true');
            const selectedArea = () => page.$$eval('#radial-prueba-detalle > div', elements => elements.find(element => getComputedStyle(element).display !== 'none')?.querySelector('strong')?.textContent.trim());
            await page.waitForFunction(() => [...document.querySelectorAll('#radial-prueba-detalle > div')].some(element => getComputedStyle(element).display !== 'none' && element.querySelector('strong')?.textContent.trim() === 'Lenguaje'));
            assert.equal(await selectedArea(), 'Lenguaje');
            assert.equal(await page.$eval('.rm-cognitive-status-chart__point:nth-of-type(4)', element => element.getAttribute('aria-pressed')), 'true');

            await page.evaluate(() => document.querySelector('.rm-cognitive-status-chart__point:nth-of-type(2)').focus());
            await page.keyboard.press('Enter');
            await page.waitForFunction(() => document.querySelectorAll('.rm-cognitive-status-chart__point')[1]?.getAttribute('aria-pressed') === 'true');
            await page.waitForFunction(() => [...document.querySelectorAll('#radial-prueba-detalle > div')].some(element => getComputedStyle(element).display !== 'none' && element.querySelector('strong')?.textContent.trim() === 'Atención'));
            assert.equal(await selectedArea(), 'Atención');
            assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
        }
    } finally {
        await browser.close();
    }
});
