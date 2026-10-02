{{-- Dashboard operativo de Enfermería: jerarquía de lectura y teclado alineadas. --}}
<section class="rm-nursing-dashboard font-sans" wire:poll.60s="refrescarTurno" aria-label="Dashboard de Enfermería">
<x-ui.dashboard-welcome-header
            :usuario="Auth::user()"
            :estado="$dashboard['estado'] ?? null"
            :modo="$dashboard['modo'] ?? null"
            :image="$welcomeImage"
            :secondary-image="$welcomeSecondaryImage"
        />

@php
        $enTurno = ($dashboard['modo'] ?? '') === 'EN_TURNO';
        $pacientesKpi = $enTurno ? ($stats['pacientes'] ?? null) : null;
        $camasPorcentaje = $camasTurno && $camasTurno['total'] > 0
            ? round($camasTurno['ocupadas'] / $camasTurno['total'] * 100)
            : null;
        $medicacionTurno = $enTurno ? ($dashboard['medicacion_turno'] ?? null) : null;
        $medicacionPendiente = $medicacionTurno['pendientes'] ?? null;
        $medicacionVariante = ($medicacionTurno['retrasadas'] ?? 0) > 0
            ? 'critical'
            : ($medicacionPendiente > 0 ? 'coral' : 'mint');
        $totalSeguimiento = array_sum($distribucionPacientes);
        $camasDisponibles = $camasTurno ? max(0, $camasTurno['total'] - $camasTurno['ocupadas']) : null;
        $accionesTurno = $dashboard['estado_tareas'] ?? [];
        $accionesRealizadas = (int) ($accionesTurno['realizadas'] ?? 0);
        $accionesPendientes = (int) ($accionesTurno['pendientes'] ?? 0);
        $accionesRetrasadas = (int) ($accionesTurno['retrasadas'] ?? 0);
        $diasOcupacion = $tendenciaOcupacion['dias'] ?? [];
        $resumenOcupacion = implode('; ', array_map(
            fn ($dia) => $dia['etiqueta'].': '.$dia['ocupadas'].' camas',
            $diasOcupacion
        ));
        $incidentesPorCategoria = ($incidentesTendencia['total_periodo'] ?? 0) > 0
            ? array_map(fn ($serie) => [
                'label' => $serie['label'],
                'total' => array_sum($serie['data']),
            ], $incidentesTendencia['datasets'])
            : [];
    @endphp
    <x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--patients"
        icon="ph-users-three" variant="mint" :value="$pacientesKpi" label="Pacientes"
        :description="$enTurno ? 'Asignados a mi turno' : 'Sin turno activo'"
        :href="$enTurno && Route::has('admin.enfermeria.pacientes') && auth()->user()?->can('enfermeria.ver_pacientes_asignados') ? route('admin.enfermeria.pacientes') : null" />
    <x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--beds"
        icon="ph-bed" variant="sky"
        :value="$camasTurno ? $camasTurno['ocupadas'].'/'.$camasTurno['total'] : null"
        label="Camas ocupadas"
        :description="$camasPorcentaje !== null ? $camasPorcentaje.'% de ocupación' : 'Sin datos disponibles'"
        :progress="$camasPorcentaje" />
    <x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--risk"
        icon="ph-shield-warning" variant="neutral" :value="null" label="Alto riesgo"
        description="Sin métrica disponible" />
    <x-ui.metric-card class="rm-nursing-dashboard__kpi rm-nursing-dashboard__kpi--medication"
        icon="ph-pill" :variant="$medicacionVariante" :value="$medicacionPendiente"
        label="Medicamentos pendientes"
        :description="$medicacionPendiente === null ? 'Sin métrica disponible' : 'Del turno actual'"
        :href="$enTurno && Route::has('admin.enfermeria.medicacion') && auth()->user()?->can('enfermeria.ver_dashboard') ? route('admin.enfermeria.medicacion') : null" />
    <div class="rm-nursing-dashboard__message rm-nursing-message" aria-labelledby="nursing-message-title">
        <h2 id="nursing-message-title">Pequeños cuidados,<br>grandes momentos</h2>
    </div>

