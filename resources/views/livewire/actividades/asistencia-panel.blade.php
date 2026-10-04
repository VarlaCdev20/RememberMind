@php
 /**
 * Deriva el resultado institucional de asistencia a partir del campo estado.
 * No existe campo asistio / hora_llegada. La asistencia se infiere del estado.
 */
 $resultado = function (string $estado): array {
 return match (strtoupper(trim($estado))) {
 'REALIZADA', 'COMPLETADA', 'FINALIZADA' => [
 'texto' => 'Asistió / cumplida',
 'clase' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
 'icon' => 'ph-check-circle',
 ],
 'PROGRAMADA', 'PENDIENTE' => [
 'texto' => 'Pendiente',
 'clase' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
 'icon' => 'ph-clock',
 ],
 'CANCELADA', 'ANULADA' => [
 'texto' => 'No realizada',
 'clase' => 'border-borde-focus bg-estado-peligroBg text-boton-acento',
 'icon' => 'ph-x-circle',
 ],
 'REPROGRAMADA' => [
 'texto' => 'Reprogramada',
 'clase' => 'border-borde bg-fondo-panel text-parrafo',
 'icon' => 'ph-arrows-clockwise',
 ],
 default => [
 'texto' => 'Sin resultado',
 'clase' => 'border-borde-suave bg-fondo-panel text-meta',
 'icon' => 'ph-minus',
 ],
 };
 };
@endphp

<div
 class="min-h-screen bg-transparent px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data
 @keydown.window.escape="$wire.cerrarModales()"
