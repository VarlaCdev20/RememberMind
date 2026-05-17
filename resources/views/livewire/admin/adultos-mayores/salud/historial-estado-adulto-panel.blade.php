<div>
    <!-- Tarjeta Principal y Botón -->
    <div class="rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-[#2F3E5C]">Estados Institucionales</h3>
                <p class="text-sm text-[#2F3E5C]/60">Trazabilidad de cambios (altas, bajas, hospitalizaciones).</p>
            </div>
            <button wire:click="abrirModalHistorialEstado('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl border border-[#2F3E5C] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] shadow-sm hover:bg-[#2F3E5C]/10 active:scale-95 transition">
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
                <div class="rounded-xl border border-[#C7B5A3] bg-white p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 h-full w-1.5 {{ $cambio->estadoNuevoRelacion?->estado === 'ACTIVO' ? 'bg-green-500' : 'bg-terracota' }}"></div>
                    <div class="pl-2">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-xs font-black uppercase text-[#2F3E5C]/55">
                                    {{ $cambio->fecha_cambio->format('d/m/Y H:i') }}
                                </span>
                                <h4 class="font-bold text-[#2F3E5C] text-sm mt-0.5">
                                    {{ $cambio->estadoAnteriorRelacion?->estado ?? 'N/A' }} 
                                    <i class="ph-bold ph-arrow-right mx-1 text-[#2F3E5C]/40"></i> 
                                    <span class="text-terracota">{{ $cambio->estadoNuevoRelacion?->estado ?? 'N/A' }}</span>
                                </h4>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-black uppercase bg-[#F8F2EC] px-2 py-1 rounded-lg text-[#2F3E5C]/70">
                                    <i class="ph-bold ph-user mr-1"></i>{{ $cambio->cambiadoPor?->name ?? 'Sistema' }}
                                </span>
                            </div>
                        </div>
                        @if($cambio->motivo)
                            <p class="text-sm text-[#2F3E5C]/80 mt-2 bg-[#F8F2EC] p-2 rounded-lg border border-[#D5C7B9]">
                                <span class="font-bold text-[#2F3E5C] text-[11px] uppercase block mb-1">Motivo:</span>
                                {{ $cambio->motivo }}
                            </p>
                        @endif
                        @if($cambio->observacion)
                            <p class="text-xs text-[#2F3E5C]/70 mt-1 pl-1">
                                <strong>Obs:</strong> {{ $cambio->observacion }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-8 text-center border-2 border-dashed border-[#C7B5A3] rounded-2xl bg-[#F8F2EC]">
                    <i class="ph-fill ph-clock text-4xl text-[#C7B5A3] mb-2"></i>
                    <p class="text-sm font-bold text-[#2F3E5C]/60">No hay registros de cambios de estado institucionales.</p>
                </div>
            @endforelse
        </div>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] transition hover:bg-[#F8F2EC]">
                Cerrar
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
