@php
    $busquedaEjemplo = match($modulo) {
        'jornadas' => 'Turno o código de jornada', 'asignaciones' => 'Personal, área, turno o código',
        'contactos' => 'Nombre, documento o código del contacto', 'documentacion' => 'Documento, tipo o titular',
        'consentimientos' => 'Tipo, residente o código', 'seguros' => 'Entidad, residente o código',
        'actividades' => 'Actividad, lugar, área o responsable', 'visitas' => 'Visitante, residente o código',
        'alertas' => 'Título, residente, responsable o código', 'incidentes' => 'Tipo, lugar, residente o código',
    };
    $fechaAnalizada = match($modulo) {
        'jornadas' => 'Día de la jornada', 'asignaciones' => 'Día de la jornada', 'documentacion' => 'Vencimiento del documento',
        'consentimientos' => 'Fecha del consentimiento', 'actividades' => 'Fecha de la actividad',
        'visitas' => $presentacion['campoFecha'] === 'ingreso' ? 'Entrada registrada' : 'Fecha programada',
        'alertas' => 'Fecha de la alerta', 'incidentes' => 'Fecha del incidente', default => null,
    };
    $abrirAvanzados = $errors->has('desde') || $errors->has('hasta') || filled($valoresFormulario['desde'] ?? '') || filled($valoresFormulario['hasta'] ?? '') || filled($filtros['orden'] ?? '');
