<div>
 @if($mostrar)
 {{-- Overlay"Indestructible" --}}
 <div class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4 backdrop-blur-sm sm:p-6"
 x-data x-init="document.body.style.overflow = 'hidden'" x-on:destroy="document.body.style.overflow = 'auto'">
 
 {{-- Modal Container --}}
 <div class="relative w-full max-w-2xl rounded-[24px] border border-borde-suave bg-fondo-app shadow-[0_32px_64px_-12px_rgba(0,0,0,0.5)] flex flex-col max-h-[90vh]">
 
 {{-- Header --}}
 <div class="flex items-center justify-between border-b border-borde-suave p-4 sm:px-8">
 <div class="flex items-center gap-3">
 <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-boton-acento/10 text-terracota shadow-inner">
 <i class="ph-fill ph-brain text-2xl"></i>
 </div>
 <div>
 <span class="text-[9px] font-bold uppercase tracking-widest text-terracota/70">Suite Geriátrica</span>
 <h2 class="text-lg font-extrabold text-titulo">Registrar Evaluación Geriátrica Integral</h2>
 </div>
 </div>
 <button type="button" wire:click="cerrar" class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card/50 text-titulo transition hover:bg-boton-acento hover:text-inverso active:scale-95 shadow-sm">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </div>

 {{-- Form Content Scrollable --}}
 <form wire:submit.prevent="guardar" class="flex-1 overflow-y-auto p-6 sm:px-8 space-y-5 custom-scrollbar">
 
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 {{-- Área --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Área de Evaluación</label>
 <select wire:model.live="cod_area" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
 <option value="">-- Seleccionar Área --</option>
 @foreach($areas as $area)
 <option value="{{ $area->cod_area }}">{{ $area->nombre }}</option>
 @endforeach
 </select>
 @error('cod_area') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>

 {{-- Instrumento --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Instrumento / Escala</label>
 <select wire:model.live="cod_instrumento" @disabled(empty($cod_area)) class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus disabled:opacity-50">
 <option value="">-- Seleccionar Instrumento --</option>
 @foreach($instrumentos as $inst)
 <option value="{{ $inst->cod_instrumento }}">{{ $inst->nombre }} ({{ $inst->siglas }})</option>
 @endforeach
 </select>
 @error('cod_instrumento') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Contexto del Instrumento Seleccionado --}}
 @if($instrumentoSeleccionado)
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4 space-y-2 text-xs font-semibold text-titulo/80">
 <div class="flex items-center justify-between">
 <span class="font-black text-titulo text-sm">{{ $instrumentoSeleccionado->nombre }}</span>
 <span class="rounded-lg bg-boton-acento/10 px-2 py-0.5 text-[10px] font-bold text-terracota uppercase tracking-widest">{{ $instrumentoSeleccionado->tipo_resultado }}</span>
 </div>
 <p class="text-titulo/70 leading-relaxed text-[11px]">{{ $instrumentoSeleccionado->descripcion }}</p>
 <div class="flex flex-wrap gap-4 pt-2 border-t border-borde-suave">
 @if($instrumentoSeleccionado->puntaje_maximo)
 <div>
 <span class="text-[10px] font-bold uppercase text-titulo/50">Puntaje Máximo:</span>
 <span class="font-black text-titulo">{{ number_format($instrumentoSeleccionado->puntaje_maximo, 0) }}</span>
 </div>
 @endif
 @if($instrumentoSeleccionado->punto_corte_normal)
 <div>
 <span class="text-[10px] font-bold uppercase text-titulo/50">Punto de Corte Normal:</span>
 <span class="font-black text-parrafo">&ge; {{ number_format($instrumentoSeleccionado->punto_corte_normal, 0) }}</span>
 </div>
 @endif
 @if($instrumentoSeleccionado->punto_corte_riesgo)
 <div>
 <span class="text-[10px] font-bold uppercase text-titulo/50">Punto de Corte Riesgo:</span>
 <span class="font-black text-red-600">&le; {{ number_format($instrumentoSeleccionado->punto_corte_riesgo, 0) }}</span>
 </div>
 @endif
 </div>
 </div>
 @endif

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 {{-- Fecha --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Fecha de Evaluación</label>
 <input type="date" wire:model="fecha_eval" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
 @error('fecha_eval') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>

 {{-- Hora --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Hora (Opcional)</label>
 <input type="time" wire:model="hora_eval" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
 @error('hora_eval') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 {{-- Resultado Métrico o Puntaje --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">
 @if($instrumentoSeleccionado && $instrumentoSeleccionado->tipo_resultado === 'TIEMPO')
 Tiempo Obtenido (Segundos)
 @else
 Puntaje / Valor Numérico
 @endif
 </label>
 <input type="number" step="0.01" wire:model="puntaje_total" 
 @disabled($instrumentoSeleccionado && in_array($instrumentoSeleccionado->tipo_resultado, ['CUALITATIVO', 'FRACCION_VISUAL']))
 class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus disabled:opacity-50"
 placeholder="{{ ($instrumentoSeleccionado && in_array($instrumentoSeleccionado->tipo_resultado, ['CUALITATIVO', 'FRACCION_VISUAL'])) ? 'No aplica' : 'Ej: 24' }}">
 @error('puntaje_total') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>

 {{-- Categoría del Resultado --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Categoría / Resultado Clínico</label>
 <input type="text" wire:model="categoria_resultado" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus" 
 placeholder="Ej: Deterioro cognitivo leve, 20/40, Normal">
 @error('categoria_resultado') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 {{-- Nivel de Alerta --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Nivel de Alerta</label>
 <select wire:model="nivel_alerta" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
 <option value="NORMAL">Normal</option>
 <option value="PREVENTIVO">Preventivo</option>
 <option value="CRITICO">Crítico</option>
 </select>
 @error('nivel_alerta') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>

 {{-- Nivel de Riesgo (Opcional) --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Nivel de Riesgo (Opcional)</label>
 <input type="text" wire:model="nivel_riesgo" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus" 
 placeholder="Ej: Sin riesgo, Dependiente moderado">
 @error('nivel_riesgo') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Observaciones --}}
 <div>
 <label class="block text-[10px] font-bold uppercase tracking-wider text-titulo/60 mb-1.5">Observaciones Adicionales</label>
 <textarea wire:model="observaciones" rows="3" class="w-full rounded-xl border border-borde-suave bg-fondo-card/80 px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus resize-none" placeholder="Registrar hallazgos, aclaraciones o notas del evaluador..."></textarea>
 @error('observaciones') <span class="text-[10px] font-bold text-red-600 mt-1 block">{{ $message }}</span> @enderror
 </div>

 </form>

 {{-- Footer --}}
 <div class="flex items-center justify-end gap-3 border-t border-borde-suave p-4 sm:px-8 bg-fondo-panel">
 <button type="button" wire:click="cerrar" class="rounded-xl bg-fondo-card/80 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-titulo transition hover:bg-fondo-card active:scale-95 shadow-sm border border-borde-suave">
 Cancelar
 </button>
 <button type="button" wire:click="guardar" class="rounded-xl bg-boton-principal px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:bg-fondo-panel active:scale-95 shadow-md flex items-center gap-2">
 <i class="ph-fill ph-floppy-disk text-base"></i> Guardar Evaluación
 </button>
 </div>

 </div>
 </div>
 @endif
</div>
