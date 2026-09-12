@extends('layouts.reportes.reporte-base')

@section('titulo', $metadata['titulo'] ?? 'Reporte de Adultos Mayores')

{{-- Chart.js (solo HTML, via Vite bundle) --}}
@unless($esPdf)
@push('head-scripts')
@vite(['resources/frontend/scripts/app.js'])
@endpush
@endunless

@section('contenido')

{{-- ══════════════════════════════════════════════════════════
 BARRA DE ACCIONES (solo HTML)
 ══════════════════════════════════════════════════════════ --}}
@unless($esPdf)
<div class="rm-acciones">
 <button onclick="window.print()" class="rm-btn rm-btn-print">Imprimir</button>
 @can('reportes.exportar_pdf')
 <a href="{{ route('admin.reportes.adultos.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
 <a href="{{ route('admin.reportes.adultos.excel') }}" class="rm-btn rm-btn-excel">Exportar Excel</a>
 @endcan
 <a href="{{ route('dashboard') }}" class="rm-btn" style="background:#C7B5A3;color:#2F3E5C;margin-left:6px;">&#8592; Volver</a>
</div>
@endunless

{{-- ══════════════════════════════════════════════════════════
 ENCABEZADO INSTITUCIONAL
 ══════════════════════════════════════════════════════════ --}}
<div class="rm-header">
 <div class="rm-header-logo">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>
 <div class="rm-header-sub">RememberMind &nbsp;&#183;&nbsp; Reporte por Sección</div>
 <span class="rm-header-badge">Adultos Mayores</span>
 <div class="rm-header-meta">
 Generado el {{ $metadata['generado_en'] }}
 &nbsp;&nbsp;|&nbsp;&nbsp;
 Usuario: <strong>{{ $metadata['generado_por'] }}</strong>
 @unless($esPdf)
 &nbsp;&nbsp;|&nbsp;&nbsp;
 Vista: previa
 @endunless
 @if($esPdf)
 &nbsp;&nbsp;|&nbsp;&nbsp;
 Incluye máximo {{ $metadata['limite_pdf'] }} registros
 @endif
 </div>
</div>

{{-- ══════════════════════════════════════════════════════════
 1. KPIs — RESUMEN GENERAL (3 filas × 3 columnas)
 ══════════════════════════════════════════════════════════ --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo">1. Resumen General</div>
 <table class="rm-kpi-wrap">
 {{-- Fila 1: Totales --}}
 <tr class="rm-kpi-row">
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor">{{ $datos['resumen']['total'] }}</div>
 <div class="rm-kpi-titulo">Total Registrados</div>
 <div class="rm-kpi-sub">En el sistema</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['activos'] }}</div>
 <div class="rm-kpi-titulo">Activos</div>
 <div class="rm-kpi-sub">Sin archivar</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['archivados'] }}</div>
 <div class="rm-kpi-titulo">Archivados</div>
 <div class="rm-kpi-sub">Fuera de seguimiento</div>
 </td>
 </tr>
 {{-- Fila 2: Familiar y ficha --}}
 <tr class="rm-kpi-row">
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['edad']['promedio'] ?? '—' }}</div>
 <div class="rm-kpi-titulo">Edad Promedio</div>
 <div class="rm-kpi-sub">años (con fecha de nac.)</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['con_familiar'] }}</div>
 <div class="rm-kpi-titulo">Con Familiar</div>
 <div class="rm-kpi-sub">Vínculo registrado</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['sin_familiar'] }}</div>
 <div class="rm-kpi-titulo">Sin Familiar</div>
 <div class="rm-kpi-sub">Sin vínculo activo</div>
 </td>
 </tr>
 {{-- Fila 3: Ingresos y ficha médica --}}
 <tr class="rm-kpi-row">
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['resumen']['nuevos_mes'] }}</div>
 <div class="rm-kpi-titulo">Nuevos Este Mes</div>
 <div class="rm-kpi-sub">{{ now()->translatedFormat('F Y') }}</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['con_ficha'] }}</div>
 <div class="rm-kpi-titulo">Con Ficha Médica</div>
 <div class="rm-kpi-sub">Ficha activa</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['sin_ficha'] }}</div>
 <div class="rm-kpi-titulo">Sin Ficha Médica</div>
 <div class="rm-kpi-sub">Sin ficha registrada</div>
 </td>
 </tr>
 </table>
</div>

{{-- ══════════════════════════════════════════════════════════
 2. GRÁFICAS — diferente según HTML vs PDF
 ══════════════════════════════════════════════════════════ --}}

