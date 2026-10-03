
 window.rmSignosVitalesMainChart = function(root, payload) {
 if (!root || typeof Chart === 'undefined' || !window.RMCharts?.presets || !payload.main) return;
 const canvas = root.querySelector('[data-chart="main"]');
 if (!canvas) return;
 const api = window.RMCharts;
 const datasets = (payload.main.datasets || []).map((dataset, index) => {
     const color = api.color(dataset.tone || 'clinical');
     return {
         label: dataset.label,
         data: dataset.data || [],
         borderColor: color,
         backgroundColor: api.hexToRgba(color, api.number('--rm-line-area-opacity', .12)),
         fill: !!dataset.fill,
         borderDash: index > 0 ? [6, 4] : [],
         spanGaps: true,
     };
 });
 const options = api.baseOptions();
 options.plugins.legend = { display: true, position: 'bottom', labels: { color: api.getCss('--rm-chart-label'), usePointStyle: true } };
 options.scales.x.grid.display = false;
 options.scales.y.title = { display: true, text: payload.main.unit || '', color: api.getCss('--rm-chart-axis') };
 const componentId = root.closest('[wire\\:id]')?.getAttribute('wire:id') || 'salud-signos';
 root.__rmChart = api.init(`salud-signos-${componentId}`, canvas,
     api.presets.line(payload.main.labels || [], datasets, options));
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
