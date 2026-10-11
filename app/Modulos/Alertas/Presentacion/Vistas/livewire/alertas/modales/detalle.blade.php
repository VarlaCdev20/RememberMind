@php $detalle = $detalle ?? $alertaActiva; @endphp
@if($modalDetalle && $detalle)
@php
 $inicioPendiente = in_array($detalle->estado, ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE'], true);
 $badgeEstadoClass = match($detalle->estado) {
     'ABIERTA' => 'rm-badge-warning',
     'EN_ATENCION' => 'rm-badge-info',
     'CERRADA' => 'rm-badge-success',
     default => 'rm-badge-neutral'
 };
 $estadoTexto = match($detalle->estado) {
     'ABIERTA' => 'ABIERTA',
     'EN_ATENCION' => 'En Atención',
     'CERRADA' => 'Resuelta / Archivada',
     default => $detalle->estado
 };

 $badgeNivelClass = match($detalle->prioridad) {
     'CRITICO' => 'rm-badge-danger',
     'ALTO' => 'rm-badge-warning',
     'MEDIO' => 'rm-badge-info',
     'BAJO' => 'rm-badge-neutral',
     default => 'rm-badge-neutral'
 };
@endphp

<x-ui.drawer-livewire
    wire:model="modalDetalle"
    size="lg"
    close-method="cerrarModales"
    badge="Dossier clínico"
    title="Detalle de la alerta"
    subtitle="Registro asistencial, evolución y trazabilidad de la atención"
    icon="ph-clipboard-text"
    :dismiss-on-backdrop="true">

    <div class="space-y-4 text-xs">
        @if (session()->has('mensaje'))
            <x-ui.callout variant="success">
                {{ session('mensaje') }}
            </x-ui.callout>
        @endif

        <!-- 1. Datos del Residente y Ubicación Física -->
        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] shadow-2xs flex-wrap gap-2.5">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white font-bold text-sm shadow-xs shrink-0">
                    {{ substr($detalle->adultoMayor?->nombres ?? 'A', 0, 1) }}{{ substr($detalle->adultoMayor?->ap_paterno ?? 'M', 0, 1) }}
                </div>
                <div>
                    <h4 class="font-bold text-[var(--rm-text-primary)] text-sm leading-tight">
                        {{ $detalle->adultoMayor?->ap_paterno }} {{ $detalle->adultoMayor?->ap_materno }} {{ $detalle->adultoMayor?->nombres }}
                    </h4>
                    <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-secondary)] mt-0.5">
                        <span>{{ $detalle->adultoMayor?->edad_texto ?? 'Edad no registrada' }}</span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1">
                            <i class="ph ph-map-pin text-[var(--rm-action-primary)]"></i>
                            <span>{{ $detalle->adultoMayor?->ubicacion_texto ?? 'Sin ubicación asignada' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <span class="rm-badge {{ $badgeEstadoClass }}">
                    {{ $estadoTexto }}
                </span>
                <button type="button"
                        wire:click="verGraficos('{{ $detalle->cod_residente }}')"
                        class="rm-btn rm-btn-sm rm-btn-secondary text-xs cursor-pointer shadow-xs">
                    <i class="ph ph-chart-line-up"></i>
                    <span>Evolución</span>
                </button>
                <button type="button"
                        wire:click="verUbicacion('{{ $detalle->cod_residente }}')"
                        class="rm-btn rm-btn-sm rm-btn-secondary text-xs cursor-pointer shadow-xs">
                    <i class="ph ph-bed"></i>
                    <span>Ubicación</span>
                </button>
            </div>
        </div>

        <!-- 2. Resumen Clínico Estructurado -->
        <div class="p-4 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-3 shadow-2xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div class="space-y-0.5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Severidad</span>
                    <span class="rm-badge {{ $badgeNivelClass }}">
                        {{ $detalle->prioridad }}
                    </span>
                </div>
                <div class="space-y-0.5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Canal / Origen</span>
                    <span class="rm-badge rm-badge-neutral text-[10px]">
                        {{ $detalle->modulo }}
                    </span>
                </div>
                <div class="space-y-0.5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Responsable</span>
                    <p class="font-bold text-[var(--rm-text-primary)] text-xs truncate">
                        {{ $detalle->responsable?->usuario?->name ?? 'Sin responsable asignado' }}
                    </p>
                    @if($detalle->turno?->nombre)<p class="text-[10px] text-[var(--rm-text-secondary)]">{{ $detalle->turno->nombre }}</p>@endif
                </div>
                <div class="space-y-0.5">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Detección</span>
                    <p class="font-bold text-[var(--rm-text-primary)] text-xs">
                        {{ $detalle->fecha_hora?->translatedFormat('d M, H:i') }}
                    </p>
                    <p class="text-[10px] text-[var(--rm-text-secondary)] font-mono">{{ $detalle->fecha_hora?->diffForHumans() }}</p>
                </div>
            </div>

            <!-- Motivo clínico -->
            <div class="pt-3 border-t border-[var(--rm-border-soft)]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block mb-1">
                    Motivo y recomendación registrados
                </span>
                <p class="p-3 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-xs text-[var(--rm-text-primary)] leading-relaxed whitespace-pre-line">
                    <strong class="text-[var(--rm-text-primary)]">{{ $detalle->tipo }}:</strong> {{ $detalle->descripcion }}
                </p>
            </div>
        </div>

        @if($signoOrigen)
            <section class="p-4 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-3" aria-label="Registro de signos que originó la alerta">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h5 class="text-sm font-bold text-[var(--rm-text-primary)]">Registro que originó la alerta</h5>
                    <time class="text-xs text-[var(--rm-text-muted)]" datetime="{{ $signoOrigen->fecha_hora?->toIso8601String() }}">{{ $signoOrigen->fecha_hora?->format('d/m/Y H:i') }}</time>
                </div>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                    @foreach([
                        ['Presión arterial', $signoOrigen->presion_sistolica !== null && $signoOrigen->presion_diastolica !== null ? $signoOrigen->presion_sistolica.'/'.$signoOrigen->presion_diastolica.' mmHg' : null],
                        ['Pulso', $signoOrigen->frecuencia_cardiaca !== null ? $signoOrigen->frecuencia_cardiaca.' lpm' : null],
                        ['Respiración', $signoOrigen->frecuencia_respiratoria !== null ? $signoOrigen->frecuencia_respiratoria.' rpm' : null],
                        ['Temperatura', $signoOrigen->temperatura !== null ? $signoOrigen->temperatura.' °C' : null],
                        ['SpO₂', $signoOrigen->saturacion_oxigeno !== null ? $signoOrigen->saturacion_oxigeno.' %' : null],
                        ['Glucemia', $signoOrigen->glucemia !== null ? $signoOrigen->glucemia.' mg/dL' : null],
                    ] as [$nombre, $valor])
                        @if($valor !== null)<div class="rounded-xl bg-[var(--rm-surface-soft)] p-2.5"><dt class="text-[var(--rm-text-muted)]">{{ $nombre }}</dt><dd class="mt-1 font-bold text-[var(--rm-text-primary)]">{{ $valor }}</dd></div>@endif
                    @endforeach
                </dl>
                @can('enfermeria.ver_ficha_paciente')
                    <a class="rm-btn rm-btn-sm rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalle->cod_residente, 'tab' => 'signos']) }}">Ver registro de signos</a>
                @endcan
                @if($puedeRegistrarNuevaMedicion)
                    <a class="rm-btn rm-btn-sm rm-btn-primary" href="{{ route('admin.enfermeria.pacientes', ['residente' => $detalle->cod_residente, 'registrar' => 'signos']) }}">Registrar nueva medición</a>
                @endif
            </section>
        @endif

        @if($evolucionSignos->count() > 1)
            <section class="p-4 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-2" aria-label="Evolución desde la alerta">
                <h5 class="text-sm font-bold text-[var(--rm-text-primary)]">Evolución desde la alerta</h5>
                <ol class="space-y-2">
                    @foreach($evolucionSignos as $lectura)
                        @php
                            $valores = array_filter([
                                $lectura->presion_sistolica !== null && $lectura->presion_diastolica !== null ? 'PA '.$lectura->presion_sistolica.'/'.$lectura->presion_diastolica.' mmHg' : null,
                                $lectura->frecuencia_cardiaca !== null ? 'Pulso '.$lectura->frecuencia_cardiaca.' lpm' : null,
                                $lectura->frecuencia_respiratoria !== null ? 'FR '.$lectura->frecuencia_respiratoria.' rpm' : null,
                                $lectura->temperatura !== null ? 'Temperatura '.$lectura->temperatura.' °C' : null,
                                $lectura->saturacion_oxigeno !== null ? 'SpO₂ '.$lectura->saturacion_oxigeno.' %' : null,
                                $lectura->glucemia !== null ? 'Glucemia '.$lectura->glucemia.' mg/dL' : null,
                            ]);
                        @endphp
                        <li class="flex flex-wrap items-baseline gap-x-3 gap-y-1 rounded-xl bg-[var(--rm-surface-soft)] p-2.5 text-xs">
                            <time class="font-bold text-[var(--rm-text-secondary)]" datetime="{{ $lectura->fecha_hora?->toIso8601String() }}">{{ $lectura->fecha_hora?->format('d/m H:i') }}</time>
                            <span class="text-[var(--rm-text-primary)]">{{ implode(' · ', $valores) }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <!-- 3. Historial de Intervenciones Clínicas (Timeline Canónico) -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)] flex items-center gap-1.5">
                    <i class="ph-bold ph-clock-counter-clockwise text-base text-[var(--rm-action-primary)]"></i>
                    Historial de Intervenciones ({{ $detalle->eventos->count() }})
                </span>
                <span class="text-[11px] text-[var(--rm-text-secondary)] font-mono">Orden cronológico</span>
            </div>

            @php
                $timelineItems = $detalle->eventos->sortBy('fecha_hora')->values()->map(function($acc) {
                    $nombreActor = $acc->usuario?->name ?? 'Profesional Clínico';
                    $fechaFormat = $acc->fecha_hora ? ($acc->fecha_hora->format('H:i') . ' (' . $acc->fecha_hora->format('d/m/Y') . ')') : '';
                    $cambioEstado = ($acc->estado_anterior && $acc->estado_nuevo && $acc->estado_anterior !== $acc->estado_nuevo)
                        ? "{$acc->estado_anterior} → {$acc->estado_nuevo}"
                        : null;
                    return [
                        'id' => $acc->cod_evento_alerta,
                        'actor' => $nombreActor,
                        'tipo' => $acc->tipo_evento,
                        'estado' => $cambioEstado,
                        'fecha' => $fechaFormat,
                        'descripcion' => $acc->descripcion,
                        'variant' => match($acc->tipo_evento) {
                            'CREACION' => 'info',
                            'ATENCION', 'INTERVENCION', 'SEGUIMIENTO' => 'terracota',
                            'CIERRE' => 'success',
                            default => 'default',
                        },
                    ];
                })->toArray();
            @endphp

            <div class="max-h-60 overflow-y-auto pr-1">
                <x-patterns.timeline :items="$timelineItems" emptyMessage="No se han registrado intervenciones aún en esta alerta." />
            </div>
        </div>

        <!-- 4. Formulario para Registrar Nueva Intervención (si la alerta no está cerrada) -->
        @if($detalle->puedeCerrarse() || $inicioPendiente)
            @canany(['alertas.gestionar', 'alertas.seguimiento'])
                <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-2 shadow-2xs">
                    @if($inicioPendiente && ! $detalle->eventos->contains('tipo_evento', 'INTERVENCION'))
                        <p class="text-xs font-semibold text-[var(--rm-danger)]">La alerta todavía no tiene una intervención registrada.</p>
                    @endif
                    <label for="nuevaIntervencionInput" class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)]">
                        {{ $inicioPendiente ? '¿Qué acción se realizó?' : 'Añadir seguimiento' }}
                    </label>
                    <div class="flex gap-2">
                        <textarea
                               id="nuevaIntervencionInput"
                               wire:model="accion"
                               rows="3"
                               placeholder="Describe únicamente la acción realmente realizada."
                               class="rm-input flex-1 text-xs"></textarea>

                        <button type="button"
                                wire:click="guardarAccion"
                                wire:loading.attr="disabled"
                                class="rm-btn rm-btn-sm rm-btn-primary shrink-0">
                            <span wire:loading.remove wire:target="guardarAccion">{{ $inicioPendiente ? 'Registrar intervención' : 'Añadir seguimiento' }}</span>
                            <span wire:loading wire:target="guardarAccion">Guardando...</span>
                        </button>
                    </div>
                    @error('accion')
                        <p class="text-xs font-bold text-[var(--rm-danger)] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endcanany
        @endif
    </div>

    <x-slot:footer>
        <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                @if($detalle && $detalle->puedeCerrarse())
                    @canany(['alertas.gestionar', 'alertas.cerrar'])
                        <button type="button"
                                wire:click="cerrarAlerta('{{ $detalle->cod_alerta }}')"
                                class="rm-btn rm-btn-sm rm-btn-secondary cursor-pointer shadow-xs">
                            <i class="ph ph-archive-box text-base"></i>
                            <span>Cerrar y Archivar</span>
                        </button>
                    @endcanany
                @endif
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                <button type="button"
                        wire:click="cerrarModales"
                        class="rm-btn rm-btn-secondary rm-btn-sm cursor-pointer">
                    Cerrar panel
                </button>

                @if($detalle && $inicioPendiente)
                    @canany(['alertas.gestionar', 'alertas.seguimiento'])
                        <button type="button"
                                wire:click="atenderAlerta('{{ $detalle->cod_alerta }}')"
                                class="rm-btn rm-btn-primary rm-btn-sm cursor-pointer shadow-xs">
                            <i class="ph-bold ph-stethoscope text-base"></i>
                            <span>Registrar intervención</span>
                        </button>
                    @endcanany
                @endif
            </div>
        </div>
    </x-slot:footer>
</x-ui.drawer-livewire>
@endif