@php
            $alertasPendientes = $dashboard['alertas_prioritarias'] ?? [];
            $pacientesTurno = $dashboard['pacientes_turno'] ?? [];
            $puedeVerAlertas = Route::has('admin.enfermeria.alertas');
            $puedeVerPacientes = Route::has('admin.enfermeria.pacientes');
        @endphp

        <x-ui.card class="rm-nursing-dashboard__alerts rm-nursing-module" aria-label="Alertas prioritarias">
            <x-ui.section-header title="Alertas prioritarias" :subtitle="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Seguimiento de Enfermería' : 'Abiertas sin atención'"
                icon="ph-warning-circle" :count="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? null : ($dashboard['alertas_pendientes_count'] ?? 0)" level="2">
                <x-slot:actions>
                    @if($puedeVerAlertas)
                        <a class="rm-nursing-module__more" href="{{ route('admin.enfermeria.alertas') }}">Ver todas <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </x-slot:actions>
            </x-ui.section-header>
            <div class="rm-nursing-module__loading" wire:loading aria-live="polite">
                @for($i = 0; $i < 4; $i++)
                    <x-ui.skeleton variant="card" label="Cargando alertas" />
                @endfor
            </div>
            <div class="rm-nursing-module__content" wire:loading.remove>
                @forelse($alertasPendientes as $alerta)
                    <x-ui.priority-alert :alert="$alerta"
                        :href="$puedeVerAlertas ? route('admin.enfermeria.alertas') : null" />
                @empty
                    <x-ui.empty-state compact :icono="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'ph-warning-circle' : 'ph-check-circle'" :titulo="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Sin contexto del turno' : 'Sin alertas prioritarias'" :texto="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Consulta Alertas para revisar los registros disponibles.' : 'No hay situaciones que requieran atención inmediata.'" />
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card class="rm-nursing-dashboard__patients rm-nursing-module" aria-label="Pacientes del turno">
            <x-ui.section-header title="Pacientes del turno"
                :subtitle="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Asignaciones de Enfermería' : (($dashboard['modo'] ?? '') === 'EN_TURNO' ? 'Asignados a mi turno activo' : 'Turno activo del equipo · solo lectura')"
                icon="ph-users-three" level="2">
                <x-slot:actions>
                    @if($puedeVerPacientes)
                        <a class="rm-nursing-module__more" href="{{ route('admin.enfermeria.pacientes') }}">Ver todos <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </x-slot:actions>
            </x-ui.section-header>
            <div class="rm-nursing-module__loading" wire:loading aria-live="polite">
                @for($i = 0; $i < 5; $i++)
                    <x-ui.skeleton variant="text" label="Cargando pacientes" />
                @endfor
            </div>
            <div class="rm-nursing-module__content" wire:loading.remove>
                @if(count($pacientesTurno))
                    <div class="rm-patient-turn-table" aria-label="Pacientes asignados al turno">
                        <div class="rm-patient-turn-table__head" aria-hidden="true">
                            <span>Paciente</span><span>Ubicación</span>
                            <span>Estado cognitivo</span><span>Riesgo</span>
                            <span>Próxima atención</span>
                        </div>
                        @foreach($pacientesTurno as $paciente)
                            <x-ui.patient-turn-row :patient="$paciente"
                                :href="$puedeVerPacientes && !empty($paciente['cod_residente']) ? route('admin.enfermeria.pacientes', ['residente' => $paciente['cod_residente']]) : null" />
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state compact icono="ph-users-three" titulo="Sin pacientes asignados" :texto="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'La lista se actualizará cuando comience una jornada.' : 'Revisa las asignaciones de la jornada actual.'" />
                @endif
            </div>
        </x-ui.card>
        <x-ui.card variant="soft" class="rm-nursing-dashboard__incidents rm-chart-card rm-incident-trend"
            aria-label="Evolución de incidentes" data-incident-trend
            data-trend="{{ $incidentesTendencia ? json_encode($incidentesTendencia, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) : '' }}">
            <x-ui.section-header title="Evolución de incidentes"
                :subtitle="$incidentesTendencia === null ? 'Últimos 7 días · sin turno activo' : (($dashboard['modo'] ?? '') === 'EN_TURNO' ? 'Últimos 7 días · residentes de mi turno' : 'Últimos 7 días · turno activo del equipo')"
                icon="ph-chart-line" level="2" />
            <div class="rm-incident-trend__loading" wire:loading aria-live="polite">
                <x-ui.skeleton variant="text" label="Cargando evolución de incidentes" />
                <x-ui.skeleton variant="card" label="Cargando gráfica de incidentes" />
            </div>
            <div class="rm-incident-trend__content" wire:loading.remove>
                @if($incidentesTendencia === null)
                    <x-ui.empty-state compact icono="ph-chart-line" titulo="Sin datos de incidentes" texto="La evolución aparecerá con un turno activo." />
                @elseif($incidentesTendencia['total_periodo'] === 0)
                    <x-ui.empty-state compact icono="ph-check-circle" titulo="Sin incidentes recientes" texto="No hay registros en los últimos 7 días." />
                @else
                    <div class="rm-incident-trend__chart" wire:ignore>
                        <canvas id="incidentTrendCanvas-{{ $this->getId() }}" role="img"
                            aria-label="Evolución diaria de incidentes de los últimos siete días para residentes del turno">Evolución diaria de incidentes.</canvas>
                    </div>
                    <div class="rm-incident-trend__legend" aria-label="Categorías mostradas">
                        @foreach($incidentesTendencia['datasets'] as $serie)
                            <span><i style="--incident-series-color: {{ $serie['color'] }}" aria-hidden="true"></i>{{ $serie['label'] }}</span>
                        @endforeach
                    </div>
                @endif

                @if($incidentesTendencia !== null)
                    <div class="rm-incident-trend__footer">
                        <div class="rm-incident-trend__stat">
                            <span>Total semanal</span>
                            <strong>{{ $incidentesTendencia['total_periodo'] }}</strong>
                            <small class="rm-incident-trend__variation rm-incident-trend__variation--{{ $incidentesTendencia['variacion_tono'] }}">
                                {{ $incidentesTendencia['variacion_texto'] }}
                            </small>
                        </div>
                        @if($incidentesTendencia['categoria_principal'])
                            <div class="rm-incident-trend__stat rm-incident-trend__stat--category">
                                <span>Incidente más frecuente</span>
                                <strong>{{ $incidentesTendencia['categoria_principal'] }}</strong>
                                <small>{{ $incidentesTendencia['categoria_principal_total'] }} esta semana</small>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card class="rm-nursing-dashboard__incident-bars rm-nursing-module rm-chart-card rm-chart-glass"
            aria-labelledby="nursing-incident-bars-title"
            data-nursing-incidents-by-type="{{ $incidentesPorCategoria ? json_encode(['labels' => array_column($incidentesPorCategoria, 'label'), 'values' => array_column($incidentesPorCategoria, 'total')], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) : '' }}">
            <x-ui.section-header title="Incidentes por categoría" subtitle="Registros de los últimos 7 días" icon="ph-chart-bar" level="2" id="nursing-incident-bars-title" />
            @if(!$incidentesPorCategoria)
                <x-ui.empty-state compact icono="ph-chart-bar" titulo="Sin incidentes para comparar" texto="La distribución por categoría aparecerá cuando existan incidentes del periodo." />
            @else
                <div class="rm-nursing-analytics-canvas rm-nursing-analytics-canvas--incident" wire:ignore>
                    <canvas id="nursingIncidentsByTypeCanvas-{{ $this->getId() }}" role="img"
                        aria-label="Incidentes de los últimos siete días por categoría: {{ implode(', ', array_map(fn ($fila) => $fila['label'].': '.$fila['total'], $incidentesPorCategoria)) }}">Incidentes por categoría.</canvas>
                </div>
                <ul class="sr-only" aria-label="Totales de incidentes por categoría">@foreach($incidentesPorCategoria as $fila)<li>{{ $fila['label'] }}: {{ $fila['total'] }}</li>@endforeach</ul>
                <p class="rm-nursing-chart-note">{{ $incidentesTendencia['total_periodo'] }} incidentes registrados en el periodo. Datos de residentes del turno.</p>
            @endif
        </x-ui.card>

