<x-sistema-layout>

 {{-- Zona 1: Encabezado personalizado --}}
 <x-ui.encabezado-dashboard :saludo="$saludo ?? []" />

 {{-- Zona 2: KPIs institucionales --}}
 <x-ui.kpis-dashboard :kpis="$kpisInstitucionales ?? []" />

 {{-- Zona 3: Resumen de salud + Equipo institucional --}}
 <div class="grid gap-4 xl:grid-cols-2">
 <x-ui.panel-salud-dashboard :resumen="$resumenSalud ?? []" />
 <x-ui.panel-equipo-institucional :equipo="$equipoInstitucional ?? []" :redFamiliar="$redFamiliar ?? []" />
 </div>

 {{-- Zona 4: Gráficas --}}
 <x-ui.seccion-graficos-dashboard />

 {{-- Zona 5: Alertas + Actividades --}}
 <div class="grid gap-4 xl:grid-cols-[1fr_1.35fr]">
 <x-ui.panel-alertas-dashboard :alertas="$alertasEstructuradas ?? []" />
 <x-ui.panel-actividades-dashboard :actividades="$actividadesDashboard ?? []" />
 </div>

 {{-- Zona 6: Bitácora --}}
 <x-ui.tabla-bitacora-dashboard :registros="$bitacoraDashboard ?? []" />

 <script>
 window.dashboardData = {
 adultosPorEstado: @json($adultosPorEstado ?? ['labels' => [], 'data' => [], 'colores' => []]),
 distribucionEquipo: @json($distribucionEquipoInstitucional ?? ['labels' => [], 'data' => [], 'colores' => []]),
 };

 document.addEventListener('DOMContentLoaded', () => {

 // Gráfico 1: Adultos por estado (doughnut)
 (function () {
 const ctx = document.getElementById('graficoAdultosPorEstado');
 if (!ctx || typeof Chart === 'undefined') return;
 const d = window.dashboardData.adultosPorEstado;
 new Chart(ctx, {
 type: 'doughnut',
 data: {
 labels: d.labels ?? [],
 datasets: [{
 data: d.data ?? [],
 backgroundColor: d.colores ?? ['#2F3E5C', '#F4A261', '#C7B5A3'],
 borderWidth: 0,
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 cutout: '68%',
 plugins: {
 datalabels: { display: false },
 legend: {
 display: true,
 position: 'bottom',
 labels: {
 color: '#2F3E5C',
 boxWidth: 10,
 font: { size: 11, weight: 'bold' }
 }
 }
 }
 }
 });
 })();

 // Gráfico 2: Distribución equipo institucional (barra horizontal)
 (function () {
 const ctx = document.getElementById('graficoEquipoInstitucional');
 if (!ctx || typeof Chart === 'undefined') return;
 const d = window.dashboardData.distribucionEquipo;
 new Chart(ctx, {
 type: 'bar',
 data: {
 labels: d.labels ?? [],
 datasets: [{
 data: d.data ?? [],
 backgroundColor: d.colores ?? ['#9B8AC7', '#E97A5F', '#8DA280'],
 borderWidth: 0,
 borderRadius: 8,
 }]
 },
 options: {
 indexAxis: 'y',
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 datalabels: { display: false },
 legend: { display: false }
 },
 scales: {
 x: {
 grid: { color: 'rgba(47,62,92,0.08)' },
 ticks: { color: '#2F3E5C', font: { size: 11, weight: 'bold' } }
 },
 y: {
 grid: { display: false },
 ticks: { color: '#2F3E5C', font: { size: 11, weight: 'bold' } }
 }
 }
 }
 });
 })();

 });
 </script>

</x-sistema-layout>
