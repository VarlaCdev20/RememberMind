<div class="rm-cognicion" x-data="rmObservacionCognitiva(@js($campos), @js($opciones))" :data-graph-open="trendOpen" :style="trendOpen && trendBounds ? '--rm-cognicion-graph-width:' + trendBounds.width + 'px' : null" @clinical-capture-changed="clinicalFormDirty = $event.detail.dirty" @cognicion-captura-reiniciada="reset()" @cognicion-registrada="saved()" @cognicion-validacion="validationFocus()">
    <x-ui.quick-register-modal id="cognicion-registro" model="mostrarFormulario" title="Observación cognitiva" subtitle="Registro de enfermería" close-method="cerrarModal" alpine-close="close()" alpine-back="close()" :back-text="true" back-label="Volver" class="rm-cognicion__modal">
        <x-slot:icon><span class="rm-cognicion__icon" aria-hidden="true"><i class="ph-bold ph-brain"></i></span></x-slot:icon>
        <x-slot:context>
            <div class="rm-cognicion__context">
                <div class="rm-cognicion__identity"><span class="rm-cognicion__avatar" aria-hidden="true">{{ mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno, 0, 1) }}</span><span><strong>{{ $residente->nombre_completo }}</strong><small>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no registrada' }} · {{ $residente->getKey() }}</small><small>{{ $residente->ubicacion_formateada }}</small></span></div>
                <div><i class="ph-bold ph-user-circle" aria-hidden="true"></i><span>Profesional que registra<strong>{{ $nombreProfesional }}</strong></span></div>
                <div><i class="ph-bold ph-sun" aria-hidden="true"></i><span>Jornada actual<strong>{{ $jornada?->turno?->nombre ?: 'Sin jornada activa' }}</strong>@if($jornada?->turno)<small>{{ substr($jornada->turno->hora_inicio, 0, 5) }}–{{ substr($jornada->turno->hora_cierre, 0, 5) }}</small>@endif</span></div>
                <div><i class="ph-bold ph-calendar" aria-hidden="true"></i><span>Fecha y hora del registro<strong>{{ now()->format('d/m/Y · H:i') }}</strong><small>Servidor · Se guardan al confirmar</small></span></div>
                <details class="rm-cognicion__context-more"><summary>Profesional, jornada y fecha <i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary><dl><div><dt>Profesional que registra</dt><dd>{{ $nombreProfesional }}</dd></div><div><dt>Jornada actual</dt><dd>{{ $jornada?->turno?->nombre ?: 'Sin jornada activa' }} @if($jornada?->turno) · {{ substr($jornada->turno->hora_inicio,0,5) }}–{{ substr($jornada->turno->hora_cierre,0,5) }}@endif</dd></div><div><dt>Fecha y hora del servidor</dt><dd>{{ now()->format('d/m/Y · H:i') }} · Se guardan al confirmar</dd></div></dl></details>
            </div>
            <x-ui.clinical-consultation-bar label="Consultar observaciones guardadas">
                <x-ui.action-button variant="secondary" icono="ph-clock-counter-clockwise" x-ref="historyTrigger" @click="openHistory($event.currentTarget)" x-bind:aria-expanded="historyOpen" aria-controls="cognicion-historial" aria-haspopup="dialog">Ver historial</x-ui.action-button>
                <x-ui.action-button variant="secondary" icono="ph-chart-line" @click="openTrend($event.currentTarget)" x-bind:aria-expanded="trendOpen" aria-controls="cognicion-grafica" aria-haspopup="dialog">Ver evolución</x-ui.action-button>
            </x-ui.clinical-consultation-bar>
        </x-slot:context>
        @if($resultado !== [])
            <section x-ref="result" tabindex="-1" class="rm-cognicion__card">
                <x-ui.resultado-operacion-clinica title="Observación cognitiva registrada" message="Se incorporó un nuevo registro al historial del residente." :resident="$residente->nombre_completo" :date-time="$resultado['fecha'].' · '.$resultado['hora']" :professional="$resultado['profesional']" />
                <p>El registro previo permanece intacto. Puedes consultar el historial y la evolución.</p>
            </section>
        @endif
        <div x-show="clinicalDiscardOpen" x-cloak class="rm-cognicion__card" role="alert" tabindex="-1" x-effect="if (clinicalDiscardOpen) $nextTick(() => $el.focus())">
            <h3 x-text="discardIntent === 'clear' ? '¿Limpiar los campos?' : '¿Salir sin guardar?'"></h3><p>Solo se descartará esta captura. El residente, la jornada y el historial permanecerán intactos.</p>
        </div>
        <x-ui.section-card class="rm-cognicion__card rm-cognicion__summary" :title="$resultado === [] ? 'Captura actual' : 'Registro confirmado'" :subtitle="$resultado === [] ? 'Completa lo observado; deja vacío lo que no evaluaste.' : 'Disponible en el historial del residente.'" icon="ph-note-pencil" x-show="!clinicalDiscardOpen" aria-label="Captura y consultas">
            @if($resultado === [])
            <details class="rm-cognicion__domain-disclosure"><summary><span><b x-text="answered"></b> de 15 respondidos · Ver resumen</span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary><progress max="15" :value="answered" aria-label="Cantidad de indicadores respondidos"></progress><div class="rm-cognicion__domains">@foreach(['orientacion' => 'ph-compass', 'memoria' => 'ph-brain', 'atencion' => 'ph-target', 'comprension' => 'ph-book-open', 'lenguaje' => 'ph-chat-circle', 'senales' => 'ph-list-checks'] as $clave => $icon)<div><i class="ph-bold {{ $icon }}" aria-hidden="true"></i><strong>{{ $dominios[$clave] }}</strong><small x-text="domainSummary(@js($clave))"></small></div>@endforeach</div><p class="rm-help">Respuestas opcionales. Este progreso mide respuestas registradas, no desempeño ni riesgo cognitivo.</p></details>
            @endif
        </x-ui.section-card>
        <div class="rm-cognicion__workspace" x-show="!clinicalDiscardOpen">
            @if($resultado === [])
            <form id="cognicion-form" class="rm-cognicion__capture" @submit.prevent="submit()" x-data="rmClinicalCapture()" @input="notifyDirty()" @change="notifyDirty()">
                    <nav class="rm-cognicion__section-nav" aria-label="Secciones de la observación cognitiva">
                        @foreach(['A' => 'Orientación', 'B' => 'Funciones cognitivas', 'C' => 'Señales observadas', 'D' => 'Observaciones'] as $letra => $nombre)
                            <button type="button" @click="focusSection('cognicion-seccion-{{ $letra }}')" aria-label="Ir a {{ $nombre }}" aria-controls="cognicion-seccion-{{ $letra }}"><b aria-hidden="true">{{ $letra }}</b><span class="rm-cognicion__nav-label">{{ $nombre }}</span><span class="rm-cognicion__nav-short">{{ ["A" => "Orientación", "B" => "Funciones", "C" => "Señales", "D" => "Notas"][$letra] }}</span><i class="ph-bold ph-arrow-down" aria-hidden="true"></i></button>
                        @endforeach
                    </nav>
                    <p x-show="transportError" x-cloak x-text="transportError" class="rm-error" role="alert"></p>
                    @unless($puedeRegistrar)<x-ui.callout variant="info" title="Solo consulta">Para registrar necesitas permiso, personal activo, jornada vigente y este residente asignado. Puedes consultar su historial autorizado.</x-ui.callout>@endunless
                    @error('persistencia')<div role="alert" class="rm-error" data-capture-error tabindex="-1">{{ $message }}</div>@enderror
                    @error('datos.jornada')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                    @error('datos.continuidad_signos')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                    <fieldset class="rm-cognicion__editable" @disabled(!$puedeRegistrar) :disabled="busy || @js(!$puedeRegistrar)">
                    @foreach(['A' => ['Orientación', 'Referencias de persona, lugar y tiempo.', array_slice(array_keys($campos), 0, 3)], 'B' => ['Funciones cognitivas', 'Memoria, atención, comprensión y lenguaje observables.', array_slice(array_keys($campos), 3, 5)]] as $letra => [$titulo, $ayuda, $grupo])
                        <details id="cognicion-seccion-{{ $letra }}" class="rm-cognicion__card rm-cognicion__section" open>
                            <summary><b aria-hidden="true">{{ $letra }}</b><span><strong>{{ $titulo }}</strong><small>{{ $ayuda }}</small></span><span class="rm-cognicion__count" x-text="count(@js($grupo)) + ' de {{ count($grupo) }}'"></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
                            <div class="rm-cognicion__fields">
                                @foreach($grupo as $campo)
                                    <div class="rm-cognicion__field" :data-answered="$wire.datos.{{ $campo }} !== null && $wire.datos.{{ $campo }} !== ''">
                                        <label for="cognicion-{{ $campo }}"><i class="ph-bold {{ $campos[$campo][1] }}" aria-hidden="true"></i>{{ $campos[$campo][0] }}</label>
                                        <p id="cognicion-{{ $campo }}-help">{{ $campos[$campo][2] }}</p>
                                        <select id="cognicion-{{ $campo }}" class="rm-select" wire:model="datos.{{ $campo }}" aria-invalid="{{ $errors->has('datos.'.$campo) ? 'true' : 'false' }}" aria-describedby="cognicion-{{ $campo }}-help cognicion-{{ $campo }}-error"><option value="">No observado</option>@foreach($opciones[$campo] as $valor => $texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach</select>
                                        <span id="cognicion-{{ $campo }}-error" class="rm-error">@error('datos.'.$campo){{ $message }}@enderror</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                    <details id="cognicion-seccion-C" class="rm-cognicion__card rm-cognicion__section" open>
                        <summary><b aria-hidden="true">C</b><span><strong>Señales observadas</strong><small>Distingue lo observado de lo que no se evaluó.</small></span><span class="rm-cognicion__count" x-text="count(@js(array_slice(array_keys($campos), 8))) + ' de 7'"></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
                        <div class="rm-cognicion__signals">
                            @foreach(array_slice($campos, 8, null, true) as $campo => [$label, $icon])
                                <fieldset class="rm-cognicion__signal"><legend><i class="ph-bold {{ $icon }}" aria-hidden="true"></i>{{ $label }}</legend><div class="rm-cognicion__segmented">
                                    @foreach(['1' => 'Sí', '0' => 'No', '' => 'No observado'] as $value => $text)<label><input type="radio" name="cognicion-{{ $campo }}" value="{{ $value }}" :checked="$wire.datos.{{ $campo }} === @js($value === '' ? null : (bool) $value)" @change="$wire.$set('datos.{{ $campo }}', @js($value === '' ? null : (bool) $value), false)" aria-invalid="{{ $errors->has('datos.'.$campo) ? 'true' : 'false' }}" aria-describedby="cognicion-{{ $campo }}-error"><span>{{ $text }}</span></label>@endforeach
                                </div><span class="rm-error" id="cognicion-{{ $campo }}-error">@error('datos.'.$campo){{ $message }}@enderror</span></fieldset>
                            @endforeach
                        </div>
                    </details>
                    <section id="cognicion-seccion-D" class="rm-cognicion__card rm-cognicion__section"><header tabindex="-1" data-section-focus><b aria-hidden="true">D</b><div><h3>Observaciones</h3><p>Describe qué ocurrió, qué observaste y su relación con el funcionamiento habitual.</p></div></header><x-ui.field label="Descripción objetiva" for="cognicion-observacion" error="datos.observacion"><textarea id="cognicion-observacion" rows="4" class="rm-textarea" wire:model="datos.observacion" placeholder="Describe las manifestaciones, el momento, el contexto y las diferencias con el funcionamiento habitual." aria-invalid="{{ $errors->has('datos.observacion') ? 'true' : 'false' }}" aria-describedby="cognicion-observacion-error"></textarea></x-ui.field><p class="rm-help">Basta un indicador observado o una descripción significativa. Los 15 campos no son obligatorios.</p></section>
                    </fieldset>
            </form>
            @endif
            <aside class="rm-cognicion__orientation" aria-label="Información de apoyo">
                <x-ui.section-card class="rm-cognicion__card" title="Última observación guardada" icon="ph-clock-counter-clockwise">@if($errorConsulta)<p role="alert">{{ $errorConsulta }}</p><button type="button" class="rm-btn-secondary" wire:click="reintentarConsulta" wire:loading.attr="disabled">Reintentar consulta</button>@elseif($contexto['ultima'])<p>{{ $contexto['ultima']['fecha'] }} · {{ $contexto['ultima']['hora'] }}</p><small>{{ $contexto['ultima']['profesional'] }}</small>@else<p>Todavía no existen observaciones cognitivas registradas para este residente.</p>@endif</x-ui.section-card>
                @if($resultado === [])
                <x-ui.section-card class="rm-cognicion__card" title="Revisión antes de guardar" subtitle="Captura pendiente · No forma parte del historial" icon="ph-note-pencil"><p x-text="answered === 0 ? 'Sin respuestas. La ausencia de datos no indica normalidad.' : (answered < 15 ? 'Observaciones parciales: ' : 'Indicadores respondidos: ') + answered + ' de 15.'"></p><details class="rm-cognicion__guidance"><summary>Ver respuestas consideradas</summary><dl class="rm-cognicion__answers">@foreach($campos as $campo => [$label])<div><dt>{{ $label }}</dt><dd x-text="response(@js($campo), $wire.datos.{{ $campo }})"></dd></div>@endforeach</dl></details></x-ui.section-card>
                @endif
                <x-ui.section-card class="rm-cognicion__card" title="Ayuda para registrar" icon="ph-lightbulb"><details class="rm-cognicion__guidance"><summary>Consultar recomendaciones</summary><p>Estas observaciones de enfermería no constituyen un diagnóstico. Ante cambios relevantes, documenta las manifestaciones e informa al profesional responsable según el protocolo institucional.</p></details><p class="rm-cognicion__context-note" x-show="$wire.datos.confusion === true">Se ha registrado presencia de confusión. Describe lo observado e informa al profesional responsable cuando corresponda.</p><p class="rm-cognicion__context-note" x-show="$wire.datos.cambio_cognitivo === true">Se ha registrado un cambio cognitivo observado. Documenta las manifestaciones y compáralas con el funcionamiento habitual. Si fue súbito, sigue el protocolo de comunicación clínica.</p></x-ui.section-card>
            </aside>
        </div>
        <x-ui.clinical-trend-window id="cognicion-grafica" title="Evolución cognitiva" :resident="$residente->nombre_completo" window-label="gráfica cognitiva">@include('livewire.cuidados.partials.cognicion-evolucion')</x-ui.clinical-trend-window>
        <section id="cognicion-historial" popover="manual" x-data="{ consultationCompact: window.innerWidth < 1280 }" @resize.window="consultationCompact = window.innerWidth < 1280" x-show="historyOpen" x-cloak role="dialog" :aria-modal="String(consultationCompact)" aria-labelledby="cognicion-history-title" class="rm-cognicion__history-overlay rm-clinical-consultation-panel" x-trap.inert="historyOpen && consultationCompact" x-effect="if (historyOpen && !$el.matches(':popover-open')) $el.showPopover(); if (!historyOpen && $el.matches(':popover-open')) $el.hidePopover()" @keydown.escape.prevent.stop="closeHistory()"><div class="rm-cognicion__history-panel"><header><div><h3 id="cognicion-history-title">Historial de observaciones cognitivas</h3><p>{{ $residente->nombre_completo }}</p></div><button type="button" class="rm-btn-icon" aria-label="Cerrar historial" @click="closeHistory()"><i class="ph-bold ph-x" aria-hidden="true"></i></button></header>@include('livewire.cuidados.partials.cognicion-historial')</div></section>
        <x-slot:footer><div class="rm-cognicion__actions">
            <template x-if="clinicalDiscardOpen"><div class="rm-cognicion__actions"><button type="button" class="rm-btn-secondary" @click="clinicalDiscardOpen = false" :disabled="busy" x-text="discardIntent === 'clear' ? 'Mantener datos' : 'Seguir editando'"></button><button type="button" class="rm-btn-primary" @click="confirmDiscard()" :disabled="busy" x-text="discardIntent === 'clear' ? 'Limpiar campos' : 'Salir sin guardar'"></button></div></template>
            <div class="rm-cognicion__actions" x-show="!clinicalDiscardOpen">@if($resultado === [])<button type="button" class="rm-btn-secondary" @click="close()" :disabled="busy">Cancelar</button><button type="button" class="rm-btn-secondary" @click="clear()" :disabled="busy"><i class="ph-bold ph-eraser" aria-hidden="true"></i> Limpiar campos</button><button type="submit" form="cognicion-form" class="rm-btn-primary" @disabled(!$puedeRegistrar) :disabled="busy || @js(!$puedeRegistrar)"><i class="ph-bold ph-check" aria-hidden="true"></i><span x-text="busy ? 'Guardando…' : 'Guardar registro'"></span></button>@else<button type="button" class="rm-btn-secondary" wire:click="cerrarModal(true)">Volver al residente</button>@if($puedeRegistrar)<button type="button" class="rm-btn-primary" wire:click="nuevoRegistro" wire:loading.attr="disabled">Nuevo registro</button>@endif @endif</div>
        </div></x-slot:footer>
    </x-ui.quick-register-modal>
</div>
