<div class="p-6 bg-crema/50 min-h-screen">
    <!-- Encabezado Principal -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-azul-profundo tracking-tight uppercase">
                Planificación y Asignaciones
            </h1>
            <p class="text-sm text-azul-profundo/50 font-medium">
                Organización de turnos, horarios y cobertura de personal por área operativa en Casa Amandita.
            </p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            @can('turnos.crear')
                <button wire:click="abrirModalTurno" class="inline-flex items-center gap-2 px-4 py-2 bg-azul-profundo text-white rounded-xl hover:bg-azul-profundo/80 transition duration-150 shadow-sm font-semibold text-xs tracking-wider uppercase">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    Nuevo Turno
                </button>
            @endcan

            @can('turnos.asignar')
                <button wire:click="abrirModalAsignacion" class="inline-flex items-center gap-2 px-4 py-2 bg-terracota text-white rounded-xl hover:bg-terracota/80 transition duration-150 shadow-sm font-semibold text-xs tracking-wider uppercase">
                    <i class="ph-bold ph-user-plus text-base"></i>
                    Asignar Turno
                </button>
            @endcan

            @can('turnos.reportes')
                <button wire:click="$set('mostrarReportes', true)" class="inline-flex items-center gap-2 px-4 py-2 bg-[#E6DDD3] text-azul-profundo rounded-xl hover:bg-[#C7B5A3]/60 transition duration-150 font-semibold text-xs tracking-wider uppercase">
                    <i class="ph-bold ph-file-text text-base"></i>
                    Reportes
                </button>
            @endcan
        </div>
    </div>

    <!-- Métrica de Resumen Ejecutivo -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <!-- Card 1 -->
        <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-azul-profundo/40 uppercase tracking-widest">Total Turnos</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-black text-azul-profundo">{{ $totalTurnos }}</span>
                <span class="text-xs text-azul-profundo/40 font-semibold">tipos</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-azul-profundo/40 uppercase tracking-widest">Colaboradores</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-black text-emerald-600">{{ $totalAsignados }}</span>
                <span class="text-xs text-azul-profundo/40 font-semibold">activos</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-azul-profundo/40 uppercase tracking-widest">Áreas Activas</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-black text-azul-profundo">{{ $areasCubiertas }}</span>
                <span class="text-xs text-azul-profundo/40 font-semibold">cubiertas</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-azul-profundo/40 uppercase tracking-widest">Sin Cobertura</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-black {{ $areasSinCobertura > 0 ? 'text-rose-500' : 'text-azul-profundo/50' }}">{{ $areasSinCobertura }}</span>
                <span class="text-xs text-azul-profundo/40 font-semibold">áreas</span>
            </div>
        </div>

        <!-- Card 5 -->
        <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-azul-profundo/40 uppercase tracking-widest">Asignaciones</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-black text-azul-profundo">{{ $asignacionesActivas }}</span>
                <span class="text-xs text-azul-profundo/40 font-semibold">vigentes</span>
            </div>
        </div>

        <!-- Card 6 -->
        <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-azul-profundo/40 uppercase tracking-widest">Sin Turno</span>
            <div class="flex items-baseline gap-1 mt-2">
                <span class="text-2xl font-black {{ $usuariosSinTurno > 0 ? 'text-amber-500 font-black' : 'text-azul-profundo/40' }}">{{ $usuariosSinTurno }}</span>
                <span class="text-xs text-azul-profundo/40 font-semibold">usuarios</span>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white border border-[#C7B5A3]/20 rounded-2xl p-4 shadow-sm mb-6">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
            <!-- Buscar Colaborador -->
            <div class="md:col-span-2 relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar colaborador..." class="w-full pl-9 pr-4 py-2 rm-input">
                <i class="ph ph-magnifying-glass absolute left-3 top-3 text-azul-profundo/40"></i>
            </div>

            <!-- Filtro Área -->
            <div>
                <select wire:model.live="filtroArea" class="w-full py-2 px-3 bg-crema/30 border border-[#C7B5A3]/30 rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo/70 font-medium">
                    <option value="">Todas las Áreas</option>
                    @foreach($areasDisponibles as $area)
                        <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Turno -->
            <div>
                <select wire:model.live="filtroTurno" class="w-full py-2 px-3 bg-crema/30 border border-[#C7B5A3]/30 rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo/70 font-medium">
                    <option value="">Todos los Turnos</option>
                    @foreach($turnosDisponibles as $t)
                        <option value="{{ $t->cod_turno }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Día -->
            <div>
                <select wire:model.live="filtroDia" class="w-full py-2 px-3 bg-crema/30 border border-[#C7B5A3]/30 rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo/70 font-medium">
                    <option value="">Cualquier Día</option>
                    <option value="LUNES">Lunes</option>
                    <option value="MARTES">Martes</option>
                    <option value="MIERCOLES">Miércoles</option>
                    <option value="JUEVES">Jueves</option>
                    <option value="VIERNES">Viernes</option>
                    <option value="SABADO">Sábado</option>
                    <option value="DOMINGO">Domingo</option>
                </select>
            </div>

            <!-- Filtro Tipo -->
            <div>
                <select wire:model.live="filtroTipo" class="w-full py-2 px-3 bg-crema/30 border border-[#C7B5A3]/30 rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo/70 font-medium">
                    <option value="">Tipo de Asignación</option>
                    <option value="REGULAR">Regular</option>
                    <option value="APOYO">Apoyo Temporal</option>
                    <option value="COBERTURA">Cobertura Especial</option>
                    <option value="VOLUNTARIADO">Voluntariado</option>
                </select>
            </div>

            <!-- Limpiar Filtros -->
            <div class="flex gap-1 justify-end">
                <button wire:click="limpiarFiltros" class="w-full py-2 bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] text-azul-profundo/70 text-xs font-bold rounded-xl transition uppercase tracking-wider">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Conmutador de Vistas -->
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-azul-profundo uppercase tracking-widest">Listado de Asignaciones</h2>
        <div class="inline-flex bg-[#E6DDD3]/70 p-0.5 rounded-xl">
            <button wire:click="cambiarVista('calendario')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $vistaActual === 'calendario' ? 'bg-white text-azul-profundo shadow-sm' : 'text-azul-profundo/50 hover:text-azul-profundo' }} flex items-center gap-1">
                <i class="ph ph-calendar"></i>
                Calendario
            </button>
            <button wire:click="cambiarVista('cards')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $vistaActual === 'cards' ? 'bg-white text-azul-profundo shadow-sm' : 'text-azul-profundo/50 hover:text-azul-profundo' }} flex items-center gap-1">
                <i class="ph ph-squares-four"></i>
                Por Áreas
            </button>
            <button wire:click="cambiarVista('tabla')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $vistaActual === 'tabla' ? 'bg-white text-azul-profundo shadow-sm' : 'text-azul-profundo/50 hover:text-azul-profundo' }} flex items-center gap-1">
                <i class="ph ph-list-bullets"></i>
                Detallada
            </button>
        </div>
    </div>

    <!-- ──────────────────────────────────────────────
         VISTA 1: CALENDARIO (PLANNER SEMANAL)
         ────────────────────────────────────────────── -->
    @if($vistaActual === 'calendario')
        <div class="bg-white border border-[#C7B5A3]/20 rounded-3xl p-5 shadow-sm mb-6">
            <h3 class="text-xs font-black text-azul-profundo/40 uppercase tracking-widest mb-4">Planificador Semanal de Cobertura</h3>
            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                @php
                    $diasSemana = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];
                    $nombresDias = [
                        'LUNES' => 'Lunes',
                        'MARTES' => 'Martes',
                        'MIERCOLES' => 'Miércoles',
                        'JUEVES' => 'Jueves',
                        'VIERNES' => 'Viernes',
                        'SABADO' => 'Sábado',
                        'DOMINGO' => 'Domingo'
                    ];
                @endphp

                @foreach($diasSemana as $dia)
                    <div class="bg-crema/30 border border-[#C7B5A3]/20 rounded-2xl p-3 min-h-[300px] flex flex-col">
                        <div class="text-center pb-2 mb-2 border-b border-[#C7B5A3]/30/50">
                            <span class="text-xs font-black text-azul-profundo uppercase tracking-wider block">{{ $nombresDias[$dia] }}</span>
                        </div>

                        <div class="space-y-2 flex-grow overflow-y-auto max-h-[400px]">
                            @php
                                $asigsDelDia = $asignaciones->filter(function($a) use ($dia) {
                                    return $a->estado === 'ACTIVA' && is_array($a->dias_semana) && in_array($dia, $a->dias_semana);
                                });
                            @endphp

                            @forelse($asigsDelDia as $asig)
                                <div wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')" class="bg-white border border-[#C7B5A3]/20 hover:border-[#C7B5A3]/60 rounded-xl p-2.5 shadow-sm transition duration-150 cursor-pointer relative overflow-hidden group">
                                    <div class="absolute left-0 top-0 bottom-0 w-1" style="background-color: {{ $asig->turno->color ?? '#3B82F6' }}"></div>
                                    <h4 class="text-xs font-bold text-azul-profundo line-clamp-1 group-hover:text-terracota transition">{{ $asig->usuario ? $asig->usuario->name : 'N/D' }}</h4>
                                    <span class="text-[9px] text-azul-profundo/50 font-semibold block mt-0.5 line-clamp-1">{{ $asig->area ? $asig->area->nombre : 'N/D' }}</span>
                                    <div class="flex items-center justify-between mt-1 pt-1 border-t border-[#C7B5A3]/10">
                                        <span class="text-[8px] font-black uppercase tracking-wider text-azul-profundo/40">{{ $asig->turno ? $asig->turno->nombre : 'N/D' }}</span>
                                        @if($asig->tipo_asignacion === 'APOYO')
                                            <span class="px-1 py-0.5 bg-rose-50 text-rose-600 rounded text-[7px] font-bold">APOYO</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-azul-profundo/30 flex flex-col items-center justify-center h-full">
                                    <i class="ph ph-calendar-x text-xl mb-1"></i>
                                    <span class="text-[9px] font-semibold">Sin personal</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ──────────────────────────────────────────────
         VISTA 2: CARDS (POR ÁREAS INSTITUCIONALES)
         ────────────────────────────────────────────── -->
    @if($vistaActual === 'cards')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            @foreach($areasDisponibles as $area)
                @php
                    $asigsArea = $asignaciones->filter(fn($a) => $a->cod_area === $area->cod_area && $a->estado === 'ACTIVA');
                @endphp
                <div class="bg-white border border-[#C7B5A3]/20 rounded-3xl p-5 shadow-sm flex flex-col justify-between relative overflow-hidden">
                    <div>
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <span class="px-2 py-0.5 bg-[#E6DDD3]/60 text-azul-profundo/70 rounded text-[9px] font-bold uppercase tracking-wider">{{ $area->tipo_area }}</span>
                                <h3 class="text-base font-bold text-azul-profundo mt-1">{{ $area->nombre }}</h3>
                            </div>
                            <span class="h-8 w-8 rounded-xl bg-[#E6DDD3]/60 flex items-center justify-center text-azul-profundo/50">
                                <i class="ph-bold {{ $area->icono ?: 'ph-buildings' }} text-lg"></i>
                            </span>
                        </div>

                        <div class="flex items-center gap-2 mb-4 text-xs text-azul-profundo/50">
                            <i class="ph ph-crown"></i>
                            <span>Responsable: <strong>{{ $area->responsable ? $area->responsable->name : 'Sin Responsable' }}</strong></span>
                        </div>

                        <div class="space-y-2 mb-6">
                            <h4 class="text-[10px] font-black text-azul-profundo/40 uppercase tracking-widest">Colaboradores en Cobertura ({{ $asigsArea->count() }})</h4>
                            @forelse($asigsArea as $asig)
                                <div wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')" class="flex items-center justify-between p-2 bg-crema/30 hover:bg-[#E6DDD3]/50 border border-[#C7B5A3]/20 hover:border-[#C7B5A3]/30 rounded-xl transition duration-150 cursor-pointer">
                                    <div>
                                        <h5 class="text-xs font-bold text-azul-profundo">{{ $asig->usuario ? $asig->usuario->name : 'N/D' }}</h5>
                                        <p class="text-[9px] text-azul-profundo/50 font-semibold">{{ $asig->turno ? $asig->turno->nombre : 'N/D' }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @if($asig->tipo_asignacion === 'APOYO')
                                            <span class="px-1.5 py-0.5 bg-rose-50 text-rose-600 border border-rose-100 rounded text-[8px] font-black uppercase">Apoyo</span>
                                        @endif
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $asig->turno->color ?? '#3B82F6' }}"></span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 bg-crema/30 rounded-2xl border border-dashed border-[#C7B5A3]/30 text-azul-profundo/40">
                                    <i class="ph ph-warning-circle text-lg mb-1"></i>
                                    <p class="text-[10px] font-bold">Sin cobertura activa</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3 border-t border-[#C7B5A3]/20 flex items-center justify-between">
                        <button wire:click="exportarReporteAreaPdf('{{ $area->cod_area }}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-azul-profundo/50 hover:text-azul-profundo transition">
                            <i class="ph ph-file-pdf"></i>
                            Exportar PDF
                        </button>
                        <button wire:click="exportarReporteAreaExcel('{{ $area->cod_area }}')" class="inline-flex items-center gap-1 text-[10px] font-bold text-azul-profundo/50 hover:text-azul-profundo transition">
                            <i class="ph ph-file-xls"></i>
                            Exportar Excel
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ──────────────────────────────────────────────
         VISTA 3: TABLA DE DETALLES GENERALES
         ────────────────────────────────────────────── -->
    @if($vistaActual === 'tabla')
        <div class="bg-white border border-[#C7B5A3]/20 rounded-3xl shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-crema/30 border-b border-[#C7B5A3]/20">
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider">Colaborador</th>
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider">Área Operativa</th>
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider">Turno / Horario</th>
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider">Días Semanales</th>
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider">Periodo</th>
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider">Estado</th>
                            <th class="p-4 text-xs font-bold text-azul-profundo/50 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B5A3]/10">
                        @forelse($asignaciones as $asig)
                            <tr class="hover:bg-crema/30 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-[#E6DDD3]/60 flex items-center justify-center text-azul-profundo/70 font-bold text-xs uppercase shadow-sm">
                                            {{ substr($asig->usuario ? $asig->usuario->nombres : 'N', 0, 2) }}
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-azul-profundo">{{ $asig->usuario ? $asig->usuario->name : 'N/D' }}</h4>
                                            <p class="text-[9px] text-azul-profundo/40 font-semibold">ID: {{ $asig->cod_usu }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-xs font-bold text-azul-profundo">{{ $asig->area ? $asig->area->nombre : 'N/D' }}</span>
                                    <span class="block text-[9px] text-azul-profundo/40 font-semibold">{{ $asig->area ? $asig->area->tipo_area : '' }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $asig->turno->color ?? '#3B82F6' }}"></span>
                                        <span class="text-xs font-bold text-azul-profundo">{{ $asig->turno ? $asig->turno->nombre : 'N/D' }}</span>
                                    </div>
                                    @if($asig->turno && $asig->turno->hora_inicio)
                                        <span class="block text-[9px] text-azul-profundo/40 font-semibold ml-4">
                                            {{ substr($asig->turno->hora_inicio, 0, 5) }} - {{ substr($asig->turno->hora_fin, 0, 5) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($asig->dias_semana as $dia)
                                            <span class="px-1 py-0.5 bg-[#E6DDD3]/60 text-azul-profundo/70 rounded text-[8px] font-bold">{{ substr($dia, 0, 3) }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-xs font-bold text-azul-profundo">{{ $asig->fecha_inicio ? \Carbon\Carbon::parse($asig->fecha_inicio)->format('d/m/Y') : '' }}</span>
                                    <span class="block text-[9px] text-azul-profundo/40 font-semibold">al {{ $asig->fecha_fin ? \Carbon\Carbon::parse($asig->fecha_fin)->format('d/m/Y') : 'Presente' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $asig->estado === 'ACTIVA' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : ($asig->estado === 'FINALIZADA' ? 'bg-[#E6DDD3]/60 text-azul-profundo/50' : 'bg-rose-50 text-rose-600 border border-rose-100') }}">
                                        {{ $asig->estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <button wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')" class="p-1.5 text-azul-profundo/50 hover:text-azul-profundo hover:bg-[#E6DDD3]/60 rounded-lg transition" title="Ver Detalle">
                                            <i class="ph-bold ph-eye text-base"></i>
                                        </button>
                                        
                                        @can('turnos.asignar')
                                            @if($asig->estado === 'ACTIVA')
                                                <button wire:click="cargarAsignacion('{{ $asig->cod_asignacion }}')" class="p-1.5 text-azul-profundo/50 hover:text-azul-profundo hover:bg-[#E6DDD3]/60 rounded-lg transition" title="Editar">
                                                    <i class="ph-bold ph-pencil-simple text-base"></i>
                                                </button>
                                            @endif
                                        @endcan

                                        @can('turnos.finalizar')
                                            @if($asig->estado === 'ACTIVA')
                                                <button wire:confirm="¿Está seguro de finalizar esta asignación con fecha de hoy?" wire:click="finalizarAsignacion('{{ $asig->cod_asignacion }}')" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Finalizar Asignación">
                                                    <i class="ph-bold ph-x-circle text-base"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-azul-profundo/40">
                                    <i class="ph ph-warning text-3xl mb-1"></i>
                                    <p class="text-sm font-bold">No se encontraron asignaciones que coincidan con los filtros.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Gráficos Estadísticos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border border-[#C7B5A3]/20 rounded-3xl p-5 shadow-sm">
            <h3 class="text-xs font-black text-azul-profundo/40 uppercase tracking-widest mb-4">Distribución del Personal por Áreas</h3>
            <div class="h-64 flex items-center justify-center">
                @if(count($distribucionAreas) > 0)
                    <div class="w-full flex flex-col gap-2">
                        @foreach($distribucionAreas as $area => $count)
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-azul-profundo/70 mb-1">
                                    <span>{{ $area }}</span>
                                    <span>{{ $count }} asignados</span>
                                </div>
                                <div class="w-full bg-[#E6DDD3]/60 h-2.5 rounded-full overflow-hidden">
                                    <div class="bg-azul-profundo h-full rounded-full" style="width: {{ ($count / max($totalAsignados, 1)) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-azul-profundo/40 text-xs">Sin datos estadísticos suficientes.</span>
                @endif
            </div>
        </div>

        <div class="bg-white border border-[#C7B5A3]/20 rounded-3xl p-5 shadow-sm">
            <h3 class="text-xs font-black text-azul-profundo/40 uppercase tracking-widest mb-4">Carga de Trabajo por Turnos</h3>
            <div class="h-64 flex items-center justify-center">
                @if(count($distribucionTurnos) > 0)
                    <div class="w-full flex flex-col gap-2">
                        @foreach($distribucionTurnos as $turno => $count)
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-azul-profundo/70 mb-1">
                                    <span>{{ $turno }}</span>
                                    <span>{{ $count }} colaboradores</span>
                                </div>
                                <div class="w-full bg-[#E6DDD3]/60 h-2.5 rounded-full overflow-hidden">
                                    <div class="bg-terracota h-full rounded-full" style="width: {{ ($count / max($totalAsignados, 1)) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-azul-profundo/40 text-xs">Sin datos estadísticos suficientes.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- ──────────────────────────────────────────────
         MODAL 1: FORMULARIO TURNO (CREACIÓN/EDICIÓN)
         ────────────────────────────────────────────── -->
    @if($mostrarFormularioTurno)
        <div class="fixed inset-0 bg-azul-profundo/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg overflow-hidden border border-[#C7B5A3]/20 animate-in fade-in zoom-in-95 duration-150">
                <!-- Header -->
                <div class="bg-crema px-6 py-4 border-b border-[#C7B5A3]/30 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-azul-profundo uppercase tracking-wider">
                            {{ $isEdit ? 'Editar Turno Institucional' : 'Nuevo Turno Institucional' }}
                        </h3>
                        <p class="text-[10px] text-azul-profundo/40 font-semibold mt-0.5">Gestione las especificaciones horarias base del centro.</p>
                    </div>
                    <button wire:click="$set('mostrarFormularioTurno', false)" class="h-7 w-7 rounded-full bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] flex items-center justify-center text-azul-profundo/50 transition">
                        <i class="ph ph-x"></i>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="guardarTurno" class="p-6 space-y-4">
                    <!-- Nombre del Turno -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Nombre del Turno *</label>
                        <input wire:model="turno_nombre" type="text" placeholder="Ej. Turno Mañana" class="w-full px-3 py-2 bg-crema/30 border {{ $errors->has('turno_nombre') ? 'border-rose-400' : 'border-[#C7B5A3]/30' }} rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo font-medium">
                        @error('turno_nombre') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Horas -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Hora Inicio</label>
                            <input wire:model="turno_hora_inicio" type="time" class="w-full px-3 py-2 rm-input">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Hora Fin</label>
                            <input wire:model="turno_hora_fin" type="time" class="w-full px-3 py-2 rm-input">
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Descripción</label>
                        <textarea wire:model="turno_descripcion" rows="2" placeholder="Describa el alcance u objetivo de este turno..." class="w-full px-3 py-2 rm-input"></textarea>
                    </div>

                    <!-- Color & Estado -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Color de Marcador</label>
                            <input wire:model="turno_color" type="color" class="w-full h-10 p-1 bg-crema/30 border border-[#C7B5A3]/30 rounded-xl cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Estado</label>
                            <select wire:model="turno_estado" class="w-full px-3 py-2 rm-input">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Observaciones</label>
                        <textarea wire:model="turno_observaciones" rows="2" placeholder="Cualquier anotación administrativa adicional..." class="w-full px-3 py-2 rm-input"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="pt-4 border-t border-[#C7B5A3]/20 flex justify-end gap-2">
                        <button type="button" wire:click="$set('mostrarFormularioTurno', false)" class="px-4 py-2 bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] text-azul-profundo font-bold text-xs rounded-xl tracking-wider uppercase transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-azul-profundo hover:bg-azul-profundo/80 text-white font-bold text-xs rounded-xl tracking-wider uppercase transition">
                            {{ $isEdit ? 'Actualizar' : 'Registrar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ──────────────────────────────────────────────
         MODAL 2: FORMULARIO ASIGNACIÓN (CREACIÓN/EDICIÓN)
         ────────────────────────────────────────────── -->
    @if($mostrarFormularioAsignacion)
        <div class="fixed inset-0 bg-azul-profundo/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg overflow-hidden border border-[#C7B5A3]/20 animate-in fade-in zoom-in-95 duration-150">
                <!-- Header -->
                <div class="bg-crema px-6 py-4 border-b border-[#C7B5A3]/30 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-azul-profundo uppercase tracking-wider">
                            {{ $isEdit ? 'Editar Asignación de Turno' : 'Asignar Turno a Colaborador' }}
                        </h3>
                        <p class="text-[10px] text-azul-profundo/40 font-semibold mt-0.5">Establezca los horarios de cobertura para el personal.</p>
                    </div>
                    <button wire:click="$set('mostrarFormularioAsignacion', false)" class="h-7 w-7 rounded-full bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] flex items-center justify-center text-azul-profundo/50 transition">
                        <i class="ph ph-x"></i>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="guardarAsignacion" class="p-6 space-y-4">
                    <!-- Colaborador -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Colaborador / Personal *</label>
                        <select wire:model="asig_cod_usu" class="w-full px-3 py-2 bg-crema/30 border {{ $errors->has('asig_cod_usu') ? 'border-rose-400' : 'border-[#C7B5A3]/30' }} rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo font-medium">
                            <option value="">Seleccione un colaborador...</option>
                            @foreach($usuariosDisponibles as $usu)
                                <option value="{{ $usu->cod_usu }}">{{ $usu->name }} (Area: {{ $usu->areaInstitucional ? $usu->areaInstitucional->nombre : 'Ninguna' }})</option>
                            @endforeach
                        </select>
                        @error('asig_cod_usu') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Área Operativa -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Área de Cobertura / Destino *</label>
                        <select wire:model="asig_cod_area" class="w-full px-3 py-2 bg-crema/30 border {{ $errors->has('asig_cod_area') ? 'border-rose-400' : 'border-[#C7B5A3]/30' }} rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo font-medium">
                            <option value="">Seleccione área operativa de destino...</option>
                            @foreach($areasDisponibles as $area)
                                <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                        @error('asig_cod_area') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- REGLA DE NEGOCIO: SweetAlert Advertencia Apoyo Temporal Checkbox -->
                    @if($asig_cod_usu && $asig_cod_area)
                        @php
                            $usuSel = $usuariosDisponibles->firstWhere('cod_usu', $asig_cod_usu);
                            $cruzarArea = $usuSel && ($usuSel->cod_area !== $asig_cod_area);
                        @endphp
                        
                        @if($cruzarArea)
                            <div class="p-3.5 bg-amber-50 border border-amber-100 rounded-2xl flex items-start gap-2.5">
                                <i class="ph-bold ph-warning text-amber-600 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Cruce de Área Detectado</p>
                                    <p class="text-[9px] text-amber-700 font-medium mt-0.5">
                                        El usuario pertenece al área: <strong>{{ $usuSel->areaInstitucional ? $usuSel->areaInstitucional->nombre : 'Ninguna' }}</strong>. Para asignarlo a <strong>{{ $areasDisponibles->firstWhere('cod_area', $asig_cod_area)->nombre ?? '' }}</strong> debe marcarlo como Apoyo Temporal.
                                    </p>
                                    <label class="inline-flex items-center gap-2 mt-2 cursor-pointer">
                                        <input type="checkbox" wire:model="asig_apoyo_temporal" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                        <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Permitir Apoyo Temporal</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Turno -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Turno Asignado *</label>
                        <select wire:model="asig_cod_turno" class="w-full px-3 py-2 bg-crema/30 border {{ $errors->has('asig_cod_turno') ? 'border-rose-400' : 'border-[#C7B5A3]/30' }} rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo font-medium">
                            <option value="">Seleccione el turno...</option>
                            @foreach($turnosDisponibles as $t)
                                <option value="{{ $t->cod_turno }}">{{ $t->nombre }} @if($t->hora_inicio) ({{ substr($t->hora_inicio,0,5) }} - {{ substr($t->hora_fin,0,5) }}) @endif</option>
                            @endforeach
                        </select>
                        @error('asig_cod_turno') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Días de la Semana -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Días de Trabajo Semanal *</label>
                        <div class="grid grid-cols-4 gap-2 pt-1">
                            @foreach(['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'] as $dia)
                                <label class="flex items-center gap-1.5 p-2 bg-crema/30 border border-[#C7B5A3]/20 rounded-xl cursor-pointer hover:bg-[#E6DDD3]/40 transition">
                                    <input type="checkbox" wire:model="asig_dias_semana" value="{{ $dia }}" class="rounded border-[#C7B5A3]/30 text-azul-profundo focus:ring-azul-profundo/30">
                                    <span class="text-[10px] font-bold text-azul-profundo/70">{{ substr($dia, 0, 3) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('asig_dias_semana') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Periodo -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Fecha de Inicio *</label>
                            <input wire:model="asig_fecha_inicio" type="date" class="w-full px-3 py-2 bg-crema/30 border {{ $errors->has('asig_fecha_inicio') ? 'border-rose-400' : 'border-[#C7B5A3]/30' }} rounded-xl text-sm focus:outline-none focus:border-azul-profundo/50 text-azul-profundo font-medium">
                            @error('asig_fecha_inicio') <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Fecha de Fin (Opcional)</label>
                            <input wire:model="asig_fecha_fin" type="date" class="w-full px-3 py-2 rm-input">
                        </div>
                    </div>

                    <!-- Tipo y Estado -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Tipo de Asignación</label>
                            <select wire:model="asig_tipo_asignacion" class="w-full px-3 py-2 rm-input">
                                <option value="REGULAR">REGULAR</option>
                                <option value="APOYO">APOYO TEMPORAL</option>
                                <option value="COBERTURA">COBERTURA ESPECIAL</option>
                                <option value="VOLUNTARIADO">VOLUNTARIADO</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Estado</label>
                            <select wire:model="asig_estado" class="w-full px-3 py-2 rm-input">
                                <option value="ACTIVA">ACTIVA</option>
                                <option value="INACTIVA">INACTIVA</option>
                                <option value="FINALIZADA">FINALIZADA</option>
                            </select>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label class="block text-xs font-black text-azul-profundo/50 uppercase tracking-wider mb-1">Observaciones</label>
                        <textarea wire:model="asig_observaciones" rows="2" placeholder="Especificaciones especiales para esta cobertura..." class="w-full px-3 py-2 rm-input"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="pt-4 border-t border-[#C7B5A3]/20 flex justify-end gap-2">
                        <button type="button" wire:click="$set('mostrarFormularioAsignacion', false)" class="px-4 py-2 bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] text-azul-profundo font-bold text-xs rounded-xl tracking-wider uppercase transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-azul-profundo hover:bg-azul-profundo/80 text-white font-bold text-xs rounded-xl tracking-wider uppercase transition">
                            {{ $isEdit ? 'Guardar Cambios' : 'Confirmar Asignación' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ──────────────────────────────────────────────
         MODAL 3: FICHA DE DETALLE DE ASIGNACIÓN (PREVIEW)
         ────────────────────────────────────────────── -->
    @if($mostrarFichaAsignacion && $asignacionSeleccionada)
        <div class="fixed inset-0 bg-azul-profundo/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md overflow-hidden border border-[#C7B5A3]/20 animate-in fade-in zoom-in-95 duration-150">
                <!-- Header -->
                <div class="bg-crema px-6 py-4 border-b border-[#C7B5A3]/30 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-black text-azul-profundo/40 uppercase tracking-widest">Ficha de Asignación</h3>
                        <h4 class="text-sm font-bold text-azul-profundo mt-0.5">ID: {{ $asignacionSeleccionada->cod_asignacion }}</h4>
                    </div>
                    <button wire:click="$set('mostrarFichaAsignacion', false)" class="h-7 w-7 rounded-full bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] flex items-center justify-center text-azul-profundo/50 transition">
                        <i class="ph ph-x"></i>
                    </button>
                </div>

                <!-- Detalle -->
                <div class="p-6 space-y-4">
                    <!-- Colaborador -->
                    <div class="flex items-center gap-3 pb-3 border-b border-[#C7B5A3]/10">
                        <div class="h-10 w-10 rounded-full bg-[#E6DDD3]/60 flex items-center justify-center text-azul-profundo/70 font-black text-sm uppercase">
                            {{ substr($asignacionSeleccionada->usuario ? $asignacionSeleccionada->usuario->nombres : 'N', 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-azul-profundo">{{ $asignacionSeleccionada->usuario ? $asignacionSeleccionada->usuario->name : 'N/D' }}</h4>
                            <p class="text-[10px] text-azul-profundo/40 font-semibold">Área Principal: <strong>{{ $asignacionSeleccionada->usuario->areaInstitucional ? $asignacionSeleccionada->usuario->areaInstitucional->nombre : 'Ninguna' }}</strong></p>
                        </div>
                    </div>

                    <!-- Datos Generales -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-[9px] font-black text-azul-profundo/40 uppercase tracking-wider block">Área de Trabajo</span>
                            <span class="text-xs font-bold text-azul-profundo block mt-0.5">{{ $asignacionSeleccionada->area ? $asignacionSeleccionada->area->nombre : 'N/D' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-azul-profundo/40 uppercase tracking-wider block">Turno Asignado</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $asignacionSeleccionada->turno->color ?? '#3B82F6' }}"></span>
                                <span class="text-xs font-bold text-azul-profundo">{{ $asignacionSeleccionada->turno ? $asignacionSeleccionada->turno->nombre : 'N/D' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Periodo y Tipo -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-[9px] font-black text-azul-profundo/40 uppercase tracking-wider block">Periodo Asignado</span>
                            <span class="text-xs font-bold text-azul-profundo block mt-0.5">
                                {{ $asignacionSeleccionada->fecha_inicio ? \Carbon\Carbon::parse($asignacionSeleccionada->fecha_inicio)->format('d/m/Y') : '' }}
                                <span class="block text-[10px] text-azul-profundo/40 font-medium">al {{ $asignacionSeleccionada->fecha_fin ? \Carbon\Carbon::parse($asignacionSeleccionada->fecha_fin)->format('d/m/Y') : 'Presente' }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-[9px] font-black text-azul-profundo/40 uppercase tracking-wider block">Tipo de Asignación</span>
                            <span class="inline-block px-2 py-0.5 bg-[#E6DDD3]/60 text-azul-profundo rounded text-[9px] font-bold uppercase mt-1">
                                {{ $asignacionSeleccionada->tipo_asignacion ?: 'REGULAR' }}
                            </span>
                        </div>
                    </div>

                    <!-- Días de Trabajo -->
                    <div>
                        <span class="text-[9px] font-black text-azul-profundo/40 uppercase tracking-wider block mb-1">Días de Trabajo Semanal</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach($asignacionSeleccionada->dias_semana as $dia)
                                <span class="px-2 py-0.5 bg-[#E6DDD3]/60 text-azul-profundo/70 rounded text-[9px] font-bold">{{ $dia }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <span class="text-[9px] font-black text-azul-profundo/40 uppercase tracking-wider block">Observaciones</span>
                        <p class="text-xs text-azul-profundo/70 font-medium mt-1 bg-crema/30 p-2.5 rounded-xl border border-[#C7B5A3]/20/50">
                            {{ $asignacionSeleccionada->observaciones ?: 'Sin observaciones administrativas.' }}
                        </p>
                    </div>

                    <!-- Auditoría básica -->
                    <div class="pt-3 border-t border-[#C7B5A3]/10 flex items-center justify-between text-[8px] text-azul-profundo/40 uppercase tracking-wider font-semibold">
                        <span>Creado por: {{ $asignacionSeleccionada->creador ? $asignacionSeleccionada->creador->name : 'Sistema' }}</span>
                        <span>Actualizado: {{ $asignacionSeleccionada->updated_at->diffForHumans() }}</span>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="pt-4 border-t border-[#C7B5A3]/20 flex justify-end gap-2">
                        @can('turnos.finalizar')
                            @if($asignacionSeleccionada->estado === 'ACTIVA')
                                <button wire:click="finalizarAsignacion('{{ $asignacionSeleccionada->cod_asignacion }}')" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl tracking-wider uppercase transition">
                                    Finalizar
                                </button>
                            @endif
                            <button wire:confirm="¿Está seguro de archivar esta asignación?" wire:click="eliminarAsignacion('{{ $asignacionSeleccionada->cod_asignacion }}')" class="px-3 py-2 bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] text-azul-profundo/70 font-bold text-xs rounded-xl tracking-wider uppercase transition">
                                Archivar
                            </button>
                        @endcan
                        <button wire:click="$set('mostrarFichaAsignacion', false)" class="px-4 py-2 bg-azul-profundo hover:bg-azul-profundo/80 text-white font-bold text-xs rounded-xl tracking-wider uppercase transition">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ──────────────────────────────────────────────
         MODAL 4: PANEL DE REPORTES INTERACTIVOS
         ────────────────────────────────────────────── -->
    @if($mostrarReportes)
        <div class="fixed inset-0 bg-azul-profundo/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg overflow-hidden border border-[#C7B5A3]/20 animate-in fade-in zoom-in-95 duration-150">
                <!-- Header -->
                <div class="bg-crema px-6 py-4 border-b border-[#C7B5A3]/30 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-azul-profundo uppercase tracking-wider">Centro de Reportes de Planificación</h3>
                        <p class="text-[10px] text-azul-profundo/40 font-semibold mt-0.5">Exportación de datos de turnos y personal.</p>
                    </div>
                    <button wire:click="$set('mostrarReportes', false)" class="h-7 w-7 rounded-full bg-[#E6DDD3]/50 hover:bg-[#E6DDD3] flex items-center justify-center text-azul-profundo/50 transition">
                        <i class="ph ph-x"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-4">
                    <p class="text-xs text-azul-profundo/50 font-medium">Seleccione el formato y tipo de reporte que desea exportar del sistema de turnos:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Card PDF 1 -->
                        <div wire:click="exportarReporteGeneralPdf" class="p-4 border border-[#C7B5A3]/20 hover:border-[#C7B5A3]/60 bg-crema/30 rounded-2xl cursor-pointer transition flex items-center gap-3">
                            <span class="h-10 w-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-file-pdf"></i>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-azul-profundo uppercase tracking-wider">General As asignaciones PDF</h4>
                                <p class="text-[9px] text-azul-profundo/40 font-medium">Formato oficial impreso.</p>
                            </div>
                        </div>

                        <!-- Card Excel 1 -->
                        <div wire:click="exportarReporteGeneralExcel" class="p-4 border border-[#C7B5A3]/20 hover:border-[#C7B5A3]/60 bg-crema/30 rounded-2xl cursor-pointer transition flex items-center gap-3">
                            <span class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-file-xls"></i>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-azul-profundo uppercase tracking-wider">General As asignaciones XLS</h4>
                                <p class="text-[9px] text-azul-profundo/40 font-medium">Planilla interactiva Excel.</p>
                            </div>
                        </div>

                        <!-- Card PDF 2 -->
                        <div wire:click="exportarCoberturaSemanalPdf" class="p-4 border border-[#C7B5A3]/20 hover:border-[#C7B5A3]/60 bg-crema/30 rounded-2xl cursor-pointer transition flex items-center gap-3">
                            <span class="h-10 w-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-calendar"></i>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-azul-profundo uppercase tracking-wider">Grilla Semanal PDF</h4>
                                <p class="text-[9px] text-azul-profundo/40 font-medium">Matriz de cobertura semanal.</p>
                            </div>
                        </div>

                        <!-- Card Excel 2 -->
                        <div wire:click="exportarPersonalSinTurnoExcel" class="p-4 border border-[#C7B5A3]/20 hover:border-[#C7B5A3]/60 bg-crema/30 rounded-2xl cursor-pointer transition flex items-center gap-3">
                            <span class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                                <i class="ph ph-users-three"></i>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-azul-profundo uppercase tracking-wider">Personal Sin Turno XLS</h4>
                                <p class="text-[9px] text-azul-profundo/40 font-medium">Colaboradores sin programar.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-crema/30 border-t border-[#C7B5A3]/20 flex justify-end">
                    <button wire:click="$set('mostrarReportes', false)" class="px-4 py-2 bg-azul-profundo hover:bg-azul-profundo/80 text-white font-bold text-xs rounded-xl tracking-wider uppercase transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