{{-- ── PDF: barras CSS proporcionales ── --}}
@if($esPdf)
<div class="rm-seccion">
 <div class="rm-seccion-titulo">2. Distribución por Variables</div>

 <table style="width:100%; border-collapse:collapse;">
 <tr>
 {{-- Columna: Estado --}}
 <td style="width:50%; vertical-align:top; padding-right:10px;">
 <div class="rm-seccion-titulo-verde" style="font-size:8.5pt; padding:4px 10px; margin-bottom:6px;">
 Por Estado
 </div>
 @include('pages.reportes.partials.grafica-barras-pdf', [
 'titulo' => '',
 'labels' => $graficas['estado']['labels'],
 'data' => $graficas['estado']['data'],
 'colores' => $graficas['estado']['colores'],
 ])
 </td>
 {{-- Columna: Género --}}
 <td style="width:50%; vertical-align:top; padding-left:10px;">
 <div class="rm-seccion-titulo-naranja" style="font-size:8.5pt; padding:4px 10px; margin-bottom:6px;">
 Por Género
 </div>
 @include('pages.reportes.partials.grafica-barras-pdf', [
 'titulo' => '',
 'labels' => $graficas['genero']['labels'],
 'data' => $graficas['genero']['data'],
 'colores' => $graficas['genero']['colores'],
 ])
 </td>
 </tr>
 </table>

 <div style="margin-top:12px;">
 <div class="rm-seccion-titulo-morado" style="font-size:8.5pt; padding:4px 10px; margin-bottom:6px;">
 Por Rango de Edad
 </div>
 @include('pages.reportes.partials.grafica-barras-pdf', [
 'titulo' => '',
 'labels' => $graficas['edad']['labels'],
 'data' => $graficas['edad']['data'],
 'colores' => $graficas['edad']['colores'],
 ])
 </div>
</div>
@endif

