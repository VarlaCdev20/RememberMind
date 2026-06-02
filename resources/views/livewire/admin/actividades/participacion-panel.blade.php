@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20';
    $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1.5';
    $errCls   = 'mt-1 text-[10px] font-bold text-boton-acento';

    $badgeAsistencia = fn(string $e) => match($e) {
        'ASISTIO'     => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
        'FALTO'       => 'border-borde-focus bg-estado-peligroBg text-boton-acento',
        'JUSTIFICADO' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
        default       => 'border-borde-suave bg-fondo-panel text-apoyo',
    };
    $labelAsistencia = fn(string $e) => match($e) {
        'ASISTIO'     => 'Asistió',
        'FALTO'       => 'Faltó',
        'JUSTIFICADO' => 'Justificado',
        default       => 'Inscrito',
    };
    $badgeNivel = fn(?string $n) => match($n) {
        'ALTA'  => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
        'MEDIA' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
        'BAJA'  => 'border-borde-focus bg-estado-peligroBg text-boton-acento',
        default => 'border-borde-suave bg-fondo-panel text-meta',
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
        <div>
            @if($viewMode === 'participantes' && $actividad)
                <button wire:click="volverALista"
                    class="mb-2 inline-flex items-center gap-1.5 text-xs font-bold text-apoyo hover:text-boton-acento transition">
                    <i class="ph-bold ph-arrow-left text-sm"></i> Volver a actividades
                </button>
                <h1 class="text-2xl font-black tracking-tight text-titulo">
                    {{ $actividad->nombre ?? optional($actividad->tipoActividad)->tipo ?? 'Actividad' }}
                </h1>
                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs font-bold text-apoyo">
                    @php $ne = \App\Models\ActividadAdulto::normalizarEstado($actividad->estado ?? ''); @endphp
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $ne['clase'] }}">{{ $ne['etiqueta'] }}</span>
                    <span><i class="ph-bold ph-calendar-blank mr-1"></i>{{ $actividad->fecha?->format('d/m/Y') }}</span>
                    @if($actividad->hora)<span><i class="ph-bold ph-clock mr-1"></i>{{ substr($actividad->hora, 0, 5) }}</span>@endif
                    @if($actividad->lugar)<span><i class="ph-bold ph-map-pin mr-1"></i>{{ $actividad->lugar }}</span>@endif
                    @if($actividad->cupo_maximo)<span><i class="ph-bold ph-users mr-1"></i>Cupo: {{ $actividad->cupo_maximo }}</span>@endif
                </div>
            @else
                <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
                    <i class="ph-bold ph-users-four text-sm"></i>
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </span>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">Participación en actividades</h1>
                <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                    Gestione los participantes de cada actividad institucional.
                </p>
            @endif
        </div>
        <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
            @if($viewMode === 'participantes' && $actividad)
                @if(! $actividad->estaCancelada())
                    @can('actividades.crear')
                    <button wire:click="abrirAgregarParticipante"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-inverso shadow-sm transition hover:shadow-md active:scale-95">
                        <i class="ph-bold ph-user-plus text-sm"></i> Agregar participante
                    </button>
                    @endcan
                @endif
            @else
                <a href="{{ route('admin.actividades.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-4 py-2.5 text-xs font-bold text-apoyo transition hover:text-titulo">
                    <i class="ph-bold ph-calendar-check text-sm"></i> Ver actividades
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ══ VISTA: LISTA DE ACTIVIDADES ══════════════════════════════════════════ --}}
@if($viewMode === 'lista')

