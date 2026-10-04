<div class="rm-resident-directory" x-data="{}" @resident-directory-opened.window="$nextTick(() => $el.querySelector('.rm-drawer-header h2')?.focus())" @resident-directory-step-changed.window="$nextTick(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus())" @resident-directory-selector-opened.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus()))" @resident-directory-selector-closed.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('#resident-register-trigger')?.focus()))">
    <x-ui.residents-page-header :title="$esSuperAdmin ? 'Supervisión de residentes' : 'Mis residentes'" subtitle="Personas asignadas a tu cuidado en esta jornada." :turno="$turnoActual" :modo-consulta="$esModoConsulta" />

    <x-ui.resident-filter-toolbar :search="$search" :filtro-rapido="$filtroRapido" :filtro-habitacion="$filtroHabitacion" :orden="$orden" :vista-modo="$vistaModo" :stats="$stats" :habitaciones="$habitaciones" />

    <section class="rm-resident-directory__results animate-fade-in-up" aria-labelledby="residents-results-title" wire:loading.class="is-loading" wire:target="search,filtroEstado,filtroRapido,filtroHabitacion,orden,vistaModo">
        <div class="rm-resident-directory__results-header">
            <div>
                <h2 id="residents-results-title">{{ $stats['total'] }} {{ $stats['total'] === 1 ? 'residente' : 'residentes' }} {{ ($esSuperAdmin || $esModoConsulta) ? ($stats['total'] === 1 ? 'disponible' : 'disponibles') : ($stats['total'] === 1 ? 'asignado' : 'asignados') }}</h2>
            </div>
            <span class="rm-resident-directory__shown" aria-live="polite">@if($search !== '' || $filtroRapido !== 'TODOS' || $filtroHabitacion !== '' || $filtroEstado !== 'TODOS') Mostrando {{ $pacientes->total() }} de {{ $stats['total'] }} @endif</span>
        </div>
        <div wire:loading wire:target="search,filtroEstado,filtroRapido,filtroHabitacion,orden,vistaModo" class="rm-resident-directory__loading" role="status" aria-label="Actualizando residentes">
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
            <div class="rm-resident-directory__empty" role="alert" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroHabitacion,orden,vistaModo">
                <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span>
                <h3>No pudimos cargar los residentes</h3>
                <p>Intenta nuevamente en unos momentos.</p>
                <button type="button" wire:click="$refresh" class="rm-btn-secondary">Reintentar</button>
            </div>
        @elseif($pacientes->isEmpty())
            <div class="rm-resident-directory__empty" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroHabitacion,orden,vistaModo">
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
            <div class="rm-resident-directory__cards" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroHabitacion,orden,vistaModo">
                @foreach($pacientes as $paciente)
                    <x-ui.resident-compact-card :paciente="$paciente" :modo-consulta="$esModoConsulta || $esSuperAdmin || app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->isActive(auth()->user())" :key="'card-'.$paciente->cod_residente" />
                @endforeach
            </div>
        @else
            <div class="rm-resident-directory__list" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroRapido,filtroHabitacion,orden,vistaModo">
                <div class="rm-resident-directory__list-heading" aria-hidden="true"><span>Residente</span><span>Ubicación</span><span>Estado</span><span>Próximo cuidado</span><span></span></div>
                @foreach($pacientes as $paciente)
                    <x-ui.resident-directory-item :paciente="$paciente" mode="row" :key="'row-'.$paciente->cod_residente" />
                @endforeach
            </div>
        @endif
    </section>

    @if($pacientes->hasPages())
        <nav class="rm-resident-directory__pagination" aria-label="Páginas de residentes">{{ $pacientes->links() }}</nav>
    @endif

    @php
        $esSelectorRegistro = $drawerPaso === 'register-selector';
        $volverRegistro = $confirmarDescarte ? null : ($drawerPaso === 'register-form' ? 'volverPanelDetalle' : null);
        $guardarMetodo = match ($registroTipo) {
            'signos' => 'guardarSignos', 'medicacion' => 'guardarMed', 'dolor' => 'guardarDolor',
            'alimentacion', 'eliminacion', 'movilidad' => 'guardarCuidado',
            'seguimiento' => 'guardarSeguimiento', 'procedimiento' => 'guardarProcedimiento',
            'alerta' => 'guardarAlerta', default => null,
        };
    @endphp
    <x-ui.drawer-livewire wire:model="mostrarPanelDetalle" title="Resumen del residente" subtitle="Resumen de enfermería" badge="" icon="ph-identification-card" size="lg" closeMethod="cerrarPanelDetalle" :dismissOnBackdrop="true">
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
    </x-ui.drawer-livewire>

    <x-ui.modal-livewire id="resident-register" wire:model="mostrarSelectorModal" class="rm-resident-directory__register-modal {{ $esSelectorRegistro ? 'rm-resident-directory__register-modal--selector' : ($registroTipo === 'signos' ? 'rm-resident-directory__register-modal--signos' : 'rm-resident-directory__register-modal--form') }}" :title="$esSelectorRegistro && $detalleResidente ? 'Registrar para '.\Illuminate\Support\Str::title(mb_strtolower($detalleResidente['nombre_completo'])) : ($registroTipo === 'signos' ? 'Signos vitales' : 'Nuevo registro')" :subtitle="$esSelectorRegistro ? 'Selecciona qué deseas registrar' : ($registroTipo === 'signos' ? 'Registro clínico del residente' : 'Registro del residente seleccionado')" max-width="lg" close-method="cerrarSelectorRegistro" :back-method="$volverRegistro" back-label="Volver al selector de registros" :show-validation="false" :dismiss-on-backdrop="true" :draggable="true">
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
                        <div class="rm-resident-directory__register-badges" aria-label="Condiciones clínicas y alertas">
                            @if(!empty($detalleResidente['alertas']) && count($detalleResidente['alertas']) > 0)
                                @foreach(array_slice($detalleResidente['alertas'], 0, 3) as $alerta)
                                    <span class="rm-badge-pill rm-badge-pill--warning" title="{{ $alerta['motivo'] }}"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ \Illuminate\Support\Str::limit($alerta['motivo'], 24) }}</span>
                                @endforeach
                            @else
                                <span class="rm-badge-pill"><i class="ph-bold ph-check-circle" aria-hidden="true"></i> Sin alertas activas</span>
                            @endif
                        </div>
                        @can('residentes.ver')
                            <a class="rm-btn-secondary rm-resident-directory__register-expediente" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente']]) }}" target="_blank">
                                <i class="ph-bold ph-file-text" aria-hidden="true"></i> Ver expediente
                            </a>
                        @endcan
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
                    <h4 id="resident-discard-title">Cambios sin guardar</h4>
                    <p>Tienes información que todavía no se ha guardado.</p>
                </section>
            @elseif(in_array($drawerPaso, ['register-selector', 'register-form'], true))
                @include('livewire.cuidados.partials.mis-residentes-registro-contenido')
            @endif
        @endif
        @if($drawerPaso === 'register-form' || $confirmarDescarte)
        <x-slot:footer>
            @if($confirmarDescarte)
                <button type="button" class="rm-btn-secondary" wire:click="cancelarDescarte">Continuar editando</button>
                <button type="button" class="rm-btn-danger" wire:click="descartarCambios">Descartar cambios</button>
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
                    <button type="button" class="rm-btn-secondary rm-signos__btn-review" x-on:click="$dispatch('signos-revisar')"><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i> Revisar mediciones</button>
                    <button type="button" class="rm-btn-primary rm-btn-primary--confirm" wire:click="guardarSignos" wire:loading.attr="disabled" wire:target="guardarSignos" @if($errors->any()) data-has-errors="true" @endif><i class="ph-bold ph-check" aria-hidden="true"></i><span wire:loading.remove wire:target="guardarSignos">Confirmar y registrar</span><span wire:loading wire:target="guardarSignos">Registrando…</span></button>
                @else
                    <button type="button" class="rm-btn-primary" wire:click="{{ $guardarMetodo }}" wire:loading.attr="disabled" wire:target="{{ $guardarMetodo }}"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i> Guardar registro</button>
                @endif
            @endif
        </x-slot:footer>
        @endif
    </x-ui.modal-livewire>
</div>
