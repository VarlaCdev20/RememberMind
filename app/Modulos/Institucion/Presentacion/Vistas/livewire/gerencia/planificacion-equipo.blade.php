@inject('visibilidad', 'App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion')
@php($puedeVerCobertura = $visibilidad->puedeVerRuta('admin.cobertura-equipo.index'))
<div class="rm-management-workspace rm-planning" x-data
     @planificacion-detalle-seleccionado.window="$nextTick(() => { const panel = $refs.detalle; panel?.focus({preventScroll:true}); if (panel && panel.getBoundingClientRect().top > innerHeight * .75) panel.scrollIntoView({block:'start'}); })"
     @planificacion-detalle-cerrado.window="$nextTick(() => Array.from($el.querySelectorAll('[data-asignacion]')).find(button => button.dataset.asignacion === $event.detail.codigo && button.getClientRects().length)?.focus())">
    <x-ui.collection-header title="Planificación del equipo" subtitle="Organiza las asignaciones por fecha, área y turno" icon="ph-calendar-check">
        <x-slot:actions>
            @include('reportes::paginas.gerencia.partials.exportar-vista', ['origenReporte' => 'planificacion', 'parametrosReporte' => ['fecha' => $fechaSeleccionada, 'periodo' => $vistaCalendario, 'area' => $filtroArea ?: null, 'turno' => $turnos->firstWhere('nombre', $filtroTurno)?->getKey()]])
            @if($puedeVerCobertura)
                <a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index', ['fecha'=>$fechaSeleccionada, 'area'=>$filtroArea ?: null, 'turno'=>$turnos->firstWhere('nombre', $filtroTurno)?->getKey()]) }}" data-rm-action-icon aria-label="Ver cobertura"><i class="ph-bold ph-users-three" aria-hidden="true"></i><span>Ver cobertura</span></a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('admin.avisos-planificacion.index'))<a class="rm-btn-secondary" href="{{ route('admin.avisos-planificacion.index') }}" data-rm-action-icon aria-label="Ver avisos"><i class="ph-bold ph-bell" aria-hidden="true"></i><span>Ver avisos</span></a>@endif
            @can('turnos.asignar')<button class="rm-btn-primary" type="button" wire:click="abrirNuevaAsignacion" data-rm-action-icon aria-label="Planificar asignación"><i class="ph-bold ph-plus" aria-hidden="true"></i><span>Planificar asignación</span></button>@endcan
        </x-slot:actions>
    </x-ui.collection-header>

    <section class="rm-card rm-planning__controls" aria-label="Controles de planificación">
        <div class="rm-planning__toolbar">
            <div class="rm-planning__period">
                <div class="rm-planning__navigation" role="group" aria-label="Cambiar período">
                    <button type="button" class="rm-btn-secondary" wire:click="moverPeriodo(-1)" aria-label="Período anterior"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></button>
                    <button type="button" class="rm-btn-secondary" wire:click="irHoy">Hoy</button>
                    <button type="button" class="rm-btn-secondary" wire:click="moverPeriodo(1)" aria-label="Período siguiente"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button>
                </div>
                <h2 aria-live="polite">{{ $datosCalendario['titulo'] ?? 'Período seleccionado' }}</h2>
            </div>
            <x-ui.collection-view-switch class="rm-planning__views" mode="livewire" change-method="cambiarVista" :value="$vistaCalendario" label="Vista del calendario"
                :options="[['value'=>'semana','label'=>'Semana','icon'=>'ph-calendar-blank'],['value'=>'mes','label'=>'Mes','icon'=>'ph-calendar'],['value'=>'dia','label'=>'Lista','icon'=>'ph-list-bullets']]" />
        </div>
        <div class="rm-planning__filter-row">
            <x-ui.filter-search model="busqueda" input-id="planificacion-buscar" label="Buscar trabajador" placeholder="Nombre o correo" :show-clear="false" />
            <div class="rm-planning__filter-actions">
                <x-ui.field class="rm-planning__team-filter" label="Equipo" for="planificacion-equipo">
                    <select id="planificacion-equipo" class="rm-input" wire:change="cambiarTipoPlanilla($event.target.value)">
                        @foreach(['general'=>'Todo el equipo','administrativo'=>'Administrativo','enfermeria'=>'Enfermería'] as $codigo => $nombre)
                            <option value="{{ $codigo }}" @selected($tipoPlanilla === $codigo)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <div class="rm-planning__filter-menu" x-data="{ openMenu: false }" @click.outside="openMenu = false" @keydown.escape.stop="openMenu = false; $refs.trigger.focus()">
                    <x-ui.action-button variant="secondary" icono="ph-sliders-horizontal" x-ref="trigger" @click="openMenu = !openMenu" x-bind:aria-expanded="openMenu.toString()" aria-controls="planificacion-filtros">
                        Filtros @if($filtroArea !== '' || $filtroTurno !== '')<span class="rm-badge">{{ (int) ($filtroArea !== '') + (int) ($filtroTurno !== '') }}</span>@endif
                    </x-ui.action-button>
                    <div id="planificacion-filtros" class="rm-planning__filters" role="region" aria-label="Filtros de planificación" x-show="openMenu" x-cloak>
                        <x-ui.field label="Área" for="planificacion-area"><select id="planificacion-area" class="rm-input" wire:model.live="filtroArea"><option value="">Todas las áreas</option>@foreach($areas as $area)<option value="{{ $area->getKey() }}">{{ $area->nombre }}</option>@endforeach</select></x-ui.field>
                        <x-ui.field label="Horario" for="planificacion-turno">
                            <select id="planificacion-turno" class="rm-input" wire:model.live="filtroTurno">
                                <option value="">Todos los horarios</option>
                                @if($filtroTurno !== '' && !$turnosFiltro->flatten(1)->contains('valor', $filtroTurno))
                                    <option value="{{ $filtroTurno }}">{{ $filtroTurno }} · Sin coincidencias</option>
                                @endif
                                @foreach($turnosFiltro as $grupo => $opciones)
                                    <optgroup label="{{ $grupo }}">
                                        @foreach($opciones as $opcion)<option value="{{ $opcion['valor'] }}">{{ $opcion['horario'] }} · {{ $opcion['nombre'] }}</option>@endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @if($turnosFiltro->isEmpty())<p class="rm-help">Sin horarios para esta consulta.</p>@endif
                        </x-ui.field>
                    </div>
                </div>
                @if($busqueda !== '' || $filtroArea !== '' || $filtroTurno !== '')
                    <button type="button" class="rm-btn-secondary rm-planning__clear" wire:click="limpiarFiltros" aria-label="Limpiar búsqueda y filtros" title="Limpiar búsqueda y filtros"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i></button>
                @endif
            </div>
        </div>
        @if($filtroArea !== '' || $filtroTurno !== '')
            <p class="rm-planning__active-filters" role="status">{{ $areas->firstWhere('cod_area', $filtroArea)?->nombre }}{{ $filtroArea !== '' && $filtroTurno !== '' ? ' · ' : '' }}{{ $filtroTurno }}</p>
        @endif
    </section>

    <div class="rm-planning__workspace {{ $vistaCalendario === 'semana' && $detalleAgenda ? 'has-detail' : 'is-calendar-only' }}">
        <section class="rm-card rm-planning__calendar" aria-label="Calendario institucional" aria-busy="false" wire:loading.class="rm-planning__loading">
            @if($vistaCalendario==='semana')
                @if($filasAgenda->isEmpty())<x-ui.empty-state title="Sin asignaciones en este período" description="Prueba otra fecha o ajusta los filtros para consultar la planificación." />@else
                <div class="rm-planning__week">
                    <table class="rm-planning__grid"><caption class="sr-only">Asignaciones de la semana, por día y turno registrado. No acredita asistencia.</caption><thead><tr><th scope="col"><span class="sr-only">Turno y horario</span></th>@foreach($datosCalendario['dias'] as $dia)<th scope="col"><span>{{ $dia['nombre'] }} <b class="{{ $dia['es_hoy'] ? 'is-today' : '' }}" @if($dia['es_hoy']) aria-current="date" @endif>{{ $dia['numero'] }}</b></span><small>{{ \Carbon\Carbon::parse($dia['fecha'])->locale('es')->translatedFormat('F') }}</small></th>@endforeach</tr></thead><tbody>
                    @foreach($filasAgenda as $fila)<tr><th scope="row"><strong>{{ $fila['nombre'] }}</strong><span>{{ $fila['inicio'] }}–{{ $fila['fin'] }}</span>@if($fila['fin'] <= $fila['inicio'])<small>+1 día</small>@endif</th>
                        @foreach($datosCalendario['dias'] as $dia)<td><div class="rm-planning__cell">@forelse($dia['eventos']->filter(fn ($evento) => $evento['asignacion']->cod_turno === $fila['codigo'])->take(3) as $evento)
                            @include('institucion::livewire.gerencia.partials.planificacion-evento', ['origen'=>'semana'])
                        @empty<span class="rm-planning__empty" aria-label="Sin asignaciones registradas">—</span>@endforelse
                        @if(($restantes = $dia['eventos']->filter(fn ($evento) => $evento['asignacion']->cod_turno === $fila['codigo'])->count() - 3) > 0)<button type="button" class="rm-btn-secondary" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')">Ver {{ $restantes }} más</button>@endif</div></td>@endforeach
                    </tr>@endforeach</tbody></table>
                </div>
                <div class="rm-planning__agenda" aria-label="Agenda semanal por día">
                    @foreach($datosCalendario['dias'] as $dia)<section class="rm-planning__day"><h3>{{ $dia['nombre'] }} {{ $dia['numero'] }} <small>{{ \Carbon\Carbon::parse($dia['fecha'])->locale('es')->translatedFormat('F') }}</small>@if($dia['es_hoy'])<span class="rm-planning__today">Hoy</span>@endif</h3><div class="rm-planning__day-events">@forelse($dia['eventos']->take(3) as $evento)
                        @include('institucion::livewire.gerencia.partials.planificacion-evento', ['origen'=>'agenda'])
                    @empty<p class="rm-help">Sin asignaciones registradas.</p>@endforelse
                    @if($dia['eventos']->count() > 3)<button type="button" class="rm-btn-secondary" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')">Ver {{ $dia['eventos']->count() - 3 }} más</button>@endif</div></section>@endforeach
                </div>
                @endif
            @elseif($vistaCalendario==='mes')
                <div class="rm-planning__month" aria-label="Asignaciones por fecha">@foreach($datosCalendario['dias'] as $dia)<button type="button" class="rm-planning__month-day {{ $dia['es_mes'] ? '' : 'is-other-month' }}" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')" aria-label="Consultar el {{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}"><strong>{{ $dia['numero'] }}</strong><span>{{ $dia['asignados'] }} personas con asignación</span></button>@endforeach</div>
            @elseif($vistaCalendario==='dia')
                <div class="rm-table-scroll rm-schedule-table-wrap"><table class="rm-table rm-schedule-table"><caption class="sr-only">Asignaciones y trabajadores de la fecha seleccionada</caption>
                    <thead><tr><th scope="col">Trabajador</th><th scope="col">Turno y horario</th><th scope="col">Área</th><th scope="col">Situación</th><th scope="col">Acción</th></tr></thead>
                    <tbody>@forelse($datosCalendario['filas'] ?? [] as $fila)
                        <tr>
                            <th scope="row" data-label="Trabajador"><strong>{{ $fila['usuario']->name }}</strong><span class="rm-help">{{ $fila['rol']['label'] }}</span></th>
                            <td data-label="Turno y horario">
                                @if($fila['asignacion']?->turno)
                                    <strong>{{ $fila['asignacion']->turno->nombre }}</strong><span class="rm-help">{{ substr($fila['asignacion']->turno->hora_inicio, 0, 5) }}–{{ substr($fila['asignacion']->turno->hora_fin, 0, 5) }}{{ $fila['asignacion']->turno->hora_fin <= $fila['asignacion']->turno->hora_inicio ? ' (+1 día)' : '' }}</span>
                                @else<span class="rm-help">Sin asignación en esta fecha</span>@endif
                            </td>
                            <td data-label="Área">{{ $fila['asignacion']?->area?->nombre ?: '—' }}</td>
                            <td data-label="Situación"><x-ui.status-badge :estado="$fila['estado']" /></td>
                            <td data-label="Acción">@can('turnos.asignar')<x-ui.action-button variant="secondary" size="sm" icono="ph-calendar-check" loading="seleccionarUsuario" wire:click="seleccionarUsuario('{{ $fila['usuario']->getKey() }}', {{ $fila['asignacion'] ? 'false' : 'true' }})">{{ $fila['asignacion'] ? 'Ver horarios' : 'Asignar' }}</x-ui.action-button>@endcan</td>
                        </tr>
                    @empty<tr><td colspan="5"><x-ui.empty-state compact title="Sin trabajadores en esta consulta" description="Revisa la búsqueda o los filtros del equipo." /></td></tr>@endforelse</tbody>
                </table></div>
            @else<p class="rm-help">Selecciona Semana, Mes o Lista para consultar las asignaciones.</p>@endif
        </section>

        @if($vistaCalendario === 'semana' && $detalleAgenda)
        <aside class="rm-card rm-planning__detail" x-ref="detalle" tabindex="-1" aria-labelledby="planificacion-detalle-titulo">
            <div class="rm-planning__detail-heading"><h2 id="planificacion-detalle-titulo">Detalle de la asignación</h2>@if($detalleAgenda)<button type="button" class="rm-btn-secondary" wire:click="cerrarDetalleAsignacion" aria-label="Cerrar detalle"><i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif</div>
                <span class="rm-planning__registered"><i class="ph-bold ph-check-circle" aria-hidden="true"></i>Registrada</span>
                <div class="rm-planning__person"><span class="rm-planning__avatar" aria-hidden="true">{{ mb_substr($detalleAgenda['titulo'],0,1) }}</span><div><h3>{{ $detalleAgenda['titulo'] }}</h3><p>{{ $detalleAgenda['rol']['label'] }}</p></div></div>
                <dl class="rm-planning__facts"><div><dt>Área</dt><dd>{{ $detalleAgenda['asignacion']->area?->nombre ?: 'Sin área registrada' }}</dd></div><div><dt>Fecha</dt><dd>{{ \Carbon\Carbon::parse($detalleAgenda['fecha'])->locale('es')->translatedFormat('l d \d\e F Y') }}</dd></div><div><dt>Turno</dt><dd>{{ $detalleAgenda['asignacion']->turno?->nombre ?: 'Sin turno registrado' }}</dd></div><div><dt>Horario</dt><dd>{{ $detalleAgenda['hora'] }}</dd></div></dl>
                @if($detalleAgenda['estado']==='CONFLICTO')<p class="rm-error"><i class="ph-bold ph-warning" aria-hidden="true"></i> Solapamiento registrado. Revisa los horarios antes de modificar la asignación.</p>@elseif($detalleAgenda['estado']==='SUSPENDIDO')<p class="rm-error">Revisa el estado del trabajador antes de gestionar su horario.</p>@endif
                <p class="rm-planning__notice"><i class="ph-bold ph-info" aria-hidden="true"></i>Asignación registrada; no confirma asistencia.</p>
                <div class="rm-planning__detail-actions">@can('turnos.asignar')@if($detalleAgenda['asignacion']->cod_usuario)<button type="button" class="rm-btn-primary" wire:click="seleccionarUsuario('{{ $detalleAgenda['asignacion']->cod_usuario }}', false)">Gestionar horarios</button>@endif@endcan @if($puedeVerCobertura)<a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index', ['fecha'=>$detalleAgenda['fecha'],'area'=>$detalleAgenda['asignacion']->cod_area,'turno'=>$detalleAgenda['asignacion']->cod_turno]) }}">Ver cobertura del área</a>@endif</div>
        </aside>
        @endif
    </div>
    <p class="rm-help" wire:loading role="status">Actualizando la consulta…</p>

    @if($modalAbierto)
    <x-ui.modal-livewire id="planificacion-nueva" class="rm-staff-action-dialog rm-planning__assignment" wire:model="modalAbierto" title="Planificar una asignación" subtitle="Elige al trabajador, configura el horario y revisa las fechas" max-width="5xl" close-method="cerrarModal" :dismiss-on-escape="false" :show-validation="false">
        @if($usuarioSeleccionadoData)<livewire:identidad.personal-institucional-horarios :usuario-id="$usuarioSeleccionadoData->getKey()" :abrir-formulario-inicial="$crearHorarioSeleccionado" wire:key="gerencia-planificar-{{ $usuarioSeleccionadoData->getKey() }}-{{ $crearHorarioSeleccionado ? 'alta' : 'consulta' }}" />
        @else
            <x-ui.field label="¿A quién asignar el horario?" for="planificar-persona">
                <x-ui.filter-search model="busquedaModal" input-id="planificar-persona" label="" placeholder="Busca por nombre, apellido, correo o rol" :show-clear="false" />
            </x-ui.field>
            <div class="rm-planning__worker-options" wire:loading.class="opacity-50" wire:target="busquedaModal">
                @forelse($personalModal->take(8) as $usuario)
                    <button type="button" class="rm-btn-secondary" wire:key="planificar-trabajador-{{ $usuario->getKey() }}" wire:click="seleccionarUsuario('{{ $usuario->getKey() }}')" wire:loading.attr="disabled" wire:target="seleccionarUsuario">
                        <span class="rm-planning__avatar" aria-hidden="true">{{ mb_substr($usuario->name, 0, 1) }}</span>
                        <span><strong>{{ $usuario->name }}</strong><small>{{ $usuario->roles->pluck('name')->join(' · ') }} · {{ $usuario->correo }}</small></span>
                        <i class="ph-bold ph-caret-right" aria-hidden="true"></i>
                    </button>
                @empty
                    <p class="rm-help" role="status">{{ mb_strlen(trim($busquedaModal)) < 2 ? 'Escribe al menos 2 caracteres para buscar un trabajador.' : 'No encontramos coincidencias. Prueba con otro nombre, correo o rol.' }}</p>
                @endforelse
            </div>
            @if($personalModal->count() > 8)<p class="rm-help" role="status">Mostrando 8 coincidencias. Añade un apellido o correo para precisar la búsqueda.</p>@endif
        @endif
    </x-ui.modal-livewire>
    @endif
</div>
