@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1';
    $errCls   = 'mt-1 text-[10px] font-bold text-boton-acento';
@endphp

{{-- CSS de FullCalendar (inyectado por Vite) --}}
@assets
    @vite(['resources/js/admin/actividades/calendario.js'])
@endassets

<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
     x-data
     @keydown.window.escape="$wire.cerrarModales()">
<div class="mx-auto max-w-[1400px] space-y-5">

{{-- ══ CABECERA ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)]">
    <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
    <div class="flex flex-col gap-4 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
                <i class="ph-bold ph-calendar-dots text-sm"></i>
                CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Calendario
            </span>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo">
                Calendario de Actividades
            </h1>
            <p class="mt-1.5 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                Planificación diaria, semanal y mensual de actividades institucionales.
                Arrastre eventos para reprogramarlos.
            </p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('admin.actividades.asistencia') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3.5 py-2 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg">
                <i class="ph-bold ph-check-square text-sm"></i> Asistencia
            </a>
            <a href="{{ route('admin.actividades.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-3.5 py-2 text-xs font-bold text-apoyo transition hover:text-titulo">
                <i class="ph-bold ph-list-bullets text-sm"></i> Ver listado
            </a>
            @can('actividades.crear')
            <button wire:click="abrirModalNueva"
                class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso shadow-sm transition hover:shadow-md active:scale-95">
                <i class="ph-bold ph-plus text-sm"></i> Nueva actividad
            </button>
            @endcan
        </div>
    </div>
</section>

