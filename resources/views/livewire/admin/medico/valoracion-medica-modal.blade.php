<div>
    @if($isOpen && $adulto)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-modal-overlay backdrop-blur-sm transition-opacity" wire:click="close"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Contenedor del Modal -->
            <div class="relative transform overflow-hidden rounded-2xl bg-modal-bg text-left shadow-modal transition-all sm:my-8 sm:w-full sm:max-w-5xl border border-modal-borde flex flex-col max-h-[90vh]">
                
                <!-- Header del Modal -->
                <div class="bg-estado-advertenciaBg px-6 py-4 border-b border-estado-advertenciaBorde flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-estado-advertencia flex items-center gap-2" id="modal-title">
                            <i class="ph-fill ph-first-aid text-2xl"></i>
                            Valoración Médica General
                        </h3>
                        <p class="text-sm font-semibold text-estado-advertencia/80 mt-1">
                            Paciente: <span class="font-bold">{{ $adulto->nombres }} {{ $adulto->apellidos }}</span> | CI: {{ $adulto->ci }}
                        </p>
                    </div>
                    <button wire:click="close" class="text-estado-advertencia hover:text-red-700 transition-colors rounded-lg p-1 hover:bg-white/50">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-4 bg-fondo-app overflow-y-auto flex-1">
                    <style>
                        input:has(+ span.text-estado-peligro), 
                        select:has(+ span.text-estado-peligro), 
                        textarea:has(+ span.text-estado-peligro) {
                            border-color: #ef4444 !important; 
                            color: #ef4444 !important;
                        }
                    </style>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Columna Izquierda: Clínico y Signos -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Signos Vitales Mini-Tracker -->
                            <div class="bg-white rounded-xl border border-borde p-4 shadow-sm" x-data="{
                                updateChart() {
                                    // Simulated simple reactive chart for data entry feedback
                                }
                            }">
                                <h4 class="font-bold text-titulo text-sm uppercase tracking-wider border-b border-borde-suave pb-2 mb-3">Signos Vitales</h4>
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo uppercase">PA Sistólica</label>
                                        <input type="number" wire:model="pa_sistolica" class="w-full rounded-md border-input-borde p-1.5 text-sm bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-1 focus:ring-input-ringFocus" placeholder="mmHg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo uppercase">PA Diastólica</label>
                                        <input type="number" wire:model="pa_diastolica" class="w-full rounded-md border-input-borde p-1.5 text-sm bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-1 focus:ring-input-ringFocus" placeholder="mmHg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo uppercase">Frec. Cardiaca</label>
                                        <input type="number" wire:model="fc" class="w-full rounded-md border-input-borde p-1.5 text-sm bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-1 focus:ring-input-ringFocus" placeholder="lpm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo uppercase">Frec. Respiratoria</label>
                                        <input type="number" wire:model="fr" class="w-full rounded-md border-input-borde p-1.5 text-sm bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-1 focus:ring-input-ringFocus" placeholder="rpm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo uppercase">Temperatura</label>
                                        <input type="number" step="0.1" wire:model="temp" class="w-full rounded-md border-input-borde p-1.5 text-sm bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-1 focus:ring-input-ringFocus" placeholder="°C">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo uppercase">Saturación O2</label>
                                        <input type="number" wire:model="sato2" class="w-full rounded-md border-input-borde p-1.5 text-sm bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-1 focus:ring-input-ringFocus" placeholder="%">
                                    </div>
                                </div>
                                
                                <!-- Simulación Visual de ECG / Chart Tracker -->
                                <div class="h-12 w-full rounded bg-fondo-app/50 border border-borde-suave flex items-center justify-center relative overflow-hidden">
                                    <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(#e5e7eb 1px, transparent 1px), linear-gradient(90deg, #e5e7eb 1px, transparent 1px); background-size: 10px 10px;"></div>
                                    <svg class="w-full h-full text-estado-advertencia opacity-70" viewBox="0 0 100 20" preserveAspectRatio="none">
                                        <path d="M0 10 L20 10 L25 5 L30 15 L35 10 L100 10" fill="none" stroke="currentColor" stroke-width="1.5" />
                                    </svg>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Alergias Conocidas</label>
                                    <textarea wire:model="alergias" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Medicación Actual</label>
                                    <textarea wire:model="medicacion_actual" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Central y Derecha: Evaluación Sistémica -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Condición Médica General <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="condicion_medica_general" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm font-semibold">
                                        <option value="">Seleccione...</option>
                                        <option value="ESTABLE">ESTABLE</option>
                                        <option value="REQUIERE_OBSERVACION">REQUIERE OBSERVACIÓN</option>
                                        <option value="DELICADA">DELICADA</option>
                                        <option value="NO_APTA">NO APTA PARA INGRESO</option>
                                    </select>
                                    @error('condicion_medica_general') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Nivel de Dependencia <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="nivel_dependencia" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm font-semibold">
                                        <option value="">Seleccione...</option>
                                        <option value="BAJO">BAJO (INDEPENDIENTE)</option>
                                        <option value="MEDIO">MEDIO (ASISTENCIA PARCIAL)</option>
                                        <option value="ALTO">ALTO (ASISTENCIA TOTAL)</option>
                                        <option value="CRITICO">CRÍTICO (CUIDADOS PALIATIVOS)</option>
                                    </select>
                                    @error('nivel_dependencia') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Estado Neurológico Básico</label>
                                    <select wire:model="estado_neurologico_basico" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="NORMAL">NORMAL / ÍNTEGRO</option>
                                        <option value="CONFUSION_LEVE">CONFUSIÓN LEVE</option>
                                        <option value="DESORIENTACION">DESORIENTACIÓN MODERADA</option>
                                        <option value="ALTERADO">ALTERADO / DETERIORO SEVERO</option>
                                        <option value="NO_EVALUABLE">NO EVALUABLE</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Estado Cognitivo Aparente <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="estado_cognitivo_aparente" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="CONSERVADO">CONSERVADO</option>
                                        <option value="OLVIDOS_LEVES">OLVIDOS LEVES BENIGNOS</option>
                                        <option value="DESORIENTACION">DESORIENTACIÓN / DEMENCIA LEVE</option>
                                        <option value="ALTERACION_IMPORTANTE">ALTERACIÓN IMPORTANTE / DEMENCIA AVANZADA</option>
                                        <option value="NO_EVALUABLE">NO EVALUABLE</option>
                                    </select>
                                    @error('estado_cognitivo_aparente') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Diagnósticos Referidos</label>
                                    <textarea wire:model="diagnosticos_referidos" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase" placeholder="Ej. HTA, Diabetes Tipo 2..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Antecedentes Médicos (Cirugías, traumas)</label>
                                    <textarea wire:model="antecedentes_medicos" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                </div>
                            </div>

                            <!-- Panel de Requerimientos Institucionales -->
                            <div class="bg-fondo-card rounded-xl p-4 border border-borde">
                                <h4 class="font-bold text-titulo text-sm uppercase tracking-wider mb-3">Requerimientos Clínicos Institucionales</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="requiere_control_medicacion" class="h-4 w-4 rounded border-borde-suave text-estado-advertencia focus:ring-estado-advertencia">
                                        <span class="text-sm font-semibold text-titulo">Requiere Control Estricto de Medicación</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="requiere_control_signos" class="h-4 w-4 rounded border-borde-suave text-estado-advertencia focus:ring-estado-advertencia">
                                        <span class="text-sm font-semibold text-titulo">Requiere Monitoreo Diario de Signos</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="requiere_seguimiento_cognitivo" class="h-4 w-4 rounded border-borde-suave text-estado-advertencia focus:ring-estado-advertencia">
                                        <span class="text-sm font-semibold text-titulo">Requiere Seguimiento Cognitivo Específico</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model.live="requiere_cuidado_especial" class="h-4 w-4 rounded border-borde-suave text-estado-advertencia focus:ring-estado-advertencia">
                                        <span class="text-sm font-semibold text-terracota">Requiere Cuidado Especial Adicional</span>
                                    </label>
                                </div>
                                
                                @if($requiere_cuidado_especial)
                                <div>
                                    <label class="block text-xs font-semibold text-label mb-1">Detalle del Cuidado Especial <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model="detalle_cuidado_especial" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase">
                                    @error('detalle_cuidado_especial') <span class="text-estado-peligro text-[10px] font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Observación y Conclusión Médica <span class="text-estado-peligro">*</span></label>
                                <textarea wire:model="observacion_medica" rows="3" placeholder="Redacte su conclusión médica para la administración respecto a la viabilidad del ingreso..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus shadow-sm uppercase"></textarea>
                                @error('observacion_medica') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-t border-modal-footerBorde flex justify-between items-center rounded-b-2xl shrink-0">
                    <button wire:click="close" class="px-4 py-2 text-sm font-bold text-apoyo hover:text-texto-principal transition-colors">
                        Cancelar
                    </button>
                    
                    <button wire:click="guardar" class="px-5 py-2 bg-estado-advertencia hover:bg-orange-600 text-white font-bold rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <i class="ph-bold ph-check-circle text-lg"></i>
                        Confirmar Valoración Médica
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
