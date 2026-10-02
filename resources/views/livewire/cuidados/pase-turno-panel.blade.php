<!-- rm-filter-bar -->
<div class="space-y-6 font-sans text-[var(--rm-text-primary)]" >

    {{-- CABECERA INSTITUCIONAL --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] p-5 sm:p-6 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:bg-[var(--rm-action-primary-soft)]">
                    <i class="ph-bold ph-arrows-left-right text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                        Pases de turno <span class="sr-only">Pase de Turno</span>
                    </h1>
                    <p class="text-xs font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-primary)]">
                        Continuidad de cuidados y comunicación entre jornadas
                    </p>
                </div>
            </div>
        </div>

        {{-- Selector de Pestañas Principales --}}
        <div class="inline-flex rounded-xl bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] p-1 border border-[var(--rm-border)] dark:border-[var(--rm-border)]">
            <button wire:click="cambiarTab('entrega')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tabActivo === 'entrega' ? 'bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)] shadow-sm' : 'text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)]' }}">
                <i class="ph-bold ph-handshake text-sm"></i>
                <span>Transferencia de guardia</span>
            </button>
            <button wire:click="cambiarTab('historial')"
                class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition {{ $tabActivo === 'historial' ? 'bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)] shadow-sm' : 'text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)]' }}">
                <i class="ph-bold ph-clock-counter-clockwise text-sm"></i>
                <span>Historial de pases</span>
            </button>
        </div>
    </div>

    {{-- MENSAJES FLASH --}}
    @if(session()->has('mensaje'))
        <div class="flex items-center justify-between rounded-xl bg-[var(--rm-action-primary-soft)] border border-[var(--rm-action-primary)]/40 px-4 py-3 text-xs font-bold text-[var(--rm-action-primary)] dark:text-[var(--rm-success)]">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-check-circle text-base"></i>
                <span>{{ session('mensaje') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-[var(--rm-action-primary)] hover:opacity-75">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
    @endif

    {{-- BARRA DE CONTEXTO ASISTENCIAL (SOLO LECTURA, DETERMINADA POR EL SISTEMA) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-2xl bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] p-4 text-xs">
        {{-- Jornada actual --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] text-[var(--rm-warning)] shrink-0 border border-[var(--rm-border)]/60">
                <i class="ph-bold ph-sun text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">Jornada actual (Saliente):</span>
                <span class="font-bold text-xs text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                    {{ $jornadaSaliente ? (($jornadaSaliente->turno?->nombre ?? 'Guardia activa') . ' · ' . \Carbon\Carbon::parse($jornadaSaliente->fecha_jornada)->format('d/m/Y')) : 'Sin jornada activa asignada' }}
                </span>
            </div>
        </div>

        {{-- Profesional autenticado --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)] shrink-0 border border-[var(--rm-border)]/60">
                <i class="ph-bold ph-user text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">Profesional responsable:</span>
                <span class="font-bold text-xs text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                    {{ auth()->user()->name ?? 'Enfermería' }}
                </span>
            </div>
        </div>

        {{-- Siguiente jornada --}}
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] text-[var(--rm-action-primary)] shrink-0 border border-[var(--rm-border)]/60">
                <i class="ph-bold ph-arrow-right text-lg"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">Siguiente jornada (Entrante):</span>
                <span class="font-bold text-xs text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">
                    {{ $jornadaEntrante ? (($jornadaEntrante->turno?->nombre ?? 'Siguiente guardia') . ' · ' . \Carbon\Carbon::parse($jornadaEntrante->fecha_jornada)->format('d/m/Y')) : 'Sin jornada entrante planificada' }}
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
            <div class="space-y-3 rounded-2xl bg-[var(--rm-surface-soft)]/70 dark:bg-[var(--rm-surface)]/70 border border-[var(--rm-border)] dark:border-[var(--rm-border)] p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-tray text-base text-[var(--rm-warning)]"></i>
                        <h2 class="text-sm font-black text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                            Pases pendientes de recibir ({{ count($pasesPendientesRecibir) }})
                        </h2>
                    </div>
                    <span class="text-[11px] font-semibold text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">
                        Confirme la recepción para asumir la continuidad asistencial
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($pasesPendientesRecibir as $paseRec)
                        <div class="rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] border border-[var(--rm-border)] p-4 space-y-3 shadow-xs">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-xs text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                                        {{ $paseRec->residente?->nombre_completo ?? 'Residente' }}
                                    </h3>
                                    <span class="text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                        {{ $paseRec->residente?->ubicacion_formateada ?? 'Sin ubicación' }}
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-[var(--rm-warning-soft)] text-[var(--rm-warning)]">
                                    Entregado
                                </span>
                            </div>

                            <div class="text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] space-y-0.5 border-t border-[var(--rm-border)]/40 pt-2">
                                <div><strong class="text-[var(--rm-text-primary)]">De:</strong> {{ $paseRec->personalSaliente?->usuario?->name ?? 'Enfermero saliente' }}</div>
                                <div><strong class="text-[var(--rm-text-primary)]">Transición:</strong> {{ $paseRec->jornadaSaliente?->turno?->nombre }} → {{ $paseRec->jornadaEntrante?->turno?->nombre }}</div>
                                <p class="line-clamp-2 italic text-[var(--rm-text-primary)] mt-1">"{{ $paseRec->resumen }}"</p>
                            </div>

                            <button wire:click="abrirRevisarPase('{{ $paseRec->cod_pase }}')"
                                class="w-full flex items-center justify-center gap-1.5 rounded-lg bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white py-1.5 text-xs font-bold transition">
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
                    <h2 class="text-base font-black text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                        Residentes a entregar
                    </h2>
                    <span class="text-xs text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                        ({{ count($residentesAEntregar) }} asignados a su guardia)
                    </span>
                </div>

                {{-- Subfiltros --}}
                <div class="flex items-center gap-1.5 text-xs">
                    <button wire:click="$set('filtroEntrega', 'todos')"
                        class="px-3 py-1 rounded-lg font-bold transition {{ $filtroEntrega === 'todos' ? 'bg-[var(--rm-action-primary)] text-white shadow-xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
                        Todos
                    </button>
                    <button wire:click="$set('filtroEntrega', 'criticos')"
                        class="px-3 py-1 rounded-lg font-bold transition {{ $filtroEntrega === 'criticos' ? 'bg-[var(--rm-danger)] text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
                        Con Alerta / Incidente
                    </button>
                    <button wire:click="$set('filtroEntrega', 'pendientes')"
                        class="px-3 py-1 rounded-lg font-bold transition {{ $filtroEntrega === 'pendientes' ? 'bg-[var(--rm-warning)] text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]' }}">
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

                    <div class="rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border {{ $esCritico ? 'border-[var(--rm-danger)]/70 bg-[var(--rm-danger-soft)]/80 dark:bg-[var(--rm-danger)]/80' : 'border-[var(--rm-border)] dark:border-[var(--rm-border)]' }} p-4 sm:p-5 shadow-sm space-y-3 flex flex-col justify-between">
                        <div class="space-y-2">
                            {{-- Cabecera Tarjeta: Residente y Ubicación --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-sm text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]">
                                        {{ $r->nombre_completo }}
                                    </h3>
                                    <span class="text-xs text-[var(--rm-warning)] dark:text-[var(--rm-warning)] font-medium block">
                                        {{ $r->ubicacion_formateada }}
                                    </span>
                                </div>

                                {{-- Badge de Estado del Pase --}}
                                @if($esRecibido)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/40">
                                        ✓ Recibido
                                    </span>
                                @elseif($esEntregado)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border border-[var(--rm-warning)]/40">
                                        ● Entregado
                                    </span>
                                @elseif($esBorrador)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border border-[var(--rm-warning)]/40">
                                        ✎ Borrador
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-text-secondary)]/20 text-[var(--rm-text-secondary)]">
                                        Pendiente
                                    </span>
                                @endif
                            </div>

                            {{-- Profesional Receptor Resuelto Automáticamente --}}
                            <div class="rounded-xl bg-[var(--rm-surface-soft)]/50 dark:bg-[var(--rm-surface)]/50 p-2.5 border border-[var(--rm-border)]/50 text-xs">
                                <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">
                                    Enfermero/a receptor/a (Jornada entrante):
                                </span>
                                <span class="font-bold {{ $item['tiene_receptor'] ? 'text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)]' : 'text-[var(--rm-warning)] italic' }}">
                                    {{ $item['nombre_receptor'] }}
                                </span>
                            </div>

                            {{-- Alertas / Incidentes vigentes --}}
                            @if($esCritico)
                                <div class="space-y-1">
                                    @if($item['alertas_count'] > 0)
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[var(--rm-danger)]">
                                            <i class="ph-bold ph-warning-circle"></i>
                                            <span>{{ $item['alertas_count'] }} alerta(s) clínica(s) activa(s)</span>
                                        </div>
                                    @endif
                                    @if($item['incidentes_count'] > 0)
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[var(--rm-warning)]">
                                            <i class="ph-bold ph-warning-octagon"></i>
                                            <span>{{ $item['incidentes_count'] }} incidente(s) en la jornada</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Botón de Acción --}}
                        <div class="pt-2 border-t border-[var(--rm-border)]/40 dark:border-[var(--rm-border)]">
                            @if($esRecibido || $esEntregado)
                                <button wire:click="abrirVer('{{ $pase->cod_pase }}')"
                                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] hover:bg-[var(--rm-border)] py-2 text-xs font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-surface)] transition">
                                    <i class="ph-bold ph-eye"></i> Ver pase registrado
                                </button>
                            @else
                                <button wire:click="abrirPrepararPase('{{ $r->cod_residente }}')"
                                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] text-white py-2 text-xs font-bold transition shadow-xs">
                                    <i class="ph-bold ph-notepad"></i>
                                    <span>{{ $esBorrador ? 'Continuar preparando pase' : 'Preparar pase' }}</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] p-12 text-center text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
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
            {{-- Filtros del Historial formato alertas --}}
            <x-ui.filter-bar class="mb-4">
                <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
                    {{-- Búsqueda textual --}}
                    <div class="lg:col-span-6 relative flex items-center">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                            <i class="ph-bold ph-magnifying-glass text-base"></i>
                        </span>
                        <input wire:model.live.debounce.300ms="searchHistorial" type="text"
                            placeholder="Buscar residente o resumen..."
                            class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                        @if(!empty($searchHistorial))
                            <button type="button" wire:click="$set('searchHistorial', '')" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer" title="Limpiar búsqueda">
                                <i class="ph-bold ph-x-circle text-base"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Fecha --}}
                    <div class="lg:col-span-3">
                        <input wire:model.live="filtroFechaHistorial" type="date" title="Filtrar por fecha"
                            class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                    </div>

                    {{-- Estado --}}
                    <div class="lg:col-span-3">
                        <select wire:model.live="filtroEstadoHistorial"
                            class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                            <option value="">Todos los estados</option>
                            <option value="ENTREGADO">Entregado</option>
                            <option value="RECIBIDO">Recibido</option>
                            <option value="BORRADOR">Borrador</option>
                        </select>
                    </div>
                </div>

                {{-- Fila de chips de filtros activos --}}
                @php
                    $hasFiltrosPase = !empty($searchHistorial) || !empty($filtroFechaHistorial) || !empty($filtroEstadoHistorial);
                @endphp
                @if($hasFiltrosPase)
                    <div class="rm-filter-bar__active">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rm-filter-bar__active-label">
                                <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                            </span>
                            @if(!empty($searchHistorial))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                                    <span>Búsqueda: "{{ Str::limit($searchHistorial, 16) }}"</span>
                                    <button type="button" wire:click="$set('searchHistorial', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                                </span>
                            @endif
                            @if(!empty($filtroFechaHistorial))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                                    <span>Fecha: {{ $filtroFechaHistorial }}</span>
                                    <button type="button" wire:click="$set('filtroFechaHistorial', null)" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                                </span>
                            @endif
                            @if(!empty($filtroEstadoHistorial))
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning-soft)] border border-[var(--rm-warning)] text-[11px] font-bold text-[var(--rm-warning)]">
                                    <span>Estado: {{ $filtroEstadoHistorial }}</span>
                                    <button type="button" wire:click="$set('filtroEstadoHistorial', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2.5">
                            <button type="button"
                                wire:click="$set('searchHistorial', ''); $set('filtroFechaHistorial', null); $set('filtroEstadoHistorial', '');"
                                class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-primary-soft)] hover:bg-[var(--rm-primary)] hover:text-white text-[var(--rm-primary)] border border-[var(--rm-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                                <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                <span>Limpiar filtros</span>
                            </button>
                        </div>
                    </div>
                @endif
            </x-ui.filter-bar>

            {{-- Tabla / Bitácora de Historial --}}
            <div class="rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="rm-data-table rm-data-table--actions w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-secondary)] font-bold border-b border-[var(--rm-border)]">
                                <th class="py-3 px-4">FECHA / HORA</th>
                                <th class="py-3 px-4">RESIDENTE</th>
                                <th class="py-3 px-4">TRANSICIÓN</th>
                                <th class="py-3 px-4">RESUMEN CLÍNICO</th>
                                <th class="py-3 px-4 text-center">ESTADO</th>
                                <th class="py-3 px-4 text-right">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--rm-border)]/40 text-[var(--rm-text-primary)]">
                            @forelse($historialPases as $hPase)
                                <tr class="hover:bg-[var(--rm-surface)]/50 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold">{{ \Carbon\Carbon::parse($hPase->fecha_hora)->format('d/m/Y H:i') }}</div>
                                        @if($hPase->fecha_hora_recepcion)
                                            <div class="text-[10px] text-[var(--rm-action-primary)]">Recibido: {{ \Carbon\Carbon::parse($hPase->fecha_hora_recepcion)->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-bold">
                                        <div>{{ $hPase->residente?->nombre_completo ?? 'Residente' }}</div>
                                        <div class="text-[10px] font-normal text-[var(--rm-text-secondary)]">{{ $hPase->residente?->ubicacion_formateada }}</div>
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
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)]">Recibido</span>
                                        @elseif($hPase->esEntregado())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-warning-soft)] text-[var(--rm-warning)]">Entregado</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--rm-warning-soft)] text-[var(--rm-warning)]">Borrador</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <button wire:click="abrirVer('{{ $hPase->cod_pase }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-border)] px-2.5 py-1 text-xs font-bold transition">
                                            <i class="ph-bold ph-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-[var(--rm-text-secondary)]">No se encontraron pases registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($historialPases->hasPages())
                    <div class="p-4 bg-[var(--rm-surface-soft)]/60 border-t border-[var(--rm-border)]">
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
            <div class="relative w-full max-w-5xl max-h-[92vh] flex flex-col rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-2xl overflow-hidden my-auto"
                @click.outside="$wire.cerrarModalPreparar()">

                {{-- Cabecera --}}
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-5 py-4">
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-handshake text-xl text-[var(--rm-warning)]"></i>
                        <div>
                            <h2 class="text-base font-black text-[var(--rm-text-primary)]">
                                Preparar pase de turno: {{ $residenteSeleccionado->nombre_completo }}
                            </h2>
                            <p class="text-[11px] font-medium text-[var(--rm-text-secondary)]">
                                {{ $residenteSeleccionado->ubicacion_formateada }} · Receptor previsto: <strong>{{ $nombreReceptorDesignado }}</strong>
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModalPreparar" class="text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Cuerpo Dividido en 2 Zonas --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-y-auto flex-1 text-xs">

                    {{-- ========================================== --}}
                    {{-- ZONA IZQUIERDA: CONTEXTO DEL TURNO (READONLY) --}}
                    {{-- ========================================== --}}
                    <div class="lg:col-span-6 p-5 space-y-4 border-b lg:border-b-0 lg:border-r border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface-soft)] overflow-y-auto max-h-[70vh]">
                        <div class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-[var(--rm-warning)]">
                            <i class="ph-bold ph-activity text-sm"></i> Contexto clínico real de la guardia (Solo lectura)
                        </div>

                        {{-- 1. Alertas e Incidentes --}}
                        @if(!empty($contextoClinico['alertas']) || !empty($contextoClinico['incidentes']))
                            <div class="space-y-2 rounded-xl bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 p-3">
                                <span class="font-bold text-[11px] text-[var(--rm-danger)] block uppercase tracking-wider">
                                    Alertas e Incidentes Activos
                                </span>
                                @foreach($contextoClinico['alertas'] as $alt)
                                    <div class="text-[11px] text-[var(--rm-danger)] font-semibold flex items-center gap-1">
                                        <i class="ph-bold ph-warning"></i> Alerta ({{ $alt['prioridad'] }}): {{ $alt['titulo'] }}
                                    </div>
                                @endforeach
                                @foreach($contextoClinico['incidentes'] as $inc)
                                    <div class="text-[11px] text-[var(--rm-warning)] font-semibold flex items-center gap-1">
                                        <i class="ph-bold ph-warning-octagon"></i> Incidente: {{ $inc['tipo'] }} ({{ $inc['gravedad'] }}) - {{ $inc['descripcion'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- 2. Medicación --}}
                        <div class="space-y-2 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 p-3">
                            <span class="font-bold text-[11px] text-[var(--rm-text-primary)] block uppercase tracking-wider">
                                Medicación del Turno
                            </span>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-[var(--rm-action-primary)]">Administradas:</span>
                                @forelse($contextoClinico['meds_administradas'] as $ma)
                                    <div class="text-[11px] text-[var(--rm-text-primary)]">
                                        ✓ {{ $ma['hora'] }} - {{ $ma['medicamento'] }} ({{ $ma['dosis'] }}) - {{ $ma['via'] }}
                                    </div>
                                @empty
                                    <div class="text-[10px] text-[var(--rm-text-secondary)] italic">Sin administraciones registradas en el turno.</div>
                                @endforelse
                            </div>
                            @if(!empty($contextoClinico['meds_pendientes']))
                                <div class="space-y-1 pt-1 border-t border-[var(--rm-border)]/40">
                                    <span class="text-[10px] font-bold text-[var(--rm-danger)]">Pendientes / Omitidas:</span>
                                    @foreach($contextoClinico['meds_pendientes'] as $mp)
                                        <div class="text-[11px] text-[var(--rm-danger)]">
                                            ⚠ {{ $mp['hora'] }} - {{ $mp['medicamento'] }} ({{ $mp['estado'] }})
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- 3. Cuidados e Intervenciones --}}
                        <div class="space-y-2 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 p-3">
                            <span class="font-bold text-[11px] text-[var(--rm-text-primary)] block uppercase tracking-wider">
                                Cuidados de Enfermería
                            </span>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-[var(--rm-action-primary)]">Realizados:</span>
                                @forelse($contextoClinico['cuidados_realizados'] as $cr)
                                    <div class="text-[11px] text-[var(--rm-text-primary)]">✓ {{ $cr['intervencion'] }}</div>
                                @empty
                                    <div class="text-[10px] text-[var(--rm-text-secondary)] italic">Sin cuidados registrados como realizados.</div>
                                @endforelse
                            </div>
                            @if(!empty($contextoClinico['cuidados_pendientes']))
                                <div class="space-y-1 pt-1 border-t border-[var(--rm-border)]/40">
                                    <span class="text-[10px] font-bold text-[var(--rm-warning)]">Pendientes:</span>
                                    @foreach($contextoClinico['cuidados_pendientes'] as $cp)
                                        <div class="text-[11px] text-[var(--rm-warning)]">● {{ $cp['intervencion'] }} ({{ $cp['estado'] }})</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- 4. Constantes y Evolución --}}
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 p-2.5 space-y-1">
                                <span class="font-bold text-[var(--rm-text-primary)] block">Signos Vitales:</span>
                                @if($contextoClinico['signos_vitales'])
                                    @php $sv = $contextoClinico['signos_vitales']; @endphp
                                    <div>PA: {{ $sv->presion_sistolica }}/{{ $sv->presion_diastolica }} mmHg</div>
                                    <div>FC: {{ $sv->frecuencia_cardiaca }} lpm</div>
                                    <div>SpO2: {{ $sv->saturacion_oxigeno }}%</div>
                                    <div>Temp: {{ $sv->temperatura }}°C</div>
                                @else
                                    <span class="text-[var(--rm-text-secondary)] italic">Sin toma en turno</span>
                                @endif
                            </div>

                            <div class="rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 p-2.5 space-y-1">
                                <span class="font-bold text-[var(--rm-text-primary)] block">Dolor y Heridas:</span>
                                <div>Dolor: {{ $contextoClinico['dolor'] ? $contextoClinico['dolor']->escala_eva . '/10' : 'No evaluado' }}</div>
                                <div>Heridas: {{ count($contextoClinico['heridas']) }} activa(s)</div>
                            </div>
                        </div>

                        {{-- 5. Registros de Necesidades Básicas --}}
                        <div class="rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 p-2.5 space-y-1 text-[11px]">
                            <span class="font-bold text-[var(--rm-text-primary)] block">Evolución de Necesidades Básicas:</span>
                            <div class="grid grid-cols-2 gap-1 text-[10px] text-[var(--rm-text-secondary)]">
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
                            <div class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-[var(--rm-warning)]">
                                <i class="ph-bold ph-pencil-simple text-sm"></i> Formulario de transferencia
                            </div>

                            {{-- Estado General --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)]">Estado general del residente al cierre:</label>
                                <input wire:model="estadoGeneral" type="text"
                                    placeholder="Ej: Tranquilo, consciente, afebril, sin cambios agudos..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] py-2 px-3 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-warning)]">
                            </div>

                            {{-- Resumen Obligatorio --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-primary)]">Resumen clínico de la guardia * (Obligatorio):</label>
                                <textarea wire:model="resumenTurno" rows="4"
                                    placeholder="Hechos relevantes ocurridos, evolución de enfermería durante el turno..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-warning)]"></textarea>
                                @error('resumenTurno')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Pendientes --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)]">Acciones o cuidados pendientes:</label>
                                <textarea wire:model="pendientesTurno" rows="2"
                                    placeholder="Controles pendientes, administración de medicación post-turno..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-warning)]"></textarea>
                            </div>

                            {{-- Vigilancia --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-warning)]">Puntos de vigilancia especial:</label>
                                <textarea wire:model="vigilanciaTurno" rows="2"
                                    placeholder="Vigilar diuresis, riesgo de caída, patrón respiratorio..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-warning)]"></textarea>
                            </div>

                            {{-- Recomendaciones --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)]">Recomendaciones de cuidado para el relevo:</label>
                                <textarea wire:model="recomendacionTurno" rows="2"
                                    placeholder="Recomendaciones dentro de la competencia de enfermería..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-warning)]"></textarea>
                            </div>

                            @error('error_general')
                                <div class="rounded-xl bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)] p-2.5 text-xs font-bold text-[var(--rm-danger)]">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Botones de Acción del Formulario --}}
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[var(--rm-border)]">
                            <button type="button" wire:click="guardarBorrador"
                                class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-4 py-2 text-xs font-bold text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] transition">
                                <i class="ph-bold ph-floppy-disk"></i> Guardar borrador
                            </button>
                            <button type="button" wire:click="confirmarEntrega"
                                class="rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] text-white px-5 py-2 text-xs font-bold transition shadow-xs">
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
            <div class="relative w-full max-w-2xl rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalRevisar()">

                <div class="flex items-center justify-between border-b border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-5 py-4">
                    <div>
                        <h3 class="text-sm font-black text-[var(--rm-text-primary)]">
                            Confirmar recepción de guardia: {{ $paseSeleccionado->residente?->nombre_completo }}
                        </h3>
                        <p class="text-[11px] text-[var(--rm-text-secondary)]">
                            Entregado por: {{ $paseSeleccionado->personalSaliente?->usuario?->name }} ({{ $paseSeleccionado->jornadaSaliente?->turno?->nombre }})
                        </p>
                    </div>
                    <button wire:click="cerrarModalRevisar" class="text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs max-h-[70vh] overflow-y-auto">
                    {{-- Ficha del Pase Entregado --}}
                    <div class="space-y-2 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] p-3.5">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] block">Estado general:</span>
                            <span class="font-bold text-[var(--rm-text-primary)]">{{ $paseSeleccionado->estado_general ?: 'Sin especificar' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] block">Resumen clínico del turno saliente:</span>
                            <p class="text-[var(--rm-text-primary)] whitespace-pre-line leading-relaxed">{{ $paseSeleccionado->resumen }}</p>
                        </div>
                        @if($paseSeleccionado->pendientes)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[var(--rm-warning)] block">Pendientes:</span>
                                <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->pendientes }}</p>
                            </div>
                        @endif
                        @if($paseSeleccionado->vigilancia)
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[var(--rm-warning)] block">Puntos de vigilancia:</span>
                                <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->vigilancia }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Campo Editable: Observación de Recepción --}}
                    <div class="space-y-1">
                        <label class="font-bold text-[var(--rm-text-primary)]">Observación de recepción (opcional):</label>
                        <textarea wire:model="observacionRecepcion" rows="2"
                            placeholder="Notas al momento de asumir el cuidado del residente..."
                            class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-2.5 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-warning)]"></textarea>
                    </div>

                    @error('error_recepcion')
                        <div class="rounded-xl bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)] p-2.5 text-xs font-bold text-[var(--rm-danger)]">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-5 py-3">
                    <button wire:click="cerrarModalRevisar"
                        class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-4 py-2 text-xs font-bold text-[var(--rm-text-secondary)]">
                        Cancelar
                    </button>
                    <button wire:click="confirmarRecepcion"
                        class="rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white px-5 py-2 text-xs font-bold transition">
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
            <div class="relative w-full max-w-2xl rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalVer()">

                <div class="flex items-center justify-between border-b border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-5 py-4">
                    <div>
                        <h3 class="text-sm font-black text-[var(--rm-text-primary)]">
                            Detalle de pase de turno: {{ $paseSeleccionado->residente?->nombre_completo }}
                        </h3>
                        <p class="text-[11px] text-[var(--rm-text-secondary)]">
                            Código: {{ $paseSeleccionado->cod_pase }} · Estado: <strong>{{ $paseSeleccionado->estado }}</strong>
                        </p>
                    </div>
                    <button wire:click="cerrarModalVer" class="text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-3 text-xs max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-2 bg-[var(--rm-surface-soft)]/50 p-3 rounded-xl">
                        <div><strong>Saliente:</strong> {{ $paseSeleccionado->personalSaliente?->usuario?->name }} ({{ $paseSeleccionado->jornadaSaliente?->turno?->nombre }})</div>
                        <div><strong>Entrante:</strong> {{ $paseSeleccionado->personalEntrante?->usuario?->name ?? 'Pendiente' }} ({{ $paseSeleccionado->jornadaEntrante?->turno?->nombre }})</div>
                        <div><strong>Fecha entrega:</strong> {{ \Carbon\Carbon::parse($paseSeleccionado->fecha_hora)->format('d/m/Y H:i') }}</div>
                        <div><strong>Fecha recepción:</strong> {{ $paseSeleccionado->fecha_hora_recepcion ? \Carbon\Carbon::parse($paseSeleccionado->fecha_hora_recepcion)->format('d/m/Y H:i') : 'Pendiente' }}</div>
                    </div>

                    <div>
                        <span class="font-bold text-[var(--rm-text-secondary)] block">Estado General:</span>
                        <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->estado_general ?: 'Sin especificar' }}</p>
                    </div>

                    <div>
                        <span class="font-bold text-[var(--rm-text-primary)] block">Resumen Clínico:</span>
                        <p class="text-[var(--rm-text-primary)] whitespace-pre-line leading-relaxed bg-[var(--rm-surface)] p-3 rounded-xl border border-[var(--rm-border)]/40">{{ $paseSeleccionado->resumen }}</p>
                    </div>

                    @if($paseSeleccionado->pendientes)
                        <div>
                            <span class="font-bold text-[var(--rm-warning)] block">Pendientes:</span>
                            <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->pendientes }}</p>
                        </div>
                    @endif

                    @if($paseSeleccionado->vigilancia)
                        <div>
                            <span class="font-bold text-[var(--rm-warning)] block">Vigilancia:</span>
                            <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->vigilancia }}</p>
                        </div>
                    @endif

                    @if($paseSeleccionado->recomendacion)
                        <div>
                            <span class="font-bold text-[var(--rm-text-secondary)] block">Recomendación:</span>
                            <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->recomendacion }}</p>
                        </div>
                    @endif

                    @if($paseSeleccionado->observacion_recepcion)
                        <div class="rounded-xl bg-[var(--rm-action-primary-soft)] border border-[var(--rm-action-primary)]/30 p-2.5">
                            <span class="font-bold text-[var(--rm-action-primary)] block">Nota de recepción del relevo:</span>
                            <p class="text-[var(--rm-text-primary)]">{{ $paseSeleccionado->observacion_recepcion }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end border-t border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-5 py-3">
                    <button wire:click="cerrarModalVer"
                        class="rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] text-white px-4 py-2 text-xs font-bold transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
