@php
    $signosRecientes = $adultoMayor->signosVitales
        ->sortByDesc(fn ($signo) => $signo->fecha_hora?->timestamp ?? 0)
        ->take(7)
        ->values();
    $ultimoSigno = $signosRecientes->first();
    $ultimaValoracion = $adultoMayor->valoracionesFuncionales
        ->sortByDesc(fn ($valoracion) => $valoracion->fecha_hora?->timestamp ?? $valoracion->created_at?->timestamp ?? 0)
        ->first();
    $plan = $adultoMayor->planCuidadoActivo;
    $intervenciones = $plan?->intervenciones?->whereIn('estado', ['ACTIVA', 'ACTIVO']) ?? collect();
    $ejecucionesHoy = $adultoMayor->ejecucionesCuidado()
        ->whereDate('fecha_hora_programada', today())
        ->get();
    $ejecucionesRealizadas = $ejecucionesHoy->whereIn('estado', ['REALIZADA', 'EJECUTADA', 'COMPLETADA'])->count();
    $administracionesHoy = $adultoMayor->administracionesMedicacion
        ->filter(fn ($administracion) => $administracion->fecha_hora_programada?->isToday());
    $alertasActivas = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
    $metricas = collect([
        ['label' => 'Presión arterial', 'valor' => $ultimoSigno?->presion_arterial, 'unidad' => 'mmHg', 'icon' => 'ph-heartbeat'],
        ['label' => 'Frecuencia cardíaca', 'valor' => $ultimoSigno?->frecuencia_cardiaca, 'unidad' => 'lpm', 'icon' => 'ph-activity'],
        ['label' => 'Saturación O₂', 'valor' => $ultimoSigno?->saturacion, 'unidad' => '%', 'icon' => 'ph-drop'],
        ['label' => 'Temperatura', 'valor' => $ultimoSigno?->temperatura, 'unidad' => '°C', 'icon' => 'ph-thermometer'],
        ['label' => 'Frecuencia respiratoria', 'valor' => $ultimoSigno?->frecuencia_respiratoria, 'unidad' => 'rpm', 'icon' => 'ph-wind'],
        ['label' => 'Glucemia', 'valor' => $ultimoSigno?->glucosa, 'unidad' => 'mg/dL', 'icon' => 'ph-drop-half-bottom'],
    ]);
@endphp

