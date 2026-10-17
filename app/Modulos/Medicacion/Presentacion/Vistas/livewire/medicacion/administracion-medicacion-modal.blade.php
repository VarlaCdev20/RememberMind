<div class="rm-medication" x-data="rmMedicacionRegistro" @medicacion-guardada.window="dirty = false; $nextTick(() => focusSaved())" @medicacion-captura-abierta.window="dirty = false" @medicacion-captura-reiniciada.window="dirty = false" @medicacion-elegir-dosis.window="$nextTick(() => focusAgenda())" @medicacion-dosis-seleccionada.window="$nextTick(() => focusMedication())">
    <x-ui.modal-livewire id="modalAdministracionMedicacion" class="rm-clinical-form-modal rm-medication__modal" wire:model="showModal" maxWidth="2xl" closeMethod="solicitarSalida" alpineClose="if (!busy) $wire.solicitarSalida('cerrar')" alpineBack="if (!busy) $wire.solicitarSalida('volver')" :dismissOnEscape="!$mostrarImagen && !$mostrarPrescripcion" :backText="true" :autoFocus="true" :showValidation="true" subtitle="Selecciona la dosis, revisa la orden y documenta el resultado">
        <x-slot:icon><span class="rm-medication__icon"><i class="ph-bold ph-pill" aria-hidden="true"></i></span></x-slot:icon>
        <x-slot:title>Administración de medicación</x-slot:title>
        @if($showModal)
        <x-slot:context>
            <div class="rm-medication__context">
                <div class="rm-medication__resident">
                    <span class="rm-medication__avatar" aria-hidden="true">{{ mb_substr($contexto['nombre'] ?? 'R', 0, 1) }}</span>
                    <div><strong>{{ $contexto['nombre'] }}</strong><p>{{ $contexto['edad'] ?? 'Edad no disponible' }}{{ isset($contexto['edad']) ? ' años' : '' }} · Habitación {{ $contexto['habitacion'] ?: 'No disponible' }} · Cama {{ $contexto['cama'] ?: 'No disponible' }}</p></div>
                </div>
                <div class="rm-medication__context-professional"><span>Profesional que registra</span><strong>{{ $contexto['profesional'] }}</strong><small>{{ $contexto['categoria'] }}</small></div>
                <div class="rm-medication__context-shift"><span>Jornada actual</span><strong>{{ $contexto['turno'] ?: 'Jornada autorizada' }}</strong><small>{{ $contexto['horario'] }}</small></div>
                <details class="rm-medication__mobile-team"><summary><i class="ph-bold ph-identification-card" aria-hidden="true"></i> Profesional y jornada</summary><dl><div><dt>Profesional</dt><dd>{{ $contexto['profesional'] }} · {{ $contexto['categoria'] }}</dd></div><div><dt>Jornada</dt><dd>{{ $contexto['turno'] ?: 'Jornada autorizada' }} · {{ $contexto['horario'] }}</dd></div></dl></details>
            </div>
            <div class="rm-medication__toolbar">
                <p><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i><span><strong>{{ $fecha === today()->toDateString() ? 'Registro de hoy' : 'Consulta histórica' }}</strong><small>{{ $contexto['momento'] }} · hora del servidor</small></span></p>
                @can('administraciones_medicacion.ver')
                    <x-ui.clinical-consultation-bar label="Consultas de medicación" class="rm-medication__consult">
                        <x-ui.action-button variant="secondary" icono="ph-clock-counter-clockwise" wire:click="abrirHistorial" loading="abrirHistorial" aria-expanded="{{ $mostrarHistorial ? 'true' : 'false' }}" aria-controls="medicacion-historial">Ver historial</x-ui.action-button>
                        <x-ui.action-button variant="secondary" icono="ph-chart-bar" wire:click="abrirEvolucion" loading="abrirEvolucion" aria-expanded="{{ $mostrarEvolucion ? 'true' : 'false' }}" aria-controls="medicacion-evolucion">Ver evolución</x-ui.action-button>
                    </x-ui.clinical-consultation-bar>
                @endcan
            </div>
        </x-slot:context>

        <div class="rm-medication__feedback">
            <p class="rm-medication__error rm-medication__transport" role="alert" x-show="transportError" x-text="transportError" x-cloak></p>

                @if($guardado)
                    <div class="rm-medication__saved" role="status"><i class="ph-bold ph-check-circle" aria-hidden="true"></i><div><strong>{{ $guardado['resultado'] === 'ADMINISTRADA' ? 'Administración registrada' : 'Omisión registrada' }}</strong><p>{{ $guardado['momento'] }}</p><span>Disponible en el historial. Puedes elegir otra dosis.</span></div></div>
                @endif
                @if($avisoConsulta)<p role="status" class="rm-medication__notice">{{ $avisoConsulta }}</p>@endif

        </div>
        <form id="formAdministracion" @submit.prevent="submit()" :aria-busy="busy" class="rm-medication__workspace">
                <section class="rm-medication__section rm-medication__agenda" aria-labelledby="med-dose-title">
                    <header><div><h4 id="med-dose-title" tabindex="-1"><span class="rm-medication__step">1</span> Dosis programada</h4><p>Selecciona el horario exacto que vas a registrar</p></div><i class="ph-bold ph-calendar-check" aria-hidden="true"></i></header>
                    <div class="rm-medication__date">
                        <x-ui.action-button variant="icon" icono="ph-caret-left" aria-label="Día anterior" wire:click="cambiarFecha(-1)" wire:loading.attr="disabled" />
                        <strong>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</strong>
                        <x-ui.action-button variant="icon" icono="ph-caret-right" aria-label="Día siguiente" wire:click="cambiarFecha(1)" :disabled="$fecha === today()->toDateString()" wire:loading.attr="disabled" />
                    </div>
                    @if($fecha !== today()->toDateString())<p class="rm-medication__notice" role="status">Modo consulta. Se muestran resultados guardados; la programación antigua no se reconstruye con la orden actual.</p>@endif
                    @error('fecha')<p class="rm-medication__error" role="alert">{{ $message }}</p>@enderror
                    <div class="rm-medication__tabs" role="group" aria-label="Filtrar dosis">
                        @foreach(['PENDIENTES' => ['Pendientes', 'ph-clock'], 'ADMINISTRADA' => ['Administradas', 'ph-check-circle'], 'OMITIDA' => ['Omitidas', 'ph-x-circle'], 'TODAS' => ['Todas', 'ph-list']] as $clave => [$label, $icon])
                            <button type="button" wire:click="$set('pestana', '{{ $clave }}')" aria-pressed="{{ $pestana === $clave ? 'true' : 'false' }}" wire:loading.attr="disabled" wire:target="guardar"><i class="ph-bold {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span><b>{{ $contadores[$clave] }}</b></button>
                        @endforeach
                    </div>
                    <div class="rm-medication__doses" role="group" aria-label="Dosis programadas">
                        @forelse($dosis as $item)
                            <div wire:key="dose-{{ $fecha }}-{{ $item['codigo'] }}" @class(['rm-medication__dose', 'is-selected' => $cod_horario_prescripcion === $item['codigo']])>
                                @if(!$item['registro'])
                                    <button type="button" aria-pressed="{{ $cod_horario_prescripcion === $item['codigo'] ? 'true' : 'false' }}" wire:click="seleccionarDosis('{{ $item['codigo'] }}')" wire:loading.attr="disabled" wire:target="guardar,seleccionarDosis" class="rm-medication__dose-select"><span class="rm-medication__radio" aria-hidden="true"></span><span class="rm-medication__dose-time">{{ $item['hora'] }}</span><span><strong>{{ $item['medicamento'] }}</strong><small>{{ $item['presentacion'] }} · {{ $item['dosis'] }} {{ $item['unidad'] }} · {{ $item['via'] }}</small></span><span class="rm-medication__badge">{{ ucfirst(mb_strtolower($item['estado'])) }}</span></button>
                                @else
                                    <div class="rm-medication__dose-select"><span class="rm-medication__dose-time">{{ $item['hora'] }}</span><span><strong>{{ $item['medicamento'] }}</strong><small>{{ $item['dosis'] }} {{ $item['unidad'] }} · {{ $item['via'] }}</small></span><span class="rm-medication__badge">{{ ucfirst(mb_strtolower($item['estado'])) }}</span>@can('administraciones_medicacion.ver')<x-ui.action-button variant="ghost" wire:click="verDetalle('{{ $item['registro'] }}')" icono="ph-eye">Ver registro</x-ui.action-button>@endcan</div>
                                @endif
                            </div>
                        @empty<p class="rm-medication__empty">{{ $pestana === 'PENDIENTES' ? 'Sin dosis pendiente de registrar' : 'Sin dosis en este filtro' }}</p>@endforelse
                    </div>
                    @error('cod_horario_prescripcion')<p class="rm-medication__error" role="alert">{{ $message }}</p>@enderror
                    @can('prescripciones.ver')<a class="rm-btn rm-btn-ghost rm-medication__agenda-link" href="{{ route('admin.enfermeria.medicacion', ['adulto' => $cod_residente]) }}"><i class="ph-bold ph-arrow-square-out" aria-hidden="true"></i> Programación y medicación según necesidad</a>@endcan
                </section>

                <section class="rm-medication__section rm-medication__medication" aria-labelledby="med-order-title">
                    <header><div><h4 id="med-order-title" tabindex="-1"><span class="rm-medication__step">2</span> Medicamento</h4><p>Orden médica · solo lectura</p></div>@if($orden)<span class="rm-medication__badge">{{ $orden['estado'] }}</span>@endif</header>
                    @if($orden)
                        <div class="rm-medication__drug"><strong>{{ $orden['medicamento'] }}</strong><p>{{ $orden['presentacion'] }}</p></div>
                        <x-ui.medication-image id="medication-order-photo" :source="$orden['imagen'] ?? null" :name="$orden['medicamento']" />
                        @if($orden['imagen'] ?? null)<x-ui.action-button variant="ghost" class="rm-medication__image-zoom" icono="ph-magnifying-glass-plus" wire:click="verImagen" loading="verImagen">Ampliar imagen</x-ui.action-button>@endif
                        @error('imagen')<p role="alert" class="rm-medication__error">{{ $message }}</p>@enderror
                        <dl class="rm-medication__essentials"><div><dt>Dosis prescrita</dt><dd>{{ $orden['dosis'] }} <small>{{ $orden['unidad'] }}</small></dd></div><div><dt>Vía</dt><dd>{{ $orden['via'] ?: 'No registrada' }}</dd></div><div><dt>Frecuencia</dt><dd>{{ $orden['frecuencia'] ?: 'No registrada' }}</dd></div></dl>
                        @if($orden['indicacion'])<p class="rm-medication__indication"><span>Indicación médica</span>{{ $orden['indicacion'] }}</p>@endif
                        <details class="rm-medication__order-more"><summary>Más información de la orden</summary><dl class="rm-medication__order">@foreach(['inicio' => 'Inicio', 'fin' => 'Suspensión registrada', 'prescriptor' => 'Profesional prescriptor'] as $key => $label)<div><dt>{{ $label }}</dt><dd>{{ $orden[$key] ?: 'No disponible' }}</dd></div>@endforeach</dl></details>
                        @can('prescripciones.ver')<x-ui.action-button variant="secondary" class="rm-medication__prescription" icono="ph-prescription" wire:click="verPrescripcion" loading="verPrescripcion">Ver prescripción completa</x-ui.action-button>@endcan
                    @else
                        <x-ui.medication-image id="medication-empty-photo" />
                        <p class="rm-medication__empty">Selecciona una dosis para mostrar el medicamento y su orden.</p>
                    @endif
                </section>

                <section class="rm-medication__section rm-medication__registration" aria-labelledby="med-result-title" @input="dirty = true" @change="dirty = true">
                    <header><div><h4 id="med-result-title"><span class="rm-medication__step">3</span> Registrar el resultado</h4><p>Completa lo ocurrido con la dosis seleccionada</p></div></header>
                    <x-ui.form-section title="Resultado de la dosis" :columns="1" class="rm-medication__result-fields" :disabled="!$cod_horario_prescripcion || (bool)$guardado || $fecha !== today()->toDateString()" x-bind:disabled="busy || !$wire.cod_horario_prescripcion || Boolean($wire.guardado?.codigo)">
                        <div class="rm-medication__results">
                            <label><input type="radio" name="resultado" value="ADMINISTRADA" wire:model.live="resultado"><span><i class="ph-bold ph-check-circle" aria-hidden="true"></i><strong>Administrada</strong><small>Se administró la dosis.</small></span></label>
                            <label><input type="radio" name="resultado" value="OMITIDA" wire:model.live="resultado"><span><i class="ph-bold ph-minus-circle" aria-hidden="true"></i><strong>Omitida / Rechazada</strong><small>No se administró. Indica el motivo.</small></span></label>
                        </div>
                        @error('resultado')<p class="rm-medication__error" role="alert">{{ $message }}</p>@enderror
                        @if($resultado === 'ADMINISTRADA')
                            <div class="rm-medication__fields">
                                <x-ui.field label="Hora real de administración" for="med-hora-real" required error="hora_real"><div class="rm-medication__time-input"><input id="med-hora-real" type="time" wire:model.live="hora_real" class="rm-input" aria-invalid="{{ $errors->has('hora_real') ? 'true' : 'false' }}" aria-describedby="med-hora-real-help"><x-ui.action-button variant="secondary" wire:click="usarHoraActual" icono="ph-clock" @click="dirty = true" title="Usar la hora actual del servidor">Ahora</x-ui.action-button></div><small id="med-hora-real-help">Hora real de hoy. No se acepta una hora futura.</small></x-ui.field>
                                <x-ui.field label="Dosis efectivamente administrada" for="med-dosis-real" required error="dosis_administrada"><div class="rm-medication__dose-input"><input id="med-dosis-real" type="number" step="0.001" min="0.001" max="9999999.999" wire:model.live.debounce.300ms="dosis_administrada" class="rm-input" aria-invalid="{{ $errors->has('dosis_administrada') ? 'true' : 'false' }}" aria-describedby="med-dosis-help"><span>{{ $orden['unidad'] ?? '' }}</span></div><small id="med-dosis-help">Programada: {{ $orden['dosis_programada'] ?? '—' }} {{ $orden['unidad'] ?? '' }}. Confirma la cantidad real.</small></x-ui.field>
                                <div class="rm-medication__readonly"><span>Vía prescrita</span><strong>{{ $orden['via'] ?? '—' }}</strong><small>Solo lectura</small></div>
                                <x-ui.field label="Efecto observado" for="med-efecto" error="efecto_observado"><textarea id="med-efecto" aria-invalid="{{ $errors->has('efecto_observado') ? 'true' : 'false' }}" wire:model.live.debounce.400ms="efecto_observado" rows="2" maxlength="2000" class="rm-textarea" aria-describedby="med-efecto-help"></textarea><small id="med-efecto-help">Respuesta observada, si corresponde.</small></x-ui.field>
                                <x-ui.field label="Reacción adversa" for="med-reaccion" error="reaccion_adversa"><textarea id="med-reaccion" aria-invalid="{{ $errors->has('reaccion_adversa') ? 'true' : 'false' }}" wire:model.live.debounce.400ms="reaccion_adversa" rows="2" maxlength="2000" class="rm-textarea" aria-describedby="med-reaccion-help"></textarea><small id="med-reaccion-help">Describe lo observado y comunica según el protocolo institucional.</small></x-ui.field>
                            </div>
                        @elseif($resultado === 'OMITIDA')
                            <x-ui.field label="Motivo de omisión o rechazo" for="med-motivo" required error="motivo_omision"><textarea id="med-motivo" wire:model.live.debounce.400ms="motivo_omision" rows="3" minlength="5" maxlength="500" class="rm-textarea" aria-invalid="{{ $errors->has('motivo_omision') ? 'true' : 'false' }}" aria-describedby="med-motivo-help"></textarea><small id="med-motivo-help">Describe el hecho observado. Entre 5 y 500 caracteres.</small></x-ui.field>
                        @else<p class="rm-medication__empty">{{ $guardado ? 'Este resultado ya está guardado. Elige otra dosis para continuar.' : 'Selecciona una dosis y su resultado para completar el registro.' }}</p>@endif
                        @if($resultado !== '')<x-ui.field label="Observaciones adicionales" for="med-observacion" error="observacion"><textarea id="med-observacion" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" wire:model.live.debounce.400ms="observacion" rows="2" maxlength="2000" class="rm-textarea"></textarea></x-ui.field>@endif
                    </x-ui.form-section>
                </section>



                <section class="rm-medication__section rm-medication__safety" aria-labelledby="med-safety-title">
                    <header><h4 id="med-safety-title"><span class="rm-medication__step">4</span> Seguridad</h4><i class="ph-bold ph-shield-check" aria-hidden="true"></i></header>
                    <details @if($contexto['alergias']) open @endif><summary><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> Alergias conocidas @if($contexto['alergias'])<span class="rm-medication__badge">Alergia registrada</span>@endif</summary>@forelse($contexto['alergias'] as $alergia)<p><strong>{{ $alergia['sustancia'] }}</strong> · {{ $alergia['estado'] }}<br>{{ $alergia['detalle'] }}<br>Reacción: {{ $alergia['reaccion'] ?: 'No documentada' }} · Gravedad documentada: {{ $alergia['gravedad'] ?: 'No disponible' }}</p>@empty<p>{{ $contexto['puede_ver_alergias'] ? 'No se dispone de alergias documentadas.' : 'Consulta de alergias no autorizada.' }}</p>@endforelse</details>
                    <details><summary><i class="ph-bold ph-arrows-left-right" aria-hidden="true"></i> Interacciones</summary><p>Verificación automatizada de interacciones no disponible.</p></details>
                    <details><summary><i class="ph-bold ph-prescription" aria-hidden="true"></i> Orden y medicamento</summary><p>{{ $orden ? $orden['medicamento'].' · '.$orden['dosis_programada'].' '.$orden['unidad'].' · '.$orden['via'].' · '.$hora_programada : 'Selecciona la dosis para consultar la orden.' }}</p><p>Comprueba el producto y su envase; la imagen de apoyo no verifica su identidad.</p></details>
                    <details><summary><i class="ph-bold ph-clock" aria-hidden="true"></i> Momento de administración</summary><p>Programada: {{ $hora_programada ?: 'Sin seleccionar' }} · Real: {{ $resultado === 'ADMINISTRADA' && $hora_real ? $hora_real : 'Sin confirmar' }}</p>@if($hora_real && $hora_programada && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora_real))<p>Diferencia descriptiva: {{ (int) \Carbon\Carbon::parse($fecha.' '.$hora_programada)->diffInMinutes(\Carbon\Carbon::parse($fecha.' '.$hora_real), false) }} minutos. No autoriza cambiar el horario.</p>@endif</details>
                    <p class="rm-medication__note">Revisa residente, medicamento, dosis, vía y hora.</p>
                </section>

                <section class="rm-medication__section rm-medication__confirmation" aria-labelledby="med-confirm-title">
                    <header><h4 id="med-confirm-title"><span class="rm-medication__step">5</span> {{ $guardado ? 'Registro confirmado' : 'Revisar registro' }}</h4><span class="rm-medication__badge">{{ $guardado ? 'Guardado' : 'Sin guardar' }}</span></header>
                    <dl class="rm-medication__order"><div><dt>Medicamento</dt><dd>{{ $orden['medicamento'] ?? 'Sin seleccionar' }}</dd></div><div><dt>Hora programada</dt><dd>{{ $hora_programada ?: 'Sin seleccionar' }}</dd></div><div><dt>Resultado</dt><dd>{{ $guardado['resultado'] ?? ($resultado ?: 'Sin confirmar') }}</dd></div>
                        @if(($guardado['resultado'] ?? $resultado) === 'ADMINISTRADA')<div><dt>Dosis real</dt><dd>{{ $guardado['dosis'] ?? ($dosis_administrada ?: 'Sin confirmar') }} {{ $orden['unidad'] ?? '' }}</dd></div><div><dt>Hora real</dt><dd>{{ $guardado['hora_real'] ?? ($hora_real ?: 'Sin confirmar') }}</dd></div>
                        @elseif(($guardado['resultado'] ?? $resultado) === 'OMITIDA')<div><dt>Motivo</dt><dd>{{ $guardado['motivo'] ?? ($motivo_omision ?: 'Sin completar') }}</dd></div>@endif
                    </dl>
                    <p class="rm-medication__note">{{ $guardado ? 'El resultado está disponible en el historial.' : 'Confirma después de revisar la información.' }}</p>
                </section>

        </form>
        @include('livewire.medicacion.partials.administracion-consultas')
        <x-slot:footer>
            @if($confirmacion)
                <div class="rm-medication__discard" role="alert"><div><strong>Hay una captura pendiente</strong><p>{{ $confirmacion === 'limpiar' ? '¿Limpiar los campos editables? Se conserva la dosis seleccionada.' : ($confirmacion === 'dosis' ? '¿Descartar esta captura y seleccionar otra dosis?' : '¿Salir sin guardar esta captura?') }}</p></div><x-ui.action-button variant="secondary" wire:click="cancelarConfirmacion" icono="ph-pencil-simple">Seguir editando</x-ui.action-button><x-ui.action-button variant="primary" wire:click="confirmarDescarte" icono="ph-eraser">{{ $confirmacion === 'limpiar' ? 'Confirmar limpieza' : 'Descartar captura' }}</x-ui.action-button></div>
            @else
                <div class="rm-medication__footer-status" role="status"><i class="ph-bold {{ $guardado ? 'ph-check-circle' : 'ph-info' }}" aria-hidden="true"></i><span>{{ $guardado ? 'Registro guardado' : (!$cod_horario_prescripcion ? 'Selecciona una dosis para comenzar' : ($resultado === '' ? 'Selecciona el resultado de la dosis' : 'Revisa los datos antes de confirmar')) }}</span></div>
                <div class="rm-medication__footer-actions">
                    <x-ui.action-button variant="ghost" icono="ph-x" wire:click="solicitarSalida('cerrar')" x-bind:disabled="busy">{{ $guardado ? 'Cerrar' : 'Cancelar' }}</x-ui.action-button>
                    @if($guardado)<x-ui.action-button variant="primary" icono="ph-arrow-right" wire:click="prepararSiguienteDosis" loading="prepararSiguienteDosis">Elegir otra dosis</x-ui.action-button>
                    @else
                        <x-ui.action-button variant="secondary" wire:click="solicitarSalida('limpiar')" icono="ph-eraser" x-bind:disabled="busy || !dirty" :disabled="!$cod_horario_prescripcion">Limpiar</x-ui.action-button>
                        <x-ui.action-button tipo="submit" form="formAdministracion" variant="primary" loading="guardar" icono="ph-check" x-bind:disabled="busy || !$wire.cod_horario_prescripcion || !$wire.resultado" :disabled="!$cod_horario_prescripcion || $resultado === '' || $fecha !== today()->toDateString()"><span wire:loading.remove wire:target="guardar">{{ $resultado === 'OMITIDA' ? 'Registrar omisión' : 'Confirmar administración' }}</span><span wire:loading wire:target="guardar">Registrando…</span></x-ui.action-button>
                    @endif
                </div>
            @endif
        </x-slot:footer>
        @endif
    </x-ui.modal-livewire>
</div>
