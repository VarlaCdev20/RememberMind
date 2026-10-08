<div class="rm-resident-directory" x-data="rmClinicalFormFeedback()" @cuidado-registrado.window="confirmCareFeedback()" @dolor-registrado.window="confirmFeedback('dolor')" @resident-directory-opened.window="$nextTick(() => $el.querySelector('.rm-drawer-header h2')?.focus())" @resident-directory-step-changed.window="feedbackAttempt = null; $nextTick(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus())" @resident-directory-selector-opened.window="feedbackAttempt = null; $nextTick(() => requestAnimationFrame(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus()))" @resident-directory-selector-closed.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('#resident-register-trigger')?.focus()))">
    @php
        $cuidadoActual = $this->cuidadoSeleccionado();
    @endphp
    <x-ui.residents-page-header :title="$cuidadoActual ? 'Cuidados · '.$cuidadoActual['label'] : ($esSuperAdmin ? 'Supervisión de residentes' : 'Mis residentes')" :subtitle="$cuidadoActual ? 'Selecciona un residente para consultar o registrar este cuidado.' : 'Personas asignadas a tu cuidado en esta jornada.'" :turno="$turnoActual" :modo-consulta="$esModoConsulta">
        <x-slot:actions>
            @can('ejecuciones_cuidado.ver')
                <a href="{{ route('admin.enfermeria.tareas') }}" class="rm-btn rm-btn-secondary">Cuidados programados</a>
            @endcan
        </x-slot:actions>
    </x-ui.residents-page-header>

    <x-ui.resident-filter-toolbar :search="$search" :filtro-rapido="$filtroRapido" :filtro-alertas="$filtroAlertas" :filtro-medicacion="$filtroMedicacion" :filtro-cuidados="$filtroCuidados" :filtro-habitacion="$filtroHabitacion" :orden="$orden" :vista-modo="$vistaModo" :stats="$stats" :habitaciones="$habitaciones" :habitaciones-conteo="$habitacionesConteo ?? null" :filtros-activos="$filtrosActivos" />

    <section class="rm-resident-directory__results animate-fade-in-up" aria-labelledby="residents-results-title" wire:loading.class="is-loading" wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
        <div class="rm-resident-directory__results-header">
            <div>
                <h2 id="residents-results-title">{{ $stats['total'] }} {{ $stats['total'] === 1 ? 'residente' : 'residentes' }} {{ ($esSuperAdmin || $esModoConsulta) ? ($stats['total'] === 1 ? 'disponible' : 'disponibles') : ($stats['total'] === 1 ? 'asignado' : 'asignados') }}</h2>
            </div>
            <span class="rm-resident-directory__shown" aria-live="polite">@if($search !== '' || $filtroRapido !== 'TODOS' || $filtroHabitacion !== '' || $filtroEstado !== 'TODOS') Mostrando {{ $pacientes->total() }} de {{ $stats['total'] }} @endif</span>
        </div>
        <div wire:loading wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros" class="rm-resident-directory__loading" role="status" aria-label="Actualizando residentes">
            @if($vistaModo === 'tarjetas')
                <div class="rm-resident-directory__cards rm-resident-directory__skeletons" aria-hidden="true">
                    @for($i = 0; $i < 3; $i++)
                        <div class="rm-resident-directory__skeleton-card"><span class="rm-resident-directory__skeleton-circle"></span><span class="rm-resident-directory__skeleton-line"></span><span class="rm-resident-directory__skeleton-line short"></span><span class="rm-resident-directory__skeleton-block"></span></div>
                    @endfor
                </div>
            @else
                <div class="rm-resident-directory__skeleton-rows" aria-hidden="true">
                    @for($i = 0; $i < 3; $i++)<span></span>@endfor
                </div>
            @endif
            <span class="sr-only">Actualizando residentes…</span>
        </div>

        @if($errorCarga)
            <div class="rm-resident-directory__empty" role="alert" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
                <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span>
                <h3>No pudimos cargar los residentes</h3>
                <p>Intenta nuevamente en unos momentos.</p>
                <button type="button" wire:click="$refresh" class="rm-btn-secondary">Reintentar</button>
            </div>
        @elseif($pacientes->isEmpty())
            <div class="rm-resident-directory__empty" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
                <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span>
                @if($stats['total'] === 0)
                    <h3>{{ $esSuperAdmin ? 'No hay residentes disponibles' : 'No tienes residentes asignados en esta jornada.' }}</h3>
                    <p>{{ $esSuperAdmin ? 'No se encontraron residentes en el alcance de esta consulta.' : 'No hay residentes asociados a tu turno actual.' }}</p>
                    @unless($esSuperAdmin)<a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary">Consultar información de mi turno <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endunless
                @else
                    <h3>No encontramos residentes con estos filtros.</h3>
                    <p>Prueba con otro nombre, habitación o estado.</p>
                    <button type="button" wire:click="limpiarFiltros" class="rm-btn-secondary">Limpiar filtros</button>
                @endif
            </div>
        @elseif($vistaModo === 'tarjetas')
            <div class="rm-resident-directory__cards" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
                @foreach($pacientes as $paciente)
                    <x-ui.resident-card :resident="$paciente" mode="card" variant="nursing" :selected="$this->residente === $paciente->cod_residente" select-method="seleccionarResidente" context-label="Próximo cuidado" :context-value="$paciente->proxima_atencion_texto" :context-time="$paciente->proxima_atencion_hora" :alert-count="$paciente->alertas_criticas_count" :key="'card-'.$paciente->cod_residente">
                        <x-slot:menu>@include('livewire.cuidados.partials.nursing-resident-menu', ['paciente' => $paciente, 'modoConsulta' => $esModoConsulta || $esSuperAdmin || app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->isActive(auth()->user())])</x-slot:menu>
                    </x-ui.resident-card>
                @endforeach
            </div>
        @else
            <div class="rm-resident-directory__list" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroAlertas,filtroMedicacion,filtroCuidados,filtroHabitacion,orden,vistaModo,aplicarFiltro,removerFiltro,limpiarFiltrosActivos,limpiarFiltros">
                <div class="rm-resident-directory__list-heading" aria-hidden="true"><span>Residente</span><span>Ubicación</span><span>Estado</span><span>Próximo cuidado</span><span></span></div>
                @foreach($pacientes as $paciente)
                    <x-ui.resident-card :resident="$paciente" mode="row" variant="nursing" :selected="$this->residente === $paciente->cod_residente" select-method="seleccionarResidente" context-label="Próximo cuidado" :context-value="$paciente->proxima_atencion_texto" :context-time="$paciente->proxima_atencion_hora" :status-label="$paciente->estado_label" :status-tone="$paciente->estado_color" :key="'row-'.$paciente->cod_residente" />
                @endforeach
            </div>
        @endif
    </section>

    @if($pacientes->hasPages())
        <nav class="rm-resident-directory__pagination" aria-label="Páginas de residentes">{{ $pacientes->links() }}</nav>
    @endif

    @php
        $esSelectorRegistro = $drawerPaso === 'register-selector';
        $esFormularioClinico = in_array($registroTipo, ['dolor', 'alimentacion', 'eliminacion', 'movilidad'], true) && ! $esSelectorRegistro;
        $tituloClinico = ['dolor' => 'Valoración de dolor', 'alimentacion' => 'Registro de ingesta', 'eliminacion' => 'Registro de eliminación', 'movilidad' => 'Registro de movilidad'][$registroTipo] ?? 'Nuevo registro';
        $esResultadoSignos = $drawerPaso === 'register-result' && $registroTipo === 'signos' && $signosResultadoRegistro !== [];
        $hayCriticoSignos = collect($signosEvaluacion['resultados'] ?? [])->contains(fn (array $resultado) => ($resultado['severidad'] ?? null) === 'CRITICO' || ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR');
        $generaraAlertaSignos = collect($signosEvaluacion['resultados'] ?? [])->contains(fn (array $resultado) => ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR');
        $volverRegistro = $confirmarDescarte ? null : ($signosConfirmacionPendiente ? 'cancelarConfirmacionSignos' : ($drawerPaso === 'register-form' ? 'volverPanelDetalle' : null));
        $guardarMetodo = match ($registroTipo) {
            'signos' => 'guardarSignos', 'medicacion' => 'guardarMed', 'dolor' => 'guardarDolor',
            'alimentacion', 'eliminacion', 'movilidad' => 'guardarCuidado',
            'seguimiento' => 'guardarSeguimiento', 'procedimiento' => 'guardarProcedimiento',
            'alerta' => 'guardarAlerta', default => null,
        };
    @endphp
    <x-ui.resident-summary-drawer model="mostrarPanelDetalle" title="Resumen del residente" subtitle="Resumen de enfermería" close-method="cerrarPanelDetalle">
        @if($detalleResidente)
            @if($drawerPaso === 'resident-summary')
            @include('livewire.cuidados.partials.resident-summary')
            @endif
        @endif
        <x-slot:footer>
            @if($detalleResidente)
                @if($drawerPaso === 'resident-summary')
                    <div class="rm-resident-directory__footer-actions">
                        @if($cuidadoActual && isset($cuidadoActual['form']) && $this->puedeRegistrar() && auth()->user()->can($cuidadoActual['create']))
                            <button id="resident-register-trigger" type="button" class="rm-btn-primary" wire:click="abrirCuidadoSeleccionado">Registrar {{ mb_strtolower($cuidadoActual['label']) }}</button>
                        @elseif($cuidadoActual && ($destinoCuidado = $this->destinoCuidadoSeleccionado()))
                            <a class="rm-btn-primary" href="{{ $destinoCuidado }}">Abrir {{ mb_strtolower($cuidadoActual['label']) }}</a>
                        @elseif(! $cuidadoActual && $this->puedeRegistrar())
                            <button id="resident-register-trigger" type="button" class="rm-btn-primary" wire:click="mostrarSelectorRegistro">+ Registrar</button>
                        @endif
                        @can('enfermeria.ver_ficha_paciente')
                            <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente']]) }}">Ver ficha clínica <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                        @endcan
                    </div>
                @endif
            @endif
        </x-slot:footer>
    </x-ui.resident-summary-drawer>

    <x-ui.quick-register-modal id="resident-register" model="mostrarSelectorModal" class="rm-resident-directory__register-modal {{ $esFormularioClinico ? 'rm-clinical-form-modal' : '' }} {{ $esResultadoSignos ? 'rm-resident-directory__register-modal--signos-result' : '' }} {{ $esSelectorRegistro ? 'rm-resident-directory__register-modal--selector' : ($registroTipo === 'signos' ? 'rm-resident-directory__register-modal--signos' : 'rm-resident-directory__register-modal--form') }} {{ $signosConfirmacionPendiente ? 'rm-resident-directory__register-modal--signos-confirm' : '' }}" :title="$esSelectorRegistro && $detalleResidente ? 'Registrar para '.\Illuminate\Support\Str::title(mb_strtolower($detalleResidente['nombre_completo'])) : ($esResultadoSignos ? 'Resultado del registro' : ($signosConfirmacionPendiente ? ($hayCriticoSignos ? 'Revisión de medición crítica' : 'Revisión de presión arterial') : ($registroTipo === 'signos' ? 'Signos vitales' : ($esFormularioClinico ? $tituloClinico : 'Nuevo registro'))))" :subtitle="$esSelectorRegistro ? 'Selecciona qué deseas registrar' : ($esResultadoSignos ? 'Seguimiento clínico actualizado' : ($signosConfirmacionPendiente ? 'Comprueba las lecturas señaladas' : ($registroTipo === 'signos' ? 'Registro clínico del residente' : 'Registro del residente seleccionado')))" close-method="cerrarSelectorRegistro" :back-method="$esResultadoSignos ? null : $volverRegistro" :back-label="$signosConfirmacionPendiente ? 'Volver y revisar' : 'Volver al selector de registros'">
        <x-slot:icon>
            @if($esSelectorRegistro)
                <span class="rm-quick-register__header-icon" aria-hidden="true"><i class="ph-bold ph-plus-circle"></i></span>
            @elseif($esFormularioClinico)
                <i class="ph-bold {{ ['dolor' => 'ph-thermometer', 'alimentacion' => 'ph-bowl-food', 'eliminacion' => 'ph-drop', 'movilidad' => 'ph-person-simple-walk'][$registroTipo] }}" aria-hidden="true"></i>
            @elseif($registroTipo === 'signos')
                <span class="rm-signos__header-icon-box" aria-hidden="true">
                    <i class="ph-bold ph-heartbeat rm-signos__header-icon"></i>
                </span>
            @endif
        </x-slot:icon>
        <x-slot:context>
            @if($detalleResidente && !$esSelectorRegistro)
                <div class="rm-resident-directory__register-context {{ $registroTipo === 'signos' ? 'rm-signos__resident-context' : '' }}">
                    @if($detalleResidente['foto'])
                        <img src="{{ $detalleResidente['foto'] }}" alt="Foto de {{ $detalleResidente['nombre_completo'] }}" class="rm-resident-directory__register-avatar">
                    @else
                        <span class="rm-resident-directory__register-avatar rm-resident-directory__register-avatar--initials" aria-hidden="true">{{ $detalleResidente['iniciales'] }}</span>
                    @endif
                    <div class="rm-resident-directory__register-identity">
                        @if($registroTipo === 'signos')<span class="rm-signos__resident-label">Residente · Control de signos vitales</span>@endif
                        <strong @if($esFormularioClinico) data-clinical-resident @endif>{{ $detalleResidente['nombre_completo'] }}</strong>
                        <span>{{ $detalleResidente['edad_texto'] ?: 'Edad no registrada' }} · {{ $detalleResidente['habitacion_texto'] }} · {{ $detalleResidente['cama_texto'] }}</span>
                    </div>
                    @if($esFormularioClinico)
                        <div class="rm-clinical-form__personnel">
                            <span>Profesional que registra</span><strong data-clinical-professional>{{ auth()->user()->name }}</strong>
                            <span>Jornada · {{ $detalleResidente['turno_nombre'] ?: 'No disponible' }}</span>
                        </div>
                    @endif
                    @if($registroTipo === 'signos')
                        <div class="rm-signos__resident-meta">
                            <div class="rm-signos__context-personnel"><span><i class="ph-bold ph-user-circle" aria-hidden="true"></i> Profesional que registra</span><strong>{{ auth()->user()->name }}</strong></div>
                            <div class="rm-signos__context-shift"><span><i class="ph-bold ph-clock" aria-hidden="true"></i> Jornada actual</span><strong>{{ $signosContextoTurno['nombre'] ?? 'Sin turno activo' }}</strong>@if(filled($signosContextoTurno['horario'] ?? null))<small>{{ $signosContextoTurno['horario'] }}</small>@endif</div>
                        </div>
                        @if(($detalleResidente['alertas_count'] ?? 0) > 0)
                            <span class="rm-badge-pill rm-badge-pill--warning" title="Alertas actualmente registradas para este residente"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> Alertas activas · {{ $detalleResidente['alertas_count'] }}</span>
                        @endif
                    @else
                        @can('alertas.ver')
                            @if($detalleResidente['alertas_count'] > 0)
                                <div class="rm-resident-directory__register-alerts" aria-label="Alertas activas del residente">
                                    @php($limiteAlertasRegistro = $detalleResidente['alertas_count'] > 2 ? 1 : 2)
                                    @foreach(array_slice($detalleResidente['alertas'], 0, $limiteAlertasRegistro) as $alerta)
                                        <span class="rm-resident-directory__register-alert" title="{{ $alerta['motivo'] }}"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ \Illuminate\Support\Str::limit($alerta['motivo'], 24) }}</span>
                                    @endforeach
                                    @if($detalleResidente['alertas_count'] > $limiteAlertasRegistro)
                                        <span class="rm-resident-directory__register-alert" title="{{ $detalleResidente['alertas_count'] - $limiteAlertasRegistro }} alertas adicionales">+{{ $detalleResidente['alertas_count'] - $limiteAlertasRegistro }} alertas</span>
                                    @endif
                                </div>
                            @endif
                        @endcan
                    @endif
                </div>
            @endif
        </x-slot:context>
        @if($detalleResidente)
            @if($confirmarDescarte)
                <section class="rm-resident-directory__discard" role="alert" aria-labelledby="resident-discard-title">
                    <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
                    @if($this->lecturaCriticaSinGuardar())
                        <h4 id="resident-discard-title">Hay una medición crítica sin registrar</h4>
                        <p>Corrige la transcripción si es incorrecta. Si la lectura es real, regístrala y atiende la alerta antes de iniciar otro control.</p>
                    @else
                        <h4 id="resident-discard-title">¿Salir sin guardar?</h4>
                        <p>Tienes cambios sin registrar. Si sales ahora se perderán las mediciones introducidas.</p>
                    @endif
                </section>
            @elseif($esResultadoSignos)
                <x-ui.resultado-operacion-clinica
                    :variant="($signosResultadoRegistro['hay_critico'] ?? false) ? 'critical' : (count($signosResultadoRegistro['advertencias'] ?? []) ? 'warning' : 'success')"
                    :title="($signosResultadoRegistro['hay_critico'] ?? false) ? 'Registro guardado · Atención requerida' : 'Registro guardado correctamente'"
                    message="Los datos ingresados se validaron y quedaron guardados en el seguimiento clínico del residente."
                    :resident="$detalleResidente['nombre_completo']"
                    :date-time="$signosResultadoRegistro['fecha_hora'] ?? null"
                    :professional="$signosResultadoRegistro['profesional'] ?? null"
                    :measurements="$signosResultadoRegistro['mediciones'] ?? []"
                    :alert-code="$signosResultadoRegistro['cod_alerta'] ?? null"
                    :warnings="$signosResultadoRegistro['advertencias'] ?? []"
                />
            @elseif($signosConfirmacionPendiente && $registroTipo === 'signos')
                <section class="rm-signos__confirm" data-tone="{{ $hayCriticoSignos ? 'danger' : 'warning' }}" role="group" aria-labelledby="signos-confirm-title" aria-describedby="signos-confirm-description">
                    <h4 id="signos-confirm-title"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ $hayCriticoSignos ? ($signosPasoConfirmacion === 'final' ? 'Confirmar valor crítico' : 'Revisión de medición crítica') : 'Revisar lectura atípica' }}</h4>
                    <p id="signos-confirm-description">{{ $signosPasoConfirmacion === 'final' ? ($hayCriticoSignos ? 'La lectura fue revisada. Al continuar se guardará un nuevo registro, se volverá a evaluar y, si corresponde, se generará una alerta abierta vinculada a la medición.' : 'La lectura fue revisada. Al continuar se guardará el registro sin clasificar esta relación entre presiones como crítica por sí sola.') : 'Comprueba la transcripción, la técnica y el contexto clínico. Sigue el protocolo institucional vigente antes de continuar.' }}</p>
                    <ul>
                        @foreach(($signosEvaluacion['resultados'] ?? []) as $resultado)
                            @if(($resultado['severidad'] ?? null) === 'CRITICO' || ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR')
                                <li><strong>{{ str_replace('_', ' ', ucfirst($resultado['variable'])) }}</strong><span>{{ $resultado['valor'] }} {{ $resultado['unidad'] }}</span><em>{{ $resultado['severidad'] ?? 'Requiere confirmación' }}</em>
                                    @if(filled($resultado['rango_o_umbral'] ?? null))<small>{{ ($resultado['fuente_evaluacion'] ?? null) === 'UMBRAL_CRITICO' ? 'Umbral utilizado' : 'Referencia utilizada' }}: {{ $resultado['rango_o_umbral'] }}</small>@endif
                                    @if(filled($resultado['explicacion'] ?? null))<small>{{ $resultado['explicacion'] }}</small>@endif
                                </li>
                            @endif
                        @endforeach
                        @if(is_numeric($signoSis) && is_numeric($signoDia) && (float)$signoSis <= (float)$signoDia)
                            <li><strong>Presión arterial atípica</strong><span>{{ $signoSis }} / {{ $signoDia }} mmHg</span><em>Revisar lectura</em></li>
                        @endif
                    </ul>
                    @if($signosPasoConfirmacion === 'revision')
                        <p>Los datos aún no se han guardado. Antes de continuar:</p>
                        <ul class="rm-signos__review-checks" aria-label="Pasos de revisión"><li>Verifica la transcripción.</li><li>Verifica la técnica utilizada.</li><li>Revisa el contexto clínico.</li><li>Sigue el protocolo institucional.</li></ul>
                    @endif
                </section>
            @elseif(in_array($drawerPaso, ['register-selector', 'register-form'], true))
                @include('livewire.cuidados.partials.mis-residentes-registro-contenido')
            @endif
        @endif
        @if(in_array($drawerPaso, ['register-form', 'register-result'], true) || $confirmarDescarte)
        <x-slot:footer>
            @if($esResultadoSignos)
                <button type="button" class="rm-btn-secondary" wire:click="volverResidenteDesdeSignos">Volver al residente</button>
                @if(auth()->user()?->can('enfermeria.ver_ficha_paciente'))
                    <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente'], 'tab' => 'signos']) }}">Ver registro</a>
                @endif
                @if(filled($signosResultadoRegistro['cod_alerta'] ?? null) && auth()->user()?->can('alertas.ver'))
                    <a class="rm-btn-danger" href="{{ route('admin.enfermeria.alertas', ['adulto' => $detalleResidente['cod_residente'], 'alerta' => $signosResultadoRegistro['cod_alerta']]) }}">Atender alerta <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                @endif
            @elseif($confirmarDescarte)
                <button type="button" class="rm-btn-secondary" wire:click="cancelarDescarte" wire:loading.attr="disabled">{{ $this->lecturaCriticaSinGuardar() ? 'Continuar revisando' : 'Seguir editando' }}</button>
                @unless($this->lecturaCriticaSinGuardar())
                    <button type="button" class="rm-btn-danger" wire:click="descartarCambios" wire:loading.attr="disabled">Salir sin guardar</button>
                @endunless
                @error('continuidad_signos')<p class="rm-signos__error" role="alert">{{ $message }}</p>@enderror
            @elseif($confirmarLimpiezaSignos && $registroTipo === 'signos')
                <p class="rm-signos__clear-confirmation" role="status">¿Limpiar las mediciones y las observaciones sin guardar? Se conservarán el residente, la fecha y la hora.</p>
                <button type="button" class="rm-btn-secondary" wire:click="$set('confirmarLimpiezaSignos', false)">Seguir editando</button>
                <button type="button" class="rm-btn-secondary" wire:click="limpiarCamposSignos" wire:loading.attr="disabled">Confirmar limpieza</button>
            @elseif($signosConfirmacionPendiente && $registroTipo === 'signos')
                <button type="button" class="rm-btn-secondary" wire:click="cancelarConfirmacionSignos">Volver y revisar</button>
                <button type="button" class="{{ $hayCriticoSignos ? 'rm-btn-danger' : 'rm-btn-primary' }}" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos">{{ $signosPasoConfirmacion === 'final' ? ($generaraAlertaSignos ? 'Registrar y generar alerta' : ($hayCriticoSignos ? 'Registrar lectura crítica' : 'Confirmar lectura')) : ($hayCriticoSignos ? 'Confirmar que la lectura es correcta' : 'Confirmar lectura') }}</button>
            @elseif($drawerPaso === 'register-form' && $guardarMetodo)
                @if($registroTipo === 'signos')
                    <button type="button" class="rm-btn-secondary" wire:click="cerrarSelectorRegistro">Cancelar</button>
                    <button type="button" class="rm-btn-secondary" wire:click="solicitarLimpiezaSignos" wire:loading.attr="disabled"><i class="ph-bold ph-eraser" aria-hidden="true"></i> Limpiar campos</button>
                @elseif($esFormularioClinico)
                    <button type="button" class="rm-btn-secondary" wire:click="cerrarSelectorRegistro" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}">Cancelar</button>
                @else
                    <button type="button" class="rm-btn-secondary" wire:click="volverPanelDetalle">Volver al selector</button>
                @endif
                @if($registroTipo === 'medicacion')
                    @if($medOcurrenciaSeleccionada)
                        <button type="button" class="rm-btn-primary" wire:click="guardarMed" wire:loading.attr="disabled" wire:target="guardarMed">
                            <i class="ph-bold ph-check" aria-hidden="true"></i>
                            <span wire:loading.remove wire:target="guardarMed">Confirmar administración</span>
                            <span wire:loading wire:target="guardarMed">Registrando…</span>
                        </button>
                    @endif
                @elseif($registroTipo === 'signos')
                    <button type="button" class="{{ $hayCriticoSignos ? 'rm-btn-danger' : 'rm-btn-primary rm-btn-primary--confirm' }}" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos" @if($errors->any()) data-has-errors="true" @endif><i class="ph-bold {{ $hayCriticoSignos ? 'ph-warning-circle' : 'ph-check' }}" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarSignos">{{ $errors->has('signos_guardado') ? 'Intentar nuevamente' : ($hayCriticoSignos ? 'Revisar lectura crítica' : 'Confirmar y registrar') }}</span><span wire:loading wire:target="guardarSignos">Evaluando…</span></button>
                @elseif($esFormularioClinico)
                    <button type="button" class="rm-btn-primary rm-btn-primary--confirm" @click="prepareFeedback(@js($registroTipo))" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}">
                        <i class="ph-bold ph-check" aria-hidden="true"></i><span wire:loading.remove wire:target="{{ $guardarMetodo }}">Confirmar y registrar</span><span wire:loading wire:target="{{ $guardarMetodo }}">Registrando…</span>
                    </button>
                @else
                    <button type="button" class="rm-btn-primary" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i> Guardar registro</button>
                @endif
            @endif
        </x-slot:footer>
        @endif
    </x-ui.quick-register-modal>
    @include('livewire.cuidados.partials.clinical-operation-result', ['clinicalResultResidentCode' => $detalleResidente['cod_residente'] ?? null])
</div>