>
 <div class="mx-auto max-w-7xl space-y-5">

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- CABECERA --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <x-ui.collection-header title="Asistencia a actividades" subtitle="Control del cumplimiento de actividades programadas para residentes." icon="ph-clipboard-text" eyebrow="Área de actividades">
 <x-slot:actions>
 <button
 type="button"
 wire:click="$refresh"
 class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2 text-xs font-bold text-apoyo transition hover:bg-fondo-app hover:text-titulo"
 >
 <i class="ph-bold ph-arrows-clockwise text-sm"></i>
 Actualizar
 </button>
 <a
 href="{{ route('admin.actividades.participacion') }}"
 class="inline-flex items-center gap-2 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-3.5 py-2 text-xs font-bold text-estado-advertencia transition hover:bg-estado-advertenciaBg"
 >
 <i class="ph-bold ph-users text-sm"></i>
 Ver participación
 </a>
 <a
 href="{{ route('admin.actividades.reportes') }}"
 class="inline-flex items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-3.5 py-2 text-xs font-bold text-parrafo transition hover:bg-fondo-panel"
 >
 <i class="ph-bold ph-chart-bar text-sm"></i>
 Ver reportes
 </a>
 </x-slot:actions>
 </x-ui.collection-header>
 <p class="rm-body-sm"><i class="ph-bold ph-info" aria-hidden="true"></i> La asistencia se consolida desde el estado de cada actividad registrada.</p>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- MÉTRICAS (8 cards) --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <section class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4" aria-label="Indicadores de asistencia">
 <x-ui.metric-card icon="ph-clipboard-text" :value="number_format($stats['total'])" label="Total registros" description="Actividades en el sistema" variant="neutral" />
 <x-ui.metric-card icon="ph-clock" :value="number_format($stats['pendientes'])" label="Pendientes" description="Programadas sin resultado" variant="neutral" />
 <x-ui.metric-card icon="ph-check-circle" :value="number_format($stats['realizadas'])" label="Realizadas" description="Completadas o cumplidas" variant="mint" />
 <x-ui.metric-card icon="ph-x-circle" :value="number_format($stats['canceladas'])" label="Canceladas" description="No realizadas o anuladas" variant="coral" />
 <x-ui.metric-card icon="ph-arrows-clockwise" :value="number_format($stats['reprogramadas'])" label="Reprogramadas" description="Pendientes de nueva fecha" variant="neutral" />
 <x-ui.metric-card icon="ph-calendar-check" :value="number_format($stats['hoy'])" label="Actividades hoy" description="Programadas para hoy" variant="sky" />
 <x-ui.metric-card icon="ph-users" :value="number_format($stats['adultos_realizados'])" label="Residentes participantes" description="Con actividades realizadas" variant="mint" />
 <x-ui.metric-card icon="ph-star" :value="number_format($stats['tipos_cumplidos'])" label="Tipos cumplidos" description="Con al menos una actividad realizada" variant="neutral" />
 </section>

     {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- FILTROS DE ASISTENCIA FORMATO ALERTAS                              --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscar adulto --}}
            <div class="lg:col-span-3">
                <label for="asistencia-buscar" class="rm-collection-filter-label">Buscar residente</label>
                <div class="relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input id="asistencia-buscar" type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if(!empty($search))
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
                </div>
            </div>

            {{-- Tipo --}}
            <div class="lg:col-span-3">
                <label for="asistencia-tipo" class="rm-collection-filter-label">Tipo de actividad</label>
                <select id="asistencia-tipo" wire:model.live="filtroTipo"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Estado --}}
            <div class="lg:col-span-2">
                <label for="asistencia-estado" class="rm-collection-filter-label">Estado</label>
                <select id="asistencia-estado" wire:model.live="filtroEstado"
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
                <label for="asistencia-desde" class="rm-collection-filter-label">Desde</label>
                <input id="asistencia-desde" type="date"
                    wire:model.live="filtroFechaDesde"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
            </div>

            {{-- Fecha hasta --}}
            <div class="lg:col-span-2">
                <label for="asistencia-hasta" class="rm-collection-filter-label">Hasta</label>
                <input id="asistencia-hasta" type="date"
                    wire:model.live="filtroFechaHasta"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($search) || !empty($filtroTipo) || !empty($filtroEstado) || !empty($filtroFechaDesde) || !empty($filtroFechaHasta);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="$set('search', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
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
                    @if(!empty($filtroFechaDesde))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Desde: {{ $filtroFechaDesde }}</span>
                            <button type="button" wire:click="$set('filtroFechaDesde', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroFechaHasta))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Hasta: {{ $filtroFechaHasta }}</span>
                            <button type="button" wire:click="$set('filtroFechaHasta', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
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

    <x-ui.collection-results title="Registros de asistencia" :count="$registros->total()" label="registros">

 {{-- Tabla --}}
 <div class="w-full overflow-x-auto" wire:loading.class="opacity-50">
 <table class="rm-data-table rm-data-table--actions rm-table min-w-[900px] w-full border-collapse text-sm">
 <thead>
 <tr class="border-b border-borde-suave bg-fondo-panel">
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Residente</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Tipo de actividad</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Fecha</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Hora</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Estado</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Resultado</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Observación</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[var(--rm-border)]/30">

 @if($registros->isEmpty())
 <tr>
 <td colspan="8" class="py-16 text-center">
 <div class="flex flex-col items-center gap-3">
 <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-fondo-panel">
 <i class="ph-bold ph-clipboard-text text-2xl text-apoyo"></i>
 </span>
 @if($search || $filtroTipo || $filtroEstado || $filtroFechaDesde || $filtroFechaHasta)
 <p class="text-sm font-bold text-apoyo">No se encontraron registros con los filtros seleccionados.</p>
 <button wire:click="limpiarFiltros" class="text-xs font-bold text-estado-advertencia hover:underline">Limpiar filtros</button>
 @else
 <p class="text-sm font-bold text-apoyo">No hay actividades registradas para controlar asistencia.</p>
 @endif
 </div>
 </td>
 </tr>
 @else
 @foreach($registros as $r)
 @php
 $estadoNorm = \App\Models\Actividad::normalizarEstado($r->estado);
 $res = $resultado($r->estado);
 $estadoUpper = strtoupper($r->estado);
 $esRealizada = in_array($estadoUpper, ['REALIZADA', 'COMPLETADA', 'FINALIZADA']);
 $esCancelada = in_array($estadoUpper, ['CANCELADA', 'ANULADA']);
 $am = optional($r->adultoMayor);
 $tipo = optional($r->tipoActividad);
 @endphp
 <tr wire:key="row-{{ $r->cod_act_adul }}" class="bg-fondo-panel transition hover:bg-fondo-panel">

 {{-- Adulto mayor --}}
 <td class="px-4 py-3">
 <p class="max-w-[160px] truncate text-xs font-bold text-titulo">
 {{ $am->ap_paterno }} {{ $am->ap_materno }}, {{ $am->nombres }}
 </p>
 <p class="text-[10px] font-bold text-apoyo">{{ $am?->edad ?? 'Edad no registrada' }}{{ $am?->edad ? ' años' : '' }}</p>
 </td>

 {{-- Tipo --}}
 <td class="px-4 py-3">
 <p class="max-w-[130px] truncate text-xs font-bold text-apoyo">
 {{ $tipo->tipo ?? '—' }}
 </p>
 </td>

 {{-- Fecha --}}
 <td class="px-4 py-3 text-xs font-bold text-apoyo">
 {{ $r->fecha?->format('d/m/Y') ?? '—' }}
 </td>

 {{-- Hora --}}
 <td class="px-4 py-3 text-xs font-bold text-apoyo">
 {{ $r->hora ? substr($r->hora, 0, 5) : '—' }}
 </td>

 {{-- Estado --}}
 <td class="px-4 py-3">
 <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $estadoNorm['clase'] }}">
 {{ $estadoNorm['etiqueta'] }}
 </span>
 </td>

 {{-- Resultado institucional --}}
 <td class="px-4 py-3">
 <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $res['clase'] }}">
 <i class="ph-bold {{ $res['icon'] }} text-[10px]"></i>
 {{ $res['texto'] }}
 </span>
 </td>

 {{-- Observación --}}
 <td class="px-4 py-3">
 <p class="max-w-[140px] truncate text-[11px] font-bold text-apoyo" title="{{ $r->obs }}">
 {{ $r->obs ? \Illuminate\Support\Str::limit($r->obs, 40) : '—' }}
 </p>
 </td>

 {{-- Acciones --}}
 <td class="px-4 py-3">
 <div class="flex items-center gap-1">

 {{-- Ver detalle --}}
 <button
 type="button"
 wire:click="abrirDetalle('{{ $r->cod_act_adul }}')"
 title="Ver detalle"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-fuerte bg-fondo-panel text-apoyo transition hover:bg-fondo-panel hover:text-titulo"
 >
 <i class="ph-bold ph-eye text-xs"></i>
 </button>

 {{-- Marcar realizada (si no lo está ya) --}}
 @can('actividades.gestionar')
 @if(!$esRealizada && !$esCancelada)
 <button
 type="button"
 title="Marcar como realizada"
 x-data
 @click="window.SwalAmandita && window.SwalAmandita.fire({
 icon: 'question',
 title: '¿Marcar actividad como realizada?',
 text: 'Se registrará el cumplimiento institucional de esta actividad.',
 showCancelButton: true,
 confirmButtonText: 'Sí, realizada',
 cancelButtonText: 'Cancelar',
 }).then(r => r.isConfirmed && $wire.marcarRealizada({{ $r->cod_act_adul }}))"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-exitoBorde bg-estado-exitoBg text-estado-exito transition hover:bg-estado-exitoBg"
 >
 <i class="ph-bold ph-check text-xs"></i>
 </button>
 @endif
 @endcan

 {{-- Marcar cancelada (si no lo está ya) --}}
 @can('actividades.gestionar')
 @if(!$esCancelada)
 <button
 type="button"
 title="Marcar como cancelada"
 x-data
 @click="window.SwalAmandita && window.SwalAmandita.fire({
 icon: 'warning',
 title: '¿Cancelar actividad?',
 text: 'La actividad quedará como no realizada, conservando el registro institucional.',
 showCancelButton: true,
 confirmButtonText: 'Sí, cancelar',
 cancelButtonText: 'No',
 }).then(r => r.isConfirmed && $wire.marcarCancelada({{ $r->cod_act_adul }}))"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento transition hover:bg-estado-peligroBg"
 >
 <i class="ph-bold ph-x text-xs"></i>
 </button>
 @endif
 @endcan

 {{-- Registrar resultado (incluye reprogramar) --}}
 @can('actividades.gestionar')
 <button
 type="button"
 wire:click="abrirResultado('{{ $r->cod_act_adul }}')"
 title="Registrar resultado"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia transition hover:bg-estado-advertenciaBg"
 >
 <i class="ph-bold ph-pencil-simple text-xs"></i>
 </button>
 @endcan

 </div>
 </td>

 </tr>
 @endforeach
 @endif

 </tbody>
 </table>
 </div>

 {{-- Paginación --}}
 @if($registros->hasPages())
 <div class="border-t border-borde-suave bg-fondo-panel px-5 py-3.5">
 {{ $registros->links() }}
 </div>
 @endif

 </x-ui.collection-results>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- BLOQUE INFORMATIVO — ALCANCE DE ASISTENCIA --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <div class="flex items-start gap-3 rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4">
 <i class="ph-bold ph-info mt-0.5 shrink-0 text-base text-estado-advertencia"></i>
 <div class="min-w-0">
 <p class="text-[11px] font-bold text-estado-advertencia">Alcance de asistencia — control institucional actual</p>
 <p class="mt-0.5 text-[11px] font-bold leading-relaxed text-apoyo">
 Actualmente el sistema registra el cumplimiento de actividades mediante el estado de la actividad.
 Para un control más detallado de asistencia individual —asistió, no asistió, tarde o justificado—
 se recomienda incorporar una tabla específica de asistencia de adultos mayores a actividades en una fase posterior.
 </p>
 </div>
 </div>

 </div>{{-- /max-w-7xl --}}


 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL DETALLE --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalDetalle && $detalle)
 @php
 $dAm = optional($detalle->adultoMayor);
 $dTipo = optional($detalle->tipoActividad);
 $dNorm = \App\Models\Actividad::normalizarEstado($detalle->estado);
 $dRes = $resultado($detalle->estado);
 $dEdad = $dAm->fecha_nac
 ? \Carbon\Carbon::parse($dAm->fecha_nac)->age . ' años'
 : '—';
 @endphp
 <div
 class="fixed inset-0 z-50 flex items-center justify-center p-4"
 role="dialog" aria-modal="true"
 wire:click.self="cerrarModales"
 >
 <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm"></div>
 <div class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto overflow-x-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-app shadow-2xl">

 {{-- Gradiente superior --}}
 <div class="h-1.5 bg-gradient-to-r from-[var(--rm-accent-terracotta)] via-[var(--rm-warning)] to-[var(--rm-action-primary)]"></div>

 {{-- Encabezado modal --}}
 <div class="flex items-start justify-between p-5 sm:p-6">
 <div>
 <span class="inline-flex items-center gap-1.5 rounded-full border border-borde-fuerte bg-fondo-panel px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">
 <i class="ph-bold ph-clipboard-text text-xs"></i>
 Detalle de actividad
 </span>
 <h2 class="mt-2 text-xl font-extrabold text-titulo">
 {{ $dAm->ap_paterno }} {{ $dAm->ap_materno }}
 @if($dAm->nombres), {{ $dAm->nombres }}@endif
 </h2>
 <p class="text-xs font-bold text-apoyo">
 {{ $dEdad }}
 </p>
 </div>
 <button
 type="button"
 wire:click="cerrarModales"
 class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-apoyo transition hover:bg-fondo-app"
 >
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>

 <div class="space-y-4 px-5 pb-6 sm:px-6">

 {{-- Tipo + Estado + Resultado --}}
 <div class="grid gap-4 sm:grid-cols-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Tipo de actividad</p>
 <p class="mt-1 text-sm font-bold text-titulo">{{ $dTipo->tipo ?? '—' }}</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Estado actual</p>
 <p class="mt-1.5">
 <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $dNorm['clase'] }}">
 {{ $dNorm['etiqueta'] }}
 </span>
 </p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Resultado institucional</p>
 <p class="mt-1.5">
 <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $dRes['clase'] }}">
 <i class="ph-bold {{ $dRes['icon'] }} text-[10px]"></i>
 {{ $dRes['texto'] }}
 </span>
 </p>
 </div>
 </div>

 {{-- Fecha + Hora --}}
 <div class="grid gap-4 sm:grid-cols-2">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Fecha</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ $detalle->fecha?->format('d/m/Y') ?? '—' }}
 </p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Hora</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ $detalle->hora ? substr($detalle->hora, 0, 5) : '—' }}
 </p>
 </div>
 </div>

 {{-- Observación --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Observación</p>
 <p class="mt-1 text-sm font-bold leading-relaxed text-apoyo">
 {{ $detalle->obs ?: 'Sin observaciones registradas.' }}
 </p>
 </div>

 {{-- Metadatos --}}
 <div class="grid gap-4 sm:grid-cols-2">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Fecha de registro</p>
 <p class="mt-1 text-xs font-bold text-apoyo">
 {{ $detalle->created_at?->format('d/m/Y H:i') ?? '—' }}
 </p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Última actualización</p>
 <p class="mt-1 text-xs font-bold text-apoyo">
 {{ $detalle->updated_at?->format('d/m/Y H:i') ?? '—' }}
 </p>
 </div>
 </div>

 {{-- Acciones del modal --}}
 <div class="flex items-center justify-between border-t border-borde-suave pt-4">
 <button
 type="button"
 wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-2 text-xs font-bold text-apoyo transition hover:bg-fondo-app"
 >
 Cerrar
 </button>
 @can('actividades.gestionar')
 <button
 type="button"
 wire:click="abrirResultado('{{ $detalle->cod_act_adul }}')"
 class="inline-flex items-center gap-2 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-4 py-2 text-xs font-bold text-estado-advertencia transition hover:bg-estado-advertenciaBg"
 >
 <i class="ph-bold ph-pencil-simple text-xs"></i>
 Registrar resultado
 </button>
 @endcan
 </div>

 </div>
 </div>
 </div>
 @endif


 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL REGISTRAR RESULTADO --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalResultado)
 <div
 class="fixed inset-0 z-50 flex items-center justify-center p-4"
 role="dialog" aria-modal="true"
 wire:click.self="cerrarModales"
 >
 <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm"></div>
 <div class="relative w-full max-w-lg max-h-[85vh] overflow-y-auto overflow-x-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-app shadow-2xl">

 <div class="h-1.5 bg-gradient-to-r from-[var(--rm-accent-terracotta)] via-[var(--rm-warning)] to-[var(--rm-action-primary)]"></div>

 <div class="flex items-start justify-between p-5 sm:p-6">
 <div>
 <span class="inline-flex items-center gap-1.5 rounded-full border border-estado-advertenciaBorde bg-estado-advertenciaBg px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-[0.15em] text-estado-advertencia">
 <i class="ph-bold ph-pencil-simple text-xs"></i>
 Registrar resultado
 </span>
 <h2 class="mt-2 text-xl font-extrabold text-titulo">Registrar resultado de actividad</h2>
 <p class="mt-0.5 text-xs font-bold text-apoyo">
 Actualice el estado y la observación del registro de actividad.
 </p>
 </div>
 <button
 type="button"
 wire:click="cerrarModales"
 class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-borde-suave bg-fondo-panel text-apoyo transition hover:bg-fondo-app"
 >
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>

 <form wire:submit.prevent="guardarResultado" class="space-y-4 px-5 pb-6 sm:px-6">

 {{-- Estado --}}
 <div>
 <label class="mb-1.5 block text-xs font-bold text-titulo">
 Estado <span class="text-boton-acento">*</span>
 </label>
 <select
 wire:model="estado"
 @class([
 'w-full rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-bold text-titulo',
 'focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[var(--rm-warning)]/20',
 'border-borde-focus' => $errors->has('estado'),
 'border-borde-suave' => !$errors->has('estado'),
 ])
 >
 <option value="PROGRAMADA">Programada — pendiente de realizarse</option>
 <option value="REALIZADA">Realizada — actividad cumplida</option>
 <option value="CANCELADA">Cancelada — no se realizó</option>
 <option value="REPROGRAMADA">Reprogramada — nueva fecha pendiente</option>
 </select>
 @error('estado')
 <p class="mt-1 text-[11px] font-bold text-boton-acento">{{ $message }}</p>
 @enderror
 </div>

 {{-- Observación --}}
 <div>
 <label class="mb-1.5 block text-xs font-bold text-titulo">
 Observación
 <span class="ml-1 text-[10px] font-bold text-apoyo">(opcional)</span>
 </label>
 <textarea
 wire:model="obs"
 rows="4"
 placeholder="Notas adicionales sobre el resultado de la actividad..."
 @class([
 'w-full resize-none rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-bold text-titulo',
 'placeholder-[var(--rm-text-muted)] focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[var(--rm-warning)]/20',
 'border-borde-focus' => $errors->has('obs'),
 'border-borde-suave' => !$errors->has('obs'),
 ])
 ></textarea>
 @error('obs')
 <p class="mt-1 text-[11px] font-bold text-boton-acento">{{ $message }}</p>
 @enderror
 </div>

 {{-- Nota: sin campos inventados --}}
 <p class="text-[10px] font-bold leading-relaxed text-apoyo">
 <i class="ph-bold ph-info mr-1"></i>
 Solo se pueden modificar el estado y la observación. La asistencia individual detallada
 (asistió, tarde, justificado) requiere una tabla específica futura.
 </p>

 {{-- Botones --}}
 <div class="flex items-center justify-between border-t border-borde-suave pt-4">
 <button
 type="button"
 wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-panel px-5 py-2 text-xs font-bold text-apoyo transition hover:bg-fondo-app"
 >
 Cancelar
 </button>
 <button
 type="submit"
 class="inline-flex items-center gap-2 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-5 py-2 text-xs font-bold text-estado-advertencia transition hover:bg-estado-advertenciaBg"
 wire:loading.attr="disabled"
 wire:loading.class="opacity-70"
 >
 <i class="ph-bold ph-floppy-disk text-sm"></i>
 <span wire:loading.remove wire:target="guardarResultado">Guardar resultado</span>
 <span wire:loading wire:target="guardarResultado">Guardando...</span>
 </button>
 </div>

 </form>
 </div>
 </div>
 @endif

</div>
