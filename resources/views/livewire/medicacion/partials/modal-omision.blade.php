{{-- MODAL EMERGENTE CENTRADO: REGISTRAR OMISIÓN — PALETA UNIFICADA --}}
@if($modalOmisionAbierto)
<div 
    x-data="{ omisionOpen: true }"
    x-cloak>
    <template x-teleport="body">
        <div 
            x-show="omisionOpen"
            x-on:keydown.escape.window="$wire.cerrarModalOmision()"
            class="fixed inset-0 z-[99999] overflow-y-auto font-sans" 
            aria-labelledby="modal-omision-title" 
            role="dialog" 
            aria-modal="true"
            style="display: none;">
    
    <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
        {{-- Backdrop con desenfoque suave --}}
        <div 
            x-show="$wire.modalOmisionAbierto"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            wire:click="cerrarModalOmision" 
            class="fixed inset-0 bg-[#1A1816]/70 dark:bg-black/85 backdrop-blur-[2px] transition-opacity">
        </div>

        {{-- Ventana Modal Flotante Mediana Centrada (max-w-xl) --}}
        <div 
            x-show="$wire.modalOmisionAbierto"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-xl text-left align-middle transition-all transform bg-[#F0E8DE] dark:bg-[#2C2924] rounded-[16px] border border-[#C7B9AA] dark:border-[#494139] shadow-[0_16px_40px_rgba(48,64,96,0.18)] dark:shadow-[0_16px_40px_rgba(0,0,0,0.6)] overflow-hidden my-6 z-10">
            
            {{-- Header del Modal en Alerta Terracota --}}
            <div class="px-5 py-4 border-b border-[#C7B9AA] dark:border-[#494139] flex items-center justify-between bg-[#E4D8CC] dark:bg-[#211F1B]">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#A35A44] text-white shadow-2xs">
                        <i class="ph-bold ph-warning-circle text-xl"></i>
                    </span>
                    <div>
                        <h3 class="text-[17px] font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight" id="modal-omision-title">
                            Registrar omisión
                        </h3>
                        <p class="text-xs text-[#677084] dark:text-[#BDAE9F]">
                            Justificación clínica y trazabilidad de dosis no suministrada
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    wire:click="cerrarModalOmision"
                    class="rounded-lg p-1.5 text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-white hover:bg-[#DED1C3] dark:hover:bg-[#38342E] transition cursor-pointer"
                    title="Cerrar modal">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            {{-- Formulario de Omisión --}}
            <form wire:submit.prevent="guardarOmision">
                <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4 custom-scrollbar text-xs">
                    
                    {{-- Resumen Solo Lectura de la Dosis a Omitir --}}
                    <div class="rounded-[12px] bg-[#F3EAE1] dark:bg-[#2A211F] border border-[#C7B9AA] dark:border-[#494139] p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-[800] text-[#A35A44] dark:text-[#E5A898] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="ph-bold ph-shield-warning"></i>
                                Dosis Objeto de Omisión
                            </span>
                            <span class="text-[10.5px] font-bold text-[#A35A44] dark:text-[#E5A898] font-mono">
                                {{ $selectedHora ?? '08:00' }} hrs
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block">Residente:</span>
                                <strong class="text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Residente' }}
                                </strong>
                            </div>

                            <div>
                                <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block">Medicamento:</span>
                                <strong class="text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento' }}
                                    ({{ $dosisDetalle['prescripcion']['dosis'] ?? '' }})
                                </strong>
                            </div>
                        </div>

                        <div class="pt-1.5 border-t border-[#C7B9AA]/60 dark:border-[#494139]/40 text-[10.5px] text-[#677084] dark:text-[#BDAE9F] flex items-center justify-between">
                            <span>Profesional que omite: <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ auth()->user()?->name ?? 'Enfermería en turno' }}</strong></span>
                            <span class="text-[#A35A44] font-semibold">Trazabilidad clínica</span>
                        </div>
                    </div>

                    {{-- Campos Editables de Omisión --}}
                    <div class="space-y-3.5">
                        
                        {{-- 1. Motivo de Omisión Justificada --}}
                        <div>
                            <label for="formMotivoOmision" class="text-xs font-[700] text-[#304060] dark:text-[#EFE5DA] block mb-1">
                                Motivo de Omisión Justificada <span class="text-[#C85D52] dark:text-[#E5A898]">*</span>
                            </label>

                            <select 
                                id="formMotivoOmision"
                                wire:model.live="formMotivoOmision"
                                required
                                class="w-full h-10 px-3 text-xs rounded-[10px] border {{ $errors->has('formMotivoOmision') ? 'border-[#C85D52] focus:ring-[#C85D52]' : 'border-[#C7B9AA] dark:border-[#494139] focus:ring-[#A35A44]' }} bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] focus:outline-none focus:ring-1 shadow-2xs cursor-pointer">
                                <option value="">Seleccione el motivo clínico o asistencial...</option>
                                <option value="Rechazo voluntario del residente">Rechazo voluntario del residente</option>
                                <option value="Ayuno médico programado (analítica / procedimiento)">Ayuno médico programado (analítica / procedimiento)</option>
                                <option value="Residente ausente temporalmente / en traslado">Residente ausente temporalmente / en traslado</option>
                                <option value="Suspensión o modificación médica verbal">Suspensión o modificación médica verbal</option>
                                <option value="Fármaco no disponible en farmacia">Fármaco no disponible en farmacia</option>
                                <option value="Intolerancia gástrica o náuseas previas">Intolerancia gástrica o náuseas previas</option>
                                <option value="Parámetro clínico contraindicado (PA/FC/Glucemia)">Parámetro clínico contraindicado (PA/FC/Glucemia)</option>
                                <option value="OTRO">Otro motivo específico (requiere justificación detallada)</option>
                            </select>

                            @error('formMotivoOmision')
                                <span class="text-[11px] font-bold text-[#C85D52] dark:text-[#E5A898] block mt-1 flex items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- 2. Observación / Justificación Detallada --}}
                        @php
                            $motivoVal = (string)($formMotivoOmision ?? $this->formMotivoOmision ?? '');
                            $motivoRequiereDetalle = ($motivoVal === 'OTRO' || str_contains($motivoVal, 'verbal') || str_contains($motivoVal, 'contraindicado'));
                        @endphp

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="formObservacionOmision" class="text-xs font-[700] text-[#304060] dark:text-[#EFE5DA]">
                                    Justificación Asistencial Detallada
                                    @if($motivoRequiereDetalle)
                                        <span class="text-[#C85D52] dark:text-[#E5A898] font-bold">* (Obligatoria para este motivo)</span>
                                    @else
                                        <span class="text-[11px] font-normal text-[#677084] dark:text-[#BDAE9F]">(Recomendada)</span>
                                    @endif
                                </label>
                                <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] font-mono">
                                    {{ strlen((string)($formObservacionOmision ?? $this->formObservacionOmision ?? '')) }}/1000
                                </span>
                            </div>

                            <textarea 
                                id="formObservacionOmision"
                                wire:model="formObservacionOmision" 
                                maxlength="1000"
                                rows="3"
                                placeholder="{{ $motivoRequiereDetalle ? 'Detalle ampliamente la razón clínica, médico que indicó la suspensión o valores de signos vitales...' : 'Indique detalles complementarios sobre la omisión de la toma...' }}"
                                class="w-full text-xs rounded-[10px] p-3 border {{ $errors->has('formObservacionOmision') ? 'border-[#C85D52] focus:ring-[#C85D52]' : 'border-[#C7B9AA] dark:border-[#494139] focus:ring-[#A35A44]' }} bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 resize-none shadow-2xs"></textarea>
                            
                            @error('formObservacionOmision')
                                <span class="text-[11px] font-bold text-[#C85D52] dark:text-[#E5A898] block mt-1 flex items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                    </div>

                </div>

                {{-- Footer con Botón Confirmar Omisión --}}
                <div class="px-5 py-3.5 bg-[#E4D8CC] dark:bg-[#211F1B] border-t border-[#C7B9AA] dark:border-[#494139] flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5">
                    <button 
                        type="button" 
                        wire:click="cerrarModalOmision"
                        class="h-10 px-4 rounded-[10px] text-xs font-semibold bg-[#F0E8DE] dark:bg-[#332F29] hover:bg-[#DED1C3] dark:hover:bg-[#3B362F] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] transition cursor-pointer">
                        Cancelar
                    </button>

                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        class="h-10 px-5 rounded-[10px] text-xs font-bold bg-[#A35A44] hover:bg-[#884A39] text-white transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <i class="ph-bold ph-warning text-sm" wire:loading.remove wire:target="guardarOmision"></i>
                        <span wire:loading.remove wire:target="guardarOmision">Confirmar Omisión</span>
                        <span wire:loading wire:target="guardarOmision">Registrando omisión...</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
</template>
</div>
@endif
