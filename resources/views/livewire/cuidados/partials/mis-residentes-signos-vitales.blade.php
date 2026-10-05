@php
    $nombreResponsable = auth()->user()->name ?? 'Enfermería';
    if (auth()->user()->nombres) {
        $nombreResponsable = trim(auth()->user()->nombres . ' ' . (auth()->user()->ap_paterno ?? ''));
    }
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
        if ($resultados->contains(fn (array $resultado) => in_array($resultado['severidad'], ['ALTO', 'ADVERTENCIA'], true))) return 'warning';
        if ($resultados->contains(fn (array $resultado) => $resultado['comportamiento_alerta'] === 'SUGERIR')) return 'warning';
        if ($resultados->contains(fn (array $resultado) => in_array($resultado['severidad'], ['NORMAL', 'OBJETIVO_PERSONALIZADO'], true))) return 'success';
        return 'neutral';
    });
@endphp

<div class="rm-signos"
     x-data="rmSignosRegistro(@js($signosHistorial ?? []), {
             sis: @js($signoSis),
             dia: @js($signoDia),
             fc: @js($signoFC),
             fr: @js($signoFR),
             temp: @js($signoTemp),
             sat: @js($signoSat),
             glucosa: @js($signoGlucosa),
             obs: @js($signoObs),
     }, @js($limitesTecnicos))"
     x-on:signos-validacion-fallida.window="$nextTick(() => review())"
     role="region"
     aria-label="Formulario de signos vitales">

    <!-- El encabezado del modal ya muestra la identidad y ubicación del residente. -->
    <section class="rm-signos__context" aria-label="Contexto de registro">
        <div class="rm-signos__context-item">
            <span class="rm-signos__context-icon rm-signos__context-icon--user"><i class="ph-bold ph-user" aria-hidden="true"></i></span>
            <div class="rm-signos__context-info">
                <span class="rm-signos__context-label">Responsable</span>
                <strong class="rm-signos__context-value">{{ $nombreResponsable }}</strong>
                <span class="rm-signos__context-sub">Enfermería</span>
            </div>
        </div>
        <div class="rm-signos__context-item">
            <span class="rm-signos__context-icon rm-signos__context-icon--sun"><i class="ph-bold ph-sun" aria-hidden="true"></i></span>
            <div class="rm-signos__context-info">
                <span class="rm-signos__context-label">Turno actual</span>
                <strong class="rm-signos__context-value">{{ $signosContextoTurno['nombre'] ?? 'Sin turno activo' }} @if(filled($signosContextoTurno['horario'] ?? null))<span class="rm-signos__context-hours">· {{ $signosContextoTurno['horario'] }}</span>@endif</strong>
            </div>
        </div>
        <p class="rm-signos__context-note"><i class="ph-bold ph-clock" aria-hidden="true"></i> Ahora · La hora se asigna al confirmar</p>
    </section>

    <!-- ERROR GENERAL -->
    @if($signosIntentoGuardar && $errors->any())
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
        <div class="rm-signos__global-error" role="alert"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><span>{{ $message }}</span></div>
    @enderror
    <div class="rm-signos__workspace">
        <div class="rm-signos__main-col">
            <div class="rm-signos__section-heading"><h4>Mediciones</h4><span wire:loading.delay wire:target="signoSis,signoDia,signoFC,signoFR,signoTemp,signoSat,signoGlucosa" role="status">Evaluando lectura…</span></div>
            <div class="rm-signos__cards-grid">
                <!-- CARD 1: Presión arterial -->
                <section class="rm-signos__card rm-signos__card--pa" x-bind:class="{ 'rm-signos__card--active': active === 'pa' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoSis,signoDia" data-tone="{{ $tonosPorTarjeta->get('pa', 'neutral') }}" aria-labelledby="signos-pa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-pa-title"><i class="ph-bold ph-heart" aria-hidden="true"></i> Presión arterial (PA)</h5>
                    </div>
                    <div class="rm-signos__pa-fields">
                        @foreach(array_slice($camposSignos, 0, 2) as $campo)
                            @include('livewire.cuidados.partials.mis-residentes-signos-campo')
                        @endforeach
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('pa', collect())])
                </section>

                <!-- CARD 2: Pulso -->
                <section class="rm-signos__card rm-signos__card--fc" x-bind:class="{ 'rm-signos__card--active': active === 'fc' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoFC" data-tone="{{ $tonosPorTarjeta->get('fc', 'neutral') }}" aria-labelledby="signos-fc-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fc-title"><i class="ph-bold ph-heart" aria-hidden="true"></i> Pulso</h5>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[2]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('fc', collect())])
                </section>

                <!-- CARD 3: Respiración -->
                <section class="rm-signos__card rm-signos__card--fr" x-bind:class="{ 'rm-signos__card--active': active === 'fr' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoFR" data-tone="{{ $tonosPorTarjeta->get('fr', 'neutral') }}" aria-labelledby="signos-fr-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fr-title"><i class="ph-bold ph-lungs" aria-hidden="true"></i> Respiración</h5>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[3]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('fr', collect())])
                </section>

                <!-- CARD 4: Temperatura -->
                <section class="rm-signos__card rm-signos__card--temp" x-bind:class="{ 'rm-signos__card--active': active === 'temp' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoTemp" data-tone="{{ $tonosPorTarjeta->get('temp', 'neutral') }}" aria-labelledby="signos-temp-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-temp-title"><i class="ph-bold ph-thermometer" aria-hidden="true"></i> Temperatura</h5>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[4]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('temp', collect())])
                </section>

                <!-- CARD 5: Saturación de oxígeno (SpO₂) -->
                <section class="rm-signos__card rm-signos__card--sat" x-bind:class="{ 'rm-signos__card--active': active === 'sat' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoSat" data-tone="{{ $tonosPorTarjeta->get('sat', 'neutral') }}" aria-labelledby="signos-sat-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-sat-title"><i class="ph-bold ph-drop" aria-hidden="true"></i> Saturación de oxígeno <small>(SpO₂)</small></h5>
                    </div>
                    <div class="rm-signos__sat-fields">
                        @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[5]])
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('sat', collect())])
                </section>

                <!-- CARD 6: Glucemia -->
                <section class="rm-signos__card rm-signos__card--glucosa" x-bind:class="{ 'rm-signos__card--active': active === 'glucosa' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoGlucosa" data-tone="{{ $tonosPorTarjeta->get('glucosa', 'neutral') }}" aria-labelledby="signos-glucosa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-glucosa-title"><i class="ph-bold ph-drop-half" aria-hidden="true"></i> Glucemia</h5>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[6]])
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('glucosa', collect())])
                </section>
            </div>

            <!-- OBSERVACIONES -->
            <section class="rm-signos__observations" aria-labelledby="signos-obs-title">
                <div class="rm-signos__obs-header">
                    <h5 id="signos-obs-title"><i class="ph-bold ph-chat-circle-dots" aria-hidden="true"></i> Observaciones</h5>
                </div>
                <label for="signos-observacion">Contexto de la medición (opcional)</label>
                <textarea id="signos-observacion" wire:model="signoObs" x-model="values.obs" maxlength="5000" rows="2" placeholder="Añade contexto relevante sobre la medición, síntomas o condiciones observadas, si corresponde." @error('observacion') aria-invalid="true" aria-describedby="signos-observacion-error" @enderror></textarea>
                <div class="rm-signos__obs-footer">
                    @error('observacion') <p id="signos-observacion-error" class="rm-signos__error" role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> {{ $message }}</p> @enderror
                    <span class="rm-signos__char-count" aria-hidden="true" x-text="String(values.obs ?? '').length + '/5000'"></span>
                </div>
            </section>
        </div>

        <!-- PANEL LATERAL: Tendencia en vivo -->
        <aside class="rm-signos__trend"
               x-bind:class="{
                   'rm-signos__trend--inactive': !active,
                   'rm-signos__trend--no-history': active && records(active).length === 0
               }"
               aria-labelledby="signos-tendencia-title">
            <div class="rm-signos__trend-heading">
                <span class="rm-signos__trend-icon-box" aria-hidden="true"><i class="ph-bold ph-chart-line"></i></span>
                <div class="rm-signos__trend-titles">
                    <h5 id="signos-tendencia-title">Evolución y validación</h5>
                    <p aria-live="polite" x-text="active ? 'Evolución de ' + meta[active].label : 'Selecciona una medición para consultar su evolución.'"></p>
                </div>
                <label class="rm-signos__trend-select-label" for="signos-tendencia-selector">Medición</label>
                <select id="signos-tendencia-selector" class="rm-signos__trend-select" x-model="active" aria-controls="signos-tendencia-contenido">
                    <option value="">Elegir</option>
                    <template x-for="(item, key) in meta" :key="key"><option :value="key" x-text="item.label"></option></template>
                </select>
            </div>

            <!-- Si ningún campo activo -->
            <div class="rm-signos__trend-empty-state" x-show="!active">
                <p>Selecciona una medición para consultar su evolución.</p>
            </div>


            <!-- Contenido cuando hay campo activo -->
            <div id="signos-tendencia-contenido" class="rm-signos__trend-content" x-show="active">
                <template x-if="active">
                    <div>
                        <div class="rm-signos__trend-section-title"><h6>Evolución</h6></div>
                        <template x-if="chartRows(active).length >= 3">
                            <div class="rm-signos__chart">
                                <div class="rm-signos__chart-canvas-wrap">
                                    <svg class="rm-signos__chart-svg" viewBox="0 0 300 140" role="img" aria-label="Tendencia de las mediciones del residente y vista previa sin guardar">
                                        <polyline class="rm-signos__chart-path" fill="none" x-bind:points="chartPoints(active)"></polyline>
                                        <template x-for="(row, index) in chartMarkers(active)" :key="index">
                                            <circle class="rm-signos__chart-dot" :class="row.preview ? 'rm-signos__chart-dot--current' : 'rm-signos__chart-dot--history'" x-bind:cx="row.x" x-bind:cy="row.y" r="5" tabindex="0" x-bind:aria-label="row.date + ': ' + row.label + ' ' + meta[active].unit"><title x-text="row.date + ': ' + row.label + ' ' + meta[active].unit"></title></circle>
                                        </template>
                                    </svg>
                                </div>
                            </div>
                        </template>
                        <p x-show="chartRows(active).length < 3">No hay suficientes registros anteriores para mostrar una tendencia.</p>
                        <div class="rm-signos__current-block" x-show="validPreview(active)">
                            <div class="rm-signos__current-left">
                                <span class="rm-signos__current-label">Valor actual · Sin guardar</span>
                                <div class="rm-signos__current-number">
                                    <strong x-text="previewLabel(active)"></strong>
                                    <small x-text="meta[active].unit"></small>
                                </div>
                            </div>
                            <div class="rm-signos__current-compare" x-show="previous(active)">
                                <span>Anterior <strong x-text="previous(active) ? labelOf(previous(active), active) + ' ' + meta[active].unit : ''"></strong></span>
                                <span>Cambio <strong x-text="change(active)"></strong></span>
                            </div>
                        </div>
                        <div class="rm-signos__history-list" x-show="records(active).length > 0">
                            <h6>Últimos registros</h6>
                            <template x-for="(row, index) in records(active).slice(0, 3)" :key="index">
                                <div class="rm-signos__history-item">
                                    <div class="rm-signos__history-date">
                                        <span class="rm-signos__history-dot rm-signos__history-dot--slate" aria-hidden="true"></span>
                                        <span x-text="row.fecha"></span>
                                    </div>
                                    <strong class="rm-signos__history-val" x-text="labelOf(row, active) + ' ' + meta[active].unit"></strong>
                                </div>
                            </template>
                        </div>
                        <p x-show="records(active).length === 0">Sin mediciones previas para este parámetro.</p>
                        <p class="rm-signos__preview-note">La lectura actual es una vista previa hasta confirmar el registro.</p>
                    </div>
                </template>
            </div>
            @foreach($evaluacionesPorTarjeta as $claveTarjeta => $resultadosTarjeta)
                <div class="rm-signos__interpretation" x-show="active === @js($claveTarjeta)" x-cloak>
                    @foreach($resultadosTarjeta as $resultado)
                        <section aria-label="Interpretación clínica">
                            <h6>Interpretación · {{ match($resultado['severidad'] ?? null) {
                                'CRITICO' => 'Crítico', 'ALTO' => 'Alto', 'ADVERTENCIA' => 'Advertencia',
                                'NORMAL' => 'Normal', 'OBJETIVO_PERSONALIZADO' => 'En objetivo',
                                default => 'Sin clasificación aplicable'
                            } }}</h6>
                            @if(filled($resultado['rango_o_umbral'] ?? null))
                                <p><strong>{{ match($resultado['fuente_evaluacion'] ?? null) {
                                    'OBJETIVO_MEDICO' => 'Objetivo individual',
                                    'UMBRAL_CRITICO' => 'Umbral de seguridad',
                                    default => 'Referencia utilizada'
                                } }}:</strong> {{ $resultado['rango_o_umbral'] }}</p>
                            @endif
                            @if(filled($resultado['referencia_utilizada'] ?? null))
                                <p class="rm-signos__reference-source"><strong>Origen:</strong> {{ $resultado['referencia_utilizada'] }}</p>
                            @endif
                            <p>{{ $resultado['explicacion'] }}</p>
                        </section>
                        <section aria-label="Acción recomendada"><h6>Acción recomendada</h6><p>{{ filled($resultado['recomendacion'] ?? null) ? $resultado['recomendacion'] : 'No hay una acción adicional indicada por la regla vigente para esta lectura.' }}</p></section>
                        @if(($resultado['comportamiento_alerta'] ?? '') === 'AUTOMATICA_AL_CONFIRMAR')
                            <p class="rm-signos__alert-note">Al confirmar se generará una alerta y su evento inicial.</p>
                        @elseif(($resultado['comportamiento_alerta'] ?? '') === 'SUGERIR')
                            <p class="rm-signos__alert-note">Revisar el contexto; esta lectura aislada no genera alerta automática.</p>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </aside>
    </div>
</div>
