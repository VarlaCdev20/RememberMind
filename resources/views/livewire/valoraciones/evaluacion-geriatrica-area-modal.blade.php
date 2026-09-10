<div>
    @if($mostrar)
    {{-- Overlay --}}
    <div class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4 backdrop-blur-sm sm:p-6"
         x-data x-init="document.body.style.overflow = 'hidden'"
         x-on:destroy="document.body.style.overflow = 'auto'">

        {{-- Modal Container --}}
        <div class="relative w-full max-w-2xl rounded-[24px] border border-borde-suave bg-fondo-app shadow-[0_32px_64px_-12px_rgba(0,0,0,0.5)] flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-borde-suave p-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento shadow-inner">
                        <i class="ph-fill ph-brain text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-boton-acento/70">Evaluación Geriátrica</span>
                        <h2 class="text-lg font-extrabold text-titulo leading-tight">
                            {{ $nombreArea ? 'Nueva Evaluación — ' . $nombreArea : 'Nueva Evaluación Geriátrica' }}
                        </h2>
                    </div>
                </div>
                <button type="button" wire:click="cerrar"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card/50 text-titulo transition hover:bg-estado-peligro hover:text-white active:scale-95 shadow-sm">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            {{-- Form Content Scrollable --}}
            <form wire:submit.prevent="guardar" class="flex-1 overflow-y-auto p-6 sm:px-8 space-y-5 custom-scrollbar">

                {{-- Selector de Paciente --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                        Adulto Mayor <span class="text-estado-peligro">*</span>
                    </label>
                    <select wire:model.live="cod_am"
                            class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
                        <option value="">-- Seleccionar Paciente --</option>
                        @foreach($pacientes as $pac)
                        <option value="{{ $pac->cod_am }}"
                                @if($cod_am === $pac->cod_am) selected @endif>
                            {{ $pac->nombres }} {{ $pac->ap_paterno }} — CI: {{ $pac->ci }}
                        </option>
                        @endforeach
                    </select>
                    @error('cod_am')
                    <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Instrumento / Escala --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                        Instrumento / Escala
                        @if($nombreArea)
                        <span class="ml-1 text-boton-acento">({{ $nombreArea }})</span>
                        @endif
                        <span class="text-estado-peligro">*</span>
                    </label>
                    <select wire:model.live="cod_instrumento"
                            @disabled(empty($cod_area))
                            class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus disabled:opacity-50">
                        <option value="">-- Seleccionar Instrumento --</option>
                        @foreach($instrumentos as $inst)
                        <option value="{{ $inst->cod_instrumento }}">
                            {{ $inst->nombre }} ({{ $inst->siglas }})
                        </option>
                        @endforeach
                    </select>
                    @error('cod_instrumento')
                    <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Info del instrumento seleccionado --}}
                @if($instrumentoSeleccionado)
                <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 space-y-2 text-xs font-semibold text-titulo/80">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-titulo text-sm">{{ $instrumentoSeleccionado->nombre }}</span>
                        <span class="rounded-lg bg-boton-acento/10 px-2 py-0.5 text-[10px] font-black text-boton-acento uppercase tracking-widest">
                            {{ $instrumentoSeleccionado->tipo_resultado }}
                        </span>
                    </div>
                    @if($instrumentoSeleccionado->descripcion)
                    <p class="text-titulo/70 leading-relaxed text-[11px]">{{ $instrumentoSeleccionado->descripcion }}</p>
                    @endif
                    <div class="flex flex-wrap gap-4 pt-2 border-t border-borde-suave">
                        @if($instrumentoSeleccionado->puntaje_maximo)
                        <div>
                            <span class="text-[10px] font-bold uppercase text-titulo/50">Puntaje Máximo: </span>
                            <span class="font-black text-titulo">{{ number_format($instrumentoSeleccionado->puntaje_maximo, 0) }}</span>
                        </div>
                        @endif
                        @if($instrumentoSeleccionado->punto_corte_normal)
                        <div>
                            <span class="text-[10px] font-bold uppercase text-titulo/50">Punto corte normal: </span>
                            <span class="font-black text-estado-exito">≥ {{ number_format($instrumentoSeleccionado->punto_corte_normal, 0) }}</span>
                        </div>
                        @endif
                        @if($instrumentoSeleccionado->punto_corte_riesgo)
                        <div>
                            <span class="text-[10px] font-bold uppercase text-titulo/50">Punto corte riesgo: </span>
                            <span class="font-black text-estado-peligro">≤ {{ number_format($instrumentoSeleccionado->punto_corte_riesgo, 0) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Fecha y Hora --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                            Fecha de Evaluación <span class="text-estado-peligro">*</span>
                        </label>
                        <input type="date" wire:model="fecha_eval"
                               class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
                        @error('fecha_eval')
                        <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Hora (Opcional)</label>
                        <input type="time" wire:model="hora_eval"
                               class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
                    </div>
                </div>

                {{-- Puntaje y Categoría --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                            @if($instrumentoSeleccionado && $instrumentoSeleccionado->tipo_resultado === 'TIEMPO')
                                Tiempo Obtenido (segundos)
                            @elseif($instrumentoSeleccionado && in_array($instrumentoSeleccionado->tipo_resultado, ['CUALITATIVO', 'FRACCION_VISUAL']))
                                Puntaje (No aplica)
                            @else
                                Puntaje / Valor numérico
                                @if($instrumentoSeleccionado && $instrumentoSeleccionado->puntaje_maximo)
                                    <span class="text-boton-acento">(máx. {{ number_format($instrumentoSeleccionado->puntaje_maximo, 0) }})</span>
                                @endif
                            @endif
                        </label>
                        <input type="number"
                               step="0.01"
                               wire:model="puntaje_total"
                               @disabled($instrumentoSeleccionado && in_array($instrumentoSeleccionado->tipo_resultado, ['CUALITATIVO', 'FRACCION_VISUAL']))
                               class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus disabled:opacity-40 disabled:cursor-not-allowed"
                               placeholder="{{ ($instrumentoSeleccionado && in_array($instrumentoSeleccionado->tipo_resultado, ['CUALITATIVO', 'FRACCION_VISUAL'])) ? 'No aplica' : 'Ej: 24' }}">
                        @error('puntaje_total')
                        <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                            Resultado / Categoría clínica
                        </label>
                        <input type="text"
                               wire:model="categoria_resultado"
                               class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus"
                               placeholder="Ej: Deterioro leve, Normal, Dependiente parcial">
                        @error('categoria_resultado')
                        <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Nivel de Alerta y Riesgo --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                            Nivel de Alerta <span class="text-estado-peligro">*</span>
                        </label>
                        <select wire:model="nivel_alerta"
                                class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
                            <option value="NORMAL">Normal</option>
                            <option value="PREVENTIVO">Preventivo</option>
                            <option value="CRITICO">Crítico</option>
                        </select>
                        @error('nivel_alerta')
                        <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                            Nivel de Riesgo (Opcional)
                        </label>
                        <input type="text"
                               wire:model="nivel_riesgo"
                               class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus"
                               placeholder="Ej: Sin riesgo, Riesgo moderado">
                    </div>
                </div>

                {{-- Observaciones --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
                        Observaciones del Evaluador
                    </label>
                    <textarea wire:model="observaciones"
                              rows="3"
                              class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus resize-none"
                              placeholder="Hallazgos relevantes, contexto de la evaluación, recomendaciones..."></textarea>
                    @error('observaciones')
                    <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span>
                    @enderror
                </div>

            </form>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-borde-suave p-4 sm:px-8 bg-fondo-panel">
                <button type="button" wire:click="cerrar"
                        class="rounded-xl border border-borde-suave bg-fondo-card/80 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-titulo transition hover:bg-fondo-card active:scale-95 shadow-sm">
                    Cancelar
                </button>
                <button type="button" wire:click="guardar"
                        class="rounded-xl bg-boton-principal px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:opacity-90 active:scale-95 shadow-md flex items-center gap-2">
                    <i class="ph-fill ph-floppy-disk text-base"></i>
                    Guardar Evaluación
                </button>
            </div>

        </div>
    </div>
    @endif
</div>
