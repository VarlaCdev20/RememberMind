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
 class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data
 @keydown.window.escape="$wire.cerrarModales()"
>
 <div class="mx-auto max-w-7xl space-y-5">

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- CABECERA --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="p-5 sm:p-7">
 <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

 <div class="min-w-0">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 <i class="ph-bold ph-clipboard-text text-sm"></i>
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Asistencia
 </span>
 <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">
 Asistencia a actividades
 </h1>
 <p class="mt-1.5 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
 Control institucional del cumplimiento de actividades programadas para adultos mayores.
 </p>
 <p class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] font-bold text-apoyo">
 <i class="ph-bold ph-info text-xs"></i>
 La asistencia se consolida actualmente desde el estado de cada actividad registrada.
 </p>
 </div>

 <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-nowrap">
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
 </div>

 </div>
 </div>
 </section>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- MÉTRICAS (8 cards) --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">

 {{-- Total --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde-fuerte bg-fondo-panel p-4 shadow-sm backdrop-blur-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Total registros</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-fondo-panel">
 <i class="ph-bold ph-clipboard-text text-xs text-titulo"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-titulo">{{ number_format($stats['total']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-apoyo">Actividades en el sistema</p>
 </div>

 {{-- Pendientes --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-estado-advertencia">Pendientes</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-estado-advertenciaBg">
 <i class="ph-bold ph-clock text-xs text-estado-advertencia"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-estado-advertencia">{{ number_format($stats['pendientes']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-estado-advertencia">Programadas / sin resultado</p>
 </div>

 {{-- Realizadas --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-estado-exito">Realizadas</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-estado-exitoBg">
 <i class="ph-bold ph-check-circle text-xs text-estado-exito"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-estado-exito">{{ number_format($stats['realizadas']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-estado-exito">Completadas / cumplidas</p>
 </div>

 {{-- Canceladas --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde-focus bg-estado-peligroBg p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-boton-acento">Canceladas</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-estado-peligroBg">
 <i class="ph-bold ph-x-circle text-xs text-boton-acento"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-boton-acento">{{ number_format($stats['canceladas']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-boton-acento">No realizadas / anuladas</p>
 </div>

 {{-- Reprogramadas --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Reprogramadas</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-fondo-panel">
 <i class="ph-bold ph-arrows-clockwise text-xs text-parrafo"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-parrafo">{{ number_format($stats['reprogramadas']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-apoyo">Pendientes de nueva fecha</p>
 </div>

 {{-- Hoy --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-estado-advertencia">Actividades hoy</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-estado-advertenciaBg">
 <i class="ph-bold ph-calendar-check text-xs text-estado-advertencia"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-estado-advertencia">{{ number_format($stats['hoy']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-estado-advertencia">Programadas para hoy</p>
 </div>

 {{-- Adultos con realizadas --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-estado-exito">Adultos activos</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-estado-exitoBg">
 <i class="ph-bold ph-users text-xs text-estado-exito"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-estado-exito">{{ number_format($stats['adultos_realizados']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-estado-exito">Con actividades realizadas</p>
 </div>

 {{-- Tipos con cumplimiento --}}
 <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
 <div class="flex items-start justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Tipos cumplidos</p>
 <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-fondo-panel">
 <i class="ph-bold ph-star text-xs text-parrafo"></i>
 </span>
 </div>
 <p class="mt-2 text-3xl font-black tracking-tight text-parrafo">{{ number_format($stats['tipos_cumplidos']) }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-apoyo">Tipos con al menos una realizada</p>
 </div>

 </div>

 {{-- ══════════════════════════════════════════════════════════════════ --}}
 {{-- TABLA + FILTROS --}}
 {{-- ══════════════════════════════════════════════════════════════════ --}}
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-md backdrop-blur-xl">

 {{-- Barra de filtros --}}
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex flex-wrap items-end gap-3">

 {{-- Buscar adulto --}}
 <div class="min-w-0 flex-1 basis-48">
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Adulto mayor</label>
 <div class="relative">
 <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
 <input
 type="text"
 wire:model.live.debounce.300ms="search"
 placeholder="Buscar por nombre..."
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel py-2 pl-8 pr-3 text-xs font-bold text-titulo placeholder-[#2F3E5C]/35 focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
 >
 </div>
 </div>

 {{-- Tipo --}}
 <div class="basis-40">
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Tipo</label>
 <select
 wire:model.live="filtroTipo"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel py-2 px-3 text-xs font-bold text-titulo focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
 >
 <option value="">Todos los tipos</option>
 @foreach($tipos as $t)
 <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
 @endforeach
 </select>
 </div>

 {{-- Estado --}}
 <div class="basis-36">
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Estado</label>
 <select
 wire:model.live="filtroEstado"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel py-2 px-3 text-xs font-bold text-titulo focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
 >
 <option value="">Todos los estados</option>
 <option value="PROGRAMADA">Programada / Pendiente</option>
 <option value="REALIZADA">Realizada / Cumplida</option>
 <option value="CANCELADA">Cancelada / Anulada</option>
 <option value="REPROGRAMADA">Reprogramada</option>
 </select>
 </div>

 {{-- Fecha desde --}}
 <div class="basis-36">
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Desde</label>
 <input
 type="date"
 wire:model.live="filtroFechaDesde"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel py-2 px-3 text-xs font-bold text-titulo focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
 >
 </div>

 {{-- Fecha hasta --}}
 <div class="basis-36">
 <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Hasta</label>
 <input
 type="date"
 wire:model.live="filtroFechaHasta"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel py-2 px-3 text-xs font-bold text-titulo focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20"
 >
 </div>

 {{-- Limpiar --}}
 <div class="shrink-0">
 <button
 type="button"
 wire:click="limpiarFiltros"
 class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-panel px-3.5 py-2 text-xs font-bold text-apoyo transition hover:bg-fondo-app hover:text-titulo"
 >
 <i class="ph-bold ph-x text-xs"></i>
 Limpiar
 </button>
 </div>

 </div>
 </div>

 {{-- Tabla --}}
 <div class="w-full overflow-x-auto" wire:loading.class="opacity-50">
 <table class="min-w-[900px] w-full border-collapse text-sm">
 <thead>
 <tr class="border-b border-borde-suave bg-fondo-panel">
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Adulto mayor</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Tipo de actividad</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Fecha</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Hora</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Estado</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Resultado</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Observación</th>
 <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/22">

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
 $estadoNorm = \App\Models\ActividadAdulto::normalizarEstado($r->estado);
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
 <p class="text-[10px] font-bold text-apoyo">{{ $r->cod_am }}</p>
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
 @can('actividades.editar')
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
 @can('actividades.anular')
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
 @can('actividades.editar')
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

 </section>

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
 $dNorm = \App\Models\ActividadAdulto::normalizarEstado($detalle->estado);
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
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>

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
 {{ $detalle->cod_am }} · {{ $dEdad }}
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
 @can('actividades.editar')
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

 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>

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
 'focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20',
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
 'placeholder-[#2F3E5C]/35 focus:border-estado-advertenciaBorde focus:outline-none focus:ring-2 focus:ring-[#D9A05B]/20',
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
