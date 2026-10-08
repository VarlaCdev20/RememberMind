<x-sistema-layout>
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')
@php
    $parametrosListadoResidentes = array_filter(array_merge($filtros, ['vista' => $vistaResidentes, 'por_pagina' => $porPagina]), fn ($valor, $clave) => filled($valor) && !in_array($clave, ['residente', 'panel_tab']), ARRAY_FILTER_USE_BOTH);
    $enlace = fn (array $cambios = []) => route('admin.administracion.residentes', array_filter(array_replace($parametrosListadoResidentes, array_key_exists('residente', $cambios) ? $cambios : ['page' => null] + $cambios), fn ($valor) => filled($valor)));
    $filtrosVisibles = ['search' => 'Búsqueda', 'estado' => 'Estado', 'piso' => 'Piso', 'cod_habitacion' => 'Habitación', 'alojamiento' => 'Alojamiento'];
    $hayFiltros = collect($filtrosVisibles)->keys()->contains(fn ($clave) => filled($filtros[$clave] ?? ''));
    $total = $resumenResidentes['total'];
    $porcentajeConCama = $total ? round($resumenResidentes['con_cama'] / $total * 100) : 0;
    $maxPiso = max(1, (int) $distribucionPisos->max('total'));
    $vistas = ['tarjetas' => ['Tarjetas', 'ph-squares-four'], 'lista' => ['Lista', 'ph-list-bullets'], 'tabla' => ['Tabla', 'ph-table'], 'camas' => ['Camas', 'ph-bed']];
