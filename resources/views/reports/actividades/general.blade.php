@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Talleres y Actividades')
@section('report_title', 'Reporte de Actividades')

@section('content')
 <div class="section-title">Resumen Estadístico de Talleres</div>
 
 <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
 <tr>
 <td style="width: 50%; padding-right: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\ActividadAdulto::count() }}</div>
 <div class="summary-label">Talleres Realizados</div>
 </div>
 </td>
 <td style="width: 50%; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\TipoActividadAdulto::count() }}</div>
 <div class="summary-label">Tipos de Talleres</div>
 </div>
 </td>
 </tr>
 </table>

 <div class="section-title">Listado de Actividades Recientes</div>
 
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 30%;">Tipo de Actividad</th>
 <th style="width: 25%;">Adulto Mayor</th>
 <th style="width: 25%;">Observación</th>
 <th style="width: 10%;">Fecha</th>
 <th style="width: 10%;">Estado</th>
 </tr>
 </thead>
 <tbody>
 @foreach (\App\Models\ActividadAdulto::with(['tipoActividad', 'adultoMayor'])->orderBy('fecha', 'desc')->take(15)->get() as $act)
 <tr>
 <td><strong>{{ optional($act->tipoActividad)->tipo ?? 'Sin tipo' }}</strong></td>
 <td>{{ trim((optional($act->adultoMayor)->nombres ?? '') . ' ' . (optional($act->adultoMayor)->ap_paterno ?? '')) ?: 'Sin registro' }}</td>
 <td>{{ $act->obs ? \Illuminate\Support\Str::limit($act->obs, 60) : '—' }}</td>
 <td>{{ $act->fecha ? \Carbon\Carbon::parse($act->fecha)->format('d/m/Y') : 'Sin fecha' }}</td>
 <td>{{ $act->estado ?? '—' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box">
 <strong>Nota del Área Social:</strong> Los talleres de terapia física y estimulación psicomotriz son vitales para combatir el aislamiento social y mejorar el bienestar neurocognitivo general de los residentes de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </div>
@endsection
