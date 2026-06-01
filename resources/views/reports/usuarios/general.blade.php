@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Usuarios y Personal')
@section('report_title', 'Reporte General de Usuarios')

@section('content')
 <div class="section-title">Resumen del Personal Administrativo y de Salud</div>
 
 <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
 <tr>
 <td style="width: 33%; padding-right: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ $usuarios->count() }}</div>
 <div class="summary-label">Total de Usuarios</div>
 </div>
 </td>
 <td style="width: 33%; padding-right: 10px; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ $usuarios->where('estado', 1)->count() }}</div>
 <div class="summary-label">Usuarios Activos</div>
 </div>
 </td>
 <td style="width: 34%; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ $usuarios->where('estado', '!=', 1)->count() }}</div>
 <div class="summary-label">Usuarios Inactivos</div>
 </div>
 </td>
 </tr>
 </table>

 <div class="section-title">Listado de Usuarios Registrados</div>
 
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 30%;">Nombre Completo</th>
 <th style="width: 25%;">Correo Electrónico</th>
 <th style="width: 20%;">Área Asignada</th>
 <th style="width: 15%;">Rol</th>
 <th style="width: 10%;">Estado</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($usuarios as $u)
 <tr>
 <td><strong>{{ $u->name }}</strong></td>
 <td>{{ $u->email }}</td>
 <td>{{ $u->area ? $u->area->nombre : 'Sin Área' }}</td>
 <td>{{ $u->getRoleNames()->first() ?? 'Sin Rol' }}</td>
 <td>
 <span class="badge-status {{ $u->estado == 1 ? 'badge-active' : 'badge-inactive' }}">
 {{ $u->estado == 1 ? 'ACTIVO' : 'INACTIVO' }}
 </span>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box">
 <strong>Control de Accesos:</strong> Todos los intentos de inicio de sesión, cambios de contraseña y modificaciones de privilegios son rastreados por el subsistema de auditoría Spatie Activitylog, garantizando la integridad de RememberMind.
 </div>
@endsection
