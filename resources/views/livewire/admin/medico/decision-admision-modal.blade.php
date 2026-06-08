<div>
    @if($isOpen && $adulto)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-modal-overlay backdrop-blur-sm transition-opacity" wire:click="close"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Contenedor del Modal -->
            <div class="relative transform overflow-hidden rounded-2xl bg-modal-bg text-left shadow-modal transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-modal-borde flex flex-col max-h-[90vh]">
                
                <!-- Header del Modal -->
                <div class="bg-estado-infoBg px-6 py-4 border-b border-estado-infoBorde flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-estado-info flex items-center gap-2" id="modal-title">
                            <i class="ph-fill ph-check-square-offset text-2xl"></i>
                            Decisión Final de Admisión Médica
                        </h3>
                        <p class="text-sm font-semibold text-estado-info/80 mt-1">
                            Paciente: <span class="font-bold">{{ $adulto->nombres }} {{ $adulto->apellidos }}</span> | CI: {{ $adulto->ci }}
                        </p>
                    </div>
                    <button wire:click="close" class="text-estado-info hover:text-red-700 transition-colors rounded-lg p-1 hover:bg-white/50">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-5 bg-fondo-app overflow-y-auto flex-1">
                    <style>
                        input:has(+ span.text-estado-peligro), 
                        select:has(+ span.text-estado-peligro), 
                        textarea:has(+ span.text-estado-peligro) {
                            border-color: #ef4444 !important; 
                            color: #ef4444 !important;
                        }
                    </style>
                    <div class="space-y-6">
                        
                        <div>
                            <label class="block text-sm font-black text-titulo mb-2">Decisión Médica <span class="text-estado-peligro">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="decision" value="ADMITIDO_NORMAL" class="peer sr-only">
                                    <div class="rounded-xl border-2 border-borde p-3 text-center transition-all peer-checked:border-estado-exito peer-checked:bg-estado-exitoBg hover:bg-fondo-hover">
                                        <i class="ph-bold ph-check-circle text-estado-exito text-2xl mb-1"></i>
                                        <div class="font-bold text-titulo text-sm">Admisión Normal</div>
                                    </div>
                                </label>
                                
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="decision" value="ADMITIDO_CON_SEGUIMIENTO" class="peer sr-only">
                                    <div class="rounded-xl border-2 border-borde p-3 text-center transition-all peer-checked:border-estado-info peer-checked:bg-estado-infoBg hover:bg-fondo-hover">
                                        <i class="ph-bold ph-eye text-estado-info text-2xl mb-1"></i>
                                        <div class="font-bold text-titulo text-sm">Admisión c/ Seguimiento</div>
                                    </div>
                                </label>
                                
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="decision" value="ADMITIDO_CON_CUIDADO_ESPECIAL" class="peer sr-only">
                                    <div class="rounded-xl border-2 border-borde p-3 text-center transition-all peer-checked:border-estado-advertencia peer-checked:bg-estado-advertenciaBg hover:bg-fondo-hover">
                                        <i class="ph-bold ph-warning-circle text-estado-advertencia text-2xl mb-1"></i>
                                        <div class="font-bold text-titulo text-sm">Admisión c/ Cuidado Especial</div>
                                    </div>
                                </label>
                                
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="decision" value="DERIVADO" class="peer sr-only">
                                    <div class="rounded-xl border-2 border-borde p-3 text-center transition-all peer-checked:border-estado-peligro peer-checked:bg-estado-peligroBg hover:bg-fondo-hover">
                                        <i class="ph-bold ph-arrow-u-up-right text-estado-peligro text-2xl mb-1"></i>
                                        <div class="font-bold text-titulo text-sm">Derivar / Rechazar</div>
                                    </div>
                                </label>
                            </div>
                            @error('decision') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Panel dinámico según decisión -->
                        <div class="bg-fondo-card border border-borde rounded-xl p-4 shadow-sm min-h-[120px]">
                            @if($decision === 'ADMITIDO_NORMAL')
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Motivo de la Decisión <span class="text-estado-peligro">*</span></label>
                                    <textarea wire:model="motivo_decision" rows="3" placeholder="Paciente apto para convivencia general..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                    @error('motivo_decision') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            @elseif($decision === 'ADMITIDO_CON_SEGUIMIENTO')
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Protocolo de Seguimiento Requerido <span class="text-estado-peligro">*</span></label>
                                    <textarea wire:model="seguimiento_requerido" rows="3" placeholder="Detalle qué seguimiento clínico o de enfermería se debe llevar a cabo..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                    @error('seguimiento_requerido') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            @elseif($decision === 'ADMITIDO_CON_CUIDADO_ESPECIAL')
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Especificación del Cuidado Especial <span class="text-estado-peligro">*</span></label>
                                    <textarea wire:model="cuidado_especial_requerido" rows="3" placeholder="Detalle los cuidados paliativos, de movilización o de nutrición especiales requeridos..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                    @error('cuidado_especial_requerido') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            @elseif($decision === 'DERIVADO')
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-label mb-1">Motivo Clínico de Derivación / Rechazo <span class="text-estado-peligro">*</span></label>
                                        <textarea wire:model="motivo_derivacion" rows="2" placeholder="Requiere internación hospitalaria, psiquiátrica, etc." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                        @error('motivo_derivacion') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-label mb-1">Institución Sugerida (Opcional)</label>
                                        <input type="text" wire:model="institucion_derivada" placeholder="Hospital de Clínicas, Instituto Psiquiátrico, etc." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase">
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-20 text-apoyo text-sm italic">
                                    Seleccione una decisión para habilitar los campos.
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-label mb-1">Recomendación Final Adicional (Opcional)</label>
                            <textarea wire:model="recomendacion_final" rows="2" placeholder="Cualquier nota para la familia o administración..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                        </div>
                        
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-t border-modal-footerBorde flex justify-between items-center rounded-b-2xl shrink-0">
                    <button wire:click="close" class="px-4 py-2 text-sm font-bold text-apoyo hover:text-texto-principal transition-colors">
                        Cancelar
                    </button>
                    
                    <button wire:click="guardar" class="px-5 py-2 bg-boton-acento hover:bg-boton-acentoHover text-white font-bold rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <i class="ph-bold ph-paper-plane-tilt text-lg"></i>
                        Confirmar Dictamen Médico
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