{{-- ══ CARDS DE RESUMEN ════════════════════════════════════════════════════ --}}
<div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
    @php
        $cards = [
            ['label' => 'Hoy',             'valor' => $stats['hoy'],             'icono' => 'ph-sun',            'cls' => 'text-estado-advertencia', 'bg' => 'bg-estado-advertenciaBg', 'border' => 'border-estado-advertenciaBorde'],
            ['label' => 'Programadas',     'valor' => $stats['programadas'],     'icono' => 'ph-clock',          'cls' => 'text-titulo', 'bg' => 'bg-fondo-panel', 'border' => 'border-borde-suave'],
            ['label' => 'En curso',        'valor' => $stats['en_curso'],        'icono' => 'ph-play-circle',    'cls' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg', 'border' => 'border-estado-exitoBorde'],
            ['label' => 'Evaluadas',       'valor' => $stats['evaluadas'],       'icono' => 'ph-clipboard-text', 'cls' => 'text-[#5A8A70]', 'bg' => 'bg-[#8DA280]/15', 'border' => 'border-[#8DA280]/40'],
            ['label' => 'Con seguimiento', 'valor' => $stats['con_seguimiento'], 'icono' => 'ph-warning-circle', 'cls' => 'text-boton-acento', 'bg' => 'bg-estado-peligroBg', 'border' => 'border-borde-focus'],
        ];
    @endphp
    @foreach($cards as $c)
    <article class="overflow-hidden rounded-2xl border {{ $c['border'] }} {{ $c['bg'] }} p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] {{ $c['cls'] }}">{{ $c['label'] }}</p>
            <i class="ph-bold {{ $c['icono'] }} text-lg {{ $c['cls'] }} shrink-0"></i>
        </div>
        <p class="mt-2 text-3xl font-black {{ $c['cls'] }}">{{ number_format($c['valor']) }}</p>
    </article>
    @endforeach
</div>

{{-- ══ FILTROS ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[180px] flex-1">
            <label class="{{ $labelCls }}">Buscar por nombre o lugar</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
                <input wire:model.live.debounce.400ms="buscar" type="text"
                    placeholder="Nombre, tipo, lugar..."
                    class="{{ $inputCls }} pl-8" />
            </div>
        </div>
        <div class="min-w-[160px] flex-1">
            <label class="{{ $labelCls }}">Tipo de actividad</label>
            <select wire:model.live="filtroTipo" class="{{ $inputCls }}">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->cod_tipo_act }}">{{ $tipo->tipo }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[150px] flex-1">
            <label class="{{ $labelCls }}">Estado</label>
            <select wire:model.live="filtroEstado" class="{{ $inputCls }}">
                <option value="">Todos los estados</option>
                <option value="PROGRAMADA">Programada</option>
                <option value="EN_CURSO">En curso</option>
                <option value="REPROGRAMADA">Reprogramada</option>
                <option value="REALIZADA">Realizada</option>
                <option value="EVALUADA">Evaluada</option>
                <option value="CANCELADA">Cancelada</option>
            </select>
        </div>
        <button wire:click="limpiarFiltros"
            class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-apoyo hover:border-borde-focus hover:text-boton-acento transition">
            <i class="ph-bold ph-x text-xs"></i> Limpiar
        </button>
        <button onclick="window.calendarioRM?.refetchEvents()"
            class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-apoyo hover:border-borde-focus hover:text-titulo transition">
            <i class="ph-bold ph-arrows-clockwise text-xs"></i> Actualizar
        </button>
    </div>

    {{-- Leyenda de estados ──────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 border-t border-borde-suave px-4 py-2.5">
        @php
            $leyenda = [
                ['color' => '#4A90D9', 'label' => 'Programada'],
                ['color' => '#8DA280', 'label' => 'En curso'],
                ['color' => '#D9A05B', 'label' => 'Reprogramada'],
                ['color' => '#2A9D8F', 'label' => 'Realizada'],
                ['color' => '#5A8A70', 'label' => 'Evaluada'],
                ['color' => '#E27D60', 'label' => 'Cancelada'],
            ];
        @endphp
        <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mr-2">Estados:</span>
        @foreach($leyenda as $l)
        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-apoyo">
            <span class="h-2.5 w-2.5 rounded-full" style="background:{{ $l['color'] }}"></span>
            {{ $l['label'] }}
        </span>
        @endforeach
    </div>
</section>

{{-- ══ CALENDARIO FULLCALENDAR ════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    {{-- Loader --}}
    <div id="calendar-loader"
         class="hidden flex items-center justify-center gap-3 border-b border-borde-suave px-5 py-3 text-xs font-bold text-apoyo">
        <i class="ph-bold ph-circle-notch animate-spin text-boton-acento text-sm"></i>
        Cargando actividades del calendario...
    </div>

    {{-- Contenedor FullCalendar (wire:ignore para que Livewire no lo toque) --}}
    <div wire:ignore class="p-4">
        <div id="fullcalendar-container" class="fc-rem-theme"></div>
    </div>
</section>

</div>{{-- /max-w --}}

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL — NUEVA ACTIVIDAD DESDE CALENDARIO --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalNueva)
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background: rgba(47,62,92,0.58)" wire:click.self="cerrarModales">
    <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl my-6">
        <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
                    <i class="ph-bold ph-calendar-plus text-lg"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Calendario</p>
                    <h3 class="text-sm font-bold text-titulo">Nueva actividad</h3>
                </div>
            </div>
            <button wire:click="cerrarModales"
                class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <form wire:submit.prevent="guardarNuevaActividad" class="max-h-[72vh] overflow-y-auto px-5 py-4 space-y-3">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Nombre</label>
                    <input wire:model="nuevaNombre" type="text" maxlength="200"
                        placeholder="Ej: Sesión de musicoterapia..."
                        class="{{ $inputCls }}" />
                    @error('nuevaNombre') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Tipo <span class="text-boton-acento">*</span></label>
                    <select wire:model="nuevaTipo" class="{{ $inputCls }}">
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $t)
                            <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                        @endforeach
                    </select>
                    @error('nuevaTipo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Fecha <span class="text-boton-acento">*</span></label>
                    <input wire:model="nuevaFecha" type="date" class="{{ $inputCls }}" />
                    @error('nuevaFecha') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Hora inicio <span class="text-boton-acento">*</span></label>
                    <input wire:model="nuevaHora" type="time" class="{{ $inputCls }}" />
                    @error('nuevaHora') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Hora fin</label>
                    <input wire:model="nuevaHoraFin" type="time" class="{{ $inputCls }}" />
                    @error('nuevaHoraFin') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Lugar</label>
                    <input wire:model="nuevaLugar" type="text" maxlength="200"
                        placeholder="Salón principal..."
                        class="{{ $inputCls }}" />
                </div>
                <div>
                    <label class="{{ $labelCls }}">Cupo máximo</label>
                    <input wire:model="nuevaCupo" type="number" min="1" max="500"
                        placeholder="Ej: 15"
                        class="{{ $inputCls }}" />
                </div>
            </div>

            <div>
                <label class="{{ $labelCls }}">Objetivo</label>
                <textarea wire:model="nuevaObjetivo" rows="2" maxlength="1000"
                    placeholder="Propósito de la actividad..."
                    class="{{ $inputCls }} resize-none"></textarea>
            </div>

            <div>
                <label class="{{ $labelCls }}">Materiales</label>
                <input wire:model="nuevaMateriales" type="text" maxlength="500"
                    placeholder="Sillas, papelería..."
                    class="{{ $inputCls }}" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Estado</label>
                    <select wire:model="nuevaEstado" class="{{ $inputCls }}">
                        <option value="BORRADOR">Borrador</option>
                        <option value="PROGRAMADA">Programada</option>
                        <option value="EN_CURSO">En curso</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Observaciones</label>
                    <input wire:model="nuevaObs" type="text" maxlength="500"
                        placeholder="Notas adicionales..."
                        class="{{ $inputCls }}" />
                </div>
            </div>

            <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-3">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                    <i class="ph-bold ph-floppy-disk text-sm" wire:loading.remove wire:target="guardarNuevaActividad"></i>
                    <i class="ph-bold ph-circle-notch animate-spin text-sm" wire:loading wire:target="guardarNuevaActividad"></i>
                    <span wire:loading.remove wire:target="guardarNuevaActividad">Crear actividad</span>
                    <span wire:loading wire:target="guardarNuevaActividad">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL — DETALLE DE EVENTO (eventClick) --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalDetalle && ! empty($detalle))
@php
    $d     = $detalle;
    $ne    = \App\Models\ActividadAdulto::normalizarEstado($d['estado'] ?? '');
    $total = $d['participantes'] ?? 0;
@endphp
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background: rgba(47,62,92,0.58)" wire:click.self="cerrarModales">
    <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl my-6">
        <div class="h-1.5 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>

        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-borde-suave px-5 py-4">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">
                    {{ $d['tipo'] ?? 'Actividad' }}
                    @if($d['categoria'] ?? null) · {{ $d['categoria'] }} @endif
                </p>
                <h3 class="mt-0.5 text-base font-black text-titulo truncate">
                    {{ $d['nombre'] ?? $d['tipo'] ?? 'Actividad' }}
                </h3>
                <p class="mt-1">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $ne['clase'] }}">
                        {{ $ne['etiqueta'] }}
                    </span>
                </p>
            </div>
            <button wire:click="cerrarModales"
                class="ml-3 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <div class="space-y-3 overflow-y-auto max-h-[65vh] p-5">

            {{-- A. Programación --}}
            <div class="rounded-xl border border-borde-suave bg-fondo-app p-3.5">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Programación</p>
                <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                    <div>
                        <p class="text-apoyo">Fecha</p>
                        <p class="text-titulo">{{ $d['fecha'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-apoyo">Horario</p>
                        <p class="text-titulo">
                            {{ $d['hora'] ?? '—' }}
                            @if($d['hora_fin'] ?? null) — {{ $d['hora_fin'] }}@endif hrs.
                        </p>
                    </div>
                    @if($d['lugar'] ?? null)
                    <div class="col-span-2">
                        <p class="text-apoyo">Lugar</p>
                        <p class="text-titulo">{{ $d['lugar'] }}</p>
                    </div>
                    @endif
                    @if($d['cupo_maximo'] ?? null)
                    <div>
                        <p class="text-apoyo">Cupo máximo</p>
                        <p class="text-titulo">{{ $d['cupo_maximo'] }} personas</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- B. Participación --}}
            @if($total > 0)
            <div class="rounded-xl border border-borde-suave bg-fondo-app p-3.5">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Participación</p>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="rounded-lg border border-estado-exitoBorde bg-estado-exitoBg p-2">
                        <p class="text-lg font-black text-estado-exito">{{ $d['asistieron'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold text-estado-exito">Asistieron</p>
                    </div>
                    <div class="rounded-lg border border-borde-focus bg-estado-peligroBg p-2">
                        <p class="text-lg font-black text-boton-acento">{{ $d['faltaron'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold text-boton-acento">Faltaron</p>
                    </div>
                    <div class="rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg p-2">
                        <p class="text-lg font-black text-estado-advertencia">{{ $d['justificados'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold text-estado-advertencia">Justif.</p>
                    </div>
                    <div class="rounded-lg border border-borde-suave bg-fondo-panel p-2">
                        <p class="text-lg font-black text-apoyo">{{ $d['pendientes'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold text-apoyo">Inscritos</p>
                    </div>
                </div>
                @if(($d['requiere_seguimiento'] ?? 0) > 0)
                <div class="mt-2 flex items-center gap-2 rounded-lg border border-borde-focus bg-estado-peligroBg px-3 py-1.5">
                    <i class="ph-bold ph-warning-circle text-boton-acento text-sm"></i>
                    <p class="text-[11px] font-bold text-boton-acento">
                        {{ $d['requiere_seguimiento'] }} participante(s) requieren seguimiento institucional.
                    </p>
                </div>
                @endif
            </div>
            @else
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Participación</p>
                <p class="mt-1 text-xs text-apoyo">Sin participantes registrados aún.</p>
            </div>
            @endif

            {{-- C. Evaluación (si está cerrada) --}}
            @if($d['resultado_general'] ?? null)
            <div class="rounded-xl border border-estado-exitoBorde bg-estado-exitoBg p-3.5">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-estado-exito">Evaluación</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <p class="font-bold text-estado-exito">Resultado</p>
                        <p class="text-titulo font-black">{{ $d['resultado_general'] }}</p>
                    </div>
                    @if($d['nivel_cumplimiento'] ?? null)
                    <div>
                        <p class="font-bold text-estado-exito">Nivel</p>
                        <p class="text-titulo font-black">{{ $d['nivel_cumplimiento'] }}</p>
                    </div>
                    @endif
                    @if($d['evaluacion_final'] ?? null)
                    <div class="col-span-2">
                        <p class="font-bold text-estado-exito">Evaluación final</p>
                        <p class="text-apoyo leading-relaxed">{{ $d['evaluacion_final'] }}</p>
                    </div>
                    @endif
                    @if($d['incidencias'] ?? null)
                    <div class="col-span-2">
                        <p class="font-bold text-estado-exito">Incidencias</p>
                        <p class="text-apoyo">{{ $d['incidencias'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- D. Objetivo --}}
            @if($d['objetivo'] ?? null)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Objetivo</p>
                <p class="mt-1 text-xs leading-relaxed text-apoyo">{{ $d['objetivo'] }}</p>
            </div>
            @endif

            {{-- E. Acciones rápidas --}}
            <div class="flex flex-wrap items-center gap-2 border-t border-borde-suave pt-3">

                @if(! in_array(strtoupper($d['estado'] ?? ''), ['CANCELADA', 'ANULADA', 'EVALUADA']))
                @can('actividades.editar')
                <a href="{{ route('admin.actividades.participacion') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-1.5 text-xs font-bold text-apoyo hover:text-titulo transition">
                    <i class="ph-bold ph-users-four text-xs"></i> Participantes
                </a>
                <a href="{{ route('admin.actividades.asistencia') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3 py-1.5 text-xs font-bold text-estado-exito hover:bg-estado-exitoBg transition">
                    <i class="ph-bold ph-check-square text-xs"></i> Asistencia
                </a>
                @endcan
                @endif

                <a href="{{ route('admin.actividades.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-1.5 text-xs font-bold text-apoyo hover:text-titulo transition">
                    <i class="ph-bold ph-list-bullets text-xs"></i> Ver listado
                </a>

                @if(! in_array(strtoupper($d['estado'] ?? ''), ['CANCELADA', 'ANULADA', 'EVALUADA']))
                @can('actividades.anular')
                <button type="button" x-data
                    @click="window.SwalAmandita.fire({
                        title: '¿Cancelar esta actividad?',
                        text: 'El historial institucional se conserva.',
                        icon: 'warning', showCancelButton: true,
                        confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
                    }).then(r => r.isConfirmed && $wire.cancelarActividad({{ (int)($d['id'] ?? 0) }}))"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-borde-focus bg-estado-peligroBg px-3 py-1.5 text-xs font-bold text-boton-acento hover:bg-estado-peligroBg transition">
                    <i class="ph-bold ph-x-circle text-xs"></i> Cancelar
                </button>
                @endcan
                @endif

                <button wire:click="cerrarModales"
                    class="ml-auto rounded-xl border border-borde-suave px-4 py-1.5 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- ESTILOS PERSONALIZADOS DE FULLCALENDAR --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
<style>
/* ── Integración visual FullCalendar con RememberMind ── */
.fc-rem-theme .fc-toolbar-title {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--color-titulo, #2F3E5C);
}
.fc-rem-theme .fc-button {
    background: var(--color-fondo-panel, #FDFAF7) !important;
    border: 1px solid var(--color-borde-suave, #C7B5A3) !important;
    color: var(--color-apoyo, #5C4A3A) !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    border-radius: 0.625rem !important;
    padding: 0.375rem 0.875rem !important;
    box-shadow: none !important;
    transition: all 0.2s ease !important;
}
.fc-rem-theme .fc-button:hover {
    background: var(--color-fondo-hover, #F5EFE9) !important;
    color: var(--color-titulo, #2F3E5C) !important;
}
.fc-rem-theme .fc-button-active,
.fc-rem-theme .fc-button:focus {
    background: var(--color-boton-acento, #BC6C25) !important;
    color: #fff !important;
    border-color: var(--color-boton-acento, #BC6C25) !important;
    outline: none !important;
    box-shadow: none !important;
}
.fc-rem-theme .fc-daygrid-day-number,
.fc-rem-theme .fc-col-header-cell-cushion {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--color-apoyo, #5C4A3A);
    text-decoration: none !important;
}
.fc-rem-theme .fc-day-today {
    background: rgba(188, 108, 37, 0.08) !important;
}
.fc-rem-theme .fc-event {
    border-radius: 6px !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    padding: 2px 4px !important;
    cursor: pointer !important;
}
.fc-rem-theme .fc-event:hover {
    opacity: 0.88 !important;
    transform: scale(1.01) !important;
}
.fc-rem-theme .fc-event-inner {
    display: flex;
    align-items: center;
    gap: 3px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.fc-rem-theme .fc-event-icon {
    font-size: 10px;
    opacity: 0.85;
    flex-shrink: 0;
}
.fc-rem-theme .fc-event-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.fc-rem-theme .fc-list-event-title {
    font-weight: 700;
    font-size: 0.75rem;
}
.fc-rem-theme .fc-scroller {
    overflow-y: auto !important;
}
.fc-rem-theme table {
    border-color: var(--color-borde-suave, #C7B5A3) !important;
}
.fc-rem-theme .fc-scrollgrid {
    border-color: transparent !important;
}
</style>

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- INICIALIZACIÓN DE FULLCALENDAR (después de que Livewire carga) --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@script
<script>
    // ── Inicializar el calendario cuando el componente está listo ────────────
    let calendarioListo = false;

    function iniciarCalendario() {
        if (calendarioListo || typeof window.initCalendarioActividades !== 'function') return;
        calendarioListo = true;

        const cal = window.initCalendarioActividades({
            eventsUrl:      '{{ route("admin.actividades.eventos") }}',
            reprogramarUrl: '{{ url("admin/actividades") }}',
            detalleUrl:     '{{ url("admin/actividades") }}',
            csrfToken:      document.querySelector('meta[name="csrf-token"]')?.content || '',
            canEdit:        {{ auth()->user()->can('actividades.editar') ? 'true' : 'false' }},
            filtros: {
                tipo:   '{{ $filtroTipo }}',
                estado: '{{ $filtroEstado }}',
                buscar: '{{ $buscar }}',
            },
        });

        // Guardar opciones actuales
        window._calendarioOpts = cal ? Object.assign({}, cal._options || {}) : {};
    }

    // Esperar a que el bundle de FullCalendar cargue
    let intentos = 0;
    const timer = setInterval(() => {
        if (typeof window.initCalendarioActividades === 'function') {
            clearInterval(timer);
            iniciarCalendario();
        } else if (++intentos > 30) {
            clearInterval(timer);
            console.warn('FullCalendar no cargó a tiempo.');
        }
    }, 200);

    // ── Comunicación JS ↔ Livewire ──────────────────────────────────────────

    // dateClick desde FullCalendar → abrir modal nueva actividad
    window.addEventListener('calendario:date-click', (e) => {
        $wire.abrirModalNueva(e.detail.fecha, e.detail.hora);
    });

    // eventClick desde FullCalendar → abrir modal detalle
    window.addEventListener('calendario:event-click', (e) => {
        $wire.setDetalle(e.detail);
    });

    // Livewire guardó actividad → refrescar calendario
    $wire.on('refetch-calendar-events', () => {
        window.calendarioRM?.refetchEvents();
    });

    // Filtros cambiaron → actualizar filtros globales y refrescar calendario
    $wire.on('filtros-actualizados', ({ filtros }) => {
        // Actualizar la variable global que lee el fetch de eventos
        window._calendarioFiltros = Object.assign(window._calendarioFiltros || {}, filtros);
        window.calendarioRM?.refetchEvents();
    });
</script>
@endscript

</div>
