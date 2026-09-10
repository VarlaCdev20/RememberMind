<div>
 <!-- Tarjeta Principal y Botón -->
 <div class="rounded-[24px] border border-borde bg-fondo-panel p-5 shadow-sm">
 <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
 <div>
 <h3 class="text-xl font-extrabold text-titulo">Valoración Funcional</h3>
 <p class="text-sm text-apoyo">Nivel de dependencia y autonomía del adulto mayor.</p>
 </div>
 <button wire:click="abrirModalValoracion('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl bg-fondo-panel px-4 py-2 text-sm font-bold text-inverso shadow-sm hover:bg-fondo-panel active:scale-95 transition">
 <i class="ph-bold ph-wheelchair"></i> Nueva Valoración
 </button>
 </div>

 @if(isset($valoracionList) && $valoracionList->isNotEmpty())
 <div class="mt-4 space-y-3">
 @foreach($valoracionList as $val)
 <div class="rounded-xl border border-borde-suave bg-fondo-card p-4 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
 <div>
 <span class="text-xs font-bold uppercase text-apoyo">
 Valoración del {{ $val->fecha_valoracion->format('d/m/Y') }}
 </span>
 <div class="mt-1">
 <span class="px-2 py-1 rounded-md text-xs font-bold uppercase 
 {{ $val->nivel_dependencia === 'INDEPENDIENTE' ? 'bg-green-100 text-green-700' : '' }}
 {{ $val->nivel_dependencia === 'LEVE' ? 'bg-yellow-100 text-yellow-700' : '' }}
 {{ $val->nivel_dependencia === 'MODERADO' ? 'bg-orange-100 text-orange-700' : '' }}
 {{ $val->nivel_dependencia === 'SEVERO' ? 'bg-red-100 text-red-700' : '' }}">
 {{ $val->nivel_dependencia }}
 </span>
 </div>
 @if($val->observacion)
 <p class="text-xs text-apoyo mt-2 line-clamp-1" title="{{ $val->observacion }}">{{ $val->observacion }}</p>
 @endif
 </div>
 <button wire:click="abrirModalValoracion('{{ $cod_am }}', {{ $val->cod_val_func }})" class="text-titulo bg-fondo-panel hover:bg-fondo-app px-3 py-1.5 rounded-lg text-xs font-bold transition shrink-0">
 <i class="ph-bold ph-pencil-simple mr-1"></i> Ver Detalles
 </button>
 </div>
 @endforeach
 </div>
 @else
 <div class="mt-4 text-center border-2 border-dashed border-borde-suave rounded-xl p-4 bg-fondo-card/50">
 <p class="text-sm font-bold text-apoyo">No hay valoraciones funcionales registradas.</p>
 </div>
 @endif
 </div>

 <x-ui.modal-livewire wire:model="showModal" title="{{ $isEditing ? 'Editar Valoración Funcional' : 'Registrar Valoración Funcional' }}" maxWidth="3xl">
 <x-slot name="icon">
 <i class="ph-bold ph-wheelchair text-terracota"></i>
 </x-slot>

 <form wire:submit.prevent="guardar" id="formValoracion">
 <div class="grid gap-6 md:grid-cols-2">
 <!-- Fecha y Nivel -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm md:col-span-2">
 <div class="grid gap-4 md:grid-cols-2 items-center">
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Fecha de Valoración *</label>
 <input type="date" wire:model="fecha_valoracion"
 class="w-full rounded-xl border {{ $errors->has('fecha_valoracion') ? 'border-terracota' : 'border-borde-suave' }} bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus">
 @error('fecha_valoracion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 <div class="text-right">
 <span class="block text-[11px] font-bold uppercase tracking-widest text-apoyo">Nivel de Dependencia Calculado</span>
 <div class="mt-1 inline-flex items-center rounded-xl border border-borde-suave bg-fondo-panel px-4 py-2">
 <span class="text-lg font-extrabold text-titulo">{{ $nivel_dependencia }}</span>
 </div>
 </div>
 </div>
 </div>

 <!-- 1. Actividades Básicas -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">1. Actividades de la Vida Diaria</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="come_solo" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Come solo/a</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="se_bana_solo" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Se baña solo/a</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="se_viste_solo" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Se viste solo/a</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="va_bano_solo" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Va al baño solo/a</span>
 </label>
 </div>
 </div>

 <!-- 2. Movilidad y Apoyo -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">2. Movilidad y Apoyos</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="camina_solo" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Camina solo/a</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="usa_baston" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Usa bastón</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="usa_andador" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Usa andador</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="usa_silla_ruedas" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Usa silla de ruedas</span>
 </label>
 </div>
 </div>

 <!-- 3. Comunicación y Sensibilidad -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">3. Comunicación y Sentidos</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="baja_vision" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Baja visión</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="baja_audicion" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Baja audición</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="dificultad_hablar" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Dificultad para hablar</span>
 </label>
 </div>
 </div>

 <!-- 4. Cognitivo y Ambiental -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">4. Cognitivo y Ambiental</h4>
 <div class="space-y-3">
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="molestia_luz" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Molestia a la luz</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="molestia_ruido" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Molestia al ruido</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer">
 <input type="checkbox" wire:model.live="se_asusta_facil" class="h-4 w-4 rounded border-borde-suave text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-titulo">Se asusta fácil</span>
 </label>
 <label class="flex items-center gap-2 cursor-pointer text-terracota">
 <input type="checkbox" wire:model.live="necesita_supervision" class="h-4 w-4 rounded border-terracota text-terracota focus:ring-borde-focus">
 <span class="text-sm font-bold text-terracota">Necesita Supervisión Constante</span>
 </label>
 </div>
 </div>

 <!-- Observaciones -->
 <div class="rounded-xl border border-borde-suave bg-fondo-card/50 p-4 shadow-sm md:col-span-2">
 <h4 class="mb-3 text-sm font-bold uppercase text-titulo">5. Observación Adicional</h4>
 <textarea wire:model="observacion" rows="3" placeholder="Detalles de la valoración, comportamiento observado..."
 class="w-full rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-sm text-titulo focus:border-borde-focus focus:ring-borde-focus"></textarea>
 @error('observacion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
 </div>
 </div>
 </form>

 <x-slot name="footer">
 <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-sm font-bold text-titulo transition hover:bg-fondo-panel">
 Cancelar
 </button>
 <button type="submit" form="formValoracion" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-sm font-bold text-inverso transition hover:bg-fondo-panel active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
 <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
 <span>{{ $isEditing ? 'Actualizar Valoración' : 'Guardar Valoración' }}</span>
 </button>
 </x-slot>
 </x-ui.modal-livewire>
</div>
