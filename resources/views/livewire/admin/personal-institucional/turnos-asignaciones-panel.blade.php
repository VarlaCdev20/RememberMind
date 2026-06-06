<div class="space-y-4">
    @php
        $estadoClases = [
            'ACTIVO' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'EN TURNO' => 'border-emerald-300 bg-emerald-100 text-emerald-800',
            'DISPONIBLE' => 'border-sky-200 bg-sky-50 text-sky-700',
            'OCUPADO' => 'border-amber-200 bg-amber-50 text-amber-700',
            'SIN HORARIO' => 'border-stone-200 bg-stone-50 text-stone-600',
            'FINALIZADO' => 'border-slate-200 bg-slate-100 text-slate-600',
            'SUSPENDIDO' => 'border-rose-200 bg-rose-50 text-rose-700',
            'CONFLICTO' => 'border-red-300 bg-red-100 text-red-800',
        ];
    @endphp

    <section class="overflow-hidden rounded-2xl border border-borde bg-white shadow-sm">
        <div class="h-1 bg-gradient-to-r from-[#D9795F] via-[#E9A05F] to-[#3F7D5A]"></div>
        <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-boton-acento/20 bg-boton-acento/10 text-boton-acento">
                    <i class="ph-fill ph-calendar-check text-2xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.16em] text-boton-acento">Gestión del Sistema</span>
                    <h1 class="text-xl font-black text-titulo">Horarios y Asignaciones</h1>
                    <p class="mt-0.5 text-xs font-semibold text-apoyo">Planificación visual de turnos, áreas y disponibilidad del personal institucional.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="$refresh" wire:loading.attr="disabled"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-borde bg-white text-apoyo shadow-sm transition hover:bg-fondo-hover hover:text-titulo"
                    title="Actualizar">
                    <i class="ph-bold ph-arrows-clockwise text-base" wire:loading.class="animate-spin" wire:target="$refresh"></i>
                </button>
                <button type="button" wire:click="exportarCalendario"
                    class="flex h-9 items-center gap-1.5 rounded-lg border border-borde bg-white px-3 text-xs font-bold text-apoyo shadow-sm transition hover:bg-fondo-hover hover:text-titulo">
                    <i class="ph-bold ph-download-simple text-base"></i>
                    Exportar calendario
                </button>
                <button type="button" wire:click="abrirNuevaAsignacion"
                    class="rm-btn-primary flex h-9 items-center gap-1.5 rounded-lg px-3 text-xs">
                    <i class="ph-bold ph-plus text-base"></i>
                    Nueva asignación
                </button>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach([
            ['icon' => 'ph-calendar-check', 'label' => 'Personal con horario', 'value' => $stats['con_horario'], 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
            ['icon' => 'ph-calendar-x', 'label' => 'Personal sin horario', 'value' => $stats['sin_horario'], 'class' => 'border-stone-200 bg-stone-50 text-stone-600'],
            ['icon' => 'ph-clock-user', 'label' => 'En turno hoy', 'value' => $stats['en_turno_hoy'], 'class' => 'border-[#F2CFC4] bg-[#FDF5F2] text-[#B85C45]'],
            ['icon' => 'ph-user-check', 'label' => 'Disponibles hoy', 'value' => $stats['disponibles_hoy'], 'class' => 'border-sky-200 bg-sky-50 text-sky-700'],
            ['icon' => 'ph-warning-circle', 'label' => 'Turnos por cubrir', 'value' => $stats['turnos_por_cubrir'], 'class' => 'border-amber-200 bg-amber-50 text-amber-700'],
            ['icon' => 'ph-stack', 'label' => 'Asignaciones activas', 'value' => $stats['asignaciones_activas'], 'class' => 'border-violet-200 bg-violet-50 text-violet-700'],
        ] as $card)
            <article class="rounded-xl border p-3 shadow-sm {{ $card['class'] }}">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-white/70">
                        <i class="ph-fill {{ $card['icon'] }} text-base"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xl font-black leading-none">{{ $card['value'] }}</p>
                        <p class="mt-1 truncate text-[10px] font-black uppercase tracking-wide">{{ $card['label'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-borde bg-white p-3 shadow-sm">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
            <div class="relative sm:col-span-2">
                <i class="ph-bold ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                <input type="search" wire:model.live.debounce.300ms="busqueda"
                    class="h-9 w-full rounded-lg border border-borde bg-fondo-card pl-9 pr-3 text-xs font-semibold text-titulo outline-none focus:border-boton-acento"
                    placeholder="Buscar personal...">
            </div>
            <select wire:model.live="filtroTipo" class="h-9 rounded-lg border border-borde bg-fondo-card px-2 text-xs font-semibold text-titulo">
                <option value="">Todo personal</option>
                <option value="admin">Administrativo</option>
                <option value="salud">Salud</option>
            </select>
            <select wire:model.live="filtroRol" class="h-9 rounded-lg border border-borde bg-fondo-card px-2 text-xs font-semibold text-titulo">
                <option value="">Todos los roles</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol }}">{{ $rol }}</option>
                @endforeach
            </select>
            <select wire:model.live="filtroArea" class="h-9 rounded-lg border border-borde bg-fondo-card px-2 text-xs font-semibold text-titulo">
                <option value="">Todas las áreas</option>
                @foreach($areas as $area)
                    <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
                @endforeach
            </select>
            <select wire:model.live="filtroTurno" class="h-9 rounded-lg border border-borde bg-fondo-card px-2 text-xs font-semibold text-titulo">
                <option value="">Todos los turnos</option>
                @foreach($turnos as $turno)
                    <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>
                @endforeach
            </select>
            <select wire:model.live="filtroEstado" class="h-9 rounded-lg border border-borde bg-fondo-card px-2 text-xs font-semibold text-titulo">
                <option value="">Todos los estados</option>
                <option value="disponible">Disponible</option>
                <option value="ocupado">Ocupado</option>
                <option value="sin_horario">Sin horario</option>
                <option value="suspendido">Suspendido</option>
                <option value="en_turno">En turno</option>
                <option value="conflicto">Conflicto</option>
            </select>
            <button type="button" wire:click="limpiarFiltros"
                class="flex h-9 items-center justify-center gap-1.5 rounded-lg border border-borde bg-fondo-hover px-3 text-xs font-bold text-apoyo transition hover:text-titulo">
                <i class="ph-bold ph-broom"></i> Limpiar
            </button>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-borde bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-borde bg-fondo-hover/40 p-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <button type="button" wire:click="moverPeriodo(-1)" class="flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-white text-apoyo hover:text-titulo">
                    <i class="ph-bold ph-caret-left"></i>
                </button>
                <button type="button" wire:click="irHoy" class="h-8 rounded-lg border border-borde bg-white px-3 text-xs font-bold text-titulo">Hoy</button>
                <button type="button" wire:click="moverPeriodo(1)" class="flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-white text-apoyo hover:text-titulo">
                    <i class="ph-bold ph-caret-right"></i>
                </button>
                <input type="date" wire:model.live="fechaSeleccionada" class="h-8 rounded-lg border border-borde bg-white px-2 text-xs font-bold text-titulo">
            </div>

            <h2 class="text-sm font-black text-titulo">{{ $datosCalendario['titulo'] }}</h2>

            <div class="flex overflow-x-auto rounded-lg border border-borde bg-white p-1">
                @foreach(['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes', 'anio' => 'Año'] as $vista => $label)
                    <button type="button" wire:click="cambiarVista('{{ $vista }}')"
                        class="h-7 whitespace-nowrap rounded-md px-3 text-[11px] font-bold transition {{ $vistaCalendario === $vista ? 'bg-boton-acento text-white shadow-sm' : 'text-apoyo hover:bg-fondo-hover hover:text-titulo' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div wire:loading.flex wire:target="busqueda,filtroTipo,filtroRol,filtroArea,filtroTurno,filtroEstado,vistaCalendario,fechaSeleccionada,moverPeriodo"
            class="items-center justify-center gap-2 border-b border-borde bg-white p-3 text-xs font-bold text-apoyo">
            <i class="ph-bold ph-spinner animate-spin text-boton-acento"></i> Actualizando calendario...
        </div>

        @if($vistaCalendario === 'dia')
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="bg-fondo-tabla text-[10px] font-black uppercase tracking-wider text-apoyo">
                        <tr>
                            <th class="px-4 py-3">Personal</th>
                            <th class="px-4 py-3">Rol / Tipo</th>
                            <th class="px-4 py-3">Área</th>
                            <th class="px-4 py-3">Turno / Horario</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde/60">
                        @forelse($datosCalendario['filas'] as $fila)
                            @php
                                $asignacion = $fila['asignacion'];
                            @endphp
                            <tr class="transition hover:bg-fondo-hover/40">
                                <td class="px-4 py-3">
                                    <p class="text-xs font-black text-titulo">{{ $fila['usuario']->name }}</p>
                                    <p class="text-[10px] font-mono text-apoyo">{{ $fila['usuario']->cod_usu }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-md border px-2 py-0.5 text-[10px] font-bold {{ $fila['rol']['class'] }}">{{ $fila['rol']['label'] }}</span>
                                    <p class="mt-1 max-w-[220px] truncate text-[10px] text-apoyo">{{ $fila['usuario']->roles->pluck('name')->implode(', ') ?: 'Sin rol asignado' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs font-semibold text-titulo">{{ $asignacion?->area?->nombre ?? 'Sin área asignada' }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-xs font-bold text-titulo">{{ $asignacion?->turno?->nombre ?? 'Sin turno' }}</p>
                                    <p class="text-[10px] text-apoyo">
                                        {{ $asignacion?->turno?->hora_inicio ? substr($asignacion->turno->hora_inicio, 0, 5) : '--:--' }}
                                        -
                                        {{ $asignacion?->turno?->hora_fin ? substr($asignacion->turno->hora_fin, 0, 5) : '--:--' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wide {{ $estadoClases[$fila['estado']] ?? $estadoClases['SIN HORARIO'] }}">
                                        {{ $fila['estado'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1">
                                        <button type="button" wire:click="seleccionarUsuario('{{ $fila['usuario']->cod_usu }}')"
                                            class="flex h-8 items-center gap-1 rounded-lg border border-borde px-2 text-[10px] font-bold text-apoyo transition hover:border-boton-acento hover:text-boton-acento">
                                            <i class="ph-bold ph-calendar-plus"></i> Gestionar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-xs font-bold text-apoyo">Sin personal para los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="space-y-2 p-3 md:hidden">
                @forelse($datosCalendario['filas'] as $fila)
                    @php
                        $asignacion = $fila['asignacion'];
                    @endphp
                    <button type="button" wire:click="seleccionarUsuario('{{ $fila['usuario']->cod_usu }}')"
                        class="w-full rounded-xl border border-borde bg-white p-3 text-left shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-black text-titulo">{{ $fila['usuario']->name }}</p>
                                <p class="text-[9px] font-mono text-apoyo">{{ $fila['usuario']->cod_usu }}</p>
                            </div>
                            <span class="inline-flex flex-shrink-0 rounded-full border px-2 py-0.5 text-[8px] font-black uppercase {{ $estadoClases[$fila['estado']] ?? $estadoClases['SIN HORARIO'] }}">{{ $fila['estado'] }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-bold {{ $fila['rol']['class'] }}">{{ $fila['rol']['label'] }}</span>
                            <span class="truncate text-[10px] font-bold text-titulo">{{ $asignacion?->turno?->nombre ?? 'Sin turno asignado' }}</span>
                        </div>
                        <p class="mt-1 truncate text-[9px] font-semibold text-apoyo">{{ $asignacion?->area?->nombre ?? 'Sin área asignada' }}</p>
                    </button>
                @empty
                    <p class="rounded-xl border border-dashed border-borde p-8 text-center text-xs font-bold text-apoyo">Sin personal para los filtros seleccionados.</p>
                @endforelse
            </div>
        @elseif($vistaCalendario === 'semana')
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-[1260px] w-full table-fixed border-collapse">
                    <thead>
                        <tr class="bg-fondo-tabla">
                            <th class="w-36 border-b border-r border-borde px-3 py-3 text-left text-[10px] font-black uppercase tracking-wider text-apoyo">Bloque</th>
                            @foreach($datosCalendario['dias'] as $dia)
                                <th class="border-b border-r border-borde px-2 py-2 text-center last:border-r-0 {{ $dia['es_hoy'] ? 'bg-boton-acento/10' : '' }}">
                                    <button type="button" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')" class="w-full">
                                        <span class="block text-[10px] font-black uppercase text-apoyo">{{ $dia['nombre'] }}</span>
                                        <span class="text-sm font-black {{ $dia['es_hoy'] ? 'text-boton-acento' : 'text-titulo' }}">{{ $dia['numero'] }}</span>
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($datosCalendario['bloques'] as $bloque)
                            <tr>
                                <th class="border-b border-r border-borde bg-fondo-hover/50 px-3 py-3 align-top text-[10px] font-black text-titulo">{{ $bloque }}</th>
                                @foreach($datosCalendario['dias'] as $dia)
                                    <td class="h-28 border-b border-r border-borde p-1.5 align-top last:border-r-0 {{ $dia['es_hoy'] ? 'bg-boton-acento/[0.03]' : '' }}">
                                        <div class="space-y-1.5">
                                            @forelse($dia['bloques'][$bloque] as $evento)
                                                <button type="button" wire:click="seleccionarUsuario('{{ $evento['asignacion']->cod_usu }}')"
                                                    class="w-full rounded-lg border p-2 text-left shadow-sm transition hover:-translate-y-0.5 {{ $evento['rol']['class'] }}">
                                                    <span class="block truncate text-[10px] font-black">{{ $evento['asignacion']->usuario?->name }}</span>
                                                    <span class="block truncate text-[9px] font-semibold opacity-80">{{ $evento['asignacion']->area?->nombre }}</span>
                                                    @if($evento['estado'] === 'CONFLICTO')
                                                        <span class="mt-1 inline-flex rounded bg-red-600 px-1 py-0.5 text-[8px] font-black text-white">CONFLICTO</span>
                                                    @endif
                                                </button>
                                            @empty
                                                <span class="block rounded-lg border border-dashed border-borde p-2 text-center text-[9px] font-bold text-apoyo/60">Sin cobertura</span>
                                            @endforelse
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 p-3 md:hidden">
                @foreach($datosCalendario['dias'] as $dia)
                    <article class="rounded-xl border border-borde bg-white p-3">
                        <button type="button" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')" class="mb-2 flex w-full items-center justify-between">
                            <span class="text-xs font-black uppercase text-titulo">{{ $dia['nombre'] }} {{ $dia['numero'] }}</span>
                            <span class="text-[10px] font-bold text-boton-acento">{{ $dia['eventos']->count() }} asignaciones</span>
                        </button>
                        <div class="space-y-1.5">
                            @forelse($dia['eventos'] as $evento)
                                <button type="button" wire:click="seleccionarUsuario('{{ $evento['asignacion']->cod_usu }}')"
                                    class="flex w-full items-center justify-between rounded-lg border p-2 text-left {{ $evento['rol']['class'] }}">
                                    <span class="min-w-0">
                                        <span class="block truncate text-[11px] font-black">{{ $evento['asignacion']->usuario?->name }}</span>
                                        <span class="block truncate text-[9px] font-semibold">{{ $evento['asignacion']->turno?->nombre }} · {{ $evento['asignacion']->area?->nombre }}</span>
                                    </span>
                                    <span class="ml-2 text-[8px] font-black">{{ $evento['estado'] }}</span>
                                </button>
                            @empty
                                <p class="rounded-lg border border-dashed border-borde p-3 text-center text-[10px] font-bold text-apoyo">Sin asignaciones</p>
                            @endforelse
                        </div>
                    </article>
                @endforeach
            </div>
        @elseif($vistaCalendario === 'mes')
            <div class="hidden md:block">
                <div class="grid grid-cols-7 border-b border-borde bg-fondo-tabla">
                    @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)
                        <div class="border-r border-borde px-2 py-2 text-center text-[10px] font-black uppercase tracking-wider text-apoyo last:border-r-0">{{ $dia }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7">
                    @foreach($datosCalendario['dias'] as $dia)
                        <button type="button" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')"
                            class="min-h-24 border-b border-r border-borde p-1.5 text-left transition hover:bg-fondo-hover last:border-r-0 {{ !$dia['es_mes'] ? 'bg-fondo-hover/50 opacity-50' : '' }} {{ $dia['es_hoy'] ? 'ring-2 ring-inset ring-boton-acento/40' : '' }}">
                            <span class="text-[10px] font-black {{ $dia['es_hoy'] ? 'text-boton-acento' : 'text-titulo' }}">{{ $dia['numero'] }}</span>
                            <span class="mt-1 block rounded bg-emerald-50 px-1 py-0.5 text-[8px] font-bold text-emerald-700">{{ $dia['asignados'] }} asignados</span>
                            <span class="mt-1 block rounded bg-sky-50 px-1 py-0.5 text-[8px] font-bold text-sky-700">{{ $dia['cubiertos'] }} cubiertos</span>
                            @if($dia['faltantes'] > 0)
                                <span class="mt-1 block rounded bg-amber-50 px-1 py-0.5 text-[8px] font-bold text-amber-700">{{ $dia['faltantes'] }} faltantes</span>
                            @endif
                            @if($dia['conflictos'] > 0)
                                <span class="mt-1 block rounded bg-red-100 px-1 py-0.5 text-[8px] font-black text-red-700">{{ $dia['conflictos'] }} conflictos</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="grid gap-2 p-3 md:hidden">
                @foreach($datosCalendario['dias']->where('es_mes', true) as $dia)
                    <button type="button" wire:click="seleccionarFecha('{{ $dia['fecha'] }}')"
                        class="flex items-center gap-3 rounded-xl border border-borde bg-white p-3 text-left shadow-sm {{ $dia['es_hoy'] ? 'ring-2 ring-boton-acento/40' : '' }}">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-fondo-hover text-sm font-black {{ $dia['es_hoy'] ? 'text-boton-acento' : 'text-titulo' }}">{{ $dia['numero'] }}</span>
                        <span class="grid min-w-0 flex-1 grid-cols-3 gap-1 text-center text-[8px] font-black uppercase">
                            <span class="rounded bg-emerald-50 px-1 py-1 text-emerald-700">{{ $dia['asignados'] }} asignados</span>
                            <span class="rounded bg-sky-50 px-1 py-1 text-sky-700">{{ $dia['cubiertos'] }} cubiertos</span>
                            <span class="rounded {{ $dia['conflictos'] > 0 ? 'bg-red-100 text-red-700' : 'bg-amber-50 text-amber-700' }}">{{ $dia['conflictos'] > 0 ? $dia['conflictos'].' conflictos' : $dia['faltantes'].' faltantes' }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        @else
            <div class="grid gap-3 p-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($datosCalendario['meses'] as $mes)
                    <button type="button" wire:click="seleccionarFecha('{{ $mes['fecha'] }}')"
                        class="rounded-xl border border-borde bg-white p-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-boton-acento">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-titulo">{{ $mes['mes'] }}</h3>
                            <span class="text-xs font-black text-boton-acento">{{ $mes['cobertura'] }}%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-fondo-hover">
                            <div class="h-full rounded-full bg-boton-acento" style="width: {{ $mes['cobertura'] }}%"></div>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[10px] font-bold text-apoyo">
                            <span>{{ $mes['asignados'] }} asignados</span>
                            <span class="{{ $mes['conflictos'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $mes['conflictos'] }} conflictos</span>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    @if($modalAbierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-3 backdrop-blur-sm">
            <div class="flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-borde bg-fondo-card shadow-2xl">
                <div class="flex items-center justify-between border-b border-borde bg-white px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento"><i class="ph-fill ph-calendar-plus text-lg"></i></span>
                        <div>
                            <h3 class="text-sm font-black text-titulo">{{ $usuarioSeleccionadoData ? 'Gestionar asignaciones' : 'Nueva asignación' }}</h3>
                            <p class="text-[10px] font-semibold text-apoyo">{{ $usuarioSeleccionadoData ? $usuarioSeleccionadoData->name : 'Busca y selecciona personal institucional' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModal" class="flex h-8 w-8 items-center justify-center rounded-lg border border-borde text-apoyo hover:bg-rose-50 hover:text-rose-700"><i class="ph-bold ph-x"></i></button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    @if(!$usuarioSeleccionadoData)
                        <div class="mx-auto max-w-3xl space-y-3">
                            <div class="relative">
                                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                <input type="search" wire:model.live.debounce.250ms="busquedaModal"
                                    class="h-10 w-full rounded-xl border border-borde bg-white pl-9 pr-3 text-sm font-semibold text-titulo outline-none focus:border-boton-acento"
                                    placeholder="Buscar usuario por nombre o cod_usu...">
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @forelse($personalModal as $usuario)
                                    @php
                                        $sinRol = $usuario->roles->isEmpty();
                                        $bloqueado = in_array((string) $usuario->estado, ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true);
                                        $conHorario = $usuario->asignacionesTurno->isNotEmpty();
                                    @endphp
                                    <button type="button" wire:click="seleccionarUsuario('{{ $usuario->cod_usu }}')"
                                        class="rounded-xl border border-borde bg-white p-3 text-left shadow-sm transition hover:border-boton-acento hover:bg-boton-acento/[0.03]">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-black text-titulo">{{ $usuario->name }}</p>
                                                <p class="text-[10px] font-mono text-apoyo">{{ $usuario->cod_usu }}</p>
                                            </div>
                                            <span class="rounded-full border px-2 py-0.5 text-[8px] font-black {{ $bloqueado ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">{{ $usuario->estado }}</span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <span class="rounded bg-fondo-hover px-1.5 py-0.5 text-[9px] font-bold text-apoyo">{{ $usuario->personalSalud ? 'SALUD' : 'ADMINISTRATIVO' }}</span>
                                            <span class="rounded px-1.5 py-0.5 text-[9px] font-bold {{ $conHorario ? 'bg-sky-50 text-sky-700' : 'bg-stone-100 text-stone-600' }}">{{ $conHorario ? 'CON HORARIO' : 'SIN HORARIO' }}</span>
                                            @if($sinRol)<span class="rounded bg-red-100 px-1.5 py-0.5 text-[9px] font-black text-red-700">SIN ROL</span>@endif
                                        </div>
                                    </button>
                                @empty
                                    <div class="rounded-xl border border-dashed border-borde p-8 text-center text-xs font-bold text-apoyo sm:col-span-2">No se encontró personal institucional.</div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        @php
                            $sinRol = $usuarioSeleccionadoData->roles->isEmpty();
                            $bloqueado = in_array((string) $usuarioSeleccionadoData->estado, ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true);
                            $conHorario = $usuarioSeleccionadoData->asignacionesTurno->where('estado', 'ACTIVA')->isNotEmpty();
                        @endphp
                        <div class="mb-4 grid gap-2 rounded-xl border border-borde bg-white p-3 sm:grid-cols-2 lg:grid-cols-5">
                            <div><p class="text-[9px] font-black uppercase text-apoyo">cod_usu</p><p class="text-xs font-mono font-bold text-titulo">{{ $usuarioSeleccionadoData->cod_usu }}</p></div>
                            <div><p class="text-[9px] font-black uppercase text-apoyo">Rol</p><p class="truncate text-xs font-bold {{ $sinRol ? 'text-red-700' : 'text-titulo' }}">{{ $usuarioSeleccionadoData->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}</p></div>
                            <div><p class="text-[9px] font-black uppercase text-apoyo">Tipo</p><p class="text-xs font-bold text-titulo">{{ $usuarioSeleccionadoData->personalSalud ? 'Salud' : 'Administrativo' }}</p></div>
                            <div><p class="text-[9px] font-black uppercase text-apoyo">Estado usuario</p><p class="text-xs font-bold {{ $bloqueado ? 'text-red-700' : 'text-emerald-700' }}">{{ $usuarioSeleccionadoData->estado }}</p></div>
                            <div><p class="text-[9px] font-black uppercase text-apoyo">Estado horario</p><p class="text-xs font-bold {{ $conHorario ? 'text-sky-700' : 'text-stone-600' }}">{{ $conHorario ? 'Con horario activo' : 'Sin horario activo' }}</p></div>
                        </div>

                        @if($sinRol)
                            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700"><i class="ph-bold ph-warning-circle"></i> Este usuario no tiene rol. No se permitirá guardar una asignación.</div>
                        @elseif($bloqueado)
                            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700"><i class="ph-bold ph-prohibit"></i> No se puede asignar horario a personal INACTIVO o SUSPENDIDO.</div>
                        @elseif($conHorario)
                            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs font-bold text-amber-700"><i class="ph-bold ph-info"></i> Este usuario ya tiene horario activo. Puedes editarlo o finalizarlo; una edición finalizará el registro anterior y creará uno nuevo.</div>
                        @endif

                        <div class="mb-3 flex justify-start">
                            <button type="button" wire:click="$set('usuarioSeleccionado', null)" class="flex h-8 items-center gap-1 rounded-lg border border-borde px-3 text-[10px] font-bold text-apoyo hover:text-titulo"><i class="ph-bold ph-arrow-left"></i> Cambiar usuario</button>
                        </div>

                        <livewire:admin.personal-institucional.partials.personal-institucional-horarios
                            :usuario-id="$usuarioSeleccionado"
                            :wire:key="'turnos-global-' . $usuarioSeleccionado" />
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('mostrarAlerta', (event) => {
        const data = event[0] ?? event;
        Swal.fire({
            title: data.title ?? '',
            text: data.message ?? '',
            icon: data.type ?? 'info',
            confirmButtonColor: '#3F7D5A',
        });
    });
</script>
@endscript
