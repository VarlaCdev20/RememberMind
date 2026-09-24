<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-estado-infoBg text-estado-info">
                <i class="ph-fill ph-users-three text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">Pacientes — Seguimiento Médico</h2>
                <p class="text-sm font-semibold text-apoyo">Residentes activos y pacientes en valoración</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.medico.dashboard') }}"
               class="rm-btn-secondary h-10 px-4 flex items-center gap-2">
                <i class="ph-bold ph-arrow-left"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <button wire:click="$refresh" class="rm-btn-secondary h-10 w-10 flex items-center justify-center" title="Actualizar datos">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
            </button>
        </div>
    </div>

    {{-- Tabs de navegación clínica --}}
    <div class="inline-flex flex-wrap gap-1 rounded-xl bg-[#DED1C3] dark:bg-[#2C2723] p-1 border border-[#C7B9AA] dark:border-[#423B34]">
        <button wire:click="setTab('activos')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tab === 'activos' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
            <i class="ph-bold ph-user-check text-sm"></i>
            Residentes activos
            <span class="rounded-full bg-[#304060]/10 dark:bg-[#F3EAE1]/10 px-2 py-0.5 text-[10px] font-black text-[#304060] dark:text-[#F3EAE1]">{{ $cntActivos }}</span>
        </button>
        <button wire:click="setTab('pendientes')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tab === 'pendientes' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
            <i class="ph-bold ph-hourglass text-sm"></i>
            Pend. valoración
            @if($cntPendientes > 0)
            <span class="rounded-full bg-[#D2A45E] px-2 py-0.5 text-[10px] font-black text-white">{{ $cntPendientes }}</span>
            @endif
        </button>
        <button wire:click="setTab('interconsultas')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tab === 'interconsultas' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
            <i class="ph-bold ph-arrows-left-right text-sm"></i>
            Interconsultas
            @if($cntInterconsultas > 0)
            <span class="rounded-full bg-[#304060]/10 px-2 py-0.5 text-[10px] font-black text-[#304060] dark:text-[#F3EAE1]">{{ $cntInterconsultas }}</span>
            @endif
        </button>
        <button wire:click="setTab('historial')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tab === 'historial' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
            <i class="ph-bold ph-clock-counter-clockwise text-sm"></i>
            Historial clínico
        </button>
    </div>

    {{-- BARRA DE FILTROS UNIFICADA FORMATO ALERTAS --}}
    <section class="rounded-2xl bg-[#DED1C3] dark:bg-[#2C2723] border border-[#C7B9AA] dark:border-[#423B34] p-3 text-xs shadow-sm flex flex-col gap-2.5">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
            {{-- Buscador Principal --}}
            <div class="lg:col-span-8 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#677084] dark:text-[#9A9084]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input wire:model.live.debounce.300ms="busqueda"
                       type="text"
                       placeholder="Buscar paciente por nombre, apellido o CI..."
                       class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#26221F] py-2 pl-9 pr-8 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] placeholder-[#677084] dark:placeholder-[#8C8276] focus:border-[#A35A44] focus:outline-none h-[38px]">
                @if(!empty($busqueda))
                    <button type="button"
                            wire:click="limpiarFiltro('busqueda')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[#677084] hover:text-[#A35A44] cursor-pointer"
                            title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Filtro Estado del Paciente --}}
            <div class="lg:col-span-4">
                <select wire:model.live="filtroEstado"
                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="">Todos los estados clínicos</option>
                    <option value="ACTIVO">Activo</option>
                    <option value="OBSERVADO">En Observación</option>
                    <option value="SEGUIMIENTO_ESPECIAL">Seguimiento Especial</option>
                    <option value="ADMITIDO">Admitido</option>
                    <option value="PENDIENTE_VALORACION_MEDICA">Pendiente Valoración</option>
                    <option value="DECISION_ADMISION">Decisión Admisión</option>
                </select>
            </div>
        </div>

        {{-- Fila de chips de filtros activos --}}
        @php
            $hasFiltrosActivos = !empty($busqueda) || !empty($filtroEstado);
        @endphp
        @if($hasFiltrosActivos)
            <div class="w-full flex flex-wrap items-center justify-between gap-2 pt-2.5 border-t border-[#C7B9AA]/60 dark:border-[#423B34] text-xs">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-[#677084] dark:text-[#9A9084] flex items-center gap-1 mr-1">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($busqueda))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] text-[11px] font-semibold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Búsqueda: "{{ Str::limit($busqueda, 18) }}"</span>
                            <button type="button" wire:click="limpiarFiltro('busqueda')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroEstado))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#D2A45E]/15 border border-[#D2A45E]/30 text-[11px] font-bold text-[#8C6422] dark:text-[#E2BD7E]">
                            <span>Estado: {{ str_replace('_', ' ', $filtroEstado) }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroEstado')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#304060]/10 dark:bg-[#F3EAE1]/10 text-[#304060] dark:text-[#F3EAE1]">
                        {{ $pacientes->total() ?? $pacientes->count() }} coincidentes
                    </span>
                    <button type="button"
                            wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-1 rounded-xl bg-[#A35A44]/15 hover:bg-[#A35A44]/25 text-[#A35A44] dark:text-[#D58C79] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </section>

    {{-- Tabla --}}
    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        @if($pacientes->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-3">Paciente</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Últ. Signos Vitales</th>
                        <th class="px-5 py-3">Últ. Nota Médica</th>
                        <th class="px-5 py-3 text-center">Medicación</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($pacientes as $pac)
                    @php
                        $sv = $ultimosSignos[$pac->cod_am] ?? null;
                        $nota = $ultimasNotas[$pac->cod_am] ?? null;
                        $meds = $cntMedicacion[$pac->cod_am] ?? 0;
                        $edad = $pac->fecha_nacimiento ? \Carbon\Carbon::parse($pac->fecha_nacimiento)->age : '—';
                        $estadoStr = $pac->estado ?? '';
                        $estadoColor = match($estadoStr) {
                            'ACTIVO' => 'bg-estado-exitoBg text-estado-exito',
                            'SEGUIMIENTO_ESPECIAL' => 'bg-estado-advertenciaBg text-estado-advertencia',
                            'PENDIENTE_VALORACION_MEDICA' => 'bg-estado-advertenciaBg text-estado-advertencia',
                            'DECISION_ADMISION' => 'bg-estado-infoBg text-estado-info',
                            'OBSERVADO' => 'bg-boton-acento/10 text-boton-acento',
                            default => 'bg-fondo-panel text-apoyo',
                        };

                        // Alertas de signos
                        $alertaSv = false;
                        if ($sv) {
                            $sist = $sv->presion_sistolica ?? 0;
                            $fc   = $sv->frecuencia_cardiaca ?? 0;
                            $sat  = $sv->saturacion ?? 100;
                            $alertaSv = $sist > 160 || $sist < 90 || $fc > 100 || $fc < 50 || $sat < 92;
                        }
                    @endphp
                    <tr class="hover:bg-fondo-panel/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-estado-infoBg text-estado-info font-black text-sm">
                                    {{ substr($pac->nombres, 0, 1) }}{{ substr($pac->ap_paterno, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-titulo">{{ $pac->nombres }} {{ $pac->ap_paterno }}</div>
                                    <div class="text-[10px] text-apoyo">{{ $edad }} años · CI: {{ $pac->ci }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $estadoColor }}">
                                {{ str_replace('_', ' ', $estadoStr ?: 'SIN ESTADO') }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($sv)
                                <div class="flex items-center gap-1.5">
                                    @if($alertaSv)
                                    <i class="ph-bold ph-warning text-estado-advertencia text-sm"></i>
                                    @endif
                                    <div>
                                        @if($sv->presion_sistolica)
                                        <div class="text-xs font-bold {{ $alertaSv ? 'text-estado-advertencia' : 'text-titulo' }}">
                                            PA: {{ $sv->presion_sistolica }}/{{ $sv->presion_diastolica }}
                                        </div>
                                        @endif
                                        @if($sv->frecuencia_cardiaca)
                                        <div class="text-[10px] text-apoyo">FC: {{ $sv->frecuencia_cardiaca }} bpm · Sat: {{ $sv->saturacion }}%</div>
                                        @endif
                                        <div class="text-[10px] text-meta">{{ \Carbon\Carbon::parse($sv->fecha)->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-apoyo italic">Sin registro</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($nota)
                                <div>
                                    <div class="text-xs font-bold text-titulo">{{ $nota['tipo_nota'] ?? 'EVOLUCION' }}</div>
                                    <div class="text-[10px] text-apoyo max-w-[150px] truncate">{{ $nota['valoracion'] ?? '' }}</div>
                                    <div class="text-[10px] text-meta">{{ \Carbon\Carbon::parse($nota['fecha'])->diffForHumans() }}</div>
                                </div>
                            @else
                                <span class="text-xs text-apoyo italic">Sin notas</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($meds > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-boton-acento/10 px-2.5 py-0.5 text-[10px] font-black text-boton-acento">
                                <i class="ph-bold ph-pill text-xs"></i> {{ $meds }}
                            </span>
                            @else
                            <span class="text-xs text-apoyo">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <button wire:click="abrirFicha('{{ $pac->cod_am }}')"
                                        title="Ver ficha clínica integrada"
                                        class="h-8 px-2.5 rounded-lg bg-estado-infoBg text-estado-info hover:bg-estado-info hover:text-white transition text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph-bold ph-folder-open text-sm"></i> Ficha
                                </button>
                                <button wire:click="nuevaNota('{{ $pac->cod_am }}')"
                                        title="Nueva nota de evolución"
                                        class="h-8 w-8 rounded-lg bg-estado-exitoBg text-estado-exito hover:bg-estado-exito hover:text-white transition flex items-center justify-center">
                                    <i class="ph-bold ph-note-pencil text-sm"></i>
                                </button>
                                <button wire:click="nuevosSignos('{{ $pac->cod_am }}')"
                                        title="Registrar signos vitales"
                                        class="h-8 w-8 rounded-lg bg-fondo-panel text-parrafo hover:bg-boton-acento hover:text-white transition flex items-center justify-center">
                                    <i class="ph-bold ph-heartbeat text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-borde px-5 py-3">
            {{ $pacientes->links() }}
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-apoyo">
                <i class="ph-bold ph-users text-3xl"></i>
            </div>
            <h3 class="mt-4 text-base font-bold text-titulo">
                @if($tab === 'pendientes') Sin valoraciones pendientes
                @elseif($tab === 'interconsultas') Sin interconsultas activas
                @elseif($tab === 'historial') Sin pacientes en el historial
                @else Sin residentes activos
                @endif
            </h3>
            <p class="mt-1 text-sm text-apoyo">
                @if($tab === 'pendientes') No hay pacientes esperando valoración médica.
                @elseif($tab === 'interconsultas') No hay notas de interconsulta registradas.
                @elseif($tab === 'historial') No hay registros en el historial clínico.
                @else No hay residentes en seguimiento activo que coincidan con los filtros aplicados.
                @endif
            </p>
            @if($hasFiltrosActivos)
                <button wire:click="limpiarFiltros" class="mt-4 rm-btn-secondary text-xs">
                    Restablecer filtros
                </button>
            @endif
        </div>
        @endif
    </div>

    @livewire('clinica.nota-evolucion-medica-modal')
    @livewire('clinica.registro-signos-vitales-modal')
    @livewire('valoraciones.valoracion-barthel-modal')
</div>
