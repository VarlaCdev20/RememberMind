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
     }, @js($limitesTecnicos), @js($signosBandasObjetivo), @js($tonosPorTarjeta->all()))"
     x-on:signos-validacion-fallida.window="$nextTick(() => review())"
     x-on:signos-evaluacion-actualizada.window="syncEvaluation($event.detail)"
     role="region"
     aria-label="Formulario de signos vitales">

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
    @if($resumenAlteraciones->isNotEmpty())
        <div class="rm-signos__evaluation-summary" data-tone="{{ $resumenAlteraciones->contains('severidad', 'CRITICO') ? 'danger' : ($resumenAlteraciones->contains('severidad', 'ALTO') ? 'high' : 'warning') }}" role="status" aria-live="polite">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <span>
                <strong>{{ $resumenAlteraciones->contains('severidad', 'CRITICO') ? $resumenAlteraciones->where('severidad', 'CRITICO')->count().' MEDICIÓN CRÍTICA'.($resumenAlteraciones->where('severidad', 'CRITICO')->count() > 1 ? 'S' : '') : $resumenEtiquetas }}</strong>
                <small>{{ $resumenAlteraciones->contains('severidad', 'CRITICO') ? 'Revisa la lectura antes de registrar.' : ($resumenAlteraciones->contains('severidad', 'ALTO') ? 'Hay una medición significativamente alterada.' : 'Hay una medición que requiere revisión.') }}</small>
            </span>
            @if($primerParametroAlterado)
                <button type="button" x-on:click="active = @js($primerParametroAlterado); $nextTick(() => { const card = document.getElementById('signos-' + active + '-title')?.closest('.rm-signos__card'); card?.scrollIntoView({ behavior: 'smooth', block: 'center' }); card?.querySelector('input')?.focus({ preventScroll: true }); })">Ver medición</button>
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
                <section class="rm-signos__card rm-signos__card--sat" x-bind:class="{ 'rm-signos__card--active': active === 'sat' }" x-bind:data-tone="toneOf('sat')" wire:loading.class="rm-signos__card--evaluating" wire:target="signoSat" aria-labelledby="signos-sat-title">
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
                        <template x-if="chartRows(active).length >= 2">
                            <div class="rm-signos__chart">
                                <div class="rm-signos__chart-canvas-wrap">
                                    <svg class="rm-signos__chart-svg" viewBox="0 0 300 140" role="img" aria-label="Tendencia de las mediciones del residente y vista previa sin guardar">
                                        <template x-for="(band, index) in chartBands(active)" :key="'band-' + index">
                                            <rect class="rm-signos__chart-band" :class="band.field === 'dia' ? 'rm-signos__chart-band--dia' : ''" x="18" width="264" x-bind:y="band.y" x-bind:height="band.height" x-bind:aria-label="band.label"><title x-text="band.label"></title></rect>
                                        </template>
                                        <polyline class="rm-signos__chart-path" fill="none" x-bind:points="chartPoints(active)"></polyline>
                                        <polyline x-show="active === 'pa'" class="rm-signos__chart-path rm-signos__chart-path--dia" fill="none" x-bind:points="chartPoints(active, 'dia')"></polyline>
                                        <template x-for="(row, index) in chartMarkers(active)" :key="index">
                                            <circle class="rm-signos__chart-dot" :class="row.preview ? 'rm-signos__chart-dot--current' : 'rm-signos__chart-dot--history'" :data-tone="row.preview ? toneOf(active) : 'history'" x-bind:cx="row.x" x-bind:cy="row.y" r="5" tabindex="0" x-bind:aria-label="row.date + ': ' + row.markerLabel"><title x-text="row.date + ': ' + row.markerLabel"></title></circle>
                                        </template>
                                        <template x-for="(row, index) in active === 'pa' ? chartMarkers(active, 'dia') : []" :key="'dia-' + index">
                                            <circle class="rm-signos__chart-dot rm-signos__chart-dot--dia" :class="row.preview ? 'rm-signos__chart-dot--current' : 'rm-signos__chart-dot--history'" :data-tone="row.preview ? toneOf(active) : 'history'" x-bind:cx="row.x" x-bind:cy="row.y" r="5" tabindex="0" x-bind:aria-label="row.date + ': ' + row.markerLabel"><title x-text="row.date + ': ' + row.markerLabel"></title></circle>
                                        </template>
                                    </svg>
                                </div>
                                <p class="rm-signos__chart-legend" x-show="active === 'pa'"><span>Sistólica</span><span>Diastólica</span></p>
                                <p class="rm-signos__chart-legend"><span>Histórico</span><span>Lectura actual sin guardar</span></p>
                            </div>
                        </template>
                        <p x-show="chartRows(active).length < 2" x-text="emptyHistoryMessage(active)"></p>
                        <h6 class="rm-signos__trend-step-title" x-show="validPreview(active) && previous(active)">Comparación con la última medición</h6>
                        <div class="rm-signos__current-block" x-show="validPreview(active)">
                            <div class="rm-signos__current-left">
                                <span class="rm-signos__current-label">Valor actual · Sin guardar</span>
                                <div class="rm-signos__current-number">
                                    <strong x-text="previewLabel(active)"></strong>
                                    <small x-text="meta[active].unit"></small>
                                </div>
                            </div>
                            <span class="rm-signos__current-status" :data-tone="toneOf(active)" x-text="toneLabel(active)"></span>
                            <div class="rm-signos__current-compare" x-show="previous(active)">
                                <span>Anterior <strong x-text="previous(active) ? labelOf(previous(active), active) + ' ' + meta[active].unit : ''"></strong></span>
                                <span>Cambio <strong x-text="change(active)"></strong></span>
                            </div>
                        </div>
                        <div class="rm-signos__history-list" x-show="records(active).length > 0">
                            <div class="rm-signos__history-heading"><h6>Últimos registros</h6>@can('enfermeria.ver_ficha_paciente')<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente'], 'tab' => 'signos']) }}">Ver todo</a>@endcan</div>
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
                        <p class="rm-signos__preview-note" x-show="validPreview(active)">La lectura actual es una vista previa hasta confirmar el registro.</p>
                    </div>
                </template>
            </div>
            @foreach($evaluacionesPorTarjeta as $claveTarjeta => $resultadosTarjeta)
                <div class="rm-signos__interpretation" x-show="active === @js($claveTarjeta) && evaluationCurrent(@js($claveTarjeta)) && !hasCardError(@js($claveTarjeta))" x-cloak>
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
                    @endforeach
                    @foreach($resultadosTarjeta as $resultado)
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
    <section class="rm-signos__observations" aria-labelledby="signos-obs-title">
        <div class="rm-signos__obs-header"><h5 id="signos-obs-title"><i class="ph-bold ph-chat-circle-dots" aria-hidden="true"></i> Observaciones</h5></div>
        <label for="signos-observacion">Contexto de la medición (opcional)</label>
        <textarea id="signos-observacion" wire:model="signoObs" x-model="values.obs" maxlength="5000" rows="2" placeholder="Añade contexto relevante sobre la medición, síntomas o condiciones observadas, si corresponde." @error('observacion') aria-invalid="true" aria-describedby="signos-observacion-error" @enderror></textarea>
        <div class="rm-signos__obs-footer">
            @error('observacion') <p id="signos-observacion-error" class="rm-signos__error" role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> {{ $message }}</p> @enderror
            <span class="rm-signos__char-count" aria-hidden="true" x-text="String(values.obs ?? '').length + '/5000'"></span>
        </div>
    </section>
</div>