<div class="space-y-5">
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Controles registrados</p>
            <p class="mt-1 text-2xl font-black text-[var(--rm-text-title)]">{{ $adultoMayor->signosVitales->count() }}</p>
            <p class="text-[10px] text-[var(--rm-text-muted)]">Signos vitales disponibles</p>
        </article>
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Cuidados de hoy</p>
            <p class="mt-1 text-2xl font-black text-[var(--rm-text-title)]">{{ $ejecucionesRealizadas }}/{{ $ejecucionesHoy->count() }}</p>
            <p class="text-[10px] text-[var(--rm-text-muted)]">Ejecuciones reales</p>
        </article>
        <article class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Medicaciones de hoy</p>
            <p class="mt-1 text-2xl font-black text-[var(--rm-text-title)]">{{ $administracionesHoy->where('resultado', 'ADMINISTRADA')->count() }}/{{ $administracionesHoy->count() }}</p>
            <p class="text-[10px] text-[var(--rm-text-muted)]">Administraciones registradas</p>
        </article>
        <article class="rounded-2xl border {{ $alertasActivas->isNotEmpty() ? 'border-rose-300 bg-rose-50/60' : 'border-[var(--rm-border)] bg-[var(--rm-surface)]' }} p-4">
            <p class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">Alertas activas</p>
            <p class="mt-1 text-2xl font-black {{ $alertasActivas->isNotEmpty() ? 'text-rose-700' : 'text-[var(--rm-text-title)]' }}">{{ $alertasActivas->count() }}</p>
            <p class="text-[10px] text-[var(--rm-text-muted)]">Según registros vigentes</p>
        </article>
    </section>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <div>
                        <h3 class="text-sm font-black text-[var(--rm-text-title)]">Último control de signos vitales</h3>
                        <p class="text-xs text-[var(--rm-text-muted)]">{{ $ultimoSigno?->fecha_hora?->format('d/m/Y H:i') ?? 'No existen controles registrados' }}</p>
                    </div>
                    <button type="button" wire:click="cambiarTab('signos')" class="rm-btn-secondary h-8 px-3 text-xs">Ver signos</button>
                </div>

                @if($ultimoSigno)
                    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3">
                        @foreach($metricas as $metrica)
                            <article class="rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-alt)] p-3">
                                <div class="flex items-center gap-2 text-[10px] font-black uppercase text-[var(--rm-text-muted)]">
                                    <i class="ph-bold {{ $metrica['icon'] }} text-sm"></i>
                                    <span>{{ $metrica['label'] }}</span>
                                </div>
                                @if($metrica['valor'] !== null && $metrica['valor'] !== '')
                                    <p class="mt-1 text-lg font-black text-[var(--rm-text-title)]">{{ $metrica['valor'] }} <span class="text-[10px] font-bold text-[var(--rm-text-muted)]">{{ $metrica['unidad'] }}</span></p>
                                @else
                                    <p class="mt-1 text-sm font-bold text-slate-500">No registrado</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    @if($ultimoSigno->observacion)
                        <p class="mt-4 rounded-xl border border-blue-200 bg-blue-50/60 p-3 text-xs text-blue-900"><span class="font-black">Observación:</span> {{ $ultimoSigno->observacion }}</p>
                    @endif
                @else
                    <div class="mt-4 rounded-xl border border-dashed border-[var(--rm-border)] p-8 text-center">
                        <i class="ph ph-chart-line-down text-3xl text-slate-400"></i>
                        <p class="mt-2 text-sm font-black text-[var(--rm-text-title)]">Sin datos clínicos para graficar</p>
                        <p class="text-xs text-[var(--rm-text-muted)]">Registre signos vitales para habilitar el resumen y las tendencias.</p>
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <div>
                        <h3 class="text-sm font-black text-[var(--rm-text-title)]">Controles recientes</h3>
                        <p class="text-xs text-[var(--rm-text-muted)]">Últimos registros persistidos, sin completar valores ausentes.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-700">{{ $signosRecientes->count() }} registros</span>
                </div>

                @if($signosRecientes->isNotEmpty())
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-xs">
                            <thead class="text-[10px] uppercase text-[var(--rm-text-muted)]">
                                <tr><th class="px-2 py-2">Fecha y hora</th><th class="px-2 py-2">PA</th><th class="px-2 py-2">FC</th><th class="px-2 py-2">SpO₂</th><th class="px-2 py-2">Temp.</th><th class="px-2 py-2">FR</th></tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--rm-border-soft)]">
                                @foreach($signosRecientes as $signo)
                                    <tr>
                                        <td class="px-2 py-2.5 font-bold text-[var(--rm-text-title)]">{{ $signo->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                                        <td class="px-2 py-2.5">{{ $signo->presion_arterial ?? '—' }}</td>
                                        <td class="px-2 py-2.5">{{ $signo->frecuencia_cardiaca ?? '—' }}</td>
                                        <td class="px-2 py-2.5">{{ $signo->saturacion !== null ? $signo->saturacion.'%' : '—' }}</td>
                                        <td class="px-2 py-2.5">{{ $signo->temperatura !== null ? $signo->temperatura.' °C' : '—' }}</td>
                                        <td class="px-2 py-2.5">{{ $signo->frecuencia_respiratoria ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 rounded-xl border border-dashed border-[var(--rm-border)] p-6 text-center text-xs text-[var(--rm-text-muted)]">No existen controles recientes.</p>
                @endif
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <h3 class="text-sm font-black text-[var(--rm-text-title)]">Plan de cuidados</h3>
                    <button type="button" wire:click="cambiarTab('cuidados')" class="text-xs font-bold text-blue-700">Ver plan</button>
                </div>
                @if($plan)
                    <div class="mt-3 space-y-2 text-xs">
                        <p class="font-black text-[var(--rm-text-title)]">{{ $plan->nombre ?: 'Plan activo sin nombre' }}</p>
                        @if($plan->objetivo)<p class="text-[var(--rm-text-body)]">{{ $plan->objetivo }}</p>@endif
                        <dl class="grid grid-cols-2 gap-2 pt-2">
                            <div class="rounded-lg bg-[var(--rm-surface-alt)] p-2"><dt class="text-[10px] text-[var(--rm-text-muted)]">Intervenciones</dt><dd class="font-black">{{ $intervenciones->count() }}</dd></div>
                            <div class="rounded-lg bg-[var(--rm-surface-alt)] p-2"><dt class="text-[10px] text-[var(--rm-text-muted)]">Estado</dt><dd class="font-black">{{ $plan->estado }}</dd></div>
                        </dl>
                    </div>
                @else
                    <p class="mt-3 rounded-xl border border-dashed border-[var(--rm-border)] p-4 text-center text-xs text-[var(--rm-text-muted)]">No existe un plan de cuidados activo.</p>
                @endif
            </section>

            <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
                <h3 class="border-b border-[var(--rm-border)] pb-3 text-sm font-black text-[var(--rm-text-title)]">Última valoración funcional</h3>
                @if($ultimaValoracion)
                    <dl class="mt-3 space-y-2 text-xs">
                        <div class="flex justify-between gap-3"><dt class="text-[var(--rm-text-muted)]">Barthel</dt><dd class="font-black">{{ $ultimaValoracion->barthel_total !== null ? $ultimaValoracion->barthel_total.'/100' : 'No registrado' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-[var(--rm-text-muted)]">Dependencia</dt><dd class="font-black text-right">{{ $ultimaValoracion->nivel_dependencia ?: 'No registrada' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-[var(--rm-text-muted)]">Riesgo de caída</dt><dd class="font-black text-right">{{ $ultimaValoracion->riesgo_caida ?: 'No registrado' }}</dd></div>
                    </dl>
                @else
                    <p class="mt-3 rounded-xl border border-dashed border-[var(--rm-border)] p-4 text-center text-xs text-[var(--rm-text-muted)]">No existe una valoración funcional registrada.</p>
                @endif
            </section>

            <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <h3 class="text-sm font-black text-[var(--rm-text-title)]">Alertas activas</h3>
                    <button type="button" wire:click="cambiarTab('alertas')" class="text-xs font-bold text-blue-700">Ver alertas</button>
                </div>
                @forelse($alertasActivas->take(4) as $alerta)
                    <article class="mt-3 rounded-xl border border-rose-200 bg-rose-50/60 p-3 text-xs">
                        <p class="font-black text-rose-900">{{ $alerta->titulo ?: ($alerta->tipo ?: 'Alerta clínica') }}</p>
                        <p class="mt-1 text-rose-800">{{ $alerta->descripcion ?: 'Sin descripción registrada' }}</p>
                    </article>
                @empty
                    <p class="mt-3 rounded-xl border border-dashed border-[var(--rm-border)] p-4 text-center text-xs text-[var(--rm-text-muted)]">No existen alertas activas registradas.</p>
                @endforelse
            </section>
        </aside>
    </div>
</div>
