<div class="space-y-6 lg:space-y-8" x-data="{ initAos() { if(typeof AOS !== 'undefined') AOS.init({ once: true, duration: 800 }); } }" x-init="initAos()">
    @php
        $turnoVisible = $horarioAsignadoHoy ?: $horarioSiguienteAsignado;
    @endphp

    {{-- Header del Dashboard --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between" data-aos="fade-down">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-boton-acento/10 text-boton-acento shadow-glow transition-transform hover:scale-110">
                <i class="ph-fill ph-stethoscope text-3xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black tracking-tight text-titulo">
                    Dashboard Operativo
                </h2>
                <p class="text-sm font-semibold text-apoyo flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-fondo-card px-2.5 py-0.5 text-xs font-bold text-meta border border-borde">
                        <i class="ph-bold ph-clock"></i>
                        {{ $turnoVisible ? ($turnoVisible['turnos'][0]['turno'] ?? 'Sin turno asignado') . ' (' . ($turnoVisible['turnos'][0]['hora_inicio'] ?? '00:00') . ' - ' . ($turnoVisible['turnos'][0]['hora_fin'] ?? '00:00') . ')' : 'Sin turno asignado' }}
                    </span>
                    <span class="text-borde-suave">•</span>
                    <span>{{ $turnoVisible ? $turnoVisible['fecha_texto'] : \Carbon\Carbon::parse($filtroFecha)->translatedFormat('d \d\e F, Y') }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="$refresh" class="rm-btn-secondary h-11 px-5 rounded-xl hover:shadow-md transition-all active:scale-95">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline font-bold">Refrescar</span>
            </button>
            <button wire:click="exportarReporte" class="rm-btn-primary h-11 px-5 rounded-xl shadow-glow transition-all hover:scale-105 active:scale-95">
                <i class="ph-bold ph-file-pdf text-lg"></i>
                <span class="hidden sm:inline font-bold">Reporte de Turno</span>
            </button>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3" data-aos="fade-up" data-aos-delay="120">
        <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Turno asignado hoy</p>
                    <h3 class="mt-2 text-lg font-black text-titulo">
                        {{ $horarioAsignadoHoy['turnos'][0]['turno'] ?? 'Sin asignación hoy' }}
                    </h3>
                    <p class="mt-1 text-xs font-semibold text-apoyo">
                        {{ $horarioAsignadoHoy ? $horarioAsignadoHoy['fecha_texto'] . ' · ' . ($horarioAsignadoHoy['turnos'][0]['hora_inicio'] ?? '00:00') . ' - ' . ($horarioAsignadoHoy['turnos'][0]['hora_fin'] ?? '00:00') : 'No tienes turno asignado para hoy' }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-boton-acento/10 text-boton-acento">
                    <i class="ph-bold ph-clock text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Próxima fecha asignada</p>
                    <h3 class="mt-2 text-lg font-black text-titulo">
                        {{ $horarioSiguienteAsignado ? $horarioSiguienteAsignado['dia_semana'] : 'Sin próxima fecha' }}
                    </h3>
                    <p class="mt-1 text-xs font-semibold text-apoyo">
                        {{ $horarioSiguienteAsignado ? $horarioSiguienteAsignado['fecha_texto'] . ' · ' . ($horarioSiguienteAsignado['turnos'][0]['hora_inicio'] ?? '00:00') . ' - ' . ($horarioSiguienteAsignado['turnos'][0]['hora_fin'] ?? '00:00') : 'No hay próximas fechas cargadas' }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-infoBg/50 text-estado-info">
                    <i class="ph-bold ph-arrow-right text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Horarios cargados</p>
                    <h3 class="mt-2 text-lg font-black text-titulo">
                        {{ count($horariosPersonal ?? []) }} registros
                    </h3>
                    <p class="mt-1 text-xs font-semibold text-apoyo">Solo los horarios vinculados a tu usuario.</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-exitoBg/50 text-estado-exito">
                    <i class="ph-bold ph-calendar-check text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm" data-aos="fade-up" data-aos-delay="205">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-[11px] font-black uppercase tracking-widest text-parrafo">Areas operativas</h3>
                <p class="mt-1 text-xs font-semibold text-apoyo">Acceso directo a lo que el enfermero usa en su jornada.</p>
            </div>
            <span class="inline-flex rounded-full border border-borde bg-fondo-card px-3 py-1 text-[10px] font-black uppercase tracking-widest text-parrafo">
                {{ count($turnosActivos ?? []) }} turnos
            </span>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <a wire:navigate href="#calendario-enfermeria" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-boton-acento hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Agenda</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Calendario de turno</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">Fechas y horarios proximos</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-boton-acento/10 text-boton-acento">
                        <i class="ph-bold ph-calendar text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.admision.valoracion-enfermeria') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-estado-info hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Admision</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Valoraciones iniciales</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['valoraciones_pendientes'] ?? 0 }} pendientes</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-estado-infoBg/50 text-estado-info">
                        <i class="ph-bold ph-clipboard-text text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.seguimiento-diario.index') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-estado-exito hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Control diario</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Seguimiento diario</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['seguimientos_hoy'] ?? 0 }} registros hoy</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-estado-exitoBg/50 text-estado-exito">
                        <i class="ph-bold ph-clipboard-text text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.enfermeria.pacientes') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-boton-acento hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Pacientes</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Mis pacientes</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['pacientes'] ?? 0 }} asignados</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-fondo-card text-boton-acento border border-borde">
                        <i class="ph-bold ph-users text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.enfermeria.tareas') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-estado-peligro hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Plan de cuidado</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Tareas del turno</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['tareas_pendientes'] ?? 0 }} pendientes</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-estado-peligroBg/50 text-estado-peligro">
                        <i class="ph-bold ph-list-checks text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.enfermeria.alertas') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-estado-peligro hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Seguridad</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Alertas clinicas</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['alertas_activas'] ?? 0 }} activas</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-estado-peligroBg/50 text-estado-peligro">
                        <i class="ph-bold ph-bell-ringing text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.enfermeria.pase-turno') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-meta hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Cierre</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Pase de turno</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['pase_pendiente'] ?? 'NO INICIADO' }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-fondo-card text-meta border border-borde">
                        <i class="ph-bold ph-handshake text-xl"></i>
                    </div>
                </div>
            </a>

            <a wire:navigate href="{{ route('admin.enfermeria.reportes') }}" class="group rounded-2xl border border-borde bg-fondo-card/40 p-4 transition-all hover:-translate-y-1 hover:border-estado-info hover:shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Historico</p>
                        <h4 class="mt-2 text-sm font-black text-titulo">Reportes del turno</h4>
                        <p class="mt-1 text-[11px] font-semibold text-apoyo">{{ $stats['valoraciones_realizadas_hoy'] ?? 0 }} valoraciones hoy</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-[16px] bg-estado-infoBg/50 text-estado-info">
                        <i class="ph-bold ph-chart-bar text-xl"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @if(count($calendarioHorarios ?? []) > 0)
        <div id="calendario-enfermeria" class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm" data-aos="fade-up" data-aos-delay="150">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-parrafo">Calendario de próximas fechas</h3>
                    <p class="mt-1 text-xs font-semibold text-apoyo">Vista de los próximos 14 días según tu programación real.</p>
                </div>
                <span class="inline-flex rounded-full border border-borde bg-fondo-card px-3 py-1 text-[10px] font-black uppercase tracking-widest text-parrafo">
                    {{ count($calendarioHorarios) }} días
                </span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($calendarioHorarios as $dia)
                    <div class="rounded-2xl border {{ $dia['es_hoy'] ? 'border-boton-acento bg-boton-acento/5' : 'border-borde bg-fondo-card/40' }} p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">{{ $dia['dia_semana'] }}</p>
                                <h4 class="mt-1 text-sm font-black text-titulo">{{ $dia['fecha_texto'] }}</h4>
                            </div>
                            @if($dia['es_hoy'])
                                <span class="inline-flex rounded-full bg-boton-acento px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-white">
                                    Hoy
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 space-y-2">
                            @forelse($dia['turnos'] as $turno)
                                <div class="rounded-xl border border-borde bg-fondo-panel px-3 py-2">
                                    <p class="text-xs font-black text-titulo">{{ $turno['turno'] }}</p>
                                    <p class="mt-0.5 text-[10px] font-bold uppercase tracking-widest text-apoyo">
                                        {{ $turno['hora_inicio'] }} - {{ $turno['hora_fin'] }}
                                    </p>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-borde px-3 py-4 text-center">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Sin turno asignado</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- LÃ­nea de tiempo (Workflow del turno) --}}    {{-- LÃ­nea de tiempo (Workflow del turno) --}}
    <div class="relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel/60 p-6 shadow-panel backdrop-blur-xl" data-aos="fade-up" data-aos-delay="100">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-boton-acento/5 blur-3xl"></div>
        <h3 class="mb-6 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-parrafo">
            <i class="ph-bold ph-activity text-boton-acento text-lg"></i>
            Flujo Operativo del Turno
        </h3>

        <div class="relative flex items-center justify-between px-2 sm:px-8">
            <div class="absolute left-10 right-10 top-1/2 h-1 -translate-y-1/2 rounded-full bg-borde-suave/50"></div>

            <div class="group relative z-10 flex flex-col items-center gap-3 cursor-pointer">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento text-white shadow-glow transition-transform group-hover:scale-110 group-hover:rotate-6">
                    <i class="ph-bold ph-check-circle text-xl"></i>
                </div>
                <span class="text-[10px] font-black text-titulo uppercase tracking-wider transition-colors group-hover:text-boton-acento">Turno Recibido</span>
            </div>

            <div class="group relative z-10 flex flex-col items-center gap-3 cursor-pointer">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $stats['tareas_pendientes'] == 0 ? 'bg-boton-acento text-white shadow-glow' : 'bg-estado-peligroBg text-estado-peligro ring-2 ring-estado-peligro animate-pulse' }} transition-transform group-hover:scale-110">
                    <i class="ph-bold ph-list-checks text-xl"></i>
                </div>
                <span class="text-[10px] font-black text-titulo uppercase tracking-wider transition-colors {{ $stats['tareas_pendientes'] > 0 ? 'text-estado-peligro' : 'group-hover:text-boton-acento' }}">Tareas</span>
            </div>

            <div class="group relative z-10 flex flex-col items-center gap-3 cursor-pointer">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-card text-meta border border-borde transition-transform group-hover:scale-110">
                    <i class="ph-bold ph-heartbeat text-xl"></i>
                </div>
                <span class="text-[10px] font-black text-titulo uppercase tracking-wider transition-colors group-hover:text-meta">Seguimiento</span>
            </div>

            <div class="group relative z-10 flex flex-col items-center gap-3 cursor-pointer">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $stats['alertas_activas'] == 0 ? 'bg-boton-acento text-white shadow-glow' : 'bg-estado-peligro text-white shadow-[0_0_15px_rgba(239,68,68,0.5)] animate-bounce' }} transition-transform group-hover:scale-110">
                    <i class="ph-bold ph-bell-ringing text-xl"></i>
                </div>
                <span class="text-[10px] font-black text-titulo uppercase tracking-wider transition-colors {{ $stats['alertas_activas'] > 0 ? 'text-estado-peligro' : 'group-hover:text-boton-acento' }}">Alertas</span>
            </div>

            <div class="group relative z-10 flex flex-col items-center gap-3 cursor-pointer">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $stats['pase_pendiente'] === 'CERRADO' ? 'bg-boton-acento text-white shadow-glow' : 'bg-fondo-card text-apoyo border border-borde-suave' }} transition-transform group-hover:scale-110 group-hover:-rotate-6">
                    <i class="ph-bold ph-handshake text-xl"></i>
                </div>
                <span class="text-[10px] font-black text-titulo uppercase tracking-wider transition-colors group-hover:text-titulo">Pase Turno</span>
            </div>
        </div>
    </div>

    {{-- Indicadores Kpis --}}
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7" data-aos="fade-up" data-aos-delay="200">
        <!-- Pacientes asignados -->
        <a href="{{ route('admin.enfermeria.pacientes') }}" class="group relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-boton-acento hover:shadow-card">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-boton-acento/5 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-fondo-card text-parrafo transition-colors group-hover:bg-boton-acento/10 group-hover:text-boton-acento">
                    <i class="ph-bold ph-users text-2xl"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-titulo">{{ $stats['pacientes'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo group-hover:text-boton-acento">Pacientes<br>Asignados</p>
        </a>

        <!-- Tareas pendientes -->
        <a href="{{ route('admin.enfermeria.tareas') }}" class="group relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-estado-peligro hover:shadow-card">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-estado-peligro/5 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-peligroBg/50 text-estado-peligro transition-colors group-hover:bg-estado-peligroBg">
                    <i class="ph-bold ph-list-checks text-2xl"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-estado-peligro">{{ $stats['tareas_pendientes'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo group-hover:text-estado-peligro">Tareas<br>Pendientes</p>
        </a>

        <!-- MedicaciÃ³n -->
        <div class="relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-estado-exito hover:shadow-card cursor-pointer">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-estado-exito/5 transition-transform hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-exitoBg/50 text-estado-exito">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-estado-exito">{{ $stats['medicacion_pendiente'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo">MedicaciÃ³n<br>Pendiente</p>
        </div>

        <!-- Signos -->
        <div class="relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-estado-info hover:shadow-card cursor-pointer">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-estado-info/5 transition-transform hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-infoBg/50 text-estado-info">
                    <i class="ph-bold ph-thermometer text-2xl"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-estado-info">{{ $stats['signos_pendientes'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo">Signos<br>Pendientes</p>
        </div>

        <!-- Alertas activas -->
        <a href="{{ route('admin.enfermeria.alertas') }}" class="group relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-estado-peligro hover:shadow-card">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-estado-peligro/5 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-peligroBg/50 text-estado-peligro transition-colors group-hover:bg-estado-peligroBg">
                    <i class="ph-bold ph-bell-ringing text-2xl"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-estado-peligro">{{ $stats['alertas_activas'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo group-hover:text-estado-peligro">Alertas<br>Activas</p>
        </a>

        <!-- Seguimiento diario -->
        <a href="{{ route('admin.seguimiento-diario.index') }}" class="group relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-estado-info hover:shadow-card">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-estado-info/5 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-infoBg/50 text-estado-info transition-colors group-hover:bg-estado-infoBg">
                    <i class="ph-bold ph-clipboard-text text-2xl"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-estado-info">{{ $stats['seguimientos_hoy'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo group-hover:text-estado-info">Seguimientos<br>Hoy</p>
        </a>

        <!-- Pase -->
        <a href="{{ route('admin.enfermeria.pase-turno') }}" class="group relative overflow-hidden rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-meta hover:shadow-card">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-meta/5 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-fondo-card text-meta border border-borde-suave transition-colors group-hover:bg-meta/10 group-hover:border-transparent">
                    <i class="ph-bold ph-handshake text-2xl"></i>
                </div>
            </div>
            <p class="text-sm font-black text-meta mt-2 truncate">{{ $stats['pase_pendiente'] }}</p>
            <p class="mt-1 text-[11px] font-black uppercase tracking-widest text-apoyo group-hover:text-meta">Estado del<br>Pase</p>
        </a>
    </div>

    <div class="grid gap-5 sm:grid-cols-2" data-aos="fade-up" data-aos-delay="230">
        <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Valoraciones pendientes</p>
                    <p class="mt-2 text-3xl font-black text-estado-info">{{ $stats['valoraciones_pendientes'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-infoBg/50 text-estado-info">
                    <i class="ph-bold ph-clipboard-text text-2xl"></i>
                </div>
            </div>
            <p class="mt-3 text-xs font-semibold text-apoyo">Casos aprobados por preadmisiÃ³n que esperan la revisiÃ³n inicial del enfermero asignado.</p>
        </div>

        <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Valoraciones realizadas hoy</p>
                    <p class="mt-2 text-3xl font-black text-estado-exito">{{ $stats['valoraciones_realizadas_hoy'] ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-estado-exitoBg/50 text-estado-exito">
                    <i class="ph-bold ph-check-circle text-2xl"></i>
                </div>
            </div>
            <p class="mt-3 text-xs font-semibold text-apoyo">Valoraciones de enfermerÃ­a ya guardadas y derivadas a valoraciÃ³n mÃ©dica.</p>
        </div>
    </div>

    @if(count($valoracionesPendientes) > 0)
        <!-- Valoraciones Iniciales Pendientes (Preadmisiones) -->
        <div class="rounded-3xl border border-estado-infoBorde bg-gradient-to-r from-estado-infoBg/40 to-fondo-panel p-6 shadow-sm mt-4 relative overflow-hidden" data-aos="fade-up" data-aos-delay="300">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-estado-info/10 rounded-full blur-2xl -z-10 pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-white text-estado-info shadow-sm ring-1 ring-estado-info/20">
                        <i class="ph-bold ph-clipboard-text text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-widest text-estado-info">Valoración Inicial Pendiente</h3>
                        <p class="text-xs font-semibold text-estado-info/80 mt-1">Pacientes nuevos derivados de preadmisión esperando la revisión del enfermero asignado.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-estado-info text-white font-bold text-sm shadow-glow">
                        {{ count($valoracionesPendientes) }}
                    </span>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-estado-infoBorde/50 bg-white/40 backdrop-blur-sm">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-estado-info/5 text-[10px] font-black uppercase tracking-widest text-estado-info border-b border-estado-infoBorde/50">
                            <th class="px-5 py-4">Caso / Paciente</th>
                            <th class="px-5 py-4 hidden md:table-cell">Edad / Origen</th>
                            <th class="px-5 py-4 hidden lg:table-cell">Motivo</th>
                            <th class="px-5 py-4 hidden sm:table-cell">Documentación</th>
                            <th class="px-5 py-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-estado-infoBorde/30">
                        @foreach($valoracionesPendientes as $paciente)
                            <tr class="group transition-colors hover:bg-white">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] bg-estado-info text-white font-bold shadow-sm ring-2 ring-white">
                                            {{ substr($paciente->nombre_completo, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-titulo block truncate">{{ $paciente->nombre_completo }}</span>
                                            <span class="text-[11px] font-bold text-estado-info/80">PRE: {{ $paciente->cod_pre ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    <span class="block font-bold text-titulo text-xs">{{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años</span>
                                    <span class="text-[10px] font-bold text-apoyo uppercase tracking-wider">{{ str_replace('_', ' ', $paciente->procedencia_ingreso ?? 'NO ESPECIFICADA') }}</span>
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    <span class="inline-flex rounded-lg bg-fondo-card px-2.5 py-1 text-[10px] font-bold text-parrafo border border-borde-suave">
                                        {{ str_replace('_', ' ', $paciente->motivo_ingreso ?? 'EVALUACIÓN') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 hidden sm:table-cell">
                                    @php
                                        $docsObligatorios = $paciente->documentos->whereIn('tipo_documento', ['CI', 'CI_ADULTO', 'FICHA_PREADMISION', 'COMPROMISO_INGRESO'])->count();
                                    @endphp
                                    <div class="flex items-center gap-1.5 {{ $docsObligatorios > 0 ? 'text-estado-exito' : 'text-estado-peligro' }}">
                                        <i class="ph-fill {{ $docsObligatorios > 0 ? 'ph-check-circle' : 'ph-warning-circle' }} text-lg"></i>
                                        <span class="font-bold text-[10px] uppercase tracking-widest">{{ $docsObligatorios > 0 ? 'Validada' : 'Faltan Req.' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="iniciarValoracion('{{ $paciente->cod_pre }}')" class="inline-flex h-9 items-center justify-center rounded-xl bg-estado-info px-4 text-xs font-bold text-white shadow-glow transition hover:bg-estado-infoHover hover:scale-105 active:scale-95" title="Iniciar valoración de enfermería">
                                            <i class="ph-bold ph-stethoscope mr-1.5"></i> Iniciar
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

    @if(count($valoracionesRealizadasHoy ?? []) > 0)
        <div class="rounded-3xl border border-estado-exitoBorde bg-gradient-to-r from-estado-exitoBg/30 to-fondo-panel p-6 shadow-sm mt-4 relative overflow-hidden" data-aos="fade-up" data-aos-delay="350">
            <div class="absolute -left-10 -top-10 w-40 h-40 bg-estado-exito/10 rounded-full blur-2xl -z-10 pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-white text-estado-exito shadow-sm ring-1 ring-estado-exito/20">
                        <i class="ph-bold ph-checks text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-widest text-estado-exito">Valoraciones realizadas hoy</h3>
                        <p class="text-xs font-semibold text-estado-exito/80 mt-1">Registros ya completados por el enfermero activo en esta jornada.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-estado-exito text-white font-bold text-sm shadow-glow">
                        {{ count($valoracionesRealizadasHoy) }}
                    </span>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-estado-exitoBorde/50 bg-white/40 backdrop-blur-sm">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-estado-exito/5 text-[10px] font-black uppercase tracking-widest text-estado-exito border-b border-estado-exitoBorde/50">
                            <th class="px-5 py-4">Paciente</th>
                            <th class="px-5 py-4 hidden md:table-cell">Fecha / Hora</th>
                            <th class="px-5 py-4 hidden lg:table-cell">Estado General</th>
                            <th class="px-5 py-4 hidden sm:table-cell">Derivacion</th>
                            <th class="px-5 py-4 text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-estado-exitoBorde/30">
                        @foreach($valoracionesRealizadasHoy as $valoracion)
                            <tr class="group transition-colors hover:bg-white">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] bg-estado-exito text-white font-bold shadow-sm ring-2 ring-white">
                                            {{ substr($valoracion->adultoMayor->nombre_completo ?? $valoracion->adultoMayor->nombres ?? 'A', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-titulo block truncate">{{ $valoracion->adultoMayor->nombre_completo ?? $valoracion->adultoMayor->nombres ?? 'Sin nombre' }}</span>
                                            <span class="text-[11px] font-bold text-estado-exito/80">VAL: {{ $valoracion->cod_val_enf }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    <span class="block font-bold text-titulo text-xs">{{ optional($valoracion->fecha_valoracion)->translatedFormat('d/m/Y') }}</span>
                                    <span class="text-[10px] font-bold text-apoyo uppercase tracking-wider">{{ $valoracion->hora_valoracion ? \Carbon\Carbon::parse($valoracion->hora_valoracion)->format('H:i') : 'SIN HORA' }}</span>
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    <span class="inline-flex rounded-lg bg-fondo-card px-2.5 py-1 text-[10px] font-bold text-parrafo border border-borde-suave">
                                        {{ str_replace('_', ' ', $valoracion->estado_general ?? 'EVALUADO') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 hidden sm:table-cell">
                                    <div class="flex items-center gap-1.5 text-estado-exito">
                                        <i class="ph-fill ph-check-circle text-lg"></i>
                                        <span class="font-bold text-[10px] uppercase tracking-widest">{{ $valoracion->estado }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', $valoracion->cod_am) }}" class="inline-flex h-9 items-center justify-center rounded-xl bg-estado-exito px-4 text-xs font-bold text-white shadow-glow transition hover:bg-estado-exitoHover hover:scale-105 active:scale-95">
                                        <i class="ph-bold ph-folder-open mr-1.5"></i> Ficha
                                    </a>
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
        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-borde bg-fondo-panel/50 py-20 text-center" data-aos="fade-up" data-aos-delay="300">
            <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-fondo-card text-meta shadow-inner border border-borde">
                <i class="ph-bold ph-bed text-4xl"></i>
            </div>
            <h3 class="text-xl font-black text-titulo">No hay pacientes asignados</h3>
            <p class="mt-2 max-w-sm text-sm font-semibold text-apoyo">Actualmente no tienes pacientes asignados a este turno, o no hay un turno activo. Consulta con el administrador.</p>
        </div>
    @else
        <!-- GrÃ¡ficas y Tabla -->
        <div class="grid gap-6 lg:grid-cols-3" data-aos="fade-up" data-aos-delay="400">

            <div class="lg:col-span-2 space-y-6">
                <!-- GrÃ¡ficas Principales -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                        <h3 class="mb-5 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-parrafo">
                            <i class="ph-bold ph-chart-pie-slice text-boton-acento text-lg"></i>
                            Estado de Tareas
                        </h3>
                        <div class="relative h-56 w-full flex items-center justify-center">
                            <canvas id="chartTareas"></canvas>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                        <h3 class="mb-5 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-parrafo">
                            <i class="ph-bold ph-trend-up text-boton-acento text-lg"></i>
                            Actividad por Hora
                        </h3>
                        <div class="relative h-56 w-full">
                            <canvas id="chartActividad"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabla Principal -->
                <div class="rounded-3xl border border-borde bg-fondo-panel shadow-panel overflow-hidden">
                    <div class="flex items-center justify-between border-b border-borde p-6 bg-fondo-card/30">
                        <h3 class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-titulo">
                            <i class="ph-bold ph-list-numbers text-boton-acento text-lg"></i>
                            PrÃ³ximas Tareas (Top 10)
                        </h3>
                        <a href="{{ route('admin.enfermeria.tareas') }}" class="text-[10px] font-black uppercase tracking-widest text-boton-acento transition-colors hover:text-boton-acentoHover flex items-center gap-1">
                            Ver todas <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-fondo-hover text-[10px] font-black uppercase tracking-widest text-apoyo border-b border-borde">
                                    <th class="px-5 py-4">Hora</th>
                                    <th class="px-5 py-4">Paciente</th>
                                    <th class="px-5 py-4 hidden sm:table-cell">Tarea</th>
                                    <th class="px-5 py-4 hidden md:table-cell">Prioridad</th>
                                    <th class="px-5 py-4">Estado</th>
                                    <th class="px-5 py-4 text-right">AcciÃ³n</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde-suave">
                                @forelse($tareasTabla as $tarea)
                                    <tr class="group transition-colors hover:bg-fondo-card/50">
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-lg bg-fondo-card px-2.5 py-1 text-xs font-bold text-titulo border border-borde shadow-sm group-hover:border-boton-acento/30 transition-colors">
                                                {{ \Carbon\Carbon::parse($tarea->hora_programada)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] bg-boton-acento/10 text-[11px] font-black text-boton-acento">
                                                    {{ substr($tarea->adultoMayor->nombres ?? 'A', 0, 1) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="font-bold text-titulo block truncate">{{ $tarea->adultoMayor->nombres ?? 'Desconocido' }}</span>
                                                    <span class="text-[10px] font-bold text-meta truncate">Hab. {{ $tarea->adultoMayor->habitacion->numero ?? $tarea->adultoMayor->cod_habitacion ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 hidden sm:table-cell text-xs font-semibold text-parrafo">
                                            {{ Str::limit($tarea->titulo, 30) }}
                                        </td>
                                        <td class="px-5 py-4 hidden md:table-cell">
                                            @php
                                                $colorPrioridad = match($tarea->prioridad) {
                                                    'ALTA' => 'bg-estado-peligroBg text-estado-peligro border-estado-peligroBorde',
                                                    'MEDIA' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
                                                    default => 'bg-fondo-card text-apoyo border-borde',
                                                };
                                            @endphp
                                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-widest {{ $colorPrioridad }}">
                                                {{ $tarea->prioridad }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @php
                                                $colorEstado = match($tarea->estado) {
                                                    'PENDIENTE' => 'text-estado-advertencia',
                                                    'REALIZADA' => 'text-estado-exito',
                                                    'OMITIDA' => 'text-estado-peligro',
                                                    default => 'text-meta',
                                                };
                                            @endphp
                                            <div class="flex items-center gap-1.5 {{ $colorEstado }}">
                                                <i class="ph-fill ph-circle text-[8px]"></i>
                                                <span class="text-[10px] font-black uppercase tracking-widest">{{ $tarea->estado }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ route('admin.enfermeria.tareas') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card text-apoyo border border-borde shadow-sm transition hover:bg-boton-acento hover:text-white hover:border-boton-acento hover:scale-110 active:scale-95" title="Atender Tarea">
                                                <i class="ph-bold ph-arrow-right text-lg"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-xs font-bold text-apoyo">
                                            <div class="flex flex-col items-center gap-3">
                                                <i class="ph-bold ph-check-circle text-4xl text-estado-exito/50"></i>
                                                <span>No hay tareas programadas pendientes.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna lateral: Mini grÃ¡ficas -->
            <div class="space-y-6">
                <!-- MedicaciÃ³n -->
                <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel relative overflow-hidden">
                    <h3 class="mb-5 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-parrafo">
                        <i class="ph-bold ph-pill text-estado-exito text-lg"></i>
                        Estatus MedicaciÃ³n
                    </h3>
                    <div class="relative h-44 w-full flex items-center justify-center">
                        <canvas id="chartMedicacion"></canvas>
                    </div>
                </div>

                <!-- Nivel de supervisiÃ³n -->
                <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                    <h3 class="mb-5 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-parrafo">
                        <i class="ph-bold ph-eye text-meta text-lg"></i>
                        SupervisiÃ³n Asignada
                    </h3>
                    <div class="relative h-44 w-full">
                        <canvas id="chartSupervision"></canvas>
                    </div>
                </div>

                <!-- Alertas -->
                <div class="rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-estado-peligro/5 rounded-bl-[100px] pointer-events-none"></div>
                    <h3 class="mb-5 flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-parrafo">
                        <i class="ph-bold ph-bell-ringing text-estado-peligro text-lg"></i>
                        Alertas ClÃ­nicas
                    </h3>
                    <div class="relative h-40 w-full">
                        <canvas id="chartAlertas"></canvas>
                    </div>
                    <div class="mt-5 flex w-full">
                        <a href="{{ route('admin.enfermeria.alertas') }}" class="rm-btn-secondary w-full text-center py-2.5 rounded-xl border-borde text-xs uppercase tracking-widest group">
                            Gestionar <i class="ph-bold ph-arrow-right ml-1 transition-transform group-hover:translate-x-1"></i>
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

            // Extractor de variables CSS dinÃ¡micas para soporte Dark Mode
            const getCssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || '#000';

            // Colores TemÃ¡ticos del Sistema RememberMind
            const colors = {
                acento: getCssVar('--color-boton-acento'),
                acentoHover: getCssVar('--color-boton-acentoHover'),
                exito: getCssVar('--color-estado-exito'),
                peligro: getCssVar('--color-estado-peligro'),
                advertencia: getCssVar('--color-estado-advertencia'),
                info: getCssVar('--color-estado-info'),
                meta: getCssVar('--color-meta'),
                apoyo: getCssVar('--color-apoyo'),
                borde: getCssVar('--color-borde'),
                fondoCard: getCssVar('--color-fondo-card'),
                grid: getCssVar('--color-borde-suave')
            };

            Chart.defaults.font.family = "'Outfit', sans-serif";
            Chart.defaults.color = colors.apoyo;
            const tension = 0.4;

            const createDoughnutPlugin = (text) => ({
                id: 'centerText',
                beforeDraw: function(chart) {
                    var width = chart.width, height = chart.height, ctx = chart.ctx;
                    ctx.restore();
                    var fontSize = (height / 120).toFixed(2);
                    ctx.font = "900 " + fontSize + "em Outfit";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = colors.acento;
                    var textX = Math.round((width - ctx.measureText(text).width) / 2),
                        textY = height / 2;
                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            });

            // 1. Dona: Tareas
            const ctxTareas = document.getElementById('chartTareas');
            if (ctxTareas) {
                const totalTareas = chartData.tareas.pendientes + chartData.tareas.realizadas + chartData.tareas.omitidas;
                new Chart(ctxTareas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Realizadas', 'Omitidas'],
                        datasets: [{
                            data: [chartData.tareas.pendientes, chartData.tareas.realizadas, chartData.tareas.omitidas],
                            backgroundColor: [colors.advertencia, colors.exito, colors.peligro],
                            borderWidth: 4,
                            borderColor: getCssVar('--color-fondo-panel'),
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 11, weight: 'bold' } } }
                        }
                    },
                    plugins: totalTareas > 0 ? [createDoughnutPlugin(totalTareas)] : []
                });
            }

            // 2. LÃ­nea: Actividad
            const ctxActividad = document.getElementById('chartActividad');
            if (ctxActividad) {
                let gradient = ctxActividad.getContext('2d').createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, colors.acento + '80'); // 50% opacity
                gradient.addColorStop(1, colors.acento + '00'); // 0% opacity

                new Chart(ctxActividad, {
                    type: 'line',
                    data: {
                        labels: Object.keys(chartData.actividad),
                        datasets: [{
                            label: 'Intervenciones',
                            data: Object.values(chartData.actividad),
                            borderColor: colors.acento,
                            backgroundColor: gradient,
                            borderWidth: 3,
                            fill: true,
                            tension: tension,
                            pointBackgroundColor: getCssVar('--color-fondo-panel'),
                            pointBorderColor: colors.acento,
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: colors.grid, drawBorder: false }, ticks: { stepSize: 1, padding: 10 } },
                            x: { grid: { display: false }, ticks: { padding: 10, font: { weight: 'bold' } } }
                        }
                    }
                });
            }

            // 3. Barras: SupervisiÃ³n
            const ctxSupervision = document.getElementById('chartSupervision');
            if (ctxSupervision) {
                new Chart(ctxSupervision, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(chartData.supervision),
                        datasets: [{
                            data: Object.values(chartData.supervision),
                            backgroundColor: colors.meta,
                            hoverBackgroundColor: colors.acento,
                            borderRadius: 6,
                            barThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                        }
                    }
                });
            }

            // 4. Dona: Medicacion
            const ctxMed = document.getElementById('chartMedicacion');
            if (ctxMed) {
                const totalMed = chartData.medicacion.administrada + chartData.medicacion.pendiente + chartData.medicacion.omitida;
                new Chart(ctxMed, {
                    type: 'doughnut',
                    data: {
                        labels: ['Admin', 'Pendiente', 'Omitida'],
                        datasets: [{
                            data: [chartData.medicacion.administrada, chartData.medicacion.pendiente, chartData.medicacion.omitida],
                            backgroundColor: [colors.exito, colors.advertencia, colors.peligro],
                            borderWidth: 4,
                            borderColor: getCssVar('--color-fondo-panel'),
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 10, weight: 'bold' } } } }
                    },
                    plugins: totalMed > 0 ? [createDoughnutPlugin(totalMed)] : []
                });
            }

            // 5. Barras: Alertas
            const ctxAlertas = document.getElementById('chartAlertas');
            if (ctxAlertas) {
                new Chart(ctxAlertas, {
                    type: 'bar',
                    data: {
                        labels: ['Leve', 'Moderada', 'CrÃ­tica'],
                        datasets: [{
                            data: [chartData.alertas.leve, chartData.alertas.moderada, chartData.alertas.critica],
                            backgroundColor: [colors.info, colors.advertencia, colors.peligro],
                            borderRadius: 6,
                            barThickness: 24
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                        }
                    }
                });
            }
        });
    </script>

    @livewire('admin.enfermeria.valoracion-inicial-modal')
</div>
