@extends('reportes.layouts.reporte-base')

@section('titulo', $metadata['titulo'] ?? 'Reporte de Bitácora')

@section('contenido')

@if(!$esPdf)
<div class="rm-acciones">
    <a href="{{ route('admin.reportes.bitacora.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
</div>
@endif

{{-- Encabezado --}}
<div class="rm-header">
    <div class="rm-header-logo">RememberMind</div>
    <div class="rm-header-sub">Casa Amandita — {{ $metadata['titulo'] }}</div>
    <span class="rm-header-badge">Bitácora de Auditoría</span>
    <div class="rm-header-meta">
        Generado el {{ $metadata['generado_en'] }} por {{ $metadata['generado_por'] }}
    </div>
</div>

{{-- Nota de seguridad --}}
@if(!empty($metadata['nota']))
<div class="rm-nota-legal">{{ $metadata['nota'] }}</div>
@endif

{{-- KPIs --}}
<div class="rm-seccion">
    <div class="rm-seccion-titulo">Resumen de Actividad</div>
    <table class="rm-kpi-wrap">
        <tr class="rm-kpi-row">
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['resumen']['total'] }}</div>
                <div class="rm-kpi-titulo">Total Eventos</div>
            </td>
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['hoy'] }}</div>
                <div class="rm-kpi-titulo">Eventos Hoy</div>
            </td>
            <td class="rm-kpi-cell">
                <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['esta_semana'] }}</div>
                <div class="rm-kpi-titulo">Esta Semana</div>
            </td>
        </tr>
    </table>
    <div style="margin-top:8px; font-size:9pt; color:#666;">
        Módulos con registros: <strong>{{ $datos['resumen']['modulos'] }}</strong>
    </div>
</div>

{{-- Actividad por módulo --}}
@if(!empty($graficas['modulos']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-verde">Eventos por Módulo</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Módulos',
        'labels'  => $graficas['modulos']['labels'],
        'data'    => $graficas['modulos']['data'],
        'colores' => $graficas['modulos']['colores'],
    ])
</div>
@endif

{{-- Distribución por tipo de evento --}}
@if(!empty($graficas['eventos']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-naranja">Tipo de Eventos</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Eventos',
        'labels'  => $graficas['eventos']['labels'],
        'data'    => $graficas['eventos']['data'],
        'colores' => $graficas['eventos']['colores'],
    ])
</div>
@endif

{{-- Tendencia mensual --}}
@if(!empty($graficas['tendencia']['labels']))
<div class="rm-seccion">
    <div class="rm-seccion-titulo-morado">Tendencia Mensual ({{ now()->year }})</div>
    @include('reportes.partials.grafica-barras-pdf', [
        'titulo'  => 'Tendencia',
        'labels'  => $graficas['tendencia']['labels'],
        'data'    => $graficas['tendencia']['data'],
        'colores' => $graficas['tendencia']['colores'],
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