{{-- ── HTML: Chart.js ── --}}
@unless($esPdf)
<div class="rm-seccion" style="margin-bottom:28px;">
 <div class="rm-seccion-titulo">2. Distribución por Variables</div>

    {{-- Doughnut estado + Doughnut género --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="rm-chart-card rm-chart-glass p-5 rounded-2xl">
            <div class="rm-seccion-titulo-verde font-bold text-xs uppercase tracking-wider mb-3">Por Estado</div>
            <div style="position:relative; height:240px;">
                <canvas id="rm-chart-estado"></canvas>
            </div>
        </div>
        <div class="rm-chart-card rm-chart-glass p-5 rounded-2xl">
            <div class="rm-seccion-titulo-naranja font-bold text-xs uppercase tracking-wider mb-3">Por Género</div>
            <div style="position:relative; height:240px;">
                <canvas id="rm-chart-genero"></canvas>
            </div>
        </div>
    </div>

    {{-- Barras horizontales por rango de edad --}}
    <div class="rm-chart-card rm-chart-glass p-5 rounded-2xl">
        <div class="rm-seccion-titulo-morado font-bold text-xs uppercase tracking-wider mb-3">Por Rango de Edad</div>
        <div style="position:relative; height:210px;">
            <canvas id="rm-chart-edad"></canvas>
        </div>
    </div>
</div>
@endunless

{{-- ══════════════════════════════════════════════════════════
 3. TABLA PRINCIPAL
 ══════════════════════════════════════════════════════════ --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo">3. Listado de Adultos Mayores
 @unless($esPdf)
 <span style="font-weight:normal; font-size:8.5pt; margin-left:8px;">
 ({{ $datos['lista']->count() }} registros mostrados)
 </span>
 @endunless
 @if($esPdf)
 <span style="font-weight:normal; font-size:8pt; margin-left:8px;">
 (máximo {{ $metadata['limite_pdf'] }} registros)
 </span>
 @endif
 </div>

 @if($datos['lista']->isEmpty())
 <div class="rm-alerta info">
 <div class="rm-alerta-nivel">Sin datos</div>
 No se encontraron adultos mayores registrados.
 </div>
 @else

 {{-- ── TABLA HTML (columnas completas) ── --}}
 @unless($esPdf)
 <div style="overflow-x:auto;">
 <table class="rm-table">
 <thead>
 <tr>
 <th>Nombre Completo</th>
 <th>Edad</th>
 <th>Género</th>
 <th>Estado</th>
 <th>Estado Civil</th>
 <th>Tipo Ingreso</th>
 <th>Familiar</th>
 <th>Ficha Médica</th>
 <th>Fecha Ingreso</th>
 </tr>
 </thead>
 <tbody>
 @foreach($datos['lista'] as $am)
 <tr>
 <td>{{ $am->nombre_completo }}</td>
 <td style="text-align:center;">
 {{ $am->edad !== null ? $am->edad . ' a.' : '—' }}
 </td>
 <td style="text-align:center;">
 @if(strtolower($am->genero ?? '') === 'm')
 <span style="color:#2F3E5C; font-weight:bold;" title="Masculino">M</span>
 @elseif(strtolower($am->genero ?? '') === 'f')
 <span style="color:#E97A5F; font-weight:bold;" title="Femenino">F</span>
 @else
 {{ $am->genero ?? '—' }}
 @endif
 </td>
 <td>
 <span style="
 display:inline-block; padding:2px 7px; border-radius:3px; font-size:8pt; font-weight:bold;
 background:{{ strtolower($am->nombre_estado ?? '') === 'activo' ? '#ECFDF5' : '#FEF3EE' }};
 color:{{ strtolower($am->nombre_estado ?? '') === 'activo' ? '#065f46' : '#991b1b' }};">
 {{ $am->nombre_estado ?? '—' }}
 </span>
 </td>
 <td>{{ $am->estado_civil ?? '—' }}</td>
 <td>{{ $am->tipo_ing ?? '—' }}</td>
 <td style="text-align:center;">
 @if($am->tiene_familiar)
 <span style="color:#065f46; font-weight:bold;">&#10003;</span>
 @else
 <span style="color:#991b1b;">&#8212;</span>
 @endif
 </td>
 <td style="text-align:center;">
 @if($am->tiene_ficha)
 <span style="color:#065f46; font-weight:bold;">&#10003;</span>
 @else
 <span style="color:#991b1b;">&#8212;</span>
 @endif
 </td>
 <td style="white-space:nowrap; font-size:8.5pt;">
 {{ $am->fecha_ing ? \Carbon\Carbon::parse($am->fecha_ing)->format('d/m/Y') : '—' }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endunless

 {{-- ── TABLA PDF (columnas compactas) ── --}}
 @if($esPdf)
 <table class="rm-table" style="font-size:8pt;">
 <thead>
 <tr>
 <th style="width:44%;">Nombre</th>
 <th style="width:7%; text-align:center;">Edad</th>
 <th style="width:7%; text-align:center;">Gén.</th>
 <th style="width:16%;">Estado</th>
 <th style="width:13%;">Familiar</th>
 <th style="width:13%;">Ficha Méd.</th>
 </tr>
 </thead>
 <tbody>
 @foreach($datos['lista'] as $am)
 <tr>
 <td style="font-size:8pt;">{{ Str::limit($am->nombre_completo, 28) }}</td>
 <td style="text-align:center;">{{ $am->edad !== null ? $am->edad : '—' }}</td>
 <td style="text-align:center;">{{ strtoupper(substr($am->genero ?? '—', 0, 1)) }}</td>
 <td style="font-size:8pt;">{{ $am->nombre_estado ?? '—' }}</td>
 <td style="text-align:center;">{{ $am->tiene_familiar ? 'Sí' : 'No' }}</td>
 <td style="text-align:center;">{{ $am->tiene_ficha ? 'Sí' : 'No' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @endif

 @endif {{-- end if lista vacía --}}
</div>

{{-- ══════════════════════════════════════════════════════════
 NOTA INSTITUCIONAL
 ══════════════════════════════════════════════════════════ --}}
<div class="rm-nota-legal">
 Este reporte es de uso exclusivo interno de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS. Los datos son confidenciales y
 están sujetos a las políticas institucionales de protección de datos. Generado por RememberMind.
</div>

{{-- ══════════════════════════════════════════════════════════
 FOOTER
 ══════════════════════════════════════════════════════════ --}}
<div class="rm-footer">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS &nbsp;&#183;&nbsp; RememberMind &nbsp;&#183;&nbsp; {{ $metadata['generado_en'] }}
 &nbsp;&#183;&nbsp; Reporte de Adultos Mayores
</div>

@endsection

{{-- ══════════════════════════════════════════════════════════
 SCRIPTS CHART.JS (solo HTML)
 ══════════════════════════════════════════════════════════ --}}
@unless($esPdf)
@push('scripts')
<script>
(function () {
    var RM = {
        estado: @json($graficas['estado']),
        genero: @json($graficas['genero']),
        edad:   @json($graficas['edad']),
    };

    function hexToRgba(hex, alpha) {
        if (window.RMCharts && window.RMCharts.helpers && window.RMCharts.helpers.hexToRgba) {
            return window.RMCharts.helpers.hexToRgba(hex, alpha);
        }
        if (!hex || typeof hex !== 'string') return hex;
        let c = hex.replace('#', '');
        if (c.length === 3) c = c.split('').map(x => x + x).join('');
        if (c.length === 6) {
            const num = parseInt(c, 16);
            return `rgba(${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}, ${alpha})`;
        }
        return hex;
    }

    function inicializarGraficas() {
        if (typeof window.Chart === 'undefined') {
            setTimeout(inicializarGraficas, 60);
            return;
        }

        var Chart = window.Chart;
        window.__rmReportesAdultosCharts = window.__rmReportesAdultosCharts || new Map();
        function setChart(key, chart) {
            if (window.__rmReportesAdultosCharts.has(key)) {
                try { window.__rmReportesAdultosCharts.get(key).destroy(); } catch (e) {}
            }
            window.__rmReportesAdultosCharts.set(key, chart);
            return chart;
        }

        // ── 1. Doughnut: Por Estado ──
        var ctxEstado = document.getElementById('rm-chart-estado');
        if (ctxEstado && RM.estado.labels.length > 0) {
            var bgColoresEstado = (RM.estado.colores || []).map(function(c) { return hexToRgba(c, 0.78); });
            var borderColoresEstado = (RM.estado.colores || []).map(function(c) { return hexToRgba(c, 0.95); });

            setChart('estado', new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: RM.estado.labels,
                    datasets: [{
                        data: RM.estado.data,
                        backgroundColor: bgColoresEstado,
                        borderWidth: 2.5,
                        borderColor: '#ffffff',
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '58%',
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                        animateRotate: true,
                        animateScale: true,
                    },
                    plugins: {
                        datalabels: { display: false },
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 11, weight: 'bold' },
                                color: '#2F3E5C',
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(47, 62, 92, 0.92)',
                            titleColor: '#F3ECE4',
                            bodyColor: '#F3ECE4',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(ctx) {
                                    var total = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
                                    var pct = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                                    return ' ' + ctx.raw + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            }));
        }

        // ── 2. Doughnut: Por Género ──
        var ctxGenero = document.getElementById('rm-chart-genero');
        if (ctxGenero && RM.genero.labels.length > 0) {
            var bgColoresGenero = (RM.genero.colores || []).map(function(c) { return hexToRgba(c, 0.78); });

            setChart('genero', new Chart(ctxGenero, {
                type: 'doughnut',
                data: {
                    labels: RM.genero.labels,
                    datasets: [{
                        data: RM.genero.data,
                        backgroundColor: bgColoresGenero,
                        borderWidth: 2.5,
                        borderColor: '#ffffff',
                        hoverOffset: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '58%',
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                        animateRotate: true,
                        animateScale: true,
                    },
                    plugins: {
                        datalabels: { display: false },
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 11, weight: 'bold' },
                                color: '#2F3E5C',
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(47, 62, 92, 0.92)',
                            titleColor: '#F3ECE4',
                            bodyColor: '#F3ECE4',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(ctx) {
                                    var total = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
                                    var pct = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                                    return ' ' + ctx.raw + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            }));
        }

        // ── 3. Barras Horizontales: Por Rango de Edad ──
        var ctxEdad = document.getElementById('rm-chart-edad');
        if (ctxEdad && RM.edad.labels.length > 0) {
            var bgColoresEdad = (RM.edad.colores || []).map(function(c) { return hexToRgba(c, 0.80); });
            var borderColoresEdad = (RM.edad.colores || []).map(function(c) { return hexToRgba(c, 0.98); });

            setChart('edad', new Chart(ctxEdad, {
                type: 'bar',
                data: {
                    labels: RM.edad.labels,
                    datasets: [{
                        label: 'Adultos Mayores',
                        data: RM.edad.data,
                        backgroundColor: bgColoresEdad,
                        borderColor: borderColoresEdad,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.86,
                        categoryPercentage: 0.92,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 950,
                        easing: 'easeOutQuart',
                    },
                    plugins: {
                        datalabels: { display: false },
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(47, 62, 92, 0.92)',
                            titleColor: '#F3ECE4',
                            bodyColor: '#F3ECE4',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(ctx) {
                                    return ' ' + ctx.raw + ' adultos';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: '#2F3E5C',
                                font: { size: 11, weight: 'bold' },
                            },
                            grid: { color: 'rgba(47, 62, 92, 0.08)' }
                        },
                        y: {
                            ticks: {
                                color: '#2F3E5C',
                                font: { size: 11, weight: 'bold' },
                            },
                            grid: { display: false }
                        }
                    }
                }
            }));
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarGraficas);
    } else {
        inicializarGraficas();
    }
})();
</script>
@endpush
@endunless
