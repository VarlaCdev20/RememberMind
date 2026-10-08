<x-sistema-layout>
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')
@if($modulo === 'reportes')
    @include('pages.admin.administracion.partials.reportes-operativos')
@elseif($modulo === 'ocupacion')
    @include('pages.admin.administracion.partials.historial-ocupacion')
@else
@php
    $ruta = 'admin.administracion.'.$modulo;
    $parametros = array_filter(array_replace($filtros, ['vista' => $vista, 'tab' => $tab, 'por_pagina' => $porPagina, 'mes' => $vista === 'calendario' ? $mesCalendario->format('Y-m') : ($filtros['mes'] ?? null)]), fn ($valor, $clave) => filled($valor) && $clave !== 'detalle', ARRAY_FILTER_USE_BOTH);
    $enlace = fn ($cambios = []) => route($ruta, array_filter(array_replace($parametros, array_key_exists('detalle', $cambios) ? $cambios : ['page' => null] + $cambios), fn ($valor) => filled($valor)));
    $etiquetasVistas = ['tabla' => ['Tabla', 'ph-table'], 'lista' => ['Lista', 'ph-list-bullets'], 'tarjetas' => ['Tarjetas', 'ph-squares-four'], 'agenda' => ['Agenda', 'ph-calendar-dots'], 'cronologia' => ['Cronología', 'ph-clock-counter-clockwise'], 'cobertura' => ['Por área', 'ph-map-trifold'], 'tablero' => ['Tablero', 'ph-kanban'], 'calendario' => ['Calendario', 'ph-calendar-dots']];
    $etiquetasVistas['agenda'][0] = match($modulo) { 'jornadas' => 'Agenda de turnos', 'actividades' => 'Programación', 'visitas' => 'Agenda de visitas', default => 'Agenda' };
    $etiquetasVistas['cronologia'][0] = $modulo === 'incidentes' ? 'Línea de sucesos' : 'Historial de firmas';
    $etiquetasVistas['tarjetas'][0] = match($modulo) { 'contactos' => 'Directorio', 'seguros' => 'Fichas de cobertura', default => 'Tarjetas' };
    $activos = collect(['search' => 'Búsqueda', 'estado' => 'Estado', 'categoria' => 'Área o grupo', 'funcion' => 'Función', 'dia' => 'Día', 'mes' => 'Mes', 'prioridad' => 'Prioridad', 'desde' => 'Desde', 'hasta' => 'Hasta', 'fecha' => 'Fecha', 'fecha_visita' => 'Periodo de visitas'])->filter(fn ($texto, $clave) => filled($filtros[$clave] ?? ''));
    $bandejaGeneral = array_key_exists('todas',$tabs) ? 'todas' : (array_key_exists('todos',$tabs) ? 'todos' : null);
    $anteriores = collect(session()->getOldInput())->only(['search', 'estado', 'tab', 'prioridad', 'desde', 'hasta', 'orden', 'categoria', 'fecha_visita'])->filter(fn($valor)=>is_scalar($valor) || $valor === null)->all();
    $valoresFormulario = $errors->any() ? array_replace($filtros,$anteriores) : $filtros;
