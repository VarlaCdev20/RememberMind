<div>
     {{-- Selector de Periodo y Filtros --}}
    <x-ui.filter-bar class="mb-6">
        <div class="w-full flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex-1">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--rm-text-primary)]">
                    Análisis y Reportes
                </span>
                <h3 class="text-base font-extrabold text-[var(--rm-text-primary)] mt-0.5">
                    Ficha y Reportes de Evolución
                </h3>
                <p class="text-[11px] font-medium text-[var(--rm-text-secondary)]">
                    Seleccione el rango de fechas para actualizar en tiempo real los análisis gráficos y registros de evolución.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
                <div class="w-[140px]">
                    <label class="block text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] mb-1 tracking-wider">Desde</label>
                    <input type="date" wire:model.live="fecha_inicio" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                </div>
                <div class="w-[140px]">
                    <label class="block text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] mb-1 tracking-wider">Hasta</label>
                    <input type="date" wire:model.live="fecha_fin" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                </div>
            </div>
        </div>
    </x-ui.filter-bar>

 {{-- Alerta de fecha inconsistente --}}
 @if($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin)
 <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700 flex items-center gap-2 shadow-xs">
 <i class="ph-bold ph-warning-circle text-base"></i>
 <span>La fecha de inicio no puede ser posterior a la fecha de fin del periodo seleccionado.</span>
 </div>
 @endif

 {{-- Indicadores rápidos en el rango --}}
 <div class="grid gap-3 mb-6 sm:grid-cols-2 lg:grid-cols-4">
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Reportes Disponibles</p>
 <p class="mt-1 text-xl font-extrabold text-titulo">6</p>
 </div>
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Registros Signos Vitales</p>
 <p class="mt-1 text-xl font-extrabold text-parrafo">
 {{ count($chartSignos['fc'] ?? []) }}
 </p>
 </div>
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Evaluaciones Registradas</p>
 <p class="mt-1 text-xl font-extrabold text-parrafo">
 {{ count($chartCognitivo['puntajes'] ?? []) }}
 </p>
 </div>
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Anexo de Trazabilidad</p>
 <x-ui.status-badge estado="ACTIVO" />
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
 'color' => 'var(--rm-clinical)',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reporte-individual', $adultoMayor->cod_residente)
 ],
 [
 'icono' => 'ph-hand-pointing',
 'titulo' => 'Reporte de Atenciones',
 'desc' => 'Historial de atenciones institucionales registradas en el periodo.',
 'color' => 'var(--rm-accent-terracotta)',
 'bg' => 'bg-estado-peligroBg',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_residente, 'medico']) . $q
 ],
 [
 'icono' => 'ph-pill',
 'titulo' => 'Reporte de Medicación',
 'desc' => 'Tratamientos y bitácora de tomas registradas en el periodo.',
 'color' => 'var(--rm-warning)',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_residente, 'medicacion']) . $q
 ],
 [
 'icono' => 'ph-heartbeat',
 'titulo' => 'Reporte Signos Vitales',
 'desc' => 'Evolución registrada e historial de constantes vitales.',
 'color' => 'var(--rm-danger)',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_residente, 'signos']) . $q
 ],
 [
 'icono' => 'ph-person-arms-spread',
 'titulo' => 'Valoración Funcional',
 'desc' => 'Nivel de autonomía e indicadores funcionales institucionales.',
 'color' => 'var(--rm-action-primary)',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_residente, 'funcional']) . $q
 ],
 [
 'icono' => 'ph-brain',
 'titulo' => 'Reporte de Evaluaciones',
 'desc' => 'Puntajes de tamizaje cognitivo y resultados interpretativos.',
 'color' => 'var(--rm-violet)',
 'bg' => 'bg-fondo-panel',
 'url' => route('admin.adultos-mayores.reportes.especifico', [$adultoMayor->cod_residente, 'cognitivo']) . $q
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
 <div class="rm-chart-card rm-chart-glass flex flex-col justify-between">
 <div class="rm-chart-header"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i><div class="rm-chart-heading"><h4 class="rm-chart-title">Evolución de Signos Vitales</h4><p class="rm-chart-subtitle">Controles del período seleccionado</p></div></div>

 @if(empty($chartSignos['fc'] ?? []))
        <div class="py-6">
            <x-ui.empty-state
                compact
                icono="ph-heartbeat"
                titulo="Sin datos de constantes vitales"
                texto="No hay registros de signos vitales en el rango de fechas seleccionado."
            />
        </div>
    @else
 <div class="rm-chart-body is-md relative w-full" wire:ignore
 x-data="{
     chart: null,
     render(value) {
         if (!value?.labels?.length) return;
         const api = window.RMCharts;
         const config = api.presets.area(value.labels, [
             { label: 'Frecuencia Cardíaca', data: value.fc, borderColor: api.color('clinical'), backgroundColor: api.hexToRgba(api.color('clinical'), api.number('--rm-line-area-opacity', .12)) },
             { label: 'Saturación O2', data: value.sat, borderColor: api.color('care'), backgroundColor: api.hexToRgba(api.color('care'), api.number('--rm-line-area-secondary-opacity', .09)) },
             { label: 'Temperatura', data: value.temp, borderColor: api.color('reference'), borderDash: [6, 4], fill: false },
         ]);
         config.options.plugins.legend = { display: true, position: 'bottom', labels: api.baseOptions().plugins.legend.labels };
         config.options.scales.y.beginAtZero = false;
         config.options.scales.x.grid.display = false;
         this.chart = api.init('reporte-adulto-signos-{{ $adultoMayor->cod_residente }}', this.$refs.canvasSignos, config);
     },
     destroy() {
         const key = 'reporte-adulto-signos-{{ $adultoMayor->cod_residente }}';
         if (window.RMCharts?.get(key)?.canvas === this.$refs.canvasSignos) window.RMCharts.destroy(key);
     },
 }"
 x-init="
 render(@js($chartSignos));
 $watch('$wire.chartSignos', value => render(value));
 window.RMCharts.onThemeChange(() => render($wire.chartSignos), 'reporte-adulto-signos-{{ $adultoMayor->cod_residente }}', $el);"
 >
 <canvas x-ref="canvasSignos"></canvas>
 </div>
 @endif
 </div>

 {{-- Gráfico Evaluaciones --}}
 <div class="rm-chart-card rm-chart-glass flex flex-col justify-between">
 <div class="rm-chart-header"><i class="ph-bold ph-brain" aria-hidden="true"></i><div class="rm-chart-heading"><h4 class="rm-chart-title">Evolución de Evaluaciones Cognitivas</h4><p class="rm-chart-subtitle">Valoraciones del período seleccionado</p></div></div>

 @if(empty($chartCognitivo['puntajes'] ?? []))
        <div class="py-6">
            <x-ui.empty-state
                compact
                icono="ph-brain"
                titulo="Sin evaluaciones cognitivas"
                texto="No hay valoraciones de tamizaje cognitivo en el rango seleccionado."
            />
        </div>
    @else
 <div class="rm-chart-body is-md relative w-full" wire:ignore
 x-data="{
     chart: null,
     render(value) {
         if (!value?.labels?.length) return;
         const api = window.RMCharts;
         const config = api.presets.semantic('bar', 'cognitive', value.labels, value.puntajes);
         config.data.datasets[0].label = 'Puntaje Obtenido';
         config.options.plugins.legend = { display: false };
         config.options.scales.y.ticks.precision = 0;
         config.options.scales.y.beginAtZero = true;
         config.options.scales.x.grid.display = false;
         this.chart = api.init('reporte-adulto-cognitivo-{{ $adultoMayor->cod_residente }}', this.$refs.canvasCognitivo, config);
     },
     destroy() {
         const key = 'reporte-adulto-cognitivo-{{ $adultoMayor->cod_residente }}';
         if (window.RMCharts?.get(key)?.canvas === this.$refs.canvasCognitivo) window.RMCharts.destroy(key);
     },
 }"
 x-init="
 render(@js($chartCognitivo));
 $watch('$wire.chartCognitivo', value => render(value));
 window.RMCharts.onThemeChange(() => render($wire.chartCognitivo), 'reporte-adulto-cognitivo-{{ $adultoMayor->cod_residente }}', $el);"
 >
 <canvas x-ref="canvasCognitivo"></canvas>
 </div>
 @endif
 </div>
 </div>
</div>