<x-ui.card class="rm-nursing-dashboard__distribution rm-nursing-module rm-nursing-distribution rm-chart-card rm-chart-glass" aria-labelledby="nursing-distribution-title"
    data-nursing-followup="{{ json_encode(['labels' => ['Sin alertas activas', 'Vigilancia', 'Alerta crítica'], 'values' => array_values($distribucionPacientes)], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) }}">
        <x-ui.section-header title="Estado de seguimiento" :subtitle="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Sin jornada activa' : (($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Residentes del equipo en turno' : 'Residentes asignados a tu turno')" icon="ph-chart-donut" level="2" id="nursing-distribution-title" />
        @if($totalSeguimiento === 0)
            <x-ui.empty-state compact icono="ph-users-three" titulo="Sin residentes en turno" texto="La distribución aparecerá cuando haya residentes asignados a una jornada activa." />
        @else
            <div class="rm-nursing-donut-layout">
                <div class="rm-nursing-donut-stage">
                    <div class="rm-nursing-donut-canvas" wire:ignore>
                        <canvas id="nursingFollowupCanvas-{{ $this->getId() }}" role="img"
                            aria-label="{{ $totalSeguimiento }} residentes: {{ $distribucionPacientes['sin_alertas'] }} sin alertas activas, {{ $distribucionPacientes['vigilancia'] }} en vigilancia y {{ $distribucionPacientes['atencion'] }} con alerta crítica">Estado de seguimiento de los residentes.</canvas>
                    </div>
                    <span class="rm-nursing-donut__center"><strong>{{ $totalSeguimiento }}</strong><small>residentes</small></span>
                </div>
                <ul class="rm-nursing-donut-legend" aria-label="Detalle del seguimiento">
                    <li><span class="rm-nursing-donut-legend__label"><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--mint" aria-hidden="true"></i>Sin alertas activas</span><strong>{{ $distribucionPacientes['sin_alertas'] }}</strong></li>
                    <li><span class="rm-nursing-donut-legend__label"><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--sky" aria-hidden="true"></i>Vigilancia</span><strong>{{ $distribucionPacientes['vigilancia'] }}</strong></li>
                    <li><span class="rm-nursing-donut-legend__label"><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--coral" aria-hidden="true"></i>Alerta crítica</span><strong>{{ $distribucionPacientes['atencion'] }}</strong></li>
                </ul>
            </div>
            <p class="rm-nursing-chart-note">Basado en alertas activas; no equivale a una valoración clínica.</p>
        @endif
    </x-ui.card>

