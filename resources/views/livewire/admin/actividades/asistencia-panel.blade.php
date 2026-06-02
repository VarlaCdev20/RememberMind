@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1.5';
    $errCls   = 'mt-1 text-[10px] font-bold text-boton-acento';

    $estadoAsistCls = fn(string $e) => match($e) {
        'ASISTIO'     => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
        'FALTO'       => 'border-borde-focus bg-estado-peligroBg text-boton-acento',
        'JUSTIFICADO' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
        default       => 'border-borde-suave bg-fondo-panel text-apoyo',
    };
@endphp

<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
     x-data
     @keydown.window.escape="$wire.cerrarModales()">
<div class="mx-auto max-w-7xl space-y-5">

{{-- ══ CABECERA ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
    <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
    <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            @if($viewMode === 'asistencia' && $actividad)
                <button wire:click="volverALista"
                    class="mb-2 inline-flex items-center gap-1.5 text-xs font-bold text-apoyo hover:text-boton-acento transition">
                    <i class="ph-bold ph-arrow-left text-sm"></i> Volver a lista
                </button>
                <h1 class="text-2xl font-black tracking-tight text-titulo">
                    Asistencia: {{ $actividad->nombre ?? optional($actividad->tipoActividad)->tipo ?? 'Actividad' }}
                </h1>
                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs font-bold text-apoyo">
                    @php $ne = \App\Models\ActividadAdulto::normalizarEstado($actividad->estado ?? ''); @endphp
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $ne['clase'] }}">{{ $ne['etiqueta'] }}</span>
                    <span><i class="ph-bold ph-calendar-blank mr-1"></i>{{ $actividad->fecha?->format('d/m/Y') }}</span>
                    @if($actividad->hora)<span><i class="ph-bold ph-clock mr-1"></i>{{ substr($actividad->hora, 0, 5) }}</span>@endif
                    <span><i class="ph-bold ph-users mr-1"></i>{{ count($asistencias) }} participante(s)</span>
                </div>
            @else
                <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
                    <i class="ph-bold ph-clipboard-text text-sm"></i>
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </span>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">Asistencia a actividades</h1>
                <p class="mt-1.5 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                    Control de asistencia individual y cierre de actividades institucionales.
                </p>
            @endif
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            @if($viewMode === 'asistencia' && $actividad)
                @if(! empty($asistencias))
                <button type="button" x-data
                    @click="window.SwalAmandita.fire({
                        title: '¿Marcar todos como asistieron?',
                        icon: 'question', showCancelButton: true,
                        confirmButtonText: 'Sí', cancelButtonText: 'No'
                    }).then(r => r.isConfirmed && $wire.marcarTodosAsistieron())"
                    class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3.5 py-2 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg active:scale-95">
                    <i class="ph-bold ph-check-circle text-sm"></i> Todos asistieron
                </button>
                <button type="button" x-data
                    @click="window.SwalAmandita.fire({
                        title: '¿Marcar todos como faltaron?',
                        icon: 'warning', showCancelButton: true,
                        confirmButtonText: 'Sí', cancelButtonText: 'No'
                    }).then(r => r.isConfirmed && $wire.marcarTodosFaltaron())"
                    class="inline-flex items-center gap-2 rounded-xl border border-borde-focus bg-estado-peligroBg px-3.5 py-2 text-xs font-bold text-boton-acento transition hover:bg-estado-peligroBg active:scale-95">
                    <i class="ph-bold ph-x-circle text-sm"></i> Todos faltaron
                </button>
                @endif
                @can('actividades.editar')
                <button wire:click="guardarAsistencia"
                    class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso shadow-sm transition hover:shadow-md active:scale-95">
                    <i class="ph-bold ph-floppy-disk text-sm"></i> Guardar asistencia
                    <span wire:loading wire:target="guardarAsistencia" class="ml-1 text-[10px]">...</span>
                </button>
                <button wire:click="abrirCerrar"
                    class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 py-2 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg active:scale-95">
                    <i class="ph-bold ph-lock-simple text-sm"></i> Cerrar actividad
                </button>
                @endcan
            @else
                <a href="{{ route('admin.actividades.participacion') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-3.5 py-2 text-xs font-bold text-apoyo transition hover:text-titulo">
                    <i class="ph-bold ph-users text-sm"></i> Participantes
                </a>
                <a href="{{ route('admin.actividades.reportes') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-3.5 py-2 text-xs font-bold text-apoyo transition hover:text-titulo">
                    <i class="ph-bold ph-chart-bar text-sm"></i> Reportes
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ══ MÉTRICAS ══════════════════════════════════════════════════════════════ --}}
@if($viewMode === 'lista')
<div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
    <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Total actividades</p>
            <i class="ph-bold ph-clipboard-text text-lg text-titulo"></i>
        </div>
        <p class="mt-2 text-3xl font-black text-titulo">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-estado-advertencia">Pendientes / En curso</p>
            <i class="ph-bold ph-clock text-lg text-estado-advertencia"></i>
        </div>
        <p class="mt-2 text-3xl font-black text-estado-advertencia">{{ number_format($stats['pendientes']) }}</p>
    </div>
    <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-estado-exito">Evaluadas</p>
            <i class="ph-bold ph-check-circle text-lg text-estado-exito"></i>
        </div>
        <p class="mt-2 text-3xl font-black text-estado-exito">{{ number_format($stats['evaluadas']) }}</p>
    </div>
    <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde-focus bg-estado-peligroBg p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-boton-acento">Con seguimiento</p>
            <i class="ph-bold ph-warning-circle text-lg text-boton-acento"></i>
        </div>
        <p class="mt-2 text-3xl font-black text-boton-acento">{{ number_format($stats['requieren_seguimiento']) }}</p>
    </div>
