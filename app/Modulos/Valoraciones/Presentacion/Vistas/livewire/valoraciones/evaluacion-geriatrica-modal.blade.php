<div>
    @if($mostrar)
        <x-ui.modal-livewire wire:model="mostrar" :id="'captura-instrumental-'.$this->getId()" title="Registrar mediciones instrumentales" max-width="lg" close-method="cerrar" subtitle="Respuestas originales, fecha de aplicación y profesional responsable">
            <x-slot:context>
                <div class="px-5 sm:px-6 py-3 text-sm break-words">
                    <p><strong>Residente:</strong> {{ $contexto['residente'] ?? 'Selecciona un residente' }}</p>
                    @if(isset($contexto['profesional']))<p class="mt-1"><strong>Profesional:</strong> {{ $contexto['profesional'] }}</p>@endif
                </div>
            </x-slot:context>
            @error('registro')<p class="rm-error mb-3" role="alert">{{ $message }}</p>@enderror
            @if($confirmarDescarte)
                <div class="space-y-4" role="alert">
                    <p>Hay cambios sin registrar. Puedes volver a revisarlos o descartarlos para cerrar.</p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="volver" class="rm-btn rm-btn-secondary">Volver a revisar</button>
                        <button type="button" wire:click="descartar" class="rm-btn rm-btn-danger">Descartar cambios</button>
                    </div>
                </div>
            @else
                <form wire:submit="revisar" class="space-y-4">
                    @if($residenteFijo === null)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-ui.field :for="'captura-buscar-'.$this->getId()" label="Buscar residente por nombre">
                                <input id="captura-buscar-{{ $this->getId() }}" class="rm-input" type="search" wire:model.live.debounce.300ms="busquedaResidente" @disabled($confirmando)>
                            </x-ui.field>
                            <x-ui.field :for="'captura-residente-'.$this->getId()" label="Residente" error="cod_residente" required>
                                <select id="captura-residente-{{ $this->getId() }}" class="rm-input" wire:model.live="cod_residente" aria-required="true" aria-invalid="{{ $errors->has('cod_residente') ? 'true' : 'false' }}" @if($errors->has('cod_residente')) aria-describedby="captura-residente-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                                    <option value="">Selecciona un residente</option>
                                    @foreach($residentes as $residente)<option value="{{ $residente->cod_residente }}">{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</option>@endforeach
                                </select>
                            </x-ui.field>
                        </div>
                    @endif
                    <x-ui.field :for="'captura-instrumento-'.$this->getId()" label="Instrumento y versión" error="cod_instrumento" required>
                        <select id="captura-instrumento-{{ $this->getId() }}" class="rm-input" wire:model.live="cod_instrumento" aria-required="true" aria-invalid="{{ $errors->has('cod_instrumento') ? 'true' : 'false' }}" @if($errors->has('cod_instrumento')) aria-describedby="captura-instrumento-{{ $this->getId() }}-error" @endif @disabled(!$cod_residente || $confirmando)>
                            <option value="">Selecciona el instrumento aplicado</option>
                            @foreach($instrumentos as $instrumento)<option value="{{ $instrumento->cod_instrumento }}">{{ $instrumento->nombre }} · {{ $instrumento->version ?: 'Versión no registrada' }}</option>@endforeach
                        </select>
                    </x-ui.field>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-ui.field :for="'captura-fecha-'.$this->getId()" label="Fecha original" error="fecha_eval" required>
                            <input id="captura-fecha-{{ $this->getId() }}" type="date" class="rm-input" wire:model="fecha_eval" aria-required="true" aria-invalid="{{ $errors->has('fecha_eval') ? 'true' : 'false' }}" @if($errors->has('fecha_eval')) aria-describedby="captura-fecha-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                        </x-ui.field>
                        <x-ui.field :for="'captura-hora-'.$this->getId()" label="Hora original" error="hora_eval" required :help="'Zona: '.config('app.timezone')">
                            <input id="captura-hora-{{ $this->getId() }}" type="time" step="1" class="rm-input" wire:model="hora_eval" aria-required="true" aria-invalid="{{ $errors->has('hora_eval') ? 'true' : 'false' }}" aria-describedby="captura-hora-{{ $this->getId() }}-help{{ $errors->has('hora_eval') ? ' captura-hora-'.$this->getId().'-error' : '' }}" @disabled($confirmando)>
                        </x-ui.field>
                    </div>
                    @if($confirmando)
                        <p class="text-sm font-semibold" role="status">Revisa el residente, la fecha y las respuestas. Al confirmar se registrará la captura; su interpretación depende del método disponible.</p>
                    @endif
                    @if($cod_instrumento && $preguntas === [])
                        <p class="text-sm" role="status">Este instrumento no tiene preguntas activas. Su catálogo debe completarse antes de registrar mediciones; no se guardará una aplicación vacía.</p>
                    @endif
                    <p class="text-sm text-rm-secondary">Deja sin responder lo que no se midió. Cero y «No» son respuestas; no equivalen a ausencia de dato.</p>
                    <div class="space-y-4">
                        @foreach($preguntas as $pregunta)
                            <div wire:key="pregunta-captura-{{ $pregunta['codigo'] }}">
                                <x-ui.field :for="'captura-valor-'.$this->getId().'-'.$pregunta['codigo']" :label="$pregunta['enunciado']" :error="'valores.'.$pregunta['codigo']">
                                    @if($pregunta['campo'] === 'valor_logico')
                                        <select id="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" class="rm-input" wire:model="valores.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('valores.'.$pregunta['codigo'])) aria-describedby="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)>
                                            <option value="">Sin registrar</option><option value="1">Sí</option><option value="0">No</option>
                                        </select>
                                    @elseif($pregunta['campo'] === 'cod_opcion')
                                        <select id="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" class="rm-input" wire:model="valores.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('valores.'.$pregunta['codigo'])) aria-describedby="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)>
                                            <option value="">Sin registrar</option>
                                            @foreach($pregunta['opciones'] as $opcion)<option value="{{ $opcion['codigo'] }}">{{ $opcion['nombre'] }}</option>@endforeach
                                        </select>
                                    @elseif($pregunta['campo'] === 'valor_numero')
                                        <input id="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" type="number" step="0.0001" class="rm-input" wire:model="valores.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('valores.'.$pregunta['codigo'])) aria-describedby="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)>
                                    @elseif($pregunta['campo'] === 'valor_texto')
                                        <textarea id="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" rows="2" class="rm-input" wire:model="valores.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('valores.'.$pregunta['codigo'])) aria-describedby="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)></textarea>
                                    @else
                                        <p class="text-sm" role="status">El tipo de respuesta de esta pregunta requiere revisión del catálogo.</p>
                                    @endif
                                </x-ui.field>
                            </div>
                        @endforeach
                    </div>
                    <x-ui.field :for="'captura-observaciones-'.$this->getId()" label="Observaciones de la aplicación" error="observaciones">
                        <textarea id="captura-observaciones-{{ $this->getId() }}" rows="3" class="rm-input" wire:model="observaciones" aria-invalid="{{ $errors->has('observaciones') ? 'true' : 'false' }}" @if($errors->has('observaciones')) aria-describedby="captura-observaciones-{{ $this->getId() }}-error" @endif @disabled($confirmando)></textarea>
                    </x-ui.field>
                    <div class="flex flex-wrap justify-end gap-3">
                        <button type="button" wire:click="cerrar" class="rm-btn rm-btn-secondary" wire:loading.attr="disabled">Cerrar</button>
                        @if($confirmando)
                            <button type="button" wire:click="volver" class="rm-btn rm-btn-secondary" wire:loading.attr="disabled">Volver a editar</button>
                            <button type="button" wire:click="guardar" class="rm-btn rm-btn-primary" wire:loading.attr="disabled" wire:target="guardar">
                                <span wire:loading.remove wire:target="guardar">Confirmar y registrar</span><span wire:loading wire:target="guardar">Registrando…</span>
                            </button>
                        @else
                            <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled" @disabled(!$cod_instrumento || $preguntas === [])>Revisar captura</button>
                        @endif
                    </div>
                </form>
            @endif
        </x-ui.modal-livewire>
    @endif
</div>
