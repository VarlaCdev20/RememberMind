<div class="space-y-5" x-data="{ masFiltros: false, menuAcciones: null }">
    @php
        $vistaTitulo = match ($tab) {
            'historial' => 'Residentes históricos',
            'pendientes' => 'Pendientes de valoración',
            'interconsultas' => 'Interconsultas',
            default => 'En residencia',
        };

        $vistaDescripcion = match ($tab) {
            'historial' => 'Expedientes fuera del seguimiento operativo. Consulta en modo lectura.',
            'pendientes' => 'Residentes que requieren valoración médica antes de continuar el flujo institucional.',
            'interconsultas' => 'Residentes con notas de interconsulta activas.',
            default => 'Residentes actualmente bajo seguimiento médico institucional.',
        };

        $toneClasses = [
            'residentes' => [
                'card' => 'border-borde bg-fondo-card',
                'icon' => 'bg-kpi-residentesBg text-kpi-residentes',
                'value' => 'text-kpi-residentes',
            ],
            'salud' => [
                'card' => 'border-estado-exitoBorde bg-fondo-card',
                'icon' => 'bg-kpi-saludBg text-kpi-salud',
                'value' => 'text-kpi-salud',
            ],
            'cognitivo' => [
                'card' => 'border-borde bg-fondo-card',
                'icon' => 'bg-kpi-cognitivoBg text-kpi-cognitivo',
                'value' => 'text-kpi-cognitivo',
            ],
            'alertas' => [
                'card' => 'border-estado-advertenciaBorde bg-fondo-card',
                'icon' => 'bg-kpi-alertasBg text-kpi-alertas',
                'value' => 'text-kpi-alertas',
            ],
        ];

        $hayFiltros = $busqueda !== '' || count($filtrosActivos) > 0 || $orden !== 'nombre';
    @endphp

    {{-- ENCABEZADO --}}
    <section class="overflow-hidden rounded-[28px] border border-borde bg-fondo-card shadow-card">
        <div class="flex flex-col gap-4 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-modulo-saludFondo text-modulo-salud shadow-sm">
                    <i class="ph-fill ph-users-three text-2xl"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-modulo-salud">
                            Seguimiento médico
                        </span>

                        <span
                            class="rounded-full border border-borde bg-fondo-panel px-2.5 py-1 text-[10px] font-black text-apoyo">
                            {{ $vistaTitulo }}
                        </span>
                    </div>

                    <h1 class="text-2xl font-black tracking-tight text-titulo sm:text-3xl">
                        Residentes
                    </h1>

                    <p class="mt-1 max-w-3xl text-sm font-semibold leading-relaxed text-apoyo">
                        {{ $vistaDescripcion }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.medico.dashboard') }}" class="rm-btn-secondary h-10 px-4">
                    <i class="ph-bold ph-arrow-left"></i>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>

                <button type="button" wire:click="$refresh" class="rm-btn-secondary h-10 w-10 !px-0"
                    title="Actualizar información">
                    <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                </button>
            </div>
        </div>
    </section>

    {{-- NAVEGACIÓN CONCEPTUAL: POBLACIÓN VS BANDEJAS --}}
    <section class="grid gap-4 xl:grid-cols-[1.15fr_1fr]">
        <div class="rounded-2xl border border-borde bg-fondo-card p-3 shadow-card">
            <div class="mb-2 flex items-center gap-2 px-1">
                <i class="ph-bold ph-users-three text-modulo-salud"></i>
                <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">
                    Población de residentes
                </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <button type="button" wire:click="setTab('activos')" class="group flex items-center justify-between rounded-xl border px-4 py-3 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.99]
                        {{ $tab === 'activos'
    ? 'border-modulo-salud bg-modulo-saludFondo shadow-sm'
    : 'border-borde bg-fondo-panel hover:border-estado-exitoBorde' }}">
                    <span class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl
                            {{ $tab === 'activos'
    ? 'bg-modulo-salud text-inverso'
    : 'bg-fondo-card text-modulo-salud' }}">
                            <i class="ph-bold ph-user-check"></i>
                        </span>

                        <span>
                            <span class="block text-xs font-black text-titulo">En residencia</span>
                            <span class="mt-0.5 block text-[9px] font-semibold text-apoyo">Seguimiento operativo
                                actual</span>
                        </span>
                    </span>

                    <span class="rounded-full bg-estado-exitoBg px-2.5 py-1 text-[10px] font-black text-estado-exito">
                        {{ $cntActivos }}
                    </span>
                </button>

                <button type="button" wire:click="setTab('historial')" class="group flex items-center justify-between rounded-xl border px-4 py-3 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.99]
                        {{ $tab === 'historial'
    ? 'border-borde-fuerte bg-fondo-panelFuerte shadow-sm'
    : 'border-borde bg-fondo-panel hover:border-borde-fuerte' }}">
                    <span class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card text-apoyo">
                            <i class="ph-bold ph-archive"></i>
                        </span>

                        <span>
                            <span class="block text-xs font-black text-titulo">Históricos / egresados</span>
                            <span class="mt-0.5 block text-[9px] font-semibold text-apoyo">Expedientes fuera de
                                operación</span>
                        </span>
                    </span>

                    <span
                        class="rounded-full bg-estado-neutralBg px-2.5 py-1 text-[10px] font-black text-estado-neutral">
                        {{ $cntHistorial }}
                    </span>
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-borde bg-fondo-card p-3 shadow-card">
            <div class="mb-2 flex items-center gap-2 px-1">
                <i class="ph-bold ph-clipboard-text text-modulo-cognitivoTexto"></i>
                <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">
                    Bandejas médicas
                </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-2">
                <button type="button" wire:click="setTab('pendientes')" class="flex items-center justify-between rounded-xl border px-4 py-3 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.99]
                        {{ $tab === 'pendientes'
    ? 'border-estado-advertenciaBorde bg-estado-advertenciaBg shadow-sm'
    : 'border-borde bg-fondo-panel hover:border-estado-advertenciaBorde' }}">
                    <span class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl
                            {{ $tab === 'pendientes'
    ? 'bg-estado-advertencia text-inverso'
    : 'bg-fondo-card text-estado-advertencia' }}">
                            <i class="ph-bold ph-hourglass"></i>
                        </span>

                        <span>
                            <span class="block text-xs font-black text-titulo">Por valorar</span>
                            <span class="mt-0.5 block text-[9px] font-semibold text-apoyo">Trabajo médico
                                pendiente</span>
                        </span>
                    </span>

                    @if($cntPendientes > 0)
                        <span class="rounded-full bg-estado-advertencia px-2.5 py-1 text-[10px] font-black text-inverso">
                            {{ $cntPendientes }}
                        </span>
                    @endif
                </button>

                <button type="button" wire:click="setTab('interconsultas')" class="flex items-center justify-between rounded-xl border px-4 py-3 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.99]
                        {{ $tab === 'interconsultas'
    ? 'border-borde bg-modulo-cognitivoFondo shadow-sm'
    : 'border-borde bg-fondo-panel hover:border-borde-fuerte' }}">
                    <span class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl
                            {{ $tab === 'interconsultas'
    ? 'bg-modulo-cognitivo text-inverso'
    : 'bg-fondo-card text-modulo-cognitivoTexto' }}">
                            <i class="ph-bold ph-arrows-left-right"></i>
                        </span>

                        <span>
                            <span class="block text-xs font-black text-titulo">Interconsultas</span>
                            <span class="mt-0.5 block text-[9px] font-semibold text-apoyo">Seguimiento
                                interdisciplinario</span>
                        </span>
                    </span>

                    @if($cntInterconsultas > 0)
                        <span
                            class="rounded-full bg-modulo-cognitivoFondo px-2.5 py-1 text-[10px] font-black text-modulo-cognitivoTexto">
                            {{ $cntInterconsultas }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </section>

    {{-- MÉTRICAS CONTEXTUALES --}}
    <section class="grid gap-3 sm:grid-cols-2 {{ count($metricasVista) >= 4 ? 'xl:grid-cols-4' : 'xl:grid-cols-3' }}">
        @foreach($metricasVista as $metrica)
            @php
                $tone = $toneClasses[$metrica['tone']] ?? $toneClasses['residentes'];
            @endphp

            <article
                class="rounded-2xl border p-4 shadow-card transition duration-200 hover:-translate-y-0.5 hover:shadow-cardHover {{ $tone['card'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">
                            {{ $metrica['label'] }}
                        </p>
                        <p class="mt-1 text-3xl font-black {{ $tone['value'] }}">
                            {{ $metrica['value'] }}
                        </p>
                    </div>

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tone['icon'] }}">
                        <i class="ph-bold {{ $metrica['icon'] }} text-lg"></i>
                    </span>
                </div>

                <p class="mt-2 text-[10px] font-semibold leading-relaxed text-apoyo">
                    {{ $metrica['help'] }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- BUSCADOR Y FILTROS --}}
    <section class="overflow-hidden rounded-2xl border border-borde bg-fondo-card shadow-card">
        <div class="p-4 sm:p-5">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                {{-- Buscador funcional --}}
                <div class="relative min-w-0 flex-1">
                    <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>

                    <input wire:model.live.debounce.300ms="busqueda" type="search" maxlength="120" autocomplete="off"
                        placeholder="Buscar por nombre, apellidos, CI, habitación o cama..."
                        class="w-full rounded-xl border border-input-borde bg-input-bg py-3 pl-10 pr-20 text-sm font-semibold text-input-texto outline-none transition placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">

                    <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-2">
                        <i wire:loading wire:target="busqueda"
                            class="ph-bold ph-spinner animate-spin text-boton-acento"></i>

                        @if($busqueda !== '')
                            <button type="button" wire:click="limpiarBusqueda"
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-apoyo transition hover:bg-fondo-hover hover:text-titulo"
                                title="Limpiar búsqueda">
                                <i class="ph-bold ph-x"></i>
                            </button>
                        @endif
                    </div>
                </div>

                @if($tab === 'activos')
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="toggleFiltroAlertas"
                                    class="inline-flex h-11 items-center gap-2 rounded-xl border px-3 text-[10px] font-black uppercase tracking-wider transition duration-200
                                            {{ $filtroAlertas === 'ABIERTAS'
                    ? 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia'
                    : 'border-borde bg-fondo-panel text-apoyo hover:border-estado-advertenciaBorde hover:text-titulo' }}">
                                    <i class="ph-bold ph-warning-circle"></i>
                                    Con alertas
                                </button>

                                <button type="button" wire:click="toggleFiltroControl"
                                    class="inline-flex h-11 items-center gap-2 rounded-xl border px-3 text-[10px] font-black uppercase tracking-wider transition duration-200
                                            {{ $filtroControl === 'PROXIMO'
                    ? 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito'
                    : 'border-borde bg-fondo-panel text-apoyo hover:border-estado-exitoBorde hover:text-titulo' }}">
                                    <i class="ph-bold ph-calendar-check"></i>
                                    Control 7 días
                                </button>

                                <button type="button" x-on:click="masFiltros = !masFiltros"
                                    class="inline-flex h-11 items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-3 text-[10px] font-black uppercase tracking-wider text-apoyo transition hover:border-borde-fuerte hover:text-titulo">
                                    <i class="ph-bold ph-funnel"></i>
                                    Más filtros
                                    <i class="ph-bold ph-caret-down transition" x-bind:class="masFiltros ? 'rotate-180' : ''"></i>
                                </button>
                            </div>
                @else
                    <div class="w-full xl:w-64">
                        <select wire:model.live="filtroEstado"
                            class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-3 text-xs font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">
                            <option value="">Todos los estados</option>
                            @foreach($estadosDisponibles as $codigo => $label)
                                <option value="{{ $codigo }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            @if($tab === 'activos')
                <div x-show="masFiltros" x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="-translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                    x-transition:leave="transition duration-150 ease-in"
                    x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-2 opacity-0"
                    class="mt-4 rounded-2xl border border-borde bg-fondo-panel p-4">
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">
                                Estado institucional
                            </label>

                            <select wire:model.live="filtroEstado"
                                class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">
                                <option value="">Todos</option>
                                @foreach($estadosDisponibles as $codigo => $label)
                                    <option value="{{ $codigo }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">
                                Riesgo cognitivo
                            </label>

                            <select wire:model.live="filtroRiesgo"
                                class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">
                                <option value="">Todos</option>
                                @foreach($riesgosDisponibles as $riesgo)
                                    <option value="{{ $riesgo }}">{{ $riesgo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">
                                Dependencia funcional
                            </label>

                            <select wire:model.live="filtroDependencia"
                                class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">
                                <option value="">Todas</option>
                                @foreach($dependenciasDisponibles as $dependencia)
                                    <option value="{{ $dependencia }}">{{ $dependencia }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">
                                Habitación
                            </label>

                            <select wire:model.live="filtroHabitacion"
                                class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">
                                <option value="">Todas</option>
                                @foreach($habitacionesDisponibles as $habitacion)
                                    <option value="{{ $habitacion->cod_habitacion }}">
                                        {{ $habitacion->codigo ?: $habitacion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Filtros activos + orden --}}
        <div
            class="flex flex-col gap-3 border-t border-borde bg-fondo-panel/60 px-4 py-3 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-wrap items-center gap-2">
                @if($busqueda !== '')
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        <i class="ph-bold ph-magnifying-glass text-boton-acento"></i>
                        “{{ \Illuminate\Support\Str::limit($busqueda, 30) }}”
                        <button type="button" wire:click="limpiarBusqueda" class="text-apoyo hover:text-boton-acento">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </span>
                @endif

                @foreach($filtrosActivos as $filtro)
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        <span class="text-apoyo">{{ $filtro['label'] }}:</span>
                        {{ $filtro['value'] }}

                        <button type="button" wire:click="limpiarFiltro('{{ $filtro['key'] }}')"
                            class="text-apoyo transition hover:text-boton-acento" title="Quitar filtro">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </span>
                @endforeach

                @if(!$hayFiltros)
                    <span class="text-[10px] font-semibold text-apoyo">
                        Sin filtros adicionales
                    </span>
                @else
                    <button type="button" wire:click="limpiarFiltros"
                        class="inline-flex items-center gap-1 text-[10px] font-black text-boton-acento transition hover:underline">
                        <i class="ph-bold ph-trash"></i>
                        Limpiar filtros
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <label class="text-[9px] font-black uppercase tracking-wider text-apoyo">
                    Ordenar
                </label>

                <select wire:model.live="orden"
                    class="rounded-xl border border-input-borde bg-input-bg px-3 py-2 text-[10px] font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus">
                    <option value="nombre">Nombre A–Z</option>
                    @if($tab === 'activos')
                        <option value="alertas">Alertas primero</option>
                    @endif
                    <option value="reciente">Actualización reciente</option>
                </select>
            </div>
        </div>
    </section>

    {{-- RESULTADOS --}}
    <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-borde px-4 py-3 sm:px-5">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-titulo">
                    {{ $vistaTitulo }}
                </h2>
                <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                    {{ $pacientes->total() }} resultado{{ $pacientes->total() === 1 ? '' : 's' }}
                    @if($hayFiltros)
                        con los criterios actuales
                    @endif
                </p>
            </div>

            <div wire:loading.flex
                wire:target="busqueda,filtroEstado,filtroAlertas,filtroControl,filtroRiesgo,filtroDependencia,filtroHabitacion,orden,setTab"
                class="items-center gap-2 text-[10px] font-bold text-boton-acento">
                <i class="ph-bold ph-spinner animate-spin"></i>
                Actualizando resultados
            </div>
        </div>

        @if($pacientes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead
                        class="border-b border-tabla-headerBorde bg-tabla-header text-[9px] font-black uppercase tracking-widest text-tabla-headerTexto">
                        <tr>
                            <th class="w-3 px-3 py-3"></th>
                            <th class="px-4 py-3">Residente</th>

                            @if($tab === 'historial')
                                <th class="px-4 py-3">Estado de cierre</th>
                                <th class="px-4 py-3">Último registro</th>
                                <th class="px-4 py-3">Ingreso</th>
                            @elseif($tab === 'pendientes')
                                <th class="px-4 py-3">Estado del proceso</th>
                                <th class="px-4 py-3">Ingreso</th>
                                <th class="px-4 py-3">Último registro</th>
                            @elseif($tab === 'interconsultas')
                                <th class="px-4 py-3">Estado / ubicación</th>
                                <th class="px-4 py-3">Última interconsulta</th>
                                <th class="px-4 py-3">Seguimiento</th>
                            @else
                                <th class="px-4 py-3">Estado / ubicación</th>
                                <th class="px-4 py-3">Clínica</th>
                                <th class="px-4 py-3">Seguimiento</th>
                                <th class="px-4 py-3">Próximo control</th>
                            @endif

                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-tabla-rowBorde">
                        @foreach($pacientes as $pac)
                                        @php
                                            $sv = $ultimosSignos[$pac->cod_am] ?? null;
                                            $nota = $ultimasNotas[$pac->cod_am] ?? null;
                                            $interconsulta = $ultimasInterconsultas[$pac->cod_am] ?? null;
                                            $meds = (int) ($cntMedicacion[$pac->cod_am] ?? 0);
                                            $alertas = $alertasPagina[$pac->cod_am] ?? collect();
                                            $alertasCount = $alertas->count();
                                            $hayAlertaAlta = $alertas->contains(
                                                fn($alerta) => in_array(
                                                    strtoupper((string) $alerta->nivel),
                                                    ['ALTO', 'CRITICO'],
                                                    true
                                                )
                                            );

                                            $barthel = $barthelPagina[$pac->cod_am] ?? null;
                                            $cognicion = $cognicionPagina[$pac->cod_am] ?? null;
                                            $control = $proximosControles[$pac->cod_am] ?? null;

                                            $estado = strtoupper((string) ($pac->estado?->estado ?? ''));

                                            $estadoClase = match ($estado) {
                                                'ACTIVO',
                                                'ADMITIDO',
                                                'ASIGNADO',
                                                'EN_SEGUIMIENTO_ACTIVO'
                                                => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',

                                                'OBSERVADO',
                                                'SEGUIMIENTO_ESPECIAL',
                                                'PENDIENTE_VALORACION_MEDICA'
                                                => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',

                                                'DERIVADO',
                                                'TRASLADADO'
                                                => 'border-estado-infoBorde bg-estado-infoBg text-estado-info',

                                                default
                                                => 'border-estado-neutralBorde bg-estado-neutralBg text-estado-neutral',
                                            };

                                            $riesgo = strtoupper((string) ($cognicion?->nivel_riesgo ?? ''));
                                            $riesgoClase = match ($riesgo) {
                                                'ALTO', 'CRITICO'
                                                => 'border-estado-peligroBorde bg-estado-peligroBg text-estado-peligro',

                                                'MEDIO', 'MODERADO'
                                                => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',

                                                'BAJO', 'NORMAL', 'SIN RIESGO', 'SIN_RIESGO'
                                                => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',

                                                default
                                                => 'border-borde bg-modulo-cognitivoFondo text-modulo-cognitivoTexto',
                                            };

                                            $dependencia = strtoupper((string) ($barthel?->nivel_dependencia ?? ''));
                                            $dependenciaClase = match (true) {
                                                str_contains($dependencia, 'TOTAL'),
                                                str_contains($dependencia, 'SEVERA')
                                                => 'border-estado-peligroBorde bg-estado-peligroBg text-estado-peligro',

                                                str_contains($dependencia, 'MODERADA')
                                                => 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia',

                                                str_contains($dependencia, 'LEVE')
                                                => 'border-estado-infoBorde bg-estado-infoBg text-estado-info',

                                                str_contains($dependencia, 'INDEPENDIENTE'),
                                                str_contains($dependencia, 'ESCASA')
                                                => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',

                                                default
                                                => 'border-borde bg-estado-neutralBg text-estado-neutral',
                                            };

                                            $indicadorFila = match ($tab) {
                                                'historial' => 'bg-estado-neutral',
                                                'pendientes' => 'bg-estado-advertencia',
                                                'interconsultas' => 'bg-modulo-cognitivo',
                                                default => $alertasCount > 0
                                                    ? ($hayAlertaAlta ? 'bg-estado-peligro' : 'bg-estado-advertencia')
                                                    : ($control ? 'bg-estado-info' : 'bg-estado-neutral'),
                                            };

                                            $estadoMostrar = $tab === 'historial' && $pac->archivado_en
                                                ? 'Archivado'
                                                : $pac->estado_humano;

                                            if ($tab === 'historial' && $pac->archivado_en) {
                                                $estadoClase = 'border-estado-neutralBorde bg-estado-neutralBg text-estado-neutral';
                                            }

                                            $ultimaFecha = collect([
                                                $nota?->fecha,
                                                $sv?->fecha,
                                            ])->filter()->sortDesc()->first();
                                        @endphp

                                        <tr class="group transition-colors duration-150 hover:bg-tabla-rowHover">
                                            <td class="px-3 py-4 align-top">
                                                <span class="mt-1 block h-2.5 w-2.5 rounded-full {{ $indicadorFila }}"></span>
                                            </td>

                                            <td class="px-4 py-4 align-top">
                                                <div class="flex items-start gap-3">
                                                    <div
                                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-kpi-residentesBg text-xs font-black text-kpi-residentes">
                                                        {{ mb_strtoupper(
                                mb_substr($pac->nombres ?? 'R', 0, 1) .
                                mb_substr($pac->ap_paterno ?? '', 0, 1)
                            ) }}
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="max-w-[220px] truncate text-sm font-black text-titulo">
                                                            {{ $pac->nombre_completo }}
                                                        </p>

                                                        <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                                            {{ $pac->edad_texto }}
                                                            @if($pac->ci)
                                                                · CI {{ $pac->ci }}
                                                            @endif
                                                        </p>

                                                        @if($tab === 'historial')
                                                            <p class="mt-1 text-[9px] font-bold text-estado-neutral">
                                                                <i class="ph-bold ph-lock-key"></i>
                                                                Expediente de solo lectura
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            @if($tab === 'historial')
                                                <td class="px-4 py-4 align-top">
                                                    <span
                                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase }}">
                                                        {{ $estadoMostrar }}
                                                    </span>

                                                    @if($pac->archivado_en)
                                                        <p class="mt-1.5 text-[9px] font-semibold text-apoyo">
                                                            Archivado {{ $pac->archivado_en->format('d/m/Y') }}
                                                        </p>
                                                    @endif
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    @if($ultimaFecha)
                                                        <p class="text-xs font-black text-titulo">
                                                            {{ \Carbon\Carbon::parse($ultimaFecha)->format('d/m/Y') }}
                                                        </p>
                                                        <p class="mt-1 text-[9px] font-semibold text-apoyo">
                                                            {{ \Carbon\Carbon::parse($ultimaFecha)->diffForHumans() }}
                                                        </p>
                                                    @else
                                                        <span class="text-xs font-semibold text-apoyo">Sin registros</span>
                                                    @endif
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <p class="text-xs font-black text-titulo">
                                                        {{ $pac->fecha_ing?->format('d/m/Y') ?? 'No registrada' }}
                                                    </p>
                                                </td>

                                            @elseif($tab === 'pendientes')
                                                <td class="px-4 py-4 align-top">
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase }}">
                                                        <i class="ph-bold ph-hourglass"></i>
                                                        {{ $pac->estado_humano }}
                                                    </span>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <p class="text-xs font-black text-titulo">
                                                        {{ $pac->fecha_ing?->format('d/m/Y') ?? 'No registrada' }}
                                                    </p>
                                                    <p class="mt-1 text-[9px] font-semibold text-apoyo">
                                                        {{ $pac->procedencia_ingreso ?: 'Procedencia no registrada' }}
                                                    </p>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    @if($nota)
                                                        <p class="text-[10px] font-black text-titulo">{{ $nota->tipo_nota }}</p>
                                                        <p class="mt-1 max-w-[230px] truncate text-[9px] font-semibold text-apoyo">
                                                            {{ $nota->valoracion ?: $nota->observaciones ?: 'Sin detalle' }}
                                                        </p>
                                                        <p class="mt-1 text-[9px] font-semibold text-meta">
                                                            {{ $nota->fecha?->format('d/m/Y') }}
                                                        </p>
                                                    @else
                                                        <span class="text-xs font-semibold text-apoyo">Sin notas médicas</span>
                                                    @endif
                                                </td>

                                            @elseif($tab === 'interconsultas')
                                                <td class="px-4 py-4 align-top">
                                                    <span
                                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase }}">
                                                        {{ $pac->estado_humano }}
                                                    </span>

                                                    <p class="mt-2 text-[9px] font-semibold text-apoyo">
                                                        <i class="ph-bold ph-bed"></i>
                                                        {{ $pac->ubicacion_texto }}
                                                    </p>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    @if($interconsulta)
                                                        <div class="max-w-[280px]">
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="rounded-md bg-modulo-cognitivoFondo px-2 py-0.5 text-[9px] font-black text-modulo-cognitivoTexto">
                                                                    INTERCONSULTA
                                                                </span>
                                                                <span class="text-[9px] font-semibold text-meta">
                                                                    {{ $interconsulta->fecha?->format('d/m/Y') }}
                                                                </span>
                                                            </div>

                                                            <p class="mt-1.5 line-clamp-2 text-[10px] font-semibold leading-relaxed text-apoyo">
                                                                {{ $interconsulta->valoracion ?: $interconsulta->plan ?: $interconsulta->observaciones ?: 'Sin resumen registrado.' }}
                                                            </p>
                                                        </div>
                                                    @else
                                                        <span class="text-xs font-semibold text-apoyo">Sin detalle disponible</span>
                                                    @endif
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <div class="flex flex-col gap-1.5">
                                                        @if($alertasCount > 0)
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border border-estado-advertenciaBorde bg-estado-advertenciaBg px-2 py-1 text-[9px] font-black text-estado-advertencia">
                                                                <i class="ph-bold ph-warning-circle"></i>
                                                                {{ $alertasCount }} alerta{{ $alertasCount === 1 ? '' : 's' }}
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border border-estado-exitoBorde bg-estado-exitoBg px-2 py-1 text-[9px] font-black text-estado-exito">
                                                                <i class="ph-bold ph-check-circle"></i>
                                                                Sin alertas abiertas
                                                            </span>
                                                        @endif

                                                        <span class="text-[9px] font-semibold text-apoyo">
                                                            <i class="ph-bold ph-pill"></i>
                                                            {{ $meds }} medicación{{ $meds === 1 ? '' : 'es' }}
                                                            activa{{ $meds === 1 ? '' : 's' }}
                                                        </span>
                                                    </div>
                                                </td>

                                            @else
                                                <td class="px-4 py-4 align-top">
                                                    <span
                                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase }}">
                                                        {{ $pac->estado_humano }}
                                                    </span>

                                                    <p class="mt-2 max-w-[190px] truncate text-[9px] font-semibold text-apoyo">
                                                        <i class="ph-bold ph-bed"></i>
                                                        {{ $pac->ubicacion_texto }}
                                                    </p>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <div class="flex flex-col gap-1.5">
                                                        @if($cognicion)
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border px-2 py-1 text-[9px] font-black {{ $riesgoClase }}">
                                                                <i class="ph-bold ph-brain"></i>
                                                                {{ $cognicion->nivel_riesgo ?: $cognicion->resultado_cualitativo ?: 'Evaluado' }}
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border border-borde bg-estado-neutralBg px-2 py-1 text-[9px] font-black text-estado-neutral">
                                                                <i class="ph-bold ph-brain"></i>
                                                                Cognición sin evaluar
                                                            </span>
                                                        @endif

                                                        @if($barthel)
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border px-2 py-1 text-[9px] font-black {{ $dependenciaClase }}">
                                                                <i class="ph-bold ph-person-simple-walk"></i>
                                                                Barthel {{ $barthel->indice_barthel ?? '—' }}
                                                                @if($barthel->nivel_dependencia)
                                                                    · {{ $barthel->nivel_dependencia }}
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border border-borde bg-estado-neutralBg px-2 py-1 text-[9px] font-black text-estado-neutral">
                                                                <i class="ph-bold ph-person-simple-walk"></i>
                                                                Barthel sin evaluar
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <div class="flex flex-col gap-1.5">
                                                        @if($alertasCount > 0)
                                                                        <span class="inline-flex w-fit items-center gap-1 rounded-full border
                                                                                            {{ $hayAlertaAlta
                                                            ? 'border-estado-peligroBorde bg-estado-peligroBg text-estado-peligro'
                                                            : 'border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia' }}
                                                                                            px-2 py-1 text-[9px] font-black">
                                                                            <i class="ph-bold ph-warning-circle"></i>
                                                                            {{ $alertasCount }} alerta{{ $alertasCount === 1 ? '' : 's' }}
                                                                        </span>
                                                        @else
                                                            <span
                                                                class="inline-flex w-fit items-center gap-1 rounded-full border border-estado-exitoBorde bg-estado-exitoBg px-2 py-1 text-[9px] font-black text-estado-exito">
                                                                <i class="ph-bold ph-check-circle"></i>
                                                                Sin alertas abiertas
                                                            </span>
                                                        @endif

                                                        <span class="inline-flex w-fit items-center gap-1 text-[9px] font-semibold text-apoyo">
                                                            <i class="ph-bold ph-pill text-boton-acento"></i>
                                                            {{ $meds }} medicación{{ $meds === 1 ? '' : 'es' }}
                                                            activa{{ $meds === 1 ? '' : 's' }}
                                                        </span>

                                                        @if($sv)
                                                            <span class="inline-flex w-fit items-center gap-1 text-[9px] font-semibold text-apoyo">
                                                                <i class="ph-bold ph-heartbeat text-modulo-salud"></i>
                                                                Signos {{ $sv->fecha?->diffForHumans() }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    @if($control)
                                                        <div class="rounded-xl border border-estado-infoBorde bg-estado-infoBg px-3 py-2">
                                                            <p class="text-[10px] font-black text-estado-info">
                                                                <i class="ph-bold ph-calendar-check"></i>
                                                                {{ $control->fecha?->format('d/m/Y') }}
                                                            </p>

                                                            <p class="mt-0.5 text-[9px] font-semibold text-apoyo">
                                                                {{ $control->hora ?: 'Hora no registrada' }}
                                                            </p>
                                                        </div>
                                                    @else
                                                        <div class="rounded-xl border border-borde bg-estado-neutralBg px-3 py-2">
                                                            <p class="text-[10px] font-black text-estado-neutral">Sin control programado</p>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endif

                                            <td class="px-4 py-4 align-top">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button type="button" wire:click="abrirFicha('{{ $pac->cod_am }}')"
                                                        class="rm-btn-primary h-8 !px-3 !text-[9px]" title="Abrir ficha clínica integrada">
                                                        <i class="ph-bold ph-folder-open"></i>
                                                        Ficha
                                                    </button>

                                                    @if($tab !== 'historial' && in_array($estado, ['ACTIVO', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO', 'OBSERVADO', 'SEGUIMIENTO_ESPECIAL'], true))
                                                        <div class="relative">
                                                            <button type="button"
                                                                x-on:click="menuAcciones = menuAcciones === '{{ $pac->cod_am }}' ? null : '{{ $pac->cod_am }}'"
                                                                class="rm-btn-secondary h-8 w-8 !px-0" title="Más acciones">
                                                                <i class="ph-bold ph-dots-three-vertical"></i>
                                                            </button>

                                                            <div x-show="menuAcciones === '{{ $pac->cod_am }}'"
                                                                x-on:click.outside="menuAcciones = null"
                                                                x-transition:enter="transition duration-150 ease-out"
                                                                x-transition:enter-start="translate-y-1 scale-95 opacity-0"
                                                                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                                                class="absolute right-0 z-30 mt-2 w-52 overflow-hidden rounded-xl border border-borde bg-fondo-card p-1.5 shadow-panel">
                                                                @can('atenciones.crear')
                                                                    <button type="button" wire:click="nuevaNota('{{ $pac->cod_am }}')"
                                                                        x-on:click="menuAcciones = null"
                                                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-[10px] font-bold text-titulo transition hover:bg-fondo-hover">
                                                                        <i class="ph-bold ph-note-pencil text-modulo-salud"></i>
                                                                        Nueva nota de evolución
                                                                    </button>
                                                                @endcan

                                                                @can('signos_vitales.crear')
                                                                    <button type="button" wire:click="nuevosSignos('{{ $pac->cod_am }}')"
                                                                        x-on:click="menuAcciones = null"
                                                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-[10px] font-bold text-titulo transition hover:bg-fondo-hover">
                                                                        <i class="ph-bold ph-heartbeat text-boton-acento"></i>
                                                                        Registrar signos vitales
                                                                    </button>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span
                                                            class="inline-flex h-8 items-center gap-1 rounded-lg border border-borde bg-estado-neutralBg px-2 text-[9px] font-black text-estado-neutral">
                                                            <i class="ph-bold ph-lock-key"></i>
                                                            Solo lectura
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-borde px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <p class="text-[10px] font-semibold text-apoyo">
                    Mostrando {{ $pacientes->firstItem() }}–{{ $pacientes->lastItem() }}
                    de {{ $pacientes->total() }} resultados
                </p>

                <div>
                    {{ $pacientes->links() }}
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-apoyo">
                    @if($hayFiltros)
                        <i class="ph-bold ph-magnifying-glass text-3xl"></i>
                    @elseif($tab === 'historial')
                        <i class="ph-bold ph-archive text-3xl"></i>
                    @elseif($tab === 'pendientes')
                        <i class="ph-bold ph-check-circle text-3xl"></i>
                    @elseif($tab === 'interconsultas')
                        <i class="ph-bold ph-arrows-left-right text-3xl"></i>
                    @else
                        <i class="ph-bold ph-users-three text-3xl"></i>
                    @endif
                </div>

                <h3 class="mt-4 text-base font-black text-titulo">
                    @if($hayFiltros)
                        No encontramos coincidencias
                    @elseif($tab === 'historial')
                        No hay expedientes históricos
                    @elseif($tab === 'pendientes')
                        No hay valoraciones médicas pendientes
                    @elseif($tab === 'interconsultas')
                        No hay interconsultas activas
                    @else
                        No hay residentes en seguimiento
                    @endif
                </h3>

                <p class="mt-1 max-w-md text-sm font-semibold text-apoyo">
                    @if($hayFiltros)
                        Revise el texto de búsqueda o quite alguno de los filtros aplicados.
                    @elseif($tab === 'historial')
                        Los residentes egresados, trasladados, derivados o archivados aparecerán aquí.
                    @elseif($tab === 'pendientes')
                        La bandeja está al día.
                    @elseif($tab === 'interconsultas')
                        No existen notas de tipo interconsulta activas para residentes actuales.
                    @else
                        No existen residentes activos para mostrar en esta vista.
                    @endif
                </p>

                @if($hayFiltros)
                    <button type="button" wire:click="limpiarFiltros" class="rm-btn-secondary mt-4">
                        <i class="ph-bold ph-trash"></i>
                        Limpiar filtros
                    </button>
                @endif
            </div>
        @endif
    </section>

    {{-- MODALES DEL FLUJO MÉDICO --}}
    @livewire('clinica.nota-evolucion-medica-modal')
    @livewire('clinica.registro-signos-vitales-modal')
    @livewire('valoraciones.valoracion-barthel-modal')
</div>