</div>

{{-- Filtros --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[180px] flex-1">
            <label class="{{ $labelCls }}">Buscar actividad</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Nombre o tipo..." class="{{ $inputCls }} pl-8 py-2.5" />
            </div>
        </div>
        <div class="min-w-[150px] flex-1">
            <label class="{{ $labelCls }}">Tipo</label>
            <select wire:model.live="filtroTipo" class="{{ $inputCls }} py-2.5">
                <option value="">Todos</option>
                @foreach($tipos as $t)
                    <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[130px] flex-1">
            <label class="{{ $labelCls }}">Estado</label>
            <select wire:model.live="filtroEstado" class="{{ $inputCls }} py-2.5">
                <option value="">Todos</option>
                <option value="PROGRAMADA">Programada / Pendiente</option>
                <option value="EN_CURSO">En curso</option>
                <option value="REALIZADA">Realizada</option>
                <option value="EVALUADA">Evaluada</option>
                <option value="CANCELADA">Cancelada</option>
                <option value="REPROGRAMADA">Reprogramada</option>
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Desde</label>
            <input wire:model.live="filtroFechaDesde" type="date" class="{{ $inputCls }} py-2.5" />
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Hasta</label>
            <input wire:model.live="filtroFechaHasta" type="date" class="{{ $inputCls }} py-2.5" />
        </div>
        <button wire:click="limpiarFiltros"
            class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-xs font-bold text-apoyo hover:text-boton-acento transition">
            <i class="ph-bold ph-x text-xs"></i> Limpiar
        </button>
    </div>
</section>

{{-- Tabla actividades --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-md">
    <div class="border-b border-borde-suave px-5 py-3.5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-clipboard-text mr-2 text-apoyo"></i>Actividades institucionales
            </h2>
            <span class="text-[10px] font-bold text-apoyo">{{ $registros->total() }} registro(s)</span>
        </div>
    </div>
    <div class="overflow-x-auto" wire:loading.class="opacity-50">
        <table class="min-w-[800px] w-full text-xs">
            <thead>
                <tr class="border-b border-borde-suave bg-fondo-panel">
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Actividad / Tipo</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Fecha</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Estado</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Participantes</th>
                    <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B5A3]/20">
                @if($registros->isEmpty())
                <tr>
                    <td colspan="5" class="py-14 text-center">
                        <p class="text-sm font-bold text-apoyo">No hay actividades que coincidan con los filtros.</p>
                    </td>
                </tr>
                @else
                @foreach($registros as $r)
                @php
                    $estadoNorm = \App\Models\ActividadAdulto::normalizarEstado($r->estado ?? '');
                    $puedeAsistencia = ! in_array(strtoupper($r->estado), ['CANCELADA', 'ANULADA', 'EVALUADA']);
                @endphp
                <tr wire:key="row-{{ $r->cod_act_adul }}" class="hover:bg-fondo-panel transition">
                    <td class="px-4 py-3">
                        <p class="font-black text-titulo max-w-[180px] truncate">
                            {{ $r->nombre ?? optional($r->tipoActividad)->tipo ?? '—' }}
                        </p>
                        @if($r->nombre)
                        <p class="text-[10px] text-apoyo">{{ optional($r->tipoActividad)->tipo }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-apoyo whitespace-nowrap">
                        {{ $r->fecha?->format('d/m/Y') ?? '—' }}
                        @if($r->hora)<p class="text-[10px]">{{ substr($r->hora, 0, 5) }}</p>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $estadoNorm['clase'] }}">
                            {{ $estadoNorm['etiqueta'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-fondo-panel text-xs font-black text-titulo">
                            {{ $r->total_participantes ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            <button wire:click="abrirDetalle({{ $r->cod_act_adul }})"
                                title="Ver detalle"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-suave text-apoyo hover:text-titulo transition">
                                <i class="ph-bold ph-eye text-xs"></i>
                            </button>
                            @if($puedeAsistencia)
                            @can('actividades.editar')
                            <button wire:click="seleccionarActividad({{ $r->cod_act_adul }})"
                                title="Registrar asistencia"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-exitoBorde bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg transition">
                                <i class="ph-bold ph-check-square text-xs"></i>
                            </button>
                            @endcan
                            @endif
                            @can('actividades.anular')
                            @if(! in_array(strtoupper($r->estado), ['CANCELADA', 'ANULADA']))
                            <button type="button" title="Cancelar" x-data
                                @click="window.SwalAmandita.fire({
                                    icon: 'warning', title: '¿Cancelar actividad?',
                                    text: 'El historial se conserva.',
                                    showCancelButton: true, confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
                                }).then(r => r.isConfirmed && $wire.marcarCancelada({{ $r->cod_act_adul }}))"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento hover:bg-estado-peligroBg transition">
                                <i class="ph-bold ph-x text-xs"></i>
                            </button>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
    @if($registros->hasPages())
    <div class="border-t border-borde-suave px-5 py-3">{{ $registros->links() }}</div>
    @endif
</section>
@endif {{-- fin viewMode lista --}}

{{-- ══ VISTA: ASISTENCIA INLINE ══════════════════════════════════════════════ --}}
@if($viewMode === 'asistencia' && $actividad)

@php
    // Contadores para el resumen de asistencia
    $cuentaAsistio     = collect($asistencias)->where('estado_asistencia', 'ASISTIO')->count();
    $cuentaFalto       = collect($asistencias)->where('estado_asistencia', 'FALTO')->count();
    $cuentaJustificado = collect($asistencias)->where('estado_asistencia', 'JUSTIFICADO')->count();
    $cuentaInscrito    = collect($asistencias)->where('estado_asistencia', 'INSCRITO')->count();
    $totalAsist        = count($asistencias);
@endphp

{{-- Alertas institucionales --}}
@if(! empty($alertas))
<div class="flex items-start gap-3 rounded-2xl border border-borde-focus bg-estado-peligroBg px-4 py-3">
    <i class="ph-bold ph-warning-circle mt-0.5 text-lg text-boton-acento shrink-0"></i>
    <p class="text-xs font-bold text-boton-acento">
        Hay {{ count($alertas) }} participante(s) con indicadores de atención.
        Revise los registros marcados con <i class="ph-bold ph-warning"></i>.
    </p>
</div>
@endif

@if(empty($asistencias))
{{-- Empty state: sin participantes --}}
<div class="flex flex-col items-center gap-4 rounded-[1.45rem] border border-borde-suave bg-fondo-panel py-16 text-center shadow-sm">
    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel">
        <i class="ph-bold ph-users text-3xl text-apoyo"></i>
    </span>
    <div>
        <p class="text-sm font-bold text-titulo">Sin participantes en esta actividad</p>
        <p class="mt-1 text-xs font-bold text-apoyo">Registre participantes primero desde el módulo de Participación.</p>
    </div>
    <a href="{{ route('admin.actividades.participacion') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold text-inverso transition hover:shadow-md active:scale-95">
        <i class="ph-bold ph-users-four"></i> Ir a Participación
    </a>
</div>

@else

{{-- ── Resumen de asistencia ────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="flex items-center gap-3 overflow-hidden rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-3.5 shadow-sm">
        <i class="ph-bold ph-check-circle text-2xl text-estado-exito shrink-0"></i>
        <div>
            <p class="text-[10px] font-bold uppercase text-estado-exito">Asistió</p>
            <p class="text-2xl font-black text-estado-exito">{{ $cuentaAsistio }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3 overflow-hidden rounded-2xl border border-borde-focus bg-estado-peligroBg p-3.5 shadow-sm">
        <i class="ph-bold ph-x-circle text-2xl text-boton-acento shrink-0"></i>
        <div>
            <p class="text-[10px] font-bold uppercase text-boton-acento">Faltó</p>
            <p class="text-2xl font-black text-boton-acento">{{ $cuentaFalto }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3 overflow-hidden rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3.5 shadow-sm">
        <i class="ph-bold ph-warning text-2xl text-estado-advertencia shrink-0"></i>
        <div>
            <p class="text-[10px] font-bold uppercase text-estado-advertencia">Justificado</p>
            <p class="text-2xl font-black text-estado-advertencia">{{ $cuentaJustificado }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3 overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 shadow-sm">
        <i class="ph-bold ph-clock text-2xl text-apoyo shrink-0"></i>
        <div>
            <p class="text-[10px] font-bold uppercase text-apoyo">Pendiente</p>
            <p class="text-2xl font-black text-apoyo">{{ $cuentaInscrito }}</p>
        </div>
    </div>
</div>

{{-- ── Tabla de asistencia ──────────────────────────────────────────────── --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    {{-- Header con acciones bulk --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-borde-suave px-5 py-3.5">
        <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
            <i class="ph-bold ph-check-square mr-2 text-apoyo"></i>
            Asistencia individual ({{ $totalAsist }} participante(s))
        </h2>
        <div class="flex flex-wrap gap-2">
            {{-- Limpiar --}}
            <button type="button" x-data
                @click="window.SwalAmandita.fire({
                    title: '¿Limpiar toda la asistencia?',
                    text: 'Todos los registros volverán a estado INSCRITO.',
                    icon: 'question', showCancelButton: true,
                    confirmButtonText: 'Sí, limpiar', cancelButtonText: 'No'
                }).then(r => r.isConfirmed && $wire.limpiarAsistencia())"
                class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave px-3 py-1.5 text-xs font-bold text-apoyo hover:border-borde-focus hover:text-titulo transition">
                <i class="ph-bold ph-eraser text-xs"></i> Limpiar
            </button>
            {{-- Todos faltaron --}}
            <button type="button" x-data
                @click="window.SwalAmandita.fire({
                    title: '¿Marcar todos como faltaron?',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'Sí', cancelButtonText: 'No'
                }).then(r => r.isConfirmed && $wire.marcarTodosFaltaron())"
                class="inline-flex items-center gap-1.5 rounded-xl border border-borde-focus bg-estado-peligroBg px-3 py-1.5 text-xs font-bold text-boton-acento hover:bg-estado-peligroBg transition">
                <i class="ph-bold ph-x-circle text-xs"></i> Todos faltaron
            </button>
            {{-- Todos asistieron --}}
            <button type="button" x-data
                @click="window.SwalAmandita.fire({
                    title: '¿Marcar todos como asistieron?',
                    icon: 'question', showCancelButton: true,
                    confirmButtonText: 'Sí', cancelButtonText: 'No'
                }).then(r => r.isConfirmed && $wire.marcarTodosAsistieron())"
                class="inline-flex items-center gap-1.5 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3 py-1.5 text-xs font-bold text-estado-exito hover:bg-estado-exitoBg transition">
                <i class="ph-bold ph-check-circle text-xs"></i> Todos asistieron
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-xs">
            <thead>
                <tr class="border-b border-borde-suave bg-fondo-panel">
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Adulto mayor</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Asistencia <span class="text-boton-acento">*</span></th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Nivel participación</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Estado observado</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">Observación</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">
                        <i class="ph-bold ph-bell" title="Seguimiento"></i>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B5A3]/20">
                @foreach($actividad->participantesActivos as $p)
                @php
                    $pId     = $p->id;
                    $pAm     = optional($p->adultoMayor);
                    $datosRow = $asistencias[$pId] ?? [
                        'estado_asistencia'     => $p->estado_asistencia ?? 'INSCRITO',
                        'nivel_participacion'   => $p->nivel_participacion ?? 'NO_APLICA',
                        'estado_observado'      => $p->estado_observado ?? '',
                        'observacion_individual'=> $p->observacion_individual ?? '',
                        'requiere_seguimiento'  => (bool) ($p->requiere_seguimiento ?? false),
                    ];
                    $tieneAlerta    = isset($alertas[$pId]);
                    $esPreocupante  = in_array($datosRow['estado_observado'], ['AISLADO','IRRITABLE','DESORIENTADO']);
                    $esFalto        = in_array($datosRow['estado_asistencia'], ['FALTO','JUSTIFICADO']);
                    $esAsistio      = $datosRow['estado_asistencia'] === 'ASISTIO';
                    $esNivelBajo    = $datosRow['nivel_participacion'] === 'BAJA';

                    // Clase de fila según asistencia
                    $rowCls = match($datosRow['estado_asistencia']) {
                        'ASISTIO'     => 'bg-estado-exitoBg/10',
                        'FALTO'       => 'bg-estado-peligroBg/15',
                        'JUSTIFICADO' => 'bg-estado-advertenciaBg/20',
                        default       => '',
                    };
                    if ($tieneAlerta || $datosRow['requiere_seguimiento']) {
                        $rowCls = 'bg-estado-peligroBg/25';
                    }
                @endphp
                <tr wire:key="asist-{{ $pId }}" class="{{ $rowCls }} transition">
                    {{-- Adulto --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            @if($tieneAlerta || $datosRow['requiere_seguimiento'])
                            <i class="ph-bold ph-warning-circle text-boton-acento shrink-0"
                               title="Requiere seguimiento o atención"></i>
                            @endif
                            <div class="min-w-0">
                                <p class="font-black text-titulo truncate max-w-[150px]">
                                    {{ $pAm->ap_paterno ?? '—' }} {{ $pAm->nombres ?? '' }}
                                </p>
                                <p class="text-[10px] text-apoyo">{{ $p->cod_am }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Asistencia --}}
                    <td class="px-4 py-3">
                        @php
                            $asistCls = match($datosRow['estado_asistencia']) {
                                'ASISTIO'     => 'border-estado-exitoBorde',
                                'FALTO'       => 'border-borde-focus',
                                'JUSTIFICADO' => 'border-estado-advertenciaBorde',
                                default       => 'border-borde-suave',
                            };
                        @endphp
                        <select wire:model.live="asistencias.{{ $pId }}.estado_asistencia"
                            class="w-full rounded-lg border {{ $asistCls }} bg-fondo-app px-2 py-1.5 text-xs font-bold text-titulo focus:border-borde-focus focus:outline-none transition">
                            <option value="INSCRITO">Inscrito</option>
                            <option value="ASISTIO">✓ Asistió</option>
                            <option value="FALTO">✗ Faltó</option>
                            <option value="JUSTIFICADO">~ Justificado</option>
                        </select>
                        @error("asistencias.{$pId}.estado_asistencia")
                        <p class="{{ $errCls }}">{{ $message }}</p>
                        @enderror
                    </td>

                    {{-- Nivel participación --}}
                    <td class="px-4 py-3">
                        @php
                            $nivCls = match($datosRow['nivel_participacion']) {
                                'ALTA'  => 'border-estado-exitoBorde',
                                'BAJA'  => 'border-borde-focus',
                                'MEDIA' => 'border-estado-advertenciaBorde',
                                default => 'border-borde-suave',
                            };
                        @endphp
                        <select wire:model="asistencias.{{ $pId }}.nivel_participacion"
                            class="w-full rounded-lg border {{ $nivCls }} bg-fondo-app px-2 py-1.5 text-xs font-bold text-titulo focus:border-borde-focus focus:outline-none transition"
                            {{ $esFalto ? 'disabled' : '' }}>
                            <option value="NO_APLICA">No aplica</option>
                            <option value="ALTA">Alta</option>
                            <option value="MEDIA">Media</option>
                            <option value="BAJA">Baja</option>
                        </select>
                        @if($esNivelBajo && $esAsistio)
                        <span class="mt-0.5 inline-flex items-center gap-0.5 text-[9px] font-bold text-estado-advertencia">
                            <i class="ph-bold ph-trend-down text-[9px]"></i> Baja participación
                        </span>
                        @endif
                    </td>

                    {{-- Estado observado --}}
                    <td class="px-4 py-3">
                        @php
                            $obsCls = $esPreocupante ? 'border-borde-focus' : 'border-borde-suave';
                        @endphp
                        <select wire:model="asistencias.{{ $pId }}.estado_observado"
                            class="w-full rounded-lg border {{ $obsCls }} bg-fondo-app px-2 py-1.5 text-xs font-bold text-titulo focus:border-borde-focus focus:outline-none transition">
                            <option value="">Sin registro</option>
                            <option value="ACTIVO">Activo</option>
                            <option value="TRANQUILO">Tranquilo</option>
                            <option value="COLABORADOR">Colaborador</option>
                            <option value="AISLADO">⚠ Aislado</option>
                            <option value="IRRITABLE">⚠ Irritable</option>
                            <option value="CANSADO">Cansado</option>
                            <option value="DESORIENTADO">⚠ Desorientado</option>
                        </select>
                        @if($esPreocupante)
                        <span class="mt-0.5 inline-flex items-center gap-0.5 text-[9px] font-bold text-boton-acento">
                            <i class="ph-bold ph-warning text-[9px]"></i> Requiere atención
                        </span>
                        @endif
                    </td>

                    {{-- Observación --}}
                    <td class="px-4 py-3">
                        <input wire:model="asistencias.{{ $pId }}.observacion_individual"
                            type="text" maxlength="200"
                            placeholder="Nota breve..."
                            class="w-full rounded-lg border border-borde-suave bg-fondo-app px-2 py-1.5 text-xs font-bold text-titulo focus:border-borde-focus focus:outline-none" />
                    </td>

                    {{-- Seguimiento --}}
                    <td class="px-4 py-3 text-center">
                        <label class="relative inline-flex cursor-pointer items-center justify-center">
                            <input wire:model="asistencias.{{ $pId }}.requiere_seguimiento"
                                type="checkbox"
                                class="h-4 w-4 rounded border-borde-suave text-boton-acento cursor-pointer" />
                        </label>
                        @if($datosRow['requiere_seguimiento'])
                        <p class="mt-0.5 text-[9px] font-bold text-boton-acento">Seguimiento</p>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pie de tabla: nota + guardar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-borde-suave px-5 py-4">
        <p class="text-[10px] font-bold text-apoyo">
            <i class="ph-bold ph-info mr-1"></i>
            FALTÓ o JUSTIFICADO → nivel de participación será NO_APLICA automáticamente.
        </p>
        @can('actividades.editar')
        <button wire:click="guardarAsistencia" x-data
            @click="window.SwalAmandita.fire({
                title: '¿Guardar registro de asistencia?',
                text: 'Se actualizará la asistencia de todos los participantes.',
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Sí, guardar', cancelButtonText: 'Cancelar'
            }).then(r => r.isConfirmed && $wire.guardarAsistencia())"
            class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2.5 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition"
            wire:loading.attr="disabled" wire:loading.class="opacity-70">
            <i class="ph-bold ph-floppy-disk text-sm" wire:loading.remove wire:target="guardarAsistencia"></i>
            <i class="ph-bold ph-circle-notch animate-spin text-sm" wire:loading wire:target="guardarAsistencia"></i>
            <span wire:loading.remove wire:target="guardarAsistencia">Guardar asistencia</span>
            <span wire:loading wire:target="guardarAsistencia">Guardando...</span>
        </button>
        @endcan
    </div>
</section>

@endif {{-- fin empty asistencias --}}
@endif {{-- fin viewMode asistencia --}}

</div>{{-- /max-w-7xl --}}

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL DETALLE ACTIVIDAD --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalDetalle && $detalleActividad)
@php
    $da = $detalleActividad;
    $daE = \App\Models\ActividadAdulto::normalizarEstado($da->estado ?? '');
@endphp
<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="relative w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex items-start justify-between p-5">
            <div>
                <h2 class="text-lg font-extrabold text-titulo">
                    {{ $da->nombre ?? optional($da->tipoActividad)->tipo ?? '—' }}
                </h2>
                <p class="mt-0.5">
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $daE['clase'] }}">
                        {{ $daE['etiqueta'] }}
                    </span>
                </p>
            </div>
            <button wire:click="cerrarModales"
                class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-titulo transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
        <div class="space-y-3 px-5 pb-6">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-borde-suave bg-fondo-app p-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Fecha</p>
                    <p class="mt-1 text-sm font-bold text-titulo">{{ $da->fecha?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="rounded-xl border border-borde-suave bg-fondo-app p-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Hora</p>
                    <p class="mt-1 text-sm font-bold text-titulo">
                        {{ $da->hora ? substr($da->hora, 0, 5) : '—' }}
                        @if($da->hora_fin) — {{ substr($da->hora_fin, 0, 5) }}@endif
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-borde-suave bg-fondo-app p-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Participantes</p>
                    <p class="mt-1 text-sm font-bold text-titulo">{{ $da->total_participantes ?? $da->participantesActivos->count() }}</p>
                </div>
                @if($da->nivel_cumplimiento)
                <div class="rounded-xl border border-borde-suave bg-fondo-app p-3">
                    <p class="text-[10px] font-bold uppercase text-apoyo">Cumplimiento</p>
                    <p class="mt-1 text-sm font-bold text-titulo">{{ $da->nivel_cumplimiento }}</p>
                </div>
                @endif
            </div>
            @if($da->resultado_general)
            <div class="rounded-xl border border-borde-suave bg-fondo-app p-3">
                <p class="text-[10px] font-bold uppercase text-apoyo">Resultado general</p>
                <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">{{ $da->resultado_general }}</p>
            </div>
            @endif
            <div class="flex justify-end border-t border-borde-suave pt-4">
                <button wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-5 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL CERRAR ACTIVIDAD --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalCerrar && $actividad)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1.5 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
        <div class="flex items-start justify-between p-5">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-estado-exitoBorde bg-estado-exitoBg px-2.5 py-0.5 text-[10px] font-bold uppercase text-estado-exito">
                    <i class="ph-bold ph-lock-simple text-xs"></i> Cerrar y evaluar
                </span>
                <h2 class="mt-2 text-xl font-extrabold text-titulo">Cerrar actividad</h2>
                <p class="mt-0.5 text-xs font-bold text-apoyo">
                    {{ $actividad->nombre ?? optional($actividad->tipoActividad)->tipo ?? '' }}
                    · {{ $actividad->fecha?->format('d/m/Y') }}
                </p>
            </div>
            <button wire:click="cerrarModales"
                class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-titulo transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
        <form wire:submit.prevent="cerrarActividad" class="space-y-4 px-5 pb-6">
            <div>
                <label class="{{ $labelCls }}">Resultado general <span class="text-boton-acento">*</span></label>
                <textarea wire:model="resultadoGeneral" rows="3" maxlength="2000"
                    placeholder="Describa el resultado de la actividad..."
                    class="{{ $inputCls }} resize-none py-2.5"></textarea>
                @error('resultadoGeneral') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Nivel de cumplimiento <span class="text-boton-acento">*</span></label>
                <select wire:model="nivelCumplimiento" class="{{ $inputCls }} py-2.5">
                    <option value="NO_EVALUADO">No evaluado</option>
                    <option value="ALTO">Alto</option>
                    <option value="MEDIO">Medio</option>
                    <option value="BAJO">Bajo</option>
                </select>
                @error('nivelCumplimiento') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Incidencias <span class="text-apoyo normal-case">(opcional)</span></label>
                <textarea wire:model="incidencias" rows="2" maxlength="1000"
                    placeholder="Eventos o situaciones durante la actividad..."
                    class="{{ $inputCls }} resize-none py-2.5"></textarea>
                @error('incidencias') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Recomendaciones <span class="text-apoyo normal-case">(opcional)</span></label>
                <textarea wire:model="recomendaciones" rows="2" maxlength="1000"
                    placeholder="Sugerencias para próximas actividades..."
                    class="{{ $inputCls }} resize-none py-2.5"></textarea>
                @error('recomendaciones') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>
            <div class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3">
                <p class="text-[11px] font-bold text-estado-advertencia">
                    <i class="ph-bold ph-warning mr-1"></i>
                    Al cerrar la actividad, su estado cambiará a <strong>EVALUADA</strong> y no podrá modificarse sin permisos de administrador.
                </p>
            </div>
            <div class="flex items-center justify-between border-t border-borde-suave pt-4">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-5 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-5 py-2 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg active:scale-95"
                    wire:loading.attr="disabled" wire:loading.class="opacity-70">
                    <i class="ph-bold ph-lock-simple text-sm" wire:loading.remove wire:target="cerrarActividad"></i>
                    <i class="ph-bold ph-circle-notch animate-spin text-sm" wire:loading wire:target="cerrarActividad"></i>
                    <span wire:loading.remove wire:target="cerrarActividad">Confirmar cierre</span>
                    <span wire:loading wire:target="cerrarActividad">Cerrando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

</div>
