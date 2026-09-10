
 window.rmSignosVitalesMainChart = function(root, payload) {
 if (!root || typeof Chart === 'undefined') return;

 if (root.__rmChart && typeof root.__rmChart.destroy === 'function') {
 root.__rmChart.destroy();
 }

 const canvas = root.querySelector('[data-chart="main"]');
 if (!canvas || !payload.main) return;

 root.__rmChart = new Chart(canvas, {
 type: 'line',
 data: {
 labels: payload.main.labels || [],
 datasets: (payload.main.datasets || []).map((dataset) => ({
 label: dataset.label,
 data: dataset.data || [],
 borderColor: dataset.color,
 backgroundColor: dataset.fill ? dataset.color + '22' : 'transparent',
 tension: 0.32,
 borderWidth: 2.5,
 pointRadius: 3,
 fill: !!dataset.fill,
 spanGaps: true
 }))
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 interaction: { mode: 'index', intersect: false },
 plugins: {
 datalabels: { display: false },
 legend: {
 position: 'bottom',
 labels: { color: '#2F3E5C', boxWidth: 10, font: { size: 11, weight: 'bold' } }
 },
 tooltip: {
 backgroundColor: 'rgba(47, 62, 92, 0.92)',
 titleColor: '#F3ECE4',
 bodyColor: '#F3ECE4',
 padding: 12,
 cornerRadius: 12
 }
 },
 scales: {
 x: { grid: { display: false }, ticks: { color: '#2F3E5C', font: { size: 10, weight: 'bold' } } },
 y: {
 grid: { color: 'rgba(47, 62, 92, 0.08)' },
 ticks: { color: '#2F3E5C', font: { size: 10, weight: 'bold' } },
 title: { display: true, text: payload.main.unit || '', color: '#2F3E5C', font: { weight: 'bold' } }
 }
 }
 }
 });
 };

 (() => {
 const componentId = rmDatosf05cd845c922;
 window.rmSignosConfirmListeners = window.rmSignosConfirmListeners || {};
 if (window.rmSignosConfirmListeners[componentId]) return;
 window.rmSignosConfirmListeners[componentId] = true;

 window.addEventListener('signos-confirmar-alertas', function(event) {
 const data = event.detail?.[0] || event.detail || {};
 const confirmar = () => {
 const component = window.Livewire && window.Livewire.find(componentId);
 if (component) component.call('confirmarGuardarConAlertas');
 };

 if (window.SwalAmandita) {
 window.SwalAmandita.fire({
 title: data.titulo || 'Valores fuera del rango referencial',
 text: data.texto || 'Verifique la información antes de guardar.',
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Guardar verificado',
 cancelButtonText: 'Revisar datos'
 }).then(result => {
 if (result.isConfirmed) confirmar();
 });
 } else if (confirm(data.texto || 'Se detectaron valores fuera de rango. ¿Desea guardar?')) {
 confirmar();
 }
 });

 window.addEventListener('signos-confirmar-anulacion', function(event) {
 const data = event.detail?.[0] || event.detail || {};
 const enviar = (motivo) => {
 const component = window.Livewire && window.Livewire.find(componentId);
 if (component) component.call('anularConMotivo', data.id, motivo);
 };

 if (window.SwalAmandita) {
 window.SwalAmandita.fire({
 title: 'Anular registro',
 text: 'Indique el motivo. El registro no será eliminado.',
 input: 'textarea',
 inputPlaceholder: 'Motivo de anulación...',
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Anular registro',
 cancelButtonText: 'Cancelar',
 inputValidator: (value) => {
 if (!value || value.trim().length < 10) return 'El motivo debe tener al menos 10 caracteres.';
 }
 }).then(result => {
 if (result.isConfirmed) enviar(result.value);
 });
 } else {
 const motivo = prompt('Motivo de anulación');
 if (motivo) enviar(motivo);
 }
 });
 })();
