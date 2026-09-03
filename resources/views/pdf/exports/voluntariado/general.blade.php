@extends('pdf.exports.layouts.report-layout')

@section('title', 'Reporte General de Voluntarios y Apoyos')
@section('report_title', 'Reporte de Voluntariado')

@section('content')
 <div class="section-title">Resumen del Cuerpo de Voluntarios</div>
 
 <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
 <tr>
 <td style="width: 50%; padding-right: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\Voluntario::count() }}</div>
 <div class="summary-label">Voluntarios Totales</div>
 </div>
 </td>
 <td style="width: 50%; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\Voluntario::where('estado', 'ACTIVO')->count() }}</div>
 <div class="summary-label">Voluntarios Activos</div>
 </div>
 </td>
 </tr>
 </table>

 <div class="section-title">Listado de Voluntarios Registrados</div>
 
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 30%;">Nombre Completo</th>
 <th style="width: 25%;">Área de Apoyo</th>
 <th style="width: 25%;">Correo Electrónico</th>
 <th style="width: 20%;">Fecha Ingreso</th>
 </tr>
 </thead>
 <tbody>
 @foreach (\App\Models\Voluntario::with('usuario')->orderBy('fecha_ing', 'desc')->take(15)->get() as $vol)
 <tr>
 <td><strong>{{ $vol->usuario ? $vol->usuario->name : 'No Asignado' }}</strong></td>
 <td>{{ $vol->area_apoyo ?? 'Apoyo General' }}</td>
 <td>{{ $vol->usuario ? $vol->usuario->email : 'Sin Correo' }}</td>
 <td>{{ $vol->fecha_ing ? \Carbon\Carbon::parse($vol->fecha_ing)->format('d/m/Y') : 'Sin fecha' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box">
 <strong>Nota Administrativa:</strong> Todo voluntario debe someterse a la inducción de seguridad y confidencialidad antes de interactuar directamente con los residentes, firmando la cláusula de resguardo de datos de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </div>
@endsection

