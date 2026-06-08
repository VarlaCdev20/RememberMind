<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-fill ph-stethoscope text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">
                    Dashboard de Enfermería
                </h2>
                <p class="text-sm font-semibold text-apoyo">
                    Turno: <span class="text-meta">{{ $turnoActual ? $turnoActual->nombre . ' (' . \Carbon\Carbon::parse($turnoActual->hora_inicio)->format('H:i') . ' - ' . \Carbon\Carbon::parse($turnoActual->hora_fin)->format('H:i') . ')' : 'Sin Turno Activo' }}</span> | {{ \Carbon\Carbon::parse($filtroFecha)->translatedFormat('d \d\e F, Y') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="rm-btn-secondary h-10 px-4">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline">Actualizar</span>
            </button>
            <button wire:click="exportarReporte" class="rm-btn-primary h-10 px-4">
                <i class="ph-bold ph-file-pdf text-lg"></i>
                <span class="hidden sm:inline">Exportar Reporte PDF</span>
            </button>
        </div>
    </div>

    <!-- Línea de tiempo (Workflow del turno) -->
    <div class="rounded-[24px] border border-borde bg-fondo-panel p-6 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-parrafo">Flujo Operativo del Turno</h3>
        <div class="relative flex items-center justify-between">
            <div class="absolute left-0 top-1/2 h-0.5 w-full -translate-y-1/2 bg-borde"></div>
            
            <div class="relative z-10 flex flex-col items-center gap-2 bg-fondo-panel px-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-boton-acento text-white ring-4 ring-fondo-panel">
                    <i class="ph-bold ph-check text-sm"></i>
                </div>
                <span class="text-[10px] font-bold text-titulo uppercase">Turno Recibido</span>
            </div>
            
            <div class="relative z-10 flex flex-col items-center gap-2 bg-fondo-panel px-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $stats['tareas_pendientes'] == 0 ? 'bg-boton-acento text-white' : 'bg-amber-500 text-white animate-pulse' }} ring-4 ring-fondo-panel">
                    <i class="ph-bold ph-list-checks text-sm"></i>
                </div>
                <span class="text-[10px] font-bold text-titulo uppercase">Tareas</span>
            </div>

            <div class="relative z-10 flex flex-col items-center gap-2 bg-fondo-panel px-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-borde text-meta ring-4 ring-fondo-panel">
                    <i class="ph-bold ph-heartbeat text-sm"></i>
                </div>
                <span class="text-[10px] font-bold text-titulo uppercase">Seguimiento Diario</span>
            </div>

            <div class="relative z-10 flex flex-col items-center gap-2 bg-fondo-panel px-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $stats['alertas_activas'] == 0 ? 'bg-boton-acento text-white' : 'bg-red-600 text-white animate-pulse' }} ring-4 ring-fondo-panel">
                    <i class="ph-bold ph-bell-ringing text-sm"></i>
                </div>
                <span class="text-[10px] font-bold text-titulo uppercase">Alertas Atendidas</span>
            </div>

            <div class="relative z-10 flex flex-col items-center gap-2 bg-fondo-panel px-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $stats['pase_pendiente'] === 'CERRADO' ? 'bg-boton-acento text-white' : 'bg-borde text-meta' }} ring-4 ring-fondo-panel">
                    <i class="ph-bold ph-handshake text-sm"></i>
                </div>
                <span class="text-[10px] font-bold text-titulo uppercase">Pase Generado</span>
            </div>
        </div>
    </div>

    <!-- Indicadores -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <!-- Pacientes asignados -->
        <a href="{{ route('admin.enfermeria.pacientes') }}" class="group relative overflow-hidden rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm transition hover:-translate-y-1 hover:border-boton-acento hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-fondo-card text-parrafo transition group-hover:bg-boton-acento/10 group-hover:text-boton-acento">
                    <i class="ph-bold ph-users text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-titulo">{{ $stats['pacientes'] }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-apoyo group-hover:text-boton-acento">Pacientes<br>Asignados</p>
        </a>

        <!-- Tareas pendientes -->
        <a href="{{ route('admin.enfermeria.tareas') }}" class="group relative overflow-hidden rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm transition hover:-translate-y-1 hover:border-amber-500 hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 transition group-hover:bg-amber-100">
                    <i class="ph-bold ph-list-checks text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-amber-600">{{ $stats['tareas_pendientes'] }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-apoyo group-hover:text-amber-600">Tareas<br>Pendientes</p>
        </a>

        <!-- Medicación -->
        <div class="relative overflow-hidden rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="ph-bold ph-pill text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-emerald-600">{{ $stats['medicacion_pendiente'] }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-apoyo">Medicación<br>Pendiente</p>
        </div>

        <!-- Signos -->
        <div class="relative overflow-hidden rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <i class="ph-bold ph-thermometer text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-blue-600">{{ $stats['signos_pendientes'] }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-apoyo">Signos<br>Pendientes</p>
        </div>

        <!-- Alertas activas -->
        <a href="{{ route('admin.enfermeria.alertas') }}" class="group relative overflow-hidden rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm transition hover:-translate-y-1 hover:border-red-500 hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-600 transition group-hover:bg-red-100">
                    <i class="ph-bold ph-bell-ringing text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-red-600">{{ $stats['alertas_activas'] }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-apoyo group-hover:text-red-600">Alertas<br>Activas</p>
        </a>

        <!-- Pase -->
        <a href="{{ route('admin.enfermeria.pase-turno') }}" class="group relative overflow-hidden rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm transition hover:-translate-y-1 hover:border-purple-500 hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 transition group-hover:bg-purple-100">
                    <i class="ph-bold ph-handshake text-xl"></i>
                </div>
            </div>
            <p class="text-xs font-black text-purple-600 mt-3 truncate">{{ $stats['pase_pendiente'] }}</p>
            <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-apoyo group-hover:text-purple-600">Estado del<br>Pase</p>
        </a>
    </div>

    @if(count($valoracionesPendientes) > 0)
        <!-- Valoraciones Iniciales Pendientes (Preadmisiones) -->
        <div class="rounded-[24px] border border-estado-infoBorde bg-estado-infoBg/30 p-5 shadow-sm mb-2 mt-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-estado-info/5 rounded-bl-[100px] -z-10 pointer-events-none"></div>
            
            <div class="flex items-center gap-3 mb-5 border-b border-estado-infoBorde/50 pb-3">
                <div class="bg-white p-2 rounded-xl shadow-sm text-estado-info">
                    <i class="ph-bold ph-clipboard-text text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-estado-info">Valoraciones Iniciales Pendientes</h3>
                    <p class="text-xs text-estado-info/80">Pacientes nuevos derivados de preadmisión esperando triage.</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest text-estado-info">
                            <th class="pb-3 pr-4">Paciente Nuevo</th>
                            <th class="pb-3 pr-4">Edad / Procedencia</th>
                            <th class="pb-3 pr-4">Motivo de Ingreso</th>
                            <th class="pb-3 pr-4">Documentación</th>
                            <th class="pb-3 pr-4">Fecha Asignación</th>
                            <th class="pb-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-estado-infoBorde/50">
                        @foreach($valoracionesPendientes as $paciente)
                            <tr class="group transition-colors hover:bg-white/60">
                                <td class="py-3 pr-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-estado-info text-white font-bold shadow-sm ring-2 ring-white">
                                            {{ substr($paciente->nombres, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-estado-info block leading-tight">{{ $paciente->nombres }} {{ $paciente->ap_paterno }}</span>
                                            <span class="text-[10px] font-semibold text-estado-info/70">CI: {{ $paciente->ci }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="block font-bold text-titulo text-xs">{{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años</span>
                                    <span class="text-[10px] text-apoyo uppercase tracking-wider">{{ str_replace('_', ' ', $paciente->procedencia_ingreso ?? 'NO ESPECIFICADA') }}</span>
                                </td>
                                <td class="py-3 pr-4 text-xs font-semibold text-parrafo">
                                    {{ str_replace('_', ' ', $paciente->motivo_ingreso ?? 'EVALUACIÓN INICIAL') }}
                                </td>
                                <td class="py-3 pr-4 text-xs">
                                    @php
                                        $docsObligatorios = $paciente->documentos->whereIn('tipo_documento', ['CI', 'CI_ADULTO', 'FICHA_PREADMISION', 'COMPROMISO_INGRESO'])->count();
                                    @endphp
                                    <div class="flex items-center gap-1.5 {{ $docsObligatorios > 0 ? 'text-estado-exito' : 'text-estado-peligro' }}">
                                        <i class="ph-bold {{ $docsObligatorios > 0 ? 'ph-check-circle' : 'ph-warning-circle' }}"></i>
                                        <span class="font-bold text-[10px] uppercase tracking-wider">{{ $docsObligatorios > 0 ? 'Validada' : 'Faltan Requisitos' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-xs font-bold text-estado-info/80">
                                    {{ \Carbon\Carbon::parse($paciente->asignacionTurnoActiva->fecha_inicio ?? $paciente->created_at)->translatedFormat('d M, Y') }}
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button class="inline-flex h-8 items-center justify-center rounded-lg bg-white border border-estado-infoBorde px-3 text-xs font-bold text-estado-info transition hover:bg-estado-info hover:text-white" title="Ver Resumen de Preadmisión">
                                            <i class="ph-bold ph-eye mr-1"></i> Resumen
                                        </button>
                                        <button wire:click="iniciarValoracion('{{ $paciente->cod_am }}')" class="inline-flex h-8 items-center justify-center rounded-lg bg-boton-acento px-4 text-xs font-bold text-white shadow-sm transition hover:bg-boton-acentoHover" title="Iniciar Triage Médico">
                                            <i class="ph-bold ph-stethoscope mr-1"></i> Iniciar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!$turnoActual || count($pacientesAsignadosIds) == 0)
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-borde bg-fondo-panel py-16 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-fondo-card text-meta shadow-inner">
                <i class="ph-bold ph-coffee text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-titulo">No hay pacientes asignados</h3>
            <p class="mt-1 max-w-sm text-sm text-apoyo">Actualmente no tienes pacientes asignados a este turno, o no hay un turno activo. Contacta al administrador para la asignación.</p>
        </div>
    @else
        <!-- Gráficas y Tabla -->
        <div class="grid gap-6 lg:grid-cols-3">
            
            <div class="lg:col-span-2 space-y-6">
                <!-- Gráficas Principales -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-parrafo">Tareas de Enfermería</h3>
                        <div class="relative h-48 w-full">
                            <canvas id="chartTareas"></canvas>
                        </div>
                    </div>
                    <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-parrafo">Actividad por Hora</h3>
                        <div class="relative h-48 w-full">
                            <canvas id="chartActividad"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla Principal -->
                <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-parrafo">Próximas Tareas (Top 10)</h3>
                        <a href="{{ route('admin.enfermeria.tareas') }}" class="text-[10px] font-bold uppercase tracking-widest text-boton-acento hover:underline">Ver todas</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-borde-suave text-[10px] font-black uppercase tracking-widest text-apoyo">
                                    <th class="pb-3 pr-4">Hora</th>
                                    <th class="pb-3 pr-4">Paciente</th>
                                    <th class="pb-3 pr-4">Habitación/Cama</th>
                                    <th class="pb-3 pr-4">Tarea</th>
                                    <th class="pb-3 pr-4">Prioridad</th>
                                    <th class="pb-3 pr-4">Estado</th>
                                    <th class="pb-3 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde-suave">
                                @forelse($tareasTabla as $tarea)
                                    <tr class="group transition-colors hover:bg-fondo-hover">
                                        <td class="py-3 pr-4 font-bold text-titulo">
                                            {{ \Carbon\Carbon::parse($tarea->hora_programada)->format('H:i') }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-fondo-card text-[9px] font-bold text-parrafo shadow-sm">
                                                    {{ substr($tarea->adultoMayor->nombres ?? 'A', 0, 1) }}
                                                </div>
                                                <span class="font-bold text-parrafo">{{ $tarea->adultoMayor->nombres ?? 'Desconocido' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-4 text-[11px] font-bold text-apoyo">
                                            Hab. {{ $tarea->adultoMayor->habitacion->numero ?? $tarea->adultoMayor->cod_habitacion ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 pr-4 text-xs font-semibold text-parrafo">
                                            {{ Str::limit($tarea->titulo, 30) }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            @php
                                                $colorPrioridad = match($tarea->prioridad) {
                                                    'ALTA' => 'bg-red-50 text-red-600 border-red-200',
                                                    'MEDIA' => 'bg-amber-50 text-amber-600 border-amber-200',
                                                    default => 'bg-fondo-card text-apoyo border-borde',
                                                };
                                            @endphp
                                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest {{ $colorPrioridad }}">
                                                {{ $tarea->prioridad }}
                                            </span>
                                        </td>
                                        <td class="py-3 pr-4">
                                            @php
                                                $colorEstado = match($tarea->estado) {
                                                    'PENDIENTE' => 'text-amber-500',
                                                    'REALIZADA' => 'text-emerald-500',
                                                    'OMITIDA' => 'text-red-500',
                                                    default => 'text-meta',
                                                };
                                            @endphp
                                            <div class="flex items-center gap-1.5 {{ $colorEstado }}">
                                                <i class="ph-fill ph-circle text-[8px]"></i>
                                                <span class="text-[10px] font-bold uppercase tracking-widest">{{ $tarea->estado }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-right">
                                            <a href="{{ route('admin.enfermeria.tareas') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-boton-acento hover:text-white" title="Atender">
                                                <i class="ph-bold ph-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-xs font-bold text-apoyo">
                                            No hay tareas programadas para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna lateral: Mini gráficas -->
            <div class="space-y-6">
                <!-- Nivel de supervisión -->
                <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-parrafo">Nivel de Supervisión</h3>
                    <div class="relative h-40 w-full">
                        <canvas id="chartSupervision"></canvas>
                    </div>
                </div>

                <!-- Medicación -->
                <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-parrafo">Medicación</h3>
                    <div class="relative h-40 w-full">
                        <canvas id="chartMedicacion"></canvas>
                    </div>
                </div>

                <!-- Alertas -->
                <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                    <h3 class="mb-4 text-xs font-bold uppercase tracking-widest text-parrafo">Alertas por Nivel</h3>
                    <div class="relative h-40 w-full">
                        <canvas id="chartAlertas"></canvas>
                    </div>
                    <div class="mt-4 flex w-full">
                        <a href="{{ route('admin.enfermeria.alertas') }}" class="rm-btn-secondary w-full text-center py-2">
                            Gestionar Alertas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chartData = @json($charts ?? []);
            
            if (Object.keys(chartData).length === 0) return;
            
            Chart.defaults.font.family = "'Outfit', sans-serif";
            Chart.defaults.color = '#8C9AAB';
            const tension = 0.4;

            // 1. Dona: Tareas
            const ctxTareas = document.getElementById('chartTareas');
            if (ctxTareas) {
                new Chart(ctxTareas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Realizadas', 'Omitidas'],
                        datasets: [{
                            data: [chartData.tareas.pendientes, chartData.tareas.realizadas, chartData.tareas.omitidas],
                            backgroundColor: ['#F59E0B', '#10B981', '#EF4444'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } } }
                    }
                });
            }

            // 2. Línea: Actividad
            const ctxActividad = document.getElementById('chartActividad');
            if (ctxActividad) {
                new Chart(ctxActividad, {
                    type: 'line',
                    data: {
                        labels: Object.keys(chartData.actividad),
                        datasets: [{
                            label: 'Intervenciones',
                            data: Object.values(chartData.actividad),
                            borderColor: '#8B5CF6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: tension,
                            pointBackgroundColor: '#8B5CF6',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(140, 154, 171, 0.1)' }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 3. Barras: Supervisión
            const ctxSupervision = document.getElementById('chartSupervision');
            if (ctxSupervision) {
                new Chart(ctxSupervision, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(chartData.supervision),
                        datasets: [{
                            data: Object.values(chartData.supervision),
                            backgroundColor: '#3B82F6',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }

            // 4. Dona: Medicacion
            const ctxMed = document.getElementById('chartMedicacion');
            if (ctxMed) {
                new Chart(ctxMed, {
                    type: 'doughnut',
                    data: {
                        labels: ['Admin', 'Pendiente', 'Omitida'],
                        datasets: [{
                            data: [chartData.medicacion.administrada, chartData.medicacion.pendiente, chartData.medicacion.omitida],
                            backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } } }
                    }
                });
            }

            // 5. Barras: Alertas
            const ctxAlertas = document.getElementById('chartAlertas');
            if (ctxAlertas) {
                new Chart(ctxAlertas, {
                    type: 'bar',
                    data: {
                        labels: ['Leve', 'Moderada', 'Crítica'],
                        datasets: [{
                            data: [chartData.alertas.leve, chartData.alertas.moderada, chartData.alertas.critica],
                            backgroundColor: ['#3B82F6', '#F59E0B', '#EF4444'],
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        });
    </script>
    
    @livewire('admin.enfermeria.valoracion-inicial-modal')
</div>
