@extends('layouts.reportes.reporte-base')

@section('titulo', $metadata['titulo'] ?? 'Reporte de Familiares')

@section('contenido')

@if(!$esPdf)
<div class="rm-acciones">
 <a href="{{ route('admin.reportes.familiares.pdf') }}" class="rm-btn rm-btn-pdf">Descargar PDF</a>
 <a href="{{ route('admin.reportes.familiares.excel') }}" class="rm-btn rm-btn-excel">Exportar Excel</a>
</div>
@endif

{{-- Encabezado --}}
<div class="rm-header">
 <div class="rm-header-logo">RememberMind</div>
 <div class="rm-header-sub">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — {{ $metadata['titulo'] }}</div>
 <span class="rm-header-badge">Familiares y Red de Apoyo</span>
 <div class="rm-header-meta">
 Generado el {{ $metadata['generado_en'] }} por {{ $metadata['generado_por'] }}
 </div>
</div>

{{-- KPIs --}}
<div class="rm-seccion">
 <div class="rm-seccion-titulo">Resumen de Red Familiar</div>
 <table class="rm-kpi-wrap">
 <tr class="rm-kpi-row">
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-azul">{{ $datos['resumen']['total_familiares'] }}</div>
 <div class="rm-kpi-titulo">Familiares Registrados</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-verde">{{ $datos['resumen']['vinculos_activos'] }}</div>
 <div class="rm-kpi-titulo">Vínculos Activos</div>
 </td>
 <td class="rm-kpi-cell">
 <div class="rm-kpi-valor rm-kpi-valor-naranja">{{ $datos['resumen']['adultos_sin_familiar'] }}</div>
 <div class="rm-kpi-titulo">Sin Familiar Registrado</div>
 </td>
 </tr>
 </table>
</div>

{{-- Distribución por parentesco --}}
@if(!empty($graficas['parentescos']['labels']))
<div class="rm-seccion">
 <div class="rm-seccion-titulo-verde">Distribución por Parentesco</div>
 @include('pages.reportes.partials.grafica-barras-pdf', [
 'titulo' => 'Parentesco',
 'labels' => $graficas['parentescos']['labels'],
 'data' => $graficas['parentescos']['data'],
 'colores' => $graficas['parentescos']['colores'],
 ])
</div>
@endif

{{-- Adultos sin familiar --}}
@if(!empty($datos['adultos_sin_fam']))
<div class="rm-seccion">
 <div class="rm-seccion-titulo-naranja">Adultos Mayores sin Familiar Registrado</div>
 <table class="rm-table">
 <thead>
 <tr>
 <th>Nombre</th>
 </tr>
 </thead>
 <tbody>
 @foreach(array_slice((array) $datos['adultos_sin_fam'], 0, 20) as $adulto)
 <tr>
 <td>{{ $adulto->nombre ?? $adulto['nombre'] ?? '—' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @if(count($datos['adultos_sin_fam']) > 20)
 <p style="font-size:8pt; color:#888; margin-top:4px;">
 Se muestran 20 de {{ count($datos['adultos_sin_fam']) }} registros.
 </p>
 @endif
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
