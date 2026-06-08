<div class="space-y-6">
    <!-- Encabezado Clínico -->
    <div class="flex flex-col gap-4 rounded-[24px] border border-borde bg-fondo-panel p-6 shadow-sm md:flex-row md:items-start md:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <span class="text-2xl font-black">{{ substr($adultoMayor->nombres, 0, 1) }}{{ substr($adultoMayor->ap_paterno, 0, 1) }}</span>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">
                    {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }}
                </h2>
                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm font-semibold text-apoyo">
                    <span class="flex items-center gap-1.5"><i class="ph-bold ph-identification-card"></i> CI: {{ $adultoMayor->ci }}</span>
                    <span class="flex items-center gap-1.5"><i class="ph-bold ph-cake"></i> Edad: {{ \Carbon\Carbon::parse($adultoMayor->fecha_nac)->age }} años</span>
                    <span class="flex items-center gap-1.5"><i class="ph-bold ph-bed"></i> Hab. {{ $adultoMayor->habitacion->codigo ?? 'N/A' }} / Cama {{ $adultoMayor->cama->codigo ?? 'N/A' }}</span>
                </div>
                
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-fondo-card px-2.5 py-1 text-[10px] font-bold uppercase text-parrafo border border-borde">
                        <i class="ph-fill ph-circle text-[8px] {{ $adultoMayor->estadoTexto === 'ACTIVO' ? 'text-emerald-500' : 'text-amber-500' }}"></i>
                        Estado: {{ $adultoMayor->estadoTexto }}
                    </span>
                    @if($adultoMayor->asignacionTurnoActiva)
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase text-blue-600 border border-blue-200">
                            <i class="ph-bold ph-clock"></i> Turno: {{ $adultoMayor->asignacionTurnoActiva->turno->nombre }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase text-blue-600 border border-blue-200">
                            <i class="ph-bold ph-user-nurse"></i> Enf: {{ $adultoMayor->asignacionTurnoActiva->enfermero->nombres }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase text-amber-600 border border-amber-200">
                            <i class="ph-bold ph-warning"></i> Sin turno asignado
                        </span>
                    @endif
                    
                    @if($adultoMayor->planCuidadoActivo)
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase text-emerald-600 border border-emerald-200">
                            <i class="ph-bold ph-clipboard-text"></i> Plan: {{ $adultoMayor->planCuidadoActivo->nivel_cuidado }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase text-red-600 border border-red-200">
                            <i class="ph-bold ph-clipboard-text"></i> Plan Pendiente
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="flex flex-col items-end gap-2">
            <div class="flex gap-2">
                <a href="{{ route('admin.enfermeria.pacientes.ficha.pdf', $adultoMayor->id) }}" target="_blank" class="rm-btn-secondary px-3 py-2 text-xs" title="Exportar Reporte PDF">
                    <i class="ph-bold ph-printer text-lg"></i>
                </a>
                <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn-secondary px-4 py-2 text-xs">
                    <i class="ph-bold ph-arrow-left text-lg"></i> Volver
                </a>
            </div>
            <!-- Acciones Clínicas Rápidas -->
            <div class="flex gap-2 mt-2">
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm" title="Registrar Signos">
                    <i class="ph-bold ph-thermometer text-lg"></i>
                </button>
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition shadow-sm" title="Administrar Medicación">
                    <i class="ph-bold ph-pill text-lg"></i>
                </button>
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white transition shadow-sm" title="Registrar Seguimiento">
                    <i class="ph-bold ph-heartbeat text-lg"></i>
                </button>
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm" title="Atender Alerta">
                    <i class="ph-bold ph-bell-ringing text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Timeline Clínico -->
    <div class="rounded-[24px] border border-borde bg-fondo-panel p-6 shadow-sm overflow-x-auto">
        <div class="flex min-w-[800px] items-center justify-between">
            @foreach($timeline as $step => $isCompleted)
                <div class="flex flex-col items-center gap-2 relative z-10 flex-1">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $isCompleted ? 'bg-emerald-500 text-white shadow-md' : 'bg-fondo-card border-2 border-borde text-apoyo' }} transition-colors duration-500">
                        @if($isCompleted)
                            <i class="ph-bold ph-check text-sm"></i>
                        @else
                            <span class="h-2 w-2 rounded-full bg-borde"></span>
                        @endif
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest {{ $isCompleted ? 'text-emerald-600' : 'text-apoyo' }} text-center">
                        {{ $step }}
                    </span>
                </div>
            @endforeach
            <!-- Línea conectora -->
            <div class="absolute left-10 right-10 top-10 h-0.5 bg-borde-suave -z-10"></div>
        </div>
    </div>

    <!-- Cards Rápidas (7) -->
    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
        <!-- Signos -->
        @php $ultimoSigno = $adultoMayor->signosVitales->first(); @endphp
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm">
            <i class="ph-fill ph-thermometer text-2xl text-blue-500 mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Últimos Signos</h4>
            <p class="mt-1 text-sm font-black text-titulo">
                {{ $ultimoSigno ? $ultimoSigno->presion_sistolica.'/'.$ultimoSigno->presion_diastolica.' mmHg' : 'Sin datos' }}
            </p>
        </div>
        
        <!-- Medicación -->
        @php $proximaMed = $adultoMayor->administracionesMedicacion->first(); @endphp
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm">
            <i class="ph-fill ph-pill text-2xl text-emerald-500 mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Próx. Medicación</h4>
            <p class="mt-1 text-sm font-black text-titulo truncate">
                {{ $proximaMed ? \Carbon\Carbon::parse($proximaMed->hora_programada)->format('H:i') : 'Libre' }}
            </p>
        </div>

        <!-- Tareas -->
        @php $tareasPendientes = $adultoMayor->tareasActuales->count(); @endphp
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm">
            <i class="ph-fill ph-list-checks text-2xl text-amber-500 mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Tareas Pendientes</h4>
            <p class="mt-1 text-sm font-black text-titulo">{{ $tareasPendientes }}</p>
        </div>

        <!-- Alertas -->
        @php $alertasActivas = $adultoMayor->alertasAbiertas->count(); @endphp
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm {{ $alertasActivas > 0 ? 'bg-red-50 border-red-200' : '' }}">
            <i class="ph-fill ph-bell-ringing text-2xl {{ $alertasActivas > 0 ? 'text-red-600 animate-pulse' : 'text-meta' }} mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Alertas Activas</h4>
            <p class="mt-1 text-sm font-black {{ $alertasActivas > 0 ? 'text-red-600' : 'text-titulo' }}">{{ $alertasActivas }}</p>
        </div>

        <!-- Seguimiento -->
        @php $ultimoSeg = $adultoMayor->seguimientosDiarios->first(); @endphp
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm">
            <i class="ph-fill ph-heartbeat text-2xl text-purple-500 mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Último Seg.</h4>
            <p class="mt-1 text-sm font-black text-titulo">{{ $ultimoSeg ? \Carbon\Carbon::parse($ultimoSeg->fecha)->format('d/m') : 'Ninguno' }}</p>
        </div>

        <!-- Pase -->
        @php $ultimoPase = $adultoMayor->pasesTurno->first(); @endphp
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm">
            <i class="ph-fill ph-handshake text-2xl text-indigo-500 mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Pase Recibido</h4>
            <p class="mt-1 text-sm font-black text-titulo">{{ $ultimoPase ? 'Sí' : 'No' }}</p>
        </div>

        <!-- Plan -->
        <div class="rounded-[16px] border border-borde bg-fondo-panel p-4 text-center shadow-sm">
            <i class="ph-fill ph-clipboard-text text-2xl text-blue-500 mb-2"></i>
            <h4 class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Plan Cuidado</h4>
            <p class="mt-1 text-sm font-black text-titulo truncate">{{ $adultoMayor->planCuidadoActivo ? 'V.'.$adultoMayor->planCuidadoActivo->version : 'Pend.' }}</p>
        </div>
    </div>

    <!-- Pestañas Internas y Contenido -->
    <div class="rounded-[24px] border border-borde bg-fondo-panel shadow-sm">
        <div class="overflow-x-auto border-b border-borde custom-scrollbar">
            <div class="flex px-4">
                @php
                    $tabs = [
                        'resumen' => ['icon' => 'ph-squares-four', 'label' => 'Resumen'],
                        'valoraciones' => ['icon' => 'ph-file-text', 'label' => 'Valoraciones Previas'],
                        'plan' => ['icon' => 'ph-clipboard-text', 'label' => 'Plan de Cuidado'],
                        'tareas' => ['icon' => 'ph-list-checks', 'label' => 'Tareas'],
                        'signos' => ['icon' => 'ph-thermometer', 'label' => 'Signos Vitales'],
                        'medicacion' => ['icon' => 'ph-pill', 'label' => 'Medicación'],
                        'seguimiento' => ['icon' => 'ph-heartbeat', 'label' => 'Seguimiento'],
                        'alertas' => ['icon' => 'ph-bell-ringing', 'label' => 'Alertas'],
                        'pase' => ['icon' => 'ph-handshake', 'label' => 'Pase de Turno'],
                        'historial' => ['icon' => 'ph-clock-counter-clockwise', 'label' => 'Historial'],
                        'reportes' => ['icon' => 'ph-chart-line-up', 'label' => 'Reportes']
                    ];
                @endphp
                
                @foreach($tabs as $key => $tab)
                    <button wire:click="cambiarTab('{{ $key }}')" 
                            class="flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-4 text-sm font-bold transition-all duration-300 {{ $tabActivo === $key ? 'border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:border-borde hover:text-parrafo' }}">
                        <i class="ph-bold {{ $tab['icon'] }} text-lg"></i>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="p-6">
            @if($tabActivo === 'resumen')
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Gráfica Signos -->
                    <div class="rounded-[20px] border border-borde-suave bg-fondo-app p-5">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-parrafo">Evolución: Presión y Saturación</h3>
                        <canvas id="chartSignos" height="200"></canvas>
                    </div>

                    <!-- Gráfica Tareas vs Cumplimiento -->
                    <div class="rounded-[20px] border border-borde-suave bg-fondo-app p-5">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-parrafo">Cumplimiento de Tareas</h3>
                        <canvas id="chartTareas" height="200"></canvas>
                    </div>

                    <!-- Gráfica Alertas por tipo -->
                    <div class="rounded-[20px] border border-borde-suave bg-fondo-app p-5">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-parrafo">Alertas por Tipo</h3>
                        <canvas id="chartAlertas" height="200"></canvas>
                    </div>

                    <!-- Gráfica Seguimientos por turno -->
                    <div class="rounded-[20px] border border-borde-suave bg-fondo-app p-5">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-parrafo">Seguimientos por Turno</h3>
                        <canvas id="chartSeguimientos" height="200"></canvas>
                    </div>
                </div>
            @elseif($tabActivo === 'valoraciones')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Valoraciones de Enfermería</h3>
                    @if($adultoMayor->valoracionesEnfermeria->isEmpty())
                        <p class="text-apoyo text-sm">No hay valoraciones de enfermería.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha</th>
                                        <th class="px-4 py-3">Puntuación Barthel</th>
                                        <th class="px-4 py-3">Nivel Dependencia</th>
                                        <th class="px-4 py-3">Enfermero(a)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->valoracionesEnfermeria as $val)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($val->fecha)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $val->puntuacion_barthel ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $val->nivel_dependencia ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $val->enfermero->nombres ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($tabActivo === 'plan')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Plan de Cuidado Activo</h3>
                    @if($adultoMayor->planCuidadoActivo)
                        <div class="rounded-xl border border-borde p-4 bg-fondo-card">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-bold">Nivel Cuidado:</span> {{ $adultoMayor->planCuidadoActivo->nivel_cuidado }}</div>
                                <div><span class="font-bold">Versión:</span> V.{{ $adultoMayor->planCuidadoActivo->version }}</div>
                                <div><span class="font-bold">Fecha Inicio:</span> {{ \Carbon\Carbon::parse($adultoMayor->planCuidadoActivo->fecha_inicio)->format('d/m/Y') }}</div>
                                <div><span class="font-bold">Creado Por:</span> {{ $adultoMayor->planCuidadoActivo->creador->nombres ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @else
                        <p class="text-apoyo text-sm">No hay un plan de cuidado activo.</p>
                    @endif
                </div>

            @elseif($tabActivo === 'tareas')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Tareas Programadas</h3>
                    @if($adultoMayor->tareasActuales->isEmpty())
                        <p class="text-apoyo text-sm">No hay tareas programadas.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha/Hora</th>
                                        <th class="px-4 py-3">Descripción</th>
                                        <th class="px-4 py-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->tareasActuales as $tarea)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($tarea->fecha_programada)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($tarea->hora_programada)->format('H:i') }}</td>
                                        <td class="px-4 py-3">{{ $tarea->descripcion }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[10px] font-bold uppercase border 
                                                {{ $tarea->estado === 'REALIZADO' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : ($tarea->estado === 'PENDIENTE' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-red-50 text-red-600 border-red-200') }}">
                                                {{ $tarea->estado }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($tabActivo === 'signos')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Últimos Signos Vitales</h3>
                    @if($adultoMayor->signosVitales->isEmpty())
                        <p class="text-apoyo text-sm">No hay signos vitales registrados.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha/Hora</th>
                                        <th class="px-4 py-3">PA</th>
                                        <th class="px-4 py-3">FC</th>
                                        <th class="px-4 py-3">FR</th>
                                        <th class="px-4 py-3">Temp.</th>
                                        <th class="px-4 py-3">SatO2</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->signosVitales as $signo)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($signo->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($signo->hora)->format('H:i') }}</td>
                                        <td class="px-4 py-3">{{ $signo->presion_sistolica }}/{{ $signo->presion_diastolica }}</td>
                                        <td class="px-4 py-3">{{ $signo->frecuencia_cardiaca }}</td>
                                        <td class="px-4 py-3">{{ $signo->frecuencia_respiratoria }}</td>
                                        <td class="px-4 py-3">{{ $signo->temperatura }}</td>
                                        <td class="px-4 py-3">{{ $signo->saturacion }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($tabActivo === 'medicacion')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Medicación Pendiente</h3>
                    @if($adultoMayor->administracionesMedicacion->isEmpty())
                        <p class="text-apoyo text-sm">No hay medicación pendiente.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha/Hora Prog.</th>
                                        <th class="px-4 py-3">Medicamento</th>
                                        <th class="px-4 py-3">Dosis</th>
                                        <th class="px-4 py-3">Vía</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->administracionesMedicacion as $admin)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($admin->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($admin->hora_programada)->format('H:i') }}</td>
                                        <td class="px-4 py-3 font-semibold">{{ $admin->medicacion->medicamento ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $admin->medicacion->dosis ?? '' }}</td>
                                        <td class="px-4 py-3">{{ $admin->medicacion->via_administracion ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($tabActivo === 'seguimiento')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Seguimientos Diarios</h3>
                    @if($adultoMayor->seguimientosDiarios->isEmpty())
                        <p class="text-apoyo text-sm">No hay seguimientos registrados.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha/Hora</th>
                                        <th class="px-4 py-3">Turno</th>
                                        <th class="px-4 py-3">Estado General</th>
                                        <th class="px-4 py-3">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->seguimientosDiarios as $seg)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($seg->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($seg->hora)->format('H:i') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $seg->turno->nombre ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $seg->estado_general }}</td>
                                        <td class="px-4 py-3 text-xs">{{ \Illuminate\Support\Str::limit($seg->observaciones, 80) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($tabActivo === 'alertas')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Alertas Clínicas Abiertas</h3>
                    @if($adultoMayor->alertasAbiertas->isEmpty())
                        <p class="text-apoyo text-sm">No hay alertas abiertas.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha</th>
                                        <th class="px-4 py-3">Tipo</th>
                                        <th class="px-4 py-3">Prioridad</th>
                                        <th class="px-4 py-3">Descripción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->alertasAbiertas as $alerta)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($alerta->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 font-semibold">{{ $alerta->tipo_alerta }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-[10px] font-bold uppercase border 
                                                {{ $alerta->nivel_prioridad === 'ALTA' ? 'bg-red-50 text-red-600 border-red-200' : ($alerta->nivel_prioridad === 'MEDIA' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-blue-50 text-blue-600 border-blue-200') }}">
                                                {{ $alerta->nivel_prioridad }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs">{{ $alerta->descripcion }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @elseif($tabActivo === 'pase')
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-parrafo mb-4">Pases de Turno Recientes</h3>
                    @if($adultoMayor->pasesTurno->isEmpty())
                        <p class="text-apoyo text-sm">No hay pases de turno registrados.</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-borde">
                            <table class="w-full text-left text-sm text-parrafo">
                                <thead class="bg-fondo-card text-xs uppercase text-titulo">
                                    <tr>
                                        <th class="px-4 py-3">Fecha</th>
                                        <th class="px-4 py-3">Turno Origen</th>
                                        <th class="px-4 py-3">Turno Destino</th>
                                        <th class="px-4 py-3">Novedades</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde">
                                    @foreach($adultoMayor->pasesTurno as $pase)
                                    <tr class="hover:bg-fondo-card/50">
                                        <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($pase->fecha)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3">{{ $pase->turnoOrigen->nombre ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $pase->turnoDestino->nombre ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-xs">{{ \Illuminate\Support\Str::limit($pase->novedades_relevantes, 80) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @else
                <!-- Placeholder para Historial y Reportes -->
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-borde bg-fondo-app py-16 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-fondo-card text-meta shadow-inner">
                        <i class="ph-bold ph-wrench text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-titulo">Pestaña: {{ $tabs[$tabActivo]['label'] }}</h3>
                    <p class="mt-1 max-w-sm text-sm text-apoyo">La vista detallada de esta pestaña se implementará en los módulos individuales según arquitectura.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Configuración global Chart.js
    Chart.defaults.color = 'var(--color-apoyo)';
    Chart.defaults.font.family = "'Outfit', sans-serif";
    
    @if($tabActivo === 'resumen')
        const ctxSignos = document.getElementById('chartSignos');
        if(ctxSignos) {
            new Chart(ctxSignos, {
                type: 'line',
                data: {
                    labels: @json($labelsSignos),
                    datasets: [
                        {
                            label: 'Sistólica',
                            data: @json($dataPresionSis),
                            borderColor: '#ef4444',
                            backgroundColor: '#ef4444',
                            tension: 0.4
                        },
                        {
                            label: 'Diastólica',
                            data: @json($dataPresionDia),
                            borderColor: '#f59e0b',
                            backgroundColor: '#f59e0b',
                            tension: 0.4
                        },
                        {
                            label: 'Saturación O2',
                            data: @json($dataSaturacion),
                            borderColor: '#3b82f6',
                            backgroundColor: '#3b82f6',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { type: 'linear', display: true, position: 'left', min: 40, max: 200 },
                        y1: { type: 'linear', display: true, position: 'right', min: 70, max: 100 }
                    }
                }
            });
        }

        const ctxTareas = document.getElementById('chartTareas');
        if(ctxTareas) {
            new Chart(ctxTareas, {
                type: 'doughnut',
                data: {
                    labels: ['Realizadas', 'Pendientes', 'Omitidas'],
                    datasets: [{
                        data: @json($dataTareas),
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        const ctxAlertas = document.getElementById('chartAlertas');
        if(ctxAlertas) {
            new Chart(ctxAlertas, {
                type: 'bar',
                data: {
                    labels: @json($labelsAlertas),
                    datasets: [{
                        label: 'Cantidad de Alertas',
                        data: @json($dataAlertas),
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        const ctxSeg = document.getElementById('chartSeguimientos');
        if(ctxSeg) {
            new Chart(ctxSeg, {
                type: 'bar',
                data: {
                    labels: @json($labelsSeguimientos),
                    datasets: [{
                        label: 'Cantidad de Seguimientos',
                        data: @json($dataSeguimientos),
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    @endif
});
</script>
