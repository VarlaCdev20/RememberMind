<x-sistema-layout>
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')
@php
    $puedePreadmisiones = $visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmisiones');
    $puedeAdmisiones = $visibilidadNavegacion->puedeVerRuta('admin.administracion.admisiones');
    $puedeOcupacion = $visibilidadNavegacion->puedeVerRuta('admin.administracion.ocupacion');
    $puedeJornadas = $visibilidadNavegacion->puedeVerRuta('admin.administracion.jornadas');
    $puedeActividades = $visibilidadNavegacion->puedeVerRuta('admin.administracion.actividades');
    $puedeVisitas = $visibilidadNavegacion->puedeVerRuta('admin.administracion.visitas');
    $puedeAlertas = $visibilidadNavegacion->puedeVerRuta('admin.administracion.alertas');
    $puedeDocumentacion = $visibilidadNavegacion->puedeVerRuta('admin.administracion.documentacion');
    $totalCamas = $datos['ocupacion']['total'];
    $historia = $datos['historia'];
    $maxHistoria = max(1, collect($historia)->max(fn ($dia) => max($dia['ocupadas'], $dia['admisiones'])));
    $maxDinamica = max(1, collect($datos['dinamica'])->max(fn ($dia) => max($dia['visitas'], $dia['actividades'])));
    $fotosBienvenida = app(\App\Backend\Modulos\Reportes\Servicios\DashboardPhotoRotation::class)->pair('administracion');
    $pendientes = ($puedeAlertas ? $datos['alertas_activas'] : 0)
        + ($puedePreadmisiones ? $datos['preadmisiones_pendientes'] : 0)
        + ($puedeAdmisiones ? $datos['admisiones_pendientes'] : 0);
    $prioridadesAlerta = ['CRITICA' => ['Críticas', '--rm-danger'], 'ALTA' => ['Altas', '--rm-chart-danger'], 'MEDIA' => ['Medias', '--rm-warning'], 'BAJA' => ['Bajas', '--rm-action-primary']];
    $otrasPrioridades = max(0, $datos['alertas_activas'] - collect(array_keys($prioridadesAlerta))->sum(fn ($clave) => (int) ($datos['alertas_prioridad'][$clave] ?? 0)));
    $segmentosAlerta = [];
    $avanceAlerta = 0;
    foreach ($prioridadesAlerta as $clave => [$etiqueta, $color]) {
        $cantidad = (int) ($datos['alertas_prioridad'][$clave] ?? 0);
        if ($cantidad > 0 && $datos['alertas_activas'] > 0) {
            $siguiente = $avanceAlerta + $cantidad * 100 / $datos['alertas_activas'];
            $borde = min(0.45, ($siguiente - $avanceAlerta) / 4);
            $inicioRelleno = $avanceAlerta + $borde;
            $finRelleno = $siguiente - $borde;
            $contorno = "color-mix(in srgb, var({$color}) var(--rm-chart-mark-outline), transparent)";
            $relleno = "color-mix(in srgb, var({$color}) var(--rm-chart-mark-fill), transparent)";
            $segmentosAlerta[] = "{$contorno} {$avanceAlerta}% {$inicioRelleno}%, {$relleno} {$inicioRelleno}% {$finRelleno}%, {$contorno} {$finRelleno}% {$siguiente}%";
            $avanceAlerta = $siguiente;
        }
    }
    if ($avanceAlerta < 100) $segmentosAlerta[] = "var(--rm-surface-muted) {$avanceAlerta}% 100%";
