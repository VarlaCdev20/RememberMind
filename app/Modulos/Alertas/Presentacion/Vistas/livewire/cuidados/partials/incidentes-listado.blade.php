@php
    $filtros = array_filter([
        'search' => trim($search) !== '' ? 'Búsqueda: '.$search : '',
        'filtro_tipo' => $filtro_tipo !== '' ? 'Tipo: '.$filtro_tipo : '',
        'filtro_gravedad' => $filtro_gravedad !== '' ? 'Gravedad: '.ucfirst(strtolower($filtro_gravedad)) : '',
        'filtro_estado' => $filtro_estado !== '' ? 'Estado: '.str_replace('_', ' ', strtolower($filtro_estado)) : '',
        'fecha_desde' => $fecha_desde !== '' ? 'Desde: '.$fecha_desde : '',
        'fecha_hasta' => $fecha_hasta !== '' ? 'Hasta: '.$fecha_hasta : '',
    ]);
@endphp

<x-ui.collection-header title="INCIDENTES" subtitle="Consulta lo ocurrido y continúa el seguimiento de tus residentes." icon="ph-warning-octagon" eyebrow="Continuidad asistencial">
    <x-slot:actions>
        @if($puedeRegistrar)
            <x-ui.action-button wire:click="abrirModalRegistro" icono="ph-plus" loading="abrirModalRegistro">Registrar incidente</x-ui.action-button>
        @endif
    </x-slot:actions>
</x-ui.collection-header>

@foreach(['mensaje' => 'success', 'error' => 'danger', 'info' => 'info'] as $clave => $tono)
    @if(session()->has($clave))
        <div class="rm-incidents-notice" data-tone="{{ $tono }}" role="{{ $clave === 'error' ? 'alert' : 'status' }}">
            <i class="ph-bold {{ $clave === 'error' ? 'ph-warning-circle' : 'ph-info' }}" aria-hidden="true"></i>
            <p>{{ session($clave) }}</p>
        </div>
    @endif
@endforeach
@if(auth()->user()?->hasRole('ENFERMEROS') && !$puedeRegistrar)
    <div class="rm-incidents-notice" role="status"><i class="ph-bold ph-eye" aria-hidden="true"></i><p>Modo consulta. Puedes ver los incidentes de tus residentes asignados; registrar y actualizar requiere turno activo y permiso.</p></div>
@endif

