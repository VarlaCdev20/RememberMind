<div class="rm-resident-directory" x-data="{ section: 'salud' }" @resident-directory-opened.window="section = 'salud'; $nextTick(() => $el.querySelector('.rm-drawer-header h2')?.focus())" @resident-directory-step-changed.window="$nextTick(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus())" @resident-directory-selector-opened.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('.rm-resident-directory__register-modal .rm-modal-panel')?.focus()))" @resident-directory-selector-closed.window="$nextTick(() => requestAnimationFrame(() => $el.querySelector('#resident-register-trigger')?.focus()))">
    <header class="rm-resident-directory__header animate-fade-in-up">
        <span class="rm-resident-directory__header-leaves" aria-hidden="true"><i class="ph ph-leaf"></i><i class="ph ph-leaf"></i><i class="ph ph-leaf"></i></span>
        <div class="rm-resident-directory__heading">
            <span class="rm-resident-directory__heading-icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span>
            <div>
                <h1>{{ $esSuperAdmin ? 'Supervisión de residentes' : 'Mis residentes' }}</h1>
                <p>Personas asignadas a tu cuidado en esta jornada.</p>
            </div>
        </div>
        @if($esModoConsulta || $turnoActual)
        <div class="rm-resident-directory__context">
            <i class="ph-bold ph-calendar-check" aria-hidden="true"></i>
            <span>
                @if($esModoConsulta)
                    <strong>MODO CONSULTA / SOLO LECTURA</strong><small>Sin acciones de registro</small>
                @elseif($turnoActual)
                    <strong>Mi turno activo</strong><small>{{ $turnoActual->nombre ?: 'Turno en curso' }}</small>
                @endif
            </span>
        </div>
        @endif
    </header>

    <x-ui.filter-bar class="mb-4 animate-fade-in-up" aria-label="Buscar y filtrar residentes">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador Principal formato alertas --}}
            <div class="lg:col-span-4 relative flex items-center">
                <span class="rm-filter-search-icon absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre, CI o habitación..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if($search !== '')
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Filtro Estado --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer"
                    aria-label="Filtrar por estado">
                    <option value="TODOS">Todos los estados</option>
                    <option value="ESTABLE">Sin alertas activas</option>
                    <option value="VIGILANCIA">En vigilancia</option>
                    <option value="REQUIERE_ATENCION">Requiere atención</option>
                </select>
            </div>

            {{-- Filtro Habitación --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroHabitacion"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer"
                    aria-label="Filtrar por habitación">
                    <option value="">Todas las habitaciones</option>
                    @foreach($habitaciones as $habitacion)
                        <option value="{{ $habitacion }}">{{ str_starts_with(mb_strtolower($habitacion), 'hab') ? $habitacion : 'Hab. '.$habitacion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Orden --}}
            <div class="lg:col-span-2">
                <select wire:model.live="orden"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer"
                    aria-label="Ordenar residentes">
                    <option value="NOMBRE_ASC">Nombre A–Z</option>
                    <option value="NOMBRE_DESC">Nombre Z–A</option>
                    <option value="HAB_ASC">Habitación asc.</option>
                    <option value="HAB_DESC">Habitación desc.</option>
                </select>
            </div>

            {{-- Switch Vista (Tarjetas / Listado) --}}
            <div class="lg:col-span-1 flex items-center justify-end">
                <div class="flex items-center p-0.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] w-full h-[38px]">
                    <button type="button" wire:click="$set('vistaModo', 'tarjetas')" aria-pressed="{{ $vistaModo === 'tarjetas' ? 'true' : 'false' }}" aria-label="Ver tarjetas" class="flex-1 h-full rounded-lg text-xs font-bold flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--rm-role-primary)] {{ $vistaModo === 'tarjetas' ? 'bg-[var(--rm-role-primary)] text-[var(--rm-role-on-primary)] shadow-xs' : 'text-[var(--rm-text-secondary)] hover:text-[var(--rm-role-primary)]' }}" title="Ver tarjetas">
                        <i class="ph-bold ph-squares-four text-base"></i>
                    </button>
                    <button type="button" wire:click="$set('vistaModo', 'tabla')" aria-pressed="{{ $vistaModo === 'tabla' ? 'true' : 'false' }}" aria-label="Ver listado" class="flex-1 h-full rounded-lg text-xs font-bold flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--rm-role-primary)] {{ $vistaModo === 'tabla' ? 'bg-[var(--rm-role-primary)] text-[var(--rm-role-on-primary)] shadow-xs' : 'text-[var(--rm-text-secondary)] hover:text-[var(--rm-role-primary)]' }}" title="Ver listado">
                        <i class="ph-bold ph-list-dashes text-base"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Fila de chips de filtros activos formato alertas --}}
        @php
            $hasFiltrosActivos = !empty($search) || ($filtroEstado !== 'TODOS') || !empty($filtroHabitacion);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="rm-filter-scroll">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="rm-filter-chip rm-filter-chip--search">
                            <i class="ph-bold ph-magnifying-glass text-xs"></i>
                            <span>B?squeda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="$set('search', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroEstado !== 'TODOS')
                        <span class="rm-filter-chip {{ $filtroEstado === 'REQUIERE_ATENCION' ? 'rm-filter-chip--danger' : ($filtroEstado === 'VIGILANCIA' ? 'rm-filter-chip--warning' : 'rm-filter-chip--success') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $filtroEstado === 'REQUIERE_ATENCION' ? 'bg-[var(--rm-danger)] animate-pulse' : ($filtroEstado === 'VIGILANCIA' ? 'bg-[var(--rm-status-high)]' : 'bg-[var(--rm-action-primary)]') }}"></span>
                            <span>Estado: {{ $filtroEstado }}</span>
                            <button type="button" wire:click="$set('filtroEstado', 'TODOS')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroHabitacion))
                        <span class="rm-filter-chip rm-filter-chip--clinical">
                            <i class="ph-bold ph-bed text-xs"></i>
                            <span>Habitaci?n: {{ $filtroHabitacion }}</span>
                            <button type="button" wire:click="$set('filtroHabitacion', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    <button type="button"
                        wire:click="$set('search', ''); $set('filtroEstado', 'TODOS'); $set('filtroHabitacion', '');"
                        class="rm-filter-bar__clear-btn">
                        <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </x-ui.filter-bar>

    <section class="rm-resident-directory__results animate-fade-in-up" aria-labelledby="residents-results-title" wire:loading.class="is-loading" wire:target="search,filtroEstado,filtroHabitacion,orden,vistaModo">
        <div class="rm-resident-directory__results-header">
            <div>
                <h2 id="residents-results-title">{{ $stats['total'] }} {{ $stats['total'] === 1 ? 'residente' : 'residentes' }} {{ ($esSuperAdmin || $esModoConsulta) ? ($stats['total'] === 1 ? 'disponible' : 'disponibles') : ($stats['total'] === 1 ? 'asignado' : 'asignados') }}</h2>
            </div>
            <span class="rm-resident-directory__shown" aria-live="polite">{{ $pacientes->total() }} {{ $pacientes->total() === 1 ? 'resultado' : 'resultados' }}</span>
        </div>
        <div wire:loading wire:target="search,filtroEstado,filtroHabitacion,orden,vistaModo" class="rm-resident-directory__loading" role="status" aria-label="Actualizando residentes">
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
            <div class="rm-resident-directory__empty" role="alert" wire:loading.remove wire:target="search,filtroEstado,filtroHabitacion,orden,vistaModo">
                <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span>
                <h3>No pudimos cargar los residentes</h3>
                <p>Intenta nuevamente en unos momentos.</p>
                <button type="button" wire:click="$refresh" class="rm-btn-secondary">Reintentar</button>
            </div>
        @elseif($pacientes->isEmpty())
            <div class="rm-resident-directory__empty" wire:loading.remove wire:target="search,filtroEstado,filtroHabitacion,orden,vistaModo">
                <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span>
                @if($stats['total'] === 0)
                    <h3>{{ $esSuperAdmin ? 'No hay residentes disponibles' : 'No tienes residentes asignados' }}</h3>
                    <p>{{ $esSuperAdmin ? 'No se encontraron residentes en el alcance de esta consulta.' : 'No hay residentes asociados a tu turno actual.' }}</p>
                    @unless($esSuperAdmin)<a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary">Consultar información de mi turno <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endunless
                @else
                    <h3>No encontramos residentes con esos criterios</h3>
                    <p>Prueba con otro nombre, habitación o estado.</p>
                    <button type="button" wire:click="limpiarFiltros" class="rm-btn-secondary">Limpiar filtros</button>
                @endif
            </div>
        @elseif($vistaModo === 'tarjetas')
            <div class="rm-resident-directory__cards" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroHabitacion,orden,vistaModo">
                @foreach($pacientes as $paciente)
                    <x-ui.resident-directory-item :paciente="$paciente" mode="card" :key="'card-'.$paciente->cod_residente" />
                @endforeach
            </div>
        @else
            <div class="rm-resident-directory__list" role="list" wire:loading.remove wire:target="search,filtroEstado,filtroHabitacion,orden,vistaModo">
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
        $volverRegistro = $confirmarDescarte ? null : ($esSelectorRegistro ? 'cerrarSelectorRegistro' : ($drawerPaso === 'register-form' ? 'volverPanelDetalle' : null));
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
            <div class="rm-resident-directory__drawer">
                <div class="rm-resident-directory__profile">
                    @if($detalleResidente['foto'])
                        <img src="{{ $detalleResidente['foto'] }}" alt="" class="rm-resident-directory__portrait">
                    @else
                        <span class="rm-resident-directory__portrait rm-resident-directory__portrait--initials" aria-hidden="true">{{ $detalleResidente['iniciales'] }}</span>
                    @endif
                    <div class="rm-resident-directory__profile-copy">
                        <span class="rm-resident-directory__eyebrow">RESIDENTE</span>
                        <h3>{{ $detalleResidente['nombre_completo'] }}</h3>
                        <p><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i> {{ $detalleResidente['edad_texto'] ?: 'Edad no registrada' }}</p>
                        <p><i class="ph-bold ph-bed" aria-hidden="true"></i> {{ $detalleResidente['ubicacion_formateada'] }}</p>
                        <span class="rm-resident-directory__status rm-resident-directory__status--{{ $detalleResidente['estado_color'] }}">{{ $detalleResidente['estado_humano'] }}</span>
                    </div>
                </div>
                <dl class="rm-resident-directory__facts">
                    <div><dt><i class="ph-bold ph-calendar-check" aria-hidden="true"></i> Fecha de ingreso</dt><dd>{{ $detalleResidente['fecha_ingreso'] ? \Carbon\Carbon::parse($detalleResidente['fecha_ingreso'])->format('d/m/Y') : 'No registrada' }}</dd></div>
                    <div><dt><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i> Plan de cuidados</dt><dd>{{ $detalleResidente['plan_prioridad'] ?: 'Sin prioridad registrada' }}</dd></div>
                </dl>

                <div class="rm-resident-directory__snapshot" aria-label="Datos destacados del residente">
                    @can('signos_vitales.ver')
                        <article class="rm-resident-directory__snapshot-card rm-resident-directory__snapshot-card--clinical">
                            <span class="rm-resident-directory__snapshot-icon" aria-hidden="true"><i class="ph-bold ph-heartbeat"></i></span>
                            <h4>Último control</h4>
                            @if($detalleResidente['ultimos_signos'])
                                <strong>PA {{ $detalleResidente['ultimos_signos']['pa'] ?: '—' }}</strong>
                                <p>FC {{ $detalleResidente['ultimos_signos']['fc'] }} · SpO₂ {{ $detalleResidente['ultimos_signos']['sat'] }}</p>
                                <small>{{ $detalleResidente['ultimos_signos']['fecha_hora'] }}</small>
                            @else
                                <p>Sin signos vitales registrados.</p>
                            @endif
                        </article>
                    @endcan
                    @can('alertas.ver')
                        <article class="rm-resident-directory__snapshot-card {{ $detalleResidente['alertas_count'] > 0 ? 'rm-resident-directory__snapshot-card--alert' : 'rm-resident-directory__snapshot-card--calm' }}">
                            <span class="rm-resident-directory__snapshot-icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span>
                            <h4>Alertas activas</h4>
                            <strong>{{ $detalleResidente['alertas_count'] }}</strong>
                            <p>{{ $detalleResidente['alertas_count'] === 0 ? 'Sin alertas activas' : ($detalleResidente['alertas_count'] === 1 ? 'Alerta activa registrada' : 'Alertas activas registradas') }}</p>
                        </article>
                    @endcan
                    @can('planes_cuidado.ver')
                        <article class="rm-resident-directory__snapshot-card rm-resident-directory__snapshot-card--care">
                            <span class="rm-resident-directory__snapshot-icon" aria-hidden="true"><i class="ph-bold ph-clipboard-text"></i></span>
                            <h4>Plan de cuidados</h4>
                            <strong>{{ $detalleResidente['plan_nombre'] ?: 'Sin plan activo' }}</strong>
                            <p>{{ $detalleResidente['plan_prioridad'] ?: 'Sin prioridad registrada' }}</p>
                        </article>
                    @endcan
                </div>

                <div class="rm-resident-directory__section-heading">
                    <h4>Resumen clínico</h4>
                    <p>Información registrada para consultar antes de atender.</p>
                </div>
                <div class="rm-resident-directory__sections">
                    @if(auth()->user()->can('signos_vitales.ver') || auth()->user()->can('alertas.ver'))
                        <button type="button" @click="section = section === 'salud' ? '' : 'salud'" :aria-expanded="section === 'salud'" aria-controls="resident-health"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i><span>Estado de salud <small>Últimos controles y alertas registradas</small></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></button>
                        <div id="resident-health" x-show="section === 'salud'" x-cloak>
                            @can('signos_vitales.ver')
                                @if($detalleResidente['ultimos_signos'])
                                    <p><strong>Último control</strong> · {{ $detalleResidente['ultimos_signos']['fecha_hora'] }}</p>
                                    <p>PA {{ $detalleResidente['ultimos_signos']['pa'] }} · FC {{ $detalleResidente['ultimos_signos']['fc'] }} · SpO₂ {{ $detalleResidente['ultimos_signos']['sat'] }}</p>
                                @else
                                    <p class="rm-resident-directory__section-empty">Aún no hay signos vitales registrados.</p>
                                @endif
                            @endcan
                            @can('alertas.ver')
                                @if($detalleResidente['alertas_count'])
                                    <p>{{ $detalleResidente['alertas_count'] }} {{ $detalleResidente['alertas_count'] === 1 ? 'alerta activa' : 'alertas activas' }}.</p>
                                @endif
                            @endcan
                        </div>
                    @endif
                    @can('prescripciones.ver')
                        <button type="button" @click="section = section === 'medicacion' ? '' : 'medicacion'" :aria-expanded="section === 'medicacion'" aria-controls="resident-medication"><i class="ph-bold ph-pill" aria-hidden="true"></i><span>Medicación <small>Prescripciones vigentes, solo lectura</small></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></button>
                        <div id="resident-medication" x-show="section === 'medicacion'" x-cloak>
                            @forelse($detalleResidente['medicacion'] as $med)
                                <p><strong>{{ $med['nombre'] }}</strong> · {{ $med['dosis'] ?: 'Dosis no registrada' }} · {{ $med['via'] ?: 'Vía no registrada' }} · {{ $med['frecuencia'] ?: 'Frecuencia no registrada' }}@if($med['horarios']) · Horarios: {{ $med['horarios'] }}@endif @if($med['ultima_administracion']) · Última administración: {{ $med['ultima_administracion'] }}@endif</p>
                            @empty<p class="rm-resident-directory__section-empty">No hay prescripciones activas registradas.</p>@endforelse
                        </div>
                    @endcan
                    @can('planes_cuidado.ver')
                        <button type="button" @click="section = section === 'cuidados' ? '' : 'cuidados'" :aria-expanded="section === 'cuidados'" aria-controls="resident-care"><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i><span>Plan de cuidados <small>{{ $detalleResidente['plan_nombre'] ?: 'Actividades registradas' }}</small></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></button>
                        <div id="resident-care" x-show="section === 'cuidados'" x-cloak>
                            @forelse($detalleResidente['cuidados'] as $cuidado)
                                <p><strong>{{ $cuidado['nombre'] }}</strong> · {{ $cuidado['estado'] }} {{ $cuidado['fecha'] ? '· '.$cuidado['fecha'] : '' }}</p>
                            @empty<p class="rm-resident-directory__section-empty">No hay cuidados pendientes registrados.</p>@endforelse
                        </div>
                    @endcan
                    @can('notas_clinicas.ver')
                        <button type="button" @click="section = section === 'observaciones' ? '' : 'observaciones'" :aria-expanded="section === 'observaciones'" aria-controls="resident-notes"><i class="ph-bold ph-note-pencil" aria-hidden="true"></i><span>Observaciones <small>Notas clínicas disponibles</small></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></button>
                        <div id="resident-notes" x-show="section === 'observaciones'" x-cloak>
                            @forelse($detalleResidente['observaciones'] as $nota)
                                <p><strong>{{ $nota['fecha'] ?: 'Sin fecha' }}</strong> · {{ $nota['contenido'] }}</p>
                            @empty<p class="rm-resident-directory__section-empty">No hay observaciones registradas.</p>@endforelse
                        </div>
                    @endcan
                    @can('atenciones.ver')
                        <button type="button" @click="section = section === 'historial' ? '' : 'historial'" :aria-expanded="section === 'historial'" aria-controls="resident-history"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i><span>Historial de atención <small>Registros cronológicos disponibles</small></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></button>
                        <div id="resident-history" x-show="section === 'historial'" x-cloak>
                            @forelse($detalleResidente['historial'] as $registro)
                                <p><strong>{{ $registro['fecha'] ?: 'Sin fecha' }}</strong> · {{ $registro['tipo'] }}</p>
                            @empty<p class="rm-resident-directory__section-empty">No hay atenciones registradas.</p>@endforelse
                        </div>
                    @endcan
                </div>
            </div>
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

    <x-ui.modal-livewire id="resident-register" wire:model="mostrarSelectorModal" class="rm-resident-directory__register-modal {{ $esSelectorRegistro ? 'rm-resident-directory__register-modal--selector' : ($registroTipo === 'signos' ? 'rm-resident-directory__register-modal--signos' : 'rm-resident-directory__register-modal--form') }}" :title="$registroTipo === 'signos' ? 'Signos vitales' : 'Nuevo registro'" :subtitle="$registroTipo === 'signos' ? 'Registro clínico del residente' : 'Registro del residente seleccionado'" max-width="lg" close-method="cerrarSelectorRegistro" :back-method="$volverRegistro" :back-label="$esSelectorRegistro ? 'Volver a la vista rápida' : 'Volver al selector de registros'" :show-validation="false" :dismiss-on-backdrop="true" :draggable="!$esSelectorRegistro && $registroTipo !== 'signos'">
        <x-slot:icon>
            @if($registroTipo === 'signos')
                <span class="rm-signos__header-icon-box" aria-hidden="true">
                    <i class="ph-bold ph-heartbeat rm-signos__header-icon"></i>
                </span>
            @endif
        </x-slot:icon>
        <x-slot:context>
            @if($detalleResidente)
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
