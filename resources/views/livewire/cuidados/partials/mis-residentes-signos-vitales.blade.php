@php
    $nombreResponsable = auth()->user()->name ?? 'Enfermería';
    if (auth()->user()->nombres) {
        $nombreResponsable = trim(auth()->user()->nombres . ' ' . (auth()->user()->ap_paterno ?? ''));
    }
@endphp

<div class="rm-signos"
     x-data="signosVitalesRegistro({
         historial: @js($signosHistorial ?? []),
         objetivos: @js($detalleResidente['objetivos_clinicos'] ?? []),
         valoresActuales: {
             sis: @entangle('signoSis').live,
             dia: @entangle('signoDia').live,
             fc: @entangle('signoFC').live,
             fr: @entangle('signoFR').live,
             temp: @entangle('signoTemp').live,
             sat: @entangle('signoSat').live,
             glucosa: @entangle('signoGlucosa').live,
             obs: @entangle('signoObs').live,
         }
     })"
     x-on:signos-revisar.window="abrirRevisionMediciones()"
     role="region"
     aria-label="Formulario de signos vitales">

    <!-- 4. FRNJZA DE CONTEXTO CLÉNICO (4 COLUMNAS) -->
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
                <strong class="rm-signos__context-value">{{ $signosContextoTurno['nombre'] ?? 'Mañana' }} <span class="rm-signos__context-hours">· {{ $signosContextoTurno['horario'] ?? '07:00 – 15:00' }}</span></strong>
            </div>
        </div>
        <div class="rm-signos__context-item">
            <span class="rm-signos__context-icon rm-signos__context-icon--bed"><i class="ph-bold ph-bed" aria-hidden="true"></i></span>
            <div class="rm-signos__context-info">
                <span class="rm-signos__context-label">Habitación</span>
                <strong class="rm-signos__context-value">{{ $detalleResidente['habitacion_texto'] ?? 'HAB-101' }} · {{ $detalleResidente['cama_texto'] ?? 'CAMA-1A' }}</strong>
            </div>
        </div>
    </section>

