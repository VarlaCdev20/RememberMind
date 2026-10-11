<div class="rm-clinical-form rm-dolor" wire:ignore.self
     x-data="rmDolorRegistro(@js($dolorHistorial), { eva: @js($dolorEva), location: @js($dolorUbicacion), duration: @js($dolorDuracionValor), unit: @js($dolorDuracionUnidad), trigger: @js($dolorDesencadenante), intervention: @js($dolorIntervencion), frequency: @js($dolorFrecuencia), relief: @js($dolorFactoresAlivio), response: @js($dolorRespuesta) }, @js($dolorFechaHora), { origin: @js($dolorCodOrigen), episode: @js($dolorEpisodio), intensityBands: @js(config('enfermeria.dolor_intensidad_presentacion')) })"
     :data-intensity="intensityPresentation().state" :data-graph-open="trendOpen" @keydown.escape="if (trendOpen) { $event.preventDefault(); $event.stopPropagation(); closeTrend() }" @resident-directory-step-changed.window="if (trendOpen && $wire.confirmarDescarte) closeTrend()" aria-label="Valoración de dolor">
    <section class="rm-dolor__time" aria-label="Momento de la valoración">
        <h5><i class="ph-bold ph-clock" aria-hidden="true"></i> Momento de la valoración</h5>
        <strong>{{ $dolorFechaHora ? \Carbon\Carbon::parse($dolorFechaHora)->format('d/m/Y · H:i') : 'Sin registro abierto' }}</strong>
        <span><i class="ph-bold ph-lock-simple" aria-hidden="true"></i> Automáticas · No editables</span>
    </section>
    @if($dolorCodOrigen)
        <section class="rm-clinical-form__section rm-dolor__origin" aria-label="Valoración de origen, solo lectura">
            <h5><i class="ph-bold ph-link" aria-hidden="true"></i> Episodio iniciado · {{ $dolorContextoOrigen['fecha'] }}</h5>
            <strong>EVA {{ $dolorContextoOrigen['intensidad'] ?? 'No registrada' }} / 10 · {{ $dolorContextoOrigen['ubicacion'] ?: 'Localización no registrada' }}</strong>
            <p>Intervención inicial: {{ $dolorContextoOrigen['intervencion'] ?: 'No registrada' }}</p>
            <small>Nueva valoración actual; la historia anterior permanece intacta.</small>
        </section>
    @endif
    @if($errors->hasAny(['intensidad', 'ubicacion', 'duracion_valor', 'duracion_unidad', 'frecuencia', 'factores_alivio', 'respuesta', 'cod_valoracion_origen', 'desencadenante', 'intervencion']))<p class="rm-dolor__error-summary" role="alert">No se puede continuar. Revisa los campos señalados.</p>@endif
    @error('cod_valoracion_origen')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
    @error('dolor_guardado')<x-ui.resultado-operacion-clinica variant="error" :resident="$detalleResidente['nombre_completo']" :message="$message" />@enderror
    <div class="rm-dolor__workspace">
        <div class="rm-dolor__column">
            <section class="rm-clinical-form__section rm-dolor__intensity" :data-intensity="intensityPresentation().state">
                <fieldset class="rm-clinical-form__scale" aria-describedby="dolor-eva-ayuda @error('intensidad') dolor-eva-error @enderror" aria-invalid="{{ $errors->has('intensidad') ? 'true' : 'false' }}">
                    <legend>Intensidad EVA <span class="rm-clinical-form__required">Obligatoria · 0–10</span></legend>
                    <p id="dolor-eva-ayuda" class="rm-clinical-form__note">Intensidad referida u observada, de 0 a 10.</p>
                    <div class="rm-clinical-form__value" aria-live="polite" aria-atomic="true"><strong x-text="current() === null ? '—' : current()"></strong><span>/ 10</span><small x-text="intensityPresentation().label + (current() === null ? '' : ' · Sin guardar')"></small></div>
                    <div class="rm-clinical-form__scale-options">
                        @foreach(range(0, 10) as $value)
                            <label class="rm-clinical-form__scale-option">
                                <input id="dolor-eva-{{ $value }}" type="radio" name="dolor-eva" value="{{ $value }}" wire:model="dolorEva" x-model="values.eva" required aria-label="{{ $value }} de 10"><span>{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="rm-dolor__scale-extremes"><span>0 · Sin dolor</span><span>10 · Máxima intensidad</span></div>
                    @error('intensidad')<p id="dolor-eva-error" role="alert" class="rm-error">{{ $message }}</p>@enderror
                </fieldset>
            </section>
            <section class="rm-clinical-form__section rm-dolor__location" aria-labelledby="dolor-mapa-title">
                <h5 id="dolor-mapa-title"><i class="ph-bold ph-person" aria-hidden="true"></i> Localización del dolor</h5>
                <p class="rm-clinical-form__note">Selecciona zonas o describe la localización. Puedes combinar ambas opciones.</p>
                <x-ui.pain-body-map />
                <div class="rm-dolor__chips" x-show="zones.length" x-cloak aria-label="Zonas seleccionadas">
                    <template x-for="zone in zones" :key="zone"><button type="button" @click="removeZone(zone)" :aria-label="'Quitar ' + zone"><span x-text="zone"></span><i class="ph-bold ph-x" aria-hidden="true"></i></button></template>
                    <button type="button" @click="clearZones()">Limpiar zonas</button>
                </div>
            </section>
            <section class="rm-clinical-form__section" aria-labelledby="dolor-continuity-title">
                <h5 id="dolor-continuity-title"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i> Continuidad de la valoración</h5>
                @can('valoraciones_dolor.ver')
                    <div class="rm-dolor__comparison" aria-live="polite">
                        <div><span>Última guardada</span><strong x-text="previous() ? previous().value + ' / 10' : '—'"></strong><small x-text="previous()?.date ?? 'Sin valoración anterior'"></small><small x-text="previous()?.location || ''"></small></div>
                        <div><span>Actual · Sin guardar</span><strong x-text="current() === null ? '—' : current() + ' / 10'"></strong><small x-text="comparison()"></small></div>
                    </div>
                    <button class="rm-btn-secondary rm-dolor__evolution" type="button" aria-controls="dolor-grafica-popup" :aria-expanded="trendOpen" @click="openTrend($event.currentTarget)"><i class="ph-bold ph-chart-line" aria-hidden="true"></i> Ver evolución</button>
                    @if(!$esModoConsulta)
                        <template x-if="previous()?.code"><button class="rm-btn-secondary" type="button" @click="$wire.abrirReevaluacionDolor(previous().code)" wire:loading.attr="disabled" wire:target="abrirReevaluacionDolor">Reevaluar dolor</button></template>
                    @endif
                @else
                    <p class="rm-clinical-form__note">No tienes permiso para consultar valoraciones anteriores.</p>
                @endcan
            </section>
        </div>
        <div class="rm-dolor__column">
            <section class="rm-clinical-form__section" aria-labelledby="dolor-context-title">
                <h5 id="dolor-context-title"><i class="ph-bold ph-note-pencil" aria-hidden="true"></i> Características del dolor</h5>
                <x-ui.field label="Localización" for="dolor-ubicacion" error="ubicacion" help="Texto final que se guardará. Opcional, máximo 120 caracteres.">
                    <input id="dolor-ubicacion" class="rm-input" type="text" maxlength="120" wire:model="dolorUbicacion" x-model="values.location" @input="syncManualLocation()" placeholder="Ej. rodilla derecha, zona lumbar" :aria-invalid="locationError ? 'true' : @js($errors->has('ubicacion') ? 'true' : 'false')" aria-describedby="dolor-ubicacion-help dolor-location-status @error('ubicacion') dolor-ubicacion-error @enderror">
                </x-ui.field>
                <div id="dolor-location-status" class="rm-dolor__location-status" aria-live="polite"><span class="rm-error" x-text="locationError"></span><span x-text="locationLength() + '/120'"></span></div>
                <div class="rm-dolor__capture-grid">
                    <x-ui.clinical-capture-select id="dolor-duracion" label="Duración" model="dolorDuracionValor" capture-key="duration" error="duracion_valor" custom-type="number" :options="['' => 'Sin indicar', 5 => '5', 10 => '10', 15 => '15', 30 => '30', 60 => '60']" />
                    <x-ui.clinical-capture-select id="dolor-unidad" label="Unidad" model="dolorDuracionUnidad" capture-key="unit" error="duracion_unidad" :options="['' => 'Seleccionar unidad', 'minutos' => 'Minutos', 'horas' => 'Horas', 'días' => 'Días']" />
                    <p id="dolor-duracion-help" class="rm-clinical-form__note">Si indicas una duración, completa valor y unidad. Puedes escribir otro valor.</p>
                    <x-ui.clinical-capture-select id="dolor-frecuencia" label="Frecuencia" model="dolorFrecuencia" capture-key="frequency" error="frecuencia" :maxlength="40" :options="['' => 'Sin indicar', 'Continua' => 'Continua', 'Intermitente' => 'Intermitente', 'Ocasional' => 'Ocasional']" />
                </div>
                <x-ui.field label="Desencadenante" for="dolor-desencadenante" error="desencadenante">
                    <textarea id="dolor-desencadenante" class="rm-textarea rm-dolor__trigger" wire:model="dolorDesencadenante" x-model="values.trigger" rows="2" placeholder="Circunstancias en las que apareció el dolor, si se conocen" aria-invalid="{{ $errors->has('desencadenante') ? 'true' : 'false' }}" @error('desencadenante') aria-describedby="dolor-desencadenante-error" @enderror></textarea>
                </x-ui.field>
                <x-ui.field label="Factores de alivio" for="dolor-alivio" error="factores_alivio" help="Qué alivia el dolor, según lo referido u observado. No sustituye la intervención realizada.">
                    <textarea id="dolor-alivio" class="rm-textarea" wire:model="dolorFactoresAlivio" x-model="values.relief" rows="2" placeholder="Ej. refiere alivio con reposo" aria-invalid="{{ $errors->has('factores_alivio') ? 'true' : 'false' }}" aria-describedby="dolor-alivio-help @error('factores_alivio') dolor-alivio-error @enderror"></textarea>
                </x-ui.field>
            </section>
            <section class="rm-clinical-form__section" aria-labelledby="dolor-intervention-title">
                <h5 id="dolor-intervention-title"><i class="ph-bold ph-first-aid" aria-hidden="true"></i> Intervención realizada</h5>
                <x-ui.field label="Medidas realizadas, si corresponde" for="dolor-intervencion" error="intervencion">
                    <textarea id="dolor-intervencion" class="rm-textarea rm-dolor__intervention" wire:model="dolorIntervencion" x-model="values.intervention" rows="3" placeholder="Describe únicamente las medidas que se realizaron" aria-invalid="{{ $errors->has('intervencion') ? 'true' : 'false' }}" @error('intervencion') aria-describedby="dolor-intervencion-error" @enderror></textarea>
                </x-ui.field>
            </section>
            @if($dolorCodOrigen)
                <section class="rm-clinical-form__section" aria-labelledby="dolor-response-title">
                    <h5 id="dolor-response-title"><i class="ph-bold ph-chat-text" aria-hidden="true"></i> Respuesta observada</h5>
                    <x-ui.field label="Respuesta en esta reevaluación" for="dolor-respuesta" error="respuesta" help="Opcional. Describe lo observado o referido, sin modificar registros anteriores.">
                        <textarea id="dolor-respuesta" class="rm-textarea" wire:model="dolorRespuesta" x-model="values.response" rows="2" aria-invalid="{{ $errors->has('respuesta') ? 'true' : 'false' }}" aria-describedby="dolor-respuesta-help @error('respuesta') dolor-respuesta-error @enderror"></textarea>
                    </x-ui.field>
                </section>
            @endif
            <section class="rm-clinical-form__section rm-dolor__summary" aria-labelledby="dolor-summary-title">
                <h5 id="dolor-summary-title"><i class="ph-bold ph-list-checks" aria-hidden="true"></i> Resumen descriptivo · Sin guardar</h5>
                <dl>
                    <div><dt>Localización</dt><dd x-text="values.location || 'No indicada'"></dd></div>
                    <div><dt>Frecuencia</dt><dd x-text="values.frequency || 'No indicada'"></dd></div>
                    <div><dt>Factores de alivio</dt><dd x-text="values.relief || 'No indicados'"></dd></div>
                </dl>
            </section>

        </div>
    </div>
    @can('valoraciones_dolor.ver')@include('livewire.cuidados.partials.mis-residentes-dolor-grafica')@endcan
</div>
