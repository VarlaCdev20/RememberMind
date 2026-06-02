<section class="space-y-6">
 <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
 <div>
 <h2 class="text-2xl font-black tracking-tight text-titulo">Resultados Preventivos</h2>
 <p class="text-sm font-semibold text-apoyo">Análisis automatizado del estado integral del adulto mayor basado en reglas del sistema experto.</p>
 </div>
 </div>

 <div class="relative overflow-hidden rounded-[24px] border border-borde bg-fondo-card/95 p-8 shadow-sm">
 
 {{-- Capa de Desenfoque / Próximamente --}}
 <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-fondo-card/60 backdrop-blur-sm">
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-6 shadow-xl text-center max-w-md mx-auto">
 <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-fondo-app text-terracota">
 <i class="ph-bold ph-shield-warning text-3xl"></i>
 </div>
 <h3 class="text-xl font-extrabold text-titulo mb-2">Motor de Análisis en Desarrollo</h3>
 <p class="text-sm font-semibold text-apoyo mb-6">El módulo de resultados preventivos calculará dinámicamente factores de riesgo combinando múltiples variables del expediente. Disponible en próximas actualizaciones.</p>
 
 <div class="flex flex-wrap justify-center gap-3">
 <button type="button" @click="tab = 'historial'" class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel hover:text-boton-acento">
 <i class="ph-bold ph-clock-counter-clockwise"></i> Ver historial individual
 </button>
 </div>
 </div>
 </div>

 {{-- Estructura visual de fondo (Placeholder) --}}
 <div class="opacity-30 pointer-events-none select-none">
 <div class="grid gap-6 md:grid-cols-3 mb-8">
 <div class="rounded-xl border border-borde bg-fondo-panel p-5">
 <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Nivel de Riesgo Global</p>
 <p class="mt-2 text-3xl font-black text-amber-500">MODERADO</p>
 <p class="mt-1 text-xs font-semibold text-apoyo">Pendiente de cálculo real</p>
 </div>
 <div class="rounded-xl border border-borde bg-fondo-panel p-5">
 <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Alerta Preventiva Principal</p>
 <p class="mt-2 text-lg font-bold text-titulo">Riesgo de declive cognitivo</p>
 <p class="mt-1 text-xs font-semibold text-apoyo">Detectado en base a evaluaciones previas</p>
 </div>
 <div class="rounded-xl border border-borde bg-fondo-panel p-5">
 <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Acción Sugerida</p>
 <p class="mt-2 text-sm font-bold text-titulo">Programar nueva evaluación funcional</p>
 <button type="button" class="mt-3 rounded-lg bg-boton-acento/20 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-boton-acento">Seguimiento requerido</button>
 </div>
 </div>

 <h4 class="mb-4 text-sm font-bold uppercase tracking-widest text-titulo">Criterios de Evaluación</h4>
 <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 flex items-center gap-3">
 <i class="ph-fill ph-brain text-indigo-500 text-xl"></i>
 <span class="text-xs font-bold text-titulo">Evaluaciones Geriátricas</span>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 flex items-center gap-3">
 <i class="ph-fill ph-notebook text-amber-500 text-xl"></i>
 <span class="text-xs font-bold text-titulo">Observaciones Institucionales</span>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 flex items-center gap-3">
 <i class="ph-fill ph-activity text-rose-500 text-xl"></i>
 <span class="text-xs font-bold text-titulo">Valoración Funcional</span>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 flex items-center gap-3">
 <i class="ph-fill ph-users-three text-emerald-500 text-xl"></i>
 <span class="text-xs font-bold text-titulo">Red de Apoyo Familiar</span>
 </div>
 </div>
 </div>

 </div>
</section>