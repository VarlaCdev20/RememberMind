<div>
@if($isOpen && $adulto)
<div class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-8"
     x-data x-on:keydown.escape.window="$wire.close()">

    <div class="relative w-full max-w-2xl rounded-[28px] border border-borde bg-fondo-card shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="flex items-center justify-between rounded-t-[28px] border-b border-borde bg-estado-infoBg px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-estado-info text-white">
                    <i class="ph-fill ph-check-square-offset text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-estado-info">Decisión Final de Admisión</h3>
                    <p class="text-xs font-semibold text-estado-info/70">
                        {{ $adulto->nombres }} {{ $adulto->ap_paterno }} · CI: {{ $adulto->ci }}
                    </p>
                </div>
            </div>
            <button wire:click="close"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-estado-info hover:bg-estado-info hover:text-white transition">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <div class="space-y-5 p-6">

            {{-- Selector de decisión --}}
            <div>
                <label class="mb-3 block text-xs font-black uppercase tracking-wider text-apoyo">
                    Decisión médica <span class="text-estado-error">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['ADMITIDO_NORMAL',            'ph-check-circle',       'estado-exito',      'Admisión normal'],
                        ['ADMITIDO_CON_SEGUIMIENTO',   'ph-eye',                'estado-info',       'Admisión c/ seguimiento'],
                        ['ADMITIDO_CON_CUIDADO_ESPECIAL','ph-warning-circle',   'estado-advertencia','Admisión c/ cuidado especial'],
                        ['DERIVADO',                   'ph-arrow-u-up-right',   'estado-error',      'Derivar / Rechazar'],
                    ] as [$val, $icon, $color, $label])
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="decision" value="{{ $val }}" class="peer sr-only">
                        <div class="flex flex-col items-center gap-1.5 rounded-2xl border-2 border-borde bg-fondo-panel p-3 text-center transition
                                    peer-checked:border-{{ $color }} peer-checked:bg-{{ $color }}Bg hover:bg-fondo-card">
                            <i class="ph-bold {{ $icon }} text-2xl text-{{ $color }}"></i>
                            <span class="text-xs font-black text-titulo">{{ $label }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('decision')
                <span class="mt-1 block text-[10px] font-bold text-estado-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Panel dinámico según decisión --}}
            <div class="min-h-[100px] rounded-2xl border border-borde bg-fondo-panel p-4">
                @if($decision === 'ADMITIDO_NORMAL')
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                        Motivo de la decisión <span class="text-estado-error">*</span>
                    </label>
                    <textarea wire:model="motivo_decision" rows="3"
                              placeholder="Paciente apto para convivencia general. Sin requerimientos especiales..."
                              class="w-full rounded-xl border @error('motivo_decision') border-estado-error @else border-borde @enderror bg-fondo-card px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                    @error('motivo_decision')
                    <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                    @enderror
                </div>

                @elseif($decision === 'ADMITIDO_CON_SEGUIMIENTO')
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                        Protocolo de seguimiento requerido <span class="text-estado-error">*</span>
                    </label>
                    <textarea wire:model="seguimiento_requerido" rows="3"
                              placeholder="Detalle qué seguimiento clínico o de enfermería se debe llevar a cabo..."
                              class="w-full rounded-xl border @error('seguimiento_requerido') border-estado-error @else border-borde @enderror bg-fondo-card px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                    @error('seguimiento_requerido')
                    <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                    @enderror
                </div>

                @elseif($decision === 'ADMITIDO_CON_CUIDADO_ESPECIAL')
                <div>
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                        Especificación del cuidado especial <span class="text-estado-error">*</span>
                    </label>
                    <textarea wire:model="cuidado_especial_requerido" rows="3"
                              placeholder="Cuidados paliativos, movilización especial, nutrición parenteral..."
                              class="w-full rounded-xl border @error('cuidado_especial_requerido') border-estado-error @else border-borde @enderror bg-fondo-card px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                    @error('cuidado_especial_requerido')
                    <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                    @enderror
                </div>

                @elseif($decision === 'DERIVADO')
                <div class="space-y-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                            Motivo clínico de derivación <span class="text-estado-error">*</span>
                        </label>
                        <textarea wire:model="motivo_derivacion" rows="2"
                                  placeholder="Requiere internación hospitalaria, psiquiátrica, etc."
                                  class="w-full rounded-xl border @error('motivo_derivacion') border-estado-error @else border-borde @enderror bg-fondo-card px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
                        @error('motivo_derivacion')
                        <span class="mt-0.5 text-[10px] font-bold text-estado-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Institución sugerida (opcional)</label>
                        <input type="text" wire:model="institucion_derivada"
                               placeholder="Hospital de Clínicas, Instituto Psiquiátrico..."
                               class="w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-sm font-bold text-titulo outline-none focus:border-borde-focus uppercase transition">
                    </div>
                </div>

                @else
                <div class="flex h-16 items-center justify-center text-sm text-apoyo italic">
                    Seleccione una decisión para habilitar los campos.
                </div>
                @endif
            </div>

            {{-- Recomendación final --}}
            <div>
                <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Recomendación final adicional (opcional)</label>
                <textarea wire:model="recomendacion_final" rows="2"
                          placeholder="Notas adicionales para la familia o la administración..."
                          class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo placeholder-apoyo/50 outline-none focus:border-borde-focus transition resize-none uppercase"></textarea>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between rounded-b-[28px] border-t border-borde px-6 py-4">
            <button wire:click="close" class="rm-btn-secondary h-10 px-5">Cancelar</button>
            <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar"
                    class="flex h-10 items-center gap-2 rounded-xl bg-boton-acento px-6 text-sm font-black text-white transition hover:bg-boton-acento/80 disabled:opacity-50">
                <span wire:loading.remove wire:target="guardar">
                    <i class="ph-bold ph-paper-plane-tilt mr-1"></i> Confirmar dictamen médico
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
