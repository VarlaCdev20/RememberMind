@php
    $estadisticas = array_merge([
        'total' => 0,
        'activos' => 0,
        'inactivos' => 0,
        'suspendidos' => 0,
        'retirados' => 0,
        'salud' => 0,
        'admin' => 0,
        'sistema' => 0,
        'medicos' => 0,
        'enfermeros' => 0,
        'psicologos' => 0,
        'fisioterapeutas' => 0,
        'nutricionistas' => 0,
        'en_turno' => 0,
        'fuera_turno' => 0,
        'incidencias' => 0,
    ], $estadisticas ?? []);

    $chartData = array_merge([
        'area_labels' => [],
        'area_data' => [],
        'estado_labels' => [],
        'estado_data' => [],
        'turno_labels' => [],
        'turno_data' => [],
        'salud_labels' => [],
        'salud_data' => [],
        'disp_labels' => [],
        'disp_data' => [],
    ], $chartData ?? []);

    $usuarios = $usuarios ?? collect();
    $personalResumen = $personalResumen ?? collect();
    $personalResumenTotal = $personalResumenTotal ?? 0;
    $personalResumenMostrados = $personalResumenMostrados ?? $personalResumen->count();
    $interpretacionReportes = $interpretacionReportes ?? [];
    $vistaListadoResumen = $vistaListadoResumen ?? 'tarjetas';
    $rubroResumen = $rubroResumen ?? 'institucional';

    $mostrarDirectorio = in_array($tabActiva, ['salud', 'admin'], true);

    $tituloDirectorio = match ($tabActiva) {
        'salud' => 'Directorio de Salud',
        'admin' => 'Directorio Administrativo',
        default => 'Directorio Institucional',
    };

    $iconoDirectorio = match ($tabActiva) {
        'salud' => 'ph-stethoscope',
        'admin' => 'ph-desktop',
        default => 'ph-list-dashes',
    };

    $claseIconoDirectorio = match ($tabActiva) {
        'salud' => 'bg-estado-infoBg text-estado-info border-estado-infoBorde',
        'admin' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde',
        default => 'bg-boton-acento/10 text-boton-acento border-borde',
    };

    $metricasResumen = [
        [
            'titulo' => 'Total de personal',
            'valor' => $estadisticas['total'],
            'descripcion' => 'Personal institucional registrado.',
            'icono' => 'ph-users-three',
            'fondo' => 'bg-boton-acento/10',
            'texto' => 'text-boton-acento',
        ],
        [
            'titulo' => 'Activos',
            'valor' => $estadisticas['activos'],
            'descripcion' => 'Habilitados actualmente.',
            'icono' => 'ph-check-circle',
            'fondo' => 'bg-estado-exitoBg',
            'texto' => 'text-estado-exito',
        ],
        [
            'titulo' => 'En turno',
            'valor' => $estadisticas['en_turno'],
            'descripcion' => 'Asignados según horario.',
            'icono' => 'ph-clock-user',
            'fondo' => 'bg-estado-infoBg',
            'texto' => 'text-estado-info',
        ],
        [
            'titulo' => 'Fuera de turno',
            'valor' => $estadisticas['fuera_turno'],
            'descripcion' => 'Sin turno activo actual.',
            'icono' => 'ph-bed',
            'fondo' => 'bg-fondo-hover',
            'texto' => 'text-apoyo',
        ],
    ];

    $tarjetasSalud = [
        [
            'titulo' => 'Médicos',
            'valor' => $estadisticas['medicos'],
            'icono' => 'ph-stethoscope',
            'fondo' => 'bg-modulo-salud-fondo',
            'texto' => 'text-modulo-salud',
            'hover' => 'group-hover:bg-modulo-salud',
        ],
        [
            'titulo' => 'Enfermeros',
            'valor' => $estadisticas['enfermeros'],
            'icono' => 'ph-first-aid',
            'fondo' => 'bg-modulo-salud-fondo',
            'texto' => 'text-modulo-salud',
            'hover' => 'group-hover:bg-modulo-salud',
        ],
        [
            'titulo' => 'Psicólogos',
            'valor' => $estadisticas['psicologos'],
            'icono' => 'ph-brain',
            'fondo' => 'bg-modulo-cognitivo-fondo',
            'texto' => 'text-modulo-cognitivo-texto',
            'hover' => 'group-hover:bg-modulo-cognitivo',
        ],
        [
            'titulo' => 'Fisioterapeuta',
            'valor' => $estadisticas['fisioterapeutas'],
            'icono' => 'ph-person-arms-spread',
            'fondo' => 'bg-modulo-salud-fondo',
            'texto' => 'text-modulo-salud',
            'hover' => 'group-hover:bg-modulo-salud',
        ],
        [
            'titulo' => 'Nutricionistas',
            'valor' => $estadisticas['nutricionistas'],
            'icono' => 'ph-apple-logo',
            'fondo' => 'bg-modulo-voluntarios-fondo',
            'texto' => 'text-modulo-voluntarios-texto',
            'hover' => 'group-hover:bg-modulo-voluntarios',
        ],
    ];

    $tarjetasAdmin = [
        [
            'titulo' => 'Administrativos',
            'valor' => $estadisticas['admin'],
            'icono' => 'ph-desktop',
            'fondo' => 'bg-modulo-administrativo-fondo',
            'texto' => 'text-modulo-administrativo',
        ],
        [
            'titulo' => 'Administradores',
            'valor' => $estadisticas['sistema'],
            'icono' => 'ph-shield-star',
            'fondo' => 'bg-estado-infoBg',
            'texto' => 'text-estado-info',
        ],
        [
            'titulo' => 'Activos',
            'valor' => $estadisticas['activos'],
            'icono' => 'ph-check-circle',
            'fondo' => 'bg-estado-exitoBg',
            'texto' => 'text-estado-exito',
        ],
        [
            'titulo' => 'Incidencias',
            'valor' => $estadisticas['incidencias'],
            'icono' => 'ph-warning-circle',
            'fondo' => $estadisticas['incidencias'] > 0 ? 'bg-estado-advertenciaBg' : 'bg-estado-exitoBg',
            'texto' => $estadisticas['incidencias'] > 0 ? 'text-estado-advertencia' : 'text-estado-exito',
        ],
    ];
