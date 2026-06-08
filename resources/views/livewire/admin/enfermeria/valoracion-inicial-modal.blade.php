<div>
    @if($isOpen && $adulto)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-modal-overlay backdrop-blur-sm transition-opacity" wire:click="close"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Contenedor del Modal -->
            <div class="relative transform overflow-hidden rounded-2xl bg-modal-bg text-left shadow-modal transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-modal-borde flex flex-col max-h-[90vh]">
                
                <!-- Header del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-b border-modal-headerBorde flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-modal-titulo flex items-center gap-2" id="modal-title">
                            <i class="ph ph-stethoscope text-boton-acento text-2xl"></i>
                            Valoración Inicial de Enfermería
                        </h3>
                        <p class="text-sm font-semibold text-apoyo mt-1">
                            Paciente: <span class="text-meta">{{ $adulto->nombres }} {{ $adulto->apellidos }}</span> | CI: {{ $adulto->ci }} {{ $adulto->expedicion_ci }}
                        </p>
                    </div>
                    <button wire:click="close" class="text-apoyo hover:text-terracota transition-colors rounded-lg p-1 hover:bg-fondo-hover">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-4 bg-fondo-app overflow-y-auto flex-1">
                    <style>
                        /* Validación visual automática */
                        input:has(+ span.text-estado-peligro), 
                        select:has(+ span.text-estado-peligro), 
                        textarea:has(+ span.text-estado-peligro) {
                            border-color: #ef4444 !important; 
                            color: #ef4444 !important;
                        }
                        input:has(+ span.text-estado-peligro):focus, 
                        select:has(+ span.text-estado-peligro):focus, 
                        textarea:has(+ span.text-estado-peligro):focus {
                            --tw-ring-color: #ef4444 !important;
                        }
                    </style>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Columna 1: Estado y Conciencia -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-titulo text-sm uppercase tracking-wider border-b border-borde-suave pb-2">Estado y Conciencia</h4>
                            
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Estado General <span class="text-estado-peligro">*</span></label>
                                <select wire:model.live="estado_general" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="ESTABLE">ESTABLE</option>
                                    <option value="REGULAR">REGULAR</option>
                                    <option value="DELICADO">DELICADO</option>
                                    <option value="CRITICO" class="text-estado-peligro font-bold">CRÍTICO</option>
                                </select>
                                @error('estado_general') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Nivel de Conciencia</label>
                                <select wire:model="nivel_conciencia" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="ALERTA">ALERTA</option>
                                    <option value="SOMNOLIENTO">SOMNOLIENTO</option>
                                    <option value="CONFUSO">CONFUSO</option>
                                    <option value="NO_RESPONDE">NO RESPONDE</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Orientación <span class="text-estado-peligro">*</span></label>
                                <select wire:model="orientacion" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="ORIENTADO">ORIENTADO (Tiempo, Espacio, Persona)</option>
                                    <option value="DESORIENTADO_TIEMPO">DESORIENTADO EN TIEMPO</option>
                                    <option value="DESORIENTADO_LUGAR">DESORIENTADO EN LUGAR</option>
                                    <option value="DESORIENTADO_PERSONA">DESORIENTADO EN PERSONA</option>
                                    <option value="NO_EVALUABLE">NO EVALUABLE</option>
                                </select>
                                @error('orientacion') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Comunicación</label>
                                <input type="text" wire:model="comunicacion" placeholder="Verbal, no verbal, afasia, etc." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                            </div>
                        </div>

                        <!-- Columna 2: Movilidad y Piel -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-titulo text-sm uppercase tracking-wider border-b border-borde-suave pb-2">Físico y Movilidad</h4>
                            
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Movilidad <span class="text-estado-peligro">*</span></label>
                                <select wire:model="movilidad" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                                    <option value="CON_BASTON">CON BASTÓN</option>
                                    <option value="CON_ANDADOR">CON ANDADOR</option>
                                    <option value="SILLA_RUEDAS">SILLA DE RUEDAS</option>
                                    <option value="CAMILLA">CAMILLA / POSTRADO</option>
                                    <option value="NO_DEAMBULA">NO DEAMBULA</option>
                                </select>
                                @error('movilidad') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Riesgo de Caída <span class="text-estado-peligro">*</span></label>
                                <select wire:model.live="riesgo_caida" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="BAJO">BAJO</option>
                                    <option value="MEDIO">MEDIO</option>
                                    <option value="ALTO" class="text-amber-600 font-bold">ALTO</option>
                                    <option value="CRITICO" class="text-estado-peligro font-bold">CRÍTICO</option>
                                </select>
                                @error('riesgo_caida') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Estado de la Piel</label>
                                <select wire:model="piel_estado" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="INTEGRA">ÍNTEGRA Y SANA</option>
                                    <option value="SECA">SECA / DESCAMATIVA</option>
                                    <option value="HEMATOMAS">CON HEMATOMAS</option>
                                    <option value="HERIDAS">CON HERIDAS</option>
                                    <option value="ULCERAS">CON ÚLCERAS POR PRESIÓN</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila: Dolor y Heridas -->
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 bg-fondo-card rounded-xl p-4 border border-borde">
                            <!-- Dolor -->
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <input type="checkbox" wire:model.live="hay_dolor" id="hay_dolor" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                    <label for="hay_dolor" class="text-sm font-semibold text-titulo cursor-pointer">Paciente presenta dolor</label>
                                </div>
                                @if($hay_dolor)
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-label mb-1">Intensidad (1-10) <span class="text-estado-peligro">*</span></label>
                                        <input type="number" wire:model="intensidad_dolor" min="1" max="10" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        @error('intensidad_dolor') <span class="text-estado-peligro text-[10px] font-medium mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-label mb-1">Ubicación</label>
                                        <input type="text" wire:model="ubicacion_dolor" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Heridas -->
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <input type="checkbox" wire:model.live="hay_heridas" id="hay_heridas" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                    <label for="hay_heridas" class="text-sm font-semibold text-titulo cursor-pointer">Paciente presenta heridas/úlceras</label>
                                </div>
                                @if($hay_heridas)
                                <div>
                                    <label class="block text-xs font-semibold text-label mb-1">Ubicación y descripción <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model="ubicacion_heridas" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('ubicacion_heridas') <span class="text-estado-peligro text-[10px] font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Otros -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Higiene al Ingreso</label>
                                <input type="text" wire:model="higiene_ingreso" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Continencia Básica</label>
                                <input type="text" wire:model="continencia_basica" placeholder="Control de esfínteres, usa pañal, sonda..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Alimentación e Hidratación Aparente</label>
                                <input type="text" wire:model="alimentacion_aparente" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-label mb-1">Signos Vitales Iniciales (Opcional)</label>
                                <input type="text" wire:model="signos_vitales_iniciales" placeholder="PA, FC, Temp, SatO2..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-label mb-1">Observaciones Generales de Enfermería</label>
                            <textarea wire:model="observacion" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-label mb-1">Recomendación Médica Urgente <span class="text-estado-peligro">*</span></label>
                            <textarea wire:model="recomendacion_enfermeria" rows="3" placeholder="Sugerencias directas para el médico que realizará la valoración..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase"></textarea>
                            @error('recomendacion_enfermeria') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-t border-modal-footerBorde flex justify-between items-center rounded-b-2xl shrink-0">
                    <button wire:click="close" class="px-4 py-2 text-sm font-bold text-apoyo hover:text-texto-principal transition-colors">
                        Cancelar
                    </button>
                    
                    <button wire:click="guardar" class="px-5 py-2 bg-estado-exitoBg hover:bg-estado-exitoBorde text-estado-exito border border-estado-exitoBorde shadow-sm text-sm font-bold rounded-lg transition-colors flex items-center gap-2">
                        <i class="ph-bold ph-check-circle text-lg"></i>
                        Confirmar y Derivar al Médico
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
