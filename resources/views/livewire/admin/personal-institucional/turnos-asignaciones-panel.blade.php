<div class="min-h-[calc(100vh-8rem)] overflow-x-hidden bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
    x-data="{ panelPlanilla: @entangle('tipoPlanilla').live }">
    <div class="mx-auto max-w-[1480px] space-y-5">
        @php
            $estadoClases = [
                'ACTIVO' => 'border-estado-exito/20 bg-estado-exitoBg text-estado-exito',
                'EN TURNO' => 'border-estado-exito/20 bg-estado-exitoBg text-estado-exito',
                'DISPONIBLE' => 'border-boton-principal/20 bg-boton-principal/10 text-boton-principal',
                'OCUPADO' => 'border-boton-acento/20 bg-boton-acento/10 text-boton-acento',
                'SIN HORARIO' => 'border-borde-suave bg-fondo-hover text-apoyo',
                'FINALIZADO' => 'border-borde-suave bg-fondo-hover text-apoyo',
                'SUSPENDIDO' => 'border-estado-peligro/20 bg-estado-peligroBg text-estado-peligro',
                'CONFLICTO' => 'border-estado-peligro/20 bg-estado-peligroBg text-estado-peligro',
            ];

            $kpis = [
                ['icon' => 'ph-calendar-check', 'label' => 'Personal con horario', 'value' => $stats['con_horario'] ?? 0, 'class' => 'border-estado-exito/20 bg-estado-exitoBg text-estado-exito'],
                ['icon' => 'ph-calendar-x', 'label' => 'Personal sin horario', 'value' => $stats['sin_horario'] ?? 0, 'class' => 'border-borde-suave bg-fondo-hover text-apoyo'],
                ['icon' => 'ph-clock-user', 'label' => 'En turno hoy', 'value' => $stats['en_turno_hoy'] ?? 0, 'class' => 'border-boton-acento/20 bg-boton-acento/10 text-boton-acento'],
                ['icon' => 'ph-user-check', 'label' => 'Disponibles hoy', 'value' => $stats['disponibles_hoy'] ?? 0, 'class' => 'border-boton-principal/20 bg-boton-principal/10 text-boton-principal'],
                ['icon' => 'ph-warning-circle', 'label' => 'Turnos por cubrir', 'value' => $stats['turnos_por_cubrir'] ?? 0, 'class' => 'border-boton-acento/20 bg-boton-acento/10 text-boton-acento'],
                ['icon' => 'ph-stack', 'label' => 'Asignaciones activas', 'value' => $stats['asignaciones_activas'] ?? 0, 'class' => 'border-borde-suave bg-fondo-card/45 text-titulo'],
            ];
        @endphp

        {{-- ENCABEZADO --}}
        <section class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-[0_16px_44px_rgba(47,62,92,0.10)]">
            <div class="h-1.5 bg-boton-acento"></div>
            <div class="flex flex-col gap-4 p-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-4xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">
                        <i class="ph-bold ph-calendar-check text-sm"></i>
                        Gestión del sistema
                    </span>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo sm:text-3xl">Horarios y asignaciones</h1>
                    <p class="mt-1 max-w-3xl text-sm font-bold leading-relaxed text-apoyo">
                        Planificación institucional para administración, personal de salud y planilla rotativa de enfermería.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="$refresh" wire:loading.attr="disabled"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-card/45 px-4 text-[11px] font-bold uppercase tracking-wider text-titulo transition hover:-translate-y-0.5 hover:bg-fondo-card">
                        <i class="ph-bold ph-arrows-clockwise text-sm" wire:loading.class="animate-spin" wire:target="$refresh"></i>
                        Actualizar
                    </button>
                    <button type="button" wire:click="exportarCalendario"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-card/45 px-4 text-[11px] font-bold uppercase tracking-wider text-titulo transition hover:-translate-y-0.5 hover:bg-fondo-card">
                        <i class="ph-bold ph-download-simple text-sm"></i>
                        Exportar
                    </button>
                    @can('turnos.asignar')
                        <button type="button" wire:click="abrirNuevaAsignacion"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-[11px] font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:-translate-y-0.5 hover:bg-boton-acento/90">
                            <i class="ph-bold ph-plus-circle text-sm"></i>
                            Nueva asignación
                        </button>
                    @endcan
                </div>
            </div>
        </section>

        {{-- KPIS --}}
        <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($kpis as $card)
                <article class="rounded-2xl border p-3 shadow-sm transition hover:-translate-y-0.5 {{ $card['class'] }}">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-panel/65">
                            <i class="ph-bold {{ $card['icon'] }} text-lg"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xl font-black leading-none">{{ $card['value'] }}</p>
                            <p class="mt-1 truncate text-[10px] font-black uppercase tracking-wide">{{ $card['label'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- FILTROS GENERALES --}}
        <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-3 shadow-sm">
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
                <div class="relative sm:col-span-2">
                    <i class="ph-bold ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                    <input type="search" wire:model.live.debounce.300ms="busqueda"
                        class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/40 pl-9 pr-3 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus"
                        placeholder="Buscar personal por nombre, correo o código...">
                </div>

                <select wire:model.live="filtroTipo" class="h-10 rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                    <option value="">Todo el personal</option>
                    <option value="salud">Personal de salud</option>
                    <option value="admin">Administrativo</option>
                </select>

                <select wire:model.live="filtroRol" class="h-10 rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol }}">{{ $rol }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroArea" class="h-10 rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                    <option value="">Todas las áreas</option>
                    @foreach ($areas as $area)
                        <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroTurno" class="h-10 rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                    <option value="">Todos los turnos</option>
                    @foreach ($turnos as $turno)
                        <option value="{{ $turno->nombre }}">{{ $turno->nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroEstado" class="h-10 rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                    <option value="">Todos los estados</option>
                    <option value="EN_TURNO">En turno</option>
                    <option value="DISPONIBLE">Disponible</option>
                    <option value="SIN_HORARIO">Sin horario</option>
                    <option value="CONFLICTO">Conflicto</option>
                </select>

                <button type="button" wire:click="limpiarFiltros"
                    class="h-10 rounded-xl border border-borde-suave bg-fondo-card/45 px-3 text-xs font-bold uppercase tracking-wide text-titulo transition hover:bg-fondo-card">
                    Limpiar
                </button>
            </div>
        </section>

        {{-- CONTROLES DE VISTA PRINCIPAL --}}
        <section class="flex flex-col gap-3 rounded-2xl border border-borde-suave bg-fondo-panel p-3 shadow-sm xl:flex-row xl:items-center xl:justify-between">
            <div class="overflow-x-auto">
                <div class="flex min-w-max gap-1 rounded-xl bg-fondo-card/35 p-1">
                    @foreach ([
                        'semana' => ['Semana', 'ph-calendar-dots'],
                        'dia' => ['Día', 'ph-sun'],
                        'mes' => ['Mes', 'ph-calendar-blank'],
                        'anio' => ['Año', 'ph-chart-line-up'],
                    ] as $vista => [$label, $icon])
                        <button type="button" wire:click="cambiarVista('{{ $vista }}')"
                            class="inline-flex h-9 items-center gap-2 rounded-lg px-3 text-xs font-black transition {{ $vistaCalendario === $vista ? 'bg-boton-principal text-inverso shadow-sm' : 'text-apoyo hover:bg-fondo-card hover:text-titulo' }}">
                            <i class="ph-bold {{ $icon }}"></i>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="moverPeriodo(-1)"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-borde-suave bg-fondo-card/45 text-titulo transition hover:bg-fondo-card">
                    <i class="ph-bold ph-caret-left"></i>
                </button>
                <button type="button" wire:click="irHoy"
                    class="h-9 rounded-xl bg-boton-principal px-4 text-xs font-black uppercase tracking-wide text-inverso">
                    Hoy
                </button>
                <button type="button" wire:click="moverPeriodo(1)"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-borde-suave bg-fondo-card/45 text-titulo transition hover:bg-fondo-card">
                    <i class="ph-bold ph-caret-right"></i>
                </button>
            </div>
        </section>

        {{-- PLANILLA INSTITUCIONAL --}}
        <section class="space-y-4 rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <span class="inline-flex rounded-full border border-borde-suave bg-fondo-card/45 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-apoyo">
                        Planilla institucional
                    </span>
                    <h2 class="mt-2 text-xl font-black text-titulo">Programación por tipo de personal</h2>
                    <p class="mt-1 max-w-3xl text-sm font-bold text-apoyo">
                        Enfermería rota con algoritmo modular. Salud general y administración mantienen horarios simples por jornada.
                    </p>
                </div>

                <div class="grid gap-2 sm:grid-cols-3">
                    @foreach ([
                        'enfermeria' => ['Enfermería', 'ph-first-aid-kit', 'Rotación modular'],
                        'salud' => ['Personal de salud', 'ph-heartbeat', 'Horarios clínicos'],
                        'administrativo' => ['Administrativo', 'ph-briefcase', 'Horarios institucionales'],
                    ] as $tipo => [$label, $icon, $desc])
                        <button type="button" wire:click="cambiarTipoPlanilla('{{ $tipo }}')"
                            class="rounded-2xl border p-3 text-left transition hover:-translate-y-0.5 {{ $tipoPlanilla === $tipo ? 'border-borde-focus bg-fondo-card shadow-sm' : 'border-borde-suave bg-fondo-card/30 hover:bg-fondo-card/55' }}">
                            <div class="flex items-center gap-2">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $tipoPlanilla === $tipo ? 'bg-boton-principal text-inverso' : 'bg-fondo-panel text-titulo' }}">
                                    <i class="ph-bold {{ $icon }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-black text-titulo">{{ $label }}</p>
                                    <p class="truncate text-[10px] font-bold text-apoyo">{{ $desc }}</p>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($tipoPlanilla === 'enfermeria')
                {{-- RESUMEN PLANILLA --}}
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                    @foreach ([
                        ['icon' => 'ph-users-three', 'label' => 'Enfermeros base', 'value' => $resumenPlanilla['total_enfermeros'] ?? 0, 'note' => 'E01 a E12'],
                        ['icon' => 'ph-shield-check', 'label' => 'Cobertura', 'value' => $resumenPlanilla['cobertura_texto'] ?? '0%', 'note' => 'Vista previa'],
                        ['icon' => 'ph-clock-countdown', 'label' => 'Turnos cubiertos', 'value' => $resumenPlanilla['turnos_cubiertos'] ?? '0/0', 'note' => 'Mañana, tarde y noche'],
                        ['icon' => 'ph-hand-heart', 'label' => 'Apoyos', 'value' => $resumenPlanilla['apoyos'] ?? 0, 'note' => 'Volantes'],
                        ['icon' => 'ph-bed', 'label' => 'Descansos', 'value' => $resumenPlanilla['descansos'] ?? 0, 'note' => 'Programados'],
                        ['icon' => 'ph-warning-diamond', 'label' => 'Alertas críticas', 'value' => $resumenPlanilla['alertas_criticas'] ?? 0, 'note' => ($resumenPlanilla['alertas_total'] ?? 0) . ' observaciones'],
                    ] as $card)
                        <article class="rounded-2xl border border-borde-suave bg-fondo-card/45 p-3 shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-titulo">
                                    <i class="ph-bold {{ $card['icon'] }} text-lg"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xl font-black leading-none text-titulo">{{ $card['value'] }}</p>
                                    <p class="mt-1 truncate text-[10px] font-black uppercase tracking-wide text-apoyo">{{ $card['label'] }}</p>
                                </div>
                            </div>
                            <p class="mt-2 truncate text-[10px] font-bold text-apoyo">{{ $card['note'] }}</p>
                        </article>
                    @endforeach
                </div>

                {{-- CONFIGURACIÓN --}}
                <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
                    <div class="rounded-2xl border border-borde-suave bg-fondo-card/25 p-4">
                        <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h3 class="text-base font-black text-titulo">Generador modular de enfermería</h3>
                                <p class="mt-1 text-xs font-bold text-apoyo">Vista previa en memoria, sin guardar todavía en base de datos.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="generarPlanillaEnfermeria"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 text-xs font-black uppercase text-inverso shadow-sm transition hover:-translate-y-0.5 hover:bg-boton-acento/90">
                                    <i class="ph-bold ph-arrows-clockwise"></i>
                                    Generar vista previa
                                </button>
                                <button type="button" wire:click="limpiarFiltrosPlanilla"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-4 text-xs font-black uppercase text-titulo transition hover:bg-fondo-card">
                                    <i class="ph-bold ph-broom"></i>
                                    Mostrar todo
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-2 md:grid-cols-12">
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-apoyo">Fecha inicio</label>
                                <input type="date" wire:model.live="fechaInicioPlanilla" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-apoyo">Semanas</label>
                                <select wire:model.live="cantidadSemanas" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                    <option value="1">1 semana</option>
                                    <option value="2">2 semanas</option>
                                    <option value="4">4 semanas</option>
                                    <option value="12">12 semanas</option>
                                    <option value="24">24 semanas</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-apoyo">Salto semanal</label>
                                <select wire:model.live="saltoSemanal" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                    <option value="1">Salto 1</option>
                                    <option value="2">Salto 2</option>
                                    <option value="3">Salto 3</option>
                                    <option value="5">Salto 5</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-apoyo">Trabajador</label>
                                <select wire:model.live="trabajadorFiltro" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                    <option value="">Todos</option>
                                    @foreach (($filtrosPlanilla['trabajadores'] ?? []) as $trabajador)
                                        <option value="{{ $trabajador['codigo'] }}">{{ $trabajador['codigo'] }} · {{ $trabajador['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-apoyo">Turno</label>
                                <select wire:model.live="turnoFiltroPlanilla" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                    <option value="">Todos</option>
                                    @foreach (($filtrosPlanilla['turnos'] ?? []) as $turno)
                                        <option value="{{ $turno['codigo'] }}">{{ $turno['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-wide text-apoyo">Grupo</label>
                                <select wire:model.live="grupoFiltroPlanilla" class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                    <option value="">Todos</option>
                                    @foreach (($filtrosPlanilla['grupos'] ?? []) as $grupo)
                                        <option value="{{ $grupo['codigo'] }}">{{ $grupo['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-[11px] font-bold text-titulo">
                                <input type="checkbox" wire:model.live="mostrarApoyo" class="rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                Mostrar apoyo
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-[11px] font-bold text-titulo">
                                <input type="checkbox" wire:model.live="mostrarDescanso" class="rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                Mostrar descanso
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-[11px] font-bold text-titulo">
                                <input type="checkbox" wire:model.live="mostrarGrupos" class="rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                Mostrar grupos A/B/C
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-[11px] font-bold text-titulo">
                                <input type="checkbox" wire:model.live="soloConflictos" class="rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                Solo conflictos
                            </label>
                        </div>
                    </div>

                    <aside class="rounded-2xl border border-borde-suave bg-fondo-card/25 p-4">
                        <h3 class="text-base font-black text-titulo">Equilibrio de rotación</h3>
                        <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">{{ $equilibrioPlanilla['descripcion'] ?? 'Genere una vista previa para calcular el equilibrio.' }}</p>
                        <div class="mt-4 space-y-2">
                            <div class="rounded-2xl bg-fondo-panel p-3">
                                <p class="text-[10px] font-black uppercase tracking-wide text-apoyo">Periodo de equilibrio</p>
                                <p class="mt-1 text-2xl font-black text-titulo">{{ $equilibrioPlanilla['periodo_equilibrio_semanas'] ?? 0 }} semanas</p>
                            </div>
                            <div class="rounded-2xl bg-fondo-panel p-3">
                                <p class="text-[10px] font-black uppercase tracking-wide text-apoyo">Semanas esperadas</p>
                                <p class="mt-1 text-sm font-black text-titulo">{{ implode(', ', $equilibrioPlanilla['semanas_equilibrio'] ?? []) ?: 'Sin calcular' }}</p>
                            </div>
                            <div class="rounded-2xl {{ $resumenPlanilla['clase_estado'] ?? 'bg-fondo-panel text-titulo' }} p-3">
                                <p class="text-[10px] font-black uppercase tracking-wide">Estado</p>
                                <p class="mt-1 text-sm font-black">{{ $resumenPlanilla['estado_planilla'] ?? 'Pendiente' }}</p>
                            </div>
                        </div>
                    </aside>
                </div>

                {{-- SUBVISTAS --}}
                <nav class="overflow-x-auto rounded-2xl border border-borde-suave bg-fondo-card/25 p-2">
                    <div class="flex min-w-max gap-1">
                        @foreach ([
                            'semanal' => ['Vista semanal', 'ph-table'],
                            'hoy' => ['Hoy', 'ph-sun-horizon'],
                            'enfermero' => ['Por enfermero', 'ph-user-focus'],
                            'carga' => ['Carga laboral', 'ph-gauge'],
                            'alertas' => ['Alertas', 'ph-warning-diamond'],
                        ] as $vista => [$label, $icon])
                            <button type="button" wire:click="cambiarVistaPlanilla('{{ $vista }}')"
                                class="inline-flex h-10 items-center gap-2 rounded-xl px-3 text-xs font-black transition {{ $vistaPlanilla === $vista ? 'bg-boton-principal text-inverso shadow-sm' : 'text-apoyo hover:bg-fondo-card hover:text-titulo' }}">
                                <i class="ph-bold {{ $icon }}"></i>
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </nav>

                @if ($vistaPlanilla === 'semanal')
                    <section class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-sm">
                        <div class="border-b border-borde-suave bg-fondo-card/30 px-4 py-3">
                            <h3 class="text-sm font-black text-titulo">Vista semanal de enfermería</h3>
                            <p class="mt-1 text-xs font-bold text-apoyo">Mañana 06:00-14:00, tarde 14:00-22:00 y noche 22:00-06:00.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-[1180px] w-full text-left text-sm">
                                <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo">
                                    <tr>
                                        <th class="w-[130px] px-4 py-3">Día</th>
                                        <th class="px-4 py-3">Mañana<br><span class="normal-case tracking-normal">06:00-14:00</span></th>
                                        <th class="px-4 py-3">Tarde<br><span class="normal-case tracking-normal">14:00-22:00</span></th>
                                        <th class="px-4 py-3">Noche<br><span class="normal-case tracking-normal">22:00-06:00</span></th>
                                        @if ($mostrarApoyo)<th class="px-4 py-3">Apoyo / Volante</th>@endif
                                        @if ($mostrarDescanso)<th class="px-4 py-3">Descanso</th>@endif
                                        <th class="px-4 py-3">Cobertura</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde-suave bg-fondo-card/20">
                                    @forelse(($vistaSemanalPlanilla ?? []) as $semana)
                                        <tr class="bg-fondo-panel/80">
                                            <td colspan="{{ 5 + ($mostrarApoyo ? 1 : 0) + ($mostrarDescanso ? 1 : 0) }}" class="px-4 py-3 text-xs font-black uppercase tracking-wider text-titulo">
                                                Semana {{ $semana['semana'] ?? '-' }} · {{ $semana['fecha_inicio'] ?? '' }} al {{ $semana['fecha_fin'] ?? '' }}
                                            </td>
                                        </tr>

                                        @foreach (($semana['dias'] ?? []) as $dia)
                                            <tr class="align-top transition hover:bg-fondo-card/45">
                                                <td class="px-4 py-4">
                                                    <p class="font-black text-titulo">{{ $dia['dia'] ?? '-' }}</p>
                                                    <p class="text-[11px] font-bold text-apoyo">{{ $dia['fecha'] ?? '' }}</p>
                                                </td>

                                                @foreach (['MANANA', 'TARDE', 'NOCHE'] as $codigoTurno)
                                                    <td class="px-4 py-4">
                                                        <div class="space-y-1.5">
                                                            @forelse(($dia['turnos'][$codigoTurno]['asignaciones'] ?? []) as $asignacion)
                                                                <button type="button"
                                                                    class="inline-flex w-full items-center justify-between gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-2.5 py-2 text-left text-xs font-bold text-titulo transition hover:border-borde-focus hover:bg-fondo-card"
                                                                    title="{{ $asignacion['nombre'] ?? $asignacion['codigo'] }}">
                                                                    <span class="flex min-w-0 items-center gap-2">
                                                                        <span class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-black {{ $asignacion['clase_familia'] ?? 'bg-fondo-card text-titulo' }}">{{ $asignacion['codigo'] ?? '-' }}</span>
                                                                        <span class="min-w-0">
                                                                            <span class="block truncate">{{ $asignacion['nombre'] ?? 'Sin nombre' }}</span>
                                                                            @if ($mostrarGrupos && !empty($asignacion['grupo_nombre']))
                                                                                <span class="block text-[10px] text-apoyo">{{ $asignacion['grupo_nombre'] }}</span>
                                                                            @endif
                                                                        </span>
                                                                    </span>
                                                                    <span class="shrink-0 text-[10px] text-apoyo">8h</span>
                                                                </button>
                                                            @empty
                                                                <span class="block rounded-xl border border-dashed border-borde-suave p-3 text-center text-[11px] font-bold text-apoyo">Sin asignar</span>
                                                            @endforelse
                                                        </div>
                                                    </td>
                                                @endforeach

                                                @if ($mostrarApoyo)
                                                    <td class="px-4 py-4">
                                                        @forelse(($dia['turnos']['APOYO']['asignaciones'] ?? []) as $asignacion)
                                                            <span class="inline-flex w-full items-center justify-between rounded-xl border border-borde-suave bg-boton-acento/10 px-2.5 py-2 text-xs font-bold text-boton-acento">
                                                                <span>{{ $asignacion['codigo'] ?? '-' }} · {{ $asignacion['nombre'] ?? 'Apoyo' }}</span>
                                                                <span class="text-[10px]">8h</span>
                                                            </span>
                                                        @empty
                                                            <span class="block rounded-xl border border-dashed border-borde-suave p-3 text-center text-[11px] font-bold text-apoyo">Sin apoyo</span>
                                                        @endforelse
                                                    </td>
                                                @endif

                                                @if ($mostrarDescanso)
                                                    <td class="px-4 py-4">
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @forelse(($dia['turnos']['DESCANSO']['asignaciones'] ?? []) as $asignacion)
                                                                <span class="rounded-xl border border-borde-suave bg-fondo-hover px-2.5 py-2 text-xs font-bold text-apoyo">{{ $asignacion['codigo'] ?? '-' }}</span>
                                                            @empty
                                                                <span class="rounded-xl border border-dashed border-borde-suave p-3 text-[11px] font-bold text-apoyo">Sin descanso</span>
                                                            @endforelse
                                                        </div>
                                                    </td>
                                                @endif

                                                <td class="px-4 py-4">
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-black {{ ($dia['cobertura']['completa'] ?? false) ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-estado-peligro' }}">
                                                        {{ ($dia['cobertura']['completa'] ?? false) ? 'Completa' : 'Revisar' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-10 text-center text-sm font-bold text-apoyo">Genere una vista previa para visualizar la planilla.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($vistaPlanilla === 'hoy')
                    <section class="grid gap-4 xl:grid-cols-[1fr_340px]">
                        <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
                            <h3 class="text-base font-black text-titulo">Programación de hoy</h3>
                            <p class="mt-1 text-xs font-bold text-apoyo">{{ $vistaHoyPlanilla['dia'] ?? 'Día no disponible' }} · {{ $vistaHoyPlanilla['fecha'] ?? '' }}</p>
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                @foreach (['MANANA' => ['Mañana', '06:00-14:00'], 'TARDE' => ['Tarde', '14:00-22:00'], 'NOCHE' => ['Noche', '22:00-06:00']] as $codigoTurno => [$label, $horario])
                                    <article class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-3">
                                        <h4 class="text-sm font-black text-titulo">{{ $label }}</h4>
                                        <p class="text-[11px] font-bold text-apoyo">{{ $horario }}</p>
                                        <div class="mt-3 space-y-2">
                                            @forelse(($vistaHoyPlanilla['turnos'][$codigoTurno]['asignaciones'] ?? []) as $asignacion)
                                                <div class="rounded-xl bg-fondo-panel p-2">
                                                    <p class="text-xs font-black text-titulo">{{ $asignacion['codigo'] ?? '-' }} · {{ $asignacion['nombre'] ?? '' }}</p>
                                                    <p class="text-[10px] font-bold text-apoyo">{{ $asignacion['grupo_nombre'] ?? 'Sin grupo asignado' }}</p>
                                                </div>
                                            @empty
                                                <p class="rounded-xl border border-dashed border-borde-suave p-3 text-center text-xs font-bold text-apoyo">Sin asignaciones</p>
                                            @endforelse
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                        <aside class="space-y-3">
                            <article class="rounded-2xl border border-borde-suave bg-boton-acento/10 p-4 text-boton-acento">
                                <p class="text-[10px] font-black uppercase tracking-wider">Apoyo / volante</p>
                                @forelse(($vistaHoyPlanilla['turnos']['APOYO']['asignaciones'] ?? []) as $asignacion)
                                    <p class="mt-3 text-sm font-black">{{ $asignacion['codigo'] ?? '-' }} · {{ $asignacion['nombre'] ?? '' }}</p>
                                @empty
                                    <p class="mt-3 text-xs font-bold">Sin apoyo programado.</p>
                                @endforelse
                            </article>
                            <article class="rounded-2xl border border-borde-suave bg-fondo-hover p-4 text-apoyo">
                                <p class="text-[10px] font-black uppercase tracking-wider">Descanso</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse(($vistaHoyPlanilla['turnos']['DESCANSO']['asignaciones'] ?? []) as $asignacion)
                                        <span class="rounded-xl bg-fondo-card px-3 py-2 text-xs font-black">{{ $asignacion['codigo'] ?? '-' }}</span>
                                    @empty
                                        <p class="text-xs font-bold">Sin descansos programados.</p>
                                    @endforelse
                                </div>
                            </article>
                        </aside>
                    </section>
                @endif

                @if ($vistaPlanilla === 'enfermero')
                    <section class="grid gap-4 xl:grid-cols-[340px_1fr]">
                        <aside class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
                            <h3 class="text-base font-black text-titulo">Consulta por enfermero</h3>
                            <p class="mt-1 text-xs font-bold text-apoyo">Use el filtro superior o seleccione un trabajador.</p>
                            <div class="mt-4 max-h-[520px] space-y-2 overflow-y-auto pr-1">
                                @forelse(($vistaPorEnfermeroPlanilla ?? []) as $enfermero)
                                    <button type="button" wire:click="$set('trabajadorFiltro', '{{ $enfermero['codigo'] }}')"
                                        class="w-full rounded-xl border border-borde-suave bg-fondo-card/35 p-3 text-left transition hover:border-borde-focus">
                                        <p class="text-sm font-black text-titulo">{{ $enfermero['codigo'] }} · {{ $enfermero['nombre'] }}</p>
                                        <p class="text-[11px] font-bold text-apoyo">{{ $enfermero['familia_visual'] ?? 'Sin familia visual' }}</p>
                                    </button>
                                @empty
                                    <p class="rounded-xl border border-dashed border-borde-suave p-4 text-center text-xs font-bold text-apoyo">Sin datos por enfermero.</p>
                                @endforelse
                            </div>
                        </aside>
                        <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel">
                            <div class="border-b border-borde-suave bg-fondo-card/30 px-4 py-3">
                                <h3 class="text-sm font-black text-titulo">Horario individual</h3>
                                <p class="mt-1 text-xs font-bold text-apoyo">Detalle semanal por trabajador.</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-[920px] w-full text-left text-sm">
                                    <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo">
                                        <tr>
                                            <th class="px-4 py-3">Enfermero</th>
                                            <th class="px-4 py-3">Semana</th>
                                            <th class="px-4 py-3">Día</th>
                                            <th class="px-4 py-3">Turno</th>
                                            <th class="px-4 py-3">Horario</th>
                                            <th class="px-4 py-3">Grupo</th>
                                            <th class="px-4 py-3">Rol</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-borde-suave bg-fondo-card/20">
                                        @forelse(($vistaPorEnfermeroPlanilla ?? []) as $enfermero)
                                            @foreach (($enfermero['programacion'] ?? []) as $programacion)
                                                <tr class="transition hover:bg-fondo-card/45">
                                                    <td class="px-4 py-3 font-black text-titulo">{{ $enfermero['codigo'] }} · {{ $enfermero['nombre'] }}</td>
                                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $programacion['semana'] }}</td>
                                                    <td class="px-4 py-3 text-xs font-bold text-titulo">{{ $programacion['dia'] }}<br><span class="font-bold text-apoyo">{{ $programacion['fecha'] }}</span></td>
                                                    <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $programacion['clase_turno'] ?? 'bg-fondo-card text-titulo' }}">{{ $programacion['turno_nombre'] }}</span></td>
                                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $programacion['hora_inicio'] ?? '—' }} - {{ $programacion['hora_fin'] ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $programacion['grupo_nombre'] ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-xs font-black text-titulo">{{ $programacion['rol_en_turno'] }}</td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-10 text-center text-sm font-bold text-apoyo">Genere una planilla o seleccione un enfermero.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($vistaPlanilla === 'carga')
                    <section class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel">
                        <div class="border-b border-borde-suave bg-fondo-card/30 px-4 py-3">
                            <h3 class="text-sm font-black text-titulo">Carga laboral y equidad</h3>
                            <p class="mt-1 text-xs font-bold text-apoyo">Control de jornadas, descansos, noches y horas acumuladas.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-[1120px] w-full text-left text-sm">
                                <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo">
                                    <tr>
                                        <th class="px-4 py-3">Enfermero</th>
                                        <th class="px-4 py-3">Mañanas</th>
                                        <th class="px-4 py-3">Tardes</th>
                                        <th class="px-4 py-3">Noches</th>
                                        <th class="px-4 py-3">Apoyos</th>
                                        <th class="px-4 py-3">Descansos</th>
                                        <th class="px-4 py-3">Jornadas</th>
                                        <th class="px-4 py-3">Horas</th>
                                        <th class="px-4 py-3">Dif. promedio</th>
                                        <th class="px-4 py-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-borde-suave bg-fondo-card/20">
                                    @forelse(($cargaLaboralPlanilla ?? []) as $carga)
                                        <tr class="transition hover:bg-fondo-card/45">
                                            <td class="px-4 py-3 font-black text-titulo">{{ $carga['codigo'] }} · {{ $carga['nombre'] }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $carga['mananas'] }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $carga['tardes'] }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $carga['noches'] }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $carga['apoyos'] }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $carga['descansos'] }}</td>
                                            <td class="px-4 py-3 text-xs font-bold text-titulo">{{ $carga['jornadas_trabajadas'] }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-titulo">{{ $carga['horas_totales'] }}h</td>
                                            <td class="px-4 py-3 text-xs font-bold text-apoyo">{{ $carga['diferencia_promedio'] ?? 0 }}h</td>
                                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $carga['clase_estado'] ?? 'bg-fondo-hover text-apoyo' }}">{{ $carga['estado'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-10 text-center text-sm font-bold text-apoyo">Genere una planilla para analizar la carga laboral.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if ($vistaPlanilla === 'alertas')
                    <section class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-black text-titulo">Alertas de planilla</h3>
                                <p class="mt-1 text-xs font-bold text-apoyo">Validaciones de cobertura, duplicidad, descanso y sobrecarga.</p>
                            </div>
                            <span class="rounded-full bg-fondo-card px-3 py-1 text-[11px] font-black text-titulo">{{ count($alertasPlanilla ?? []) }} alerta(s)</span>
                        </div>
                        <div class="mt-4 grid gap-2">
                            @forelse(($alertasPlanilla ?? []) as $alerta)
                                <article class="rounded-2xl border p-3 {{ $alerta['clase_visual'] ?? 'border-borde-suave bg-fondo-card/35 text-titulo' }}">
                                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-wider">{{ $alerta['tipo'] ?? 'Alerta' }} · {{ $alerta['nivel'] ?? 'INFO' }}</p>
                                            <p class="mt-1 text-sm font-bold">{{ $alerta['mensaje'] ?? 'Sin detalle' }}</p>
                                        </div>
                                        <p class="text-[11px] font-bold">{{ $alerta['dia'] ?? '' }} {{ $alerta['fecha'] ?? '' }}</p>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-card/30 p-8 text-center">
                                    <i class="ph-bold ph-shield-check text-3xl text-estado-exito"></i>
                                    <p class="mt-2 text-sm font-black text-titulo">Sin alertas críticas</p>
                                    <p class="mt-1 text-xs font-bold text-apoyo">La planilla generada cumple la cobertura esperada.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endif
            @else
                <section class="grid gap-4 xl:grid-cols-[1fr_360px]">
                    <div class="rounded-2xl border border-borde-suave bg-fondo-card/30 p-5">
                        <h3 class="text-lg font-black text-titulo">
                            {{ $tipoPlanilla === 'salud' ? 'Planilla del personal de salud' : 'Planilla administrativa' }}
                        </h3>
                        <p class="mt-1 max-w-3xl text-sm font-bold text-apoyo">
                            {{ $tipoPlanilla === 'salud'
                                ? 'Médicos, psicología, nutrición, fisioterapia y otros roles asistenciales manejan jornadas simples por horario institucional.'
                                : 'El personal administrativo mantiene jornadas por horario institucional, sin rotación compleja como enfermería.' }}
                        </p>
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            <article class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
                                <i class="ph-bold ph-clock text-2xl text-titulo"></i>
                                <p class="mt-3 text-sm font-black text-titulo">Jornada por horario</p>
                                <p class="mt-1 text-xs font-bold text-apoyo">Asignación por día y hora.</p>
                            </article>
                            <article class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
                                <i class="ph-bold ph-user-list text-2xl text-titulo"></i>
                                <p class="mt-3 text-sm font-black text-titulo">Filtro por rol</p>
                                <p class="mt-1 text-xs font-bold text-apoyo">Consulta por personal y función.</p>
                            </article>
                            <article class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
                                <i class="ph-bold ph-files text-2xl text-titulo"></i>
                                <p class="mt-3 text-sm font-black text-titulo">Control institucional</p>
                                <p class="mt-1 text-xs font-bold text-apoyo">Base para reportes posteriores.</p>
                            </article>
                        </div>
                    </div>
                    <aside class="rounded-2xl border border-dashed border-borde-suave bg-fondo-card/25 p-4">
                        <p class="text-xs font-black uppercase tracking-wider text-apoyo">Horario general</p>
                        <p class="mt-2 text-sm font-bold text-titulo">Este tipo se gestiona desde la vista institucional inferior y los filtros generales.</p>
                    </aside>
                </section>
            @endif
        </section>

        {{-- CALENDARIO / HORARIOS GENERALES --}}
        <section class="grid gap-4 xl:grid-cols-[1fr_340px]">
            <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-base font-black text-titulo">Calendario institucional</h2>
                        <p class="mt-1 text-xs font-bold text-apoyo">{{ $datosCalendario['titulo'] ?? 'Sin periodo seleccionado' }}</p>
                    </div>
                    <span class="rounded-full bg-fondo-card px-3 py-1 text-[11px] font-black text-apoyo">
                        Vista {{ ucfirst($vistaCalendario) }}
                    </span>
                </div>

                @if ($vistaCalendario === 'dia')
                    <div class="space-y-2">
                        @forelse(($datosCalendario['filas'] ?? []) as $fila)
                            @php
                                $usuario = $fila['usuario'];
                                $estado = $fila['estado'];
                                $rol = $fila['rol'];
                                $asignacion = $fila['asignacion'];
                                $claseEstado = $estadoClases[$estado] ?? 'border-borde-suave bg-fondo-hover text-apoyo';
                            @endphp
                            <article class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-3 transition hover:bg-fondo-card/55">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-titulo">
                                            {{ $usuario->nombres ?? '' }} {{ $usuario->ap_paterno ?? '' }}
                                        </p>
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $rol['class'] }}">{{ $rol['label'] }}</span>
                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-black {{ $claseEstado }}">{{ $estado }}</span>
                                        </div>
                                    </div>
                                    <div class="text-left md:text-right">
                                        <p class="text-xs font-black text-titulo">{{ $asignacion->turno?->nombre ?? 'Sin turno' }}</p>
                                        <p class="text-[11px] font-bold text-apoyo">{{ $asignacion->area_nombre ?? 'Sin área' }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-2xl border border-dashed border-borde-suave p-8 text-center text-sm font-bold text-apoyo">Sin personal para mostrar.</p>
                        @endforelse
                    </div>
                @elseif ($vistaCalendario === 'semana')
                    <div class="grid gap-2 md:grid-cols-7">
                        @foreach (($datosCalendario['dias'] ?? []) as $dia)
                            <div class="min-h-[260px] rounded-2xl border {{ ($dia['es_hoy'] ?? false) ? 'border-borde-focus bg-estado-peligroBg' : 'border-borde-suave bg-fondo-card/30' }} p-2">
                                <div class="mb-2">
                                    <p class="text-[10px] font-black uppercase text-apoyo">{{ $dia['nombre'] ?? '' }}</p>
                                    <p class="text-sm font-black text-titulo">{{ $dia['numero'] ?? '' }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    @forelse(($dia['eventos'] ?? []) as $evento)
                                        <article class="rounded-xl border border-borde-suave bg-fondo-panel p-2 shadow-sm">
                                            <p class="truncate text-[11px] font-black text-titulo">{{ $evento['titulo'] ?? 'Evento' }}</p>
                                            <p class="truncate text-[10px] font-bold text-apoyo">{{ $evento['subtitulo'] ?? '' }}</p>
                                            <p class="mt-1 text-[10px] font-bold text-apoyo">{{ $evento['hora'] ?? '' }}</p>
                                        </article>
                                    @empty
                                        <p class="rounded-xl border border-dashed border-borde-suave p-3 text-center text-[10px] font-bold text-apoyo">Sin eventos</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($vistaCalendario === 'mes')
                    <div class="grid grid-cols-2 gap-2 md:grid-cols-7">
                        @foreach (($datosCalendario['dias'] ?? []) as $dia)
                            <button type="button" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')"
                                class="min-h-[120px] rounded-2xl border p-2 text-left transition hover:border-borde-focus {{ ($dia['es_hoy'] ?? false) ? 'border-borde-focus bg-estado-peligroBg' : 'border-borde-suave bg-fondo-card/30' }} {{ !($dia['es_mes'] ?? true) ? 'opacity-50' : '' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-black text-titulo">{{ $dia['numero'] }}</p>
                                    @if (($dia['conflictos'] ?? 0) > 0)
                                        <span class="rounded-full bg-estado-peligroBg px-2 py-0.5 text-[9px] font-black text-estado-peligro">{{ $dia['conflictos'] }}</span>
                                    @endif
                                </div>
                                <p class="mt-3 text-[11px] font-bold text-apoyo">{{ $dia['asignados'] ?? 0 }} asignados</p>
                                <p class="text-[11px] font-bold text-apoyo">{{ $dia['cubiertos'] ?? 0 }} turnos</p>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
                        @foreach (($datosCalendario['meses'] ?? []) as $mes)
                            <button type="button" wire:click="seleccionarFecha('{{ $mes['fecha'] }}')"
                                class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4 text-left transition hover:border-borde-focus">
                                <p class="text-sm font-black text-titulo">{{ $mes['mes'] }}</p>
                                <p class="mt-2 text-xs font-bold text-apoyo">{{ $mes['asignados'] }} asignados</p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-fondo-panel">
                                    <div class="h-full rounded-full bg-boton-principal" style="width: {{ $mes['cobertura'] }}%"></div>
                                </div>
                                <p class="mt-2 text-[11px] font-bold text-apoyo">{{ $mes['cobertura'] }}% cobertura</p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
                <h3 class="text-base font-black text-titulo">Guía de lectura</h3>
                <p class="mt-1 text-xs font-bold text-apoyo">El calendario general muestra administración y salud por horarios simples. La planilla de enfermería usa simulación rotativa.</p>
                <div class="mt-4 space-y-2 text-[11px] font-bold text-apoyo">
                    <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-boton-principal"></span>Turno regular</p>
                    <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-boton-acento"></span>Apoyo / cobertura</p>
                    <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-estado-exitoBg"></span>Cobertura correcta</p>
                    <p><span class="mr-2 inline-block h-2.5 w-2.5 rounded-full bg-estado-peligroBg"></span>Conflicto o alerta</p>
                </div>
            </aside>
        </section>

        {{-- MODAL DE NUEVA ASIGNACIÓN / SELECCIÓN DE USUARIO --}}
        @if ($modalAbierto)
            <div class="fixed inset-0 z-[70] flex items-center justify-center bg-titulo/40 p-4 backdrop-blur-sm">
                <div class="max-h-[86vh] w-full max-w-4xl flex flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-panel shadow-2xl">
                    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-borde-suave p-5">
                        <div>
                            <h2 class="text-lg font-black text-titulo">Nueva asignación institucional</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">Seleccione el personal. El registro definitivo se conectará al flujo de asignación correspondiente.</p>
                        </div>
                        <button type="button" wire:click="cerrarModal"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-titulo transition hover:bg-boton-acento hover:text-inverso">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>

                    <div class="p-5 overflow-y-auto">
                        @if ($usuarioSeleccionadoData)
                            <div class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4">
                                <div class="flex flex-col gap-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Personal seleccionado</p>
                                            <p class="mt-2 text-base font-black text-titulo">{{ $usuarioSeleccionadoData->nombres }} {{ $usuarioSeleccionadoData->ap_paterno }}</p>
                                            <p class="text-xs font-bold text-apoyo">{{ $usuarioSeleccionadoData->correo }}</p>
                                        </div>
                                        <button type="button" wire:click="$set('usuarioSeleccionado', null)" class="text-[11px] font-bold text-boton-acento hover:text-boton-acento/80 transition">
                                            Cambiar personal
                                        </button>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <livewire:admin.personal-institucional.partials.personal-institucional-horarios 
                                            :usuario-id="$usuarioSeleccionadoData->cod_usu" 
                                            :abrir-formulario-inicial="true"
                                            wire:key="horario-modal-{{ $usuarioSeleccionadoData->cod_usu }}" 
                                        />
                                    </div>

                                    <div class="mt-2 flex justify-end border-t border-borde-suave pt-4">
                                        <button type="button" wire:click="cerrarModal" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black uppercase tracking-wide text-inverso transition hover:bg-boton-principal/90">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="relative">
                                <i class="ph-bold ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                <input type="search" wire:model.live.debounce.300ms="busquedaModal"
                                    class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/40 pl-9 pr-3 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus"
                                    placeholder="Buscar personal para asignar...">
                            </div>

                            <div class="mt-4 max-h-[420px] space-y-2 overflow-y-auto pr-1">
                                @forelse ($personalModal as $usuario)
                                    <button type="button" wire:click="seleccionarUsuario('{{ $usuario->cod_usu }}')"
                                        class="w-full rounded-2xl border border-borde-suave bg-fondo-card/35 p-3 text-left transition hover:border-borde-focus hover:bg-fondo-card">
                                        <p class="text-sm font-black text-titulo">{{ $usuario->nombres }} {{ $usuario->ap_paterno }}</p>
                                        <p class="text-xs font-bold text-apoyo">{{ $usuario->correo }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach ($usuario->roles as $rol)
                                                <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-[9px] font-black text-apoyo">{{ $rol->name }}</span>
                                            @endforeach
                                        </div>
                                    </button>
                                @empty
                                    <p class="rounded-2xl border border-dashed border-borde-suave p-8 text-center text-sm font-bold text-apoyo">No se encontraron usuarios disponibles.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