@endphp
<form class="rm-filter-bar rm-residents-filters rm-operation-filters" action="{{ route($ruta) }}" method="GET" x-ref="filtros" @submit.prevent="filtrar()" role="search" aria-label="Filtrar {{ mb_strtolower($definicion['titulo']) }}">
    @if($errors->any())<p class="rm-alert rm-alert--warning" role="alert">El filtro no se aplicó. Revisa los campos; los resultados conservan la última consulta válida.</p><x-validation-errors class="mb-3" />@endif
    <input type="hidden" name="vista" value="{{ $vista }}"><input type="hidden" name="por_pagina" value="{{ $porPagina }}">
    @foreach(['fecha', 'mes', 'dia', 'funcion'] as $clave)@if(filled($filtros[$clave] ?? ''))<input type="hidden" name="{{ $clave }}" value="{{ $filtros[$clave] }}">@endif @endforeach
    <div class="rm-operation-filters__primary">
        <div class="rm-residents-search"><label for="operacion-search">Buscar en {{ mb_strtolower($definicion['titulo']) }}</label><div><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i><input id="operacion-search" class="rm-input" type="search" name="search" maxlength="100" value="{{ $valoresFormulario['search'] ?? '' }}" placeholder="{{ $busquedaEjemplo }}"></div></div>
        @if($tabs)<div><label for="operacion-bandeja">Bandeja</label><x-ui.selector id="operacion-bandeja" name="tab" label="Bandeja">@foreach($tabs as $clave => $texto)<option value="{{ $clave }}" @selected(($valoresFormulario['tab'] ?? $tab) === $clave)>{{ $texto }}</option>@endforeach</x-ui.selector></div>@endif
        <div><label for="operacion-estado">Estado registrado</label><x-ui.selector id="operacion-estado" name="estado" label="Estado registrado"><option value="">Todos los estados</option>@foreach($estados as $estado)<option value="{{ $estado->estado ?? '__sin_estado__' }}" @selected(($valoresFormulario['estado'] ?? '') === ($estado->estado ?? '__sin_estado__'))>{{ $estado->estado ? ucfirst(strtolower(str_replace('_', ' ', $estado->estado))) : 'Sin estado' }} ({{ $estado->cantidad }})</option>@endforeach</x-ui.selector></div>
        @if($modulo === 'alertas')<div><label for="operacion-prioridad">Prioridad registrada</label><x-ui.selector id="operacion-prioridad" name="prioridad" label="Prioridad"><option value="">Todas las prioridades</option>@foreach($prioridades as $prioridad)<option value="{{ $prioridad }}" @selected(($valoresFormulario['prioridad'] ?? '') === $prioridad)>{{ $prioridad }}</option>@endforeach</x-ui.selector></div>@endif
        @if(in_array($modulo,['asignaciones','actividades'],true))<div><label for="operacion-area">Área</label><x-ui.selector id="operacion-area" name="categoria" label="Área"><option value="">Todas las áreas</option>@foreach($opcionesCategoria as $opcion)<option value="{{ $opcion }}" @selected(($valoresFormulario['categoria'] ?? '') === $opcion)>{{ $opcion }}</option>@endforeach</x-ui.selector></div>@elseif(filled($filtros['categoria'] ?? ''))<input type="hidden" name="categoria" value="{{ $filtros['categoria'] }}">@endif
        @if($modulo === 'visitas')<div><label for="operacion-fecha-visita">Mostrar por</label><x-ui.selector id="operacion-fecha-visita" name="fecha_visita" label="Fecha de visitas"><option value="programacion" @selected(($valoresFormulario['fecha_visita'] ?? 'programacion') === 'programacion')>Programación</option><option value="ingreso" @selected(($valoresFormulario['fecha_visita'] ?? '') === 'ingreso')>Entradas registradas</option></x-ui.selector></div>@endif
        <button type="submit" class="rm-btn-primary" :disabled="loading"><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i><span x-text="loading ? 'Buscando…' : 'Buscar'"></span></button>
    </div>
    <details class="rm-operation-filters__advanced" @if($abrirAvanzados) open @endif>
        <summary><i class="ph-bold ph-sliders-horizontal" aria-hidden="true"></i>{{ $presentacion['campoFecha'] ? 'Fechas y orden' : 'Orden de los resultados' }}<i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
        @if($fechaAnalizada)<p class="rm-control-help">El periodo filtra: <strong>{{ $fechaAnalizada }}</strong>.</p>@endif
        <div class="rm-residents-filter-grid">
            @if($presentacion['campoFecha'])<div><label for="operacion-desde">Desde</label><x-ui.calendario id="operacion-desde" name="desde" label="Fecha desde" :value="$valoresFormulario['desde'] ?? ''" :aria-invalid="$errors->has('desde') ? 'true' : 'false'" aria-describedby="operacion-desde-error" />@error('desde')<p id="operacion-desde-error" class="rm-field-error" role="alert">{{ $message }}</p>@enderror</div><div><label for="operacion-hasta">Hasta</label><x-ui.calendario id="operacion-hasta" name="hasta" label="Fecha hasta" :value="$valoresFormulario['hasta'] ?? ''" :aria-invalid="$errors->has('hasta') ? 'true' : 'false'" aria-describedby="operacion-hasta-error" />@error('hasta')<p id="operacion-hasta-error" class="rm-field-error" role="alert">{{ $message }}</p>@enderror</div>@endif
            <div><label for="operacion-orden">Orden</label><x-ui.selector id="operacion-orden" name="orden" label="Orden"><option value="recientes" @selected(($valoresFormulario['orden'] ?? $ordenInicial) === 'recientes')>{{ $presentacion['campoFecha'] ? 'Fecha descendente' : 'Nombre Z–A' }}</option><option value="antiguas" @selected(($valoresFormulario['orden'] ?? $ordenInicial) === 'antiguas')>{{ $presentacion['campoFecha'] ? 'Fecha ascendente' : 'Nombre A–Z' }}</option></x-ui.selector></div>
        </div>
    </details>
    @if($activos->isNotEmpty())<div class="rm-residents-active" aria-label="Filtros activos"><span>Tu selección</span>@foreach($activos as $clave=>$texto)<a wire:navigate href="{{ $enlace($clave === 'mes' ? ['mes'=>null,'dia'=>null] : [$clave=>null]) }}" aria-label="Quitar {{ $texto }}"><span>{{ $texto }}: {{ $clave === 'fecha_visita' ? (($filtros['fecha_visita'] ?? '') === 'ingreso' ? 'Entrada registrada' : 'Programación') : match($filtros[$clave] ?? '') { '__sin_estado__' => 'Sin estado', '__sin_categoria__' => 'Sin registrar', '__sin_funcion__' => 'Sin función registrada', default => $filtros[$clave] } }}</span><i class="ph-bold ph-x" aria-hidden="true"></i></a>@endforeach<a wire:navigate class="rm-btn-secondary" href="{{ route($ruta,['vista'=>$vista,'por_pagina'=>$porPagina]) }}"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i> Limpiar</a></div>@endif
</form>
