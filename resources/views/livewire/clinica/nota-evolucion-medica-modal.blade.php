<div>
@if($mostrar)
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-8"
     x-data x-on:keydown.escape.window="$wire.cerrar()">

    <div class="relative w-full max-w-2xl rounded-[28px] border border-borde bg-fondo-card shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-borde px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-fill ph-note-pencil text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-titulo">Nota de Evolución Médica</h3>
                    @if($adulto)
                    <p class="text-xs font-semibold text-apoyo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</p>
                    @endif
                </div>
            </div>
            <button wire:click="cerrar" class="flex h-8 w-8 items-center justify-center rounded-full text-apoyo hover:bg-fondo-panel hover:text-titulo transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <div class="space-y-5 p-6">

            {{-- Tipo y fecha --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Tipo de nota</label>
                    <select wire:model.live="tipo_nota"
                            class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                        <option value="EVOLUCION">Evolución clínica</option>
                        <option value="INGRESO">Nota de ingreso</option>
                        <option value="EGRESO">Nota de egreso</option>
                        <option value="INTERCONSULTA">Interconsulta</option>
                        <option value="URGENCIA">Urgencia/Emergencia</option>
                        <option value="PROCEDIMIENTO">Procedimiento</option>
                    </select>
                    @error('tipo_nota')<span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>@enderror
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Fecha</label>
                        <input wire:model="fecha" type="date"
                               class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                        @error('fecha')<span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Hora</label>
                        <input wire:model="hora" type="time"
                               class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                    </div>
                </div>
            </div>

            {{-- SOAP divider --}}
            <div class="flex items-center gap-3">
                <div class="h-px flex-1 bg-borde"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-apoyo">Nota SOAP</span>
                <div class="h-px flex-1 bg-borde"></div>
            </div>

            {{-- S - Subjetivo --}}
            <div>
                <label class="mb-1.5 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-estado-infoBg text-estado-info text-[10px] font-black">S</span>
                    Subjetivo — motivo de consulta, síntomas referidos
                </label>
                <textarea wire:model="subjetivo" rows="2"
                          placeholder="¿Qué refiere el paciente? Síntomas, quejas, cambios percibidos..."
                          class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
            </div>

            {{-- O - Objetivo --}}
            <div>
                <label class="mb-1.5 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-estado-advertenciaBg text-estado-advertencia text-[10px] font-black">O</span>
                    Objetivo — hallazgos clínicos observados
                </label>
                <textarea wire:model="objetivo" rows="2"
                          placeholder="Exploración física, resultados de exámenes, signos observados..."
                          class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
            </div>

            {{-- A - Valoración --}}
            <div>
                <label class="mb-1.5 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-boton-acento/10 text-boton-acento text-[10px] font-black">A</span>
                    Valoración / Diagnóstico <span class="text-estado-error">*</span>
                </label>
                <textarea wire:model="valoracion" rows="3"
                          placeholder="Diagnóstico, impresión clínica, análisis del caso..."
                          class="w-full rounded-xl border @error('valoracion') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
                @error('valoracion')<span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>@enderror
            </div>

            {{-- P - Plan --}}
            <div>
                <label class="mb-1.5 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-estado-exitoBg text-estado-exito text-[10px] font-black">P</span>
                    Plan de tratamiento <span class="text-estado-error">*</span>
                </label>
                <textarea wire:model="plan" rows="3"
                          placeholder="Medicamentos, indicaciones, controles, interconsultas solicitadas..."
                          class="w-full rounded-xl border @error('plan') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
                @error('plan')<span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>@enderror
            </div>

            {{-- Signos vitales opcionales --}}
            <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                <button wire:click="$toggle('incluirSignos')"
                        class="flex w-full items-center gap-2 text-sm font-bold text-titulo">
                    <div class="flex h-5 w-5 items-center justify-center rounded border-2 transition
                                {{ $incluirSignos ? 'border-boton-acento bg-boton-acento text-white' : 'border-borde bg-fondo-card' }}">
                        @if($incluirSignos)<i class="ph-bold ph-check text-[10px]"></i>@endif
                    </div>
                    <i class="ph-bold ph-heartbeat text-apoyo"></i>
                    Incluir signos vitales en esta nota
                </button>

                @if($incluirSignos)
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">PA Sistólica</label>
                        <input wire:model.live="pa_sistolica" type="number" min="50" max="300" placeholder="mmHg"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">PA Diastólica</label>
                        <input wire:model.live="pa_diastolica" type="number" min="30" max="200" placeholder="mmHg"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">FC (bpm)</label>
                        <input wire:model.live="fc" type="number" min="20" max="300" placeholder="lpm"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">FR (rpm)</label>
                        <input wire:model.live="fr" type="number" min="5" max="60" placeholder="rpm"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">Temp (°C)</label>
                        <input wire:model.live="temperatura" type="number" step="0.1" min="30" max="44" placeholder="°C"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">SpO2 (%)</label>
                        <input wire:model.live="saturacion" type="number" min="50" max="100" placeholder="%"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">Glucosa (mg/dL)</label>
                        <input wire:model.live="glucosa" type="number" step="0.1" min="0" max="800" placeholder="mg/dL"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo uppercase tracking-wider">Peso (kg)</label>
                        <input wire:model.live="peso" type="number" step="0.1" min="10" max="300" placeholder="kg"
                               class="w-full rounded-lg border border-borde bg-fondo-card px-2.5 py-2 text-sm font-semibold text-titulo outline-none focus:border-borde-focus">
                    </div>
                </div>
                @endif
            </div>

            {{-- Observaciones --}}
            <div>
                <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Observaciones adicionales</label>
                <textarea wire:model="observaciones" rows="2"
                          placeholder="Notas adicionales, precauciones, seguimiento especial..."
                          class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 border-t border-borde px-6 py-4">
            <button wire:click="cerrar"
                    class="rm-btn-secondary h-10 px-5">
                Cancelar
            </button>
            <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar"
                    class="rm-btn-primary h-10 px-6 flex items-center gap-2">
                <span wire:loading.remove wire:target="guardar"><i class="ph-bold ph-check text-sm"></i> Guardar nota</span>
                <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    Guardando...
                </span>
            </button>
        </div>
    </div>
</div>
@endif
</div>
