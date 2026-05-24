@extends('reportes.layouts.reporte-base')

@section('titulo', $metadata['titulo'] ?? 'Reporte de Actividades')

@section('contenido')

@if(!$esPdf)
<div class="rm-acciones">
    <a href="{{ route('admin.reportes.actividades.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
    <a href="{{ route('admin.reportes.actividades.excel') }}" class="rm-btn rm-btn-excel">Exportar Excel</a>
</div>
@endif

{{-- Encabezado --}}
<div class="rm-header">
    <div class="rm-header-logo">RememberMind</div>
    <div class="rm-header-sub">Casa Amandita — {{ $metadata['titulo'] }}</div>
    <span class="rm-header-badge">Actividades</span>
    <div class="rm-header-meta">
        Generado el {{ $metadata['generado_en'] }} por {{ $metadata['generado_por'] }}
    </div>
</div>

{{-- KPIs --}}
<div class="rm-seccion">
    <div class="rm-seccion-titulo">Resumen de Actividades</div>
    <table class="rm-kpi-wrap">
        <tr class="rm-kpi-row">
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['resumen']['total'] }}</div>
                <div class="rm-kpi-titulo">Total Registradas</div>
            </td>
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['completadas'] }}</div>
                <div class="rm-kpi-titulo">Completadas</div>
            </td>
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['pendientes'] }}</div>
                <div class="rm-kpi-titulo">Pendientes</div>
            </td>
        </tr>
    </table>
    <div style="margin-top:8px; font-size:9pt; color:#666;">
        Canceladas: <strong>{{ $datos['resumen']['canceladas'] }}</strong>
    </div>
</div>

{{-- Actividades por mes --}}
@if(!empty($graficas['por_mes']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-morado">Actividades por Mes ({{ now()->year }})</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Actividades mensuales',
        'labels'  => $graficas['por_mes']['labels'],
        'data'    => $graficas['por_mes']['data'],
        'colores' => $graficas['por_mes']['colores'],
    ])
</div>
@endif

{{-- Distribución por estado --}}
@if(!empty($graficas['estado']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-verde">Estado de las Actividades</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Estado',
        'labels'  => $graficas['estado']['labels'],
        'data'    => $graficas['estado']['data'],
        'colores' => $graficas['estado']['colores'],
    ])
</div>
@endif

<div class="rm-footer">
    Casa Amandita · RememberMind · {{ $metadata['generado_en'] }}
</div>

@if(!$esPdf)
@push('scripts')
{{-- Chart.js se integrará en FASE 2B --}}
@endpush
@endif

@endsection
