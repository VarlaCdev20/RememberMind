<div x-data="{ grupoExpandido: 'Administración' }">
    {{-- OVERLAY DE CARGA GLOBAL --}}
    <div wire:loading.delay wire:target="seleccionarRol,guardarPermisos,restaurarCambios" class="absolute inset-0 z-[60] flex items-center justify-center rounded-[2rem] bg-fondo-card/45 backdrop-blur-sm">
        <div class="flex items-center gap-3 rounded-full bg-fondo-card px-5 py-3 shadow-lg">
            <i class="ph-bold ph-spinner animate-spin text-2xl text-boton-acento"></i>
            <span class="text-xs font-black uppercase tracking-widest text-titulo">Procesando</span>
        </div>
    </div>

    {{-- ENCABEZADO Y MÉTRICAS --}}
    <header class="mb-5 rounded-[1.6rem] border border-transparent bg-fondo-panel px-6 py-5 shadow-[0_12px_28px_rgba(47,62,92,0.11)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-boton-principal text-inverso shadow-lg shadow-[#2F3E5C]/15 sm:flex">
                    <i class="ph-bold ph-shield-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-boton-acento">Control de accesos</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-titulo md:text-[2.3rem]">
                        Roles y <span class="text-boton-acento">permisos</span>
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm font-semibold leading-6 text-apoyo">
                        Gestiona la jerarquía de seguridad y privilegios del sistema institucional.
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Roles del Sistema</p>
                    <p class="text-2xl font-black text-titulo">{{ $roles ? $roles->count() : 0 }}</p>
                </div>
                <div class="h-10 w-px bg-fondo-panel"></div>
                <div class="text-left">
                    <p class="text-[10px] font-black uppercase tracking-widest text-apoyo">Permisos Totales</p>
                    <p class="text-2xl font-black text-boton-acento">{{ collect($permisosAgrupados)->flatten()->count() }}</p>
                </div>
            </div>
        </div>
    </header>

    @if(!auth()->user()->can('roles.editar_permisos'))
        <div class="mb-5 rounded-[1.35rem] bg-fondo-panel border-none p-4 shadow-sm flex items-center gap-3">
            <i class="ph-bold ph-info text-boton-acento text-xl"></i>
            <span class="text-sm font-bold text-titulo">Solo tienes permiso de consulta. No puedes modificar accesos.</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-12 gap-5 items-start">
        
        {{-- PANEL IZQUIERDO: ROLES --}}
        <aside class="lg:col-span-4 flex flex-col gap-3">
            @if($roles)
                @foreach($roles as $rol)
                    @php
                        $esActivo = $rolSeleccionadoId === $rol->id;
                        $colorClass = $this->obtenerColorRol($rol->name);
                    @endphp
                    
                    <button type="button" 
                            wire:click="seleccionarRol({{ $rol->id }})"
                            class="group relative w-full text-left overflow-hidden rounded-[1.45rem] border border-transparent shadow-[0_10px_24px_rgba(47,62,92,0.08)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_14px_30px_rgba(47,62,92,0.12)] {{ $esActivo ? 'bg-fondo-panel ring-2 ring-[#E27D60]/30' : 'bg-fondo-panel hover:bg-fondo-panel' }}">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $esActivo ? 'bg-boton-acento' : 'bg-transparent group-hover:bg-fondo-panel' }} transition-colors"></div>
                        
                        <div class="p-4 pl-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $colorClass }} shadow-sm">
                                        {{ $this->obtenerNombreVisualRol($rol->name) }}
                                    </span>
                                    <h3 class="mt-2 text-[10px] font-bold text-apoyo uppercase tracking-widest">{{ $rol->name }}</h3>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-fondo-card/60 text-titulo shadow-sm transition group-hover:scale-110">
                                    <i class="ph-bold ph-caret-right"></i>
                                </div>
                            </div>
                            
                            <p class="mt-2 text-xs font-semibold text-apoyo">
                                {{ $this->obtenerDescripcionRol($rol->name) }}
                            </p>
                            
                            <div class="mt-3 flex items-center gap-4 border-t border-borde-suave pt-3">
                                <div class="flex items-center gap-1.5 text-[10px] font-black text-apoyo">
                                    <i class="ph-bold ph-users text-sm"></i>
                                    {{ $rol->users_count }} Usuarios
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-black text-apoyo">
                                    <i class="ph-bold ph-key text-sm"></i>
                                    {{ $rol->permissions->count() }} Permisos
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            @endif
        </aside>

        {{-- PANEL DERECHO: PERMISOS --}}
        <main class="lg:col-span-8 rounded-[1.6rem] bg-fondo-panel shadow-[0_14px_38px_rgba(47,62,92,0.13)] border-transparent overflow-hidden">
            @if($rolSeleccionado)
                <header class="border-b border-borde-suave bg-fondo-panel px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-titulo">
                                Permisos: <span class="text-boton-acento">{{ $this->obtenerNombreVisualRol($rolSeleccionado->name) }}</span>
                            </h2>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo mt-1">
                                {{ count($permisosSeleccionados) }} Privilegios activos
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="abrirUsuariosRol" class="inline-flex h-9 items-center gap-2 rounded-xl bg-fondo-card/60 px-4 text-[10px] font-black uppercase text-titulo shadow-sm transition hover:bg-fondo-card active:scale-95">
                                <i class="ph-bold ph-users"></i>
                                Ver Usuarios
                            </button>
                        </div>
                    </div>
                </header>

                <div class="p-6 space-y-4">
                    @foreach($permisosAgrupados as $grupo => $permisos)
                        <div class="overflow-hidden rounded-[1.2rem] border border-borde-suave bg-fondo-card/50 transition-all duration-300">
                            <button type="button" 
                                    @click="grupoExpandido = (grupoExpandido === '{{ $grupo }}' ? '' : '{{ $grupo }}')"
                                    class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-fondo-card/70">
                                <span class="text-xs font-black uppercase tracking-widest text-titulo">{{ $grupo }}</span>
                                <i class="ph-bold ph-caret-down text-boton-acento transition-transform duration-300" 
                                   :class="grupoExpandido === '{{ $grupo }}' ? 'rotate-180' : ''"></i>
                            </button>
                            
                            <div x-show="grupoExpandido === '{{ $grupo }}'" x-collapse>
                                <div class="grid sm:grid-cols-2 gap-px bg-fondo-panel border-t border-borde-suave p-px">
                                    @foreach($permisos as $permiso)
                                        @php
                                            $tienePermiso = in_array($permiso, $permisosSeleccionados);
                                            $esCritico = $permiso === 'roles.editar_permisos';
                                        @endphp
                                        <div class="bg-fondo-card px-5 py-4 flex items-start justify-between gap-3 group transition hover:bg-fondo-panel">
                                            <div>
                                                <p class="text-sm font-black text-titulo {{ $esCritico ? 'text-boton-acento' : '' }}">
                                                    {{ $this->obtenerNombreVisualPermiso($permiso) }}
                                                </p>
                                                <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo mt-0.5">
                                                    {{ $permiso }}
                                                </p>
                                            </div>
                                            
                                            <button type="button" 
                                                    wire:click="togglePermiso('{{ $permiso }}')"
                                                    @if(!auth()->user()->can('roles.editar_permisos')) disabled @endif
                                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed {{ $tienePermiso ? ($esCritico ? 'bg-boton-acento' : 'bg-estado-exitoBg') : 'bg-fondo-panel' }}">
                                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-fondo-card shadow ring-0 transition duration-200 ease-in-out {{ $tienePermiso ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- FOOTER DE GUARDADO --}}
                <footer class="border-t border-borde-suave bg-fondo-panel px-6 py-4 flex items-center justify-end gap-3 transition-all duration-300 {{ $hayCambios ? 'bg-fondo-panel' : '' }}">
                    @if($hayCambios)
                        <span class="mr-auto text-[10px] font-black uppercase text-boton-acento flex items-center gap-1.5 animate-pulse">
                            <i class="ph-bold ph-warning-circle text-base"></i> Hay cambios sin guardar
                        </span>
                        
                        <button type="button" 
                                wire:click="restaurarCambios" 
                                class="rounded-full bg-fondo-card px-5 py-2.5 text-[10px] font-black uppercase text-titulo shadow-sm transition hover:bg-fondo-app active:scale-95">
                            Restaurar
                        </button>
                    @endif

                    @can('roles.editar_permisos')
                    <button type="button" 
                            wire:click="guardarPermisos" 
                            @if(!$hayCambios) disabled @endif
                            class="inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-[10px] font-black uppercase shadow-lg transition active:scale-95 {{ $hayCambios ? 'bg-boton-acento text-inverso hover:bg-boton-principal' : 'bg-fondo-panel text-inverso cursor-not-allowed' }}">
                        <i class="ph-bold ph-check-circle"></i>
                        Guardar cambios
                    </button>
                    @endcan
                </footer>
            @else
                <div class="p-10 text-center flex flex-col items-center justify-center text-apoyo">
                    <i class="ph-bold ph-shield-slash text-4xl mb-3"></i>
                    <p class="font-bold">Selecciona un rol para gestionar sus accesos</p>
                </div>
            @endif
        </main>
    </div>

    {{-- MODAL LATERAL DE USUARIOS --}}
    @if($mostrarUsuariosRol && $rolSeleccionado)
        <div class="fixed inset-0 z-[80] flex justify-end">
            <div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm transition-opacity" 
                 wire:click="cerrarUsuariosRol"></div>
            
            <aside class="relative w-full max-w-md bg-fondo-panel shadow-[0_20px_50px_rgba(0,0,0,0.3)] h-full flex flex-col transition-transform duration-300 transform translate-x-0" 
                   style="animation: slideInRight 0.3s ease-out">
                
                <header class="flex items-center justify-between bg-fondo-card/60 px-6 py-5 border-b border-borde-suave">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-titulo">Usuarios Asociados</h3>
                        <p class="text-[10px] font-bold text-boton-acento uppercase mt-1">{{ $this->obtenerNombreVisualRol($rolSeleccionado->name) }}</p>
                    </div>
                    <button type="button" wire:click="cerrarUsuariosRol" class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-app text-titulo transition hover:bg-boton-acento hover:text-inverso active:scale-90 shadow-sm">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </header>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    @forelse($usuariosDelRol as $usu)
                        <div class="bg-fondo-card rounded-2xl p-4 shadow-sm border border-transparent hover:border-borde-suave transition">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-principal text-inverso font-black">
                                    {{ mb_substr($usu->nombres ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-titulo uppercase">{{ $usu->nombres }} {{ $usu->ap_paterno }}</p>
                                    <p class="text-[10px] font-bold text-apoyo mt-0.5 lowercase">{{ $usu->correo }}</p>
                                    
                                    <span class="inline-block mt-2 rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-widest {{ $usu->estado === 'ACTIVO' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo-panel text-meta' }}">
                                        {{ $usu->estado }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-6 text-apoyo">
                            <i class="ph-bold ph-users-slash text-3xl mb-2"></i>
                            <p class="text-xs font-bold uppercase tracking-widest">No hay usuarios asignados a este rol</p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    @endif
    
    <style>
        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</div>
