<!-- rm-filter-bar -->
<div class="space-y-4 font-sans bg-[var(--rm-surface)] dark:bg-[var(--rm-surface-soft)] p-3 sm:p-5 rounded-2xl">
    {{-- ==================================================
         1. CABECERA INSTITUCIONAL Y NAVEGACIÓN DE TURNO
         ================================================== --}}
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-2 border-b border-[var(--rm-border)]/70 dark:border-[var(--rm-border)]">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-action-primary-ink)] dark:text-[var(--rm-action-primary)] block">
                CENTRO GERIÁTRICO LOS ALMENDROS · ENFERMERÍA
            </span>
            <div class="flex items-center gap-2 mt-0.5">
                <h1 class="text-xl sm:text-2xl font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] tracking-tight">
                    {{ $esSuperAdmin ? 'Agenda institucional de Enfermería' : 'Agenda Operativa de Cuidados' }}
                </h1>
                <span class="text-[11px] font-mono text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] px-2 py-0.5 rounded-lg border border-[var(--rm-border)] dark:border-[var(--rm-border)]">
                    Hoy, {{ today()->translatedFormat('d \d\e F') }}
                </span>
            </div>
            <p class="text-xs text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] mt-0.5">
                {{ $esSuperAdmin ? 'Supervisión integral de alertas, medicación y cuidados de todos los residentes.' : 'Programación asistencial del turno basada en planes clínicos individuales.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start lg:self-center">
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
        </div>
    </header>

    {{-- ==================================================
         2. PESTAÑAS PRINCIPALES: AGENDA | HISTORIAL
         ================================================== --}}
    <nav class="flex items-center gap-6 border-b border-[var(--rm-border)] dark:border-[var(--rm-border)] text-xs font-[700] pb-0.5">
        <button 
            type="button"
            wire:click="cambiarTab('agenda')"
            class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-2 {{ $tab === 'agenda' ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-[var(--rm-surface-soft)]' }}">
            <i class="ph ph-calendar-check text-sm {{ $tab === 'agenda' ? 'text-[var(--rm-warning)]' : 'text-[var(--rm-text-secondary)]' }}"></i>
            <span>AGENDA</span>
            <span class="ml-1 px-1.5 py-0.2 rounded-full text-xs font-semibold {{ $tab === 'agenda' ? 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-secondary)]' }}">
                {{ count($itemsAgenda) }}
            </span>
        </button>

        <button 
            type="button"
            wire:click="cambiarTab('historial')"
            class="pb-2.5 cursor-pointer transition-colors relative flex items-center gap-2 {{ $tab === 'historial' ? 'border-b-2 border-[var(--rm-action-primary)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)]' : 'text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-[var(--rm-surface-soft)]' }}">
            <i class="ph ph-clock-counter-clockwise text-sm {{ $tab === 'historial' ? 'text-[var(--rm-warning)]' : 'text-[var(--rm-text-secondary)]' }}"></i>
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
        {{-- Barra de Filtros Compacta de Agenda formato alertas --}}
        <x-ui.filter-bar class="mb-4">
            <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-2 items-center">
                {{-- Búsqueda textual --}}
                <div class="lg:col-span-4 relative flex items-center">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                        <i class="ph-bold ph-magnifying-glass text-base"></i>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.300ms="buscar"
                        placeholder="Buscar residente, intervención o plan..."
                        class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                    @if(!empty($buscar))
                        <button type="button" wire:click="$set('buscar', '')" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer" title="Limpiar búsqueda">
                            <i class="ph-bold ph-x-circle text-base"></i>
                        </button>
                    @endif
                </div>

                {{-- Prioridad --}}
                <div class="lg:col-span-3">
                    <select wire:model.live="filtroPrioridad" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                        <option value="">Prioridad (Todas)</option>
                        <option value="ALTA">Alta</option>
                        <option value="MEDIA">Media</option>
                        <option value="BAJA">Baja</option>
                    </select>
                </div>

                {{-- Estado --}}
                <div class="lg:col-span-2">
                    <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                        <option value="">Estado (Todos)</option>
                        <option value="ALERTA">Con alerta activa</option>
                        <option value="VENCIDA">Vencidas</option>
                        <option value="PENDIENTE">Pendientes</option>
                        <option value="REALIZADA">Realizadas</option>
                    </select>
                </div>

                {{-- Residente --}}
                <div class="lg:col-span-3">
                    <select wire:model.live="filtroResidente" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                        <option value="">Todos los residentes</option>
                        @foreach($residentes as $res)
                            <option value="{{ $res->cod_residente }}">
                                {{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Chips de filtros activos formato alertas --}}
            @php
                $hasFiltrosAgenda = !empty($buscar) || !empty($filtroPrioridad) || !empty($filtroEstado) || !empty($filtroResidente);
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
                                <button type="button" wire:click="$set('buscar', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                        @if(!empty($filtroPrioridad))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-primary-soft)] border border-[var(--rm-primary)] text-[11px] font-bold text-[var(--rm-primary)]">
                                <span>Prioridad: {{ $filtroPrioridad }}</span>
                                <button type="button" wire:click="$set('filtroPrioridad', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                        @if(!empty($filtroEstado))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning-soft)] border border-[var(--rm-warning)] text-[11px] font-bold text-[var(--rm-warning)]">
                                <span>Estado: {{ $filtroEstado }}</span>
                                <button type="button" wire:click="$set('filtroEstado', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                        @if(!empty($filtroResidente))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                                <span>Residente filtrado</span>
                                <button type="button" wire:click="$set('filtroResidente', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button"
                            wire:click="$set('buscar', ''); $set('filtroPrioridad', ''); $set('filtroEstado', ''); $set('filtroResidente', '');"
                            class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-primary-soft)] hover:bg-[var(--rm-primary)] hover:text-white text-[var(--rm-primary)] border border-[var(--rm-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                            <span>Limpiar filtros</span>
                        </button>
                    </div>
                </div>
            @endif
        </x-ui.filter-bar>

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
                        $cardClass = 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger)] border-l-4 border-l-[var(--rm-danger)] border-y border-r border-[var(--rm-warning-soft)] dark:border-[var(--rm-danger)] shadow-xs';
                        $badgeEstado = 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] dark:text-[var(--rm-warning-soft)] border border-[var(--rm-warning-soft)]';
                    } elseif ($esPrioridadAlta) {
                        // Prioridad alta sin alerta: destacar en terracota, no rojo completo
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-warning)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-2xs';
                        $badgeEstado = match($item['estado']) {
                            'VENCIDA' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-warning-soft)]',
                            'REALIZADA' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success-soft)]',
                            'NO_REALIZADA' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-warning-soft)]',
                            default => 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-warning-soft)]',
                        };
                    } elseif ($esVencida) {
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-danger)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-2xs';
                        $badgeEstado = 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-warning-soft)]';
                    } elseif ($esRealizada) {
                        $cardClass = 'opacity-75 bg-[var(--rm-surface)]/70 dark:bg-[var(--rm-surface)]/70 border-l-4 border-l-[var(--rm-action-primary)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] hover:opacity-100 transition-opacity';
                        $badgeEstado = 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success-soft)]';
                    } elseif ($esNoRealizada) {
                        $cardClass = 'opacity-85 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-danger)] border border-[var(--rm-border)] dark:border-[var(--rm-border)]';
                        $badgeEstado = 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-warning-soft)]';
                    } else {
                        // Pendiente
                        $cardClass = 'bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border-l-4 border-l-[var(--rm-warning)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)]/40 shadow-2xs';
                        $badgeEstado = 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-warning-soft)]';
                    }
                @endphp

                <article class="p-3 sm:p-3.5 rounded-xl transition duration-150 {{ $cardClass }}">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        {{-- Contenido Principal --}}
                        <div class="flex items-start gap-3 min-w-0">
                            {{-- Hora Programada en Pill --}}
                            <div class="shrink-0 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] font-mono text-xs font-bold">
                                    {{ $item['hora_programada'] }}
                                </span>
                            </div>

                            {{-- Información de Intervención y Paciente --}}
                            <div class="min-w-0 space-y-1">
                                {{-- Cabecera de la ficha --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Residente --}}
                                    <span class="font-bold text-sm text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">
                                        {{ $item['nombre_residente'] }}
                                    </span>

                                    {{-- Hab / Cama --}}
                                    <span class="text-xs text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] font-semibold bg-[var(--rm-surface-soft)]/60 dark:bg-[var(--rm-surface)] px-2 py-0.5 rounded-[5px] border border-[var(--rm-border)]/60">
                                        {{ $item['ubicacion'] }}
                                    </span>

                                    {{-- Badges de Alerta / Prioridad --}}
                                    @if($esAlerta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-xs font-semibold font-bold bg-[var(--rm-danger)] text-white">
                                            <i class="ph ph-warning-bold text-xs"></i>
                                            <span>ALERTA</span>
                                        </span>
                                    @elseif($item['residente_con_alerta'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-xs font-semibold font-semibold bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-warning-soft)]">
                                            <i class="ph ph-warning-circle"></i>
                                            <span>Residente con alerta activa</span>
                                        </span>
                                    @endif

                                    @if($esPrioridadAlta)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-xs font-semibold font-bold bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] dark:text-[var(--rm-warning-soft)] border border-[var(--rm-warning)]/30">
                                            Prioridad Alta
                                        </span>
                                    @elseif($item['prioridad'] === 'BAJA')
                                        <span class="px-1.5 py-0.2 rounded text-[10px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] bg-[var(--rm-surface-soft)]/60 border border-[var(--rm-border)]/60">
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
                                        <i class="ph ph-folder text-xs text-[var(--rm-warning)]"></i>
                                        <span>{{ $item['nombre_plan'] }}</span>
                                    </span>
                                    <span>·</span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ph ph-clock text-xs text-[var(--rm-text-secondary)]"></i>
                                        <span>Frecuencia: {{ $item['frecuencia'] }}</span>
                                    </span>
                                </div>

                                @if($esAlerta && !empty($item['alerta_descripcion']))
                                    <p class="text-[11px] font-semibold text-[var(--rm-danger)] dark:text-[var(--rm-warning-soft)] mt-0.5">
                                        ⚠ {{ $item['alerta_descripcion'] }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Estado y Botón Registrar --}}
                        <div class="flex items-center gap-3 self-end md:self-center shrink-0">
                            {{-- Badge Estado --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-[700] {{ $badgeEstado }}">
                                {{ $item['estado'] === 'VENCIDA' ? 'Vencida' : ($item['estado'] === 'REALIZADA' ? 'Realizada' : ($item['estado'] === 'NO_REALIZADA' ? 'No realizada' : ($esAlerta ? 'Alerta activa' : 'Pendiente'))) }}
                            </span>

                            {{-- Acción Registrar --}}
                            @if(!$esRealizada)
                                <button type="button"
                                    wire:click="abrirModalRegistrar('{{ $item['cod_intervencion'] }}', '{{ $item['cod_residente'] }}', '{{ $item['cod_programacion'] }}', '{{ $item['hora_programada'] }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] text-white shadow-2xs transition cursor-pointer">
                                    <i class="ph ph-pencil-simple-line"></i>
                                    <span>Registrar</span>
                                </button>
                            @else
                                <span class="text-[11px] font-semibold text-[var(--rm-action-primary)] dark:text-[var(--rm-success)] inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i>
                                    <span>Firmada</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-dashed border-[var(--rm-border)] text-xs text-[var(--rm-text-secondary)]">
                    <i class="ph ph-check-circle text-3xl text-[var(--rm-action-primary)] mb-1.5 block"></i>
                    <p class="font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">No hay intervenciones registradas en este filtro</p>
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
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-2xs grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <div>
                    <input type="text"
                        wire:model.live.debounce.300ms="buscarHistorial"
                        placeholder="Buscar residente o intervención..."
                        class="w-full h-9 px-3 text-xs rounded-lg border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] placeholder-[var(--rm-text-secondary)]" />
                </div>
                <div>
                    <select wire:model.live="filtroHistorialResidente" class="w-full h-10 px-3 text-xs rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">
                        <option value="">Todos los residentes</option>
                        @foreach($residentes as $res)
                            <option value="{{ $res->cod_residente }}">{{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date"
                        wire:model.live="filtroHistorialFecha"
                        class="w-full h-10 px-3 text-xs rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]" />
                </div>
            </div>

            {{-- Bitácora Tabular Directa --}}
            <div class="bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-sm overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="rm-data-table w-full table-auto text-left border-collapse min-w-[850px] text-xs">
                        <thead>
                            <tr class="bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-xs font-semibold font-[700] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] uppercase tracking-wider border-b border-[var(--rm-border)] dark:border-[var(--rm-border)]">
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
                        <tbody class="divide-y divide-[var(--rm-border)]/60 dark:divide-[var(--rm-border)]">
                            @forelse($historial as $ej)
                                @php
                                    $res = $ej->residente;
                                    $int = $ej->intervencion;
                                    $plan = $int?->plan;
                                    $prof = $ej->personal;
                                    $esOmitida = ($ej->estado === 'NO_REALIZADA' || in_array(strtolower((string)$ej->resultado), ['omitida', 'no realizada']));
                                @endphp
                                <tr class="hover:bg-[var(--rm-surface-soft)]/40 transition">
                                    {{-- Hora Programada --}}
                                    <td class="px-2.5 py-2 text-center font-mono font-[700] text-[var(--rm-text-secondary)] whitespace-nowrap">
                                        {{ $ej->fecha_hora_programada ? $ej->fecha_hora_programada->format('H:i') : '--:--' }}
                                    </td>

                                    {{-- Hora Real --}}
                                    <td class="px-2.5 py-2 text-center font-mono font-[700] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] whitespace-nowrap">
                                        {{ $ej->fecha_hora_ejecucion ? $ej->fecha_hora_ejecucion->format('H:i') : '--:--' }}
                                    </td>

                                    {{-- Residente --}}
                                    <td class="px-3 py-2 font-[700] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">
                                        {{ $res ? trim("{$res->nombres} {$res->apellido_paterno}") : 'Residente' }}
                                    </td>

                                    {{-- Intervención --}}
                                    <td class="px-3 py-2 font-medium text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">
                                        {{ $int?->nombre ?? 'Cuidado programado' }}
                                    </td>

                                    {{-- Plan --}}
                                    <td class="px-3 py-2 text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                        {{ $plan?->nombre ?? 'Plan de Cuidado' }}
                                    </td>

                                    {{-- Resultado --}}
                                    <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-xs font-semibold font-[700] {{ $esOmitida ? 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-warning-soft)]' : 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success-soft)]' }}">
                                            {{ $ej->resultado ?? ($esOmitida ? 'No realizada' : 'Realizada') }}
                                        </span>
                                    </td>

                                    {{-- Observación / Motivo Omisión --}}
                                    <td class="px-3 py-2 text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                        @if(!empty($ej->motivo_omision))
                                            <span class="font-bold text-[var(--rm-danger)] block">Motivo: {{ $ej->motivo_omision }}</span>
                                        @endif
                                        @if(!empty($ej->observacion))
                                            <span class="block">{{ $ej->observacion }}</span>
                                        @elseif(empty($ej->motivo_omision))
                                            <span class="italic text-[var(--rm-text-muted)]">Sin observaciones registradas</span>
                                        @endif
                                    </td>

                                    {{-- Profesional --}}
                                    <td class="px-3 py-2 text-[11px] font-semibold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] whitespace-nowrap">
                                        {{ $prof ? trim("{$prof->nombres} {$prof->apellido_paterno}") : 'Enfermería' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-xs text-[var(--rm-text-secondary)]">
                                        <i class="ph ph-folder-open text-3xl text-[var(--rm-warning)] mb-1.5 block"></i>
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
            <div class="w-full max-w-lg bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] rounded-2xl border border-[var(--rm-border)] dark:border-[var(--rm-border)] shadow-xl overflow-hidden text-xs">
                {{-- Header del Modal --}}
                <div class="px-4 py-3 bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] border-b border-[var(--rm-border)] dark:border-[var(--rm-border)] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[var(--rm-warning)] text-white flex items-center justify-center">
                            <i class="ph ph-pencil-line text-sm"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">
                                Registrar Ejecución de Cuidado
                            </h2>
                            <span class="text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                Registro clínico directo en la bitácora del turno
                            </span>
                        </div>
                    </div>
                    <button type="button" 
                        wire:click="cerrarModalRegistrar" 
                        class="p-1 text-[var(--rm-text-secondary)] hover:text-[var(--rm-warning)] rounded-lg transition cursor-pointer">
                        <i class="ph ph-x text-base"></i>
                    </button>
                </div>

                {{-- Cuerpo del Modal --}}
                <div class="p-4 space-y-3.5 max-h-[75vh] overflow-y-auto">
                    {{-- BLOQUE SOLO LECTURA: Contexto Clínico del Residente y Plan --}}
                    <div class="p-3 rounded-xl bg-[var(--rm-surface-soft)]/70 dark:bg-[var(--rm-surface)] border border-[var(--rm-border)]/70 dark:border-[var(--rm-border)] space-y-2">
                        <span class="text-[10px] font-bold text-[var(--rm-warning)] dark:text-[var(--rm-warning-soft)] uppercase tracking-wider block">
                            Datos del Cuidado (Solo Lectura)
                        </span>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-xs font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">Residente:</span>
                                <strong class="text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)]">{{ $datosModal['residente_nombre'] ?? 'N/A' }}</strong>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">Ubicación:</span>
                                <span class="text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] font-semibold">{{ $datosModal['ubicacion'] ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="border-t border-[var(--rm-border)]/50 pt-1.5">
                            <span class="text-xs font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] block">Intervención:</span>
                            <strong class="text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] text-xs block leading-snug">
                                {{ $datosModal['intervencion_nombre'] ?? 'N/A' }}
                            </strong>
                            <p class="text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] mt-0.5">
                                {{ $datosModal['intervencion_descripcion'] ?? '' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-2 pt-1 border-t border-[var(--rm-border)]/50 text-[11px]">
                            <div>
                                <span class="text-[10px] text-[var(--rm-text-secondary)] block">Prioridad:</span>
                                <span class="font-bold text-[var(--rm-warning)]">{{ $datosModal['prioridad'] ?? 'MEDIA' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[var(--rm-text-secondary)] block">Hora prog.:</span>
                                <span class="font-mono font-bold text-[var(--rm-clinical)]">{{ $datosModal['hora_programada'] ?? '--:--' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-[var(--rm-text-secondary)] block">Frecuencia:</span>
                                <span class="font-semibold text-[var(--rm-text-secondary)]">{{ $datosModal['frecuencia'] ?? 'Turno' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE EDITABLE --}}
                    <div class="space-y-3">
                        {{-- Toggle Omisión --}}
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[var(--rm-surface-soft)]/40 border border-[var(--rm-border)]/60">
                            <div>
                                <span class="font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] text-xs">¿Intervención no realizada u omitida?</span>
                                <p class="text-xs font-semibold text-[var(--rm-text-secondary)]">Marque si el residente rechazó o hubo impedimento clínico.</p>
                            </div>
                            <input type="checkbox"
                                wire:model.live="esNoRealizada"
                                class="w-4 h-4 rounded text-[var(--rm-warning)] focus:ring-[var(--rm-action-primary)] focus:border-[var(--rm-action-primary)] cursor-pointer" />
                        </div>

                        {{-- Resultado Obligatorio --}}
                        <div>
                            <label class="block text-xs font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] mb-1">
                                Resultado de la Intervención <span class="text-[var(--rm-danger)]">*</span>
                            </label>
                            @if(!$esNoRealizada)
                                <select wire:model="resultado" class="w-full h-9 px-3 text-xs rounded-lg border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:border-[var(--rm-action-primary)]">
                                    <option value="Satisfactorio">Satisfactorio (completado con éxito)</option>
                                    <option value="Realizado según protocolo">Realizado según protocolo</option>
                                    <option value="Con dificultad / colaboración parcial">Con dificultad / colaboración parcial</option>
                                    <option value="Sin cambios clínicos relevantes">Sin cambios clínicos relevantes</option>
                                    <option value="Evolución favorable">Evolución favorable</option>
                                </select>
                            @else
                                <select wire:model="resultado" class="w-full h-9 px-3 text-xs rounded-lg border border-[var(--rm-danger)] bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] font-bold">
                                    <option value="No realizada / Omitida">No realizada / Omitida</option>
                                    <option value="Rechazada por residente">Rechazada por residente</option>
                                    <option value="Contraindicación clínica transitoria">Contraindicación clínica transitoria</option>
                                    <option value="Ausencia de residente en centro">Ausencia de residente en centro</option>
                                </select>
                            @endif
                            @error('resultado') <span class="text-[11px] text-[var(--rm-danger)] font-semibold">{{ $message }}</span> @enderror
                        </div>

                        {{-- Motivo de Omisión (Obligatorio si no realizada) --}}
                        @if($esNoRealizada)
                            <div>
                                <label class="block text-xs font-bold text-[var(--rm-danger)] mb-1">
                                    Motivo de Omisión Justificado <span class="text-[var(--rm-danger)]">*</span>
                                </label>
                                <textarea wire:model="motivoOmision"
                                    rows="2"
                                    placeholder="Indique con claridad el motivo clínico por el cual no se realizó..."
                                    class="w-full p-2.5 text-xs rounded-lg border border-[var(--rm-danger)] bg-[var(--rm-danger-soft)] text-[var(--rm-clinical)] placeholder-[var(--rm-text-muted)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-danger)]"></textarea>
                                @error('motivoOmision') <span class="text-[11px] text-[var(--rm-danger)] font-semibold block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Observación de Enfermería --}}
                        <div>
                            <label class="block text-xs font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] mb-1">
                                Observación Clínica de Enfermería (Opcional)
                            </label>
                            <textarea wire:model="observacion"
                                rows="2"
                                placeholder="Anotaciones asistenciales relevantes para la bitácora o pase de turno..."
                                class="w-full p-2.5 text-xs rounded-lg border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-inverse)] placeholder-[var(--rm-text-secondary)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:border-[var(--rm-action-primary)]"></textarea>
                            @error('observacion') <span class="text-[11px] text-[var(--rm-danger)] font-semibold block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- NOTA AUTOMÁTICA --}}
                    <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)]/50 text-xs font-semibold text-[var(--rm-text-secondary)] flex items-center gap-1.5">
                        <i class="ph ph-lock-key text-xs text-[var(--rm-action-primary)]"></i>
                        <span>Registro automático con fecha/hora actual (now()), personal firmante y jornada activa.</span>
                    </div>
                </div>

                {{-- Footer del Modal --}}
                <div class="px-4 py-3 bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] border-t border-[var(--rm-border)] dark:border-[var(--rm-border)] flex items-center justify-end gap-2">
                    <button type="button"
                        wire:click="cerrarModalRegistrar"
                        class="px-3 py-1.5 rounded-lg font-semibold text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] bg-[var(--rm-surface)] border border-[var(--rm-border)] cursor-pointer">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="registrarEjecucion"
                        wire:loading.attr="disabled"
                        class="px-4 py-1.5 rounded-lg font-bold text-white bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] shadow-2xs transition cursor-pointer flex items-center gap-1.5">
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
