@php
    $inputCls  = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $inputSmCls= 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $labelCls  = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1.5';
    $sectionLbl= 'flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-apoyo border-b border-borde-suave pb-2 mb-3';
    $errCls    = 'mt-1 text-[10px] font-bold text-boton-acento';
@endphp

<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
     x-data
     @keydown.window.escape="$wire.cerrarModales()">
<div class="mx-auto max-w-7xl space-y-5">

{{-- ══ CABECERA ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
    <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
    <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
                <i class="ph-bold ph-calendar-check text-sm"></i>
                CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Actividades
            </span>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">
                Actividades institucionales
            </h1>
            <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                Registro, seguimiento y evaluación de actividades dirigidas a adultos mayores.
            </p>
        </div>
        <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
            <a href="{{ route('admin.actividades.participacion') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-4 py-2.5 text-xs font-bold text-apoyo transition hover:border-borde-focus hover:text-titulo">
                <i class="ph-bold ph-users-four text-sm"></i>
                Participantes
            </a>
            <a href="{{ route('admin.actividades.asistencia') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 py-2.5 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg">
                <i class="ph-bold ph-check-square text-sm"></i>
                Asistencia
            </a>
            @can('actividades.crear')
            <button wire:click="abrirRegistrar"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-inverso shadow-sm transition hover:shadow-md active:scale-95">
                <i class="ph-bold ph-plus text-sm"></i>
                Nueva actividad
            </button>
            @endcan
        </div>
    </div>
</section>

{{-- ══ MÉTRICAS ══════════════════════════════════════════════════════════════ --}}
@php
    $metricas = [
        ['label' => 'Total', 'valor' => $stats['total'], 'icono' => 'ph-calendar-blank', 'cls' => 'text-titulo', 'bg' => 'bg-fondo-panel', 'bar' => 'bg-boton-principal'],
        ['label' => 'Programadas/En curso', 'valor' => $stats['programadas'], 'icono' => 'ph-clock', 'cls' => 'text-estado-advertencia', 'bg' => 'bg-estado-advertenciaBg', 'bar' => 'bg-estado-advertenciaBg'],
        ['label' => 'Realizadas', 'valor' => $stats['realizadas'], 'icono' => 'ph-check-circle', 'cls' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg', 'bar' => 'bg-estado-exitoBg'],
        ['label' => 'Evaluadas', 'valor' => $stats['evaluadas'], 'icono' => 'ph-clipboard-text', 'cls' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg', 'bar' => 'bg-[#8DA280]'],
        ['label' => 'Canceladas', 'valor' => $stats['canceladas'], 'icono' => 'ph-x-circle', 'cls' => 'text-boton-acento', 'bg' => 'bg-estado-peligroBg', 'bar' => 'bg-boton-acento'],
        ['label' => 'Reprogramadas', 'valor' => $stats['reprogramadas'], 'icono' => 'ph-arrows-clockwise', 'cls' => 'text-parrafo', 'bg' => 'bg-fondo-panel', 'bar' => 'bg-fondo-panel'],
        ['label' => 'Hoy', 'valor' => $stats['hoy'], 'icono' => 'ph-sun', 'cls' => 'text-estado-advertencia', 'bg' => 'bg-estado-advertenciaBg', 'bar' => 'bg-estado-advertenciaBg'],
        ['label' => 'Próximas 7 días', 'valor' => $stats['proximas7'], 'icono' => 'ph-calendar-dots', 'cls' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg', 'bar' => 'bg-estado-exitoBg'],
    ];
@endphp
<section class="grid gap-3 sm:grid-cols-4 lg:grid-cols-8">
    @foreach($metricas as $m)
    <article class="relative overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 shadow-sm">
        <div class="absolute inset-x-0 top-0 h-0.5 {{ $m['bar'] }}"></div>
        <div class="flex items-start justify-between gap-1">
            <p class="text-[9px] font-bold uppercase leading-snug tracking-[0.1em] text-apoyo">{{ $m['label'] }}</p>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $m['bg'] }}">
                <i class="ph-bold {{ $m['icono'] }} text-sm {{ $m['cls'] }}"></i>
            </span>
        </div>
        <p class="mt-2.5 text-2xl font-black leading-none {{ $m['cls'] }}">{{ number_format($m['valor']) }}</p>
    </article>
    @endforeach
</section>

{{-- ══ FILTROS ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[200px] flex-1">
            <label class="{{ $labelCls }}">Buscar actividad, adulto o tipo</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Nombre de actividad, adulto mayor, tipo..."
                    class="{{ $inputSmCls }} pl-8" />
            </div>
        </div>
        <div class="min-w-[160px] flex-1">
            <label class="{{ $labelCls }}">Tipo de actividad</label>
            <select wire:model.live="filtroTipo" class="{{ $inputSmCls }}">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px] flex-1">
            <label class="{{ $labelCls }}">Estado</label>
            <select wire:model.live="filtroEstado" class="{{ $inputSmCls }}">
                <option value="">Todos</option>
                <option value="BORRADOR">Borrador</option>
                <option value="PROGRAMADA">Programada</option>
                <option value="EN_CURSO">En curso</option>
                <option value="REALIZADA">Realizada</option>
                <option value="EVALUADA">Evaluada</option>
                <option value="CANCELADA">Cancelada</option>
                <option value="REPROGRAMADA">Reprogramada</option>
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Desde</label>
            <input wire:model.live="filtroFechaDesde" type="date" class="{{ $inputSmCls }}" />
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Hasta</label>
            <input wire:model.live="filtroFechaHasta" type="date" class="{{ $inputSmCls }}" />
        </div>
        <button wire:click="limpiarFiltros"
            class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-apoyo hover:border-borde-focus hover:text-boton-acento transition">
            <i class="ph-bold ph-x text-xs"></i> Limpiar
        </button>
    </div>
</section>

{{-- ══ TABLA ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex items-center justify-between border-b border-borde-suave px-5 py-3.5">
        <div class="flex items-center gap-2.5">
            <i class="ph-bold ph-list-bullets text-apoyo text-lg"></i>
            <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">Listado de actividades</h2>
        </div>
        <span class="text-[10px] font-bold text-apoyo">{{ $actividades->total() }} registro(s)</span>
    </div>

    @if($actividades->isEmpty())
    <div class="flex flex-col items-center gap-4 py-16 text-center">
        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel">
            <i class="ph-bold ph-calendar-blank text-3xl text-apoyo"></i>
        </span>
        @if($search || $filtroTipo || $filtroEstado || $filtroFechaDesde || $filtroFechaHasta)
        <div>
            <p class="text-sm font-bold text-apoyo">No se encontraron actividades con los filtros aplicados.</p>
            <button wire:click="limpiarFiltros" class="mt-2 text-xs font-bold text-boton-acento hover:underline">
                Limpiar filtros
            </button>
        </div>
        @else
        <div>
            <p class="text-sm font-bold text-titulo">No hay actividades registradas</p>
            <p class="mt-1 text-xs font-bold text-apoyo">Cree la primera actividad institucional.</p>
            @can('actividades.crear')
            <button wire:click="abrirRegistrar"
                class="mt-3 inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso shadow-sm transition hover:shadow-md">
                <i class="ph-bold ph-plus"></i> Nueva actividad
            </button>
            @endcan
        </div>
        @endif
    </div>
    @else
    <div class="overflow-x-auto" wire:loading.class="opacity-50 transition-opacity"
         wire:target="search,filtroTipo,filtroEstado,filtroFechaDesde,filtroFechaHasta">
        <table class="w-full min-w-[900px] text-xs">
            <thead>
                <tr class="border-b border-borde-suave">
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Actividad</th>
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Adulto / Tipo</th>
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Fecha</th>
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Lugar</th>
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Estado</th>
                    <th class="px-4 pb-2.5 pt-3 text-center font-black uppercase tracking-[0.12em] text-apoyo">Particip.</th>
                    <th class="px-4 pb-2.5 pt-3 text-right font-black uppercase tracking-[0.12em] text-apoyo">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B5A3]/25">
                @foreach($actividades as $actividad)
                @php
                    $ne           = \App\Models\ActividadAdulto::normalizarEstado($actividad->estado ?? '');
                    $estadoUpper  = strtoupper($actividad->estado ?? '');
                    $esCerrada    = in_array($estadoUpper, ['EVALUADA', 'CANCELADA', 'ANULADA']);
                    $esEvaluada   = $estadoUpper === 'EVALUADA';
                    $puedeEditar  = ! $esCerrada;
                    $puedeCancelar= ! in_array($estadoUpper, ['CANCELADA', 'ANULADA', 'EVALUADA']);
                    $puedeCerrar  = in_array($estadoUpper, ['PROGRAMADA', 'EN_CURSO', 'REALIZADA', 'PENDIENTE']);
                    $participantes = $actividad->total_participantes ?? 0;
                @endphp
                <tr wire:key="act-{{ $actividad->cod_act_adul }}"
                    class="group transition hover:bg-fondo-panel {{ $esEvaluada ? 'bg-estado-exitoBg/10' : '' }}">

                    {{-- Actividad --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($actividad->color)
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $actividad->color }}"></span>
                            @endif
                            <div class="min-w-0">
                                <p class="font-black text-titulo truncate max-w-[160px]">
                                    {{ $actividad->nombre ?? optional($actividad->tipoActividad)->tipo ?? '—' }}
                                </p>
                                @if($actividad->nombre)
                                <p class="text-[10px] text-apoyo">{{ optional($actividad->tipoActividad)->tipo }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Adulto / Tipo --}}
                    <td class="px-4 py-3">
                        @if($actividad->adultoMayor)
                        <p class="font-bold text-titulo truncate max-w-[140px]">
                            {{ optional($actividad->adultoMayor)->ap_paterno }} {{ optional($actividad->adultoMayor)->nombres }}
                        </p>
                        <p class="text-[10px] text-apoyo">{{ $actividad->cod_am }}</p>
                        @else
                        <p class="text-[10px] font-bold text-apoyo italic">Actividad grupal</p>
                        @endif
                    </td>

                    {{-- Fecha / Hora --}}
                    <td class="px-4 py-3 text-apoyo whitespace-nowrap">
                        {{ $actividad->fecha?->format('d/m/Y') ?? '—' }}
                        @if($actividad->hora)
                        <p class="text-[10px]">
                            {{ substr($actividad->hora, 0, 5) }}
                            @if($actividad->hora_fin)— {{ substr($actividad->hora_fin, 0, 5) }}@endif
                        </p>
                        @endif
                    </td>

                    {{-- Lugar --}}
                    <td class="px-4 py-3 text-apoyo">
                        @if($actividad->lugar)
                        <span class="truncate max-w-[100px] block">{{ $actividad->lugar }}</span>
                        @else
                        <span class="text-apoyo">—</span>
                        @endif
                    </td>

                    {{-- Estado --}}
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase {{ $ne['clase'] }}">
                            {{ $ne['etiqueta'] }}
                        </span>
                        @if($esEvaluada && $actividad->nivel_cumplimiento)
                        <p class="mt-0.5 text-[9px] font-bold text-apoyo">Nivel: {{ $actividad->nivel_cumplimiento }}</p>
                        @endif
                    </td>

                    {{-- Participantes --}}
                    <td class="px-4 py-3 text-center">
                        @if($participantes > 0)
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-fondo-panel text-xs font-black text-titulo">
                            {{ $participantes }}
                        </span>
                        @else
                        <span class="text-[10px] text-apoyo">—</span>
                        @endif
                        @if($actividad->cupo_maximo)
                        <p class="text-[9px] text-apoyo">/{{ $actividad->cupo_maximo }}</p>
                        @endif
                    </td>

                    {{-- Acciones --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Ver --}}
                            <button wire:click="abrirDetalle({{ $actividad->cod_act_adul }})"
                                title="Ver detalle"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-suave text-apoyo hover:text-titulo transition">
                                <i class="ph-bold ph-eye text-xs"></i>
                            </button>

                            {{-- Editar --}}
                            @if($puedeEditar)
                            @can('actividades.editar')
                            <button wire:click="abrirEditar({{ $actividad->cod_act_adul }})"
                                title="Editar actividad"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia hover:bg-estado-advertenciaBg transition">
                                <i class="ph-bold ph-pencil text-xs"></i>
                            </button>
                            @endcan
                            @endif

                            {{-- Participantes --}}
                            <a href="{{ route('admin.actividades.participacion') }}"
                                title="Gestionar participantes"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-suave text-parrafo hover:bg-fondo-panel transition">
                                <i class="ph-bold ph-users-four text-xs"></i>
                            </a>

                            {{-- Asistencia --}}
                            <a href="{{ route('admin.actividades.asistencia') }}"
                                title="Registrar asistencia"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-exitoBorde bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg transition">
                                <i class="ph-bold ph-check-square text-xs"></i>
                            </a>

                            {{-- Cerrar actividad --}}
                            @if($puedeCerrar)
                            @can('actividades.editar')
                            <button wire:click="abrirCerrar({{ $actividad->cod_act_adul }})"
                                title="Cerrar y evaluar actividad"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#8DA280]/50 bg-[#8DA280]/15 text-[#4A6043] hover:bg-[#8DA280]/25 transition">
                                <i class="ph-bold ph-lock-simple text-xs"></i>
                            </button>
                            @endcan
                            @endif

                            {{-- Cancelar --}}
                            @if($puedeCancelar)
                            @can('actividades.anular')
                            <button type="button" title="Cancelar actividad" x-data
                                @click="window.SwalAmandita.fire({
                                    title: '¿Cancelar actividad?',
                                    text: 'El estado cambiará a CANCELADA. El historial se conserva.',
                                    icon: 'warning', showCancelButton: true,
                                    confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
                                }).then(r => { if (r.isConfirmed) $wire.cancelarActividad({{ $actividad->cod_act_adul }}) })"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento hover:bg-estado-peligroBg transition">
                                <i class="ph-bold ph-x-circle text-xs"></i>
                            </button>
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($actividades->hasPages())
    <div class="border-t border-borde-suave px-5 py-3.5">
        {{ $actividades->links() }}
    </div>
    @endif
    @endif
</section>

</div>{{-- /max-w-7xl --}}

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL — REGISTRAR / EDITAR ACTIVIDAD (compartido) --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalRegistrar || $modalEditar)
@php $esEdicion = $modalEditar; @endphp
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-6"
     style="background: rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-2xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl my-6">
        <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $esEdicion ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-peligroBg text-boton-acento' }}">
                    <i class="ph-bold {{ $esEdicion ? 'ph-pencil-simple' : 'ph-calendar-plus' }} text-lg"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Actividades</p>
                    <h3 class="text-sm font-bold text-titulo">
                        {{ $esEdicion ? "Editar actividad #{$editandoId}" : 'Registrar nueva actividad' }}
                    </h3>
                </div>
            </div>
            <button wire:click="cerrarModales"
                class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:border-borde-focus hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        {{-- Formulario con secciones --}}
        <form wire:submit.prevent="{{ $esEdicion ? 'actualizarActividad' : 'guardarActividad' }}"
              class="max-h-[75vh] overflow-y-auto px-5 py-4 space-y-5">

            {{-- A. DATOS PRINCIPALES --}}
            <div>
                <p class="{{ $sectionLbl }}">
                    <i class="ph-bold ph-info text-sm"></i> A. Datos principales
                </p>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Nombre de la actividad</label>
                            <input wire:model="nombre" type="text" maxlength="200"
                                placeholder="Ej: Gimnasia del martes..."
                                class="{{ $inputCls }}" />
                            @error('nombre') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
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
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Adulto mayor <span class="text-apoyo normal-case">(dejar vacío para actividad grupal)</span></label>
                        <select wire:model="codAm" class="{{ $inputCls }}">
                            <option value="">Sin adulto asignado (actividad grupal)</option>
                            @foreach($adultos as $adulto)
                                <option value="{{ $adulto->cod_am }}">
                                    {{ $adulto->ap_paterno }} {{ $adulto->ap_materno ?? '' }}, {{ $adulto->nombres }}
                                </option>
                            @endforeach
                        </select>
                        @error('codAm') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Descripción</label>
                            <textarea wire:model="descripcion" rows="2" maxlength="1000"
                                placeholder="Descripción de la actividad..."
                                class="{{ $inputCls }} resize-none text-sm"></textarea>
                            @error('descripcion') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Objetivo</label>
                            <textarea wire:model="objetivo" rows="2" maxlength="1000"
                                placeholder="Propósito terapéutico o institucional..."
                                class="{{ $inputCls }} resize-none text-sm"></textarea>
                            @error('objetivo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Estado <span class="text-boton-acento">*</span></label>
                        <select wire:model="estado" class="{{ $inputCls }}">
                            <option value="BORRADOR">Borrador</option>
                            <option value="PROGRAMADA">Programada</option>
                            <option value="EN_CURSO">En curso</option>
                            <option value="REALIZADA">Realizada</option>
                            <option value="REPROGRAMADA">Reprogramada</option>
                            <option value="CANCELADA">Cancelada</option>
                        </select>
                        @error('estado') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- B. PROGRAMACIÓN --}}
            <div>
                <p class="{{ $sectionLbl }}">
                    <i class="ph-bold ph-calendar-check text-sm"></i> B. Programación
                </p>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="{{ $labelCls }}">Fecha <span class="text-boton-acento">*</span></label>
                            <input wire:model="fecha" type="date" class="{{ $inputCls }}" />
                            @error('fecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Hora inicio <span class="text-boton-acento">*</span></label>
                            <input wire:model="hora" type="time" class="{{ $inputCls }}" />
                            @error('hora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Hora fin</label>
                            <input wire:model="horaFin" type="time" class="{{ $inputCls }}" />
                            @error('horaFin') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Cupo máximo</label>
                            <input wire:model="cupoMaximo" type="number" min="1" max="500"
                                placeholder="Ej: 15"
                                class="{{ $inputCls }}" />
                            @error('cupoMaximo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Lugar / Espacio físico</label>
                        <input wire:model="lugar" type="text" maxlength="200"
                            placeholder="Ej: Salón de usos múltiples, Patio interior..."
                            class="{{ $inputCls }}" />
                        @error('lugar') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- C. LOGÍSTICA --}}
            <div>
                <p class="{{ $sectionLbl }}">
                    <i class="ph-bold ph-package text-sm"></i> C. Logística y recursos
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $labelCls }}">Materiales necesarios</label>
                        <textarea wire:model="materiales" rows="2" maxlength="500"
                            placeholder="Ej: Sillas, pelotas, papelería..."
                            class="{{ $inputCls }} resize-none text-sm"></textarea>
                        @error('materiales') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Color identificador</label>
                        <div class="flex items-center gap-2">
                            <input wire:model="color" type="color"
                                class="h-10 w-12 shrink-0 cursor-pointer rounded-lg border border-borde-suave bg-fondo-app p-1" />
                            <input wire:model="color" type="text" maxlength="10"
                                placeholder="#D9A05B"
                                class="{{ $inputCls }} text-xs font-mono" />
                        </div>
                        @error('color') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                        <p class="mt-1 text-[10px] text-apoyo">Útil para identificar visualmente la actividad.</p>
                    </div>
                </div>
            </div>

            {{-- D. OBSERVACIONES --}}
            <div>
                <p class="{{ $sectionLbl }}">
                    <i class="ph-bold ph-note text-sm"></i> D. Observaciones adicionales
                </p>
                <div>
                    <label class="{{ $labelCls }}">Observaciones</label>
                    <textarea wire:model="obs" rows="3" maxlength="2000"
                        placeholder="Notas relevantes sobre la actividad..."
                        class="{{ $inputCls }} resize-none"></textarea>
                    @error('obs') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Botones --}}
            <div class="sticky bottom-0 flex justify-end gap-2.5 border-t border-borde-suave bg-fondo-panel pt-4 pb-1">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave bg-fondo-card px-5 py-2.5 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl {{ $esEdicion ? 'bg-estado-advertenciaBg border border-estado-advertenciaBorde text-estado-advertencia' : 'bg-boton-acento text-inverso' }} px-5 py-2.5 text-xs font-bold shadow-sm hover:shadow-md active:scale-95 transition"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                    <i class="ph-bold ph-floppy-disk text-sm" wire:loading.remove wire:target="{{ $esEdicion ? 'actualizarActividad' : 'guardarActividad' }}"></i>
                    <i class="ph-bold ph-circle-notch animate-spin text-sm" wire:loading wire:target="{{ $esEdicion ? 'actualizarActividad' : 'guardarActividad' }}"></i>
                    {{ $esEdicion ? 'Guardar cambios' : 'Registrar actividad' }}
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
    $ne    = \App\Models\ActividadAdulto::normalizarEstado($detalle->estado ?? '');
    $am    = optional($detalle->adultoMayor);
    $tipo  = optional($detalle->tipoActividad);
    $edad  = $am->fecha_nac ? \Carbon\Carbon::parse($am->fecha_nac)->age : null;
