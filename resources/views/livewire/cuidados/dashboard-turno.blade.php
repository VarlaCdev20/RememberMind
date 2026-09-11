<div class="space-y-6 pb-12">
    {{-- 1. CABECERA COMPACTA INSTITUCIONAL --}}
    <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-boton-principal/20 bg-boton-principal/5 px-2.5 py-1 text-xs font-bold text-boton-principal">
                        <i class="ph-bold ph-shield-check text-sm"></i>
                        <span>{{ $esSuperAdmin ? 'Supervisión global de Enfermería' : 'Dashboard de Enfermería' }}</span>
                    </span>

                    @if($turnoActual)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-2.5 py-1 text-xs font-semibold text-titulo">
                            <i class="ph-bold ph-sun text-amber-600"></i>
                            <span>{{ $turnoActual->nombre }}</span>
                            <span class="text-apoyo">({{ \Carbon\Carbon::parse($turnoActual->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turnoActual->hora_fin)->format('H:i') }})</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/20 dark:text-amber-300">
                            <i class="ph-bold ph-warning-circle"></i>
                            <span>Sin turno asignado</span>
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-2.5 py-1 text-xs font-semibold text-parrafo">
                        <i class="ph-bold ph-calendar-blank text-apoyo"></i>
                        <span>{{ ucfirst(\Carbon\Carbon::parse($filtroFecha)->translatedFormat('l, d \d\e F \d\e Y')) }}</span>
                    </span>

                    @if($esSuperAdmin)
                        <span class="inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/20 dark:text-blue-300">
                            <i class="ph-bold ph-buildings"></i>
                            <span>Visión Global</span>
                        </span>
                    @endif
                </div>

                <div class="flex items-baseline gap-2">
                    <h1 class="text-xl font-black tracking-tight text-titulo">
                        {{ $esSuperAdmin ? 'Panel institucional de cuidados' : 'Cola Operativa de Guardia' }}
                    </h1>
                    <span class="text-xs text-apoyo">|</span>
                    <p class="text-xs font-semibold text-apoyo">
                        {{ $esSuperAdmin ? 'Cobertura: todos los residentes y turnos' : 'Enfermero(a):' }}
                        @unless($esSuperAdmin)<strong class="text-parrafo">{{ auth()->user()->nombres }} {{ auth()->user()->ap_paterno }}</strong>@endunless
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn-secondary px-3.5 py-2 text-xs font-bold">
                    <i class="ph-bold ph-calendar-check text-sm"></i>
                    <span>Agenda</span>
                </a>
                <a href="{{ route('admin.enfermeria.registros') }}" class="rm-btn-secondary px-3.5 py-2 text-xs font-bold">
                    <i class="ph-bold ph-notebook text-sm"></i>
                    <span>{{ $esSuperAdmin ? 'Cuidados e incidentes' : 'Registrar cuidado' }}</span>
                </a>
                <button wire:click="$refresh" class="rm-btn-secondary px-3.5 py-2 text-xs font-bold">
                    <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                    <span>Actualizar</span>
                </button>
                <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn-primary px-3.5 py-2 text-xs font-bold">
                    <i class="ph-bold ph-users text-sm"></i>
                    <span>{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis Pacientes' }} ({{ $stats['pacientes'] }})</span>
                </a>
            </div>
        </div>

        {{-- 2. PRIMERA FILA DE INDICADORES (COMPACTAS Y CLICABLES) --}}
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 border-t border-borde pt-4">
            {{-- Pacientes --}}
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="group rounded-xl border border-borde bg-fondo-card/50 p-3 transition hover:border-boton-principal hover:bg-fondo-card block">
                <div class="flex items-center justify-between text-apoyo group-hover:text-boton-principal">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Pacientes</span>
                    <i class="ph-bold ph-users text-sm"></i>
                </div>
                <p class="text-xl font-black text-titulo mt-1">{{ $stats['pacientes'] }}</p>
                <span class="text-[10px] font-medium text-apoyo">{{ $esSuperAdmin ? 'Cobertura institucional' : 'Asignados en turno' }}</span>
            </a>

            {{-- Alertas --}}
            <a href="#zona-alertas" class="group rounded-xl border {{ $stats['alertas_activas'] > 0 ? 'border-red-200 bg-red-50/60 dark:bg-red-950/20' : 'border-borde bg-fondo-card/50' }} p-3 transition hover:border-red-400 block">
                <div class="flex items-center justify-between {{ $stats['alertas_activas'] > 0 ? 'text-red-600' : 'text-apoyo' }}">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Alertas</span>
                    <i class="ph-bold ph-bell-ringing text-sm"></i>
                </div>
                <p class="text-xl font-black {{ $stats['alertas_activas'] > 0 ? 'text-red-600' : 'text-titulo' }} mt-1">{{ $stats['alertas_activas'] }}</p>
                <span class="text-[10px] font-medium {{ $stats['alertas_activas'] > 0 ? 'text-red-700 dark:text-red-300' : 'text-apoyo' }}">
                    {{ $stats['alertas_criticas'] > 0 ? $stats['alertas_criticas'] . ' críticas' : ($stats['alertas_activas'] > 0 ? 'Por atender' : 'Sin alertas') }}
                </span>
            </a>

            {{-- Medicación --}}
            <a href="#zona-acciones" class="group rounded-xl border {{ $stats['medicacion_pendiente'] > 0 ? 'border-amber-200 bg-amber-50/60 dark:bg-amber-950/20' : 'border-borde bg-fondo-card/50' }} p-3 transition hover:border-amber-400 block">
                <div class="flex items-center justify-between {{ $stats['medicacion_pendiente'] > 0 ? 'text-amber-600' : 'text-apoyo' }}">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Medicación</span>
                    <i class="ph-bold ph-pill text-sm"></i>
                </div>
                <p class="text-xl font-black {{ $stats['medicacion_pendiente'] > 0 ? 'text-amber-600' : 'text-titulo' }} mt-1">{{ $stats['medicacion_pendiente'] }}</p>
                <span class="text-[10px] font-medium {{ $stats['medicacion_pendiente'] > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-apoyo' }}">Dosis pendientes</span>
            </a>

            {{-- Tareas --}}
            <a href="{{ route('admin.enfermeria.tareas') }}" class="group rounded-xl border {{ $stats['tareas_pendientes'] > 0 ? 'border-amber-200 bg-amber-50/60 dark:bg-amber-950/20' : 'border-borde bg-fondo-card/50' }} p-3 transition hover:border-amber-400 block">
                <div class="flex items-center justify-between {{ $stats['tareas_pendientes'] > 0 ? 'text-amber-600' : 'text-apoyo' }}">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Tareas Plan</span>
                    <i class="ph-bold ph-list-checks text-sm"></i>
                </div>
                <p class="text-xl font-black {{ $stats['tareas_pendientes'] > 0 ? 'text-amber-600' : 'text-titulo' }} mt-1">{{ $stats['tareas_pendientes'] }}</p>
                <span class="text-[10px] font-medium {{ $stats['tareas_vencidas'] > 0 ? 'text-red-600 font-bold' : ($stats['tareas_pendientes'] > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-apoyo') }}">
                    {{ $stats['tareas_vencidas'] > 0 ? $stats['tareas_vencidas'] . ' vencidas' : 'Por ejecutar' }}
                </span>
            </a>

            {{-- Seguimientos --}}
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="group rounded-xl border {{ $stats['seguimientos_faltantes'] > 0 ? 'border-blue-200 bg-blue-50/60 dark:bg-blue-950/20' : 'border-borde bg-fondo-card/50' }} p-3 transition hover:border-blue-400 block">
                <div class="flex items-center justify-between {{ $stats['seguimientos_faltantes'] > 0 ? 'text-blue-600' : 'text-apoyo' }}">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Seguimientos</span>
                    <i class="ph-bold ph-notebook text-sm"></i>
                </div>
                <p class="text-xl font-black {{ $stats['seguimientos_faltantes'] > 0 ? 'text-blue-600' : 'text-titulo' }} mt-1">{{ $stats['seguimientos_faltantes'] }}</p>
                <span class="text-[10px] font-medium {{ $stats['seguimientos_faltantes'] > 0 ? 'text-blue-700 dark:text-blue-300' : 'text-apoyo' }}">Faltan en guardia</span>
            </a>

            {{-- Pase de Turno --}}
            <a href="{{ route('admin.enfermeria.pase-turno') }}" class="group rounded-xl border border-borde bg-fondo-card/50 p-3 transition hover:border-boton-principal hover:bg-fondo-card block">
                <div class="flex items-center justify-between text-apoyo group-hover:text-boton-principal">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Pase Turno</span>
                    <i class="ph-bold ph-arrows-left-right text-sm"></i>
                </div>
                <p class="text-xs font-black uppercase text-boton-acento mt-2 truncate">{{ $stats['pase_estado'] }}</p>
                <span class="text-[10px] font-medium text-apoyo">Relevo de guardia</span>
            </a>
        </div>

        @if($esSuperAdmin)
            <div class="mt-3 grid grid-cols-2 gap-3 border-t border-borde pt-3 md:grid-cols-4">
                @foreach([
                    ['cuidados_registrados', 'Cuidados de hoy', 'ph-hand-heart', 'admin.enfermeria.registros'],
                    ['incidentes_abiertos', 'Incidentes abiertos', 'ph-warning-octagon', 'admin.enfermeria.registros'],
                    ['lesiones_activas', 'Lesiones activas', 'ph-bandaids', 'admin.enfermeria.registros'],
                    ['dispositivos_activos', 'Dispositivos activos', 'ph-first-aid-kit', 'admin.enfermeria.registros'],
                ] as [$clave, $etiqueta, $icono, $ruta])
                    <a href="{{ route($ruta) }}" class="rounded-xl border border-borde bg-fondo-card/50 p-3 transition hover:border-boton-principal">
                        <div class="flex items-center justify-between text-apoyo">
                            <span class="text-[10px] font-bold uppercase tracking-wider">{{ $etiqueta }}</span>
                            <i class="ph-bold {{ $icono }} text-sm"></i>
                        </div>
                        <p class="mt-1 text-xl font-black text-titulo">{{ $stats[$clave] }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- 3. SECCIÓN PRINCIPAL: PACIENTES PRIORITARIOS (TABLA INSTITUCIONAL, 3-5 RESIDENTES) --}}
    <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex items-center justify-between border-b border-borde pb-3.5 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/30">
                    <i class="ph-bold ph-heart-straight text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wide text-titulo">
                        Pacientes Prioritarios
                    </h2>
                    <p class="text-xs text-apoyo">Residentes con alertas activas o acciones inmediatas requeridas</p>
                </div>
            </div>
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="text-xs font-bold text-boton-principal hover:underline flex items-center gap-1">
                <span>Ver todos los pacientes ({{ $stats['pacientes'] }})</span>
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        @if($pacientesPrioritarios->isEmpty())
            <div class="rounded-xl border border-dashed border-borde bg-fondo-card/20 p-6 text-center">
                <i class="ph-bold ph-check-circle text-2xl text-emerald-600 mb-1"></i>
                <p class="text-xs font-bold text-titulo">No hay pacientes con prioridades críticas en este turno</p>
                <p class="text-[11px] text-apoyo">Todos los residentes asignados se encuentran con signos estables y tareas al día.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-borde bg-fondo-card/40 text-[10px] font-black uppercase tracking-wider text-apoyo">
                            <th class="px-3.5 py-2.5">Paciente</th>
                            <th class="px-3.5 py-2.5">Habitación / Cama</th>
                            <th class="px-3.5 py-2.5">Estado</th>
                            <th class="px-3.5 py-2.5">Próxima Acción</th>
                            <th class="px-3.5 py-2.5 text-center">Alertas</th>
                            <th class="px-3.5 py-2.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde">
                        @foreach($pacientesPrioritarios as $item)
                            @php
                                $p = $item['paciente'];
                                $estado = $item['estado'];
                                $proxima = $item['proxima_accion'];
                            @endphp
                            <tr class="hover:bg-fondo-card/30 transition">
                                {{-- Paciente --}}
                                <td class="px-3.5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-fondo-card border border-borde font-bold text-titulo text-xs">
                                            {{ substr($p->nombres, 0, 1) }}{{ substr($p->ap_paterno, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.enfermeria.pacientes.ficha', $p->cod_am) }}" class="font-bold text-titulo hover:text-boton-principal transition">
                                                {{ $p->nombres }} {{ $p->ap_paterno }}
                                            </a>
                                            <div class="text-[10px] text-apoyo">
                                                <span>{{ $p->cod_am }}</span>
                                                @if($p->fecha_nacimiento)
                                                    <span>· {{ \Carbon\Carbon::parse($p->fecha_nacimiento)->age }} años</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Habitación / Cama --}}
                                <td class="px-3.5 py-3">
                                    <span class="inline-flex items-center gap-1 font-semibold text-parrafo">
                                        <i class="ph-bold ph-bed text-apoyo"></i>
                                        <span>{{ $p->habitacion->codigo ?? 'Sin hab.' }}</span>
                                        @if($p->cama)
                                            <span class="text-apoyo font-normal">(Cama {{ $p->cama->numero ?? $p->cama->codigo ?? '1' }})</span>
                                        @endif
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="px-3.5 py-3">
                                    @if($estado === 'ATENCION')
                                        <span class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-black uppercase text-red-700 dark:bg-red-950/30 dark:text-red-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                            <span>Atención</span>
                                        </span>
                                    @elseif($estado === 'VIGILANCIA')
                                        <span class="inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-black uppercase text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            <span>Vigilancia</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Estable</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Próxima acción --}}
                                <td class="px-3.5 py-3">
                                    <div class="flex items-center gap-1.5 max-w-xs truncate text-parrafo">
                                        <i class="ph-bold {{ $proxima['icono'] }} text-sm shrink-0 text-apoyo"></i>
                                        <span class="truncate font-medium">{{ $proxima['texto'] }}</span>
                                    </div>
                                </td>

                                {{-- Alertas --}}
                                <td class="px-3.5 py-3 text-center">
                                    @if($item['alertas_count'] > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-black {{ $item['alerta_max'] === 'CRITICO' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                                            <i class="ph-bold ph-warning"></i>
                                            <span>{{ $item['alertas_count'] }} {{ $item['alerta_max'] ?? 'activa' }}</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] font-medium text-apoyo">Sin alertas</span>
                                    @endif
                                </td>

                                {{-- Acción --}}
                                <td class="px-3.5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="abrirRegistrarSignos('{{ $p->cod_am }}')" class="rm-btn-secondary px-2.5 py-1 text-[11px] font-bold" title="Control rápido de signos">
                                            <i class="ph-bold ph-heartbeat"></i>
                                            <span class="hidden sm:inline">Signos</span>
                                        </button>
                                        <a href="{{ route('admin.enfermeria.pacientes.ficha', $p->cod_am) }}" class="rm-btn-primary px-2.5 py-1 text-[11px] font-bold">
                                            <span>Ficha 360°</span>
                                            <i class="ph-bold ph-arrow-right"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 4. SEGUNDA ZONA: DOS COLUMNAS (PRÓXIMAS ACCIONES DEL TURNO VS ALERTAS RECIENTES) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Columna Izquierda: Próximas acciones del turno --}}
        <div id="zona-acciones" class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-borde pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/30">
                        <i class="ph-bold ph-clock-countdown text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wide text-titulo">
                            Próximas Acciones del Turno
                        </h2>
                        <p class="text-xs text-apoyo">Medicación y tareas programadas por ejecutar</p>
                    </div>
                </div>
                <span class="rounded-full bg-fondo-card border border-borde px-2.5 py-0.5 text-[10px] font-bold text-apoyo">
                    {{ $proximasAcciones->count() }} inmediatas
                </span>
            </div>

            @if($proximasAcciones->isEmpty())
                <div class="rounded-xl border border-dashed border-borde bg-fondo-card/20 p-6 text-center">
                    <i class="ph-bold ph-check-circle text-2xl text-emerald-600 mb-1"></i>
                    <p class="text-xs font-bold text-titulo">Sin acciones pendientes en este bloque</p>
                    <p class="text-[11px] text-apoyo">Todas las tomas y tareas programadas han sido registradas.</p>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($proximasAcciones as $accion)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-borde bg-fondo-card/40 p-3 hover:bg-fondo-card/70 transition">
                            <div class="flex items-start gap-2.5">
                                <span class="font-mono text-xs font-black rounded-lg bg-fondo-panel border border-borde px-2 py-1 text-titulo shrink-0 mt-0.5">
                                    {{ $accion['hora'] }}
                                </span>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($accion['tipo'] === 'MEDICACION')
                                            <span class="rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.2 text-[9px] font-black uppercase">
                                                Medicación
                                            </span>
                                        @else
                                            <span class="rounded bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.2 text-[9px] font-black uppercase">
                                                Tarea
                                            </span>
                                        @endif
                                        <a href="{{ route('admin.enfermeria.pacientes.ficha', $accion['paciente']->cod_am) }}" class="text-xs font-bold text-titulo hover:text-boton-principal">
                                            {{ $accion['paciente']->nombres }} {{ $accion['paciente']->ap_paterno }}
                                        </a>
                                        <span class="text-[10px] text-apoyo">
                                            (Hab. {{ $accion['paciente']->habitacion->codigo ?? 'N/A' }})
                                        </span>
                                    </div>
                                    <p class="text-xs font-medium text-parrafo">
                                        {{ $accion['titulo'] }}
                                    </p>
                                    <p class="text-[10px] text-apoyo">
                                        {{ $accion['detalle'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                @if($accion['tipo'] === 'MEDICACION')
                                    <button wire:click="administrarMed('{{ $accion['item_med']['medicacion']->cod_med_adulto }}', '{{ $accion['paciente']->cod_am }}')" class="rm-btn-primary px-2.5 py-1 text-[11px] font-bold">
                                        <i class="ph-bold ph-check"></i>
                                        <span>Administrar</span>
                                    </button>
                                    <button wire:click="abrirOmitirMed('{{ $accion['item_med']['medicacion']->cod_med_adulto }}', '{{ $accion['paciente']->cod_am }}')" class="rm-btn-secondary px-2 py-1 text-[11px] font-bold text-amber-700 hover:text-amber-800" title="Omitir toma">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                @else
                                    <button wire:click="completarTarea('{{ $accion['item_tarea']->cod_tarea }}')" class="rm-btn-primary px-2.5 py-1 text-[11px] font-bold">
                                        <i class="ph-bold ph-check"></i>
                                        <span>Completar</span>
                                    </button>
                                    <button wire:click="abrirOmitirTarea('{{ $accion['item_tarea']->cod_tarea }}')" class="rm-btn-secondary px-2 py-1 text-[11px] font-bold text-amber-700 hover:text-amber-800" title="Omitir tarea">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Columna Derecha: Alertas recientes --}}
        <div id="zona-alertas" class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-borde pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $alertas->isNotEmpty() ? 'bg-red-50 text-red-600 dark:bg-red-950/30' : 'bg-fondo-card text-apoyo' }}">
                        <i class="ph-bold ph-bell-ringing text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wide text-titulo">
                            Alertas Recientes
                        </h2>
                        <p class="text-xs text-apoyo">Notificaciones y alertas activas de tus residentes</p>
                    </div>
                </div>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $alertas->isNotEmpty() ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-fondo-card text-apoyo border border-borde' }}">
                    {{ $alertas->count() }} activas
                </span>
            </div>

            @if($alertas->isEmpty())
                <div class="rounded-xl border border-dashed border-borde bg-fondo-card/20 p-6 text-center">
                    <i class="ph-bold ph-shield-check text-2xl text-emerald-600 mb-1"></i>
                    <p class="text-xs font-bold text-titulo">Sin alertas clínicas activas</p>
                    <p class="text-[11px] text-apoyo">Todos los signos vitales y condiciones están bajo control.</p>
                </div>
            @else
                <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    @foreach($alertas as $alerta)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-3 transition {{ $alerta->nivel === 'CRITICO' ? 'border-red-300 bg-red-50/50 dark:bg-red-950/20' : ($alerta->nivel === 'ALTO' ? 'border-amber-300 bg-amber-50/40 dark:bg-amber-950/20' : 'border-borde bg-fondo-card/40') }}">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex shrink-0 items-center justify-center rounded px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $alerta->nivel === 'CRITICO' ? 'bg-red-600 text-white' : ($alerta->nivel === 'ALTO' ? 'bg-amber-500 text-white' : 'bg-blue-600 text-white') }}">
                                        {{ $alerta->nivel }}
                                    </span>
                                    <h3 class="text-xs font-black text-titulo">
                                        {{ $alerta->tipo_alerta }}
                                    </h3>
                                    <span class="text-[10px] text-apoyo">·</span>
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', $alerta->cod_am) }}?tab=alertas" class="text-xs font-bold text-boton-principal hover:underline">
                                        {{ $alerta->adultoMayor->nombres }} {{ $alerta->adultoMayor->ap_paterno }}
                                    </a>
                                </div>
                                <p class="text-xs text-parrafo">
                                    {{ $alerta->descripcion }}
                                </p>
                                <p class="text-[10px] text-apoyo">
                                    Hab. {{ $alerta->adultoMayor->habitacion->codigo ?? 'N/A' }} · Hace {{ $alerta->created_at->diffForHumans(null, true) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                @if($alerta->estado === 'ABIERTA')
                                    <button wire:click="abrirAtenderAlerta('{{ $alerta->cod_alerta }}')" class="rm-btn-primary px-2.5 py-1 text-[11px] font-bold">
                                        <i class="ph-bold ph-hand-pointing"></i>
                                        <span>Atender</span>
                                    </button>
                                @else
                                    <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">
                                        En Atención
                                    </span>
                                @endif
                                <button wire:click="abrirCerrarAlerta('{{ $alerta->cod_alerta }}')" class="rm-btn-secondary px-2.5 py-1 text-[11px] font-bold">
                                    <i class="ph-bold ph-check"></i>
                                    <span>Cerrar</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- 5. MÁXIMO 2 GRÁFICAS ÚTILES (DATOS REALES) --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Gráfica 1: Cumplimiento del turno (% tareas y seguimientos) --}}
        <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-borde pb-3 mb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-chart-donut text-boton-principal text-base"></i>
                        <h3 class="text-xs font-black uppercase tracking-wide text-titulo">
                            Cumplimiento del Turno Operativo
                        </h3>
                    </div>
                    <span class="rounded-full bg-fondo-card border border-borde px-2 py-0.5 text-[10px] font-black text-parrafo">
                        {{ $cumplimientoTurno['porcentaje'] }}% completado
                    </span>
                </div>
                <p class="text-xs text-apoyo mb-3">Progreso consolidado de tareas y seguimientos diarios de la guardia</p>

                <div class="h-44 w-full relative flex items-center justify-center" wire:ignore x-data="{
                    chart: null,
                    init() {
                        if (typeof window.Chart === 'undefined') return;
                        const ctx = this.$refs.canvas.getContext('2d');
                        const dataCompletada = {{ $cumplimientoTurno['completadas'] }};
                        const dataPendiente = {{ max(0, $cumplimientoTurno['total_acciones'] - $cumplimientoTurno['completadas']) }};
                        
                        this.chart = new window.Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Realizadas', 'Pendientes'],
                                datasets: [{
                                    data: [dataCompletada, dataPendiente],
                                    backgroundColor: ['#3F7D5A', '#E9A05F'],
                                    borderWidth: 2,
                                    borderColor: '#FFF8F1',
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '75%',
                                plugins: {
                                    datalabels: { display: false },
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: { size: 10, weight: 'bold' }, padding: 12 }
                                    },
                                    tooltip: {
                                        padding: 8,
                                        cornerRadius: 8,
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas" class="max-h-44"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-5">
                        <span class="text-xl font-black text-titulo">{{ $cumplimientoTurno['porcentaje'] }}%</span>
                        <span class="text-[9px] uppercase font-bold text-apoyo">Completitud</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 border-t border-borde pt-3 mt-3 text-center">
                <div class="rounded-lg bg-fondo-card/50 p-2">
                    <span class="text-[10px] font-bold text-apoyo block uppercase">Tareas Realizadas</span>
                    <span class="text-xs font-black text-titulo">{{ $cumplimientoTurno['tareas_completadas'] }} / {{ $cumplimientoTurno['tareas_completadas'] + $cumplimientoTurno['tareas_pendientes'] }}</span>
                </div>
                <div class="rounded-lg bg-fondo-card/50 p-2">
                    <span class="text-[10px] font-bold text-apoyo block uppercase">Seguimientos Listos</span>
                    <span class="text-xs font-black text-titulo">{{ $cumplimientoTurno['seguimientos_completados'] }} / {{ $cumplimientoTurno['seguimientos_completados'] + $cumplimientoTurno['seguimientos_pendientes'] }}</span>
                </div>
            </div>
        </div>

        {{-- Gráfica 2: Distribución de pacientes (estable / vigilancia / atención) --}}
        <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-borde pb-3 mb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-chart-pie-slice text-boton-principal text-base"></i>
                        <h3 class="text-xs font-black uppercase tracking-wide text-titulo">
                            Distribución Asistencial de Pacientes
                        </h3>
                    </div>
                    <span class="rounded-full bg-fondo-card border border-borde px-2 py-0.5 text-[10px] font-black text-parrafo">
                        {{ $stats['pacientes'] }} residentes
                    </span>
                </div>
                <p class="text-xs text-apoyo mb-3">Clasificación según nivel de criticidad y controles pendientes</p>

                <div class="h-44 w-full relative flex items-center justify-center" wire:ignore x-data="{
                    chart: null,
                    init() {
                        if (typeof window.Chart === 'undefined') return;
                        const ctx = this.$refs.canvas.getContext('2d');
                        const estable = {{ $distribucionPacientes['estable'] }};
                        const vigilancia = {{ $distribucionPacientes['vigilancia'] }};
                        const atencion = {{ $distribucionPacientes['atencion'] }};
                        
                        this.chart = new window.Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Estables', 'Vigilancia', 'Atención Prioritaria'],
                                datasets: [{
                                    data: [estable, vigilancia, atencion],
                                    backgroundColor: ['#3F7D5A', '#E9A05F', '#C9654E'],
                                    borderWidth: 2,
                                    borderColor: '#FFF8F1',
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '75%',
                                plugins: {
                                    datalabels: { display: false },
                                    legend: {
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: { size: 10, weight: 'bold' }, padding: 12 }
                                    },
                                    tooltip: {
                                        padding: 8,
                                        cornerRadius: 8,
                                    }
                                }
                            }
                        });
                    }
                }">
                    <canvas x-ref="canvas" class="max-h-44"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-5">
                        <span class="text-xl font-black text-titulo">{{ $stats['pacientes'] }}</span>
                        <span class="text-[9px] uppercase font-bold text-apoyo">Residentes</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 border-t border-borde pt-3 mt-3 text-center">
                <div class="rounded-lg bg-emerald-50/70 dark:bg-emerald-950/20 p-2 border border-emerald-200">
                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 block uppercase">Estables</span>
                    <span class="text-xs font-black text-emerald-800 dark:text-emerald-200">{{ $distribucionPacientes['estable'] }}</span>
                </div>
                <div class="rounded-lg bg-amber-50/70 dark:bg-amber-950/20 p-2 border border-amber-200">
                    <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 block uppercase">Vigilancia</span>
                    <span class="text-xs font-black text-amber-800 dark:text-amber-200">{{ $distribucionPacientes['vigilancia'] }}</span>
                </div>
                <div class="rounded-lg bg-red-50/70 dark:bg-red-950/20 p-2 border border-red-200">
                    <span class="text-[10px] font-bold text-red-700 dark:text-red-300 block uppercase">Atención</span>
                    <span class="text-xs font-black text-red-800 dark:text-red-200">{{ $distribucionPacientes['atencion'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. AL FINAL: RESUMEN PEQUEÑO DEL PASE DE TURNO --}}
    <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-boton-principal/10 text-boton-principal">
                        <i class="ph-bold ph-arrows-left-right text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-wide text-titulo">
                        Resumen del Pase de Guardia
                    </h3>
                    <span class="rounded-md border border-borde bg-fondo-card px-2 py-0.5 text-[10px] font-black uppercase text-boton-acento">
                        {{ $stats['pase_estado'] }}
                    </span>
                </div>
                <p class="text-xs text-parrafo">
                    @if($paseTurnoHoy)
                        Relevo registrado de <strong>{{ $paseTurnoHoy->enfermeroSaliente->name ?? 'Enfermero(a)' }}</strong> hacia <strong>{{ $paseTurnoHoy->enfermeroEntrante->name ?? 'Por confirmar' }}</strong>.
                        @if($paseTurnoHoy->requiere_vigilancia_especial)
                            <span class="font-bold text-amber-700 block mt-0.5">· Vigilancia especial activa registrada en el pase.</span>
                        @endif
                    @else
                        El pase de este turno aún no ha sido redactado. Complete la entrega antes de finalizar su horario.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.enfermeria.pase-turno') }}" class="rm-btn-secondary px-3.5 py-2 text-xs font-bold">
                    <i class="ph-bold ph-clipboard-text"></i>
                    <span>Gestionar Pase de Turno</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 7. MODALES OPERATIVOS (PRESERVADOS Y FUNCIONALES) --}}

    {{-- Modal Omitir Tarea --}}
    @if($modalOmitirTarea)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-2xl border border-borde bg-fondo-panel p-5 shadow-lg">
                <h3 class="text-sm font-black text-titulo">Registrar Omisión de Tarea</h3>
                <p class="text-xs text-apoyo mt-1">Indique el motivo asistencial por el cual no se ejecutó la tarea.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-parrafo block mb-1">Motivo de omisión *</label>
                    <textarea wire:model="motivoOmisionTarea" rows="3" class="rm-input w-full text-xs" placeholder="Ej: Residente dormido en horario programado..."></textarea>
                    @error('motivoOmisionTarea') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="$set('modalOmitirTarea', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="confirmarOmisionTarea" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Confirmar Omisión</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Omitir Medicación --}}
    @if($modalOmitirMed)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-2xl border border-borde bg-fondo-panel p-5 shadow-lg">
                <h3 class="text-sm font-black text-titulo">Registrar Omisión de Medicamento</h3>
                <p class="text-xs text-apoyo mt-1">Justifique la razón clínica o de rechazo para no suministrar la dosis.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-parrafo block mb-1">Motivo clínico de omisión *</label>
                    <textarea wire:model="motivoOmisionMed" rows="3" class="rm-input w-full text-xs" placeholder="Ej: Rechazo explícito por náuseas, informado a médico..."></textarea>
                    @error('motivoOmisionMed') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="$set('modalOmitirMed', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="confirmarOmisionMed" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Confirmar Omisión</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Atender Alerta --}}
    @if($modalAtenderAlerta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-2xl border border-borde bg-fondo-panel p-5 shadow-lg">
                <h3 class="text-sm font-black text-titulo">Registrar Atención de Alerta</h3>
                <p class="text-xs text-apoyo mt-1">Describa la intervención o verificación inicial realizada.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-parrafo block mb-1">Acción tomada *</label>
                    <textarea wire:model="accionTomadaAlerta" rows="3" class="rm-input w-full text-xs" placeholder="Ej: Se acudió a habitación y se verificó saturación de O2..."></textarea>
                    @error('accionTomadaAlerta') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="$set('modalAtenderAlerta', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="confirmarAtencionAlerta" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Registrar Atención</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Cerrar Alerta --}}
    @if($modalCerrarAlerta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-2xl border border-borde bg-fondo-panel p-5 shadow-lg">
                <h3 class="text-sm font-black text-titulo">Cerrar Alerta Clínica</h3>
                <p class="text-xs text-apoyo mt-1">Registre el resultado final y la resolución de la condición clínica.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-parrafo block mb-1">Observación de Cierre *</label>
                    <textarea wire:model="observacionCierreAlerta" rows="3" class="rm-input w-full text-xs" placeholder="Ej: Parámetros estabilizados tras administración de medicación indicada..."></textarea>
                    @error('observacionCierreAlerta') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="$set('modalCerrarAlerta', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="confirmarCierreAlerta" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Confirmar Cierre</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Rápido de Signos Vitales --}}
    @if($modalSignos)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-2xl border border-borde bg-fondo-panel p-5 shadow-lg">
                <div class="flex items-center justify-between border-b border-borde pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-heartbeat text-boton-principal text-lg"></i>
                        <h3 class="text-sm font-black text-titulo">Control Rápido de Signos Vitales</h3>
                    </div>
                    <button wire:click="$set('modalSignos', false)" class="text-apoyo hover:text-parrafo">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <p class="text-xs text-apoyo mt-2">Registre los valores obtenidos en la valoración directa.</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Presión Arterial (PA)</label>
                        <input type="text" wire:model="signoPresion" placeholder="120/80" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Frec. Cardaca (bpm)</label>
                        <input type="number" wire:model="signoFC" placeholder="72" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Temperatura (°C)</label>
                        <input type="number" step="0.1" wire:model="signoTemp" placeholder="36.5" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Saturación SpO2 (%)</label>
                        <input type="number" wire:model="signoSat" placeholder="96" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Glucemia (mg/dL)</label>
                        <input type="number" step="0.1" wire:model="signoGlucosa" placeholder="95" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Dolor Escala EVA (0-10)</label>
                        <input type="number" min="0" max="10" wire:model="signoDolor" placeholder="0" class="rm-input w-full text-xs" />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="text-xs font-bold text-parrafo block mb-1">Observaciones</label>
                    <input type="text" wire:model="signoObservacion" placeholder="Opcional..." class="rm-input w-full text-xs" />
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-borde pt-3">
                    <button wire:click="$set('modalSignos', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="guardarSignos" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Guardar Signos</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Rápido de Seguimiento --}}
    @if($modalSeguimiento)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-2xl border border-borde bg-fondo-panel p-5 shadow-lg">
                <div class="flex items-center justify-between border-b border-borde pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-notebook text-boton-principal text-lg"></i>
                        <h3 class="text-sm font-black text-titulo">Registro de Seguimiento Diario</h3>
                    </div>
                    <button wire:click="$set('modalSeguimiento', false)" class="text-apoyo hover:text-parrafo">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <p class="text-xs text-apoyo mt-2">Evolución durante el turno activo para el residente.</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Estado General</label>
                        <select wire:model="segEstadoGeneral" class="rm-select w-full text-xs">
                            <option value="ESTABLE">Estable</option>
                            <option value="DELICADO">Delicado</option>
                            <option value="EN_OBSERVACION">En Observación</option>
                            <option value="CRITICO">Crítico</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Alimentación</label>
                        <select wire:model="segAlimentacion" class="rm-select w-full text-xs">
                            <option value="COMPLETA">Completa (100%)</option>
                            <option value="PARCIAL">Parcial (50-75%)</option>
                            <option value="ESCASA">Escasa (&lt; 50%)</option>
                            <option value="RECHAZO">Rechazo Total</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Movilidad</label>
                        <select wire:model="segMovilidad" class="rm-select w-full text-xs">
                            <option value="INDEPENDIENTE">Independiente</option>
                            <option value="ASISTIDA">Asistida</option>
                            <option value="EN_CAMA">En Cama / Reposo</option>
                            <option value="SILLA_RUEDAS">Silla de Ruedas</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Sueño / Descanso</label>
                        <select wire:model="segSueno" class="rm-select w-full text-xs">
                            <option value="NORMAL">Normal / Reparador</option>
                            <option value="INTERRUMPIDO">Interrumpido</option>
                            <option value="INSOMNIO">Insomnio</option>
                            <option value="SOMNOLENCIA">Somnolencia excesiva</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2 text-xs font-semibold text-parrafo cursor-pointer">
                        <input type="checkbox" wire:model="segIncidente" class="rounded text-boton-acento" />
                        <span>Ocurrió un incidente (caída, desorientación severa, etc.)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-parrafo cursor-pointer">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded text-boton-acento" />
                        <span>Requiere valoración o revisión médica inmediata</span>
                    </label>
                </div>

                <div class="mt-3">
                    <label class="text-xs font-bold text-parrafo block mb-1">Observaciones</label>
                    <textarea wire:model="segObservacion" rows="2" class="rm-input w-full text-xs" placeholder="Detalles de la guardia..."></textarea>
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-borde pt-3">
                    <button wire:click="$set('modalSeguimiento', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="guardarSeguimiento" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Guardar Seguimiento</button>
                </div>
            </div>
        </div>
    @endif
</div>
