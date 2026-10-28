<div class="rm-captura-instrumental" x-data="{ errorSolicitud: '' }" @rm-valoraciones-error="errorSolicitud = $event.detail.message" @rm-valoraciones-inicio="errorSolicitud = ''">
    @if($mostrar)
        <x-ui.modal-livewire wire:model="mostrar" :id="'captura-instrumental-'.$this->getId()" title="Registro de evaluación" max-width="xl" close-method="cerrar" subtitle="Contexto, respuestas originales y revisión antes de guardar" icon="ph-clipboard-text" class="rm-captura__modal">
            <x-slot:context>
                <dl class="rm-captura__contexto">
                    <div><dt><i class="ph-bold ph-user-circle" aria-hidden="true"></i> Residente</dt><dd>{{ $contexto['residente'] ?? 'Selecciona un residente' }}</dd></div>
                    <div><dt><i class="ph-bold ph-identification-card" aria-hidden="true"></i> Profesional que registra</dt><dd>{{ $contexto['profesional'] ?? 'Selecciona el contexto de la evaluación' }}</dd></div>
                </dl>
            </x-slot:context>
            @error('registro')<p class="rm-error mb-3" role="alert">{{ $message }}</p>@enderror
            <p x-show="errorSolicitud" x-cloak x-text="errorSolicitud" role="alert" class="rm-error"></p>
            @if($confirmarDescarte)
                <x-ui.form-section title="Cambios sin guardar" description="Puedes volver a revisarlos o descartarlos para cerrar." :columns="1" icon="ph-note-pencil">
                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="volver" class="rm-btn rm-btn-secondary">Volver a revisar</button>
                        <button type="button" wire:click="descartar" class="rm-btn rm-btn-danger">Descartar cambios</button>
                    </div>
                </x-ui.form-section>
            @else
                <ol class="rm-captura__etapas" aria-label="Etapas de registro">
                    <li class="{{ !$confirmando ? 'is-current' : '' }}" @if(!$confirmando) aria-current="step" @endif><span>1</span> Completar captura</li>
                    <li class="{{ $confirmando ? 'is-current' : '' }}" @if($confirmando) aria-current="step" @endif><span>2</span> Revisar y registrar</li>
                </ol>
                @if($confirmando)
                    <div class="rm-captura__revision" wire:key="revision-captura" tabindex="-1" x-init="$nextTick(() => $el.focus({ preventScroll: true }))">
                        <x-ui.form-section title="Revisión de la captura" description="Estos datos todavía no están guardados. Comprueba el original antes de confirmar." :columns="1" icon="ph-checks">
                            <dl class="rm-captura__resumen">
                                <div><dt>Instrumento y versión</dt><dd>{{ $resumenCaptura['instrumento'] }} · {{ $resumenCaptura['version'] ?: 'Versión no registrada' }}</dd></div>
                                <div><dt>Fecha y hora originales</dt><dd>{{ \Carbon\CarbonImmutable::parse($fecha_eval)->format('d/m/Y') }} · {{ $hora_eval }}</dd></div>
                                <div><dt>Origen</dt><dd>{{ $transcripcion ? 'Documento original' : 'Aplicación propia' }}</dd></div>
                                <div><dt>Respuestas capturadas</dt><dd>{{ $resumenCaptura['respondidas'] }} de {{ $resumenCaptura['total'] }} · {{ $resumenCaptura['total'] - $resumenCaptura['respondidas'] }} sin registrar</dd></div>
                            </dl>
                            @if($transcripcion)
                                <dl class="rm-captura__resumen">
                                    <div><dt>Documento cotejado</dt><dd>{{ $documentos->firstWhere('cod_documento_clinico', $cod_documento_clinico)?->titulo ?? 'Documento original seleccionado' }}</dd></div>
                                    <div><dt>Página o sección del original</dt><dd>{{ $localizador_fuente }}</dd></div>
                                    @if($clasificacion_original !== '')<div><dt>Clasificación consignada</dt><dd>{{ $clasificacion_original }}</dd></div>@endif
                                    @if($motivo_rectificacion !== '')<div><dt>Motivo de la rectificación</dt><dd>{{ $motivo_rectificacion }}</dd></div>@endif
                                </dl>
                                @if($interpretacion_original !== '')<div><h3 class="font-semibold">Interpretación consignada en el original</h3><p class="whitespace-pre-line break-words">{{ $interpretacion_original }}</p></div>@endif
                            @endif
                            <dl class="rm-captura__respuestas-revision">
                                @foreach($resumenCaptura['respuestas'] as $respuesta)
                                    <div><dt>{{ $loop->iteration }}. {{ $respuesta['pregunta'] }}</dt><dd class="{{ !$respuesta['registrada'] ? 'text-rm-secondary' : '' }}">{{ $respuesta['valor'] }}</dd>
                                        @if($respuesta['puntaje'] !== null && $respuesta['puntaje'] !== '')<dd class="text-sm">Puntuación del original: {{ $respuesta['puntaje'] }}</dd>@endif
                                        @if($respuesta['observacion'] !== null && $respuesta['observacion'] !== '')<dd class="text-sm whitespace-pre-line">{{ $respuesta['observacion'] }}</dd>@endif
                                    </div>
                                @endforeach
                            </dl>
                            @if($observaciones !== '')<div><h3 class="font-semibold">Observaciones de la aplicación</h3><p class="whitespace-pre-line break-words">{{ $observaciones }}</p></div>@endif
                            @if($transcripcion)<p class="rm-captura__ayuda" role="status">{{ match($estadoConciliacion) { 'IDENTICA' => 'Esta fuente ya está registrada con el mismo contenido. Se conservará sin duplicarla.', 'VINCULACION' => 'Se vinculará el original al registro existente sin modificar sus respuestas ni duplicarlo.', 'RECTIFICACION' => 'Se añadirá una rectificación y se conservará la aplicación anterior.', default => 'Documento original preparado para el registro.' } }}</p>@endif
                            <p class="rm-captura__ayuda">El número de respuestas describe la captura; no es una puntuación clínica. La interpretación depende del método disponible.</p>
                        </x-ui.form-section>
                    </div>
                @else
                    <form id="captura-form-{{ $this->getId() }}" wire:submit="revisar" class="rm-captura__formulario">
                        <x-ui.form-section title="Contexto de la evaluación" description="Identifica el instrumento aplicado y cuándo se obtuvo la información." :columns="1" step="1" icon="ph-identification-card">
                    @can('aplicaciones_instrumento.transcribir')
                        <fieldset class="rm-captura__origen">
                            <legend class="rm-label">Origen del registro</legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label for="captura-propia-{{ $this->getId() }}"><input id="captura-propia-{{ $this->getId() }}" type="radio" name="origen-{{ $this->getId() }}" wire:model.live="transcripcion" value="0" @checked(!$transcripcion)> <span>Aplicación propia</span></label>
                                <label for="captura-transcrita-{{ $this->getId() }}"><input id="captura-transcrita-{{ $this->getId() }}" type="radio" name="origen-{{ $this->getId() }}" wire:model.live="transcripcion" value="1" @checked($transcripcion)> <span>Documento original</span></label>
                            </div>
                        </fieldset>
                    @endcan
                    @if($residenteFijo === null)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-ui.field :for="'captura-buscar-'.$this->getId()" label="Buscar residente por nombre">
                                <input id="captura-buscar-{{ $this->getId() }}" class="rm-input" type="search" wire:model.live.debounce.300ms="busquedaResidente" @disabled($confirmando)>
                            </x-ui.field>
                            <x-ui.field :for="'captura-residente-'.$this->getId()" label="Residente" error="cod_residente" required>
