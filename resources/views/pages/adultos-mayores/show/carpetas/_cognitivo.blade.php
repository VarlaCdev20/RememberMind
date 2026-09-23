<div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="mb-5 flex items-center justify-between border-b border-borde pb-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-brain text-xl"></i>
 </div>
 <div>
 <h2 class="text-lg font-extrabold text-titulo">Cognitivo Resumido</h2>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Resumen de pruebas y evolución</p>
 </div>
 </div>
 </div>

 @if($evaluacionesLista->count() > 0)
 <div class="space-y-6">
 
 <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Pruebas Totales</span>
 <p class="text-xl font-extrabold text-parrafo">{{ $evaluacionesLista->count() }}</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Deterioro Detectado</span>
 <p class="text-xl font-extrabold {{ $evaluacionesLista->whereIn('nivel_riesgo', ['MEDIO', 'ALTO', 'MODERADO'])->count() > 0 ? 'text-amber-600' : 'text-parrafo' }}">
 {{ $evaluacionesLista->whereIn('nivel_riesgo', ['MEDIO', 'ALTO', 'MODERADO'])->count() }}
 </p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Evolución Promedio</span>
 <p class="text-xl font-extrabold text-titulo">{{ number_format($evaluacionesLista->avg('puntaje_total'), 1) }}</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-3">
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Tendencia</span>
 <p class="text-sm font-bold text-titulo mt-1.5">
 @if($evaluacionesLista->count() > 1)
 @php
 $latest = $evaluacionesLista->first()->puntaje_total;
 $previous = $evaluacionesLista->skip(1)->first()->puntaje_total;
 @endphp
 @if($latest > $previous)
 <span class="text-parrafo"><i class="ph-bold ph-trend-up"></i> Mejora</span>
 @elseif($latest < $previous)
 <span class="text-amber-600"><i class="ph-bold ph-trend-down"></i> Declive</span>
 @else
 <span class="text-apoyo"><i class="ph-bold ph-minus"></i> Estable</span>
 @endif
 @else
 --
 @endif
 </p>
 </div>
 </div>

 @php
 $ultima = $evaluacionesLista->first();
 $riesgo = strtoupper($ultima->nivel_riesgo ?? 'NORMAL');
 $colorRiesgo = match($riesgo) {
 'ALTO' => 'text-red-600',
 'MEDIO', 'MODERADO' => 'text-amber-600',
 default => 'text-parrafo',
 };
 @endphp

 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-3">Última Prueba Aplicada</span>
 <div class="grid sm:grid-cols-3 gap-4 rounded-xl border border-borde-suave bg-fondo-card p-5">
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Tipo de Prueba</span>
 <p class="text-base font-extrabold text-parrafo">{{ optional($ultima->tipoEvaluacion)->nombre ?? 'Evaluación' }}</p>
 <p class="text-xs font-bold text-apoyo">{{ \Carbon\Carbon::parse($ultima->fecha_eval)->format('d/m/Y') }}</p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Resultado Obtenido</span>
 <p class="text-xl font-extrabold text-titulo">{{ $ultima->puntaje_total }} <span class="text-xs font-bold text-apoyo">/ {{ optional($ultima->tipoEvaluacion)->puntaje_maximo ?? 30 }}</span></p>
 </div>
 <div>
 <span class="block text-xs font-bold uppercase tracking-wide text-apoyo mb-1">Interpretación Clínica</span>
 <p class="text-xs font-bold {{ $colorRiesgo }}">{{ $ultima->resultado_interpretacion ?? 'N/D' }}</p>
 <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-fondo-panel text-titulo">Riesgo: {{ $riesgo }}</span>
 </div>
 </div>
 </div>

 <div class="text-center mt-4">
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Para actualizar esta información, utilice el módulo correspondiente.</p>
 </div>
 </div>
 @else
 <div class="py-10 text-center">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo mb-4">
 <i class="ph-bold ph-brain text-3xl"></i>
 </div>
 <p class="text-sm font-bold text-apoyo">No existen evaluaciones cognitivas registradas.</p>
 <p class="mt-2 text-xs font-bold text-apoyo uppercase tracking-wide">Gestione desde el módulo correspondiente.</p>
 </div>
 @endif
</div>
