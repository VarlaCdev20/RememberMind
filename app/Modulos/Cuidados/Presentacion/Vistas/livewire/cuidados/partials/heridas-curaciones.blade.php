@php
    $tiposHerida = ['Úlcera por presión', 'Herida quirúrgica', 'Dehiscencia quirúrgica', 'Desgarro cutáneo', 'Abrasión', 'Laceración', 'Herida cortante', 'Herida punzante', 'Quemadura', 'Úlcera venosa', 'Úlcera arterial', 'Úlcera de pie diabético', 'Lesión por humedad'];
    $ubicacionesHerida = ['Región sacra', 'Cóccix', 'Glúteo derecho', 'Glúteo izquierdo', 'Cadera derecha', 'Cadera izquierda', 'Talón derecho', 'Talón izquierdo', 'Tobillo derecho', 'Tobillo izquierdo', 'Pie derecho', 'Pie izquierdo', 'Dedos del pie derecho', 'Dedos del pie izquierdo', 'Pierna derecha', 'Pierna izquierda', 'Rodilla derecha', 'Rodilla izquierda', 'Muslo derecho', 'Muslo izquierdo', 'Espalda', 'Abdomen', 'Tórax', 'Hombro derecho', 'Hombro izquierdo', 'Brazo derecho', 'Brazo izquierdo', 'Codo derecho', 'Codo izquierdo', 'Antebrazo derecho', 'Antebrazo izquierdo', 'Mano derecha', 'Mano izquierda', 'Región occipital', 'Oreja derecha', 'Oreja izquierda', 'Cara', 'Cuello'];
    $curacionInicial = ['lesionId' => '', 'largoLesion' => null, 'anchoLesion' => null, 'profundidadLesion' => null, 'dolorLesion' => null, 'exudadoLesion' => '', 'aspectoLesion' => '', 'accionLesion' => '', 'observacionLesion' => '', 'olorLesion' => '', 'materialesLesion' => '', 'respuestaLesion' => '', 'nuevaHeridaTipo' => '', 'nuevaHeridaUbicacion' => '', 'nuevaHeridaClasificacion' => '', 'nuevaHeridaCausa' => '', 'nuevaHeridaObservacion' => '', 'resultadoCierreLesion' => '', 'motivoCierreLesion' => ''];
