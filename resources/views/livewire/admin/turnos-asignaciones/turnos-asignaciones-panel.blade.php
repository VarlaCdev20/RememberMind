<div class="min-h-[calc(100vh-8rem)] overflow-x-hidden bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1480px] space-y-5">
        <section
            class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-[0_16px_44px_rgba(47,62,92,0.11)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-4xl">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">
                        <i class="ph-bold ph-calendar-check text-sm"></i>
                        Administracion
                    </span>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo sm:text-3xl">Horarios y asignaciones
                    </h1>
                    <p class="mt-1 max-w-3xl text-sm font-bold leading-relaxed text-apoyo">
                        Planificacion administrativa de turnos, horarios, asignaciones y cobertura institucional.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('turnos.asignar')
                        <button type="button" wire:click="abrirModalHorario"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-4 text-[11px] font-bold uppercase tracking-wider text-parrafo transition hover:-translate-y-0.5 hover:bg-estado-exitoBg">
                            <i class="ph-bold ph-clock-afternoon text-sm"></i>
                            Registrar horario
                        </button>
                        <button type="button" wire:click="abrirModalAsignacion"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-[11px] font-bold uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:-translate-y-0.5 hover:bg-fondo-panel">
                            <i class="ph-bold ph-user-plus text-sm"></i>
                            Registrar asignacion
                        </button>
                    @endcan

                    @can('turnos.crear')
                        <button type="button" wire:click="abrirModalTurno"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-fuerte bg-fondo-panel px-4 text-[11px] font-bold uppercase tracking-wider text-titulo transition hover:-translate-y-0.5 hover:border-borde-fuerte">
                            <i class="ph-bold ph-plus-circle text-sm"></i>
                            Nuevo turno
                        </button>
                    @endcan
                </div>
            </div>
        </section>

        <nav class="overflow-x-auto rounded-2xl border border-borde-suave bg-fondo-panel p-2 shadow-sm">
            <div class="flex min-w-max gap-1">
                @foreach ([
        'resumen' => ['Resumen', 'ph-chart-pie-slice'],
        'horarios' => ['Horarios', 'ph-clock'],
        'turnos' => ['Turnos', 'ph-clock-countdown'],
        'asignaciones' => ['Asignaciones', 'ph-users-three'],
        'calendario' => ['Calendario', 'ph-calendar-dots'],
        'alertas' => ['Alertas', 'ph-warning-diamond'],
        'reportes' => ['Reportes', 'ph-file-text'],
    ] as $tab => [$label, $icon])
                    <button type="button" wire:click="cambiarTab('{{ $tab }}')"
                        class="inline-flex h-10 items-center gap-2 rounded-xl px-3 text-xs font-bold transition {{ $tabActiva === $tab ? 'bg-boton-principal text-inverso shadow-sm' : 'text-apoyo hover:bg-fondo-card/45 hover:text-titulo' }}">
                        <i class="ph-bold {{ $icon }}"></i>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </nav>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
        ['icon' => 'ph-identification-card', 'label' => 'Personal activo', 'value' => $metricas['personal_activo'], 'note' => 'Usuarios institucionales activos', 'class' => 'bg-fondo-panel text-titulo border-borde-fuerte'],
        ['icon' => 'ph-clock', 'label' => 'Horarios registrados', 'value' => $metricas['horarios_registrados'], 'note' => $metricas['horarios_activos'] . ' activos', 'class' => 'bg-sky-100/75 text-sky-700 border-sky-200'],
        ['icon' => 'ph-timer', 'label' => 'Turnos activos', 'value' => $metricas['turnos_activos'], 'note' => 'Bloques institucionales', 'class' => 'bg-indigo-100/75 text-indigo-700 border-indigo-200'],
        ['icon' => 'ph-calendar-check', 'label' => 'Asignaciones de hoy', 'value' => $metricas['asignaciones_hoy'], 'note' => 'Cobertura del dia', 'class' => 'bg-estado-exitoBg text-parrafo border-estado-exitoBorde'],
        ['icon' => 'ph-calendar-x', 'label' => 'Turnos sin cubrir', 'value' => $metricas['turnos_sin_cubrir'], 'note' => 'Areas sin asignacion activa', 'class' => 'bg-amber-100/75 text-amber-700 border-amber-200'],
        ['icon' => 'ph-warning-diamond', 'label' => 'Conflictos detectados', 'value' => $metricas['conflictos_detectados'], 'note' => 'Alertas operativas', 'class' => 'bg-rose-100/75 text-rose-700 border-rose-200'],
        ['icon' => 'ph-gauge', 'label' => 'Personal con sobrecarga', 'value' => $metricas['personal_sobrecarga'], 'note' => '4 o mas asignaciones activas', 'class' => 'bg-violet-100/75 text-violet-700 border-violet-200'],
        ['icon' => 'ph-arrow-fat-lines-right', 'label' => 'Proximas asignaciones', 'value' => $metricas['proximas_asignaciones'], 'note' => 'Vigentes o por iniciar', 'class' => 'bg-estado-peligroBg text-parrafo border-borde-focus'],
    ] as $card)
                <article class="rounded-2xl border {{ $card['class'] }} p-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-card/55">
                            <i class="ph-bold {{ $card['icon'] }} text-lg"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xl font-extrabold leading-none">{{ $card['value'] }}</p>
                            <p class="mt-1 truncate text-[11px] font-bold text-titulo">{{ $card['label'] }}</p>
                        </div>
                    </div>
                    <p class="mt-2 truncate text-[11px] font-bold text-apoyo">{{ $card['note'] }}</p>
                </article>
            @endforeach
        </section>

        @if ($tabActiva === 'resumen')
            <section class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-extrabold text-titulo">Resumen operativo</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">Lectura rapida de cobertura, turnos y carga
                                semanal.</p>
                        </div>
                        <button type="button" wire:click="cambiarTab('calendario')"
                            class="rounded-xl bg-boton-principal px-3 py-2 text-[11px] font-bold uppercase text-inverso">Ver
                            calendario</button>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-2">
                        <div class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-apoyo">Asignaciones de hoy</h3>
                            <div class="mt-3 space-y-2">
                                @forelse($asignacionesHoyLista as $asig)
                                    <button type="button"
                                        wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')"
                                        class="w-full rounded-xl border border-borde-suave bg-fondo-panel p-3 text-left transition hover:border-borde-focus">
                                        <span
                                            class="block truncate text-sm font-bold text-titulo">{{ $asig->usuario?->name ?? 'Sin usuario' }}</span>
                                        <span
                                            class="block truncate text-xs font-bold text-apoyo">{{ $asig->area?->nombre ?? 'Sin area' }}
                                            · {{ $asig->turno?->nombre ?? 'Sin turno' }}</span>
                                    </button>
                                @empty
                                    <p
                                        class="rounded-xl border border-dashed border-borde-suave p-4 text-center text-xs font-bold text-apoyo">
                                        No existen asignaciones programadas para hoy.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-apoyo">Proximos turnos</h3>
                            <div class="mt-3 space-y-2">
                                @forelse($proximasAsignaciones as $asig)
                                    <button type="button"
                                        wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')"
                                        class="w-full rounded-xl bg-fondo-panel p-3 text-left transition hover:bg-fondo-card/60">
                                        <span class="flex items-center justify-between gap-2">
                                            <span
                                                class="truncate text-sm font-bold text-titulo">{{ $asig->usuario?->name ?? 'Sin usuario' }}</span>
                                            <span
                                                class="shrink-0 rounded-full bg-fondo-app px-2 py-0.5 text-[9px] font-bold text-apoyo">{{ $asig->fecha_inicio?->format('d/m') ?? 'S/F' }}</span>
                                        </span>
                                        <span
                                            class="mt-1 block truncate text-xs font-bold text-apoyo">{{ $asig->turno?->nombre ?? 'Sin turno' }}
                                            · {{ implode(', ', $asig->dias_semana ?: []) }}</span>
                                    </button>
                                @empty
                                    <p
                                        class="rounded-xl border border-dashed border-borde-suave p-4 text-center text-xs font-bold text-apoyo">
                                        No hay proximas asignaciones registradas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-borde-suave bg-fondo-card/35 p-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-apoyo">Resumen semanal</h3>
                        <div class="mt-3 grid gap-2 md:grid-cols-7">
                            @foreach ($resumenSemanal as $dia => $info)
                                <div class="rounded-xl bg-fondo-panel p-3 text-center">
                                    <p class="text-[10px] font-bold uppercase text-apoyo">
                                        {{ substr($info['label'], 0, 3) }}</p>
                                    <p class="mt-1 text-lg font-extrabold text-titulo">{{ $info['asignaciones'] }}</p>
                                    <p class="text-[10px] font-bold text-apoyo">{{ $info['horarios'] }} horarios</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <h2 class="text-base font-extrabold text-titulo">Alertas operativas</h2>
                        <div class="mt-3 space-y-2">
                            @forelse($alertas->take(6) as $alerta)
                                <div
                                    class="rounded-xl border {{ $alerta['prioridad'] === 'Alta' ? 'border-rose-200 bg-rose-50/70' : 'border-amber-200 bg-amber-50/70' }} p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-titulo">{{ $alerta['tipo'] }} ·
                                                {{ $alerta['persona'] }}</p>
                                            <p class="mt-1 text-[11px] font-bold text-apoyo">
                                                {{ $alerta['descripcion'] }}</p>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full bg-fondo-card/65 px-2 py-0.5 text-[9px] font-bold text-apoyo">{{ $alerta['prioridad'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p
                                    class="rounded-xl border border-dashed border-borde-suave p-4 text-center text-xs font-bold text-apoyo">
                                    No hay alertas operativas pendientes.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                        <h2 class="text-base font-extrabold text-titulo">Carga por areas y turnos</h2>
                        <div class="mt-3 grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                @forelse($distribucionAreas as $area => $count)
                                    <div>
                                        <div class="mb-1 flex justify-between text-[11px] font-bold text-apoyo"><span
                                                class="truncate">{{ $area }}</span><span>{{ $count }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-fondo-app">
                                            <div class="h-full rounded-full bg-boton-principal"
                                                style="width: {{ min(($count / max($metricas['personal_activo'], 1)) * 100, 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs font-bold text-apoyo">Sin asignaciones por area.</p>
                                @endforelse
                            </div>
                            <div class="space-y-2">
                                @forelse($distribucionTurnos as $turno => $count)
                                    <div>
                                        <div class="mb-1 flex justify-between text-[11px] font-bold text-apoyo"><span
                                                class="truncate">{{ $turno }}</span><span>{{ $count }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-fondo-app">
                                            <div class="h-full rounded-full bg-boton-acento"
                                                style="width: {{ min(($count / max($metricas['personal_activo'], 1)) * 100, 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs font-bold text-apoyo">Sin carga por turno.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        @endif

        @if ($tabActiva === 'horarios')
            <section class="space-y-4">
                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-base font-extrabold text-titulo">Horarios del personal</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">Disponibilidad y jornada semanal del personal
                                administrativo y de salud.</p>
                        </div>
                        @can('turnos.asignar')
                            <button type="button" wire:click="abrirModalHorario"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-estado-exitoBg px-4 text-xs font-bold uppercase text-inverso">
                                <i class="ph-bold ph-plus"></i>
                                Registrar horario
                            </button>
                        @endcan
                    </div>

                    <div class="grid gap-2 md:grid-cols-6">
                        <input type="search" wire:model.live.debounce.300ms="horarioSearch"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus md:col-span-2"
                            placeholder="Buscar personal">
                        <select wire:model.live="horarioTipoPersonal"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Tipo</option>
                            <option value="admin">Administrativo</option>
                            <option value="salud">Salud</option>
                        </select>
                        <select wire:model.live="horarioDia"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Dia</option>
                            @foreach ($diasSemana as $dia)
                                <option value="{{ $dia }}">{{ $diaLabels[$dia] }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="horarioTurno"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Turno</option>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Noche">Noche</option>
                            <option value="Guardia">Guardia</option>
                        </select>
                        <button type="button" wire:click="limpiarFiltrosHorario"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-card/35 px-3 text-xs font-bold text-titulo">Limpiar</button>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-6">
                    @foreach ($diasSemana as $dia)
                        <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3 shadow-sm">
                            <h3 class="mb-2 text-center text-[11px] font-bold uppercase text-apoyo">
                                {{ $diaLabels[$dia] }}</h3>
                            <div class="space-y-2">
                                @forelse($horarios->where('dia', $dia)->take(4) as $horario)
                                    <button type="button"
                                        wire:click="verDetalleHorario('{{ $horario['tipo'] }}', {{ $horario['id'] }})"
                                        class="w-full rounded-xl border border-white/50 bg-fondo-card/45 p-2 text-left transition hover:border-borde-focus">
                                        <span
                                            class="block truncate text-[11px] font-bold text-titulo">{{ $horario['persona'] }}</span>
                                        <span
                                            class="block text-[10px] font-bold text-apoyo">{{ $horario['hora_inicio'] }}
                                            - {{ $horario['hora_fin'] }}</span>
                                    </button>
                                @empty
                                    <p
                                        class="rounded-xl border border-dashed border-borde-suave p-3 text-center text-[10px] font-bold text-apoyo">
                                        Sin horarios</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-[1060px] w-full text-left text-sm">
                            <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-4 py-3">Personal</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Cargo / especialidad</th>
                                    <th class="px-4 py-3">Dia</th>
                                    <th class="px-4 py-3">Horario</th>
                                    <th class="px-4 py-3">Turno</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/45 bg-fondo-card/25">
                                @forelse($horarios as $horario)
                                    <tr class="transition hover:bg-fondo-card/45">
                                        <td class="px-4 py-3 font-black text-titulo">{{ $horario['persona'] }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                                            {{ $horario['tipo_label'] }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $horario['cargo'] }}
                                        </td>
                                        <td class="px-4 py-3 text-xs font-bold text-titulo">
                                            {{ $horario['dia_label'] }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                                            {{ $horario['hora_inicio'] }} - {{ $horario['hora_fin'] }}</td>
                                        <td class="px-4 py-3"><span
                                                class="rounded-full px-2.5 py-1 text-[10px] font-bold text-inverso"
                                                style="background-color: {{ $horario['color'] }}">{{ $horario['turno'] }}</span>
                                        </td>
                                        <td class="px-4 py-3"><span
                                                class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $horario['estado'] === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $horario['estado'] }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-1.5">
                                                <button type="button"
                                                    wire:click="verDetalleHorario('{{ $horario['tipo'] }}', {{ $horario['id'] }})"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso"
                                                    title="Ver detalle"><i class="ph-bold ph-eye"></i></button>
                                                @can('turnos.asignar')
                                                    <button type="button"
                                                        wire:click="cargarHorario('{{ $horario['tipo'] }}', {{ $horario['id'] }})"
                                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-estado-peligroBg text-parrafo transition hover:bg-boton-acento hover:text-inverso"
                                                        title="Editar horario"><i
                                                            class="ph-bold ph-pencil-simple"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-4 py-10 text-center text-sm font-bold text-apoyo">No se
                                            encontraron horarios con los filtros seleccionados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if ($tabActiva === 'turnos')
            <section class="space-y-4">
                <div
                    class="flex flex-col gap-3 rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-titulo">Turnos institucionales</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">Bloques de tiempo con color y estado operativo.
                        </p>
                    </div>
                    @can('turnos.crear')
                        <button type="button" wire:click="abrirModalTurno"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 text-xs font-bold uppercase text-inverso"><i
                                class="ph-bold ph-plus"></i>Nuevo turno</button>
                    @endcan
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @forelse($turnosLista as $turno)
                        <article class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <span
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl text-inverso shadow-sm"
                                    style="background-color: {{ $turno->color ?: '#2F3E5C' }}"><i
                                        class="ph-bold ph-clock-countdown text-xl"></i></span>
                                <span
                                    class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $turno->estado === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $turno->estado }}</span>
                            </div>
                            <h3 class="mt-3 text-base font-extrabold text-titulo">{{ $turno->nombre }}</h3>
                            <p class="mt-1 text-xs font-bold text-apoyo">
                                {{ substr($turno->hora_inicio ?? '--:--', 0, 5) }} -
                                {{ substr($turno->hora_fin ?? '--:--', 0, 5) }}</p>
                            <p class="mt-3 line-clamp-2 min-h-[2.5rem] text-xs font-bold text-apoyo">
                                {{ $turno->descripcion ?: 'Sin descripcion registrada.' }}</p>
                            <div class="mt-4 flex justify-end gap-1.5">
                                @can('turnos.editar')
                                    <button type="button" wire:click="cargarTurno('{{ $turno->cod_turno }}')"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso"><i
                                            class="ph-bold ph-pencil-simple"></i></button>
                                @endcan
                                @can('turnos.cambiar_estado')
                                    <button type="button" wire:click="eliminarTurno('{{ $turno->cod_turno }}')"
                                        wire:confirm="¿Archivar este turno institucional?"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700 transition hover:bg-rose-600 hover:text-inverso"><i
                                            class="ph-bold ph-archive"></i></button>
                                @endcan
                            </div>
                        </article>
                    @empty
                        <div
                            class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-8 text-center text-sm font-bold text-apoyo md:col-span-2 xl:col-span-4">
                            No hay turnos institucionales registrados.</div>
                    @endforelse
                </div>
            </section>
        @endif

        @if ($tabActiva === 'asignaciones')
            <section class="space-y-4">
                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-base font-extrabold text-titulo">Asignaciones de personal</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">Relaciona personal, area, turno, dias de
                                cobertura y vigencia.</p>
                        </div>
                        @can('turnos.asignar')
                            <button type="button" wire:click="abrirModalAsignacion"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-xs font-bold uppercase text-inverso"><i
                                    class="ph-bold ph-user-plus"></i>Registrar asignacion</button>
                        @endcan
                    </div>

                    <div class="grid gap-2 md:grid-cols-7">
                        <input type="search" wire:model.live.debounce.300ms="search"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus md:col-span-2"
                            placeholder="Buscar personal">
                        <select wire:model.live="filtroArea"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Area</option>
                            @foreach ($areasDisponibles as $area)
                                <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filtroTurno"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Turno</option>
                            @foreach ($turnosDisponibles as $turno)
                                <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filtroEstado"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Estado</option>
                            <option value="ACTIVA">Activa</option>
                            <option value="INACTIVA">Inactiva</option>
                            <option value="FINALIZADA">Finalizada</option>
                        </select>
                        <select wire:model.live="filtroDia"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold outline-none focus:border-borde-focus">
                            <option value="">Dia</option>
                            @foreach ($diasSemana as $dia)
                                <option value="{{ $dia }}">{{ $diaLabels[$dia] }}</option>
                            @endforeach
                        </select>
                        <button type="button" wire:click="limpiarFiltros"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-card/35 px-3 text-xs font-bold text-titulo">Limpiar</button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-[1120px] w-full text-left text-sm">
                            <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-4 py-3">Personal</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Area</th>
                                    <th class="px-4 py-3">Turno</th>
                                    <th class="px-4 py-3">Dias</th>
                                    <th class="px-4 py-3">Vigencia</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#C7B5A3]/45 bg-fondo-card/25">
                                @forelse($asignaciones as $asig)
                                    <tr class="transition hover:bg-fondo-card/45">
                                        <td class="px-4 py-3">
                                            <p class="truncate font-black text-titulo">
                                                {{ $asig->usuario?->name ?? 'Sin usuario' }}</p>
                                            <p class="text-[11px] font-bold text-apoyo">
                                                {{ $asig->usuario?->personalSalud ? 'Salud' : ($asig->usuario?->personalAdmin ? 'Administrativo' : 'Institucional') }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                                            {{ $asig->tipo_asignacion }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                                            {{ $asig->area?->nombre ?? 'Sin area' }}</td>
                                        <td class="px-4 py-3"><span
                                                class="rounded-full px-2.5 py-1 text-[10px] font-bold text-inverso"
                                                style="background-color: {{ $asig->turno?->color ?: '#2F3E5C' }}">{{ $asig->turno?->nombre ?? 'Sin turno' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($asig->dias_semana ?: [] as $dia)
                                                    <span
                                                        class="rounded bg-fondo-app px-1.5 py-0.5 text-[9px] font-bold text-apoyo">{{ substr($dia, 0, 3) }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                                            {{ $asig->fecha_inicio?->format('d/m/Y') ?? 'S/F' }} -
                                            {{ $asig->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}</td>
                                        <td class="px-4 py-3"><span
                                                class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $asig->estado === 'ACTIVA' ? 'bg-emerald-100 text-emerald-700' : ($asig->estado === 'FINALIZADA' ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-700') }}">{{ $asig->estado }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-1.5">
                                                <button type="button"
                                                    wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-titulo transition hover:bg-boton-principal hover:text-inverso"
                                                    title="Ver detalle"><i class="ph-bold ph-eye"></i></button>
                                                @can('turnos.asignar')
                                                    @if ($asig->estado === 'ACTIVA')
                                                        <button type="button"
                                                            wire:click="cargarAsignacion('{{ $asig->cod_asignacion }}')"
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-estado-peligroBg text-parrafo transition hover:bg-boton-acento hover:text-inverso"
                                                            title="Editar"><i
                                                                class="ph-bold ph-pencil-simple"></i></button>
                                                    @endif
                                                @endcan
                                                @can('turnos.finalizar')
                                                    @if ($asig->estado === 'ACTIVA')
                                                        <button type="button"
                                                            wire:click="finalizarAsignacion('{{ $asig->cod_asignacion }}')"
                                                            wire:confirm="¿Finalizar esta asignacion?"
                                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700 transition hover:bg-rose-600 hover:text-inverso"
                                                            title="Finalizar"><i
                                                                class="ph-bold ph-check-circle"></i></button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-4 py-10 text-center text-sm font-bold text-apoyo">No se
                                            encontraron asignaciones con los filtros aplicados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if ($tabActiva === 'calendario')
            <section class="grid gap-4 xl:grid-cols-[1fr_320px]">
                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-base font-extrabold text-titulo">Calendario institucional</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">{{ $calendario['titulo'] }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div class="inline-flex rounded-xl bg-fondo-panel p-1">
                                @foreach (['dia' => 'Dia', 'semana' => 'Semana', 'mes' => 'Mes'] as $modo => $label)
                                    <button type="button" wire:click="cambiarModoCalendario('{{ $modo }}')"
                                        class="rounded-lg px-3 py-1.5 text-[11px] font-bold {{ $modoCalendario === $modo ? 'bg-fondo-card text-titulo shadow-sm' : 'text-apoyo' }}">{{ $label }}</button>
                                @endforeach
                            </div>
                            <button type="button" wire:click="calendarioAnterior"
                                class="h-9 rounded-xl border border-borde-suave bg-fondo-card/35 px-3 text-xs font-bold"><i
                                    class="ph-bold ph-caret-left"></i></button>
                            <button type="button" wire:click="calendarioHoy"
                                class="h-9 rounded-xl bg-boton-principal px-3 text-xs font-bold text-inverso">Hoy</button>
                            <button type="button" wire:click="calendarioSiguiente"
                                class="h-9 rounded-xl border border-borde-suave bg-fondo-card/35 px-3 text-xs font-bold"><i
                                    class="ph-bold ph-caret-right"></i></button>
                        </div>
                    </div>

                    <div
                        class="grid gap-2 {{ $modoCalendario === 'dia' ? 'grid-cols-1' : ($modoCalendario === 'mes' ? 'grid-cols-2 md:grid-cols-7' : 'grid-cols-1 md:grid-cols-7') }}">
                        @foreach ($calendario['dias'] as $dia)
                            <div class="rounded-2xl border {{ $dia['es_hoy'] ? 'border-borde-focus bg-estado-peligroBg' : 'border-borde-suave bg-fondo-card/30' }} p-2"
                                style="min-height: {{ $modoCalendario === 'mes' ? '128px' : '260px' }};">
                                <div class="mb-2 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-apoyo">{{ $dia['dia_label'] }}
                                        </p>
                                        <p
                                            class="text-sm font-bold {{ $dia['es_mes_actual'] ? 'text-titulo' : 'text-apoyo' }}">
                                            {{ $dia['numero'] }} {{ $modoCalendario === 'mes' ? '' : $dia['mes'] }}
                                        </p>
                                    </div>
                                    @if ($dia['total_eventos'] > 0)
                                        <span
                                            class="rounded-full bg-fondo-panel px-2 py-0.5 text-[10px] font-bold text-titulo">{{ $dia['total_eventos'] }}</span>
                                    @endif
                                </div>

                                <div
                                    class="space-y-1.5 overflow-y-auto {{ $modoCalendario === 'mes' ? 'max-h-[84px]' : 'max-h-[210px]' }}">
                                    @forelse($dia['eventos'] as $evento)
                                        @if ($evento['tipo'] === 'asignacion')
                                            <button type="button"
                                                wire:click="verFichaAsignacion('{{ $evento['id'] }}')"
                                                class="w-full rounded-xl border border-white/50 bg-fondo-card/55 p-2 text-left shadow-sm transition hover:border-borde-focus">
                                                <span
                                                    class="block truncate text-[11px] font-bold text-titulo">{{ $evento['titulo'] }}</span>
                                                <span
                                                    class="block truncate text-[10px] font-bold text-apoyo">{{ $evento['hora'] }}</span>
                                                <span class="mt-1 block h-1 rounded-full"
                                                    style="background-color: {{ $evento['color'] }}"></span>
                                            </button>
                                        @else
                                            <button type="button"
                                                wire:click="verDetalleHorario('{{ $evento['tipo_horario'] }}', {{ $evento['id'] }})"
                                                class="w-full rounded-xl border border-sky-100 bg-sky-50/75 p-2 text-left transition hover:border-sky-300">
                                                <span
                                                    class="block truncate text-[11px] font-bold text-titulo">{{ $evento['titulo'] }}</span>
                                                <span
                                                    class="block truncate text-[10px] font-bold text-apoyo">{{ $evento['hora'] }}</span>
                                            </button>
                                        @endif
                                    @empty
                                        <p
                                            class="rounded-xl border border-dashed border-borde-suave p-3 text-center text-[10px] font-bold text-apoyo">
                                            Sin eventos</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                    <h3 class="text-base font-extrabold text-titulo">Detalle del evento</h3>
                    @if ($detalleEvento)
                        <div class="mt-3 rounded-2xl border border-borde-suave bg-fondo-card/35 p-4">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold text-inverso"
                                style="background-color: {{ $detalleEvento['color'] }}">{{ $detalleEvento['tipo'] }}</span>
                            <h4 class="mt-3 text-sm font-bold text-titulo">{{ $detalleEvento['titulo'] }}</h4>
                            <p class="mt-1 text-xs font-bold text-apoyo">{{ $detalleEvento['subtitulo'] }}</p>
                            <div class="mt-3 space-y-2 text-xs font-bold text-apoyo">
                                <p><span class="font-black text-titulo">Fecha:</span> {{ $detalleEvento['fecha'] }}
                                </p>
                                <p><span class="font-black text-titulo">Horario:</span>
                                    {{ $detalleEvento['horario'] }}</p>
                                <p><span class="font-black text-titulo">Estado:</span> {{ $detalleEvento['estado'] }}
                                </p>
                                <p class="rounded-xl bg-fondo-panel p-3">{{ $detalleEvento['descripcion'] }}</p>
                            </div>
                            @can('turnos.asignar')
                                <button type="button"
                                    wire:click="cargarHorario('{{ $detalleEvento['tipo_horario'] }}', {{ $detalleEvento['id'] }})"
                                    class="mt-4 w-full rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold uppercase text-inverso">Editar
                                    horario</button>
                            @endcan
                        </div>
                    @else
                        <p
                            class="mt-3 rounded-2xl border border-dashed border-borde-suave p-5 text-center text-xs font-bold text-apoyo">
                            Seleccione un horario del calendario para ver su detalle. Las asignaciones abren su ficha
                            emergente.</p>
                    @endif
                    <div class="mt-4 space-y-2 text-[11px] font-bold text-apoyo">
                        <p><span class="inline-block h-2 w-2 rounded-full bg-sky-400"></span> Horario regular</p>
                        <p><span class="inline-block h-2 w-2 rounded-full bg-estado-exitoBg"></span> Asignacion activa
                        </p>
                        <p><span class="inline-block h-2 w-2 rounded-full bg-boton-acento"></span> Turno especial /
                            apoyo</p>
                    </div>
                </aside>
            </section>
        @endif

        @if ($tabActiva === 'alertas')
            <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                <h2 class="text-base font-extrabold text-titulo">Conflictos y alertas</h2>
                <p class="mt-1 text-xs font-bold text-apoyo">Incidencias calculadas desde horarios, turnos y
                    asignaciones vigentes.</p>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-borde-suave">
                    <table class="min-w-[980px] w-full text-left text-sm">
                        <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                            <tr>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Persona / area</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Horario</th>
                                <th class="px-4 py-3">Descripcion</th>
                                <th class="px-4 py-3">Prioridad</th>
                                <th class="px-4 py-3">Accion sugerida</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C7B5A3]/45 bg-fondo-card/25">
                            @forelse($alertas as $alerta)
                                <tr class="transition hover:bg-fondo-card/45">
                                    <td class="px-4 py-3 font-black text-titulo">{{ $alerta['tipo'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $alerta['persona'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $alerta['fecha'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $alerta['horario'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $alerta['descripcion'] }}
                                    </td>
                                    <td class="px-4 py-3"><span
                                            class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $alerta['prioridad'] === 'Alta' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">{{ $alerta['prioridad'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs font-bold text-titulo">{{ $alerta['accion'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-sm font-bold text-apoyo">No
                                        existen alertas operativas pendientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($tabActiva === 'reportes')
            <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                <h2 class="text-base font-extrabold text-titulo">Reportes administrativos</h2>
                <p class="mt-1 text-xs font-bold text-apoyo">Evidencia institucional disponible para horarios,
                    cobertura y asignaciones.</p>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @can('turnos.reportes')
                        <button type="button" wire:click="exportarReporteGeneralPdf"
                            class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4 text-left transition hover:border-borde-focus">
                            <i class="ph-bold ph-file-pdf text-2xl text-rose-600"></i>
                            <span class="mt-3 block text-sm font-bold text-titulo">Reporte mensual de asignaciones</span>
                            <span class="mt-1 block text-xs font-bold text-apoyo">PDF general operativo</span>
                        </button>
                        <button type="button" wire:click="exportarReporteGeneralExcel"
                            class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4 text-left transition hover:border-estado-exitoBorde">
                            <i class="ph-bold ph-file-xls text-2xl text-emerald-700"></i>
                            <span class="mt-3 block text-sm font-bold text-titulo">Base de asignaciones</span>
                            <span class="mt-1 block text-xs font-bold text-apoyo">Exportacion Excel</span>
                        </button>
                        <button type="button" wire:click="exportarCoberturaSemanalPdf"
                            class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4 text-left transition hover:border-borde-fuerte">
                            <i class="ph-bold ph-calendar-check text-2xl text-titulo"></i>
                            <span class="mt-3 block text-sm font-bold text-titulo">Cobertura semanal</span>
                            <span class="mt-1 block text-xs font-bold text-apoyo">PDF de turnos</span>
                        </button>
                        <button type="button" wire:click="exportarPersonalSinTurnoExcel"
                            class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4 text-left transition hover:border-amber-300">
                            <i class="ph-bold ph-user-warning text-2xl text-amber-700"></i>
                            <span class="mt-3 block text-sm font-bold text-titulo">Personal sin horario/turno</span>
                            <span class="mt-1 block text-xs font-bold text-apoyo">Excel de seguimiento</span>
                        </button>
                    @else
                        <p
                            class="rounded-2xl border border-dashed border-borde-suave p-8 text-center text-sm font-bold text-apoyo md:col-span-2 xl:col-span-4">
                            No tiene permiso para generar reportes.</p>
                    @endcan
                    <article class="rounded-2xl border border-dashed border-borde-suave bg-fondo-card/20 p-4">
                        <i class="ph-bold ph-hourglass-medium text-2xl text-apoyo"></i>
                        <span class="mt-3 block text-sm font-bold text-titulo">Reporte individual de personal</span>
                        <span class="mt-1 block text-xs font-bold text-apoyo">Preparado para siguiente fase</span>
                    </article>
                    <article class="rounded-2xl border border-dashed border-borde-suave bg-fondo-card/20 p-4">
                        <i class="ph-bold ph-hourglass-medium text-2xl text-apoyo"></i>
                        <span class="mt-3 block text-sm font-bold text-titulo">Reporte de conflictos</span>
                        <span class="mt-1 block text-xs font-bold text-apoyo">Estructura visible sin boton roto</span>
                    </article>
                </div>
            </section>
        @endif
    </div>

    @if ($mostrarFormularioHorario)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-fondo-panel p-4 backdrop-blur-sm">
            <div
                class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-borde-suave p-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">
                            {{ $isEdit ? 'Editar horario' : 'Registrar horario' }}</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">Defina dias, turno y rango horario del personal.
                        </p>
                    </div>
                    <button type="button" wire:click="$set('mostrarFormularioHorario', false)"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-acento hover:text-inverso"><i
                            class="ph-bold ph-x"></i></button>
                </div>
                <form wire:submit.prevent="guardarHorario" class="overflow-y-auto p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Tipo de
                                personal</label>
                            <select wire:model.live="horario_tipo_personal"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"
                                @disabled($isEdit)>
                                <option value="admin">Administrativo</option>
                                <option value="salud">Salud</option>
                            </select>
                            @error('horario_tipo_personal')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Personal</label>
                            <select wire:model="horario_personal_id"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"
                                @disabled($isEdit)>
                                <option value="">Seleccione personal</option>
                                @foreach ($personalHorarioOpciones as $persona)
                                    <option value="{{ $persona['id'] }}">{{ $persona['nombre'] }} ·
                                        {{ $persona['cargo'] }}</option>
                                @endforeach
                            </select>
                            @error('horario_personal_id')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-[10px] font-bold uppercase text-apoyo">Dias de la
                                semana</label>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($diasSemana as $dia)
                                    <label
                                        class="flex items-center justify-between rounded-xl border border-borde-suave bg-fondo-card/35 px-4 py-3 text-sm font-bold text-titulo">
                                        {{ $diaLabels[$dia] }}
                                        <input wire:model="horario_dias_semana" type="checkbox"
                                            value="{{ $dia }}"
                                            class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-[#E27D60]"
                                            @disabled($isEdit && !in_array($dia, $horario_dias_semana ?? []))>
                                    </label>
                                @endforeach
                            </div>
                            @error('horario_dias_semana')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Hora inicio</label>
                            <input wire:model="horario_hora_inicio" type="time"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                            @error('horario_hora_inicio')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Hora fin</label>
                            <input wire:model="horario_hora_fin" type="time"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                            @error('horario_hora_fin')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Turno</label>
                            <select wire:model="horario_turno"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                                <option value="Noche">Noche</option>
                                <option value="Guardia">Guardia</option>
                                <option value="Evento especial">Evento especial</option>
                                <option value="Apoyo">Apoyo</option>
                            </select>
                            @error('horario_turno')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Estado</label>
                            <select wire:model="horario_estado"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                            @error('horario_estado')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Observacion</label>
                            <textarea wire:model="horario_observaciones" rows="3"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"></textarea>
                            @error('horario_observaciones')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div
                        class="mt-5 flex flex-col-reverse gap-2 border-t border-borde-suave pt-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('mostrarFormularioHorario', false)"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-card/35 px-4 text-xs font-bold text-titulo">Cancelar</button>
                        <button type="submit"
                            class="h-10 rounded-xl bg-estado-exitoBg px-5 text-xs font-bold uppercase text-inverso">Guardar
                            horario</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($mostrarFormularioTurno)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-fondo-panel p-4 backdrop-blur-sm">
            <div
                class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-borde-suave p-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">
                            {{ $isEdit ? 'Editar turno institucional' : 'Registrar turno institucional' }}</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">Identificacion, horario, color y estado del turno.
                        </p>
                    </div>
                    <button type="button" wire:click="$set('mostrarFormularioTurno', false)"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-acento hover:text-inverso"><i
                            class="ph-bold ph-x"></i></button>
                </div>
                <form wire:submit.prevent="guardarTurno" class="overflow-y-auto p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2"><label
                                class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Nombre del
                                turno</label><input wire:model="turno_nombre" type="text"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"
                                placeholder="Ej. Turno mañana">
                            @error('turno_nombre')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Hora
                                inicio</label><input wire:model="turno_hora_inicio" type="time"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                            @error('turno_hora_inicio')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Hora
                                fin</label><input wire:model="turno_hora_fin" type="time"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                            @error('turno_hora_fin')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Color</label><input
                                wire:model="turno_color" type="color"
                                class="h-12 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2">
                            @error('turno_color')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Estado</label><select
                                wire:model="turno_estado"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                            @error('turno_estado')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2"><label
                                class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Descripcion</label>
                            <textarea wire:model="turno_descripcion" rows="2"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"></textarea>
                            @error('turno_descripcion')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2"><label
                                class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Observacion</label>
                            <textarea wire:model="turno_observaciones" rows="2"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"></textarea>
                            @error('turno_observaciones')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div
                        class="mt-5 flex flex-col-reverse gap-2 border-t border-borde-suave pt-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('mostrarFormularioTurno', false)"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-card/35 px-4 text-xs font-bold text-titulo">Cancelar</button><button
                            type="submit"
                            class="h-10 rounded-xl bg-boton-principal px-5 text-xs font-bold uppercase text-inverso">Guardar
                            turno</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($mostrarFormularioAsignacion)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-fondo-panel p-4 backdrop-blur-sm">
            <div
                class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-borde-suave p-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">
                            {{ $isEdit ? 'Editar asignacion' : 'Registrar asignacion' }}</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">Se validara compatibilidad con horarios y
                            conflictos activos.</p>
                    </div>
                    <button type="button" wire:click="$set('mostrarFormularioAsignacion', false)"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-acento hover:text-inverso"><i
                            class="ph-bold ph-x"></i></button>
                </div>
                <form wire:submit.prevent="guardarAsignacion" class="overflow-y-auto p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Personal
                                asignado</label><select wire:model="asig_cod_usu"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="">Seleccione personal</option>
                                @foreach ($usuariosDisponibles as $usuario)
                                    <option value="{{ $usuario->cod_usu }}">{{ $usuario->name }} ·
                                        {{ $usuario->areaInstitucional?->nombre ?? 'Sin area' }}</option>
                                @endforeach
                            </select>
                            @error('asig_cod_usu')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Area
                                institucional</label><select wire:model="asig_cod_area"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="">Seleccione area</option>
                                @foreach ($areasDisponibles as $area)
                                    <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                            @error('asig_cod_area')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Turno</label><select
                                wire:model="asig_cod_turno"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="">Seleccione turno</option>
                                @foreach ($turnosDisponibles as $turno)
                                    <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} ·
                                        {{ substr($turno->hora_inicio ?? '--:--', 0, 5) }}-{{ substr($turno->hora_fin ?? '--:--', 0, 5) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('asig_cod_turno')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Tipo de
                                asignacion</label><select wire:model="asig_tipo_asignacion"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="REGULAR">Regular</option>
                                <option value="APOYO">Apoyo temporal</option>
                                <option value="COBERTURA">Cobertura especial</option>
                                <option value="VOLUNTARIADO">Voluntariado</option>
                            </select>
                            @error('asig_tipo_asignacion')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Fecha
                                inicio</label><input wire:model="asig_fecha_inicio" type="date"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                            @error('asig_fecha_inicio')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Fecha
                                fin</label><input wire:model="asig_fecha_fin" type="date"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                            @error('asig_fecha_fin')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2"><label
                                class="mb-2 block text-[10px] font-bold uppercase text-apoyo">Dias asignados</label>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($diasSemana as $dia)
                                    <label
                                        class="flex items-center justify-between rounded-xl border border-borde-suave bg-fondo-card/35 px-4 py-3 text-sm font-bold text-titulo">{{ $diaLabels[$dia] }}<input
                                            wire:model="asig_dias_semana" type="checkbox"
                                            value="{{ $dia }}"
                                            class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-[#E27D60]"></label>
                                @endforeach
                            </div>
                            @error('asig_dias_semana')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div><label class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Estado</label><select
                                wire:model="asig_estado"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus">
                                <option value="ACTIVA">Activa</option>
                                <option value="INACTIVA">Inactiva</option>
                                <option value="FINALIZADA">Finalizada</option>
                            </select>
                            @error('asig_estado')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <label
                            class="flex items-center justify-between rounded-xl border border-borde-suave bg-fondo-card/35 px-4 py-3 text-sm font-bold text-titulo">Apoyo
                            temporal en otra area<input wire:model="asig_apoyo_temporal" type="checkbox"
                                class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-[#E27D60]"></label>
                        <div class="md:col-span-2"><label
                                class="mb-1 block text-[10px] font-bold uppercase text-apoyo">Observacion</label>
                            <textarea wire:model="asig_observaciones" rows="3"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-sm font-bold outline-none focus:border-borde-focus"></textarea>
                            @error('asig_observaciones')
                                <span class="mt-1 block text-xs font-bold text-rose-600">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div
                        class="mt-5 flex flex-col-reverse gap-2 border-t border-borde-suave pt-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('mostrarFormularioAsignacion', false)"
                            class="h-10 rounded-xl border border-borde-suave bg-fondo-card/35 px-4 text-xs font-bold text-titulo">Cancelar</button><button
                            type="submit"
                            class="h-10 rounded-xl bg-boton-acento px-5 text-xs font-bold uppercase text-inverso">Guardar
                            asignacion</button></div>
                </form>
            </div>
        </div>
    @endif

    @if ($mostrarFichaAsignacion && $asignacionSeleccionada)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-fondo-panel p-4 backdrop-blur-sm">
            <div
                class="w-full max-w-2xl overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-app shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-borde-suave p-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-titulo">Detalle de asignacion</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">Consulta operativa sin salir del modulo.</p>
                    </div>
                    <button type="button" wire:click="$set('mostrarFichaAsignacion', false)"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-acento hover:text-inverso"><i
                            class="ph-bold ph-x"></i></button>
                </div>
                <div class="p-5">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-fondo-card/35 p-3">
                            <p class="text-[10px] font-bold uppercase text-apoyo">Personal</p>
                            <p class="mt-1 text-sm font-bold text-titulo">
                                {{ $asignacionSeleccionada->usuario?->name ?? 'Sin usuario' }}</p>
                        </div>
                        <div class="rounded-2xl bg-fondo-card/35 p-3">
                            <p class="text-[10px] font-bold uppercase text-apoyo">Area</p>
                            <p class="mt-1 text-sm font-bold text-titulo">
                                {{ $asignacionSeleccionada->area?->nombre ?? 'Sin area' }}</p>
                        </div>
                        <div class="rounded-2xl bg-fondo-card/35 p-3">
                            <p class="text-[10px] font-bold uppercase text-apoyo">Turno</p>
                            <p class="mt-1 text-sm font-bold text-titulo">
                                {{ $asignacionSeleccionada->turno?->nombre ?? 'Sin turno' }}</p>
                            <p class="text-xs font-bold text-apoyo">
                                {{ substr($asignacionSeleccionada->turno?->hora_inicio ?? '--:--', 0, 5) }} -
                                {{ substr($asignacionSeleccionada->turno?->hora_fin ?? '--:--', 0, 5) }}</p>
                        </div>
                        <div class="rounded-2xl bg-fondo-card/35 p-3">
                            <p class="text-[10px] font-bold uppercase text-apoyo">Estado</p>
                            <p class="mt-1 text-sm font-bold text-titulo">{{ $asignacionSeleccionada->estado }}</p>
                        </div>
                        <div class="rounded-2xl bg-fondo-card/35 p-3 sm:col-span-2">
                            <p class="text-[10px] font-bold uppercase text-apoyo">Dias y vigencia</p>
                            <p class="mt-1 text-sm font-bold text-titulo">
                                {{ implode(', ', $asignacionSeleccionada->dias_semana ?: []) }}</p>
                            <p class="text-xs font-bold text-apoyo">
                                {{ $asignacionSeleccionada->fecha_inicio?->format('d/m/Y') ?? 'S/F' }} -
                                {{ $asignacionSeleccionada->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}</p>
                        </div>
                        <div class="rounded-2xl bg-fondo-card/35 p-3 sm:col-span-2">
                            <p class="text-[10px] font-bold uppercase text-apoyo">Observacion</p>
                            <p class="mt-1 text-sm font-bold text-apoyo">
                                {{ $asignacionSeleccionada->observaciones ?: 'Sin observaciones.' }}</p>
                        </div>
                    </div>
                    <div
                        class="mt-5 flex flex-col-reverse gap-2 border-t border-borde-suave pt-4 sm:flex-row sm:justify-end">
                        @can('turnos.asignar')
                            @if ($asignacionSeleccionada->estado === 'ACTIVA')
                                <button type="button"
                                    wire:click="cargarAsignacion('{{ $asignacionSeleccionada->cod_asignacion }}')"
                                    class="h-10 rounded-xl border border-borde-suave bg-fondo-card/35 px-4 text-xs font-bold text-titulo">Editar</button>
                            @endif
                        @endcan
                        <button type="button" wire:click="$set('mostrarFichaAsignacion', false)"
                            class="h-10 rounded-xl bg-boton-principal px-4 text-xs font-bold uppercase text-inverso">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
