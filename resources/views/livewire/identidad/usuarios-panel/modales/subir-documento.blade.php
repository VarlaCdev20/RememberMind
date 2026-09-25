@if($mostrarModalSubirDoc && $tipoDocSeleccionado)
 <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
 <div class="relative w-full max-w-md rounded-[2rem] border border-borde-suave bg-fondo-app shadow-2xl p-6 space-y-5">
 <header class="flex items-center justify-between border-b border-borde-suave pb-3">
 <div>
 <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-boton-acento">Gestión Documental</span>
 <h3 class="text-base font-extrabold text-parrafo uppercase leading-tight">{{ $tipoDocNombre }}</h3>
 </div>
 <button type="button" wire:click="cerrarModalSubirDoc" class="flex h-8 w-8 items-center justify-center rounded-full bg-fondo-card/60 hover:bg-fondo-card text-parrafo transition active:scale-95">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </header>

 <form wire:submit.prevent="guardarDocumento" class="space-y-4">
 {{-- Archivo Temporal --}}
 <div class="space-y-1">
 <label class="block text-[9px] font-bold uppercase tracking-widest text-apoyo">Archivo (PDF, JPG, JPEG, PNG - Max 10MB) *</label>
 <div class="relative flex flex-col items-center justify-center border-2 border-dashed border-borde rounded-xl bg-fondo-card/40 p-4 transition hover:bg-fondo-card/60">
 <input type="file" wire:model="archivoTemporal" id="archivoDoc" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.jpg,.jpeg,.png">
 
 <div class="text-center space-y-1 pointer-events-none">
 <i class="ph-bold ph-cloud-arrow-up text-3xl text-parrafo/45"></i>
 <p class="text-xs font-bold text-parrafo/80">
 @if($archivoTemporal)
 Archivo seleccionado: <span class="text-boton-acento font-black">{{ $archivoTemporal->getClientOriginalName() }}</span>
 @else
 Seleccionar o arrastrar archivo
 @endif
 </p>
 <p class="text-[9px] text-meta">Formatos: PDF, JPG, PNG de hasta 10 MB</p>
 </div>
 </div>
 @error('archivoTemporal') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 {{-- Fechas --}}
 <div class="grid grid-cols-2 gap-3">
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Emisión</label>
 <input type="date" wire:model="fechaEmisionDoc" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('fechaEmisionDoc') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Fecha de Vencimiento</label>
 <input type="date" wire:model="fechaVencimientoDoc" class="w-full h-10 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
 @error('fechaVencimientoDoc') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Observaciones --}}
 <div>
 <label class="mb-1 block text-[9px] font-bold uppercase tracking-widest text-apoyo">Observaciones / Detalles</label>
 <textarea wire:model="observacionesDoc" placeholder="Opcional. Escriba algún comentario relevante..." rows="3"
 class="uppercase w-full rounded-xl border border-borde bg-fondo-card px-3 py-2 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus resize-none"></textarea>
 @error('observacionesDoc') <span class="mt-1 block text-[9px] font-bold text-boton-acento uppercase">{{ $message }}</span> @enderror
 </div>

 {{-- Botones --}}
 <div class="flex items-center gap-2 pt-2">
 <button type="button" wire:click="cerrarModalSubirDoc" class="flex-1 inline-flex h-10 items-center justify-center rounded-xl bg-fondo-card border border-borde-suave text-xs font-bold text-parrafo transition hover:bg-fondo-card/80 active:scale-95">
 Cancelar
 </button>
 <button type="submit" class="flex-1 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-boton-principal text-xs font-bold text-inverso shadow-lg transition hover:bg-estado-exitoBg active:scale-95">
 <i class="ph-bold ph-check"></i>
 Guardar Documento
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif
