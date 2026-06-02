@php
 $timeline = collect();

 foreach($observacionesLista ?? [] as $obs) {
 $timeline->push((object)[
 'tipo' => 'observacion',
 'fecha' => \Carbon\Carbon::parse($obs->fecha),
 'titulo' => 'Observación Institucional: ' . $obs->tipo_obs,
 'desc' => $obs->descripcion,
 'icon' => 'ph-notebook',
 'color' => 'text-amber-600',
 'bg' => 'bg-amber-600/10'
 ]);
 }

 foreach($atencionesLista ?? [] as $aten) {
 $timeline->push((object)[
 'tipo' => 'atencion',
 'fecha' => \Carbon\Carbon::parse($aten->fecha),
 'titulo' => 'Atención Médica: ' . ($aten->tipoAtencion->tipo ?? 'General'),
 'desc' => $aten->obs ?? 'Sin observación',
 'icon' => 'ph-stethoscope',
 'color' => 'text-terracota',
 'bg' => 'bg-terracota/10'
 ]);
 }

 foreach($actividadesLista ?? [] as $act) {
 $timeline->push((object)[
 'tipo' => 'actividad',
 'fecha' => \Carbon\Carbon::parse($act->fecha),
 'titulo' => 'Actividad: ' . ($act->tipoActividad->tipo ?? 'General'),
 'desc' => $act->obs ?? 'Sin observación',
 'icon' => 'ph-calendar-check',
 'color' => 'text-emerald-600',
 'bg' => 'bg-emerald-600/10'
 ]);
 }

 foreach($evaluacionesLista ?? [] as $eval) {
 $timeline->push((object)[
 'tipo' => 'evaluacion',
 'fecha' => \Carbon\Carbon::parse($eval->fecha_eval),
 'titulo' => 'Evaluación Geriátrica: ' . ($eval->tipoEvaluacion->nombre ?? 'Prueba'),
 'desc' => 'Puntaje: ' . $eval->puntaje_total . ' - Riesgo: ' . $eval->nivel_riesgo,
 'icon' => 'ph-brain',
 'color' => 'text-indigo-600',
 'bg' => 'bg-indigo-600/10'
 ]);
 }

 foreach($documentosLista ?? [] as $doc) {
 $timeline->push((object)[
 'tipo' => 'documento',
 'fecha' => \Carbon\Carbon::parse($doc->fecha_doc),
 'titulo' => 'Documento: ' . $doc->nom_doc,
 'desc' => 'Categoría: ' . $doc->tipo_doc,
 'icon' => 'ph-folder-open',
 'color' => 'text-blue-600',
 'bg' => 'bg-blue-600/10'
 ]);
 }

 foreach($historialEstadosLista ?? [] as $est) {
 $timeline->push((object)[
 'tipo' => 'estado',
 'fecha' => \Carbon\Carbon::parse($est->fecha_cambio ?? $est->created_at),
 'titulo' => 'Cambio de Estado: ' . ($est->estadoNuevo->estado ?? 'Desconocido'),
 'desc' => $est->motivo_cambio ?? 'Sin motivo registrado',
 'icon' => 'ph-arrows-left-right',
 'color' => 'text-slate-600',
 'bg' => 'bg-slate-600/10'
 ]);
 }

 $timeline = $timeline->sortByDesc('fecha')->values();
@endphp

