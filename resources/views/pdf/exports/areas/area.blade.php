@extends('pdf.exports.layouts.report-layout')

@section('title', 'Reporte Administrativo del Área')
@section('report_title', 'Reporte del Área')

@section('content')
 <div class="section-title">Ficha Técnica del Área</div>
 
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Nombre del Área:</td>
 <td class="value-detail"><strong>{{ $area->nombre }}</strong></td>
 </tr>
 <tr>
 <td class="label-detail">Clasificación / Tipo:</td>
 <td class="value-detail">{{ $area->tipo_area }}</td>
 </tr>
 <tr>
 <td class="label-detail">Responsable Asignado:</td>
 <td class="value-detail"><strong>{{ $area->responsable ? $area->responsable->name : 'Sin Responsable Asignado' }}</strong></td>
 </tr>
 <tr>
 <td class="label-detail">Estado Operativo:</td>
 <td class="value-detail">
 <span class="badge-status {{ strtoupper($area->estado) === 'ACTIVA' ? 'badge-active' : 'badge-inactive' }}">
 {{ $area->estado }}
 </span>
 </td>
 </tr>
 <tr>
 <td class="label-detail">Descripción Operativa:</td>
 <td class="value-detail">{{ $area->descripcion ?? 'Sin descripción provista.' }}</td>
 </tr>
 @if($area->observaciones)
 <tr>
 <td class="label-detail">Observaciones del Área:</td>
 <td class="value-detail" style="color: #E27D60; font-weight: bold;">{{ $area->observaciones }}</td>
 </tr>
 @endif
 </table>
 </div>

 <div class="section-title">Resumen Estadístico</div>
 
 <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
 <tr>
 <td style="width: 33%; padding-right: 10px;">
 <div class="summary-card">
 <div class="summary-number">{{ $totalUsuarios }}</div>
 <div class="summary-label">Total Usuarios</div>
 </div>
 </td>
 <td style="width: 33%; padding-right: 10px; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number" style="color: #63775B;">{{ $usuariosActivos }}</div>
 <div class="summary-label">Usuarios Activos</div>
 </div>
 </td>
 <td style="width: 34%; padding-left: 10px;">
 <div class="summary-card">
 <div class="summary-number" style="color: #E27D60;">{{ $usuariosInactivos }}</div>
 <div class="summary-label">Usuarios Inactivos</div>
 </div>
 </td>
 </tr>
 </table>

 <div class="section-title">Personal Vinculado al Área</div>
 
 @if ($area->usuarios && $area->usuarios->count() > 0)
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 30%;">Nombre Completo</th>
 <th style="width: 20%;">Rol</th>
 <th style="width: 25%;">Cargo/Especialidad</th>
 <th style="width: 10%;">Estado</th>
 <th style="width: 15%;">Último acceso</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($area->usuarios as $u)
 @php
 $cargoEspecialidad = 'Sin Asignar';
 if ($u->personalSalud && $u->personalSalud->especialidad) {
 $cargoEspecialidad = $u->personalSalud->especialidad->nombre;
 } elseif ($u->personalAdmin) {
 $cargoEspecialidad = $u->personalAdmin->cargoAdmin?->nombre ?? $u->personalAdmin->cargo ?? 'Personal Administrativo';
 }
 $rolName = $u->getRoleNames()->first() ?? 'Sin Rol';
 $rolLimpio = strtoupper(str_replace('_', ' ', $rolName));
 @endphp
 <tr>
 <td><strong>{{ $u->name }}</strong></td>
 <td>{{ $rolLimpio }}</td>
 <td>{{ $cargoEspecialidad }}</td>
 <td>
 <span class="badge-status {{ in_array($u->estado, ['ACTIVO', 1, '1']) ? 'badge-active' : 'badge-inactive' }}">
 {{ in_array($u->estado, ['ACTIVO', 1, '1']) ? 'ACTIVO' : 'INACTIVO' }}
 </span>
 </td>
 <td>{{ $u->ultimo_acceso ? \Carbon\Carbon::parse($u->ultimo_acceso)->format('d/m/Y H:i') : 'Nunca' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @else
 <div style="padding: 20px; text-align: center; color: #7C7168; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 6px; font-size: 10px;">
 No hay personal ni usuarios adscritos a esta área institucional actualmente.
 </div>
 @endif

 <div class="alert-box">
 <strong>Trazabilidad y Control Interno:</strong> Todo cambio en los cargos, roles, o la inhabilitación de usuarios pertenecientes al área se registra automáticamente en la bitácora global del sistema para auditorías periódicas.
 </div>
@endsection

