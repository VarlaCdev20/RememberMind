@php
    $opcionesRegistro = [
        ['tipo' => 'signos', 'titulo' => 'Signos vitales', 'descripcion' => 'Registra controles de salud.', 'icono' => 'ph-heartbeat', 'permiso' => 'signos_vitales.crear'],
        ['tipo' => 'medicacion', 'titulo' => 'Medicación programada', 'descripcion' => 'Administra dosis indicadas.', 'icono' => 'ph-pill', 'permiso' => 'administraciones_medicacion.crear'],
        ['tipo' => 'dolor', 'titulo' => 'Valoración de dolor', 'descripcion' => 'Registra la intensidad del dolor.', 'icono' => 'ph-thermometer', 'permiso' => 'valoraciones_dolor.crear'],
        ['tipo' => 'alimentacion', 'titulo' => 'Cuidado de alimentación', 'descripcion' => 'Registra ingesta y tolerancia.', 'icono' => 'ph-bowl-food', 'permiso' => 'registros_ingesta.crear'],
        ['tipo' => 'eliminacion', 'titulo' => 'Cuidado de eliminación', 'descripcion' => 'Registra patrones de eliminación.', 'icono' => 'ph-drop', 'permiso' => 'registros_eliminacion.crear'],
        ['tipo' => 'movilidad', 'titulo' => 'Cuidado de movilidad', 'descripcion' => 'Registra apoyo y traslado.', 'icono' => 'ph-person-simple-walk', 'permiso' => 'registros_movilidad.crear'],
        ['tipo' => 'seguimiento', 'titulo' => 'Seguimiento diario', 'descripcion' => 'Registra evolución del turno.', 'icono' => 'ph-clipboard-text', 'permiso' => 'atenciones.crear'],
        ['tipo' => 'procedimiento', 'titulo' => 'Curación o procedimiento', 'descripcion' => 'Registra procedimiento realizado.', 'icono' => 'ph-first-aid', 'permiso' => 'atenciones.crear'],
    ];
    $tituloFormulario = collect($opcionesRegistro)->firstWhere('tipo', $registroTipo)['titulo'] ?? 'Registro de enfermería';
@endphp

