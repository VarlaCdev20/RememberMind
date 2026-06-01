@extends('reports.layouts.report-layout')

@section('title', 'Reporte de Bitácora de Operaciones y Auditoría')
@section('report_title', 'Reporte de Bitácora Global')

@section('content')
 <div class="section-title">Resumen de Actividad del Sistema</div>
 
 <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
 <tr>
 <td style="width: 50%; padding-right: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ $actividades->count() }}</div>
 <div class="summary-label">Acciones Registradas en Reporte</div>
 </div>
 </td>
 <td style="width: 50%; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ $actividades->unique('causer_id')->count() }}</div>
 <div class="summary-label">Usuarios Operadores Distintos</div>
 </div>
 </td>
 </tr>
 </table>

 <div class="section-title">Registro de Cambios y Modificaciones Recientes</div>
 
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 15%;">Módulo</th>
 <th style="width: 50%;">Descripción de la Acción</th>
 <th style="width: 20%;">Operador</th>
 <th style="width: 15%;">Fecha</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($actividades as $act)
 <tr>
 <td><strong>{{ $act->log_name ? ucfirst($act->log_name) : 'General' }}</strong></td>
 <td>{{ $act->description }}</td>
 <td>{{ $act->causer ? $act->causer->name : 'Sistema' }}</td>
 <td>{{ $act->created_at ? $act->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box">
 <strong>Aviso de Seguridad de Sistemas:</strong> La bitácora de auditoría es un registro inmutable y permanente. La elisión o alteración de estos registros está estrictamente prohibida y regulada bajo las políticas de cumplimiento digital de RememberMind.
 </div>
@endsection
