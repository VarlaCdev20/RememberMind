<div class="space-y-4 font-sans bg-[#E9DFD3] dark:bg-[#1C1A18] p-3 sm:p-5 rounded-[18px]">
    {{-- ==================================================
         1. CABECERA INSTITUCIONAL Y NAVEGACIÓN DE TURNO
         ================================================== --}}
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-2 border-b border-[#C7B9AA]/70 dark:border-[#494139]">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#A35A44] dark:text-[#E5A898] block">
                CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · ENFERMERÍA
            </span>
            <div class="flex items-center gap-2 mt-0.5">
                <h1 class="text-xl sm:text-2xl font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight">
                    {{ $esSuperAdmin ? 'Agenda institucional de Enfermería' : 'Agenda Operativa de Cuidados' }}
                </h1>
                <span class="text-[11px] font-mono text-[#677084] dark:text-[#BDAE9F] bg-[#F0E8DE] dark:bg-[#2C2924] px-2 py-0.5 rounded-[6px] border border-[#C7B9AA] dark:border-[#494139]">
                    Hoy, {{ today()->translatedFormat('d \d\e F') }}
                </span>
            </div>
            <p class="text-xs text-[#677084] dark:text-[#BDAE9F] mt-0.5">
                {{ $esSuperAdmin ? 'Supervisión integral de alertas, medicación y cuidados de todos los residentes.' : 'Programación asistencial del turno basada en planes clínicos individuales.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start lg:self-center">
            <a href="{{ route('admin.enfermeria.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-xs font-bold bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139] hover:bg-[#E4D8CC] transition">
                <i class="ph ph-squares-four text-sm text-[#A35A44]"></i>
                <span>{{ $esSuperAdmin ? 'Resumen global' : 'Mi turno' }}</span>
            </a>

            <a href="{{ route('admin.enfermeria.pacientes') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-xs font-bold bg-[#F0E8DE] dark:bg-[#2C2924] text-[#304060] dark:text-[#EFE5DA] border border-[#C7B9AA] dark:border-[#494139] hover:bg-[#E4D8CC] transition">
                <i class="ph ph-users text-sm text-[#71876A]"></i>
                <span>{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis residentes' }}</span>
            </a>

            @if(!$esSuperAdmin && $turno && !$recepcion)
                <button type="button" 
                        wire:click="recibirTurno" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-xs font-bold bg-[#71876A] hover:bg-[#5D7056] text-white shadow-2xs transition cursor-pointer">
                    <i class="ph ph-handshake text-sm"></i>
                    <span>Recibir turno</span>
                </button>
            @elseif($recepcion)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] text-xs font-bold bg-[#E3EBE0] dark:bg-[#71876A]/20 text-[#55694E] dark:text-[#91A287] border border-[#C5D6C0] dark:border-[#71876A]/40">
                    <i class="ph ph-check-circle"></i>
                    <span>Recibido</span>
                </span>
            @endif
        </div>
    </header>

    {{-- ==================================================
         2. PESTAÑAS PRINCIPALES: AGENDA | HISTORIAL
         ================================================== --}}
    <nav class="flex items-center gap-6 border-b border-[#C7B9AA] dark:border-[#494139] text-xs font-[700] pb-0.5">
        <button 
            type="button"
            wire:click="cambiarTab('agenda')"
            class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-2 {{ $tab === 'agenda' ? 'border-b-2 border-[#A35A44] text-[#A35A44] dark:text-[#E5A898]' : 'text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-[#EFE5DA]' }}">
            <i class="ph ph-calendar-check text-sm {{ $tab === 'agenda' ? 'text-[#A35A44]' : 'text-[#677084]' }}"></i>
            <span>AGENDA</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10.5px] {{ $tab === 'agenda' ? 'bg-[#A35A44]/15 text-[#A35A44] dark:text-[#E5A898]' : 'bg-[#E4D8CC] dark:bg-[#211F1B] text-[#677084]' }}">
                {{ count($itemsAgenda) }}
            </span>
        </button>

        <button 
            type="button"
            wire:click="cambiarTab('historial')"
            class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-2 {{ $tab === 'historial' ? 'border-b-2 border-[#A35A44] text-[#A35A44] dark:text-[#E5A898]' : 'text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-[#EFE5DA]' }}">
            <i class="ph ph-clock-counter-clockwise text-sm {{ $tab === 'historial' ? 'text-[#A35A44]' : 'text-[#677084]' }}"></i>
            <span>HISTORIAL</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10.5px] {{ $tab === 'historial' ? 'bg-[#A35A44]/15 text-[#A35A44] dark:text-[#E5A898]' : 'bg-[#E4D8CC] dark:bg-[#211F1B] text-[#677084]' }}">
                {{ count($historial) }}
            </span>
        </button>
    </nav>

    {{-- ==================================================
         3. PESTAÑA: AGENDA OPERATIVA DEL TURNO
         ================================================== --}}
    @if($tab === 'agenda')
        {{-- Barra de Filtros Compacta de Agenda --}}
        <section class="rm-filter-bar">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-2 items-center text-xs">
                {{-- Búsqueda --}}
                <div class="lg:col-span-4 relative flex items-center">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-[#677084] dark:text-[#BDAE9F]">
                        <i class="ph ph-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.300ms="buscar"
                        placeholder="Buscar residente, intervención o plan..."
                        class="w-full h-9 pl-8 pr-7 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 focus:ring-[#A35A44] transition" />
                    @if(!empty($buscar))
                        <button type="button" wire:click="$set('buscar', '')" class="absolute inset-y-0 right-0 flex items-center pr-2 text-[#677084] hover:text-[#A35A44]">
                            <i class="ph ph-x-circle text-sm"></i>
                        </button>
                    @endif
                </div>

                {{-- Prioridad --}}
                <div class="lg:col-span-3">
                    <select wire:model.live="filtroPrioridad" class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                        <option value="">Prioridad (Todas)</option>
                        <option value="ALTA">Alta</option>
                        <option value="MEDIA">Media</option>
                        <option value="BAJA">Baja</option>
                    </select>
                </div>

                {{-- Estado --}}
                <div class="lg:col-span-2">
                    <select wire:model.live="filtroEstado" class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                        <option value="">Estado (Todos)</option>
                        <option value="ALERTA">Con alerta activa</option>
                        <option value="VENCIDA">Vencidas</option>
                        <option value="PENDIENTE">Pendientes</option>
                        <option value="REALIZADA">Realizadas</option>
                    </select>
                </div>

                {{-- Residente --}}
                <div class="lg:col-span-3">
                    <select wire:model.live="filtroResidente" class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 focus:ring-[#A35A44] cursor-pointer">
                        <option value="">Todos los residentes</option>
                        @foreach($residentes as $res)
                            <option value="{{ $res->cod_residente }}">
                                {{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- Listado Operativo de Intervenciones (Orden Obligatorio Clínico) --}}
        <section class="space-y-2.5">
            @forelse($itemsAgenda as $item)
                @php
                    $esAlerta = !empty($item['alerta_vinculada']);
                    $esPrioridadAlta = ($item['prioridad'] === 'ALTA' && !$esAlerta);
                    $esVencida = ($item['estado'] === 'VENCIDA');
                    $esRealizada = ($item['estado'] === 'REALIZADA');
                    $esNoRealizada = ($item['estado'] === 'NO_REALIZADA');
                    $esPendiente = ($item['estado'] === 'PENDIENTE');

                    // Tratamiento de Ficha
                    if ($esAlerta) {
                        // Alerta activa relacionada: TODA la ficha a tratamiento rojo suave
                        $cardClass = 'bg-[#FDF4F3] dark:bg-[#352422] border-l-4 border-l-[#C85D52] border-y border-r border-[#E5BDB5] dark:border-[#523330] shadow-xs';
                        $badgeEstado = 'bg-[#F3DDDA] dark:bg-[#C85D52]/20 text-[#C85D52] dark:text-[#E5A898] border border-[#E5BDB5]';
                    } elseif ($esPrioridadAlta) {
                        // Prioridad alta sin alerta: destacar en terracota, no rojo completo
                        $cardClass = 'bg-[#F0E8DE] dark:bg-[#2C2924] border-l-4 border-l-[#A35A44] border border-[#C7B9AA] dark:border-[#494139] shadow-2xs';
                        $badgeEstado = match($item['estado']) {
                            'VENCIDA' => 'bg-[#F3DDDA] text-[#C85D52] border border-[#E5BDB5]',
                            'REALIZADA' => 'bg-[#E3EBE0] text-[#55694E] border border-[#C5D6C0]',
                            'NO_REALIZADA' => 'bg-[#F3DDDA] text-[#C85D52] border border-[#E5BDB5]',
                            default => 'bg-[#FBF0D9] text-[#9E732B] border border-[#EED7A1]',
                        };
                    } elseif ($esVencida) {
                        $cardClass = 'bg-[#F0E8DE] dark:bg-[#2C2924] border-l-4 border-l-[#C85D52] border border-[#C7B9AA] dark:border-[#494139] shadow-2xs';
                        $badgeEstado = 'bg-[#F3DDDA] text-[#C85D52] border border-[#E5BDB5]';
                    } elseif ($esRealizada) {
                        $cardClass = 'opacity-75 bg-[#F0E8DE]/70 dark:bg-[#2C2924]/70 border-l-4 border-l-[#71876A] border border-[#C7B9AA] dark:border-[#494139] hover:opacity-100 transition-opacity';
                        $badgeEstado = 'bg-[#E3EBE0] text-[#55694E] border border-[#C5D6C0]';
                    } elseif ($esNoRealizada) {
                        $cardClass = 'opacity-85 bg-[#F0E8DE] dark:bg-[#2C2924] border-l-4 border-l-[#C85D52] border border-[#C7B9AA] dark:border-[#494139]';
                        $badgeEstado = 'bg-[#F3DDDA] text-[#C85D52] border border-[#E5BDB5]';
                    } else {
                        // Pendiente
                        $cardClass = 'bg-[#F0E8DE] dark:bg-[#2C2924] border-l-4 border-l-[#D2A45E] border border-[#C7B9AA] dark:border-[#494139] hover:bg-[#E4D8CC]/40 shadow-2xs';
                        $badgeEstado = 'bg-[#FBF0D9] text-[#9E732B] border border-[#EED7A1]';
                    }
                @endphp

                <article class="p-3 sm:p-3.5 rounded-[12px] transition duration-150 {{ $cardClass }}">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        {{-- Contenido Principal --}}
                        <div class="flex items-start gap-3 min-w-0">
                            {{-- Hora Programada en Pill --}}
                            <div class="shrink-0 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-[6px] bg-[#E4D8CC] dark:bg-[#211F1B] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] font-mono text-xs font-[800]">
                                    {{ $item['hora_programada'] }}
                                </span>
                            </div>

                            {{-- Información de Intervención y Paciente --}}
                            <div class="min-w-0 space-y-1">
                                {{-- Cabecera de la ficha --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Residente --}}
                                    <span class="font-[800] text-sm text-[#304060] dark:text-[#EFE5DA]">
                                        {{ $item['nombre_residente'] }}
                                    </span>

                                    {{-- Hab / Cama --}}
                                    <span class="text-xs text-[#677084] dark:text-[#BDAE9F] font-semibold bg-[#E4D8CC]/60 dark:bg-[#211F1B] px-2 py-0.5 rounded-[5px] border border-[#C7B9AA]/60">
                                        {{ $item['ubicacion'] }}
                                    </span>

                                    {{-- Badges de Alerta / Prioridad --}}
                                    @if($esAlerta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-[10.5px] font-[800] bg-[#C85D52] text-white">
                                            <i class="ph ph-warning-bold text-xs"></i>
                                            <span>ALERTA</span>
                                        </span>
                                    @elseif($item['residente_con_alerta'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-[10.5px] font-semibold bg-[#F3DDDA] text-[#C85D52] border border-[#E5BDB5]">
                                            <i class="ph ph-warning-circle"></i>
                                            <span>Residente con alerta activa</span>
                                        </span>
                                    @endif

                                    @if($esPrioridadAlta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-[10.5px] font-bold bg-[#A35A44]/15 text-[#A35A44] dark:text-[#E5A898] border border-[#A35A44]/30">
                                            Prioridad Alta
                                        </span>
                                    @elseif($item['prioridad'] === 'BAJA')
                                        <span class="px-1.5 py-0.2 rounded text-[10px] text-[#677084] dark:text-[#BDAE9F] bg-[#E4D8CC]/60 border border-[#C7B9AA]/60">
                                            Baja
                                        </span>
                                    @endif
                                </div>

                                {{-- Nombre de la Intervención --}}
                                <h3 class="font-[700] text-xs text-[#304060] dark:text-[#EFE5DA] leading-snug">
                                    {{ $item['nombre_intervencion'] }}
                                </h3>

                                {{-- Plan de Cuidado y Frecuencia --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ph ph-folder text-xs text-[#A35A44]"></i>
                                        <span>{{ $item['nombre_plan'] }}</span>
                                    </span>
                                    <span>·</span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ph ph-clock text-xs text-[#677084]"></i>
                                        <span>Frecuencia: {{ $item['frecuencia'] }}</span>
                                    </span>
                                </div>

                                @if($esAlerta && !empty($item['alerta_descripcion']))
                                    <p class="text-[11px] font-semibold text-[#C85D52] dark:text-[#E5A898] mt-0.5">
                                        ⚠ {{ $item['alerta_descripcion'] }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Estado y Botón Registrar --}}
                        <div class="flex items-center gap-3 self-end md:self-center shrink-0">
                            {{-- Badge Estado --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-[6px] text-xs font-[700] {{ $badgeEstado }}">
                                {{ $item['estado'] === 'VENCIDA' ? 'Vencida' : ($item['estado'] === 'REALIZADA' ? 'Realizada' : ($item['estado'] === 'NO_REALIZADA' ? 'No realizada' : ($esAlerta ? 'Alerta activa' : 'Pendiente'))) }}
                            </span>

                            {{-- Acción Registrar --}}
                            @if(!$esRealizada)
                                <button type="button"
                                    wire:click="abrirModalRegistrar('{{ $item['cod_intervencion'] }}', '{{ $item['cod_residente'] }}', '{{ $item['cod_programacion'] }}', '{{ $item['hora_programada'] }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] text-xs font-bold bg-[#A35A44] hover:bg-[#884A39] text-white shadow-2xs transition cursor-pointer">
                                    <i class="ph ph-pencil-simple-line"></i>
                                    <span>Registrar</span>
                                </button>
                            @else
                                <span class="text-[11px] font-semibold text-[#71876A] dark:text-[#91A287] inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i>
                                    <span>Firmada</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center rounded-[12px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-dashed border-[#C7B9AA] text-xs text-[#677084]">
                    <i class="ph ph-check-circle text-3xl text-[#71876A] mb-1.5 block"></i>
                    <p class="font-bold text-[#304060] dark:text-[#EFE5DA]">No hay intervenciones registradas en este filtro</p>
                    <p class="text-[11px] mt-0.5">La agenda se alimenta de los planes de cuidado activos de sus residentes.</p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ==================================================
         4. PESTAÑA: HISTORIAL CLÍNICO DIRECTO
         (Bitácora completa sin botón "Ver detalle")
         ================================================== --}}
    @if($tab === 'historial')
        <section class="space-y-3">
            {{-- Filtros de Historial --}}
            <div class="p-3 rounded-[14px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA] dark:border-[#494139] shadow-2xs grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <div>
                    <input type="text"
                        wire:model.live.debounce.300ms="buscarHistorial"
                        placeholder="Buscar residente o intervención..."
                        class="w-full h-9 px-3 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084]" />
                </div>
                <div>
                    <select wire:model.live="filtroHistorialResidente" class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA]">
                        <option value="">Todos los residentes</option>
                        @foreach($residentes as $res)
                            <option value="{{ $res->cod_residente }}">{{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date"
                        wire:model.live="filtroHistorialFecha"
                        class="w-full h-9 px-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA]" />
                </div>
            </div>

            {{-- Bitácora Tabular Directa --}}
            <div class="bg-[#F0E8DE] dark:bg-[#2C2924] rounded-[14px] border border-[#C7B9AA] dark:border-[#494139] shadow-sm overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="w-full table-auto text-left border-collapse min-w-[850px] text-xs">
                        <thead>
                            <tr class="bg-[#E4D8CC] dark:bg-[#211F1B] text-[10.5px] font-[700] text-[#677084] dark:text-[#BDAE9F] uppercase tracking-wider border-b border-[#C7B9AA] dark:border-[#494139]">
                                <th class="px-2.5 py-2 w-[70px] text-center">Prog.</th>
                                <th class="px-2.5 py-2 w-[70px] text-center">Real</th>
                                <th class="px-3 py-2">Residente</th>
                                <th class="px-3 py-2">Intervención</th>
                                <th class="px-3 py-2">Plan de cuidado</th>
                                <th class="px-2.5 py-2 w-[110px] text-center">Resultado</th>
                                <th class="px-3 py-2">Observación / Motivo</th>
                                <th class="px-3 py-2 w-[120px]">Profesional</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C7B9AA]/60 dark:divide-[#494139]">
                            @forelse($historial as $ej)
                                @php
                                    $res = $ej->residente;
                                    $int = $ej->intervencion;
                                    $plan = $int?->plan;
                                    $prof = $ej->personal;
                                    $esOmitida = ($ej->estado === 'NO_REALIZADA' || in_array(strtolower((string)$ej->resultado), ['omitida', 'no realizada']));
                                @endphp
                                <tr class="hover:bg-[#E4D8CC]/40 transition">
                                    {{-- Hora Programada --}}
                                    <td class="px-2.5 py-2 text-center font-mono font-[700] text-[#677084] whitespace-nowrap">
                                        {{ $ej->fecha_hora_programada ? $ej->fecha_hora_programada->format('H:i') : '--:--' }}
                                    </td>

                                    {{-- Hora Real --}}
                                    <td class="px-2.5 py-2 text-center font-mono font-[700] text-[#304060] dark:text-[#EFE5DA] whitespace-nowrap">
                                        {{ $ej->fecha_hora_ejecucion ? $ej->fecha_hora_ejecucion->format('H:i') : '--:--' }}
                                    </td>

                                    {{-- Residente --}}
                                    <td class="px-3 py-2 font-[700] text-[#304060] dark:text-[#EFE5DA]">
                                        {{ $res ? trim("{$res->nombres} {$res->apellido_paterno}") : 'Residente' }}
                                    </td>

                                    {{-- Intervención --}}
                                    <td class="px-3 py-2 font-medium text-[#304060] dark:text-[#EFE5DA]">
                                        {{ $int?->nombre ?? 'Cuidado programado' }}
                                    </td>

                                    {{-- Plan --}}
                                    <td class="px-3 py-2 text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                                        {{ $plan?->nombre ?? 'Plan de Cuidado' }}
                                    </td>

                                    {{-- Resultado --}}
                                    <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10.5px] font-[700] {{ $esOmitida ? 'bg-[#F3DDDA] text-[#C85D52] border border-[#E5BDB5]' : 'bg-[#E3EBE0] text-[#55694E] border border-[#C5D6C0]' }}">
                                            {{ $ej->resultado ?? ($esOmitida ? 'No realizada' : 'Realizada') }}
                                        </span>
                                    </td>

                                    {{-- Observación / Motivo Omisión --}}
                                    <td class="px-3 py-2 text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                                        @if(!empty($ej->motivo_omision))
                                            <span class="font-bold text-[#C85D52] block">Motivo: {{ $ej->motivo_omision }}</span>
                                        @endif
                                        @if(!empty($ej->observacion))
                                            <span class="block">{{ $ej->observacion }}</span>
                                        @elseif(empty($ej->motivo_omision))
                                            <span class="italic text-[#A0988E]">Sin observaciones registradas</span>
                                        @endif
                                    </td>

                                    {{-- Profesional --}}
                                    <td class="px-3 py-2 text-[11px] font-semibold text-[#304060] dark:text-[#EFE5DA] whitespace-nowrap">
                                        {{ $prof ? trim("{$prof->nombres} {$prof->apellido_paterno}") : 'Enfermería' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-xs text-[#677084]">
                                        <i class="ph ph-folder-open text-3xl text-[#A35A44] mb-1.5 block"></i>
                                        No hay registros de cuidados ejecutados en el rango de fechas seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    {{-- ==================================================
         5. MODAL FLOTANTE CENTRADO DE REGISTRO
         (Solo lectura de contexto + campos editables y automáticos)
         ================================================== --}}
    @if($modalRegistrarAbierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/50 backdrop-blur-xs animate-fade-in"
             role="dialog"
             aria-modal="true">
            <div class="w-full max-w-lg bg-[#F0E8DE] dark:bg-[#2C2924] rounded-[16px] border border-[#C7B9AA] dark:border-[#494139] shadow-xl overflow-hidden text-xs">
                {{-- Header del Modal --}}
                <div class="px-4 py-3 bg-[#E4D8CC] dark:bg-[#211F1B] border-b border-[#C7B9AA] dark:border-[#494139] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-[8px] bg-[#A35A44] text-white flex items-center justify-center">
                            <i class="ph ph-pencil-line text-sm"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-[800] text-[#304060] dark:text-[#EFE5DA]">
                                Registrar Ejecución de Cuidado
                            </h2>
                            <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                                Registro clínico directo en la bitácora del turno
                            </span>
                        </div>
                    </div>
                    <button type="button" 
                        wire:click="cerrarModalRegistrar" 
                        class="p-1 text-[#677084] hover:text-[#A35A44] rounded-[6px] transition cursor-pointer">
                        <i class="ph ph-x text-base"></i>
                    </button>
                </div>

                {{-- Cuerpo del Modal --}}
                <div class="p-4 space-y-3.5 max-h-[75vh] overflow-y-auto">
                    {{-- BLOQUE SOLO LECTURA: Contexto Clínico del Residente y Plan --}}
                    <div class="p-3 rounded-[10px] bg-[#E4D8CC]/70 dark:bg-[#211F1B] border border-[#C7B9AA]/70 dark:border-[#494139] space-y-2">
                        <span class="text-[10px] font-bold text-[#A35A44] dark:text-[#E5A898] uppercase tracking-wider block">
                            Datos del Cuidado (Solo Lectura)
                        </span>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block">Residente:</span>
                                <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ $datosModal['residente_nombre'] ?? 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block">Ubicación:</span>
                                <span class="text-[#304060] dark:text-[#EFE5DA] font-semibold">{{ $datosModal['ubicacion'] ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="border-t border-[#C7B9AA]/50 pt-1.5">
                            <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block">Intervención:</span>
                            <strong class="text-[#304060] dark:text-[#EFE5DA] text-xs block leading-snug">
                                {{ $datosModal['intervencion_nombre'] ?? 'N/A' }}
                            </strong>
                            <p class="text-[11px] text-[#677084] dark:text-[#BDAE9F] mt-0.5">
                                {{ $datosModal['intervencion_descripcion'] ?? '' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-2 pt-1 border-t border-[#C7B9AA]/50 text-[11px]">
                            <div>
                                <span class="text-[10px] text-[#677084] block">Prioridad:</span>
                                <span class="font-bold text-[#A35A44]">{{ $datosModal['prioridad'] ?? 'MEDIA' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#677084] block">Hora prog.:</span>
                                <span class="font-mono font-bold text-[#304060]">{{ $datosModal['hora_programada'] ?? '--:--' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#677084] block">Frecuencia:</span>
                                <span class="font-semibold text-[#677084]">{{ $datosModal['frecuencia'] ?? 'Turno' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE EDITABLE --}}
                    <div class="space-y-3">
                        {{-- Toggle Omisión --}}
                        <div class="flex items-center justify-between p-2 rounded-[8px] bg-[#E4D8CC]/40 border border-[#C7B9AA]/60">
                            <div>
                                <span class="font-bold text-[#304060] dark:text-[#EFE5DA] text-xs">¿Intervención no realizada u omitida?</span>
                                <p class="text-[10.5px] text-[#677084]">Marque si el residente rechazó o hubo impedimento clínico.</p>
                            </div>
                            <input type="checkbox"
                                wire:model.live="esNoRealizada"
                                class="w-4 h-4 rounded text-[#A35A44] focus:ring-[#A35A44] cursor-pointer" />
                        </div>

                        {{-- Resultado Obligatorio --}}
                        <div>
                            <label class="block text-xs font-bold text-[#304060] dark:text-[#EFE5DA] mb-1">
                                Resultado de la Intervención <span class="text-[#C85D52]">*</span>
                            </label>
                            @if(!$esNoRealizada)
                                <select wire:model="resultado" class="w-full h-9 px-3 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:ring-1 focus:ring-[#A35A44]">
                                    <option value="Satisfactorio">Satisfactorio (completado con éxito)</option>
                                    <option value="Realizado según protocolo">Realizado según protocolo</option>
                                    <option value="Con dificultad / colaboración parcial">Con dificultad / colaboración parcial</option>
                                    <option value="Sin cambios clínicos relevantes">Sin cambios clínicos relevantes</option>
                                    <option value="Evolución favorable">Evolución favorable</option>
                                </select>
                            @else
                                <select wire:model="resultado" class="w-full h-9 px-3 text-xs rounded-[8px] border border-[#C85D52] bg-[#FDF4F3] text-[#C85D52] font-bold">
                                    <option value="No realizada / Omitida">No realizada / Omitida</option>
                                    <option value="Rechazada por residente">Rechazada por residente</option>
                                    <option value="Contraindicación clínica transitoria">Contraindicación clínica transitoria</option>
                                    <option value="Ausencia de residente en centro">Ausencia de residente en centro</option>
                                </select>
                            @endif
                            @error('resultado') <span class="text-[11px] text-[#C85D52] font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Motivo de Omisión (Obligatorio si no realizada) --}}
                        @if($esNoRealizada)
                            <div>
                                <label class="block text-xs font-bold text-[#C85D52] mb-1">
                                    Motivo de Omisión Justificado <span class="text-[#C85D52]">*</span>
                                </label>
                                <textarea wire:model="motivoOmision"
                                    rows="2"
                                    placeholder="Indique con claridad el motivo clínico por el cual no se realizó..."
                                    class="w-full p-2.5 text-xs rounded-[8px] border border-[#C85D52] bg-[#FDF4F3] text-[#304060] placeholder-[#A0988E] focus:outline-none focus:ring-1 focus:ring-[#C85D52]"></textarea>
                                @error('motivoOmision') <span class="text-[11px] text-[#C85D52] font-semibold block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Observación de Enfermería --}}
                        <div>
                            <label class="block text-xs font-bold text-[#304060] dark:text-[#EFE5DA] mb-1">
                                Observación Clínica de Enfermería (Opcional)
                            </label>
                            <textarea wire:model="observacion"
                                rows="2"
                                placeholder="Anotaciones asistenciales relevantes para la bitácora o pase de turno..."
                                class="w-full p-2.5 text-xs rounded-[8px] border border-[#C7B9AA] dark:border-[#494139] bg-[#E4D8CC] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] focus:outline-none focus:ring-1 focus:ring-[#A35A44]"></textarea>
                            @error('observacion') <span class="text-[11px] text-[#C85D52] font-semibold block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- NOTA AUTOMÁTICA --}}
                    <div class="p-2 rounded-[6px] bg-[#E4D8CC]/50 text-[10.5px] text-[#677084] flex items-center gap-1.5">
                        <i class="ph ph-lock-key text-xs text-[#71876A]"></i>
                        <span>Registro automático con fecha/hora actual (now()), personal firmante y jornada activa.</span>
                    </div>
                </div>

                {{-- Footer del Modal --}}
                <div class="px-4 py-3 bg-[#E4D8CC] dark:bg-[#211F1B] border-t border-[#C7B9AA] dark:border-[#494139] flex items-center justify-end gap-2">
                    <button type="button"
                        wire:click="cerrarModalRegistrar"
                        class="px-3 py-1.5 rounded-[8px] font-semibold text-[#677084] hover:text-[#304060] bg-[#F0E8DE] border border-[#C7B9AA] cursor-pointer">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="registrarEjecucion"
                        wire:loading.attr="disabled"
                        class="px-4 py-1.5 rounded-[8px] font-bold text-white bg-[#A35A44] hover:bg-[#884A39] shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                        <span wire:loading.remove>Confirmar Registro</span>
                        <span wire:loading class="inline-flex items-center gap-1">
                            <i class="ph ph-spinner animate-spin"></i> Registrando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
