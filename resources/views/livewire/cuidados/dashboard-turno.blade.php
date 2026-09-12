<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-5 pb-10">
    {{-- 1. CABECERA COMPACTA INSTITUCIONAL --}}
    <div class="rm-page-header-card rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-boton-principal/20 bg-boton-principal/5 px-2.5 py-1 text-xs font-bold text-boton-principal">
                        <i class="ph-bold ph-shield-check text-sm"></i>
                        <span>{{ $esSuperAdmin ? 'Supervisión global de Enfermería' : 'Dashboard de Enfermería' }}</span>
                    </span>

                    @if($turnoActual)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-text-title)]">
                            <i class="ph-bold ph-sun text-amber-600"></i>
                            <span>{{ $turnoActual->nombre }}</span>
                            <span class="text-[var(--rm-text-muted)]">({{ \Carbon\Carbon::parse($turnoActual->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turnoActual->hora_fin)->format('H:i') }})</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/20 dark:text-amber-300">
                            <i class="ph-bold ph-warning-circle"></i>
                            <span>Sin turno asignado</span>
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-text-body)]">
                        <i class="ph-bold ph-calendar-blank text-[var(--rm-text-muted)]"></i>
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
                    <h1 class="text-xl font-black tracking-tight text-[var(--rm-text-title)]">
                        {{ $esSuperAdmin ? 'Panel institucional de cuidados' : 'Cola Operativa de Guardia' }}
                    </h1>
                    <span class="text-xs text-[var(--rm-text-muted)]">|</span>
                    <p class="text-xs font-semibold text-[var(--rm-text-muted)]">
                        {{ $esSuperAdmin ? 'Cobertura: todos los residentes y turnos' : 'Enfermero(a):' }}
                        @unless($esSuperAdmin)<strong class="text-[var(--rm-text-body)]">{{ auth()->user()->nombres }} {{ auth()->user()->ap_paterno }}</strong>@endunless
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
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 border-t border-[var(--rm-border)] pt-4">
            {{-- Pacientes --}}
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-card-metric rm-card-interactive border-l-4 border-l-[var(--rm-primary)] block cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="rm-metric-label">Pacientes</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface-alt)] text-[var(--rm-primary)]">
                        <i class="ph-bold ph-users text-sm"></i>
                    </span>
                </div>
                <p class="rm-metric-value mt-1">{{ $stats['pacientes'] }}</p>
                <div class="rm-metric-meta justify-between">
                    <span>{{ $esSuperAdmin ? 'Institucional' : 'En turno' }}</span>
                    <span class="font-semibold underline">Ver lista</span>
                </div>
            </a>

            {{-- Alertas --}}
            <a href="#zona-alertas" class="rm-card-metric rm-card-interactive border-l-4 border-l-[var(--rm-danger-action)] block cursor-pointer {{ $stats['alertas_activas'] > 0 ? 'ring-1 ring-rose-500/30' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="rm-metric-label">Alertas</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $stats['alertas_activas'] > 0 ? 'bg-rose-50 text-[var(--rm-danger-action)] dark:bg-rose-950/40' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)]' }}">
                        <i class="ph-bold ph-bell-ringing text-sm"></i>
                    </span>
                </div>
                <p class="rm-metric-value mt-1 {{ $stats['alertas_activas'] > 0 ? 'text-[var(--rm-danger-action)]' : '' }}">{{ $stats['alertas_activas'] }}</p>
                <div class="rm-metric-meta">
                    <span>{{ $stats['alertas_criticas'] > 0 ? $stats['alertas_criticas'] . ' críticas' : ($stats['alertas_activas'] > 0 ? 'Por atender' : 'Sin alertas') }}</span>
                </div>
            </a>

            {{-- Medicación --}}
            <a href="#zona-acciones" class="rm-card-metric rm-card-interactive border-l-4 border-l-[var(--rm-warning-action)] block cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="rm-metric-label">Medicación</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $stats['medicacion_pendiente'] > 0 ? 'bg-amber-50 text-[var(--rm-warning-action)] dark:bg-amber-950/40' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)]' }}">
                        <i class="ph-bold ph-pill text-sm"></i>
                    </span>
                </div>
                <p class="rm-metric-value mt-1 {{ $stats['medicacion_pendiente'] > 0 ? 'text-[var(--rm-warning-action)]' : '' }}">{{ $stats['medicacion_pendiente'] }}</p>
                <div class="rm-metric-meta">
                    <span>Dosis pendientes</span>
                </div>
            </a>

            {{-- Tareas --}}
            <a href="{{ route('admin.enfermeria.tareas') }}" class="rm-card-metric rm-card-interactive border-l-4 border-l-[var(--rm-accent)] block cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="rm-metric-label">Tareas Plan</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface-alt)] text-[var(--rm-accent)]">
                        <i class="ph-bold ph-list-checks text-sm"></i>
                    </span>
                </div>
                <p class="rm-metric-value mt-1">{{ $stats['tareas_pendientes'] }}</p>
                <div class="rm-metric-meta">
                    <span>{{ $stats['tareas_vencidas'] > 0 ? $stats['tareas_vencidas'] . ' vencidas' : 'Por ejecutar' }}</span>
                </div>
            </a>

            {{-- Seguimientos --}}
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-card-metric rm-card-interactive border-l-4 border-l-[var(--rm-info-action)] block cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="rm-metric-label">Seguimientos</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface-alt)] text-[var(--rm-info-action)]">
                        <i class="ph-bold ph-notebook text-sm"></i>
                    </span>
                </div>
                <p class="rm-metric-value mt-1">{{ $stats['seguimientos_faltantes'] }}</p>
                <div class="rm-metric-meta">
                    <span>Faltan en guardia</span>
                </div>
            </a>

            {{-- Pase de Turno --}}
            <a href="{{ route('admin.enfermeria.pase-turno') }}" class="rm-card-metric rm-card-interactive border-l-4 border-l-[var(--rm-state-success-border)] block cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="rm-metric-label">Pase Turno</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface-alt)] text-[var(--rm-state-success-border)]">
                        <i class="ph-bold ph-arrows-left-right text-sm"></i>
                    </span>
                </div>
                <p class="text-xs font-black uppercase text-[var(--rm-accent)] mt-2 truncate">{{ $stats['pase_estado'] }}</p>
                <div class="rm-metric-meta">
                    <span>Relevo guardia</span>
                </div>
            </a>
        </div>

        @if($esSuperAdmin)
            <div class="mt-3 grid grid-cols-2 gap-3 border-t border-[var(--rm-border)] pt-3 md:grid-cols-4">
                @foreach([
                    ['cuidados_registrados', 'Cuidados de hoy', 'ph-hand-heart', 'admin.enfermeria.registros'],
                    ['incidentes_abiertos', 'Incidentes abiertos', 'ph-warning-octagon', 'admin.enfermeria.registros'],
                    ['lesiones_activas', 'Lesiones activas', 'ph-bandaids', 'admin.enfermeria.registros'],
                    ['dispositivos_activos', 'Dispositivos activos', 'ph-first-aid-kit', 'admin.enfermeria.registros'],
                ] as [$clave, $etiqueta, $icono, $ruta])
                    <a href="{{ route($ruta) }}" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/60 p-3 transition hover:border-boton-principal">
                        <div class="flex items-center justify-between text-[var(--rm-text-muted)]">
                            <span class="text-[10px] font-bold uppercase tracking-wider">{{ $etiqueta }}</span>
                            <i class="ph-bold {{ $icono }} text-sm"></i>
                        </div>
                        <p class="mt-1 text-xl font-black text-[var(--rm-text-title)]">{{ $stats[$clave] }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- 3. SECCIÓN PRINCIPAL: PACIENTES PRIORITARIOS (TABLA INSTITUCIONAL, 3-5 RESIDENTES) --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3.5 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-950/30">
                    <i class="ph-bold ph-heart-straight text-lg"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wide text-[var(--rm-text-title)]">
                        Pacientes Prioritarios
                    </h2>
                    <p class="text-xs text-[var(--rm-text-muted)]">Residentes con alertas activas o acciones inmediatas requeridas</p>
                </div>
            </div>
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="text-xs font-bold text-boton-principal hover:underline flex items-center gap-1">
                <span>Ver todos los pacientes ({{ $stats['pacientes'] }})</span>
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>

        @if($pacientesPrioritarios->isEmpty())
            <div class="rounded-xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/20 p-6 text-center">
                <i class="ph-bold ph-check-circle text-2xl text-emerald-600 mb-1"></i>
                <p class="text-xs font-bold text-[var(--rm-text-title)]">No hay pacientes con prioridades críticas en este turno</p>
                <p class="text-[11px] text-[var(--rm-text-muted)]">Todos los residentes asignados se encuentran con signos estables y tareas al día.</p>
            </div>
        @else
            <div class="rm-table-container">
                <table class="rm-table text-xs">
                    <thead>
                        <tr class="rm-table-header">
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
                            <tr class="rm-table-row">
                                {{-- Paciente --}}
                                <td class="px-3.5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] font-bold text-[var(--rm-text-title)] text-xs">
                                            {{ substr($p->nombres, 0, 1) }}{{ substr($p->ap_paterno, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.enfermeria.pacientes.ficha', $p->cod_am) }}" class="font-bold text-[var(--rm-text-title)] hover:text-boton-principal transition">
                                                {{ $p->nombres }} {{ $p->ap_paterno }}
                                            </a>
                                            <div class="text-[10px] text-[var(--rm-text-muted)]">
                                                <span>{{ $p->ci ? 'CI '.$p->ci : 'Documento no registrado' }}</span>
                                                @if($p->fecha_nacimiento)
                                                    <span>· {{ \Carbon\Carbon::parse($p->fecha_nacimiento)->age }} años</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Habitación / Cama --}}
                                <td class="px-3.5 py-3">
                                    <span class="inline-flex items-center gap-1 font-semibold text-[var(--rm-text-body)]">
                                        <i class="ph-bold ph-bed text-[var(--rm-text-muted)]"></i>
                                        <span>{{ $p->habitacion_texto }}</span>
                                        @if($p->cama)
                                            <span class="text-[var(--rm-text-muted)] font-normal">({{ $p->cama_texto }})</span>
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
                                    <div class="flex items-center gap-1.5 max-w-xs truncate text-[var(--rm-text-body)]">
                                        <i class="ph-bold {{ $proxima['icono'] }} text-sm shrink-0 text-[var(--rm-text-muted)]"></i>
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
                                        <span class="text-[10px] font-medium text-[var(--rm-text-muted)]">Sin alertas</span>
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
        <div id="zona-acciones" class="rm-card rm-card-glass p-4 sm:p-5 rounded-2xl border border-[var(--rm-border)] space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/30">
                        <i class="ph-bold ph-clock-countdown text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wide text-[var(--rm-text-title)]">
                            Próximas Acciones del Turno
                        </h2>
                        <p class="text-xs text-[var(--rm-text-muted)]">Medicación y tareas programadas por ejecutar</p>
                    </div>
                </div>
                <span class="rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] px-2.5 py-0.5 text-[10px] font-bold text-[var(--rm-text-muted)]">
                    {{ $proximasAcciones->count() }} inmediatas
                </span>
            </div>

            @if($proximasAcciones->isEmpty())
                <div class="rounded-xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/20 p-6 text-center">
                    <i class="ph-bold ph-check-circle text-2xl text-emerald-600 mb-1"></i>
                    <p class="text-xs font-bold text-[var(--rm-text-title)]">Sin acciones pendientes en este bloque</p>
                    <p class="text-[11px] text-[var(--rm-text-muted)]">Todas las tomas y tareas programadas han sido registradas.</p>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($proximasAcciones as $accion)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/40 p-3 hover:bg-[var(--rm-surface-alt)]/70 transition">
                            <div class="flex items-start gap-2.5">
                                <span class="font-mono text-xs font-black rounded-lg bg-[var(--rm-surface)] border border-[var(--rm-border)] px-2 py-1 text-[var(--rm-text-title)] shrink-0 mt-0.5">
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
                                        <a href="{{ route('admin.enfermeria.pacientes.ficha', $accion['paciente']->cod_am) }}" class="text-xs font-bold text-[var(--rm-text-title)] hover:text-boton-principal">
                                            {{ $accion['paciente']->nombres }} {{ $accion['paciente']->ap_paterno }}
                                        </a>
                                        <span class="text-[10px] text-[var(--rm-text-muted)]">
                                            ({{ $accion['paciente']->habitacion_texto }})
                                        </span>
                                    </div>
                                    <p class="text-xs font-medium text-[var(--rm-text-body)]">
                                        {{ $accion['titulo'] }}
                                    </p>
                                    <p class="text-[10px] text-[var(--rm-text-muted)]">
                                        {{ $accion['detalle'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                @if($accion['tipo'] === 'MEDICACION')
                                    <button wire:click="administrarMed('{{ $accion['item_med']['medicacion']->cod_med_adulto }}', '{{ $accion['paciente']->cod_am }}', '{{ $accion['hora'] }}')" class="rm-btn-primary px-2.5 py-1 text-[11px] font-bold">
                                        <i class="ph-bold ph-check"></i>
                                        <span>Administrar</span>
                                    </button>
                                    <button wire:click="abrirOmitirMed('{{ $accion['item_med']['medicacion']->cod_med_adulto }}', '{{ $accion['paciente']->cod_am }}', '{{ $accion['hora'] }}')" class="rm-btn-secondary px-2 py-1 text-[11px] font-bold text-amber-700 hover:text-amber-800" title="Omitir toma">
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
        <div id="zona-alertas" class="rm-card rm-card-glass p-4 sm:p-5 rounded-2xl border border-[var(--rm-border)] space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $alertas->isNotEmpty() ? 'bg-red-50 text-red-600 dark:bg-red-950/30' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)]' }}">
                        <i class="ph-bold ph-bell-ringing text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wide text-[var(--rm-text-title)]">
                            Alertas Recientes
                        </h2>
                        <p class="text-xs text-[var(--rm-text-muted)]">Notificaciones y alertas activas de tus residentes</p>
                    </div>
                </div>
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $alertas->isNotEmpty() ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border border-[var(--rm-border)]' }}">
                    {{ $alertas->count() }} activas
                </span>
            </div>

            @if($alertas->isEmpty())
                <div class="rounded-xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/20 p-6 text-center">
                    <i class="ph-bold ph-shield-check text-2xl text-emerald-600 mb-1"></i>
                    <p class="text-xs font-bold text-[var(--rm-text-title)]">Sin alertas clínicas activas</p>
                    <p class="text-[11px] text-[var(--rm-text-muted)]">Todos los signos vitales y condiciones están bajo control.</p>
                </div>
            @else
                <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    @foreach($alertas as $alerta)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-3 transition {{ $alerta->nivel === 'CRITICO' ? 'border-red-300 bg-red-50/50 dark:bg-red-950/20' : ($alerta->nivel === 'ALTO' ? 'border-amber-300 bg-amber-50/40 dark:bg-amber-950/20' : 'border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/40') }}">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex shrink-0 items-center justify-center rounded px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $alerta->nivel === 'CRITICO' ? 'bg-red-600 text-white' : ($alerta->nivel === 'ALTO' ? 'bg-amber-500 text-white' : 'bg-blue-600 text-white') }}">
                                        {{ $alerta->nivel }}
                                    </span>
                                    <h3 class="text-xs font-black text-[var(--rm-text-title)]">
                                        {{ $alerta->tipo_alerta }}
                                    </h3>
                                    <span class="text-[10px] text-[var(--rm-text-muted)]">·</span>
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', $alerta->cod_am) }}?tab=alertas" class="text-xs font-bold text-boton-principal hover:underline">
                                        {{ $alerta->adultoMayor->nombres }} {{ $alerta->adultoMayor->ap_paterno }}
                                    </a>
                                </div>
                                <p class="text-xs text-[var(--rm-text-body)]">
                                    {{ $alerta->descripcion }}
                                </p>
                                <p class="text-[10px] text-[var(--rm-text-muted)]">
                                    {{ $alerta->adultoMayor?->habitacion_texto ?? 'Habitación no asignada' }} · Hace {{ $alerta->created_at->diffForHumans(null, true) }}
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
        {{-- Gráfica 1: Cumplimiento del turno operativo (Design System Translúcido Grueso) --}}
        <div class="rm-chart-card rm-chart-glass flex flex-col justify-between">
            <div>
                <div class="rm-chart-header border-b border-[var(--rm-border)] pb-3 mb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-chart-donut text-[var(--rm-primary)] text-base"></i>
                        <h3 class="rm-chart-title">
                            Cumplimiento del Turno Operativo
                        </h3>
                    </div>
                    <span class="rm-chart-kpi-badge">
                        {{ $cumplimientoTurno['porcentaje'] }}% completado
                    </span>
                </div>
                <p class="rm-chart-subtitle mb-3">Progreso consolidado de tareas y seguimientos diarios de la guardia</p>

                <div class="h-44 w-full relative flex items-center justify-center" wire:ignore x-data="{
                    render() {
                        if (typeof window.RMCharts === 'undefined' || !window.RMCharts.presets) {
                            setTimeout(() => this.render(), 80);
                            return;
                        }
                        const sem = window.RMCharts.semanticColors();
                        const dataCompletada = {{ $cumplimientoTurno['completadas'] }};
                        const dataPendiente = {{ max(0, $cumplimientoTurno['total_acciones'] - $cumplimientoTurno['completadas']) }};
                        
                        const config = window.RMCharts.presets.doughnut(
                            ['Realizadas', 'Pendientes'],
                            [dataCompletada, dataPendiente],
                            [sem.success, sem.warning],
                            {
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: { size: 10, weight: 'bold' }, padding: 12 }
                                    }
                                }
                            }
                        );

                        window.RMCharts.init('dashboard_turno_cumplimiento', this.$refs.canvas, config, () => this.render());
                    },
                    init() {
                        this.$nextTick(() => this.render());
                        window.RMCharts?.onThemeChange(() => this.render());
                    }
                }">
                    <canvas x-ref="canvas" class="max-h-44"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-5">
                        <span class="text-xl font-extrabold text-[var(--rm-text-title)]">{{ $cumplimientoTurno['porcentaje'] }}%</span>
                        <span class="text-[9px] uppercase font-bold text-[var(--rm-text-muted)]">Completitud</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 border-t border-[var(--rm-border)] pt-3 mt-3 text-center">
                <div class="rounded-xl bg-[var(--rm-surface-alt)] p-2 border border-[var(--rm-border)]">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] block uppercase">Tareas Realizadas</span>
                    <span class="text-xs font-black text-[var(--rm-text-title)]">{{ $cumplimientoTurno['tareas_completadas'] }} / {{ $cumplimientoTurno['tareas_completadas'] + $cumplimientoTurno['tareas_pendientes'] }}</span>
                </div>
                <div class="rounded-xl bg-[var(--rm-surface-alt)] p-2 border border-[var(--rm-border)]">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] block uppercase">Seguimientos Listos</span>
                    <span class="text-xs font-black text-[var(--rm-text-title)]">{{ $cumplimientoTurno['seguimientos_completados'] }} / {{ $cumplimientoTurno['seguimientos_completados'] + $cumplimientoTurno['seguimientos_pendientes'] }}</span>
                </div>
            </div>
        </div>

        {{-- Gráfica 2: Distribución de pacientes (estable / vigilancia / atención) --}}
        <div class="rm-chart-card rm-chart-glass flex flex-col justify-between">
            <div>
                <div class="rm-chart-header border-b border-[var(--rm-border)] pb-3 mb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-chart-pie-slice text-[var(--rm-primary)] text-base"></i>
                        <h3 class="rm-chart-title">
                            Distribución Asistencial de Pacientes
                        </h3>
                    </div>
                    <span class="rm-chart-kpi-badge">
                        {{ $stats['pacientes'] }} residentes
                    </span>
                </div>
                <p class="rm-chart-subtitle mb-3">Clasificación según nivel de criticidad y controles pendientes</p>

                <div class="h-44 w-full relative flex items-center justify-center" wire:ignore x-data="{
                    render() {
                        if (typeof window.RMCharts === 'undefined' || !window.RMCharts.presets) {
                            setTimeout(() => this.render(), 80);
                            return;
                        }
                        const sem = window.RMCharts.semanticColors();
                        const estable = {{ $distribucionPacientes['estable'] }};
                        const vigilancia = {{ $distribucionPacientes['vigilancia'] }};
                        const atencion = {{ $distribucionPacientes['atencion'] }};
                        
                        const config = window.RMCharts.presets.doughnut(
                            ['Estables', 'Vigilancia', 'Atención Prioritaria'],
                            [estable, vigilancia, atencion],
                            [sem.success, sem.warning, sem.danger],
                            {
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: { boxWidth: 10, font: { size: 10, weight: 'bold' }, padding: 12 }
                                    }
                                }
                            }
                        );

                        window.RMCharts.init('dashboard_turno_distribucion', this.$refs.canvas, config, () => this.render());
                    },
                    init() {
                        this.$nextTick(() => this.render());
                        window.RMCharts?.onThemeChange(() => this.render());
                    }
                }">
                    <canvas x-ref="canvas" class="max-h-44"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-5">
                        <span class="text-xl font-extrabold text-[var(--rm-text-title)]">{{ $stats['pacientes'] }}</span>
                        <span class="text-[9px] uppercase font-bold text-[var(--rm-text-muted)]">Residentes</span>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-3 gap-2 border-t border-[var(--rm-border)] pt-3 mt-3 text-center">
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
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-boton-principal/10 text-boton-principal">
                        <i class="ph-bold ph-arrows-left-right text-sm"></i>
                    </span>
                    <h3 class="text-xs font-black uppercase tracking-wide text-[var(--rm-text-title)]">
                        Resumen del Pase de Guardia
                    </h3>
                    <span class="rounded-md border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-2 py-0.5 text-[10px] font-black uppercase text-boton-acento">
                        {{ $stats['pase_estado'] }}
                    </span>
                </div>
                <p class="text-xs text-[var(--rm-text-body)]">
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
            <div class="w-full max-w-md rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-lg">
                <h3 class="text-sm font-black text-[var(--rm-text-title)]">Registrar Omisión de Tarea</h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-1">Indique el motivo asistencial por el cual no se ejecutó la tarea.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Motivo de omisión *</label>
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
            <div class="w-full max-w-md rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-lg">
                <h3 class="text-sm font-black text-[var(--rm-text-title)]">Registrar Omisión de Medicamento</h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-1">Justifique la razón clínica o de rechazo para no suministrar la dosis.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Motivo clínico de omisión *</label>
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
            <div class="w-full max-w-md rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-lg">
                <h3 class="text-sm font-black text-[var(--rm-text-title)]">Registrar Atención de Alerta</h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-1">Describa la intervención o verificación inicial realizada.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Acción tomada *</label>
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
            <div class="w-full max-w-md rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-lg">
                <h3 class="text-sm font-black text-[var(--rm-text-title)]">Cerrar Alerta Clínica</h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-1">Registre el resultado final y la resolución de la condición clínica.</p>
                <div class="mt-4">
                    <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Observación de Cierre *</label>
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
            <div class="w-full max-w-lg rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-lg">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-heartbeat text-boton-principal text-lg"></i>
                        <h3 class="text-sm font-black text-[var(--rm-text-title)]">Control Rápido de Signos Vitales</h3>
                    </div>
                    <button wire:click="$set('modalSignos', false)" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-body)]">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <p class="text-xs text-[var(--rm-text-muted)] mt-2">Registre los valores obtenidos en la valoración directa.</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Presión Arterial (PA)</label>
                        <input type="text" wire:model="signoPresion" placeholder="120/80" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Frec. Cardaca (bpm)</label>
                        <input type="number" wire:model="signoFC" placeholder="72" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Temperatura (°C)</label>
                        <input type="number" step="0.1" wire:model="signoTemp" placeholder="36.5" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Saturación SpO2 (%)</label>
                        <input type="number" wire:model="signoSat" placeholder="96" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Glucemia (mg/dL)</label>
                        <input type="number" step="0.1" wire:model="signoGlucosa" placeholder="95" class="rm-input w-full text-xs" />
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Dolor Escala EVA (0-10)</label>
                        <input type="number" min="0" max="10" wire:model="signoDolor" placeholder="0" class="rm-input w-full text-xs" />
                    </div>
                </div>

                <div class="mt-3">
                    <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Observaciones</label>
                    <input type="text" wire:model="signoObservacion" placeholder="Opcional..." class="rm-input w-full text-xs" />
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-[var(--rm-border)] pt-3">
                    <button wire:click="$set('modalSignos', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="guardarSignos" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Guardar Signos</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Rápido de Seguimiento --}}
    @if($modalSeguimiento)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-lg">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-notebook text-boton-principal text-lg"></i>
                        <h3 class="text-sm font-black text-[var(--rm-text-title)]">Registro de Seguimiento Diario</h3>
                    </div>
                    <button wire:click="$set('modalSeguimiento', false)" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-body)]">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <p class="text-xs text-[var(--rm-text-muted)] mt-2">Evolución durante el turno activo para el residente.</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Estado General</label>
                        <select wire:model="segEstadoGeneral" class="rm-select w-full text-xs">
                            <option value="ESTABLE">Estable</option>
                            <option value="DELICADO">Delicado</option>
                            <option value="EN_OBSERVACION">En Observación</option>
                            <option value="CRITICO">Crítico</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Alimentación</label>
                        <select wire:model="segAlimentacion" class="rm-select w-full text-xs">
                            <option value="COMPLETA">Completa (100%)</option>
                            <option value="PARCIAL">Parcial (50-75%)</option>
                            <option value="ESCASA">Escasa (&lt; 50%)</option>
                            <option value="RECHAZO">Rechazo Total</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Movilidad</label>
                        <select wire:model="segMovilidad" class="rm-select w-full text-xs">
                            <option value="INDEPENDIENTE">Independiente</option>
                            <option value="ASISTIDA">Asistida</option>
                            <option value="EN_CAMA">En Cama / Reposo</option>
                            <option value="SILLA_RUEDAS">Silla de Ruedas</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Sueño / Descanso</label>
                        <select wire:model="segSueno" class="rm-select w-full text-xs">
                            <option value="NORMAL">Normal / Reparador</option>
                            <option value="INTERRUMPIDO">Interrumpido</option>
                            <option value="INSOMNIO">Insomnio</option>
                            <option value="SOMNOLENCIA">Somnolencia excesiva</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2 text-xs font-semibold text-[var(--rm-text-body)] cursor-pointer">
                        <input type="checkbox" wire:model="segIncidente" class="rounded text-boton-acento" />
                        <span>Ocurrió un incidente (caída, desorientación severa, etc.)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-[var(--rm-text-body)] cursor-pointer">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded text-boton-acento" />
                        <span>Requiere valoración o revisión médica inmediata</span>
                    </label>
                </div>

                <div class="mt-3">
                    <label class="text-xs font-bold text-[var(--rm-text-body)] block mb-1">Observaciones</label>
                    <textarea wire:model="segObservacion" rows="2" class="rm-input w-full text-xs" placeholder="Detalles de la guardia..."></textarea>
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-[var(--rm-border)] pt-3">
                    <button wire:click="$set('modalSeguimiento', false)" class="rm-btn-secondary px-3.5 py-1.5 text-xs font-bold">Cancelar</button>
                    <button wire:click="guardarSeguimiento" class="rm-btn-primary px-3.5 py-1.5 text-xs font-bold">Guardar Seguimiento</button>
                </div>
            </div>
        </div>
    @endif
</div>