<section class="rm-incidents-controls" aria-label="Buscar y filtrar incidentes">
    <div class="rm-incidents-navigation">
        <nav class="rm-tabs" aria-label="Vistas de incidentes">
            @foreach(['listado' => ['Activos', 'ph-list-checks'], 'historial' => ['Historial', 'ph-clock-counter-clockwise']] as $tab => [$titulo, $icono])
                <button type="button" class="rm-tab-item {{ $tabActiva === $tab ? 'is-selected' : '' }}" wire:click="setTab('{{ $tab }}')" aria-current="{{ $tabActiva === $tab ? 'page' : 'false' }}" aria-controls="incidentes-resultados">
                    <i class="ph-bold {{ $icono }}" aria-hidden="true"></i>{{ $titulo }}<span class="rm-tab-badge">{{ $conteos[$tab] }}</span>
                </button>
            @endforeach
        </nav>
        <p>{{ $tabActiva === 'listado' ? 'Abiertos y en seguimiento' : 'Cerrados y anulados' }}</p>
    </div>

    <x-ui.filter-bar class="rm-incidents-filters">
        <div class="rm-incidents-filters__primary">
            <div>
                <label for="incidentes-buscar" class="rm-collection-filter-label">Buscar incidente</label>
                <div class="rm-incidents-search">
                    <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
                    <input id="incidentes-buscar" type="search" wire:model.live.debounce.300ms="search" placeholder="Residente, evento o lugar" maxlength="150" />
                </div>
            </div>
            <div>
                <label for="incidentes-estado" class="rm-collection-filter-label">Estado</label>
                <select id="incidentes-estado" wire:model.live="filtro_estado">
                    <option value="">Todos los {{ $tabActiva === 'listado' ? 'activos' : 'del historial' }}</option>
                    @foreach($estadosFiltro as $valor => $texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach
                </select>
            </div>
            <x-ui.action-button variant="secondary" wire:click="toggleMasFiltros" icono="ph-funnel" aria-controls="incidentes-filtros-extra" aria-expanded="{{ $mostrarMasFiltros ? 'true' : 'false' }}">
                {{ $mostrarMasFiltros ? 'Menos filtros' : 'Más filtros' }}
            </x-ui.action-button>
        </div>
        @if($mostrarMasFiltros)
            <div id="incidentes-filtros-extra" class="rm-incidents-filters__extra">
                <div>
                    <label for="incidentes-tipo" class="rm-collection-filter-label">Tipo de incidente</label>
                    <select id="incidentes-tipo" wire:model.live="filtro_tipo"><option value="">Todos los tipos</option>@foreach($tiposFiltro as $tipo)<option value="{{ $tipo }}">{{ $tipo }}</option>@endforeach</select>
                </div>
                <div>
                    <label for="incidentes-gravedad" class="rm-collection-filter-label">Gravedad registrada</label>
                    <select id="incidentes-gravedad" wire:model.live="filtro_gravedad"><option value="">Todas</option><option value="BAJA">Baja</option><option value="MEDIA">Media</option><option value="ALTA">Alta</option><option value="CRITICA">Crítica</option></select>
                </div>
                <div>
                    <label for="incidentes-desde" class="rm-collection-filter-label">Desde</label>
                    <input id="incidentes-desde" type="date" wire:model.live="fecha_desde" aria-invalid="{{ $errors->has('fecha_desde') ? 'true' : 'false' }}" @error('fecha_desde') aria-describedby="incidentes-desde-error" @enderror />
                    @error('fecha_desde')<p id="incidentes-desde-error" class="rm-incidents-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="incidentes-hasta" class="rm-collection-filter-label">Hasta</label>
                    <input id="incidentes-hasta" type="date" wire:model.live="fecha_hasta" aria-invalid="{{ $errors->has('fecha_hasta') ? 'true' : 'false' }}" @error('fecha_hasta') aria-describedby="incidentes-hasta-error" @enderror />
                    @error('fecha_hasta')<p id="incidentes-hasta-error" class="rm-incidents-error" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>
        @endif
        @if(!$mostrarMasFiltros && ($errors->has('fecha_desde') || $errors->has('fecha_hasta')))
            <p class="rm-incidents-error" role="alert">{{ $errors->first('fecha_desde') ?: $errors->first('fecha_hasta') }} Abre «Más filtros» para corregir las fechas.</p>
        @endif
        @if($filtros)
            <div class="rm-incidents-active-filters" aria-label="Filtros aplicados">
                @foreach($filtros as $campo => $texto)
                    <button type="button" class="rm-incidents-filter-chip" wire:click="limpiarFiltro('{{ $campo }}')" aria-label="Quitar filtro {{ $texto }}"><span>{{ $texto }}</span><i class="ph-bold ph-x" aria-hidden="true"></i></button>
                @endforeach
                <x-ui.action-button variant="ghost" wire:click="limpiarFiltros" icono="ph-arrow-counter-clockwise">Limpiar filtros</x-ui.action-button>
            </div>
        @endif
    </x-ui.filter-bar>
</section>

<section id="incidentes-resultados" class="rm-incidents-results" aria-labelledby="incidentes-resultados-titulo" wire:loading.class="is-loading" wire:target="search,filtro_tipo,filtro_gravedad,filtro_estado,fecha_desde,fecha_hasta,setTab,limpiarFiltros,limpiarFiltro,gotoPage">
    <header class="rm-incidents-results__heading">
        <div><h2 id="incidentes-resultados-titulo">{{ $tabActiva === 'listado' ? 'Incidentes activos' : 'Historial de incidentes' }}</h2><p>{{ $tabActiva === 'listado' ? 'Graves primero; luego seguimiento y abiertos.' : 'Más recientes primero. Incluye registros anulados.' }}</p></div>
        <span role="status" aria-live="polite">{{ $incidentes->total() }} {{ $incidentes->total() === 1 ? 'resultado' : 'resultados' }}</span>
        <span wire:loading wire:target="search,filtro_tipo,filtro_gravedad,filtro_estado,fecha_desde,fecha_hasta,setTab,limpiarFiltros,limpiarFiltro,gotoPage" role="status">Actualizando…</span>
    </header>
    <div class="rm-incidents-list">
        @forelse($incidentes as $inc)
            @php
                $nombre = $inc->residente?->nombre_completo ?? 'Residente '.$inc->cod_residente;
                $gravedadTono = match($inc->gravedad) { 'CRITICA', 'ALTA' => 'danger', 'MEDIA' => 'warning', default => 'neutral' };
                $estadoTono = match($inc->estado) { 'CERRADO' => 'success', 'EN_SEGUIMIENTO' => 'info', 'ANULADO' => 'neutral', default => 'warning' };
            @endphp
            <article class="rm-incident" wire:key="incidente-{{ $tabActiva }}-{{ $inc->cod_incidente }}" aria-labelledby="incidente-{{ $inc->cod_incidente }}-titulo">
                @if($inc->gravedad === 'CRITICA' && in_array($inc->estado, ['ABIERTO', 'EN_SEGUIMIENTO']))
                    <p class="rm-incident__priority"><i class="ph-bold ph-warning-octagon" aria-hidden="true"></i>Gravedad crítica registrada · {{ ucfirst(str_replace('_', ' ', strtolower($inc->estado))) }}</p>
                @endif
                <div class="rm-incident__identity">
                    <span class="rm-incident__avatar" aria-hidden="true">{{ mb_substr($inc->residente?->nombres ?? 'R', 0, 1) }}{{ mb_substr($inc->residente?->apellido_paterno ?? '', 0, 1) }}</span>
                    <div><h3 id="incidente-{{ $inc->cod_incidente }}-titulo">{{ $nombre }}</h3><p><i class="ph-bold ph-bed" aria-hidden="true"></i>{{ $inc->residente?->ubicacion_formateada ?? 'Sin ubicación' }}</p></div>
                    <time datetime="{{ $inc->fecha_hora->toIso8601String() }}"><i class="ph-bold ph-clock" aria-hidden="true"></i>{{ $inc->fecha_hora->format('d/m/Y') }}<strong>{{ $inc->fecha_hora->format('H:i') }}</strong></time>
                </div>
                <div class="rm-incident__event">
                    <div class="rm-incident__event-heading"><span class="rm-incident__type">{{ $inc->tipo_incidente }}</span>@if($inc->lugar)<span><i class="ph-bold ph-map-pin" aria-hidden="true"></i>{{ $inc->lugar }}</span>@endif</div>
                    <p class="rm-incident__description">{{ $inc->descripcion }}</p>
                    @if($inc->medida_inmediata)<p class="rm-incident__measure"><strong>Medida inmediata:</strong> {{ $inc->medida_inmediata }}</p>@endif
                    @if($inc->alertaAsociada && in_array($inc->alertaAsociada->estado, ['ACTIVA', 'PENDIENTE', 'EN_PROCESO', 'RECONOCIDA', 'ASIGNADA']))<p class="rm-incident__alert"><i class="ph-bold ph-bell-ringing" aria-hidden="true"></i>Alerta vinculada · {{ str_replace('_', ' ', strtolower($inc->alertaAsociada->estado)) }}</p>@endif
                </div>
                <div class="rm-incident__status">
                    <span class="rm-incident__label">Gravedad / estado</span>
                    <div class="rm-incident__badges"><x-ui.status-badge :estado="$inc->gravedad" :label="$inc->gravedad === 'CRITICA' ? 'Crítica' : ucfirst(strtolower($inc->gravedad))" :variant="$gravedadTono" /><x-ui.status-badge :estado="$inc->estado" :label="ucfirst(str_replace('_', ' ', strtolower($inc->estado)))" :variant="$estadoTono" /></div>
                    <x-ui.action-button variant="secondary" icono="ph-eye" wire:click="verIncidente('{{ $inc->cod_incidente }}')" loading="verIncidente('{{ $inc->cod_incidente }}')" aria-label="Ver detalle del incidente de {{ $nombre }}">Ver detalle</x-ui.action-button>
                </div>
                @if($puedeRegistrar)
                    <details class="rm-incident__actions">
                        <summary>Acciones de seguimiento<i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
                        <div>
                            <x-ui.action-button variant="ghost" icono="ph-arrows-clockwise" wire:click="abrirModalEstado('{{ $inc->cod_incidente }}')">Actualizar estado</x-ui.action-button>
                            <x-ui.action-button variant="ghost" icono="ph-first-aid" wire:click="solicitarValoracionMedica('{{ $inc->cod_incidente }}')" loading="solicitarValoracionMedica('{{ $inc->cod_incidente }}')">Solicitar valoración médica</x-ui.action-button>
                            <x-ui.action-button variant="ghost" icono="ph-arrow-square-out" wire:click="abrirModalDerivacion('{{ $inc->cod_incidente }}')">Crear derivación</x-ui.action-button>
                        </div>
                    </details>
                @endif
            </article>
        @empty
            <x-ui.empty-state icon="ph-clipboard-text" :title="$filtros ? 'No hay coincidencias' : ($tabActiva === 'listado' ? 'Sin incidentes activos' : 'El historial está vacío')" :description="$filtros ? 'Prueba con otro residente, estado o intervalo de fechas.' : ($tabActiva === 'listado' ? 'No hay incidentes abiertos o en seguimiento en tu alcance de consulta.' : 'Aquí aparecerán los incidentes cerrados y anulados de tus residentes.')">
                @if($filtros)<x-ui.action-button variant="secondary" wire:click="limpiarFiltros" icono="ph-arrow-counter-clockwise">Limpiar filtros</x-ui.action-button>@endif
            </x-ui.empty-state>
        @endforelse
    </div>
    @if($incidentes->hasPages())<footer class="rm-incidents-pagination">{{ $incidentes->links() }}</footer>@endif
</section>
