@extends('pdf.exports.layouts.report-layout')

@section('title', 'Expediente Institucional - ' . $usuario->name)
@section('report_title', 'EXPEDIENTE DIGITAL DE USUARIO')

@section('content')
 <!-- Perfil de Identidad -->
 <div class="section-title">Datos Personales y de Identidad</div>
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Nombre Completo:</td>
 <td class="value-detail" style="font-weight: bold; font-size: 11px;">{{ $usuario->nombres }} {{ $usuario->ap_paterno }} {{ $usuario->ap_materno }}</td>
 <td class="label-detail">Código Interno:</td>
 <td class="value-detail" style="font-weight: bold; color: #E27D60;">{{ $usuario->cod_usu }}</td>
 </tr>
 <tr>
 <td class="label-detail">Documento Identidad:</td>
 <td class="value-detail">{{ $usuario->tipo_documento ?? 'CI' }} {{ $usuario->numero_documento }} {{ $usuario->expedido }}</td>
 <td class="label-detail">Nacionalidad:</td>
 <td class="value-detail">{{ $usuario->pais_documento ?? 'Bolivia' }}</td>
 </tr>
 <tr>
 <td class="label-detail">Género:</td>
 <td class="value-detail">{{ $usuario->genero ?? 'No especificado' }}</td>
 <td class="label-detail">Fecha Nacimiento:</td>
 <td class="value-detail">
 {{ $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('d/m/Y') : 'No registrado' }}
 @if($usuario->fecha_nacimiento)
 ({{ $usuario->fecha_nacimiento->age }} años)
 @endif
 </td>
 </tr>
 <tr>
 <td class="label-detail">Correo Electrónico:</td>
 <td class="value-detail" style="text-transform: lowercase;">{{ $usuario->correo }}</td>
 <td class="label-detail">Celular / Teléfono:</td>
 <td class="value-detail">{{ $usuario->codigo_telefono }} {{ $usuario->telefono ?? 'Sin registrar' }}</td>
 </tr>
 </table>
 </div>

 <!-- Perfil del Sistema -->
 <div class="section-title">Perfil Institucional y Desempeño</div>
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Rol Asignado:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $nombre_rol }}</td>
 <td class="label-detail">Área Operativa:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $area }}</td>
 </tr>
 <tr>
 <td class="label-detail">Cargo / Especialidad:</td>
 <td class="value-detail">
 @if($usuario->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']))
 {{ $usuario->personalSalud?->especialidad?->nombre ?? 'Personal de salud' }}
 @elseif($usuario->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']))
 {{ $usuario->personalAdmin?->cargoAdmin?->nombre ?? 'Personal administrativo' }}
 @else
 {{ $nombre_rol }}
 @endif
 </td>
 <td class="label-detail">Fecha Ingreso:</td>
 <td class="value-detail">
 @if($usuario->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) && $usuario->personalSalud?->fecha_ing)
 {{ $usuario->personalSalud->fecha_ing instanceof \Carbon\Carbon ? $usuario->personalSalud->fecha_ing->format('d/m/Y') : \Carbon\Carbon::parse($usuario->personalSalud->fecha_ing)->format('d/m/Y') }}
 @elseif($usuario->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']) && $usuario->personalAdmin?->fecha_ingreso)
 {{ $usuario->personalAdmin->fecha_ingreso instanceof \Carbon\Carbon ? $usuario->personalAdmin->fecha_ingreso->format('d/m/Y') : \Carbon\Carbon::parse($usuario->personalAdmin->fecha_ingreso)->format('d/m/Y') }}
 @else
 {{ $usuario->created_at->format('d/m/Y') }}
 @endif
 </td>
 </tr>
 <tr>
 <td class="label-detail">Estado General:</td>
 <td class="value-detail">
 <span class="badge-status {{ $usuario->estado === 'ACTIVO' ? 'badge-active' : 'badge-inactive' }}">
 {{ $usuario->estado }}
 </span>
 </td>
 <td class="label-detail">Acceso Sistema:</td>
 <td class="value-detail" style="font-weight: bold;">
 {{ $usuario->acceso_sistema }}
 </td>
 </tr>
 </table>
 </div>

 <!-- Asignación Horaria Planificada -->
 <div class="section-title">Horarios y Jornada Laboral</div>
 @if($horarios)
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Turno Planificado:</td>
 <td class="value-detail" style="font-weight: bold; color: #2F3E5C;">{{ $horarios->turno?->nombre }}</td>
 <td class="label-detail">Sector Asignado:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $horarios->area?->nombre }}</td>
 </tr>
 <tr>
 <td class="label-detail">Rango Horario:</td>
 <td class="value-detail">{{ substr($horarios->turno?->hora_inicio, 0, 5) }} a {{ substr($horarios->turno?->hora_fin, 0, 5) }}</td>
 <td class="label-detail">Das de Jornada:</td>
 <td class="value-detail">
 @foreach($horarios->dias_semana ?? [] as $d)
 <span style="font-weight: bold; background-color: #2F3E5C; color: #ffffff; padding: 1px 3px; border-radius: 3px; font-size: 7px; margin-right: 2px;">
 {{ $d }}
 </span>
 @endforeach
 </td>
 </tr>
 </table>
 </div>
 @else
 <div class="details-box" style="text-align: center; color: #7C7168; font-style: italic; font-size: 10px; padding: 15px;">
 El usuario no tiene una asignación de jornada horaria activa registrada en el sistema.
 </div>
 @endif

 <div class="page-break"></div>

 <!-- Expediente Documental -->
 <div class="section-title">Estatus de Expediente Documental (Avance: {{ $avance_documental['porcentaje_avance'] }}%)</div>
 <div style="margin-bottom: 12px; background-color: #F8F3ED; border: 1px solid #E6DDD3; border-radius: 6px; padding: 6px 12px; font-size: 9px; font-weight: bold; color: #2F3E5C;">
 Requisitos validados: {{ $avance_documental['validados'] }} de {{ $avance_documental['total_requeridos'] }} obligatorios requeridos.
 </div>

 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 45%;">Requisito / Documento</th>
 <th style="width: 15%;">Tipo</th>
 <th style="width: 20%;">Estatus</th>
 <th style="width: 20%;">Fecha Carga</th>
 </tr>
 </thead>
 <tbody>
 @foreach($checklist as $doc)
 <tr>
 <td style="font-weight: bold;">
 {{ $doc['nombre'] }}
 <div style="font-size: 8px; color: #7C7168; font-weight: normal; margin-top: 1px;">
 {{ $doc['descripcion'] }}
 </div>
 </td>
 <td>
 @if($doc['obligatorio'])
 <span style="color: #E27D60; font-weight: bold;">OBLIGATORIO</span>
 @else
 <span style="color: #7C7168;">OPCIONAL</span>
 @endif
 </td>
 <td>
 @php
 $statusColor = match($doc['estado']) {
 'VALIDADO' => 'color: #63775B; font-weight: bold;',
 'CARGADO' => 'color: #5E6599; font-weight: bold;',
 'OBSERVADO' => 'color: #E27D60; font-weight: bold;',
 'VENCIDO' => 'color: #7B624F; font-weight: bold;',
 default => 'color: #7C7168;'
 };
 @endphp
 <span style="{{ $statusColor }}">
 {{ $doc['estado'] }}
 </span>
 </td>
 <td>
 {{ $doc['cargado'] && $doc['documento']->created_at ? $doc['documento']->created_at->format('d/m/Y') : 'Pendiente' }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box" style="margin-top: 30px;">
 <strong>Declaración de Autenticidad:</strong><br>
 El presente expediente digital consolida la información técnica, laboral y legal del usuario registrado en RememberMind. Cualquier inconsistencia o adulteración de los documentos aquí validados dará lugar a las acciones administrativas internas estipuladas por la dirección de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </div>
@endsection

