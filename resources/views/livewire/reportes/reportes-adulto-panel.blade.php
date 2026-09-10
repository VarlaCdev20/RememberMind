<div>
 {{-- Selector de Periodo y Filtros --}}
 <div class="mb-6 rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-[0_12px_28px_rgba(47,62,92,0.05)] backdrop-blur-xl flex flex-col md:flex-row gap-4 items-end justify-between">
 <div class="flex-1">
 <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-parrafo">
 Análisis y Reportes
 </span>
 <h3 class="text-base font-extrabold text-titulo mt-1 mb-1">
 Ficha y Reportes de Evolución
 </h3>
 <p class="text-[11px] font-bold text-apoyo leading-relaxed">
 Seleccione el rango de fechas para actualizar en tiempo real los análisis gráficos y registros de evolución.
 </p>
 </div>
 <div class="flex flex-wrap items-center gap-3 w-full md:w-auto shrink-0">
 <div class="w-[140px]">
 <label class="block text-[9px] font-bold uppercase text-apoyo mb-1 tracking-wider">Desde</label>
 <input type="date" wire:model.live="fecha_inicio" class="w-full rounded-xl border border-borde-suave bg-fondo-card/45 px-3 py-1.5 text-xs font-bold text-titulo outline-none hover:bg-fondo-card transition">
 </div>
 <div class="w-[140px]">
 <label class="block text-[9px] font-bold uppercase text-apoyo mb-1 tracking-wider">Hasta</label>
 <input type="date" wire:model.live="fecha_fin" class="w-full rounded-xl border border-borde-suave bg-fondo-card/45 px-3 py-1.5 text-xs font-bold text-titulo outline-none hover:bg-fondo-card transition">
 </div>
 </div>
 </div>

 {{-- Alerta de fecha inconsistente --}}
 @if($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin)
 <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700 flex items-center gap-2 shadow-xs">
 <i class="ph-bold ph-warning-circle text-base"></i>
 <span>La fecha de inicio no puede ser posterior a la fecha de fin del periodo seleccionado.</span>
 </div>
 @endif

 {{-- Indicadores rápidos en el rango --}}
 <div class="grid gap-3 mb-6 sm:grid-cols-2 lg:grid-cols-4">
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Reportes Disponibles</p>
 <p class="mt-1 text-xl font-extrabold text-titulo">6</p>
 </div>
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Registros Signos Vitales</p>
 <p class="mt-1 text-xl font-extrabold text-parrafo">
 {{ count($chartSignos['fc'] ?? []) }}
 </p>
 </div>
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Evaluaciones Registradas</p>
 <p class="mt-1 text-xl font-extrabold text-parrafo">
 {{ count($chartCognitivo['puntajes'] ?? []) }}
 </p>
 </div>
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Anexo de Trazabilidad</p>
 <span class="mt-1.5 inline-flex items-center rounded-md bg-fondo-panel border border-borde px-2 py-0.5 text-[10px] font-bold text-parrafo">
 ACTIVO
 </span>
 </div>
 </div>

 {{-- Cards de Reportes Individuales --}}
 <h3 class="text-xs font-bold uppercase tracking-widest text-apoyo mb-4 border-b border-borde-suave pb-2 flex items-center gap-2">
 <i class="ph-bold ph-file-text"></i> Catálogo de Reportes Individuales
 </h3>

 <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 mb-8">
 @php
 $q ="?start_date={$fecha_inicio}&end_date={$fecha_fin}";
 $reportes = [
 [
 'icono' => 'ph-files',
 'titulo' => 'Ficha Integral del Adulto Mayor',
 'desc' => 'Consolidado general administrativo, red de apoyo y evolución.',
 'color' => '#2F3E5C',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reporte-individual', $adultoMayor->cod_am)
 ],
 [
 'icono' => 'ph-hand-pointing',
 'titulo' => 'Reporte de Atenciones',
 'desc' => 'Historial de atenciones institucionales registradas en el periodo.',
 'color' => '#E27D60',
 'bg' => 'bg-estado-peligroBg',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'medico']) . $q
 ],
 [
 'icono' => 'ph-pill',
 'titulo' => 'Reporte de Medicación',
 'desc' => 'Tratamientos y bitácora de tomas registradas en el periodo.',
 'color' => '#D9A27C',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'medicacion']) . $q
 ],
 [
 'icono' => 'ph-heartbeat',
 'titulo' => 'Reporte Signos Vitales',
 'desc' => 'Evolución registrada e historial de constantes vitales.',
 'color' => '#C45F4B',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'signos']) . $q
 ],
 [
 'icono' => 'ph-person-arms-spread',
 'titulo' => 'Valoración Funcional',
 'desc' => 'Nivel de autonomía e indicadores funcionales institucionales.',
 'color' => '#8EA17D',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'funcional']) . $q
 ],
 [
 'icono' => 'ph-brain',
 'titulo' => 'Reporte de Evaluaciones',
 'desc' => 'Puntajes de tamizaje cognitivo y resultados interpretativos.',
 'color' => '#5B5F97',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_am, 'cognitivo']) . $q
 ],
 ];
 @endphp

 @foreach($reportes as $rep)
 <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between">
 <div class="flex items-start gap-3">
 <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $rep['bg'] }}" style="color: {{ $rep['color'] }}">
 <i class="ph-bold {{ $rep['icono'] }} text-xl"></i>
 </div>
 <div>
 <h4 class="text-xs font-bold text-titulo leading-snug">{{ $rep['titulo'] }}</h4>
 <p class="text-[10px] font-bold text-apoyo mt-1 mb-3 leading-relaxed">{{ $rep['desc'] }}</p>
 </div>
 </div>
 <div class="flex gap-2 border-t border-borde-suave pt-3">
 <a href="{{ $rep['url'] }}" target="_blank" class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg bg-fondo-card/55 px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-wider text-titulo transition hover:bg-fondo-card border border-borde-suave active:scale-95">
 <i class="ph-bold ph-eye"></i> Ver
 </a>
 <a href="{{ $rep['url'] }}&format=pdf" class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg bg-boton-principal px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-file-pdf"></i> PDF
 </a>
 </div>
 </div>
 @endforeach
 </div>

 {{-- Gráficos de Evolución --}}
 <h3 class="text-xs font-bold uppercase tracking-widest text-apoyo mb-4 border-b border-borde-suave pb-2 flex items-center gap-2">
 <i class="ph-bold ph-trend-up"></i> Gráficos de Evolución Institucional
 </h3>

 <div class="grid gap-6 lg:grid-cols-2">
 {{-- Gráfico Signos Vitales --}}
 <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-xs flex flex-col justify-between">
 <h4 class="text-xs font-bold text-titulo mb-3 flex items-center gap-1.5">
 <span class="h-2 w-2 rounded-full bg-fondo-panel"></span>
 Evolución de Signos Vitales
 </h4>
 
 @if(empty($chartSignos['fc'] ?? []))
 <div class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-borde-suave rounded-xl bg-fondo-panel min-h-[260px]">
 <i class="ph-bold ph-heartbeat text-3xl text-apoyo mb-2"></i>
 <p class="text-xs font-bold text-apoyo">Sin datos suficientes para generar esta gráfica.</p>
 <p class="text-[10px] font-bold text-apoyo mt-0.5">Registre constantes vitales en el rango de fechas seleccionado.</p>
 </div>
 @else
 <div class="relative h-64 w-full"
 x-data="{ chart: null }"
 x-init="
 chart = new Chart($refs.canvasSignos, {
 type: 'line',
 data: {
 labels: @js($chartSignos['labels']),
 datasets: [
 { label: 'Frecuencia Cardíaca', data: @js($chartSignos['fc']), borderColor: '#D96F58', backgroundColor: 'rgba(217,111,88,0.08)', tension: 0.3, fill: true },
 { label: 'Saturación O2', data: @js($chartSignos['sat']), borderColor: '#5B5F97', backgroundColor: 'transparent', tension: 0.3 },
 { label: 'Temperatura', data: @js($chartSignos['temp']), borderColor: '#8EA17D', backgroundColor: 'transparent', tension: 0.3 }
 ]
 },
 options: { 
 responsive: true, 
 maintainAspectRatio: false,
 plugins: { 
 legend: { 
 position: 'bottom', 
 labels: { font: { size: 9, family: 'Outfit', weight: 'bold' }, color: '#2F3E5C' } 
 } 
 } 
 }
 });
 $watch('$wire.chartSignos', value => {
 if (value && value.labels && value.labels.length > 0) {
 chart.data.labels = value.labels;
 chart.data.datasets[0].data = value.fc;
 chart.data.datasets[1].data = value.sat;
 chart.data.datasets[2].data = value.temp;
 chart.update();
 }
 });"
 >
 <canvas x-ref="canvasSignos"></canvas>
 </div>
 @endif
 </div>

 {{-- Gráfico Evaluaciones --}}
 <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-xs flex flex-col justify-between">
 <h4 class="text-xs font-bold text-titulo mb-3 flex items-center gap-1.5">
 <span class="h-2 w-2 rounded-full bg-fondo-panel"></span>
 Evolución de Evaluaciones Cognitivas
 </h4>

 @if(empty($chartCognitivo['puntajes'] ?? []))
 <div class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-borde-suave rounded-xl bg-fondo-panel min-h-[260px]">
 <i class="ph-bold ph-brain text-3xl text-apoyo mb-2"></i>
 <p class="text-xs font-bold text-apoyo">Sin datos suficientes para generar esta gráfica.</p>
 <p class="text-[10px] font-bold text-apoyo mt-0.5">Registre valoraciones de tamizaje cognitivo en el rango seleccionado.</p>
 </div>
 @else
 <div class="relative h-64 w-full"
 x-data="{ chart: null }"
 x-init="
 chart = new Chart($refs.canvasCognitivo, {
 type: 'bar',
 data: {
 labels: @js($chartCognitivo['labels']),
 datasets: [
 { label: 'Puntaje Obtenido', data: @js($chartCognitivo['puntajes']), backgroundColor: '#5B5F97', borderRadius: 6 }
 ]
 },
 options: { 
 responsive: true, 
 maintainAspectRatio: false,
 plugins: { 
 legend: { 
 position: 'bottom', 
 labels: { font: { size: 9, family: 'Outfit', weight: 'bold' }, color: '#2F3E5C' } 
 } 
 },
 scales: { 
 y: { beginAtZero: true, ticks: { color: '#2F3E5C', font: { size: 9 } } },
 x: { ticks: { color: '#2F3E5C', font: { size: 9 } } }
 } 
 }
 });
 $watch('$wire.chartCognitivo', value => {
 if (value && value.labels && value.labels.length > 0) {
 chart.data.labels = value.labels;
 chart.data.datasets[0].data = value.puntajes;
 chart.update();
 }
 });"
 >
 <canvas x-ref="canvasCognitivo"></canvas>
 </div>
 @endif
 </div>
 </div>
</div>
