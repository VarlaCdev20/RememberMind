<div>
@if($isOpen && $adulto)
<div class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-8"
     x-data x-on:keydown.escape.window="$wire.close()">

    <div class="relative w-full max-w-5xl rounded-[28px] border border-borde bg-fondo-card shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center justify-between rounded-t-[28px] border-b border-borde bg-estado-advertenciaBg px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-estado-advertencia text-white">
                    <i class="ph-fill ph-first-aid text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-estado-advertencia">Valoración Médica General</h3>
                    <p class="text-xs font-semibold text-estado-advertencia/70">
                        {{ $adulto->nombres }} {{ $adulto->ap_paterno }} · CI: {{ $adulto->ci }}
                    </p>
                </div>
            </div>
            <button wire:click="close"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-estado-advertencia hover:bg-estado-advertencia hover:text-white transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- ── Columna izquierda: Signos vitales + Alergias ── --}}
                <div class="space-y-5">

                    {{-- Signos vitales --}}
                    <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                        <div class="mb-3 flex items-center gap-2">
                            <i class="ph-bold ph-heartbeat text-estado-error text-sm"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-apoyo">Signos Vitales</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">PA Sistólica</label>
                                <input type="number" wire:model="pa_sistolica" placeholder="mmHg"
                                       class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">PA Diastólica</label>
                                <input type="number" wire:model="pa_diastolica" placeholder="mmHg"
                                       class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">FC (lpm)</label>
                                <input type="number" wire:model="fc" placeholder="lpm"
                                       class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">FR (rpm)</label>
                                <input type="number" wire:model="fr" placeholder="rpm"
                                       class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">Temperatura</label>
                                <input type="number" step="0.1" wire:model="temp" placeholder="°C"
                                       class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus transition">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">SpO2 (%)</label>
                                <input type="number" wire:model="sato2" placeholder="%"
                                       class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus transition">
                            </div>
                        </div>
                    </div>

                    {{-- Alergias / Medicación --}}
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Alergias conocidas</label>
                            <textarea wire:model="alergias" rows="2"
                                      placeholder="Ninguna / Penicilina / AINEs..."
                                      class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Medicación actual referida</label>
                            <textarea wire:model="medicacion_actual" rows="2"
                                      placeholder="Fármacos que el paciente tomaba antes del ingreso..."
                                      class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Columna central/derecha: Evaluación clínica ── --}}
                <div class="space-y-5 lg:col-span-2">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                                Condición médica general <span class="text-estado-error">*</span>
                            </label>
                            <select wire:model="condicion_medica_general"
                                    class="w-full rounded-xl border @error('condicion_medica_general') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                                <option value="">Seleccione...</option>
                                <option value="ESTABLE">ESTABLE</option>
                                <option value="REQUIERE_OBSERVACION">REQUIERE OBSERVACIÓN</option>
                                <option value="DELICADA">DELICADA</option>
                                <option value="NO_APTA">NO APTA PARA INGRESO</option>
                            </select>
                            @error('condicion_medica_general')
                            <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                                Nivel de dependencia <span class="text-estado-error">*</span>
                            </label>
                            <select wire:model="nivel_dependencia"
                                    class="w-full rounded-xl border @error('nivel_dependencia') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                                <option value="">Seleccione...</option>
                                <option value="BAJO">BAJO — Independiente</option>
                                <option value="MEDIO">MEDIO — Asistencia parcial</option>
                                <option value="ALTO">ALTO — Asistencia total</option>
                                <option value="CRITICO">CRÍTICO — Cuidados paliativos</option>
                            </select>
                            @error('nivel_dependencia')
                            <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Estado neurológico</label>
                            <select wire:model="estado_neurologico_basico"
                                    class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                                <option value="">Seleccione...</option>
                                <option value="NORMAL">Normal / Íntegro</option>
                                <option value="CONFUSION_LEVE">Confusión leve</option>
                                <option value="DESORIENTACION">Desorientación moderada</option>
                                <option value="ALTERADO">Alterado / Deterioro severo</option>
                                <option value="NO_EVALUABLE">No evaluable</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                                Estado cognitivo aparente <span class="text-estado-error">*</span>
                            </label>
                            <select wire:model="estado_cognitivo_aparente"
                                    class="w-full rounded-xl border @error('estado_cognitivo_aparente') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                                <option value="">Seleccione...</option>
                                <option value="CONSERVADO">Conservado</option>
                                <option value="OLVIDOS_LEVES">Olvidos leves benignos</option>
                                <option value="DESORIENTACION">Desorientación / Demencia leve</option>
                                <option value="ALTERACION_IMPORTANTE">Alteración importante / Demencia avanzada</option>
                                <option value="NO_EVALUABLE">No evaluable</option>
                            </select>
                            @error('estado_cognitivo_aparente')
                            <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Diagnósticos referidos</label>
                            <textarea wire:model="diagnosticos_referidos" rows="2"
                                      placeholder="HTA, Diabetes tipo 2, EPOC..."
                                      class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Antecedentes (cirugías, traumas)</label>
                            <textarea wire:model="antecedentes_medicos" rows="2"
                                      placeholder="Colecistectomía 2015, fractura cadera..."
                                      class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                        </div>
                    </div>

                    {{-- Requerimientos institucionales --}}
                    <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                        <div class="mb-3 text-[10px] font-black uppercase tracking-widest text-apoyo">Requerimientos clínicos institucionales</div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach([
                                ['requiere_control_medicacion',   'Control estricto de medicación'],
                                ['requiere_control_signos',       'Monitoreo diario de signos vitales'],
                                ['requiere_seguimiento_cognitivo','Seguimiento cognitivo específico'],
                            ] as [$prop, $label])
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" wire:model="{{ $prop }}"
                                       class="h-4 w-4 rounded border-borde text-estado-advertencia focus:ring-estado-advertencia">
                                <span class="text-xs font-semibold text-titulo">{{ $label }}</span>
                            </label>
                            @endforeach
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" wire:model.live="requiere_cuidado_especial"
                                       class="h-4 w-4 rounded border-borde text-boton-acento focus:ring-boton-acento">
                                <span class="text-xs font-semibold text-boton-acento">Requiere cuidado especial adicional</span>
                            </label>
                        </div>
                        @if($requiere_cuidado_especial)
                        <div class="mt-3">
                            <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase">Detalle del cuidado especial <span class="text-estado-error">*</span></label>
                            <input type="text" wire:model="detalle_cuidado_especial"
                                   class="w-full rounded-xl border @error('detalle_cuidado_especial') border-estado-error @else border-borde @enderror bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus uppercase transition">
                            @error('detalle_cuidado_especial')
                            <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                    </div>

                    {{-- Observación y conclusión --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                            Conclusión médica para administración <span class="text-estado-error">*</span>
                        </label>
                        <textarea wire:model="observacion_medica" rows="3"
                                  placeholder="Redacte su conclusión respecto a la viabilidad del ingreso del paciente..."
                                  class="w-full rounded-xl border @error('observacion_medica') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                        @error('observacion_medica')
                        <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between rounded-b-[28px] border-t border-borde px-6 py-4">
            <button wire:click="close" class="rm-btn-secondary h-10 px-5">Cancelar</button>
            <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar"
                    class="flex h-10 items-center gap-2 rounded-xl bg-estado-advertencia px-6 text-sm font-black text-white transition hover:bg-estado-advertencia/80 disabled:opacity-50">
                <span wire:loading.remove wire:target="guardar">
                    <i class="ph-bold ph-check-circle mr-1"></i> Confirmar valoración médica
                </span>
                <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>
    </div>
</div>
@endif
</div>
