@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Salud y Seguimiento Clínico')
@section('report_title', 'Reporte General de Salud')

@section('content')
 <div class="section-title">Resumen de Controles de Salud</div>
 
 <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
 <tr>
 <td style="width: 33%; padding-right: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\AdultoMayor::count() }}</div>
 <div class="summary-label">Residentes bajo Cuidado</div>
 </div>
 </td>
 <td style="width: 33%; padding-right: 10px; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\SignosVitalesAdulto::count() }}</div>
 <div class="summary-label">Controles de Signos Vitales</div>
 </div>
 </td>
 <td style="width: 34%; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ \App\Models\MedicacionAdulto::count() }}</div>
 <div class="summary-label">Recetas Activas</div>
 </div>
 </td>
 </tr>
 </table>

 <div class="section-title">Detalle de Diagnósticos y Alertas</div>
 
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 30%;">Residente</th>
 <th style="width: 25%;">Estado Residencial</th>
 <th style="width: 25%;">Último Control</th>
 <th style="width: 20%;">Oxígeno Promedio</th>
 </tr>
 </thead>
 <tbody>
 @foreach (\App\Models\AdultoMayor::with(['estado', 'signosVitales'])->orderBy('ap_paterno')->take(15)->get() as $am)
 @php
 $ultimoControl = $am->signosVitales->first();
 @endphp
 <tr>
 <td><strong>{{ $am->nombres }} {{ $am->ap_paterno }}</strong></td>
 <td>{{ $am->estado ? $am->estado->estado : 'Sin Estado' }}</td>
 <td>{{ $ultimoControl ? \Carbon\Carbon::parse($ultimoControl->fecha)->format('d/m/Y') : 'Sin controles' }}</td>
 <td>{{ $ultimoControl ? $ultimoControl->saturacion_oxigeno . '%' : 'N/A' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box">
 <strong>Aviso del Departamento de Enfermería:</strong> Los signos vitales y alarmas de saturación de oxígeno inferiores a 90% deben ser informados de inmediato al médico de turno para el protocolo de emergencia de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </div>
@endsection
