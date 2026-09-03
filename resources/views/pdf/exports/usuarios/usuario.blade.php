@extends('pdf.exports.layouts.report-layout')

@section('title', 'Ficha Técnica de Usuario')
@section('report_title', 'Ficha de Usuario')

@section('content')
 <div class="section-title">Información de Identidad y Acceso</div>
 
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Nombre de Usuario:</td>
 <td class="value-detail"><strong>{{ $usuario_data->name }}</strong></td>
 </tr>
 <tr>
 <td class="label-detail">Correo Electrónico:</td>
 <td class="value-detail">{{ $usuario_data->email }}</td>
 </tr>
 <tr>
 <td class="label-detail">Área Asignada:</td>
 <td class="value-detail">{{ $usuario_data->area ? $usuario_data->area->nombre : 'Sin Área Asignada' }}</td>
 </tr>
 <tr>
 <td class="label-detail">Rol Principal:</td>
 <td class="value-detail"><strong>{{ $usuario_data->getRoleNames()->first() ?? 'Sin Rol Asignado' }}</strong></td>
 </tr>
 <tr>
 <td class="label-detail">Estado de Cuenta:</td>
 <td class="value-detail">
 <span class="badge-status {{ $usuario_data->estado == 1 ? 'badge-active' : 'badge-inactive' }}">
 {{ $usuario_data->estado == 1 ? 'ACTIVO' : 'INACTIVO' }}
 </span>
 </td>
 </tr>
 <tr>
 <td class="label-detail">Miembro Desde:</td>
 <td class="value-detail">{{ $usuario_data->created_at ? $usuario_data->created_at->format('d/m/Y') : 'Sin registro' }}</td>
 </tr>
 </table>
 </div>

 <div class="section-title">Permisos Asignados Indirectamente por Rol</div>
 
 @php
 $permisos = $usuario_data->getAllPermissions()->pluck('name');
 @endphp

 @if ($permisos->count() > 0)
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 50%;">Clave del Permiso</th>
 <th style="width: 50%;">Descripción Operativa</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($permisos as $permiso)
 <tr>
 <td><code>{{ $permiso }}</code></td>
 <td>Permiso heredado del rol institucional asignado.</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @else
 <div style="padding: 20px; text-align: center; color: #7C7168; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 6px; font-size: 10px;">
 Este usuario no cuenta con permisos especiales ni heredados en la plataforma.
 </div>
 @endif

 <div class="alert-box">
 <strong>Aviso de Seguridad:</strong> De acuerdo con el principio de mínimo privilegio, los usuarios de salud y administrativos tienen acceso restringido a las secciones clínicas basadas estrictamente en sus Spatie Roles. Toda alteración de esta ficha debe ser autorizada por Dirección.
 </div>
@endsection

