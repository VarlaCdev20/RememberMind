<div>
    @if($mostrar)
        <x-ui.modal-livewire wire:model="mostrar" :id="'captura-instrumental-'.$this->getId()" title="Registrar aplicación y respuestas" max-width="lg" close-method="cerrar" subtitle="Tareas originales, fecha de aplicación y profesional responsable">
            <x-slot:context>
                <div class="px-5 sm:px-6 py-3 text-sm break-words">
                    <p><strong>Residente:</strong> {{ $contexto['residente'] ?? 'Selecciona un residente' }}</p>
                    @if(isset($contexto['profesional']))<p class="mt-1"><strong>Profesional que registra:</strong> {{ $contexto['profesional'] }}</p>@endif
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
                    @can('aplicaciones_instrumento.transcribir')
                        <x-ui.field :for="'captura-origen-'.$this->getId()" label="Origen del registro">
                            <select id="captura-origen-{{ $this->getId() }}" class="rm-input" wire:model.live="transcripcion" @disabled($confirmando)>
                                <option value="0">Aplicación propia</option><option value="1">Transcripción de un documento original</option>
                            </select>
                        </x-ui.field>
                    @endcan
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
                    @if($cod_instrumento && $vinculoMetodo !== [])
                        <section class="rounded-lg border p-4 space-y-2" aria-label="Vinculación del método de captura">
                            @if(($vinculoMetodo['estado'] ?? null) === 'OBSERVACIONAL')
                                <p class="font-semibold">Registro observacional</p>
                                <p class="text-sm text-rm-secondary">Estas respuestas conservan observaciones clínicas. No se interpretan como medidas de una prueba cognitiva.</p>
                            @elseif(($vinculoMetodo['estado'] ?? null) === 'VINCULADO')
                                <p class="font-semibold">Método vinculado: {{ $vinculoMetodo['metodo'] }}</p>
                                <p class="text-sm text-rm-secondary">Componentes: {{ implode(', ', $vinculoMetodo['componentes']) }}. La identidad configurada debe coincidir con el protocolo realmente aplicado; esta vinculación no certifica la aplicación.</p>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-sm">
                                    @foreach($vinculoMetodo['identidad'] as $nombre => $valor)
                                        <div><dt class="inline font-semibold">{{ str_replace('_', ' ', $nombre) }}:</dt> <dd class="inline">{{ is_scalar($valor) ? $valor : 'Sin identificar' }}</dd></div>
                                    @endforeach
                                </dl>
                                <p class="text-sm text-rm-secondary">Registra solo resultados originales. Las entradas de escolaridad y condiciones se capturan como preguntas del protocolo cuando están definidas.</p>
                            @else
                                <p class="font-semibold">Sin método de interpretación listo</p>
                                <p class="text-sm text-rm-secondary">Puedes conservar las respuestas originales. O.R.I.O.N. se abstendrá de interpretarlas hasta que exista un vínculo único, exacto y completo con el método aplicable.</p>
                            @endif
                            @if(($vinculoMetodo['entradas'] ?? []) !== [])
                                <details>
                                    <summary class="cursor-pointer font-semibold">Entradas requeridas y vínculo con tareas</summary>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        @foreach($vinculoMetodo['entradas'] as $entrada)
                                            <li>{{ str_replace('_', ' ', $entrada['nombre']) }} ({{ $entrada['tipo'] }}): {{ $entrada['disponible'] ? 'tarea '.$entrada['codigo_tarea'] : 'falta tarea compatible' }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </section>
                    @endif
                    @if($transcripcion)
                        <x-ui.field :for="'captura-documento-'.$this->getId()" label="Documento original del residente" required error="cod_documento_clinico">
                            <select id="captura-documento-{{ $this->getId() }}" class="rm-input" wire:model="cod_documento_clinico" aria-invalid="{{ $errors->has('cod_documento_clinico') ? 'true' : 'false' }}" @if($errors->has('cod_documento_clinico')) aria-describedby="captura-documento-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                                <option value="">Selecciona el documento cotejado</option>
                                @foreach($documentos as $documento)<option value="{{ $documento->getKey() }}">{{ $documento->titulo }} · {{ $documento->getKey() }}</option>@endforeach
                            </select>
                        </x-ui.field>
                        @foreach(['cod_personal_original' => 'Código del profesional aplicador original', 'localizador_fuente' => 'Página, ficha o fila del original',
                            'cod_aplicacion_existente' => 'Aplicación ya registrada, solo para vincular su original',
                            'cod_aplicacion_anterior' => 'Aplicación anterior, solo si rectifica', 'motivo_rectificacion' => 'Motivo de la rectificación'] as $campo => $etiqueta)
                            <x-ui.field :for="'captura-'.$campo.'-'.$this->getId()" :label="$etiqueta" :error="$campo">
                                <input id="captura-{{ $campo }}-{{ $this->getId() }}" class="rm-input" type="text" wire:model="{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @if($errors->has($campo)) aria-describedby="captura-{{ $campo }}-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                            </x-ui.field>
                        @endforeach
                        <label class="flex items-start gap-3" for="captura-cotejo-{{ $this->getId() }}">
                            <input id="captura-cotejo-{{ $this->getId() }}" type="checkbox" wire:model="original_cotejado" aria-invalid="{{ $errors->has('original_cotejado') ? 'true' : 'false' }}" @if($errors->has('original_cotejado')) aria-describedby="captura-cotejo-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                            He cotejado la firma, la fecha, el instrumento y las respuestas con el documento original.
                        </label>
                        @error('original_cotejado')<p id="captura-cotejo-{{ $this->getId() }}-error" role="alert" class="rm-error">{{ $message }}</p>@enderror
                        <p class="text-sm text-rm-secondary">Conserva el autor y la hora que figuran en el original. Una rectificación añade otra aplicación; no reemplaza la anterior.</p>
                        <details class="space-y-3" @if($errors->has('cod_atencion') || $errors->has('clasificacion_original') || $errors->has('interpretacion_original')) open @endif>
                            <summary class="cursor-pointer font-semibold">Datos adicionales que figuran en el original</summary>
                            <p class="text-sm text-rm-secondary">Copia estos datos solo si constan en el documento. No representan una conclusión nueva de O.R.I.O.N.</p>
                            @foreach(['cod_atencion' => 'Código de la atención original, si consta', 'clasificacion_original' => 'Clasificación consignada en el original'] as $campo => $etiqueta)
                                <x-ui.field :for="'captura-'.$campo.'-'.$this->getId()" :label="$etiqueta" :error="$campo">
                                    <input id="captura-{{ $campo }}-{{ $this->getId() }}" class="rm-input" type="text" wire:model="{{ $campo }}" maxlength="{{ $campo === 'cod_atencion' ? 20 : 80 }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @if($errors->has($campo)) aria-describedby="captura-{{ $campo }}-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                                </x-ui.field>
                            @endforeach
                            <x-ui.field :for="'captura-interpretacion-'.$this->getId()" label="Interpretación consignada en el original" error="interpretacion_original">
                                <textarea id="captura-interpretacion-{{ $this->getId() }}" class="rm-input" rows="3" wire:model="interpretacion_original" maxlength="4000" aria-invalid="{{ $errors->has('interpretacion_original') ? 'true' : 'false' }}" @if($errors->has('interpretacion_original')) aria-describedby="captura-interpretacion-{{ $this->getId() }}-error" @endif @disabled($confirmando)></textarea>
                            </x-ui.field>
                        </details>
                    @endif
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
                        @if($transcripcion)<p class="text-sm" role="status">Conciliación: {{ match($estadoConciliacion) { 'IDENTICA' => 'Esta fuente ya está registrada con el mismo contenido. Se conservará sin duplicarla.', 'VINCULACION' => 'Se vinculará el original al registro existente sin modificar sus respuestas ni duplicarlo.', 'RECTIFICACION' => 'Se añadirá una rectificación y se conservará la aplicación anterior.', default => 'Fuente nueva, lista para registrar.' } }}</p>@endif
                    @endif
                    @if($cod_instrumento && $preguntas === [])
                        <p class="text-sm" role="status">Este instrumento no tiene preguntas activas. Su catálogo debe completarse antes de registrar mediciones; no se guardará una aplicación vacía.</p>
                    @endif
                    <p class="text-sm text-rm-secondary">Deja sin responder lo que no se midió. Cero y «No» son respuestas; no equivalen a ausencia de dato.</p>
                    <div class="space-y-4">
                        @foreach($preguntas as $pregunta)
                            <div wire:key="pregunta-captura-{{ $pregunta['codigo'] }}">
                                <p class="text-xs text-rm-secondary">Tarea {{ $pregunta['codigo_catalogo'] }}@if($pregunta['dominio']) · {{ $pregunta['dominio'] }}@endif · {{ $pregunta['tipo'] }}</p>
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
                                @if($transcripcion && $pregunta['campo'] !== null)
                                    <details class="mt-2 space-y-3" @if($errors->has('puntajesOriginales.'.$pregunta['codigo']) || $errors->has('observacionesRespuesta.'.$pregunta['codigo'])) open @endif>
                                        <summary class="cursor-pointer text-sm font-semibold">Puntuación y observación del original, si constan</summary>
                                        <x-ui.field :for="'captura-puntaje-'.$this->getId().'-'.$pregunta['codigo']" label="Puntuación original de esta respuesta" :error="'puntajesOriginales.'.$pregunta['codigo']" help="Deja vacío si no consta. Cero debe escribirse como 0; no se completa desde el catálogo actual.">
                                            <input id="captura-puntaje-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" type="number" step="0.01" min="0" max="999999.99" class="rm-input" wire:model="puntajesOriginales.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('puntajesOriginales.'.$pregunta['codigo']) ? 'true' : 'false' }}" aria-describedby="captura-puntaje-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-help{{ $errors->has('puntajesOriginales.'.$pregunta['codigo']) ? ' captura-puntaje-'.$this->getId().'-'.$pregunta['codigo'].'-error' : '' }}" @disabled($confirmando)>
                                        </x-ui.field>
                                        <x-ui.field :for="'captura-nota-'.$this->getId().'-'.$pregunta['codigo']" label="Observación original de esta respuesta" :error="'observacionesRespuesta.'.$pregunta['codigo']">
                                            <textarea id="captura-nota-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" rows="2" maxlength="4000" class="rm-input" wire:model="observacionesRespuesta.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('observacionesRespuesta.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('observacionesRespuesta.'.$pregunta['codigo'])) aria-describedby="captura-nota-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)></textarea>
                                        </x-ui.field>
                                    </details>
                                @endif
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