<div class="rm-resident-directory__register">
    @if($drawerPaso === 'register-selector')
        @include('livewire.cuidados.partials.quick-register-selector')
    @elseif($drawerPaso === 'register-form')
        @if($registroTipo !== 'signos')
            <h4 class="rm-resident-directory__form-title">{{ $tituloFormulario }}</h4>
            <p class="rm-resident-directory__register-intro">Completa los datos del registro. Se guardarán en el expediente de este residente.</p>
        @endif
        <div class="rm-resident-directory__form-fields">
            @switch($registroTipo)
                @case('signos')
                    @include('livewire.cuidados.partials.mis-residentes-signos-vitales')
                @break

                @case('medicacion')
                    @if(!$medOcurrenciaSeleccionada)
                        <p class="rm-resident-directory__form-note">Selecciona la dosis programada que vas a registrar. Cada opción corresponde a una prescripción y horario reales de este residente.</p>
                        <div class="rm-resident-directory__register-options">
                            @foreach($medOpcionesProgramadas as $opcion)
                                <button type="button" class="rm-resident-directory__register-option" wire:click="seleccionarOcurrenciaMed('{{ $opcion['cod_horario'] }}')">
                                    <span class="rm-resident-directory__register-option-copy">
                                        <strong>{{ $opcion['medicamento'] }}</strong>
                                        <small>{{ $opcion['hora'] }} · {{ $opcion['dosis'] }} {{ $opcion['unidad'] }}</small>
                                    </span>
                                    <i class="ph-bold ph-caret-right" aria-hidden="true"></i>
                                </button>
                            @endforeach
                        </div>
                        @error('medOcurrenciaSeleccionada') <small role="alert" class="rm-resident-directory__form-error">{{ $message }}</small> @enderror
                    @else
                        @error('medOcurrenciaSeleccionada') <small role="alert" class="rm-resident-directory__form-error">{{ $message }}</small> @enderror
                        <section class="rm-resident-directory__form-section" aria-labelledby="med-prescripcion-title">
                            <h5 id="med-prescripcion-title">Prescripción médica</h5>
                            <p class="rm-resident-directory__form-note">Datos de la orden médica, solo lectura.</p>
                            <div class="rm-resident-directory__form-grid">
                                <label class="rm-resident-directory__field">Medicamento<input type="text" value="{{ $medDetalleProgramado['medicamento'] ?? '—' }}" readonly></label>
                                <label class="rm-resident-directory__field">Presentación<input type="text" value="{{ $medDetalleProgramado['presentacion'] ?: 'No registrada' }}" readonly></label>
                                <label class="rm-resident-directory__field">Dosis prescrita<input type="text" value="{{ $medDetalleProgramado['dosis_prescrita'] ?? 'No registrada' }}" readonly></label>
                                <label class="rm-resident-directory__field">Unidad<input type="text" value="{{ $medDetalleProgramado['unidad'] ?: 'No registrada' }}" readonly></label>
                                <label class="rm-resident-directory__field">Vía<input type="text" value="{{ $medDetalleProgramado['via'] }}" readonly></label>
                                <label class="rm-resident-directory__field">Frecuencia<input type="text" value="{{ $medDetalleProgramado['frecuencia'] ?: 'No registrada' }}" readonly></label>
                                <label class="rm-resident-directory__field">Hora programada<input type="text" value="{{ $medDetalleProgramado['hora'] }}" readonly></label>
                                <label class="rm-resident-directory__field">Dosis programada<input type="text" value="{{ $medDetalleProgramado['dosis_programada'] ?? 'Según prescripción' }}" readonly></label>
                            </div>
                        </section>
                        <section class="rm-resident-directory__form-section" aria-labelledby="med-administracion-title">
                            <h5 id="med-administracion-title">Administración</h5>
                            <div class="rm-resident-directory__form-grid">
                                <label class="rm-resident-directory__field">Estado
                                    <select wire:model.live="medResultado">
                                        <option value="ADMINISTRADA">Administrada</option>
                                        <option value="OMITIDA">Omitida</option>
                                    </select>
                                    @error('medResultado') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <label class="rm-resident-directory__field">Vía prescrita<input type="text" value="{{ $medDetalleProgramado['via'] }}" readonly></label>
                                @if($medResultado === 'ADMINISTRADA')
                                    <label class="rm-resident-directory__field">Fecha y hora de administración
                                        <input type="datetime-local" wire:model="medFechaHoraReal" required>
                                        @error('medFechaHoraReal') <small role="alert">{{ $message }}</small> @enderror
                                    </label>
                                    <label class="rm-resident-directory__field">Dosis administrada <small>{{ $medDetalleProgramado['unidad'] }}</small>
                                        <input type="number" wire:model="medDosisAdministrada" min="0.001" max="9999999.999" step="0.001" required>
                                        @error('medDosisAdministrada') <small role="alert">{{ $message }}</small> @enderror
                                    </label>
                                @elseif($medResultado === 'OMITIDA')
                                    <label class="rm-resident-directory__field">Motivo
                                        <textarea wire:model="medMotivoOmision" maxlength="500" rows="3" required placeholder="Explica por qué no se administró la dosis"></textarea>
                                        @error('medMotivoOmision') <small role="alert">{{ $message }}</small> @enderror
                                    </label>
                                @endif
                            </div>
                            <label class="rm-resident-directory__field">Observaciones
                                <textarea wire:model="medObservacion" maxlength="2000" rows="3" placeholder="Observaciones del registro, si corresponde"></textarea>
                                @error('medObservacion') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </section>
                    @endif
                @break

                @case('dolor')
                    <section class="rm-resident-directory__form-section" aria-labelledby="dolor-datos-title">
                        <h5 id="dolor-datos-title">Datos del registro</h5>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Fecha y hora de valoración
                                <input type="datetime-local" wire:model="dolorFechaHora" required>
                                @error('fecha_hora') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="rm-resident-directory__field">Registrado por
                                <input type="text" value="{{ trim(auth()->user()->nombres.' '.auth()->user()->ap_paterno.' '.auth()->user()->ap_materno) }}" readonly>
                            </label>
                        </div>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="dolor-eva-title">
                        <h5 id="dolor-eva-title">Intensidad inicial</h5>
                        <fieldset class="rm-resident-directory__eva" aria-describedby="dolor-eva-ayuda">
                            <legend>Escala EVA inicial, de 0 a 10</legend>
                            <div class="rm-resident-directory__eva-options">
                                @foreach(range(0, 10) as $valor)
                                    <label class="rm-resident-directory__eva-option">
                                        <input type="radio" name="dolor-eva-inicial" value="{{ $valor }}" wire:model.live="dolorEva">
                                        <span>{{ $valor }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <p id="dolor-eva-ayuda" class="rm-resident-directory__form-note">0 = sin dolor; 10 = máxima intensidad de la escala.</p>
                        @error('intensidad') <small role="alert" class="rm-resident-directory__form-error">{{ $message }}</small> @enderror
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="dolor-caracteristicas-title">
                        <h5 id="dolor-caracteristicas-title">Características</h5>
                        <label class="rm-resident-directory__field">Localización
                            <input type="text" wire:model="dolorUbicacion" maxlength="120" placeholder="Describe dónde se localiza el dolor">
                            @error('ubicacion') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Duración, valor
                                <input type="number" wire:model="dolorDuracionValor" min="0.01" step="any" placeholder="Ej. 30">
                                @error('duracion_valor') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="rm-resident-directory__field">Duración, unidad
                                <input type="text" wire:model="dolorDuracionUnidad" maxlength="60" placeholder="Ej. minutos">
                                @error('duracion_unidad') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </div>
                        <label class="rm-resident-directory__field">Desencadenante
                            <textarea wire:model="dolorDesencadenante" rows="2" placeholder="Circunstancias que desencadenaron el dolor, si se conocen"></textarea>
                            @error('desencadenante') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <p class="rm-resident-directory__form-note">El tipo de dolor no dispone de opciones validadas. La hora de inicio del dolor no se registra por separado.</p>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="dolor-intervencion-title">
                        <h5 id="dolor-intervencion-title">Intervención</h5>
                        <label class="rm-resident-directory__field">Intervención realizada, si corresponde
                            <textarea wire:model="dolorIntervencion" rows="3" placeholder="Medidas realizadas durante esta valoración"></textarea>
                            @error('intervencion') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="dolor-reevaluacion-title">
                        <h5 id="dolor-reevaluacion-title">Reevaluación</h5>
                        <p class="rm-resident-directory__form-note">EVA posterior y hora de reevaluación no están disponibles en este registro.</p>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="dolor-observaciones-title">
                        <h5 id="dolor-observaciones-title">Observaciones</h5>
                        <label class="rm-resident-directory__field">Observaciones generales
                            <textarea rows="3" disabled aria-disabled="true" placeholder="No disponibles en este registro"></textarea>
                        </label>
                        <p class="rm-resident-directory__form-note">Este campo todavía no se puede guardar en la valoración de dolor.</p>
                    </section>
                @break

                @case('alimentacion')
                    <section class="rm-resident-directory__form-section" aria-labelledby="ingesta-datos-title">
                        <h5 id="ingesta-datos-title">Datos del registro</h5>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Fecha y hora
                                <input type="text" value="{{ now()->format('d/m/Y H:i') }}" readonly>
                            </label>
                            <label class="rm-resident-directory__field">Turno
                                <input type="text" value="{{ app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class)->obtenerTurnoActivo(auth()->user())?->nombre ?? 'Sin turno activo' }}" readonly>
                            </label>
                            <label class="rm-resident-directory__field">Registrado por
                                <input type="text" value="{{ trim(auth()->user()->nombres.' '.auth()->user()->ap_paterno.' '.auth()->user()->ap_materno) }}" readonly>
                            </label>
                        </div>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="ingesta-comida-title">
                        <h5 id="ingesta-comida-title">Comida</h5>
                        <label class="rm-resident-directory__field">Tipo de comida
                            <select wire:model="ingestaTipoComida" required>
                                <option value="">Seleccionar</option>
                                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::TIPOS_COMIDA as $tipoComida)
                                    <option value="{{ $tipoComida }}">{{ mb_convert_case(str_replace('_', ' ', $tipoComida), MB_CASE_TITLE, 'UTF-8') }}</option>
                                @endforeach
                            </select>
                            @error('tipo_comida') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="ingesta-cantidad-title">
                        <h5 id="ingesta-cantidad-title">Ingesta</h5>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Porcentaje consumido <small>%</small>
                                <input type="number" wire:model="ingestaPorcentaje" min="0" max="100" step="0.01" placeholder="0–100">
                                @error('porcentaje_consumido') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            @can('registros_hidratacion.crear')
                                <label class="rm-resident-directory__field">Líquidos consumidos <small>mL</small>
                                    <input type="number" wire:model="ingestaCantidadMl" min="0" max="999999.99" step="0.01" placeholder="Opcional">
                                    @error('cantidad_ml') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                            @endcan
                        </div>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="ingesta-asistencia-title">
                        <h5 id="ingesta-asistencia-title">Asistencia</h5>
                        <p class="rm-resident-directory__form-note">Si necesitó ayuda para comer, descríbela en observaciones.</p>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="ingesta-tolerancia-title">
                        <h5 id="ingesta-tolerancia-title">Tolerancia y dificultades</h5>
                        <label class="rm-resident-directory__field">Tolerancia
                            <select wire:model="ingestaTolerancia">
                                <option value="">Sin registrar</option>
                                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::TOLERANCIAS_INGESTA as $tolerancia)
                                    <option value="{{ $tolerancia }}">{{ mb_convert_case(str_replace('_', ' ', $tolerancia), MB_CASE_TITLE, 'UTF-8') }}</option>
                                @endforeach
                            </select>
                            @error('tolerancia') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="rm-resident-directory__check"><input type="checkbox" wire:model="ingestaDificultadDeglucion"> Dificultad para deglutir</label>
                        @error('dificultad_deglucion') <small role="alert">{{ $message }}</small> @enderror
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="ingesta-observaciones-title">
                        <h5 id="ingesta-observaciones-title">Observaciones</h5>
                        <label class="rm-resident-directory__field">Detalles adicionales
                            <textarea wire:model="ingestaObservacion" rows="4" maxlength="5000" placeholder="Asistencia, dificultades u otros detalles observados"></textarea>
                            @error('observacion') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                @break

                @case('eliminacion')
                    <section class="rm-resident-directory__form-section" aria-labelledby="elim-datos-title">
                        <h5 id="elim-datos-title">Datos del registro</h5>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Fecha y hora
                                <input type="text" value="{{ now()->format('d/m/Y H:i') }}" readonly>
                            </label>
                            <label class="rm-resident-directory__field">Turno
                                <input type="text" value="{{ app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class)->obtenerTurnoActivo(auth()->user())?->nombre ?? 'Sin turno activo' }}" readonly>
                            </label>
                            <label class="rm-resident-directory__field">Registrado por
                                <input type="text" value="{{ trim(auth()->user()->nombres.' '.auth()->user()->ap_paterno.' '.auth()->user()->ap_materno) }}" readonly>
                            </label>
                        </div>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="elim-tipo-title">
                        <h5 id="elim-tipo-title">Tipo de eliminación</h5>
                        <fieldset class="rm-resident-directory__form-grid" aria-required="true">
                            <legend class="sr-only">Selecciona el tipo de eliminación</legend>
                            <label class="rm-resident-directory__check"><input type="radio" wire:model.live="elimTipo" name="tipoEliminacion" value="URINARIA" required> Urinaria</label>
                            <label class="rm-resident-directory__check"><input type="radio" wire:model.live="elimTipo" name="tipoEliminacion" value="INTESTINAL" required> Intestinal</label>
                        </fieldset>
                        @error('tipo_eliminacion') <small role="alert">{{ $message }}</small> @enderror
                    </section>
                    @if($elimTipo === 'URINARIA')
                        <section class="rm-resident-directory__form-section" aria-labelledby="elim-urinaria-title">
                            <h5 id="elim-urinaria-title">Eliminación urinaria</h5>
                            <div class="rm-resident-directory__form-grid">
                                <label class="rm-resident-directory__field">Cantidad observada
                                    <input type="number" wire:model="elimCantidadUrinaria" min="0" step="any" placeholder="Opcional">
                                    @error('cantidad') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <label class="rm-resident-directory__field">Continencia
                                    <select wire:model="elimContinenciaUrinaria">
                                        <option value="">Sin registrar</option>
                                        <option value="CONTINENTE">Continente</option>
                                        <option value="INCONTINENCIA_URINARIA">Incontinencia urinaria</option>
                                    </select>
                                    @error('continencia') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                            </div>
                            <label class="rm-resident-directory__field">Características
                                <input type="text" wire:model="elimCaracteristicaUrinaria" maxlength="120" placeholder="Color, aspecto u olor observados">
                                @error('caracteristica') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </section>
                    @elseif($elimTipo === 'INTESTINAL')
                        <section class="rm-resident-directory__form-section" aria-labelledby="elim-intestinal-title">
                            <h5 id="elim-intestinal-title">Eliminación intestinal</h5>
                            <div class="rm-resident-directory__form-grid">
                                <label class="rm-resident-directory__field">Cantidad observada
                                    <input type="number" wire:model="elimCantidadIntestinal" min="0" step="any" placeholder="Opcional">
                                    @error('cantidad') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                                <label class="rm-resident-directory__field">Continencia
                                    <select wire:model="elimContinenciaIntestinal">
                                        <option value="">Sin registrar</option>
                                        <option value="CONTINENTE">Continente</option>
                                        <option value="INCONTINENCIA_FECAL">Incontinencia fecal</option>
                                    </select>
                                    @error('continencia') <small role="alert">{{ $message }}</small> @enderror
                                </label>
                            </div>
                            <label class="rm-resident-directory__field">Características
                                <input type="text" wire:model="elimCaracteristicaIntestinal" maxlength="120" placeholder="Consistencia y otras características observadas">
                                @error('caracteristica') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </section>
                    @endif
                    <section class="rm-resident-directory__form-section" aria-labelledby="elim-observaciones-title">
                        <h5 id="elim-observaciones-title">Observaciones</h5>
                        <label class="rm-resident-directory__field">Detalles adicionales
                            <textarea wire:model="elimObservacion" rows="4" maxlength="5000" placeholder="Dispositivo, asistencia u otros detalles observados"></textarea>
                            @error('observacion') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                @break

                @case('movilidad')
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-datos-title">
                        <h5 id="mov-datos-title">Datos del registro</h5>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Fecha y hora
                                <input type="text" value="{{ now()->format('d/m/Y H:i') }}" readonly>
                            </label>
                            <label class="rm-resident-directory__field">Turno
                                <input type="text" value="{{ app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class)->obtenerTurnoActivo(auth()->user())?->nombre ?? 'Sin turno activo' }}" readonly>
                            </label>
                            <label class="rm-resident-directory__field">Registrado por
                                <input type="text" value="{{ trim(auth()->user()->nombres.' '.auth()->user()->ap_paterno.' '.auth()->user()->ap_materno) }}" readonly>
                            </label>
                        </div>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-actividad-title">
                        <h5 id="mov-actividad-title">Actividad</h5>
                        <label class="rm-resident-directory__field">Movilidad observada
                            <select wire:model="movMarcha" required>
                                <option value="">Seleccionar</option>
                                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::MOVILIDAD_OBSERVADA as $valor)
                                    <option value="{{ $valor }}">{{ mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>
                                @endforeach
                            </select>
                            @error('marcha') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-traslado-title">
                        <h5 id="mov-traslado-title">Traslado</h5>
                        <label class="rm-resident-directory__field">Modalidad de traslado
                            <select wire:model="movTraslado">
                                <option value="">Sin registrar</option>
                                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::TRASLADOS as $valor)
                                    <option value="{{ $valor }}">{{ mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>
                                @endforeach
                            </select>
                            @error('traslado') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-asistencia-title">
                        <h5 id="mov-asistencia-title">Asistencia</h5>
                        <label class="rm-resident-directory__field">Nivel de ayuda
                            <select wire:model="movTipoApoyo">
                                <option value="">Sin registrar</option>
                                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::NIVELES_AYUDA as $valor)
                                    <option value="{{ $valor }}">{{ mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>
                                @endforeach
                            </select>
                            @error('tipo_apoyo') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <p class="rm-resident-directory__form-note">Si se utilizó un dispositivo, descríbelo en observaciones.</p>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-resultado-title">
                        <h5 id="mov-resultado-title">Resultado</h5>
                        <div class="rm-resident-directory__form-grid">
                            <label class="rm-resident-directory__field">Equilibrio
                                <select wire:model="movEquilibrio"><option value="">Sin valorar</option>
                                    @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::EQUILIBRIOS as $valor)
                                        <option value="{{ $valor }}">{{ mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>
                                    @endforeach
                                </select>
                                @error('equilibrio') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="rm-resident-directory__field">Fatiga observada
                                <select wire:model="movFatiga"><option value="">Sin registrar</option>
                                    @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::FATIGAS as $valor)
                                        <option value="{{ $valor }}">{{ mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>
                                    @endforeach
                                </select>
                                @error('fatiga') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                            <label class="rm-resident-directory__field">Riesgo de caída
                                <select wire:model="movRiesgoCaida"><option value="">Sin valorar</option>
                                    @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::RIESGOS_CAIDA as $valor)
                                        <option value="{{ $valor }}">{{ mb_convert_case(str_replace('_', ' ', $valor), MB_CASE_TITLE, 'UTF-8') }}</option>
                                    @endforeach
                                </select>
                                @error('riesgo_caida') <small role="alert">{{ $message }}</small> @enderror
                            </label>
                        </div>
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-incidencias-title">
                        <h5 id="mov-incidencias-title">Incidencias</h5>
                        <p class="rm-resident-directory__form-note">Si ocurrió un incidente, utiliza el registro de incidentes.</p>
                        @can('incidentes.crear')
                            <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.incidentes') }}">Registrar incidente</a>
                        @endcan
                    </section>
                    <section class="rm-resident-directory__form-section" aria-labelledby="mov-observaciones-title">
                        <h5 id="mov-observaciones-title">Observaciones</h5>
                        <label class="rm-resident-directory__field">Detalles adicionales
                            <textarea wire:model="movObservacion" rows="4" maxlength="5000" placeholder="Dispositivo utilizado y respuesta observada"></textarea>
                            @error('observacion') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </section>
                @break

                @case('seguimiento')
                    <div class="rm-resident-directory__form-grid">
                        <label class="rm-resident-directory__field">Estado general
                            <select wire:model="segEstado" required>
                                <option value="">Seleccionar</option>
                                <option value="ESTABLE">Estable</option>
                                <option value="VIGILANCIA">En vigilancia</option>
                                <option value="DELICADO">Delicado</option>
                                <option value="CRITICO">Crítico</option>
                            </select>
                            @error('segEstado') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="rm-resident-directory__field">Alimentación
                            <select wire:model="segAlimentacion" required>
                                <option value="">Seleccionar</option>
                                <option value="COMPLETA">Completa</option>
                                <option value="PARCIAL">Parcial</option>
                                <option value="RECHAZADA">Rechazada</option>
                                <option value="AYUNO">Ayuno</option>
                            </select>
                            @error('segAlimentacion') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="rm-resident-directory__field">Movilidad
                            <select wire:model="segMovilidad" required>
                                <option value="">Seleccionar</option>
                                <option value="INDEPENDIENTE">Independiente</option>
                                <option value="ASISTIDA">Asistida</option>
                                <option value="SILLA_RUEDAS">Silla de ruedas</option>
                                <option value="ENCAMADO">Encamado</option>
                            </select>
                            @error('segMovilidad') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="rm-resident-directory__field">Sueño
                            <select wire:model="segSueno" required>
                                <option value="">Seleccionar</option>
                                <option value="NORMAL">Normal</option>
                                <option value="INTERRUMPIDO">Interrumpido</option>
                                <option value="INSOMNIO">Insomnio</option>
                                <option value="SOMNOLENCIA">Somnolencia</option>
                            </select>
                            @error('segSueno') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </div>
                    <label class="rm-resident-directory__check"><input type="checkbox" wire:model="segIncidente"> Hubo un incidente durante el turno</label>
                    <label class="rm-resident-directory__check"><input type="checkbox" wire:model="segRequiereMedico"> Requiere valoración médica</label>
                    <label class="rm-resident-directory__field">Observaciones de evolución
                        <textarea wire:model="segObs" rows="4" maxlength="1000" placeholder="Cambios observados y medidas tomadas" required></textarea>
                        @error('segObs') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                @break

                @case('procedimiento')
                    <label class="rm-resident-directory__field">Tipo de procedimiento
                        <select wire:model="procTipo">
                            <option value="CURACION">Curación</option>
                            <option value="SONDA">Manejo de sonda</option>
                            <option value="CATETER">Catéter</option>
                            <option value="OXIGENO">Oxigenoterapia</option>
                            <option value="OTRO">Otro</option>
                        </select>
                        @error('procTipo') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="rm-resident-directory__field">Detalle y evolución
                        <textarea wire:model="procDetalle" rows="4" maxlength="2000" placeholder="Describe el procedimiento, materiales y respuesta" required></textarea>
                        @error('procDetalle') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                @break

                @case('alerta')
                    <div class="rm-resident-directory__form-grid">
                        <label class="rm-resident-directory__field">Tipo de alerta
                            <select wire:model="alertaTipo">
                                <option value="INCIDENTE">Incidente general</option>
                                <option value="CLINICA">Descompensación clínica</option>
                                <option value="CAIDA">Caída</option>
                                <option value="CONDUCTA">Alteración de conducta</option>
                                <option value="MEDICACION">Efecto adverso de medicación</option>
                                <option value="OTRO">Otro</option>
                            </select>
                            @error('alertaTipo') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="rm-resident-directory__field">Prioridad
                            <select wire:model="alertaNivel">
                                <option value="ALTO">Alta</option>
                                <option value="CRITICO">Crítica</option>
                                <option value="MEDIO">Media</option>
                            </select>
                            @error('alertaNivel') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                    </div>
                    <label class="rm-resident-directory__field">Motivo y medidas inmediatas
                        <textarea wire:model="alertaMotivo" rows="4" placeholder="Describe el evento, estado actual y acciones realizadas" required></textarea>
                        @error('alertaMotivo') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                @break
            @endswitch
        </div>
    @endif
</div>
