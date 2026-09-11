<div class="space-y-6">
    <!-- CABECERA INSTITUCIONAL -->
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 rounded-md bg-boton-acento/10 px-2 py-0.5 text-[11px] font-bold text-boton-acento uppercase tracking-wider">
                    <i class="ph-bold ph-shield-check text-xs"></i> Enfermería & Cuidados
                </span>
                <span class="text-xs text-apoyo">·</span>
                <span class="text-xs font-semibold text-apoyo">{{ now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-titulo">
                {{ $esSuperAdmin ? 'Supervisión de residentes' : 'Mis pacientes' }}
            </h1>
            <p class="text-xs font-semibold text-apoyo">
                {{ $esSuperAdmin ? 'Visibilidad global de residentes, asignaciones y estado asistencial' : 'Residentes asignados a tu turno actual' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn-secondary h-9 px-3 text-xs font-bold" title="Abrir agenda priorizada">
                <i class="ph-bold ph-calendar-check text-sm"></i><span class="hidden sm:inline">Agenda</span>
            </a>
            <a href="{{ route('admin.enfermeria.registros') }}" class="rm-btn-secondary h-9 px-3 text-xs font-bold" title="Registrar cuidados">
                <i class="ph-bold ph-notebook text-sm"></i><span class="hidden sm:inline">Registros</span>
            </a>
            <button wire:click="$refresh" class="rm-btn-secondary h-9 px-3 text-xs font-bold" title="Actualizar datos">
                <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                <span class="hidden sm:inline">Actualizar</span>
            </button>
            <a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary h-9 px-3 text-xs font-bold" title="Ir a cola operativa">
                <i class="ph-bold ph-queue text-sm"></i>
                <span class="hidden sm:inline">Cola Operativa</span>
            </a>
        </div>
    </div>

    <!-- BARRA DE CONTROL: BÚSQUEDA, FILTRO DE ESTADO, SELECTOR LISTA / TARJETAS -->
    <div class="flex flex-col gap-3 rounded-2xl border border-borde bg-fondo-panel p-3.5 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <!-- Búsqueda -->
            <div class="relative w-full lg:w-72">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo text-sm"></i>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text"
                    class="rm-input w-full pl-9 py-1.5 text-xs" 
                    placeholder="Buscar residente, habitación o cama...">
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-apoyo hover:text-titulo text-xs" title="Limpiar búsqueda">
                        <i class="ph-bold ph-x"></i>
                    </button>
                @endif
            </div>

            <!-- Filtro por Estado (Todos / Estable / Vigilancia / Requiere atención) -->
            <div class="flex flex-wrap items-center gap-1.5">
                <!-- Todos -->
                <button 
                    type="button"
                    wire:click="$set('filtroEstado', 'TODOS')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'TODOS' ? 'bg-fondo-card border-borde text-titulo shadow-sm ring-1 ring-boton-acento/30' : 'border-transparent text-apoyo hover:text-titulo hover:bg-fondo-card/50' }}">
                    <span>Todos</span>
                    <span class="rounded-md bg-fondo-panel px-1.5 py-0.5 text-[10px] font-black text-parrafo border border-borde">{{ $stats['total'] }}</span>
                </button>

                <!-- Estable (Verde) -->
                <button 
                    type="button"
                    wire:click="$set('filtroEstado', 'ESTABLE')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'ESTABLE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 shadow-sm' : 'border-transparent text-apoyo hover:text-emerald-700 hover:bg-emerald-50/50' }}">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Estable</span>
                    <span class="rounded-md bg-emerald-100/80 dark:bg-emerald-900/60 px-1.5 py-0.5 text-[10px] font-black text-emerald-700 dark:text-emerald-300">{{ $stats['estable'] }}</span>
                </button>

                <!-- Vigilancia (Amarillo) -->
                <button 
                    type="button"
                    wire:click="$set('filtroEstado', 'VIGILANCIA')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'VIGILANCIA' ? 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 shadow-sm' : 'border-transparent text-apoyo hover:text-amber-700 hover:bg-amber-50/50' }}">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    <span>Vigilancia</span>
                    <span class="rounded-md bg-amber-100/80 dark:bg-amber-900/60 px-1.5 py-0.5 text-[10px] font-black text-amber-700 dark:text-amber-300">{{ $stats['vigilancia'] }}</span>
                </button>

                <!-- Requiere atención (Rojo) -->
                <button 
                    type="button"
                    wire:click="$set('filtroEstado', 'REQUIERE_ATENCION')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'REQUIERE_ATENCION' ? 'bg-red-50 text-red-800 border-red-300 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800 shadow-sm' : 'border-transparent text-apoyo hover:text-red-700 hover:bg-red-50/50' }}">
                    <span class="h-2 w-2 rounded-full bg-red-500 {{ $stats['requiere_atencion'] > 0 ? 'animate-pulse' : '' }}"></span>
                    <span>Requiere atención</span>
                    <span class="rounded-md bg-red-100/80 dark:bg-red-900/60 px-1.5 py-0.5 text-[10px] font-black text-red-700 dark:text-red-300">{{ $stats['requiere_atencion'] }}</span>
                </button>
            </div>

            <!-- Selector Lista / Tarjetas y Filtros de Turno (Superadmin) -->
            <div class="flex items-center justify-between gap-2.5 pt-2 border-t border-borde lg:border-t-0 lg:pt-0">
                @if($esSuperAdmin)
                    <div class="flex items-center gap-1.5">
                        <select wire:model.live="filtroTurno" class="rm-select text-[11px] py-1">
                            <option value="">Todos los Turnos</option>
                            @foreach($turnos as $t)
                                <option value="{{ $t->cod_turno }}">{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="inline-flex rounded-xl border border-borde bg-fondo-card/60 p-0.5">
                    <button 
                        type="button"
                        wire:click="$set('vistaModo', 'tabla')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $vistaModo === 'tabla' ? 'bg-fondo-panel text-titulo shadow-sm border border-borde' : 'text-apoyo hover:text-titulo' }}"
                        title="Vista en tabla (Lista)">
                        <i class="ph-bold ph-list text-sm"></i>
                        <span>Lista</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="$set('vistaModo', 'tarjetas')"
                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $vistaModo === 'tarjetas' ? 'bg-fondo-panel text-titulo shadow-sm border border-borde' : 'text-apoyo hover:text-titulo' }}"
                        title="Vista en tarjetas compactas">
                        <i class="ph-bold ph-squares-four text-sm"></i>
                        <span>Tarjetas</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL: LISTA O TARJETAS -->
    @if($pacientes->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-borde bg-fondo-panel py-16 text-center shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-card text-apoyo mb-3">
                <i class="ph-bold ph-users text-2xl"></i>
            </div>
            <h3 class="text-base font-bold text-titulo">{{ $esSuperAdmin ? 'No se encontraron residentes' : 'No se encontraron residentes asignados' }}</h3>
            <p class="text-xs text-apoyo max-w-sm mt-1">
                @if($filtroEstado !== 'TODOS' || $search)
                    No hay pacientes que coincidan con los filtros seleccionados. Intenta restablecer el filtro o término de búsqueda.
                @else
                    {{ $esSuperAdmin ? 'No existen residentes que coincidan con los filtros seleccionados.' : 'No tienes pacientes asignados en el turno actual o tu guardia no está activa.' }}
                @endif
            </p>
            @if($filtroEstado !== 'TODOS' || $search)
                <button wire:click="$set('filtroEstado', 'TODOS'); $set('search', '')" class="rm-btn-secondary mt-4 px-3 py-1.5 text-xs font-bold">
                    <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i> Ver todos
                </button>
            @endif
        </div>
    @elseif($vistaModo === 'tabla')
        <!-- VISTA LISTA / TABLA COMPACTA -->
        <div class="overflow-hidden rounded-2xl border border-borde bg-fondo-panel shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-borde bg-fondo-card/50 text-[10px] font-black uppercase tracking-wider text-apoyo">
                            <th class="py-3 px-4">Residente</th>
                            <th class="py-3 px-3">Ubicación</th>
                            <th class="py-3 px-3">Cuidado</th>
                            <th class="py-3 px-3">Últimos Signos</th>
                            <th class="py-3 px-3">Medicación</th>
                            <th class="py-3 px-3">Tareas</th>
                            <th class="py-3 px-3">Seguimiento</th>
                            <th class="py-3 px-3">Alertas</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde">
                        @foreach($pacientes as $paciente)
                            <tr class="hover:bg-fondo-card/40 transition">
                                <!-- Residente (Avatar, Nombre, Edad, Estado) -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            @if($paciente->foto)
                                                <img src="{{ asset('storage/' . $paciente->foto) }}" alt="{{ $paciente->nombres }}" class="h-10 w-10 rounded-full object-cover border border-borde">
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-fondo-card border border-borde font-black text-xs text-titulo">
                                                    {{ mb_substr($paciente->nombres, 0, 1) }}{{ mb_substr($paciente->ap_paterno, 0, 1) }}
                                                </div>
                                            @endif
                                            <!-- Indicador semántico de estado -->
                                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-fondo-panel {{ $paciente->estado_color === 'red' ? 'bg-red-500 animate-pulse' : ($paciente->estado_color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}" title="Estado: {{ $paciente->estado_label }}"></span>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" class="font-black text-titulo hover:text-boton-acento transition block text-xs">
                                                {{ $paciente->nombres }} {{ $paciente->ap_paterno }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[11px] text-apoyo font-semibold">{{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años</span>
                                                <span class="text-apoyo text-[10px]">·</span>
                                                <span class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.2 text-[9px] font-black {{ $paciente->estado_color === 'red' ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300' : ($paciente->estado_color === 'amber' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300') }}">
                                                    {{ $paciente->estado_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Ubicación (Habitación / Cama) -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-bold text-titulo">
                                        Hab. {{ $paciente->habitacion->codigo ?? $paciente->habitacion->numero ?? 'S/H' }}
                                    </div>
                                    <div class="text-[11px] text-apoyo font-semibold">
                                        Cama {{ $paciente->cama->codigo ?? $paciente->cama->numero ?? 'S/C' }}
                                    </div>
                                </td>

                                <!-- Nivel de Cuidado -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="rounded-lg bg-fondo-card px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-apoyo border border-borde">
                                        {{ $paciente->planCuidadoActivo?->nivel_cuidado ?? 'General' }}
                                    </span>
                                </td>

                                <!-- Últimos Signos -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($paciente->ultimo_signo)
                                        <div class="font-black text-titulo text-[11px]">
                                            PA {{ $paciente->ultimo_signo->presion_arterial ?: ($paciente->ultimo_signo->presion_sistolica . '/' . $paciente->ultimo_signo->presion_diastolica) }}
                                        </div>
                                        <div class="text-[10px] text-apoyo font-semibold">
                                            FC {{ $paciente->ultimo_signo->frecuencia_cardiaca ?? '--' }} bpm
                                            <span class="text-apoyo">({{ \Carbon\Carbon::parse($paciente->ultimo_signo->fecha)->isToday() ? substr($paciente->ultimo_signo->hora, 0, 5) : \Carbon\Carbon::parse($paciente->ultimo_signo->fecha)->format('d/m') }})</span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-apoyo italic">Sin registro hoy</span>
                                    @endif
                                </td>

                                <!-- Medicación Pendiente -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($paciente->meds_activas_total === 0)
                                        <span class="text-apoyo text-[11px]">Sin prescripción</span>
                                    @elseif($paciente->meds_pendientes_count > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                            <i class="ph-bold ph-clock"></i> {{ $paciente->meds_pendientes_count }} pend.
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                            <i class="ph-bold ph-check-circle"></i> Al día
                                        </span>
                                    @endif
                                </td>

                                <!-- Tareas Pendientes -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($paciente->tareas_pendientes_count > 0)
                                        <span class="font-black text-xs {{ $paciente->tareas_vencidas_count > 0 ? 'text-amber-600' : 'text-titulo' }}">
                                            {{ $paciente->tareas_pendientes_count }} pend.
                                        </span>
                                        @if($paciente->tareas_vencidas_count > 0)
                                            <div class="text-[10px] font-bold text-red-500">
                                                {{ $paciente->tareas_vencidas_count }} vencida(s)
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-[11px] text-apoyo">Al día</span>
                                    @endif
                                </td>

                                <!-- Seguimiento (Realizado / Pendiente) -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($paciente->seguimientos_hoy_count > 0)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                            <i class="ph-bold ph-check-circle"></i> Realizado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                            <i class="ph-bold ph-hourglass"></i> Pendiente
                                        </span>
                                    @endif
                                </td>

                                <!-- Alertas Activas -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($paciente->alertas_activas_count > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-black {{ $paciente->alertas_criticas_count > 0 ? 'bg-red-50 text-red-700 border border-red-200 animate-pulse dark:bg-red-950/40 dark:text-red-300' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                            <i class="ph-bold ph-bell-ringing"></i> {{ $paciente->alertas_activas_count }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-apoyo">0</span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- Abrir ficha -->
                                        <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" 
                                           class="rm-btn-secondary h-8 px-2.5 text-[11px] font-bold gap-1" 
                                           title="Abrir Ficha 360°">
                                            <i class="ph-bold ph-identification-card text-xs"></i>
                                            <span class="hidden xl:inline">Ficha</span>
                                        </a>

                                        <!-- Registrar signos -->
                                        <button wire:click="abrirRegistrarSignos('{{ $paciente->cod_am }}')" 
                                                class="rm-btn-secondary h-8 px-2 text-[11px] font-bold gap-1" 
                                                title="Registrar signos vitales">
                                            <i class="ph-bold ph-heartbeat text-xs"></i>
                                            <span class="hidden 2xl:inline">Signos</span>
                                        </button>

                                        <!-- Medicación -->
                                        <button wire:click="abrirAdministrarMed('{{ $paciente->cod_am }}')" 
                                                class="rm-btn-secondary h-8 px-2 text-[11px] font-bold gap-1" 
                                                title="Administrar medicación">
                                            <i class="ph-bold ph-pill text-xs"></i>
                                            <span class="hidden 2xl:inline">Meds</span>
                                        </button>

                                        <!-- Seguimiento -->
                                        <button wire:click="abrirRegistrarSeguimiento('{{ $paciente->cod_am }}')" 
                                                class="rm-btn-secondary h-8 px-2 text-[11px] font-bold gap-1" 
                                                title="Registrar seguimiento diario">
                                            <i class="ph-bold ph-note-pencil text-xs"></i>
                                            <span class="hidden 2xl:inline">Seguim.</span>
                                        </button>

                                        <!-- Menú secundario (...) -->
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" 
                                                    type="button" 
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-panel transition" 
                                                    title="Más opciones">
                                                <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                            </button>
                                            <div x-show="open" 
                                                 @click.outside="open = false" 
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 z-30 mt-1 w-48 rounded-xl border border-borde bg-fondo-panel p-1 shadow-lg text-left" 
                                                 style="display: none;">
                                                <button type="button" 
                                                        @click="open = false; $wire.abrirReportarAlerta('{{ $paciente->cod_am }}')" 
                                                        class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                                                    <i class="ph-bold ph-bell-ringing text-sm"></i>
                                                    <span>Reportar Alerta</span>
                                                </button>
                                                <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am, 'tab' => 'historial']) }}" 
                                                   class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-parrafo hover:bg-fondo-card transition">
                                                    <i class="ph-bold ph-clock-counter-clockwise text-sm text-apoyo"></i>
                                                    <span>Historial 360°</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- VISTA TARJETAS COMPACTAS -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($pacientes as $paciente)
                <div class="flex flex-col justify-between rounded-2xl border {{ $paciente->estado_color === 'red' ? 'border-red-300 bg-red-50/10 dark:border-red-800' : ($paciente->estado_color === 'amber' ? 'border-amber-300 bg-amber-50/10 dark:border-amber-800' : 'border-borde') }} bg-fondo-panel p-4 shadow-sm hover:shadow-md transition">
                    <div>
                        <!-- Header de la Tarjeta: Avatar, Datos Principales y Menú Secundario -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0">
                                    @if($paciente->foto)
                                        <img src="{{ asset('storage/' . $paciente->foto) }}" alt="{{ $paciente->nombres }}" class="h-11 w-11 rounded-full object-cover border border-borde">
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-fondo-card border border-borde font-black text-xs text-titulo">
                                            {{ mb_substr($paciente->nombres, 0, 1) }}{{ mb_substr($paciente->ap_paterno, 0, 1) }}
                                        </div>
                                    @endif
                                    <!-- Indicador semántico de estado -->
                                    <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-fondo-panel {{ $paciente->estado_color === 'red' ? 'bg-red-500 animate-pulse' : ($paciente->estado_color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}" title="Estado: {{ $paciente->estado_label }}"></span>
                                </div>
                                <div>
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" class="text-sm font-black text-titulo hover:text-boton-acento transition block leading-tight">
                                        {{ $paciente->nombres }} {{ $paciente->ap_paterno }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs font-semibold text-apoyo">{{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años</span>
                                        <span class="text-apoyo text-xs">·</span>
                                        <span class="text-[11px] font-bold text-parrafo">
                                            Hab. {{ $paciente->habitacion->codigo ?? $paciente->habitacion->numero ?? 'S/H' }} · Cama {{ $paciente->cama->codigo ?? $paciente->cama->numero ?? 'S/C' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Menú secundario (...) en tarjeta -->
                            <div x-data="{ open: false }" class="relative inline-block text-left shrink-0">
                                <button @click="open = !open" 
                                        type="button" 
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo hover:text-titulo hover:bg-fondo-panel transition" 
                                        title="Más opciones">
                                    <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                </button>
                                <div x-show="open" 
                                     @click.outside="open = false" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 z-30 mt-1 w-48 rounded-xl border border-borde bg-fondo-panel p-1 shadow-lg text-left" 
                                     style="display: none;">
                                    <button type="button" 
                                            @click="open = false; $wire.abrirReportarAlerta('{{ $paciente->cod_am }}')" 
                                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                                        <i class="ph-bold ph-bell-ringing text-sm"></i>
                                        <span>Reportar Alerta</span>
                                    </button>
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am, 'tab' => 'historial']) }}" 
                                       class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-parrafo hover:bg-fondo-card transition">
                                        <i class="ph-bold ph-clock-counter-clockwise text-sm text-apoyo"></i>
                                        <span>Historial 360°</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Fila de Estado Clínico y Nivel de Cuidado -->
                        <div class="mt-3 flex items-center justify-between border-t border-borde pt-2.5 pb-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-black {{ $paciente->estado_color === 'red' ? 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300' : ($paciente->estado_color === 'amber' ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300') }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $paciente->estado_color === 'red' ? 'bg-red-500' : ($paciente->estado_color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                <span>{{ $paciente->estado_label }}</span>
                            </span>
                            <span class="rounded-lg bg-fondo-card px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-apoyo border border-borde">
                                {{ $paciente->planCuidadoActivo?->nivel_cuidado ?? 'General' }}
                            </span>
                        </div>

                        <!-- Malla de Datos Clínicos y Operativos Compactos -->
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <!-- Último Control de Signos -->
                            <div class="rounded-xl bg-fondo-card/60 p-2 border border-borde">
                                <span class="block text-[9px] font-black uppercase text-apoyo tracking-wider">Últimos Signos</span>
                                @if($paciente->ultimo_signo)
                                    <p class="text-xs font-black text-titulo truncate mt-0.5">
                                        PA {{ $paciente->ultimo_signo->presion_arterial ?: ($paciente->ultimo_signo->presion_sistolica . '/' . $paciente->ultimo_signo->presion_diastolica) }}
                                        <span class="text-[10px] font-bold text-apoyo">· FC {{ $paciente->ultimo_signo->frecuencia_cardiaca ?? '--' }}</span>
                                    </p>
                                @else
                                    <p class="text-xs font-bold text-apoyo italic mt-0.5">Sin registro hoy</p>
                                @endif
                            </div>

                            <!-- Medicación Pendiente -->
                            <div class="rounded-xl bg-fondo-card/60 p-2 border border-borde">
                                <span class="block text-[9px] font-black uppercase text-apoyo tracking-wider">Medicación</span>
                                @if($paciente->meds_activas_total === 0)
                                    <p class="text-xs font-semibold text-apoyo mt-0.5">Sin prescripción</p>
                                @elseif($paciente->meds_pendientes_count > 0)
                                    <p class="text-xs font-black text-amber-600 mt-0.5 flex items-center gap-1">
                                        <i class="ph-bold ph-clock"></i> {{ $paciente->meds_pendientes_count }} pend.
                                    </p>
                                @else
                                    <p class="text-xs font-black text-emerald-600 mt-0.5 flex items-center gap-1">
                                        <i class="ph-bold ph-check"></i> Al día
                                    </p>
                                @endif
                            </div>

                            <!-- Tareas del Turno -->
                            <div class="rounded-xl bg-fondo-card/60 p-2 border border-borde">
                                <span class="block text-[9px] font-black uppercase text-apoyo tracking-wider">Tareas de Turno</span>
                                <p class="text-xs font-black {{ $paciente->tareas_pendientes_count > 0 ? ($paciente->tareas_vencidas_count > 0 ? 'text-amber-600' : 'text-titulo') : 'text-apoyo' }} mt-0.5">
                                    {{ $paciente->tareas_pendientes_count }} pend.
                                    @if($paciente->tareas_vencidas_count > 0)
                                        <span class="text-[10px] font-bold text-red-500">({{ $paciente->tareas_vencidas_count }} venc.)</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Seguimiento Diario -->
                            <div class="rounded-xl bg-fondo-card/60 p-2 border border-borde">
                                <span class="block text-[9px] font-black uppercase text-apoyo tracking-wider">Seguimiento</span>
                                <p class="text-xs font-black {{ $paciente->seguimientos_hoy_count > 0 ? 'text-emerald-600' : 'text-amber-600' }} mt-0.5 flex items-center gap-1">
                                    <i class="ph-bold {{ $paciente->seguimientos_hoy_count > 0 ? 'ph-check-circle' : 'ph-hourglass' }}"></i>
                                    {{ $paciente->seguimientos_hoy_count > 0 ? 'Realizado' : 'Pendiente' }}
                                </p>
                            </div>
                        </div>

                        <!-- Indicador de Alertas si Existen -->
                        @if($paciente->alertas_activas_count > 0)
                            <div class="mt-2 flex items-center justify-between rounded-xl px-2.5 py-1 text-xs {{ $paciente->alertas_criticas_count > 0 ? 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                <span class="flex items-center gap-1.5 font-bold">
                                    <i class="ph-bold ph-bell-ringing"></i>
                                    <span>{{ $paciente->alertas_activas_count }} alerta(s) activa(s)</span>
                                </span>
                                @if($paciente->alertas_criticas_count > 0)
                                    <span class="text-[10px] font-black uppercase tracking-wider bg-red-600 text-white rounded px-1.5 py-0.2">Crítica</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Botonera de Acciones Rápidas -->
                    <div class="mt-4 pt-3 border-t border-borde space-y-2">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" class="rm-btn-primary flex-1 justify-center py-2 text-xs font-bold shadow-sm">
                                <i class="ph-bold ph-identification-card text-sm"></i> Abrir Ficha
                            </a>
                        </div>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button wire:click="abrirRegistrarSignos('{{ $paciente->cod_am }}')" class="rm-btn-secondary px-2 py-1.5 text-[11px] font-bold justify-center" title="Registrar Signos">
                                <i class="ph-bold ph-heartbeat text-xs"></i> Signos
                            </button>
                            <button wire:click="abrirAdministrarMed('{{ $paciente->cod_am }}')" class="rm-btn-secondary px-2 py-1.5 text-[11px] font-bold justify-center" title="Administrar Medicación">
                                <i class="ph-bold ph-pill text-xs"></i> Meds
                            </button>
                            <button wire:click="abrirRegistrarSeguimiento('{{ $paciente->cod_am }}')" class="rm-btn-secondary px-2 py-1.5 text-[11px] font-bold justify-center" title="Registrar Seguimiento">
                                <i class="ph-bold ph-note-pencil text-xs"></i> Seguim.
                            </button>
                        </div>
                    </div>
                </div>

                @php
                    $partesPaRapida = preg_split('/\s*\/\s*/', trim((string) $signoPA));
                    $paRapidaAtipica = count($partesPaRapida) === 2
                        && is_numeric($partesPaRapida[0]) && is_numeric($partesPaRapida[1])
                        && (int) $partesPaRapida[0] <= (int) $partesPaRapida[1];
                @endphp
                @if($paRapidaAtipica)
                    <label class="mt-3 flex cursor-pointer items-start gap-2 rounded-xl bg-estado-advertenciaBg p-3 text-xs font-bold text-estado-advertencia">
                        <input type="checkbox" wire:model="signoConfirmarAtipico" class="mt-0.5 rounded border-borde text-boton-acento focus:ring-boton-acento">
                        <span>Repetí la medición y confirmo que los valores atípicos son correctos.</span>
                    </label>
                @endif
            @endforeach
        </div>
    @endif

    <!-- PAGINACIÓN -->
    <div class="mt-4">
        {{ $pacientes->links() }}
    </div>

    <!-- ── MODALES OPERATIVOS RÁPIDOS ─────────────────────────────── -->

    <!-- 1. Modal Registrar Signos -->
    @if($modalSignos)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-titulo">Registrar Signos Vitales</h3>
                        <p class="text-xs text-apoyo mt-0.5">Control clínico del turno para el residente seleccionado.</p>
                    </div>
                    <button wire:click="$set('modalSignos', false)" class="text-apoyo hover:text-titulo p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>
                
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Presión Arterial (PA)</label>
                        <input type="text" wire:model="signoPA" placeholder="120/80" class="rm-input w-full text-xs" />
                        @error('signoPA') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Frec. cardíaca (bpm)</label>
                        <input type="number" wire:model="signoFC" placeholder="72" class="rm-input w-full text-xs" />
                        @error('signoFC') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Temperatura (°C)</label>
                        <input type="number" step="0.1" wire:model="signoTemp" placeholder="36.5" class="rm-input w-full text-xs" />
                        @error('signoTemp') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Saturación SpO2 (%)</label>
                        <input type="number" wire:model="signoSat" placeholder="96" class="rm-input w-full text-xs" />
                        @error('signoSat') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Frec. Respiratoria (rpm)</label>
                        <input type="number" wire:model="signoFR" placeholder="16" class="rm-input w-full text-xs" />
                        @error('signoFR') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Glucosa (mg/dL)</label>
                        <input type="number" step="0.1" wire:model="signoGlucosa" placeholder="95" class="rm-input w-full text-xs" />
                        @error('signoGlucosa') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label class="text-xs font-bold text-parrafo block mb-1">Observaciones clínicas</label>
                    <input type="text" wire:model="signoObs" placeholder="Opcional..." class="rm-input w-full text-xs" />
                    @error('signoObs') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalSignos', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarSignos" class="rm-btn-primary px-4 py-2 text-xs">Guardar Control</button>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. Modal Registrar Seguimiento Diario -->
    @if($modalSeguimiento)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-titulo">Seguimiento Diario de Guardia</h3>
                        <p class="text-xs text-apoyo mt-0.5">Reporte del estado del residente durante el turno actual.</p>
                    </div>
                    <button wire:click="$set('modalSeguimiento', false)" class="text-apoyo hover:text-titulo p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Estado General</label>
                        <select wire:model="segEstado" class="rm-select w-full text-xs">
                            <option value="ESTABLE">Estable</option>
                            <option value="VIGILANCIA">En vigilancia</option>
                            <option value="DELICADO">Delicado</option>
                            <option value="CRITICO">Crítico</option>
                        </select>
                        @error('segEstado') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Alimentación</label>
                        <select wire:model="segAlimentacion" class="rm-select w-full text-xs">
                            <option value="COMPLETA">Completa (100%)</option>
                            <option value="PARCIAL">Parcial (50-75%)</option>
                            <option value="RECHAZADA">Rechazada</option>
                            <option value="AYUNO">Ayuno indicado</option>
                        </select>
                        @error('segAlimentacion') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Movilidad</label>
                        <select wire:model="segMovilidad" class="rm-select w-full text-xs">
                            <option value="INDEPENDIENTE">Independiente</option>
                            <option value="ASISTIDA">Asistida</option>
                            <option value="SILLA_RUEDAS">Silla de ruedas</option>
                            <option value="ENCAMADO">Encamado</option>
                        </select>
                        @error('segMovilidad') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Sueño / Descanso</label>
                        <select wire:model="segSueno" class="rm-select w-full text-xs">
                            <option value="NORMAL">Normal / Reparador</option>
                            <option value="INTERRUMPIDO">Interrumpido</option>
                            <option value="INSOMNIO">Insomnio</option>
                            <option value="SOMNOLENCIA">Somnolencia excesiva</option>
                        </select>
                        @error('segSueno') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-parrafo cursor-pointer">
                        <input type="checkbox" wire:model="segIncidente" class="rounded text-boton-acento" />
                        <span>Ocurrió un incidente durante la guardia</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-parrafo cursor-pointer">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded text-boton-acento" />
                        <span>Requiere revisión médica urgente</span>
                    </label>
                </div>

                <div class="mt-3">
                    <label class="text-xs font-bold text-parrafo block mb-1">Observaciones</label>
                    <textarea wire:model="segObs" rows="2" class="rm-input w-full text-xs" placeholder="Detalles de la guardia..."></textarea>
                    @error('segObs') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalSeguimiento', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarSeguimiento" class="rm-btn-primary px-4 py-2 text-xs">Guardar Seguimiento</button>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. Modal Administrar Medicación -->
    @if($modalMed)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-titulo">Administración de Medicamento</h3>
                        <p class="text-xs text-apoyo mt-0.5">Seleccione el fármaco prescrito y registre la acción.</p>
                    </div>
                    <button wire:click="$set('modalMed', false)" class="text-apoyo hover:text-titulo p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Medicamento Activo *</label>
                        @if(empty($medicacionesPaciente) || count($medicacionesPaciente) === 0)
                            <p class="text-xs text-amber-600 font-bold bg-amber-50 dark:bg-amber-950/30 p-2.5 rounded-xl border border-amber-200">El residente no tiene medicamentos activos prescritos actualmente.</p>
                        @else
                            <select wire:model="medCodMed" class="rm-select w-full text-xs">
                                @foreach($medicacionesPaciente as $m)
                                    <option value="{{ $m->cod_med_adulto }}">{{ $m->nombre_medicamento }} ({{ $m->dosis }} - {{ $m->via_administracion }})</option>
                                @endforeach
                            </select>
                        @endif
                        @error('medCodMed') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="radio" wire:model.live="medAdministrado" value="1" class="text-emerald-600" />
                            <span>Administrada con éxito</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="radio" wire:model.live="medAdministrado" value="0" class="text-amber-600" />
                            <span>Omitida</span>
                        </label>
                    </div>

                    @if(!$medAdministrado)
                        <div>
                            <label class="text-xs font-bold text-parrafo block mb-1">Motivo de Omisión *</label>
                            <textarea wire:model="medMotivoOmision" rows="2" class="rm-input w-full text-xs" placeholder="Ej: Rechazo de toma, náuseas..."></textarea>
                            @error('medMotivoOmision') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalMed', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarMed" class="rm-btn-primary px-4 py-2 text-xs">Registrar Toma</button>
                </div>
            </div>
        </div>
    @endif

    <!-- 4. Modal Reportar Alerta / Incidente -->
    @if($modalAlerta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-titulo">Reportar Alerta o Incidente</h3>
                        <p class="text-xs text-apoyo mt-0.5">Genera un evento persistente visible en el turno y la ficha del paciente.</p>
                    </div>
                    <button wire:click="$set('modalAlerta', false)" class="text-apoyo hover:text-titulo p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Tipo de Evento</label>
                        <select wire:model="alertaTipo" class="rm-select w-full text-xs">
                            <option value="INCIDENTE">Incidente / Caída</option>
                            <option value="CONDUCTA">Cambio de Conducta / Agitación</option>
                            <option value="DESORIENTACION">Desorientación Aguda</option>
                            <option value="DOLOR">Dolor Agudo Intenso</option>
                            <option value="SIGNOS">Descompensación Hemodinámica</option>
                            <option value="SOLICITUD_MEDICA">Solicitud de Revisión Médica</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Severidad</label>
                        <select wire:model="alertaNivel" class="rm-select w-full text-xs">
                            <option value="ALTO">Alto</option>
                            <option value="CRITICO">Crítico</option>
                            <option value="MEDIO">Medio</option>
                            <option value="BAJO">Bajo</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Descripción del Incidente *</label>
                        <textarea wire:model="alertaMotivo" rows="3" class="rm-input w-full text-xs" placeholder="Describa claramente lo sucedido y las medidas inmediatas adoptadas..."></textarea>
                        @error('alertaMotivo') <span class="text-estado-peligro text-[10px] font-bold block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalAlerta', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarAlerta" class="rm-btn-primary px-4 py-2 text-xs">Generar Alerta</button>
                </div>
            </div>
        </div>
    @endif
</div>
