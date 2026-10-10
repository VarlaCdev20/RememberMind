@php
    $desdeReporte = $filtros['desde'] ?? now()->startOfMonth()->toDateString();
    $hastaReporte = $filtros['hasta'] ?? now()->toDateString();
    $periodo = ['desde' => $desdeReporte, 'hasta' => $hastaReporte];
    if ($errors->any()) $periodo = array_replace($periodo,collect(session()->getOldInput())->only(['desde','hasta'])->filter(fn ($valor)=>is_scalar($valor) || $valor === null)->all());
    $enlaceReporte = fn($reporte) => route($reporte['ruta'], match($reporte['titulo']) {
        'Ocupación actual' => [],
        'Preadmisiones' => ['fecha_inicio' => $desdeReporte, 'fecha_fin' => $hastaReporte],
        'Visitas' => ['desde' => $desdeReporte, 'hasta' => $hastaReporte, 'tab' => 'todas', 'fecha_visita' => 'ingreso'],
        'Alertas' => ['desde' => $desdeReporte, 'hasta' => $hastaReporte, 'tab' => 'todas'],
        default => ['desde' => $desdeReporte, 'hasta' => $hastaReporte],
    });
    $fechaFuente = fn($titulo) => match($titulo) {
        'Preadmisiones' => 'Fecha de solicitud', 'Admisiones' => 'Fecha de admisión formal', 'Visitas' => 'Entrada registrada',
        'Actividades' => 'Fecha de la actividad', 'Alertas' => 'Fecha de la alerta', 'Incidentes' => 'Fecha del incidente', default => 'Alojamiento vigente ahora',
    };
    $datosIndicador = fn($reporte) => ['titulo'=>$reporte['titulo'], 'total'=>(int)$reporte['total'], 'fuente'=>$fechaFuente($reporte['titulo']), 'desde'=>$desdeReporte, 'hasta'=>$hastaReporte, 'actual'=>$reporte['titulo'] === 'Ocupación actual'];
    $indicadoresPeriodo = collect($reportes)->where('titulo','!=','Ocupación actual')->values();
    $ocupacionActual = collect($reportes)->firstWhere('titulo','Ocupación actual');
@endphp
<div class="rm-residents rm-operations rm-operations--reportes" x-data="rmOperaciones('reportes')" :aria-busy="loading" x-on:livewire:navigated.window="finalizarNavegacion()">
    <x-ui.collection-header title="Reportes administrativos" subtitle="Compara registros por periodo y abre el proceso que necesitas revisar." icon="ph-chart-bar" eyebrow="Administración / Indicadores" :date="now()->locale('es')"><x-slot:actions><button type="button" class="rm-btn-icon" aria-label="Ayuda de reportes" @click="abrirAyuda($event)"><i class="ph-bold ph-question" aria-hidden="true"></i></button><button type="button" class="rm-btn-icon" aria-label="Actualizar reportes" @click="actualizar()"><i class="ph-bold ph-arrow-clockwise" aria-hidden="true"></i></button></x-slot:actions></x-ui.collection-header>
    @include('pages.admin.administracion.partials.guia-operativa')
    @include('pages.admin.administracion.partials.indicador-modal')
    <form class="rm-filter-bar rm-residents-filters" action="{{ route('admin.administracion.reportes') }}" method="GET" x-ref="filtros" @submit.prevent="filtrar()" aria-label="Periodo de reportes">
        @if($errors->any())<p class="rm-alert rm-alert--warning" role="alert">El periodo no se aplicó. Revisa las fechas; los indicadores conservan la última consulta válida.</p><x-validation-errors class="mb-3" />@endif
        <div class="rm-residents-filter-grid"><div><label for="reporte-desde">Desde</label><x-ui.calendario id="reporte-desde" name="desde" label="Fecha desde" :value="$periodo['desde']" :aria-invalid="$errors->has('desde') ? 'true' : 'false'" aria-describedby="reporte-desde-error" />@error('desde')<p id="reporte-desde-error" class="rm-field-error" role="alert">{{ $message }}</p>@enderror</div><div><label for="reporte-hasta">Hasta</label><x-ui.calendario id="reporte-hasta" name="hasta" label="Fecha hasta" :value="$periodo['hasta']" :aria-invalid="$errors->has('hasta') ? 'true' : 'false'" aria-describedby="reporte-hasta-error" />@error('hasta')<p id="reporte-hasta-error" class="rm-field-error" role="alert">{{ $message }}</p>@enderror</div></div>
        <div class="rm-residents-filter-actions"><p><i class="ph-bold ph-calendar-check" aria-hidden="true"></i> {{ \Carbon\Carbon::parse($desdeReporte)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hastaReporte)->format('d/m/Y') }}</p><button type="submit" class="rm-btn-primary" :disabled="loading"><i class="ph-bold ph-funnel" aria-hidden="true"></i> Aplicar periodo</button></div>
    </form>
    <section class="rm-operations-report-grid" aria-label="Indicadores del periodo">
        @forelse($indicadoresPeriodo as $reporte)
            @if($visibilidadNavegacion->puedeVerRuta($reporte['ruta']))<button type="button" class="rm-operation-report" @click="abrirIndicador(@js($datosIndicador($reporte)), $event)"><span><i class="ph-bold {{ $reporte['icono'] }}" aria-hidden="true"></i></span><div><strong>{{ $reporte['total'] }}</strong><h2>{{ $reporte['titulo'] }}</h2><p>{{ $fechaFuente($reporte['titulo']) }}</p><small>Ver lectura del indicador <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></small></div></button>@endif
        @empty<div class="rm-residents-empty"><h2>Sin indicadores autorizados</h2><p>Los indicadores disponibles dependen de los permisos de cada módulo.</p></div>@endforelse
    </section>
    @if($ocupacionActual && $visibilidadNavegacion->puedeVerRuta($ocupacionActual['ruta']))<section class="rm-operation-snapshot" aria-label="Ocupación actual"><span class="rm-operations-context__icon"><i class="ph-bold ph-bed" aria-hidden="true"></i></span><div><h2>{{ $ocupacionActual['total'] }} camas ocupadas ahora</h2><p>Alojamiento vigente · no depende del periodo elegido</p></div><button type="button" class="rm-btn-secondary" @click="abrirIndicador(@js($datosIndicador($ocupacionActual)), $event)">Ver indicador <i class="ph-bold ph-eye" aria-hidden="true"></i></button></section>@endif
    <section class="rm-collection-results rm-operation-report-chart" aria-label="Comparación del periodo"><header class="rm-residents-results__header"><div><h2><i class="ph-bold ph-chart-bar" aria-hidden="true"></i> Registros por proceso</h2><p>Son procesos distintos; no se suman como personas únicas. Selecciona un proceso para conocer cómo se cuenta.</p></div></header><div class="rm-operation-report-comparison"><x-ui.grafico-operativo tipo="barras" :datos="$indicadoresPeriodo->sortByDesc('total')->values()->map(fn($reporte)=>['etiqueta'=>$reporte['titulo'],'cantidad'=>(int)$reporte['total'],'url'=>'#indicador','indicador'=>$datosIndicador($reporte)])" etiqueta="Comparación de procesos en el periodo" /></div></section>
</div>