{{-- Métricas --}}
<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @php
    $cards = [
        ['label' => 'Actividades totales',   'valor' => $stats['total'],           'icono' => 'ph-calendar-check',  'cls' => 'text-titulo'],
        ['label' => 'Con participantes',      'valor' => $stats['con_participantes'],'icono' => 'ph-users-four',      'cls' => 'text-estado-exito'],
        ['label' => 'Inscritos en total',     'valor' => $stats['total_inscritos'], 'icono' => 'ph-user-check',      'cls' => 'text-estado-advertencia'],
        ['label' => 'Requieren seguimiento',  'valor' => $stats['requieren_seguimiento'], 'icono' => 'ph-warning-circle', 'cls' => 'text-boton-acento'],
    ];
    @endphp
    @foreach($cards as $c)
    <article class="relative overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">{{ $c['label'] }}</p>
            <i class="ph-bold {{ $c['icono'] }} text-lg {{ $c['cls'] }}"></i>
        </div>
        <p class="mt-3 text-3xl font-black {{ $c['cls'] }}">{{ number_format($c['valor']) }}</p>
    </article>
    @endforeach
</section>

{{-- Filtros --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[180px] flex-1">
            <label class="{{ $labelCls }}">Buscar actividad</label>
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
                <input wire:model.live.debounce.300ms="searchActividad" type="text"
                    placeholder="Nombre o tipo..." class="{{ $inputCls }} pl-8" />
            </div>
        </div>
        <div class="min-w-[150px] flex-1">
            <label class="{{ $labelCls }}">Tipo</label>
            <select wire:model.live="filtroTipoAct" class="{{ $inputCls }}">
                <option value="">Todos</option>
                @foreach($tipos as $t)
                    <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[130px] flex-1">
            <label class="{{ $labelCls }}">Estado</label>
            <select wire:model.live="filtroEstadoAct" class="{{ $inputCls }}">
                <option value="">Todos</option>
                <option value="PROGRAMADA">Programada</option>
                <option value="EN_CURSO">En curso</option>
                <option value="REALIZADA">Realizada</option>
                <option value="EVALUADA">Evaluada</option>
                <option value="CANCELADA">Cancelada</option>
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Desde</label>
            <input wire:model.live="filtroFechaDesde" type="date" class="{{ $inputCls }}" />
        </div>
        <div class="min-w-[130px]">
            <label class="{{ $labelCls }}">Hasta</label>
            <input wire:model.live="filtroFechaHasta" type="date" class="{{ $inputCls }}" />
        </div>
        <button wire:click="limpiarFiltros"
            class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-xs font-bold text-apoyo hover:text-boton-acento transition">
            <i class="ph-bold ph-x text-xs"></i> Limpiar
        </button>
    </div>
</section>

{{-- Tabla actividades --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="border-b border-borde-suave px-5 py-3.5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-calendar-check mr-2 text-apoyo"></i>Actividades
            </h2>
            <span class="text-[10px] font-bold text-apoyo">{{ $actividades->total() }} registro(s)</span>
        </div>
    </div>
    <div class="overflow-x-auto" wire:loading.class="opacity-50" wire:target="searchActividad,filtroTipoAct,filtroEstadoAct,filtroFechaDesde,filtroFechaHasta">
        @if($actividades->isEmpty())
            <div class="flex flex-col items-center gap-3 py-12 text-center">
                <i class="ph-bold ph-calendar-blank text-3xl text-apoyo"></i>
                <p class="text-sm font-bold text-apoyo">No se encontraron actividades.</p>
            </div>
        @else
        <table class="w-full min-w-[700px] text-xs">
            <thead>
                <tr class="border-b border-borde-suave">
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Actividad / Tipo</th>
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Fecha</th>
                    <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Estado</th>
                    <th class="px-4 pb-2.5 pt-3 text-center font-black uppercase tracking-[0.12em] text-apoyo">Participantes</th>
                    <th class="px-4 pb-2.5 pt-3 text-right font-black uppercase tracking-[0.12em] text-apoyo">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B5A3]/20">
                @foreach($actividades as $act)
                @php $ne = \App\Models\ActividadAdulto::normalizarEstado($act->estado ?? ''); @endphp
                <tr wire:key="act-{{ $act->cod_act_adul }}" class="hover:bg-fondo-panel transition">
                    <td class="px-4 py-3">
                        <p class="font-black text-titulo">{{ $act->nombre ?? optional($act->tipoActividad)->tipo ?? '—' }}</p>
                        @if($act->nombre)
                        <p class="text-[10px] text-apoyo">{{ optional($act->tipoActividad)->tipo }}</p>
                        @endif
                        @if($act->lugar)
                        <p class="text-[10px] text-apoyo"><i class="ph-bold ph-map-pin mr-0.5"></i>{{ $act->lugar }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-apoyo whitespace-nowrap">
                        {{ $act->fecha?->format('d/m/Y') ?? '—' }}
                        @if($act->hora)
                        <p class="text-[10px]">{{ substr($act->hora, 0, 5) }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $ne['clase'] }}">
                            {{ $ne['etiqueta'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-fondo-panel text-xs font-black text-titulo">
                            {{ $act->total_participantes ?? 0 }}
                        </span>
                        @if($act->cupo_maximo)
                        <p class="text-[10px] text-apoyo">/ {{ $act->cupo_maximo }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if(! $act->estaCancelada())
                        <button wire:click="seleccionarActividad({{ $act->cod_act_adul }})"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-boton-acento px-3 py-1.5 text-[11px] font-bold text-inverso transition hover:shadow-md active:scale-95">
                            <i class="ph-bold ph-users text-xs"></i> Participantes
                        </button>
                        @else
                        <span class="text-[10px] font-bold text-apoyo">Cancelada</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($actividades->hasPages())
        <div class="border-t border-borde-suave px-5 py-3">{{ $actividades->links() }}</div>
        @endif
        @endif
    </div>
</section>

@endif {{-- fin viewMode lista --}}

{{-- ══ VISTA: PARTICIPANTES DE UNA ACTIVIDAD ════════════════════════════════ --}}
@if($viewMode === 'participantes' && $actividad)

{{-- Alertas institucionales --}}
@if(! empty($alertas))
<div class="flex items-start gap-3 rounded-2xl border border-borde-focus bg-estado-peligroBg px-4 py-3">
    <i class="ph-bold ph-warning-circle mt-0.5 shrink-0 text-lg text-boton-acento"></i>
    <div class="min-w-0">
        <p class="text-xs font-bold text-boton-acento">Indicadores de seguimiento detectados</p>
        <p class="text-[11px] font-bold text-apoyo mt-0.5">
            Hay participantes que requieren seguimiento institucional. Revise los registros marcados con
            <i class="ph-bold ph-warning text-boton-acento"></i>.
        </p>
    </div>
</div>
@endif

{{-- Buscador participantes --}}
<div class="flex items-center gap-3">
    <div class="relative flex-1 max-w-sm">
        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
        <input wire:model.live.debounce.300ms="searchParticipante" type="text"
            placeholder="Buscar participante por nombre o CI..."
            class="{{ $inputCls }} pl-8" />
    </div>
    <span class="text-xs font-bold text-apoyo">{{ $participantes->count() }} participante(s)</span>
</div>

{{-- Tabla de participantes --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    @if($participantes->isEmpty())
        <div class="flex flex-col items-center gap-3 py-12 text-center">
            <i class="ph-bold ph-users text-3xl text-apoyo"></i>
            <p class="text-sm font-bold text-apoyo">No hay participantes registrados en esta actividad.</p>
            @if(! $actividad->estaCancelada())
            @can('actividades.crear')
            <button wire:click="abrirAgregarParticipante"
                class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso transition hover:shadow-md">
                <i class="ph-bold ph-user-plus"></i> Agregar el primero
            </button>
            @endcan
            @endif
        </div>
    @else
    <table class="w-full min-w-[700px] text-xs">
        <thead>
            <tr class="border-b border-borde-suave">
                <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Adulto mayor</th>
                <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Asistencia</th>
                <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Participación</th>
                <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Estado observado</th>
                <th class="px-4 pb-2.5 pt-3 text-left font-black uppercase tracking-[0.12em] text-apoyo">Seguimiento</th>
                <th class="px-4 pb-2.5 pt-3 text-right font-black uppercase tracking-[0.12em] text-apoyo">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#C7B5A3]/20">
            @foreach($participantes as $p)
            @php
                $am = optional($p->adultoMayor);
                $tieneAlerta = isset($alertas[$p->id]);
                $flagsAlerta = $alertas[$p->id] ?? [];
            @endphp
            <tr wire:key="part-{{ $p->id }}" class="hover:bg-fondo-panel transition {{ $tieneAlerta ? 'bg-estado-peligroBg/30' : '' }}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        @if($tieneAlerta)
                        <i class="ph-bold ph-warning-circle text-boton-acento shrink-0" title="Requiere seguimiento"></i>
                        @endif
                        <div class="min-w-0">
                            <p class="font-black text-titulo truncate max-w-[160px]">
                                {{ $am->ap_paterno ?? '—' }} {{ $am->nombres ?? '' }}
                            </p>
                            <p class="text-[10px] text-apoyo">{{ $p->cod_am }}</p>
                        </div>
                    </div>
                    @if(in_array('faltas_consecutivas', $flagsAlerta))
                    <span class="mt-0.5 inline-flex items-center gap-1 rounded-full border border-borde-focus bg-estado-peligroBg px-1.5 py-0.5 text-[9px] font-bold text-boton-acento">
                        <i class="ph-bold ph-x-circle text-[9px]"></i> 3 faltas consecutivas
                    </span>
                    @endif
                    @if(in_array('participacion_baja', $flagsAlerta))
                    <span class="mt-0.5 inline-flex items-center gap-1 rounded-full border border-estado-advertenciaBorde bg-estado-advertenciaBg px-1.5 py-0.5 text-[9px] font-bold text-estado-advertencia">
                        <i class="ph-bold ph-trend-down text-[9px]"></i> Participación baja
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $badgeAsistencia($p->estado_asistencia) }}">
                        {{ $labelAsistencia($p->estado_asistencia) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $badgeNivel($p->nivel_participacion) }}">
                        {{ $p->nivel_participacion ?? 'N/A' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-apoyo">
                    @if($p->estado_observado)
                    @php
                        $esPreocupante = in_array($p->estado_observado, ['AISLADO', 'IRRITABLE', 'DESORIENTADO']);
                    @endphp
                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $esPreocupante ? 'border-borde-focus bg-estado-peligroBg text-boton-acento' : 'border-borde-suave bg-fondo-panel text-apoyo' }}">
                        @if($esPreocupante)<i class="ph-bold ph-warning text-[9px]"></i>@endif
                        {{ $p->estado_observado }}
                    </span>
                    @else
                    <span class="text-apoyo">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($p->requiere_seguimiento)
                    <span class="inline-flex items-center gap-1 rounded-full border border-borde-focus bg-estado-peligroBg px-2 py-0.5 text-[10px] font-bold text-boton-acento">
                        <i class="ph-bold ph-bell text-[9px]"></i> Sí
                    </span>
                    @else
                    <span class="text-[10px] text-apoyo">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        <button wire:click="abrirDetalleParticipante({{ $p->id }})"
                            title="Ver detalle"
                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-suave text-apoyo hover:bg-fondo-panel transition">
                            <i class="ph-bold ph-eye text-xs"></i>
                        </button>
                        @can('actividades.editar')
                        @if(! $actividad->estaCancelada())
                        <button wire:click="abrirEditarParticipante({{ $p->id }})"
                            title="Editar"
                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia hover:bg-estado-advertenciaBg transition">
                            <i class="ph-bold ph-pencil text-xs"></i>
                        </button>
                        @endif
                        @endcan
                        @can('actividades.anular')
                        @if(! $actividad->estaCancelada())
                        <button type="button" title="Quitar participante" x-data
                            @click="window.SwalAmandita.fire({
                                title: '¿Quitar participante?',
                                text: 'El registro se conservará en el historial institucional.',
                                icon: 'warning', showCancelButton: true,
                                confirmButtonText: 'Sí, quitar', cancelButtonText: 'No'
                            }).then(r => { if (r.isConfirmed) $wire.quitarParticipante({{ $p->id }}) })"
                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento hover:bg-estado-peligroBg transition">
                            <i class="ph-bold ph-x text-xs"></i>
                        </button>
                        @endif
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</section>

@endif {{-- fin viewMode participantes --}}

</div>{{-- /max-w-7xl --}}

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL — AGREGAR PARTICIPANTE --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalAgregar)
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background:rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
                    <i class="ph-bold ph-user-plus text-lg"></i>
                </span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Participante</p>
                    <h3 class="text-sm font-bold text-titulo">Agregar participante</h3>
                </div>
            </div>
            <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
        <form wire:submit.prevent="agregarParticipante" class="space-y-4 p-5">

            {{-- AUTOCOMPLETE adulto mayor --}}
            <div>
                <label class="{{ $labelCls }}">Adulto mayor <span class="text-boton-acento">*</span></label>

                {{-- Si ya hay uno seleccionado, mostramos la tarjeta --}}
                @if($codAmAgregar && $nombreAdultoSeleccionado)
                <div class="flex items-center justify-between rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-user-check text-estado-exito"></i>
                        <div>
                            <p class="text-xs font-black text-titulo">{{ $nombreAdultoSeleccionado }}</p>
                            <p class="text-[10px] text-apoyo">{{ $codAmAgregar }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="limpiarAdultoAgregar"
                        class="flex h-6 w-6 items-center justify-center rounded-lg text-estado-exito hover:bg-estado-exitoBg transition"
                        title="Cambiar adulto mayor">
                        <i class="ph-bold ph-x text-xs"></i>
                    </button>
                </div>
                @else
                {{-- Campo de búsqueda con dropdown --}}
                <div class="relative" x-data="{ open: false }"
                     @click.away="open = false"
                     @keydown.escape="open = false">
                    <div class="relative">
                        <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo pointer-events-none"></i>
                        <input
                            wire:model.live.debounce.300ms="searchAdultoAgregar"
                            @input="open = true"
                            @focus="open = $el.value.length >= 2"
                            type="text"
                            placeholder="Escriba nombre, apellido o CI (mín. 2 caracteres)..."
                            autocomplete="off"
                            class="{{ $inputCls }} pl-8" />
                        @if($searchAdultoAgregar)
                        <button type="button"
                            wire:click="$set('searchAdultoAgregar', '')"
                            @click="open = false"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-apoyo hover:text-titulo transition">
                            <i class="ph-bold ph-x text-xs"></i>
                        </button>
                        @endif
                    </div>

                    {{-- Dropdown resultados --}}
                    <div x-show="open && {{ $adultos->isNotEmpty() ? 'true' : 'false' }}"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-borde-suave bg-fondo-panel shadow-xl">
                        @foreach($adultos as $a)
                        @php
                            $nombreCompleto = trim("{$a->ap_paterno} {$a->ap_materno}, {$a->nombres}");
                        @endphp
                        <button type="button"
                            wire:click="seleccionarAdultoAgregar('{{ $a->cod_am }}', '{{ addslashes($nombreCompleto) }}')"
                            @click="open = false"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition hover:bg-fondo-panel focus:bg-fondo-panel">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-fondo-panel text-xs font-black text-titulo">
                                {{ strtoupper(substr($a->ap_paterno, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-black text-titulo truncate">{{ $nombreCompleto }}</p>
                                <p class="text-[10px] text-apoyo">{{ $a->cod_am }} · CI: {{ $a->ci }}</p>
                            </div>
                        </button>
                        @endforeach
                    </div>

                    {{-- Sin resultados --}}
                    @if(strlen($searchAdultoAgregar) >= 2 && $adultos->isEmpty())
                    <div class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 shadow-xl">
                        <p class="text-xs font-bold text-apoyo">
                            <i class="ph-bold ph-info mr-1"></i>
                            Sin coincidencias. El adulto puede ya estar inscrito o no existe.
                        </p>
                    </div>
                    @endif
                </div>
                @endif

                @error('codAmAgregar') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Estado de asistencia <span class="text-boton-acento">*</span></label>
                    <select wire:model.live="estadoAsistenciaAgregar" class="{{ $inputCls }}">
                        <option value="INSCRITO">Inscrito (pendiente)</option>
                        <option value="ASISTIO">Asistió</option>
                        <option value="FALTO">Faltó</option>
                        <option value="JUSTIFICADO">Justificado</option>
                    </select>
                    @error('estadoAsistenciaAgregar') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Nivel de participación</label>
                    <select wire:model="nivelParticipacionAgregar" class="{{ $inputCls }}"
                        {{ in_array($estadoAsistenciaAgregar, ['FALTO', 'JUSTIFICADO']) ? 'disabled' : '' }}>
                        <option value="NO_APLICA">No aplica</option>
                        <option value="ALTA">Alta</option>
                        <option value="MEDIA">Media</option>
                        <option value="BAJA">Baja</option>
                    </select>
                    @error('nivelParticipacionAgregar') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="{{ $labelCls }}">Estado observado</label>
                <select wire:model="estadoObservadoAgregar" class="{{ $inputCls }}">
                    <option value="">Sin registro</option>
                    <option value="ACTIVO">Activo</option>
                    <option value="TRANQUILO">Tranquilo</option>
                    <option value="COLABORADOR">Colaborador</option>
                    <option value="AISLADO">Aislado</option>
                    <option value="IRRITABLE">Irritable</option>
                    <option value="CANSADO">Cansado</option>
                    <option value="DESORIENTADO">Desorientado</option>
                </select>
                @error('estadoObservadoAgregar') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="{{ $labelCls }}">Observación individual <span class="text-apoyo normal-case">(opcional)</span></label>
                <textarea wire:model="observacionIndividualAgregar" rows="2" maxlength="1000"
                    placeholder="Notas sobre la participación de este adulto mayor..."
                    class="{{ $inputCls }} resize-none"></textarea>
                @error('observacionIndividualAgregar') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>

            <label class="flex cursor-pointer items-center gap-2.5">
                <input wire:model="requiereSeguimientoAgregar" type="checkbox"
                    class="h-4 w-4 rounded border-borde-suave text-boton-acento" />
                <span class="text-xs font-bold text-titulo">Requiere seguimiento institucional</span>
            </label>

            <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
                <button type="button" wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm hover:shadow-md active:scale-95 transition">
                    <i class="ph-bold ph-user-plus text-sm"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL — DETALLE / EDITAR PARTICIPANTE --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@if($modalParticipante && $detalleParticipante)
@php $dp = $detalleParticipante; $dpAm = optional($dp->adultoMayor); @endphp
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8"
     style="background:rgba(47,62,92,0.55)" wire:click.self="cerrarModales">
    <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
        <div class="h-1 bg-gradient-to-r from-[#8DA280] via-[#D9A05B] to-[#E27D60]"></div>
        <div class="flex items-center justify-between border-b border-borde-suave px-5 py-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Participante</p>
                <h3 class="text-sm font-bold text-titulo">
                    {{ $dpAm->ap_paterno ?? '—' }} {{ $dpAm->nombres ?? '' }}
                </h3>
                <p class="text-[10px] font-bold text-apoyo">{{ $dp->cod_am }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(! $editandoParticipante)
                @can('actividades.editar')
                <button wire:click="$set('editandoParticipante', true)"
                    class="flex h-8 w-8 items-center justify-center rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia hover:bg-estado-advertenciaBg transition">
                    <i class="ph-bold ph-pencil text-sm"></i>
                </button>
                @endcan
                @endif
                <button wire:click="cerrarModales"
                    class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo hover:text-boton-acento transition">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>
        </div>

        @if($editandoParticipante)
        <form wire:submit.prevent="guardarParticipante" class="space-y-4 p-5">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $labelCls }}">Asistencia <span class="text-boton-acento">*</span></label>
                    <select wire:model.live="estadoAsistenciaEdit" class="{{ $inputCls }}">
                        <option value="INSCRITO">Inscrito</option>
                        <option value="ASISTIO">Asistió</option>
                        <option value="FALTO">Faltó</option>
                        <option value="JUSTIFICADO">Justificado</option>
                    </select>
                    @error('estadoAsistenciaEdit') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Nivel participación</label>
                    <select wire:model="nivelParticipacionEdit" class="{{ $inputCls }}"
                        {{ in_array($estadoAsistenciaEdit, ['FALTO', 'JUSTIFICADO']) ? 'disabled' : '' }}>
                        <option value="NO_APLICA">No aplica</option>
                        <option value="ALTA">Alta</option>
                        <option value="MEDIA">Media</option>
                        <option value="BAJA">Baja</option>
                    </select>
                    @error('nivelParticipacionEdit') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="{{ $labelCls }}">Estado observado</label>
                <select wire:model="estadoObservadoEdit" class="{{ $inputCls }}">
                    <option value="">Sin registro</option>
                    <option value="ACTIVO">Activo</option>
                    <option value="TRANQUILO">Tranquilo</option>
                    <option value="COLABORADOR">Colaborador</option>
                    <option value="AISLADO">Aislado</option>
                    <option value="IRRITABLE">Irritable</option>
                    <option value="CANSADO">Cansado</option>
                    <option value="DESORIENTADO">Desorientado</option>
                </select>
                @error('estadoObservadoEdit') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Observación</label>
                <textarea wire:model="observacionIndividualEdit" rows="2" maxlength="1000"
                    class="{{ $inputCls }} resize-none"></textarea>
                @error('observacionIndividualEdit') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
            </div>
            <label class="flex cursor-pointer items-center gap-2.5">
                <input wire:model="requiereSeguimientoEdit" type="checkbox" class="h-4 w-4 rounded border-borde-suave" />
                <span class="text-xs font-bold text-titulo">Requiere seguimiento</span>
            </label>
            <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
                <button type="button" wire:click="$set('editandoParticipante', false)"
                    class="rounded-xl border border-borde-suave px-4 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cancelar edición
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-estado-advertenciaBg border border-estado-advertenciaBorde px-5 py-2 text-xs font-bold text-estado-advertencia hover:bg-estado-advertenciaBg transition active:scale-95">
                    <i class="ph-bold ph-floppy-disk text-sm"></i> Guardar
                </button>
            </div>
        </form>
        @else
        <div class="space-y-3 p-5">
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Asistencia</p>
                    <p class="mt-1">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $badgeAsistencia($dp->estado_asistencia) }}">
                            {{ $labelAsistencia($dp->estado_asistencia) }}
                        </span>
                    </p>
                </div>
                <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Participación</p>
                    <p class="mt-1">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $badgeNivel($dp->nivel_participacion) }}">
                            {{ $dp->nivel_participacion ?? 'N/A' }}
                        </span>
                    </p>
                </div>
            </div>
            @if($dp->estado_observado)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Estado observado</p>
                <p class="mt-1 text-sm font-bold text-titulo">{{ $dp->estado_observado }}</p>
            </div>
            @endif
            @if($dp->observacion_individual)
            <div class="rounded-xl border border-borde-suave bg-fondo-app px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-apoyo">Observación</p>
                <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">{{ $dp->observacion_individual }}</p>
            </div>
            @endif
            @if($dp->requiere_seguimiento)
            <div class="flex items-center gap-2 rounded-xl border border-borde-focus bg-estado-peligroBg px-4 py-2.5">
                <i class="ph-bold ph-warning-circle text-boton-acento"></i>
                <p class="text-xs font-bold text-boton-acento">Requiere seguimiento institucional</p>
            </div>
            @endif
            <div class="flex justify-end border-t border-borde-suave pt-4">
                <button wire:click="cerrarModales"
                    class="rounded-xl border border-borde-suave px-5 py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    Cerrar
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

</div>
