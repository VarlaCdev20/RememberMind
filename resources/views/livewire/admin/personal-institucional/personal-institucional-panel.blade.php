<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-fill ph-users-three text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">
                    Personal Institucional
                </h2>
                <p class="text-sm font-semibold text-apoyo">
                    Gestión centralizada del equipo médico, administrativo y operativo
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Botón unificado de registro -->
            @can('usuarios.crear')
            <button class="rm-btn-primary h-10 px-4 flex items-center gap-2">
                <i class="ph-bold ph-plus text-lg"></i>
                <span>Registrar Personal</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-fondo-card rounded-2xl border border-borde p-4 flex items-center gap-4">
            <div class="bg-boton-acento/10 text-boton-acento p-3 rounded-xl">
                <i class="ph-fill ph-users text-2xl"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-apoyo uppercase tracking-wider">Total Personal</div>
                <div class="text-2xl font-black text-titulo">{{ $estadisticas['total'] }}</div>
            </div>
        </div>
        <div class="bg-fondo-card rounded-2xl border border-borde p-4 flex items-center gap-4">
            <div class="bg-estado-infoBg text-estado-info p-3 rounded-xl">
                <i class="ph-fill ph-stethoscope text-2xl"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-apoyo uppercase tracking-wider">Pers. de Salud</div>
                <div class="text-2xl font-black text-titulo">{{ $estadisticas['salud'] }}</div>
            </div>
        </div>
        <div class="bg-fondo-card rounded-2xl border border-borde p-4 flex items-center gap-4">
            <div class="bg-estado-advertenciaBg text-estado-advertencia p-3 rounded-xl">
                <i class="ph-fill ph-desktop text-2xl"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-apoyo uppercase tracking-wider">Administrativos</div>
                <div class="text-2xl font-black text-titulo">{{ $estadisticas['admin'] }}</div>
            </div>
        </div>
        <div class="bg-fondo-card rounded-2xl border border-borde p-4 flex items-center gap-4">
            <div class="bg-estado-exitoBg text-estado-exito p-3 rounded-xl">
                <i class="ph-fill ph-check-circle text-2xl"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-apoyo uppercase tracking-wider">Activos</div>
                <div class="text-2xl font-black text-titulo">{{ $estadisticas['activos'] }}</div>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal con Alpine.js para Tabs -->
    <div class="bg-fondo-card rounded-2xl border border-borde shadow-sm">
        
        <!-- Controles de Tabla: Buscador y Tabs -->
        <div class="border-b border-borde px-5 pt-5 pb-0 flex flex-col md:flex-row justify-between items-end gap-4">
            
            <!-- Tabs -->
            <div class="flex space-x-6 overflow-x-auto w-full md:w-auto">
                <button wire:click="$set('tabSeleccionada', 'todos')" class="pb-3 text-sm font-bold transition-all border-b-2 {{ $tabSeleccionada === 'todos' ? 'text-boton-acento border-boton-acento' : 'text-apoyo border-transparent hover:text-titulo' }}">
                    Todos
                </button>
                <button wire:click="$set('tabSeleccionada', 'salud')" class="pb-3 text-sm font-bold transition-all border-b-2 {{ $tabSeleccionada === 'salud' ? 'text-estado-info border-estado-info' : 'text-apoyo border-transparent hover:text-titulo' }} flex items-center gap-1">
                    <i class="ph-fill ph-stethoscope"></i> Salud
                </button>
                <button wire:click="$set('tabSeleccionada', 'admin')" class="pb-3 text-sm font-bold transition-all border-b-2 {{ $tabSeleccionada === 'admin' ? 'text-estado-advertencia border-estado-advertencia' : 'text-apoyo border-transparent hover:text-titulo' }} flex items-center gap-1">
                    <i class="ph-fill ph-desktop"></i> Administrativo
                </button>
            </div>

            <!-- Buscador -->
            <div class="w-full md:w-64 pb-3">
                <div class="relative">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                    <input type="text" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar personal..." class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border-input-borde bg-input-bg focus:ring-input-ringFocus focus:border-input-bordeFocus">
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-fondo-tabla text-apoyo uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-5 py-4">Usuario / Nombre</th>
                        <th class="px-5 py-4">Área / Rol</th>
                        <th class="px-5 py-4">Contacto</th>
                        <th class="px-5 py-4 text-center">Estado</th>
                        <th class="px-5 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-fondo-hover transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-full border border-borde">
                                        <img src="{{ $usuario->profile_photo_url }}" alt="{{ $usuario->name }}" class="h-full w-full object-cover" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-titulo">{{ $usuario->name }}</div>
                                        <div class="text-[10px] text-apoyo uppercase font-semibold">{{ $usuario->cod_usu }} | CI: {{ $usuario->numero_documento }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @if($usuario->personalSalud)
                                    <div class="text-xs font-bold text-estado-info flex items-center gap-1">
                                        <i class="ph-fill ph-stethoscope"></i> Salud
                                    </div>
                                    <div class="text-[10px] text-apoyo">{{ $usuario->personalSalud->especialidad->nombre_especialidad ?? 'Sin especialidad' }}</div>
                                @elseif($usuario->personalAdmin)
                                    <div class="text-xs font-bold text-estado-advertencia flex items-center gap-1">
                                        <i class="ph-fill ph-desktop"></i> Administrativo
                                    </div>
                                    <div class="text-[10px] text-apoyo">{{ $usuario->personalAdmin->cargo->nombre_cargo ?? 'Sin cargo' }}</div>
                                @else
                                    <div class="text-xs font-bold text-apoyo">Sin clasificación</div>
                                @endif
                                <div class="text-[10px] mt-1 text-apoyo font-semibold bg-borde/30 inline-block px-2 py-0.5 rounded">
                                    {{ $usuario->roles->pluck('name')->join(', ') }}
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="text-xs text-titulo flex items-center gap-1"><i class="ph-fill ph-envelope-simple text-apoyo"></i> {{ $usuario->correo ?? 'N/A' }}</div>
                                <div class="text-xs text-titulo flex items-center gap-1"><i class="ph-fill ph-phone text-apoyo"></i> {{ $usuario->telefono ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php
                                    // Adaptador temporal para estados booleanos y strings
                                    $estadoClass = '';
                                    $estadoLabel = '';
                                    
                                    if($usuario->estado === 'ACTIVO' || $usuario->estado == 1) {
                                        $estadoClass = 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde';
                                        $estadoLabel = 'ACTIVO';
                                    } elseif($usuario->estado === 'INACTIVO' || $usuario->estado == 0) {
                                        $estadoClass = 'bg-estado-peligroBg text-estado-peligro border-estado-peligroBorde';
                                        $estadoLabel = 'INACTIVO';
                                    } elseif($usuario->estado === 'SUSPENDIDO') {
                                        $estadoClass = 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde';
                                        $estadoLabel = 'SUSPENDIDO';
                                    } elseif($usuario->estado === 'RETIRADO') {
                                        $estadoClass = 'bg-borde text-apoyo border-borde-suave';
                                        $estadoLabel = 'RETIRADO';
                                    } else {
                                        $estadoClass = 'bg-estado-infoBg text-estado-info border-estado-infoBorde';
                                        $estadoLabel = $usuario->estado;
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $estadoClass }}">
                                    {{ $estadoLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button class="h-8 w-8 rounded-lg bg-borde/50 text-parrafo hover:bg-boton-acento hover:text-white transition-colors flex items-center justify-center tooltip-btn" title="Editar Información">
                                        <i class="ph-bold ph-pencil-simple text-sm"></i>
                                    </button>
                                    <button class="h-8 w-8 rounded-lg bg-borde/50 text-parrafo hover:bg-estado-info hover:text-white transition-colors flex items-center justify-center tooltip-btn" title="Gestionar Horarios">
                                        <i class="ph-bold ph-calendar-blank text-sm"></i>
                                    </button>
                                    <button class="h-8 w-8 rounded-lg bg-borde/50 text-parrafo hover:bg-estado-advertencia hover:text-white transition-colors flex items-center justify-center tooltip-btn" title="Gestionar Documentos">
                                        <i class="ph-bold ph-folder-open text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-apoyo">
                                    <i class="ph-fill ph-users-slash text-4xl mb-2"></i>
                                    <p class="text-sm font-semibold">No se encontraron resultados para la búsqueda actual.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