@endphp
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background: rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-calendar-check text-lg"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Actividad #{{ $detalle->cod_act_adul }}</p>
                    <h3 class="text-sm font-bold text-titulo">{{ $detalle->nombre ?? $tipo->tipo ?? 'Detalle' }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $ne['clase'] }}">
                    {{ $ne['etiqueta'] }}
                </span>
                <button wire:click="cerrarModales"
                    class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>
        </div>
        <div class="space-y-3 p-5">

            {{-- Adulto Mayor --}}
            @if($detalle->adultoMayor)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Adulto mayor</p>
                <p class="mt-1 text-sm font-bold text-titulo">
                    {{ $am->ap_paterno }} {{ $am->ap_materno }} {{ $am->nombres }}
                </p>
                <p class="text-[10px] text-apoyo">{{ $detalle->cod_am }}{{ $edad ? " · {$edad} años" : '' }}</p>
            </div>
            @else
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Tipo</p>
                <p class="mt-1 text-sm font-bold text-titulo">{{ $tipo->tipo ?? '—' }}</p>
                <p class="text-[10px] text-apoyo">Actividad grupal · {{ $detalle->total_participantes ?? 0 }} participante(s)</p>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Fecha</p>
                    <p class="mt-1 text-sm font-bold text-titulo">{{ $detalle->fecha?->format('d/m/Y') ?? '—' }}</p>
                    @if($detalle->hora)
                    <p class="text-xs text-apoyo">
                        {{ substr($detalle->hora, 0, 5) }}
                        @if($detalle->hora_fin)— {{ substr($detalle->hora_fin, 0, 5) }} hrs.@endif
                    </p>
                    @endif
                </div>
                @if($detalle->lugar)
                <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Lugar</p>
                    <p class="mt-1 text-sm font-bold text-titulo">{{ $detalle->lugar }}</p>
                </div>
                @endif
                @if($detalle->cupo_maximo)
                <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Cupo</p>
                    <p class="mt-1 text-sm font-bold text-titulo">{{ $detalle->cupo_maximo }} personas</p>
                </div>
                @endif
            </div>

            @if($detalle->objetivo)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Objetivo</p>
                <p class="mt-1 text-xs leading-relaxed text-apoyo">{{ $detalle->objetivo }}</p>
            </div>
            @endif

            @if($detalle->obs)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Observaciones / Evaluación</p>
                <p class="mt-1 text-xs leading-relaxed text-apoyo">{{ $detalle->obs }}</p>
            </div>
            @endif

            @if($detalle->resultado_general || $detalle->nivel_cumplimiento)
            <div class="grid grid-cols-2 gap-3">
                @if($detalle->resultado_general)
                <div class="rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 py-3">
                    <p class="text-[10px] font-bold uppercase text-estado-exito">Resultado</p>
                    <p class="mt-1 text-sm font-bold text-estado-exito">{{ $detalle->resultado_general }}</p>
                </div>
                @endif
                @if($detalle->nivel_cumplimiento)
                <div class="rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 py-3">
                    <p class="text-[10px] font-bold uppercase text-estado-exito">Nivel cumplimiento</p>
                    <p class="mt-1 text-sm font-bold text-estado-exito">{{ $detalle->nivel_cumplimiento }}</p>
                </div>
                @endif
            </div>
            @endif

            @if($detalle->materiales)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Materiales</p>
                <p class="mt-1 text-xs text-apoyo">{{ $detalle->materiales }}</p>
            </div>
            @endif

            <div class="flex flex-wrap justify-end gap-2.5 border-t border-borde-suave pt-4">
                @can('actividades.editar')
                @if(! in_array(strtoupper($detalle->estado ?? ''), ['CANCELADA', 'ANULADA', 'EVALUADA']))
                <button type="button" wire:click="abrirEditar({{ $detalle->cod_act_adul }})"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-4 py-2 text-xs font-bold text-estado-advertencia hover:bg-estado-advertenciaBg transition">
                    <i class="ph-bold ph-pencil text-xs"></i> Editar
                </button>
                @endif
                @endcan
                <button wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL — CERRAR Y EVALUAR ACTIVIDAD --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalCerrar && $actividadCerrando)
