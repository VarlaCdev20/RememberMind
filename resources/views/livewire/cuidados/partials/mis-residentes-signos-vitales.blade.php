@php
    $nombreResponsable = auth()->user()->name ?? 'Enfermería';
    if (auth()->user()->nombres) {
        $nombreResponsable = trim(auth()->user()->nombres . ' ' . (auth()->user()->ap_paterno ?? ''));
    }
    $camposSignos = [
        ['key' => 'sis', 'error' => 'presion_sistolica', 'label' => 'Sistólica', 'wire' => 'signoSis', 'unit' => 'mmHg', 'max' => '999', 'step' => '1'],
        ['key' => 'dia', 'error' => 'presion_diastolica', 'label' => 'Diastólica', 'wire' => 'signoDia', 'unit' => 'mmHg', 'max' => '999', 'step' => '1'],
        ['key' => 'fc', 'error' => 'frecuencia_cardiaca', 'wire' => 'signoFC', 'unit' => 'lpm', 'max' => '9999', 'step' => '1'],
        ['key' => 'fr', 'error' => 'frecuencia_respiratoria', 'wire' => 'signoFR', 'unit' => 'rpm', 'max' => '9999', 'step' => '1'],
        ['key' => 'temp', 'error' => 'temperatura', 'wire' => 'signoTemp', 'unit' => '°C', 'max' => '999.9', 'step' => '0.1'],
        ['key' => 'sat', 'error' => 'saturacion_oxigeno', 'wire' => 'signoSat', 'unit' => '%', 'max' => '100', 'step' => '0.01'],
        ['key' => 'glucosa', 'error' => 'glucemia', 'wire' => 'signoGlucosa', 'unit' => 'mg/dL', 'max' => '999999.99', 'step' => '0.01'],
    ];
@endphp

