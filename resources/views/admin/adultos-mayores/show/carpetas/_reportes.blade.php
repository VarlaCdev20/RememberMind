@php
    // ── Datos para gráficas ─────────────────────────────────────────

    // Signos vitales: últimas 10 vigentes, orden cronológico asc
    $svVigentes      = ($signosVitales ?? collect())->where('estado', 'VIGENTE')->sortBy('fecha')->take(10)->values();
    $svLabels        = $svVigentes->map(fn($s) => optional($s->fecha)->format('d/m') ?? '—')->toArray();
    $svSistolica     = $svVigentes->pluck('presion_sistolica')->map(fn($v) => (int)($v ?? 0))->toArray();
    $svDiastolica    = $svVigentes->pluck('presion_diastolica')->map(fn($v) => (int)($v ?? 0))->toArray();
    $svPulso         = $svVigentes->pluck('frecuencia_cardiaca')->map(fn($v) => (int)($v ?? 0))->toArray();
    $svSaturacion    = $svVigentes->pluck('saturacion')->map(fn($v) => (int)($v ?? 0))->toArray();

    // Medicación: distribución por estado
    $medGrupos  = ($medicaciones ?? collect())->groupBy('estado');
    $medLabels  = $medGrupos->keys()->values()->toArray();
    $medCounts  = $medGrupos->map(fn($group) => $group->count())->values()->toArray();
    $medColors  = collect($medLabels)->map(fn($l) => match(strtoupper((string)$l)) {
        'ACTIVO'      => '#617453',
        'PAUSADO'     => '#E2A45F',
        'EN REVISION' => '#5B5F97',
        'SUSPENDIDO'  => '#E27D60',
        'FINALIZADO'  => '#CBBBAA',
        default       => '#9B8EA0',
    })->toArray();

    // Valoración funcional: índice Barthel histórico
    $valOrdenadas   = ($valoracionesFuncionales ?? collect())
        ->filter(fn($v) => $v->indice_barthel !== null)
        ->sortBy('fecha_valoracion')
        ->values();
    $barthelLabels  = $valOrdenadas->map(fn($v) => optional($v->fecha_valoracion)->format('d/m/Y') ?? '—')->toArray();
    $barthelValues  = $valOrdenadas->pluck('indice_barthel')->map(fn($v) => (int)$v)->toArray();

    // Evaluaciones cognitivas: puntaje histórico
    $evalOrdenadas  = ($evaluacionesActivas ?? collect())->sortBy('fecha_eval')->values();
    $evalLabels     = $evalOrdenadas->map(fn($e) =>
        (optional($e->fecha_eval)->format('d/m') ?? '—') . "\n" . ($e->tipoEvaluacion->nombre ?? '')
    )->toArray();
    $evalPuntajes   = $evalOrdenadas->pluck('puntaje_total')->map(fn($v) => (float)($v ?? 0))->toArray();
    $evalMaximos    = $evalOrdenadas->pluck('puntaje_maximo')->map(fn($v) => (float)($v ?? 30))->toArray();

    // Stats rápidos
    $statSignos     = ($signosVitales ?? collect())->where('estado', 'VIGENTE')->count();
    $statMedActivas = ($medicaciones ?? collect())->where('estado', 'ACTIVO')->count();
    $statValoracion = $valOrdenadas->count();
    $statEval       = $evalOrdenadas->count();

    $ultimoBarth    = $valOrdenadas->last();
    $ultimaEval     = $evalOrdenadas->last();
    $ultimaSV       = $svVigentes->last();
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
                    <h2 class="text-xl font-black">Centro de Analítica y Reportes</h2>
                    <p class="text-xs font-semibold text-inverso/60">Indicadores clínicos, gráficas evolutivas y exportación del expediente</p>
                </div>
            </div>
            <span class="hidden sm:block text-xs font-black uppercase tracking-wide bg-fondo-card/10 rounded-lg px-4 py-2">
                {{ $adulto->cod_am }}
            </span>
        </div>
    </div>

    {{-- ── Stats Row ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase tracking-wide text-apoyo">Signos Vitales</span>
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
                <span class="text-xs font-black uppercase tracking-wide text-apoyo">Medicación Activa</span>
                <i class="ph-bold ph-pill text-parrafo text-xl"></i>
            </div>
            <p class="text-3xl font-black text-titulo">{{ $statMedActivas }}</p>
            <p class="text-xs font-bold text-apoyo mt-1">
                {{ ($medicaciones ?? collect())->count() }} medicamentos en total
            </p>
        </div>

        <div class="rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase tracking-wide text-apoyo">Valorac. Funcional</span>
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
                <span class="text-xs font-black uppercase tracking-wide text-apoyo">Evaluaciones Cog.</span>
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
        <div class="rounded-[20px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-activity text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Signos Vitales</h3>
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
                    <span class="text-xs font-black uppercase tracking-widest">Sin registros de signos vitales</span>
                </div>
            @endif
        </div>

        {{-- Gráfica 2: Medicación por Estado --}}
        <div class="rounded-[20px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-pill text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Medicación por Estado</h3>
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
                    <span class="text-xs font-black uppercase tracking-widest">Sin medicación registrada</span>
                </div>
            @endif
        </div>

        {{-- Gráfica 3: Índice Barthel --}}
        <div class="rounded-[20px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-person-arms-spread text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Índice Barthel</h3>
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
                    <span class="text-xs font-black uppercase tracking-widest">Sin valoraciones funcionales</span>
                </div>
            @endif
        </div>

        {{-- Gráfica 4: Evaluaciones Cognitivas --}}
        <div class="rounded-[20px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-fondo-panel text-parrafo">
                    <i class="ph-bold ph-brain text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-titulo">Evaluaciones Cognitivas</h3>
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
                    <span class="text-xs font-black uppercase tracking-widest">Sin evaluaciones cognitivas</span>
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
                <h3 class="text-base font-black text-titulo">Centro de Descargas</h3>
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
                            <h4 class="text-sm font-black text-titulo">Expediente Integral 360°</h4>
                            <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Datos completos: personal, salud, cognitivo, participación</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'pdf']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-boton-principal px-3 py-2 text-xs font-black text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
                            <i class="ph-bold ph-file-pdf"></i> PDF
                        </a>
                        <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'excel']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-black text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
                            <i class="ph-bold ph-file-xls"></i> Excel
                        </a>
                        <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'word']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-black text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
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
                            <h4 class="text-sm font-black text-titulo">Reporte Médico de Salud</h4>
                            <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Ficha médica, signos vitales y medicación</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'medico', 'format' => 'pdf']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-black text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
                            <i class="ph-bold ph-file-pdf"></i> PDF
                        </a>
                        <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'signos', 'format' => 'pdf']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-2 text-xs font-black text-parrafo uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
                            <i class="ph-bold ph-chart-line"></i> Signos Vitales
                        </a>
                        <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'medicacion', 'format' => 'pdf']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-2 text-xs font-black text-parrafo uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
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
                            <h4 class="text-sm font-black text-titulo">Reporte de Valoración Funcional</h4>
                            <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Índice Barthel, dependencia y riesgo de caída</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'funcional', 'format' => 'pdf']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-black text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
                            <i class="ph-bold ph-file-pdf"></i> PDF
                        </a>
                        <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'excel']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-card border border-borde px-3 py-2 text-xs font-black text-parrafo uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
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
                            <h4 class="text-sm font-black text-titulo">Reporte de Evaluaciones Cognitivas</h4>
                            <p class="text-xs font-bold text-apoyo uppercase tracking-wide">Historial MoCA / MMSE y evolución</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.adultos-mayores.reportes.especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'cognitivo', 'format' => 'pdf']) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-3 py-2 text-xs font-black text-inverso uppercase tracking-wide transition hover:bg-fondo-panel active:scale-95 shadow-sm">
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
(function () {
    'use strict';

    // Datos preparados en PHP
    const signosLabels    = @json($svLabels);
    const sistolica       = @json($svSistolica);
    const diastolica      = @json($svDiastolica);
    const pulso           = @json($svPulso);
    const saturacion      = @json($svSaturacion);

    const medLabels       = @json($medLabels);
    const medCounts       = @json($medCounts);
    const medColors       = @json($medColors);

    const barthelLabels   = @json($barthelLabels);
    const barthelValues   = @json($barthelValues);

    const evalLabels      = @json($evalLabels);
    const evalPuntajes    = @json($evalPuntajes);
    const evalMaximos     = @json($evalMaximos);

    const suffix          = '{{ $idAdulto }}';

    let initialized = false;

    function initCharts() {
        if (initialized || typeof window.Chart === 'undefined') return;
        initialized = true;

        const defaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { font: { family: 'inherit', size: 10, weight: 'bold' }, boxWidth: 12, padding: 12 } },
                datalabels: { display: false },
            },
        };

        // ── Gráfica 1: Signos Vitales (línea) ─────────────────────
        const canvasSignos = document.getElementById('chartSignos' + suffix);
        if (canvasSignos && signosLabels.length > 0) {
            new window.Chart(canvasSignos, {
                type: 'line',
                data: {
                    labels: signosLabels,
                    datasets: [
                        {
                            label: 'Sistólica',
                            data: sistolica,
                            borderColor: '#E27D60',
                            backgroundColor: 'rgba(226,125,96,0.08)',
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#E27D60',
                            fill: true,
                        },
                        {
                            label: 'Diastólica',
                            data: diastolica,
                            borderColor: '#5B5F97',
                            backgroundColor: 'rgba(91,95,151,0.06)',
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#5B5F97',
                        },
                        {
                            label: 'Pulso (lpm)',
                            data: pulso,
                            borderColor: '#C45F4B',
                            borderDash: [4, 3],
                            tension: 0.3,
                            pointRadius: 3,
                            pointBackgroundColor: '#C45F4B',
                        },
                    ],
                },
                options: {
                    ...defaults,
                    scales: {
                        x: { ticks: { font: { size: 9 } }, grid: { color: '#F0ECE8' } },
                        y: { min: 40, ticks: { font: { size: 9 } }, grid: { color: '#F0ECE8' } },
                    },
                },
            });
        }

        // ── Gráfica 2: Medicación (dona) ──────────────────────────
        const canvasMed = document.getElementById('chartMed' + suffix);
        if (canvasMed && medLabels.length > 0) {
            new window.Chart(canvasMed, {
                type: 'doughnut',
                data: {
                    labels: medLabels,
                    datasets: [{
                        data: medCounts,
                        backgroundColor: medColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 6,
                    }],
                },
                options: {
                    ...defaults,
                    cutout: '60%',
                    plugins: {
                        ...defaults.plugins,
                        datalabels: {
                            display: true,
                            color: '#fff',
                            font: { size: 10, weight: 'bold' },
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                },
            });
        }

        // ── Gráfica 3: Barthel (línea) ────────────────────────────
        const canvasBarthel = document.getElementById('chartBarthel' + suffix);
        if (canvasBarthel && barthelLabels.length > 0) {
            new window.Chart(canvasBarthel, {
                type: 'line',
                data: {
                    labels: barthelLabels,
                    datasets: [{
                        label: 'Índice Barthel',
                        data: barthelValues,
                        borderColor: '#5B5F97',
                        backgroundColor: 'rgba(91,95,151,0.1)',
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#5B5F97',
                        fill: true,
                    }],
                },
                options: {
                    ...defaults,
                    scales: {
                        x: { ticks: { font: { size: 8 }, maxRotation: 35 }, grid: { color: '#F0ECE8' } },
                        y: { min: 0, max: 100, ticks: { font: { size: 9 }, stepSize: 20 }, grid: { color: '#F0ECE8' } },
                    },
                    plugins: {
                        ...defaults.plugins,
                        annotation: undefined,
                        datalabels: {
                            display: true,
                            color: '#5B5F97',
                            font: { size: 9, weight: 'bold' },
                            anchor: 'end',
                            align: 'top',
                        },
                    },
                },
            });
        }

        // ── Gráfica 4: Evaluaciones Cognitivas (barras) ───────────
        const canvasEval = document.getElementById('chartEval' + suffix);
        if (canvasEval && evalLabels.length > 0) {
            new window.Chart(canvasEval, {
                type: 'bar',
                data: {
                    labels: evalLabels,
                    datasets: [
                        {
                            label: 'Puntaje Obtenido',
                            data: evalPuntajes,
                            backgroundColor: 'rgba(168,107,60,0.75)',
                            borderColor: '#A86B3C',
                            borderWidth: 1,
                            borderRadius: 5,
                        },
                        {
                            label: 'Puntaje Máximo',
                            data: evalMaximos,
                            backgroundColor: 'rgba(203,187,170,0.4)',
                            borderColor: '#CBBBAA',
                            borderWidth: 1,
                            borderRadius: 5,
                        },
                    ],
                },
                options: {
                    ...defaults,
                    scales: {
                        x: { ticks: { font: { size: 8 }, maxRotation: 30 }, grid: { display: false } },
                        y: { min: 0, ticks: { font: { size: 9 } }, grid: { color: '#F0ECE8' } },
                    },
                    plugins: {
                        ...defaults.plugins,
                        datalabels: {
                            display: true,
                            color: '#2F3E5C',
                            font: { size: 9, weight: 'bold' },
                            anchor: 'end',
                            align: 'top',
                            formatter: (v) => v > 0 ? v : '',
                        },
                    },
                },
            });
        }
    }

    // Inicializar cuando la sección sea visible (IntersectionObserver)
    function attachObserver() {
        const section = document.getElementById('reportes-charts-section-{{ $idAdulto }}');
        if (!section) {
            requestAnimationFrame(attachObserver);
            return;
        }

        // Si ya está visible al cargar
        if (section.offsetParent !== null) {
            setTimeout(initCharts, 50);
        }

        // Cuando Alpine.js lo muestre
        const io = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    setTimeout(initCharts, 50);
                    io.disconnect();
                }
            },
            { threshold: 0.05 }
        );
        io.observe(section);

        // Fallback: escuchar click en el botón "Reportes" del sidebar
        document.querySelectorAll('[\\@click*="carpetaActiva = \'reportes\'"]').forEach(btn => {
            btn.addEventListener('click', () => setTimeout(initCharts, 100));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachObserver);
    } else {
        attachObserver();
    }
})();
</script>