<x-ui.card class="rm-nursing-dashboard__occupancy rm-nursing-module rm-nursing-distribution rm-chart-card rm-chart-glass" aria-labelledby="nursing-occupancy-title"
    data-nursing-beds="{{ $camasTurno ? json_encode(['labels' => ['Ocupadas', 'Disponibles'], 'values' => [$camasTurno['ocupadas'], $camasDisponibles]], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) : '' }}">
        <x-ui.section-header title="Ocupación de camas" subtitle="Disponibilidad actual del centro" icon="ph-bed" level="2" id="nursing-occupancy-title" />
        @if($camasTurno === null)
            <x-ui.empty-state compact icono="ph-bed" titulo="Sin camas registradas" texto="La ocupación aparecerá cuando existan camas activas verificables." />
        @else
            <div class="rm-nursing-donut-layout">
                <div class="rm-nursing-donut-stage">
                    <div class="rm-nursing-donut-canvas" wire:ignore>
                        <canvas id="nursingBedsCanvas-{{ $this->getId() }}" role="img"
                            aria-label="{{ $camasTurno['ocupadas'] }} de {{ $camasTurno['total'] }} camas ocupadas; {{ $camasDisponibles }} disponibles">Ocupación actual de camas.</canvas>
                    </div>
                    <span class="rm-nursing-donut__center"><strong>{{ $camasPorcentaje }}%</strong><small>ocupación</small></span>
                </div>
                <ul class="rm-nursing-donut-legend" aria-label="Detalle de camas">
                    <li><span class="rm-nursing-donut-legend__label"><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--mint" aria-hidden="true"></i>Ocupadas</span><strong>{{ $camasTurno['ocupadas'] }}</strong></li>
                    <li><span class="rm-nursing-donut-legend__label"><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--neutral" aria-hidden="true"></i>Disponibles</span><strong>{{ $camasDisponibles }}</strong></li>
                    <li class="rm-nursing-donut-legend__total"><span>Total de camas</span><strong>{{ $camasTurno['total'] }}</strong></li>
                </ul>
            </div>
            <p class="rm-nursing-chart-note">Camas activas y ocupaciones vigentes verificadas.</p>
        @endif
    </x-ui.card>

<x-ui.card class="rm-nursing-dashboard__schedule rm-nursing-module rm-nursing-agenda" aria-labelledby="nursing-schedule-title">
            <x-ui.section-header title="Agenda de medicación y cuidados" :subtitle="($dashboard['estado'] ?? null) === 'SIN_JORNADA_ACTIVA' ? 'Programación de Enfermería' : (($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Turno del equipo · solo lectura' : 'Programación de tu turno')" icon="ph-calendar-check" level="2" id="nursing-schedule-title">
                @if(Route::has('admin.enfermeria.agenda'))
                    <x-slot:actions>
                        <a class="rm-nursing-module__more" href="{{ route('admin.enfermeria.agenda') }}">Ver agenda <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
                    </x-slot:actions>
                @endif
            </x-ui.section-header>
            <div wire:loading.grid wire:target="refrescarTurno" class="rm-nursing-module__loading" aria-label="Cargando agenda">
                @for($i = 0; $i < 4; $i++)<x-ui.skeleton class="rm-schedule-item__skeleton" />@endfor
            </div>
            <div wire:loading.remove wire:target="refrescarTurno" class="rm-nursing-agenda__content">
                @if(($dashboard['estado'] ?? null) === 'SIN_JORNADA_ACTIVA')
                    <x-ui.empty-state compact icono="ph-calendar-x" titulo="Sin eventos del turno" texto="La agenda aparecerá cuando comience una jornada." />
                @elseif(empty($dashboard['agenda_resumen']))
                    <x-ui.empty-state compact icono="ph-calendar-check" titulo="Sin eventos programados" texto="No hay cuidados o medicaciones programadas para este turno." />
                @else
                    <ol class="rm-nursing-agenda__timeline" aria-label="Próximos eventos del turno">
                        @foreach($dashboard['agenda_resumen'] as $evento)
                            <x-ui.schedule-item :time="$evento['time']" :datetime="$evento['datetime']" :title="$evento['title']" :patient="$evento['patient']" :type="$evento['type']" :status="$evento['status']" :icon="$evento['icon']" :omission="$evento['omission']" />
                        @endforeach
                    </ol>
                @endif
            </div>
        </x-ui.card>

