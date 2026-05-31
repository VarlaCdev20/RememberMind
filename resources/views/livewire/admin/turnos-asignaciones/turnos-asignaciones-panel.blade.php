<div class="min-h-[calc(100vh-8rem)] overflow-x-hidden bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1480px] space-y-5">
        <section class="overflow-hidden rounded-2xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/75 shadow-[0_16px_44px_rgba(47,62,92,0.11)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-4xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/25 bg-[#E27D60]/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-[#E27D60]">
                        <i class="ph-bold ph-calendar-check text-sm"></i>
                        Administracion
                    </span>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-[#2F3E5C] sm:text-3xl">Horarios y asignaciones</h1>
                    <p class="mt-1 max-w-3xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                        Planificacion administrativa de turnos, horarios, asignaciones y cobertura institucional.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('turnos.asignar')
                        <button type="button" wire:click="abrirModalHorario" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#8DA280]/35 bg-[#8DA280]/15 px-4 text-[11px] font-black uppercase tracking-wider text-[#5F7E55] transition hover:-translate-y-0.5 hover:bg-[#8DA280]/22">
                            <i class="ph-bold ph-clock-afternoon text-sm"></i>
                            Registrar horario
                        </button>
                        <button type="button" wire:click="abrirModalAsignacion" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-4 text-[11px] font-black uppercase tracking-wider text-white shadow-[0_8px_18px_rgba(226,125,96,0.22)] transition hover:-translate-y-0.5 hover:bg-[#D96F58]">
                            <i class="ph-bold ph-user-plus text-sm"></i>
                            Registrar asignacion
                        </button>
                    @endcan

                    @can('turnos.crear')
                        <button type="button" wire:click="abrirModalTurno" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#2F3E5C]/15 bg-[#F3ECE4]/90 px-4 text-[11px] font-black uppercase tracking-wider text-[#2F3E5C] transition hover:-translate-y-0.5 hover:border-[#2F3E5C]/35">
                            <i class="ph-bold ph-plus-circle text-sm"></i>
                            Nuevo turno
                        </button>
                    @endcan
                </div>
            </div>
        </section>

        <nav class="overflow-x-auto rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-2 shadow-sm">
            <div class="flex min-w-max gap-1">
                @foreach([
                    'resumen' => ['Resumen', 'ph-chart-pie-slice'],
                    'horarios' => ['Horarios', 'ph-clock'],
                    'turnos' => ['Turnos', 'ph-clock-countdown'],
                    'asignaciones' => ['Asignaciones', 'ph-users-three'],
                    'calendario' => ['Calendario', 'ph-calendar-dots'],
                    'alertas' => ['Alertas', 'ph-warning-diamond'],
                    'reportes' => ['Reportes', 'ph-file-text'],
                ] as $tab => [$label, $icon])
                    <button
                        type="button"
                        wire:click="cambiarTab('{{ $tab }}')"
                        class="inline-flex h-10 items-center gap-2 rounded-xl px-3 text-xs font-black transition {{ $tabActiva === $tab ? 'bg-[#2F3E5C] text-white shadow-sm' : 'text-[#2F3E5C]/62 hover:bg-white/45 hover:text-[#2F3E5C]' }}"
                    >
                        <i class="ph-bold {{ $icon }}"></i>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </nav>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['icon' => 'ph-identification-card', 'label' => 'Personal activo', 'value' => $metricas['personal_activo'], 'note' => 'Usuarios institucionales activos', 'class' => 'bg-[#2F3E5C]/8 text-[#2F3E5C] border-[#2F3E5C]/15'],
                ['icon' => 'ph-clock', 'label' => 'Horarios registrados', 'value' => $metricas['horarios_registrados'], 'note' => $metricas['horarios_activos'] . ' activos', 'class' => 'bg-sky-100/75 text-sky-700 border-sky-200'],
                ['icon' => 'ph-timer', 'label' => 'Turnos activos', 'value' => $metricas['turnos_activos'], 'note' => 'Bloques institucionales', 'class' => 'bg-indigo-100/75 text-indigo-700 border-indigo-200'],
                ['icon' => 'ph-calendar-check', 'label' => 'Asignaciones de hoy', 'value' => $metricas['asignaciones_hoy'], 'note' => 'Cobertura del dia', 'class' => 'bg-[#8DA280]/15 text-[#5F7E55] border-[#8DA280]/30'],
                ['icon' => 'ph-calendar-x', 'label' => 'Turnos sin cubrir', 'value' => $metricas['turnos_sin_cubrir'], 'note' => 'Areas sin asignacion activa', 'class' => 'bg-amber-100/75 text-amber-700 border-amber-200'],
                ['icon' => 'ph-warning-diamond', 'label' => 'Conflictos detectados', 'value' => $metricas['conflictos_detectados'], 'note' => 'Alertas operativas', 'class' => 'bg-rose-100/75 text-rose-700 border-rose-200'],
                ['icon' => 'ph-gauge', 'label' => 'Personal con sobrecarga', 'value' => $metricas['personal_sobrecarga'], 'note' => '4 o mas asignaciones activas', 'class' => 'bg-violet-100/75 text-violet-700 border-violet-200'],
                ['icon' => 'ph-arrow-fat-lines-right', 'label' => 'Proximas asignaciones', 'value' => $metricas['proximas_asignaciones'], 'note' => 'Vigentes o por iniciar', 'class' => 'bg-[#E27D60]/12 text-[#C75F46] border-[#E27D60]/25'],
            ] as $card)
                <article class="rounded-2xl border {{ $card['class'] }} p-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/55">
                            <i class="ph-bold {{ $card['icon'] }} text-lg"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xl font-black leading-none">{{ $card['value'] }}</p>
                            <p class="mt-1 truncate text-[11px] font-black text-[#2F3E5C]">{{ $card['label'] }}</p>
                        </div>
                    </div>
                    <p class="mt-2 truncate text-[11px] font-bold text-[#2F3E5C]/55">{{ $card['note'] }}</p>
                </article>
            @endforeach
        </section>

        @if($tabActiva === 'resumen')
            <section class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Resumen operativo</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Lectura rapida de cobertura, turnos y carga semanal.</p>
                        </div>
                        <button type="button" wire:click="cambiarTab('calendario')" class="rounded-xl bg-[#2F3E5C] px-3 py-2 text-[11px] font-black uppercase text-white">Ver calendario</button>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-2">
                        <div class="rounded-2xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#2F3E5C]/55">Asignaciones de hoy</h3>
                            <div class="mt-3 space-y-2">
                                @forelse($asignacionesHoyLista as $asig)
                                    <button type="button" wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')" class="w-full rounded-xl border border-[#C7B5A3]/45 bg-[#F8F3ED]/70 p-3 text-left transition hover:border-[#E27D60]/45">
                                        <span class="block truncate text-sm font-black text-[#2F3E5C]">{{ $asig->usuario?->name ?? 'Sin usuario' }}</span>
                                        <span class="block truncate text-xs font-bold text-[#2F3E5C]/58">{{ $asig->area?->nombre ?? 'Sin area' }} · {{ $asig->turno?->nombre ?? 'Sin turno' }}</span>
                                    </button>
                                @empty
                                    <p class="rounded-xl border border-dashed border-[#C7B5A3]/70 p-4 text-center text-xs font-bold text-[#2F3E5C]/55">No existen asignaciones programadas para hoy.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                            <h3 class="text-xs font-black uppercase tracking-wider text-[#2F3E5C]/55">Proximos turnos</h3>
                            <div class="mt-3 space-y-2">
                                @forelse($proximasAsignaciones as $asig)
                                    <button type="button" wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')" class="w-full rounded-xl bg-[#F8F3ED]/70 p-3 text-left transition hover:bg-white/60">
                                        <span class="flex items-center justify-between gap-2">
                                            <span class="truncate text-sm font-black text-[#2F3E5C]">{{ $asig->usuario?->name ?? 'Sin usuario' }}</span>
                                            <span class="shrink-0 rounded-full bg-[#E6DDD3] px-2 py-0.5 text-[9px] font-black text-[#2F3E5C]/60">{{ $asig->fecha_inicio?->format('d/m') ?? 'S/F' }}</span>
                                        </span>
                                        <span class="mt-1 block truncate text-xs font-bold text-[#2F3E5C]/58">{{ $asig->turno?->nombre ?? 'Sin turno' }} · {{ implode(', ', $asig->dias_semana ?: []) }}</span>
                                    </button>
                                @empty
                                    <p class="rounded-xl border border-dashed border-[#C7B5A3]/70 p-4 text-center text-xs font-bold text-[#2F3E5C]/55">No hay proximas asignaciones registradas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                        <h3 class="text-xs font-black uppercase tracking-wider text-[#2F3E5C]/55">Resumen semanal</h3>
                        <div class="mt-3 grid gap-2 md:grid-cols-7">
                            @foreach($resumenSemanal as $dia => $info)
                                <div class="rounded-xl bg-[#F8F3ED]/70 p-3 text-center">
                                    <p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">{{ substr($info['label'], 0, 3) }}</p>
                                    <p class="mt-1 text-lg font-black text-[#2F3E5C]">{{ $info['asignaciones'] }}</p>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/50">{{ $info['horarios'] }} horarios</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                        <h2 class="text-base font-black text-[#2F3E5C]">Alertas operativas</h2>
                        <div class="mt-3 space-y-2">
                            @forelse($alertas->take(6) as $alerta)
                                <div class="rounded-xl border {{ $alerta['prioridad'] === 'Alta' ? 'border-rose-200 bg-rose-50/70' : 'border-amber-200 bg-amber-50/70' }} p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-black text-[#2F3E5C]">{{ $alerta['tipo'] }} · {{ $alerta['persona'] }}</p>
                                            <p class="mt-1 text-[11px] font-bold text-[#2F3E5C]/60">{{ $alerta['descripcion'] }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-white/65 px-2 py-0.5 text-[9px] font-black text-[#2F3E5C]/60">{{ $alerta['prioridad'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="rounded-xl border border-dashed border-[#C7B5A3]/70 p-4 text-center text-xs font-bold text-[#2F3E5C]/55">No hay alertas operativas pendientes.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                        <h2 class="text-base font-black text-[#2F3E5C]">Carga por areas y turnos</h2>
                        <div class="mt-3 grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                @forelse($distribucionAreas as $area => $count)
                                    <div>
                                        <div class="mb-1 flex justify-between text-[11px] font-bold text-[#2F3E5C]/65"><span class="truncate">{{ $area }}</span><span>{{ $count }}</span></div>
                                        <div class="h-2 overflow-hidden rounded-full bg-[#E6DDD3]"><div class="h-full rounded-full bg-[#2F3E5C]" style="width: {{ min(($count / max($metricas['personal_activo'], 1)) * 100, 100) }}%"></div></div>
                                    </div>
                                @empty
                                    <p class="text-xs font-bold text-[#2F3E5C]/50">Sin asignaciones por area.</p>
                                @endforelse
                            </div>
                            <div class="space-y-2">
                                @forelse($distribucionTurnos as $turno => $count)
                                    <div>
                                        <div class="mb-1 flex justify-between text-[11px] font-bold text-[#2F3E5C]/65"><span class="truncate">{{ $turno }}</span><span>{{ $count }}</span></div>
                                        <div class="h-2 overflow-hidden rounded-full bg-[#E6DDD3]"><div class="h-full rounded-full bg-[#E27D60]" style="width: {{ min(($count / max($metricas['personal_activo'], 1)) * 100, 100) }}%"></div></div>
                                    </div>
                                @empty
                                    <p class="text-xs font-bold text-[#2F3E5C]/50">Sin carga por turno.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        @endif

        @if($tabActiva === 'horarios')
            <section class="space-y-4">
                <div class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Horarios del personal</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Disponibilidad y jornada semanal del personal administrativo y de salud.</p>
                        </div>
                        @can('turnos.asignar')
                            <button type="button" wire:click="abrirModalHorario" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#8DA280] px-4 text-xs font-black uppercase text-white">
                                <i class="ph-bold ph-plus"></i>
                                Registrar horario
                            </button>
                        @endcan
                    </div>

                    <div class="grid gap-2 md:grid-cols-6">
                        <input type="search" wire:model.live.debounce.300ms="horarioSearch" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60] md:col-span-2" placeholder="Buscar personal">
                        <select wire:model.live="horarioTipoPersonal" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]">
                            <option value="">Tipo</option>
                            <option value="admin">Administrativo</option>
                            <option value="salud">Salud</option>
                        </select>
                        <select wire:model.live="horarioDia" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]">
                            <option value="">Dia</option>
                            @foreach($diasSemana as $dia)
                                <option value="{{ $dia }}">{{ $diaLabels[$dia] }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="horarioTurno" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]">
                            <option value="">Turno</option>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Noche">Noche</option>
                            <option value="Guardia">Guardia</option>
                        </select>
                        <button type="button" wire:click="limpiarFiltrosHorario" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-3 text-xs font-black text-[#2F3E5C]">Limpiar</button>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-6">
                    @foreach($diasSemana as $dia)
                        <div class="rounded-2xl border border-[#C7B5A3]/60 bg-[#F3ECE4]/80 p-3 shadow-sm">
                            <h3 class="mb-2 text-center text-[11px] font-black uppercase text-[#2F3E5C]/55">{{ $diaLabels[$dia] }}</h3>
                            <div class="space-y-2">
                                @forelse($horarios->where('dia', $dia)->take(4) as $horario)
                                    <button type="button" wire:click="verDetalleHorario('{{ $horario['tipo'] }}', {{ $horario['id'] }})" class="w-full rounded-xl border border-white/50 bg-white/45 p-2 text-left transition hover:border-[#E27D60]/45">
                                        <span class="block truncate text-[11px] font-black text-[#2F3E5C]">{{ $horario['persona'] }}</span>
                                        <span class="block text-[10px] font-bold text-[#2F3E5C]/55">{{ $horario['hora_inicio'] }} - {{ $horario['hora_fin'] }}</span>
                                    </button>
                                @empty
                                    <p class="rounded-xl border border-dashed border-[#C7B5A3]/70 p-3 text-center text-[10px] font-bold text-[#2F3E5C]/45">Sin horarios</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-[1060px] w-full text-left text-sm">
                            <thead class="bg-[#E6DDD3]/80 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
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
                            <tbody class="divide-y divide-[#C7B5A3]/45 bg-white/25">
                                @forelse($horarios as $horario)
                                    <tr class="transition hover:bg-white/45">
                                        <td class="px-4 py-3 font-black text-[#2F3E5C]">{{ $horario['persona'] }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/65">{{ $horario['tipo_label'] }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/65">{{ $horario['cargo'] }}</td>
                                        <td class="px-4 py-3 text-xs font-black text-[#2F3E5C]">{{ $horario['dia_label'] }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/65">{{ $horario['hora_inicio'] }} - {{ $horario['hora_fin'] }}</td>
                                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black text-white" style="background-color: {{ $horario['color'] }}">{{ $horario['turno'] }}</span></td>
                                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $horario['estado'] === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $horario['estado'] }}</span></td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-1.5">
                                                <button type="button" wire:click="verDetalleHorario('{{ $horario['tipo'] }}', {{ $horario['id'] }})" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white" title="Ver detalle"><i class="ph-bold ph-eye"></i></button>
                                                @can('turnos.asignar')
                                                    <button type="button" wire:click="cargarHorario('{{ $horario['tipo'] }}', {{ $horario['id'] }})" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E27D60]/12 text-[#C75F46] transition hover:bg-[#E27D60] hover:text-white" title="Editar horario"><i class="ph-bold ph-pencil-simple"></i></button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-4 py-10 text-center text-sm font-black text-[#2F3E5C]/55">No se encontraron horarios con los filtros seleccionados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if($tabActiva === 'turnos')
            <section class="space-y-4">
                <div class="flex flex-col gap-3 rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-base font-black text-[#2F3E5C]">Turnos institucionales</h2>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Bloques de tiempo con color y estado operativo.</p>
                    </div>
                    @can('turnos.crear')
                        <button type="button" wire:click="abrirModalTurno" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 text-xs font-black uppercase text-white"><i class="ph-bold ph-plus"></i>Nuevo turno</button>
                    @endcan
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @forelse($turnosLista as $turno)
                        <article class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl text-white shadow-sm" style="background-color: {{ $turno->color ?: '#2F3E5C' }}"><i class="ph-bold ph-clock-countdown text-xl"></i></span>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $turno->estado === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $turno->estado }}</span>
                            </div>
                            <h3 class="mt-3 text-base font-black text-[#2F3E5C]">{{ $turno->nombre }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">{{ substr($turno->hora_inicio ?? '--:--', 0, 5) }} - {{ substr($turno->hora_fin ?? '--:--', 0, 5) }}</p>
                            <p class="mt-3 line-clamp-2 min-h-[2.5rem] text-xs font-bold text-[#2F3E5C]/58">{{ $turno->descripcion ?: 'Sin descripcion registrada.' }}</p>
                            <div class="mt-4 flex justify-end gap-1.5">
                                @can('turnos.editar')
                                    <button type="button" wire:click="cargarTurno('{{ $turno->cod_turno }}')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white"><i class="ph-bold ph-pencil-simple"></i></button>
                                @endcan
                                @can('turnos.cambiar_estado')
                                    <button type="button" wire:click="eliminarTurno('{{ $turno->cod_turno }}')" wire:confirm="¿Archivar este turno institucional?" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700 transition hover:bg-rose-600 hover:text-white"><i class="ph-bold ph-archive"></i></button>
                                @endcan
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-8 text-center text-sm font-black text-[#2F3E5C]/55 md:col-span-2 xl:col-span-4">No hay turnos institucionales registrados.</div>
                    @endforelse
                </div>
            </section>
        @endif

        @if($tabActiva === 'asignaciones')
            <section class="space-y-4">
                <div class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Asignaciones de personal</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Relaciona personal, area, turno, dias de cobertura y vigencia.</p>
                        </div>
                        @can('turnos.asignar')
                            <button type="button" wire:click="abrirModalAsignacion" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-4 text-xs font-black uppercase text-white"><i class="ph-bold ph-user-plus"></i>Registrar asignacion</button>
                        @endcan
                    </div>

                    <div class="grid gap-2 md:grid-cols-7">
                        <input type="search" wire:model.live.debounce.300ms="search" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60] md:col-span-2" placeholder="Buscar personal">
                        <select wire:model.live="filtroArea" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]"><option value="">Area</option>@foreach($areasDisponibles as $area)<option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>@endforeach</select>
                        <select wire:model.live="filtroTurno" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]"><option value="">Turno</option>@foreach($turnosDisponibles as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>@endforeach</select>
                        <select wire:model.live="filtroEstado" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]"><option value="">Estado</option><option value="ACTIVA">Activa</option><option value="INACTIVA">Inactiva</option><option value="FINALIZADA">Finalizada</option></select>
                        <select wire:model.live="filtroDia" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-3 text-xs font-bold outline-none focus:border-[#E27D60]"><option value="">Dia</option>@foreach($diasSemana as $dia)<option value="{{ $dia }}">{{ $diaLabels[$dia] }}</option>@endforeach</select>
                        <button type="button" wire:click="limpiarFiltros" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-3 text-xs font-black text-[#2F3E5C]">Limpiar</button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-[1120px] w-full text-left text-sm">
                            <thead class="bg-[#E6DDD3]/80 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
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
                            <tbody class="divide-y divide-[#C7B5A3]/45 bg-white/25">
                                @forelse($asignaciones as $asig)
                                    <tr class="transition hover:bg-white/45">
                                        <td class="px-4 py-3">
                                            <p class="truncate font-black text-[#2F3E5C]">{{ $asig->usuario?->name ?? 'Sin usuario' }}</p>
                                            <p class="text-[11px] font-bold text-[#2F3E5C]/50">{{ $asig->usuario?->personalSalud ? 'Salud' : ($asig->usuario?->personalAdmin ? 'Administrativo' : 'Institucional') }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/65">{{ $asig->tipo_asignacion }}</td>
                                        <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/65">{{ $asig->area?->nombre ?? 'Sin area' }}</td>
                                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black text-white" style="background-color: {{ $asig->turno?->color ?: '#2F3E5C' }}">{{ $asig->turno?->nombre ?? 'Sin turno' }}</span></td>
                                        <td class="px-4 py-3"><div class="flex flex-wrap gap-1">@foreach($asig->dias_semana ?: [] as $dia)<span class="rounded bg-[#E6DDD3] px-1.5 py-0.5 text-[9px] font-black text-[#2F3E5C]/65">{{ substr($dia, 0, 3) }}</span>@endforeach</div></td>
                                        <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/65">{{ $asig->fecha_inicio?->format('d/m/Y') ?? 'S/F' }} - {{ $asig->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}</td>
                                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $asig->estado === 'ACTIVA' ? 'bg-emerald-100 text-emerald-700' : ($asig->estado === 'FINALIZADA' ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-700') }}">{{ $asig->estado }}</span></td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-1.5">
                                                <button type="button" wire:click="verFichaAsignacion('{{ $asig->cod_asignacion }}')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white" title="Ver detalle"><i class="ph-bold ph-eye"></i></button>
                                                @can('turnos.asignar')
                                                    @if($asig->estado === 'ACTIVA')
                                                        <button type="button" wire:click="cargarAsignacion('{{ $asig->cod_asignacion }}')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E27D60]/12 text-[#C75F46] transition hover:bg-[#E27D60] hover:text-white" title="Editar"><i class="ph-bold ph-pencil-simple"></i></button>
                                                    @endif
                                                @endcan
                                                @can('turnos.finalizar')
                                                    @if($asig->estado === 'ACTIVA')
                                                        <button type="button" wire:click="finalizarAsignacion('{{ $asig->cod_asignacion }}')" wire:confirm="¿Finalizar esta asignacion?" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700 transition hover:bg-rose-600 hover:text-white" title="Finalizar"><i class="ph-bold ph-check-circle"></i></button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-4 py-10 text-center text-sm font-black text-[#2F3E5C]/55">No se encontraron asignaciones con los filtros aplicados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if($tabActiva === 'calendario')
            <section class="grid gap-4 xl:grid-cols-[1fr_320px]">
                <div class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Calendario institucional</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">{{ $calendario['titulo'] }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div class="inline-flex rounded-xl bg-[#E6DDD3]/80 p-1">
                                @foreach(['dia' => 'Dia', 'semana' => 'Semana', 'mes' => 'Mes'] as $modo => $label)
                                    <button type="button" wire:click="cambiarModoCalendario('{{ $modo }}')" class="rounded-lg px-3 py-1.5 text-[11px] font-black {{ $modoCalendario === $modo ? 'bg-white text-[#2F3E5C] shadow-sm' : 'text-[#2F3E5C]/55' }}">{{ $label }}</button>
                                @endforeach
                            </div>
                            <button type="button" wire:click="calendarioAnterior" class="h-9 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-3 text-xs font-black"><i class="ph-bold ph-caret-left"></i></button>
                            <button type="button" wire:click="calendarioHoy" class="h-9 rounded-xl bg-[#2F3E5C] px-3 text-xs font-black text-white">Hoy</button>
                            <button type="button" wire:click="calendarioSiguiente" class="h-9 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-3 text-xs font-black"><i class="ph-bold ph-caret-right"></i></button>
                        </div>
                    </div>

                    <div class="grid gap-2 {{ $modoCalendario === 'dia' ? 'grid-cols-1' : ($modoCalendario === 'mes' ? 'grid-cols-2 md:grid-cols-7' : 'grid-cols-1 md:grid-cols-7') }}">
                        @foreach($calendario['dias'] as $dia)
                            <div class="rounded-2xl border {{ $dia['es_hoy'] ? 'border-[#E27D60]/60 bg-[#E27D60]/8' : 'border-[#C7B5A3]/55 bg-white/30' }} p-2" style="min-height: {{ $modoCalendario === 'mes' ? '128px' : '260px' }};">
                                <div class="mb-2 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">{{ $dia['dia_label'] }}</p>
                                        <p class="text-sm font-black {{ $dia['es_mes_actual'] ? 'text-[#2F3E5C]' : 'text-[#2F3E5C]/35' }}">{{ $dia['numero'] }} {{ $modoCalendario === 'mes' ? '' : $dia['mes'] }}</p>
                                    </div>
                                    @if($dia['total_eventos'] > 0)
                                        <span class="rounded-full bg-[#2F3E5C]/10 px-2 py-0.5 text-[10px] font-black text-[#2F3E5C]">{{ $dia['total_eventos'] }}</span>
                                    @endif
                                </div>

                                <div class="space-y-1.5 overflow-y-auto {{ $modoCalendario === 'mes' ? 'max-h-[84px]' : 'max-h-[210px]' }}">
                                    @forelse($dia['eventos'] as $evento)
                                        @if($evento['tipo'] === 'asignacion')
                                            <button type="button" wire:click="verFichaAsignacion('{{ $evento['id'] }}')" class="w-full rounded-xl border border-white/50 bg-white/55 p-2 text-left shadow-sm transition hover:border-[#E27D60]/45">
                                                <span class="block truncate text-[11px] font-black text-[#2F3E5C]">{{ $evento['titulo'] }}</span>
                                                <span class="block truncate text-[10px] font-bold text-[#2F3E5C]/55">{{ $evento['hora'] }}</span>
                                                <span class="mt-1 block h-1 rounded-full" style="background-color: {{ $evento['color'] }}"></span>
                                            </button>
                                        @else
                                            <button type="button" wire:click="verDetalleHorario('{{ $evento['tipo_horario'] }}', {{ $evento['id'] }})" class="w-full rounded-xl border border-sky-100 bg-sky-50/75 p-2 text-left transition hover:border-sky-300">
                                                <span class="block truncate text-[11px] font-black text-[#2F3E5C]">{{ $evento['titulo'] }}</span>
                                                <span class="block truncate text-[10px] font-bold text-[#2F3E5C]/55">{{ $evento['hora'] }}</span>
                                            </button>
                                        @endif
                                    @empty
                                        <p class="rounded-xl border border-dashed border-[#C7B5A3]/70 p-3 text-center text-[10px] font-bold text-[#2F3E5C]/45">Sin eventos</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                    <h3 class="text-base font-black text-[#2F3E5C]">Detalle del evento</h3>
                    @if($detalleEvento)
                        <div class="mt-3 rounded-2xl border border-[#C7B5A3]/55 bg-white/35 p-4">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-black text-white" style="background-color: {{ $detalleEvento['color'] }}">{{ $detalleEvento['tipo'] }}</span>
                            <h4 class="mt-3 text-sm font-black text-[#2F3E5C]">{{ $detalleEvento['titulo'] }}</h4>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">{{ $detalleEvento['subtitulo'] }}</p>
                            <div class="mt-3 space-y-2 text-xs font-bold text-[#2F3E5C]/70">
                                <p><span class="font-black text-[#2F3E5C]">Fecha:</span> {{ $detalleEvento['fecha'] }}</p>
                                <p><span class="font-black text-[#2F3E5C]">Horario:</span> {{ $detalleEvento['horario'] }}</p>
                                <p><span class="font-black text-[#2F3E5C]">Estado:</span> {{ $detalleEvento['estado'] }}</p>
                                <p class="rounded-xl bg-[#E6DDD3]/60 p-3">{{ $detalleEvento['descripcion'] }}</p>
                            </div>
                            @can('turnos.asignar')
                                <button type="button" wire:click="cargarHorario('{{ $detalleEvento['tipo_horario'] }}', {{ $detalleEvento['id'] }})" class="mt-4 w-full rounded-xl bg-[#2F3E5C] px-4 py-2 text-xs font-black uppercase text-white">Editar horario</button>
                            @endcan
                        </div>
                    @else
                        <p class="mt-3 rounded-2xl border border-dashed border-[#C7B5A3]/70 p-5 text-center text-xs font-bold text-[#2F3E5C]/55">Seleccione un horario del calendario para ver su detalle. Las asignaciones abren su ficha emergente.</p>
                    @endif
                    <div class="mt-4 space-y-2 text-[11px] font-bold text-[#2F3E5C]/65">
                        <p><span class="inline-block h-2 w-2 rounded-full bg-sky-400"></span> Horario regular</p>
                        <p><span class="inline-block h-2 w-2 rounded-full bg-[#8DA280]"></span> Asignacion activa</p>
                        <p><span class="inline-block h-2 w-2 rounded-full bg-[#E27D60]"></span> Turno especial / apoyo</p>
                    </div>
                </aside>
            </section>
        @endif

        @if($tabActiva === 'alertas')
            <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                <h2 class="text-base font-black text-[#2F3E5C]">Conflictos y alertas</h2>
                <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Incidencias calculadas desde horarios, turnos y asignaciones vigentes.</p>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-[#C7B5A3]/55">
                    <table class="min-w-[980px] w-full text-left text-sm">
                        <thead class="bg-[#E6DDD3]/80 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
                            <tr><th class="px-4 py-3">Tipo</th><th class="px-4 py-3">Persona / area</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Horario</th><th class="px-4 py-3">Descripcion</th><th class="px-4 py-3">Prioridad</th><th class="px-4 py-3">Accion sugerida</th></tr>
                        </thead>
                        <tbody class="divide-y divide-[#C7B5A3]/45 bg-white/25">
                            @forelse($alertas as $alerta)
                                <tr class="transition hover:bg-white/45">
                                    <td class="px-4 py-3 font-black text-[#2F3E5C]">{{ $alerta['tipo'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">{{ $alerta['persona'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">{{ $alerta['fecha'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">{{ $alerta['horario'] }}</td>
                                    <td class="px-4 py-3 text-xs font-bold text-[#2F3E5C]/70">{{ $alerta['descripcion'] }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $alerta['prioridad'] === 'Alta' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">{{ $alerta['prioridad'] }}</span></td>
                                    <td class="px-4 py-3 text-xs font-black text-[#2F3E5C]">{{ $alerta['accion'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm font-black text-[#2F3E5C]/55">No existen alertas operativas pendientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if($tabActiva === 'reportes')
            <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm">
                <h2 class="text-base font-black text-[#2F3E5C]">Reportes administrativos</h2>
                <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Evidencia institucional disponible para horarios, cobertura y asignaciones.</p>
                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @can('turnos.reportes')
                        <button type="button" wire:click="exportarReporteGeneralPdf" class="rounded-2xl border border-[#C7B5A3]/60 bg-white/35 p-4 text-left transition hover:border-[#E27D60]/45">
                            <i class="ph-bold ph-file-pdf text-2xl text-rose-600"></i>
                            <span class="mt-3 block text-sm font-black text-[#2F3E5C]">Reporte mensual de asignaciones</span>
                            <span class="mt-1 block text-xs font-bold text-[#2F3E5C]/55">PDF general operativo</span>
                        </button>
                        <button type="button" wire:click="exportarReporteGeneralExcel" class="rounded-2xl border border-[#C7B5A3]/60 bg-white/35 p-4 text-left transition hover:border-[#8DA280]/45">
                            <i class="ph-bold ph-file-xls text-2xl text-emerald-700"></i>
                            <span class="mt-3 block text-sm font-black text-[#2F3E5C]">Base de asignaciones</span>
                            <span class="mt-1 block text-xs font-bold text-[#2F3E5C]/55">Exportacion Excel</span>
                        </button>
                        <button type="button" wire:click="exportarCoberturaSemanalPdf" class="rounded-2xl border border-[#C7B5A3]/60 bg-white/35 p-4 text-left transition hover:border-[#2F3E5C]/35">
                            <i class="ph-bold ph-calendar-check text-2xl text-[#2F3E5C]"></i>
                            <span class="mt-3 block text-sm font-black text-[#2F3E5C]">Cobertura semanal</span>
                            <span class="mt-1 block text-xs font-bold text-[#2F3E5C]/55">PDF de turnos</span>
                        </button>
                        <button type="button" wire:click="exportarPersonalSinTurnoExcel" class="rounded-2xl border border-[#C7B5A3]/60 bg-white/35 p-4 text-left transition hover:border-amber-300">
                            <i class="ph-bold ph-user-warning text-2xl text-amber-700"></i>
                            <span class="mt-3 block text-sm font-black text-[#2F3E5C]">Personal sin horario/turno</span>
                            <span class="mt-1 block text-xs font-bold text-[#2F3E5C]/55">Excel de seguimiento</span>
                        </button>
                    @else
                        <p class="rounded-2xl border border-dashed border-[#C7B5A3]/70 p-8 text-center text-sm font-black text-[#2F3E5C]/55 md:col-span-2 xl:col-span-4">No tiene permiso para generar reportes.</p>
                    @endcan
                    <article class="rounded-2xl border border-dashed border-[#C7B5A3]/70 bg-white/20 p-4">
                        <i class="ph-bold ph-hourglass-medium text-2xl text-[#2F3E5C]/35"></i>
                        <span class="mt-3 block text-sm font-black text-[#2F3E5C]">Reporte individual de personal</span>
                        <span class="mt-1 block text-xs font-bold text-[#2F3E5C]/55">Preparado para siguiente fase</span>
                    </article>
                    <article class="rounded-2xl border border-dashed border-[#C7B5A3]/70 bg-white/20 p-4">
                        <i class="ph-bold ph-hourglass-medium text-2xl text-[#2F3E5C]/35"></i>
                        <span class="mt-3 block text-sm font-black text-[#2F3E5C]">Reporte de conflictos</span>
                        <span class="mt-1 block text-xs font-bold text-[#2F3E5C]/55">Estructura visible sin boton roto</span>
                    </article>
                </div>
            </section>
        @endif
    </div>

    @if($mostrarFormularioHorario)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-[#2F3E5C]/55 p-4 backdrop-blur-sm">
            <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-[1.5rem] border border-[#C7B5A3]/70 bg-[#F3ECE4] shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-[#C7B5A3]/60 p-5">
                    <div>
                        <h2 class="text-lg font-black text-[#2F3E5C]">{{ $isEdit ? 'Editar horario' : 'Registrar horario' }}</h2>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Defina dias, turno y rango horario del personal.</p>
                    </div>
                    <button type="button" wire:click="$set('mostrarFormularioHorario', false)" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E6DDD3] text-[#2F3E5C] transition hover:bg-[#E27D60] hover:text-white"><i class="ph-bold ph-x"></i></button>
                </div>
                <form wire:submit.prevent="guardarHorario" class="overflow-y-auto p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Tipo de personal</label>
                            <select wire:model.live="horario_tipo_personal" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" @disabled($isEdit)>
                                <option value="admin">Administrativo</option>
                                <option value="salud">Salud</option>
                            </select>
                            @error('horario_tipo_personal') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Personal</label>
                            <select wire:model="horario_personal_id" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" @disabled($isEdit)>
                                <option value="">Seleccione personal</option>
                                @foreach($personalHorarioOpciones as $persona)
                                    <option value="{{ $persona['id'] }}">{{ $persona['nombre'] }} · {{ $persona['cargo'] }}</option>
                                @endforeach
                            </select>
                            @error('horario_personal_id') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Dias de la semana</label>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach($diasSemana as $dia)
                                    <label class="flex items-center justify-between rounded-xl border border-[#C7B5A3]/60 bg-white/35 px-4 py-3 text-sm font-black text-[#2F3E5C]">
                                        {{ $diaLabels[$dia] }}
                                        <input wire:model="horario_dias_semana" type="checkbox" value="{{ $dia }}" class="h-5 w-5 rounded border-[#C7B5A3] text-[#E27D60] focus:ring-[#E27D60]" @disabled($isEdit && !in_array($dia, $horario_dias_semana ?? []))>
                                    </label>
                                @endforeach
                            </div>
                            @error('horario_dias_semana') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Hora inicio</label>
                            <input wire:model="horario_hora_inicio" type="time" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                            @error('horario_hora_inicio') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Hora fin</label>
                            <input wire:model="horario_hora_fin" type="time" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                            @error('horario_hora_fin') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Turno</label>
                            <select wire:model="horario_turno" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">
                                <option value="Mañana">Mañana</option><option value="Tarde">Tarde</option><option value="Noche">Noche</option><option value="Guardia">Guardia</option><option value="Evento especial">Evento especial</option><option value="Apoyo">Apoyo</option>
                            </select>
                            @error('horario_turno') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Estado</label>
                            <select wire:model="horario_estado" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="ACTIVO">Activo</option><option value="INACTIVO">Inactivo</option></select>
                            @error('horario_estado') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Observacion</label>
                            <textarea wire:model="horario_observaciones" rows="3" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"></textarea>
                            @error('horario_observaciones') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#C7B5A3]/60 pt-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="$set('mostrarFormularioHorario', false)" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-4 text-xs font-black text-[#2F3E5C]">Cancelar</button>
                        <button type="submit" class="h-10 rounded-xl bg-[#8DA280] px-5 text-xs font-black uppercase text-white">Guardar horario</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($mostrarFormularioTurno)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-[#2F3E5C]/55 p-4 backdrop-blur-sm">
            <div class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-[1.5rem] border border-[#C7B5A3]/70 bg-[#F3ECE4] shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-[#C7B5A3]/60 p-5">
                    <div><h2 class="text-lg font-black text-[#2F3E5C]">{{ $isEdit ? 'Editar turno institucional' : 'Registrar turno institucional' }}</h2><p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Identificacion, horario, color y estado del turno.</p></div>
                    <button type="button" wire:click="$set('mostrarFormularioTurno', false)" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E6DDD3] text-[#2F3E5C] transition hover:bg-[#E27D60] hover:text-white"><i class="ph-bold ph-x"></i></button>
                </div>
                <form wire:submit.prevent="guardarTurno" class="overflow-y-auto p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2"><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Nombre del turno</label><input wire:model="turno_nombre" type="text" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]" placeholder="Ej. Turno mañana">@error('turno_nombre') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Hora inicio</label><input wire:model="turno_hora_inicio" type="time" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">@error('turno_hora_inicio') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Hora fin</label><input wire:model="turno_hora_fin" type="time" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">@error('turno_hora_fin') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Color</label><input wire:model="turno_color" type="color" class="h-12 w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-3 py-2">@error('turno_color') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Estado</label><select wire:model="turno_estado" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="ACTIVO">Activo</option><option value="INACTIVO">Inactivo</option></select>@error('turno_estado') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div class="md:col-span-2"><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Descripcion</label><textarea wire:model="turno_descripcion" rows="2" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"></textarea>@error('turno_descripcion') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div class="md:col-span-2"><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Observacion</label><textarea wire:model="turno_observaciones" rows="2" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"></textarea>@error('turno_observaciones') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#C7B5A3]/60 pt-4 sm:flex-row sm:justify-end"><button type="button" wire:click="$set('mostrarFormularioTurno', false)" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-4 text-xs font-black text-[#2F3E5C]">Cancelar</button><button type="submit" class="h-10 rounded-xl bg-[#2F3E5C] px-5 text-xs font-black uppercase text-white">Guardar turno</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($mostrarFormularioAsignacion)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-[#2F3E5C]/55 p-4 backdrop-blur-sm">
            <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-[1.5rem] border border-[#C7B5A3]/70 bg-[#F3ECE4] shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-[#C7B5A3]/60 p-5">
                    <div><h2 class="text-lg font-black text-[#2F3E5C]">{{ $isEdit ? 'Editar asignacion' : 'Registrar asignacion' }}</h2><p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Se validara compatibilidad con horarios y conflictos activos.</p></div>
                    <button type="button" wire:click="$set('mostrarFormularioAsignacion', false)" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E6DDD3] text-[#2F3E5C] transition hover:bg-[#E27D60] hover:text-white"><i class="ph-bold ph-x"></i></button>
                </div>
                <form wire:submit.prevent="guardarAsignacion" class="overflow-y-auto p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Personal asignado</label><select wire:model="asig_cod_usu" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="">Seleccione personal</option>@foreach($usuariosDisponibles as $usuario)<option value="{{ $usuario->cod_usu }}">{{ $usuario->name }} · {{ $usuario->areaInstitucional?->nombre ?? 'Sin area' }}</option>@endforeach</select>@error('asig_cod_usu') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Area institucional</label><select wire:model="asig_cod_area" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="">Seleccione area</option>@foreach($areasDisponibles as $area)<option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>@endforeach</select>@error('asig_cod_area') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Turno</label><select wire:model="asig_cod_turno" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="">Seleccione turno</option>@foreach($turnosDisponibles as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} · {{ substr($turno->hora_inicio ?? '--:--', 0, 5) }}-{{ substr($turno->hora_fin ?? '--:--', 0, 5) }}</option>@endforeach</select>@error('asig_cod_turno') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Tipo de asignacion</label><select wire:model="asig_tipo_asignacion" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="REGULAR">Regular</option><option value="APOYO">Apoyo temporal</option><option value="COBERTURA">Cobertura especial</option><option value="VOLUNTARIADO">Voluntariado</option></select>@error('asig_tipo_asignacion') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Fecha inicio</label><input wire:model="asig_fecha_inicio" type="date" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">@error('asig_fecha_inicio') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Fecha fin</label><input wire:model="asig_fecha_fin" type="date" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]">@error('asig_fecha_fin') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div class="md:col-span-2"><label class="mb-2 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Dias asignados</label><div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">@foreach($diasSemana as $dia)<label class="flex items-center justify-between rounded-xl border border-[#C7B5A3]/60 bg-white/35 px-4 py-3 text-sm font-black text-[#2F3E5C]">{{ $diaLabels[$dia] }}<input wire:model="asig_dias_semana" type="checkbox" value="{{ $dia }}" class="h-5 w-5 rounded border-[#C7B5A3] text-[#E27D60] focus:ring-[#E27D60]"></label>@endforeach</div>@error('asig_dias_semana') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <div><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Estado</label><select wire:model="asig_estado" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"><option value="ACTIVA">Activa</option><option value="INACTIVA">Inactiva</option><option value="FINALIZADA">Finalizada</option></select>@error('asig_estado') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                        <label class="flex items-center justify-between rounded-xl border border-[#C7B5A3]/60 bg-white/35 px-4 py-3 text-sm font-black text-[#2F3E5C]">Apoyo temporal en otra area<input wire:model="asig_apoyo_temporal" type="checkbox" class="h-5 w-5 rounded border-[#C7B5A3] text-[#E27D60] focus:ring-[#E27D60]"></label>
                        <div class="md:col-span-2"><label class="mb-1 block text-[10px] font-black uppercase text-[#2F3E5C]/60">Observacion</label><textarea wire:model="asig_observaciones" rows="3" class="w-full rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/70 px-4 py-3 text-sm font-bold outline-none focus:border-[#E27D60]"></textarea>@error('asig_observaciones') <span class="mt-1 block text-xs font-black text-rose-600">{{ $message }}</span> @enderror</div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#C7B5A3]/60 pt-4 sm:flex-row sm:justify-end"><button type="button" wire:click="$set('mostrarFormularioAsignacion', false)" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-4 text-xs font-black text-[#2F3E5C]">Cancelar</button><button type="submit" class="h-10 rounded-xl bg-[#E27D60] px-5 text-xs font-black uppercase text-white">Guardar asignacion</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($mostrarFichaAsignacion && $asignacionSeleccionada)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-[#2F3E5C]/55 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl overflow-hidden rounded-[1.5rem] border border-[#C7B5A3]/70 bg-[#F3ECE4] shadow-2xl">
                <div class="flex items-start justify-between gap-3 border-b border-[#C7B5A3]/60 p-5">
                    <div><h2 class="text-lg font-black text-[#2F3E5C]">Detalle de asignacion</h2><p class="mt-1 text-xs font-bold text-[#2F3E5C]/58">Consulta operativa sin salir del modulo.</p></div>
                    <button type="button" wire:click="$set('mostrarFichaAsignacion', false)" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#E6DDD3] text-[#2F3E5C] transition hover:bg-[#E27D60] hover:text-white"><i class="ph-bold ph-x"></i></button>
                </div>
                <div class="p-5">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white/35 p-3"><p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Personal</p><p class="mt-1 text-sm font-black text-[#2F3E5C]">{{ $asignacionSeleccionada->usuario?->name ?? 'Sin usuario' }}</p></div>
                        <div class="rounded-2xl bg-white/35 p-3"><p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Area</p><p class="mt-1 text-sm font-black text-[#2F3E5C]">{{ $asignacionSeleccionada->area?->nombre ?? 'Sin area' }}</p></div>
                        <div class="rounded-2xl bg-white/35 p-3"><p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Turno</p><p class="mt-1 text-sm font-black text-[#2F3E5C]">{{ $asignacionSeleccionada->turno?->nombre ?? 'Sin turno' }}</p><p class="text-xs font-bold text-[#2F3E5C]/55">{{ substr($asignacionSeleccionada->turno?->hora_inicio ?? '--:--', 0, 5) }} - {{ substr($asignacionSeleccionada->turno?->hora_fin ?? '--:--', 0, 5) }}</p></div>
                        <div class="rounded-2xl bg-white/35 p-3"><p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Estado</p><p class="mt-1 text-sm font-black text-[#2F3E5C]">{{ $asignacionSeleccionada->estado }}</p></div>
                        <div class="rounded-2xl bg-white/35 p-3 sm:col-span-2"><p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Dias y vigencia</p><p class="mt-1 text-sm font-black text-[#2F3E5C]">{{ implode(', ', $asignacionSeleccionada->dias_semana ?: []) }}</p><p class="text-xs font-bold text-[#2F3E5C]/55">{{ $asignacionSeleccionada->fecha_inicio?->format('d/m/Y') ?? 'S/F' }} - {{ $asignacionSeleccionada->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}</p></div>
                        <div class="rounded-2xl bg-white/35 p-3 sm:col-span-2"><p class="text-[10px] font-black uppercase text-[#2F3E5C]/50">Observacion</p><p class="mt-1 text-sm font-bold text-[#2F3E5C]/70">{{ $asignacionSeleccionada->observaciones ?: 'Sin observaciones.' }}</p></div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#C7B5A3]/60 pt-4 sm:flex-row sm:justify-end">
                        @can('turnos.asignar')
                            @if($asignacionSeleccionada->estado === 'ACTIVA')
                                <button type="button" wire:click="cargarAsignacion('{{ $asignacionSeleccionada->cod_asignacion }}')" class="h-10 rounded-xl border border-[#C7B5A3]/70 bg-white/35 px-4 text-xs font-black text-[#2F3E5C]">Editar</button>
                            @endif
                        @endcan
                        <button type="button" wire:click="$set('mostrarFichaAsignacion', false)" class="h-10 rounded-xl bg-[#2F3E5C] px-4 text-xs font-black uppercase text-white">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
