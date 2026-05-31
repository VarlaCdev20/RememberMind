<div>
    <!-- Tarjeta Principal y Botón -->
    <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-titulo">Estados Institucionales</h3>
                <p class="text-sm text-apoyo">Trazabilidad de cambios (altas, bajas, hospitalizaciones).</p>
            </div>
            <button wire:click="abrirModalHistorialEstado('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl border border-borde-fuerte bg-fondo-card px-4 py-2 text-sm font-bold text-titulo shadow-sm hover:bg-fondo-panel active:scale-95 transition">
                <i class="ph-bold ph-clock-counter-clockwise"></i> Ver Historial
            </button>
        </div>
    </div>

    <x-ui.modal-livewire wire:model="showModal" title="Historial de Cambios de Estado" maxWidth="3xl">
        <x-slot name="icon">
            <i class="ph-bold ph-clock-counter-clockwise text-terracota"></i>
        </x-slot>

        <div class="space-y-4">
            @forelse($historial as $cambio)
                <div class="rounded-xl border border-borde-suave bg-fondo-card p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 h-full w-1.5 {{ $cambio->estadoNuevoRelacion?->estado === 'ACTIVO' ? 'bg-green-500' : 'bg-boton-acento' }}"></div>
                    <div class="pl-2">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-xs font-black uppercase text-apoyo">
                                    {{ $cambio->fecha_cambio->format('d/m/Y H:i') }}
                                </span>
                                <h4 class="font-bold text-titulo text-sm mt-0.5">
                                    {{ $cambio->estadoAnteriorRelacion?->estado ?? 'N/A' }} 
                                    <i class="ph-bold ph-arrow-right mx-1 text-apoyo"></i> 
                                    <span class="text-terracota">{{ $cambio->estadoNuevoRelacion?->estado ?? 'N/A' }}</span>
                                </h4>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-black uppercase bg-fondo-panel px-2 py-1 rounded-lg text-apoyo">
                                    <i class="ph-bold ph-user mr-1"></i>{{ $cambio->cambiadoPor?->name ?? 'Sistema' }}
                                </span>
                            </div>
                        </div>
                        @if($cambio->motivo)
                            <p class="text-sm text-apoyo mt-2 bg-fondo-panel p-2 rounded-lg border border-borde-suave">
                                <span class="font-bold text-titulo text-[11px] uppercase block mb-1">Motivo:</span>
                                {{ $cambio->motivo }}
                            </p>
                        @endif
                        @if($cambio->observacion)
                            <p class="text-xs text-apoyo mt-1 pl-1">
                                <strong>Obs:</strong> {{ $cambio->observacion }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 text-center border-2 border-dashed border-borde-suave rounded-2xl bg-fondo-panel">
                    <i class="ph-fill ph-clock text-4xl text-meta mb-2"></i>
                    <p class="text-sm font-bold text-apoyo">No hay registros de cambios de estado institucionales.</p>
                </div>
            @endforelse
        </div>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-sm font-bold text-titulo transition hover:bg-fondo-panel">
                Cerrar
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
