@php
    $cronologia = $this->historialFiltrado;
    $resumen = $this->resumenLongitudinal;
    $ultimaFuncional = $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
    $signosRegistrados = $adultoMayor->signosVitales->count();
    $ultimoSigno = $adultoMayor->signosVitales
        ->sortByDesc(fn ($signo) => $signo->fecha_hora ?? $signo->created_at)
        ->first();
@endphp

<section class="space-y-5" aria-labelledby="seguimiento-title">
    <header class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-parrafo">Registro longitudinal</p>
                <h2 id="seguimiento-title" class="mt-1 text-xl font-black text-titulo">Seguimiento clínico</h2>
                <p class="mt-1 text-sm text-parrafo">Información consolidada únicamente desde registros clínicos persistidos.</p>
            </div>
            @can('atenciones.crear')
                <button type="button" wire:click="abrirModalSeguimiento"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800">
                    <i class="ph-bold ph-plus-circle"></i>
                    Registrar seguimiento
                </button>
            @endcan
        </div>
    </header>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <article class="rounded-2xl border border-borde bg-fondo-card p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-parrafo">Eventos registrados</p>
            <p class="mt-2 text-2xl font-black text-titulo">{{ $this->historialCronologico->count() }}</p>
            <p class="mt-1 text-xs text-parrafo">Cronología clínica disponible</p>
        </article>
        <article class="rounded-2xl border border-borde bg-fondo-card p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-parrafo">Signos vitales</p>
            <p class="mt-2 text-2xl font-black text-titulo">{{ $signosRegistrados }}</p>
            <p class="mt-1 text-xs text-parrafo">Controles persistidos</p>
        </article>
        <article class="rounded-2xl border border-borde bg-fondo-card p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-parrafo">Adherencia registrada</p>
            <p class="mt-2 text-2xl font-black text-titulo">{{ $resumen['total_admin'] > 0 ? $resumen['adherencia_pct'].'%' : 'Sin datos' }}</p>
            <p class="mt-1 text-xs text-parrafo">{{ $resumen['total_admin'] > 0 ? $resumen['admin_ok'].' de '.$resumen['total_admin'].' administraciones' : 'No hay administraciones evaluables' }}</p>
        </article>
        <article class="rounded-2xl border border-borde bg-fondo-card p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-parrafo">Cuidados ejecutados</p>
            <p class="mt-2 text-2xl font-black text-titulo">{{ $resumen['total_tareas'] > 0 ? $resumen['cumplimiento_pct'].'%' : 'Sin datos' }}</p>
            <p class="mt-1 text-xs text-parrafo">{{ $resumen['total_tareas'] > 0 ? $resumen['tareas_realizadas'].' de '.$resumen['total_tareas'].' tareas' : 'No hay tareas evaluables' }}</p>
        </article>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl border border-borde bg-fondo-card p-5">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-heartbeat text-lg text-blue-700"></i>
                <h3 class="font-black text-titulo">Último control de signos</h3>
            </div>
            @if($ultimoSigno)
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-parrafo">Presión arterial</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimoSigno->presion_arterial ? $ultimoSigno->presion_arterial.' mmHg' : 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-parrafo">Frecuencia cardíaca</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimoSigno->frecuencia_cardiaca !== null ? $ultimoSigno->frecuencia_cardiaca.' lpm' : 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-parrafo">Saturación</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimoSigno->saturacion !== null ? $ultimoSigno->saturacion.'%' : 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-parrafo">Temperatura</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimoSigno->temperatura !== null ? $ultimoSigno->temperatura.' °C' : 'No registrada' }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-parrafo">Fecha: {{ $ultimoSigno->fecha_hora?->format('d/m/Y H:i') ?? $ultimoSigno->created_at?->format('d/m/Y H:i') ?? 'No registrada' }}</p>
            @else
                <p class="mt-4 rounded-xl bg-fondo-base p-4 text-sm text-parrafo">No existen controles de signos vitales para este residente.</p>
            @endif
        </article>

        <article class="rounded-2xl border border-borde bg-fondo-card p-5">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-person-simple-walk text-lg text-blue-700"></i>
                <h3 class="font-black text-titulo">Última valoración funcional</h3>
            </div>
            @if($ultimaFuncional)
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs text-parrafo">Barthel</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimaFuncional->barthel_total !== null ? $ultimaFuncional->barthel_total.'/100' : 'No registrado' }}</dd></div>
                    <div><dt class="text-xs text-parrafo">Katz</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimaFuncional->katz_total !== null ? $ultimaFuncional->katz_total : 'No registrado' }}</dd></div>
                    <div><dt class="text-xs text-parrafo">Dependencia</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimaFuncional->nivel_dependencia ?: 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-parrafo">Riesgo de caída</dt><dd class="mt-1 font-bold text-titulo">{{ $ultimaFuncional->riesgo_caida ?: 'No registrado' }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-parrafo">Fecha: {{ $ultimaFuncional->fecha_valoracion ? \Carbon\Carbon::parse($ultimaFuncional->fecha_valoracion)->format('d/m/Y') : ($ultimaFuncional->created_at?->format('d/m/Y') ?? 'No registrada') }}</p>
            @else
                <p class="mt-4 rounded-xl bg-fondo-base p-4 text-sm text-parrafo">No existe una valoración funcional registrada.</p>
            @endif
        </article>
    </div>

    <article class="rounded-2xl border border-borde bg-fondo-card p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h3 class="font-black text-titulo">Cronología clínica</h3>
                <p class="mt-1 text-xs text-parrafo">Los filtros no alteran ni infieren el contenido de los registros.</p>
            </div>
            <div class="rm-filter-bar grid gap-2 sm:grid-cols-3">
                <label class="text-xs font-semibold text-parrafo">Tipo
                    <select wire:model.live="historialFiltroTipo" class="mt-1 w-full rounded-lg border-borde bg-fondo-base text-sm text-titulo">
                        <option value="TODOS">Todos</option><option value="SIGNOS">Signos vitales</option><option value="MEDICACION">Medicación</option><option value="CUIDADOS">Cuidados</option><option value="SEGUIMIENTO">Seguimientos</option><option value="VALORACIONES">Valoraciones</option><option value="PASES">Pases de turno</option><option value="ALERTAS">Alertas</option><option value="INCIDENTES">Incidentes</option>
                    </select>
                </label>
                <label class="text-xs font-semibold text-parrafo">Desde
                    <input type="date" wire:model.live="historialFechaDesde" class="mt-1 w-full rounded-lg border-borde bg-fondo-base text-sm text-titulo">
                </label>
                <label class="text-xs font-semibold text-parrafo">Hasta
                    <input type="date" wire:model.live="historialFechaHasta" class="mt-1 w-full rounded-lg border-borde bg-fondo-base text-sm text-titulo">
                </label>
            </div>
        </div>

        <div class="mt-5 space-y-3">
            @forelse($cronologia as $evento)
                <article class="rounded-xl border border-borde bg-fondo-base p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-black uppercase text-blue-800 dark:bg-blue-950/50 dark:text-blue-200">{{ $evento['tipo_label'] }}</span>
                                @if(!empty($evento['estado_badge']))<span class="text-xs font-bold text-parrafo">{{ $evento['estado_badge'] }}</span>@endif
                            </div>
                            <h4 class="mt-2 font-bold text-titulo">{{ $evento['titulo'] }}</h4>
                            <p class="mt-1 whitespace-pre-line text-sm text-parrafo">{{ $evento['descripcion'] ?: 'Sin detalle registrado.' }}</p>
                            <p class="mt-2 text-xs text-parrafo">Responsable: {{ $evento['responsable'] ?: 'No registrado' }}</p>
                        </div>
                        <time class="shrink-0 text-xs font-semibold text-parrafo">{{ !empty($evento['fecha']) ? \Carbon\Carbon::parse($evento['fecha'])->format('d/m/Y') : 'Fecha no registrada' }}{{ !empty($evento['hora']) ? ' · '.$evento['hora'] : '' }}</time>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-borde p-8 text-center">
                    <i class="ph ph-folder-open text-3xl text-parrafo"></i>
                    <p class="mt-2 font-bold text-titulo">Sin registros para los filtros seleccionados</p>
                    <p class="mt-1 text-sm text-parrafo">No se generan valores ni eventos de demostración.</p>
                </div>
            @endforelse
        </div>
    </article>
</section>
