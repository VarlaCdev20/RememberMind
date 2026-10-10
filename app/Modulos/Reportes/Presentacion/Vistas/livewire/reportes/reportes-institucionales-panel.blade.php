<div class="relative z-10 space-y-6 py-8 antialiased text-parrafo print:bg-[var(--rm-surface-raised)] print:py-0"
 x-data="reporteGraficas()"
 x-init="initCharts()"
>
 <!-- CONTENEDOR DE DATOS SERIALIZADOS PARA ALPINE/CHART.JS -->
 <div id="chart-data-container"
 data-estados='@json($graficaEstados)'
 data-edades='@json($graficaEdades)'
 data-niveles='@json($graficaNiveles)'
 data-areas='@json($graficaAreas)'
 data-documentos='@json($graficaDocumentos)'
 data-seguimiento='@json($graficaSeguimiento)'
 data-tipo-reporte="{{ $tipoReporte }}"
 class="hidden"></div>

 <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 print:max-w-full print:px-0">
 <x-ui.page-header
    class="mb-6"
    title="Reportes Institucionales"
    subtitle="Indicadores generales, exportaciones y análisis institucional de residentes."
    overline="Dirección y Gestión"
    icon="ph-chart-bar"
    :date="now()">
    <!-- Botones Reales de Acción -->
    <div class="flex items-center gap-3 print:hidden">
 <button
 type="button"
 onclick="window.print()"
 class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-3 text-xs font-bold uppercase tracking-wider text-inverso shadow-md transition-all duration-300 hover:bg-boton-acento-dark hover:scale-105 active:scale-95"
 >
 <i class="ph-bold ph-printer text-base"></i>
 Vista de Impresión / PDF
 </button>
 </div>
 </x-ui.page-header>

     <!-- ── SECCIÓN DE FILTROS FORMATO ALERTAS ─────────────────── -->
    <x-ui.filter-bar class="mb-8 print:hidden">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            <!-- Rango de Fecha: Desde -->
            <div class="lg:col-span-3">
                <input
                    type="date"
                    id="fechaDesde"
                    wire:model.live="fechaDesde"
                    title="Fecha Ingreso Desde"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]"
                />
            </div>

            <!-- Rango de Fecha: Hasta -->
            <div class="lg:col-span-3">
                <input
                    type="date"
                    id="fechaHasta"
                    wire:model.live="fechaHasta"
                    title="Fecha Ingreso Hasta"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]"
                />
            </div>

            <!-- Estado Institucional -->
            <div class="lg:col-span-3">
                <select
                    id="filtroEstado"
                    wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]"
                >
                    <option value="todos">Todos los estados</option>
                    @foreach($estadosList as $est)
                        <option value="{{ $est->cod_est_adul }}">{{ $est->estado }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipo de Reporte Específico -->
            <div class="lg:col-span-3">
                <select
                    id="tipoReporte"
                    wire:model.live="tipoReporte"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]"
                >
                    <option value="general">1. Reporte General</option>
                    <option value="documental">2. Reporte Documental</option>
                    <option value="red_de_apoyo">3. Red de Apoyo</option>
                    <option value="salud">4. Salud y Cuidados</option>
                    <option value="evaluaciones">5. Evaluaciones Geriátricas</option>
                    <option value="seguimiento">6. Seguimiento Institucional</option>
                    <option value="trazabilidad">7. Trazabilidad</option>
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($fechaDesde) || !empty($fechaHasta) || ($filtroEstado !== 'todos') || ($tipoReporte !== 'general');
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($fechaDesde))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Desde: {{ $fechaDesde }}</span>
                            <button type="button" wire:click="$set('fechaDesde', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($fechaHasta))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Hasta: {{ $fechaHasta }}</span>
                            <button type="button" wire:click="$set('fechaHasta', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroEstado !== 'todos')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Estado seleccionado</span>
                            <button type="button" wire:click="$set('filtroEstado', 'todos')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($tipoReporte !== 'general')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Sección: {{ $tipoReporte }}</span>
                            <button type="button" wire:click="$set('tipoReporte', 'general')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('fechaDesde', ''); $set('fechaHasta', ''); $set('filtroEstado', 'todos'); $set('tipoReporte', 'general')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 <!-- ── TARJETAS DE INDICADORES SUPERIORES ───────────────── -->
 <div class="mb-8 grid gap-4 grid-cols-2 md:grid-cols-6 print:grid-cols-3 print:gap-2">
 <!-- Total -->
 <div class="rm-metric-card p-4">
 <p class="text-[9px] font-bold uppercase tracking-wider text-meta">Total Adultos</p>
 <p class="mt-2 text-2xl font-black text-titulo">{{ $indicadores['total'] }}</p>
 </div>

 <!-- Activos -->
 <div class="rm-metric-card p-4">
 <p class="text-[9px] font-bold uppercase tracking-wider text-meta">Activos</p>
 <p class="mt-2 text-2xl font-black text-estado-exito">{{ $indicadores['activos'] }}</p>
 </div>

 <!-- Sin Documentos -->
 <div class="rm-metric-card p-4">
 <p class="text-[9px] font-bold uppercase tracking-wider text-meta">Sin Expediente</p>
 <p class="mt-2 text-2xl font-black text-estado-advertencia">{{ $indicadores['sin_documentos'] }}</p>
 </div>

 <!-- Sin Evaluación Geriátrica -->
 <div class="rm-metric-card p-4">
 <p class="text-[9px] font-bold uppercase tracking-wider text-meta">Sin Evaluación</p>
 <p class="mt-2 text-2xl font-black text-estado-peligro">{{ $indicadores['sin_evaluacion'] }}</p>
 </div>

 <!-- Seguimiento Prioritario -->
 <div class="rm-metric-card p-4">
 <p class="text-[9px] font-bold uppercase tracking-wider text-meta">Urgentes / Alta</p>
 <p class="mt-2 text-2xl font-black text-estado-peligro">{{ $indicadores['urgentes'] }}</p>
 </div>

 <!-- Pendientes -->
 <div class="rm-metric-card p-4">
 <p class="text-[9px] font-bold uppercase tracking-wider text-meta">Pendientes</p>
 <p class="mt-2 text-2xl font-black text-estado-advertencia">{{ $indicadores['pendientes'] }}</p>
 </div>
 </div>

 <!-- ── CONTENIDO DEL REPORTE ────────────────────────────── -->
 <div class="grid gap-8 lg:grid-cols-3 print:grid-cols-1">
 
 <!-- SECCIÓN DE MÉTRICAS DETALLADAS (Izquierda/Centro) -->
 <div class="lg:col-span-2 space-y-6 print:lg:col-span-3">
 <div class="rm-card p-6 print:border-none print:shadow-none print:p-0">
 
 @if($stats['total'] === 0)
 <!-- ESTADO VACÍO -->
 <div class="py-12 text-center">
 <i class="ph ph-files text-6xl text-meta mb-4"></i>
 <h3 class="text-base font-extrabold text-titulo">Sin datos suficientes para generar este reporte.</h3>
 <p class="text-xs text-meta mt-1">Intente ampliando el rango de fechas o seleccionando otro filtro de estado.</p>
 </div>
 @else
 <!-- CATEGORÍA 1: REPORTE GENERAL -->
 @if($tipoReporte === 'general')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 1. Reporte General de Población
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Distribución de Estados</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Activos:</span>
 <span class="font-black text-estado-exito">{{ $stats['activos'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Inactivos / Archivados:</span>
 <span class="font-black text-estado-peligro">{{ $stats['archivados'] }}</span>
 </li>
 </ul>
 </div>

 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Distribución por Género</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Femenino:</span>
 <span class="font-black text-titulo">{{ $stats['mujeres'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Masculino:</span>
 <span class="font-black text-titulo">{{ $stats['hombres'] }}</span>
 </li>
 </ul>
 </div>

 <div class="rounded-2xl bg-fondo-panel p-5 sm:col-span-2">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Distribución por Rangos de Edad</h4>
 <div class="grid grid-cols-2 gap-4 mt-4">
 <div class="flex justify-between text-xs font-bold">
 <span>Menores de 65:</span>
 <span class="font-black text-titulo">{{ $stats['menores65'] }}</span>
 </div>
 <div class="flex justify-between text-xs font-bold">
 <span>65 a 75 años:</span>
 <span class="font-black text-titulo">{{ $stats['de65a75'] }}</span>
 </div>
 <div class="flex justify-between text-xs font-bold">
 <span>76 a 85 años:</span>
 <span class="font-black text-titulo">{{ $stats['de76a85'] }}</span>
 </div>
 <div class="flex justify-between text-xs font-bold">
 <span>Mayores de 85:</span>
 <span class="font-black text-titulo">{{ $stats['mayores85'] }}</span>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- CATEGORÍA 2: REPORTE DOCUMENTAL -->
 @elseif($tipoReporte === 'documental')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 2. Reporte Documental e Historial
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Expediente Digital</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Con documentos:</span>
 <span class="font-black text-estado-exito">{{ $stats['conDocumentos'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Sin documentos registrados:</span>
 <span class="font-black text-estado-peligro">{{ $stats['sinDocumentos'] }}</span>
 </li>
 </ul>
 </div>

 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Archivos Digitalizados</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Documentos Activos:</span>
 <span class="font-black text-estado-exito">{{ $stats['documentosActivos'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Anulados / Archivados:</span>
 <span class="font-black text-estado-peligro">{{ $stats['documentosAnulados'] }}</span>
 </li>
 </ul>
 </div>
 </div>
 </div>

 <!-- CATEGORÍA 3: REPORTE RED DE APOYO -->
 @elseif($tipoReporte === 'red_de_apoyo')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 3. Red de Apoyo Familiar
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Vínculos de Apoyo</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Con familiares asociados:</span>
 <span class="font-black text-estado-exito">{{ $stats['conFamiliar'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Sin familiar registrado:</span>
 <span class="font-black text-estado-peligro">{{ $stats['sinFamiliar'] }}</span>
 </li>
 </ul>
 </div>

 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Críticos y Responsabilidad</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Sin responsable principal:</span>
 <span class="font-black text-estado-advertencia">{{ $stats['sinResponsable'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Sin contacto de emergencia:</span>
 <span class="font-black text-estado-peligro">{{ $stats['sinContacto'] }}</span>
 </li>
 </ul>
 </div>
 </div>
 </div>

 <!-- CATEGORÍA 4: REPORTE SALUD Y CUIDADOS -->
 @elseif($tipoReporte === 'salud')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 4. Salud y Cuidados
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Ficha Médica Básica</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Con ficha registrada:</span>
 <span class="font-black text-estado-exito">{{ $stats['conFicha'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Sin ficha registrada:</span>
 <span class="font-black text-estado-peligro">{{ $stats['sinFicha'] }}</span>
 </li>
 </ul>
 </div>

 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Monitoreo Clínico</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Con signos vitales registrados:</span>
 <span class="font-black text-estado-exito">{{ $stats['conSignos'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Con valoración funcional registrada:</span>
 <span class="font-black text-estado-info">{{ $stats['conValoracion'] }}</span>
 </li>
 </ul>
 </div>
 </div>
 </div>

 <!-- CATEGORÍA 5: REPORTE EVALUACIONES GERIÁTRICAS -->
 @elseif($tipoReporte === 'evaluaciones')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 5. Evaluaciones Geriátricas Integrales
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <div class="rounded-2xl bg-fondo-panel p-5 sm:col-span-2 flex justify-between items-center">
 <div>
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Evaluaciones Totales</h4>
 <p class="mt-1 text-2xl font-black text-titulo">{{ $stats['totalEvaluaciones'] }} realizadas</p>
 </div>
 <div class="text-right">
 <span class="text-xs font-bold text-estado-peligro">Sin evaluación: {{ $stats['sinEvaluacion'] }}</span>
 </div>
 </div>

 <!-- Por Área -->
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Por Área Geriátrica</h4>
 <ul class="mt-4 space-y-2">
 @forelse($stats['evaluacionesPorArea'] as $area => $total)
 <li class="flex justify-between text-xs font-bold border-b border-borde-suave pb-1.5">
 <span>{{ $area }}:</span>
 <span class="font-black text-titulo">{{ $total }}</span>
 </li>
 @empty
 <li class="text-xs font-bold text-meta py-2">Sin registros.</li>
 @endforelse
 </ul>
 </div>

 <!-- Por Alerta -->
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Por Nivel de Alerta</h4>
 <ul class="mt-4 space-y-2">
 <li class="flex justify-between text-xs font-bold border-b border-borde-suave pb-1.5">
 <span>NORMAL:</span>
 <span class="font-black text-estado-exito">{{ $stats['evaluacionesPorNivel']['NORMAL'] ?? 0 }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold border-b border-borde-suave pb-1.5">
 <span>PREVENTIVO:</span>
 <span class="font-black text-estado-advertencia">{{ $stats['evaluacionesPorNivel']['PREVENTIVO'] ?? 0 }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold pb-1.5">
 <span>CRÍTICO:</span>
 <span class="font-black text-estado-peligro">{{ $stats['evaluacionesPorNivel']['CRITICO'] ?? 0 }}</span>
 </li>
 </ul>
 </div>
 </div>
 </div>

 <!-- CATEGORÍA 6: REPORTE DE SEGUIMIENTO -->
 @elseif($tipoReporte === 'seguimiento')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 6. Seguimiento Institucional
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <!-- Eventos de seguimiento -->
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Eventos de Seguimiento</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Observaciones registradas:</span>
 <span class="font-black text-titulo">{{ $stats['observaciones'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold text-estado-peligro font-black">
 <span>Importancia Alta / Urgente:</span>
 <span>{{ $stats['observacionesAlta'] }}</span>
 </li>
 </ul>
 </div>

 <!-- Agenda -->
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Agenda y Pendientes</h4>
 <ul class="mt-4 space-y-3">
 <li class="flex justify-between text-xs font-bold">
 <span>Atenciones registradas:</span>
 <span class="font-black text-estado-info">{{ $stats['atenciones'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold">
 <span>Actividades registradas:</span>
 <span class="font-black text-titulo">{{ $stats['actividades'] }}</span>
 </li>
 <li class="flex justify-between text-xs font-bold text-estado-advertencia">
 <span>Acciones Pendientes:</span>
 <span>{{ $stats['pendientesSeguimiento'] }}</span>
 </li>
 </ul>
 </div>
 </div>
 </div>

 <!-- CATEGORÍA 7: REPORTE DE TRAZABILIDAD -->
 @elseif($tipoReporte === 'trazabilidad')
 <div>
 <h3 class="text-lg font-extrabold uppercase tracking-wider text-titulo border-b border-borde-suave pb-3 mb-6">
 7. Trazabilidad y Auditoría
 </h3>
 <div class="grid gap-6 sm:grid-cols-2">
 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Historial de Estados</h4>
 <p class="mt-2 text-3xl font-black text-titulo">{{ $stats['cambiosEstado'] }}</p>
 <p class="text-[10px] font-bold text-meta mt-1">Cambios en el estado institucional del adulto mayor.</p>
 </div>

 <div class="rounded-2xl bg-fondo-panel p-5">
 <h4 class="text-xs font-bold uppercase tracking-widest text-apoyo">Actividad de Bitácora</h4>
 <p class="mt-2 text-3xl font-black text-estado-info">{{ $stats['actividadBitacora'] }}</p>
 <p class="text-[10px] font-bold text-meta mt-1">Registros de acciones auditados en el sistema.</p>
 </div>
 </div>
 </div>
 @endif
 @endif
 </div>
 </div>

 <!-- COLUMNA DE GR?FICAS FORMATO UNIFICADO ENFERMER?A (Derecha) -->
        <div class="space-y-6">
            @if($stats['total'] === 0)
                <div class="rm-chart-card rm-chart-glass p-8 text-center">
                    <x-ui.empty-state compact icono="ph-chart-line" titulo="Sin datos para graficar" texto="No hay registros suficientes en el periodo seleccionado para generar las m?tricas visuales." />
                </div>
            @else
                <!-- Gr?ficas del Reporte General -->
                @if($tipoReporte === 'general')
                    <div class="rm-chart-card rm-chart-glass space-y-3">
                        <div class="rm-chart-header">
                            <div>
                                <h3 class="rm-chart-title">Estado Institucional</h3>
                                <p class="rm-chart-subtitle">Residentes activos, egresados y temporalidades</p>
                            </div>
                            <span class="rm-chart-kpi-badge">
                                <i class="ph-bold ph-chart-donut text-xs text-[var(--rm-action-primary)]"></i> Estados
                            </span>
                        </div>
                        <div class="rm-chart-body is-sm relative w-full" wire:ignore>
                            <canvas id="chartEstados"></canvas>
                        </div>
                    </div>

                    <div class="rm-chart-card rm-chart-glass space-y-3">
                        <div class="rm-chart-header">
                            <div>
                                <h3 class="rm-chart-title">Rangos de Edad</h3>
                                <p class="rm-chart-subtitle">Estratificaci?n decenal de residentes</p>
                            </div>
                            <span class="rm-chart-kpi-badge">
                                <i class="ph-bold ph-chart-bar text-xs text-[var(--rm-clinical)]"></i> Edades
                            </span>
                        </div>
                        <div class="rm-chart-body is-sm relative w-full" wire:ignore>
                            <canvas id="chartEdades"></canvas>
                        </div>
                    </div>

                <!-- Gr?ficas del Reporte Documental -->
                @elseif($tipoReporte === 'documental')
                    <div class="rm-chart-card rm-chart-glass space-y-3">
                        <div class="rm-chart-header">
                            <div>
                                <h3 class="rm-chart-title">Expedientes Digitalizados</h3>
                                <p class="rm-chart-subtitle">Volumen de documentaci?n por tipo de archivo</p>
                            </div>
                            <span class="rm-chart-kpi-badge">
                                <i class="ph-bold ph-files text-xs text-[var(--rm-warning)]"></i> Documentos
                            </span>
                        </div>
                        <div class="rm-chart-body is-md relative w-full" wire:ignore>
                            <canvas id="chartDocumentos"></canvas>
                        </div>
                    </div>

                <!-- Gr?ficas del Reporte de Red de Apoyo -->
                @elseif($tipoReporte === 'red_de_apoyo')
                    <div class="rm-chart-card rm-chart-glass p-8 text-center">
                        <x-ui.empty-state compact icono="ph-users-three" titulo="Red de Apoyo" texto="Sin datos suficientes para generar esta gr?fica en el periodo." />
                    </div>

                <!-- Gr?ficas del Reporte de Salud -->
                @elseif($tipoReporte === 'salud')
                    <div class="rm-chart-card rm-chart-glass p-8 text-center">
                        <x-ui.empty-state compact icono="ph-heartbeat" titulo="Reporte de Salud" texto="Sin datos suficientes para generar esta gr?fica en el periodo." />
                    </div>

                <!-- Gr?ficas del Reporte de Evaluaciones -->
                @elseif($tipoReporte === 'evaluaciones')
                    <div class="rm-chart-card rm-chart-glass space-y-3">
                        <div class="rm-chart-header">
                            <div>
                                <h3 class="rm-chart-title">Niveles de Alerta</h3>
                                <p class="rm-chart-subtitle">Severidad de observaciones registradas</p>
                            </div>
                            <span class="rm-chart-kpi-badge">
                                <i class="ph-bold ph-bell-ringing text-xs text-[var(--rm-danger)]"></i> Alertas
                            </span>
                        </div>
                        <div class="rm-chart-body is-sm relative w-full" wire:ignore>
                            <canvas id="chartNiveles"></canvas>
                        </div>
                    </div>

                    <div class="rm-chart-card rm-chart-glass space-y-3">
                        <div class="rm-chart-header">
                            <div>
                                <h3 class="rm-chart-title">?reas Geri?tricas Evaluadas</h3>
                                <p class="rm-chart-subtitle">Frecuencia por dimensi?n cl?nica</p>
                            </div>
                            <span class="rm-chart-kpi-badge">
                                <i class="ph-bold ph-activity text-xs text-[var(--rm-clinical)]"></i> ?reas
                            </span>
                        </div>
                        <div class="rm-chart-body is-sm relative w-full" wire:ignore>
                            <canvas id="chartAreas"></canvas>
                        </div>
                    </div>

                <!-- Gr?ficas de Seguimiento -->
                @elseif($tipoReporte === 'seguimiento')
                    <div class="rm-chart-card rm-chart-glass space-y-3">
                        <div class="rm-chart-header">
                            <div>
                                <h3 class="rm-chart-title">Eventos de Seguimiento</h3>
                                <p class="rm-chart-subtitle">Evoluci?n y frecuencia de notas cl?nicas</p>
                            </div>
                            <span class="rm-chart-kpi-badge">
                                <i class="ph-bold ph-clipboard-text text-xs text-[var(--rm-action-primary)]"></i> Seguimiento
                            </span>
                        </div>
                        <div class="rm-chart-body is-md relative w-full" wire:ignore>
                            <canvas id="chartSeguimiento"></canvas>
                        </div>
                    </div>

                <!-- Gr?ficas de Trazabilidad -->
                @elseif($tipoReporte === 'trazabilidad')
                    <div class="rm-chart-card rm-chart-glass p-8 text-center">
                        <x-ui.empty-state compact icono="ph-clock-counter-clockwise" titulo="Trazabilidad" texto="Sin datos suficientes para generar esta gr?fica en el periodo." />
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- ── FOOTER DE FIRMA INSTITUCIONAL PARA IMPRESIÓN ──────── -->
 <div class="hidden print:block mt-20 text-center border-t border-slate-300 pt-8">
 <p class="text-xs font-bold uppercase tracking-widest text-slate-800">
 RememberMind — Suite de Gestión"CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
 </p>
 <p class="text-[10px] text-slate-500 mt-1">
 Generado el {{ date('d/m/Y H:i:s') }} por el personal institucional autorizado.
 </p>
 </div>

 <!-- ── SCRIPTS DE ALPINE Y CHART.JS ───────────────────────── -->
 <script>
 function reporteGraficas() {
 return {
 charts: {},
 initCharts() {
 // Inicializar y graficar al inicio
 this.renderCharts();

 // Registrar hook en Livewire para renderizar de nuevo al finalizar cada commit
 document.addEventListener('livewire:initialized', () => {
 Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
 succeed(({ snapshot, effect }) => {
 queueMicrotask(() => {
 this.renderCharts();
 });
 });
 });
 });
 },
 renderCharts() {
 const container = document.getElementById('chart-data-container');
 if (!container) return;

 const estados = JSON.parse(container.getAttribute('data-estados') || '{}');
 const edades = JSON.parse(container.getAttribute('data-edades') || '{}');
 const niveles = JSON.parse(container.getAttribute('data-niveles') || '{}');
 const areas = JSON.parse(container.getAttribute('data-areas') || '{}');
 const documentos = JSON.parse(container.getAttribute('data-documentos') || '{}');
 const seguimiento = JSON.parse(container.getAttribute('data-seguimiento') || '{}');
 const tipoReporte = container.getAttribute('data-tipo-reporte');

 const previousCharts = this.charts;
 this.charts = {};

                    const chartDesign = window.RMCharts;
                    const palette = chartDesign.palette();
                    const toTranslucent = (color, token) => chartDesign.hexToRgba(color, chartDesign.number(token, .84));
                    const legend = { display: true, position: 'bottom', labels: { usePointStyle: true, font: { family: chartDesign.getCss('--rm-chart-font-family'), size: chartDesign.number('--rm-chart-legend-size', 12), weight: '600' } } };

                    // 1. Dona: Adultos por Estado (Anillo grueso y translúcido con giro)
                    const ctxEstados = document.getElementById('chartEstados');
                    if (ctxEstados && Object.keys(estados).length > 0) {
                        const rawColors = [palette[0], palette[2], palette[1], palette[3], palette[4]];
                        this.charts.estados = chartDesign.init('reporte-institucional-estados', ctxEstados, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(estados),
                                datasets: [{
                                    data: Object.values(estados),
                                    backgroundColor: rawColors.map(c => toTranslucent(c, '--rm-donut-ring-opacity')),
                                    borderColor: rawColors.map(c => toTranslucent(c, '--rm-chart-state-hover')),
                                    borderWidth: 2,
                                    hoverOffset: 8,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: chartDesign.getCss('--rm-donut-cutout'),
                        borderRadius: chartDesign.number('--rm-donut-gap', 2),
                        spacing: chartDesign.number('--rm-donut-gap', 2),
                                animation: {
                                    duration: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : chartDesign.number('--rm-chart-donut-enter-duration', 750),
                                    easing: chartDesign.getCss('--rm-chart-js-easing'),
                                    animateRotate: true,
                                    animateScale: false,
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 10,
                                            font: legend.labels.font,
                                            padding: 10
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 2. Barras: Adultos por Rango de Edad (Barras gruesas translúcidas)
                    const ctxEdades = document.getElementById('chartEdades');
                    if (ctxEdades && Object.values(edades).some(v => v > 0)) {
                        this.charts.edades = chartDesign.init('reporte-institucional-edades', ctxEdades, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(edades),
                                datasets: [{
                                    data: Object.values(edades),
                                    backgroundColor: toTranslucent(chartDesign.color('neutral'), '--rm-bar-fill-opacity'),
                                    borderColor: chartDesign.color('neutral'),
                                    borderWidth: 1.5,
                                    borderRadius: chartDesign.number('--rm-bar-radius', 10),
                                    barPercentage: 0.86,
                                    categoryPercentage: 0.90,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : chartDesign.number('--rm-chart-bar-enter-duration', 600), easing: chartDesign.getCss('--rm-chart-js-easing') },
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { display: false } },
                                    y: { beginAtZero: true, ticks: { precision: 0 } }
                                }
                            }
                        });
                    }

                    // 3. Dona: Evaluaciones por Nivel de Alerta (Anillo grueso semántico)
                    const ctxNiveles = document.getElementById('chartNiveles');
                    if (ctxNiveles && tipoReporte === 'evaluaciones' && Object.values(niveles).some(v => v > 0)) {
                        const sem = chartDesign.semanticColors();
                        const rawNivelColors = [sem.danger, sem.warning, sem.success, sem.info];
                        this.charts.niveles = chartDesign.init('reporte-institucional-niveles', ctxNiveles, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(niveles),
                                datasets: [{
                                    data: Object.values(niveles),
                                    backgroundColor: rawNivelColors.map(c => toTranslucent(c, '--rm-donut-ring-opacity')),
                                    borderColor: rawNivelColors.map(c => toTranslucent(c, '--rm-chart-state-hover')),
                                    borderWidth: 2,
                                    hoverOffset: 8,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: chartDesign.getCss('--rm-donut-cutout'),
                        borderRadius: chartDesign.number('--rm-donut-gap', 2),
                        spacing: chartDesign.number('--rm-donut-gap', 2),
                                animation: {
                                    duration: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : chartDesign.number('--rm-chart-donut-enter-duration', 750),
                                    easing: chartDesign.getCss('--rm-chart-js-easing'),
                                    animateRotate: true,
                                    animateScale: false,
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: legend.labels.font }
                                    }
                                }
                            }
                        });
                    }

                    // 4. PolarArea: Evaluaciones por Área
                    const ctxAreas = document.getElementById('chartAreas');
                    if (ctxAreas && tipoReporte === 'evaluaciones' && Object.keys(areas).length > 0) {
                        const areaPalette = palette.map(c => toTranslucent(c, '--rm-chart-state-secondary'));
                        this.charts.areas = chartDesign.init('reporte-institucional-areas', ctxAreas, {
                            type: 'polarArea',
                            data: {
                                labels: Object.keys(areas),
                                datasets: [{
                                    data: Object.values(areas),
                                    backgroundColor: areaPalette,
                                    borderColor: palette,
                                    borderWidth: 1.5,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : chartDesign.number('--rm-chart-donut-enter-duration', 750), easing: chartDesign.getCss('--rm-chart-js-easing'), animateScale: false, animateRotate: true },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: legend.labels.font }
                                    }
                                }
                            }
                        });
                    }

                    // 5. Pie: Documentos Activos vs Anulados
                    const ctxDocumentos = document.getElementById('chartDocumentos');
                    if (ctxDocumentos && tipoReporte === 'documental' && Object.values(documentos).some(v => v > 0)) {
                        const docColors = [palette[2], palette[1], palette[3]];
                        this.charts.documentos = chartDesign.init('reporte-institucional-documentos', ctxDocumentos, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(documentos),
                                datasets: [{
                                    data: Object.values(documentos),
                                    backgroundColor: docColors.map(c => toTranslucent(c, '--rm-donut-ring-opacity')),
                                    borderColor: docColors.map(c => toTranslucent(c, '--rm-chart-state-hover')),
                                    borderWidth: 2,
                                    hoverOffset: 8,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : chartDesign.number('--rm-chart-donut-enter-duration', 750), easing: chartDesign.getCss('--rm-chart-js-easing'), animateRotate: true, animateScale: false },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: legend.labels.font }
                                    }
                                }
                            }
                        });
                    }

                    // 6. Barras: Seguimiento por Tipo (Barras gruesas translúcidas)
                    const ctxSeguimiento = document.getElementById('chartSeguimiento');
                    if (ctxSeguimiento && tipoReporte === 'seguimiento' && Object.values(seguimiento).some(v => v > 0)) {
                        this.charts.seguimiento = chartDesign.init('reporte-institucional-seguimiento', ctxSeguimiento, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(seguimiento),
                                datasets: [{
                                    data: Object.values(seguimiento),
                                    backgroundColor: toTranslucent(chartDesign.color('clinical'), '--rm-bar-fill-opacity'),
                                    borderColor: palette[1],
                                    borderWidth: 1.5,
                                    borderRadius: chartDesign.number('--rm-bar-radius', 10),
                                    barPercentage: 0.86,
                                    categoryPercentage: 0.90,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : chartDesign.number('--rm-chart-bar-enter-duration', 600), easing: chartDesign.getCss('--rm-chart-js-easing') },
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { display: false } },
                                    y: { beginAtZero: true, ticks: { precision: 0 } }
                                }
                            }
                        });
                    }
                    Object.keys(previousCharts || {}).forEach(key => {
                        if (!this.charts[key]) chartDesign.destroy(`reporte-institucional-${key}`);
                    });

 }
 }
 }
 </script>

 <!-- ── HOJA DE ESTILOS DE IMPRESIÓN AD-HOC ─────────────────── -->
 <style>
 @media print {
 body {
 background-color: white !important;
 color: black !important;
 font-family: 'Outfit', sans-serif !important;
 }
 .mx-auto {
 margin: 0 !important;
 max-width: 100% !important;
 padding: 0 !important;
 }
 aside, nav, header, footer, .print\:hidden, input, select, button {
 display: none !important;
 }
 .rounded-3xl, .rounded-2xl {
 border-radius: 0 !important;
 border: none !important;
 background-color: transparent !important;
 }
 canvas {
 max-height: 250px !important;
 }
 .print\:break-before-page {
 break-before: page !important;
 }
 }
 </style>
</div>
