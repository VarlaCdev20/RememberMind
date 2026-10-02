<div class="rm-page-layout font-sans space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] p-5 sm:p-6 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]">
                    <i class="ph-bold ph-chart-donut text-lg"></i>
                </span>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--rm-action-primary-ink)]">Continuidad asistencial</p>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--rm-text-primary)]">Reportes de Enfermería</h1>
            <p class="text-xs text-[var(--rm-text-secondary)]">{{ $esSuperAdmin ? 'Actividad institucional y evolución individual por periodo.' : 'Actividad real del turno y evolución individual por periodo.' }}</p>
        </div>
        <div>
            <a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold">
                <i class="ph-bold ph-arrow-left text-sm"></i>
                <span>{{ $esSuperAdmin ? 'Volver al resumen global' : 'Volver a Mi turno' }}</span>
            </a>
        </div>
    </header>

    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm grid gap-3 md:grid-cols-3">
        <label class="text-xs font-bold text-[var(--rm-text-secondary)]">Desde
            <input wire:model.live="desde" type="date" class="mt-1 rm-input w-full text-xs">
        </label>
        <label class="text-xs font-bold text-[var(--rm-text-secondary)]">Hasta
            <input wire:model.live="hasta" type="date" class="mt-1 rm-input w-full text-xs">
        </label>
        <label class="text-xs font-bold text-[var(--rm-text-secondary)]">Residente
            <select wire:model.live="codResidente" class="mt-1 rm-select w-full text-xs">
                <option value="">{{ $esSuperAdmin ? 'Todos los residentes' : 'Todos los asignados' }}</option>
                @foreach($pacientes as $p)
                    <option value="{{ $p->cod_residente }}">{{ $p->ap_paterno }}, {{ $p->nombres }}</option>
                @endforeach
            </select>
        </label>
    </section>

    <section class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
        @foreach(['cuidados'=>'Cuidados','signos'=>'Controles','dosis'=>'Dosis administradas','omisiones'=>'Omisiones','incidentes'=>'Incidentes','alertas_abiertas'=>'Alertas abiertas'] as $k=>$e)
            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm">
                <p class="text-2xl font-bold text-[var(--rm-text-primary)]">{{ $indicadores[$k] }}</p>
                <p class="mt-1 text-xs font-bold text-[var(--rm-text-secondary)]">{{ $e }}</p>
            </div>
        @endforeach
    </section>

    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-[var(--rm-text-primary)]">Evolución de cuidados</h2>
                <p class="text-xs text-[var(--rm-text-secondary)]">Ingesta, hidratación y dolor calculados desde registros firmados.</p>
            </div>
            @if($codResidente)
                <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $codResidente, 'tab' => 'historial']) }}" class="text-xs font-bold text-[var(--rm-action-primary-ink)] hover:underline">Abrir registros originales &rarr;</a>
            @endif
        </div>
        <div class="mt-5 grid min-h-48 grid-cols-7 items-end gap-3 overflow-x-auto md:grid-cols-14 py-2">
            @forelse($tendencia as $dia)
                <div class="flex min-w-12 flex-col items-center gap-1" title="{{ $dia['fecha'] }} · Alimentación {{ $dia['alimentacion'] ?? 'S/D' }}% · Hidratación {{ $dia['hidratacion'] }} ml · Dolor {{ $dia['dolor'] ?? 'S/D' }}">
                    <span class="text-[10px] font-bold text-[var(--rm-text-secondary)]">{{ $dia['alimentacion'] !== null ? $dia['alimentacion'].'%' : 'S/D' }}</span>
                    <div class="flex h-32 w-full items-end rounded-lg bg-[var(--rm-surface-soft)] p-1">
                        <div class="w-full rounded-md bg-[var(--rm-action-primary)] transition-all duration-300" style="height: {{ max(3, (int) ($dia['alimentacion'] ?? 0)) }}%"></div>
                    </div>
                    <span class="text-[10px] font-semibold text-[var(--rm-text-muted)]">{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m') }}</span>
                </div>
            @empty
                <p class="col-span-full self-center text-center text-sm text-[var(--rm-text-muted)] py-8">Registre cuidados para visualizar la tendencia.</p>
            @endforelse
        </div>
        @if($tendencia->isNotEmpty())
            <div class="mt-4 rm-table-container overflow-x-auto rounded-xl border border-[var(--rm-border-soft)]">
                <table class="rm-data-table rm-table w-full text-left text-xs">
                    <thead class="bg-[var(--rm-surface-soft)] font-bold text-[var(--rm-text-primary)]">
                        <tr>
                            <th class="p-3">Fecha</th>
                            <th class="p-3">Alimentación</th>
                            <th class="p-3">Hidratación</th>
                            <th class="p-3">Dolor medio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--rm-border-soft)]">
                        @foreach($tendencia as $dia)
                            <tr class="hover:bg-[var(--rm-surface-soft)]/50 transition">
                                <td class="p-3 font-medium text-[var(--rm-text-primary)]">{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td>
                                <td class="p-3 text-[var(--rm-text-secondary)]">{{ $dia['alimentacion'] !== null ? $dia['alimentacion'].'%' : 'Sin dato' }}</td>
                                <td class="p-3 text-[var(--rm-text-secondary)]">{{ $dia['hidratacion'] }} ml</td>
                                <td class="p-3 text-[var(--rm-text-secondary)]">{{ $dia['dolor'] ?? 'Sin dato' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
            <h2 class="text-base sm:text-lg font-bold text-[var(--rm-text-primary)]">Cuidados registrados</h2>
            <div class="mt-4 max-h-[32rem] space-y-2 overflow-y-auto pr-1">
                @forelse($cuidados as $r)
                    <article class="rounded-xl bg-[var(--rm-surface-soft)] p-3 border border-[var(--rm-border-soft)]">
                        <div class="flex justify-between gap-3">
                            <p class="text-xs font-black text-[var(--rm-text-primary)]">{{ $r->adultoMayor?->nombres }} {{ $r->adultoMayor?->apellido_paterno }} · {{ $r->intervencion?->nombre ?? 'Cuidado' }}</p>
                            <time class="text-[11px] text-[var(--rm-text-muted)] shrink-0">{{ ($r->fecha_hora_ejecucion ?? $r->fecha_hora_programada)?->format('d/m H:i') }}</time>
                        </div>
                        <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">{{ $r->observacion ?: ($r->intervencion?->descripcion ?? 'Sin observación') }}</p>
                    </article>
                @empty
                    <p class="text-sm text-[var(--rm-text-muted)] py-6 text-center">Sin actividad en el periodo.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
            <h2 class="text-base sm:text-lg font-bold text-[var(--rm-text-primary)]">Incidentes y seguimiento</h2>
            <div class="mt-4 max-h-[32rem] space-y-2 overflow-y-auto pr-1">
                @forelse($incidentes as $i)
                    <article class="rounded-xl border border-[var(--rm-warning)]/30 bg-[var(--rm-warning-soft)] p-3">
                        <div class="flex justify-between gap-3">
                            <p class="text-xs font-black text-[var(--rm-text-primary)]">{{ $i->adultoMayor?->nombres }} · {{ str_replace('_',' ',$i->tipo) }}</p>
                            <time class="text-[11px] text-[var(--rm-text-muted)] shrink-0">{{ $i->fecha_hora->format('d/m H:i') }}</time>
                        </div>
                        <p class="mt-1 text-xs text-[var(--rm-text-secondary)]">{{ $i->descripcion }}</p>
                    </article>
                @empty
                    <p class="text-sm text-[var(--rm-text-muted)] py-6 text-center">Sin incidentes en el periodo.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>