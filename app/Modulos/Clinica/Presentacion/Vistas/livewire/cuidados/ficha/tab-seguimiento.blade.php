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
    <header class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Registro longitudinal</p>
                <h2 id="seguimiento-title" class="mt-1 text-xl font-bold text-[var(--rm-text-primary)]">Seguimiento clínico</h2>
                <p class="mt-1 text-sm text-[var(--rm-text-secondary)]">Información consolidada únicamente desde registros clínicos persistidos.</p>
            </div>
            @can('atenciones.crear')
                <button type="button" wire:click="abrirModalSeguimiento"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--rm-action-primary)] px-4 py-2.5 text-sm font-bold text-white hover:bg-[var(--rm-action-primary-hover)]">
                    <i class="ph-bold ph-plus-circle"></i>
                    Registrar seguimiento
                </button>
            @endcan
        </div>
    </header>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--rm-text-secondary)]">Eventos registrados</p>
            <p class="mt-2 text-2xl font-bold text-[var(--rm-text-primary)]">{{ $this->historialCronologico->count() }}</p>
            <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">Cronología clínica disponible</p>
        </article>
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--rm-text-secondary)]">Signos vitales</p>
            <p class="mt-2 text-2xl font-bold text-[var(--rm-text-primary)]">{{ $signosRegistrados }}</p>
            <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">Controles persistidos</p>
        </article>
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--rm-text-secondary)]">Adherencia registrada</p>
            <p class="mt-2 text-2xl font-bold text-[var(--rm-text-primary)]">{{ $resumen['total_admin'] > 0 ? $resumen['adherencia_pct'].'%' : 'Sin datos' }}</p>
            <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">{{ $resumen['total_admin'] > 0 ? $resumen['admin_ok'].' de '.$resumen['total_admin'].' administraciones' : 'No hay administraciones evaluables' }}</p>
        </article>
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-[var(--rm-text-secondary)]">Cuidados ejecutados</p>
            <p class="mt-2 text-2xl font-bold text-[var(--rm-text-primary)]">{{ $resumen['total_tareas'] > 0 ? $resumen['cumplimiento_pct'].'%' : 'Sin datos' }}</p>
            <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">{{ $resumen['total_tareas'] > 0 ? $resumen['tareas_realizadas'].' de '.$resumen['total_tareas'].' tareas' : 'No hay tareas evaluables' }}</p>
        </article>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-heartbeat text-lg text-[var(--rm-action-primary)]"></i>
                <h3 class="font-bold text-[var(--rm-text-primary)]">Último control de signos</h3>
            </div>
            @if($ultimoSigno)
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Presión arterial</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimoSigno->presion_arterial ? $ultimoSigno->presion_arterial.' mmHg' : 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Frecuencia cardíaca</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimoSigno->frecuencia_cardiaca !== null ? $ultimoSigno->frecuencia_cardiaca.' lpm' : 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Saturación</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimoSigno->saturacion !== null ? $ultimoSigno->saturacion.'%' : 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Temperatura</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimoSigno->temperatura !== null ? $ultimoSigno->temperatura.' °C' : 'No registrada' }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-[var(--rm-text-secondary)]">Fecha: {{ $ultimoSigno->fecha_hora?->format('d/m/Y H:i') ?? $ultimoSigno->created_at?->format('d/m/Y H:i') ?? 'No registrada' }}</p>
            @else
                <p class="mt-4 rounded-xl bg-[var(--rm-surface-soft)] p-4 text-sm text-[var(--rm-text-secondary)]">No existen controles de signos vitales para este residente.</p>
            @endif
        </article>

        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-person-simple-walk text-lg text-[var(--rm-action-primary)]"></i>
                <h3 class="font-bold text-[var(--rm-text-primary)]">Última valoración funcional</h3>
            </div>
            @if($ultimaFuncional)
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Barthel</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimaFuncional->barthel_total !== null ? $ultimaFuncional->barthel_total.'/100' : 'No registrado' }}</dd></div>
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Katz</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimaFuncional->katz_total !== null ? $ultimaFuncional->katz_total : 'No registrado' }}</dd></div>
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Dependencia</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimaFuncional->nivel_dependencia ?: 'No registrada' }}</dd></div>
                    <div><dt class="text-xs text-[var(--rm-text-secondary)]">Riesgo de caída</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $ultimaFuncional->riesgo_caida ?: 'No registrado' }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-[var(--rm-text-secondary)]">Fecha: {{ $ultimaFuncional->fecha_valoracion ? \Carbon\Carbon::parse($ultimaFuncional->fecha_valoracion)->format('d/m/Y') : ($ultimaFuncional->created_at?->format('d/m/Y') ?? 'No registrada') }}</p>
            @else
                <p class="mt-4 rounded-xl bg-[var(--rm-surface-soft)] p-4 text-sm text-[var(--rm-text-secondary)]">No existe una valoración funcional registrada.</p>
            @endif
        </article>
    </div>

    <article class="rm-chart-card rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h3 class="font-bold text-[var(--rm-text-primary)]">Cronología clínica</h3>
                <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">Los filtros no alteran ni infieren el contenido de los registros.</p>
            </div>
            <x-ui.filter-bar as="div" class="rm-filter-bar grid gap-2 sm:grid-cols-3">
                <label class="text-xs font-semibold text-[var(--rm-text-secondary)]">Tipo
                    <select wire:model.live="historialFiltroTipo" class="mt-1 w-full rounded-lg border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-sm text-[var(--rm-text-primary)]">
                        <option value="TODOS">Todos</option><option value="SIGNOS">Signos vitales</option><option value="MEDICACION">Medicación</option><option value="CUIDADOS">Cuidados</option><option value="SEGUIMIENTO">Seguimientos</option><option value="VALORACIONES">Valoraciones</option><option value="PASES">Pases de turno</option><option value="ALERTAS">Alertas</option><option value="INCIDENTES">Incidentes</option>
                    </select>
                </label>
                <label class="text-xs font-semibold text-[var(--rm-text-secondary)]">Desde
                    <input type="date" wire:model.live="historialFechaDesde" class="mt-1 w-full rounded-lg border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-sm text-[var(--rm-text-primary)]">
                </label>
                <label class="text-xs font-semibold text-[var(--rm-text-secondary)]">Hasta
                    <input type="date" wire:model.live="historialFechaHasta" class="mt-1 w-full rounded-lg border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-sm text-[var(--rm-text-primary)]">
                </label>
            </x-ui.filter-bar>
        </div>

        <div class="mt-5 space-y-3">
            @forelse($cronologia as $evento)
                <article class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-[var(--rm-action-primary-soft)] px-2.5 py-1 text-[11px] font-bold uppercase text-[var(--rm-action-primary-ink)] dark:bg-blue-950/50 dark:text-blue-200">{{ $evento['tipo_label'] }}</span>
                                @if(!empty($evento['estado_badge']))<span class="text-xs font-bold text-[var(--rm-text-secondary)]">{{ $evento['estado_badge'] }}</span>@endif
                            </div>
                            <h4 class="mt-2 font-bold text-[var(--rm-text-primary)]">{{ $evento['titulo'] }}</h4>
                            <p class="mt-1 whitespace-pre-line text-sm text-[var(--rm-text-secondary)]">{{ $evento['descripcion'] ?: 'Sin detalle registrado.' }}</p>
                            <p class="mt-2 text-xs text-[var(--rm-text-secondary)]">Responsable: {{ $evento['responsable'] ?: 'No registrado' }}</p>
                        </div>
                        <time class="shrink-0 text-xs font-semibold text-[var(--rm-text-secondary)]">{{ !empty($evento['fecha']) ? \Carbon\Carbon::parse($evento['fecha'])->format('d/m/Y') : 'Fecha no registrada' }}{{ !empty($evento['hora']) ? ' · '.$evento['hora'] : '' }}</time>
                    </div>
                </article>
                        @empty
                <x-ui.empty-state
                    icon="ph-folder-open"
                    title="Sin registros para los filtros seleccionados"
                    description="Aquí se muestran los registros confirmados del residente." />
            @endforelse
        </div>
    </article>
</section>
