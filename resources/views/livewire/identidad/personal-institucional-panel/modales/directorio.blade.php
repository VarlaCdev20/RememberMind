@if($mostrarDirectorio)
            <div class="mt-4">
                <div class="rm-card flex flex-col overflow-hidden border border-borde bg-fondo-card !p-0 shadow-sm">
                    <div class="border-b border-borde bg-fondo-hover/30 px-4 py-3" x-data="{ openFilters: false }">
                        <div class="flex flex-col items-start justify-between gap-3 md:flex-row md:items-center">
                            <h3 class="flex items-center gap-2 text-sm font-black text-titulo">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl border {{ $claseIconoDirectorio }} shadow-sm">
                                    <i class="ph-fill {{ $iconoDirectorio }} text-lg"></i>
                                </div>
                                {{ $tituloDirectorio }}
                            </h3>

                            <div class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                                <div class="group relative w-full sm:w-72">
                                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo transition-colors group-focus-within:text-boton-acento"></i>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="busqueda"
                                        placeholder="Buscar nombre, correo o CI..."
                                        class="h-9 w-full rounded-lg border border-borde bg-fondo-card pl-9 pr-3 text-xs text-texto shadow-sm outline-none transition-all placeholder:text-apoyo/70 focus:border-boton-acento focus:ring-1 focus:ring-boton-acento"
                                    >
                                </div>

                                <button
                                    type="button"
                                    @click="openFilters = !openFilters"
                                    class="flex h-9 w-full items-center justify-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-texto shadow-sm transition-colors hover:bg-fondo-hover hover:text-titulo sm:w-auto"
                                >
                                    <i class="ph-bold ph-funnel text-base"></i>
                                    Filtros
                                </button>

                                <button
                                    type="button"
                                    wire:click="limpiarFiltros"
                                    class="flex h-9 w-full items-center justify-center gap-1.5 rounded-lg border border-borde bg-fondo-card px-3 text-xs font-bold text-apoyo shadow-sm transition-colors hover:bg-fondo-hover hover:text-titulo sm:w-auto"
                                >
                                    <i class="ph-bold ph-broom text-base"></i>
                                    Limpiar
                                </button>
                            </div>
                        </div>

                        <div x-show="openFilters" x-cloak class="mt-3 grid grid-cols-1 gap-3 border-t border-borde pt-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-apoyo">Tipo</label>
                                <select wire:model.live="filtroTipo" class="h-8 w-full rounded-lg border border-borde bg-fondo-card px-2.5 text-xs text-texto outline-none focus:border-boton-acento">
                                    <option value="">Todos</option>
                                    <option value="salud">Salud</option>
                                    <option value="admin">Administrativo</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-apoyo">Rol</label>
                                <select wire:model.live="filtroRol" class="h-8 w-full rounded-lg border border-borde bg-fondo-card px-2.5 text-xs text-texto outline-none focus:border-boton-acento">
                                    <option value="">Todos</option>
                                    <option value="medico">Médico</option>
                                    <option value="enfermero">Enfermero</option>
                                    <option value="psicologo">Psicólogo</option>
                                    <option value="fisioterapeuta">Fisioterapeuta</option>
                                    <option value="nutricionista">Nutricionista</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-apoyo">Estado</label>
                                <select wire:model.live="filtroEstado" class="h-8 w-full rounded-lg border border-borde bg-fondo-card px-2.5 text-xs text-texto outline-none focus:border-boton-acento">
                                    <option value="">Todos</option>
                                    <option value="activo">Activo</option>
                                    <option value="suspendido">Suspendido</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-apoyo">Disponibilidad</label>
                                <select wire:model.live="filtroDisponibilidad" class="h-8 w-full rounded-lg border border-borde bg-fondo-card px-2.5 text-xs text-texto outline-none focus:border-boton-acento">
                                    <option value="">Todas</option>
                                    <option value="libre">Libre</option>
                                    <option value="ocupado">Ocupado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[920px] text-left">
                            <thead class="border-y border-borde bg-fondo-hover/50 text-[10px] font-bold uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-4 py-3">Personal</th>
                                    <th class="px-4 py-3">Rol / Tipo</th>
                                    <th class="px-4 py-3">Área / Turno</th>
                                    <th class="px-4 py-3 text-center">Estado</th>
                                    <th class="px-4 py-3 text-center">Carga</th>
                                    <th class="px-4 py-3 text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-borde/50 bg-fondo-card">
                                @forelse($usuarios as $usuario)
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

                                        $tipoSalud = $usuario->rol_principal;

                                        $tipo = $usuario->categoria_institucional ?? ($usuario->roles->first()?->name ?? 'Sistema');

                                        $area = $usuario->areaInstitucional?->nombre ?? ($usuario->tipo_personal === 'salud' ? 'Salud' : ($usuario->tipo_personal === 'admin' ? 'Administrativo' : 'Sistema'));

                                        $estadoActivo = $usuario->estado === 'ACTIVO' || $usuario->estado == 1;
                                        $estadoSuspendido = $usuario->estado === 'SUSPENDIDO';

                                        $estadoClass = $estadoActivo
                                            ? 'bg-estado-exito text-white'
                                            : ($estadoSuspendido ? 'bg-estado-advertencia text-white' : 'bg-estado-peligro text-white');

                                        $estadoLabel = $estadoActivo ? 'ACTIVO' : ($usuario->estado ?? 'INACTIVO');

                                        $esEnfermeria = in_array($tipoSalud, ['ENFERMERO', 'ENFERMERA', 'ENFERMEROS', 'ENFERMERAS'], true);
                                    @endphp

                                    <tr class="group transition-colors hover:bg-fondo-hover/60">
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border border-borde bg-fondo text-xs font-black text-boton-acento shadow-sm">
                                                    @if($usuario->profile_photo_path ?? false)
                                                        <img src="{{ $usuario->profile_photo_url }}" alt="{{ $nombreUsuario }}" class="h-full w-full object-cover">
                                                    @else
                                                        {{ $iniciales ?: 'PI' }}
                                                    @endif
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="truncate text-sm font-black text-titulo transition-colors group-hover:text-boton-acento">
                                                        {{ $nombreUsuario ?: 'Personal sin nombre' }}
                                                    </div>
                                                    <div class="mt-0.5 flex items-center gap-1 truncate text-[11px] font-medium text-apoyo">
                                                        <i class="ph-fill ph-envelope-simple"></i>
                                                        {{ $correoUsuario }}
                                                    </div>
                                                    @if($ciUsuario)
                                                        <div class="mt-0.5 text-[10px] font-bold tracking-wide text-apoyo">
                                                            CI: {{ $ciUsuario }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <div class="truncate text-xs font-black text-titulo">{{ $tipo }}</div>

                                            @if($usuario->personalSalud)
                                                <span class="mt-1 inline-block rounded-md border border-estado-exitoBorde bg-estado-exitoBg px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-estado-exito">
                                                    Salud
                                                </span>
                                            @elseif($usuario->personalAdmin)
                                                <span class="mt-1 inline-block rounded-md border border-estado-advertenciaBorde bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-estado-advertencia">
                                                    Administración
                                                </span>
                                            @else
                                                <span class="mt-1 inline-block rounded-md border border-borde bg-fondo px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-texto">
                                                    Sistema
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <div class="truncate text-xs font-bold text-titulo">
                                                {{ $area }}
                                            </div>

                                            <div class="mt-1 flex items-center gap-1 text-[11px] font-medium text-apoyo">
                                                <i class="ph-fill ph-clock"></i>
                                                {{ $asignacionActiva?->turno?->nombre ?? 'Sin turno asignado' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-2.5 text-center">
                                            <span class="inline-block rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider shadow-sm {{ $estadoClass }}">
                                                {{ $estadoLabel }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2.5 text-center">
                                            @if($usuario->personalSalud && $esEnfermeria)
                                                <span class="rounded-md border border-borde bg-fondo px-2 py-1 text-xs font-black text-titulo" title="Asignaciones activas">
                                                    {{ $usuario->asignacionesTurno->whereIn('estado', ['ACTIVO', 'ACTIVA'])->count() }}
                                                </span>
                                            @else
                                                <span class="text-[10px] font-bold uppercase text-apoyo">N/A</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-2.5 text-center">
                                            <div class="flex items-center justify-center gap-1 opacity-70 transition-opacity group-hover:opacity-100 focus-within:opacity-100">
                                                @can('usuarios.editar')
                                                    <button
                                                        type="button"
                                                        wire:click="abrirModalEdicion('{{ $usuario->cod_usu }}')"
                                                        class="tooltip-btn flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-apoyo shadow-sm transition-colors hover:border-borde-hover hover:bg-fondo-hover hover:text-boton-acento"
                                                        title="Ver / Editar ficha"
                                                    >
                                                        <i class="ph-bold ph-pencil-simple text-base"></i>
                                                    </button>
                                                @endcan

                                                <button
                                                    type="button"
                                                    wire:click="abrirHorariosPersonal('{{ $usuario->cod_usu }}')"
                                                    class="tooltip-btn flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-apoyo shadow-sm transition-colors hover:border-borde-hover hover:bg-fondo-hover hover:text-estado-info"
                                                    title="Ver / asignar horarios"
                                                >
                                                    <i class="ph-bold ph-calendar-plus text-base"></i>
                                                </button>

                                                @can('usuarios.cambiar_estado')
                                                    <button
                                                        type="button"
                                                        wire:click="toggleEstado('{{ $usuario->cod_usu }}')"
                                                        class="tooltip-btn flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-apoyo shadow-sm transition-colors hover:border-borde-hover hover:bg-fondo-hover hover:text-estado-peligro"
                                                        title="{{ $estadoActivo ? 'Suspender' : 'Reactivar' }}"
                                                    >
                                                        <i class="ph-bold {{ $estadoActivo ? 'ph-pause-circle' : 'ph-play-circle' }} text-base"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl border border-borde bg-fondo">
                                                    <i class="ph-fill ph-users-slash text-2xl text-apoyo"></i>
                                                </div>
                                                <h4 class="text-sm font-black text-titulo">No se encontró personal</h4>
                                                <p class="mt-1 max-w-sm text-xs font-medium text-apoyo">
                                                    Ajusta los filtros de búsqueda o registra nuevo personal institucional.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
