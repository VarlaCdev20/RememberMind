@php
    [$tipoCategoria, $tipoTiempo, $lectura] = match($modulo) {
        'jornadas' => ['horarios', 'columnas', 'Localiza los horarios con más jornadas y abre su agenda.'],
        'asignaciones' => ['mosaico', 'anillo', 'Explora áreas y funciones registradas; cada conteo representa asignaciones.'],
        'contactos' => ['anillo', 'franja', 'Distingue contactos con distintos números de vínculos activos.'],
        'documentacion' => ['barras', 'puntos', 'Compara tipos de documento y localiza sus vencimientos.'],
        'consentimientos' => ['anillo', 'puntos', 'Consulta la proporción por tipo y las fechas de registro.'],
        'seguros' => ['barras', 'anillo', 'Compara registros de cobertura por entidad y situación.'],
        'actividades' => ['comparacion', 'anillo', 'Compara participantes y cupo registrados; estos datos no confirman asistencia.'],
        'visitas' => ['estaciones', 'puntos', 'Abre cada situación registrada para coordinar los encuentros.'],
        'alertas' => ['estaciones', 'columnas', 'Prioridades registradas para dirigir la consulta; no calcula riesgo clínico.'],
        'incidentes' => ['columnas', 'puntos', 'Compara la gravedad registrada y cuándo ocurrieron los sucesos.'],
    };
    $datosCategorias = $categorias->map(function($grupo) use ($enlace, $bandejaGeneral, $modulo, $filtros) {
        $valor = $grupo->etiqueta === null || $grupo->etiqueta === '' ? '__sin_categoria__' : (string)$grupo->etiqueta;
        $etiqueta = $valor === '__sin_categoria__' ? 'Sin registrar' : ($modulo === 'contactos' ? $valor.' vínculos' : $valor);
        $dato = ['etiqueta' => $etiqueta, 'cantidad' => (int)$grupo->cantidad, 'url' => $enlace(['categoria' => $valor]), 'icono' => $modulo === 'alertas' ? 'ph-flag' : 'ph-door-open', 'seleccionado' => ($filtros['categoria'] ?? '') === $valor];
        if ($modulo === 'alertas') $dato['color'] = match($valor) { 'CRITICA', 'ALTA' => '--rm-danger', 'MEDIA' => '--rm-warning', 'BAJA' => '--rm-info', default => '--rm-chart-reference-500' };
        return $dato;
    });
    $datosTiempo = $historia->map(fn($mes) => ['etiqueta' => \Carbon\Carbon::parse($mes->mes.'-01')->locale('es')->translatedFormat('M Y'), 'cantidad' => (int)$mes->cantidad, 'seleccionado' => ($filtros['desde'] ?? '') === $mes->mes.'-01' && ($filtros['hasta'] ?? '') === \Carbon\Carbon::parse($mes->mes.'-01')->endOfMonth()->toDateString(), 'url' => $enlace(['desde' => $mes->mes.'-01', 'hasta' => \Carbon\Carbon::parse($mes->mes.'-01')->endOfMonth()->toDateString()])]);
    $datosEstados = $estadosAnalisis->map(fn($estado) => ['etiqueta' => $estado->estado ? ucfirst(strtolower(str_replace('_', ' ', $estado->estado))) : 'Sin estado', 'cantidad' => (int)$estado->cantidad, 'seleccionado' => ($filtros['estado'] ?? '') === ($estado->estado ?? '__sin_estado__'), 'url' => $enlace(['estado' => $estado->estado ?? '__sin_estado__'])]);
    $tituloTiempo = match($modulo) { 'jornadas' => 'Jornadas por mes', 'asignaciones' => 'Funciones registradas', 'documentacion' => 'Vencimientos por mes', 'consentimientos' => 'Consentimientos por mes', 'actividades' => 'Distribución por área', 'visitas' => $presentacion['campoFecha'] === 'ingreso' ? 'Entradas registradas por mes' : 'Visitas programadas por mes', 'alertas' => 'Alertas por mes', 'incidentes' => 'Incidentes por mes', default => 'Situación registrada' };
    $datosComparacion = $comparacionOperativa->map(fn($registro) => ['etiqueta' => $registro->titulo ?? ($registro->etiqueta ?: 'Sin función registrada'), 'cantidad' => (int)$registro->cantidad, 'url' => isset($registro->codigo) ? $enlace(['detalle'=>$registro->codigo]) : $enlace(['funcion'=>$registro->etiqueta ?: '__sin_funcion__']), 'ficha' => $registro->codigo ?? null, 'subetiqueta' => isset($registro->codigo) ? ($registro->codigo.' · '.($registro->fecha ? \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y H:i') : 'Fecha sin registrar').(isset($registro->horario) ? ' · '.$registro->horario : '')) : null] + ($modulo === 'actividades' ? ['cupo'=>$registro->cupo] : ['medida'=>'Personal con asignación activa']));
    if($modulo === 'asignaciones') $datosComparacion = $datosComparacion->map(fn($dato)=>array_diff_key($dato,['ficha'=>true]));
    $tituloPrincipal = $modulo === 'actividades' ? 'Participantes y cupo por actividad' : $presentacion['tituloCategoria'];
    $datosPrincipal = $modulo === 'actividades' ? $datosComparacion : $datosCategorias;
    $tituloSecundario = $modulo === 'jornadas' ? 'Personal con asignación activa' : $tituloTiempo;
    $tipoSecundario = $modulo === 'jornadas' ? 'comparacion' : $tipoTiempo;
    $datosSecundario = match($modulo) { 'jornadas','asignaciones' => $datosComparacion, 'actividades' => $datosCategorias, default => $presentacion['campoFecha'] ? $datosTiempo : $datosEstados };
@endphp
<section id="operacion-graficos" class="rm-process-analytics rm-process-analytics--{{ $modulo }}" x-show="graficos" x-transition.opacity aria-label="Resumen de {{ mb_strtolower($definicion['titulo']) }}">
    <div class="rm-process-analytics__intro"><i class="ph-bold {{ $definicion['icono'] }}" aria-hidden="true"></i><p>{{ $lectura }}</p><span>{{ $totalAnalisis }} {{ $presentacion['unidad'] }}</span></div>
    <article class="rm-residents-chart"><header><h2><i class="ph-bold {{ match($tipoCategoria) { 'anillo' => 'ph-chart-donut', 'columnas' => 'ph-chart-bar', 'horarios' => 'ph-clock', 'estaciones' => 'ph-circles-three', default => 'ph-chart-bar-horizontal' } }}" aria-hidden="true"></i>{{ $tituloPrincipal }}</h2><p>{{ $modulo === 'actividades' ? 'Hasta ocho actividades con más participantes registrados. Abre su ficha sin salir de esta ventana.' : 'Hasta ocho grupos de la selección. Elegir uno conserva los otros filtros.' }}</p></header><x-ui.grafico-operativo :tipo="$tipoCategoria" :datos="$datosPrincipal" :etiqueta="$tituloPrincipal" :unidad="$presentacion['unidad']" /></article>
    <article class="rm-residents-chart"><header><h2><i class="ph-bold {{ $presentacion['campoFecha'] ? 'ph-calendar-dots' : 'ph-chart-pie-slice' }}" aria-hidden="true"></i>{{ $tituloSecundario }}</h2><p>{{ in_array($modulo,['jornadas','asignaciones','actividades'],true) ? 'Datos registrados; hasta ocho grupos o registros del contexto de búsqueda' : ($presentacion['campoFecha'] ? 'Hasta ocho meses con datos; meses ausentes no equivalen a cero' : 'Proporción de estados del contexto de búsqueda') }}</p></header><x-ui.grafico-operativo :tipo="$tipoSecundario" :datos="$datosSecundario" :etiqueta="$tituloSecundario" :unidad="$presentacion['unidad']" /></article>
</section>
