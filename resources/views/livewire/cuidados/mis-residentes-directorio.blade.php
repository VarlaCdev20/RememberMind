<div class="rm-resident-directory" x-data="{}" @resident-directory-opened.window="$nextTick(() => $el.querySelector('.rm-drawer-header h2')?.focus())" @resident-directory-step-changed.window="$nextTick(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus())" @resident-directory-selector-opened.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus()))" @resident-directory-selector-closed.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('#resident-register-trigger')?.focus()))">
    <x-ui.residents-page-header :title="$esSuperAdmin ? 'Supervisión de residentes' : 'Mis residentes'" subtitle="Personas asignadas a tu cuidado en esta jornada." :turno="$turnoActual" :modo-consulta="$esModoConsulta" />

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
                        @if($this->puedeRegistrar())
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

    <x-ui.quick-register-modal id="resident-register" model="mostrarSelectorModal" class="rm-resident-directory__register-modal {{ $esSelectorRegistro ? 'rm-resident-directory__register-modal--selector' : ($registroTipo === 'signos' ? 'rm-resident-directory__register-modal--signos' : 'rm-resident-directory__register-modal--form') }} {{ $signosConfirmacionPendiente ? 'rm-resident-directory__register-modal--signos-confirm' : '' }}" :title="$esSelectorRegistro && $detalleResidente ? 'Registrar para '.\Illuminate\Support\Str::title(mb_strtolower($detalleResidente['nombre_completo'])) : ($esResultadoSignos ? 'Resultado del registro' : ($signosConfirmacionPendiente ? 'Revisión de medición crítica' : ($registroTipo === 'signos' ? 'Signos vitales' : 'Nuevo registro')))" :subtitle="$esSelectorRegistro ? 'Selecciona qué deseas registrar' : ($esResultadoSignos ? 'Seguimiento clínico actualizado' : ($signosConfirmacionPendiente ? 'Comprueba las lecturas señaladas' : ($registroTipo === 'signos' ? 'Registro clínico del residente' : 'Registro del residente seleccionado')))" close-method="cerrarSelectorRegistro" :back-method="$esResultadoSignos ? null : $volverRegistro" :back-label="$signosConfirmacionPendiente ? 'Volver y revisar' : 'Volver al selector de registros'">
        <x-slot:icon>
            @if($esSelectorRegistro)
                <span class="rm-quick-register__header-icon" aria-hidden="true"><i class="ph-bold ph-plus-circle"></i></span>
            @elseif($registroTipo === 'signos')
                <span class="rm-signos__header-icon-box" aria-hidden="true">
                    <i class="ph-bold ph-heartbeat rm-signos__header-icon"></i>
                </span>
            @endif
        </x-slot:icon>
        <x-slot:context>
            @if($detalleResidente && !$esSelectorRegistro)
                <div class="rm-resident-directory__register-context">
                    @if($detalleResidente['foto'])
                        <img src="{{ $detalleResidente['foto'] }}" alt="Foto de {{ $detalleResidente['nombre_completo'] }}" class="rm-resident-directory__register-avatar">
                    @else
                        <span class="rm-resident-directory__register-avatar rm-resident-directory__register-avatar--initials" aria-hidden="true">{{ $detalleResidente['iniciales'] }}</span>
                    @endif
                    <div class="rm-resident-directory__register-identity">
                        <strong>{{ $detalleResidente['nombre_completo'] }}</strong>
                        <span>{{ $detalleResidente['edad_texto'] ?: 'Edad no registrada' }} · {{ $detalleResidente['habitacion_texto'] }} · {{ $detalleResidente['cama_texto'] }}</span>
                    </div>
                    @if($registroTipo === 'signos')
                        @if(($detalleResidente['alertas_count'] ?? 0) > 0)
                            <span class="rm-badge-pill rm-badge-pill--warning" title="Alertas actualmente registradas para este residente"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ $detalleResidente['alertas_count'] }} alertas activas</span>
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
                        <h4 id="resident-discard-title">{{ $descarteCriticoConfirmado ? 'Confirmar descarte de medición crítica' : 'Hay una medición crítica sin registrar' }}</h4>
                        <p>{{ $descarteCriticoConfirmado ? 'El valor no será registrado y no se generará una alerta asociada a esta lectura.' : 'Esta lectura todavía no se ha incorporado al expediente. Continúa revisando antes de descartarla.' }}</p>
                    @else
                        <h4 id="resident-discard-title">¿Salir sin guardar?</h4>
                        <p>Tienes cambios sin registrar. Si sales ahora se perderán las mediciones introducidas.</p>
                    @endif
                </section>
            @elseif($esResultadoSignos)
                <x-ui.resultado-operacion-clinica :variant="($signosResultadoRegistro['hay_critico'] ?? false) ? 'critical' : (count($signosResultadoRegistro['advertencias'] ?? []) ? 'warning' : 'success')" :resident="$detalleResidente['nombre_completo']" :date-time="$signosResultadoRegistro['fecha_hora'] ?? null" :professional="$signosResultadoRegistro['profesional'] ?? null" :measurements="$signosResultadoRegistro['mediciones'] ?? []" :alert-code="$signosResultadoRegistro['cod_alerta'] ?? null" :warnings="$signosResultadoRegistro['advertencias'] ?? []" />
            @elseif($signosConfirmacionPendiente && $registroTipo === 'signos')
                <section class="rm-signos__confirm" role="group" aria-labelledby="signos-confirm-title" aria-describedby="signos-confirm-description">
                    <h4 id="signos-confirm-title"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ $signosPasoConfirmacion === 'final' ? 'Confirmar valor crítico' : 'Revisión de medición crítica' }}</h4>
                    <p id="signos-confirm-description">{{ $signosPasoConfirmacion === 'final' ? 'La lectura fue revisada. Al continuar se guardará un nuevo registro, se volverá a evaluar y, si corresponde, se generará una alerta abierta vinculada a la medición.' : 'Comprueba la transcripción, la técnica y el contexto clínico. Sigue el protocolo institucional vigente antes de continuar.' }}</p>
                    <ul>
                        @foreach(($signosEvaluacion['resultados'] ?? []) as $resultado)
                            @if(($resultado['severidad'] ?? null) === 'CRITICO' || ($resultado['comportamiento_alerta'] ?? null) === 'AUTOMATICA_AL_CONFIRMAR')
                                <li><strong>{{ str_replace('_', ' ', ucfirst($resultado['variable'])) }}</strong><span>{{ $resultado['valor'] }} {{ $resultado['unidad'] }}</span><em>{{ $resultado['severidad'] ?? 'Requiere confirmación' }}</em></li>
                            @endif
                        @endforeach
                        @if(is_numeric($signoSis) && is_numeric($signoDia) && (float)$signoSis <= (float)$signoDia)
                            <li><strong>Presión arterial atípica</strong><span>{{ $signoSis }} / {{ $signoDia }} mmHg</span><em>Revisar lectura</em></li>
                        @endif
                    </ul>
                    @if($signosPasoConfirmacion === 'revision')<p>Los datos aún no se han guardado. Si corresponde según protocolo, repite la medición y revisa el contexto antes de confirmar que la lectura es correcta.</p>@endif
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
                <button type="button" class="rm-btn-secondary" wire:click="cancelarDescarte">{{ $this->lecturaCriticaSinGuardar() ? 'Continuar revisando' : 'Seguir editando' }}</button>
                <button type="button" class="rm-btn-danger" wire:click="descartarCambios">{{ $this->lecturaCriticaSinGuardar() ? ($descarteCriticoConfirmado ? 'Descartar medición' : 'Revisar descarte') : 'Salir sin guardar' }}</button>
            @elseif($signosConfirmacionPendiente && $registroTipo === 'signos')
                <button type="button" class="rm-btn-secondary" wire:click="cancelarConfirmacionSignos">Volver y revisar</button>
                <button type="button" class="{{ $hayCriticoSignos ? 'rm-btn-danger' : 'rm-btn-primary' }}" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos">{{ $signosPasoConfirmacion === 'final' ? ($generaraAlertaSignos ? 'Registrar y generar alerta' : 'Registrar lectura crítica') : 'Confirmar que la lectura es correcta' }}</button>
            @elseif($drawerPaso === 'register-form' && $guardarMetodo)
                @if($registroTipo === 'signos')
                    <button type="button" class="rm-btn-secondary" wire:click="cerrarSelectorRegistro">Cancelar</button>
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
                    <button type="button" class="{{ $hayCriticoSignos ? 'rm-btn-danger' : 'rm-btn-primary rm-btn-primary--confirm' }}" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos" @if($errors->any()) data-has-errors="true" @endif><i class="ph-bold {{ $hayCriticoSignos ? 'ph-warning-circle' : 'ph-check' }}" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarSignos">{{ $errors->has('signos_guardado') ? 'Intentar nuevamente' : ($hayCriticoSignos ? 'Revisar alerta crítica' : 'Confirmar y registrar') }}</span><span wire:loading wire:target="guardarSignos">Evaluando…</span></button>
                @else
                    <button type="button" class="rm-btn-primary" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i> Guardar registro</button>
                @endif
            @endif
        </x-slot:footer>
        @endif
    </x-ui.quick-register-modal>
</div>
