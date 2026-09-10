<div>
 <x-ui.modal-livewire wire:model="showModal" title="Registrar Administración de Medicación" maxWidth="2xl">
 <x-slot name="icon">
 <i class="ph-bold ph-calendar-check text-boton-acento"></i>
 </x-slot>

 <form wire:submit.prevent="guardar" id="formAdministracion">
 
 <!-- Resumen de Medicación -->
 <div class="mb-6 rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm backdrop-blur-xl flex items-center justify-between">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-widest text-boton-acento">Medicamento a Administrar</span>
 <h3 class="mt-1 text-lg font-extrabold text-titulo">{{ $medicamento_nombre ?: 'Sin Seleccionar' }}</h3>
 <div class="mt-1 flex items-center gap-2 text-xs font-bold text-apoyo">
 <i class="ph-bold ph-clock"></i> Programado para las: {{ $hora_programada }}
 </div>
 </div>
 <div class="h-12 w-12 flex items-center justify-center rounded-full bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-pill text-2xl"></i>
 </div>
 </div>

 <div class="grid gap-6 md:grid-cols-2">
 <!-- 1. Estado de Administración -->
 <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-card p-5 shadow-sm md:col-span-2 relative overflow-hidden">
 <div class="absolute top-0 left-0 w-2 h-full {{ $administrado ? 'bg-estado-exitoBg' : 'bg-boton-acento' }} transition-colors duration-300"></div>
 <h4 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-titulo">
 <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">1</span>
 Estado de la Toma
 </h4>
 
 <div class="grid grid-cols-2 gap-3">
 <label class="cursor-pointer">
 <input type="radio" wire:model.live="administrado" value="1" class="peer sr-only">
 <div class="rounded-xl border-2 border-borde-suave bg-fondo-panel p-4 text-center transition-all peer-checked:border-estado-exitoBorde peer-checked:bg-estado-exitoBg peer-checked:text-estado-exito hover:bg-fondo-app">
 <i class="ph-bold ph-check-circle text-2xl mb-1"></i>
 <div class="text-xs font-bold uppercase tracking-wider">Administrado</div>
 </div>
 </label>

 <label class="cursor-pointer">
 <input type="radio" wire:model.live="administrado" value="0" class="peer sr-only">
 <div class="rounded-xl border-2 border-borde-suave bg-fondo-panel p-4 text-center transition-all peer-checked:border-borde-focus peer-checked:bg-estado-peligroBg peer-checked:text-boton-acento hover:bg-fondo-app">
 <i class="ph-bold ph-x-circle text-2xl mb-1"></i>
 <div class="text-xs font-bold uppercase tracking-wider">Omitido / Rechazado</div>
 </div>
 </label>
 </div>
 </div>

 <!-- 2. Fecha y Hora -->
 <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
 <h4 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-titulo">
 <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">2</span>
 Detalle del Registro
 </h4>
 
 <div class="grid gap-4 md:grid-cols-2">
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Fecha de Toma *</label>
 <input type="date" wire:model="fecha"
 class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('fecha') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
 </div>
 
 @if($administrado)
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Hora Real de Toma *</label>
 <input type="time" wire:model="hora_real"
 class="w-full rounded-xl border {{ $errors->has('hora_real') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('hora_real') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
 </div>
 @endif
 </div>
 </div>

 <!-- 3. Motivo de Omisión (Si no fue administrado) -->
 @if(!$administrado)
 <div class="rounded-[1.4rem] border border-borde-focus bg-estado-peligroBg p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
 <h4 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-boton-acento">
 <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-estado-peligroBg text-boton-acento">3</span>
 Motivo de Omisión / Rechazo *
 </h4>
 <textarea wire:model="motivo_omision" rows="2" placeholder="Ej. El paciente se negó a tomarlo, estaba dormido, etc."
 class="w-full rounded-xl border {{ $errors->has('motivo_omision') ? 'border-red-500' : 'border-borde-focus' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/20"></textarea>
 @error('motivo_omision') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
 </div>
 @endif

 <!-- 4. Observaciones Adicionales -->
 <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
 <h4 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-titulo">
 <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">{{ $administrado ? '3' : '4' }}</span>
 Observaciones Adicionales
 </h4>
 <div class="grid gap-4 md:grid-cols-2">
 @if($administrado)
 <div>
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Efecto Observado</label>
 <input type="text" wire:model="efecto_observado" placeholder="Ej. Tolerancia adecuada"
 class="w-full rounded-xl border border-borde-suave bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('efecto_observado') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
 </div>
 @endif
 <div class="{{ $administrado ? '' : 'md:col-span-2' }}">
 <label class="mb-1 block text-[11px] font-bold uppercase tracking-widest text-apoyo">Nota / Responsable</label>
 <input type="text" wire:model="observacion" placeholder="Detalle extra"
 class="w-full rounded-xl border border-borde-suave bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 @error('observacion') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
 </div>
 </div>
 </div>

 </div>
 </form>

 <x-slot name="footer">
 <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde-suave bg-fondo-panel px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-titulo transition hover:bg-fondo-panel active:scale-95">
 Cancelar
 </button>
 <button type="submit" form="formAdministracion" class="inline-flex items-center justify-center gap-2 rounded-xl {{ $administrado ? 'bg-estado-exitoBg shadow-[0_8px_16px_rgba(141,162,128,0.2)] hover:bg-fondo-panel' : 'bg-boton-acento shadow-[0_8px_16px_rgba(226,125,96,0.2)] hover:bg-fondo-panel' }} px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso transition hover:-translate-y-0.5 active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
 <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
 <span>{{ $administrado ? 'Confirmar Administración' : 'Registrar Omisión' }}</span>
 </button>
 </x-slot>
 </x-ui.modal-livewire>
</div>
