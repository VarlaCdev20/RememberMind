@php
    $indicadores = ['agitacion' => ['Agitación', 'ph-lightning'], 'ansiedad' => ['Ansiedad', 'ph-brain'], 'aislamiento' => ['Aislamiento', 'ph-user-minus'], 'deambulacion' => ['Deambulación', 'ph-person-simple-walk'], 'agresividad' => ['Agresividad', 'ph-hand-fist'], 'apatia' => ['Apatía', 'ph-minus-circle']];
    $residente = $contexto['residente'] ?? null;
    $historial = $contexto['historial'] ?? [];
@endphp
<div class="rm-conducta" x-data="rmConductaRegistro()" :data-graph-open="trendOpen" @conducta-registrada="saved()" @conducta-captura-reiniciada="reset()" @clinical-capture-changed="clinicalFormDirty = $event.detail.dirty">
    @if(!$emergente)
        <x-ui.page-header title="Registro de conducta" subtitle="Observaciones, intervención y respuesta del residente." icon="ph-brain" />
        @if(!$residente)
            <x-ui.field label="Residente" for="conducta-residente"><select id="conducta-residente" class="rm-select" wire:change="seleccionarResidente($event.target.value)"><option value="">Selecciona un residente asignado</option>@foreach($residentes as $persona)<option value="{{ $persona->getKey() }}">{{ $persona->nombre_completo }}</option>@endforeach</select></x-ui.field>
        @else
            <x-ui.section-card :title="$residente->nombre_completo" subtitle="Cada registro conserva el profesional y la fecha originales." icon="ph-user">
                @if($puedeRegistrar)<button type="button" class="rm-btn-primary" wire:click="abrirRegistro">Nuevo registro</button>@else<p class="rm-help">Consulta disponible. Para registrar necesitas permiso, jornada activa y asignación vigente.</p>@endif
                @include('livewire.cuidados.partials.conducta-historial', ['historyContext' => 'page'])
            </x-ui.section-card>
        @endif
    @endif
    @if($residente)
    <x-ui.quick-register-modal id="conducta-registro" model="mostrarFormulario" title="Registro de conducta" subtitle="Seguimiento de conducta y respuesta del residente" close-method="cerrarModal" alpine-close="close()" alpine-back="close()" :back-text="true" back-label="Volver" drag-icon="ph-dots-three-vertical" class="rm-conducta__modal">
        <x-slot:icon><span class="rm-conducta__module-icon" aria-hidden="true"><i class="ph-bold ph-brain"></i></span></x-slot:icon>
        <x-slot:context>
            <div class="rm-conducta__context">
                <span class="rm-conducta__avatar" aria-hidden="true" x-data="{ photoAvailable: true }">@if(filled($residente->foto))<img x-show="photoAvailable" x-on:error="photoAvailable = false" src="{{ asset('storage/'.$residente->foto) }}" alt=""><span x-show="!photoAvailable" x-cloak>{{ mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno, 0, 1) }}</span>@else{{ mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno, 0, 1) }}@endif</span>
                <div class="rm-conducta__identity"><strong>{{ $residente->nombre_completo }}</strong><small>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no registrada' }} · {{ ucfirst(mb_strtolower($residente->genero ?: 'Género no registrado')) }}</small></div>
                <div><i class="ph-bold ph-bed" aria-hidden="true"></i><span>Habitación / cama<strong>{{ $residente->ubicacion_formateada }}</strong></span></div>
                <div><i class="ph-bold ph-user-circle" aria-hidden="true"></i><span>Profesional que registra<strong>{{ auth()->user()->name }}</strong></span></div>
                <div><i class="ph-bold ph-sun" aria-hidden="true"></i><span>Turno<strong>{{ $jornada?->turno?->nombre ?: 'Sin jornada activa' }}</strong>@if($jornada?->turno)<small>{{ substr($jornada->turno->hora_inicio, 0, 5) }}–{{ substr($jornada->turno->hora_cierre, 0, 5) }}</small>@endif</span></div>
                <div><i class="ph-bold ph-calendar" aria-hidden="true"></i><span>Fecha y hora del registro<strong>{{ now()->format('d/m/Y · H:i') }}</strong><small><i class="ph-bold ph-lock-key" aria-hidden="true"></i> Se guardan al confirmar</small></span></div>
                <details class="rm-conducta__context-more"><summary>Detalles del registro <i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary><dl><div><dt>Profesional que registra</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>Turno</dt><dd>{{ $jornada?->turno?->nombre ?: 'Sin jornada activa' }}</dd></div><div><dt>Fecha y hora del servidor</dt><dd>{{ now()->format('d/m/Y · H:i') }} · Se guardan al confirmar</dd></div></dl></details>
            </div>
            <x-ui.clinical-consultation-bar label="Consultar conducta registrada">
                <x-ui.action-button variant="secondary" icono="ph-clock-counter-clockwise" x-ref="historyTrigger" @click="openHistory($event.currentTarget)" x-bind:aria-expanded="historyOpen" aria-controls="conducta-historial" aria-haspopup="dialog">Ver historial</x-ui.action-button>
                <x-ui.action-button variant="secondary" icono="ph-chart-line" class="rm-conducta__evolution-trigger" @click="openTrend($event.currentTarget)" x-bind:aria-expanded="trendOpen" aria-controls="conducta-grafica-popup" aria-haspopup="dialog">Ver evolución</x-ui.action-button>
            </x-ui.clinical-consultation-bar>
        </x-slot:context>
        @if($resultado !== [])
            <div x-ref="result" tabindex="-1">
                <x-ui.resultado-operacion-clinica title="Conducta registrada correctamente" message="El registro se incorporó al historial del residente." :resident="$residente->nombre_completo" :date-time="$resultado['fecha'].' · '.$resultado['hora']" :professional="$resultado['profesional']" />
                <dl class="rm-conducta__result-details">@foreach(['estado_animo' => 'Estado de ánimo', 'participacion' => 'Participación', 'descripcion' => 'Descripción', 'intervencion' => 'Intervención', 'respuesta' => 'Respuesta'] as $campo => $label)<div><dt>{{ $label }}</dt><dd>{{ filled($resultado[$campo]) ? $resultado[$campo] : 'Sin registrar' }}</dd></div>@endforeach</dl>
                <div class="rm-conducta__actions"><button type="button" class="rm-btn-secondary" wire:click="cerrarModal(true)">Volver al residente</button><button type="button" class="rm-btn-secondary" x-ref="historyTrigger" @click="openHistory($event.currentTarget)">Ver historial</button>@if($puedeRegistrar)<button type="button" class="rm-btn-primary" wire:click="abrirRegistro">Registrar otro seguimiento</button>@endif</div>
            </div>
        @else
            <div x-show="clinicalDiscardOpen" x-cloak class="rm-conducta__discard" role="alert" tabindex="-1" x-effect="if(clinicalDiscardOpen) $nextTick(() => $el.focus())"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><h3 x-text="discardIntent === 'clear' ? '¿Limpiar los campos?' : '¿Salir sin guardar?'"></h3><p>Los datos de este registro se perderán. El historial guardado permanece intacto.</p></div>
            <div class="rm-conducta__workspace" x-show="!clinicalDiscardOpen">
                <form id="conducta-form" @submit.prevent="submit()" x-data="rmClinicalCapture()" @input="notifyDirty()" @change="notifyDirty(); focusCustom($event)" class="rm-conducta__capture">
                    <x-validation-errors />
                    @unless($puedeRegistrar)<x-ui.callout variant="info" title="Solo consulta">No puedes registrar fuera de una jornada y asignación vigentes, o sin el permiso de creación.</x-ui.callout>@endunless
                    <fieldset class="rm-conducta__editable" @disabled(!$puedeRegistrar) :disabled="busy || @js(!$puedeRegistrar)">
                    <section class="rm-conducta__card">
                        <header><b aria-hidden="true">1</b><div><h3>Conducta observada</h3><p>Distingue lo observado de lo no registrado.</p></div></header>
                        <div class="rm-conducta__indicators">
                            @foreach($indicadores as $campo => [$label, $icon])
                            <fieldset class="rm-conducta__indicator" :data-observed="$wire.datos.{{ $campo }} === true ? 'true' : 'false'"><legend><i class="ph-bold {{ $icon }}" aria-hidden="true"></i>{{ $label }}</legend><div class="rm-conducta__tri">
                                @foreach(['' => 'Sin registrar', '0' => 'No', '1' => 'Sí'] as $value => $text)
                                <label><input type="radio" name="conducta-{{ $campo }}" value="{{ $value }}" :checked="$wire.datos.{{ $campo }} === @js($value === '' ? null : (bool) $value)" @change="$wire.$set('datos.{{ $campo }}', @js($value === '' ? null : (bool) $value), false)" /><span>{{ $text }}</span></label>
                                @endforeach
                            </div>@error('datos.'.$campo)<p class="rm-error" role="alert">{{ $message }}</p>@enderror</fieldset>
                            @endforeach
                        </div>
                        <div class="rm-conducta__mood">
                            @include('livewire.cuidados.partials.conducta-estado-animo')
                            <x-ui.free-text-select id="conducta-participacion" label="Participación / cooperación" model="datos.participacion" :maxlength="30" icon="ph-users" placeholder="Sin registrar" :options="['ACTIVA', 'PARCIAL', 'NO_PARTICIPA']" />
                        </div>
                        <fieldset class="rm-conducta__change"><legend>¿Existe un cambio respecto a su comportamiento habitual?</legend><div class="rm-conducta__tri">@foreach(['' => 'Sin registrar', '0' => 'No', '1' => 'Sí'] as $value => $text)<label><input type="radio" name="conducta-cambio" value="{{ $value }}" :checked="$wire.datos.cambio_conducta === @js($value === '' ? null : (bool) $value)" @change="$wire.$set('datos.cambio_conducta', @js($value === '' ? null : (bool) $value), false)" /><span>{{ $text }}</span></label>@endforeach</div></fieldset>
                    </section>
                    <section class="rm-conducta__card">
                        <header><b aria-hidden="true">2</b><div><h3>Descripción del episodio</h3><p>Contexto y manifestaciones observadas.</p></div></header>
                        <div class="rm-conducta__chips" data-tone="context" role="group" aria-label="Ayudas de contexto">@foreach(['Durante higiene', 'Durante alimentación', 'Durante movilización', 'Durante medicación', 'Durante descanso', 'Interacción con otras personas', 'Cambio de ambiente', 'Ruido', 'Dolor referido'] as $texto)<button type="button" :data-added="String($wire.datos.descripcion ?? '').trim().split('\n').includes(@js('Contexto: '.mb_strtolower($texto).'.'))" @click="addText('descripcion', @js('Contexto: '.mb_strtolower($texto).'.'))">{{ $texto }}</button>@endforeach</div>
                        <p class="rm-conducta__help">Al pulsar una ayuda se añade una frase al texto que se guardará. Puedes editarla.</p>
                        <x-ui.field label="Qué ocurrió y en qué contexto" for="conducta-descripcion" error="datos.descripcion"><textarea id="conducta-descripcion" wire:model="datos.descripcion" class="rm-textarea" rows="3" placeholder="Describe lo observado, dónde ocurrió y quién estaba presente." aria-invalid="{{ $errors->has('datos.descripcion') ? 'true' : 'false' }}" @error('datos.descripcion') aria-describedby="conducta-descripcion-error" @enderror></textarea><p class="rm-conducta__counter"><span x-text="($wire.datos.descripcion ?? '').length"></span> caracteres</p></x-ui.field>
                    </section>
                    <section class="rm-conducta__card">
                        <header><b aria-hidden="true">3</b><div><h3>Intervención realizada</h3><p>Registra únicamente las acciones que aplicaste.</p></div></header>
                        <div class="rm-conducta__chips" data-tone="care" role="group" aria-label="Ayudas de intervención">@foreach(['Contención verbal', 'Reorientación', 'Acompañamiento', 'Cambio de ambiente', 'Explicación / tranquilidad', 'Pausa de la actividad', 'Aviso a familiar', 'Aviso a profesional médico', 'Medidas de seguridad'] as $texto)<button type="button" :data-added="String($wire.datos.intervencion ?? '').trim().split('\n').includes(@js($texto.'.'))" @click="addText('intervencion', @js($texto.'.'))">{{ $texto }}</button>@endforeach</div>
                        <x-ui.field label="Detalle de la intervención" for="conducta-intervencion" error="datos.intervencion"><textarea id="conducta-intervencion" wire:model="datos.intervencion" class="rm-textarea" rows="2" aria-invalid="{{ $errors->has('datos.intervencion') ? 'true' : 'false' }}" @error('datos.intervencion') aria-describedby="conducta-intervencion-error" @enderror placeholder="Describe las acciones realizadas."></textarea></x-ui.field>
                        <header class="rm-conducta__response-heading"><b aria-hidden="true">4</b><div><h3>Respuesta del residente</h3><p>Describe cómo respondió a la intervención.</p></div></header>
                        <div class="rm-conducta__chips" data-tone="response" role="group" aria-label="Ayudas de respuesta">@foreach(['Mejora', 'Sin cambios', 'Empeora'] as $texto)<button type="button" :data-added="String($wire.datos.respuesta ?? '').trim().split('\n').includes(@js('Respuesta observada: '.mb_strtolower($texto).'.'))" @click="addText('respuesta', @js('Respuesta observada: '.mb_strtolower($texto).'.'))">{{ $texto }}</button>@endforeach</div>
                        <x-ui.field label="Detalle de la respuesta" for="conducta-respuesta" error="datos.respuesta"><textarea id="conducta-respuesta" wire:model="datos.respuesta" class="rm-textarea" rows="2" aria-invalid="{{ $errors->has('datos.respuesta') ? 'true' : 'false' }}" @error('datos.respuesta') aria-describedby="conducta-respuesta-error" @enderror placeholder="Describe los cambios observados tras la intervención."></textarea></x-ui.field>
                    </section>
                    </fieldset>
                </form>
                <aside class="rm-conducta__continuity" aria-label="Seguimiento de conducta guardado">

                    <section class="rm-conducta__card"><header><i class="ph-bold ph-chart-line" aria-hidden="true"></i><h3>Resumen del periodo</h3></header><div class="rm-conducta__metrics">@foreach(['total' => 'Registros', 'agitacion' => 'Con agitación', 'ansiedad' => 'Con ansiedad', 'cambios' => 'Cambio de conducta'] as $campo => $label)<div><strong>{{ $contexto['resumen'][$campo] }}</strong><span>{{ $label }}</span></div>@endforeach</div><p class="rm-conducta__help">Hechos registrados. Los conteos no indican gravedad.</p></section>
                    <section class="rm-conducta__card"><header><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i><h3>Historial reciente</h3><button type="button" class="rm-conducta__text-button" x-ref="historyTrigger" @click="openHistory($event.currentTarget)" :aria-expanded="historyOpen">Ver completo</button></header>@include('livewire.cuidados.partials.conducta-historial', ['compacto' => true])</section>
                </aside>
            </div>
        @endif
    <div id="conducta-grafica-popup" popover="manual" role="dialog" aria-modal="false" class="rm-signos__graph-popup rm-conducta__graph-popup" x-ref="trendDialog" wire:ignore.self :style="trendStyle()" aria-labelledby="conducta-grafica-title" aria-describedby="conducta-grafica-description" @resize.window="if (trendOpen) fitTrend()" @keydown.escape.prevent.stop="closeTrend()">
        <header class="rm-signos__graph-popup-header">
            <button type="button" class="rm-btn-icon rm-signos__graph-move" data-trend-move aria-label="Mover gráfica: arrastra o usa las flechas" @pointerdown="startTrendPointer($event, 'move')" @pointermove="updateTrendPointer($event)" @pointerup="endTrendPointer($event)" @pointercancel="endTrendPointer($event)" @lostpointercapture="trendPointer = null" @keydown.arrow-left.prevent="moveTrend(-24, 0)" @keydown.arrow-right.prevent="moveTrend(24, 0)" @keydown.arrow-up.prevent="moveTrend(0, -24)" @keydown.arrow-down.prevent="moveTrend(0, 24)" @keydown.home.prevent="resetTrend()"><i class="ph-bold ph-arrows-out-cardinal" aria-hidden="true"></i></button>
            <div><h3 id="conducta-grafica-title">Registros de conducta por fecha</h3><p id="conducta-grafica-description">{{ $residente->nombre_completo }} · Solo registros confirmados.</p></div>
            <button type="button" class="rm-btn-icon" @click="closeTrend()" aria-label="Cerrar gráfica"><i class="ph-bold ph-x" aria-hidden="true"></i></button>
        </header>
        <div class="rm-signos__graph-popup-body">@include('livewire.cuidados.partials.conducta-evolucion')</div>
        <footer class="rm-signos__graph-popup-tools"><span>La captura se conserva al cerrar.</span>
            <button type="button" class="rm-btn-icon" @click="resizeTrend(-64, -48)" aria-label="Reducir gráfica"><i class="ph-bold ph-minus" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon" @click="resizeTrend(64, 48)" aria-label="Ampliar gráfica"><i class="ph-bold ph-plus" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon" @click="resetTrend()" aria-label="Restablecer tamaño y posición"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon rm-signos__graph-resize" aria-label="Ajustar tamaño: arrastra o usa las flechas" @pointerdown="startTrendPointer($event, 'resize')" @pointermove="updateTrendPointer($event)" @pointerup="endTrendPointer($event)" @pointercancel="endTrendPointer($event)" @lostpointercapture="trendPointer = null" @keydown.arrow-left.prevent="resizeTrend(-24, 0)" @keydown.arrow-right.prevent="resizeTrend(24, 0)" @keydown.arrow-up.prevent="resizeTrend(0, -24)" @keydown.arrow-down.prevent="resizeTrend(0, 24)"><i class="ph-bold ph-arrows-out-simple" aria-hidden="true"></i></button>
        </footer>
    </div>
    <section id="conducta-historial" popover="manual" x-data="{ consultationCompact: window.innerWidth < 1280 }" @resize.window="consultationCompact = window.innerWidth < 1280" x-show="historyOpen" x-cloak class="rm-conducta__history-overlay rm-clinical-consultation-panel" role="dialog" :aria-modal="String(consultationCompact)" aria-labelledby="conducta-historia-titulo" x-trap.inert="historyOpen && consultationCompact" x-effect="if (historyOpen && !$el.matches(':popover-open')) $el.showPopover(); if (!historyOpen && $el.matches(':popover-open')) $el.hidePopover()" @keydown.escape.stop="closeHistory()">
        <div class="rm-conducta__history-panel"><header><h3 id="conducta-historia-titulo">Historial de conducta</h3><button type="button" class="rm-btn-secondary" @click="closeHistory()">Cerrar historial</button></header><x-ui.field label="Filtrar registros guardados" for="conducta-history-filter"><select id="conducta-history-filter" class="rm-select" wire:model.live="filtroHistorial"><option value="">Todos</option>@foreach($indicadores as $campo => [$label])<option value="{{ $campo }}">Con {{ mb_strtolower($label) }}</option>@endforeach</select></x-ui.field>@include('livewire.cuidados.partials.conducta-historial', ['compacto' => false])</div>
    </section>
        <x-slot:footer>
            <div class="rm-conducta__actions">
                @if($resultado === [])
                    <template x-if="clinicalDiscardOpen"><div class="rm-conducta__actions"><button type="button" class="rm-btn-secondary" @click="clinicalDiscardOpen = false" :disabled="busy">Seguir editando</button><button type="button" class="rm-btn-primary" @click="confirmDiscard()" :disabled="busy" x-text="discardIntent === 'clear' ? 'Limpiar campos' : 'Salir sin guardar'"></button></div></template>
                    <template x-if="!clinicalDiscardOpen"><div class="rm-conducta__actions"><button type="button" class="rm-btn-secondary" @click="clear()" :disabled="busy">Limpiar campos</button><button type="button" class="rm-btn-secondary" @click="close()" :disabled="busy">Cancelar</button>@if($puedeRegistrar)<button type="submit" form="conducta-form" class="rm-btn-primary" :disabled="busy" wire:loading.attr="disabled" wire:target="guardar"><i class="ph-bold" :class="busy ? 'ph-spinner' : 'ph-check'" aria-hidden="true"></i><span x-text="busy ? 'Registrando…' : 'Confirmar y registrar'">Confirmar y registrar</span></button>@endif</div></template>
                @else<button type="button" class="rm-btn-secondary" @click="close()">Cerrar</button>@endif
            </div>
        </x-slot:footer>
    </x-ui.quick-register-modal>
    @endif
</div>