<x-ui.card class="rm-nursing-dashboard__tasks rm-turn-complement" aria-labelledby="nursing-tasks-title">
        <x-ui.section-header title="Tareas del turno" subtitle="Cuidados registrados" icon="ph-list-checks" level="2" id="nursing-tasks-title" />
        <div wire:loading.grid wire:target="refrescarTurno" class="rm-turn-complement__loading" aria-label="Cargando tareas">
            @for($i = 0; $i < 3; $i++)<x-ui.skeleton variant="text" />@endfor
        </div>
        <div wire:loading.remove wire:target="refrescarTurno">
            @if(!$complementos['activo'])
                <x-ui.empty-state compact icono="ph-list-checks" titulo="Sin actividad del turno" texto="Los cuidados registrados aparecerán aquí." />
            @elseif(!$complementos['tareas']['disponible'])
                <x-ui.empty-state compact icono="ph-lock" titulo="Cuidados no disponibles" texto="No tienes acceso a esta información del turno." />
            @elseif(!$complementos['tareas']['total'])
                <x-ui.empty-state compact icono="ph-list-checks" titulo="Sin cuidados registrados" texto="Aún no hay ejecuciones de cuidado en este turno." />
            @else
                <div class="rm-turn-complement__progress-copy"><strong>{{ $complementos['tareas']['completadas'] }}/{{ $complementos['tareas']['total'] }}</strong><span>Realizadas</span></div>
                <progress class="rm-turn-complement__progress" value="{{ $complementos['tareas']['completadas'] }}" max="{{ $complementos['tareas']['total'] }}" aria-label="Tareas realizadas">{{ $complementos['tareas']['progreso'] }}%</progress>
                <ul class="rm-turn-complement__list" aria-label="Cuidados del turno">
                    @foreach($complementos['tareas']['items'] as $tarea)
                        <x-ui.task-item :task="$tarea" :href="auth()->user()?->can('enfermeria.ver_ficha_paciente') && Route::has('admin.enfermeria.pacientes.ficha') ? route('admin.enfermeria.pacientes.ficha', ['adulto' => $tarea['cod_residente'], 'tab' => 'cuidados']) : null" />
                    @endforeach
                </ul>
            @endif
        </div>
</x-ui.card>

<x-ui.card class="rm-nursing-dashboard__activity-bars rm-nursing-module rm-chart-card rm-chart-glass" aria-labelledby="nursing-activity-bars-title"
    data-nursing-activity="{{ json_encode(['labels' => ['Realizadas', 'Pendientes', 'Retrasadas'], 'values' => [$accionesRealizadas, $accionesPendientes, $accionesRetrasadas]], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) }}">
    <x-ui.section-header title="Actividad del turno" subtitle="Acciones programadas según estado" icon="ph-chart-bar" level="2" id="nursing-activity-bars-title" />
    @if(($accionesTurno['total'] ?? 0) === 0)
        <x-ui.empty-state compact icono="ph-chart-bar" titulo="Sin acciones para graficar" texto="Las acciones del turno aparecerán aquí cuando exista una programación activa." />
    @else
        <div class="rm-nursing-analytics-canvas" wire:ignore>
            <canvas id="nursingActivityCanvas-{{ $this->getId() }}" role="img"
                aria-label="{{ $accionesRealizadas }} acciones realizadas, {{ $accionesPendientes }} pendientes y {{ $accionesRetrasadas }} retrasadas">Actividad del turno por estado.</canvas>
        </div>
        <p class="rm-nursing-analytics-summary">{{ $accionesRealizadas }} realizadas <span aria-hidden="true">·</span> {{ $accionesPendientes }} pendientes <span aria-hidden="true">·</span> {{ $accionesRetrasadas }} retrasadas</p>
        <p class="rm-nursing-chart-note">{{ $accionesTurno['total'] }} acciones en la agenda del turno. Las retrasadas se muestran por separado.</p>
    @endif
</x-ui.card>

<x-ui.card class="rm-nursing-dashboard__occupancy-trend rm-nursing-module rm-chart-card rm-chart-glass" aria-labelledby="nursing-occupancy-trend-title"
    data-nursing-occupancy="{{ $tendenciaOcupacion ? json_encode($tendenciaOcupacion, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) : '' }}">
    <x-ui.section-header title="Tendencia de ocupación" subtitle="Camas activas ocupadas al cierre de cada día · últimos 7 días" icon="ph-chart-line-up" level="2" id="nursing-occupancy-trend-title" />
    @if($tendenciaOcupacion === null)
        <x-ui.empty-state compact icono="ph-chart-line-up" titulo="Sin historial de ocupación" texto="La tendencia aparecerá cuando existan camas activas registradas." />
    @else
        <div class="rm-nursing-analytics-canvas rm-nursing-analytics-canvas--line" wire:ignore>
            <canvas id="nursingOccupancyCanvas-{{ $this->getId() }}" role="img" aria-label="Tendencia de ocupación: {{ $resumenOcupacion }}">Ocupación de camas de los últimos siete días.</canvas>
        </div>
        <ol class="sr-only" aria-label="Ocupación por día">@foreach($diasOcupacion as $dia)<li>{{ $dia['etiqueta'] }}: {{ $dia['ocupadas'] }} camas ocupadas</li>@endforeach</ol>
        <div class="rm-nursing-line-chart__summary"><span>Hoy <strong>{{ $diasOcupacion[6]['ocupadas'] }} ocupadas</strong></span><span>Capacidad <strong>{{ $tendenciaOcupacion['total_camas'] }} camas</strong></span></div>
    @endif
