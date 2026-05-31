<div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between border-b border-borde pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
                <i class="ph-bold ph-clock-counter-clockwise text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-titulo">Historial Institucional</h2>
                <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Observaciones y últimos movimientos</p>
            </div>
        </div>
    </div>

    @if($observacionesLista->count() > 0 || $bitacoraLista->count() > 0)
        <div class="grid lg:grid-cols-2 gap-6">
            
            {{-- Notas y Observaciones --}}
            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-apoyo mb-3">Últimas Notas Relevantes</span>
                @if($observacionesLista->count() > 0)
                    <div class="space-y-4">
                        @foreach($observacionesLista->take(4) as $obs)
                            <div class="border-l-2 border-borde pl-3 py-1">
                                <p class="text-xs font-black uppercase tracking-wide text-apoyo mb-1">{{ \Carbon\Carbon::parse($obs->fecha)->format('d/m/Y') }} • {{ $obs->tipo_obs ?? 'Nota' }}</p>
                                <p class="text-xs text-titulo leading-relaxed">"{{ $obs->descripcion }}"</p>
                                @if(optional($obs->usuario)->name)
                                    <p class="text-xs font-bold text-apoyo mt-1">Por: {{ $obs->usuario->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs font-bold text-apoyo">Sin notas registradas.</p>
                @endif
            </div>

            {{-- Movimientos / Bitácora --}}
            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-apoyo mb-3">Últimos Movimientos</span>
                @if($bitacoraLista->count() > 0)
                    <div class="space-y-3 relative before:absolute before:inset-y-0 before:left-2 before:w-px before:bg-fondo-app">
                        @foreach($bitacoraLista->take(5) as $mov)
                            <div class="relative pl-6">
                                <div class="absolute left-1 top-1 h-2.5 w-2.5 rounded-full border-2 border-white {{ $mov['color_bg'] ?? 'bg-fondo-panel' }}"></div>
                                <p class="text-xs font-bold text-apoyo">{{ $mov['fecha_exacta'] ?? '--' }}</p>
                                <p class="text-xs font-black text-titulo mt-0.5">{{ $mov['etiqueta'] ?? 'Actualización' }}</p>
                                <p class="text-xs text-apoyo truncate">{{ $mov['descripcion'] ?? 'Movimiento registrado en sistema' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs font-bold text-apoyo">Sin movimientos recientes.</p>
                @endif
            </div>
            
        </div>
        
        <div class="text-center mt-6 border-t border-borde pt-4">
            <p class="text-xs font-bold text-apoyo uppercase tracking-wide">El historial se actualiza automáticamente.</p>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo mb-4">
                <i class="ph-bold ph-clock-counter-clockwise text-3xl"></i>
            </div>
            <p class="text-sm font-bold text-apoyo">No hay historial institucional disponible.</p>
        </div>
    @endif
</div>
