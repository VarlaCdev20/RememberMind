<div class="space-y-6 font-sans text-[#304060] dark:text-[#EAE6E1]" style="font-family: 'Outfit', sans-serif;">

    {{-- CABECERA INSTITUCIONAL --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] p-5 sm:p-6 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#A35A44]/15 text-[#A35A44] dark:bg-[#A35A44]/25">
                    <i class="ph-bold ph-arrows-left-right text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[#304060] dark:text-[#F3EAE1]">
                        Pases de turno
                    </h1>
                    <p class="text-xs font-medium text-[#677084] dark:text-[#B5AAA0]">
                        Continuidad de cuidados y comunicación entre jornadas
                    </p>
                </div>
            </div>
        </div>

        {{-- Selector de Pestañas Principales --}}
        <div class="inline-flex rounded-xl bg-[#DED1C3] dark:bg-[#2C2723] p-1 border border-[#C7B9AA] dark:border-[#423B34]">
            <button wire:click="cambiarTab('entrega')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tabActivo === 'entrega' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
                <i class="ph-bold ph-handshake text-sm"></i>
                <span>Transferencia de guardia</span>
            </button>
            <button wire:click="cambiarTab('historial')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tabActivo === 'historial' ? 'bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shadow-sm' : 'text-[#677084] dark:text-[#A89F93] hover:text-[#304060]' }}">
                <i class="ph-bold ph-clock-counter-clockwise text-sm"></i>
                <span>Historial de pases</span>
            </button>
        </div>
    </div>

    {{-- MENSAJES FLASH --}}
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

    {{-- BARRA DE CONTEXTO ASISTENCIAL (SOLO LECTURA, DETERMINADA POR EL SISTEMA) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-2xl bg-[#DED1C3] dark:bg-[#2C2723] border border-[#C7B9AA] dark:border-[#423B34] p-4 text-xs">
        {{-- Jornada actual --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F0E8DE] dark:bg-[#211E1B] text-[#A35A44] shrink-0 border border-[#C7B9AA]/60">
                <i class="ph-bold ph-sun text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#A89F93] block">Jornada actual (Saliente):</span>
                <span class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1]">
                    {{ $jornadaSaliente->turno?->nombre ?? 'Guardia activa' }} · {{ \Carbon\Carbon::parse($jornadaSaliente->fecha_jornada)->format('d/m/Y') }}
                </span>
            </div>
        </div>

        {{-- Profesional autenticado --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F0E8DE] dark:bg-[#211E1B] text-[#304060] dark:text-[#F3EAE1] shrink-0 border border-[#C7B9AA]/60">
                <i class="ph-bold ph-user text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#A89F93] block">Profesional responsable:</span>
                <span class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1]">
                    {{ auth()->user()->name ?? 'Enfermería' }}
                </span>
            </div>
        </div>

        {{-- Siguiente jornada --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F0E8DE] dark:bg-[#211E1B] text-[#71876A] shrink-0 border border-[#C7B9AA]/60">
                <i class="ph-bold ph-arrow-right text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#A89F93] block">Siguiente jornada (Entrante):</span>
                <span class="font-bold text-xs text-[#884A39] dark:text-[#D58C79]">
                    {{ $jornadaEntrante->turno?->nombre ?? 'Siguiente guardia' }} · {{ \Carbon\Carbon::parse($jornadaEntrante->fecha_jornada)->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>


    {{-- ======================================================== --}}
    {{-- VISTA 1: TRANSFERENCIA DE GUARDIA (OPERATIVA)            --}}
    {{-- ======================================================== --}}
    @if($tabActivo === 'entrega')

        {{-- SECCIÓN A: PASES PENDIENTES DE RECIBIR (SI EXISTEN PARA ESTE PROFESIONAL) --}}
        @if(!empty($pasesPendientesRecibir))
            <div class="space-y-3 rounded-2xl bg-[#E4D8CC]/70 dark:bg-[#2C2723]/70 border border-[#C7B9AA] dark:border-[#423B34] p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-tray text-base text-[#A35A44]"></i>
                        <h2 class="text-sm font-black text-[#304060] dark:text-[#F3EAE1]">
                            Pases pendientes de recibir ({{ count($pasesPendientesRecibir) }})
                        </h2>
                    </div>
                    <span class="text-[11px] font-semibold text-[#884A39] dark:text-[#D58C79]">
                        Confirme la recepción para asumir la continuidad asistencial
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($pasesPendientesRecibir as $paseRec)
                        <div class="rounded-xl bg-[#F0E8DE] dark:bg-[#211E1B] border border-[#C7B9AA] p-4 space-y-3 shadow-xs">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-xs text-[#304060] dark:text-[#F3EAE1]">
                                        {{ $paseRec->residente?->nombre_completo ?? 'Residente' }}
                                    </h3>
                                    <span class="text-[11px] text-[#677084] dark:text-[#A89F93]">
                                        {{ $paseRec->residente?->ubicacion_formateada ?? 'Sin ubicación' }}
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-[#A35A44]/15 text-[#A35A44]">
                                    Entregado
                                </span>
                            </div>

                            <div class="text-[11px] text-[#677084] dark:text-[#A89F93] space-y-0.5 border-t border-[#C7B9AA]/40 pt-2">
                                <div><strong class="text-[#304060] dark:text-[#E8DFD5]">De:</strong> {{ $paseRec->personalSaliente?->usuario?->name ?? 'Enfermero saliente' }}</div>
                                <div><strong class="text-[#304060] dark:text-[#E8DFD5]">Transición:</strong> {{ $paseRec->jornadaSaliente?->turno?->nombre }} → {{ $paseRec->jornadaEntrante?->turno?->nombre }}</div>
                                <p class="line-clamp-2 italic text-[#304060] dark:text-[#E8DFD5] mt-1">"{{ $paseRec->resumen }}"</p>
                            </div>

                            <button wire:click="abrirRevisarPase('{{ $paseRec->cod_pase }}')"
                                class="w-full flex items-center justify-center gap-1.5 rounded-lg bg-[#71876A] hover:bg-[#5C7056] text-white py-1.5 text-xs font-bold transition">
                                <i class="ph-bold ph-check-square"></i> Revisar y recibir pase
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- SECCIÓN B: RESIDENTES A ENTREGAR --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-black text-[#304060] dark:text-[#F3EAE1]">
                        Residentes a entregar
                    </h2>
                    <span class="text-xs text-[#677084] dark:text-[#A89F93]">
                        ({{ count($residentesAEntregar) }} asignados a su guardia)
                    </span>
                </div>

                {{-- Subfiltros --}}
                <div class="flex items-center gap-1.5 text-xs">
                    <button wire:click="$set('filtroEntrega', 'todos')"
                        class="px-3 py-1 rounded-lg font-bold transition {{ $filtroEntrega === 'todos' ? 'bg-[#304060] text-white' : 'bg-[#DED1C3] text-[#677084] hover:text-[#304060]' }}">
                        Todos
                    </button>
                    <button wire:click="$set('filtroEntrega', 'criticos')"
                        class="px-3 py-1 rounded-lg font-bold transition {{ $filtroEntrega === 'criticos' ? 'bg-[#C85D52] text-white' : 'bg-[#DED1C3] text-[#677084] hover:text-[#304060]' }}">
                        Con Alerta / Incidente
                    </button>
                    <button wire:click="$set('filtroEntrega', 'pendientes')"
                        class="px-3 py-1 rounded-lg font-bold transition {{ $filtroEntrega === 'pendientes' ? 'bg-[#A35A44] text-white' : 'bg-[#DED1C3] text-[#677084] hover:text-[#304060]' }}">
                        Por entregar
                    </button>
                </div>
            </div>

            {{-- Grid de Residentes a Entregar --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($residentesAEntregar as $item)
                    @php
                        $r = $item['residente'];
                        $pase = $item['pase'];
                        $esCritico = $item['tiene_alerta_critica'] || $item['incidentes_count'] > 0;
                        $esEntregado = $pase && $pase->esEntregado();
                        $esRecibido = $pase && $pase->esRecibido();
                        $esBorrador = $pase && $pase->esBorrador();
                    @endphp

                    <div class="rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border {{ $esCritico ? 'border-[#C85D52]/70 bg-[#FDF2F0]/80 dark:bg-[#341F20]/80' : 'border-[#C7B9AA] dark:border-[#423B34]' }} p-4 sm:p-5 shadow-sm space-y-3 flex flex-col justify-between">
                        <div class="space-y-2">
                            {{-- Cabecera Tarjeta: Residente y Ubicación --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-sm text-[#304060] dark:text-[#F3EAE1]">
                                        {{ $r->nombre_completo }}
                                    </h3>
                                    <span class="text-xs text-[#884A39] dark:text-[#D58C79] font-medium block">
                                        {{ $r->ubicacion_formateada }}
                                    </span>
                                </div>

                                {{-- Badge de Estado del Pase --}}
                                @if($esRecibido)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#71876A]/20 text-[#71876A] border border-[#71876A]/40">
                                        ✓ Recibido
                                    </span>
                                @elseif($esEntregado)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#A35A44]/20 text-[#A35A44] border border-[#A35A44]/40">
                                        ● Entregado
                                    </span>
                                @elseif($esBorrador)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#D2A45E]/20 text-[#8C6B32] border border-[#D2A45E]/40">
                                        ✎ Borrador
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#677084]/20 text-[#677084]">
                                        Pendiente
                                    </span>
                                @endif
                            </div>

                            {{-- Profesional Receptor Resuelto Automáticamente --}}
                            <div class="rounded-xl bg-[#E4D8CC]/50 dark:bg-[#2C2723]/50 p-2.5 border border-[#C7B9AA]/50 text-xs">
                                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#A89F93] block">
                                    Enfermero/a receptor/a (Jornada entrante):
                                </span>
                                <span class="font-bold {{ $item['tiene_receptor'] ? 'text-[#304060] dark:text-[#F3EAE1]' : 'text-[#8C6B32] italic' }}">
                                    {{ $item['nombre_receptor'] }}
                                </span>
                            </div>

                            {{-- Alertas / Incidentes vigentes --}}
                            @if($esCritico)
                                <div class="space-y-1">
                                    @if($item['alertas_count'] > 0)
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#C85D52]">
                                            <i class="ph-bold ph-warning-circle"></i>
                                            <span>{{ $item['alertas_count'] }} alerta(s) clínica(s) activa(s)</span>
                                        </div>
                                    @endif
                                    @if($item['incidentes_count'] > 0)
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#A35A44]">
                                            <i class="ph-bold ph-warning-octagon"></i>
                                            <span>{{ $item['incidentes_count'] }} incidente(s) en la jornada</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Botón de Acción --}}
                        <div class="pt-2 border-t border-[#C7B9AA]/40 dark:border-[#423B34]">
                            @if($esRecibido || $esEntregado)
                                <button wire:click="abrirVer('{{ $pase->cod_pase }}')"
                                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-[#DED1C3] dark:bg-[#38312B] hover:bg-[#C7B9AA] py-2 text-xs font-bold text-[#304060] dark:text-[#F3EAE1] transition">
                                    <i class="ph-bold ph-eye"></i> Ver pase registrado
                                </button>
                            @else
                                <button wire:click="abrirPrepararPase('{{ $r->cod_residente }}')"
                                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-[#A35A44] hover:bg-[#884A39] text-white py-2 text-xs font-bold transition shadow-xs">
                                    <i class="ph-bold ph-notepad"></i>
                                    <span>{{ $esBorrador ? 'Continuar preparando pase' : 'Preparar pase' }}</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] p-12 text-center text-[#677084] dark:text-[#A89F93]">
                        <p class="font-bold text-sm">No hay residentes asignados para entregar en la jornada actual.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- VISTA 2: TAB HISTORIAL DE PASES                          --}}
    {{-- ======================================================== --}}
    @if($tabActivo === 'historial')
        <div class="space-y-4">
            {{-- Filtros del Historial --}}
            <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-[#DED1C3] dark:bg-[#2C2723] border border-[#C7B9AA] dark:border-[#423B34] p-3.5 text-xs">
                <div class="relative w-full sm:w-64">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[#677084]"></i>
                    <input wire:model.live.debounce.300ms="searchHistorial" type="text"
                        placeholder="Buscar residente o resumen..."
                        class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] py-1.5 pl-8 pr-3 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]">
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-semibold text-[#677084]">Fecha:</span>
                    <input wire:model.live="filtroFechaHistorial" type="date"
                        class="rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] py-1.5 px-3 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]">
                </div>

                <div class="flex items-center gap-2">
                    <span class="font-semibold text-[#677084]">Estado:</span>
                    <select wire:model.live="filtroEstadoHistorial"
                        class="rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] py-1.5 px-3 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]">
                        <option value="">Todos los estados</option>
                        <option value="ENTREGADO">Entregado</option>
                        <option value="RECIBIDO">Recibido</option>
                        <option value="BORRADOR">Borrador</option>
                    </select>
                </div>
            </div>

            {{-- Tabla / Bitácora de Historial --}}
            <div class="rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#DED1C3] dark:bg-[#2C2723] text-[#677084] font-bold border-b border-[#C7B9AA]">
                                <th class="py-3 px-4">FECHA / HORA</th>
                                <th class="py-3 px-4">RESIDENTE</th>
                                <th class="py-3 px-4">TRANSICIÓN</th>
                                <th class="py-3 px-4">RESUMEN CLÍNICO</th>
                                <th class="py-3 px-4 text-center">ESTADO</th>
                                <th class="py-3 px-4 text-right">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C7B9AA]/40 text-[#304060] dark:text-[#E8DFD5]">
                            @forelse($historialPases as $hPase)
                                <tr class="hover:bg-[#E8DDD1]/50 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold">{{ \Carbon\Carbon::parse($hPase->fecha_hora)->format('d/m/Y H:i') }}</div>
                                        @if($hPase->fecha_hora_recepcion)
                                            <div class="text-[10px] text-[#71876A]">Recibido: {{ \Carbon\Carbon::parse($hPase->fecha_hora_recepcion)->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-bold">
                                        <div>{{ $hPase->residente?->nombre_completo ?? 'Residente' }}</div>
                                        <div class="text-[10px] font-normal text-[#677084]">{{ $hPase->residente?->ubicacion_formateada }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-[11px]">
                                        <div><strong>De:</strong> {{ $hPase->personalSaliente?->usuario?->name ?? 'Personal' }} ({{ $hPase->jornadaSaliente?->turno?->nombre }})</div>
                                        <div><strong>A:</strong> {{ $hPase->personalEntrante?->usuario?->name ?? 'Pendiente' }} ({{ $hPase->jornadaEntrante?->turno?->nombre }})</div>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate" title="{{ $hPase->resumen }}">
                                        {{ $hPase->resumen }}
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($hPase->esRecibido())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#71876A]/20 text-[#71876A]">Recibido</span>
                                        @elseif($hPase->esEntregado())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#A35A44]/20 text-[#A35A44]">Entregado</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#D2A45E]/20 text-[#8C6B32]">Borrador</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <button wire:click="abrirVer('{{ $hPase->cod_pase }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-[#DED1C3] hover:bg-[#C7B9AA] px-2.5 py-1 text-xs font-bold transition">
                                            <i class="ph-bold ph-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-[#677084]">No se encontraron pases registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($historialPases->hasPages())
                    <div class="p-4 bg-[#DED1C3]/60 border-t border-[#C7B9AA]">
                        {{ $historialPases->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 1: PREPARAR PASE (2 ZONAS: CONTEXTO + FORMULARIO)  --}}
    {{-- ======================================================== --}}
    @if($modalPreparar && $residenteSeleccionado)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="relative w-full max-w-5xl max-h-[92vh] flex flex-col rounded-2xl bg-[#F0E8DE] dark:bg-[#26221F] border border-[#C7B9AA] dark:border-[#423B34] shadow-2xl overflow-hidden my-auto"
                @click.outside="$wire.cerrarModalPreparar()">

                {{-- Cabecera --}}
                <div class="flex items-center justify-between border-b border-[#C7B9AA] bg-[#DED1C3] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-handshake text-xl text-[#A35A44]"></i>
                        <div>
                            <h2 class="text-base font-black text-[#304060]">
                                Preparar pase de turno: {{ $residenteSeleccionado->nombre_completo }}
                            </h2>
                            <p class="text-[11px] font-medium text-[#677084]">
                                {{ $residenteSeleccionado->ubicacion_formateada }} · Receptor previsto: <strong>{{ $nombreReceptorDesignado }}</strong>
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModalPreparar" class="text-[#677084] hover:text-[#304060]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Cuerpo Dividido en 2 Zonas --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-y-auto flex-1 text-xs">

                    {{-- ========================================== --}}
                    {{-- ZONA IZQUIERDA: CONTEXTO DEL TURNO (READONLY) --}}
                    {{-- ========================================== --}}
                    <div class="lg:col-span-6 p-5 space-y-4 border-b lg:border-b-0 lg:border-r border-[#C7B9AA] bg-[#F5EFE6] dark:bg-[#201D1A] overflow-y-auto max-h-[70vh]">
                        <div class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-[#884A39]">
                            <i class="ph-bold ph-activity text-sm"></i> Contexto clínico real de la guardia (Solo lectura)
                        </div>

                        {{-- 1. Alertas e Incidentes --}}
                        @if(!empty($contextoClinico['alertas']) || !empty($contextoClinico['incidentes']))
                            <div class="space-y-2 rounded-xl bg-[#FDF2F0] border border-[#C85D52]/40 p-3">
                                <span class="font-bold text-[11px] text-[#C85D52] block uppercase tracking-wider">
                                    Alertas e Incidentes Activos
                                </span>
                                @foreach($contextoClinico['alertas'] as $alt)
                                    <div class="text-[11px] text-[#C85D52] font-semibold flex items-center gap-1">
                                        <i class="ph-bold ph-warning"></i> Alerta ({{ $alt['prioridad'] }}): {{ $alt['titulo'] }}
                                    </div>
                                @endforeach
                                @foreach($contextoClinico['incidentes'] as $inc)
                                    <div class="text-[11px] text-[#A35A44] font-semibold flex items-center gap-1">
                                        <i class="ph-bold ph-warning-octagon"></i> Incidente: {{ $inc['tipo'] }} ({{ $inc['gravedad'] }}) - {{ $inc['descripcion'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- 2. Medicación --}}
                        <div class="space-y-2 rounded-xl bg-[#F0E8DE] border border-[#C7B9AA]/60 p-3">
                            <span class="font-bold text-[11px] text-[#304060] block uppercase tracking-wider">
                                Medicación del Turno
                            </span>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-[#71876A]">Administradas:</span>
                                @forelse($contextoClinico['meds_administradas'] as $ma)
                                    <div class="text-[11px] text-[#304060]">
                                        ✓ {{ $ma['hora'] }} - {{ $ma['medicamento'] }} ({{ $ma['dosis'] }}) - {{ $ma['via'] }}
                                    </div>
                                @empty
                                    <div class="text-[10px] text-[#677084] italic">Sin administraciones registradas en el turno.</div>
                                @endforelse
                            </div>
                            @if(!empty($contextoClinico['meds_pendientes']))
                                <div class="space-y-1 pt-1 border-t border-[#C7B9AA]/40">
                                    <span class="text-[10px] font-bold text-[#C85D52]">Pendientes / Omitidas:</span>
                                    @foreach($contextoClinico['meds_pendientes'] as $mp)
                                        <div class="text-[11px] text-[#C85D52]">
                                            ⚠ {{ $mp['hora'] }} - {{ $mp['medicamento'] }} ({{ $mp['estado'] }})
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- 3. Cuidados e Intervenciones --}}
                        <div class="space-y-2 rounded-xl bg-[#F0E8DE] border border-[#C7B9AA]/60 p-3">
                            <span class="font-bold text-[11px] text-[#304060] block uppercase tracking-wider">
                                Cuidados de Enfermería
                            </span>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-[#71876A]">Realizados:</span>
                                @forelse($contextoClinico['cuidados_realizados'] as $cr)
                                    <div class="text-[11px] text-[#304060]">✓ {{ $cr['intervencion'] }}</div>
                                @empty
                                    <div class="text-[10px] text-[#677084] italic">Sin cuidados registrados como realizados.</div>
                                @endforelse
                            </div>
                            @if(!empty($contextoClinico['cuidados_pendientes']))
                                <div class="space-y-1 pt-1 border-t border-[#C7B9AA]/40">
                                    <span class="text-[10px] font-bold text-[#A35A44]">Pendientes:</span>
                                    @foreach($contextoClinico['cuidados_pendientes'] as $cp)
                                        <div class="text-[11px] text-[#A35A44]">● {{ $cp['intervencion'] }} ({{ $cp['estado'] }})</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- 4. Constantes y Evolución --}}
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-xl bg-[#F0E8DE] border border-[#C7B9AA]/60 p-2.5 space-y-1">
                                <span class="font-bold text-[#304060] block">Signos Vitales:</span>
                                @if($contextoClinico['signos_vitales'])
                                    @php $sv = $contextoClinico['signos_vitales']; @endphp
                                    <div>PA: {{ $sv->presion_sistolica }}/{{ $sv->presion_diastolica }} mmHg</div>
                                    <div>FC: {{ $sv->frecuencia_cardiaca }} lpm</div>
                                    <div>SpO2: {{ $sv->saturacion_oxigeno }}%</div>
                                    <div>Temp: {{ $sv->temperatura }}°C</div>
                                @else
                                    <span class="text-[#677084] italic">Sin toma en turno</span>
                                @endif
                            </div>

                            <div class="rounded-xl bg-[#F0E8DE] border border-[#C7B9AA]/60 p-2.5 space-y-1">
                                <span class="font-bold text-[#304060] block">Dolor y Heridas:</span>
                                <div>Dolor: {{ $contextoClinico['dolor'] ? $contextoClinico['dolor']->escala_eva . '/10' : 'No evaluado' }}</div>
                                <div>Heridas: {{ count($contextoClinico['heridas']) }} activa(s)</div>
                            </div>
                        </div>

                        {{-- 5. Registros de Necesidades Básicas --}}
                        <div class="rounded-xl bg-[#F0E8DE] border border-[#C7B9AA]/60 p-2.5 space-y-1 text-[11px]">
                            <span class="font-bold text-[#304060] block">Evolución de Necesidades Básicas:</span>
                            <div class="grid grid-cols-2 gap-1 text-[10px] text-[#677084]">
                                <div>Ingesta: {{ $contextoClinico['ingesta']?->porcentaje_consumido ?? 'Sin registro' }}%</div>
                                <div>Hidratación: {{ $contextoClinico['hidratacion']?->volumen_ml ?? 'Sin registro' }} ml</div>
                                <div>Eliminación: {{ $contextoClinico['eliminacion']?->tipo ?? 'Sin registro' }}</div>
                                <div>Sueño: {{ $contextoClinico['sueno']?->calidad ?? 'Sin registro' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- ZONA DERECHA: FORMULARIO DEL PASE          --}}
                    {{-- ========================================== --}}
                    <div class="lg:col-span-6 p-5 space-y-4 overflow-y-auto max-h-[70vh] flex flex-col justify-between">
                        <div class="space-y-4">
                            <div class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-[#A35A44]">
                                <i class="ph-bold ph-pencil-simple text-sm"></i> Formulario de transferencia
                            </div>

                            {{-- Estado General --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084]">Estado general del residente al cierre:</label>
                                <input wire:model="estadoGeneral" type="text"
                                    placeholder="Ej: Tranquilo, consciente, afebril, sin cambios agudos..."
                                    class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] py-2 px-3 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]">
                            </div>

                            {{-- Resumen Obligatorio --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#304060]">Resumen clínico de la guardia * (Obligatorio):</label>
                                <textarea wire:model="resumenTurno" rows="4"
                                    placeholder="Hechos relevantes ocurridos, evolución de enfermería durante el turno..."
                                    class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] p-3 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]"></textarea>
                                @error('resumenTurno')
                                    <span class="text-[11px] font-bold text-[#C85D52]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Pendientes --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084]">Acciones o cuidados pendientes:</label>
                                <textarea wire:model="pendientesTurno" rows="2"
                                    placeholder="Controles pendientes, administración de medicación post-turno..."
                                    class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] p-2.5 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]"></textarea>
                            </div>

                            {{-- Vigilancia --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#884A39]">Puntos de vigilancia especial:</label>
                                <textarea wire:model="vigilanciaTurno" rows="2"
                                    placeholder="Vigilar diuresis, riesgo de caída, patrón respiratorio..."
                                    class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] p-2.5 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]"></textarea>
                            </div>

                            {{-- Recomendaciones --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[#677084]">Recomendaciones de cuidado para el relevo:</label>
                                <textarea wire:model="recomendacionTurno" rows="2"
                                    placeholder="Recomendaciones dentro de la competencia de enfermería..."
                                    class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] p-2.5 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]"></textarea>
                            </div>

                            @error('error_general')
                                <div class="rounded-xl bg-[#C85D52]/15 border border-[#C85D52] p-2.5 text-xs font-bold text-[#C85D52]">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Botones de Acción del Formulario --}}
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#C7B9AA]">
                            <button type="button" wire:click="guardarBorrador"
                                class="rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] px-4 py-2 text-xs font-bold text-[#677084] hover:text-[#304060] transition">
                                <i class="ph-bold ph-floppy-disk"></i> Guardar borrador
                            </button>
                            <button type="button" wire:click="confirmarEntrega"
                                class="rounded-xl bg-[#A35A44] hover:bg-[#884A39] text-white px-5 py-2 text-xs font-bold transition shadow-xs">
                                <i class="ph-bold ph-paper-plane-right"></i> Confirmar entrega
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 2: REVISAR Y RECIBIR PASE                          --}}
    {{-- ======================================================== --}}
    @if($modalRevisar && $paseSeleccionado)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl rounded-2xl bg-[#F0E8DE] border border-[#C7B9AA] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalRevisar()">

                <div class="flex items-center justify-between border-b border-[#C7B9AA] bg-[#DED1C3] px-5 py-4">
                    <div>
                        <h3 class="text-sm font-black text-[#304060]">
                            Confirmar recepción de guardia: {{ $paseSeleccionado->residente?->nombre_completo }}
                        </h3>
                        <p class="text-[11px] text-[#677084]">
                            Entregado por: {{ $paseSeleccionado->personalSaliente?->usuario?->name }} ({{ $paseSeleccionado->jornadaSaliente?->turno?->nombre }})
                        </p>
                    </div>
                    <button wire:click="cerrarModalRevisar" class="text-[#677084] hover:text-[#304060]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs max-h-[70vh] overflow-y-auto">
                    {{-- Ficha del Pase Entregado --}}
                    <div class="space-y-2 rounded-xl bg-[#F5EFE6] border border-[#C7B9AA] p-3.5">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Estado general:</span>
                            <span class="font-bold text-[#304060]">{{ $paseSeleccionado->estado_general ?: 'Sin especificar' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[#677084] block">Resumen clínico del turno saliente:</span>
                            <p class="text-[#304060] whitespace-pre-line leading-relaxed">{{ $paseSeleccionado->resumen }}</p>
                        </div>
                        @if($paseSeleccionado->pendientes)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#A35A44] block">Pendientes:</span>
                                <p class="text-[#304060]">{{ $paseSeleccionado->pendientes }}</p>
                            </div>
                        @endif
                        @if($paseSeleccionado->vigilancia)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#884A39] block">Puntos de vigilancia:</span>
                                <p class="text-[#304060]">{{ $paseSeleccionado->vigilancia }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Campo Editable: Observación de Recepción --}}
                    <div class="space-y-1">
                        <label class="font-bold text-[#304060]">Observación de recepción (opcional):</label>
                        <textarea wire:model="observacionRecepcion" rows="2"
                            placeholder="Notas al momento de asumir el cuidado del residente..."
                            class="w-full rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] p-2.5 text-xs text-[#304060] focus:outline-none focus:border-[#A35A44]"></textarea>
                    </div>

                    @error('error_recepcion')
                        <div class="rounded-xl bg-[#C85D52]/15 border border-[#C85D52] p-2.5 text-xs font-bold text-[#C85D52]">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[#C7B9AA] bg-[#DED1C3] px-5 py-3">
                    <button wire:click="cerrarModalRevisar"
                        class="rounded-xl border border-[#C7B9AA] bg-[#F0E8DE] px-4 py-2 text-xs font-bold text-[#677084]">
                        Cancelar
                    </button>
                    <button wire:click="confirmarRecepcion"
                        class="rounded-xl bg-[#71876A] hover:bg-[#5C7056] text-white px-5 py-2 text-xs font-bold transition">
                        ✓ Confirmar recepción de guardia
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 3: VER DETALLE (SOLO LECTURA)                      --}}
    {{-- ======================================================== --}}
    @if($modalVer && $paseSeleccionado)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl rounded-2xl bg-[#F0E8DE] border border-[#C7B9AA] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalVer()">

                <div class="flex items-center justify-between border-b border-[#C7B9AA] bg-[#DED1C3] px-5 py-4">
                    <div>
                        <h3 class="text-sm font-black text-[#304060]">
                            Detalle de pase de turno: {{ $paseSeleccionado->residente?->nombre_completo }}
                        </h3>
                        <p class="text-[11px] text-[#677084]">
                            Código: {{ $paseSeleccionado->cod_pase }} · Estado: <strong>{{ $paseSeleccionado->estado }}</strong>
                        </p>
                    </div>
                    <button wire:click="cerrarModalVer" class="text-[#677084] hover:text-[#304060]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-3 text-xs max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-2 bg-[#E4D8CC]/50 p-3 rounded-xl">
                        <div><strong>Saliente:</strong> {{ $paseSeleccionado->personalSaliente?->usuario?->name }} ({{ $paseSeleccionado->jornadaSaliente?->turno?->nombre }})</div>
                        <div><strong>Entrante:</strong> {{ $paseSeleccionado->personalEntrante?->usuario?->name ?? 'Pendiente' }} ({{ $paseSeleccionado->jornadaEntrante?->turno?->nombre }})</div>
                        <div><strong>Fecha entrega:</strong> {{ \Carbon\Carbon::parse($paseSeleccionado->fecha_hora)->format('d/m/Y H:i') }}</div>
                        <div><strong>Fecha recepción:</strong> {{ $paseSeleccionado->fecha_hora_recepcion ? \Carbon\Carbon::parse($paseSeleccionado->fecha_hora_recepcion)->format('d/m/Y H:i') : 'Pendiente' }}</div>
                    </div>

                    <div>
                        <span class="font-bold text-[#677084] block">Estado General:</span>
                        <p class="text-[#304060]">{{ $paseSeleccionado->estado_general ?: 'Sin especificar' }}</p>
                    </div>

                    <div>
                        <span class="font-bold text-[#304060] block">Resumen Clínico:</span>
                        <p class="text-[#304060] whitespace-pre-line leading-relaxed bg-[#F5EFE6] p-3 rounded-xl border border-[#C7B9AA]/40">{{ $paseSeleccionado->resumen }}</p>
                    </div>

                    @if($paseSeleccionado->pendientes)
                        <div>
                            <span class="font-bold text-[#A35A44] block">Pendientes:</span>
                            <p class="text-[#304060]">{{ $paseSeleccionado->pendientes }}</p>
                        </div>
                    @endif

                    @if($paseSeleccionado->vigilancia)
                        <div>
                            <span class="font-bold text-[#884A39] block">Vigilancia:</span>
                            <p class="text-[#304060]">{{ $paseSeleccionado->vigilancia }}</p>
                        </div>
                    @endif

                    @if($paseSeleccionado->recomendacion)
                        <div>
                            <span class="font-bold text-[#677084] block">Recomendación:</span>
                            <p class="text-[#304060]">{{ $paseSeleccionado->recomendacion }}</p>
                        </div>
                    @endif

                    @if($paseSeleccionado->observacion_recepcion)
                        <div class="rounded-xl bg-[#71876A]/10 border border-[#71876A]/30 p-2.5">
                            <span class="font-bold text-[#71876A] block">Nota de recepción del relevo:</span>
                            <p class="text-[#304060]">{{ $paseSeleccionado->observacion_recepcion }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end border-t border-[#C7B9AA] bg-[#DED1C3] px-5 py-3">
                    <button wire:click="cerrarModalVer"
                        class="rounded-xl bg-[#A35A44] hover:bg-[#884A39] text-white px-4 py-2 text-xs font-bold transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
