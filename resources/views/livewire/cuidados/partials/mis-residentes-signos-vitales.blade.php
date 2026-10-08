@php
    $limitesTecnicos = \App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService::limitesRegistro();
    $camposSignos = [
        ['key' => 'sis', 'error' => 'presion_sistolica', 'label' => 'Sistólica', 'wire' => 'signoSis', 'unit' => '', 'step' => '1'],
        ['key' => 'dia', 'error' => 'presion_diastolica', 'label' => 'Diastólica', 'wire' => 'signoDia', 'unit' => 'mmHg', 'step' => '1'],
        ['key' => 'fc', 'error' => 'frecuencia_cardiaca', 'label' => 'Pulso', 'wire' => 'signoFC', 'unit' => 'lpm', 'step' => '1'],
        ['key' => 'fr', 'error' => 'frecuencia_respiratoria', 'label' => 'Respiración', 'wire' => 'signoFR', 'unit' => 'rpm', 'step' => '1'],
        ['key' => 'temp', 'error' => 'temperatura', 'label' => 'Temperatura', 'wire' => 'signoTemp', 'unit' => '°C', 'step' => '0.1'],
        ['key' => 'sat', 'error' => 'saturacion_oxigeno', 'label' => 'Saturación de oxígeno', 'wire' => 'signoSat', 'unit' => '%', 'step' => '0.01'],
        ['key' => 'glucosa', 'error' => 'glucemia', 'label' => 'Glucemia', 'wire' => 'signoGlucosa', 'unit' => 'mg/dL', 'step' => '0.01'],
    ];
    $evaluacionesPorTarjeta = collect($signosEvaluacion['resultados'] ?? [])->groupBy(fn (array $resultado) => match ($resultado['variable']) {
        'presion_arterial', 'presion_sistolica', 'presion_diastolica' => 'pa',
        'frecuencia_cardiaca' => 'fc',
        'frecuencia_respiratoria' => 'fr',
        'temperatura' => 'temp',
        'saturacion_oxigeno' => 'sat',
        'glucemia' => 'glucosa',
        default => $resultado['variable'],
    });
    $tonosPorTarjeta = $evaluacionesPorTarjeta->map(function ($resultados) {
        if ($resultados->contains(fn (array $resultado) => $resultado['severidad'] === 'CRITICO')) return 'danger';
        if ($resultados->contains(fn (array $resultado) => $resultado['severidad'] === 'ALTO')) return 'high';
        if ($resultados->contains(fn (array $resultado) => $resultado['severidad'] === 'ADVERTENCIA')) return 'warning';
        if ($resultados->contains(fn (array $resultado) => $resultado['comportamiento_alerta'] === 'SUGERIR')) return 'warning';
        if ($resultados->contains(fn (array $resultado) => $resultado['severidad'] === 'OBJETIVO_PERSONALIZADO')) return 'target';
        if ($resultados->contains(fn (array $resultado) => $resultado['severidad'] === 'NORMAL')) return 'success';
        return 'neutral';
    });
    $resumenAlteraciones = collect($signosEvaluacion['resultados'] ?? [])->filter(
        fn (array $resultado) => in_array($resultado['severidad'] ?? null, ['ADVERTENCIA', 'ALTO', 'CRITICO'], true)
    );
    $resumenEtiquetas = collect(['CRITICO' => 'crítico', 'ALTO' => 'alto', 'ADVERTENCIA' => 'advertencia'])
        ->map(fn (string $etiqueta, string $severidad) => $resumenAlteraciones->where('severidad', $severidad)->count()
            ? $resumenAlteraciones->where('severidad', $severidad)->count().' '.$etiqueta : null)
        ->filter()->implode(' · ');
    $primerParametroAlterado = $evaluacionesPorTarjeta->keys()->first(
        fn (string $clave) => in_array($tonosPorTarjeta->get($clave), ['warning', 'high', 'danger'], true)
    );
@endphp