</x-ui.card>

<x-ui.card class="rm-nursing-dashboard__location rm-turn-complement" aria-labelledby="nursing-location-title">
        <x-ui.section-header title="Ubicación de pacientes" subtitle="Habitaciones y camas" icon="ph-bed" level="2" id="nursing-location-title" />
        <div wire:loading.grid wire:target="refrescarTurno" class="rm-turn-complement__loading" aria-label="Cargando ubicaciones">
            @for($i = 0; $i < 4; $i++)<x-ui.skeleton variant="text" />@endfor
        </div>
        <div wire:loading.remove wire:target="refrescarTurno">
            @if(!$complementos['activo'] || !$complementos['ubicacion']['total'])
                <x-ui.empty-state compact icono="ph-bed" titulo="Sin ubicaciones del turno" texto="No hay residentes asignados en este contexto." />
            @elseif(!$complementos['ubicacion']['disponible'])
                <x-ui.empty-state compact icono="ph-bed" titulo="Ubicación no disponible" texto="No se pudo consultar la ocupación del turno." />
            @elseif(empty($complementos['ubicacion']['items']))
                <x-ui.empty-state compact icono="ph-bed" titulo="Ubicación no disponible" texto="Sin ocupaciones activas verificables en este contexto." />
            @else
                <div class="rm-turn-complement__locations">
                    @foreach($complementos['ubicacion']['items'] as $ubicacion)
                        <x-ui.location-cell :location="$ubicacion" :href="auth()->user()?->can('enfermeria.ver_ficha_paciente') && Route::has('admin.enfermeria.pacientes.ficha') ? route('admin.enfermeria.pacientes.ficha', ['adulto' => $ubicacion['cod_residente']]) : null" />
                    @endforeach
                </div>
                @if($complementos['ubicacion']['sin_ubicacion'])<p class="rm-turn-complement__hint">{{ $complementos['ubicacion']['sin_ubicacion'] }} sin ubicación verificable.</p>@endif
                @if(($complementos['ubicacion']['ubicados'] ?? 0) > 6)<p class="rm-turn-complement__hint">Mostrando 6 de {{ $complementos['ubicacion']['ubicados'] }} ubicaciones.</p>@endif
            @endif
        </div>
    </x-ui.card>

<x-ui.card class="rm-nursing-dashboard__notes rm-turn-complement" aria-labelledby="nursing-notes-title">
        <x-ui.section-header title="Conducta y estado emocional" subtitle="Últimas 24 horas" icon="ph-note" level="2" id="nursing-notes-title" />
        <div wire:loading.grid wire:target="refrescarTurno" class="rm-turn-complement__loading" aria-label="Cargando observaciones">
            @for($i = 0; $i < 3; $i++)<x-ui.skeleton variant="text" />@endfor
        </div>
        <div wire:loading.remove wire:target="refrescarTurno">
            @if(!$complementos['activo'])
                <x-ui.empty-state compact icono="ph-note" titulo="Sin observaciones del turno" texto="Los registros de conducta aparecerán aquí." />
            @elseif(!$complementos['notas']['disponible'])
                <x-ui.empty-state compact icono="ph-note" titulo="Conducta no disponible" texto="No se puede consultar esta información ahora." />
            @elseif(empty($complementos['notas']['items']))
                <x-ui.empty-state compact icono="ph-note" titulo="Sin observaciones recientes" texto="No hay registros de las últimas 24 horas." />
            @else
                <ul class="rm-turn-complement__list" aria-label="Observaciones de conducta recientes">
                    @foreach($complementos['notas']['items'] as $nota)
                        <x-ui.behavior-note :note="$nota" :href="auth()->user()?->can('enfermeria.ver_ficha_paciente') && Route::has('admin.enfermeria.pacientes.ficha') ? route('admin.enfermeria.pacientes.ficha', ['adulto' => $nota['cod_residente'], 'tab' => 'seguimiento']) : null" />
                    @endforeach
                </ul>
            @endif
        </div>
    </x-ui.card>
</section>

