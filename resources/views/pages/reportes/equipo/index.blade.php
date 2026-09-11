@extends('layouts.reportes.reporte-base')

@section('titulo', $metadata['titulo'] ?? 'Reporte del Equipo Institucional')

@section('contenido')

@if(!$esPdf)
<div class="rm-acciones">
 <a href="{{ route('admin.reportes.equipo.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
 <a href="{{ route('admin.reportes.equipo.excel') }}" class="rm-btn rm-btn-excel">Exportar Excel</a>
</div>
@endif

{{-- Encabezado --}}
<div class="rm-header">
 <div class="rm-header-logo">RememberMind</div>
 <div class="rm-header-sub">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — {{ $metadata['titulo'] }}</div>
 <span class="rm-header-badge">Equipo Institucional</span>
 <div class="rm-header-meta">
 Generado el {{ $metadata['generado_en'] }} por {{ $metadata['generado_por'] }}
 </div>
</div>

{{-- KPIs --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo">Resumen del Equipo</div>
 <table class="rm-kpi-wrap">
 <tr class="rm-kpi-row">
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['resumen']['personal_salud'] }}</div>
 <div class="rm-kpi-titulo">Personal de Salud</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['personal_admin'] }}</div>
 <div class="rm-kpi-titulo">Personal Administrativo</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-morado">{{ $datos['resumen']['voluntarios'] }}</div>
 <div class="rm-kpi-titulo">Voluntarios Activos</div>
 </td>
 </tr>
 </table>
 <div style="margin-top:8px; font-size:9pt; color:#666;">
 Total del equipo: <strong>{{ $datos['resumen']['total'] }}</strong> personas
 </div>
</div>

{{-- Especialidades del personal de salud --}}
@if(!empty($graficas['especialidades']['labels']))
<div class="rm-seccion">
 <div class="rm-seccion-titulo-verde">Especialidades — Personal de Salud</div>
 @include('pages.reportes.partials.grafica-barras-pdf', [
 'titulo' => 'Especialidades',
 'labels' => $graficas['especialidades']['labels'],
 'data' => $graficas['especialidades']['data'],
 'colores' => $graficas['especialidades']['colores'],
 ])
</div>
@endif

{{-- Áreas de voluntarios --}}
@if(!empty($graficas['areas_voluntarios']['labels']))
<div class="rm-seccion">
 <div class="rm-seccion-titulo-morado">Áreas de Apoyo — Voluntarios</div>
 @include('pages.reportes.partials.grafica-barras-pdf', [
 'titulo' => 'Áreas',
 'labels' => $graficas['areas_voluntarios']['labels'],
 'data' => $graficas['areas_voluntarios']['data'],
 'colores' => $graficas['areas_voluntarios']['colores'],
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
