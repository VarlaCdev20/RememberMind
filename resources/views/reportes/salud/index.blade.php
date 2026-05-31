@extends('reportes.layouts.reporte-base')

@section('titulo', $metadata['titulo'] ?? 'Reporte de Salud y Seguimiento')

@section('contenido')

@if(!$esPdf)
<div class="rm-acciones">
    <a href="{{ route('admin.reportes.salud.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
    <a href="{{ route('admin.reportes.salud.excel') }}" class="rm-btn rm-btn-excel">Exportar Excel</a>
</div>
@endif

{{-- Encabezado --}}
<div class="rm-header">
    <div class="rm-header-logo">RememberMind</div>
    <div class="rm-header-sub">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — {{ $metadata['titulo'] }}</div>
    <span class="rm-header-badge">Salud y Seguimiento</span>
    <div class="rm-header-meta">
        Generado el {{ $metadata['generado_en'] }} por {{ $metadata['generado_por'] }}
    </div>
</div>

{{-- KPIs --}}
<div class="rm-seccion">
    <div class="rm-seccion-titulo">Resumen de Salud</div>
    <table class="rm-kpi-wrap">
        <tr class="rm-kpi-row">
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['resumen']['fichas'] }}</div>
                <div class="rm-kpi-titulo">Fichas Médicas</div>
            </td>
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['medicaciones'] }}</div>
                <div class="rm-kpi-titulo">Medicaciones Activas</div>
            </td>
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['valoraciones'] }}</div>
                <div class="rm-kpi-titulo">Valoraciones Vigentes</div>
            </td>
        </tr>
    </table>
    <div style="margin-top:8px; font-size:9pt; color:#666;">
        Atenciones registradas: <strong>{{ $datos['resumen']['atenciones'] }}</strong>
    </div>
</div>

{{-- Distribución por nivel de dependencia --}}
@if(!empty($graficas['dependencia']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-verde">Nivel de Dependencia Funcional</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Dependencia',
        'labels'  => $graficas['dependencia']['labels'],
        'data'    => $graficas['dependencia']['data'],
        'colores' => $graficas['dependencia']['colores'],
    ])
</div>
@endif

{{-- Riesgos de caída --}}
@if(!empty($graficas['riesgos']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-naranja">Riesgo de Caída</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Riesgo de Caída',
        'labels'  => $graficas['riesgos']['labels'],
        'data'    => $graficas['riesgos']['data'],
        'colores' => $graficas['riesgos']['colores'],
    ])
</div>
@endif

{{-- Atenciones por mes --}}
@if(!empty($graficas['atenciones']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-morado">Atenciones por Mes ({{ now()->year }})</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Atenciones mensuales',
        'labels'  => $graficas['atenciones']['labels'],
        'data'    => $graficas['atenciones']['data'],
        'colores' => $graficas['atenciones']['colores'],
    ])
</div>
@endif

<div class="rm-footer">
    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · RememberMind · {{ $metadata['generado_en'] }}
</div>

@if(!$esPdf)
@push('scripts')
{{-- Chart.js se integrará en FASE 2B --}}
@endpush
@endif

@endsection
