@php
    $curacionInicial = ['lesionId' => '', 'lesionMedible' => true, 'largoLesion' => null, 'anchoLesion' => null, 'profundidadLesion' => null, 'dolorLesion' => null, 'exudadoLesion' => '', 'aspectoLesion' => '', 'accionLesion' => '', 'observacionLesion' => '', 'resultadoCierreLesion' => '', 'motivoCierreLesion' => ''];
@endphp
<x-ui.section-card title="Heridas y evolución" subtitle="Cada herida conserva su identidad. Las curaciones se muestran del más reciente al más antiguo." icon="ph-bandaids">
    @if($adulto->heridas->isNotEmpty())
        <div class="rm-clinical-workspace__history">
        @foreach($adulto->heridas->sortByDesc('fecha_hora_identificacion') as $herida)
            <article class="rm-clinical-workspace__record" wire:key="herida-{{ $herida->cod_herida }}">
                <div class="rm-clinical-workspace__record-heading"><time>{{ $herida->fecha_hora_identificacion?->format('d/m/Y H:i') ?? 'Fecha no registrada' }}</time><span class="rm-badge-pill">{{ mb_convert_case(str_replace('_', ' ', $herida->estado), MB_CASE_TITLE, 'UTF-8') }}</span></div>
                <h3 class="rm-section-title">{{ mb_convert_case(str_replace('_', ' ', $herida->tipo_herida), MB_CASE_TITLE, 'UTF-8') }}</h3>
                <dl class="rm-clinical-workspace__details">
                    <div><dt>Ubicación</dt><dd>{{ $herida->ubicacion }}</dd></div>
                    <div><dt>Clasificación registrada</dt><dd>{{ $herida->clasificacion ?: 'No registrada' }}</dd></div>
                    <div><dt>Causa registrada</dt><dd>{{ $herida->causa ?: 'No registrada' }}</dd></div>
                    @if($herida->fecha_hora_cierre)<div><dt>Cierre</dt><dd>{{ $herida->fecha_hora_cierre->format('d/m/Y H:i') }}</dd></div>@endif
                </dl>
                @if(filled($herida->observacion))<p class="rm-clinical-form__note">{{ $herida->observacion }}</p>@endif
                @if($herida->estado === 'ACTIVA')
                    <div class="rm-clinical-workspace__actions mt-3">
                        @if($this->puedeMutarRegistro('curaciones_herida.crear'))
                            <button type="button" @if($loop->first) id="resident-register-trigger" @endif class="rm-btn-primary" @click="clinicalReturnTarget = $event.currentTarget; $wire.$set('lesionId', @js($herida->cod_herida), false); clinicalOperation = 'curacion'; clinicalFormDirty = false; clinicalDiscardOpen = false; clinicalFormOpen = true">Registrar curación<span class="sr-only"> · {{ $herida->ubicacion }}</span></button>
                        @endif
                        @if($this->puedeMutarRegistro('atenciones.crear'))
                            <button type="button" class="rm-btn-secondary" @click="clinicalReturnTarget = $event.currentTarget; $wire.$set('lesionId', @js($herida->cod_herida), false); clinicalOperation = 'cierre-herida'; clinicalFormDirty = false; clinicalDiscardOpen = false; clinicalFormOpen = true">Documentar cierre<span class="sr-only"> · {{ $herida->ubicacion }}</span></button>
                        @endif
                    </div>
                @endif
                @can('curaciones_herida.ver')
                    <details class="rm-clinical-workspace__evolution mt-4">
                        <summary>Historial de curaciones · {{ $herida->curaciones->count() }}</summary>
                        @if($herida->curaciones->isNotEmpty())
                            <ol class="rm-clinical-workspace__timeline">
                            @foreach($herida->curaciones->sortByDesc('fecha_hora') as $curacion)
                                <li wire:key="curacion-{{ $curacion->cod_curacion }}">
                                    <time>{{ $curacion->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no registrada' }}</time>
                                    <dl class="rm-clinical-workspace__details">
                                        @foreach(['longitud' => 'Largo', 'ancho' => 'Ancho', 'profundidad' => 'Profundidad'] as $campo => $etiqueta)
                                            <div><dt>{{ $etiqueta }}</dt><dd>{{ $curacion->$campo !== null ? $curacion->$campo.' cm' : 'No registrado' }}</dd></div>
                                        @endforeach
                                        @foreach(['tejido' => 'Aspecto / tejido', 'exudado' => 'Exudado', 'olor' => 'Olor', 'dolor' => 'Dolor registrado', 'materiales' => 'Materiales', 'respuesta' => 'Respuesta'] as $campo => $etiqueta)
                                            @if(filled($curacion->$campo))<div><dt>{{ $etiqueta }}</dt><dd>{{ $curacion->$campo }}</dd></div>@endif
                                        @endforeach
                                    </dl>
                                    <p class="rm-clinical-form__note"><strong>Procedimiento:</strong> {{ $curacion->procedimiento }}</p>
                                    @if(filled($curacion->observacion))<p class="rm-clinical-form__note">{{ $curacion->observacion }}</p>@endif
                                </li>
                            @endforeach
                            </ol>
                        @else
                            <p class="rm-clinical-form__note">Esta herida todavía no tiene curaciones registradas.</p>
                        @endif
                    </details>
                @endcan
            </article>
        @endforeach
        </div>
    @else
        <x-ui.empty-state icon="ph-bandaids" title="Sin heridas registradas" description="No hay heridas documentadas para este residente. Una herida y su curación son registros distintos." />
    @endif
</x-ui.section-card>

@if($adulto->heridas->where('estado', 'ACTIVA')->isNotEmpty() && ($this->puedeMutarRegistro('curaciones_herida.crear') || $this->puedeMutarRegistro('atenciones.crear')))
<x-ui.modal-livewire id="clinical-wound" title="Atención de herida" subtitle="Curación o cierre documentado" alpine-model="clinicalFormOpen" alpine-close="closeClinicalForm()" class="rm-clinical-form-modal" :show-validation="false">
    <x-slot:context>
        <div class="rm-resident-directory__register-context">
            <span class="rm-resident-directory__register-avatar" aria-hidden="true"><i class="ph-bold ph-bandaids"></i></span>
            <div class="rm-resident-directory__register-identity"><strong data-clinical-resident>{{ trim($adulto->nombres.' '.$adulto->ap_paterno.' '.$adulto->ap_materno) }}</strong><span x-data="{ wounds: @js($adulto->heridas->mapWithKeys(fn ($w) => [$w->cod_herida => $w->tipo_herida.' · '.$w->ubicacion])) }" x-text="wounds[$wire.lesionId] || 'Selecciona una herida activa'"></span></div>
            <div class="rm-clinical-form__personnel"><span>Profesional que registra</span><strong data-clinical-professional>{{ auth()->user()->name }}</strong></div>
        </div>
    </x-slot:context>
    <div x-show="clinicalDiscardOpen" x-cloak role="alert" class="rm-clinical-form__section" tabindex="-1" x-effect="if(clinicalDiscardOpen) $nextTick(() => $el.focus())"><h4 class="rm-section-title">¿Salir sin guardar?</h4><p class="rm-clinical-form__note">Los datos de esta atención se perderán.</p></div>
    @if($this->puedeMutarRegistro('curaciones_herida.crear'))
    <form id="clinical-wound-form" wire:submit="guardarSeguimientoLesion" @submit="prepareFeedback('curacion')" x-show="clinicalOperation === 'curacion' && !clinicalDiscardOpen" class="rm-clinical-form" x-data="rmClinicalCapture()" x-effect="if(clinicalFormOpen && clinicalOperation === 'curacion') $nextTick(() => initialCapture = snapshot())" @input="notifyDirty()" @change="notifyDirty()">
        <x-validation-errors />
        <p class="rm-clinical-form__note">Nueva curación. Fecha y hora automáticas al guardar; se conservan las curaciones anteriores.</p>
        <x-ui.form-section title="Herida y medición" icon="ph-ruler" :columns="3" class="rm-clinical-form__section">
            <x-ui.field label="Herida activa" for="curacion-herida" error="lesionId" :required="true" class="rm-clinical-form__full"><select id="curacion-herida" wire:model="lesionId" class="rm-select" aria-invalid="{{ $errors->has('lesionId') ? 'true' : 'false' }}" @error('lesionId') aria-describedby="curacion-herida-error" @enderror><option value="">Seleccione</option>@foreach($adulto->heridas->where('estado', 'ACTIVA') as $herida)<option value="{{ $herida->cod_herida }}">{{ $herida->tipo_herida }} · {{ $herida->ubicacion }}</option>@endforeach</select></x-ui.field>
            <label class="rm-clinical-form__check rm-clinical-form__full"><input id="curacion-medible" type="checkbox" wire:model.live="lesionMedible" @change="if(!$event.target.checked) ['largoLesion','anchoLesion','profundidadLesion'].forEach(field => $wire.$set(field, null, false))"><span>La herida es medible</span></label>
            @if($lesionMedible)
                @foreach(['largoLesion' => 'Largo', 'anchoLesion' => 'Ancho', 'profundidadLesion' => 'Profundidad'] as $campo => $etiqueta)
                    <x-ui.field :label="$etiqueta" :for="'curacion-'.$campo" :error="$campo">
                        <div class="rm-clinical-form__unit"><input id="curacion-{{ $campo }}" wire:model="{{ $campo }}" type="number" step="0.01" min="0" max="99999" inputmode="decimal" class="rm-input" aria-label="{{ $etiqueta }} en centímetros" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="curacion-{{ $campo }}-error" @enderror><span aria-hidden="true">cm</span></div>
                    </x-ui.field>
                @endforeach
            @endif
        </x-ui.form-section>
        <x-ui.form-section title="Observación y procedimiento" icon="ph-note-pencil" :columns="2" class="rm-clinical-form__section">
            <x-ui.field label="Dolor, de 0 a 10" for="curacion-dolor" error="dolorLesion"><input id="curacion-dolor" wire:model="dolorLesion" type="number" min="0" max="10" step="1" class="rm-input" aria-invalid="{{ $errors->has('dolorLesion') ? 'true' : 'false' }}" @error('dolorLesion') aria-describedby="curacion-dolor-error" @enderror></x-ui.field>
            <x-ui.field label="Exudado" for="curacion-exudado" error="exudadoLesion"><input id="curacion-exudado" wire:model="exudadoLesion" maxlength="80" class="rm-input" aria-invalid="{{ $errors->has('exudadoLesion') ? 'true' : 'false' }}" @error('exudadoLesion') aria-describedby="curacion-exudado-error" @enderror></x-ui.field>
            <x-ui.field label="Aspecto / tejido observado" for="curacion-aspecto" error="aspectoLesion" :required="true" help="Descripción breve, hasta 80 caracteres." class="rm-clinical-form__full"><input id="curacion-aspecto" wire:model="aspectoLesion" maxlength="80" class="rm-input" aria-invalid="{{ $errors->has('aspectoLesion') ? 'true' : 'false' }}" aria-describedby="curacion-aspecto-help @error('aspectoLesion') curacion-aspecto-error @enderror"></x-ui.field>
            <x-ui.field label="Procedimiento realizado" for="curacion-procedimiento" error="accionLesion" :required="true" class="rm-clinical-form__full"><textarea id="curacion-procedimiento" wire:model="accionLesion" rows="3" maxlength="2000" class="rm-textarea" aria-invalid="{{ $errors->has('accionLesion') ? 'true' : 'false' }}" @error('accionLesion') aria-describedby="curacion-procedimiento-error" @enderror></textarea></x-ui.field>
            <x-ui.field label="Observación complementaria" for="curacion-observacion" error="observacionLesion" class="rm-clinical-form__full"><textarea id="curacion-observacion" wire:model="observacionLesion" rows="2" maxlength="2000" class="rm-textarea" aria-invalid="{{ $errors->has('observacionLesion') ? 'true' : 'false' }}" @error('observacionLesion') aria-describedby="curacion-observacion-error" @enderror></textarea></x-ui.field>
        </x-ui.form-section>
    </form>
    @endif
    @if($this->puedeMutarRegistro('atenciones.crear'))
    <form id="clinical-wound-close-form" @submit.prevent="prepareFeedback('cierre-herida'); $wire.cerrarLesion($wire.lesionId)" x-show="clinicalOperation === 'cierre-herida' && !clinicalDiscardOpen" class="rm-clinical-form" x-data="rmClinicalCapture()" x-effect="if(clinicalFormOpen && clinicalOperation === 'cierre-herida') $nextTick(() => initialCapture = snapshot())" @input="notifyDirty()" @change="notifyDirty()">
        <x-validation-errors />
        <p class="rm-clinical-form__note">Documenta el cierre de la herida seleccionada. Sus curaciones anteriores seguirán disponibles.</p>
        <x-ui.form-section title="Cierre documentado" icon="ph-check-circle" :columns="1" class="rm-clinical-form__section">
            <x-ui.field label="Resultado clínico al cierre" for="herida-resultado" error="resultadoCierreLesion" :required="true"><textarea id="herida-resultado" wire:model="resultadoCierreLesion" maxlength="1000" rows="3" class="rm-textarea" aria-invalid="{{ $errors->has('resultadoCierreLesion') ? 'true' : 'false' }}" @error('resultadoCierreLesion') aria-describedby="herida-resultado-error" @enderror></textarea></x-ui.field>
            <x-ui.field label="Motivo del cierre" for="herida-motivo" error="motivoCierreLesion" :required="true"><textarea id="herida-motivo" wire:model="motivoCierreLesion" maxlength="1000" rows="3" class="rm-textarea" aria-invalid="{{ $errors->has('motivoCierreLesion') ? 'true' : 'false' }}" @error('motivoCierreLesion') aria-describedby="herida-motivo-error" @enderror></textarea></x-ui.field>
        </x-ui.form-section>
    </form>
    @endif
    <x-slot:footer>
        <div x-show="clinicalDiscardOpen" class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="clinicalDiscardOpen = false">Seguir editando</button><button type="button" class="rm-btn-danger" @click="discardClinicalForm(@js($curacionInicial))">Salir sin guardar</button></div>
        <div x-show="!clinicalDiscardOpen" class="rm-clinical-workspace__actions">
            <button type="button" class="rm-btn-secondary" @click="closeClinicalForm()" wire:loading.attr="disabled" wire:target="guardarSeguimientoLesion,cerrarLesion">Cancelar</button>
            @if($this->puedeMutarRegistro('curaciones_herida.crear'))
                <button x-show="clinicalOperation === 'curacion'" type="submit" form="clinical-wound-form" class="rm-btn-primary rm-btn-primary--confirm" wire:loading.attr="disabled" wire:target="guardarSeguimientoLesion"><span wire:loading.remove wire:target="guardarSeguimientoLesion">Confirmar y registrar curación</span><span wire:loading wire:target="guardarSeguimientoLesion">Registrando…</span></button>
            @endif
            @if($this->puedeMutarRegistro('atenciones.crear'))
                <button x-show="clinicalOperation === 'cierre-herida'" type="submit" form="clinical-wound-close-form" class="rm-btn-primary rm-btn-primary--confirm" wire:loading.attr="disabled" wire:target="cerrarLesion"><span wire:loading.remove wire:target="cerrarLesion">Confirmar cierre de herida</span><span wire:loading wire:target="cerrarLesion">Guardando…</span></button>
            @endif
        </div>
    </x-slot:footer>
</x-ui.modal-livewire>
@endif
@include('livewire.cuidados.partials.clinical-operation-result', ['clinicalResultResidentCode' => $adulto->cod_residente])
