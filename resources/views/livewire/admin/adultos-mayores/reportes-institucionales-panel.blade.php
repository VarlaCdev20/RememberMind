<div class="min-h-screen bg-[#F7F5F2] py-8 font-sans antialiased text-[#2F3E5C] print:bg-white print:py-0"
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
        <!-- ── ENCABEZADO ────────────────────────────────────────── -->
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center print:mb-6 print:border-b-2 print:border-slate-800 print:pb-4">
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tight text-azul-profundo sm:text-4xl print:text-2xl print:text-black">
                    Reportes Institucionales
                </h1>
                <p class="mt-1 text-sm font-bold text-azul-profundo/60 print:text-xs print:text-slate-600">
                    Indicadores generales, exportaciones y análisis institucional del módulo Adultos Mayores.
                </p>
            </div>

            <!-- Botones Reales de Acción -->
            <div class="flex items-center gap-3 print:hidden">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl bg-terracota px-5 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md transition-all duration-300 hover:bg-terracota-dark hover:scale-105 active:scale-95"
                >
                    <i class="ph-bold ph-printer text-base"></i>
                    Vista de Impresión / PDF
                </button>
            </div>
        </div>

        <!-- ── SECCIÓN DE FILTROS ─────────────────────────────────── -->
        <div class="mb-8 rounded-3xl border border-[#C7B5A3]/40 bg-white p-6 shadow-sm print:hidden">
            <h2 class="mb-4 text-xs font-black uppercase tracking-widest text-azul-profundo/70 flex items-center gap-2">
                <i class="ph-bold ph-funnel text-terracota"></i>
                Filtros de Análisis Institucional
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <!-- Rango de Fecha: Desde -->
                <div class="flex flex-col gap-1.5">
                    <label for="fechaDesde" class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
                        Fecha Ingreso Desde
                    </label>
                    <input
                        type="date"
                        id="fechaDesde"
                        wire:model.live="fechaDesde"
                        class="rounded-xl border border-[#C7B5A3]/60 bg-[#F7F5F2] px-3.5 py-2.5 text-xs font-bold text-azul-profundo focus:border-terracota focus:outline-none focus:ring-1 focus:ring-terracota"
                    />
                </div>

                <!-- Rango de Fecha: Hasta -->
                <div class="flex flex-col gap-1.5">
                    <label for="fechaHasta" class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
                        Fecha Ingreso Hasta
                    </label>
                    <input
                        type="date"
                        id="fechaHasta"
                        wire:model.live="fechaHasta"
                        class="rounded-xl border border-[#C7B5A3]/60 bg-[#F7F5F2] px-3.5 py-2.5 text-xs font-bold text-azul-profundo focus:border-terracota focus:outline-none focus:ring-1 focus:ring-terracota"
                    />
                </div>

                <!-- Estado Institucional -->
                <div class="flex flex-col gap-1.5">
                    <label for="filtroEstado" class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
                        Estado Institucional
                    </label>
                    <select
                        id="filtroEstado"
                        wire:model.live="filtroEstado"
                        class="rounded-xl border border-[#C7B5A3]/60 bg-[#F7F5F2] px-3.5 py-2.5 text-xs font-bold text-azul-profundo focus:border-terracota focus:outline-none focus:ring-1 focus:ring-terracota"
                    >
                        <option value="todos">Todos los estados</option>
                        @foreach($estadosList as $est)
                            <option value="{{ $est->cod_est_adul }}">{{ $est->estado }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo de Reporte Específico -->
                <div class="flex flex-col gap-1.5">
                    <label for="tipoReporte" class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
                        Sección / Categoría
                    </label>
                    <select
                        id="tipoReporte"
                        wire:model.live="tipoReporte"
                        class="rounded-xl border border-[#C7B5A3]/60 bg-[#F7F5F2] px-3.5 py-2.5 text-xs font-bold text-azul-profundo focus:border-terracota focus:outline-none focus:ring-1 focus:ring-terracota"
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
        </div>

        <!-- ── TARJETAS DE INDICADORES SUPERIORES ───────────────── -->
        <div class="mb-8 grid gap-4 grid-cols-2 md:grid-cols-6 print:grid-cols-3 print:gap-2">
            <!-- Total -->
            <div class="rounded-2xl border border-[#C7B5A3]/30 bg-white p-4 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-azul-profundo/50">Total Adultos</p>
                <p class="mt-2 text-2xl font-black text-azul-profundo">{{ $indicadores['total'] }}</p>
            </div>

            <!-- Activos -->
            <div class="rounded-2xl border border-[#C7B5A3]/30 bg-white p-4 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-azul-profundo/50">Activos</p>
                <p class="mt-2 text-2xl font-black text-emerald-600">{{ $indicadores['activos'] }}</p>
            </div>

            <!-- Sin Documentos -->
            <div class="rounded-2xl border border-[#C7B5A3]/30 bg-white p-4 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-azul-profundo/50">Sin Expediente</p>
                <p class="mt-2 text-2xl font-black text-amber-600">{{ $indicadores['sin_documentos'] }}</p>
            </div>

            <!-- Sin Evaluación Geriátrica -->
            <div class="rounded-2xl border border-[#C7B5A3]/30 bg-white p-4 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-azul-profundo/50">Sin Evaluación</p>
                <p class="mt-2 text-2xl font-black text-rose-600">{{ $indicadores['sin_evaluacion'] }}</p>
            </div>

            <!-- Seguimiento Prioritario -->
            <div class="rounded-2xl border border-[#C7B5A3]/30 bg-white p-4 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-azul-profundo/50">Urgentes / Alta</p>
                <p class="mt-2 text-2xl font-black text-rose-700">{{ $indicadores['urgentes'] }}</p>
            </div>

            <!-- Pendientes -->
            <div class="rounded-2xl border border-[#C7B5A3]/30 bg-white p-4 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-azul-profundo/50">Pendientes</p>
                <p class="mt-2 text-2xl font-black text-amber-700">{{ $indicadores['pendientes'] }}</p>
            </div>
        </div>

        <!-- ── CONTENIDO DEL REPORTE ────────────────────────────── -->
        <div class="grid gap-8 lg:grid-cols-3 print:grid-cols-1">
            
            <!-- SECCIÓN DE MÉTRICAS DETALLADAS (Izquierda/Centro) -->
            <div class="lg:col-span-2 space-y-6 print:lg:col-span-3">
                <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white p-6 shadow-sm print:border-none print:shadow-none print:p-0">
                    
                    @if($stats['total'] === 0)
                        <!-- ESTADO VACÍO -->
                        <div class="py-12 text-center">
                            <i class="ph ph-files text-6xl text-[#C7B5A3]/60 mb-4"></i>
                            <h3 class="text-base font-black text-azul-profundo">Sin datos suficientes para generar este reporte.</h3>
                            <p class="text-xs text-azul-profundo/50 mt-1">Intente ampliando el rango de fechas o seleccionando otro filtro de estado.</p>
                        </div>
                    @else
                        <!-- CATEGORÍA 1: REPORTE GENERAL -->
                        @if($tipoReporte === 'general')
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    1. Reporte General de Población
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Distribución de Estados</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Activos:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['activos'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Inactivos / Archivados:</span>
                                                <span class="font-black text-rose-700">{{ $stats['archivados'] }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Distribución por Género</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Femenino:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['mujeres'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Masculino:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['hombres'] }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="rounded-2xl bg-[#F7F5F2] p-5 sm:col-span-2">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Distribución por Rangos de Edad</h4>
                                        <div class="grid grid-cols-2 gap-4 mt-4">
                                            <div class="flex justify-between text-xs font-bold">
                                                <span>Menores de 65:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['menores65'] }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs font-bold">
                                                <span>65 a 75 años:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['de65a75'] }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs font-bold">
                                                <span>76 a 85 años:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['de76a85'] }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs font-bold">
                                                <span>Mayores de 85:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['mayores85'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- CATEGORÍA 2: REPORTE DOCUMENTAL -->
                        @elseif($tipoReporte === 'documental')
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    2. Reporte Documental e Historial
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Expediente Digital</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Con documentos:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['conDocumentos'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Sin documentos registrados:</span>
                                                <span class="font-black text-rose-700">{{ $stats['sinDocumentos'] }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Archivos Digitalizados</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Documentos Activos:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['documentosActivos'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Anulados / Archivados:</span>
                                                <span class="font-black text-rose-700">{{ $stats['documentosAnulados'] }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        <!-- CATEGORÍA 3: REPORTE RED DE APOYO -->
                        @elseif($tipoReporte === 'red_de_apoyo')
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    3. Red de Apoyo Familiar
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Vínculos de Apoyo</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Con familiares asociados:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['conFamiliar'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Sin familiar registrado:</span>
                                                <span class="font-black text-rose-700">{{ $stats['sinFamiliar'] }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Críticos y Responsabilidad</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Sin responsable principal:</span>
                                                <span class="font-black text-amber-700">{{ $stats['sinResponsable'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Sin contacto de emergencia:</span>
                                                <span class="font-black text-rose-700">{{ $stats['sinContacto'] }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        <!-- CATEGORÍA 4: REPORTE SALUD Y CUIDADOS -->
                        @elseif($tipoReporte === 'salud')
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    4. Salud y Cuidados
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Ficha Médica Básica</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Con ficha registrada:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['conFicha'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Sin ficha registrada:</span>
                                                <span class="font-black text-rose-700">{{ $stats['sinFicha'] }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Monitoreo Clínico</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Con signos vitales registrados:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['conSignos'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Con valoración funcional registrada:</span>
                                                <span class="font-black text-[#5B5F97]">{{ $stats['conValoracion'] }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        <!-- CATEGORÍA 5: REPORTE EVALUACIONES GERIÁTRICAS -->
                        @elseif($tipoReporte === 'evaluaciones')
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    5. Evaluaciones Geriátricas Integrales
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5 sm:col-span-2 flex justify-between items-center">
                                        <div>
                                            <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Evaluaciones Totales</h4>
                                            <p class="mt-1 text-2xl font-black text-azul-profundo">{{ $stats['totalEvaluaciones'] }} realizadas</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-bold text-rose-700">Sin evaluación: {{ $stats['sinEvaluacion'] }}</span>
                                        </div>
                                    </div>

                                    <!-- Por Área -->
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Por Área Geriátrica</h4>
                                        <ul class="mt-4 space-y-2">
                                            @forelse($stats['evaluacionesPorArea'] as $area => $total)
                                                <li class="flex justify-between text-xs font-bold border-b border-[#C7B5A3]/20 pb-1.5">
                                                    <span>{{ $area }}:</span>
                                                    <span class="font-black text-azul-profundo">{{ $total }}</span>
                                                </li>
                                            @empty
                                                <li class="text-xs font-bold text-azul-profundo/50 py-2">Sin registros.</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <!-- Por Alerta -->
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Por Nivel de Alerta</h4>
                                        <ul class="mt-4 space-y-2">
                                            <li class="flex justify-between text-xs font-bold border-b border-[#C7B5A3]/20 pb-1.5">
                                                <span>NORMAL:</span>
                                                <span class="font-black text-emerald-700">{{ $stats['evaluacionesPorNivel']['NORMAL'] ?? 0 }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold border-b border-[#C7B5A3]/20 pb-1.5">
                                                <span>PREVENTIVO:</span>
                                                <span class="font-black text-amber-700">{{ $stats['evaluacionesPorNivel']['PREVENTIVO'] ?? 0 }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold pb-1.5">
                                                <span>CRÍTICO:</span>
                                                <span class="font-black text-rose-700">{{ $stats['evaluacionesPorNivel']['CRITICO'] ?? 0 }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        <!-- CATEGORÍA 6: REPORTE DE SEGUIMIENTO -->
                        @elseif($tipoReporte === 'seguimiento')
                            <div>
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    6. Seguimiento Institucional
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <!-- Eventos de seguimiento -->
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Eventos de Seguimiento</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Observaciones registradas:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['observaciones'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold text-rose-700 font-black">
                                                <span>Importancia Alta / Urgente:</span>
                                                <span>{{ $stats['observacionesAlta'] }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Agenda -->
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Agenda y Pendientes</h4>
                                        <ul class="mt-4 space-y-3">
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Atenciones registradas:</span>
                                                <span class="font-black text-[#5B5F97]">{{ $stats['atenciones'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-bold">
                                                <span>Actividades registradas:</span>
                                                <span class="font-black text-azul-profundo">{{ $stats['actividades'] }}</span>
                                            </li>
                                            <li class="flex justify-between text-xs font-black text-amber-700">
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
                                <h3 class="text-lg font-black uppercase tracking-wider text-azul-profundo border-b border-[#C7B5A3]/40 pb-3 mb-6">
                                    7. Trazabilidad y Auditoría
                                </h3>
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Historial de Estados</h4>
                                        <p class="mt-2 text-3xl font-black text-azul-profundo">{{ $stats['cambiosEstado'] }}</p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 mt-1">Cambios en el estado institucional del adulto mayor.</p>
                                    </div>

                                    <div class="rounded-2xl bg-[#F7F5F2] p-5">
                                        <h4 class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60">Actividad de Bitácora</h4>
                                        <p class="mt-2 text-3xl font-black text-[#5B5F97]">{{ $stats['actividadBitacora'] }}</p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 mt-1">Registros de acciones auditados en el sistema.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- COLUMNA DE GRÁFICAS (Derecha) -->
            <div class="space-y-6">
                <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white p-6 shadow-sm print:break-before-page">
                    <h3 class="text-xs font-black uppercase tracking-widest text-azul-profundo border-b border-[#C7B5A3]/30 pb-3 mb-6">
                        Análisis Visual Real
                    </h3>

                    @if($stats['total'] === 0)
                        <div class="py-8 text-center text-xs font-bold text-azul-profundo/40">
                            Sin datos suficientes para generar esta gráfica.
                        </div>
                    @else
                        <!-- Gráficas del Reporte General -->
                        @if($tipoReporte === 'general')
                            <div class="space-y-8">
                                <div class="flex flex-col gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-azul-profundo/60">Estado Institucional</span>
                                    <div class="relative h-48 w-full">
                                        <canvas id="chartEstados"></canvas>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-azul-profundo/60">Rangos de Edad</span>
                                    <div class="relative h-48 w-full">
                                        <canvas id="chartEdades"></canvas>
                                    </div>
                                </div>
                            </div>

                        <!-- Gráficas del Reporte Documental -->
                        @elseif($tipoReporte === 'documental')
                            <div class="flex flex-col gap-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-azul-profundo/60">Expedientes Digitalizados</span>
                                <div class="relative h-56 w-full">
                                    <canvas id="chartDocumentos"></canvas>
                                </div>
                            </div>

                        <!-- Gráficas del Reporte de Red de Apoyo -->
                        @elseif($tipoReporte === 'red_de_apoyo')
                            <div class="py-12 text-center text-xs font-bold text-[#2F3E5C]/50">
                                Sin datos suficientes para generar esta gráfica.
                            </div>

                        <!-- Gráficas del Reporte de Salud -->
                        @elseif($tipoReporte === 'salud')
                            <div class="py-12 text-center text-xs font-bold text-[#2F3E5C]/50">
                                Sin datos suficientes para generar esta gráfica.
                            </div>

                        <!-- Gráficas del Reporte de Evaluaciones -->
                        @elseif($tipoReporte === 'evaluaciones')
                            <div class="space-y-8">
                                <div class="flex flex-col gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-azul-profundo/60">Niveles de Alerta</span>
                                    <div class="relative h-48 w-full">
                                        <canvas id="chartNiveles"></canvas>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-azul-profundo/60">Áreas Geriátricas Evaluadas</span>
                                    <div class="relative h-48 w-full">
                                        <canvas id="chartAreas"></canvas>
                                    </div>
                                </div>
                            </div>

                        <!-- Gráficas de Seguimiento -->
                        @elseif($tipoReporte === 'seguimiento')
                            <div class="flex flex-col gap-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-azul-profundo/60">Eventos de Seguimiento</span>
                                <div class="relative h-56 w-full">
                                    <canvas id="chartSeguimiento"></canvas>
                                </div>
                            </div>

                        <!-- Gráficas de Trazabilidad -->
                        @elseif($tipoReporte === 'trazabilidad')
                            <div class="py-12 text-center text-xs font-bold text-[#2F3E5C]/50">
                                Sin datos suficientes para generar esta gráfica.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- ── FOOTER DE FIRMA INSTITUCIONAL PARA IMPRESIÓN ──────── -->
    <div class="hidden print:block mt-20 text-center border-t border-slate-300 pt-8">
        <p class="text-xs font-black uppercase tracking-widest text-slate-800">
            RememberMind — Suite de Gestión "CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
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

                    // Destruir gráficos anteriores para evitar fugas de memoria y solapamientos
                    Object.values(this.charts).forEach(chart => {
                        if (chart && typeof chart.destroy === 'function') {
                            chart.destroy();
                        }
                    });
                    this.charts = {};

                    // 1. Dona: Adultos por Estado
                    const ctxEstados = document.getElementById('chartEstados');
                    if (ctxEstados && Object.keys(estados).length > 0) {
                        this.charts.estados = new Chart(ctxEstados, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(estados),
                                datasets: [{
                                    data: Object.values(estados),
                                    backgroundColor: ['#E27D60', '#5B5F97', '#85D2B4', '#D9A27C', '#2F3E5C'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 8,
                                            font: { family: 'Outfit', size: 10, weight: 'bold' }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 2. Barras: Adultos por Rango de Edad
                    const ctxEdades = document.getElementById('chartEdades');
                    if (ctxEdades && Object.values(edades).some(v => v > 0)) {
                        this.charts.edades = new Chart(ctxEdades, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(edades),
                                datasets: [{
                                    data: Object.values(edades),
                                    backgroundColor: '#5B5F97',
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { stepSize: 1, font: { family: 'Outfit', size: 9 } }
                                    },
                                    x: {
                                        ticks: { font: { family: 'Outfit', size: 9, weight: 'bold' } }
                                    }
                                }
                            }
                        });
                    }

                    // 3. Dona: Evaluaciones por Nivel de Alerta
                    const ctxNiveles = document.getElementById('chartNiveles');
                    if (ctxNiveles && tipoReporte === 'evaluaciones' && Object.values(niveles).some(v => v > 0)) {
                        this.charts.niveles = new Chart(ctxNiveles, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(niveles),
                                datasets: [{
                                    data: Object.values(niveles),
                                    backgroundColor: ['#85D2B4', '#F4D068', '#E27D60'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 8, font: { family: 'Outfit', size: 10, weight: 'bold' } }
                                    }
                                }
                            }
                        });
                    }

                    // 4. PolarArea: Evaluaciones por Área
                    const ctxAreas = document.getElementById('chartAreas');
                    if (ctxAreas && tipoReporte === 'evaluaciones' && Object.keys(areas).length > 0) {
                        this.charts.areas = new Chart(ctxAreas, {
                            type: 'polarArea',
                            data: {
                                labels: Object.keys(areas),
                                datasets: [{
                                    data: Object.values(areas),
                                    backgroundColor: ['rgba(226, 125, 96, 0.7)', 'rgba(91, 95, 151, 0.7)', 'rgba(133, 210, 180, 0.7)', 'rgba(217, 162, 124, 0.7)'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 8, font: { family: 'Outfit', size: 9, weight: 'bold' } }
                                    }
                                }
                            }
                        });
                    }

                    // 5. Pie: Documentos Activos vs Anulados/Archivados
                    const ctxDocumentos = document.getElementById('chartDocumentos');
                    if (ctxDocumentos && tipoReporte === 'documental' && Object.values(documentos).some(v => v > 0)) {
                        this.charts.documentos = new Chart(ctxDocumentos, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(documentos),
                                datasets: [{
                                    data: Object.values(documentos),
                                    backgroundColor: ['#85D2B4', '#E27D60'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 8, font: { family: 'Outfit', size: 10, weight: 'bold' } }
                                    }
                                }
                            }
                        });
                    }

                    // 6. Barras: Seguimiento por Tipo
                    const ctxSeguimiento = document.getElementById('chartSeguimiento');
                    if (ctxSeguimiento && tipoReporte === 'seguimiento' && Object.values(seguimiento).some(v => v > 0)) {
                        this.charts.seguimiento = new Chart(ctxSeguimiento, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(seguimiento),
                                datasets: [{
                                    data: Object.values(seguimiento),
                                    backgroundColor: '#E27D60',
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { stepSize: 1, font: { family: 'Outfit', size: 9 } }
                                    },
                                    x: {
                                        ticks: { font: { family: 'Outfit', size: 9, weight: 'bold' } }
                                    }
                                }
                            }
                        });
                    }
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
