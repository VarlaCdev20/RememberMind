{{-- TAB EVALUACIONES COGNITIVAS (RESUMEN) --}}
<section
 
 
 
 class="space-y-6"
>
 <!-- HEADER BLOCK -->
 <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-borde pb-5">
 <div class="flex items-center gap-3">
 <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-fill ph-brain text-3xl"></i>
 </div>
 <div>
 <h2 class="text-2xl font-black tracking-tight text-titulo">Evaluaciones geriátricas</h2>
 <p class="text-sm font-semibold text-apoyo">Resumen de pruebas MoCA, MMSE y evolución cognitiva.</p>
 </div>
 </div>
 
 <div class="flex items-center gap-2">

 <a href="{{ (Route::has('admin.adultos-mayores.evaluaciones.index') ? route('admin.adultos-mayores.evaluaciones.index', $idAdulto) : '#') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-4 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-[0.98]">
 Ver Evaluaciones geriátricas <i class="ph-bold ph-arrow-right"></i>
 </a>
 </div>
 </div>

 <!-- METRICS GRID -->
 
    <!-- Áreas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-brain text-boton-acento"></i> Área Cognitiva</h3>
            <p class="text-sm text-apoyo">Evaluaciones actuales registradas en el sistema (MoCA, MMSE).</p>
        </div>
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-wheelchair text-boton-acento"></i> Área Funcional</h3>
            <p class="text-sm text-apoyo">La valoración funcional actual se registra en el módulo de salud.</p>
        </div>
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5 opacity-60">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-heart text-meta"></i> Área Afectiva</h3>
            <p class="text-sm text-apoyo">Futuro módulo para evaluación afectiva.</p>
        </div>
        <div class="rounded-xl border border-borde-suave bg-fondo-panel p-5 opacity-60">
            <h3 class="font-bold text-titulo flex items-center gap-2 mb-2"><i class="ph-bold ph-apple-logo text-meta"></i> Área Nutricional</h3>
            <p class="text-sm text-apoyo">Futuro módulo para evaluación nutricional.</p>
        </div>
    </div>
    
    <h3 class="text-lg font-bold text-titulo mt-6 mb-4">Métricas Cognitivas</h3>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Total Pruebas</p>
 <p class="mt-2 text-3xl font-black text-parrafo">{{ ($evaluacionesLista ?? collect())->count() }}</p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Deterioro Detectado</p>
 <p class="mt-2 text-3xl font-black {{ ($evaluacionesLista ?? collect())->whereIn('nivel_riesgo', ['MEDIO', 'ALTO'])->count() > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
 {{ ($evaluacionesLista ?? collect())->whereIn('nivel_riesgo', ['MEDIO', 'ALTO'])->count() }}
 </p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Evolución Cognitiva</p>
 <p class="mt-2 text-3xl font-black text-titulo">
 @if(($evaluacionesLista ?? collect())->count() > 0)
 {{ number_format(($evaluacionesLista ?? collect())->avg('puntaje_total'), 1) }} <span class="text-xs text-apoyo">pts</span>
 @else
 --
 @endif
 </p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Tendencia General</p>
 <p class="mt-2 text-sm font-bold text-titulo">
 @if(($evaluacionesLista ?? collect())->count() > 1)
 @php
 $latest = ($evaluacionesLista ?? collect())->first()->puntaje_total;
 $previous = ($evaluacionesLista ?? collect())->skip(1)->first()->puntaje_total;
 @endphp
 @if($latest > $previous)
 <span class="text-emerald-600"><i class="ph-bold ph-trend-up"></i> Mejora</span>
 @elseif($latest < $previous)
 <span class="text-amber-600"><i class="ph-bold ph-trend-down"></i> Declive</span>
 @else
 <span class="text-apoyo"><i class="ph-bold ph-minus"></i> Estable</span>
 @endif
 @else
 Requiere más pruebas
 @endif
 </p>
 </div>
 </div>

 <!-- ÚLTIMA PRUEBA CARD -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="flex items-center justify-between border-b border-borde pb-4 mb-4">
 <h3 class="text-lg font-extrabold text-titulo">Última Prueba Aplicada</h3>
 <a href="{{ (Route::has('admin.adultos-mayores.reporte-individual') ? route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'tipo' => 'cognitivo', 'format' => 'pdf']) : '#') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde-fuerte px-3 py-1.5 text-xs font-bold text-parrafo hover:bg-fondo-panel transition">
 <i class="ph-bold ph-printer"></i> Reporte Cognitivo
 </a>
 </div>
 
 @if(($evaluacionesLista ?? collect())->count() > 0)
 @php
 $ultima = ($evaluacionesLista ?? collect())->first();
 $riesgo = strtoupper($ultima->nivel_riesgo ?? 'NORMAL');
 $colorRiesgo = match($riesgo) {
 'ALTO' => 'text-red-600',
 'MEDIO', 'MODERADO' => 'text-amber-600',
 default => 'text-emerald-600',
 };
 @endphp
 <div class="grid md:grid-cols-3 gap-6">
 <div>
 <span class="block text-[10px] font-bold uppercase tracking-widest text-apoyo">Tipo de Prueba</span>
 <p class="text-xl font-extrabold text-parrafo mt-1">{{ $ultima->tipoEvaluacion->nombre }}</p>
 <p class="text-xs font-bold text-apoyo mt-1">Fecha: {{ $ultima->fecha_eval->format('d/m/Y') }}</p>
 </div>
 <div>
 <span class="block text-[10px] font-bold uppercase tracking-widest text-apoyo">Resultado Obtenido</span>
 <p class="text-xl font-extrabold text-titulo mt-1">{{ $ultima->puntaje_total }} <span class="text-sm font-bold text-apoyo">/ {{ $ultima->tipoEvaluacion->puntaje_maximo ?? 30 }} pts</span></p>
 </div>
 <div>
 <span class="block text-[10px] font-bold uppercase tracking-widest text-apoyo">Interpretación Clínica</span>
 <p class="text-sm font-bold {{ $colorRiesgo }} mt-1">{{ $ultima->resultado_interpretacion }}</p>
 <span class="inline-block mt-2 px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-fondo-panel text-titulo">Riesgo: {{ $riesgo }}</span>
 </div>
 </div>
 
 <div class="mt-6 text-center">
 <a href="{{ (Route::has('admin.adultos-mayores.evaluaciones.index') ? route('admin.adultos-mayores.evaluaciones.index', $idAdulto) : '#') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo shadow-sm transition hover:bg-fondo-panel active:scale-[0.98]">
 Ver Historial Completo
 </a>
 </div>
 @else
 <div class="py-8 text-center">
 <i class="ph-fill ph-brain text-4xl text-meta mb-3 block"></i>
 <p class="text-sm font-bold text-apoyo">No se encontraron pruebas cognitivas registradas.</p>

 </div>
 @endif
 </div>
</section>