<div class="rm-signos"
     x-data="rmSignosRegistro(@js($signosHistorial ?? []), {
             sis: @entangle('signoSis').live,
             dia: @entangle('signoDia').live,
             fc: @entangle('signoFC').live,
             fr: @entangle('signoFR').live,
             temp: @entangle('signoTemp').live,
             sat: @entangle('signoSat').live,
             glucosa: @entangle('signoGlucosa').live,
             obs: @entangle('signoObs').live,
     })"
     x-on:signos-revisar.window="review()"
     role="region"
     aria-label="Formulario de signos vitales">

    <!-- Contexto clínico del registro -->
    <section class="rm-signos__context" aria-label="Contexto de registro">
        <div class="rm-signos__context-item">
            <span class="rm-signos__context-icon rm-signos__context-icon--lock"><i class="ph-bold ph-lock-key" aria-hidden="true"></i></span>
            <div class="rm-signos__context-info">
                <span class="rm-signos__context-label">Registro clínico</span>
                <span class="rm-signos__context-sub">Se registrará al confirmar (hora del servidor)</span>
                <span class="rm-signos__context-time"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i> {{ now()->format('d M Y · H:i') }}</span>
            </div>
        </div>
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
        <div class="rm-signos__context-item">
            <span class="rm-signos__context-icon rm-signos__context-icon--bed"><i class="ph-bold ph-bed" aria-hidden="true"></i></span>
            <div class="rm-signos__context-info">
                <span class="rm-signos__context-label">Habitación</span>
                <strong class="rm-signos__context-value">{{ $detalleResidente['habitacion_texto'] ?? 'Sin habitación asignada' }} · {{ $detalleResidente['cama_texto'] ?? 'Sin cama asignada' }}</strong>
            </div>
        </div>
    </section>

    <section class="rm-signos__objectives" aria-labelledby="signos-objectives-title">
        <div class="rm-signos__objectives-header">
            <i class="ph-bold ph-notebook" aria-hidden="true"></i>
            <div>
                <strong id="signos-objectives-title">Objetivos clínicos indicados por médico</strong>
                <p>Sin objetivo individual configurado.</p>
            </div>
        </div>
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
    <div class="rm-signos__workspace">
        <div class="rm-signos__main-col">
            <div class="rm-signos__cards-grid">
                <!-- CARD 1: Presión arterial -->
                <section class="rm-signos__card rm-signos__card--pa" x-bind:class="{ 'rm-signos__card--active': active === 'pa' }" aria-labelledby="signos-pa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-pa-title"><i class="ph-bold ph-heart text-rose-500" aria-hidden="true"></i> Presión arterial (PA)</h5>
                    </div>
                    <div class="rm-signos__pa-fields">
                        @foreach(array_slice($camposSignos, 0, 2) as $campo)
                            @include('livewire.cuidados.partials.mis-residentes-signos-campo')
                        @endforeach
                    </div>
                </section>

                <!-- CARD 2: Pulso -->
                <section class="rm-signos__card rm-signos__card--fc" x-bind:class="{ 'rm-signos__card--active': active === 'fc' }" aria-labelledby="signos-fc-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fc-title"><i class="ph-bold ph-heart text-rose-500" aria-hidden="true"></i> Pulso</h5>
                        <span class="rm-signos__unit-badge">bpm</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[2]])
                </section>

                <!-- CARD 3: Respiración -->
                <section class="rm-signos__card rm-signos__card--fr" x-bind:class="{ 'rm-signos__card--active': active === 'fr' }" aria-labelledby="signos-fr-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fr-title"><i class="ph-bold ph-lungs text-blue-500" aria-hidden="true"></i> Respiración</h5>
                        <span class="rm-signos__unit-badge">rpm</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[3]])
                </section>

                <!-- CARD 4: Temperatura -->
                <section class="rm-signos__card rm-signos__card--temp" x-bind:class="{ 'rm-signos__card--active': active === 'temp' }" aria-labelledby="signos-temp-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-temp-title"><i class="ph-bold ph-thermometer text-rose-500" aria-hidden="true"></i> Temperatura</h5>
                        <span class="rm-signos__unit-badge">°C</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[4]])
                </section>

                <!-- CARD 5: Saturación de oxígeno (SpO₂) -->
                <section class="rm-signos__card rm-signos__card--sat" x-bind:class="{ 'rm-signos__card--active': active === 'sat' }" aria-labelledby="signos-sat-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-sat-title"><i class="ph-bold ph-drop text-sky-500" aria-hidden="true"></i> Saturación de oxígeno <small>(SpO₂)</small></h5>
                    </div>
                    <div class="rm-signos__sat-fields">
                        @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[5]])
                    </div>
                </section>

                <!-- CARD 6: Glucemia -->
                <section class="rm-signos__card rm-signos__card--glucosa" x-bind:class="{ 'rm-signos__card--active': active === 'glucosa' }" aria-labelledby="signos-glucosa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-glucosa-title"><i class="ph-bold ph-drop-half text-sky-500" aria-hidden="true"></i> Glucemia</h5>
                        <span class="rm-signos__unit-badge">mg/dL</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[6]])
                </section>
            </div>

            <!-- OBSERVACIONES -->
            <section class="rm-signos__observations" aria-labelledby="signos-obs-title">
                <div class="rm-signos__obs-header">
                    <h5 id="signos-obs-title"><i class="ph-bold ph-chat-circle-dots text-amber-600" aria-hidden="true"></i> Observaciones</h5>
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
                    <h5 id="signos-tendencia-title">Tendencia en vivo</h5>
                    <p aria-live="polite" x-text="active ? 'Evolución de ' + meta[active].label : 'Selecciona una medición para consultar su evolución.'"></p>
                </div>
                <button type="button" class="rm-signos__trend-nav-btn" aria-label="Siguiente medición" @click="const keys = Object.keys(meta); active = keys[(keys.indexOf(active) + 1) % keys.length]"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button>
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
                                    <svg class="rm-signos__chart-svg" viewBox="0 0 300 140" role="img" aria-label="Tendencia de las mediciones reales">
                                        <polyline class="rm-signos__chart-path" fill="none" x-bind:points="chartPoints(active)"></polyline>
                                        <template x-for="(row, index) in chartMarkers(active)" :key="index">
                                            <circle class="rm-signos__chart-dot" :class="row.preview ? 'rm-signos__chart-dot--current' : 'rm-signos__chart-dot--history'" x-bind:cx="row.x" x-bind:cy="row.y" r="4.5"></circle>
                                        </template>
                                    </svg>
                                </div>
                            </div>
                        </template>
                        <p x-show="chartRows(active).length < 3">La gráfica aparecerá cuando haya tres mediciones de este residente.</p>
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
                </template>
            </div>
        </aside>
    </div>
</div>
