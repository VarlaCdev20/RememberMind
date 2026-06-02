{{-- TAB: ACTIVIDADES Y PARTICIPACIÓN --}}
{{-- Variables esperadas del controlador:
     $adulto              → AdultoMayor
     $actividadesActivas  → Collection<ActividadAdulto> (directas, no eliminadas)
     $actividadesAnuladas → Collection<ActividadAdulto> (softDeleted)
     $participacionesGrupales → Collection<ActividadParticipante> con actividad.tipoActividad
     $asignaciones        → Collection (voluntarios asignados)
     $idAdulto es $adulto->cod_am
--}}
@php
    $idAdulto           = $adulto->cod_am;
    $totalDirectas      = ($actividadesActivas ?? collect())->count();
    $totalGrupales      = ($participacionesGrupales ?? collect())->count();
    $totalActividades   = $totalDirectas + $totalGrupales;
    $totalAsignaciones  = ($asignaciones ?? collect())->count();

    $badgeAsist = fn(?string $e) => match(strtoupper($e ?? '')) {
        'ASISTIO'     => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
        'FALTO'       => 'border-borde-focus bg-estado-peligroBg text-boton-acento',
        'JUSTIFICADO' => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',
        default       => 'border-borde-suave bg-fondo-panel text-apoyo',
    };
    $labelAsist = fn(?string $e) => match(strtoupper($e ?? '')) {
        'ASISTIO'     => 'Asistió',
        'FALTO'       => 'Faltó',
        'JUSTIFICADO' => 'Justificado',
        default       => 'Inscrito',
    };
@endphp

