
(function () {
 'use strict';

 // Datos preparados en PHP
 const signosLabels = @json($svLabels);
 const sistolica = @json($svSistolica);
 const diastolica = @json($svDiastolica);
 const pulso = @json($svPulso);
 const saturacion = @json($svSaturacion);

 const medLabels = @json($medLabels);
 const medCounts = @json($medCounts);
 const medColors = @json($medColors);

 const barthelLabels = @json($barthelLabels);
 const barthelValues = @json($barthelValues);

 const evalLabels = @json($evalLabels);
 const evalPuntajes = @json($evalPuntajes);
 const evalMaximos = @json($evalMaximos);

 const suffix = '{{ $idAdulto }}';

 let initialized = false;

 function initCharts() {
 if (initialized || typeof window.Chart === 'undefined') return;
 initialized = true;

 const defaults = {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: { labels: { font: { family: 'inherit', size: 10, weight: 'bold' }, boxWidth: 12, padding: 12 } },
 datalabels: { display: false },
 },
 };

 // ── Gráfica 1: Signos Vitales (línea) ─────────────────────
 const canvasSignos = document.getElementById('chartSignos' + suffix);
 if (canvasSignos && signosLabels.length > 0) {
 new window.Chart(canvasSignos, {
 type: 'line',
 data: {
 labels: signosLabels,
 datasets: [
 {
 label: 'Sistólica',
 data: sistolica,
 borderColor: '#E27D60',
 backgroundColor: 'rgba(226,125,96,0.08)',
 tension: 0.4,
 pointRadius: 4,
 pointBackgroundColor: '#E27D60',
 fill: true,
 },
 {
 label: 'Diastólica',
 data: diastolica,
 borderColor: '#5B5F97',
 backgroundColor: 'rgba(91,95,151,0.06)',
 tension: 0.4,
 pointRadius: 4,
 pointBackgroundColor: '#5B5F97',
 },
 {
 label: 'Pulso (lpm)',
 data: pulso,
 borderColor: '#C45F4B',
 borderDash: [4, 3],
 tension: 0.3,
 pointRadius: 3,
 pointBackgroundColor: '#C45F4B',
 },
 ],
 },
 options: {
 ...defaults,
 scales: {
 x: { ticks: { font: { size: 9 } }, grid: { color: '#F0ECE8' } },
 y: { min: 40, ticks: { font: { size: 9 } }, grid: { color: '#F0ECE8' } },
 },
 },
 });
 }

 // ── Gráfica 2: Medicación (dona) ──────────────────────────
 const canvasMed = document.getElementById('chartMed' + suffix);
 if (canvasMed && medLabels.length > 0) {
 new window.Chart(canvasMed, {
 type: 'doughnut',
 data: {
 labels: medLabels,
 datasets: [{
 data: medCounts,
 backgroundColor: medColors,
 borderWidth: 2,
 borderColor: '#fff',
 hoverOffset: 6,
 }],
 },
 options: {
 ...defaults,
 cutout: '60%',
 plugins: {
 ...defaults.plugins,
 datalabels: {
 display: true,
 color: '#fff',
 font: { size: 10, weight: 'bold' },
 formatter: (v) => v > 0 ? v : '',
 },
 },
 },
 });
 }

 // ── Gráfica 3: Barthel (línea) ────────────────────────────
 const canvasBarthel = document.getElementById('chartBarthel' + suffix);
 if (canvasBarthel && barthelLabels.length > 0) {
 new window.Chart(canvasBarthel, {
 type: 'line',
 data: {
 labels: barthelLabels,
 datasets: [{
 label: 'Índice Barthel',
 data: barthelValues,
 borderColor: '#5B5F97',
 backgroundColor: 'rgba(91,95,151,0.1)',
 tension: 0.4,
 pointRadius: 5,
 pointBackgroundColor: '#5B5F97',
 fill: true,
 }],
 },
 options: {
 ...defaults,
 scales: {
 x: { ticks: { font: { size: 8 }, maxRotation: 35 }, grid: { color: '#F0ECE8' } },
 y: { min: 0, max: 100, ticks: { font: { size: 9 }, stepSize: 20 }, grid: { color: '#F0ECE8' } },
 },
 plugins: {
 ...defaults.plugins,
 annotation: undefined,
 datalabels: {
 display: true,
 color: '#5B5F97',
 font: { size: 9, weight: 'bold' },
 anchor: 'end',
 align: 'top',
 },
 },
 },
 });
 }

 // ── Gráfica 4: Evaluaciones Cognitivas (barras) ───────────
 const canvasEval = document.getElementById('chartEval' + suffix);
 if (canvasEval && evalLabels.length > 0) {
 new window.Chart(canvasEval, {
 type: 'bar',
 data: {
 labels: evalLabels,
 datasets: [
 {
 label: 'Puntaje Obtenido',
 data: evalPuntajes,
 backgroundColor: 'rgba(168,107,60,0.75)',
 borderColor: '#A86B3C',
 borderWidth: 1,
 borderRadius: 5,
 },
 {
 label: 'Puntaje Máximo',
 data: evalMaximos,
 backgroundColor: 'rgba(203,187,170,0.4)',
 borderColor: '#CBBBAA',
 borderWidth: 1,
 borderRadius: 5,
 },
 ],
 },
 options: {
 ...defaults,
 scales: {
 x: { ticks: { font: { size: 8 }, maxRotation: 30 }, grid: { display: false } },
 y: { min: 0, ticks: { font: { size: 9 } }, grid: { color: '#F0ECE8' } },
 },
 plugins: {
 ...defaults.plugins,
 datalabels: {
 display: true,
 color: '#2F3E5C',
 font: { size: 9, weight: 'bold' },
 anchor: 'end',
 align: 'top',
 formatter: (v) => v > 0 ? v : '',
 },
 },
 },
 });
 }
 }

 // Inicializar cuando la sección sea visible (IntersectionObserver)
 function attachObserver() {
 const section = document.getElementById('reportes-charts-section-{{ $idAdulto }}');
 if (!section) {
 requestAnimationFrame(attachObserver);
 return;
 }

 // Si ya está visible al cargar
 if (section.offsetParent !== null) {
 setTimeout(initCharts, 50);
 }

 // Cuando Alpine.js lo muestre
 const io = new IntersectionObserver(
 (entries) => {
 if (entries[0].isIntersecting) {
 setTimeout(initCharts, 50);
 io.disconnect();
 }
 },
 { threshold: 0.05 }
 );
 io.observe(section);

 // Fallback: escuchar click en el botón"Reportes" del sidebar
 document.querySelectorAll('[\\@click*="carpetaActiva = \'reportes\'"]').forEach(btn => {
 btn.addEventListener('click', () => setTimeout(initCharts, 100));
 });
 }

 if (document.readyState === 'loading') {
 document.addEventListener('DOMContentLoaded', attachObserver);
 } else {
 attachObserver();
 }
})();