@endphp
<div class="rm-residents" x-data="rmResidentes()" :aria-busy="loading" x-on:livewire:navigated.window="loading = false" x-on:alojamiento-residente-actualizado.window="actualizar()">
    <x-ui.collection-header title="Residentes" subtitle="Personas, alojamiento y red de apoyo en un mismo lugar." icon="ph-users-three" eyebrow="Administración / Vida en la residencia" :date="now()->locale('es')">
        <x-slot:actions>
            <button type="button" class="rm-btn-secondary" @click="estadisticas = !estadisticas" :aria-expanded="estadisticas" aria-controls="residentes-estadisticas"><i class="ph-bold ph-chart-bar" aria-hidden="true"></i> <span x-text="estadisticas ? 'Ocultar gráficos' : 'Mostrar gráficos'"></span></button>
            <button type="button" class="rm-btn-icon" @click="actualizar()" :disabled="loading" aria-label="Actualizar residentes" title="Actualizar datos"><i class="ph-bold ph-arrow-clockwise" aria-hidden="true"></i></button>
            @if($visibilidadNavegacion->puedeVerRuta('admin.administracion.admisiones'))
                <a wire:navigate href="{{ route('admin.administracion.admisiones', ['tab' => 'preparacion']) }}" class="rm-btn-secondary"><i class="ph-bold ph-user-plus" aria-hidden="true"></i> Ingresos por formalizar</a>
            @endif
        </x-slot:actions>
    </x-ui.collection-header>

    <section class="rm-residents-overview" aria-label="Resumen de residentes">
        @foreach([['Residentes', $total, 'ph-users-three', 'Personas que coinciden con los filtros', ['alojamiento' => null]], ['Con cama vigente', $resumenResidentes['con_cama'], 'ph-bed', 'Ocupación activa registrada', ['alojamiento' => 'con_cama']], ['Sin cama vigente', $resumenResidentes['sin_cama'], 'ph-map-pin-line', 'Revisar su trayectoria de alojamiento', ['alojamiento' => 'sin_cama']]] as [$titulo, $cantidad, $icono, $descripcion, $cambios])
            <a wire:navigate href="{{ $enlace($cambios) }}" class="rm-residents-metric"><span><i class="ph-bold {{ $icono }}" aria-hidden="true"></i></span><div><strong>{{ $cantidad }}</strong><h2>{{ $titulo }}</h2><p>{{ $descripcion }}</p></div><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
        @endforeach
    </section>

    <section class="rm-residents-analytics" id="residentes-estadisticas" x-show="estadisticas" x-transition.opacity aria-label="Distribución de residentes y alojamiento">
        <article class="rm-residents-chart">
            <header><div><h2><i class="ph-bold ph-chart-donut" aria-hidden="true"></i> Alojamiento actual</h2><p>Distribución de los {{ $total }} residentes filtrados</p></div></header>
            <div class="rm-residents-chart__content">
                <div class="rm-residents-donut" role="img" aria-label="{{ $resumenResidentes['con_cama'] }} con cama vigente y {{ $resumenResidentes['sin_cama'] }} sin cama vigente">
                    <svg viewBox="0 0 120 120" aria-hidden="true"><circle class="rm-residents-donut__track" cx="60" cy="60" r="48"/><circle class="rm-residents-donut__value" cx="60" cy="60" r="48" pathLength="100" stroke-dasharray="{{ $porcentajeConCama }} 100"/></svg>
                    <span><strong>{{ $porcentajeConCama }}%</strong><small>con cama</small></span>
                </div>
                <div class="rm-residents-legend">
                    <a wire:navigate href="{{ $enlace(['alojamiento' => 'con_cama']) }}"><i class="ph-bold ph-bed" aria-hidden="true"></i> Con cama <strong>{{ $resumenResidentes['con_cama'] }}</strong></a>
                    <a wire:navigate href="{{ $enlace(['alojamiento' => 'sin_cama']) }}"><i class="ph-bold ph-map-pin-line" aria-hidden="true"></i> Sin cama <strong>{{ $resumenResidentes['sin_cama'] }}</strong></a>
                    <a wire:navigate href="{{ $enlace(['vista' => 'camas', 'alojamiento' => null, 'search' => null, 'estado' => null]) }}"><i class="ph-bold ph-door-open" aria-hidden="true"></i> Camas disponibles <strong>{{ $resumenResidentes['camas_disponibles'] }}</strong></a>
                </div>
            </div>
        </article>
        <article class="rm-residents-chart">
            <header><div><h2><i class="ph-bold ph-buildings" aria-hidden="true"></i> Residentes por piso</h2><p>Selecciona un piso para explorar su alojamiento</p></div></header>
            <div class="rm-residents-bars">
                @forelse($distribucionPisos as $planta)
                    @if(filled($planta->piso))
                    <a wire:navigate href="{{ $enlace(['piso' => $planta->piso, 'cod_habitacion' => null, 'vista' => 'camas']) }}"><span>Piso {{ $planta->piso }}</span><strong>{{ $planta->total }}</strong><div class="rm-residents-bar" aria-hidden="true"><span style="width: {{ round($planta->total / $maxPiso * 100) }}%"></span></div></a>
                    @else
                    <a wire:navigate href="{{ $enlace(['piso' => '__sin_piso__', 'cod_habitacion' => null, 'vista' => 'camas']) }}"><span>Piso sin registrar</span><strong>{{ $planta->total }}</strong><div class="rm-residents-bar" aria-hidden="true"><span style="width: {{ round($planta->total / $maxPiso * 100) }}%"></span></div></a>
                    @endif
                @empty
                    <p>No hay residentes que coincidan con estos filtros.</p>
                @endforelse
            </div>
        </article>
    </section>

    @if($resumenResidentes['sin_admision'] || $resumenResidentes['sin_responsable'])
        <div class="rm-residents-guidance" role="note"><i class="ph-bold ph-info" aria-hidden="true"></i><p>Datos por revisar en este resultado: <strong>{{ $resumenResidentes['sin_admision'] }} sin admisión vinculada</strong> y <strong>{{ $resumenResidentes['sin_responsable'] }} sin responsable principal</strong>. Consulta el detalle antes de realizar cambios.</p></div>
    @endif

    <form class="rm-filter-bar rm-residents-filters" x-ref="filtros" action="{{ route('admin.administracion.residentes') }}" method="GET" role="search" aria-label="Filtrar residentes" @submit.prevent="filtrar()">
        <input type="hidden" name="vista" value="{{ $vistaResidentes }}"><input type="hidden" name="por_pagina" value="{{ $porPagina }}">
        <div class="rm-residents-search"><label for="residentes-buscar">Buscar residentes o ubicación</label><div><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i><input id="residentes-buscar" name="search" type="search" maxlength="100" value="{{ $filtros['search'] ?? '' }}" placeholder="Nombre, documento, código, habitación o cama"></div></div>
        <div class="rm-residents-filter-grid">
            <div><label for="residentes-estado">Estado</label><x-ui.selector name="estado" id="residentes-estado" label="Estado de residente"><option value="">Todos los estados</option>@foreach($estadosResidentes as $estado)<option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ str_replace('_', ' ', $estado) }}</option>@endforeach</x-ui.selector></div>
            <div><label for="residentes-piso">Piso</label><x-ui.selector name="piso" id="residentes-piso" label="Piso"><option value="">Todos los pisos</option><option value="__sin_piso__" @selected(($filtros['piso'] ?? '') === '__sin_piso__')>Piso sin registrar</option>@foreach($pisosDisponibles as $piso)<option value="{{ $piso }}" @selected((string) ($filtros['piso'] ?? '') === (string) $piso)>Piso {{ $piso }}</option>@endforeach</x-ui.selector></div>
            <div><label for="residentes-habitacion">Habitación</label><x-ui.selector name="cod_habitacion" id="residentes-habitacion" label="Habitación"><option value="">Todas las habitaciones</option>@foreach($habitacionesDisponibles as $habitacion)<option value="{{ $habitacion->cod_habitacion }}" @selected(($filtros['cod_habitacion'] ?? '') === $habitacion->cod_habitacion)>{{ $habitacion->codigo }}{{ filled($habitacion->piso) ? ' · Piso '.$habitacion->piso : '' }}</option>@endforeach</x-ui.selector></div>
            <div><label for="residentes-alojamiento">Alojamiento</label><x-ui.selector name="alojamiento" id="residentes-alojamiento" label="Alojamiento"><option value="">Con y sin cama</option><option value="con_cama" @selected(($filtros['alojamiento'] ?? '') === 'con_cama')>Con cama vigente</option><option value="sin_cama" @selected(($filtros['alojamiento'] ?? '') === 'sin_cama')>Sin cama vigente</option></x-ui.selector></div>
        </div>
        <div class="rm-residents-filter-actions"><p><i class="ph-bold ph-sliders-horizontal" aria-hidden="true"></i> Combina filtros para encontrar personas y espacios.</p><button type="submit" class="rm-btn-primary" :disabled="loading"><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i> <span x-text="loading ? 'Buscando…' : 'Buscar'"></span></button>@if($hayFiltros)<a wire:navigate href="{{ route('admin.administracion.residentes', ['vista' => $vistaResidentes, 'por_pagina' => $porPagina]) }}" class="rm-btn-secondary"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i> Limpiar</a>@endif</div>
        @if($hayFiltros)
        <div class="rm-residents-active" aria-label="Filtros activos"><span>Filtros activos</span>@foreach($filtrosVisibles as $clave => $etiqueta)@if(filled($filtros[$clave] ?? ''))
            @php $valorFiltro = match($clave) { 'cod_habitacion' => $habitacionesDisponibles->firstWhere('cod_habitacion', $filtros[$clave])?->codigo ?? $filtros[$clave], 'piso' => $filtros[$clave] === '__sin_piso__' ? 'Sin registrar' : $filtros[$clave], 'alojamiento' => $filtros[$clave] === 'con_cama' ? 'Con cama vigente' : 'Sin cama vigente', default => $filtros[$clave] }; @endphp
            <a wire:navigate href="{{ $enlace([$clave => null]) }}" aria-label="Quitar filtro {{ $etiqueta }}"><span>{{ $etiqueta }}: {{ $valorFiltro }}</span><i class="ph-bold ph-x" aria-hidden="true"></i></a>
        @endif @endforeach</div>
        @endif
    </form>

    <section class="rm-collection-results rm-residents-results" aria-label="Directorio de residentes">
        <header class="rm-residents-results__header"><div><h2><i class="ph-bold {{ $vistaResidentes === 'camas' ? 'ph-bed' : 'ph-users-three' }}" aria-hidden="true"></i> {{ $vistaResidentes === 'camas' ? 'Mapa de alojamiento' : 'Directorio de residentes' }} <span>{{ $vistaResidentes === 'camas' ? $habitacionesPaginadas->total().' habitaciones' : $registros->total().' residentes' }}</span></h2><p>{{ $vistaResidentes === 'camas' ? 'Explora pisos, habitaciones y camas; abre una cama para ver su detalle.' : 'Abre el detalle de una persona para consultar su ubicación y red de apoyo.' }}</p></div>
            <nav class="rm-view-toggle" aria-label="Vistas de residentes">@foreach($vistas as $clave => [$titulo, $icono])<a wire:navigate href="{{ $enlace(['vista' => $clave]) }}" class="rm-view-toggle__btn {{ $vistaResidentes === $clave ? 'is-active' : '' }}" @if($vistaResidentes === $clave) aria-current="page" @endif><i class="ph-bold {{ $icono }}" aria-hidden="true"></i> {{ $titulo }}</a>@endforeach</nav>
        </header>

        @if($vistaResidentes === 'camas')
            <nav class="rm-residents-floors" aria-label="Explorar pisos"><a wire:navigate href="{{ $enlace(['piso' => null, 'cod_habitacion' => null]) }}" @if(!filled($filtros['piso'] ?? '')) aria-current="page" @endif><i class="ph-bold ph-buildings" aria-hidden="true"></i> Todos los pisos</a>@foreach($pisosDisponibles as $piso)<a wire:navigate href="{{ $enlace(['piso' => $piso, 'cod_habitacion' => null]) }}" @if((string) ($filtros['piso'] ?? '') === (string) $piso) aria-current="page" @endif>Piso {{ $piso }}</a>@endforeach</nav>
            <div class="rm-residents-guidance"><i class="ph-bold ph-info" aria-hidden="true"></i><p><strong>{{ $resumenResidentes['camas_ocupadas'] }} ocupadas · {{ $resumenResidentes['camas_disponibles'] }} disponibles · {{ $resumenResidentes['camas_no_habilitadas'] }} no habilitadas.</strong> La primera cama se asigna al formalizar la admisión. Los traslados conservan el historial de alojamiento.@if($hayFiltros) Los filtros de personas muestran solo sus camas.@endif</p></div>
            @forelse($pisosHabitaciones as $piso => $habitaciones)
            <section class="rm-residents-floor" aria-label="{{ filled($piso) ? 'Piso '.$piso : 'Piso sin registrar' }}"><header><h3><i class="ph-bold ph-stack" aria-hidden="true"></i> {{ filled($piso) ? 'Piso '.$piso : 'Piso sin registrar' }}</h3><span>{{ $habitaciones->count() }} habitaciones en esta página</span></header><div class="rm-residents-rooms">
                @foreach($habitaciones as $habitacion)
                <article class="rm-residents-room"><header><i class="ph-bold ph-door" aria-hidden="true"></i><div><h3>{{ $habitacion->codigo }}</h3><p>{{ $habitacion->nombre ?: 'Habitación' }} · {{ $habitacion->camas->count() }} camas{{ $hayFiltros ? ' visibles' : '' }}</p></div><x-ui.status-badge :estado="$habitacion->estado" /></header><div class="rm-residents-room__beds">
                    @forelse($habitacion->camas as $cama)
                        @php $detalleCama = ['codigo' => $cama->codigo, 'cod_cama' => $cama->cod_cama, 'habitacion' => $habitacion->codigo, 'piso' => filled($piso) ? 'Piso '.$piso : 'Sin registrar', 'tipo' => $cama->tipo ?: 'No registrado', 'estado' => ['ocupado' => 'Ocupada', 'disponible' => 'Disponible', 'no_habilitado' => 'No habilitada'][$cama->estado_mapa], 'estado_mapa' => $cama->estado_mapa, 'estado_real' => $cama->estado_real, 'residente' => $cama->ocupante?->titulo, 'cod_residente' => $cama->ocupante?->codigo, 'detalle_url' => $cama->ocupante ? $enlace(['residente' => $cama->ocupante->codigo]) : null]; @endphp
                        <button type="button" class="rm-residents-bed is-{{ $cama->estado_mapa }}" @click="abrirCama(@js($detalleCama), $el)" aria-label="Abrir cama {{ $cama->codigo }}: {{ $detalleCama['estado'] }}"><i class="ph-bold {{ $cama->estado_mapa === 'no_habilitado' ? 'ph-lock-key' : 'ph-bed' }}" aria-hidden="true"></i><span><strong>{{ $cama->codigo }}</strong><small>{{ $detalleCama['estado'] }}</small><em>{{ $cama->ocupante?->titulo ?: ($cama->estado_mapa === 'disponible' ? 'Lista para un ingreso o traslado' : 'No disponible para asignar') }}</em></span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></button>
                    @empty<p>Sin camas registradas{{ $hayFiltros ? ' que coincidan con los filtros' : '' }}.</p>@endforelse
                </div></article>
                @endforeach
            </div></section>
            @empty
                <div class="rm-residents-empty"><i class="ph-bold ph-bed" aria-hidden="true"></i><h3>No hay habitaciones en este resultado</h3><p>{{ ($filtros['alojamiento'] ?? '') === 'sin_cama' ? 'Los residentes sin cama vigente no tienen una ubicación en el mapa. Consulta sus tarjetas para revisar su trayectoria.' : 'Prueba otro piso o retira un filtro para explorar el alojamiento.' }}</p><a wire:navigate href="{{ $enlace(['vista' => 'tarjetas']) }}" class="rm-btn-secondary">Ver residentes</a></div>
            @endforelse
            <x-ui.paginacion :paginator="$habitacionesPaginadas" mode="url" :per-page="$porPagina" per-page-name="por_pagina" label="habitaciones" />
        @elseif($registros->isEmpty())
            <div class="rm-residents-empty"><i class="ph-bold ph-users-three" aria-hidden="true"></i><h3>{{ $hayFiltros ? 'Sin coincidencias' : 'Aún no hay residentes' }}</h3><p>{{ $hayFiltros ? 'Cambia la búsqueda o quita filtros para encontrar otros residentes.' : 'Las personas aparecen aquí después de formalizar su admisión.' }}</p>@if($hayFiltros)<a wire:navigate href="{{ route('admin.administracion.residentes', ['vista' => $vistaResidentes]) }}" class="rm-btn-secondary">Quitar filtros</a>@endif</div>
        @else
            @if($vistaResidentes === 'tabla')
                <div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Residentes, alojamiento y responsable principal</caption><thead><tr><th scope="col">Residente</th><th scope="col">Alojamiento</th><th scope="col">Responsable</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody>@foreach($registros as $registro)<tr><td>@include('pages.admin.administracion.partials.identidad-directorio')</td><td>{{ $registro->habitacion ?: 'Sin habitación' }}<small class="block">{{ $registro->cama ?: 'Sin cama vigente' }}{{ filled($registro->piso) ? ' · Piso '.$registro->piso : '' }}</small></td><td>{{ $registro->responsable ?: 'Sin responsable principal' }}</td><td><x-ui.status-badge :estado="$registro->estado" /></td><td>@include('pages.admin.administracion.partials.acciones-directorio')</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="rm-residents-records rm-residents-records--{{ $vistaResidentes }}">@foreach($registros as $registro)<article class="rm-residents-record">
                    @include('pages.admin.administracion.partials.identidad-directorio')
                    <dl class="rm-residents-facts"><div><dt><i class="ph-bold ph-bed" aria-hidden="true"></i> Alojamiento</dt><dd>{{ $registro->habitacion ?: 'Sin habitación asignada' }}<small>{{ $registro->cama ?: 'Sin cama vigente' }}{{ filled($registro->piso) ? ' · Piso '.$registro->piso : '' }}</small></dd></div><div><dt><i class="ph-bold ph-user-circle" aria-hidden="true"></i> Responsable principal</dt><dd>{{ $registro->responsable ?: 'Sin responsable registrado' }}@if($registro->parentesco)<small>{{ $registro->parentesco }}</small>@endif</dd></div></dl>
                    <footer class="rm-residents-record__footer"><x-ui.status-badge :estado="$registro->estado" />@include('pages.admin.administracion.partials.acciones-directorio')</footer>
                </article>@endforeach</div>
            @endif
            <x-ui.paginacion :paginator="$registros" mode="url" :per-page="$porPagina" per-page-name="por_pagina" label="residentes" />
        @endif
    </section>

    <template x-teleport="body"><div x-cloak x-show="cama" x-transition.opacity class="rm-modal-shell rm-residents-bed-modal fixed inset-0 flex items-center justify-center p-4" x-trap.inert.noscroll="cama !== null" @keydown.escape.window="if(cama) cerrarCama()" @click.self="cerrarCama()" role="dialog" aria-modal="true" aria-labelledby="detalle-cama-titulo">
        <section class="rm-modal-panel relative"><header class="rm-modal-header"><div><span class="rm-residents-eyebrow">Detalle de alojamiento</span><h2 id="detalle-cama-titulo" x-text="cama?.codigo"></h2><p x-text="cama?.habitacion + ' · ' + cama?.piso"></p></div><button type="button" x-ref="cerrarCama" @click="cerrarCama()" class="rm-btn-icon" aria-label="Cerrar detalle de cama"><i class="ph-bold ph-x" aria-hidden="true"></i></button></header><div class="rm-modal-body"><dl class="rm-residents-bed-modal__data"><div><dt>Disponibilidad</dt><dd x-text="cama?.estado"></dd></div><div><dt>Tipo de cama</dt><dd x-text="cama?.tipo"></dd></div><div><dt>Estado registrado</dt><dd x-text="cama?.estado_real"></dd></div><div><dt>Residente actual</dt><dd x-text="cama?.residente || 'Sin ocupación activa'"></dd></div></dl><p class="rm-residents-guidance" x-show="cama?.estado_mapa === 'disponible'"><i class="ph-bold ph-info" aria-hidden="true"></i> Una cama disponible se utiliza al formalizar un ingreso o al trasladar un residente. La disponibilidad vuelve a validarse al confirmar.</p><p class="rm-residents-guidance" x-show="cama?.estado_mapa === 'no_habilitado'"><i class="ph-bold ph-lock-key" aria-hidden="true"></i> Esta cama o su habitación no está habilitada para recibir una asignación.</p></div><footer class="rm-modal-footer"><button class="rm-btn-secondary" type="button" @click="cerrarCama()">Cerrar</button><a wire:navigate class="rm-btn-primary" x-show="cama?.detalle_url" :href="cama?.detalle_url" @click="cama = null">Ver residente <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@if($visibilidadNavegacion->puedeVerRuta('admin.administracion.admisiones'))<a wire:navigate class="rm-btn-primary" x-show="cama?.estado_mapa === 'disponible'" href="{{ route('admin.administracion.admisiones', ['tab' => 'preparacion']) }}">Preparar ingreso</a>@endif</footer></section>
    </div></template>

    @if($panelResidente)
        @include('pages.admin.administracion.partials.panel-residente', ['residente' => $panelResidente, 'datosPanel' => $panelDatos])
    @endif
    @if(!$panelResidente)
    @can('ocupaciones_cama.gestionar')
        @livewire(App\Frontend\Livewire\Admisiones\AlojamientoResidente::class)
    @endcan
    @endif
</div>
</x-sistema-layout>
