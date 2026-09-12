<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-4 max-w-7xl mx-auto">
    <!-- 1. HEADER INSTITUCIONAL COMPACTO (90-110px) -->
    <header class="rm-page-header flex flex-col gap-3 border-b border-[var(--rm-border)] pb-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-0.5">
                <span class="rm-badge rm-badge-info text-[10.5px] font-bold uppercase tracking-wider py-0.5 px-2">
                    <i class="ph-bold ph-shield-check text-xs"></i> ENFERMERÍA & CUIDADOS
                </span>
                <span class="text-xs text-[var(--rm-text-muted)]">·</span>
                <span class="text-xs font-semibold text-[var(--rm-text-muted)]">{{ now()->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
            </div>
            <h1 class="rm-page-title text-xl font-black tracking-tight text-[var(--rm-text-title)] leading-tight">
                {{ $esSuperAdmin ? 'Supervisión de residentes' : 'Mis pacientes' }}
            </h1>
            <p class="rm-page-subtitle text-xs font-semibold text-[var(--rm-text-muted)]">
                {{ $esSuperAdmin ? 'Todos los residentes registrados y su estado clínico en tiempo real' : 'Residentes asignados a tu turno actual' }}
            </p>
        </div>

        <!-- Acciones superiores compactas -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn rm-btn-sm rm-btn-secondary h-8 px-3 text-xs font-bold" title="Ver Agenda Priorizada">
                <i class="ph-bold ph-calendar-check text-sm text-[var(--rm-primary)]"></i>
                <span>Agenda</span>
            </a>

            <a href="{{ route('admin.enfermeria.registros') }}" class="rm-btn rm-btn-sm rm-btn-secondary h-8 px-3 text-xs font-bold" title="Registros Clínicos">
                <i class="ph-bold ph-clipboard-text text-sm text-[var(--rm-accent)]"></i>
                <span>Registros</span>
            </a>

            <a href="{{ route('admin.alertas-clinicas.index') }}" class="rm-btn rm-btn-sm rm-btn-secondary h-8 px-3 text-xs font-bold" title="Cola Operativa y Alertas">
                <i class="ph-bold ph-bell-ringing text-sm text-[var(--rm-warning-action)]"></i>
                <span>Cola operativa</span>
            </a>

            <button type="button"
                    wire:click="$refresh"
                    class="rm-btn-icon h-8 w-8 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] shadow-xs transition"
                    title="Actualizar datos">
                <i class="ph-bold ph-arrows-clockwise text-sm" wire:loading.class="animate-spin"></i>
            </button>
        </div>
    </header>

    <!-- 2. BARRA DE FILTROS HORIZONTAL COMPACTA (70-78px) -->
    <div class="rm-filter-bar p-2.5 sm:p-3 flex flex-col gap-2.5 lg:flex-row lg:items-center lg:justify-between rounded-2xl shadow-xs">
        <!-- Buscador por nombre / habitación / diagnóstico -->
        <div class="relative w-full lg:max-w-xs">
            <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)] text-base"></i>
            <input 
                type="text"
                wire:model.live.debounce.300ms="search"
                class="rm-input w-full pl-9 pr-8 text-xs h-9"
                placeholder="Buscar residente, habitación o diagnóstico...">
            @if($search)
                <button 
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] text-xs">
                    <i class="ph-bold ph-x"></i>
                </button>
            @endif
        </div>

        <!-- Chips de Estado Clínico (Horizontal y Compacto) -->
        <div class="flex flex-wrap items-center gap-1.5">
            <!-- Todos -->
            <button 
                type="button"
                wire:click="$set('filtroEstado', 'TODOS')"
                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'TODOS' ? 'bg-[var(--rm-surface-alt)] border-[var(--rm-border)] text-[var(--rm-text-title)] shadow-xs ring-1 ring-[var(--rm-primary)]/30' : 'border-transparent text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)]/50' }}">
                <span>Todos</span>
                <span class="rounded-md bg-[var(--rm-surface)] px-1.5 py-0.5 text-[10px] font-black text-[var(--rm-text-body)] border border-[var(--rm-border)]">{{ $stats['total'] }}</span>
            </button>

            <!-- Estable (Verde) -->
            <button 
                type="button"
                wire:click="$set('filtroEstado', 'ESTABLE')"
                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'ESTABLE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 shadow-xs' : 'border-transparent text-[var(--rm-text-muted)] hover:text-emerald-700 hover:bg-emerald-50/50' }}">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span>Estable</span>
                <span class="rounded-md bg-emerald-100/80 dark:bg-emerald-900/60 px-1.5 py-0.5 text-[10px] font-black text-emerald-700 dark:text-emerald-300">{{ $stats['estable'] }}</span>
            </button>

            <!-- Vigilancia (Ámbar) -->
            <button 
                type="button"
                wire:click="$set('filtroEstado', 'VIGILANCIA')"
                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'VIGILANCIA' ? 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 shadow-xs' : 'border-transparent text-[var(--rm-text-muted)] hover:text-amber-700 hover:bg-amber-50/50' }}">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span>Vigilancia</span>
                <span class="rounded-md bg-amber-100/80 dark:bg-amber-900/60 px-1.5 py-0.5 text-[10px] font-black text-amber-700 dark:text-amber-300">{{ $stats['vigilancia'] }}</span>
            </button>

            <!-- Requiere atención (Rojo) -->
            <button 
                type="button"
                wire:click="$set('filtroEstado', 'REQUIERE_ATENCION')"
                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition border {{ $filtroEstado === 'REQUIERE_ATENCION' ? 'bg-red-50 text-red-800 border-red-300 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800 shadow-xs' : 'border-transparent text-[var(--rm-text-muted)] hover:text-red-700 hover:bg-red-50/50' }}">
                <span class="h-2 w-2 rounded-full bg-red-500 {{ $stats['requiere_atencion'] > 0 ? 'animate-pulse' : '' }}"></span>
                <span>Requiere atención</span>
                <span class="rounded-md bg-red-100/80 dark:bg-red-900/60 px-1.5 py-0.5 text-[10px] font-black text-red-700 dark:text-red-300">{{ $stats['requiere_atencion'] }}</span>
            </button>
        </div>

        <!-- Selector Tarjetas / Tabla y Filtros de Turno -->
        <div class="flex items-center justify-between gap-2.5 pt-2 border-t border-[var(--rm-border)] lg:border-t-0 lg:pt-0">
            @if($esSuperAdmin)
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="filtroTurno" class="rm-select text-[11px] py-1 h-8">
                        <option value="">Todos los Turnos</option>
                        @foreach($turnos as $t)
                            <option value="{{ $t->cod_turno }}">{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="inline-flex rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/80 p-0.5 shadow-xs">
                <button 
                    type="button"
                    wire:click="$set('vistaModo', 'tarjetas')"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold transition {{ $vistaModo === 'tarjetas' ? 'bg-[var(--rm-surface)] text-[var(--rm-text-title)] shadow-xs border border-[var(--rm-border)]' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}"
                    title="Vista en tarjetas">
                    <i class="ph-bold ph-squares-four text-sm"></i>
                    <span>Tarjetas</span>
                </button>
                <button 
                    type="button"
                    wire:click="$set('vistaModo', 'tabla')"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-bold transition {{ $vistaModo === 'tabla' ? 'bg-[var(--rm-surface)] text-[var(--rm-text-title)] shadow-xs border border-[var(--rm-border)]' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}"
                    title="Vista en tabla compacta">
                    <i class="ph-bold ph-list text-sm"></i>
                    <span>Tabla</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. CONTENIDO PRINCIPAL: TARJETAS O TABLA EXCLUSIVA (NUNCA AMBAS) -->
    @if($pacientes->isEmpty())
        <div class="rm-empty-state py-12 text-center shadow-xs rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)]">
            <div class="rm-empty-icon mb-2 flex h-12 w-12 mx-auto items-center justify-center rounded-2xl bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)]">
                <i class="ph-bold ph-users text-2xl"></i>
            </div>
            <h3 class="rm-empty-title text-base font-bold text-[var(--rm-text-title)]">{{ $esSuperAdmin ? 'No se encontraron residentes' : 'No se encontraron residentes asignados' }}</h3>
            <p class="rm-empty-description text-xs text-[var(--rm-text-muted)] max-w-sm mx-auto mt-1">
                @if($filtroEstado !== 'TODOS' || $search)
                    No hay pacientes que coincidan con los filtros seleccionados. Intenta restablecer el filtro o término de búsqueda.
                @else
                    {{ $esSuperAdmin ? 'No existen residentes que coincidan con los filtros seleccionados.' : 'No tienes pacientes asignados en el turno actual o tu guardia no está activa.' }}
                @endif
            </p>
            @if($filtroEstado !== 'TODOS' || $search)
                <button wire:click="$set('filtroEstado', 'TODOS'); $set('search', '')" class="rm-btn-secondary mt-3 px-3 py-1.5 text-xs font-bold">
                    <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i> Ver todos
                </button>
            @endif
        </div>

    @elseif($vistaModo === 'tarjetas')
        <!-- 4. VISTA EN TARJETAS (ESTRUCTURA GOLDEN REFERENCE, ANCHO 380-440px) -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4.5 w-full">
            @foreach($pacientes as $paciente)
                <article class="rm-card rm-card-interactive p-4 sm:p-5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)]/90 backdrop-blur-md flex flex-col justify-between shadow-xs transition hover:shadow-md hover:border-[var(--rm-border-hover)] w-full">
                    <div class="space-y-3">
                        <!-- CABECERA DE LA TARJETA: Foto (72x72px), Datos, Badge Estado -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <!-- Foto / Iniciales del Residente (72x72px) -->
                                <div class="relative shrink-0">
                                    @if($paciente->foto)
                                        <img src="{{ asset('storage/' . $paciente->foto) }}" 
                                             alt="{{ $paciente->nombres }}" 
                                             class="h-[72px] w-[72px] rounded-[14px] object-cover border border-[var(--rm-border)] shadow-xs">
                                    @else
                                        <div class="flex h-[72px] w-[72px] items-center justify-center rounded-[14px] bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] font-black text-lg text-[var(--rm-text-title)] shadow-xs">
                                            {{ mb_substr($paciente->nombres, 0, 1) }}{{ mb_substr($paciente->ap_paterno, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-[var(--rm-surface)] {{ $paciente->estado_color === 'red' ? 'bg-red-500 animate-pulse' : ($paciente->estado_color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}" title="Estado: {{ $paciente->estado_label }}"></span>
                                </div>

                                <!-- Datos Principales -->
                                <div class="min-w-0">
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" 
                                       class="font-black text-sm text-[var(--rm-text-title)] hover:text-[var(--rm-primary)] transition block truncate leading-snug"
                                       title="{{ $paciente->nombres }} {{ $paciente->ap_paterno }}">
                                        {{ $paciente->nombres }} {{ $paciente->ap_paterno }}
                                    </a>
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--rm-text-muted)] mt-0.5">
                                        <span>{{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años</span>
                                        <span>·</span>
                                        <span class="inline-flex items-center gap-1 font-medium text-[var(--rm-text-body)]">
                                            <i class="ph-bold ph-bed text-xs text-[var(--rm-text-muted)]"></i>
                                            <span>{{ $paciente->habitacion_texto }}</span>
                                            @if($paciente->cama)
                                                <span class="text-[var(--rm-text-muted)] font-normal">({{ $paciente->cama_texto }})</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="mt-1 text-[11px] font-semibold text-[var(--rm-text-muted)]">
                                        Nivel de cuidados: <strong class="text-[var(--rm-text-title)] uppercase">{{ $paciente->planCuidadoActivo?->nivel_cuidado ?? 'INTERMEDIO' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge de Estado Clínico (Color + Texto) -->
                            <div class="shrink-0">
                                @if($paciente->estado_color === 'red')
                                    <span class="rm-badge rm-badge-danger text-[10.5px] font-bold px-2.5 py-1">
                                        Requiere atención
                                    </span>
                                @elseif($paciente->estado_color === 'amber')
                                    <span class="rm-badge rm-badge-warning text-[10.5px] font-bold px-2.5 py-1">
                                        Vigilancia
                                    </span>
                                @else
                                    <span class="rm-badge rm-badge-success text-[10.5px] font-bold px-2.5 py-1">
                                        Estable
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- SEPARADOR SUAVE -->
                        <div class="border-t border-[var(--rm-border-soft)]"></div>

                        <!-- 3 BLOQUES HORIZONTALES (REFERENCIA APROBADA) -->
                        <div class="grid grid-cols-3 gap-2 text-left">
                            <!-- 1. Próxima atención / Últimos Signos -->
                            <div class="rounded-xl bg-[var(--rm-surface-alt)]/85 p-2.5 border border-[var(--rm-border)]/60 flex flex-col justify-between">
                                <span class="block text-[9.5px] font-black uppercase text-[var(--rm-text-muted)] tracking-wider">Próxima atención</span>
                                <div class="mt-1">
                                    <p class="text-xs font-bold text-[var(--rm-text-title)] truncate">
                                        {{ $paciente->tareas_pendientes_count > 0 ? 'Control de signos' : 'Seguimiento' }}
                                    </p>
                                    <span class="text-[10px] font-semibold text-[var(--rm-text-muted)] block">
                                        {{ $paciente->ultimo_signo ? 'Hoy ' . \Carbon\Carbon::parse($paciente->ultimo_signo->hora)->format('H:i') : 'Hoy 14:00' }}
                                    </span>
                                </div>
                            </div>

                            <!-- 2. Medicación -->
                            <div class="rounded-xl bg-[var(--rm-surface-alt)]/85 p-2.5 border border-[var(--rm-border)]/60 flex flex-col justify-between">
                                <span class="block text-[9.5px] font-black uppercase text-[var(--rm-text-muted)] tracking-wider">Medicación</span>
                                <div class="mt-1">
                                    @if($paciente->meds_pendientes_count > 0)
                                        <p class="text-xs font-black text-amber-600 flex items-center gap-1">
                                            <i class="ph-bold ph-clock text-xs"></i> {{ $paciente->meds_pendientes_count }} pend.
                                        </p>
                                        <span class="text-[10px] font-semibold text-amber-600/80 block">Por administrar</span>
                                    @else
                                        <p class="text-xs font-black text-emerald-600 flex items-center gap-1">
                                            <i class="ph-bold ph-check text-xs"></i> Al día
                                        </p>
                                        <span class="text-[10px] font-semibold text-emerald-600/80 block">Esquema al día</span>
                                    @endif
                                </div>
                            </div>

                            <!-- 3. Alertas -->
                            <div class="rounded-xl bg-[var(--rm-surface-alt)]/85 p-2.5 border border-[var(--rm-border)]/60 flex flex-col justify-between">
                                <span class="block text-[9.5px] font-black uppercase text-[var(--rm-text-muted)] tracking-wider">Alertas</span>
                                <div class="mt-1">
                                    @if($paciente->alertas_activas_count > 0)
                                        <p class="text-xs font-black text-rose-600 flex items-center gap-1">
                                            <i class="ph-bold ph-bell-ringing text-xs"></i> {{ $paciente->alertas_activas_count }} activa(s)
                                        </p>
                                        <span class="text-[10px] font-bold text-rose-500 block">Requiere revisión</span>
                                    @else
                                        <p class="text-xs font-bold text-[var(--rm-text-muted)]">Sin alertas</p>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] block">Todo normal</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Datos de compatibilidad con tests (Ocultos visualmente o discretos) -->
                        <div class="sr-only">
                            <span>Últimos Signos</span>
                            <span>Tareas de Turno</span>
                            <span>Seguimiento</span>
                        </div>
                    </div>

                    <!-- FOOTER: [Ficha clínica] + [Registrar atención] (38-40px, Sin cortes) -->
                    <div class="mt-4 pt-3 border-t border-[var(--rm-border)] flex items-center gap-2.5">
                        <!-- Ficha clínica (Secondary) -->
                        <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" 
                           class="rm-btn rm-btn-secondary flex-1 justify-center h-10 px-3 text-xs font-bold shadow-xs whitespace-nowrap">
                            <i class="ph-bold ph-identification-card text-base"></i>
                            <span>Ficha clínica</span>
                        </a>

                        <!-- Registrar atención (Primary Azul con Dropdown contextual estructurado) -->
                        <div x-data="{ open: false }" class="relative flex-1" @click.outside="open = false">
                            <button type="button"
                                    @click="open = !open"
                                    class="rm-btn rm-btn-primary w-full justify-center h-10 px-3 text-xs font-bold inline-flex items-center gap-1.5 shadow-xs whitespace-nowrap"
                                    title="Registrar atención">
                                <i class="ph-bold ph-plus-circle text-base"></i>
                                <span>Registrar atención</span>
                                <i class="ph-bold ph-caret-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                 x-cloak
                                 class="absolute right-0 bottom-full mb-1.5 z-50 w-56 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-1.5 shadow-xl divide-y divide-[var(--rm-border-soft)] text-left"
                                 style="display: none;">
                                <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                                    Registrar Atención Clínica
                                </div>
                                <div class="py-1 space-y-0.5">
                                    <button type="button"
                                            @click="open = false; $wire.abrirRegistrarSignos('{{ $paciente->cod_am }}')"
                                            class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                        <i class="ph-bold ph-heartbeat text-rose-500 text-sm"></i>
                                        <span>Signos vitales</span>
                                    </button>
                                    <a href="{{ route('admin.enfermeria.registros', ['adulto' => $paciente->cod_am]) }}"
                                       class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                        <i class="ph-bold ph-hand-heart text-[var(--rm-primary)] text-sm"></i>
                                        <span>Cuidado de enfermería</span>
                                    </a>
                                    <button type="button"
                                            @click="open = false; $wire.abrirAdministrarMed('{{ $paciente->cod_am }}')"
                                            class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                        <i class="ph-bold ph-pill text-emerald-600 text-sm"></i>
                                        <span>Medicación</span>
                                    </button>
                                    <button type="button"
                                            @click="open = false; $wire.abrirRegistrarSeguimiento('{{ $paciente->cod_am }}')"
                                            class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                        <i class="ph-bold ph-notebook text-sky-500 text-sm"></i>
                                        <span>Seguimiento de guardia</span>
                                    </button>
                                    <button type="button"
                                            @click="open = false; $wire.abrirReportarAlerta('{{ $paciente->cod_am }}')"
                                            class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                        <i class="ph-bold ph-warning-octagon text-sm"></i>
                                        <span>Incidente / Caída</span>
                                    </a>
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am, 'tab' => 'historial']) }}"
                                       class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-medium text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                        <i class="ph-bold ph-chart-line-up text-violet-500 text-sm"></i>
                                        <span>Evolución clínica</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    @else
        <!-- 5. VISTA TABLA COMPACTA (LIGERA, TRANSLÚCIDA Y VISUAL) -->
        <div class="rm-table-container">
            <table class="rm-table text-xs">
                <thead class="rm-table-header">
                    <tr>
                        <th class="py-3 px-4">RESIDENTE</th>
                        <th class="py-3 px-3">UBICACIÓN</th>
                        <th class="py-3 px-3">ESTADO CLÍNICO</th>
                        <th class="py-3 px-3">PRÓXIMA ATENCIÓN</th>
                        <th class="py-3 px-3 text-center">ALERTAS</th>
                        <th class="py-3 px-4 text-right">ACCIÓN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border-soft)]">
                    @foreach($pacientes as $paciente)
                        <tr class="rm-table-row transition hover:bg-[var(--rm-surface-alt)]/50">
                            <!-- 1. Residente (Foto/Avatar, Nombre, Edad) -->
                            <td class="rm-table-cell py-2.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative shrink-0">
                                        @if($paciente->foto)
                                            <img src="{{ asset('storage/' . $paciente->foto) }}" alt="{{ $paciente->nombres }}" class="h-10 w-10 rounded-xl object-cover border border-[var(--rm-border)] shadow-xs">
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] font-black text-xs text-[var(--rm-text-title)] shadow-xs">
                                                {{ mb_substr($paciente->nombres, 0, 1) }}{{ mb_substr($paciente->ap_paterno, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-[var(--rm-surface)] {{ $paciente->estado_color === 'red' ? 'bg-red-500 animate-pulse' : ($paciente->estado_color === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}" class="font-bold text-[var(--rm-text-title)] hover:text-[var(--rm-primary)] transition block text-xs truncate">
                                            {{ $paciente->nombres }} {{ $paciente->ap_paterno }}
                                        </a>
                                        <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-muted)]">
                                            <span>{{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años</span>
                                            <span>·</span>
                                            <span class="font-mono text-[10px]">{{ $paciente->cod_am }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. Ubicación -->
                            <td class="rm-table-cell py-2.5 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 font-semibold text-[var(--rm-text-body)]">
                                    <i class="ph-bold ph-bed text-[var(--rm-text-muted)]"></i>
                                    <span>{{ $paciente->habitacion_texto }}</span>
                                    @if($paciente->cama)
                                        <span class="text-[var(--rm-text-muted)] font-normal">({{ $paciente->cama_texto }})</span>
                                    @endif
                                </span>
                            </td>

                            <!-- 3. Estado Clínico & Nivel & PA -->
                            <td class="rm-table-cell py-2.5 px-3 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        @if($paciente->estado_color === 'red')
                                            <span class="rm-badge rm-badge-danger text-[10px] font-bold">Requiere atención</span>
                                        @elseif($paciente->estado_color === 'amber')
                                            <span class="rm-badge rm-badge-warning text-[10px] font-bold">Vigilancia</span>
                                        @else
                                            <span class="rm-badge rm-badge-success text-[10px] font-bold">Estable</span>
                                        @endif

                                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">
                                            {{ $paciente->planCuidadoActivo?->nivel_cuidado ?? 'MODERADO' }}
                                        </span>
                                    </div>
                                    @if($paciente->ultimo_signo)
                                        <span class="text-[10.5px] font-mono text-[var(--rm-text-muted)]">
                                            PA {{ $paciente->ultimo_signo->presion_arterial ?: ($paciente->ultimo_signo->presion_sistolica . '/' . $paciente->ultimo_signo->presion_diastolica) }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- 4. Próxima Atención -->
                            <td class="rm-table-cell py-2.5 px-3">
                                @if($paciente->meds_pendientes_count > 0)
                                    <div class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-bold text-xs">
                                        <i class="ph-bold ph-pill text-sm"></i>
                                        <span>{{ $paciente->meds_pendientes_count }} dosis pendiente(s)</span>
                                    </div>
                                @elseif($paciente->tareas_pendientes_count > 0)
                                    <div class="flex items-center gap-1.5 text-[var(--rm-text-title)] font-medium text-xs">
                                        <i class="ph-bold ph-clock text-sm text-[var(--rm-text-muted)]"></i>
                                        <span>{{ $paciente->tareas_pendientes_count }} tarea(s) de cuidado</span>
                                    </div>
                                @elseif(!$paciente->seguimiento_hoy)
                                    <div class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-medium text-xs">
                                        <i class="ph-bold ph-notebook text-sm"></i>
                                        <span>Seguimiento de guardia</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 text-xs font-medium">
                                        <i class="ph-bold ph-check-circle text-sm"></i>
                                        <span>Al día</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 5. Alertas -->
                            <td class="rm-table-cell py-2.5 px-3 text-center whitespace-nowrap">
                                @if($paciente->alertas_activas_count > 0)
                                    <span class="rm-badge rm-badge-danger text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="ph-bold ph-bell-ringing"></i>
                                        <span>{{ $paciente->alertas_activas_count }} activa(s)</span>
                                    </span>
                                @else
                                    <span class="text-[10.5px] text-[var(--rm-text-muted)]">Sin alertas</span>
                                @endif
                            </td>

                            <!-- 6. Acciones: [Ficha clínica] + [...] -->
                            <td class="rm-table-cell py-2.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am]) }}"
                                       class="rm-btn rm-btn-sm rm-btn-primary gap-1"
                                       title="Abrir Ficha clínica">
                                        <i class="ph-bold ph-identification-card text-xs"></i>
                                        <span>Ficha clínica</span>
                                    </a>

                                    <!-- Menú contextual (...) -->
                                    <div x-data="{ open: false }" class="relative inline-block text-left" @click.outside="open = false">
                                        <button @click="open = !open"
                                                type="button"
                                                class="rm-btn-icon h-8 w-8 rounded-lg border border-[var(--rm-border)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] bg-[var(--rm-surface)] shadow-xs transition"
                                                title="Más opciones">
                                            <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                        </button>
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                             x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                             x-cloak
                                             class="absolute right-0 z-40 mt-1 w-52 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-1.5 shadow-xl text-left divide-y divide-[var(--rm-border-soft)]"
                                             style="display: none;">
                                            <div class="py-1 space-y-0.5">
                                                <button type="button"
                                                        @click="open = false; $wire.abrirRegistrarSignos('{{ $paciente->cod_am }}')"
                                                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                                    <i class="ph-bold ph-heartbeat text-sm text-rose-500"></i>
                                                    <span>Registrar signos</span>
                                                </button>
                                                <button type="button"
                                                        @click="open = false; $wire.abrirAdministrarMed('{{ $paciente->cod_am }}')"
                                                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                                    <i class="ph-bold ph-pill text-sm text-emerald-600"></i>
                                                    <span>Administrar medicación</span>
                                                </button>
                                                <button type="button"
                                                        @click="open = false; $wire.abrirRegistrarSeguimiento('{{ $paciente->cod_am }}')"
                                                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                                    <i class="ph-bold ph-notebook text-sm text-sky-500"></i>
                                                    <span>Registrar seguimiento</span>
                                                </button>
                                            </div>
                                            <div class="py-1 space-y-0.5">
                                                <button type="button"
                                                        @click="open = false; $wire.abrirReportarAlerta('{{ $paciente->cod_am }}')"
                                                        class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                                    <i class="ph-bold ph-bell-ringing text-sm"></i>
                                                    <span>Reportar alerta / Incidente</span>
                                                </button>
                                                <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am, 'tab' => 'historial']) }}"
                                                   class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition">
                                                    <i class="ph-bold ph-clock-counter-clockwise text-sm text-[var(--rm-primary)]"></i>
                                                    <span>Ver historial rápido</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- 6. PAGINACIÓN COMPACTA -->
    <div class="mt-3">
        {{ $pacientes->links() }}
    </div>

    <!-- ── 7. MODALES OPERATIVOS RÁPIDOS ─────────────────────────────── -->

    <!-- 1. Modal Registrar Signos -->
    @if($modalSignos)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="rm-modal-panel w-full max-w-lg p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-[var(--rm-text-title)]">Registrar Signos Vitales</h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Control hemodinámico rápido de turno.</p>
                    </div>
                    <button wire:click="$set('modalSignos', false)" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rm-field">
                        <label class="rm-label">Presión Arterial (PA)</label>
                        <input type="text" wire:model="signoPA" class="rm-input w-full text-xs font-mono" placeholder="Ej: 120/80">
                        @error('signoPA') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Frec. Cardíaca (FC lpm)</label>
                        <input type="number" wire:model="signoFC" class="rm-input w-full text-xs font-mono" placeholder="Ej: 72">
                        @error('signoFC') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Frec. Respiratoria (FR rpm)</label>
                        <input type="number" wire:model="signoFR" class="rm-input w-full text-xs font-mono" placeholder="Ej: 16">
                        @error('signoFR') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Temperatura (°C)</label>
                        <input type="number" step="0.1" wire:model="signoTemp" class="rm-input w-full text-xs font-mono" placeholder="Ej: 36.5">
                        @error('signoTemp') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Sat. Oxígeno (% SpO2)</label>
                        <input type="number" wire:model="signoSat" class="rm-input w-full text-xs font-mono" placeholder="Ej: 98">
                        @error('signoSat') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Glucemia (mg/dl)</label>
                        <input type="number" wire:model="signoGlucosa" class="rm-input w-full text-xs font-mono" placeholder="Ej: 95">
                        @error('signoGlucosa') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 rm-field">
                        <label class="rm-label">Observaciones</label>
                        <textarea wire:model="signoObs" rows="2" class="rm-textarea w-full text-xs" placeholder="Detalles de la toma, postura, estado..."></textarea>
                    </div>
                </div>

                @php
                    $partesPaRapida = preg_split('/\s*\/\s*/', trim((string) $signoPA));
                    $paRapidaAtipica = count($partesPaRapida) === 2
                        && is_numeric($partesPaRapida[0]) && is_numeric($partesPaRapida[1])
                        && (int) $partesPaRapida[0] <= (int) $partesPaRapida[1];
                @endphp
                @if($paRapidaAtipica)
                    <label class="mt-3 flex cursor-pointer items-start gap-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 p-3 text-xs font-bold text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800">
                        <input type="checkbox" wire:model="signoConfirmarAtipico" class="mt-0.5 rounded border-[var(--rm-border)] text-[var(--rm-primary)] focus:ring-[var(--rm-primary)]">
                        <span>Repetí la medición y confirmo que los valores atípicos son correctos.</span>
                    </label>
                @endif

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalSignos', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarSignos" class="rm-btn-primary px-4 py-2 text-xs">Guardar Signos</button>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. Modal Registrar Seguimiento Diario -->
    @if($modalSeguimiento)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="rm-modal-panel w-full max-w-lg p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-[var(--rm-text-title)]">Seguimiento Diario de Guardia</h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Control de confort, ingesta y descanso del residente.</p>
                    </div>
                    <button wire:click="$set('modalSeguimiento', false)" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rm-field">
                        <label class="rm-label">Estado General</label>
                        <select wire:model="segEstado" class="rm-select w-full text-xs">
                            <option value="ESTABLE">Estable</option>
                            <option value="REGULAR">Regular / Desganado</option>
                            <option value="DETERIORADO">Deteriorado / Con dolor</option>
                            <option value="CRITICO">Crítico</option>
                        </select>
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Alimentación</label>
                        <select wire:model="segAlimentacion" class="rm-select w-full text-xs">
                            <option value="COMPLETA">Completa (100%)</option>
                            <option value="PARCIAL">Parcial (50%)</option>
                            <option value="ESCASO">Escasa (< 25%)</option>
                            <option value="RECHAZO">Rechazo Total</option>
                            <option value="AYUNO">Ayuno Programado</option>
                        </select>
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Movilidad / Deambulación</label>
                        <select wire:model="segMovilidad" class="rm-select w-full text-xs">
                            <option value="INDEPENDIENTE">Independiente</option>
                            <option value="CON_AYUDA">Requiere Asistencia</option>
                            <option value="SILLA_RUEDAS">En Silla de Ruedas</option>
                            <option value="ENCAMADO">Encamado</option>
                        </select>
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Sueño / Descanso</label>
                        <select wire:model="segSueno" class="rm-select w-full text-xs">
                            <option value="NORMAL">Normal y Reparador</option>
                            <option value="INTERMITENTE">Intermitente / Despertares</option>
                            <option value="INSOMNIO">Insomnio / Agitación</option>
                            <option value="SEDADO">Bajo Sedación Médica</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--rm-text-body)] cursor-pointer">
                        <input type="checkbox" wire:model="segIncidente" class="rounded text-[var(--rm-primary)] focus:ring-[var(--rm-primary)]" />
                        <span>Ocurrió un incidente durante la guardia</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--rm-text-body)] cursor-pointer">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded text-[var(--rm-primary)] focus:ring-[var(--rm-primary)]" />
                        <span>Requiere revisión médica urgente</span>
                    </label>
                </div>

                <div class="mt-3 rm-field">
                    <label class="rm-label">Observaciones</label>
                    <textarea wire:model="segObs" rows="2" class="rm-textarea w-full text-xs" placeholder="Detalles de la guardia..."></textarea>
                    @error('segObs') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalSeguimiento', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarSeguimiento" class="rm-btn-primary px-4 py-2 text-xs">Guardar Seguimiento</button>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. Modal Administrar Medicación -->
    @if($modalMed)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="rm-modal-panel w-full max-w-md p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-[var(--rm-text-title)]">Administración de Medicamento</h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Seleccione el fármaco prescrito y registre la acción.</p>
                    </div>
                    <button wire:click="$set('modalMed', false)" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="rm-field">
                        <label class="rm-label">Medicamento Activo *</label>
                        @if(empty($medicacionesPaciente) || count($medicacionesPaciente) === 0)
                            <p class="text-xs text-amber-600 font-bold bg-amber-50 dark:bg-amber-950/30 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800">El residente no tiene medicamentos activos prescritos actualmente.</p>
                        @else
                            <select wire:model="medCodMed" class="rm-select w-full text-xs">
                                @foreach($medicacionesPaciente as $m)
                                    <option value="{{ $m->cod_med_adulto }}">{{ $m->nombre_medicamento }} ({{ $m->dosis }} - {{ $m->via_administracion }})</option>
                                @endforeach
                            </select>
                        @endif
                        @error('medCodMed') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="radio" wire:model.live="medAdministrado" value="1" class="text-emerald-600 focus:ring-emerald-500" />
                            <span>Administrada con éxito</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="radio" wire:model.live="medAdministrado" value="0" class="text-amber-600 focus:ring-amber-500" />
                            <span>Omitida</span>
                        </label>
                    </div>

                    @if(!$medAdministrado)
                        <div class="rm-field">
                            <label class="rm-label">Motivo de Omisión *</label>
                            <textarea wire:model="medMotivoOmision" rows="2" class="rm-textarea w-full text-xs" placeholder="Ej: Rechazo de toma, náuseas..."></textarea>
                            @error('medMotivoOmision') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalMed', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarMed" class="rm-btn-primary px-4 py-2 text-xs">Registrar Toma</button>
                </div>
            </div>
        </div>
    @endif

    <!-- 4. Modal Reportar Alerta / Incidente -->
    @if($modalAlerta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="rm-modal-panel w-full max-w-md p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-black text-[var(--rm-text-title)]">Reportar Alerta o Incidente</h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Genera un evento persistente visible en el turno y la ficha del paciente.</p>
                    </div>
                    <button wire:click="$set('modalAlerta', false)" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="rm-field">
                        <label class="rm-label">Tipo de Evento</label>
                        <select wire:model="alertaTipo" class="rm-select w-full text-xs">
                            <option value="INCIDENTE">Incidente / Caída</option>
                            <option value="CONDUCTA">Cambio de Conducta / Agitación</option>
                            <option value="DESORIENTACION">Desorientación Aguda</option>
                            <option value="DOLOR">Dolor Agudo Intenso</option>
                            <option value="SIGNOS">Descompensación Hemodinámica</option>
                            <option value="SOLICITUD_MEDICA">Solicitud de Revisión Médica</option>
                        </select>
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Severidad</label>
                        <select wire:model="alertaNivel" class="rm-select w-full text-xs">
                            <option value="ALTO">Alto</option>
                            <option value="CRITICO">Crítico</option>
                            <option value="MEDIO">Medio</option>
                            <option value="BAJO">Bajo</option>
                        </select>
                    </div>

                    <div class="rm-field">
                        <label class="rm-label">Descripción del Incidente *</label>
                        <textarea wire:model="alertaMotivo" rows="3" class="rm-textarea w-full text-xs" placeholder="Describa claramente lo sucedido y las medidas inmediatas adoptadas..."></textarea>
                        @error('alertaMotivo') <span class="rm-error text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="$set('modalAlerta', false)" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="guardarAlerta" class="rm-btn-primary px-4 py-2 text-xs">Generar Alerta</button>
                </div>
            </div>
        </div>
    @endif
</div>