<div class="rm-signos" wire:ignore.self
     :data-graph-open="trendOpen"
     @keydown.escape="if (trendOpen) { $event.preventDefault(); $event.stopPropagation(); closeTrend() }"
     x-data="rmSignosRegistro(@js($signosHistorial ?? []), {
             sis: @js($signoSis),
             dia: @js($signoDia),
             fc: @js($signoFC),
             fr: @js($signoFR),
             temp: @js($signoTemp),
             sat: @js($signoSat),
             glucosa: @js($signoGlucosa),
             obs: @js($signoObs),
             fecha_hora: @js($signoFechaHora),
     }, @js($limitesTecnicos), @js($signosBandasObjetivo), @js($tonosPorTarjeta->all()))"
     x-on:signos-validacion-fallida.window="$nextTick(() => review())"
     x-on:signos-evaluacion-actualizada.window="syncEvaluation($event.detail)"
     x-on:signos-campos-limpiados.window="resetCapture($event.detail)"
     role="region"
     aria-label="Formulario de signos vitales">

    <p x-show="captureCleared" x-cloak role="status">Campos limpiados. Puedes introducir una nueva lectura; se conservaron el residente, la fecha y la hora.</p>
    <section class="rm-signos__measurement-time" aria-labelledby="signos-fecha-title">
        <h5 id="signos-fecha-title"><i class="ph-bold ph-clock" aria-hidden="true"></i> Momento de la medición</h5>
        <p id="signos-fecha-hora"><strong>Fecha y hora:</strong> {{ $signoFechaHora ? \Carbon\Carbon::parse($signoFechaHora)->format('d/m/Y H:i') : 'Sin registro abierto' }}</p>
        <p id="signos-fecha-ayuda"><i class="ph-bold ph-lock-simple" aria-hidden="true"></i> Fecha y hora automáticas · No editables</p>
        @error('fecha_hora')<p id="signos-fecha-error" class="rm-signos__error" role="alert">{{ $message }}</p>@enderror
    </section>
    @error('continuidad_signos')<p class="rm-signos__global-error" role="alert">{{ $message }}</p>@enderror
    @if($signosObjetivos !== [])
        <details class="rm-signos__observations">
            <summary>Objetivos médicos aplicables a esta medición</summary>
            @foreach($signosObjetivos as $objetivo)
                <p><strong>{{ $objetivo['parametro'] }} · {{ $objetivo['rango'] }}</strong><br>
                    Médico: {{ $objetivo['medico'] }} · {{ $objetivo['codigo'] }}<br>
                    Vigencia: {{ $objetivo['desde'] }} — {{ $objetivo['hasta'] }}<br>
                    Límites críticos: {{ $objetivo['criticos'] }} · Motivo: {{ $objetivo['motivo'] }}</p>
            @endforeach
        </details>
    @endif
    <!-- ERROR GENERAL -->
    @if($signosIntentoGuardar && $errors->any() && ! $errors->has('signos_guardado'))
        <div class="rm-signos__global-error" role="alert">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <span>No se puede continuar. Revisa las mediciones señaladas.</span>
        </div>
    @endif
    @error('mediciones')
        <div class="rm-signos__global-error" role="alert">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <!-- WORKSPACE PRINCIPAL -->
    @error('signos_guardado')
        <x-ui.resultado-operacion-clinica class="rm-signos__error-result" variant="error" :resident="$detalleResidente['nombre_completo']" :message="$message" />
    @enderror
    @if($resumenAlteraciones->isEmpty() && in_array($signosEvaluacion['severidad_global'] ?? null, ['NORMAL', 'OBJETIVO_PERSONALIZADO'], true))
        <div class="rm-signos__evaluation-summary" data-tone="success" role="status" aria-live="polite">
            <i class="ph-bold ph-check-circle" aria-hidden="true"></i>
            <span><strong>Lecturas evaluadas en rango</strong><small>Los campos sin medir o sin referencia no se clasifican como normales.</small></span>
        </div>
    @endif
    @if($resumenAlteraciones->isNotEmpty())
        <div class="rm-signos__evaluation-summary" data-tone="{{ $resumenAlteraciones->contains('severidad', 'CRITICO') ? 'danger' : ($resumenAlteraciones->contains('severidad', 'ALTO') ? 'high' : 'warning') }}" role="status" aria-live="polite">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <span>
                <strong>{{ $resumenAlteraciones->contains('severidad', 'CRITICO') ? $resumenAlteraciones->where('severidad', 'CRITICO')->count().' MEDICIÓN CRÍTICA'.($resumenAlteraciones->where('severidad', 'CRITICO')->count() > 1 ? 'S' : '') : $resumenEtiquetas }}</strong>
                <small>{{ $resumenAlteraciones->contains('severidad', 'CRITICO') ? 'Verifica y registra la lectura. Se generará una alerta crítica; los otros controles quedarán bloqueados hasta documentar atención.' : ($resumenAlteraciones->contains('severidad', 'ALTO') ? 'Hay una medición significativamente alterada.' : 'Hay una medición que requiere revisión.') }}</small>
            </span>
            @if($primerParametroAlterado)
                <button type="button" x-on:click="active = @js($primerParametroAlterado); $nextTick(() => { const card = document.getElementById('signos-' + active + '-title')?.closest('.rm-signos__card'); card?.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' }); card?.querySelector('input')?.focus({ preventScroll: true }); })">Ver medición</button>
            @endif
        </div>
    @endif
    <div class="rm-signos__workspace">
        <div class="rm-signos__main-col">
            <div class="rm-signos__section-heading"><h4>Mediciones</h4><span wire:loading.delay wire:target="signoSis,signoDia,signoFC,signoFR,signoTemp,signoSat,signoGlucosa" role="status">Evaluando lectura…</span></div>
            <div class="rm-signos__cards-grid">
                <!-- CARD 1: Presión arterial -->
                <section class="rm-signos__card rm-signos__card--pa" x-bind:class="{ 'rm-signos__card--active': active === 'pa' }" x-bind:data-tone="toneOf('pa')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoSis,signoDia" aria-labelledby="signos-pa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-pa-title"><i class="ph-bold ph-heart" aria-hidden="true"></i> Presión arterial (PA)</h5>
                        @include('livewire.cuidados.partials.mis-residentes-signos-badge', ['claveTarjeta' => 'pa', 'tonoBadge' => $tonosPorTarjeta->get('pa', 'neutral'), 'evaluacionesBadge' => $evaluacionesPorTarjeta->get('pa', collect())])
                    </div>
                    <div class="rm-signos__pa-fields">
                        @foreach(array_slice($camposSignos, 0, 2) as $campo)
                            @include('livewire.cuidados.partials.mis-residentes-signos-campo')
                        @endforeach
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('pa', collect()), 'claveTarjeta' => 'pa'])
                </section>

                <!-- CARD 2: Pulso -->
                <section class="rm-signos__card rm-signos__card--fc" x-bind:class="{ 'rm-signos__card--active': active === 'fc' }" x-bind:data-tone="toneOf('fc')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoFC" aria-labelledby="signos-fc-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fc-title"><i class="ph-bold ph-heart" aria-hidden="true"></i> Pulso</h5>
                        @include('livewire.cuidados.partials.mis-residentes-signos-badge', ['claveTarjeta' => 'fc', 'tonoBadge' => $tonosPorTarjeta->get('fc', 'neutral'), 'evaluacionesBadge' => $evaluacionesPorTarjeta->get('fc', collect())])
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[2]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('fc', collect()), 'claveTarjeta' => 'fc'])
                </section>

                <!-- CARD 3: Respiración -->
                <section class="rm-signos__card rm-signos__card--fr" x-bind:class="{ 'rm-signos__card--active': active === 'fr' }" x-bind:data-tone="toneOf('fr')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoFR" aria-labelledby="signos-fr-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fr-title"><i class="ph-bold ph-lungs" aria-hidden="true"></i> Respiración</h5>
                        @include('livewire.cuidados.partials.mis-residentes-signos-badge', ['claveTarjeta' => 'fr', 'tonoBadge' => $tonosPorTarjeta->get('fr', 'neutral'), 'evaluacionesBadge' => $evaluacionesPorTarjeta->get('fr', collect())])
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[3]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('fr', collect()), 'claveTarjeta' => 'fr'])
                </section>

                <!-- CARD 4: Temperatura -->
                <section class="rm-signos__card rm-signos__card--temp" x-bind:class="{ 'rm-signos__card--active': active === 'temp' }" x-bind:data-tone="toneOf('temp')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoTemp" aria-labelledby="signos-temp-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-temp-title"><i class="ph-bold ph-thermometer" aria-hidden="true"></i> Temperatura</h5>
                        @include('livewire.cuidados.partials.mis-residentes-signos-badge', ['claveTarjeta' => 'temp', 'tonoBadge' => $tonosPorTarjeta->get('temp', 'neutral'), 'evaluacionesBadge' => $evaluacionesPorTarjeta->get('temp', collect())])
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[4]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('temp', collect()), 'claveTarjeta' => 'temp'])
                </section>

                <!-- CARD 5: Saturación de oxígeno (SpO₂) -->
                <section class="rm-signos__card rm-signos__card--sat" x-bind:class="{ 'rm-signos__card--active': active === 'sat', 'rm-signos__card--awaiting-goal': awaitingMedicalGoal('sat') }" x-bind:data-tone="toneOf('sat')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoSat" aria-labelledby="signos-sat-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-sat-title"><i class="ph-bold ph-drop" aria-hidden="true"></i> Saturación de oxígeno <small>(SpO₂)</small></h5>
                        @include('livewire.cuidados.partials.mis-residentes-signos-badge', ['claveTarjeta' => 'sat', 'tonoBadge' => $tonosPorTarjeta->get('sat', 'neutral'), 'evaluacionesBadge' => $evaluacionesPorTarjeta->get('sat', collect())])
                    </div>
                    <div class="rm-signos__sat-fields">
                        @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[5]])
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('sat', collect()), 'claveTarjeta' => 'sat'])
                </section>

                <!-- CARD 6: Glucemia -->
                <section class="rm-signos__card rm-signos__card--glucosa" x-bind:class="{ 'rm-signos__card--active': active === 'glucosa' }" x-bind:data-tone="toneOf('glucosa')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoGlucosa" aria-labelledby="signos-glucosa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-glucosa-title"><i class="ph-bold ph-drop-half" aria-hidden="true"></i> Glucemia</h5>
                        @include('livewire.cuidados.partials.mis-residentes-signos-badge', ['claveTarjeta' => 'glucosa', 'tonoBadge' => $tonosPorTarjeta->get('glucosa', 'neutral'), 'evaluacionesBadge' => $evaluacionesPorTarjeta->get('glucosa', collect())])
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[6]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('glucosa', collect()), 'claveTarjeta' => 'glucosa'])
                </section>
            </div>

        </div>

        <aside class="rm-signos__assessment"
               aria-labelledby="signos-tendencia-title">
            <div class="rm-signos__trend-heading">
                <span class="rm-signos__trend-icon-box" aria-hidden="true"><i class="ph-bold ph-stethoscope"></i></span>
                <div class="rm-signos__trend-titles">
                    <h5 id="signos-tendencia-title">Interpretación y acción</h5>
                    <p aria-live="polite" x-text="active ? 'Lectura de ' + meta[active].label : 'Selecciona una medición.'"></p>
                </div>
                <label class="rm-signos__trend-select-label" for="signos-tendencia-selector">Medición</label>
                <select id="signos-tendencia-selector" class="rm-signos__trend-select" x-model="active" @change="focusMeasurement(active, $event.currentTarget)" aria-controls="signos-tendencia-contenido signos-grafica-popup">
                    <option value="">Elegir</option>
                    <template x-for="(item, key) in meta" :key="key"><option :value="key" x-text="item.label"></option></template>
                </select>
            </div>

            <!-- Si ningún campo activo -->
            <div class="rm-signos__trend-empty-state" x-show="!active">
                <p>Selecciona una medición para consultar la interpretación y la acción recomendada.</p>
            </div>


            <div id="signos-tendencia-contenido" class="rm-signos__assessment-empty" x-show="active && !validPreview(active)"><i class="ph-bold ph-stethoscope" aria-hidden="true"></i><strong>Introduce una medición</strong><p>Al completar el valor, aquí aparecerán su interpretación y el siguiente paso.</p></div>
            <p class="rm-signos__assessment-empty" x-show="active && validPreview(active) && !evaluationCurrent(active)" role="status">Evaluando la lectura…</p>
            @foreach($evaluacionesPorTarjeta as $claveTarjeta => $resultadosTarjeta)
                <div class="rm-signos__interpretation" data-tone="{{ $tonosPorTarjeta->get($claveTarjeta, 'neutral') }}" x-show="active === @js($claveTarjeta) && evaluationCurrent(@js($claveTarjeta)) && !hasCardError(@js($claveTarjeta))" x-cloak>
                    @foreach($resultadosTarjeta as $resultado)
                        <section aria-label="Interpretación clínica">
                            <h6>Interpretación · {{ match($resultado['severidad'] ?? null) {
                                'CRITICO' => 'Crítico', 'ALTO' => 'Alto', 'ADVERTENCIA' => 'Advertencia',
                                'NORMAL' => 'Normal', 'OBJETIVO_PERSONALIZADO' => 'En objetivo',
                                default => 'Sin clasificación aplicable'
                            } }}</h6>
                            <p><strong>Valor medido:</strong> {{ $resultado['valor'] }} {{ $resultado['unidad'] }}</p>
                            <details class="rm-signos__clinical-details"><summary>Criterio y explicación clínica</summary>
                            @if(filled($resultado['rango_o_umbral'] ?? null))
                                <p><strong>{{ match($resultado['fuente_evaluacion'] ?? null) {
                                    'OBJETIVO_MEDICO' => 'Objetivo individual',
                                    'UMBRAL_CRITICO' => 'Umbral de seguridad',
                                    default => 'Rango o límite de referencia'
                                } }}:</strong> {{ $resultado['rango_o_umbral'] }}</p>
                            @endif
                            @if(filled($resultado['referencia_utilizada'] ?? null))
                                <p class="rm-signos__reference-source"><strong>Criterio aplicado:</strong> {{ $resultado['referencia_utilizada'] }}</p>
                            @endif
                            <p>{{ $resultado['explicacion'] }}</p>
                            </details>
                        </section>
                    @endforeach
                    @foreach($resultadosTarjeta as $resultado)
                        <section class="rm-signos__recommended-action" aria-label="Acción recomendada">
                            <h6><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i> Acción recomendada</h6>
                            @if(($resultado['comportamiento_alerta'] ?? '') === 'AUTOMATICA_AL_CONFIRMAR')
                                <ol class="rm-signos__action-steps">
                                    <li><strong>Verificar la lectura</strong><p>{{ $resultado['recomendacion'] ?: 'Comprueba la transcripción, la técnica y el contexto clínico; sigue el protocolo institucional.' }}</p></li>
                                    <li><strong>Confirmar y registrar</strong><p>Guarda la lectura real; se generará una alerta vinculada.</p></li>
                                    <li><strong>Documentar la atención</strong><p>Registra la intervención en esa alerta para habilitar los otros controles.</p></li>
                                </ol>
                            @elseif(filled($resultado['recomendacion'] ?? null))
                                <p>{{ $resultado['recomendacion'] }}</p>
                            @elseif(in_array($resultado['severidad'] ?? null, ['NORMAL', 'OBJETIVO_PERSONALIZADO'], true))
                                <strong>Registrar el control</strong><p>La lectura está en la referencia utilizada. Confirma y registra la medición. Si hay síntomas o condiciones relevantes, descríbelos en Observaciones; estar en rango no los descarta.</p>
                            @elseif(in_array($resultado['severidad'] ?? null, ['ADVERTENCIA', 'ALTO'], true))
                                <strong>Revisar la lectura y el contexto</strong><p>Comprueba la transcripción y las condiciones de medición. Conserva la clasificación mostrada y sigue el protocolo institucional para registrar el control.</p>
                            @else
                                <strong>Registrar el valor y su contexto</strong><p>No hay una recomendación automática aplicable a esta lectura. Documenta el valor y las condiciones observadas; consulta el protocolo institucional para la atención.</p>
                            @endif
                        </section>
                        @if(($resultado['comportamiento_alerta'] ?? '') === 'AUTOMATICA_AL_CONFIRMAR')
                            <p class="rm-signos__alert-note">Asignar o reconocer la alerta no habilita otros controles ni confirma un aviso al médico.</p>
                        @elseif(($resultado['comportamiento_alerta'] ?? '') === 'SUGERIR')
                            <p class="rm-signos__alert-note">Revisar el contexto; esta lectura aislada no genera alerta automática.</p>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </aside>
    </div>
    <div id="signos-grafica-popup" popover="manual" role="dialog" aria-modal="false" class="rm-signos__graph-popup" x-ref="trendDialog" wire:ignore.self :style="trendStyle()" aria-labelledby="signos-grafica-title" aria-describedby="signos-grafica-description" @resize.window="if (trendOpen) fitTrend()" @keydown.escape.prevent.stop="closeTrend()">
        @foreach(['w' => 'izquierdo', 'e' => 'derecho', 'n' => 'superior', 's' => 'inferior', 'nw' => 'superior izquierdo', 'ne' => 'superior derecho', 'sw' => 'inferior izquierdo', 'se' => 'inferior derecho'] as $borde => $nombreBorde)
            <button type="button" class="rm-signos__graph-edge rm-signos__graph-edge--{{ $borde }}" aria-label="Ajustar borde {{ $nombreBorde }} de la gráfica" title="Arrastra este borde para ajustar el tamaño" @pointerdown="startTrendPointer($event, 'resize', '{{ $borde }}')" @pointermove="updateTrendPointer($event)" @pointerup="endTrendPointer($event)" @pointercancel="endTrendPointer($event)" @lostpointercapture="trendPointer = null" @keydown.arrow-left.prevent="resizeTrendEdge('{{ $borde }}', -24, 0)" @keydown.arrow-right.prevent="resizeTrendEdge('{{ $borde }}', 24, 0)" @keydown.arrow-up.prevent="resizeTrendEdge('{{ $borde }}', 0, -24)" @keydown.arrow-down.prevent="resizeTrendEdge('{{ $borde }}', 0, 24)"></button>
        @endforeach
        <header class="rm-signos__graph-popup-header">
            <button type="button" class="rm-btn-icon rm-signos__graph-move" aria-label="Mover gráfica: arrastra o usa las flechas; Inicio restablece la ventana" title="Mover gráfica" @pointerdown="startTrendPointer($event, 'move')" @pointermove="updateTrendPointer($event)" @pointerup="endTrendPointer($event)" @pointercancel="endTrendPointer($event)" @lostpointercapture="trendPointer = null" @keydown.arrow-left.prevent="moveTrend(-24, 0)" @keydown.arrow-right.prevent="moveTrend(24, 0)" @keydown.arrow-up.prevent="moveTrend(0, -24)" @keydown.arrow-down.prevent="moveTrend(0, 24)" @keydown.home.prevent="resetTrend()"><i class="ph-bold ph-arrows-out-cardinal" aria-hidden="true"></i></button>
            <div><h5 id="signos-grafica-title"><i class="ph-bold ph-chart-line" aria-hidden="true"></i><span x-text="active ? 'Evolución de ' + meta[active].label : 'Gráfica de evolución'"></span></h5><p id="signos-grafica-description">{{ $detalleResidente['nombre_completo'] }} · La captura se conserva al cerrar.</p></div><button type="button" class="rm-btn-icon" @click="closeTrend()" aria-label="Cerrar gráfica"><i class="ph-bold ph-x" aria-hidden="true"></i></button>
        </header>
        <div class="rm-signos__graph-popup-body">
            <template x-if="trendOpen && active"><div>
                @include('livewire.cuidados.partials.mis-residentes-signos-grafica')
                <details class="rm-signos__popup-history" x-show="records(active).length"><summary>Últimos registros guardados</summary><template x-for="(row, index) in records(active).slice(0, 3)" :key="index"><div class="rm-signos__history-item"><span x-text="row.fecha"></span><strong x-text="labelOf(row, active) + ' ' + meta[active].unit"></strong></div></template>@can('enfermeria.ver_ficha_paciente')<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente'], 'tab' => 'signos']) }}">Ver historial en la ficha clínica</a>@endcan</details>
            </div></template>
        </div>
        <footer class="rm-signos__graph-popup-tools">
            <span>Arrastra los bordes para ajustar el tamaño</span>
            <button type="button" class="rm-btn-icon" @click="resizeTrend(-64, -48)" aria-label="Reducir gráfica" title="Reducir"><i class="ph-bold ph-minus" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon" @click="resizeTrend(64, 48)" aria-label="Ampliar gráfica" title="Ampliar"><i class="ph-bold ph-plus" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon" @click="resetTrend()" aria-label="Restablecer tamaño y posición de la gráfica" title="Restablecer"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon rm-signos__graph-resize" aria-label="Ajustar tamaño: arrastra o usa las flechas" title="Arrastra para ajustar tamaño" @pointerdown="startTrendPointer($event, 'resize')" @pointermove="updateTrendPointer($event)" @pointerup="endTrendPointer($event)" @pointercancel="endTrendPointer($event)" @lostpointercapture="trendPointer = null" @keydown.arrow-left.prevent="resizeTrend(-24, 0)" @keydown.arrow-right.prevent="resizeTrend(24, 0)" @keydown.arrow-up.prevent="resizeTrend(0, -24)" @keydown.arrow-down.prevent="resizeTrend(0, 24)"><i class="ph-bold ph-arrows-out-simple" aria-hidden="true"></i></button>
        </footer>
    </div>
    <section class="rm-signos__observations" aria-labelledby="signos-obs-title">
        <div class="rm-signos__obs-header"><h5 id="signos-obs-title"><i class="ph-bold ph-chat-circle-dots" aria-hidden="true"></i> Observaciones</h5></div>
        <label for="signos-observacion">Contexto de la medición (opcional)</label>
        <textarea id="signos-observacion" wire:model="signoObs" x-model="values.obs" maxlength="5000" rows="3" aria-describedby="signos-contexto-ayuda @error('observacion') signos-observacion-error @enderror" placeholder="Añade contexto relevante sobre la medición, síntomas o condiciones observadas, si corresponde." @error('observacion') aria-invalid="true" @enderror></textarea>
        <p id="signos-contexto-ayuda">Si corresponde, describe síntomas o cambios recientes, postura y técnica, oxígeno recibido durante la medición y relación de la glucemia con comidas o medicación. Una lectura en rango no descarta síntomas relevantes.</p>
        <div class="rm-signos__obs-footer">
            @error('observacion') <p id="signos-observacion-error" class="rm-signos__error" role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> {{ $message }}</p> @enderror
            <span class="rm-signos__char-count" aria-hidden="true" x-text="String(values.obs ?? '').length + '/5000'"></span>
        </div>
    </section>
</div>