@php
    $ac     = $actividadCerrando;
    $acTipo = optional($ac->tipoActividad);
@endphp
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background: rgba(47,62,92,0.60)" wire:click.self="cerrarModales">
    <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1.5 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-lock-simple text-lg"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Cierre y evaluación</p>
                    <h3 class="text-sm font-bold text-titulo">Cerrar y evaluar actividad</h3>
                </div>
            </div>
            <button wire:click="cerrarModales"
                class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        {{-- Resumen de la actividad --}}
        <div class="border-b border-borde-suave bg-fondo-app px-5 py-3">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs font-bold text-apoyo">
                <span class="font-black text-titulo">
                    {{ $ac->nombre ?? $acTipo->tipo ?? 'Actividad' }}
                </span>
                @if($ac->nombre)<span class="text-apoyo">{{ $acTipo->tipo }}</span>@endif
                <span><i class="ph-bold ph-calendar-blank mr-1"></i>{{ $ac->fecha?->format('d/m/Y') }}</span>
                @if($ac->hora)<span><i class="ph-bold ph-clock mr-1"></i>{{ substr($ac->hora, 0, 5) }}</span>@endif
                @if($ac->lugar)<span><i class="ph-bold ph-map-pin mr-1"></i>{{ $ac->lugar }}</span>@endif
                <span class="inline-flex items-center gap-1 rounded-full border border-estado-exitoBorde bg-estado-exitoBg px-2 py-0.5 text-[10px] text-estado-exito">
                    <i class="ph-bold ph-users text-[10px]"></i>
                    {{ $ac->total_participantes ?? 0 }} participante(s)
                </span>
            </div>
        </div>

        <form wire:submit.prevent="confirmarCierre" class="max-h-[65vh] overflow-y-auto px-5 py-4 space-y-4">

            {{-- Resultado general --}}
            <div>
                <label class="{{ $labelCls }}">Resultado general <span class="text-boton-acento">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @php
                        $resultados = [
                            'EXITOSA'         => ['label' => 'Exitosa', 'cls' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito', 'icon' => 'ph-check-circle'],
                            'REGULAR'         => ['label' => 'Regular', 'cls' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia', 'icon' => 'ph-minus-circle'],
                            'CON_INCIDENCIAS' => ['label' => 'Con incidencias', 'cls' => 'border-borde-focus bg-estado-peligroBg text-boton-acento', 'icon' => 'ph-warning-circle'],
                            'NO_REALIZADA'    => ['label' => 'No realizada', 'cls' => 'border-borde bg-fondo-panel text-apoyo', 'icon' => 'ph-x-circle'],
                        ];
                    @endphp
                    @foreach($resultados as $val => $opt)
                    <label class="relative flex cursor-pointer items-center gap-2.5 rounded-xl border p-3 transition {{ $resultadoGeneral === $val ? $opt['cls'] : 'border-borde-suave bg-fondo-app text-apoyo hover:border-borde-focus' }}">
                        <input type="radio" wire:model.live="resultadoGeneral" value="{{ $val }}"
                            class="sr-only" />
                        <i class="ph-bold {{ $opt['icon'] }} text-lg shrink-0"></i>
                        <span class="text-xs font-bold">{{ $opt['label'] }}</span>
                        @if($resultadoGeneral === $val)
                        <i class="ph-bold ph-check-circle absolute right-2.5 top-1/2 -translate-y-1/2 text-sm"></i>
                        @endif
                    </label>
                    @endforeach
                </div>
                @error('resultadoGeneral') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            {{-- Nivel de cumplimiento --}}
            <div>
                <label class="{{ $labelCls }}">Nivel de cumplimiento <span class="text-boton-acento">*</span></label>
                <div class="flex gap-2">
                    @php
                        $niveles = ['ALTO' => 'text-estado-exito border-estado-exitoBorde bg-estado-exitoBg', 'MEDIO' => 'text-estado-advertencia border-estado-advertenciaBorde bg-estado-advertenciaBg', 'BAJO' => 'text-boton-acento border-borde-focus bg-estado-peligroBg', 'NO_EVALUADO' => 'text-apoyo border-borde-suave bg-fondo-app'];
                    @endphp
                    @foreach($niveles as $nval => $ncls)
                    <label class="flex-1 cursor-pointer rounded-xl border py-2 text-center text-[11px] font-bold transition {{ $nivelCumplimiento === $nval ? $ncls : 'border-borde-suave bg-fondo-app text-apoyo hover:border-borde-focus' }}">
                        <input type="radio" wire:model="nivelCumplimiento" value="{{ $nval }}" class="sr-only" />
                        {{ ucfirst(strtolower(str_replace('_', ' ', $nval))) }}
                    </label>
                    @endforeach
                </div>
                @error('nivelCumplimiento') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            {{-- Evaluación final --}}
            <div>
                <label class="{{ $labelCls }}">Evaluación final <span class="text-boton-acento">*</span></label>
                <textarea wire:model="evaluacionFinal" rows="3" maxlength="2000"
                    placeholder="Descripción detallada del desarrollo de la actividad, logros y aprendizajes..."
                    class="{{ $inputCls }} resize-none"></textarea>
                @error('evaluacionFinal') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            {{-- Incidencias --}}
            <div>
                <label class="{{ $labelCls }}">Incidencias <span class="text-apoyo normal-case">(opcional)</span></label>
                <textarea wire:model="incidencias" rows="2" maxlength="1000"
                    placeholder="Eventos o situaciones relevantes durante la actividad..."
                    class="{{ $inputCls }} resize-none"></textarea>
                @error('incidencias') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            {{-- Recomendaciones --}}
            <div>
                <label class="{{ $labelCls }}">Recomendaciones <span class="text-apoyo normal-case">(opcional)</span></label>
                <textarea wire:model="recomendaciones" rows="2" maxlength="1000"
                    placeholder="Sugerencias para la próxima edición de esta actividad..."
                    class="{{ $inputCls }} resize-none"></textarea>
                @error('recomendaciones') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            {{-- Aviso --}}
            <div class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3">
                <p class="text-[11px] font-bold text-estado-advertencia">
                    <i class="ph-bold ph-warning mr-1"></i>
                    Al confirmar, el estado cambiará a <strong>EVALUADA</strong>. Esta acción queda registrada en el historial institucional.
                </p>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between border-t border-borde-suave pt-4">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-5 py-2.5 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit" x-data
                    @click.prevent="window.SwalAmandita.fire({
                        title: '¿Confirmar cierre y evaluación?',
                        text: 'La actividad quedará como EVALUADA y no podrá editarse sin permisos especiales.',
                        icon: 'question', showCancelButton: true,
                        confirmButtonText: 'Sí, cerrar actividad', cancelButtonText: 'Revisar'
                    }).then(r => { if (r.isConfirmed) $wire.confirmarCierre() })"
                    class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-5 py-2.5 text-xs font-bold text-estado-exito hover:bg-estado-exitoBg active:scale-95 transition"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                    <i class="ph-bold ph-lock-simple text-sm" wire:loading.remove wire:target="confirmarCierre"></i>
                    <i class="ph-bold ph-circle-notch animate-spin text-sm" wire:loading wire:target="confirmarCierre"></i>
                    <span wire:loading.remove wire:target="confirmarCierre">Cerrar y evaluar</span>
                    <span wire:loading wire:target="confirmarCierre">Procesando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

</div>
