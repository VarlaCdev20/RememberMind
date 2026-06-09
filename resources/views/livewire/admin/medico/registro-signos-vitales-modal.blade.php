<div>
@if($mostrar)
<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-8"
     x-data x-on:keydown.escape.window="$wire.cerrar()">

    <div class="relative w-full max-w-xl rounded-[28px] border border-borde bg-fondo-card shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-borde px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                    <i class="ph-fill ph-heartbeat text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-titulo">Signos Vitales</h3>
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

            @error('general')
            <div class="flex items-center gap-2 rounded-xl bg-estado-errorBg p-3 text-sm font-bold text-estado-error">
                <i class="ph-bold ph-warning-circle text-base"></i> {{ $message }}
            </div>
            @enderror

            {{-- Fecha y hora --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Fecha <span class="text-estado-error">*</span></label>
                    <input wire:model="fecha" type="date"
                           class="w-full rounded-xl border @error('fecha') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                    @error('fecha')<span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Hora</label>
                    <input wire:model="hora" type="time"
                           class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none focus:border-borde-focus transition">
                </div>
            </div>

            {{-- Sección: Cardiovascular --}}
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <i class="ph-bold ph-heart text-estado-error text-sm"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-apoyo">Cardiovascular</span>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">PA Sistólica (mmHg)</label>
                        <input wire:model.live="pa_sistolica" type="number" min="50" max="300" placeholder="120"
                               class="w-full rounded-xl border @error('pa_sistolica') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                        @error('pa_sistolica')<span class="text-[9px] font-bold text-estado-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">PA Diastólica (mmHg)</label>
                        <input wire:model.live="pa_diastolica" type="number" min="30" max="200" placeholder="80"
                               class="w-full rounded-xl border @error('pa_diastolica') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                        @error('pa_diastolica')<span class="text-[9px] font-bold text-estado-error">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Frec. Cardíaca (bpm)</label>
                        <input wire:model.live="fc" type="number" min="20" max="300" placeholder="70"
                               class="w-full rounded-xl border @error('fc') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                        @error('fc')<span class="text-[9px] font-bold text-estado-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                {{-- Alerta PA --}}
                @if($pa_sistolica && ($pa_sistolica > 140 || $pa_sistolica < 90))
                <div class="mt-2 flex items-center gap-2 rounded-lg bg-estado-advertenciaBg px-3 py-1.5 text-xs font-bold text-estado-advertencia">
                    <i class="ph-bold ph-warning text-sm"></i>
                    PA {{ $pa_sistolica > 140 ? 'elevada' : 'baja' }} — requiere atención médica
                </div>
                @endif
            </div>

            {{-- Sección: Respiratorio --}}
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <i class="ph-bold ph-wind text-estado-info text-sm"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-apoyo">Respiratorio</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Frec. Respiratoria (rpm)</label>
                        <input wire:model.live="fr" type="number" min="5" max="60" placeholder="16"
                               class="w-full rounded-xl border @error('fr') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Saturación O2 (%)</label>
                        <input wire:model.live="saturacion" type="number" min="50" max="100" placeholder="98"
                               class="w-full rounded-xl border @error('saturacion') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                        @error('saturacion')<span class="text-[9px] font-bold text-estado-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                @if($saturacion && $saturacion < 92)
                <div class="mt-2 flex items-center gap-2 rounded-lg bg-estado-errorBg px-3 py-1.5 text-xs font-bold text-estado-error">
                    <i class="ph-bold ph-warning-octagon text-sm"></i>
                    Saturación crítica {{ $saturacion }}% — oxigenoterapia
                </div>
                @endif
            </div>

            {{-- Sección: Otros --}}
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <i class="ph-bold ph-thermometer text-boton-acento text-sm"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-apoyo">Temperatura y Glucemia</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Temperatura (°C)</label>
                        <input wire:model.live="temperatura" type="number" step="0.1" min="30" max="44" placeholder="36.5"
                               class="w-full rounded-xl border @error('temperatura') border-estado-error @else border-borde @enderror bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Glucosa (mg/dL)</label>
                        <input wire:model.live="glucosa" type="number" step="0.1" min="0" max="800" placeholder="100"
                               class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                    </div>
                </div>
            </div>

            {{-- Sección: Antropométricos --}}
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <i class="ph-bold ph-ruler text-estado-exito text-sm"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-apoyo">Peso y Talla</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Peso (kg)</label>
                        <input wire:model.live="peso" type="number" step="0.1" min="10" max="300" placeholder="kg"
                               class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">Talla (cm)</label>
                        <input wire:model.live="talla" type="number" step="0.1" min="50" max="250" placeholder="cm"
                               class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-bold text-apoyo">IMC</label>
                        <div class="flex h-[38px] items-center rounded-xl border border-borde bg-fondo-panel px-3 text-sm font-black
                                    {{ $imc ? ($imc < 18.5 ? 'text-estado-error' : ($imc < 25 ? 'text-estado-exito' : ($imc < 30 ? 'text-estado-advertencia' : 'text-estado-error'))) : 'text-apoyo' }}">
                            {{ $imc ? number_format($imc, 1) : '—' }}
                            @if($imc)
                            <span class="ml-1 text-[9px] font-bold text-apoyo">
                                {{ $imc < 18.5 ? 'Bajo' : ($imc < 25 ? 'Normal' : ($imc < 30 ? 'Sobrepeso' : 'Obesidad')) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Escala del dolor --}}
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-apoyo">
                    Escala Visual de Dolor (EVA 0-10)
                </label>
                <div class="flex items-center gap-3">
                    <input wire:model.live="dolor" type="range" min="0" max="10" step="1"
                           class="flex-1 accent-boton-acento">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full font-black text-sm
                                 {{ ($dolor ?? 0) >= 7 ? 'bg-estado-errorBg text-estado-error' : (($dolor ?? 0) >= 4 ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-exitoBg text-estado-exito') }}">
                        {{ $dolor ?? 0 }}
                    </span>
                    <span class="text-xs font-bold text-apoyo">
                        {{ ($dolor ?? 0) == 0 ? 'Sin dolor' : (($dolor ?? 0) < 4 ? 'Leve' : (($dolor ?? 0) < 7 ? 'Moderado' : 'Severo')) }}
                    </span>
                </div>
            </div>

            {{-- Observación --}}
            <div>
                <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Observación</label>
                <textarea wire:model="observacion" rows="2" placeholder="Comentarios adicionales..."
                          class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none"></textarea>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 border-t border-borde px-6 py-4">
            <button wire:click="cerrar" class="rm-btn-secondary h-10 px-5">Cancelar</button>
            <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar"
                    class="rm-btn-primary h-10 px-6 flex items-center gap-2">
                <span wire:loading.remove wire:target="guardar"><i class="ph-bold ph-check text-sm"></i> Registrar</span>
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
