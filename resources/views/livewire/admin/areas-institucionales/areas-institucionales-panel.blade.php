<div class="p-6 md:p-8 space-y-8 relative min-h-screen bg-[#F8F3ED]/40">
    {{-- ENCABEZADO PREMIUM --}}
    <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-[#2F3E5C]">Áreas Institucionales</h1>
            <p class="text-sm font-semibold text-[#967B66]">Estructura y organigrama funcional operativo de Casa Amandita</p>
        </div>

        <div class="flex items-center gap-3">
            @can('areas.reportes')
                <button type="button"
                        wire:click="abrirReportes"
                        class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-[#C7B5A3] bg-white px-5 py-2.5 text-xs font-black text-[#7C7168] shadow-md transition-all duration-300 hover:bg-[#F3EEE8] active:scale-95">
                    <i class="ph-bold ph-printer text-base"></i>
                    Reportes
                </button>
            @endcan

            @can('areas.crear')
                <button type="button"
                        wire:click="crearArea"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E27D60] px-6 py-2.5 text-xs font-black text-white shadow-lg shadow-[#E27D60]/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0 active:scale-95">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    Nueva área
                </button>
            @endcan
        </div>
    </header>

    {{-- MÉTRICAS E INDICADORES --}}
    <section class="grid grid-cols-2 md:grid-cols-5 gap-4">
        {{-- Total Áreas --}}
        <div class="rounded-2xl border-none bg-white p-4 shadow-[0_12px_24px_rgba(47,62,92,0.05)] transition duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-[#967B66]">Total Áreas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                    <i class="ph-bold ph-layout text-lg"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[#2F3E5C]">{{ $totalAreas }}</p>
        </div>

        {{-- Áreas Activas --}}
        <div class="rounded-2xl border-none bg-white p-4 shadow-[0_12px_24px_rgba(47,62,92,0.05)] transition duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-[#967B66]">Activas</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#8DA280]/20 text-[#63775B]">
                    <i class="ph-bold ph-check-circle text-lg"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[#63775B]">{{ $areasActivas }}</p>
        </div>

        {{-- Usuarios Vinculados --}}
        <div class="rounded-2xl border-none bg-white p-4 shadow-[0_12px_24px_rgba(47,62,92,0.05)] transition duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-[#967B66]">Personal</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#E27D60]/10 text-[#E27D60]">
                    <i class="ph-bold ph-users-three text-lg"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-[#E27D60]">{{ $usuariosVinculados }}</p>
        </div>

        {{-- Áreas Sin Responsable --}}
        <div class="rounded-2xl border-none bg-white p-4 shadow-[0_12px_24px_rgba(47,62,92,0.05)] transition duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-[#967B66]">Sin Responsable</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-100 text-red-700">
                    <i class="ph-bold ph-warning-circle text-lg"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-red-600">{{ $areasSinResponsable }}</p>
        </div>

        {{-- Módulos Vinculados --}}
        <div class="rounded-2xl border-none bg-white p-4 shadow-[0_12px_24px_rgba(47,62,92,0.05)] transition duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-[#967B66]">Módulos en Uso</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-100 text-purple-700">
                    <i class="ph-bold ph-square-half text-lg"></i>
                </span>
            </div>
            <p class="mt-2 text-2xl font-black text-purple-700">{{ $modulosUtilizadosCount }}</p>
        </div>
    </section>

    {{-- FILTROS DE BÚSQUEDA Y VISTA --}}
    <section class="rounded-2xl bg-white/70 backdrop-blur-md p-4 shadow-[0_8px_30px_rgba(47,62,92,0.04)] border border-[#C7B5A3]/30">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            {{-- Búsqueda --}}
            <div class="relative md:col-span-2">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#967B66]">
                    <i class="ph-bold ph-magnifying-glass"></i>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nombre, código o tipo..."
                       class="w-full rounded-xl border-[#C7B5A3]/50 bg-white/80 py-2.5 pl-10 pr-4 text-xs font-semibold text-[#2F3E5C] placeholder-[#967B66]/60 shadow-sm transition focus:border-[#E27D60] focus:ring-1 focus:ring-[#E27D60]">
            </div>

            {{-- Tipo de Área --}}
            <div>
                <select wire:model.live="filtroTipo"
                        class="w-full rounded-xl border-[#C7B5A3]/50 bg-white py-2.5 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60] focus:ring-1 focus:ring-[#E27D60]">
                    <option value="">-- Todos los tipos --</option>
                    <option value="Administrativa">Administrativa</option>
                    <option value="Salud">Salud</option>
                    <option value="Social">Social</option>
                    <option value="Soporte">Soporte</option>
                </select>
            </div>

            {{-- Estado --}}
            <div>
                <select wire:model.live="filtroEstado"
                        class="w-full rounded-xl border-[#C7B5A3]/50 bg-white py-2.5 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60] focus:ring-1 focus:ring-[#E27D60]">
                    <option value="">-- Todos los estados --</option>
                    <option value="ACTIVA">Áreas Activas</option>
                    <option value="INACTIVA">Áreas Inactivas</option>
                </select>
            </div>

            {{-- Botón limpiar --}}
            <div class="flex gap-2">
                <button type="button"
                        wire:click="limpiarFiltros"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#C7B5A3] bg-white py-2.5 text-xs font-black text-[#7C7168] shadow-sm transition hover:bg-[#F3EEE8] active:scale-95">
                    <i class="ph-bold ph-funnel-simple-x"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- GRID PRINCIPAL DE CARDS (EL MAPA INSTITUCIONAL) --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($areas as $area)
            @php
                $esInactiva = $area->estado === 'INACTIVA';
                $colorAccent = $area->color ?? '#2F3E5C';
            @endphp
            <div class="group relative rounded-3xl border-transparent transition-all duration-300 hover:-translate-y-1 bg-[#FDFBF9] shadow-[0_14px_30px_rgba(47,62,92,0.06)] hover:shadow-[0_20px_40px_rgba(47,62,92,0.12)] p-6 overflow-hidden border-2 border-transparent hover:border-[#C7B5A3]/40 {{ $esInactiva ? 'opacity-70 grayscale bg-[#E6DDD3]/20' : '' }}">
                
                {{-- DETALLE DE COLOR EN BARRA SUPERIOR --}}
                <div class="absolute top-0 left-0 w-full h-1.5" style="background-color: {{ $colorAccent }}"></div>

                {{-- ENCABEZADO CARD --}}
                <div class="flex items-start justify-between">
                    {{-- Icono y Título --}}
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl shadow-inner text-white" style="background-color: {{ $esInactiva ? '#9B8B7E' : $colorAccent }}">
                            <i class="ph-bold {{ $area->icono ?? 'ph-buildings' }} text-xl"></i>
                        </div>
                        <div>
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-[9px] font-black border uppercase {{ $this->obtenerColorTipo($area->tipo_area) }}">
                                {{ $area->tipo_area }}
                            </span>
                            <h3 class="mt-1 text-base font-black text-[#2F3E5C] leading-snug group-hover:text-[#E27D60] transition-colors duration-200">
                                {{ $area->nombre }}
                            </h3>
                        </div>
                    </div>

                    {{-- Badge de Estado Inactivo --}}
                    @if($esInactiva)
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[8px] font-black text-red-700 uppercase tracking-widest border border-red-200">
                            Inactiva
                        </span>
                    @endif
                </div>

                {{-- DESCRIPCIÓN --}}
                <p class="mt-4 text-xs font-semibold text-[#7C7168] line-clamp-3 leading-relaxed">
                    {{ $area->descripcion }}
                </p>

                {{-- RESPONSABLE --}}
                <div class="mt-5 rounded-2xl bg-[#F4EFEA]/60 p-3 flex items-center justify-between border border-[#C7B5A3]/30">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-[#2F3E5C] text-[10px] font-black text-white">
                            {{ $area->responsable ? substr($area->responsable->nombres, 0, 1) . substr($area->responsable->ap_paterno, 0, 1) : '?' }}
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-[#967B66] uppercase tracking-wider">Responsable</p>
                            <p class="text-[10px] font-black text-[#2F3E5C] truncate max-w-[140px]">
                                {{ $area->responsable ? $area->responsable->name : 'Sin Responsable' }}
                            </p>
                        </div>
                    </div>
                    
                    {{-- Contador Personal --}}
                    <div class="text-right">
                        <p class="text-[9px] font-black text-[#967B66] uppercase tracking-wider">Personal</p>
                        <span class="inline-flex items-center gap-1 text-[11px] font-black text-[#E27D60]">
                            <i class="ph-bold ph-users-three"></i>
                            {{ $area->usuarios_count }}
                        </span>
                    </div>
                </div>

                {{-- LISTA DE ROLES SUGERIDOS --}}
                @if($area->roles_sugeridos)
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach($area->roles_sugeridos as $rol)
                            <span class="rounded-full bg-[#E6DDD3]/50 px-2.5 py-0.5 text-[9px] font-black text-[#7C7168] border border-[#C7B5A3]/40">
                                {{ $rol }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- ACCIONES DE CARD --}}
                <div class="mt-6 pt-4 border-t border-[#C7B5A3]/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button"
                                wire:click="verArea('{{ $area->cod_area }}')"
                                class="inline-flex h-8 items-center gap-1.5 rounded-full bg-[#2F3E5C]/10 px-3 text-[10px] font-black text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition duration-200"
                                title="Ver Ficha Completa">
                            <i class="ph-bold ph-eye"></i>
                            Ver ficha
                        </button>

                        @can('areas.reportes')
                            <button type="button"
                                    wire:click="generarReporteEspecifico('{{ $area->cod_area }}')"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#E6DDD3]/40 text-[#7C7168] hover:bg-[#E6DDD3] transition duration-200"
                                    title="Imprimir Reporte">
                                <i class="ph-bold ph-printer"></i>
                            </button>
                        @endcan
                    </div>

                    <div class="flex items-center gap-1.5">
                        @can('areas.editar')
                            <button type="button"
                                    wire:click="editarArea('{{ $area->cod_area }}')"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#E27D60]/10 text-[#E27D60] hover:bg-[#E27D60] hover:text-white transition duration-200"
                                    title="Editar Área">
                                <i class="ph-bold ph-pencil-simple"></i>
                            </button>
                        @endcan

                        @can('areas.cambiar_estado')
                            <button type="button"
                                    wire:click="toggleEstado('{{ $area->cod_area }}')"
                                    wire:confirm="¿Está seguro de cambiar el estado de este área institucional?"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full transition duration-200 {{ $esInactiva ? 'bg-[#8DA280]/20 text-[#63775B] hover:bg-[#8DA280] hover:text-white' : 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-white' }}"
                                    title="{{ $esInactiva ? 'Activar Área' : 'Desactivar Área' }}">
                                <i class="ph-bold {{ $esInactiva ? 'ph-power' : 'ph-x-circle' }}"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-3xl bg-white p-12 text-center shadow-[0_12px_24px_rgba(0,0,0,0.02)]">
                <div class="flex justify-center text-5xl text-[#C7B5A3] mb-4">
                    <i class="ph-bold ph-folder-open"></i>
                </div>
                <h3 class="text-base font-black text-[#2F3E5C]">No se encontraron áreas institucionales</h3>
                <p class="text-xs font-semibold text-[#967B66] mt-1">Intenta ajustando los filtros de búsqueda o registra una nueva área.</p>
            </div>
        @endforelse
    </section>

    {{-- FICHA LATERAL DETALLE DEL ÁREA --}}
    @if($mostrarFicha && $areaSeleccionada)
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                {{-- Fondo oscuro --}}
                <div class="absolute inset-0 bg-[#2F3E5C]/40 backdrop-blur-sm transition-opacity" wire:click="cerrarFicha"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-md transform bg-[#FDFBF9] shadow-[0_20px_60px_rgba(47,62,92,0.25)] transition duration-500 ease-in-out">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white">
                            
                            {{-- Cabecera Ficha --}}
                            <div class="relative p-6 text-white" style="background-color: {{ $areaSeleccionada->color ?? '#2F3E5C' }}">
                                <button type="button"
                                        wire:click="cerrarFicha"
                                        class="absolute top-6 right-6 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white hover:bg-white hover:text-[#2F3E5C] transition duration-200">
                                    <i class="ph-bold ph-x text-sm"></i>
                                </button>

                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-white border border-white/20">
                                        <i class="ph-bold {{ $areaSeleccionada->icono ?? 'ph-buildings' }} text-lg"></i>
                                    </div>
                                    <div>
                                        <span class="inline-block rounded-full bg-white/20 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-white">
                                            {{ $areaSeleccionada->tipo_area }}
                                        </span>
                                        <h2 class="text-lg font-black mt-0.5 leading-tight">{{ $areaSeleccionada->nombre }}</h2>
                                    </div>
                                </div>
                            </div>

                            {{-- Cuerpo Ficha --}}
                            <div class="flex-1 p-6 space-y-6 overflow-y-auto bg-[#F8F3ED]/30">
                                {{-- Descripción --}}
                                <div class="space-y-1.5">
                                    <h4 class="text-[10px] font-black text-[#967B66] uppercase tracking-wider">Descripción del Área</h4>
                                    <p class="text-xs font-semibold text-[#7C7168] bg-white p-3 rounded-2xl shadow-sm border border-[#C7B5A3]/20 leading-relaxed">
                                        {{ $areaSeleccionada->descripcion }}
                                    </p>
                                </div>

                                {{-- Responsable --}}
                                <div class="space-y-1.5">
                                    <h4 class="text-[10px] font-black text-[#967B66] uppercase tracking-wider">Responsable Asignado</h4>
                                    <div class="flex items-center gap-3 bg-white p-3 rounded-2xl shadow-sm border border-[#C7B5A3]/20">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#E27D60] text-xs font-black text-white shadow-sm">
                                            {{ $areaSeleccionada->responsable ? substr($areaSeleccionada->responsable->nombres, 0, 1) . substr($areaSeleccionada->responsable->ap_paterno, 0, 1) : '?' }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-[#2F3E5C]">
                                                {{ $areaSeleccionada->responsable ? $areaSeleccionada->responsable->name : 'Sin Responsable' }}
                                            </p>
                                            <p class="text-[9px] font-black text-[#967B66] uppercase tracking-wider">
                                                {{ $areaSeleccionada->responsable ? ($areaSeleccionada->responsable->getRoleNames()->first() ?? 'Sin Rol') : 'Área Operativa Húerfana' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sugerencias Institucionales --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-[10px] font-black text-[#967B66] uppercase tracking-wider mb-1.5">Roles Sugeridos</h4>
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($areaSeleccionada->roles_sugeridos ?? [] as $rol)
                                                <span class="rounded-full bg-[#2F3E5C]/5 px-2 py-0.5 text-[9px] font-black text-[#2F3E5C] border border-[#2F3E5C]/10">
                                                    {{ $rol }}
                                                </span>
                                            @empty
                                                <span class="text-[10px] font-semibold text-[#967B66] italic">Ninguno</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-[#967B66] uppercase tracking-wider mb-1.5">Módulos Clave</h4>
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($areaSeleccionada->modulos_relacionados ?? [] as $mod)
                                                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[9px] font-black text-purple-700 border border-purple-100">
                                                    {{ $mod }}
                                                </span>
                                            @empty
                                                <span class="text-[10px] font-semibold text-[#967B66] italic">Ninguno</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                {{-- Usuarios Vinculados --}}
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-[10px] font-black text-[#967B66] uppercase tracking-wider">Personal Vinculado ({{ count($areaSeleccionada->usuarios) }})</h4>
                                    </div>
                                    
                                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                        @forelse($areaSeleccionada->usuarios as $u)
                                            <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-[#C7B5A3]/25 shadow-sm transition hover:border-[#E27D60]/55">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#E6DDD3] text-[10px] font-black text-[#7C7168]">
                                                        {{ substr($u->nombres, 0, 1) . substr($u->ap_paterno, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h5 class="text-xs font-black text-[#2F3E5C] leading-snug">{{ $u->name }}</h5>
                                                        <p class="text-[9px] font-black text-[#967B66] uppercase tracking-wider">
                                                            {{ $u->getRoleNames()->first() ?? 'Sin Rol asignado' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <span class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-wider border {{ $u->estado == 1 ? 'bg-[#8DA280]/20 text-[#63775B] border-[#8DA280]/30' : 'bg-red-50 text-red-600 border-red-100' }}">
                                                    {{ $u->estado == 1 ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="rounded-xl border border-dashed border-[#C7B5A3] p-6 text-center text-xs font-semibold text-[#967B66] bg-white/40">
                                                No hay personal vinculado a este área en este momento.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Observaciones --}}
                                @if($areaSeleccionada->observaciones)
                                    <div class="space-y-1.5">
                                        <h4 class="text-[10px] font-black text-[#967B66] uppercase tracking-wider">Observaciones Técnicas</h4>
                                        <p class="text-xs font-semibold text-[#7C7168] bg-[#FDFBF9] p-3 rounded-2xl border border-orange-200/50 leading-relaxed italic">
                                            "{{ $areaSeleccionada->observaciones }}"
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Acciones Ficha --}}
                            <div class="border-t border-[#C7B5A3]/30 p-4 bg-white flex items-center justify-between">
                                <span class="text-[10px] font-bold text-[#967B66]">ID: {{ $areaSeleccionada->cod_area }}</span>
                                <div class="flex items-center gap-2">
                                    @can('areas.editar')
                                        <button type="button"
                                                wire:click="editarArea('{{ $areaSeleccionada->cod_area }}')"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-[#E27D60] px-4 py-2 text-xs font-black text-white hover:bg-[#d86c50] shadow transition duration-200">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                            Editar
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DE CREACIÓN / EDICIÓN --}}
    @if($mostrarFormulario)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" role="dialog" aria-modal="true">
            {{-- Fondo Oscuro --}}
            <div class="fixed inset-0 bg-[#2F3E5C]/40 backdrop-blur-sm transition-opacity" wire:click="cerrarFormulario"></div>

            {{-- Contenedor del Modal --}}
            <div class="relative w-full max-w-2xl rounded-[2.2rem] bg-white p-6 shadow-[0_20px_50px_rgba(47,62,92,0.2)] border border-[#C7B5A3]/40 transform transition-all duration-300">
                
                {{-- Encabezado Modal --}}
                <header class="flex items-center justify-between pb-4 border-b border-[#C7B5A3]/30">
                    <div>
                        <h3 class="text-xl font-black text-[#2F3E5C]">{{ $isEdit ? 'Editar Área Institucional' : 'Nueva Área Institucional' }}</h3>
                        <p class="text-xs font-semibold text-[#967B66] mt-0.5">Por favor, rellene las especificaciones funcionales del área.</p>
                    </div>
                    <button type="button"
                            wire:click="cerrarFormulario"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F3EEE8] text-[#7C7168] hover:bg-[#E27D60] hover:text-white transition duration-200">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </header>

                {{-- Formulario --}}
                <form wire:submit.prevent="guardarArea" class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 max-h-[60vh] overflow-y-auto p-1 pr-2 [scrollbar-width:thin] [scrollbar-color:#C7B5A3_transparent]">
                        
                        {{-- ── SECCIÓN 1: DATOS BÁSICOS ── --}}
                        <div class="space-y-4 md:col-span-2">
                            <h4 class="text-[10px] font-black text-[#E27D60] uppercase tracking-widest border-b border-[#E27D60]/20 pb-1">1. Datos Básicos</h4>
                        </div>

                        {{-- Nombre --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Nombre de la Área</label>
                            <input type="text"
                                   wire:model="nombre"
                                   placeholder="Ej: Área de Fisioterapia y Rehabilitación"
                                   class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60] focus:ring-[#E27D60]">
                            @error('nombre') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tipo de Área --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Tipo de Área</label>
                            <select wire:model="tipo_area"
                                    class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60] focus:ring-[#E27D60]">
                                <option value="Administrativa">Administrativa</option>
                                <option value="Salud">Salud</option>
                                <option value="Social">Social</option>
                                <option value="Soporte">Soporte</option>
                            </select>
                            @error('tipo_area') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Responsable --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Responsable del Área</label>
                            <select wire:model="responsable_id"
                                    class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60] focus:ring-[#E27D60]">
                                <option value="">-- Sin asignar responsable --</option>
                                @foreach($responsablesDisponibles as $resp)
                                    <option value="{{ $resp->cod_usu }}">{{ $resp->name }}</option>
                                @endforeach
                            </select>
                            @error('responsable_id') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Descripción Funcional</label>
                            <textarea wire:model="descripcion"
                                      rows="3"
                                      placeholder="Describa el rol operativo, alcances y responsabilidades institucionales del área..."
                                      class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60] focus:ring-[#E27D60]"></textarea>
                            @error('descripcion') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- ── SECCIÓN 2: PRIVILEGIOS Y SUGERENCIAS ── --}}
                        <div class="space-y-4 md:col-span-2 mt-2">
                            <h4 class="text-[10px] font-black text-[#E27D60] uppercase tracking-widest border-b border-[#E27D60]/20 pb-1">2. Privilegios e Interacciones</h4>
                        </div>

                        {{-- Roles Sugeridos --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Roles Sugeridos</label>
                            <div class="rounded-xl border border-[#C7B5A3]/50 p-3 bg-[#F8F3ED]/20 max-h-36 overflow-y-auto space-y-1.5">
                                @foreach($rolesDisponibles as $rol)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                               wire:model="roles_sugeridos"
                                               value="{{ $rol }}"
                                               class="rounded border-[#C7B5A3]/80 text-[#E27D60] focus:ring-[#E27D60]">
                                        <span class="text-xs font-semibold text-[#7C7168]">{{ $rol }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('roles_sugeridos') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Módulos Relacionados --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Módulos del Sistema Vinculados</label>
                            <div class="rounded-xl border border-[#C7B5A3]/50 p-3 bg-[#F8F3ED]/20 max-h-36 overflow-y-auto space-y-1.5">
                                @foreach($modulosDisponibles as $key => $val)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox"
                                               wire:model="modulos_relacionados"
                                               value="{{ $key }}"
                                               class="rounded border-[#C7B5A3]/80 text-[#E27D60] focus:ring-[#E27D60]">
                                        <span class="text-xs font-semibold text-[#7C7168]">{{ $val }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('modulos_relacionados') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- ── SECCIÓN 3: DISEÑO Y ORDEN ── --}}
                        <div class="space-y-4 md:col-span-2 mt-2">
                            <h4 class="text-[10px] font-black text-[#E27D60] uppercase tracking-widest border-b border-[#E27D60]/20 pb-1">3. Configuración de Diseño y Orden</h4>
                        </div>

                        {{-- Color --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Color Representativo (Hexadecimal)</label>
                            <div class="flex gap-2">
                                <input type="color"
                                       wire:model="color"
                                       class="h-9 w-12 rounded-xl border border-[#C7B5A3]/50 bg-transparent p-0 cursor-pointer">
                                <input type="text"
                                       wire:model="color"
                                       placeholder="#FFFFFF"
                                       class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60]">
                            </div>
                            @error('color') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Icono --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Icono Phosphor (Ej: ph-brain)</label>
                            <input type="text"
                                   wire:model="icono"
                                   placeholder="ph-stethoscope, ph-buildings..."
                                   class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60]">
                            @error('icono') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Orden --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Orden de Visualización</label>
                            <input type="number"
                                   wire:model="orden"
                                   min="0"
                                   class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60]">
                            @error('orden') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estado --}}
                        <div>
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Estado de Área</label>
                            <select wire:model="estado"
                                    class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60]">
                                <option value="ACTIVA">ACTIVA</option>
                                <option value="INACTIVA">INACTIVA</option>
                            </select>
                            @error('estado') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        {{-- Observaciones internas --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-[#2F3E5C] mb-1">Observaciones Técnicas Internas</label>
                            <textarea wire:model="observaciones"
                                      rows="2"
                                      placeholder="Solo visible para administradores..."
                                      class="w-full rounded-xl border-[#C7B5A3]/50 py-2 text-xs font-semibold text-[#2F3E5C] shadow-sm focus:border-[#E27D60]"></textarea>
                            @error('observaciones') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <footer class="flex items-center justify-end gap-2 pt-4 border-t border-[#C7B5A3]/30">
                        <button type="button"
                                wire:click="cerrarFormulario"
                                class="rounded-full border-2 border-[#C7B5A3] bg-white px-5 py-2 text-xs font-black text-[#7C7168] shadow-sm transition hover:bg-[#F3EEE8]">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-full bg-[#E27D60] px-6 py-2 text-xs font-black text-white shadow-lg transition hover:bg-[#d86c50]">
                            Guardar cambios
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL / PANEL DE REPORTES INTERACTIVOS --}}
    @if($mostrarReportes)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" role="dialog" aria-modal="true">
            {{-- Fondo Oscuro --}}
            <div class="fixed inset-0 bg-[#2F3E5C]/40 backdrop-blur-sm transition-opacity" wire:click="cerrarReportes"></div>

            {{-- Contenedor del Modal --}}
            <div class="relative w-full max-w-4xl rounded-[2.2rem] bg-white p-6 shadow-[0_20px_50px_rgba(47,62,92,0.2)] border border-[#C7B5A3]/40 transform transition-all duration-300">
                
                {{-- Encabezado Modal --}}
                <header class="flex items-center justify-between pb-4 border-b border-[#C7B5A3]/30 print:hidden">
                    <div>
                        <h3 class="text-xl font-black text-[#2F3E5C]">Reportes del Módulo</h3>
                        <p class="text-xs font-semibold text-[#967B66] mt-0.5">Genere y consulte reportes técnicos estructurados de Casa Amandita.</p>
                    </div>
                    <div class="flex gap-2">
                        @if($reporteTipo)
                            <button type="button"
                                    onclick="window.print()"
                                    class="flex h-8 items-center gap-2 rounded-lg bg-[#63775B] px-3 text-xs font-black text-white hover:bg-[#52624b] transition duration-200">
                                <i class="ph-bold ph-printer"></i>
                                Imprimir / PDF
                            </button>
                        @endif
                        <button type="button"
                                wire:click="cerrarReportes"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-[#F3EEE8] text-[#7C7168] hover:bg-[#E27D60] hover:text-white transition duration-200">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>
                </header>

                {{-- Menú de Reportes --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 print:hidden">
                    <button type="button"
                            wire:click="generarReporteGeneral"
                            class="flex flex-col items-center justify-center p-4 rounded-2xl border text-center transition {{ $reporteTipo === 'general' ? 'border-[#E27D60] bg-[#E27D60]/5 text-[#E27D60]' : 'border-[#C7B5A3]/40 bg-[#F8F3ED]/10 text-[#7C7168] hover:bg-[#F3EEE8]' }}">
                        <i class="ph-bold ph-newspaper text-2xl mb-1"></i>
                        <span class="text-xs font-black">Reporte General</span>
                        <span class="text-[9px] font-medium text-[#967B66] mt-1">Estructura global de áreas</span>
                    </button>

                    <button type="button"
                            wire:click="generarReporteUsuarios"
                            class="flex flex-col items-center justify-center p-4 rounded-2xl border text-center transition {{ $reporteTipo === 'usuarios' ? 'border-[#E27D60] bg-[#E27D60]/5 text-[#E27D60]' : 'border-[#C7B5A3]/40 bg-[#F8F3ED]/10 text-[#7C7168] hover:bg-[#F3EEE8]' }}">
                        <i class="ph-bold ph-users-three text-2xl mb-1"></i>
                        <span class="text-xs font-black">Personal por Área</span>
                        <span class="text-[9px] font-medium text-[#967B66] mt-1">Distribución del personal</span>
                    </button>

                    <button type="button"
                            wire:click="generarReporteDistribucion"
                            class="flex flex-col items-center justify-center p-4 rounded-2xl border text-center transition {{ $reporteTipo === 'distribucion' ? 'border-[#E27D60] bg-[#E27D60]/5 text-[#E27D60]' : 'border-[#C7B5A3]/40 bg-[#F8F3ED]/10 text-[#7C7168] hover:bg-[#F3EEE8]' }}">
                        <i class="ph-bold ph-chart-pie text-2xl mb-1"></i>
                        <span class="text-xs font-black">Distribución Operativa</span>
                        <span class="text-[9px] font-medium text-[#967B66] mt-1">Indicadores e impacto</span>
                    </button>

                    <button type="button"
                            wire:click="generarReporteSinResponsable"
                            class="flex flex-col items-center justify-center p-4 rounded-2xl border text-center transition {{ $reporteTipo === 'sin_responsable' ? 'border-[#E27D60] bg-[#E27D60]/5 text-[#E27D60]' : 'border-[#C7B5A3]/40 bg-[#F8F3ED]/10 text-[#7C7168] hover:bg-[#F3EEE8]' }}">
                        <i class="ph-bold ph-warning-circle text-2xl mb-1"></i>
                        <span class="text-xs font-black">Alertas del Organigrama</span>
                        <span class="text-[9px] font-medium text-[#967B66] mt-1">Áreas sin responsable</span>
                    </button>
                </div>

                {{-- Contenedor del Reporte Imprimible --}}
                <div class="mt-6 p-6 rounded-2xl border border-[#C7B5A3]/40 max-h-[50vh] overflow-y-auto bg-white print:max-h-none print:border-none print:p-0 print:m-0">
                    @if($reporteTipo === 'general')
                        <div class="space-y-4">
                            <div class="text-center pb-4 border-b border-[#C7B5A3]/50">
                                <h2 class="text-lg font-black text-[#2F3E5C]">REPORTE GENERAL DE ÁREAS INSTITUCIONALES</h2>
                                <p class="text-xs text-[#967B66]">CASA AMANDITA - REMEMBERMIND</p>
                                <p class="text-[10px] text-[#967B66] mt-1">Fecha: {{ date('d/m/Y H:i') }} | Generado por: {{ Auth::user()->name }}</p>
                            </div>

                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-[#F8F3ED] text-[#2F3E5C] font-black border-b border-[#C7B5A3]/50">
                                        <th class="p-3">Código</th>
                                        <th class="p-3">Área</th>
                                        <th class="p-3">Tipo</th>
                                        <th class="p-3">Responsable</th>
                                        <th class="p-3 text-center">Personal</th>
                                        <th class="p-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#C7B5A3]/30">
                                    @foreach($reporteData as $rep)
                                        <tr class="hover:bg-[#F8F3ED]/30">
                                            <td class="p-3 font-bold">{{ $rep['cod_area'] }}</td>
                                            <td class="p-3 font-black text-[#2F3E5C]">{{ $rep['nombre'] }}</td>
                                            <td class="p-3">{{ $rep['tipo_area'] }}</td>
                                            <td class="p-3">{{ $rep['responsable']['nombres'] ?? 'Sin Responsable' }} {{ $rep['responsable']['ap_paterno'] ?? '' }}</td>
                                            <td class="p-3 text-center font-bold text-[#E27D60]">{{ $rep['usuarios_count'] }}</td>
                                            <td class="p-3 font-black text-[10px] {{ $rep['estado'] === 'ACTIVA' ? 'text-[#63775B]' : 'text-red-600' }}">{{ $rep['estado'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($reporteTipo === 'usuarios')
                        <div class="space-y-4">
                            <div class="text-center pb-4 border-b border-[#C7B5A3]/50">
                                <h2 class="text-lg font-black text-[#2F3E5C]">REPORTE DE PERSONAL POR ÁREA INSTITUCIONAL</h2>
                                <p class="text-xs text-[#967B66]">CASA AMANDITA - REMEMBERMIND</p>
                                <p class="text-[10px] text-[#967B66] mt-1">Fecha: {{ date('d/m/Y H:i') }} | Generado por: {{ Auth::user()->name }}</p>
                            </div>

                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-[#F8F3ED] text-[#2F3E5C] font-black border-b border-[#C7B5A3]/50">
                                        <th class="p-3">Personal</th>
                                        <th class="p-3">Área Vinculada</th>
                                        <th class="p-3">Rol</th>
                                        <th class="p-3">Estado</th>
                                        <th class="p-3">Último Acceso</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#C7B5A3]/30">
                                    @foreach($reporteData as $rep)
                                        <tr class="hover:bg-[#F8F3ED]/30">
                                            <td class="p-3 font-black text-[#2F3E5C]">{{ $rep['nombre_completo'] }}</td>
                                            <td class="p-3 font-bold text-[#967B66]">{{ $rep['area'] }}</td>
                                            <td class="p-3 font-semibold">{{ $rep['rol'] }}</td>
                                            <td class="p-3 font-black text-[10px] {{ $rep['estado'] === 'ACTIVO' ? 'text-[#63775B]' : 'text-red-600' }}">{{ $rep['estado'] }}</td>
                                            <td class="p-3 text-gray-500">{{ $rep['ultimo_acceso'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($reporteTipo === 'distribucion')
                        <div class="space-y-6">
                            <div class="text-center pb-4 border-b border-[#C7B5A3]/50">
                                <h2 class="text-lg font-black text-[#2F3E5C]">REPORTE DE DISTRIBUCIÓN Y MÉTRICAS INSTITUCIONALES</h2>
                                <p class="text-xs text-[#967B66]">CASA AMANDITA - REMEMBERMIND</p>
                                <p class="text-[10px] text-[#967B66] mt-1">Fecha: {{ date('d/m/Y H:i') }} | Generado por: {{ Auth::user()->name }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-[#F8F3ED]/50 p-4 rounded-xl border border-[#C7B5A3]/30 space-y-2">
                                    <h4 class="text-xs font-black text-[#2F3E5C] uppercase tracking-wider">Métricas del Organigrama</h4>
                                    <ul class="space-y-1 text-xs">
                                        <li class="flex justify-between"><span>Total Áreas:</span> <strong>{{ $reporteData['total_areas'] }}</strong></li>
                                        <li class="flex justify-between"><span>Áreas Activas:</span> <strong class="text-[#63775B]">{{ $reporteData['activas'] }}</strong></li>
                                        <li class="flex justify-between"><span>Áreas Inactivas:</span> <strong class="text-red-600">{{ $reporteData['inactivas'] }}</strong></li>
                                        <li class="flex justify-between"><span>Áreas sin Responsable:</span> <strong class="text-yellow-600">{{ $reporteData['sin_responsable'] }}</strong></li>
                                    </ul>
                                </div>

                                <div class="bg-[#F8F3ED]/50 p-4 rounded-xl border border-[#C7B5A3]/30 space-y-2">
                                    <h4 class="text-xs font-black text-[#2F3E5C] uppercase tracking-wider">Líderes de Operación</h4>
                                    <p class="text-xs font-semibold">Área con mayor dotación de personal:</p>
                                    <p class="text-sm font-black text-[#E27D60]">{{ $reporteData['mas_usuarios'] }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <h4 class="text-xs font-black text-[#2F3E5C] uppercase tracking-wider">Distribución por Tipo de Área</h4>
                                <table class="w-full text-left text-xs border border-[#C7B5A3]/30">
                                    <thead>
                                        <tr class="bg-[#F8F3ED] border-b border-[#C7B5A3]/30 font-black text-[#2F3E5C]">
                                            <th class="p-2">Tipo de Área</th>
                                            <th class="p-2 text-center">Cantidad de Áreas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#C7B5A3]/30">
                                        @foreach($reporteData['tipos'] as $tipo)
                                            <tr>
                                                <td class="p-2 font-bold">{{ $tipo['tipo_area'] }}</td>
                                                <td class="p-2 text-center font-bold text-[#E27D60]">{{ $tipo['total'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @elseif($reporteTipo === 'sin_responsable')
                        <div class="space-y-4">
                            <div class="text-center pb-4 border-b border-[#C7B5A3]/50">
                                <h2 class="text-lg font-black text-[#2F3E5C]">ÁREAS ACTIVAS SIN LIDERAZGO REGISTRADO</h2>
                                <p class="text-xs text-[#967B66]">CASA AMANDITA - REMEMBERMIND</p>
                                <p class="text-[10px] text-[#967B66] mt-1">Fecha: {{ date('d/m/Y H:i') }} | Generado por: {{ Auth::user()->name }}</p>
                            </div>

                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-[#F8F3ED] text-[#2F3E5C] font-black border-b border-[#C7B5A3]/50">
                                        <th class="p-3">Código</th>
                                        <th class="p-3">Área sin Responsable</th>
                                        <th class="p-3">Tipo</th>
                                        <th class="p-3 text-center">Personal</th>
                                        <th class="p-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#C7B5A3]/30">
                                    @forelse($reporteData as $rep)
                                        <tr class="hover:bg-yellow-50/55">
                                            <td class="p-3 font-bold text-yellow-700">{{ $rep['cod_area'] }}</td>
                                            <td class="p-3 font-black text-[#2F3E5C]">{{ $rep['nombre'] }}</td>
                                            <td class="p-3">{{ $rep['tipo_area'] }}</td>
                                            <td class="p-3 text-center font-bold text-[#E27D60]">{{ $rep['usuarios_count'] }}</td>
                                            <td class="p-3 font-black text-[10px] text-yellow-600">{{ $rep['estado'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-6 text-center text-xs font-bold text-[#63775B] bg-[#8DA280]/10">
                                                🎉 Excelente: Todas las áreas activas cuentan con un líder o responsable registrado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @elseif($reporteTipo === 'especifico')
                        <div class="space-y-4">
                            <div class="text-center pb-4 border-b border-[#C7B5A3]/50">
                                <h2 class="text-lg font-black text-[#2F3E5C] uppercase">REPORTE DETALLADO: {{ $reporteData['nombre'] }}</h2>
                                <p class="text-xs text-[#967B66]">CASA AMANDITA - REMEMBERMIND</p>
                                <p class="text-[10px] text-[#967B66] mt-1">Fecha: {{ date('d/m/Y H:i') }} | Generado por: {{ Auth::user()->name }}</p>
                            </div>

                            <div class="bg-[#F8F3ED]/40 p-4 rounded-xl border border-[#C7B5A3]/30 space-y-2 text-xs">
                                <p><strong>Código de Área:</strong> {{ $reporteData['cod_area'] }}</p>
                                <p><strong>Tipo de Área:</strong> {{ $reporteData['tipo_area'] }}</p>
                                <p><strong>Responsable:</strong> {{ $reporteData['responsable'] }}</p>
                                <p><strong>Estado:</strong> {{ $reporteData['estado'] }}</p>
                                <p class="mt-2 leading-relaxed"><strong>Descripción Operativa:</strong> {{ $reporteData['descripcion'] }}</p>
                            </div>

                            <div class="space-y-2">
                                <h4 class="text-xs font-black text-[#2F3E5C] uppercase tracking-wider">Personal Vinculado</h4>
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="bg-[#F8F3ED] border-b border-[#C7B5A3]/30 font-black text-[#2F3E5C]">
                                            <th class="p-2">Personal</th>
                                            <th class="p-2">Rol Asignado</th>
                                            <th class="p-2">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#C7B5A3]/30">
                                        @forelse($reporteData['usuarios'] as $u)
                                            <tr>
                                                <td class="p-2 font-bold">{{ $u['nombre'] }}</td>
                                                <td class="p-2">{{ $u['rol'] }}</td>
                                                <td class="p-2 text-[10px] font-black {{ $u['estado'] === 'ACTIVO' ? 'text-[#63775B]' : 'text-red-600' }}">{{ $u['estado'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="p-4 text-center text-[#967B66] italic">No hay personal vinculado a este área en este momento.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12 text-[#967B66] italic text-xs">
                            <i class="ph-bold ph-arrow-circle-up text-3xl mb-2 block"></i>
                            Seleccione un tipo de reporte de la barra superior para procesar la información.
                        </div>
                    @endif
                </div>

                {{-- Botón Cerrar Modal Reportes --}}
                <footer class="flex items-center justify-end mt-4 pt-4 border-t border-[#C7B5A3]/30 print:hidden">
                    <button type="button"
                            wire:click="cerrarReportes"
                            class="rounded-full bg-[#2F3E5C] px-6 py-2 text-xs font-black text-white shadow-lg transition hover:bg-[#1f293d]">
                        Cerrar panel
                    </button>
                </footer>
            </div>
        </div>
    @endif
</div>
