<div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between border-b border-[#CBBBAA]/30 pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#6873A6]/10 text-[#6873A6]">
                <i class="ph-bold ph-clock-counter-clockwise text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-[#2F3E5C]">Historial Institucional</h2>
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">Observaciones y últimos movimientos</p>
            </div>
        </div>
    </div>

    @if($observacionesLista->count() > 0 || $bitacoraLista->count() > 0)
        <div class="grid lg:grid-cols-2 gap-6">
            
            {{-- Notas y Observaciones --}}
            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Últimas Notas Relevantes</span>
                @if($observacionesLista->count() > 0)
                    <div class="space-y-4">
                        @foreach($observacionesLista->take(4) as $obs)
                            <div class="border-l-2 border-[#6873A6] pl-3 py-1">
                                <p class="text-xs font-black uppercase tracking-wide text-[#2F3E5C]/40 mb-1">{{ \Carbon\Carbon::parse($obs->fecha)->format('d/m/Y') }} • {{ $obs->tipo_obs ?? 'Nota' }}</p>
                                <p class="text-xs text-[#2F3E5C] leading-relaxed">"{{ $obs->descripcion }}"</p>
                                @if(optional($obs->usuario)->name)
                                    <p class="text-xs font-bold text-[#2F3E5C]/50 mt-1">Por: {{ $obs->usuario->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/40">Sin notas registradas.</p>
                @endif
            </div>

            {{-- Movimientos / Bitácora --}}
            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Últimos Movimientos</span>
                @if($bitacoraLista->count() > 0)
                    <div class="space-y-3 relative before:absolute before:inset-y-0 before:left-2 before:w-px before:bg-[#D5C7B9]">
                        @foreach($bitacoraLista->take(5) as $mov)
                            <div class="relative pl-6">
                                <div class="absolute left-1 top-1 h-2.5 w-2.5 rounded-full border-2 border-white {{ $mov['color_bg'] ?? 'bg-[#6873A6]' }}"></div>
                                <p class="text-xs font-bold text-[#2F3E5C]/50">{{ $mov['fecha_exacta'] ?? '--' }}</p>
                                <p class="text-xs font-black text-[#2F3E5C] mt-0.5">{{ $mov['etiqueta'] ?? 'Actualización' }}</p>
                                <p class="text-xs text-[#2F3E5C]/70 truncate">{{ $mov['descripcion'] ?? 'Movimiento registrado en sistema' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/40">Sin movimientos recientes.</p>
                @endif
            </div>
            
        </div>
        
        <div class="text-center mt-6 border-t border-[#CBBBAA]/20 pt-4">
            <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">El historial se actualiza automáticamente.</p>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#F2EBE3]/50 text-[#2F3E5C]/30 mb-4">
                <i class="ph-bold ph-clock-counter-clockwise text-3xl"></i>
            </div>
            <p class="text-sm font-bold text-[#2F3E5C]/60">No hay historial institucional disponible.</p>
        </div>
    @endif
</div>