@push('scripts')
<script>
    (() => {
        if (window.remembermindIncidentTrend) {
            window.remembermindIncidentTrend.render();
            return;
        }

        const controller = { chart: null, lastPayload: null, lastTheme: null, scheduled: false };
        const token = (name, fallback) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
        const resolveColor = (value) => {
            const match = /^var\((--[a-z0-9-]+)\)$/.exec(value || '');
            return match ? token(match[1], token('--rm-sky-500', '#5397B8')) : token('--rm-sky-500', '#5397B8');
        };

        controller.render = () => {
            const root = document.querySelector('[data-incident-trend]');
            const canvas = root?.querySelector('canvas');
            if (!canvas || !root.dataset.trend) {
                controller.chart?.destroy();
                controller.chart = null;
                controller.lastPayload = null;
                return;
            }
            if (typeof Chart === 'undefined') return;

            const raw = root.dataset.trend;
            const theme = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            if (controller.chart?.canvas === canvas && controller.lastPayload === raw && controller.lastTheme === theme) return;

            let summary;
            try { summary = JSON.parse(raw); } catch (_) { return; }
            const datasets = summary.datasets.map((series) => ({
                label: series.label,
                data: series.data,
                borderColor: resolveColor(series.color),
                backgroundColor: resolveColor(series.color),
                fill: false,
                tension: 0.35,
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 6,
            }));
            const chartData = { labels: summary.labels, datasets };
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                animation: matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 650, easing: 'easeOutQuart' },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: token('--rm-chart-tooltip-bg', '#4C403A'),
                        titleColor: token('--rm-chart-tooltip-text', '#EEE8E2'),
                        bodyColor: token('--rm-chart-tooltip-text', '#EEE8E2'),
                        borderColor: token('--rm-chart-tooltip-border', '#6D625D'),
                        borderWidth: 1,
                        cornerRadius: 12,
                        callbacks: { title: (items) => summary.labels_completas[items[0]?.dataIndex] || '' },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: token('--rm-text-secondary', '#6D625D'), maxRotation: 0, font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: token('--rm-border-soft', 'rgba(120,101,91,.10)') },
                        ticks: {
                            color: token('--rm-text-secondary', '#6D625D'),
                            precision: 0,
                            callback: (value) => Number.isInteger(value) ? value : '',
                            font: { size: 11 },
                        },
                    },
                },
            };

            if (controller.chart?.canvas !== canvas) {
                controller.chart?.destroy();
                Chart.getChart(canvas)?.destroy();
                controller.chart = new Chart(canvas, { type: 'line', data: chartData, options });
            } else {
                controller.chart.data = chartData;
                controller.chart.options = options;
                controller.chart.update('none');
            }
            controller.lastPayload = raw;
            controller.lastTheme = theme;
        };
        controller.schedule = () => {
            if (controller.scheduled) return;
            controller.scheduled = true;
            requestAnimationFrame(() => { controller.scheduled = false; controller.render(); });
        };
        window.remembermindIncidentTrend = controller;

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', controller.schedule, { once: true });
        else controller.schedule();
        window.addEventListener('load', controller.schedule, { once: true });
        window.addEventListener('remembermind:theme-changed', controller.schedule);
        document.addEventListener('livewire:navigated', controller.schedule);
        const bindLivewire = () => Livewire.hook('morphed', () => controller.schedule());
        if (window.Livewire) bindLivewire();
        else document.addEventListener('livewire:init', bindLivewire, { once: true });
    })();
</script>

