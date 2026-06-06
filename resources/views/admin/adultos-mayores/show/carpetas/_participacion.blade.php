<div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="mb-5 flex items-center justify-between border-b border-borde pb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-handshake text-xl"></i>
 </div>
 <div>
 <h2 class="text-lg font-extrabold text-titulo">Participación Institucional</h2>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Actividades, terapias y eventos</p>
 </div>
 </div>
 </div>

 @if($actividadesLista->count() > 0)
 <div class="space-y-6">
 
 <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Actividades Asignadas</span>
 <p class="text-xl font-extrabold text-parrafo">{{ $actividadesLista->count() }}</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Actividades Asistidas</span>
 <p class="text-xl font-extrabold text-parrafo">
 {{ $actividadesLista->whereIn('estado', ['COMPLETADA', 'REALIZADA'])->count() }}
 </p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3 sm:col-span-1 col-span-2">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Última Participación</span>
 <p class="text-xs font-bold text-titulo mt-2">
 @php $ultimaAct = $actividadesLista->first(); @endphp
 {{ optional($ultimaAct->tipoActividad)->tipo ?? 'Actividad' }}
 <span class="block mt-0.5 text-xs text-apoyo font-normal">{{ \Carbon\Carbon::parse($ultimaAct->fecha)->format('d/m/Y') }}</span>
 </p>
 </div>
 </div>

 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Participaciones Recientes</span>
 <div class="space-y-3">
 @foreach($actividadesLista->take(5) as $act)
 <div class="flex items-center gap-4 rounded-xl border border-borde-suave bg-fondo-card p-3">
 <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo shrink-0">
 <i class="ph-bold ph-calendar-check"></i>
 </div>
 <div class="flex-1 min-w-0">
 <p class="text-sm font-bold text-titulo truncate">{{ optional($act->tipoActividad)->tipo ?? 'Actividad' }}</p>
 <p class="text-xs font-bold text-apoyo mt-0.5">{{ \Carbon\Carbon::parse($act->fecha)->format('d/m/Y') }} {{ $act->hora ? ' • '.substr($act->hora, 0, 5) : '' }}</p>
 </div>
 <span class="shrink-0 inline-block px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide {{ in_array($act->estado, ['COMPLETADA', 'REALIZADA']) ? 'bg-fondo-panel text-parrafo' : 'bg-amber-100 text-amber-700' }}">
 {{ $act->estado ?? 'PROGRAMADA' }}
 </span>
 </div>
 @endforeach
 </div>
 </div>

 <div class="text-center mt-4">
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Para actualizar esta información, utilice el módulo correspondiente.</p>
 </div>
 </div>
 @else
 <div class="py-10 text-center">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo mb-4">
 <i class="ph-bold ph-handshake text-3xl"></i>
 </div>
 <p class="text-sm font-bold text-apoyo">No hay participación registrada.</p>
 <p class="mt-2 text-xs font-bold text-apoyo uppercase tracking-wide">Gestione desde el módulo correspondiente.</p>
 </div>
 @endif
</div>
