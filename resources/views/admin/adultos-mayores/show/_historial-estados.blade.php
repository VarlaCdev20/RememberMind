{{-- TAB HISTORIAL INSTITUCIONAL (RESUMEN) --}}
<section
    x-show="tab === 'historial'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#6873A6]/10 text-[#6873A6]">
                <i class="ph-fill ph-clock-counter-clockwise text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Historial Institucional Resumido</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Últimos movimientos, trazabilidad y cambios de estado.</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-[#CBBBAA]/50 px-4 py-2.5 text-xs font-black text-[#2F3E5C] shadow-sm transition hover:-translate-y-0.5 hover:bg-[#F2EBE3] active:scale-[0.98]">
                <i class="ph-bold ph-magnifying-glass text-lg"></i>
                Ver Trazabilidad Completa
            </a>
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-center text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/50">Estado Actual</p>
            <p class="mt-2 text-xl font-black {{ $estadoTexto === 'ACTIVO' ? 'text-emerald-600' : 'text-[#7A5C49]' }}">
                {{ ucfirst(strtolower($estadoTexto)) }}
            </p>
            <p class="text-[10px] font-bold text-[#2F3E5C]/40 mt-1 uppercase tracking-widest">Desde: {{ $fechaIngresoFormateada }}</p>
        </div>

        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-center text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/50">Movimientos Registrados</p>
            <p class="mt-2 text-3xl font-black text-[#6873A6]">
                {{ collect($bitacoraLista)->count() }}
            </p>
        </div>

        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-center text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/50">Última Modificación</p>
            <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                @if(collect($bitacoraLista)->count() > 0)
                    @php $ultimoMov = collect($bitacoraLista)->first(); @endphp
                    <span class="block text-xs text-[#2F3E5C]/70">Por: {{ $ultimoMov['causer'] ?? 'Sistema' }}</span>
                    <span class="block mt-1 text-xs font-normal text-[#2F3E5C]/50">{{ $ultimoMov['fecha_exacta'] ?? '--' }}</span>
                @else
                    --
                @endif
            </p>
        </div>
    </div>

    <!-- ÚLTIMOS MOVIMIENTOS TIMELINE -->
    <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
        <h3 class="text-lg font-black text-[#2F3E5C] border-b border-[#CBBBAA]/30 pb-4 mb-4">Últimos Registros Relevantes</h3>
        
        @if(collect($bitacoraLista)->count() > 0)
            <div class="relative border-l-2 border-[#CBBBAA]/30 ml-3 space-y-6 pb-4">
                @foreach(collect($bitacoraLista)->take(5) as $log)
                    <div class="relative pl-6">
                        <!-- Nodo del timeline -->
                        <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white {{ $log['color_bg'] }} shadow-sm"></div>
                        
                        <div class="rounded-xl border border-[#CBBBAA]/20 bg-[#F2EBE3]/20 p-4 transition hover:bg-[#F2EBE3]/40">
                            <div class="flex items-center justify-between gap-4 mb-2">
                                <h4 class="text-sm font-black text-[#2F3E5C]"><i class="{{ $log['icono'] }} {{ $log['color_text'] }} mr-1"></i> {{ $log['etiqueta'] }}</h4>
                                <span class="text-[10px] font-bold text-[#2F3E5C]/50">{{ $log['fecha_relativa'] }}</span>
                            </div>
                            
                            @if($log['modulo'])
                                <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-[#6873A6]/10 text-[#6873A6] mb-2">
                                    {{ $log['modulo'] }}
                                </span>
                            @endif
                            
                            <p class="text-xs font-semibold text-[#2F3E5C]/70">{{ $log['descripcion'] }}</p>
                            
                            <div class="mt-3 flex items-center gap-2 text-[10px] font-bold text-[#2F3E5C]/50 border-t border-[#CBBBAA]/20 pt-3">
                                <i class="ph-bold ph-user"></i> Autor: <span class="text-[#2F3E5C]/80">{{ $log['causer'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-[#6873A6]/30 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-[#6873A6] shadow-sm transition hover:bg-[#6873A6]/10 active:scale-[0.98]">
                    Ver Bitácora Completa
                </a>
            </div>
        @else
            <div class="py-8 text-center">
                <i class="ph-fill ph-clock-counter-clockwise text-4xl text-[#C7B5A3] mb-3 block"></i>
                <p class="text-sm font-bold text-[#2F3E5C]/60">No se encontraron movimientos recientes en el historial institucional.</p>
            </div>
        @endif
    </div>
</section>