<section class="space-y-5">

    {{-- ENCABEZADO --}}
    <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-borde-suave px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-parrafo">
                    Participación institucional
                </span>
                <h2 class="mt-1 text-lg font-extrabold text-titulo">
                    Actividades y apoyo voluntario
                </h2>
                <p class="mt-1 text-xs font-bold leading-5 text-apoyo">
                    Registro directo, participación grupal y voluntarios asignados.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                @can('actividades.crear')
                <a href="{{ route('admin.actividades.participacion') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-fondo-panel px-4 py-2.5 text-xs font-bold text-inverso shadow transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-[0.98]">
                    <i class="ph-bold ph-users-four"></i>
                    Gestionar participación grupal
                </a>
                <a href="{{ route('admin.actividades.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-fondo-panel px-4 py-2.5 text-xs font-bold text-inverso shadow transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-[0.98]">
                    <i class="ph-bold ph-calendar-check"></i>
                    Ver todas las actividades
                </a>
                @endcan
            </div>
        </div>

        {{-- Métricas --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Actividades totales</p>
                <p class="mt-2 text-2xl font-black text-parrafo">{{ $totalActividades }}</p>
                <p class="text-[10px] text-apoyo">{{ $totalDirectas }} directas · {{ $totalGrupales }} grupales</p>
            </div>
            <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Voluntarios</p>
                <p class="mt-2 text-2xl font-black text-parrafo">{{ $totalAsignaciones }}</p>
            </div>
            <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Con seguimiento</p>
                @php
                    $conSeguimiento = ($participacionesGrupales ?? collect())->where('requiere_seguimiento', true)->count();
                @endphp
                <p class="mt-2 text-2xl font-black {{ $conSeguimiento > 0 ? 'text-boton-acento' : 'text-parrafo' }}">
                    {{ $conSeguimiento }}
                </p>
            </div>
            <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Estado general</p>
                <p class="mt-2 text-sm font-bold {{ $totalActividades > 0 ? 'text-parrafo' : 'text-apoyo' }}">
                    {{ $totalActividades > 0 ? 'Con actividad registrada' : 'Sin actividad' }}
                </p>
            </div>
        </div>
    </section>

    {{-- ══ PARTICIPACIÓN GRUPAL (actividad_participantes) ══════════════════ --}}
    @if(($participacionesGrupales ?? collect())->isNotEmpty())
    <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center gap-3 border-b border-borde-suave bg-fondo-panel px-6 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                <i class="ph-bold ph-users-four text-xl"></i>
            </div>
            <div>
                <h3 class="text-base font-extrabold text-titulo">Participación en actividades grupales</h3>
                <p class="text-[10px] font-bold text-apoyo uppercase tracking-widest">Inscripciones registradas en el módulo de participación</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-titulo">
                <thead class="bg-fondo-panel text-[10px] uppercase tracking-widest text-apoyo">
                    <tr>
                        <th class="px-6 py-3">Actividad</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Asistencia</th>
                        <th class="px-6 py-3">Participación</th>
                        <th class="px-6 py-3">Estado observado</th>
                        <th class="px-6 py-3">Seguimiento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @foreach($participacionesGrupales as $pg)
                    @php
                        $pgAct  = $pg->actividad;
                        $pgNE   = $pgAct ? \App\Models\ActividadAdulto::normalizarEstado($pgAct->estado ?? '') : [];
                        $esPreoc = in_array($pg->estado_observado, ['AISLADO', 'IRRITABLE', 'DESORIENTADO']);
                    @endphp
                    <tr class="hover:bg-fondo-panel transition {{ $pg->requiere_seguimiento ? 'bg-estado-peligroBg/10' : '' }}">
                        <td class="px-6 py-3">
                            <p class="text-xs font-bold text-titulo">
                                {{ $pgAct?->nombre ?? optional($pgAct?->tipoActividad)->tipo ?? 'Actividad' }}
                            </p>
                            @if($pgAct?->nombre)
                            <p class="text-[10px] text-apoyo">{{ optional($pgAct->tipoActividad)->tipo }}</p>
                            @endif
                            @if($pgAct && $pgNE)
                            <span class="inline-flex items-center rounded-full border px-1.5 py-0.5 text-[9px] font-bold {{ $pgNE['clase'] }}">
                                {{ $pgNE['etiqueta'] }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-xs text-apoyo whitespace-nowrap">
                            {{ $pgAct?->fecha?->format('d/m/Y') ?? '—' }}
                            @if($pgAct?->hora)
                            <p class="text-[10px]">{{ substr($pgAct->hora, 0, 5) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $badgeAsist($pg->estado_asistencia) }}">
                                {{ $labelAsist($pg->estado_asistencia) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $nivCls = match($pg->nivel_participacion) {
                                    'ALTA'  => 'text-estado-exito',
                                    'BAJA'  => 'text-boton-acento',
                                    'MEDIA' => 'text-estado-advertencia',
                                    default => 'text-apoyo',
                                };
                            @endphp
                            <span class="text-xs font-bold {{ $nivCls }}">
                                {{ $pg->nivel_participacion ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @if($pg->estado_observado)
                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $esPreoc ? 'border-borde-focus bg-estado-peligroBg text-boton-acento' : 'border-borde-suave bg-fondo-panel text-apoyo' }}">
                                @if($esPreoc)<i class="ph-bold ph-warning text-[9px]"></i>@endif
                                {{ $pg->estado_observado }}
                            </span>
                            @else
                            <span class="text-apoyo">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if($pg->requiere_seguimiento)
                            <span class="inline-flex items-center gap-1 rounded-full border border-borde-focus bg-estado-peligroBg px-2 py-0.5 text-[10px] font-bold text-boton-acento">
                                <i class="ph-bold ph-bell text-[9px]"></i> Sí
                            </span>
                            @else
                            <span class="text-[10px] text-apoyo">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    {{-- ══ ACTIVIDADES DIRECTAS (actividades_adulto) ════════════════════════ --}}
    <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center justify-between gap-3 border-b border-borde-suave px-6 py-4 bg-fondo-panel">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-calendar-check text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-titulo">Registro directo de actividades</h3>
                    <p class="text-[10px] font-bold text-apoyo uppercase tracking-widest">Actividades asignadas individualmente</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-titulo">
                <thead class="bg-fondo-panel text-[10px] uppercase tracking-widest text-apoyo">
                    <tr>
                        <th class="px-6 py-3">Fecha / Hora</th>
                        <th class="px-6 py-3">Tipo Actividad</th>
                        <th class="px-6 py-3">Observación</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse(($actividadesActivas ?? collect()) as $actividad)
                    @php
                        $actividadObj = is_object($actividad) ? $actividad : null;
                        $estadoAct    = strtoupper(optional($actividadObj)->estado ?? 'PROGRAMADA');
                        $neAct        = \App\Models\ActividadAdulto::normalizarEstado($estadoAct);
                    @endphp
                    <tr class="group transition hover:bg-fondo-panel">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-xs font-bold text-titulo">{{ optional($actividadObj)->fecha ? \Carbon\Carbon::parse($actividadObj->fecha)->format('d/m/Y') : 'N/D' }}</p>
                            <p class="text-[10px] font-bold text-apoyo">{{ optional($actividadObj)->hora ? substr($actividadObj->hora, 0, 5) : '--:--' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-titulo">{{ optional($actividadObj)->tipoActividad->tipo ?? 'Actividad' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-apoyo line-clamp-1">{{ optional($actividadObj)->obs ?? 'Sin detalle' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[9px] font-bold uppercase {{ $neAct['clase'] }}">
                                {{ $neAct['etiqueta'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1.5">
                                @can('actividades.anular')
                                @if($estadoAct !== 'CANCELADA')
                                <form action="{{ Route::has('admin.adultos-mayores.actividades.destroy') ? route('admin.adultos-mayores.actividades.destroy', [$idAdulto, $actividadObj->cod_act_adul ?? '0']) : '#' }}" method="POST"
                                    onsubmit="confirmarAccion(event, 'Anular registro de actividad', 'La participación en esta actividad será anulada del expediente activo.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Anular actividad"
                                        class="rounded-lg bg-boton-acento/5 p-2 text-terracota hover:bg-boton-acento hover:text-inverso transition">
                                        <i class="ph-bold ph-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-xs font-bold text-apoyo">Sin actividades directas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ══ ACTIVIDADES ANULADAS ════════════════════════════════════════════ --}}
    @if(($actividadesAnuladas ?? collect())->isNotEmpty())
    <div class="border-t border-borde-suave pt-2">
        <h4 class="mb-3 px-4 text-xs font-bold uppercase tracking-widest text-terracota/60 flex items-center gap-2">
            <i class="ph-bold ph-x-circle"></i> Historial de Actividades Anuladas
        </h4>
        <div class="overflow-hidden rounded-[24px] border border-borde-suave opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-fondo-panel backdrop-blur-sm shadow-sm mx-1">
            <table class="w-full text-left text-sm text-titulo">
                <thead class="bg-fondo-panel text-[9px] uppercase tracking-widest text-apoyo">
                    <tr>
                        <th class="px-6 py-3">Fecha Anulación</th>
                        <th class="px-6 py-3">Actividad</th>
                        <th class="px-6 py-3 text-right">Restaurar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/20">
                    @foreach($actividadesAnuladas as $actAnu)
                    <tr class="hover:bg-fondo-card/10 transition">
                        <td class="px-6 py-3 text-xs font-bold text-apoyo">
                            {{ $actAnu->deleted_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-[11px] font-bold text-apoyo uppercase">{{ optional($actAnu->tipoActividad)->tipo ?? 'Actividad' }}</p>
                            <p class="text-[10px] font-semibold text-apoyo">Estado anterior: {{ $actAnu->estado }}</p>
                        </td>
                        <td class="px-6 py-3 text-right">
                            @can('actividades.editar')
                            <form action="{{ Route::has('admin.adultos-mayores.actividades.restore') ? route('admin.adultos-mayores.actividades.restore', [$adulto->cod_am, $actAnu->cod_act_adul]) : '#' }}" method="POST"
                                onsubmit="confirmarAccion(event, 'Restaurar actividad', 'El registro de participación volverá a la vista activa del expediente.')">
                                @csrf @method('PATCH')
                                <button type="submit" title="Restaurar"
                                    class="rounded-lg bg-fondo-panel p-1.5 text-parrafo hover:bg-fondo-panel hover:text-inverso transition">
                                    <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ══ VOLUNTARIOS ASIGNADOS ═══════════════════════════════════════════ --}}
    <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center justify-between gap-3 border-b border-borde-suave px-6 py-4 bg-fondo-panel">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-hand-heart text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-titulo">Voluntarios Asignados</h3>
                    <p class="text-[10px] font-bold text-apoyo uppercase tracking-widest">Apoyo y acompañamiento externo</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-titulo">
                <thead class="bg-fondo-panel text-[10px] uppercase tracking-widest text-apoyo">
                    <tr>
                        <th class="px-6 py-3">Voluntario</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Observación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse(($asignaciones ?? collect()) as $asignacion)
                    @php
                        $asignacionObj = is_object($asignacion) ? $asignacion : null;
                        $nombreVol     = optional(optional($asignacionObj)->voluntario)->name ?? 'Voluntario';
                        $estadoAsig    = strtoupper(optional($asignacionObj)->estado ?? 'ACTIVO');
                    @endphp
                    <tr class="group transition hover:bg-fondo-panel">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-xs font-bold text-parrafo">
                                    {{ strtoupper(substr($nombreVol, 0, 1)) }}
                                </div>
                                <span class="text-xs font-bold text-titulo">{{ $nombreVol }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase border border-borde-suave bg-fondo-panel text-apoyo">
                                {{ $estadoAsig }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-[11px] font-semibold text-apoyo line-clamp-1">{{ optional($asignacionObj)->observaciones ?? '—' }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-xs font-bold text-apoyo">Sin voluntarios asignados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</section>
