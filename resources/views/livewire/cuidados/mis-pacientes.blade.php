<div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-fill ph-users text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">
                    Mis Pacientes
                </h2>
                <p class="text-sm font-semibold text-apoyo">
                    Adultos mayores asignados a mi supervisión en este turno
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="rm-btn-secondary h-10 px-4">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline">Actualizar</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                    <i class="ph-bold ph-users text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-titulo">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-apoyo">Total Pacientes</p>
        </div>
        
        <div class="rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <i class="ph-bold ph-bell-ringing text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-red-600">{{ $stats['con_alerta'] }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-apoyo">Con Alerta</p>
        </div>

        <div class="rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                    <i class="ph-bold ph-list-checks text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-amber-600">{{ $stats['tareas_vencidas'] }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-apoyo">Tareas Vencidas</p>
        </div>

        <div class="rounded-[20px] border border-borde bg-fondo-panel p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-meta/10 text-meta">
                    <i class="ph-bold ph-heartbeat text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-black text-meta">{{ $stats['sin_seguimiento'] }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-apoyo">Sin Seguimiento Hoy</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-col gap-4 rounded-[24px] border border-borde bg-fondo-panel p-4 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
            <div class="relative w-full md:max-w-xs">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="ph ph-magnifying-glass text-lg text-apoyo"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="rm-input w-full pl-10" placeholder="Buscar paciente...">
            </div>

            <select wire:model.live="filtroRapido" class="rm-select w-full md:w-auto">
                <option value="TODOS">Todos los pacientes</option>
                <option value="CON_TAREAS">Con tareas pendientes</option>
                <option value="CON_ALERTAS">Con alertas activas</option>
                <option value="MEDICACION_PENDIENTE">Con medicación pendiente</option>
                <option value="CON_SIGNOS_PENDIENTES">Con signos pendientes</option>
                <option value="SIN_SEGUIMIENTO">Sin seguimiento diario</option>
            </select>
            
            <select wire:model.live="filtroEstado" class="rm-select w-full md:w-auto text-xs">
                <option value="TODOS">Todos los Estados</option>
                <option value="ACTIVO">Activos</option>
                <option value="EN_OBSERVACION">En Observación</option>
                <option value="HOSPITALIZADO">Hospitalizados</option>
            </select>
        </div>

        @if(auth()->user()->hasRole('SUPERADMINISTRADOR'))
            <div class="flex items-center gap-2">
                <select wire:model.live="filtroTurno" class="rm-select text-xs">
                    <option value="">Cualquier Turno</option>
                    @foreach($turnos as $t)
                        <option value="{{ $t->cod_turno }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filtroEnfermero" class="rm-select text-xs">
                    <option value="">Cualquier Enfermero</option>
                    @foreach($enfermeros as $e)
                        <option value="{{ $e->cod_usu }}">{{ $e->nombres }} {{ $e->ap_paterno }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <!-- Vista Escritorio: Tabla / Vista Móvil: Cards -->
    @if($pacientes->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-borde bg-fondo-panel py-16 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-fondo-card text-meta shadow-inner">
                <i class="ph-bold ph-bed text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-titulo">No hay pacientes asignados</h3>
            <p class="mt-1 max-w-sm text-sm text-apoyo">No se encontraron pacientes que coincidan con los filtros o tu turno actual.</p>
        </div>
    @else
        <!-- Tabla Escritorio (Oculta en móvil) -->
        <div class="hidden overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-sm lg:block">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-borde-suave bg-fondo-panel text-[10px] font-black uppercase tracking-widest text-apoyo">
                            <th class="px-6 py-4">Paciente</th>
                            <th class="px-6 py-4">Asignación</th>
                            <th class="px-6 py-4 text-center">Tareas</th>
                            <th class="px-6 py-4 text-center">Alertas</th>
                            <th class="px-6 py-4 text-center">Plan</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde-suave">
                        @foreach($pacientes as $paciente)
                            @php
                                $asignacion = $paciente->asignacionesTurno->first();
                                $tienePlan = $paciente->planesCuidado->count() > 0;
                            @endphp
                            <tr class="group transition-colors hover:bg-fondo-hover">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-boton-acento/10 text-boton-acento">
                                            <span class="text-sm font-bold">{{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->ap_paterno, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-titulo">{{ $paciente->nombres }} {{ $paciente->ap_paterno }}</p>
                                            <p class="text-xs font-semibold text-apoyo">
                                                CI: {{ $paciente->ci }} | Edad: {{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($asignacion)
                                        <div class="text-xs font-bold text-parrafo">
                                            Hab. {{ $paciente->habitacion->numero ?? 'N/A' }} / Cama {{ $paciente->cama->numero ?? 'N/A' }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="inline-block rounded-md bg-fondo-card px-2 py-0.5 text-[9px] font-bold uppercase text-meta border border-borde">
                                                Turno: {{ $asignacion->turno->nombre ?? 'N/A' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-block rounded-md bg-amber-50 px-2 py-0.5 text-[9px] font-bold uppercase text-amber-600 border border-amber-200">
                                            Asignación Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($paciente->tareas_pendientes_count > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600 border border-amber-200">
                                            <i class="ph-bold ph-list-checks"></i>
                                            {{ $paciente->tareas_pendientes_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-600 border border-emerald-200">
                                            <i class="ph-bold ph-check"></i> 0
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($paciente->alertas_activas_count > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 border border-red-200 animate-pulse">
                                            <i class="ph-bold ph-bell-ringing"></i>
                                            {{ $paciente->alertas_activas_count }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-apoyo">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($tienePlan)
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-[10px] font-bold uppercase text-blue-600 border border-blue-200">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase text-red-600 border border-red-200">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.enfermeria.pacientes.ficha', $paciente->cod_am) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-boton-acento hover:text-white" title="Ver Ficha Clínica">
                                            <i class="ph-bold ph-folder-user text-lg"></i>
                                        </a>
                                        <a href="{{ route('admin.enfermeria.tareas') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-amber-500 hover:text-white" title="Tareas">
                                            <i class="ph-bold ph-list-checks text-lg"></i>
                                        </a>
                                        <a href="#" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-blue-500 hover:text-white" title="Signos Vitales">
                                            <i class="ph-bold ph-thermometer text-lg"></i>
                                        </a>
                                        <a href="#" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-emerald-500 hover:text-white" title="Medicación">
                                            <i class="ph-bold ph-pill text-lg"></i>
                                        </a>
                                        <a href="#" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-purple-500 hover:text-white" title="Seguimiento Diario">
                                            <i class="ph-bold ph-heartbeat text-lg"></i>
                                        </a>
                                        <a href="{{ route('admin.enfermeria.alertas') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-card text-apoyo transition hover:bg-red-500 hover:text-white" title="Alertas">
                                            <i class="ph-bold ph-bell-ringing text-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pacientes->hasPages())
                <div class="border-t border-borde px-6 py-4">
                    {{ $pacientes->links() }}
                </div>
            @endif
        </div>

        <!-- Cards Móvil (Oculto en desktop) -->
        <div class="grid gap-4 lg:hidden sm:grid-cols-2">
            @foreach($pacientes as $paciente)
                @php
                    $asignacion = $paciente->asignacionesTurno->first();
                    $tienePlan = $paciente->planesCuidado->count() > 0;
                @endphp
                <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-boton-acento/10 text-boton-acento">
                            <span class="text-lg font-black">{{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->ap_paterno, 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-titulo truncate">{{ $paciente->nombres }} {{ $paciente->ap_paterno }}</h4>
                            <p class="text-xs font-semibold text-apoyo">Hab. {{ $paciente->habitacion->numero ?? 'N/A' }} / Cama {{ $paciente->cama->numero ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="rounded-xl border border-borde bg-fondo-card p-2 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo mb-1">Tareas</p>
                            @if($paciente->tareas_pendientes_count > 0)
                                <p class="text-sm font-black text-amber-600">{{ $paciente->tareas_pendientes_count }} pend.</p>
                            @else
                                <p class="text-sm font-black text-emerald-600">0 pend.</p>
                            @endif
                        </div>
                        <div class="rounded-xl border border-borde bg-fondo-card p-2 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo mb-1">Alertas</p>
                            @if($paciente->alertas_activas_count > 0)
                                <p class="text-sm font-black text-red-600 animate-pulse">{{ $paciente->alertas_activas_count }} activas</p>
                            @else
                                <p class="text-sm font-black text-emerald-600">Ninguna</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex gap-1.5 mt-4">
                        <a href="{{ route('admin.enfermeria.pacientes.ficha', $paciente->cod_am) }}" class="rm-btn-secondary flex-1 py-2 text-[10px] sm:text-xs text-boton-acento hover:bg-boton-acento/10" title="Ficha">
                            <i class="ph-bold ph-folder-user"></i>
                        </a>
                        <a href="{{ route('admin.enfermeria.tareas') }}" class="rm-btn-secondary flex-1 py-2 text-[10px] sm:text-xs text-amber-600 hover:bg-amber-50" title="Tareas">
                            <i class="ph-bold ph-list-checks"></i>
                        </a>
                        <a href="#" class="rm-btn-secondary flex-1 py-2 text-[10px] sm:text-xs text-blue-500 hover:bg-blue-50" title="Signos">
                            <i class="ph-bold ph-thermometer"></i>
                        </a>
                        <a href="#" class="rm-btn-secondary flex-1 py-2 text-[10px] sm:text-xs text-emerald-500 hover:bg-emerald-50" title="Medicación">
                            <i class="ph-bold ph-pill"></i>
                        </a>
                        <a href="#" class="rm-btn-secondary flex-1 py-2 text-[10px] sm:text-xs text-purple-500 hover:bg-purple-50" title="Seguimiento">
                            <i class="ph-bold ph-heartbeat"></i>
                        </a>
                        <a href="{{ route('admin.enfermeria.alertas') }}" class="rm-btn-secondary flex-1 py-2 text-[10px] sm:text-xs text-red-600 hover:bg-red-50" title="Alertas">
                            <i class="ph-bold ph-bell-ringing"></i>
                        </a>
                    </div>
                </div>
            @endforeach
            
            <div class="col-span-full">
                {{ $pacientes->links() }}
            </div>
        </div>
    @endif
</div>
