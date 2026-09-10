
 (() => {
 const payload = rmDatosff6fbf562949;
 const chartDefaults = {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: {
 position: 'bottom',
 labels: {
 boxWidth: 10,
 usePointStyle: true,
 font: { size: 11, weight: '700' },
 color: '#2F3E5C',
 },
 },
 datalabels: { display: false },
 },
 };

 const destroyPrevious = (canvas) => {
 if (canvas && canvas.__familiaChart) {
 canvas.__familiaChart.destroy();
 canvas.__familiaChart = null;
 }
 };

 const makeDoughnut = (id, data, colors) => {
 const canvas = document.getElementById(id);
 if (!canvas || !window.Chart || !data?.data?.some((value) => Number(value) > 0)) return;

 destroyPrevious(canvas);
 canvas.__familiaChart = new Chart(canvas, {
 type: 'doughnut',
 data: {
 labels: data.labels,
 datasets: [{
 data: data.data,
 backgroundColor: colors,
 borderColor: '#F3ECE4',
 borderWidth: 3,
 }],
 },
 options: {
 ...chartDefaults,
 cutout: '64%',
 },
 });
 };

 const makeBar = (id, data) => {
 const canvas = document.getElementById(id);
 if (!canvas || !window.Chart || !data?.data?.some((value) => Number(value) > 0)) return;

 destroyPrevious(canvas);
 canvas.__familiaChart = new Chart(canvas, {
 type: 'bar',
 data: {
 labels: data.labels,
 datasets: [{
 data: data.data,
 label: 'Visitas',
 backgroundColor: '#8DA280',
 borderColor: '#6F8A64',
 borderRadius: 8,
 maxBarThickness: 34,
 }],
 },
 options: {
 ...chartDefaults,
 scales: {
 y: {
 beginAtZero: true,
 ticks: { precision: 0, color: '#2F3E5C', font: { size: 11, weight: '700' } },
 grid: { color: 'rgba(47, 62, 92, 0.08)' },
 },
 x: {
 ticks: { color: '#2F3E5C', font: { size: 11, weight: '700' } },
 grid: { display: false },
 },
 },
 },
 });
 };

 const renderCharts = () => {
 makeDoughnut('familiaRedChart', payload.red, ['#8DA280', '#D9A05B']);
 makeBar('familiaVisitasChart', payload.visitas);
 makeDoughnut('familiaFichaChart', payload.ficha, ['#8DA280', '#D9A05B', '#E27D60']);
 };

 if (document.readyState === 'loading') {
 document.addEventListener('DOMContentLoaded', renderCharts, { once: true });
 } else {
 renderCharts();
 }
 })();
 