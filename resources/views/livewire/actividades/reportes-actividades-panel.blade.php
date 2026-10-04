<div class="min-h-screen bg-transparent px-4 py-5 text-titulo sm:px-6 lg:px-8">
 <div class="mx-auto max-w-7xl space-y-5">

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- CABECERA --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <x-ui.collection-header title="Reportes de actividades" subtitle="Evidencia institucional sobre actividades, participación y cumplimiento." icon="ph-chart-bar" eyebrow="Área de actividades">
 <x-slot:actions>

 {{-- Vista previa --}}
 <a
 href="{{ $urlPreview }}"
 class="inline-flex items-center gap-2 rounded-xl border border-borde-fuerte bg-fondo-panel px-3.5 py-2 text-xs font-bold text-apoyo transition hover:bg-fondo-panel hover:text-titulo"
 >
 <i class="ph-bold ph-eye text-sm"></i>
 Vista previa
 </a>

 {{-- PDF --}}
 @can('reportes.exportar_pdf')
 <a
 href="{{ $urlPdf }}"
 x-data
 @click.prevent="
 @if($stats['total'] === 0)
 window.SwalAmandita && window.SwalAmandita.fire({
 icon: 'warning',
 title: 'Sin datos para exportar',
 text: 'No hay actividades con los filtros seleccionados.',
 });
 @else
 window.location.href = '{{ $urlPdf }}';
 @endif"
 class="inline-flex items-center gap-2 rounded-xl border border-borde-focus bg-estado-peligroBg px-3.5 py-2 text-xs font-bold text-boton-acento transition hover:bg-estado-peligroBg"
 >
 <i class="ph-bold ph-file-pdf text-sm"></i>
 Exportar PDF
 </a>
 @endcan

 {{-- Excel --}}
 @can('reportes.exportar_pdf')
 <a
 href="{{ $urlExcel }}"
 x-data
 @click.prevent="
 @if($stats['total'] === 0)
 window.SwalAmandita && window.SwalAmandita.fire({
 icon: 'warning',
 title: 'Sin datos para exportar',
 text: 'No hay actividades con los filtros seleccionados.',
 });
 @else
 window.location.href = '{{ $urlExcel }}';
 @endif"
 class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3.5 py-2 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg"
 >
 <i class="ph-bold ph-file-xls text-sm"></i>
 Exportar Excel
 </a>
 @endcan

 {{-- Limpiar filtros --}}
 @if($hasFiltros)
 <button
 type="button"
 wire:click="limpiarFiltros"
 class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2 text-xs font-bold text-apoyo transition hover:bg-fondo-app"
 >
 <i class="ph-bold ph-x text-xs"></i>
 Limpiar filtros
 </button>
 @endif

 </x-slot:actions>
 </x-ui.collection-header>

     {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- FILTROS DE REPORTE FORMATO ALERTAS                                 --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscar adulto --}}
            <div class="lg:col-span-3">
                <label for="reporte-actividades-buscar" class="rm-collection-filter-label">Buscar residente</label>
                <div class="relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input id="reporte-actividades-buscar" type="text"
                    wire:model.live.debounce.400ms="buscar"
                    placeholder="Buscar por nombre o apellido..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if(!empty($buscar))
                    <button type="button"
                        wire:click="$set('buscar', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
                </div>
            </div>

            {{-- Tipo --}}
            <div class="lg:col-span-3">
                <label for="reporte-actividades-tipo" class="rm-collection-filter-label">Tipo de actividad</label>
                <select id="reporte-actividades-tipo" wire:model.live="filtroTipo"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Estado --}}
            <div class="lg:col-span-2">
                <label for="reporte-actividades-estado" class="rm-collection-filter-label">Estado</label>
                <select id="reporte-actividades-estado" wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los estados</option>
                    <option value="PROGRAMADA">Programada / Pendiente</option>
                    <option value="REALIZADA">Realizada / Cumplida</option>
                    <option value="CANCELADA">Cancelada / Anulada</option>
                    <option value="REPROGRAMADA">Reprogramada</option>
                </select>
            </div>

            {{-- Fecha desde --}}
            <div class="lg:col-span-2">
                <label for="reporte-actividades-desde" class="rm-collection-filter-label">Desde</label>
                <input id="reporte-actividades-desde" type="date"
                    wire:model.live="fechaDesde"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
            </div>

            {{-- Fecha hasta --}}
            <div class="lg:col-span-2">
                <label for="reporte-actividades-hasta" class="rm-collection-filter-label">Hasta</label>
                <input id="reporte-actividades-hasta" type="date"
                    wire:model.live="fechaHasta"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
            </div>
        </div>

        @if($hasFiltros)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($buscar))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($buscar, 16) }}"</span>
                            <button type="button" wire:click="$set('buscar', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroTipo))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Tipo seleccionado</span>
                            <button type="button" wire:click="$set('filtroTipo', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroEstado))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Estado: {{ $filtroEstado }}</span>
                            <button type="button" wire:click="$set('filtroEstado', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($fechaDesde))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Desde: {{ $fechaDesde }}</span>
                            <button type="button" wire:click="$set('fechaDesde', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($fechaHasta))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Hasta: {{ $fechaHasta }}</span>
                            <button type="button" wire:click="$set('fechaHasta', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="limpiarFiltros" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- Indicadores del período --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4" wire:loading.class="opacity-60">

 <x-ui.metric-card icon="ph-calendar-blank" :value="number_format($stats['total'])" label="Total actividades" description="Registradas en el sistema" variant="neutral" />
 <x-ui.metric-card icon="ph-clock" :value="number_format($stats['programadas'])" label="Programadas" description="Pendientes de realizarse" variant="sky" />
 <x-ui.metric-card icon="ph-check-circle" :value="number_format($stats['realizadas'])" label="Realizadas" description="Completadas o cumplidas" variant="mint" />
 <x-ui.metric-card icon="ph-x-circle" :value="number_format($stats['canceladas'])" label="Canceladas" description="No realizadas o anuladas" variant="coral" />
 <x-ui.metric-card icon="ph-arrows-clockwise" :value="number_format($stats['reprogramadas'])" label="Reprogramadas" description="Pendientes de nueva fecha" variant="neutral" />
 <x-ui.metric-card icon="ph-users" :value="number_format($stats['adultos'])" label="Residentes vinculados" description="Con actividades en el período" variant="mint" />
 <x-ui.metric-card icon="ph-tag" :value="number_format($stats['tipos'])" label="Tipos utilizados" description="Tipos de actividad distintos" variant="neutral" />
 <x-ui.metric-card icon="ph-calendar-dots" :value="$fechaDesde || $fechaHasta ? 'Filtrado' : 'Completo'" label="Período" :description="$stats['periodo']" variant="neutral" />

 </div>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- GRÁFICOS CSS — DISTRIBUCIÓN POR ESTADO + POR MES --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 @if($chartEstado['data'] || $chartMes['data'])
 <div class="grid gap-5 lg:grid-cols-2">

 {{-- Distribución por estado --}}
 @if(!empty($chartEstado['data']))
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-chart-donut text-apoyo text-sm"></i>
 <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">Distribución por estado</h3>
 </div>
 </div>
 <div class="space-y-3 p-5">
 @foreach($chartEstado['data'] as $item)
 @php
 $pct = $chartEstado['maximo'] > 0
 ? round($item['total'] / $chartEstado['maximo'] * 100)
 : 0;
 $barColor = match ($item['estado']) {
 'REALIZADA', 'COMPLETADA', 'FINALIZADA' => 'var(--rm-chart-care-500)',
 'CANCELADA', 'ANULADA' => 'var(--rm-chart-alert-500)',
 'REPROGRAMADA' => 'var(--rm-chart-neutral-500)',
 default => 'var(--rm-chart-clinical-500)',
 };
 @endphp
 <div class="flex items-center gap-3">
 <span class="w-24 shrink-0 text-right text-[11px] font-bold text-apoyo">{{ $item['label'] }}</span>
 <div class="min-w-0 flex-1 overflow-hidden rounded-full bg-fondo-panel h-2.5">
 <div
 class="h-full rounded-full transition-all duration-500"
 style="width: {{ $pct }}%; background-color: {{ $barColor }};"
 ></div>
 </div>
 <span class="w-8 shrink-0 text-right text-xs font-bold text-titulo">{{ $item['total'] }}</span>
 </div>
 @endforeach
 </div>
 </section>
 @endif

 {{-- Actividades por mes --}}
 @if(!empty($chartMes['data']))
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-chart-bar text-apoyo text-sm"></i>
 <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
 Actividades por mes
 @if(!$fechaDesde && !$fechaHasta)
 <span class="ml-1 text-apoyo">({{ $chartMes['anio'] }})</span>
 @endif
 </h3>
 </div>
 </div>
 <div class="p-5">
 <div class="flex items-end gap-1.5 overflow-x-auto pb-2" style="min-height: 100px;">
 @foreach($chartMes['data'] as $item)
 @php
 $height = $chartMes['maximo'] > 0
 ? max(8, round($item['total'] / $chartMes['maximo'] * 80))
 : 8;
 @endphp
 <div class="flex flex-1 min-w-[28px] flex-col items-center gap-1">
 <span class="text-[9px] font-bold text-apoyo">{{ $item['total'] }}</span>
 <div
 class="w-full rounded-t-md bg-fondo-panel transition-all duration-500"
 style="height: {{ $height }}px;"
 title="{{ $item['label'] }}: {{ $item['total'] }}"
 ></div>
 <span class="text-[9px] font-bold text-apoyo">{{ $item['label'] }}</span>
 </div>
 @endforeach
 </div>
 </div>
 </section>
 @endif

 </div>
 @endif

 {{-- Top tipos --}}
 @if(!empty($chartTipo['data']))
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-ranking text-apoyo text-sm"></i>
 <h3 class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">Tipos más utilizados</h3>
 </div>
 </div>
 <div class="space-y-2.5 p-5">
 @foreach($chartTipo['data'] as $i => $item)
 @php
 $pct = $chartTipo['maximo'] > 0
 ? round($item['total'] / $chartTipo['maximo'] * 100)
 : 0;
 $barColor = match($i % 5) {
 0 => 'var(--rm-violet)', 1 => 'var(--rm-action-primary)', 2 => 'var(--rm-warning)',
 3 => 'var(--rm-accent-terracotta)', default => 'var(--rm-clinical)',
 };
 @endphp
 <div class="flex items-center gap-3">
 <span class="w-5 shrink-0 text-center text-[10px] font-bold text-apoyo">{{ $i + 1 }}</span>
 <span class="w-40 min-w-0 shrink-0 truncate text-[11px] font-bold text-apoyo">{{ $item['label'] }}</span>
 <div class="min-w-0 flex-1 overflow-hidden rounded-full bg-fondo-panel h-2">
 <div
 class="h-full rounded-full"
 style="width: {{ $pct }}%; background-color: {{ $barColor }};"
 ></div>
 </div>
 <span class="w-8 shrink-0 text-right text-xs font-bold text-titulo">{{ $item['total'] }}</span>
 </div>
 @endforeach
 </div>
 </section>
 @endif

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- VISTA PREVIA --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <x-ui.collection-results title="Vista previa" :count="$preview->count()" label="registros recientes">
 <x-slot:actions>
 <a
 href="{{ $urlPreview }}"
 class="inline-flex items-center gap-1.5 text-[11px] font-bold text-estado-advertencia hover:underline"
 >
 <i class="ph-bold ph-arrow-square-out text-xs"></i>
 Ver completo
 </a>
 </x-slot:actions>

 <div class="w-full overflow-x-auto" wire:loading.class="opacity-50">
 <table class="rm-data-table rm-table min-w-[700px] w-full border-collapse text-sm">
 <thead>
 <tr class="border-b border-borde-suave bg-fondo-panel">
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Residente</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Tipo de actividad</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Fecha</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Hora</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Estado</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Observación</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[var(--rm-border)]/30">
 @forelse($preview as $r)
 @php
 $norm = \App\Models\Actividad::normalizarEstado($r->estado);
 $am = optional($r->adultoMayor);
 $tipo = optional($r->tipoActividad);
 @endphp
 <tr class="bg-fondo-panel hover:bg-fondo-panel transition">
 <td class="px-4 py-3">
 <p class="max-w-[160px] truncate text-xs font-bold text-titulo">
 {{ $am->ap_paterno }} {{ $am->ap_materno }}, {{ $am->nombres }}
 </p>
 </td>
 <td class="px-4 py-3">
 <p class="max-w-[130px] truncate text-xs font-bold text-apoyo">{{ $tipo->tipo ?? '—' }}</p>
 </td>
 <td class="px-4 py-3 text-xs font-bold text-apoyo">
 {{ $r->fecha?->format('d/m/Y') ?? '—' }}
 </td>
 <td class="px-4 py-3 text-xs font-bold text-apoyo">
 {{ $r->hora ? substr($r->hora, 0, 5) : '—' }}
 </td>
 <td class="px-4 py-3">
 <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $norm['clase'] }}">
 {{ $norm['etiqueta'] }}
 </span>
 </td>
 <td class="px-4 py-3">
 <p class="max-w-[150px] truncate text-[11px] font-bold text-apoyo" title="{{ $r->obs }}">
 {{ $r->obs ? \Illuminate\Support\Str::limit($r->obs, 45) : '—' }}
 </p>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="py-12 text-center">
 <div class="flex flex-col items-center gap-3">
 <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-panel">
 <i class="ph-bold ph-table text-xl text-apoyo"></i>
 </span>
 @if($buscar || $filtroTipo || $filtroEstado || $fechaDesde || $fechaHasta)
 <p class="text-sm font-bold text-apoyo">No hay actividades para los filtros seleccionados.</p>
 <button wire:click="limpiarFiltros" class="text-xs font-bold text-estado-advertencia hover:underline">Limpiar filtros</button>
 @else
 <p class="text-sm font-bold text-apoyo">No hay actividades registradas.</p>
 @endif
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </x-ui.collection-results>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- REPORTES DISPONIBLES --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-export text-boton-acento text-sm"></i>
 <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">Reportes disponibles</h2>
 </div>
 </div>
 <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">

 {{-- A. Reporte general --}}
 <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-borde-fuerte bg-fondo-panel p-4 shadow-sm">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel">
 <i class="ph-bold ph-file-text text-titulo text-lg"></i>
 </div>
 <div class="min-w-0">
 <p class="text-xs font-bold text-titulo">Reporte general</p>
 <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-apoyo">Resumen institucional por período, estado y tipo de actividad.</p>
 </div>
 <div class="mt-auto flex flex-wrap gap-1.5">
 <a href="{{ $urlPreview }}" class="rounded-lg border border-borde-suave px-2.5 py-1 text-[10px] font-bold text-apoyo hover:bg-fondo-app">
 <i class="ph-bold ph-eye mr-0.5"></i>Vista previa
 </a>
 @can('reportes.exportar_pdf')
 <a href="{{ $urlPdf }}" class="rounded-lg border border-borde-focus bg-estado-peligroBg px-2.5 py-1 text-[10px] font-bold text-boton-acento hover:bg-estado-peligroBg">
 <i class="ph-bold ph-file-pdf mr-0.5"></i>PDF
 </a>
 <a href="{{ $urlExcel }}" class="rounded-lg border border-estado-exitoBorde bg-estado-exitoBg px-2.5 py-1 text-[10px] font-bold text-estado-exito hover:bg-estado-exitoBg">
 <i class="ph-bold ph-file-xls mr-0.5"></i>Excel
 </a>
 @endcan
 </div>
 </div>

 {{-- B. Participación --}}
 <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-estado-exitoBorde bg-fondo-panel p-4 shadow-sm">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg">
 <i class="ph-bold ph-users text-estado-exito text-lg"></i>
 </div>
 <div class="min-w-0">
 <p class="text-xs font-bold text-titulo">Participación</p>
 <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-apoyo">Residentes vinculados a actividades registradas.</p>
 </div>
 <div class="mt-auto flex flex-wrap gap-1.5">
 @php
 $filtrosConRealizada = array_filter(array_merge(
 collect($this->buildFiltros())->except(['estado'])->all(),
 ['estado' => 'REALIZADA']
 ));
 @endphp
 <a href="{{ route('admin.reportes.actividades.preview', $filtrosConRealizada) }}" class="rounded-lg border border-borde-suave px-2.5 py-1 text-[10px] font-bold text-apoyo hover:bg-fondo-app">
 <i class="ph-bold ph-eye mr-0.5"></i>Vista previa
 </a>
 @can('reportes.exportar_pdf')
 <a href="{{ route('admin.reportes.actividades.excel', $filtrosConRealizada) }}" class="rounded-lg border border-estado-exitoBorde bg-estado-exitoBg px-2.5 py-1 text-[10px] font-bold text-estado-exito hover:bg-estado-exitoBg">
 <i class="ph-bold ph-file-xls mr-0.5"></i>Excel
 </a>
 @endcan
 </div>
 </div>

 {{-- C. Por tipo --}}
 <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-estado-advertenciaBorde bg-fondo-panel p-4 shadow-sm">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-advertenciaBg">
 <i class="ph-bold ph-tag text-estado-advertencia text-lg"></i>
 </div>
 <div class="min-w-0">
 <p class="text-xs font-bold text-titulo">Por tipo de actividad</p>
 <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-apoyo">Distribución según clasificación institucional de actividades.</p>
 </div>
 <div class="mt-auto flex flex-wrap gap-1.5">
 <a href="{{ route('admin.actividades.tipos') }}" class="rounded-lg border border-borde-suave px-2.5 py-1 text-[10px] font-bold text-apoyo hover:bg-fondo-app">
 <i class="ph-bold ph-list-bullets mr-0.5"></i>Ver tipos
 </a>
 @can('reportes.exportar_pdf')
 <a href="{{ $urlExcel }}" class="rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg px-2.5 py-1 text-[10px] font-bold text-estado-advertencia hover:bg-estado-advertenciaBg">
 <i class="ph-bold ph-file-xls mr-0.5"></i>Excel
 </a>
 @endcan
 </div>
 </div>

 {{-- D. Cumplimiento --}}
 <div class="flex flex-col gap-3 overflow-hidden rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel">
 <i class="ph-bold ph-chart-line text-parrafo text-lg"></i>
 </div>
 <div class="min-w-0">
 <p class="text-xs font-bold text-titulo">Cumplimiento</p>
 <p class="mt-0.5 text-[10px] font-bold leading-relaxed text-apoyo">Actividades realizadas, pendientes, canceladas o reprogramadas.</p>
 </div>
 <div class="mt-auto flex flex-wrap gap-1.5">
 <a href="{{ route('admin.actividades.asistencia') }}" class="rounded-lg border border-borde-suave px-2.5 py-1 text-[10px] font-bold text-apoyo hover:bg-fondo-app">
 <i class="ph-bold ph-clipboard-text mr-0.5"></i>Asistencia
 </a>
 @can('reportes.exportar_pdf')
 <a href="{{ $urlPdf }}" class="rounded-lg border border-borde bg-fondo-panel px-2.5 py-1 text-[10px] font-bold text-parrafo hover:bg-fondo-panel">
 <i class="ph-bold ph-file-pdf mr-0.5"></i>PDF
 </a>
 @endcan
 </div>
 </div>

 </div>
 </section>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- BLOQUE INFORMATIVO --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <div class="flex items-start gap-3 rounded-2xl border border-borde-suave bg-fondo-panel p-4">
 <i class="ph-bold ph-info mt-0.5 shrink-0 text-base text-apoyo"></i>
 <div class="min-w-0">
 <p class="text-[11px] font-bold text-apoyo">Acerca de estos reportes</p>
 <p class="mt-0.5 text-[11px] font-bold leading-relaxed text-apoyo">
 Los reportes incluyen datos de las tablas V2 <span class="font-black">actividades</span> y <span class="font-black">participantes_actividad</span>. Los filtros seleccionados
 se conservan en los enlaces de exportación PDF y Excel. La exportación requiere el permiso
 <span class="font-black">reportes.exportar_pdf</span>.
 Los registros se muestran sin incluir actividades eliminadas (soft delete).
 </p>
 </div>
 </div>

 </div>
</div>