<section class="space-y-6" x-data="{ filtroHistorial: 'todo' }">
 <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
 <div>
 <h2 class="text-2xl font-black tracking-tight text-titulo">Historial Individual</h2>
 <p class="text-sm font-semibold text-apoyo">Línea de tiempo cronológica de todos los eventos del adulto mayor.</p>
 </div>
 <div class="flex gap-2">
 <button type="button" @click="abrir('observacion')" class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold text-inverso transition hover:bg-fondo-panel hover:text-boton-acento active:scale-[0.98]">
 <i class="ph-bold ph-plus-circle"></i> Nueva observación
 </button>
 <a href="{{ (Route::has('admin.adultos-mayores.reportes.especifico') ? route('admin.adultos-mayores.reportes.especifico', [$adulto->cod_am, 'historial']) : '#') }}" class="inline-flex items-center gap-2 rounded-xl bg-fondo-card border border-borde px-4 py-2.5 text-xs font-bold text-parrafo transition hover:bg-fondo-panel active:scale-[0.98]">
 <i class="ph-bold ph-printer"></i> Imprimir historial
 </a>
 </div>
 </div>

 <div class="flex flex-wrap gap-2">
 <button @click="filtroHistorial = 'todo'" :class="filtroHistorial === 'todo' ? 'bg-fondo-panel border-borde text-titulo' : 'bg-transparent border-transparent text-apoyo'" class="rounded-full border px-4 py-1.5 text-xs font-bold transition hover:bg-fondo-panel">Todo</button>
 <button @click="filtroHistorial = 'observacion'" :class="filtroHistorial === 'observacion' ? 'bg-amber-600/10 border-amber-600/30 text-amber-700' : 'bg-transparent border-transparent text-apoyo'" class="rounded-full border px-4 py-1.5 text-xs font-bold transition hover:bg-fondo-panel">Observaciones</button>
 <button @click="filtroHistorial = 'atencion'" :class="filtroHistorial === 'atencion' ? 'bg-terracota/10 border-terracota/30 text-terracota' : 'bg-transparent border-transparent text-apoyo'" class="rounded-full border px-4 py-1.5 text-xs font-bold transition hover:bg-fondo-panel">Atenciones</button>
 <button @click="filtroHistorial = 'actividad'" :class="filtroHistorial === 'actividad' ? 'bg-emerald-600/10 border-emerald-600/30 text-emerald-700' : 'bg-transparent border-transparent text-apoyo'" class="rounded-full border px-4 py-1.5 text-xs font-bold transition hover:bg-fondo-panel">Actividades</button>
 <button @click="filtroHistorial = 'evaluacion'" :class="filtroHistorial === 'evaluacion' ? 'bg-indigo-600/10 border-indigo-600/30 text-indigo-700' : 'bg-transparent border-transparent text-apoyo'" class="rounded-full border px-4 py-1.5 text-xs font-bold transition hover:bg-fondo-panel">Evaluaciones</button>
 <button @click="filtroHistorial = 'documento'" :class="filtroHistorial === 'documento' ? 'bg-blue-600/10 border-blue-600/30 text-blue-700' : 'bg-transparent border-transparent text-apoyo'" class="rounded-full border px-4 py-1.5 text-xs font-bold transition hover:bg-fondo-panel">Documentos</button>
 </div>

 <div class="mt-8 rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 @if($timeline->isEmpty())
 <div class="py-12 text-center">
 <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo">
 <i class="ph-bold ph-clock-counter-clockwise text-3xl"></i>
 </div>
 <h3 class="text-lg font-bold text-titulo">Sin eventos registrados</h3>
 <p class="mt-1 text-sm font-semibold text-apoyo">La línea de tiempo se construirá conforme se registren interacciones en el expediente.</p>
 </div>
 @else
 <div class="relative border-l-2 border-borde-suave ml-4 space-y-8 pb-4">
 @foreach($timeline as $evento)
 <div class="relative pl-8" x-show="filtroHistorial === 'todo' || filtroHistorial === '{{ $evento->tipo }}'" x-transition>
 <div class="absolute -left-[17px] top-1 flex h-8 w-8 items-center justify-center rounded-full {{ $evento->bg }} {{ $evento->color }} ring-4 ring-fondo-card">
 <i class="ph-bold {{ $evento->icon }} text-sm"></i>
 </div>
 <div class="mb-1 flex flex-wrap items-center gap-2">
 <p class="text-xs font-bold uppercase tracking-widest text-apoyo">{{ $evento->fecha->format('d M Y') }}</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4 shadow-sm">
 <h4 class="text-sm font-bold text-titulo">{{ $evento->titulo }}</h4>
 <p class="mt-1 text-xs font-semibold text-apoyo">{{ $evento->desc }}</p>
 </div>
 </div>
 @endforeach
 </div>
 @endif
 </div>
</section>