<section class="rm-signos__objectives" aria-labelledby="signos-objectives-title">
        <div class="rm-signos__objectives-header">
            <span class="rm-signos__objectives-target-icon"><i class="ph-bold ph-notebook" aria-hidden="true"></i></span>
            <strong id="signos-objectives-title">Objetivos clínicos indicados por médico <small>(solo lectura)</small></strong>
        </div>
        <div class="rm-signos__objectives-grid">
            <div class="rm-signos__objective-chip">
                <span class="rm-signos__objective-icon rm-signos__objective-icon--mint"><i class="ph-bold ph-heart" aria-hidden="true"></i></span>
                <div class="rm-signos__objective-info">
                    <strong>Presión arterial</strong>
                    <span>110 – 135 / 65 – 80&#160;mmHg</span>
                </div>
            </div>
            <div class="rm-signos__objective-chip">
                <span class="rm-signos__objective-icon rm-signos__objective-icon--red"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i></span>
                <div class="rm-signos__objective-info">
                    <strong>Pulso</strong>
                    <span>60 – 85&#160;lpm</span>
                </div>
            </div>
            <div class="rm-signos__objective-chip">
                <span class="rm-signos__objective-icon rm-signos__objective-icon--blue"><i class="ph-bold ph-lungs" aria-hidden="true"></i></span>
                <div class="rm-signos__objective-info">
                    <strong>Respiración</strong>
                    <span>14 – 20&#160;rpm</span>
                </div>
            </div>
            <div class="rm-signos__objective-chip">
                <span class="rm-signos__objective-icon rm-signos__objective-icon--amber"><i class="ph-bold ph-thermometer" aria-hidden="true"></i></span>
                <div class="rm-signos__objective-info">
                    <strong>Temperatura</strong>
                    <span>36.0 – 37.5°C <small>(Basal: 36.4°C)</small></span>
                </div>
            </div>
            <div class="rm-signos__objective-chip">
                <span class="rm-signos__objective-icon rm-signos__objective-icon--blue"><i class="ph-bold ph-drop" aria-hidden="true"></i></span>
                <div class="rm-signos__objective-info">
                    <strong>SpO₂</strong>
                    <span>92 – 96&#160;%</span>
                </div>
            </div>
            <div class="rm-signos__objective-chip">
                <span class="rm-signos__objective-icon rm-signos__objective-icon--mint"><i class="ph-bold ph-drop-half" aria-hidden="true"></i></span>
                <div class="rm-signos__objective-info">
                    <strong>Glucemia <small>(antes de comida)</small></strong>
                    <span>90 – 150&#160;mg/dL</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ERROR GENERAL -->
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
                        <div class="rm-signos__toggle-group" role="group" aria-label="Tipo de medición de presión arterial">
                            <span class="rm-signos__toggle-label">Tipo de medición:</span>
                            <button type="button" class="rm-signos__toggle-btn" :class="{ 'is-active': paTipo === 'habitual' }" @click="paTipo = 'habitual'">Habitual</button>
                            <button type="button" class="rm-signos__toggle-btn" :class="{ 'is-active': paTipo === 'ortostatica' }" @click="paTipo = 'ortostatica'">Ortostática</button>
                        </div>
                    </div>
                    <div class="rm-signos__pa-fields">
                        @foreach(array_slice($camposSignos, 0, 2) as $campo)
                            @include('livewire.cuidados.partials.mis-residentes-signos-campo')
                        @endforeach
                    </div>
                    <div class="rm-signos__card-status" x-show="validPreview('pa')">
                        <template x-if="Number(values.sis) >= 110 && Number(values.sis) <= 135 && Number(values.dia) >= 65 && Number(values.dia) <= 80">
                            <span class="rm-signos__status-tag rm-signos__status-tag--in-range"><i class="ph-bold ph-check" aria-hidden="true"></i> En rango del objetivo</span>
                        </template>
                        <template x-if="Number(values.sis) < 110 || Number(values.dia) < 65">
                            <span class="rm-signos__status-tag rm-signos__status-tag--below"><i class="ph-bold ph-info" aria-hidden="true"></i> Por debajo del objetivo indicado</span>
                        </template>
                        <template x-if="Number(values.sis) > 135 || Number(values.dia) > 80">
                            <span class="rm-signos__status-tag rm-signos__status-tag--above"><i class="ph-bold ph-info" aria-hidden="true"></i> Por encima del objetivo indicado</span>
                        </template>
                    </div>
                    <div class="rm-signos__range-bar" x-show="validPreview('pa')">
                        <div class="rm-signos__range-track">
                            <div class="rm-signos__range-target" style="left: 20%; width: 50%;"></div>
                            <div class="rm-signos__range-marker" :class="(Number(values.sis) >= 110 && Number(values.sis) <= 135) ? 'is-in-range' : 'is-attention'" :style="'left: ' + Math.min(Math.max((Number(values.sis) - 80) / 80 * 100, 4), 96) + '%'"></div>
                        </div>
                        <div class="rm-signos__range-ticks">
                            <span :class="(Number(values.sis) >= 110 && Number(values.sis) <= 135) ? 'is-in-range' : 'is-attention'" x-text="values.sis"></span>
                            <span>110</span>
                            <span>135</span>
                        </div>
                    </div>
                    <p class="rm-signos__objective-footer">Objetivo médico: 110 – 135 / 65 – 80&#160;mmHg</p>
                </section>

                <!-- CARD 2: Pulso -->
                <section class="rm-signos__card rm-signos__card--fc" x-bind:class="{ 'rm-signos__card--active': active === 'fc' }" aria-labelledby="signos-fc-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fc-title"><i class="ph-bold ph-heart text-rose-500" aria-hidden="true"></i> Pulso</h5>
                        <span class="rm-signos__unit-badge">bpm</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[2]])
                    <div class="rm-signos__card-status" x-show="validPreview('fc')">
                        <template x-if="Number(values.fc) >= 60 && Number(values.fc) <= 85">
                            <span class="rm-signos__status-tag rm-signos__status-tag--in-range"><i class="ph-bold ph-check" aria-hidden="true"></i> En rango del objetivo</span>
                        </template>
                        <template x-if="Number(values.fc) < 60">
                            <span class="rm-signos__status-tag rm-signos__status-tag--below"><i class="ph-bold ph-info" aria-hidden="true"></i> Por debajo del objetivo indicado (60 lpm)</span>
                        </template>
                        <template x-if="Number(values.fc) > 85">
                            <span class="rm-signos__status-tag rm-signos__status-tag--above"><i class="ph-bold ph-info" aria-hidden="true"></i> Por encima del objetivo indicado (85 lpm)</span>
                        </template>
                    </div>
                    <div class="rm-signos__range-bar" x-show="validPreview('fc')">
                        <div class="rm-signos__range-track">
                            <div class="rm-signos__range-target" style="left: 30%; width: 45%;"></div>
                            <div class="rm-signos__range-marker" :class="(Number(values.fc) >= 60 && Number(values.fc) <= 85) ? 'is-in-range' : 'is-attention'" :style="'left: ' + Math.min(Math.max((Number(values.fc) - 40) / 70 * 100, 4), 96) + '%'"></div>
                        </div>
                        <div class="rm-signos__range-ticks">
                            <span :class="(Number(values.fc) >= 60 && Number(values.fc) <= 85) ? 'is-in-range' : 'is-attention'" x-text="values.fc"></span>
                            <span>60</span>
                            <span>85</span>
                        </div>
                    </div>
                    <p class="rm-signos__objective-footer">Objetivo médico: 60 – 85&#160;lpm</p>
                </section>

                <!-- CARD 3: Respiración -->
                <section class="rm-signos__card rm-signos__card--fr" x-bind:class="{ 'rm-signos__card--active': active === 'fr' }" aria-labelledby="signos-fr-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-fr-title"><i class="ph-bold ph-lungs text-blue-500" aria-hidden="true"></i> Respiración</h5>
                        <span class="rm-signos__unit-badge">rpm</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[3]])
                    <div class="rm-signos__card-status" x-show="validPreview('fr')">
                        <template x-if="Number(values.fr) >= 14 && Number(values.fr) <= 20">
                            <span class="rm-signos__status-tag rm-signos__status-tag--in-range"><i class="ph-bold ph-check" aria-hidden="true"></i> En rango del objetivo</span>
                        </template>
                        <template x-if="Number(values.fr) < 14">
                            <span class="rm-signos__status-tag rm-signos__status-tag--below"><i class="ph-bold ph-info" aria-hidden="true"></i> Por debajo del objetivo indicado (14 rpm)</span>
                        </template>
                        <template x-if="Number(values.fr) > 20">
                            <span class="rm-signos__status-tag rm-signos__status-tag--above"><i class="ph-bold ph-info" aria-hidden="true"></i> Por encima del objetivo indicado (20 rpm)</span>
                        </template>
                    </div>
                    <div class="rm-signos__range-bar" x-show="validPreview('fr')">
                        <div class="rm-signos__range-track">
                            <div class="rm-signos__range-target" style="left: 35%; width: 40%;"></div>
                            <div class="rm-signos__range-marker" :class="(Number(values.fr) >= 14 && Number(values.fr) <= 20) ? 'is-in-range' : 'is-attention'" :style="'left: ' + Math.min(Math.max((Number(values.fr) - 8) / 18 * 100, 4), 96) + '%'"></div>
                        </div>
                        <div class="rm-signos__range-ticks">
                            <span>14</span>
                            <span :class="(Number(values.fr) >= 14 && Number(values.fr) <= 20) ? 'is-in-range' : 'is-attention'" x-text="values.fr"></span>
                            <span>20</span>
                        </div>
                    </div>
                    <p class="rm-signos__objective-footer">Objetivo médico: 14 – 20&#160;rpm</p>
                </section>

                <!-- CARD 4: Temperatura -->
                <section class="rm-signos__card rm-signos__card--temp" x-bind:class="{ 'rm-signos__card--active': active === 'temp' }" aria-labelledby="signos-temp-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-temp-title"><i class="ph-bold ph-thermometer text-rose-500" aria-hidden="true"></i> Temperatura</h5>
                        <span class="rm-signos__unit-badge">°C</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[4]])
                    <div class="rm-signos__card-status" x-show="validPreview('temp')">
                        <template x-if="Number(values.temp) >= 36.0 && Number(values.temp) <= 37.5">
                            <span class="rm-signos__status-tag rm-signos__status-tag--in-range"><i class="ph-bold ph-check" aria-hidden="true"></i> En rango del objetivo</span>
                        </template>
                        <template x-if="Number(values.temp) < 36.0">
                            <span class="rm-signos__status-tag rm-signos__status-tag--below"><i class="ph-bold ph-info" aria-hidden="true"></i> Por debajo del objetivo mínimo</span>
                        </template>
                        <template x-if="Number(values.temp) > 37.5">
                            <span class="rm-signos__status-tag rm-signos__status-tag--above"><i class="ph-bold ph-info" aria-hidden="true"></i> Por encima del objetivo máximo</span>
                        </template>
                    </div>
                    <div class="rm-signos__range-bar" x-show="validPreview('temp')">
                        <div class="rm-signos__range-track">
                            <div class="rm-signos__range-target" style="left: 25%; width: 50%;"></div>
                            <div class="rm-signos__range-marker" :class="(Number(values.temp) >= 36.0 && Number(values.temp) <= 37.5) ? 'is-in-range' : 'is-attention'" :style="'left: ' + Math.min(Math.max((Number(values.temp) - 35.0) / 3.5 * 100, 4), 96) + '%'"></div>
                        </div>
                        <div class="rm-signos__range-ticks">
                            <span>36.0</span>
                            <span>37.5</span>
                            <span :class="(Number(values.temp) >= 36.0 && Number(values.temp) <= 37.5) ? 'is-in-range' : 'is-attention'" x-text="values.temp"></span>
                        </div>
                    </div>
                    <p class="rm-signos__objective-footer">Objetivo médico: 36.0 – 37.5°C · Basal del residente: 36.4°C</p>
                </section>

                <!-- CARD 5: Saturación de oxígeno (SpO₂) -->
                <section class="rm-signos__card rm-signos__card--sat" x-bind:class="{ 'rm-signos__card--active': active === 'sat' }" aria-labelledby="signos-sat-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-sat-title"><i class="ph-bold ph-drop text-sky-500" aria-hidden="true"></i> Saturación de oxígeno <small>(SpO₂)</small></h5>
                        <div class="rm-signos__toggle-group" role="group" aria-label="Oxígeno suplementario">
                            <span class="rm-signos__toggle-label">Oxígeno suplementario:</span>
                            <button type="button" class="rm-signos__toggle-btn rm-signos__toggle-btn--sage" :class="{ 'is-active': satOxigeno }" @click="satOxigeno = true">Sí</button>
                            <button type="button" class="rm-signos__toggle-btn" :class="{ 'is-active': !satOxigeno }" @click="satOxigeno = false">No</button>
                        </div>
                    </div>
                    <div class="rm-signos__sat-fields" :class="{ 'has-supplemental': satOxigeno }">
                        @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[5]])
                        <div class="rm-signos__field rm-signos__field--flow" x-show="satOxigeno">
                            <label for="signos-flujo-o2">Flujo (L/min)</label>
                            <div class="rm-signos__input-wrap">
                                <input id="signos-flujo-o2" type="number" step="0.5" min="0" max="15" x-model="satFlujo" placeholder="2.0">
                            </div>
                        </div>
                    </div>
                    <div class="rm-signos__card-status" x-show="validPreview('sat')">
                        <template x-if="Number(values.sat) >= 92 && Number(values.sat) <= 96">
                            <span class="rm-signos__status-tag rm-signos__status-tag--in-range"><i class="ph-bold ph-check" aria-hidden="true"></i> En rango del objetivo</span>
                        </template>
                        <template x-if="Number(values.sat) < 92">
                            <span class="rm-signos__status-tag rm-signos__status-tag--below"><i class="ph-bold ph-info" aria-hidden="true"></i> Por debajo del objetivo indicado (92 %)</span>
                        </template>
                        <template x-if="Number(values.sat) > 96">
                            <span class="rm-signos__status-tag rm-signos__status-tag--above"><i class="ph-bold ph-info" aria-hidden="true"></i> Por encima del objetivo indicado</span>
                        </template>
                    </div>
                    <div class="rm-signos__range-bar" x-show="validPreview('sat')">
                        <div class="rm-signos__range-track">
                            <div class="rm-signos__range-target" style="left: 45%; width: 35%;"></div>
                            <div class="rm-signos__range-marker" :class="(Number(values.sat) >= 92 && Number(values.sat) <= 96) ? 'is-in-range' : 'is-attention'" :style="'left: ' + Math.min(Math.max((Number(values.sat) - 80) / 20 * 100, 4), 96) + '%'"></div>
                        </div>
                        <div class="rm-signos__range-ticks">
                            <span :class="(Number(values.sat) >= 92 && Number(values.sat) <= 96) ? 'is-in-range' : 'is-attention'" x-text="values.sat"></span>
                            <span>92</span>
                            <span>96</span>
                        </div>
                    </div>
                    <p class="rm-signos__objective-footer">Objetivo médico: 92 – 96&#160;%</p>
                </section>

                <!-- CARD 6: Glucemia -->
                <section class="rm-signos__card rm-signos__card--glucosa" x-bind:class="{ 'rm-signos__card--active': active === 'glucosa' }" aria-labelledby="signos-glucosa-title">
                    <div class="rm-signos__card-header">
                        <h5 id="signos-glucosa-title"><i class="ph-bold ph-drop-half text-sky-500" aria-hidden="true"></i> Glucemia</h5>
                        <span class="rm-signos__unit-badge">mg/dL</span>
                    </div>
                    @include('livewire.cuidados.partials.mis-residentes-signos-campo', ['campo' => $camposSignos[6]])
                    <div class="rm-signos__field rm-signos__field--contexto">
                        <label for="signos-contexto-glucosa">Contexto de la medición</label>
                        <select id="signos-contexto-glucosa" class="rm-signos__select" x-model="glucosaContexto">
                            <option value="antes">Antes de comida</option>
                            <option value="despues">Después de comida</option>
                            <option value="ayunas">En ayunas</option>
                            <option value="aleatorio">Aleatorio / Control</option>
                        </select>
                    </div>
                    <div class="rm-signos__card-status" x-show="validPreview('glucosa')">
                        <template x-if="Number(values.glucosa) >= 90 && Number(values.glucosa) <= 150">
                            <span class="rm-signos__status-tag rm-signos__status-tag--in-range"><i class="ph-bold ph-check" aria-hidden="true"></i> En rango del objetivo</span>
                        </template>
                        <template x-if="Number(values.glucosa) < 90">
                            <span class="rm-signos__status-tag rm-signos__status-tag--below"><i class="ph-bold ph-info" aria-hidden="true"></i> Por debajo del objetivo indicado (90 mg/dL)</span>
                        </template>
                        <template x-if="Number(values.glucosa) > 150">
                            <span class="rm-signos__status-tag rm-signos__status-tag--above"><i class="ph-bold ph-info" aria-hidden="true"></i> Por encima del objetivo indicado (150 mg/dL)</span>
                        </template>
                    </div>
                    <div class="rm-signos__range-bar" x-show="validPreview('glucosa')">
                        <div class="rm-signos__range-track">
                            <div class="rm-signos__range-target" style="left: 30%; width: 45%;"></div>
                            <div class="rm-signos__range-marker" :class="(Number(values.glucosa) >= 90 && Number(values.glucosa) <= 150) ? 'is-in-range' : 'is-attention'" :style="'left: ' + Math.min(Math.max((Number(values.glucosa) - 50) / 150 * 100, 4), 96) + '%'"></div>
                        </div>
                        <div class="rm-signos__range-ticks">
                            <span :class="(Number(values.glucosa) >= 90 && Number(values.glucosa) <= 150) ? 'is-in-range' : 'is-attention'" x-text="values.glucosa"></span>
                            <span>90</span>
                            <span>150</span>
                        </div>
                    </div>
                    <p class="rm-signos__objetive-footer">Objetivo médico: 90 – 150&#160;mg/dL</p>
                </section>
            </div>

            <!-- OBSERVACIONES -->
            <section class="rm-signos__observations" aria-labelledby="signos-obs-title">
                <div class="rm-signos__obs-header">
                    <h5 id="signos-obs-title"><i class="ph-bold ph-chat-circle-dots text-amber-600" aria-hidden="true"></i> Observaciones</h5>
                </div>
                <label class="sr-only" for="signos-observacion">Observación clínica</label>
                <textarea id="signos-observacion" wire:model="signoObs" maxlength="500" rows="2" placeholder="Añade contexto relevante sobre la medición, síntomas, condiciones, etc." @error('observacion') aria-invalid="true" aria-describedby="signos-observacion-error" @enderror></textarea>
                <div class="rm-signos__obs-footer">
                    @error('observacion') <p id="signos-observacion-error" class="rm-signos__error" role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> {{ $message }}</p> @enderror
                    <span class="rm-signos__char-count" aria-hidden="true">0/500</span>
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
                        <!-- Subtítulo de gráfico -->
                        <div class="rm-signos__trend-section-title">
                            <h6>Úzltimos registros</h6>
                        </div>

                        <!-- Gráfico de tendencia -->
                        <div class="rm-signos__chart">
                            <div class="rm-signos__chart-header-sub">
                                <span class="rm-signos__chart-target-badge" x-show="active === 'sat'">Rango objetivo 92 – 96 %</span>
                                <span class="rm-signos__chart-target-badge" x-show="active === 'fc'">Rango objetivo 60 – 85 lpm</span>
                                <span class="rm-signos__chart-target-badge" x-show="active === 'fr'">Rango objetivo 14 – 20 rpm</span>
                                <span class="rm-signos__chart-target-badge" x-show="active === 'temp'">Rango objetivo 36.0 – 37.5 °C</span>
                                <span class="rm-signos__chart-target-badge" x-show="active === 'glucosa'">Rango objetivo 90 – 150 mg/dL</span>
                                <span class="rm-signos__chart-target-badge" x-show="active === 'pa'">Rango objetivo 110 – 135 mmHg</span>
                            </div>
                            <div class="rm-signos__chart-canvas-wrap">
                                <div class="rm-signos__chart-y-axis">
                                    <span>100</span>
                                    <span>95</span>
                                    <span>90</span>
                                    <span>85</span>
                                    <span>80</span>
                                </div>
                                <svg class="rm-signos__chart-svg" viewBox="0 0 250 105" role="img" aria-label="Gráfica de tendencia">
                                    <!-- Horizontal grid lines -->
                                    <line x1="0" y1="15" x2="250" y2="15" class="rm-signos__chart-gridline" />
                                    <line x1="0" y1="35" x2="250" y2="35" class="rm-signos__chart-gridline" />
                                    <line x1="0" y1="55" x2="250" y2="55" class="rm-signos__chart-gridline" />
                                    <line x1="0" y1="75" x2="250" y2="75" class="rm-signos__chart-gridline" />
                                    <line x1="0" y1="95" x2="250" y2="95" class="rm-signos__chart-gridline" />


                                    <!-- Trend line -->
                                    <path d="M 25 35 L 125 50 L 225 72" class="rm-signos__chart-path" />


                                    <!-- Data points -->
                                    <circle cx="25" cy="35" r="4.5" class="rm-signos__chart-dot rm-signos__chart-dot--history" />
                                    <text x="25" y="48" class="rm-signos__chart-val">94</text>


                                    <circle cx="125" cy="50" r="4.5" class="rm-signos__chart-dot rm-signos__chart-dot--history" />
                                    <text x="125" y="63" class="rm-signos__chart-val">92</text>


                                    <circle cx="225" cy="72" r="4.5" class="rm-signos__chart-dot rm-signos__chart-dot--current" />
                                    <text x="225" y="85" class="rm-signos__chart-val rm-signos__chart-val--current">89</text>
                                </svg>
                            </div>
                            <div class="rm-signos__chart-x-axis">
                                <span>30 sep 18:20</span>
                                <span>01 oct 08:10</span>
                                <span>01 oct 10:24</span>
                            </div>
                        </div>


                        <!-- Valor actual & Variación -->
                        <div class="rm-signos__current-block">
                            <div class="rm-signos__current-left">
                                <span class="rm-signos__current-label">Valor actual</span>
                                <div class="rm-signos__current-number">
                                    <strong x-text="validPreview(active) ? previewLabel(active) : '89'">89</strong>
                                    <small x-text="meta[active].unit">%</small>
                                </div>
                            </div>
                            <div class="rm-signos__current-badge rm-signos__current-badge--down">
                                <i class="ph-bold ph-arrow-down" aria-hidden="true"></i> - 3 % <span class="rm-signos__badge-vs">vs. anterior (92 %)</span>
                            </div>
                        </div>


                        <!-- Últimos 3 registros lista -->
                        <div class="rm-signos__history-list">
                            <h6>Úzltimos 3 registros</h6>
                            <div class="rm-signos__history-item rm-signos__history-item--current">
                                <div class="rm-signos__history-date">
                                    <span class="rm-signos__history-dot rm-signos__history-dot--red"></span>
                                    <span>01 oct 2026 · 10:24</span>
                                </div>
                                <strong class="rm-signos__history-val rm-signos__history-val--red">89 %</strong>
                            </div>
                            <div class="rm-signos__history-item">
                                <div class="rm-signos__history-date">
                                    <span class="rm-signos__history-dot rm-signos__history-dot--slate"></span>
                                    <span>01 oct 2026 · 08:10</span>
                                </div>
                                <strong class="rm-signos__history-val">92 %</strong>
                            </div>
                            <div class="rm-signos__history-item">
                                <div class="rm-signos__history-date">
                                    <span class="rm-signos__history-dot rm-signos__history-dot--slate"></span>
                                    <span>30 sep 2026 · 18:20</span>
                                </div>
                                <strong class="rm-signos__history-val">94 %</strong>
                            </div>
                        </div>


                        <!-- Info Callout Vista en vivo -->
                        <div class="rm-signos__live-callout">
                            <i class="ph-bold ph-info" aria-hidden="true"></i>
                            <div>
                                <strong>Vista en vivo</strong>
                                <p>El gráfico se actualiza al registrar un nuevo valor.</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </aside>
    </div>
</div>