<script>
    (() => {
        if (window.remembermindNursingAnalytics) {
            window.remembermindNursingAnalytics.schedule();
            return;
        }

        const controller = { scheduled: false, themeBound: false, attempts: 0, state: {} };
        const token = (name) => window.RMCharts?.getCss(name) || getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
        const specs = {
            followup: { selector: '[data-nursing-followup]', attribute: 'nursingFollowup', key: 'nursing-dashboard-followup' },
            beds: { selector: '[data-nursing-beds]', attribute: 'nursingBeds', key: 'nursing-dashboard-beds' },
            activity: { selector: '[data-nursing-activity]', attribute: 'nursingActivity', key: 'nursing-dashboard-activity' },
            incidentsByType: { selector: '[data-nursing-incidents-by-type]', attribute: 'nursingIncidentsByType', key: 'nursing-dashboard-incidents-by-type' },
            occupancy: { selector: '[data-nursing-occupancy]', attribute: 'nursingOccupancy', key: 'nursing-dashboard-occupancy' },
        };

        controller.renderOne = (type) => {
            const spec = specs[type];
            const root = document.querySelector(spec.selector);
            const canvas = root?.querySelector('canvas');
            const raw = root?.dataset[spec.attribute] || '';
            if (!canvas || !raw) {
                window.RMCharts.destroy(spec.key);
                delete controller.state[type];
                return;
            }

            const theme = window.RMCharts.isDark() ? 'dark' : 'light';
            const previous = controller.state[type];
            if (previous?.canvas === canvas && previous.raw === raw && previous.theme === theme) return;

            let data;
            try { data = JSON.parse(raw); } catch (_) { return; }

            let config;
            if (type === 'followup' || type === 'beds') {
                const colors = type === 'followup'
                    ? [token('--rm-chart-1'), token('--rm-chart-2'), token('--rm-chart-10')]
                    : [token('--rm-chart-1'), token('--rm-chart-9')];
                config = window.RMCharts.presets.doughnut(data.labels, data.values, colors);
                config.options.animation = reducedMotion() ? false : {
                    duration: 700, easing: 'easeOutQuart', animateRotate: true, animateScale: true,
                };
            } else if (type === 'activity' || type === 'incidentsByType') {
                config = window.RMCharts.presets.barHorizontal(data.labels, data.values, [
                    ...(type === 'activity'
                        ? [token('--rm-chart-1'), token('--rm-chart-2'), token('--rm-chart-10')]
                        : [token('--rm-chart-10'), token('--rm-chart-2'), token('--rm-chart-1')]),
                ]);
                config.options.plugins.tooltip.callbacks.label = (item) => ` ${item.raw} ${type === 'activity' ? 'acciones' : 'incidentes'}`;
                config.options.animation = reducedMotion() ? false : { duration: 650, easing: 'easeOutQuart' };
            } else {
                const color = token('--rm-chart-1');
                config = window.RMCharts.presets.area(
                    data.dias.map((dia) => dia.etiqueta),
                    [{
                        label: 'Camas ocupadas',
                        data: data.dias.map((dia) => dia.ocupadas),
                        borderColor: color,
                        backgroundColor: window.RMCharts.hexToRgba(color, .16),
                        pointBackgroundColor: color,
                        pointBorderColor: token('--rm-surface-raised'),
                    }]
                );
                config.options.scales.x.grid.display = false;
                config.options.scales.y.beginAtZero = true;
                config.options.scales.y.suggestedMax = Math.max(1, data.total_camas);
                config.options.scales.y.ticks.precision = 0;
                config.options.plugins.tooltip.callbacks = {
                    title: (items) => data.dias[items[0]?.dataIndex]?.fecha || '',
                    label: (item) => ` ${item.parsed.y} camas ocupadas`,
                };
                config.options.animation = reducedMotion() ? false : { duration: 800, easing: 'easeOutQuart' };
            }

            if (window.RMCharts.init(spec.key, canvas, config)) {
                controller.state[type] = { canvas, raw, theme };
            }
        };
        controller.render = () => {
            if (typeof Chart === 'undefined' || !window.RMCharts?.presets) {
                if (controller.attempts++ < 20) setTimeout(controller.schedule, 100);
                return;
            }
            controller.attempts = 0;
            if (!controller.themeBound) {
                window.RMCharts.onThemeChange(controller.schedule);
                controller.themeBound = true;
            }
            Object.keys(specs).forEach(controller.renderOne);
        };
        controller.schedule = () => {
            if (controller.scheduled) return;
            controller.scheduled = true;
            requestAnimationFrame(() => { controller.scheduled = false; controller.render(); });
        };
        window.remembermindNursingAnalytics = controller;

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', controller.schedule, { once: true });
        else controller.schedule();
        window.addEventListener('load', controller.schedule, { once: true });
        document.addEventListener('livewire:navigated', controller.schedule);
        const bindLivewire = () => Livewire.hook('morphed', controller.schedule);
        if (window.Livewire) bindLivewire();
        else document.addEventListener('livewire:init', bindLivewire, { once: true });
    })();
</script>

<script>
    (() => {
        if (window.remembermindNursingLightMotion) return;
        window.remembermindNursingLightMotion = true;
        const finePointer = matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)');
        let frame = 0;
        let active = null;
        let pointerX = 0;
        let pointerY = 0;

        document.addEventListener('pointermove', (event) => {
            if (!finePointer.matches || document.documentElement.classList.contains('dark') || document.documentElement.dataset.theme === 'dark') return;
            const surface = event.target.closest?.('.rm-nursing-dashboard .rm-nursing-welcome, .rm-nursing-dashboard .rm-nursing-module');
            if (!surface) return;
            active = surface;
            pointerX = event.clientX;
            pointerY = event.clientY;
            if (frame) return;
            frame = requestAnimationFrame(() => {
                frame = 0;
                if (!active?.isConnected) return;
                const bounds = active.getBoundingClientRect();
                active.style.setProperty('--rm-nursing-glint-x', `${pointerX - bounds.left}px`);
                active.style.setProperty('--rm-nursing-glint-y', `${pointerY - bounds.top}px`);
            });
        }, { passive: true });
    })();
</script>

@endpush