<x-ui.selector :id="'captura-residente-'.$this->getId()" label="Residente" wire:model.live="cod_residente" :disabled="$confirmando" :aria-invalid="$errors->has('cod_residente') ? 'true' : 'false'" :aria-describedby="$errors->has('cod_residente') ? 'captura-residente-'.$this->getId().'-error' : null" teleport="">
                                    <option value="">Selecciona un residente</option>
                                    @foreach($residentes as $residente)<option value="{{ $residente->cod_residente }}">{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</option>@endforeach
                                </x-ui.selector>
                            </x-ui.field>
                        </div>
                    @endif
                    <x-ui.field :for="'captura-instrumento-'.$this->getId()" label="Instrumento y versión" error="cod_instrumento" required>
<x-ui.selector :id="'captura-instrumento-'.$this->getId()" label="Instrumento y versión" wire:model.live="cod_instrumento" :disabled="!$cod_residente || $confirmando" :aria-invalid="$errors->has('cod_instrumento') ? 'true' : 'false'" :aria-describedby="$errors->has('cod_instrumento') ? 'captura-instrumento-'.$this->getId().'-error' : null" teleport="">
                            <option value="">Selecciona el instrumento aplicado</option>
                            @foreach($instrumentos as $instrumento)<option value="{{ $instrumento->cod_instrumento }}">{{ $instrumento->nombre }} · {{ $instrumento->version ?: 'Versión no registrada' }}</option>@endforeach
                        </x-ui.selector>
                    </x-ui.field>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-ui.field :for="'captura-fecha-'.$this->getId()" label="Fecha original" error="fecha_eval" required>
<x-ui.calendario :id="'captura-fecha-'.$this->getId()" label="Fecha original" wire:model.live="fecha_eval" :max="now()->format('Y-m-d')" :disabled="$confirmando" :aria-invalid="$errors->has('fecha_eval') ? 'true' : 'false'" :aria-describedby="$errors->has('fecha_eval') ? 'captura-fecha-'.$this->getId().'-error' : null" teleport="" />
                        </x-ui.field>
                        <x-ui.field :for="'captura-hora-'.$this->getId()" label="Hora original" error="hora_eval" required :help="'Zona: '.config('app.timezone')">
                            <input id="captura-hora-{{ $this->getId() }}" type="time" step="1" class="rm-input" wire:model="hora_eval" aria-required="true" aria-invalid="{{ $errors->has('hora_eval') ? 'true' : 'false' }}" aria-describedby="captura-hora-{{ $this->getId() }}-help{{ $errors->has('hora_eval') ? ' captura-hora-'.$this->getId().'-error' : '' }}" @disabled($confirmando)>
                        </x-ui.field>
                    </div>
                        </x-ui.form-section>
                        @if($transcripcion)
                            <x-ui.form-section title="Procedencia del documento" description="Conserva quién realizó el acto y dónde está el original." :columns="1" icon="ph-file-text">
                        <x-ui.field :for="'captura-documento-'.$this->getId()" label="Documento original del residente" required error="cod_documento_clinico">
<x-ui.selector :id="'captura-documento-'.$this->getId()" label="Documento original del residente" wire:model.live="cod_documento_clinico" :disabled="$confirmando" :aria-invalid="$errors->has('cod_documento_clinico') ? 'true' : 'false'" :aria-describedby="$errors->has('cod_documento_clinico') ? 'captura-documento-'.$this->getId().'-error' : null" teleport="">
                                <option value="">Selecciona el documento cotejado</option>
                                @foreach($documentos as $documento)<option value="{{ $documento->getKey() }}">{{ $documento->titulo }}</option>@endforeach
                            </x-ui.selector>
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
                            <p class="text-sm text-rm-secondary">Copia estos datos solo si constan en el documento. No representan una conclusión nueva del sistema.</p>
                            @foreach(['cod_atencion' => 'Código de la atención original, si consta', 'clasificacion_original' => 'Clasificación consignada en el original'] as $campo => $etiqueta)
                                <x-ui.field :for="'captura-'.$campo.'-'.$this->getId()" :label="$etiqueta" :error="$campo">
                                    <input id="captura-{{ $campo }}-{{ $this->getId() }}" class="rm-input" type="text" wire:model="{{ $campo }}" maxlength="{{ $campo === 'cod_atencion' ? 20 : 80 }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @if($errors->has($campo)) aria-describedby="captura-{{ $campo }}-{{ $this->getId() }}-error" @endif @disabled($confirmando)>
                                </x-ui.field>
                            @endforeach
                            <x-ui.field :for="'captura-interpretacion-'.$this->getId()" label="Interpretación consignada en el original" error="interpretacion_original">
                                <textarea id="captura-interpretacion-{{ $this->getId() }}" class="rm-textarea" rows="3" wire:model="interpretacion_original" maxlength="4000" aria-invalid="{{ $errors->has('interpretacion_original') ? 'true' : 'false' }}" @if($errors->has('interpretacion_original')) aria-describedby="captura-interpretacion-{{ $this->getId() }}-error" @endif @disabled($confirmando)></textarea>
                            </x-ui.field>
                        </details>
                            </x-ui.form-section>
                        @endif
                        <x-ui.form-section title="Respuestas y mediciones originales" description="Registra lo obtenido con el protocolo. Cero y «No» son respuestas; lo no medido permanece sin registrar." :columns="1" step="2" icon="ph-list-checks">
                            @if(!$cod_instrumento)
                                <div class="rm-captura__vacio"><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i><p>Selecciona el instrumento para ver sus preguntas disponibles.</p></div>
                            @endif
                    @if($cod_instrumento && $preguntas === [])
                        <p class="text-sm" role="status">Este instrumento no tiene preguntas activas. Su catálogo debe completarse antes de registrar mediciones; no se guardará una aplicación vacía.</p>
                    @endif
                    <div class="space-y-4">
                        @foreach($preguntas as $pregunta)
                            <div class="rm-captura__pregunta" wire:key="pregunta-captura-{{ $pregunta['codigo'] }}">
                                <p class="text-xs text-rm-secondary">Respuesta {{ $loop->iteration }}@if($pregunta['dominio']) · {{ $pregunta['dominio'] }}@endif</p>
                                <x-ui.field :for="'captura-valor-'.$this->getId().'-'.$pregunta['codigo']" :label="$pregunta['enunciado']" :error="'valores.'.$pregunta['codigo']">
                                    @if($pregunta['campo'] === 'valor_logico')
<x-ui.selector :id="'captura-valor-'.$this->getId().'-'.$pregunta['codigo']" :label="$pregunta['enunciado']" wire:model.live="valores.{{ $pregunta['codigo'] }}" :disabled="$confirmando" :aria-invalid="$errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false'" :aria-describedby="$errors->has('valores.'.$pregunta['codigo']) ? 'captura-valor-'.$this->getId().'-'.$pregunta['codigo'].'-error' : null" teleport="">
                                            <option value="">Sin registrar</option><option value="1">Sí</option><option value="0">No</option>
                                        </x-ui.selector>
                                    @elseif($pregunta['campo'] === 'cod_opcion')
<x-ui.selector :id="'captura-valor-'.$this->getId().'-'.$pregunta['codigo']" :label="$pregunta['enunciado']" wire:model.live="valores.{{ $pregunta['codigo'] }}" :disabled="$confirmando" :aria-invalid="$errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false'" :aria-describedby="$errors->has('valores.'.$pregunta['codigo']) ? 'captura-valor-'.$this->getId().'-'.$pregunta['codigo'].'-error' : null" teleport="">
                                            <option value="">Sin registrar</option>
                                            @foreach($pregunta['opciones'] as $opcion)<option value="{{ $opcion['codigo'] }}">{{ $opcion['nombre'] }}</option>@endforeach
                                        </x-ui.selector>
                                    @elseif($pregunta['campo'] === 'valor_numero')
                                        <input id="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" type="number" step="0.0001" class="rm-input" wire:model.live.debounce.350ms="valores.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('valores.'.$pregunta['codigo'])) aria-describedby="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)>
                                    @elseif($pregunta['campo'] === 'valor_texto')
                                        <textarea id="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" rows="2" class="rm-textarea" wire:model.live.debounce.350ms="valores.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('valores.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('valores.'.$pregunta['codigo'])) aria-describedby="captura-valor-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)></textarea>
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
                                            <textarea id="captura-nota-{{ $this->getId() }}-{{ $pregunta['codigo'] }}" rows="2" maxlength="4000" class="rm-textarea" wire:model="observacionesRespuesta.{{ $pregunta['codigo'] }}" aria-invalid="{{ $errors->has('observacionesRespuesta.'.$pregunta['codigo']) ? 'true' : 'false' }}" @if($errors->has('observacionesRespuesta.'.$pregunta['codigo'])) aria-describedby="captura-nota-{{ $this->getId() }}-{{ $pregunta['codigo'] }}-error" @endif @disabled($confirmando)></textarea>
                                        </x-ui.field>
                                    </details>
                                @endif
                            </div>
                        @endforeach
                    </div>
                        </x-ui.form-section>
                        <x-ui.form-section title="Observaciones y condiciones" description="Añade el contexto que ayude a comprender la aplicación. No sustituye las respuestas del protocolo." :columns="1" step="3" icon="ph-note-pencil">
                    <x-ui.field :for="'captura-observaciones-'.$this->getId()" label="Observaciones de la aplicación" error="observaciones">
                        <textarea id="captura-observaciones-{{ $this->getId() }}" rows="4" class="rm-textarea" maxlength="4000" wire:model="observaciones" aria-invalid="{{ $errors->has('observaciones') ? 'true' : 'false' }}" @if($errors->has('observaciones')) aria-describedby="captura-observaciones-{{ $this->getId() }}-error" @endif @disabled($confirmando)></textarea>
                    </x-ui.field>
                            <x-ui.cognitive-context-guide :id="'captura-guia-'.$this->getId()" :nivel-educativo="$contexto['nivel_educativo'] ?? null" />
                        </x-ui.form-section>
                    @if($cod_instrumento && $vinculoMetodo !== [])
                        <details class="rm-captura__guia"><summary>Alcance de la interpretación</summary>
                            @if(($vinculoMetodo['estado'] ?? null) === 'OBSERVACIONAL')
                                <p class="font-semibold">Registro observacional</p>
                                <p class="text-sm text-rm-secondary">Estas respuestas conservan observaciones clínicas. No se interpretan como medidas de una prueba cognitiva.</p>
                            @elseif(($vinculoMetodo['estado'] ?? null) === 'VINCULADO')
                                <p class="font-semibold">Método vinculado al protocolo</p>
                                <p class="text-sm text-rm-secondary">Componentes: {{ implode(', ', $vinculoMetodo['componentes']) }}. La identidad configurada debe coincidir con el protocolo realmente aplicado; esta vinculación no certifica la aplicación.</p>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-sm">
                                    @foreach($vinculoMetodo['identidad'] as $nombre => $valor)
                                        <div><dt class="inline font-semibold">{{ str_replace('_', ' ', $nombre) }}:</dt> <dd class="inline">{{ is_scalar($valor) ? $valor : 'Sin identificar' }}</dd></div>
                                    @endforeach
                                </dl>
                                <p class="text-sm text-rm-secondary">Registra solo resultados originales. Las entradas de escolaridad y condiciones se capturan como preguntas del protocolo cuando están definidas.</p>
                            @else
                                <p class="font-semibold">Sin método de interpretación listo</p>
                                <p class="text-sm text-rm-secondary">Puedes conservar las respuestas originales. La interpretación automatizada se abstendrá de interpretarlas hasta que exista un vínculo único, exacto y completo con el método aplicable.</p>
                            @endif
                            @if(($vinculoMetodo['entradas'] ?? []) !== [])
                                <details>
                                    <summary class="cursor-pointer font-semibold">Entradas requeridas y vínculo con tareas</summary>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        @foreach($vinculoMetodo['entradas'] as $entrada)
                                            <li>{{ str_replace('_', ' ', $entrada['nombre']) }} ({{ $entrada['tipo'] }}): {{ $entrada['disponible'] ? 'dato vinculado' : 'falta dato compatible' }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </details>
                    @endif
                    </form>
                @endif
            @endif
            <x-slot:footer>
                @if(!$confirmarDescarte)
                    <p class="rm-captura__pie-ayuda" role="status" aria-live="polite">{{ $confirmando ? 'Pendiente de confirmar el registro' : $resumenCaptura['respondidas'].' de '.$resumenCaptura['total'].' respuestas capturadas' }}</p>
                    <div class="rm-captura__acciones">
                        <button type="button" wire:click="cerrar" class="rm-btn rm-btn-secondary" wire:loading.attr="disabled">Cerrar</button>
                        @if($confirmando)
                            <button type="button" wire:click="volver" class="rm-btn rm-btn-secondary" wire:loading.attr="disabled">Volver a editar</button>
                            <button type="button" wire:click="guardar" class="rm-btn rm-btn-primary" wire:loading.attr="disabled" wire:target="guardar"><span wire:loading.remove wire:target="guardar">Confirmar y registrar</span><span wire:loading wire:target="guardar">Registrando…</span></button>
                        @else
                            <button type="submit" form="captura-form-{{ $this->getId() }}" class="rm-btn rm-btn-primary" wire:loading.attr="disabled" @disabled(!$cod_instrumento || $preguntas === [])>Revisar captura <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>
                        @endif
                    </div>
                @endif
            </x-slot:footer>
        </x-ui.modal-livewire>
    @endif
</div>
