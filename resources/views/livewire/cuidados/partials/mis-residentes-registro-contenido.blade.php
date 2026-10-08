@php
    $opcionesRegistro = [
        ['tipo' => 'signos', 'titulo' => 'Signos vitales', 'descripcion' => 'Registra controles de salud.', 'icono' => 'ph-heartbeat', 'permiso' => 'signos_vitales.crear'],
        ['tipo' => 'medicacion', 'titulo' => 'Medicación programada', 'descripcion' => 'Administra dosis indicadas.', 'icono' => 'ph-pill', 'permiso' => 'administraciones_medicacion.crear'],
        ['tipo' => 'dolor', 'titulo' => 'Valoración de dolor', 'descripcion' => 'Registra la intensidad del dolor.', 'icono' => 'ph-thermometer', 'permiso' => 'valoraciones_dolor.crear'],
        ['tipo' => 'alimentacion', 'titulo' => 'Cuidado de alimentación', 'descripcion' => 'Registra ingesta y tolerancia.', 'icono' => 'ph-bowl-food', 'permiso' => 'registros_ingesta.crear'],
        ['tipo' => 'eliminacion', 'titulo' => 'Cuidado de eliminación', 'descripcion' => 'Registra patrones de eliminación.', 'icono' => 'ph-drop', 'permiso' => 'registros_eliminacion.crear'],
        ['tipo' => 'movilidad', 'titulo' => 'Cuidado de movilidad', 'descripcion' => 'Registra apoyo y traslado.', 'icono' => 'ph-person-simple-walk', 'permiso' => 'registros_movilidad.crear'],
        ['tipo' => 'seguimiento', 'titulo' => 'Seguimiento diario', 'descripcion' => 'Registra evolución del turno.', 'icono' => 'ph-clipboard-text', 'permiso' => 'atenciones.crear'],
        ['tipo' => 'procedimiento', 'titulo' => 'Procedimiento de enfermería', 'descripcion' => 'Registra procedimiento realizado.', 'icono' => 'ph-first-aid', 'permiso' => 'atenciones.crear'],
    ];
    $tituloFormulario = collect($opcionesRegistro)->firstWhere('tipo', $registroTipo)['titulo'] ?? 'Registro de enfermería';
@endphp

<div class="rm-resident-directory__register">
    @if($drawerPaso === 'register-selector')
        @include('livewire.cuidados.partials.quick-register-selector')
    @elseif($drawerPaso === 'register-form')
        @if( ! in_array($registroTipo, ['signos', 'dolor', 'alimentacion', 'eliminacion', 'movilidad'], true))
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
                    @include('livewire.cuidados.partials.mis-residentes-dolor')
                @break

                @case('alimentacion')
                    @include('livewire.cuidados.partials.mis-residentes-ingesta')
                @break

                @case('eliminacion')
                    @include('livewire.cuidados.partials.mis-residentes-eliminacion')
                @break

                @case('movilidad')
                    @include('livewire.cuidados.partials.mis-residentes-movilidad')
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
                    <p class="rm-resident-directory__form-note">Este registro documenta un procedimiento general. No se incorpora al historial de una herida.</p>
                    @can('heridas.ver')
                        <a class="rm-clinical-form__history-link" target="_blank" rel="noopener" href="{{ route('admin.enfermeria.registros', ['adulto' => $detalleResidente['cod_residente'], 'cuidado' => 'heridas', 'seccion' => 'HERIDAS']) }}">Registrar curación vinculada a una herida <span class="sr-only">(abre otra pestaña)</span></a>
                    @endcan
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