@endphp
<div class="rm-wound" x-data="rmWoundWorkspace()" :data-new-wound="nueva ? 'true' : 'false'" :data-wound-operation="clinicalOperation" @herida-evidencia-registrada.window="saved()" @keydown.escape.window="if(viewerOpen || compareOpen) { $event.stopImmediatePropagation(); closeGallery() }" @keydown.arrow-left.window="if(viewerOpen) { $event.preventDefault(); step(-1) }" @keydown.arrow-right.window="if(viewerOpen) { $event.preventDefault(); step(1) }">
    <span hidden data-wound-popup="{{ $emergente ? 'true' : 'false' }}" data-wound-operation="{{ $lesionId !== '' ? 'curacion' : 'nueva-herida' }}"></span>
    @if(!$emergente)
    <x-ui.section-card title="Heridas y evolución" subtitle="Cada herida conserva su identidad. Las curaciones se muestran del más reciente al más antiguo." icon="ph-bandaids">
        @if($this->puedeMutarRegistro('heridas.crear'))
            <button type="button" class="rm-btn-primary" aria-haspopup="dialog" aria-controls="clinical-wound-form" @click="openForm($event, 'nueva-herida')"><i class="ph-bold ph-plus" aria-hidden="true"></i> Nueva herida</button>
        @endif
        @if($adulto->heridas->isNotEmpty())
            <div class="rm-wound__selector" role="group" aria-label="Heridas del residente">
            @foreach($adulto->heridas->sortByDesc('fecha_hora_identificacion') as $herida)
                <button type="button" wire:key="herida-selector-{{ $herida->getKey() }}" class="rm-wound__choice {{ $lesionId === $herida->getKey() ? 'is-selected' : '' }}" aria-pressed="{{ $lesionId === $herida->getKey() ? 'true' : 'false' }}" @click="chooseWound(@js($herida->getKey()))">
                    <strong>{{ $herida->tipo_herida }}</strong><span>{{ $herida->ubicacion }}</span><span>{{ $herida->clasificacion ?: 'Clasificación no registrada' }}</span><span>{{ $herida->estado }} · {{ $herida->fecha_hora_identificacion?->format('d/m/Y H:i') }}</span>
                </button>
            @endforeach
            </div>
        @else
            <x-ui.empty-state icon="ph-bandaids" title="Sin heridas registradas" description="No hay heridas documentadas para este residente. Una herida y su curación son registros distintos." />
        @endif
        @if($heridaSeleccionada)
            <div class="rm-clinical-workspace__actions mt-4">
                @if($heridaSeleccionada->estado === 'ACTIVA' && $this->puedeMutarRegistro('curaciones_herida.crear'))
                    <button id="resident-register-trigger" type="button" class="rm-btn-primary" aria-haspopup="dialog" aria-controls="clinical-wound-form" @click="openForm($event)">Registrar curación<span class="sr-only"> · {{ $heridaSeleccionada->ubicacion }}</span></button>
                @endif
                @if($heridaSeleccionada->estado === 'ACTIVA' && $this->puedeMutarRegistro('atenciones.crear'))
                    <button type="button" class="rm-btn-secondary" aria-haspopup="dialog" aria-controls="clinical-wound-close-form" @click="openForm($event, 'cierre-herida')">Documentar cierre<span class="sr-only"> · {{ $heridaSeleccionada->ubicacion }}</span></button>
                @endif
            </div>
        @endif
        <div class="rm-clinical-workspace__history mt-4">
        @foreach($adulto->heridas->sortByDesc('fecha_hora_identificacion') as $herida)
            <article class="rm-clinical-workspace__record" wire:key="herida-historial-{{ $herida->getKey() }}">
                <h3 class="rm-section-title">{{ $herida->tipo_herida }} · {{ $herida->ubicacion }}</h3>
                <p>{{ ucfirst(strtolower($herida->estado)) }} · {{ $herida->fecha_hora_identificacion?->format('d/m/Y H:i') }}</p>
                @if($herida->fecha_hora_cierre)<p>Cierre: {{ $herida->fecha_hora_cierre->format('d/m/Y H:i') }}</p>@endif
                @if(filled($herida->observacion))<p>{{ $herida->observacion }}</p>@endif
                @can('curaciones_herida.ver')
                <details class="rm-clinical-workspace__evolution mt-3">
                    <summary>Historial de curaciones · {{ $herida->curaciones->count() }}</summary>
                    @forelse($herida->curaciones->sortByDesc('fecha_hora') as $curacion)
                        <article class="rm-wound__history-entry"><time>{{ $curacion->fecha_hora?->format('d/m/Y H:i') }}</time>
                            <dl class="rm-clinical-workspace__details">
                            @foreach(['longitud' => 'Largo', 'ancho' => 'Ancho', 'profundidad' => 'Profundidad'] as $campo => $etiqueta)<div><dt>{{ $etiqueta }}</dt><dd>{{ $curacion->$campo !== null ? $curacion->$campo.' cm' : 'No registrado' }}</dd></div>@endforeach
                            @foreach(['tejido' => 'Aspecto / tejido', 'exudado' => 'Exudado', 'olor' => 'Olor', 'dolor' => 'Dolor registrado', 'materiales' => 'Materiales', 'respuesta' => 'Respuesta'] as $campo => $etiqueta) @if(filled($curacion->$campo))<div><dt>{{ $etiqueta }}</dt><dd>{{ $curacion->$campo }}</dd></div>@endif @endforeach
                            </dl>
                            <p><strong>Procedimiento:</strong> {{ $curacion->procedimiento }}</p>@if(filled($curacion->observacion))<p>{{ $curacion->observacion }}</p>@endif
                        </article>
                    @empty <p>Esta herida todavía no tiene curaciones registradas.</p> @endforelse
                </details>
                @endcan
            </article>
        @endforeach
        </div>
    </x-ui.section-card>
    @endif

    @if($resultadoHerida && !$emergente)
    <section x-ref="result" tabindex="-1" role="status" class="rm-wound__result mt-4" aria-label="Registro confirmado">
        <h3 class="rm-section-title">{{ $resultadoHerida['tipo'] === 'herida' ? 'Herida registrada correctamente' : 'Curación registrada correctamente' }}</h3>
        <p>{{ trim($adulto->nombres.' '.$adulto->apellido_paterno.' '.$adulto->apellido_materno) }} · {{ $resultadoHerida['ubicacion'] }} · {{ $resultadoHerida['fecha'] }}</p>
        <p>{{ $resultadoHerida['fotografias'] }} fotografías registradas.</p>
        @if($resultadoHerida['tipo'] === 'curacion')<p>Largo: {{ $resultadoHerida['longitud'] ?? 'No registrado' }} · Ancho: {{ $resultadoHerida['ancho'] ?? 'No registrado' }} · Profundidad: {{ $resultadoHerida['profundidad'] ?? 'No registrado' }} cm</p>@endif
        <div class="rm-clinical-workspace__actions"><a href="{{ route('admin.enfermeria.pacientes.ficha', $adulto->getKey()) }}" class="rm-btn-secondary">Volver al residente</a><button type="button" class="rm-btn-secondary" @click="document.querySelector('.rm-wound__history-entry')?.closest('details')?.setAttribute('open', '')">Ver historial</button>@if($galeriaHerida->isNotEmpty())<button type="button" class="rm-btn-secondary" @click="viewGallery(@js($galeriaHerida), 0, $event)">Ver fotografías</button>@endif<button type="button" class="rm-btn-primary" @click="openForm($event)">Registrar otra curación</button></div>
    </section>
    @endif

    @if($this->puedeMutarRegistro('curaciones_herida.crear') || $this->puedeMutarRegistro('heridas.crear') || $this->puedeMutarRegistro('atenciones.crear'))
    <x-ui.quick-register-modal id="clinical-wound" model="mostrarFormularioHerida" title="Heridas / curaciones" close-method="descartarFotosHerida" alpine-close="closeWoundForm()" alpine-back="closeWoundForm()" :back-text="true" drag-icon="ph-dots-three-vertical" back-label="Volver" class="rm-resident-directory__register-modal rm-resident-directory__register-modal--form rm-clinical-form-modal rm-wound__modal" :auto-focus="false">
        <x-slot:subtitle><span x-text="nueva ? 'Registra la identificación clínica inicial y la evidencia fotográfica de la herida.' : 'Seguimiento y registro de curaciones del residente'">Registro clínico de la herida</span></x-slot:subtitle>
        <x-slot:icon><span class="rm-clinical-form__icon" aria-hidden="true"><i class="ph-bold ph-bandaids"></i></span></x-slot:icon>
        <x-slot:title><span x-text="nueva ? 'Registrar nueva herida' : (clinicalOperation === 'cierre-herida' ? 'Documentar cierre de herida' : 'Heridas / curaciones')">Heridas / curaciones</span></x-slot:title>
        <x-slot:context><div class="rm-wound__resident-context">
            <span class="rm-wound__avatar" aria-hidden="true">{{ mb_substr($adulto->nombres, 0, 1).mb_substr($adulto->apellido_paterno, 0, 1) }}</span>
            <div class="rm-wound__resident"><strong data-clinical-resident>{{ $adulto->nombre_completo }}</strong><span class="rm-wound__identity-meta">{{ $adulto->edad !== null ? $adulto->edad.' años' : 'Edad no registrada' }}{{ $adulto->sexo ? ' · '.ucfirst(mb_strtolower($adulto->sexo)) : '' }}</span><small class="rm-wound__context-location-mobile">{{ $adulto->ubicacion_formateada }}</small></div>
            <div class="rm-wound__context-item rm-wound__location"><i class="ph-duotone ph-bed" aria-hidden="true"></i><div><span>Ubicación del residente</span><strong>{{ $adulto->ubicacion_formateada }}</strong></div></div>
            <div class="rm-wound__context-item rm-wound__professional"><i class="ph-duotone ph-user-circle" aria-hidden="true"></i><div><span>Profesional que registra</span><strong data-clinical-professional>{{ \Illuminate\Support\Str::title(mb_strtolower(auth()->user()->name)) }}</strong></div></div>
            <div class="rm-wound__context-item rm-wound__shift"><i class="ph-duotone ph-sun" aria-hidden="true"></i><div><span>Jornada actual</span><strong>{{ $jornadaHerida?->turno?->nombre ?: 'Sin jornada activa' }}</strong><small>@if($jornadaHerida){{ substr($jornadaHerida->turno->hora_inicio, 0, 5) }}–{{ substr($jornadaHerida->turno->hora_cierre, 0, 5) }}@else Consulta el contexto de tu jornada.@endif</small></div></div>
            <div class="rm-wound__context-item rm-wound__server"><i class="ph-duotone ph-calendar" aria-hidden="true"></i><div><span>Registro (sistema)</span><strong>{{ now()->format('d/m/Y · H:i') }}</strong><small><i class="ph-bold ph-lock-key" aria-hidden="true"></i> No editable · Se registra al confirmar</small></div></div>
        </div>
        @can('curaciones_herida.ver')
        <x-ui.clinical-consultation-bar x-show="!nueva" label="Consultas de la herida">
            <x-ui.action-button variant="secondary" icono="ph-clock-counter-clockwise" @click="openHistory($event)" x-bind:aria-expanded="historyOpen" aria-controls="herida-historial-popup" aria-haspopup="dialog">Ver historial</x-ui.action-button>
            <x-ui.action-button variant="secondary" icono="ph-chart-line" @click="openEvolution($event)" x-bind:aria-expanded="evolutionOpen" aria-controls="herida-evolucion-popup" aria-haspopup="dialog">Ver evolución</x-ui.action-button>
        </x-ui.clinical-consultation-bar>
        @endcan
        </x-slot:context>
        @if($resultadoHerida && $resultadoHerida['tipo'] === 'herida')
            <section x-ref="result" tabindex="-1" role="status" class="rm-wound__result">
                <span class="rm-wound__success-icon" aria-hidden="true"><i class="ph-bold ph-check"></i></span>
                <h4 class="rm-section-title">{{ $resultadoHerida['tipo'] === 'herida' ? 'Herida registrada' : 'Curación registrada correctamente' }}</h4>
                <p>{{ trim($adulto->nombres.' '.$adulto->apellido_paterno.' '.$adulto->apellido_materno) }}</p>
                <p>{{ $resultadoHerida['ubicacion'] }} · {{ $resultadoHerida['fecha'] }}</p>
                @if($resultadoHerida['tipo'] === 'herida')<dl class="rm-clinical-workspace__details"><div><dt>Tipo</dt><dd>{{ $resultadoHerida['tipo_herida'] }}</dd></div><div><dt>Clasificación</dt><dd>{{ $resultadoHerida['clasificacion'] ?: 'No registrada' }}</dd></div></dl><details><summary>Ver herida</summary><p><strong>Causa:</strong> {{ $resultadoHerida['causa'] ?: 'No registrada' }}</p><p><strong>Observación inicial:</strong> {{ $resultadoHerida['observacion'] ?: 'No registrada' }}</p></details>@endif
                <p>{{ $resultadoHerida['fotografias'] }} fotografías registradas.</p>
                @if($galeriaHerida->isNotEmpty()) @include('livewire.cuidados.partials.fotografias-heridas-gallery') @endif
            </section>
        @else
        <div x-show="clinicalDiscardOpen" x-cloak role="alert" tabindex="-1" x-effect="if(clinicalDiscardOpen) $nextTick(() => $el.focus())"><h4 class="rm-section-title">¿Salir sin guardar?</h4><p>Los datos de este registro y las fotografías seleccionadas se perderán.</p></div>
        <div class="rm-wound__workspace" :class="{ 'rm-wound__workspace--single': nueva || clinicalOperation === 'cierre-herida' }" x-show="!clinicalDiscardOpen">
            <div>
                <form id="clinical-wound-form" @submit.prevent="if(!uploading) { nueva ? $wire.guardarNuevaHerida() : $wire.guardarSeguimientoLesion() }" x-show="clinicalOperation !== 'cierre-herida'" class="rm-clinical-form" @input="clinicalFormDirty = true" @change="clinicalFormDirty = true">
                    <x-validation-errors />
                    @if(!$adulto->heridas->contains('estado', 'ACTIVA'))
                        <x-ui.callout variant="info" title="Sin heridas activas registradas" class="rm-wound__entry-notice" x-show="nueva">
                            <p>Empieza identificando la herida. Al guardarla podrás abrir «Registrar primera curación».</p>
                            <ol class="rm-wound__entry-steps" aria-label="Pasos del registro"><li><b aria-hidden="true">1</b> Identificar la herida</li><li><b aria-hidden="true">2</b> Registrar la curación</li></ol>
                        </x-ui.callout>
                    @endif
                    <section x-show="nueva" class="rm-clinical-form__section rm-wound__initial-section" aria-labelledby="nueva-herida-datos-title">
                        <header class="rm-wound__section-heading rm-wound__initial-heading"><span class="rm-wound__section-number" aria-hidden="true">1</span><div><h4 id="nueva-herida-datos-title">Identificar la herida</h4><p>Indica el tipo y la ubicación. Si necesitas otra descripción, elige «Otro / escribir».</p></div><span class="rm-wound__initial-badge">Registro inicial</span></header>
                        <fieldset class="rm-wound__initial-grid" :disabled="!nueva" aria-labelledby="nueva-herida-datos-title">
                            <x-ui.free-text-select id="nueva-nuevaHeridaTipo" label="Tipo de herida" model="nuevaHeridaTipo" icon="ph-bandaids" required
                                :options="$tiposHerida">
                                <x-slot:examples>
                                    @foreach($tiposHerida as $opcion)
                                        <button type="button" x-show="matches(@js($opcion))" @click="chooseIllustration(@js($opcion), $el)" :aria-pressed="choice === @js($opcion)">
                                            @include('livewire.cuidados.partials.herida-opcion-ilustrada', ['illustrationKind' => 'tipo'])
                                            <span>{{ $opcion }}</span>
                                        </button>
                                    @endforeach
                                </x-slot:examples>
                            </x-ui.free-text-select>
                            <x-ui.free-text-select id="nueva-nuevaHeridaUbicacion" label="Ubicación" model="nuevaHeridaUbicacion" icon="ph-map-pin" :maxlength="120" required
                                :options="$ubicacionesHerida">
                                <x-slot:examples>
                                    @foreach($ubicacionesHerida as $opcion)
                                        <button type="button" x-show="matches(@js($opcion))" @click="chooseIllustration(@js($opcion), $el)" :aria-pressed="choice === @js($opcion)">
                                            @include('livewire.cuidados.partials.herida-opcion-ilustrada', ['illustrationKind' => 'ubicacion'])
                                            <span>{{ $opcion }}</span>
                                        </button>
                                    @endforeach
                                </x-slot:examples>
                            </x-ui.free-text-select>
                            <div class="rm-wound__initial-divider rm-wound__full"><i class="ph-bold ph-note-pencil" aria-hidden="true"></i><span>Información complementaria</span><small>Opcional</small></div>
                            <x-ui.free-text-select id="nueva-nuevaHeridaClasificacion" label="Clasificación" model="nuevaHeridaClasificacion" icon="ph-tag" placeholder="Sin indicar" help="Los estadios de presión solo corresponden a lesiones por presión. Puedes escribir otra clasificación."
                                :options="['Lesión por presión: estadio I', 'Lesión por presión: estadio II', 'Lesión por presión: estadio III', 'Lesión por presión: estadio IV', 'Lesión por presión: no clasificable', 'Lesión por presión: tejidos profundos', 'No aplica']" />
                            <x-ui.free-text-select id="nueva-nuevaHeridaCausa" label="Causa" model="nuevaHeridaCausa" icon="ph-note-pencil" :maxlength="120" placeholder="Sin indicar"
                                :options="['Presión mantenida', 'Fricción', 'Cizallamiento', 'Traumatismo', 'Caída', 'Golpe', 'Corte', 'Punción', 'Procedimiento quirúrgico', 'Contacto térmico', 'Contacto químico', 'Humedad prolongada', 'Adhesivo médico', 'Dispositivo médico', 'Causa no identificada']" />
                            <x-ui.field label="Observación inicial" for="nueva-nuevaHeridaObservacion" error="nuevaHeridaObservacion" class="rm-wound__full rm-wound__initial-observation"><textarea id="nueva-nuevaHeridaObservacion" wire:model="nuevaHeridaObservacion" rows="2" maxlength="1000" class="rm-textarea" placeholder="Describe el aspecto inicial u otros detalles relevantes." aria-describedby="herida-observacion-contador{{ $errors->has('nuevaHeridaObservacion') ? ' nueva-nuevaHeridaObservacion-error' : '' }}" aria-invalid="{{ $errors->has('nuevaHeridaObservacion') ? 'true' : 'false' }}"></textarea><p id="herida-observacion-contador" class="rm-wound__counter"><span x-text="($wire.nuevaHeridaObservacion ?? '').length"></span> / 1000</p></x-ui.field>
                        </fieldset>
                    </section>
                    @include('livewire.cuidados.partials.curacion-herida-valoracion')
                    @include('livewire.cuidados.partials.fotografias-heridas-upload')
                    @include('livewire.cuidados.partials.curacion-herida-procedimiento')
                </form>
                <form id="clinical-wound-close-form" @submit.prevent="prepareFeedback('cierre-herida'); $wire.cerrarLesion($wire.lesionId)" x-show="clinicalOperation === 'cierre-herida'" class="rm-clinical-form" @input="clinicalFormDirty = true"><x-validation-errors /><p>Documenta el cierre de la herida seleccionada. Sus curaciones anteriores seguirán disponibles.</p><x-ui.field label="Resultado clínico al cierre" for="herida-resultado" error="resultadoCierreLesion" :required="true"><textarea id="herida-resultado" wire:model="resultadoCierreLesion" maxlength="1000" class="rm-textarea"></textarea></x-ui.field><x-ui.field label="Motivo del cierre" for="herida-motivo" error="motivoCierreLesion" :required="true"><textarea id="herida-motivo" wire:model="motivoCierreLesion" maxlength="1000" class="rm-textarea"></textarea></x-ui.field></form>
            </div>

        </div>
        @endif
        @include('livewire.cuidados.partials.curacion-herida-historial')
        @can('curaciones_herida.ver')
        <section id="herida-evolucion-popup" popover="manual" x-ref="woundEvolution" x-data="{ consultationCompact: window.innerWidth < 1280 }" @resize.window="consultationCompact = window.innerWidth < 1280" role="dialog" :aria-modal="String(consultationCompact)" aria-labelledby="herida-evolucion-title" class="rm-clinical-consultation-panel" x-trap="evolutionOpen && !viewerOpen && !compareOpen && consultationCompact" x-effect="if(evolutionOpen && !viewerOpen && !compareOpen && !$el.matches(':popover-open')) $el.showPopover(); if((!evolutionOpen || viewerOpen || compareOpen) && $el.matches(':popover-open')) $el.hidePopover()" @keydown.escape.prevent.stop="closeEvolution()">
            <div><header><h3 id="herida-evolucion-title">Evolución de la herida</h3><button type="button" class="rm-btn-icon" aria-label="Cerrar evolución" @click="closeEvolution()"><i class="ph-bold ph-x" aria-hidden="true"></i></button></header>
                @include('livewire.cuidados.partials.curacion-herida-seguimiento')
            </div>
        </section>
        @endcan
        <x-slot:footer>
            @if(($resultadoHerida['tipo'] ?? '') === 'curacion')<div x-show="curationToast" x-ref="curationToast" tabindex="-1" class="rm-wound__toast" role="status"><i class="ph-fill ph-check-circle" aria-hidden="true"></i><div><strong>Curación registrada correctamente.</strong><small>{{ $resultadoHerida['fecha'] }} · {{ $resultadoHerida['ubicacion'] }}</small></div><button type="button" @click="curationToast = false" aria-label="Cerrar confirmación"><i class="ph-bold ph-x" aria-hidden="true"></i></button></div>@endif
            @if($resultadoHerida && $resultadoHerida['tipo'] === 'herida')
                @if($emergente)<button type="button" class="rm-btn-secondary" @click="returnToResident()">Volver al residente</button>@else<a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', $adulto->getKey()) }}">Volver al residente</a>@endif
                @if($resultadoHerida['tipo'] === 'herida' && $this->puedeMutarRegistro('curaciones_herida.crear'))<button type="button" class="rm-btn-primary" @click="startFirstCuration()" wire:loading.attr="disabled" wire:target="prepararPrimeraCuracion">Registrar primera curación</button>@endif
            @else
            <div x-show="clinicalDiscardOpen" class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="clinicalDiscardOpen = false">Seguir editando</button><button type="button" class="rm-btn-danger" @click="discardWoundForm(@js($curacionInicial))">Salir sin guardar</button></div>
            <div x-show="!clinicalDiscardOpen" class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="closeWoundForm()" :disabled="uploading" wire:loading.attr="disabled" wire:target="guardarSeguimientoLesion,guardarNuevaHerida,cerrarLesion">Cancelar</button><button x-show="clinicalOperation !== 'cierre-herida'" type="submit" form="clinical-wound-form" class="rm-btn-primary rm-btn-primary--confirm" :disabled="uploading" wire:loading.attr="disabled" wire:target="guardarSeguimientoLesion,guardarNuevaHerida,loteFotografias"><i class="ph-bold ph-check" wire:loading.remove wire:target="guardarSeguimientoLesion,guardarNuevaHerida" aria-hidden="true"></i><span class="rm-btn__spinner" wire:loading wire:target="guardarSeguimientoLesion,guardarNuevaHerida" aria-hidden="true"></span><span wire:loading.remove wire:target="guardarSeguimientoLesion,guardarNuevaHerida" x-text="'Confirmar y registrar'"></span><span wire:loading wire:target="guardarSeguimientoLesion,guardarNuevaHerida">Registrando…</span></button><button x-show="clinicalOperation === 'cierre-herida'" type="submit" form="clinical-wound-close-form" class="rm-btn-primary" wire:loading.attr="disabled" wire:target="cerrarLesion">Confirmar cierre de herida</button></div>
            @endif
        </x-slot:footer>
    </x-ui.quick-register-modal>
    @endif
    @if(!($this->puedeMutarRegistro('curaciones_herida.crear') || $this->puedeMutarRegistro('heridas.crear') || $this->puedeMutarRegistro('atenciones.crear')))
        @include('livewire.cuidados.partials.curacion-herida-historial')
    @endif
    @if($heridaSeleccionada && !$emergente)<div class="mt-4">@include('livewire.cuidados.partials.fotografias-heridas-gallery')</div>@endif

    @include('livewire.cuidados.partials.fotografias-heridas-viewer')
</div>
@include('livewire.cuidados.partials.clinical-operation-result', ['clinicalResultResidentCode' => $adulto->cod_residente])
