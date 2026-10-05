@php
    $nombreResponsable = auth()->user()->name ?? 'Enfermería';
    if (auth()->user()->nombres) {
        $nombreResponsable = trim(auth()->user()->nombres . ' ' . (auth()->user()->ap_paterno ?? ''));
    }
    $camposSignos = [
        ['key' => 'sis', 'error' => 'presion_sistolica', 'label' => 'Sistólica', 'wire' => 'signoSis', 'unit' => 'mmHg', 'max' => \App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService::PAS_MAX, 'step' => '1'],
        ['key' => 'dia', 'error' => 'presion_diastolica', 'label' => 'Diastólica', 'wire' => 'signoDia', 'unit' => 'mmHg', 'max' => \App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService::PAD_MAX, 'step' => '1'],
        ['key' => 'fc', 'error' => 'frecuencia_cardiaca', 'label' => 'Pulso', 'wire' => 'signoFC', 'unit' => 'lpm', 'max' => \App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService::FC_MAX, 'step' => '1'],
        ['key' => 'fr', 'error' => 'frecuencia_respiratoria', 'label' => 'Respiración', 'wire' => 'signoFR', 'unit' => 'rpm', 'max' => \App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService::FR_MAX, 'step' => '1'],
        ['key' => 'temp', 'error' => 'temperatura', 'label' => 'Temperatura', 'wire' => 'signoTemp', 'unit' => '°C', 'max' => \App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService::TEMP_MAX, 'step' => '0.1'],
        ['key' => 'sat', 'error' => 'saturacion_oxigeno', 'label' => 'Saturación de oxígeno', 'wire' => 'signoSat', 'unit' => '%', 'max' => '100', 'step' => '0.01'],
        ['key' => 'glucosa', 'error' => 'glucemia', 'label' => 'Glucemia', 'wire' => 'signoGlucosa', 'unit' => 'mg/dL', 'max' => '999999.99', 'step' => '0.01'],
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
     })"
     x-on:signos-revisar.window="review()"
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
        <p class="rm-signos__context-note"><i class="ph-bold ph-clock" aria-hidden="true"></i> La fecha y hora se asignan al confirmar.</p>
    </section>

    <section class="rm-signos__medical-goals" aria-label="Objetivos clínicos indicados por médico">
        <strong>Objetivos clínicos indicados por médico</strong>
        @forelse($signosObjetivos as $objetivo)
            <span>{{ $objetivo['nombre'] }}: {{ $objetivo['min'] ?? '—' }}–{{ $objetivo['max'] ?? '—' }}</span>
        @empty
            <span>Sin objetivo individual configurado.</span>
        @endforelse
    </section>

    <section class="rm-signos__intro" aria-labelledby="signos-intro-title">
        <i class="ph-bold ph-heartbeat" aria-hidden="true"></i>
        <div>
            <strong id="signos-intro-title">Registro de signos vitales</strong>
            <p>Completa únicamente las mediciones realizadas. Los datos se incorporarán al seguimiento clínico del residente.</p>
        </div>
        <span class="rm-signos__evaluating" wire:loading.delay wire:target="signoSis,signoDia,signoFC,signoFR,signoTemp,signoSat,signoGlucosa" role="status">Evaluando lectura…</span>
    </section>

    <!-- ERROR GENERAL -->
    @if($signosIntentoGuardar && $errors->any())
        <div class="rm-signos__global-error" role="alert">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <span>Revisa algunos datos antes de registrar.</span>
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
                    <p class="rm-signos__reference">Referencia general: sistólica &lt;{{ config('signos_vitales.presion.sistolica_referencia_alta') }} / diastólica &lt;{{ config('signos_vitales.presion.diastolica_referencia_alta') }} mmHg</p>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('pa', collect())])
                </section>

                <!-- CARD 2: Pulso -->
                <section class="rm-signos__card rm-signos__card--fc" x-bind:class="{ 'rm-signos__card--active': active === 'fc' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoFC" data-tone="{{ $tonosPorTarjeta->get('fc', 'neutral') }}" aria-labelledby="signos-fc-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fc-title"><i class="ph-bold ph-heart" aria-hidden="true"></i> Pulso</h5>
                        <span class="rm-signos__unit-badge">bpm</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[2]])
                    <p class="rm-signos__reference">Referencia general: {{ config('signos_vitales.pulso.advertencia_baja') + 1 }}–{{ config('signos_vitales.pulso.normal_alta') }} lpm</p>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('fc', collect())])
                </section>

                <!-- CARD 3: Respiración -->
                <section class="rm-signos__card rm-signos__card--fr" x-bind:class="{ 'rm-signos__card--active': active === 'fr' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoFR" data-tone="{{ $tonosPorTarjeta->get('fr', 'neutral') }}" aria-labelledby="signos-fr-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fr-title"><i class="ph-bold ph-lungs" aria-hidden="true"></i> Respiración</h5>
                        <span class="rm-signos__unit-badge">rpm</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[3]])
                    <p class="rm-signos__reference">Referencia general: {{ config('signos_vitales.respiracion.advertencia_baja') + 1 }}–{{ config('signos_vitales.respiracion.normal_alta') }} rpm</p>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('fr', collect())])
                </section>

                <!-- CARD 4: Temperatura -->
                <section class="rm-signos__card rm-signos__card--temp" x-bind:class="{ 'rm-signos__card--active': active === 'temp' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoTemp" data-tone="{{ $tonosPorTarjeta->get('temp', 'neutral') }}" aria-labelledby="signos-temp-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-temp-title"><i class="ph-bold ph-thermometer" aria-hidden="true"></i> Temperatura</h5>
                        <span class="rm-signos__unit-badge">°C</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[4]])
                    <p class="rm-signos__reference">Referencia general: {{ config('signos_vitales.temperatura.referencia_baja') }}–{{ config('signos_vitales.temperatura.referencia_alta') }} °C</p>
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
                    <p class="rm-signos__reference">La saturación se valora según el objetivo individual indicado por médico.</p>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('sat', collect())])
                </section>

                <!-- CARD 6: Glucemia -->
                <section class="rm-signos__card rm-signos__card--glucosa" x-bind:class="{ 'rm-signos__card--active': active === 'glucosa' }" wire:loading.class="rm-signos__card--evaluating" wire:target="signoGlucosa" data-tone="{{ $tonosPorTarjeta->get('glucosa', 'neutral') }}" aria-labelledby="signos-glucosa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-glucosa-title"><i class="ph-bold ph-drop-half" aria-hidden="true"></i> Glucemia</h5>
                        <span class="rm-signos__unit-badge">mg/dL</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[6]])
                    <p class="rm-signos__reference">Atención si &lt;{{ config('signos_vitales.glucemia.advertencia_baja') }}; revisar contexto si &gt;{{ config('signos_vitales.glucemia.revision_alta') }} mg/dL.</p>
                    @include('livewire.cuidados.partials.mis-residentes-signos-estado', ['evaluaciones' => $evaluacionesPorTarjeta->get('glucosa', collect())])
                </section>
            </div>

            @php
                $lecturasParaRevisar = collect($signosEvaluacion['resultados'] ?? [])->filter(
                    fn (array $resultado) => in_array($resultado['severidad'] ?? null, ['ADVERTENCIA', 'ALTO', 'CRITICO'], true)
                        || ($resultado['comportamiento_alerta'] ?? null) === 'SUGERIR'
                );
            @endphp
            @if($lecturasParaRevisar->isNotEmpty())
                <section class="rm-signos__review" aria-labelledby="signos-review-title">
                    <div>
                        <strong id="signos-review-title"><i class="ph-bold ph-eye" aria-hidden="true"></i> Revisión antes de registrar</strong>
                        <p>Hay {{ $lecturasParaRevisar->count() }} {{ $lecturasParaRevisar->count() === 1 ? 'lectura que requiere' : 'lecturas que requieren' }} revisión. Confirma que transcribiste los valores medidos.</p>
                    </div>
                    <label for="signos-confirmar-lecturas">
                        <input id="signos-confirmar-lecturas" type="checkbox" wire:model="signoConfirmarAtipico">
                        <span>Revisé las mediciones señaladas y deseo registrarlas.</span>
                    </label>
                    @error('signos_confirmacion')<p class="rm-signos__error" role="alert">{{ $message }}</p>@enderror
                </section>
            @endif

            <!-- OBSERVACIONES -->
            <section class="rm-signos__observations" aria-labelledby="signos-obs-title">
                <div class="rm-signos__obs-header">
                    <h5 id="signos-obs-title"><i class="ph-bold ph-chat-circle-dots" aria-hidden="true"></i> Observaciones</h5>
                </div>
                <label class="sr-only" for="signos-observacion">Observación clínica</label>
                <textarea id="signos-observacion" wire:model="signoObs" x-model="values.obs" maxlength="5000" rows="2" placeholder="Añade contexto relevante sobre la medición, síntomas, condiciones, etc." @error('observacion') aria-invalid="true" aria-describedby="signos-observacion-error" @enderror></textarea>
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
                        <div class="rm-signos__trend-section-title"><h6>Últimos registros</h6></div>
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
                        <p x-show="chartRows(active).length < 3">No hay suficientes registros previos para mostrar una tendencia.</p>
                        <div class="rm-signos__current-block" x-show="validPreview(active)">
                            <div class="rm-signos__current-left">
                                <span class="rm-signos__current-label">Valor sin guardar</span>
                                <div class="rm-signos__current-number">
                                    <strong x-text="previewLabel(active)"></strong>
                                    <small x-text="meta[active].unit"></small>
                                </div>
                            </div>
                            <div class="rm-signos__current-badge" x-show="previous(active)" x-text="change(active)"></div>
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
                        <div class="rm-signos__live-callout">
                            <i class="ph-bold ph-info" aria-hidden="true"></i>
                            <div><strong>Vista en vivo</strong><p>Los valores actuales son una vista previa hasta confirmar el registro.</p></div>
                        </div>
                    </div>
                </template>
            </div>
            <section class="rm-signos__validation-key" aria-label="Estados de validación">
                <h6><i class="ph-bold ph-check-square-offset" aria-hidden="true"></i> Estados de validación</h6>
                <ul>
                    <li><span data-tone="success">Verde</span> Dentro de la referencia u objetivo indicado</li>
                    <li><span data-tone="warning">Ámbar</span> Lectura que requiere revisión</li>
                    <li><span data-tone="danger">Rojo</span> Valor crítico según la regla vigente</li>
                    <li><span data-tone="neutral">Neutro</span> Sin clasificación aplicable</li>
                </ul>
                <p>La evaluación es orientativa hasta confirmar el registro. Una alerta clínica se crea solo cuando la regla lo requiere y el registro se guarda.</p>
            </section>
        </aside>
    </div>
</div>
