<x-sistema-layout>
    <div class="space-y-6" x-data="{ vista: 'timeline' }">
        {{-- ENCABEZADO --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-azul-profundo">
                    <i class="ph-bold ph-activity mr-2 text-terracota"></i>
                    Centro de Auditoría
                </h1>
                <p class="mt-1 text-sm font-medium text-azul-profundo/60">
                    Trazabilidad institucional y registro de eventos críticos — Casa Amandita
                </p>
            </div>
            <div class="flex items-center gap-2 p-1 bg-[#D5C7B9]/30 rounded-xl">
                <button @click="vista = 'timeline'" 
                        :class="vista === 'timeline' ? 'bg-white text-azul-profundo shadow-sm' : 'text-azul-profundo/50 hover:text-azul-profundo'"
                        class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-black transition-all">
                    <i class="ph-bold ph-clock-counter-clockwise"></i> Línea de Tiempo
                </button>
                <button @click="vista = 'table'" 
                        :class="vista === 'table' ? 'bg-white text-azul-profundo shadow-sm' : 'text-azul-profundo/50 hover:text-azul-profundo'"
                        class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-black transition-all">
                    <i class="ph-bold ph-table"></i> Tabla Detallada
                </button>
            </div>
        </div>

        {{-- FILTROS AVANZADOS --}}
        <div class="rounded-3xl border border-[#C7B5A3]/50 bg-[#E6DDD3]/80 p-5 shadow-sm backdrop-blur-md">
            <form method="GET" action="{{ route('admin.bitacora.index') }}" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    {{-- Búsqueda --}}
                    <div class="lg:col-span-2 space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 ml-1">Buscar en bitácora</label>
                        <div class="relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-azul-profundo/40"></i>
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Ej. Eulogio Condori o Inició sesión..."
                                   class="w-full rounded-xl border-[#C7B5A3] bg-white/80 pl-10 pr-4 py-2 text-sm font-bold text-azul-profundo placeholder:text-azul-profundo/30 focus:border-terracota focus:ring-4 focus:ring-terracota/10">
                        </div>
                    </div>

                    {{-- Usuario --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 ml-1">Causante</label>
                        <select name="usuario" class="w-full rounded-xl border-[#C7B5A3] bg-white/80 px-3 py-2 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-4 focus:ring-terracota/10">
                            <option value="">Todos</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->cod_usu }}" @selected(request('usuario') == $u->cod_usu)>{{ $u->nombres }} {{ $u->ap_paterno }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Módulo --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 ml-1">Módulo</label>
                        <select name="modulo" class="w-full rounded-xl border-[#C7B5A3] bg-white/80 px-3 py-2 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-4 focus:ring-terracota/10">
                            <option value="">Todos</option>
                            @foreach($modulos as $m)
                                <option value="{{ $m }}" @selected(request('modulo') == $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fecha Desde --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 ml-1">Fecha Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                               class="w-full rounded-xl border-[#C7B5A3] bg-white/80 px-3 py-2 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-4 focus:ring-terracota/10">
                    </div>

                    {{-- Fecha Hasta --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 ml-1">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                               class="w-full rounded-xl border-[#C7B5A3] bg-white/80 px-3 py-2 text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-4 focus:ring-terracota/10">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-[#C7B5A3]/20">
                    <p class="text-[10px] font-bold text-azul-profundo/40 italic">
                        Mostrando {{ $registros->count() }} de {{ $registros->total() }} eventos registrados.
                    </p>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.bitacora.index') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-[#D5C7B9] px-5 text-xs font-black text-azul-profundo transition hover:bg-[#C7B5A3] active:scale-95">
                            <i class="ph-bold ph-arrow-counter-clockwise"></i> Limpiar
                        </a>
                        <button type="submit" class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-azul-profundo px-6 text-xs font-black text-white shadow-lg shadow-azul-profundo/20 transition hover:-translate-y-0.5 active:scale-95">
                            <i class="ph-bold ph-funnel"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- CONTENIDO DINÁMICO --}}
        
        {{-- VISTA: LINEA DE TIEMPO --}}
        <div x-show="vista === 'timeline'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
            @php $currentDate = null; @endphp
            @forelse($registros as $log)
                @if($currentDate !== $log->fecha_dia)
                    @php $currentDate = $log->fecha_dia; @endphp
                    <div class="sticky top-0 z-10 flex items-center gap-4 py-2">
                        <div class="h-px flex-1 bg-[#C7B5A3]/30"></div>
                        <span class="rounded-full bg-terracota px-4 py-1 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                            {{ $log->fecha_dia }}
                        </span>
                        <div class="h-px flex-1 bg-[#C7B5A3]/30"></div>
                    </div>
                @endif

                <div class="group relative flex gap-6 pl-4 sm:pl-10">
                    {{-- Línea conectora --}}
                    <div class="absolute left-[2.45rem] top-0 h-full w-0.5 bg-[#C7B5A3]/20 group-last:h-12 hidden sm:block"></div>
                    
                    {{-- Punto de tiempo --}}
                    <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $log->modulo_info['dot'] }} text-white shadow-lg shadow-black/10 transition group-hover:scale-110">
                        <i class="ph-bold {{ $log->modulo_info['icono'] }} text-lg"></i>
                    </div>

                    {{-- Card del evento --}}
                    <div class="flex-1 rounded-2xl border border-[#C7B5A3]/30 bg-white/80 p-4 shadow-sm transition hover:border-terracota/30 hover:bg-white hover:shadow-md">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-azul-profundo">{{ $log->evento_info['nombre'] }}</span>
                                    <span class="px-2 py-0.5 rounded-lg border {{ $log->modulo_info['color'] }} text-[9px] font-black uppercase tracking-tighter flex items-center gap-1">
                                        <i class="ph-bold {{ $log->modulo_info['icono'] }}"></i>
                                        {{ $log->modulo_info['nombre'] }}
                                    </span>
                                    <span class="text-[10px] font-bold text-azul-profundo/40">— {{ $log->hora }}</span>
                                </div>
                                <h4 class="text-sm font-medium text-azul-profundo/80">
                                    {{ $log->description }}
                                </h4>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 border-l border-[#C7B5A3]/30 pl-4">
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-azul-profundo">{{ $log->causer_nombre }}</p>
                                    <p class="text-[9px] font-bold text-terracota uppercase tracking-widest">{{ $log->causer_rol }}</p>
                                </div>
                                <div class="h-8 w-8 rounded-full bg-azul-profundo/10 flex items-center justify-center text-azul-profundo font-black text-[10px]">
                                    {{ substr($log->causer_nombre, 0, 1) }}
                                </div>
                            </div>
                        </div>

                        {{-- Footer del card con el registro afectado --}}
                        <div class="mt-3 flex items-center justify-between border-t border-[#C7B5A3]/10 pt-2">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-azul-profundo/30">Registro afectado:</span>
                                <span class="text-[10px] font-bold text-azul-profundo/60">{{ $log->registro_afectado }}</span>
                            </div>
                            <span class="text-[9px] font-medium text-azul-profundo/30 italic">{{ $log->fecha_relativa }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 rounded-3xl bg-white/50 border-2 border-dashed border-[#C7B5A3]/50">
                    <i class="ph-bold ph-monitor-play text-6xl text-[#C7B5A3]/50 mb-4"></i>
                    <p class="text-lg font-black text-azul-profundo/40">No hay actividad registrada</p>
                    <p class="text-sm font-medium text-azul-profundo/30">Ajusta los filtros para ver otros periodos</p>
                </div>
            @endforelse
        </div>

        {{-- VISTA: TABLA DETALLADA --}}
        <div x-show="vista === 'table'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="overflow-hidden rounded-3xl border border-[#C7B5A3]/50 bg-white/80 shadow-xl backdrop-blur-md">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#C7B5A3]/40 bg-[#E6DDD3]/60">
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Fecha y Hora</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Usuario y Rol</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Módulo</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Acción</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-azul-profundo/60">Registro Afectado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#C7B5A3]/20">
                        @forelse($registros as $log)
                            <tr class="transition-colors hover:bg-terracota/[0.02]">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-azul-profundo">{{ $log->fecha_formateada }}</span>
                                        <span class="text-[10px] font-medium text-azul-profundo/40">{{ $log->fecha_relativa }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-xl bg-[#2F3E5C]/10 flex items-center justify-center text-azul-profundo font-black text-[10px]">
                                            {{ substr($log->causer_nombre, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-azul-profundo">{{ $log->causer_nombre }}</p>
                                            <p class="text-[10px] font-bold text-terracota uppercase tracking-widest">{{ $log->causer_rol }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border {{ $log->modulo_info['color'] }} px-2.5 py-1 text-[10px] font-black uppercase tracking-widest">
                                        <i class="ph-bold {{ $log->modulo_info['icono'] }}"></i>
                                        {{ $log->modulo_info['nombre'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5">
                                            <div class="h-2 w-2 rounded-full {{ $log->modulo_info['dot'] }}"></div>
                                            <span class="text-[10px] font-black uppercase tracking-tighter text-azul-profundo">{{ $log->evento_info['nombre'] }}</span>
                                        </div>
                                        <p class="text-xs font-medium text-azul-profundo/70 max-w-[200px] truncate" title="{{ $log->description }}">
                                            {{ $log->description }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-[10px] font-bold text-azul-profundo/60">{{ $log->registro_afectado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-20 text-center">
                                    <i class="ph-bold ph-tray text-6xl text-[#C7B5A3]/40 mb-4"></i>
                                    <p class="text-lg font-black text-azul-profundo/40">Sin resultados</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-4">
            {{ $registros->links() }}
        </div>
    </div>
</x-sistema-layout>
