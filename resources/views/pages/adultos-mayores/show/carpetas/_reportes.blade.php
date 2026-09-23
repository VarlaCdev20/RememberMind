@php
 // ── Datos para gráficas ─────────────────────────────────────────

 // Signos vitales: últimas 10 vigentes, orden cronológico asc
 $svVigentes = ($signosVitales ?? collect())->where('estado', 'VIGENTE')->sortBy('fecha')->take(10)->values();
 $svLabels = $svVigentes->map(fn($s) => optional($s->fecha)->format('d/m') ?? '—')->toArray();
 $svSistolica = $svVigentes->pluck('presion_sistolica')->map(fn($v) => (int)($v ?? 0))->toArray();
 $svDiastolica = $svVigentes->pluck('presion_diastolica')->map(fn($v) => (int)($v ?? 0))->toArray();
 $svPulso = $svVigentes->pluck('frecuencia_cardiaca')->map(fn($v) => (int)($v ?? 0))->toArray();
 $svSaturacion = $svVigentes->pluck('saturacion')->map(fn($v) => (int)($v ?? 0))->toArray();

 // Medicación: distribución por estado
 $medGrupos = ($medicaciones ?? collect())->groupBy('estado');
 $medLabels = $medGrupos->keys()->values()->toArray();
 $medCounts = $medGrupos->map(fn($group) => $group->count())->values()->toArray();
 $medColors = collect($medLabels)->map(fn($l) => match(strtoupper((string)$l)) {
 'ACTIVO' => '#617453',
 'PAUSADO' => '#E2A45F',
 'EN REVISION' => '#5B5F97',
 'SUSPENDIDO' => '#E27D60',
 'FINALIZADO' => '#CBBBAA',
 default => '#9B8EA0',
 })->toArray();

 // Valoración funcional: índice Barthel histórico
 $valOrdenadas = ($valoracionesFuncionales ?? collect())
 ->filter(fn($v) => $v->indice_barthel !== null)
 ->sortBy('fecha_valoracion')
 ->values();
 $barthelLabels = $valOrdenadas->map(fn($v) => optional($v->fecha_valoracion)->format('d/m/Y') ?? '—')->toArray();
 $barthelValues = $valOrdenadas->pluck('indice_barthel')->map(fn($v) => (int)$v)->toArray();

 // Evaluaciones cognitivas: puntaje histórico
 $evalOrdenadas = ($evaluacionesActivas ?? collect())->sortBy('fecha_eval')->values();
 $evalLabels = $evalOrdenadas->map(fn($e) =>
 (optional($e->fecha_eval)->format('d/m') ?? '—') ."\n" . ($e->tipoEvaluacion->nombre ?? '')
 )->toArray();
 $evalPuntajes = $evalOrdenadas->pluck('puntaje_total')->map(fn($v) => (float)($v ?? 0))->toArray();
 $evalMaximos = $evalOrdenadas->pluck('puntaje_maximo')->map(fn($v) => (float)($v ?? 30))->toArray();

 // Stats rápidos
 $statSignos = ($signosVitales ?? collect())->where('estado', 'VIGENTE')->count();
 $statMedActivas = ($medicaciones ?? collect())->where('estado', 'ACTIVO')->count();
 $statValoracion = $valOrdenadas->count();
 $statEval = $evalOrdenadas->count();

 $ultimoBarth = $valOrdenadas->last();
 $ultimaEval = $evalOrdenadas->last();
 $ultimaSV = $svVigentes->last();
@endphp

