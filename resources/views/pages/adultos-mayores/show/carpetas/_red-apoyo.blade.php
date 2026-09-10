<div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="mb-5 flex items-center justify-between border-b border-borde pb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-users-three text-xl"></i>
 </div>
 <div>
 <h2 class="text-lg font-extrabold text-titulo">Red de Apoyo y Contactos</h2>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Familiares, responsables y emergencias</p>
 </div>
 </div>
 </div>

 @if($familiaresLista->count() > 0 || optional($adultoObj)->contacto_emergencia_nombre)
 <div class="space-y-6">
 
 @if(optional($adultoObj)->contacto_emergencia_nombre)
 <div class="rounded-xl border border-red-200 bg-red-50/30 p-4">
 <span class="block text-xs font-bold uppercase tracking-wide text-red-600/70 mb-2">Contacto de Emergencia Directo</span>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
 <div>
 <span class="block text-xs font-bold text-red-800/60 uppercase">Nombre</span>
 <p class="text-sm font-bold text-red-900">{{ optional($adultoObj)->contacto_emergencia_nombre }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold text-red-800/60 uppercase">Parentesco</span>
 <p class="text-sm font-bold text-red-900">{{ optional($adultoObj)->contacto_emergencia_parentesco ?? 'N/D' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold text-red-800/60 uppercase">Celular</span>
 <p class="text-sm font-bold text-red-900 flex items-center gap-1"><i class="ph-bold ph-phone"></i> {{ optional($adultoObj)->contacto_emergencia_celular ?? 'N/D' }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold text-red-800/60 uppercase">Dirección</span>
 <p class="text-xs font-bold text-red-900">{{ optional($adultoObj)->contacto_emergencia_direccion ?? 'N/D' }}</p>
 </div>
 </div>
 </div>
 @endif

 @if($familiaresLista->count() > 0)
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Familiares Vinculados ({{ $familiaresLista->count() }})</span>
 <div class="grid grid-cols-1 gap-3">
 @foreach($familiaresLista as $familiar)
 @php
 $esResp = optional($familiar)->es_responsable ?? optional(optional($familiar)->pivot)->es_responsable ?? false;
 $nomFamiliar = trim(optional($familiar)->nombres . ' ' . optional($familiar)->ap_paterno);
 $parentesco = optional($familiar)->parentesco ?? optional(optional($familiar)->pivot)->parentesco_vinculo ?? 'N/D';
 @endphp
 <div class="flex items-center justify-between rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div class="flex items-center gap-4">
 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $esResp ? 'bg-fondo-panel text-parrafo border border-borde' : 'bg-fondo-card border border-borde-suave text-apoyo' }}">
 <i class="ph-bold {{ $esResp ? 'ph-star' : 'ph-user' }} text-lg"></i>
 </div>
 <div>
 <p class="text-sm font-bold text-titulo">{{ $nomFamiliar }}</p>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">{{ $parentesco }}</p>
 </div>
 </div>
 <div class="text-right">
 @if($esResp)
 <span class="inline-block mb-1 rounded bg-fondo-panel px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-parrafo">Responsable</span>
 @endif
 <p class="text-xs font-bold text-titulo"><i class="ph-bold ph-phone mr-1"></i>{{ optional($familiar)->celular ?? optional($familiar)->telefono ?? 'Sin número' }}</p>
 </div>
 </div>
 @endforeach
 </div>
 </div>
 @endif
 </div>
 @else
 <div class="py-10 text-center">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo mb-4">
 <i class="ph-bold ph-users-three text-3xl"></i>
 </div>
 <p class="text-sm font-bold text-apoyo">No hay familiares o responsables vinculados.</p>
 </div>
 @endif
</div>
