<!-- rm-filter-bar -->
<div class="space-y-4 font-sans bg-transparent p-3 sm:p-5">
    {{-- ==================================================
         1. CABECERA INSTITUCIONAL Y NAVEGACIÓN DE TURNO
         ================================================== --}}
    <x-ui.collection-header :title="$esSuperAdmin ? 'Agenda institucional de Enfermería' : 'Agenda operativa de cuidados'" :subtitle="$esSuperAdmin ? 'Supervisión de alertas, medicación y cuidados de los residentes.' : 'Programación asistencial del turno basada en planes clínicos individuales.'" icon="ph-calendar-check" eyebrow="Enfermería" :date="today()">
      <x-slot:actions>
            <a href="{{ route('admin.enfermeria.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)] transition">
                <i class="ph ph-squares-four text-sm text-[var(--rm-action-primary-ink)]"></i>
                <span>{{ $esSuperAdmin ? 'Resumen global' : 'Mi turno' }}</span>
            </a>

            <a href="{{ route('admin.enfermeria.pacientes') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)] transition">
                <i class="ph ph-users text-sm text-[var(--rm-action-primary)]"></i>
                <span>{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis residentes' }}</span>
            </a>

            <a href="{{ route('admin.enfermeria.pase-turno') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)] transition">
                <i class="ph ph-arrows-clockwise text-sm text-[var(--rm-action-primary)]"></i>
                <span>Pases de turno</span>
            </a>

            @if(!$esSuperAdmin && $turno && !$recepcion)
                <button type="button" 
                        wire:click="recibirTurno" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-action-primary)] hover:bg-[var(--rm-success)] text-white shadow-2xs transition cursor-pointer">
                    <i class="ph ph-handshake text-sm"></i>
                    <span>Recibir turno</span>
                </button>
            @elseif($recepcion)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[var(--rm-success-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-success)] dark:text-[var(--rm-success)] border border-[var(--rm-success-soft)] dark:border-[var(--rm-action-primary)]/40">
                    <i class="ph ph-check-circle"></i>
                    <span>Recibido</span>
                </span>
            @endif
      </x-slot:actions>
    </x-ui.collection-header>

    {{-- ==================================================
         2. PESTAÑAS PRINCIPALES: AGENDA | HISTORIAL
         ================================================== --}}
    <nav class="flex items-center gap-6 border-b border-[var(--rm-border)] dark:border-[var(--rm-border)] text-xs font-[700] pb-0.5">
        <button 
            type="button"
            wire:click="cambiarTab('agenda')"
            class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-2 {{ $tab === 'agenda' ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-[var(--rm-surface-soft)]' }}">
            <i class="ph ph-calendar-check text-sm {{ $tab === 'agenda' ? 'text-[var(--rm-action-primary)]' : 'text-[var(--rm-text-secondary)]' }}"></i>
            <span>AGENDA</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-xs font-semibold {{ $tab === 'agenda' ? 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-secondary)]' }}">
                {{ count($itemsAgenda) }}
            </span>
        </button>

        <button 
            type="button"
            wire:click="cambiarTab('historial')"
            class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-2 {{ $tab === 'historial' ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-[var(--rm-surface-soft)]' }}">
            <i class="ph ph-clock-counter-clockwise text-sm {{ $tab === 'historial' ? 'text-[var(--rm-action-primary)]' : 'text-[var(--rm-text-secondary)]' }}"></i>
            <span>HISTORIAL</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-xs font-semibold {{ $tab === 'historial' ? 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-secondary)]' }}">
                {{ count($historial) }}
            </span>
        </button>
    </nav>

    {{-- ==================================================
         3. PESTAÑA: AGENDA OPERATIVA DEL TURNO
         ================================================== --}}
    @if($tab === 'agenda')
        {{-- Banner de confirmación compacta tras registrar cuidado --}}
        @if($ultimoRegistro)
            <div class="p-3 rounded-xl bg-[var(--rm-success-soft)] border border-[var(--rm-success)]/40 flex items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-6 h-6 rounded-full bg-[var(--rm-success)] text-white flex items-center justify-center shrink-0">
                        <i class="ph-bold ph-check text-xs"></i>
                    </span>
                    <div class="min-w-0 text-[var(--rm-text-primary)] truncate">
                        <strong class="font-bold text-[var(--rm-success)]">Cuidado guardado:</strong>
                        <span class="font-semibold">{{ $ultimoRegistro['intervencion'] }}</span> para 
                        <span>{{ $ultimoRegistro['residente'] }}</span> 
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold {{ $ultimoRegistro['estado'] === 'REALIZADA' ? 'bg-[var(--rm-success)] text-white' : 'bg-[var(--rm-danger)] text-white' }}">
                            {{ $ultimoRegistro['estado'] }}
                        </span>
                        <span class="text-[var(--rm-text-secondary)] font-mono">({{ $ultimoRegistro['hora'] }})</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" 
                        wire:click="cambiarTab('historial')" 
                        class="px-2.5 py-1 rounded-lg bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-action-primary)] font-bold hover:bg-[var(--rm-surface-soft)] transition cursor-pointer">
                        Ver registro
                    </button>
                    <button type="button" 
                        wire:click="$set('ultimoRegistro', null)" 
                        class="text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] cursor-pointer p-1"
                        title="Cerrar aviso">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- 4 CATEGORÍAS INSTITUCIONALES DE FILTRADO RÁPIDO --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Por atender (Pendientes + Vencidas) --}}
            <button type="button" 
                wire:click="$set('filtroEstado', 'por_atender')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer {{ (strtolower($filtroEstado) === 'por_atender' || $filtroEstado === 'por atender' || empty($filtroEstado)) ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs' : 'bg-[var(--rm-surface)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)]' }}">
                <i class="ph ph-hourglass-high text-sm"></i>
                <span>Por atender</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ (strtolower($filtroEstado) === 'por_atender' || $filtroEstado === 'por atender' || empty($filtroEstado)) ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)]' }}">
                    {{ $contadores['por_atender'] ?? 0 }}
                </span>
            </button>

            {{-- No realizadas (Omitidas / Rechazadas) --}}
            <button type="button" 
                wire:click="$set('filtroEstado', 'no_realizadas')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer {{ (strtolower($filtroEstado) === 'no_realizadas' || $filtroEstado === 'no realizadas' || $filtroEstado === 'no_realizada') ? 'bg-[var(--rm-danger)] text-white shadow-2xs' : 'bg-[var(--rm-surface)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)]' }}">
                <i class="ph ph-x-circle text-sm"></i>
                <span>No realizadas</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ (strtolower($filtroEstado) === 'no_realizadas' || $filtroEstado === 'no realizadas' || $filtroEstado === 'no_realizada') ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)]' }}">
                    {{ $contadores['no_realizadas'] ?? 0 }}
                </span>
            </button>

            {{-- Realizadas --}}
            <button type="button" 
                wire:click="$set('filtroEstado', 'realizadas')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer {{ (strtolower($filtroEstado) === 'realizadas' || $filtroEstado === 'realizada') ? 'bg-[var(--rm-success)] text-white shadow-2xs' : 'bg-[var(--rm-surface)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)]' }}">
                <i class="ph ph-check-circle text-sm"></i>
                <span>Realizadas</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ (strtolower($filtroEstado) === 'realizadas' || $filtroEstado === 'realizada') ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)]' }}">
                    {{ $contadores['realizadas'] ?? 0 }}
                </span>
            </button>

            {{-- Todas --}}
            <button type="button" 
                wire:click="$set('filtroEstado', 'todas')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer {{ strtolower($filtroEstado) === 'todas' ? 'bg-[var(--rm-text-primary)] text-white shadow-2xs' : 'bg-[var(--rm-surface)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)]' }}">
                <i class="ph ph-list text-sm"></i>
                <span>Todas</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ strtolower($filtroEstado) === 'todas' ? 'bg-white/20 text-white' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)]' }}">
                    {{ $contadores['todas'] ?? 0 }}
                </span>
            </button>
        </div>

        {{-- Barra de Filtros Complementarios (Búsqueda, Prioridad y Residente) --}}
        <x-ui.filter-bar class="mb-4">
            <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-2 items-center">
                {{-- Búsqueda textual --}}
                <div class="lg:col-span-5">
                    <label for="agenda-buscar" class="rm-collection-filter-label">Buscar en la agenda</label>
                    <div class="relative flex items-center">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                        <i class="ph-bold ph-magnifying-glass text-base"></i>
                    </span>
                    <input id="agenda-buscar" type="text"
                        wire:model.live.debounce.300ms="buscar"
                        placeholder="Buscar residente, intervención o plan..."
                        class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none h-[38px]" />
                    @if(!empty($buscar))
                        <button type="button" wire:click="$set('buscar', '')" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-action-primary)] cursor-pointer" title="Limpiar búsqueda">
                            <i class="ph-bold ph-x-circle text-base"></i>
                        </button>
                    @endif
                    </div>
                </div>

                {{-- Prioridad --}}
                <div class="lg:col-span-3">
                    <label for="agenda-prioridad" class="rm-collection-filter-label">Prioridad</label>
                    <select id="agenda-prioridad" wire:model.live="filtroPrioridad" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none h-[38px] cursor-pointer">
                        <option value="">Prioridad (Todas)</option>
                        <option value="ALTA">Alta</option>
                        <option value="MEDIA">Media</option>
                        <option value="BAJA">Baja</option>
                    </select>
                </div>

                {{-- Residente filtrado --}}
                <div class="lg:col-span-4">
                    <label for="agenda-residente" class="rm-collection-filter-label">Residente</label>
                    <select id="agenda-residente" wire:model.live="filtroResidente" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none h-[38px] cursor-pointer">
                        <option value="">Todos los residentes</option>
                        @foreach($residentes as $res)
                            <option value="{{ $res->cod_residente }}">
                                {{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Chips de filtros activos --}}
            @php
                $hasFiltrosAgenda = !empty($buscar) || !empty($filtroPrioridad) || !empty($filtroResidente) || (!empty($filtroEstado) && $filtroEstado !== 'por_atender');
            @endphp
            @if($hasFiltrosAgenda)
                <div class="rm-filter-bar__active">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rm-filter-bar__active-label">
                            <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                        </span>
                        @if(!empty($buscar))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                                <span>Búsqueda: "{{ Str::limit($buscar, 16) }}"</span>
                                <button type="button" wire:click="$set('buscar', '')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                        @if(!empty($filtroPrioridad))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-action-primary-soft)] border border-[var(--rm-action-primary)] text-[11px] font-bold text-[var(--rm-action-primary)]">
                                <span>Prioridad: {{ $filtroPrioridad }}</span>
                                <button type="button" wire:click="$set('filtroPrioridad', '')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                        @if(!empty($filtroResidente))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                                <span>Residente seleccionado</span>
                                <button type="button" wire:click="$set('filtroResidente', '')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button"
                            wire:click="$set('buscar', ''); $set('filtroPrioridad', ''); $set('filtroEstado', 'por_atender'); $set('filtroResidente', '');"
                            class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-action-primary-soft)] hover:bg-[var(--rm-action-primary)] hover:text-white text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                            <span>Restablecer filtros</span>
                        </button>
                    </div>
                </div>
            @endif
        </x-ui.filter-bar>

        {{-- Listado Operativo de Intervenciones con panel de detalle previo --}}
        <section class="space-y-3">
            @forelse($itemsAgenda as $item)
                @php
                    $esAlerta = !empty($item['alerta_vinculada']);
                    $esPrioridadAlta = ($item['prioridad'] === 'ALTA' && !$esAlerta);
                    $esVencida = ($item['estado'] === 'VENCIDA');
                    $esRealizada = ($item['estado'] === 'REALIZADA');
                    $esNoRealizada = ($item['estado'] === 'NO_REALIZADA');
                    $esCancelada = ($item['estado'] === 'CANCELADA');
                    $esPendiente = ($item['estado'] === 'PENDIENTE');
                    $estaDetalleAbierto = ($itemDetalleId === $item['id']);

                    // Tratamiento de Ficha según contrato visual
                    if ($esAlerta) {
                        $cardClass = 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger)] border-l-4 border-l-[var(--rm-danger)] border-y border-r border-[var(--rm-border)] shadow-xs';
                        $badgeEstado = 'bg-[var(--rm-danger)] text-white';
                    } elseif ($esPrioridadAlta) {
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-action-primary)] border border-[var(--rm-border)] shadow-2xs';
                        $badgeEstado = match($item['estado']) {
                            'VENCIDA' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/30',
                            'REALIZADA' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success)]/30',
                            'NO_REALIZADA' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/30',
                            default => 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30',
                        };
                    } elseif ($esVencida) {
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-danger)] border border-[var(--rm-border)] shadow-2xs';
                        $badgeEstado = 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/30';
                    } elseif ($esRealizada) {
                        $cardClass = 'opacity-85 bg-[var(--rm-surface)]/80 dark:bg-[var(--rm-surface)]/80 border-l-4 border-l-[var(--rm-success)] border border-[var(--rm-border)] hover:opacity-100 transition-opacity';
                        $badgeEstado = 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success)]/30';
                    } elseif ($esNoRealizada) {
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-danger)] border border-[var(--rm-border)] shadow-2xs';
                        $badgeEstado = 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/30';
                    } else {
                        // Pendiente ordinaria
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-action-primary)] border border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)]/40 shadow-2xs';
                        $badgeEstado = 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30';
                    }
                @endphp

                <article class="p-3.5 sm:p-4 rounded-xl transition duration-150 {{ $cardClass }}">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        {{-- Contenido Principal --}}
                        <div class="flex items-start gap-3 min-w-0">
                            {{-- Hora Programada en Pill --}}
                            <div class="shrink-0 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] font-mono text-xs font-bold">
                                    {{ $item['hora_programada'] }}
                                </span>
                            </div>

                            {{-- Información de Intervención y Residente --}}
                            <div class="min-w-0 space-y-1">
                                {{-- Cabecera: Residente y Ubicación --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">
                                        {{ $item['nombre_residente'] }}
                                    </span>

                                    <span class="text-xs text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] font-semibold bg-[var(--rm-surface-soft)]/60 px-2 py-0.5 rounded-[5px] border border-[var(--rm-border)]/60">
                                        {{ $item['ubicacion'] }}
                                    </span>

                                    @if($esAlerta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-xs font-bold bg-[var(--rm-danger)] text-white">
                                            <i class="ph ph-warning-bold text-xs"></i>
                                            <span>ALERTA</span>
                                        </span>
                                    @elseif($item['residente_con_alerta'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-xs font-semibold bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/30">
                                            <i class="ph ph-warning-circle"></i>
                                            <span>Alerta en residente</span>
                                        </span>
                                    @endif

                                    @if($esPrioridadAlta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-xs font-bold bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30">
                                            Prioridad Alta
                                        </span>
                                    @elseif($item['prioridad'] === 'BAJA')
                                        <span class="px-1.5 py-0.2 rounded text-[10px] text-[var(--rm-text-secondary)] bg-[var(--rm-surface-soft)]/60 border border-[var(--rm-border)]/60">
                                            Baja
                                        </span>
                                    @endif
                                </div>

                                {{-- Nombre de la Intervención --}}
                                <h3 class="font-[700] text-xs text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] leading-snug">
                                    {{ $item['nombre_intervencion'] }}
                                </h3>

                                {{-- Plan de Cuidado y Frecuencia --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ph ph-folder text-xs text-[var(--rm-action-primary)]"></i>
                                        <span>{{ $item['nombre_plan'] }}</span>
                                    </span>
                                    <span>·</span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ph ph-clock text-xs text-[var(--rm-text-secondary)]"></i>
                                        <span>Frecuencia: {{ $item['frecuencia'] }}</span>
                                    </span>
                                </div>

                                @if($esAlerta && !empty($item['alerta_descripcion']))
                                    <p class="text-[11px] font-semibold text-[var(--rm-danger)] mt-0.5">
                                        ⚠ {{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($item['alerta_descripcion']) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Estado y Botones de Detalle / Registro --}}
                        <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                            {{-- Badge Estado --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-[700] {{ $badgeEstado }}">
                                {{ $esCancelada ? 'Cancelada' : ($item['estado'] === 'VENCIDA' ? 'Vencida' : ($item['estado'] === 'REALIZADA' ? 'Realizada' : ($item['estado'] === 'NO_REALIZADA' ? 'No realizada' : ($esAlerta ? 'Alerta activa' : 'Pendiente')))) }}
                            </span>

                            {{-- Botón Inspección de Detalle previo --}}
                            <button type="button"
                                wire:click="toggleDetalleItem('{{ $item['id'] }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] transition cursor-pointer"
                                title="Ver indicación completa, programación y última ejecución">
                                <i class="ph {{ $estaDetalleAbierto ? 'ph-caret-up' : 'ph-info' }} text-sm text-[var(--rm-action-primary)]"></i>
                                <span>{{ $estaDetalleAbierto ? 'Ocultar' : 'Detalle' }}</span>
                            </button>

                            {{-- Acción Registrar --}}
                            @if(!$esRealizada && !$esCancelada && $puedeRegistrar)
                                <button type="button"
                                    wire:click="abrirModalRegistrar('{{ $item['cod_intervencion'] }}', '{{ $item['cod_residente'] }}', '{{ $item['cod_programacion'] }}', '{{ $item['hora_programada'] }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-action-primary)] hover:bg-[var(--rm-success)] text-white shadow-2xs transition cursor-pointer">
                                    <i class="ph ph-pencil-simple-line"></i>
                                    <span>Registrar</span>
                                </button>
                            @elseif($esRealizada)
                                <span class="text-[11px] font-semibold text-[var(--rm-success)] inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i>
                                    <span>Firmada</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- PANEL DE DETALLE CLÍNICO PREVIO AL REGISTRO --}}
                    @if($estaDetalleAbierto)
                        <div class="mt-3 pt-3 border-t border-[var(--rm-border)]/70 bg-[var(--rm-surface-soft)]/50 rounded-lg p-3 space-y-2.5 text-xs text-[var(--rm-text-primary)]">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[10px] text-[var(--rm-action-primary)] uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph ph-file-text"></i> Panel de Detalle Asistencial
                                </span>
                                <span class="text-[11px] text-[var(--rm-text-secondary)]">
                                    Responsable: <strong>{{ $item['responsable_nombre'] }}</strong>
                                </span>
                            </div>

                            {{-- Indicación completa --}}
                            <div>
                                <span class="text-xs font-semibold text-[var(--rm-text-secondary)] block">Indicación clínica completa:</span>
                                <p class="text-xs text-[var(--rm-text-primary)] font-medium leading-relaxed bg-[var(--rm-surface)] p-2 rounded border border-[var(--rm-border)]/60">
                                    {{ $item['descripcion'] ?: 'Realizar cuidado asistencial programado conforme a protocolo y criterio de enfermería.' }}
                                </p>
                            </div>

                            {{-- Programación y ejecución previa --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                <div class="p-2 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 space-y-1">
                                    <span class="text-[10px] font-bold text-[var(--rm-text-secondary)] block">PROGRAMACIÓN</span>
                                    <div><strong>Hora prevista:</strong> <span class="font-mono font-bold">{{ $item['hora_programada'] }}</span></div>
                                    <div><strong>Frecuencia:</strong> {{ $item['frecuencia'] }}</div>
                                    <div><strong>Prioridad:</strong> {{ $item['prioridad'] }}</div>
                                </div>

                                <div class="p-2 rounded bg-[var(--rm-surface)] border border-[var(--rm-border)]/60 space-y-1">
                                    <span class="text-[10px] font-bold text-[var(--rm-text-secondary)] block">ÚLTIMA EJECUCIÓN REGISTRADA</span>
                                    @if(!empty($item['ultima_ejecucion']))
                                        <div><strong>Fecha/Hora:</strong> {{ $item['ultima_ejecucion']['fecha_hora'] ?? 'Hoy' }}</div>
                                        <div><strong>Resultado:</strong> {{ $item['ultima_ejecucion']['resultado'] }} ({{ $item['ultima_ejecucion']['estado'] }})</div>
                                        <div><strong>Profesional:</strong> {{ $item['ultima_ejecucion']['personal'] }}</div>
                                        @if(!empty($item['ultima_ejecucion']['motivo_omision']))
                                            <div class="text-[var(--rm-danger)]"><strong>Motivo:</strong> {{ $item['ultima_ejecucion']['motivo_omision'] }}</div>
                                        @endif
                                    @else
                                        <p class="text-[var(--rm-text-secondary)] italic">Sin registros de ejecución previos para esta intervención.</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Botón de registro directo desde el panel de detalle --}}
                            @if(!$esRealizada && !$esCancelada && $puedeRegistrar)
                                <div class="pt-1 flex justify-end">
                                    <button type="button"
                                        wire:click="abrirModalRegistrar('{{ $item['cod_intervencion'] }}', '{{ $item['cod_residente'] }}', '{{ $item['cod_programacion'] }}', '{{ $item['hora_programada'] }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-action-primary)] hover:bg-[var(--rm-success)] text-white shadow-2xs transition cursor-pointer">
                                        <i class="ph ph-pencil-simple-line"></i>
                                        <span>Registrar este cuidado</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </article>
            @empty
                <div class="p-8 text-center rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-dashed border-[var(--rm-border)] text-xs text-[var(--rm-text-secondary)]">
                    <i class="ph ph-check-circle text-3xl text-[var(--rm-action-primary)] mb-1.5 block"></i>
                    <p class="font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">No hay cuidados en este filtro</p>
                    <p class="text-[11px] mt-0.5">Consulte los otros filtros o seleccione otro residente para ver su agenda.</p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ==================================================
         4. PESTAÑA: HISTORIAL CLÍNICO DIRECTO
         ================================================== --}}
    @if($tab === 'historial')
        <section class="space-y-3">
            {{-- Filtros de Historial --}}
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-2xs grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <div>
                    <input type="text"
                        wire:model.live.debounce.300ms="buscarHistorial"
                        placeholder="Buscar residente o intervención..."
                        class="w-full h-9 px-3 text-xs rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)]" />
                </div>
                <div>
                    <select wire:model.live="filtroHistorialResidente" class="w-full h-9 px-3 text-xs rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)]">
                        <option value="">Todos los residentes</option>
                        @foreach($residentes as $res)
                            <option value="{{ $res->cod_residente }}">{{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date"
                        wire:model.live="filtroHistorialFecha"
                        class="w-full h-9 px-3 text-xs rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)]" />
                </div>
            </div>

            {{-- Listado de Registros en Historial --}}
            <div class="space-y-2">
                @forelse($historial as $ej)
                    @php
                        $esOmision = $ej->estado === 'NO_REALIZADA' || in_array(strtolower((string) $ej->resultado), ['no realizada', 'omitida', 'no realizada / omitida']);
                    @endphp
                    <article class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-2xs text-xs space-y-1.5 {{ $esOmision ? 'border-l-4 border-l-[var(--rm-danger)]' : 'border-l-4 border-l-[var(--rm-success)]' }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[var(--rm-text-primary)]">
                                    {{ $ej->residente ? trim("{$ej->residente->nombres} {$ej->residente->apellido_paterno}") : 'Residente' }}
                                </span>
                                <span class="text-[11px] text-[var(--rm-text-secondary)]">·</span>
                                <span class="font-semibold text-[var(--rm-action-primary)]">
                                    {{ $ej->intervencion?->nombre ?? 'Cuidado' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-secondary)]">
                                <span class="font-mono">{{ $ej->fecha_hora_ejecucion ? Carbon\Carbon::parse($ej->fecha_hora_ejecucion)->format('d/m/Y H:i') : '' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $esOmision ? 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)]' : 'bg-[var(--rm-success-soft)] text-[var(--rm-success)]' }}">
                                    {{ $ej->estado }}
                                </span>
                            </div>
                        </div>

                        <div class="text-[11px] text-[var(--rm-text-secondary)] flex flex-wrap gap-x-4">
                            <span><strong>Resultado:</strong> {{ $ej->resultado }}</span>
                            @if($ej->personal)
                                <span><strong>Registrado por:</strong> {{ trim("{$ej->personal->nombres} {$ej->personal->apellido_paterno}") }}</span>
                            @endif
                        </div>

                        @if(!empty($ej->motivo_omision))
                            <p class="text-[11px] text-[var(--rm-danger)] bg-[var(--rm-danger-soft)]/50 p-2 rounded border border-[var(--rm-danger)]/20">
                                <strong>Motivo de omisión:</strong> {{ $ej->motivo_omision }}
                            </p>
                        @endif

                        @if(!empty($ej->observacion))
                            <p class="text-[11px] text-[var(--rm-text-primary)] bg-[var(--rm-surface-soft)]/60 p-2 rounded border border-[var(--rm-border)]/40">
                                <strong>Observación:</strong> {{ $ej->observacion }}
                            </p>
                        @endif
                    </article>
                @empty
                    <div class="p-8 text-center rounded-xl bg-[var(--rm-surface)] border border-dashed border-[var(--rm-border)] text-xs text-[var(--rm-text-secondary)]">
                        <i class="ph ph-clock-counter-clockwise text-3xl text-[var(--rm-action-primary)] mb-1.5 block"></i>
                        <p class="font-bold text-[var(--rm-text-primary)]">Sin registros históricos coincidentes</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- ==================================================
         5. VENTANA MODAL CENTRAL DE REGISTRO CLÍNICO
         (Flujo en 2 pasos: Formulario -> Revisión -> Confirmación)
         ================================================== --}}
    @if($modalRegistrarAbierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/50 backdrop-blur-xs" 
             role="dialog" 
             aria-modal="true">
            <div class="w-full max-w-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] rounded-2xl border border-[var(--rm-border)] shadow-xl overflow-hidden text-xs">
                {{-- Header del Modal --}}
                <div class="px-5 py-3.5 bg-[var(--rm-surface-soft)] border-b border-[var(--rm-border)] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-[var(--rm-action-primary)] text-white flex items-center justify-center">
                            <i class="ph ph-pencil-line text-sm"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[var(--rm-text-primary)]">
                                {{ $pasoRevision ? 'Revisión antes de Confirmar Registro' : 'Registrar Ejecución de Cuidado' }}
                            </h2>
                            <span class="text-[11px] text-[var(--rm-text-secondary)]">
                                {{ $pasoRevision ? 'Verifique los datos clínicos antes de la firma formal en turno' : 'Registro asistencial formal en la bitácora del turno activo' }}
                            </span>
                        </div>
                    </div>
                    <button type="button" 
                        wire:click="cerrarModalRegistrar" 
                        class="p-1 text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] rounded-lg transition cursor-pointer">
                        <i class="ph ph-x text-base"></i>
                    </button>
                </div>

                {{-- Banner de error interno del modal si ocurre --}}
                @error('errorModal')
                    <div class="mx-5 mt-3 p-2.5 rounded-lg bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)] text-[var(--rm-danger)] text-xs font-semibold flex items-center gap-2">
                        <i class="ph ph-warning text-base shrink-0"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                {{-- PASO 1: FORMULARIO DE CAPTURA CLÍNICA --}}
                @if(!$pasoRevision)
                    <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                        {{-- BLOQUE SOLO LECTURA: Contexto Clínico del Residente y Plan --}}
                        <div class="p-3.5 rounded-xl bg-[var(--rm-surface-soft)]/70 border border-[var(--rm-border)]/80 space-y-2">
                            <span class="text-[10px] font-bold text-[var(--rm-action-primary)] uppercase tracking-wider block">
                                Contexto Clínico de la Intervención (Solo Lectura)
                            </span>

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Residente:</span>
                                    <strong class="text-[var(--rm-text-primary)]">{{ $datosModal['residente_nombre'] ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Ubicación:</span>
                                    <span class="text-[var(--rm-text-primary)] font-semibold">{{ $datosModal['ubicacion'] ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="border-t border-[var(--rm-border)]/50 pt-2">
                                <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Intervención:</span>
                                <strong class="text-[var(--rm-text-primary)] text-xs block leading-snug">
                                    {{ $datosModal['intervencion_nombre'] ?? 'N/A' }}
                                </strong>
                                <p class="text-[11px] text-[var(--rm-text-secondary)] mt-0.5">
                                    {{ $datosModal['intervencion_descripcion'] ?? '' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[var(--rm-border)]/50 text-[11px]">
                                <div>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">Prioridad:</span>
                                    <span class="font-bold text-[var(--rm-action-primary)]">{{ $datosModal['prioridad'] ?? 'MEDIA' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">Hora prog.:</span>
                                    <span class="font-mono font-bold text-[var(--rm-text-primary)]">{{ $datosModal['hora_programada'] ?? '--:--' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">Frecuencia:</span>
                                    <span class="font-semibold text-[var(--rm-text-secondary)]">{{ $datosModal['frecuencia'] ?? 'Turno' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- PREGUNTA EXPRESA OBLIGATORIA: ¿SE REALIZÓ EL CUIDADO? --}}
                        <div class="space-y-1.5 p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                            <label class="block text-xs font-bold text-[var(--rm-text-primary)]">
                                ¿Se realizó el cuidado programado? <span class="text-[var(--rm-danger)]">*</span>
                            </label>
                            <p class="text-[11px] text-[var(--rm-text-secondary)]">Indique expresamente si el procedimiento pudo ser ejecutado o si fue omitido/rechazado.</p>
                            <div class="grid grid-cols-2 gap-2.5 pt-1">
                                <button type="button" 
                                    wire:click="setFueRealizado(true)"
                                    class="p-2.5 rounded-xl border text-xs font-bold flex items-center justify-center gap-2 cursor-pointer transition {{ $fueRealizado === true ? 'bg-[var(--rm-action-primary)] text-white border-[var(--rm-action-primary)] shadow-xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)] hover:bg-[var(--rm-surface)]' }}">
                                    <i class="ph-bold ph-check-circle text-base"></i>
                                    <span>SÍ, fue realizado</span>
                                </button>
                                <button type="button" 
                                    wire:click="setFueRealizado(false)"
                                    class="p-2.5 rounded-xl border text-xs font-bold flex items-center justify-center gap-2 cursor-pointer transition {{ $fueRealizado === false ? 'bg-[var(--rm-danger)] text-white border-[var(--rm-danger)] shadow-xs' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)] hover:bg-[var(--rm-surface)]' }}">
                                    <i class="ph-bold ph-x-circle text-base"></i>
                                    <span>NO fue realizado (omitido)</span>
                                </button>
                            </div>
                            @error('fueRealizado') <span class="text-[11px] text-[var(--rm-danger)] font-bold block pt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- CAMPOS EDITABLES CONDICIONALES --}}
                        @if($fueRealizado !== null)
                            <div class="space-y-3.5">
                                {{-- Resultado Clínico (NO PRESELECCIONADO "Satisfactorio") --}}
                                <div>
                                    <label class="block text-xs font-bold text-[var(--rm-text-primary)] mb-1">
                                        Resultado Clínico de la Intervención <span class="text-[var(--rm-danger)]">*</span>
                                    </label>
                                    @if($fueRealizado)
                                        <select wire:model.live="resultado" class="w-full h-10 px-3 text-xs rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:border-[var(--rm-action-primary)] cursor-pointer">
                                            <option value="">-- Seleccione el resultado clínico --</option>
                                            <option value="Satisfactorio">Satisfactorio (completado con éxito)</option>
                                            <option value="Realizado según protocolo">Realizado según protocolo institucional</option>
                                            <option value="Con dificultad / colaboración parcial">Con dificultad / colaboración parcial</option>
                                            <option value="Evolución favorable">Evolución favorable</option>
                                            <option value="Cambio relevante observado">Cambio relevante observado</option>
                                            <option value="Sin cambios clínicos relevantes">Sin cambios clínicos relevantes</option>
                                        </select>
                                    @else
                                        <select wire:model.live="resultado" class="w-full h-10 px-3 text-xs rounded-xl border border-[var(--rm-danger)] bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] font-bold cursor-pointer">
                                            <option value="No realizada / Omitida">No realizada / Omitida</option>
                                            <option value="Rechazada por el residente">Rechazada por el residente</option>
                                            <option value="Contraindicación clínica transitoria">Contraindicación clínica transitoria</option>
                                            <option value="Ausencia transitoria del residente">Ausencia transitoria del residente</option>
                                            <option value="Impedimento técnico o de material">Impedimento técnico o de material</option>
                                            <option value="Criterio clínico de enfermería">Criterio clínico de enfermería</option>
                                        </select>
                                    @endif
                                    @error('resultado') <span class="text-[11px] text-[var(--rm-danger)] font-bold block mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Motivo de Omisión (Obligatorio cuando no realizada) --}}
                                @if(!$fueRealizado)
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-xs font-bold text-[var(--rm-danger)]">
                                                Motivo de Omisión Justificado <span class="text-[var(--rm-danger)]">*</span>
                                            </label>
                                            <span class="text-[10px] text-[var(--rm-text-secondary)] font-mono">
                                                {{ mb_strlen($motivoOmision) }}/500
                                            </span>
                                        </div>
                                        <textarea wire:model="motivoOmision"
                                            rows="2"
                                            maxlength="500"
                                            placeholder="Describa con precisión la justificación clínica o institucional de la omisión..."
                                            class="w-full p-2.5 text-xs rounded-xl border border-[var(--rm-danger)] bg-[var(--rm-danger-soft)]/50 text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-danger)]"></textarea>
                                        @error('motivoOmision') <span class="text-[11px] text-[var(--rm-danger)] font-bold block">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                {{-- Observación Clínica (Obligatoria si omisión o dificultad/favorable/cambio) --}}
                                @php
                                    $resLower = mb_strtolower($resultado);
                                    $obsRequerida = (!$fueRealizado) || str_contains($resLower, 'dificultad') || str_contains($resLower, 'parcial') || str_contains($resLower, 'favorable') || str_contains($resLower, 'cambio');
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-[var(--rm-text-primary)]">
                                            Observación Clínica de Enfermería 
                                            @if($obsRequerida)
                                                <span class="text-[var(--rm-danger)]">* (Requerida por el resultado seleccionado)</span>
                                            @else
                                                <span class="text-[var(--rm-text-secondary)] font-normal">(Opcional)</span>
                                            @endif
                                        </label>
                                        <span class="text-[10px] text-[var(--rm-text-secondary)] font-mono">
                                            {{ mb_strlen($observacion) }}/500
                                        </span>
                                    </div>
                                    <textarea wire:model="observacion"
                                        rows="3"
                                        maxlength="500"
                                        placeholder="{{ $obsRequerida ? 'Describa la respuesta del residente, dificultad observada o detalles relevantes (mínimo 5 caracteres)...' : 'Anotaciones asistenciales para la bitácora o pase de turno...' }}"
                                        class="w-full p-2.5 text-xs rounded-xl border {{ $obsRequerida ? 'border-[var(--rm-action-primary)]' : 'border-[var(--rm-border)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:border-[var(--rm-action-primary)]"></textarea>
                                    @error('observacion') <span class="text-[11px] text-[var(--rm-danger)] font-bold block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer del Paso 1 --}}
                    <div class="px-5 py-3.5 bg-[var(--rm-surface-soft)] border-t border-[var(--rm-border)] flex items-center justify-end gap-2">
                        <button type="button"
                            wire:click="cerrarModalRegistrar"
                            class="px-3.5 py-1.5 rounded-xl font-semibold text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] bg-[var(--rm-surface)] border border-[var(--rm-border)] cursor-pointer">
                            Cancelar
                        </button>

                        <button type="button"
                            wire:click="revisarAntesDeConfirmar"
                            class="px-4 py-1.5 rounded-xl font-bold text-white bg-[var(--rm-action-primary)] hover:bg-[var(--rm-success)] shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                            <span>Revisar antes de confirmar</span>
                            <i class="ph-bold ph-arrow-right"></i>
                        </button>
                    </div>
                @else
                    {{-- PASO 2: REVISIÓN DE REGISTRO ANTES DE CONFIRMAR --}}
                    <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                        <div class="p-3 rounded-xl bg-[var(--rm-action-primary-soft)] border border-[var(--rm-action-primary)]/40 flex items-center gap-2.5 text-xs">
                            <i class="ph-bold ph-check-square-offset text-lg text-[var(--rm-action-primary)] shrink-0"></i>
                            <div>
                                <strong class="text-[var(--rm-action-primary)]">Confirme la veracidad de los datos clínicos</strong>
                                <p class="text-[11px] text-[var(--rm-text-primary)] mt-0.5">El registro quedará firmado con su cuenta institucional y formará parte de la bitácora del turno y el pase de relevo.</p>
                            </div>
                        </div>

                        {{-- Resumen de datos a firmar --}}
                        <div class="p-3.5 rounded-xl bg-[var(--rm-surface-soft)]/80 border border-[var(--rm-border)] space-y-2 text-xs">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Residente:</span>
                                    <strong class="text-[var(--rm-text-primary)]">{{ $datosModal['residente_nombre'] ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Intervención:</span>
                                    <strong class="text-[var(--rm-text-primary)]">{{ $datosModal['intervencion_nombre'] ?? 'N/A' }}</strong>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[var(--rm-border)]/50 text-[11px]">
                                <div>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">Hora programada:</span>
                                    <span class="font-mono font-bold">{{ $datosModal['hora_programada'] ?? '--:--' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">Estado a registrar:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $fueRealizado ? 'bg-[var(--rm-success)] text-white' : 'bg-[var(--rm-danger)] text-white' }}">
                                        {{ $fueRealizado ? 'REALIZADA' : 'NO REALIZADA' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-[var(--rm-text-secondary)] block">Firmante:</span>
                                    <span class="font-semibold">{{ Auth::user()->name }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-[var(--rm-border)]/50">
                                <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Resultado clínico:</span>
                                <strong class="text-[var(--rm-text-primary)] block">{{ $resultado }}</strong>
                            </div>

                            @if(!$fueRealizado && !empty($motivoOmision))
                                <div class="pt-2 border-t border-[var(--rm-border)]/50">
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)] block">Motivo de omisión:</span>
                                    <p class="text-[11px] text-[var(--rm-text-primary)] bg-[var(--rm-surface)] p-2 rounded border border-[var(--rm-border)]">{{ $motivoOmision }}</p>
                                </div>
                            @endif

                            @if(!empty($observacion))
                                <div class="pt-2 border-t border-[var(--rm-border)]/50">
                                    <span class="text-[11px] font-semibold text-[var(--rm-text-secondary)] block">Observación clínica descriptiva:</span>
                                    <p class="text-[11px] text-[var(--rm-text-primary)] bg-[var(--rm-surface)] p-2 rounded border border-[var(--rm-border)]">{{ $observacion }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer del Paso 2 --}}
                    <div class="px-5 py-3.5 bg-[var(--rm-surface-soft)] border-t border-[var(--rm-border)] flex items-center justify-between">
                        <button type="button"
                            wire:click="volverAEditar"
                            class="px-3.5 py-1.5 rounded-xl font-semibold text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] bg-[var(--rm-surface)] border border-[var(--rm-border)] cursor-pointer flex items-center gap-1">
                            <i class="ph-bold ph-arrow-left"></i>
                            <span>Modificar datos</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                wire:click="cerrarModalRegistrar"
                                class="px-3.5 py-1.5 rounded-xl font-semibold text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] bg-[var(--rm-surface)] border border-[var(--rm-border)] cursor-pointer">
                                Cancelar
                            </button>

                            <button type="button"
                                wire:click="registrarEjecucion"
                                wire:loading.attr="disabled"
                                class="px-5 py-1.5 rounded-xl font-bold text-white bg-[var(--rm-action-primary)] hover:bg-[var(--rm-success)] shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                                <span wire:loading.remove>Confirmar registro</span>
                                <span wire:loading class="inline-flex items-center gap-1">
                                    <i class="ph ph-spinner animate-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
