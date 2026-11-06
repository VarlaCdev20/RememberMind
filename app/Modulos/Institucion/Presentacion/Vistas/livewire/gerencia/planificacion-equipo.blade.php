<div class="rm-management-workspace rm-planning" x-data
     @planificacion-detalle-seleccionado.window="$nextTick(() => { const panel = $refs.detalle; panel?.focus({preventScroll:true}); if (panel && panel.getBoundingClientRect().top > innerHeight * .75) panel.scrollIntoView({block:'start'}); })"
     @planificacion-detalle-cerrado.window="$nextTick(() => Array.from($el.querySelectorAll('[data-asignacion]')).find(button => button.dataset.asignacion === $event.detail.codigo && button.getClientRects().length)?.focus())">
    <x-ui.collection-header title="Planificación del equipo" subtitle="Organiza las asignaciones por fecha, área y turno" icon="ph-calendar-check">
        <x-slot:actions>
            <a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index', ['fecha'=>$fechaSeleccionada, 'area'=>$filtroArea ?: null]) }}">Ver cobertura</a>
            @if(\Illuminate\Support\Facades\Route::has('admin.avisos-planificacion.index'))<a class="rm-btn-secondary" href="{{ route('admin.avisos-planificacion.index') }}">Ver avisos</a>@endif
            @can('turnos.asignar')<button class="rm-btn-primary" type="button" wire:click="abrirNuevaAsignacion"><i class="ph-bold ph-plus" aria-hidden="true"></i>Planificar asignación</button>@endcan
        </x-slot:actions>
    </x-ui.collection-header>

    <x-ui.filter-bar class="rm-planning__filters">
        <x-ui.field label="Buscar trabajador" for="planificacion-buscar"><div class="rm-filter-search"><span class="rm-filter-search-icon"><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i></span><input id="planificacion-buscar" class="rm-input" type="search" wire:model.live.debounce.300ms="busqueda" placeholder="Nombre o correo" autocomplete="off"></div></x-ui.field>
        <x-ui.field label="Área" for="planificacion-area"><select id="planificacion-area" class="rm-input" wire:model.live="filtroArea"><option value="">Todas las áreas</option>@foreach($areas as $area)<option value="{{ $area->getKey() }}">{{ $area->nombre }}</option>@endforeach</select></x-ui.field>
        <x-ui.field label="Turno" for="planificacion-turno"><select id="planificacion-turno" class="rm-input" wire:model.live="filtroTurno"><option value="">Todos los turnos</option>@foreach($turnos as $turno)<option value="{{ $turno->nombre }}">{{ $turno->nombre }}</option>@endforeach</select></x-ui.field>
        <button class="rm-btn-secondary rm-planning__clear" type="button" wire:click="limpiarFiltros"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i>Limpiar</button>
    </x-ui.filter-bar>

    <section class="rm-card rm-planning__toolbar" aria-label="Período y presentación del calendario">
        <div class="rm-planning__period"><button type="button" class="rm-btn-secondary" wire:click="moverPeriodo(-1)" aria-label="Período anterior"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></button><button type="button" class="rm-btn-secondary" wire:click="irHoy">Hoy</button><button type="button" class="rm-btn-secondary" wire:click="moverPeriodo(1)" aria-label="Período siguiente"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button><h2 aria-live="polite">{{ $datosCalendario['titulo'] ?? 'Período seleccionado' }}</h2></div>
        <div class="rm-planning__switch" aria-label="Vista">@foreach(['semana'=>'Semana','mes'=>'Mes','dia'=>'Lista'] as $clave=>$nombre)<button type="button" class="rm-btn-secondary" wire:click="cambiarVista('{{ $clave }}')" aria-pressed="{{ $vistaCalendario===$clave ? 'true' : 'false' }}">{{ $nombre }}</button>@endforeach</div>
        <div class="rm-planning__switch" aria-label="Equipo de la planificación"><button type="button" class="rm-btn-secondary" wire:click="cambiarTipoPlanilla('general')" aria-pressed="{{ $tipoPlanilla==='general' ? 'true' : 'false' }}">Todo el equipo</button><button type="button" class="rm-btn-secondary" wire:click="cambiarTipoPlanilla('enfermeria')" aria-pressed="{{ $tipoPlanilla==='enfermeria' ? 'true' : 'false' }}">Enfermería</button></div>
    </section>

    <div class="rm-planning__workspace {{ $vistaCalendario === 'semana' ? '' : 'is-calendar-only' }}">
        <section class="rm-card rm-planning__calendar" aria-label="Calendario institucional" aria-busy="false" wire:loading.class="rm-planning__loading">
            @if($vistaCalendario==='semana')
                @if($filasAgenda->isEmpty())<x-ui.empty-state title="Sin turnos en esta consulta" description="Prueba otro turno o período. No se infiere una falta de cobertura." />@else
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
                <div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Asignaciones y trabajadores de la fecha seleccionada</caption><thead><tr><th scope="col">Trabajador</th><th scope="col">Turno registrado</th><th scope="col">Área</th><th scope="col">Acción</th></tr></thead><tbody>@forelse($datosCalendario['filas'] ?? [] as $fila)<tr><th scope="row">{{ $fila['usuario']->name }}</th><td>{{ $fila['asignacion']?->turno?->nombre ?: 'Sin turno registrado' }}</td><td>{{ $fila['asignacion']?->area?->nombre ?: 'Sin área registrada' }}</td><td>@can('turnos.asignar')<button type="button" class="rm-btn-secondary" wire:click="seleccionarUsuario('{{ $fila['usuario']->getKey() }}')">Ver horarios</button>@endcan</td></tr>@empty<tr><td colspan="4">Sin trabajadores para esta consulta.</td></tr>@endforelse</tbody></table></div>
            @else<p class="rm-help">Selecciona Semana, Mes o Lista para consultar las asignaciones.</p>@endif
        </section>

        @if($vistaCalendario === 'semana')
        <aside class="rm-card rm-planning__detail" x-ref="detalle" tabindex="-1" aria-labelledby="planificacion-detalle-titulo">
            <div class="rm-planning__detail-heading"><h2 id="planificacion-detalle-titulo">Detalle de la asignación</h2>@if($detalleAgenda)<button type="button" class="rm-btn-secondary" wire:click="cerrarDetalleAsignacion" aria-label="Cerrar detalle"><i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif</div>
            @if($detalleAgenda)
                <span class="rm-planning__registered"><i class="ph-bold ph-check-circle" aria-hidden="true"></i>Registrada</span>
                <div class="rm-planning__person"><span class="rm-planning__avatar" aria-hidden="true">{{ mb_substr($detalleAgenda['titulo'],0,1) }}</span><div><h3>{{ $detalleAgenda['titulo'] }}</h3><p>{{ $detalleAgenda['rol']['label'] }}</p></div></div>
                <dl class="rm-planning__facts"><div><dt>Área</dt><dd>{{ $detalleAgenda['asignacion']->area?->nombre ?: 'Sin área registrada' }}</dd></div><div><dt>Fecha</dt><dd>{{ \Carbon\Carbon::parse($detalleAgenda['fecha'])->locale('es')->translatedFormat('l d \d\e F Y') }}</dd></div><div><dt>Turno</dt><dd>{{ $detalleAgenda['asignacion']->turno?->nombre ?: 'Sin turno registrado' }}</dd></div><div><dt>Horario</dt><dd>{{ $detalleAgenda['hora'] }}</dd></div></dl>
                @if($detalleAgenda['estado']==='CONFLICTO')<p class="rm-error"><i class="ph-bold ph-warning" aria-hidden="true"></i> Solapamiento registrado. Revisa los horarios antes de modificar la asignación.</p>@elseif($detalleAgenda['estado']==='SUSPENDIDO')<p class="rm-error">Revisa el estado del trabajador antes de gestionar su horario.</p>@endif
                <p class="rm-planning__notice"><i class="ph-bold ph-info" aria-hidden="true"></i>Asignación registrada; no confirma asistencia.</p>
                <div class="rm-planning__detail-actions">@can('turnos.asignar')@if($detalleAgenda['asignacion']->cod_usuario)<button type="button" class="rm-btn-primary" wire:click="seleccionarUsuario('{{ $detalleAgenda['asignacion']->cod_usuario }}')">Gestionar horarios</button>@endif@endcan<a class="rm-btn-secondary" href="{{ route('admin.cobertura-equipo.index', ['fecha'=>$detalleAgenda['fecha'],'area'=>$detalleAgenda['asignacion']->cod_area]) }}">Ver cobertura del área</a></div>
            @else
                <div class="rm-planning__no-selection"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><h3>Selecciona una tarjeta</h3><p>Consulta su área, fecha y horario aquí, sin modificar la asignación.</p></div>
                <p class="rm-planning__notice"><i class="ph-bold ph-info" aria-hidden="true"></i>Solo asignaciones guardadas. No se infiere asistencia ni suficiencia de personal.</p>
            @endif
        </aside>
        @endif
    </div>
    <div class="rm-planning__legend"><span><i class="rm-planning__swatch" aria-hidden="true"></i>Asignación registrada</span><span><i class="rm-planning__swatch is-selected" aria-hidden="true"></i>Selección actual</span><p class="rm-help" wire:loading role="status">Actualizando la consulta…</p></div>

    @if($modalAbierto)
    <x-ui.modal-livewire id="planificacion-nueva" wire:model="modalAbierto" title="Planificar una asignación" subtitle="Selecciona al trabajador y revisa área, turno y fechas" max-width="lg" close-method="cerrarModal" :dismiss-on-escape="false">
        @if($usuarioSeleccionadoData)<h3 class="rm-section-title">{{ $usuarioSeleccionadoData->name }}</h3><livewire:identidad.personal-institucional-horarios :usuario-id="$usuarioSeleccionadoData->getKey()" :abrir-formulario-inicial="!($detalleAgenda && $detalleAgenda['asignacion']->cod_usuario === $usuarioSeleccionadoData->getKey())" wire:key="gerencia-planificar-{{ $usuarioSeleccionadoData->getKey() }}" />
        @else<x-ui.field label="Buscar trabajador para la asignación" for="planificar-persona"><input id="planificar-persona" class="rm-input" type="search" wire:model.live.debounce.300ms="busquedaModal"></x-ui.field><div class="space-y-3 mt-3">@forelse($personalModal as $usuario)<button type="button" class="rm-btn-secondary w-full text-left" wire:click="seleccionarUsuario('{{ $usuario->getKey() }}')">{{ $usuario->name }}</button>@empty<p class="rm-help">Sin coincidencias.</p>@endforelse</div>@endif
    </x-ui.modal-livewire>
    @endif
</div>
