<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-5">
    {{-- Encabezado Institucional Canónico --}}
    <header class="rm-page-header">
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--rm-accent)] text-white shadow-md">
                <i class="ph-bold ph-calendar-check text-2xl"></i>
            </span>
            <div class="rm-page-title-group">
                <div class="flex items-center gap-2 mb-1">
                    <span class="rm-badge rm-badge-info text-[10.5px]">
                        <i class="ph-bold ph-clock"></i>
                        {{ $esSuperAdmin ? 'Supervisión Institucional' : 'Puesto de Trabajo' }}
                    </span>
                    @if($turno)
                        <span class="text-xs text-[var(--rm-text-muted)]">•</span>
                        <span class="text-xs font-semibold text-[var(--rm-text-muted)]">{{ $turno->nombre }}</span>
                    @endif
                </div>
                <h1 class="rm-page-title text-[var(--rm-text-title)]">
                    {{ $esSuperAdmin ? 'Agenda institucional de Enfermería' : 'Agenda Priorizada de Enfermería' }}
                </h1>
                <p class="rm-page-subtitle text-[var(--rm-text-muted)]">
                    {{ $esSuperAdmin ? 'Alertas, medicación y cuidados de todos los residentes, ordenados por urgencia.' : 'Alertas, medicación y cuidados de sus residentes asignados, ordenados por urgencia.' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn rm-btn-secondary text-xs">
                <i class="ph-bold ph-squares-four text-sm"></i>
                <span>{{ $esSuperAdmin ? 'Resumen global' : 'Mi turno' }}</span>
            </a>
            <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn rm-btn-secondary text-xs">
                <i class="ph-bold ph-users text-sm"></i>
                <span>{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis pacientes' }}</span>
            </a>
            @if(!$esSuperAdmin && $turno && !$recepcion)
                <button wire:click="recibirTurno" class="rm-btn rm-btn-primary text-xs cursor-pointer">
                    <i class="ph-bold ph-handshake text-sm"></i>
                    <span>Recibir turno</span>
                </button>
            @elseif($recepcion)
                <span class="rm-badge rm-badge-success text-xs font-bold">
                    <i class="ph-bold ph-check-circle"></i>
                    Recibido {{ $recepcion->fecha_hora_recepcion->format('H:i') }}
                </span>
            @endif
        </div>
    </header>

    @if(!$esSuperAdmin && !$turno)
        <div class="rm-alert rm-alert-warning flex items-center gap-3">
            <i class="ph-bold ph-warning text-xl"></i>
            <span>No existe un turno activo asignado para hoy. Solicite la asignación antes de registrar cuidados.</span>
        </div>
    @endif

    {{-- BARRA DE FILTROS CANÓNICA --}}
    <section class="rm-card rm-card-glass p-3 rounded-2xl border border-[var(--rm-border)]">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-md">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)] text-sm"></i>
                <input wire:model.live.debounce.300ms="buscar"
                       type="search"
                       placeholder="Buscar residente, medicamento o tarea..."
                       class="rm-input w-full pl-9 py-2 text-xs">
            </div>

            <div class="flex flex-wrap gap-1.5">
                @foreach(['PENDIENTES' => 'Pendientes', 'VENCIDOS' => 'Vencidos', 'PROXIMOS' => 'Próximos', 'MEDICACION' => 'Medicación', 'ALERTAS' => 'Alertas', 'TAREAS' => 'Cuidados', 'TODOS' => 'Todos'] as $valor => $etiqueta)
                    <button wire:click="$set('filtro', '{{ $valor }}')"
                            type="button"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition border cursor-pointer {{ $filtro === $valor ? 'bg-[var(--rm-primary)] text-white border-[var(--rm-primary)] shadow-xs' : 'border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)]' }}">
                        {{ $etiqueta }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- LISTADO DE ACTIVIDADES ASISTENCIALES --}}
    <section class="space-y-3">
        @forelse($items as $item)
            @php
                $paciente = $item['paciente'];
                $bordeSeveridad = match($item['prioridad']) {
                    1 => 'border-l-4 border-l-[var(--rm-danger-action)] ring-1 ring-rose-500/20 bg-rose-50/20 dark:bg-rose-950/10',
                    2, 3, 4 => 'border-l-4 border-l-[var(--rm-warning-action)] bg-amber-50/20 dark:bg-amber-950/10',
                    default => 'border-l-4 border-l-[var(--rm-primary)]',
                };
            @endphp
            <article class="rm-card rm-card-interactive rm-card-glass p-3.5 sm:p-4 rounded-2xl border border-[var(--rm-border)] {{ $bordeSeveridad }}">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex min-w-0 items-start gap-3.5">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xl text-[var(--rm-primary)] shadow-xs">
                            <i class="ph-bold {{ $item['tipo'] === 'MEDICACION' ? 'ph-pill text-[var(--rm-warning-action)]' : ($item['tipo'] === 'ALERTA' ? 'ph-bell-ringing text-[var(--rm-danger-action)]' : 'ph-check-square text-[var(--rm-accent)]') }}"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                <span class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">
                                    {{ $item['tipo'] }} • {{ $item['estado'] }}
                                </span>
                                <span class="text-[10px] text-[var(--rm-text-muted)]">•</span>
                                <time class="text-xs font-bold text-[var(--rm-text-title)] font-mono">
                                    {{ $item['fecha_hora']?->format('H:i') }}
                                </time>
                            </div>
                            <h2 class="truncate text-sm font-bold text-[var(--rm-text-title)]">
                                {{ $paciente?->nombres }} {{ $paciente?->ap_paterno }} — {{ $item['titulo'] }}
                            </h2>
                            <p class="mt-0.5 text-xs text-[var(--rm-text-body)]">
                                {{ $item['detalle'] }}
                            </p>
                            <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-[var(--rm-text-muted)]">
                                <i class="ph-bold ph-bed text-xs"></i>
                                <span>Habitación {{ $paciente?->habitacion?->numero ?? $paciente?->habitacion?->codigo ?? 'sin asignar' }}</span>
                            </div>
                        </div>
                    </div>
                    @if($paciente)
                        <div class="shrink-0 self-end md:self-center">
                            <a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $paciente->cod_am, 'tab' => $item['tipo'] === 'ALERTA' ? 'alertas' : ($item['tipo'] === 'MEDICACION' ? 'medicacion' : 'cuidado')]) }}"
                               class="rm-btn rm-btn-sm rm-btn-primary gap-1">
                                <span>Abrir y registrar</span>
                                <i class="ph-bold ph-arrow-right text-xs"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rm-card rm-card-glass p-12 text-center rounded-2xl border border-dashed border-[var(--rm-border)]">
                <i class="ph-bold ph-check-circle text-4xl text-[var(--rm-state-success-border)] mb-2 block"></i>
                <h2 class="text-base font-bold text-[var(--rm-text-title)]">No hay actividades en este filtro</h2>
                <p class="mt-1 text-xs text-[var(--rm-text-muted)]">La agenda se actualizará desde órdenes, planes y alertas en tiempo real.</p>
            </div>
        @endforelse
    </section>
</div>
