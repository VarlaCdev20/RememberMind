@php
 $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-sm font-bold text-titulo outline-none ring-[#E27D60]/25 transition focus:border-borde-focus focus:ring-2';
 $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1.5';
 $errCls = 'mt-1 text-[10px] font-bold text-boton-acento';
@endphp

<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data
 @keydown.window.escape="$wire.cerrarModales()">
 <div class="mx-auto max-w-7xl space-y-6">

 {{-- ── CABECERA ─────────────────────────────────────────────────────── --}}
 <section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
 <div class="max-w-3xl">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 <i class="ph-bold ph-calendar-check text-sm"></i>
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Área de Actividades
 </span>
 <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">
 Actividades institucionales
 </h1>
 <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
 Gestión global de actividades dirigidas a adultos mayores: registro, seguimiento y control.
 </p>
 </div>
 <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
 <div class="flex items-center gap-3 rounded-2xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-sm">
 <i class="ph-bold ph-calendar-check text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-apoyo">Total</p>
 <p class="text-2xl font-black leading-none text-titulo">{{ number_format($stats['total']) }}</p>
 </div>
 </div>
 <button wire:click="abrirRegistrar"
 class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-inverso shadow-sm transition hover:bg-fondo-panel hover:shadow-md active:scale-95">
 <i class="ph-bold ph-plus text-sm"></i>
 Nueva actividad
 </button>
 </div>
 </div>
 </section>

 {{-- ── 8 MÉTRICAS ──────────────────────────────────────────────────── --}}
 @php
 $metricas = [
 ['label' => 'Total actividades', 'valor' => $stats['total'], 'icono' => 'ph-calendar-blank', 'tono' => 'azul'],
 ['label' => 'Programadas', 'valor' => $stats['programadas'], 'icono' => 'ph-clock', 'tono' => 'dorado'],
 ['label' => 'Realizadas', 'valor' => $stats['realizadas'], 'icono' => 'ph-check-circle', 'tono' => 'verde'],
 ['label' => 'Canceladas', 'valor' => $stats['canceladas'], 'icono' => 'ph-x-circle', 'tono' => 'terracota'],
 ['label' => 'Reprogramadas', 'valor' => $stats['reprogramadas'], 'icono' => 'ph-arrow-counter-clockwise', 'tono' => 'violeta'],
 ['label' => 'Hoy', 'valor' => $stats['hoy'], 'icono' => 'ph-sun', 'tono' => 'dorado'],
 ['label' => 'Próximas 7 das', 'valor' => $stats['proximas7'], 'icono' => 'ph-calendar-dots', 'tono' => 'verde'],
 ['label' => 'Adultos con actividades','valor' => $stats['adultos_distintos'],'icono' => 'ph-users', 'tono' => 'azul'],
 ];
 $tonoClases = [
 'azul' => ['icono' => 'bg-fondo-panel text-titulo', 'valor' => 'text-titulo', 'linea' => 'bg-boton-principal'],
 'verde' => ['icono' => 'bg-estado-exitoBg text-estado-exito', 'valor' => 'text-estado-exito', 'linea' => 'bg-estado-exitoBg'],
 'terracota'=> ['icono' => 'bg-estado-peligroBg text-boton-acento', 'valor' => 'text-boton-acento', 'linea' => 'bg-boton-acento'],
 'dorado' => ['icono' => 'bg-estado-advertenciaBg text-estado-advertencia', 'valor' => 'text-estado-advertencia', 'linea' => 'bg-estado-advertenciaBg'],
 'violeta' => ['icono' => 'bg-fondo-panel text-parrafo', 'valor' => 'text-parrafo', 'linea' => 'bg-fondo-panel'],
 ];
 @endphp
 <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
 @foreach($metricas as $m)
 @php
 $tono = $tonoClases[$m['tono']];
 @endphp
 <article class="relative min-h-[110px] overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)]">
 <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
 <div class="flex items-start justify-between gap-3">
 <p class="max-w-[11rem] text-[10px] font-bold uppercase leading-snug tracking-[0.13em] text-apoyo">{{ $m['label'] }}</p>
 <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
 <i class="ph-bold {{ $m['icono'] }} text-lg"></i>
 </span>
 </div>
 <p class="mt-4 text-3xl font-black leading-none {{ $tono['valor'] }}">{{ number_format($m['valor']) }}</p>
 </article>
 @endforeach
 </section>

 {{-- ── FILTROS Y BÚSQUEDA ───────────────────────────────────────────── --}}
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-funnel text-apoyo text-base"></i>
 <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-titulo">Filtros y búsqueda</span>
 </div>
 </div>
 <div class="p-4">
 <div class="flex flex-wrap gap-3 items-end">
 {{-- Búsqueda --}}
 <div class="min-w-[180px] flex-1">
 <label class="{{ $labelCls }}">Buscar adulto mayor</label>
 <div class="relative">
 <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-apoyo">
 <i class="ph-bold ph-magnifying-glass text-sm"></i>
 </span>
 <input wire:model.live.debounce.300ms="search"
 type="text" placeholder="Nombre o apellido..."
 class="{{ $inputCls }} pl-8" />
 </div>
 </div>
 {{-- Tipo --}}
 <div class="min-w-[160px] flex-1">
 <label class="{{ $labelCls }}">Tipo de actividad</label>
 <select wire:model.live="filtroTipo" class="{{ $inputCls }}">
 <option value="">Todos los tipos</option>
 @foreach($tipos as $tipo)
 <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
 @endforeach
 </select>
 </div>
 {{-- Estado --}}
 <div class="min-w-[140px] flex-1">
 <label class="{{ $labelCls }}">Estado</label>
 <select wire:model.live="filtroEstado" class="{{ $inputCls }}">
 <option value="">Todos los estados</option>
 <option value="PROGRAMADA">Programada</option>
 <option value="REALIZADA">Realizada</option>
 <option value="COMPLETADA">Completada</option>
 <option value="CANCELADA">Cancelada</option>
 <option value="REPROGRAMADA">Reprogramada</option>
 </select>
 </div>
 {{-- Fecha desde --}}
 <div class="min-w-[140px]">
 <label class="{{ $labelCls }}">Desde</label>
 <input wire:model.live="filtroFechaDesde" type="date" class="{{ $inputCls }}" />
 </div>
 {{-- Fecha hasta --}}
 <div class="min-w-[140px]">
 <label class="{{ $labelCls }}">Hasta</label>
 <input wire:model.live="filtroFechaHasta" type="date" class="{{ $inputCls }}" />
 </div>
 {{-- Limpiar --}}
 <div>
 <button wire:click="limpiarFiltros"
 class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-xs font-bold text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-xs"></i>
 Limpiar
 </button>
 </div>
 </div>
 </div>
 </section>

 {{-- ── TABLA DE ACTIVIDADES ─────────────────────────────────────────── --}}
 @php /** @var \Illuminate\Pagination\LengthAwarePaginator $actividades */ @endphp
 <section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="border-b border-borde-suave bg-fondo-panel px-5 py-3.5">
 <div class="flex items-center justify-between gap-3">
 <div class="flex items-center gap-2.5">
 <i class="ph-bold ph-list-bullets text-apoyo text-lg"></i>
 <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">Listado de actividades</h2>
 </div>
 <span class="text-[10px] font-bold text-apoyo">
 {{ $actividades->total() }} registro(s)
 </span>
 </div>
 </div>
 <div class="p-5">
 @if($actividades->isEmpty())
 <div class="flex flex-col items-center gap-3 py-10 text-center">
 <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-fondo-panel">
 <i class="ph-bold ph-calendar-blank text-2xl text-apoyo"></i>
 </span>
 <p class="text-sm font-bold text-apoyo">No se encontraron actividades con los filtros aplicados.</p>
 </div>
 @else
 <div class="overflow-x-auto" wire:loading.class="opacity-50 transition-opacity"
 wire:target="search,filtroTipo,filtroEstado,filtroFechaDesde,filtroFechaHasta">
 <table class="w-full min-w-[700px] text-xs">
 <thead>
 <tr class="border-b border-borde-suave">
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">#</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Adulto mayor</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Tipo actividad</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Fecha</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Hora</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Estado</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Observación</th>
 <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/25">
 @foreach($actividades as $actividad)
 @php
 $ne = \App\Models\ActividadAdulto::normalizarEstado($actividad->estado ?? '');
 @endphp
 <tr wire:key="act-{{ $actividad->cod_act_adul }}" class="group transition hover:bg-fondo-panel">
 <td class="py-3 pr-3 font-bold text-apoyo">{{ $actividad->cod_act_adul }}</td>
 <td class="py-3 pr-4 font-bold text-titulo">
 {{ optional($actividad->adultoMayor)->ap_paterno ?? '—' }}
 {{ optional($actividad->adultoMayor)->nombres ?? '' }}
 </td>
 <td class="py-3 pr-4 text-apoyo">
 {{ optional($actividad->tipoActividad)->tipo ?? '—' }}
 </td>
 <td class="py-3 pr-4 text-apoyo">
 {{ \Carbon\Carbon::parse($actividad->fecha)->format('d/m/Y') }}
 </td>
 <td class="py-3 pr-4 text-apoyo">
 {{ $actividad->hora ? substr($actividad->hora, 0, 5) : '—' }}
 </td>
 <td class="py-3 pr-4">
 <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $ne['clase'] }}">
 {{ $ne['etiqueta'] }}
 </span>
 </td>
 <td class="py-3 pr-4 max-w-[160px] truncate text-apoyo">
 {{ $actividad->obs ? mb_substr($actividad->obs, 0, 45) . (mb_strlen($actividad->obs) > 45 ? '…' : '') : '—' }}
 </td>
 <td class="py-3">
 <div class="flex items-center gap-1.5">
 <button wire:click="abrirDetalle('{{ $actividad->cod_act_adul }}')"
 title="Ver detalle"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-fuerte bg-fondo-panel text-apoyo transition hover:border-borde-fuerte hover:bg-fondo-panel">
 <i class="ph-bold ph-eye text-xs"></i>
 </button>
 <button wire:click="abrirEditar('{{ $actividad->cod_act_adul }}')"
 title="Editar"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia transition hover:border-estado-advertenciaBorde hover:bg-estado-advertenciaBg">
 <i class="ph-bold ph-pencil text-xs"></i>
 </button>
 @if(strtoupper($actividad->estado) !== 'CANCELADA')
 <button type="button"
 title="Cancelar actividad"
 x-data
 @click="
 window.SwalAmandita.fire({
 title: '¿Cancelar actividad?',
 text: 'El estado cambiará a CANCELADA. Esta acción quedará registrada en el historial.',
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Sí, cancelar',
 cancelButtonText: 'No'
 }).then(r => { if (r.isConfirmed) $wire.cancelarActividad({{ $actividad->cod_act_adul }}) })"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento transition hover:border-borde-focus hover:bg-estado-peligroBg">
 <i class="ph-bold ph-x-circle text-xs"></i>
 </button>
 @endif
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @if($actividades->hasPages())
 <div class="mt-5 border-t border-borde-suave pt-4">
 {{ $actividades->links() }}
 </div>
 @endif
 @endif
 </div>
 </section>

 </div>

 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL — REGISTRAR ACTIVIDAD --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalRegistrar)
 <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
 style="background: rgba(47,62,92,0.50)"
 wire:click.self="cerrarModales">
 <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
 <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 {{-- Header --}}
 <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex items-center gap-2.5">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-calendar-plus text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Actividades</p>
 <h3 class="text-sm font-bold text-titulo">Registrar actividad</h3>
 </div>
 </div>
 <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>
 {{-- Formulario --}}
 <form wire:submit.prevent="guardarActividad" class="p-5 space-y-4">
 {{-- Adulto Mayor --}}
 <div>
 <label class="{{ $labelCls }}">Adulto mayor <span class="text-boton-acento">*</span></label>
 <select wire:model="codAm" class="{{ $inputCls }}">
 <option value="">Seleccione un adulto mayor...</option>
 @foreach($adultos as $adulto)
 <option value="{{ $adulto->cod_am }}">
 {{ $adulto->ap_paterno }} {{ $adulto->ap_materno ?? '' }}, {{ $adulto->nombres }}
 </option>
 @endforeach
 </select>
 @error('codAm') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Tipo de Actividad --}}
 <div>
 <label class="{{ $labelCls }}">Tipo de actividad <span class="text-boton-acento">*</span></label>
 <select wire:model="codTipoAct" class="{{ $inputCls }}">
 <option value="">Seleccione un tipo...</option>
 @foreach($tipos as $tipo)
 <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
 @endforeach
 </select>
 @error('codTipoAct') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Fecha y Hora --}}
 <div class="grid grid-cols-2 gap-3">
 <div>
 <label class="{{ $labelCls }}">Fecha <span class="text-boton-acento">*</span></label>
 <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
 @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="{{ $labelCls }}">Hora <span class="text-boton-acento">*</span></label>
 <input wire:model="hora" type="time" class="{{ $inputCls }}" />
 @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 </div>
 {{-- Estado --}}
 <div>
 <label class="{{ $labelCls }}">Estado <span class="text-boton-acento">*</span></label>
 <select wire:model="estado" class="{{ $inputCls }}">
 <option value="PROGRAMADA">Programada</option>
 <option value="REALIZADA">Realizada</option>
 <option value="COMPLETADA">Completada</option>
 <option value="REPROGRAMADA">Reprogramada</option>
 <option value="CANCELADA">Cancelada</option>
 </select>
 @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Observaciones --}}
 <div>
 <label class="{{ $labelCls }}">Observaciones <span class="text-apoyo normal-case tracking-normal">(opcional)</span></label>
 <textarea wire:model="obs" rows="3" maxlength="2000"
 placeholder="Notas adicionales sobre la actividad..."
 class="{{ $inputCls }} resize-none"></textarea>
 @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Botones --}}
 <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
 <button type="button" wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo transition hover:border-borde-suave hover:text-titulo">
 Cancelar
 </button>
 <button type="submit"
 class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-floppy-disk text-sm"></i>
 Registrar actividad
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif

 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL — EDITAR ACTIVIDAD --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalEditar)
 <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
 style="background: rgba(47,62,92,0.50)"
 wire:click.self="cerrarModales">
 <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
 <div class="h-1 bg-gradient-to-r from-[#D9A05B] via-[#E27D60] to-[#8DA280]"></div>
 {{-- Header --}}
 <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex items-center gap-2.5">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
 <i class="ph-bold ph-pencil-simple text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Actividades</p>
 <h3 class="text-sm font-bold text-titulo">Editar actividad #{{ $editandoId }}</h3>
 </div>
 </div>
 <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>
 {{-- Formulario --}}
 <form wire:submit.prevent="actualizarActividad" class="p-5 space-y-4">
 {{-- Adulto Mayor --}}
 <div>
 <label class="{{ $labelCls }}">Adulto mayor <span class="text-boton-acento">*</span></label>
 <select wire:model="codAm" class="{{ $inputCls }}">
 <option value="">Seleccione un adulto mayor...</option>
 @foreach($adultos as $adulto)
 <option value="{{ $adulto->cod_am }}">
 {{ $adulto->ap_paterno }} {{ $adulto->ap_materno ?? '' }}, {{ $adulto->nombres }}
 </option>
 @endforeach
 </select>
 @error('codAm') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Tipo de Actividad --}}
 <div>
 <label class="{{ $labelCls }}">Tipo de actividad <span class="text-boton-acento">*</span></label>
 <select wire:model="codTipoAct" class="{{ $inputCls }}">
 <option value="">Seleccione un tipo...</option>
 @foreach($tipos as $tipo)
 <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
 @endforeach
 </select>
 @error('codTipoAct') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Fecha y Hora --}}
 <div class="grid grid-cols-2 gap-3">
 <div>
 <label class="{{ $labelCls }}">Fecha <span class="text-boton-acento">*</span></label>
 <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
 @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="{{ $labelCls }}">Hora <span class="text-boton-acento">*</span></label>
 <input wire:model="hora" type="time" class="{{ $inputCls }}" />
 @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 </div>
 {{-- Estado --}}
 <div>
 <label class="{{ $labelCls }}">Estado <span class="text-boton-acento">*</span></label>
 <select wire:model="estado" class="{{ $inputCls }}">
 <option value="PROGRAMADA">Programada</option>
 <option value="REALIZADA">Realizada</option>
 <option value="COMPLETADA">Completada</option>
 <option value="REPROGRAMADA">Reprogramada</option>
 <option value="CANCELADA">Cancelada</option>
 </select>
 @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Observaciones --}}
 <div>
 <label class="{{ $labelCls }}">Observaciones <span class="text-apoyo normal-case tracking-normal">(opcional)</span></label>
 <textarea wire:model="obs" rows="3" maxlength="2000"
 placeholder="Notas adicionales sobre la actividad..."
 class="{{ $inputCls }} resize-none"></textarea>
 @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 {{-- Botones --}}
 <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
 <button type="button" wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo transition hover:border-borde-suave hover:text-titulo">
 Cancelar
 </button>
 <button type="submit"
 class="inline-flex items-center gap-2 rounded-xl bg-estado-advertenciaBg px-5 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-floppy-disk text-sm"></i>
 Guardar cambios
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif

 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL — DETALLE --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalDetalle && $detalle)
 @php
 $ne = \App\Models\ActividadAdulto::normalizarEstado($detalle->estado ?? '');
 @endphp
 <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
 style="background: rgba(47,62,92,0.50)"
 wire:click.self="cerrarModales">
 <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
 <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
 {{-- Header --}}
 <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex items-center gap-2.5">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
 <i class="ph-bold ph-calendar-check text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Actividades</p>
 <h3 class="text-sm font-bold text-titulo">Detalle de actividad</h3>
 </div>
 </div>
 <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>
 {{-- Contenido --}}
 <div class="p-5 space-y-4">
 {{-- Estado badge --}}
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Actividad #{{ $detalle->cod_act_adul }}</span>
 <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-wide {{ $ne['clase'] }}">
 {{ $ne['etiqueta'] }}
 </span>
 </div>
 {{-- Adulto Mayor --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Adulto mayor</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ optional($detalle->adultoMayor)->ap_paterno ?? '—' }}
 {{ optional($detalle->adultoMayor)->ap_materno ?? '' }}
 {{ optional($detalle->adultoMayor)->nombres ?? '' }}
 </p>
 @if($detalle->adultoMayor)
 <p class="text-[10px] font-bold text-apoyo">{{ $detalle->cod_am }}</p>
 @endif
 </div>
 {{-- Tipo + Programación --}}
 <div class="grid grid-cols-2 gap-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Tipo de actividad</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ optional($detalle->tipoActividad)->tipo ?? '—' }}
 </p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Fecha y hora</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ \Carbon\Carbon::parse($detalle->fecha)->format('d/m/Y') }}
 </p>
 @if($detalle->hora)
 <p class="text-xs font-bold text-apoyo">{{ substr($detalle->hora, 0, 5) }} hrs.</p>
 @endif
 </div>
 </div>
 {{-- Observaciones --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Observaciones</p>
 <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">
 {{ $detalle->obs ?: 'Sin observaciones registradas.' }}
 </p>
 </div>
 {{-- Timestamps --}}
 @if($detalle->created_at)
 <div class="flex items-center gap-4 text-[10px] font-bold text-apoyo">
 <span>Registrado: {{ $detalle->created_at->format('d/m/Y H:i') }}</span>
 @if($detalle->updated_at && $detalle->updated_at->ne($detalle->created_at))
 <span>Editado: {{ $detalle->updated_at->format('d/m/Y H:i') }}</span>
 @endif
 </div>
 @endif
 {{-- Botones --}}
 <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
 <button type="button" wire:click="abrirEditar('{{ $detalle->cod_act_adul }}')"
 class="inline-flex items-center gap-1.5 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-4 py-2 text-xs font-bold text-estado-advertencia transition hover:bg-estado-advertenciaBg">
 <i class="ph-bold ph-pencil text-xs"></i>
 Editar
 </button>
 <button type="button" wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo transition hover:border-borde-suave hover:text-titulo">
 Cerrar
 </button>
 </div>
 </div>
 </div>
 </div>
 @endif

</div>
