<div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="mb-5 flex items-center justify-between border-b border-borde pb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-identification-card text-xl"></i>
 </div>
 <div>
 <h2 class="text-lg font-extrabold text-titulo">Identificación Institucional</h2>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Datos administrativos y demográficos</p>
 </div>
 </div>
 </div>

 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Nombre Completo</span>
 <p class="text-sm font-bold text-titulo">{{ $nombreCompleto }}</p>
 </div>
 
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Cédula de Identidad</span>
 <p class="text-sm font-bold text-titulo">{{ $ci }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Fecha de Nacimiento</span>
 <p class="text-sm font-bold text-titulo">{{ $fechaNacimientoFormateada }} <span class="text-xs text-apoyo">({{ $edad ? $edad . ' años' : 'N/D' }})</span></p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Género</span>
 <p class="text-sm font-bold text-titulo">{{ $genero }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Estado Civil</span>
 <p class="text-sm font-bold text-titulo">{{ optional($adultoObj)->estado_civil ?? 'N/D' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Nivel Educativo</span>
 <p class="text-sm font-bold text-titulo">{{ optional($adultoObj)->nivel_educativo ?? optional($adultoObj)->nivel_educat ?? 'N/D' }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Fecha de Ingreso</span>
 <p class="text-sm font-bold text-titulo">{{ $fechaIngresoFormateada }}</p>
 </div>

 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Estado Institucional</span>
 <p class="text-sm font-bold {{ $estadoTexto === 'ACTIVO' ? 'text-parrafo' : 'text-parrafo' }}">{{ $estadoTexto }}</p>
 </div>
 
 @if(optional($adultoObj)->observaciones)
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 sm:col-span-2 lg:col-span-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Observación Institucional</span>
 <p class="text-sm font-medium text-titulo leading-relaxed">{{ optional($adultoObj)->observaciones }}</p>
 </div>
 @endif
 </div>
</div>