@endphp

<div class="space-y-4">
    <div class="rm-page-header gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento shadow-inner">
                <i class="ph-fill ph-users-three text-2xl"></i>
            </div>

            <div class="min-w-0">
                <div class="mb-0.5 flex items-center gap-2">
                    <span class="rm-badge-info border-estado-info/20 bg-estado-info/10 px-2 py-0.5 text-[10px] text-estado-info">
                        Gestión del Sistema
                    </span>
                </div>

                <h2 class="rm-section-title">Personal Institucional</h2>

                <p class="rm-section-subtitle max-w-2xl">
                    Gestión centralizada del equipo médico, administrativo, disponibilidad y horarios institucionales.
                </p>
            </div>
        </div>

        <div class="flex w-full flex-wrap items-center gap-2 lg:w-auto lg:justify-end">
            <button
                type="button"
                wire:click="$refresh"
                class="tooltip-btn flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-borde bg-fondo-card text-texto shadow-sm transition-colors hover:border-borde-hover hover:bg-fondo-hover hover:text-titulo"
                title="Actualizar"
            >
                <i class="ph-bold ph-arrows-clockwise text-base"></i>
            </button>

            <button
                type="button"
                class="flex h-9 items-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto shadow-sm transition-colors hover:border-borde-hover hover:bg-fondo-hover hover:text-titulo"
            >
                <i class="ph-bold ph-file-pdf text-base"></i>
                Exportar PDF
            </button>

            <button
                type="button"
                wire:click="abrirModuloHorarios"
                class="rm-btn-success h-9 gap-1.5 rounded-lg px-3 text-xs"
            >
                <i class="ph-bold ph-calendar-plus text-base"></i>
                Asignar horario
            </button>

            @can('usuarios.crear')
                <button
                    type="button"
                    wire:click="abrirModalNuevo"
                    class="rm-btn-primary h-9 gap-1.5 rounded-lg px-3 text-xs"
                >
                    <i class="ph-bold ph-plus text-base"></i>
                    Registrar personal
                </button>
            @endcan
        </div>
    </div>

    <div class="flex overflow-x-auto border-b border-borde scrollbar-hide">
        <div class="flex min-w-max items-center gap-1 px-1">
            <button
                type="button"
                wire:click="setTab('resumen')"
                class="flex items-center gap-1.5 whitespace-nowrap border-b-2 px-2.5 py-2.5 text-xs font-bold transition-all {{ $tabActiva === 'resumen' ? 'border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:text-titulo' }}"
            >
                <i class="{{ $tabActiva === 'resumen' ? 'ph-fill' : 'ph-bold' }} ph-squares-four text-lg"></i>
                Resumen
            </button>

            <button
                type="button"
                wire:click="setTab('salud')"
                class="flex items-center gap-1.5 whitespace-nowrap border-b-2 px-2.5 py-2.5 text-xs font-bold transition-all {{ $tabActiva === 'salud' ? 'border-estado-info text-estado-info' : 'border-transparent text-apoyo hover:text-titulo' }}"
            >
                <i class="{{ $tabActiva === 'salud' ? 'ph-fill' : 'ph-bold' }} ph-stethoscope text-lg"></i>
                Personal de salud
            </button>

            <button
                type="button"
                wire:click="setTab('admin')"
                class="flex items-center gap-1.5 whitespace-nowrap border-b-2 px-2.5 py-2.5 text-xs font-bold transition-all {{ $tabActiva === 'admin' ? 'border-estado-advertencia text-estado-advertencia' : 'border-transparent text-apoyo hover:text-titulo' }}"
            >
                <i class="{{ $tabActiva === 'admin' ? 'ph-fill' : 'ph-bold' }} ph-desktop text-lg"></i>
                Personal administrativo
            </button>

            <button
                type="button"
                wire:click="setTab('disponibilidad')"
                class="flex items-center gap-1.5 whitespace-nowrap border-b-2 px-2.5 py-2.5 text-xs font-bold transition-all {{ $tabActiva === 'disponibilidad' ? 'border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:text-titulo' }}"
            >
                <i class="{{ $tabActiva === 'disponibilidad' ? 'ph-fill' : 'ph-bold' }} ph-clock-user text-lg"></i>
                Disponibilidad
            </button>

            <button
                type="button"
                wire:click="setTab('reportes')"
                class="flex items-center gap-1.5 whitespace-nowrap border-b-2 px-2.5 py-2.5 text-xs font-bold transition-all {{ $tabActiva === 'reportes' ? 'border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:text-titulo' }}"
            >
                <i class="{{ $tabActiva === 'reportes' ? 'ph-fill' : 'ph-bold' }} ph-chart-bar text-lg"></i>
                Reportes
            </button>
        </div>
    </div>

    <div class="min-h-[260px]" wire:key="personal-institucional-tab-{{ $tabActiva }}-{{ $tabVersion ?? 0 }}">
        @if($tabActiva === 'resumen')
            <div class="space-y-4" wire:key="contenido-resumen-{{ $tabVersion ?? 0 }}">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @foreach($metricasResumen as $metrica)
                        <div class="rm-card flex items-center justify-between border border-borde bg-fondo-card px-4 py-3 shadow-sm transition-all hover:-translate-y-0.5 hover:border-borde-hover hover:shadow-md">
                            <div class="min-w-0">
                                <div class="text-[10px] font-black uppercase tracking-[0.22em] text-apoyo">
                                    {{ $metrica['titulo'] }}
                                </div>
                                <div class="mt-2 text-2xl font-black leading-none text-titulo">
                                    {{ $metrica['valor'] }}
                                </div>
                                <div class="mt-1 truncate text-[11px] font-semibold text-apoyo">
                                    {{ $metrica['descripcion'] }}
                                </div>
                            </div>

                            <div class="ml-3 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $metrica['fondo'] }} {{ $metrica['texto'] }}">
                                <i class="ph-fill {{ $metrica['icono'] }} text-xl"></i>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="rm-card flex flex-col gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black text-titulo">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                <i class="ph-fill ph-users-three text-lg"></i>
                            </span>
                            Personal mostrado
                        </h3>
                        <p class="mt-1 text-xs font-semibold text-apoyo">
                            Revisión del personal interno, excluyendo familiares y voluntarios.
                        </p>
                    </div>

                    <div class="flex w-full rounded-xl border border-borde bg-fondo p-1 shadow-inner sm:w-auto">
                        <button
                            type="button"
                            wire:click="cambiarVistaListadoResumen('tarjetas')"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-black transition-all sm:flex-none {{ $vistaListadoResumen === 'tarjetas' ? 'bg-boton-acento text-white shadow-sm' : 'text-apoyo hover:bg-fondo-card hover:text-titulo' }}"
                        >
                            <i class="ph-bold ph-cards-three"></i>
                            Vista tarjetas
                        </button>

                        <button
                            type="button"
                            wire:click="cambiarVistaListadoResumen('tabla')"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-black transition-all sm:flex-none {{ $vistaListadoResumen === 'tabla' ? 'bg-boton-acento text-white shadow-sm' : 'text-apoyo hover:bg-fondo-card hover:text-titulo' }}"
                        >
                            <i class="ph-bold ph-table"></i>
                            Tabla compacta
                        </button>
                    </div>
                </div>

                <div class="rm-card border border-borde bg-fondo-card !p-0 shadow-sm">
                    <div class="border-b border-borde bg-fondo-hover/30 px-4 py-3">
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                            <div class="lg:col-span-3">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-apoyo">
                                    Buscar
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="busquedaResumen"
                                        placeholder="Nombre, correo o CI..."
                                        class="h-9 w-full rounded-lg border border-borde bg-fondo-card pl-9 pr-3 text-xs text-texto shadow-sm outline-none transition-all placeholder:text-apoyo/70 focus:border-boton-acento focus:ring-1 focus:ring-boton-acento"
                                    >
                                </div>
                            </div>

                            <div class="lg:col-span-3">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-apoyo">
                                    Rubro
                                </label>
                                <select
                                    wire:model.live="rubroResumen"
                                    class="h-9 w-full rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto shadow-sm outline-none transition-all focus:border-boton-acento focus:ring-1 focus:ring-boton-acento"
                                >
                                    <option value="institucional">Todos los institucionales</option>
                                    <option value="salud">Personal de salud</option>
                                    <option value="admin">Personal administrativo</option>
                                    <option value="sistema">Administradores del sistema</option>
                                    <option value="activos">Personal activo</option>
                                    <option value="en_turno">En turno actual</option>
                                    <option value="fuera_turno">Fuera de turno</option>
                                    <option value="incidencias">Suspendidos / inactivos</option>
                                </select>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-apoyo">
                                    Estado
                                </label>
                                <select
                                    wire:model.live="estadoResumen"
                                    class="h-9 w-full rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto shadow-sm outline-none transition-all focus:border-boton-acento focus:ring-1 focus:ring-boton-acento"
                                >
                                    <option value="">Todos</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="suspendido">Suspendido</option>
                                </select>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-apoyo">
                                    Turno
                                </label>
                                <select
                                    wire:model.live="turnoResumen"
                                    class="h-9 w-full rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto shadow-sm outline-none transition-all focus:border-boton-acento focus:ring-1 focus:ring-boton-acento"
                                >
                                    <option value="">Todos</option>
                                    <option value="Mañana">Mañana</option>
                                    <option value="Tarde">Tarde</option>
                                    <option value="Noche">Noche</option>
                                    <option value="Madrugada">Madrugada</option>
                                    <option value="sin_turno">Sin turno</option>
                                </select>
                            </div>

                            <div class="flex items-end lg:col-span-2">
                                <button
                                    type="button"
                                    wire:click="limpiarFiltrosResumen"
                                    class="flex h-9 w-full items-center justify-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-black text-apoyo shadow-sm transition-colors hover:bg-fondo-hover hover:text-titulo"
                                >
                                    <i class="ph-bold ph-broom text-base"></i>
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 border-b border-borde px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs font-bold text-texto">
                            Mostrando
                            <span class="font-black text-titulo">{{ $personalResumenMostrados }}</span>
                            de
                            <span class="font-black text-titulo">{{ $personalResumenTotal }}</span>
                            personas institucionales
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            @if($rubroResumen !== 'institucional')
                                <span class="rounded-full bg-boton-acento/10 px-2 py-1 text-[10px] font-black uppercase tracking-wide text-boton-acento">
                                    Rubro filtrado
                                </span>
                            @endif

                            @if($estadoResumen !== '')
                                <span class="rounded-full bg-estado-infoBg px-2 py-1 text-[10px] font-black uppercase tracking-wide text-estado-info">
                                    Estado filtrado
                                </span>
                            @endif

                            @if($turnoResumen !== '')
                                <span class="rounded-full bg-estado-exitoBg px-2 py-1 text-[10px] font-black uppercase tracking-wide text-estado-exito">
                                    Turno filtrado
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($vistaListadoResumen === 'tarjetas')
                        <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse($personalResumen as $usuario)
                                @php
                                    $nombreUsuario = $usuario->name
                                        ?? trim(($usuario->nombres ?? '') . ' ' . ($usuario->ap_paterno ?? '') . ' ' . ($usuario->ap_materno ?? ''));

                                    $correoUsuario = $usuario->correo ?? $usuario->email ?? 'Sin correo';
                                    $ciUsuario = $usuario->ci ?? $usuario->carnet ?? null;

                                    $inicialNombre = mb_substr($usuario->nombres ?? $usuario->name ?? 'P', 0, 1);
                                    $inicialApellido = mb_substr($usuario->ap_paterno ?? '', 0, 1);
                                    $iniciales = trim($inicialNombre . $inicialApellido);

                                    $asignacionActiva = $usuario->asignacionesTurno->first(
                                        fn ($asignacion) => in_array($asignacion->estado, ['ACTIVO', 'ACTIVA'], true)
                                    );

                                    $tipo = $usuario->categoria_institucional ?? ($usuario->roles->first()?->name ?? 'Sistema');

                                    $area = $usuario->areaInstitucional?->nombre ?? ($usuario->tipo_personal === 'salud' ? 'Salud' : ($usuario->tipo_personal === 'admin' ? 'Administrativo' : 'Sistema'));

                                    $estadoActivo = $usuario->estado === 'ACTIVO' || $usuario->estado == 1;
                                    $estadoSuspendido = $usuario->estado === 'SUSPENDIDO';

                                    $estadoClass = $estadoActivo
                                        ? 'bg-estado-exito text-white'
                                        : ($estadoSuspendido ? 'bg-estado-advertencia text-white' : 'bg-estado-peligro text-white');

                                    $estadoLabel = $estadoActivo ? 'Activo' : ($usuario->estado ?? 'Inactivo');
                                @endphp

                                <article class="rm-card group relative overflow-hidden border border-borde bg-fondo-card p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-borde-hover hover:shadow-md">
                                    <div class="absolute right-0 top-0 h-16 w-16 rounded-bl-[2rem] bg-boton-acento/10 opacity-80 transition-transform group-hover:scale-110"></div>

                                    <div class="relative z-10 flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-borde bg-fondo text-lg font-black text-boton-acento shadow-sm">
                                                @if($usuario->profile_photo_path ?? false)
                                                    <img src="{{ $usuario->profile_photo_url }}" alt="{{ $nombreUsuario }}" class="h-full w-full object-cover">
                                                @else
                                                    {{ $iniciales ?: 'PI' }}
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <h4 class="truncate text-sm font-black uppercase tracking-tight text-titulo">
                                                    {{ $nombreUsuario ?: 'Personal sin nombre' }}
                                                </h4>
                                                <p class="mt-0.5 truncate text-[11px] font-semibold text-apoyo">
                                                    {{ $correoUsuario }}
                                                </p>
                                                @if($ciUsuario)
                                                    <p class="mt-0.5 text-[10px] font-bold text-apoyo">
                                                        CI: {{ $ciUsuario }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider shadow-sm {{ $estadoClass }}">
                                            {{ $estadoLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-2">
                                        <div class="rounded-xl border border-borde bg-fondo px-3 py-2">
                                            <div class="text-[10px] font-black uppercase tracking-[0.16em] text-apoyo">Área</div>
                                            <div class="mt-0.5 truncate text-xs font-black text-titulo">{{ $area }}</div>
                                        </div>

                                        <div class="rounded-xl border border-borde bg-fondo px-3 py-2">
                                            <div class="text-[10px] font-black uppercase tracking-[0.16em] text-apoyo">Rol / Tipo</div>
                                            <div class="mt-0.5 truncate text-xs font-black text-titulo">{{ $tipo }}</div>
                                        </div>

                                        <div class="rounded-xl border border-borde bg-fondo px-3 py-2">
                                            <div class="text-[10px] font-black uppercase tracking-[0.16em] text-apoyo">Turno</div>
                                            <div class="mt-0.5 flex items-center gap-1 text-xs font-bold text-texto">
                                                <i class="ph-fill ph-clock text-apoyo"></i>
                                                {{ $asignacionActiva?->turno?->nombre ?? 'Sin turno asignado' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        @can('usuarios.ver')
                                            <button
                                                type="button"
                                                wire:click="abrirModalEdicion('{{ $usuario->cod_usu }}')"
                                                class="flex h-8 items-center gap-1.5 rounded-lg bg-boton-acento px-3 text-[11px] font-black text-white shadow-sm transition-opacity hover:opacity-90"
                                            >
                                                <i class="ph-bold ph-eye"></i>
                                                Ver
                                            </button>
                                        @endcan

                                        @can('usuarios.editar')
                                            <button
                                                type="button"
                                                wire:click="abrirModalEdicion('{{ $usuario->cod_usu }}')"
                                                class="flex h-8 items-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-3 text-[11px] font-black text-texto shadow-sm transition-colors hover:bg-fondo-hover hover:text-boton-acento"
                                            >
                                                <i class="ph-bold ph-pencil-simple"></i>
                                                Editar
                                            </button>
                                        @endcan

                                        <button
                                            type="button"
                                            wire:click="abrirHorariosPersonal('{{ $usuario->cod_usu }}')"
                                            class="flex h-8 items-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-3 text-[11px] font-black text-texto shadow-sm transition-colors hover:bg-fondo-hover hover:text-estado-info"
                                        >
                                            <i class="ph-bold ph-calendar-plus"></i>
                                            Horario
                                        </button>

                                        @can('usuarios.cambiar_estado')
                                            <button
                                                type="button"
                                                wire:click="toggleEstado('{{ $usuario->cod_usu }}')"
                                                class="ml-auto flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo shadow-sm transition-colors hover:bg-fondo-hover hover:text-estado-peligro"
                                                title="{{ $estadoActivo ? 'Suspender' : 'Reactivar' }}"
                                            >
                                                <i class="ph-bold {{ $estadoActivo ? 'ph-pause-circle' : 'ph-play-circle' }}"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </article>
                            @empty
                                <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-borde bg-fondo-card/60 px-4 py-12 text-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl border border-borde bg-fondo-card">
                                        <i class="ph-fill ph-users-slash text-2xl text-apoyo"></i>
                                    </div>
                                    <h4 class="text-sm font-black text-titulo">No se encontró personal</h4>
                                    <p class="mt-1 max-w-sm text-xs font-semibold text-apoyo">
                                        Ajusta los filtros o limpia la búsqueda para revisar otros registros institucionales.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-left">
                                <thead class="border-b border-borde bg-fondo text-[10px] font-black uppercase tracking-wider text-apoyo">
                                    <tr>
                                        <th class="px-4 py-3">Personal</th>
                                        <th class="px-4 py-3">Área</th>
                                        <th class="px-4 py-3">Rol / Tipo</th>
                                        <th class="px-4 py-3">Turno</th>
                                        <th class="px-4 py-3 text-center">Estado</th>
                                        <th class="px-4 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-borde/60">
                                    @forelse($personalResumen as $usuario)
                                        @php
                                            $nombreUsuario = $usuario->name
                                                ?? trim(($usuario->nombres ?? '') . ' ' . ($usuario->ap_paterno ?? '') . ' ' . ($usuario->ap_materno ?? ''));

                                            $correoUsuario = $usuario->correo ?? $usuario->email ?? 'Sin correo';

                                            $inicialNombre = mb_substr($usuario->nombres ?? $usuario->name ?? 'P', 0, 1);
                                            $inicialApellido = mb_substr($usuario->ap_paterno ?? '', 0, 1);
                                            $iniciales = trim($inicialNombre . $inicialApellido);

                                            $asignacionActiva = $usuario->asignacionesTurno->first(
                                                fn ($asignacion) => in_array($asignacion->estado, ['ACTIVO', 'ACTIVA'], true)
                                            );

                                            $tipo = $usuario->categoria_institucional ?? ($usuario->roles->first()?->name ?? 'Sistema');

                                            $area = $usuario->areaInstitucional?->nombre ?? ($usuario->tipo_personal === 'salud' ? 'Salud' : ($usuario->tipo_personal === 'admin' ? 'Administrativo' : 'Sistema'));

                                            $estadoActivo = $usuario->estado === 'ACTIVO' || $usuario->estado == 1;
                                            $estadoSuspendido = $usuario->estado === 'SUSPENDIDO';

                                            $estadoClass = $estadoActivo
                                                ? 'bg-estado-exito text-white'
                                                : ($estadoSuspendido ? 'bg-estado-advertencia text-white' : 'bg-estado-peligro text-white');

                                            $estadoLabel = $estadoActivo ? 'Activo' : ($usuario->estado ?? 'Inactivo');
                                        @endphp

                                        <tr class="transition-colors hover:bg-fondo-hover/60">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border border-borde bg-fondo text-xs font-black text-boton-acento">
                                                        @if($usuario->profile_photo_path ?? false)
                                                            <img src="{{ $usuario->profile_photo_url }}" alt="{{ $nombreUsuario }}" class="h-full w-full object-cover">
                                                        @else
                                                            {{ $iniciales ?: 'PI' }}
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="truncate text-sm font-black text-titulo">{{ $nombreUsuario ?: 'Personal sin nombre' }}</div>
                                                        <div class="mt-0.5 truncate text-[11px] font-semibold text-apoyo">{{ $correoUsuario }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 text-xs font-bold text-texto">{{ $area }}</td>
                                            <td class="px-4 py-3 text-xs font-black text-titulo">{{ $tipo }}</td>
                                            <td class="px-4 py-3 text-xs font-semibold text-apoyo">
                                                {{ $asignacionActiva?->turno?->nombre ?? 'Sin turno' }}
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $estadoClass }}">
                                                    {{ $estadoLabel }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    @can('usuarios.editar')
                                                        <button
                                                            type="button"
                                                            wire:click="abrirModalEdicion('{{ $usuario->cod_usu }}')"
                                                            class="tooltip-btn flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo transition-colors hover:bg-fondo-hover hover:text-boton-acento"
                                                            title="Ver / editar"
                                                        >
                                                            <i class="ph-bold ph-pencil-simple"></i>
                                                        </button>
                                                    @endcan

                                                    <button
                                                        type="button"
                                                        wire:click="abrirHorariosPersonal('{{ $usuario->cod_usu }}')"
                                                        class="tooltip-btn flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo transition-colors hover:bg-fondo-hover hover:text-estado-info"
                                                        title="Horarios"
                                                    >
                                                        <i class="ph-bold ph-calendar-plus"></i>
                                                    </button>

                                                    @can('usuarios.cambiar_estado')
                                                        <button
                                                            type="button"
                                                            wire:click="toggleEstado('{{ $usuario->cod_usu }}')"
                                                            class="tooltip-btn flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo transition-colors hover:bg-fondo-hover hover:text-estado-peligro"
                                                            title="{{ $estadoActivo ? 'Suspender' : 'Reactivar' }}"
                                                        >
                                                            <i class="ph-bold {{ $estadoActivo ? 'ph-pause-circle' : 'ph-play-circle' }}"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-10 text-center">
                                                <h4 class="text-sm font-black text-titulo">No se encontró personal</h4>
                                                <p class="mt-1 text-xs font-semibold text-apoyo">
                                                    No existen registros que coincidan con los filtros seleccionados.
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($tabActiva === 'salud')
            <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5" wire:key="contenido-salud-{{ $tabVersion ?? 0 }}">
                @foreach($tarjetasSalud as $tarjeta)
                    <div class="rm-card group flex cursor-default items-center gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm transition-all hover:-translate-y-0.5 hover:border-borde-hover hover:shadow-md">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $tarjeta['fondo'] }} {{ $tarjeta['texto'] }} shadow-sm transition-colors {{ $tarjeta['hover'] }} group-hover:text-white">
                            <i class="ph-fill {{ $tarjeta['icono'] }} text-lg"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="text-xl font-black leading-none text-titulo">
                                {{ $tarjeta['valor'] }}
                            </div>
                            <div class="mt-1 truncate text-[11px] font-bold uppercase tracking-wider text-apoyo">
                                {{ $tarjeta['titulo'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($tabActiva === 'admin')
            <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4" wire:key="contenido-admin-{{ $tabVersion ?? 0 }}">
                @foreach($tarjetasAdmin as $tarjeta)
                    <div class="rm-card group flex cursor-default items-center gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm transition-all hover:-translate-y-0.5 hover:border-borde-hover hover:shadow-md">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $tarjeta['fondo'] }} {{ $tarjeta['texto'] }} shadow-sm">
                            <i class="ph-fill {{ $tarjeta['icono'] }} text-lg"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="text-xl font-black leading-none text-titulo">
                                {{ $tarjeta['valor'] }}
                            </div>
                            <div class="mt-1 truncate text-[11px] font-bold uppercase tracking-wider text-apoyo">
                                {{ $tarjeta['titulo'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($tabActiva === 'disponibilidad')
            <div class="space-y-4" wire:key="contenido-disponibilidad-{{ $tabVersion ?? 0 }}">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-4 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                            <i class="ph-fill ph-clock-user text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black leading-none text-titulo">{{ $estadisticas['en_turno'] }}</div>
                            <div class="mt-1 text-[11px] font-black uppercase tracking-wider text-apoyo">En turno</div>
                        </div>
                    </div>

                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-4 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-hover text-apoyo">
                            <i class="ph-fill ph-bed text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black leading-none text-titulo">{{ $estadisticas['fuera_turno'] }}</div>
                            <div class="mt-1 text-[11px] font-black uppercase tracking-wider text-apoyo">Fuera de turno</div>
                        </div>
                    </div>

                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-4 shadow-sm">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                            <i class="ph-fill ph-check-circle text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-black leading-none text-titulo">{{ $estadisticas['activos'] }}</div>
                            <div class="mt-1 text-[11px] font-black uppercase tracking-wider text-apoyo">Personal activo</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-borde bg-fondo-card/50 px-4 py-14 text-center">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl border border-borde bg-fondo-card shadow-sm">
                        <i class="ph-fill ph-clock-user text-3xl text-estado-info"></i>
                    </div>
                    <h3 class="text-lg font-black text-titulo">Mapa de disponibilidad</h3>
                    <p class="mt-1.5 max-w-md text-xs font-semibold text-apoyo">
                        Gestión de turnos, ausencias, bajas médicas y suplencias del personal institucional.
                    </p>
                </div>
            </div>
        @elseif($tabActiva === 'reportes')
            <div class="space-y-4" wire:key="contenido-reportes-{{ $tabVersion ?? 0 }}">
                <div class="rm-card flex flex-col gap-4 border border-borde bg-fondo-card p-4 shadow-sm xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black text-titulo">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                <i class="ph-fill ph-chart-bar text-lg"></i>
                            </span>
                            Reportes institucionales
                        </h3>
                        <p class="mt-1 text-xs font-semibold text-apoyo">
                            Análisis visual del personal, disponibilidad, estados y distribución operativa.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:flex xl:items-center">
                        <select class="h-9 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto outline-none focus:border-boton-acento">
                            <option>Periodo: Este mes</option>
                            <option>Periodo: Esta semana</option>
                            <option>Periodo: Hoy</option>
                        </select>

                        <select class="h-9 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto outline-none focus:border-boton-acento">
                            <option>Área: Todas</option>
                            <option>Área: Salud</option>
                            <option>Área: Administrativo</option>
                        </select>

                        <button type="button" class="h-9 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-black text-texto transition-colors hover:bg-fondo-hover">
                            Exportar Excel
                        </button>

                        <button type="button" class="h-9 rounded-lg bg-boton-acento px-3 text-xs font-black text-white shadow-sm transition-colors hover:opacity-90">
                            Exportar PDF
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                            <i class="ph-fill ph-users-three text-xl"></i>
                        </span>
                        <div>
                            <div class="text-2xl font-black text-titulo">{{ $estadisticas['total'] }}</div>
                            <div class="text-[11px] font-black uppercase tracking-wider text-apoyo">Total institucional</div>
                        </div>
                    </div>

                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                            <i class="ph-fill ph-check-circle text-xl"></i>
                        </span>
                        <div>
                            <div class="text-2xl font-black text-titulo">{{ $estadisticas['activos'] }}</div>
                            <div class="text-[11px] font-black uppercase tracking-wider text-apoyo">Activos</div>
                        </div>
                    </div>

                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                            <i class="ph-fill ph-clock-user text-xl"></i>
                        </span>
                        <div>
                            <div class="text-2xl font-black text-titulo">{{ $estadisticas['en_turno'] }}</div>
                            <div class="text-[11px] font-black uppercase tracking-wider text-apoyo">En turno</div>
                        </div>
                    </div>

                    <div class="rm-card flex items-center gap-3 border border-borde bg-fondo-card px-4 py-3 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $estadisticas['incidencias'] > 0 ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-exitoBg text-estado-exito' }}">
                            <i class="ph-fill ph-warning-circle text-xl"></i>
                        </span>
                        <div>
                            <div class="text-2xl font-black text-titulo">{{ $estadisticas['incidencias'] }}</div>
                            <div class="text-[11px] font-black uppercase tracking-wider text-apoyo">Incidencias</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <div class="rm-chart-panel flex h-[300px] flex-col rounded-xl border border-borde bg-fondo-card p-4 shadow-sm">
                        <h4 class="mb-2 flex items-center gap-2 text-sm font-black text-titulo">
                            <i class="ph-fill ph-chart-pie-slice text-boton-acento"></i>
                            Distribución por áreas
                        </h4>

                        <div
                            wire:ignore
                            wire:key="chart-area-{{ $tabVersion ?? 0 }}"
                            class="relative min-h-0 flex-1"
                            x-data="{
                                hasData: {{ array_sum($chartData['area_data'] ?? []) > 0 ? 'true' : 'false' }},
                                init() {
                                    this.$nextTick(() => {
                                        if (!this.hasData || !this.$refs.chart || typeof Chart === 'undefined') return;
                                        const canvas = this.$refs.chart;
                                        const previous = Chart.getChart(canvas);
                                        if (previous) previous.destroy();

                                        const root = document.documentElement;
                                        const textColor = getComputedStyle(root).getPropertyValue('--color-texto').trim() || '#6B7280';

                                        new Chart(canvas, {
                                            type: 'doughnut',
                                            data: {
                                                labels: @js($chartData['area_labels'] ?? []),
                                                datasets: [{
                                                    data: @js($chartData['area_data'] ?? []),
                                                    backgroundColor: [
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-1').trim() || '#3F7D5A',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-2').trim() || '#D9795F',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-3').trim() || '#293A59'
                                                    ],
                                                    borderWidth: 0,
                                                    hoverOffset: 4
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '62%',
                                                plugins: {
                                                    legend: {
                                                        position: 'bottom',
                                                        labels: {
                                                            color: textColor,
                                                            usePointStyle: true,
                                                            boxWidth: 8,
                                                            font: { family: 'Outfit', size: 10, weight: '600' }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }"
                        >
                            <canvas x-ref="chart" x-show="hasData" class="h-full w-full"></canvas>
                            <div x-show="!hasData" class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                <i class="ph-fill ph-chart-pie-slice mb-2 text-4xl"></i>
                                <span class="text-xs font-black uppercase tracking-wider">Sin datos</span>
                            </div>
                        </div>
                    </div>

                    <div class="rm-chart-panel flex h-[300px] flex-col rounded-xl border border-borde bg-fondo-card p-4 shadow-sm">
                        <h4 class="mb-2 flex items-center gap-2 text-sm font-black text-titulo">
                            <i class="ph-fill ph-chart-bar text-estado-info"></i>
                            Estado laboral
                        </h4>

                        <div
                            wire:ignore
                            wire:key="chart-estado-{{ $tabVersion ?? 0 }}"
                            class="relative min-h-0 flex-1"
                            x-data="{
                                hasData: {{ array_sum($chartData['estado_data'] ?? []) > 0 ? 'true' : 'false' }},
                                init() {
                                    this.$nextTick(() => {
                                        if (!this.hasData || !this.$refs.chart || typeof Chart === 'undefined') return;
                                        const canvas = this.$refs.chart;
                                        const previous = Chart.getChart(canvas);
                                        if (previous) previous.destroy();

                                        const root = document.documentElement;
                                        const textColor = getComputedStyle(root).getPropertyValue('--color-texto').trim() || '#6B7280';
                                        const gridColor = getComputedStyle(root).getPropertyValue('--color-borde').trim() || '#E5E7EB';

                                        new Chart(canvas, {
                                            type: 'bar',
                                            data: {
                                                labels: @js($chartData['estado_labels'] ?? []),
                                                datasets: [{
                                                    label: 'Personal',
                                                    data: @js($chartData['estado_data'] ?? []),
                                                    backgroundColor: [
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-1').trim() || '#3F7D5A',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-4').trim() || '#94A3B8',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-3').trim() || '#E9A05F',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-2').trim() || '#D9795F'
                                                    ],
                                                    borderRadius: 8,
                                                    maxBarThickness: 46
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        border: { display: false },
                                                        grid: { color: gridColor },
                                                        ticks: { precision: 0, color: textColor, font: { family: 'Outfit', size: 10 } }
                                                    },
                                                    x: {
                                                        grid: { display: false },
                                                        ticks: { color: textColor, font: { family: 'Outfit', size: 10, weight: '600' } }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }"
                        >
                            <canvas x-ref="chart" x-show="hasData" class="h-full w-full"></canvas>
                            <div x-show="!hasData" class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                <i class="ph-fill ph-chart-bar mb-2 text-4xl"></i>
                                <span class="text-xs font-black uppercase tracking-wider">Sin datos</span>
                            </div>
                        </div>
                    </div>

                    <div class="rm-chart-panel flex h-[300px] flex-col rounded-xl border border-borde bg-fondo-card p-4 shadow-sm">
                        <h4 class="mb-2 flex items-center gap-2 text-sm font-black text-titulo">
                            <i class="ph-fill ph-clock text-modulo-salud"></i>
                            Personal asignado por turno
                        </h4>

                        <div
                            wire:ignore
                            wire:key="chart-turno-{{ $tabVersion ?? 0 }}"
                            class="relative min-h-0 flex-1"
                            x-data="{
                                hasData: {{ array_sum($chartData['turno_data'] ?? []) > 0 ? 'true' : 'false' }},
                                init() {
                                    this.$nextTick(() => {
                                        if (!this.hasData || !this.$refs.chart || typeof Chart === 'undefined') return;
                                        const canvas = this.$refs.chart;
                                        const previous = Chart.getChart(canvas);
                                        if (previous) previous.destroy();

                                        const root = document.documentElement;
                                        const textColor = getComputedStyle(root).getPropertyValue('--color-texto').trim() || '#6B7280';
                                        const gridColor = getComputedStyle(root).getPropertyValue('--color-borde').trim() || '#E5E7EB';

                                        new Chart(canvas, {
                                            type: 'bar',
                                            data: {
                                                labels: @js($chartData['turno_labels'] ?? []),
                                                datasets: [{
                                                    label: 'Personal',
                                                    data: @js($chartData['turno_data'] ?? []),
                                                    backgroundColor: getComputedStyle(root).getPropertyValue('--color-grafico-1').trim() || '#3F7D5A',
                                                    borderRadius: 8,
                                                    maxBarThickness: 36
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                indexAxis: 'y',
                                                plugins: { legend: { display: false } },
                                                scales: {
                                                    x: {
                                                        beginAtZero: true,
                                                        border: { display: false },
                                                        grid: { color: gridColor },
                                                        ticks: { precision: 0, color: textColor, font: { family: 'Outfit', size: 10 } }
                                                    },
                                                    y: {
                                                        grid: { display: false },
                                                        ticks: { color: textColor, font: { family: 'Outfit', size: 10, weight: '600' } }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }"
                        >
                            <canvas x-ref="chart" x-show="hasData" class="h-full w-full"></canvas>
                            <div x-show="!hasData" class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                <i class="ph-fill ph-clock-user mb-2 text-4xl"></i>
                                <span class="text-xs font-black uppercase tracking-wider">Sin turnos activos</span>
                            </div>
                        </div>
                    </div>

                    <div class="rm-chart-panel flex h-[300px] flex-col rounded-xl border border-borde bg-fondo-card p-4 shadow-sm">
                        <h4 class="mb-2 flex items-center gap-2 text-sm font-black text-titulo">
                            <i class="ph-fill ph-first-aid text-estado-exito"></i>
                            Distribución del personal de salud
                        </h4>

                        <div
                            wire:ignore
                            wire:key="chart-salud-{{ $tabVersion ?? 0 }}"
                            class="relative min-h-0 flex-1"
                            x-data="{
                                hasData: {{ array_sum($chartData['salud_data'] ?? []) > 0 ? 'true' : 'false' }},
                                init() {
                                    this.$nextTick(() => {
                                        if (!this.hasData || !this.$refs.chart || typeof Chart === 'undefined') return;
                                        const canvas = this.$refs.chart;
                                        const previous = Chart.getChart(canvas);
                                        if (previous) previous.destroy();

                                        const root = document.documentElement;
                                        const textColor = getComputedStyle(root).getPropertyValue('--color-texto').trim() || '#6B7280';

                                        new Chart(canvas, {
                                            type: 'doughnut',
                                            data: {
                                                labels: @js($chartData['salud_labels'] ?? []),
                                                datasets: [{
                                                    data: @js($chartData['salud_data'] ?? []),
                                                    backgroundColor: [
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-1').trim() || '#3F7D5A',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-2').trim() || '#D9795F',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-3').trim() || '#E9A05F',
                                                        getComputedStyle(root).getPropertyValue('--color-grafico-4').trim() || '#293A59',
                                                        '#7FA587'
                                                    ],
                                                    borderWidth: 0,
                                                    hoverOffset: 4
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '62%',
                                                plugins: {
                                                    legend: {
                                                        position: 'bottom',
                                                        labels: {
                                                            color: textColor,
                                                            usePointStyle: true,
                                                            boxWidth: 8,
                                                            font: { family: 'Outfit', size: 10, weight: '600' }
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }"
                        >
                            <canvas x-ref="chart" x-show="hasData" class="h-full w-full"></canvas>
                            <div x-show="!hasData" class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                <i class="ph-fill ph-first-aid mb-2 text-4xl"></i>
                                <span class="text-xs font-black uppercase tracking-wider">Sin datos de salud</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rm-card overflow-hidden border border-borde bg-fondo-card !p-0 shadow-sm">
                    <div class="border-b border-borde bg-fondo-hover/40 px-4 py-3">
                        <h3 class="flex items-center gap-2 text-sm font-black text-titulo">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                <i class="ph-fill ph-clipboard-text text-lg"></i>
                            </span>
                            Interpretación del reporte
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[880px] text-left">
                            <thead class="border-b border-borde bg-fondo text-[10px] font-black uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-4 py-3">Indicador</th>
                                    <th class="px-4 py-3">Resultado</th>
                                    <th class="px-4 py-3">Interpretación</th>
                                    <th class="px-4 py-3">Acción sugerida</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-borde/60">
                                @forelse($interpretacionReportes as $fila)
                                    <tr class="transition-colors hover:bg-fondo-hover/60">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-borde bg-fondo-card text-boton-acento">
                                                    <i class="ph-fill {{ $fila['icono'] ?? 'ph-info' }}"></i>
                                                </span>
                                                <span class="text-xs font-black text-titulo">{{ $fila['indicador'] ?? 'Indicador' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-black text-titulo">{{ $fila['resultado'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold text-texto">{{ $fila['interpretacion'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold text-apoyo">{{ $fila['accion'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-xs font-semibold text-apoyo">
                                            No existen datos suficientes para interpretar el reporte.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @include('livewire.identidad.personal-institucional-panel.modales.directorio')
    </div>

    @include('livewire.identidad.personal-institucional-panel.modales.gestion')
</div>

@script
<script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-identidad-personal-institucional-panel.js')) !!}
</script>
@endscript
