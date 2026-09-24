<div class="space-y-6 font-sans text-[#304060] dark:text-[#EAE6E1]" style="font-family: 'Outfit', sans-serif;">

    {{-- CABECERA CLÍNICA INSTITUCIONAL --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] p-5 sm:p-6 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#A35A44]/15 text-[#A35A44] dark:bg-[#A35A44]/25">
                    <i class="ph-bold ph-warning-octagon text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[#304060] dark:text-[#F3EAE1]">
                        Incidentes
                    </h1>
                    <p class="text-xs font-medium text-[#677084] dark:text-[#B5AAA0]">
                        Registro y seguimiento de eventos relacionados con la atención del residente
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button wire:click="abrirModalRegistro"
                class="inline-flex items-center gap-2 rounded-xl bg-[#A35A44] hover:bg-[#884A39] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-[0.98]">
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>+ Registrar incidencia</span>
            </button>
        </div>
    </div>

    {{-- NOTIFICACIONES Y MENSAJES FLASH --}}
    @if(session()->has('mensaje'))
        <div class="flex items-center justify-between rounded-xl bg-[#71876A]/15 border border-[#71876A]/40 px-4 py-3 text-xs font-bold text-[#71876A] dark:text-[#95AF8D]">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-check-circle text-base"></i>
                <span>{{ session('mensaje') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-[#71876A] hover:opacity-75">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="flex items-center justify-between rounded-xl bg-[#C85D52]/15 border border-[#C85D52]/40 px-4 py-3 text-xs font-bold text-[#C85D52] dark:text-[#E28379]">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-warning-circle text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-[#C85D52] hover:opacity-75">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
    @endif

    @if(session()->has('info'))
        <div class="flex items-center justify-between rounded-xl bg-[#D2A45E]/15 border border-[#D2A45E]/40 px-4 py-3 text-xs font-bold text-[#8C6B32] dark:text-[#E4BE7F]">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-info text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-[#8C6B32] hover:opacity-75">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
    @endif

    {{-- TABS & FILTRO UNIFICADO ESTILO ALERTAS --}}
    <div class="space-y-3">
        {{-- Barra de Tabs y Acciones Secundarias --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="inline-flex rounded-xl bg-[#DED1C3] dark:bg-[#2C2723] p-1 border border-[#C7B9AA] dark:border-[#423B34]">
                <button wire:click="setTab('listado')"
                    class="flex items-center gap-2 rounded-lg px-4 py-1.5 text-xs font-bold transition {{ $tabActiva === 'listado' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
                    <i class="ph-bold ph-list-dashes text-sm"></i>
                    <span>Listado activo</span>
                </button>
                <button wire:click="setTab('historial')"
                    class="flex items-center gap-2 rounded-lg px-4 py-1.5 text-xs font-bold transition {{ $tabActiva === 'historial' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
                    <i class="ph-bold ph-clock-counter-clockwise text-sm"></i>
                    <span>Historial cerrado</span>
                </button>
            </div>
        </div>

        {{-- BARRA DE FILTROS UNIFICADA FORMATO ALERTAS --}}
        <section class="rounded-2xl bg-[#DED1C3] dark:bg-[#2C2723] border border-[#C7B9AA] dark:border-[#423B34] p-3 text-xs shadow-sm flex flex-col gap-2.5">
            <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
                {{-- Búsqueda textual --}}
                <div class="lg:col-span-3 relative flex items-center">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#677084] dark:text-[#9A9084]">
                        <i class="ph-bold ph-magnifying-glass text-base"></i>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar residente, tipo o descripción..."
                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#26221F] py-2 pl-9 pr-8 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] placeholder-[#677084] dark:placeholder-[#8C8276] focus:border-[#A35A44] focus:outline-none h-[38px]" />
                    @if($search !== '')
                        <button type="button"
                            wire:click="limpiarFiltro('search')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[#677084] hover:text-[#A35A44] cursor-pointer"
                            title="Limpiar búsqueda">
                            <i class="ph-bold ph-x-circle text-base"></i>
                        </button>
                    @endif
                </div>

                {{-- Filtro Tipo --}}
                <div class="lg:col-span-2">
                    <select wire:model.live="filtro_tipo" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFrecuentes as $tf)
                            @if($tf !== 'OTRO')
                                <option value="{{ $tf }}">{{ $tf }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Filtro Gravedad --}}
                <div class="lg:col-span-2">
                    <select wire:model.live="filtro_gravedad" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                        <option value="">Todas las gravedades</option>
                        <option value="BAJA">🟢 Baja</option>
                        <option value="MEDIA">⚡ Media</option>
                        <option value="ALTA">⚠️ Alta</option>
                        <option value="CRITICA">🚨 Crítica</option>
                    </select>
                </div>

                {{-- Filtro Estado --}}
                <div class="lg:col-span-2">
                    <select wire:model.live="filtro_estado" class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]">
                        <option value="">Todos los estados</option>
                        <option value="ABIERTO">Abierto</option>
                        <option value="EN_SEGUIMIENTO">En seguimiento</option>
                        <option value="CERRADO">Cerrado</option>
                        <option value="ANULADO">Anulado</option>
                    </select>
                </div>

                {{-- Fechas Desde / Hasta agrupadas --}}
                <div class="lg:col-span-3 grid grid-cols-2 gap-1.5">
                    <input type="date"
                        wire:model.live="fecha_desde"
                        title="Fecha desde"
                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-2 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]" />
                    <input type="date"
                        wire:model.live="fecha_hasta"
                        title="Fecha hasta"
                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-2 text-xs font-medium text-[#304060] dark:text-[#E8DFD5] focus:border-[#A35A44] focus:outline-none h-[38px]" />
                </div>
            </div>

            {{-- Fila de chips de filtros activos --}}
            @php
                $hasFiltrosActivos = !empty($search) || !empty($filtro_tipo) || !empty($filtro_gravedad) || !empty($filtro_estado) || !empty($fecha_desde) || !empty($fecha_hasta);
            @endphp
            @if($hasFiltrosActivos)
                <div class="w-full flex flex-wrap items-center justify-between gap-2 pt-2.5 border-t border-[#C7B9AA]/60 dark:border-[#423B34] text-xs">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-[#677084] dark:text-[#9A9084] flex items-center gap-1 mr-1">
                            <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                        </span>

                        @if(!empty($search))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] dark:border-[#4E463E] text-[11px] font-semibold text-[#304060] dark:text-[#F3EAE1]">
                                <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                                <button type="button" wire:click="limpiarFiltro('search')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif

                        @if(!empty($filtro_tipo))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#304060]/10 dark:bg-[#F3EAE1]/10 border border-[#C7B9AA] text-[11px] font-bold text-[#304060] dark:text-[#F3EAE1]">
                                <span>Tipo: {{ $filtro_tipo }}</span>
                                <button type="button" wire:click="limpiarFiltro('filtro_tipo')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif

                        @if(!empty($filtro_gravedad))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#C85D52]/15 border border-[#C85D52]/30 text-[11px] font-bold text-[#8C2C22] dark:text-[#FFA399]">
                                <span>Gravedad: {{ $filtro_gravedad }}</span>
                                <button type="button" wire:click="limpiarFiltro('filtro_gravedad')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif

                        @if(!empty($filtro_estado))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#D2A45E]/15 border border-[#D2A45E]/30 text-[11px] font-bold text-[#8C6422] dark:text-[#E2BD7E]">
                                <span>Estado: {{ $filtro_estado }}</span>
                                <button type="button" wire:click="limpiarFiltro('filtro_estado')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif

                        @if(!empty($fecha_desde))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#71876A]/15 border border-[#71876A]/30 text-[11px] font-bold text-[#495B44] dark:text-[#9FB897]">
                                <span>Desde: {{ $fecha_desde }}</span>
                                <button type="button" wire:click="limpiarFiltro('fecha_desde')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif

                        @if(!empty($fecha_hasta))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#71876A]/15 border border-[#71876A]/30 text-[11px] font-bold text-[#495B44] dark:text-[#9FB897]">
                                <span>Hasta: {{ $fecha_hasta }}</span>
                                <button type="button" wire:click="limpiarFiltro('fecha_hasta')" class="hover:text-[#A35A44] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[#304060]/10 dark:bg-[#F3EAE1]/10 text-[#304060] dark:text-[#F3EAE1]">
                            {{ $incidentes instanceof \Illuminate\Pagination\LengthAwarePaginator ? $incidentes->total() : count($incidentes) }} coincidentes
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
    </div>

    {{-- ======================================================== --}}
    {{-- VISTA 1: TAB LISTADO                                     --}}
    {{-- ======================================================== --}}
    @if($tabActiva === 'listado')
        <div class="rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-[#DED1C3] dark:bg-[#2C2723] text-[#677084] dark:text-[#A89F93] font-bold border-b border-[#C7B9AA] dark:border-[#423B34]">
                            <th class="py-3 px-4">FECHA / HORA</th>
                            <th class="py-3 px-4">RESIDENTE</th>
                            <th class="py-3 px-4">TIPO</th>
                            <th class="py-3 px-4">DESCRIPCIÓN</th>
                            <th class="py-3 px-4 text-center">GRAVEDAD</th>
                            <th class="py-3 px-4 text-center">ESTADO</th>
                            <th class="py-3 px-4 text-right">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B9AA]/40 dark:divide-[#423B34]/60 text-[#304060] dark:text-[#E8DFD5]">
                        @forelse($incidentes as $inc)
                            @php
                                $esGraveOUrgente = in_array($inc->gravedad, ['ALTA', 'CRITICA']) || $inc->alertaAsociada?->estado === 'ACTIVA';
                                $esCerrado = $inc->estado === 'CERRADO';
                                $esSeguimiento = $inc->estado === 'EN_SEGUIMIENTO';
                            @endphp
                            <tr class="transition-colors {{ $esGraveOUrgente && !$esCerrado ? 'bg-[#FDF2F0] dark:bg-[#341F20] border-l-4 border-l-[#C85D52]' : 'hover:bg-[#E8DDD1]/50 dark:hover:bg-[#2F2925]/50' }}">
                                {{-- Fecha / Hora --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-[#304060] dark:text-[#F3EAE1]">
                                        {{ \Carbon\Carbon::parse($inc->fecha_hora)->format('d/m/Y') }}
                                    </div>
                                    <div class="text-[11px] font-medium text-[#677084] dark:text-[#A89F93]">
                                        {{ \Carbon\Carbon::parse($inc->fecha_hora)->format('H:i') }} hrs
                                    </div>
                                </td>

                                {{-- Residente --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        @if($esGraveOUrgente && !$esCerrado)
                                            <i class="ph-bold ph-warning text-[#C85D52] text-sm shrink-0" title="Incidencia con gravedad alta o alerta"></i>
                                        @endif
                                        <div>
                                            <div class="font-bold text-[#304060] dark:text-[#F3EAE1]">
                                                {{ $inc->residente ? $inc->residente->nombres . ' ' . $inc->residente->primer_apellido : 'Residente ' . $inc->cod_residente }}
                                            </div>
                                            <div class="text-[11px] text-[#677084] dark:text-[#A89F93]">
                                                {{ $inc->residente?->ubicacion_formateada ?? 'Sin ubicación' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tipo --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold text-[11px] bg-[#DED1C3] dark:bg-[#38312B] text-[#304060] dark:text-[#F3EAE1]">
                                        {{ $inc->tipo_incidente }}
                                    </span>
                                    @if($inc->lugar)
                                        <div class="text-[10px] text-[#677084] dark:text-[#A89F93] mt-0.5">
                                            <i class="ph-bold ph-map-pin text-[10px]"></i> {{ $inc->lugar }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Descripción --}}
                                <td class="py-3.5 px-4 max-w-xs md:max-w-md">
                                    <p class="truncate text-xs text-[#304060] dark:text-[#E8DFD5]" title="{{ $inc->descripcion }}">
                                        {{ $inc->descripcion }}
                                    </p>
                                    @if($inc->medida_inmediata)
                                        <p class="text-[11px] text-[#677084] dark:text-[#A89F93] italic truncate mt-0.5" title="Medida: {{ $inc->medida_inmediata }}">
                                            <span class="font-semibold text-[#884A39] dark:text-[#D58C79]">Medida:</span> {{ $inc->medida_inmediata }}
                                        </p>
                                    @endif
                                </td>

                                {{-- Gravedad --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($inc->gravedad === 'CRITICA')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#C85D52] text-white">
                                            CRÍTICA
                                        </span>
                                    @elseif($inc->gravedad === 'ALTA')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E28379] border border-[#C85D52]/40">
                                            ALTA
                                        </span>
                                    @elseif($inc->gravedad === 'MEDIA')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#D2A45E]/20 text-[#8C6B32] dark:text-[#E4BE7F] border border-[#D2A45E]/40">
                                            MEDIA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-[#677084]/15 text-[#677084] dark:text-[#A89F93]">
                                            BAJA
                                        </span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($inc->estado === 'CERRADO')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#71876A]/20 text-[#71876A] dark:text-[#95AF8D] border border-[#71876A]/40">
                                            ● Cerrado
                                        </span>
                                    @elseif($inc->estado === 'EN_SEGUIMIENTO')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D2A45E]/20 text-[#8C6B32] dark:text-[#E4BE7F] border border-[#D2A45E]/40">
                                            ● En seguimiento
                                        </span>
                                    @elseif($inc->estado === 'ANULADO')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#677084]/20 text-[#677084] dark:text-[#A89F93]">
                                            ✕ Anulado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#A35A44]/15 text-[#A35A44] dark:text-[#D58C79] border border-[#A35A44]/30">
                                            ● Abierto
                                        </span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="relative inline-flex items-center gap-1.5" x-data="{ open: false }">
                                        {{-- Botón Ver --}}
                                        <button wire:click="verIncidente('{{ $inc->cod_incidente }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-[#DED1C3] dark:bg-[#38312B] hover:bg-[#C7B9AA] dark:hover:bg-[#4E463E] px-2.5 py-1.5 text-xs font-bold text-[#304060] dark:text-[#F3EAE1] transition">
                                            <i class="ph-bold ph-eye text-sm"></i> Ver
                                        </button>

                                        {{-- Botón ⋮ Menú contextual --}}
                                        <button @click="open = !open" @click.outside="open = false"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-[#DED1C3] dark:bg-[#38312B] hover:bg-[#C7B9AA] dark:hover:bg-[#4E463E] text-xs font-bold text-[#304060] dark:text-[#F3EAE1] transition">
                                            <i class="ph-bold ph-dots-three-vertical text-base"></i>
                                        </button>

                                        {{-- Dropdown Menú contextual --}}
                                        <div x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            style="display: none;"
                                            class="absolute right-0 top-8 z-30 w-52 rounded-xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-lg py-1 text-left text-xs font-medium">

                                            {{-- Ver residente --}}
                                            <button @click="open = false" wire:click="verIncidente('{{ $inc->cod_incidente }}')"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[#304060] dark:text-[#E8DFD5] hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition">
                                                <i class="ph-bold ph-user text-sm text-[#A35A44]"></i> Ver residente y detalle
                                            </button>

                                            {{-- Actualizar estado --}}
                                            <button @click="open = false" wire:click="abrirModalEstado('{{ $inc->cod_incidente }}')"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[#304060] dark:text-[#E8DFD5] hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition">
                                                <i class="ph-bold ph-arrows-clockwise text-sm text-[#D2A45E]"></i> Actualizar estado
                                            </button>

                                            {{-- Ver alerta asociada si existe --}}
                                            @if($inc->alertaAsociada)
                                                <button @click="open = false" wire:click="verIncidente('{{ $inc->cod_incidente }}')"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-[#C85D52] hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition">
                                                    <i class="ph-bold ph-bell-ringing text-sm"></i> Ver alerta asociada
                                                </button>
                                            @endif

                                            {{-- Solicitar valoración médica --}}
                                            <button @click="open = false" wire:click="solicitarValoracionMedica('{{ $inc->cod_incidente }}')"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[#304060] dark:text-[#E8DFD5] hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition">
                                                <i class="ph-bold ph-first-aid text-sm text-[#884A39]"></i> Solicitar valoración médica
                                            </button>

                                            {{-- Crear derivación --}}
                                            <button @click="open = false" wire:click="abrirModalDerivacion('{{ $inc->cod_incidente }}')"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[#304060] dark:text-[#E8DFD5] hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition">
                                                <i class="ph-bold ph-arrow-square-out text-sm text-[#304060]"></i> Crear derivación
                                            </button>

                                            {{-- Corregir / anular --}}
                                            <button @click="open = false" wire:click="abrirModalEstado('{{ $inc->cod_incidente }}')"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[#677084] dark:text-[#A89F93] hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition border-t border-[#C7B9AA]/40 dark:border-[#423B34]">
                                                <i class="ph-bold ph-prohibit text-sm"></i> Corregir / Anular
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#677084] dark:text-[#A89F93]">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="ph-bold ph-clipboard-text text-3xl text-[#C7B9AA]"></i>
                                        <p class="font-bold text-sm">No se encontraron incidentes registrados</p>
                                        <p class="text-xs">Ajuste los filtros o registre un nuevo incidente asistencial.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($incidentes->hasPages())
                <div class="p-4 bg-[#DED1C3]/60 dark:bg-[#2C2723]/60 border-t border-[#C7B9AA] dark:border-[#423B34]">
                    {{ $incidentes->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- VISTA 2: TAB HISTORIAL                                   --}}
    {{-- ======================================================== --}}
    @if($tabActiva === 'historial')
        <div class="space-y-4">
            <div class="flex items-center justify-between text-xs text-[#677084] dark:text-[#A89F93] px-1">
                <span class="font-semibold">Bitácora clínica autosuficiente (Cronológico, más reciente primero)</span>
                <span>Total de eventos: {{ $incidentes->total() }}</span>
            </div>

            <div class="space-y-3">
                @forelse($incidentes as $inc)
                    @php
                        $esGrave = in_array($inc->gravedad, ['ALTA', 'CRITICA']);
                    @endphp
                    <div class="rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border {{ $esGrave ? 'border-[#C85D52]/60 bg-[#FDF2F0]/80 dark:bg-[#341F20]/80' : 'border-[#C7B9AA] dark:border-[#423B34]' }} p-4 sm:p-5 shadow-sm space-y-3">
                        {{-- Cabecera de la ficha cronológica --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[#C7B9AA]/50 dark:border-[#423B34] pb-3">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Fecha / Hora --}}
                                <div class="inline-flex items-center gap-1.5 rounded-lg bg-[#DED1C3] dark:bg-[#38312B] px-2.5 py-1 text-xs font-bold text-[#304060] dark:text-[#F3EAE1]">
                                    <i class="ph-bold ph-calendar-blank"></i>
                                    {{ \Carbon\Carbon::parse($inc->fecha_hora)->format('d/m/Y H:i') }}
                                </div>

                                {{-- Residente y Habitación --}}
                                <div class="font-bold text-sm text-[#304060] dark:text-[#F3EAE1] ml-1">
                                    {{ $inc->residente ? $inc->residente->nombres . ' ' . $inc->residente->primer_apellido : 'Residente ' . $inc->cod_residente }}
                                </div>
                                <span class="text-xs text-[#677084] dark:text-[#A89F93]">
                                    ({{ $inc->residente?->ubicacion_formateada ?? 'Sin ubicación' }})
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Tipo --}}
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#E4D8CC] dark:bg-[#38312B] text-[#304060] dark:text-[#F3EAE1]">
                                    {{ $inc->tipo_incidente }}
                                </span>

                                {{-- Gravedad --}}
                                @if($inc->gravedad === 'CRITICA')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-[#C85D52] text-white">
                                        CRÍTICA
                                    </span>
                                @elseif($inc->gravedad === 'ALTA')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E28379] border border-[#C85D52]/40">
                                        ALTA
                                    </span>
                                @elseif($inc->gravedad === 'MEDIA')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-[#D2A45E]/20 text-[#8C6B32] dark:text-[#E4BE7F] border border-[#D2A45E]/40">
                                        MEDIA
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-[#677084]/15 text-[#677084] dark:text-[#A89F93]">
                                        BAJA
                                    </span>
                                @endif

                                {{-- Estado --}}
                                @if($inc->estado === 'CERRADO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#71876A]/20 text-[#71876A] dark:text-[#95AF8D] border border-[#71876A]/40">
                                        ● Cerrado
                                    </span>
                                @elseif($inc->estado === 'EN_SEGUIMIENTO')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#D2A45E]/20 text-[#8C6B32] dark:text-[#E4BE7F] border border-[#D2A45E]/40">
                                        ● En seguimiento
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#A35A44]/15 text-[#A35A44] dark:text-[#D58C79] border border-[#A35A44]/30">
                                        ● Abierto
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Contenido central descriptivo (Autosuficiente) --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
                            {{-- Descripción principal --}}
                            <div class="md:col-span-7 space-y-1">
                                <span class="font-bold text-[#677084] dark:text-[#A89F93] block">Descripción del evento:</span>
                                <p class="text-[#304060] dark:text-[#E8DFD5] leading-relaxed bg-[#F3EAE1] dark:bg-[#201D1A] p-2.5 rounded-xl border border-[#C7B9AA]/40 dark:border-[#423B34]">
                                    {{ $inc->descripcion }}
                                </p>
                            </div>

                            {{-- Medida inmediata tomada --}}
                            <div class="md:col-span-5 space-y-1">
                                <span class="font-bold text-[#884A39] dark:text-[#D58C79] block">Medida inmediata realizada:</span>
                                <p class="text-[#304060] dark:text-[#E8DFD5] leading-relaxed bg-[#E4D8CC]/50 dark:bg-[#2A2522] p-2.5 rounded-xl border border-[#C7B9AA]/40 dark:border-[#423B34]">
                                    {{ $inc->medida_inmediata ?: 'Sin medida inmediata especificada.' }}
                                </p>
                            </div>
                        </div>

                        {{-- Bloque de Escalamiento y Profesional --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#C7B9AA]/40 dark:border-[#423B34] text-xs">
                            <div class="flex flex-wrap items-center gap-3">
                                {{-- Valoración Médica --}}
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-[#677084] dark:text-[#A89F93]">Valoración médica:</span>
                                    @if($inc->requiere_medico)
                                        <span class="font-bold text-[#C85D52] bg-[#C85D52]/15 px-2 py-0.5 rounded-md">Sí requerida</span>
                                    @else
                                        <span class="text-[#677084] dark:text-[#A89F93]">No</span>
                                    @endif
                                </div>

                                {{-- Derivación --}}
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-[#677084] dark:text-[#A89F93]">Derivación:</span>
                                    @if($inc->requiere_derivacion)
                                        <span class="font-bold text-[#A35A44] bg-[#A35A44]/15 px-2 py-0.5 rounded-md">Sí requerida</span>
                                    @else
                                        <span class="text-[#677084] dark:text-[#A89F93]">No</span>
                                    @endif
                                </div>

                                {{-- Lugar si existe --}}
                                @if($inc->lugar)
                                    <div class="text-[#677084] dark:text-[#A89F93]">
                                        <i class="ph-bold ph-map-pin"></i> {{ $inc->lugar }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-[11px] text-[#677084] dark:text-[#A89F93]">
                                <span>Registrado por:</span>
                                <span class="font-bold text-[#304060] dark:text-[#F3EAE1]">
                                    {{ $inc->personal?->usuario?->name ?? ($inc->personal ? $inc->personal->nombres . ' ' . $inc->personal->apellido_paterno : $inc->cod_personal) }}
                                </span>
                            </div>
                        </div>

                        {{-- Observación / Trazabilidad si existe --}}
                        @if($inc->observacion)
                            <div class="rounded-xl bg-[#DED1C3]/50 dark:bg-[#201D1A]/50 p-2 text-[11px] text-[#677084] dark:text-[#B5AAA0] border border-[#C7B9AA]/30">
                                <span class="font-bold text-[#304060] dark:text-[#F3EAE1]">Trazabilidad / Observación:</span>
                                <p class="whitespace-pre-line mt-0.5">{{ $inc->observacion }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] p-12 text-center text-[#677084] dark:text-[#A89F93]">
                        <p class="font-bold text-sm">No existen registros en el historial</p>
                    </div>
                @endforelse
            </div>

            @if($incidentes->hasPages())
                <div class="p-4 bg-[#F0E8DE] dark:bg-[#26221F] rounded-2xl border border-[#C7B9AA] dark:border-[#423B34]">
                    {{ $incidentes->links() }}
                </div>
            @endif
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 1: REGISTRAR INCIDENCIA (CENTRADO, OVERLAY SUAVE)  --}}
    {{-- ======================================================== --}}
    @if($modalRegistrar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="relative w-full max-w-3xl max-h-[90vh] flex flex-col rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-2xl overflow-hidden my-auto"
                @click.outside="$wire.cerrarModalRegistro()">

                {{-- Cabecera del Modal --}}
                <div class="flex items-center justify-between border-b border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-bold ph-warning-octagon text-xl text-[#A35A44]"></i>
                        <div>
                            <h2 class="text-base font-black text-[#304060] dark:text-[#F3EAE1]">
                                Registrar incidencia
                            </h2>
                            <p class="text-[11px] font-medium text-[#677084] dark:text-[#A89F93]">
                                Documente objetivamente el evento ocurrido durante el cuidado
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModalRegistro"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-[#677084] hover:bg-[#C7B9AA]/50 dark:hover:bg-[#38312B] transition">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Cuerpo con scroll --}}
                <div class="p-5 sm:p-6 space-y-6 overflow-y-auto flex-1 text-xs text-[#304060] dark:text-[#E8DFD5]">

                    {{-- SECCIÓN 1: RESIDENTE --}}
                    <div class="space-y-3 rounded-xl bg-[#E4D8CC]/50 dark:bg-[#2C2723]/50 p-4 border border-[#C7B9AA]/60 dark:border-[#423B34]">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] flex items-center gap-1.5">
                                <i class="ph-bold ph-user-circle text-sm"></i> Sección 1 — Residente
                            </span>
                            @if($residenteSeleccionado)
                                <button type="button" wire:click="quitarResidente"
                                    class="text-[11px] font-bold text-[#A35A44] hover:underline">
                                    Cambiar residente
                                </button>
                            @endif
                        </div>

                        @if(!$residenteSeleccionado)
                            {{-- Selector / Buscador de Residente --}}
                            <div class="space-y-2">
                                <label class="font-semibold block text-[#677084] dark:text-[#A89F93]">
                                    Buscar / seleccionar residente *
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[#677084]"></i>
                                    <input wire:model.live.debounce.300ms="searchResidente"
                                        type="text"
                                        placeholder="Escriba nombre o cédula para filtrar..."
                                        class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 pl-9 pr-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                </div>

                                {{-- Lista de coincidencias --}}
                                <div class="max-h-40 overflow-y-auto rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] divide-y divide-[#C7B9AA]/40">
                                    @forelse($residentesDisponibles as $res)
                                        <button type="button" wire:click="seleccionarResidente('{{ $res->cod_residente }}')"
                                            class="w-full flex items-center justify-between p-2.5 text-left hover:bg-[#DED1C3] dark:hover:bg-[#38312B] transition">
                                            <div>
                                                <span class="font-bold block text-[#304060] dark:text-[#F3EAE1]">
                                                    {{ $res->nombres }} {{ $res->primer_apellido }} {{ $res->segundo_apellido }}
                                                </span>
                                                <span class="text-[11px] text-[#677084] dark:text-[#A89F93]">
                                                    CI: {{ $res->documento_identidad ?? 'S/D' }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-semibold text-[#884A39] dark:text-[#D58C79]">
                                                {{ $res->ubicacion_formateada }}
                                            </span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-center text-[#677084] dark:text-[#A89F93]">
                                            No se encontraron residentes con ese criterio.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            {{-- Residente Seleccionado (Solo lectura) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#F0E8DE] dark:bg-[#211E1B] p-3 rounded-xl border border-[#C7B9AA]">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[#677084] block">Nombre Completo:</span>
                                    <span class="font-bold text-sm text-[#304060] dark:text-[#F3EAE1]">
                                        {{ $residenteSeleccionado->nombres }} {{ $residenteSeleccionado->primer_apellido }} {{ $residenteSeleccionado->segundo_apellido }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[#677084] block">Habitación / Cama actual:</span>
                                    <span class="font-bold text-xs text-[#884A39] dark:text-[#D58C79]">
                                        {{ $residenteSeleccionado->ubicacion_formateada }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[#677084] block">Profesional autenticado:</span>
                                    <span class="font-semibold text-xs text-[#304060] dark:text-[#F3EAE1]">
                                        {{ auth()->user()->name ?? 'Personal de guardia' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[#677084] block">Contexto de Jornada:</span>
                                    <span class="font-semibold text-xs text-[#71876A] dark:text-[#95AF8D]">
                                        Turno activo registrado
                                    </span>
                                </div>
                            </div>
                        @endif

                        @error('cod_residente')
                            <span class="text-[11px] font-bold text-[#C85D52] block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- SECCIÓN 2: DATOS DEL INCIDENTE --}}
                    <div class="space-y-4">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] flex items-center gap-1.5">
                            <i class="ph-bold ph-notepad text-sm"></i> Sección 2 — Datos del Incidente
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Tipo de incidente --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084] dark:text-[#A89F93]">Tipo de incidente *</label>
                                <select wire:model.live="tipo_incidente"
                                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-semibold text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                    <option value="">Seleccione el tipo...</option>
                                    @foreach($tiposFrecuentes as $tf)
                                        <option value="{{ $tf }}">{{ $tf }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_incidente')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Gravedad --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084] dark:text-[#A89F93]">Gravedad *</label>
                                <select wire:model.live="gravedad"
                                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-semibold text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                    @foreach($gravedadesDisponibles as $keyG => $labelG)
                                        <option value="{{ $keyG }}">{{ $labelG }}</option>
                                    @endforeach
                                </select>
                                @error('gravedad')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Especificar si es "OTRO" --}}
                        @if($tipo_incidente === 'OTRO')
                            <div class="space-y-1 bg-[#F3EAE1] dark:bg-[#201D1A] p-3 rounded-xl border border-[#A35A44]/40">
                                <label class="font-bold text-[#884A39] dark:text-[#D58C79]">Especifique el tipo de incidente * (Máximo 60 caracteres)</label>
                                <input wire:model="tipo_incidente_otro"
                                    type="text"
                                    maxlength="60"
                                    placeholder="Ej: Desorientación súbita en comedor..."
                                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                @error('tipo_incidente_otro')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- Lugar --}}
                            <div class="space-y-1 sm:col-span-1">
                                <label class="font-bold text-[#677084] dark:text-[#A89F93]">Lugar del evento</label>
                                <input wire:model="lugar"
                                    type="text"
                                    maxlength="120"
                                    placeholder="Ej: Pasillo norte, baño, comedor..."
                                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                @error('lugar')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Fecha incidente --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084] dark:text-[#A89F93]">Fecha del incidente *</label>
                                <input wire:model="fecha_incidente"
                                    type="date"
                                    max="{{ date('Y-m-d') }}"
                                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                @error('fecha_incidente')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Hora incidente --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084] dark:text-[#A89F93]">Hora del incidente *</label>
                                <input wire:model="hora_incidente"
                                    type="time"
                                    class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-medium text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none">
                                @error('hora_incidente')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: DESCRIPCIÓN DEL EVENTO --}}
                    <div class="space-y-4">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] flex items-center gap-1.5">
                            <i class="ph-bold ph-chat-text text-sm"></i> Sección 3 — Descripción del Evento
                        </span>

                        <div class="space-y-1">
                            <label class="font-bold text-[#677084] dark:text-[#A89F93]">
                                Descripción objetiva de lo ocurrido *
                            </label>
                            <textarea wire:model="descripcion"
                                rows="3"
                                placeholder="Relate con precisión objetiva los hechos observados, estado del paciente y circunstancias..."
                                class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] p-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none"></textarea>
                            @error('descripcion')
                                <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="font-bold text-[#677084] dark:text-[#A89F93]">
                                Medida inmediata realizada
                            </label>
                            <textarea wire:model="medida_inmediata"
                                rows="2"
                                placeholder="Indique qué acción realizó inmediatamente (ej: toma de constantes, curación, reposo en cama, aviso a supervisión)..."
                                class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] p-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none"></textarea>
                            @error('medida_inmediata')
                                <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- SECCIÓN 4: ESCALAMIENTO --}}
                    <div class="space-y-4 rounded-xl bg-[#E4D8CC]/50 dark:bg-[#2C2723]/50 p-4 border border-[#C7B9AA]/60 dark:border-[#423B34]">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] flex items-center gap-1.5">
                            <i class="ph-bold ph-shield-warning text-sm"></i> Sección 4 — Escalamiento
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- ¿Requiere valoración médica? --}}
                            <div class="space-y-2">
                                <label class="font-bold text-[#304060] dark:text-[#F3EAE1] block">
                                    ¿Requiere valoración médica? *
                                </label>
                                <div class="flex items-center gap-4">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_medico" :value="false" class="text-[#A35A44] focus:ring-[#A35A44]">
                                        <span class="font-medium">No</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_medico" :value="true" class="text-[#A35A44] focus:ring-[#A35A44]">
                                        <span class="font-bold text-[#C85D52]">Sí</span>
                                    </label>
                                </div>
                            </div>

                            {{-- ¿Requiere derivación? --}}
                            <div class="space-y-2">
                                <label class="font-bold text-[#304060] dark:text-[#F3EAE1] block">
                                    ¿Requiere derivación a otra área/especialidad? *
                                </label>
                                <div class="flex items-center gap-4">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_derivacion" :value="false" class="text-[#A35A44] focus:ring-[#A35A44]">
                                        <span class="font-medium">No</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_derivacion" :value="true" class="text-[#A35A44] focus:ring-[#A35A44]">
                                        <span class="font-bold text-[#A35A44]">Sí</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Banner condicional suave si requiere seguimiento --}}
                        @if(in_array($gravedad, ['ALTA', 'CRITICA']) || $requiere_medico || $requiere_derivacion)
                            <div class="rounded-xl bg-[#FDF2F0] dark:bg-[#341F20] border border-[#C85D52]/40 p-3 flex items-start gap-2.5 text-[#C85D52] dark:text-[#E28379]">
                                <i class="ph-bold ph-warning-circle text-lg shrink-0 mt-0.5"></i>
                                <div>
                                    <div class="font-bold text-xs">Requiere seguimiento adicional</div>
                                    <div class="text-[11px] font-medium opacity-90">
                                        Después de registrar se habilitarán las acciones correspondientes para emitir la alerta médica o derivación formal.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- SECCIÓN 5: OBSERVACIONES --}}
                    <div class="space-y-3">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] flex items-center gap-1.5">
                            <i class="ph-bold ph-info text-sm"></i> Sección 5 — Observaciones
                        </span>

                        <div class="space-y-1">
                            <label class="font-semibold text-[#677084] dark:text-[#A89F93]">
                                Observación complementaria (opcional)
                            </label>
                            <textarea wire:model="observacion"
                                rows="2"
                                placeholder="Notas adicionales, antecedentes inmediatos relevantes..."
                                class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] p-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:border-[#A35A44] focus:outline-none"></textarea>
                        </div>
                        <p class="text-[10px] text-[#677084] dark:text-[#A89F93] italic">
                            * El estado inicial del incidente se registrará como ABIERTO para seguimiento asistencial.
                        </p>
                    </div>

                    @error('error_general')
                        <div class="rounded-xl bg-[#C85D52]/15 border border-[#C85D52] p-3 text-xs font-bold text-[#C85D52]">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Footer del Modal --}}
                <div class="flex items-center justify-end gap-3 border-t border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-4">
                    <button type="button" wire:click="cerrarModalRegistro"
                        class="rounded-xl border border-[#C7B9AA] dark:border-[#4E463E] bg-[#F0E8DE] dark:bg-[#211E1B] px-4 py-2.5 text-xs font-bold text-[#677084] dark:text-[#A89F93] hover:text-[#304060] dark:hover:text-[#F3EAE1] transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="registrarIncidente" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#A35A44] hover:bg-[#884A39] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-[0.98] disabled:opacity-50">
                        <span wire:loading.remove wire:target="registrarIncidente">Registrar incidencia</span>
                        <span wire:loading wire:target="registrarIncidente">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 2: VER INCIDENTE (LECTURA COMPLETA + CONTACTOS)    --}}
    {{-- ======================================================== --}}
    @if($modalVer && $incidenteDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="relative w-full max-w-3xl max-h-[90vh] flex flex-col rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-2xl overflow-hidden my-auto"
                @click.outside="$wire.cerrarModalVer()">

                {{-- Cabecera --}}
                <div class="flex items-center justify-between border-b border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-bold ph-clipboard-text text-xl text-[#304060] dark:text-[#F3EAE1]"></i>
                        <div>
                            <h2 class="text-base font-black text-[#304060] dark:text-[#F3EAE1]">
                                Detalle de la Incidencia: {{ $incidenteDetalle->cod_incidente }}
                            </h2>
                            <p class="text-[11px] font-medium text-[#677084] dark:text-[#A89F93]">
                                Registro clínico trazable de enfermería
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModalVer"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-[#677084] hover:bg-[#C7B9AA]/50 dark:hover:bg-[#38312B] transition">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Contenido con scroll --}}
                <div class="p-5 sm:p-6 space-y-5 overflow-y-auto flex-1 text-xs text-[#304060] dark:text-[#E8DFD5]">

                    {{-- Bloque Alerta Activa si existe --}}
                    @if($alertaVinculada)
                        <div class="rounded-xl bg-[#FDF2F0] dark:bg-[#341F20] border border-[#C85D52]/60 p-4 space-y-1.5 text-[#C85D52] dark:text-[#E28379]">
                            <div class="flex items-center justify-between">
                                <span class="font-black text-xs flex items-center gap-1.5">
                                    <i class="ph-bold ph-bell-ringing"></i> Alerta Activa Vinculada ({{ $alertaVinculada->cod_alerta }})
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-[#C85D52] text-white">
                                    Prioridad {{ $alertaVinculada->prioridad }}
                                </span>
                            </div>
                            <p class="text-xs font-semibold">{{ $alertaVinculada->titulo }}</p>
                            <p class="text-[11px] opacity-90">{{ $alertaVinculada->descripcion }}</p>
                        </div>
                    @endif

                    {{-- Ficha Principal del Residente y Ubicación --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#E4D8CC]/50 dark:bg-[#2C2723]/50 p-4 rounded-xl border border-[#C7B9AA]">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Residente:</span>
                            <span class="font-bold text-sm text-[#304060] dark:text-[#F3EAE1]">
                                {{ $incidenteDetalle->residente?->nombres }} {{ $incidenteDetalle->residente?->primer_apellido }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Ubicación actual:</span>
                            <span class="font-bold text-xs text-[#884A39] dark:text-[#D58C79]">
                                {{ $incidenteDetalle->residente?->ubicacion_formateada ?? 'Sin ubicación' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Fecha y Hora del Suceso:</span>
                            <span class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1]">
                                {{ \Carbon\Carbon::parse($incidenteDetalle->fecha_hora)->format('d/m/Y H:i') }} hrs
                            </span>
                        </div>
                    </div>

                    {{-- Clasificación: Tipo, Gravedad, Estado, Lugar --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#F0E8DE] dark:bg-[#211E1B] p-3 rounded-xl border border-[#C7B9AA]/50">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Tipo de Incidente:</span>
                            <span class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1]">
                                {{ $incidenteDetalle->tipo_incidente }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Gravedad:</span>
                            <span class="font-bold text-xs {{ in_array($incidenteDetalle->gravedad, ['ALTA', 'CRITICA']) ? 'text-[#C85D52]' : 'text-[#8C6B32]' }}">
                                {{ $incidenteDetalle->gravedad }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Estado:</span>
                            <span class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1]">
                                {{ $incidenteDetalle->estado }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Lugar:</span>
                            <span class="font-semibold text-xs text-[#304060] dark:text-[#F3EAE1]">
                                {{ $incidenteDetalle->lugar ?: 'No especificado' }}
                            </span>
                        </div>
                    </div>

                    {{-- Descripción Completa --}}
                    <div class="space-y-1">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] block">
                            Descripción Completa del Evento:
                        </span>
                        <div class="rounded-xl bg-[#F3EAE1] dark:bg-[#201D1A] p-3.5 border border-[#C7B9AA]/40 text-xs leading-relaxed text-[#304060] dark:text-[#E8DFD5] whitespace-pre-line">
                            {{ $incidenteDetalle->descripcion }}
                        </div>
                    </div>

                    {{-- Medida Inmediata Realizada --}}
                    <div class="space-y-1">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#884A39] dark:text-[#D58C79] block">
                            Medida Inmediata Realizada:
                        </span>
                        <div class="rounded-xl bg-[#E4D8CC]/50 dark:bg-[#2A2522] p-3.5 border border-[#C7B9AA]/40 text-xs leading-relaxed text-[#304060] dark:text-[#E8DFD5] whitespace-pre-line">
                            {{ $incidenteDetalle->medida_inmediata ?: 'Sin medida inmediata registrada.' }}
                        </div>
                    </div>

                    {{-- Escalamientos requeridos --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#E4D8CC]/40 dark:bg-[#2C2723]/40 p-3 rounded-xl border border-[#C7B9AA]/50">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-[#677084] dark:text-[#A89F93]">Valoración médica requerida:</span>
                            <span class="font-bold text-xs {{ $incidenteDetalle->requiere_medico ? 'text-[#C85D52]' : 'text-[#71876A]' }}">
                                {{ $incidenteDetalle->requiere_medico ? 'SÍ' : 'NO' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-[#677084] dark:text-[#A89F93]">Derivación requerida:</span>
                            <span class="font-bold text-xs {{ $incidenteDetalle->requiere_derivacion ? 'text-[#A35A44]' : 'text-[#71876A]' }}">
                                {{ $incidenteDetalle->requiere_derivacion ? 'SÍ' : 'NO' }}
                            </span>
                        </div>
                    </div>

                    {{-- Trazabilidad / Observación --}}
                    @if($incidenteDetalle->observacion)
                        <div class="space-y-1">
                            <span class="font-bold text-xs uppercase tracking-wider text-[#677084] dark:text-[#A89F93] block">
                                Historial de Trazabilidad y Observaciones:
                            </span>
                            <div class="rounded-xl bg-[#DED1C3]/60 dark:bg-[#201D1A] p-3 border border-[#C7B9AA]/40 text-xs text-[#304060] dark:text-[#E8DFD5] whitespace-pre-line">
                                {{ $incidenteDetalle->observacion }}
                            </div>
                        </div>
                    @endif

                    {{-- CONTACTOS DE EMERGENCIA DEL RESIDENTE --}}
                    <div class="space-y-2 pt-2 border-t border-[#C7B9AA]/50 dark:border-[#423B34]">
                        <span class="font-bold text-xs uppercase tracking-wider text-[#304060] dark:text-[#F3EAE1] flex items-center gap-1.5">
                            <i class="ph-bold ph-phone-call text-sm text-[#A35A44]"></i> Contactos de emergencia y responsables autorizados
                        </span>

                        @if(!empty($contactosEmergencia))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($contactosEmergencia as $ce)
                                    <div class="rounded-xl bg-[#F0E8DE] dark:bg-[#211E1B] p-3 border border-[#C7B9AA]/60 flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1] flex items-center gap-1.5">
                                                {{ $ce['nombre'] }}
                                                @if($ce['es_emergencia'])
                                                    <span class="text-[9px] font-black uppercase bg-[#C85D52]/20 text-[#C85D52] px-1.5 py-0.2 rounded">Emergencia</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-[#677084] dark:text-[#A89F93]">
                                                {{ $ce['parentesco'] }} · {{ $ce['telefono'] }}
                                            </div>
                                        </div>
                                        @if($ce['telefono'] && $ce['telefono'] !== 'Sin teléfono')
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $ce['telefono']) }}"
                                                class="inline-flex items-center gap-1 rounded-lg bg-[#71876A] hover:bg-[#5C7056] text-white px-2.5 py-1 text-xs font-bold transition">
                                                <i class="ph-bold ph-phone text-xs"></i> Llamar
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl bg-[#E4D8CC]/40 p-3 text-[11px] text-[#677084] dark:text-[#A89F93]">
                                No hay contactos de emergencia registrados o autorizados para este residente.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between border-t border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-3">
                    <div class="text-[11px] text-[#677084] dark:text-[#A89F93]">
                        Registrado por: <strong class="text-[#304060] dark:text-[#F3EAE1]">{{ $incidenteDetalle->personal?->usuario?->name ?? $incidenteDetalle->cod_personal }}</strong>
                    </div>
                    <button wire:click="cerrarModalVer"
                        class="rounded-xl bg-[#A35A44] hover:bg-[#884A39] px-4 py-2 text-xs font-bold text-white transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 3: ACTUALIZAR ESTADO (CAMBIO TRAZABLE)             --}}
    {{-- ======================================================== --}}
    @if($modalEstado)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalEstado()">

                <div class="flex items-center justify-between border-b border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-4">
                    <h3 class="text-sm font-black text-[#304060] dark:text-[#F3EAE1]">
                        Actualizar estado del incidente
                    </h3>
                    <button wire:click="cerrarModalEstado" class="text-[#677084] hover:text-[#304060]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-[#677084] dark:text-[#A89F93]">Nuevo estado *</label>
                        <select wire:model="nuevo_estado"
                            class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-bold text-[#304060] dark:text-[#F3EAE1] focus:outline-none focus:border-[#A35A44]">
                            <option value="ABIERTO">ABIERTO</option>
                            <option value="EN_SEGUIMIENTO">EN SEGUIMIENTO</option>
                            <option value="CERRADO">CERRADO (Resuelto)</option>
                            <option value="ANULADO">ANULADO (Error / Corrección)</option>
                        </select>
                        @error('nuevo_estado')
                            <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-[#677084] dark:text-[#A89F93]">
                            Justificación clínica del cambio / Nota de evolución *
                        </label>
                        <textarea wire:model="nota_estado"
                            rows="3"
                            placeholder="Detalle los motivos clínicos o resolución del incidente..."
                            class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] p-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:outline-none focus:border-[#A35A44]"></textarea>
                        @error('nota_estado')
                            <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-3">
                    <button wire:click="cerrarModalEstado"
                        class="rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] px-4 py-2 text-xs font-bold text-[#677084]">
                        Cancelar
                    </button>
                    <button wire:click="actualizarEstado"
                        class="rounded-xl bg-[#A35A44] hover:bg-[#884A39] px-4 py-2 text-xs font-bold text-white transition">
                        Guardar cambio
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 4: CREAR DERIVACIÓN CLÍNICA                        --}}
    {{-- ======================================================== --}}
    @if($modalDerivacion)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalDerivacion()">

                <div class="flex items-center justify-between border-b border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-4">
                    <h3 class="text-sm font-black text-[#304060] dark:text-[#F3EAE1] flex items-center gap-2">
                        <i class="ph-bold ph-arrow-square-out text-base text-[#A35A44]"></i> Crear derivación formal
                    </h3>
                    <button wire:click="cerrarModalDerivacion" class="text-[#677084] hover:text-[#304060]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-[#677084] dark:text-[#A89F93]">Área receptora *</label>
                        <select wire:model="derivacion_area_receptora"
                            class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-semibold text-[#304060] dark:text-[#F3EAE1] focus:outline-none focus:border-[#A35A44]">
                            <option value="">Seleccione el área...</option>
                            @foreach($areasDisponibles as $ar)
                                <option value="{{ $ar->cod_area }}">{{ $ar->nombre }}</option>
                            @endforeach
                        </select>
                        @error('derivacion_area_receptora')
                            <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-[#677084] dark:text-[#A89F93]">Prioridad *</label>
                        <select wire:model="derivacion_prioridad"
                            class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] py-2 px-3 text-xs font-semibold text-[#304060] dark:text-[#F3EAE1] focus:outline-none focus:border-[#A35A44]">
                            <option value="BAJA">Baja</option>
                            <option value="MEDIA">Media</option>
                            <option value="ALTA">Alta</option>
                            <option value="URGENTE">Urgente</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-[#677084] dark:text-[#A89F93]">Motivo clínico *</label>
                        <textarea wire:model="derivacion_motivo"
                            rows="3"
                            placeholder="Motivo detallado de la derivación..."
                            class="w-full rounded-xl border border-[#C7B9AA] dark:border-[#423B34] bg-[#F0E8DE] dark:bg-[#211E1B] p-3 text-xs text-[#304060] dark:text-[#F3EAE1] focus:outline-none focus:border-[#A35A44]"></textarea>
                        @error('derivacion_motivo')
                            <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[#C7B9AA] dark:border-[#423B34] bg-[#DED1C3] dark:bg-[#2C2723] px-5 py-3">
                    <button wire:click="cerrarModalDerivacion"
                        class="rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] px-4 py-2 text-xs font-bold text-[#677084]">
                        Cancelar
                    </button>
                    <button wire:click="crearDerivacion"
                        class="rounded-xl bg-[#A35A44] hover:bg-[#884A39] px-4 py-2 text-xs font-bold text-white transition">
                        Emitir derivación
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 5: CONFIRMACIÓN POST-REGISTRO Y ACCIONES           --}}
    {{-- ======================================================== --}}
    @if($modalExito)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-2xl p-6 text-center space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#71876A]/20 text-[#71876A]">
                    <i class="ph-bold ph-check-circle text-3xl"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-black text-[#304060] dark:text-[#F3EAE1]">
                        ✓ Incidencia registrada
                    </h3>
                    <p class="text-xs text-[#677084] dark:text-[#A89F93]">
                        Código asignado: <strong class="text-[#304060] dark:text-[#F3EAE1]">{{ $ultimoCodIncidente }}</strong>
                    </p>
                </div>

                @if($ultimoRequiereMedico || $ultimoRequiereDerivacion)
                    <div class="p-3 bg-[#E4D8CC]/50 dark:bg-[#2C2723] rounded-xl space-y-2 text-left">
                        <span class="text-[11px] font-bold text-[#884A39] dark:text-[#D58C79] block">
                            Acciones de seguimiento recomendadas:
                        </span>

                        @if($ultimoRequiereMedico)
                            <button wire:click="solicitarValoracionMedica('{{ $ultimoCodIncidente }}')"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#C85D52] hover:bg-[#AC473D] text-white py-2 px-3 text-xs font-bold transition">
                                <i class="ph-bold ph-first-aid text-sm"></i> Solicitar valoración médica
                            </button>
                        @endif

                        @if($ultimoRequiereDerivacion)
                            <button wire:click="abrirModalDerivacion('{{ $ultimoCodIncidente }}')"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#A35A44] hover:bg-[#884A39] text-white py-2 px-3 text-xs font-bold transition">
                                <i class="ph-bold ph-arrow-square-out text-sm"></i> Crear derivación formal
                            </button>
                        @endif
                    </div>
                @endif

                <div class="pt-2">
                    <button wire:click="cerrarModalExito"
                        class="w-full rounded-xl bg-[#DED1C3] dark:bg-[#38312B] hover:bg-[#C7B9AA] dark:hover:bg-[#4E463E] py-2.5 text-xs font-bold text-[#304060] dark:text-[#F3EAE1] transition">
                        Finalizar y volver al listado
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
