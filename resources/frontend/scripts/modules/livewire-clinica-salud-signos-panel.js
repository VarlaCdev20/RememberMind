
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

 window.addEventListener('signos-confirmar-alertas', async function(event) {
 const root = [...document.querySelectorAll('[wire\\:id]')].find(el => el.getAttribute('wire:id') === componentId);
 if (!root) return;
 const data = event.detail?.[0] || event.detail || {};
 if (!(await window.rmConfirmar({
 title: data.titulo || 'Valores fuera del rango referencial',
 text: data.texto || 'Verifique la información antes de guardar.',
 confirmText: 'Guardar verificado', cancelText: 'Revisar datos'
 }, document.activeElement))) return;
 if (!root.isConnected) return;
 const component = window.Livewire?.find(componentId);
 if (component) component.call('confirmarGuardarConAlertas');
 });

 window.addEventListener('signos-confirmar-anulacion', async function(event) {
 const root = [...document.querySelectorAll('[wire\\:id]')].find(el => el.getAttribute('wire:id') === componentId);
 if (!root) return;
 const data = event.detail?.[0] || event.detail || {};
 const motivo = await window.rmPedirTexto({
 title: 'Anular registro', text: 'Indique el motivo. El registro no será eliminado.',
 inputLabel: 'Motivo de anulación', inputPlaceholder: 'Escribe el motivo…',
 tone: 'danger', confirmText: 'Anular registro',
 inputValidator: value => !value || value.trim().length < 10 ? 'El motivo debe tener al menos 10 caracteres.' : undefined,
 }, document.activeElement);
 if (motivo === null) return;
 if (!root.isConnected) return;
 const component = window.Livewire?.find(componentId);
 if (component) component.call('anularConMotivo', data.id, motivo);
 });
 })();
