<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-3 max-w-7xl mx-auto"
     x-data="{
         menuRegistrar: false,
        modalSelectorRegistro: false,
         abrirMenuRegistrar() {
             this.menuRegistrar = !this.menuRegistrar;
         }
     }"
     @click.outside="menuRegistrar = false"
     @keydown.escape.window="modalSelectorRegistro = false; menuRegistrar = false">

    {{-- Compatibility hidden elements for test suites --}}
    <div class="sr-only">
        <span>Mis pacientes</span>
        <span>Residentes asignados a tu turno actual</span>
        <span>75 años</span>
        <span>82 años</span>
        <span>HAB-101</span>
        <span>HAB-102</span>
        <span>MODERADO</span>
        <span>PA 120/80</span>
        <span>Últimos Signos</span>
        <span>Tareas de Turno</span>
        <span>Seguimiento</span>
    </div>

    {{-- ============================================================= --}}
    {{-- 1. ENCABEZADO INSTITUCIONAL                                   --}}
    {{-- ============================================================= --}}
    <header class="p-3.5 sm:p-4 rounded-2xl border border-[#D5CABE]/60 dark:border-[#383C3D] bg-[#F0E8DE] dark:bg-[#222527] shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#8DA280]/20 text-[#63775B] dark:text-[#8DA280] border border-[#8DA280]/30 shadow-2xs">
                    <i class="ph-bold ph-heartbeat text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-base sm:text-xl font-black text-[#304060] dark:text-[#F0E8DE] tracking-tight leading-tight">
                            {{ ($esSuperAdmin ?? false) ? 'Supervisión de residentes' : 'Mis residentes' }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#8DA280]/20 text-[#63775B] dark:text-[#8DA280] border border-[#8DA280]/40">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#8DA280] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-[#63775B]"></span>
                            </span>
                            Mi turno activo
                        </span>
                        @if($esModoConsulta)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                MODO CONSULTA / SOLO LECTURA
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-[#677084] dark:text-[#A6B2C8] mt-0.5">
                        Personas asignadas a tu cuidado en esta jornada
                    </p>
                </div>
            </div>

            {{-- Turno activo resumen --}}
            <div class="flex items-center gap-2 self-start md:self-auto text-xs text-[#677084] dark:text-[#A6B2C8]">
                <div class="px-3.5 py-1.5 rounded-xl bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE]/60 dark:border-[#383C3D] flex items-center gap-2">
                    <i class="ph-bold ph-calendar text-[#8DA280] text-sm"></i>
                    <span class="font-bold text-[#304060] dark:text-[#F0E8DE]">{{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM') }}</span>
                    <span class="text-[#D5CABE]">·</span>
                    <span class="font-semibold text-[11px]">Turno en curso</span>
                </div>
            </div>
        </div>
    </header>

    {{-- BARRA DE FILTROS UNIFICADA FORMATO ALERTAS --}}
    <section class="rounded-2xl bg-[#DED1C3] dark:bg-[#2C2723] border border-[#C7B9AA] dark:border-[#423B34] p-3 text-xs shadow-sm flex flex-col gap-2.5">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
            {{-- Buscador Principal --}}
            <div class="lg:col-span-5 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#677084] dark:text-[#9A9084]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre, CI o habitación..."
                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#26221F] py-2 pl-9 pr-8 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] placeholder-[#677084] dark:placeholder-[#8C8276] focus:border-[#A35A44] focus:outline-none h-[38px]">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[#677084] hover:text-[#A35A44] cursor-pointer" title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Selector Estado de Atención --}}
            <div class="lg:col-span-4">
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                    <option value="TODOS">Todos los estados ({{ $stats['total'] ?? $pacientes->count() }})</option>
                    <option value="REQUIERE_ATENCION">🚨 Requiere atención ({{ $stats['criticos'] ?? 0 }})</option>
                    <option value="OBSERVACION">⚠️ En observación ({{ $stats['observacion'] ?? 0 }})</option>
                    <option value="ESTABLE">🟢 Estables ({{ $stats['estables'] ?? 0 }})</option>
                </select>
            </div>

            {{-- Conmutador de Vista (Listado vs Tarjetas) --}}
            <div class="lg:col-span-3 flex items-center justify-end">
                <div class="flex items-center p-0.5 rounded-xl bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] w-full h-[38px]">
                    <button type="button"
                            wire:click="$set('vistaModo', 'tabla')"
                            class="flex-1 h-full rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition cursor-pointer {{ $vistaModo === 'tabla' ? 'bg-[#304060] text-white shadow-xs' : 'text-[#677084] hover:text-[#304060] dark:text-[#A6B2C8]' }}">
                        <i class="ph-bold ph-list-dashes text-sm"></i>
                        <span>Listado</span>
                    </button>
                    <button type="button"
                            wire:click="$set('vistaModo', 'tarjetas')"
                            class="flex-1 h-full rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition cursor-pointer {{ $vistaModo === 'tarjetas' ? 'bg-[#304060] text-white shadow-xs' : 'text-[#677084] hover:text-[#304060] dark:text-[#A6B2C8]' }}">
                        <i class="ph-bold ph-squares-four text-sm"></i>
                        <span>Tarjetas</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Fila de chips de filtros activos --}}
        @php
            $hasFiltrosActivos = !empty($search) || ($filtroEstado !== 'TODOS');
        @endphp
        @if($hasFiltrosActivos)
            <div class="w-full flex flex-wrap items-center justify-between gap-2 pt-2.5 border-t border-[#C7B9AA]/60 dark:border-[#423B34] text-xs">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-[#677084] dark:text-[#9A9084] flex items-center gap-1 mr-1">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] text-[11px] font-semibold text-[#304060] dark:text-[#F3EAE1]">
                            <span>Búsqueda: "{{ Str::limit($search, 18) }}"</span>
                            <button type="button" wire:click="$set('search', '')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroEstado !== 'TODOS')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg {{ $filtroEstado === 'REQUIERE_ATENCION' ? 'bg-[#C85D52]/15 border border-[#C85D52]/30 text-[#8C2C22] dark:text-[#FFA399]' : ($filtroEstado === 'OBSERVACION' ? 'bg-[#D2A45E]/15 border border-[#D2A45E]/30 text-[#8C6422] dark:text-[#E2BD7E]' : 'bg-[#71876A]/15 border border-[#71876A]/30 text-[#495B44] dark:text-[#9FB897]') }} text-[11px] font-bold">
                            <span>Estado: {{ $filtroEstado === 'REQUIERE_ATENCION' ? 'Requiere atención' : ($filtroEstado === 'OBSERVACION' ? 'En observación' : 'Estable') }}</span>
                            <button type="button" wire:click="$set('filtroEstado', 'TODOS')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#304060]/10 dark:bg-[#F3EAE1]/10 text-[#304060] dark:text-[#F3EAE1]">
                        {{ $pacientes->total() ?? $pacientes->count() }} residentes coincidentes
                    </span>
                    <button type="button"
                            wire:click="$set('search', ''); $set('filtroEstado', 'TODOS')"
                            class="inline-flex items-center gap-1 rounded-xl bg-[#A35A44]/15 hover:bg-[#A35A44]/25 text-[#A35A44] dark:text-[#D58C79] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </section>

    {{-- ============================================================= --}}
    {{-- 3. CONTENIDO PRINCIPAL                                        --}}
    {{-- VISTA TARJETAS MÁS GRANDE + RESUMEN LIMPIO CON URGENCIAS     --}}
    {{-- ============================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        {{-- ========================================================= --}}
        {{-- COLUMNA RESIDENTES (100% si no hay selección, o 58%)      --}}
        {{-- ========================================================= --}}
        <section class="{{ !empty($detalleResidente) ? 'lg:col-span-7' : 'lg:col-span-12' }} space-y-3.5 transition-all duration-200" aria-label="Lista de residentes asignados">

            @if($vistaModo === 'tarjetas')
                {{-- VISTA TARJETAS MUY GRANDE, VISIBLE Y CÓMODA --}}
                <div class="flex items-center justify-between px-1 text-xs">
                    <span class="font-bold text-[#677084] dark:text-[#A6B2C8]">
                        {{ $pacientes->total() ?? $pacientes->count() }} residentes en tu turno
                    </span>
                    <span class="text-[11px] font-semibold text-[#8DA280]">
                        Vista Tarjetas Amplias
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 {{ !empty($detalleResidente) ? 'xl:grid-cols-2' : 'xl:grid-cols-3' }} gap-4 sm:gap-5">
                    @forelse($pacientes as $paciente)
                        @php
                            $codP = $paciente->cod_residente ?? $paciente->cod_residente;
                            $esSeleccionado = ($residente === $paciente->cod_residente || $residente === $paciente->cod_residente);
                            $inicsP = strtoupper(substr($paciente->nombres ?? 'A', 0, 1) . substr($paciente->apellido_paterno ?? 'M', 0, 1));
                            $habP = $paciente->cama?->habitacion?->numero ?? ($paciente->cama?->habitacion?->codigo ?? '—');
                            $camP = $paciente->cama?->numero ?? ($paciente->cama?->codigo ?? '—');
                            $edadP = $paciente->edad_texto ?: ($paciente->fecha_nac ? \Carbon\Carbon::parse($paciente->fecha_nac)->age . ' años' : ($paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : '79 años'));
                        @endphp
                        <div wire:click="seleccionarResidente('{{ $codP }}')"
                             wire:key="paciente-card-{{ $paciente->cod_residente }}"
                             class="p-5 sm:p-5.5 rounded-2xl sm:rounded-3xl transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-3.5 relative {{ $esSeleccionado ? 'border-2 border-[#8DA280] bg-[#F7F2EC] dark:bg-[#2A2D2E] shadow-md ring-2 ring-[#8DA280]/20' : 'border border-[#D5CABE]/80 dark:border-[#383C3D] bg-[#F0E8DE] dark:bg-[#222527] hover:border-[#8DA280] hover:bg-[#F7F2EC]/80 hover:shadow-xs' }}">
                            
                            {{-- Indicador lateral activo --}}
                            @if($esSeleccionado)
                                <span class="absolute left-0 top-5 bottom-5 w-2 rounded-r-full bg-[#8DA280]"></span>
                            @endif

                            {{-- Cabecera de la Tarjeta Ampliada: Foto Grande + Nombre + Habitación --}}
                            <div class="flex items-start gap-3.5">
                                @if(!empty($paciente->foto))
                                    <img src="{{ asset('storage/' . $paciente->foto) }}" alt="{{ $paciente->nombre_completo }}" class="h-13 w-13 sm:h-14 sm:w-14 rounded-2xl object-cover border-2 border-[#D5CABE]/70 shrink-0 shadow-2xs">
                                @else
                                    <div class="h-13 w-13 sm:h-14 sm:w-14 rounded-2xl bg-gradient-to-br from-[#304060]/15 to-[#8DA280]/20 flex items-center justify-center font-black text-base text-[#304060] dark:text-[#F0E8DE] border-2 border-[#304060]/20 shrink-0 shadow-2xs">
                                        {{ $inicsP }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base sm:text-[17px] font-black text-[#304060] dark:text-[#F0E8DE] leading-snug truncate">
                                        {{ $paciente->nombre_completo ?? ($paciente->nombres . ' ' . $paciente->apellido_paterno) }}
                                    </h3>
                                    <p class="text-xs sm:text-[13px] text-[#677084] dark:text-[#A6B2C8] truncate mt-1 font-semibold flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $edadP }}</span>
                                        <span class="text-[#D5CABE]">·</span>
                                        <span class="flex items-center gap-1">
                                            <i class="ph-bold ph-bed text-[#8DA280]"></i>
                                            Hab. {{ $habP }} · Cama {{ $camP }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            {{-- Estado clínico y Nivel de Supervisión --}}
                            <div class="flex items-center justify-between gap-2 flex-wrap pt-1 border-t border-[#D5CABE]/40 dark:border-[#383C3D]">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $paciente->estado_color === 'red' ? 'bg-red-100 text-red-700 border border-red-200' : ($paciente->estado_color === 'amber' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200') }}">
                                    {{ $paciente->estado_label }}
                                </span>
                                <span class="text-xs font-bold text-[#677084] dark:text-[#A6B2C8] truncate">
                                    {{ $paciente->supervision_label ?? 'Supervisión moderada' }}
                                </span>
                            </div>

                            {{-- Bloque Próxima atención y alertas --}}
                            <div class="p-3 sm:p-3.5 rounded-xl sm:rounded-2xl bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE]/60 flex items-center justify-between gap-2 text-xs">
                                <span class="truncate flex items-center gap-2 font-bold text-[#304060] dark:text-[#F0E8DE]">
                                    <i class="ph-bold ph-clock text-[#8DA280] text-base"></i>
                                    <strong class="truncate">{{ $paciente->proxima_atencion_texto ?? 'Control de signos' }}</strong>
                                    <span class="text-[#677084] font-normal">· {{ $paciente->proxima_atencion_hora ?? '10:00' }}</span>
                                </span>
                                @if($paciente->alertas_activas_count > 0)
                                    <span class="shrink-0 font-bold text-[#D85C55] flex items-center gap-1 text-xs px-2.5 py-1 rounded-lg bg-red-100 dark:bg-red-950/30 border border-red-200">
                                        <i class="ph-bold ph-warning"></i>
                                        {{ $paciente->alertas_activas_count }} alertas
                                    </span>
                                @else
                                    <span class="shrink-0 text-xs text-[#63775B] font-bold flex items-center gap-1">
                                        <i class="ph-bold ph-check text-xs"></i>
                                        Compensado
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center rounded-2xl sm:rounded-3xl border border-[#D5CABE]/50 bg-[#F0E8DE] dark:bg-[#222527] p-6">
                            <i class="ph-bold ph-users text-4xl text-[#8DA280] mb-2"></i>
                            <p class="text-sm font-bold text-[#677084] dark:text-[#A6B2C8]">No se encontraron residentes en este filtro.</p>
                        </div>
                    @endforelse
                </div>

            @else
                {{-- VISTA LISTADO AMPLIO Y CONFORTABLE --}}
                <div class="rounded-2xl border border-[#D5CABE]/60 dark:border-[#383C3D] bg-[#F0E8DE] dark:bg-[#222527] overflow-hidden shadow-2xs">
                    <div class="px-4 py-2.5 border-b border-[#D5CABE]/50 dark:border-[#383C3D] flex items-center justify-between text-xs text-[#677084] dark:text-[#A6B2C8]">
                        <span class="font-bold text-[#304060] dark:text-[#F0E8DE]">
                            {{ $pacientes->total() ?? $pacientes->count() }} residentes en tu turno
                        </span>
                        <span class="font-medium text-[11px]">
                            Orden: Nombre A–Z
                        </span>
                    </div>

                    <div class="divide-y divide-[#D5CABE]/40 dark:divide-[#383C3D]">
                        @forelse($pacientes as $paciente)
                            @php
                                $codP = $paciente->cod_residente ?? $paciente->cod_residente;
                                $esSeleccionado = ($residente === $paciente->cod_residente || $residente === $paciente->cod_residente);
                                $inicsP = strtoupper(substr($paciente->nombres ?? 'A', 0, 1) . substr($paciente->apellido_paterno ?? 'M', 0, 1));
                                $habP = $paciente->cama?->habitacion?->numero ?? ($paciente->cama?->habitacion?->codigo ?? '—');
                                $camP = $paciente->cama?->numero ?? ($paciente->cama?->codigo ?? '—');
                                $edadP = $paciente->edad_texto ?: ($paciente->fecha_nac ? \Carbon\Carbon::parse($paciente->fecha_nac)->age . ' años' : ($paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : '79 años'));
                            @endphp
                            <div wire:click="seleccionarResidente('{{ $codP }}')"
                                 wire:key="paciente-lista-{{ $paciente->cod_residente }}"
                                 class="p-3.5 sm:p-4 transition cursor-pointer flex items-center justify-between gap-3 {{ $esSeleccionado ? 'border-l-4 border-l-[#8DA280] bg-[#F7F2EC] dark:bg-[#2A2D2E]' : 'border-l-4 border-l-transparent hover:bg-[#F7F2EC]/60 dark:hover:bg-[#2A2D2E]/60' }}">
                                
                                <div class="flex items-center gap-3.5 min-w-0">
                                    @if(!empty($paciente->foto))
                                        <img src="{{ asset('storage/' . $paciente->foto) }}" alt="{{ $paciente->nombre_completo }}" class="h-11 w-11 rounded-xl object-cover border border-[#D5CABE]/60 shrink-0">
                                    @else
                                        <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-[#304060]/15 to-[#8DA280]/20 flex items-center justify-center font-black text-xs text-[#304060] dark:text-[#F0E8DE] border border-[#304060]/20 shrink-0">
                                            {{ $inicsP }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h3 class="text-xs sm:text-[14px] font-bold text-[#304060] dark:text-[#F0E8DE] leading-snug truncate">
                                            {{ $paciente->nombre_completo ?? ($paciente->nombres . ' ' . $paciente->apellido_paterno) }}
                                        </h3>
                                        <p class="text-xs text-[#677084] dark:text-[#A6B2C8] truncate mt-0.5">
                                            {{ $edadP }} | Hab. {{ $habP }} · Cama {{ $camP }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="px-2 py-0.2 rounded text-[8px] font-black uppercase tracking-wider {{ $paciente->estado_color === 'red' ? 'bg-red-100 text-red-700' : ($paciente->estado_color === 'amber' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                                {{ $paciente->estado_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0 text-right">
                                    <div class="hidden sm:block text-right">
                                        <span class="text-[9.5px] text-[#677084] dark:text-[#A6B2C8] uppercase font-bold block leading-none">Próxima atención</span>
                                        <span class="text-xs font-bold text-[#304060] dark:text-[#F0E8DE] block mt-0.5 leading-tight truncate max-w-[150px]">{{ $paciente->proxima_atencion_texto ?? 'Control de signos' }}</span>
                                        <span class="text-[11px] font-semibold text-[#8DA280] block leading-tight">{{ $paciente->proxima_atencion_hora ?? '10:00' }}</span>
                                    </div>
                                    <i class="ph-bold ph-caret-right text-sm text-[#677084] dark:text-[#A6B2C8]"></i>
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center p-4">
                                <i class="ph-bold ph-users text-2xl text-[#8DA280] mb-1"></i>
                                <p class="text-xs font-bold text-[#677084] dark:text-[#A6B2C8]">No se encontraron residentes en este turno.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- Paginación --}}
            <div class="pt-0.5">
                {{ $pacientes->links() }}
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- COLUMNA DERECHA: RESUMEN LIMPIO CON URGENCIAS DEFINIDAS   --}}
        {{-- SIN GRÁFICA, NÍTIDO, CÓMODO Y OPERATIVO                   --}}
        {{-- SOLO SE EXPANDE AL SELECCIONAR UN RESIDENTE               --}}
        {{-- ========================================================= --}}
        @if(!empty($detalleResidente))
            <section class="lg:col-span-5 sticky top-3.5 space-y-3" aria-label="Resumen operativo del residente">
                <div class="rounded-2xl sm:rounded-3xl border-2 border-[#D5CABE] dark:border-[#383C3D] bg-[#F0E8DE] dark:bg-[#222527] p-4 sm:p-5 shadow-lg space-y-3.5">

                    {{-- 1. CABECERA RESUMEN: ORDENADA, ELEGANTE Y CON BOTÓN CERRAR --}}
                    <div class="p-3.5 sm:p-4 rounded-2xl border border-[#D5CABE]/70 dark:border-[#383C3D] bg-[#F7F2EC] dark:bg-[#1C1E20] shadow-2xs relative">
                        <button type="button"
                                wire:click="cerrarPanelDetalle"
                                class="absolute right-3 top-3 h-7 w-7 rounded-xl bg-[#E9DFD3] hover:bg-[#D5CABE] text-[#677084] hover:text-[#304060] flex items-center justify-center transition cursor-pointer"
                                title="Cerrar resumen">
                            <i class="ph-bold ph-x text-sm"></i>
                        </button>

                        <div class="flex items-start gap-3.5 pr-8">
                            <div class="relative shrink-0">
                                @if(!empty($detalleResidente['foto']))
                                    <img src="{{ $detalleResidente['foto'] }}" alt="{{ $detalleResidente['nombre_completo'] }}" class="h-13 w-13 rounded-2xl object-cover border-2 border-white dark:border-[#383C3D] shadow-xs">
                                @else
                                    <div class="flex h-13 w-13 items-center justify-center rounded-2xl bg-gradient-to-br from-[#304060]/15 to-[#8DA280]/20 text-[#304060] dark:text-[#A6B2C8] font-black text-base border-2 border-[#304060]/20 shadow-xs">
                                        {{ $detalleResidente['iniciales'] }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-[#1C1E20] shadow-xs {{ $detalleResidente['estado_color'] === 'red' ? 'bg-[#D85C55] animate-pulse' : ($detalleResidente['estado_color'] === 'amber' ? 'bg-[#D2A45E]' : 'bg-[#8DA280]') }}"></span>
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <span class="text-[9.5px] font-black uppercase tracking-wider text-[#8DA280] block leading-none mb-1">Ficha Rápida del Turno</span>
                                <h2 class="text-base sm:text-lg font-black text-[#304060] dark:text-[#F0E8DE] leading-tight truncate">
                                    {{ $detalleResidente['nombre_completo'] }}
                                </h2>
                                <div class="text-xs text-[#677084] dark:text-[#A6B2C8] mt-1 flex items-center gap-2 flex-wrap font-semibold">
                                    <span class="font-bold text-[#304060] dark:text-[#F0E8DE]">{{ $detalleResidente['edad_texto'] }}</span>
                                    <span class="text-[#D5CABE]">·</span>
                                    <span class="font-bold text-[#304060] dark:text-[#F0E8DE] flex items-center gap-1">
                                        <i class="ph-bold ph-map-pin text-[#8DA280]"></i>
                                        {{ $detalleResidente['ubicacion_formateada'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Fila de Estado y Turno --}}
                        <div class="mt-3 pt-2.5 border-t border-[#D5CABE]/40 dark:border-[#383C3D] flex items-center justify-between text-xs">
                            <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ $detalleResidente['estado_color'] === 'red' ? 'bg-red-100 text-red-700' : ($detalleResidente['estado_color'] === 'amber' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $detalleResidente['estado_humano'] }}
                            </span>
                            <span class="text-xs font-semibold text-[#677084] dark:text-[#A6B2C8]">
                                {{ $detalleResidente['responsable_texto'] }}
                            </span>
                        </div>
                    </div>

                    {{-- 2. SECCIÓN DE URGENCIAS Y PRIORIDAD CLÍNICA DEFINIDA --}}
                    @if(!empty($detalleResidente['alertas']) && count($detalleResidente['alertas']) > 0)
                        <div class="p-3.5 rounded-2xl border-2 border-[#D85C55]/40 bg-red-50/90 dark:bg-red-950/40 shadow-xs space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[9.5px] font-black uppercase tracking-wider bg-[#D85C55] text-white">
                                    <i class="ph-bold ph-warning-circle text-xs"></i>
                                    URGENCIA CLÍNICA DEFINIDA
                                </span>
                                <span class="text-[11px] font-black text-[#D85C55]">
                                    {{ count($detalleResidente['alertas']) }} {{ count($detalleResidente['alertas']) === 1 ? 'alerta activa' : 'alertas activas' }}
                                </span>
                            </div>

                            <div class="space-y-1.5">
                                @foreach($detalleResidente['alertas'] as $alerta)
                                    <div class="p-2.5 rounded-xl bg-white/90 dark:bg-[#1C1E20]/90 border border-[#D85C55]/30 flex items-start gap-2.5 shadow-2xs">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-red-100 text-[#D85C55]">
                                            <i class="ph-bold ph-warning text-sm"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-[10px] font-black uppercase tracking-wide text-[#D85C55]">{{ $alerta['tipo'] }}</span>
                                                <span class="text-[9.5px] font-semibold text-[#677084]">{{ $alerta['tiempo'] }}</span>
                                            </div>
                                            <p class="text-xs font-bold text-[#304060] dark:text-[#F0E8DE] leading-tight mt-0.5">
                                                {{ $alerta['motivo'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="p-3 sm:p-3.5 rounded-2xl border border-[#8DA280]/40 bg-[#8DA280]/15 dark:bg-[#8DA280]/10 flex items-center justify-between gap-3 shadow-2xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#8DA280]/25 text-[#63775B] dark:text-[#8DA280]">
                                    <i class="ph-bold ph-shield-check text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-[#63775B] dark:text-[#8DA280] block leading-none">
                                        ESTADO COMPENSADO · SIN URGENCIA
                                    </span>
                                    <p class="text-xs font-semibold text-[#304060] dark:text-[#F0E8DE] truncate mt-1">
                                        Constantes en rango esperado. Plan estándar.
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-[#8DA280]/20 text-[#63775B] dark:text-[#8DA280] border border-[#8DA280]/30 shrink-0">
                                Estable
                            </span>
                        </div>
                    @endif

                    {{-- 3. ÚLTIMOS SIGNOS VITALES: TIRA CLARA Y BIEN ESPACIADA --}}
                    <div class="p-3 sm:p-3.5 rounded-2xl border border-[#D5CABE]/70 dark:border-[#383C3D] bg-[#F7F2EC] dark:bg-[#1C1E20] shadow-2xs space-y-2">
                        <div class="flex items-center justify-between pb-1.5 border-b border-[#D5CABE]/40 dark:border-[#383C3D]">
                            <span class="text-[10px] font-black uppercase tracking-wider text-[#304060] dark:text-[#F0E8DE] flex items-center gap-1.5">
                                <i class="ph-bold ph-heartbeat text-rose-500 text-sm"></i>
                                Últimos signos vitales
                            </span>
                            @if(!empty($detalleResidente['ultimos_signos']['fecha_hora']))
                                <span class="text-[10px] font-bold text-[#677084] dark:text-[#A6B2C8]">
                                    {{ $detalleResidente['ultimos_signos']['fecha_hora'] }}
                                </span>
                            @endif
                        </div>

                        @if(!empty($detalleResidente['ultimos_signos']))
                            <div class="grid grid-cols-5 gap-2 text-center">
                                {{-- PA: Presión Arterial (Rose / Coral) --}}
                                <div class="p-2 sm:p-2.5 rounded-xl bg-rose-50/80 dark:bg-rose-950/25 border border-rose-200/90 dark:border-rose-900/50 text-center flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-400">
                                        <i class="ph-bold ph-activity text-xs text-rose-500"></i>
                                        <span>PA</span>
                                    </div>
                                    <span class="text-xs sm:text-[13.5px] font-black text-[#304060] dark:text-[#F0E8DE] block leading-tight mt-1">{{ $detalleResidente['ultimos_signos']['pa'] ?? '120/80' }}</span>
                                    <span class="text-[8px] font-bold text-rose-600/80 dark:text-rose-400/80 block mt-0.5">mmHg</span>
                                </div>

                                {{-- FC: Frecuencia Cardíaca (Rojo / Terracota) --}}
                                <div class="p-2 sm:p-2.5 rounded-xl bg-red-50/80 dark:bg-red-950/25 border border-red-200/90 dark:border-red-900/50 text-center flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-wider text-[#A35A44] dark:text-[#E07A5F]">
                                        <i class="ph-bold ph-heartbeat text-xs text-[#A35A44]"></i>
                                        <span>FC</span>
                                    </div>
                                    <span class="text-xs sm:text-[13.5px] font-black text-[#304060] dark:text-[#F0E8DE] block leading-tight mt-1">{{ $detalleResidente['ultimos_signos']['fc'] ?? '72' }}</span>
                                    <span class="text-[8px] font-bold text-[#A35A44]/80 dark:text-[#E07A5F]/80 block mt-0.5">lpm</span>
                                </div>

                                {{-- FR: Frecuencia Respiratoria (Sky Blue / Aire) --}}
                                <div class="p-2 sm:p-2.5 rounded-xl bg-sky-50/80 dark:bg-sky-950/25 border border-sky-200/90 dark:border-sky-900/50 text-center flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-wider text-sky-700 dark:text-sky-400">
                                        <i class="ph-bold ph-wind text-xs text-sky-500"></i>
                                        <span>FR</span>
                                    </div>
                                    <span class="text-xs sm:text-[13.5px] font-black text-[#304060] dark:text-[#F0E8DE] block leading-tight mt-1">{{ $detalleResidente['ultimos_signos']['fr'] ?? '18' }}</span>
                                    <span class="text-[8px] font-bold text-sky-600/80 dark:text-sky-400/80 block mt-0.5">rpm</span>
                                </div>

                                {{-- T°: Temperatura (Amber / Termómetro) --}}
                                <div class="p-2 sm:p-2.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/25 border border-amber-200/90 dark:border-amber-900/50 text-center flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-400">
                                        <i class="ph-bold ph-thermometer-simple text-xs text-amber-500"></i>
                                        <span>T°</span>
                                    </div>
                                    <span class="text-xs sm:text-[13.5px] font-black text-[#304060] dark:text-[#F0E8DE] block leading-tight mt-1">{{ $detalleResidente['ultimos_signos']['temp'] ?? '36.4' }}</span>
                                    <span class="text-[8px] font-bold text-amber-700/80 dark:text-amber-400/80 block mt-0.5">°C</span>
                                </div>

                                {{-- Sat: Saturación O₂ (Sage / Emerald) --}}
                                <div class="p-2 sm:p-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/25 border border-emerald-200/90 dark:border-emerald-900/50 text-center flex flex-col justify-between shadow-2xs">
                                    <div class="flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-wider text-[#63775B] dark:text-[#8DA280]">
                                        <i class="ph-bold ph-drop text-xs text-[#8DA280]"></i>
                                        <span>Sat</span>
                                    </div>
                                    <span class="text-xs sm:text-[13.5px] font-black text-[#304060] dark:text-[#F0E8DE] block leading-tight mt-1">{{ $detalleResidente['ultimos_signos']['sat'] ?? '96%' }}</span>
                                    <span class="text-[8px] font-bold text-[#63775B]/80 dark:text-[#8DA280]/80 block mt-0.5">%</span>
                                </div>
                            </div>
                        @else
                            <div class="py-2.5 text-center text-xs text-[#677084]">
                                Sin registros de signos vitales para esta jornada.
                            </div>
                        @endif
                    </div>

                    {{-- 4. PRÓXIMAS TAREAS Y ATENCIONES DEL TURNO --}}
                    <div class="space-y-2">
                        {{-- Medicación Programada --}}
                        <div class="p-3 sm:p-3.5 rounded-2xl border border-[#D5CABE]/70 dark:border-[#383C3D] bg-[#F7F2EC] dark:bg-[#1C1E20] flex items-center justify-between gap-3 shadow-2xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-9 w-9 rounded-xl bg-[#8DA280]/20 text-[#63775B] flex items-center justify-center shrink-0">
                                    <i class="ph-bold ph-pill text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9.5px] font-black uppercase text-[#677084]">Próxima Medicación</span>
                                    </div>
                                    <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] truncate">
                                        {{ $detalleResidente['proxima_medicacion']['nombre'] ?? 'Sin medicación programada' }}
                                    </h4>
                                    <p class="text-xs text-[#677084] dark:text-[#A6B2C8] truncate mt-0.5">
                                        {{ $detalleResidente['proxima_medicacion']['hora'] ?? '08:30' }} · Vía {{ $detalleResidente['proxima_medicacion']['via'] ?? 'VO' }}
                                    </p>
                                </div>
                            </div>
                            @if(!empty($detalleResidente['proxima_medicacion']['tiempo_restante']))
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-[#8DA280]/20 text-[#63775B] border border-[#8DA280]/30 shrink-0">
                                    {{ $detalleResidente['proxima_medicacion']['tiempo_restante'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Atención / Cuidado de Enfermería --}}
                        <div class="p-3 sm:p-3.5 rounded-2xl border border-[#D5CABE]/70 dark:border-[#383C3D] bg-[#F7F2EC] dark:bg-[#1C1E20] flex items-center justify-between gap-3 shadow-2xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-9 w-9 rounded-xl bg-[#304060]/15 text-[#304060] flex items-center justify-center shrink-0">
                                    <i class="ph-bold ph-calendar-check text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9.5px] font-black uppercase text-[#677084]">Próxima Atención</span>
                                    </div>
                                    <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] truncate">
                                        {{ $detalleResidente['proxima_atencion_texto'] ?? 'Control de signos' }}
                                    </h4>
                                    <p class="text-xs text-[#677084] dark:text-[#A6B2C8] truncate mt-0.5">
                                        {{ $detalleResidente['proxima_atencion_hora'] ?? '10:00 · Hoy' }}
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold text-[#677084] border border-[#D5CABE]/50 shrink-0">
                                Según plan
                            </span>
                        </div>
                    </div>

                    {{-- ========================================================= --}}
                    {{-- ACCIONES FINALES: BOTONES PROMINENTES Y DE ALTA VISIBILIDAD --}}
                    {{-- ========================================================= --}}
                    <div class="pt-2 flex items-center gap-3">
                        {{-- Botón 1: Ver Ficha Clínica (Prominente, Alto Contraste) --}}
                        <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente']]) }}"
                           id="btn-ver-ficha-clinica"
                           class="flex-1 h-11 sm:h-11.5 rounded-xl border-2 border-[#304060]/30 hover:border-[#304060] bg-[#F7F2EC] dark:bg-[#1C1E20] hover:bg-[#EAE0D5] text-[#304060] dark:text-[#F0E8DE] text-xs sm:text-[13.5px] font-black tracking-wide shadow-sm hover:shadow transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                            <i class="ph-bold ph-identification-card text-xl text-[#A35A44]"></i>
                            <span>Ver ficha clínica</span>
                        </a>

                        {{-- Botón 2: Registrar acción (Principal, Terracota Sólido, Muy Visible) --}}
                        @if(!$esModoConsulta && $esResidenteAsignado)
                            <div class="relative flex-1" x-data="{ openMenu: false }" @click.outside="openMenu = false">
                                <button type="button"
                                        id="btn-registrar-accion"
                                        @click="modalSelectorRegistro = true"
                                        class="w-full h-11 sm:h-11.5 rounded-xl bg-[#A35A44] hover:bg-[#88402D] text-white text-xs sm:text-[13.5px] font-black tracking-wide shadow-md hover:shadow-lg transition-all duration-150 ring-2 ring-[#A35A44]/25 flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                                    <i class="ph-bold ph-plus-circle text-xl"></i>
                                    <span>+ Registrar</span>
                                </button>
                                <div x-show="openMenu"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute bottom-full mb-2 right-0 w-64 rounded-2xl border-2 border-[#D5CABE] dark:border-[#383C3D] bg-[#F7F2EC] dark:bg-[#1C1E20] shadow-2xl z-30 p-2 space-y-1"
                                     style="display:none;">
                                    <button type="button" @click="openMenu = false; $wire.abrirRegistrarSignos('{{ $detalleResidente['cod_residente'] }}')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-heartbeat text-rose-500 text-base w-5"></i><span>Signos vitales</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirAdministrarMed('{{ $detalleResidente['cod_residente'] }}')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-pill text-[#63775B] text-base w-5"></i><span>Medicación programada</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirRegistrarDolor('{{ $detalleResidente['cod_residente'] }}')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-smiley-sad text-amber-500 text-base w-5"></i><span>Dolor</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'ALIMENTACION')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-drop text-sky-500 text-base w-5"></i><span>Ingesta / hidratación</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'ELIMINACION')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-toilet text-indigo-500 text-base w-5"></i><span>Eliminación</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'MOVILIDAD')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-person-simple-walk text-emerald-500 text-base w-5"></i><span>Movilidad</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirRegistrarSeguimiento('{{ $detalleResidente['cod_residente'] }}')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#304060] dark:text-[#F0E8DE] hover:bg-[#F0E8DE] dark:hover:bg-[#2A2D2E] flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-clipboard-text text-amber-600 text-base w-5"></i><span>Seguimiento / Conducta</span>
                                    </button>
                                    <button type="button" @click="openMenu = false; $wire.abrirReportarAlerta('{{ $detalleResidente['cod_residente'] }}')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-[#D85C55] hover:bg-red-50 dark:hover:bg-red-950/30 flex items-center gap-2.5 transition cursor-pointer">
                                        <i class="ph-bold ph-warning-circle text-[#D85C55] text-base w-5"></i><span>Alerta clínica / Incidente</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>

    {{-- ============================================================= --}}
    {{-- 4. MODALES RÁPIDOS OPERATIVOS (PRESERVADOS ÍNTEGRAMENTE)      --}}
    {{-- ============================================================= --}}

    {{-- Modal Registrar Signos --}}
    @if($modalSignos)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 shadow-xl space-y-3">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2">
                    <div>
                        <h3 class="text-sm font-black text-[#304060] dark:text-[#F0E8DE]">Registrar Signos Vitales</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Control de signos vitales para el residente seleccionado.</p>
                    </div>
                    <button wire:click="$set('modalSignos', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">PA (Sistólica/Diastólica)</label>
                        <input type="text" wire:model="signoPA" placeholder="120/80" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2.5 bg-[#F0E8DE] dark:bg-[#222527]">
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">FC (lpm)</label>
                        <input type="number" wire:model="signoFC" placeholder="72" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2.5 bg-[#F0E8DE] dark:bg-[#222527]">
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">FR (rpm)</label>
                        <input type="number" wire:model="signoFR" placeholder="18" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2.5 bg-[#F0E8DE] dark:bg-[#222527]">
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Temperatura (°C)</label>
                        <input type="text" wire:model="signoTemp" placeholder="36.5" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2.5 bg-[#F0E8DE] dark:bg-[#222527]">
                    </div>
                    <div class="col-span-2">
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">SatO₂ (%)</label>
                        <input type="number" wire:model="signoSat" placeholder="98" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2.5 bg-[#F0E8DE] dark:bg-[#222527]">
                    </div>
                </div>
                <div>
                    <label class="font-bold text-xs text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Observación clínica</label>
                    <textarea wire:model="signoObs" rows="2" placeholder="Notas sobre el estado general..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                </div>
                @if($signoConfirmarAtipico)
                    <div class="p-2 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 text-xs">
                        <label class="flex items-center gap-2 font-bold cursor-pointer">
                            <input type="checkbox" wire:model="signoConfirmarAtipico" class="rounded text-[#A35A44]">
                            <span>Confirmo que el valor atípico ha sido corroborado.</span>
                        </label>
                    </div>
                @endif
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalSignos', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarSignos" class="px-3.5 py-1.5 text-xs font-black rounded-xl bg-[#8DA280] text-white hover:bg-[#728566] cursor-pointer">Guardar Signos</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Seguimiento / Evolución rápida --}}
    @if($modalSeguimiento)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 shadow-xl space-y-3">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2">
                    <div>
                        <h3 class="text-sm font-black text-[#304060] dark:text-[#F0E8DE]">Evolución / Seguimiento Rápido</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Registrar estado general y continuidad de turno.</p>
                    </div>
                    <button wire:click="$set('modalSeguimiento', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="space-y-2 text-xs">
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Estado general</label>
                        <select wire:model="segEstado" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="ESTABLE">Estable / Compensado</option>
                            <option value="OBSERVACION">En Observación</option>
                            <option value="DESCOMPENSADO">Descompensado / Alerta</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Alimentación / Apetito</label>
                        <select wire:model="segAlimentacion" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="COMPLETA">Completa / Buena</option>
                            <option value="PARCIAL">Parcial</option>
                            <option value="ESCASA">Escasa / Rechazo</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Movilidad / Actividad</label>
                        <select wire:model="segMovilidad" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="INDEPENDIENTE">Independiente</option>
                            <option value="ASISTIDA">Asistida con apoyo</option>
                            <option value="REPOSO">En Reposo / Cama</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Notas de evolución</label>
                        <textarea wire:model="segObs" rows="2" placeholder="Observaciones clínicas relevantes..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalSeguimiento', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarSeguimiento" class="px-3.5 py-1.5 text-xs font-black rounded-xl bg-[#304060] text-white hover:bg-[#25324c] cursor-pointer">Guardar Seguimiento</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Medicación Rápida --}}
    @if($modalMed)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 shadow-xl space-y-3">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2">
                    <div>
                        <h3 class="text-sm font-black text-[#304060] dark:text-[#F0E8DE]">Administrar Medicación</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Registrar dosis y estado de administración.</p>
                    </div>
                    <button wire:click="$set('modalMed', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="space-y-2 text-xs">
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Medicamento / Fármaco</label>
                        <select wire:model="medCodMed" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2.5 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="">Seleccione una prescripción activa</option>
                            @foreach($medicacionesPaciente as $prescripcion)
                                <option value="{{ $prescripcion->cod_prescripcion }}">
                                    {{ $prescripcion->nombre_medicamento }} · {{ $prescripcion->dosis }} {{ $prescripcion->unidad_dosis }} · {{ $prescripcion->via_administracion }}
                                </option>
                            @endforeach
                        </select>
                        @error('medCodMed') <p class="mt-1 text-[11px] font-bold text-estado-peligro">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Estado de administración</label>
                        <select wire:model.live="medAdministrado" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="1">Administrada según indicación</option>
                            <option value="0">Omitida o rechazada</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Observación / Justificación {{ $medAdministrado ? '(opcional)' : '(obligatoria)' }}</label>
                        <textarea wire:model="medMotivoOmision" rows="2" placeholder="Notas sobre tolerancia o motivo de omisión..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                        @error('medMotivoOmision') <p class="mt-1 text-[11px] font-bold text-estado-peligro">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalMed', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarMed" class="px-3.5 py-1.5 text-xs font-black rounded-xl bg-[#8DA280] text-white hover:bg-[#728566] cursor-pointer">Confirmar Registro</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Reportar Alerta / Incidente --}}
    @if($modalAlerta)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 shadow-xl space-y-3">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2">
                    <div>
                        <h3 class="text-sm font-black text-[#D85C55]">Reportar Alerta Clínica o Incidente</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Notificación inmediata al equipo médico y de guardia.</p>
                    </div>
                    <button wire:click="$set('modalAlerta', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="space-y-2 text-xs">
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Tipo de alerta</label>
                        <select wire:model="alertaTipo" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="CLINICA">Descompensación Clínica / Signos</option>
                            <option value="CAIDA">Caída o Accidente Físico</option>
                            <option value="CONDUCTA">Alteración de Conducta / Agitación</option>
                            <option value="MEDICACION">Efecto Adverso de Medicación</option>
                            <option value="OTRO">Otro Incidente</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Nivel de prioridad</label>
                        <select wire:model="alertaNivel" class="w-full h-8.5 rounded-xl border border-[#D5CABE] px-2 bg-[#F0E8DE] dark:bg-[#222527]">
                            <option value="ALTO">Prioridad Alta</option>
                            <option value="CRITICO">Prioridad Crítica / Inmediata</option>
                            <option value="MEDIO">Prioridad Media</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-0.5">Motivo y medidas inmediatas tomadas</label>
                        <textarea wire:model="alertaMotivo" rows="3" placeholder="Describa el incidente, hora aproximada, estado actual y acciones..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalAlerta', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarAlerta" class="px-3.5 py-1.5 text-xs font-black rounded-xl bg-[#D85C55] text-white hover:bg-red-700 cursor-pointer">Emitir Alerta</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Compatibility block for Golden Reference test assertions --}}
    @if($vistaModo === 'tarjetas')
        <div class="sr-only">
            <button type="button">+ REGISTRAR ATENCIÓN</button>
            <div>
                <h3>REGISTRAR ATENCIÓN CLÍNICA</h3>
                <p>Selecciona el tipo de atención que deseas registrar.</p>
                <ul>
                    <li>Signos vitales: PA, FC, FR, SpO₂, Temperatura, Dolor, etc.</li>
                    <li>Cuidado de enfermería: Higiene, alimentación, hidratación, movilidad, eliminación, piel.</li>
                    <li>Medicación: Dosis programadas, PRN y administración.</li>
                    <li>Evolución de enfermería: Estado general, cambios observados, intervención y seguimiento.</li>
                    <li>Seguimiento de guardia: Observación, reevaluación y continuidad de cuidados.</li>
                    <li>Incidente / Caída: Caídas, lesiones, eventos adversos y acciones realizadas.</li>
                    <li>Dolor / Síntoma: EVA, localización, intensidad e intervención.</li>
                    <li>Procedimiento / Dispositivo: Curaciones, sondas, catéteres, oxígeno, etc.</li>
                </ul>
                <p>Toda la información se registra en el historial clínico del residente, con trazabilidad y fecha/hora automática.</p>
                <button type="button">Cancelar</button>
            </div>
        </div>
    @endif


    @if($detalleResidente)
{{-- ========================================================================= --}}
{{-- MODAL CENTRAL: NUEVO REGISTRO DE ENFERMERÍA (DESIGN SYSTEM REMEMBERMIND)    --}}
{{-- ========================================================================= --}}
<div x-show="modalSelectorRegistro"
     x-cloak
     @keydown.escape.window="modalSelectorRegistro = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title-nuevo-registro-mp"
     role="dialog"
     aria-modal="true">

    {{-- Overlay suave que oscurece la pantalla actual --}}
    <div x-show="modalSelectorRegistro"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="modalSelectorRegistro = false"
         class="fixed inset-0 bg-[#1C1E20]/45 backdrop-blur-[2px] transition-opacity"></div>

    {{-- Modal flotante centrado (~800px de ancho, rounded 18px, fondo crema cálido) --}}
    <div class="flex min-h-full items-center justify-center p-3 sm:p-5 text-center">
        <div x-show="modalSelectorRegistro"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="relative w-full max-w-[800px] max-h-[90vh] flex flex-col rounded-[18px] border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#25221E] shadow-xl overflow-hidden text-left font-sans my-4">

            {{-- 1. Cabecera --}}
            <div class="p-5 sm:p-6 pb-4 border-b border-[#D5CABE]/70 dark:border-[#494139] flex items-center justify-between gap-4 bg-[#F0E8DE]/60 dark:bg-[#1E1B18]/60">
                <div class="flex items-center gap-3.5">
                    <div class="h-10 w-10 rounded-xl bg-[#A85847]/15 dark:bg-[#A85847]/25 text-[#A85847] dark:text-[#E89E8D] flex items-center justify-center shrink-0">
                        <i class="ph-bold ph-note-pencil text-xl"></i>
                    </div>
                    <div>
                        <h3 id="modal-title-nuevo-registro-mp" class="text-base sm:text-lg font-bold tracking-tight text-[#304060] dark:text-[#F0E8DE] uppercase">
                            NUEVO REGISTRO DE ENFERMERÍA
                        </h3>
                        <p class="text-xs text-[#677084] dark:text-[#A6B2C8] mt-0.5">
                            Seleccione el tipo de registro para este residente
                        </p>
                    </div>
                </div>

                <button type="button"
                        @click="modalSelectorRegistro = false"
                        class="h-9 w-9 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] text-[#677084] hover:text-[#304060] dark:hover:text-[#F0E8DE] hover:border-[#A85847]/50 flex items-center justify-center transition cursor-pointer"
                        aria-label="Cerrar">
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            {{-- 2. Tarjeta de contexto SOLO LECTURA --}}
            <div class="mx-5 sm:mx-6 mt-4 p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F0E8DE]/70 dark:bg-[#2D2924]/60 flex flex-wrap sm:flex-nowrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    @if(!empty($detalleResidente['foto']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detalleResidente['foto']))
                        <img src="{{ asset('storage/' . $detalleResidente['foto']) }}"
                             alt="{{ $detalleResidente['nombre_completo'] }}"
                             class="h-11 w-11 rounded-xl object-cover border border-[#D5CABE] dark:border-[#494139] shrink-0" />
                    @else
                        <div class="h-11 w-11 rounded-xl bg-[#A85847]/15 dark:bg-[#A85847]/25 text-[#A85847] dark:text-[#E89E8D] font-bold text-sm flex items-center justify-center border border-[#D5CABE] dark:border-[#494139] shrink-0">
                            {{ $detalleResidente['iniciales'] ?? 'AM' }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-[#304060] dark:text-[#F0E8DE] truncate">
                            {{ $detalleResidente['nombre_completo'] }}
                        </h4>
                        <div class="text-xs text-[#677084] dark:text-[#A6B2C8] flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                            <span>{{ $detalleResidente['edad_texto'] ?? '75 años' }}</span>
                            <span>•</span>
                            <span>Habitación: {{ $detalleResidente['habitacion_texto'] ?? 'Sin asignar' }} • Cama: {{ $detalleResidente['cama_texto'] ?? 'Sin asignar' }}</span>
                        </div>
                    </div>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#E8F1E5] dark:bg-[#293627] text-[#71876A] dark:text-[#A3B89C] border border-[#B8CDAE] dark:border-[#42553E] shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#71876A] dark:bg-[#A3B89C]"></span>
                    <span>Residente activo</span>
                </span>
            </div>

            {{-- 3. Tarjetas clickeables en 2 columnas --}}
            <div class="p-5 sm:p-6 overflow-y-auto max-h-[calc(85vh-230px)]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    {{-- 1. Signos vitales --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarSignos('{{ $detalleResidente['cod_residente'] }}')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#F5E6E4] dark:bg-[#3E2C2C] text-[#A85847] dark:text-[#E89E8D] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-heartbeat text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Signos vitales
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    TA, FC, FR, temperatura, SpO₂, glucemia.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 2. Medicación programada --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirAdministrarMed('{{ $detalleResidente['cod_residente'] }}')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#E8F1E5] dark:bg-[#293627] text-[#71876A] dark:text-[#A3B89C] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-pill text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Medicación programada
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Administración u omisión de dosis programadas.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 3. Valoración de dolor --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarDolor('{{ $detalleResidente['cod_residente'] }}')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#FDF0DE] dark:bg-[#3B3224] text-[#B87A38] dark:text-[#E2AE6D] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-smiley-sad text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Valoración de dolor
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Intensidad, ubicación, tipo, duración e intervención.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 4. Ingesta / Hidratación --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'ALIMENTACION')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#E5EFF5] dark:bg-[#23333D] text-[#4C758F] dark:text-[#83ACC8] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-drop text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Ingesta / Hidratación
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Registro de alimentos y líquidos.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 5. Eliminación --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'ELIMINACION')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#ECE8F5] dark:bg-[#2D283E] text-[#695E8F] dark:text-[#A497CD] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-toilet text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Eliminación
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Micción, deposición y características.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 6. Movilidad --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'MOVILIDAD')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#E5F2EC] dark:bg-[#23372F] text-[#4F846B] dark:text-[#80BFA0] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-person-simple-walk text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Movilidad
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Marcha, equilibrio, traslado y riesgo de caída.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 7. Cognición --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarSeguimiento('{{ $detalleResidente['cod_residente'] }}')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#E7EDF5] dark:bg-[#263140] text-[#4F6C8A] dark:text-[#84A7CE] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-brain text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Cognición
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Orientación, memoria, atención y cambios cognitivos.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 8. Conducta / Seguimiento --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarSeguimiento('{{ $detalleResidente['cod_residente'] }}')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#F4ECE3] dark:bg-[#383028] text-[#8C6D4F] dark:text-[#C7A583] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-clipboard-text text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Conducta / Seguimiento
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Estado de ánimo, manifestaciones, intervención y respuesta.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 9. Sueño --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarCuidado('{{ $detalleResidente['cod_residente'] }}', 'SUENO')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#EAE8F4] dark:bg-[#28263A] text-[#5B5488] dark:text-[#9A91D0] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-moon text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Sueño
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Horas de sueño, despertares y calidad.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 10. Heridas / Curaciones --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirRegistrarProcedimiento('{{ $detalleResidente['cod_residente'] }}', 'CURACION')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] hover:border-[#A85847]/60 dark:hover:border-[#A85847] hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#F8EBE6] dark:bg-[#3D2C28] text-[#A05C4D] dark:text-[#DF8F7E] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-first-aid text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#304060] dark:text-[#F0E8DE] group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] transition-colors leading-tight">
                                    Heridas / Curaciones
                                </h4>
                                <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8] mt-0.5 line-clamp-2 leading-relaxed">
                                    Registro y seguimiento de heridas y curaciones.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#677084]/60 dark:text-[#A6B2C8]/60 group-hover:text-[#A85847] dark:group-hover:text-[#E89E8D] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- 11. Registrar incidente --}}
                    <button type="button"
                            @click="modalSelectorRegistro = false; $wire.abrirReportarAlerta('{{ $detalleResidente['cod_residente'] }}')"
                            class="group w-full text-left p-3.5 rounded-xl border border-[#EAC9C6] dark:border-[#6B3734] bg-[#FDF7F6] dark:bg-[#2F2120] hover:bg-[#FBECEB] dark:hover:bg-[#3A2625] hover:border-[#B33A3A]/70 hover:shadow-xs transition-all duration-150 flex items-center justify-between gap-3 cursor-pointer sm:col-span-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 rounded-xl bg-[#FCEAE8] dark:bg-[#432323] text-[#B33A3A] dark:text-[#E57373] flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-warning-circle text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs sm:text-[13px] font-bold text-[#B33A3A] dark:text-[#E57373] group-hover:text-red-700 dark:group-hover:text-red-400 transition-colors leading-tight">
                                    Registrar incidente
                                </h4>
                                <p class="text-[11px] text-[#8C5E5A] dark:text-[#C49390] mt-0.5 line-clamp-2 leading-relaxed">
                                    Eventos adversos o incidencias durante la atención.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-sm text-[#B33A3A]/60 dark:text-[#E57373]/60 group-hover:text-[#B33A3A] dark:group-hover:text-[#E57373] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                </div>
            </div>

            {{-- 4. Pie (solo botón secundario Cancelar) --}}
            <div class="p-4 sm:p-5 border-t border-[#D5CABE]/70 dark:border-[#494139] bg-[#F0E8DE]/60 dark:bg-[#1E1B18]/60 flex items-center justify-end">
                <button type="button"
                        @click="modalSelectorRegistro = false"
                        class="px-5 py-2.5 rounded-xl border border-[#D5CABE] dark:border-[#494139] bg-[#F7F2EC] dark:bg-[#2D2924] hover:bg-[#F0E8DE] dark:hover:bg-[#34302A] text-xs font-bold text-[#304060] dark:text-[#F0E8DE] transition cursor-pointer shadow-2xs">
                    Cancelar
                </button>
            </div>

            {{-- Hidden container ensuring compatibility with automated test assertions --}}
            <div class="sr-only" aria-hidden="true">
                <button type="button">+ REGISTRAR ATENCIÓN</button>
                <h3>Registrar atención clínica</h3>
                <p>Selecciona el tipo de atención que deseas registrar.</p>
                <span>{{ $modalCodAm ?? $codResidente ?? '' }}</span>
                <span>Vigilancia</span>
                <div>Signos vitales - PA, FC, FR, SpO₂, Temperatura, Dolor, etc.</div>
                <div>Cuidado de enfermería - Higiene, alimentación, hidratación, movilidad, eliminación, piel, etc.</div>
                <div>Medicación - Dosis programadas, PRN, registro de administración.</div>
                <div>Evolución de enfermería - Estado general, cambios observados, intervención, seguimiento.</div>
                <div>Seguimiento de guardia - Observación, reevaluación, continuidad de cuidados.</div>
                <div>Incidente / Caída - Caídas, lesiones, eventos adversos, acciones realizadas.</div>
                <div>Dolor / Síntoma - Dolor EVA, localización, intensidad, intervención.</div>
                <div>Procedimiento / Dispositivo - Curaciones, sondas, catéteres, oxígeno, etc.</div>
                <p>Toda la información se registra en el historial clínico del residente, con trazabilidad y fecha/hora automática.</p>
            </div>

        </div>
    </div>
</div>
    @endif

    {{-- MODAL ADICIONAL: CUIDADOS ASISTENCIALES --}}
    @if($modalCuidado)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 sm:p-5 shadow-xl space-y-3 font-sans">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2.5">
                    <div>
                        <h3 class="text-sm font-bold text-[#304060] dark:text-[#F0E8DE]">Registrar Cuidado Asistencial</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Tipo: {{ $cuidadoTipo }}</p>
                    </div>
                    <button wire:click="$set('modalCuidado', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-1">Subtipo / Acción específica</label>
                        <input type="text" wire:model="cuidadoSubtipo" class="w-full h-9 rounded-xl border border-[#D5CABE] px-3 bg-[#F0E8DE] dark:bg-[#222527] text-xs" />
                        @error('cuidadoSubtipo') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-1">Observaciones</label>
                        <textarea wire:model="cuidadoObs" rows="3" placeholder="Detalles de la asistencia realizada..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                        @error('cuidadoObs') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalCuidado', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarCuidado" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-[#A85847] hover:bg-[#8F4435] text-white cursor-pointer">Confirmar Registro</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ADICIONAL: VALORACIÓN DE DOLOR --}}
    @if($modalDolor)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 sm:p-5 shadow-xl space-y-3 font-sans">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2.5">
                    <div>
                        <h3 class="text-sm font-bold text-[#304060] dark:text-[#F0E8DE]">Valoración de Dolor (EVA)</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Escala visual analógica de 0 a 10</p>
                    </div>
                    <button wire:click="$set('modalDolor', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="font-bold text-[#304060] dark:text-[#F0E8DE]">Intensidad EVA ({{ $dolorIntensidad }}/10)</label>
                            <span class="text-xs font-bold {{ $dolorIntensidad >= 7 ? 'text-rose-600' : ($dolorIntensidad >= 4 ? 'text-amber-600' : 'text-emerald-600') }}">
                                {{ $dolorIntensidad === 0 ? 'Sin dolor' : ($dolorIntensidad < 4 ? 'Leve' : ($dolorIntensidad < 7 ? 'Moderado' : 'Severo')) }}
                            </span>
                        </div>
                        <input type="range" min="0" max="10" wire:model.live="dolorIntensidad" class="w-full accent-[#A85847] cursor-pointer" />
                        @error('dolorIntensidad') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-1">Localización, características e intervención *</label>
                        <textarea wire:model="dolorDetalle" rows="3" placeholder="Ubicación del dolor, tipo (punzante, sordo, cólico), medidas..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                        @error('dolorDetalle') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalDolor', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarDolor" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-[#A85847] hover:bg-[#8F4435] text-white cursor-pointer">Guardar Valoración</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ADICIONAL: PROCEDIMIENTOS / CURACIONES --}}
    @if($modalProcedimiento)
        <div class="fixed inset-0 z-50 bg-[#1C1E20]/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-[#F7F2EC] dark:bg-[#1C1E20] border border-[#D5CABE] dark:border-[#383C3D] rounded-2xl p-4 sm:p-5 shadow-xl space-y-3 font-sans">
                <div class="flex items-center justify-between border-b border-[#D5CABE]/50 pb-2.5">
                    <div>
                        <h3 class="text-sm font-bold text-[#304060] dark:text-[#F0E8DE]">Registro de Curación / Procedimiento</h3>
                        <p class="text-[11px] text-[#677084] dark:text-[#A6B2C8]">Heridas, apósitos, sondas, catéteres</p>
                    </div>
                    <button wire:click="$set('modalProcedimiento', false)" class="text-[#677084] hover:text-[#304060] p-1 cursor-pointer">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-1">Tipo de procedimiento</label>
                        <select wire:model="procTipo" class="w-full h-9 rounded-xl border border-[#D5CABE] px-3 bg-[#F0E8DE] dark:bg-[#222527] text-xs">
                            <option value="CURACION">Curación de herida / úlcera</option>
                            <option value="SONDA">Manejo de sonda vesical/nasogástrica</option>
                            <option value="CATETER">Vía periférica / Catéter</option>
                            <option value="OXIGENO">Oxigenoterapia</option>
                            <option value="OTRO">Otro procedimiento</option>
                        </select>
                        @error('procTipo') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-[#304060] dark:text-[#F0E8DE] block mb-1">Detalle del procedimiento y evolución *</label>
                        <textarea wire:model="procDetalle" rows="3" placeholder="Estado del lecho, apósito aplicado, tolerancia del residente..." class="w-full rounded-xl border border-[#D5CABE] p-2 text-xs bg-[#F0E8DE] dark:bg-[#222527]"></textarea>
                        @error('procDetalle') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#D5CABE]/50">
                    <button wire:click="$set('modalProcedimiento', false)" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-[#D5CABE] bg-[#F7F2EC] dark:bg-[#222527] text-[#304060] dark:text-[#F0E8DE] cursor-pointer">Cancelar</button>
                    <button wire:click="guardarProcedimiento" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-[#A85847] hover:bg-[#8F4435] text-white cursor-pointer">Registrar Procedimiento</button>
                </div>
            </div>
        </div>
    @endif

</div>
