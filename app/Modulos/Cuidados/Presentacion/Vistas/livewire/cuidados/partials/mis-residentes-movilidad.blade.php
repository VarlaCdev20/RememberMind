<div class="rm-clinical-form rm-movilidad" x-data="rmMovilidadRegistro(@js($movHistorial), @js(array_merge($movDatos, ['marcha' => $movMarcha, 'traslado' => $movTraslado, 'tipo_apoyo' => $movTipoApoyo, 'equilibrio' => $movEquilibrio, 'fatiga' => $movFatiga, 'riesgo_caida' => $movRiesgoCaida, 'observacion' => $movObservacion])), false, @js($registroInicial))" aria-label="Registro de movilidad">
    @php($movOpciones = $this->movilidadOpciones)
    @if($errors->has('movilidad_guardado'))
        <div class="rm-movilidad__save-error" role="alert" aria-live="assertive" tabindex="-1" x-data x-init="$nextTick(() => $el.focus())"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><div><strong>No se guardó el registro</strong><p>{{ $errors->first('movilidad_guardado') }}</p></div></div>
    @else
        <x-validation-errors />
    @endif
    <div class="rm-movilidad__timestamp"><i class="ph-bold ph-clock" aria-hidden="true"></i><div><strong>Momento del registro</strong><time>{{ \Carbon\Carbon::parse($movMomento)->format('d/m/Y · H:i') }}</time></div><span><i class="ph-bold ph-lock" aria-hidden="true"></i> Fecha y hora automáticas · No editables<small>La hora definitiva se asigna al guardar.</small></span></div>
    <div class="rm-movilidad__layout">
        <div class="rm-movilidad__capture">
            <section class="rm-movilidad__section"><h4><b>A</b> Motivo del registro</h4><x-ui.mobility-choice-group field="motivo_registro" :label="$movOpciones['motivo_registro']['label']" :options="$movOpciones['motivo_registro']['options']" icon="ph-clipboard-text" /><x-ui.mobility-other-detail field="motivo_otro" selection="motivo_registro" label="Especifica el otro motivo" /></section>
            <section class="rm-movilidad__section"><h4><b>B</b> Actividad realizada</h4><x-ui.mobility-choice-group field="actividad_realizada" :label="$movOpciones['actividad_realizada']['label']" :options="$movOpciones['actividad_realizada']['options']" icon="ph-person-simple-walk" /></section>
            <section class="rm-movilidad__section"><h4><b>C</b> Capacidad observada</h4>
                @foreach(['marcha' => 'ph-person-simple-walk', 'traslado' => 'ph-bed', 'tipo_apoyo' => 'ph-users', 'dispositivo' => 'ph-wheelchair', 'equilibrio' => 'ph-person-arms-spread'] as $field => $icon)
                    <x-ui.mobility-choice-group :field="$field" :label="$movOpciones[$field]['label']" :options="$movOpciones[$field]['options']" :icon="$icon" :required="$field === 'marcha'" />
                    @if($field === 'dispositivo')
                        <x-ui.mobility-other-detail field="dispositivo_otro" selection="dispositivo" label="Especifica el otro dispositivo" />
                        <fieldset class="rm-movilidad__group" x-show="canWalk()" x-cloak><legend><i class="ph-bold ph-ruler" aria-hidden="true"></i> Distancia recorrida</legend>
                            <x-ui.field for="movilidad-distancia" label="Metros recorridos" error="distancia_metros" help="Opcional. Solo cuando se realizó una actividad de deambulación."><div class="rm-movilidad__unit"><input id="movilidad-distancia" class="rm-input" type="number" min="0" max="99999.99" step="0.01" inputmode="decimal" x-model="values.distancia_metros" @input="setValue('distancia_metros', $event.target.value)" :disabled="!canWalk()" aria-invalid="{{ $errors->has('distancia_metros') ? 'true' : 'false' }}" aria-describedby="movilidad-distancia-help @error('distancia_metros') movilidad-distancia-error @enderror"><span>m</span></div></x-ui.field>
                            <div class="rm-movilidad__shortcuts" aria-label="Distancias frecuentes">@foreach([5, 10, 25, 50] as $distance)<button type="button" @click="setValue('distancia_metros', '{{ $distance }}')">{{ $distance }} m</button>@endforeach</div>
                        </fieldset>
                    @endif
                @endforeach
            </section>
            <section class="rm-movilidad__section"><h4><b>D</b> Tolerancia y síntomas</h4><div class="rm-movilidad__symptoms">
                @foreach(['fatiga' => 'ph-lightning', 'disnea' => 'ph-wind', 'tolerancia_movilidad' => 'ph-activity', 'debilidad' => 'ph-battery-low', 'dolor_movilidad' => 'ph-heartbeat', 'riesgo_caida' => 'ph-shield-warning', 'mareo' => 'ph-spinner-gap', 'cambio_habitual' => 'ph-trend-up'] as $field => $icon)<x-ui.mobility-choice-group :field="$field" :label="$movOpciones[$field]['label']" :options="$movOpciones[$field]['options']" :icon="$icon" />@endforeach
            </div><p class="rm-help">Registra lo observado o referido. No valorado es distinto de No.</p></section>
            <section class="rm-movilidad__section"><h4><b>E</b> Observaciones clínicas</h4><x-ui.field for="movilidad-observacion" label="Detalles relevantes" error="observacion" help="Opcional · hasta 5000 caracteres."><textarea id="movilidad-observacion" class="rm-textarea" maxlength="5000" rows="3" x-model="values.observacion" @input="setValue('observacion', $event.target.value)" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" aria-describedby="movilidad-observacion-help @error('observacion') movilidad-observacion-error @enderror" placeholder="Describe la respuesta del residente, asistencia realizada u otros detalles relevantes."></textarea></x-ui.field><small class="rm-movilidad__counter" x-text="values.observacion.length + ' / 5000'"></small></section>
            <div class="rm-movilidad__incident"><i class="ph-bold ph-info" aria-hidden="true"></i><p>Si ocurrió una caída, golpe u otro incidente, utiliza el registro específico.</p>@can('incidentes.crear')<a class="rm-btn-secondary" target="_blank" rel="noopener" href="{{ route('admin.enfermeria.incidentes', ['adulto' => $detalleResidente['cod_residente']]) }}">Registrar incidente ↗<span class="sr-only">(abre otra pestaña)</span></a>@endcan</div>
        </div>
        <aside class="rm-movilidad__continuity" aria-label="Resumen y continuidad">
            <h4>Resumen y continuidad</h4>
            @can('registros_movilidad.ver')
                <section class="rm-movilidad__section"><h5><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i> Último registro</h5>
                    @if($movHistorial)<time>{{ $movHistorial[0]['fecha'] }} · {{ $movHistorial[0]['hora'] }}</time><strong>{{ $movHistorial[0]['actividad'] }}</strong><dl>@foreach($movHistorial[0]['campos'] as $field)<div><dt>{{ $field['nombre'] }}</dt><dd>{{ $field['valor'] }}</dd></div>@endforeach</dl>@else<p class="rm-help">Sin registros previos de movilidad.</p>@endif
                </section>
                <section class="rm-movilidad__section"><h5><i class="ph-bold ph-calendar-check" aria-hidden="true"></i> Jornada actual</h5><div class="rm-movilidad__counts">@foreach(['registros' => 'Registros de movilidad', 'deambulacion' => 'Actividades con deambulación', 'fatiga' => 'Con fatiga registrada'] as $key => $label)<span><b>{{ $movContinuidad[$key] ?? 0 }}</b>{{ $label }}</span>@endforeach</div><p class="rm-help">Conteos de eventos guardados, no una valoración funcional.</p></section>
                <section class="rm-movilidad__section"><h5>Historial reciente</h5><ol class="rm-movilidad__recent">@forelse(array_slice($movHistorial, 0, 6) as $row)<li><time>{{ $row['fecha'] }} · {{ $row['hora'] }}</time><strong>{{ $row['actividad'] }}</strong><span>{{ collect($row['campos'])->whereIn('nombre', ['Movilidad observada', 'Dispositivo', 'Distancia recorrida', 'Apoyo requerido', 'Fatiga'])->pluck('valor')->join(' · ') }}</span></li>@empty<li>Sin registros previos.</li>@endforelse</ol><button type="button" class="rm-btn-secondary" @click="openTrend($event.currentTarget)" aria-controls="movilidad-historial-popup" :aria-expanded="trendOpen">Ver historial</button></section>
                @include('livewire.cuidados.partials.mis-residentes-movilidad-historial')
            @else<p class="rm-help">El historial requiere permiso de consulta de movilidad.</p>@endcan
        </aside>
    </div>
</div>
