@if($drawerUbicacion && $adultoDrawer)
<div class="fixed inset-0 z-50 overflow-hidden font-sans"
     x-data
     x-on:keydown.escape.window="$wire.cerrarDrawer()">
    <!-- Backdrop oscuro con blur suave -->
    <div class="fixed inset-0 bg-[var(--color-modal-overlay)] backdrop-blur-sm transition-opacity"
         wire:click="cerrarDrawer"></div>

    <!-- Contenedor Deslizante Lateral (Barra Derecha) -->
    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="rm-drawer pointer-events-auto flex h-full w-screen max-w-xl transform flex-col overflow-hidden transition duration-300 ease-in-out">
            
            <!-- Encabezado del Drawer con color institucional -->
            <div class="flex items-center justify-between border-b border-borde bg-fondo-hover px-6 py-4 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/30">
                        <i class="ph-bold ph-map-pin text-xl"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-titulo">Ubicación y Ficha del Residente</h3>
                        <p class="text-xs text-apoyo">Habitación, cama y cuidados del adulto mayor</p>
                    </div>
                </div>
                <button type="button"
                    wire:click="cerrarDrawer"
                    class="rm-btn-icon h-8 w-8 rounded-lg" aria-label="Cerrar panel">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Cuerpo del Drawer con Scroll y Fondo enriquecido -->
            <div class="flex-1 overflow-y-auto p-6 space-y-5 scrollbar-thin">
                
                <!-- 1. Perfil del Residente -->
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/90 dark:bg-slate-800/80 border border-blue-200/70 dark:border-blue-900/50 shadow-sm backdrop-blur-sm">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 font-extrabold text-lg shadow-sm">
                        {{ substr($adultoDrawer->nombres ?? 'A', 0, 1) }}{{ substr($adultoDrawer->ap_paterno ?? 'M', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-base font-black text-slate-800 dark:text-white truncate">
                            {{ $adultoDrawer->nombres }} {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }}
                        </h4>
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            <span class="font-mono font-bold">{{ $adultoDrawer->cod_am }}</span>
                            <span>?</span>
                            <span>{{ $adultoDrawer->genero ?? 'Adulto Mayor' }}</span>
                            @if($adultoDrawer->fecha_nacimiento)
                                <span>?</span>
                                <span>{{ $adultoDrawer->fecha_nacimiento->age }} años</span>
                            @elseif($adultoDrawer->edad)
                                <span>?</span>
                                <span>{{ $adultoDrawer->edad }} años</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. Tarjeta Destacada: DÓNDE ESTÁ EL PACIENTE con color y contraste -->
                <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-100/90 via-sky-50 to-indigo-100/80 dark:from-slate-800/95 dark:via-blue-950/50 dark:to-slate-800/90 border border-blue-300/90 dark:border-blue-800/70 shadow-md space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-blue-950 dark:text-blue-300 flex items-center gap-1.5">
                            <i class="ph-bold ph-door text-base text-blue-600"></i>
                            Ubicación Física en Residencia
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-sm">
                            Cama Asignada
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-white/95 dark:bg-slate-900/90 rounded-xl border border-blue-200 dark:border-blue-900/60 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Habitación</span>
                            <p class="font-black text-slate-800 dark:text-white text-sm">
                                {{ $adultoDrawer->habitacion?->nombre ?? ($adultoDrawer->habitacion?->codigo ?? 'Sin habitación') }}
                            </p>
                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $adultoDrawer->habitacion?->codigo ?? '-' }}</p>
                        </div>

                        <div class="p-3 bg-white/95 dark:bg-slate-900/90 rounded-xl border border-blue-200 dark:border-blue-900/60 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Cama Clínica</span>
                            <p class="font-black text-slate-800 dark:text-white text-sm">
                                {{ $adultoDrawer->cama?->codigo ?? 'Sin cama' }}
                            </p>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">En Uso Activo</p>
                        </div>

                        <div class="p-3 bg-white/95 dark:bg-slate-900/90 rounded-xl border border-blue-200 dark:border-blue-900/60 col-span-2 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Pabellón / Sector</span>
                            <p class="font-bold text-slate-700 dark:text-slate-200">
                                <i class="ph-bold ph-compass mr-1 text-blue-500"></i>
                                {{ $adultoDrawer->habitacion?->ubicacion ?? 'Pabellón Central' }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Tipo: {{ $adultoDrawer->habitacion?->tipo_habitacion ?? 'INDIVIDUAL' }}</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Información Asistencial y Diagnóstica -->
                <div class="p-4 rounded-2xl bg-white/90 dark:bg-slate-800/80 border border-blue-200/70 dark:border-slate-700 shadow-sm space-y-3">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 block">
                        Condición y Cuidados Especiales
                    </span>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-amber-700 dark:text-amber-300">Dependencia</span>
                            <p class="font-bold text-slate-800 dark:text-white mt-0.5">{{ $adultoDrawer->nivel_dependencia ?? 'Moderada' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-rose-700 dark:text-rose-300">Riesgo Caídas</span>
                            <p class="font-bold text-rose-600 dark:text-rose-400 mt-0.5">{{ $adultoDrawer->riesgo_caida ?? 'Medio' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-300">Dieta</span>
                            <p class="font-bold text-slate-800 dark:text-white mt-0.5">{{ $adultoDrawer->tipo_dieta ?? 'General normos?dica' }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/50 shadow-sm">
                            <span class="text-[10px] font-bold uppercase text-purple-700 dark:text-purple-300">Alergias</span>
                            <p class="font-bold text-purple-700 dark:text-purple-300 mt-0.5">{{ $adultoDrawer->alergias ?? 'Ninguna registrada' }}</p>
                        </div>
                    </div>
                </div>

                <!-- 4. Historial Reciente de Alertas del Residente -->
                @if($adultoDrawer->alertas && $adultoDrawer->alertas->isNotEmpty())
                    <div class="space-y-2">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                            Alertas Clínicas Recientes ({{ $adultoDrawer->alertas->count() }})
                        </span>
                        <div class="space-y-2">
                            @foreach($adultoDrawer->alertas as $alt)
                                <div class="p-3 rounded-xl bg-white/95 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs flex items-center justify-between shadow-sm">
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white">{{ $alt->tipo_alerta }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $alt->motivo }}</p>
                                    </div>
                                    <span class="shrink-0 px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $alt->nivel === 'CRITICO' ? 'bg-red-600 text-white' : ($alt->nivel === 'ALTO' ? 'bg-amber-500 text-white' : 'bg-blue-600 text-white') }}">
                                        {{ $alt->nivel }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pie del Drawer -->
            <div class="px-6 py-4 border-t border-blue-200/70 dark:border-blue-900/50 bg-gradient-to-r from-blue-50/80 via-white/90 dark:via-slate-900/90 to-cyan-50/80 flex items-center justify-between">
                <button type="button"
                    wire:click="verGraficos('{{ $adultoDrawer->cod_am }}')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-purple-700 hover:text-purple-800 bg-purple-100/80 hover:bg-purple-200 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-purple-300 dark:border-purple-800 transition cursor-pointer shadow-sm active:scale-95">
                    <i class="ph-bold ph-chart-line-up text-sm"></i>
                    <span>Ver Gráficos Clínicos</span>
                </button>

                <button type="button"
                    wire:click="cerrarDrawer"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer shadow-sm active:scale-95">
                    Cerrar Panel
                </button>
            </div>
        </div>
    </div>
</div>
@endif
