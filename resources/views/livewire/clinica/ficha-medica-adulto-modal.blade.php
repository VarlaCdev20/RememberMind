<div>
 <!-- Tarjeta Principal y Botón -->
 <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
 <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
 <div>
 <h3 class="text-xl font-extrabold text-titulo">Ficha Médica Base</h3>
 <p class="text-sm text-apoyo">Antecedentes, patologías crónicas y alergias.</p>
 </div>
 <button wire:click="abrirModalFichaMedica('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl bg-fondo-panel px-4 py-2 text-sm font-bold text-inverso shadow-sm hover:bg-fondo-panel active:scale-95 transition">
 <i class="ph-bold ph-heartbeat"></i> Gestionar Ficha
 </button>
 </div>

 <!-- Lista de Fichas Médicas -->
 @if($fichaList->isNotEmpty())
 <div class="mt-4 space-y-3">
 @foreach($fichaList as $ficha)
 <div class="rounded-xl border border-borde-suave bg-fondo-card p-4 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
 <div>
 <span class="text-xs font-bold uppercase text-apoyo">Ficha registrada el {{ $ficha->created_at->format('d/m/Y') }}</span>
 <div class="mt-1 flex flex-wrap gap-2">
 @if($ficha->hipertension) <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-md">Hipertensión</span> @endif
 @if($ficha->diabetes) <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-md">Diabetes</span> @endif
 @if($ficha->alergias) <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-1 rounded-md">Alergias</span> @endif
 <!-- Mostrar si hay más datos... -->
 </div>
 </div>
 <button wire:click="abrirModalFichaMedica('{{ $cod_am }}', {{ $ficha->cod_ficha_medica }})" class="text-titulo bg-fondo-panel hover:bg-fondo-app px-3 py-1.5 rounded-lg text-xs font-bold transition">
 <i class="ph-bold ph-pencil-simple mr-1"></i> Editar
 </button>
 </div>
 @endforeach
 </div>
 @else
 <div class="mt-4 text-center border-2 border-dashed border-borde-suave rounded-xl p-4 bg-fondo-card/50">
 <p class="text-sm font-bold text-apoyo">No hay ficha médica registrada.</p>
 </div>
 @endif
 </div>

 <x-ui.modal-livewire wire:model="showModal" title="{{ $isEditing ? 'Editar Ficha Médica' : 'Registrar Ficha Médica' }}" maxWidth="4xl">
 <x-slot name="icon">
 <i class="ph-bold ph-heartbeat text-terracota"></i>
 </x-slot>

 <form wire:submit.prevent="guardar" id="formFichaMedica">
 <div class="grid gap-6 md:grid-cols-2">
 <!-- 1. Enfermedades crónicas -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">1. Enfermedades Crónicas</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="hipertension" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Hipertensión Arterial</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="diabetes" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Diabetes</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="problemas_cardiacos" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Problemas Cardacos</span>
 </label>
 </div>
 </div>

 <!-- 2. Antecedentes neurológicos -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">2. Antecedentes Neurológicos</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="acv" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">ACV (Accidente Cerebrovascular)</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="parkinson" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Parkinson</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="epilepsia" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Epilepsia</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="alzheimer_diagnosticado" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Alzheimer Diagnosticado</span>
 </label>
 </div>
 </div>

 <!-- 3. Estado emocional / sueño -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">3. Estado Emocional / Sueño</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="depresion" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Depresión</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="ansiedad" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Ansiedad</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="problemas_sueno" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Problemas de Sueño (Insomnio)</span>
 </label>
 </div>
 </div>

 <!-- 4. Sensorial y Dolor -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">4. Sensorial y Dolor</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="problemas_visuales" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Problemas Visuales</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="problemas_auditivos" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Problemas Auditivos</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model="dolor_cronico" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Dolor Crónico</span>
 </label>
 </div>
 </div>

 <!-- 5. Alergias, Restricciones y Quirúrgicos -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm md:col-span-2">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">5. Otros Antecedentes</h4>
 <div class="grid gap-4 md:grid-cols-2">
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Alergias</label>
 <input type="text" wire:model="alergias" placeholder="Ej. Penicilina, polen..."
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
 @error('alergias') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Restricciones Alimentarias</label>
 <input type="text" wire:model="restricciones_alimentarias" placeholder="Ej. Intolerante a la lactosa"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
 @error('restricciones_alimentarias') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Hospitalizaciones Previas</label>
 <input type="text" wire:model="hospitalizaciones" placeholder="Describa brevemente"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
 @error('hospitalizaciones') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Cirugías Previas</label>
 <input type="text" wire:model="cirugias" placeholder="Describa brevemente"
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
 @error('cirugias') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 </div>
 </div>

 <!-- 6. Observación Médica General -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm md:col-span-2">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">6. Observación Médica</h4>
 <textarea wire:model="observacion_medica" rows="3" placeholder="Detalles adicionales sobre el estado de salud..."
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus"></textarea>
 @error('observacion_medica') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 </div>
 </form>

 <x-slot name="footer">
 <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-sm font-bold text-titulo transition hover:bg-fondo-panel">
 Cancelar
 </button>
 <button type="submit" form="formFichaMedica" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-sm font-bold text-inverso transition hover:bg-fondo-panel active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
 <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
 <span>{{ $isEditing ? 'Actualizar Ficha' : 'Guardar Ficha' }}</span>
 </button>
 </x-slot>
 </x-ui.modal-livewire>
</div>
