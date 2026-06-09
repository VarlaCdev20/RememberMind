<div>
    @if($isOpen && $preadmision)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-modal-overlay backdrop-blur-sm transition-opacity" wire:click="close"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Contenedor del Modal -->
            <div class="relative transform overflow-hidden rounded-2xl bg-modal-bg text-left shadow-modal transition-all sm:my-8 sm:w-full sm:max-w-5xl border border-modal-borde flex flex-col max-h-[95vh]">
                
                <!-- Header del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-b border-modal-headerBorde flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-modal-titulo flex items-center gap-2" id="modal-title">
                            <i class="ph ph-stethoscope text-boton-acento text-2xl animate-pulse"></i>
                            Valoración Clínica Inicial de Enfermería
                        </h3>
                        <p class="text-sm font-semibold text-apoyo mt-1">
                            Paciente: <span class="text-meta font-extrabold">{{ $preadmision->nombre_completo }}</span> | CI: {{ $preadmision->ci }} {{ $preadmision->expedicion_ci }}
                        </p>
                    </div>
                    <button wire:click="close" class="text-apoyo hover:text-terracota transition-colors rounded-lg p-1 hover:bg-fondo-hover">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="px-6 py-4 bg-fondo-app overflow-y-auto flex-1 space-y-6">
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

                    <!-- SECCIÓN 1: ESTADO GENERAL Y CONCIENCIA -->
                    <div class="rounded-xl border border-borde bg-fondo-panel p-4 space-y-4 shadow-sm">
                        <div class="flex items-center gap-2 pb-2 border-b border-borde-suave">
                            <i class="ph-bold ph-brain text-boton-acento text-lg"></i>
                            <h4 class="font-bold text-titulo text-xs uppercase tracking-wider">Estado General y Conciencia</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Estado General <span class="text-estado-peligro">*</span></label>
                                <select wire:model.live="estado_general" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="ESTABLE">ESTABLE</option>
                                    <option value="REGULAR">REGULAR</option>
                                    <option value="DELICADO">DELICADO</option>
                                    <option value="CRITICO" class="text-estado-peligro font-bold">CRÍTICO</option>
                                </select>
                                @error('estado_general') <span class="text-estado-peligro text-[10px] font-medium mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Nivel de Conciencia <span class="text-estado-peligro">*</span></label>
                                <select wire:model="nivel_conciencia" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="ALERTA">ALERTA</option>
                                    <option value="SOMNOLIENTO">SOMNOLIENTO</option>
                                    <option value="CONFUSO">CONFUSO</option>
                                    <option value="NO_RESPONDE">NO RESPONDE / COMA</option>
                                </select>
                                @error('nivel_conciencia') <span class="text-estado-peligro text-[10px] font-medium mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Comunicación / Lenguaje</label>
                                <input type="text" wire:model="comunicacion" placeholder="Ej: Verbal coherente, afasia motora, mímica..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase">
                            </div>
                        </div>

                        <!-- Orientación Desglosada -->
                        <div class="bg-fondo/35 p-3 rounded-lg border border-borde/50">
                            <label class="block text-xs font-black text-titulo mb-2 uppercase tracking-wide">Orientación Cognitiva <span class="text-estado-peligro">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach (['persona' => 'Orientación Persona', 'tiempo' => 'Orientación Tiempo', 'espacio' => 'Orientación Espacio'] as $key => $title)
                                    <div>
                                        <label class="block text-[10px] font-bold text-apoyo mb-1 uppercase">{{ $title }}</label>
                                        <select wire:model="orientacion_{{ $key }}" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                            <option value="ORIENTADO">ORIENTADO / CONSERVA</option>
                                            <option value="DESORIENTADO">DESORIENTADO / PARCIAL</option>
                                            <option value="NO_EVALUABLE">NO EVALUABLE</option>
                                        </select>
                                        @error('orientacion_'.$key) <span class="text-estado-peligro text-[10px] font-medium mt-1">{{ $message }}</span> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: SIGNOS VITALES INICIALES -->
                    <div class="rounded-xl border border-borde bg-fondo-panel p-4 space-y-4 shadow-sm">
                        <div class="flex items-center gap-2 pb-2 border-b border-borde-suave">
                            <i class="ph-bold ph-heartbeat text-boton-acento text-lg"></i>
                            <h4 class="font-bold text-titulo text-xs uppercase tracking-wider">Signos Vitales y Antropometría</h4>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">PA Sistólica (mmHg) <span class="text-estado-peligro">*</span></label>
                                <input type="number" wire:model="pa_sistolica" placeholder="120" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('pa_sistolica') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">PA Diastólica (mmHg) <span class="text-estado-peligro">*</span></label>
                                <input type="number" wire:model="pa_diastolica" placeholder="80" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('pa_diastolica') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">Frec. Cardiaca (Lpm) <span class="text-estado-peligro">*</span></label>
                                <input type="number" wire:model="frecuencia_cardiaca" placeholder="72" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('frecuencia_cardiaca') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">Frec. Respiratoria (Rpm) <span class="text-estado-peligro">*</span></label>
                                <input type="number" wire:model="frecuencia_respiratoria" placeholder="16" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('frecuencia_respiratoria') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">Temperatura (°C) <span class="text-estado-peligro">*</span></label>
                                <input type="number" step="0.1" wire:model="temperatura" placeholder="36.5" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('temperatura') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">Saturación O2 (%) <span class="text-estado-peligro">*</span></label>
                                <input type="number" wire:model="saturacion_oxigeno" placeholder="95" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('saturacion_oxigeno') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">Peso (Kg) <span class="text-apoyo font-medium">(Opcional)</span></label>
                                <input type="number" step="0.1" wire:model="peso" placeholder="70.2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('peso') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-0.5">Talla (cm) <span class="text-apoyo font-medium">(Opcional)</span></label>
                                <input type="number" wire:model="talla" placeholder="165" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm">
                                @error('talla') <span class="text-estado-peligro text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: CAPACIDAD FÍSICA Y MOVILIDAD -->
                    <div class="rounded-xl border border-borde bg-fondo-panel p-4 space-y-4 shadow-sm">
                        <div class="flex items-center gap-2 pb-2 border-b border-borde-suave">
                            <i class="ph-bold ph-wheelchair text-boton-acento text-lg"></i>
                            <h4 class="font-bold text-titulo text-xs uppercase tracking-wider">Físico, Movilidad y Cuidados</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Movilidad Funcional <span class="text-estado-peligro">*</span></label>
                                <select wire:model="movilidad" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                                    <option value="CON_BASTON">CON BASTÓN</option>
                                    <option value="CON_ANDADOR">CON ANDADOR</option>
                                    <option value="SILLA_RUEDAS">SILLA DE RUEDAS</option>
                                    <option value="CAMILLA">CAMILLA / POSTRADO</option>
                                    <option value="NO_DEAMBULA">NO DEAMBULA</option>
                                </select>
                                @error('movilidad') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Apoyo / Asistencia Movilidad</label>
                                <input type="text" wire:model="apoyo_movilidad" placeholder="Ej: Bastón de 4 apoyos, requiere 1 asistente..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Riesgo de Caída <span class="text-estado-peligro">*</span></label>
                                <select wire:model.live="riesgo_caida" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="BAJO">BAJO</option>
                                    <option value="MEDIO">MEDIO</option>
                                    <option value="ALTO" class="text-amber-600 font-bold">ALTO</option>
                                    <option value="CRITICO" class="text-estado-peligro font-bold">CRÍTICO</option>
                                </select>
                                @error('riesgo_caida') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Estado de la Piel</label>
                                <select wire:model="piel_estado" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="INTEGRA">ÍNTEGRA Y SANA</option>
                                    <option value="SECA">SECA / DESCAMATIVA</option>
                                    <option value="HEMATOMAS">CON HEMATOMAS</option>
                                    <option value="HERIDAS">CON HERIDAS</option>
                                    <option value="ULCERAS">CON ÚLCERAS POR PRESIÓN</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Higiene al Ingreso</label>
                                <input type="text" wire:model="higiene_ingreso" placeholder="Ej: Adecuada, precaria, requiere baño..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Continencia Básica</label>
                                <input type="text" wire:model="continencia_basica" placeholder="Ej: Controla esfínteres, usa pañal nocturno, sonda Foley..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase">
                            </div>
                        </div>

                        <!-- Grid: Dolor y Heridas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-fondo/40 rounded-xl p-3 border border-borde/75">
                            <!-- Dolor -->
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="hay_dolor" id="hay_dolor" class="h-4 w-4 rounded border-borde text-boton-acento focus:ring-boton-acento">
                                    <label for="hay_dolor" class="text-xs font-bold text-titulo cursor-pointer uppercase">Presenta dolor clínico</label>
                                </div>
                                @if($hay_dolor)
                                <div class="grid grid-cols-3 gap-2 animate-fade-in">
                                    <div>
                                        <label class="block text-[10px] font-bold text-label mb-1">Escala (1-10) *</label>
                                        <input type="number" wire:model="intensidad_dolor" min="1" max="10" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1 shadow-sm">
                                        @error('intensidad_dolor') <span class="text-estado-peligro text-[9px] font-medium block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-bold text-label mb-1">Localización / Tipo</label>
                                        <input type="text" wire:model="ubicacion_dolor" placeholder="Ej: Lumbar opresivo" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1 shadow-sm uppercase">
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Heridas -->
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="hay_heridas" id="hay_heridas" class="h-4 w-4 rounded border-borde text-boton-acento focus:ring-boton-acento">
                                    <label for="hay_heridas" class="text-xs font-bold text-titulo cursor-pointer uppercase">Presenta heridas / úlceras active</label>
                                </div>
                                @if($hay_heridas)
                                <div class="animate-fade-in">
                                    <label class="block text-[10px] font-bold text-label mb-1">Ubicación y descripción clínica *</label>
                                    <input type="text" wire:model="ubicacion_heridas" placeholder="Ej: Úlcera sacra grado II de 3cm" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1 shadow-sm uppercase">
                                    @error('ubicacion_heridas') <span class="text-estado-peligro text-[9px] font-medium block mt-1">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: VALORACIÓN GERIÁTRICA RÁPIDA -->
                    <div class="rounded-xl border border-borde bg-fondo-panel p-4 space-y-4 shadow-sm">
                        <div class="flex items-center gap-2 pb-2 border-b border-borde-suave">
                            <i class="ph-bold ph-scales text-boton-acento text-lg"></i>
                            <h4 class="font-bold text-titulo text-xs uppercase tracking-wider">Valoración Geriátrica Rápida</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Dependencia Funcional <span class="text-estado-peligro">*</span></label>
                                <select wire:model="dependencia_funcional" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="INDEPENDIENTE">INDEPENDIENTE</option>
                                    <option value="DEPENDENCIA_LEVE">DEPENDENCIA LEVE</option>
                                    <option value="DEPENDENCIA_MODERADA">DEPENDENCIA MODERADA</option>
                                    <option value="DEPENDENCIA_SEVERA">DEPENDENCIA SEVERA</option>
                                    <option value="DEPENDENCIA_TOTAL">DEPENDENCIA TOTAL</option>
                                </select>
                                @error('dependencia_funcional') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Riesgo Nutricional Básico <span class="text-estado-peligro">*</span></label>
                                <select wire:model="riesgo_nutricional" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="SIN RIESGO">SIN RIESGO</option>
                                    <option value="RIESGO_MODERADO">RIESGO MODERADO / DESNUTRICIÓN</option>
                                    <option value="RIESGO_ALTO">RIESGO ALTO / DESNUTRICIÓN SEVERA</option>
                                </select>
                                @error('riesgo_nutricional') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Deterioro Cognitivo Observado <span class="text-estado-peligro">*</span></label>
                                <select wire:model="riesgo_cognitivo" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="SIN DETERIORO">SIN DETERIORO</option>
                                    <option value="DETERIORO_LEVE">DETERIORO LEVE</option>
                                    <option value="DETERIORO_MODERADO">DETERIORO MODERADO</option>
                                    <option value="DETERIORO_SEVERO">DETERIORO SEVERO</option>
                                </select>
                                @error('riesgo_cognitivo') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Alimentación Aparente al Ingreso</label>
                                <input type="text" wire:model="alimentacion_aparente" placeholder="Ej: Dieta blanda, deglución conservada..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Necesidad de Apoyo Inmediato</label>
                                <input type="text" wire:model="necesidad_apoyo_inmediato" placeholder="Ej: Ayuda para alimentación, contención mecánica ocasional..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase">
                            </div>
                        </div>

                        <!-- Anamnesis Referida -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-fondo/45 p-3 rounded-lg border border-borde/75">
                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Antecedentes Clínicos Relevantes Referidos</label>
                                <textarea wire:model="antecedentes_relevantes" rows="2" placeholder="Ej: HTA, Diabetes Tipo 2, ACV en 2023..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm uppercase"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Medicación Habitual Referida</label>
                                <textarea wire:model="medicacion_referida" rows="2" placeholder="Ej: Losartán 50mg/día, Metformina 850mg..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm uppercase"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Alergias Referidas</label>
                                <textarea wire:model="alergias_referidas" rows="2" placeholder="Ej: Alergia a la Penicilina, mariscos, ninguna referida..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm uppercase"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 5: OBSERVACIONES Y RECOMENDACIÓN -->
                    <div class="rounded-xl border border-borde bg-fondo-panel p-4 space-y-4 shadow-sm">
                        <div class="flex items-center gap-2 pb-2 border-b border-borde-suave">
                            <i class="ph-bold ph-notebook text-boton-acento text-lg"></i>
                            <h4 class="font-bold text-titulo text-xs uppercase tracking-wider">Notas Clínicas y Recomendaciones</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Comentarios / Observaciones Adicionales</label>
                                <textarea wire:model="comentarios_adicionales" rows="2" placeholder="Escriba aquí cualquier observación física, psicológica o social relevante..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-1.5 shadow-sm uppercase"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Prioridad de Valoración Médica Sugerida <span class="text-estado-peligro">*</span></label>
                                <select wire:model="prioridad_sugerida" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm">
                                    <option value="BAJA">BAJA (Evaluación de rutina)</option>
                                    <option value="MEDIA">MEDIA (Evaluación regular)</option>
                                    <option value="ALTA">ALTA (Evaluación prioritaria)</option>
                                    <option value="CRITICA">CRÍTICA (Atención médica urgente)</option>
                                </select>
                                @error('prioridad_sugerida') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-label mb-1 uppercase">Recomendación Clínico-Médica Directa para Valoración <span class="text-estado-peligro">*</span></label>
                                <textarea wire:model="recomendacion_enfermeria" rows="3" placeholder="Sugerencias directas e inmediatas de enfermería para el médico que realizará la valoración geriatra..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus text-xs py-2 shadow-sm uppercase"></textarea>
                                @error('recomendacion_enfermeria') <span class="text-estado-peligro text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Confirmación Legal y Trazabilidad -->
                        <div class="flex flex-col md:flex-row items-center justify-between p-3.5 bg-fondo/55 border border-borde/75 rounded-lg gap-4">
                            <div class="flex items-start gap-2 text-left">
                                <input type="checkbox" wire:model.live="confirmacion_documentacion" id="confirmacion_doc" class="h-5 w-5 rounded border-borde text-boton-acento focus:ring-boton-acento shrink-0 mt-0.5">
                                <label for="confirmacion_doc" class="text-xs font-bold text-titulo cursor-pointer leading-tight uppercase">
                                    Confirmo que he revisado la documentación física del paciente y que los signos vitales corresponden a la medición realizada hoy.
                                </label>
                            </div>
                            <div class="shrink-0 text-right bg-input-bg px-3 py-1.5 rounded-lg border border-input-borde text-[10px] font-bold text-meta leading-tight">
                                <div>ENFERMERO: {{ auth()->user()->nombre_completo }}</div>
                                <div>FECHA REGISTRO: {{ now()->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="text-center">
                            @error('confirmacion_documentacion') <span class="text-estado-peligro text-xs font-black"><i class="ph-bold ph-warning-circle mr-1"></i>{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-t border-modal-footerBorde flex justify-between items-center rounded-b-2xl shrink-0">
                    <button wire:click="close" class="px-4 py-2 text-sm font-bold text-apoyo hover:text-texto-principal transition-colors">
                        Cancelar
                    </button>
                    
                    <button wire:click="guardar" class="px-5 py-2 bg-estado-exitoBg hover:bg-estado-exitoBorde text-estado-exito border border-estado-exitoBorde shadow-sm text-sm font-bold rounded-lg transition-colors flex items-center gap-2">
                        <i class="ph-bold ph-check-circle text-lg animate-bounce"></i>
                        Confirmar y Derivar al Médico
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