<div class="space-y-6">

 {{-- ── Header ────────────────────────────────────────────── --}}
 <div class="rounded-[24px] border border-borde bg-gradient-to-br from-[#2F3E5C] to-[#4A5D8A] p-6 text-inverso">
 <div class="flex items-center justify-between">
 <div class="flex items-center gap-4">
 <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-fondo-card/10">
 <i class="ph-fill ph-chart-bar text-3xl"></i>
 </div>
 <div>
 <h2 class="text-xl font-extrabold">Centro de Analítica y Reportes</h2>
 <p class="text-xs font-semibold text-inverso/60">Indicadores clínicos, gráficas evolutivas y exportación del expediente</p>
 </div>
 </div>
 <span class="hidden sm:block text-xs font-bold uppercase tracking-wide bg-fondo-card/10 rounded-lg px-4 py-2">
 {{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}
 </span>
 </div>
 </div>

 {{-- ── Stats Row ───────────────────────────────────────────── --}}
 <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
 <div class="rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
 <div class="flex items-center justify-between mb-2">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Signos Vitales</span>
 <i class="ph-bold ph-heartbeat text-parrafo text-xl"></i>
 </div>
 <p class="text-3xl font-black text-titulo">{{ $statSignos }}</p>
 @if($ultimaSV)
 <p class="text-xs font-bold text-apoyo mt-1">
 Últ.: {{ optional($ultimaSV->fecha)->format('d/m/Y') ?? '—' }}
 @if($ultimaSV->presion_sistolica) · {{ $ultimaSV->presion_sistolica }}/{{ $ultimaSV->presion_diastolica }} mmHg @endif
 </p>
 @else
 <p class="text-xs font-bold text-apoyo mt-1">Sin registros vigentes</p>
 @endif
 </div>

 <div class="rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
 <div class="flex items-center justify-between mb-2">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Medicación Activa</span>
 <i class="ph-bold ph-pill text-parrafo text-xl"></i>
 </div>
 <p class="text-3xl font-black text-titulo">{{ $statMedActivas }}</p>
 <p class="text-xs font-bold text-apoyo mt-1">
 {{ ($medicaciones ?? collect())->count() }} medicamentos en total
 </p>
 </div>

 <div class="rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
 <div class="flex items-center justify-between mb-2">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Valorac. Funcional</span>
 <i class="ph-bold ph-person-arms-spread text-parrafo text-xl"></i>
 </div>
 <p class="text-3xl font-black text-titulo">{{ $ultimoBarth?->indice_barthel ?? '—' }}</p>
 <p class="text-xs font-bold text-apoyo mt-1">
 @if($ultimoBarth)
 Barthel · {{ $ultimoBarth->nivel_dependencia ?? '—' }}
 @else
 Sin valoraciones registradas
 @endif
 </p>
 </div>

 <div class="rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
 <div class="flex items-center justify-between mb-2">
 <span class="text-xs font-bold uppercase tracking-wide text-apoyo">Evaluaciones Cog.</span>
 <i class="ph-bold ph-brain text-parrafo text-xl"></i>
 </div>
 <p class="text-3xl font-black text-titulo">{{ $statEval }}</p>
 <p class="text-xs font-bold text-apoyo mt-1">
 @if($ultimaEval)
 Últ.: {{ $ultimaEval->puntaje_total ?? '—' }}/{{ $ultimaEval->puntaje_maximo ?? '—' }} pts
 @else
 Sin evaluaciones registradas
 @endif
 </p>
 </div>
 </div>

 {{-- ── Gráficas ─────────────────────────────────────────────── --}}
 <div class="grid md:grid-cols-2 gap-6" id="reportes-charts-section-{{ $idAdulto }}">

 {{-- Gráfica 1: Signos Vitales --}}
 <div class="rm-chart-card rm-chart-glass">
 <div class="flex items-center gap-2 mb-4">
 <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-activity text-sm"></i>
 </div>
 <div>
 <h3 class="text-sm font-bold text-titulo">Signos Vitales</h3>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Tendencia de presión arterial y pulso</p>
 </div>
 </div>
 @if($svVigentes->isNotEmpty())
 <div style="position:relative; height:200px;">
 <canvas id="chartSignos{{ $idAdulto }}"></canvas>
 </div>
 @else
 <div class="flex flex-col items-center justify-center h-40 text-apoyo">
 <i class="ph-bold ph-chart-line text-4xl mb-2"></i>
 <span class="text-xs font-bold uppercase tracking-widest">Sin registros de signos vitales</span>
 </div>
 @endif
 </div>

 {{-- Gráfica 2: Medicación por Estado --}}
 <div class="rm-chart-card rm-chart-glass">
 <div class="flex items-center gap-2 mb-4">
 <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-pill text-sm"></i>
 </div>
 <div>
 <h3 class="text-sm font-bold text-titulo">Medicación por Estado</h3>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Distribución del esquema terapéutico</p>
 </div>
 </div>
 @if(($medicaciones ?? collect())->isNotEmpty())
 <div style="position:relative; height:200px;" class="flex items-center justify-center">
 <canvas id="chartMed{{ $idAdulto }}"></canvas>
 </div>
 @else
 <div class="flex flex-col items-center justify-center h-40 text-apoyo">
 <i class="ph-bold ph-pill text-4xl mb-2"></i>
 <span class="text-xs font-bold uppercase tracking-widest">Sin medicación registrada</span>
 </div>
 @endif
 </div>

 {{-- Gráfica 3: Índice Barthel --}}
 <div class="rm-chart-card rm-chart-glass">
 <div class="flex items-center gap-2 mb-4">
 <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-person-arms-spread text-sm"></i>
 </div>
 <div>
 <h3 class="text-sm font-bold text-titulo">Índice Barthel</h3>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Evolución de la capacidad funcional (0–100)</p>
 </div>
 </div>
 @if($valOrdenadas->isNotEmpty())
 <div style="position:relative; height:200px;">
 <canvas id="chartBarthel{{ $idAdulto }}"></canvas>
 </div>
 @else
 <div class="flex flex-col items-center justify-center h-40 text-apoyo">
 <i class="ph-bold ph-chart-line text-4xl mb-2"></i>
 <span class="text-xs font-bold uppercase tracking-widest">Sin valoraciones funcionales</span>
 </div>
 @endif
 </div>

 {{-- Gráfica 4: Evaluaciones Cognitivas --}}
 <div class="rm-chart-card rm-chart-glass">
 <div class="flex items-center gap-2 mb-4">
 <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-brain text-sm"></i>
 </div>
 <div>
 <h3 class="text-sm font-bold text-titulo">Evaluaciones Cognitivas</h3>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Puntaje obtenido vs. puntaje máximo</p>
 </div>
 </div>
 @if($evalOrdenadas->isNotEmpty())
 <div style="position:relative; height:200px;">
 <canvas id="chartEval{{ $idAdulto }}"></canvas>
 </div>
 @else
 <div class="flex flex-col items-center justify-center h-40 text-apoyo">
 <i class="ph-bold ph-brain text-4xl mb-2"></i>
 <span class="text-xs font-bold uppercase tracking-widest">Sin evaluaciones cognitivas</span>
 </div>
 @endif
 </div>
 </div>

 {{-- ── Centro de Descargas ──────────────────────────────────── --}}
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <div class="mb-5 flex items-center gap-3 border-b border-borde pb-4">
 <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-download-simple text-xl"></i>
 </div>
 <div>
 <h3 class="text-base font-extrabold text-titulo">Centro de Descargas</h3>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Exporta el expediente en el formato que necesites</p>
 </div>
 </div>

 <div class="space-y-3">

 {{-- Expediente Integral 360° --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-fondo-panel text-titulo">
 <i class="ph-bold ph-files text-xl"></i>
 </div>
 <div>
 <h4 class="text-sm font-bold text-titulo">Expediente Integral 360°</h4>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Datos completos: personal, salud, cognitivo, participación</p>
 </div>
 </div>
 <div class="flex flex-wrap gap-2 shrink-0">
 <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'pdf']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-boton-principal px-3 py-2 text-xs font-bold text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-pdf"></i> PDF
 </a>
 <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'excel']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-bold text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-xls"></i> Excel
 </a>
 <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'word']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-bold text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-doc"></i> Word
 </a>
 </div>
 </div>
 </div>

 {{-- Reporte Médico --}}
 @if($fichasMedicas->isNotEmpty() || ($signosVitales ?? collect())->isNotEmpty())
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-heartbeat text-xl"></i>
 </div>
 <div>
 <h4 class="text-sm font-bold text-titulo">Reporte Médico de Salud</h4>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Ficha médica, signos vitales y medicación</p>
 </div>
 </div>
 <div class="flex flex-wrap gap-2 shrink-0">
 <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'medico', 'format' => 'pdf']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-bold text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-pdf"></i> PDF
 </a>
 <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'signos', 'format' => 'pdf']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-2 text-xs font-bold text-parrafo uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-chart-line"></i> Signos Vitales
 </a>
 <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'medicacion', 'format' => 'pdf']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-2 text-xs font-bold text-parrafo uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-pill"></i> Medicación
 </a>
 </div>
 </div>
 </div>
 @endif

 {{-- Reporte Funcional --}}
 @if($valOrdenadas->isNotEmpty())
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-person-arms-spread text-xl"></i>
 </div>
 <div>
 <h4 class="text-sm font-bold text-titulo">Reporte de Valoración Funcional</h4>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Índice Barthel, dependencia y riesgo de caída</p>
 </div>
 </div>
 <div class="flex flex-wrap gap-2 shrink-0">
 <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'funcional', 'format' => 'pdf']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-bold text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-pdf"></i> PDF
 </a>
 <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'excel']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-2 text-xs font-bold text-parrafo uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-xls"></i> Excel
 </a>
 </div>
 </div>
 </div>
 @endif

 {{-- Reporte Cognitivo --}}
 @if($evaluacionesLista->isNotEmpty())
 <div class="rounded-xl border border-borde-suave bg-fondo-panel p-4">
 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
 <i class="ph-bold ph-brain text-xl"></i>
 </div>
 <div>
 <h4 class="text-sm font-bold text-titulo">Reporte de Evaluaciones Cognitivas</h4>
 <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Historial MoCA / MMSE y evolución</p>
 </div>
 </div>
 <div class="flex flex-wrap gap-2 shrink-0">
 <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'cognitivo', 'format' => 'pdf']) }}"
 class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-bold text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
 <i class="ph-bold ph-file-pdf"></i> PDF
 </a>
 </div>
 </div>
 </div>
 @endif

 </div>
 </div>

</div>

{{-- ── Script de inicialización de gráficas ─────────────────────── --}}
<script>
{!! view()->file(resource_path('frontend/scripts/modules/pages-adultos-mayores-show-carpetas-_reportes.js.blade.php'), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render() !!}
</script>
