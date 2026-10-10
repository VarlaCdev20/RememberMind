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
        $esFormularioClinico = in_array($registroTipo, ['dolor', 'alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true) && ! $esSelectorRegistro;
        $tituloClinico = ['dolor' => $dolorCodOrigen ? 'Reevaluación del dolor' : 'Valoración de dolor', 'alimentacion' => 'Registro de ingesta', 'hidratacion' => 'Hidratación', 'eliminacion' => 'Registro de eliminación', 'movilidad' => 'Registro de movilidad'][$registroTipo] ?? 'Nuevo registro';
        $esResultadoDolor = $drawerPaso === 'register-result' && $registroTipo === 'dolor' && $dolorResultado !== [];
        $esResultadoMovilidad = $drawerPaso === 'register-result' && $registroTipo === 'movilidad' && $movResultado !== [];
        $esResultadoEliminacion = $drawerPaso === 'register-result' && $registroTipo === 'eliminacion' && $elimResultado !== [];
        $esResultadoHidratacion = $drawerPaso === 'register-result' && $registroTipo === 'hidratacion' && $hidratacionResultado !== [];
        $esResultadoIngesta = $drawerPaso === 'register-result' && $registroTipo === 'alimentacion' && $ingestaResultado !== [];
        $esResultadoSignos = $drawerPaso === 'register-result' && $registroTipo === 'signos' && $signosResultadoRegistro !== [];
        $hayCriticoSignos = collect($signosEvaluacion['resultados'] ?? [])->contains(fn (array $resultado) => ($resultado['severidad'] ?? null) === 'CRITICO' || ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR');
        $generaraAlertaSignos = collect($signosEvaluacion['resultados'] ?? [])->contains(fn (array $resultado) => ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR');
        $volverRegistro = $confirmarDescarte ? null : ($signosConfirmacionPendiente ? 'cancelarConfirmacionSignos' : ($drawerPaso === 'register-form' ? 'volverPanelDetalle' : null));
        $guardarMetodo = match ($registroTipo) {
            'hidratacion' => 'guardarHidratacion', 'signos' => 'guardarSignos', 'medicacion' => 'guardarMed', 'dolor' => 'guardarDolor',
            'eliminacion' => 'guardarEliminacion', 'alimentacion', 'movilidad' => 'guardarCuidado',
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
                        @if($puedeConsultarExperto)
                            <a class="rm-btn rm-btn-secondary min-h-11 whitespace-normal" href="{{ route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $detalleResidente['cod_residente']]) }}"><i class="ph ph-brain" aria-hidden="true"></i> Sistema experto</a>
                        @endif
                    </div>
                @endif
            @endif
        </x-slot:footer>
    </x-ui.resident-summary-drawer>

    <x-ui.quick-register-modal id="resident-register" model="mostrarSelectorModal" class="rm-resident-directory__register-modal {{ $esFormularioClinico ? 'rm-clinical-form-modal' : '' }} {{ $registroTipo === 'dolor' ? 'rm-resident-directory__register-modal--dolor' : '' }} {{ $registroTipo === 'alimentacion' ? 'rm-resident-directory__register-modal--ingesta' : '' }} {{ $registroTipo === 'eliminacion' ? 'rm-resident-directory__register-modal--eliminacion' : '' }} {{ $registroTipo === 'movilidad' ? 'rm-resident-directory__register-modal--movilidad' : '' }} {{ $registroTipo === 'hidratacion' ? 'rm-resident-directory__register-modal--hidratacion' : '' }} {{ $esResultadoSignos ? 'rm-resident-directory__register-modal--signos-result' : '' }} {{ $esSelectorRegistro ? 'rm-resident-directory__register-modal--selector' : ($registroTipo === 'signos' ? 'rm-resident-directory__register-modal--signos' : 'rm-resident-directory__register-modal--form') }} {{ $signosConfirmacionPendiente ? 'rm-resident-directory__register-modal--signos-confirm' : '' }}" :title="$esSelectorRegistro && $detalleResidente ? 'Registrar para '.\Illuminate\Support\Str::title(mb_strtolower($detalleResidente['nombre_completo'])) : (($esResultadoSignos || $esResultadoDolor || $esResultadoIngesta || $esResultadoHidratacion || $esResultadoEliminacion || $esResultadoMovilidad) ? 'Resultado del registro' : ($signosConfirmacionPendiente ? ($hayCriticoSignos ? 'Revisión de medición crítica' : 'Revisión de presión arterial') : ($registroTipo === 'signos' ? 'Signos vitales' : ($esFormularioClinico ? $tituloClinico : 'Nuevo registro'))))" :subtitle="$registroTipo === 'movilidad' && !$esResultadoMovilidad ? 'Registra la movilidad observada, los apoyos utilizados y la respuesta del residente.' : ($registroTipo === 'eliminacion' && !$esResultadoEliminacion ? 'Registra una eliminación observada durante el cuidado del residente.' : ($registroTipo === 'hidratacion' && !$esResultadoHidratacion ? 'Registro de aporte de líquidos del residente' : ($esSelectorRegistro ? 'Selecciona qué deseas registrar' : (($esResultadoSignos || $esResultadoDolor || $esResultadoIngesta || $esResultadoHidratacion || $esResultadoEliminacion || $esResultadoMovilidad) ? 'Seguimiento clínico actualizado' : ($signosConfirmacionPendiente ? 'Comprueba las lecturas señaladas' : ($registroTipo === 'signos' ? 'Registro clínico del residente' : ($registroTipo === 'alimentacion' ? 'Registra la alimentación y tolerancia observada durante el turno.' : ($registroTipo === 'dolor' ? ($dolorCodOrigen ? 'Registra una nueva valoración sin modificar la historia anterior.' : 'Registra intensidad, localización y características observadas del dolor.') : 'Registro del residente seleccionado'))))))))" close-method="cerrarSelectorRegistro" :back-method="($esResultadoSignos || $esResultadoDolor || $esResultadoIngesta || $esResultadoHidratacion || $esResultadoEliminacion || $esResultadoMovilidad) ? null : $volverRegistro" :back-label="$signosConfirmacionPendiente ? 'Volver y revisar' : 'Volver al selector de registros'">
        <x-slot:icon>
            @if($esSelectorRegistro)
                <span class="rm-quick-register__header-icon" aria-hidden="true"><i class="ph-bold ph-plus-circle"></i></span>
            @elseif($registroTipo === 'dolor')
                <span class="rm-clinical-form__icon" aria-hidden="true"><i class="ph-bold ph-person"></i></span>
            @elseif($registroTipo === 'alimentacion')
                <span class="rm-ingesta__header-icon" aria-hidden="true"><i class="ph-bold ph-bowl-food"></i></span>
            @elseif($registroTipo === 'hidratacion')
                <span class="rm-hidratacion__header-icon" aria-hidden="true"><i class="ph-bold ph-drop"></i></span>
            @elseif($esFormularioClinico)
                <i class="ph-bold {{ ['dolor' => 'ph-person', 'alimentacion' => 'ph-bowl-food', 'eliminacion' => 'ph-drop', 'movilidad' => 'ph-person-simple-walk'][$registroTipo] }}" aria-hidden="true"></i>
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
                            <span>{{ $registroTipo === 'movilidad' ? 'Profesional · Enfermería' : 'Profesional que registra'.(in_array($registroTipo, ['dolor', 'alimentacion', 'hidratacion', 'eliminacion'], true) ? ' · Enfermería' : '') }}</span><strong data-clinical-professional>{{ auth()->user()->name }}</strong>
                            <span>Jornada · {{ $detalleResidente['turno_nombre'] ?: 'No disponible' }}</span>
                            @if(in_array($registroTipo, ['alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true) && $turnoActual)
                                <span>{{ \Carbon\Carbon::parse($turnoActual->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($turnoActual->hora_cierre ?: $turnoActual->hora_fin)->format('H:i') }}</span>
                            @endif
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
            @if($registroTipo === 'dolor' && $drawerPaso === 'register-form')
                <div wire:key="pain-capture-{{ $detalleResidente['cod_residente'] }}-{{ $dolorCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
                    @include('livewire.cuidados.partials.mis-residentes-dolor')
                </div>
            @endif
            @if($registroTipo === 'alimentacion' && $drawerPaso === 'register-form')
                <div wire:key="intake-capture-{{ $detalleResidente['cod_residente'] }}-{{ $registroCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
                    @include('livewire.cuidados.partials.mis-residentes-ingesta')
                </div>
            @endif
            @if($registroTipo === 'hidratacion' && $drawerPaso === 'register-form')
                <div wire:key="hydration-capture-{{ $detalleResidente['cod_residente'] }}-{{ $registroCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
                    @include('livewire.cuidados.partials.mis-residentes-hidratacion')
                </div>
            @endif
            @if($registroTipo === 'movilidad' && $drawerPaso === 'register-form')
                <div wire:key="mobility-capture-{{ $registroCapturaVersion }}">@include('livewire.cuidados.partials.mis-residentes-movilidad')</div>
            @endif
            @if($registroTipo === 'eliminacion' && $drawerPaso === 'register-form')
                <div wire:key="elimination-capture-{{ $detalleResidente['cod_residente'] }}-{{ $registroCapturaVersion }}" @if($confirmarDescarte) hidden inert @endif>
                    @include('livewire.cuidados.partials.mis-residentes-eliminacion')
                </div>
            @endif
            @if($confirmarDescarte)
                <section class="rm-resident-directory__discard" role="alert" aria-labelledby="resident-discard-title">
                    <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
                    <h4 id="resident-discard-title">¿Salir sin guardar?</h4>
                    <p>Tienes cambios sin registrar. Si confirmas, se descartarán los datos introducidos. El historial guardado permanecerá intacto.</p>
                </section>
            @elseif($esResultadoDolor)
                <x-ui.resultado-operacion-clinica :title="($dolorResultado['reevaluacion'] ?? false) ? 'Reevaluación registrada' : 'Valoración de dolor registrada'" message="La valoración se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$dolorResultado['fecha_hora']" :professional="$dolorResultado['profesional']" :measurements="$dolorResultado['mediciones']" />
                @can('valoraciones_dolor.ver')
                    <div class="rm-dolor" wire:key="pain-result-{{ $dolorResultado['codigo'] }}" x-data="rmDolorRegistro(@js($dolorHistorial), { eva: '' }, '', { persisted: true, origin: @js($dolorCodOrigen), episode: @js($dolorEpisodio) })">
                        <button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="dolor-grafica-popup" :aria-expanded="trendOpen">Ver evolución</button>
                        @include('livewire.cuidados.partials.mis-residentes-dolor-grafica')
                    </div>
                @endcan
            @elseif($esResultadoIngesta)
                <x-ui.resultado-operacion-clinica title="Ingesta registrada" message="La comida observada se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$ingestaResultado['fecha_hora']" :professional="$ingestaResultado['profesional']" :measurements="$ingestaResultado['mediciones']" />
                @can('registros_ingesta.ver')
                    <div class="rm-ingesta" wire:key="intake-result-{{ $ingestaResultado['codigo'] }}" x-data="rmIngestaRegistro(@js($ingestaHistorial), {}, '', @js(config('enfermeria.porcentaje_baja_ingesta', 50)), true)" :data-graph-open="trendOpen ? 'true' : 'false'">
                        <button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="ingesta-grafica-popup" :aria-expanded="trendOpen">Ver evolución</button>
                        @include('livewire.cuidados.partials.mis-residentes-ingesta-grafica')
                    </div>
                @endcan
            @elseif($esResultadoHidratacion)
                <x-ui.resultado-operacion-clinica title="Hidratación registrada" message="El aporte quedó incorporado al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$hidratacionResultado['fecha_hora']" :professional="$hidratacionResultado['profesional']" :measurements="$hidratacionResultado['mediciones']" />
                @can('registros_hidratacion.ver')
                    <div class="rm-hidratacion" wire:key="hydration-result-{{ $hidratacionResultado['codigo'] }}" x-data="rmHidratacionRegistro(@js($hidratacionHistorial), {}, '', true)" :data-graph-open="trendOpen ? 'true' : 'false'">
                        <button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="hidratacion-grafica-popup" :aria-expanded="trendOpen">Ver evolución</button>
                        @include('livewire.cuidados.partials.mis-residentes-hidratacion-grafica')
                    </div>
                @endcan
            @elseif($esResultadoMovilidad)
                <x-ui.resultado-operacion-clinica title="Movilidad registrada" message="El evento se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$movResultado['fecha_hora']" :professional="$movResultado['profesional']" :measurements="$movResultado['mediciones']" />
                @can('registros_movilidad.ver')
                    <div class="rm-movilidad" wire:key="mobility-result-{{ $movResultado['codigo'] }}" x-data="rmMovilidadRegistro(@js($movHistorial), {}, true)"><button type="button" class="rm-btn-secondary" @click="openTrend($event.currentTarget)" aria-controls="movilidad-historial-popup" :aria-expanded="trendOpen">Ver historial</button>@include('livewire.cuidados.partials.mis-residentes-movilidad-historial')</div>
                @endcan
            @elseif($esResultadoEliminacion)
                <x-ui.resultado-operacion-clinica title="Eliminación registrada" message="El evento se incorporó al historial del residente." :resident="$detalleResidente['nombre_completo']" :date-time="$elimResultado['fecha_hora']" :professional="$elimResultado['profesional']" :measurements="$elimResultado['mediciones']" />
                @can('registros_eliminacion.ver')
                    <div class="rm-eliminacion" wire:key="elimination-result-{{ $elimResultado['codigo'] }}" x-data="rmEliminacionRegistro(@js($elimHistorial), {}, true)" :data-history-open="trendOpen ? 'true' : 'false'">
                        <button class="rm-btn-secondary" type="button" @click="openTrend($event.currentTarget)" aria-controls="eliminacion-historial-popup" :aria-expanded="trendOpen">Ver historial</button>
                        @include('livewire.cuidados.partials.mis-residentes-eliminacion-historial')
                    </div>
                @endcan
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
            @elseif(in_array($drawerPaso, ['register-selector', 'register-form'], true) && !in_array($registroTipo, ['dolor', 'alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true))
                <div wire:key="register-capture-{{ $registroTipo }}-{{ $registroCapturaVersion }}" @if(in_array($registroTipo, ['medicacion', 'seguimiento', 'procedimiento', 'alerta'], true)) x-data="rmClinicalCapture(@js($registroInicial))" @endif>
                @include('livewire.cuidados.partials.mis-residentes-registro-contenido')
                </div>
            @endif
        @endif
        @if(in_array($drawerPaso, ['register-form', 'register-result'], true) || $confirmarDescarte)
        <x-slot:footer>
            @if($esResultadoDolor)
                <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeDolor">Volver al residente</button>
                @can('valoraciones_dolor.ver')
                    <button type="button" class="rm-btn-secondary" wire:click="abrirReevaluacionDolor('{{ $dolorResultado['codigo'] }}')" wire:loading.attr="disabled" wire:target="abrirReevaluacionDolor">Registrar reevaluación</button>
                @endcan
            @elseif($esResultadoIngesta)
                <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeIngesta">Volver al residente</button>
                <button type="button" class="rm-btn-secondary" wire:click="registrarOtraIngesta" wire:loading.attr="disabled">Registrar otra ingesta</button>
            @elseif($esResultadoHidratacion)
                <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeHidratacion">Volver al residente</button>
                <button type="button" class="rm-btn-secondary" wire:click="registrarOtroAporte" wire:loading.attr="disabled">Registrar otro aporte</button>
            @elseif($esResultadoMovilidad)
                <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeMovilidad">Volver al residente</button>
                <button type="button" class="rm-btn-secondary" wire:click="registrarOtraMovilidad" wire:loading.attr="disabled">Registrar otro evento</button>
            @elseif($esResultadoEliminacion)
                <button type="button" class="rm-btn-primary" wire:click="volverResidenteDesdeEliminacion">Volver al residente</button>
                <button type="button" class="rm-btn-secondary" wire:click="registrarOtraEliminacion" wire:loading.attr="disabled">Registrar otro evento</button>
            @elseif($esResultadoSignos)
                <button type="button" class="rm-btn-secondary" wire:click="volverResidenteDesdeSignos">Volver al residente</button>
                @if(auth()->user()?->can('enfermeria.ver_ficha_paciente'))
                    <a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente'], 'tab' => 'signos']) }}">Ver registro</a>
                @endif
                @if(filled($signosResultadoRegistro['cod_alerta'] ?? null) && auth()->user()?->can('alertas.ver'))
                    <a class="rm-btn-danger" href="{{ route('admin.enfermeria.alertas', ['adulto' => $detalleResidente['cod_residente'], 'alerta' => $signosResultadoRegistro['cod_alerta']]) }}">Atender alerta <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                @endif
            @elseif($confirmarDescarte)
                <button type="button" class="rm-btn-secondary" wire:click="cancelarDescarte" wire:loading.attr="disabled">Seguir editando</button>
                <button type="button" class="rm-btn-danger" wire:click="descartarCambios" wire:loading.attr="disabled">Salir sin guardar</button>
                @error('continuidad_signos')<p class="rm-signos__error" role="alert">{{ $message }}</p>@enderror
            @elseif($confirmarLimpiezaRegistro)
                <p class="rm-signos__clear-confirmation" role="status">¿Limpiar los campos sin guardar? Se conservarán el residente y el contexto del registro.</p>
                <button type="button" class="rm-btn-secondary" wire:click="cancelarLimpiezaRegistro" wire:loading.attr="disabled">Seguir editando</button>
                <button type="button" class="rm-btn-secondary" wire:click="limpiarCamposRegistro" wire:loading.attr="disabled">Confirmar limpieza</button>
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
                @elseif($esFormularioClinico)
                    <button type="button" class="rm-btn-secondary" wire:click="cerrarSelectorRegistro" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}">Cancelar</button>
                @else
                    <button type="button" class="rm-btn-secondary" wire:click="volverPanelDetalle">Volver al selector</button>
                @endif
                <button type="button" class="rm-btn-secondary" wire:click="solicitarLimpiezaRegistro" wire:loading.attr="disabled"><i class="ph-bold ph-eraser" aria-hidden="true"></i> Limpiar campos</button>
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
                @elseif($registroTipo === 'dolor')
                    <button type="button" class="rm-btn-primary rm-btn-primary--confirm" wire:click="guardarDolor" wire:loading.attr="disabled" wire:target="guardarDolor"><i class="ph-bold ph-check" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarDolor">Confirmar y registrar</span><span wire:loading wire:target="guardarDolor">Registrando…</span></button>
                @elseif(in_array($registroTipo, ['alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true))
                    <button type="button" class="rm-btn-primary rm-btn-primary--confirm" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}"><i class="ph-bold ph-check" aria-hidden="true" wire:loading.remove wire:target="{{ $guardarMetodo }}"></i><span class="rm-btn__spinner" aria-hidden="true" wire:loading.inline-block wire:target="{{ $guardarMetodo }}"></span><span wire:loading.remove wire:target="{{ $guardarMetodo }}">Confirmar y registrar</span><span wire:loading wire:target="{{ $guardarMetodo }}">Registrando…</span></button>
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