@endphp
<div class="rm-residents rm-operations rm-operations--{{ $modulo }}" x-data="rmOperaciones(@js($modulo))" :aria-busy="loading" x-on:livewire:navigated.window="finalizarNavegacion()">
    <x-ui.collection-header :title="$definicion['titulo']" :subtitle="$presentacion['ayuda']" :icon="$definicion['icono']" eyebrow="Administración / Coordinación" :date="now()->locale('es')">
        <x-slot:actions>
            <button type="button" class="rm-btn-secondary" @click="alternarGraficos()" :aria-expanded="graficos" aria-controls="operacion-graficos"><i class="ph-bold ph-chart-bar" aria-hidden="true"></i><span x-text="graficos ? 'Ocultar gráficos' : 'Mostrar gráficos'"></span></button>
            <button type="button" class="rm-btn-icon" aria-label="Ayuda de {{ mb_strtolower($definicion['titulo']) }}" @click="abrirAyuda($event)"><i class="ph-bold ph-question" aria-hidden="true"></i></button>
            <button type="button" class="rm-btn-icon" @click="actualizar()" :disabled="loading" aria-label="Actualizar {{ mb_strtolower($definicion['titulo']) }}"><i class="ph-bold ph-arrow-clockwise" aria-hidden="true"></i></button>
        </x-slot:actions>
    </x-ui.collection-header>
    @include('pages.admin.administracion.partials.guia-operativa')
    <section class="rm-operations-context rm-operations-context--compact" aria-label="Contexto de {{ mb_strtolower($definicion['titulo']) }}"><div><span class="rm-operations-context__icon"><i class="ph-bold {{ $definicion['icono'] }}" aria-hidden="true"></i></span><div><div class="rm-operation-count"><strong>{{ $vista === 'calendario' ? $totalCalendario : $registros->total() }}</strong><span>{{ $presentacion['unidad'] }} {{ $vista === 'calendario' ? 'en el mes' : 'en la selección' }}</span></div><small>{{ $totalContexto }} según búsqueda y fechas</small></div></div><nav class="rm-operations-states" aria-label="Filtrar por estado">@foreach($distribucion as $estado)<a wire:navigate href="{{ $enlace(['estado' => $estado->estado ?? '__sin_estado__', 'categoria'=>null, 'tab' => $tabs ? (array_key_exists('todas', $tabs) ? 'todas' : (array_key_exists('todos', $tabs) ? 'todos' : null)) : null]) }}" @if(($filtros['estado'] ?? null) === ($estado->estado ?? '__sin_estado__')) aria-current="page" @endif><span>{{ $estado->estado ? ucfirst(strtolower(str_replace('_', ' ', $estado->estado))) : 'Sin estado' }}</span><strong>{{ $estado->cantidad }}</strong></a>@endforeach</nav></section>
    @include('pages.admin.administracion.partials.filtros-operativos')
    @include('pages.admin.administracion.partials.graficos-por-proceso')
    <section class="rm-collection-results" aria-label="Resultados de {{ mb_strtolower($definicion['titulo']) }}"><header class="rm-residents-results__header"><div><h2>{{ $tabs[$tab] ?? $definicion['titulo'] }} <span>{{ $registros->total() }}</span></h2><p>Selecciona un registro para consultar sus detalles.</p></div><nav class="rm-view-toggle" aria-label="Vistas de {{ mb_strtolower($definicion['titulo']) }}">@foreach($presentacion['vistas'] as $tipo)<a wire:navigate class="rm-view-toggle__btn {{ $vista === $tipo ? 'is-active' : '' }}" href="{{ $enlace(['vista'=>$tipo]) }}" @if($vista === $tipo) aria-current="page" @endif><i class="ph-bold {{ $etiquetasVistas[$tipo][1] }}" aria-hidden="true"></i>{{ $etiquetasVistas[$tipo][0] }}</a>@endforeach</nav></header>
        @if($vista === 'calendario')
            @include('pages.admin.administracion.partials.calendario-operativo')
        @elseif($registros->isEmpty())<div class="rm-residents-empty"><i class="ph-bold {{ $definicion['icono'] }}" aria-hidden="true"></i><h3>{{ $activos->isNotEmpty() ? 'Sin resultados para estos filtros' : 'Sin registros en esta bandeja' }}</h3><p>{{ $activos->isNotEmpty() ? 'Ajusta la búsqueda o limpia los filtros.' : 'Consulta otra bandeja o revisa el proceso relacionado.' }}</p>@if($estados->sum('cantidad') > 0)<a wire:navigate class="rm-btn-secondary" href="{{ route($ruta,['vista'=>$vista,'tab'=>$bandejaGeneral,'por_pagina'=>$porPagina]) }}">Ver todos los registros</a>@endif</div>
        @elseif($vista === 'tabla')<div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">{{ $definicion['titulo'] }} en la bandeja seleccionada</caption><thead><tr><th scope="col">Registro</th>@foreach($columnas as $etiqueta)<th scope="col">{{ $etiqueta }}</th>@endforeach<th scope="col">Detalle</th></tr></thead><tbody>@foreach($registros as $registro)<tr><td><strong>{{ $registro->titulo }}</strong><small class="block">{{ $registro->codigo }}</small></td>@foreach($columnas as $campo=>$etiqueta)<td>@include('pages.admin.administracion.partials.campo-operativo',['valor'=>$registro->{$campo} ?? null])</td>@endforeach<td><a class="rm-btn-secondary" href="{{ $enlace(['detalle'=>$registro->codigo]) }}" data-registro="{{ $registro->codigo }}" @click.prevent="abrirFicha(@js($enlace(['detalle'=>$registro->codigo])), @js($registro->codigo), $event)" aria-label="Ver detalle de {{ $registro->titulo }}"><i class="ph-bold ph-eye" aria-hidden="true"></i> Ver</a></td></tr>@endforeach</tbody></table></div>
        @else
            @if(in_array($vista,['cobertura','tablero'],true))
                @include('pages.admin.administracion.partials.vista-agrupada-operativa')
            @elseif(in_array($vista,['agenda','cronologia'],true))
                @foreach($registros->getCollection()->groupBy(fn($registro)=>($registro->{$presentacion['campoFecha']} ?? null) ? \Carbon\Carbon::parse($registro->{$presentacion['campoFecha']})->toDateString() : 'sin_fecha') as $dia=>$grupo)<section class="rm-operations-day is-{{ $vista }}"><h3><i class="ph-bold {{ $vista === 'agenda' ? 'ph-calendar-check' : 'ph-clock' }}" aria-hidden="true"></i>{{ $dia === 'sin_fecha' ? 'Fecha sin registrar' : \Carbon\Carbon::parse($dia)->locale('es')->translatedFormat('l, d \d\e F Y') }}<span>{{ $grupo->count() }} en esta página</span></h3><div class="rm-operations-cards is-{{ $vista }}">@foreach($grupo as $registro)@include('pages.admin.administracion.partials.tarjeta-operativa')@endforeach</div></section>@endforeach
            @else<div class="rm-operations-cards is-{{ $vista }}">@foreach($registros as $registro)@include('pages.admin.administracion.partials.tarjeta-operativa')@endforeach</div>@endif
        @endif
        @if($vista !== 'calendario')<x-ui.paginacion :exclude-query="['detalle']" :paginator="$registros" mode="url" :per-page="$porPagina" :label="$presentacion['unidad']" />@endif
    </section>
    <p x-show="cargandoFicha" x-cloak class="rm-control-help" role="status">Abriendo ficha…</p>
    <p x-show="errorFicha" x-cloak class="rm-alert rm-alert--warning" role="alert" x-text="errorFicha"></p>
    <div x-ref="ficha" x-html="fichaHtml"></div>
    @if($detalle)<div x-show="fichaHtml === ''" x-init="fichaHtml = $el.innerHTML; fichaAbierta = true; $el.remove()">@include('pages.admin.administracion.partials.detalle-operativo') </div>@endif
</div>
@endif
</x-sistema-layout>