@endphp
<div class="rm-admin-dashboard rm-dashboard-composition" aria-label="Centro de Coordinación Residencial">
    <x-ui.dashboard-header
        eyebrow="CENTRO GERIÁTRICO LOS ALMENDROS"
        :title="$saludo['saludo'] . ', ' . $saludo['nombre']"
        subtitle="Acompañamos cada ingreso y el bienestar de quienes viven en Los Almendros."
        role="Administración"
        :date="$saludo['fecha']"
        scope="Centro de Coordinación Residencial"
        :image="asset($fotosBienvenida[0])"
    />
    @if($puedePreadmisiones)
        <div class="rm-dashboard-header-actions"><a class="rm-admin-dashboard__primary-action" href="{{ route('admin.admisiones.preadmisiones') }}"><i class="ph-bold ph-user-plus" aria-hidden="true"></i> Revisar preadmisiones</a></div>
    @endif

    <x-ui.dashboard-divider />

    @if($puedeAlertas || $puedePreadmisiones || $puedeAdmisiones)
    <section class="rm-admin-dashboard__section rm-admin-dashboard__attention" aria-labelledby="admin-attention-title">
        <div class="rm-admin-dashboard__section-heading rm-section-header"><div class="rm-section-header__main"><span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span><h2 id="admin-attention-title" class="rm-section-header__title">Requiere atención ahora</h2></div><span class="rm-section-header__count">{{ $pendientes }} {{ $pendientes === 1 ? 'pendiente' : 'pendientes' }}</span></div>
        <div class="rm-admin-dashboard__attention-body">
            @if($pendientes)
                <div class="rm-admin-dashboard__attention-list">
                    @foreach([['Alertas operativas', $datos['alertas_activas'], 'admin.administracion.alertas', 'ph-warning-circle'], ['Preadmisiones en revisión', $datos['preadmisiones_pendientes'], 'admin.admisiones.preadmisiones', 'ph-user-plus'], ['Admisiones por formalizar', $datos['admisiones_pendientes'], 'admin.administracion.admisiones', 'ph-clipboard-text']] as [$titulo, $cantidad, $ruta, $icono])
                        @if($cantidad > 0 && $visibilidadNavegacion->puedeVerRuta($ruta))<a class="rm-admin-dashboard__attention-item" href="{{ route($ruta) }}"><span class="rm-admin-dashboard__attention-icon"><i class="ph-bold {{ $icono }}" aria-hidden="true"></i></span><span><strong>{{ $titulo }}</strong><small>Requiere seguimiento</small></span><b>{{ $cantidad }}</b><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>@endif
                    @endforeach
                </div>
                @if($puedeAlertas && $datos['alertas_activas'])<div class="rm-admin-dashboard__attention-detail"><span>Alertas por prioridad</span><div class="rm-admin-dashboard__priority-breakdown">@foreach(['CRITICA' => 'Críticas', 'ALTA' => 'Altas', 'MEDIA' => 'Medias', 'BAJA' => 'Bajas'] as $clave => $etiqueta)@if(($datos['alertas_prioridad'][$clave] ?? 0) > 0)<a href="{{ route('admin.administracion.alertas', ['prioridad' => $clave]) }}"><span>{{ $etiqueta }}</span><strong>{{ $datos['alertas_prioridad'][$clave] }}</strong></a>@endif @endforeach @if($otrasPrioridades > 0)<div><span>Otras prioridades</span><strong>{{ $otrasPrioridades }}</strong></div>@endif</div></div>@endif
            @else
                <div class="rm-admin-dashboard__clear"><i class="ph-bold ph-check-circle" aria-hidden="true"></i><span><strong>Todo está al día</strong><small>No hay prioridades operativas abiertas.</small></span></div>
            @endif
        </div>
    </section>
    @endif

    <section class="rm-admin-dashboard__section" aria-labelledby="admin-residence-title">
        <div class="rm-admin-dashboard__section-heading rm-section-header"><div class="rm-section-header__main"><span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span><h2 id="admin-residence-title" class="rm-section-header__title">Indicadores de coordinación</h2></div>@if($puedeOcupacion)<a href="{{ route('admin.administracion.ocupacion') }}">Ver ocupación <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif</div>
        <div class="rm-admin-dashboard__state-strip">
            @if($puedePreadmisiones)
            <x-ui.metric-card icon="ph-user-plus" variant="coral" :value="$datos['preadmisiones_pendientes']" label="Preadmisiones pendientes" description="Solicitudes por revisar" :href="route('admin.admisiones.preadmisiones')" />
            @endif
            @if($puedeOcupacion)
            <x-ui.metric-card icon="ph-bed" variant="sky" :value="$datos['camas_disponibles']" label="Camas disponibles" description="Capacidad inmediata" :href="route('admin.administracion.ocupacion')" />
            @endif
            <x-ui.metric-card icon="ph-identification-badge" variant="mint" :value="$datos['personal_activo']" label="Personal activo" description="Equipo institucional vigente" />
            @if($puedeDocumentacion)
            <x-ui.metric-card icon="ph-file-text" variant="neutral" :value="$datos['documentos_revision']" label="Documentos por revisar" description="Pendientes de validación" :href="route('admin.administracion.documentacion')" />
            @endif
        </div>
    </section>

    @if($puedePreadmisiones)
    <section class="rm-admin-dashboard__section" aria-labelledby="admin-admissions-title">
        <div class="rm-admin-dashboard__section-heading rm-section-header"><div class="rm-section-header__main"><span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold ph-user-plus"></i></span><h2 id="admin-admissions-title" class="rm-section-header__title">Ingresos en proceso</h2></div><a href="{{ route('admin.admisiones.preadmisiones') }}">Ver preadmisiones <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a></div>
        <div class="rm-admin-dashboard__split">
            <div class="rm-admin-dashboard__panel"><div class="rm-admin-dashboard__panel-heading"><h3>Preadmisiones recientes</h3><span>{{ $datos['preadmisiones_pendientes'] }} por revisar</span></div>
                @forelse($datos['preadmisiones_recientes'] as $pre)<a class="rm-admin-dashboard__record rm-admin-dashboard__record--preadmission" href="{{ route('admin.admisiones.preadmisiones', ['search' => $pre->cod_preadmision]) }}"><span class="rm-admin-dashboard__record-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pre->nombres, 0, 1).mb_substr($pre->apellido_paterno, 0, 1)) }}</span><span class="rm-admin-dashboard__record-copy"><strong>{{ $pre->nombres }} {{ $pre->apellido_paterno }}</strong><small>{{ $pre->fecha_nacimiento ? \Carbon\Carbon::parse($pre->fecha_nacimiento)->age.' años · ' : '' }}{{ \Carbon\Carbon::parse($pre->fecha_solicitud)->format('d/m/Y') }}</small></span><span class="rm-admin-dashboard__record-meta"><small>{{ $pre->prioridad ?: 'Sin prioridad' }}</small><x-ui.status-badge :estado="$pre->estado" /></span></a>
                @empty <x-ui.empty-state icono="ph-folder-open" titulo="Sin preadmisiones" texto="No hay solicitudes recientes." compact /> @endforelse
            </div>
            <div class="rm-admin-dashboard__panel"><div class="rm-admin-dashboard__panel-heading"><h3>Admisiones en preparación</h3><span>{{ $datos['admisiones_pendientes'] }} por formalizar</span></div>
                @forelse($datos['admisiones_preparacion'] as $pre)<a class="rm-admin-dashboard__record" href="{{ route('admin.admisiones.preadmisiones', ['estado' => 'APROBADA', 'search' => $pre->cod_preadmision]) }}"><span><strong>{{ $pre->nombres }} {{ $pre->apellido_paterno }}</strong><small>Etapa: formalización pendiente · Aprobada {{ $pre->fecha_revision ? \Carbon\Carbon::parse($pre->fecha_revision)->format('d/m/Y') : '' }}</small></span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
                @empty <x-ui.empty-state icono="ph-check-circle" titulo="Sin admisiones por formalizar" texto="No existen admisiones pendientes." compact /> @endforelse
            </div>
        </div>
    </section>
    @endif

    @if($puedeActividades || $puedeJornadas)
    <section class="rm-admin-dashboard__section" aria-labelledby="admin-today-title">
        <div class="rm-admin-dashboard__section-heading rm-section-header"><div class="rm-section-header__main"><span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold ph-calendar-check"></i></span><h2 id="admin-today-title" class="rm-section-header__title">Lo que ocurre hoy</h2></div></div>
        <div class="rm-admin-dashboard__split rm-admin-dashboard__split--today">
            @if($puedeActividades)
            <div class="rm-admin-dashboard__panel rm-admin-dashboard__agenda"><div class="rm-admin-dashboard__panel-heading"><h3>Agenda residencial de hoy</h3><a href="{{ route('admin.administracion.actividades') }}">Ver agenda</a></div>
                @forelse($datos['agenda'] as $actividad)<a class="rm-admin-dashboard__record" href="{{ route('admin.administracion.actividades', ['search' => $actividad->nombre]) }}"><time>{{ \Carbon\Carbon::parse($actividad->fecha_hora)->format('H:i') }}</time><span><strong>{{ $actividad->nombre }}</strong><small>{{ $actividad->lugar ?: 'Ubicación sin registrar' }}{{ $actividad->responsable ? ' · '.$actividad->responsable : '' }}</small></span></a>
                @empty <x-ui.empty-state icono="ph-calendar-blank" titulo="Sin actividades programadas" texto="No hay actividades para hoy." compact /> @endforelse
            </div>
            @endif
            @if($puedeJornadas)
            <div class="rm-admin-dashboard__today-side">
                <div class="rm-admin-dashboard__panel"><div class="rm-admin-dashboard__panel-heading"><h3>Cobertura de la jornada</h3><span>{{ $datos['jornada'] ? 'Jornada activa' : 'Sin jornada activa' }}</span></div>
                    @forelse($datos['cobertura'] as $area)<div class="rm-admin-dashboard__coverage"><span>{{ $area->area }}</span><strong>{{ $area->asignados }} asignados</strong></div>
                    @empty <x-ui.empty-state icono="ph-calendar-x" titulo="Sin jornada activa" texto="No hay asignaciones de personal para mostrar." compact /> @endforelse
                </div>
            </div>
            @endif
        </div>
    </section>
    @endif

    @if($puedeAlertas || $puedeJornadas || $puedeVisitas)
    <section class="rm-admin-dashboard__secondary" aria-label="Indicadores de seguimiento">
        @if($puedeAlertas)
        <a href="{{ route('admin.administracion.alertas') }}"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><span><strong>{{ $datos['alertas_activas'] }}</strong><small>Alertas operativas</small></span></a>
        @endif
        @if($puedeJornadas)
        <a href="{{ route('admin.administracion.jornadas') }}"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><span><strong>{{ $datos['personal_asignado'] ?? '—' }}</strong><small>Cobertura de jornada{{ $datos['personal_asignado'] === null ? ' · Sin jornada activa' : '' }}</small></span></a>
        @endif
        @if($puedeVisitas)
        <a href="{{ route('admin.administracion.visitas', ['fecha' => today()->toDateString()]) }}"><i class="ph-bold ph-door-open" aria-hidden="true"></i><span><strong>{{ $datos['visitas_hoy'] }}</strong><small>Visitas de hoy</small></span></a>
        @endif
    </section>
    @endif

    @if(($puedeOcupacion && $puedeAdmisiones) || ($puedeVisitas && $puedeActividades) || $puedeAlertas)
    <section class="rm-admin-dashboard__section" aria-labelledby="admin-operation-title">
        <div class="rm-admin-dashboard__section-heading rm-section-header"><div class="rm-section-header__main"><span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold ph-chart-bar"></i></span><h2 id="admin-operation-title" class="rm-section-header__title">Cómo funciona la operación</h2></div></div>
        <div class="rm-admin-dashboard__split rm-admin-dashboard__split--analysis">
            @if($puedeOcupacion && $puedeAdmisiones)
            <div class="rm-admin-dashboard__panel rm-admin-dashboard__analysis"><div class="rm-admin-dashboard__panel-heading"><span class="rm-admin-dashboard__chart-icon" aria-hidden="true"><i class="ph-bold ph-chart-bar"></i></span><div class="rm-admin-dashboard__chart-heading"><h3>Evolución de ocupación y admisiones</h3><small>Capacidad y admisiones formalizadas</small></div><form method="GET"><label for="periodo-admin" class="sr-only">Periodo</label><select id="periodo-admin" name="periodo" onchange="this.form.submit()">@foreach(['7d' => 'Últimos 7 días', '4w' => 'Últimas 4 semanas', '3m' => 'Últimos 3 meses', 'year' => 'Este año'] as $clave => $etiqueta)<option value="{{ $clave }}" @selected($datos['periodo'] === $clave)>{{ $etiqueta }}</option>@endforeach</select></form></div>
                @if($totalCamas > 0 || collect($historia)->sum('admisiones') > 0)<div class="rm-admin-dashboard__bar-chart" role="img" tabindex="0" aria-label="Ocupación y admisiones por periodo">@foreach($historia as $dia)<div class="rm-admin-dashboard__bar-group" title="{{ $dia['etiqueta'] }}: {{ $dia['ocupadas'] }} camas ocupadas, {{ $dia['admisiones'] }} admisiones"><span class="rm-admin-dashboard__bar rm-admin-dashboard__bar--mint" style="height: {{ 100 * $dia['ocupadas'] / $maxHistoria }}%"></span><span class="rm-admin-dashboard__bar rm-admin-dashboard__bar--taupe" style="height: {{ 100 * $dia['admisiones'] / $maxHistoria }}%"></span><small>{{ explode('–', $dia['etiqueta'])[1] ?? $dia['etiqueta'] }}</small></div>@endforeach</div><table class="sr-only"><caption>Datos de ocupación y admisiones</caption><thead><tr><th scope="col">Periodo</th><th scope="col">Camas ocupadas</th><th scope="col">Admisiones</th></tr></thead><tbody>@foreach($historia as $dia)<tr><th scope="row">{{ $dia['etiqueta'] }}</th><td>{{ $dia['ocupadas'] }}</td><td>{{ $dia['admisiones'] }}</td></tr>@endforeach</tbody></table><p class="rm-admin-dashboard__legend"><span>Ocupación</span><span>Admisiones formalizadas</span></p>
                @else <x-ui.empty-state icono="ph-chart-line-up" titulo="Sin datos disponibles" texto="Aún no hay capacidad ni admisiones para graficar." compact /> @endif
            </div>
            @endif
            @if($puedeVisitas && $puedeActividades)
            <div class="rm-admin-dashboard__panel rm-admin-dashboard__analysis"><div class="rm-admin-dashboard__panel-heading"><span class="rm-admin-dashboard__chart-icon" aria-hidden="true"><i class="ph-bold ph-calendar-check"></i></span><div class="rm-admin-dashboard__chart-heading"><h3>Dinámica residencial</h3><small>Visitas y actividades registradas</small></div><span class="rm-admin-dashboard__chart-period">Últimos 7 días</span></div>
                @if(collect($datos['dinamica'])->sum('visitas') + collect($datos['dinamica'])->sum('actividades') > 0)<div class="rm-admin-dashboard__bar-chart" role="img" tabindex="0" aria-label="Visitas y actividades de los últimos siete días">@foreach($datos['dinamica'] as $dia)<div class="rm-admin-dashboard__bar-group" title="{{ $dia['etiqueta'] }}: {{ $dia['visitas'] }} visitas, {{ $dia['actividades'] }} actividades"><span class="rm-admin-dashboard__bar rm-admin-dashboard__bar--taupe" style="height: {{ 100 * $dia['visitas'] / $maxDinamica }}%"></span><span class="rm-admin-dashboard__bar rm-admin-dashboard__bar--mint" style="height: {{ 100 * $dia['actividades'] / $maxDinamica }}%"></span><small>{{ $dia['etiqueta'] }}</small></div>@endforeach</div><table class="sr-only"><caption>Datos de visitas y actividades</caption><thead><tr><th scope="col">Día</th><th scope="col">Visitas</th><th scope="col">Actividades</th></tr></thead><tbody>@foreach($datos['dinamica'] as $dia)<tr><th scope="row">{{ $dia['etiqueta'] }}</th><td>{{ $dia['visitas'] }}</td><td>{{ $dia['actividades'] }}</td></tr>@endforeach</tbody></table><p class="rm-admin-dashboard__legend"><span>Visitas</span><span>Actividades</span></p>
                @else <x-ui.empty-state icono="ph-calendar-blank" titulo="Sin actividad registrada" texto="No hay visitas ni actividades en el periodo." compact /> @endif
            </div>
            @endif
            @if($puedeAlertas)
            <div class="rm-admin-dashboard__panel rm-admin-dashboard__alert-chart"><div class="rm-admin-dashboard__panel-heading"><span class="rm-admin-dashboard__chart-icon" aria-hidden="true"><i class="ph-bold ph-warning-circle"></i></span><div class="rm-admin-dashboard__chart-heading"><h3>Alertas por prioridad</h3><small>Situaciones operativas abiertas</small></div><span class="rm-admin-dashboard__chart-period">Actual</span></div>
                @if($datos['alertas_activas'])
                    <div class="rm-admin-dashboard__donut-layout">
                        <div class="rm-admin-dashboard__donut" style="background: conic-gradient({{ implode(', ', $segmentosAlerta) }})" role="img" aria-label="{{ $datos['alertas_activas'] }} alertas activas por prioridad"><strong>{{ $datos['alertas_activas'] }}</strong><small>activas</small></div>
                        <div class="rm-admin-dashboard__donut-legend">@foreach($prioridadesAlerta as $clave => [$etiqueta, $color])<a href="{{ route('admin.administracion.alertas', ['prioridad' => $clave]) }}"><span style="--priority-color: var({{ $color }})">{{ $etiqueta }}</span><strong>{{ $datos['alertas_prioridad'][$clave] ?? 0 }}</strong></a>@endforeach @if($otrasPrioridades > 0)<div><span style="--priority-color: var(--rm-surface-muted)">Otras prioridades</span><strong>{{ $otrasPrioridades }}</strong></div>@endif</div>
                    </div>
                @else <x-ui.empty-state icono="ph-check-circle" titulo="Sin alertas operativas" texto="No hay situaciones pendientes." compact /> @endif
            </div>
            @endif
        </div>
    </section>
    @endif

    <section class="rm-admin-dashboard__section" aria-labelledby="admin-events-title"><div class="rm-admin-dashboard__section-heading rm-section-header"><div class="rm-section-header__main"><span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold ph-clock-counter-clockwise"></i></span><h2 id="admin-events-title" class="rm-section-header__title">Movimientos recientes</h2></div></div><div class="rm-admin-dashboard__timeline">
        @forelse($datos['movimientos'] as $movimiento)
            @php
                $tipoMovimiento = strtolower($movimiento->log_name);
                $iconoMovimiento = match ($tipoMovimiento) {
                    'preadmisiones', 'admisiones' => 'ph-user-plus',
                    'visitas' => 'ph-door-open',
                    'documentos' => 'ph-file-text',
                    'alertas' => 'ph-warning-circle',
                    'jornadas' => 'ph-calendar-check',
                    default => 'ph-bed',
                };
            @endphp
            <div class="rm-admin-dashboard__timeline-item rm-admin-dashboard__timeline-item--{{ $tipoMovimiento }}">
                <span class="rm-admin-dashboard__timeline-dot" aria-hidden="true"><i class="ph-bold {{ $iconoMovimiento }}"></i></span>
                <span class="rm-admin-dashboard__timeline-content"><strong>{{ ucfirst(str_replace('_', ' ', $movimiento->log_name)) }}</strong><small>{{ $movimiento->description }}</small></span>
                <time datetime="{{ \Carbon\Carbon::parse($movimiento->created_at)->toIso8601String() }}">{{ \Carbon\Carbon::parse($movimiento->created_at)->format('d/m H:i') }}</time>
            </div>
        @empty <x-ui.empty-state icono="ph-clock" titulo="Sin movimientos recientes" texto="Aún no hay actividad operativa registrada." compact /> @endforelse
    </div></section>
</div>
</x-sistema-layout>
